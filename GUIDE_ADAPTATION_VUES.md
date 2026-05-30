# 📱 GUIDE D'ADAPTATION DES VUES - Affichage des Notes Orphelines

## 🎯 Objectif

Adapter les vues pour afficher correctement les notes quand `teacher_id = NULL`.

---

## 1️⃣ Vue : `grades/index.php`

**Objectif :** Afficher les notes dans le registre principal

### Code à adapter

#### Avant (Actuellement)
```php
<?php foreach ($recentGrades as $grade): ?>
    <tr>
        <td><?php echo htmlspecialchars($grade['student_nom'] . ' ' . $grade['student_prenom']); ?></td>
        <td><?php echo htmlspecialchars($grade['teacher_nom'] . ' ' . $grade['teacher_prenom']); ?></td>
        <td><?php echo htmlspecialchars($grade['subject_nom']); ?></td>
        <td><?php echo number_format((float) $grade['valeur'], 2); ?>/20</td>
    </tr>
<?php endforeach; ?>
```

#### Après (À implémenter)
```php
<?php foreach ($recentGrades as $grade): ?>
    <tr class="<?php echo $grade['teacher_id'] === null ? 'orphaned-grade' : ''; ?>">
        <td><?php echo htmlspecialchars($grade['student_nom'] . ' ' . $grade['student_prenom']); ?></td>
        <td>
            <?php if ($grade['teacher_id'] !== null): ?>
                <!-- Enseignant actif -->
                <?php echo htmlspecialchars($grade['teacher_nom'] . ' ' . $grade['teacher_prenom']); ?>
            <?php else: ?>
                <!-- Enseignant supprimé -->
                <span class="text-muted">
                    <?php echo htmlspecialchars(
                        ($grade['teacher_prenom_snapshot'] ?? '') . ' ' . 
                        ($grade['teacher_nom_snapshot'] ?? 'Enseignant indéterminé')
                    ); ?>
                </span>
                <span class="badge badge-warning">Supprimé</span>
            <?php endif; ?>
        </td>
        <td><?php echo htmlspecialchars($grade['subject_nom'] ?? $grade['subject_nom_snapshot']); ?></td>
        <td><?php echo number_format((float) $grade['valeur'], 2); ?>/20</td>
        <td>
            <span class="text-muted">
                <?php if ($grade['created_by_type'] === 'admin'): ?>
                    <i class="fas fa-shield-alt"></i> Admin
                <?php else: ?>
                    <i class="fas fa-chalkboard-user"></i> Enseignant
                <?php endif; ?>
            </span>
        </td>
    </tr>
<?php endforeach; ?>
```

### CSS à ajouter (dans `style.css`)

```css
/* Notes orphelines (enseignant supprimé) */
tr.orphaned-grade {
    background-color: #fff3cd; /* Jaune pâle */
    opacity: 0.9;
}

tr.orphaned-grade:hover {
    background-color: #ffe69c;
}

tr.orphaned-grade td {
    border-left: 4px solid #ffc107;
}

/* Badge de suppression */
.badge-warning {
    background-color: #ffc107;
    color: #000;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    margin-left: 0.5rem;
}

/* Texte d'enseignant supprimé */
.text-muted {
    color: #6c757d;
    font-style: italic;
}
```

---

## 2️⃣ Vue : `grades/saisie.php`

**Objectif :** Interface de saisie des notes

### Code à adapter

#### Avant
```php
<div class="form-group">
    <label>Enseignant affecté :</label>
    <p><?php echo htmlspecialchars($assignment['teacher_nom'] . ' ' . $assignment['teacher_prenom']); ?></p>
</div>
```

#### Après
```php
<div class="form-group">
    <label>Enseignant affecté :</label>
    <p>
        <?php echo htmlspecialchars($assignment['teacher_nom'] . ' ' . $assignment['teacher_prenom']); ?>
        <!-- Info temporelle -->
        <small class="text-muted">
            Saisi le <?php echo date('d/m/Y H:i', strtotime($grade['created_at'])); ?>
        </small>
    </p>
</div>

<!-- Avertissement si notes orphelines détectées -->
<?php if (strpos($sql, 'WHERE') !== false && 
          ($stats['orphaned_count'] ?? 0) > 0): ?>
    <div class="alert alert-warning">
        <strong>ℹ️ Informations :</strong>
        Cette classe contient <?php echo $stats['orphaned_count']; ?> note(s) d'enseignants supprimés.
        Ces notes sont conservées dans le système pour l'audit.
    </div>
<?php endif; ?>
```

---

## 3️⃣ Vue : `grades/export.php` (Export PDF)

**Objectif :** Exporter correctement les notes, y compris les orphelines

### Code à adapter

#### Avant
```php
<?php foreach ($recentGrades as $grade): ?>
    <tr>
        <td><?php echo htmlspecialchars($grade['teacher_prenom'] . ' ' . $grade['teacher_nom']); ?></td>
    </tr>
<?php endforeach; ?>
```

#### Après
```php
<?php foreach ($recentGrades as $grade): ?>
    <tr>
        <td>
            <?php 
            // Utiliser l'enseignant actif ou le snapshot
            if ($grade['teacher_id'] !== null) {
                $displayName = htmlspecialchars(
                    $grade['teacher_prenom'] . ' ' . $grade['teacher_nom']
                );
            } else {
                $displayName = htmlspecialchars(
                    ($grade['teacher_prenom_snapshot'] ?? '') . ' ' . 
                    ($grade['teacher_nom_snapshot'] ?? 'Enseignant supprimé')
                );
                $displayName .= ' (Supprimé)'; // Indicateur visuel
            }
            echo $displayName;
            ?>
        </td>
        <td>
            <?php echo htmlspecialchars($grade['subject_nom'] ?? $grade['subject_nom_snapshot']); ?>
        </td>
        <!-- Autres colonnes... -->
    </tr>
<?php endforeach; ?>
```

---

## 4️⃣ Vue : `dashboard/index.php`

**Objectif :** Afficher les statistiques et avertissements sur le dashboard

### Code à ajouter

```php
<!-- Section: Alertes de notes orphelines -->
<?php
$orphanedCount = (int) $db->query(
    "SELECT COUNT(*) FROM grades WHERE teacher_id IS NULL"
)->fetchColumn();

if ($orphanedCount > 0): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <strong>📊 Notes orphelines détectées :</strong>
        <?php echo $orphanedCount; ?> note(s) d'enseignants supprimés sont conservées 
        en base pour l'audit. 
        <a href="/notes?show=orphaned" class="alert-link">Voir les détails</a>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Tableau: Synthèse des notes orphelines -->
<?php
$orphanedSummary = $db->query(
    "SELECT teacher_nom_snapshot, COUNT(*) as count
     FROM grades WHERE teacher_id IS NULL
     GROUP BY teacher_nom_snapshot
     ORDER BY count DESC"
)->fetchAll(PDO::FETCH_ASSOC);

if ($orphanedSummary): ?>
    <div class="card">
        <div class="card-header">
            <h5>Notes par Enseignants Supprimés</h5>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Enseignant (Supprimé)</th>
                        <th>Nombre de Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orphanedSummary as $row): ?>
                        <tr>
                            <td>
                                <span class="badge badge-warning">Supprimé</span>
                                <?php echo htmlspecialchars($row['teacher_nom_snapshot']); ?>
                            </td>
                            <td><?php echo $row['count']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
```

---

## 5️⃣ Vue : `grades/templates/export_pdf_file_report.php`

**Objectif :** Export fiche de collecte de notes

### Code à adapter pour le PDF

```php
<!-- En-tête du rapport -->
<h3>Fiche de Collecte des Notes</h3>
<p>
    <strong>Enseignant :</strong> 
    <?php 
    $teacherDisplay = htmlspecialchars(
        Session::get('user_prenom') . ' ' . Session::get('user_nom')
    );
    echo $teacherDisplay;
    ?>
</p>

<!-- Notes existantes (pour mémoire) -->
<?php if ($existingGrades = getExistingGrades()): ?>
    <div class="alert alert-info">
        <h5>Notes Existantes</h5>
        <p>Pour référence, voici les notes déjà saisies :</p>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Élève</th>
                    <th>Note Actuelle</th>
                    <th>Saisie par</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($existingGrades as $grade): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($grade['student_nom']); ?></td>
                        <td><?php echo $grade['valeur'] ?? '-'; ?>/20</td>
                        <td>
                            <?php 
                            if ($grade['teacher_id'] !== null) {
                                echo htmlspecialchars($grade['teacher_prenom'] . ' ' . $grade['teacher_nom']);
                            } else {
                                echo htmlspecialchars($grade['teacher_prenom_snapshot'] . ' ' . $grade['teacher_nom_snapshot']) 
                                   . ' (Supprimé)';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
```

---

## 🎨 Styles Globaux

Ajouter au fichier `public/css/style.css` :

```css
/* ═════════════════════════════════════════ */
/* Notes Orphelines & Enseignants Supprimés */
/* ═════════════════════════════════════════ */

/* Ligne d'une note orpheline */
.orphaned-grade {
    background-color: #fff3cd !important;
    border-left: 4px solid #ffc107 !important;
}

.orphaned-grade:hover {
    background-color: #ffe69c !important;
}

/* Badge d'état */
.teacher-status {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.teacher-status.active {
    color: #28a745;
}

.teacher-status.deleted {
    color: #dc3545;
    text-decoration: line-through;
    opacity: 0.7;
}

/* Avertissements et informations */
.alert-orphaned-grades {
    background-color: #e8f4f8;
    border-left: 4px solid #17a2b8;
}

.orphaned-count {
    font-weight: bold;
    color: #dc3545;
}

/* Tableau des orphelines */
.table-orphaned-grades tbody tr {
    opacity: 0.8;
}

.table-orphaned-grades tbody tr:hover {
    opacity: 1;
}

/* Indicateur de suppression */
.deleted-teacher {
    font-style: italic;
    color: #6c757d;
}

.deleted-teacher::after {
    content: " (supprimé)";
    color: #dc3545;
    font-weight: bold;
}

/* Icônes */
.icon-teacher {
    margin-right: 0.5rem;
}

.icon-admin {
    margin-right: 0.5rem;
}
```

---

## 🔍 Filtrage des Notes Orphelines

### Vue : Filtre dans `grades/index.php`

```php
<!-- Filtre pour afficher/masquer les notes orphelines -->
<div class="form-group">
    <label for="filter-orphaned">Affichage :</label>
    <select id="filter-orphaned" class="form-control" name="show_orphaned">
        <option value="all">Toutes les notes</option>
        <option value="active" selected>Enseignants actifs seulement</option>
        <option value="orphaned">Enseignants supprimés seulement</option>
    </select>
</div>

<script>
document.getElementById('filter-orphaned').addEventListener('change', function(e) {
    const filter = e.target.value;
    const rows = document.querySelectorAll('table tbody tr');
    
    rows.forEach(row => {
        const isOrphaned = row.classList.contains('orphaned-grade');
        
        if (filter === 'all') {
            row.style.display = '';
        } else if (filter === 'active' && !isOrphaned) {
            row.style.display = '';
        } else if (filter === 'orphaned' && isOrphaned) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
```

---

## ✅ Checklist d'Adaptation

- [ ] `grades/index.php` - Affichage des notes adapté
- [ ] `grades/saisie.php` - Avertissements ajoutés
- [ ] `grades/export.php` - Export PDF adapté
- [ ] `dashboard/index.php` - Statistiques orphelines
- [ ] `style.css` - Styles pour notes orphelines
- [ ] `public/css/alerts-premium.css` - Badges et alertes
- [ ] Tests visuels en développement
- [ ] Test d'impression PDF
- [ ] Déploiement en production

---

## 📞 Support

Pour toute question sur l'adaptation des vues :
1. Voir la documentation : `MIGRATION_NOTES_PROTECTION.md`
2. Exécuter les tests : `php scripts/test_teacher_deletion.php`
3. Vérifier la base : `php diagnostic_db.php`

---

**Dernier mise à jour :** 30 mai 2026
**Statut :** Guide d'adaptation - À implémenter
