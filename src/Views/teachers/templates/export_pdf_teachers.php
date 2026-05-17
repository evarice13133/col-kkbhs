<?php
/** @var array $teachers Liste des enseignants */
/** @var string $school_name Nom de l'établissement */
/** @var string $logo_base64 Logo en base64 */
/** @var string $title Titre du document */

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
            /* marges : haut / droite / bas / gauche */
            margin: 10mm 12mm 10mm 10mm;
        }

        /* ===== EN-TÊTE (font-size 12pt) ===== */
        .header {
            width: 100%;
            border-bottom: 2.5px solid #1a3a5c;
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
            color: #1a3a5c;
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
            margin: 14px 0 6px 0;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #1a3a5c;
            text-decoration: underline;
        }
        .document-subtitle {
            text-align: center;
            font-size: 9pt;
            color: #777;
            margin-bottom: 14px;
        }

        /* ===== RÉSUMÉ ===== */
        .summary-box {
            background: #eef2f7;
            border-left: 4px solid #1a3a5c;
            padding: 5px 10px;
            margin-bottom: 12px;
            font-size: 10pt;
            color: #444;
        }

        /* ===== TABLEAU (taille des éléments réduite à 11px) ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            table-layout: fixed;
        }
        thead tr {
            background-color: #1a3a5c;
            color: #fff;
        }
        th {
            border: 1px solid #1a3a5c;
            padding: 6px 5px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            white-space: nowrap;
            overflow: hidden;
        }
        td {
            border: 1px solid #ccc;
            padding: 6px 5px;
            vertical-align: middle;
            word-wrap: break-word;
            overflow: hidden;
            font-size: 11px;
        }
        tr:nth-child(even) td {
            background-color: #f4f7fb;
        }
        .text-center { text-align: center; }
        .num-col   { text-align: center; color: #666; font-size: 11px; }
        .login-col { font-family: monospace; font-size: 11px; color: #444; }
        .nom-col   { font-weight: bold; font-size: 11px; }
        .tag-list  { font-size: 10.5px; color: #333; line-height: 1.4; }

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
            <strong><?= __('teachers') ?></strong><br>
            <?= __('academic_year') ?> : <?= htmlspecialchars($academic_year_nom ?? date('Y')) ?>
        </div>
    </div>

    <!-- TITRE -->
    <div class="document-title"><?= htmlspecialchars($title) ?></div>
    <div class="document-subtitle">
        <?= count($teachers) ?> <?= count($teachers) > 1 ? __('teachers') : __('teacher') ?>
    </div>

    <!-- RÉSUMÉ -->
    <div class="summary-box">
        <?= __('total') ?> : <strong><?= count($teachers) ?> <?= __('teachers') ?></strong>
    </div>

    <!-- TABLEAU DES ENSEIGNANTS -->
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 15px;">N°</th>
                <th style="width: 30%;"><?= $app_lang === 'fr' ? 'Nom & Prénom' : 'Name & Surname' ?></th>
                <th style="width: 15%;"><?= __('username') ?></th>
                <th style="width: 25%;"><?= __('subjects') ?></th>
                <th style="width: 25%;"><?= __('classes') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($teachers)): ?>
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px; color: #999;">
                        <?= __('no_data') ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($teachers as $index => $teacher): ?>
                    <tr>
                        <td class="num-col"><?= $index + 1 ?></td>
                        <td class="nom-col">
                            <?php 
                                $formatted_nom = function_exists('mb_strtoupper') 
                                    ? mb_strtoupper((string) $teacher['nom'], 'UTF-8') 
                                    : strtoupper((string) $teacher['nom']);
                                $formatted_prenom = function_exists('mb_convert_case') 
                                    ? mb_convert_case((string) $teacher['prenom'], MB_CASE_TITLE, 'UTF-8') 
                                    : ucwords(strtolower((string) $teacher['prenom']));
                                echo htmlspecialchars($formatted_nom . ' ' . $formatted_prenom);
                            ?>
                        </td>
                        <td class="login-col"><?= htmlspecialchars((string) $teacher['username']) ?></td>
                        <td class="tag-list">
                            <?= htmlspecialchars((string) ($teacher['subjects_list'] ?: '-')) ?>
                        </td>
                        <td class="tag-list">
                            <?= htmlspecialchars((string) ($teacher['classes_list'] ?: '-')) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- PIED DE PAGE -->
    <div class="footer">
        <span class="footer-left">NotesMaster &mdash; <?= htmlspecialchars($school_name) ?></span>
        <span class="footer-right"><?= __('generated_on') ?> : <?= date('d/m/Y H:i') ?></span>
    </div>

</body>
</html>
