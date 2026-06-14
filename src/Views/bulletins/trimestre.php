<?php
/**
 * 1. INITIALISATION DES DONNÉES
 * Variables transmises par le BulletinController.
 */
$embeddedBatch = $embeddedBatch ?? false;
$institution = $institution ?? [];
$activeYear = $activeYear ?? [];
$student = $student ?? [];
$rows = $rows ?? [];
$groupedRows = $groupedRows ?? [];
$classStats = $classStats ?? [];
$discipline = $discipline ?? [];
$rank = $rank ?? null;
$effectif = $effectif ?? 0;
$average = $average ?? null;
$mention = $mention ?? '';
$total_coefficients = $total_coefficients ?? 0;
$total_coef_valide = $total_coef_valide ?? 0;
$term = $term ?? 1;                           // Numéro du trimestre (1, 2 ou 3)
$evaluationLabels = $evaluationLabels ?? ['S1', 'S2', 'Trim']; // Libellés des colonnes (Séquences + Trimestre)
$seqAverages = $seqAverages ?? [];             // Moyennes séquentielles pour le rappel
$seqRanks = $seqRanks ?? [];                // Rangs séquentiels pour le rappel
$globalAppreciation = $globalAppreciation ?? '-';

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
    $logoSize = '70px';
}

/**
 * 3. FONCTIONS D'AFFICHAGE (HELPERS)
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
 * 4. BLOC CSS POUR LE RENDU PDF
 * Utilise le fichier partiel bulletin_header.php
 */
if (isset($styleOnly)) {
    $styleOnly = true;
    include __DIR__ . '/bulletin_header.php';
    return;
}
?>
<?php if (!$embeddedBatch && empty($isPdf)): ?>
    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <title><?= htmlspecialchars((string) ($pdf_filename ?? ('bulletin-trimestre-' . (int) $term))) ?></title>
        <style>
            <?php $styleOnly = true;
            include __DIR__ . '/bulletin_header.php'; ?>
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
                <a href="<?= $_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'], '?') !== false ? '&' : '?') ?>format=pdf"
                    class="pv-btn pv-btn-download">
                    <i class="bi bi-file-pdf"></i> <?= __('pv_download_btn') ?>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <div class="bulletin-sheet">
        <?php
        // Inclure le partiel d'entête HTML
        $bulletinType = strtoupper(__('trimester')) . ' ' . (int) $term;
        include __DIR__ . '/bulletin_header_html.php';
        ?>

        <!-- C. TABLEAU DES NOTES TRIMESTRIELLES -->
        <div class="grades-table-wrap">
            <?php if (($lang ?? 'fr') !== 'en'): ?>
                <div class="grades-watermark"><?= htmlspecialchars($schoolCodeWatermark) ?></div>
            <?php endif; ?>
            <?php
            /**
             * GESTION DYNAMIQUE DES COLONNES DE SÉQUENCES
             * Le bulletin s'adapte au nombre de séquences rattachées au trimestre.
             */
            $numSeqs = count($termSequences);
            // Matière: 30% | Trim: 7% | Coef: 5% | NoteXCoef: 7.5% | MoyCl: 7.5% | Rang: 7% | Appréc: 8%
            // TOTAL FIXE: 30 + 7 + 5 + 7.5 + 7.5 + 7 + 8 = 72%
            // RESTE POUR SÉQUENCES: 28%
            $colWidth = ($numSeqs > 0) ? (28 / $numSeqs) : 28;

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
                        <?php for ($sidx = 0; $sidx < $numSeqs; $sidx++): ?>
                            <col style="width:<?= $colWidth ?>%;">
                        <?php endfor; ?>
                        <col style="width:7%;">
                        <col style="width:5%;">
                        <col style="width:7.5%;">
                        <col style="width:7%;">
                        <col style="width:8%;">
                    </colgroup>
                    <?php if ($chunkIndex === 0): ?>
                        <thead>
                            <tr>
                                <th><?= __('subjects') ?></th>
                                <?php for ($sidx = 0; $sidx < $numSeqs; $sidx++): ?>
                                    <th><?= htmlspecialchars($termSequences[$sidx]['code'] ?? ('S' . ($sidx + 1))) ?></th>
                                <?php endfor; ?>
                                <th><?= __('average_title') ?><?= __('trimester_title') ?></th>
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
                                        <div class="subject-line">
                                            <span class="subject-name"><?= htmlspecialchars($row['subject']) ?></span>
                                            <?php if ($showTeacherNamesOnBulletins): ?>
                                                <span class="teacher-info">Eng: <?= htmlspecialchars($row['teacher']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <?php for ($sidx = 0; $sidx < $numSeqs; $sidx++): ?>
                                        <td><?= isset($row['sequence_values'][$sidx]) ? formatNote($row['sequence_values'][$sidx]) : '-' ?>
                                        </td>
                                    <?php endfor; ?>
                                    <td><?= formatNote($row['term_note']) ?></td>
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
                     * CALCULS DES SOUS-TOTAUX PAR GROUPE
                     * On calcule la somme des moyennes séquentielles et la moyenne trimestrielle du groupe (MGP).
                     */
                    $groupSeqTotals = array_fill(0, $numSeqs, 0.0);
                    $groupSeqHasTotals = array_fill(0, $numSeqs, false);
                    $groupTrimTotal = 0.0;
                    $groupHasTrimTotal = false;
                    foreach ($group['rows'] as $groupRow) {
                        for ($sidx = 0; $sidx < $numSeqs; $sidx++) {
                            if (($groupRow['sequence_values'][$sidx] ?? null) !== null && $groupRow['sequence_values'][$sidx] !== '') {
                                // Somme brute des notes pour chaque séquence
                                $groupSeqTotals[$sidx] += (float) $groupRow['sequence_values'][$sidx];
                                $groupSeqHasTotals[$sidx] = true;
                            }
                        }
                        if (($groupRow['term_note'] ?? null) !== null && $groupRow['term_note'] !== '') {
                            $groupTrimTotal += (float) $groupRow['term_note'];
                            $groupHasTrimTotal = true;
                        }
                    }

                    $groupPoints = (float) ($group['total_points'] ?? 0);
                    $groupCoeffs = (int) ($group['total_coefficients'] ?? 0);
                    $mgp = $groupCoeffs > 0 ? round($groupPoints / $groupCoeffs, 2) : 0;
                    ?>
                    <table class="group-subtotal-line" style="width: 100%; border-collapse: collapse; border: none; margin: 8px 0 5px; background-color: #e8f4e8; color: #333; font-weight: normal; font-size: <?= $baseFontSize + 2 ?>px;">
                        <colgroup>
                            <col style="width:37.5%;">
                            <?php for ($sidx = 0; $sidx < $numSeqs; $sidx++): ?>
                                <col style="width:<?= $colWidth ?>%;">
                            <?php endfor; ?>
                            <col style="width:7%;">
                            <col style="width:5%;">
                            <col style="width:7.5%;">
                            <col style="width:7%;">
                            <col style="width:8%;">
                        </colgroup>
                        <tr>
                            <td style="text-align: left; padding: 6px 8px; border: none;"><?= htmlspecialchars($group['label']) ?></td>
                            <?php for ($sidx = 0; $sidx < $numSeqs; $sidx++): ?>
                                <td style="text-align: center; padding: 6px 8px; border: none;">&nbsp;</td>
                            <?php endfor; ?>
                            <td style="text-align: center; padding: 6px 8px; border: none;" colspan="3"><strong><?= formatSimple($groupPoints) ?> Points / <?= (float) $groupCoeffs ?> Coef</strong></td>
                            <td style="text-align: right; padding: 6px 8px; border: none;" colspan="2"><strong class="<?= $mgp >= 10 ? 'vert' : 'rouge' ?>">MGP: <?= formatSimple($mgp) ?></strong></td>
                        </tr>
                    </table>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>

        <!-- D. RÉSULTATS GLOBAUX, RÉCAPITULATIF ET DÉCISION DU CONSEIL (TABLEAU UNIFIÉ À 3 COLONNES HORIZONTALES) -->
        <table
            style="width: 100%; border: 0.5px solid #000; border-collapse: collapse; font-size: 10px; margin-top: 5px;">
            <!-- LIGNES D'EN-TÊTE PRINCIPALES -->
            <tr style="background-color: #f2f2f2; font-weight: bold; text-align: center;">
                <th colspan="2" style="width: 33%; border: 0.5px solid #000; padding: 3px; font-size: 10px;">
                    <?= strtoupper(__('class_stats')) ?> & <?= strtoupper(__('student_summary')) ?></th>
                <th colspan="2" style="width: 33%; border: 0.5px solid #000; padding: 3px; font-size: 10px;">
                    <?= strtoupper(__('recall')) ?> & <?= strtoupper(__('conduct')) ?></th>
                <th colspan="2" style="width: 34%; border: 0.5px solid #000; padding: 3px; font-size: 10px;">
                    <?= strtoupper(__('council_decision')) ?></th>
            </tr>
            <?php
            // Préparations des variables pour les séquences
            $seq1_label = htmlspecialchars($termSequences[0]['short_label'] ?? 'S1');
            $seq1_val = (isset($seqAverages[0]) ? formatSimple($seqAverages[0]) : '-') . ' (Rg: ' . ($seqRanks[0] ?? '-') . ')';
            $seq2_label = htmlspecialchars($termSequences[1]['short_label'] ?? 'S2');
            $seq2_val = (isset($seqAverages[1]) ? formatSimple($seqAverages[1]) : '-') . ' (Rg: ' . ($seqRanks[1] ?? '-') . ')';

            // Consolidation des totaux consolidés (MGP et Coefficients)
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
            <!-- ROW 1 -->
            <tr>
                <!-- Partie 1: Statistiques & Synthèse -->
                <td style="width: 22%; border: 0.5px solid #000; padding: 2px 4px;"><?= __('class_avg_gen') ?></td>
                <td
                    style="width: 11%; border: 0.5px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">
                    <?= formatSimple($classStats['average'] ?? null) ?></td>
                <!-- Partie 2: Rappels & Absences -->
                <td style="width: 22%; border: 0.5px solid #000; padding: 2px 4px;"><?= $seq1_label ?></td>
                <td
                    style="width: 11%; border: 0.5px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">
                    <?= $seq1_val ?></td>
                <!-- Partie 3: Décision du Conseil -->
                <td style="width: 22%; border: 0.5px solid #000; padding: 2px 4px;"><?= __('warn_conduct') ?> /
                    <?= __('blame_conduct') ?></td>
                <td
                    style="width: 12%; border: 0.5px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">
                    <?= $discipline['warning_conduct'] ?> / <?= $discipline['blame_conduct'] ?></td>
            </tr>
            <!-- ROW 2 -->
            <tr>
                <td style="border: 0.5px solid #000; padding: 2px 4px;"><?= __('avg_max') ?></td>
                <td style="border: 0.5px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">
                    <?= formatSimple($classStats['max'] ?? null) ?></td>
                <td style="border: 0.5px solid #000; padding: 2px 4px;"><?= $seq2_label ?></td>
                <td style="border: 0.5px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">
                    <?= $seq2_val ?></td>
                <td style="border: 0.5px solid #000; padding: 2px 4px;"><?= __('exclusions') ?> / <?= __('consignes') ?>
                </td>
                <td style="border: 0.5px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">
                    <?= sprintf('%02d', (int) ($discipline['exclusion_days'] ?? 0)) ?>j /
                    <?= sprintf('%02d', (int) ($discipline['consignes'] ?? 0)) ?></td>
            </tr>
            <!-- ROW 3 -->
            <tr>
                <td style="border: 0.5px solid #000; padding: 2px 4px;"><?= __('success_rate') ?></td>
                <td style="border: 0.5px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">
                    <?= isset($classStats['success_rate']) ? formatSimple($classStats['success_rate']) . '%' : '-' ?>
                </td>
                <td style="border: 0.5px solid #000; padding: 2px 4px; font-weight: bold;"><?= __('total_mgp') ?> /
                    <?= __('total_coeffs') ?></td>
                <td style="border: 0.5px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">
                    <?= formatSimple($totalMGPs) ?> / <?= (float) $totalAllCoeffs ?></td>
                <td style="border: 0.5px solid #000; padding: 2px 4px;"><?= __('honour_roll') ?></td>
                <td style="border: 0.5px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">
                    <?php if ($discipline['tableau_honneur'] === 'X'): ?>
                        <span class="vert"><?= strtoupper(__('yes')) ?></span>
                    <?php elseif ($discipline['tableau_honneur'] === '' && $average >= 12): ?>
                        <span class="vert"><?= strtoupper(__('yes')) ?></span>
                    <?php else: ?>
                        <span class="rouge"><?= strtoupper(__('no')) ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <!-- ROW 4 -->
            <tr>
                <td style="border: 0.5px solid #000; padding: 2px 4px; font-weight: bold;"><?= __('student_avg') ?></td>
                <td
                    style="border: 0.5px solid #000; padding: 2px 4px; text-align: center; font-weight: bold; background-color: #fafafa;">
                    <?= formatNote($average) ?></td>
                <td style="border: 0.5px solid #000; padding: 2px 4px;">&bull; <?= __('total') ?> <?= __('absences') ?>
                </td>
                <td style="border: 0.5px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">
                    <?= sprintf('%02d', (int) ($discipline['absences']['total'] ?? 0)) ?></td>
                <td style="border: 0.5px solid #000; padding: 2px 4px;"><?= __('encouragements') ?></td>
                <td style="border: 0.5px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">
                    <?php if ($discipline['encouragements'] === 'X'): ?>
                        <span class="vert"><?= __('work_good') ?></span>
                    <?php else: ?>
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
                    <?php endif; ?>
                </td>
            </tr>
            <!-- ROW 5 -->
            <tr>
                <td style="border: 0.5px solid #000; padding: 2px 4px;"><?= __('student_rank') ?> & <?= __('mention') ?>
                </td>
                <td style="border: 0.5px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">
                    <?= $rank !== null ? $rank . '/' . $effectif : '-' ?> (<?= htmlspecialchars($mention) ?>)</td>
                <td style="border: 0.5px solid #000; padding: 2px 4px;">&bull; <?= __('justified') ?></td>
                <td style="border: 0.5px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">
                    <?= sprintf('%02d', (int) ($discipline['absences']['justified'] ?? 0)) ?></td>
                <td style="border: 0.5px solid #000; padding: 2px 4px;"><?= __('congratulations') ?></td>
                <td style="border: 0.5px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">
                    <?php if ($discipline['felicitations'] === 'X'): ?>
                        <span class="vert"><?= strtoupper(__('yes')) ?></span>
                    <?php elseif ($discipline['felicitations'] === '' && $average >= 14): ?>
                        <span class="vert"><?= strtoupper(__('yes')) ?></span>
                    <?php else: ?>
                        <span class="rouge"><?= strtoupper(__('no')) ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <!-- ROW 6 -->
            <tr>
                <td style="border: 0.5px solid #000; padding: 2px 4px;"><?= __('general_observation') ?></td>
                <td style="border: 0.5px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">
                    <?= htmlspecialchars($globalAppreciation) ?></td>
                <td style="border: 0.5px solid #000; padding: 2px 4px;">&bull; <?= __('unjustified') ?></td>
                <td style="border: 0.5px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">
                    <?= sprintf('%02d', (int) ($discipline['absences']['unjustified'] ?? 0)) ?></td>
                <td style="border: 0.5px solid #000; padding: 2px 4px;"><?= __('warn_work') ?></td>
                <td style="border: 0.5px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">
                    <?php if ($discipline['warning_work'] === 'X'): ?>
                        <span class="rouge"><?= strtoupper(__('yes')) ?></span>
                    <?php else: ?>
                        <?php
                        $trendText = '';
                        $trendClass = '';
                        if (count($seqAverages) >= 2) {
                            $currS = (float) $seqAverages[count($seqAverages) - 1];
                            $prevS = (float) $seqAverages[count($seqAverages) - 2];
                            if ($currS < $prevS) {
                                $trendText = __('trend_down');
                                $trendClass = 'rouge';
                            } else {
                                $trendText = __('trend_up');
                                $trendClass = 'vert';
                            }
                        }
                        ?>
                        <span class="<?= $trendClass ?>"><?= strtoupper((string) $trendText) ?></span>
                    <?php endif; ?>
                </td>
            </tr>

            <!-- SECTION 4: LÉGENDE -->
            <tr>
                <td colspan="6"
                    style="border: 0.5px solid #000; padding: 3px; font-size: 8.5px; line-height: 1.1; background-color: #fafafa;">
                    <span
                        style="font-weight: bold; text-decoration: underline;"><?= __('legend_appreciation') ?>:</span>
                    CTBA : <?= __('ctba_desc') ?> | CBA : <?= __('cba_desc') ?> | CA : <?= __('ca_desc') ?> |
                    CMA : <?= __('cma_desc') ?> | CNA : <?= __('cna_desc') ?> |
                    <strong><?= __('mgp_group') ?></strong>
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
                <td style="text-align: center;"><?php if ($showTeacherNamesOnBulletins): ?><?= htmlspecialchars($professor_name ?? '') ?><?php endif; ?></td>
                <td style="text-align: right;"></td>
            </tr>
        </table>
    </div>

    <?php if (!$embeddedBatch): ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const downloadBtns = document.querySelectorAll('.pv-btn-download');
            downloadBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    showPdfGuidance();
                });
            });
        });

        function showPdfGuidance() {
            let modal = document.getElementById('pdf-guidance-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'pdf-guidance-modal';
                modal.innerHTML = `
                    <div class="pdf-modal-backdrop" onclick="closePdfGuidance()"></div>
                    <div class="pdf-modal-card">
                        <div class="pdf-modal-header">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="#ffd700" viewBox="0 0 16 16" style="vertical-align: middle;">
                                <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                                <path d="M4.603 14.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.697 19.697 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .471.215c.15.18-.162 1.305-.162 1.305v.006c-.316.427-.58.111-.58.111s.54.407.728.846c.155.362.29.74.405 1.134.208.718.36 1.4.453 1.954.555.15 1.144.33 1.705.513.29.096.55.195.74.296.262.138.45.321.492.51.042.19.014.39-.115.546-.129.155-.327.24-.546.269-.219.03-.466-.02-.713-.102a4.954 4.954 0 0 1-1.396-.757c-.88-.705-1.58-1.748-1.9-2.235-.351.054-.7.108-1.049.157-.428.06-1.08.125-1.764.125-.453.03-.9.08-1.332.146-.356.055-.705.12-1.05.19-.24.049-.49.123-.715.22z"/>
                            </svg>
                            <h2>Enregistrement PDF Premium</h2>
                        </div>
                        <div class="pdf-modal-body">
                            <p>Pour exporter votre bulletin avec une <strong>qualité d'impression absolue</strong> (textes parfaits, aucun décalage de tableau, aucune coupure de page) :</p>
                            <div class="pdf-step">
                                <span class="pdf-step-num">1</span>
                                <span class="pdf-step-text">La fenêtre d'impression système va s'ouvrir.</span>
                            </div>
                            <div class="pdf-step">
                                <span class="pdf-step-num">2</span>
                                <span class="pdf-step-text">Dans la case <strong>"Destination"</strong>, sélectionnez <strong>"Enregistrer au format PDF"</strong>.</span>
                            </div>
                            <div class="pdf-step">
                                <span class="pdf-step-num">3</span>
                                <span class="pdf-step-text">Cliquez sur le bouton bleu <strong>"Enregistrer"</strong>.</span>
                            </div>
                        </div>
                        <div class="pdf-modal-footer">
                            <button class="pdf-modal-btn cancel" onclick="closePdfGuidance()">Annuler</button>
                            <button class="pdf-modal-btn confirm" onclick="launchPdfPrint()">Lancer l'Enregistrement PDF</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
            modal.style.display = 'block';
        }

        function closePdfGuidance() {
            const modal = document.getElementById('pdf-guidance-modal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        function launchPdfPrint() {
            closePdfGuidance();
            setTimeout(() => {
                window.print();
            }, 300);
        }
        </script>
    </body>

    </html>
<?php endif; ?>