<?php
/**
 * 1. INITIALISATION DES DONNÉES
 * Ces variables sont transmises par le BulletinController.
 * On utilise l'opérateur de coalescence (??) pour garantir que le script ne plante pas si une donnée est manquante.
 */
$embeddedBatch = $embeddedBatch ?? false;       // Si vrai, indique qu'on génère plusieurs bulletins à la suite
$institution = $institution ?? [];              // Informations sur l'école (nom, logo, etc.)
$activeYear = $activeYear ?? [];               // Année académique en cours
$student = $student ?? [];                     // Données personnelles de l'élève
$rows = $rows ?? [];                           // Lignes de notes par matière
$groupedRows = $groupedRows ?? [];             // Matières regroupées par groupes (ex: Groupe 1, Groupe 2)
$classStats = $classStats ?? [];               // Statistiques de la classe (moyenne, max, min, taux de réussite)
$discipline = $discipline ?? [];               // Données de conduite/discipline
$rank = $rank ?? null;                         // Rang de l'élève
$effectif = $effectif ?? 0;                    // Nombre total d'élèves dans la classe
$average = $average ?? null;                   // Moyenne générale de l'élève
$mention = $mention ?? '';                     // Mention obtenue (ex: Tableau d'honneur)
$total_coefficients = $total_coefficients ?? 0;// Somme des coefficients
$total_coef_valide = $total_coef_valide ?? 0;  // Somme des coefficients où la note est >= 10
$sequence = $sequence ?? [];                   // Détails de la séquence concernée (ex: Séquence 1)
$globalAppreciation = $globalAppreciation ?? '-'; // Appréciation globale du conseil

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
 * Pour s'assurer que le bulletin tient sur une seule page A4, on réduit la taille du texte
 * proportionnellement au nombre de matières (lignes dans le tableau).
 */
$subjectCount = count($rows);
$baseFontSize = 14;
$pageMargin = '0.5cm';
$lineHeight = 1.3;
$logoSize = '85px';

if ($subjectCount >= 10 && $subjectCount <= 12) {
    $baseFontSize = 12;
    $pageMargin = '0.3cm';
    $lineHeight = 1.2;
} elseif ($subjectCount >= 13 && $subjectCount <= 15) {
    $baseFontSize = 11;
    $pageMargin = '0.25cm';
    $lineHeight = 1.15;
} elseif ($subjectCount > 15) {
    $baseFontSize = 10;
    $pageMargin = '0.2cm';
    $lineHeight = 1.1;
    $logoSize = '70px';
}

/**
 * 3. FONCTIONS D'AFFICHAGE (HELPERS)
 * Ces fonctions formatent les données brutes pour le rendu visuel.
 */

// Formate une note avec couleur (vert si >= 10, rouge sinon)
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

// Formate un nombre simple sans couleur (ex: moyenne de classe)
if (!function_exists('formatSimple')) {
    function formatSimple($val)
    {
        if ($val === null || $val === '-') {
            return '-';
        }
        return number_format((float) $val, 2, ',', ' ');
    }
}

// Formate une date au format français (jj/mm/aaaa)
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
        <title><?= htmlspecialchars((string) ($pdf_filename ?? ('bulletin-' . ($sequence['label'] ?? 'sequence')))) ?>
        </title>
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

    <div class="bulletin-wrapper">
        <div class="bulletin-sheet">
        <?php
        /**
         * B. CALCUL DU TITRE DU BULLETIN
         * On extrait le numéro de la séquence (ex: SEQUENCE 1) pour l'afficher proprement.
         */
        $evalTitle = (string) ($sequence['short_label'] ?? $sequence['label'] ?? '');
        if ($evalTitle === '' || stripos($evalTitle, 'Sequence') !== false || stripos($evalTitle, 'Séquence') !== false) {
            if (preg_match('/SEQUENCE\s+\d+/i', (string) ($sequence['label'] ?? ''), $matches)) {
                $evalTitle = strtoupper($matches[0]);
            } else {
                $evalTitle = strtoupper((string) ($sequence['label'] ?? ''));
            }
        } else {
            $evalTitle = strtoupper($evalTitle);
        }

        $evalSummaryLabel = (string) ($sequence['short_label'] ?? 'Sequence');
        if ($evalSummaryLabel === 'Sequence' || stripos($evalSummaryLabel, 'Sequence') !== false) {
            if (preg_match('/SEQUENCE\s+(\d+)/i', (string) ($sequence['label'] ?? ''), $matches)) {
                $evalSummaryLabel = 'Seq ' . $matches[1];
            }
        }
        
        // Inclure le partiel d'entête HTML
        $bulletinType = htmlspecialchars($evalTitle);
        include __DIR__ . '/bulletin_header_html.php';
        ?>

        <!-- D. TABLEAU DES NOTES -->
        <div class="grades-table-wrap">
            <?php if (($lang ?? 'fr') !== 'en'): ?>
                <div class="grades-watermark"><?= htmlspecialchars($schoolCodeWatermark) ?></div>
            <?php endif; ?>
            <?php
            // Découpage dynamique en 3 blocs tout en conservant les mêmes données/calculs.
            $groupedRowsCount = count($groupedRows);
            $groupsPerTable = max(1, (int) ceil($groupedRowsCount / 3));
            $groupedRowsChunks = array_chunk($groupedRows, $groupsPerTable);

            // Garantit toujours 3 tableaux (le 2e et/ou 3e peuvent être vides selon les données).
            while (count($groupedRowsChunks) < 3) {
                $groupedRowsChunks[] = [];
            }
            ?>
            <?php foreach ($groupedRowsChunks as $chunkIndex => $groupsChunk): ?>
                <?php if ($chunkIndex === 0): ?>
                    <table class="grades-table-header">
                        <colgroup>
                            <col style="width:40%;">
                            <col style="width:10%;">
                            <col style="width:5%;">
                            <col style="width:10%;">
                            <col style="width:10%;">
                            <col style="width:10%;">
                            <col style="width:15%;">
                        </colgroup>
                        <tr class="grades-header-row">
                            <th><?= __('subjects') ?></th>
                            <th><?= __('average') ?></th>
                            <th><?= __('coef') ?></th>
                            <th><?= __('weighted_mark') ?></th>
                            <th>TOTAL</th>
                            <th><?= __('rank') ?></th>
                            <th><?= __('appreciation') ?></th>
                        </tr>
                    </table>
                <?php endif; ?>
                <table class="grades-table">
                    <colgroup>
                        <col style="width:40%;">
                        <col style="width:10%;">
                        <col style="width:5%;">
                        <col style="width:10%;">
                        <col style="width:10%;">
                        <col style="width:10%;">
                        <col style="width:15%;">
                    </colgroup>
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
                                    <td><?= formatNote($row['note']) ?></td>
                                    <td><?= (int) $row['coefficient'] ?></td>
                                    <td><?= formatNote($row['weighted']) ?></td>
                                    <td><?= formatSimple($row['weighted']) ?></td>
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
                     * On calcule la somme des points et des coefficients pour déterminer la MGP du groupe.
                     */
                    $groupEvaluationTotal = 0.0;
                    $groupHasEvaluationTotal = false;
                    foreach ($group['rows'] as $groupRow) {
                        if (($groupRow['note'] ?? null) !== null && $groupRow['note'] !== '') {
                            $groupEvaluationTotal += (float) $groupRow['note'];
                            $groupHasEvaluationTotal = true;
                        }
                    }

                    $groupPoints = (float) ($group['total_points'] ?? 0);
                    $groupCoeffs = (int) ($group['total_coefficients'] ?? 0);
                    
                    // Calcul de la moyenne groupée des notes de séquence
                    $groupSeqNotes = [];
                    foreach ($group['rows'] as $groupRow) {
                        if (($groupRow['seq_average'] ?? null) !== null && $groupRow['seq_average'] !== '') {
                            $groupSeqNotes[] = (float) $groupRow['seq_average'];
                        }
                    }
                    $groupAverage = count($groupSeqNotes) > 0 ? round(array_sum($groupSeqNotes) / count($groupSeqNotes), 2) : 0;
                    
                    ?>
                    <table class="group-subtotal-line" style="width: 100%; border-collapse: collapse; border: none; margin: 4px 0 3px; background-color: transparent; color: #333; font-weight: normal; font-size: <?= $baseFontSize + 1 ?>px;">
                        <colgroup>
                            <col style="width:50%;">
                            <col style="width:25%;">
                            <col style="width:5%;">
                            <col style="width:20%;">
                        </colgroup>
                        <tr class="subject-group">
                            <td style="text-align: left; padding: 3px 6px; border: none;"><?= chr(65 + $chunkIndex) ?> - <?= htmlspecialchars($group['label']) ?></td>
                            <td style="text-align: center; padding: 3px 6px; border: none;"><strong><?= formatSimple($groupPoints) ?> Points / <?= (float) ($group['total_coeffs_all'] ?? 0) ?> Coef</strong></td>
                            <td style="text-align: center; padding: 3px 6px; border: none;">&nbsp;</td>
                            <td style="text-align: right; padding: 3px 6px; border: none;"><strong class="<?= $groupAverage >= 10 ? 'vert' : 'rouge' ?>">Moy: <?= formatSimple($groupAverage) ?></strong></td>
                        </tr>
                    </table>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>

        <!-- E. RÉSULTATS GLOBAUX, RÉCAPITULATIF ET DÉCISION DU CONSEIL (TABLEAU UNIFIÉ À 3 COLONNES HORIZONTALES) -->
        <div class="grades-table-wrap">
            <table class="grades-table-header">
                <colgroup>
                    <col style="width:22%;">
                    <col style="width:11%;">
                    <col style="width:22%;">
                    <col style="width:11%;">
                    <col style="width:22%;">
                    <col style="width:12%;">
                </colgroup>
                <!-- LIGNES D'EN-TÊTE PRINCIPALES -->
                <tr class="grades-header-row">
                    <th colspan="2" style="white-space: nowrap; padding-left: 2px; padding-right: 2px;"><?= __('stat_classe_short') ?></th>
                    <th colspan="2" style="white-space: nowrap; padding-left: 2px; padding-right: 2px;"><?= __('synthesis_conduct_short') ?></th>
                    <th colspan="2" style="white-space: nowrap; padding-left: 2px; padding-right: 2px;"><?= __('council_decision_short') ?></th>
                </tr>
            </table>
            <table class="grades-table">
                <colgroup>
                    <col style="width:22%;">
                    <col style="width:11%;">
                    <col style="width:22%;">
                    <col style="width:11%;">
                    <col style="width:22%;">
                    <col style="width:12%;">
                </colgroup>
                <tbody>
            <?php
            // Consolidation des totaux consolidés (TOTAUX et Coefficients)
            $totalAllCoeffs = 0;
            $totalTotals = 0;
            foreach ($groupedRows as $g) {
                $totalAllCoeffs += (float) ($g['total_coeffs_all'] ?? 0);
                $gPoints = (float) ($g['total_points'] ?? 0);
                $totalTotals += $gPoints;
            }
            ?>
            <!-- ROW 1 -->
            <tr>
                <!-- Partie 1: Statistiques & Synthèse -->
                <td><?= __('class_avg_gen') ?></td>
                <td style="text-align: center; font-weight: bold;">
                    <?= formatSimple($classStats['average'] ?? null) ?></td>
                <!-- Partie 2: Période & Absences -->
                <td style="font-weight: bold;">
                    <?= __('period') ?></td>
                <td style="text-align: center; font-weight: bold;">
                    <?= htmlspecialchars($evalTitle) ?></td>
                <!-- Partie 3: Décision du Conseil -->
                <td><?= __('warn_conduct') ?> /
                    <?= __('blame_conduct') ?></td>
                <td style="text-align: center; font-weight: bold;">
                    <?= $discipline['warning_conduct'] ?> / <?= $discipline['blame_conduct'] ?></td>
            </tr>
            <!-- ROW 2 -->
            <tr>
                <td><?= __('avg_max') ?></td>
                <td style="text-align: center; font-weight: bold;">
                    <?= formatSimple($classStats['max'] ?? null) ?></td>
                <td style="font-weight: bold;"><?= __('appreciation') ?>
                </td>
                <td style="text-align: center; font-weight: bold;">
                    <?= htmlspecialchars($globalAppreciation) ?></td>
                <td><?= __('exclusions') ?> / <?= __('consignes') ?>
                </td>
                <td style="text-align: center; font-weight: bold;">
                    <?= sprintf('%02d', $discipline['exclusion_days']) ?>j /
                    <?= sprintf('%02d', $discipline['consignes']) ?></td>
            </tr>
            <!-- ROW 3 -->
            <tr>
                <td><?= __('success_rate') ?></td>
                <td style="text-align: center; font-weight: bold;">
                    <?= isset($classStats['success_rate']) ? formatSimple($classStats['success_rate']) . '%' : '-' ?>
                </td>
                <td style="font-weight: bold;">TOTAL A+B+C <br> <?= __('total_coeffs') ?></td>
                <td style="text-align: center; font-weight: bold;">
                    <?= formatSimple($totalTotals) ?> <br> <?= (float) $totalAllCoeffs ?></td>
                <td><?= __('honour_roll') ?></td>
                <td style="text-align: center; font-weight: bold;">
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
                <td style="font-weight: bold;"><?= __('student_avg') ?></td>
                <td style="text-align: center; font-weight: bold;">
                    <?= formatNote($average) ?></td>
                <td>&bull; <?= __('total') ?> <?= __('absences') ?>
                </td>
                <td style="text-align: center; font-weight: bold;">
                    <?= sprintf('%02d', (int) ($discipline['absences']['total'] ?? 0)) ?></td>
                <td><?= __('encouragements') ?></td>
                <td style="text-align: center; font-weight: bold;">
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
                <td><?= __('student_rank') ?> & <?= __('mention') ?>
                </td>
                <td style="text-align: center; font-weight: bold;">
                    <?= $rank !== null ? $rank . '/' . $effectif : '-' ?> (<?= htmlspecialchars($mention) ?>)</td>
                <td>&bull; <?= __('justified') ?></td>
                <td style="text-align: center; font-weight: bold;">
                    <?= sprintf('%02d', (int) ($discipline['absences']['justified'] ?? 0)) ?></td>
                <td><?= __('congratulations') ?></td>
                <td style="text-align: center; font-weight: bold;">
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
                <td><?= __('general_observation') ?></td>
                <td style="text-align: center; font-weight: bold;">
                    <?= htmlspecialchars($globalAppreciation) ?></td>
                <td>&bull; <?= __('unjustified') ?></td>
                <td style="text-align: center; font-weight: bold;">
                    <?= sprintf('%02d', (int) ($discipline['absences']['unjustified'] ?? 0)) ?></td>
                <td><?= __('warn_work') ?></td>
                <td style="text-align: center; font-weight: bold;">
                    <?= $discipline['warning_work'] ?></td>
            </tr>

            <!-- SECTION 4: LÉGENDE -->
            <tr>
                <td colspan="6"
                    style="padding: 3px; font-size: 8.5px; line-height: 1.1; background-color: #fafafa;">
                    <span
                        style="font-weight: bold; text-decoration: underline;"><?= __('legend_appreciation') ?>:</span>
                    CTBA : <?= __('ctba_desc') ?> | CBA : <?= __('cba_desc') ?> | CA : <?= __('ca_desc') ?> |
                    CMA : <?= __('cma_desc') ?> | CNA : <?= __('cna_desc') ?> |
                    <strong><?= __('mgp_group') ?></strong>
                </td>
            </tr>
                </tbody>
            </table>
        </div>

        <!-- G. BLOC DES SIGNATURES -->
        <div class="grades-table-wrap" style="padding: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <div style="flex: 1;"><?= __('signature_student_parent') ?></div>
                <div style="flex: 1; text-align: center;"><?= __('signature_teacher') ?><?php if ($showTeacherNamesOnBulletins): ?> <?= htmlspecialchars($professor_name ?? '') ?><?php endif; ?></div>
                <div style="flex: 1; text-align: center; font-size: 10px;">
                    <em><?= __('date') ?> : ..............................................</em><br><br>
                    <strong style="font-weight: bold;"><?= __('signature_principal') ?></strong>
                </div>
            </div>
        </div>
    </div>

    <!-- H. BANDE DE PIED DE PAGE (Extérieur du cadre) -->
    <div class="bulletin-footer">
        © NoteMaster - Douala-Cameroun. <?= htmlspecialchars($i['school_code'] ?? '') ?>.camertech.com
    </div>
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