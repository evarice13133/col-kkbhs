<?php
/**
 * Vue : Page de Connexion (Login)
 *
 * Ce fichier est responsable de l'affichage de l'interface d'authentification sécurisée.
 * 
 * [Concept Architecture (MVC) pour les Juniors] : 
 * - La Vue ne doit jamais requêter directement la base de données.
 * - Le `AuthController` prépare toutes les variables nécessaires ($brandSettings, $logoData, 
 *   $csrfToken, $error) et les passe à ce fichier. La Vue ne s'occupe que du rendu (UI).
 * 
 * [Design System & UI/UX] :
 * - Le design utilise des variables CSS natives (--brand-primary, etc.) injectées dynamiquement.
 * - L'effet "Glassmorphism" (transparence + flou) est utilisé pour un rendu premium.
 */

// Simplification pour la gestion bilingue dans la vue
$isEn = __('lang') === 'en';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('login_title_seo') ?> | <?= htmlspecialchars((string) $brandSettings['school_code']) ?></title>
    <meta name="description" content="<?= __('meta_description_default') ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        :root {
            --brand-primary:
                <?= htmlspecialchars((string) $brandSettings['theme_login_button']) ?>
            ;
            --brand-bg-start:
                <?= htmlspecialchars((string) $brandSettings['theme_login_bg_start']) ?>
            ;
            --brand-bg-mid:
                <?= htmlspecialchars((string) $brandSettings['theme_login_bg_mid']) ?>
            ;
            --brand-bg-end:
                <?= htmlspecialchars((string) $brandSettings['theme_login_bg_end']) ?>
            ;
            --brand-accent:
                <?= htmlspecialchars((string) $brandSettings['theme_login_bubble']) ?>
            ;
            --text-main: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --card-bg: rgba(255, 255, 255, 0.85);
            --border-color: rgba(226, 232, 240, 0.8);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 50%, #f8fafc 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem; /* Marge de sécurité pour les petits écrans */
            overflow-y: auto; /* Permet le scroll sur les petits ordinateurs portables */
            overflow-x: hidden;
            position: relative;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* --- Premium Background Pattern --- */
        .mesh-gradient {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background:
                radial-gradient(ellipse at 20% 20%, rgba(99, 102, 241, 0.12) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 80%, rgba(59, 130, 246, 0.1) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 50%, rgba(139, 92, 246, 0.05) 0%, transparent 70%);
        }

        .login-container {
            width: 100%;
            max-width: 1100px;
            min-height: 600px; /* S'adapte à la taille du contenu au lieu d'une hauteur fixe qui casse le design */
            height: auto;
            display: flex;
            background: var(--card-bg);
            border-radius: 28px;
            box-shadow:
                0 25px 50px -12px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.6) inset,
                0 0 0 1px rgba(0, 0, 0, 0.05);
            margin: auto; /* Centre automatiquement sans casser sur les petits écrans */
            overflow: hidden;
            position: relative;
            animation: containerEntry 0.7s cubic-bezier(0.16, 1, 0.3, 1);
            backdrop-filter: blur(20px) saturate(180%);
        }

        @keyframes containerEntry {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(20px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* --- Left Side: Showcase --- */
        .login-showcase {
            flex: 1.1;
            background: linear-gradient(160deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            padding: 3.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }

        .login-showcase::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(circle at 30% 20%, rgba(99, 102, 241, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 70% 80%, rgba(59, 130, 246, 0.1) 0%, transparent 40%);
            pointer-events: none;
        }

        .login-showcase::after {
            content: '';
            position: absolute;
            top: -40%;
            right: -40%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, var(--brand-primary) 0%, transparent 70%);
            opacity: 0.06;
            filter: blur(100px);
        }

        .brand-section {
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 10;
        }

        .brand-logo-wrapper {
            width: 52px;
            height: 52px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            padding: 10px;
            box-shadow:
                0 4px 6px -1px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.1) inset;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .brand-logo-wrapper:hover {
            transform: scale(1.02);
            box-shadow:
                0 8px 12px -2px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.15) inset;
        }

        .brand-logo-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .brand-name {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #ffffff;
        }

        .brand-tagline {
            font-size: 0.75rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 0.12em;
            margin-top: -2px;
        }

        .showcase-content h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2.5rem;
            line-height: 1.2;
            margin-bottom: 1rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #ffffff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .showcase-content p {
            font-size: 1rem;
            line-height: 1.75;
            color: rgba(255, 255, 255, 0.75);
            max-width: 380px;
            font-weight: 400;
        }

        .showcase-features {
            display: grid;
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            animation: slideInLeft 0.5s ease forwards;
            opacity: 0;
        }

        .feature-item:nth-child(1) {
            animation-delay: 0.4s;
        }

        .feature-item:nth-child(2) {
            animation-delay: 0.5s;
        }

        .feature-item:nth-child(3) {
            animation-delay: 0.6s;
        }

        @keyframes slideInLeft {
            from {
                transform: translateX(-20px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .feature-icon {
            width: 38px;
            height: 38px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            color: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
        }

        .feature-item:hover .feature-icon {
            background: rgba(255, 255, 255, 0.12);
            transform: translateY(-2px);
        }

        .feature-text h4 {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: rgba(255, 255, 255, 0.95);
        }

        .feature-text p {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.6);
        }

        /* --- Right Side: Portal --- */
        .login-portal {
            flex: 0.9;
            padding: 4rem 3.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: rgba(255, 255, 255, 0.6);
        }

        .portal-header {
            margin-bottom: 2rem;
        }

        .portal-header h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.875rem;
            color: var(--text-main);
            margin-bottom: 0.375rem;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        .portal-header p {
            color: var(--text-secondary);
            font-size: 0.9375rem;
            line-height: 1.6;
        }

        /* --- Form Elements --- */
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.8125rem;
            font-weight: 500;
            color: var(--text-secondary);
            transition: all 0.2s ease;
        }

        .form-group:focus-within .form-label {
            color: var(--brand-primary);
            transform: translateX(2px);
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i.prefix-icon {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 1.1rem;
            transition: color 0.2s;
        }

        .input-control {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.875rem;
            background: rgba(255, 255, 255, 0.8);
            border: 1.5px solid var(--border-color);
            border-radius: 14px;
            font-family: inherit;
            font-size: 0.9375rem;
            color: var(--text-main);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .input-control::placeholder {
            color: var(--text-muted);
            font-weight: 400;
        }

        .input-control:hover {
            border-color: #cbd5e1;
            background: rgba(255, 255, 255, 0.95);
        }

        .input-control:focus {
            outline: none;
            background: #ffffff;
            border-color: var(--brand-primary);
            box-shadow:
                0 0 0 4px rgba(var(--brand-primary-rgb, 59, 130, 246), 0.1),
                0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transform: translateY(-1px);
        }

        .input-wrapper:has(.input-control:focus) .prefix-icon {
            color: var(--brand-primary);
        }

        /* Password Toggle */
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(241, 245, 249, 0.8);
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .password-toggle:hover {
            color: var(--text-main);
            background: rgba(226, 232, 240, 0.9);
            transform: translateY(-50%) scale(1.05);
        }

        .password-toggle:active {
            transform: translateY(-50%) scale(0.95);
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, var(--brand-primary) 0%, color-mix(in srgb, var(--brand-primary) 85%, black) 100%);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
            box-shadow:
                0 4px 6px -1px rgba(0, 0, 0, 0.1),
                0 2px 4px -2px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.1) inset;
            position: relative;
            overflow: hidden;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-submit:hover::before {
            left: 100%;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow:
                0 10px 15px -3px rgba(0, 0, 0, 0.15),
                0 4px 6px -4px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.1) inset;
        }

        .btn-submit:active {
            transform: translateY(0);
            box-shadow:
                0 4px 6px -1px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.1) inset;
        }

        .btn-submit i {
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .btn-submit:hover i {
            transform: translateX(4px);
        }

        /* Error Message */
        .alert-error {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 1.5px solid #fecaca;
            padding: 0.875rem 1rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 0.625rem;
            color: #b91c1c;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 1.25rem;
            animation: shake 0.4s cubic-bezier(.36, .07, .19, .97) both;
            box-shadow: 0 1px 3px rgba(185, 28, 28, 0.1);
        }

        .alert-error i {
            font-size: 1.1rem;
            flex-shrink: 0;
            color: #dc2626;
        }

        @keyframes shake {

            10%,
            90% {
                transform: translate3d(-1px, 0, 0);
            }

            20%,
            80% {
                transform: translate3d(2px, 0, 0);
            }

            30%,
            50%,
            70% {
                transform: translate3d(-4px, 0, 0);
            }

            40%,
            60% {
                transform: translate3d(4px, 0, 0);
            }
        }

        .portal-footer {
            margin-top: 2rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--border-color);
            text-align: center;
            font-size: 0.8125rem;
            color: var(--text-secondary);
        }

        .portal-footer a {
            color: var(--brand-primary);
            text-decoration: none;
            font-weight: 600;
            position: relative;
            transition: color 0.2s ease;
        }

        .portal-footer a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 1.5px;
            background: var(--brand-primary);
            transition: width 0.3s ease;
        }

        .portal-footer a:hover {
            color: color-mix(in srgb, var(--brand-primary) 80%, black);
        }

        .portal-footer a:hover::after {
            width: 100%;
        }

        /* --- Social Links/Helpers --- */
        .helper-links {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 0.75rem;
            font-size: 0.8125rem;
        }

        .helper-links a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.2s ease;
            font-weight: 500;
            position: relative;
        }

        .helper-links a:hover {
            color: var(--brand-primary);
            transform: translateX(1px);
        }

        .helper-links label {
            cursor: pointer;
            user-select: none;
            transition: color 0.2s ease;
        }

        .helper-links input[type="checkbox"]:hover+label {
            color: var(--brand-primary);
        }

        /* --- Global Loader Injection --- */
        #global-loader {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .loader-spinner {
            width: 44px;
            height: 44px;
            border: 3px solid #f1f5f9;
            border-top-color: var(--brand-primary);
            border-right-color: var(--brand-primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* --- Ultra-Minimal Mobile & Tablet Design --- */
        @media (max-width: 992px) {
            body {
                background: #ffffff;
                /* Fond blanc pur pour une simplicité absolue */
                padding: 0;
                display: block;
                height: 100vh;
                overflow-y: auto;
            }

            .mesh-gradient {
                display: none;
                /* Suppression des effets complexes sur mobile */
            }

            .login-container {
                max-width: 100%;
                width: 100%;
                min-height: 100vh;
                margin: 0;
                border-radius: 0;
                box-shadow: none;
                background: transparent;
                backdrop-filter: none;
                flex-direction: column;
                justify-content: flex-start;
                animation: none;
            }

            /* En-tête ultra épuré avec juste le logo */
            .login-showcase {
                padding: 4rem 1.5rem 1rem;
                flex: none;
                background: transparent;
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .login-showcase::before,
            .login-showcase::after {
                display: none;
            }

            .brand-section {
                flex-direction: column;
                justify-content: center;
                gap: 0.5rem;
                text-align: center;
            }

            .brand-logo-wrapper {
                width: 60px;
                height: 60px;
                background: transparent;
                border: none;
                box-shadow: none;
                padding: 0;
            }

            .brand-name {
                font-size: 1.5rem;
                color: #0f172a;
                /* Texte sombre et épuré */
                letter-spacing: -0.04em;
            }

            .brand-tagline {
                display: none;
                /* On enlève le texte inutile */
            }

            .showcase-content,
            .showcase-footer {
                display: none;
                /* On cache les longs textes */
            }

            /* Formulaire central et spacieux */
            .login-portal {
                padding: 1rem 1.5rem 3rem;
                flex: 1;
                display: flex;
                flex-direction: column;
                background: transparent;
                max-width: 440px;
                margin: 0 auto;
                width: 100%;
            }

            .portal-header {
                margin-bottom: 2.5rem;
                text-align: center;
            }

            .portal-header span {
                display: none !important;
            }

            .portal-header h2 {
                font-size: 1.75rem;
                color: #0f172a;
                letter-spacing: -0.04em;
                margin-bottom: 0.5rem;
            }

            .portal-header p {
                font-size: 0.95rem;
                color: #64748b;
            }

            /* Inputs minimalistes fond gris (Style Apple/Stripe) */
            .form-group {
                margin-bottom: 1.25rem;
            }

            .form-label {
                font-size: 0.85rem;
                font-weight: 600;
                color: #334155;
                margin-bottom: 0.5rem;
            }

            .input-wrapper i.prefix-icon {
                color: #94a3b8;
                left: 1rem;
            }

            .input-control {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                box-shadow: none;
                border-radius: 12px;
                padding: 0.9rem 1rem 0.9rem 2.75rem;
                font-size: 16px;
                /* Empêche le zoom auto sur iOS */
            }

            .input-control:hover,
            .input-control:focus {
                background: #ffffff;
                border-color: var(--brand-primary);
                box-shadow: 0 0 0 3px rgba(var(--brand-primary-rgb, 59, 130, 246), 0.1);
            }

            .password-toggle {
                background: transparent;
                right: 0.5rem;
            }

            /* Gros bouton "Pilule" très visuel */
            .btn-submit {
                border-radius: 100px;
                /* Forme pilule */
                padding: 1rem;
                font-size: 1rem;
                margin-top: 1.5rem;
                background: var(--brand-primary);
                box-shadow: 0 8px 20px -6px var(--brand-primary);
            }

            .btn-submit::before {
                display: none;
            }

            .btn-submit:hover {
                transform: translateY(-2px);
                box-shadow: 0 12px 24px -8px var(--brand-primary);
            }

            /* Liens d'aide discrets */
            .helper-links {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                margin-top: 1.5rem;
            }

            .helper-links label,
            .helper-links a {
                font-size: 0.85rem;
            }

            .portal-footer {
                margin-top: auto;
                padding-top: 3rem;
                border-top: none;
                font-size: 0.85rem;
            }
        }
    </style>
</head>

<body>
    <div class="mesh-gradient"></div>

    <div id="global-loader">
        <div class="loader-spinner"></div>
        <p style="margin-top: 15px; font-weight: 600; color: #1e293b;">Validation en cours...</p>
    </div>

    <main class="login-container">
        <!-- Section Showcase (Left) -->
        <section class="login-showcase">
            <div class="brand-section">
                <div class="brand-logo-wrapper">
                    <?php if ($logoData['has_logo'] && !empty($logoData['base64'])): ?>
                        <img src="<?= htmlspecialchars($logoData['base64']) ?>"
                            alt="<?= htmlspecialchars((string) ($brandSettings['school_code'] ?? 'School')) ?>"
                            loading="eager" style="width: 100%; height: 100%; object-fit: contain;">
                    <?php elseif ($logoData['has_logo'] && !empty($logoData['url'])): ?>
                        <img src="<?= htmlspecialchars($logoData['url']) ?>"
                            alt="<?= htmlspecialchars((string) ($brandSettings['school_code'] ?? 'School')) ?>"
                            loading="eager" style="width: 100%; height: 100%; object-fit: contain;">
                    <?php else: ?>
                        <div
                            style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700; color: rgba(255,255,255,0.6); letter-spacing: 2px;">
                            <?= htmlspecialchars($logoData['fallback_letter']) ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <!-- [Junior] htmlspecialchars sécurise les données venant de la BDD contre les failles XSS -->
                    <div class="brand-name"><?= htmlspecialchars((string) $brandSettings['school_code']) ?></div>
                    <div class="brand-tagline"><?= $isEn ? 'Smart Education' : 'Pilotage Scolaire' ?></div>
                </div>
            </div>

            <!-- Contenu de présentation (Texte & Fonctionnalités) -->
            <div class="showcase-content">
                <h1><?= $isEn ? 'Manage School Life with Ease.' : 'Pilotez la vie scolaire avec sérénité.' ?></h1>
                <p><?= $isEn ? 'Enter your professional interface to track student progress, grades, and administrative data in one place.' : 'Accédez à votre espace professionnel pour centraliser le suivi des élèves, les résultats et les indicateurs de réussite.' ?>
                </p>

                <div class="showcase-features">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                        <div class="feature-text">
                            <h4><?= __('lang') === 'en' ? 'Secure Login' : 'Connexion Sécurisée' ?></h4>
                            <p><?= __('lang') === 'en' ? 'Enterprise-grade protection' : 'Protection de vos données' ?>
                            </p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-lightning-charge"></i></div>
                        <div class="feature-text">
                            <h4><?= __('lang') === 'en' ? 'Daily Efficiency' : 'Efficacité Quotidienne' ?></h4>
                            <p><?= __('lang') === 'en' ? 'Streamlined workflows' : 'Flux de travail optimisés' ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="showcase-footer">
                <p style="font-size: 0.8rem; opacity: 0.5;">&copy; <?= date('Y') ?> NotesMaster v2.0</p>
            </div>
        </section>

        <!-- Section Portail (Right) - Formulaire de connexion -->
        <section class="login-portal">
            <div class="portal-header">
                <span
                    style="color: var(--brand-primary); font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.1em; display: block; margin-bottom: 8px;"><?= $isEn ? 'Workspace' : 'Espace de travail' ?></span>
                <h2><?= $isEn ? 'Welcome Back' : 'Ravi de vous revoir' ?></h2>
                <p><?= $isEn ? 'Access your dashboard and start managing.' : 'Entrez vos identifiants pour continuer.' ?>
                </p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert-error">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?= htmlspecialchars((string) $error) ?></span>
                </div>
            <?php endif; ?>

            <form action="/login" method="POST" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div class="form-group">
                    <label class="form-label" for="username"><?= __('username') ?></label>
                    <div class="input-wrapper">
                        <i class="bi bi-person prefix-icon"></i>
                        <input type="text" id="username" name="username" class="input-control"
                            placeholder="<?= __('lang') === 'en' ? 'Enter your login' : 'Votre identifiant' ?>" required
                            autofocus autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password"><?= __('password') ?></label>
                    <div class="input-wrapper">
                        <i class="bi bi-lock prefix-icon"></i>
                        <input type="password" id="password" name="password" class="input-control"
                            placeholder="<?= __('lang') === 'en' ? 'Your password' : 'Votre mot de passe' ?>" required
                            autocomplete="current-password">
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="helper-links">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="remember" style="accent-color: var(--brand-primary);">
                        <label for="remember"
                            style="color: var(--text-secondary);"><?= $isEn ? 'Remember me' : 'Se souvenir de moi' ?></label>
                    </div>
                    <?php
                        // Lien direct vers WhatsApp pour le support (Réinitialisation)
                        // Note : J'ai ajouté l'indicatif 237 par défaut (Cameroun) vu le format du numéro.
                        $waPhone = "237679164801"; 
                        $waText = $isEn 
                            ? "Hello, I forgot my NotesMaster password. Can you help me reset it?" 
                            : "Bonjour, j'ai oublié mon mot de passe NotesMaster. Pouvez-vous m'aider à le réinitialiser ?";
                        $waLink = "https://wa.me/" . $waPhone . "?text=" . urlencode($waText);
                    ?>
                    <!-- [Junior] UX : Redirection vers WhatsApp avec message pré-rempli pour un support réactif -->
                    <a href="<?= $waLink ?>" target="_blank" rel="noopener noreferrer" style="display: flex; align-items: center; gap: 5px;">
                        <i class="bi bi-whatsapp" style="color: #25D366;"></i>
                        <?= $isEn ? 'Forgot password?' : 'Mot de passe oublié ?' ?>
                    </a>
                </div>

                <button type="submit" class="btn-submit">
                    <span><?= __('login') ?></span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <?php if (isset($brandSettings['allow_teacher_registration']) && $brandSettings['allow_teacher_registration'] == '1'): ?>
                <div style="margin-top: 1.5rem; text-align: center; font-size: 0.9rem; padding-top: 1.5rem; border-top: 1px solid rgba(0,0,0,0.05);">
                    <span style="color: var(--text-secondary);"><?= $isEn ? 'Are you a teacher?' : 'Vous êtes enseignant ?' ?></span>
                    <a href="/register-teacher" style="color: var(--brand-primary); font-weight: 600; text-decoration: none; margin-left: 5px; transition: color 0.2s;" onmouseover="this.style.color='var(--text-main)'" onmouseout="this.style.color='var(--brand-primary)'">
                        <?= $isEn ? 'Create your account' : 'Créez votre compte' ?>
                    </a>
                </div>
            <?php endif; ?>

            <div class="portal-footer">
                <?= __('lang') === 'en' ? 'Need help?' : 'Besoin d\'aide ?' ?> <a
                    href="mailto:evaricekuete2@gmail.com">Contactez le support</a>
            </div>
        </section>
    </main>

    <script>
        // Password visibility toggle
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });

        // Form submission loader
        const loginForm = document.getElementById('loginForm');
        const loader = document.getElementById('global-loader');

        loginForm.addEventListener('submit', function () {
            loader.style.display = 'flex';
        });

        // Brand Color RGB Helper (for translucent effects)
        document.documentElement.style.setProperty('--brand-primary-rgb',
            hexToRgb('<?= htmlspecialchars((string) $brandSettings['theme_login_button']) ?>').join(', ')
        );

        function hexToRgb(hex) {
            const r = parseInt(hex.slice(1, 3), 16);
            const g = parseInt(hex.slice(3, 5), 16);
            const b = parseInt(hex.slice(5, 7), 16);
            return [r, g, b];
        }
    </script>
</body>

</html>