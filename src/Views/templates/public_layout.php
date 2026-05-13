<?php
/**
 * Layout pour la vitrine publique (Landing Page)
 * Sans sidebar, optimisé SEO et conversion.
 */
$app_lang = \App\Core\Locale::get();
?>
<!DOCTYPE html>
<html lang="<?= $app_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= (isset($title) ? $title . ' | ' : '') . __('app_name') ?> par <?= __('company_name') ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= $meta_description ?? __('meta_description_default') ?>">
    <meta name="keywords" content="<?= __('meta_keywords_default') ?>">
    <meta name="author" content="<?= __('company_name') ?>">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- AOS Animations -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #60a5fa;
            --primary-dark: #1d4ed8;
            --secondary: #64748b;
            --dark: #0f172a;
            --light: #f8fafc;
            --accent: #f59e0b;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            color: var(--dark);
            overflow-x: hidden !important;
            width: 100% !important;
            position: relative;
            background-color: var(--light);
            -webkit-font-smoothing: antialiased;
            touch-action: pan-y;
        }

        html {
            overflow-x: hidden !important;
            width: 100% !important;
            scroll-behavior: smooth;
        }

        main {
            overflow-x: hidden !important;
            width: 100%;
        }

        * {
            box-sizing: border-box;
            max-width: 100vw;
        }
        
        h1, h2, h3, h4, .font-heading {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
        }
        
        .row {
            margin-left: 0;
            margin-right: 0;
        }

        .container {
            overflow: hidden;
        }

        img {
            max-width: 100%;
            height: auto;
        }
        .navbar-landing {
            background: rgba(255, 255, 255, 0);
            backdrop-filter: blur(0px);
            border-bottom: 1px solid transparent;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 2000;
            padding: 24px 0;
        }
        
        .navbar-landing.scrolled {
            padding: 12px 0;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 15px 35px -10px rgba(0,0,0,0.08);
            margin-top: 10px;
            width: 95%;
            left: 2.5%;
            border-radius: 100px;
        }

        .nav-link {
            color: var(--secondary);
            font-weight: 600;
            font-size: 0.95rem;
            padding: 8px 20px !important;
            transition: all 0.3s;
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: all 0.3s;
            transform: translateX(-50%);
        }
        
        .nav-link:hover::after {
            width: 20px;
        }
        
        .btn-premium {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            color: white;
            padding: 14px 34px;
            border-radius: 100px;
            font-weight: 700;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        
        .btn-premium:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 20px 35px -10px rgba(37, 99, 235, 0.5);
            color: white;
        }
        
        .mesh-gradient {
            background: 
                radial-gradient(at 0% 0%, rgba(37, 99, 235, 0.1) 0, transparent 50%),
                radial-gradient(at 50% 0%, rgba(245, 158, 11, 0.05) 0, transparent 50%),
                radial-gradient(at 100% 0%, rgba(37, 99, 235, 0.1) 0, transparent 50%);
        }
        
        section {
            padding: 100px 0;
            position: relative;
            overflow-x: hidden;
        }

        @media (max-width: 991px) {
            .navbar-landing {
                background: rgba(255, 255, 255, 0.98) !important;
                backdrop-filter: blur(20px) !important;
                -webkit-backdrop-filter: blur(20px) !important;
                padding: 10px 0 !important;
                margin: 10px 15px !important;
                width: calc(100% - 30px) !important;
                left: 0 !important;
                border-radius: 20px !important;
                border: 1px solid rgba(0,0,0,0.05) !important;
                box-shadow: 0 10px 40px rgba(0,0,0,0.12) !important;
            }
            .navbar-collapse {
                background: transparent;
                padding: 20px 10px;
                text-align: center;
                max-height: 80vh;
                overflow-y: auto;
            }
            .nav-item {
                width: 100%;
                margin-bottom: 5px;
            }
            .nav-link {
                font-size: 1.05rem;
                padding: 15px !important;
                border-radius: 12px;
                color: var(--dark) !important;
                transition: background 0.3s;
            }
            .nav-link:hover {
                background: rgba(37, 99, 235, 0.05);
                color: var(--primary) !important;
            }
            .btn-nav-login {
                display: block;
                width: 100%;
                margin-top: 15px;
                padding: 14px;
                font-size: 1rem;
            }
            
            /* Background dimming when menu is open - Sidebar style */
            .navbar-overlay {
                position: fixed;
                top: 0; left: 0; width: 100vw; height: 100vh;
                background: rgba(0, 0, 0, 0.4);
                backdrop-filter: blur(4px);
                -webkit-backdrop-filter: blur(4px);
                z-index: 1500;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease-in-out;
            }
            .navbar-overlay.active {
                opacity: 1;
                visibility: visible;
            }
        }

        /* Custom Hamburger Animation */
        .navbar-toggler {
            width: 45px;
            height: 45px;
            position: relative;
            transition: .5s ease-in-out;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .toggler-icon {
            display: block;
            position: absolute;
            height: 3px;
            width: 25px;
            background: var(--primary);
            border-radius: 9px;
            opacity: 1;
            left: 10px;
            transform: rotate(0deg);
            transition: .25s ease-in-out;
        }
        .toggler-icon:nth-child(1) { top: 14px; }
        .toggler-icon:nth-child(2), .toggler-icon:nth-child(3) { top: 21px; }
        .toggler-icon:nth-child(4) { top: 28px; }

        .navbar-toggler.open .toggler-icon:nth-child(1) { top: 21px; width: 0%; left: 50%; }
        .navbar-toggler.open .toggler-icon:nth-child(2) { transform: rotate(45deg); }
        .navbar-toggler.open .toggler-icon:nth-child(3) { transform: rotate(-45deg); }
        .navbar-toggler.open .toggler-icon:nth-child(4) { top: 21px; width: 0%; left: 50%; }

        @media (max-width: 768px) {
            section {
                padding: 40px 0;
            }
            .display-3 {
                font-size: 1.85rem !important;
                letter-spacing: -0.5px;
            }
            .display-5 {
                font-size: 1.6rem !important;
            }
            .lead {
                font-size: 0.95rem !important;
            }
            .btn-premium, .btn-lg {
                padding: 10px 20px !important;
                font-size: 0.9rem !important;
                width: 100%;
                text-align: center;
            }
            .hero-image-container {
                margin-top: 30px;
            }
        }
        
        ::selection {
            background: var(--primary);
            color: white;
        }

        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
            border: 2px solid #f1f5f9;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-light);
        }

        .form-control {
            border-radius: 14px;
            padding: 0.8rem 1.2rem;
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s;
        }

        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            border-color: var(--primary);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 24px;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05);
        }
        
        .glass-card:hover {
            transform: translateY(-10px) scale(1.02);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 40px 80px -20px rgba(0,0,0,0.1);
            border-color: var(--primary-light);
        }

        .text-gradient {
            background: linear-gradient(135deg, var(--primary) 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .bg-gradient-soft {
            background: linear-gradient(180deg, #fff 0%, #f1f5f9 100%);
        }

        .loader-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255,255,255,0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 3000;
            backdrop-filter: blur(5px);
        }

        footer {
            background-color: #050a18;
            color: white;
            padding-top: 100px;
            padding-bottom: 50px;
            position: relative;
            overflow: hidden;
        }

        footer::before {
            content: '';
            position: absolute;
            top: 0; left: 50%; width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.05) 0%, transparent 70%);
            transform: translateX(-50%) translateY(-50%);
            pointer-events: none;
        }

        .footer-link {
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.95rem;
            display: inline-block;
        }

        .footer-link:hover {
            color: white;
            transform: translateX(5px);
        }

        .social-btn {
            width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 12px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.05);
            color: #94a3b8;
            transition: all 0.3s;
        }

        .social-btn:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-5px);
            box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.4);
        }

        .footer-logo {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.75rem;
            letter-spacing: -1px;
            background: linear-gradient(135deg, #fff 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="mesh-gradient">
    <div class="loader-overlay" id="pageLoader">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <div id="navbarOverlay" class="navbar-overlay d-lg-none"></div>

    <!-- Header / Navbar -->
    <nav class="navbar navbar-expand-lg navbar-landing fixed-top" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="/">
                <div class="bg-primary rounded-3 p-1 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                    <i class="bi bi-mortarboard-fill text-white fs-6"></i>
                </div>
                <span class="fw-extra-bold fs-4 text-dark" style="font-family: 'Outfit'; letter-spacing: -1px;">
                    Note<span class="text-primary">Master</span>
                </span>
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" id="navToggler">
                <span class="toggler-icon"></span>
                <span class="toggler-icon"></span>
                <span class="toggler-icon"></span>
                <span class="toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#home"><?= __('home') ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="#services"><?= __('services') ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact"><?= __('contact') ?></a></li>
                    <li class="nav-item ms-lg-4">
                        <a href="/login" class="btn-nav-login"><?= __('login') ?></a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <main>
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4">
                    <div class="footer-logo mb-4"><?= __('app_name') ?></div>
                    <p class="text-secondary mb-4" style="line-height: 1.8;">
                        La solution SaaS tout-en-un pour les établissements scolaires tournés vers l'avenir au Cameroun. 
                        Digitalisez vos processus académiques avec excellence et simplicité.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#" class="social-btn"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-btn"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="social-btn"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="social-btn"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 ms-lg-auto">
                    <h5 class="fw-bold mb-4 text-white">Plateforme</h5>
                    <ul class="list-unstyled d-flex flex-column gap-3">
                        <li><a href="#home" class="footer-link">Accueil</a></li>
                        <li><a href="#services" class="footer-link">Services</a></li>
                        <li><a href="/login" class="footer-link">Se Connecter</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h5 class="fw-bold mb-4 text-white">Aide</h5>
                    <ul class="list-unstyled d-flex flex-column gap-3">
                        <li><a href="#contact" class="footer-link">Support IT</a></li>
                        <li><a href="/register-teacher" class="footer-link">Guide Enseignant</a></li>
                        <li><a href="#" class="footer-link">Sécurité</a></li>
                        <li><a href="#" class="footer-link">Confidentialité</a></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h5 class="fw-bold mb-4 text-white">Contact Direct</h5>
                    <ul class="list-unstyled d-flex flex-column gap-3">
                        <li class="d-flex gap-3">
                            <div class="social-btn" style="width: 32px; height: 32px; pointer-events: none;"><i class="bi bi-geo-alt fs-6"></i></div>
                            <span class="text-secondary small">Douala, Cameroun</span>
                        </li>
                        <li class="d-flex gap-3">
                            <div class="social-btn" style="width: 32px; height: 32px; pointer-events: none;"><i class="bi bi-envelope fs-6"></i></div>
                            <span class="text-secondary small">evaricekuete2@gmail.com</span>
                        </li>
                        <li class="d-flex gap-3">
                            <div class="social-btn" style="width: 32px; height: 32px; pointer-events: none;"><i class="bi bi-whatsapp fs-6"></i></div>
                            <span class="text-secondary small">+237 679 164 801</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-top border-white border-opacity-5 mt-5 pt-5">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start">
                        <p class="mb-0 text-secondary small">
                            &copy; <?= date('Y') ?> <strong>Camertech</strong>. Propulsé par l'innovation technologique africaine.
                        </p>
                    </div>
                    <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                        <span class="text-secondary small">Design Premium & Performance</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialisation de AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100,
            easing: 'ease-out-cubic'
        });

        const mainNavbar = document.getElementById('mainNavbar');
        const navToggler = document.getElementById('navToggler');
        const navbarCollapse = document.getElementById('navbarNav');

        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                mainNavbar.classList.add('scrolled');
            } else {
                mainNavbar.classList.remove('scrolled');
            }
        });

        // Gestion du menu mobile (Animation & Overlay style Sidebar)
        const navbarOverlay = document.getElementById('navbarOverlay');
        const bsCollapse = new bootstrap.Collapse(navbarCollapse, {toggle: false});

        function closeMobileMenu() {
            navToggler.classList.remove('open');
            navbarOverlay.classList.remove('active');
            document.body.classList.remove('menu-open');
            bsCollapse.hide();
        }

        function openMobileMenu() {
            navToggler.classList.add('open');
            navbarOverlay.classList.add('active');
            document.body.classList.add('menu-open');
            bsCollapse.show();
        }

        navToggler.addEventListener('click', function() {
            if (this.classList.contains('open')) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });

        // Fermer le menu au clic sur l'overlay (comme la sidebar)
        if (navbarOverlay) {
            navbarOverlay.addEventListener('click', closeMobileMenu);
        }

        // Fermer le menu au clic sur un lien
        document.querySelectorAll('.nav-link, .btn-nav-login').forEach(link => {
            link.addEventListener('click', () => {
                if (navToggler.classList.contains('open')) {
                    closeMobileMenu();
                }
            });
        });
    </script>
</body>
</html>
