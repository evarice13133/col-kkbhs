<?php

namespace App\Controllers;

use App\Core\Database;
use App\Models\ReceiptVerification;
use App\Services\SettingsStore;
use PDO;

class PublicVerificationController
{
    private PDO $db;

    public function __construct()
    {
        // No RBAC checks here! This is purely public.
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Page publique de vérification de reçu de versement (Scan QR Code).
     */
    public function verifyPublic()
    {
        // On récupère le token soit depuis la query string (rétrocompatibilité),
        // soit depuis le path /verify-receipt/{token}
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (strpos($path, '/verify-receipt/') === 0) {
            $code = str_replace('/verify-receipt/', '', $path);
        } else {
            $code = trim((string) ($_GET['code'] ?? ''));
        }

        // On enlève les potentiels paramètres de query s'il y en a dans le path str_replace
        $code = explode('?', $code)[0];
        $code = trim(urldecode($code));

        $payment = null;
        $enroll = null;
        $isValid = 0;
        $errorCase = null;
        $receiptType = null;
        $paymentId = null;
        $studentId = null;
        $academicYearId = null;

        if ($code !== '') {
            // Rechercher le paiement par le code de vérification (table legacy payments)
            $stmt = $this->db->prepare("
                SELECT p.*, s.class_id, s.nom as student_nom, s.prenom as student_prenom, s.email as matricule, s.sexe, s.date_naissance, s.lieu_naissance, s.adresse,
                       c.nom as classe_nom, u.nom as user_nom, u.prenom as user_prenom,
                       ay.nom as annee_scolaire
                FROM payments p
                JOIN students s ON p.student_id = s.id
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN users u ON p.created_by = u.id
                LEFT JOIN academic_years ay ON p.academic_year_id = ay.id
                WHERE p.verification_code = ?
            ");
            $stmt->execute([$code]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($payment) {
                $isValid = 1;
                $receiptType = $payment['type'] ?? 'inscription';
                $paymentId = $payment['id'];
                $studentId = $payment['student_id'];
                $academicYearId = $payment['academic_year_id'];
                
                // Récupérer l'inscription correspondante
                $stmt = $this->db->prepare("
                    SELECT student_status, reste_a_payer, total_paye, frais_scolarite_brut, total_reductions, total_bourses,
                           (frais_scolarite_brut - total_reductions - total_bourses) as scolarite_nette
                    FROM enrollments WHERE student_id = ? AND academic_year_id = ?
                ");
                $stmt->execute([$studentId, $academicYearId]);
                $enroll = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                // Rechercher dans payment_receipts -> student_payments (Scolarité nouveau format)
                $stmt2 = $this->db->prepare("
                    SELECT pr.*, sp.*, s.class_id, s.nom as student_nom, s.prenom as student_prenom, s.email as matricule, s.sexe, s.date_naissance, s.lieu_naissance, s.adresse,
                           c.nom as classe_nom, u.nom as user_nom, u.prenom as user_prenom,
                           pr.verification_code,
                           ay.nom as annee_scolaire
                    FROM payment_receipts pr
                    JOIN student_payments sp ON pr.student_payment_id = sp.id
                    JOIN students s ON sp.student_id = s.id
                    LEFT JOIN classes c ON s.class_id = c.id
                    LEFT JOIN users u ON sp.created_by = u.id
                    LEFT JOIN academic_years ay ON sp.academic_year_id = ay.id
                    WHERE pr.verification_code = ?
                ");
                $stmt2->execute([$code]);
                $payment2 = $stmt2->fetch(PDO::FETCH_ASSOC);

                if ($payment2) {
                    $payment = $payment2;
                    $payment['type'] = 'scolarite'; // Forcer le type pour l'affichage
                    
                    $isValid = 1;
                    $receiptType = 'scolarite';
                    $paymentId = $payment['student_payment_id'];
                    $studentId = $payment['student_id'];
                    $academicYearId = $payment['academic_year_id'];

                    // Récupérer l'inscription correspondante
                    $stmt = $this->db->prepare("
                        SELECT student_status, reste_a_payer, total_paye, frais_scolarite_brut, total_reductions, total_bourses,
                               (frais_scolarite_brut - total_reductions - total_bourses) as scolarite_nette
                        FROM enrollments WHERE student_id = ? AND academic_year_id = ?
                    ");
                    $stmt->execute([$studentId, $academicYearId]);
                    $enroll = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $errorCase = 'not_found';
                }
            }
        } else {
            $errorCase = 'missing_code';
        }

        // Si le reçu est annulé, on modifie le statut
        if ($payment && isset($payment['status']) && $payment['status'] === 'annule') {
            $isValid = 0;
            $errorCase = 'cancelled';
        }

        // Enregistrer la tentative de vérification
        if ($code !== '') {
            try {
                $verifier = new ReceiptVerification();
                $verifier->logVerification([
                    'verification_code' => $code,
                    'payment_id' => $paymentId,
                    'receipt_type' => $receiptType,
                    'student_id' => $studentId,
                    'academic_year_id' => $academicYearId,
                    'is_valid' => $isValid,
                    'error_case' => $errorCase
                ]);
            } catch (\Exception $e) {
                // Ignore silent fail for tracking if DB issue
            }
        }

        $paymentHistory = [];
        $installments = [];
        $lastPayment = null;
        
        if ($isValid && $studentId && $academicYearId) {
            // Historique des paiements
            $stmtHist = $this->db->prepare("
                SELECT 'scolarite' as type, sp.amount, sp.payment_date, sp.payment_method, sp.id, sp.reference, sp.created_at, pr.verification_code
                FROM student_payments sp
                LEFT JOIN payment_receipts pr ON pr.student_payment_id = sp.id
                WHERE sp.student_id = ? AND sp.academic_year_id = ? AND sp.status != 'annule'
                ORDER BY sp.payment_date DESC, sp.created_at DESC
            ");
            $stmtHist->execute([$studentId, $academicYearId]);
            $paymentHistory = $stmtHist->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($paymentHistory)) {
                $lastPayment = $paymentHistory[0];
            }

            // Échéancier (Tranches)
            $classId = $payment['class_id'] ?? 0;
            $stmtInst = $this->db->prepare("
                SELECT si.id, si.amount_planned as amount_expected, si.amount_paid, 
                       IFNULL(
                           (SELECT deadline_date FROM installment_deadlines idl WHERE idl.academic_year_id = si.academic_year_id AND idl.class_id = ? AND idl.installment_number = si.installment_number LIMIT 1),
                           (SELECT deadline_date FROM fee_installments f WHERE f.academic_year_id = si.academic_year_id AND f.installment_order = si.installment_number LIMIT 1)
                       ) as deadline,
                       CONCAT('Tranche ', si.installment_number) as tranche_name
                FROM student_installments si
                WHERE si.student_id = ? AND si.academic_year_id = ?
                ORDER BY si.installment_number ASC
            ");
            $stmtInst->execute([$classId, $studentId, $academicYearId]);
            $installments = $stmtInst->fetchAll(PDO::FETCH_ASSOC);
            
            // Format fallback si deadline toujours null
            foreach ($installments as &$inst) {
                if (empty($inst['deadline'])) {
                    $inst['deadline'] = date('Y-m-d');
                }
            }
            unset($inst);
        }

        // Charger les settings de l'école
        $settingsStore = new SettingsStore($this->db);
        $settings = $settingsStore->all();

        // Gestion des actions spéciales (Voir, PDF, Imprimer) pour un reçu valide
        if ($isValid && isset($_GET['action'])) {
            $action = $_GET['action'];
            if (in_array($action, ['view', 'pdf', 'print'])) {
                $viewFile = ($receiptType === 'inscription') ? '/../Views/payments/receipt.php' : '/../Views/school_fees/receipt.php';
                
                // Indiquer que c'est une vue publique/parent pour cacher l'audit
                $isPublicView = true;
                $isPdf = ($action === 'pdf');
                $printLogs = []; // Pas de log d'impression visible en public
                
                ob_start();
                include __DIR__ . $viewFile;
                $html = ob_get_clean();
                
                if ($action === 'pdf') {
                    require_once __DIR__ . '/../../vendor/autoload.php';
                    $options = new \Dompdf\Options();
                    $options->set('isHtml5ParserEnabled', true);
                    $options->set('isRemoteEnabled', true);
                    // Autoriser chroot pour accéder au logo localement si nécessaire
                    $options->set('chroot', realpath(__DIR__ . '/../../public'));
                    
                    $dompdf = new \Dompdf\Dompdf($options);
                    $dompdf->loadHtml($html);
                    $dompdf->setPaper('A4', 'portrait');
                    $dompdf->render();
                    $receiptNumber = $payment['receipt_number'] ?? $payment['id'];
                    $dompdf->stream("Recu_" . $receiptNumber . ".pdf", ["Attachment" => true]);
                    exit;
                } else {
                    echo $html;
                    if ($action === 'print') {
                        echo '<script>window.onload = function() { window.print(); }</script>';
                    }
                    exit;
                }
            }
        }

        include __DIR__ . '/../Views/public/verify_receipt.php';
    }
}
