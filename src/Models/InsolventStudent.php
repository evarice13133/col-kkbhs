<?php

namespace App\Models;

use PDO;

class InsolventStudent extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Récupérer tous les élèves insolvables pour l'année courante avec filtres.
     */
    public function getAll(int $academicYearId, array $filters = []): array
    {
        $sql = "
            SELECT ins.*, 
                   s.nom as student_nom, s.prenom as student_prenom, s.id as student_id,
                   c.nom as class_name,
                   cy.nom as cycle_name,
                   sec.nom as section_name,
                   tt.nom as teaching_type_name,
                   e.reste_a_payer as total_reste_a_payer
            FROM insolvent_students ins
            JOIN students s ON ins.student_id = s.id
            JOIN enrollments e ON (ins.student_id = e.student_id AND ins.academic_year_id = e.academic_year_id)
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN cycles cy ON c.cycle_id = cy.id
            LEFT JOIN sections sec ON c.section_id = sec.id
            LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id
            WHERE ins.academic_year_id = ? AND s.is_withdrawn = 0 AND s.actif = 1 AND s.status != 'Démission' AND s.status != 'Démissionnaire' AND s.status != 'Abandon'
        ";

        $params = [$academicYearId];

        if (!empty($filters['teaching_type_id'])) {
            $sql .= " AND c.teaching_type_id = ?";
            $params[] = (int)$filters['teaching_type_id'];
        }
        if (!empty($filters['cycle_id'])) {
            $sql .= " AND c.cycle_id = ?";
            $params[] = (int)$filters['cycle_id'];
        }
        if (!empty($filters['section_id'])) {
            $sql .= " AND c.section_id = ?";
            $params[] = (int)$filters['section_id'];
        }
        if (!empty($filters['class_id'])) {
            $sql .= " AND s.class_id = ?";
            $params[] = (int)$filters['class_id'];
        }
        if (!empty($filters['q'])) {
            $sql .= " AND (LOWER(s.nom) LIKE ? OR LOWER(s.prenom) LIKE ?)";
            $searchVal = '%' . strtolower($filters['q']) . '%';
            $params[] = $searchVal;
            $params[] = $searchVal;
        }

        $sql .= " ORDER BY ins.amount_due DESC, s.nom ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mettre à jour la table cache des insolvables en comparant les échéances et paiements.
     */
    public function refreshCache(int $academicYearId): void
    {
        // Vider la table pour cette année scolaire
        $stmtDel = $this->db->prepare("DELETE FROM insolvent_students WHERE academic_year_id = ?");
        $stmtDel->execute([$academicYearId]);

        // Sélectionner toutes les inscriptions de l'année
        $enrollments = $this->db->query("
            SELECT e.student_id, e.class_id, e.reste_a_payer 
            FROM enrollments e
            JOIN students s ON e.student_id = s.id
            WHERE e.academic_year_id = $academicYearId AND s.is_withdrawn = 0 AND s.actif = 1 AND s.status != 'Démission' AND s.status != 'Démissionnaire' AND s.status != 'Abandon'
        ")->fetchAll(PDO::FETCH_ASSOC);

        $stmtInst = $this->db->prepare("
            SELECT id, installment_number, amount_planned, amount_paid 
            FROM student_installments 
            WHERE student_id = ? AND academic_year_id = ?
            ORDER BY installment_number ASC
        ");

        $feeInstModel = new FeeInstallment();

        $insertStmt = $this->db->prepare("
            INSERT INTO insolvent_students (student_id, academic_year_id, amount_due, unpaid_installments_count, last_overdue_deadline)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($enrollments as $enroll) {
            $studentId = (int)$enroll['student_id'];
            $classId = (int)$enroll['class_id'];
            
            if (!$classId) {
                continue;
            }

            // Résoudre les tranches et les échéances de la classe
            $resolved = $feeInstModel->resolveInstallments($academicYearId, $classId);
            $deadlines = [];
            foreach ($resolved as $r) {
                $deadlines[(int)$r['installment_order']] = $r['deadline_date'];
            }

            // Récupérer les tranches planifiées de l'élève
            $stmtInst->execute([$studentId, $academicYearId]);
            $studentInsts = $stmtInst->fetchAll(PDO::FETCH_ASSOC);

            // Si l'élève n'a pas de tranches enregistrées, on simule à partir des tranches configurées de la classe
            if (empty($studentInsts)) {
                $studentInsts = [];
                foreach ($resolved as $r) {
                    $studentInsts[] = [
                        'installment_number' => $r['installment_order'],
                        'amount_planned' => $r['amount'],
                        'amount_paid' => 0.0
                    ];
                }
            }

            $amountDueTotal = 0.0;
            $unpaidCount = 0;
            $lastOverdue = null;

            foreach ($studentInsts as $si) {
                $order = (int)$si['installment_number'];
                $planned = (float)$si['amount_planned'];
                $paid = (float)$si['amount_paid'];
                
                // Obtenir l'échéance résolue
                $deadline = $deadlines[$order] ?? null;
                
                // Si la tranche n'est pas soldée
                if ($paid < $planned) {
                    $amountDueTotal += ($planned - $paid);
                    $unpaidCount++;
                    if ($deadline) {
                        if ($lastOverdue === null || $deadline > $lastOverdue) {
                            $lastOverdue = $deadline;
                        }
                    }
                }
            }

            if ($unpaidCount > 0 && $amountDueTotal > 0) {
                $insertStmt->execute([
                    $studentId,
                    $academicYearId,
                    $amountDueTotal,
                    $unpaidCount,
                    $lastOverdue
                ]);
            }
        }
    }

    /**
     * Récupérer les élèves insolvables pour une classe et une tranche spécifique de manière dynamique.
     */
    public function getInsolventsForTranche(int $academicYearId, int $classId, int $installmentNumber): array
    {
        // 1. Résoudre les tranches configurées de la classe pour obtenir le montant attendu
        $feeInstModel = new FeeInstallment();
        $resolved = $feeInstModel->resolveInstallments($academicYearId, $classId);
        
        $plannedAmount = 0.0;
        foreach ($resolved as $r) {
            if ((int)$r['installment_order'] === $installmentNumber) {
                $plannedAmount = (float)$r['amount'];
                break;
            }
        }

        // 2. Récupérer tous les élèves inscrits dans cette classe
        $sql = "
            SELECT 
                s.id as student_id,
                s.nom as student_nom,
                s.prenom as student_prenom,
                s.email as student_matricule,
                c.nom as class_name,
                sec.nom as section_name,
                tt.nom as teaching_type_name,
                e.reste_a_payer as total_reste_a_payer,
                coalesce(si.amount_planned, :planned_amount1) as amount_planned,
                coalesce(si.amount_paid, 0.00) as amount_paid,
                (coalesce(si.amount_planned, :planned_amount2) - coalesce(si.amount_paid, 0.00)) as reste_a_payer
            FROM enrollments e
            JOIN students s ON e.student_id = s.id
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN sections sec ON c.section_id = sec.id
            LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id
            LEFT JOIN student_installments si ON (si.student_id = s.id AND si.academic_year_id = e.academic_year_id AND si.installment_number = :installment_number)
            WHERE e.academic_year_id = :academic_year_id
              AND s.is_withdrawn = 0
              AND s.actif = 1
              AND s.status != 'Démission'
              AND s.status != 'Démissionnaire'
              AND s.status != 'Abandon'
              AND s.class_id = :class_id
            ORDER BY s.nom ASC, s.prenom ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'planned_amount1' => $plannedAmount,
            'planned_amount2' => $plannedAmount,
            'installment_number' => $installmentNumber,
            'academic_year_id' => $academicYearId,
            'class_id' => $classId
        ]);

        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Filtrer les élèves réellement insolvables (reste_a_payer > 0)
        $insolvents = [];
        foreach ($students as $stud) {
            if ((float)$stud['reste_a_payer'] > 0.01) {
                $insolvents[] = $stud;
            }
        }

        return $insolvents;
    }
}
