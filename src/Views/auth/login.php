<?php
/**
 * Vue : Page de Connexion (Login) - Refonte Premium & UI/UX Microsoft / EduHub
 * 
 * Toutes les variables transmises par AuthController sont conservées et utilisées :
 * $brandSettings, $logoData, $csrfToken, $error (si présent), $isEn
 */
$isEn = __('lang') === 'en';
?>
<!DOCTYPE html>
<html lang="<?= $isEn ? 'en' : 'fr' ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('login_title_seo') ?> | <?= htmlspecialchars((string) ($brandSettings['school_code'] ?? 'NotesMaster')) ?></title>
    <meta name="description" content="<?= __('meta_description_default') ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        :root {
            --brand-primary: <?= htmlspecialchars((string) ($brandSettings['theme_login_button'] ?? '#7c3aed')) ?>;
            --brand-primary-hover: color-mix(in srgb, var(--brand-primary) 85%, #000000);
            --brand-bg-start: <?= htmlspecialchars((string) ($brandSettings['theme_login_bg_start'] ?? '#35155D')) ?>;
            --brand-bg-mid: <?= htmlspecialchars((string) ($brandSettings['theme_login_bg_mid'] ?? '#512B81')) ?>;
            --brand-bg-end: <?= htmlspecialchars((string) ($brandSettings['theme_login_bg_end'] ?? '#1B0C38')) ?>;
            --brand-accent: <?= htmlspecialchars((string) ($brandSettings['theme_login_bubble'] ?? '#f43f5e')) ?>;
            
            --text-main: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --border-color: #e2e8f0;
            --input-bg: #f8fafc;
            --card-radius: 28px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #0f172a;
            height: 100vh;
            width: 100vw;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            overflow: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Arrière-plan global subtil */
        .page-background-glow {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background: 
                radial-gradient(circle at 15% 20%, rgba(124, 58, 237, 0.25) 0%, transparent 45%),
                radial-gradient(circle at 85% 80%, rgba(236, 72, 153, 0.2) 0%, transparent 45%),
                radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.15) 0%, transparent 60%);
        }

        /* --- Global Loader --- */
        #global-loader {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(12px);
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #ffffff;
        }

        .loader-spinner {
            width: 48px;
            height: 48px;
            border: 3.5px solid rgba(255, 255, 255, 0.2);
            border-top-color: #a855f7;
            border-right-color: #06b6d4;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* --- Main Login Card Container (Plein Écran / Full Surface) --- */
        .login-card {
            position: relative;
            z-index: 10;
            width: 100vw;
            height: 100vh;
            max-width: 100vw;
            min-height: 100vh;
            background: #ffffff;
            border-radius: 0;
            box-shadow: none;
            display: flex;
            overflow: hidden;
            animation: cardEntrance 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes cardEntrance {
            from {
                opacity: 0;
                transform: translateY(24px) scale(0.97);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* ==========================================================================
           LEFT SECTION: Interactive Animated 3D Bubbles Showcase
           ========================================================================== */
        .showcase-section {
            flex: 1.15;
            background: linear-gradient(145deg, var(--brand-bg-start) 0%, var(--brand-bg-mid) 50%, var(--brand-bg-end) 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem;
            overflow: hidden;
            color: #ffffff;
        }

        /* Canvas interactif pour les bulles 3D */
        #bubblesCanvas {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        /* Calque de superposition pour donner du relief et de la profondeur */
        .showcase-overlay {
            position: absolute;
            inset: 0;
            z-index: 2;
            background: 
                radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 20% 90%, rgba(0, 0, 0, 0.35) 0%, transparent 60%);
            pointer-events: none;
        }

        /* Contenu du bas de la section visuelle (Bienvenue) */
        .showcase-welcome {
            position: relative;
            z-index: 5;
            margin-top: auto;
            max-width: 420px;
        }

        .welcome-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 54px;
            height: 54px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 16px;
            font-size: 1.6rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .welcome-title {
            font-family: 'Outfit', sans-serif;
            font-size: 2.25rem;
            font-weight: 800;
            line-height: 1.2;
            color: #ffffff;
            margin-bottom: 0.75rem;
            letter-spacing: -0.02em;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .welcome-subtitle {
            font-size: 0.975rem;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.82);
            font-weight: 400;
            margin-bottom: 1.5rem;
        }

        .welcome-accent-bar {
            width: 70px;
            height: 5px;
            border-radius: 10px;
            background: linear-gradient(90deg, #ec4899 0%, #06b6d4 100%);
            box-shadow: 0 0 12px rgba(236, 72, 153, 0.6);
        }

        /* ==========================================================================
           RIGHT SECTION: Clean & Modern Login Portal
           ========================================================================== */
        .portal-section {
            flex: 1;
            background: #ffffff;
            padding: 3.5rem 3.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            z-index: 5;
            overflow-y: auto;
            max-height: 100vh;
        }

        .portal-brand {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2.25rem;
        }

        .brand-logo-container {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            flex-shrink: 0;
        }

        .brand-logo-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .brand-logo-fallback {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--brand-primary);
        }

        .brand-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.45rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
            line-height: 1.1;
        }

        .brand-subtitle {
            font-size: 0.775rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 2px;
        }

        .portal-header {
            margin-bottom: 1.75rem;
        }

        .portal-header h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.025em;
            margin-bottom: 0.35rem;
        }

        .portal-header p {
            font-size: 0.9rem;
            color: #64748b;
            line-height: 1.5;
        }

        /* --- Alert Error --- */
        .alert-error {
            background: #fef2f2;
            border: 1.5px solid #fecaca;
            border-radius: 14px;
            padding: 0.875rem 1.125rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #991b1b;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
            animation: alertShake 0.4s cubic-bezier(.36, .07, .19, .97) both;
        }

        .alert-error i {
            font-size: 1.2rem;
            color: #dc2626;
            flex-shrink: 0;
        }

        @keyframes alertShake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }

        /* --- Form Elements --- */
        .form-group {
            margin-bottom: 1.35rem;
        }

        .form-label {
            display: block;
            font-size: 0.825rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.45rem;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-left {
            position: absolute;
            left: 1.125rem;
            color: #94a3b8;
            font-size: 1.15rem;
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .form-input {
            width: 100%;
            height: 48px;
            padding: 0.75rem 1rem 0.75rem 2.85rem;
            background: var(--input-bg);
            border: 1.5px solid var(--border-color);
            border-radius: 12px;
            font-family: inherit;
            font-size: 0.9375rem;
            color: #0f172a;
            transition: all 0.2s ease;
        }

        .form-input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        .form-input:hover {
            border-color: #cbd5e1;
            background: #ffffff;
        }

        .form-input:focus {
            outline: none;
            background: #ffffff;
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3.5px rgba(124, 58, 237, 0.15);
        }

        .input-wrapper:focus-within .input-icon-left {
            color: var(--brand-primary);
        }

        /* Toggle Mot de Passe */
        .password-toggle-btn {
            position: absolute;
            right: 0.75rem;
            background: transparent;
            border: none;
            color: #64748b;
            padding: 6px;
            font-size: 1.1rem;
            cursor: pointer;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .password-toggle-btn:hover {
            color: #0f172a;
            background: #e2e8f0;
        }

        /* Actions et Liens d'aide */
        .helper-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 0.25rem;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
        }

        .remember-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            user-select: none;
            color: #475569;
            font-weight: 500;
        }

        .remember-checkbox input[type="checkbox"] {
            width: 17px;
            height: 17px;
            accent-color: var(--brand-primary);
            border-radius: 4px;
            cursor: pointer;
        }

        .forgot-link {
            color: var(--brand-primary);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: color 0.2s ease;
        }

        .forgot-link:hover {
            color: var(--brand-primary-hover);
            text-decoration: underline;
        }

        /* Bouton Principal de Soumission */
        .btn-submit {
            width: 100%;
            height: 52px;
            background: linear-gradient(135deg, var(--brand-primary) 0%, color-mix(in srgb, var(--brand-primary) 80%, #000000) 100%);
            color: #ffffff;
            border: none;
            border-radius: 14px;
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            box-shadow: 0 8px 20px -4px rgba(124, 58, 237, 0.4);
            transition: all 0.25s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px -4px rgba(124, 58, 237, 0.5);
        }

        .btn-submit:active {
            transform: translateY(0);
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
        }

        .btn-submit i {
            font-size: 1.15rem;
            transition: transform 0.25s ease;
        }

        .btn-submit:hover i {
            transform: translateX(4px);
        }

        /* Inscription Enseignant */
        .teacher-reg-container {
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px dashed #e2e8f0;
            text-align: center;
            font-size: 0.875rem;
            color: #64748b;
        }

        .teacher-reg-link {
            color: var(--brand-primary);
            font-weight: 700;
            text-decoration: none;
            margin-left: 0.35rem;
            transition: color 0.2s ease;
        }

        .teacher-reg-link:hover {
            text-decoration: underline;
            color: var(--brand-primary-hover);
        }

        /* Footer Bas de Formulaire */
        .portal-footer {
            margin-top: auto;
            padding-top: 1.5rem;
            text-align: center;
            font-size: 0.8125rem;
            color: #94a3b8;
        }

        .portal-footer a {
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .portal-footer a:hover {
            color: var(--brand-primary);
            text-decoration: underline;
        }

        /* ==========================================================================
           RESPONSIVE DESIGN (Tablette & Mobile)
           ========================================================================== */
        @media (max-width: 960px) {
            body {
                padding: 1rem 0.5rem;
                background: #0f172a;
            }

            .login-card {
                flex-direction: column;
                max-width: 480px;
                min-height: auto;
                border-radius: 20px;
                margin: auto;
            }

            .showcase-section {
                flex: none;
                height: 220px;
                padding: 1.75rem 1.5rem;
            }

            .welcome-badge {
                width: 42px;
                height: 42px;
                font-size: 1.25rem;
                margin-bottom: 0.5rem;
            }

            .welcome-title {
                font-size: 1.5rem;
                margin-bottom: 0.25rem;
            }

            .welcome-subtitle {
                font-size: 0.85rem;
                margin-bottom: 0.75rem;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .portal-section {
                padding: 2rem 1.5rem;
            }

            .portal-brand {
                margin-bottom: 1.5rem;
            }

            .brand-logo-container {
                width: 46px;
                height: 46px;
            }

            .brand-title {
                font-size: 1.25rem;
            }

            .portal-header h1 {
                font-size: 1.45rem;
            }
        }

        /* Accessibilité : Réduction de mouvement */
        @media (prefers-reduced-motion: reduce) {
            .login-card, .alert-error, .btn-submit, .btn-submit i {
                animation: none !important;
                transition: none !important;
            }
        }
    </style>
</head>

<body>
    <!-- Fond Flou Lumineux -->
    <div class="page-background-glow"></div>

    <!-- Indicator Loader -->
    <div id="global-loader">
        <div class="loader-spinner"></div>
        <p style="margin-top: 16px; font-weight: 600; font-size: 0.95rem; letter-spacing: 0.02em;">
            <?= $isEn ? 'Verifying credentials...' : 'Connexion en cours...' ?>
        </p>
    </div>

    <!-- Carte Principale Split Screen -->
    <main class="login-card">
        
        <!-- SECTION GAUCHE : Animation 3D des 5 Familles de Bulles Flottantes -->
        <section class="showcase-section">
            <canvas id="bubblesCanvas"></canvas>
            <div class="showcase-overlay"></div>

            <div class="showcase-welcome">
                <div class="welcome-badge">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <h2 class="welcome-title"><?= $isEn ? 'Welcome ! 👋' : 'Bienvenue ! 👋' ?></h2>
                <p class="welcome-subtitle">
                    <?= $isEn ? 'Log in to access your school dashboard and manage grades smoothly.' : 'Connectez-vous pour accéder à votre espace de gestion scolaire.' ?>
                </p>
                <div class="welcome-accent-bar"></div>
            </div>
        </section>

        <!-- SECTION DROITE : Portail de Connexion Épuré -->
        <section class="portal-section">
            
            <!-- Branding / Logo d'Établissement -->
            <div class="portal-brand">
                <div class="brand-logo-container">
                    <?php if ($logoData['has_logo'] && !empty($logoData['base64'])): ?>
                        <img src="<?= htmlspecialchars($logoData['base64']) ?>" alt="Logo">
                    <?php elseif ($logoData['has_logo'] && !empty($logoData['url'])): ?>
                        <img src="<?= htmlspecialchars($logoData['url']) ?>" alt="Logo">
                    <?php else: ?>
                        <div class="brand-logo-fallback"><?= htmlspecialchars($logoData['fallback_letter'] ?? 'N') ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="brand-title"><?= htmlspecialchars((string) ($brandSettings['school_code'] ?? 'NotesMaster')) ?></div>
                    <div class="brand-subtitle"><?= $isEn ? 'Smart Education Platform' : 'Gestion Scolaire Inteligente' ?></div>
                </div>
            </div>

            <!-- Titre du formulaire -->
            <div class="portal-header">
                <h1><?= $isEn ? 'Sign In' : 'Connexion' ?></h1>
                <p><?= $isEn ? 'Please enter your details to sign in' : 'Veuillez saisir vos identifiants pour vous connecter' ?></p>
            </div>

            <!-- Message d'erreur éventuel -->
            <?php if (isset($error) && !empty($error)): ?>
                <div class="alert-error" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?= htmlspecialchars((string) $error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Formulaire de Connexion -->
            <form action="/login" method="POST" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($csrfToken ?? '')) ?>">

                <!-- Champ Nom d'utilisateur / Identifiant -->
                <div class="form-group">
                    <label class="form-label" for="username"><?= $isEn ? 'Username' : 'Nom d\'utilisateur' ?></label>
                    <div class="input-wrapper">
                        <i class="bi bi-person input-icon-left"></i>
                        <input type="text" id="username" name="username" class="form-input" 
                               placeholder="<?= $isEn ? 'Enter your username' : 'Entrez votre nom d\'utilisateur' ?>" 
                               required autofocus autocomplete="username">
                    </div>
                </div>

                <!-- Champ Mot de passe -->
                <div class="form-group">
                    <label class="form-label" for="password"><?= $isEn ? 'Password' : 'Mot de passe' ?></label>
                    <div class="input-wrapper">
                        <i class="bi bi-lock input-icon-left"></i>
                        <input type="password" id="password" name="password" class="form-input" 
                               placeholder="<?= $isEn ? 'Enter your password' : 'Entrez votre mot de passe' ?>" 
                               required autocomplete="current-password">
                        <button type="button" class="password-toggle-btn" id="togglePassword" aria-label="Afficher le mot de passe">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Se Souvenir de moi & Mot de passe oublié (WhatsApp) -->
                <div class="helper-row">
                    <label class="remember-checkbox">
                        <input type="checkbox" id="remember" name="remember" value="1">
                        <span><?= $isEn ? 'Remember me' : 'Se souvenir de moi' ?></span>
                    </label>

                    <?php
                        $waPhone = "237679164801"; 
                        $waText = $isEn 
                            ? "Hello, I forgot my NotesMaster password. Can you help me reset it?" 
                            : "Bonjour, j'ai oublié mon mot de passe NotesMaster. Pouvez-vous m'aider à le réinitialiser ?";
                        $waLink = "https://wa.me/" . $waPhone . "?text=" . urlencode($waText);
                    ?>
                    <a href="<?= $waLink ?>" target="_blank" rel="noopener noreferrer" class="forgot-link">
                        <?= $isEn ? 'Forgot password?' : 'Mot de passe oublié ?' ?>
                    </a>
                </div>

                <!-- Bouton Se Connecter -->
                <button type="submit" class="btn-submit">
                    <span><?= $isEn ? 'Sign In' : 'Se connecter' ?></span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <!-- Option Inscription Enseignant (si activée) -->
            <?php if (isset($brandSettings['allow_teacher_registration']) && $brandSettings['allow_teacher_registration'] == '1'): ?>
                <div class="teacher-reg-container">
                    <span><?= $isEn ? 'Are you a teacher?' : 'Vous êtes enseignant ?' ?></span>
                    <a href="/register-teacher" class="teacher-reg-link">
                        <?= $isEn ? 'Create an account' : 'Créez votre compte' ?>
                    </a>
                </div>
            <?php endif; ?>

            <!-- Footer & Contact Support -->
            <div class="portal-footer">
                <div style="margin-bottom: 4px;">
                    <?= $isEn ? 'Need help?' : 'Besoin d\'aide ?' ?> 
                    <a href="mailto:evaricekuete2@gmail.com"><?= $isEn ? 'Contact support' : 'Contactez le support' ?></a>
                </div>
                <div>&copy; <?= date('Y') ?> <?= htmlspecialchars((string) ($brandSettings['school_name'] ?? 'NotesMaster')) ?>. <?= $isEn ? 'All rights reserved.' : 'Tous droits réservés.' ?></div>
            </div>
        </section>

    </main>

    <!-- SCRIPT DE L'ANIMATION DE BULLES 3D (5 FAMILLES DE COULEURS AVEC PHYSIQUE ET REBOND) -->
    <script>
        (function () {
            'use strict';

            // Affichage / Masquage du mot de passe
            const togglePasswordBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');

            if (togglePasswordBtn && passwordInput) {
                togglePasswordBtn.addEventListener('click', function () {
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('bi-eye', !isPassword);
                        icon.classList.toggle('bi-eye-slash', isPassword);
                    }
                });
            }

            // Loader lors de la soumission du formulaire
            const loginForm = document.getElementById('loginForm');
            const globalLoader = document.getElementById('global-loader');

            if (loginForm && globalLoader) {
                loginForm.addEventListener('submit', function () {
                    globalLoader.style.display = 'flex';
                });
            }

            // =========================================================================
            // MOTEUR D'ANIMATION DE BULLES 3D INTERACTIVES (5 FAMILLES DE COULEURS)
            // =========================================================================
            const canvas = document.getElementById('bubblesCanvas');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            let animationFrameId = null;
            let width = 0;
            let height = 0;

            // Définition des 5 familles de couleurs distinctes avec dégradés 3D
            const colorPalettes = [
                // 1. Violet / Purple
                { inner: '#d8b4fe', outer: '#7c3aed', shadow: 'rgba(168, 85, 247, 0.4)' },
                // 2. Cyan / Blue
                { inner: '#67e8f9', outer: '#0284c7', shadow: 'rgba(6, 182, 212, 0.4)' },
                // 3. Pink / Rose
                { inner: '#fbcfe8', outer: '#e11d48', shadow: 'rgba(244, 63, 94, 0.4)' },
                // 4. Orange / Coral
                { inner: '#ffedd5', outer: '#ea580c', shadow: 'rgba(249, 115, 22, 0.4)' },
                // 5. Yellow / Gold
                { inner: '#fef08a', outer: '#ca8a04', shadow: 'rgba(234, 179, 8, 0.4)' }
            ];

            class Bubble {
                constructor() {
                    this.reset(true);
                }

                reset(initial = false) {
                    this.radius = Math.random() * 32 + 10; // Tailles variées de 10px à 42px
                    this.x = Math.random() * (width - this.radius * 2) + this.radius;
                    this.y = initial ? Math.random() * height : height + this.radius + Math.random() * 50;
                    
                    // Vitesse fluide en X et Y
                    this.vx = (Math.random() - 0.5) * 0.8;
                    this.vy = -(Math.random() * 0.7 + 0.3);
                    
                    // Transparence et pulsation
                    this.alpha = Math.random() * 0.45 + 0.45;
                    this.pulseSpeed = Math.random() * 0.02 + 0.005;
                    this.pulseAngle = Math.random() * Math.PI * 2;

                    // Choix de l'une des 5 familles de couleurs
                    this.palette = colorPalettes[Math.floor(Math.random() * colorPalettes.length)];
                }

                update() {
                    this.x += this.vx;
                    this.y += this.vy;

                    // Rebond fluide sur les bords horizontaux
                    if (this.x - this.radius <= 0 || this.x + this.radius >= width) {
                        this.vx *= -1;
                    }

                    // Pulsation subtile de taille
                    this.pulseAngle += this.pulseSpeed;
                    this.currentRadius = this.radius + Math.sin(this.pulseAngle) * 2;

                    // Réinitialisation lorsqu'elle s'échappe par le haut
                    if (this.y + this.radius < -20) {
                        this.reset(false);
                    }
                }

                draw() {
                    ctx.save();
                    ctx.globalAlpha = this.alpha;

                    // Ombre lumineuse pour l'effet Neon/Glow
                    ctx.shadowColor = this.palette.shadow;
                    ctx.shadowBlur = 12;

                    // Dégradé radial pour créer le volume de sphère 3D
                    const gradX = this.x - this.currentRadius * 0.3;
                    const gradY = this.y - this.currentRadius * 0.3;
                    const gradient = ctx.createRadialGradient(
                        gradX, gradY, this.currentRadius * 0.1,
                        this.x, this.y, this.currentRadius
                    );

                    gradient.addColorStop(0, '#ffffff'); // Point lumineux de reflet en haut à gauche
                    gradient.addColorStop(0.2, this.palette.inner);
                    gradient.addColorStop(0.85, this.palette.outer);
                    gradient.addColorStop(1, this.palette.outer);

                    ctx.beginPath();
                    ctx.arc(this.x, this.y, Math.max(2, this.currentRadius), 0, Math.PI * 2);
                    ctx.fillStyle = gradient;
                    ctx.fill();

                    ctx.restore();
                }
            }

            const bubbles = [];
            const BUBBLE_COUNT = 24; // Nombre idéal pour une fluidité 60fps sans surcharge

            function resize() {
                const parent = canvas.parentElement;
                if (!parent) return;
                width = parent.clientWidth;
                height = parent.clientHeight;
                canvas.width = width;
                canvas.height = height;

                if (bubbles.length === 0) {
                    for (let i = 0; i < BUBBLE_COUNT; i++) {
                        bubbles.push(new Bubble());
                    }
                }
            }

            function animate() {
                ctx.clearRect(0, 0, width, height);

                // Lignes de connexion douces entre bulles proches
                for (let i = 0; i < bubbles.length; i++) {
                    for (let j = i + 1; j < bubbles.length; j++) {
                        const dx = bubbles[i].x - bubbles[j].x;
                        const dy = bubbles[i].y - bubbles[j].y;
                        const dist = Math.sqrt(dx * dx + dy * dy);

                        if (dist < 110) {
                            ctx.save();
                            ctx.globalAlpha = (1 - dist / 110) * 0.15;
                            ctx.strokeStyle = '#ffffff';
                            ctx.lineWidth = 1;
                            ctx.beginPath();
                            ctx.moveTo(bubbles[i].x, bubbles[i].y);
                            ctx.lineTo(bubbles[j].x, bubbles[j].y);
                            ctx.stroke();
                            ctx.restore();
                        }
                    }
                }

                // Mise à jour et dessin des bulles
                bubbles.forEach(bubble => {
                    bubble.update();
                    bubble.draw();
                });

                animationFrameId = requestAnimationFrame(animate);
            }

            // Détection de la préférence 'prefers-reduced-motion'
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

            function initAnimation() {
                resize();
                if (!prefersReducedMotion.matches) {
                    if (animationFrameId) cancelAnimationFrame(animationFrameId);
                    animate();
                } else {
                    // Dessiner une seule frame statique si l'utilisateur demande moins de mouvements
                    bubbles.forEach(b => b.draw());
                }
            }

            window.addEventListener('resize', resize);
            initAnimation();

        })();
    </script>
</body>

</html>