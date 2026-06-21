<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Core\PermissionManager;
use PDO;

/**
 * FinancialHistoryController
 * 
 * Permet de consulter l'historique complet des transactions et des modifications financières.
 */
class FinancialHistoryController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();

        // Sécurité RBAC : Accès réservé aux rôles financiers
        PermissionManager::requirePermission('view_financial_history');
    }

    /**
     * Affiche l'historique financier avec filtres.
     */
    public function index()
    {
        $filters = [
            'period' => $_GET['period'] ?? 'all',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? '',
            'entity_type' => $_GET['entity_type'] ?? 'all',
            'action' => $_GET['action'] ?? 'all',
            'teaching_type_id' => (int)($_GET['teaching_type_id'] ?? 0),
            'class_id' => (int)($_GET['class_id'] ?? 0)
        ];

        $history = $this->getFilteredHistory($filters);

        // Si requete AJAX, renvoyer la reponse en JSON pour fluidite UX
        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            header('Content-Type: application/json');
            
            // Helpers necessaires pour le rendu de tbody.php
            if (!function_exists('getFriendlyFieldName')) {
                function getFriendlyFieldName($key) {
                    $map = [
                        'amount' => __('col_amount'),
                        'amount_type' => __('type_field'),
                        'motive' => __('motive'),
                        'status' => __('status'),
                        'date_effet' => __('date_effet'),
                        'commentaire' => __('comment'),
                        'reference' => __('col_reference'),
                        'payment_method' => __('col_method'),
                        'payment_date' => __('col_date'),
                        'student_id' => __('student_id'),
                        'class_id' => __('class_id'),
                        'type' => __('fee_type')
                    ];
                    return $map[$key] ?? ucfirst($key);
                }
            }

            if (!function_exists('formatHistoryValue')) {
                function formatHistoryValue($key, $val) {
                    if ($key === 'amount_type') {
                        return $val === 'percentage' ? __('percentage_label') : __('amount_fixed_label');
                    }
                    if ($key === 'amount' && is_numeric($val)) {
                        return number_format((float)$val, 0, '.', ' ') . ' FCFA';
                    }
                    if ($key === 'status') {
                        return $val === 'active' ? __('active') : __('inactive');
                    }
                    if (is_array($val)) {
                        return json_encode($val, JSON_UNESCAPED_UNICODE);
                    }
                    return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
                }
            }

            ob_start();
            include __DIR__ . '/../Views/financial_history/tbody.php';
            $tbodyHtml = ob_get_clean();

            ob_start();
            include __DIR__ . '/../Views/financial_history/badges.php';
            $badgesHtml = ob_get_clean();

            echo json_encode([
                'success' => true,
                'tbody' => $tbodyHtml,
                'badges' => $badgesHtml,
                'count' => count($history)
            ]);
            exit;
        }

        $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $classes = $this->db->query("SELECT id, nom FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/financial_history/index.php';
    }

    /**
     * Exporte l'historique financier filtré en PDF.
     */
    public function print()
    {
        $filters = [
            'period' => $_GET['period'] ?? 'all',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? '',
            'entity_type' => $_GET['entity_type'] ?? 'all',
            'action' => $_GET['action'] ?? 'all',
            'teaching_type_id' => (int)($_GET['teaching_type_id'] ?? 0),
            'class_id' => (int)($_GET['class_id'] ?? 0)
        ];

        $history = $this->getFilteredHistory($filters);

        // Charger l'institution
        $settingsStore = new \App\Services\SettingsStore($this->db);
        $settings = $settingsStore->all();

        // Récupérer le logo Base64
        $logoManager = \App\Core\LogoManager::getInstance($this->db);
        $logoBase64 = $logoManager->hasLogo() ? $logoManager->getLogoBase64() : '';

        // Obtenir l'année académique active
        $acYearService = new \App\Services\AcademicYearService($this->db);
        $activeYear = $acYearService->getActiveYear();

        // Charger la liste des types d'enseignement pour la correspondance des filtres
        $teachingTypesList = $this->db->query("SELECT id, nom FROM teaching_types")->fetchAll(PDO::FETCH_ASSOC);
        $teachingTypesMap = [];
        foreach ($teachingTypesList as $tt) {
            $teachingTypesMap[$tt['id']] = $tt['nom'];
        }

        // Charger la liste des classes pour la correspondance des filtres
        $classesList = $this->db->query("SELECT id, nom FROM classes")->fetchAll(PDO::FETCH_ASSOC);
        $classesMap = [];
        foreach ($classesList as $c) {
            $classesMap[$c['id']] = $c['nom'];
        }

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

        // Helpers de formatage de vue
        $friendlyFieldName = function($key) {
            $map = [
                'amount' => __('col_amount'),
                'amount_type' => __('type_field'),
                'motive' => __('motive'),
                'status' => __('status'),
                'date_effet' => __('date_effet'),
                'commentaire' => __('comment'),
                'reference' => __('col_reference'),
                'payment_method' => __('col_method'),
                'payment_date' => __('col_date'),
                'student_id' => __('student_id'),
                'class_id' => __('class_id'),
                'type' => __('fee_type')
            ];
            return $map[$key] ?? ucfirst($key);
        };

        $formatHistoryValue = function($key, $val) {
            if ($key === 'amount_type') {
                return $val === 'percentage' ? __('percentage_label') : __('amount_fixed_label');
            }
            if ($key === 'amount' && is_numeric($val)) {
                return number_format((float)$val, 0, '.', ' ') . ' FCFA';
            }
            if ($key === 'status') {
                return $val === 'active' ? __('active') : __('inactive');
            }
            if (is_array($val)) {
                return json_encode($val, JSON_UNESCAPED_UNICODE);
            }
            return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
        };

        // Construction du document HTML pour Dompdf
        $html = '
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>' . __('financial_history_report') . '</title>
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
                    padding: 5px 4px;
                    text-align: left;
                    vertical-align: top;
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
                .badge-action {
                    padding: 2px 5px;
                    border-radius: 3px;
                    font-size: 8px;
                    font-weight: bold;
                    text-transform: uppercase;
                }
                .badge-create { background-color: #d1fae5; color: #065f46; }
                .badge-delete { background-color: #fee2e2; color: #991b1b; }
                .badge-status { background-color: #fef3c7; color: #92400e; }
                .badge-update { background-color: #dbeafe; color: #1e40af; }
                .badge-entity {
                    background-color: #f3f4f6;
                    color: #374151;
                    padding: 1px 4px;
                    border-radius: 3px;
                    font-size: 8px;
                    border: 1px solid #e5e7eb;
                }
                .change-item {
                    margin-bottom: 2px;
                }
                .change-key {
                    background-color: #f3f4f6;
                    padding: 1px 3px;
                    border-radius: 2px;
                    font-weight: bold;
                    font-size: 7.5px;
                    border: 1px solid #e5e7eb;
                }
                .change-val {
                    font-family: monospace;
                    font-size: 8px;
                }
            </style>
        </head>
        <body>
            <!-- Official Header -->
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
                ' . __('financial_history_report') . '
            </div>

            <!-- Stats/Filters Box -->
            <div class="stats-box">
                <table class="stats-table">
                    <tr>
                        <td><strong>Période :</strong> ';
        if ($filters['period'] === 'today') $html .= __('today');
        elseif ($filters['period'] === 'week') $html .= __('this_week');
        elseif ($filters['period'] === 'month') $html .= __('this_month');
        elseif ($filters['period'] === 'custom') $html .= __('custom_range') . ' (' . htmlspecialchars($filters['start_date']) . ' au ' . htmlspecialchars($filters['end_date']) . ')';
        else $html .= __('all_m');
        $html .= '      </td>
                        <td><strong>Type Entité :</strong> ' . ($filters['entity_type'] !== 'all' ? htmlspecialchars($filters['entity_type']) : __('all_entities')) . '</td>
                    </tr>
                    <tr>
                        <td><strong>Classe :</strong> ' . ($filters['class_id'] > 0 && isset($classesMap[$filters['class_id']]) ? htmlspecialchars($classesMap[$filters['class_id']]) : (__('all_classes') ?? 'Toutes les classes')) . '</td>
                        <td><strong>Action :</strong> ' . ($filters['action'] !== 'all' ? htmlspecialchars($filters['action']) : __('all_actions')) . '</td>
                    </tr>
                    <tr>
                        <td><strong>Type Enseignement :</strong> ' . ($filters['teaching_type_id'] > 0 && isset($teachingTypesMap[$filters['teaching_type_id']]) ? htmlspecialchars($teachingTypesMap[$filters['teaching_type_id']]) : __('all_m')) . '</td>
                        <td><strong>Nombre total d\'opérations :</strong> ' . count($history) . '</td>
                    </tr>
                    <tr>
                        <td><strong>Date d\'impression :</strong> ' . $printDate . '</td>
                        <td></td>
                    </tr>
                </table>
            </div>

            <!-- Table of History -->
            <table class="table-list">
                <thead>
                    <tr>
                        <th style="width: 13%;">' . __('col_date') . '</th>
                        <th style="width: 17%;">' . __('col_operator') . '</th>
                        <th style="width: 15%;">' . __('entity') . '</th>
                        <th style="width: 7%;">' . __('entity_id') . '</th>
                        <th style="width: 8%;">' . __('col_action') . '</th>
                        <th style="width: 20%;">' . __('old_values') . '</th>
                        <th style="width: 20%;">' . __('new_values') . '</th>
                    </tr>
                </thead>
                <tbody>';
        if (empty($history)) {
            $html .= '<tr><td colspan="7" class="text-center py-4" style="color: gray;">' . __('no_data') . '</td></tr>';
        } else {
            foreach ($history as $h) {
                $action = strtolower($h['action']);
                if ($action === 'create') {
                    $badgeClass = 'badge-create';
                    $actionTxt = __('action_create');
                } elseif ($action === 'delete') {
                    $badgeClass = 'badge-delete';
                    $actionTxt = __('action_delete');
                } elseif ($action === 'update_status') {
                    $badgeClass = 'badge-status';
                    $actionTxt = __('action_status');
                } else {
                    $badgeClass = 'badge-update';
                    $actionTxt = __('action_update');
                }

                $entityType = strtolower($h['entity_type']);
                if ($entityType === 'payment') {
                    $entityTxt = __('entity_payment');
                } elseif ($entityType === 'student_payment') {
                    $entityTxt = __('entity_student_payment');
                } elseif ($entityType === 'student_discount') {
                    $entityTxt = __('entity_student_discount');
                } elseif ($entityType === 'class_discount') {
                    $entityTxt = __('entity_class_discount');
                } elseif ($entityType === 'student_scholarship') {
                    $entityTxt = __('entity_student_scholarship');
                } elseif ($entityType === 'class_scholarship') {
                    $entityTxt = __('entity_class_scholarship');
                } elseif ($entityType === 'class_finance') {
                    $entityTxt = __('entity_class_finance');
                } else {
                    $entityTxt = htmlspecialchars($h['entity_type'], ENT_QUOTES, 'UTF-8');
                }

                $oldValDecoded = json_decode($h['old_value'] ?? '', true);
                $newValDecoded = json_decode($h['new_value'] ?? '', true);

                $oldFormatted = '';
                if ($oldValDecoded && is_array($oldValDecoded)) {
                    foreach ($oldValDecoded as $k => $v) {
                        if ($k !== 'tranches') {
                            $oldFormatted .= '<div class="change-item"><span class="change-key">' . htmlspecialchars($friendlyFieldName($k), ENT_QUOTES, 'UTF-8') . '</span> <span class="change-val">' . htmlspecialchars($formatHistoryValue($k, $v), ENT_QUOTES, 'UTF-8') . '</span></div>';
                        }
                    }
                } elseif ($h['old_value']) {
                    $oldFormatted = htmlspecialchars($h['old_value'], ENT_QUOTES, 'UTF-8');
                } else {
                    $oldFormatted = '-';
                }

                $newFormatted = '';
                if ($newValDecoded && is_array($newValDecoded)) {
                    foreach ($newValDecoded as $k => $v) {
                        if ($k !== 'tranches') {
                            $newFormatted .= '<div class="change-item"><span class="change-key">' . htmlspecialchars($friendlyFieldName($k), ENT_QUOTES, 'UTF-8') . '</span> <span class="change-val">' . htmlspecialchars($formatHistoryValue($k, $v), ENT_QUOTES, 'UTF-8') . '</span></div>';
                        }
                    }
                } elseif ($h['new_value']) {
                    $newFormatted = htmlspecialchars($h['new_value'], ENT_QUOTES, 'UTF-8');
                } else {
                    $newFormatted = '-';
                }

                $html .= '
                <tr>
                    <td>' . date('d/m/Y H:i', strtotime($h['event_date'])) . '</td>
                    <td>
                        <strong>' . htmlspecialchars(($h['user_nom'] ?: __('system')), ENT_QUOTES, 'UTF-8') . '</strong><br>
                        <span style="font-size: 7px; text-transform: uppercase; color: #555;">' . htmlspecialchars(($h['user_role'] ? __($h['user_role']) : __('automatic')), ENT_QUOTES, 'UTF-8') . '</span>
                    </td>
                    <td><span class="badge-entity">' . $entityTxt . '</span></td>
                    <td>#' . $h['entity_id'] . '</td>
                    <td><span class="badge-action ' . $badgeClass . '">' . $actionTxt . '</span></td>
                    <td>' . $oldFormatted . '</td>
                    <td>' . $newFormatted . '</td>
                </tr>';
            }
        }
        $html .= '
                </tbody>
            </table>
        </body>
        </html>';

        // Rendu PDF avec Dompdf
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream("historique_financier_" . date('Ymd_His') . ".pdf", ["Attachment" => false]);
        exit;
    }

    /**
     * Construit et exécute la requête filtrée de l'historique financier.
     */
    private function getFilteredHistory(array $filters): array
    {
        $where = [];
        $params = [];

        // Filtre par période/date
        $period = $filters['period'] ?? 'all';
        if ($period === 'today') {
            $where[] = "DATE(sub.event_date) = CURDATE()";
        } elseif ($period === 'week') {
            $where[] = "sub.event_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        } elseif ($period === 'month') {
            $where[] = "sub.event_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        } elseif ($period === 'custom') {
            $startDate = $filters['start_date'] ?? '';
            $endDate = $filters['end_date'] ?? '';
            if ($startDate !== '') {
                $where[] = "sub.event_date >= ?";
                $params[] = $startDate . ' 00:00:00';
            }
            if ($endDate !== '') {
                $where[] = "sub.event_date <= ?";
                $params[] = $endDate . ' 23:59:59';
            }
        }

        // Filtre par type d'entité
        $entityType = $filters['entity_type'] ?? 'all';
        if ($entityType !== 'all') {
            $where[] = "sub.entity_type = ?";
            $params[] = $entityType;
        }

        // Filtre par action
        $action = $filters['action'] ?? 'all';
        if ($action !== 'all') {
            if ($action === 'status') {
                $where[] = "sub.action = 'update_status'";
            } else {
                $where[] = "sub.action = ?";
                $params[] = $action;
            }
        }

        // Filtre par type d'enseignement
        $teachingTypeId = (int)($filters['teaching_type_id'] ?? 0);
        if ($teachingTypeId > 0) {
            $where[] = "sub.teaching_type_id = ?";
            $params[] = $teachingTypeId;
        }

        // Filtre par classe
        $classId = (int)($filters['class_id'] ?? 0);
        if ($classId > 0) {
            $where[] = "sub.class_id = ?";
            $params[] = $classId;
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        $sql = "
            SELECT * FROM (
                SELECT fh.*, u.nom as user_nom, u.prenom as user_prenom, u.role as user_role,
                       (
                           CASE fh.entity_type
                               WHEN 'payment' THEN (
                                   SELECT c.teaching_type_id 
                                   FROM student_payments sp 
                                   JOIN students s ON sp.student_id = s.id 
                                   JOIN classes c ON s.class_id = c.id 
                                   WHERE sp.id = fh.entity_id
                               )
                               WHEN 'student_payment' THEN (
                                   SELECT c.teaching_type_id 
                                   FROM student_payments sp 
                                   JOIN students s ON sp.student_id = s.id 
                                   JOIN classes c ON s.class_id = c.id 
                                   WHERE sp.id = fh.entity_id
                               )
                               WHEN 'student_discount' THEN (
                                   SELECT c.teaching_type_id 
                                   FROM student_discounts sd 
                                   JOIN students s ON sd.student_id = s.id 
                                   JOIN classes c ON s.class_id = c.id 
                                   WHERE sd.id = fh.entity_id
                               )
                               WHEN 'class_discount' THEN (
                                   SELECT c.teaching_type_id 
                                   FROM class_discounts cd 
                                   JOIN classes c ON cd.class_id = c.id 
                                   WHERE cd.id = fh.entity_id
                               )
                               WHEN 'student_scholarship' THEN (
                                   SELECT c.teaching_type_id 
                                   FROM student_scholarships ss 
                                   JOIN students s ON ss.student_id = s.id 
                                   JOIN classes c ON s.class_id = c.id 
                                   WHERE ss.id = fh.entity_id
                               )
                               WHEN 'class_scholarship' THEN (
                                   SELECT c.teaching_type_id 
                                   FROM class_scholarships cs 
                                   JOIN classes c ON cs.class_id = c.id 
                                   WHERE cs.id = fh.entity_id
                               )
                               WHEN 'class_finance' THEN (
                                   SELECT c.teaching_type_id 
                                   FROM classes c 
                                   WHERE c.id = fh.entity_id
                               )
                               ELSE NULL
                           END
                       ) as teaching_type_id,
                       (
                           CASE fh.entity_type
                               WHEN 'payment' THEN (
                                   SELECT s.class_id 
                                   FROM student_payments sp 
                                   JOIN students s ON sp.student_id = s.id 
                                   WHERE sp.id = fh.entity_id
                               )
                               WHEN 'student_payment' THEN (
                                   SELECT s.class_id 
                                   FROM student_payments sp 
                                   JOIN students s ON sp.student_id = s.id 
                                   WHERE sp.id = fh.entity_id
                               )
                               WHEN 'student_discount' THEN (
                                   SELECT s.class_id 
                                   FROM student_discounts sd 
                                   JOIN students s ON sd.student_id = s.id 
                                   WHERE sd.id = fh.entity_id
                               )
                               WHEN 'class_discount' THEN (
                                   SELECT cd.class_id 
                                   FROM class_discounts cd 
                                   WHERE cd.id = fh.entity_id
                               )
                               WHEN 'student_scholarship' THEN (
                                   SELECT s.class_id 
                                   FROM student_scholarships ss 
                                   JOIN students s ON ss.student_id = s.id 
                                   WHERE ss.id = fh.entity_id
                               )
                               WHEN 'class_scholarship' THEN (
                                   SELECT cs.class_id 
                                   FROM class_scholarships cs 
                                   WHERE cs.id = fh.entity_id
                               )
                               WHEN 'class_finance' THEN fh.entity_id
                               ELSE NULL
                           END
                       ) as class_id
                FROM financial_history fh
                LEFT JOIN users u ON fh.user_id = u.id
            ) as sub
            $whereClause
            ORDER BY sub.event_date DESC, sub.id DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
