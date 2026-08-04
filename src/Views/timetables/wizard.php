<?php
$title = __('timetables_wizard_title') . " - " . __('app_name');
ob_start();
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-black text-main-theme mb-0 fs-4">
                <i class="bi bi-magic text-primary me-2"></i><?= __('timetables_wizard_title') ?>
            </h2>
            <p class="text-muted-theme small mb-0"><?= __('timetables_wizard_subtitle') ?></p>
        </div>
        <a href="/timetables" class="btn btn-sm btn-light-theme rounded-pill px-3 py-2 fw-semibold border-theme-light shadow-sm">
            <i class="bi bi-arrow-left me-1"></i><?= __('cancel') ?? 'Retour' ?>
        </a>
    </div>

    <!-- Wizard Stepper Nav -->
    <div class="modern-card border-0 shadow-sm p-4 mb-4">
        <div class="row text-center g-2 position-relative">
            <div class="col wizard-step-item active" id="step-nav-1">
                <div class="wizard-icon-circle mx-auto mb-2"><i class="bi bi-mortarboard-fill"></i></div>
                <div class="fw-bold small text-main-theme"><?= __('timetables_step_1') ?></div>
            </div>
            <div class="col wizard-step-item opacity-50" id="step-nav-2">
                <div class="wizard-icon-circle mx-auto mb-2"><i class="bi bi-diagram-3-fill"></i></div>
                <div class="fw-bold small text-main-theme"><?= __('timetables_step_2') ?></div>
            </div>
            <div class="col wizard-step-item opacity-50" id="step-nav-3">
                <div class="wizard-icon-circle mx-auto mb-2"><i class="bi bi-door-open-fill"></i></div>
                <div class="fw-bold small text-main-theme"><?= __('timetables_step_3') ?></div>
            </div>
            <div class="col wizard-step-item opacity-50" id="step-nav-4">
                <div class="wizard-icon-circle mx-auto mb-2"><i class="bi bi-calendar-range-fill"></i></div>
                <div class="fw-bold small text-main-theme"><?= __('timetables_step_4') ?></div>
            </div>
            <div class="col wizard-step-item opacity-50" id="step-nav-5">
                <div class="wizard-icon-circle mx-auto mb-2"><i class="bi bi-grid-3x3-gap-fill"></i></div>
                <div class="fw-bold small text-main-theme"><?= __('timetables_step_5') ?></div>
            </div>
        </div>
    </div>

    <!-- Wizard Form Container -->
    <div class="modern-card border-0 shadow-sm p-4">
        <form id="wizardForm" method="POST" action="/timetables/wizard/generate">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">

            <!-- Étape 1 : Type d'enseignement -->
            <div class="wizard-tab-content" id="tab-step-1">
                <h5 class="fw-black text-main-theme mb-1"><?= __('timetables_wizard_step1_title') ?></h5>
                <p class="text-muted small mb-4"><?= __('timetables_wizard_step1_desc') ?></p>

                <div class="row g-3">
                    <?php foreach ($teachingTypes as $tt): ?>
                        <div class="col-md-6">
                            <div class="card card-theme h-100 border-2 select-card p-3 rounded-4 cursor-pointer" 
                                 onclick="selectTeachingType(<?= $tt['id'] ?>, this)">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-init bg-primary bg-opacity-10 text-primary me-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        <i class="bi bi-building-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1 text-main-theme"><?= h($tt['nom']) ?></h6>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-0.5 rounded-pill small">Code: <?= h($tt['code']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="teaching_type_id" id="input_teaching_type_id" required>
            </div>

            <!-- Étape 2 : Cycle -->
            <div class="wizard-tab-content d-none" id="tab-step-2">
                <h5 class="fw-black text-main-theme mb-1"><?= __('timetables_wizard_step2_title') ?></h5>
                <p class="text-muted small mb-4"><?= __('timetables_wizard_step2_desc') ?></p>
                <div class="row g-3" id="cyclesContainer"></div>
                <input type="hidden" name="cycle_id" id="input_cycle_id" required>
            </div>

            <!-- Étape 3 : Classe -->
            <div class="wizard-tab-content d-none" id="tab-step-3">
                <h5 class="fw-black text-main-theme mb-1"><?= __('timetables_wizard_step3_title') ?></h5>
                <p class="text-muted small mb-4"><?= __('timetables_wizard_step3_desc') ?></p>
                <div class="row g-3" id="classesContainer"></div>
                <input type="hidden" name="class_id" id="input_class_id" required>
            </div>

            <!-- Étape 4 : Semaine -->
            <div class="wizard-tab-content d-none" id="tab-step-4">
                <h5 class="fw-black text-main-theme mb-1"><?= __('timetables_wizard_step4_title') ?></h5>
                <p class="text-muted small mb-4"><?= __('timetables_wizard_step4_desc') ?></p>
                <div class="row g-3" id="weeksContainer"></div>
                <input type="hidden" name="week_id" id="input_week_id" required>
            </div>

            <!-- Étape 5 : Confirmation & Génération -->
            <div class="wizard-tab-content d-none" id="tab-step-5">
                <div class="text-center py-4">
                    <div class="avatar-init bg-success bg-opacity-10 text-success mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 72px; height: 72px;">
                        <i class="bi bi-check-lg fs-1"></i>
                    </div>
                    <h4 class="fw-black text-main-theme mb-2"><?= __('timetables_wizard_step5_title') ?></h4>
                    <p class="text-muted small mb-4" style="max-width: 500px; margin: 0 auto;"><?= __('timetables_wizard_step5_desc') ?></p>

                    <div class="modern-card border p-3 rounded-4 mx-auto text-start mb-4" style="max-width: 500px;">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small"><?= __('timetables_col_type_cycle') ?> :</span>
                            <strong id="summary_teaching_type" class="text-main-theme">--</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small"><?= __('cycle') ?> :</span>
                            <strong id="summary_cycle" class="text-main-theme">--</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small"><?= __('class') ?> :</span>
                            <strong id="summary_class" class="text-main-theme">--</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small"><?= __('timetables_col_week') ?> :</span>
                            <strong id="summary_week" class="text-main-theme">--</strong>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-lg btn-success rounded-pill px-5 fw-black shadow">
                        <i class="bi bi-magic me-2"></i><?= __('timetables_wizard_btn_generate') ?>
                    </button>
                </div>
            </div>

            <!-- Footer Buttons Nav -->
            <div class="d-flex justify-content-between align-items-center border-top pt-4 mt-4">
                <button type="button" class="btn btn-light-theme rounded-pill px-4 fw-bold" id="btnPrev" onclick="prevStep()" disabled>
                    <i class="bi bi-arrow-left me-1"></i><?= __('timetables_btn_previous') ?>
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="btnNext" onclick="nextStep()" disabled>
                    <?= __('timetables_btn_next') ?><i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentStep = 1;
let selectedData = {
    teachingTypeId: null,
    teachingTypeName: '',
    cycleId: null,
    cycleName: '',
    classId: null,
    className: '',
    weekId: null,
    weekName: ''
};

function updateStepperNav() {
    for (let i = 1; i <= 5; i++) {
        const item = document.getElementById(`step-nav-${i}`);
        if (i === currentStep) {
            item.classList.remove('opacity-50');
            item.classList.add('active');
        } else if (i < currentStep) {
            item.classList.remove('opacity-50');
            item.classList.remove('active');
        } else {
            item.classList.add('opacity-50');
            item.classList.remove('active');
        }

        const tab = document.getElementById(`tab-step-${i}`);
        if (i === currentStep) {
            tab.classList.remove('d-none');
        } else {
            tab.classList.add('d-none');
        }
    }

    document.getElementById('btnPrev').disabled = (currentStep === 1);
    if (currentStep === 5) {
        document.getElementById('btnNext').classList.add('d-none');
    } else {
        document.getElementById('btnNext').classList.remove('d-none');
    }
}

function selectTeachingType(id, el) {
    document.querySelectorAll('#tab-step-1 .select-card').forEach(c => c.classList.remove('border-primary', 'shadow-sm'));
    el.classList.add('border-primary', 'shadow-sm');
    
    selectedData.teachingTypeId = id;
    selectedData.teachingTypeName = el.querySelector('h6').innerText;
    document.getElementById('input_teaching_type_id').value = id;
    document.getElementById('btnNext').disabled = false;

    // Load cycles via API
    fetch(`/timetables/api/wizard/cycles?teaching_type_id=${id}`)
        .then(r => r.json())
        .then(cycles => {
            const container = document.getElementById('cyclesContainer');
            container.innerHTML = '';
            cycles.forEach(c => {
                container.innerHTML += `
                    <div class="col-md-6">
                        <div class="card card-theme h-100 border-2 select-card p-3 rounded-4 cursor-pointer" onclick="selectCycle(${c.id}, '${c.nom.replace(/'/g, "\\'")}', this)">
                            <div class="d-flex align-items-center">
                                <div class="avatar-init bg-info bg-opacity-10 text-info me-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi bi-diagram-3-fill fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1 text-main-theme">${c.nom}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
        });
}

function selectCycle(id, name, el) {
    document.querySelectorAll('#tab-step-2 .select-card').forEach(c => c.classList.remove('border-primary', 'shadow-sm'));
    el.classList.add('border-primary', 'shadow-sm');

    selectedData.cycleId = id;
    selectedData.cycleName = name;
    document.getElementById('input_cycle_id').value = id;
    document.getElementById('btnNext').disabled = false;

    // Load classes via API
    fetch(`/timetables/api/wizard/classes?cycle_id=${id}`)
        .then(r => r.json())
        .then(classes => {
            const container = document.getElementById('classesContainer');
            container.innerHTML = '';
            classes.forEach(cl => {
                container.innerHTML += `
                    <div class="col-md-6">
                        <div class="card card-theme h-100 border-2 select-card p-3 rounded-4 cursor-pointer" onclick="selectClass(${cl.id}, '${cl.nom.replace(/'/g, "\\'")}', this)">
                            <div class="d-flex align-items-center">
                                <div class="avatar-init bg-success bg-opacity-10 text-success me-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi bi-door-open-fill fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1 text-main-theme">${cl.nom}</h6>
                                    <span class="text-muted small">${cl.effectif} élève(s)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
        });
}

function selectClass(id, name, el) {
    document.querySelectorAll('#tab-step-3 .select-card').forEach(c => c.classList.remove('border-primary', 'shadow-sm'));
    el.classList.add('border-primary', 'shadow-sm');

    selectedData.classId = id;
    selectedData.className = name;
    document.getElementById('input_class_id').value = id;
    document.getElementById('btnNext').disabled = false;

    // Load weeks via API
    fetch(`/timetables/api/wizard/weeks`)
        .then(r => r.json())
        .then(weeks => {
            const container = document.getElementById('weeksContainer');
            container.innerHTML = '';
            weeks.forEach(w => {
                container.innerHTML += `
                    <div class="col-md-6">
                        <div class="card card-theme h-100 border-2 select-card p-3 rounded-4 cursor-pointer" onclick="selectWeek(${w.id}, '${w.libelle.replace(/'/g, "\\'")}', this)">
                            <div class="d-flex align-items-center">
                                <div class="avatar-init bg-warning bg-opacity-10 text-warning me-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi bi-calendar-range-fill fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1 text-main-theme">${w.libelle}</h6>
                                    <span class="text-muted small">Du ${w.date_debut} au ${w.date_fin}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
        });
}

function selectWeek(id, name, el) {
    document.querySelectorAll('#tab-step-4 .select-card').forEach(c => c.classList.remove('border-primary', 'shadow-sm'));
    el.classList.add('border-primary', 'shadow-sm');

    selectedData.weekId = id;
    selectedData.weekName = name;
    document.getElementById('input_week_id').value = id;
    document.getElementById('btnNext').disabled = false;
}

function nextStep() {
    if (currentStep < 5) {
        currentStep++;
        updateStepperNav();

        if (currentStep === 5) {
            document.getElementById('summary_teaching_type').innerText = selectedData.teachingTypeName;
            document.getElementById('summary_cycle').innerText = selectedData.cycleName;
            document.getElementById('summary_class').innerText = selectedData.className;
            document.getElementById('summary_week').innerText = selectedData.weekName;
        } else {
            // Check if step requires selection before enabling Next
            if (currentStep === 2 && !selectedData.cycleId) document.getElementById('btnNext').disabled = true;
            if (currentStep === 3 && !selectedData.classId) document.getElementById('btnNext').disabled = true;
            if (currentStep === 4 && !selectedData.weekId) document.getElementById('btnNext').disabled = true;
        }
    }
}

function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        updateStepperNav();
        document.getElementById('btnNext').disabled = false;
    }
}
</script>

<style>
    .wizard-icon-circle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background-color: rgba(var(--primary-rgb), 0.1);
        color: var(--primary-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .wizard-step-item.active .wizard-icon-circle {
        background-color: var(--primary-color);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.3);
    }
    .select-card {
        transition: all 0.2s ease;
    }
    .select-card:hover {
        transform: translateY(-2px);
    }

    /* Thème sombre pour le wizard */
    [data-theme="dark"] .modern-card {
        background: rgba(30, 30, 45, 0.6);
        border-color: rgba(255, 255, 255, 0.08);
    }
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../templates/layout.php';
?>
