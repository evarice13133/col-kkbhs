# Documentation Fonctionnelle NoteMaster

## 1. Vue d'Ensemble de l'Application

**NoteMaster** (Futura CamerTech) est une plateforme SaaS de gestion d'établissements scolaires adaptée aux systèmes éducatifs secondaires et supérieurs (francophones et anglophones). Elle couvre la gestion académique, administrative, financière, les évaluations et la génération de documents officiels.

---

## 2. Description des Modules Métier

### 2.1 Module Administration & Sécurité (RBAC)
- **Gestion des utilisateurs** : Création, modification, désactivation et réinitialisation des comptes utilisateurs (Administrateurs, IT Managers, Comptables, Caissiers, Enseignants).
- **Rôles & Permissions (RBAC)** : Contrôle d'accès granulaire garantissant le principe du moindre privilège.
- **Paramètres Globaux** : Configuration de l'identité de l'établissement (Nom, Code, Logos, Slogan, Titres officiels) par type d'enseignement.
- **Logs et Piste d'Audit** : Traçabilité des connexions, des navigations et des opérations sensibles (paiements, saisie de notes, modifications de paramètres).

### 2.2 Module Structure Académique & Pédagogique
- **Années Académiques** : Gestion des campagnes scolaires, activation de l'année courante, assistant d'archivage et de clôture.
- **Types d'Enseignement** : Structuration de l'enseignement (Général, Technique, Professionnel, Bilingue).
- **Niveaux & Cycles** : Organisation des niveaux scolaires (ex: 6ème à Tle, Form 1 to Upper Sixth) et des cycles d'études (Premier cycle, Second cycle).
- **Sections & Départements** : Découpage en sections (Francophone, Anglophone) et départements disciplinaires.
- **Classes & Affectations** : Gestion des effectifs, attribution du professeur principal et association de l'équipe enseignante.
- **Catalogue des Matières & Groupes (UE)** : Configuration des matières, coefficients, groupes de modules (Unités d'Enseignement) et codes UV/UE.

### 2.3 Module Gestion des Élèves (RH Élèves)
- **Inscriptions & Matricules** : Immatriculation automatique ou manuelle des élèves, affectation aux classes et suivi des dossiers.
- **Registres** : Consultation des fiches individuelles, élèves inscrits, pré-inscrits et démissionnaires/abandonnés.
- **Import / Export** : Importation en masse via templates Excel et exportation des annuaires.

### 2.4 Module Évaluations & Notes
- **Périodes & Séquences** : Configuration des séquences d'évaluation (Séquences 1 à 6) et des trimestres.
- **Saisie des Notes** : Interface de saisie centralisée par classe, matière et séquence, avec contrôle des notes minimales/maximales.
- **Discipline & Absences** : Enregistrement des heures d'absence, avertissements, blâmes et conduites.

### 2.5 Module Gestion Financière & Caisse
- **Grille Tarifaire & Tranches** : Paramétrage des frais de scolarité par classe et ventilation en tranches horodatées.
- **Versements & Reçus** : Enregistrement des paiements (Espèces, Mobile Money, Virement), émission de reçus uniques horodatés avec QR Code de vérification publique.
- **Réductions & Bourses** : Gestion des exonérations, remises exceptionnelles et bourses scolaires.
- **Gestion des Dépenses** : Saisie, catégorisation et approbation des dépenses de fonctionnement avec piste d'audit.
- **Rapports Financiers & Insolvables** : Suivi des créances, liste des élèves insolvables et journal de caisse quotidien.

### 2.6 Module Éditions & Documents Officiels
- **Bulletins de Notes** : Génération PDF des bulletins séquentiels, trimestriels et annuels avec calcul automatique des rangs, moyennes et appréciations.
- **Procès-Verbaux (PV)** : Édition des PV récapitulatifs d'évaluation et de fin d'année.
- **Relevés de Notes (Transcripts)** : Génération des relevés de notes officiels consolidés.
- **Tableau d'Honneur** : Impression des diplômes de mérite et félicitations.

---

## 3. Rôles et Parcours Utilisateurs

### 3.1 Superadministrateur (`superadmin`)
- **Périmètre** : Accès intégral et illimité à toutes les fonctionnalités et paramètres du système.
- **Parcours type** : Initialisation de l'école, gestion de l'année académique, sauvegardes BDD, audits de sécurité et gestion des accès des administrateurs.

### 3.2 Administrateur (`admin`)
- **Périmètre** : Pilotage quotidien de l'établissement (pédagogie, élèves, enseignants, finances et impressions).
- **Parcours type** : Validation de la structure des classes, supervision de la saisie des notes, impression des bulletins/PV et contrôle financier.

### 3.3 IT Manager (`it_manager`)
- **Périmètre** : Gestionnaire de l'infrastructure académique et de la configuration technique.
- **Parcours type** : Création des années scolaires, gestion des comptes utilisateurs, affectation des enseignants aux matières/classes et maintenance des référentiels.

### 3.4 Comptable (`comptable`)
- **Périmètre** : Responsable de la gestion financière, du contrôle budgétaire et de la trésorerie.
- **Parcours type** : Configuration des tarifs de scolarité, validation et audit des dépenses, génération des rapports d'insolvabilité et clôture financière.

### 3.5 Caissier (`caissier`)
- **Périmètre** : Opérations de caisse quotidiennes et encaissement des frais.
- **Parcours type** : Recherche d'élève, enregistrement des versements, impression instantanée des reçus sécurisés et inscription initiale des élèves.

### 3.6 Enseignant (`enseignant`)
- **Périmètre** : Accès restreint à la saisie des notes et à la consultation de ses classes attribuées.
- **Parcours type** : Consultation de l'emploi du temps/classes, saisie des notes par évaluation et suivi académique de ses élèves.

---

## 4. Règles Métier Principales

1. **Unicité du Matricule Élève** : Chaque élève possède un matricule unique à l'échelle de l'établissement.
2. **Intégrité des Notes** : Une note ne peut excéder la note maximale paramétrée pour la matière (défaut : 20).
3. **Clôture de Séquence** : La saisie des notes est verrouillée une fois la séquence clôturée par l'administration.
4. **Non-suppression des Transactions Financières** : Les reçus et paiements validés ne peuvent pas être supprimés directement sans laisser une trace dans l'historique d'audit.
5. **Principe du Moindre Privilège** : Aucun utilisateur ne voit un menu ou ne peut exécuter une action qui n'est pas couverte par ses permissions effectives.
