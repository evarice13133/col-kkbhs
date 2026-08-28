<?php
/**
 * BULLETIN HEADER PARTIAL
 * Contient les parties communes à tous les types de bulletins (annuel, trimestre, sequence)
 * Utilisé pour faciliter la maintenance et éviter la duplication de code.
 * 
 * Variables requises :
 * - $institution : Informations sur l'école
 * - $activeYear : Année académique en cours
 * - $student : Données personnelles de l'élève
 * - $embeddedBatch : Si vrai, indique qu'on génère plusieurs bulletins à la suite
 * - $isPdf : Si vrai, indique qu'on est en mode PDF
 * - $baseFontSize : Taille de police de base
 * - $pageMargin : Marge de page
 * - $lineHeight : Hauteur de ligne
 * - $logoSize : Taille du logo
 * - $lang : Langue (fr/en)
 * - $contact : Informations de contact
 * - $studentLastName : Nom de l'étudiant en majuscules
 * - $schoolDisplayName : Nom de l'école en majuscules
 * - $schoolCodeWatermark : Code de l'école pour le filigrane
 * - $birthDate : Date de naissance
 * - $birthPlace : Lieu de naissance
 * - $isRedoublant : Si l'élève redouble
 * - $effectif : Effectif de la classe
 * - $showTeacherNamesOnBulletins : Si on affiche les noms des enseignants
 * - $displayMatricule : Matricule à afficher
 * - $bulletinType : Type de bulletin (annual, trimester, sequence)
 * - $pdf_filename : Nom du fichier PDF
 */

// Vérifier si on est en mode styleOnly pour le CSS
if (isset($styleOnly)) {
    ?>
    * { box-sizing: border-box; }
    @page { size: A4 portrait; margin: <?= $pageMargin ?>; }
    @page :first { margin-top: <?= $pageMargin ?>; }
    body { font-family: 'Arial', sans-serif; font-size: <?= $baseFontSize ?>px; margin: 0; padding: 0; color: #000;
    background: #fff; line-height: <?= $lineHeight ?>; }
    .bulletin-sheet { width: 100%; min-height: 0; margin: 0 auto; display: flex; flex: 1 1 auto; flex-direction: column; page-break-after: auto; page-break-inside: avoid; padding: 3px; border: none; }
    .bulletin-sheet:last-child { page-break-after: auto; }
    .bulletin-wrapper { width: 100%; min-height: calc(297mm - <?= $pageMargin ?> - <?= $pageMargin ?>); display: flex; flex-direction: column; page-break-after: always; page-break-inside: avoid; margin: 0 auto 10px; padding: 3px; border: 2px solid #14347a; }
    table { width: 99.5%; margin: 0 auto 1px; border-collapse: collapse; table-layout: fixed; border: 1px solid #14347a; }
    th, td { border: 1px solid #14347a; padding: 2px 4px; text-align: center; color: black; }
    th { background-color: #14347a; color: white; text-transform: uppercase; font-weight: bold; border: 2px solid #14347a; }
    .grades-header-row th { border: 2px solid #14347a; background-color: #14347a; color: white; }
    tbody tr:first-child td { border-top: 2px solid #14347a; }
    .subject-group { background: #d9e8fb; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .left { text-align: left; }
    .bold { font-weight: bold; }
    .uppercase { text-transform: uppercase; }
    .vert { color: #198754; font-weight: bold; }
    .rouge { color: #ff0000; }
    .title-box { display: table; text-align: center; font-family: 'Arial Black', Arial, sans-serif; font-size: 19px; margin: 8px auto 5px; text-transform: uppercase;
    padding: 2px 3px; }
    .header-wrapper { width: 100%; display: grid; grid-template-columns: 1fr auto 1fr; align-items: start; column-gap: 8px; margin-bottom: 5px; }
    .header-left, .header-center, .header-right { min-width: 0; }
    .header-left { text-align: left; }
    .header-center { display: flex; flex-direction: column; align-items: center; text-align: center; }
    .header-right { text-align: right; }
    .header-side-content { width: 100%; padding: 0 2px; }
    .school-name-display, .academic-year-display { margin: 0; line-height: 1.15; }
    .school-name-display { font-family: 'Arial Black', Arial, sans-serif; font-weight: 900; font-size: 19px; color: #0057b8; text-transform: uppercase; }
    .academic-year-display { margin-top: 1px; font-weight: 700; font-size: 17px; text-transform: uppercase; }
    .header-line { font-size: 16px; font-weight: bold; margin: 0; line-height: 1.15; text-transform: uppercase; }
    .header-contact { font-size: 15px; margin: 0; line-height: 1.15; }
    .header-left .header-line { font-size: 20px; color: #0057b8; }
    .header-left .ministry-line { font-size: 17px; color: #000; }
    .header-left .header-contact { font-size: 17px; }
    .header-contact-label { color: #0057b8; }
    .header-contact-value { color: #000; font-weight: 700; }
    .header-right .header-line { font-size: 15px; }
    .header-separator { margin: 0; font-size: 8px; line-height: 1; color: #000; }
    .logo-box { width: <?= $logoSize ?>; height: <?= $logoSize ?>; margin: 0 auto; display: flex; align-items: center;
    justify-content: center;
    overflow: hidden; }
    .logo-box img { width: 100%; height: 100%; object-fit: contain; display: block; }
    .logo-placeholder { width: <?= $logoSize ?>; height: <?= $logoSize ?>; display: flex; align-items: center;
    justify-content: center; font-size:
    8px; font-weight: bold; letter-spacing: 1px; color: #8b97a3; line-height: 1.2; background: #f4f6f8; border-radius: 50%;
    }
    .school-name { font-weight: bold; font-size: 13px; margin-top: 1px; line-height: 1.1; }
    .grades-table-wrap { position: relative; margin-bottom: 1px; }
    .grades-watermark {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    text-align: center;
    padding-top: 120px;
    font-size: 60px;
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
    .grades-table { position: relative; z-index: 1; table-layout: fixed; width: 100%; border-collapse: collapse; page-break-inside: avoid; }
    .grades-table-header { table-layout: fixed; width: 100%; border-collapse: collapse; }
    .grades-table td:not(.left) { font-size: 0.85em; }
    .subject-line { display: flex; justify-content: space-between; align-items: center; width: 100%; }
    .subject-name { font-weight: bold; font-size: <?= $baseFontSize + 1 ?>px; color: black; }
    .teacher-info { font-size: <?= $baseFontSize - 4 ?>px; font-style: italic; color: #666; font-weight: normal; }
    .student-info-table {
    table-layout: auto;
    border: none;
    margin: 0;
    background: transparent;
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    }
    .student-info-table tr { margin: 0; padding: 0; line-height: 1; }
    .student-info-table td { border: 1px solid #14347a !important; text-align: left; padding: 0 4px; font-size: <?= $baseFontSize + 2 ?>px; line-height: 1; margin: 0; }
    .student-info-table tr + tr td { border-top: 1px solid #14347a !important; }
    .department-banner {
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    font-weight: bold;
    color: #14347A;
    margin: 10px 0;
    padding: 3px 0;
    text-transform: uppercase;
    font-size: 14px;
    letter-spacing: .2px;
    }
    .department-banner::before,
    .department-banner::after {
    content: "";
    flex: 1;
    height: 2px;
    background: linear-gradient(to right, transparent 0%, #9aadd1 16%, #14347A 42%, #14347A 58%, #9aadd1 84%, transparent 100%);
    box-shadow: 0 1px 0 rgba(20, 52, 122, .16);
    margin: 0 14px;
    }
    .department-label {
    font-size: 10px;
    font-weight: 700;
    color: #52658d;
    }
    .department-label::after {
    content: "";
    display: inline-block;
    width: 3px;
    height: 3px;
    margin: 0 5px 2px 4px;
    border-radius: 50%;
    background: #d4a72c;
    }
    .department-name {
    font-size: 12px;
    font-weight: 800;
    color: #14347A;
    }
    }
    .student-photo-cell {
    width: 55px;
    height: 100%;
    vertical-align: middle;
    padding: 0;
    margin: 0;
    border-right: 1px solid #14347a !important;
    }
    .student-photo-container {
    width: 55px;
    height: 100%;
    min-height: 84px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    max-width: 100%;
    max-height: 100%;
    padding: 0;
    margin: 0;
    }
    .student-photo-container img {
    width: 100%;
    height: 100%;
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    object-position: center;
    display: block;
    overflow: hidden;
    margin: 0;
    }
    .student-photo-placeholder {
    width: 51px;
    height: 59px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    font-size: 18px;
    color: #000;
    line-height: 1;
    margin: 0;
    padding: 0;
    }
    .student-identity-row {
    padding: 0 4px;
    border: 1px solid #14347a !important;
    line-height: 1;
    margin: 0;
    }
    .student-info-table td > * { margin: 0; padding: 0; }
    .student-identity-label {
    font-weight: 700;
    margin-right: 3px;
    font-size: <?= $baseFontSize - 1 ?>px;
    color: #000;
    }
    .student-identity-value {
    font-weight: 700;
    color: #0057b8;
    font-size: <?= $baseFontSize - 1 ?>px;
    }
    .student-identity-item {
    display: inline-block;
    margin-right: 12px;
    }
    .student-identity-item:last-child {
    margin-right: 0;
    }
    .student-name-value {
    font-weight: 900;
    font-size: <?= $baseFontSize + 2 ?>px;
    text-transform: uppercase;
    color: #0057b8;
    }
    .check-group { font-family: 'Courier New', monospace; white-space: nowrap; }
    .nowrap { white-space: nowrap; }
    .grades-table th { font-size: <?= $baseFontSize - 1 ?>px; word-wrap: break-word; overflow-wrap: break-word; }
    .grades-table thead th {
    background: #14347a;
    color: white;
    font-weight: 700;
    letter-spacing: 0.2px;
    text-transform: uppercase;
    padding: 2px 4px;
    border-color: #14347a;
    }
    .group-header { background-color: #e9e9e9; text-align: left; padding-left: 8px; }
    .group-subtotal-line {
    width: 100%;
    margin: 6px 0 4px;
    background: transparent;
    color: #333;
    font-weight: normal;
    font-size: <?= $baseFontSize + 1 ?>px;
    text-transform: uppercase;
    white-space: nowrap;
    page-break-inside: avoid;
    break-inside: avoid;
    }
    .teacher-name { font-size: 0.75em; text-transform: uppercase; display: block; margin-top: 1px; }
    .container-table { border: none; margin-bottom: 8px; width: 100%; border-collapse: collapse; }
    .container-table td { border: none; padding: 0; vertical-align: top; }
    .container-table > tr > td:not(:last-child), .container-table > tbody > tr > td:not(:last-child) { padding-right: 10px;
    }
    .compact-layout { margin-bottom: 1px; }
    .compact-layout > tbody > tr > td { padding-right: 0; }
    .compact-layout > tbody > tr > td:last-child { padding-right: 0; }
    .side-table { width: 100%; border: 1px solid #14347a; }
    .side-table th { font-size: <?= $baseFontSize - 2 ?>px; border: 1px solid #14347a; padding: 1px 2px; }
    .side-table td { border: 1px solid #14347a; padding: 1px 2px; font-size: <?= $baseFontSize - 2 ?>px; }
    .compact-side { width: 100%; margin-bottom: 0; }
    .compact-side th, .compact-side td { padding: 1px 2px; line-height: 0.95; }
    .rounded-legend { border: 1px solid #14347a; border-radius: 4px; border-collapse: separate; }
    .signature-table td { border: none; height: 30px; vertical-align: top; padding-top: 1px; }
    .bulletin-footer { width: 100%; height: 15px; background-color: #14347a; color: white; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: bold; margin-top: 10px; text-align: center; }
    .no-border { border: none !important; }
    .absences-title { text-align: center; vertical-align: middle; width: 15px; font-weight: bold; font-size: 6.5px;
    line-height: 0.8; }
    .avg-box { border: 2px solid #000; padding: 2px; text-align: center; font-size: 9px; }
    .compact-note-box { border: 1px solid #000; border-radius: 4px; padding: 2px 3px; min-height: 75px; }
    .compact-note-title { text-align: center; margin-bottom: 1px; font-size: 7px; font-weight: bold; }
    .legend-text { font-size: 6.5px; line-height: 0.95; text-align: left; }
    .summary-total td { background-color: #f7f7f7; font-weight: bold; }
    .compact-value { font-weight: bold; font-size: 9px; }
    .report-card-grid { display: flex; flex-direction: column; flex: 1 1 auto; min-height: 0; margin: 4px 0 6px; page-break-inside: avoid; }
    .report-card-header-table { display: table; width: 100%; margin: 0; border-collapse: collapse; table-layout: fixed; border: 2px solid #14347a; }
    .report-card-header-table th { border: 1px solid #fff; padding: 2px 3px; text-align: center; vertical-align: middle; font-size: 10px; line-height: 1; }
    .report-card-header-table thead th { border-bottom: 1px solid #fff; }
    .report-card-main-table { width: 100%; border-collapse: collapse; table-layout: fixed; border: 2px solid #14347a; }
    .report-card-main-table th, .report-card-main-table td { border: 1px solid #14347a !important; padding: 3px 4px; text-align: center; vertical-align: middle; }
    .report-card-statistics-table { width: 100%; border-collapse: collapse; table-layout: auto; border: 1px solid #14347a; margin-top: 5px; }
    .report-card-statistics-table col { width: auto !important; }
    .report-card-statistics-table th, .report-card-statistics-table td { border: 1px solid #14347a; padding: 3px 4px; text-align: center; vertical-align: middle; white-space: nowrap; }
    .report-card-statistics-table th { background: #14347a; color: #fff; font-weight: 700; text-transform: uppercase; }
    .report-card-statistics-table td:nth-child(odd) { font-weight: 700; color: #14347a; }
    .report-card-statistics-table .student-average-label,
    .report-card-statistics-table .student-average-value { border-width: 2px; font-weight: 700; }
    .report-card-statistics-table .student-average-value { font-size: 11px !important; }
    .report-card-statistics-table .average-positive { border-color: #198754; color: #198754; }
    .report-card-statistics-table .average-negative { border-color: #dc3545; color: #dc3545; }
    .stats-student-col, .stats-work-col { width: 6%; }
    .stats-profile-col { width: 4.666%; }
    .report-card-footer-table { width: 100%; flex: 1 1 auto; min-height: 0; border-collapse: collapse; table-layout: fixed; border: 1px solid #14347a; margin-top: auto; }
    .report-card-footer-table th, .report-card-footer-table td { border: 1px solid #14347a; padding: 3px 4px; text-align: center; vertical-align: top; font-size: 10px; }
    .report-card-footer-table th { background: #14347a; color: #fff; font-weight: 700; }
    .report-card-footer-table th:first-child, .report-card-footer-table td:first-child { width: 33%; }
    .report-card-footer-table th:nth-child(2), .report-card-footer-table td:nth-child(2),
    .report-card-footer-table th:nth-child(3), .report-card-footer-table td:nth-child(3) { width: 11.167%; }
    .report-card-footer-table th:last-child, .report-card-footer-table td:last-child { width: 44.666%; }
    .report-card-footer-table td { height: 70px; }
    .report-card-footer-table .footer-observation { text-align: left; font-weight: 700; }
    .bulletin-sheet table { margin: 0 !important; font-size: 10px !important; }
    .bulletin-sheet table th, .bulletin-sheet table td { font-size: 10px !important; padding: 2px 3px; }
    .bulletin-sheet .report-card-header-table,
    .bulletin-sheet .report-card-main-table,
    .bulletin-sheet .report-card-statistics-table { width: 100% !important; margin-left: 0; margin-right: 0; }
    .bulletin-sheet .report-card-header-table { margin-bottom: 5px !important; }
    .bulletin-sheet .report-card-statistics-table { margin-top: 5px !important; }
    .bulletin-sheet .report-card-grid { margin: 5px 0 0; }
    .bulletin-sheet .report-card-teacher { font-size: 10px !important; }
    .bulletin-sheet .student-info-table td { font-size: 10px !important; }
    .bulletin-sheet .department-banner { margin: 5px 0; padding: 3px 0; font-size: 11px; }
    .bulletin-sheet .department-banner::before,
    .bulletin-sheet .department-banner::after { margin: 0 16px; }
    .bulletin-sheet .department-label { font-size: 9px; }
    .bulletin-sheet .department-name { font-size: 12px; }
    .bulletin-sheet .report-card-legend { margin: 0; padding: 2px 4px; font-size: 10px; flex-wrap: wrap; justify-content: flex-start; gap: 4px 10px; line-height: 1.1; }
    .bulletin-sheet .report-card-footer-table td { height: auto; }
    .report-card-header-table thead th, .report-card-group-header th, .report-card-grand-total th { background: #14347a; color: #fff; font-weight: 700; text-transform: uppercase; }
    .report-card-header-table thead th { height: 18px; }
    .report-card-group-header th { background: #193f8f; text-align: left; padding-left: 7px; }
    .report-card-subject { text-align: left !important; font-weight: 700; }
    .report-card-teacher { display: block; font-size: .78em; font-style: italic; font-weight: 400; color: #3d4f6f; }
    .report-card-competence { text-align: left !important; font-size: .9em; }
    .report-card-subtotal td { background: #d9e8fb; color: #14347a; font-weight: 700; }
    .report-card-grand-total th, .report-card-grand-total td { background: #14347a; color: #fff; font-weight: 700; }
    .report-card-legend { display: flex; gap: 14px; justify-content: flex-end; align-items: center; border: 1px solid #14347a; padding: 3px 6px; color: #14347a; font-size: .8em; }
    .report-card-lower-grid { display: grid; grid-template-columns: 1.35fr .8fr 1fr; gap: 5px; margin-top: 5px; }
    .report-card-lower-grid table, .report-card-decision-table { width: 100%; border-collapse: collapse; border: 1px solid #14347a; }
    .report-card-lower-grid th, .report-card-decision-table th { background: #14347a; color: #fff; font-weight: 700; }
    .report-card-lower-grid td, .report-card-decision-table td { border: 1px solid #14347a; padding: 3px 5px; }
    .report-card-kpi { text-align: center; font-size: 1.25em; font-weight: 700; color: #14347a; }
    .report-card-decision-table { margin-top: 5px; table-layout: fixed; }
    .report-card-decision-table th, .report-card-decision-table td { width: 33.333%; }
    .remarks-space { height: 72px; vertical-align: top; }
    .report-card-signatures { display: flex; justify-content: space-between; gap: 10px; padding-top: 8px; font-size: .82em; }
    .col-subject { width: 24%; }
    .col-competence { width: 22%; }
    .col-test { width: 5%; }
    .col-term { width: 5%; }
    .col-average { width: 5.5%; }
    .col-coefficient { width: 6%; }
    .col-score { width: 6%; }
    .col-rank { width: 6%; }
    .col-appreciation { width: 14%; }
    .appreciation-header, .appreciation-cell {
    white-space: normal;
    overflow-wrap: anywhere;
    word-break: break-word;
    }
    .legacy-grades-table { display: none; }
    .legacy-summary-table { display: none; }
    /* BARRE D'OUTILS */
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
    .pv-btn-print { background: #0d6efd; color: white; }
    .pv-btn-back { background: rgba(255,255,255,0.15); color: white; margin-right: 5px; }
    @media screen and (max-width: 600px) {
    .pv-toolbar { flex-direction: column; align-items: stretch; gap: 8px; }
    .pv-btn { width: 100%; text-align: center; margin: 2px 0 !important; }
    .bulletin-sheet { overflow-x: hidden; }
    .grades-table { table-layout: auto; word-wrap: break-word; }
    .grades-table th, .grades-table td { word-wrap: break-word; overflow-wrap: break-word; }
    .student-info-table { display: block; overflow-x: auto; }
    .student-info-table td { display: block; width: 100%; padding: 1px 4px; border-bottom: none !important; }
    .student-info-table tr { display: block; margin-bottom: 0; }
    .header-wrapper { display: flex; flex-direction: column; }
    .header-left, .header-center, .header-right { width: 100%; float: none; margin-bottom: 5px; }
    .school-name-display { font-size: 14px; }
    .academic-year-display { font-size: 12px; }
    }
    @media screen and (max-width: 700px) {
        .report-card-lower-grid { grid-template-columns: 1fr; }
        .report-card-legend, .report-card-signatures { flex-wrap: wrap; justify-content: flex-start; }
        .report-card-main-table, .report-card-header-table { font-size: 10px; }
    }
    @media print {
    .bulletin-wrapper { page-break-after: always; page-break-inside: avoid; }
    .bulletin-wrapper { border: 2px solid #14347a; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .bulletin-sheet { page-break-inside: avoid; border: none; padding: 5px; }
    table { page-break-inside: avoid; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    tr { page-break-inside: avoid; }
    th, td { border: 1px solid #14347a; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    th { background-color: #14347a !important; color: white !important; border: 2px solid #14347a !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .report-card-header-table th { border: 1px solid #fff !important; }
    .report-card-header-table thead th { border-bottom-color: #fff !important; }
    .bulletin-footer { background-color: #14347a !important; color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .subject-group { background: #d9e8fb !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
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

$i = $institution;
$isEnglish = (($lang ?? \App\Core\Session::get('app_lang', 'fr')) === 'en');
$stateRepublic = $isEnglish ? ($i['school_republic_en'] ?? __('republic_of_cameroon')) : ($i['school_republic'] ?? __('republic_of_cameroon'));
$stateMinistry = $isEnglish ? ($i['school_ministry_en'] ?? __('ministry_secondary_education')) : ($i['school_ministry'] ?? __('ministry_secondary_education'));
$stateMotto = $isEnglish ? ($i['school_motto_en'] ?? __('motto')) : ($i['school_motto'] ?? __('motto'));
$stateSlogan = $isEnglish ? ($i['school_slogan_en'] ?? __('slogan')) : ($i['school_slogan'] ?? __('slogan'));
$stateDelegation = $isEnglish ? ($i['school_delegation_en'] ?? '') : ($i['school_delegation'] ?? '');
$stateDelegationHtml = nl2br(htmlspecialchars(str_replace(';', "\n", trim((string) $stateDelegation)), ENT_QUOTES, 'UTF-8'));
$schoolPhone = trim((string) ($i['school_phone'] ?? ''));
$schoolAddress = trim((string) ($i['school_address'] ?? $i['school_city'] ?? ''));
?>

<?php if (!$embeddedBatch && empty($isPdf)): ?>
    <!DOCTYPE html>
    <html lang="<?= $isEnglish ? 'en' : 'fr' ?>">

    <head>
        <meta charset="UTF-8">
        <title><?= htmlspecialchars((string) ($pdf_filename ?? 'bulletin')) ?></title>
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
        <!-- A. EN-TÊTE EN TROIS COLONNES -->
        <div class="header-wrapper">
            <div class="header-left">
                <div class="header-side-content">
                    <p class="header-line ministry-line"><?= htmlspecialchars((string) $stateMinistry) ?></p>
                    <p class="header-line school-name-display"><?= htmlspecialchars($schoolDisplayName) ?></p>
                    <p class="header-contact"><span class="header-contact-label"><?= htmlspecialchars(__('tel')) ?>:</span> <span class="header-contact-value"><?= htmlspecialchars($schoolPhone) ?></span></p>
                    <p class="header-contact"><span class="header-contact-label"><?= htmlspecialchars(__('address')) ?>:</span> <span class="header-contact-value"><?= htmlspecialchars($schoolAddress) ?></span></p>
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
                <div class="academic-year-display"><?= __('academic_years') ?> : <?= htmlspecialchars((string) ($activeYear['nom'] ?? '')) ?></div>
            </div>

            <div class="header-right">
                <div class="header-side-content">
                    <p class="header-line"><?= htmlspecialchars((string) $stateRepublic) ?></p>
                    <p class="header-separator">*************</p>
                    <p class="header-line"><?= htmlspecialchars((string) $stateMotto) ?></p>
                    <p class="header-separator">*************</p>
                    <?php if ($stateDelegationHtml !== ''): ?>
                        <p class="header-line"><?= $stateDelegationHtml ?></p>
                        <p class="header-separator">*************</p>
                    <?php endif; ?>
                    <p class="header-line"><?= htmlspecialchars((string) $stateSlogan) ?></p>
                </div>
            </div>

        </div>

        <div class="title-box" style="font-weight: bold;"><?= __('report_card') ?> <?= strtoupper($bulletinType) ?></div>

        <!-- B. CARTE D'IDENTITÉ -->
        <table class="student-info-table">
            <tr>
                <td class="student-photo-cell" rowspan="4">
                    <?php if (!empty($student['photo_eleve'])): ?>
                        <?php
                        $photoPath = $student['photo_eleve'];
                        // Gérer les deux formats de chemin: /uploads/ et /public/uploads/
                        if (strpos($photoPath, '/public/uploads/') === 0) {
                            // Le chemin est déjà au bon format
                        } elseif (strpos($photoPath, '/uploads/') === 0) {
                            // Ancien format, ajouter /public/
                            $photoPath = '/public' . $photoPath;
                        }
                        ?>
                        <div class="student-photo-container">
                            <img src="<?= $photoPath ?>" alt="Photo de l'élève">
                        </div>
                    <?php else: ?>
                        <div class="student-photo-placeholder">
                            👤
                        </div>
                    <?php endif; ?>
                </td>
                <td colspan="5" class="student-identity-row">
                    <span class="student-identity-label"><?= __('name_and_surname') ?> :</span>
                    <span class="student-name-value"><?= htmlspecialchars($studentLastName . ' ' . ($student['prenom'] ?? '')) ?></span>
                </td>
            </tr>
            <tr>
                <td class="student-identity-row">
                    <span class="student-identity-label"><?= __('birth_date') ?> :</span>
                    <span class="student-identity-value"><?= htmlspecialchars(formatBulletinDate($birthDate)) ?></span>
                </td>
                <td colspan="2" class="student-identity-row">
                    <span class="student-identity-label"><?= __('birth_place') ?> :</span>
                    <span class="student-identity-value"><?= htmlspecialchars($birthPlace) ?></span>
                </td>
                <td class="student-identity-row">
                    <span class="student-identity-label"><?= __('matricule') ?> :</span>
                    <span class="student-identity-value"><?= htmlspecialchars((string) ($displayMatricule ?? $student['matricule'] ?? '')) ?></span>
                </td>
            </tr>
            <tr>
                <td class="student-identity-row">
                    <span class="student-identity-label"><?= __('class') ?> :</span>
                    <span class="student-identity-value"><?= htmlspecialchars((string) ($student['class_nom'] ?? '')) ?></span>
                </td>
                <td class="student-identity-row">
                    <span class="student-identity-label"><?= __('effectif') ?> :</span>
                    <span class="student-identity-value"><?= (int) $effectif ?></span>
                </td>
                <td class="student-identity-row">
                    <span class="student-identity-label"><?= __('sex') ?> :</span>
                    <span class="student-identity-value"><?= htmlspecialchars((string) ($student['sexe'] ?? '-')) ?></span>
                </td>
                <td colspan="2" class="student-identity-row">
                    <span class="student-identity-label"><?= __('repeating') ?> :</span>
                    <span class="student-identity-value"><?= $isRedoublant ? __('yes') : __('no') ?></span>
                </td>
            </tr>
        </table>
