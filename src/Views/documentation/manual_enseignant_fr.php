<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Guide Utilisateur - Enseignant - NoteMaster</title>
    <style>
        :root {
            --m-primary: #3b82f6;
            --m-primary-dark: #1d4ed8;
            --m-bg: #ffffff;
            --m-text: #0f172a;
            --m-text-light: #64748b;
            --m-border: #e2e8f0;
            --m-section-bg: #eff6ff;
            --m-tip-bg: #dbeafe;
            --m-tip-border: #bfdbfe;
            --m-tip-text: #1e40af;
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
            <h1 class="title">Guide Utilisateur — Enseignant</h1>
            <p class="subtitle">Saisie Centralisée des Notes, Suivi des Élèves & Export Excel des Évaluations</p>
        </div>

        <div class="section">
            <div class="section-title">1. Espace Enseignant & Ruban d'Accueil</div>
            <p>En tant qu'<strong>Enseignant</strong>, votre espace de travail est concentré sur les activités pédagogiques de vos classes attribuées.</p>
            <div class="feature-box">
                <strong>Menus et Fonctionnalités Accessibles :</strong>
                <ul>
                    <li><strong>Saisie des Notes (`/notes`) :</strong> Saisie directe des notes d'évaluation par classe, matière et séquence.</li>
                    <li><strong>Mes Élèves (`/students`) :</strong> Consultation des fiches et trombinoscopes de vos classes assignées.</li>
                    <li><strong>Aide & Documentation (`/documentation`) :</strong> Consultation et téléchargement des manuels d'utilisation.</li>
                </ul>
            </div>
            <div class="tip">
                💡 <strong>Astuce Gain de Temps :</strong> Dans l'écran de saisie des notes, utilisez la touche <kbd>Entrée</kbd> ou <kbd>Tab</kbd> pour passer automatiquement à l'élève suivant.
            </div>
        </div>

        <div class="section">
            <div class="section-title">2. Procédure : Saisir les Notes d'une Séquence</div>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">
                    <span class="step-title">Accéder à la Saisie des Notes</span>
                    Cliquez sur <strong>Saisie des Notes</strong> dans le ruban ou utilisez le bouton crayon de la barre d'accès rapide.
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">
                    <span class="step-title">Sélectionner les Filtres</span>
                    Choisissez l'année académique, la classe, la matière dispensée et la séquence d'évaluation (ex: Séquence 1).
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">
                    <span class="step-title">Saisie & Enregistrement</span>
                    Saisissez les notes (comprises entre 0 et la note maximale paramétrée, par exemple 20). Le système calcule en temps réel la moyenne de la classe. Cliquez sur <strong>Enregistrer les Notes</strong>.
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">3. Procédure : Import / Export Excel des Fiches de Notes</div>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">
                    <span class="step-title">Télécharger le Canevas Excel</span>
                    Sur la page de saisie des notes, cliquez sur <strong>Télécharger Template Excel</strong>.
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">
                    <span class="step-title">Remplir les Notes Hors-Ligne</span>
                    Complétez la colonne des notes dans le fichier Excel sur votre ordinateur sans modifier les matricules.
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">
                    <span class="step-title">Importer le Fichier Rempli</span>
                    Cliquez sur <strong>Importer Fichier Excel</strong> et sélectionnez votre fichier. Le système valide automatiquement les notes et signale les incohérences éventuelles.
                </div>
            </div>
        </div>

        <div class="footer">
            NoteMaster © <?= date('Y') ?> Futura CamerTech — Manuel Utilisateur Enseignant v2.5
        </div>
    </div>
</body>
</html>
