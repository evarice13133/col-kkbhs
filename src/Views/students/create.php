<?php
$title = __('enroll_student');
$formData = $formData ?? [];
$selectedCycle = (string) ($formData['cycle_id'] ?? '');
$selectedSection = (string) ($formData['section_id'] ?? '');
$selectedDepartment = (string) ($formData['department_id'] ?? '');
$selectedClass = (string) ($formData['class_id'] ?? '');
$selectedTeachingType = (string) ($formData['teaching_type_id'] ?? '');
$selectedSexe = (string) ($formData['sexe'] ?? '');
$isRedoublant = (string) ($formData['is_redoublant'] ?? '0');
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
        <h2 class="fw-black text-main-theme mb-0 fs-4"><?= __('enroll_student') ?></h2>
        <a href="/students" class="btn btn-sm btn-light-theme rounded-pill px-3 border-theme-light">
            <i class="bi bi-arrow-left me-1"></i> <?= __('back_to_list') ?>
        </a>
    </div>

    <form action="/students/store" method="POST" id="studentEnrollForm" enctype="multipart/form-data" class="no-loader">
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
                            <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1"><?= __('learner_identity') ?></h6>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('family_name') ?></label>
                            <input type="text" name="nom" class="form-control premium-input" 
                                placeholder="<?= __('name_placeholder') ?>" value="<?= h($formData['nom'] ?? '') ?>" required autofocus>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('first_names') ?></label>
                            <input type="text" name="prenom" class="form-control premium-input" 
                                placeholder="<?= __('first_name_placeholder') ?>" value="<?= h($formData['prenom'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('matricule') ?></label>
                            <input type="text" name="email" class="form-control premium-input border-primary border-opacity-25" 
                                placeholder="Optionnel (Généré si vide)" value="<?= h($formData['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('sex') ?></label>
                            <select name="sexe" class="form-select premium-input" required>
                                <option value="" disabled selected>Sélectionner...</option>
                                <option value="M" <?= $selectedSexe === 'M' ? 'selected' : '' ?>><?= __('male') ?></option>
                                <option value="F" <?= $selectedSexe === 'F' ? 'selected' : '' ?>><?= __('female') ?></option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('birth_date_full') ?></label>
                            <input type="date" name="date_naissance" class="form-control premium-input" value="<?= h($formData['date_naissance'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('birth_place_full') ?></label>
                            <input type="text" name="lieu_naissance" class="form-control premium-input" 
                                placeholder="Lieu de naissance" value="<?= h($formData['lieu_naissance'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('parent_contact') ?? 'Contact Père/Mère' ?></label>
                            <input type="tel" name="parent_contact" class="form-control premium-input" 
                                placeholder="+237 600000000" value="<?= h($formData['parent_contact'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('guardian_contact') ?? 'Contact Tuteur' ?></label>
                            <input type="tel" name="guardian_contact" class="form-control premium-input" 
                                placeholder="+237 600000000" value="<?= h($formData['guardian_contact'] ?? '') ?>">
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
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('photo_upload') ?> <span class="text-muted">(<?= __('optional') ?>)</span></label>
                            <input type="file" name="photo_eleve" class="form-control premium-input" accept="image/jpeg,image/jpg,image/png,image/webp" id="photoInput">
                            <div class="form-text small text-muted">
                                <?= __('photo_formats') ?>: JPG, JPEG, PNG, WEBP<br>
                                <?= __('photo_max_size') ?>: 5MB
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('photo_preview') ?></label>
                            <div id="photoPreview" class="border rounded d-flex align-items-center justify-content-center bg-light" style="width: 150px; height: 150px; overflow: hidden;">
                                <span class="text-muted small text-center px-2"><?= __('no_photo_selected') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Academic Section -->
                <div class="form-step" id="step3">
                    <div class="row g-4 mb-3">
                        <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                            <h6 class="fw-black text-success m-0 text-uppercase letter-spacing-1"><?= __('academic_assignment') ?></h6>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1 opacity-50">Type Enseignement</label>
                            <select id="teaching_type_select" name="teaching_type_id" class="form-select premium-input">
                                <option value="">Tous les types</option>
                                <?php foreach ($teachingTypes as $tt): ?>
                                    <option value="<?= $tt['id'] ?>" <?= $selectedTeachingType === (string) $tt['id'] ? 'selected' : '' ?>><?= h($tt['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1 opacity-50"><?= __('cycle_membership_label') ?></label>
                            <select id="cycle_select" name="cycle_id" class="form-select premium-input">
                                <option value=""><?= __('all_cycles') ?></option>
                                <?php foreach ($cycles as $cy): ?>
                                    <option value="<?= $cy['id'] ?>" <?= $selectedCycle === (string) $cy['id'] ? 'selected' : '' ?>><?= h($cy['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1 opacity-50"><?= __('section_stream') ?></label>
                            <select id="section_select" name="section_id" class="form-select premium-input">
                                <option value=""><?= __('all_sections') ?></option>
                                <?php foreach ($sections as $sec): ?>
                                    <option value="<?= $sec['id'] ?>" <?= $selectedSection === (string) $sec['id'] ? 'selected' : '' ?>><?= h($sec['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1 opacity-50"><?= __('department') ?></label>
                            <select id="department_select" name="department_id" class="form-select premium-input">
                                <option value=""><?= __('all_departments') ?? 'Tous les départements' ?></option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>" data-teaching-type="<?= $dept['teaching_type_id'] ?? '' ?>" <?= $selectedDepartment === (string) $dept['id'] ? 'selected' : '' ?>><?= h($dept['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-primary fw-black extra-small text-uppercase mb-1"><?= __('student_class_label') ?> *</label>
                            <select name="class_id" id="class_select" class="form-select premium-input border-primary border-opacity-25" required data-current="<?= h($selectedClass) ?>">
                                <option value=""><?= __('select_class') ?></option>
                                <?php foreach ($classes as $cla): ?>
                                    <option value="<?= $cla['id'] ?>" data-teaching-type="<?= $cla['teaching_type_id'] ?>" data-cycle="<?= $cla['cycle_id'] ?>" data-section="<?= $cla['section_id'] ?>" data-department="<?= $cla['department_id'] ?>"><?= h($cla['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-12 mt-4">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2"><?= __('repeat_status') ?></label>
                            <div class="d-flex gap-2">
                                <div class="flex-grow-1" style="max-width: 150px;">
                                    <input type="radio" class="btn-check" name="is_redoublant" id="red_no" value="0" <?= $isRedoublant !== '1' ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-secondary btn-sm w-100 rounded-pill" for="red_no"><?= __('no') ?></label>
                                </div>
                                <div class="flex-grow-1" style="max-width: 150px;">
                                    <input type="radio" class="btn-check" name="is_redoublant" id="red_yes" value="1" <?= $isRedoublant === '1' ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-warning btn-sm w-100 rounded-pill" for="red_yes"><?= __('yes') ?></label>
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
    // Stepper Logic
    let currentStep = 1;
    const totalSteps = 3;
    const form = document.getElementById('studentEnrollForm');
    
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
        validateCurrentStep(); // Visual feedback only
        if (currentStep < totalSteps) {
            currentStep++;
            updateStepper();
        }
    });
    
    prevBtn.addEventListener('click', () => {
        if (currentStep > 1) {
            currentStep--;
            updateStepper();
        }
    });

    // Make stepper items freely clickable
    document.querySelectorAll('.stepper-item').forEach((item, index) => {
        item.addEventListener('click', () => {
            currentStep = index + 1;
            updateStepper();
        });
    });

    // Final Validation on Submit
    form.addEventListener('submit', function(e) {
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

    // Photo preview
    const photoInput = document.getElementById('photoInput');
    const photoPreview = document.getElementById('photoPreview');
    
    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                photoPreview.innerHTML = '<img src="' + e.target.result + '" alt="Aperçu" style="width: 100%; height: 100%; object-fit: cover;">';
            };
            reader.readAsDataURL(file);
        } else {
            photoPreview.innerHTML = '<span class="text-muted small text-center px-2"><?= __('no_photo_selected') ?></span>';
        }
    });

    // Initial setup
    updateStepper();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
