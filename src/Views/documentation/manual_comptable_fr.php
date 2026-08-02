<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Guide Utilisateur - Comptable - NoteMaster</title>
    <style>
        :root {
            --m-primary: #0284c7;
            --m-primary-dark: #0369a1;
            --m-bg: #ffffff;
            --m-text: #0f172a;
            --m-text-light: #64748b;
            --m-border: #e2e8f0;
            --m-section-bg: #f0f9ff;
            --m-tip-bg: #e0f2fe;
            --m-tip-border: #bae6fd;
            --m-tip-text: #0369a1;
        }
        body { font-family: 'Inter', -apple-system, sans-serif; color: var(--m-text); line-height: 1.6; background: var(--m-bg); margin: 0; padding: 0; }
        .container { max-width: 850px; margin: 0 auto; background: var(--m-bg); padding: 40px; }
        .header { text-align: center; margin-bottom: 40px; padding-bottom: 30px; border-bottom: 2px solid var(--m-border); }
        .title { font-family: 'Outfit', sans-serif; color: var(--m-primary); font-size: 30px; font-weight: 800; margin: 0 0 8px 0; }
        .subtitle { color: var(--m-text-light); font-size: 16px; margin: 0; }
        .section { margin-bottom: 40px; }
        .section-title { font-family: 'Outfit', sans-serif; display: flex; align-items: center; gap: 12px; background: var(--m-section-bg); color: var(--m-primary-dark); padding: 12px 20px; border-radius: 12px; font-size: 19px; font-weight: 700; margin-bottom: 20px; border-left: 5px solid var(--m-primary); }
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
            <h1 class="title">Guide Utilisateur — Comptable</h1>
            <p class="subtitle">Gestion Financière Globale, Configuration Tarifaire, Dépenses d'Exploitation & Audit Trésorerie</p>
        </div>

        <div class="section">
            <div class="section-title">1. Espace Utilisateur & Responsabilités Financières</div>
            <p>Le <strong>Comptable</strong> assure le contrôle budgétaire de l'établissement, le paramétrage de la scolarité et l'audit de caisse.</p>
            <div class="feature-box">
                <strong>Modules et Menus Accessibles :</strong>
                <ul>
                    <li><strong>Accueil :</strong> Tableau de bord des entrées/sorties et indicateurs financiers.</li>
                    <li><strong>Finances / Scolarité :</strong>
                        <ul>
                            <li>Grille Tarifaire (`/school_fees/grille`) — Définition des frais de scolarité par classe.</li>
                            <li>Tranches de Paiement (`/school_fees/tranches`) — Paramétrage des échéanciers.</li>
                            <li>Journal des Versements (`/school_fees/versements`) — Historique complet des entrées.</li>
                            <li>Rapport des Insolvables (`/school_fees/insolvables`) — Suivi des arriérés de paiement.</li>
                        </ul>
                    </li>
                    <li><strong>Finances / Exonérations :</strong>
                        <ul>
                            <li>Réductions & Remises (`/discounts`, `/discount_types`) — Gestion des règles de réduction.</li>
                            <li>Bourses Scolaires (`/scholarships`) — Attribution des bourses d'études.</li>
                        </ul>
                    </li>
                    <li><strong>Finances / Dépenses & Audit :</strong>
                        <ul>
                            <li>Historique Financier (`/financial-history`) — Journal comptable global.</li>
                            <li>Saisie des Dépenses (`/expenses`) — Engagements et charges d'exploitation.</li>
                            <li>Catégories de Dépenses (`/expenses/categories`) — Structuration budgétaire.</li>
                            <li>Piste d'Audit (`/expenses/audit`) — Contrôle de traçabilité des modifications.</li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>

        <div class="section">
            <div class="section-title">2. Procédure : Configurer la Grille Tarifaire d'une Classe</div>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">
                    <span class="step-title">Accéder à la Grille Tarifaire</span>
                    Rendez-vous dans <strong>Finances → Grille Tarifaire</strong> (`/school_fees/grille`).
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">
                    <span class="step-title">Sélectionner la Classe et le Type d'Enseignement</span>
                    Filtrez par type d'enseignement (ex: Général ou Technique) et sélectionnez la classe à paramétrer.
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">
                    <span class="step-title">Définir le Montant Total & la Ventilation en Tranches</span>
                    Saisissez les montants de la tranche d'inscription, tranche 1, 2 et 3 ainsi que leurs dates d'échéance.
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">3. Procédure : Saisir et Approuver une Dépense d'Exploitation</div>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">
                    <span class="step-title">Ouvrir le Module Dépenses</span>
                    Cliquez sur <strong>Finances → Liste des Dépenses</strong> (`/expenses`).
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">
                    <span class="step-title">Enregistrer une Nouvelle Dépense</span>
                    Cliquez sur "Nouvelle Dépense". Renseignez le motif, le montant, la catégorie (ex: Électricité, Salaires, Fournitures) et joignez la référence de la pièce justificative.
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">
                    <span class="step-title">Validation & Traçabilité</span>
                    La dépense est comptabilisée dans l'historique financier et enregistrée dans la <strong>Piste d'Audit</strong> (`/expenses/audit`).
                </div>
            </div>
        </div>

        <div class="footer">
            NoteMaster © <?= date('Y') ?> Futura CamerTech — Manuel Utilisateur Comptable v2.5
        </div>
    </div>
</body>
</html>
