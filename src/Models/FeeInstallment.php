<?php

namespace App\Models;

use PDO;

class FeeInstallment extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Récupérer toutes les tranches paramétrées.
     */
    public function getAll(int $academicYearId): array
    {
        $stmt = $this->db->prepare("
            SELECT fi.*, 
                   c.nom as class_name, 
                   cy.nom as cycle_name, 
                   tt.nom as teaching_type_name
            FROM fee_installments fi
            LEFT JOIN classes c ON fi.class_id = c.id
            LEFT JOIN cycles cy ON fi.cycle_id = cy.id
            LEFT JOIN teaching_types tt ON fi.teaching_type_id = tt.id
            WHERE fi.academic_year_id = ?
            ORDER BY fi.class_id ASC, fi.cycle_id ASC, fi.teaching_type_id ASC, fi.installment_order ASC
        ");
        $stmt->execute([$academicYearId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Résoudre la liste des tranches applicables à une classe (hiérarchie : Classe > Cycle > Type d'enseignement).
     */
    public function resolveInstallments(int $academicYearId, int $classId): array
    {
        // 1. Recherche spécifique par classe
        $stmt = $this->db->prepare("
            SELECT name, installment_order, amount, deadline_date, 'class' as source_type
            FROM fee_installments 
            WHERE class_id = ? AND academic_year_id = ? 
            ORDER BY installment_order ASC
        ");
        $stmt->execute([$classId, $academicYearId]);
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($res)) {
            return $res;
        }

        // Récupérer les informations de la classe (cycle et type d'enseignement)
        $stmtClass = $this->db->prepare("SELECT cycle_id, teaching_type_id, frais_scolarite_brut, nbr_tranches FROM classes WHERE id = ?");
        $stmtClass->execute([$classId]);
        $class = $stmtClass->fetch(PDO::FETCH_ASSOC);

        if ($class) {
            // 2. Recherche par cycle
            if ($class['cycle_id']) {
                $stmt = $this->db->prepare("
                    SELECT name, installment_order, amount, deadline_date, 'cycle' as source_type
                    FROM fee_installments 
                    WHERE cycle_id = ? AND academic_year_id = ? 
                    ORDER BY installment_order ASC
                ");
                $stmt->execute([$class['cycle_id'], $academicYearId]);
                $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($res)) {
                    return $res;
                }
            }

            // 3. Recherche par type d'enseignement
            if ($class['teaching_type_id']) {
                $stmt = $this->db->prepare("
                    SELECT name, installment_order, amount, deadline_date, 'teaching_type' as source_type
                    FROM fee_installments 
                    WHERE teaching_type_id = ? AND academic_year_id = ? 
                    ORDER BY installment_order ASC
                ");
                $stmt->execute([$class['teaching_type_id'], $academicYearId]);
                $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($res)) {
                    return $res;
                }
            }

            // 4. Repli sur les tranches existantes dans class_installments
            $stmtOld = $this->db->prepare("
                SELECT CONCAT('Tranche ', installment_number) as name, 
                       installment_number as installment_order, 
                       amount, 
                       DATE_ADD(CURDATE(), INTERVAL 60 DAY) as deadline_date,
                       'legacy' as source_type
                FROM class_installments 
                WHERE class_id = ? 
                ORDER BY installment_number ASC
            ");
            $stmtOld->execute([$classId]);
            $res = $stmtOld->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($res)) {
                // Check if deadlines exist in installment_deadlines for legacy fallback
                $stmtDl = $this->db->prepare("SELECT installment_number, deadline_date FROM installment_deadlines WHERE class_id = ? AND academic_year_id = ?");
                $stmtDl->execute([$classId, $academicYearId]);
                $deadlines = $stmtDl->fetchAll(PDO::FETCH_KEY_PAIR);
                foreach ($res as &$r) {
                    if (isset($deadlines[$r['installment_order']])) {
                        $r['deadline_date'] = $deadlines[$r['installment_order']];
                    }
                }
                return $res;
            }

            // 5. Par défaut, 1 tranche de 100%
            $tuition = (float)$class['frais_scolarite_brut'];
            return [
                [
                    'name' => 'Tranche Unique',
                    'installment_order' => 1,
                    'amount' => $tuition,
                    'deadline_date' => date('Y-12-31'),
                    'source_type' => 'default'
                ]
            ];
        }

        return [];
    }

    /**
     * Créer une tranche.
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO fee_installments (academic_year_id, name, installment_order, amount, deadline_date, class_id, cycle_id, teaching_type_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            (int)$data['academic_year_id'],
            trim((string)$data['name']),
            (int)$data['installment_order'],
            (float)$data['amount'],
            $data['deadline_date'],
            !empty($data['class_id']) ? (int)$data['class_id'] : null,
            !empty($data['cycle_id']) ? (int)$data['cycle_id'] : null,
            !empty($data['teaching_type_id']) ? (int)$data['teaching_type_id'] : null
        ]);
    }

    /**
     * Supprimer toutes les tranches d'un groupe spécifique pour ré-initialisation.
     */
    public function deleteByGroup(int $academicYearId, ?int $classId, ?int $cycleId, ?int $teachingTypeId): bool
    {
        $sql = "DELETE FROM fee_installments WHERE academic_year_id = ?";
        $params = [$academicYearId];

        if ($classId) {
            $sql .= " AND class_id = ?";
            $params[] = $classId;
        } else {
            $sql .= " AND class_id IS NULL";
        }

        if ($cycleId) {
            $sql .= " AND cycle_id = ?";
            $params[] = $cycleId;
        } else {
            $sql .= " AND cycle_id IS NULL";
        }

        if ($teachingTypeId) {
            $sql .= " AND teaching_type_id = ?";
            $params[] = $teachingTypeId;
        } else {
            $sql .= " AND teaching_type_id IS NULL";
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Supprimer une tranche spécifique.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM fee_installments WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
