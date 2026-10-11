<?php
$lang = \App\Core\Locale::get();
$schoolName = trim((string) ($institution['school_name'] ?? '')) ?: 'NotesMaster';
$defaultRepublic = $lang === 'en' ? 'Republic of Cameroon' : 'République du Cameroun';
$defaultMinistry = $lang === 'en' ? 'Ministry of Secondary Education' : 'Ministère des Enseignements Secondaires';
$nationalMotto = $lang === 'en' ? 'Peace - Work - Fatherland' : 'Paix - Travail - Patrie';
$republic = trim((string) ($institution[$lang === 'en' ? 'school_republic_en' : 'school_republic'] ?? '')) ?: $defaultRepublic;
$ministry = trim((string) ($institution[$lang === 'en' ? 'school_ministry_en' : 'school_ministry'] ?? '')) ?: $defaultMinistry;
$delegationFr = trim((string) ($institution['school_delegation'] ?? ''));
$delegationEn = trim((string) ($institution['school_delegation_en'] ?? ''));
$formatDelegation = static function (string $delegation): string {
    $parts = array_values(array_filter(array_map('trim', explode(';', $delegation)), static fn(string $part): bool => $part !== ''));
    return implode(';<br>', array_map(static fn(string $part): string => htmlspecialchars($part, ENT_QUOTES, 'UTF-8'), $parts));
};
$schoolSlogan = trim((string) ($institution[$lang === 'en' ? 'school_slogan_en' : 'school_slogan'] ?? ''));
$phone = trim((string) ($institution['school_phone'] ?? ''));
$address = trim((string) ($institution['school_address'] ?? ''));
$city = trim((string) ($institution['school_city'] ?? ''));
$poBox = trim((string) ($institution['school_po_box'] ?? ''));
$email = trim((string) ($institution['school_email'] ?? ''));
$addressLine = $address !== '' && $city !== ''
    ? $address . ' - ' . $city
    : ($address !== '' ? $address : $city);
$contactParts = [];
if ($poBox !== '') {
    $contactParts[] = ($lang === 'en' ? 'P.O. Box ' : 'BP ') . $poBox;
}
if ($phone !== '') {
    $contactParts[] = ($lang === 'en' ? 'Tel: ' : 'Tél: ') . $phone;
}
if ($email !== '') {
    $contactParts[] = $email;
}
$contactLine = implode(' · ', $contactParts);
$schoolLogo = trim((string) ($institution['school_logo'] ?? ''));
$schoolSlogan = $schoolSlogan !== '' ? $schoolSlogan : ($lang === 'en' ? 'Discipline - Work - Success' : 'Discipline - Travail - Succès');
$level = trim((string) ($class['cycle_name'] ?? '')) ?: trim((string) ($class['teaching_type_name'] ?? ''));
$previewMode = (string) ($_GET['preview'] ?? '') === '1';
$pageMargin = $orientation === 'portrait' ? '2.5mm' : '5mm';
$pdfComplement = (string) $class['nom'];
if ($studentId > 0 && isset($students[0])) {
    $pdfComplement = trim(implode(' ', array_filter([
        trim((string) ($students[0]['nom'] ?? '')),
        trim((string) ($students[0]['prenom'] ?? '')),
    ], static fn(string $part): bool => $part !== '')));
}
$pdfComplement = trim(preg_replace('~[\\\\/:*?"<>|\x00-\x1F]~u', '-', $pdfComplement) ?? $pdfComplement, " .-");
$pdfFileName = __('access_cards_title') . ' - ' . $pdfComplement . '.pdf';

$imagePath = static function (?string $path): string {
    $path = trim((string) $path);
    if ($path === '') {
        return '';
    }
    if (strpos($path, 'uploads/') === 0) {
        $path = '/public/' . $path;
    } elseif (strpos($path, '/uploads/') === 0) {
        $path = '/public' . $path;
    } elseif (strpos($path, 'public/') === 0) {
        $path = '/' . $path;
    }
    return $path;
};
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pdfFileName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #e8edf0; color: #111; font-family: Arial, sans-serif; }
        .toolbar { position: sticky; top: 0; z-index: 5; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 20px; background: #173c34; color: #fff; font-family: Arial, sans-serif; }
        .toolbar h1 { margin: 0; font-size: 15px; }
        .toolbar-actions { display: flex; gap: 8px; }
        .toolbar a, .toolbar button { display: inline-flex; align-items: center; justify-content: center; min-height: 36px; padding: 0 14px; border: 0; border-radius: 4px; color: #173c34; background: #fff; font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; }
        .toolbar button { background: #f2c94c; }
        .sheet { margin: 18px auto; padding: 18px; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); grid-template-rows: repeat(2, minmax(0, 1fr)); gap: 12px; background: #fff; box-shadow: 0 8px 28px #17232a1a; page-break-after: always; break-after: page; }
        .sheet-landscape { width: min(100% - 32px, 1123px); aspect-ratio: 297 / 210; }
        .sheet-portrait { width: min(100% - 32px, 794px); aspect-ratio: 210 / 297; grid-template-rows: repeat(4, minmax(0, 1fr)); gap: 12px; }
        .sheet:last-child { page-break-after: auto; break-after: auto; }
        .card-slot { min-width: 0; min-height: 0; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .access-card { flex: none; width: 141.5mm; height: 98mm; min-width: 0; min-height: 0; padding: 3mm; display: flex; flex-direction: column; overflow: hidden; border: 1px dashed var(--card-border); background: var(--card-background); color: var(--card-text); }
        .sheet-portrait .access-card { transform: scale(.69); }
        .header-top { display: grid; grid-template-columns: minmax(0, 1fr) 18mm minmax(0, 1fr); align-items: start; gap: 2mm; text-align: center; font-size: 2mm; line-height: 1.05; }
        .header-left, .header-right { min-width: 0; }
        .header-right { text-align: center; }
        .header-strong { color: var(--card-accent); font-size: 10px; font-weight: 700; }
        .stars { margin: .5mm 0; color: var(--card-accent); letter-spacing: 1mm; }
        .logo-container { display: flex; justify-content: center; align-items: center; }
        .school-logo, .logo-placeholder { width: 15mm; height: 15mm; object-fit: contain; }
        .logo-placeholder { display: grid; place-items: center; border: .3mm solid var(--card-border); border-radius: 50%; background: #ffffff80; color: var(--card-muted); font-size: 2.2mm; }
        .header-center-text { margin-top: .5mm; text-align: center; line-height: 1; }
        .school-type { color: var(--card-accent); font-size: 14px; font-weight: 700; }
        .school-type::after { content: ""; display: block; width: 34mm; height: .6mm; margin: .5mm auto 0; border-radius: 1mm; background: linear-gradient(to right, var(--flag-green) 0 33.33%, var(--flag-red) 33.33% 66.66%, var(--flag-yellow) 66.66%); }
        .school-type-en { color: var(--card-muted); font-size: 10px; font-style: italic; }
        .cycle { margin: .3mm 0; color: var(--card-text); font-size: 11px; }
        .address { margin-top: .5mm; color: var(--card-text); font-size: 11px; font-weight: 700; overflow-wrap: anywhere; }
        .contact { color: var(--card-muted); font-size: 11px; overflow-wrap: anywhere; }
        .separator { position: relative; height: 2.5mm; margin: .2mm auto; width: 60%; display: grid; place-items: center; background: linear-gradient(to right, var(--flag-green) 0 33.33%, var(--flag-red) 33.33% 66.66%, var(--flag-yellow) 66.66%); }
        .star-icon { position: absolute; top: 50%; left: 50%; display: block; transform: translate(-50%, -50%); color: var(--flag-yellow); font-size: 2.4mm; line-height: 1; }
        .title-section { margin-bottom: .2mm; text-align: center; line-height: 1.15; }
        .title-fr { color: var(--card-accent); font-size: 4mm; font-weight: 700; }
        .title-en { color: var(--card-muted); font-size: 2.6mm; font-style: italic; }
        .body-section { min-height: 0; flex: 1; display: flex; justify-content: space-between; gap: 3mm; }
        .info-col { min-width: 0; flex: 1; display: grid; grid-template-columns: minmax(0, .75fr) minmax(0, 1.25fr); align-content: space-between; column-gap: 4mm; row-gap: 1.5mm; }
        .form-row { min-width: 0; display: flex; align-items: baseline; gap: 1.5mm; color: var(--card-text); font-size: 12px; }
        .form-label { flex: 0 0 auto; color: var(--card-text); font-weight: 700; }
        .form-value { min-width: 0; flex: 1; overflow: hidden; border-bottom: .35mm dotted var(--card-border); color: var(--card-text); font-size: inherit; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
        .year-row, .last-name-row, .split-row { grid-column: 1 / -1; }
        .year-row { max-width: 74%; }
        .matricule-row { grid-column: 2; align-items: baseline; white-space: nowrap; }
        .matricule-row .form-label { white-space: nowrap; }
        .split-row { display: grid; grid-template-columns: auto minmax(0, 1fr); align-items: baseline; gap: 1.5mm; }
        .split-row .form-label { font-size: 12px; }
        .photo-col { width: 31mm; min-height: 0; align-self: stretch; display: grid; grid-template-rows: minmax(0, 4fr) minmax(0, 1fr); overflow: hidden; border: .35mm solid var(--card-border); }
        .photo-box { min-height: 0; display: grid; place-items: center; overflow: hidden; border-bottom: .35mm solid var(--card-border); background: #ffffff30; color: var(--card-muted); font-size: 2.5mm; }
        .student-photo { display: block; width: 100%; height: 100%; min-width: 0; min-height: 0; object-fit: contain; object-position: center; image-rendering: auto; }
        .photo-class { min-width: 0; min-height: 0; display: flex; align-items: flex-start; justify-content: center; gap: .5mm; padding: .3mm; font-size: 2.5mm; text-align: center; }
        .photo-class .form-value { min-width: 0; flex: 0 1 auto; border: 0; line-height: 1.05; white-space: normal; overflow: hidden; overflow-wrap: anywhere; text-overflow: clip; }
        .tranches-section { margin-top: .3mm; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 3mm; }
        .tranche-box { min-width: 0; height: 15mm; padding: 1mm; display: flex; flex-direction: column; justify-content: space-between; border: .35mm solid var(--card-border); color: var(--card-text); text-align: center; font-size: 2.4mm; font-weight: 700; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .tranche-box:nth-child(1) { background: var(--card-tranche-1); }
        .tranche-box:nth-child(2) { background: var(--card-tranche-2); }
        .tranche-box:nth-child(3) { background: var(--card-tranche-3); }
        .tranche-line { width: 70%; margin: 0 auto 1mm; border-bottom: .3mm dotted var(--card-border); }
        .preview-mode { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .preview-mode .toolbar { display: none !important; }
        .preview-mode .sheet { aspect-ratio: auto; margin: 0; padding: 18px; box-shadow: none; }
        .preview-mode .sheet-landscape { width: 1085px; height: 756px; }
        .preview-mode .sheet-portrait { width: 774px; height: 1104px; }
        @media (max-width: 760px) { .toolbar { align-items: flex-start; flex-direction: column; } .sheet { height: auto; aspect-ratio: auto; grid-template-rows: auto auto; } .access-card { min-height: 340px; } }
        @media (max-width: 480px) { .sheet { width: 100%; margin: 0 auto 12px; padding: 10px; grid-template-columns: 1fr; grid-template-rows: auto; } .access-card { min-height: 360px; } }
        @page { size: A4 <?= htmlspecialchars($orientation, ENT_QUOTES, 'UTF-8') ?>; margin: <?= htmlspecialchars($pageMargin, ENT_QUOTES, 'UTF-8') ?>; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .sheet { aspect-ratio: auto; margin: 0; padding: 0; grid-template-columns: repeat(2, minmax(0, 1fr)); grid-template-rows: repeat(2, minmax(0, 1fr)); gap: 4mm; box-shadow: none; page-break-after: always; break-after: page; }
            .sheet-landscape { width: 287mm; height: 200mm; grid-template-rows: repeat(2, minmax(0, 1fr)); gap: 4mm; }
            .sheet-portrait { width: 205mm; height: 292mm; grid-template-rows: repeat(4, minmax(0, 1fr)); gap: 3mm; }
            .card-slot { overflow: hidden; }
            .sheet:last-child { page-break-after: auto; break-after: auto; }
            .access-card { min-height: 0; padding: 3mm; page-break-inside: avoid; break-inside: avoid; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .sheet-landscape .access-card { transform: none; }
            .sheet-portrait .access-card { transform: scale(.69); }
            .sheet, .access-card { page-break-inside: avoid; break-inside: avoid; }
            .access-card, .tranche-box, .separator { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body class="<?= $previewMode ? 'preview-mode' : '' ?>">
    <?php $printTitle = $classId > 0 ? (string) $class['nom'] : $sectionName; ?>
    <div class="toolbar">
            <h1><?= htmlspecialchars(__('access_cards_title') . ' · ' . $printTitle, ENT_QUOTES, 'UTF-8') ?></h1>
            <div class="toolbar-actions">
                <a href="/access-cards?<?= htmlspecialchars(http_build_query(['section_id' => $sectionId, 'class_id' => $classId, 'orientation' => $orientation, 'palette' => $palette]), ENT_QUOTES, 'UTF-8') ?>"><?= __('back') ?></a>
            <button type="button" onclick="window.print()"><?= __('print') ?></button>
            </div>
    </div>

    <?php foreach ($pages as $pageStudents): ?>
            <section class="sheet sheet-<?= htmlspecialchars($orientation, ENT_QUOTES, 'UTF-8') ?>" aria-label="<?= htmlspecialchars(__('access_cards_per_sheet_' . $orientation), ENT_QUOTES, 'UTF-8') ?>">
                <?php foreach ($pageStudents as $student): ?>
                    <?php
                    $photo = $imagePath($student['photo_eleve'] ?? null);
                    $studentLevel = trim((string) ($student['cycle_name'] ?? ''))
                        ?: trim((string) ($student['teaching_type_name'] ?? ''))
                        ?: $level;
                    ?>
            <div class="card-slot">
            <article class="access-card" style="<?= htmlspecialchars(\App\Services\AccessCardPalette::cssVariables($palette), ENT_QUOTES, 'UTF-8') ?>">
                    <header class="header-top">
                        <div class="header-left">
                            <div class="header-strong"><?= htmlspecialchars($republic, ENT_QUOTES, 'UTF-8') ?></div>
                            <div><?= htmlspecialchars($nationalMotto, ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="stars">*********</div>
                            <div><?= htmlspecialchars($ministry, ENT_QUOTES, 'UTF-8') ?></div>
                            <?php if ($delegationFr !== ''): ?>
                                <div><?= $formatDelegation($delegationFr) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="logo-container">
                            <?php if ($schoolLogo !== ''): ?>
                                <img class="school-logo" src="<?= htmlspecialchars($imagePath($schoolLogo), ENT_QUOTES, 'UTF-8') ?>" alt="">
                            <?php else: ?>
                                <div class="logo-placeholder" aria-hidden="true"><?= htmlspecialchars(function_exists('mb_substr') ? mb_substr($schoolName, 0, 1, 'UTF-8') : substr($schoolName, 0, 1), ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="header-right">
                            <div class="header-strong">Republic of Cameroon</div>
                            <div>Peace - Work - Fatherland</div>
                            <div class="stars">*********</div>
                            <div>Ministry of Secondary Education</div>
                            <?php if ($delegationEn !== ''): ?>
                                <div><?= $formatDelegation($delegationEn) ?></div>
                            <?php endif; ?>
                        </div>
                    </header>

                    <div class="header-center-text">
                        <div class="school-type"><?= htmlspecialchars(function_exists('mb_strtoupper') ? mb_strtoupper($schoolName, 'UTF-8') : strtoupper($schoolName), ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="school-type-en"><?= htmlspecialchars($schoolSlogan, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php if ($addressLine !== ''): ?>
                            <div class="cycle"><?= htmlspecialchars($addressLine, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                        <?php if ($contactLine !== ''): ?>
                            <div class="contact"><?= htmlspecialchars($contactLine, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="separator"><span class="star-icon">&#9733;</span></div>
                    <div class="title-section">
                        <div class="title-fr"><?= htmlspecialchars(__('access_card'), ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="title-en">(Access Card)</div>
                    </div>

                    <div class="body-section">
                        <div class="info-col">
                            <div class="form-row year-row"><span class="form-label"><?= __('access_card_school_year') ?> :</span><span class="form-value"><?= htmlspecialchars((string) $activeYear['nom'], ENT_QUOTES, 'UTF-8') ?></span></div>
                            <div class="form-row last-name-row"><span class="form-label"><?= __('access_card_last_name') ?> :</span><span class="form-value"><?= htmlspecialchars((string) ($student['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                            <div class="form-row first-name-row"><span class="form-label"><?= __('access_card_first_name') ?> :</span><span class="form-value"><?= htmlspecialchars((string) ($student['prenom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></div>
                            <div class="form-row matricule-row"><span class="form-label"><?= __('matricule') ?> :</span><span class="form-value"><?= htmlspecialchars((string) (($student['email'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?></span></div>
                            <div class="form-row split-row"><span class="form-label"><?= __('access_card_school_level') ?> &amp; <?= __('access_card_cycle') ?> :</span><span class="form-value"><?= htmlspecialchars($studentLevel !== '' ? $studentLevel : '-', ENT_QUOTES, 'UTF-8') ?></span></div>
                        </div>
                        <div class="photo-col">
                            <div class="photo-box">
                                <?php if ($photo !== ''): ?>
                                    <img class="student-photo" src="<?= htmlspecialchars($photo, ENT_QUOTES, 'UTF-8') ?>" alt="">
                                <?php else: ?>
                                    <span><?= __('access_card_no_photo') ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="photo-class"><span class="form-label"><?= __('class') ?> :</span><span class="form-value"><?= htmlspecialchars((string) ($student['class_name'] ?? $class['nom']), ENT_QUOTES, 'UTF-8') ?></span></div>
                        </div>
                    </div>

                    <footer class="tranches-section">
                        <?php for ($tranche = 1; $tranche <= 3; $tranche++): ?>
                            <div class="tranche-box"><span><?= __('access_card_installment') ?> <?= $tranche ?></span><span class="tranche-line"></span></div>
                        <?php endfor; ?>
                    </footer>
                </article>
                </div>
            <?php endforeach; ?>
            <?php for ($emptySlot = count($pageStudents); $emptySlot < $cardsPerPage; $emptySlot++): ?>
                <div class="card-slot"><article class="access-card" style="<?= htmlspecialchars(\App\Services\AccessCardPalette::cssVariables($palette), ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></article></div>
            <?php endfor; ?>
        </section>
    <?php endforeach; ?>
</body>
</html>