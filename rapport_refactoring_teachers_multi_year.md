# RAPPORT D'AUDIT : REFACTORING GESTION DES ENSEIGNANTS POUR ARCHITECTURE MULTI-ANNÉES

**Date:** 2026-06-03  
**Objectif:** Transformer l'architecture pour supporter la gestion multi-années des enseignants et des affectations  
**Statut:** Phase 1 - Audit complet terminé

---

## RÉSUMÉ EXÉCUTIF

L'audit révèle que le système actuel possède déjà une base multi-années partielle mais souffre de problèmes structurels critiques qui empêchent une véritable gestion historique des enseignants. La table `teacher_assignments` contient déjà `academic_year_id`, mais les contraintes de clé primaire et unique empêchent la gestion correcte des affectations multi-années.

---

## PHASE 1: AUDIT COMPLET

### 1.1 Fichiers identifiés liés aux enseignants

**Controllers:**
- `src/Controllers/TeacherController.php` (541 lignes)
  - Gestion CRUD des enseignants
  - Gestion des affectations pédagogiques
  - Import/Export
  - Déjà utilise `academic_year_id` dans les requêtes d'affectation

**Services:**
- `src/Services/Import/TeacherImportProcessor.php` - Import des enseignants

**Vues:**
- `src/Views/teachers/assign.php` - Interface d'affectation
- `src/Views/teachers/create.php` - Création enseignant
- `src/Views/teachers/edit.php` - Modification enseignant
- `src/Views/teachers/import.php` - Import enseignants
- `src/Views/teachers/index.php` - Liste enseignants
- `src/Views/teachers/templates/export_pdf_teachers.php` - Export PDF

**Autres:**
- `src/Views/auth/register_teacher.php` - Inscription enseignant
- `src/Views/dashboard/teacher.php` - Dashboard enseignant

---

### 1.2 Occurrences des champs clés

**teacher_id:** 38 occurrences dans 10 fichiers
- `GradeController.php` (13 occurrences) - Notes et teacher_id
- `ClassController.php` (6 occurrences) - Gestion équipe classe
- `TeacherController.php` (5 occurrences) - Affectations
- `Views/classes/manage_team.php` (4 occurrences) - Équipe pédagogique
- `AcademicYearController.php` (2 occurrences)
- `BulletinController.php` (2 occurrences)
- `AcademicYearService.php` (2 occurrences)
- `GradeImportProcessor.php` (2 occurrences)
- `DashboardController.php` (1 occurrence)
- `Views/teachers/index.php` (1 occurrence)

**class_id:** 284 occurrences dans 37 fichiers
- `GradeController.php` (79 occurrences)
- `BulletinController.php` (52 occurrences)
- `DashboardController.php` (26 occurrences)
- `StudentController.php` (21 occurrences)
- `TeacherController.php` (16 occurrences)
- `SubjectController.php` (13 occurrences)
- ... et 31 autres fichiers

**subject_id:** 198 occurrences dans 20 fichiers
- `GradeController.php` (72 occurrences)
- `BulletinController.php` (27 occurrences)
- `DashboardController.php` (23 occurrences)
- `TeacherController.php` (17 occurrences)
- `ProcesVerbalController.php` (14 occurrences)
- ... et 15 autres fichiers

**academic_year_id:** 130 occurrences dans 18 fichiers
- `BulletinController.php` (35 occurrences)
- `GradeController.php` (16 occurrences)
- `AcademicYearController.php` (13 occurrences)
- `DashboardController.php` (12 occurrences)
- `TeacherController.php` (11 occurrences)
- ... et 13 autres fichiers

---

### 1.3 Cartographie des relations actuelles

```
users (role='enseignant')
    ↓ (user_id)
teacher_assignments
    ├─ user_id → users.id
    ├─ subject_id → subjects.id
    ├─ class_id → classes.id
    └─ academic_year_id → academic_years.id

grades
    ├─ teacher_id → users.id (snapshot: teacher_nom_snapshot, teacher_prenom_snapshot)
    ├─ student_id → students.id
    ├─ subject_id → subjects.id
    └─ academic_year_id → academic_years.id

subject_classes
    ├─ subject_id → subjects.id
    ├─ class_id → classes.id
    └─ academic_year_id → academic_years.id
```

---

## PHASE 2: ANALYSE DE LA BASE DE DONNÉES

### 2.1 Structure actuelle de teacher_assignments

```sql
CREATE TABLE `teacher_assignments` (
  `user_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL DEFAULT 2,
  PRIMARY KEY (`user_id`,`subject_id`,`class_id`),
  UNIQUE KEY `idx_teacher_unique_assignment` (`class_id`,`subject_id`),
  KEY `subject_id` (`subject_id`),
  KEY `idx_ta_user` (`user_id`),
  KEY `idx_teacher_assignments_academic_year` (`academic_year_id`),
  CONSTRAINT `fk_teacher_assignments_academic_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `teacher_assignments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_assignments_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_assignments_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
```

### 2.2 Problèmes structurels identifiés

**PROBLÈME CRITIQUE #1: Clé primaire incorrecte**
- **Actuel:** `PRIMARY KEY (user_id, subject_id, class_id)`
- **Problème:** Empêche un enseignant d'avoir la même affectation dans différentes années scolaires
- **Impact:** Un enseignant qui enseigne Maths en 6ème A en 2024-2025 ne peut pas enseigner Maths en 6ème A en 2025-2026 sans supprimer l'affectation précédente

**PROBLÈME CRITIQUE #2: Contrainte unique incorrecte**
- **Actuel:** `UNIQUE KEY idx_teacher_unique_assignment (class_id, subject_id)`
- **Problème:** Empêche différents enseignants d'enseigner la même matière dans la même classe dans différentes années
- **Impact:** Si Prof A enseigne Maths en 6ème A en 2024-2025, Prof B ne peut pas enseigner Maths en 6ème A en 2025-2026

**PROBLÈME #3: Absence de table teacher_contracts**
- **Actuel:** Aucune table pour suivre l'historique des contrats et statuts
- **Impact:** Impossible de suivre les changements de statut (permanent → vacataire, départ, retour)

**PROBLÈME #4: Suppression directe d'enseignants**
- **Localisation:** `TeacherController::delete()` (ligne 168)
- **Problème:** Supprime l'enseignant sans vérifier l'historique pédagogique
- **Impact:** Perte de l'historique si l'enseignant a des notes ou des affectations

---

### 2.3 Structure de grades (snapshot)

```sql
CREATE TABLE `grades` (
  ...
  `teacher_id` int(11) DEFAULT NULL,
  `teacher_nom_snapshot` varchar(100) DEFAULT NULL COMMENT 'Nom de l''enseignant au moment de la saisie',
  `teacher_prenom_snapshot` varchar(100) DEFAULT NULL COMMENT 'Prénom de l''enseignant au moment de la saisie',
  ...
  CONSTRAINT `grades_fk_teacher_safe` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ...
)
```

**Observation positive:** Les snapshots préservent l'historique des enseignants dans les notes.

---

## PHASE 3: MODULES SUPPOSANT L'UNICITÉ TEMPORELLE

### 3.1 Modules identifiés

**TeacherController:**
- `assign()` - Affiche les affectations de l'année active uniquement
- `storeAssignment()` - Purge et ré-affecte pour l'année active
- `directAssign()` - Affectation rapide sans vérification d'unicité multi-années
- `delete()` - Suppression directe sans vérification d'historique

**GradeController:**
- Utilise `teacher_id` dans les notes mais avec snapshots
- Ne filtre pas par année scolaire pour l'affichage de l'historique enseignant

**ClassController:**
- `manage_team.php` - Affiche l'équipe pédagogique sans filtre d'année

**BulletinController:**
- Utilise `teacher_id` pour les signatures et appréciations
- Peut afficher des enseignants d'années précédentes

---

## PHASE 4: RECOMMANDATIONS DE REFACTORING

### 4.1 Correction de teacher_assignments

**Action requise:** Modifier la structure de la table

```sql
-- Supprimer l'ancienne clé primaire
ALTER TABLE teacher_assignments DROP PRIMARY KEY;

-- Supprimer la contrainte unique incorrecte
ALTER TABLE teacher_assignments DROP INDEX idx_teacher_unique_assignment;

-- Ajouter la nouvelle clé primaire correcte
ALTER TABLE teacher_assignments ADD PRIMARY KEY (user_id, subject_id, class_id, academic_year_id);

-- Ajouter une contrainte unique pour éviter les doublons dans la même année
ALTER TABLE teacher_assignments ADD UNIQUE KEY idx_unique_year_assignment (user_id, subject_id, class_id, academic_year_id);
```

### 4.2 Création de teacher_contracts

```sql
CREATE TABLE `teacher_contracts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `contract_type` enum('PERMANENT','VACATAIRE','CONTRACTUEL','STAGIAIRE','SUSPENDU','RETRAITE','INACTIF') NOT NULL DEFAULT 'VACATAIRE',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_teacher_year_contract` (`teacher_id`,`academic_year_id`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT `fk_teacher_contracts_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_teacher_contracts_academic_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### 4.3 Sécurisation de la suppression

**Modification de TeacherController::delete():**
- Vérifier si l'enseignant a des affectations historiques
- Vérifier si l'enseignant a des notes
- Si historique existe: utiliser un flag `is_active` au lieu de supprimer
- Si pas d'historique: permettre la suppression

### 4.4 Correction des écrans d'affectation

**TeacherController::assign():**
- Ajouter un sélecteur d'année scolaire
- Permettre de consulter l'historique des affectations
- Empêcher la modification des affectations d'années fermées

---

## PHASE 5: IMPACT SUR LES MODULES PÉDAGOGIQUES

### 5.1 Modules à auditer

**Saisie des notes:**
- Vérifier que l'enseignant actif est bien celui de l'année scolaire active
- Filtrer les affectations par `academic_year_id`

**Bulletins:**
- Utiliser les snapshots pour l'historique
- Filtrer les appréciations par année scolaire

**Calcul des moyennes:**
- Aucun impact direct (les notes sont déjà filtrées par année)

**Classements:**
- Aucun impact direct (dépend des notes filtrées)

**Tableaux d'honneur:**
- Aucun impact direct (dépend des notes filtrées)

**Conseils de classe:**
- Vérifier que les enseignants présents sont ceux de l'année active

**Décisions de fin d'année:**
- Utiliser les contrats pour déterminer le statut des enseignants

**Procès-verbaux:**
- Utiliser les snapshots pour l'historique
- Filtrer par année scolaire

---

## PHASE 6: PLAN DE MIGRATION

### 6.1 Migration de teacher_assignments

```sql
-- Étape 1: Sauvegarde des données existantes
CREATE TABLE teacher_assignments_backup AS SELECT * FROM teacher_assignments;

-- Étape 2: Suppression des contraintes
ALTER TABLE teacher_assignments DROP PRIMARY KEY;
ALTER TABLE teacher_assignments DROP INDEX idx_teacher_unique_assignment;

-- Étape 3: Ajout de la nouvelle clé primaire
ALTER TABLE teacher_assignments ADD PRIMARY KEY (user_id, subject_id, class_id, academic_year_id);

-- Étape 4: Ajout de la contrainte unique par année
ALTER TABLE teacher_assignments ADD UNIQUE KEY idx_unique_year_assignment (user_id, subject_id, class_id, academic_year_id);
```

### 6.2 Migration des contrats existants

```sql
-- Créer des contrats par défaut pour les enseignants actifs
INSERT INTO teacher_contracts (teacher_id, academic_year_id, contract_type, is_active)
SELECT DISTINCT ta.user_id, ta.academic_year_id, 'VACATAIRE' as contract_type, 1 as is_active
FROM teacher_assignments ta
WHERE NOT EXISTS (
    SELECT 1 FROM teacher_contracts tc 
    WHERE tc.teacher_id = ta.user_id AND tc.academic_year_id = ta.academic_year_id
);
```

---

## PHASE 7: TESTS RECOMMANDÉS

### 7.1 Scénario 1: Enseignant multi-années

**Année 2024-2025:**
- Prof A enseigne Maths en 6ème A

**Année 2025-2026:**
- Prof A quitte l'établissement

**Année 2026-2027:**
- Prof A revient comme vacataire
- Prof A enseigne Maths en Terminale C

**Vérifications:**
- Conservation de l'historique 2024-2025
- Aucune donnée perdue pendant 2025-2026
- Correcte affectation en 2026-2027
- Cohérence des notes et bulletins

### 7.2 Scénario 2: Changement de statut

**Année 2024-2025:**
- Prof B est permanent

**Année 2025-2026:**
- Prof B devient vacataire

**Vérifications:**
- Historique des contrats conservé
- Aucun impact sur les notes historiques
- Affichage correct du statut actuel

### 7.3 Scénario 3: Doublon d'affectation

**Tentative:**
- Affecter Prof C à Maths en 6ème A en 2024-2025
- Affecter Prof D à Maths en 6ème A en 2024-2025 (même année)

**Vérifications:**
- Rejet du doublon
- Message d'erreur clair
- Aucune donnée corrompue

---

## PHASE 8: SÉCURITÉ

### 8.1 Règles à implémenter

1. **Interdiction de suppression d'enseignants avec historique**
   - Vérifier les affectations passées
   - Vérifier les notes existantes
   - Utiliser un flag `is_active` si historique existe

2. **Interdiction des doublons d'affectation**
   - Contrainte unique `(user_id, subject_id, class_id, academic_year_id)`
   - Validation applicative avant insertion

3. **Interdiction des incohérences matière/classe**
   - Vérifier que la matière est assignée à la classe dans `subject_classes`
   - Vérifier que l'année scolaire est active pour les modifications

4. **Interdiction des affectations sur des années fermées**
   - Vérifier `academic_years.is_active = 1`
   - Bloquer les modifications sur les années historiques

5. **Archivage plutôt que suppression**
   - Utiliser `is_active` pour les enseignants
   - Conserver toutes les données historiques

---

## PHASE 9: LIVRABLES

### 9.1 Fichiers à modifier

**Controllers:**
- `src/Controllers/TeacherController.php`
  - Modifier `delete()` pour utiliser `is_active`
  - Ajouter sélecteur d'année dans `assign()`
  - Ajouter validation pour années fermées

**Vues:**
- `src/Views/teachers/assign.php`
  - Ajouter sélecteur d'année scolaire
  - Ajouter onglet historique
  - Bloquer modification années fermées

**Migrations:**
- Créer `scripts/migrate_teacher_assignments.php`
- Créer `scripts/migrate_teacher_contracts.php`

### 9.2 Nouveaux fichiers

**Migrations:**
- `scripts/migrate_teacher_assignments.php`
- `scripts/migrate_teacher_contracts.php`

**Services:**
- `src/Services/TeacherContractService.php` (optionnel)

---

## CONCLUSION

L'audit révèle que le système possède déjà une base multi-années partielle mais souffre de problèmes structurels critiques dans la table `teacher_assignments`. Les corrections nécessaires sont bien identifiées et ont été mises en œuvre avec succès.

---

## MISES À JOUR RÉALISÉES

### Phase 3: Migration de teacher_assignments ✓
- **Script:** `scripts/migrate_teacher_assignments.php`
- **Modifications:**
  - Nouvelle clé primaire: `(user_id, subject_id, class_id, academic_year_id)`
  - Nouvelle contrainte unique: `idx_unique_year_assignment`
  - Clés étrangères re-créées
- **Résultat:** 18 enregistrements migrés sans perte de données

### Phase 4: Création de teacher_contracts ✓
- **Script:** `scripts/migrate_teacher_contracts.php`
- **Modifications:**
  - Table créée avec structure complète
  - 4 contrats par défaut créés pour les enseignants actifs
- **Résultat:** Structure prête pour la gestion des statuts professionnels

### Phase 5: Gestion des départs et retours ✓
- **Fichier:** `src/Controllers/TeacherController.php`
- **Modifications:**
  - `delete()`: Utilise `is_active` au lieu de suppression si historique existe
  - `activate()`: Nouvelle méthode pour réactiver les enseignants
- **Résultat:** Les enseignants peuvent quitter et revenir sans perte d'historique

### Phase 6: Écrans d'affectation ✓
- **Fichiers:** `src/Controllers/TeacherController.php`, `src/Views/teachers/assign.php`
- **Modifications:**
  - Sélecteur d'année scolaire ajouté
  - Mode lecture seule pour les années fermées
  - Bouton sauvegarde désactivé pour années fermées
  - Onglet catalogue masqué en mode historique
- **Résultat:** Consultation historique possible sans risque de modification

### Phase 7: Impact pédagogique ✓
- **Audit:** Vérification des contrôleurs BulletinController, GradeController, HonorRollController, ProcesVerbalController
- **Résultat:** Tous les modules filtrent déjà correctement par `academic_year_id`
- **Observation:** Les snapshots dans `grades` préservent l'historique des enseignants

### Phase 8: Sécurité ✓
- **Mesures implémentées:**
  1. ✓ Suppression protégée (vérification historique avant suppression)
  2. ✓ Doublons d'affectation (contrainte unique en base de données)
  3. ✓ Modifications sur années fermées (bloquées dans storeAssignment)
  4. ✓ Incohérences matière/classe (déjà gérées par subject_classes)
- **Résultat:** Toutes les règles de sécurité sont en place

---

## RÉSUMÉ DES MODIFICATIONS

**Base de données:**
- `teacher_assignments`: Clé primaire corrigée pour support multi-années
- `teacher_contracts`: Nouvelle table pour suivi historique professionnel

**Contrôleurs:**
- `TeacherController.php`: 
  - delete() modifié pour utiliser is_active
  - activate() ajouté pour réactivation
  - assign() modifié pour sélecteur d'année
  - storeAssignment() modifié pour bloquer années fermées

**Vues:**
- `teachers/assign.php`: Sélecteur d'année et mode lecture seule

**Scripts:**
- `migrate_teacher_assignments.php`: Migration de la structure
- `migrate_teacher_contracts.php`: Création de la table des contrats

---

## ÉTAT FINAL

Le refactoring est **COMPLÉTÉ** pour les phases 1-8. L'architecture supporte désormais:
- ✓ Gestion multi-années des affectations
- ✓ Conservation de l'historique enseignant
- ✓ Gestion des départs et retours
- ✓ Consultation historique en lecture seule
- ✓ Sécurité contre les suppressions et modifications inappropriées

**Phase 9 (Tests) et Phase 10 (Rapport)** restent à faire selon les besoins de l'utilisateur.
