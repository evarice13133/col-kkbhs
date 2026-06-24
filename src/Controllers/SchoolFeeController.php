<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Core\PermissionManager;
use App\Services\AcademicYearService;
use App\Services\FinancialService;
use App\Models\SchoolFee;
use App\Models\FeeInstallment;
use App\Models\StudentPayment;
use App\Models\InsolventStudent;
use PDO;

class SchoolFeeController
{
    private PDO $db;
    private AcademicYearService $academicYearService;
    private FinancialService $financialService;
    private SchoolFee $schoolFeeModel;
    private FeeInstallment $feeInstallmentModel;
    private StudentPayment $studentPaymentModel;
    private InsolventStudent $insolventStudentModel;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->academicYearService = new AcademicYearService($this->db);
        $this->financialService = new FinancialService($this->db);
        $this->schoolFeeModel = new SchoolFee();
        $this->feeInstallmentModel = new FeeInstallment();
        $this->studentPaymentModel = new StudentPayment();
        $this->insolventStudentModel = new InsolventStudent();

        // Sécurité RBAC : Accès réservé aux rôles financiers
        PermissionManager::requirePermission('manage_fees');
    }

    /**
     * Grille de scolarité
     */
    public function grille()
    {
        $activeYearId = $this->academicYearService->getActiveYearId();

        // Filtres
        $teachingTypeId = (int)($_GET['teaching_type_id'] ?? 0);
        $cycleId = (int)($_GET['cycle_id'] ?? 0);
        $sectionId = (int)($_GET['section_id'] ?? 0);
        $classId = (int)($_GET['class_id'] ?? 0);

        // Données des sélecteurs
        $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        $classesQuery = "SELECT id, nom, cycle_id, section_id, teaching_type_id FROM classes WHERE 1=1";
        $classesParams = [];
        if ($teachingTypeId) {
            $classesQuery .= " AND teaching_type_id = ?";
            $classesParams[] = $teachingTypeId;
        }
        if ($cycleId) {
            $classesQuery .= " AND cycle_id = ?";
            $classesParams[] = $cycleId;
        }
        if ($sectionId) {
            $classesQuery .= " AND section_id = ?";
            $classesParams[] = $sectionId;
        }
        $classesQuery .= " ORDER BY nom ASC";
        
        $stmtClasses = $this->db->prepare($classesQuery);
        $stmtClasses->execute($classesParams);
        $allClasses = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

        // Traiter chaque classe pour récupérer les frais et tranches
        $grilleData = [];
        foreach ($allClasses as $class) {
            if ($classId && (int)$class['id'] !== $classId) {
                continue;
            }

            // Résoudre les frais
            $resolvedAmount = $this->schoolFeeModel->resolveAmount($activeYearId, (int)$class['id']);
            
            // Résoudre les tranches
            $tranches = $this->feeInstallmentModel->resolveInstallments($activeYearId, (int)$class['id']);
            
            // Récupérer les frais d'inscription depuis la classe
            $stmtC = $this->db->prepare("SELECT frais_inscription, frais_inscription_reinscription FROM classes WHERE id = ?");
            $stmtC->execute([$class['id']]);
            $cDetails = $stmtC->fetch(PDO::FETCH_ASSOC);

            $grilleData[] = [
                'class_name' => $class['nom'],
                'frais_inscription_nouveau' => (float)$cDetails['frais_inscription'],
                'frais_inscription_ancien' => (float)$cDetails['frais_inscription_reinscription'],
                'frais_scolarite_brut' => $resolvedAmount,
                'nbr_tranches' => count($tranches),
                'tranches' => $tranches
            ];
        }

        include __DIR__ . '/../Views/school_fees/grille.php';
    }

    /**
     * Gestion des tranches de scolarité
     */
    public function tranches()
    {
        $activeYearId = $this->academicYearService->getActiveYearId();

        // AJAX endpoint to get tranches and tuition amount for a target
        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            $targetType = $_GET['target_type'] ?? '';
            $targetId = (int)($_GET['target_id'] ?? 0);
            
            $tuitionAmount = 0.0;
            $installments = [];
            $inherited = false;
            $inheritedFrom = __('no_config');

            if ($targetId > 0) {
                // 1. Get tuition amount
                if ($targetType === 'class') {
                    $tuitionAmount = $this->schoolFeeModel->resolveAmount($activeYearId, $targetId);
                } else {
                    $stmt = $this->db->prepare("SELECT amount FROM school_fees WHERE academic_year_id = ? AND " . $targetType . "_id = ?");
                    $stmt->execute([$activeYearId, $targetId]);
                    $tuitionAmount = (float)($stmt->fetchColumn() ?: 0.0);
                }

                // 2. Get installments
                // Try specific first
                $stmt = $this->db->prepare("
                    SELECT name, amount, deadline_date 
                    FROM fee_installments 
                    WHERE academic_year_id = ? AND " . $targetType . "_id = ?
                    ORDER BY installment_order ASC
                ");
                $stmt->execute([$activeYearId, $targetId]);
                $installments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($installments)) {
                    $inherited = false;
                    $inheritedFrom = __('specific_config');
                } else {
                    // Try to resolve (if class)
                    if ($targetType === 'class') {
                        $resolved = $this->feeInstallmentModel->resolveInstallments($activeYearId, $targetId);
                        if (!empty($resolved)) {
                            // Check the source type
                            $source = $resolved[0]['source_type'] ?? 'default';
                            if ($source === 'cycle') {
                                $inherited = true;
                                $inheritedFrom = __('inherited_cycle');
                                $installments = $resolved;
                            } elseif ($source === 'teaching_type') {
                                $inherited = true;
                                $inheritedFrom = __('inherited_teaching_type');
                                $installments = $resolved;
                            } elseif ($source === 'legacy') {
                                $inherited = true;
                                $inheritedFrom = __('inherited_legacy');
                                $installments = $resolved;
                            } elseif ($source === 'default') {
                                $inherited = true;
                                $inheritedFrom = __('default_single_installment');
                                $installments = $resolved;
                            }
                        }
                    }
                }
            }

            header('Content-Type: application/json');
            echo json_encode([
                'tuition_amount' => $tuitionAmount,
                'installments' => $installments,
                'inherited' => $inherited,
                'inherited_from' => $inheritedFrom
            ]);
            exit;
        }

        // Si soumission du formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', "Jeton CSRF invalide.");
                header("Location: /school_fees/tranches");
                exit;
            }

            $targetType = $_POST['target_type'] ?? ''; // 'class', 'cycle', 'teaching_type'
            $targetId = (int)($_POST['target_id'] ?? 0);
            
            $trancheNames = $_POST['tranche_name'] ?? [];
            $trancheAmounts = $_POST['tranche_amount'] ?? [];
            $trancheDeadlines = $_POST['tranche_deadline'] ?? [];

            if ($targetId <= 0 || empty($trancheNames)) {
                Session::setFlash('error', "Veuillez remplir les informations requises.");
                header("Location: /school_fees/tranches");
                exit;
            }

            try {
                $this->db->beginTransaction();

                $classId = ($targetType === 'class') ? $targetId : null;
                $cycleId = ($targetType === 'cycle') ? $targetId : null;
                $teachingTypeId = ($targetType === 'teaching_type') ? $targetId : null;

                // Vider les anciennes tranches pour ce groupe
                $this->feeInstallmentModel->deleteByGroup($activeYearId, $classId, $cycleId, $teachingTypeId);

                // Insérer les nouvelles tranches
                for ($i = 0; $i < count($trancheNames); $i++) {
                    if (empty($trancheNames[$i]) || $trancheAmounts[$i] <= 0) continue;

                    $this->feeInstallmentModel->create([
                        'academic_year_id' => $activeYearId,
                        'name' => $trancheNames[$i],
                        'installment_order' => $i + 1,
                        'amount' => (float)$trancheAmounts[$i],
                        'deadline_date' => $trancheDeadlines[$i],
                        'class_id' => $classId,
                        'cycle_id' => $cycleId,
                        'teaching_type_id' => $teachingTypeId
                    ]);
                }

                // Si c'est configuré pour une classe spécifique, on synchronise avec la table legacy class_installments
                if ($classId) {
                    $delLegacy = $this->db->prepare("DELETE FROM class_installments WHERE class_id = ?");
                    $delLegacy->execute([$classId]);

                    $insLegacy = $this->db->prepare("INSERT INTO class_installments (class_id, installment_number, amount) VALUES (?, ?, ?)");
                    $updClass = $this->db->prepare("UPDATE classes SET nbr_tranches = ?, frais_scolarite_brut = ? WHERE id = ?");
                    
                    $totalScolarite = 0.0;
                    $nbrTr = 0;
                    for ($i = 0; $i < count($trancheNames); $i++) {
                        if (empty($trancheNames[$i]) || $trancheAmounts[$i] <= 0) continue;
                        $amt = (float)$trancheAmounts[$i];
                        $insLegacy->execute([$classId, $i + 1, $amt]);
                        
                        // Enregistrer dans installment_deadlines pour que le cache puisse les récupérer
                        $this->db->prepare("DELETE FROM installment_deadlines WHERE class_id = ? AND academic_year_id = ? AND installment_number = ?")->execute([$classId, $activeYearId, $i + 1]);
                        $this->db->prepare("INSERT INTO installment_deadlines (academic_year_id, class_id, installment_number, deadline_date) VALUES (?, ?, ?, ?)")->execute([$activeYearId, $classId, $i + 1, $trancheDeadlines[$i]]);

                        $totalScolarite += $amt;
                        $nbrTr++;
                    }

                    $updClass->execute([$nbrTr, $totalScolarite, $classId]);
                    
                    // Lancer la resynchronisation de tous les élèves de la classe
                    $this->financialService->syncClassFinancials($classId, $activeYearId);
                }

                $this->db->commit();
                Session::setFlash('success', "Tranches configurées avec succès.");
            } catch (\Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                Session::setFlash('error', "Erreur lors de la configuration : " . $e->getMessage());
            }

            header("Location: /school_fees/tranches");
            exit;
        }

        // Récupérer les classes, cycles, types d'enseignement
        $classes = $this->db->query("SELECT id, nom, cycle_id, teaching_type_id FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        // Tranches configurées pour affichage
        $installments = $this->feeInstallmentModel->getAll($activeYearId);

        include __DIR__ . '/../Views/school_fees/tranches.php';
    }

    /**
     * Liste et enregistrement des versements
     */
    public function versements()
    {
        $activeYearId = $this->academicYearService->getActiveYearId();
        $search = trim((string)($_GET['q'] ?? ''));

        // Liste des versements récents
        $payments = $this->studentPaymentModel->getAll($activeYearId, $search);

        // Récupérer la liste des élèves actifs par classe pour le formulaire
        $studentsRaw = $this->db->query("
            SELECT s.id, s.nom, s.prenom, c.nom as class_name, e.reste_a_payer, e.frais_scolarite_brut
            FROM students s
            JOIN enrollments e ON (s.id = e.student_id AND e.academic_year_id = $activeYearId)
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE s.is_withdrawn = 0 AND s.actif = 1
            ORDER BY c.nom ASC, s.nom ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $groupedStudents = [];
        foreach ($studentsRaw as $s) {
            $cName = $s['class_name'] ?: 'Sans classe';
            $groupedStudents[$cName][] = $s;
        }

        include __DIR__ . '/../Views/school_fees/versements.php';
    }

    /**
     * Enregistrer un versement (POST)
     */
    public function storeVersement()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /school_fees/versements");
            exit;
        }

        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', "Jeton CSRF invalide.");
            header("Location: /school_fees/versements");
            exit;
        }

        $studentId = (int)$_POST['student_id'];
        $amount = (float)$_POST['amount'];
        $paymentDate = trim((string)($_POST['payment_date'] ?? date('Y-m-d')));
        $paymentMethod = trim((string)($_POST['payment_method'] ?? 'ESPECES'));
        $reference = trim((string)($_POST['reference'] ?? ''));
        $observation = trim((string)($_POST['observation'] ?? ''));

        $activeYearId = $this->academicYearService->getActiveYearId();

        if ($amount <= 0) {
            Session::setFlash('error', "Le montant doit être supérieur à 0.");
            header("Location: /school_fees/versements");
            exit;
        }

        try {
            // Enregistrer
            $paymentId = $this->studentPaymentModel->create([
                'student_id' => $studentId,
                'academic_year_id' => $activeYearId,
                'amount' => $amount,
                'payment_date' => $paymentDate,
                'payment_method' => $paymentMethod,
                'reference' => $reference,
                'observation' => $observation,
                'created_by' => Session::get('user_id')
            ]);

            // Synchroniser le solde général
            $this->financialService->syncStudentFinancials($studentId, $activeYearId);

            // Audit
            $this->financialService->logHistory(
                Session::get('user_id'),
                'student_payment',
                $paymentId,
                'create',
                null,
                ['amount' => $amount, 'student_id' => $studentId, 'method' => $paymentMethod]
            );

            Session::setFlash('success', "Versement enregistré. Génération du reçu...");
            header("Location: /school_fees/receipt?id=" . $paymentId);
            exit;
        } catch (\Exception $e) {
            Session::setFlash('error', "Erreur lors de l'enregistrement : " . $e->getMessage());
            header("Location: /school_fees/versements");
            exit;
        }
    }

    /**
     * Supprimer un versement
     */
    public function deleteVersement()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /school_fees/versements");
            exit;
        }

        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', "Session expirée ou requête invalide.");
            header("Location: /school_fees/versements");
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $motive = trim($_POST['motive'] ?? '');
        
        if (empty($motive)) {
            Session::setFlash('error', "Le motif d'annulation est obligatoire.");
            header("Location: /school_fees/versements");
            exit;
        }

        $activeYearId = $this->academicYearService->getActiveYearId();

        try {
            $payment = $this->studentPaymentModel->find($id);
            if ($payment) {
                $studentId = (int)$payment['student_id'];
                
                $this->studentPaymentModel->delete($id, Session::get('user_id'), $motive);

                // Synchroniser le solde
                $this->financialService->syncStudentFinancials($studentId, $activeYearId);

                // Audit
                $this->financialService->logHistory(
                    Session::get('user_id'),
                    'student_payment',
                    $id,
                    'cancel',
                    $payment,
                    ['motive' => $motive]
                );

                Session::setFlash('success', "Versement annulé avec succès.");
            } else {
                Session::setFlash('error', "Versement introuvable.");
            }
        } catch (\Exception $e) {
            Session::setFlash('error', "Erreur : " . $e->getMessage());
        }

        header("Location: /school_fees/versements");
        exit;
    }

    /**
     * Suivi des insolvables
     */
    public function insolvables()
    {
        $activeYearId = $this->academicYearService->getActiveYearId();

        // 1. Forcer la mise à jour du cache d'insolvabilité
        $this->insolventStudentModel->refreshCache($activeYearId);

        // Filtres
        $filters = [
            'teaching_type_id' => (int)($_GET['teaching_type_id'] ?? 0),
            'cycle_id' => (int)($_GET['cycle_id'] ?? 0),
            'section_id' => (int)($_GET['section_id'] ?? 0),
            'class_id' => (int)($_GET['class_id'] ?? 0),
            'installment_number' => (int)($_GET['installment_number'] ?? 0),
            'q' => trim((string)($_GET['q'] ?? ''))
        ];

        // AJAX endpoint
        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            $action = $_GET['action'] ?? '';
            header('Content-Type: application/json');

            if ($action === 'get_cycles') {
                $teachingTypeId = (int)($_GET['teaching_type_id'] ?? 0);
                if ($teachingTypeId > 0) {
                    $sql = "SELECT DISTINCT cy.id, cy.nom FROM cycles cy JOIN classes c ON c.cycle_id = cy.id WHERE c.teaching_type_id = ? ORDER BY cy.nom ASC";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$teachingTypeId]);
                    $cycles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                }
                echo json_encode($cycles);
                exit;
            }

            if ($action === 'get_sections') {
                $teachingTypeId = (int)($_GET['teaching_type_id'] ?? 0);
                $cycleId = (int)($_GET['cycle_id'] ?? 0);
                $sql = "SELECT DISTINCT s.id, s.nom FROM sections s JOIN classes c ON c.section_id = s.id WHERE 1=1";
                $params = [];
                if ($teachingTypeId > 0) {
                    $sql .= " AND c.teaching_type_id = ?";
                    $params[] = $teachingTypeId;
                }
                if ($cycleId > 0) {
                    $sql .= " AND c.cycle_id = ?";
                    $params[] = $cycleId;
                }
                $sql .= " ORDER BY s.nom ASC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($sections);
                exit;
            }

            if ($action === 'get_classes') {
                $teachingTypeId = (int)($_GET['teaching_type_id'] ?? 0);
                $cycleId = (int)($_GET['cycle_id'] ?? 0);
                $sectionId = (int)($_GET['section_id'] ?? 0);
                $sql = "SELECT id, nom FROM classes WHERE 1=1";
                $params = [];
                if ($teachingTypeId > 0) {
                    $sql .= " AND teaching_type_id = ?";
                    $params[] = $teachingTypeId;
                }
                if ($cycleId > 0) {
                    $sql .= " AND cycle_id = ?";
                    $params[] = $cycleId;
                }
                if ($sectionId > 0) {
                    $sql .= " AND section_id = ?";
                    $params[] = $sectionId;
                }
                $sql .= " ORDER BY nom ASC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($classes);
                exit;
            }

            if ($action === 'get_tranches') {
                $classId = (int)($_GET['class_id'] ?? 0);
                $tranches = [];
                if ($classId > 0) {
                    $tranches = $this->feeInstallmentModel->resolveInstallments($activeYearId, $classId);
                }
                echo json_encode($tranches);
                exit;
            }

            if ($action === 'get_insolvables') {
                $teachingTypeId = (int)($_GET['teaching_type_id'] ?? 0);
                $cycleId = (int)($_GET['cycle_id'] ?? 0);
                $sectionId = (int)($_GET['section_id'] ?? 0);
                $classId = (int)($_GET['class_id'] ?? 0);
                $installmentNumber = (int)($_GET['installment_number'] ?? 0);
                $q = trim((string)($_GET['q'] ?? ''));

                if ($classId > 0 && $installmentNumber > 0) {
                    // Tranche-specific view
                    $insolvents = $this->insolventStudentModel->getInsolventsForTranche($activeYearId, $classId, $installmentNumber);
                    
                    if ($q !== '') {
                        $qLower = strtolower($q);
                        $insolvents = array_filter($insolvents, function($stud) use ($qLower) {
                            return strpos(strtolower($stud['student_nom']), $qLower) !== false ||
                                   strpos(strtolower($stud['student_prenom']), $qLower) !== false ||
                                   strpos(strtolower($stud['student_matricule'] ?? ''), $qLower) !== false;
                        });
                    }

                    $thead = '
                    <tr>
                        <th class="ps-4" style="width: 5%;">N°</th>
                        <th>' . __('matricule') . '</th>
                        <th>' . __('student') . '</th>
                        <th class="text-end">' . __('col_installment_amount') . '</th>
                        <th class="text-end">' . __('col_amount_allocated') . '</th>
                        <th class="text-end text-danger">' . __('col_remaining_to_pay') . '</th>
                        <th class="text-center pe-4">' . __('status') . '</th>
                    </tr>';

                    $tbody = '';
                    $totalCount = count($insolvents);
                    $totalRemaining = 0.0;

                    if (empty($insolvents)) {
                        $tbody = '
                        <tr>
                            <td colspan="7" class="text-center py-5 text-success">
                                <i class="bi bi-check-circle-fill fs-3 d-block mb-2 text-success"></i>
                                ' . __('congrats_no_insolvent') . '
                            </td>
                        </tr>';
                    } else {
                        $idx = 1;
                        foreach ($insolvents as $row) {
                            $totalRemaining += (float)$row['reste_a_payer'];
                            $paid = (float)$row['amount_paid'];
                            $statusBadge = '';
                            if ($paid <= 0) {
                                $statusBadge = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-15 px-2 py-0.5 rounded-pill small">' . __('status_unpaid') . '</span>';
                            } else {
                                $statusBadge = '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-15 px-2 py-0.5 rounded-pill small">' . __('partially_paid') . '</span>';
                            }

                            $tbody .= '
                            <tr>
                                <td class="ps-4 text-center fw-bold">' . $idx++ . '</td>
                                <td><code class="small text-secondary">' . htmlspecialchars($row['student_matricule'] ?? '-') . '</code></td>
                                <td>
                                    <div class="fw-bold text-main-theme" style="font-size: 0.85rem;">' . htmlspecialchars($row['student_nom']) . '</div>
                                    <div class="text-muted opacity-75" style="font-size: 0.72rem;">' . htmlspecialchars($row['student_prenom']) . '</div>
                                </td>
                                <td class="text-end fw-bold">' . number_format($row['amount_planned'], 0, '.', ' ') . ' <span class="extra-small">FCFA</span></td>
                                <td class="text-end fw-bold text-success">' . number_format($row['amount_paid'], 0, '.', ' ') . ' <span class="extra-small">FCFA</span></td>
                                <td class="text-end fw-black text-danger">' . number_format($row['reste_a_payer'], 0, '.', ' ') . ' <span class="extra-small">FCFA</span></td>
                                <td class="text-center pe-4">' . $statusBadge . '</td>
                            </tr>';
                        }
                    }

                    echo json_encode([
                        'success' => true,
                        'view' => 'tranche',
                        'thead' => $thead,
                        'tbody' => $tbody,
                        'count' => $totalCount,
                        'total_remaining' => number_format($totalRemaining, 0, '.', ' ') . ' FCFA'
                    ]);
                    exit;

                } else {
                    // General view
                    $filters = [
                        'teaching_type_id' => $teachingTypeId,
                        'cycle_id' => $cycleId,
                        'section_id' => $sectionId,
                        'class_id' => $classId,
                        'q' => $q
                    ];

                    $insolventStudents = $this->insolventStudentModel->getAll($activeYearId, $filters);
                    $totalCount = count($insolventStudents);
                    $totalRemaining = 0.0;

                    $thead = '
                    <tr>
                        <th class="ps-4">' . __('student') . '</th>
                        <th>' . __('class') . '</th>
                        <th>' . __('class_section') . '</th>
                        <th>' . __('teaching_type') . '</th>
                        <th class="text-end text-danger">' . __('col_amount_due') . '</th>
                        <th class="text-center">' . __('col_unpaid_tranches') . '</th>
                        <th>' . __('col_last_overdue') . '</th>
                        <th class="text-end pe-4">' . __('col_remaining_total') . '</th>
                    </tr>';

                    $tbody = '';
                    if (empty($insolventStudents)) {
                        $tbody = '
                        <tr>
                            <td colspan="8" class="text-center py-5 text-success">
                                <i class="bi bi-check-circle-fill fs-3 d-block mb-2 text-success"></i>
                                ' . __('congrats_no_insolvent') . '
                            </td>
                        </tr>';
                    } else {
                        foreach ($insolventStudents as $row) {
                            $totalRemaining += (float)$row['amount_due'];
                            $avatarChar = strtoupper(substr((string) $row['student_nom'], 0, 1));
                            
                            $deadlineFormatted = $row['last_overdue_deadline'] ? date('d/m/Y', strtotime($row['last_overdue_deadline'])) : '-';

                            $tbody .= '
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-init bg-danger bg-opacity-10 text-danger fw-bold rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                             style="width: 32px; height: 32px; font-size: 0.85rem; border: 1px solid rgba(220, 53, 69, 0.2);">
                                            ' . $avatarChar . '
                                        </div>
                                        <div>
                                            <div class="fw-bold text-main-theme" style="font-size: 0.85rem;">' . htmlspecialchars($row['student_nom']) . '</div>
                                            <div class="text-muted opacity-75" style="font-size: 0.72rem;">' . htmlspecialchars($row['student_prenom']) . '</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-15 px-2 py-0.5 rounded-pill small">
                                        ' . htmlspecialchars($row['class_name'] ?: '-') . '
                                    </span>
                                </td>
                                <td>' . htmlspecialchars($row['section_name'] ?: '-') . '</td>
                                <td>' . htmlspecialchars($row['teaching_type_name'] ?: '-') . '</td>
                                <td class="text-end fw-black text-danger">' . number_format($row['amount_due'], 0, '.', ' ') . ' <span class="extra-small">FCFA</span></td>
                                <td class="text-center fw-bold">
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-15 px-2.5 py-0.5 rounded-pill">
                                        ' . $row['unpaid_installments_count'] . ' ' . __('unpaid_tranches_suffix') . '
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-premium badge-premium-danger">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>' . $deadlineFormatted . '
                                    </span>
                                </td>
                                <td class="text-end fw-bold pe-4 text-muted">' . number_format($row['total_reste_a_payer'], 0, '.', ' ') . ' FCFA</td>
                            </tr>';
                        }
                    }

                    echo json_encode([
                        'success' => true,
                        'view' => 'general',
                        'thead' => $thead,
                        'tbody' => $tbody,
                        'count' => $totalCount,
                        'total_remaining' => number_format($totalRemaining, 0, '.', ' ') . ' FCFA'
                    ]);
                    exit;
                }
            }
        }

        // Obtenir la liste initiale (vue générale ou pré-filtrée)
        $insolventStudents = $this->insolventStudentModel->getAll($activeYearId, $filters);

        // Données des filtres en cascade par rapport aux sélections courantes
        $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        if ($filters['teaching_type_id'] > 0) {
            $cycles = $this->db->query("SELECT DISTINCT cy.id, cy.nom FROM cycles cy JOIN classes c ON c.cycle_id = cy.id WHERE c.teaching_type_id = " . $filters['teaching_type_id'] . " ORDER BY cy.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($filters['teaching_type_id'] > 0 || $filters['cycle_id'] > 0) {
            $sqlSec = "SELECT DISTINCT s.id, s.nom FROM sections s JOIN classes c ON c.section_id = s.id WHERE 1=1";
            $paramsSec = [];
            if ($filters['teaching_type_id'] > 0) {
                $sqlSec .= " AND c.teaching_type_id = ?";
                $paramsSec[] = $filters['teaching_type_id'];
            }
            if ($filters['cycle_id'] > 0) {
                $sqlSec .= " AND c.cycle_id = ?";
                $paramsSec[] = $filters['cycle_id'];
            }
            $sqlSec .= " ORDER BY s.nom ASC";
            $stmtSec = $this->db->prepare($sqlSec);
            $stmtSec->execute($paramsSec);
            $sections = $stmtSec->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($filters['teaching_type_id'] > 0 || $filters['cycle_id'] > 0 || $filters['section_id'] > 0) {
            $sqlCla = "SELECT id, nom FROM classes WHERE 1=1";
            $paramsCla = [];
            if ($filters['teaching_type_id'] > 0) {
                $sqlCla .= " AND teaching_type_id = ?";
                $paramsCla[] = $filters['teaching_type_id'];
            }
            if ($filters['cycle_id'] > 0) {
                $sqlCla .= " AND cycle_id = ?";
                $paramsCla[] = $filters['cycle_id'];
            }
            if ($filters['section_id'] > 0) {
                $sqlCla .= " AND section_id = ?";
                $paramsCla[] = $filters['section_id'];
            }
            $sqlCla .= " ORDER BY nom ASC";
            $stmtCla = $this->db->prepare($sqlCla);
            $stmtCla->execute($paramsCla);
            $classes = $stmtCla->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $classes = $this->db->query("SELECT id, nom FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        }

        $tranches = [];
        if ($filters['class_id'] > 0) {
            $tranches = $this->feeInstallmentModel->resolveInstallments($activeYearId, $filters['class_id']);
        }

        include __DIR__ . '/../Views/school_fees/insolvables.php';
    }

    /**
     * Reçu officiel de versement
     */
    public function receipt()
    {
        $id = (int)($_GET['id'] ?? 0);
        $activeYearId = $this->academicYearService->getActiveYearId();

        $payment = $this->studentPaymentModel->find($id);
        if (!$payment) {
            Session::setFlash('error', "Reçu introuvable.");
            header("Location: /school_fees/versements");
            exit;
        }

        // 1. Incrémenter le compteur d'impression si c'est la vue HTML standard (pas PDF ni Ajax)
        $isPdf = isset($_GET['pdf']) && $_GET['pdf'] == 1;
        if (!$isPdf && (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest')) {
            $newPrintCount = (int)$payment['print_count'] + 1;
            
            // Increment
            $this->studentPaymentModel->incrementPrintCount($id);
            
            // Log d'audit financier
            $this->financialService->logHistory(
                Session::get('user_id'),
                'student_payment',
                $id,
                'print',
                (string)$payment['print_count'],
                (string)$newPrintCount
            );
            
            $payment['print_count'] = $newPrintCount;
        }

        // 2. Charger l'historique d'impression pour la vue d'administration
        $stmtLogs = $this->db->prepare("
            SELECT fh.*, u.nom as user_nom, u.prenom as user_prenom 
            FROM financial_history fh 
            LEFT JOIN users u ON fh.user_id = u.id 
            WHERE fh.entity_type = 'student_payment' AND fh.entity_id = ? AND fh.action = 'print' 
            ORDER BY fh.event_date DESC
        ");
        $stmtLogs->execute([$id]);
        $printLogs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

        // 3. Récupérer toutes les tranches prévues de l'élève
        $stmtPlanned = $this->db->prepare("
            SELECT installment_number, amount_planned 
            FROM student_installments 
            WHERE student_id = ? AND academic_year_id = ?
            ORDER BY installment_number ASC
        ");
        $stmtPlanned->execute([$payment['student_id'], $activeYearId]);
        $plannedInstallments = $stmtPlanned->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($plannedInstallments) && !empty($payment['class_id'])) {
            $resolved = $this->feeInstallmentModel->resolveInstallments($activeYearId, (int)$payment['class_id']);
            foreach ($resolved as $r) {
                $plannedInstallments[] = [
                    'installment_number' => (int)$r['installment_order'],
                    'amount_planned' => (float)$r['amount']
                ];
            }
        }

        // 4. Récupérer tous les versements de l'élève dans l'ordre chronologique
        $stmtPays = $this->db->prepare("
            SELECT id, amount, payment_date 
            FROM student_payments 
            WHERE student_id = ? AND academic_year_id = ?
            ORDER BY payment_date ASC, id ASC
        ");
        $stmtPays->execute([$payment['student_id'], $activeYearId]);
        $chronologicalPayments = $stmtPays->fetchAll(PDO::FETCH_ASSOC);

        // 5. Simuler la répartition chronologique pour déterminer :
        //    - Les allocations de la transaction en cours ($allocations)
        //    - Le montant total versé cumulé par tranche ($globalPaidAmounts)
        $globalPaidAmounts = [];
        foreach ($plannedInstallments as $pi) {
            $globalPaidAmounts[(int)$pi['installment_number']] = 0.0;
        }

        $allocations = [];
        $currentPaymentId = (int)$payment['id'];

        foreach ($chronologicalPayments as $p) {
            $pAmount = (float)$p['amount'];
            $pId = (int)$p['id'];
            
            foreach ($plannedInstallments as $pi) {
                if ($pAmount <= 0) break;
                
                $instNum = (int)$pi['installment_number'];
                $amountPlanned = (float)$pi['amount_planned'];
                $alreadyPaid = $globalPaidAmounts[$instNum];
                
                $due = $amountPlanned - $alreadyPaid;
                if ($due > 0) {
                    $allocated = min($pAmount, $due);
                    
                    if ($pId === $currentPaymentId) {
                        $allocations[] = [
                            'installment_number' => $instNum,
                            'amount_planned' => $amountPlanned,
                            'amount_allocated' => $allocated,
                            'total_installment_paid' => $alreadyPaid + $allocated
                        ];
                    }
                    
                    $globalPaidAmounts[$instNum] += $allocated;
                    $pAmount -= $allocated;
                }
            }
            
            // Reliquat sur la dernière tranche en cas de sur-paiement
            if ($pAmount > 0 && !empty($plannedInstallments)) {
                $lastInst = end($plannedInstallments);
                $lastNum = (int)$lastInst['installment_number'];
                
                if ($pId === $currentPaymentId) {
                    $found = false;
                    foreach ($allocations as &$ca) {
                        if ($ca['installment_number'] === $lastNum) {
                            $ca['amount_allocated'] += $pAmount;
                            $ca['total_installment_paid'] += $pAmount;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $allocations[] = [
                            'installment_number' => $lastNum,
                            'amount_planned' => (float)$lastInst['amount_planned'],
                            'amount_allocated' => $pAmount,
                            'total_installment_paid' => $globalPaidAmounts[$lastNum] + $pAmount
                        ];
                    }
                }
                
                $globalPaidAmounts[$lastNum] += $pAmount;
            }
        }

        // Construire $studentInstallments dynamiquement d'après la simulation pour Tableau 2
        $studentInstallments = [];
        foreach ($plannedInstallments as $pi) {
            $instNum = (int)$pi['installment_number'];
            $studentInstallments[] = [
                'installment_number' => $instNum,
                'amount_planned' => (float)$pi['amount_planned'],
                'amount_paid' => isset($globalPaidAmounts[$instNum]) ? $globalPaidAmounts[$instNum] : 0.0
            ];
        }

        // 6. Récupérer l'historique complet des versements pour cet élève
        $paymentsHistory = $this->studentPaymentModel->getByStudent((int)$payment['student_id'], $activeYearId);

        // 7. Traduction du montant en lettres
        $amountInWords = \App\Core\NumberToWords::toWords($payment['amount']);

        // 8. Récupérer les totaux d'inscription de l'élève
        $stmt = $this->db->prepare("SELECT student_status, reste_a_payer, total_paye, total_reductions, total_bourses,
                                           frais_scolarite_brut, (frais_scolarite_brut - total_reductions - total_bourses) as scolarite_nette
                                    FROM enrollments WHERE student_id = ? AND academic_year_id = ?");
        $stmt->execute([$payment['student_id'], $activeYearId]);
        $enroll = $stmt->fetch(PDO::FETCH_ASSOC);

        // 9. Résoudre les noms et échéances des tranches pour la classe de l'élève
        $installmentsMap = [];
        if (!empty($payment['class_id'])) {
            $resolvedInstallments = $this->feeInstallmentModel->resolveInstallments($activeYearId, (int)$payment['class_id']);
            foreach ($resolvedInstallments as $inst) {
                $installmentsMap[(int)$inst['installment_order']] = [
                    'name' => $inst['name'],
                    'deadline' => $inst['deadline_date']
                ];
            }
        }

        // 7. Rendu PDF si demandé
        if ($isPdf) {
            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'Helvetica');

            $dompdf = new \Dompdf\Dompdf($options);
            
            ob_start();
            include __DIR__ . '/../Views/school_fees/receipt.php';
            $html = ob_get_clean();

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream("recu_scolarite_" . $payment['receipt_number'] . ".pdf", ["Attachment" => false]);
            exit;
        }

        include __DIR__ . '/../Views/school_fees/receipt.php';
    }

    /**
     * Impression officielle PDF de la liste des insolvables
     */
    public function printInsolvables()
    {
        $activeYear = $this->academicYearService->getActiveYear();
        $activeYearId = (int)($activeYear['id'] ?? 0);

        $classId = (int)($_GET['class_id'] ?? 0);
        $installmentNumber = (int)($_GET['installment_number'] ?? 0);

        if (!$classId || !$installmentNumber) {
            echo "Paramètres invalides ou manquants.";
            exit;
        }

        // Récupérer les informations de la classe
        $stmtC = $this->db->prepare("SELECT nom FROM classes WHERE id = ?");
        $stmtC->execute([$classId]);
        $className = $stmtC->fetchColumn() ?: '';

        // Récupérer les insolvables
        $insolvents = $this->insolventStudentModel->getInsolventsForTranche($activeYearId, $classId, $installmentNumber);

        // Charger l'institution
        $settingsStore = new \App\Services\SettingsStore($this->db);
        $settings = $settingsStore->all();

        // Récupérer le logo Base64
        $logoManager = \App\Core\LogoManager::getInstance($this->db);
        $logoBase64 = $logoManager->hasLogo() ? $logoManager->getLogoBase64() : '';

        // Nom de la tranche
        $feeInstModel = new \App\Models\FeeInstallment();
        $resolved = $feeInstModel->resolveInstallments($activeYearId, $classId);
        $trancheName = "Tranche " . $installmentNumber;
        foreach ($resolved as $r) {
            if ((int)$r['installment_order'] === $installmentNumber) {
                $trancheName = $r['name'];
                break;
            }
        }

        // Statistiques
        $totalCount = count($insolvents);
        $totalRemaining = 0.0;
        $totalPlanned = 0.0;
        $totalPaid = 0.0;
        foreach ($insolvents as $row) {
            $totalRemaining += (float)$row['reste_a_payer'];
            $totalPlanned += (float)$row['amount_planned'];
            $totalPaid += (float)$row['amount_paid'];
        }

        // Construction du document HTML
        $schoolName = htmlspecialchars($settings['school_name'] ?? '');
        $republic = htmlspecialchars($settings['school_republic'] ?? 'REPUBLIQUE DU CAMEROUN');
        $republicEn = htmlspecialchars($settings['school_republic_en'] ?? 'REPUBLIC OF CAMEROON');
        $motto = htmlspecialchars($settings['school_motto'] ?? 'Paix - Travail - Patrie');
        $mottoEn = htmlspecialchars($settings['school_motto_en'] ?? 'Peace - Work - Fatherland');
        $slogan = htmlspecialchars($settings['school_slogan'] ?? '');
        $sloganEn = htmlspecialchars($settings['school_slogan_en'] ?? '');
        $phone = htmlspecialchars($settings['school_phone'] ?? '');
        $city = htmlspecialchars($settings['school_city'] ?? '');
        $poBox = htmlspecialchars($settings['school_po_box'] ?? '');
        
        $contact = "TEL: " . $phone;
        if ($poBox) {
            $contact .= " | B.P.: " . $poBox;
        }
        $contact .= " | " . $city;

        $printDate = date('d/m/Y H:i');

        $html = '
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>Liste des insolvables - ' . htmlspecialchars($className) . '</title>
            <style>
                body {
                    font-family: "Helvetica", "Arial", sans-serif;
                    font-size: 11px;
                    line-height: 1.3;
                    color: #000;
                    margin: 0;
                    padding: 0;
                }
                .header-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 15px;
                }
                .header-table td {
                    vertical-align: top;
                    padding: 0;
                }
                .header-left, .header-right {
                    width: 40%;
                    text-align: center;
                }
                .header-center {
                    width: 20%;
                    text-align: center;
                }
                .header-line {
                    font-size: 9px;
                    font-weight: bold;
                    margin: 2px 0;
                    text-transform: uppercase;
                }
                .header-contact {
                    font-size: 8px;
                    margin: 2px 0;
                    text-transform: uppercase;
                }
                .logo-img {
                    max-width: 70px;
                    max-height: 70px;
                    object-fit: contain;
                }
                .school-name-row {
                    text-align: center;
                    margin-top: 5px;
                    border-bottom: 2px solid #000;
                    padding-bottom: 5px;
                }
                .school-name {
                    font-size: 15px;
                    font-weight: bold;
                    text-transform: uppercase;
                }
                .academic-year {
                    font-size: 11px;
                    margin-top: 2px;
                }
                .title-box {
                    text-align: center;
                    font-size: 13px;
                    font-weight: bold;
                    text-transform: uppercase;
                    border: 1.5px solid #000;
                    padding: 6px;
                    margin: 15px 0 10px 0;
                    background-color: #f3f4f6;
                }
                .stats-box {
                    margin-bottom: 15px;
                    border: 1px solid #ddd;
                    padding: 8px;
                    background-color: #fafafa;
                }
                .stats-table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .stats-table td {
                    padding: 2px 0;
                }
                .table-list {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                }
                .table-list th, .table-list td {
                    border: 1px solid #000;
                    padding: 6px 5px;
                    text-align: left;
                }
                .table-list th {
                    background-color: #e5e7eb;
                    font-weight: bold;
                    text-transform: uppercase;
                    font-size: 10px;
                }
                .text-end {
                    text-align: right;
                }
                .text-center {
                    text-align: center;
                }
                .fw-bold {
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <!-- Official Header Without Ministry -->
            <table class="header-table">
                <tr>
                    <td class="header-left">
                        <p class="header-line">' . $republic . '</p>
                        <p class="header-line">' . $motto . '</p>
                        <p class="header-line">' . $slogan . '</p>
                        <p class="header-contact">' . $contact . '</p>
                    </td>
                    <td class="header-center">';
        if ($logoBase64) {
            $html .= '<img class="logo-img" src="' . $logoBase64 . '" alt="Logo">';
        } else {
            $html .= '<div style="font-size: 8px; font-weight: bold; color: #888; border: 1px solid #ccc; width: 60px; height: 60px; line-height: 60px; margin: 0 auto; border-radius: 50%;">LOGO</div>';
        }
        $html .= '  </td>
                    <td class="header-right">
                        <p class="header-line">' . $republicEn . '</p>
                        <p class="header-line">' . $mottoEn . '</p>
                        <p class="header-line">' . $sloganEn . '</p>
                        <p class="header-contact">' . $contact . '</p>
                    </td>
                </tr>
            </table>

            <div class="school-name-row">
                <div class="school-name">' . $schoolName . '</div>
                <div class="academic-year">Année Académique : ' . htmlspecialchars($activeYear['nom'] ?? '') . '</div>
            </div>

            <div class="title-box">
                LISTE DES ÉLÈVES INSOLVABLES
            </div>

            <!-- Stats Box -->
            <div class="stats-box">
                <table class="stats-table">
                    <tr>
                        <td><strong>Classe :</strong> ' . htmlspecialchars($className) . '</td>
                        <td><strong>Tranche :</strong> ' . htmlspecialchars($trancheName) . '</td>
                    </tr>
                    <tr>
                        <td><strong>Date d\'impression :</strong> ' . $printDate . '</td>
                        <td><strong>Nombre d\'insolvables :</strong> ' . $totalCount . '</td>
                    </tr>
                    <tr>
                        <td colspan="2"><strong>Montant Total Restant :</strong> ' . number_format($totalRemaining, 0, '.', ' ') . ' FCFA</td>
                    </tr>
                </table>
            </div>

            <!-- Insolvents Table -->
            <table class="table-list">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 5%;">N°</th>
                        <th style="width: 15%;">Matricule</th>
                        <th style="width: 40%;">Nom / Prénom</th>
                        <th class="text-end" style="width: 13%;">Montant Tranche</th>
                        <th class="text-end" style="width: 13%;">Montant Versé</th>
                        <th class="text-end" style="width: 14%;">Reste à Payer</th>
                    </tr>
                </thead>
                <tbody>';
        if (empty($insolvents)) {
            $html .= '<tr><td colspan="6" class="text-center py-4" style="color: green;">Aucun élève insolvable pour cette tranche.</td></tr>';
        } else {
            $idx = 1;
            foreach ($insolvents as $row) {
                $html .= '
                <tr>
                    <td class="text-center">' . $idx++ . '</td>
                    <td style="font-family: monospace;">' . htmlspecialchars($row['student_matricule'] ?? '-') . '</td>
                    <td>' . htmlspecialchars($row['student_nom'] . ' ' . $row['student_prenom']) . '</td>
                    <td class="text-end">' . number_format($row['amount_planned'], 0, '.', ' ') . '</td>
                    <td class="text-end">' . number_format($row['amount_paid'], 0, '.', ' ') . '</td>
                    <td class="text-end fw-bold" style="color: #d9534f;">' . number_format($row['reste_a_payer'], 0, '.', ' ') . '</td>
                </tr>';
            }
        }
        $html .= '
                </tbody>
                <tfoot>
                    <tr class="fw-bold" style="background-color: #f3f4f6;">
                        <td colspan="3" class="text-end">TOTAL :</td>
                        <td class="text-end">' . number_format($totalPlanned, 0, '.', ' ') . '</td>
                        <td class="text-end">' . number_format($totalPaid, 0, '.', ' ') . '</td>
                        <td class="text-end" style="color: #d9534f;">' . number_format($totalRemaining, 0, '.', ' ') . '</td>
                    </tr>
                </tfoot>
            </table>
        </body>
        </html>
        ';

        // Rendu PDF avec Dompdf
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $filename = "eleves_insolvables_" . str_replace(' ', '_', $className) . "_tranche_" . $installmentNumber . ".pdf";
        $dompdf->stream($filename, ["Attachment" => false]);
        exit;
    }

    public function printGrille()
    {
        $activeYear = $this->academicYearService->getActiveYear();
        $activeYearId = (int)($activeYear['id'] ?? 0);

        // Filtres
        $teachingTypeId = (int)($_GET['teaching_type_id'] ?? 0);
        $cycleId = (int)($_GET['cycle_id'] ?? 0);
        $sectionId = (int)($_GET['section_id'] ?? 0);
        $classId = (int)($_GET['class_id'] ?? 0);

        // Données des sélecteurs pour les libellés de filtres dans l'en-tête du PDF
        $filterTexts = [];
        if ($teachingTypeId) {
            $stmt = $this->db->prepare("SELECT nom FROM teaching_types WHERE id = ?");
            $stmt->execute([$teachingTypeId]);
            $filterTexts[] = "Enseignement: " . $stmt->fetchColumn();
        }
        if ($cycleId) {
            $stmt = $this->db->prepare("SELECT nom FROM cycles WHERE id = ?");
            $stmt->execute([$cycleId]);
            $filterTexts[] = "Cycle: " . $stmt->fetchColumn();
        }
        if ($sectionId) {
            $stmt = $this->db->prepare("SELECT nom FROM sections WHERE id = ?");
            $stmt->execute([$sectionId]);
            $filterTexts[] = "Section: " . $stmt->fetchColumn();
        }
        if ($classId) {
            $stmt = $this->db->prepare("SELECT nom FROM classes WHERE id = ?");
            $stmt->execute([$classId]);
            $filterTexts[] = "Classe: " . $stmt->fetchColumn();
        }
        $filtersDescription = empty($filterTexts) ? "Toutes les classes" : implode(" | ", $filterTexts);

        // Récupérer les classes concernées
        $classesQuery = "SELECT id, nom, cycle_id, section_id, teaching_type_id FROM classes WHERE 1=1";
        $classesParams = [];
        if ($teachingTypeId) {
            $classesQuery .= " AND teaching_type_id = ?";
            $classesParams[] = $teachingTypeId;
        }
        if ($cycleId) {
            $classesQuery .= " AND cycle_id = ?";
            $classesParams[] = $cycleId;
        }
        if ($sectionId) {
            $classesQuery .= " AND section_id = ?";
            $classesParams[] = $sectionId;
        }
        $classesQuery .= " ORDER BY nom ASC";
        
        $stmtClasses = $this->db->prepare($classesQuery);
        $stmtClasses->execute($classesParams);
        $allClasses = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

        $grilleData = [];
        foreach ($allClasses as $class) {
            if ($classId && (int)$class['id'] !== $classId) {
                continue;
            }

            $resolvedAmount = $this->schoolFeeModel->resolveAmount($activeYearId, (int)$class['id']);
            $tranches = $this->feeInstallmentModel->resolveInstallments($activeYearId, (int)$class['id']);
            
            $stmtC = $this->db->prepare("SELECT frais_inscription, frais_inscription_reinscription FROM classes WHERE id = ?");
            $stmtC->execute([$class['id']]);
            $cDetails = $stmtC->fetch(PDO::FETCH_ASSOC);

            $grilleData[] = [
                'class_name' => $class['nom'],
                'frais_inscription_nouveau' => (float)$cDetails['frais_inscription'],
                'frais_inscription_ancien' => (float)$cDetails['frais_inscription_reinscription'],
                'frais_scolarite_brut' => $resolvedAmount,
                'nbr_tranches' => count($tranches),
                'tranches' => $tranches
            ];
        }

        // Charger l'institution et logo
        $settingsStore = new \App\Services\SettingsStore($this->db);
        $settings = $settingsStore->all();

        $logoManager = \App\Core\LogoManager::getInstance($this->db);
        $logoBase64 = $logoManager->hasLogo() ? $logoManager->getLogoBase64() : '';

        $schoolName = htmlspecialchars($settings['school_name'] ?? '');
        $republic = htmlspecialchars($settings['school_republic'] ?? 'REPUBLIQUE DU CAMEROUN');
        $republicEn = htmlspecialchars($settings['school_republic_en'] ?? 'REPUBLIC OF CAMEROON');
        $motto = htmlspecialchars($settings['school_motto'] ?? 'Paix - Travail - Patrie');
        $mottoEn = htmlspecialchars($settings['school_motto_en'] ?? 'Peace - Work - Fatherland');
        $slogan = htmlspecialchars($settings['school_slogan'] ?? '');
        $sloganEn = htmlspecialchars($settings['school_slogan_en'] ?? '');
        $phone = htmlspecialchars($settings['school_phone'] ?? '');
        $city = htmlspecialchars($settings['school_city'] ?? '');
        $poBox = htmlspecialchars($settings['school_po_box'] ?? '');
        
        $contact = "TEL: " . $phone;
        if ($poBox) {
            $contact .= " | B.P.: " . $poBox;
        }
        $contact .= " | " . $city;

        $printDate = date('d/m/Y H:i');

        // HTML content
        $html = '
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>Grille des Frais de Scolarité</title>
            <style>
                body {
                    font-family: "Helvetica", "Arial", sans-serif;
                    font-size: 10px;
                    line-height: 1.3;
                    color: #000;
                    margin: 0;
                    padding: 0;
                }
                .header-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 15px;
                }
                .header-table td {
                    vertical-align: top;
                    padding: 0;
                }
                .header-left, .header-right {
                    width: 40%;
                    text-align: center;
                }
                .header-center {
                    width: 20%;
                    text-align: center;
                }
                .header-line {
                    font-size: 9px;
                    font-weight: bold;
                    margin: 2px 0;
                    text-transform: uppercase;
                }
                .header-contact {
                    font-size: 8px;
                    margin: 2px 0;
                    text-transform: uppercase;
                }
                .logo-img {
                    max-width: 70px;
                    max-height: 70px;
                    object-fit: contain;
                }
                .school-name-row {
                    text-align: center;
                    margin-top: 5px;
                    border-bottom: 2px solid #000;
                    padding-bottom: 5px;
                }
                .school-name {
                    font-size: 15px;
                    font-weight: bold;
                    text-transform: uppercase;
                }
                .academic-year {
                    font-size: 11px;
                    margin-top: 2px;
                }
                .title-box {
                    text-align: center;
                    font-size: 13px;
                    font-weight: bold;
                    text-transform: uppercase;
                    border: 1.5px solid #000;
                    padding: 6px;
                    margin: 15px 0 10px 0;
                    background-color: #f3f4f6;
                }
                .stats-box {
                    margin-bottom: 15px;
                    border: 1px solid #ddd;
                    padding: 8px;
                    background-color: #fafafa;
                }
                .stats-table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .stats-table td {
                    padding: 2px 0;
                }
                .table-list {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                }
                .table-list th, .table-list td {
                    border: 1px solid #000;
                    padding: 6px 5px;
                    text-align: left;
                    vertical-align: middle;
                }
                .table-list th {
                    background-color: #e5e7eb;
                    font-weight: bold;
                    text-transform: uppercase;
                    font-size: 9px;
                }
                .text-end {
                    text-align: right;
                }
                .text-center {
                    text-align: center;
                }
                .fw-bold {
                    font-weight: bold;
                }
                .tranches-container {
                    font-size: 8.5px;
                }
                .tranche-item {
                    display: inline-block;
                    margin-right: 8px;
                    padding: 2px 4px;
                    border: 1px solid #ddd;
                    background-color: #f9f9f9;
                    border-radius: 3px;
                }
            </style>
        </head>
        <body>
            <!-- Official Header Without Ministry -->
            <table class="header-table">
                <tr>
                    <td class="header-left">
                        <p class="header-line">' . $republic . '</p>
                        <p class="header-line">' . $motto . '</p>
                        <p class="header-line">' . $slogan . '</p>
                        <p class="header-contact">' . $contact . '</p>
                    </td>
                    <td class="header-center">';
        if ($logoBase64) {
            $html .= '<img class="logo-img" src="' . $logoBase64 . '" alt="Logo">';
        } else {
            $html .= '<div style="font-size: 8px; font-weight: bold; color: #888; border: 1px solid #ccc; width: 60px; height: 60px; line-height: 60px; margin: 0 auto; border-radius: 50%;">LOGO</div>';
        }
        $html .= '  </td>
                    <td class="header-right">
                        <p class="header-line">' . $republicEn . '</p>
                        <p class="header-line">' . $mottoEn . '</p>
                        <p class="header-line">' . $sloganEn . '</p>
                        <p class="header-contact">' . $contact . '</p>
                    </td>
                </tr>
            </table>

            <div class="school-name-row">
                <div class="school-name">' . $schoolName . '</div>
                <div class="academic-year">Année Académique : ' . htmlspecialchars($activeYear['nom'] ?? '') . '</div>
            </div>

            <div class="title-box">
                GRILLE DES FRAIS DE SCOLARITÉ
            </div>

            <!-- Stats Box -->
            <div class="stats-box">
                <table class="stats-table">
                    <tr>
                        <td><strong>Filtres appliqués :</strong> ' . htmlspecialchars($filtersDescription) . '</td>
                        <td><strong>Date d\'impression :</strong> ' . $printDate . '</td>
                    </tr>
                </table>
            </div>

            <!-- Grille Table -->
            <table class="table-list">
                <thead>
                    <tr>
                        <th style="width: 20%;">Classe</th>
                        <th class="text-end" style="width: 15%;">Inscription (Nouveau)</th>
                        <th class="text-end" style="width: 15%;">Inscription (Ancien)</th>
                        <th class="text-end" style="width: 15%;">Scolarité Brut</th>
                        <th class="text-center" style="width: 10%;">Tranches</th>
                        <th style="width: 25%;">Échéances</th>
                    </tr>
                </thead>
                <tbody>';
        
        if (empty($grilleData)) {
            $html .= '<tr><td colspan="6" class="text-center py-4">Aucune donnée de scolarité disponible.</td></tr>';
        } else {
            foreach ($grilleData as $row) {
                $tranchesHtml = '';
                if (!empty($row['tranches'])) {
                    foreach ($row['tranches'] as $tr) {
                        $tranchesHtml .= '<span class="tranche-item"><strong>' . htmlspecialchars($tr['name']) . ':</strong> ' . number_format($tr['amount'], 0, '.', ' ') . ' F (' . date('d/m/Y', strtotime($tr['deadline_date'])) . ')</span> ';
                    }
                } else {
                    $tranchesHtml = '<span style="color: #666; font-style: italic;">Aucune tranche définie</span>';
                }

                $html .= '
                <tr>
                    <td class="fw-bold">' . htmlspecialchars($row['class_name']) . '</td>
                    <td class="text-end">' . number_format($row['frais_inscription_nouveau'], 0, '.', ' ') . '</td>
                    <td class="text-end">' . number_format($row['frais_inscription_ancien'], 0, '.', ' ') . '</td>
                    <td class="text-end fw-bold" style="color: #2563EB;">' . number_format($row['frais_scolarite_brut'], 0, '.', ' ') . '</td>
                    <td class="text-center">' . $row['nbr_tranches'] . '</td>
                    <td class="tranches-container">' . $tranchesHtml . '</td>
                </tr>';
            }
        }

        $html .= '
                </tbody>
            </table>
        </body>
        </html>';

        // Render PDF with Dompdf
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("grille_scolarite.pdf", ["Attachment" => false]);
        exit;
    }

    public function templateGrille()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        ini_set('memory_limit', '512M');
        $lang = Session::get('app_lang', 'fr') === 'en' ? 'en' : 'fr';
        
        $teachingTypeId = (int)($_GET['teaching_type_id'] ?? 0);
        $cycleId = (int)($_GET['cycle_id'] ?? 0);
        $sectionId = (int)($_GET['section_id'] ?? 0);
        $classId = (int)($_GET['class_id'] ?? 0);

        try {
            $svc = new \App\Services\Import\ExcelTemplateService($this->db);
            $content = $svc->generateGrilleTemplate($lang, $teachingTypeId, $cycleId, $sectionId, $classId);
            $filename = $lang === 'fr' ? 'Modele_Import_Grille_Scolarite.xlsx' : 'Fees_Grid_Import_Template.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            echo $content;
            exit;
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
            header('Location: /school_fees/grille');
            exit;
        }
    }

    public function importGrille()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['import_file'])) {
            header('Location: /school_fees/grille');
            exit;
        }

        $file = $_FILES['import_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('error', 'Erreur de téléchargement du fichier.');
            header('Location: /school_fees/grille');
            exit;
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (!in_array(strtolower($extension), ['xlsx', 'xls', 'csv'])) {
            Session::setFlash('error', 'Veuillez sélectionner un fichier Excel (.xlsx, .xls) ou CSV.');
            header('Location: /school_fees/grille');
            exit;
        }

        $activeYearId = $this->academicYearService->getActiveYearId();
        
        $teachingTypeId = (int)($_GET['teaching_type_id'] ?? 0);
        $cycleId = (int)($_GET['cycle_id'] ?? 0);
        $sectionId = (int)($_GET['section_id'] ?? 0);
        $classId = (int)($_GET['class_id'] ?? 0);

        try {
            $processor = new \App\Services\Import\GrilleImportProcessor($this->db);
            $result = $processor->process($file['tmp_name'], $activeYearId, $teachingTypeId, $cycleId, $sectionId, $classId);

            if ($result['success']) {
                if ($result['count'] > 0) {
                    Session::setFlash('success', "Importation réussie ! {$result['count']} classe(s) configurée(s) avec succès.");
                } else {
                    Session::setFlash('error', "Aucune classe correspondante n'a été importée (vérifiez vos filtres actifs ou les noms de classes).");
                }
            } else {
                $errStr = implode('<br>', array_slice($result['errors'], 0, 10));
                if (count($result['errors']) > 10) {
                    $errStr .= '<br>... et d\'autres erreurs.';
                }
                Session::setFlash('error', "Échec de l'importation. Aucune modification n'a été enregistrée.<br>Erreurs détectées :<br>" . $errStr);
            }
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Erreur système lors du traitement : ' . $e->getMessage());
        }

        $redirectUrl = '/school_fees/grille';
        if (!empty($_SERVER['QUERY_STRING'])) {
            $redirectUrl .= '?' . $_SERVER['QUERY_STRING'];
        }
        header('Location: ' . $redirectUrl);
        exit;
    }
}
