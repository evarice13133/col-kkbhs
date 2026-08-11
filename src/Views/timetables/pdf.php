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
            margin: 4mm 6mm;
        }

        @media print {
            .no-print {
                display: none !important;
            }
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
                opacity: 0.22 !important;
                filter: alpha(opacity=22) !important;
                z-index: 1 !important;
                display: block !important;
                visibility: visible !important;
            }
            .cert-watermark img {
                display: block !important;
                visibility: visible !important;
                max-width: 320px !important;
                max-height: 230px !important;
                margin: 0 auto !important;
            }
            .watermark-code {
                display: block !important;
                visibility: visible !important;
                font-size: 28px !important;
                font-weight: 900 !important;
                color: #1a3a5a !important;
                letter-spacing: 10px !important;
                text-transform: uppercase !important;
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
            .teacher-name {
                color: #2563eb !important;
            }
            .room-name {
                color: #dc2626 !important;
            }
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
            font-size: 8.5px;
            background: #ffffff;
            line-height: 1.15;
        }

        /* WATERMARK FIXE EN ARRIÈRE-PLAN SUR TOUTES LES PAGES (STYLE STRICT DU TABLEAU D'HONNEUR) */
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
            max-width: 320px;
            max-height: 230px;
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

        /* Conteneur de page groupe */
        .timetable-group-page {
            width: 100%;
            position: relative;
            z-index: 2;
            min-height: 95vh;
        }

        .page-break-before {
            page-break-before: always;
            break-before: page;
        }

        /* En-tête Institutionnel Centré */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5px solid #1e3a8a;
            padding-bottom: 1px;
            margin-bottom: 2px;
            background: transparent;
            position: relative;
            z-index: 3;
        }

        .logo-container {
            width: 12%;
            vertical-align: middle;
            text-align: left;
        }

        .logo-img {
            max-height: 30px;
            max-width: 85px;
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
            padding: 0 3px;
        }

        .school-name {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
        }

        .school-decree {
            font-size: 7.5px;
            font-style: italic;
            color: #475569;
            margin-top: 0px;
            margin-bottom: 1px;
            text-align: center;
            line-height: 1.05;
        }

        .doc-ref-number {
            font-size: 8.5px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
            margin-top: 1px;
            margin-bottom: 0px;
        }

        .doc-title {
            font-size: 11.5px;
            font-weight: 800;
            color: #2563eb;
            margin-top: 1px;
            text-transform: uppercase;
            text-align: center;
            text-decoration: underline;
        }

        .cert-container {
            width: 12%;
            vertical-align: middle;
            text-align: right;
        }

        .partner-logo-img {
            max-height: 30px;
            max-width: 85px;
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

        /* INFORMATIONS CONTEXTUELLES SUR UNE SEULE LIGNE SANS BORDURE NI FOND */
        .meta-single-line {
            text-align: center;
            font-size: 9px;
            color: #1e293b;
            margin-top: 2px;
            margin-bottom: 3px;
            background: transparent;
            border: none;
            padding: 0;
            line-height: 1.2;
            position: relative;
            z-index: 3;
        }

        .meta-item {
            display: inline-block;
            vertical-align: middle;
        }

        .meta-label {
            font-weight: 700;
            color: #475569;
        }

        .meta-val {
            font-weight: 800;
            color: #0f172a;
        }

        .meta-sep {
            color: #94a3b8;
            font-weight: 300;
        }

        .meta-group-tag {
            background-color: #15803d;
            color: #ffffff;
            font-weight: 800;
            font-size: 8.5px;
            padding: 1px 6px;
            border-radius: 3px;
            text-transform: uppercase;
            display: inline-block;
        }

        /* Grille Tableau Pro (Design System Bulletin de Notes - Vert Institutionnel Origine) */
        .grid-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 1px;
            border: 1px solid green;
            background: transparent;
            position: relative;
            z-index: 3;
        }

        .grid-table thead {
            display: table-header-group;
        }

        .grid-table tfoot {
            display: table-footer-group;
        }

        .grid-table th {
            background-color: green;
            color: #ffffff;
            font-weight: 800;
            text-transform: uppercase;
            padding: 3px 1px;
            font-size: 8px;
            border: 1px solid green;
            text-align: center;
            vertical-align: middle;
            letter-spacing: 0.3px;
        }

        .grid-table tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .grid-table td {
            border: 1px solid green;
            padding: 2px 1px;
            vertical-align: middle;
            text-align: center;
            min-height: 25px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            color: #000000;
        }

        /* Cellules Spéciales Centrées */
        .day-cell {
            background: linear-gradient(180deg, #e8f5e9 0%, #c8e6c9 100%);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-weight: 900;
            color: #1b5e20;
            font-size: 8px;
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
            font-size: 7px;
            font-family: monospace;
            text-align: center;
            vertical-align: middle;
            border: 1px solid green !important;
        }

        .pause-row-cell {
            background-color: #e8f5e9 !important;
            color: #1b5e20 !important;
            font-weight: 800;
            font-size: 7px !important;
            text-align: center;
            vertical-align: middle;
            border: 1px solid green !important;
            padding: 2px 1px !important;
        }

        .empty-cell {
            color: #cbd5e1;
            font-size: 8px;
        }

        /* Contenu des Cours Centré */
        .course-box {
            text-align: center;
            vertical-align: middle;
            padding: 1px;
        }

        .subject-code {
            font-size: 7px;
            font-weight: bold;
            color: #2563eb;
            text-transform: uppercase;
            margin-bottom: 1px;
        }

        .subject-name {
            font-size: 8.5px;
            font-weight: 800;
            color: #0f172a !important;
            line-height: 1.1;
            margin-bottom: 1px;
            text-align: center;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .teacher-name {
            font-size: 7.5px;
            color: #2563eb !important;
            font-weight: 700;
            text-align: center;
            margin-bottom: 1px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .room-name {
            font-size: 7.5px;
            color: #dc2626 !important;
            font-weight: 700;
            text-align: center;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
    </style>
</head>

<body>
    <?php
    $allClasses = $gridData['classes'] ?? [];
    // Découpage dynamique par groupes de 5 classes maximum
    $classChunks = !empty($allClasses) ? array_chunk($allClasses, 5) : [[]];
    $totalGroups = count($classChunks);
    ?>

    <?php foreach ($classChunks as $groupIndex => $chunkClasses): ?>
        <?php $groupNumber = $groupIndex + 1; ?>

        <div class="timetable-group-page <?= $groupIndex > 0 ? 'page-break-before' : '' ?>">
            <!-- FILIGRANE (LOGO ET CODE ÉTABLISSEMENT) FIXÉ AU CENTRE DE CHAQUE PAGE PAGE DE GROUPE -->
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
                        <div class="doc-ref-number">
                            EMPLOI DU TEMPS N°
                            <?= htmlspecialchars($timetableNum ?? str_pad((string) ($id ?? 1), 5, '0', STR_PAD_LEFT)) ?>/<?= h($activeYear['nom'] ?? date('Y')) ?>/<?= h($school_code ?? 'ISTEC') ?>/DIR/DAAC/SG-BWADIBO
                        </div>
                    </td>
                    <td class="cert-container">
                        <?php if (!empty($partnerLogoBase64)): ?>
                            <img src="<?= $partnerLogoBase64 ?>" class="partner-logo-img" alt="Tutelle">
                        <?php elseif (!empty($partner_logo)): ?>
                            <img src="<?= htmlspecialchars($partner_logo) ?>" class="partner-logo-img" alt="Tutelle">
                        <?php else: ?>
                            <div class="partner-logo-fallback">TUTELLE</div>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <!-- Titre du document -->
            <div class="doc-header-banner" style="text-align: center; margin-top: 2px; margin-bottom: 2px;">
                <div class="doc-title">
                    <?= __('timetables_pdf_global_title') ?>
                    <?= h($levelRow['libelle_' . __('lang')] ?? $levelRow['libelle_fr'] ?? $levelRow['code'] ?? '') ?>
                </div>
            </div>

            <!-- INFORMATIONS CONTEXTUELLES SUR UNE SEULE LIGNE SANS BORDURE NI FOND -->
            <div class="meta-single-line">
                <span class="meta-item"><span class="meta-label">Cycle :</span> <span class="meta-val"><?= h($cycleRow['nom'] ?? '-') ?></span></span>
                <span class="meta-sep">&nbsp;&nbsp;|&nbsp;&nbsp;</span>
                <span class="meta-item"><span class="meta-label">Année Académique :</span> <span class="meta-val"><?= h($activeYear['nom'] ?? date('Y')) ?></span></span>
                <span class="meta-sep">&nbsp;&nbsp;|&nbsp;&nbsp;</span>
                <span class="meta-item">
                    <span class="meta-label">Semaine :</span> 
                    <span class="meta-val">
                        <?php
                        $dDebut = !empty($weekRow['date_debut']) ? date('d/m/Y', strtotime($weekRow['date_debut'])) : '';
                        $dFin = !empty($weekRow['date_fin']) ? date('d/m/Y', strtotime($weekRow['date_fin'])) : '';
                        if (!empty($dDebut) && !empty($dFin)) {
                            echo 'du ' . $dDebut . ' au ' . $dFin;
                        } else {
                            echo h($weekRow['libelle'] ?? '-');
                        }
                        ?>
                    </span>
                </span>
                <span class="meta-sep">&nbsp;&nbsp;|&nbsp;&nbsp;</span>
                <?php if ($totalGroups > 1): ?>
                    <span class="meta-item"><span class="meta-group-tag">Groupe <?= $groupNumber ?>/<?= $totalGroups ?></span></span>
                    <span class="meta-sep">&nbsp;&bull;&nbsp;</span>
                <?php endif; ?>
                <span class="meta-item"><span class="meta-label">Classes :</span> <span class="meta-val"><?= implode(' &middot; ', array_map(function($c) { return h($c['nom']); }, $chunkClasses)) ?></span></span>
            </div>

            <!-- Grille de l'Emploi du Temps pour ce Groupe de Classes -->
            <table class="grid-table">
                <thead>
                    <tr>
                        <th style="width: 7%;"><?= __('timetables_day_header') ?></th>
                        <th style="width: 11%;"><?= __('timetables_schedule_header') ?></th>
                        <?php 
                        $chunkCount = count($chunkClasses);
                        $classColWidth = $chunkCount > 0 ? (82 / $chunkCount) . '%' : '82%';
                        foreach ($chunkClasses as $cls): 
                        ?>
                            <th style="width: <?= $classColWidth ?>;"><?= h($cls['nom']) ?></th>
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
                                        <?= h($day) ?>
                                    </td>
                                <?php endif; ?>

                                <td class="time-cell">
                                    <?= substr($slot['heure_debut'], 0, 5) ?> - <?= substr($slot['heure_fin'], 0, 5) ?>
                                </td>

                                <?php if ($isPause): ?>
                                    <td colspan="<?= count($chunkClasses) ?>" class="pause-row-cell">
                                        [ PAUSE & INTERVALLE ] &nbsp; (<?= substr($slot['heure_debut'], 0, 5) ?> - <?= substr($slot['heure_fin'], 0, 5) ?>)
                                    </td>
                                <?php else: ?>
                                    <?php foreach ($chunkClasses as $cls): ?>
                                        <?php
                                        $classId = (int) $cls['id'];
                                        $entry = $gridData['matrix'][$day][$slot['id']][$classId] ?? null;
                                        ?>
                                        <?php if ($entry): ?>
                                            <td>
                                                <div class="course-box">
                                                    <?php if (!empty($entry['subject_code'])): ?>
                                                        <div class="subject-code"><?= h($entry['subject_code']) ?></div>
                                                    <?php endif; ?>
                                                    <div class="subject-name"><?= h($entry['subject_name']) ?></div>
                                                    <div class="teacher-name"><?= h($entry['teacher_name']) ?></div>
                                                    <div class="room-name"><?= __('timetables_room_prefix') ?><?= h($entry['room_name']) ?></div>
                                                </div>
                                            </td>
                                        <?php else: ?>
                                            <td class="empty-cell">-</td>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Signature Institutionnelle (Angle Inférieur Droit) -->
            <table style="width: 100%; border-collapse: collapse; margin-top: 3px; page-break-inside: avoid; position: relative; z-index: 3;">
                <tr>
                    <td style="width: 65%; border: none;"></td>
                    <td style="width: 35%; text-align: center; border: none; vertical-align: top;">
                        <div style="font-size: 8px; font-style: italic; color: #1e293b; margin-bottom: 2px;">
                            Fait à <?= htmlspecialchars(!empty($school_city) ? $school_city : 'Douala') ?>, le <?= date('d/m/Y') ?>
                        </div>
                        <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; text-decoration: underline; color: #0f172a; letter-spacing: 0.5px;">
                            LA DIRECTION
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    <?php endforeach; ?>

    <?php 
    $isPrintMode = ($mode === 'print' || (isset($_GET['mode']) && $_GET['mode'] === 'print'));
    $isPreviewMode = ($mode === 'preview' || (isset($_GET['mode']) && $_GET['mode'] === 'preview'));
    ?>

    <?php if ($isPrintMode || $isPreviewMode): ?>
        <div class="no-print" style="position: fixed; top: 12px; right: 16px; z-index: 9999; display: flex; align-items: center; gap: 8px; background: rgba(15, 23, 42, 0.88); padding: 8px 14px; border-radius: 30px; backdrop-filter: blur(8px); box-shadow: 0 4px 14px rgba(0,0,0,0.3); color: #ffffff; font-family: system-ui, -apple-system, sans-serif;">
            <button type="button" onclick="window.print()" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: #ffffff; border: none; padding: 7px 18px; border-radius: 20px; font-weight: 700; font-size: 12px; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(37,99,235,0.4);">
                🖨️ Imprimer maintenant
            </button>
            <button type="button" onclick="window.close()" style="background: rgba(255,255,255,0.15); color: #ffffff; border: 1px solid rgba(255,255,255,0.25); padding: 7px 14px; border-radius: 20px; font-weight: 600; font-size: 12px; cursor: pointer;">
                ✕ Fermer
            </button>
        </div>
    <?php endif; ?>

    <?php if ($isPrintMode): ?>
        <script>
            window.addEventListener('load', function () {
                setTimeout(function () {
                    window.print();
                }, 300);
            });
        </script>
    <?php endif; ?>
</body>

</html>