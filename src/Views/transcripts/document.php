<?php
/**
 * DOCUMENT : RELEVÉ DE NOTES OFFICIEL (TRANSCRIPT OF RECORDS)
 * DESIGN : Institutionnel Premium - Optimisé Page Unique A4 Portrait
 */

$lang = \App\Core\Locale::get();

// Formater les données de contact et établissement
$schoolName = $institution['school_name'] ?? 'ÉTABLISSEMENT SCOLAIRE';
$schoolCode = $institution['school_code'] ?? 'FUTURA';
$schoolDisplayName = mb_strtoupper((string) $schoolName, 'UTF-8');

$phone = $institution['school_phone'] ?? '';
$email = $institution['school_email'] ?? '';
$city  = $institution['school_city'] ?? '';
$bp    = $institution['school_po_box'] ?? '';

$contactParts = [];
if (!empty($bp)) $contactParts[] = "BP: " . $bp;
if (!empty($city)) $contactParts[] = $city;
if (!empty($phone)) $contactParts[] = "Tél: " . $phone;
if (!empty($email)) $contactParts[] = "Email: " . $email;
$contact = implode(' - ', $contactParts);

?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('transcript_of_records') ?> - <?= htmlspecialchars($schoolCode) ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', 'Arial', sans-serif;
            font-size: 9.5px;
            color: #000000;
            background: #f1f5f9;
            line-height: 1.2;
        }

        /* Toolbar Écran */
        .transcript-toolbar {
            position: sticky;
            top: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
            background: #0f172a;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .transcript-toolbar h1 {
            font-size: 15px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-action {
            padding: 6px 14px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-print { background: #2563eb; color: #ffffff; }
        .btn-print:hover { background: #1d4ed8; }
        .btn-back { background: #475569; color: #ffffff; }
        .btn-back:hover { background: #334155; }

        /* Conteneur Feuille A4 Compact */
        .transcript-page {
            width: 210mm;
            min-height: 297mm;
            margin: 12px auto;
            background: #ffffff;
            padding: 6mm 8mm;
            position: relative;
            box-shadow: 0 6px 20px rgba(0,0,0,0.06);
            page-break-after: always;
            page-break-inside: avoid;
        }
        .transcript-page:last-child { page-break-after: auto; }

        /* En-tête officiel compact */
        .header-wrapper { width: 100%; margin-bottom: 4px; border-bottom: 1.5px solid #000; padding-bottom: 3px; }
        .header-left { float: left; width: 41%; text-align: center; }
        .header-center { float: left; width: 18%; text-align: center; }
        .header-right { float: right; width: 41%; text-align: center; }
        .header-side-content { width: 100%; padding: 0 2px; }
        .school-name-row { clear: both; width: 100%; text-align: center; padding-top: 2px; }
        .school-name-display { font-weight: 900; font-size: 11.5px; text-transform: uppercase; line-height: 1.1; }
        .academic-year-display { font-weight: 800; font-size: 9.5px; text-transform: uppercase; margin-top: 1px; }
        .header-line { font-size: 8px; font-weight: 800; margin: 0; text-transform: uppercase; line-height: 1.15; }
        .header-contact { font-size: 7.5px; margin: 0; opacity: 0.9; text-transform: uppercase; font-weight: 600; }
        .logo-box { width: 52px; height: 52px; margin: 0 auto; display: flex; align-items: center; justify-content: center; }
        .logo-box img { max-width: 100%; max-height: 100%; object-fit: contain; }

        /* Carte d'identité compacte (2 lignes) */
        .student-card {
            width: 100%;
            border: 1.5px solid #000;
            margin-bottom: 5px;
            background: #fafafa;
        }
        .student-card-table {
            width: 100%;
            border-collapse: collapse;
        }
        .student-card-table td {
            padding: 3px 6px;
            font-size: 9.5px;
            border: 1px solid #cbd5e1;
        }
        .info-label { font-weight: 800; color: #0f172a; text-transform: uppercase; font-size: 8.5px; margin-right: 2px; }
        .info-value { font-weight: 800; color: #000; text-transform: uppercase; font-size: 9px; }

        /* Titre du tableau du Jury */
        .jury-title-box {
            text-align: center;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #e2e8f0;
            border: 1.5px solid #000;
            padding: 3px 4px;
            margin-bottom: 5px;
        }

        /* Tableau principal ultra-optimisé */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 2.5px 4px;
            font-size: 9px;
            text-align: center;
            line-height: 1.15;
        }
        .main-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 8.5px;
        }
        .semester-divider-row td {
            background-color: #1e293b !important;
            color: #ffffff !important;
            font-weight: 900;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 3px;
        }
        .group-header-row td {
            background-color: #f1f5f9;
            font-weight: 800;
            text-align: left;
            padding-left: 6px;
            font-size: 8.5px;
            color: #0f172a;
            text-transform: uppercase;
        }
        .summary-row td {
            background-color: #f8fafc;
            font-weight: 800;
            font-size: 8.5px;
            padding: 3px;
        }

        /* Clôture Générale & Récapitulatif compact (1 ligne) */
        .closure-box {
            width: 100%;
            border: 1.5px solid #000;
            background: #f8fafc;
            padding: 4px 8px;
            margin-top: 4px;
            margin-bottom: 6px;
        }
        .closure-grid {
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            font-size: 9.5px;
            font-weight: 800;
        }
        .closure-item {
            flex: 1;
            padding: 2px 4px;
            border-right: 1px dashed #cbd5e1;
        }
        .closure-item:last-child { border-right: none; }
        .closure-label { font-size: 8px; color: #475569; text-transform: uppercase; display: block; }
        .closure-val { font-size: 10.5px; color: #000; font-weight: 900; margin-top: 1px; }
        .valide-tag { color: #15803d; font-weight: 900; }
        .non-valide-tag { color: #b91c1c; font-weight: 900; }

        /* Signatures compactes */
        .signatures-container {
            width: 100%;
            display: flex;
            justify-content: space-between;
            margin-top: 6px;
            padding: 0 8px;
        }
        .signature-block {
            width: 44%;
            text-align: center;
            font-weight: 800;
            font-size: 9.5px;
            text-transform: uppercase;
        }
        .signature-space {
            height: 32px;
        }

        /* Impression A4 strict page unique */
        @media print {
            .transcript-toolbar { display: none !important; }
            body { background: #ffffff !important; padding: 0 !important; }
            .transcript-page {
                box-shadow: none !important;
                margin: 0 !important;
                width: 100% !important;
                padding: 4mm 6mm !important;
                min-height: auto !important;
                page-break-after: always !important;
                page-break-inside: avoid !important;
            }
            @page {
                size: A4 portrait;
                margin: 4mm 5mm;
            }
        }
    </style>
</head>
<body>

    <!-- TOOLBAR (ÉCRAN) -->
    <div class="transcript-toolbar">
        <h1>
            <span><?= __('transcript_of_records') ?></span>
        </h1>
        <div style="display: flex; gap: 10px;">
            <button onclick="window.print()" class="btn-action btn-print">
                <span><?= __('print') ?></span>
            </button>
            <a href="/transcripts" class="btn-action btn-back">
                <span><?= __('back') ?></span>
            </a>
        </div>
    </div>

    <?php foreach ($studentsData as $data): ?>
        <?php 
        $st = $data['student'];
        $displayMatricule = $data['display_matricule'];
        $semesters = $data['semesters'];
        $summary = $data['summary'];
        ?>

        <div class="transcript-page">

            <!-- A. EN-TÊTE MINISTÉRIEL ET LOGO OFFICIEL -->
            <div class="header-wrapper">
                <div class="header-left">
                    <div class="header-side-content">
                        <p class="header-line"><?= htmlspecialchars((string) ($institution['school_republic'] ?? __('republic_of_cameroon'))) ?></p>
                        <p class="header-line"><?= htmlspecialchars((string) ($institution['school_motto'] ?? __('motto'))) ?></p>
                        <p class="header-line"><?= htmlspecialchars((string) ($institution['school_ministry'] ?? __('ministry_secondary_education'))) ?></p>
                        <p class="header-line"><?= htmlspecialchars((string) ($institution['school_slogan'] ?? __('slogan'))) ?></p>
                        <p class="header-contact"><?= htmlspecialchars(strtoupper($contact)) ?></p>
                    </div>
                </div>

                <div class="header-center">
                    <div class="logo-box">
                        <?php if (!empty($institution['school_logo_base64'])): ?>
                            <img src="<?= $institution['school_logo_base64'] ?>" alt="Logo">
                        <?php elseif (!empty($institution['school_logo'])):
                            $logoPath = \App\Core\Helpers::normalizeLogoPath((string) $institution['school_logo']); ?>
                            <img src="<?= htmlspecialchars($logoPath) ?>" alt="Logo">
                        <?php else: ?>
                            <div style="font-weight:bold; font-size:9px;">LOGO</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="header-right">
                    <div class="header-side-content">
                        <p class="header-line"><?= htmlspecialchars((string) ($institution['school_republic_en'] ?? 'REPUBLIC OF CAMEROON')) ?></p>
                        <p class="header-line"><?= htmlspecialchars((string) ($institution['school_motto_en'] ?? 'PEACE - WORK - FATHERLAND')) ?></p>
                        <p class="header-line"><?= htmlspecialchars((string) ($institution['school_ministry_en'] ?? 'MINISTRY OF SECONDARY EDUCATION')) ?></p>
                        <p class="header-line"><?= htmlspecialchars((string) ($institution['school_slogan_en'] ?? 'DISCIPLINE - WORK - SUCCESS')) ?></p>
                        <p class="header-contact"><?= htmlspecialchars(strtoupper($contact)) ?></p>
                    </div>
                </div>

                <div class="school-name-row">
                    <div class="school-name-display"><?= htmlspecialchars($schoolDisplayName) ?></div>
                    <div class="academic-year-display"><?= __('academic_years') ?> : <?= htmlspecialchars((string) ($data['academic_year_name'])) ?></div>
                </div>
            </div>

            <!-- B. INFORMATIONS COMPACTES DE L'ÉLÈVE (2 LIGNES) -->
            <div class="student-card">
                <table class="student-card-table">
                    <tr>
                        <td style="width: 35%;"><span class="info-label"><?= __('full_name') ?>:</span> <span class="info-value"><?= htmlspecialchars(strtoupper($st['nom'] . ' ' . $st['prenom'])) ?></span></td>
                        <td style="width: 20%;"><span class="info-label"><?= __('matricule') ?>:</span> <span class="info-value"><?= htmlspecialchars($displayMatricule) ?></span></td>
                        <td style="width: 25%;">
                            <span class="info-label"><?= __('ne_le') ?>:</span> 
                            <span class="info-value"><?= !empty($st['date_naissance']) ? date('d/m/Y', strtotime($st['date_naissance'])) : 'N/A' ?> <?= __('a_lieu') ?> <?= htmlspecialchars($st['lieu_naissance'] ?? 'N/A') ?></span>
                        </td>
                        <td style="width: 20%;"><span class="info-label"><?= __('filiere') ?>:</span> <span class="info-value"><?= htmlspecialchars($data['filiere']) ?></span></td>
                    </tr>
                    <tr>
                        <td><span class="info-label"><?= __('niveau') ?>:</span> <span class="info-value"><?= htmlspecialchars($data['niveau']) ?></span></td>
                        <td><span class="info-label"><?= __('cycle') ?>:</span> <span class="info-value"><?= htmlspecialchars($data['cycle']) ?></span></td>
                        <td><span class="info-label"><?= __('specialite') ?>:</span> <span class="info-value"><?= htmlspecialchars($data['specialite']) ?></span></td>
                        <td><span class="info-label"><?= __('option_label') ?>:</span> <span class="info-value"><?= htmlspecialchars($data['option']) ?></span></td>
                    </tr>
                </table>
            </div>

            <!-- C. TITRE DU TABLEAU DU JURY -->
            <div class="jury-title-box">
                <?= __('pv_jury_title') ?> ............................................................
            </div>

            <!-- D. TABLEAU PRINCIPAL (ORGANISÉ AVEC SESSION ET CODE UE OPTIMISÉ) -->
            <table class="main-table">
                <thead>
                    <tr>
                        <th style="width: 10%;"><?= __('code_uv') ?></th>
                        <th style="width: 38%; text-align: left; padding-left: 6px;"><?= __('intitule_enseignements') ?></th>
                        <th style="width: 10%;"><?= __('credits_totaux') ?></th>
                        <th style="width: 10%;"><?= __('note_20') ?></th>
                        <th style="width: 10%;"><?= __('mention') ?></th>
                        <th style="width: 10%;"><?= __('credits_acquis') ?></th>
                        <th style="width: 12%;"><?= __('session') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($semesters as $semNum => $semData): ?>
                        <!-- LIGNE DE SÉPARATION DE SEMESTRE -->
                        <tr class="semester-divider-row">
                            <td colspan="7"><?= htmlspecialchars($semData['title']) ?></td>
                        </tr>

                        <?php if (!empty($semData['groups'])): ?>
                            <?php foreach ($semData['groups'] as $grpName => $grpInfo): ?>
                                <!-- ENTÊTE DU GROUPE DE MODULES (UE : CODE + LIBELLÉ) -->
                                <tr class="group-header-row">
                                    <td colspan="7">UE : <?= htmlspecialchars($grpInfo['code_ue']) ?> &mdash; <?= htmlspecialchars($grpInfo['libelle']) ?></td>
                                </tr>

                                <?php foreach ($grpInfo['subjects'] as $sub): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($sub['code_uv']) ?></td>
                                        <td style="text-align: left; padding-left: 6px; font-weight: 600;"><?= htmlspecialchars($sub['nom']) ?></td>
                                        <td><?= $sub['coefficient'] ?></td>
                                        <td style="font-weight: 800;"><?= number_format($sub['note'], 2) ?></td>
                                        <td><?= htmlspecialchars($sub['mention']) ?></td>
                                        <td style="font-weight: 800; color: <?= $sub['credits_acquis'] > 0 ? '#15803d' : '#b91c1c' ?>;"><?= $sub['credits_acquis'] ?></td>
                                        <td><?= htmlspecialchars($sub['session']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- RÉCAPITULATIF DE FIN DE SEMESTRE -->
                        <tr class="summary-row">
                            <td colspan="2" style="text-align: right; padding-right: 8px; font-weight: 800; text-transform: uppercase;">
                                RÉCAPITULATIF <?= htmlspecialchars($semData['title']) ?> :
                            </td>
                            <td style="font-weight: 900;"><?= $semData['stats']['expected_credits'] ?></td>
                            <td style="font-weight: 900;"><?= number_format($semData['stats']['average'], 2) ?></td>
                            <td style="font-weight: 800; color: <?= $semData['stats']['decision'] === __('valide') ? '#15803d' : '#b91c1c' ?>;">
                                <?= htmlspecialchars($semData['stats']['decision']) ?>
                            </td>
                            <td style="font-weight: 900;"><?= $semData['stats']['earned_credits'] ?></td>
                            <td style="font-weight: 900;"><?= htmlspecialchars($semData['session']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- E. CLÔTURE DU DOCUMENT & RÉCAPITULATIF GÉNÉRAL -->
            <div class="closure-box">
                <div class="closure-grid">
                    <div class="closure-item">
                        <span class="closure-label"><?= __('credits_acquis_attendus') ?></span>
                        <div class="closure-val"><?= $summary['total_earned_credits'] ?> / <?= $summary['total_expected_credits'] ?></div>
                    </div>
                    <div class="closure-item">
                        <span class="closure-label"><?= __('moyenne_generale') ?></span>
                        <div class="closure-val" style="font-size: 11.5px; color: #0f172a;"><?= number_format($summary['general_average'], 2) ?> / 20</div>
                    </div>
                    <div class="closure-item">
                        <span class="closure-label"><?= __('decision_finale') ?> & <?= __('mention_generale') ?></span>
                        <div class="closure-val">
                            <span class="<?= $summary['final_decision'] === __('valide') ? 'valide-tag' : 'non-valide-tag' ?>">
                                <?= htmlspecialchars($summary['final_decision']) ?>
                            </span>
                            <span>(<?= htmlspecialchars($summary['general_mention']) ?>)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- F. SIGNATURES -->
            <div class="signatures-container">
                <div class="signature-block">
                    <div><?= __('secretaire_du_jury') ?></div>
                    <div class="signature-space"></div>
                    <div style="font-size: 8px; color: #64748b;">(Nom & Signature)</div>
                </div>
                <div class="signature-block">
                    <div><?= __('president_du_jury') ?></div>
                    <div class="signature-space"></div>
                    <div style="font-size: 8px; color: #64748b;">(Nom, Signature & Cachet)</div>
                </div>
            </div>

        </div>
    <?php endforeach; ?>

</body>
</html>
