<?php
$title = __('modify_student_profile');
$selectedSexe = (string) ($student['sexe'] ?? '');
$selectedDepartment = (string) ($student['department_id'] ?? '');
$selectedTeachingType = (string) ($student['teaching_type_id'] ?? '');
$isRedoublant = (string) ((int) ($student['is_redoublant'] ?? 0));
ob_start();
?>

<style>
/* Stepper CSS (Respecting Global Theme) */
.stepper-wrapper {
  display: flex;
  justify-content: space-between;
  margin-bottom: 30px;
  position: relative;
  padding: 0 10px;
}
.stepper-wrapper::before {
  content: '';
  position: absolute;
  top: 20px;
  left: 30px;
  right: 30px;
  height: 2px;
  background: var(--bs-border-color);
  z-index: 0;
}
.stepper-progress {
  position: absolute;
  top: 20px;
  left: 30px;
  height: 2px;
  background: var(--bs-primary);
  z-index: 0;
  transition: width 0.4s ease;
}
.stepper-item {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
  cursor: pointer;
}
.stepper-circle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--bs-body-bg);
  border: 2px solid var(--bs-border-color);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: var(--bs-body-color);
  transition: all 0.3s ease;
}
.stepper-item.active .stepper-circle {
  background: var(--bs-primary);
  border-color: var(--bs-primary);
  color: #fff;
  transform: scale(1.1);
}
.stepper-item.completed .stepper-circle {
  background: var(--bs-success);
  border-color: var(--bs-success);
  color: #fff;
}
.stepper-title {
  margin-top: 8px;
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--bs-secondary-color);
  text-transform: uppercase;
  transition: color 0.3s ease;
}
.stepper-item.active .stepper-title {
  color: var(--bs-primary);
}
.stepper-item.completed .stepper-title {
  color: var(--bs-success);
}
.form-step {
  display: none;
  animation: fadeIn 0.4s ease forwards;
}
.form-step.active {
  display: block;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<div class="animate-fade-in container-fluid py-2">
    <!-- Compact Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h2 class="fw-black text-main-theme mb-0 fs-4"><?= __('modify_learner') ?></h2>
            <p class="text-muted-theme small mb-0"><?= h($student['nom'] . ' ' . $student['prenom']) ?> •
                <?= h($student['email']) ?></p>
        </div>
        <a href="/students" class="btn btn-sm btn-light-theme rounded-pill px-3 border-theme-light">
            <i class="bi bi-arrow-left me-1"></i> <?= __('back_to_list') ?>
        </a>
    </div>

    <form action="/students/update?id=<?= $student['id'] ?>" method="POST" id="studentEditForm" enctype="multipart/form-data" class="no-loader">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">

        <div class="subject-card-compact border-0 shadow-sm overflow-hidden mb-4">
            <div class="card-body p-4">

                <!-- Stepper -->
                <div class="stepper-wrapper" id="stepper">
                    <div class="stepper-progress" id="stepperProgress" style="width: 0%;"></div>
                    <div class="stepper-item active" data-step="1">
                        <div class="stepper-circle">1</div>
                        <div class="stepper-title"><?= __('learner_identity') ?></div>
                    </div>
                    <div class="stepper-item" data-step="2">
                        <div class="stepper-circle">2</div>
                        <div class="stepper-title"><?= __('student_photo') ?></div>
                    </div>
                    <div class="stepper-item" data-step="3">
                        <div class="stepper-circle">3</div>
                        <div class="stepper-title"><?= __('academic_assignment') ?></div>
                    </div>
                </div>

                <!-- Step 1: Identity Section -->
                <div class="form-step active" id="step1">
                    <div class="row g-4 mb-3">
                        <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                            <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1">
                                <?= __('learner_identity') ?>
                            </h6>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('family_name') ?></label>
                            <input type="text" name="nom" class="form-control premium-input"
                                placeholder="<?= __('name_placeholder') ?>" value="<?= h($student['nom'] ?? '') ?>"
                                required autofocus>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('first_names') ?></label>
                            <input type="text" name="prenom" class="form-control premium-input"
                                placeholder="<?= __('first_name_placeholder') ?>" value="<?= h($student['prenom'] ?? '') ?>"
                                required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('matricule') ?></label>
                            <input type="text" name="email" class="form-control premium-input border-primary border-opacity-25 fw-black"
                                value="<?= h($student['email'] ?? '') ?>"
                                placeholder="Laisser vide pour générer automatiquement">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('sex') ?></label>
                            <select name="sexe" class="form-select premium-input" required>
                                <option value="" disabled>Sélectionner...</option>
                                <option value="M" <?= $selectedSexe === 'M' ? 'selected' : '' ?>><?= __('male') ?></option>
                                <option value="F" <?= $selectedSexe === 'F' ? 'selected' : '' ?>><?= __('female') ?></option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('birth_date_full') ?></label>
                            <input type="date" name="date_naissance" class="form-control premium-input"
                                value="<?= h($student['date_naissance'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('birth_place_full') ?></label>
                            <input type="text" name="lieu_naissance" class="form-control premium-input"
                                placeholder="Lieu de naissance" value="<?= h($student['lieu_naissance'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('parent_contact') ?? 'Contact Père/Mère' ?></label>
                            <input type="tel" name="parent_contact" class="form-control premium-input" 
                                placeholder="+237 600000000" value="<?= h($student['parent_contact'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('guardian_contact') ?? 'Contact Tuteur' ?></label>
                            <input type="tel" name="guardian_contact" class="form-control premium-input" 
                                placeholder="+237 600000000" value="<?= h($student['guardian_contact'] ?? '') ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Adresse</label>
                            <input type="text" name="adresse" class="form-control premium-input" 
                                placeholder="Adresse complète (Quartier, Rue, etc.)" value="<?= h($student['adresse'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Step 2: Photo Section -->
                <div class="form-step" id="step2">
                    <div class="row g-4 mb-3">
                        <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                            <h6 class="fw-black text-info m-0 text-uppercase letter-spacing-1"><?= __('student_photo') ?></h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('current_photo') ?></label>
                            <div class="d-flex align-items-center gap-3">
                                <?php if (!empty($student['photo_eleve'])): ?>
                                    <div class="border rounded overflow-hidden" style="width: 120px; height: 120px;">
                                        <?php 
                                        $photoPath = $student['photo_eleve'];
                                        if (strpos($photoPath, '/') !== 0) {
                                            $photoPath = '/' . $photoPath;
                                        }
                                        if (strpos($photoPath, '/public/uploads/') === 0) {
                                            // Already correct
                                        } elseif (strpos($photoPath, '/uploads/') === 0) {
                                            $photoPath = '/public' . $photoPath;
                                        }
                                        ?>
                                        <img src="<?= $photoPath ?>" alt="Photo actuelle" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'; this.parentElement.innerHTML='<span class=\\'text-muted small text-center px-2\\'>Erreur chargement</span>';">
                                    </div>
                                    <div>
                                        <button type="submit" name="delete_photo" value="1" class="btn btn-sm btn-outline-danger rounded-pill">
                                            <i class="bi bi-trash me-1"></i> <?= __('delete_photo') ?>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="border rounded d-flex align-items-center justify-content-center bg-light" style="width: 120px; height: 120px;">
                                        <span class="text-muted small text-center px-2"><?= __('no_photo') ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('replace_photo') ?></label>
                            <input type="file" name="photo_eleve" class="form-control premium-input mb-2" accept="image/jpeg,image/jpg,image/png,image/webp" id="photoInputEdit">
                            <div class="form-text small text-muted mb-3">
                                <?= __('photo_formats') ?>: JPG, JPEG, PNG, WEBP<br>
                                <?= __('photo_max_size') ?>: 5MB
                            </div>
                            
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Aperçu de la nouvelle photo</label>
                            <div id="photoPreviewContainer" class="border rounded d-flex align-items-center justify-content-center bg-light" style="width: 120px; height: 120px; overflow: hidden;">
                                <span class="text-muted small text-center px-2">Aucune nouvelle photo sélectionnée</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Academic Section -->
                <div class="form-step" id="step3">
                    <div class="row g-4">
                        <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                            <h6 class="fw-black text-success m-0 text-uppercase letter-spacing-1">
                                <?= __('academic_assignment') ?>
                            </h6>
                        </div>

                        <!-- Left Column: Academic Structure Filters -->
                        <div class="col-lg-8">
                            <div class="card bg-light bg-opacity-50 border-theme-light rounded-4 p-4 shadow-sm">
                                <span class="fw-bold text-muted-theme extra-small text-uppercase mb-3 d-block">
                                    <i class="bi bi-funnel text-success me-1"></i> Filtrer par structure académique
                                </span>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Type Enseignement</label>
                                        <select id="teaching_type_select" name="teaching_type_id" class="form-select premium-input">
                                            <option value="">Tous les types</option>
                                            <?php foreach ($teachingTypes as $tt): ?>
                                                <option value="<?= $tt['id'] ?>" <?= $selectedTeachingType === (string) $tt['id'] ? 'selected' : '' ?>><?= h($tt['nom']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('cycle_membership_label') ?></label>
                                        <select id="cycle_select" name="cycle_id" class="form-select premium-input">
                                            <option value=""><?= __('all_cycles') ?></option>
                                            <?php foreach ($cycles as $cy): ?>
                                                <option value="<?= $cy['id'] ?>" <?= (string) ($student['cycle_id'] ?? '') === (string) $cy['id'] ? 'selected' : '' ?>><?= h($cy['nom']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('section_stream') ?></label>
                                        <select id="section_select" name="section_id" class="form-select premium-input">
                                            <option value=""><?= __('all_sections') ?></option>
                                            <?php foreach ($sections as $sec): ?>
                                                <option value="<?= $sec['id'] ?>" <?= (string) ($student['section_id'] ?? '') === (string) $sec['id'] ? 'selected' : '' ?>><?= h($sec['nom']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('department') ?></label>
                                        <select id="department_select" name="department_id" class="form-select premium-input">
                                            <option value=""><?= __('all_departments') ?? 'Tous les départements' ?></option>
                                            <?php foreach ($departments as $dept): ?>
                                                <option value="<?= $dept['id'] ?>" data-teaching-type="<?= $dept['teaching_type_id'] ?? '' ?>" <?= $selectedDepartment === (string) $dept['id'] ? 'selected' : '' ?>><?= h($dept['nom']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Final Class Selection & Status -->
                        <div class="col-lg-4">
                            <div class="card border-success border-opacity-25 shadow-sm rounded-4 p-4 h-100 bg-success bg-opacity-5">
                                <div class="mb-4">
                                    <label class="form-label text-success fw-black extra-small text-uppercase mb-1 d-flex align-items-center">
                                        <i class="bi bi-door-open-fill me-1"></i> <?= __('student_class_label') ?> *
                                    </label>
                                    <select name="class_id" id="class_select"
                                        class="form-select premium-input border-success fw-bold" required
                                        data-current="<?= h($student['class_id'] ?? '') ?>">
                                        <option value=""><?= __('select_class') ?></option>
                                        <?php foreach ($classes as $cla): ?>
                                            <option value="<?= $cla['id'] ?>" data-teaching-type="<?= $cla['teaching_type_id'] ?>" data-cycle="<?= $cla['cycle_id'] ?>"
                                                data-section="<?= $cla['section_id'] ?>" data-department="<?= $cla['department_id'] ?>"><?= h($cla['nom']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text extra-small text-muted mt-1">La classe assignée à l'élève.</div>
                                </div>

                                <div>
                                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">
                                        <i class="bi bi-arrow-repeat me-1"></i> <?= __('repeat_status') ?>
                                    </label>
                                    <div class="d-flex gap-2">
                                        <div class="flex-grow-1">
                                            <input type="radio" class="btn-check" name="is_redoublant" id="red_no" value="0"
                                                <?= $isRedoublant !== '1' ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-secondary w-100 rounded-pill btn-sm py-2 fw-semibold"
                                                for="red_no"><?= __('no') ?></label>
                                        </div>
                                        <div class="flex-grow-1">
                                            <input type="radio" class="btn-check" name="is_redoublant" id="red_yes" value="1"
                                                <?= $isRedoublant === '1' ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-warning w-100 rounded-pill btn-sm py-2 fw-semibold"
                                                for="red_yes"><?= __('yes') ?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Footer -->
                <div class="d-flex justify-content-between border-top border-theme-light pt-4 mt-2">
                    <button type="button" class="btn btn-light-theme rounded-pill px-4" id="prevBtn" style="display: none;">
                        <i class="bi bi-arrow-left me-1"></i> Précédent
                    </button>
                    <div class="ms-auto">
                        <button type="button" class="btn btn-primary rounded-pill px-4" id="nextBtn">
                            Suivant <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                        <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm transition-base scale-on-hover" id="submitBtn" style="display: none;">
                            <i class="bi bi-check-circle-fill me-2"></i> Valider
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let currentStep = 1;
    const totalSteps = 3;
    const form = document.getElementById('studentEditForm');
    
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    const submitBtn = document.getElementById('submitBtn');
    const stepperProgress = document.getElementById('stepperProgress');

    function updateStepper() {
        // Update Steps visibility
        document.querySelectorAll('.form-step').forEach(step => {
            step.classList.remove('active');
        });
        document.getElementById('step' + currentStep).classList.add('active');
        
        // Update Navigation Buttons
        prevBtn.style.display = currentStep > 1 ? 'inline-block' : 'none';
        
        if (currentStep === totalSteps) {
            nextBtn.style.display = 'none';
            submitBtn.style.display = 'inline-block';
        } else {
            nextBtn.style.display = 'inline-block';
            submitBtn.style.display = 'none';
        }
        
        // Update Stepper UI
        const items = document.querySelectorAll('.stepper-item');
        items.forEach((item, index) => {
            const stepNum = index + 1;
            item.classList.remove('active', 'completed');
            
            if (stepNum < currentStep) {
                item.classList.add('completed');
                item.querySelector('.stepper-circle').innerHTML = '<i class="bi bi-check-lg"></i>';
            } else if (stepNum === currentStep) {
                item.classList.add('active');
                item.querySelector('.stepper-circle').innerHTML = stepNum;
            } else {
                item.querySelector('.stepper-circle').innerHTML = stepNum;
            }
        });
        
        // Update Progress Bar
        const progressPercentage = ((currentStep - 1) / (totalSteps - 1)) * 100;
        stepperProgress.style.width = progressPercentage + '%';
    }

    function validateCurrentStep() {
        const currentStepEl = document.getElementById('step' + currentStep);
        const inputs = currentStepEl.querySelectorAll('input[required], select[required]');
        let isValid = true;
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('is-invalid');
                input.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                }, { once: true });
            }
        });
        
        return isValid;
    }
    
    nextBtn.addEventListener('click', () => {
        if (validateCurrentStep()) {
            if (currentStep < totalSteps) {
                currentStep++;
                updateStepper();
            }
        }
    });
    
    prevBtn.addEventListener('click', () => {
        if (currentStep > 1) {
            currentStep--;
            updateStepper();
        }
    });

    // Make stepper items freely clickable if valid or already completed
    document.querySelectorAll('.stepper-item').forEach((item, index) => {
        item.addEventListener('click', () => {
            const targetStep = index + 1;
            if (targetStep < currentStep) {
                currentStep = targetStep;
                updateStepper();
            } else {
                if (validateCurrentStep()) {
                    currentStep = targetStep;
                    updateStepper();
                }
            }
        });
    });

    // Final Validation on Submit
    form.addEventListener('submit', function(e) {
        // If delete_photo button triggered the submit, skip fields validation
        const submitter = e.submitter;
        if (submitter && submitter.name === 'delete_photo') {
            return;
        }

        const requiredInputs = form.querySelectorAll('input[required], select[required]');
        let isValid = true;
        let firstInvalidStep = null;

        requiredInputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('is-invalid');
                
                const stepEl = input.closest('.form-step');
                if (stepEl && !firstInvalidStep) {
                    firstInvalidStep = parseInt(stepEl.id.replace('step', ''));
                }
                
                input.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                }, { once: true });
            }
        });

        if (!isValid) {
            e.preventDefault();
            if (firstInvalidStep && firstInvalidStep !== currentStep) {
                currentStep = firstInvalidStep;
                updateStepper();
            }
        }
    });

    // Dropdown Filtering Logic
    const teachingTypeSelect = document.getElementById('teaching_type_select');
    const cycleSelect = document.getElementById('cycle_select');
    const sectionSelect = document.getElementById('section_select');
    const departmentSelect = document.getElementById('department_select');
    const classSelect = document.getElementById('class_select');
    const currentClassId = classSelect.getAttribute('data-current') || '';
    
    const labels = <?= json_encode([
        'selectClass' => __('select_class'),
        'noClassForCriteria' => __('no_class_for_criteria'),
        'allDepartments' => __('all_departments') ?? 'Tous les départements',
    ], JSON_UNESCAPED_UNICODE) ?>;

    const originalOptions = Array.from(classSelect.options).filter(opt => opt.value !== '');
    const originalDeptOptions = Array.from(departmentSelect.options).filter(opt => opt.value !== '');

    function filterDepartments() {
        if (!teachingTypeSelect) return;
        const selectedTeachingType = teachingTypeSelect.value;
        const currentDeptId = departmentSelect.value;
        
        departmentSelect.innerHTML = '<option value="">' + labels.allDepartments + '</option>';
        
        let deptFoundSelected = false;
        
        originalDeptOptions.forEach(opt => {
            const optTeachingType = opt.getAttribute('data-teaching-type');
            const matchTeachingType = !selectedTeachingType || !optTeachingType || optTeachingType === selectedTeachingType;
            
            if (matchTeachingType) {
                const clonedOption = opt.cloneNode(true);
                if (clonedOption.value === currentDeptId) {
                    clonedOption.selected = true;
                    deptFoundSelected = true;
                }
                departmentSelect.appendChild(clonedOption);
            }
        });
        
        if (currentDeptId && !deptFoundSelected) {
            departmentSelect.value = '';
        }
        
        filterClasses();
    }

    function filterClasses() {
        const selectedTeachingType = teachingTypeSelect.value;
        const selectedCycle = cycleSelect.value;
        const selectedSection = sectionSelect.value;
        const selectedDept = departmentSelect.value;
        classSelect.innerHTML = '<option value="">' + labels.selectClass + '</option>';

        let addedCount = 0;
        originalOptions.forEach(opt => {
            const matchTeachingType = !selectedTeachingType || opt.getAttribute('data-teaching-type') === selectedTeachingType;
            const matchCycle = !selectedCycle || opt.getAttribute('data-cycle') === selectedCycle;
            const matchSection = !selectedSection || opt.getAttribute('data-section') === selectedSection;
            const matchDept = !selectedDept || opt.getAttribute('data-department') === selectedDept;

            if (matchTeachingType && matchCycle && matchSection && matchDept) {
                const clonedOption = opt.cloneNode(true);
                if (clonedOption.value === currentClassId) clonedOption.selected = true;
                classSelect.appendChild(clonedOption);
                addedCount++;
            }
        });

        if (addedCount === 0 && (selectedTeachingType || selectedCycle || selectedSection || selectedDept)) {
            classSelect.innerHTML = '<option value="">' + labels.noClassForCriteria + '</option>';
        }
    }

    if (teachingTypeSelect) teachingTypeSelect.addEventListener('change', filterDepartments);
    if (cycleSelect) cycleSelect.addEventListener('change', filterClasses);
    sectionSelect.addEventListener('change', filterClasses);
    departmentSelect.addEventListener('change', filterClasses);
    if (teachingTypeSelect) filterDepartments(); else filterClasses();

    // Photo preview for edit form
    const photoInputEdit = document.getElementById('photoInputEdit');
    const photoPreviewContainer = document.getElementById('photoPreviewContainer');
    
    if (photoInputEdit && photoPreviewContainer) {
        photoInputEdit.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    photoPreviewContainer.innerHTML = '<img src="' + e.target.result + '" alt="Aperçu" style="width: 100%; height: 100%; object-fit: cover;">';
                };
                reader.readAsDataURL(file);
            } else {
                photoPreviewContainer.innerHTML = '<span class="text-muted small text-center px-2">Aucune nouvelle photo sélectionnée</span>';
            }
        });
    }

    // Initial setup
    updateStepper();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>