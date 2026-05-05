<?php
$i = $institution;
$schoolDisplayName = function_exists('mb_strtoupper') ? mb_strtoupper((string) ($i['school_name'] ?? $i['school_code'] ?? ''), 'UTF-8') : strtoupper((string) ($i['school_name'] ?? $i['school_code'] ?? ''));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= __('honor_roll_title') ?> - <?= htmlspecialchars($classInfo['nom']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Outfit:wght@500;700;900&family=Cinzel:wght@700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1e293b;
            --accent: #b38728;
            --gold-grad: linear-gradient(135deg, #bf953f 0%, #fcf6ba 45%, #b38728 50%, #fcf6ba 55%, #aa771c 100%);
            --glass: rgba(255, 255, 255, 0.95);
        }
        * { box-sizing: border-box; }
        @page { size: A4 portrait; margin: 0; }
        body { 
            font-family: 'Inter', sans-serif; 
            font-size: 13px; 
            margin: 0; 
            padding: 0; 
            color: var(--primary); 
            background: #fdfdfd;
            line-height: 1.5;
        }
        .honor-roll-container {
            padding: 1.2cm;
            min-height: 297mm;
            position: relative;
            background: #fff;
        }
        
        /* Ultra Premium Header */
        .elite-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(to right, #1e293b, #0f172a);
            border-radius: 20px;
            color: #fff;
            border-bottom: 4px solid #b38728;
        }
        .elite-logo-wrapper {
            background: #fff;
            padding: 8px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .elite-logo-img { width: 60px; height: 60px; object-fit: contain; }
        .elite-header-content { text-align: right; flex-grow: 1; margin-left: 20px; }
        .elite-school-name { 
            font-family: 'Cinzel', serif;
            font-size: 20px; 
            font-weight: 900; 
            letter-spacing: 1px;
            margin: 0;
            background: var(--gold-grad);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .elite-school-meta { font-size: 11px; opacity: 0.8; font-weight: 600; margin-top: 5px; }

        /* Hero Title Section */
        .hero-banner {
            position: relative;
            text-align: center;
            padding: 50px 30px;
            background: linear-gradient(135deg, #aa771c 0%, #1a1a1a 100%);
            border-radius: 24px;
            margin-bottom: 35px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(184, 134, 11, 0.2);
        }
        .hero-banner::after {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url('https://www.transparenttextures.com/patterns/natural-paper.png');
            opacity: 0.1;
        }
        .hero-title {
            font-family: 'Outfit', sans-serif;
            font-size: 42px;
            font-weight: 900;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 6px;
            margin: 0;
            position: relative;
            z-index: 1;
        }
        .hero-subtitle {
            font-size: 20px;
            color: #fcf6ba;
            font-weight: 700;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 4px;
            position: relative;
            z-index: 1;
        }

        /* Stats Grid */
        .elite-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 35px;
        }
        .elite-stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 18px;
            border: 1px solid #e2e8f0;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }
        .elite-stat-label { font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }
        .elite-stat-value { font-size: 26px; font-weight: 900; color: var(--primary); display: block; margin-top: 5px; }

        /* Premium Table */
        .elite-table-wrapper {
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        }
        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        th { 
            background: #fdfdfd; 
            color: #475569; 
            font-weight: 800; 
            text-transform: uppercase; 
            font-size: 11px; 
            padding: 18px 20px;
            text-align: left;
            border-bottom: 2px solid #e2e8f0;
        }
        td { 
            padding: 16px 20px; 
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }
        
        .rank-circle {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, #aa771c, #bf953f);
            color: #fff;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 900;
            font-size: 12px;
        }
        .student-name-elite { font-weight: 800; color: var(--primary); font-family: 'Outfit', sans-serif; font-size: 15px; }
        .avg-pill {
            background: rgba(184, 134, 11, 0.1);
            color: #aa771c;
            padding: 6px 15px;
            border-radius: 10px;
            font-weight: 900;
            font-size: 15px;
            display: inline-block;
        }
        .mention-badge-elite {
            padding: 6px 12px;
            background: #fff9e6;
            color: #aa771c;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            border: 1px solid #fcf6ba;
        }

        /* Footer Signatures */
        .elite-signatures {
            margin-top: 60px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
        }
        .elite-sig-box {
            background: #fdfdfd;
            border: 1.5px dashed #e2e8f0;
            padding: 30px;
            border-radius: 20px;
            text-align: center;
        }
        .elite-sig-title { font-weight: 900; font-size: 13px; text-transform: uppercase; color: var(--primary); margin-bottom: 60px; display: block; }
        .elite-sig-line { border-top: 2px solid #aa771c; width: 140px; margin: 0 auto; }

        @media print { .no-print { display: none !important; } }
        .no-print {
            position: sticky; top: 0; z-index: 10000; display: flex; align-items: center; justify-content: space-between;
            padding: 15px 30px; background: #0f172a; color: white;
        }
        .btn-elite { 
            padding: 12px 24px; border-radius: 12px; cursor: pointer; font-size: 14px; font-weight: 800; 
            text-decoration: none; color: white; border: none;
        }
        .btn-print { background: #aa771c; }
        .btn-back { background: rgba(255,255,255,0.1); }
    </style>
</head>
<body>
    <div class="no-print">
        <div style="font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 900; letter-spacing: 1px;">NOTESMASTER <span style="color: #aa771c;">ULTRA</span></div>
        <div style="display: flex; gap: 15px;">
            <a href="/honors?class_id=<?= (int) $classId ?>" class="btn-elite btn-back"><?= __('back') ?></a>
            <button class="btn-elite btn-print" onclick="window.print()"><?= __('pv_print_btn') ?></button>
        </div>
    </div>

    <div class="honor-roll-container">
        <div class="elite-header">
            <div class="elite-logo-wrapper">
                <?php if (!empty($i['school_logo_base64'])): ?>
                    <img src="<?= $i['school_logo_base64'] ?>" class="elite-logo-img" alt="Logo">
                <?php endif; ?>
            </div>
            <div class="elite-header-content">
                <h2 class="elite-school-name"><?= htmlspecialchars($schoolDisplayName) ?></h2>
                <div class="elite-school-meta">
                    <?= htmlspecialchars($i['school_city'] ?? '') ?> • <?= htmlspecialchars($i['school_phone'] ?? '') ?><br>
                    Tableau d'Honneur Annuel <?= htmlspecialchars($activeYear['nom']) ?>
                </div>
            </div>
        </div>

        <div class="hero-banner">
            <h1 class="hero-title"><?= __('honor_roll_title') ?></h1>
            <div class="hero-subtitle"><?= htmlspecialchars($classInfo['nom']) ?> — <?= strtoupper(__('annual')) ?></div>
        </div>

        <div class="elite-stats">
            <div class="elite-stat-card">
                <span class="elite-stat-label"><?= __('total_students') ?></span>
                <span class="elite-stat-value"><?= count($honors) ?></span>
            </div>
            <div class="elite-stat-card">
                <span class="elite-stat-label">Excellence Annuelle</span>
                <span class="elite-stat-value" style="color: #aa771c;">
                    <?= count(array_filter($honors, fn($h) => $h['average'] >= 16)) ?>
                </span>
            </div>
            <div class="elite-stat-card">
                <span class="elite-stat-label">Moyenne Générale</span>
                <span class="elite-stat-value">
                    <?php 
                        $totalAvg = count($honors) > 0 ? array_sum(array_column($honors, 'average')) / count($honors) : 0;
                        echo number_format($totalAvg, 2, ',', ' ');
                    ?>
                </span>
            </div>
        </div>

        <div class="elite-table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 80px; text-align: center;">Rang</th>
                        <th>Nom & Prénoms de l'élève</th>
                        <th style="width: 140px; text-align: center;">Moyenne Annuelle</th>
                        <th style="width: 200px; text-align: center;">Mention Finale</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($honors)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 60px; color: #94a3b8; font-weight: 600;">
                                <?= __('no_data') ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $r = 1; foreach ($honors as $studentId => $data): ?>
                            <tr>
                                <td style="text-align: center;">
                                    <div style="display: flex; justify-content: center;">
                                        <div class="rank-circle"><?= $r++ ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="student-name-elite"><?= htmlspecialchars($data['nom'] . ' ' . $data['prenom']) ?></div>
                                    <div style="font-size: 10px; color: #64748b; margin-top: 2px;">Matricule: <?= htmlspecialchars($data['matricule'] ?? 'N/A') ?></div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="avg-pill"><?= number_format($data['average'], 2, ',', ' ') ?></span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="mention-badge-elite">
                                        <?= htmlspecialchars($this->getMention($data['average'])) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="elite-signatures">
            <div class="elite-sig-box">
                <span class="elite-sig-title"><?= __('signature_principal') ?></span>
                <div class="elite-sig-line"></div>
            </div>
            <div class="elite-sig-box">
                <span class="elite-sig-title"><?= __('signature_teacher') ?></span>
                <div class="elite-sig-line"></div>
            </div>
        </div>

        <div style="margin-top: 40px; text-align: center; font-size: 9px; color: #94a3b8; text-transform: uppercase; letter-spacing: 2px; font-weight: 700;">
            Généré officiellement par NotesMaster v2.0 - Distinction Annuelle d'Excellence
        </div>
    </div>
</body>
</html>
