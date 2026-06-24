<?php

namespace App\Models;

use PDO;

class SchoolFee extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtenir toutes les configurations de frais de scolarité pour une année scolaire.
     */
    public function getAll(int $academicYearId): array
    {
        $stmt = $this->db->prepare("
            SELECT sf.*, 
                   c.nom as class_name, 
                   cy.nom as cycle_name, 
                   tt.nom as teaching_type_name
            FROM school_fees sf
            LEFT JOIN classes c ON sf.class_id = c.id
            LEFT JOIN cycles cy ON sf.cycle_id = cy.id
            LEFT JOIN teaching_types tt ON sf.teaching_type_id = tt.id
            WHERE sf.academic_year_id = ?
            ORDER BY sf.created_at DESC
        ");
        $stmt->execute([$academicYearId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Trouver une configuration par ID.
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM school_fees WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Résoudre le montant des frais pour une classe (hiérarchie : Classe > Cycle > Type d'enseignement).
     */
    public function resolveAmount(int $academicYearId, int $classId): float
    {
        // 1. Recherche par classe spécifique
        $stmt = $this->db->prepare("SELECT amount FROM school_fees WHERE class_id = ? AND academic_year_id = ? LIMIT 1");
        $stmt->execute([$classId, $academicYearId]);
        $val = $stmt->fetchColumn();
        if ($val !== false) {
            return (float)$val;
        }

        // Récupérer les informations de la classe (cycle et type d'enseignement)
        $stmtClass = $this->db->prepare("SELECT cycle_id, teaching_type_id, frais_scolarite_brut FROM classes WHERE id = ?");
        $stmtClass->execute([$classId]);
        $class = $stmtClass->fetch(PDO::FETCH_ASSOC);

        if ($class) {
            // 2. Recherche par cycle
            if ($class['cycle_id']) {
                $stmt = $this->db->prepare("SELECT amount FROM school_fees WHERE cycle_id = ? AND academic_year_id = ? LIMIT 1");
                $stmt->execute([$class['cycle_id'], $academicYearId]);
                $val = $stmt->fetchColumn();
                if ($val !== false) {
                    return (float)$val;
                }
            }

            // 3. Recherche par type d'enseignement
            if ($class['teaching_type_id']) {
                $stmt = $this->db->prepare("SELECT amount FROM school_fees WHERE teaching_type_id = ? AND academic_year_id = ? LIMIT 1");
                $stmt->execute([$class['teaching_type_id'], $academicYearId]);
                $val = $stmt->fetchColumn();
                if ($val !== false) {
                    return (float)$val;
                }
            }

            // 4. Repli sur le montant brut défini dans la table des classes (pour rétrocompatibilité)
            return (float)$class['frais_scolarite_brut'];
        }

        return 0.0;
    }

    /**
     * Enregistrer une nouvelle configuration de frais.
     */
    public function create(array $data): bool
    {
        // Check for duplicates
        $classId = !empty($data['class_id']) ? (int)$data['class_id'] : null;
        $cycleId = !empty($data['cycle_id']) ? (int)$data['cycle_id'] : null;
        $teachingTypeId = !empty($data['teaching_type_id']) ? (int)$data['teaching_type_id'] : null;
        
        $checkSql = "SELECT COUNT(*) FROM school_fees WHERE academic_year_id = ?";
        $checkParams = [(int)$data['academic_year_id']];

        if ($classId) {
            $checkSql .= " AND class_id = ?";
            $checkParams[] = $classId;
        } else {
            $checkSql .= " AND class_id IS NULL";
        }

        if ($cycleId) {
            $checkSql .= " AND cycle_id = ?";
            $checkParams[] = $cycleId;
        } else {
            $checkSql .= " AND cycle_id IS NULL";
        }

        if ($teachingTypeId) {
            $checkSql .= " AND teaching_type_id = ?";
            $checkParams[] = $teachingTypeId;
        } else {
            $checkSql .= " AND teaching_type_id IS NULL";
        }

        $stmtCheck = $this->db->prepare($checkSql);
        $stmtCheck->execute($checkParams);
        if ((int)$stmtCheck->fetchColumn() > 0) {
            // Déjà configuré
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO school_fees (academic_year_id, class_id, cycle_id, teaching_type_id, amount)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            (int)$data['academic_year_id'],
            $classId,
            $cycleId,
            $teachingTypeId,
            (float)$data['amount']
        ]);
    }

    /**
     * Mettre à jour une configuration de frais.
     */
    public function update(int $id, float $amount): bool
    {
        $stmt = $this->db->prepare("UPDATE school_fees SET amount = ? WHERE id = ?");
        return $stmt->execute([$amount, $id]);
    }

    /**
     * Supprimer une configuration de frais.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM school_fees WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
