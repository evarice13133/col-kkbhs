<?php
/**
 * FICHE DE LISTE - DYNAMIQUE (ÉLÈVES OU ENSEIGNANTS)
 * Paramètre $type = 'students' ou 'teachers'
 * Paramètre $hideToolbar = true pour impression batch
 * Paramètre $embeddedMode = true pour mode intégré (sans html/head/body)
 */
$hideToolbar = $hideToolbar ?? false;
$embeddedMode = $embeddedMode ?? false;
$type = $type ?? 'students'; // 'students' ou 'teachers'

$institution = $institution ?? [];
$activeYear = $activeYear ?? [];
$classInfo = $classInfo ?? [];
$items = $items ?? []; // students ou teachers
$title = $title ?? 'LISTE';
$i = $institution;

if (!$embeddedMode):
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?> - <?= htmlspecialchars($classInfo['nom'] ?? '') ?></title>
    <style>
        * { box-sizing: border-box; }
        @page { size: A4 portrait; margin: 1cm; }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            margin: 0; 
            padding: 0; 
            color: #333; 
            background: #fff;
            line-height: 1.4;
        }
        .container {
            padding: 1cm;
            min-height: 277mm;
        }
        
        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        .logo-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-img { width: 70px; height: 70px; object-fit: contain; }
        .header-content { text-align: right; }
        .school-name { 
            font-size: 16px; 
            font-weight: bold; 
            margin: 0;
            text-transform: uppercase;
        }
        .school-meta { font-size: 10px; color: #666; margin-top: 5px; }

        /* Title */
        .title {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #f5f5f5;
            border: 1px solid #ddd;
        }
        .title h1 {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }
        .title p {
            font-size: 12px;
            color: #666;
            margin: 5px 0 0 0;
        }

        /* Table */
        .table-wrapper {
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }
        table { width: 100%; border-collapse: collapse; }
        th { 
            background: #f5f5f5; 
            color: #333; 
            font-weight: bold; 
            text-transform: uppercase; 
            font-size: 10px; 
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid #333;
        }
        td { 
            padding: 8px 10px; 
            border-bottom: 1px solid #ddd;
            font-size: 11px;
            vertical-align: middle;
        }
        tr:nth-child(even) { background-color: #f9f9f9; }
        
        .checkbox-cell {
            width: 40px;
            text-align: center;
        }
        .checkbox {
            width: 16px;
            height: 16px;
            border: 1px solid #333;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .sig-box {
            border: 1px dashed #999;
            padding: 15px;
            text-align: center;
        }
        .sig-title { font-weight: bold; font-size: 10px; text-transform: uppercase; color: #333; margin-bottom: 40px; display: block; }
        .sig-line { border-top: 1px solid #333; width: 100px; margin: 0 auto; }

        /* No Print Toolbar */
        @media print { .no-print { display: none !important; } }
        .no-print {
            position: sticky; top: 0; z-index: 10000; display: flex; align-items: center; justify-content: space-between;
            padding: 10px 20px; background: #333; color: white;
        }
        .btn { 
            padding: 8px 16px; cursor: pointer; font-size: 12px; font-weight: bold; 
            text-decoration: none; color: white; border: none;
            display: inline-flex; align-items: center; gap: 5px;
        }
        .btn-print { background: #1a73e8; }
        .btn-back { background: #666; }
    </style>
</head>
<body>
    <?php if (!$hideToolbar): ?>
    <div class="no-print">
        <div style="font-weight: bold;">NOTESMASTER</div>
        <div style="display: flex; gap: 10px;">
            <a href="/fiches" class="btn btn-back">Retour</a>
            <button class="btn btn-print" onclick="window.print()">Imprimer</button>
        </div>
    </div>
    <?php endif; ?>

    <div class="container">
<?php endif; ?>

        <div class="header">
            <div class="logo-wrapper">
                <?php if (!empty($i['school_logo_base64'])): ?>
                    <img src="<?= $i['school_logo_base64'] ?>" class="logo-img" alt="Logo">
                <?php endif; ?>
            </div>
            <div class="header-content">
                <h2 class="school-name"><?= htmlspecialchars($i['school_name'] ?? $i['school_code'] ?? '') ?></h2>
                <div class="school-meta">
                    <?= htmlspecialchars($i['school_city'] ?? '') ?> • <?= htmlspecialchars($i['school_phone'] ?? '') ?><br>
                    Année Scolaire <?= htmlspecialchars($activeYear['nom'] ?? '') ?>
                </div>
            </div>
        </div>

        <div class="title">
            <h1><?= $title ?></h1>
            <p><?= htmlspecialchars($classInfo['nom'] ?? '') ?> • Effectif: <?= count($items) ?></p>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">N°</th>
                        <?php if ($type === 'students'): ?>
                            <th>Nom & Prénoms de l'élève</th>
                            <th style="width: 100px;">Matricule</th>
                            <th class="checkbox-cell">Présence</th>
                            <th class="checkbox-cell">Absence</th>
                            <th class="checkbox-cell">Retard</th>
                        <?php else: ?>
                            <th>Nom & Prénoms de l'enseignant</th>
                            <th style="width: 100px;">Username</th>
                            <th style="width: 120px;">Matières</th>
                            <th style="width: 120px;">Classes</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="<?= $type === 'students' ? 6 : 5 ?>" style="text-align: center; padding: 30px; color: #999; font-weight: bold;">
                                Aucun élément
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $n = 1; foreach ($items as $item): ?>
                            <tr>
                                <td style="text-align: center; font-weight: bold;"><?= $n++ ?></td>
                                <?php if ($type === 'students'): ?>
                                    <td>
                                        <strong><?= htmlspecialchars($item['nom'] ?? '') ?></strong> <?= htmlspecialchars($item['prenom'] ?? '') ?>
                                    </td>
                                    <td><?= htmlspecialchars($item['matricule'] ?? 'N/A') ?></td>
                                    <td class="checkbox-cell"><input type="checkbox" class="checkbox"></td>
                                    <td class="checkbox-cell"><input type="checkbox" class="checkbox"></td>
                                    <td class="checkbox-cell"><input type="checkbox" class="checkbox"></td>
                                <?php else: ?>
                                    <td>
                                        <strong><?= htmlspecialchars($item['nom'] ?? '') ?></strong> <?= htmlspecialchars($item['prenom'] ?? '') ?>
                                    </td>
                                    <td><?= htmlspecialchars($item['username'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($item['subjects_count'] ?? 0) ?> matière(s)</td>
                                    <td><?= htmlspecialchars($item['classes_list'] ?? '-') ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="footer">
            <div class="sig-box">
                <span class="sig-title"><?= $type === 'students' ? 'Signature du Professeur' : 'Signature du Directeur' ?></span>
                <div class="sig-line"></div>
            </div>
            <div class="sig-box">
                <span class="sig-title">Signature du Chef d'Établissement</span>
                <div class="sig-line"></div>
            </div>
        </div>

        <div style="margin-top: 20px; text-align: center; font-size: 9px; color: #999; text-transform: uppercase; letter-spacing: 1px; font-weight: bold;">
            Généré par NotesMaster
        </div>

<?php if (!$embeddedMode): ?>
    </div>
</body>
</html>
<?php endif; ?>