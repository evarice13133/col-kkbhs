<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guide Enseignant - NotesMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --secondary: #8b5cf6;
            --accent: #06b6d4;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg: #f8fafc;
            --bg-card: #ffffff;
            --text: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text);
            line-height: 1.7;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: var(--bg-card);
            border-radius: 24px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 50px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .logo {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .title {
            font-family: 'Outfit', sans-serif;
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .subtitle {
            font-size: 18px;
            opacity: 0.95;
            font-weight: 500;
        }

        .content {
            padding: 50px 40px;
        }

        .section {
            margin-bottom: 50px;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--border);
        }

        .section-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            box-shadow: var(--shadow);
        }

        .section-title {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: var(--text);
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-top: 24px;
        }

        .feature-card {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow);
            border-color: var(--primary-light);
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            background: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            margin-bottom: 16px;
        }

        .feature-title {
            font-weight: 700;
            font-size: 18px;
            margin-bottom: 8px;
            color: var(--text);
        }

        .feature-desc {
            color: var(--text-muted);
            font-size: 15px;
            line-height: 1.6;
        }

        .step-list {
            margin-top: 24px;
        }

        .step-item {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
            padding: 20px;
            background: var(--bg);
            border-radius: 12px;
            border-left: 4px solid var(--primary);
        }

        .step-number {
            width: 36px;
            height: 36px;
            background: var(--primary);
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
            flex-shrink: 0;
        }

        .step-content h4 {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 6px;
            color: var(--text);
        }

        .step-content p {
            color: var(--text-muted);
            font-size: 15px;
            margin: 0;
        }

        .tip-box {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border: 1px solid #10b981;
            border-radius: 16px;
            padding: 24px;
            margin-top: 24px;
            display: flex;
            gap: 16px;
        }

        .tip-icon {
            font-size: 28px;
            color: var(--success);
        }

        .tip-content h4 {
            font-weight: 700;
            color: var(--success);
            margin-bottom: 8px;
        }

        .tip-content p {
            color: #065f46;
            font-size: 15px;
            margin: 0;
        }

        .warning-box {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 1px solid var(--warning);
            border-radius: 16px;
            padding: 24px;
            margin-top: 24px;
            display: flex;
            gap: 16px;
        }

        .warning-icon {
            font-size: 28px;
            color: var(--warning);
        }

        .warning-content h4 {
            font-weight: 700;
            color: #92400e;
            margin-bottom: 8px;
        }

        .warning-content p {
            color: #78350f;
            font-size: 15px;
            margin: 0;
        }

        .footer {
            background: var(--bg);
            padding: 30px 40px;
            text-align: center;
            border-top: 1px solid var(--border);
        }

        .footer-text {
            color: var(--text-muted);
            font-size: 14px;
        }

        .footer-text strong {
            color: var(--primary);
        }

        @media (max-width: 768px) {
            body {
                padding: 20px 10px;
            }

            .header {
                padding: 40px 24px;
            }

            .title {
                font-size: 28px;
            }

            .content {
                padding: 30px 24px;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }

            .section-header {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <div class="logo">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <h1 class="title">Guide Enseignant</h1>
                <p class="subtitle">Gestion complète de vos classes, notes et évaluations</p>
            </div>
        </div>

        <div class="content">
            <!-- Section 1: Saisie des Notes -->
            <div class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <h2 class="section-title">Saisie des Notes</h2>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-keyboard"></i>
                    </div>
                    <h3 class="feature-title">Accès à la Saisie</h3>
                    <p class="feature-desc">Cliquez sur le bouton <strong>"Saisir les notes"</strong> dans votre dashboard pour accéder à toutes vos classes et matières affectées. Saisissez les notes élève par élève pour chaque évaluation active.</p>
                </div>

                <div class="step-list">
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>Accéder à la saisie</h4>
                            <p>Cliquez sur le bouton <strong>"Saisir les notes"</strong> dans votre dashboard enseignant.</p>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>Sélectionner la classe et la matière</h4>
                            <p>Choisissez la classe et la matière pour lesquelles vous souhaitez saisir les notes.</p>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>Saisir les notes</h4>
                            <p>Entrez les notes pour chaque élève dans les champs prévus à cet effet.</p>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h4>Enregistrer</h4>
                            <p>Enregistrez vos saisies. Les moyennes sont calculées automatiquement en temps réel.</p>
                        </div>
                    </div>
                </div>

                <div class="tip-box">
                    <div class="tip-icon">
                        <i class="bi bi-lightbulb-fill"></i>
                    </div>
                    <div class="tip-content">
                        <h4>Astuce</h4>
                        <p>Les moyennes et les rangs sont calculés automatiquement dès que vous enregistrez les notes d'une évaluation.</p>
                    </div>
                </div>
            </div>

            <!-- Section 2: Dashboard Enseignant -->
            <div class="section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <h2 class="section-title">Dashboard Enseignant</h2>
                </div>

                <div class="feature-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-door-open-fill"></i>
                        </div>
                        <h3 class="feature-title">Classes Assignées</h3>
                        <p class="feature-desc">Visualisez le nombre total de classes qui vous sont assignées.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-journal-check"></i>
                        </div>
                        <h3 class="feature-title">Disciplines Enseignées</h3>
                        <p class="feature-desc">Consultez le nombre de matières que vous enseignez.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-check-all"></i>
                        </div>
                        <h3 class="feature-title">Entrées Confirmées</h3>
                        <p class="feature-desc">Suivez le nombre total de notes que vous avez saisies.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <h3 class="feature-title">Progression Globale</h3>
                        <p class="feature-desc">Visualisez votre progression de saisie en pourcentage pour toutes vos matières assignées.</p>
                    </div>
                </div>

                <div class="step-list">
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>État des Séquences Actives</h4>
                            <p>Consultez la progression de saisie pour chaque séquence/évaluation active avec le pourcentage et le nombre de notes saisies.</p>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>Progression par Classe</h4>
                            <p>Visualisez un tableau détaillé de votre progression par classe avec le nombre d'élèves et le niveau de complétion.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            <p class="footer-text">
                <strong>NotesMaster</strong> - Plateforme de Gestion Scolaire<br>
                Documentation Enseignant v2.0 - &copy; <?= date('Y') ?> - Mis à jour le <?= date('d/m/Y') ?>
            </p>
        </div>
    </div>
</body>
</html>
