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

// Ribbon UI Definition (Inspired by Microsoft Word 365)
$ribbon_structure = [
    [
        'id' => 'tab-home',
        'title' => __('home'),
        'icon' => 'bi-house-door',
        'groups' => [
            [
                'title' => __('pilotage') ?? 'Pilotage',
                'items' => [
                    ['icon' => 'bi-speedometer2', 'label' => __('dashboard'), 'url' => '/', 'roles' => ['superadmin', 'admin', 'enseignant', 'caissier', 'comptable', 'it_manager']]
                ]
            ],
            [
                'title' => __('enseignant') ?? 'Enseignant',
                'items' => [
                    ['icon' => 'bi-pencil-square', 'label' => __('enter_marks'), 'url' => '/notes', 'roles' => ['enseignant']],
                    ['icon' => 'bi-people', 'label' => __('my_students'), 'url' => '/students', 'roles' => ['enseignant']],
                    ['icon' => 'bi-question-circle', 'label' => __('help'), 'url' => '/documentation', 'roles' => ['enseignant']],
                ]
            ],
            [
                'title' => __('caisse') ?? 'Caisse',
                'items' => [
                    ['icon' => 'bi-door-open', 'label' => __('classes'), 'url' => '/classes', 'roles' => ['caissier', 'comptable']]
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
                    ['icon' => 'bi-calendar-event', 'label' => __('academic_years'), 'url' => '/academic_years', 'roles' => ['superadmin', 'it_manager']],
                    ['icon' => 'bi-diagram-3', 'label' => __('teaching_types'), 'url' => '/teaching_types', 'roles' => ['superadmin', 'admin']],
                    ['icon' => 'bi-bar-chart-steps', 'label' => __('levels') ?? 'Niveaux', 'url' => '/levels', 'roles' => ['superadmin', 'admin']],
                    ['icon' => 'bi-layers', 'label' => __('academic_cycles'), 'url' => '/cycles', 'roles' => ['superadmin', 'admin']],
                    ['icon' => 'bi-grid-3x3-gap', 'label' => __('academic_sections'), 'url' => '/sections', 'roles' => ['superadmin', 'admin']],
                    ['icon' => 'bi-building', 'label' => __('departments'), 'url' => '/departments', 'roles' => ['superadmin', 'admin', 'it_manager']],
                ]
            ],
            [
                'title' => 'Système & Config',
                'items' => [
                    ['icon' => 'bi-gear', 'label' => __('settings'), 'url' => '/settings', 'roles' => ['superadmin', 'admin']],
                    ['icon' => 'bi-question-circle', 'label' => __('help'), 'url' => '/documentation', 'roles' => ['superadmin', 'admin', 'it_manager']],
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
                    ['icon' => 'bi-person-plus', 'label' => __('register_student_menu'), 'url' => '/students/create', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable']],
                    ['icon' => 'bi-people', 'label' => __('registered_students_menu'), 'url' => '/students', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable']],
                    ['icon' => 'bi-person-dash', 'label' => __('unregistered_students_menu'), 'url' => '/students/non-inscrits', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable']],
                    ['icon' => 'bi-person-check', 'label' => __('my_registrations_menu'), 'url' => '/students?only_mine=1', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable']],
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
                    ['icon' => 'bi-people-fill', 'label' => __('users'), 'url' => '/users', 'roles' => ['superadmin', 'it_manager']],
                    ['icon' => 'bi-person-plus-fill', 'label' => __('manage_cashiers_menu'), 'url' => '/users/caissiers', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable']],
                    ['icon' => 'bi-person-badge', 'label' => __('teachers'), 'url' => '/teachers', 'roles' => ['superadmin', 'admin', 'it_manager']],
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
                    ['icon' => 'bi-credit-card', 'label' => __('payments_menu'), 'url' => '/payments', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable']],
                    ['icon' => 'bi-table', 'label' => __('grille_title'), 'url' => '/school_fees/grille', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable']],
                    ['icon' => 'bi-diagram-2', 'label' => __('tranches_menu'), 'url' => '/school_fees/tranches', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable']],
                    ['icon' => 'bi-receipt-cutoff', 'label' => __('versements_menu'), 'url' => '/school_fees/versements', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable']],
                    ['icon' => 'bi-exclamation-triangle', 'label' => __('insolvent_title'), 'url' => '/school_fees/insolvables', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable']],
                ]
            ],
            [
                'title' => __('discounts'),
                'items' => [
                    ['icon' => 'bi-percent', 'label' => __('discounts_granted'), 'url' => '/discounts', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable']],
                    ['icon' => 'bi-mortarboard', 'label' => __('scholarships'), 'url' => '/scholarships', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable']],
                    ['icon' => 'bi-tags', 'label' => __('discount_types_title'), 'url' => '/discount_types', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable']],
                ]
            ],
            [
                'title' => __('expenses_menu'),
                'items' => [
                    ['icon' => 'bi-journal-text', 'label' => __('financial_history'), 'url' => '/financial-history', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable']],
                    ['icon' => 'bi-list-ul', 'label' => __('expenses_list'), 'url' => '/expenses', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable']],
                    ['icon' => 'bi-tags-fill', 'label' => __('expense_categories'), 'url' => '/expenses/categories', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable']],
                    ['icon' => 'bi-shield-check', 'label' => __('expense_audit'), 'url' => '/expenses/audit', 'roles' => ['superadmin', 'admin', 'caissier', 'comptable']],
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
                    ['icon' => 'bi-check2-square', 'label' => __('evaluations'), 'url' => '/sequences', 'roles' => ['superadmin', 'admin']],
                    ['icon' => 'bi-pencil-square', 'label' => __('enter_marks'), 'url' => '/notes', 'roles' => ['superadmin', 'admin']],
                    ['icon' => 'bi-door-open', 'label' => __('classes'), 'url' => '/classes', 'roles' => ['superadmin', 'admin']],
                    ['icon' => 'bi-book', 'label' => __('subjects'), 'url' => '/subjects', 'roles' => ['superadmin', 'admin']],
                    ['icon' => 'bi-collection', 'label' => __('subject_groups') ?? 'Groupe de Modules', 'url' => '/subject-groups', 'roles' => ['superadmin', 'admin']],
                    ['icon' => 'bi-shield-check', 'label' => __('discipline_management'), 'url' => '/bulletins/discipline', 'roles' => ['superadmin', 'admin']],
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
                    ['icon' => 'bi-file-earmark-pdf', 'label' => __('bulletins'), 'url' => '/bulletins', 'roles' => ['superadmin', 'admin']],
                    ['icon' => 'bi-award', 'label' => __('honor_roll_title'), 'url' => '/honors', 'roles' => ['superadmin', 'admin']],
                    ['icon' => 'bi-file-earmark-text', 'label' => __('proces_verbaux'), 'url' => '/proces-verbal', 'roles' => ['superadmin', 'admin']],
                    ['icon' => 'bi-file-earmark-spreadsheet', 'label' => __('transcripts') ?? 'Relevé de Notes', 'url' => '/transcripts', 'roles' => ['superadmin', 'admin']],
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
            if (in_array($user_role, $item['roles']) && $isUrlActive($item['url'])) {
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
            if (in_array($user_role, $item['roles'])) {
                $authorized_commands[] = [
                    'tab' => $tab['title'],
                    'group' => $group['title'],
                    'label' => $item['label'],
                    'icon' => $item['icon'],
                    'url' => $item['url']
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
        : '2.1.0';
    ?>
    <link rel="stylesheet" href="/public/css/modern-dashboard.css?v=<?= $asset_version ?>">
    <link rel="stylesheet" href="/public/css/alerts-premium.css?v=<?= $asset_version ?>">
    <link rel="stylesheet" href="/public/css/ux-improvements.css?v=<?= $asset_version ?>">

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
        <nav class="word-ribbon-container">
            
            <!-- 1. BARRE SUPÉRIEURE INDÉPENDANTE (Brand + QAT + Global Search + Utility Actions) -->
            <div class="ribbon-top-bar">
                <!-- Gauche : Mobile Button + Logo + Quick Access Toolbar (QAT) + Global Search Input -->
                <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 750px;">
                    <!-- Mobile Menu Button -->
                    <?php if (\App\Core\Session::isLogged()): ?>
                        <button class="btn btn-theme-soft p-1 me-1 d-lg-none border-0 rounded-circle flex-shrink-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileRibbonDrawer" title="Menu Mobile">
                            <i class="bi bi-list fs-4 text-main-theme"></i>
                        </button>
                    <?php endif; ?>

                    <!-- Brand / Logo -->
                    <a href="/" class="ribbon-brand flex-shrink-0 me-2">
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
                        <span class="ribbon-brand-text d-none d-sm-inline"><?= htmlspecialchars((string) $school_identity) ?></span>
                    </a>

                    <!-- MS Word Quick Access Toolbar (QAT - Barre d'Accès Rapide) -->
                    <?php if (\App\Core\Session::isLogged()): ?>
                        <div class="ribbon-qat-container d-none d-md-flex me-2 flex-shrink-0">
                            <a href="/" class="ribbon-qat-btn <?= $current_path === '/' ? 'active' : '' ?>" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= __('dashboard') ?>">
                                <i class="bi bi-house-door"></i>
                            </a>
                            <?php if (in_array($user_role, ['superadmin', 'admin', 'enseignant'])): ?>
                                <a href="/notes" class="ribbon-qat-btn <?= strpos($current_path, '/notes') === 0 ? 'active' : '' ?>" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= __('enter_marks') ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (in_array($user_role, ['superadmin', 'admin', 'caissier', 'comptable'])): ?>
                                <a href="/payments" class="ribbon-qat-btn <?= strpos($current_path, '/payments') === 0 ? 'active' : '' ?>" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= __('payments_menu') ?>">
                                    <i class="bi bi-credit-card"></i>
                                </a>
                                <a href="/students/create" class="ribbon-qat-btn <?= strpos($current_path, '/students/create') === 0 ? 'active' : '' ?>" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= __('register_student_menu') ?>">
                                    <i class="bi bi-person-plus"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (in_array($user_role, ['superadmin', 'admin'])): ?>
                                <a href="/bulletins" class="ribbon-qat-btn <?= strpos($current_path, '/bulletins') === 0 ? 'active' : '' ?>" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= __('bulletins') ?>">
                                    <i class="bi bi-printer"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Global Search Trigger (Linear / VSCode Command Palette Trigger) -->
                    <?php if (\App\Core\Session::isLogged()): ?>
                        <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2 align-items-center flex-grow-1 cursor-pointer" id="openCmdPaletteTrigger" style="border: 1px solid var(--border-color) !important; max-width: 340px; cursor: pointer;">
                            <span class="input-group-text border-0 bg-transparent text-primary p-1 ps-1">
                                <i class="bi bi-search" style="font-size: 0.82rem;"></i>
                            </span>
                            <input type="text" class="form-control border-0 bg-transparent shadow-none py-1 text-main cursor-pointer"
                                placeholder="Rechercher une commande... (Ctrl+K)" readonly style="font-size: 0.78rem; height: 30px; cursor: pointer;">
                            <span class="badge bg-secondary bg-opacity-10 text-muted border rounded-pill extra-small px-1 py-0 me-1 d-none d-md-inline" style="font-size: 0.62rem;">⌘K</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Droite : Theme Switcher + Compact User Account Avatar -->
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <!-- Theme Toggle -->
                    <button class="theme-toggle-btn btn btn-theme-soft rounded-circle d-flex align-items-center justify-content-center p-0 border-0 shadow-sm transition-all"
                            id="themeToggle" title="<?= __('change_theme') ?>" style="width: 36px; height: 36px;">
                        <i class="bi bi-moon-stars fs-6 text-main-theme"></i>
                    </button>

                    <!-- Compact User Account Avatar Dropdown (Space-Saving) -->
                    <?php if (\App\Core\Session::isLogged()): ?>
                        <div class="dropdown">
                            <a href="#"
                                class="user-avatar-btn"
                                data-bs-toggle="dropdown" aria-expanded="false"
                                data-bs-placement="bottom" title="<?= h($user_name) ?> (<?= h(__($user_role)) ?>)">
                                <?= $user_initials ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2" style="min-width: 250px;">
                                <li>
                                    <div class="user-dropdown-header-card d-flex align-items-center gap-2">
                                        <div class="user-avatar bg-primary text-white d-flex align-items-center justify-content-center rounded-circle fw-bold shadow-sm"
                                            style="width: 38px; height: 38px; font-size: 1rem;">
                                            <?= $user_initials ?>
                                        </div>
                                        <div class="d-flex flex-column text-start overflow-hidden">
                                            <span class="text-main-theme fw-bold text-truncate lh-1" style="font-size: 0.88rem;"><?= h($user_name) ?></span>
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill mt-1 w-auto align-self-start extra-small"><?= h(__($user_role)) ?></span>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-modern" href="/profile">
                                        <i class="bi bi-person text-primary"></i> <?= __('my_profile') ?>
                                    </a>
                                </li>
                                <?php if (in_array($user_role, ['superadmin'])): ?>
                                    <li>
                                        <a class="dropdown-item dropdown-item-modern" href="/settings">
                                            <i class="bi bi-gear text-secondary"></i> <?= __('settings') ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><h6 class="dropdown-header small text-uppercase fw-bold"><?= __('language') ?></h6></li>
                                <li>
                                    <a class="dropdown-item dropdown-item-modern <?= $app_lang === 'fr' ? 'active' : '' ?>"
                                        href="javascript:void(0)" onclick="UX.switchLanguage('fr')">
                                        <span class="fs-6">🇫🇷</span> Français
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-modern <?= $app_lang === 'en' ? 'active' : '' ?>"
                                        href="javascript:void(0)" onclick="UX.switchLanguage('en')">
                                        <span class="fs-6">🇺🇸</span> English
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item dropdown-item-modern text-danger" href="/logout">
                                        <i class="bi bi-box-arrow-right"></i> <?= __('logout') ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="d-flex align-items-center gap-2">
                            <a href="/login" class="btn btn-primary rounded-pill px-3 py-1-5 btn-sm fw-bold shadow-sm">
                                <?= __('login') ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 2. BARRE D'ONGLETS RIBBON DÉDIÉE (Pleine Largeur - Exclusivité Menus Métier) -->
            <?php if (\App\Core\Session::isLogged()): ?>
                <div class="ribbon-tabs-bar border-bottom d-none d-lg-flex px-3">
                    <div class="ribbon-tabs-wrapper flex-grow-1" id="ribbonTabsWrapper">
                        <?php foreach ($ribbon_structure as $tab): ?>
                            <?php
                            $tab_accessible_count = 0;
                            foreach ($tab['groups'] as $group) {
                                foreach ($group['items'] as $item) {
                                    if (in_array($user_role, $item['roles'])) {
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
                                    if (in_array($user_role, $item['roles'])) {
                                        $tab_accessible_items[] = $item;
                                    }
                                }
                            }
                            $total_tab_items = count($tab_accessible_items);
                            ?>
                            <?php if ($total_tab_items > 0): ?>
                                <div class="ribbon-tab-pane <?= $tab['id'] === $active_tab_id ? 'active' : '' ?>" id="<?= $tab['id'] ?>">
                                    <?php if ($total_tab_items <= 10): ?>
                                        <!-- CAS 1 : <= 10 SOUS-MENUS -> AFFICHAGE DIRECT EN GROUPES RIBBON -->
                                        <?php foreach ($tab['groups'] as $group): ?>
                                            <?php
                                            $group_visible_items = array_filter($group['items'], fn($i) => in_array($user_role, $i['roles']));
                                            ?>
                                            <?php if (count($group_visible_items) > 0): ?>
                                                <div class="ribbon-group">
                                                    <div class="ribbon-group-items">
                                                        <?php foreach ($group_visible_items as $item): ?>
                                                            <?php $isActive = $isUrlActive($item['url']); ?>
                                                            <a href="<?= $item['url'] ?>" 
                                                               class="ribbon-btn-large <?= $isActive ? 'active' : '' ?>" 
                                                               title="<?= htmlspecialchars($item['label']) ?>">
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
                                        <!-- CAS 2 : > 10 SOUS-MENUS -> DISSOLUTION DE LA DROPDOWN CLASSIQUE & ÉCLATEMENT VISUEL AU MÊME NIVEAU (SUB-RIBBON SECTIONS) -->
                                        <div class="d-flex align-items-stretch gap-2 flex-grow-1 overflow-x-auto" style="scrollbar-width: thin;">
                                            <?php foreach ($tab['groups'] as $group): ?>
                                                <?php
                                                $group_visible_items = array_filter($group['items'], fn($i) => in_array($user_role, $i['roles']));
                                                ?>
                                                <?php if (count($group_visible_items) > 0): ?>
                                                    <div class="ribbon-group-exploded">
                                                        <div class="ribbon-group-items">
                                                            <?php foreach ($group_visible_items as $item): ?>
                                                                <?php $isActive = $isUrlActive($item['url']); ?>
                                                                <a href="<?= $item['url'] ?>" 
                                                                   class="ribbon-btn-large <?= $isActive ? 'active' : '' ?>" 
                                                                   title="<?= htmlspecialchars($item['label']) ?>">
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

        <!-- Mobile Navigation Offcanvas Drawer -->
        <?php if (\App\Core\Session::isLogged()): ?>
            <div class="offcanvas offcanvas-start ribbon-mobile-drawer" tabindex="-1" id="mobileRibbonDrawer" aria-labelledby="mobileRibbonDrawerLabel">
                <div class="offcanvas-header border-bottom">
                    <h5 class="offcanvas-title fw-bold" id="mobileRibbonDrawerLabel"><?= htmlspecialchars((string) $school_identity) ?></h5>
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <?php foreach ($ribbon_structure as $tab): ?>
                        <?php
                        $tab_items = [];
                        foreach ($tab['groups'] as $group) {
                            foreach ($group['items'] as $item) {
                                if (in_array($user_role, $item['roles'])) {
                                    $tab_items[] = $item;
                                }
                            }
                        }
                        ?>
                        <?php if (count($tab_items) > 0): ?>
                            <div class="ribbon-mobile-section">
                                <div class="ribbon-mobile-section-title">
                                    <i class="bi <?= $tab['icon'] ?> me-1"></i> <?= htmlspecialchars($tab['title']) ?>
                                </div>
                                <?php foreach ($tab_items as $item): ?>
                                    <?php $isActive = $isUrlActive($item['url']); ?>
                                    <a href="<?= $item['url'] ?>" class="ribbon-mobile-link <?= $isActive ? 'active' : '' ?>">
                                        <i class="bi <?= $item['icon'] ?>"></i>
                                        <span><?= htmlspecialchars($item['label']) ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
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

    <!-- Scripts de base -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Services Applicatifs -->
    <script src="/public/js/AlertService.js?v=<?= $asset_version ?>"></script>
    <script src="/public/js/ux-improvements.js?v=<?= $asset_version ?>"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
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
                                <div class="extra-small text-muted">${item.tab} &bull; ${item.group}</div>
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
                            item.group.toLowerCase().includes(q)
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
                } else if (e.key === 'Escape' && cmdPalette && cmdPalette.classList.contains('active')) {
                    closeCmdPalette();
                }
            });

            if (cmdPalette) {
                cmdPalette.addEventListener('click', function (e) {
                    if (e.target === this) closeCmdPalette();
                });
            }

            // --- RIBBON UI CONTROLLER (Microsoft Word Style) ---
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

            // Tab Switching Handler
            tabButtons.forEach((btn, index) => {
                btn.addEventListener('click', function () {
                    const targetSelector = this.getAttribute('data-tab-target');
                    const targetPane = document.querySelector(targetSelector);

                    if (!targetPane) return;

                    // Deactivate all tabs and panes
                    tabButtons.forEach(b => b.classList.remove('active'));
                    tabPanes.forEach(p => p.classList.remove('active'));

                    // Activate clicked tab and target pane
                    this.classList.add('active');
                    targetPane.classList.add('active');

                    // If toolbar was collapsed, expand it when user explicitly clicks a tab
                    if (toolbarContainer && toolbarContainer.classList.contains('collapsed')) {
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
</body>

</html>