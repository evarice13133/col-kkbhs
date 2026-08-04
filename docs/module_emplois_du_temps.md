# Documentation Fonctionnelle et Technique : Module de Gestion des Emplois du Temps

**Projet** : NoteMaster / Futura.Camertech  
**Version** : 1.0.0  
**Stack** : PHP 8.3, MVC, RBAC, PDO, Clean Architecture, Dompdf, Bootstrap 5 / Canva-Inspired UI  

---

## 1. Spécifications Fonctionnelles Complètes

Le module de **Gestion des Emplois du Temps** permet la planification globale et dynamique des cours et examens dans les établissements d'enseignement. Il est composé de 4 sous-modules clés et d'un assistant de planification intéractif en 5 étapes.

### 1.1 Sous-Module : Créneaux Horaires (`timetable_time_slots`)
- Ajout, modification et suppression des créneaux.
- Chaque créneau contient : `heure_debut`, `heure_fin`, `type_creneau` (cours/pause), `duree_minutes` (calculé automatiquement) et `ordre_affichage`.
- **Règle de séparation** : Les pauses sont physiquement identifiées avec une icône de tasse à café, un fond doré, et ne peuvent recevoir aucune affectation de cours.

### 1.2 Sous-Module : Salles de Classe (`class_rooms`)
- Gestion du parc de salles avec : `nom`, `code` unique, `capacite` d'accueil et `description`.
- **Statut dynamique calculé** : Statut calculé en temps réel (`Disponible` vs `Occupée`) lors de l'interrogation d'une semaine et d'un créneau donnés. Aucune saisie manuelle autorisée pour le statut.

### 1.3 Sous-Module : Semaines de Cours (`timetable_weeks`)
- Gestion des périodes de cours rattachées aux années académiques.
- **Formulaire intelligent** : Propose automatiquement la suggestion de la prochaine semaine disponible (du Lundi au Samedi).
- Rejet des chevauchements de dates et interdiction d'avoir deux semaines avec la même date de début sur une même année académique.

### 1.4 Assistant de Création (Wizard en 5 Étapes)
- **Étape 1** : Sélection automatique du type d'enseignement actif (Par défaut **Supérieur LMD**).
- **Étape 2** : Filtrage dynamique des cycles académiques rattachés.
- **Étape 3** : Sélection de la classe parmi celles rattachées au cycle choisi.
- **Étape 4** : Sélection de la semaine de cours.
- **Étape 5** : Génération automatique de la grille Canva-style.

---

## 2. Règles Métier Détaillées & Verrouillage 168h

### 2.1 Verrouillage Automatique (168 Heures / 7 Jours)
- **Déclencheur** : Une fois que `NOW() > date_fin_semaine + 168 Heures` (7 jours révolus après le Samedi/Dimanche de la semaine concernée à 23:59:59).
- **Conséquences** :
  - Passage automatique en statut `verrouille`.
  - Désactivation complète des boutons d'ajout, de modification et de suppression pour les rôles administratifs standards (`admin`, `enseignant`, `it_manager`).
  - Bouton **Déverrouiller (Superadmin)** accessible exclusivement au rôle `superadmin`.
  - Toute action de déverrouillage nécessite un motif textuel obligatoire et est inscrite dans `timetable_audit_logs`.

### 2.2 Moteur d'Anti-Collision en Temps Réel
Validation côté serveur et via l'API Ajax `/timetables/api/validate-conflict` pour bloquer :
1. **Conflit Enseignant** : 2 cours pour le même enseignant au même créneau horaire, même jour, quelle que soit la classe.
2. **Conflit Salle** : 2 cours dans la même salle au même créneau horaire, même jour.
3. **Conflit Classe** : 2 cours pour la même classe au même créneau horaire.
4. **Affectation sur créneau de pause**.

---

## 3. Modèles de Données & Relations (ERD)

```
+------------------------+        +-----------------------+        +-------------------------+
|     academic_years     |        |    teaching_types     |        |         cycles          |
+------------------------+        +-----------------------+        +-------------------------+
| id (PK)                |<-------| id (PK)               |<-------| id (PK)                 |
| libelle                |        | nom                   |        | nom                     |
+------------------------+        +-----------------------+        | teaching_type_id (FK)   |
            ^                                                      +-------------------------+
            |                                                                   ^
            |                                                                   |
+------------------------+                                         +-------------------------+
|    timetable_weeks     |                                         |         classes         |
+------------------------+                                         +-------------------------+
| id (PK)                |                                         | id (PK)                 |
| academic_year_id (FK)  |                                         | cycle_id (FK)           |
| date_debut, date_fin   |                                         +-------------------------+
+------------------------+                                                      ^
            ^                                                                   |
            |                                                                   |
            +-----------------------------------+-------------------------------+
                                                |
                                    +-----------------------+
                                    |      timetables       |
                                    +-----------------------+
                                    | id (PK)               |
                                    | academic_year_id (FK) |
                                    | teaching_type_id (FK) |
                                    | cycle_id (FK)         |
                                    | class_id (FK)         |
                                    | week_id (FK)          |
                                    | statut, is_locked     |
                                    +-----------------------+
                                                ^
                                                |
                                    +-----------------------+
                                    |   timetable_entries   |
                                    +-----------------------+
                                    | id (PK)               |
                                    | timetable_id (FK)     |
                                    | slot_id (FK)          |
                                    | day_of_week           |
                                    | subject_id (FK)       |
                                    | teacher_id (FK)       |
                                    | room_id (FK)          |
                                    +-----------------------+
```

---

## 4. Permissions RBAC Integrées

| Permission Code | Intitulé | Description | Rôles Autorisés par Défaut |
|---|---|---|---|
| `view_timetables` | Consulter les emplois du temps | Visualisation, impression et téléchargement PDF | Superadmin, Admin, IT Manager, Enseignant |
| `manage_timetables` | Gérer les emplois du temps | Création, édition des grilles, créneaux, salles et semaines | Superadmin, Admin, IT Manager |
| `unlock_timetables` | Déverrouiller les emplois du temps | Déverrouillage d'un emploi du temps verrouillé (>168h) | Superadmin uniquement |

---

## 5. Parcours Utilisateurs (User Journeys)

1. **Création d'un nouvel emploi du temps** :
   `Emplois du Temps` -> `Nouvel Emploi du Temps (Assistant)` -> `Étape 1: Supérieur LMD` -> `Étape 2: Cycle (ex: Licence)` -> `Étape 3: Classe (ex: L3-INFO)` -> `Étape 4: Semaine 12` -> `Étape 5: Générer la grille`.
2. **Affectation d'un cours sur la grille** :
   Cliquez sur une cellule vide -> Sélection de la matière, de l'enseignant et de la salle -> Vérification Ajax anti-conflit -> Sauvegarde instantanée.
3. **Export PDF et Impression** :
   Cliquez sur `Imprimer` ou `Télécharger PDF` sur la grille pour obtenir le document paysage A4 certifié NoteMaster.

---

## 6. Scénarios de Test (QA)

| ID Test | Composant | Description du Test | Résultat Attendu |
|---|---|---|---|
| `TC-01` | Créneaux | Tentative d'ajout d'un créneau 08:30-09:30 chevauchant 08:00-09:00 | Rejet avec message UX d'erreur de chevauchement |
| `TC-02` | Salles | Affectation de la Salle 101 au même créneau pour 2 classes différentes | Blocage par `TimetableConflictService` avec message explicite |
| `TC-03` | Enseignants | Affectation du même enseignant au même créneau pour 2 cours simultanés | Rejet instantané et affichage du conflit enseignant |
| `TC-04` | Verrouillage | Simulation d'une semaine dépassée de 168 heures | Affichage du badge de verrouillage, désactivation de l'édition pour admin/enseignant |
| `TC-05` | Superadmin | Déverrouillage manuel par le Superadmin avec raison obligatoire | Succès, remise en mode éditable et inscription de l'action dans `timetable_audit_logs` |
