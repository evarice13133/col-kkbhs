<?php

namespace App\Services;

use PDO;

class FinancialService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Synchronise et recalcule le solde et les tranches d'un élève.
     */
    public function syncStudentFinancials(int $studentId, int $academicYearId): array
    {
        $useTransaction = !$this->db->inTransaction();
        try {
            if ($useTransaction) {
                $this->db->beginTransaction();
            }

            // 1. Récupérer l'élève et sa classe
            $stmt = $this->db->prepare("SELECT s.id, s.class_id, c.frais_scolarite_brut, c.frais_inscription, c.nbr_tranches 
                                        FROM students s
                                        LEFT JOIN classes c ON s.class_id = c.id
                                        WHERE s.id = ?");
            $stmt->execute([$studentId]);
            $student = $stmt->fetch();

            if (!$student) {
                if ($useTransaction) {
                    $this->db->rollBack();
                }
                return ['success' => false, 'message' => 'Élève introuvable.'];
            }

            $classId = $student['class_id'];
            $grossTuition = $student['frais_scolarite_brut'] ? (float)$student['frais_scolarite_brut'] : 0.0;
            $nbrTranches = $student['nbr_tranches'] ? (int)$student['nbr_tranches'] : 0;

            // Si l'élève n'a pas de classe, ses soldes sont mis à zéro
            if (!$classId) {
                // Nettoyer les tranches de l'élève
                $del = $this->db->prepare("DELETE FROM student_installments WHERE student_id = ? AND academic_year_id = ?");
                $del->execute([$studentId, $academicYearId]);

                // Mettre à jour enrollments à zéro
                $this->updateEnrollment($studentId, 0, $academicYearId, 0.0, 0.0, 0.0, 0.0, 0.0);
                if ($useTransaction) {
                    $this->db->commit();
                }
                return ['success' => true, 'message' => 'Élève sans classe synchronisé à zéro.'];
            }

            // 2. Calculer les réductions actives de l'élève
            $totalReductions = 0.0;

            // Réductions individuelles actives
            $stmt = $this->db->prepare("SELECT amount, amount_type FROM student_discounts WHERE student_id = ? AND status = 'active'");
            $stmt->execute([$studentId]);
            while ($disc = $stmt->fetch()) {
                if ($disc['amount_type'] === 'percentage') {
                    $totalReductions += ($grossTuition * (float)$disc['amount'] / 100.0);
                } else {
                    $totalReductions += (float)$disc['amount'];
                }
            }

            // Réductions collectives (classe) actives
            $stmt = $this->db->prepare("SELECT amount, amount_type FROM class_discounts WHERE class_id = ? AND status = 'active'");
            $stmt->execute([$classId]);
            while ($disc = $stmt->fetch()) {
                if ($disc['amount_type'] === 'percentage') {
                    $totalReductions += ($grossTuition * (float)$disc['amount'] / 100.0);
                } else {
                    $totalReductions += (float)$disc['amount'];
                }
            }

            // 3. Calculer les bourses actives de l'élève
            $totalScholarships = 0.0;

            // Bourses individuelles actives
            $stmt = $this->db->prepare("SELECT amount, amount_type FROM student_scholarships WHERE student_id = ? AND status = 'active'");
            $stmt->execute([$studentId]);
            while ($schol = $stmt->fetch()) {
                if ($schol['amount_type'] === 'percentage') {
                    $totalScholarships += ($grossTuition * (float)$schol['amount'] / 100.0);
                } else {
                    $totalScholarships += (float)$schol['amount'];
                }
            }

            // Bourses collectives (classe) actives
            $stmt = $this->db->prepare("SELECT amount, amount_type FROM class_scholarships WHERE class_id = ? AND status = 'active'");
            $stmt->execute([$classId]);
            while ($schol = $stmt->fetch()) {
                if ($schol['amount_type'] === 'percentage') {
                    $totalScholarships += ($grossTuition * (float)$schol['amount'] / 100.0);
                } else {
                    $totalScholarships += (float)$schol['amount'];
                }
            }

            // Calcul de la scolarité nette (solde initial)
            $netTuition = $grossTuition - $totalReductions - $totalScholarships;
            if ($netTuition < 0.0) {
                $netTuition = 0.0;
            }

            // 4. Générer ou mettre à jour les tranches prévues (student_installments)
            // Récupérer la configuration des tranches de la classe via le nouveau modèle FeeInstallment
            $feeInstModel = new \App\Models\FeeInstallment();
            $resolvedInstallments = $feeInstModel->resolveInstallments($academicYearId, $classId);
            
            $classInstallments = [];
            foreach ($resolvedInstallments as $ri) {
                $classInstallments[] = [
                    'installment_number' => $ri['installment_order'],
                    'amount' => $ri['amount']
                ];
            }

            $studentPlannedInstallments = [];
            if ($nbrTranches > 0 && count($classInstallments) > 0) {
                // Calcul du prorata
                $sumClassAmount = 0.0;
                foreach ($classInstallments as $ci) {
                    $sumClassAmount += (float)$ci['amount'];
                }

                if ($sumClassAmount > 0) {
                    $sumCalculated = 0.0;
                    for ($i = 0; $i < count($classInstallments); $i++) {
                        $ci = $classInstallments[$i];
                        $ciNum = (int)$ci['installment_number'];
                        $ciAmount = (float)$ci['amount'];

                        if ($i === count($classInstallments) - 1) {
                            // Ajustement sur la dernière tranche pour éviter les erreurs d'arrondi
                            $amountPlanned = max(0.0, round($netTuition - $sumCalculated, 2));
                        } else {
                            $amountPlanned = round($ciAmount * ($netTuition / $sumClassAmount), 2);
                            $sumCalculated += $amountPlanned;
                        }

                        $studentPlannedInstallments[$ciNum] = $amountPlanned;
                    }
                } else {
                    // Si la somme des tranches de la classe est 0, on répartit équitablement
                    $sumCalculated = 0.0;
                    for ($i = 1; $i <= $nbrTranches; $i++) {
                        if ($i === $nbrTranches) {
                            $amountPlanned = max(0.0, round($netTuition - $sumCalculated, 2));
                        } else {
                            $amountPlanned = round($netTuition / $nbrTranches, 2);
                            $sumCalculated += $amountPlanned;
                        }
                        $studentPlannedInstallments[$i] = $amountPlanned;
                    }
                }
            } else {
                // Pas de tranches configurées sur la classe, une seule tranche de 100% de la scolarité nette
                $studentPlannedInstallments[1] = $netTuition;
            }

            // 5. Récupérer les paiements de scolarité de l'élève (hors annulés)
            $stmt = $this->db->prepare("SELECT SUM(amount) FROM payments WHERE student_id = ? AND academic_year_id = ? AND type = 'scolarite' AND status = 'valide'");
            $stmt->execute([$studentId, $academicYearId]);
            $totalPaid = (float)$stmt->fetchColumn();

            // 6. Insérer ou mettre à jour student_installments avec la distribution des paiements
            $remainingPaid = $totalPaid;
            
            // On supprime d'abord les anciennes tranches pour cette année
            $del = $this->db->prepare("DELETE FROM student_installments WHERE student_id = ? AND academic_year_id = ?");
            $del->execute([$studentId, $academicYearId]);

            $ins = $this->db->prepare("INSERT INTO student_installments (student_id, academic_year_id, installment_number, amount_planned, amount_paid) VALUES (?, ?, ?, ?, ?)");

            foreach ($studentPlannedInstallments as $instNum => $amountPlanned) {
                if ($remainingPaid > 0) {
                    if ($remainingPaid >= $amountPlanned) {
                        $amountPaidForInst = $amountPlanned;
                        $remainingPaid -= $amountPlanned;
                    } else {
                        $amountPaidForInst = $remainingPaid;
                        $remainingPaid = 0.0;
                    }
                } else {
                    $amountPaidForInst = 0.0;
                }

                $ins->execute([$studentId, $academicYearId, $instNum, $amountPlanned, $amountPaidForInst]);
            }

            // 7. Mettre à jour enrollments
            $remainingToPay = max(0.0, round($netTuition - $totalPaid, 2));
            $this->updateEnrollment($studentId, $classId, $academicYearId, $grossTuition, $totalReductions, $totalScholarships, $totalPaid, $remainingToPay);

            if ($useTransaction) {
                $this->db->commit();
            }

            // Actualiser le cache des insolvables
            try {
                $insModel = new \App\Models\InsolventStudent();
                $insModel->refreshCache($academicYearId);
            } catch (\Exception $ex) {}
            return [
                'success' => true,
                'data' => [
                    'gross_tuition' => $grossTuition,
                    'total_reductions' => $totalReductions,
                    'total_bourses' => $totalScholarships,
                    'net_tuition' => $netTuition,
                    'total_paid' => $totalPaid,
                    'remaining_to_pay' => $remainingToPay
                ]
            ];
        } catch (\Exception $e) {
            if ($useTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Synchronise tous les élèves d'une classe pour une année donnée.
     */
    public function syncClassFinancials(int $classId, int $academicYearId): void
    {
        $stmt = $this->db->prepare("SELECT id FROM students WHERE class_id = ?");
        $stmt->execute([$classId]);
        $studentIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($studentIds as $studentId) {
            $this->syncStudentFinancials((int)$studentId, $academicYearId);
        }
    }

    /**
     * Annule un paiement de manière sécurisée (soft delete) et ses paiements enfants.
     */
    public function cancelPayment(int $paymentId, int $userId, string $motive): array
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT * FROM payments WHERE id = ?");
            $stmt->execute([$paymentId]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment) {
                throw new \Exception("Paiement introuvable.");
            }
            if ($payment['status'] === 'annule') {
                throw new \Exception("Ce paiement est déjà annulé.");
            }

            // Annuler le paiement principal
            $upd = $this->db->prepare("UPDATE payments SET status = 'annule', cancelled_by = ?, cancelled_at = NOW(), cancellation_motive = ? WHERE id = ?");
            $upd->execute([$userId, $motive, $paymentId]);

            // Audit
            $this->logHistory($userId, 'payment', $paymentId, 'cancel', $payment, ['motive' => $motive]);

            // Annuler les paiements enfants (ex: surplus d'inscription transformé en scolarité)
            $childStmt = $this->db->prepare("SELECT id FROM payments WHERE parent_payment_id = ? AND status = 'valide'");
            $childStmt->execute([$paymentId]);
            $children = $childStmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($children as $childId) {
                $upd->execute([$userId, "Annulation automatique suite à l'annulation du paiement parent #$paymentId", $childId]);
                $this->logHistory($userId, 'payment', $childId, 'cancel', ['parent_id' => $paymentId], ['motive' => 'Annulation parente']);
            }

            // Resynchroniser l'élève
            $this->syncStudentFinancials((int)$payment['student_id'], (int)$payment['academic_year_id']);

            $this->db->commit();
            return ['success' => true, 'message' => 'Le paiement a été annulé avec succès.', 'student_id' => $payment['student_id']];
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Met à jour ou insère la ligne d'inscription dans la base de données.
     */
    private function updateEnrollment(
        int $studentId,
        int $classId,
        int $academicYearId,
        float $gross,
        float $reductions,
        float $bourses,
        float $paid,
        float $remaining
    ): void {
        $stmt = $this->db->prepare("SELECT id FROM enrollments WHERE student_id = ? AND academic_year_id = ?");
        $stmt->execute([$studentId, $academicYearId]);
        $enrollmentId = $stmt->fetchColumn();

        if ($enrollmentId) {
            $upd = $this->db->prepare("UPDATE enrollments 
                                       SET class_id = ?, frais_scolarite_brut = ?, total_reductions = ?, total_bourses = ?, total_paye = ?, reste_a_payer = ? 
                                       WHERE id = ?");
            $upd->execute([$classId, $gross, $reductions, $bourses, $paid, $remaining, $enrollmentId]);
        } else {
            $ins = $this->db->prepare("INSERT INTO enrollments (student_id, class_id, academic_year_id, frais_scolarite_brut, total_reductions, total_bourses, total_paye, reste_a_payer) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->execute([$studentId, $classId, $academicYearId, $gross, $reductions, $bourses, $paid, $remaining]);
        }
    }

    /**
     * Enregistre un log d'audit financier.
     */
    public function logHistory(
        ?int $userId,
        string $entityType,
        int $entityId,
        string $action,
        $oldValue = null,
        $newValue = null
    ): void {
        $oldValStr = is_array($oldValue) ? json_encode($oldValue, JSON_UNESCAPED_UNICODE) : (string)$oldValue;
        $newValStr = is_array($newValue) ? json_encode($newValue, JSON_UNESCAPED_UNICODE) : (string)$newValue;

        $stmt = $this->db->prepare("INSERT INTO financial_history (user_id, entity_type, entity_id, action, old_value, new_value) 
                                    VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $entityType, $entityId, $action, $oldValStr, $newValStr]);
    }
}
