<?php
// Vue du Reçu Officiel de Versement
// Compatible impression A4 Portrait (Exemplaire Parent et Exemplaire Administration)
// Support Dompdf et impression standard par le navigateur

use App\Core\Database;
use App\Core\LogoManager;

$db = Database::getInstance()->getConnection();
$logoManager = LogoManager::getInstance($db);
$logoBase64 = $logoManager->hasLogo() ? $logoManager->getLogoBase64() : '';

// 1. Charger les settings de l'école
$settingsStore = new \App\Services\SettingsStore($db);
$settings = $settingsStore->all();

// URL de vérification publique pour le QR Code
$verifyUrl = APP_URL . '/verify-receipt/' . urlencode($payment['verification_code'] ?? '');

$qrCodeApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($verifyUrl);

// Tenter de convertir le QR code en Base64 pour garantir son rendu local dans Dompdf
$qrCodeSrc = $qrCodeApiUrl;
try {
    $ctx = stream_context_create([
        "ssl" => [
            "verify_peer" => false,
            "verify_peer_name" => false,
        ],
        "http" => [
            "timeout" => 3
        ]
    ]);
    $qrImage = @file_get_contents($qrCodeApiUrl, false, $ctx);
    if ($qrImage) {
        $qrCodeSrc = 'data:image/png;base64,' . base64_encode($qrImage);
    }
} catch (\Throwable $e) {
    // Garde le fallback en URL
}

// Active language for PDF and local translations
$app_lang = \App\Core\Session::get('app_lang', 'fr');

// Nature de paiement conviviaux
$nature = __('other_payment');
if ($payment['type'] === 'inscription') {
    $nature = __('registration_payment_option');
} elseif ($payment['type'] === 'scolarite') {
    $nature = __('tuition_payment_option');
}

// Déterminer le statut de duplicata
$isDuplicate = (int)($payment['print_count'] ?? 1) > 1;
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars((string) __('lang')) ?>">
<head>
    <meta charset="UTF-8">
    <title><?= __('official_payment_receipt_no') ?><?= h($payment['id']) ?></title>
    <style>
        /* Optimisation stricte A4 Portrait pour faire tenir les 2 reçus sur une seule page */
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.25;
            color: #1e293b;
            margin: 0;
            padding: 8px;
            background: #fff;
        }

        .receipt-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }

        /* Cadre de chaque exemplaire - Hauteur réduite à 460px pour éviter tout débordement sur A4 */
        .receipt-block {
            border: none;
            padding: 12px;
            margin-bottom: 5px;
            position: relative;
            background: #fff;
            overflow: hidden;
            box-sizing: border-box;
            height: 460px; 
        }

        /* Style du filigrane d'arrière-plan - Taille réduite */
        .watermark-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.05;
            z-index: 0;
            pointer-events: none;
            width: 180px;
            height: 180px;
            text-align: center;
        }

        .watermark-img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .watermark-text-diagonal {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 60px;
            font-weight: 900;
            color: #ef4444;
            opacity: 0.07;
            z-index: 0;
            pointer-events: none;
            letter-spacing: 5px;
        }

        /* Contenu au premier plan */
        .receipt-content {
            position: relative;
            z-index: 1;
        }

        /* En-tête */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .header-table td {
            vertical-align: top;
            padding: 0;
        }

        .school-info-col {
            width: 55%;
        }

        .meta-info-col {
            width: 45%;
            text-align: right;
        }

        /* École Info */
        .school-logo-img {
            float: left;
            max-height: 40px;
            margin-right: 8px;
            object-fit: contain;
        }

        .school-name-text {
            font-size: 11px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            margin: 0;
        }

        .school-slogan-text {
            font-style: italic;
            font-size: 8px;
            color: #475569;
            margin: 1px 0;
        }

        .school-contact-text {
            font-size: 8px;
            color: #475569;
            margin: 1px 0;
        }

        /* République & Ministère (Simplifié sans délégations) */
        .republic-hierarchy {
            clear: both;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            margin-top: 3px;
            border-top: 1px solid #cbd5e1;
            padding-top: 2px;
        }

        /* Box meta de droite */
        .meta-box {
            display: inline-block;
            text-align: left;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 5px;
            background-color: #f8fafc;
            width: 95%;
            box-sizing: border-box;
        }

        .meta-box div {
            font-size: 8.5px;
            margin-bottom: 1px;
        }

        /* Titre Reçu */
        .receipt-title-container {
            border: none;
            border-radius: 6px;
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            text-align: center;
            padding: 6px 3px;
            margin-top: 4px;
            font-weight: bold;
            color: #ffffff;
            font-size: 11px;
            display: inline-block;
            width: 95%;
            box-sizing: border-box;
            box-shadow: 0 2px 4px rgba(30, 58, 138, 0.15);
        }

        /* Badge Duplicata */
        .duplicate-badge {
            display: inline-block;
            background-color: #ef4444;
            color: #ffffff;
            font-weight: bold;
            font-size: 8px;
            padding: 1px 5px;
            border-radius: 100px;
            margin-left: 4px;
            text-transform: uppercase;
        }

        /* Section détails élève */
        .details-section-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }

        .details-section-table td {
            padding: 4px 6px;
            font-size: 9.5px;
        }

        /* Table des finances */
        .financial-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .financial-table th, .financial-table td {
            border: 1px solid #475569;
            padding: 4px 5px;
            text-align: left;
            font-size: 9.5px;
        }

        .financial-table th {
            background-color: #e2e8f0;
            font-weight: bold;
            color: #0f172a;
        }

        /* Montant en toutes lettres */
        .amount-in-words-box {
            font-style: italic;
            font-weight: bold;
            background: #f8fafc;
            border-left: 4px solid #3b82f6;
            color: #1e293b;
            padding: 6px 10px;
            margin-bottom: 6px;
            font-size: 9.5px;
            border-radius: 0 6px 6px 0;
        }

        /* Section de bas avec QR Code et Totaux */
        .bottom-section-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .bottom-section-table td {
            vertical-align: top;
            padding: 0;
        }

        .qr-col {
            width: 35%;
        }

        .breakdown-col {
            width: 65%;
        }

        /* QR code block */
        .qr-wrapper {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .qr-img {
            border: 1px solid #cbd5e1;
            padding: 1px;
            background: #fff;
            max-width: 55px;
            max-height: 55px;
        }

        .qr-text-info {
            font-size: 6.5px;
            color: #64748b;
            line-height: 1.1;
        }

        /* Tableau de ventilation */
        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
            margin-left: auto;
        }

        .breakdown-table td {
            padding: 2px 4px;
            font-size: 8.5px;
            border: 1px solid #cbd5e1;
        }

        .breakdown-table td.label-td {
            background-color: #f8fafc;
            font-weight: bold;
            color: #475569;
            width: 60%;
        }

        .breakdown-table td.value-td {
            text-align: right;
            font-weight: bold;
            color: #0f172a;
            width: 40%;
        }

        /* Bloc Signatures */
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .signatures-table td {
            width: 50%;
            vertical-align: top;
            font-size: 8.5px;
        }

        .signature-box-left {
            text-align: left;
            padding-left: 5px;
        }

        .signature-box-right {
            text-align: right;
            padding-right: 5px;
        }

        .signature-line-placeholder {
            margin-top: 20px;
            border-top: 1px dotted #64748b;
            width: 110px;
            display: inline-block;
        }

        /* Pied de page */
        .receipt-footer-text {
            border-top: 1px solid #cbd5e1;
            margin-top: 8px;
            padding-top: 2px;
            font-size: 7px;
            color: #64748b;
            text-align: center;
            line-height: 1.1;
        }

        /* Style écran uniquement */
        .no-print {
            background-color: #f1f5f9;
            border-bottom: 1px solid #cbd5e1;
            padding: 10px 20px;
            margin-bottom: 20px;
            border-radius: 6px;
        }

        .btn-premium {
            background-color: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            padding: 6px 12px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            font-size: 11px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-premium-secondary {
            background-color: #64748b;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            padding: 6px 12px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            font-size: 11px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-premium:hover, .btn-premium-secondary:hover {
            opacity: 0.9;
        }

        .admin-logs-card {
            background-color: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 12px;
            margin-top: 20px;
        }

        /* Page settings and margins */
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        /* Page break avoid pour Dompdf & Force Color Adjust for print */
        @media print {
            body {
                padding: 0;
                margin: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
            .receipt-container {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .receipt-block {
                page-break-inside: avoid;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                padding: 10px !important;
                height: 460px !important;
            }
        }

        /* Amélioration de l'UX à l'écran (non-print) */
        @media screen {
            body {
                background-color: #f8fafc;
                padding: 20px 10px;
            }
            .receipt-container {
                background: transparent;
            }
            .receipt-block {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -4px rgba(0, 0, 0, 0.05);
                margin-bottom: 30px;
                padding: 20px;
                height: auto;
            }
            .print-btn-container {
                background-color: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
                padding: 15px 20px;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <?php if (!(isset($isPdf) && $isPdf)): ?>
     <!-- Barre d'actions écran -->
    <div class="print-btn-container no-print receipt-container" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h4 style="margin: 0; color: #1e3a8a;"><?= __('official_receipt_manager') ?></h4>
            <small style="color: #64748b;"><?= __('receipt_visualisation') ?></small>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="/payments/student?id=<?= $payment['student_id'] ?>" class="btn-premium-secondary">
                ← <?= __('student_file') ?>
            </a>
            <button class="btn-premium" onclick="window.print();">
                <?= __('print_receipt') ?>
            </button>
        </div>
    </div>
    <?php endif; ?>

    <div class="receipt-container">
        
        <?php
        // On génère deux blocs identiques : Exemplaire Parent et Exemplaire Administration
        $copies = [
            'Parent' => __('exemplaire_eleve'),
            'Admin' => __('exemplaire_etablissement')
        ];

        $i = 0;
        foreach ($copies as $key => $copyName):
            if ($i > 0): ?>
                <!-- Separator with scissor -->
                <table class="receipt-separator-table" style="width: 100%; margin: 8px 0; border-collapse: collapse; clear: both;">
                    <tr>
                        <td style="width: 25px; font-size: 16px; color: #475569; padding: 0; vertical-align: middle; line-height: 1; text-align: left;">✂</td>
                        <td style="border-bottom: 2.5px dashed #475569; padding: 0; vertical-align: middle; height: 1px; line-height: 1;">&nbsp;</td>
                    </tr>
                </table>
            <?php endif;
            $i++;
        ?>
        <!-- BLOC DE REÇU : <?= $copyName ?> -->
        <div class="receipt-block">
            
            <!-- Filigrane Logo de l'établissement -->
            <?php if ($logoBase64): ?>
                <div class="watermark-container">
                    <img src="<?= $logoBase64 ?>" class="watermark-img" alt="Watermark Logo">
                </div>
            <?php endif; ?>

            <!-- Filigranes statutaires -->
            <?php if (($payment['status'] ?? 'valide') === 'annule'): ?>
                <div class="watermark-text-diagonal" style="color: #ef4444; opacity: 0.15;">ANNULÉ</div>
            <?php elseif ($isDuplicate): ?>
                <div class="watermark-text-diagonal"><?= __('duplicata') ?></div>
            <?php endif; ?>

            <div class="receipt-content">
                <!-- En-tête de l'établissement et de l'administration -->
                <table class="header-table">
                    <tr>
                        <!-- Partie Gauche : Infos Ecole et Tutelle -->
                        <td class="school-info-col">
                            <?php if ($logoBase64): ?>
                                <img src="<?= $logoBase64 ?>" class="school-logo-img" alt="Logo Établissement">
                            <?php endif; ?>
                            <div class="school-name-text"><?= h($settings['school_name'] ?? 'Établissement Scolaire') ?></div>
                            <div class="school-slogan-text"><?= h($settings['school_slogan'] ?? 'Discipline - Travail - Succès') ?></div>
                            <div class="school-contact-text">
                                Tel: <?= h($settings['school_phone'] ?? 'N/A') ?>
                                <?php if (!empty($settings['school_email'])): ?> | Email: <?= h($settings['school_email']) ?><?php endif; ?>
                            </div>
                            <div class="school-contact-text">
                                <?php if (!empty($settings['school_po_box'])): ?>BP: <?= h($settings['school_po_box']) ?><?php endif; ?>
                                <?php if (!empty($settings['school_website'])): ?> | Web: <?= h($settings['school_website']) ?><?php endif; ?>
                            </div>
                            
                            <!-- Tutelle républicaine simplifiée sans délégation -->
                            <div class="republic-hierarchy">
                                <?= h(($app_lang === 'en') ? ($settings['school_republic_en'] ?? 'Republic of Cameroon') : ($settings['school_republic'] ?? 'République du Cameroun')) ?><br>
                                <?= h(($app_lang === 'en') ? ($settings['school_ministry_en'] ?? 'Ministry of Secondary Education') : ($settings['school_ministry'] ?? 'Ministère des Enseignements Secondaires')) ?>
                            </div>
                        </td>

                        <!-- Partie Droite : Titre Officiel et Métadonnées -->
                        <td class="meta-info-col">
                            <div class="receipt-title-container" style="margin-top: 0; margin-bottom: 4px;">
                                <?= __('official_payment_receipt') ?>
                                <div style="font-size: 7.5px; font-weight: normal; margin-top: 1px; color: #e0f2fe; text-transform: uppercase;">
                                    <?= $copyName ?>
                                </div>
                            </div>

                            <div class="meta-box" style="margin-top: 0; margin-bottom: 0;">
                                <div>
                                    <strong><?= __('school_year_label') ?></strong> <?= h($settings['display_school_year'] ?? $payment['academic_year_id'] ?? '') ?> &nbsp;|&nbsp; 
                                    <strong><?= __('date_emission_label') ?></strong> <?= date('d/m/Y H:i', strtotime($payment['created_at'])) ?>
                                </div>
                                <div style="margin-top: 2px;">
                                    <strong><?= __('jeton') ?></strong> <span style="font-family: monospace; font-weight: bold;"><?= h($payment['verification_code']) ?></span> &nbsp;|&nbsp; 
                                    <strong><?= __('col_print_number') ?> :</strong> <span><?= (int)$payment['print_count'] ?></span>
                                    <?php if ($isDuplicate): ?>
                                        <span class="duplicate-badge"><?= __('duplicata') ?></span>
                                    <?php else: ?>
                                        <span class="duplicate-badge" style="background-color: #16a34a;"><?= __('original') ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- Fiche d'identité de l'élève (Format compact en ligne sans bordure gauche colorée) -->
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0; border-radius: 6px; background-color: #f8fafc; margin: 6px 0;">
                    <tr>
                        <td style="padding: 4px 6px; border-bottom: 1px solid #e2e8f0; font-size: 8.5px; line-height: 1.2; vertical-align: middle;">
                            <strong style="color: #64748b; font-size: 8px;"><?= __('student') ?> :</strong>
                            <strong style="color: #1e3a8a; text-transform: uppercase;"><?= h($payment['student_nom'] ?? '') ?></strong>
                            <span style="color: #0f172a; font-weight: bold;"><?= h($payment['student_prenom'] ?? '') ?></span>
                        </td>
                        <td style="padding: 4px 6px; border-bottom: 1px solid #e2e8f0; border-left: 1px solid #e2e8f0; font-size: 8.5px; line-height: 1.2; vertical-align: middle; width: 40%;">
                            <strong style="color: #64748b; font-size: 8px;"><?= __('matricule') ?> :</strong>
                            <span style="font-family: monospace; font-weight: bold; color: #0369a1; background-color: #e0f2fe; padding: 0.5px 3px; border-radius: 3px;"><?= h($payment['matricule'] ?? '') ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 6px; border-bottom: 1px solid #e2e8f0; font-size: 8.5px; line-height: 1.2; vertical-align: middle;">
                            <strong style="color: #64748b; font-size: 8px;"><?= __('class') ?> :</strong>
                            <span style="color: #0f172a; font-weight: bold;"><?= h($payment['classe_nom'] ?: __('not_specified')) ?></span>
                        </td>
                        <td style="padding: 4px 6px; border-bottom: 1px solid #e2e8f0; border-left: 1px solid #e2e8f0; font-size: 8.5px; line-height: 1.2; vertical-align: middle;">
                            <strong style="color: #64748b; font-size: 8px;"><?= __('sex') ?> :</strong>
                            <span style="color: #334155; font-weight: bold;"><?= ($payment['sexe'] ?? '') === 'M' ? __('male') : (($payment['sexe'] ?? '') === 'F' ? __('female') : 'N/A') ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 6px; border-right: 1px solid #e2e8f0; font-size: 8.5px; line-height: 1.2; vertical-align: middle;">
                            <strong style="color: #64748b; font-size: 8px;"><?= __('born_on') ?> :</strong>
                            <span style="color: #334155; font-weight: bold;">
                                <?= !empty($payment['date_naissance']) ? date('d/m/Y', strtotime($payment['date_naissance'])) : 'N/A' ?>
                                <?= !empty($payment['lieu_naissance']) ? ' ' . __('born_at') . ' ' . h($payment['lieu_naissance']) : '' ?>
                            </span>
                        </td>
                        <td style="padding: 4px 6px; font-size: 8.5px; line-height: 1.2; vertical-align: middle;">
                            <strong style="color: #64748b; font-size: 8px;"><?= __('year') ?> :</strong>
                            <span style="color: #334155; font-weight: bold;"><?= h($settings['display_school_year'] ?? $payment['academic_year_id'] ?? '') ?></span>
                        </td>
                    </tr>
                </table>

                <!-- Tableau Financier du versement -->
                <table class="financial-table">
                    <thead>
                        <tr>
                            <th style="width: 40%;"><?= __('nature_payment') ?></th>
                            <th style="width: 25%;"><?= __('payment_method_label') ?></th>
                            <th style="width: 20%;"><?= __('col_reference') ?></th>
                            <th style="width: 15%; text-align: right;"><?= __('col_amount') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-weight: bold; color: #1e3a8a;"><?= $nature ?></td>
                            <td><?= h($payment['payment_method']) ?></td>
                            <td style="font-family: monospace; font-size: 8.5px;"><?= $payment['reference'] ? h($payment['reference']) : 'N/A' ?></td>
                            <td style="text-align: right; font-weight: bold; font-size: 10px;"><?= number_format($payment['amount'], 0, '.', ' ') ?> FCFA</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Somme en toutes lettres -->
                <div class="amount-in-words-box">
                    <?= __('amount_words_prefix') ?> <strong><?= h($amountInWords) ?></strong>
                </div>

                <!-- Section de bas : Ventilation financière -->
                <table class="bottom-section-table" style="width: 100%;">
                    <tr>
                        <td class="breakdown-col" style="width: 100%;">
                            <table class="breakdown-table" style="width: 100%;">
                                <?php if ($payment['type'] === 'inscription'): 
                                    // Calculer les frais prévus selon le statut
                                    $policy = $settings['registration_fee_policy'] ?? 'all';
                                    $expectedFee = 0.00;
                                    if ($policy === 'new_only') {
                                        $expectedFee = (($enroll['student_status'] ?? 'nouveau') === 'nouveau') ? (float)$payment['frais_inscription'] : 0.00;
                                    } elseif ($policy === 'by_status') {
                                        $expectedFee = (($enroll['student_status'] ?? 'nouveau') === 'nouveau') ? (float)$payment['frais_inscription'] : (float)$payment['frais_inscription_reinscription'];
                                    } else {
                                        $expectedFee = (float)$payment['frais_inscription'];
                                    }
                                ?>
                                    <tr>
                                        <td class="label-td" style="width: 60%;"><?= __('registration_payment_option') ?> <?= __('expected_fee_suffix') ?></td>
                                        <td class="value-td" style="width: 40%;"><?= number_format($expectedFee, 0, '.', ' ') ?> FCFA</td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" style="color: #16a34a; background-color: #f0fdf4; width: 60%;"><?= __('registration_payment_option') ?> <?= __('paid_fee_suffix') ?></td>
                                        <td class="value-td" style="color: #16a34a; background-color: #f0fdf4; width: 40%;"><?= number_format($payment['amount'], 0, '.', ' ') ?> FCFA</td>
                                    </tr>
                                    <?php if (!empty($childSurplus)): ?>
                                    <tr>
                                        <td class="label-td" style="color: #0284c7; background-color: #f0f9ff; width: 60%;">Surplus transféré vers scolarité</td>
                                        <td class="value-td" style="color: #0284c7; background-color: #f0f9ff; width: 40%;"><?= number_format($childSurplus['amount'], 0, '.', ' ') ?> FCFA</td>
                                    </tr>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <tr>
                                        <td class="label-td" style="width: 60%;"><?= __('gross_tuition_fee') ?></td>
                                        <td class="value-td" style="width: 40%;"><?= number_format($enroll['scolarite_nette'] ?? 0, 0, '.', ' ') ?> FCFA</td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" style="width: 60%;"><?= __('total_cumulated_paid') ?></td>
                                        <td class="value-td" style="width: 40%;"><?= number_format($enroll['total_paye'] ?? 0, 0, '.', ' ') ?> FCFA</td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" style="color: #b91c1c; background-color: #fef2f2; width: 60%;"><?= __('remaining_tuition_balance') ?></td>
                                        <td class="value-td" style="color: #b91c1c; background-color: #fef2f2; text-decoration: underline; width: 40%;"><?= number_format($enroll['reste_a_payer'] ?? 0, 0, '.', ' ') ?> FCFA</td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Bloc des signatures & Authentification -->
                <table class="signatures-table" style="margin-top: 10px; width: 100%;">
                    <tr>
                        <!-- QR Code de lutte contre les faux reçus (remplace Signature Parent) -->
                        <td style="width: 48%; padding-right: 2%; vertical-align: top;">
                            <div style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 10px; min-height: 80px; background-color: #f8fafc; box-sizing: border-box; display: flex; align-items: center; gap: 8px;">
                                <img src="<?= $qrCodeSrc ?>" style="border: 1px solid #cbd5e1; padding: 1px; background: #fff; max-width: 50px; max-height: 50px;" alt="QR Code">
                                <div style="font-size: 7.5px; color: #64748b; line-height: 1.2;">
                                    <strong style="color: #1e3a8a; font-size: 8px; text-transform: uppercase; display: block; margin-bottom: 2px;"><?= __('verification_enrollment') ?></strong>
                                    <?= __('jeton') ?> : <span style="font-family: monospace; font-weight: bold; color: #0f172a;"><?= h($payment['verification_code']) ?></span><br>
                                    <?= __('scan_qr_help') ?>
                                </div>
                            </div>
                        </td>
                        <!-- Signature de la caisse -->
                        <td style="width: 48%; padding-left: 2%; vertical-align: top;">
                            <div style="border: 1px dashed #cbd5e1; border-radius: 6px; padding: 6px 10px; min-height: 80px; background-color: #fafafa; position: relative; box-sizing: border-box;">
                                <strong style="color: #475569; font-size: 8.5px; text-transform: uppercase;"><?= __('cashier_signature_label') ?></strong>
                                <div style="font-size: 8.5px; color: #1e293b; margin-top: 4px; line-height: 1.3;">
                                    <?= __('entered_by') ?> : <strong><?= h($payment['user_nom'] ?? '') ?> <?= h($payment['user_prenom'] ?? '') ?></strong><br>
                                    <?= __('printed_on') ?> <?= date('d/m/Y H:i') ?>
                                </div>
                                <div style="margin-top: 25px; border-top: 1px dotted #94a3b8; width: 100%;"></div>
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- Pied de page -->
                <div class="receipt-footer-text">
                    <?= __('official_receipt_doc') ?> <strong><?= h($settings['school_name'] ?? 'NotesMaster') ?></strong> - 
                    Tél : <?= h($settings['school_phone'] ?? 'N/A') ?>
                    <?php if (!empty($settings['school_email'])): ?> | Email : <?= h($settings['school_email']) ?><?php endif; ?>
                    <?php if (!empty($settings['school_website'])): ?> | Site : <?= h($settings['school_website']) ?><?php endif; ?>
                </div>

            </div>
        </div>
        <?php endforeach; ?>

    </div>

    <!-- Historique des impressions (Visible uniquement à l'écran par l'administrateur, pas en PDF ni à l'impression) -->
    <?php if (!$isPdf && !empty($printLogs)): ?>
    <div class="receipt-container no-print admin-logs-card">
        <h5 style="margin-top: 0; color: #1e293b; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px;">
            <i class="bi bi-clock-history"></i> <?= __('print_history_title') ?>
        </h5>
        <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
            <thead>
                <tr style="background-color: #f8fafc; border-bottom: 1px solid #cbd5e1; text-align: left;">
                    <th style="padding: 6px;"><?= __('col_datetime') ?></th>
                    <th style="padding: 6px;"><?= __('col_operator') ?></th>
                    <th style="padding: 6px;"><?= __('col_action') ?></th>
                    <th style="padding: 6px;"><?= __('col_print_number') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($printLogs as $log): ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 6px; font-weight: bold;"><?= date('d/m/Y H:i:s', strtotime($log['event_date'])) ?></td>
                    <td style="padding: 6px;"><?= h($log['user_nom']) ?> <?= h($log['user_prenom']) ?></td>
                    <td style="padding: 6px; text-transform: uppercase; color: #2563eb; font-weight: bold;"><?= __('print_action_label') ?></td>
                    <td style="padding: 6px; font-weight: bold;">
                        #<?= (int)$log['new_value'] ?>
                        <?php if ((int)$log['new_value'] === 1): ?>
                            <span style="color: #16a34a; font-size: 8px; margin-left: 4px;">[ <?= __('original') ?> ]</span>
                        <?php else: ?>
                            <span style="color: #ef4444; font-size: 8px; margin-left: 4px;">[ <?= __('duplicata') ?> ]</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</body>
</html>
