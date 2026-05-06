<?php
/**
 * Point d'entrée principal - Passerelle avec Splash Screen
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';

\App\Core\Session::start();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// On n'affiche le splash QUE sur la racine "/" et si pas encore vu dans cette session
if ($path === '/' && !\App\Core\Session::has('splash_done') && !isset($_GET['skip'])) {
    
    // Récupération des données pour le splash
    $db = \App\Core\Database::getInstance()->getConnection();
    $settingsStore = new \App\Services\SettingsStore($db);
    $logoManager = \App\Core\LogoManager::getInstance($db);
    
    $school_name = $settingsStore->get('school_name', 'NotesMaster');
    $logo_base64 = $logoManager->hasLogo() ? $logoManager->getLogoBase64() : '';
    $fallback_letter = $logoManager->getFallbackLetter();
    
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Chargement - <?= htmlspecialchars($school_name) ?></title>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;800&display=swap" rel="stylesheet">
        <style>
            :root {
                --primary-color: #3b82f6;
                --bg-color: #ffffff;
                --text-color: #1e293b;
            }

            /* Dark mode support */
            @media (prefers-color-scheme: dark) {
                :root {
                    --bg-color: #0f172a;
                    --text-color: #f8fafc;
                }
            }

            body, html {
                margin: 0;
                padding: 0;
                width: 100%;
                height: 100%;
                overflow: hidden;
                background-color: var(--bg-color);
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Outfit', sans-serif;
            }

            .splash-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-align: center;
            }

            .loader-wrapper {
                position: relative;
                width: 160px;
                height: 160px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .spinner {
                position: absolute;
                width: 140px;
                height: 140px;
                border: 5px solid rgba(59, 130, 246, 0.1);
                border-top: 5px solid var(--primary-color);
                border-radius: 50%;
                animation: spin 1.5s linear infinite;
            }

            .logo-img {
                width: 100px;
                height: 100px;
                object-fit: contain;
                z-index: 2;
                animation: pulse 2s ease-in-out infinite;
            }

            .logo-fallback {
                width: 100px;
                height: 100px;
                background: var(--primary-color);
                color: white;
                border-radius: 25px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 3rem;
                font-weight: 800;
                z-index: 2;
                box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
            }

            .school-name {
                margin-top: 30px;
                font-size: 1.5rem;
                font-weight: 800;
                color: var(--text-color);
                text-transform: uppercase;
                letter-spacing: 2px;
                opacity: 0;
                animation: fadeIn 1s ease-out forwards 0.5s;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.08); }
            }

            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }

            /* Progress bar bottom */
            .progress-bar {
                position: fixed;
                bottom: 0;
                left: 0;
                height: 4px;
                background: var(--primary-color);
                width: 0%;
                transition: width 5s linear;
                box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
            }
        </style>
    </head>
    <body>
        <div class="splash-container">
            <div class="loader-wrapper">
                <div class="spinner"></div>
                <?php if ($logo_base64): ?>
                    <img src="<?= $logo_base64 ?>" alt="Logo" class="logo-img">
                <?php else: ?>
                    <div class="logo-fallback"><?= $fallback_letter ?></div>
                <?php endif; ?>
            </div>
            <div class="school-name"><?= htmlspecialchars($school_name) ?></div>
        </div>
        <div class="progress-bar" id="progressBar"></div>

        <script>
            window.onload = function() {
                const bar = document.getElementById('progressBar');
                // Petit délai pour déclencher la transition CSS
                setTimeout(() => {
                    bar.style.width = '100%';
                }, 50);

                // Redirection après 5 secondes
                setTimeout(() => {
                    window.location.href = '/?skip=1';
                }, 5000);
            };
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Si on a déjà vu le splash ou qu'on n'est pas sur la racine
if (isset($_GET['skip'])) {
    \App\Core\Session::set('splash_done', true);
}

require __DIR__ . '/public/index.php';
