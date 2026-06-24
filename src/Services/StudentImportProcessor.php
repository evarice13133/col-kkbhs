<?php

namespace App\Services;

use PDO;
use Exception;

class StudentImportProcessor
{
    private PDO $db;
    private int $activeYearId;
    private \App\Services\MatriculeService $matriculeService;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->matriculeService = new \App\Services\MatriculeService($db);
        $this->setActiveYear();
    }

    public function processValidRows(array $validRows): array
    {
        $successCount = 0;
        $errors = [];

        try {
            $this->db->beginTransaction();

            foreach ($validRows as $row) {
                try {
                    $matricule = $row['matricule'];
                    if (empty($matricule)) {
                        $matricule = $this->matriculeService->generate($row['class_id']);
                    }

                    $sql = "INSERT INTO students (nom, prenom, email, class_id, teaching_type_id, sexe, date_naissance, lieu_naissance, is_redoublant, academic_year_id, parent_contact, guardian_contact) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $this->db->prepare($sql);
                    
                    $stmt->execute([
                        $row['nom'],
                        $row['prenom'],
                        $matricule, // email = matricule
                        $row['class_id'],
                        $row['teaching_type_id'],
                        $row['sexe'],
                        $row['date_naissance'],
                        $row['lieu_naissance'],
                        $row['is_redoublant'],
                        $this->activeYearId,
                        $row['parent_contact'],
                        $row['guardian_contact']
                    ]);

                    $studentId = (int)$this->db->lastInsertId();

                    // Initialiser l'inscription financière
                    $enrollmentStmt = $this->db->prepare("INSERT INTO enrollments (student_id, class_id, academic_year_id, frais_scolarite_brut, total_reductions, total_bourses, total_paye, reste_a_payer) VALUES (?, ?, ?, 0.00, 0.00, 0.00, 0.00, 0.00)");
                    $enrollmentStmt->execute([$studentId, $row['class_id'], $this->activeYearId]);

                    // Synchroniser les finances
                    $financialService = new FinancialService($this->db);
                    $financialService->syncStudentFinancials($studentId, $this->activeYearId);

                    $successCount++;
                } catch (Exception $e) {
                    $errors[] = "Erreur insertion Ligne {$row['line']} : " . $e->getMessage();
                }
            }

            if (empty($errors)) {
                $this->db->commit();
            } else {
                $this->db->rollBack();
            }

            return [
                'success' => empty($errors),
                'count' => $successCount,
                'errors' => $errors
            ];

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return [
                'success' => false,
                'count' => 0,
                'errors' => ["Erreur fatale d'importation : " . $e->getMessage()]
            ];
        }
    }

    private function setActiveYear()
    {
        $stmt = $this->db->prepare("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1");
        $stmt->execute();
        $this->activeYearId = (int) $stmt->fetchColumn();
    }
}
