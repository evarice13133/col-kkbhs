<?php
/**
 * 1. INITIALISATION DES DONNÉES
 * Variables transmises par le BulletinController pour le bilan annuel.
 */
$embeddedBatch = $embeddedBatch ?? false;
$institution = $institution ?? [];
$activeYear = $activeYear ?? [];
$student = $student ?? [];
$rows = $rows ?? [];                           // Notes annuelles par matière
$groupedRows = $groupedRows ?? [];             // Matières regroupées par groupes
$classStats = $classStats ?? [];               // Statistiques annuelles de la classe
$discipline = $discipline ?? [];               // Bilan disciplinaire annuel
$rank = $rank ?? null;                         // Rang annuel
$effectif = $effectif ?? 0;
$average = $average ?? null;
$mention = $mention ?? '';
$total_coefficients = $total_coefficients ?? 0;
$total_coef_valide = $total_coef_valide ?? 0;
$termAverages = $termAverages ?? [];
$termRanks = $termRanks ?? [];
$globalAppreciation = $globalAppreciation ?? '-';

/**
 * 2. LOGIQUE DE TAILLE DE POLICE DYNAMIQUE
 */
$subjectCount = count($rows);
$baseFontSize = 14;
$pageMargin = '0.5cm';
$lineHeight = 1.3;
$logoSize = '85px';

if ($subjectCount >= 13 && $subjectCount <= 16) {
    $baseFontSize = 12;
    $pageMargin = '0.4cm';
    $lineHeight = 1.2;
} elseif ($subjectCount > 16) {
    $baseFontSize = 10;
    $pageMargin = '0.3cm';
    $lineHeight = 1.1;
    $logoSize = '85px';
}

$lang = \App\Core\Session::get('app_lang', 'fr');
$i = $institution;
$contactLabels = [
    'bp' => __('bp'),
    'tel' => __('tel'),
    'fax' => __('fax'),
    'email' => __('email'),
    'web' => __('web')
];
$contact = trim($contactLabels['tel'] . ': ' . ($i['school_phone'] ?? '') . ' | ' . ($i['school_city'] ?? ''));
$studentLastName = function_exists('mb_strtoupper') ? mb_strtoupper((string) ($student['nom'] ?? ''), 'UTF-8') : strtoupper((string) ($student['nom'] ?? ''));
$schoolDisplayName = function_exists('mb_strtoupper') ? mb_strtoupper((string) ($i['school_name'] ?? $i['school_code'] ?? ''), 'UTF-8') : strtoupper((string) ($i['school_name'] ?? $i['school_code'] ?? ''));
$schoolCodeWatermark = function_exists('mb_strtoupper') ? mb_strtoupper(trim((string) ($i['school_code'] ?? '')), 'UTF-8') : strtoupper(trim((string) ($i['school_code'] ?? '')));
$birthDate = $student['date_naissance'] ?? null;
$birthPlace = trim((string) ($student['lieu_naissance'] ?? ''));
$birthPlace = $birthPlace !== '' ? $birthPlace : '-';
$isRedoublant = !empty($student['is_redoublant']);

/**
 * 2. FONCTIONS D'AFFICHAGE (HELPERS)
 * Identiques aux autres bulletins pour garantir une cohérence visuelle.
 */
if (!function_exists('formatNote')) {
    function formatNote($val)
    {
        if ($val === null || $val === '-') {
            return '-';
        }
        $n = (float) $val;
        $fmt = number_format($n, 2, ',', ' ');
        if ($n >= 10) {
            return '<span class="vert">' . $fmt . '</span>';
        }
        return '<span class="rouge">' . $fmt . '</span>';
    }
}

if (!function_exists('formatSimple')) {
    function formatSimple($val)
    {
        if ($val === null || $val === '-') {
            return '-';
        }
        return number_format((float) $val, 2, ',', ' ');
    }
}

if (!function_exists('formatBulletinDate')) {
    function formatBulletinDate($val)
    {
        if ($val === null || $val === '') {
            return '-';
        }
        $timestamp = strtotime((string) $val);
        if ($timestamp === false) {
            return '-';
        }
        return date('d/m/Y', $timestamp);
    }
}

/**
 * 3. LOGIQUE DES STYLES
 * Le bulletin annuel réutilise la feuille de style du bulletin trimestriel.
 */
if (isset($styleOnly)) {
    include __DIR__ . '/trimestre.php';
    return;
}
?>
<?php if (!$embeddedBatch && empty($isPdf)): ?>
    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <title><?= htmlspecialchars((string) ($pdf_filename ?? 'bulletin-annuel')) ?></title>
        <style>
            <?php $styleOnly = true;
            include __FILE__; ?>
        </style>
    </head>

    <body>
        <!-- BARRE D'OUTILS (Non visible à l'impression) -->
        <div class="pv-toolbar">
            <div class="pv-toolbar-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"
                    style="vertical-align:-3px; margin-right:5px;">
                    <path
                        d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z" />
                    <path
                        d="M4.603 14.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.697 19.697 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .471.215c.15.18-.162 1.305-.162 1.305v.006c-.316.427-.58.111-.58.111s.54.407.728.846c.155.362.29.74.405 1.134.208.718.36 1.4.453 1.954.555.15 1.144.33 1.705.513.29.096.55.195.74.296.262.138.45.321.492.51.042.19.014.39-.115.546-.129.155-.327.24-.546.269-.219.03-.466-.02-.713-.102a4.954 4.954 0 0 1-1.396-.757c-.88-.705-1.58-1.748-1.9-2.235-.351.054-.7.108-1.049.157-.428.06-1.08.125-1.764.125-.453.03-.9.08-1.332.146-.356.055-.705.12-1.05.19-.24.049-.49.123-.715.22z" />
                </svg>
                Mode Impression & Export
            </div>
            <div class="pv-toolbar-hint">
                <?= __('pv_print_hint') ?>
            </div>
            <div>
                <a href="/bulletins?class_id=<?= (int) $student['class_id'] ?>" class="pv-btn pv-btn-back">
                    &larr; <?= __('back') ?>
                </a>
                <button class="pv-btn pv-btn-print" onclick="window.print()">
                    <?= __('pv_print_btn') ?>
                </button>
                <a href="<?= $_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'], '?') !== false ? '&' : '?') ?>format=pdf" class="pv-btn pv-btn-download">
                     <i class="bi bi-file-pdf"></i> <?= __('pv_download_btn') ?>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <div class="bulletin-sheet">
        <!-- A. EN-TÊTE MINISTÉRIEL ET LOGO -->
        <div class="header-wrapper">
            <div class="header-left">
                <div class="header-side-content">
                    <p class="header-line">
                        <?= htmlspecialchars((string) ($i['school_republic'] ?? __('republic_of_cameroon'))) ?></p>
                    <p class="header-line"><?= htmlspecialchars((string) ($i['school_motto'] ?? __('motto'))) ?></p>
                    <p class="header-line">
                        <?= htmlspecialchars((string) ($i['school_ministry'] ?? __('ministry_secondary_education'))) ?>
                    </p>
                    <p class="header-line"><?= htmlspecialchars((string) ($i['school_slogan'] ?? __('slogan'))) ?></p>
                    <p class="header-contact"><?= htmlspecialchars(strtoupper($contact)) ?></p>
                </div>
            </div>

            <div class="header-center">
                <div class="logo-box">
                    <?php if (!empty($i['school_logo_base64'])): ?>
                        <img src="<?= $i['school_logo_base64'] ?>" alt="Logo">
                    <?php elseif (!empty($i['school_logo'])):
                        $logoPath = \App\Core\Helpers::normalizeLogoPath((string) $i['school_logo']); ?>
                        <img src="<?= htmlspecialchars($logoPath) ?>" alt="Logo de l'etablissement">
                    <?php else: ?>
                        <div class="logo-placeholder">LOGO</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="header-right">
                <div class="header-side-content">
                    <p class="header-line">
                        <?= htmlspecialchars((string) ($i['school_republic_en'] ?? 'REPUBLIC OF CAMEROON')) ?></p>
                    <p class="header-line">
                        <?= htmlspecialchars((string) ($i['school_motto_en'] ?? 'PEACE - WORK - FATHERLAND')) ?></p>
                    <p class="header-line">
                        <?= htmlspecialchars((string) ($i['school_ministry_en'] ?? 'MINISTRY OF SECONDARY EDUCATION')) ?>
                    </p>
                    <p class="header-line">
                        <?= htmlspecialchars((string) ($i['school_slogan_en'] ?? 'DISCIPLINE - WORK - SUCCESS')) ?></p>
                    <p class="header-contact"><?= htmlspecialchars(strtoupper($contact)) ?></p>
                </div>
            </div>

            <div class="school-name-row">
                <div class="school-name-display"><?= htmlspecialchars($schoolDisplayName) ?></div>
            </div>
        </div>

        <!-- B. TITRE ET CARTE D'IDENTITÉ -->
        <div class="title-box" style="font-weight: bold;"><?= __('report_card') ?> <?= strtoupper(__('annual_short')) ?>
        </div>

        <table class="student-info-table">
            <tr>
                <td colspan="4" class="nowrap" style="width: auto;"><span
                        class="student-info-label"><?= __('name_and_surname') ?>
                        :</span><span
                        class="student-info-value uppercase"><?= htmlspecialchars($studentLastName . ' ' . ($student['prenom'] ?? '')) ?></span>
                </td>
                <td class="nowrap" style="width: 1%;"><span class="student-info-label"><?= __('matricule') ?>
                        :</span><span
                        class="student-info-value"><?= htmlspecialchars((string) ($displayMatricule ?? $student['matricule'] ?? '')) ?></span>
                </td>
                <td class="nowrap" style="width: 1%;"><span class="student-info-label"><?= __('class') ?> :</span><span
                        class="student-info-value"><?= htmlspecialchars((string) ($student['class_nom'] ?? '')) ?></span>
                </td>
                <td class="nowrap" style="width: 1%;"><span class="student-info-label"><?= __('year') ?> :</span><span
                        class="student-info-value"><?= htmlspecialchars((string) ($activeYear['nom'] ?? '')) ?></span>
                </td>
            </tr>
            <tr>
                <td class="nowrap"><span class="student-info-label"><?= __('birth_date') ?> :</span><span
                        class="student-info-value"><?= htmlspecialchars(formatBulletinDate($birthDate)) ?></span></td>
                <td colspan="2" class="nowrap"><span class="student-info-label"><?= __('birth_place') ?> :</span><span
                        class="student-info-value"><?= htmlspecialchars($birthPlace) ?></span></td>
                <td class="nowrap"><span class="student-info-label"><?= __('sex') ?> :</span><span
                        class="student-info-value"><?= htmlspecialchars((string) ($student['sexe'] ?? '-')) ?></span>
                </td>
                <td colspan="2" class="nowrap"><span class="student-info-label"><?= __('repeating') ?> :</span><span
                        class="check-group student-info-value"><?= __('yes') ?><?= $isRedoublant ? '[X]' : '[ ]' ?>
                        <?= __('no') ?><?= !$isRedoublant ? '[X]' : '[ ]' ?></span></td>
                <td class="nowrap"><span class="student-info-label"><?= __('effectif') ?> :</span><span
                        class="student-info-value"><?= (int) $effectif ?></span>
                </td>
            </tr>
        </table>

        <!-- C. TABLEAU DES NOTES ANNUELLES (Synthèse des 3 trimestres) -->
        <div class="grades-table-wrap">
            <?php if (($lang ?? 'fr') !== 'en'): ?>
                <div class="grades-watermark"><?= htmlspecialchars($schoolCodeWatermark) ?></div>
            <?php endif; ?>
            <?php
            $groupedRowsCount = count($groupedRows);
            $groupsPerTable = max(1, (int) ceil($groupedRowsCount / 3));
            $groupedRowsChunks = array_chunk($groupedRows, $groupsPerTable);
            while (count($groupedRowsChunks) < 3) {
                $groupedRowsChunks[] = [];
            }
            ?>
            <?php foreach ($groupedRowsChunks as $chunkIndex => $groupsChunk): ?>
                <table class="grades-table">
                    <colgroup>
                        <col style="width:37.5%;">
                        <col style="width:10%;">
                        <col style="width:10%;">
                        <col style="width:10%;">
                        <col style="width:7%;">
                        <col style="width:5%;">
                        <col style="width:7.5%;">
                        <col style="width:5%;">
                        <col style="width:8%;">
                    </colgroup>
                    <?php if ($chunkIndex === 0): ?>
                        <thead>
                            <tr>
                                <th><?= __('subjects') ?></th>
                                <th><?= __('trimester_short') ?> 1</th>
                                <th><?= __('trimester_short') ?> 2</th>
                                <th><?= __('trimester_short') ?> 3</th>
                                <th><?= __('annual_avg_short') ?></th>
                                <th><?= __('coef') ?></th>
                                <th><?= __('class_avg') ?></th>
                                <th><?= __('rank') ?></th>
                                <th><?= __('appreciation') ?></th>
                            </tr>
                        </thead>
                    <?php endif; ?>
                    <tbody>
                        <?php foreach ($groupsChunk as $group): ?>
                            <?php foreach ($group['rows'] as $row): ?>
                                <tr>
                                    <td class="left">
                                        <div><?= htmlspecialchars($row['subject']) ?></div>
                                        <span class="teacher-name"><?= htmlspecialchars($row['teacher']) ?></span>
                                    </td>
                                    <td><?= isset($row['term_values'][0]) ? formatNote($row['term_values'][0]) : '-' ?></td>
                                    <td><?= isset($row['term_values'][1]) ? formatNote($row['term_values'][1]) : '-' ?></td>
                                    <td><?= isset($row['term_values'][2]) ? formatNote($row['term_values'][2]) : '-' ?></td>
                                    <td><?= formatNote($row['annual_note']) ?></td>
                                    <td><?= (int) $row['coefficient'] ?></td>
                                    <td><?= formatSimple($row['class_average_subject']) ?></td>
                                    <td><?= $row['rank_subject'] ?? '-' ?></td>
                                    <td><?= htmlspecialchars($row['appreciation']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php foreach ($groupsChunk as $group): ?>
                    <?php
                    /**
                     * CALCULS DES SOUS-TOTAUX ANNUELS PAR GROUPE
                     */
                    $groupTrimTotals = [0.0, 0.0, 0.0];
                    $groupTrimHasTotals = [false, false, false];
                    $groupAnnualTotal = 0.0;
                    $groupHasAnnualTotal = false;
                    foreach ($group['rows'] as $groupRow) {
                        foreach ([0, 1, 2] as $termIndex) {
                            if (($groupRow['term_values'][$termIndex] ?? null) !== null) {
                                $groupTrimTotals[$termIndex] += (float) $groupRow['term_values'][$termIndex];
                                $groupTrimHasTotals[$termIndex] = true;
                            }
                        }
                        if (($groupRow['annual_note'] ?? null) !== null) {
                            $groupAnnualTotal += (float) $groupRow['annual_note'];
                            $groupHasAnnualTotal = true;
                        }
                    }

                    $groupPoints = (float) ($group['total_points'] ?? 0);
                    $groupCoeffs = (int) ($group['total_coefficients'] ?? 0);
                    $mgp = $groupCoeffs > 0 ? round($groupPoints / $groupCoeffs, 2) : 0;
                    ?>
                    <div class="group-subtotal-line">
                        <span><?= htmlspecialchars($group['label']) ?></span>
                        <span><?= strtoupper(__('points')) ?>: <?= formatSimple($groupAnnualTotal) ?></span>
                        <span><?= __('t_coefs') ?>: <?= (float) ($group['total_coeffs_all'] ?? 0) ?></span>
                        <span><?= strtoupper(__('total')) ?>: <?= formatSimple($groupPoints) ?></span>
                        <span><?= __('mgp') ?>: <span
                                class="<?= $mgp >= 10 ? 'vert' : 'rouge' ?>"><?= formatSimple($mgp) ?></span></span>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>

        <!-- D. RÉSULTATS GLOBAUX ET RAPPEL TRIMESTRIEL -->
        <table class="container-table compact-layout">
            <tr>
                <!-- 1. Statistiques de la classe (Annuel) -->
                <td width="29%">
                    <table class="side-table compact-side">
                        <tr>
                            <th colspan="2"><?= __('class_stats') ?></th>
                        </tr>
                        <tr>
                            <td class="left"><?= __('class_avg_gen') ?></td>
                            <td><?= formatSimple($classStats['average'] ?? null) ?></td>
                        </tr>
                        <tr>
                            <td class="left"><?= __('note_max') ?></td>
                            <td><?= formatSimple($classStats['max'] ?? null) ?></td>
                        </tr>
                        <tr>
                            <td class="left"><?= __('note_min') ?></td>
                            <td><?= formatSimple($classStats['min'] ?? null) ?></td>
                        </tr>
                        <tr>
                            <td class="left"><?= __('avg_max') ?></td>
                            <td><?= formatSimple($classStats['max'] ?? null) ?></td>
                        </tr>
                        <tr>
                            <td class="left"><?= __('success_rate') ?></td>
                            <td><?= isset($classStats['success_rate']) ? formatSimple($classStats['success_rate']) . '%' : '-' ?></td>
                        </tr>
                    </table>
                </td>
                <!-- 2. Résumé de l'élève (Annuel) -->
                <td width="31%">
                    <table class="side-table compact-side">
                        <tr>
                            <th colspan="2"><?= __('student_summary') ?></th>
                        </tr>
                        <tr>
                            <td class="left"><?= __('student_avg') ?></td>
                            <td><?= formatNote($average) ?></td>
                        </tr>
                        <tr>
                            <td class="left"><?= __('student_rank') ?></td>
                            <td><?= $rank !== null ? $rank . ' / ' . $effectif : '-' ?></td>
                        </tr>
                        <tr>
                            <td class="left"><?= __('mention') ?></td>
                            <td><?= htmlspecialchars($mention) ?></td>
                        </tr>
                        <tr>
                            <td class="left"><?= __('general_observation') ?></td>
                            <td class="left"><?= htmlspecialchars($globalAppreciation) ?></td>
                        </tr>
                    </table>
                </td>
                <!-- 3. Rappel des moyennes des 3 trimestres -->
                <td width="40%">
                    <table class="side-table compact-side">
                        <tr>
                            <th><?= __('recall') ?></th>
                            <th width="25%"><?= __('average') ?></th>
                            <th width="20%"><?= __('rank') ?></th>
                        </tr>
                        <tr>
                            <td class="left"><?= strtoupper(__('trimester_short_title')) ?> 1</td>
                            <td><?= isset($termAverages[0]) ? formatSimple($termAverages[0]) : '-' ?></td>
                            <td><?= $termRanks[0] ?? '-' ?></td>
                        </tr>
                        <tr>
                            <td class="left"><?= strtoupper(__('trimester_short_title')) ?> 2</td>
                            <td><?= isset($termAverages[1]) ? formatSimple($termAverages[1]) : '-' ?></td>
                            <td><?= $termRanks[1] ?? '-' ?></td>
                        </tr>
                        <tr>
                            <td class="left"><?= strtoupper(__('trimester_short_title')) ?> 3</td>
                            <td><?= isset($termAverages[2]) ? formatSimple($termAverages[2]) : '-' ?></td>
                            <td><?= $termRanks[2] ?? '-' ?></td>
                        </tr>
                        <?php
                        // Consolidation finale de l'année (Somme des MGP et Coeffs)
                        $totalAllCoeffs = 0;
                        $totalMGPs = 0;
                        foreach ($groupedRows as $g) {
                            $totalAllCoeffs += (float) ($g['total_coeffs_all'] ?? 0);
                            $gPoints = (float) ($g['total_points'] ?? 0);
                            $gCoeffs = (int) ($g['total_coefficients'] ?? 0);
                            $gMgp = $gCoeffs > 0 ? round($gPoints / $gCoeffs, 2) : 0;
                            $totalMGPs += $gMgp;
                        }
                        ?>
                        <tr>
                            <td class="left" style="font-weight: bold;"><?= __('total_mgp') ?> |
                                <?= __('total_coeffs') ?>
                            </td>
                            <td style="font-weight: bold;"><?= formatSimple($totalMGPs) ?></td>
                            <td style="font-weight: bold;"><?= (float) $totalAllCoeffs ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- E. DISCIPLINE ET DÉCISION DU CONSEIL -->
        <table class="container-table compact-layout">
            <tr>
                <!-- Légende -->
                <td width="32%">
                    <div class="compact-note-box">
                        <div class="uppercase compact-note-title"><?= __('legend_appreciation') ?></div>
                        <div class="legend-text" style="font-size: 9px; white-space: nowrap; line-height: 1.2;">
                            CTBA : <?= __('ctba_desc') ?><br>
                            CBA : <?= __('cba_desc') ?><br>
                            CA : <?= __('ca_desc') ?><br>
                            CMA : <?= __('cma_desc') ?><br>
                            CNA : <?= __('cna_desc') ?><br>
                            <strong><?= __('mgp_group') ?></strong>
                        </div>
                    </div>
                </td>
                <!-- Discipline annuelle -->
                <td width="26%">
                    <table class="side-table compact-side">
                        <tr>
                            <th colspan="3"><?= __('conduct') ?></th>
                        </tr>
                        <tr>
                            <td rowspan="3" class="absences-title"><?= strtoupper(__('absences')) ?></td>
                            <td class="left"><?= __('total') ?></td>
                            <td class="bold" width="25%"><?= sprintf('%02d', $discipline['absences']['total']) ?></td>
                        </tr>
                        <tr>
                            <td class="left"><?= __('justified') ?></td>
                            <td class="bold"><?= sprintf('%02d', $discipline['absences']['justified']) ?></td>
                        </tr>
                        <tr>
                            <td class="left"><?= __('unjustified') ?></td>
                            <td class="bold"><?= sprintf('%02d', $discipline['absences']['unjustified']) ?></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="left"><?= __('suspended') ?> (<?= __('days') ?>)</td>
                            <td class="bold"><?= sprintf('%02d', $discipline['exclusion_days']) ?></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="left"><?= __('warn_conduct') ?></td>
                            <td class="bold"><?= $discipline['warning_conduct'] ?></td>
                        </tr>
                    </table>
                </td>
                <!-- Décision annuelle du conseil -->
                <td width="42%">
                    <table class="side-table compact-side">
                        <tr>
                            <th colspan="4"><?= __('council_decision') ?></th>
                        </tr>
                        <tr>
                            <th colspan="2"><?= __('discipline') ?></th>
                            <th colspan="2"><?= __('work') ?></th>
                        </tr>
                        <tr>
                            <td class="left"><?= __('warn_conduct') ?></td>
                            <td><?= $discipline['warning_conduct'] ?></td>
                            <td class="left"><?= __('honour_roll') ?></td>
                            <td class="bold">
                                <span class="<?= ($average >= 12) ? 'vert' : 'rouge' ?>">
                                    <?= ($average >= 12) ? strtoupper(__('yes')) : strtoupper(__('no')) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="left"><?= __('blame_conduct') ?></td>
                            <td class="bold"><?= $discipline['blame_conduct'] ?></td>
                            <td class="left"><?= __('encouragements') ?></td>
                            <td class="bold">
                                <?php
                                $workKey = 'work_bad';
                                if ($average >= 14)
                                    $workKey = 'work_excellent';
                                elseif ($average >= 12)
                                    $workKey = 'work_good';
                                elseif ($average >= 10)
                                    $workKey = 'work_passable';
                                ?>
                                <span class="<?= ($average >= 10) ? 'vert' : 'rouge' ?>">
                                    <?= __($workKey) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="left"><?= __('exclusions') ?> (<?= __('days') ?>)</td>
                            <td class="bold"><?= sprintf('%02d', $discipline['exclusion_days']) ?></td>
                            <td class="left"><?= __('congratulations') ?></td>
                            <td class="bold">
                                <span class="<?= ($average >= 14) ? 'vert' : 'rouge' ?>">
                                    <?= ($average >= 14) ? strtoupper(__('yes')) : strtoupper(__('no')) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="left"><?= __('consignes') ?></td>
                            <td class="bold"><?= sprintf('%02d', $discipline['consignes']) ?></td>
                            <td class="left"><?= __('warn_work') ?></td>
                            <td class="bold">
                                <?php
                                $trendText = '';
                                $trendClass = '';
                                // On filtre les moyennes valides (non nulles/vides)
                                $validTerms = array_filter($termAverages, function ($v) {
                                    return $v !== null && $v !== '' && $v !== '-';
                                });
                                if (count($validTerms) >= 2) {
                                    $termsValues = array_values($validTerms);
                                    $currT = (float) end($termsValues);
                                    $prevT = (float) prev($termsValues);
                                    if ($currT < $prevT) {
                                        $trendText = __('trend_down');
                                        $trendClass = 'rouge';
                                    } else {
                                        $trendText = __('trend_up');
                                        $trendClass = 'vert';
                                    }
                                }
                                ?>
                                <span class="<?= $trendClass ?>"><?= strtoupper((string) $trendText) ?></span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- F. BLOC DES SIGNATURES -->
        <table class="signature-table" style="margin-top: 5px;">
            <tr>
                <td width="33%"><?= __('signature_student_parent') ?></td>
                <td width="33%" style="text-align: center;"><?= __('signature_teacher') ?></td>
                <td width="33%" style="text-align: right;"><?= __('signature_principal') ?></td>
            </tr>
            <tr>
                <td></td>
                <td style="text-align: center;"><?= htmlspecialchars($professor_name ?? '') ?></td>
                <td style="text-align: right;"></td>
            </tr>
        </table>
    </div>

    <?php if (!$embeddedBatch): ?>
    </body>

    </html>
<?php endif; ?>