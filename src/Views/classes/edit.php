<?php
$title = __('edit_class_title');
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
            <h2 class="fw-black text-main-theme mb-0 fs-4"><?= __('modify_class_room') ?></h2>
            <p class="text-muted-theme small mb-0"><?= h($classe['nom'] ?? '') ?></p>
        </div>
        <a href="/classes" class="btn btn-sm btn-light-theme rounded-pill px-3 border-theme-light">
            <i class="bi bi-arrow-left me-1"></i> <?= __('back_to_list') ?>
        </a>
    </div>

    <form action="/classes/update?id=<?= $classe['id'] ?>" method="POST" id="classEditForm" class="no-loader">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">

        <div class="subject-card-compact border-0 shadow-sm overflow-hidden mb-4">
            <div class="card-body p-4">

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 px-3 mb-4 fs-7">
                        <?= h($error) ?>
                    </div>
                <?php endif; ?>
                
                <!-- Stepper -->
                <div class="stepper-wrapper" id="stepper">
                    <div class="stepper-progress" id="stepperProgress" style="width: 0%;"></div>
                    <div class="stepper-item active" data-step="1">
                        <div class="stepper-circle">1</div>
                        <div class="stepper-title">Identification</div>
                    </div>
                    <div class="stepper-item" data-step="2">
                        <div class="stepper-circle">2</div>
                        <div class="stepper-title">Frais & Scolarité</div>
                    </div>
                    <div class="stepper-item" data-step="3">
                        <div class="stepper-circle">3</div>
                        <div class="stepper-title">Échéances</div>
                    </div>
                </div>

                <!-- Step 1: Identification -->
                <div class="form-step active" id="step1">
                    <div class="row g-4 mb-3">
                        <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                            <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1"><?= __('class_identification') ?></h6>
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('class_designation') ?> *</label>
                            <input type="text" name="nom" class="form-control premium-input" 
                                placeholder="<?= __('class_designation_placeholder') ?>" value="<?= h($classe['nom'] ?? '') ?>" required autofocus>
                            <div class="form-text extra-small mt-1 opacity-75"><?= __('class_naming_help') ?></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Type Enseignement *</label>
                            <select name="teaching_type_id" class="form-select premium-input border-primary border-opacity-25" required>
                                <option value="">Sélectionner un type</option>
                                <?php foreach ($teachingTypes as $tt): ?>
                                    <option value="<?= $tt['id'] ?>" <?= (($classe['teaching_type_id'] ?? '') == $tt['id']) ? 'selected' : '' ?>><?= h($tt['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('cycle_membership') ?></label>
                            <select name="cycle_id" id="cycle_id" class="form-select premium-input">
                                <option value=""><?= __('no_specific_cycle') ?></option>
                                <?php foreach ($cycles as $cy): ?>
                                    <option value="<?= $cy['id'] ?>" data-teaching-type-id="<?= $cy['teaching_type_id'] ?>" <?= (($classe['cycle_id'] ?? '') == $cy['id']) ? 'selected' : '' ?>><?= h($cy['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('section_stream') ?></label>
                            <select name="section_id" class="form-select premium-input">
                                <option value=""><?= __('general_no_section') ?></option>
                                <?php foreach ($sections as $sec): ?>
                                    <option value="<?= $sec['id'] ?>" <?= (($classe['section_id'] ?? '') == $sec['id']) ? 'selected' : '' ?>><?= h($sec['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('department') ?></label>
                            <select name="department_id" id="department_id" class="form-select premium-input">
                                <option value=""><?= __('no_department') ?></option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>" data-teaching-type-id="<?= $dept['teaching_type_id'] ?>" <?= (($classe['department_id'] ?? '') == $dept['id']) ? 'selected' : '' ?>>
                                        <?= h($dept['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('honor_roll_threshold_label') ?></label>
                            <input type="number" name="honor_roll_threshold" step="0.01" min="0" max="20" class="form-control premium-input"
                                value="<?= h($classe['honor_roll_threshold'] ?? '') ?>">
                            <div class="form-text extra-small mt-1 opacity-75"><?= __('honor_roll_threshold_help') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Frais & Scolarité -->
                <div class="form-step" id="step2">
                    <div class="row g-4 mb-3">
                        <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                            <h6 class="fw-black text-success m-0 text-uppercase letter-spacing-1">Configuration Financière</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Frais inscription Nouveau (FCFA)</label>
                            <input type="number" name="frais_inscription" min="0" step="50" class="form-control premium-input" id="frais_inscription"
                                value="<?= h($classe['frais_inscription'] ?? '0') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Frais réinscription Ancien (FCFA)</label>
                            <input type="number" name="frais_inscription_reinscription" min="0" step="50" class="form-control premium-input" id="frais_inscription_reinscription"
                                value="<?= h($classe['frais_inscription_reinscription'] ?? '0') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Frais de scolarité brut (FCFA)</label>
                            <input type="number" name="frais_scolarite_brut" min="0" step="50" class="form-control premium-input border-primary border-opacity-25" id="frais_scolarite_brut"
                                value="<?= h($classe['frais_scolarite_brut'] ?? '0') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Nombre de tranches</label>
                            <input type="number" name="nbr_tranches" min="0" max="10" class="form-control premium-input border-primary border-opacity-25" id="nbr_tranches"
                                value="<?= h($classe['nbr_tranches'] ?? '0') ?>">
                        </div>
                    </div>
                </div>

                <!-- Step 3: Échéances -->
                <div class="form-step" id="step3">
                    <div class="row g-4 mb-3">
                        <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                            <h6 class="fw-black text-warning m-0 text-uppercase letter-spacing-1">Configuration des Échéances de Paiement</h6>
                        </div>

                        <div class="col-12">
                            <div id="tranchesCard" class="card bg-light bg-opacity-50 border-0 rounded-3 mb-3" style="display: none;">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold text-secondary fs-7 mb-3 text-uppercase">Montant et Date d'échéance par tranche</h6>
                                    <div id="tranchesContainer" class="row g-3">
                                        <!-- Dynamic Inputs -->
                                    </div>
                                    <div class="mt-4 border-top pt-3 d-flex justify-content-between align-items-center fs-7 text-muted">
                                        <div>
                                            Frais de scolarité brut : <span id="fraisScolariteRef" class="fw-bold text-secondary">0</span> FCFA
                                        </div>
                                        <div>
                                            Total saisi : <span id="totalTranchesSaisi" class="fw-bold text-primary">0</span> FCFA
                                        </div>
                                    </div>
                                    <div id="tranchesError" class="alert alert-danger mt-2 py-2 px-3 fs-7" style="display: none;"></div>
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
document.addEventListener('DOMContentLoaded', function() {
    // Stepper Navigation
    let currentStep = 1;
    const totalSteps = 3;
    const form = document.getElementById('classEditForm');
    
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    const submitBtn = document.getElementById('submitBtn');
    const stepperProgress = document.getElementById('stepperProgress');

    const teachingTypeSelect = document.querySelector('select[name="teaching_type_id"]');
    const departmentSelect = document.getElementById('department_id');
    const cycleSelect = document.getElementById('cycle_id');
    const originalDeptOptions = departmentSelect ? Array.from(departmentSelect.options) : [];
    const originalCycleOptions = cycleSelect ? Array.from(cycleSelect.options) : [];

    function filterDepartmentsAndCycles() {
        const selectedType = teachingTypeSelect ? teachingTypeSelect.value : '';
        
        if (departmentSelect) {
            const currentDeptValue = departmentSelect.value;
            departmentSelect.innerHTML = '';
            let foundCurrentDept = false;
            originalDeptOptions.forEach(opt => {
                if (opt.value === '' || !selectedType || opt.dataset.teachingTypeId == selectedType || !opt.dataset.teachingTypeId) {
                    departmentSelect.appendChild(opt.cloneNode(true));
                    if (opt.value === currentDeptValue) {
                        foundCurrentDept = true;
                    }
                }
            });
            departmentSelect.value = foundCurrentDept ? currentDeptValue : '';
        }

        if (cycleSelect) {
            const currentCycleValue = cycleSelect.value;
            cycleSelect.innerHTML = '';
            let foundCurrentCycle = false;
            originalCycleOptions.forEach(opt => {
                if (opt.value === '' || !selectedType || opt.dataset.teachingTypeId == selectedType || !opt.dataset.teachingTypeId) {
                    cycleSelect.appendChild(opt.cloneNode(true));
                    if (opt.value === currentCycleValue) {
                        foundCurrentCycle = true;
                    }
                }
            });
            cycleSelect.value = foundCurrentCycle ? currentCycleValue : '';
        }
    }

    if (teachingTypeSelect) {
        teachingTypeSelect.addEventListener('change', filterDepartmentsAndCycles);
        filterDepartmentsAndCycles(); // Initial call
    }

    // Dynamic Tranches Logic
    const fraisScolariteInput = document.getElementById('frais_scolarite_brut');
    const nbrTranchesInput = document.getElementById('nbr_tranches');
    const tranchesCard = document.getElementById('tranchesCard');
    const tranchesContainer = document.getElementById('tranchesContainer');
    const totalTranchesSaisi = document.getElementById('totalTranchesSaisi');
    const fraisScolariteRef = document.getElementById('fraisScolariteRef');
    const tranchesError = document.getElementById('tranchesError');

    const preloadedTranches = <?= json_encode($classe['tranches'] ?? []) ?>;

    function generateTrancheInputs() {
        const nbr = parseInt(nbrTranchesInput.value) || 0;
        const totalFrais = parseFloat(fraisScolariteInput.value) || 0;

        tranchesContainer.innerHTML = '';
        if (nbr > 0 && totalFrais > 0) {
            tranchesCard.style.display = 'block';
            fraisScolariteRef.textContent = formatNumber(totalFrais);

            for (let i = 1; i <= nbr; i++) {
                const amountVal = preloadedTranches[i] && preloadedTranches[i].amount !== undefined ? preloadedTranches[i].amount : (preloadedTranches[i] !== undefined && typeof preloadedTranches[i] !== 'object' ? preloadedTranches[i] : '');
                const deadlineVal = preloadedTranches[i] && preloadedTranches[i].deadline !== undefined ? preloadedTranches[i].deadline : '';

                const colAmount = document.createElement('div');
                colAmount.className = 'col-md-6';
                colAmount.innerHTML = `
                    <label class="form-label extra-small text-muted mb-1">Montant Tranche ${i} (FCFA) *</label>
                    <input type="number" name="tranches[${i}][amount]" min="0" step="50" class="form-control premium-input tranche-amount-input" 
                        value="${amountVal}" required>
                `;

                const colDeadline = document.createElement('div');
                colDeadline.className = 'col-md-6';
                colDeadline.innerHTML = `
                    <label class="form-label extra-small text-muted mb-1">Échéance de paiement *</label>
                    <input type="date" name="tranches[${i}][deadline]" class="form-control premium-input tranche-deadline-input" 
                        value="${deadlineVal}" required>
                `;

                const rowGroup = document.createElement('div');
                rowGroup.className = 'col-12 border rounded-3 p-3 bg-white shadow-sm mb-2';

                const innerRow = document.createElement('div');
                innerRow.className = 'row g-3';
                innerRow.appendChild(colAmount);
                innerRow.appendChild(colDeadline);

                rowGroup.appendChild(innerRow);
                tranchesContainer.appendChild(rowGroup);
            }

            document.querySelectorAll('.tranche-amount-input').forEach(input => {
                input.addEventListener('input', calculateTotalTranches);
            });
            calculateTotalTranches();
        } else {
            tranchesCard.style.display = 'none';
        }
    }

    function calculateTotalTranches() {
        let total = 0;
        document.querySelectorAll('.tranche-amount-input').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        totalTranchesSaisi.textContent = formatNumber(total);
        const totalFrais = parseFloat(fraisScolariteInput.value) || 0;

        if (Math.abs(total - totalFrais) > 0.01) {
            tranchesError.textContent = `La somme des tranches (${formatNumber(total)} FCFA) diffère des frais de scolarité (${formatNumber(totalFrais)} FCFA).`;
            tranchesError.style.display = 'block';
        } else {
            tranchesError.style.display = 'none';
        }
    }

    function formatNumber(val) {
        return new Intl.NumberFormat('fr-FR').format(val);
    }

    fraisScolariteInput.addEventListener('input', generateTrancheInputs);
    nbrTranchesInput.addEventListener('input', generateTrancheInputs);

    // Initial load if tranches exist
    if (parseInt(nbrTranchesInput.value) > 0) {
        generateTrancheInputs();
    }

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

        if (currentStep === 3) {
            generateTrancheInputs();
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

    // Make stepper items freely clickable if valid
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
        const totalFrais = parseFloat(fraisScolariteInput.value) || 0;
        const nbr = parseInt(nbrTranchesInput.value) || 0;

        if (totalFrais > 0 && nbr > 0) {
            let total = 0;
            document.querySelectorAll('.tranche-amount-input').forEach(input => {
                total += parseFloat(input.value) || 0;
            });

            if (Math.abs(total - totalFrais) > 0.01) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur de validation',
                    text: `La somme des tranches (${formatNumber(total)} FCFA) doit être égale aux frais de scolarité brut (${formatNumber(totalFrais)} FCFA).`,
                    confirmButtonColor: '#2563EB'
                });
                return;
            }

            // check empty deadlines
            let emptyDeadline = false;
            document.querySelectorAll('.tranche-deadline-input').forEach(input => {
                if (!input.value) {
                    emptyDeadline = true;
                    input.classList.add('is-invalid');
                    input.addEventListener('input', function() {
                        this.classList.remove('is-invalid');
                    }, { once: true });
                }
            });

            if (emptyDeadline) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Échéances manquantes',
                    text: 'Veuillez renseigner toutes les dates d\'échéance de paiement.',
                    confirmButtonColor: '#2563EB'
                });
            }
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
