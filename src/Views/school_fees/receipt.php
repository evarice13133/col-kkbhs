<?php
// Vue du Reçu Officiel de Versement Scolaire (Frais de Scolarité)
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

// URL de vérification publique pour le QR Code avec métadonnées complètes
$verifyUrl = APP_URL . '/payments/verify?code=' . urlencode($payment['verification_code'] ?? '')
    . '&id=' . urlencode($payment['id'] ?? '')
    . '&matricule=' . urlencode($payment['matricule'] ?? '')
    . '&annee=' . urlencode($settings['display_school_year'] ?? $payment['academic_year_id'] ?? '');

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

// Déterminer le statut de duplicata
$isDuplicate = (int) ($payment['print_count'] ?? 1) > 1;
$app_lang = \App\Core\Session::get('app_lang', 'fr');
?>
<!DOCTYPE html>
<html lang="<?= $app_lang ?>">

<head>
    <meta charset="UTF-8">
    <title><?= __('receipt_officiel_title') ?><?= h($payment['receipt_number'] ?? $payment['id']) ?></title>
    <style>
        /* Optimisation stricte A4 Portrait pour faire tenir les 2 reçus sur une seule page */
        body {
            font-family: Arial, sans-serif;
            font-size: 9.5px;
            line-height: 1.1;
            color: #1e293b;
            margin: 0;
            padding: 2px;
            background: #fff;
        }

        .receipt-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }

        /* Cadre de chaque exemplaire - Hauteur ajustée pour tenir sur A4 */
        .receipt-block {
            border: none;
            padding: 2px 4px;
            margin-bottom: 1px;
            position: relative;
            background: #fff;
            overflow: hidden;
            box-sizing: border-box;
        }

        /* Style du filigrane d'arrière-plan - Taille réduite */
        .watermark-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.08;
            z-index: 0;
            pointer-events: none;
            width: 120px;
            height: 120px;
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
            font-size: 40px;
            font-weight: 900;
            color: #ef4444;
            opacity: 0.04;
            z-index: 0;
            pointer-events: none;
            letter-spacing: 4px;
        }

        /* Contenu au premier plan */
        .receipt-content {
            position: relative;
            z-index: 1;
        }

        /* En-tête en 3 colonnes pour économiser de la hauteur */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }

        .header-table td {
            vertical-align: top;
            padding: 0;
        }

        .school-info-col {
            width: 32%;
        }

        .tutelle-col {
            width: 36%;
            text-align: center;
            font-size: 8px;
            line-height: 1.1;
            color: #64748b;
            font-weight: bold;
            text-transform: uppercase;
        }

        .receipt-meta-col {
            width: 32%;
            text-align: right;
        }

        /* École Info */
        .school-logo-img {
            float: left;
            max-height: 22px;
            margin-right: 4px;
            object-fit: contain;
        }

        .school-name-text {
            font-size: 10px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            margin: 0;
            line-height: 1.1;
        }

        .school-slogan-text {
            font-style: italic;
            font-size: 8px;
            color: #475569;
            margin: 0;
            line-height: 1;
        }

        .school-contact-text {
            font-size: 8px;
            color: #475569;
            margin: 0;
            line-height: 1;
        }

        /* Box meta de droite */
        .meta-box {
            display: block;
            text-align: left;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 2px;
            background-color: #f8fafc;
            width: 100%;
            box-sizing: border-box;
        }

        .meta-box div {
            font-size: 8.5px;
            margin-bottom: 0px;
            line-height: 1.1;
        }

        /* Titre Reçu */
        .receipt-title-container {
            border: none;
            border-radius: 6px;
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            text-align: center;
            padding: 4px 2px;
            margin-top: 0px;
            margin-bottom: 2px;
            font-weight: bold;
            color: #ffffff;
            font-size: 10.5px;
            display: block;
            width: 100%;
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
            padding: 0.5px 2px;
            border-radius: 100px;
            margin-left: 2px;
            text-transform: uppercase;
        }

        /* Section détails élève - Design modern card */
        .details-section-card {
            background-color: #f8fafc;
            border-left: 3px solid #1e3a8a;
            border-top: 1px solid #cbd5e1;
            border-right: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 3px 6px;
            margin-top: 1px;
            box-sizing: border-box;
        }

        .details-section-card table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .details-section-card td {
            border: none !important;
            padding: 1.5px 0 !important;
            font-size: 9.2px;
            vertical-align: middle;
        }

        .details-section-card td.label-td {
            color: #475569;
            font-weight: bold;
            font-size: 8.5px;
            text-transform: uppercase;
            width: 18%;
        }

        .details-section-card td.value-td {
            color: #0f172a;
        }

        /* Table des finances */
        .financial-table {
            width: 100%;
            border-collapse: collapse;
        }

        .financial-table th,
        .financial-table td {
            border: 1px solid #475569;
            padding: 1px 2px;
            text-align: left;
            font-size: 9.2px;
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
            padding: 4px 8px;
            font-size: 9.2px;
            border-radius: 0 6px 6px 0;
            margin-bottom: 2px;
        }

        /* Section de bas avec QR Code et Totaux */
        .bottom-section-table {
            width: 100%;
            border-collapse: collapse;
        }

        .bottom-section-table td {
            vertical-align: top;
            padding: 0;
        }

        .qr-col {
            width: 45%;
        }

        .breakdown-col {
            width: 50%;
        }

        /* QR code block */
        .qr-wrapper {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .qr-img {
            border: 1px solid #cbd5e1;
            padding: 1px;
            background: #fff;
            max-width: 32px;
            max-height: 32px;
        }

        .qr-text-info {
            font-size: 8px;
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
            padding: 0.8px 2px;
            font-size: 9px;
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
            margin-top: 1px;
        }

        .signatures-table td {
            width: 50%;
            vertical-align: top;
            font-size: 9px;
        }

        .signature-box-left {
            text-align: left;
            padding-left: 2px;
        }

        .signature-box-right {
            text-align: right;
            padding-right: 2px;
        }

        .signature-line-placeholder {
            margin-top: 5px;
            border-top: 1px dotted #64748b;
            width: 70px;
            display: inline-block;
        }

        /* Pied de page */
        .receipt-footer-text {
            border-top: 1px solid #cbd5e1;
            margin-top: 2px;
            padding-top: 1px;
            font-size: 8px;
            color: #64748b;
            text-align: center;
            line-height: 1.1;
        }

        /* Style écran uniquement */
        .no-print {
            background-color: #f1f5f9;
            border-bottom: 1px solid #cbd5e1;
            padding: 8px 16px;
            margin-bottom: 10px;
            border-radius: 6px;
        }

        .btn-premium {
            background-color: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            font-size: 10px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-premium-secondary {
            background-color: #64748b;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            font-size: 10px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-premium:hover,
        .btn-premium-secondary:hover {
            opacity: 0.9;
        }

        .admin-logs-card {
            background-color: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px;
            margin-top: 15px;
        }

        /* Page settings and margins */
        @page {
            size: A4 portrait;
            margin: 5mm;
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
                padding: 2px 4px !important;
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
                padding: 15px;
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

    <!-- Barre d'actions écran -->
    <div class="print-btn-container no-print receipt-container"
        style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h4 style="margin: 0; color: #1e3a8a; font-family: Arial, sans-serif;"><?= __('receipt_title') ?></h4>
            <small style="color: #64748b;"><?= __('receipt_visualisation') ?></small>
        </div>
        <div style="display: flex; gap: 8px;">
            <?php if (isset($_GET['back']) && $_GET['back'] === 'student' && isset($_GET['student_id'])): ?>
                <a href="/payments/student?id=<?= (int)$_GET['student_id'] ?>" class="btn-premium-secondary">
                    <?= __('back_to_ledger') ?>
                </a>
            <?php else: ?>
                <a href="/school_fees/versements" class="btn-premium-secondary">
                    <?= __('back_to_payments') ?>
                </a>
            <?php endif; ?>
            <button class="btn-premium" onclick="window.print();">
                <?= __('print_receipt') ?>
            </button>
        </div>
    </div>

    <div class="receipt-container">

        <?php
        // On génère deux blocs identiques : Exemplaire Elève et Exemplaire Etablissement
        $copies = [
            'Parent' => __('exemplaire_eleve'),
            'Admin' => __('exemplaire_etablissement')
        ];

        $i = 0;
        foreach ($copies as $key => $copyName):
            if ($i > 0): ?>
                <!-- Separator with scissor -->
                <table class="receipt-separator-table"
                    style="width: 100%; margin: 12px 0; border-collapse: collapse; clear: both;">
                    <tr>
                        <td
                            style="width: 16px; font-size: 9px; color: #475569; padding: 0; vertical-align: middle; line-height: 1; text-align: left;">
                            ✂</td>
                        <td
                            style="border-bottom: 1.5px dashed #475569; padding: 0; vertical-align: middle; height: 1px; line-height: 1;">
                            &nbsp;</td>
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
                            <!-- Partie Gauche : Infos Ecole -->
                            <td class="school-info-col" style="width: 65%; vertical-align: top;">
                                <div
                                    style="margin-bottom: 2px; font-size: 8px; font-weight: bold; text-transform: uppercase; color: #64748b; letter-spacing: 0.2px;">
                                    <?= h($settings['school_republic'] ?? 'République du Cameroun') ?> &nbsp;|&nbsp; PAIX -
                                    TRAVAIL - PATRIE
                                </div>
                                <table style="width: 100%; border-collapse: collapse; border: none;">
                                    <tr>
                                        <?php if ($logoBase64): ?>
                                            <td style="width: 36px; vertical-align: top; padding: 0;">
                                                <img src="<?= $logoBase64 ?>"
                                                    style="max-height: 32px; max-width: 34px; object-fit: contain; float: left; margin-right: 4px;"
                                                    alt="Logo Établissement">
                                            </td>
                                        <?php endif; ?>
                                        <td
                                            style="vertical-align: top; padding-left: 2px; padding-top: 0; padding-bottom: 0;">
                                            <div class="school-name-text"
                                                style="font-size: 11px; font-weight: bold; color: #1e3a8a; text-transform: uppercase; margin: 0; line-height: 1.15;">
                                                <?= h($settings['school_name'] ?? 'Collège polyvalent bilingue marie Thérèse') ?>
                                            </div>
                                            <div class="school-slogan-text"
                                                style="font-style: italic; font-size: 8.5px; color: #475569; margin: 1px 0;">
                                                <?= h($settings['school_slogan'] ?? 'Discipline - Travail - Succès') ?>
                                            </div>
                                            <div class="school-contact-text"
                                                style="font-size: 8.5px; color: #475569; line-height: 1.25;">
                                                Tel: <?= h($settings['school_phone'] ?? '686061923/696007229') ?>
                                                &nbsp;|&nbsp;
                                                Email:
                                                <?= h($settings['school_email'] ?? 'fotsomarietherese2024@gmail.com') ?><br>
                                                BP: <?= h($settings['school_po_box'] ?? '51442') ?> &nbsp;|&nbsp;
                                                Web:
                                                <?= h($settings['school_website'] ?? 'https://copobimat.camertech.com') ?>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>

                            <!-- Partie Droite : Titre Officiel et Métadonnées -->
                            <td class="receipt-meta-col" style="width: 35%; padding-left: 10px; vertical-align: top;">
                                <div class="receipt-title-container">
                                    <?= __('receipt_title') ?>
                                    <div
                                        style="font-size: 8px; font-weight: normal; margin-top: 0.5px; color: #e0f2fe; text-transform: uppercase;">
                                        <?= $copyName ?>
                                    </div>
                                </div>

                                <div class="meta-box">
                                    <div>
                                        <strong><?= __('school_year_label') ?></strong>
                                        <?= h($settings['display_school_year'] ?? $payment['academic_year_id'] ?? '') ?>
                                    </div>
                                    <div style="margin-top: 1px;">
                                        <strong><?= __('date_label') ?></strong>
                                        <?= date('d/m/Y H:i', strtotime($payment['created_at'])) ?>
                                    </div>
                                    <div style="margin-top: 1px;">
                                        <strong><?= __('receipt_no_label') ?></strong> <span
                                            style="font-family: monospace; font-weight: bold;"><?= h($payment['receipt_number']) ?></span>
                                        <?php if ($isDuplicate): ?>
                                            <span class="duplicate-badge"><?= __('duplicata') ?></span>
                                        <?php else: ?>
                                            <span class="duplicate-badge"
                                                style="background-color: #16a34a;"><?= __('original') ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <!-- SECTION COTE-A-COTE 1: INFOS ELEVE + DETAIL OPERATION (Wrapper Table) -->
                    <table style="width: 100%; border-collapse: collapse; border: none; margin-bottom: 2px;">
                        <tr>
                            <!-- Gauche: Infos Élève -->
                            <td style="width: 49%; vertical-align: top; padding-right: 4px;">
                                <div
                                    style="font-weight: bold; font-size: 9.2px; color: #1e3a8a; margin-bottom: 1px; text-transform: uppercase;">
                                    <i class="bi bi-person-fill"></i> <?= __('student_info_section') ?>
                                </div>
                                <table style="width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0; border-radius: 6px; background-color: #f8fafc; margin-top: 1px;">
                                    <tr>
                                        <td style="padding: 4px 6px; border-bottom: 1px solid #e2e8f0; font-size: 8.5px; line-height: 1.2; vertical-align: middle;">
                                            <strong style="color: #64748b; font-size: 8px;"><?= __('student') ?> :</strong>
                                            <strong style="color: #1e3a8a; text-transform: uppercase;"><?= h($payment['student_nom'] ?? '') ?></strong>
                                            <span style="color: #0f172a; font-weight: bold;"><?= h($payment['student_prenom'] ?? '') ?></span>
                                        </td>
                                        <td style="padding: 4px 6px; border-bottom: 1px solid #e2e8f0; border-left: 1px solid #e2e8f0; font-size: 8.5px; line-height: 1.2; vertical-align: middle; width: 40%;">
                                            <strong style="color: #64748b; font-size: 8px;"><?= __('matricule') ?> :</strong>
                                            <span style="font-family: monospace; font-weight: bold; color: #0369a1; background-color: #e0f2fe; padding: 0.5px 3px; border-radius: 3px;"><?= h($payment['matricule'] ?? 'MAT-' . sprintf('%04d', $payment['student_id'])) ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 6px; font-size: 8.5px; line-height: 1.2; vertical-align: middle;">
                                            <strong style="color: #64748b; font-size: 8px;"><?= __('class') ?> :</strong>
                                            <strong style="color: #0f172a;"><?= h($payment['class_name'] ?: '-') ?></strong>
                                        </td>
                                        <td style="padding: 4px 6px; border-left: 1px solid #e2e8f0; font-size: 8.5px; line-height: 1.2; vertical-align: middle;">
                                            <strong style="color: #64748b; font-size: 8px;"><?= __('born_on') ?> :</strong>
                                            <span style="color: #334155; font-weight: bold;">
                                                <?= !empty($payment['date_naissance']) ? date('d/m/Y', strtotime($payment['date_naissance'])) : 'N/A' ?>
                                                <?= !empty($payment['lieu_naissance']) ? ' ' . __('born_at') . ' ' . h($payment['lieu_naissance']) : '' ?>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </td>

                            <!-- Droite: Détail de cette opération -->
                            <td style="width: 49%; vertical-align: top; padding-left: 4px;">
                                <div
                                    style="font-weight: bold; font-size: 9.2px; color: #1e3a8a; margin-bottom: 1px; text-transform: uppercase;">
                                    <i class="bi bi-wallet2"></i> <?= __('operation_detail_section') ?>
                                </div>
                                <table class="financial-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 35%;"><?= __('col_installment_concerned') ?></th>
                                            <th style="width: 20%; text-align: right;"><?= __('col_installment_amount') ?>
                                            </th>
                                            <th style="width: 25%; text-align: right;"><?= __('col_amount_allocated') ?>
                                            </th>
                                            <th style="width: 20%; text-align: right;"><?= __('col_amount_remaining') ?>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $totalOperationAmount = 0.0;
                                        $totalOperationRemaining = 0.0;
                                        foreach ($allocations as $alloc):
                                            $orderNum = (int) $alloc['installment_number'];
                                            $instName = isset($installmentsMap[$orderNum]) ? $installmentsMap[$orderNum]['name'] : 'Tranche N°' . $orderNum;
                                            $amountPlanned = (float) $alloc['amount_planned'];
                                            $amountAllocated = (float) $alloc['amount_allocated'];
                                            $reste = max(0.0, $amountPlanned - (float) $alloc['total_installment_paid']);

                                            $totalOperationAmount += $amountAllocated;
                                            $totalOperationRemaining += $reste;
                                            ?>
                                            <tr>
                                                <td style="font-weight: bold; color: #1e3a8a;"><?= h($instName) ?></td>
                                                <td style="text-align: right;"><?= number_format($amountPlanned, 0, '.', ' ') ?>
                                                </td>
                                                <td style="text-align: right; font-weight: bold; color: #16a34a;">
                                                    <?= number_format($amountAllocated, 0, '.', ' ') ?></td>
                                                <td
                                                    style="text-align: right; font-weight: bold; color: <?= $reste > 0 ? '#b91c1c' : '#1e293b' ?>;">
                                                    <?= number_format($reste, 0, '.', ' ') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr style="background-color: #f8fafc; font-weight: bold;">
                                            <td><?= __('cumul') ?></td>
                                            <td></td>
                                            <td style="text-align: right; font-weight: black; color: #16a34a;">
                                                <?= number_format($totalOperationAmount, 0, '.', ' ') ?></td>
                                            <td
                                                style="text-align: right; font-weight: black; color: <?= $totalOperationRemaining > 0 ? '#b91c1c' : '#1e293b' ?>;">
                                                <?= number_format($totalOperationRemaining, 0, '.', ' ') ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <!-- Somme en toutes lettres (Intercalé) -->
                    <div class="amount-in-words-box" style="margin-bottom: 2px;">
                        <?= __('amount_words_prefix') ?> <strong><?= h($amountInWords) ?></strong>
                    </div>

                    <!-- SECTION COTE-A-COTE 2: SITUATION GLOBALE & HISTORIQUE + VENTILATION & QR CODE (Wrapper Table) -->
                    <table style="width: 100%; border-collapse: collapse; border: none; margin-bottom: 2px;">
                        <tr>
                            <!-- Gauche: État complet des tranches de scolarité ET Historique complet des versements -->
                            <td style="width: 59%; vertical-align: top; padding-right: 4px;">
                                <!-- ÉTAT COMPLET DE LA SCOLARITÉ -->
                                <div
                                    style="font-weight: bold; font-size: 9.2px; color: #1e3a8a; margin-bottom: 1px; text-transform: uppercase;">
                                    <i class="bi bi-calendar-check"></i> <?= __('tuition_status_section') ?>
                                </div>
                                <table class="financial-table" style="margin-bottom: 6px;">
                                    <thead>
                                        <tr>
                                            <th style="width: 25%;"><?= __('col_installment') ?></th>
                                            <th style="width: 18%;"><?= __('col_deadline') ?></th>
                                            <th style="width: 16%; text-align: right;"><?= __('col_planned') ?></th>
                                            <th style="width: 16%; text-align: right;"><?= __('col_paid') ?></th>
                                            <th style="width: 13%; text-align: right;"><?= __('col_remaining') ?></th>
                                            <th style="width: 12%; text-align: center;"><?= __('col_status') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $currentDate = date('Y-m-d');
                                        foreach ($studentInstallments as $si):
                                            $orderNum = (int) $si['installment_number'];
                                            $instName = isset($installmentsMap[$orderNum]) ? $installmentsMap[$orderNum]['name'] : 'Tranche N°' . $orderNum;
                                            $deadlineRaw = isset($installmentsMap[$orderNum]) ? $installmentsMap[$orderNum]['deadline'] : '';
                                            $deadline = $deadlineRaw ? date('d/m/Y', strtotime($deadlineRaw)) : 'N/A';

                                            $amountPlanned = (float) $si['amount_planned'];
                                            $amountPaid = (float) $si['amount_paid'];
                                            $reste = max(0.0, $amountPlanned - $amountPaid);

                                            // Determine status
                                            $status = __('status_unpaid');
                                            $statusColor = '#64748b';
                                            $statusBg = '#f1f5f9';

                                            if ($amountPaid >= $amountPlanned) {
                                                $status = __('status_paid');
                                                $statusColor = '#16a34a';
                                                $statusBg = '#d1fae5';
                                            } elseif ($deadlineRaw && $currentDate > $deadlineRaw) {
                                                $status = __('status_overdue');
                                                $statusColor = '#ef4444';
                                                $statusBg = '#fee2e2';
                                            } elseif ($amountPaid > 0) {
                                                $status = __('status_partial');
                                                $statusColor = '#d97706';
                                                $statusBg = '#fef3c7';
                                            }
                                            ?>
                                            <tr>
                                                <td style="font-weight: bold; color: #1e3a8a;"><?= h($instName) ?></td>
                                                <td><?= h($deadline) ?></td>
                                                <td style="text-align: right;"><?= number_format($amountPlanned, 0, '.', ' ') ?>
                                                </td>
                                                <td style="text-align: right; font-weight: bold; color: #16a34a;">
                                                    <?= number_format($amountPaid, 0, '.', ' ') ?></td>
                                                <td
                                                    style="text-align: right; font-weight: bold; color: <?= $reste > 0 ? '#b91c1c' : '#1e293b' ?>;">
                                                    <?= number_format($reste, 0, '.', ' ') ?></td>
                                                <td style="text-align: center; padding: 1px;">
                                                    <span
                                                        style="display: inline-block; padding: 0.5px 2px; border-radius: 3px; font-size: 6.8px; font-weight: bold; color: <?= $statusColor ?>; background-color: <?= $statusBg ?>; text-transform: uppercase;">
                                                        <?= $status ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <!-- HISTORIQUE COMPLET DES VERSEMENTS -->
                                <div
                                    style="font-weight: bold; font-size: 9.2px; color: #1e3a8a; margin-bottom: 1px; text-transform: uppercase;">
                                    <i class="bi bi-clock-history"></i> <?= __('payments_history_section') ?>
                                </div>
                                <table class="financial-table">
                                    <thead>
                                        <tr>
                                            <th style="font-size: 9.2px; padding: 1.5px 2px;"><?= __('col_reference') ?>
                                            </th>
                                            <th style="font-size: 9.2px; padding: 1.5px 2px;"><?= __('col_method') ?></th>
                                            <th style="font-size: 9.2px; padding: 1.5px 2px;"><?= __('col_date') ?></th>
                                            <th style="font-size: 9.2px; padding: 1.5px 2px; text-align: right;">
                                                <?= __('col_amount') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $histTotal = 0.0;
                                        foreach ($paymentsHistory as $hist):
                                            $histTotal += (float) $hist['amount'];
                                            ?>
                                            <tr>
                                                <td style="font-family: monospace; font-size: 8.8px; padding: 1.5px 2px;">
                                                    <?= h($hist['reference'] ?: 'N/A') ?></td>
                                                <td style="font-size: 8.8px; padding: 1.5px 2px;">
                                                    <?= h($hist['payment_method']) ?></td>
                                                <td style="font-size: 8.8px; padding: 1.5px 2px;">
                                                    <?= date('d/m/Y', strtotime($hist['payment_date'])) ?></td>
                                                <td
                                                    style="font-size: 8.8px; padding: 1.5px 2px; text-align: right; font-weight: bold;">
                                                    <?= number_format($hist['amount'], 0, '.', ' ') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr style="background-color: #f8fafc; font-weight: bold;">
                                            <td colspan="3"
                                                style="font-size: 9.2px; padding: 1.5px 2px; text-align: right;">
                                                <?= __('cumul') ?></td>
                                            <td
                                                style="font-size: 9.2px; padding: 1.5px 2px; text-align: right; color: #16a34a;">
                                                <?= number_format($histTotal, 0, '.', ' ') ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>

                            <!-- Droite: État général des frais (Tableau de ventilation) + QR Code & Mention Légale -->
                            <td style="width: 39%; vertical-align: top; padding-left: 4px;">
                                <!-- SITUATION FINANCIÈRE GLOBALE -->
                                <div
                                    style="font-weight: bold; font-size: 9.2px; color: #1e3a8a; margin-bottom: 1px; text-transform: uppercase;">
                                    <i class="bi bi-calculator"></i> <?= __('global_financial_status') ?>
                                </div>
                                <table class="breakdown-table"
                                    style="border: 1px solid #cbd5e1; width: 100%; margin-left: 0; margin-bottom: 3px;">
                                    <tr>
                                        <td class="label-td"><?= __('gross_tuition_fee') ?></td>
                                        <td class="value-td">
                                            <?= number_format($enroll['frais_scolarite_brut'] ?? 0, 0, '.', ' ') ?> FCFA
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td"><?= __('discounts_applied') ?></td>
                                        <td class="value-td" style="color: #ef4444;">
                                            -<?= number_format($enroll['total_reductions'] ?? 0, 0, '.', ' ') ?> FCFA</td>
                                    </tr>
                                    <tr>
                                        <td class="label-td"><?= __('scholarships_applied') ?></td>
                                        <td class="value-td" style="color: #ef4444;">
                                            -<?= number_format($enroll['total_bourses'] ?? 0, 0, '.', ' ') ?> FCFA</td>
                                    </tr>
                                    <tr style="background-color: #f1f5f9; border-top: 1.5px solid #475569;">
                                        <td class="label-td"><?= __('net_amount_due') ?></td>
                                        <td class="value-td" style="color: #1e3a8a;">
                                            <?= number_format($enroll['scolarite_nette'] ?? 0, 0, '.', ' ') ?> FCFA</td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" style="color: #16a34a;"><?= __('total_cumulated_paid') ?></td>
                                        <td class="value-td" style="color: #16a34a;">
                                            <?= number_format($enroll['total_paye'] ?? 0, 0, '.', ' ') ?> FCFA</td>
                                    </tr>
                                    <tr style="background-color: #fef2f2; border-top: 1px double #ef4444;">
                                        <td class="label-td" style="color: #b91c1c;"><?= __('remaining_tuition_balance') ?>
                                        </td>
                                        <td class="value-td" style="color: #b91c1c; text-decoration: underline;">
                                            <?= number_format($enroll['reste_a_payer'] ?? 0, 0, '.', ' ') ?> FCFA</td>
                                    </tr>
                                </table>

                                <!-- MENTION OBLIGATOIRE EN GRAS, VISIBLE -->
                                <div
                                    style="font-weight: bold; font-size: 9.5px; color: #ef4444; margin-top: 2px; text-align: left; letter-spacing: 0.1px;">
                                    <?= __('non_refundable_notice') ?>
                                </div>
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
                                        <?= __('cashier_label') ?> <strong><?= h($payment['creator_nom'] ?? $payment['user_nom'] ?? '') ?> <?= h($payment['creator_prenom'] ?? $payment['user_prenom'] ?? '') ?></strong><br>
                                        <?= __('printed_on') ?> <?= date('d/m/Y H:i') ?>
                                    </div>
                                    <div style="margin-top: 25px; border-top: 1px dotted #94a3b8; width: 100%;"></div>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <!-- Pied de page -->
                    <div class="receipt-footer-text">
                        <?= __('official_receipt_doc') ?>     <?= h($settings['school_name'] ?? 'NotesMaster') ?>.<br>
                        Web: <?= h($settings['school_website'] ?? 'https://copobimat.camertech.com') ?>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>

    </div>

    <!-- Historique d'impression (Audit de Sécurité écran uniquement) -->
    <?php if (!$isPdf && !empty($printLogs)): ?>
        <div class="receipt-container no-print admin-logs-card">
            <h5 style="margin-top: 0; color: #1e293b; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px;">
                <i class="bi bi-clock-history"></i> <?= __('print_history_title') ?>
            </h5>
            <table style="width: 100%; border-collapse: collapse; font-size: 9px;">
                <thead>
                    <tr style="background-color: #f8fafc; border-bottom: 1px solid #cbd5e1; text-align: left;">
                        <th style="padding: 5px;"><?= __('col_datetime') ?></th>
                        <th style="padding: 5px;"><?= __('col_operator') ?></th>
                        <th style="padding: 5px;"><?= __('col_action') ?></th>
                        <th style="padding: 5px;"><?= __('col_print_number') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($printLogs as $log): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 5px; font-weight: bold;">
                                <?= date('d/m/Y H:i:s', strtotime($log['event_date'])) ?></td>
                            <td style="padding: 5px;"><?= h($log['user_nom']) ?>         <?= h($log['user_prenom']) ?></td>
                            <td style="padding: 5px; text-transform: uppercase; color: #2563eb; font-weight: bold;">
                                <?= __('print_action_label') ?></td>
                            <td style="padding: 5px; font-weight: bold;">
                                #<?= (int) $log['new_value'] ?>
                                <?php if ((int) $log['new_value'] === 1): ?>
                                    <span style="color: #16a34a; font-size: 7.5px; margin-left: 4px;">[ <?= __('original') ?>
                                        ]</span>
                                <?php else: ?>
                                    <span style="color: #ef4444; font-size: 7.5px; margin-left: 4px;">[ <?= __('duplicata') ?>
                                        ]</span>
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