<?php
// Vue officielle autonome : Historique complet des versements d'un élève (Design Premium avec Titres de Sections Simples)
$app_lang = \App\Core\Session::get('app_lang', 'fr');
\App\Core\Translator::load($app_lang);

// Logo de l'établissement
$logoPath = null;
if (!empty($settings['school_logo'])) {
    $possiblePath = __DIR__ . '/../../public/storage/' . $settings['school_logo'];
    if (file_exists($possiblePath)) {
        $logoPath = $possiblePath;
    }
}
if (!$logoPath) {
    $defaultLogo = __DIR__ . '/../../public/assets/images/logo.png';
    if (file_exists($defaultLogo)) {
        $logoPath = $defaultLogo;
    }
}
$logoSrc = '';
if ($logoPath && file_exists($logoPath)) {
    $mime = mime_content_type($logoPath);
    $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
}

$generatedAt = date('d/m/Y H:i');
$scolariteBrute = (float)($enroll['frais_scolarite_brut'] ?? 0);
$totalReductions = (float)($enroll['total_reductions'] ?? 0) + (float)($enroll['total_bourses'] ?? 0);
$scolariteNette = (float)($enroll['scolarite_nette'] ?? ($scolariteBrute - $totalReductions));
$totalPaye = (float)($enroll['total_paye'] ?? 0);
$resteAPayer = (float)($enroll['reste_a_payer'] ?? max(0.0, $scolariteNette - $totalPaye));

// Traitement du matricule élève
$matricule = !empty($student['matricule']) ? $student['matricule'] : (!empty($student['email']) ? $student['email'] : 'N/A');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars((string) $app_lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('full_payment_history_doc') ?> - <?= h($student['nom'] ?? '') ?> <?= h($student['prenom'] ?? '') ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 10mm 12mm 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        /* Direct Print Action Controls */
        .no-print-bar {
            background: #0f172a;
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .btn-print {
            background: #2563eb;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print:hover { background: #1d4ed8; }

        @media print {
            .no-print-bar { display: none !important; }
            body { background: white; }
            .container { padding: 0; max-width: 100%; }
        }

        /* En-tête Institutionnel Raffiné */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }

        .header-table td {
            vertical-align: middle;
        }

        .school-title {
            font-size: 15px;
            font-weight: 800;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .school-meta {
            font-size: 9px;
            color: #64748b;
        }

        .doc-title {
            font-size: 14px;
            font-weight: 900;
            color: #2563eb;
            text-transform: uppercase;
            text-align: right;
            margin: 0;
            letter-spacing: 0.5px;
        }

        .doc-sub {
            font-size: 9px;
            color: #475569;
            text-align: right;
        }

        /* Titre de Section SIMPLE & ÉPURÉ (Demande explicite de l'utilisateur) */
        .section-title-simple {
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
        }

        /* Tableau d'informations Élève & Finances */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            overflow: hidden;
        }

        .info-table td {
            padding: 6px 10px;
            font-size: 9.5px;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-table tr:last-child td {
            border-bottom: none;
        }

        .lbl {
            font-weight: 700;
            color: #475569;
            width: 18%;
            background-color: #f8fafc;
            border-right: 1px solid #e2e8f0;
        }

        .val {
            color: #0f172a;
            width: 32%;
            font-weight: 500;
        }

        .val-highlight {
            font-weight: 800;
        }

        /* Tableau des Versements Premium */
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #cbd5e1;
        }

        .history-table thead {
            display: table-header-group;
        }

        .history-table tr {
            page-break-inside: avoid;
        }

        .history-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 7px 10px;
            text-align: left;
            border: 1px solid #1e3a8a;
        }

        .history-table td {
            padding: 7px 10px;
            font-size: 9.5px;
            border-bottom: 1px solid #e2e8f0;
            border-left: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
        }

        .history-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .badge-status {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 50px;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-paid {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .badge-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Signatures */
        .signature-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .signature-cell {
            width: 48%;
            border: 1px dashed #94a3b8;
            background-color: #fafafa;
            border-radius: 6px;
            padding: 10px;
            height: 75px;
            vertical-align: top;
            font-size: 8.5px;
        }
    </style>
</head>
<body>

    <div class="container">
        
        <?php if (!isset($isPdf) || !$isPdf): ?>
        <div class="no-print-bar">
            <div>
                <strong><?= __('full_payment_history_doc') ?></strong> — <?= h($student['nom'] ?? '') ?> <?= h($student['prenom'] ?? '') ?>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn-print" onclick="window.print()">
                    <i class="bi bi-printer"></i> <?= __('print_history_btn') ?>
                </button>
                <a href="?id=<?= (int)$student['id'] ?>&pdf=1" class="btn-print" style="background: #059669;">
                    <i class="bi bi-file-earmark-pdf"></i> <?= __('download_history_pdf') ?>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- En-tête Institutionnel -->
        <table class="header-table">
            <tr>
                <td style="width: 15%;">
                    <?php if ($logoSrc): ?>
                        <img src="<?= $logoSrc ?>" style="max-height: 48px; max-width: 75px; border-radius: 4px;" alt="Logo">
                    <?php endif; ?>
                </td>
                <td style="width: 45%;">
                    <div class="school-title"><?= h($settings['school_name'] ?? 'Établissement Scolaire') ?></div>
                    <div class="school-meta"><?= h($settings['school_address'] ?? '') ?></div>
                    <div class="school-meta"><?= __('academic_year_verify') ?> : <strong><?= h($enroll['annee_scolaire'] ?? $settings['display_school_year'] ?? '') ?></strong></div>
                </td>
                <td style="width: 40%;">
                    <h1 class="doc-title"><?= __('full_payment_history_doc') ?></h1>
                    <div class="doc-sub"><?= __('student_payment_history_sub') ?></div>
                    <div class="doc-sub" style="margin-top: 3px; font-style: italic;"><?= __('generated_on') ?> <?= $generatedAt ?></div>
                </td>
            </tr>
        </table>

        <!-- Titre de section 1 : Simple & Épuré -->
        <div class="section-title-simple">
            <?= __('student_info_section_title') ?> &amp; <?= __('financial_summary') ?>
        </div>

        <table class="info-table">
            <tr>
                <td class="lbl"><?= __('student') ?> :</td>
                <td class="val val-highlight"><?= h($student['nom'] ?? '') ?> <?= h($student['prenom'] ?? '') ?></td>
                <td class="lbl"><?= __('net_tuition') ?> :</td>
                <td class="val val-highlight" style="color: #1e3a8a;"><?= number_format($scolariteNette, 0, '.', ' ') ?> FCFA</td>
            </tr>
            <tr>
                <td class="lbl"><?= __('matricule') ?> :</td>
                <td class="val font-monospace val-highlight" style="color: #2563eb;"><?= h($matricule) ?></td>
                <td class="lbl"><?= __('total_already_collected') ?> :</td>
                <td class="val val-highlight" style="color: #16a34a;"><?= number_format($totalPaye, 0, '.', ' ') ?> FCFA</td>
            </tr>
            <tr>
                <td class="lbl"><?= __('class') ?> :</td>
                <td class="val"><?= h($student['classe_nom'] ?? 'N/A') ?></td>
                <td class="lbl"><?= __('remaining_tuition_balance') ?> :</td>
                <td class="val val-highlight" style="color: #b91c1c;"><?= number_format($resteAPayer, 0, '.', ' ') ?> FCFA</td>
            </tr>
            <?php if (!empty($student['date_naissance'])): ?>
            <tr>
                <td class="lbl"><?= __('born_on') ?> :</td>
                <td class="val" colspan="3">
                    <?= date('d/m/Y', strtotime($student['date_naissance'])) ?>
                    <?= !empty($student['lieu_naissance']) ? ' ' . __('born_at') . ' ' . h($student['lieu_naissance']) : '' ?>
                </td>
            </tr>
            <?php endif; ?>
        </table>

        <!-- Titre de section 2 : Simple & Épuré -->
        <div class="section-title-simple">
            <?= __('full_history_current_year') ?>
        </div>

        <table class="history-table">
            <thead>
                <tr>
                    <th style="width: 13%;"><?= __('col_date') ?></th>
                    <th style="width: 17%;"><?= __('nature_payment') ?></th>
                    <th style="width: 20%;"><?= __('col_reference') ?></th>
                    <th style="width: 14%;"><?= __('col_method') ?></th>
                    <th style="width: 18%; text-align: right;"><?= __('col_amount') ?></th>
                    <th style="width: 18%; text-align: center;"><?= __('col_status') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalPaidCalculated = 0.0;
                if (empty($paymentsHistory)): 
                ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 16px; color: #64748b;">
                            <?= __('no_payments_registered') ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($paymentsHistory as $ph): 
                        $isCancelled = ($ph['status'] ?? '') === 'annule';
                        $amt = (float)($ph['amount'] ?? 0);
                        if (!$isCancelled) {
                            $totalPaidCalculated += $amt;
                        }
                    ?>
                        <tr style="<?= $isCancelled ? 'opacity: 0.55; background-color: #f1f5f9;' : '' ?>">
                            <td><strong><?= date('d/m/Y', strtotime($ph['payment_date'])) ?></strong></td>
                            <td>
                                <?= ($ph['type'] ?? '') === 'inscription' ? __('registration_payment_option') : __('tuition_payment_option') ?>
                            </td>
                            <td style="font-family: monospace; font-size: 9px;"><?= h($ph['reference'] ?: '-') ?></td>
                            <td><?= h(strtoupper($ph['payment_method'] ?? '')) ?></td>
                            <td style="text-align: right; font-weight: 800; <?= $isCancelled ? 'text-decoration: line-through; color: #94a3b8;' : 'color: #15803d;' ?>">
                                <?= number_format($amt, 0, '.', ' ') ?> FCFA
                            </td>
                            <td style="text-align: center;">
                                <?php if ($isCancelled): ?>
                                    <span class="badge-status badge-cancelled"><?= __('status_cancelled') ?></span>
                                <?php else: ?>
                                    <span class="badge-status badge-paid"><?= __('status_paid') ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr style="background: #f8fafc; font-weight: bold; border-top: 2px solid #1e3a8a;">
                    <td colspan="4" style="text-align: right; text-transform: uppercase; color: #1e3a8a; font-size: 9px;"><?= __('cumulative_total_paid') ?> :</td>
                    <td style="text-align: right; color: #16a34a; font-weight: 900; font-size: 11px;"><?= number_format($totalPaidCalculated, 0, '.', ' ') ?> FCFA</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <!-- Signatures -->
        <table class="signature-table">
            <tr>
                <td class="signature-cell">
                    <strong style="color: #475569; text-transform: uppercase;"><?= __('cashier_signature_label') ?></strong>
                    <div style="margin-top: 35px; border-top: 1px dotted #cbd5e1; width: 100%;"></div>
                </td>
                <td style="width: 4%;"></td>
                <td class="signature-cell">
                    <strong style="color: #475569; text-transform: uppercase;"><?= __('direction_stamp_label') ?></strong>
                    <div style="margin-top: 35px; border-top: 1px dotted #cbd5e1; width: 100%;"></div>
                </td>
            </tr>
        </table>

    </div>

</body>
</html>
