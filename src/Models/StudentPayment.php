<?php

namespace App\Models;

use PDO;

class StudentPayment extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtenir tous les versements pour une année scolaire donnée.
     */
    public function getAll(int $academicYearId, string $search = ''): array
    {
        $sql = "
            SELECT sp.*, 
                   s.nom as student_nom, s.prenom as student_prenom, s.id as student_id,
                   c.nom as class_name,
                   u.nom as creator_nom, u.prenom as creator_prenom
            FROM student_payments sp
            JOIN students s ON sp.student_id = s.id
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN users u ON sp.created_by = u.id
            WHERE sp.academic_year_id = ?
        ";

        $params = [$academicYearId];
        if (!empty($search)) {
            $sql .= " AND (LOWER(s.nom) LIKE ? OR LOWER(s.prenom) LIKE ? OR LOWER(sp.reference) LIKE ?)";
            $searchVal = '%' . strtolower($search) . '%';
            $params[] = $searchVal;
            $params[] = $searchVal;
            $params[] = $searchVal;
        }

        $sql .= " ORDER BY sp.payment_date DESC, sp.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtenir les versements d'un élève.
     */
    public function getByStudent(int $studentId, int $academicYearId): array
    {
        $stmt = $this->db->prepare("
            SELECT sp.*, u.nom as creator_nom, u.prenom as creator_prenom
            FROM student_payments sp
            LEFT JOIN users u ON sp.created_by = u.id
            WHERE sp.student_id = ? AND sp.academic_year_id = ?
            ORDER BY sp.payment_date DESC, sp.created_at DESC
        ");
        $stmt->execute([$studentId, $academicYearId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Trouver un versement par son ID.
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT sp.*, 
                   s.nom as student_nom, s.prenom as student_prenom, s.email as matricule, s.date_naissance, s.lieu_naissance, s.adresse, s.class_id,
                   c.nom as class_name, c.frais_scolarite_brut as class_scolarite_brut,
                   e.frais_scolarite_brut as enroll_scolarite_brut, e.total_reductions, e.total_bourses, e.total_paye, e.reste_a_payer,
                   u.nom as creator_nom, u.prenom as creator_prenom,
                   pr.receipt_number, pr.verification_code, pr.print_count
            FROM student_payments sp
            JOIN students s ON sp.student_id = s.id
            LEFT JOIN enrollments e ON (sp.student_id = e.student_id AND sp.academic_year_id = e.academic_year_id)
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN users u ON sp.created_by = u.id
            LEFT JOIN payment_receipts pr ON sp.id = pr.student_payment_id
            WHERE sp.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Récupérer les allocations par tranche pour un versement.
     */
    public function getAllocations(int $paymentId): array
    {
        $stmt = $this->db->prepare("
            SELECT spa.*, 
                   si.installment_number, si.amount_planned, si.amount_paid as total_installment_paid
            FROM student_payment_allocations spa
            JOIN student_installments si ON spa.student_installment_id = si.id
            WHERE spa.student_payment_id = ?
            ORDER BY si.installment_number ASC
        ");
        $stmt->execute([$paymentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Enregistrer un nouveau versement de scolarité.
     */
    public function create(array $data): ?int
    {
        $useTransaction = !$this->db->inTransaction();
        try {
            if ($useTransaction) {
                $this->db->beginTransaction();
            }

            $studentId = (int) $data['student_id'];
            $academicYearId = (int) $data['academic_year_id'];
            $amount = (float) $data['amount'];
            $paymentDate = $data['payment_date'];
            $paymentMethod = $data['payment_method'];

            if (\App\Services\PaymentReferenceGenerator::isCashMethod($paymentMethod)) {
                $refGen = new \App\Services\PaymentReferenceGenerator($this->db);
                $reference = $refGen->generateUniqueReference();
            } else {
                $reference = !empty($data['reference']) ? trim($data['reference']) : null;
            }

            $observation = !empty($data['observation']) ? trim($data['observation']) : null;
            $createdBy = (int) $data['created_by'];
            $parentPaymentId = isset($data['parent_payment_id']) ? (int) $data['parent_payment_id'] : null;

            // 1. Insérer dans la table existante (legacy) `payments` pour générer un ID unique global
            $stmtLegacy = $this->db->prepare("
                INSERT INTO payments (student_id, academic_year_id, amount, type, payment_date, payment_method, reference, commentaire, created_by, parent_payment_id)
                VALUES (?, ?, ?, 'scolarite', ?, ?, ?, ?, ?, ?)
            ");
            $stmtLegacy->execute([
                $studentId,
                $academicYearId,
                $amount,
                $paymentDate,
                $paymentMethod,
                $reference,
                $observation,
                $createdBy,
                $parentPaymentId
            ]);
            $paymentId = (int) $this->db->lastInsertId();

            // 2. Insérer dans student_payments avec le même ID
            $stmt = $this->db->prepare("
                INSERT INTO student_payments (id, student_id, academic_year_id, amount, payment_date, payment_method, reference, observation, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $paymentId,
                $studentId,
                $academicYearId,
                $amount,
                $paymentDate,
                $paymentMethod,
                $reference,
                $observation,
                $createdBy
            ]);

            // 3. Répartir le montant payé sur les tranches de l'élève (student_installments)
            // Récupérer les tranches planifiées de l'élève pour l'année courante
            $stmtInst = $this->db->prepare("
                SELECT id, installment_number, amount_planned, amount_paid 
                FROM student_installments 
                WHERE student_id = ? AND academic_year_id = ?
                ORDER BY installment_number ASC
            ");
            $stmtInst->execute([$studentId, $academicYearId]);
            $installments = $stmtInst->fetchAll(PDO::FETCH_ASSOC);

            // Si aucune tranche n'existe encore, on lance une synchronisation initiale
            if (empty($installments)) {
                $fs = new \App\Services\FinancialService($this->db);
                $fs->syncStudentFinancials($studentId, $academicYearId);

                // On recharge les tranches
                $stmtInst->execute([$studentId, $academicYearId]);
                $installments = $stmtInst->fetchAll(PDO::FETCH_ASSOC);
            }

            $remaining = $amount;
            $stmtAlloc = $this->db->prepare("
                INSERT INTO student_payment_allocations (student_payment_id, student_installment_id, amount_allocated)
                VALUES (?, ?, ?)
            ");
            $stmtUpdateInst = $this->db->prepare("
                UPDATE student_installments 
                SET amount_paid = amount_paid + ? 
                WHERE id = ?
            ");

            foreach ($installments as $inst) {
                if ($remaining <= 0)
                    break;

                $dueForInst = (float) $inst['amount_planned'] - (float) $inst['amount_paid'];
                if ($dueForInst > 0) {
                    $allocAmount = min($remaining, $dueForInst);

                    // Enregistrer l'allocation
                    $stmtAlloc->execute([$paymentId, (int) $inst['id'], $allocAmount]);

                    // Mettre à jour la tranche
                    $stmtUpdateInst->execute([$allocAmount, (int) $inst['id']]);

                    $remaining -= $allocAmount;
                }
            }

            // Si après répartition sur les tranches il reste un reliquat (sur-paiement), on l'alloue sur la dernière tranche
            if ($remaining > 0 && !empty($installments)) {
                $lastInst = end($installments);
                $stmtAlloc->execute([$paymentId, (int) $lastInst['id'], $remaining]);
                $stmtUpdateInst->execute([$remaining, (int) $lastInst['id']]);
            }

            // 4. Générer le reçu officiel
            $receiptNum = 'REC-' . date('Ymd', strtotime($paymentDate)) . '-' . sprintf('%04d', $paymentId);
            $vCode = bin2hex(random_bytes(16));

            $stmtReceipt = $this->db->prepare("
                INSERT INTO payment_receipts (student_payment_id, receipt_number, verification_code, print_count)
                VALUES (?, ?, ?, 0)
            ");
            $stmtReceipt->execute([$paymentId, $receiptNum, $vCode]);

            // Mettre à jour le code de vérification et le nombre d'impressions dans le reçu existant (legacy) pour compatibilité
            $stmtLegacyReceiptUpdate = $this->db->prepare("
                UPDATE payments 
                SET verification_code = ?, print_count = 0 
                WHERE id = ?
            ");
            $stmtLegacyReceiptUpdate->execute([$vCode, $paymentId]);

            if ($useTransaction) {
                $this->db->commit();
            }

            return $paymentId;
        } catch (\Exception $e) {
            if ($useTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Supprimer un versement (Soft delete).
     */
    public function delete(int $id, int $userId, string $motive): bool
    {
        $useTransaction = !$this->db->inTransaction();
        try {
            if ($useTransaction) {
                $this->db->beginTransaction();
            }

            $payment = $this->find($id);
            if (!$payment) {
                if ($useTransaction)
                    $this->db->rollBack();
                return false;
            }

            if (($payment['status'] ?? 'valide') === 'annule') {
                if ($useTransaction)
                    $this->db->rollBack();
                return false;
            }

            // Déduire les allocations des tranches de l'élève
            $allocations = $this->getAllocations($id);
            $stmtUpdateInst = $this->db->prepare("
                UPDATE student_installments 
                SET amount_paid = GREATEST(0.0, amount_paid - ?) 
                WHERE id = ?
            ");
            foreach ($allocations as $alloc) {
                $stmtUpdateInst->execute([(float) $alloc['amount_allocated'], (int) $alloc['student_installment_id']]);
            }

            // Marquer le versement comme annulé (Soft delete)
            $stmt = $this->db->prepare("UPDATE student_payments SET status = 'annule', cancelled_by = ?, cancelled_at = NOW(), cancellation_motive = ? WHERE id = ?");
            $stmt->execute([$userId, $motive, $id]);

            // Aussi annuler dans la table legacy `payments` via FinancialService pour garder la consistance
            $fs = new \App\Services\FinancialService($this->db);
            $fs->cancelPayment($id, $userId, $motive);

            if ($useTransaction) {
                $this->db->commit();
            }
            return true;
        } catch (\Exception $e) {
            if ($useTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Incrémenter le nombre d'impressions du reçu.
     */
    public function incrementPrintCount(int $paymentId): int
    {
        $stmt = $this->db->prepare("UPDATE payment_receipts SET print_count = print_count + 1 WHERE student_payment_id = ?");
        $stmt->execute([$paymentId]);

        // Sync and get count
        $stmtGet = $this->db->prepare("SELECT print_count FROM payment_receipts WHERE student_payment_id = ?");
        $stmtGet->execute([$paymentId]);
        $count = (int) $stmtGet->fetchColumn();

        // Sync into legacy `payments`
        $stmtLegacy = $this->db->prepare("UPDATE payments SET print_count = ? WHERE id = ?");
        $stmtLegacy->execute([$count, $paymentId]);

        return $count;
    }
}
