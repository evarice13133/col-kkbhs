<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Guide Utilisateur - Administrateur - NoteMaster</title>
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
        body { font-family: 'Inter', -apple-system, sans-serif; color: var(--m-text); line-height: 1.6; background: var(--m-bg); margin: 0; padding: 0; }
        .container { max-width: 850px; margin: 0 auto; background: var(--m-bg); padding: 40px; }
        .header { text-align: center; margin-bottom: 40px; padding-bottom: 30px; border-bottom: 2px solid var(--m-border); }
        .title { font-family: 'Outfit', sans-serif; color: var(--m-primary); font-size: 32px; font-weight: 800; margin: 0 0 8px 0; }
        .subtitle { color: var(--m-text-light); font-size: 17px; margin: 0; }
        .section { margin-bottom: 45px; }
        .section-title { font-family: 'Outfit', sans-serif; display: flex; align-items: center; gap: 12px; background: var(--m-section-bg); color: var(--m-primary-dark); padding: 12px 20px; border-radius: 12px; font-size: 20px; font-weight: 700; margin-bottom: 20px; border-left: 5px solid var(--m-primary); }
        .step { margin-bottom: 20px; display: flex; gap: 15px; }
        .step-number { background: var(--m-primary); color: #ffffff; min-width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; margin-top: 2px; }
        .step-text { flex: 1; }
        .step-title { font-weight: 700; color: var(--m-text); display: block; margin-bottom: 4px; font-size: 16px; }
        .tip { background: var(--m-tip-bg); border: 1px solid var(--m-tip-border); padding: 15px; border-radius: 12px; margin-top: 15px; font-size: 15px; color: var(--m-tip-text); }
        .feature-box { border: 1px solid var(--m-border); border-radius: 12px; padding: 15px; margin-bottom: 15px; background: #fafafa; }
        .footer { text-align: center; font-size: 12px; color: var(--m-text-light); margin-top: 50px; padding-top: 20px; border-top: 1px solid var(--m-border); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="title">Guide Utilisateur — Administrateur</h1>
            <p class="subtitle">Pilotage Pédagogique, Supervision Financière, Bulletins, PV & Relevés de Notes</p>
        </div>

        <div class="section">
            <div class="section-title">1. Espace Administrateur & Menus Accessibles</div>
            <p>Le rôle <strong>Administrateur</strong> dispose d'un périmètre étendu pour piloter les activités académiques, encadrer le corps enseignant et éditer les documents officiels.</p>
            <div class="feature-box">
                <strong>Ruban de Navigation :</strong>
                <ul>
                    <li><strong>Pilotage :</strong> Types d'enseignement, Niveaux, Cycles, Sections, Départements et Paramètres école.</li>
                    <li><strong>Ressources Humaines :</strong> Inscription et annuaire des élèves, Gestion des caissiers et Enseignants.</li>
                    <li><strong>Finances :</strong> Versements, Grille tarifaire, Tranches, Réductions, Bourses, Dépenses & Audit.</li>
                    <li><strong>Gestion des Notes :</strong> Séquences/Évaluations, Saisie centralisée des notes, Matières, Groupes de modules (UE) et Discipline.</li>
                    <li><strong>Impressions :</strong> Bulletins de notes PDF, Tableaux d'honneur, Procès-Verbaux (PV) et Relevés de Notes consolidés (Transcripts).</li>
                </ul>
            </div>
            <div class="tip">
                💡 <strong>Palette de Commandes :</strong> Pressez <kbd>Cmd+K</kbd> ou <kbd>Ctrl+K</kbd> pour effectuer une recherche instantanée dans l'ensemble des modules.
            </div>
        </div>

        <div class="section">
            <div class="section-title">2. Procédure : Calculer & Imprimer les Bulletins Trimestriels</div>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">
                    <span class="step-title">Accéder au Module Bulletins</span>
                    Rendez-vous dans <strong>Impressions → Bulletins</strong> (`/bulletins`).
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">
                    <span class="step-title">Filtrer la Classe et la Période</span>
                    Sélectionnez la section, la classe et le trimestre ou la séquence cible.
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">
                    <span class="step-title">Calcul & Génération des PDF</span>
                    Le système calcule automatiquement les moyennes coeficiées, les rangs et les appréciations. Téléchargez le lot PDF prêt pour l'impression.
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">3. Procédure : Édition des Relevés de Notes Consolidés (Transcripts)</div>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">
                    <span class="step-title">Ouvrir le Module Relevés de Notes</span>
                    Cliquez sur <strong>Impressions → Relevé de Notes</strong> (`/transcripts`).
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">
                    <span class="step-title">Sélectionner la Classe et l'Élève</span>
                    Sélectionnez la classe et l'élève requis. Les unités d'enseignement (UE/UV) associées s'affichent automatiquement.
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">
                    <span class="step-title">Export PDF du Relevé Officiel</span>
                    Générez le document consolidé avec mention et décision du jury.
                </div>
            </div>
        </div>

        <div class="footer">
            NoteMaster © <?= date('Y') ?> Futura CamerTech — Manuel Utilisateur Administrateur v2.5
        </div>
    </div>
</body>
</html>
