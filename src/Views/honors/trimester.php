<?php
$i = $institution;
$schoolDisplayName = function_exists('mb_strtoupper') ? mb_strtoupper((string) ($i['school_name'] ?? $i['school_code'] ?? ''), 'UTF-8') : strtoupper((string) ($i['school_name'] ?? $i['school_code'] ?? ''));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= __('honor_roll_title') ?> - <?= htmlspecialchars($classInfo['nom']) ?></title>
    <style>
        * { box-sizing: border-box; }
        @page { size: A4 portrait; margin: 0; }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            margin: 0; 
            padding: 0; 
            color: #333; 
            background: #fff;
            line-height: 1.4;
        }
        .honor-roll-container {
            padding: 1.5cm;
            min-height: 297mm;
        }
        
        /* Simple Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
        }
        .logo-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-img { width: 80px; height: 80px; object-fit: contain; }
        .header-content { text-align: right; }
        .school-name { 
            font-size: 18px; 
            font-weight: bold; 
            margin: 0;
            text-transform: uppercase;
        }
        .school-meta { font-size: 11px; color: #666; margin-top: 5px; }

        /* Simple Title */
        .title-section {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #f5f5f5;
            border: 1px solid #ddd;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 0;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 14px;
            color: #666;
            margin-top: 8px;
        }

        /* Simple Stats */
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #fff;
            padding: 15px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .stat-label { font-size: 11px; font-weight: bold; color: #666; text-transform: uppercase; }
        .stat-value { font-size: 24px; font-weight: bold; color: #333; display: block; margin-top: 5px; }
        .stat-value.gold { color: #b38728; }

        /* Simple Table */
        .table-wrapper {
            border: 1px solid #ddd;
        }
        table { width: 100%; border-collapse: collapse; }
        th { 
            background: #f5f5f5; 
            color: #333; 
            font-weight: bold; 
            text-transform: uppercase; 
            font-size: 11px; 
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #333;
        }
        td { 
            padding: 12px; 
            border-bottom: 1px solid #ddd;
            font-size: 12px;
            vertical-align: middle;
        }
        tr:hover td { background-color: #f9f9f9; }
        
        .rank {
            width: 28px; height: 28px;
            background: #333;
            color: #fff;
            border-radius: 4px;
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: bold;
            font-size: 12px;
        }
        .student-name { font-weight: bold; color: #333; }
        .student-meta { font-size: 10px; color: #999; margin-top: 2px; }
        .avg {
            background: #e8f0fe;
            color: #1a73e8;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 13px;
        }
        .mention {
            padding: 4px 8px;
            background: #f5f5f5;
            color: #333;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #ddd;
        }

        /* Simple Signatures */
        .signatures {
            margin-top: 50px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }
        .sig-box {
            border: 1px dashed #999;
            padding: 20px;
            text-align: center;
        }
        .sig-title { font-weight: bold; font-size: 12px; text-transform: uppercase; color: #333; margin-bottom: 50px; display: block; }
        .sig-line { border-top: 1px solid #333; width: 120px; margin: 0 auto; }

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
    <div class="no-print">
        <div style="font-weight: bold;">NOTESMASTER</div>
        <div style="display: flex; gap: 10px;">
            <a href="/honors?class_id=<?= (int) $classId ?>" class="btn btn-back"><?= __('back') ?></a>
            <button class="btn btn-print" onclick="window.print()"><?= __('pv_print_btn') ?></button>
        </div>
    </div>

    <div class="honor-roll-container">
        <div class="header">
            <div class="logo-wrapper">
                <?php if (!empty($i['school_logo_base64'])): ?>
                    <img src="<?= $i['school_logo_base64'] ?>" class="logo-img" alt="Logo">
                <?php endif; ?>
            </div>
            <div class="header-content">
                <h2 class="school-name"><?= htmlspecialchars($schoolDisplayName) ?></h2>
                <div class="school-meta">
                    <?= htmlspecialchars($i['school_city'] ?? '') ?> • <?= htmlspecialchars($i['school_phone'] ?? '') ?><br>
                    Année Scolaire <?= htmlspecialchars($activeYear['nom']) ?>
                </div>
            </div>
        </div>

        <div style="margin-bottom: 20px; text-align: center;">
            <h1 style="font-size: 20px; font-weight: bold; margin: 0; text-transform: uppercase;"><?= __('honor_roll_title') ?></h1>
            <div style="font-size: 13px; color: #666; margin-top: 5px;"><?= htmlspecialchars($classInfo['nom']) ?> — <?= __('trimester') ?> <?= (int) $term ?></div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 70px; text-align: center;">Rang</th>
                        <th>Nom & Prénoms de l'élève</th>
                        <th style="width: 100px; text-align: center;">Moyenne</th>
                        <th style="width: 150px; text-align: center;">Mention Accordée</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($honors)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 30px; color: #999; font-weight: bold;">
                                <?= __('no_data') ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $r = 1; foreach ($honors as $studentId => $data): ?>
                            <tr>
                                <td style="text-align: center;">
                                    <span class="rank"><?= $r++ ?></span>
                                </td>
                                <td>
                                    <div class="student-name"><?= htmlspecialchars($data['nom'] . ' ' . $data['prenom']) ?></div>
                                    <div class="student-meta">Matricule: <?= htmlspecialchars($data['matricule'] ?? 'N/A') ?></div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="avg"><?= number_format($data['average'], 2, ',', ' ') ?></span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="mention">
                                        <?= htmlspecialchars($this->getMention($data['average'])) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="signatures">
            <div class="sig-box">
                <span class="sig-title"><?= __('signature_principal') ?></span>
                <div class="sig-line"></div>
            </div>
            <div class="sig-box">
                <span class="sig-title"><?= __('signature_teacher') ?></span>
                <div class="sig-line"></div>
            </div>
        </div>

        <div style="margin-top: 20px; text-align: center; font-size: 9px; color: #999; text-transform: uppercase; letter-spacing: 1px; font-weight: bold;">
            Généré par NotesMaster
        </div>
    </div>
</body>
</html>
