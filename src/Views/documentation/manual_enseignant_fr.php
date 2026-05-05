<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        :root {
            --m-primary: #8b5cf6;
            --m-primary-dark: #6d28d9;
            --m-bg: #ffffff;
            --m-text: #0f172a;
            --m-text-light: #64748b;
            --m-border: #e2e8f0;
            --m-section-bg: #f8fafc;
            --m-tip-bg: #f0fdf4;
            --m-tip-border: #dcfce7;
            --m-tip-text: #166534;
        }
        body { 
            font-family: 'Inter', -apple-system, sans-serif; 
            color: var(--m-text); 
            line-height: 1.6; 
            background: var(--m-bg);
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 850px;
            margin: 0 auto;
            background: var(--m-bg);
            padding: 40px;
        }
        .header { 
            text-align: center; 
            margin-bottom: 40px; 
            padding-bottom: 30px;
            border-bottom: 2px solid var(--m-border);
        }
        .title { 
            font-family: 'Outfit', sans-serif;
            color: var(--m-primary); 
            font-size: 32px; 
            font-weight: 800;
            margin: 0 0 8px 0;
        }
        .subtitle { 
            color: var(--m-text-light); 
            font-size: 17px; 
            margin: 0;
        }
        .section { 
            margin-bottom: 45px; 
        }
        .section-title { 
            font-family: 'Outfit', sans-serif;
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--m-section-bg); 
            color: var(--m-primary-dark); 
            padding: 12px 20px; 
            border-radius: 12px; 
            font-size: 20px; 
            font-weight: 700; 
            margin-bottom: 20px; 
            border-left: 5px solid var(--m-primary);
        }
        .step { 
            margin-bottom: 20px; 
            display: flex;
            gap: 15px;
        }
        .step-number { 
            background: var(--m-primary); 
            color: #ffffff; 
            min-width: 28px; 
            height: 28px; 
            border-radius: 8px; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            font-weight: 800; 
            font-size: 14px;
            margin-top: 2px;
        }
        .step-text { flex: 1; }
        .step-title { font-weight: 700; color: var(--m-text); display: block; margin-bottom: 4px; font-size: 16px; }
        .tip {
            background: var(--m-tip-bg);
            border: 1px solid var(--m-tip-border);
            padding: 15px;
            border-radius: 12px;
            margin-top: 15px;
            font-size: 15px;
            color: var(--m-tip-text);
            display: flex;
            gap: 10px;
        }
        .footer { 
            text-align: center; 
            font-size: 12px; 
            color: var(--m-text-light); 
            border-top: 1px solid var(--m-border); 
            padding-top: 25px;
            margin-top: 50px;
        }
        strong { color: var(--m-primary-dark); font-weight: 700; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="title">Guide Enseignant</h1>
            <p class="subtitle">Gestion quotidienne de vos classes et notes</p>
        </div>

        <div class="section">
            <div class="section-title">1. Saisie des Notes & Discipline</div>
            <div class="step">
                <span class="step-number">1</span>
                <div class="step-text">
                    <span class="step-title">Accéder aux classes</span>
                    Allez dans <strong>"Saisie des notes"</strong> pour voir vos cours affectés. Le système supporte désormais le filtrage rapide des classes.
                </div>
            </div>
            <div class="step">
                <span class="step-number">2</span>
                <div class="step-text">
                    <span class="step-title">Discipline en temps réel</span>
                    Vous pouvez désormais signaler les absences et les sanctions disciplinaires directement depuis votre interface. Ces informations seront reflétées sur les bulletins officiels.
                </div>
            </div>
            <div class="tip">
                💡 <strong>Astuce :</strong> Les moyennes et les rangs sont calculés automatiquement dès que vous enregistrez les notes d'une évaluation.
            </div>
        </div>

        <div class="section">
            <div class="section-title">2. Rapports & Consultations</div>
            <div class="step">
                <span class="step-number">1</span>
                <div class="step-text">
                    <span class="step-title">Procès-Verbaux de Classe</span>
                    En tant que titulaire, vous pouvez générer des <strong>Procès-Verbaux (PV)</strong> modernisés au format paysage A4, incluant des analyses statistiques détaillées par groupe de matières.
                </div>
            </div>
            <div class="step">
                <span class="step-number">2</span>
                <div class="step-text">
                    <span class="step-title">Visualisation Multilingue</span>
                    Les documents administratifs s'adaptent automatiquement à la langue de la section de votre classe (Français ou Anglais).
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">3. Votre Profil & Sécurité</div>
            <div class="step">
                <span class="step-number">1</span>
                <div class="step-text">
                    <span class="step-title">Gestion du compte</span>
                    Mettez à jour vos informations personnelles, votre photo et votre mot de passe dans l'onglet <strong>"Mon Profil"</strong>.
                </div>
            </div>
        </div>

        <div class="footer">
            NotesMaster v2.0 - &copy; <?= date('Y') ?> - Documentation mise à jour le <?= date('d/m/Y') ?>
        </div>
    </div>
</body>
</html>
