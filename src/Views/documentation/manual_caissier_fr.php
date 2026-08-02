<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Guide Utilisateur - Caissier - NoteMaster</title>
    <style>
        :root {
            --m-primary: #059669;
            --m-primary-dark: #047857;
            --m-bg: #ffffff;
            --m-text: #0f172a;
            --m-text-light: #64748b;
            --m-border: #e2e8f0;
            --m-section-bg: #f0fdf4;
            --m-tip-bg: #ecfdf5;
            --m-tip-border: #a7f3d0;
            --m-tip-text: #065f46;
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
            <h1 class="title">Guide Utilisateur — Caissier</h1>
            <p class="subtitle">Gestion des Inscriptions, Enregistrement des Paiements & Reçus de Caisse Sécurisés</p>
        </div>

        <div class="section">
            <div class="section-title">1. Espace Utilisateur & Ruban de Navigation</div>
            <p>En tant que <strong>Caissier</strong>, votre espace de travail est optimisé pour la rapidité des opérations de caisse et la gestion des inscriptions d'élèves.</p>
            <div class="feature-box">
                <strong>Onglets et Menus Accessibles :</strong>
                <ul>
                    <li><strong>Accueil :</strong> Accès au tableau de bord général et aux raccourcis de caisse.</li>
                    <li><strong>RH / Élèves :</strong> Inscription des nouveaux élèves (`/students/create`), annuaire des élèves inscrits et suivi de vos enregistrements.</li>
                    <li><strong>Finances (Scolarité & Exonérations) :</strong>
                        <ul>
                            <li>Paiements & Reçus (`/payments`) — Encaissement direct et délivrance de reçu.</li>
                            <li>Grille Tarifaire (`/school_fees/grille`) — Consultation de la scolarité par classe.</li>
                            <li>Journal des Versements (`/school_fees/versements`) — Historique des encaissements de caisse.</li>
                            <li>Rapport des Insolvables (`/school_fees/insolvables`) — Élèves en retard de paiement.</li>
                            <li>Réductions & Bourses (`/discounts`, `/scholarships`) — Consultation des exonérations.</li>
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="tip">
                💡 <strong>Raccourci Rapide :</strong> Appuyez sur <kbd>Ctrl+K</kbd> ou <kbd>Cmd+K</kbd> n'importe où dans l'application pour ouvrir la palette de commandes et chercher instantanément un élève ou une action de caisse.
            </div>
        </div>

        <div class="section">
            <div class="section-title">2. Procédure : Encaisser un Versement & Émettre un Reçu</div>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">
                    <span class="step-title">Accéder au Module Paiements</span>
                    Cliquez sur <strong>Finances → Paiements & Reçus</strong> dans le ruban ou cliquez sur l'icône de carte bancaire dans la barre d'accès rapide.
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">
                    <span class="step-title">Sélectionner l'Élève</span>
                    Utilisez le champ de recherche pour trouver l'élève par son nom, prénom ou matricule unique.
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">
                    <span class="step-title">Saisir le Versement</span>
                    Indiquez le montant versé, le mode de paiement (Espèces, Mobile Money, Virement) et la tranche concernée.
                </div>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-text">
                    <span class="step-title">Générer le Reçu Sécurisé</span>
                    Validez l'opération. Le système génère automatiquement un reçu PDF horodaté contenant un <strong>QR Code de vérification publique</strong>. Imprimez le reçu pour le parent.
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">3. Procédure : Inscrire un Nouvel Élève</div>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">
                    <span class="step-title">Ouvrir le Formulaire d'Inscription</span>
                    Rendez-vous dans <strong>Ressources Humaines → Inscrire un Élève</strong> (`/students/create`).
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">
                    <span class="step-title">Renseigner la Fiche Élève</span>
                    Saisissez le nom, prénom, date de naissance, sexe, classe d'affectation et les coordonnées du parent/tuteur.
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">
                    <span class="step-title">Validation & Génération du Matricule</span>
                    Validez le formulaire. L'application génère un matricule unique pour l'élève et l'enregistre immédiatement dans la base académique.
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">4. Bonne Pratiques & Clôture de Caisse</div>
            <ul>
                <li>Vérifiez toujours le nom et la classe de l'élève avant de valider un encaissement.</li>
                <li>Consultez régulièrement le <strong>Journal des Versements</strong> (`/school_fees/versements`) à la fin de votre journée pour contrôler le total des espèces perçues.</li>
                <li>Toute tentative de modification ou d'annulation de reçu nécessite la validation d'un Comptable ou Administrateur.</li>
            </ul>
        </div>

        <div class="footer">
            NoteMaster © <?= date('Y') ?> Futura CamerTech — Manuel Utilisateur Caissier v2.5
        </div>
    </div>
</body>
</html>
