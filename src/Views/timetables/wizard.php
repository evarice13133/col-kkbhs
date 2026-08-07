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
            <p class="text-muted-theme small mb-0">Création et planification d'un emploi du temps par Niveau & Semaine</p>
        </div>
        <a href="/timetables" class="btn btn-sm btn-light-theme rounded-pill px-3 py-2 fw-semibold border-theme-light shadow-sm">
            <i class="bi bi-arrow-left me-1"></i><?= __('cancel') ?? 'Retour' ?>
        </a>
    </div>

    <!-- Wizard Stepper Nav -->
    <div class="modern-card border-0 shadow-sm p-4 mb-4">
        <div class="row text-center g-2 position-relative">
            <div class="col wizard-step-item active" id="step-nav-1">
                <div class="wizard-icon-circle mx-auto mb-2"><i class="bi bi-building-fill"></i></div>
                <div class="fw-bold small text-main-theme">1. <?= __('timetables_step_1') ?></div>
            </div>
            <div class="col wizard-step-item opacity-50" id="step-nav-2">
                <div class="wizard-icon-circle mx-auto mb-2"><i class="bi bi-diagram-3-fill"></i></div>
                <div class="fw-bold small text-main-theme">2. <?= __('timetables_step_2') ?></div>
            </div>
            <div class="col wizard-step-item opacity-50" id="step-nav-3">
                <div class="wizard-icon-circle mx-auto mb-2"><i class="bi bi-layers-fill"></i></div>
                <div class="fw-bold small text-main-theme">3. <?= __('level') ?? 'Niveau' ?></div>
            </div>
            <div class="col wizard-step-item opacity-50" id="step-nav-4">
                <div class="wizard-icon-circle mx-auto mb-2"><i class="bi bi-calendar-range-fill"></i></div>
                <div class="fw-bold small text-main-theme">4. <?= __('timetables_step_4') ?></div>
            </div>
            <div class="col wizard-step-item opacity-50" id="step-nav-5">
                <div class="wizard-icon-circle mx-auto mb-2"><i class="bi bi-grid-3x3-gap-fill"></i></div>
                <div class="fw-bold small text-main-theme">5. <?= __('timetables_step_5') ?></div>
            </div>
        </div>
    </div>

    <!-- Wizard Form Container -->
    <div class="modern-card border-0 shadow-sm p-4">
        <form id="wizardForm" method="POST" action="/timetables/wizard/generate">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">

            <!-- Étape 1 : Type d'enseignement (Unique: Supérieur LMD, Verrouillé) -->
            <div class="wizard-tab-content" id="tab-step-1">
                <h5 class="fw-black text-main-theme mb-1"><?= __('timetables_step_1') ?></h5>
                <p class="text-muted small mb-4"><?= __('timetables_wizard_step1_desc') ?></p>

                <div class="row g-3">
                    <?php foreach ($teachingTypes as $tt): ?>
                        <div class="col-md-6">
                            <div class="card card-theme h-100 border-2 border-primary shadow-sm select-card p-3 rounded-4 bg-primary bg-opacity-10 cursor-not-allowed">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-init bg-primary text-white me-3 rounded-circle d-flex align-items-center justify-content-center shadow-xs" style="width: 48px; height: 48px;">
                                            <i class="bi bi-mortarboard-fill fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1 text-main-theme">Supérieur LMD</h6>
                                            <span class="badge bg-primary text-white border border-primary px-2.5 py-1 rounded-pill small fw-semibold">Code: LMD</span>
                                        </div>
                                    </div>
                                    <div class="text-primary fs-3">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="teaching_type_id" id="input_teaching_type_id" value="<?= $defaultType['id'] ?? 9 ?>" required>
            </div>

            <!-- Étape 2 : Cycle -->
            <div class="wizard-tab-content d-none" id="tab-step-2">
                <h5 class="fw-black text-main-theme mb-1"><?= __('timetables_wizard_step2_title') ?></h5>
                <p class="text-muted small mb-4"><?= __('timetables_wizard_step2_desc') ?></p>
                <div class="row g-3" id="cyclesContainer">
                    <?php if (!empty($cycles)): ?>
                        <?php foreach ($cycles as $c): ?>
                            <div class="col-md-6">
                                <div class="card card-theme h-100 border-2 select-card p-3 rounded-4 cursor-pointer" onclick="selectCycle(<?= $c['id'] ?>, '<?= addslashes($c['nom']) ?>', this)">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-init bg-info bg-opacity-10 text-info me-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                            <i class="bi bi-diagram-3-fill fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1 text-main-theme"><?= h($c['nom']) ?></h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <input type="hidden" name="cycle_id" id="input_cycle_id" required>
            </div>


            <!-- Étape 3 : Niveau -->
            <div class="wizard-tab-content d-none" id="tab-step-3">
                <h5 class="fw-black text-main-theme mb-1"><?= __('timetables_wizard_step3_title') ?></h5>
                <p class="text-muted small mb-4"><?= __('timetables_wizard_step3_desc') ?></p>
                <div class="row g-3" id="levelsContainer"></div>
                <input type="hidden" name="level_id" id="input_level_id" required>
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
                        <i class="bi bi-magic fs-1"></i>
                    </div>
                    <h4 class="fw-black text-main-theme mb-2"><?= __('timetables_wizard_step5_title') ?></h4>
                    <p class="text-muted small mb-4" style="max-width: 550px; margin: 0 auto;">
                        <?= __('timetables_wizard_step5_desc') ?>
                    </p>

                    <div class="modern-card border p-3.5 rounded-4 mx-auto text-start mb-4 shadow-sm" style="max-width: 520px;">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small"><?= __('timetables_step_1') ?> :</span>
                            <strong id="summary_teaching_type" class="text-main-theme">Supérieur LMD</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small"><?= __('timetables_step_2') ?> :</span>
                            <strong id="summary_cycle" class="text-main-theme">--</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small"><?= __('level') ?? 'Niveau' ?> :</span>
                            <strong id="summary_level" class="text-main-theme">--</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small"><?= __('timetables_all_classes') ?> :</span>
                            <strong id="summary_classes_count" class="text-primary">--</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small"><?= __('timetables_step_4') ?> :</span>
                            <strong id="summary_week" class="text-main-theme">--</strong>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-lg btn-success rounded-pill px-5 fw-black shadow">
                        <i class="bi bi-grid-3x3-gap-fill me-2"></i><?= __('timetables_wizard_btn_generate') ?>
                    </button>
                </div>
            </div>

            <!-- Footer Buttons Nav -->
            <div class="d-flex justify-content-between align-items-center border-top pt-4 mt-4">
                <button type="button" class="btn btn-light-theme rounded-pill px-4 fw-bold" id="btnPrev" onclick="prevStep()" disabled>
                    <i class="bi bi-arrow-left me-1"></i><?= __('timetables_btn_previous') ?>
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="btnNext" onclick="nextStep()">
                    <?= __('timetables_btn_next') ?><i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentStep = 1;
let selectedData = {
    teachingTypeId: <?= (int)($defaultType['id'] ?? 9) ?>,
    teachingTypeName: 'Supérieur LMD',
    cycleId: null,
    cycleName: '',
    levelId: null,
    levelName: '',
    classesCount: 0,
    weekId: null,
    weekName: ''
};

function resetWizard() {
    currentStep = 1;
    selectedData = {
        teachingTypeId: <?= (int)($defaultType['id'] ?? 9) ?>,
        teachingTypeName: 'Supérieur LMD',
        cycleId: null,
        cycleName: '',
        levelId: null,
        levelName: '',
        classesCount: 0,
        weekId: null,
        weekName: ''
    };
    document.getElementById('input_teaching_type_id').value = selectedData.teachingTypeId;
    document.getElementById('input_cycle_id').value = '';
    document.getElementById('input_level_id').value = '';
    document.getElementById('input_week_id').value = '';
    loadCycles(selectedData.teachingTypeId);
    updateStepperNav();
}

function updateStepperNav() {
    for (let i = 1; i <= 5; i++) {
        const item = document.getElementById(`step-nav-${i}`);
        const tab = document.getElementById(`tab-step-${i}`);
        
        if (i === currentStep) {
            item.classList.remove('opacity-50');
            item.classList.add('active');
            tab.classList.remove('d-none');
        } else {
            item.classList.add('opacity-50');
            item.classList.remove('active');
            tab.classList.add('d-none');
        }
    }

    document.getElementById('btnPrev').disabled = (currentStep === 1);
    
    if (currentStep === 5) {
        document.getElementById('btnNext').classList.add('d-none');
    } else {
        document.getElementById('btnNext').classList.remove('d-none');
        // Validation des étapes pour l'état du bouton Suivant
        if (currentStep === 1) document.getElementById('btnNext').disabled = false;
        if (currentStep === 2 && !selectedData.cycleId) document.getElementById('btnNext').disabled = true;
        if (currentStep === 3 && !selectedData.levelId) document.getElementById('btnNext').disabled = true;
        if (currentStep === 4 && !selectedData.weekId) document.getElementById('btnNext').disabled = true;
    }
}

function loadCycles(typeId) {
    fetch(`/timetables/api/wizard/cycles?teaching_type_id=${typeId}`)
        .then(r => r.json())
        .then(data => {
            const cycles = data.cycles || (Array.isArray(data) ? data : []);
            const container = document.getElementById('cyclesContainer');
            if (cycles.length > 0) {
                container.innerHTML = '';
                cycles.forEach(c => {
                    const isSel = selectedData.cycleId == c.id ? 'border-primary shadow-sm' : '';
                    container.innerHTML += `
                        <div class="col-md-6">
                            <div class="card card-theme h-100 border-2 ${isSel} select-card p-3 rounded-4 cursor-pointer" onclick="selectCycle(${c.id}, '${c.nom.replace(/'/g, "\\'")}', this)">
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
            }
        })
        .catch(err => console.error("Erreur chargement cycles:", err));
}


function selectCycle(id, name, el) {
    document.querySelectorAll('#tab-step-2 .select-card').forEach(c => c.classList.remove('border-primary', 'shadow-sm'));
    el.classList.add('border-primary', 'shadow-sm');

    selectedData.cycleId = id;
    selectedData.cycleName = name;
    selectedData.levelId = null;
    selectedData.levelName = '';
    document.getElementById('input_cycle_id').value = id;
    document.getElementById('btnNext').disabled = false;

    // Charger les niveaux rattachés au cycle via l'API
    fetch(`/timetables/api/wizard/levels?cycle_id=${id}`)
        .then(r => r.json())
        .then(data => {
            const levels = data.levels || data;
            const container = document.getElementById('levelsContainer');
            container.innerHTML = '';
            if (levels.length === 0) {
                container.innerHTML = '<div class="col-12"><div class="alert alert-info">Aucun niveau configuré. Vous pouvez passer à l\'étape suivante pour afficher toutes les classes du cycle.</div></div>';
            } else {
                levels.forEach(l => {
                    container.innerHTML += `
                        <div class="col-md-6">
                            <div class="card card-theme h-100 border-2 select-card p-3 rounded-4 cursor-pointer" onclick="selectLevel(${l.id}, '${l.nom.replace(/'/g, "\\'")}', this)">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-init bg-success bg-opacity-10 text-success me-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        <i class="bi bi-layers-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1 text-main-theme">${l.nom}</h6>
                                        <span class="text-muted small">Code: ${l.code || 'N/A'}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }
        });
}

function selectLevel(id, name, el) {
    document.querySelectorAll('#tab-step-3 .select-card').forEach(c => c.classList.remove('border-primary', 'shadow-sm'));
    if (el) el.classList.add('border-primary', 'shadow-sm');

    selectedData.levelId = id;
    selectedData.levelName = name;
    document.getElementById('input_level_id').value = id;
    document.getElementById('btnNext').disabled = false;

    // Charger les semaines via API
    loadWeeks();
}

function loadWeeks() {
    fetch(`/timetables/api/wizard/weeks`)
        .then(r => r.json())
        .then(data => {
            const weeks = data.weeks || data;
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
            document.getElementById('summary_cycle').innerText = selectedData.cycleName || 'Tous les cycles';
            document.getElementById('summary_level').innerText = selectedData.levelName || 'Toutes les classes';
            document.getElementById('summary_week').innerText = selectedData.weekName;

            // Compter les classes concernées
            fetch(`/timetables/api/wizard/classes?cycle_id=${selectedData.cycleId || 0}&level_id=${selectedData.levelId || 0}`)
                .then(r => r.json())
                .then(data => {
                    const classes = data.classes || data;
                    selectedData.classesCount = classes.length;
                    document.getElementById('summary_classes_count').innerText = `${classes.length} classe(s) concernée(s)`;
                });
        }
    }
}

function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        updateStepperNav();
    }
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    resetWizard();
});
</script>

<style>
    /* Microsoft 365 Inspired Wizard Stepper & Canva Card Micro-Interactions */
    .wizard-icon-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background-color: rgba(var(--primary-rgb), 0.1);
        color: var(--primary-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid transparent;
    }
    .wizard-step-item.active .wizard-icon-circle {
        background-color: var(--primary-color);
        color: #ffffff;
        box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.2), 0 6px 16px rgba(var(--primary-rgb), 0.35);
        transform: scale(1.08);
    }
    .select-card {
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease, border-color 0.3s ease, border-style 0.3s ease;
        border: 1px solid var(--border-color, rgba(226, 232, 240, 0.8)) !important;
        border-radius: 14px !important;
        background: var(--bg-card, #ffffff);
    }
    .select-card:hover {
        transform: translateY(-3px) !important;
        border-style: dashed !important;
        border-width: 1.5px !important;
        box-shadow: 0 14px 28px -6px rgba(124, 58, 237, 0.15) !important;
        border-color: var(--primary-color, #7c3aed) !important;
    }
    .select-card.border-primary {
        border-color: var(--primary-color) !important;
        box-shadow: 0 0 0 2px rgba(var(--primary-rgb), 0.2) !important;
    }
    .cursor-not-allowed {
        cursor: not-allowed !important;
    }

    /* Dark Mode Overrides for Wizard */
    [data-theme="dark"] .modern-card {
        background: var(--bg-card, #0f172a) !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
        color: #f8fafc !important;
    }
    [data-theme="dark"] .select-card {
        background: var(--bg-card, #0f172a) !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
        color: #f8fafc !important;
    }
    [data-theme="dark"] .select-card:hover {
        background: var(--bg-card, #0f172a) !important;
        border-style: dashed !important;
        border-color: var(--primary-color, #7c3aed) !important;
        box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.6) !important;
    }
    [data-theme="dark"] .wizard-icon-circle {
        background-color: rgba(59, 130, 246, 0.15);
        color: #60a5fa;
    }
    [data-theme="dark"] .wizard-step-item.active .wizard-icon-circle {
        background-color: #3b82f6;
        color: #ffffff;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.3), 0 6px 16px rgba(0, 0, 0, 0.5);
    }
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../templates/layout.php';
?>
