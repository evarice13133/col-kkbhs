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

// --- 1.1 Récupération et comptage initial des matières et des élèves ---
$matieres = $donneesPV['matieres'] ?? [];
$eleves   = $donneesPV['matriceEleves'] ?? [];
$nbMatieres = count($matieres);

// --- 1.2 Découpage (Chunking) des élèves par groupes de 20 ---
// Permet d'assurer une pagination A4 Paysage parfaite sans débordement vertical.
$chunks = array_chunk($eleves, 20);
$totalChunks = count($chunks);

// --- 1.3 Calcul des statistiques globales de la classe ---
// Détermine le nombre d'élèves admis (moyenne >= 10) et échoués, ainsi que les moyennes extrêmes.
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

// --- 1.4 Récupération sécurisée du logo institutionnel via le LogoManager ---
// Utilisation du format Base64 ou de l'URL pour assurer l'affichage même lors de l'impression hors-ligne.
$db = \App\Core\Database::getInstance()->getConnection();
$teachingTypeId = (int) ($contexte['teaching_type_id'] ?? 0);
$logoManager = \App\Core\LogoManager::getInstance($db, $teachingTypeId > 0 ? $teachingTypeId : null);
$logoData = [
    'has_logo' => $logoManager->hasLogo(),
    'base64' => $logoManager->hasLogo() ? $logoManager->getLogoBase64() : '',
    'url' => $logoManager->getLogoUrl(),
    'fallback_letter' => $logoManager->getFallbackLetter()
];

// --- 1.5 Regroupement thématique des matières par groupe d'enseignement ---
// Les matières sont regroupées (ex: Groupe 1, Scientifiques, etc.) pour structurer l'en-tête du tableau.
$groupedMatieres = [];
foreach ($matieres as $m) {
    $grp = $m['groupe_nom'] ?? $m['groupe'] ?? $m['group_name'] ?? 'AUTRES';
    $groupedMatieres[$grp][] = $m;
}

// --- 1.6 Analyse Pédagogique fine de la classe ---
// Détermine pour chaque matière la note maximale, la note minimale et le taux de réussite.
// Identifie ensuite la "Matière Forte" (plus de moyennes >= 10) et la "Matière Faible".
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

// --- 1.7 Synthèse des performances par groupes de matières ---
// Calcule la moyenne pondérée (Moyenne = Points / Coefficients) pour chaque groupe de matières.
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

// --- 1.8 Calcul dynamique des dimensions et détection linguistique ---
$subWidth = $nbMatieres > 0 ? (75 / $nbMatieres) : 15;
$lang = \App\Core\Locale::get();
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
        /* Importation de la typographie premium Inter pour un rendu type SaaS moderne */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        
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

    <!-- 3. BOUCLE DE RENDU PAGINÉE (Par groupes de 20 élèves) -->
    <?php foreach ($chunks as $chunkIndex => $currentChunk): ?>
        <!-- Chaque groupe est rendu dans une feuille A4 Paysage physique distincte -->
        <div class="pv-page-container">
            
            <!-- 3.1 EN-TÊTE OFFICIEL BILINGUE DE L'ÉTABLISSEMENT -->
            <header class="pv-header">
                <!-- Côté gauche : République du Cameroun (Français) -->
                <div class="header-side">
                    <div><?= htmlspecialchars($contexte['institution']['school_republic'] ?? 'REPUBLIQUE DU CAMEROUN') ?></div>
                    <div style="font-weight: bold; font-style: italic;"><?= htmlspecialchars($contexte['institution']['school_motto'] ?? 'Paix - Travail - Patrie') ?></div>
                    <div>**********</div>
                    <div><?= htmlspecialchars($contexte['institution']['school_ministry'] ?? 'MINISTERE DES ENSEIGNEMENTS SECONDAIRES') ?></div>
                </div>
                <!-- Centre : Logo institutionnel et Nom de l'établissement -->
                <div class="header-center" style="text-align: center;">
                    <?php if ($logoData['has_logo'] && !empty($logoData['base64'])): ?>
                        <img src="<?= htmlspecialchars($logoData['base64']) ?>" class="school-logo" alt="Logo">
                    <?php elseif ($logoData['has_logo'] && !empty($logoData['url'])): ?>
                        <img src="<?= htmlspecialchars($logoData['url']) ?>" class="school-logo" alt="Logo">
                    <?php endif; ?>
                    <div class="school-name"><?= htmlspecialchars($contexte['institution']['school_name'] ?? 'NotesMaster') ?></div>
                </div>
                <!-- Côté droit : Republic of Cameroon (Anglais) -->
                <div class="header-side">
                    <div><?= htmlspecialchars($contexte['institution']['school_republic_en'] ?? 'REPUBLIC OF CAMEROON') ?></div>
                    <div style="font-weight: bold; font-style: italic;"><?= htmlspecialchars($contexte['institution']['school_motto_en'] ?? 'Peace - Work - Fatherland') ?></div>
                    <div>**********</div>
                    <div><?= htmlspecialchars($contexte['institution']['school_ministry_en'] ?? 'MINISTRY OF SECONDARY EDUCATION') ?></div>
                </div>
            </header>

            <!-- 3.2 CARTUCHE DE TITRE (Type de PV, Classe, Année scolaire et Période) -->
            <div class="pv-title-box">
                <span class="pv-main-title"><?= __('pv_document_title') ?> - <?= $contexte['typeEvaluation'] ?></span>
                <div style="font-size: 9.5px; font-weight: 700; text-transform: uppercase;">
                    <?= __('class') ?> : <?= $contexte['classeNom'] ?> | <?= __('year') ?> : <?= $contexte['anneeNom'] ?> | <?= __('period') ?> : <?= $contexte['periodeLabel'] ?>
                </div>
            </div>

            <!-- 3.3 TABLEAU DES NOTES ET MOYENNES (Chunk courant de 20 élèves) -->
            <table class="pv-table">
                <!-- Définition stricte des largeurs des colonnes pour éviter tout débordement -->
                <colgroup>
                    <col style="width: 3.5%;"> <!-- Colonne RG (remplace N°) -->
                    <col style="width: 15.5%;">  <!-- Colonne Nom & Prénoms (espace augmenté pour le nom d'élève) -->
                    <?php foreach ($matieres as $m): ?>
                        <col style="width: <?= $subWidth ?>%;"> <!-- Colonnes Matières (Dimensionnement dynamique) -->
                    <?php endforeach; ?>
                    <col style="width: 6%;">  <!-- Colonne Moyenne Individuelle -->
                </colgroup>
                <!-- 3.4 EN-TÊTES DE DEUX NIVEAUX : Groupes de matières puis matières individuelles avec coefficients -->
                <thead>
                    <tr>
                        <th rowspan="2">RG</th>
                        <th rowspan="2" class="col-student"><?= __('student_name') ?></th>
                        <?php foreach ($groupedMatieres as $grp => $subs): ?>
                            <!-- Affiche le nom du groupe (ex: Scientifique) fusionné sur le nombre de ses matières -->
                            <th colspan="<?= count($subs) ?>" class="group-header"><?= htmlspecialchars($grp) ?></th>
                        <?php endforeach; ?>
                        <th rowspan="2">MOY.</th>
                    </tr>
                    <tr>
                        <?php foreach ($groupedMatieres as $grp => $subs): ?>
                            <?php foreach ($subs as $m): ?>
                                <!-- Affiche le nom de la matière et son coefficient (ex: C:4) -->
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
                <!-- 3.5 CORPS DE LA TABLE : Données des élèves -->
                <tbody>
                    <?php foreach ($currentChunk as $i => $el): ?>
                        <?php 
                            // Calcul de l'index réel de l'élève sur la classe complète et détection d'échec
                            $realIdx = ($chunkIndex * 20) + $i;
                            $isFail = ($el['moyenne'] !== null && $el['moyenne'] < 10);
                        ?>
                        <tr class="<?= $isFail ? 'row-fail' : '' ?>">
                            <td class="cell-rank" style="font-weight: 800;"><?= $realIdx + 1 ?></td>
                            <td class="col-student"><?= htmlspecialchars($el['nom'] . ' ' . $el['prenom']) ?></td>
                            <?php foreach ($groupedMatieres as $grp => $subs): ?>
                                <?php foreach ($subs as $m): ?>
                                    <?php 
                                        $n = $el['notesParMatiere'][$m['id']] ?? null; 
                                        $badgeClass = 'note-pass';
                                        if ($n !== null) {
                                            $nVal = (float)$n;
                                            if ($nVal < 10) {
                                                // Note sous la moyenne : Badge Rouge
                                                $badgeClass = 'note-badge note-fail';
                                            } elseif ($nVal >= 14) {
                                                // Note excellente : Badge Vert
                                                $badgeClass = 'note-badge note-good';
                                            }
                                        }
                                    ?>
                                    <td>
                                        <?php if ($n !== null): ?>
                                            <span class="<?= $badgeClass ?>"><?= number_format($n, 2) ?></span>
                                        <?php else: ?>
                                            <span style="color: #cbd5e1;">-</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                            <!-- Moyenne générale individuelle -->
                            <td class="cell-avg <?= $isFail ? 'cell-fail' : '' ?>">
                                <?= ($el['moyenne'] !== null) ? number_format($el['moyenne'], 2) : '-' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                
                <!-- 3.6 MOYENNES DE CLASSE PAR MATIÈRE (Affichées en pied de tableau uniquement sur la dernière page) -->
                <?php if ($chunkIndex === $totalChunks - 1): ?>
                <tfoot>
                    <tr style="background: #f1f5f9; font-weight: 800; border-top: 2px solid #0f172a;">
                        <td colspan="2" style="text-align: left; padding-left: 10px; color: #0f172a; font-size: 8.5px;"><?= __('pv_class_avg_per_subject') ?></td>
                        <?php foreach ($groupedMatieres as $grp => $subs): ?>
                            <?php foreach ($subs as $m): ?>
                                <?php 
                                    $avgVal = $donneesPV['moyennesClasse'][$m['id']] ?? null;
                                    $badgeClass = '';
                                    if ($avgVal !== null && $avgVal !== '-') {
                                        $avgValNum = (float)$avgVal;
                                        if ($avgValNum < 10) {
                                            $badgeClass = 'note-badge note-fail';
                                        } elseif ($avgValNum >= 14) {
                                            $badgeClass = 'note-badge note-good';
                                        }
                                    }
                                ?>
                                <td style="font-weight: 700; color: #0f172a;">
                                    <?php if ($avgVal !== null && $avgVal !== '-'): ?>
                                        <span class="<?= $badgeClass ?>"><?= htmlspecialchars($avgVal) ?></span>
                                    <?php else: ?>
                                        <span style="color: #cbd5e1;">-</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        <td class="cell-avg" style="border-top: 2px solid #0f172a; font-size: 9px; font-weight: 900; background: #e2e8f0;">
                            <?= ($donneesPV['moyenneGenerale'] !== null) ? number_format((float)$donneesPV['moyenneGenerale'], 2) : '-' ?>
                        </td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>

            <!-- 3.7 PIED DE PAGE DE SYNTHÈSE GLOBALE (Rendu exclusivement sur la dernière page A4) -->
            <div class="pv-footer">
                <?php if ($chunkIndex === $totalChunks - 1): ?>
                    <?php
                        // Préparation de l'indexation pour la synthèse des groupes de matières (4 maximum)
                        $sliceGroups = [];
                        foreach (array_slice($groupStats, 0, 4) as $name => $s) {
                            $sliceGroups[] = [
                                'name' => $name,
                                's' => $s,
                                'gAvg' => $s['coefs'] > 0 ? ($s['pts'] / $s['coefs']) : 0
                            ];
                        }

                        // Calcul des totaux globaux cumulés sur tous les groupes de matières (pour la ligne de synthèse)
                        $totalPtsAllGroups = 0;
                        $totalCoefsAllGroups = 0;
                        $totalAboveAllGroups = 0;
                        $totalCountAllGroups = 0;
                        foreach ($groupStats as $s) {
                            $totalPtsAllGroups += $s['pts'];
                            $totalCoefsAllGroups += $s['coefs'];
                            $totalAboveAllGroups += $s['above'];
                            $totalCountAllGroups += $s['total'];
                        }
                        $avgAllGroups = $totalCoefsAllGroups > 0 ? ($totalPtsAllGroups / $totalCoefsAllGroups) : 0;
                        $pctAllGroups = $totalCountAllGroups > 0 ? ($totalAboveAllGroups / $totalCountAllGroups * 100) : 0;
                    ?>
                    <div class="footer-flex">
                        <!-- TABLEAU DE SYNTHÈSE UNIQUE ET HARMONISÉ (Regroupe Stats, Analyse Pédagogique et Moyennes de Groupes) -->
                        <table class="summary-table">
                            <thead>
                                <tr>
                                    <th colspan="2" class="table-title"><?= __('pv_general_stats') ?></th>
                                    <th colspan="2" class="table-title"><?= __('pv_pedagogical_analysis') ?></th>
                                    <th colspan="3" class="table-title"><?= __('pv_groups_synthesis') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Ligne 1 : Effectif / Matière Forte / Premier Groupe -->
                                <tr>
                                    <th><?= __('pv_total_students') ?></th>
                                    <td><?= $contexte['effectif'] ?></td>
                                    
                                    <th><?= __('pv_top_subject') ?></th>
                                    <td><?= $bestSub ? htmlspecialchars($bestSub['nom']) : '-' ?></td>
                                    
                                    <?php if (isset($sliceGroups[0])): ?>
                                        <td style="font-weight:bold;"><?= htmlspecialchars(mb_substr($sliceGroups[0]['name'], 0, 15)) ?></td>
                                        <td><?= number_format($sliceGroups[0]['gAvg'], 2) ?></td>
                                        <td><?= $sliceGroups[0]['s']['total'] > 0 ? number_format(($sliceGroups[0]['s']['above'] / $sliceGroups[0]['s']['total'] * 100), 0) : '0' ?>%</td>
                                    <?php else: ?>
                                        <td colspan="3" style="background: #f8fafc;">-</td>
                                    <?php endif; ?>
                                </tr>
                                <!-- Ligne 2 : Admis & Échoués / Matière Faible / Deuxième Groupe -->
                                <tr>
                                    <th><?= __('pv_passed_failed') ?></th>
                                    <td><?= $nbAdmis ?> / <?= $nbEchoue ?></td>
                                    
                                    <th><?= __('pv_flop_subject') ?></th>
                                    <td><?= $worstSub ? htmlspecialchars($worstSub['nom']) : '-' ?></td>
                                    
                                    <?php if (isset($sliceGroups[1])): ?>
                                        <td style="font-weight:bold;"><?= htmlspecialchars(mb_substr($sliceGroups[1]['name'], 0, 15)) ?></td>
                                        <td><?= number_format($sliceGroups[1]['gAvg'], 2) ?></td>
                                        <td><?= $sliceGroups[1]['s']['total'] > 0 ? number_format(($sliceGroups[1]['s']['above'] / $sliceGroups[1]['s']['total'] * 100), 0) : '0' ?>%</td>
                                    <?php else: ?>
                                        <td colspan="3" style="background: #f8fafc;">-</td>
                                    <?php endif; ?>
                                </tr>
                                <!-- Ligne 3 : Taux de Réussite / Moyenne du Premier / Troisième Groupe -->
                                <tr>
                                    <th><?= __('pv_success_rate') ?></th>
                                    <td style="font-weight: 800;"><?= $donneesPV['tauxReussiteGlobal'] ?? '-' ?>%</td>
                                    
                                    <th><?= $lang === 'fr' ? 'MOY. DU PREMIER' : 'TOP AVG' ?></th>
                                    <td style="font-weight: 700;"><?= number_format($maxAvg, 2) ?></td>
                                    
                                    <?php if (isset($sliceGroups[2])): ?>
                                        <td style="font-weight:bold;"><?= htmlspecialchars(mb_substr($sliceGroups[2]['name'], 0, 15)) ?></td>
                                        <td><?= number_format($sliceGroups[2]['gAvg'], 2) ?></td>
                                        <td><?= $sliceGroups[2]['s']['total'] > 0 ? number_format(($sliceGroups[2]['s']['above'] / $sliceGroups[2]['s']['total'] * 100), 0) : '0' ?>%</td>
                                    <?php else: ?>
                                        <td colspan="3" style="background: #f8fafc;">-</td>
                                    <?php endif; ?>
                                </tr>
                                <!-- Ligne 4 : Moyenne Générale / Moyenne du Dernier / Quatrième Groupe ou Synthèse cumulée -->
                                <tr>
                                    <th><?= __('pv_general_average') ?></th>
                                    <td style="font-weight: 900; font-size: 11px;"><?= number_format((float)$donneesPV['moyenneGenerale'], 2) ?></td>
                                    
                                    <th><?= $lang === 'fr' ? 'MOY. DU DERNIER' : 'LAST AVG' ?></th>
                                    <td style="font-weight: 700;"><?= number_format($minAvg, 2) ?></td>
                                    
                                    <?php if (isset($sliceGroups[3])): ?>
                                        <td style="font-weight:bold;"><?= htmlspecialchars(mb_substr($sliceGroups[3]['name'], 0, 15)) ?></td>
                                        <td><?= number_format($sliceGroups[3]['gAvg'], 2) ?></td>
                                        <td><?= $sliceGroups[3]['s']['total'] > 0 ? number_format(($sliceGroups[3]['s']['above'] / $sliceGroups[3]['s']['total'] * 100), 0) : '0' ?>%</td>
                                    <?php else: ?>
                                        <td style="font-weight: 800; background: #f8fafc; color: #000000; font-size: 7.5px;"><?= $lang === 'fr' ? 'SYNTHÈSE CLASSE' : 'CLASS TOTAL' ?></td>
                                        <td style="font-weight: 800; background: #f8fafc; color: #000000;"><?= number_format($avgAllGroups, 2) ?></td>
                                        <td style="font-weight: 800; background: #f8fafc; color: #000000;"><?= number_format($pctAllGroups, 0) ?>%</td>
                                    <?php endif; ?>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Grille de Signature pour l'Authentification Administrative -->
                        <div class="sig-grid" style="width: 25%;">
                            <div class="sig-box"><span class="sig-label"><?= __('pv_teacher_in_charge') ?></span></div>
                            <div class="sig-box"><span class="sig-label"><?= __('pv_principal') ?></span></div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 3.8 BANDEAU DE BAS DE PAGE (Métadonnées de sécurité et pagination automatique) -->
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
