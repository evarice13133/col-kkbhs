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
 * Ce style est injecté directement dans le <head> ou utilisé par Dompdf pour générer le bulletin.
 */
if (isset($styleOnly)) {
    ?>
    * { box-sizing: border-box; }
    @page { size: A4 portrait; margin: <?= $pageMargin ?>; }
    body { font-family: 'Arial', sans-serif; font-size: <?= $baseFontSize ?>px; margin: 0; padding: 0; color: #000;
    background: #fff; line-height: <?= $lineHeight ?>; }
    .bulletin-sheet { width: 100%; margin: 0 auto; page-break-after: always; padding: 0 2px; }
    .bulletin-sheet:last-child { page-break-after: auto; }
    table { width: 99.5%; margin: 0 auto 2px; border-collapse: collapse; table-layout: fixed; }
    th, td { border: 1px solid #000; padding: 1px 2px; text-align: center; }
    th { background-color: #f2f2f2; text-transform: uppercase; }
    .left { text-align: left; }
    .bold { font-weight: bold; }
    .uppercase { text-transform: uppercase; }
    .vert { color: #008000; }
    .rouge { color: #ff0000; }
    .title-box { text-align: center; font-size: 14px; margin: 3px 0 2px; text-transform: uppercase; border: 2px solid #000;
    padding: 2px 3px; }
    .header-wrapper { width: 100%; margin-bottom: 10px; }
    .header-left { float: left; width: 40%; text-align: center; }
    .header-center { float: left; width: 20%; text-align: center; }
    .header-right { float: right; width: 40%; text-align: center; }
    .header-side-content { width: 100%; padding: 0 2px; min-height: 85px; }
    .school-name-row { clear: both; width: 100%; text-align: center; padding: 0; margin: 0; }
    .school-name-display { width: 100%; margin: 0 auto; font-weight: bold; font-size: 12px; text-transform: uppercase;
    border: none; padding: 0; }
    .header-line { font-size: 12px; font-weight: bold; margin: 0; text-transform: uppercase; }
    .header-contact { font-size: 11px; margin: 0; width: 100%; text-align: center; }
    .logo-box { width: <?= $logoSize ?>; height: <?= $logoSize ?>; margin: 0 auto; display: flex; align-items: center;
    justify-content: center;
    overflow: hidden; }
    .logo-box img { width: 100%; height: 100%; object-fit: contain; display: block; }
    .logo-placeholder { width: <?= $logoSize ?>; height: <?= $logoSize ?>; display: flex; align-items: center;
    justify-content: center; font-size:
    8px; font-weight: bold; letter-spacing: 1px; color: #8b97a3; line-height: 1.2; background: #f4f6f8; border-radius: 50%;
    }
    .school-name { font-weight: bold; font-size: 14px; margin-top: 3px; line-height: 1.15; }
    .grades-table-wrap { position: relative; margin-bottom: 2px; }
    .grades-watermark {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    text-align: center;
    padding-top: 120px;
    font-size: 60px;
    font-weight: 800;
    color: #eeeeee;
    letter-spacing: 4px;
    text-transform: uppercase;
    pointer-events: none;
    user-select: none;
    z-index: 0;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
    }
    .grades-table { position: relative; z-index: 1; table-layout: fixed; width: 100%; border-collapse: collapse; }
    .grades-table td:not(.left) { font-size: 0.9em; }
    .teacher-name { font-size: 7px; text-transform: uppercase; display: block; margin-top: 1px; color: #555; }
    .student-info-table {
    table-layout: auto;
    border: none;
    border-radius: 6px;
    margin: 4px 0 7px;
    background: #f8f9fa;
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    }
    .student-info-table td {
    border: none !important;
    text-align: left;
    padding: 3px 6px;
    font-size: <?= $baseFontSize ?>px;
    line-height: 1.3;
    }
    .student-info-table tr + tr td {
    border-top: none !important;
    }
    .student-info-label {
    color: #3a5067;
    font-weight: 600;
    margin-right: 4px;
    }
    .student-info-value {
    font-weight: 700;
    color: #1a1a1a;
    }
    .check-group { font-family: 'Courier New', monospace; white-space: nowrap; }
    .nowrap { white-space: nowrap; }
    .grades-table th { font-size: <?= $baseFontSize ?>px; }
    .grades-table thead th {
    background: #eaf1f8;
    color: #1b3550;
    font-weight: 700;
    letter-spacing: 0.2px;
    text-transform: uppercase;
    padding: 3px 4px;
    border-color: #8ea8c6;
    }
    .group-header { background-color: #e9e9e9; text-align: left; padding-left: 10px; }
    .group-subtotal-line {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    width: 100%;
    margin: 1px 0 3px;
    padding: 2px 7px;
    background: #d9e7f6;
    color: #17324d;
    font-weight: bold;
    white-space: nowrap;
    page-break-inside: avoid;
    break-inside: avoid;
    }
    .group-subtotal-line > span {
    flex: 1 1 0;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    }
    .group-subtotal-line > span:first-child {
    flex: 0 0 40%;
    max-width: 40%;
    }
    .group-subtotal-line > span:last-child {
    text-align: right;
    }
    .teacher-name { font-size: 0.8em; text-transform: uppercase; display: block; margin-top: 1px; }
    .container-table { border: none; margin-bottom: 12px; width: 100%; border-collapse: collapse; }
    .container-table td { border: none; padding: 0; vertical-align: top; }
    .container-table > tr > td:not(:last-child), .container-table > tbody > tr > td:not(:last-child) { padding-right: 15px;
    }
    .compact-layout { margin-bottom: 1px; }
    .compact-layout > tbody > tr > td { padding-right: 0; }
    .compact-layout > tbody > tr > td:last-child { padding-right: 0; }
    .side-table { width: 100%; border: 1px solid #000; }
    .side-table th { font-size: <?= $baseFontSize ?>px; border: 1px solid #000; }
    .side-table td { border: 1px solid #000; padding: 2px; }
    .compact-side { width: 100%; margin-bottom: 0; }
    .compact-side th, .compact-side td { padding: 1px 2px; line-height: 1.1; }
    .rounded-legend { border: 1px solid #000; border-radius: 8px; border-collapse: separate; }
    .signature-table td { border: none; height: 48px; vertical-align: top; padding-top: 3px; }
    .no-border { border: none !important; }
    .absences-title { writing-mode: vertical-rl; transform: rotate(180deg); width: 25px; font-weight: bold; }
    .avg-box { border: 2px solid #000; padding: 5px; text-align: center; font-size: 11px; }
    .compact-note-box { border: 1px solid #000; border-radius: 8px; padding: 3px 4px; min-height: 85px; }
    .compact-note-title { text-align: center; margin-bottom: 2px; }
    .legend-text { font-size: 7px; line-height: 1.1; text-align: left; }
    .summary-total td { background-color: #f7f7f7; font-weight: bold; }
    .compact-value { font-weight: bold; font-size: 10px; }
    /* BARRE D'OUTILS (Visible seulement à l'écran) */
    @media print { .pv-toolbar { display: none !important; } }
    .pv-toolbar {
    position: sticky; top: 0; z-index: 100; display: flex; align-items: center; justify-content: space-between;
    padding: 10px 20px; background: #1a1a2e; color: white; gap: 12px; flex-wrap: wrap; margin-bottom: 15px; font-family:
    Arial, Helvetica, sans-serif;
    }
    .pv-toolbar-title { font-weight: bold; font-size: 13px; opacity: 0.9; }
    .pv-toolbar-hint { font-size: 10px; opacity: 0.6; margin-right: auto; }
    .pv-btn { padding: 7px 18px; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: bold;
    text-decoration: none; transition: opacity 0.2s; display: inline-block; }
    .pv-btn:hover { opacity: 0.85; }
    .pv-btn-print { background: #0d6efd; color: white; }
    .pv-btn-download { background: #198754; color: white; margin-left: 5px; }
    .pv-btn-back { background: rgba(255,255,255,0.15); color: white; margin-right: 5px; }
    @media screen and (max-width: 600px) {
    .pv-toolbar { flex-direction: column; align-items: stretch; gap: 8px; }
    .pv-btn { width: 100%; text-align: center; margin: 2px 0 !important; }
    }
    <?php
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
            </div>
        </div>
    <?php endif; ?>

    <div class="bulletin-sheet">
        <!-- A. EN-TÊTE MINISTÉRIEL ET LOGO -->
        <div class="header-wrapper">
            <div class="header-left">
                <div class="header-side-content">
                    <p class="header-line">
                        <?= htmlspecialchars((string) ($i['school_republic'] ?? __('republic_of_cameroon'))) ?>
                    </p>
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
                            <img src="<?= htmlspecialchars($logoPath) ?>" alt="Logo">
                    <?php else: ?>
                        <div class="logo-placeholder">LOGO</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="header-right">
                <div class="header-side-content">
                    <p class="header-line">
                        <?= htmlspecialchars((string) ($i['school_republic_en'] ?? 'REPUBLIC OF CAMEROON')) ?>
                    </p>
                    <p class="header-line">
                        <?= htmlspecialchars((string) ($i['school_motto_en'] ?? 'PEACE - WORK - FATHERLAND')) ?>
                    </p>
                    <p class="header-line">
                        <?= htmlspecialchars((string) ($i['school_ministry_en'] ?? 'MINISTRY OF SECONDARY EDUCATION')) ?>
                    </p>
                    <p class="header-line">
                        <?= htmlspecialchars((string) ($i['school_slogan_en'] ?? 'DISCIPLINE - WORK - SUCCESS')) ?>
                    </p>
                    <p class="header-contact"><?= htmlspecialchars(strtoupper($contact)) ?></p>
                </div>
            </div>

            <div class="school-name-row">
                <div class="school-name-display"><?= htmlspecialchars($schoolDisplayName) ?></div>
            </div>
        </div>

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
        ?>
        <div class="title-box" style="font-weight: bold;"><?= __('report_card') ?> <?= htmlspecialchars($evalTitle) ?>
        </div>

        <!-- C. CARTE D'IDENTITÉ DE L'ÉLÈVE -->
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
                        <?= __('no') ?></span><?= !$isRedoublant ? '[X]' : '[ ]' ?> </td>
                <td class="nowrap"><span class="student-info-label"><?= __('effectif') ?> :</span><span
                        class="student-info-value"><?= (int) $effectif ?></span>
                </td>
            </tr>
        </table>

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
                    <?php if ($chunkIndex === 0): ?>
                        <thead>
                            <tr class="grades-header-row">
                                <th><?= __('subjects') ?></th>
                                <th><?= __('average') ?></th>
                                <th><?= __('coef') ?></th>
                                <th><?= __('weighted_mark') ?></th>
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
                                    <td><?= formatNote($row['note']) ?></td>
                                    <td><?= (int) $row['coefficient'] ?></td>
                                    <td><?= formatNote($row['weighted']) ?></td>
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
                     * On calcule la somme des points et des coefficients pour déterminer la MGP du groupe.
                     */
                    $groupEvaluationTotal = 0.0;
                    $groupHasEvaluationTotal = false;
                    foreach ($group['rows'] as $groupRow) {
                        if (($groupRow['note'] ?? null) !== null) {
                            $groupEvaluationTotal += (float) $groupRow['note'];
                            $groupHasEvaluationTotal = true;
                        }
                    }

                    $groupPoints = (float) ($group['total_points'] ?? 0);
                    $groupCoeffs = (int) ($group['total_coefficients'] ?? 0);
                    $mgp = $groupCoeffs > 0 ? round($groupPoints / $groupCoeffs, 2) : 0;
                    ?>
                    <div class="group-subtotal-line">
                        <span><?= htmlspecialchars($group['label']) ?></span>
                        <span>T.<?= htmlspecialchars($evalSummaryLabel) ?>:
                            <?= $groupHasEvaluationTotal ? formatSimple($groupEvaluationTotal) : '-' ?></span>
                        <span>T.Coef: <?= (float) ($group['total_coeffs_all'] ?? 0) ?></span>
                        <span><?= strtoupper(__('total')) ?>: <?= formatSimple($groupPoints) ?></span>
                        <span>MGP: <span class="<?= $mgp >= 10 ? 'vert' : 'rouge' ?>"><?= formatSimple($mgp) ?></span></span>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>

        <!-- E. RÉSULTATS GLOBAUX (Statistiques Classe, Résumé Élève, Observations) -->
        <table class="container-table compact-layout">
            <tr>
                <!-- 1. Statistiques de la classe -->
                <td width="29%">
                    <table class="side-table compact-side">
                        <tr>
                            <th colspan="2"><?= __('class_stats') ?></th>
                        </tr>
                        <tr>
                            <td class="left"><?= __('class_avg_gen') ?></td>
                            <td><?= formatSimple($classStats['average']) ?></td>
                        </tr>
                        <tr>
                            <td class="left"><?= __('note_max') ?></td>
                            <td><?= formatSimple($classStats['max']) ?></td>
                        </tr>
                        <tr>
                            <td class="left"><?= __('note_min') ?></td>
                            <td><?= formatSimple($classStats['min']) ?></td>
                        </tr>
                        <tr>
                            <td class="left"><?= __('avg_max') ?></td>
                            <td><?= formatSimple($classStats['max']) ?></td>
                        </tr>
                        <tr>
                            <td class="left"><?= __('success_rate') ?></td>
                            <td><?= formatSimple($classStats['success_rate'] ?? 0) ?>%</td>
                        </tr>
                    </table>
                </td>
                <!-- 2. Résumé de performance de l'élève -->
                <td width="29%">
                    <table class="side-table compact-side">
                        <tr>
                            <th colspan="2"><?= __('student_summary') ?></th>
                        </tr>
                        <tr>
                            <td class="left"><?= __('student_avg') ?></td>
                            <td width="25%"><?= formatNote($average) ?></td>
                        </tr>
                        <tr>
                            <td class="left"><?= __('student_rank') ?></td>
                            <td><?= $rank !== null ? $rank . ' / ' . $effectif : '-' ?></td>
                        </tr>
                        <tr>
                            <td class="left"><?= __('mention') ?></td>
                            <td><?= htmlspecialchars($mention) ?></td>
                        </tr>
                        <?php
                        // Calcul des totaux consolidés (Somme des MGP et Somme des Coefficients)
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
                            <td class="left" style="font-weight: bold;"><?= __('total_mgp') ?></td>
                            <td style="font-weight: bold;"><?= formatSimple($totalMGPs) ?></td>
                        </tr>
                        <tr>
                            <td class="left" style="font-weight: bold;"><?= __('total_coeffs') ?></td>
                            <td style="font-weight: bold;"><?= (float) $totalAllCoeffs ?></td>
                        </tr>
                    </table>
                </td>
                <!-- 3. Observations générales -->
                <td width="42%">
                    <table class="side-table compact-side">
                        <tr>
                            <th colspan="2"><?= __('general_observation') ?></th>
                        </tr>
                        <tr>
                            <td class="left"><?= __('period') ?></td>
                            <td><?= htmlspecialchars($evalTitle) ?></td>
                        </tr>
                        <tr>
                            <td class="left"><?= __('appreciation') ?></td>
                            <td class="left"><?= htmlspecialchars($globalAppreciation) ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- F. DISCIPLINE ET DÉCISION DU CONSEIL -->
        <table class="container-table compact-layout">
            <tr>
                <!-- Légende des acquisitions -->
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
                <!-- Suivi des absences -->
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
                    </table>
                </td>
                <!-- Décisions du conseil (Discipline & Travail) -->
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
                            <td class="bold"><?= $discipline['warning_conduct'] ?></td>
                            <td class="left"><?= __('honour_roll') ?></td>
                            <td class="bold">
                                <?php if ($discipline['tableau_honneur'] === 'X'): ?>
                                    <span class="vert"><?= strtoupper(__('yes')) ?></span>
                                <?php elseif ($discipline['tableau_honneur'] === '' && $average >= 12): ?>
                                    <span class="vert"><?= strtoupper(__('yes')) ?></span>
                                <?php else: ?>
                                    <span class="rouge"><?= strtoupper(__('no')) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="left"><?= __('blame_conduct') ?></td>
                            <td class="bold"><?= $discipline['blame_conduct'] ?></td>
                            <td class="left"><?= __('encouragements') ?></td>
                            <td class="bold">
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
                        <tr>
                            <td class="left"><?= __('exclusions') ?> (<?= __('days') ?>)</td>
                            <td class="bold"><?= sprintf('%02d', $discipline['exclusion_days']) ?></td>
                            <td class="left"><?= __('congratulations') ?></td>
                            <td class="bold">
                                <?php if ($discipline['felicitations'] === 'X'): ?>
                                    <span class="vert"><?= strtoupper(__('yes')) ?></span>
                                <?php elseif ($discipline['felicitations'] === '' && $average >= 14): ?>
                                    <span class="vert"><?= strtoupper(__('yes')) ?></span>
                                <?php else: ?>
                                    <span class="rouge"><?= strtoupper(__('no')) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="left"><?= __('consignes') ?></td>
                            <td class="bold"><?= sprintf('%02d', $discipline['consignes']) ?></td>
                            <td class="left"><?= __('warn_work') ?></td>
                            <td class="bold"><?= $discipline['warning_work'] ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- G. BLOC DES SIGNATURES -->
        <table class="signature-table" style="margin-top: 10px;">
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