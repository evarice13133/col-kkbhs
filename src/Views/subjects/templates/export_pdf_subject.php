<?php
/** @var array $subjects Liste des matières */
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
        body {
            font-family: 'Helvetica', sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #444;
            padding-bottom: 15px;
            margin-bottom: 20px;
            display: table;
        }
        .header-logo {
            display: table-cell;
            vertical-align: middle;
            width: 80px;
        }
        .header-logo img {
            max-width: 80px;
            max-height: 80px;
        }
        .header-info {
            display: table-cell;
            vertical-align: middle;
            padding-left: 20px;
            font-size: 12pt;
        }
        .header-info h1 {
            margin: 0;
            font-size: 14pt;
            text-transform: uppercase;
        }
        .document-title {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 30px;
            text-decoration: underline;
            font-size: 13pt;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11pt;
        }
        th {
            background-color: #f2f2f2;
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        td {
            border: 1px solid #ccc;
            padding: 8px;
            vertical-align: top;
        }
        .text-center { text-align: center; }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: right;
            font-size: 8pt;
            color: #777;
            padding-top: 5px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-logo">
            <?php if (!empty($logo_base64)): ?>
                <img src="<?= $logo_base64 ?>" alt="Logo">
            <?php else: ?>
                <div style="width: 80px; height: 80px; border: 1px dashed #ccc; text-align: center; line-height: 80px; font-size: 8pt;">LOGO</div>
            <?php endif; ?>
        </div>
        <div class="header-info">
            <h1><?= htmlspecialchars($school_name) ?></h1>
            <p><?= date('d/m/Y H:i') ?></p>
        </div>
    </div>

    <div class="document-title">
        <?= htmlspecialchars($title) ?>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30%;"><?= __('subject') ?></th>
                <th style="width: 10%;" class="text-center"><?= __('coef') ?></th>
                <th style="width: 20%;"><?= __('group') ?></th>
                <th style="width: 40%;"><?= __('classes') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($subjects)): ?>
                <tr>
                    <td colspan="4" class="text-center"><?= __('no_data') ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($subjects as $s): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars((string) $s['nom']) ?></strong></td>
                        <td class="text-center"><?= (int) $s['coefficient'] ?></td>
                        <td><?= htmlspecialchars($s['groupe'] ?? 'Groupe 1') ?></td>
                        <td>
                            <span style="font-size: 9pt;">
                                <?= htmlspecialchars((string) ($s['classes_list'] ?: __('no_class_associated'))) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        NotesMaster - <?= date('Y') ?> - Page 1/1
    </div>
</body>
</html>
