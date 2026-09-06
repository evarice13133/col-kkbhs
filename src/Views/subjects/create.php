<?php
$title = __('define_subject');
ob_start();
?>

<div class="animate-fade-in container-fluid py-2 px-2 px-md-3">
    <!-- Compact Header -->
    <div class="d-flex align-items-center justify-content-between mb-2 mb-md-3 flex-wrap gap-2">
        <h2 class="fw-black text-main-theme mb-0 fs-5 fs-md-4"><?= __('create_subject') ?></h2>
        <a href="/subjects" class="btn btn-sm btn-light-theme rounded-pill px-3 border-theme-light">
            <i class="bi bi-arrow-left me-1"></i> <span class="d-none d-sm-inline"><?= __('back_to_list') ?></span>
        </a>
    </div>

    <form action="/subjects/store" method="POST" id="subjectCreateForm">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                <div><?= h($error) ?></div>
            </div>
        <?php endif; ?>

        <div class="modern-card border-0 shadow-sm overflow-hidden mb-3 mb-md-4">
            <div class="card-body p-3 p-md-4">

                <!-- 1. Identification & Paramètres de la matière -->
                <div class="row g-3 g-md-4 mb-4 mb-md-5">
                    <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                        <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1">
                            <i class="bi bi-book-half me-2"></i><?= __('subject_identification') ?>
                        </h6>
                    </div>

                    <div class="col-md-3">
                        <label
                            class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('subject_official_name') ?> *</label>
                        <input type="text" name="nom" class="form-control premium-input"
                            placeholder="<?= __('subject_name_placeholder') ?>" value="<?= h($nom ?? '') ?>" required
                            autofocus>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Type Enseignement *</label>
                        <select name="teaching_type_id" id="teaching_type_id" class="form-select premium-input border-primary border-opacity-25" required>
                            <option value=""><?= __('select_teaching_type') ?? 'Sélectionner un type...' ?></option>
                            <?php foreach ($teachingTypes as $tt): ?>
                                <option value="<?= $tt['id'] ?>" data-code="<?= h($tt['code'] ?? '') ?>" <?= (int)($teaching_type_id ?? 0) === (int)$tt['id'] ? 'selected' : '' ?>><?= h($tt['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Forme d’enseignement *</label>
                        <select name="teaching_form_id" id="teaching_form_id" class="form-select premium-input border-primary border-opacity-25" required>
                            <option value="">Sélectionner une forme...</option>
                            <?php foreach ($teachingForms as $tf): ?>
                                <option value="<?= $tf['id'] ?>" data-teaching-type-id="<?= $tf['teaching_type_id'] ?>" <?= (int)($teaching_form_id ?? 0) === (int)$tf['id'] ? 'selected' : '' ?>><?= h($tf['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label
                            class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('subject_group') ?> *</label>
                        <select name="subject_group_id" id="subject_group_id" class="form-select premium-input border-primary border-opacity-25" required>
                            <option value="">Sélectionner un groupe de matières...</option>
                            <?php foreach ($subjectGroups as $grp): ?>
                                <option value="<?= $grp['id'] ?>" data-teaching-type-id="<?= $grp['teaching_type_id'] ?? '' ?>" data-teaching-form-id="<?= $grp['teaching_form_id'] ?? '' ?>" <?= (int)($subject_group_id ?? 0) === (int)$grp['id'] ? 'selected' : '' ?>>
                                    <?= h($grp['libelle']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label
                            class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('base_coefficient') ?></label>
                        <input type="number" name="coefficient" class="form-control premium-input text-center"
                            value="<?= h($coeff ?? 1) ?>" min="1" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">VHm (Vol. Ministériel)</label>
                        <input type="number" name="vhm" class="form-control premium-input text-center"
                            value="<?= h($vhm ?? '') ?>" min="0" step="any" placeholder="Ex: 60">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">VHp (Vol. Proposé)</label>
                        <input type="number" name="vhp" class="form-control premium-input text-center"
                            value="<?= h($vhp ?? '') ?>" min="0" step="any" placeholder="Ex: 54">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">TH(Max) (Taux Max)</label>
                        <input type="number" name="th_max" class="form-control premium-input text-center"
                            value="<?= h($th_max ?? '') ?>" min="0" step="any" placeholder="Ex: 30">
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Observations</label>
                        <input type="text" name="observations" class="form-control premium-input"
                            value="<?= h($observations ?? '') ?>" placeholder="Remarques ou détails...">
                    </div>

                    <!-- Dynamic LMD Fields (Code UV / Code UE) -->
                    <div class="col-12" id="lmd_fields_container" style="display: none;">
                        <div class="p-3 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-4">
                            <h6 class="fw-bold text-primary mb-2 extra-small text-uppercase">
                                <i class="bi bi-mortarboard-fill me-1"></i><?= __('lmd_academic_info') ?? 'Informations Supérieur LMD' ?>
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('code_uv_optional') ?? 'Code UV (Optionnel)' ?></label>
                                    <input type="text" name="code_uv" id="code_uv" class="form-control premium-input" placeholder="Ex: INF301" value="<?= h($code_uv ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('code_ue_optional') ?? 'Code UE (Optionnel)' ?></label>
                                    <input type="text" name="code_ue" id="code_ue" class="form-control premium-input" placeholder="Ex: UE-INF3" value="<?= h($code_ue ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Classes Sélectionnées -->
                <div class="row g-3 g-md-4 mb-4 mb-md-5">
                    <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-3">
                            <h6 class="fw-black text-success m-0 text-uppercase letter-spacing-1">
                                <i class="bi bi-building me-2"></i><?= __('impacted_classes') ?> *
                            </h6>
                            
                            <div class="d-flex align-items-center gap-2 w-100 w-md-auto">
                                <div class="flex-grow-1" style="max-width: 250px;">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-transparent border-end-0 rounded-start-pill border-theme-light ps-3">
                                            <i class="bi bi-search text-muted-theme"></i>
                                        </span>
                                        <input type="text" id="classSearchInput" class="form-control border-start-0 rounded-end-pill border-theme-light bg-transparent extra-small fw-bold" 
                                               placeholder="<?= __('Rechercher...') ?>">
                                    </div>
                                </div>

                                <div class="form-check form-switch m-0 flex-shrink-0">
                                    <input class="form-check-input" type="checkbox" id="selectAllClasses">
                                    <label class="form-check-label extra-small fw-bold text-muted-theme ms-1 d-none d-sm-inline"
                                        for="selectAllClasses"><?= __('all') ?></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <!-- State 1: Prompt Selection Alert -->
                        <div id="no_teaching_type_alert" class="alert alert-warning border-0 rounded-3 mb-3 d-flex align-items-center d-none">
                            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                            <div>Veuillez d'abord sélectionner un <strong>Type d’enseignement</strong> et une <strong>Forme d'enseignement</strong> pour afficher les classes correspondantes.</div>
                        </div>

                        <!-- State 2: Loading State -->
                        <div id="classes_loading_spinner" class="alert alert-light border border-theme-light rounded-3 mb-3 d-flex align-items-center justify-content-center p-3 d-none">
                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                            <span class="extra-small fw-bold text-muted-theme">Filtrage et chargement des classes...</span>
                        </div>

                        <!-- State 4: No Classes Alert -->
                        <div id="no_classes_alert" class="alert alert-info border-0 rounded-3 mb-3 d-flex align-items-center d-none">
                            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                            <div>Aucune classe disponible pour cette sélection.</div>
                        </div>

                        <!-- State 5: Error Alert -->
                        <div id="classes_error_alert" class="alert alert-danger border-0 rounded-3 mb-3 d-flex align-items-center d-none">
                            <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
                            <div id="classes_error_message">Une erreur s'est produite lors de l'affichage des classes.</div>
                        </div>

                        <!-- State 3: Classes Grid Container -->
                        <div class="row g-2 d-none" id="classesGridContainer">
                            <?php foreach ($classes as $c): ?>
                                <?php $isChecked = in_array((int) $c['id'], array_map('intval', $classes_ids ?? []), true); ?>
                                <div class="col-6 col-sm-4 col-md-3 col-xl-2 class-wrapper" data-teaching-type-id="<?= $c['teaching_type_id'] ?? '' ?>" data-teaching-form-id="<?= $c['teaching_form_id'] ?? '' ?>">
                                    <div
                                        class="class-selection-item d-flex align-items-center p-2 rounded-3 border border-theme-light h-100 transition-base mobile-compact">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input border-primary class-checkbox" type="checkbox"
                                                name="classes[]" value="<?= $c['id'] ?>" id="class_<?= $c['id'] ?>" data-class-nom="<?= h($c['nom']) ?>"
                                                <?= $isChecked ? 'checked' : '' ?>>
                                        </div>
                                        <label class="d-flex align-items-center mb-0 ms-2 w-100 py-1"
                                            for="class_<?= $c['id'] ?>" style="cursor: pointer;">
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="fw-bold text-main-theme text-truncate"
                                                    style="font-size: 0.8rem;"><?= h($c['nom']) ?></div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="form-text extra-small text-muted-theme mt-2">
                            <i class="bi bi-info-circle me-1"></i>Sélectionnez au moins une classe. Chaque classe recevra sa propre instance autonome.
                        </div>
                    </div>
                </div>

                <!-- 3. Désolidarisation des matières -->
                <div class="row g-3 g-md-4 mb-4 mb-md-5">
                    <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                        <h6 class="fw-black text-warning m-0 text-uppercase letter-spacing-1">
                            <i class="bi bi-diagram-3-fill me-2"></i>Désolidarisation des matières
                        </h6>
                    </div>
                    <div class="col-12">
                        <div class="p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-4">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-warning text-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; flex-shrink: 0;">
                                    <i class="bi bi-shuffle fs-5"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold text-warning-emphasis mb-1">Désolidarisation automatique des instances</h6>
                                    <p class="small text-muted-theme mb-2">
                                        Si plusieurs classes sont sélectionnées, le système va créer <strong>une occurrence complètement indépendante de la matière pour chaque classe</strong>.
                                        Les compétences saisies ci-dessous seront initialisées pour toutes les occurrences, puis pourront être modifiées ou supprimées indépendamment par classe sans affecter les autres.
                                    </p>
                                    <div id="desolidarisation_preview" class="d-flex flex-wrap align-items-center gap-1 mt-2">
                                        <span class="badge bg-secondary opacity-75 extra-small">Aucune classe sélectionnée</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Compétences / Objectifs -->
                <div class="row g-3 g-md-4 mb-4 mb-md-5">
                    <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="fw-black text-info m-0 text-uppercase letter-spacing-1">
                                <i class="bi bi-check2-square me-2"></i><?= __('competencies_objectives') ?? 'Compétences / Objectifs' ?>
                            </h6>
                            <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3 py-1 extra-small fw-bold">
                                <?= __('optional') ?? 'Optionnel' ?>
                            </span>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="p-3 rounded-4">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">
                                        <?= __('number_of_competencies') ?? 'Nombre de compétences' ?>
                                    </label>
                                    <input type="number" id="competencyCount" class="form-control premium-input" 
                                           min="0" max="20" value="0" placeholder="0">
                                </div>
                                <div class="col-md-8">
                                    <div class="form-text extra-small text-muted-theme">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Les compétences ci-dessous seront rattachées spécifiquement à chaque occurrence autonome créée.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12" id="competenciesContainer" style="display: none;">
                        <div class="row g-3" id="competenciesFields">
                            <!-- Les champs de compétences seront générés dynamiquement ici -->
                        </div>
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="d-flex justify-content-end border-top border-theme-light pt-3 pt-md-4 mt-2">
                    <button type="submit"
                        class="btn btn-primary rounded-pill px-4 px-md-5 py-2 fw-bold shadow-sm transition-base scale-on-hover w-100 w-md-auto">
                        <i class="bi bi-check-circle-fill me-2"></i> <?= __('validate') ?>
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('selectAllClasses');
        const checkboxes = document.querySelectorAll('.class-checkbox');

        const previewContainer = document.getElementById('desolidarisation_preview');

        function updateDesolidarisationPreview() {
            if (!previewContainer) return;
            const checked = Array.from(checkboxes).filter(cb => cb.checked && !cb.closest('.class-wrapper')?.classList.contains('d-none'));
            if (checked.length === 0) {
                previewContainer.innerHTML = '<span class="badge bg-secondary opacity-75 extra-small">Aucune classe sélectionnée</span>';
            } else {
                let html = `<span class="badge bg-primary extra-small me-1">${checked.length} occurrence(s) distincte(s) :</span>`;
                checked.forEach(cb => {
                    const nom = cb.getAttribute('data-class-nom') || cb.closest('.class-selection-item')?.querySelector('.text-truncate')?.textContent?.trim() || `Classe #${cb.value}`;
                    html += `<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 extra-small">${nom}</span> `;
                });
                previewContainer.innerHTML = html;
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(cb => {
                    const wrapper = cb.closest('.class-wrapper');
                    if (wrapper && !wrapper.classList.contains('d-none')) {
                        cb.checked = selectAll.checked;
                    }
                });
                updateDesolidarisationPreview();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function () {
                const visibleCheckboxes = Array.from(checkboxes).filter(c => {
                    const wrapper = c.closest('.class-wrapper');
                    return wrapper && !wrapper.classList.contains('d-none');
                });
                if (visibleCheckboxes.length > 0) {
                    const allChecked = visibleCheckboxes.every(c => c.checked);
                    const noneChecked = visibleCheckboxes.every(c => !c.checked);
                    selectAll.checked = allChecked;
                    selectAll.indeterminate = !allChecked && !noneChecked;
                }
                updateDesolidarisationPreview();
            });
        });

        // Instant Search Logic
        const searchInput = document.getElementById('classSearchInput');
        const classItems = document.querySelectorAll('.class-selection-item');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                const selectedTT = document.getElementById('teaching_type_id')?.value;
                const selectedTF = document.getElementById('teaching_form_id')?.value;
                
                classItems.forEach(item => {
                    const label = item.querySelector('.text-truncate').textContent.toLowerCase();
                    const container = item.closest('.class-wrapper');
                    const classTT = container.getAttribute('data-teaching-type-id');
                    const classTF = container.getAttribute('data-teaching-form-id');
                    
                    const matchTT = !selectedTT || !classTT || String(classTT).trim() === String(selectedTT).trim();
                    const matchTF = !selectedTF || !classTF || String(classTF).trim() === String(selectedTF).trim();

                    if (matchTT && matchTF && label.includes(query)) {
                        container.classList.remove('d-none');
                    } else {
                        container.classList.add('d-none');
                    }
                });
            });
        }

        // Dynamic Competency Fields Generation
        const competencyCountInput = document.getElementById('competencyCount');
        const competenciesContainer = document.getElementById('competenciesContainer');
        const competenciesFields = document.getElementById('competenciesFields');
        let existingCompetencies = [];

        if (competencyCountInput) {
            competencyCountInput.addEventListener('input', function() {
                const count = Math.max(0, Math.min(20, parseInt(this.value) || 0));
                generateCompetencyFields(count);
            });
        }

        function generateCompetencyFields(count) {
            const currentInputs = competenciesFields.querySelectorAll('input[type="text"]');
            existingCompetencies = Array.from(currentInputs).map(input => input.value);
            competenciesFields.innerHTML = '';

            if (count > 0) {
                competenciesContainer.style.display = 'block';
                
                for (let i = 0; i < count; i++) {
                    const col = document.createElement('div');
                    col.className = 'col-md-6 col-lg-4';
                    const inputValue = existingCompetencies[i] || '';
                    col.innerHTML = `
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-1 extra-small fw-bold">
                                ${i + 1}
                            </span>
                            <input type="text" 
                                   name="competencies[]" 
                                   class="form-control premium-input" 
                                   placeholder="<?= __('competency_placeholder') ?? 'Ex: Comprendre les concepts de base' ?>"
                                   value="${inputValue}"
                                   data-index="${i}">
                        </div>
                    `;
                    competenciesFields.appendChild(col);
                }

                if (existingCompetencies.length < count) {
                    const firstNewField = competenciesFields.querySelector(`input[data-index="${existingCompetencies.length}"]`);
                    if (firstNewField) {
                        firstNewField.focus();
                    }
                }
            } else {
                competenciesContainer.style.display = 'none';
            }
        }

        // Dynamic Cascading Filtering State Controller
        const teachingTypeSelect = document.getElementById('teaching_type_id');
        const teachingFormSelect = document.getElementById('teaching_form_id');
        const groupSelect = document.getElementById('subject_group_id');
        const classWrappers = document.querySelectorAll('.class-wrapper');

        const noTTAlert = document.getElementById('no_teaching_type_alert');
        const loadingSpinner = document.getElementById('classes_loading_spinner');
        const noClassesAlert = document.getElementById('no_classes_alert');
        const errorAlert = document.getElementById('classes_error_alert');
        const errorMsg = document.getElementById('classes_error_message');
        const classesGrid = document.getElementById('classesGridContainer');

        const rawTFOptions = teachingFormSelect ? Array.from(teachingFormSelect.options) : [];
        const rawGroupOptions = groupSelect ? Array.from(groupSelect.options) : [];

        function setClassesState(stateName, customErrorText = null) {
            // Hide all state components using Bootstrap 'd-none' class
            if (noTTAlert) noTTAlert.classList.add('d-none');
            if (loadingSpinner) loadingSpinner.classList.add('d-none');
            if (noClassesAlert) noClassesAlert.classList.add('d-none');
            if (errorAlert) errorAlert.classList.add('d-none');
            if (classesGrid) classesGrid.classList.add('d-none');

            switch (stateName) {
                case 'PROMPT_SELECTION':
                    if (noTTAlert) noTTAlert.classList.remove('d-none');
                    break;
                case 'LOADING':
                    if (loadingSpinner) loadingSpinner.classList.remove('d-none');
                    break;
                case 'HAS_CLASSES':
                    if (classesGrid) classesGrid.classList.remove('d-none');
                    break;
                case 'NO_CLASSES':
                    if (noClassesAlert) noClassesAlert.classList.remove('d-none');
                    break;
                case 'ERROR':
                    if (errorMsg && customErrorText) errorMsg.textContent = customErrorText;
                    if (errorAlert) errorAlert.classList.remove('d-none');
                    break;
            }
        }

        let filterToken = 0;

        function filterCascading() {
            const thisToken = ++filterToken;
            const selectedTT = teachingTypeSelect ? String(teachingTypeSelect.value || '').trim() : '';

            // If Type is not selected, prompt selection
            if (!selectedTT) {
                setClassesState('PROMPT_SELECTION');
                
                if (teachingFormSelect) {
                    teachingFormSelect.innerHTML = '<option value="" disabled selected>Sélectionnez d\'abord un type...</option>';
                    teachingFormSelect.disabled = true;
                }
                if (groupSelect) {
                    groupSelect.innerHTML = '<option value="" disabled selected>Sélectionnez d\'abord un type...</option>';
                    groupSelect.disabled = true;
                }
                
                classWrappers.forEach(wrapper => {
                    wrapper.classList.add('d-none');
                    const checkbox = wrapper.querySelector('.class-checkbox');
                    if (checkbox) checkbox.checked = false;
                });
                return;
            }

            // 1. Filter Teaching Forms based on Teaching Type
            if (teachingFormSelect) {
                const currentTFVal = String(teachingFormSelect.value || '').trim();
                teachingFormSelect.innerHTML = '';

                rawTFOptions.forEach(opt => {
                    if (!opt.value) {
                        const defaultOpt = opt.cloneNode(true);
                        defaultOpt.textContent = "Sélectionner une forme...";
                        teachingFormSelect.appendChild(defaultOpt);
                        return;
                    }
                    const optTT = opt.getAttribute('data-teaching-type-id');
                    if (!optTT || String(optTT).trim() === selectedTT) {
                        const newOpt = opt.cloneNode(true);
                        if (String(newOpt.value).trim() === currentTFVal) {
                            newOpt.selected = true;
                        }
                        teachingFormSelect.appendChild(newOpt);
                    }
                });

                teachingFormSelect.disabled = false;
            }

            const selectedTF = teachingFormSelect ? String(teachingFormSelect.value || '').trim() : '';

            // 2. Filter Subject Groups based on Teaching Type & Form
            if (groupSelect) {
                const currentGrpVal = String(groupSelect.value || '').trim();
                groupSelect.innerHTML = '';

                rawGroupOptions.forEach(opt => {
                    if (!opt.value) {
                        const defaultOpt = opt.cloneNode(true);
                        defaultOpt.textContent = "Sélectionner un groupe de matières...";
                        groupSelect.appendChild(defaultOpt);
                        return;
                    }
                    const optTT = opt.getAttribute('data-teaching-type-id');
                    const optTF = opt.getAttribute('data-teaching-form-id');

                    const matchTT = !optTT || String(optTT).trim() === selectedTT;
                    const matchTF = !selectedTF || !optTF || String(optTF).trim() === selectedTF;

                    if (matchTT && matchTF) {
                        const newOpt = opt.cloneNode(true);
                        if (String(newOpt.value).trim() === currentGrpVal) {
                            newOpt.selected = true;
                        }
                        groupSelect.appendChild(newOpt);
                    }
                });

                groupSelect.disabled = false;
            }

            // 3. Filter Classes Grid
            let visibleClassCount = 0;
            classWrappers.forEach(wrapper => {
                const classTT = wrapper.getAttribute('data-teaching-type-id');
                const classTF = wrapper.getAttribute('data-teaching-form-id');

                const matchTT = !classTT || String(classTT).trim() === selectedTT;
                const matchTF = !selectedTF || !classTF || String(classTF).trim() === selectedTF;

                if (matchTT && matchTF) {
                    wrapper.classList.remove('d-none');
                    visibleClassCount++;
                } else {
                    wrapper.classList.add('d-none');
                    const checkbox = wrapper.querySelector('.class-checkbox');
                    if (checkbox) checkbox.checked = false;
                }
            });

            // Prevent race conditions
            if (thisToken !== filterToken) return;

            // Set final state based on visible class count
            if (visibleClassCount > 0) {
                setClassesState('HAS_CLASSES');
            } else {
                setClassesState('NO_CLASSES');
            }

            if (typeof updateDesolidarisationPreview === 'function') {
                updateDesolidarisationPreview();
            }
        }

        if (teachingTypeSelect) {
            teachingTypeSelect.addEventListener('change', filterCascading);
        }
        if (teachingFormSelect) {
            teachingFormSelect.addEventListener('change', filterCascading);
        }
        filterCascading();
        if (typeof updateDesolidarisationPreview === 'function') {
            updateDesolidarisationPreview();
        }

        // Form Validation on Submit
        const form = document.getElementById('subjectCreateForm');
        if (form) {
            form.addEventListener('submit', function (e) {
                const nomVal = document.querySelector('input[name="nom"]')?.value.trim();
                const ttVal = document.getElementById('teaching_type_id')?.value;
                const grpVal = document.getElementById('subject_group_id')?.value;
                const checkedClasses = document.querySelectorAll('.class-checkbox:checked');

                if (!nomVal) {
                    e.preventDefault();
                    alert("Veuillez saisir le nom de la matière.");
                    return;
                }
                if (!ttVal) {
                    e.preventDefault();
                    alert("Veuillez sélectionner un type d’enseignement.");
                    return;
                }
                if (!grpVal) {
                    e.preventDefault();
                    alert("Veuillez sélectionner un groupe de matières.");
                    return;
                }
                if (checkedClasses.length === 0) {
                    e.preventDefault();
                    alert("Veuillez sélectionner au moins une classe.");
                    return;
                }
            });
        }
    });
</script>

<style>
    /* Styles pour améliorer la présentation mobile */
    @media (max-width: 767.98px) {
        .mobile-compact {
            padding: 0.5rem !important;
        }
        
        .mobile-compact .form-check-input {
            width: 1.1em;
            height: 1.1em;
            margin-top: 0.1em;
        }
        
        .mobile-compact label {
            font-size: 0.75rem;
        }
        
        .mobile-compact .text-truncate {
            max-width: 80px !important;
        }
        
        .extra-small {
            font-size: 0.65rem;
        }
        
        /* Réduire les espacements entre les éléments de formulaire */
        .form-label {
            margin-bottom: 0.25rem;
            font-size: 0.7rem;
        }
        
        .premium-input {
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
        }
        
        /* Optimiser le switch sur mobile */
        .form-check-input[type="checkbox"] {
            width: 2.5em;
            height: 1.3em;
        }
    }
    
    /* Styles pour les transitions */
    .transition-base {
        transition: all 0.2s ease;
    }
    
    .scale-on-hover:hover {
        transform: scale(1.02);
    }
    
    /* Thème sombre pour le formulaire */
    [data-theme="dark"] .modern-card {
        background: rgba(30, 30, 45, 0.6);
        border-color: rgba(255, 255, 255, 0.08);
    }
    
    [data-theme="dark"] .border-theme-light {
        border-color: rgba(255, 255, 255, 0.1) !important;
    }
    
    [data-theme="dark"] .premium-input {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.1);
        color: #ffffff;
    }
    
    [data-theme="dark"] .premium-input:focus {
        background: rgba(255, 255, 255, 0.08);
        border-color: var(--primary-color);
        color: #ffffff;
    }
    
    [data-theme="dark"] .class-selection-item:hover {
        background: rgba(255, 255, 255, 0.05);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const teachingTypeSelect = document.querySelector('select[name="teaching_type_id"]');
    const departmentSelect = document.getElementById('department_id');
    if (!departmentSelect) {
        return;
    }
    const originalDeptOptions = Array.from(departmentSelect.options);

    function filterDepartments() {
        const selectedType = teachingTypeSelect.value;
        const currentDeptValue = departmentSelect.value;
        
        departmentSelect.innerHTML = '';
        
        let foundCurrent = false;
        
        originalDeptOptions.forEach(opt => {
            if (opt.value === '' || !selectedType || opt.dataset.teachingTypeId == selectedType || !opt.dataset.teachingTypeId) {
                departmentSelect.appendChild(opt.cloneNode(true));
                if (opt.value === currentDeptValue) {
                    foundCurrent = true;
                }
            }
        });
        
        if (!foundCurrent) {
            departmentSelect.value = '';
        } else {
            departmentSelect.value = currentDeptValue;
        }
    }

    function toggleLmdFields() {
        const select = document.getElementById('teaching_type_id');
        const container = document.getElementById('lmd_fields_container');
        if (!select || !container) return;
        const selectedOption = select.options[select.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            container.style.display = 'none';
            return;
        }
        const code = (selectedOption.getAttribute('data-code') || '').toUpperCase();
        const text = (selectedOption.textContent || selectedOption.innerText || '').toUpperCase();
        if (code === 'LMD' || text.includes('LMD') || text.includes('SUPÉRIEUR') || text.includes('SUPERIEUR')) {
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
            const uv = document.getElementById('code_uv');
            const ue = document.getElementById('code_ue');
            if (uv) uv.value = '';
            if (ue) ue.value = '';
        }
    }

    if (teachingTypeSelect) {
        teachingTypeSelect.addEventListener('change', function() {
            if (typeof filterDepartments === 'function') filterDepartments();
            toggleLmdFields();
        });
        if (typeof filterDepartments === 'function') filterDepartments();
        toggleLmdFields();
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>