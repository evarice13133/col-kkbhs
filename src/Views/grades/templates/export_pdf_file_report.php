<?php
/** @var array $students Liste des élèves */
/** @var string $school_name Nom de l'établissement */
/** @var string $logo_base64 Logo en base64 */
/** @var string $title Titre du document */
/** @var array $classInfo Infos de la classe */
/** @var array $subjectInfo Infos de la matière */
/** @var array $activeYear Année académique */

$app_lang = \App\Core\Session::get('app_lang', 'fr');
?>
<!DOCTYPE html>
<html lang="<?= $app_lang ?>">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Helvetica', sans-serif;
            color: #222;
            font-size: 10pt;
            /* marge droite pour éviter les débordements */
            margin: 10mm 12mm 10mm 10mm;
        }

        /* ===== EN-TÊTE (font-size 12pt) ===== */
        .header {
            width: 100%;
            border-bottom: 2.5px solid #2c3e50;
            padding-bottom: 10px;
            margin-bottom: 14px;
            display: table;
        }
        .header-logo {
            display: table-cell;
            vertical-align: middle;
            width: 80px;
        }
        .header-logo img {
            max-width: 75px;
            max-height: 75px;
        }
        .header-logo-placeholder {
            width: 70px;
            height: 70px;
            border: 1px dashed #aaa;
            text-align: center;
            line-height: 70px;
            font-size: 7pt;
            color: #aaa;
        }
        .header-info {
            display: table-cell;
            vertical-align: middle;
            padding-left: 14px;
            font-size: 12pt;
        }
        .header-info .school-name {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #2c3e50;
            margin-bottom: 4px;
        }
        .header-info .header-meta {
            font-size: 10pt;
            color: #666;
        }
        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            font-size: 10pt;
            color: #555;
        }

        /* ===== TITRE DU DOCUMENT ===== */
        .document-title {
            text-align: center;
            margin: 16px 0 8px 0;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #2c3e50;
            text-decoration: underline;
        }
        .document-subtitle {
            text-align: center;
            font-size: 10pt;
            color: #777;
            margin-bottom: 20px;
        }

        /* ===== TABLEAU (font-size 10pt) ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            table-layout: fixed;
        }
        thead tr {
            background-color: #2c3e50;
            color: #fff;
        }
        th {
            border: 1px solid #2c3e50;
            padding: 5px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 9pt;
            white-space: nowrap;
            overflow: hidden;
        }
        td {
            border: 1px solid #ccc;
            padding: 4px 4px;
            vertical-align: middle;
            word-wrap: break-word;
            overflow: hidden;
            height: 30px;
        }
        tr:nth-child(even) td {
            background-color: #f7f9fc;
        }
        .text-center { text-align: center; }
        .num-col { text-align: center; width: 35px; font-size: 9pt; color: #888; }
        .nom-col { font-weight: bold; }

        /* ===== PIED DE PAGE ===== */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 8pt;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 4px;
            display: table;
            width: 100%;
        }
        .footer-left  { display: table-cell; text-align: left; }
        .footer-right { display: table-cell; text-align: right; }

        .signature-zone {
            margin-top: 30px;
            width: 100%;
            display: table;
        }
        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding-top: 10px;
            font-size: 10pt;
            font-weight: bold;
        }
        .signature-line {
            margin: 40px auto 0;
            width: 150px;
            border-top: 1px solid #333;
        }
    </style>
</head>
<body>

    <!-- EN-TÊTE -->
    <div class="header">
        <div class="header-logo">
            <?php if (!empty($logo_base64)): ?>
                <img src="<?= $logo_base64 ?>" alt="Logo">
            <?php else: ?>
                <div class="header-logo-placeholder">LOGO</div>
            <?php endif; ?>
        </div>
        <div class="header-info">
            <div class="school-name"><?= htmlspecialchars($school_name) ?></div>
            <div class="header-meta"><?= date('d/m/Y') ?></div>
        </div>
        <div class="header-right">
            <strong><?= __('academic_year') ?></strong> : <?= htmlspecialchars($activeYear['nom'] ?? date('Y')) ?>
        </div>
    </div>

    <!-- TITRE -->
    <div class="document-title"><?= htmlspecialchars($title) ?></div>
    <div class="document-subtitle">
        <?= htmlspecialchars($subjectInfo['nom'] ?? '') ?> | <?= htmlspecialchars($classInfo['nom'] ?? '') ?>
    </div>

    <!-- TABLEAU DES ÉLÈVES -->
    <table>
        <thead>
            <tr>
                <th class="text-center num-col">N°</th>
                <th style="width: 50%;"><?= __('name_and_surname') ?></th>
                <th style="width: 20%;" class="text-center"><?= __('class') ?></th>
                <th style="width: 25%;" class="text-center"><?= __('note') ?> / 20</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($students)): ?>
                <tr>
                    <td colspan="4" class="text-center" style="padding: 20px; color: #999;">
                        <?= __('no_data') ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($students as $index => $student): ?>
                    <tr>
                        <td class="num-col"><?= $index + 1 ?></td>
                        <td class="nom-col">
                            <?= htmlspecialchars(
                                function_exists('mb_strtoupper')
                                    ? mb_strtoupper((string) $student['nom'], 'UTF-8')
                                    : strtoupper((string) $student['nom'])
                            ) ?> <?= htmlspecialchars((string) $student['prenom']) ?>
                        </td>
                        <td class="text-center"><?= htmlspecialchars((string) ($classInfo['nom'] ?? '-')) ?></td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="signature-zone">
        <div class="signature-box">
            <?= __('signature_teacher') ?>
            <div class="signature-line"></div>
        </div>
        <div class="signature-box">
            <?= __('administration_visa') ?>
            <div class="signature-line"></div>
        </div>
    </div>

    <!-- PIED DE PAGE -->
    <div class="footer">
        <span class="footer-left">NotesMaster &mdash; <?= htmlspecialchars($school_name) ?></span>
        <span class="footer-right"><?= __('generated_on') ?> : <?= date('d/m/Y H:i') ?></span>
    </div>

</body>
</html>
