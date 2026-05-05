<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        :root {
            --m-primary: #4f46e5;
            --m-primary-dark: #3730a3;
            --m-bg: #ffffff;
            --m-text: #0f172a;
            --m-text-light: #64748b;
            --m-border: #e2e8f0;
            --m-section-bg: #f8fafc;
            --m-tip-bg: #f0fdf4;
            --m-tip-border: #dcfce7;
            --m-tip-text: #166534;
            --m-warn-bg: #fffbeb;
            --m-warn-border: #fef3c7;
            --m-warn-text: #92400e;
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
        .warning-box {
            background: var(--m-warn-bg);
            border: 1px solid var(--m-warn-border);
            padding: 15px;
            border-radius: 12px;
            margin-top: 15px;
            font-size: 15px;
            color: var(--m-warn-text);
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
            <h1 class="title">Guide Super Administrateur</h1>
            <p class="subtitle">Pilotage stratégique de NotesMaster v2.0</p>
        </div>

        <div class="section">
            <div class="section-title">1. Structure & Internationalisation</div>
            <div class="step">
                <span class="step-number">1</span>
                <div class="step-text">
                    <span class="step-title">Configuration Bilingue</span>
                    NotesMaster v2.0 est désormais 100% bilingue (FR/EN). Vous pouvez définir la langue par défaut de l'établissement dans les paramètres globaux.
                </div>
            </div>
            <div class="step">
                <span class="step-number">2</span>
                <div class="step-text">
                    <span class="step-title">Gestion des Sections</span>
                    Organisez votre école par sections (Francophone/Anglophone). Les modèles de rapports (PV, Bulletins) s'adaptent automatiquement à la section choisie.
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">2. Pilotage des Résultats "Gold Standard"</div>
            <div class="step">
                <span class="step-number">1</span>
                <div class="step-text">
                    <span class="step-title">Rapports Institutionnels</span>
                    Supervisez la génération des <strong>Procès-Verbaux modernisés</strong>. Ces documents offrent une vue paysage A4 avec une pagination rigoureuse et une analytique avancée (taux de réussite, top/flop matières).
                </div>
            </div>
            <div class="step">
                <span class="step-number">2</span>
                <div class="step-text">
                    <span class="step-title">Optimisation de l'Affectation</span>
                    Grâce au nouveau moteur de <strong>recherche instantanée</strong>, l'affectation des matières aux classes est désormais 5 fois plus rapide, facilitant la configuration de début d'année.
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">3. Surveillance & Sécurité</div>
            <div class="step">
                <span class="step-number">1</span>
                <div class="step-text">
                    <span class="step-title">Intégrité des Données</span>
                    Le module de discipline est désormais centralisé. En tant que Superadmin, vous pouvez auditer toutes les sanctions et absences qui impactent les bulletins finaux.
                </div>
            </div>
            <div class="step">
                <span class="step-number">2</span>
                <div class="step-text">
                    <span class="step-title">Sauvegardes & Maintenance</span>
                    Le système de sauvegarde a été renforcé. Veillez à exporter régulièrement la base de données pour garantir la continuité du service.
                </div>
            </div>
            <div class="warning-box">
                <strong>⚠️ Attention :</strong> Vos privilèges vous permettent de modifier les coefficients et les groupes de matières à tout moment. Toute modification impacte rétroactivement les calculs de moyennes.
            </div>
        </div>

        <div class="footer">
            NotesMaster v2.0 - &copy; <?= date('Y') ?> - Documentation mise à jour le <?= date('d/m/Y') ?>
        </div>
    </div>
</body>
</html>
