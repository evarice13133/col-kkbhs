<?php
$title = __('identity_cards_title');
$paletteDefaults = \App\Services\AccessCardPalette::defaults();
$paletteLabels = [
    'background' => __('access_card_palette_background'),
    'accent' => __('access_card_palette_accent'),
    'border' => __('access_card_palette_border'),
    'text' => __('access_card_palette_text'),
    'muted' => __('access_card_palette_muted'),
];
$paletteKeys = array_keys($paletteLabels);
$tranchePaletteDefaults = array_intersect_key($paletteDefaults, array_flip(['tranche1', 'tranche2', 'tranche3']));
$selectedSection = null;
foreach ($sections as $section) {
    if ((int) $section['id'] === $sectionId) {
        $selectedSection = $section;
        break;
    }
}
$missingInfoFields = [
    'nom' => __('access_card_last_name'),
    'prenom' => __('access_card_first_name'),
    'email' => __('matricule'),
    'date_naissance' => __('access_card_birth'),
    'lieu_naissance' => __('access_card_birth_place'),
    'sexe' => __('access_card_sex'),
    'photo_eleve' => __('access_card_no_photo'),
    'parent_contact' => __('access_card_contact'),
];
$studentsWithMissingInfo = [];
foreach ($students as $student) {
    $missingFields = [];
    foreach ($missingInfoFields as $field => $label) {
        if (trim((string) ($student[$field] ?? '')) === '') {
            $missingFields[] = $label;
        }
    }
    if ($missingFields !== []) {
        $studentsWithMissingInfo[] = [
            'name' => trim((string) ($student['nom'] ?? '') . ' ' . (string) ($student['prenom'] ?? '')),
            'class_name' => (string) ($student['class_name'] ?? ''),
            'missing_fields' => $missingFields,
        ];
    }
}
ob_start();
?>

<div class="animate-fade-in admin-analytics module-bureau-flow">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2 mb-4">
        <h1 class="h4 fw-bold text-main-theme mb-1"><?= __('identity_cards_title') ?></h1>
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 px-3 py-2" id="identityCardsPerSheet">
            <i class="bi bi-grid-3x2 me-1"></i><?= __('identity_cards_per_sheet_' . $orientation) ?>
        </span>
    </div>

    <div class="modern-card border-0 shadow-sm mb-4">
        <div class="modern-card-body p-3 p-lg-4">
            <form method="GET" action="<?= htmlspecialchars($routePrefix, ENT_QUOTES, 'UTF-8') ?>" class="row g-3 align-items-end" id="accessCardFilters">
                <div class="col-12 col-md-6">
                    <label for="section_id" class="form-label fw-semibold"><?= __('access_cards_section_label') ?></label>
                    <select id="section_id" name="section_id" class="form-select" onchange="this.form.requestSubmit()">
                        <option value="0"><?= __('access_cards_choose_section') ?></option>
                        <?php foreach ($sections as $section): ?>
                            <option value="<?= (int) $section['id'] ?>" <?= $sectionId === (int) $section['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $section['nom'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($selectedSection): ?>
                    <div class="col-12 col-md-6">
                        <label for="class_id" class="form-label fw-semibold"><?= __('access_cards_class_scope_label') ?></label>
                        <select id="class_id" name="class_id" class="form-select" onchange="this.form.requestSubmit()">
                            <?php $sectionStudentCount = (int) $selectedSection['enrolled_count']; ?>
                            <option value="0" <?= $classId === 0 ? 'selected' : '' ?>>
                                <?= sprintf(__('access_cards_print_section_students_' . ($sectionStudentCount === 1 ? 'singular' : 'plural')), $sectionStudentCount) ?>
                            </option>
                            <?php foreach ($classes as $class): ?>
                                <?php $enrolledCount = (int) $class['enrolled_count']; ?>
                                <option value="<?= (int) $class['id'] ?>" <?= $classId === (int) $class['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $class['nom'], ENT_QUOTES, 'UTF-8') ?> — <?= sprintf(__('access_cards_class_enrolled_count_' . ($enrolledCount === 1 ? 'singular' : 'plural')), $enrolledCount) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                <input type="hidden" name="orientation" value="<?= htmlspecialchars($orientation, ENT_QUOTES, 'UTF-8') ?>" id="filterOrientation">
                <?php foreach ($paletteKeys as $key): ?>
                    <input type="hidden" name="palette[<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>]" value="<?= htmlspecialchars($palette[$key], ENT_QUOTES, 'UTF-8') ?>" data-filter-palette="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">
                <?php endforeach; ?>
            </form>
        </div>
    </div>

    <?php if (empty($sections)): ?>
        <div class="alert alert-light border d-flex align-items-start gap-3 mb-0" role="status">
            <i class="bi bi-info-circle fs-4 text-primary" aria-hidden="true"></i>
            <span><?= __('access_cards_sections_empty') ?></span>
        </div>
    <?php elseif (!$selectedSection): ?>
        <div class="alert alert-light border d-flex align-items-center gap-3 mb-0" role="status">
            <i class="bi bi-arrow-up-circle fs-4 text-primary" aria-hidden="true"></i>
            <span><?= __('access_cards_choose_section_hint') ?></span>
        </div>
    <?php elseif (empty($students)): ?>
        <div class="alert alert-light border d-flex align-items-center gap-3 mb-0" role="status">
            <i class="bi bi-person-x fs-4 text-muted" aria-hidden="true"></i>
            <span><?= __('identity_cards_empty') ?></span>
        </div>
    <?php else: ?>
        <div class="modern-card border-0 shadow-sm">
            <div class="modern-card-body p-3 p-lg-4">
                <form method="GET" action="<?= htmlspecialchars($routePrefix . '/print', ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="row g-3 align-items-end" id="accessCardPrintForm">
                    <input type="hidden" name="academic_year_id" value="<?= (int) $academicYearId ?>">
                    <input type="hidden" name="section_id" value="<?= (int) $sectionId ?>">
                    <input type="hidden" name="class_id" value="<?= (int) $classId ?>">
                    <input type="hidden" name="orientation" value="<?= htmlspecialchars($orientation, ENT_QUOTES, 'UTF-8') ?>">
                    <fieldset class="col-12">
                        <legend class="form-label fw-semibold mb-2"><?= __('access_card_orientation') ?></legend>
                        <div class="d-flex flex-wrap gap-3">
                            <?php foreach (['portrait', 'landscape'] as $printOrientation): ?>
                                <div class="form-check">
                                    <input class="form-check-input identity-card-orientation" type="radio" name="orientation_choice" id="identity_orientation_<?= $printOrientation ?>" value="<?= $printOrientation ?>" <?= $orientation === $printOrientation ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="identity_orientation_<?= $printOrientation ?>"><?= __('access_card_orientation_' . $printOrientation) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                    <section class="col-12 access-card-palette" aria-labelledby="accessCardPaletteTitle">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div>
                                <h2 id="accessCardPaletteTitle" class="h6 fw-bold mb-1"><?= __('access_card_palette_title') ?></h2>
                                <p class="small text-muted mb-0"><?= __('access_card_palette_description') ?></p>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="resetAccessCardPalette">
                                <i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i><?= __('access_card_palette_reset') ?>
                            </button>
                        </div>
                        <div class="row g-2">
                            <?php foreach ($paletteKeys as $key): ?>
                                <div class="col-6 col-md-3">
                                    <label for="palette_<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" class="form-label small fw-semibold mb-1"><?= htmlspecialchars($paletteLabels[$key], ENT_QUOTES, 'UTF-8') ?></label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="color" class="form-control form-control-color access-card-color" id="palette_<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" name="palette[<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>]" value="<?= htmlspecialchars($palette[$key], ENT_QUOTES, 'UTF-8') ?>" data-default="<?= htmlspecialchars($paletteDefaults[$key], ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars($paletteLabels[$key], ENT_QUOTES, 'UTF-8') ?>">
                                        <output class="font-monospace small" data-color-value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($palette[$key], ENT_QUOTES, 'UTF-8') ?></output>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="row g-2 align-items-end mt-2">
                            <div class="col-12 col-md-5">
                                <label for="identityPaletteName" class="form-label small fw-semibold mb-1"><?= htmlspecialchars(__('student_passes_palette_name'), ENT_QUOTES, 'UTF-8') ?></label>
                                <input type="text" class="form-control" id="identityPaletteName" maxlength="60" autocomplete="off" placeholder="<?= htmlspecialchars(__('student_passes_palette_name_placeholder'), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="savedIdentityPalettes" class="form-label small fw-semibold mb-1"><?= htmlspecialchars(__('student_passes_saved_palettes'), ENT_QUOTES, 'UTF-8') ?></label>
                                <select class="form-select" id="savedIdentityPalettes">
                                    <option value=""><?= htmlspecialchars(__('student_passes_palette_choose'), ENT_QUOTES, 'UTF-8') ?></option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3 d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary flex-fill" id="saveIdentityPalette"><?= htmlspecialchars(__('student_passes_palette_save'), ENT_QUOTES, 'UTF-8') ?></button>
                                <button type="button" class="btn btn-outline-danger" id="deleteIdentityPalette" aria-label="<?= htmlspecialchars(__('student_passes_palette_delete'), ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars(__('student_passes_palette_delete'), ENT_QUOTES, 'UTF-8') ?>" disabled>
                                    <i class="bi bi-trash" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div class="col-12">
                                <p class="form-text mb-0"><?= htmlspecialchars(__('student_passes_palette_storage_hint'), ENT_QUOTES, 'UTF-8') ?></p>
                                <div id="identityPaletteStatus" class="small mt-1" role="status" aria-live="polite"></div>
                            </div>
                        </div>
                        <div class="row g-3 align-items-center mt-1">
                            <div class="col-12 col-lg-8">
                                <h3 class="small fw-bold mb-2"><?= __('access_card_palette_preview') ?></h3>
                                <div class="access-card-preview-stage" id="accessCardPreviewStage">
                                    <iframe id="accessCardPreview" title="<?= htmlspecialchars(__('access_card_palette_preview'), ENT_QUOTES, 'UTF-8') ?>" loading="lazy"></iframe>
                                </div>
                                <div id="accessCardContrast" class="small text-muted mt-2" role="status" aria-live="polite" data-ok="<?= htmlspecialchars(__('access_card_palette_contrast_ok'), ENT_QUOTES, 'UTF-8') ?>" data-warning="<?= htmlspecialchars(__('access_card_palette_contrast_warning'), ENT_QUOTES, 'UTF-8') ?>"></div>
                            </div>
                            <div class="col-12 col-lg-4">
                                <section class="access-card-missing-panel rounded p-3 h-100" aria-labelledby="accessCardMissingInfoTitle">
                                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                        <div>
                                            <h3 id="accessCardMissingInfoTitle" class="h6 fw-bold mb-1"><?= __('access_cards_missing_info_title') ?></h3>
                                            <p class="small text-muted mb-0"><?= __('access_cards_missing_info_description') ?></p>
                                        </div>
                                        <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning-emphasis"><?= count($studentsWithMissingInfo) ?></span>
                                    </div>
                                    <?php if (empty($studentsWithMissingInfo)): ?>
                                        <p class="small text-success mb-0"><i class="bi bi-check-circle me-1" aria-hidden="true"></i><?= __('access_cards_missing_info_none') ?></p>
                                    <?php else: ?>
                                        <ul class="access-card-missing-list list-unstyled mb-0" aria-label="<?= htmlspecialchars(__('access_cards_missing_info_title'), ENT_QUOTES, 'UTF-8') ?>">
                                            <?php foreach ($studentsWithMissingInfo as $studentInfo): ?>
                                                <li class="access-card-missing-item py-2">
                                                    <div class="fw-semibold"><?= htmlspecialchars($studentInfo['name'] !== '' ? $studentInfo['name'] : __('identity_card_student'), ENT_QUOTES, 'UTF-8') ?></div>
                                                    <?php if ($studentInfo['class_name'] !== ''): ?>
                                                        <div class="small text-muted"><?= htmlspecialchars($studentInfo['class_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                                    <?php endif; ?>
                                                    <div class="small mt-1"><span class="text-muted"><?= __('access_cards_missing_fields_label') ?>:</span> <?= htmlspecialchars(implode(', ', $studentInfo['missing_fields']), ENT_QUOTES, 'UTF-8') ?></div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </section>
                            </div>
                        </div>
                    </section>
                    <div class="col-12 col-md-8">
                        <label for="student_id" class="form-label fw-semibold"><?= __('identity_card_student') ?></label>
                        <select id="student_id" name="student_id" class="form-select">
                            <option value="0"><?= __('identity_cards_print_all') ?> (<?= count($students) ?>)</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= (int) $student['id'] ?>"><?= htmlspecialchars(trim((string) $student['nom'] . ' ' . $student['prenom']) . ' · ' . (string) ($student['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-printer-fill" aria-hidden="true"></i><?= __('print') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    .access-card-palette { padding: 1rem; border: 1px solid #dee2e6; border-radius: .375rem; }
    .access-card-preview-stage { position: relative; width: 100%; aspect-ratio: 297 / 210; overflow: hidden; border: 1px solid #dee2e6; background: #fff; }
    #accessCardPreview { position: absolute; top: 0; left: 0; width: 1123px; height: 794px; border: 0; transform-origin: top left; }
    .access-card-missing-panel { color: var(--text-main, #212529); background: rgba(100, 116, 139, .06); border: 1px solid var(--bs-border-color, rgba(100, 116, 139, .25)); }
    [data-theme="dark"] .access-card-missing-panel { background: rgba(255, 255, 255, .04); border-color: rgba(255, 255, 255, .16); }
    .access-card-missing-list { max-height: 340px; overflow-y: auto; }
    .access-card-missing-item + .access-card-missing-item { border-top: 1px solid var(--bs-border-color, rgba(100, 116, 139, .25)); }
    #accessCardContrast.text-danger { font-weight: 700; }
</style>
<script>
(() => {
    const filterForm = document.getElementById('accessCardFilters');
    const printForm = document.getElementById('accessCardPrintForm');
    if (!filterForm || !printForm) return;

    const colorInputs = [...printForm.querySelectorAll('.access-card-color')];
    const preview = document.getElementById('accessCardPreview');
    const previewStage = document.getElementById('accessCardPreviewStage');
    const contrastStatus = document.getElementById('accessCardContrast');
    const studentSelect = printForm.querySelector('[name="student_id"]');
    const orientationInput = printForm.querySelector('[name="orientation"]');
    const orientationRadios = [...printForm.querySelectorAll('.identity-card-orientation')];
    const pageCountBadge = document.getElementById('identityCardsPerSheet');
    const previewStudentId = <?= (int) $students[0]['id'] ?>;
    const paletteNameInput = document.getElementById('identityPaletteName');
    const savedPalettesSelect = document.getElementById('savedIdentityPalettes');
    const savePaletteButton = document.getElementById('saveIdentityPalette');
    const deletePaletteButton = document.getElementById('deleteIdentityPalette');
    const paletteStatus = document.getElementById('identityPaletteStatus');
    const paletteKeys = colorInputs.map((input) => input.id.replace('palette_', ''));
    const tranchePaletteDefaults = <?= json_encode($tranchePaletteDefaults, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
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
        colorInputs.map((input) => [input.id.replace('palette_', ''), input.value.toUpperCase()])
    );
    const isValidPalette = palette => paletteKeys.every(key =>
        typeof palette?.[key] === 'string' && /^#[0-9a-f]{6}$/i.test(palette[key])
    );
    const readSavedPalettes = () => {
        const stored = localStorage.getItem(paletteStorageKey);
        if (stored === null) return [];

        const parsed = JSON.parse(stored);
        if (!Array.isArray(parsed)) throw new Error('invalid_palette_data');

        return parsed.filter(preset =>
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

    filterForm.addEventListener('submit', () => {
        for (const input of colorInputs) {
            const hidden = filterForm.querySelector(`[data-filter-palette="${input.id.replace('palette_', '')}"]`);
            if (hidden) hidden.value = input.value;
        }
    });
    const luminance = hex => {
        const channels = hex.slice(1).match(/.{2}/g).map(channel => parseInt(channel, 16) / 255);
        const linear = channels.map(channel => channel <= .04045 ? channel / 12.92 : ((channel + .055) / 1.055) ** 2.4);
        return .2126 * linear[0] + .7152 * linear[1] + .0722 * linear[2];
    };
    const contrastRatio = (first, second) => {
        const values = [luminance(first), luminance(second)].sort((a, b) => b - a);
        return (values[0] + .05) / (values[1] + .05);
    };
    const updateContrast = palette => {
        palette = { ...tranchePaletteDefaults, ...palette };
        const textPairs = [
            [palette.text, palette.background], [palette.muted, palette.background],
            [palette.accent, palette.background], [palette.text, palette.tranche1],
            [palette.text, palette.tranche2], [palette.text, palette.tranche3],
        ];
        const borderPairs = [palette.background, palette.tranche1, palette.tranche2, palette.tranche3]
            .map(color => [palette.border, color]);
        const minimum = Math.min(...textPairs.map(pair => contrastRatio(...pair)));
        const weakPairs = textPairs.filter(pair => contrastRatio(...pair) < 4.5).length
            + borderPairs.filter(pair => contrastRatio(...pair) < 3).length;
        const template = weakPairs ? contrastStatus.dataset.warning : contrastStatus.dataset.ok;
        contrastStatus.textContent = template.replace('%s', minimum.toFixed(2)).replace('%s', String(weakPairs));
        contrastStatus.classList.toggle('text-danger', weakPairs > 0);
        contrastStatus.classList.toggle('text-success', weakPairs === 0);
    };
    const updatePreview = () => {
        const orientation = printForm.querySelector('.identity-card-orientation:checked')?.value || 'landscape';
        orientationInput.value = orientation;
        document.getElementById('filterOrientation').value = orientation;
        pageCountBadge.innerHTML = `<i class="bi bi-grid-3x2 me-1"></i>${orientation === 'portrait' ? <?= json_encode(__('identity_cards_per_sheet_portrait')) ?> : <?= json_encode(__('identity_cards_per_sheet_landscape')) ?>}`;
        const palette = {};
        for (const input of colorInputs) {
            const key = input.id.replace('palette_', '');
            palette[key] = input.value.toUpperCase();
            const output = printForm.querySelector(`[data-color-value="${key}"]`);
            if (output) output.value = palette[key];
        }
        updateContrast(palette);
        const dimensions = orientation === 'portrait' ? [794, 1123] : [1123, 794];
        previewStage.style.aspectRatio = `${dimensions[0]} / ${dimensions[1]}`;
        preview.style.width = `${dimensions[0]}px`;
        preview.style.height = `${dimensions[1]}px`;
        preview.style.transform = `scale(${previewStage.clientWidth / dimensions[0]})`;

        clearTimeout(previewTimer);
        previewTimer = setTimeout(() => {
            const params = new URLSearchParams(new FormData(printForm));
            params.delete('orientation_choice');
            params.set('student_id', studentSelect.value === '0' ? String(previewStudentId) : studentSelect.value);
            params.set('preview', '1');
            params.set('preview_version', String(Date.now()));
            preview.src = `${printForm.action}?${params.toString()}`;
        }, 180);
    };
    const resizePreview = () => {
        const orientation = printForm.querySelector('.identity-card-orientation:checked')?.value || 'landscape';
        const width = orientation === 'portrait' ? 794 : 1123;
        preview.style.transform = `scale(${previewStage.clientWidth / width})`;
    };
    for (const input of colorInputs) input.addEventListener('input', updatePreview);
    studentSelect.addEventListener('change', updatePreview);
    for (const input of orientationRadios) input.addEventListener('change', updatePreview);
    document.getElementById('resetAccessCardPalette').addEventListener('click', () => {
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
            const key = input.id.replace('palette_', '');
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

        const existingIndex = savedPalettes.findIndex(preset => preset.name.toLowerCase() === name.toLowerCase());
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
    new ResizeObserver(resizePreview).observe(previewStage);
    resizePreview();
    updatePreview();
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../templates/layout.php';
