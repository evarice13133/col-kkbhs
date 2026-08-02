# Documentation des Permissions & Matrice RBAC NoteMaster

## 1. Dictionnaire Exhaustif des Permissions

| Code Permission | Libellé | Module | Description | Rôles qui la possèdent par défaut |
|---|---|---|---|---|
| `manage_users` | Gérer les utilisateurs | Administration | Créer, modifier, activer/désactiver et supprimer des utilisateurs. | `superadmin`, `admin`, `it_manager` |
| `manage_settings` | Gérer les paramètres | Administration | Configurer l'identité de l'école, logos et paramètres globaux. | `superadmin`, `admin` |
| `view_system_logs` | Consulter les journaux | Administration | Visualiser les logs d'audit et d'activité système. | `superadmin`, `admin`, `it_manager` |
| `manage_rbac` | Gérer la sécurité RBAC | Administration | Configurer les rôles et affecter des permissions. | `superadmin` |
| `view_classes` | Consulter les classes | Pédagogie | Visualiser la liste des classes et leurs effectifs. | `superadmin`, `admin`, `it_manager`, `comptable`, `caissier`, `enseignant` |
| `manage_classes_structure` | Gérer la structure des classes | Pédagogie | Créer, modifier la structure des niveaux et classes. | `superadmin`, `admin`, `it_manager` |
| `manage_teaching_types` | Gérer les types d'enseignement | Structure | Créer et configurer les types d'enseignement. | `superadmin`, `admin`, `it_manager` |
| `manage_cycles` | Gérer les cycles | Structure | Créer et modifier les cycles académiques. | `superadmin`, `admin`, `it_manager` |
| `manage_sections` | Gérer les sections | Structure | Créer et modifier les sections d'études. | `superadmin`, `admin`, `it_manager` |
| `manage_departments` | Gérer les départements | Structure | Créer et modifier les départements d'enseignement. | `superadmin`, `admin`, `it_manager` |
| `manage_subjects` | Gérer les matières | Pédagogie | Gérer le catalogue des matières, groupes (UE) et coefficients. | `superadmin`, `admin`, `it_manager` |
| `manage_teachers` | Gérer les enseignants | RH | Gérer le registre des enseignants et leurs affectations. | `superadmin`, `admin`, `it_manager` |
| `manage_timetables` | Gérer les emplois du temps | Pédagogie | Établir et modifier les emplois du temps. | `superadmin`, `admin`, `it_manager` |
| `manage_academic_years` | Gérer les années scolaires | Structure | Créer, activer, clôturer et archiver des années académiques. | `superadmin`, `it_manager` |
| `manage_sequences` | Gérer les séquences | Évaluations | Configurer les évaluations, séquences et périodes. | `superadmin`, `admin`, `it_manager` |
| `view_students` | Consulter les élèves | Élèves | Visualiser les registres et fiches des élèves. | `superadmin`, `admin`, `it_manager`, `comptable`, `caissier`, `enseignant` |
| `manage_students` | Gérer les élèves | Élèves | Inscrire, modifier les profils, réintégrer ou retirer des élèves. | `superadmin`, `admin`, `it_manager`, `comptable`, `caissier` |
| `manage_marks` | Gérer les notes | Évaluations | Saisir, importer et modifier des notes d'évaluation. | `superadmin`, `admin`, `it_manager`, `enseignant` |
| `manage_bulletins` | Gérer les bulletins et PV | Éditions | Calculer les moyennes, générer des bulletins, PV et diplômes. | `superadmin`, `admin`, `it_manager` |
| `manage_absences` | Gérer les absences | Discipline | Saisir et suivre les absences et la discipline. | `superadmin`, `admin`, `it_manager` |
| `manage_staff` | Gérer le personnel | RH | Gérer les fiches du personnel et employés. | `superadmin`, `admin`, `it_manager`, `comptable` |
| `manage_contracts` | Gérer les contrats | RH | Établir et suivre les contrats de travail du personnel. | `superadmin`, `admin`, `it_manager`, `comptable` |
| `manage_fees` | Gérer la scolarité | Finances | Accéder aux modules de scolarité et paramétrage des frais. | `superadmin`, `admin`, `comptable`, `caissier` |
| `view_class_finances` | Consulter les finances de classe | Finances | Voir les grilles tarifaires et tranches d'une classe. | `superadmin`, `admin`, `comptable`, `caissier` |
| `edit_class_finances` | Modifier les finances de classe | Finances | Définir la scolarité et les tranches tarifaires d'une classe. | `superadmin`, `admin`, `comptable` |
| `manage_payments` | Gérer les paiements et reçus | Finances | Enregistrer les versements et imprimer des reçus de caisse. | `superadmin`, `admin`, `comptable`, `caissier` |
| `manage_discounts` | Gérer les réductions | Finances | Attribuer et paramétrer les réductions de scolarité. | `superadmin`, `admin`, `comptable`, `caissier` |
| `manage_scholarships` | Gérer les bourses | Finances | Attribuer et paramétrer les bourses scolaires. | `superadmin`, `admin`, `comptable`, `caissier` |
| `view_financial_history` | Consulter l'historique financier | Finances | Consulter le journal d'audit financier et l'historique comptable. | `superadmin`, `admin`, `comptable`, `caissier` |
| `view_financial_reports` | Consulter les rapports financiers | Finances | Consulter les statistiques financières et la liste des insolvables. | `superadmin`, `admin`, `comptable`, `caissier` |
| `view_transcripts` | Consulter les relevés de notes | Éditions | Visualiser et prévisualiser les relevés de notes des élèves. | `superadmin`, `admin`, `it_manager` |
| `manage_transcripts` | Gérer les relevés de notes | Éditions | Générer, exporter en PDF et imprimer les relevés de notes. | `superadmin`, `admin` |

---

## 2. Matrice Rôles vs Permissions

| Permission Code | Superadmin | Admin | IT Manager | Comptable | Caissier | Enseignant |
|---|---|---|---|---|---|---|
| `manage_users` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| `manage_settings` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| `view_system_logs` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| `manage_rbac` | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `view_classes` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `manage_classes_structure` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| `manage_teaching_types` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| `manage_cycles` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| `manage_sections` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| `manage_departments` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| `manage_subjects` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| `manage_teachers` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| `manage_timetables` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| `manage_academic_years` | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| `manage_sequences` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| `view_students` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `manage_students` | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| `manage_marks` | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ |
| `manage_bulletins` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| `manage_absences` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| `manage_staff` | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| `manage_contracts` | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| `manage_fees` | ✅ | ✅ | ❌ | ✅ | ✅ | ❌ |
| `view_class_finances` | ✅ | ✅ | ❌ | ✅ | ✅ | ❌ |
| `edit_class_finances` | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| `manage_payments` | ✅ | ✅ | ❌ | ✅ | ✅ | ❌ |
| `manage_discounts` | ✅ | ✅ | ❌ | ✅ | ✅ | ❌ |
| `manage_scholarships` | ✅ | ✅ | ❌ | ✅ | ✅ | ❌ |
| `view_financial_history` | ✅ | ✅ | ❌ | ✅ | ✅ | ❌ |
| `view_financial_reports` | ✅ | ✅ | ❌ | ✅ | ✅ | ❌ |
| `view_transcripts` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| `manage_transcripts` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |

---

## 3. Procédure d'Ajout d'une Nouvelle Permission

1. Insérer la nouvelle permission dans la table `permissions` via un script de migration dans `scripts/` (ex: `scripts/migration_add_xxx_rbac.php`).
2. Associer la permission aux rôles concernés dans la table `role_permissions`.
3. Ajouter le contrôle dans le contrôleur PHP (`PermissionManager::requirePermission('nouveau_code')`).
4. Conditionner l'élément UI correspondant avec `PermissionManager::hasPermission('nouveau_code')`.
5. Mettre à jour cette documentation (`docs/documentation_permissions.md`).
