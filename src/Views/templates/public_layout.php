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
            overflow-x: hidden;
            background-color: var(--light);
        }
        
        h1, h2, h3, h4, .font-heading {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
        }
        
        .navbar-landing {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0,0,0,0.03);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 2000;
        }
        
        .navbar-landing.scrolled {
            padding: 8px 0;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 10px 40px rgba(0,0,0,0.04);
        }
        
        .nav-link {
            font-weight: 600;
            color: var(--dark) !important;
            padding: 0.6rem 1.2rem !important;
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
            padding: 120px 0;
            position: relative;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 32px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .glass-card:hover {
            transform: translateY(-12px);
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 30px 60px -15px rgba(0,0,0,0.1);
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
    </style>
</head>
<body class="mesh-gradient">
    <div class="loader-overlay" id="pageLoader">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Header / Navbar -->
    <nav class="navbar navbar-expand-lg navbar-landing fixed-top py-3" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="/">
                <span class="fw-extra-bold fs-3 text-primary" style="font-family: 'Outfit'; letter-spacing: -1px;">
                    <?= __('app_name') ?>
                </span>
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list fs-2"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    <li class="nav-item"><a class="nav-link" href="#home"><?= __('home') ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="#services"><?= __('services') ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="#about"><?= __('about_us') ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact"><?= __('contact') ?></a></li>
                    <li class="nav-item ms-lg-3">
                        <a href="/login" class="btn btn-primary-gradient"><?= __('login') ?></a>
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
                    <h4 class="fw-bold mb-4"><?= __('app_name') ?></h4>
                    <p class="text-white-50"><?= __('app_description') ?></p>
                    <div class="d-flex gap-3 mt-4">
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle"><i class="bi bi-twitter-x"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 ms-lg-auto">
                    <h5 class="fw-bold mb-4">Liens Rapides</h5>
                    <ul class="list-unstyled d-flex flex-column gap-2">
                        <li><a href="#home" class="footer-link">Accueil</a></li>
                        <li><a href="#services" class="footer-link">Services</a></li>
                        <li><a href="#about" class="footer-link">À Propos</a></li>
                        <li><a href="/register-teacher" class="footer-link">Inscription Enseignant</a></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h5 class="fw-bold mb-4">Contact</h5>
                    <ul class="list-unstyled d-flex flex-column gap-3">
                        <li class="d-flex gap-3">
                            <i class="bi bi-geo-alt text-primary"></i>
                            <span class="text-white-50"><?= __('office_address') ?></span>
                        </li>
                        <li class="d-flex gap-3">
                            <i class="bi bi-envelope text-primary"></i>
                            <span class="text-white-50"><?= __('contact_email') ?></span>
                        </li>
                    </ul>
                </div>
            </div>
            <hr class="my-5 opacity-10">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <p class="mb-0 text-white-50 small"><?= __('footer_made_with') ?></p>
                <p class="mb-0 text-white-50 small">&copy; <?= date('Y') ?> <?= __('company_name') ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                document.getElementById('mainNavbar').classList.add('scrolled');
            } else {
                document.getElementById('mainNavbar').classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>
