<?php
/**
 * DOCUMENT : Procès-Verbal (PV) d'évaluation - Modèle Supérieur LMD
 * STYLE : Institutionnel Premium, Mode Paysage A4
 * DÉVELOPPEUR : Antigravity (Google DeepMind)
 * EXIGENCES : Bordures visibles, Lignes en gras, Groupes de modules (UE), Crédits, Statistiques complets, Légende, Signatures.
 */

// --- 1. Préparation des données ---
$matieres = $donneesPV['matieres'] ?? [];
$eleves   = $donneesPV['matriceEleves'] ?? [];
$nbMatieres = count($matieres);

// Pagination par 15 élèves pour garantir un affichage A4 Paysage aéré avec le grand tableau LMD
$chunks = array_chunk($eleves, 15);
$totalChunks = max(1, count($chunks));

// Logo institutionnel
$db = \App\Core\Database::getInstance()->getConnection();
$logoManager = \App\Core\LogoManager::getInstance($db);
$logoData = [
    'has_logo' => $logoManager->hasLogo(),
    'base64' => $logoManager->hasLogo() ? $logoManager->getLogoBase64() : '',
    'url' => $logoManager->getLogoUrl(),
    'fallback_letter' => $logoManager->getFallbackLetter()
];

// Regroupement par Groupe de Modules (UE)
$groupedMatieres = [];
$totalCreditsExpected = 0;
foreach ($matieres as $m) {
    $grp = $m['groupe_nom'] ?? $m['groupe'] ?? $m['group_name'] ?? 'UE FONDAMENTALES';
    $groupedMatieres[$grp][] = $m;
    $totalCreditsExpected += (float) ($m['coefficient'] ?? 1);
}

// Calcule les sous-colonnes par groupe
// Chaque UE possède : Code UE, Crédits (Coef.), Moy.
$lang = \App\Core\Locale::get();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title>PV LMD - <?= htmlspecialchars($contexte['specialiteNom'] ?? $contexte['classeNom']) ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');
        
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; font-size: 9.5px; color: #000000; background: #f7fafc; line-height: 1.2; counter-reset: page_counter; }
        
        /* Toolbar (Écran) */
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

        /* Conteneur A4 Paysage */
        .pv-page-container { 
            width: 297mm; min-height: 210mm; margin: 15px auto; background: white; 
            padding: 8mm 10mm; display: flex; flex-direction: column; position: relative;
            box-shadow: 0 8px 25px rgba(0,0,0,0.06); page-break-after: always;
        }
        .pv-page-container:last-child { page-break-after: auto; }

        /* En-tête LMD officiel */
        .pv-header { display: flex; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 8px; }
        .header-side { width: 38%; text-align: center; font-size: 8px; text-transform: uppercase; line-height: 1.35; font-weight: 700; }
        .header-center { width: 24%; text-align: center; }
        .school-logo { width: 50px; height: 50px; object-fit: contain; }
        .school-name { font-size: 12px; font-weight: 900; text-transform: uppercase; margin-top: 4px; }

        /* Cartouche des informations LMD */
        .lmd-info-card {
            border: 1.5px solid #000;
            background: #f8fafc;
            padding: 6px 10px;
            margin-bottom: 8px;
            border-radius: 4px;
        }
        .lmd-info-title {
            text-align: center;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            text-decoration: underline;
            margin-bottom: 6px;
        }
        .lmd-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 4px 12px;
            font-size: 8.5px;
        }
        .lmd-item {
            display: flex;
            align-items: baseline;
        }
        .lmd-label {
            font-weight: 800;
            text-transform: uppercase;
            margin-right: 4px;
            white-space: nowrap;
        }
        .lmd-val {
            font-weight: 700;
            border-bottom: 1px dotted #000;
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Tableau Principal LMD : Bordures visibles (1px solid #000) et toutes lignes en GRAS */
        .pv-table-lmd { 
            width: 100%; 
            border-collapse: collapse; 
            table-layout: auto; 
            border: 1.5px solid #000000; 
            margin-bottom: 8px;
        }
        .pv-table-lmd th, .pv-table-lmd td { 
            border: 1px solid #000000 !important; 
            padding: 4px 3px; 
            text-align: center; 
            font-size: 8px; 
            font-weight: 700; /* TOUTES LES LIGNES EN GRAS */
            vertical-align: middle;
            color: #000000;
        }
        .pv-table-lmd thead th { 
            background: #e2e8f0 !important; 
            color: #000000 !important;
            font-weight: 900; 
            text-transform: uppercase; 
            font-size: 7.5px;
        }
        .pv-table-lmd thead th.group-header { 
            background: #cbd5e1 !important; 
            font-size: 8px; 
            font-weight: 900;
            letter-spacing: 0.5px;
        }

        /* Styles d'affichage des Moyennes & Décisions */
        .val-pass { color: #008000 !important; font-weight: 900 !important; } /* Vert + Gras */
        .val-fail { color: #cc0000 !important; font-weight: 900 !important; font-style: italic !important; } /* Rouge + Gras + Italique */

        .decision-admis { color: #008000 !important; font-weight: 900 !important; text-transform: uppercase; }
        .decision-rattrapage { color: #cc0000 !important; font-weight: 900 !important; text-transform: uppercase; }

        /* Alignement élève */
        .col-student-lmd {
            text-align: left !important;
            padding-left: 5px !important;
            font-weight: 900 !important;
            white-space: nowrap;
        }

        /* Section Statistiques */
        .stats-section {
            margin-top: 6px;
            margin-bottom: 8px;
        }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000;
        }
        .stats-table th, .stats-table td {
            border: 1px solid #000;
            padding: 3px 5px;
            font-size: 8px;
            font-weight: 700;
        }
        .stats-table th {
            background: #f1f5f9;
            text-align: left;
            font-weight: 800;
        }
        .stats-header-title {
            background: #cbd5e1 !important;
            text-align: center !important;
            font-weight: 900 !important;
            font-size: 8.5px !important;
            text-transform: uppercase;
        }

        /* Section Légende */
        .legend-container {
            border: 1.5px solid #000;
            border-radius: 4px;
            padding: 5px;
            background: #fafafa;
            margin-bottom: 8px;
            font-size: 7.5px;
        }
        .legend-title {
            font-weight: 900;
            font-size: 8.5px;
            text-transform: uppercase;
            text-decoration: underline;
            margin-bottom: 4px;
        }
        .legend-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .legend-box {
            background: white;
            border: 1px solid #cbd5e1;
            padding: 4px;
            border-radius: 3px;
        }
        .legend-box-title {
            font-weight: 800;
            font-size: 8px;
            margin-bottom: 3px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 2px;
        }
        .legend-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .legend-list li {
            margin-bottom: 2px;
            line-height: 1.2;
        }

        /* Section Signatures */
        .signatures-grid {
            display: flex;
            justify-content: space-between;
            margin-top: auto;
            padding-top: 6px;
        }
        .sig-card {
            width: 42%;
            border: 1.5px solid #000;
            height: 65px;
            padding: 5px;
            text-align: center;
            border-radius: 4px;
        }
        .sig-title {
            font-weight: 900;
            font-size: 8.5px;
            text-transform: uppercase;
            text-decoration: underline;
            display: block;
        }

        /* Footer minimal */
        .minimal-footer {
            font-size: 7.5px;
            color: #4a5568;
            display: flex;
            justify-content: space-between;
            border-top: 1px dashed #000;
            margin-top: 6px;
            padding-top: 3px;
        }
        .page-num::after { counter-increment: page_counter; content: "PAGE : " counter(page_counter) " / <?= $totalChunks ?>"; font-weight: bold; }

        @page { size: A4 landscape; margin: 0; }
        @media print {
            body { background: white; }
            .pv-toolbar { display: none; }
            .pv-page-container { margin: 0; border: none; box-shadow: none; width: 100%; min-height: 210mm; }
            .pv-table-lmd th, .stats-table th, .stats-header-title { background: #e2e8f0 !important; color: #000000 !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="pv-toolbar">
        <span style="font-weight: 800;"><?= __('pv_title') ?> LMD - <?= htmlspecialchars($contexte['specialiteNom'] ?? $contexte['classeNom']) ?></span>
        <div>
            <a href="/proces-verbal" class="pv-btn"><?= __('back') ?></a>
            <button class="pv-btn pv-btn-print" onclick="window.print()"><?= __('IMPRIMER LE PV') ?></button>
        </div>
    </div>

    <?php foreach ($chunks as $chunkIndex => $currentChunk): ?>
        <div class="pv-page-container">
            
            <!-- EN-TÊTE OFFICIEL BILINGUE -->
            <header class="pv-header">
                <div class="header-side">
                    <div><?= htmlspecialchars($contexte['institution']['school_republic'] ?? 'REPUBLIQUE DU CAMEROUN') ?></div>
                    <div style="font-style: italic;"><?= htmlspecialchars($contexte['institution']['school_motto'] ?? 'Paix - Travail - Patrie') ?></div>
                    <div>**********</div>
                    <div><?= htmlspecialchars($contexte['institution']['school_ministry'] ?? 'MINISTERE DE L\'ENSEIGNEMENT SUPERIEUR') ?></div>
                </div>
                <div class="header-center">
                    <?php if ($logoData['has_logo'] && !empty($logoData['base64'])): ?>
                        <img src="<?= htmlspecialchars($logoData['base64']) ?>" class="school-logo" alt="Logo">
                    <?php elseif ($logoData['has_logo'] && !empty($logoData['url'])): ?>
                        <img src="<?= htmlspecialchars($logoData['url']) ?>" class="school-logo" alt="Logo">
                    <?php endif; ?>
                    <div class="school-name"><?= htmlspecialchars($contexte['institution']['school_name'] ?? 'NotesMaster') ?></div>
                </div>
                <div class="header-side">
                    <div><?= htmlspecialchars($contexte['institution']['school_republic_en'] ?? 'REPUBLIC OF CAMEROON') ?></div>
                    <div style="font-style: italic;"><?= htmlspecialchars($contexte['institution']['school_motto_en'] ?? 'Peace - Work - Fatherland') ?></div>
                    <div>**********</div>
                    <div><?= htmlspecialchars($contexte['institution']['school_ministry_en'] ?? 'MINISTRY OF HIGHER EDUCATION') ?></div>
                </div>
            </header>

            <!-- CARTOUCHE DES INFORMATIONS LMD -->
            <div class="lmd-info-card">
                <div class="lmd-info-title"><?= __('pv_document_title') ?> - SUPÉRIEUR LMD</div>
                <div class="lmd-grid">
                    <div class="lmd-item"><span class="lmd-label"><?= __('pv_specialty') ?> :</span><span class="lmd-val"><?= htmlspecialchars($contexte['specialiteNom']) ?></span></div>
                    <div class="lmd-item"><span class="lmd-label"><?= __('pv_level') ?> :</span><span class="lmd-val"><?= htmlspecialchars($contexte['niveauNom']) ?></span></div>
                    <div class="lmd-item"><span class="lmd-label"><?= __('pv_cycle') ?> :</span><span class="lmd-val"><?= htmlspecialchars($contexte['cycleNom']) ?></span></div>
                    <div class="lmd-item"><span class="lmd-label"><?= __('pv_department') ?> :</span><span class="lmd-val"><?= htmlspecialchars($contexte['departementNom']) ?></span></div>
                    
                    <div class="lmd-item"><span class="lmd-label"><?= __('pv_filiere') ?> :</span><span class="lmd-val"><?= htmlspecialchars($contexte['filiereNom']) ?></span></div>
                    <div class="lmd-item"><span class="lmd-label"><?= __('year') ?> :</span><span class="lmd-val"><?= htmlspecialchars($contexte['anneeNom']) ?></span></div>
                    <div class="lmd-item"><span class="lmd-label"><?= __('pv_eval_code') ?> :</span><span class="lmd-val"><?= htmlspecialchars($contexte['codeEvaluation']) ?></span></div>
                    <div class="lmd-item"><span class="lmd-label"><?= __('pv_eval_label') ?> :</span><span class="lmd-val"><?= htmlspecialchars($contexte['libelleEvaluation'] ?? $contexte['typeEvaluation']) ?></span></div>
                    <div class="lmd-item"><span class="lmd-label"><?= __('pv_period') ?> :</span><span class="lmd-val"><?= htmlspecialchars($contexte['periodeLabel']) ?></span></div>
                    <div class="lmd-item"><span class="lmd-label"><?= __('pv_session') ?> :</span><span class="lmd-val"><?= htmlspecialchars($contexte['sessionNom'] ?? '-') ?></span></div>
                </div>
            </div>

            <!-- TABLEAU PRINCIPAL LMD -->
            <table class="pv-table-lmd">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 3%;">Rang</th>
                        <th rowspan="2" style="width: 22%;" class="col-student-lmd">Nom et prénom(s)</th>
                        <th rowspan="2" style="width: 3%;">Sexe</th>
                        
                        <?php foreach ($groupedMatieres as $grpName => $subs): ?>
                            <th colspan="<?= count($subs) + 1 ?>" class="group-header"><?= htmlspecialchars($grpName) ?></th>
                        <?php endforeach; ?>

                        <!-- <th rowspan="2" style="width: 6%;"><?= __('pv_credits_acquired') ?> / <?= __('pv_credits_expected') ?></th> -->
                        <th rowspan="2" style="width: 6%;"><?= __('pv_credits_acquired') ?></th>
                        <th rowspan="2" style="width: 5%;">Moy.UE</th>
                        <th rowspan="2" style="width: 6%;">Mention</th>
                        <th rowspan="2" style="width: 6%;"><?= __('pv_decision') ?></th>
                    </tr>
                    <tr>
                        <?php foreach ($groupedMatieres as $grpName => $subs): ?> 
                            <?php foreach ($subs as $m): ?>
                                <?php 
                                    $codeMatiere = htmlspecialchars($m['code_ue'] ?? $m['code'] ?? ('UV' . $m['id']));
                                    $crMatiere = (float)($m['coefficient'] ?? 1);
                                ?>
                                <th style="font-size: 7px; padding: 2px 1px; line-height: 1.15; min-width: 38px;">
                                    <div style="font-size: 6px; font-weight: 600; color: #4a5568;">[CR: <?= $crMatiere ?>]</div>
                                    <div style="font-weight: 900; font-size: 7.5px; margin: 1px 0;"><?= $codeMatiere ?></div>
                                </th>
                            <?php endforeach; ?>
                            <th style="font-size: 7px; background: #cbd5e1 !important; color: #000000; font-weight: 900; min-width: 42px;">
                                Moy. UE
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($currentChunk as $i => $el): ?>
                        <?php 
                            $realIdx = ($chunkIndex * 15) + $i + 1;
                            $isAdmis = !empty($el['isAdmisLmd']);
                        ?>
                        <tr>
                            <td><?= $el['rang'] ?? $realIdx ?></td>
                            <td class="col-student-lmd"><?= htmlspecialchars($el['nom'] . ' ' . $el['prenom']) ?></td>
                            <td><?= htmlspecialchars($el['sexe'] ?? 'M') ?></td>

                            <?php foreach ($groupedMatieres as $grpName => $subs): ?>
                                <?php foreach ($subs as $m): ?>
                                    <?php 
                                        $mid = (int)$m['id'];
                                        $n = $el['notesParMatiere'][$mid] ?? null;
                                    ?>
                                    <td style="font-size: 8px;">
                                        <?php if ($n !== null): ?>
                                            <?php $nVal = (float)$n; ?>
                                            <?php if ($nVal >= 10.0): ?>
                                                <span style="font-weight: 700; color: #000;"><?= number_format($nVal, 2, ',', ' ') ?></span>
                                            <?php else: ?>
                                                <span style="font-weight: 700; color: #cc0000;"><?= number_format($nVal, 2, ',', ' ') ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span>-</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>

                                <!-- Colonne Moy. UE pour ce groupe -->
                                <?php 
                                    $ueInfo = $el['moyennesUE'][$grpName] ?? null;
                                    $moyUEVal = $ueInfo['moyenne'] ?? null;
                                    $isEliminated = !empty($ueInfo['is_eliminated']) || $moyUEVal === null;
                                ?>
                                <td style="background: #f8fafc;">
                                    <?php if (!$isEliminated && $moyUEVal !== null): ?>
                                        <?php if ($moyUEVal >= 10.0): ?>
                                            <span class="val-pass"><?= number_format($moyUEVal, 2, ',', ' ') ?></span>
                                        <?php else: ?>
                                            <span class="val-fail"><?= number_format($moyUEVal, 2, ',', ' ') ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="val-fail" style="font-weight: bold;">EL</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>

                            <!-- Colonnes Finales -->
                            <td><?= (int)($el['creditsAcquis'] ?? 0) ?> / <?= (int)$totalCreditsExpected ?></td>
                            <td style="font-size: 9px; font-weight: 900;">
                                <?php if (($el['moyenneLmdDisplay'] ?? '-') === 'EL'): ?>
                                    <span class="val-fail">EL</span>
                                <?php elseif (($el['moyenneLmdDisplay'] ?? '-') !== '-'): ?>
                                    <span class="val-pass"><?= $el['moyenneLmdDisplay'] ?></span>
                                <?php else: ?>
                                    <span>-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($el['mention'] ?? '-') ?></td>
                            <td>
                                <?php if (($el['moyenneLmdDisplay'] ?? '-') !== '-'): ?>
                                    <?php if ($isAdmis): ?>
                                        <span class="decision-admis"><?= __('pv_admitted') ?></span>
                                    <?php else: ?>
                                        <span class="decision-rattrapage"><?= __('pv_rattrapage') ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span>-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- SEULEMENT SUR LA DERNIÈRE PAGE : STATISTIQUES, LÉGENDE ET SIGNATURES -->
            <?php if ($chunkIndex === $totalChunks - 1): ?>
                
                <!-- TABLEAU DES STATISTIQUES LMD -->
                <div class="stats-section">
                    <table class="stats-table">
                        <thead>
                            <tr>
                                <th colspan="4" class="stats-header-title">TABLEAU DES STATISTIQUES GLOBALES ET INDICE PÉDAGOGIQUE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th><?= __('pv_total_students') ?></th>
                                <td><?= $donneesPV['statsClasseLmd']['effectifTotal'] ?? count($eleves) ?></td>
                                <th>Total des crédits validés</th>
                                <td><?= $donneesPV['statsClasseLmd']['totalCreditsValides'] ?? 0 ?></td>
                            </tr>
                            <tr>
                                <th><?= __('pv_presents') ?></th>
                                <td><?= $donneesPV['statsClasseLmd']['presents'] ?? 0 ?></td>
                                <th>Total des crédits attendus</th>
                                <td><?= $donneesPV['statsClasseLmd']['totalCreditsAttendus'] ?? 0 ?></td>
                            </tr>
                            <tr>
                                <th><?= __('pv_absents') ?></th>
                                <td><?= $donneesPV['statsClasseLmd']['absents'] ?? 0 ?></td>
                                <th><?= __('pv_total_credits_obtained') ?></th>
                                <td><?= $donneesPV['statsClasseLmd']['totalCreditsObtenus'] ?? 0 ?></td>
                            </tr>
                            <tr>
                                <th><?= __('pv_admitted') ?></th>
                                <td class="val-pass"><?= $donneesPV['statsClasseLmd']['admis'] ?? 0 ?></td>
                                <th><?= __('pv_credits_unvalidated') ?></th>
                                <td class="val-fail"><?= $donneesPV['statsClasseLmd']['totalCreditsNonValides'] ?? 0 ?></td>
                            </tr>
                            <tr>
                                <th><?= __('pv_rattrapages_count') ?></th>
                                <td class="val-fail"><?= $donneesPV['statsClasseLmd']['rattrapages'] ?? 0 ?></td>
                                <th><?= __('pv_most_below_10') ?></th>
                                <td><?= htmlspecialchars($donneesPV['statsClasseLmd']['matierePlusNotesInf10'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th><?= __('pv_success_rate') ?></th>
                                <td style="font-weight: 900;"><?= $donneesPV['statsClasseLmd']['tauxReussite'] ?? '0' ?>%</td>
                                <th><?= __('pv_most_above_10') ?></th>
                                <td><?= htmlspecialchars($donneesPV['statsClasseLmd']['matierePlusNotesSup10'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th><?= __('pv_general_average') ?></th>
                                <td style="font-size: 9.5px; font-weight: 900;"><?= number_format((float)($donneesPV['moyenneGenerale'] ?? 0), 2, ',', ' ') ?></td>
                                <th><?= __('pv_best_avg') ?> / <?= __('pv_lowest_avg') ?></th>
                                <td><?= number_format((float)($donneesPV['statsClasseLmd']['meilleureMoyenne'] ?? 0), 2, ',', ' ') ?> / <?= number_format((float)($donneesPV['statsClasseLmd']['plusFaibleMoyenne'] ?? 0), 2, ',', ' ') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- SECTION LÉGENDE DE LECTURE -->
                <div class="legend-container">
                    <div class="legend-title"><?= __('pv_legend_reading') ?></div>
                    <div class="legend-grid">
                        <div class="legend-box">
                            <div class="legend-box-title"><?= __('pv_teaching_codes') ?></div>
                            <ul class="legend-list">
                                <?php foreach ($matieres as $m): ?>
                                    <li>
                                        <strong><?= htmlspecialchars($m['code_ue'] ?? $m['code'] ?? ('UE' . $m['id'])) ?></strong> : 
                                        <?= htmlspecialchars($m['nom']) ?> (<strong>CR : <?= (float)($m['coefficient'] ?? 1) ?></strong>)
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="legend-box">
                            <div class="legend-box-title"><?= __('pv_symbols_meaning') ?></div>
                            <ul class="legend-list">
                                <li><strong>EL</strong> : Élément de Module non validé (Moyenne < 10/20)</li>
                                <li><strong>Moy.</strong> : Moyenne de l'Unité d'Enseignement ou Générale (sur 20)</li>
                                <li><strong>UE</strong> : Unité d'Enseignement</li>
                                <li><strong>CR</strong> : Crédits de l'Unité d'Enseignement</li>
                                <li><strong class="val-pass">Admis</strong> : Moyenne générale ≥ 10,00/20</li>
                                <li><strong class="val-fail">Rattrapage</strong> : Moyenne générale < 10,00/20 (0 à 9,99)</li>
                                <li><strong>Mention</strong> : Appreciation académique (Passable, Assez Bien, Bien, Très Bien, Excellent)</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- SECTION SIGNATURES OFFICIELLES -->
                <div class="signatures-grid">
                    <div class="sig-card">
                        <span class="sig-title"><?= __('pv_cell_it') ?></span>
                    </div>
                    <div class="sig-card">
                        <span class="sig-title"><?= __('pv_direction_jury') ?></span>
                    </div>
                </div>

            <?php endif; ?>

            <!-- FOOTER MINIMAL -->
            <div class="minimal-footer">
                <span><?= __('pv_official_log') ?> (LMD) - <?= __('pv_generated_on') ?> <?= date('d/m/Y H:i') ?></span>
                <span class="page-num" style="font-weight: bold; border: 1px solid #000; padding: 1px 6px; border-radius: 2px;"></span>
                <span><?= __('pv_confidential_document') ?></span>
            </div>

        </div>
    <?php endforeach; ?>

</body>
</html>
