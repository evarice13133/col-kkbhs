<?php
/** @var string $content Contenu de la page à injecter */
/** @var string $title Titre de la page */

$user_role = \App\Core\Session::get('user_role');
$user_name = \App\Core\Session::get('user_name', __('user_default'));
$user_initials = strtoupper(substr($user_name, 0, 1));
$app_lang = \App\Core\Session::get('app_lang', 'fr');

// Load settings for logo and school identity
$db = \App\Core\Database::getInstance()->getConnection();
$settingsStore = new \App\Services\SettingsStore($db);
$logoManager = \App\Core\LogoManager::getInstance($db);
$school_name = $settingsStore->get('school_name', 'NotesMaster');
$school_code = trim((string) $settingsStore->get('school_code', ''));
$school_identity = $school_code !== '' ? $school_code : $school_name;

// Récupération des données logo pour affichage fiable (base64 ou fallback)
$logoData = [
    'has_logo' => $logoManager->hasLogo(),
    'url' => $logoManager->getLogoUrl(),
    'base64' => $logoManager->hasLogo() ? $logoManager->getLogoBase64() : '',
    'fallback_letter' => $logoManager->getFallbackLetter()
];

$current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$current_uri = $_SERVER['REQUEST_URI'];

$isUrlActive = function ($itemUrl) use ($current_path, $current_uri) {
    if ($itemUrl === '/') {
        return $current_path === '/';
    }
    $itemPath = parse_url($itemUrl, PHP_URL_PATH);
    $itemQuery = parse_url($itemUrl, PHP_URL_QUERY);
    
    $pathMatch = ($current_path === $itemPath || strpos($current_path, $itemPath . '/') === 0);
    if (!$pathMatch) {
        return false;
    }
    
    if ($itemQuery) {
        parse_str($itemQuery, $itemParams);
        foreach ($itemParams as $k => $v) {
            if (!isset($_GET[$k]) || $_GET[$k] != $v) {
                return false;
            }
        }
        return true;
    } else {
        if ($itemPath === '/students' && ($current_path === '/students/create' || $current_path === '/students/non-inscrits' || (isset($_GET['only_mine']) && $_GET['only_mine'] == 1))) {
            return false;
        }
        if ($itemPath === '/users' && ($current_path === '/users/create-caissier' || $current_path === '/users/caissiers' || $current_path === '/users/toggle-status')) {
            return false;
        }
        if ($itemPath === '/bulletins' && strpos($current_path, '/bulletins/') === 0) {
            return false;
        }
        if ($itemPath === '/bulletins/discipline' && !in_array($current_path, ['/bulletins/discipline', '/bulletins/discipline/save'])) {
            return false;
        }
        if ($itemPath === '/honors' && strpos($current_path, '/honors/') === 0) {
            return false;
        }
        if ($itemPath === '/expenses' && in_array($current_path, ['/expenses/categories', '/expenses/audit'])) {
            return false;
        }
        if ($itemPath === '/proces-verbal' && strpos($current_path, '/proces-verbal/') === 0) {
            return false;
        }
        if ($itemPath === '/transcripts' && strpos($current_path, '/transcripts/') === 0) {
            return false;
        }
    }
    return true;
};

$isItemAllowed = function ($item) use ($user_role) {
    if (isset($item['permission']) && !empty($item['permission'])) {
        return \App\Core\PermissionManager::hasPermission($item['permission']);
    }
    if (isset($item['roles']) && is_array($item['roles'])) {
        return in_array($user_role, $item['roles'], true);
    }
    return true;
};

// Ribbon UI Definition (Inspired by Microsoft Word 365 with Rich Contextual Descriptions)
$ribbon_structure = [
    [
        'id' => 'tab-home',
        'title' => __('home'),
        'icon' => 'bi-house-door',
        'groups' => [
            [
                'title' => __('pilotage') ?? 'Pilotage',
                'items' => [
                    ['icon' => 'bi-speedometer2', 'label' => __('dashboard'), 'url' => '/', 'roles' => ['superadmin', 'admin', 'enseignant', 'caissier', 'comptable', 'it_manager'], 'desc' => 'Vue globale des indicateurs clés et statistiques de l\'établissement.']
                ]
            ],
            [
                'title' => __('enseignant') ?? 'Enseignant',
                'items' => [
                    ['icon' => 'bi-pencil-square', 'label' => __('enter_marks'), 'url' => '/notes', 'permission' => 'manage_marks', 'roles' => ['enseignant'], 'desc' => 'Saisie et validation des notes d\'évaluation par classe et matière.'],
                    ['icon' => 'bi-people', 'label' => __('my_students'), 'url' => '/students', 'permission' => 'view_students', 'roles' => ['enseignant'], 'desc' => 'Consulter la liste et le suivi individuel de vos élèves.'],
                    ['icon' => 'bi-question-circle', 'label' => __('help'), 'url' => '/documentation', 'roles' => ['enseignant'], 'desc' => 'Guides d\'utilisation et centre d\'assistance.'],
                ]
            ],
            [
                'title' => __('caisse') ?? 'Caisse',
                'items' => [
                    ['icon' => 'bi-credit-card', 'label' => __('payments_menu'), 'url' => '/payments', 'permission' => 'manage_payments', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable'], 'desc' => 'Enregistrement des versements de scolarité et impression des reçus.'],
                    ['icon' => 'bi-exclamation-triangle', 'label' => __('insolvent_title'), 'url' => '/school_fees/insolvables', 'permission' => 'view_financial_reports', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable'], 'desc' => 'Rapport des élèves en retard de paiement.'],
                    ['icon' => 'bi-door-open', 'label' => __('classes'), 'url' => '/classes', 'permission' => 'view_classes', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable'], 'desc' => 'Gestion et état d\'avancement des paiements par classe.']
                ]
            ]
        ]
    ],
    [
        'id' => 'tab-pilotage',
        'title' => __('centre_de_pilotage'),
        'icon' => 'bi-sliders2',
        'groups' => [
            [
                'title' => 'Structure Académique',
                'items' => [
                    ['icon' => 'bi-calendar-event', 'label' => __('academic_years'), 'url' => '/academic_years', 'permission' => 'manage_academic_years', 'roles' => ['superadmin', 'it_manager'], 'desc' => 'Configuration des années scolaires et périodes académiques.'],
                    ['icon' => 'bi-diagram-3', 'label' => __('teaching_types'), 'url' => '/teaching_types', 'permission' => 'manage_teaching_types', 'roles' => ['superadmin', 'admin'], 'desc' => 'Définition des types d\'enseignement (Général, Technique, etc.).'],
                    ['icon' => 'bi-bar-chart-steps', 'label' => __('levels') ?? 'Niveaux', 'url' => '/levels', 'permission' => 'manage_classes_structure', 'roles' => ['superadmin', 'admin'], 'desc' => 'Gestion des niveaux scolaires et coefficients.'],
                    ['icon' => 'bi-layers', 'label' => __('academic_cycles'), 'url' => '/cycles', 'permission' => 'manage_cycles', 'roles' => ['superadmin', 'admin'], 'desc' => 'Cycles d\'études (Premier cycle, Second cycle).'],
                    ['icon' => 'bi-grid-3x3-gap', 'label' => __('academic_sections'), 'url' => '/sections', 'permission' => 'manage_sections', 'roles' => ['superadmin', 'admin'], 'desc' => 'Sections francophones, anglophones et spécialités.'],
                    ['icon' => 'bi-building', 'label' => __('departments'), 'url' => '/departments', 'permission' => 'manage_departments', 'roles' => ['superadmin', 'admin', 'it_manager'], 'desc' => 'Organisation des départements académiques.'],
                ]
            ],
            [
                'title' => 'Système & Config',
                'items' => [
                    ['icon' => 'bi-gear', 'label' => __('settings'), 'url' => '/settings', 'permission' => 'manage_settings', 'roles' => ['superadmin', 'admin'], 'desc' => 'Identité de l\'école, logos, documents officiels et paramètres généraux.'],
                    ['icon' => 'bi-question-circle', 'label' => __('help'), 'url' => '/documentation', 'roles' => ['superadmin', 'admin', 'it_manager'], 'desc' => 'Documentation technique et manuels utilisateur.'],
                ]
            ]
        ]
    ],
    [
        'id' => 'tab-rh',
        'title' => __('ressources_humaines'),
        'icon' => 'bi-people',
        'groups' => [
            [
                'title' => 'Inscriptions & Élèves',
                'items' => [
                    ['icon' => 'bi-person-plus', 'label' => __('register_student_menu'), 'url' => '/students/create', 'permission' => 'manage_students', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable'], 'desc' => 'Inscrire un nouvel élève, attribuer une classe et générer un matricule.'],
                    ['icon' => 'bi-people', 'label' => __('registered_students_menu'), 'url' => '/students', 'permission' => 'view_students', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable'], 'desc' => 'Annuaire et dossiers complets de tous les élèves inscrits.'],
                    ['icon' => 'bi-person-dash', 'label' => __('unregistered_students_menu'), 'url' => '/students/non-inscrits', 'permission' => 'view_students', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable'], 'desc' => 'Élèves pré-inscrits en attente de régularisation.'],
                    ['icon' => 'bi-person-check', 'label' => __('my_registrations_menu'), 'url' => '/students?only_mine=1', 'permission' => 'view_students', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable'], 'desc' => 'Historique des inscriptions enregistrées par votre compte.'],
                ]
            ]
        ]
    ],
    [
        'id' => 'tab-users',
        'title' => __('users_management_menu'),
        'icon' => 'bi-person-gear',
        'groups' => [
            [
                'title' => 'Personnel & Comptes',
                'items' => [
                    ['icon' => 'bi-people-fill', 'label' => __('users'), 'url' => '/users', 'permission' => 'manage_users', 'roles' => ['superadmin', 'it_manager'], 'desc' => 'Création des comptes administrateurs, enseignants et gestionnaires.'],
                    ['icon' => 'bi-person-plus-fill', 'label' => __('manage_cashiers_menu'), 'url' => '/users/caissiers', 'permission' => 'manage_users', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable'], 'desc' => 'Gestion spécifique des caissiers et agents de saisie.'],
                    ['icon' => 'bi-person-badge', 'label' => __('teachers'), 'url' => '/teachers', 'permission' => 'manage_teachers', 'roles' => ['superadmin', 'admin', 'it_manager'], 'desc' => 'Attribution des matières et des classes au corps enseignant.'],
                ]
            ]
        ]
    ],
    [
        'id' => 'tab-finances',
        'title' => __('financial_management'),
        'icon' => 'bi-wallet2',
        'groups' => [
            [
                'title' => __('scolarite_menu'),
                'items' => [
                    ['icon' => 'bi-credit-card', 'label' => __('payments_menu'), 'url' => '/payments', 'permission' => 'manage_payments', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable'], 'desc' => 'Enregistrement des versements de scolarité et impression des reçus.'],
                    ['icon' => 'bi-table', 'label' => __('grille_title'), 'url' => '/school_fees/grille', 'permission' => 'view_class_finances', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable'], 'desc' => 'Grille tarifaire des frais de scolarité par classe.'],
                    ['icon' => 'bi-diagram-2', 'label' => __('tranches_menu'), 'url' => '/school_fees/tranches', 'permission' => 'edit_class_finances', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable'], 'desc' => 'Configuration des tranches et échéanciers de paiement.'],
                    ['icon' => 'bi-receipt-cutoff', 'label' => __('versements_menu'), 'url' => '/school_fees/versements', 'permission' => 'view_financial_history', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable'], 'desc' => 'Journal détaillé des reçus et opérations de caisse.'],
                    ['icon' => 'bi-exclamation-triangle', 'label' => __('insolvent_title'), 'url' => '/school_fees/insolvables', 'permission' => 'view_financial_reports', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable'], 'desc' => 'Rapport des élèves en retard de paiement.'],
                ]
            ],
            [
                'title' => __('discounts'),
                'items' => [
                    ['icon' => 'bi-percent', 'label' => __('discounts_granted'), 'url' => '/discounts', 'permission' => 'manage_discounts', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable'], 'desc' => 'Liste des remises exceptionnelles accordées.'],
                    ['icon' => 'bi-mortarboard', 'label' => __('scholarships'), 'url' => '/scholarships', 'permission' => 'manage_scholarships', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable'], 'desc' => 'Bourses d\'études attribuées aux élèves boursiers.'],
                    ['icon' => 'bi-tags', 'label' => __('discount_types_title'), 'url' => '/discount_types', 'permission' => 'manage_discounts', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable'], 'desc' => 'Types et règles d\'exonération tarifaire.'],
                ]
            ],
            [
                'title' => __('expenses_menu'),
                'items' => [
                    ['icon' => 'bi-journal-text', 'label' => __('financial_history'), 'url' => '/financial-history', 'permission' => 'view_financial_history', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable'], 'desc' => 'Historique comptable global des entrées et sorties.'],
                    ['icon' => 'bi-list-ul', 'label' => __('expenses_list'), 'url' => '/expenses', 'permission' => 'manage_fees', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable'], 'desc' => 'Saisie et approbation des dépenses d\'exploitation.'],
                    ['icon' => 'bi-tags-fill', 'label' => __('expense_categories'), 'url' => '/expenses/categories', 'permission' => 'manage_fees', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable'], 'desc' => 'Catégories budgétaires des dépenses.'],
                    ['icon' => 'bi-shield-check', 'label' => __('expense_audit'), 'url' => '/expenses/audit', 'permission' => 'view_system_logs', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable'], 'desc' => 'Piste d\'audit et contrôle financier.'],
                ]
            ]
        ]
    ],
    [
        'id' => 'tab-notes',
        'title' => __('gestion_des_notes'),
        'icon' => 'bi-journal-check',
        'groups' => [
            [
                'title' => 'Évaluations & Matières',
                'items' => [
                    ['icon' => 'bi-check2-square', 'label' => __('evaluations'), 'url' => '/sequences', 'permission' => 'manage_sequences', 'roles' => ['superadmin', 'admin'], 'desc' => 'Configuration des séquences et trimestres d\'évaluation.'],
                    ['icon' => 'bi-pencil-square', 'label' => __('enter_marks'), 'url' => '/notes', 'permission' => 'manage_marks', 'roles' => ['superadmin', 'admin'], 'desc' => 'Saisie centralisée des notes d\'évaluation par classe.'],
                    ['icon' => 'bi-door-open', 'label' => __('classes'), 'url' => '/classes', 'permission' => 'view_classes', 'roles' => ['superadmin', 'admin'], 'desc' => 'Aperçu des performances académiques par classe.'],
                    ['icon' => 'bi-book', 'label' => __('subjects'), 'url' => '/subjects', 'permission' => 'manage_subjects', 'roles' => ['superadmin', 'admin'], 'desc' => 'Programme d\'enseignement et matières dispensées.'],
                    ['icon' => 'bi-collection', 'label' => __('subject_groups') ?? 'Groupe de Modules', 'url' => '/subject-groups', 'permission' => 'manage_subjects', 'roles' => ['superadmin', 'admin'], 'desc' => 'Regroupement de matières en unités d\'enseignement (UE).'],
                    ['icon' => 'bi-shield-check', 'label' => __('discipline_management'), 'url' => '/bulletins/discipline', 'permission' => 'manage_absences', 'roles' => ['superadmin', 'admin'], 'desc' => 'Saisie des absences, blâmes et avertissements disciplinaires.'],
                ]
            ]
        ]
    ],
    [
        'id' => 'tab-print',
        'title' => __('print'),
        'icon' => 'bi-printer',
        'groups' => [
            [
                'title' => 'Édition & Documents',
                'items' => [
                    ['icon' => 'bi-file-earmark-pdf', 'label' => __('bulletins'), 'url' => '/bulletins', 'permission' => 'manage_bulletins', 'roles' => ['superadmin', 'admin'], 'desc' => 'Génération et impression des bulletins de notes périodiques.'],
                    ['icon' => 'bi-award', 'label' => __('honor_roll_title'), 'url' => '/honors', 'permission' => 'manage_bulletins', 'roles' => ['superadmin', 'admin'], 'desc' => 'Impression des tableaux d\'honneur et félicitations.'],
                    ['icon' => 'bi-file-earmark-text', 'label' => __('proces_verbaux'), 'url' => '/proces-verbal', 'permission' => 'manage_bulletins', 'roles' => ['superadmin', 'admin'], 'desc' => 'Édition des procès-verbaux de récapitulation annuelle.'],
                    ['icon' => 'bi-file-earmark-spreadsheet', 'label' => __('transcripts') ?? 'Relevé de Notes', 'url' => '/transcripts', 'permission' => 'view_transcripts', 'roles' => ['superadmin', 'admin'], 'desc' => 'Génération des relevés de notes consolidés.'],
                ]
            ]
        ]
    ]
];

// Calculate active Ribbon tab dynamically based on current URL route
$active_tab_id = 'tab-home';
foreach ($ribbon_structure as $tab) {
    foreach ($tab['groups'] as $group) {
        foreach ($group['items'] as $item) {
            if ($isItemAllowed($item) && $isUrlActive($item['url'])) {
                $active_tab_id = $tab['id'];
                break 3;
            }
        }
    }
}

// Flat authorized commands list for Command Palette JS
$authorized_commands = [];
foreach ($ribbon_structure as $tab) {
    foreach ($tab['groups'] as $group) {
        foreach ($group['items'] as $item) {
            if ($isItemAllowed($item)) {
                $authorized_commands[] = [
                    'tab' => $tab['title'],
                    'group' => $group['title'],
                    'label' => $item['label'],
                    'icon' => $item['icon'],
                    'url' => $item['url'],
                    'desc' => $item['desc'] ?? ''
                ];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $app_lang ?>" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        (function () {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();

        // --- TRANSLATIONS & COMMANDS FOR JAVASCRIPT ---
        window.NM_COMMANDS = <?= json_encode($authorized_commands, JSON_UNESCAPED_UNICODE) ?>;
        window.NM_I18N = {
            'confirmation': "<?= addslashes((string) __('confirmation')) ?>",
            'confirm': "<?= addslashes((string) __('confirm')) ?>",
            'cancel': "<?= addslashes((string) __('cancel')) ?>",
            'saving': "<?= addslashes((string) __('saving')) ?>",
            'action_forbidden': "<?= addslashes((string) __('action_forbidden')) ?>",
            'class_not_empty_error': "<?= addslashes((string) __('class_not_empty_error')) ?>",
            'warning_title': "<?= addslashes((string) __('warning_title')) ?>",
            'info_title': "<?= addslashes((string) __('info_title')) ?>",
            'error_title': "<?= addslashes((string) __('error_title')) ?>",
            'success_title': "<?= addslashes((string) __('success_title')) ?>"
        };
    </script>
    <title><?= (isset($title) ? $title . ' | ' : '') . __('app_name') ?> - <?= __('app_tagline') ?></title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= isset($meta_description) ? h($meta_description) : __('meta_description_default') ?>">
    <meta name="keywords" content="<?= isset($meta_keywords) ? h($meta_keywords) : __('meta_keywords_default') ?>">
    <link rel="canonical" href="https://copobimat.camertech.com<?= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?>">
    <meta name="author" content="NoteMaster">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://notemaster.camertech.com/">
    <meta property="og:title" content="<?= (isset($title) ? $title . ' | ' : '') . __('app_name') ?>">
    <meta property="og:description" content="<?= __('meta_description_default') ?>">
    <meta property="og:image" content="https://notemaster.camertech.com/public/img/og-image.jpg">

    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json"> 
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "NoteMaster",
      "operatingSystem": "All",
      "applicationCategory": "EducationalApplication",
      "description": "<?= addslashes((string) __('app_description')) ?>",
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "XAF"
      },
      "publisher": {
        "@type": "Organization",
        "name": "NoteMaster",
        "url": "https://notemaster.camertech.com",
        "logo": "https://notemaster.camertech.com/public/img/logo.png"
      }
    }
    </script>

    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <?php
    $asset_version = file_exists(__DIR__ . '/../../../public/css/modern-dashboard.css')
        ? filemtime(__DIR__ . '/../../../public/css/modern-dashboard.css')
        : '2.4.0';
    ?>
    <link rel="stylesheet" href="/public/css/modern-dashboard.css?v=<?= $asset_version ?>">
    <link rel="stylesheet" href="/public/css/alerts-premium.css?v=<?= $asset_version ?>">
    <link rel="stylesheet" href="/public/css/ux-improvements.css?v=<?= $asset_version ?>">
    <link rel="stylesheet" href="/public/css/topbar-onboarding.css?v=<?= $asset_version ?>">

    <style>
        :root {
            --primary-color: #7c3aed;
            --primary-rgb: 124, 58, 237;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        h1, h2, h3, h4, h5, h6, .page-title, .ribbon-brand-text {
            font-family: 'Outfit', sans-serif;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .dropdown-menu-modern {
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-radius: 16px;
            padding: 0.75rem;
            min-width: 240px;
        }

        .dropdown-item-modern {
            border-radius: 10px;
            padding: 0.6rem 1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
        }

        .dropdown-item-modern:hover {
            background: #f1f5f9;
        }

        /* Dark Mode Support for Dropdowns */
        [data-theme="dark"] .dropdown-menu-modern {
            background-color: #000000;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        [data-theme="dark"] .dropdown-item-modern {
            color: #ffffff;
        }

        [data-theme="dark"] .dropdown-item-modern:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        .btn-theme-soft {
            background: #f8fafc;
            color: #475569;
        }

        [data-theme="dark"] .btn-theme-soft {
            background: rgba(255, 255, 255, 0.1) !important;
            color: #f8fafc !important;
        }

        .user-profile-pill {
            background: #f8fafc;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        [data-theme="dark"] .user-profile-pill {
            background: rgba(255, 255, 255, 0.05) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }
    </style>
</head>

<body>

    <div class="dashboard-wrapper">
        <!-- MICROSOFT WORD STYLE RIBBON UI NAVIGATION -->
        <nav class="word-ribbon-container" id="ribbonNavContainer">
            
            <!-- 1. BARRE SUPÉRIEURE INDÉPENDANTE (Brand + QAT + Global Search + Utility Actions) -->
            <div class="ribbon-top-bar" id="ribbonTopBar">
                <!-- Gauche : Mobile Button + Logo + Quick Access Toolbar (QAT) + Global Search Input -->
                <div class="d-flex align-items-center gap-2 flex-grow-1 overflow-hidden" style="max-width: 750px;">
                    <!-- Mobile Menu Button (WCAG 44x44px Touch Target) -->
                    <?php if (\App\Core\Session::isLogged()): ?>
                        <button class="btn btn-theme-soft p-0 d-lg-none border-0 rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileRibbonDrawer" title="Menu Mobile" style="width: 44px; height: 44px;">
                            <i class="bi bi-list fs-3 text-main-theme"></i>
                        </button>
                    <?php endif; ?>

                    <!-- Brand / Logo -->
                    <a href="/" class="ribbon-brand flex-shrink-0 me-1 text-decoration-none" id="tourBrandLogo">
                        <?php if ($logoData['has_logo'] && !empty($logoData['base64'])): ?>
                            <div class="sidebar-logo-container" style="width: 32px; height: 32px;">
                                <img src="<?= htmlspecialchars($logoData['base64']) ?>" alt="Logo" class="sidebar-logo">
                            </div>
                        <?php elseif ($logoData['has_logo'] && !empty($logoData['url'])): ?>
                            <div class="sidebar-logo-container" style="width: 32px; height: 32px;">
                                <img src="<?= htmlspecialchars($logoData['url']) ?>" alt="Logo" class="sidebar-logo">
                            </div>
                        <?php else: ?>
                            <div class="logo-fallback-modern" style="width: 32px; height: 32px; font-size: 1.05rem;">
                                <?= htmlspecialchars($logoData['fallback_letter']) ?>
                            </div>
                        <?php endif; ?>
                        <span class="ribbon-brand-text d-none d-sm-inline fw-bold"><?= htmlspecialchars((string) $school_identity) ?></span>
                    </a>

                    <!-- MS Word Quick Access Toolbar (QAT - Barre d'Accès Rapide) -->
                    <?php if (\App\Core\Session::isLogged()): ?>
                        <div class="ribbon-qat-container d-none d-md-flex me-2 flex-shrink-0" id="tourQAT">
                            <a href="/" class="ribbon-qat-btn <?= $current_path === '/' ? 'active' : '' ?>" data-bs-toggle="tooltip" data-bs-html="true" title="<div class='tooltip-rich-title'><i class='bi bi-house-door'></i> Tableau de bord</div><div class='tooltip-rich-desc'>Vue d'ensemble et statistiques générales de l'école.</div>">
                                <i class="bi bi-house-door"></i>
                            </a>
                            <?php if (\App\Core\PermissionManager::hasPermission('manage_marks')): ?>
                                <a href="/notes" class="ribbon-qat-btn <?= strpos($current_path, '/notes') === 0 ? 'active' : '' ?>" data-bs-toggle="tooltip" data-bs-html="true" title="<div class='tooltip-rich-title'><i class='bi bi-pencil-square'></i> Saisie des Notes</div><div class='tooltip-rich-desc'>Saisir et valider les notes d'évaluation par classe.</div>">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (\App\Core\PermissionManager::hasPermission('manage_payments')): ?>
                                <a href="/payments" class="ribbon-qat-btn <?= strpos($current_path, '/payments') === 0 ? 'active' : '' ?>" data-bs-toggle="tooltip" data-bs-html="true" title="<div class='tooltip-rich-title'><i class='bi bi-credit-card'></i> Caisse & Versements</div><div class='tooltip-rich-desc'>Encaisser les frais de scolarité et délivrer les reçus.</div>">
                                    <i class="bi bi-credit-card"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (\App\Core\PermissionManager::hasPermission('manage_students')): ?>
                                <a href="/students/create" class="ribbon-qat-btn <?= strpos($current_path, '/students/create') === 0 ? 'active' : '' ?>" data-bs-toggle="tooltip" data-bs-html="true" title="<div class='tooltip-rich-title'><i class='bi bi-person-plus'></i> Inscription Élève</div><div class='tooltip-rich-desc'>Enregistrer un nouvel élève et lui attribuer une classe.</div>">
                                    <i class="bi bi-person-plus"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (\App\Core\PermissionManager::hasPermission('manage_bulletins')): ?>
                                <a href="/bulletins" class="ribbon-qat-btn <?= strpos($current_path, '/bulletins') === 0 ? 'active' : '' ?>" data-bs-toggle="tooltip" data-bs-html="true" title="<div class='tooltip-rich-title'><i class='bi bi-printer'></i> Impression Bulletins</div><div class='tooltip-rich-desc'>Générer les bulletins de notes périodiques officiels.</div>">
                                    <i class="bi bi-printer"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Global Search Trigger (Linear / VSCode Command Palette Trigger) -->
                    <?php if (\App\Core\Session::isLogged()): ?>
                        <!-- Desktop / Tablet Search Input Trigger -->
                        <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2 align-items-center flex-grow-1 cursor-pointer d-none d-sm-flex" id="openCmdPaletteTrigger" style="border: 1px solid var(--border-color) !important; max-width: 340px; min-height: 36px; cursor: pointer;">
                            <span class="input-group-text border-0 bg-transparent text-primary p-1 ps-1">
                                <i class="bi bi-search" style="font-size: 0.85rem;"></i>
                            </span>
                            <input type="text" class="form-control border-0 bg-transparent shadow-none py-1 text-main cursor-pointer"
                                placeholder="Rechercher... (Ctrl+K)" readonly style="font-size: 0.8rem; height: 32px; cursor: pointer;">
                            <span class="badge bg-secondary bg-opacity-10 text-muted border rounded-pill extra-small px-1-5 py-0 me-1 d-none d-md-inline" style="font-size: 0.65rem;">⌘K</span>
                        </div>
                        <!-- Mobile Search Icon Button (WCAG Touch Target 44x44px) -->
                        <button type="button" class="btn btn-theme-soft rounded-circle d-flex d-sm-none align-items-center justify-content-center p-0 border-0 flex-shrink-0"
                                id="openCmdPaletteTriggerMobile" title="Rechercher une commande (Ctrl+K)" style="width: 44px; height: 44px;">
                            <i class="bi bi-search fs-5 text-primary"></i>
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Droite : Onboarding Progress Pill + Primary CTA + Notification Bell + Theme Switcher + User Account -->
                <div class="d-flex align-items-center gap-1-5 gap-sm-2 flex-shrink-0">
                    <?php if (\App\Core\Session::isLogged()): ?>
                        <!-- 1. Pill Badge Widget de Progression (Checklist Interactive Popover) - Tablet/Desktop -->
                        <div class="dropdown me-1 d-none d-md-block" id="onboardingProgressPillDropdown">
                            <div class="onboarding-pill-badge" data-bs-toggle="dropdown" aria-expanded="false" title="Progression de la configuration de votre espace">
                                <span class="pill-sparkle">✨</span>
                                <span id="onboardingPillText">⚡ 0% configuré</span>
                                <i class="bi bi-chevron-down extra-small opacity-75 ms-1"></i>
                            </div>
                            <div class="dropdown-menu dropdown-menu-end onboarding-checklist-menu p-3">
                                <div class="onboarding-checklist-header d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="fw-bold m-0 fs-6 text-main-theme">Guide d'Onboarding</h6>
                                        <small class="text-muted" id="onboardingChecklistRemaining">Étapes restantes</small>
                                    </div>
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-1 extra-small">Setup</span>
                                </div>
                                <div id="onboardingChecklistContainer" class="d-flex flex-column gap-1 my-2">
                                    <!-- Dynamic Checklist items rendered via JS -->
                                </div>
                                <div class="mt-2 pt-2 border-top text-center">
                                    <a href="/documentation" class="text-decoration-none extra-small text-muted hover-opacity-100">
                                        <i class="bi bi-question-circle me-1"></i> Besoin d'aide pour configurer ?
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Bouton CTA Dynamique Contextuel -->
                        <a href="/settings" class="btn-onboarding-cta pulse-glow me-1 d-none d-lg-inline-flex" id="onboardingPrimaryCTA">
                            <i class="bi bi-rocket-takeoff"></i> Configurer maintenant
                        </a>

                        <!-- 3. Smart Notification Bell Dropdown (44x44px Touch Target) -->
                        <div class="dropdown" id="onboardingBellDropdown">
                            <button type="button" class="btn btn-theme-soft notification-bell-btn rounded-circle d-flex align-items-center justify-content-center p-0 border-0 shadow-sm transition-all"
                                    data-bs-toggle="dropdown" aria-expanded="false" title="Notifications & Conseils Onboarding" style="width: 44px; height: 44px;">
                                <i class="bi bi-bell fs-5 text-main-theme"></i>
                                <span class="notification-bell-badge" id="onboardingBellBadge"></span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end notification-drawer-menu shadow-lg border-0 p-3 mt-2">
                                <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
                                    <h6 class="fw-bold m-0 fs-6 text-main-theme">Notifications & Conseils</h6>
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill extra-small">Onboarding</span>
                                </div>
                                <div class="notification-item-card">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="p-2 rounded-circle bg-primary bg-opacity-10 text-primary flex-shrink-0">
                                            <i class="bi bi-hand-thumbs-up fs-6"></i>
                                        </div>
                                        <div>
                                            <span class="fw-semibold d-block text-main-theme" style="font-size: 0.82rem;">Bienvenue dans NoteMaster !</span>
                                            <p class="text-muted extra-small mb-1">Découvrez les 5 étapes simples pour configurer votre établissement.</p>
                                            <a href="javascript:void(0)" onclick="TopBarOnboarding.updateUI()" class="extra-small text-primary fw-semibold text-decoration-none">
                                                Voir la checklist →
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="notification-item-card">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="p-2 rounded-circle bg-info bg-opacity-10 text-info flex-shrink-0">
                                            <i class="bi bi-lightning-charge fs-6"></i>
                                        </div>
                                        <div>
                                            <span class="fw-semibold d-block text-main-theme" style="font-size: 0.82rem;">Conseil Pro : Raccourcis</span>
                                            <p class="text-muted extra-small mb-0">Utilisez <kbd class="extra-small">Cmd+K</kbd> pour naviguer instantanément à travers l'application.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Interactive Guided Tour Button (44x44px Touch Target) -->
                        <button type="button" class="btn btn-theme-soft rounded-circle d-none d-sm-flex align-items-center justify-content-center p-0 border-0 shadow-sm transition-all"
                                id="startGuidedTour" title="Découvrir la Navigation (Visite Guidée)" style="width: 44px; height: 44px;">
                            <i class="bi bi-compass fs-5 text-primary"></i>
                        </button>
                    <?php endif; ?>

                    <!-- Theme Toggle (44x44px Touch Target - Hidden on mobile, managed in User Avatar Menu & Drawer) -->
                    <button class="theme-toggle-btn btn btn-theme-soft rounded-circle d-none d-sm-flex align-items-center justify-content-center p-0 border-0 shadow-sm transition-all"
                            id="themeToggle" title="<?= __('change_theme') ?>" style="width: 44px; height: 44px;">
                        <i class="bi bi-moon-stars fs-5 text-main-theme"></i>
                    </button>

                    <!-- Compact User Account Avatar Dropdown (44x44px Touch Target - Single Source of Truth for User Preferences) -->
                    <?php if (\App\Core\Session::isLogged()): ?>
                        <div class="dropdown" id="tourUserAccount">
                            <a href="#"
                                class="user-avatar-btn d-flex align-items-center justify-content-center text-decoration-none"
                                data-bs-toggle="dropdown" aria-expanded="false"
                                data-bs-placement="bottom" title="<?= h($user_name) ?> (<?= h(__($user_role)) ?>)"
                                style="width: 44px; height: 44px;">
                                <?= $user_initials ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2" style="min-width: 260px;">
                                <li>
                                    <div class="user-dropdown-header-card d-flex align-items-center gap-2 p-2 rounded-3 bg-card border mb-2">
                                        <div class="user-avatar bg-primary text-white d-flex align-items-center justify-content-center rounded-circle fw-bold shadow-sm flex-shrink-0"
                                            style="width: 40px; height: 40px; font-size: 1.1rem;">
                                            <?= $user_initials ?>
                                        </div>
                                        <div class="d-flex flex-column text-start overflow-hidden">
                                            <span class="text-main-theme fw-bold text-truncate lh-1" style="font-size: 0.9rem;"><?= h($user_name) ?></span>
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill mt-1 w-auto align-self-start extra-small"><?= h(__($user_role)) ?></span>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-modern py-2" href="/profile">
                                        <i class="bi bi-person text-primary fs-5 me-2"></i> <?= __('my_profile') ?>
                                    </a>
                                </li>
                                <?php if (in_array($user_role, ['superadmin', 'admin'])): ?>
                                    <li>
                                        <a class="dropdown-item dropdown-item-modern py-2" href="/settings">
                                            <i class="bi bi-gear text-secondary fs-5 me-2"></i> <?= __('settings') ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <a class="dropdown-item dropdown-item-modern py-2" href="javascript:void(0)" onclick="document.getElementById('themeToggle').click();">
                                        <i class="bi bi-moon-stars text-warning fs-5 me-2"></i> <?= __('change_theme') ?>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li><h6 class="dropdown-header small text-uppercase fw-bold text-muted"><?= __('language') ?></h6></li>
                                <li>
                                    <a class="dropdown-item dropdown-item-modern py-2 <?= $app_lang === 'fr' ? 'active' : '' ?>"
                                        href="javascript:void(0)" onclick="UX.switchLanguage('fr')">
                                        <span class="fs-5 me-2">🇫🇷</span> Français
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-modern py-2 <?= $app_lang === 'en' ? 'active' : '' ?>"
                                        href="javascript:void(0)" onclick="UX.switchLanguage('en')">
                                        <span class="fs-5 me-2">🇺🇸</span> English
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <a class="dropdown-item dropdown-item-modern py-2 text-danger" href="/logout">
                                        <i class="bi bi-box-arrow-right fs-5 me-2"></i> <?= __('logout') ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="d-flex align-items-center gap-2">
                            <a href="/login" class="btn btn-primary rounded-pill px-3 py-2 btn-sm fw-bold shadow-sm">
                                <?= __('login') ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Bottom Micro Progress Line -->
                <?php if (\App\Core\Session::isLogged()): ?>
                    <div class="topbar-progress-container" title="Progression de la configuration de votre espace">
                        <div class="topbar-progress-line" id="topbarProgressLine" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Onboarding Contextual Banner -->
            <?php if (\App\Core\Session::isLogged()): ?>
                <div class="onboarding-context-banner" id="onboardingContextBanner" style="display: none;">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fs-5">✨</span>
                        <span id="onboardingBannerText">Bienvenue 👋 Commençons la configuration de votre espace.</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="banner-dismiss-btn" id="dismissOnboardingBanner" title="Masquer cette bannière">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 2. BARRE D'ONGLETS RIBBON DÉDIÉE (Pleine Largeur - Exclusivité Menus Métier) -->
            <?php if (\App\Core\Session::isLogged()): ?>
                <div class="ribbon-tabs-bar border-bottom d-none d-lg-flex px-3" id="tourRibbonTabs">
                    <div class="ribbon-tabs-wrapper flex-grow-1" id="ribbonTabsWrapper">
                        <?php foreach ($ribbon_structure as $tab): ?>
                            <?php
                            $tab_accessible_count = 0;
                            foreach ($tab['groups'] as $group) {
                                foreach ($group['items'] as $item) {
                                    if ($isItemAllowed($item)) {
                                        $tab_accessible_count++;
                                    }
                                }
                            }
                            ?>
                            <?php if ($tab_accessible_count > 0): ?>
                                <button type="button" 
                                        class="ribbon-tab-btn <?= $tab['id'] === $active_tab_id ? 'active' : '' ?>" 
                                        data-tab-target="#<?= $tab['id'] ?>">
                                    <i class="bi <?= $tab['icon'] ?>"></i>
                                    <span><?= htmlspecialchars($tab['title']) ?></span>
                                    <?php if ($tab_accessible_count > 10): ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill ms-1 extra-small"><?= $tab_accessible_count ?></span>
                                    <?php endif; ?>
                                </button>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 3. BARRE D'OUTILS COMMANDES DU RUBAN (DISSOLUTION DU DROPDOWN & ÉCLATEMENT VISUEL AU MÊME NIVEAU) -->
                <div class="ribbon-toolbar-container d-none d-lg-flex" id="ribbonToolbarContainer">
                    <div class="d-flex align-items-stretch flex-grow-1 overflow-x-auto" id="ribbonPanesWrapper" style="scrollbar-width: thin;">
                        <?php foreach ($ribbon_structure as $tab): ?>
                            <?php
                            $tab_accessible_items = [];
                            foreach ($tab['groups'] as $group) {
                                foreach ($group['items'] as $item) {
                                    if ($isItemAllowed($item)) {
                                        $tab_accessible_items[] = $item;
                                    }
                                }
                            }
                            $total_tab_items = count($tab_accessible_items);
                            ?>
                            <?php if ($total_tab_items > 0): ?>
                                <div class="ribbon-tab-pane <?= $tab['id'] === $active_tab_id ? 'active' : '' ?>" id="<?= $tab['id'] ?>">
                                    <?php if ($total_tab_items <= 15): ?>
                                        <!-- CAS 1 : <= 15 SOUS-MENUS -> AFFICHAGE HARMONISÉ EN GROUPES RIBBON DIRECTS -->
                                        <?php foreach ($tab['groups'] as $group): ?>
                                            <?php
                                            $group_visible_items = array_filter($group['items'], fn($i) => $isItemAllowed($i));
                                            ?>
                                            <?php if (count($group_visible_items) > 0): ?>
                                                <div class="ribbon-group">
                                                    <div class="ribbon-group-items">
                                                        <?php foreach ($group_visible_items as $item): ?>
                                                            <?php $isActive = $isUrlActive($item['url']); ?>
                                                            <a href="<?= $item['url'] ?>" 
                                                               class="ribbon-btn-large <?= $isActive ? 'active' : '' ?>" 
                                                               data-bs-toggle="tooltip" data-bs-html="true"
                                                               title="<div class='tooltip-rich-title'><i class='bi <?= $item['icon'] ?>'></i> <?= htmlspecialchars($item['label']) ?></div><div class='tooltip-rich-desc'><?= htmlspecialchars($item['desc'] ?? '') ?></div>">
                                                                <i class="bi <?= $item['icon'] ?>"></i>
                                                                <span><?= htmlspecialchars($item['label']) ?></span>
                                                            </a>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <div class="ribbon-group-title"><?= htmlspecialchars($group['title']) ?></div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <!-- CAS 2 : > 15 SOUS-MENUS -> ÉCLATEMENT VISUEL AU MÊME NIVEAU -->
                                        <div class="d-flex align-items-stretch gap-2 flex-grow-1 overflow-x-auto" style="scrollbar-width: thin;">
                                            <?php foreach ($tab['groups'] as $group): ?>
                                                <?php
                                                $group_visible_items = array_filter($group['items'], fn($i) => $isItemAllowed($i));
                                                ?>
                                                <?php if (count($group_visible_items) > 0): ?>
                                                    <div class="ribbon-group-exploded">
                                                        <div class="ribbon-group-items">
                                                            <?php foreach ($group_visible_items as $item): ?>
                                                                <?php $isActive = $isUrlActive($item['url']); ?>
                                                                <a href="<?= $item['url'] ?>" 
                                                                   class="ribbon-btn-large <?= $isActive ? 'active' : '' ?>" 
                                                                   data-bs-toggle="tooltip" data-bs-html="true"
                                                                   title="<div class='tooltip-rich-title'><i class='bi <?= $item['icon'] ?>'></i> <?= htmlspecialchars($item['label']) ?></div><div class='tooltip-rich-desc'><?= htmlspecialchars($item['desc'] ?? '') ?></div>">
                                                                    <i class="bi <?= $item['icon'] ?>"></i>
                                                                    <span><?= htmlspecialchars($item['label']) ?></span>
                                                                </a>
                                                            <?php endforeach; ?>
                                                        </div>
                                                        <div class="d-flex align-items-center justify-content-between pt-1 border-top border-opacity-10 mt-1">
                                                            <span class="ribbon-group-exploded-badge">
                                                                <i class="bi bi-folder2-open"></i> <?= htmlspecialchars($group['title']) ?>
                                                            </span>
                                                            <span class="extra-small text-muted fw-bold"><?= count($group_visible_items) ?> actions</span>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <!-- Ribbon Collapse/Expand Pin Button (MS Word Style) -->
                    <button type="button" class="ribbon-toggle-btn ms-2" id="ribbonCollapseToggle" title="Réduire/Étendre le Ruban">
                        <i class="bi bi-chevron-up"></i>
                    </button>
                </div>
            <?php endif; ?>
        </nav>

        <!-- Mobile Navigation Offcanvas Drawer (Canva SaaS Inspired - Navigation Focus & One-Handed UX) -->
        <?php if (\App\Core\Session::isLogged()): ?>
            <div class="offcanvas offcanvas-start ribbon-mobile-drawer shadow-lg border-0" tabindex="-1" id="mobileRibbonDrawer" aria-labelledby="mobileRibbonDrawerLabel" style="width: 320px; max-width: 88vw;">
                <!-- Offcanvas Header: Canva Workspace Banner -->
                <div class="offcanvas-header flex-column align-items-stretch border-bottom pb-3 pt-3 px-3 position-relative" style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.12) 0%, rgba(59, 130, 246, 0.08) 100%);">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2-5 overflow-hidden">
                            <?php if ($logoData['has_logo'] && !empty($logoData['base64'])): ?>
                                <img src="<?= htmlspecialchars($logoData['base64']) ?>" alt="Logo" style="width: 36px; height: 36px; object-fit: contain;" class="rounded-2 shadow-sm">
                            <?php elseif ($logoData['has_logo'] && !empty($logoData['url'])): ?>
                                <img src="<?= htmlspecialchars($logoData['url']) ?>" alt="Logo" style="width: 36px; height: 36px; object-fit: contain;" class="rounded-2 shadow-sm">
                            <?php else: ?>
                                <div class="logo-fallback-modern" style="width: 36px; height: 36px; font-size: 1.1rem;">
                                    <?= htmlspecialchars($logoData['fallback_letter']) ?>
                                </div>
                            <?php endif; ?>
                            <div class="d-flex flex-column text-start overflow-hidden">
                                <h5 class="offcanvas-title fw-bold fs-6 text-main-theme m-0 text-truncate" id="mobileRibbonDrawerLabel"><?= htmlspecialchars((string) $school_identity) ?></h5>
                                <span class="extra-small text-primary fw-semibold" style="font-size: 0.7rem;">Espace de gestion</span>
                            </div>
                        </div>
                        <button type="button" class="btn-close text-reset p-2" data-bs-dismiss="offcanvas" aria-label="Fermer" style="width: 48px; height: 48px;"></button>
                    </div>

                    <!-- Canva Workspace Context Pill -->
                    <div class="canva-workspace-pill p-2 rounded-3 d-flex align-items-center justify-content-between mt-1" style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(124, 58, 237, 0.2);">
                        <div class="d-flex align-items-center gap-2 overflow-hidden">
                            <span class="fs-6">🎓</span>
                            <span class="fw-semibold text-main-theme extra-small text-truncate"><?= h($user_name) ?></span>
                        </div>
                        <span class="badge bg-primary text-white rounded-pill px-2 py-1 extra-small" style="font-size: 0.65rem;"><?= h(__($user_role)) ?></span>
                    </div>
                </div>

                <!-- Real-time Filter Search Box inside Drawer (Module Filter Only) -->
                <div class="px-3 pt-3 pb-2 border-bottom bg-card">
                    <div class="input-group input-group-sm rounded-pill border overflow-hidden bg-body" style="min-height: 40px;">
                        <span class="input-group-text border-0 bg-transparent text-muted ps-3 pe-1">
                            <i class="bi bi-search" style="font-size: 0.85rem;"></i>
                        </span>
                        <input type="text" id="mobileDrawerSearch" class="form-control border-0 shadow-none py-2 text-main bg-transparent" placeholder="Filtrer les modules..." style="font-size: 0.84rem;">
                    </div>
                </div>

                <!-- Accordion Navigation Body (Main Modules) -->
                <div class="offcanvas-body p-2" id="mobileDrawerNavBody" style="overflow-y: auto; scrollbar-width: thin;">
                    <div class="accordion accordion-flush" id="mobileRibbonAccordion">
                        <?php $tabIndex = 0; ?>
                        <?php foreach ($ribbon_structure as $tab): ?>
                            <?php
                            $tab_items = [];
                            foreach ($tab['groups'] as $group) {
                                foreach ($group['items'] as $item) {
                                    if ($isItemAllowed($item)) {
                                        $tab_items[] = array_merge($item, ['group_title' => $group['title']]);
                                    }
                                }
                            }
                            $hasActiveItem = false;
                            foreach ($tab_items as $item) {
                                if ($isUrlActive($item['url'])) {
                                    $hasActiveItem = true;
                                    break;
                                }
                            }
                            $tabIndex++;
                            $collapseId = "mobileTabCollapse_" . $tabIndex;
                            $headingId = "mobileTabHeading_" . $tabIndex;
                            ?>
                            <?php if (count($tab_items) > 0): ?>
                                <div class="accordion-item border-0 mb-1 rounded-3 overflow-hidden mobile-nav-group-item">
                                    <h2 class="accordion-header" id="<?= $headingId ?>">
                                        <button class="accordion-button <?= $hasActiveItem ? '' : 'collapsed' ?> py-2-5 px-3 rounded-3 fw-bold text-main-theme shadow-none bg-transparent" 
                                                type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#<?= $collapseId ?>" 
                                                aria-expanded="<?= $hasActiveItem ? 'true' : 'false' ?>" 
                                                aria-controls="<?= $collapseId ?>"
                                                style="font-size: 0.9rem; min-height: 48px;">
                                            <i class="bi <?= $tab['icon'] ?> text-primary fs-5 me-2 flex-shrink-0"></i>
                                            <span class="flex-grow-1 text-truncate"><?= htmlspecialchars($tab['title']) ?></span>
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill me-2 extra-small"><?= count($tab_items) ?></span>
                                        </button>
                                    </h2>
                                    <div id="<?= $collapseId ?>" 
                                         class="accordion-collapse collapse <?= $hasActiveItem ? 'show' : '' ?>" 
                                         aria-labelledby="<?= $headingId ?>" 
                                         data-bs-parent="#mobileRibbonAccordion">
                                        <div class="accordion-body p-1 ps-2">
                                            <div class="d-flex flex-column gap-1">
                                                <?php foreach ($tab_items as $item): ?>
                                                    <?php $isActive = $isUrlActive($item['url']); ?>
                                                    <a href="<?= $item['url'] ?>" 
                                                       class="mobile-drawer-link <?= $isActive ? 'active' : '' ?> d-flex align-items-center justify-content-between p-2 rounded-3 text-decoration-none text-main transition-all"
                                                       data-menu-label="<?= strtolower(htmlspecialchars($item['label'] . ' ' . $tab['title'])) ?>"
                                                       style="min-height: 44px;">
                                                        <div class="d-flex align-items-center gap-2-5 overflow-hidden">
                                                            <div class="mobile-drawer-icon-box rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 <?= $isActive ? 'bg-primary text-white shadow-sm' : 'bg-primary bg-opacity-10 text-primary' ?>"
                                                                 style="width: 34px; height: 34px; font-size: 0.9rem;">
                                                                <i class="bi <?= $item['icon'] ?>"></i>
                                                            </div>
                                                            <span class="fw-medium text-truncate" style="font-size: 0.86rem;"><?= htmlspecialchars($item['label']) ?></span>
                                                        </div>
                                                        <?php if ($isActive): ?>
                                                            <i class="bi bi-check-circle-fill text-primary fs-6 me-1 flex-shrink-0"></i>
                                                        <?php else: ?>
                                                            <i class="bi bi-chevron-right extra-small text-muted flex-shrink-0 opacity-50"></i>
                                                        <?php endif; ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <!-- Section "Plus..." (Raccourcis & Assistance Secondaires) -->
                        <div class="accordion-item border-0 mt-2 rounded-3 overflow-hidden mobile-nav-group-item">
                            <h2 class="accordion-header" id="mobileTabHeading_Plus">
                                <button class="accordion-button collapsed py-2-5 px-3 rounded-3 fw-bold text-secondary shadow-none bg-transparent" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#mobileTabCollapse_Plus" 
                                        aria-expanded="false" 
                                        aria-controls="mobileTabCollapse_Plus"
                                        style="font-size: 0.88rem; min-height: 48px;">
                                    <i class="bi bi-three-dots text-secondary fs-5 me-2 flex-shrink-0"></i>
                                    <span class="flex-grow-1 text-truncate">Plus & Assistance</span>
                                </button>
                            </h2>
                            <div id="mobileTabCollapse_Plus" 
                                 class="accordion-collapse collapse" 
                                 aria-labelledby="mobileTabHeading_Plus" 
                                 data-bs-parent="#mobileRibbonAccordion">
                                <div class="accordion-body p-1 ps-2">
                                    <div class="d-flex flex-column gap-1">
                                        <a href="/documentation" 
                                           class="mobile-drawer-link d-flex align-items-center justify-content-between p-2 rounded-3 text-decoration-none text-main transition-all"
                                           data-menu-label="aide documentation guides" style="min-height: 44px;">
                                            <div class="d-flex align-items-center gap-2-5 overflow-hidden">
                                                <div class="mobile-drawer-icon-box rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center flex-shrink-0"
                                                     style="width: 34px; height: 34px; font-size: 0.9rem;">
                                                    <i class="bi bi-question-circle"></i>
                                                </div>
                                                <span class="fw-medium text-truncate" style="font-size: 0.86rem;"><?= __('help') ?> & Documentation</span>
                                            </div>
                                            <i class="bi bi-chevron-right extra-small text-muted flex-shrink-0 opacity-50"></i>
                                        </a>
                                        <a href="/profile" 
                                           class="mobile-drawer-link d-flex align-items-center justify-content-between p-2 rounded-3 text-decoration-none text-main transition-all"
                                           data-menu-label="mon profil compte" style="min-height: 44px;">
                                            <div class="d-flex align-items-center gap-2-5 overflow-hidden">
                                                <div class="mobile-drawer-icon-box rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0"
                                                     style="width: 34px; height: 34px; font-size: 0.9rem;">
                                                    <i class="bi bi-person-gear"></i>
                                                </div>
                                                <span class="fw-medium text-truncate" style="font-size: 0.86rem;"><?= __('my_profile') ?></span>
                                            </div>
                                            <i class="bi bi-chevron-right extra-small text-muted flex-shrink-0 opacity-50"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Clean Canva Drawer -->
                <div class="offcanvas-footer border-top p-2-5 bg-card text-center">
                    <span class="extra-small text-muted">&copy; <?= date('Y') ?> <?= htmlspecialchars((string) $school_identity) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Main Content Area -->
        <main class="main-area" id="mainArea">
            <!-- Breadcrumb & Page Info Sub-Header -->
            <div class="px-3 px-md-4 py-2 border-bottom bg-card bg-opacity-50 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <h1 class="page-title fs-5 fw-bold text-main-theme mb-0 text-truncate lh-1">
                        <?= $title ?? __('dashboard') ?>
                    </h1>
                </div>
                <nav aria-label="breadcrumb" class="d-none d-md-block">
                    <ol class="breadcrumb mb-0" style="font-size: 0.75rem; font-weight: 500;">
                        <li class="breadcrumb-item">
                            <a href="/" class="text-decoration-none text-muted hover-primary transition-all"><?= __('home') ?></a>
                        </li>
                        <li class="breadcrumb-item active text-primary" aria-current="page">
                            <?= $title ?? __('dashboard') ?>
                        </li>
                    </ol>
                </nav>
            </div>

            <!-- Page Content Inner -->
            <div class="content-inner">
                <?= $content ?>
            </div>

            <!-- Footer -->
            <footer class="footer mt-auto py-3 border-top bg-card shadow-sm">
                <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <span class="text-muted-theme small">&copy; <?= date('Y') ?> <strong><?= __('app_name') ?></strong>. <?= __('footer_made_with') ?></span>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-muted-theme text-decoration-none small hover-primary"><?= __('technical_support') ?></a>
                        <a href="#" class="text-muted-theme text-decoration-none small hover-primary"><?= __('privacy_policy') ?></a>
                    </div>
                </div>
            </footer>
        </main>
    </div>

    <!-- ULTRA-PREMIUM COMMAND PALETTE (CTRL+K / CMD+K) -->
    <div class="command-palette-backdrop" id="commandPalette">
        <div class="command-palette-box">
            <div class="command-palette-header">
                <i class="bi bi-search fs-5 text-primary"></i>
                <input type="text" id="cmdPaletteInput" class="command-palette-input" placeholder="Rechercher une commande... (ex: Notes, Inscription, Tarifs)" autocomplete="off">
                <span class="badge bg-secondary bg-opacity-10 text-muted border rounded px-2 py-1 extra-small">ESC</span>
            </div>
            <div class="command-palette-results" id="cmdPaletteResults">
                <!-- Results dynamically populated in JS -->
            </div>
            <div class="command-palette-footer">
                <span><i class="bi bi-arrow-down-up me-1"></i> Navigation Flèches</span>
                <span><i class="bi bi-box-arrow-in-right me-1"></i> Entrée pour exécuter</span>
            </div>
        </div>
    </div>

    <!-- SPOTLIGHT GUIDED TOUR CARD (NO DARK BACKDROP OVERLAY) -->
    <div class="onboarding-card" id="onboardingCard" style="display: none;">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="onboarding-step-badge" id="onboardingStepBadge">Étape 1 / 5</span>
            <button type="button" class="btn-close" id="closeOnboardingBtn" aria-label="Close"></button>
        </div>
        <h5 class="fw-bold text-main-theme mb-1" id="onboardingTitle">Bienvenue sur NoteMaster !</h5>
        <p class="small text-muted mb-3" id="onboardingBody">Découvrez votre nouvelle navigation style Microsoft Word 365 conçue pour accélérer votre travail quotidien.</p>
        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
            <button type="button" class="btn btn-sm btn-light rounded-pill px-3" id="prevOnboardingBtn">Précédent</button>
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-sm" id="nextOnboardingBtn">Suivant</button>
        </div>
    </div>

    <!-- Scripts de base -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Services Applicatifs -->
    <script src="/public/js/AlertService.js?v=<?= $asset_version ?>"></script>
    <script src="/public/js/ux-improvements.js?v=<?= $asset_version ?>"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // --- INTERACTIVE SPOTLIGHT GUIDED TOUR CONTROLLER (NO DARK OVERLAY) ---
            const tourSteps = [
                {
                    target: '#tourBrandLogo',
                    title: "Identité de l'Établissement",
                    body: "Retrouvez ici le nom et le logo officiels de votre école. Un clic vous ramène instantanément au tableau de bord principal."
                },
                {
                    target: '#tourQAT',
                    title: "Barre d'Accès Rapide (QAT)",
                    body: "Accédez en 1 clic aux 5 fonctionnalités les plus vitales : Accueil, Saisie des notes, Caisse, Inscription élève et Bulletins."
                },
                {
                    target: '#openCmdPaletteTrigger',
                    title: "Recherche Globale & Command Palette (Ctrl+K)",
                    body: "Recherchez n'importe quel module ou commande au clavier grâce à la combinaison Ctrl+K ou ⌘K."
                },
                {
                    target: '#tourRibbonTabs',
                    title: "Onglets Métier du Ruban",
                    body: "Naviguez entre les grands pôles de gestion (Pilotage, RH, Finances, Notes, Impression). Utilisez les flèches du clavier ← → pour basculer rapidement."
                },
                {
                    target: '#tourUserAccount',
                    title: "Compte & Préférences",
                    body: "Gérez votre profil utilisateur, les paramètres système, le choix de la langue (Français/Anglais) et le mode Sombre."
                }
            ];

            let currentStepIdx = 0;
            const card = document.getElementById('onboardingCard');
            const stepBadge = document.getElementById('onboardingStepBadge');
            const titleEl = document.getElementById('onboardingTitle');
            const bodyEl = document.getElementById('onboardingBody');
            const prevBtn = document.getElementById('prevOnboardingBtn');
            const nextBtn = document.getElementById('nextOnboardingBtn');
            const closeBtn = document.getElementById('closeOnboardingBtn');
            const startBtn = document.getElementById('startGuidedTour');

            const showTourStep = (idx) => {
                // Clear previous highlights
                document.querySelectorAll('.onboarding-highlight').forEach(el => el.classList.remove('onboarding-highlight'));

                if (idx < 0 || idx >= tourSteps.length) {
                    endTour();
                    return;
                }

                currentStepIdx = idx;
                const step = tourSteps[idx];
                const targetEl = document.querySelector(step.target);

                stepBadge.textContent = `Étape ${idx + 1} / ${tourSteps.length}`;
                titleEl.textContent = step.title;
                bodyEl.textContent = step.body;

                prevBtn.style.display = idx === 0 ? 'none' : 'inline-block';
                nextBtn.textContent = idx === tourSteps.length - 1 ? 'Terminer' : 'Suivant';

                card.style.display = 'block';

                if (targetEl) {
                    targetEl.classList.add('onboarding-highlight');
                    const rect = targetEl.getBoundingClientRect();
                    const cardHeight = 200;
                    
                    let topPos = rect.bottom + 12;
                    if (topPos + cardHeight > window.innerHeight) {
                        topPos = Math.max(20, rect.top - cardHeight - 12);
                    }

                    let leftPos = Math.max(15, Math.min(rect.left, window.innerWidth - 400));
                    
                    card.style.top = topPos + 'px';
                    card.style.left = leftPos + 'px';
                } else {
                    card.style.top = '20%';
                    card.style.left = '50%';
                    card.style.transform = 'translateX(-50%)';
                }
            };

            const endTour = () => {
                card.style.display = 'none';
                document.querySelectorAll('.onboarding-highlight').forEach(el => el.classList.remove('onboarding-highlight'));
                localStorage.setItem('nm_tour_completed', 'true');
            };

            if (startBtn) {
                startBtn.addEventListener('click', () => showTourStep(0));
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', () => showTourStep(currentStepIdx + 1));
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', () => showTourStep(currentStepIdx - 1));
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', endTour);
            }

            // Auto-trigger tour for first-time visitors
            if (!localStorage.getItem('nm_tour_completed')) {
                setTimeout(() => showTourStep(0), 1200);
            }

            // --- DYNAMIC COMMAND PALETTE CONTROLLER (LINEAR / VS CODE STYLE) ---
            const cmdPalette = document.getElementById('commandPalette');
            const cmdPaletteInput = document.getElementById('cmdPaletteInput');
            const cmdPaletteResults = document.getElementById('cmdPaletteResults');
            const openTrigger = document.getElementById('openCmdPaletteTrigger');
            let selectedIndex = 0;
            let currentResults = [];

            const renderResults = (items) => {
                currentResults = items;
                if (items.length === 0) {
                    cmdPaletteResults.innerHTML = `
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-search fs-3 d-block mb-2 text-opacity-50"></i>
                            <span class="small">Aucune commande trouvée</span>
                        </div>`;
                    return;
                }

                cmdPaletteResults.innerHTML = items.map((item, idx) => `
                    <a href="${item.url}" class="command-palette-item ${idx === selectedIndex ? 'selected' : ''}" data-index="${idx}">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi ${item.icon} fs-5 text-primary"></i>
                            <div>
                                <div class="fw-bold">${item.label}</div>
                                <div class="extra-small text-muted">${item.tab} &bull; ${item.group} - ${item.desc || ''}</div>
                            </div>
                        </div>
                        <span class="command-palette-badge">${item.tab}</span>
                    </a>
                `).join('');
            };

            const openCmdPalette = () => {
                if (!cmdPalette) return;
                cmdPalette.classList.add('active');
                cmdPaletteInput.value = '';
                selectedIndex = 0;
                renderResults(window.NM_COMMANDS || []);
                setTimeout(() => cmdPaletteInput.focus(), 50);
            };

            const closeCmdPalette = () => {
                if (!cmdPalette) return;
                cmdPalette.classList.remove('active');
            };

            if (openTrigger) {
                openTrigger.addEventListener('click', openCmdPalette);
            }

            if (cmdPaletteInput) {
                cmdPaletteInput.addEventListener('input', function () {
                    const q = this.value.trim().toLowerCase();
                    selectedIndex = 0;
                    if (q === '') {
                        renderResults(window.NM_COMMANDS || []);
                    } else {
                        const filtered = (window.NM_COMMANDS || []).filter(item => 
                            item.label.toLowerCase().includes(q) ||
                            item.tab.toLowerCase().includes(q) ||
                            item.group.toLowerCase().includes(q) ||
                            (item.desc && item.desc.toLowerCase().includes(q))
                        );
                        renderResults(filtered);
                    }
                });

                cmdPaletteInput.addEventListener('keydown', function (e) {
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (currentResults.length > 0) {
                            selectedIndex = (selectedIndex + 1) % currentResults.length;
                            renderResults(currentResults);
                        }
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (currentResults.length > 0) {
                            selectedIndex = (selectedIndex - 1 + currentResults.length) % currentResults.length;
                            renderResults(currentResults);
                        }
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (currentResults[selectedIndex]) {
                            window.location.href = currentResults[selectedIndex].url;
                        }
                    } else if (e.key === 'Escape') {
                        closeCmdPalette();
                    }
                });
            }

            document.addEventListener('keydown', function (e) {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                    e.preventDefault();
                    if (cmdPalette && cmdPalette.classList.contains('active')) {
                        closeCmdPalette();
                    } else {
                        openCmdPalette();
                    }
                } else if (e.key === 'Escape') {
                    if (cmdPalette && cmdPalette.classList.contains('active')) {
                        closeCmdPalette();
                    } else if (card && card.style.display === 'block') {
                        endTour();
                    }
                }
            });

            if (cmdPalette) {
                cmdPalette.addEventListener('click', function (e) {
                    if (e.target === this) closeCmdPalette();
                });
            }

            // --- RIBBON UI CONTROLLER (Microsoft Word Style Expand/Collapse Toggle Rules) ---
            const tabButtons = document.querySelectorAll('.ribbon-tab-btn');
            const tabPanes = document.querySelectorAll('.ribbon-tab-pane');
            const toolbarContainer = document.getElementById('ribbonToolbarContainer');
            const collapseToggle = document.getElementById('ribbonCollapseToggle');

            // Apply saved ribbon collapsed state (only if explicitly collapsed by user)
            if (toolbarContainer && localStorage.getItem('ribbon-collapsed') === 'true') {
                toolbarContainer.classList.add('collapsed');
                if (collapseToggle) {
                    const icon = collapseToggle.querySelector('i');
                    if (icon) icon.className = 'bi bi-chevron-down';
                }
            }

            // Tab Toggle & Switching Handler (MS Word Style Expand/Collapse Rules)
            tabButtons.forEach((btn, index) => {
                btn.addEventListener('click', function (e) {
                    const targetSelector = this.getAttribute('data-tab-target');
                    const targetPane = document.querySelector(targetSelector);

                    if (!targetPane) return;

                    const isCurrentlyActive = this.classList.contains('active');
                    const isToolbarCollapsed = toolbarContainer ? toolbarContainer.classList.contains('collapsed') : false;

                    // RULE 2: If clicking the ALREADY ACTIVE tab and toolbar is OPEN -> Collapse it!
                    if (isCurrentlyActive && !isToolbarCollapsed) {
                        if (toolbarContainer) {
                            toolbarContainer.classList.add('collapsed');
                            localStorage.setItem('ribbon-collapsed', 'true');
                        }
                        this.classList.remove('active');
                        if (collapseToggle) {
                            const icon = collapseToggle.querySelector('i');
                            if (icon) icon.className = 'bi bi-chevron-down';
                        }
                        return;
                    }

                    // RULE 1 & 3: Click on a closed tab OR a different tab -> Deactivate others and open/expand!
                    tabButtons.forEach(b => b.classList.remove('active'));
                    tabPanes.forEach(p => p.classList.remove('active'));

                    this.classList.add('active');
                    targetPane.classList.add('active');

                    if (toolbarContainer) {
                        toolbarContainer.classList.remove('collapsed');
                        localStorage.setItem('ribbon-collapsed', 'false');
                        if (collapseToggle) {
                            const icon = collapseToggle.querySelector('i');
                            if (icon) icon.className = 'bi bi-chevron-up';
                        }
                    }
                });

                // Keyboard Arrow Key Navigation for Ribbon Tabs (MS Word Style)
                btn.addEventListener('keydown', function (e) {
                    if (e.key === 'ArrowRight') {
                        e.preventDefault();
                        const nextBtn = tabButtons[(index + 1) % tabButtons.length];
                        if (nextBtn) { nextBtn.focus(); nextBtn.click(); }
                    } else if (e.key === 'ArrowLeft') {
                        e.preventDefault();
                        const prevBtn = tabButtons[(index - 1 + tabButtons.length) % tabButtons.length];
                        if (prevBtn) { prevBtn.focus(); prevBtn.click(); }
                    }
                });
            });

            // Pin / Collapse Toolbar Handler
            if (collapseToggle && toolbarContainer) {
                collapseToggle.addEventListener('click', function () {
                    const isCollapsed = toolbarContainer.classList.toggle('collapsed');
                    localStorage.setItem('ribbon-collapsed', isCollapsed ? 'true' : 'false');
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.className = isCollapsed ? 'bi bi-chevron-down' : 'bi bi-chevron-up';
                    }
                });
            }

            // Outside Click Handler to close floating elements / tour if clicked outside ribbon
            document.addEventListener('click', function (e) {
                const ribbonNav = document.getElementById('ribbonNavContainer');
                if (ribbonNav && !ribbonNav.contains(e.target)) {
                    // Close tour if active and user clicks outside
                    if (card && card.style.display === 'block' && !card.contains(e.target) && e.target.id !== 'startGuidedTour') {
                        endTour();
                    }
                }
            });

            // Theme Toggle Handler
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = themeToggle ? themeToggle.querySelector('i') : null;

            const updateThemeIcon = (theme) => {
                if (!themeIcon) return;
                themeIcon.className = theme === 'dark' ? 'bi bi-sun fs-6 text-main-theme' : 'bi bi-moon-stars fs-6 text-main-theme';
            };

            updateThemeIcon(document.documentElement.getAttribute('data-theme'));

            if (themeToggle) {
                themeToggle.addEventListener('click', () => {
                    const currentTheme = document.documentElement.getAttribute('data-theme');
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

                    document.documentElement.setAttribute('data-theme', newTheme);
                    localStorage.setItem('theme', newTheme);
                    updateThemeIcon(newTheme);
                });
            }

            // --- AUTO-DETECTION DES MESSAGES FLASH (Toasts discrets) ---
            <?php if ($flash_success = \App\Core\Session::getFlash('success')): ?>
                AlertService.toast('success', "<?= addslashes((string) $flash_success) ?>");
            <?php endif; ?>

            <?php if ($flash_error = \App\Core\Session::getFlash('error')): ?>
                AlertService.toast('error', "<?= addslashes((string) $flash_error) ?>");
            <?php endif; ?>

            <?php if ($popup_error = \App\Core\Session::getFlash('popup_error')): ?>
                AlertService.error("<?= addslashes((string) __('error_title')) ?>", "<?= addslashes((string) $popup_error) ?>");
            <?php endif; ?>

            // --- MESSAGE DE BIENVENUE FLASH (Connexion uniquement) ---
            <?php if ($welcome_msg = \App\Core\Session::getFlash('welcome_user')): ?>
                Swal.fire({
                    title: "<?= addslashes((string) $welcome_msg) ?>",
                    icon: 'success',
                    timer: 5000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    width: '320px',
                    background: '#ffffff',
                    customClass: {
                        popup: 'rounded-4 shadow-sm p-4 border border-light',
                        title: 'text-black fw-bolder fs-5'
                    }
                });
            <?php endif; ?>

            // Initialisation des tooltips Bootstrap
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(t => new bootstrap.Tooltip(t));
        });
    </script>
    <script src="/public/js/topbar-onboarding.js?v=<?= $asset_version ?>"></script>
</body>

</html>