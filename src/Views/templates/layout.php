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

// Navigation Items based on role
$nav_items = [
    ['icon' => 'bi-speedometer2', 'label' => __('dashboard'), 'url' => '/', 'roles' => ['superadmin', 'admin', 'enseignant']],

    // SECTION: PILOTAGE
    ['section' => __('pilotage'), 'roles' => ['superadmin', 'admin']],
    ['icon' => 'bi-calendar-event', 'label' => __('academic_years'), 'url' => '/academic_years', 'roles' => ['superadmin', 'admin']],
    ['icon' => 'bi-check2-square', 'label' => __('evaluations'), 'url' => '/sequences', 'roles' => ['superadmin', 'admin']],

    // SECTION: STRUCTURE
    ['section' => __('structure_et_classes'), 'roles' => ['superadmin']],
    ['icon' => 'bi-layers', 'label' => __('academic_cycles'), 'url' => '/cycles', 'roles' => ['superadmin']],
    ['icon' => 'bi-grid-3x3-gap', 'label' => __('academic_sections'), 'url' => '/sections', 'roles' => ['superadmin']],
    ['icon' => 'bi-building', 'label' => __('departments'), 'url' => '/departments', 'roles' => ['superadmin']],
    ['icon' => 'bi-door-open', 'label' => __('classes'), 'url' => '/classes', 'roles' => ['superadmin', 'admin']],

    // SECTION: RESSOURCES HUMAINES
    ['section' => __('ressources_humaines'), 'roles' => ['superadmin', 'admin', 'enseignant']],
    ['icon' => 'bi-people', 'label' => __('students'), 'url' => '/students', 'roles' => ['superadmin', 'admin']],
    ['icon' => 'bi-people', 'label' => __('my_students'), 'url' => '/students', 'roles' => ['enseignant']],
    ['icon' => 'bi-person-badge', 'label' => __('teachers'), 'url' => '/teachers', 'roles' => ['superadmin', 'admin']],

    // SECTION: PÉDAGOGIE
    ['section' => __('pedagogie_et_resultats'), 'roles' => ['superadmin', 'admin', 'enseignant']],
    ['icon' => 'bi-book', 'label' => __('subjects'), 'url' => '/subjects', 'roles' => ['superadmin', 'admin']],
    ['icon' => 'bi-pencil-square', 'label' => __('enter_marks'), 'url' => '/notes', 'roles' => ['superadmin', 'admin', 'enseignant']],
    ['icon' => 'bi-file-earmark-pdf', 'label' => __('bulletins'), 'url' => '/bulletins', 'roles' => ['superadmin', 'admin']],
    ['icon' => 'bi-award', 'label' => __('honor_roll_title'), 'url' => '/honors', 'roles' => ['superadmin', 'admin']],
    ['icon' => 'bi-shield-check', 'label' => __('discipline_management'), 'url' => '/bulletins/discipline', 'roles' => ['superadmin', 'admin']],
    ['icon' => 'bi-file-earmark-text', 'label' => __('proces_verbaux'), 'url' => '/proces-verbal', 'roles' => ['superadmin', 'admin']],

    // SECTION: ADMINISTRATION
    ['section' => __('administration'), 'roles' => ['superadmin']],
    ['icon' => 'bi-people-fill', 'label' => __('users'), 'url' => '/users', 'roles' => ['superadmin']],
    ['icon' => 'bi-gear', 'label' => __('settings'), 'url' => '/settings', 'roles' => ['superadmin']],
    ['icon' => 'bi-question-circle', 'label' => __('help'), 'url' => '/documentation', 'roles' => ['superadmin', 'admin', 'enseignant']],
];

$current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
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
        
        // --- TRANSLATIONS FOR JAVASCRIPT ---
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
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="/public/css/modern-dashboard.css?v=1.1">
    <link rel="stylesheet" href="/public/css/alerts-premium.css?v=1.1">
    <link rel="stylesheet" href="/public/css/ux-improvements.css?v=1.1">


    <style>
        :root {
            --primary-color: #3b82f6;
            --primary-rgb: 59, 130, 246;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .page-title,
        .sidebar-brand {
            font-family: 'Outfit', sans-serif;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
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

        .btn-logout {
            color: #ef4444;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            background: #fef2f2;
            color: #b91c1c;
        }
            background: #fef2f2;
            color: #b91c1c;
        }

        .nav-link-custom.active {
            background: linear-gradient(135deg, var(--primary-color), #4f46e5);
            box-shadow: 0 8px 20px -6px rgba(59, 130, 246, 0.5);
            color: white !important;
            transform: translateX(4px);
        }

        .nav-section {
            padding: 1.5rem 1.5rem 0.5rem;
            opacity: 0.8;
        }

        .nav-section-title {
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--primary-color);
            border-bottom: 1px solid rgba(var(--primary-rgb), 0.1);
            padding-bottom: 4px;
        }

        .nav-link-custom {
            margin: 0.2rem 0.75rem;
            padding: 0.7rem 1rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #64748b;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
        }

        .nav-link-custom:hover:not(.active) {
            background: rgba(var(--primary-rgb), 0.05);
            color: var(--primary-color);
            transform: translateX(4px);
        }

        .nav-link-custom i {
            font-size: 1.1rem;
            transition: transform 0.2s;
        }

        .nav-link-custom:hover i {
            transform: scale(1.1);
        }

        [data-theme="dark"] .nav-link-custom {
            color: #94a3b8;
        }

        [data-theme="dark"] .nav-link-custom:hover:not(.active) {
            background: rgba(255, 255, 255, 0.05);
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

        /* Dark Mode Support for Native Select Options */
        [data-theme="dark"] select option {
            background-color: #000000 !important;
            color: #ffffff !important;
        }

        [data-theme="dark"] .form-select {
            background-color: rgba(255, 255, 255, 0.05);
            color: #ffffff;
        }

        /* Dark Mode Support for Topbar Elements */
        .topbar-glass {
            background: rgba(255, 255, 255, 0.95);
        }
        [data-theme="dark"] .topbar-glass {
            background: rgba(15, 23, 42, 0.9) !important;
            border-bottom-color: rgba(255, 255, 255, 0.08) !important;
        }

        .btn-theme-soft {
            background: #f8fafc;
            color: #475569;
        }
        [data-theme="dark"] .btn-theme-soft {
            background: rgba(255, 255, 255, 0.1) !important;
            color: #f8fafc !important;
        }
        [data-theme="dark"] .btn-theme-soft:hover {
            background: rgba(255, 255, 255, 0.15) !important;
        }

        .user-profile-pill {
            background: #f8fafc;
            border: 1px solid rgba(0,0,0,0.05);
        }
        [data-theme="dark"] .user-profile-pill {
            background: rgba(255, 255, 255, 0.05) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        /* Sidebar Overlay for Mobile */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
            z-index: 1040;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease-in-out;
        }
        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Close Button Mobile */
        .sidebar-close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(0,0,0,0.05);
            border: none;
            color: var(--text-main);
            transition: all 0.2s ease;
            z-index: 10;
        }
        [data-theme="dark"] .sidebar-close-btn {
            background: rgba(255,255,255,0.1);
            color: #ffffff;
        }
    </style>
</head>

<body>

    <div class="dashboard-wrapper">
        <!-- Sidebar Overlay (Mobile) -->
        <div id="sidebarOverlay" class="sidebar-overlay d-xl-none"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <!-- start logo head -->
                <div class="sidebar-header d-flex justify-content-center py-4 position-relative">
                     <!-- Bouton de fermeture mobile -->
                     <button id="sidebarClose" class="sidebar-close-btn d-xl-none">
                         <i class="bi bi-x-lg"></i>
                     </button>
                     <a href="/" class="sidebar-brand d-flex flex-column align-items-center text-center gap-2">
                     <?php if ($logoData['has_logo'] && !empty($logoData['base64'])): ?>
                 <div class="sidebar-logo-container">
                     <img src="<?= htmlspecialchars($logoData['base64']) ?>" alt="Logo" class="sidebar-logo" style="width: 100%; height: 100%; object-fit: contain;">
                 </div>
                 <?php elseif ($logoData['has_logo'] && !empty($logoData['url'])): ?>
                 <div class="sidebar-logo-container">
                     <img src="<?= htmlspecialchars($logoData['url']) ?>" alt="Logo" class="sidebar-logo" style="width: 100%; height: 100%; object-fit: contain;">
                 </div>
                 <?php else: ?>
                 <div class="logo-fallback-modern text-main-theme">
                     <?= htmlspecialchars($logoData['fallback_letter']) ?>
                 </div>
                 <?php endif; ?>
                 <span class="brand-text fw-bold fs-5 mt-1 text-main-theme">
                 <?= htmlspecialchars((string) $school_identity) ?>
                 </span>
                 </a>
                </div>
                <!-- end logo head -->

            <div class="sidebar-content">
                <?php if (\App\Core\Session::isLogged()): ?>
                    <?php foreach ($nav_items as $item): ?>
                        <?php if (isset($item['section'])): ?>
                            <?php if (in_array($user_role, $item['roles'])): ?>
                                <div class="nav-section">
                                    <div class="nav-section-title"><?= $item['section'] ?></div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if (in_array($user_role, $item['roles'])): ?>
                                <?php $isActive = ($current_path === $item['url']); ?>
                                <a href="<?= $item['url'] ?>" class="nav-link-custom <?= $isActive ? 'active' : '' ?>">
                                    <i class="bi <?= $item['icon'] ?>"></i>
                                    <span><?= $item['label'] ?></span>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="px-3 py-4">
                        <div class="alert alert-info border-0 rounded-4 small mb-4" style="background: rgba(59, 130, 246, 0.05); color: #3b82f6;">
                            <i class="bi bi-info-circle me-2"></i>
                            Accédez à votre espace pour gérer votre établissement.
                        </div>
                        <a href="/login" class="nav-link-custom mb-2">
                            <i class="bi bi-box-arrow-in-right"></i>
                            <span><?= __('login') ?></span>
                        </a>
                        <a href="/register-teacher" class="nav-link-custom">
                            <i class="bi bi-person-plus"></i>
                            <span>S'enregistrer</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (\App\Core\Session::isLogged()): ?>
                <div class="sidebar-footer">
                    <a href="/logout" class="nav-link-custom text-danger">
                        <i class="bi bi-box-arrow-right"></i>
                        <span><?= __('logout') ?></span>
                    </a>
                </div>
            <?php endif; ?>
        </aside>

        <!-- Main Area -->
        <main class="main-area" id="mainArea">
            <!-- Topbar (Premium & Responsive) -->
            <header class="topbar topbar-glass d-flex align-items-center justify-content-between px-3 px-md-4 py-2 py-md-3 shadow-sm border-bottom" style="position: sticky; top: 0; z-index: 1000; backdrop-filter: blur(10px);">
                <div class="d-flex align-items-center gap-2 gap-md-3" style="min-width: 0;">
                    <!-- Menu Toggle (Mobile) -->
                    <button class="btn btn-theme-soft rounded-circle d-xl-none d-flex align-items-center justify-content-center p-0 border-0 shadow-sm hover-elevate transition-all" id="sidebarToggle" style="width: 40px; height: 40px; flex-shrink: 0;">
                        <i class="bi bi-list fs-4 text-main-theme"></i>
                    </button>
                    
                    <!-- Page Title & Breadcrumb -->
                    <div class="page-info d-flex flex-column justify-content-center" style="min-width: 0;">
                        <h1 class="page-title fs-5 fs-md-4 fw-bold text-main-theme mb-0 text-truncate lh-1" style="letter-spacing: -0.02em;">
                            <?= $title ?? __('dashboard') ?>
                        </h1>
                        <nav aria-label="breadcrumb" class="d-none d-md-block mt-1">
                            <ol class="breadcrumb mb-0" style="font-size: 0.75rem; font-weight: 500;">
                                <li class="breadcrumb-item"><a href="/" class="text-decoration-none text-muted hover-primary transition-all"><?= __('home') ?></a></li>
                                <li class="breadcrumb-item active text-primary" aria-current="page">
                                    <?= $title ?? __('dashboard') ?>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <!-- Actions Right -->
                <div class="topbar-actions d-flex align-items-center gap-2 gap-md-3 flex-shrink-0">
                    
                    <!-- Theme Toggle -->
                    <button class="theme-toggle-btn btn btn-theme-soft rounded-circle d-flex align-items-center justify-content-center p-0 border-0 shadow-sm hover-elevate transition-all" id="themeToggle" title="<?= __('change_theme') ?>" style="width: 40px; height: 40px;">
                        <i class="bi bi-moon-stars fs-5 text-main-theme"></i>
                    </button>

                    <!-- User Actions -->
                    <?php if (\App\Core\Session::isLogged()): ?>
                        <div class="dropdown">
                            <a href="#" class="user-profile user-profile-pill d-flex align-items-center gap-2 text-decoration-none p-1 pe-md-3 rounded-pill transition-all hover-elevate shadow-sm" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="user-avatar bg-primary text-white d-flex align-items-center justify-content-center rounded-circle fw-bold shadow-sm" style="width: 34px; height: 34px; font-size: 0.9rem;">
                                    <?= $user_initials ?>
                                </div>
                                <div class="d-none d-md-flex flex-column justify-content-center text-start">
                                    <span class="text-main-theme fw-bold lh-1" style="font-size: 0.85rem;"><?= h($user_name) ?></span>
                                    <span class="text-muted" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 3px;"><?= h(__($user_role)) ?></span>
                                </div>
                                <i class="bi bi-chevron-down text-muted small d-none d-md-block ms-1"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2">
                                <li>
                                    <h6 class="dropdown-header small text-uppercase fw-bold"><?= __('account') ?></h6>
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
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <h6 class="dropdown-header small text-uppercase fw-bold"><?= __('language') ?></h6>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-modern <?= $app_lang === 'fr' ? 'active' : '' ?>"
                                        href="javascript:void(0)" onclick="UX.switchLanguage('fr')">
                                        <span class="fs-5">🇫🇷</span> Français
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-modern <?= $app_lang === 'en' ? 'active' : '' ?>"
                                        href="javascript:void(0)" onclick="UX.switchLanguage('en')">
                                        <span class="fs-5">🇺🇸</span> English
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-modern text-danger" href="/logout">
                                        <i class="bi bi-box-arrow-right"></i> <?= __('logout') ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="d-flex align-items-center gap-2">
                            <a href="/login" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm hover-scale d-none d-md-inline-block">
                                <?= __('login') ?>
                            </a>
                            <div class="dropdown d-md-none">
                                <button class="btn btn-theme-soft rounded-circle d-flex align-items-center justify-content-center p-0 border-0 shadow-sm" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-circle fs-5"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2">
                                    <li><a class="dropdown-item dropdown-item-modern" href="/login"><?= __('login') ?></a></li>
                                    <li><a class="dropdown-item dropdown-item-modern" href="/register-teacher">S'enregistrer</a></li>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </header>
            <!-- Page Content -->
            <div class="content-inner">
                <?= $content ?>
            </div>

            <!-- Footer -->
            <footer class="footer mt-auto py-3 border-top bg-card shadow-sm">
                <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <span class="text-muted-theme small">&copy; <?= date('Y') ?> <strong><?= __('app_name') ?></strong>.
                        <?= __('footer_made_with') ?></span>
                    <div class="d-flex gap-3">
                        <a href="#"
                            class="text-muted-theme text-decoration-none small hover-primary"><?= __('technical_support') ?></a>
                        <a href="#"
                            class="text-muted-theme text-decoration-none small hover-primary"><?= __('privacy_policy') ?></a>
                    </div>
                </div>
            </footer>
        </main>
    </div>

    <!-- Scripts de base -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Services Applicatifs -->
    <script src="/public/js/AlertService.js?v=1.1"></script>
    <script src="/public/js/ux-improvements.js?v=1.1"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Gestion du side-bar mobile avec overlay
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebarToggle');
            const closeBtn = document.getElementById('sidebarClose');
            const overlay = document.getElementById('sidebarOverlay');

            function toggleSidebar() {
                const isOpen = sidebar.classList.toggle('mobile-open');
                if (overlay) {
                    overlay.classList.toggle('active', isOpen);
                }
            }

            if (toggle) toggle.addEventListener('click', toggleSidebar);
            if (closeBtn) closeBtn.addEventListener('click', toggleSidebar);
            if (overlay) overlay.addEventListener('click', toggleSidebar);

            // Gestion du changement de thème
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = themeToggle ? themeToggle.querySelector('i') : null;

            const updateIcon = (theme) => {
                if (!themeIcon) return;
                themeIcon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
            };

            // Init icon
            updateIcon(document.documentElement.getAttribute('data-theme'));

            if (themeToggle) {
                themeToggle.addEventListener('click', () => {
                    const currentTheme = document.documentElement.getAttribute('data-theme');
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

                    document.documentElement.setAttribute('data-theme', newTheme);
                    localStorage.setItem('theme', newTheme);
                    updateIcon(newTheme);
                });
            }

            // --- AUTO-DETECTION DES MESSAGES FLASH ---
            <?php if ($flash_success = \App\Core\Session::getFlash('success')): ?>
                AlertService.success("<?= addslashes((string) __('success_title')) ?>", "<?= addslashes((string) $flash_success) ?>");
            <?php endif; ?>

            <?php if ($flash_error = \App\Core\Session::getFlash('error')): ?>
                AlertService.error("<?= addslashes((string) __('error_title')) ?>", "<?= addslashes((string) $flash_error) ?>");
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

            // Initialisation des tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            tooltipTriggerList.map(t => new bootstrap.Tooltip(t));
        });
    </script>
</body>

</html>