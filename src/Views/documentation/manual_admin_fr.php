<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        :root {
            --m-primary: #10b981;
            --m-primary-dark: #065f46;
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
            <h1 class="title">Guide Administrateur</h1>
            <p class="subtitle">Gestion fluide et efficace de la scolarité</p>
        </div>

        <div class="section">
            <div class="section-title">1. Gestion des Élèves & Classes</div>
            <div class="step">
                <span class="step-number">1</span>
                <div class="step-text">
                    <span class="step-title">Inscriptions & Fiches</span>
                    Gérez les nouveaux arrivants via le menu <strong>"Élèves"</strong>. Vous pouvez générer des fiches récapitulatives de classe en un clic.
                </div>
            </div>
            <div class="step">
                <span class="step-number">2</span>
                <div class="step-text">
                    <span class="step-title">Configuration des Matières</span>
                    Lors de la création ou modification d'une matière, utilisez la <strong>recherche instantanée</strong> pour affecter rapidement les classes concernées sans parcourir toute la liste.
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">2. Résultats & Procès-Verbaux</div>
            <div class="step">
                <span class="step-number">1</span>
                <div class="step-text">
                    <span class="step-title">Procès-Verbaux "Gold Standard"</span>
                    Les PV sont désormais générés en mode <strong>A4 Paysage</strong>. Ils incluent une pagination automatique (20 élèves max par page) et des en-têtes de matières avec retour à la ligne pour une lisibilité parfaite.
                </div>
            </div>
            <div class="step">
                <span class="step-number">2</span>
                <div class="step-text">
                    <span class="step-title">Analytique Avancée</span>
                    Chaque PV intègre automatiquement une synthèse par groupe de matières, le top/flop des matières et le taux de réussite global. Les signatures sont fixées en fin de document.
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">3. Discipline & Internationalisation</div>
            <div class="step">
                <span class="step-number">1</span>
                <div class="step-text">
                    <span class="step-title">Suivi Disciplinaire</span>
                    Saisissez les absences et les sanctions dans le module dédié. Ces données sont automatiquement synchronisées sur les bulletins et PV.
                </div>
            </div>
            <div class="step">
                <span class="step-number">2</span>
                <div class="step-text">
                    <span class="step-title">Système Bilingue</span>
                    L'application bascule entièrement entre Français et Anglais. Les PV et bulletins s'adaptent automatiquement à la section (Francophone/Anglophone) de l'établissement.
                </div>
            </div>
        </div>

        <div class="tip">
            <strong>Conseil :</strong> Pour une impression optimale des PV, vérifiez que l'option "En-têtes et pieds de page" est activée dans votre navigateur pour profiter de la pagination automatique intégrée.
        </div>

        <div class="footer">
            NotesMaster v2.0 - &copy; <?= date('Y') ?> - Documentation mise à jour le <?= date('d/m/Y') ?>
        </div>
    </div>
</body>
</html>
