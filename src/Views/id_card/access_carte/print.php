<?php
$lang = \App\Core\Locale::get();
$schoolName = trim((string) ($institution['school_name'] ?? '')) ?: 'NotesMaster';
$defaultRepublic = $lang === 'en' ? 'Republic of Cameroon' : 'République du Cameroun';
$defaultMinistry = $lang === 'en' ? 'Ministry of Secondary Education' : 'Ministère des Enseignements Secondaires';
$nationalMotto = $lang === 'en' ? 'Peace - Work - Fatherland' : 'Paix - Travail - Patrie';
$republic = trim((string) ($institution[$lang === 'en' ? 'school_republic_en' : 'school_republic'] ?? ''));
$combinedRepublic = '/R[eé]publique\s+du\s+Cameroun\s*Republic(?:\s+of\s+Cameroon)?/iu';
if ($republic === '' || preg_match($combinedRepublic, $republic)) {
    $republic = $defaultRepublic;
}
$ministry = trim((string) ($institution[$lang === 'en' ? 'school_ministry_en' : 'school_ministry'] ?? '')) ?: $defaultMinistry;
$schoolSlogan = trim((string) ($institution[$lang === 'en' ? 'school_slogan_en' : 'school_slogan'] ?? ''))
    ?: ($lang === 'en' ? 'Discipline - Work - Success' : 'Discipline - Travail - Succès');
$phone = trim((string) ($institution['school_phone'] ?? ''));
$address = trim((string) ($institution['school_address'] ?? ''));
$city = trim((string) ($institution['school_city'] ?? ''));
$poBox = trim((string) ($institution['school_po_box'] ?? ''));
$email = trim((string) ($institution['school_email'] ?? ''));
$address = $address !== '' ? $address : $city;
$schoolLogo = trim((string) ($institution['school_logo'] ?? ''));

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

$formatDate = static function (?string $date): string {
    if (!$date || $date === '0000-00-00') {
        return '-';
    }
    $timestamp = strtotime($date);
    return $timestamp === false ? '-' : date('d/m/Y', $timestamp);
};
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(__('access_cards_title') . ' - ' . $schoolName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #e8edf0; color: #17232a; font-family: Arial, sans-serif; }
        .toolbar { position: sticky; top: 0; z-index: 5; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 20px; background: #173c34; color: #fff; }
        .toolbar h1 { margin: 0; font-size: 15px; }
        .toolbar-actions { display: flex; gap: 8px; }
        .toolbar a, .toolbar button { display: inline-flex; align-items: center; justify-content: center; min-height: 36px; padding: 0 14px; border: 0; border-radius: 4px; color: #173c34; background: #fff; font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; }
        .toolbar button { background: #f2c94c; }
        .sheet { width: min(100% - 32px, 1123px); min-height: 756px; margin: 18px auto; padding: 30px; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); grid-auto-rows: max-content; align-content: center; justify-content: center; gap: 12px; background: #fff; box-shadow: 0 8px 28px #17232a1a; page-break-after: always; }
        .sheet:last-child { page-break-after: auto; }
        .access-card { position: relative; min-width: 0; width: 100%; aspect-ratio: 85.6 / 54; overflow: hidden; border: 1px solid #aab7b5; border-radius: 2.5mm; background: #fff; box-shadow: 0 2px 6px #17232a12; }
        .flag-line { height: 2.2mm; background: linear-gradient(to right, #087b55 0 33.33%, #c82732 33.33% 66.66%, #f0c735 66.66%); }
        .card-inner { height: calc(100% - 2.2mm); padding: 2mm 2.5mm 1.5mm; display: flex; flex-direction: column; }
        .card-head { display: grid; grid-template-columns: minmax(0, 1fr) 11mm minmax(0, 1fr); align-items: center; gap: 1.2mm; min-height: 10mm; padding-bottom: 1.2mm; border-bottom: .25mm solid #d9e1df; }
        .head-government, .head-contact { min-width: 0; display: flex; flex-direction: column; gap: .65mm; color: #53635e; font-size: 1.55mm; line-height: 1.15; }
        .head-government { text-align: center; }
        .head-contact { text-align: right; }
        .head-line { overflow-wrap: anywhere; }
        .head-line-strong { color: #173c34; font-size: 1.65mm; font-weight: 800; text-transform: uppercase; }
        .head-national-motto { font-size: 1.45mm; font-style: italic; }
        .head-logo { display: flex; align-items: center; justify-content: center; }
        .school-logo, .school-mark { width: 10mm; height: 10mm; object-fit: contain; }
        .school-mark { display: grid; place-items: center; border: .6mm solid #087b55; border-radius: 50%; color: #087b55; font-size: 4mm; font-weight: 900; }
        .school-branding { padding: 1.1mm 1mm .4mm; text-align: center; }
        .school-name { max-width: 100%; color: #173c34; font-size: 2.65mm; font-weight: 900; line-height: 1.08; overflow-wrap: anywhere; text-align: center; text-transform: uppercase; }
        .motto { margin-top: .5mm; color: #75817f; font-size: 1.6mm; font-style: italic; }
        .card-title { margin: 1.1mm 0; color: #c82732; font-size: 2.2mm; font-weight: 800; text-align: center; text-decoration: underline; text-underline-offset: .5mm; text-transform: uppercase; }
        .card-body { display: grid; grid-template-columns: 16mm minmax(0, 1fr); gap: 2mm; min-height: 20mm; flex: 1 0 20mm; }
        .student-photo { width: 16mm; height: 20mm; align-self: center; overflow: hidden; border: .35mm solid #d1dad8; background: #f1f5f4; object-fit: cover; }
        .photo-fallback { display: grid; place-items: center; color: #78908a; font-size: 2mm; font-weight: 700; text-transform: uppercase; }
        .student-info { min-width: 0; display: grid; grid-template-columns: minmax(0, 1fr) 15mm; align-items: center; gap: 1.5mm; }
        .student-details { min-width: 0; display: flex; flex-direction: column; justify-content: center; gap: .8mm; }
        .student-qr { display: block; width: 15mm; height: 15mm; padding: .5mm; border: .25mm solid #d9e1df; background: #fff; image-rendering: crisp-edges; }
        .student-name { overflow: hidden; color: #14231f; font-size: 3.3mm; font-weight: 900; line-height: 1.05; text-overflow: ellipsis; text-transform: uppercase; white-space: nowrap; }
        .student-first-name { overflow: hidden; color: #344540; font-size: 2.6mm; font-weight: 700; text-overflow: ellipsis; text-transform: uppercase; white-space: nowrap; }
        .detail-row { display: flex; align-items: baseline; gap: 1mm; min-width: 0; font-size: 2.1mm; line-height: 1.15; }
        .detail-label { flex: 0 0 auto; color: #6d7976; font-size: 1.8mm; font-weight: 700; text-transform: uppercase; }
        .detail-value { min-width: 0; overflow: hidden; color: #263a34; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
        .card-foot { display: flex; align-items: center; justify-content: space-between; gap: 2mm; margin-top: 1mm; padding-top: 1mm; border-top: .25mm solid #d9e1df; }
        .matricule-label { color: #6d7976; font-size: 1.5mm; font-weight: 700; text-transform: uppercase; }
        .matricule { color: #173c34; font-family: monospace; font-size: 2.25mm; font-weight: 800; }
        .year-badge { max-width: 48%; overflow: hidden; padding: .7mm 1.4mm; border-radius: 1mm; background: #eaf2ee; color: #173c34; font-size: 1.8mm; font-weight: 800; text-overflow: ellipsis; white-space: nowrap; }
        @media (max-width: 760px) {
            .toolbar { align-items: flex-start; flex-direction: column; }
            .sheet { min-height: auto; grid-template-columns: repeat(2, minmax(0, 1fr)); padding: 16px; }
        }
        @media (max-width: 480px) { .sheet { grid-template-columns: 1fr; } }
        @page { size: A4 landscape; margin: 5mm; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .sheet { width: 287mm; height: 200mm; min-height: 0; margin: 0; padding: 0; grid-template-columns: repeat(3, 85.6mm); grid-template-rows: repeat(3, 54mm); grid-auto-rows: 54mm; align-content: center; justify-content: center; gap: 2mm; box-shadow: none; page-break-after: always; break-after: page; }
            .sheet:last-child { page-break-after: auto; break-after: auto; }
            .access-card { width: 85.6mm; height: 54mm; aspect-ratio: auto; border-radius: 1.2mm; box-shadow: none; -webkit-print-color-adjust: exact; print-color-adjust: exact; page-break-inside: avoid; break-inside: avoid; }
            .flag-line, .year-badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <h1><?= htmlspecialchars(__('access_cards_title') . ' · ' . $class['nom'], ENT_QUOTES, 'UTF-8') ?></h1>
        <div class="toolbar-actions">
            <a href="/access-cards?<?= htmlspecialchars(http_build_query(['academic_year_id' => $activeYear['id'], 'teaching_type_id' => $teachingTypeId, 'class_id' => $class['id']]), ENT_QUOTES, 'UTF-8') ?>"><?= __('back') ?></a>
            <button type="button" onclick="window.print()"><?= __('print') ?></button>
        </div>
    </div>

    <?php foreach ($pages as $pageStudents): ?>
        <section class="sheet" aria-label="<?= htmlspecialchars(__('access_cards_per_sheet'), ENT_QUOTES, 'UTF-8') ?>">
            <?php foreach ($pageStudents as $student): ?>
                <?php
                $photo = $imagePath($student['photo_eleve'] ?? null);
                $contact = trim((string) ($student['parent_contact'] ?? ''));
                if ($contact === '') {
                    $contact = trim((string) ($student['guardian_contact'] ?? ''));
                }
                $qrToken = bin2hex(random_bytes(16));
                $qrCode = \Endroid\QrCode\QrCode::create($qrToken)->setSize(320)->setMargin(1);
                $qrDataUri = (new \Endroid\QrCode\Writer\PngWriter())->write($qrCode)->getDataUri();
                ?>
                <article class="access-card">
                    <div class="flag-line"></div>
                    <div class="card-inner">
                        <header class="card-head">
                            <div class="head-government">
                                <div class="head-line head-line-strong"><?= htmlspecialchars($republic, ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="head-line head-national-motto"><?= htmlspecialchars($nationalMotto, ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="head-line"><?= htmlspecialchars($ministry, ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                            <div class="head-logo">
                                <?php if ($schoolLogo !== ''): ?>
                                    <img class="school-logo" src="<?= htmlspecialchars($imagePath($schoolLogo), ENT_QUOTES, 'UTF-8') ?>" alt="">
                                <?php else: ?>
                                    <div class="school-mark" aria-hidden="true"><?= htmlspecialchars(function_exists('mb_substr') ? mb_substr($schoolName, 0, 1, 'UTF-8') : substr($schoolName, 0, 1), ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="head-contact">
                                <?php if ($address !== ''): ?><div class="head-line"><?= htmlspecialchars($address, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                                <?php if ($poBox !== ''): ?><div class="head-line"><?= htmlspecialchars(($lang === 'en' ? 'P.O. Box ' : 'BP ') . $poBox, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                                <?php if ($phone !== ''): ?><div class="head-line"><?= htmlspecialchars(($lang === 'en' ? 'Tel: ' : 'Tél: ') . $phone, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                                <?php if ($email !== ''): ?><div class="head-line"><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>
                        </header>
                        <div class="school-branding">
                            <div class="school-name"><?= htmlspecialchars($schoolName, ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="motto"><?= htmlspecialchars($schoolSlogan, ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                        <div class="card-title"><?= __('access_card') ?></div>
                        <div class="card-body">
                            <?php if ($photo !== ''): ?>
                                <img class="student-photo" src="<?= htmlspecialchars($photo, ENT_QUOTES, 'UTF-8') ?>" alt="">
                            <?php else: ?>
                                <div class="student-photo photo-fallback"><?= __('access_card_no_photo') ?></div>
                            <?php endif; ?>
                            <div class="student-info">
                                <div class="student-details">
                                    <div class="student-name"><?= htmlspecialchars((string) ($student['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="student-first-name"><?= htmlspecialchars((string) ($student['prenom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="detail-row"><span class="detail-label"><?= __('access_card_birth') ?></span><span class="detail-value"><?= htmlspecialchars($formatDate($student['date_naissance'] ?? null), ENT_QUOTES, 'UTF-8') ?></span></div>
                                    <div class="detail-row"><span class="detail-label"><?= __('access_card_birth_place') ?></span><span class="detail-value"><?= htmlspecialchars((string) (($student['lieu_naissance'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?></span></div>
                                    <div class="detail-row"><span class="detail-label"><?= __('access_card_sex') ?></span><span class="detail-value"><?= htmlspecialchars((string) (($student['sexe'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?></span><span class="detail-label"><?= __('class') ?></span><span class="detail-value"><?= htmlspecialchars((string) $student['class_name'], ENT_QUOTES, 'UTF-8') ?></span></div>
                                    <div class="detail-row"><span class="detail-label"><?= __('access_card_contact') ?></span><span class="detail-value"><?= htmlspecialchars($contact !== '' ? $contact : '-', ENT_QUOTES, 'UTF-8') ?></span></div>
                                </div>
                                <img class="student-qr" src="<?= htmlspecialchars($qrDataUri, ENT_QUOTES, 'UTF-8') ?>" alt="QR code de la carte">
                            </div>
                        </div>
                        <footer class="card-foot">
                            <div><div class="matricule-label"><?= __('matricule') ?></div><div class="matricule"><?= htmlspecialchars((string) (($student['email'] ?? '') ?: '-'), ENT_QUOTES, 'UTF-8') ?></div></div>
                            <div class="year-badge"><?= htmlspecialchars((string) $activeYear['nom'], ENT_QUOTES, 'UTF-8') ?></div>
                        </footer>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endforeach; ?>
</body>
</html>