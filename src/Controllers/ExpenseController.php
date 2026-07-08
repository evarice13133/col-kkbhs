<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Core\PermissionManager;
use App\Services\AcademicYearService;
use App\Services\SettingsStore;
use App\Core\LogoManager;
use PDO;
use Exception;

/**
 * ExpenseController
 * 
 * Gère les dépenses, les catégories de dépenses, l'audit et l'impression PDF.
 */
class ExpenseController
{
    private PDO $db;
    private AcademicYearService $academicYearService;
    private SettingsStore $settingsStore;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->academicYearService = new AcademicYearService($this->db);
        $this->settingsStore = new SettingsStore($this->db);

        // RBAC: Autorisé pour superadmin, admin, caissier, comptable
        PermissionManager::requirePermission('manage_expenses');
    }

    /**
     * Liste des dépenses avec filtres, recherche, tri et pagination.
     */
    public function index()
    {
        $activeYearId = $this->academicYearService->getActiveYearId();

        // 1. Récupérer les filtres
        $filters = [
            'start_date' => $_GET['start_date'] ?? null,
            'end_date'   => $_GET['end_date'] ?? null,
            'category'   => $_GET['category'] ?? null,
            'user'       => $_GET['user'] ?? null,
            'min_amount' => $_GET['min_amount'] ?? null,
            'max_amount' => $_GET['max_amount'] ?? null,
            'status'     => $_GET['status'] ?? null,
        ];
        $search = trim($_GET['q'] ?? '');

        // 2. Construire la requête de base
        $sql = "SELECT e.*, ec.name as category_name, CONCAT(u.prenom, ' ', u.nom) as user_name 
                FROM expenses e
                JOIN expense_categories ec ON e.category_id = ec.id
                JOIN users u ON e.user_id = u.id
                WHERE e.academic_year_id = :academic_year_id";
        
        $params = [':academic_year_id' => $activeYearId];

        // Appliquer les filtres
        if (!empty($filters['start_date'])) {
            $sql .= " AND e.expense_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND e.expense_date <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        if (!empty($filters['category'])) {
            $sql .= " AND e.category_id = :category_id";
            $params[':category_id'] = (int)$filters['category'];
        }
        if (!empty($filters['user'])) {
            $sql .= " AND e.user_id = :user_id";
            $params[':user_id'] = (int)$filters['user'];
        }
        if (!empty($filters['min_amount'])) {
            $sql .= " AND e.amount >= :min_amount";
            $params[':min_amount'] = (float)$filters['min_amount'];
        }
        if (!empty($filters['max_amount'])) {
            $sql .= " AND e.amount <= :max_amount";
            $params[':max_amount'] = (float)$filters['max_amount'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND e.status = :status";
            $params[':status'] = $filters['status'];
        }

        // Appliquer la recherche textuelle
        if ($search !== '') {
            $sql .= " AND (e.reference LIKE :search OR e.motive LIKE :search OR e.description LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        // Tri
        $allowedSorts = ['reference', 'expense_date', 'amount', 'status', 'category_name'];
        $sort = in_array($_GET['sort'] ?? '', $allowedSorts) ? $_GET['sort'] : 'expense_date';
        $direction = strtoupper($_GET['dir'] ?? '') === 'ASC' ? 'ASC' : 'DESC';

        if ($sort === 'category_name') {
            $sql .= " ORDER BY ec.name $direction, e.id DESC";
        } else {
            $sql .= " ORDER BY e.$sort $direction, e.id DESC";
        }

        // Pagination
        $limit = 15;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $limit;

        // Requête de comptage pour la pagination
        $countSql = "SELECT COUNT(*) FROM (" . $sql . ") as tmp";
        $stmtCount = $this->db->prepare($countSql);
        $stmtCount->execute($params);
        $totalItems = (int)$stmtCount->fetchColumn();
        $totalPages = ceil($totalItems / $limit);

        // Appliquer LIMIT & OFFSET
        $sql .= " LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        
        // Liaison des paramètres types pour limit/offset
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Charger les filtres dropdown (catégories et utilisateurs habilités aux dépenses)
        $categories = $this->db->query("SELECT * FROM expense_categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        // Utilisateurs ayant des rôles autorisés à enregistrer des dépenses
        $users = $this->db->query("
            SELECT DISTINCT u.id, u.nom, u.prenom 
            FROM users u
            WHERE u.role IN ('admin', 'caissier', 'comptable')
            ORDER BY u.nom ASC, u.prenom ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Total des dépenses filtrées (somme des montants affichés selon filtres, hors annulés/inactifs si souhaité, mais ici global filtre)
        $sumSql = "SELECT COALESCE(SUM(amount), 0) FROM expenses e WHERE e.academic_year_id = :academic_year_id AND e.status = 'active'";
        $sumParams = [':academic_year_id' => $activeYearId];
        // si des filtres sont appliqués, on calcule le total sur les dépenses filtrées actives
        $sumFilteredSql = "SELECT COALESCE(SUM(e.amount), 0) FROM expenses e JOIN expense_categories ec ON e.category_id = ec.id WHERE e.academic_year_id = :academic_year_id AND e.status = 'active'";
        if (!empty($filters['start_date'])) { $sumFilteredSql .= " AND e.expense_date >= :start_date"; }
        if (!empty($filters['end_date'])) { $sumFilteredSql .= " AND e.expense_date <= :end_date"; }
        if (!empty($filters['category'])) { $sumFilteredSql .= " AND e.category_id = :category_id"; }
        if (!empty($filters['user'])) { $sumFilteredSql .= " AND e.user_id = :user_id"; }
        if (!empty($filters['min_amount'])) { $sumFilteredSql .= " AND e.amount >= :min_amount"; }
        if (!empty($filters['max_amount'])) { $sumFilteredSql .= " AND e.amount <= :max_amount"; }
        if ($search !== '') { $sumFilteredSql .= " AND (e.reference LIKE :search OR e.motive LIKE :search OR e.description LIKE :search)"; }
        
        $stmtSum = $this->db->prepare($sumFilteredSql);
        $stmtSum->execute($params);
        $totalAmountFiltered = (float)$stmtSum->fetchColumn();

        include __DIR__ . '/../Views/expenses/index.php';
    }

    /**
     * Enregistre une dépense.
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', "Session expirée ou requête invalide.");
                header("Location: /expenses");
                exit;
            }

            $category_id = (int)($_POST['category_id'] ?? 0);
            $expense_date = trim((string)($_POST['expense_date'] ?? date('Y-m-d')));
            $amount = (float)($_POST['amount'] ?? 0.0);
            $motive = trim((string)($_POST['motive'] ?? ''));
            $description = trim((string)($_POST['description'] ?? ''));
            $activeYearId = $this->academicYearService->getActiveYearId();
            $userId = (int)Session::get('user_id');

            if ($category_id <= 0 || $amount <= 0 || empty($motive)) {
                Session::setFlash('error', "Veuillez remplir tous les champs obligatoires (Catégorie, Montant, Motif).");
                header("Location: /expenses");
                exit;
            }

            // Vérifier la catégorie
            $stmtCat = $this->db->prepare("SELECT active FROM expense_categories WHERE id = ?");
            $stmtCat->execute([$category_id]);
            $catActive = $stmtCat->fetchColumn();

            if ($catActive === false || (int)$catActive !== 1) {
                Session::setFlash('error', "Catégorie inexistante ou inactive.");
                header("Location: /expenses");
                exit;
            }

            try {
                $this->db->beginTransaction();

                // Génération de la référence unique (EXP-Ymd-XXXX)
                $refExists = true;
                $reference = '';
                while ($refExists) {
                    $rand = sprintf("%04d", rand(1000, 9999));
                    $reference = 'EXP-' . date('Ymd', strtotime($expense_date)) . '-' . $rand;
                    $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM expenses WHERE reference = ?");
                    $stmtCheck->execute([$reference]);
                    if ((int)$stmtCheck->fetchColumn() === 0) {
                        $refExists = false;
                    }
                }

                // Insertion
                $stmt = $this->db->prepare("
                    INSERT INTO expenses (reference, expense_date, category_id, amount, motive, description, user_id, academic_year_id, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
                ");
                $stmt->execute([
                    $reference,
                    $expense_date,
                    $category_id,
                    $amount,
                    $motive,
                    $description ?: null,
                    $userId,
                    $activeYearId
                ]);
                $expenseId = (int)$this->db->lastInsertId();

                // Journal d'audit
                $newValues = [
                    'reference' => $reference,
                    'expense_date' => $expense_date,
                    'category_id' => $category_id,
                    'amount' => $amount,
                    'motive' => $motive,
                    'description' => $description
                ];
                $this->logExpenseAudit($expenseId, null, $userId, 'create', null, $newValues);

                $this->db->commit();
                Session::setFlash('success', "Dépense enregistrée avec succès sous la référence " . $reference);
            } catch (Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                Session::setFlash('error', "Erreur lors de l'enregistrement de la dépense : " . $e->getMessage());
            }

            header("Location: /expenses");
            exit;
        }
    }

    /**
     * Modifie une dépense.
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', "Session expirée ou requête invalide.");
                header("Location: /expenses");
                exit;
            }

            $id = (int)($_POST['id'] ?? 0);
            $category_id = (int)($_POST['category_id'] ?? 0);
            $expense_date = trim((string)($_POST['expense_date'] ?? date('Y-m-d')));
            $amount = (float)($_POST['amount'] ?? 0.0);
            $motive = trim((string)($_POST['motive'] ?? ''));
            $description = trim((string)($_POST['description'] ?? ''));
            $userId = (int)Session::get('user_id');

            if ($id <= 0 || $category_id <= 0 || $amount <= 0 || empty($motive)) {
                Session::setFlash('error', "Veuillez remplir tous les champs obligatoires.");
                header("Location: /expenses");
                exit;
            }

            // Récupérer l'ancienne dépense
            $stmtOld = $this->db->prepare("SELECT * FROM expenses WHERE id = ?");
            $stmtOld->execute([$id]);
            $oldExpense = $stmtOld->fetch(PDO::FETCH_ASSOC);

            if (!$oldExpense) {
                Session::setFlash('error', "Dépense introuvable.");
                header("Location: /expenses");
                exit;
            }

            if ($oldExpense['status'] === 'cancelled') {
                Session::setFlash('error', "Impossible de modifier une dépense annulée.");
                header("Location: /expenses");
                exit;
            }

            try {
                $this->db->beginTransaction();

                $stmt = $this->db->prepare("
                    UPDATE expenses 
                    SET category_id = ?, expense_date = ?, amount = ?, motive = ?, description = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $category_id,
                    $expense_date,
                    $amount,
                    $motive,
                    $description ?: null,
                    $id
                ]);

                // Journal d'audit
                $oldValues = [
                    'category_id' => (int)$oldExpense['category_id'],
                    'expense_date' => $oldExpense['expense_date'],
                    'amount' => (float)$oldExpense['amount'],
                    'motive' => $oldExpense['motive'],
                    'description' => $oldExpense['description']
                ];
                $newValues = [
                    'category_id' => $category_id,
                    'expense_date' => $expense_date,
                    'amount' => $amount,
                    'motive' => $motive,
                    'description' => $description
                ];

                $this->logExpenseAudit($id, null, $userId, 'update', $oldValues, $newValues);

                $this->db->commit();
                Session::setFlash('success', "Dépense mise à jour avec succès.");
            } catch (Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                Session::setFlash('error', "Erreur lors de la modification de la dépense : " . $e->getMessage());
            }

            header("Location: /expenses");
            exit;
        }
    }

    /**
     * Annule une dépense (Suppression logique).
     */
    public function cancel()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', "Session expirée ou requête invalide.");
                header("Location: /expenses");
                exit;
            }

            $id = (int)($_POST['id'] ?? 0);
            $cancel_reason = trim((string)($_POST['cancel_reason'] ?? ''));
            $userId = (int)Session::get('user_id');

            if ($id <= 0 || empty($cancel_reason)) {
                Session::setFlash('error', "Le motif d'annulation est obligatoire.");
                header("Location: /expenses");
                exit;
            }

            // Récupérer la dépense
            $stmtExp = $this->db->prepare("SELECT * FROM expenses WHERE id = ?");
            $stmtExp->execute([$id]);
            $expense = $stmtExp->fetch(PDO::FETCH_ASSOC);

            if (!$expense) {
                Session::setFlash('error', "Dépense introuvable.");
                header("Location: /expenses");
                exit;
            }

            if ($expense['status'] === 'cancelled') {
                Session::setFlash('error', "Cette dépense est déjà annulée.");
                header("Location: /expenses");
                exit;
            }

            try {
                $this->db->beginTransaction();

                $stmt = $this->db->prepare("UPDATE expenses SET status = 'cancelled', cancel_reason = ? WHERE id = ?");
                $stmt->execute([$cancel_reason, $id]);

                // Journal d'audit
                $oldValues = ['status' => $expense['status']];
                $newValues = ['status' => 'cancelled', 'cancel_reason' => $cancel_reason];
                
                $this->logExpenseAudit($id, null, $userId, 'cancel', $oldValues, $newValues, $cancel_reason);

                $this->db->commit();
                Session::setFlash('success', "La dépense a été annulée.");
            } catch (Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                Session::setFlash('error', "Erreur lors de l'annulation de la dépense : " . $e->getMessage());
            }

            header("Location: /expenses");
            exit;
        }
    }

    /**
     * Génère l'impression PDF des dépenses filtrées.
     */
    public function printReport()
    {
        $activeYear = $this->academicYearService->getActiveYear();
        $activeYearId = (int)($activeYear['id'] ?? 0);

        // Récupérer les filtres
        $filters = [
            'start_date' => $_GET['start_date'] ?? null,
            'end_date'   => $_GET['end_date'] ?? null,
            'category'   => $_GET['category'] ?? null,
            'user'       => $_GET['user'] ?? null,
            'min_amount' => $_GET['min_amount'] ?? null,
            'max_amount' => $_GET['max_amount'] ?? null,
            'status'     => $_GET['status'] ?? null,
        ];
        $search = trim($_GET['q'] ?? '');

        // Construire la requête
        $sql = "SELECT e.*, ec.name as category_name, CONCAT(u.prenom, ' ', u.nom) as user_name 
                FROM expenses e
                JOIN expense_categories ec ON e.category_id = ec.id
                JOIN users u ON e.user_id = u.id
                WHERE e.academic_year_id = :academic_year_id";
        
        $params = [':academic_year_id' => $activeYearId];

        // Appliquer filtres
        if (!empty($filters['start_date'])) {
            $sql .= " AND e.expense_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND e.expense_date <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        if (!empty($filters['category'])) {
            $sql .= " AND e.category_id = :category_id";
            $params[':category_id'] = (int)$filters['category'];
        }
        if (!empty($filters['user'])) {
            $sql .= " AND e.user_id = :user_id";
            $params[':user_id'] = (int)$filters['user'];
        }
        if (!empty($filters['min_amount'])) {
            $sql .= " AND e.amount >= :min_amount";
            $params[':min_amount'] = (float)$filters['min_amount'];
        }
        if (!empty($filters['max_amount'])) {
            $sql .= " AND e.amount <= :max_amount";
            $params[':max_amount'] = (float)$filters['max_amount'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND e.status = :status";
            $params[':status'] = $filters['status'];
        }
        if ($search !== '') {
            $sql .= " AND (e.reference LIKE :search OR e.motive LIKE :search OR e.description LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY e.expense_date DESC, e.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calcul totaux
        $totalCount = count($expenses);
        $totalAmount = 0.0;
        foreach ($expenses as $e) {
            if ($e['status'] === 'active') {
                $totalAmount += (float)$e['amount'];
            }
        }

        // Charger l'institution
        $settings = $this->settingsStore->all();
        $logoBase64 = LogoManager::getInstance($this->db)->hasLogo() ? LogoManager::getInstance($this->db)->getLogoBase64() : '';

        // Infos filtres textuels pour le PDF
        $filterTexts = [];
        if (!empty($filters['start_date']) || !empty($filters['end_date'])) {
            $start = !empty($filters['start_date']) ? date('d/m/Y', strtotime($filters['start_date'])) : 'Début';
            $end = !empty($filters['end_date']) ? date('d/m/Y', strtotime($filters['end_date'])) : 'Fin';
            $filterTexts[] = "Période : Du $start au $end";
        }
        if (!empty($filters['category'])) {
            $stmtCat = $this->db->prepare("SELECT name FROM expense_categories WHERE id = ?");
            $stmtCat->execute([$filters['category']]);
            $filterTexts[] = "Catégorie : " . $stmtCat->fetchColumn();
        }
        if (!empty($filters['status'])) {
            $statusMap = ['active' => 'Actif', 'inactive' => 'Inactif', 'cancelled' => 'Annulé'];
            $filterTexts[] = "Statut : " . ($statusMap[$filters['status']] ?? $filters['status']);
        }
        if (!empty($filters['user'])) {
            $stmtU = $this->db->prepare("SELECT CONCAT(prenom, ' ', nom) FROM users WHERE id = ?");
            $stmtU->execute([$filters['user']]);
            $filterTexts[] = "Auteur : " . $stmtU->fetchColumn();
        }
        $filtersApplied = empty($filterTexts) ? "Aucun" : implode(" | ", $filterTexts);

        // HTML
        $schoolName = htmlspecialchars($settings['school_name'] ?? '');
        $republic = htmlspecialchars($settings['school_republic'] ?? 'REPUBLIQUE DU CAMEROUN');
        $republicEn = htmlspecialchars($settings['school_republic_en'] ?? 'REPUBLIC OF CAMEROON');
        $motto = htmlspecialchars($settings['school_motto'] ?? 'Paix - Travail - Patrie');
        $mottoEn = htmlspecialchars($settings['school_motto_en'] ?? 'Peace - Work - Fatherland');
        $phone = htmlspecialchars($settings['school_phone'] ?? '');
        $city = htmlspecialchars($settings['school_city'] ?? '');
        $poBox = htmlspecialchars($settings['school_po_box'] ?? '');
        
        $contact = "TEL: " . $phone;
        if ($poBox) { $contact .= " | B.P.: " . $poBox; }
        $contact .= " | " . $city;
        $printDate = date('d/m/Y H:i');
        $currentUser = Session::get('user_prenom') . ' ' . Session::get('user_nom');

        $html = '
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>Rapport des Dépenses</title>
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
                    font-size: 8px;
                    font-weight: bold;
                    margin: 2px 0;
                    text-transform: uppercase;
                }
                .header-contact {
                    font-size: 7px;
                    margin: 2px 0;
                    text-transform: uppercase;
                }
                .logo-img {
                    max-width: 60px;
                    max-height: 60px;
                    object-fit: contain;
                }
                .school-name-row {
                    text-align: center;
                    margin-top: 5px;
                    border-bottom: 2px solid #000;
                    padding-bottom: 5px;
                }
                .school-name {
                    font-size: 14px;
                    font-weight: bold;
                    text-transform: uppercase;
                }
                .academic-year {
                    font-size: 10px;
                    margin-top: 2px;
                }
                .title-box {
                    text-align: center;
                    font-size: 12px;
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
                    padding: 5px;
                    text-align: left;
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
                .cancelled-row {
                    text-decoration: line-through;
                    color: #888;
                    background-color: #f9f9f9;
                }
            </style>
        </head>
        <body>
            <table class="header-table">
                <tr>
                    <td class="header-left">
                        <p class="header-line">' . $republic . '</p>
                        <p class="header-line">' . $motto . '</p>
                        <p class="header-contact">' . $contact . '</p>
                    </td>
                    <td class="header-center">';
        if ($logoBase64) {
            $html .= '<img class="logo-img" src="' . $logoBase64 . '" alt="Logo">';
        } else {
            $html .= '<div style="font-size: 8px; font-weight: bold; color: #888; border: 1px solid #ccc; width: 50px; height: 50px; line-height: 50px; margin: 0 auto; border-radius: 50%;">LOGO</div>';
        }
        $html .= '  </td>
                    <td class="header-right">
                        <p class="header-line">' . $republicEn . '</p>
                        <p class="header-line">' . $mottoEn . '</p>
                        <p class="header-contact">' . $contact . '</p>
                    </td>
                </tr>
            </table>

            <div class="school-name-row">
                <div class="school-name">' . $schoolName . '</div>
                <div class="academic-year">Année Académique : ' . htmlspecialchars($activeYear['nom'] ?? '') . '</div>
            </div>

            <div class="title-box">
                RAPPORT DE SUIVI DES DÉPENSES
            </div>

            <div class="stats-box">
                <table class="stats-table">
                    <tr>
                        <td><strong>Filtres appliqués :</strong> ' . htmlspecialchars($filtersApplied) . '</td>
                        <td><strong>Date de génération :</strong> ' . $printDate . '</td>
                    </tr>
                    <tr>
                        <td><strong>Généré par :</strong> ' . htmlspecialchars($currentUser) . '</td>
                        <td><strong>Nombre de dépenses :</strong> ' . $totalCount . '</td>
                    </tr>
                    <tr>
                        <td colspan="2"><strong>Montant Total (Actif) :</strong> ' . number_format($totalAmount, 0, '.', ' ') . ' FCFA</td>
                    </tr>
                </table>
            </div>

            <table class="table-list">
                <thead>
                    <tr>
                        <th style="width: 15%;">Référence</th>
                        <th style="width: 12%;">Date</th>
                        <th style="width: 18%;">Catégorie</th>
                        <th style="width: 35%;">Motif</th>
                        <th class="text-end" style="width: 20%;">Montant</th>
                    </tr>
                </thead>
                <tbody>';
        if (empty($expenses)) {
            $html .= '<tr><td colspan="5" class="text-center py-4">Aucune dépense enregistrée.</td></tr>';
        } else {
            foreach ($expenses as $row) {
                $classAttr = $row['status'] === 'cancelled' ? ' class="cancelled-row"' : '';
                $amountText = number_format((float)$row['amount'], 0, '.', ' ') . ' FCFA';
                if ($row['status'] === 'cancelled') {
                    $amountText .= ' (Annulé)';
                }
                $html .= '
                <tr' . $classAttr . '>
                    <td>' . htmlspecialchars($row['reference']) . '</td>
                    <td>' . date('d/m/Y', strtotime($row['expense_date'])) . '</td>
                    <td>' . htmlspecialchars($row['category_name']) . '</td>
                    <td>' . htmlspecialchars($row['motive']) . '</td>
                    <td class="text-end fw-bold">' . $amountText . '</td>
                </tr>';
            }
        }
        $html .= '
                </tbody>
                <tfoot>
                    <tr class="fw-bold" style="background-color: #f3f4f6;">
                        <td colspan="4" class="text-end">TOTAL ACTIF :</td>
                        <td class="text-end">' . number_format($totalAmount, 0, '.', ' ') . ' FCFA</td>
                    </tr>
                </tfoot>
            </table>
        </body>
        </html>
        ';

        // Rendre le PDF
        while (ob_get_level()) {
            ob_end_clean();
        }

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = "Rapport_Depenses_" . date('Ymd_His') . ".pdf";
        $dompdf->stream($filename, ["Attachment" => false]);
        exit;
    }

    /**
     * Liste des catégories de dépenses.
     */
    public function categories()
    {
        $categories = $this->db->query("SELECT * FROM expense_categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        include __DIR__ . '/../Views/expenses/categories.php';
    }

    /**
     * Enregistre une catégorie.
     */
    public function storeCategory()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', "Session expirée ou requête invalide.");
                header("Location: /expenses/categories");
                exit;
            }

            $name = trim((string)($_POST['name'] ?? ''));
            $userId = (int)Session::get('user_id');

            if (empty($name)) {
                Session::setFlash('error', "Le nom de la catégorie est obligatoire.");
                header("Location: /expenses/categories");
                exit;
            }

            // Vérifier les doublons
            $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM expense_categories WHERE name = ?");
            $stmtCheck->execute([$name]);
            if ((int)$stmtCheck->fetchColumn() > 0) {
                Session::setFlash('error', "Cette catégorie existe déjà.");
                header("Location: /expenses/categories");
                exit;
            }

            try {
                $this->db->beginTransaction();

                $stmt = $this->db->prepare("INSERT INTO expense_categories (name, active) VALUES (?, 1)");
                $stmt->execute([$name]);
                $catId = (int)$this->db->lastInsertId();

                $this->logExpenseAudit(null, $catId, $userId, 'create', null, ['name' => $name, 'active' => 1]);

                $this->db->commit();
                Session::setFlash('success', "Catégorie ajoutée avec succès.");
            } catch (Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                Session::setFlash('error', "Erreur lors de la création : " . $e->getMessage());
            }

            header("Location: /expenses/categories");
            exit;
        }
    }

    /**
     * Modifie le nom d'une catégorie.
     */
    public function updateCategory()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', "Session expirée ou requête invalide.");
                header("Location: /expenses/categories");
                exit;
            }

            $id = (int)($_POST['id'] ?? 0);
            $name = trim((string)($_POST['name'] ?? ''));
            $userId = (int)Session::get('user_id');

            if ($id <= 0 || empty($name)) {
                Session::setFlash('error', "Tous les champs sont obligatoires.");
                header("Location: /expenses/categories");
                exit;
            }

            // Récupérer ancienne valeur
            $stmtOld = $this->db->prepare("SELECT * FROM expense_categories WHERE id = ?");
            $stmtOld->execute([$id]);
            $oldCat = $stmtOld->fetch(PDO::FETCH_ASSOC);

            if (!$oldCat) {
                Session::setFlash('error', "Catégorie introuvable.");
                header("Location: /expenses/categories");
                exit;
            }

            // Vérifier les doublons
            $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM expense_categories WHERE name = ? AND id <> ?");
            $stmtCheck->execute([$name, $id]);
            if ((int)$stmtCheck->fetchColumn() > 0) {
                Session::setFlash('error', "Une autre catégorie possède déjà ce nom.");
                header("Location: /expenses/categories");
                exit;
            }

            try {
                $this->db->beginTransaction();

                $stmt = $this->db->prepare("UPDATE expense_categories SET name = ? WHERE id = ?");
                $stmt->execute([$name, $id]);

                $this->logExpenseAudit(null, $id, $userId, 'update', ['name' => $oldCat['name']], ['name' => $name]);

                $this->db->commit();
                Session::setFlash('success', "Catégorie renommée avec succès.");
            } catch (Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                Session::setFlash('error', "Erreur lors de la modification : " . $e->getMessage());
            }

            header("Location: /expenses/categories");
            exit;
        }
    }

    /**
     * Active/désactive une catégorie (pas de suppression physique).
     */
    public function toggleCategoryStatus()
    {
        $id = (int)($_GET['id'] ?? 0);
        $userId = (int)Session::get('user_id');

        if ($id <= 0) {
            Session::setFlash('error', "Identifiant invalide.");
            header("Location: /expenses/categories");
            exit;
        }

        $stmtOld = $this->db->prepare("SELECT * FROM expense_categories WHERE id = ?");
        $stmtOld->execute([$id]);
        $cat = $stmtOld->fetch(PDO::FETCH_ASSOC);

        if (!$cat) {
            Session::setFlash('error', "Catégorie introuvable.");
            header("Location: /expenses/categories");
            exit;
        }

        $newActive = $cat['active'] == 1 ? 0 : 1;
        $action = $newActive === 1 ? 'reactivate' : 'deactivate';

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("UPDATE expense_categories SET active = ? WHERE id = ?");
            $stmt->execute([$newActive, $id]);

            $this->logExpenseAudit(null, $id, $userId, $action, ['active' => (int)$cat['active']], ['active' => $newActive]);

            $this->db->commit();
            Session::setFlash('success', "Statut de la catégorie mis à jour.");
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            Session::setFlash('error', "Erreur : " . $e->getMessage());
        }

        header("Location: /expenses/categories");
        exit;
    }

    /**
     * Journal d'audit complet des dépenses.
     */
    public function auditLogs()
    {
        $sql = "
            SELECT l.*, CONCAT(u.prenom, ' ', u.nom) as user_name, u.role as user_role,
                   e.reference as expense_ref, ec.name as category_name
            FROM expense_logs l
            JOIN users u ON l.user_id = u.id
            LEFT JOIN expenses e ON l.expense_id = e.id
            LEFT JOIN expense_categories ec ON l.category_id = ec.id
            ORDER BY l.created_at DESC
            LIMIT 100
        ";
        $logs = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/expenses/audit.php';
    }

    /**
     * Enregistre une ligne dans le journal d'audit des dépenses.
     */
    private function logExpenseAudit(
        ?int $expenseId,
        ?int $categoryId,
        int $userId,
        string $action,
        $oldValue = null,
        $newValue = null,
        ?string $reason = null
    ): void {
        $oldValStr = is_array($oldValue) ? json_encode($oldValue, JSON_UNESCAPED_UNICODE) : (string)$oldValue;
        $newValStr = is_array($newValue) ? json_encode($newValue, JSON_UNESCAPED_UNICODE) : (string)$newValue;

        $stmt = $this->db->prepare("
            INSERT INTO expense_logs (expense_id, category_id, user_id, action, old_values, new_values, reason) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $expenseId ?: null,
            $categoryId ?: null,
            $userId,
            $action,
            $oldValue !== null ? $oldValStr : null,
            $newValue !== null ? $newValStr : null,
            $reason ?: null
        ]);
    }
}
