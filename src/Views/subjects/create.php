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

        <div class="modern-card border-0 shadow-sm overflow-hidden mb-3 mb-md-4">
            <div class="card-body p-3 p-md-4">

                <!-- Basic Info Section -->
                <div class="row g-3 g-md-4 mb-4 mb-md-5">
                    <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                        <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1">
                            <?= __('subject_identification') ?></h6>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('teaching_type') ?? 'Type Enseignement' ?> *</label>
                        <select name="teaching_type_id" id="teaching_type_id" class="form-select premium-input border-primary border-opacity-25" required>
                            <option value=""><?= __('select_teaching_type') ?? 'Sélectionner un type' ?></option>
                            <?php foreach ($teachingTypes as $tt): ?>
                                <option value="<?= $tt['id'] ?>" data-code="<?= h($tt['code'] ?? '') ?>"><?= h($tt['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label
                            class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('subject_official_name') ?> *</label>
                        <input type="text" name="nom" class="form-control premium-input"
                            placeholder="<?= __('subject_name_placeholder') ?>" value="<?= h($nom ?? '') ?>" required
                            autofocus>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('department') ?></label>
                        <select name="department_id" id="department_id" class="form-select premium-input">
                            <option value=""><?= __('no_department') ?? 'Aucun département' ?></option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>" data-teaching-type-id="<?= $dept['teaching_type_id'] ?>">
                                    <?= h($dept['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label
                            class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('subject_group') ?></label>
                        <select name="subject_group_id" id="subject_group_id" class="form-select premium-input">
                            <option value="">Sélectionner un groupe de modules...</option>
                            <?php foreach ($subjectGroups as $grp): ?>
                                <option value="<?= $grp['id'] ?>" data-teaching-type-id="<?= $grp['teaching_type_id'] ?>" <?= (int)($subject_group_id ?? 0) === (int)$grp['id'] ? 'selected' : '' ?>>
                                    <?= h($grp['libelle']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
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

                <!-- Class Assignment Section -->
                <div class="row g-3 g-md-4 mb-3 mb-md-4">
                    <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-3">
                            <h6 class="fw-black text-success m-0 text-uppercase letter-spacing-1">
                                <?= __('impacted_classes') ?></h6>
                            
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
                        <div class="row g-2">
                            <?php foreach ($classes as $c): ?>
                                <?php $isChecked = in_array((int) $c['id'], array_map('intval', $classes_ids ?? []), true); ?>
                                <div class="col-6 col-sm-4 col-md-3 col-xl-2 class-wrapper" data-teaching-type-id="<?= $c['teaching_type_id'] ?? '' ?>">
                                    <div
                                        class="class-selection-item d-flex align-items-center p-2 rounded-3 border border-theme-light h-100 transition-base mobile-compact">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input border-primary class-checkbox" type="checkbox"
                                                name="classes[]" value="<?= $c['id'] ?>" id="class_<?= $c['id'] ?>"
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
                        <div class="form-text extra-small text-danger mt-3 opacity-75">
                            <i class="bi bi-info-circle me-1"></i><?= __('impacted_classes_warning') ?>
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

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(cb => {
                    cb.checked = selectAll.checked;
                });
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function () {
                const allChecked = Array.from(checkboxes).every(c => c.checked);
                const noneChecked = Array.from(checkboxes).every(c => !c.checked);
                selectAll.checked = allChecked;
                selectAll.indeterminate = !allChecked && !noneChecked;
            });
        });

        // Instant Search Logic
        const searchInput = document.getElementById('classSearchInput');
        const classItems = document.querySelectorAll('.class-selection-item');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                
                classItems.forEach(item => {
                    const label = item.querySelector('.text-truncate').textContent.toLowerCase();
                    const container = item.closest('.class-wrapper'); // The grid column wrapper
                    
                    if (label.includes(query)) {
                        container.classList.remove('d-none');
                    } else {
                        container.classList.add('d-none');
                    }
                });
            });
        }

        // Dynamic Filtering between Teaching Type, Department, Subject Groups and Classes
        const teachingTypeSelect = document.getElementById('teaching_type_id');
        const deptSelect = document.getElementById('department_id');
        const groupSelect = document.getElementById('subject_group_id');
        const classWrappers = document.querySelectorAll('.class-wrapper');

        if (teachingTypeSelect) {
            function filterDeptAndClasses() {
                const selectedTT = teachingTypeSelect.value;

                // 1. Filter Departments
                if (deptSelect) {
                    const originalDeptVal = deptSelect.value;
                    let deptValid = false;
                    Array.from(deptSelect.options).forEach(opt => {
                        if (!opt.value) return; // Keep "Aucun département"
                        const optTT = opt.getAttribute('data-teaching-type-id');
                        if (!selectedTT || !optTT || optTT === selectedTT) {
                            opt.style.display = '';
                            if (opt.value === originalDeptVal) deptValid = true;
                        } else {
                            opt.style.display = 'none';
                        }
                    });
                    if (originalDeptVal && !deptValid) {
                        deptSelect.value = '';
                    }
                }

                // 2. Filter Subject Groups
                if (groupSelect) {
                    const originalGrpVal = groupSelect.value;
                    let grpValid = false;
                    Array.from(groupSelect.options).forEach(opt => {
                        if (!opt.value) return;
                        const optTT = opt.getAttribute('data-teaching-type-id');
                        if (!selectedTT || !optTT || optTT === selectedTT) {
                            opt.style.display = '';
                            if (opt.value === originalGrpVal) grpValid = true;
                        } else {
                            opt.style.display = 'none';
                        }
                    });
                    if (originalGrpVal && !grpValid) {
                        groupSelect.value = '';
                    }
                }

                // 3. Filter Classes
                classWrappers.forEach(wrapper => {
                    const classTT = wrapper.getAttribute('data-teaching-type-id');
                    const checkbox = wrapper.querySelector('.class-checkbox');

                    if (!selectedTT || !classTT || classTT === selectedTT) {
                        wrapper.classList.remove('d-none');
                    } else {
                        wrapper.classList.add('d-none');
                        if (checkbox) checkbox.checked = false; // Uncheck hidden classes
                    }
                });
            }

            teachingTypeSelect.addEventListener('change', filterDeptAndClasses);
            filterDeptAndClasses();
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