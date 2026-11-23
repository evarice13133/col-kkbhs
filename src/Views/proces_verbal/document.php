<?php
/**
 * DOCUMENT : Procès-Verbal (PV) de Classe "Gold Standard"
 * STYLE : Institutionnel Premium, Mode Paysage A4
 * DÉVELOPPEUR : Antigravity (Google DeepMind)
 * CONFIGURATION : 10 élèves par page, pagination automatique et support multilingue
 */

/* ==========================================================================
   1. PRÉPARATION DES DONNÉES (LOGIQUE MÉTIER)
   ========================================================================== */

// --- 1.1 Récupération et comptage initial des matières et des élèves ---
$matieres = $donneesPV['matieres'] ?? [];
$eleves   = $donneesPV['matriceEleves'] ?? [];
$nbMatieres = count($matieres);
$lang = \App\Core\Locale::get();

// Le modèle de référence présente dix élèves par page pour garder les notes lisibles.
$rowsPerPage = 10;
$chunks = array_chunk($eleves, $rowsPerPage);
$totalChunks = max(1, count($chunks));

// --- 1.3 Calcul des statistiques globales affichées dans l'en-tête ---
$nbAdmis = 0;
$allAverages = [];
$nbGarcons = $nbFilles = $nbRedoublants = 0;
$admisGarcons = $admisFilles = $admisRedoublants = 0;
foreach ($eleves as $ligne) {
    $sexe = strtoupper((string) ($ligne['sexe'] ?? ''));
    $isRedoublant = !empty($ligne['is_redoublant']);
    if ($sexe === 'M') {
        $nbGarcons++;
    } elseif ($sexe === 'F') {
        $nbFilles++;
    }
    if ($isRedoublant) {
        $nbRedoublants++;
    }

    if ($ligne['moyenne'] !== null) {
        $val = (float)$ligne['moyenne'];
        $allAverages[] = $val;
        if ($val >= 10.0) {
            $nbAdmis++;
            if ($sexe === 'M') $admisGarcons++;
            if ($sexe === 'F') $admisFilles++;
            if ($isRedoublant) $admisRedoublants++;
        }
    }
}
$decimalSeparator = $lang === 'fr' ? ',' : '.';
$formatAverage = static fn($value) => $value !== null
    ? number_format((float) $value, 2, $decimalSeparator, '')
    : '—';
$formatRate = static fn(int $passed, int $count) => $count > 0
    ? number_format(($passed / $count) * 100, 2, $decimalSeparator, '') . '%'
    : '—';
$formatRank = static function ($rank) use ($lang) {
    if (!is_numeric($rank)) {
        return htmlspecialchars((string) $rank, ENT_QUOTES, 'UTF-8');
    }
    $rank = (int) $rank;
    if ($lang === 'fr') {
        return $rank === 1 ? '1er' : $rank . 'e';
    }
    $lastTwo = $rank % 100;
    $suffix = ($lastTwo >= 11 && $lastTwo <= 13) ? 'th' : ([1 => 'st', 2 => 'nd', 3 => 'rd'][$rank % 10] ?? 'th');
    return $rank . $suffix;
};

// --- 1.5 Calcul dynamique des largeurs des colonnes ---
$fixedColumnWidth = 3.2 + 19 + 4.8 + 4 + 5.5 + 4 + 4 + 7.5;
$subWidth = $nbMatieres > 0 ? max(0, (100 - $fixedColumnWidth) / $nbMatieres) : 0;
$institution = $contexte['institution'] ?? [];
$schoolName = (string) ($institution['school_name'] ?? 'NotesMaster');
$schoolLogoBase64 = (string) ($institution['school_logo_base64'] ?? '');
$schoolLogoPath = (string) ($institution['school_logo'] ?? '');
$schoolAddress = trim(implode(' · ', array_filter([
    (string) ($institution['school_address'] ?? ''),
    (string) ($institution['school_city'] ?? ''),
    !empty($institution['school_phone']) ? __('pv_phone') . ' ' . $institution['school_phone'] : '',
    !empty($institution['school_po_box']) ? 'B.P. ' . $institution['school_po_box'] : '',
])));
$periodValue = (string) ($contexte['periodeValeur'] ?? $contexte['periodeLabel'] ?? '-');
$teacherName = trim((string) ($contexte['profPrincipal'] ?? ''));
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title>PV - <?= $contexte['classeNom'] ?></title>
    <style>
        /* ==========================================================================
           2. SYSTÈME DE STYLE & COMPORTEMENT DE MISE EN PAGE
           ========================================================================== */
        /* Réinitialisation de base */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; font-size: 10px; color: #1a202c; background: #f7fafc; line-height: 1.2; counter-reset: page_counter; }
        
        /* 2.1 Barre d'outils interactive (Toolbar) - Visible uniquement à l'écran, masquée lors de l'impression */
        .pv-toolbar { 
            position: sticky; top: 0; z-index: 2000; display: flex; align-items: center; justify-content: space-between; 
            padding: 12px 30px; background: #1a202c; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .pv-btn { 
            padding: 8px 20px; border-radius: 6px; cursor: pointer; font-size: 11px; text-decoration: none; 
            color: white; border: 1px solid rgba(255,255,255,0.2); background: transparent; transition: 0.2s;
        }
        .pv-btn:hover { background: rgba(255,255,255,0.1); }
        .pv-btn-print { background: #3182ce; border-color: #3182ce; font-weight: bold; }

        /* 2.2 Conteneur Physique A4 Paysage - Délimite chaque feuille imprimable de 20 élèves */
        .pv-page-container { 
            width: 297mm; height: 210mm; margin: 20px auto; background: white; 
            padding: 10mm; display: flex; flex-direction: column; position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); page-break-after: always;
        }
        .pv-page-container:last-child { page-break-after: auto; }

        /* 2.3 En-tête institutionnel (République, Ministère, Logo et Nom d'Établissement) */
        .pv-header { display: flex; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 10px; }
        .header-side { width: 38%; text-align: center; font-size: 8px; text-transform: uppercase; line-height: 1.4; }
        .school-logo { width: 50px; height: 50px; object-fit: contain; }
        .school-name { font-size: 13px; font-weight: 800; text-transform: uppercase; margin-top: 5px; }

        /* 2.4 Cadre de titre principal de la période d'évaluation */
        .pv-title-box { text-align: center; border: 1px solid #000; padding: 5px; background: #f8fafc; margin-bottom: 8px; border-radius: 4px; }
        .pv-main-title { font-size: 13px; font-weight: 900; text-transform: uppercase; text-decoration: underline; display: block; }
        
        /* 2.5 Tableau principal des Notes (Filière Premium / Espacements Fins / Rendu Épuré) */
        .pv-table { 
            width: 100%; 
            border-collapse: separate; 
            border-spacing: 0;
            table-layout: fixed; 
            border: 1px solid #cbd5e1; 
            border-radius: 8px;
            overflow: hidden;
            flex: 1; 
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
        }
        .pv-table th, .pv-table td { 
            border-bottom: 1px solid #cbd5e1; 
            border-right: 1px solid #cbd5e1; 
            padding: 5px 3px; 
            text-align: center; 
            font-size: 8.5px; 
            vertical-align: middle;
        }
        .pv-table th:last-child, .pv-table td:last-child {
            border-right: none;
        }
        .pv-table tr:last-child td {
            border-bottom: none;
        }
        
        /* En-tête du tableau principal (Couleur Noire obligatoirement sur fonds gris clair) */
        .pv-table thead th { 
            background: #f1f5f9; 
            color: #000000;
            font-weight: 700; 
            text-transform: uppercase; 
            font-size: 7px; 
            letter-spacing: 0.5px;
            border-bottom: 2px solid #cbd5e1;
            border-right: 1px solid #cbd5e1;
        }
        .pv-table thead th.group-header { 
            background: #e2e8f0; 
            color: #000000;
            font-size: 7.5px; 
            font-weight: 800;
            letter-spacing: 0.7px;
            border-bottom: 1px solid #cbd5e1;
        }
        
        /* Colonne dédiée à l'identité des élèves (Alignement gauche pour une lisibilité parfaite) */
        .col-student { 
            text-align: left !important; 
            padding-left: 8px !important; 
            font-weight: 700; 
            font-size: 9px; 
            color: #1e293b;
            background: #f8fafc;
        }
        
        /* Rendu alterné des lignes (Zebra striping) et marquage rouge des élèves en échec de moyenne */
        .pv-table tbody tr:nth-child(even) {
            background: #ffffff;
        }
        .pv-table tbody tr:nth-child(odd) {
            background: #f8fafc;
        }
        .row-fail { 
            background: #fff1f2 !important; 
        }
        
        /* 2.6 Badges de Performance des Notes (Rouge doux pour échec, Vert doux pour excellent) */
        .note-badge {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 8px;
        }
        .note-fail { 
            background: #ffe4e6;
            color: #e11d48;
            border: 1px solid #fecdd3;
        }
        .note-pass { 
            color: #334155;
        }
        .note-good {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }
        
        /* Colonnes de Moyenne individuelle et de Rang */
        .cell-avg { 
            background: #f1f5f9; 
            font-weight: 800; 
            color: #0f172a;
            font-size: 9px;
        }
        .cell-avg.cell-fail {
            background: #ffe4e6;
            color: #e11d48;
        }
        .cell-rank {
            background: #faf5ff;
            color: #6b21a8;
            font-weight: 800;
        }

        /* 2.7 Pied de page administratif (Rapports de synthèse finaux) */
        .pv-footer { margin-top: auto; padding-top: 10px; border-top: 1.5px solid #000; }
        .footer-flex { display: flex; gap: 12px; align-items: stretch; margin-bottom: 10px; }
        
        /* Tableaux de statistiques globales, analyses pédagogiques et groupes */
        .summary-table { border-collapse: collapse; flex: 1; }
        .summary-table td, .summary-table th { border: 1px solid #000; padding: 4px; font-size: 8.5px; }
        .summary-table th { background: #f1f5f9; text-align: left; font-weight: 800; color: #000000; }
        .table-title { background: #e2e8f0; color: #000000; text-align: center; font-weight: 800; font-size: 9px; text-transform: uppercase; }
        
        /* PROTECTION ANTI-BLANCO IMPRESSION : Force le texte en noir pour toute la synthèse et ses tableaux */
        .pv-footer, .pv-footer *, .summary-table, .summary-table *, .table-title, .table-title * {
            color: #000000 !important;
        }
 
        /* 2.8 Formatage vertical et césure automatique des noms de matières trop longs */
        .subject-header-cell {
            vertical-align: middle;
            padding: 5px 2px !important;
            line-height: 1.1;
        }
        .subject-name-wrap {
            display: block;
            font-size: 8px;
            font-weight: 800;
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 100%;
        }

        /* 2.9 Emplacements de signatures finales des autorités académiques */
        .sig-grid { width: 30%; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .sig-box { border: 1.5px solid #000; padding: 6px; height: 65px; text-align: center; border-radius: 4px; }
        .sig-label { font-weight: 900; font-size: 8.5px; text-transform: uppercase; text-decoration: underline; display: block; margin-bottom: 15px; }

        /* Bloc d'informations minimales de bas de page (Confidentialité et pagination) */
        .minimal-footer { font-size: 7.5px; color: #718096; display: flex; justify-content: space-between; border-top: 1px dashed #cbd5e1; padding-top: 4px; align-items: center; }
        .page-num::after { counter-increment: page_counter; content: "PAGE : " counter(page_counter) " / <?= $totalChunks ?>"; font-weight: bold; }

        /* 2.10 DIRECTIVES D'IMPRESSION HAUTE-FIDÉLITÉ (Impression Navigateur & PDF client) */
        @page { size: A4 landscape; margin: 0; }
        @media print {
            body { background: white; }
            .pv-toolbar { display: none; }
            .pv-page-container { margin: 0; border: none; box-shadow: none; width: 100%; height: 210mm; }
            .pv-table thead th, .summary-table th, .table-title { background: #f1f5f9 !important; color: #000000 !important; -webkit-print-color-adjust: exact; }
            .pv-table thead th.group-header { background: #e2e8f0 !important; color: #000000 !important; -webkit-print-color-adjust: exact; }
        }

        body { font-family: Arial, sans-serif; font-size: 8px; color: #172033; background: #fff; }
        .pv-page-container { height: 210mm; margin: 8px auto; padding: 2mm 8mm 7mm; display: flex; gap: 2mm; box-shadow: none; }
        .pv-header { display: grid; grid-template-columns: minmax(0, 1fr) 55mm minmax(0, 1fr); column-gap: 3mm; align-items: center; min-height: 23mm; padding: 0 0 1mm; margin: 0; border: 0; }
        .header-side { width: auto; padding: 0 2mm; font-size: 7.5px; line-height: 1.45; }
        .header-side:first-child { text-align: left; }
        .header-side:last-child { text-align: right; }
        .pv-header-contact { margin-top: 1mm; font-size: 7px; font-weight: 700; line-height: 1.3; }
        .logo-box { width: 19mm; height: 19mm; margin: 0 auto 1mm; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .logo-box img { display: block; width: 100%; height: 100%; object-fit: contain; }
        .logo-placeholder { width: 19mm; height: 19mm; display: flex; align-items: center; justify-content: center; border: 1px solid #d1d5db; border-radius: 50%; background: #f4f6f8; color: #64748b; font-size: 7px; font-weight: 700; }
        .school-name { max-width: 100%; font-size: 10px; line-height: 1.2; margin-top: 1mm; }
        .header-center { min-width: 0; text-align: center; font-size: 7px; }
        .pv-title-box { padding: 0; margin: 0; border: 0; background: transparent; }
        .pv-main-title { font-size: 12px; text-decoration: none; }
        .pv-meta-grid { display: grid; grid-template-columns: repeat(4, 1fr); border: 0; }
        .pv-meta-card { min-width: 0; padding: 1.5mm 2mm; border: 0; }
        .pv-meta-row { display: flex; justify-content: space-between; gap: 2mm; margin-top: .8mm; font-size: 7px; font-weight: 700; }
        .pv-meta-row span { color: #4b5563; font-size: 10px; font-weight: 700; }
        .header-side > strong { font-size: 10.5px; }
        .pv-header-contact { font-size: 10px; }
        .school-name { font-size: 13px; }
        .pv-main-title { font-size: 15px; }
        .pv-meta-row strong { font-size: 10px; text-align: right; }
        .reference-table { width: 100%; flex: 0 0 auto; border-collapse: collapse; table-layout: fixed; }
        .reference-table th, .reference-table td { border: 1px solid #374151; padding: 1mm .5mm; text-align: center; font-size: 7px; overflow-wrap: anywhere; }
        .reference-table th, .reference-table .student-cell, .reference-table .average-cell { font-size: 10px; }
        .reference-table thead th { color: #fff; background: #243b63; font-weight: 700; text-transform: uppercase; }
        .reference-table thead th.subject-head { height: 29mm; padding: 1mm .5mm; writing-mode: vertical-rl; transform: rotate(180deg); }
        .reference-table tbody tr { height: 9mm; }
        .reference-table .student-cell { text-align: left; padding-left: 1.5mm; font-weight: 600; }
        .reference-table .average-cell { font-weight: 700; }
        .logo-placeholder { font-size: 10px; }
        .reference-table .mention-cell { font-size: 6.5px; }
        .reference-table .observations-cell { text-align: left; }
        .pv-page-number { margin-top: auto; padding-top: 1mm; text-align: right; font-size: 7px; }
        @media print {
            .pv-page-container { width: 297mm; height: 210mm; margin: 0; padding: 2mm 8mm 7mm; }
            .reference-table thead th { color: #fff !important; background: #243b63 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .pv-page-container { break-after: page; page-break-after: always; }
            .pv-page-container:last-of-type { break-after: auto; page-break-after: auto; }
        }
    </style>
</head>
<body>

    <div class="pv-toolbar">
        <span style="font-weight: 800;"><?= __('pv_title') ?> - <?= $contexte['classeNom'] ?></span>
        <div>
            <a href="/proces-verbal" class="pv-btn"><?= __('back') ?></a>
            <button class="pv-btn pv-btn-print" onclick="window.print()"><?= __('IMPRIMER LE PV') ?></button>
        </div>
    </div>

    <?php foreach ($chunks as $chunkIndex => $currentChunk): ?>
        <main class="pv-page-container">
            <header class="pv-header">
                <div class="header-side">
                    <strong><?= htmlspecialchars($institution['school_republic'] ?? __('school_republic_fr_placeholder'), ENT_QUOTES, 'UTF-8') ?></strong>
                    <div><?= htmlspecialchars($institution['school_motto'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                    <div>**********</div>
                    <div><?= htmlspecialchars($institution['school_ministry'] ?? __('school_ministry_fr_placeholder'), ENT_QUOTES, 'UTF-8') ?></div>
                    <?php if (!empty($institution['school_delegation'])): ?>
                        <div><?= htmlspecialchars($institution['school_delegation'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <?php if ($schoolAddress !== ''): ?>
                        <div class="pv-header-contact"><?= htmlspecialchars($schoolAddress, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>
                <div class="header-center">
                    <div class="logo-box">
                        <?php if ($schoolLogoBase64 !== ''): ?>
                            <img src="<?= htmlspecialchars($schoolLogoBase64, ENT_QUOTES, 'UTF-8') ?>" alt="<?= __('school_logo_alt') ?>">
                        <?php elseif ($schoolLogoPath !== ''): ?>
                            <?php $logoPath = \App\Core\Helpers::normalizeLogoPath($schoolLogoPath); ?>
                            <img src="<?= htmlspecialchars($logoPath, ENT_QUOTES, 'UTF-8') ?>" alt="<?= __('school_logo_alt') ?>">
                        <?php else: ?>
                            <div class="logo-placeholder">LOGO</div>
                        <?php endif; ?>
                    </div>
                    <div class="school-name"><?= htmlspecialchars($schoolName, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php if (!empty($institution['creation_decree'])): ?>
                        <div><?= htmlspecialchars($institution['creation_decree'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>
                <div class="header-side">
                    <strong><?= htmlspecialchars($institution['school_republic_en'] ?? __('school_republic_en_placeholder'), ENT_QUOTES, 'UTF-8') ?></strong>
                    <div><?= htmlspecialchars($institution['school_motto_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                    <div>**********</div>
                    <div><?= htmlspecialchars($institution['school_ministry_en'] ?? __('school_ministry_en_placeholder'), ENT_QUOTES, 'UTF-8') ?></div>
                    <?php if (!empty($institution['school_delegation_en'])): ?>
                        <div><?= htmlspecialchars($institution['school_delegation_en'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <?php if ($schoolAddress !== ''): ?>
                        <div class="pv-header-contact"><?= htmlspecialchars($schoolAddress, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>
            </header>

            <div class="pv-title-box">
                <span class="pv-main-title"><?= __('pv_document_title') ?> — <?= htmlspecialchars(htmlspecialchars_decode((string) $contexte['typeEvaluation'], ENT_QUOTES), ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <section class="pv-meta-grid" aria-label="<?= __('pv_general_stats') ?>">
                <div class="pv-meta-card">
                    <div class="pv-meta-row"><span><?= __('year') ?></span><strong><?= htmlspecialchars(htmlspecialchars_decode((string) $contexte['anneeNom'], ENT_QUOTES), ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <div class="pv-meta-row"><span><?= __('class') ?></span><strong><?= htmlspecialchars(htmlspecialchars_decode((string) $contexte['classeNom'], ENT_QUOTES), ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <div class="pv-meta-row"><span><?= __('pv_period') ?></span><strong><?= htmlspecialchars($periodValue, ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <div class="pv-meta-row"><span><?= __('pv_teacher_in_charge') ?></span><strong><?= $teacherName !== '' ? htmlspecialchars($teacherName, ENT_QUOTES, 'UTF-8') : '—' ?></strong></div>
                </div>
                <div class="pv-meta-card">
                    <div class="pv-meta-row"><span><?= __('pv_students') ?></span><strong><?= count($eleves) ?></strong></div>
                    <div class="pv-meta-row"><span><?= __('pv_boys') ?></span><strong><?= $nbGarcons ?></strong></div>
                    <div class="pv-meta-row"><span><?= __('pv_girls') ?></span><strong><?= $nbFilles ?></strong></div>
                    <div class="pv-meta-row"><span><?= __('pv_repeaters') ?></span><strong><?= $nbRedoublants ?></strong></div>
                </div>
                <div class="pv-meta-card">
                    <div class="pv-meta-row"><span><?= __('pv_general_average') ?></span><strong><?= $formatAverage($donneesPV['moyenneGenerale'] ?? null) ?></strong></div>
                    <div class="pv-meta-row"><span><?= __('pv_admitted') ?></span><strong><?= $nbAdmis ?> / <?= count($eleves) ?></strong></div>
                    <div class="pv-meta-row"><span><?= __('pv_top_average') ?></span><strong><?= $allAverages ? $formatAverage(max($allAverages)) : '—' ?></strong></div>
                    <div class="pv-meta-row"><span><?= __('pv_lowest_average') ?></span><strong><?= $allAverages ? $formatAverage(min($allAverages)) : '—' ?></strong></div>
                </div>
                <div class="pv-meta-card">
                    <div class="pv-meta-row"><span><?= __('pv_boys') ?></span><strong><?= $formatRate($admisGarcons, $nbGarcons) ?></strong></div>
                    <div class="pv-meta-row"><span><?= __('pv_girls') ?></span><strong><?= $formatRate($admisFilles, $nbFilles) ?></strong></div>
                    <div class="pv-meta-row"><span><?= __('pv_repeaters') ?></span><strong><?= $formatRate($admisRedoublants, $nbRedoublants) ?></strong></div>
                    <div class="pv-meta-row"><span><?= __('pv_class_success_rate') ?></span><strong><?= $formatRate($nbAdmis, count($eleves)) ?></strong></div>
                </div>
            </section>

            <table class="reference-table">
                <colgroup>
                    <col style="width:3.2%">
                    <col style="width:19%">
                    <?php foreach ($matieres as $matiere): ?>
                        <col style="width:<?= number_format($subWidth, 3, '.', '') ?>%">
                    <?php endforeach; ?>
                    <col style="width:4.8%">
                    <col style="width:4%">
                    <col style="width:5.5%">
                    <col style="width:4%">
                    <col style="width:4%">
                    <col style="width:7.5%">
                </colgroup>
                <thead>
                    <tr>
                        <th><?= __('pv_serial_number') ?></th>
                        <th><?= __('student_name') ?></th>
                        <?php foreach ($matieres as $matiere): ?>
                            <th class="subject-head"><?= htmlspecialchars($matiere['nom'], ENT_QUOTES, 'UTF-8') ?></th>
                        <?php endforeach; ?>
                        <th><?= __('pv_col_average') ?></th>
                        <th><?= __('pv_col_rank') ?></th>
                        <th><?= __('pv_col_mention') ?></th>
                        <th><?= __('pv_col_absences') ?></th>
                        <th><?= __('pv_col_lateness') ?></th>
                        <th><?= __('pv_col_observations') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($currentChunk as $i => $eleve): ?>
                        <tr>
                            <td><?= ($chunkIndex * $rowsPerPage) + $i + 1 ?></td>
                            <td class="student-cell"><?= htmlspecialchars(html_entity_decode((string) $eleve['nom'], ENT_QUOTES, 'UTF-8') . ' ' . html_entity_decode((string) $eleve['prenom'], ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8') ?></td>
                            <?php foreach ($matieres as $matiere): ?>
                                <?php $note = $eleve['notesParMatiere'][$matiere['id']] ?? null; ?>
                                <td><?= $note !== null ? number_format((float) $note, 2, $lang === 'fr' ? ',' : '.', '') : '—' ?></td>
                            <?php endforeach; ?>
                            <td class="average-cell"><?= $formatAverage($eleve['moyenne'] ?? null) ?></td>
                            <td><?= $formatRank($eleve['rang'] ?? null) ?></td>
                            <td class="mention-cell"><?= htmlspecialchars((string) ($eleve['mention'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= (int) ($eleve['absences'] ?? 0) ?></td>
                            <td><?= $eleve['retards'] !== null ? (int) $eleve['retards'] : '—' ?></td>
                            <td class="observations-cell"><?= htmlspecialchars((string) ($eleve['observations'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="pv-page-number"><?= __('page') ?> <?= $chunkIndex + 1 ?> / <?= $totalChunks ?></div>
        </main>
    <?php endforeach; ?>



</body>
</html>
