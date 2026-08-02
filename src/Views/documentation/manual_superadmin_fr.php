<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Guide Utilisateur - Super Administrateur - NoteMaster</title>
    <style>
        :root {
            --m-primary: #7c3aed;
            --m-primary-dark: #5b21b6;
            --m-bg: #ffffff;
            --m-text: #0f172a;
            --m-text-light: #64748b;
            --m-border: #e2e8f0;
            --m-section-bg: #f5f3ff;
            --m-tip-bg: #ede9fe;
            --m-tip-border: #ddd6fe;
            --m-tip-text: #4c1d95;
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
            <h1 class="title">Guide Utilisateur — Super Administrateur</h1>
            <p class="subtitle">Administration Système Complète, Sauvegardes, Clôture Annuelle & Sécurité RBAC</p>
        </div>

        <div class="section">
            <div class="section-title">1. Espace Super Administrateur & Privilèges</div>
            <p>Le <strong>Super Administrateur</strong> possède les droits totaux et inconditionnels sur l'ensemble de la plateforme SaaS NoteMaster.</p>
            <div class="feature-box">
                <strong>Périmètre Exclusif :</strong>
                <ul>
                    <li><strong>Sauvegarde et Restauration BDD :</strong> Déclenchement des sauvegardes complètes en un clic et restauration d'archives (`/settings/run_backup`).</li>
                    <li><strong>Clôture et Archivage d'Année :</strong> Assistant de fin d'année et verrouillage de campagne (`/academic_years/archive_wizard`).</li>
                    <li><strong>Sécurité RBAC :</strong> Gestion intégrale des rôles, réassignation des permissions et audit de sécurité (`/admin/run-migrations`, `security.log`).</li>
                    <li><strong>Configuration Système :</strong> Identité visuelle, logos par type d'enseignement et clés de sécurité.</li>
                </ul>
            </div>
        </div>

        <div class="section">
            <div class="section-title">2. Procédure : Déclencher une Sauvegarde Système</div>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">
                    <span class="step-title">Accéder aux Paramètres Système</span>
                    Allez dans <strong>Centre de Pilotage → Paramètres</strong> (`/settings`).
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">
                    <span class="step-title">Lancer la Sauvegarde</span>
                    Cliquez sur le bouton "Lancer la Sauvegarde BDD". L'application génère un snapshot SQL complet dans `storage/backups/`.
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">3. Procédure : Assistant de Clôture & Archivage d'Année</div>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">
                    <span class="step-title">Ouvrir l'Assistant d'Archivage</span>
                    Dans <strong>Années Scolaires</strong> (`/academic_years`), cliquez sur "Archiver" sur l'année écoulée.
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">
                    <span class="step-title">Vérification de la Cohérence</span>
                    L'assistant contrôle que tous les bulletins et PV sont générés et que les notes sont définitivement verrouillées.
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">
                    <span class="step-title">Clôture & Bascule d'Année Active</span>
                    L'année est archivée en mode lecture seule et l'année suivante est activée.
                </div>
            </div>
        </div>

        <div class="footer">
            NoteMaster © <?= date('Y') ?> Futura CamerTech — Manuel Super Administrateur v2.5
        </div>
    </div>
</body>
</html>
