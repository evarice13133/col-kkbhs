<?php
$title = "403 - " . (__('action_forbidden') ?? 'Accès Interdit');
$app_lang = \App\Core\Session::get('app_lang', 'fr');
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
    </script>
    <title><?= $title ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --primary-color: #3b82f6;
            --primary-rgb: 59, 130, 246;
            --danger-color: #ef4444;
            --danger-rgb: 239, 68, 68;
            --border-color: #e2e8f0;
        }

        [data-theme="dark"] {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #334155;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            transition: background-color 0.3s, color 0.3s;
        }

        .forbidden-container {
            max-width: 500px;
            width: 90%;
            text-align: center;
        }

        .forbidden-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 28px;
            padding: 3rem 2rem;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.05);
            transition: background-color 0.3s, border-color 0.3s, transform 0.3s;
        }

        .forbidden-card:hover {
            transform: translateY(-5px);
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: rgba(var(--danger-rgb), 0.1);
            color: var(--danger-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 2.5rem;
            animation: pulse-ring 2s infinite;
        }

        h1 {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 2rem;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }

        .error-code {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--danger-color);
            margin-bottom: 1.5rem;
        }

        p {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .btn-home {
            background: linear-gradient(135deg, var(--primary-color), #4f46e5);
            border: none;
            color: #ffffff;
            font-weight: 600;
            border-radius: 100px;
            padding: 0.8rem 2rem;
            box-shadow: 0 8px 20px -6px rgba(var(--primary-rgb), 0.5);
            transition: all 0.2s ease-in-out;
            text-decoration: none;
            display: inline-block;
        }

        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -6px rgba(var(--primary-rgb), 0.7);
            color: #ffffff;
        }

        @keyframes pulse-ring {
            0% {
                box-shadow: 0 0 0 0 rgba(var(--danger-rgb), 0.4);
            }
            70% {
                box-shadow: 0 0 0 15px rgba(var(--danger-rgb), 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(var(--danger-rgb), 0);
            }
        }
    </style>
</head>
<body>
    <div class="forbidden-container">
        <div class="forbidden-card">
            <div class="icon-circle">
                <i class="bi bi-shield-slash"></i>
            </div>
            <div class="error-code">Erreur 403 / Forbidden</div>
            <h1><?= htmlspecialchars(__('action_forbidden') ?? 'Accès Interdit') ?></h1>
            <p>
                <?= htmlspecialchars(__('unauthorized_access_description') ?? 'Vous ne possédez pas les permissions nécessaires pour accéder à cette section ou effectuer cette action. Veuillez contacter votre administrateur si vous pensez qu\'il s\'agit d\'une erreur.') ?>
            </p>
            <a href="/" class="btn-home">
                <i class="bi bi-arrow-left me-2"></i><?= htmlspecialchars(__('back_to_dashboard') ?? 'Retour au Tableau de Bord') ?>
            </a>
        </div>
    </div>
</body>
</html>
