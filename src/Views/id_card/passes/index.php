<?php
$title = __('student_passes_title');
$paletteDefaults = \App\Services\AccessCardPalette::defaults();
$paletteKeys = ['background', 'accent', 'border', 'text', 'muted'];
$paletteLabels = [
    'background' => __('access_card_palette_background'),
    'accent' => __('access_card_palette_accent'),
    'border' => __('access_card_palette_border'),
    'text' => __('access_card_palette_text'),
    'muted' => __('access_card_palette_muted'),
];
$hasFilter = $sectionId > 0 || $cycleId > 0 || $classId > 0;
ob_start();
?>
<div class="animate-fade-in admin-analytics module-bureau-flow">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h4 fw-bold text-main-theme mb-1"><?= htmlspecialchars(__('student_passes_title'), ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="text-muted mb-0"><?= htmlspecialchars(__('student_passes_description'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 px-3 py-2">
            <i class="bi bi-file-earmark-text me-1" aria-hidden="true"></i><?= htmlspecialchars(__('access_cards_per_sheet_' . $orientation), ENT_QUOTES, 'UTF-8') ?>
        </span>
    </div>

    <div class="modern-card border-0 shadow-sm mb-4">
        <div class="modern-card-body p-3 p-lg-4">
            <form method="GET" action="/student-passes" class="row g-3 align-items-end">
                <input type="hidden" name="orientation" value="<?= htmlspecialchars($orientation, ENT_QUOTES, 'UTF-8') ?>">
                <div class="col-12 col-md-4">
                    <label for="pass_section_id" class="form-label fw-semibold"><?= htmlspecialchars(__('access_cards_section_label'), ENT_QUOTES, 'UTF-8') ?></label>
                    <select id="pass_section_id" name="section_id" class="form-select" onchange="this.form.requestSubmit()">
                        <option value="0"><?= htmlspecialchars(__('access_cards_choose_section'), ENT_QUOTES, 'UTF-8') ?></option>
                        <?php foreach ($sections as $section): ?>
                            <option value="<?= (int) $section['id'] ?>" <?= $sectionId === (int) $section['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $section['nom'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label for="pass_cycle_id" class="form-label fw-semibold"><?= htmlspecialchars(__('cycle'), ENT_QUOTES, 'UTF-8') ?></label>
                    <select id="pass_cycle_id" name="cycle_id" class="form-select" onchange="this.form.requestSubmit()">
                        <option value="0"><?= htmlspecialchars(__('choose_cycle'), ENT_QUOTES, 'UTF-8') ?></option>
                        <?php foreach ($cycles as $cycle): ?>
                            <option value="<?= (int) $cycle['id'] ?>" <?= $cycleId === (int) $cycle['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $cycle['nom'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label for="pass_class_id" class="form-label fw-semibold"><?= htmlspecialchars(__('class'), ENT_QUOTES, 'UTF-8') ?></label>
                    <select id="pass_class_id" name="class_id" class="form-select" onchange="this.form.requestSubmit()">
                        <option value="0"><?= htmlspecialchars(__('choose_class'), ENT_QUOTES, 'UTF-8') ?></option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= (int) $class['id'] ?>" <?= $classId === (int) $class['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $class['nom'], ENT_QUOTES, 'UTF-8') ?>
                                <?php if (!empty($class['section_name'])): ?> — <?= htmlspecialchars((string) $class['section_name'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
                                <?php if (!empty($class['cycle_name'])): ?> · <?= htmlspecialchars((string) $class['cycle_name'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($sections)): ?>
        <div class="alert alert-light border d-flex align-items-center gap-3 mb-0" role="status">
            <i class="bi bi-info-circle fs-4 text-primary" aria-hidden="true"></i>
            <span><?= htmlspecialchars(__('access_cards_sections_empty'), ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php elseif (!$hasFilter): ?>
        <div class="alert alert-light border d-flex align-items-center gap-3 mb-0" role="status">
            <i class="bi bi-arrow-up-circle fs-4 text-primary" aria-hidden="true"></i>
            <span><?= htmlspecialchars(__('student_passes_choose_class_hint'), ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php elseif (empty($classes)): ?>
        <div class="alert alert-light border d-flex align-items-center gap-3 mb-0" role="status">
            <i class="bi bi-info-circle fs-4 text-primary" aria-hidden="true"></i>
            <span><?= htmlspecialchars(__('student_passes_no_classes'), ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php else: ?>
        <div class="modern-card border-0 shadow-sm">
            <div class="modern-card-body p-3 p-lg-4">
                <form method="GET" action="/student-passes/print" target="_blank" class="row g-3 align-items-end" id="passPrintForm">
                    <input type="hidden" name="section_id" value="<?= (int) $sectionId ?>">
                    <input type="hidden" name="cycle_id" value="<?= (int) $cycleId ?>">
                    <input type="hidden" name="class_id" value="<?= (int) $classId ?>">
                    <fieldset class="col-12">
                        <legend class="form-label fw-semibold mb-2"><?= htmlspecialchars(__('access_card_orientation'), ENT_QUOTES, 'UTF-8') ?></legend>
                        <div class="d-flex flex-wrap gap-3">
                            <?php foreach (['portrait', 'landscape'] as $printOrientation): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="orientation" id="pass_orientation_<?= $printOrientation ?>" value="<?= $printOrientation ?>" <?= $orientation === $printOrientation ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="pass_orientation_<?= $printOrientation ?>"><?= htmlspecialchars(__('access_card_orientation_' . $printOrientation), ENT_QUOTES, 'UTF-8') ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                    <section class="col-12" aria-labelledby="passPaletteTitle">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <div>
                                <h2 id="passPaletteTitle" class="h6 fw-bold mb-1"><?= htmlspecialchars(__('access_card_palette_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                                <p class="small text-muted mb-0"><?= htmlspecialchars(__('access_card_palette_description'), ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="resetPassPalette">
                                <i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i><?= htmlspecialchars(__('access_card_palette_reset'), ENT_QUOTES, 'UTF-8') ?>
                            </button>
                        </div>
                        <div class="row g-2">
                            <?php foreach ($paletteKeys as $key): ?>
                                <div class="col-6 col-md-3">
                                    <label for="pass_palette_<?= $key ?>" class="form-label small fw-semibold mb-1"><?= htmlspecialchars($paletteLabels[$key], ENT_QUOTES, 'UTF-8') ?></label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" class="form-control form-control-color pass-palette-color" id="pass_palette_<?= $key ?>" name="palette[<?= $key ?>]" value="<?= htmlspecialchars($palette[$key], ENT_QUOTES, 'UTF-8') ?>" data-default="<?= htmlspecialchars($paletteDefaults[$key], ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars($paletteLabels[$key], ENT_QUOTES, 'UTF-8') ?>">
                                        <output class="font-monospace small" data-pass-color-value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($palette[$key], ENT_QUOTES, 'UTF-8') ?></output>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="row g-2 align-items-end mt-2">
                            <div class="col-12 col-md-5">
                                <label for="passPaletteName" class="form-label small fw-semibold mb-1"><?= htmlspecialchars(__('student_passes_palette_name'), ENT_QUOTES, 'UTF-8') ?></label>
                                <input type="text" class="form-control" id="passPaletteName" maxlength="60" autocomplete="off" placeholder="<?= htmlspecialchars(__('student_passes_palette_name_placeholder'), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="savedPassPalettes" class="form-label small fw-semibold mb-1"><?= htmlspecialchars(__('student_passes_saved_palettes'), ENT_QUOTES, 'UTF-8') ?></label>
                                <select class="form-select" id="savedPassPalettes">
                                    <option value=""><?= htmlspecialchars(__('student_passes_palette_choose'), ENT_QUOTES, 'UTF-8') ?></option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3 d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary flex-fill" id="savePassPalette"><?= htmlspecialchars(__('student_passes_palette_save'), ENT_QUOTES, 'UTF-8') ?></button>
                                <button type="button" class="btn btn-outline-danger" id="deletePassPalette" aria-label="<?= htmlspecialchars(__('student_passes_palette_delete'), ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars(__('student_passes_palette_delete'), ENT_QUOTES, 'UTF-8') ?>" disabled>
                                    <i class="bi bi-trash" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div class="col-12">
                                <p class="form-text mb-0"><?= htmlspecialchars(__('student_passes_palette_storage_hint'), ENT_QUOTES, 'UTF-8') ?></p>
                                <div id="passPaletteStatus" class="small mt-1" role="status" aria-live="polite"></div>
                            </div>
                        </div>
                        <div class="row g-3 align-items-center mt-1">
                            <div class="col-12 col-lg-8">
                                <h3 class="small fw-bold mb-2"><?= htmlspecialchars(__('access_card_palette_preview'), ENT_QUOTES, 'UTF-8') ?></h3>
                                <div class="pass-preview-stage" id="passPreviewStage">
                                    <iframe id="passPreview" title="<?= htmlspecialchars(__('access_card_palette_preview'), ENT_QUOTES, 'UTF-8') ?>" loading="lazy"></iframe>
                                </div>
                            </div>
                        </div>
                    </section>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-printer-fill" aria-hidden="true"></i><?= htmlspecialchars(__('print'), ENT_QUOTES, 'UTF-8') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>
<style>
    .pass-preview-stage { position: relative; width: 100%; aspect-ratio: 297 / 210; overflow: hidden; border: 1px solid var(--bs-border-color, #dee2e6); background: #fff; }
    #passPreview { position: absolute; top: 0; left: 0; width: 1123px; height: 794px; border: 0; transform-origin: top left; }
</style>
<script>
(() => {
    const printForm = document.getElementById('passPrintForm');
    const preview = document.getElementById('passPreview');
    const previewStage = document.getElementById('passPreviewStage');
    if (!printForm || !preview || !previewStage) return;

    const colorInputs = [...printForm.querySelectorAll('.pass-palette-color')];
    const paletteNameInput = document.getElementById('passPaletteName');
    const savedPalettesSelect = document.getElementById('savedPassPalettes');
    const savePaletteButton = document.getElementById('savePassPalette');
    const deletePaletteButton = document.getElementById('deletePassPalette');
    const paletteStatus = document.getElementById('passPaletteStatus');
    const paletteKeys = colorInputs.map((input) => input.id.replace('pass_palette_', ''));
    const paletteStorageKey = <?= json_encode(
        'notemaster.studentPassPalettes.v1.' . (int) \App\Core\Session::get('user_id', 0),
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ) ?>;
    let savedPalettes = [];
    let previewTimer;

    const showPaletteStatus = (message, isError = false) => {
        paletteStatus.textContent = message;
        paletteStatus.classList.toggle('text-danger', isError);
        paletteStatus.classList.toggle('text-success', !isError && message !== '');
    };

    const currentPalette = () => Object.fromEntries(
        colorInputs.map((input) => [input.id.replace('pass_palette_', ''), input.value.toUpperCase()])
    );

    const isValidPalette = (palette) => paletteKeys.every((key) =>
        typeof palette?.[key] === 'string' && /^#[0-9a-f]{6}$/i.test(palette[key])
    );

    const readSavedPalettes = () => {
        const stored = localStorage.getItem(paletteStorageKey);
        if (stored === null) return [];

        const parsed = JSON.parse(stored);
        if (!Array.isArray(parsed)) throw new Error('invalid_palette_data');

        return parsed.filter((preset) =>
            preset
            && typeof preset.name === 'string'
            && preset.name.trim() !== ''
            && isValidPalette(preset.palette)
        );
    };

    const renderSavedPalettes = (selectedIndex = '') => {
        savedPalettesSelect.replaceChildren(new Option(
            <?= json_encode(__('student_passes_palette_choose'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            ''
        ));
        savedPalettes.forEach((preset, index) => {
            savedPalettesSelect.add(new Option(preset.name, String(index)));
        });
        savedPalettesSelect.value = selectedIndex;
        deletePaletteButton.disabled = selectedIndex === '';
    };

    const persistSavedPalettes = () => {
        localStorage.setItem(paletteStorageKey, JSON.stringify(savedPalettes));
    };

    const updatePreview = () => {
        for (const input of colorInputs) {
            const key = input.id.replace('pass_palette_', '');
            const output = printForm.querySelector(`[data-pass-color-value="${key}"]`);
            if (output) output.value = input.value.toUpperCase();
        }

        const selectedOrientation = printForm.querySelector('[name="orientation"]:checked')?.value || 'landscape';
        const dimensions = selectedOrientation === 'portrait' ? [794, 1123] : [1123, 794];
        previewStage.style.aspectRatio = `${dimensions[0]} / ${dimensions[1]}`;
        preview.style.width = `${dimensions[0]}px`;
        preview.style.height = `${dimensions[1]}px`;
        preview.style.transform = `scale(${previewStage.clientWidth / dimensions[0]})`;

        clearTimeout(previewTimer);
        previewTimer = setTimeout(() => {
            const params = new URLSearchParams(new FormData(printForm));
            params.set('preview', '1');
            params.set('preview_version', String(Date.now()));
            preview.src = `${printForm.action}?${params.toString()}`;
        }, 180);
    };

    for (const input of colorInputs) input.addEventListener('input', updatePreview);
    printForm.querySelectorAll('[name="orientation"]').forEach((input) => input.addEventListener('change', updatePreview));
    document.getElementById('resetPassPalette')?.addEventListener('click', () => {
        for (const input of colorInputs) input.value = input.dataset.default;
        showPaletteStatus('');
        updatePreview();
    });
    savedPalettesSelect.addEventListener('change', () => {
        const index = Number.parseInt(savedPalettesSelect.value, 10);
        if (!Number.isInteger(index) || !savedPalettes[index]) {
            deletePaletteButton.disabled = true;
            return;
        }

        for (const input of colorInputs) {
            const key = input.id.replace('pass_palette_', '');
            input.value = savedPalettes[index].palette[key];
        }
        deletePaletteButton.disabled = false;
        paletteNameInput.value = savedPalettes[index].name;
        showPaletteStatus(<?= json_encode(__('student_passes_palette_applied'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
        updatePreview();
    });
    savePaletteButton.addEventListener('click', () => {
        const name = paletteNameInput.value.trim();
        if (!name) {
            showPaletteStatus(<?= json_encode(__('student_passes_palette_name_required'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>, true);
            paletteNameInput.focus();
            return;
        }

        const existingIndex = savedPalettes.findIndex((preset) => preset.name.toLowerCase() === name.toLowerCase());
        const previousPalettes = savedPalettes;
        savedPalettes = [...savedPalettes];
        const preset = { name, palette: currentPalette() };
        if (existingIndex >= 0) {
            savedPalettes[existingIndex] = preset;
        } else {
            savedPalettes.push(preset);
        }

        try {
            persistSavedPalettes();
            renderSavedPalettes(String(existingIndex >= 0 ? existingIndex : savedPalettes.length - 1));
            showPaletteStatus(<?= json_encode(__('student_passes_palette_saved'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
        } catch (error) {
            savedPalettes = previousPalettes;
            renderSavedPalettes(existingIndex >= 0 ? String(existingIndex) : '');
            showPaletteStatus(<?= json_encode(__('student_passes_palette_storage_error'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>, true);
        }
    });
    deletePaletteButton.addEventListener('click', () => {
        const index = Number.parseInt(savedPalettesSelect.value, 10);
        if (!Number.isInteger(index) || !savedPalettes[index]) return;

        const previousPalettes = savedPalettes;
        savedPalettes = savedPalettes.filter((preset, presetIndex) => presetIndex !== index);
        try {
            persistSavedPalettes();
            renderSavedPalettes();
            paletteNameInput.value = '';
            showPaletteStatus(<?= json_encode(__('student_passes_palette_deleted'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
        } catch (error) {
            savedPalettes = previousPalettes;
            renderSavedPalettes(String(index));
            showPaletteStatus(<?= json_encode(__('student_passes_palette_storage_error'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>, true);
        }
    });
    try {
        savedPalettes = readSavedPalettes();
        renderSavedPalettes();
    } catch (error) {
        savePaletteButton.disabled = true;
        deletePaletteButton.disabled = true;
        showPaletteStatus(<?= json_encode(__('student_passes_palette_storage_error'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>, true);
    }
    new ResizeObserver(() => {
        const selectedOrientation = printForm.querySelector('[name="orientation"]:checked')?.value || 'landscape';
        const width = selectedOrientation === 'portrait' ? 794 : 1123;
        preview.style.transform = `scale(${previewStage.clientWidth / width})`;
    }).observe(previewStage);

    updatePreview();
})();
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../templates/layout.php';
?>
