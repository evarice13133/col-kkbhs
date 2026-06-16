<?php
$title = __('add_class_title');
ob_start();
?>

<div class="animate-fade-in container-fluid py-2">
    <!-- Compact Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="fw-black text-main-theme mb-0 fs-4"><?= __('build_active_class') ?></h2>
        <a href="/classes" class="btn btn-sm btn-light-theme rounded-pill px-3 border-theme-light">
            <i class="bi bi-arrow-left me-1"></i> <?= __('back_to_list') ?>
        </a>
    </div>

    <form action="/classes/store" method="POST" id="classCreateForm">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">

        <div class="subject-card-compact border-0 shadow-sm overflow-hidden mb-4">
            <div class="card-body p-4">
                
                <!-- Identification Section -->
                <div class="row g-4 mb-4">
                    <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                        <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1"><?= __('class_identification') ?></h6>
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('class_designation') ?></label>
                        <input type="text" name="nom" class="form-control premium-input" 
                            placeholder="<?= __('class_designation_placeholder') ?>" required autofocus>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Type Enseignement *</label>
                        <select name="teaching_type_id" class="form-select premium-input border-primary border-opacity-25" required>
                            <option value="">Sélectionner un type</option>
                            <?php foreach ($teachingTypes as $tt): ?>
                                <option value="<?= $tt['id'] ?>"><?= h($tt['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('cycle_membership') ?></label>
                        <select name="cycle_id" class="form-select premium-input">
                            <option value=""><?= __('no_specific_cycle') ?></option>
                            <?php foreach ($cycles as $cy): ?>
                                <option value="<?= $cy['id'] ?>"><?= h($cy['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('section_stream') ?></label>
                        <select name="section_id" class="form-select premium-input">
                            <option value=""><?= __('general_no_section') ?></option>
                            <?php foreach ($sections as $sec): ?>
                                <option value="<?= $sec['id'] ?>"><?= h($sec['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('department') ?></label>
                        <select name="department_id" id="department_id" class="form-select premium-input">
                            <option value=""><?= __('no_department') ?></option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>" data-teaching-type-id="<?= $dept['teaching_type_id'] ?>">
                                    <?= h($dept['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('honor_roll_threshold_label') ?></label>
                        <input type="number" name="honor_roll_threshold" step="0.01" min="0" max="20" class="form-control premium-input"
                            value="<?= htmlspecialchars((string) ($classe['honor_roll_threshold'] ?? '')) ?>">
                        <div class="form-text extra-small mt-1 opacity-75"><?= __('honor_roll_threshold_help') ?></div>
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="d-flex justify-content-end border-top border-theme-light pt-4 mt-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm transition-base scale-on-hover">
                        <i class="bi bi-check-circle-fill me-2"></i> Valider
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

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

    teachingTypeSelect.addEventListener('change', filterDepartments);
    filterDepartments(); // Initial call
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
