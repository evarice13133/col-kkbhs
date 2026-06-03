<?php

use App\Core\Database;
use App\Core\Translator;

/** Libellé traduit avec repli explicite (évite l'affichage brut des clés si __() est indisponible). */
if (!function_exists('nm_report_t')) {
    function nm_report_t(string $key, string $fallbackFr): string
    {
        if (!function_exists('__')) {
            return $fallbackFr;
        }
        $v = (string) __($key);

        return ($v === '' || $v === $key) ? $fallbackFr : $v;
    }
}

// Si la vue est rendue hors du bootstrap principal, charger l'autoloader et __().
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

// On recharge les parametres institutionnels pour garder un rendu officiel sur la fiche papier.
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

if (!function_exists('nm_report_short_label')) {
    function nm_report_short_label($label)
    {
        // On raccourcit les evaluations pour garder une fiche de collecte lisible en portrait.
        if (preg_match('/Trimestre\s+(\d+)\s*-\s*Sequence\s+(\d+)/i', (string) $label, $matches)) {
            return 'Trim ' . $matches[1] . ' Seq-' . $matches[2];
        }

        return (string) $label;
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars((string) __('lang')) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(nm_report_t('grade_report_document_title', 'Fiche de relevé de notes')) ?> — <?= htmlspecialchars((string) ($classInfo['nom'] ?? '')) ?> — <?= htmlspecialchars((string) ($subjectInfo['nom'] ?? '')) ?></title>
    <style>
        @page { size: A4 portrait; margin: 10mm 8mm 14mm 8mm; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; font-size: 10.5px; color: #111; background: #fff; }
        /* BARRE D'OUTILS */
        @media print { .pv-toolbar { display: none !important; } }
        .pv-toolbar { position: sticky; top: 0; z-index: 100; display: flex; align-items: center; justify-content: space-between; padding: 10px 20px; background: #1a1a2e; color: white; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; font-family: Arial, Helvetica, sans-serif;}
        .pv-toolbar-title { font-weight: bold; font-size: 13px; opacity: 0.9; }
        .pv-toolbar-hint  { font-size: 10px; opacity: 0.6; margin-right: auto; }
        .pv-btn { padding: 7px 18px; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: bold; text-decoration: none; transition: opacity 0.2s; display: inline-block; }
        .pv-btn:hover { opacity: 0.85; }
        .pv-btn-print { background: #0d6efd; color: white; }
        .pv-btn-back { background: rgba(255,255,255,0.15); color: white; margin-right: 5px; }
        .sheet { padding: 0 8px 12px; }
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
        .doc-line { text-align: center; font-weight: bold; text-transform: uppercase; margin: 4px 0 2px; }
        .meta-line { text-align: center; margin: 0 0 5px; text-transform: uppercase; font-size: 10px; }
        .info-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 6px; margin: 8px 0 10px; }
        .info-box { border: 1px solid #000; padding: 4px 6px; min-height: 30px; }
        .info-label { display: block; font-size: 8px; text-transform: uppercase; color: #444; margin-bottom: 1px; }
        .info-value { font-weight: bold; font-size: 9.2px; line-height: 1.15; }
        .guide-note { margin: 0 0 8px; font-size: 9.8px; color: #444; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px 4px; vertical-align: middle; }
        th { background: #efefef; text-transform: uppercase; font-size: 9px; text-align: center; }
        td { height: 28px; }
        td.student-cell { font-weight: bold; }
        td.blank-cell { background: #fff; }
        .number-cell { width: 30px; text-align: center; }
        .student-cell { width: 32%; }
        .evaluation-cell { width: 11%; text-align: center; }
        .observation-cell { width: 18%; }
        .signature-zone { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; margin-top: 12px; }
        .signature-box { border-top: 1px solid #000; padding-top: 6px; min-height: 42px; text-align: center; font-size: 10px; }
        .footer { position: fixed; left: 8mm; right: 8mm; bottom: 4mm; display: flex; justify-content: space-between; font-size: 10px; }
        @media print { .pv-toolbar { display: none; } }
    </style>
</head>
<body>
    <!-- BARRE D'OUTILS (Non visible à l'impression) -->
    <div class="pv-toolbar">
        <div class="pv-toolbar-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="vertical-align:-3px; margin-right:5px;">
                <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                <path d="M4.603 14.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.697 19.697 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .471.215c.15.18-.162 1.305-.162 1.305v.006c-.316.427-.58.111-.58.111s.54.407.728.846c.155.362.29.74.405 1.134.208.718.36 1.4.453 1.954.555.15 1.144.33 1.705.513.29.096.55.195.74.296.262.138.45.321.492.51.042.19.014.39-.115.546-.129.155-.327.24-.546.269-.219.03-.466-.02-.713-.102a4.954 4.954 0 0 1-1.396-.757c-.88-.705-1.58-1.748-1.9-2.235-.351.054-.7.108-1.049.157-.428.06-1.08.125-1.764.125-.453.03-.9.08-1.332.146-.356.055-.705.12-1.05.19-.24.049-.49.123-.715.22z"/>
            </svg>
            <?= htmlspecialchars(nm_report_t('grade_report_toolbar_title', 'Impression et export PDF')) ?>
        </div>
        <div class="pv-toolbar-hint">
            <?= htmlspecialchars(nm_report_t('pv_print_hint', 'Utilisez « Imprimer » puis « Enregistrer au format PDF » dans la boîte de dialogue.')) ?>
        </div>
        <div>
            <a href="javascript:history.back()" class="pv-btn pv-btn-back">
                &larr; <?= htmlspecialchars(nm_report_t('page_back', 'Retour')) ?>
            </a>
            <button class="pv-btn pv-btn-print" onclick="window.print()">
                <?= htmlspecialchars(nm_report_t('print_pdf', 'Imprimer / PDF')) ?>
            </button>
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

        <div class="doc-line"><?= htmlspecialchars(nm_report_t('grade_report_document_title', 'FICHE DE RELEVÉ DE NOTES')) ?></div>
        <div class="meta-line"><?= htmlspecialchars(nm_report_t('grade_report_blank_sheet_hint', 'Fiche vierge de collecte des notes par évaluation (saisie manuelle).')) ?></div>

        <div class="info-grid">
            <div class="info-box">
                <span class="info-label"><?= htmlspecialchars(nm_report_t('class', 'Classe')) ?></span>
                <div class="info-value"><?= htmlspecialchars((string) ($classInfo['nom'] ?? '-')) ?></div>
            </div>
            <div class="info-box">
                <span class="info-label"><?= htmlspecialchars(nm_report_t('subject', 'Matière')) ?></span>
                <div class="info-value"><?= htmlspecialchars((string) ($subjectInfo['nom'] ?? '-')) ?><?php if (isset($subjectInfo['coefficient'])): ?> | <?= htmlspecialchars(nm_report_t('coef', 'Coef.')) ?> <?= (int) $subjectInfo['coefficient'] ?><?php endif; ?></div>
            </div>
            <div class="info-box">
                <span class="info-label"><?= htmlspecialchars(nm_report_t('academic_year', 'Année scolaire')) ?></span>
                <div class="info-value"><?= htmlspecialchars((string) ($activeYear['nom'] ?? '-')) ?></div>
            </div>
            <div class="info-box">
                <span class="info-label"><?= htmlspecialchars(nm_report_t('teacher', 'Enseignant')) ?></span>
                <div class="info-value"><?= htmlspecialchars($teacherName !== '' ? $teacherName : nm_report_t('not_specified', 'Non spécifié')) ?></div>
            </div>
        </div>

        <p class="guide-note">
            <?= htmlspecialchars(nm_report_t('grade_report_blank_sheet_hint', 'Fiche vierge de collecte des notes par évaluation (saisie manuelle).')) ?>
            <?= htmlspecialchars(nm_report_t('date_generated', 'Généré le')) ?> <?= htmlspecialchars((string) strtoupper($generatedAt)) ?>.
        </p>

        <table>
            <thead>
                <tr>
                    <th class="number-cell">N°</th>
                    <th class="student-cell"><?= htmlspecialchars(nm_report_t('name_and_surname', 'Noms et prénoms')) ?></th>
                    <?php foreach ($activeEvaluations as $evaluation): ?>
                        <th class="evaluation-cell"><?= htmlspecialchars(nm_report_short_label((string) $evaluation)) ?></th>
                    <?php endforeach; ?>
                    <th class="observation-cell"><?= htmlspecialchars(nm_report_t('observation', 'Observation')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $index => $student): ?>
                    <tr>
                        <td class="number-cell"><?= $index + 1 ?></td>
                        <td class="student-cell"><?= htmlspecialchars((string) $student['nom']) ?> <?= htmlspecialchars((string) $student['prenom']) ?></td>
                        <?php foreach ($activeEvaluations as $evaluation): ?>
                            <td class="blank-cell"></td>
                        <?php endforeach; ?>
                        <td class="blank-cell"></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="<?= count($activeEvaluations) + 3 ?>" style="text-align:center; padding: 12px;"><?= htmlspecialchars(nm_report_t('student_none_in_class', 'Aucun élève dans cette classe.')) ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="signature-zone">
            <div class="signature-box"><?= htmlspecialchars(nm_report_t('signature_teacher', 'Professeur')) ?></div>
            <div class="signature-box"><?= htmlspecialchars(nm_report_t('administration_visa', 'Visa administration')) ?></div>
        </div>
    </div>

    <div class="footer">
        <div>&copy; Page 1 | evaricekuete2@gmail.com</div>
        <div><?= htmlspecialchars((string) $schoolCode) ?></div>
    </div>
</body>
</html>
