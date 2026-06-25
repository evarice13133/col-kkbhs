<?php
/**
 * Point d'entrée principal - Passerelle avec Splash Screen
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';

\App\Core\Session::start();

// Bootstrapping des helpers globaux nécessaires au splash screen
if (!function_exists('__')) {
    function __($key, $replacements = [], $count = null) {
        if ($key === null) return '';
        return \App\Core\Translator::translate((string)$key, $replacements, $count);
    }
}
\App\Core\Locale::bootstrapFromRequest();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// On n'affiche le splash QUE sur la racine "/" et si pas encore vu dans cette session
if ($path === '/' && !\App\Core\Session::has('splash_done') && !isset($_GET['skip'])) {
    
    // Récupération des données pour le splash
    $db = \App\Core\Database::getInstance()->getConnection();
    $settingsStore = new \App\Services\SettingsStore($db);
    $logoManager = \App\Core\LogoManager::getInstance($db);
    
    $school_name = $settingsStore->get('school_name', 'NotesMaster');
    $school_code = $settingsStore->get('school_code', 'IMT');
    $logo_base64 = $logoManager->hasLogo() ? $logoManager->getLogoBase64() : '';
    $fallback_letter = $logoManager->getFallbackLetter();
    
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>NoteMaster - <?= htmlspecialchars($school_name) ?></title>
        <meta name="description" content="<?= __('meta_description_default') ?>">
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

            .school-code-splash {
                font-size: 3.5rem;
                font-weight: 900;
                background: linear-gradient(to right, #22c55e, #ef4444, #eab308, #22c55e);
                background-size: 300% auto;
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                animation: colorSweep 3s linear infinite;
                z-index: 2;
                letter-spacing: -2px;
            }

            @keyframes colorSweep {
                0% { background-position: 0% center; }
                100% { background-position: 300% center; }
            }

            .school-name {
                margin-top: 30px;
                font-size: 1.2rem;
                font-weight: 700;
                color: var(--text-color);
                text-transform: uppercase;
                letter-spacing: 4px;
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
                <div class="school-code-splash"><?= htmlspecialchars((string) $school_code) ?></div>
            </div>
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
