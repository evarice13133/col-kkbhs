<?php
/**
 * FICHE DE REPORT DE NOTES - CLASSE
 * Corps du document uniquement (en-tête fourni)
 */

// Données attendues
$students = $students ?? [];
$matieres = $matieres ?? [];
$className = $className ?? '';
$evaluation = $evaluation ?? '';
$anneeScolaire = $anneeScolaire ?? '';

// Fonction d'appréciation
function getAppreciation(float $moyenne): string {
    if ($moyenne >= 16) return 'Excellent';
    if ($moyenne >= 14) return 'Très bien';
    if ($moyenne >= 12) return 'Bien';
    if ($moyenne >= 10) return 'Assez bien';
    return 'Insuffisant';
}

// Calcul des moyennes et classement
$processedStudents = [];
foreach ($students as $student) {
    $totalNotes = 0;
    $totalCoefs = 0;
    foreach ($matieres as $mat) {
        $note = $student['notes'][$mat['code']] ?? null;
        $coef = $mat['coefficient'] ?? 1;
        if ($note !== null) {
            $totalNotes += (float) $note * $coef;
            $totalCoefs += $coef;
        }
    }
    $moyenne = $totalCoefs > 0 ? round($totalNotes / $totalCoefs, 2) : 0;
    
    $processedStudents[] = [
        'nom' => $student['nom'] ?? '',
        'prenom' => $student['prenom'] ?? '',
        'notes' => $student['notes'] ?? [],
        'moyenne' => $moyenne,
        'appreciation' => getAppreciation($moyenne)
    ];
}

// Tri par ordre décroissant de moyenne
usort($processedStudents, fn($a, $b) => $b['moyenne'] <=> $a['moyenne']);

// Attribution des rangs
foreach ($processedStudents as $index => &$student) {
    $student['rang'] = $index + 1;
}
unset($student);

// Statistiques de la classe
$moyennes = array_column($processedStudents, 'moyenne');
$classAverage = count($moyennes) > 0 ? round(array_sum($moyennes) / count($moyennes), 2) : 0;
$bestAverage = count($moyennes) > 0 ? max($moyennes) : 0;
$worstAverage = count($moyennes) > 0 ? min($moyennes) : 0;

// Orientation paysage si beaucoup de matières
$pageOrientation = count($matieres) > 5 ? 'landscape' : 'portrait';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars((string) __('lang')) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche de Report - <?= htmlspecialchars((string) $className) ?></title>
    <style>
        @page { size: A4 <?= $pageOrientation ?>; margin: 10mm 8mm 15mm 8mm; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; font-size: 9px; color: #111; background: #fff; }
        
        /* Barre d'outils */
        @media print { .toolbar { display: none !important; } }
        .toolbar { position: sticky; top: 0; z-index: 100; display: flex; align-items: center; justify-content: space-between; padding: 10px 15px; background: #1a1a2e; color: white; gap: 10px; margin-bottom: 15px; }
        .toolbar-title { font-weight: bold; font-size: 13px; }
        .btn { padding: 6px 12px; border: none; border-radius: 3px; cursor: pointer; font-size: 11px; font-weight: bold; text-decoration: none; }
        .btn-print { background: #0d6efd; color: white; }
        .btn-back { background: rgba(255,255,255,0.2); color: white; }
        
        /* Titre */
        .report-title { text-align: center; font-size: 14px; font-weight: bold; text-transform: uppercase; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 2px solid #333; }
        .report-meta { text-align: center; font-size: 10px; color: #555; margin-bottom: 12px; }
        
        /* Tableau principal */
        table { width: 100%; border-collapse: collapse; font-size: 8px; }
        th { background: #2c3e50; color: white; padding: 5px 3px; text-align: center; font-weight: bold; border: 1px solid #1a252f; white-space: nowrap; }
        td { border: 1px solid #bdc3c7; padding: 4px 3px; text-align: center; }
        tr:nth-child(even) { background: #f8f9fa; }
        
        /* Colonnes spécifiques */
        .col-rank { width: 25px; font-weight: bold; background: #ecf0f1; }
        .col-name { text-align: left; font-weight: 500; min-width: 120px; }
        .col-mat { width: 35px; }
        .col-avg { width: 40px; font-weight: bold; background: #e8f4f8; }
        .col-rang { width: 30px; font-weight: bold; color: #c0392b; }
        .col-app { width: 70px; font-size: 7px; }
        
        /* Appréciations couleurs */
        .app-excellent { color: #27ae60; font-weight: bold; }
        .app-tresbien { color: #2980b9; font-weight: bold; }
        .app-bien { color: #8e44ad; }
        .app-assezbien { color: #f39c12; }
        .app-insuffisant { color: #e74c3c; font-weight: bold; }
        
        /* Résumé */
        .summary { margin-top: 15px; padding: 10px; background: #ecf0f1; border-radius: 4px; border-left: 4px solid #3498db; }
        .summary-title { font-weight: bold; font-size: 10px; margin-bottom: 6px; text-transform: uppercase; }
        .summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .summary-item { text-align: center; }
        .summary-label { font-size: 8px; color: #555; text-transform: uppercase; }
        .summary-value { font-size: 12px; font-weight: bold; color: #2c3e50; }
        
        /* Footer */
        .footer { position: fixed; left: 8mm; right: 8mm; bottom: 5mm; display: flex; justify-content: space-between; font-size: 8px; color: #7f8c8d; }
        
        /* Sauts de page - chaque fiche sur une page */
        .report-container { page-break-after: always; }
        .report-container:last-child { page-break-after: auto; }
        @media print {
            body { margin: 0; }
            .report-container { 
                height: 100vh; 
                page-break-inside: avoid; 
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <!-- Barre d'outils (hors page) -->
    <div class="toolbar">
        <div class="toolbar-title"> Fiche de Report - <?= htmlspecialchars((string) $className) ?></div>
        <div>
            <a href="javascript:history.back()" class="btn btn-back">&larr; Retour</a>
            <button class="btn btn-print" onclick="window.print()">Imprimer</button>
        </div>
    </div>

    <!-- Conteneur fiche (une seule page) -->
    <div class="report-container">
        <!-- Titre -->
        <div class="report-title">Fiche de Report des Notes</div>
        <div class="report-meta">
            <?= htmlspecialchars((string) $className) ?> | <?= htmlspecialchars((string) $evaluation) ?> | <?= htmlspecialchars((string) $anneeScolaire) ?>
        </div>

        <!-- Tableau de report -->
        <table>
            <thead>
                <tr>
                    <th class="col-rank">N°</th>
                    <th class="col-name">Nom et Prénom</th>
                    <?php foreach ($matieres as $mat): ?>
                        <th class="col-mat" title="<?= htmlspecialchars((string) $mat['nom']) ?>">
                            <?= htmlspecialchars((string) ($mat['code'] ?? substr($mat['nom'], 0, 3))) ?>
                        </th>
                    <?php endforeach; ?>
                    <th class="col-avg">Moy.</th>
                    <th class="col-rang">Rang</th>
                    <th class="col-app">Appréciation</th>
                </tr>
            </thead>
        <tbody>
            <?php foreach ($processedStudents as $student): ?>
                <?php
                $appClass = match($student['appreciation']) {
                    'Excellent' => 'app-excellent',
                    'Très bien' => 'app-tresbien',
                    'Bien' => 'app-bien',
                    'Assez bien' => 'app-assezbien',
                    default => 'app-insuffisant'
                };
                ?>
                <tr>
                    <td class="col-rank"><?= (int) $student['rang'] ?></td>
                    <td class="col-name"><?= htmlspecialchars(strtoupper($student['nom']) . ' ' . $student['prenom']) ?></td>
                    <?php foreach ($matieres as $mat): 
                        $note = $student['notes'][$mat['code']] ?? '-';
                        $noteVal = is_numeric($note) ? (float) $note : null;
                        $noteStyle = '';
                        if ($noteVal !== null) {
                            if ($noteVal < 10) $noteStyle = 'color: #e74c3c; font-weight: bold;';
                            elseif ($noteVal >= 16) $noteStyle = 'color: #27ae60; font-weight: bold;';
                        }
                    ?>
                        <td class="col-mat" style="<?= $noteStyle ?>"><?= htmlspecialchars((string) $note) ?></td>
                    <?php endforeach; ?>
                    <td class="col-avg"><?= number_format($student['moyenne'], 2) ?></td>
                    <td class="col-rang"><?= (int) $student['rang'] ?>e</td>
                    <td class="col-app <?= $appClass ?>"><?= htmlspecialchars($student['appreciation']) ?></td>
                </tr>
            <?php endforeach; ?>
            
            <?php if (empty($processedStudents)): ?>
                <tr>
                    <td colspan="<?= 4 + count($matieres) ?>" class="empty">Aucun élève trouvé</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Résumé -->
    <div class="summary">
        <div class="summary-title">Résumé de la classe</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Moyenne générale</div>
                <div class="summary-value"><?= number_format($classAverage, 2) ?>/20</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Meilleure moyenne</div>
                <div class="summary-value"><?= number_format($bestAverage, 2) ?>/20</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Moyenne la plus faible</div>
                <div class="summary-value"><?= number_format($worstAverage, 2) ?>/20</div>
            </div>
        </div>
    </div>
    </div><!-- /report-container -->

    <div class="footer">
        <div>Fiche de report générée automatiquement</div>
        <div><?= count($processedStudents) ?> élève(s)</div>
    </div>
</body>
</html>
