<?php
$lang = \App\Core\Locale::get();
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$previewMode = (string) ($_GET['preview'] ?? '') === '1';
$pageMargin = $orientation === 'portrait' ? '2.5mm' : '5mm';
$title = __('student_passes_title');
$printTitle = $title . ' · ' . count($printCards);
?>
<!DOCTYPE html>
<html lang="<?= $escape($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $escape($printTitle) ?></title>
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; color: #172033; font-family: Arial, sans-serif; }
        body { background: #e9edf3; }
        .toolbar { position: sticky; top: 0; z-index: 5; display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 12px 18px; background: #172033; color: #fff; }
        .toolbar h1 { margin: 0; font-size: 15px; }
        .toolbar-actions { display: flex; gap: 8px; }
        .toolbar a, .toolbar button { display: inline-flex; align-items: center; justify-content: center; min-height: 36px; padding: 0 14px; border: 0; border-radius: 4px; background: #fff; color: #172033; font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; }
        .toolbar button { background: #7c3aed; color: #fff; }
        .pass-sheet { width: min(100% - 32px, 1123px); aspect-ratio: 297 / 210; margin: 18px auto; padding: 18px; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); grid-template-rows: repeat(2, minmax(0, 1fr)); gap: 12px; background: #fff; box-shadow: 0 8px 28px #17232a1a; page-break-after: always; break-after: page; }
        .pass-sheet.orientation-portrait { width: min(100% - 32px, 794px); aspect-ratio: 210 / 297; grid-template-rows: repeat(4, minmax(0, 1fr)); }
        .pass-sheet:last-child { page-break-after: auto; break-after: auto; }
        .pass-slot { min-width: 0; min-height: 0; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .pass-card { position: relative; flex: none; width: 141.5mm; height: 98mm; min-width: 0; min-height: 0; padding: 3mm; display: flex; flex-direction: column; overflow: hidden; border: 1px dashed var(--card-border); box-shadow: inset 0 0 0 2px var(--card-border); background: var(--card-background); color: var(--card-text); }
        .pass-sheet.orientation-portrait .pass-card { transform: scale(.69); }
        .pass-card-content { position: relative; z-index: 1; display: flex; width: 100%; height: 100%; min-height: 0; flex-direction: column; gap: 2mm; }
        .pass-heading { display: flex; align-items: center; justify-content: center; gap: 3mm; padding: 0 1mm 2mm; border-bottom: .5mm solid var(--card-border); text-align: center; }
        .pass-school-logo { flex: 0 0 15mm; width: 15mm; height: 15mm; object-fit: contain; }
        .pass-heading-text { min-width: 0; flex: 1; }
        .pass-school { max-width: 100%; margin: 0 auto; color: var(--card-text); font-size: clamp(3.5mm, 1.7vw, 6mm); font-weight: 900; line-height: 1.12; overflow-wrap: anywhere; text-align: center; text-transform: uppercase; word-break: break-word; }
        .pass-document-group { flex: 0 0 auto; text-align: center; }
        .pass-document { display: inline-block; margin-top: 2mm; padding: 1mm 3mm; background: var(--card-accent); color: var(--card-background); font-size: clamp(6mm, 3vw, 11mm); font-weight: 900; letter-spacing: .04em; line-height: 1.1; text-align: center; }
        .pass-document-en { margin-top: 1mm; color: var(--card-muted); font-size: clamp(2.5mm, 1vw, 4mm); font-style: italic; font-weight: 700; }
        .pass-class { display: flex; min-height: 0; flex: 1; align-items: center; justify-content: center; color: var(--card-text); font-size: clamp(8mm, 4vw, 16mm); font-weight: 900; line-height: 1.05; overflow-wrap: anywhere; text-align: center; word-break: break-word; }
        .pass-duration { margin: auto 0 0; padding-top: 2mm; border-top: .4mm solid var(--card-border); color: var(--card-text); font-size: clamp(3.5mm, 1.5vw, 5mm); font-weight: 800; text-align: center; }
        .pass-duration-en { display: block; margin-top: .7mm; color: var(--card-muted); font-size: .8em; font-style: italic; font-weight: 700; }
        .preview-mode { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .preview-mode .toolbar { display: none !important; }
        .preview-mode .pass-sheet { aspect-ratio: auto; margin: 0; padding: 18px; box-shadow: none; }
        .preview-mode .pass-sheet.orientation-landscape { width: 1085px; height: 756px; }
        .preview-mode .pass-sheet.orientation-portrait { width: 774px; height: 1104px; }
        @page { size: A4 <?= $escape($orientation) ?>; margin: <?= $escape($pageMargin) ?>; }
        @media print {
            html, body { width: 100%; height: auto; background: #fff; }
            .toolbar { display: none !important; }
            .pass-sheet { aspect-ratio: auto; margin: 0; padding: 0; grid-template-columns: repeat(2, minmax(0, 1fr)); grid-template-rows: repeat(2, minmax(0, 1fr)); gap: 4mm; box-shadow: none; page-break-after: always; break-after: page; }
            .pass-sheet.orientation-landscape { width: 287mm; height: 200mm; }
            .pass-sheet.orientation-portrait { width: 205mm; height: 292mm; grid-template-rows: repeat(4, minmax(0, 1fr)); gap: 3mm; }
            .pass-sheet:last-child { page-break-after: auto; break-after: auto; }
            .pass-slot { overflow: hidden; }
            .pass-card { page-break-inside: avoid; break-inside: avoid; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .pass-sheet.orientation-landscape .pass-card { transform: none; }
            .pass-sheet.orientation-portrait .pass-card { transform: scale(.69); }
            .pass-sheet, .pass-card { page-break-inside: avoid; break-inside: avoid; }
        }
        @media screen and (max-width: 640px) {
            .toolbar { align-items: flex-start; flex-direction: column; }
            .pass-sheet { width: 100%; margin: 0 auto 12px; }
        }
    </style>
</head>
<body class="<?= $previewMode ? 'preview-mode' : '' ?>">
    <div class="toolbar">
        <h1><?= $escape($printTitle) ?></h1>
        <div class="toolbar-actions">
            <a href="/student-passes?<?= $escape(http_build_query([
                'section_id' => $sectionId,
                'cycle_id' => $cycleId,
                'class_id' => $classId,
                'orientation' => $orientation,
                'palette' => $palette,
            ])) ?>"><?= $escape(__('back')) ?></a>
            <button type="button" onclick="window.print()"><?= $escape(__('print')) ?></button>
        </div>
    </div>
    <?php $pagesToRender = $previewMode ? array_slice($printPages, 0, 1) : $printPages; ?>
    <?php foreach ($pagesToRender as $printPage): ?>
        <section class="pass-sheet orientation-<?= $escape($orientation) ?>" aria-label="<?= $escape(__('access_cards_per_sheet_' . $orientation)) ?>">
            <?php foreach ($printPage as $printCard): ?>
                <?php
                $class = $printCard['class'];
                $institution = $printCard['institution'];
                $schoolName = trim((string) ($institution['school_name'] ?? '')) ?: 'NotesMaster';
                $logoBase64 = (string) ($printCard['logo_base64'] ?? '');
                ?>
                <div class="pass-slot">
                    <article class="pass-card" style="<?= $escape(\App\Services\AccessCardPalette::cssVariables($palette)) ?>">
                        <div class="pass-card-content">
                            <header class="pass-heading">
                                <?php if ($logoBase64 !== ''): ?>
                                    <img class="pass-school-logo" src="<?= $escape($logoBase64) ?>" alt="">
                                <?php endif; ?>
                                <div class="pass-heading-text">
                                    <div class="pass-school"><?= $escape($schoolName) ?></div>
                                </div>
                            </header>
                            <div class="pass-document-group">
                                <div class="pass-document"><?= $escape(__('student_passes_document_fr')) ?></div>
                                <div class="pass-document-en"><?= $escape(__('student_passes_document_en')) ?></div>
                            </div>
                            <div class="pass-class"><?= $escape((string) $class['nom']) ?></div>
                            <p class="pass-duration">
                                <?= $escape(__('student_passes_duration_fr')) ?>
                                <span class="pass-duration-en"><?= $escape(__('student_passes_duration_en')) ?></span>
                            </p>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
            <?php for ($emptySlot = count($printPage); $emptySlot < $cardsPerPage; $emptySlot++): ?>
                <div class="pass-slot"><article class="pass-card" style="<?= $escape(\App\Services\AccessCardPalette::cssVariables($palette)) ?>" aria-hidden="true"></article></div>
            <?php endfor; ?>
        </section>
    <?php endforeach; ?>
</body>
</html>
