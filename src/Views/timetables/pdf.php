<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= h($timetable['titre']) ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 0;
            font-size: 10px;
            background: #ffffff;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .school-title {
            font-size: 18px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
        }
        .timetable-title {
            font-size: 14px;
            font-weight: bold;
            color: #3b82f6;
            margin-top: 5px;
        }
        .meta-text {
            font-size: 9px;
            color: #64748b;
        }
        .grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .grid-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 4px;
            font-size: 9px;
            border: 1px solid #0f172a;
            text-align: center;
        }
        .grid-table td {
            border: 1px solid #cbd5e1;
            padding: 5px;
            vertical-align: middle;
            text-align: center;
            height: 45px;
        }
        .time-cell {
            background-color: #f8fafc;
            font-weight: bold;
            color: #334155;
            width: 90px;
        }
        .pause-cell {
            background-color: #fef08a;
            color: #854d0e;
            font-weight: bold;
            font-size: 9px;
        }
        .course-box {
            background-color: #f0f9ff;
            border-left: 3px solid #3b82f6;
            padding: 4px;
            border-radius: 4px;
            text-align: left;
        }
        .subject-name {
            font-size: 10px;
            font-weight: bold;
            color: #1e40af;
        }
        .teacher-name {
            font-size: 8px;
            color: #475569;
        }
        .room-name {
            font-size: 8px;
            color: #dc2626;
            font-weight: bold;
        }
        .footer-table {
            width: 100%;
            margin-top: 15px;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td>
                <div class="school-title"><?= h($school_name) ?></div>
                <div class="timetable-title"><?= h($timetable['titre']) ?></div>
                <div class="meta-text">
                    <strong>Classe :</strong> <?= h($timetable['class_name']) ?> | 
                    <strong>Semaine :</strong> <?= h($timetable['week_libelle']) ?> (du <?= date('d/m/Y', strtotime($timetable['week_start'])) ?> au <?= date('d/m/Y', strtotime($timetable['week_end'])) ?>)
                </div>
            </td>
            <td style="text-align: right;">
                <div class="meta-text">Généré le <?= date('d/m/Y à H:i') ?></div>
                <div class="meta-text">Système NoteMaster - Pilotage Scolaire</div>
            </td>
        </tr>
    </table>

    <table class="grid-table">
        <thead>
            <tr>
                <th>Horaire</th>
                <?php foreach ($gridData['days'] as $day): ?>
                    <th><?= $day ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($gridData['slots'] as $slot): ?>
                <?php $isPause = ($slot['type_creneau'] === 'pause'); ?>
                <tr>
                    <td class="time-cell">
                        <?= substr($slot['heure_debut'], 0, 5) ?> - <?= substr($slot['heure_fin'], 0, 5) ?>
                    </td>
                    <?php foreach ($gridData['days'] as $day): ?>
                        <?php $entry = $matrix[$slot['id']][$day] ?? null; ?>
                        <?php if ($isPause): ?>
                            <td class="pause-cell">PAUSE</td>
                        <?php elseif ($entry): ?>
                            <td>
                                <div class="course-box" style="border-left-color: <?= h($entry['couleur_hex']) ?>;">
                                    <div class="subject-name"><?= h($entry['subject_name']) ?></div>
                                    <div class="teacher-name">Ens: <?= h($entry['teacher_name']) ?></div>
                                    <div class="room-name">Salle: <?= h($entry['room_name']) ?></div>
                                </div>
                            </td>
                        <?php else: ?>
                            <td style="color: #cbd5e1;">-</td>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <table class="footer-table">
        <tr>
            <td>NoteMaster &copy; <?= date('Y') ?> - Tous droits réservés. Document officiel d'emploi du temps.</td>
            <td style="text-align: right;">Page 1 / 1</td>
        </tr>
    </table>

    <?php if (isset($_GET['mode']) && $_GET['mode'] === 'print'): ?>
        <script>
            window.onload = function() {
                window.print();
            };
        </script>
    <?php endif; ?>
</body>
</html>
