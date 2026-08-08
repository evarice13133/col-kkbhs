<!DOCTYPE html>
<html lang="<?= __('lang') ?>">

<head>
    <meta charset="UTF-8">
    <title><?= __('timetables_menu') ?> -
        <?= h(($levelRow['libelle_' . __('lang')] ?? $levelRow['libelle_fr'] ?? 'Niveau')) ?> -
        <?= h($weekRow['libelle'] ?? '') ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 2mm 4mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            margin: 0;
            padding: 0;
            font-size: 7px;
            background: #ffffff;
            line-height: 1.05;
        }

        /* En-tête Institutionnel Centré Ultra-Compact (1 Page Stricte) */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5px solid #1e3a8a;
            padding-bottom: 1px;
            margin-bottom: 1px;
        }

        .logo-container {
            width: 12%;
            vertical-align: middle;
            text-align: left;
        }

        .logo-img {
            max-height: 26px;
            max-width: 75px;
        }

        .logo-fallback {
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: 900;
            font-size: 9px;
            padding: 3px 5px;
            border-radius: 3px;
            text-align: center;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .institution-container {
            width: 76%;
            vertical-align: middle;
            text-align: center;
            padding: 0 2px;
        }

        .school-name {
            font-size: 11px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
        }

        .school-decree {
            font-size: 7px;
            font-style: italic;
            color: #475569;
            margin-top: 0px;
            margin-bottom: 1px;
            text-align: center;
            line-height: 1.05;
        }

        .doc-ref-number {
            font-size: 9.5px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
            margin-top: 0px;
            margin-bottom: 1px;
        }

        .doc-title {
            font-size: 11px;
            font-weight: 800;
            color: #2563eb;
            margin-top: 0px;
            text-transform: uppercase;
            text-align: center;
            text-decoration: underline;
        }

        .meta-bar {
            font-size: 9.5px;
            color: #1e293b;
            margin-top: 2px;
            margin-bottom: 2px;
            text-align: center;
            font-weight: 700;
        }

        .week-highlight {
            display: inline-block;
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: 800;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 9.5px;
        }

        .cert-container {
            width: 12%;
            vertical-align: middle;
            text-align: right;
        }

        .partner-logo-img {
            max-height: 26px;
            max-width: 75px;
            object-fit: contain;
        }

        .partner-logo-fallback {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: 800;
            font-size: 7.5px;
            padding: 3px 5px;
            border-radius: 3px;
            text-align: center;
            display: inline-block;
            letter-spacing: 0.5px;
        }

        .cert-time {
            font-size: 5.5px;
            color: #64748b;
            margin-top: 0px;
            font-weight: 500;
        }

        /* Grille Tableau Pro (Design System Bulletin de Notes - Vert Institutionnel) */
        .grid-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 1px;
            page-break-inside: avoid;
            border: 1px solid green;
        }

        .grid-table th {
            background-color: green;
            color: #ffffff;
            font-weight: 800;
            text-transform: uppercase;
            padding: 2.5px 1px;
            font-size: 7.5px;
            border: 1px solid green;
            text-align: center;
            vertical-align: middle;
            letter-spacing: 0.3px;
        }

        .grid-table tr {
            page-break-inside: avoid;
        }

        .grid-table td {
            border: 1px solid green;
            padding: 1px 0.5px;
            vertical-align: middle;
            text-align: center;
            height: 20px;
            word-wrap: break-word;
            overflow: hidden;
            color: #000000;
        }

        /* Cellules Spéciales Centrées */
        .day-cell {
            background: linear-gradient(180deg, #e8f5e9 0%, #c8e6c9 100%);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-weight: 900;
            color: #1b5e20;
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
            vertical-align: middle;
            border: 1px solid green !important;
        }

        .time-cell {
            background-color: #f4fbf7;
            font-weight: 700;
            color: #1b5e20;
            font-size: 6.5px;
            font-family: monospace;
            text-align: center;
            vertical-align: middle;
            border: 1px solid green !important;
        }

        .pause-row-cell {
            background-color: #e8f5e9 !important;
            color: #1b5e20 !important;
            font-weight: 800;
            font-size: 6.5px !important;
            text-align: center;
            vertical-align: middle;
            border: 1px solid green !important;
            height: 10px !important;
            padding: 0px 1px !important;
        }

        /* Contenu des Cours Centré */
        .course-box {
            text-align: center;
            vertical-align: middle;
            padding: 0.5px;
        }

        .subject-name {
            font-size: 7.5px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.05;
            margin-bottom: 0px;
            text-align: center;
        }

        .teacher-name {
            font-size: 6.5px;
            color: #334155;
            font-weight: 500;
            text-align: center;
        }

        /* WATERMARK FIXE REPRODUIT STRICTEMENT DU TABLEAU D'HONNEUR */
        .cert-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            -webkit-transform: translate(-50%, -50%);
            width: 380px;
            opacity: 0.22;
            filter: alpha(opacity=22);
            z-index: 1;
            text-align: center;
            pointer-events: none;
        }
        .cert-watermark img {
            width: 100%;
            max-width: 300px;
            max-height: 220px;
            display: block;
            margin: 0 auto;
            object-fit: contain;
        }
        .watermark-code {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 28px;
            font-weight: 900;
            color: #1a3a5a;
            margin-top: 10px;
            letter-spacing: 10px;
            text-transform: uppercase;
            text-align: center;
            line-height: 1.1;
        }

        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            body {
                background: #ffffff !important;
            }
            .cert-watermark {
                position: absolute !important;
                top: 50% !important;
                left: 50% !important;
                transform: translate(-50%, -50%) !important;
                -webkit-transform: translate(-50%, -50%) !important;
                opacity: 0.25 !important;
                filter: alpha(opacity=25) !important;
                z-index: 1 !important;
                display: block !important;
                visibility: visible !important;
            }
            .grid-table th {
                background-color: green !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .day-cell {
                background: linear-gradient(180deg, #e8f5e9 0%, #c8e6c9 100%) !important;
                background-color: #c8e6c9 !important;
                color: #1b5e20 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .time-cell {
                background-color: #f4fbf7 !important;
                color: #1b5e20 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .pause-row-cell {
                background-color: #e8f5e9 !important;
                color: #1b5e20 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>

<body>
    <!-- En-tête Institutionnel Officiel -->
    <table class="header-table">
        <tr>
            <td class="logo-container">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>" class="logo-img" alt="Logo">
                <?php elseif (!empty($school_logo)): ?>
                    <img src="<?= $school_logo ?>" class="logo-img" alt="Logo">
                <?php else: ?>
                    <div class="logo-fallback"><?= h($school_code ?? 'ACADEMIE') ?></div>
                <?php endif; ?>
            </td>
            <td class="institution-container">
                <div class="school-name"><?= h($school_name) ?></div>
                <?php if (!empty($creation_decree)): ?>
                    <div class="school-decree"><?= \App\Core\Helpers::formatCreationDecree((string) $creation_decree) ?>
                    </div>
                <?php endif; ?>
            </td>
            <td class="cert-container">
                <?php if (!empty($partnerLogoBase64)): ?>
                    <img src="<?= $partnerLogoBase64 ?>" class="partner-logo-img" alt="Tutelle">
                <?php elseif (!empty($partner_logo)): ?>
                    <img src="<?= htmlspecialchars($partner_logo) ?>" class="partner-logo-img" alt="Tutelle">
                <?php else: ?>
                    <div class="partner-logo-fallback">TUTELLE</div>
                <?php endif; ?>
                <div class="cert-time">Édité le <?= date('d/m/Y H:i') ?></div>
            </td>
        </tr>
    </table>

    <!-- Titre et Métadonnées de l'Emploi du Temps (Sous l'en-tête) -->
    <div class="doc-header-banner" style="text-align: center; margin-top: 3px; margin-bottom: 5px;">
        <div class="doc-ref-number">
            EMPLOI DU TEMPS N°
            <?= htmlspecialchars($timetableNum ?? str_pad((string) ($id ?? 1), 5, '0', STR_PAD_LEFT)) ?>/<?= h($activeYear['nom'] ?? date('Y')) ?>/<?= h($school_code ?? 'ISTEC') ?>/DIR/DAAC/SG-BWADIBO
        </div>
        <div class="doc-title"><?= __('timetables_pdf_global_title') ?>
            <?= h($levelRow['libelle_' . __('lang')] ?? $levelRow['libelle_fr'] ?? $levelRow['code'] ?? '') ?></div>
        <div class="meta-bar">
            Type : Supérieur LMD &nbsp;|&nbsp;
            Cycle : <?= h($cycleRow['nom'] ?? '') ?> &nbsp;|&nbsp;
            Année Académique : <?= h($activeYear['nom'] ?? date('Y')) ?> &nbsp;|&nbsp;
            <span class="week-highlight">
                <?php
                $wName = h($weekRow['libelle'] ?? '');
                $dDebut = date('d/m/Y', strtotime($weekRow['date_debut']));
                $dFin = date('d/m/Y', strtotime($weekRow['date_fin']));
                if (str_ireplace('Semaine du', '', $wName) !== $wName) {
                    echo $wName . ' au ' . $dFin;
                } else {
                    echo 'Semaine du ' . $dDebut . ' au ' . $dFin;
                }
                ?>
            </span>
        </div>
    </div>

    <!-- Conteneur avec filigrane en arrière-plan (Technique Tableau d'Honneur) -->
    <div style="position: relative; width: 100%;">
        <div class="cert-watermark">
            <?php if (!empty($logoBase64)): ?>
                <img src="<?= $logoBase64 ?>" alt="Watermark Logo">
            <?php elseif (!empty($school_logo)): ?>
                <img src="<?= $school_logo ?>" alt="Watermark Logo">
            <?php endif; ?>
            <?php if (!empty($school_code)): ?>
                <div class="watermark-code"><?= htmlspecialchars((string)$school_code) ?></div>
            <?php endif; ?>
        </div>

        <!-- Grille de l'Emploi du Temps -->
        <table class="grid-table" style="position: relative; z-index: 2; background: transparent;">
        <thead>
            <tr>
                <th style="width: 7%;"><?= __('timetables_day_header') ?></th>
                <th style="width: 11%;"><?= __('timetables_schedule_header') ?></th>
                <?php foreach ($gridData['classes'] as $cls): ?>
                    <th><?= h($cls['nom']) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($gridData['days'] as $dayIndex => $day): ?>
                <?php foreach ($gridData['slots'] as $slotIndex => $slot): ?>
                    <?php $isPause = ($slot['type_creneau'] === 'pause'); ?>
                    <tr>
                        <?php if ($slotIndex === 0): ?>
                            <td rowspan="<?= count($gridData['slots']) ?>" class="day-cell">
                                <?= $day ?>
                            </td>
                        <?php endif; ?>

                        <td class="time-cell">
                            <?= substr($slot['heure_debut'], 0, 5) ?> - <?= substr($slot['heure_fin'], 0, 5) ?>
                        </td>

                        <?php if ($isPause): ?>
                            <td colspan="<?= count($gridData['classes']) ?>" class="pause-row-cell">
                                [ PAUSE & INTERVALLE ] &nbsp; (<?= substr($slot['heure_debut'], 0, 5) ?> -
                                <?= substr($slot['heure_fin'], 0, 5) ?>)
                            </td>
                        <?php else: ?>
                            <?php foreach ($gridData['classes'] as $cls): ?>
                                <?php
                                $classId = (int) $cls['id'];
                                $entry = $gridData['matrix'][$day][$slot['id']][$classId] ?? null;
                                ?>
                                <?php if ($entry): ?>
                                    <td>
                                        <div class="course-box">
                                            <?php if (!empty($entry['subject_code'])): ?>
                                                <div class="subject-code"
                                                    style="font-size: 6.5px; font-weight: bold; color: #2563eb; text-transform: uppercase; margin-bottom: 0.5px;">
                                                    <?= h($entry['subject_code']) ?></div>
                                            <?php endif; ?>
                                            <div class="subject-name"><?= h($entry['subject_name']) ?></div>
                                            <div class="teacher-name"><?= h($entry['teacher_name']) ?></div>
                                            <div class="room-name"><?= __('timetables_room_prefix') ?>                     <?= h($entry['room_name']) ?></div>
                                        </div>
                                    </td>
                                <?php else: ?>
                                    <td style="color: #cbd5e1; font-size: 7.5px;">-</td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <!-- Signature Institutionnelle (Angle Inférieur Droit) -->
    <table style="width: 100%; border-collapse: collapse; margin-top: 3px; page-break-inside: avoid;">
        <tr>
            <td style="width: 65%; border: none;"></td>
            <td style="width: 35%; text-align: center; border: none; vertical-align: top;">
                <div style="font-size: 8px; font-style: italic; color: #1e293b; margin-bottom: 2px;">
                    Fait à <?= htmlspecialchars(!empty($school_city) ? $school_city : 'Douala') ?>, le
                    <?= date('d/m/Y') ?>
                </div>
                <div
                    style="font-size: 10px; font-weight: 800; text-transform: uppercase; text-decoration: underline; color: #0f172a; letter-spacing: 0.5px;">
                    LA DIRECTION
                </div>
            </td>
        </tr>
    </table>

    <?php if (isset($_GET['mode']) && $_GET['mode'] === 'print'): ?>
        <script>
            window.onload = function () {
                window.print();
            };
        </script>
    <?php endif; ?>
</body>

</html>