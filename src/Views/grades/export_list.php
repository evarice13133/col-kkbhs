<?php
use App\Core\Database;
use App\Core\Translator;

/** Libellé traduit avec repli explicite. */
if (!function_exists('nm_export_t')) {
    function nm_export_t(string $key, string $fallbackFr): string
    {
        if (!function_exists('__')) {
            return $fallbackFr;
        }
        $v = (string) __($key);
        return ($v === '' || $v === $key) ? $fallbackFr : $v;
    }
}

// Charger l'autoloader si nécessaire
$nmRoot = dirname(__DIR__, 3);
if (is_file($nmRoot . '/vendor/autoload.php')) {
    require_once $nmRoot . '/vendor/autoload.php';
}
if (!function_exists('__')) {
    function __(string $key, array $replacements = [], $count = null): string
    {
        return Translator::translate($key, $replacements, $count);
    }
}

// Charger les paramètres institutionnels
$settings = [
    'school_republic' => 'Republique du Cameroun',
    'school_republic_en' => 'Republic of Cameroon',
    'school_ministry' => 'Ministere des Enseignements Secondaires',
    'school_ministry_en' => 'Ministry of Secondary Education',
    'school_motto' => 'Paix - Travail - Patrie',
    'school_motto_en' => 'Peace - Work - Fatherland',
    'school_name' => 'NotesMaster',
    'school_code' => 'CMR-COL',
];

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
    foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (\Throwable $e) {
}

// Utiliser LogoManager pour récupérer le logo comme dans le PV
$logoManager = \App\Core\LogoManager::getInstance($db);
$logoData = [
    'has_logo' => $logoManager->hasLogo(),
    'base64' => $logoManager->hasLogo() ? $logoManager->getLogoBase64() : '',
    'url' => $logoManager->getLogoUrl(),
    'fallback_letter' => $logoManager->getFallbackLetter()
];

$schoolMotto = $settings['school_motto'];
$schoolMottoEn = $settings['school_motto_en'];
$schoolName = $settings['school_name'];
$schoolCode = $settings['school_code'];
$generatedAt = (new DateTime())->format('d/m/Y H:i');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars((string) __('lang')) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(nm_export_t('grade_export_title', 'Export des notes')) ?></title>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; font-size: 11px; color: #111; background: #fff; }
        @media print { .toolbar { display: none !important; } }
        .toolbar { position: sticky; top: 0; z-index: 100; display: flex; align-items: center; justify-content: space-between; padding: 10px 20px; background: #1a1a2e; color: white; gap: 12px; margin-bottom: 20px; }
        .toolbar-title { font-weight: bold; font-size: 13px; }
        .btn { padding: 7px 18px; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: bold; text-decoration: none; }
        .btn-print { background: #0d6efd; color: white; }
        .btn-back { background: rgba(255,255,255,0.15); color: white; margin-right: 5px; }
        .sheet { padding: 0 10px; }
        .header-block { border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 10px; }
        .header-grid { display: flex; justify-content: space-between; align-items: center; }
        .header-side { width: 38%; text-align: center; font-size: 8px; text-transform: uppercase; line-height: 1.4; }
        .header-side.right { text-align: center; }
        .header-line { margin: 0 0 1px; font-weight: bold; }
        .header-center { text-align: center; }
        .logo-box { width: 50px; height: 50px; margin: 0 auto; display: flex; align-items: center; justify-content: center; }
        .logo-box img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .logo-fallback { font-weight: bold; font-size: 17px; }
        .school-name { font-size: 13px; font-weight: 800; text-transform: uppercase; margin-top: 5px; }
        .school-code { font-weight: bold; text-transform: uppercase; font-size: 11px; }
        .doc-line { text-align: center; font-weight: bold; text-transform: uppercase; margin: 8px 0; font-size: 14px; }
        .meta-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin: 10px 0; }
        .meta-box { border: 1px solid #000; padding: 6px; }
        .meta-label { font-size: 9px; text-transform: uppercase; color: #444; margin-bottom: 2px; }
        .meta-value { font-weight: bold; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px 8px; text-align: center; }
        th { background: #f0f0f0; text-transform: uppercase; font-size: 10px; font-weight: bold; }
        td { font-size: 11px; }
        td.text-left { text-align: left; }
        .footer { position: fixed; left: 10mm; right: 10mm; bottom: 5mm; display: flex; justify-content: space-between; font-size: 10px; }
    </style>
</head>
<body>
    <div class="toolbar">
        <div class="toolbar-title"><?= htmlspecialchars(nm_export_t('grade_export_title', 'Export des notes')) ?></div>
        <div>
            <a href="javascript:history.back()" class="btn btn-back">&larr; <?= htmlspecialchars(nm_export_t('back', 'Retour')) ?></a>
            <button class="btn btn-print" onclick="window.print()"><?= htmlspecialchars(nm_export_t('print', 'Imprimer')) ?></button>
        </div>
    </div>

    <div class="sheet">
        <div class="header-block">
            <div class="header-grid">
                <div class="header-side">
                    <div><?= htmlspecialchars($settings['school_republic'] ?? 'REPUBLIQUE DU CAMEROUN') ?></div>
                    <div style="font-weight: bold; font-style: italic;"><?= htmlspecialchars($schoolMotto) ?></div>
                    <div>**********</div>
                    <div><?= htmlspecialchars($settings['school_ministry'] ?? 'MINISTERE DES ENSEIGNEMENTS SECONDAIRES') ?></div>
                </div>

                <div class="header-center">
                    <div class="logo-box">
                        <?php if ($logoData['has_logo'] && !empty($logoData['base64'])): ?>
                            <img src="<?= htmlspecialchars($logoData['base64']) ?>" alt="Logo">
                        <?php elseif ($logoData['has_logo'] && !empty($logoData['url'])): ?>
                            <img src="<?= htmlspecialchars($logoData['url']) ?>" alt="Logo">
                        <?php else: ?>
                            <div class="logo-fallback"><?= htmlspecialchars(substr((string) $schoolCode, 0, 3)) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="school-name"><?= htmlspecialchars($schoolName) ?></div>
                </div>

                <div class="header-side right">
                    <div><?= htmlspecialchars($settings['school_republic_en'] ?? 'REPUBLIC OF CAMEROON') ?></div>
                    <div style="font-weight: bold; font-style: italic;"><?= htmlspecialchars($schoolMottoEn) ?></div>
                    <div>**********</div>
                    <div><?= htmlspecialchars($settings['school_ministry_en'] ?? 'MINISTRY OF SECONDARY EDUCATION') ?></div>
                </div>
            </div>
        </div>

        <div class="doc-line"><?= htmlspecialchars(nm_export_t('grade_export_title', 'RELEVÉ DES NOTES')) ?></div>

        <div class="meta-grid">
            <?php foreach ($exportMetaItems as $item): ?>
                <div class="meta-box">
                    <span class="meta-label"><?= htmlspecialchars((string) $item['label']) ?></span>
                    <div class="meta-value"><?= htmlspecialchars((string) $item['value']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <table>
            <thead>
                <tr>
                    <?php foreach ($exportColumns as $column): ?>
                        <th><?= htmlspecialchars((string) $column) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exportRows as $row): ?>
                    <tr>
                        <?php foreach ($row as $cell): ?>
                            <td class="text-left"><?= htmlspecialchars((string) $cell) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($exportRows)): ?>
                    <tr>
                        <td colspan="<?= count($exportColumns) ?>" style="text-align:center; padding: 20px;">
                            <?= htmlspecialchars(nm_export_t('no_grades', 'Aucune note à exporter.')) ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div style="margin-top: 20px; font-size: 10px; color: #666;">
            <?= htmlspecialchars(nm_export_t('generated_at', 'Généré le')) ?> <?= htmlspecialchars((string) $generatedAt) ?>
        </div>
    </div>

    <div class="footer">
        <div>&copy; <?= htmlspecialchars((string) $schoolCode) ?></div>
        <div><?= htmlspecialchars((string) $generatedAt) ?></div>
    </div>
</body>
</html>
