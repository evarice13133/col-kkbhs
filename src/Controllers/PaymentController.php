<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Core\PermissionManager;
use App\Services\AcademicYearService;
use App\Services\FinancialService;
use App\Models\StudentPayment;
use PDO;

/**
 * PaymentController
 * 
 * Gère les paiements d'inscription et de scolarité, ainsi que l'état financier des élèves.
 */
class PaymentController
{
    private PDO $db;
    private AcademicYearService $academicYearService;
    private FinancialService $financialService;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->academicYearService = new AcademicYearService($this->db);
        $this->financialService = new FinancialService($this->db);

        // Sécurité RBAC : Accès réservé aux rôles financiers
        PermissionManager::requirePermission('manage_payments');
    }

    /**
     * Liste et filtre l'état financier des élèves.
     */
    public function index()
    {
        $activeYearId = $this->academicYearService->getActiveYearId();

        $search = trim((string) ($_GET['q'] ?? ''));
        $classId = (int) ($_GET['class_id'] ?? 0);
        $status = trim((string) ($_GET['status'] ?? '')); // 'paid', 'unpaid', 'debt' (reste à payer > 0)

        // Récupérer les classes pour le sélecteur
        $classes = $this->db->query("SELECT id, nom FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        // Requête principale
        $sql = "SELECT s.id, s.nom, s.prenom, s.email as matricule, c.nom as classe_nom, 
                       e.frais_scolarite_brut, e.total_reductions, e.total_bourses, e.total_paye, e.reste_a_payer,
                       (e.frais_scolarite_brut - e.total_reductions - e.total_bourses) as scolarite_nette
                FROM students s
                JOIN enrollments e ON s.id = e.student_id AND e.academic_year_id = ?
                LEFT JOIN classes c ON e.class_id = c.id
                WHERE s.is_withdrawn = 0 AND s.actif = 1";

        $params = [$activeYearId];

        if ($search !== '') {
            $sql .= " AND (s.nom LIKE ? OR s.prenom LIKE ? OR s.email LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        if ($classId > 0) {
            $sql .= " AND e.class_id = ?";
            $params[] = $classId;
        }

        if ($status === 'paid') {
            $sql .= " AND e.reste_a_payer <= 0 AND (e.frais_scolarite_brut - e.total_reductions - e.total_bourses) > 0";
        } elseif ($status === 'unpaid') {
            $sql .= " AND e.total_paye = 0 AND (e.frais_scolarite_brut - e.total_reductions - e.total_bourses) > 0";
        } elseif ($status === 'debt') {
            $sql .= " AND e.reste_a_payer > 0";
        }

        $sql .= " ORDER BY c.nom ASC, s.nom ASC, s.prenom ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/payments/index.php';
    }

    /**
     * Fiche financière détaillée d'un élève.
     */
    public function studentDetails($id)
    {
        $id = (int) $id;
        $activeYearId = $this->academicYearService->getActiveYearId();

        // 1. Récupérer l'élève, sa classe et son enrollment
        $stmt = $this->db->prepare("SELECT s.id, s.nom, s.prenom, s.email as matricule, c.id as class_id, c.nom as classe_nom, c.frais_inscription as class_frais_inscription,
                                           e.frais_scolarite_brut, e.total_reductions, e.total_bourses, e.total_paye, e.reste_a_payer
                                    FROM students s
                                    LEFT JOIN enrollments e ON s.id = e.student_id AND e.academic_year_id = ?
                                    LEFT JOIN classes c ON s.class_id = c.id
                                    WHERE s.id = ? AND s.is_withdrawn = 0 AND s.actif = 1");
        $stmt->execute([$activeYearId, $id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            Session::setFlash('error', "Élève introuvable ou retiré.");
            header("Location: /payments");
            exit;
        }

        // Scolarité nette
        $student['scolarite_nette'] = max(0.0, $student['frais_scolarite_brut'] - $student['total_reductions'] - $student['total_bourses']);

        // 2. Récupérer les tranches planifiées (student_installments)
        $stmt = $this->db->prepare("SELECT * FROM student_installments WHERE student_id = ? AND academic_year_id = ? ORDER BY installment_number ASC");
        $stmt->execute([$id, $activeYearId]);
        $installments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Récupérer l'historique des paiements de cet élève
        $stmt = $this->db->prepare("SELECT p.*, u.nom as user_nom, u.prenom as user_prenom 
                                    FROM payments p
                                    LEFT JOIN users u ON p.created_by = u.id
                                    WHERE p.student_id = ? AND p.academic_year_id = ?
                                    ORDER BY p.payment_date DESC, p.id DESC");
        $stmt->execute([$id, $activeYearId]);
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculer les totaux par type pour l'historique
        $totalPaidRegistration = 0.0;
        $totalPaidTuition = 0.0;
        foreach ($payments as $p) {
            if ($p['status'] === 'annule') {
                continue;
            }
            if ($p['type'] === 'inscription') {
                $totalPaidRegistration += (float) $p['amount'];
            } else {
                $totalPaidTuition += (float) $p['amount'];
            }
        }

        include __DIR__ . '/../Views/payments/details.php';
    }

    /**
     * Enregistre un paiement.
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', "Session expirée ou requête invalide.");
                header("Location: /payments");
                exit;
            }

            $studentId = (int) $_POST['student_id'];
            $type = trim((string) ($_POST['type'] ?? 'scolarite'));
            $amount = (float) $_POST['amount'];
            $payment_date = trim((string) ($_POST['payment_date'] ?? date('Y-m-d')));
            $payment_method = trim((string) ($_POST['payment_method'] ?? 'CASH'));
            if (\App\Services\PaymentReferenceGenerator::isCashMethod($payment_method)) {
                $refGen = new \App\Services\PaymentReferenceGenerator($this->db);
                $reference = $refGen->generateUniqueReference();
            } else {
                $reference = trim((string) ($_POST['reference'] ?? ''));
            }
            $commentaire = trim((string) ($_POST['commentaire'] ?? ''));

            $activeYearId = $this->academicYearService->getActiveYearId();

            if ($amount <= 0.0) {
                Session::setFlash('error', "Le montant du paiement doit être supérieur à 0.");
                header("Location: /payments/student?id=" . $studentId);
                exit;
            }

            // Validation du montant par rapport au solde restant
            $balance = 0.0;
            if ($type === 'inscription') {
                $stmtEnroll = $this->db->prepare("SELECT e.student_status, c.frais_inscription, c.frais_inscription_reinscription FROM enrollments e JOIN classes c ON e.class_id = c.id WHERE e.student_id = ? AND e.academic_year_id = ?");
                $stmtEnroll->execute([$studentId, $activeYearId]);
                $enrollData = $stmtEnroll->fetch(PDO::FETCH_ASSOC);

                $expectedFee = 0.0;
                if ($enrollData) {
                    $expectedFee = ($enrollData['student_status'] === 'nouveau') ? (float) $enrollData['frais_inscription'] : (float) $enrollData['frais_inscription_reinscription'];
                }

                $stmtPaid = $this->db->prepare("SELECT SUM(amount) FROM payments WHERE student_id = ? AND academic_year_id = ? AND type = 'inscription' AND status = 'valide'");
                $stmtPaid->execute([$studentId, $activeYearId]);
                $paidRegistration = (float) $stmtPaid->fetchColumn();

                $balance = max(0.0, $expectedFee - $paidRegistration);
            } else {
                $stmtEnroll = $this->db->prepare("SELECT reste_a_payer FROM enrollments WHERE student_id = ? AND academic_year_id = ?");
                $stmtEnroll->execute([$studentId, $activeYearId]);
                $balance = (float) $stmtEnroll->fetchColumn();
            }

            if ($amount > $balance) {
                Session::setFlash('error', "Montant supérieur au solde restant de l'élève.");
                header("Location: /payments/student?id=" . $studentId);
                exit;
            }

            try {
                $this->db->beginTransaction();

                $redirectUrl = "/payments/student?id=" . $studentId;

                if ($type === 'inscription') {
                    // Calcul du frais attendu
                    $stmtEnroll = $this->db->prepare("SELECT e.student_status, c.frais_inscription, c.frais_inscription_reinscription FROM enrollments e JOIN classes c ON e.class_id = c.id WHERE e.student_id = ? AND e.academic_year_id = ?");
                    $stmtEnroll->execute([$studentId, $activeYearId]);
                    $enrollData = $stmtEnroll->fetch(PDO::FETCH_ASSOC);

                    $expectedFee = 0;
                    if ($enrollData) {
                        $expectedFee = ($enrollData['student_status'] === 'nouveau') ? (float) $enrollData['frais_inscription'] : (float) $enrollData['frais_inscription_reinscription'];
                    }

                    $amountInscription = min($amount, $expectedFee);
                    $surplus = max(0.0, $amount - $expectedFee);

                    if ($expectedFee <= 0) {
                        $amountInscription = 0;
                        $surplus = $amount;
                    }

                    if ($amountInscription > 0 || $amount == 0) {
                        $stmt = $this->db->prepare("INSERT INTO payments (student_id, academic_year_id, amount, type, payment_date, payment_method, reference, commentaire, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$studentId, $activeYearId, $amountInscription, 'inscription', $payment_date, $payment_method, $reference ?: null, $commentaire ?: null, Session::get('user_id')]);
                        $paymentId = (int) $this->db->lastInsertId();

                        $this->financialService->logHistory(Session::get('user_id'), 'payment', $paymentId, 'create', null, [
                            'student_id' => $studentId,
                            'type' => 'inscription',
                            'amount' => $amountInscription,
                            'payment_date' => $payment_date,
                            'payment_method' => $payment_method,
                            'reference' => $reference
                        ]);

                        $redirectUrl = "/payments/receipt?id=" . $paymentId;
                    }

                    if ($surplus > 0) {
                        $surplusRef = $reference ? $reference . ' (Surplus)' : 'Surplus Inscription';
                        
                        $studentPaymentModel = new StudentPayment();
                        $surplusId = $studentPaymentModel->create([
                            'student_id' => $studentId,
                            'academic_year_id' => $activeYearId,
                            'amount' => $surplus,
                            'payment_date' => $payment_date,
                            'payment_method' => $payment_method,
                            'reference' => $surplusRef,
                            'observation' => 'Transfert automatique du surplus d\'inscription',
                            'created_by' => Session::get('user_id'),
                            'parent_payment_id' => $paymentId ?? null
                        ]);

                        $this->financialService->logHistory(Session::get('user_id'), 'student_payment', $surplusId, 'create', null, [
                            'student_id' => $studentId,
                            'type' => 'scolarite',
                            'amount' => $surplus,
                            'payment_date' => $payment_date,
                            'payment_method' => $payment_method,
                            'reference' => $surplusRef,
                            'parent_payment_id' => $paymentId ?? null
                        ]);

                        $redirectUrl = "/school_fees/receipt?id=" . $surplusId . "&back=student&student_id=" . $studentId;
                    }
                } else {
                    // Paiement standard scolarite via StudentPayment
                    $studentPaymentModel = new StudentPayment();
                    $paymentId = $studentPaymentModel->create([
                        'student_id' => $studentId,
                        'academic_year_id' => $activeYearId,
                        'amount' => $amount,
                        'payment_date' => $payment_date,
                        'payment_method' => $payment_method,
                        'reference' => $reference,
                        'observation' => $commentaire ?: null,
                        'created_by' => Session::get('user_id')
                    ]);

                    $this->financialService->logHistory(Session::get('user_id'), 'student_payment', $paymentId, 'create', null, [
                        'student_id' => $studentId,
                        'type' => 'scolarite',
                        'amount' => $amount,
                        'payment_date' => $payment_date,
                        'payment_method' => $payment_method,
                        'reference' => $reference
                    ]);

                    $redirectUrl = "/school_fees/receipt?id=" . $paymentId . "&back=student&student_id=" . $studentId;
                }

                // Synchronisation des finances de l'élève
                $this->financialService->syncStudentFinancials($studentId, $activeYearId);

                $this->db->commit();
                Session::setFlash('success', "Paiement de " . number_format($amount, 0, '.', ' ') . " FCFA enregistré avec succès.");
            } catch (\Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                Session::setFlash('error', "Erreur lors de l'enregistrement : " . $e->getMessage());
            }

            header("Location: " . $redirectUrl);
            exit;
        }
    }

    /**
     * Annule un paiement.
     */
    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', "Session expirée ou requête invalide.");
                header("Location: /payments");
                exit;
            }

            $motive = trim($_POST['motive'] ?? '');
            if (empty($motive)) {
                Session::setFlash('error', "Le motif d'annulation est obligatoire.");
                header("Location: /payments");
                exit;
            }

            $id = (int) $id;

            // Récupérer le type de paiement pour aiguiller correctement l'annulation
            $stmt = $this->db->prepare("SELECT type, student_id FROM payments WHERE id = ?");
            $stmt->execute([$id]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment) {
                Session::setFlash('error', "Paiement introuvable.");
                header("Location: /payments");
                exit;
            }

            if ($payment['type'] === 'scolarite') {
                $studentPaymentModel = new StudentPayment();
                $success = $studentPaymentModel->delete($id, Session::get('user_id'), $motive);
                if ($success) {
                    Session::setFlash('success', "Versement annulé avec succès.");
                } else {
                    Session::setFlash('error', "Impossible d'annuler ce versement.");
                }
                header("Location: /payments/student?id=" . $payment['student_id']);
            } else {
                $result = $this->financialService->cancelPayment($id, Session::get('user_id'), $motive);
                if ($result['success']) {
                    Session::setFlash('success', $result['message']);
                    header("Location: /payments/student?id=" . $result['student_id']);
                } else {
                    Session::setFlash('error', $result['message']);
                    header("Location: /payments");
                }
            }
            exit;
        } else {
            // Si c'est un GET, rediriger
            header("Location: /payments");
            exit;
        }
    }

    /**
     * Génère un reçu de paiement imprimable.
     */
    public function receipt($id)
    {
        $id = (int) $id;

        // Récupérer le paiement et les détails de l'élève
        $stmt = $this->db->prepare("SELECT p.*, s.nom as student_nom, s.prenom as student_prenom, s.email as matricule, s.sexe, s.date_naissance, s.lieu_naissance, s.adresse,
                                           c.nom as classe_nom, c.frais_inscription, c.frais_inscription_reinscription, c.frais_scolarite_brut, 
                                           u.nom as user_nom, u.prenom as user_prenom
                                    FROM payments p
                                    JOIN students s ON p.student_id = s.id
                                    LEFT JOIN classes c ON s.class_id = c.id
                                    LEFT JOIN users u ON p.created_by = u.id
                                    WHERE p.id = ?");
        $stmt->execute([$id]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$payment) {
            Session::setFlash('error', "Reçu introuvable.");
            header("Location: /payments");
            exit;
        }

        // Si le versement est de type scolarite, utiliser le reçu officiel de scolarité
        if ($payment['type'] === 'scolarite') {
            header("Location: /school_fees/receipt?id=" . $id . "&back=student&student_id=" . $payment['student_id']);
            exit;
        }

        // 1. Générer le code de vérification s'il n'existe pas encore
        if (empty($payment['verification_code'])) {
            $code = 'REC-' . strtoupper(implode('-', str_split(bin2hex(random_bytes(6)), 4)));
            while (true) {
                $chk = $this->db->prepare("SELECT COUNT(*) FROM payments WHERE verification_code = ?");
                $chk->execute([$code]);
                if ($chk->fetchColumn() == 0) {
                    break;
                }
                $code = 'REC-' . strtoupper(implode('-', str_split(bin2hex(random_bytes(6)), 4)));
            }
            $upCode = $this->db->prepare("UPDATE payments SET verification_code = ? WHERE id = ?");
            $upCode->execute([$code, $payment['id']]);
            $payment['verification_code'] = $code;
        }

        // 1.5. Récupérer l'éventuel surplus généré (enfant)
        $childSurplus = null;
        if ($payment['type'] === 'inscription') {
            $stmtSurplus = $this->db->prepare("SELECT * FROM payments WHERE parent_payment_id = ? AND status != 'annule'");
            $stmtSurplus->execute([$payment['id']]);
            $childSurplus = $stmtSurplus->fetch(PDO::FETCH_ASSOC);
        }

        // 2. Récupérer les totaux payés par l'élève pour cette année (scolarité ou inscription)
        $stmt = $this->db->prepare("SELECT student_status, reste_a_payer, total_paye, total_reductions, total_bourses,
                                           (frais_scolarite_brut - total_reductions - total_bourses) as scolarite_nette
                                    FROM enrollments WHERE student_id = ? AND academic_year_id = ?");
        $stmt->execute([$payment['student_id'], $payment['academic_year_id']]);
        $enroll = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3. Incrémenter le compteur d'impression si c'est la vue HTML standard (pas PDF ni Ajax)
        $isPdf = isset($_GET['pdf']) && $_GET['pdf'] == 1;
        if (!$isPdf && (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest')) {
            $newPrintCount = (int) $payment['print_count'] + 1;
            $up = $this->db->prepare("UPDATE payments SET print_count = ? WHERE id = ?");
            $up->execute([$newPrintCount, $payment['id']]);

            // Log d'audit financier
            $this->financialService->logHistory(
                Session::get('user_id'),
                'payment',
                $id,
                'print',
                (string) $payment['print_count'],
                (string) $newPrintCount
            );

            $payment['print_count'] = $newPrintCount;
        }

        // 4. Charger l'historique d'impression pour la vue d'administration
        $stmtLogs = $this->db->prepare("
            SELECT fh.*, u.nom as user_nom, u.prenom as user_prenom 
            FROM financial_history fh 
            LEFT JOIN users u ON fh.user_id = u.id 
            WHERE fh.entity_type = 'payment' AND fh.entity_id = ? AND fh.action = 'print' 
            ORDER BY fh.event_date DESC
        ");
        $stmtLogs->execute([$id]);
        $printLogs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

        // 5. Récupérer les informations de l'établissement (Settings)
        $settingsStore = new \App\Services\SettingsStore($this->db);
        $settings = $settingsStore->all();
        $school_name = $settings['school_name'] ?? 'NotesMaster';

        // 6. Traduction du montant en lettres
        $amountInWords = \App\Core\NumberToWords::toWords($payment['amount']);

        // 7. Rendu PDF si demandé
        if ($isPdf) {
            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'Helvetica');

            $dompdf = new \Dompdf\Dompdf($options);

            ob_start();
            include __DIR__ . '/../Views/payments/receipt.php';
            $html = ob_get_clean();

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');

            try {
                $dompdf->render();
                $dompdf->stream("Recu_" . $payment['id'] . ".pdf", ['Attachment' => false]);
                exit;
            } catch (\Throwable $e) {
                echo "Erreur de génération PDF: " . $e->getMessage();
                exit;
            }
        }

        include __DIR__ . '/../Views/payments/receipt.php';
    }

    /**
     * Page publique de vérification de reçu de versement (Scan QR Code).
     */
    public function verify()
    {
        $code = trim((string) ($_GET['code'] ?? ''));

        $payment = null;
        $enroll = null;

        if ($code !== '') {
            // Rechercher le paiement par le code de vérification
            $stmt = $this->db->prepare("
                SELECT p.*, s.nom as student_nom, s.prenom as student_prenom, s.email as matricule, s.sexe, s.date_naissance, s.lieu_naissance, s.adresse,
                       c.nom as classe_nom, u.nom as user_nom, u.prenom as user_prenom
                FROM payments p
                JOIN students s ON p.student_id = s.id
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN users u ON p.created_by = u.id
                WHERE p.verification_code = ?
            ");
            $stmt->execute([$code]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($payment) {
                // Récupérer l'inscription correspondante
                $stmt = $this->db->prepare("
                    SELECT student_status, reste_a_payer, total_paye, 
                           (frais_scolarite_brut - total_reductions - total_bourses) as scolarite_nette
                    FROM enrollments WHERE student_id = ? AND academic_year_id = ?
                ");
                $stmt->execute([$payment['student_id'], $payment['academic_year_id']]);
                $enroll = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }

        // Charger les settings de l'école
        $settingsStore = new \App\Services\SettingsStore($this->db);
        $settings = $settingsStore->all();

        include __DIR__ . '/../Views/payments/verify.php';
    }
}
