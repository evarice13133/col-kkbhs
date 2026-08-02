# Guide Utilisateur Global NoteMaster (Tous Rôles)

---

## 1. Vue d'Ensemble & Ergonomie du Ruban Microsoft 365

L'interface de **NoteMaster** (Futura CamerTech) s'articule autour d'un **ruban de navigation horizontal dynamique** inspiré de Microsoft Word 365.

### 1.1 Navigation & Raccourcis Clés
- **Ruban Horizontal (Tabs & Groupes)** : Les onglets (Accueil, Pilotage, RH, Comptes, Finances, Notes, Impressions) s'affichent dynamiquement en fonction du rôle et des permissions RBAC attribuées à votre compte.
- **Barre d'Accès Rapide (QAT)** : Située en haut à gauche, elle regroupe vos actions quotidiennes les plus fréquentes (Tableau de bord, Saisie des notes, Caisse, Inscription élève, Impressions).
- **Palette de Commandes Globales (`Ctrl+K` / `Cmd+K`)** : Permet de rechercher instantanément une fonctionnalité, un formulaire ou une page sans naviguer dans les menus.
- **Sélecteur de Thème & Langue** : Bascule entre le mode Clair/Sombre et la langue d'interface (Français/Anglais) depuis le menu profil en haut à droite.

---

## 2. Guide par Profil Utilisateur

---

### 2.1 Guide Super Administrateur (`superadmin`)

#### Périmètre & Responsabilités
Le Super Administrateur dispose du contrôle total sur la plateforme SaaS, les configurations système, l'infrastructure de base de données et la sécurité RBAC.

#### Actions Principales
1. **Sauvegarde et Restauration BDD (`/settings/run_backup`)** :
   - Accéder au Centre de Pilotage → Paramètres.
   - Déclencher le bouton "Lancer la Sauvegarde BDD".
   - Le système génère un horodatage SQL dans `storage/backups/`.
2. **Clôture et Archivage d'Année (`/academic_years/archive_wizard`)** :
   - Ouvrir l'Assistant d'Archivage sur l'année écoulée.
   - Vérifier l'intégrité des bulletins et des notes.
   - Clôturer l'année (passage en lecture seule) et basculer sur la nouvelle campagne.
3. **Administration RBAC & Audit (`/admin/run-migrations`, `logs/security.log`)** :
   - Consulter la piste d'audit de sécurité et exécuter le runner de migration.

---

### 2.2 Guide Administrateur (`admin`)

#### Périmètre & Responsabilités
L'Administrateur supervise la gestion pédagogique, l'encadrement des enseignants, la validation financière et la génération des impressions officielles.

#### Actions Principales
1. **Validation & Calcul des Bulletins (`/bulletins`)** :
   - Sélectionner la section, la classe et le trimestre.
   - Le système génère le lot PDF avec moyennes coeficiées, rangs et mentions.
2. **Édition des Relevés de Notes Consolidés (`/transcripts`)** :
   - Sélectionner l'élève et la classe.
   - Générer le relevé officiel consolidé structuré par Unités d'Enseignement (UE/UV).
3. **Supervision des Évaluations & Discipline (`/sequences`, `/bulletins/discipline`)** :
   - Configurer les séquences et enregistrer les blâmes/absences disciplinaires.

---

### 2.3 Guide IT Manager (`it_manager`)

#### Périmètre & Responsabilités
L'IT Manager administre l'infrastructure académique, la gestion des comptes applicatifs et l'affectation du corps enseignant.

#### Actions Principales
1. **Création & Activation des Années Scolaires (`/academic_years`)** :
   - Créer la nouvelle année scolaire avec ses dates de début et fin.
   - Définir l'année comme "Active" pour l'établissement.
2. **Gestion des Comptes Utilisateurs (`/users`)** :
   - Créer les comptes pour les enseignants, caissiers et agents d'encaissement.
   - Réinitialiser les mots de passe et activer/désactiver les comptes.
3. **Affectation des Enseignants (`/teachers`)** :
   - Associer les matières et les classes attribuées à chaque enseignant.

---

### 2.4 Guide Comptable (`comptable`)

#### Périmètre & Responsabilités
Le Comptable gère la tarification des frais de scolarité, l'approbation des dépenses de fonctionnement et le suivi de la trésorerie.

#### Actions Principales
1. **Configuration de la Grille Tarifaire (`/school_fees/grille`, `/school_fees/tranches`)** :
   - Définir le montant total et la répartition par tranches des frais de scolarité par classe.
2. **Saisie & Validation des Dépenses (`/expenses`)** :
   - Enregistrer les dépenses d'exploitation (Salaires, Électricité, Maintenance) avec la catégorie et la référence de la pièce justificative.
3. **Piste d'Audit & Contrôle Financier (`/expenses/audit`, `/financial-history`)** :
   - Consulter le journal comptable global et la piste d'audit des modifications de caisse.
4. **Rapport des Insolvables (`/school_fees/insolvables`)** :
   - Exporter la liste des élèves en retard de paiement par tranche.

---

### 2.5 Guide Caissier (`caissier`)

#### Périmètre & Responsabilités
Le Caissier enregistre les versements de scolarité au quotidien, délivre les reçus sécurisés et effectue les inscriptions initiales.

#### Actions Principales
1. **Encaissement & Reçu de Caisse (`/payments`)** :
   - Rechercher l'élève par son nom ou matricule.
   - Saisir le montant versé, le mode de paiement (Espèces, Mobile Money, Virement) et la tranche.
   - Générer le reçu PDF sécurisé comportant un **QR Code de vérification publique**.
2. **Inscription d'un Élève (`/students/create`)** :
   - Saisir la fiche d'état civil, les coordonnées du parent et la classe attribuée.
   - Valider pour attribuer le matricule unique d'immatriculation.
3. **Journal de Caisse (`/school_fees/versements`)** :
   - Contrôler à la fin de la journée le total des encaissements enregistrés.

---

### 2.6 Guide Enseignant (`enseignant`)

#### Périmètre & Responsabilités
L'Enseignant effectue la saisie des notes d'évaluation pour ses classes et matières assignées.

#### Actions Principales
1. **Saisie Centralisée des Notes (`/notes`)** :
   - Sélectionner la classe, la matière et la séquence ouverte.
   - Saisir les notes des élèves et valider l'enregistrement.
2. **Import / Export Excel des Notes** :
   - Télécharger le canevas Excel de la classe.
   - Compléter les notes hors-ligne et importer le fichier Excel rempli.
3. **Consultation de sa Classe (`/students`)** :
   - Accéder à la liste des élèves de ses classes attribuées.

---

## 3. Guide de Résolution des Problèmes & Dépannage

| Problème Rencontré | Cause Fréquente | Solution |
|---|---|---|
| **Message "Accès Interdit (403)"** | Votre compte ne possède pas la permission RBAC requise pour cette action. | Contactez votre Administrateur ou IT Manager pour ajuster vos droits. |
| **Saisie de note bloquée** | La séquence sélectionnée est fermée ou clôturée par l'administration. | Demandez à l'administration de réouvrir temporairement la période d'évaluation. |
| **QR Code de reçu invalide** | Modification manuelle du reçu ou référence inexistante. | Scanner le QR Code mène à la route `/verify-receipt` pour contrôler l'authenticité en DB. |
| **Bouton d'action absent du Ruban** | La fonctionnalité est masquée dynamiquement car non autorisée pour votre rôle. | Référez-vous à la matrice RBAC du guide technique. |
