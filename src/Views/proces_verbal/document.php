<?php
/**
 * DOCUMENT : Procès-Verbal (PV) de Classe "Gold Standard"
 * STYLE : Institutionnel Premium, Mode Paysage A4
 * DÉVELOPPEUR : Antigravity (Google DeepMind)
 * CONFIGURATION : 20 élèves par page, Pagination automatique, Support Multilingue
 */

/* ==========================================================================
   1. PRÉPARATION DES DONNÉES (LOGIQUE MÉTIER)
   ========================================================================== */

$matieres = $donneesPV['matieres'] ?? [];
$eleves   = $donneesPV['matriceEleves'] ?? [];
$nbMatieres = count($matieres);

// --- Chunking des élèves (20 par page) ---
$chunks = array_chunk($eleves, 20);
$totalChunks = count($chunks);

// --- Statistiques Globales (pour le bloc final) ---
$nbAdmis = 0; $nbEchoue = 0; $allAverages = [];
foreach ($eleves as $ligne) {
    if ($ligne['moyenne'] !== null) {
        $val = (float)$ligne['moyenne'];
        $allAverages[] = $val;
        if ($val >= 10.0) $nbAdmis++; else $nbEchoue++;
    }
}
$maxAvg = !empty($allAverages) ? max($allAverages) : 0;
$minAvg = !empty($allAverages) ? min($allAverages) : 0;

// Récupération du logo via LogoManager pour affichage fiable
$db = \App\Core\Database::getInstance()->getConnection();
$logoManager = \App\Core\LogoManager::getInstance($db);
$logoData = [
    'has_logo' => $logoManager->hasLogo(),
    'base64' => $logoManager->hasLogo() ? $logoManager->getLogoBase64() : '',
    'url' => $logoManager->getLogoUrl(),
    'fallback_letter' => $logoManager->getFallbackLetter()
];

// --- Groupement des matières ---
$groupedMatieres = [];
foreach ($matieres as $m) {
    $grp = $m['groupe_nom'] ?? $m['groupe'] ?? $m['group_name'] ?? 'AUTRES';
    $groupedMatieres[$grp][] = $m;
}

// --- Analyse Pédagogique (Calculée sur TOUS les élèves) ---
$matiereStats = [];
foreach ($matieres as $m) {
    $matiereStats[$m['id']] = [
        'nom' => $m['nom'], 'prof' => $m['enseignant'] ?? $m['teacher'] ?? '-',
        'above' => 0, 'below' => 0, 'max' => null, 'min' => null
    ];
}
foreach ($eleves as $ligne) {
    foreach ($matieres as $m) {
        $mid = $m['id']; $n = $ligne['notesParMatiere'][$mid] ?? null;
        if ($n !== null) {
            $n = (float)$n;
            if ($n >= 10) $matiereStats[$mid]['above']++; else $matiereStats[$mid]['below']++;
            if ($matiereStats[$mid]['max'] === null || $n > $matiereStats[$mid]['max']) $matiereStats[$mid]['max'] = $n;
            if ($matiereStats[$mid]['min'] === null || $n < $matiereStats[$mid]['min']) $matiereStats[$mid]['min'] = $n;
        }
    }
}
$bestSub = null; $worstSub = null;
foreach ($matiereStats as $ms) {
    if ($bestSub === null || $ms['above'] > $bestSub['above']) $bestSub = $ms;
    if ($worstSub === null || $ms['below'] > $worstSub['below']) $worstSub = $ms;
}

// --- Synthèse Groupes ---
$groupStats = [];
foreach ($groupedMatieres as $grpName => $subs) {
    $groupStats[$grpName] = ['pts' => 0, 'coefs' => 0, 'above' => 0, 'total' => 0];
    foreach ($subs as $m) {
        $mid = $m['id']; $c = (float)$m['coefficient'];
        foreach ($eleves as $ligne) {
            $n = $ligne['notesParMatiere'][$mid] ?? null;
            if ($n !== null) {
                $n = (float)$n;
                $groupStats[$grpName]['pts'] += ($n * $c); $groupStats[$grpName]['coefs'] += $c;
                $groupStats[$grpName]['total']++; if ($n >= 10) $groupStats[$grpName]['above']++;
            }
        }
    }
}

// Largeur dynamique
$subWidth = $nbMatieres > 0 ? (65 / $nbMatieres) : 15;
$lang = \App\Core\Locale::get();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title>PV - <?= $contexte['classeNom'] ?></title>
    <style>
        /* ==========================================================================
           2. SYSTÈME DE STYLE
           ========================================================================== */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; font-size: 10px; color: #1a202c; background: #f7fafc; line-height: 1.2; counter-reset: page_counter; }
        
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

        /* Structure de Page Unique */
        .pv-page-container { 
            width: 297mm; height: 210mm; margin: 20px auto; background: white; 
            padding: 10mm; display: flex; flex-direction: column; position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); page-break-after: always;
        }
        .pv-page-container:last-child { page-break-after: auto; }

        /* En-tête */
        .pv-header { display: flex; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 10px; }
        .header-side { width: 38%; text-align: center; font-size: 8px; text-transform: uppercase; line-height: 1.4; }
        .school-logo { width: 50px; height: 50px; object-fit: contain; }
        .school-name { font-size: 13px; font-weight: 800; text-transform: uppercase; margin-top: 5px; }

        .pv-title-box { text-align: center; border: 1px solid #000; padding: 5px; background: #f8fafc; margin-bottom: 8px; border-radius: 4px; }
        .pv-main-title { font-size: 13px; font-weight: 900; text-transform: uppercase; text-decoration: underline; display: block; }
        
        /* Table des notes */
        .pv-table { width: 100%; border-collapse: collapse; table-layout: fixed; border: 1.5px solid #000; flex: 1; }
        .pv-table th, .pv-table td { border: 1px solid #000; padding: 4px 2px; text-align: center; font-size: 9px; }
        .pv-table thead th { background: #edf2f7; font-weight: 800; text-transform: uppercase; font-size: 7.5px; }
        .pv-table thead th.group-header { background: #cbd5e0; font-size: 8px; }
        .col-student { text-align: left !important; padding-left: 8px !important; font-weight: 800; font-size: 10px; }
        .row-fail { background: #fff5f5; }
        .cell-fail { color: #e53e3e; font-weight: bold; }
        .cell-avg { background: #f7fafc; font-weight: 900; }

        /* Pied de page */
        .pv-footer { margin-top: auto; padding-top: 10px; border-top: 1.5px solid #000; }
        .footer-flex { display: flex; gap: 12px; align-items: stretch; margin-bottom: 10px; }
        
        .summary-table { border-collapse: collapse; flex: 1; }
        .summary-table td, .summary-table th { border: 1px solid #000; padding: 4px; font-size: 8.5px; }
        .summary-table th { background: #edf2f7; text-align: left; font-weight: 800; }
        .table-title { background: #1a202c; color: black; text-align: center; font-weight: 800; font-size: 9px; text-transform: uppercase; }

        /* Style pour les noms de matières avec retour à la ligne */
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

        .sig-grid { width: 30%; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .sig-box { border: 1.5px solid #000; padding: 6px; height: 65px; text-align: center; border-radius: 4px; }
        .sig-label { font-weight: 900; font-size: 8.5px; text-transform: uppercase; text-decoration: underline; display: block; margin-bottom: 15px; }

        .minimal-footer { font-size: 7.5px; color: #718096; display: flex; justify-content: space-between; border-top: 1px dashed #cbd5e1; padding-top: 4px; align-items: center; }
        .page-num::after { counter-increment: page_counter; content: "PAGE : " counter(page_counter) " / <?= $totalChunks ?>"; font-weight: bold; }

        @page { size: A4 landscape; margin: 0; }
        @media print {
            body { background: white; }
            .pv-toolbar { display: none; }
            .pv-page-container { margin: 0; border: none; box-shadow: none; width: 100%; height: 210mm; }
            .pv-table thead th, .summary-table th, .table-title { background: #edf2f7 !important; -webkit-print-color-adjust: exact; }
            .table-title { background: #1a202c !important; color: white !important; }
            .row-fail { background: #fff5f5 !important; -webkit-print-color-adjust: exact; }
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
        <div class="pv-page-container">
            
            <!-- HEADER -->
            <header class="pv-header">
                <div class="header-side">
                    <div><?= htmlspecialchars($contexte['institution']['school_republic'] ?? 'REPUBLIQUE DU CAMEROUN') ?></div>
                    <div style="font-weight: bold; font-style: italic;"><?= htmlspecialchars($contexte['institution']['school_motto'] ?? 'Paix - Travail - Patrie') ?></div>
                    <div>**********</div>
                    <div><?= htmlspecialchars($contexte['institution']['school_ministry'] ?? 'MINISTERE DES ENSEIGNEMENTS SECONDAIRES') ?></div>
                </div>
                <div class="header-center" style="text-align: center;">
                    <?php if ($logoData['has_logo'] && !empty($logoData['base64'])): ?>
                        <img src="<?= htmlspecialchars($logoData['base64']) ?>" class="school-logo" alt="Logo">
                    <?php elseif ($logoData['has_logo'] && !empty($logoData['url'])): ?>
                        <img src="<?= htmlspecialchars($logoData['url']) ?>" class="school-logo" alt="Logo">
                    <?php endif; ?>
                    <div class="school-name"><?= htmlspecialchars($contexte['institution']['school_name'] ?? 'NotesMaster') ?></div>
                </div>
                <div class="header-side">
                    <div><?= htmlspecialchars($contexte['institution']['school_republic_en'] ?? 'REPUBLIC OF CAMEROON') ?></div>
                    <div style="font-weight: bold; font-style: italic;"><?= htmlspecialchars($contexte['institution']['school_motto_en'] ?? 'Peace - Work - Fatherland') ?></div>
                    <div>**********</div>
                    <div><?= htmlspecialchars($contexte['institution']['school_ministry_en'] ?? 'MINISTRY OF SECONDARY EDUCATION') ?></div>
                </div>
            </header>

            <div class="pv-title-box">
                <span class="pv-main-title"><?= __('pv_document_title') ?> - <?= $contexte['typeEvaluation'] ?></span>
                <div style="font-size: 9.5px; font-weight: 700; text-transform: uppercase;">
                    <?= __('class') ?> : <?= $contexte['classeNom'] ?> | <?= __('year') ?> : <?= $contexte['anneeNom'] ?> | <?= __('period') ?> : <?= $contexte['periodeLabel'] ?>
                </div>
            </div>

            <!-- TABLE DES NOTES (Chunk courant) -->
            <table class="pv-table">
                <colgroup>
                    <col style="width: 3.5%;">
                    <col style="width: 25%;">
                    <?php foreach ($matieres as $m): ?>
                        <col style="width: <?= $subWidth ?>%;">
                    <?php endforeach; ?>
                    <col style="width: 7%;">
                    <col style="width: 4%;">
                </colgroup>
                <thead>
                    <tr>
                        <th rowspan="2">N°</th>
                        <th rowspan="2" class="col-student"><?= __('student_name') ?></th>
                        <?php foreach ($groupedMatieres as $grp => $subs): ?>
                            <th colspan="<?= count($subs) ?>" class="group-header"><?= htmlspecialchars($grp) ?></th>
                        <?php endforeach; ?>
                        <th rowspan="2">MOY.</th>
                        <th rowspan="2">RG</th>
                    </tr>
                    <tr>
                        <?php foreach ($groupedMatieres as $grp => $subs): ?>
                            <?php foreach ($subs as $m): ?>
                                <th class="subject-header-cell">
                                    <div class="subject-name-wrap"><?= htmlspecialchars($m['nom']) ?></div>
                                    <div style="font-size: 6.5px; font-weight: 400; opacity: 0.8; margin-top: 3px; border-top: 0.5px solid #cbd5e0; padding-top: 2px;">
                                        C:<?= (float)$m['coefficient'] ?>
                                    </div>
                                </th>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($currentChunk as $i => $el): ?>
                        <?php 
                            $realIdx = ($chunkIndex * 20) + $i;
                            $isFail = ($el['moyenne'] !== null && $el['moyenne'] < 10);
                        ?>
                        <tr class="<?= $isFail ? 'row-fail' : '' ?>">
                            <td><?= sprintf('%02d', $realIdx + 1) ?></td>
                            <td class="col-student"><?= htmlspecialchars($el['nom'] . ' ' . $el['prenom']) ?></td>
                            <?php foreach ($groupedMatieres as $grp => $subs): ?>
                                <?php foreach ($subs as $m): ?>
                                    <?php $n = $el['notesParMatiere'][$m['id']] ?? null; ?>
                                    <td class="<?= ($n !== null && $n < 10) ? 'cell-fail' : '' ?>">
                                        <?= ($n !== null) ? number_format($n, 2) : '-' ?>
                                    </td>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                            <td class="cell-avg <?= $isFail ? 'cell-fail' : '' ?>">
                                <?= ($el['moyenne'] !== null) ? number_format($el['moyenne'], 2) : '-' ?>
                            </td>
                            <td style="font-weight: 800;"><?= $realIdx + 1 ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                
                <?php if ($chunkIndex === $totalChunks - 1): ?>
                <tfoot>
                    <tr style="background: #edf2f7; font-weight: 800;">
                        <td colspan="2" style="text-align: left; padding-left: 10px;"><?= __('pv_class_avg_per_subject') ?></td>
                        <?php foreach ($groupedMatieres as $grp => $subs): ?>
                            <?php foreach ($subs as $m): ?>
                                <td><?= $donneesPV['moyennesClasse'][$m['id']] ?? '-' ?></td>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        <td class="cell-avg"><?= ($donneesPV['moyenneGenerale'] !== null) ? number_format((float)$donneesPV['moyenneGenerale'], 2) : '-' ?></td>
                        <td>-</td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>

            <!-- FOOTER (Synthèse uniquement sur la dernière page) -->
            <div class="pv-footer">
                <?php if ($chunkIndex === $totalChunks - 1): ?>
                    <div class="footer-flex">
                        <table class="summary-table">
                            <tr><th colspan="2" class="table-title text-dark"><?= __('pv_general_stats') ?></th></tr>
                            <tr><th><?= __('pv_total_students') ?></th><td><?= $contexte['effectif'] ?></td></tr>
                            <tr><th><?= __('pv_passed_failed') ?></th><td><?= $nbAdmis ?> / <?= $nbEchoue ?></td></tr>
                            <tr><th><?= __('pv_success_rate') ?></th><td style="font-weight: 800;"><?= $donneesPV['tauxReussiteGlobal'] ?? '-' ?>%</td></tr>
                            <tr><th><?= __('pv_general_average') ?></th><td style="font-weight: 900; font-size: 11px;"><?= number_format((float)$donneesPV['moyenneGenerale'], 2) ?></td></tr>
                        </table>

                        <table class="summary-table">
                            <tr><th colspan="2" class="table-title"><?= __('pv_pedagogical_analysis') ?></th></tr>
                            <tr><th><?= __('pv_top_subject') ?></th><td><?= $bestSub ? $bestSub['nom'] : '-' ?></td></tr>
                            <tr><th><?= __('pv_flop_subject') ?></th><td><?= $worstSub ? $worstSub['nom'] : '-' ?></td></tr>
                            <tr><th><?= __('pv_avg_first_last') ?></th><td><?= number_format($maxAvg, 2) ?> / <?= number_format($minAvg, 2) ?></td></tr>
                        </table>

                        <table class="summary-table">
                            <tr><th colspan="3" class="table-title"><?= __('pv_groups_synthesis') ?></th></tr>
                            <?php foreach (array_slice($groupStats, 0, 3) as $name => $s): ?>
                                <?php $gAvg = $s['coefs'] > 0 ? ($s['pts'] / $s['coefs']) : 0; ?>
                                <tr>
                                    <td style="font-weight:bold;"><?= mb_substr($name, 0, 15) ?></td>
                                    <td><?= number_format($gAvg, 2) ?></td>
                                    <td><?= $s['total'] > 0 ? number_format(($s['above'] / $s['total'] * 100), 0) : '0' ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </table>

                        <div class="sig-grid" style="width: 25%;">
                            <div class="sig-box"><span class="sig-label"><?= __('pv_teacher_in_charge') ?></span></div>
                            <div class="sig-box"><span class="sig-label"><?= __('pv_principal') ?></span></div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="minimal-footer">
                    <span><?= __('pv_official_log') ?> - <?= __('pv_generated_on') ?> <?= date('d/m/Y H:i') ?></span>
                    <span class="page-num" style="font-weight: bold; border: 1px solid #cbd5e1; padding: 2px 8px; border-radius: 3px;"></span>
                    <span><?= __('pv_confidential_document') ?></span>
                </div>
            </div>

        </div>
    <?php endforeach; ?>

</body>
</html>
