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
    <link rel="icon" type="image/png" href="/public/assets/camertech-logo.png">
    <link rel="apple-touch-icon" href="/public/assets/camertech-logo.png">
    
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
            --primary: #6d28d9;
            --primary-light: #a78bfa;
            --primary-dark: #5b21b6;
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
            box-shadow: 0 10px 25px -5px rgba(109, 40, 217, 0.4);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        
        .btn-premium:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 20px 35px -10px rgba(109, 40, 217, 0.5);
            color: white;
        }
        
        .mesh-gradient {
            background: 
                radial-gradient(at 0% 0%, rgba(109, 40, 217, 0.1) 0, transparent 50%),
                radial-gradient(at 50% 0%, rgba(245, 158, 11, 0.05) 0, transparent 50%),
                radial-gradient(at 100% 0%, rgba(109, 40, 217, 0.1) 0, transparent 50%);
        }
        
        section {
            padding: 100px 0;
            position: relative;
            overflow-x: hidden;
        }

        @media (max-width: 991px) {
            .navbar-landing {
                background: rgba(255, 255, 255, 0.98);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                padding: 10px 0;
                margin: 10px 15px;
                width: calc(100% - 30px);
                left: 0;
                border-radius: 20px;
                border: 1px solid rgba(0,0,0,0.05);
                box-shadow: 0 10px 40px rgba(0,0,0,0.12);
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
                background: rgba(109, 40, 217, 0.05);
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
            box-shadow: 0 0 0 4px rgba(109, 40, 217, 0.1);
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
            background: radial-gradient(circle, rgba(109, 40, 217, 0.05) 0%, transparent 70%);
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
            box-shadow: 0 10px 20px -5px rgba(109, 40, 217, 0.4);
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

        .landing-page {
            --ink: #10223f;
            --muted: #64748b;
            --brand-blue: #6d28d9;
            --brand-cyan: #a78bfa;
            background: #f7f9fc;
        }

        .landing-page .container {
            overflow: visible;
        }

        .landing-page .navbar-landing,
        .landing-page .navbar-landing.scrolled {
            top: 14px;
            left: 50%;
            width: min(1240px, calc(100% - 40px));
            margin: 0;
            padding: 10px 18px;
            transform: translateX(-50%);
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.94);
            box-shadow: 0 12px 36px rgba(15, 35, 67, 0.1);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .landing-page .navbar-landing .container {
            max-width: 100%;
            padding: 0 8px;
        }

        .landing-page .navbar-brand > div {
            width: 48px !important;
            height: 48px !important;
            flex: 0 0 48px;
            overflow: hidden;
            border-radius: 8px !important;
            background: #fff !important;
            box-shadow: none;
        }

        .landing-page .navbar-brand-logo {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .landing-page .navbar-brand > span {
            color: var(--ink) !important;
            font-size: 1.3rem !important;
        }

        .landing-page .navbar-brand .text-primary {
            color: var(--brand-blue) !important;
        }

        .landing-page .navbar-nav {
            gap: 6px;
        }

        .landing-page .nav-link {
            padding: 10px 14px !important;
            border-radius: 10px;
            color: #52627a;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .landing-page .nav-link::after {
            display: none;
        }

        .landing-page .nav-link:hover {
            background: #f3edff;
            color: var(--brand-blue);
        }

        .landing-page .btn-nav-login {
            display: inline-flex;
            min-height: 42px;
            align-items: center;
            justify-content: center;
            padding: 0 20px;
            border-radius: 11px;
            background: var(--ink);
            color: #fff;
            font-size: 0.9rem;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .landing-page .btn-nav-login:hover {
            transform: translateY(-1px);
            background: var(--brand-blue);
            color: #fff;
        }

        .landing-page main {
            overflow-x: clip !important;
        }

        .landing-page .hero-section {
            min-height: 720px;
            display: flex;
            align-items: center;
            padding: 150px 0 112px;
            color: #fff;
            background:
                radial-gradient(ellipse at 78% 44%, rgba(124, 58, 237, 0.34), transparent 42%),
                radial-gradient(ellipse at 8% 100%, rgba(167, 139, 250, 0.18), transparent 38%),
                linear-gradient(118deg, #17102b 0%, #241442 57%, #321b59 100%);
            isolation: isolate;
        }

        .landing-page .hero-section::before {
            position: absolute;
            z-index: -1;
            inset: 0;
            background-image: linear-gradient(rgba(255,255,255,.035) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.035) 1px, transparent 1px);
            background-size: 54px 54px;
            content: "";
            mask-image: linear-gradient(90deg, transparent, #000 75%);
            pointer-events: none;
        }

        .landing-page .hero-container {
            z-index: 1;
        }

        .landing-page .hero-orb {
            position: absolute;
            z-index: -1;
            width: 360px;
            height: 360px;
            border-radius: 50%;
            filter: blur(90px);
            opacity: .22;
            pointer-events: none;
        }

        .landing-page .hero-orb-one {
            top: 5%;
            right: 5%;
            background: #7c3aed;
        }

        .landing-page .hero-orb-two {
            bottom: -20%;
            left: 24%;
            width: 280px;
            height: 280px;
            background: #a78bfa;
            opacity: .18;
        }

        .landing-page .hero-copy {
            position: relative;
            z-index: 2;
        }

        .landing-page .hero-eyebrow,
        .landing-page .section-kicker {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #ddd6fe;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .landing-page .hero-eyebrow {
            margin-bottom: 24px;
            padding: 9px 13px;
            border: 1px solid rgba(221, 214, 254, .24);
            border-radius: 99px;
            background: rgba(255, 255, 255, .06);
            letter-spacing: .06em;
            text-transform: none;
        }

        .landing-page .hero-eyebrow-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #c4b5fd;
            box-shadow: 0 0 0 4px rgba(196, 181, 253, .16);
        }

        .landing-page .hero-title {
            max-width: 650px;
            margin: 0 0 22px;
            color: #fff;
            font-size: clamp(2.6rem, 5vw, 4.5rem);
            font-weight: 800;
            letter-spacing: -.055em;
            line-height: 1.05;
        }

        .landing-page .hero-title .text-gradient {
            background: linear-gradient(100deg, #c4b5fd 3%, #f0abfc 94%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .landing-page .hero-description {
            max-width: 560px;
            margin: 0 0 30px;
            color: #c3d0e2;
            font-size: 1.08rem;
            line-height: 1.8;
        }

        .landing-page .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .landing-page .hero-actions .btn {
            display: inline-flex;
            min-height: 54px;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 0 22px;
            border-radius: 13px;
            font-size: .96rem;
        }

        .landing-page .hero-actions .btn-premium {
            background: linear-gradient(135deg, #8b5cf6, #6d28d9);
            box-shadow: 0 12px 28px rgba(109, 40, 217, .34);
        }

        .landing-page .hero-actions .btn-premium:hover {
            box-shadow: 0 16px 34px rgba(109, 40, 217, .44);
        }

        .landing-page .btn-hero-outline {
            border: 1px solid rgba(226, 232, 240, .32);
            background: rgba(255,255,255,.05);
            color: #f8fafc;
            font-weight: 700;
        }

        .landing-page .btn-hero-outline:hover {
            border-color: rgba(226,232,240,.65);
            background: rgba(255,255,255,.12);
            color: #fff;
        }

        .landing-page .hero-proof {
            display: flex;
            align-items: center;
            gap: 13px;
            margin-top: 38px;
            color: #dbeafe;
        }

        .landing-page .hero-proof-icon {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border: 1px solid rgba(191,219,254,.2);
            border-radius: 13px;
            background: rgba(255,255,255,.07);
            color: #c4b5fd;
            font-size: 1.15rem;
        }

        .landing-page .hero-proof strong,
        .landing-page .hero-proof span {
            display: block;
        }

        .landing-page .hero-proof strong {
            font-size: .87rem;
        }

        .landing-page .hero-proof span {
            margin-top: 3px;
            color: #91a7c4;
            font-size: .78rem;
        }

        .landing-page .hero-visual {
            padding: 18px 20px 30px;
        }

        .landing-page .hero-visual-glow {
            position: absolute;
            inset: 10% 2%;
            border-radius: 50%;
            background: rgba(139, 92, 246, .32);
            filter: blur(75px);
        }

        .landing-page .hero-image-wrapper {
            position: relative;
            overflow: hidden;
            padding: 12px;
            border: 1px solid rgba(255,255,255,.3);
            border-radius: 21px;
            background: #fff;
            box-shadow: 0 32px 80px rgba(0,0,0,.3), 0 4px 12px rgba(0,0,0,.1);
            transition: transform .3s ease, box-shadow .3s ease;
        }

        .landing-page .hero-image-wrapper:hover {
            transform: translateY(-5px);
            box-shadow: 0 40px 90px rgba(0,0,0,.36);
        }

        .landing-page .hero-image-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 2px 4px 10px;
            color: #7b8798;
            font-size: .68rem;
            font-weight: 600;
        }

        .landing-page .hero-image-topbar > span:last-child {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .landing-page .hero-image-topbar > span:last-child i {
            color: #16a34a;
        }

        .landing-page .hero-window-dots {
            display: flex;
            gap: 4px;
        }

        .landing-page .hero-window-dots i {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #fca5a5;
        }

        .landing-page .hero-window-dots i:nth-child(2) { background: #fcd34d; }
        .landing-page .hero-window-dots i:nth-child(3) { background: #86efac; }

        .landing-page .hero-dashboard {
            display: block;
            width: 100%;
            border: 1px solid #e9eef5;
            border-radius: 11px;
        }

        .landing-page .hero-image-caption {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 3px 1px;
            color: var(--ink);
        }

        .landing-page .hero-caption-icon {
            display: grid;
            width: 34px;
            height: 34px;
            place-items: center;
            border-radius: 10px;
            background: #f1eaff;
            color: #6d28d9;
        }

        .landing-page .hero-image-caption strong,
        .landing-page .hero-image-caption small,
        .landing-page .hero-float-card strong,
        .landing-page .hero-float-card small {
            display: block;
        }

        .landing-page .hero-image-caption strong,
        .landing-page .hero-float-card strong { font-size: .78rem; }
        .landing-page .hero-image-caption small,
        .landing-page .hero-float-card small { margin-top: 2px; color: #8190a5; font-size: .68rem; }

        .landing-page .hero-float-card {
            position: absolute;
            right: -5px;
            bottom: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border: 1px solid rgba(226,232,240,.9);
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 14px 35px rgba(5,20,45,.2);
            color: var(--ink);
        }

        .landing-page .hero-float-icon {
            display: grid;
            width: 32px;
            height: 32px;
            place-items: center;
            border-radius: 10px;
            background: #f1eaff;
            color: #6d28d9;
        }

        .landing-page .hero-scroll-cue {
            position: absolute;
            bottom: 25px;
            left: 50%;
            display: grid;
            width: 28px;
            height: 42px;
            place-items: start center;
            padding-top: 8px;
            transform: translateX(-50%);
            border: 1px solid rgba(221,214,254,.3);
            border-radius: 99px;
            text-decoration: none;
        }

        .landing-page .hero-scroll-cue span {
            width: 4px;
            height: 8px;
            border-radius: 9px;
            background: #c4b5fd;
            animation: landing-scroll 1.6s ease-in-out infinite;
        }

        @keyframes landing-scroll {
            0%, 100% { transform: translateY(0); opacity: .5; }
            50% { transform: translateY(9px); opacity: 1; }
        }

        .landing-page .stats-section {
            padding: 44px 0 34px !important;
            background: #fff !important;
        }

        .landing-page .stats-container {
            max-width: 1120px;
        }

        .landing-page .stats-section .glass-card {
            min-height: 130px;
            padding: 23px 14px !important;
            border: 1px solid #e8eef6 !important;
            border-radius: 17px;
            background: #fff !important;
            box-shadow: 0 8px 24px rgba(16, 34, 63, .045);
            transition: transform .25s ease, box-shadow .25s ease;
        }

        .landing-page .stats-section .glass-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 32px rgba(16, 34, 63, .09);
        }

        .landing-page .stats-section .display-6 {
            color: #6d28d9 !important;
            font-family: 'Outfit', sans-serif;
            font-size: 2rem;
            letter-spacing: -.04em;
        }

        .landing-page .stats-section p {
            margin: 0;
            color: #718096 !important;
            font-size: .67rem;
            letter-spacing: .1em;
        }

        .landing-page .intelligence-section {
            padding: 65px 0 80px !important;
            background: #f7f9fc !important;
        }

        .landing-page .section-heading {
            max-width: 720px;
            margin: 0 auto 42px;
        }

        .landing-page .section-heading .section-kicker {
            margin-bottom: 13px;
            color: #6d28d9;
            font-size: .72rem;
        }

        .landing-page .section-heading h2 {
            margin-bottom: 12px;
            color: #10223f;
            font-size: clamp(1.8rem, 3vw, 2.65rem);
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .landing-page .section-heading p {
            margin: 0;
            color: #687991;
            font-size: 1rem;
            line-height: 1.7;
        }

        .landing-page .intelligence-section .glass-card {
            padding: 27px !important;
            border: 1px solid #e8eef6 !important;
            border-radius: 18px;
            background: #fff !important;
            box-shadow: 0 12px 32px rgba(16,34,63,.045);
            transition: transform .25s ease, box-shadow .25s ease;
        }

        .landing-page .intelligence-section .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 42px rgba(16,34,63,.09);
        }

        .landing-page .intelligence-section .icon-box {
            display: grid;
            width: 50px;
            height: 50px;
            flex: 0 0 50px;
            place-items: center;
            border-radius: 15px;
            background: #f1eaff;
            font-size: 1.35rem !important;
        }

        .landing-page .intelligence-section .col-md-6:nth-child(2) .icon-box {
            background: #f1eaff;
        }

        .landing-page .intelligence-section h4 {
            margin-top: 2px;
            color: #10223f;
            font-size: 1.05rem;
        }

        .landing-page .intelligence-section p {
            margin-bottom: 0;
            line-height: 1.7;
        }

        .landing-page #services {
            padding: 88px 0 !important;
            background: linear-gradient(180deg, #faf9fd, #f1edf8) !important;
        }

        .landing-page #services .section-heading {
            margin-bottom: 72px;
        }

        .landing-page #services .row.align-items-center {
            margin-bottom: 72px !important;
            padding-bottom: 0 !important;
        }

        .landing-page #services .row.align-items-center:last-child {
            margin-bottom: 0 !important;
        }

        .landing-page #services h2 {
            color: #10223f;
            font-size: clamp(1.9rem, 3.2vw, 2.75rem);
            font-weight: 800;
            letter-spacing: -.045em;
        }

        .landing-page #services .badge {
            padding: 8px 13px !important;
            font-size: .71rem;
            font-weight: 700;
            letter-spacing: .05em;
        }

        .landing-page .image-showcase {
            overflow: hidden;
            padding: 9px !important;
            border: 1px solid #e2eaf3 !important;
            border-radius: 20px !important;
            background: #fff !important;
            box-shadow: 0 20px 55px rgba(16,34,63,.12) !important;
        }

        .landing-page .image-showcase img {
            display: block;
            border-radius: 13px !important;
        }

        .landing-page .image-showcase:hover {
            transform: translateY(-4px);
        }

        .landing-page .zoom-overlay {
            inset: 9px;
            width: calc(100% - 18px);
            height: calc(100% - 18px);
            border-radius: 13px !important;
            background: rgba(13, 37, 74, .48);
        }

        .landing-page #services .glass-card {
            border-color: #e5ecf5 !important;
            border-radius: 17px !important;
            box-shadow: 0 10px 28px rgba(16,34,63,.06);
        }

        .landing-page #services .glass-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 36px rgba(16,34,63,.1);
        }

        .landing-page #contact {
            padding: 88px 0 !important;
            background: #fff !important;
        }

        .landing-page #contact .section-heading,
        .landing-page #contact .col-lg-8 {
            margin-bottom: 38px !important;
        }

        .landing-page #contact h2 {
            color: #10223f;
            font-size: clamp(2rem, 3.5vw, 3rem);
            letter-spacing: -.045em;
        }

        .landing-page .contact-card {
            padding: clamp(22px, 4vw, 46px) !important;
            border: 1px solid #e5ecf5 !important;
            border-radius: 22px !important;
            background: #f7f9fc !important;
            box-shadow: 0 18px 55px rgba(16,34,63,.075) !important;
        }

        .landing-page .contact-card .form-label {
            margin-bottom: 8px;
            color: #34445c;
            font-size: .71rem;
            letter-spacing: .06em;
        }

        .landing-page .contact-card .form-control {
            min-height: 54px;
            border: 1px solid #e3eaf3 !important;
            border-radius: 12px;
            background: #fff !important;
            box-shadow: none !important;
            color: #10223f;
        }

        .landing-page .contact-card textarea.form-control {
            min-height: 140px;
        }

        .landing-page .contact-card .form-control:focus {
            border-color: #a78bfa !important;
            box-shadow: 0 0 0 4px rgba(109,40,217,.1) !important;
        }

        .landing-page .contact-card .btn-premium {
            min-height: 54px;
            border-radius: 12px;
        }

        .landing-page .rhetorical-popup {
            width: min(360px, calc(100% - 32px));
        }

        .landing-page .rhetorical-popup .glass-card {
            border: 1px solid #dbe7f5;
            border-radius: 18px;
            background: rgba(255,255,255,.96);
            box-shadow: 0 18px 48px rgba(16,34,63,.18);
        }

        .landing-page .rhetorical-popup .glass-card:hover {
            transform: none;
        }

        .landing-page footer {
            padding-top: 72px;
            padding-bottom: 32px;
            background:
                radial-gradient(ellipse at 85% 0%, rgba(124,58,237,.22), transparent 35%),
                #17102b;
        }

        .landing-page footer .container {
            max-width: 1180px;
        }

        @media (max-width: 991px) {
            .landing-page .navbar-landing,
            .landing-page .navbar-landing.scrolled {
                top: 10px;
                width: calc(100% - 28px);
                padding: 8px 12px;
                border-radius: 16px;
            }

            .landing-page .navbar-collapse {
                padding: 12px 8px 8px;
            }

            .landing-page .hero-section {
                min-height: auto;
                padding: 136px 0 90px;
            }

            .landing-page .hero-visual {
                max-width: 650px;
                margin: 4px auto 0;
            }

            .landing-page .hero-scroll-cue {
                display: none;
            }
        }

        @media (max-width: 767px) {
            .landing-page .hero-section {
                padding: 118px 0 72px;
            }

            .landing-page .hero-title {
                font-size: clamp(2.3rem, 11vw, 3.5rem);
            }

            .landing-page .hero-description {
                font-size: .98rem;
                line-height: 1.7;
            }

            .landing-page .hero-actions {
                align-items: stretch;
            }

            .landing-page .hero-actions .btn {
                width: 100%;
            }

            .landing-page .hero-proof {
                margin-top: 27px;
            }

            .landing-page .hero-visual {
                padding: 8px 6px 25px;
            }

            .landing-page .hero-float-card {
                right: -2px;
                bottom: -2px;
                padding: 9px 12px;
            }

            .landing-page .rhetorical-popup {
                right: 16px;
                bottom: 16px;
                left: auto;
                width: min(360px, calc(100% - 32px));
            }

            .landing-page .rhetorical-popup .glass-card {
                padding: 18px !important;
            }

            .landing-page .hero-image-caption small {
                font-size: .62rem;
            }

            .landing-page .stats-section {
                padding: 28px 0 20px !important;
            }

            .landing-page .stats-section .glass-card {
                min-height: 105px;
                padding: 16px 8px !important;
            }

            .landing-page .stats-section .display-6 {
                font-size: 1.7rem;
            }

            .landing-page .stats-section p {
                font-size: .57rem;
            }

            .landing-page .intelligence-section {
                padding: 48px 0 !important;
            }

            .landing-page .section-heading {
                margin-bottom: 30px;
            }

            .landing-page .intelligence-section .glass-card {
                padding: 21px !important;
            }

            .landing-page #services {
                padding: 58px 0 !important;
            }

            .landing-page #services .section-heading {
                margin-bottom: 44px;
            }

            .landing-page #services .row.align-items-center {
                margin-bottom: 48px !important;
            }

            .landing-page #services h2 {
                margin-bottom: 16px !important;
            }

            .landing-page #contact {
                padding: 58px 0 !important;
            }

            .landing-page #contact .col-lg-8 {
                margin-bottom: 25px !important;
            }

            .landing-page footer {
                padding-top: 54px;
            }

            .landing-page footer .row.g-5 {
                --bs-gutter-y: 2.25rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .landing-page *,
            .landing-page *::before,
            .landing-page *::after {
                scroll-behavior: auto !important;
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }
        }

        .landing-page .hero-section {
            min-height: 0;
            padding: 132px 0 94px;
        }

        .landing-page .hero-title {
            font-size: clamp(2.7rem, 4.45vw, 4.15rem);
            letter-spacing: -.045em;
            line-height: 1.08;
        }

        .landing-page .hero-description {
            max-width: 510px;
            font-size: 1.02rem;
        }

        .landing-page .hero-proof {
            padding-top: 21px;
            border-top: 1px solid rgba(219,234,254,.15);
        }

        .landing-page .hero-visual {
            padding-right: 28px;
        }

        .landing-page .hero-image-wrapper {
            padding: 15px;
            border-radius: 16px;
            box-shadow: 0 26px 70px rgba(0,0,0,.25);
        }

        .landing-page .hero-image-caption {
            padding: 12px 2px 0;
        }

        .landing-page .hero-float-card {
            right: 4px;
            bottom: 8px;
        }

        .landing-page .text-gradient {
            background-image: linear-gradient(100deg, #6d28d9, #a855f7);
        }

        .landing-page .hero-workflow-card {
            padding: 20px 22px;
        }

        .landing-page .hero-workflow-heading {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 18px 2px 20px;
            border-bottom: 1px solid #eee9f7;
        }

        .landing-page .hero-workflow-heading > span:nth-child(2),
        .landing-page .hero-workflow-item > span:nth-child(2) {
            display: flex;
            flex: 1;
            flex-direction: column;
            gap: 3px;
        }

        .landing-page .hero-workflow-heading strong,
        .landing-page .hero-workflow-item strong {
            color: #25203a;
            font-size: .9rem;
            font-weight: 750;
        }

        .landing-page .hero-workflow-heading small,
        .landing-page .hero-workflow-item small {
            color: #7c748e;
            font-size: .76rem;
        }

        .landing-page .hero-caption-icon {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border-radius: 13px;
            background: #f1eaff;
            color: #6d28d9;
            font-size: 1.1rem;
        }

        .landing-page .hero-language-badge {
            padding: 7px 10px;
            border-radius: 99px;
            background: #f4efff;
            color: #6d28d9;
            font-size: .7rem;
            font-weight: 750;
            white-space: nowrap;
        }

        .landing-page .hero-workflow-list {
            display: grid;
            gap: 9px;
            padding: 17px 0 8px;
        }

        .landing-page .hero-workflow-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border: 1px solid #f0edf5;
            border-radius: 12px;
            background: #fff;
        }

        .landing-page .hero-workflow-icon {
            display: grid;
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            place-items: center;
            border-radius: 11px;
            background: #f4efff;
            color: #6d28d9;
            font-size: 1rem;
        }

        .landing-page .hero-workflow-check {
            color: #7c3aed;
            font-size: 1.05rem;
        }

        .landing-page .hero-image-caption {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 15px 2px 2px;
            border-top: 1px solid #eee9f7;
        }

        .landing-page .hero-image-caption > span:last-child {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .landing-page .hero-image-caption strong {
            color: #302442;
            font-size: .8rem;
        }

        .landing-page .hero-image-caption small {
            color: #7c748e;
            font-size: .72rem;
        }

        .landing-page .platform-strip {
            position: relative;
            z-index: 2;
            padding: 22px 0;
            border-bottom: 1px solid #e8edf4;
            background: #fff;
        }

        .landing-page .platform-strip-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px 26px;
            max-width: 1160px;
        }

        .landing-page .platform-strip-label {
            color: #8491a3;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .landing-page .platform-strip-inner > span:not(.platform-strip-label) {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #34445c;
            font-size: .82rem;
            font-weight: 650;
            white-space: nowrap;
        }

        .landing-page .platform-strip-inner i {
            color: #7c3aed;
            font-size: 1rem;
        }

        .landing-page .intelligence-section {
            padding: 92px 0 100px !important;
            background: #fff !important;
        }

        .landing-page .intelligence-section .section-heading {
            margin-bottom: 38px;
        }

        .landing-page .feature-card {
            padding: 25px 23px;
            border: 1px solid #e6ebf2;
            border-radius: 13px;
            background: #fff;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }

        .landing-page .feature-card:hover {
            transform: translateY(-3px);
            border-color: #d9c8fa;
            box-shadow: 0 16px 34px rgba(23,52,91,.075);
        }

        .landing-page .feature-icon {
            display: grid;
            width: 42px;
            height: 42px;
            margin-bottom: 20px;
            place-items: center;
            border-radius: 11px;
            font-size: 1.1rem;
        }

        .landing-page .feature-icon-blue { background: #f1edff; color: #6632bf; }
        .landing-page .feature-icon-violet { background: #f2eaff; color: #6d28d9; }
        .landing-page .feature-icon-amber { background: #f5efff; color: #7c3aed; }
        .landing-page .feature-icon-green { background: #eee9ff; color: #5b21b6; }
        .landing-page .feature-icon-cyan { background: #f3edff; color: #7e22ce; }
        .landing-page .feature-icon-rose { background: #f7edff; color: #9333ea; }

        .landing-page .feature-card h3 {
            margin: 0 0 9px;
            color: #172b49;
            font-size: 1.02rem;
            font-weight: 700;
            letter-spacing: -.015em;
        }

        .landing-page .feature-card p {
            margin: 0;
            color: #69788d;
            font-size: .86rem;
            line-height: 1.7;
        }

        .landing-page .trusted-schools-section {
            padding: 88px 0 92px;
            background: #fff;
        }

        .landing-page .trusted-schools-section .section-heading {
            margin-bottom: 38px;
        }

        .landing-page .trusted-school-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px 25px;
            border: 1px solid #e9e3f2;
            border-radius: 16px;
            background: #fff;
            text-align: center;
            box-shadow: 0 8px 24px rgba(49, 27, 82, .035);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }

        .landing-page .trusted-school-card:hover {
            transform: translateY(-3px);
            border-color: #d9c8fa;
            box-shadow: 0 16px 34px rgba(49, 27, 82, .09);
        }

        .landing-page .trusted-school-logo {
            display: grid;
            width: 116px;
            height: 116px;
            margin-bottom: 20px;
            padding: 8px;
            place-items: center;
            overflow: hidden;
            border: 1px solid #eee9f7;
            border-radius: 18px;
            background: #fff;
        }

        .landing-page .trusted-school-logo img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .landing-page .trusted-school-logo-istec img {
            width: 84%;
            height: 84%;
        }

        .landing-page .trusted-school-type {
            margin-bottom: 8px;
            color: #6d28d9;
            font-size: .7rem;
            font-weight: 750;
            letter-spacing: .07em;
            text-transform: uppercase;
        }

        .landing-page .trusted-school-card h3 {
            margin: 0 0 10px;
            color: #172b49;
            font-size: 1.05rem;
            font-weight: 750;
            line-height: 1.4;
        }

        .landing-page .trusted-school-card p {
            margin: 0;
            color: #69788d;
            font-size: .87rem;
            line-height: 1.7;
        }

        .landing-page #services.benefits-section {
            padding: 88px 0 92px !important;
            background: #f7f5fb !important;
        }

        .landing-page #services.benefits-section .section-heading {
            max-width: 790px;
            margin-bottom: 38px;
        }

        .landing-page #services.benefits-section .section-heading h2 {
            line-height: 1.15;
        }

        .landing-page .benefit-card {
            padding: 28px 25px;
            border: 1px solid #e9e3f2;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(49, 27, 82, .035);
        }

        .landing-page .benefit-icon {
            display: grid;
            width: 46px;
            height: 46px;
            margin-bottom: 20px;
            place-items: center;
            border-radius: 13px;
            background: #f1eaff;
            color: #6d28d9;
            font-size: 1.2rem;
        }

        .landing-page .benefit-card h3 {
            margin: 0 0 10px;
            color: #172b49;
            font-size: 1.04rem;
            font-weight: 750;
        }

        .landing-page .benefit-card p {
            margin: 0;
            color: #69788d;
            font-size: .88rem;
            line-height: 1.7;
        }

        .landing-page .benefits-cta {
            margin-top: 32px;
        }

        .landing-page .benefits-cta p {
            margin-bottom: 15px;
            color: #5c536b;
            font-size: .92rem;
        }

        .landing-page #contact {
            padding: 92px 0 !important;
            background: #f4f7fb !important;
        }

        .landing-page #contact .contact-container {
            max-width: 1120px;
        }

        .landing-page .contact-intro .section-kicker {
            margin-bottom: 17px;
            color: #6d28d9;
            font-size: .72rem;
        }

        .landing-page .contact-intro h2 {
            margin-bottom: 17px;
            color: #172b49;
            font-size: clamp(2rem, 3.7vw, 3rem);
            font-weight: 800;
            letter-spacing: -.045em;
            line-height: 1.12;
        }

        .landing-page .contact-intro h2 .text-gradient {
            background: linear-gradient(110deg, #6d28d9, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .landing-page .contact-lead {
            color: #68788e;
            font-size: .98rem;
            line-height: 1.75;
        }

        .landing-page .contact-points {
            display: grid;
            gap: 20px;
            margin-top: 32px;
        }

        .landing-page .contact-points > div {
            display: flex;
            align-items: flex-start;
            gap: 13px;
        }

        .landing-page .contact-points > div > span {
            display: grid;
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            place-items: center;
            border: 1px solid #e2eaf4;
            border-radius: 10px;
            background: #fff;
            color: #6d28d9;
        }

        .landing-page .contact-points p {
            margin: 1px 0 0;
        }

        .landing-page .contact-points strong,
        .landing-page .contact-points small {
            display: block;
        }

        .landing-page .contact-points strong {
            color: #293d59;
            font-size: .85rem;
        }

        .landing-page .contact-points small {
            margin-top: 3px;
            color: #77869a;
            font-size: .77rem;
            line-height: 1.5;
        }

        .landing-page .contact-card {
            padding: clamp(22px, 3vw, 36px) !important;
            border: 1px solid #e2e9f2 !important;
            border-radius: 15px !important;
            background: #fff !important;
            box-shadow: 0 16px 42px rgba(24,45,77,.075) !important;
        }

        .landing-page .contact-form-heading {
            display: flex;
            align-items: center;
            gap: 13px;
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 1px solid #edf0f5;
        }

        .landing-page .contact-form-icon {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border-radius: 11px;
            background: #edf4ff;
            color: #6d28d9;
        }

        .landing-page .contact-form-heading h3 {
            margin: 0 0 3px;
            color: #203652;
            font-size: 1rem;
            font-weight: 700;
        }

        .landing-page .contact-form-heading p {
            margin: 0;
            color: #8390a2;
            font-size: .74rem;
        }

        .landing-page .contact-card .form-label span {
            color: #d25656;
        }

        .landing-page .contact-card .form-control {
            min-height: 48px;
            padding: .7rem .85rem;
            border-color: #e0e7f0 !important;
            border-radius: 9px;
            font-size: .86rem;
        }

        .landing-page .contact-card textarea.form-control {
            min-height: 116px;
        }

        .landing-page .contact-card .form-label {
            color: #3c4e68;
            font-size: .67rem;
        }

        .landing-page .contact-card .btn-premium {
            min-height: 49px;
            border-radius: 9px;
            font-size: .88rem;
        }

        .landing-page .contact-privacy {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            gap: 6px;
            margin: 12px 0 0;
            color: #8591a1;
            font-size: .69rem;
            text-align: center;
        }

        .landing-page .contact-privacy i {
            color: #52846b;
        }

        .landing-page footer {
            padding-top: 58px;
            padding-bottom: 28px;
        }

        @media (max-width: 991px) {
            .landing-page .hero-section {
                padding: 122px 0 78px;
            }

            .landing-page .hero-title {
                font-size: clamp(2.6rem, 7vw, 3.5rem);
            }

            .landing-page .platform-strip-inner {
                justify-content: center;
            }

            .landing-page .platform-strip-label {
                width: 100%;
                text-align: center;
            }
        }

        @media (max-width: 767px) {
            .landing-page .hero-section {
                padding: 112px 0 62px;
            }

            .landing-page .hero-title {
                font-size: clamp(2.35rem, 10.5vw, 3.2rem);
            }

            .landing-page .hero-visual {
                padding-right: 8px;
            }

            .landing-page .platform-strip {
                padding: 18px 0;
            }

            .landing-page .platform-strip-inner {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                justify-items: start;
                gap: 14px 10px;
            }

            .landing-page .platform-strip-inner > span:not(.platform-strip-label) {
                font-size: .72rem;
            }

            .landing-page .platform-strip-label {
                grid-column: 1 / -1;
            }

            .landing-page .intelligence-section {
                padding: 62px 0 !important;
            }

            .landing-page .feature-card {
                padding: 20px;
            }

            .landing-page .feature-icon {
                margin-bottom: 14px;
            }

            .landing-page .trusted-schools-section {
                padding: 62px 0;
            }

            .landing-page .trusted-school-card {
                padding: 24px 20px;
            }

            .landing-page #services.benefits-section {
                padding: 62px 0 !important;
            }

            .landing-page #services.benefits-section .section-heading {
                margin-bottom: 30px;
            }

            .landing-page .hero-workflow-card {
                padding: 15px;
            }

            .landing-page #contact {
                padding: 62px 0 !important;
            }

            .landing-page .contact-points {
                gap: 14px;
                margin: 22px 0 28px;
            }

            .landing-page .contact-card {
                padding: 20px !important;
            }
        }
    </style>
</head>
<body class="mesh-gradient landing-page">
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
                <div class="d-flex align-items-center justify-content-center">
                    <img class="navbar-brand-logo" src="/public/assets/camertech-logo.png" alt="Logo Camertech, éditeur de NoteMaster">
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
                    <li class="nav-item"><a class="nav-link" href="#features">Fonctionnalités</a></li>
                    <li class="nav-item"><a class="nav-link" href="#etablissements">Nos établissements</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">Pourquoi NoteMaster</a></li>
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
                        <li><a href="#services" class="footer-link">Pourquoi NoteMaster</a></li>
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
                            <span class="text-secondary small">contact@camertech.com</span>
                        </li>
                        <li class="d-flex gap-3">
                            <div class="social-btn" style="width: 32px; height: 32px; pointer-events: none;"><i class="bi bi-whatsapp fs-6"></i></div>
                            <span class="text-secondary small">+237 679 164 801 / +237 659 099 093</span>
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
