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
    .bulletin-sheet { width: 100%; margin: 0 auto; page-break-after: always; padding: 5px; border: 2px solid green; }
    .bulletin-sheet:last-child { page-break-after: auto; }
    table { width: 99.5%; margin: 0 auto 2px; border-collapse: collapse; table-layout: fixed; border: 1px solid green; }
    th, td { border: 1px solid green; padding: 3px 5px; text-align: center; color: black; }
    th { background-color: green; color: white; text-transform: uppercase; font-weight: bold; }
    .left { text-align: left; }
    .bold { font-weight: bold; }
    .uppercase { text-transform: uppercase; }
    .vert { color: green; font-weight: bold; }
    .rouge { color: #ff0000; }
    .title-box { text-align: center; font-size: 16px; margin: 3px 0 2px; text-transform: uppercase; border: 2px solid #000;
    padding: 2px 3px; }
    .header-wrapper { width: 100%; margin-bottom: 10px; }
    .header-left { float: left; width: 40%; text-align: center; }
    .header-center { float: left; width: 20%; text-align: center; }
    .header-right { float: right; width: 40%; text-align: center; }
    .header-side-content { width: 100%; padding: 0 2px; min-height: 85px; }
    .school-name-row { clear: both; width: 100%; text-align: center; padding: 0; margin: 0; }
    .school-name-display { width: 100%; margin: 0 auto; font-weight: 900; font-size: 16px; text-transform: uppercase;
    border: none; padding: 0; }
    .academic-year-display { width: 100%; margin: 2px auto 0; font-weight: 700; font-size: 14px; text-transform: uppercase;
    border: none; padding: 0; }
    .header-line { font-size: 12px; font-weight: bold; margin: 0; text-transform: uppercase; }
    .header-contact { font-size: 11px; margin: 0; width: 100%; text-align: center; opacity: 0.9; }
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
    padding-top: 150px;
    font-size: 70px;
    font-weight: 900;
    color: #f2f2f2;
    letter-spacing: 5px;
    text-transform: uppercase;
    pointer-events: none;
    user-select: none;
    z-index: -1;
    transform: rotate(-30deg);
    opacity: 0.4;
    }
    .grades-table { position: relative; z-index: 1; table-layout: fixed; width: 100%; border-collapse: collapse; }
    .grades-table td:not(.left) { font-size: 0.9em; }
    .subject-line { display: flex; justify-content: space-between; align-items: center; width: 100%; }
    .subject-name { font-weight: bold; font-size: <?= $baseFontSize + 2 ?>px; color: black; }
    .teacher-info { font-size: <?= $baseFontSize - 3 ?>px; font-style: italic; color: #666; font-weight: normal; }
    .student-info-table {
    table-layout: auto;
    border: none;
    margin: 10px 0 15px;
    background: transparent;
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    }
    .student-info-table td {
    border: none !important;
    text-align: left;
    padding: 8px 12px;
    font-size: <?= $baseFontSize + 3 ?>px;
    line-height: 1.5;
    }
    .student-info-table tr + tr td {
    border-top: none !important;
    }
    .student-info-label {
    color: #1a5f1a;
    font-weight: 700;
    margin-right: 8px;
    }
    .student-info-value {
    font-weight: 700;
    color: black;
    font-size: <?= $baseFontSize + 2 ?>px;
    }
    .check-group { font-family: 'Courier New', monospace; white-space: nowrap; }
    .nowrap { white-space: nowrap; }
    .grades-table th { font-size: <?= $baseFontSize ?>px; word-wrap: break-word; overflow-wrap: break-word; }
    .grades-table thead th {
    background: green;
    color: white;
    font-weight: 700;
    letter-spacing: 0.2px;
    text-transform: uppercase;
    padding: 3px 5px;
    border-color: green;
    }
    .group-header { background-color: #e9e9e9; text-align: left; padding-left: 10px; }
    .group-subtotal-line {
    width: 100%;
    margin: 8px 0 5px;
    background: #e8f4e8;
    color: #333;
    font-weight: normal;
    font-size: <?= $baseFontSize + 2 ?>px;
    text-transform: uppercase;
    white-space: nowrap;
    page-break-inside: avoid;
    break-inside: avoid;
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
    .absences-title { text-align: center; vertical-align: middle; width: 15px; font-weight: bold; font-size: 7px;
    line-height: 0.85; }
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
    .bulletin-sheet { overflow-x: hidden; }
    .grades-table { table-layout: auto; word-wrap: break-word; }
    .grades-table th, .grades-table td { word-wrap: break-word; overflow-wrap: break-word; }
    .student-info-table { display: block; overflow-x: auto; }
    .student-info-table td { display: block; width: 100%; padding: 6px 8px; border-bottom: none !important; }
    .student-info-table tr { display: block; margin-bottom: 5px; }
    .header-wrapper { display: flex; flex-direction: column; }
    .header-left, .header-center, .header-right { width: 100%; float: none; margin-bottom: 5px; }
    .school-name-display { font-size: 14px; }
    .academic-year-display { font-size: 12px; }
    }
    @media print {
    .bulletin-sheet { page-break-inside: avoid; border: 2px solid green; padding: 5px; }
    table { page-break-inside: avoid; }
    tr { page-break-inside: avoid; }
    th, td { border: 1px solid green; }
    }
    
    /* MODAL DE GUIDAGE PDF EN HAUTE FIDÉLITÉ */
    #pdf-guidance-modal {
        display: none;
        position: fixed;
        top: 0; left: 0; width: 100vw; height: 100vh;
        z-index: 10000;
        font-family: Arial, Helvetica, sans-serif;
    }
    .pdf-modal-backdrop {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
    .pdf-modal-card {
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        width: 90%;
        max-width: 480px;
        background: rgba(30, 41, 59, 0.98);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 16px;
        padding: 24px;
        color: white;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        animation: modalScaleUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    @keyframes modalScaleUp {
        from { transform: translate(-50%, -45%) scale(0.95); opacity: 0; }
        to { transform: translate(-50%, -50%) scale(1); opacity: 1; }
    }
    .pdf-modal-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 12px;
    }
    .pdf-modal-header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #ffd700;
    }
    .pdf-modal-body p {
        margin: 0 0 16px;
        font-size: 13px;
        line-height: 1.5;
        color: #cbd5e1;
    }
    .pdf-step {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 12px;
        background: rgba(255, 255, 255, 0.05);
        padding: 10px 14px;
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        text-align: left;
    }
    .pdf-step-num {
        background: #ffd700;
        color: #0f172a;
        width: 22px; height: 22px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 12px;
        flex-shrink: 0;
    }
    .pdf-step-text {
        font-size: 12.5px;
        line-height: 1.4;
        color: #f1f5f9;
    }
    .pdf-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 20px;
    }
    .pdf-modal-btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 13px;
        font-weight: bold;
        transition: all 0.2s;
    }
    .pdf-modal-btn.cancel {
        background: rgba(255, 255, 255, 0.1);
        color: #cbd5e1;
    }
    .pdf-modal-btn.cancel:hover {
        background: rgba(255, 255, 255, 0.15);
        color: white;
    }
    .pdf-modal-btn.confirm {
        background: #ffd700;
        color: #0f172a;
        box-shadow: 0 4px 12px rgba(255, 215, 0, 0.2);
    }
    .pdf-modal-btn.confirm:hover {
        background: #ffea00;
        box-shadow: 0 4px 16px rgba(255, 215, 0, 0.35);
    }
    @media print {
        #pdf-guidance-modal { display: none !important; }
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
                <a href="<?= $_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'], '?') !== false ? '&' : '?') ?>format=pdf"
                    class="pv-btn pv-btn-download">
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
                <div class="academic-year-display">ANNEE : <?= htmlspecialchars((string) ($activeYear['nom'] ?? '')) ?></div>
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
                        if (($groupRow['note'] ?? null) !== null && $groupRow['note'] !== '') {
                            $groupEvaluationTotal += (float) $groupRow['note'];
                            $groupHasEvaluationTotal = true;
                        }
                    }

                    $groupPoints = (float) ($group['total_points'] ?? 0);
                    $groupCoeffs = (int) ($group['total_coefficients'] ?? 0);
                    $mgp = $groupCoeffs > 0 ? round($groupPoints / $groupCoeffs, 2) : 0;
                    ?>
                    <table class="group-subtotal-line" style="width: 100%; border-collapse: collapse; border: none; margin: 8px 0 5px; background-color: #e8f4e8; color: #333; font-weight: normal; font-size: <?= $baseFontSize + 2 ?>px;">
                        <colgroup>
                            <col style="width:40%;">
                            <col style="width:10%;">
                            <col style="width:5%;">
                            <col style="width:10%;">
                            <col style="width:10%;">
                            <col style="width:10%;">
                            <col style="width:15%;">
                        </colgroup>
                        <tr>
                            <td style="text-align: left; padding: 6px 8px; border: none;"><?= htmlspecialchars($group['label']) ?></td>
                            <td style="text-align: center; padding: 6px 8px; border: none;"><strong><?= $groupHasEvaluationTotal ? formatSimple($groupEvaluationTotal) : '-' ?></strong></td>
                            <td style="text-align: center; padding: 6px 8px; border: none;" colspan="3"><strong><?= formatSimple($groupPoints) ?> Points / <?= (float) ($group['total_coeffs_all'] ?? 0) ?> Coef</strong></td>
                            <td style="text-align: center; padding: 6px 8px; border: none;">&nbsp;</td>
                            <td style="text-align: right; padding: 6px 8px; border: none;" colspan="2"><strong class="<?= $mgp >= 10 ? 'vert' : 'rouge' ?>">MGP: <?= formatSimple($mgp) ?></strong></td>
                        </tr>
                    </table>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>

        <!-- E. RÉSULTATS GLOBAUX, RÉCAPITULATIF ET DÉCISION DU CONSEIL (TABLEAU UNIFIÉ À 3 COLONNES HORIZONTALES) -->
        <table
            style="width: 100%; border: 0.5px solid #000; border-collapse: collapse; font-size: 10px; margin-top: 5px;">
            <!-- LIGNES D'EN-TÊTE PRINCIPALES -->
            <tr style="background-color: #f2f2f2; font-weight: bold; text-align: center;">
                <th colspan="2" style="width: 33%; border: 0.5px solid #000; padding: 3px; font-size: 10px;">
                    <?= strtoupper(__('class_stats')) ?> & <?= strtoupper(__('student_summary')) ?></th>
                <th colspan="2" style="width: 33%; border: 0.5px solid #000; padding: 3px; font-size: 10px;">
                    <?= strtoupper(__('general_observation')) ?> & <?= strtoupper(__('conduct')) ?></th>
                <th colspan="2" style="width: 34%; border: 0.5px solid #000; padding: 3px; font-size: 10px;">
                    <?= strtoupper(__('council_decision')) ?></th>
            </tr>
            <?php
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
                <!-- Partie 2: Période & Absences -->
                <td style="width: 22%; border: 0.5px solid #000; padding: 2px 4px; font-weight: bold;">
                    <?= __('period') ?></td>
                <td
                    style="width: 11%; border: 0.5px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">
                    <?= htmlspecialchars($evalTitle) ?></td>
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
                <td style="border: 0.5px solid #000; padding: 2px 4px; font-weight: bold;"><?= __('appreciation') ?>
                </td>
                <td style="border: 0.5px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">
                    <?= htmlspecialchars($globalAppreciation) ?></td>
                <td style="border: 0.5px solid #000; padding: 2px 4px;"><?= __('exclusions') ?> / <?= __('consignes') ?>
                </td>
                <td style="border: 0.5px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">
                    <?= sprintf('%02d', $discipline['exclusion_days']) ?>j /
                    <?= sprintf('%02d', $discipline['consignes']) ?></td>
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
                    <?= $discipline['warning_work'] ?></td>
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

        <!-- G. BLOC DES SIGNATURES -->
        <table class="signature-table" style="margin-top: 10px;">
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