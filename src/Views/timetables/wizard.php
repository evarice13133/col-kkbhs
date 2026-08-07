<?php
$title = __('timetables_wizard_title') . " - " . __('app_name');
ob_start();
?>

<div class="animate-fade-in container-fluid py-3 px-md-4 google-material-scope">
    <!-- Header Minimaliste Haut Niveau avec Feedback & Visual Badge -->
    <div class="d-flex align-items-center justify-content-between gap-3 mb-3 p-3 rounded-4 bg-google-surface shadow-xs border-google header-hover-card transition-all">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar-init bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-xs icon-pulse-subtle" style="width: 44px; height: 44px;">
                <i class="bi bi-lightning-charge-fill fs-5"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h2 class="fw-black text-google-dark mb-0 fs-5 font-google">
                        Planification Express
                    </h2>
                    <span class="badge bg-google-blue-subtle text-google-blue font-monospace extra-small px-2 py-1 rounded-pill fw-bold border border-google-blue-subtle">
                        v2.5 UI/UX Enhanced
                    </span>
                </div>
                <p class="text-google-muted extra-small font-google mb-0">
                    <i class="bi bi-shield-check text-success me-1"></i>Génération instantanée et sécurisée
                </p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button id="btnToggleMode" type="button" class="btn google-btn-outlined rounded-pill px-3 py-1.5 fw-bold extra-small shadow-xs transition-all me-1" onclick="toggleWizardMode()">
                <i class="bi bi-layers-half me-1.5 text-primary"></i>Vue Étape par Étape
            </button>
            <a href="/timetables" class="google-btn-ghost rounded-pill px-3 py-1.5 fw-semibold extra-small transition-all">
                <i class="bi bi-arrow-left me-1"></i><?= __('cancel') ?? 'Retour' ?>
            </a>
        </div>
    </div>

    <!-- Mode Express Ultra-Focalisé (Toutes les étapes sur 1 SEULE LIGNE) -->
    <div id="expressModeContainer" class="mb-4">
        
        <!-- Google Single-Row Toolbar Container avec Onboarding & Retour Visuel -->
        <div class="google-material-card p-4 rounded-4 mb-4 shadow-sm border-google position-relative toolbar-express-card">
            
            <!-- Onboarding Progress Velocity Bar -->
            <div class="progress rounded-pill mb-3 position-relative overflow-hidden" style="height: 7px; background-color: rgba(26, 115, 232, 0.1);">
                <div class="progress-bar bg-gradient-google-animated" id="expressProgressBar" style="width: 33%; transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);"></div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-google-blue-subtle text-google-blue rounded-pill font-google px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs">
                        <i class="bi bi-lightning-charge-fill text-warning animate-bounce-short"></i>Assistant Monopage (3 Clics Auto-Submit)
                    </span>
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill font-google extra-small px-2.5 py-1 fw-bold border border-success-subtle transition-all" id="expressStatusBadge">
                        Étape 1/3 Prête
                    </span>
                </div>
                <div class="text-google-muted extra-small font-google d-none d-md-flex align-items-center gap-1.5">
                    <i class="bi bi-magic text-google-blue"></i>
                    <span>Auto-lancement instantané sur le 3ème choix</span>
                </div>
            </div>

            <form id="expressForm" method="POST" action="/timetables/wizard/generate" class="row g-3 align-items-end">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                <input type="hidden" name="teaching_type_id" value="<?= $defaultType['id'] ?? 9 ?>">

                <!-- ÉTAPE 1: Cycle (Toujours actif au départ) -->
                <div class="col-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                        <div class="d-flex align-items-center gap-2">
                            <span class="google-step-badge bg-google-blue shadow-xs transition-all" id="badgeStep1">1</span>
                            <label class="form-label font-google fw-bold text-google-dark small mb-0">1. Choisir le Cycle *</label>
                        </div>
                        <span class="badge bg-primary text-white rounded-pill extra-small font-google fw-bold shadow-xs transition-all" id="statusStep1">Étape 1</span>
                    </div>
                    <div class="position-relative">
                        <select name="cycle_id" id="express_cycle_id" class="form-select form-select-lg rounded-pill border-2 font-google fw-bold text-google-dark shadow-xs google-input-glow step-highlight-active" required onchange="onExpressCycleChangeSequential(this.value)">
                            <option value="">-- 1. Sélectionner un cycle --</option>
                            <?php foreach ($cycles as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= h($c['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div id="expressCycleLoader" class="spinner-border spinner-border-sm text-primary position-absolute end-0 top-50 translate-middle-y me-3 d-none" role="status"></div>
                    </div>
                </div>

                <!-- ÉTAPE 2: Niveau (Désactivé jusqu'au choix du Cycle) -->
                <div class="col-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                        <div class="d-flex align-items-center gap-2">
                            <span class="google-step-badge bg-secondary opacity-50 transition-all" id="badgeStep2">2</span>
                            <label class="form-label font-google fw-bold text-muted small mb-0 transition-all" id="labelStep2">2. Choisir le Niveau *</label>
                        </div>
                        <span class="badge bg-light text-muted rounded-pill extra-small font-google border transition-all" id="statusStep2">🔒 En attente du cycle</span>
                    </div>
                    <div class="position-relative">
                        <select name="level_id" id="express_level_id" class="form-select form-select-lg rounded-pill border-2 font-google fw-bold text-google-dark shadow-xs google-input-glow opacity-50" disabled required onchange="onLevelChangeSequential(this.value)">
                            <option value="">-- 2. Choisir d'abord un cycle --</option>
                        </select>
                        <div id="expressLevelLoader" class="spinner-border spinner-border-sm text-primary position-absolute end-0 top-50 translate-middle-y me-3 d-none" role="status"></div>
                    </div>
                </div>

                <!-- ÉTAPE 3: Semaine (Désactivé jusqu'au choix du Niveau) -->
                <div class="col-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                        <div class="d-flex align-items-center gap-2">
                            <span class="google-step-badge bg-secondary opacity-50 transition-all" id="badgeStep3">3</span>
                            <label class="form-label font-google fw-bold text-muted small mb-0 transition-all" id="labelStep3">3. Choisir la Semaine *</label>
                        </div>
                        <span class="badge bg-light text-muted rounded-pill extra-small font-google border transition-all" id="statusStep3">🔒 En attente du niveau</span>
                    </div>
                    <div class="position-relative">
                        <select name="week_id" id="express_week_id" class="form-select form-select-lg rounded-pill border-2 font-google fw-bold text-google-dark shadow-xs google-input-glow opacity-50" disabled required onchange="onWeekChangeSequential(this.value)">
                            <option value="">-- 3. Choisir d'abord un niveau --</option>
                            <?php foreach ($weeks as $idx => $w): ?>
                                <option value="<?= $w['id'] ?>">
                                    <?= h($w['libelle']) ?> (Du <?= date('d/m', strtotime($w['date_debut'])) ?> au <?= date('d/m', strtotime($w['date_fin'])) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="expressWeekLoader" class="spinner-border spinner-border-sm text-primary position-absolute end-0 top-50 translate-middle-y me-3 d-none" role="status"></div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Google Toast Snackbar Container pour Retour Visuel Instantané -->
        <div id="googleToastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;"></div>
    </div>

    <!-- Mode 2: Wizard Stepper Nav (Masqué par défaut) -->
    <div id="fullWizardContainer" class="d-none animate-fade-in">
        <div class="modern-card border-0 shadow-sm p-4 mb-4 rounded-4 bg-google-surface">
            <div class="row text-center g-2 position-relative">
                <div class="col wizard-step-item active" id="step-nav-1">
                    <div class="wizard-icon-circle mx-auto mb-2 shadow-xs"><i class="bi bi-building-fill"></i></div>
                    <div class="fw-bold small text-main-theme">1. <?= __('timetables_step_1') ?></div>
                </div>
                <div class="col wizard-step-item opacity-50" id="step-nav-2">
                    <div class="wizard-icon-circle mx-auto mb-2 shadow-xs"><i class="bi bi-diagram-3-fill"></i></div>
                    <div class="fw-bold small text-main-theme">2. <?= __('timetables_step_2') ?></div>
                </div>
                <div class="col wizard-step-item opacity-50" id="step-nav-3">
                    <div class="wizard-icon-circle mx-auto mb-2 shadow-xs"><i class="bi bi-layers-fill"></i></div>
                    <div class="fw-bold small text-main-theme">3. <?= __('level') ?? 'Niveau' ?></div>
                </div>
                <div class="col wizard-step-item opacity-50" id="step-nav-4">
                    <div class="wizard-icon-circle mx-auto mb-2 shadow-xs"><i class="bi bi-calendar-range-fill"></i></div>
                    <div class="fw-bold small text-main-theme">4. <?= __('timetables_step_4') ?></div>
                </div>
                <div class="col wizard-step-item opacity-50" id="step-nav-5">
                    <div class="wizard-icon-circle mx-auto mb-2 shadow-xs"><i class="bi bi-grid-3x3-gap-fill"></i></div>
                    <div class="fw-bold small text-main-theme">5. <?= __('timetables_step_5') ?></div>
                </div>
            </div>
        </div>

        <div class="modern-card border-0 shadow-sm p-4 rounded-4 bg-google-surface">
            <form id="wizardForm" method="POST" action="/timetables/wizard/generate">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">

                <!-- Étape 1 : Type d'enseignement (Unique: Supérieur LMD, Verrouillé) -->
                <div class="wizard-tab-content" id="tab-step-1">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <div>
                            <h5 class="fw-black text-main-theme mb-1 d-flex align-items-center gap-2">
                                <i class="bi bi-mortarboard-fill text-primary"></i><?= __('timetables_step_1') ?>
                            </h5>
                            <p class="text-muted small mb-0"><?= __('timetables_wizard_step1_desc') ?></p>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1.5 font-google extra-small fw-bold">
                            <i class="bi bi-check-circle-fill me-1"></i>Pré-sélectionné
                        </span>
                    </div>

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
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <div>
                            <h5 class="fw-black text-main-theme mb-1 d-flex align-items-center gap-2">
                                <i class="bi bi-diagram-3-fill text-info"></i><?= __('timetables_wizard_step2_title') ?>
                            </h5>
                            <p class="text-muted small mb-0"><?= __('timetables_wizard_step2_desc') ?></p>
                        </div>
                    </div>
                    <div class="row g-3" id="cyclesContainer">
                        <?php if (!empty($cycles)): ?>
                            <?php foreach ($cycles as $c): ?>
                                <div class="col-md-6">
                                    <div class="card card-theme h-100 border-2 select-card p-3 rounded-4 cursor-pointer transition-all hover-lift" onclick="selectCycle(<?= $c['id'] ?>, '<?= addslashes($c['nom']) ?>', this)">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-init bg-info bg-opacity-10 text-info me-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                    <i class="bi bi-diagram-3-fill fs-4"></i>
                                                </div>
                                                <div>
                                                    <h6 class="fw-bold mb-1 text-main-theme"><?= h($c['nom']) ?></h6>
                                                    <span class="text-muted extra-small">Cliquer pour sélectionner</span>
                                                </div>
                                            </div>
                                            <i class="bi bi-circle text-muted fs-4 select-check-icon transition-all"></i>
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
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <div>
                            <h5 class="fw-black text-main-theme mb-1 d-flex align-items-center gap-2">
                                <i class="bi bi-layers-fill text-success"></i><?= __('timetables_wizard_step3_title') ?>
                            </h5>
                            <p class="text-muted small mb-0"><?= __('timetables_wizard_step3_desc') ?></p>
                        </div>
                    </div>
                    <div class="row g-3" id="levelsContainer"></div>
                    <input type="hidden" name="level_id" id="input_level_id" required>
                </div>

                <!-- Étape 4 : Semaine -->
                <div class="wizard-tab-content d-none" id="tab-step-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <div>
                            <h5 class="fw-black text-main-theme mb-1 d-flex align-items-center gap-2">
                                <i class="bi bi-calendar-range-fill text-warning"></i><?= __('timetables_wizard_step4_title') ?>
                            </h5>
                            <p class="text-muted small mb-0"><?= __('timetables_wizard_step4_desc') ?></p>
                        </div>
                    </div>
                    <div class="row g-3" id="weeksContainer"></div>
                    <input type="hidden" name="week_id" id="input_week_id" required>
                </div>

                <!-- Étape 5 : Confirmation & Génération -->
                <div class="wizard-tab-content d-none" id="tab-step-5">
                    <div class="text-center py-4">
                        <div class="avatar-init bg-success bg-opacity-10 text-success mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center shadow-sm pulse-badge" style="width: 72px; height: 72px;">
                            <i class="bi bi-magic fs-1"></i>
                        </div>
                        <h4 class="fw-black text-main-theme mb-2"><?= __('timetables_wizard_step5_title') ?></h4>
                        <p class="text-muted small mb-4" style="max-width: 550px; margin: 0 auto;">
                            <?= __('timetables_wizard_step5_desc') ?>
                        </p>

                        <div class="modern-card border p-4 rounded-4 mx-auto text-start mb-4 shadow-sm transition-all" style="max-width: 680px; background: var(--bg-card-secondary, #f8fafc);">
                            <div class="row g-3 align-items-center mb-3 pb-3 border-bottom">
                                <div class="col-sm-5 text-muted-theme fw-semibold">
                                    <i class="bi bi-building-fill text-primary me-2"></i><?= __('timetables_step_1') ?> :
                                </div>
                                <div class="col-sm-7 text-sm-end">
                                    <span id="summary_teaching_type" class="badge bg-primary text-white fs-6 px-3 py-1.5 rounded-pill fw-bold shadow-xs">Supérieur LMD</span>
                                </div>
                            </div>
                            <div class="row g-3 align-items-center mb-3 pb-3 border-bottom">
                                <div class="col-sm-5 text-muted-theme fw-semibold">
                                    <i class="bi bi-diagram-3-fill text-info me-2"></i><?= __('timetables_step_2') ?> :
                                </div>
                                <div class="col-sm-7 text-sm-end">
                                    <span id="summary_cycle" class="badge bg-info text-dark fs-6 px-3 py-1.5 rounded-pill fw-extrabold shadow-xs">--</span>
                                </div>
                            </div>
                            <div class="row g-3 align-items-center mb-3 pb-3 border-bottom">
                                <div class="col-sm-5 text-muted-theme fw-semibold">
                                    <i class="bi bi-layers-fill text-success me-2"></i><?= __('level') ?? 'Niveau' ?> :
                                </div>
                                <div class="col-sm-7 text-sm-end">
                                    <span id="summary_level" class="badge bg-success text-white fs-6 px-3 py-1.5 rounded-pill fw-extrabold shadow-xs">--</span>
                                </div>
                            </div>
                            <div class="row g-3 align-items-center mb-3 pb-3 border-bottom">
                                <div class="col-sm-5 text-muted-theme fw-semibold">
                                    <i class="bi bi-people-fill text-primary me-2"></i><?= __('timetables_all_classes') ?> :
                                </div>
                                <div class="col-sm-7 text-sm-end">
                                    <span id="summary_classes_count" class="badge bg-primary text-white fs-6 px-3 py-1.5 rounded-pill fw-bold shadow-xs">
                                        <div class="spinner-border spinner-border-sm me-1" role="status"></div> Calcul...
                                    </span>
                                </div>
                            </div>
                            <div class="row g-3 align-items-center">
                                <div class="col-sm-5 text-muted-theme fw-semibold">
                                    <i class="bi bi-calendar-range-fill text-warning me-2"></i><?= __('timetables_step_4') ?> :
                                </div>
                                <div class="col-sm-7 text-sm-end">
                                    <span id="summary_week" class="badge bg-warning text-dark fs-6 px-3 py-1.5 rounded-pill fw-bold shadow-xs">--</span>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-lg btn-success rounded-pill px-5 fw-black shadow-lg hover-scale transition-all" id="btnFinalGenerate">
                            <i class="bi bi-grid-3x3-gap-fill me-2"></i><?= __('timetables_wizard_btn_generate') ?>
                        </button>
                    </div>
                </div>

                <!-- Footer Buttons Nav -->
                <div class="d-flex justify-content-between align-items-center border-top pt-4 mt-4">
                    <button type="button" class="btn btn-light-theme rounded-pill px-4 fw-bold transition-all" id="btnPrev" onclick="prevStep()" disabled>
                        <i class="bi bi-arrow-left me-1"></i><?= __('timetables_btn_previous') ?>
                    </button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm transition-all hover-scale" id="btnNext" onclick="nextStep()">
                        <?= __('timetables_btn_next') ?><i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </form>
        </div>
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

function renderSkeletons(containerId, count = 2) {
    const container = document.getElementById(containerId);
    if (!container) return;
    let skeletonHtml = '';
    for (let i = 0; i < count; i++) {
        skeletonHtml += `
            <div class="col-md-6">
                <div class="card border-0 p-3 rounded-4 shadow-xs skeleton-card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="skeleton-avatar rounded-circle"></div>
                        <div class="flex-grow-1">
                            <div class="skeleton-line skeleton-title mb-2"></div>
                            <div class="skeleton-line skeleton-subtitle"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    container.innerHTML = skeletonHtml;
}

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
            tab.classList.add('animate-fade-in');
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
        if (currentStep === 1) document.getElementById('btnNext').disabled = false;
        if (currentStep === 2 && !selectedData.cycleId) document.getElementById('btnNext').disabled = true;
        if (currentStep === 3 && selectedData.levelId === null) document.getElementById('btnNext').disabled = true;
        if (currentStep === 4 && !selectedData.weekId) document.getElementById('btnNext').disabled = true;
    }
}

function loadCycles(typeId) {
    renderSkeletons('cyclesContainer', 4);
    fetch(`/timetables/api/wizard/cycles?teaching_type_id=${typeId}`)
        .then(r => r.json())
        .then(data => {
            const cycles = data.cycles || (Array.isArray(data) ? data : []);
            const container = document.getElementById('cyclesContainer');
            if (cycles.length > 0) {
                container.innerHTML = '';
                cycles.forEach(c => {
                    const isSel = selectedData.cycleId == c.id ? 'border-primary shadow-sm bg-primary bg-opacity-10' : '';
                    const checkIcon = selectedData.cycleId == c.id ? 'bi-check-circle-fill text-primary' : 'bi-circle text-muted';
                    container.innerHTML += `
                        <div class="col-md-6">
                            <div class="card card-theme h-100 border-2 ${isSel} select-card p-3 rounded-4 cursor-pointer transition-all hover-lift" onclick="selectCycle(${c.id}, '${c.nom.replace(/'/g, "\\'")}', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-init bg-info bg-opacity-10 text-info me-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                            <i class="bi bi-diagram-3-fill fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1 text-main-theme">${escapeHtml(c.nom)}</h6>
                                            <span class="text-muted extra-small">Cliquer pour choisir</span>
                                        </div>
                                    </div>
                                    <i class="bi ${checkIcon} fs-4 select-check-icon transition-all"></i>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                container.innerHTML = '<div class="col-12"><div class="alert alert-info rounded-4 border-0 shadow-xs"><i class="bi bi-info-circle me-2"></i>Aucun cycle trouvé pour ce type d\'enseignement.</div></div>';
            }
        })
        .catch(err => {
            console.error("Erreur chargement cycles:", err);
            showGoogleToast("Impossible de charger les cycles", "bi-exclamation-triangle-fill");
        });
}

function selectCycle(id, name, el) {
    document.querySelectorAll('#tab-step-2 .select-card').forEach(c => {
        c.classList.remove('border-primary', 'shadow-sm', 'bg-primary', 'bg-opacity-10');
        const icon = c.querySelector('.select-check-icon');
        if (icon) {
            icon.className = 'bi bi-circle text-muted fs-4 select-check-icon transition-all';
        }
    });
    el.classList.add('border-primary', 'shadow-sm', 'bg-primary', 'bg-opacity-10');
    const selIcon = el.querySelector('.select-check-icon');
    if (selIcon) {
        selIcon.className = 'bi bi-check-circle-fill text-primary fs-4 select-check-icon transition-all';
    }

    selectedData.cycleId = id;
    selectedData.cycleName = name;
    selectedData.levelId = null;
    selectedData.levelName = '';
    document.getElementById('input_cycle_id').value = id;
    document.getElementById('btnNext').disabled = false;

    renderSkeletons('levelsContainer', 4);

    fetch(`/timetables/api/wizard/levels?cycle_id=${id}`)
        .then(r => r.json())
        .then(data => {
            const levels = data.levels || (Array.isArray(data) ? data : []);
            const container = document.getElementById('levelsContainer');
            container.innerHTML = '';
            
            container.innerHTML += `
                <div class="col-md-6">
                    <div class="card card-theme h-100 border-2 select-card p-3 rounded-4 cursor-pointer transition-all hover-lift" onclick="selectLevel(0, 'Toutes les classes du cycle', this)">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="avatar-init bg-primary bg-opacity-10 text-primary me-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi bi-layers-fill fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1 text-main-theme">Toutes les classes</h6>
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill extra-small">Vue globale</span>
                                </div>
                            </div>
                            <i class="bi bi-circle text-muted fs-4 select-check-icon transition-all"></i>
                        </div>
                    </div>
                </div>
            `;

            if (levels && levels.length > 0) {
                levels.forEach(l => {
                    const label = l.nom || (l.libelle_fr ? l.libelle_fr : (l.libelle_en ? l.libelle_en : `Niveau ${l.code || l.id}`));
                    container.innerHTML += `
                        <div class="col-md-6">
                            <div class="card card-theme h-100 border-2 select-card p-3 rounded-4 cursor-pointer transition-all hover-lift" onclick="selectLevel(${l.id}, '${escapeHtml(label).replace(/'/g, "\\'")}', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-init bg-success bg-opacity-10 text-success me-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                            <i class="bi bi-layers fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1 text-main-theme">${escapeHtml(label)}</h6>
                                            <span class="text-muted small">Code: ${escapeHtml(l.code || 'N/A')}</span>
                                        </div>
                                    </div>
                                    <i class="bi bi-circle text-muted fs-4 select-check-icon transition-all"></i>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }
            showGoogleToast(`Cycle "${name}" sélectionné`, 'bi-check-circle-fill');
        })
        .catch(err => {
            console.error('Erreur chargement niveaux:', err);
            showGoogleToast('Erreur lors du chargement des niveaux', 'bi-exclamation-triangle-fill');
        });
}

function selectLevel(id, name, el) {
    document.querySelectorAll('#tab-step-3 .select-card').forEach(c => {
        c.classList.remove('border-primary', 'shadow-sm', 'bg-primary', 'bg-opacity-10');
        const icon = c.querySelector('.select-check-icon');
        if (icon) icon.className = 'bi bi-circle text-muted fs-4 select-check-icon transition-all';
    });
    if (el) {
        el.classList.add('border-primary', 'shadow-sm', 'bg-primary', 'bg-opacity-10');
        const selIcon = el.querySelector('.select-check-icon');
        if (selIcon) selIcon.className = 'bi bi-check-circle-fill text-primary fs-4 select-check-icon transition-all';
    }

    selectedData.levelId = id;
    selectedData.levelName = name;
    document.getElementById('input_level_id').value = id;
    document.getElementById('btnNext').disabled = false;

    loadWeeks();
}

function loadWeeks() {
    renderSkeletons('weeksContainer', 4);
    fetch(`/timetables/api/wizard/weeks`)
        .then(r => r.json())
        .then(data => {
            const weeks = data.weeks || (Array.isArray(data) ? data : []);
            const container = document.getElementById('weeksContainer');
            container.innerHTML = '';
            if (weeks && weeks.length > 0) {
                weeks.forEach(w => {
                    const isSel = selectedData.weekId == w.id ? 'border-primary shadow-sm bg-primary bg-opacity-10' : '';
                    const checkIcon = selectedData.weekId == w.id ? 'bi-check-circle-fill text-primary' : 'bi-circle text-muted';
                    container.innerHTML += `
                        <div class="col-md-6">
                            <div class="card card-theme h-100 border-2 ${isSel} select-card p-3 rounded-4 cursor-pointer transition-all hover-lift" onclick="selectWeek(${w.id}, '${escapeHtml(w.libelle).replace(/'/g, "\\'")}', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-init bg-warning bg-opacity-10 text-warning me-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                            <i class="bi bi-calendar-range-fill fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1 text-main-theme">${escapeHtml(w.libelle)}</h6>
                                            <span class="text-muted small">Du ${w.date_debut} au ${w.date_fin}</span>
                                        </div>
                                    </div>
                                    <i class="bi ${checkIcon} fs-4 select-check-icon transition-all"></i>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                container.innerHTML = '<div class="col-12"><div class="alert alert-warning rounded-4 border-0 shadow-xs"><i class="bi bi-exclamation-triangle me-2"></i>Aucune semaine disponible.</div></div>';
            }
        })
        .catch(err => {
            console.error('Erreur chargement semaines:', err);
            showGoogleToast('Erreur de chargement des semaines', 'bi-exclamation-triangle-fill');
        });
}

function selectWeek(id, name, el) {
    document.querySelectorAll('#tab-step-4 .select-card').forEach(c => {
        c.classList.remove('border-primary', 'shadow-sm', 'bg-primary', 'bg-opacity-10');
        const icon = c.querySelector('.select-check-icon');
        if (icon) icon.className = 'bi bi-circle text-muted fs-4 select-check-icon transition-all';
    });
    if (el) {
        el.classList.add('border-primary', 'shadow-sm', 'bg-primary', 'bg-opacity-10');
        const selIcon = el.querySelector('.select-check-icon');
        if (selIcon) selIcon.className = 'bi bi-check-circle-fill text-primary fs-4 select-check-icon transition-all';
    }

    selectedData.weekId = id;
    selectedData.weekName = name;
    document.getElementById('input_week_id').value = id;
    document.getElementById('btnNext').disabled = false;
    showGoogleToast(`Semaine "${name}" sélectionnée`, 'bi-calendar-check-fill');
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
            document.getElementById('summary_classes_count').innerHTML = '<div class="spinner-border spinner-border-sm me-1" role="status"></div> Calcul...';

            fetch(`/timetables/api/wizard/classes?cycle_id=${selectedData.cycleId || 0}&level_id=${selectedData.levelId || 0}`)
                .then(r => r.json())
                .then(data => {
                    const classes = data.classes || (Array.isArray(data) ? data : []);
                    selectedData.classesCount = classes.length;
                    document.getElementById('summary_classes_count').innerText = `${classes.length} classe(s) concernée(s)`;
                })
                .catch(err => {
                    console.error('Erreur comptage classes:', err);
                    document.getElementById('summary_classes_count').innerText = '1+ classe(s)';
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

let expressClicks = 0;
function checkGoogleAutoLaunch(triggerSource) {
    const cycleId = document.getElementById('express_cycle_id').value;
    const levelId = document.getElementById('express_level_id').value;
    const weekId = document.getElementById('express_week_id').value;

    expressClicks++;

    if (cycleId !== '' && levelId !== '' && weekId !== '' && (triggerSource === 'level' || triggerSource === 'week' || expressClicks >= 2)) {
        triggerGoogleInstantLaunch();
    }
}

function triggerGoogleInstantLaunch() {
    let overlay = document.getElementById('googleInstantOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'googleInstantOverlay';
        overlay.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center text-white animate-fade-in';
        overlay.style.zIndex = '99999';
        overlay.style.background = 'rgba(15, 23, 42, 0.85)';
        overlay.style.backdropFilter = 'blur(12px)';
        overlay.innerHTML = `
            <div class="card border-0 shadow-lg p-4 rounded-5 text-center bg-white text-dark hover-scale transition-all" style="max-width: 420px;">
                <div class="spinner-border text-primary mx-auto mb-3" style="width: 3.5rem; height: 3.5rem; border-width: 0.3em;" role="status"></div>
                <h5 class="fw-black font-google mb-1 text-dark d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-rocket-takeoff-fill text-primary"></i>3ème Clic Détecté !
                </h5>
                <p class="text-muted extra-small font-google mb-3">Chargement automatique de la grille multi-classes en cours...</p>
                <div class="progress rounded-pill mb-2 overflow-hidden" style="height: 6px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary w-100"></div>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);
    } else {
        overlay.classList.remove('d-none');
    }

    setTimeout(() => {
        const form = document.getElementById('expressForm');
        if (form) form.submit();
    }, 250);
}

function toggleWizardMode() {
    const expressContainer = document.getElementById('expressModeContainer');
    const fullWizardContainer = document.getElementById('fullWizardContainer');
    const btnToggle = document.getElementById('btnToggleMode');

    if (expressContainer.classList.contains('d-none')) {
        expressContainer.classList.remove('d-none');
        fullWizardContainer.classList.add('d-none');
        btnToggle.innerHTML = '<i class="bi bi-layers-half me-1.5 text-primary"></i>Vue Étape par Étape';
        showGoogleToast('Mode Express Monopage activé', 'bi-lightning-charge-fill');
    } else {
        expressContainer.classList.add('d-none');
        fullWizardContainer.classList.remove('d-none');
        btnToggle.innerHTML = '<i class="bi bi-cpu-fill me-1.5 text-primary"></i>Mode Express (3 Clics)';
        showGoogleToast('Mode Guidé Étape par Étape activé', 'bi-layers-fill');
    }
}

function quickLaunchPreset(cycleId, levelId, weekId, cardEl) {
    if (cardEl) {
        cardEl.classList.add('preset-card-active');
    }
    showGoogleToast('⚡ Lancement rapide en cours...', 'bi-rocket-takeoff-fill');
    const form = document.getElementById('expressForm');
    document.getElementById('express_cycle_id').value = cycleId;
    document.getElementById('express_level_id').value = levelId || 0;
    document.getElementById('express_week_id').value = weekId;
    triggerGoogleInstantLaunch();
}

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function updateExpressProgress(percent) {
    const bar = document.getElementById('expressProgressBar');
    const badge = document.getElementById('expressStatusBadge');
    if (bar) bar.style.width = percent + '%';
    if (badge) {
        if (percent === 0) {
            badge.className = 'badge bg-success bg-opacity-10 text-success rounded-pill font-google extra-small px-2.5 py-1 fw-bold border border-success-subtle transition-all';
            badge.innerText = 'Étape 1/3 Prête';
        } else if (percent === 33) {
            badge.className = 'badge bg-primary bg-opacity-10 text-primary rounded-pill font-google extra-small px-2.5 py-1 fw-bold border border-primary-subtle transition-all';
            badge.innerText = 'Étape 2/3 Prête';
        } else if (percent === 66) {
            badge.className = 'badge bg-warning bg-opacity-10 text-warning rounded-pill font-google extra-small px-2.5 py-1 fw-bold border border-warning-subtle transition-all';
            badge.innerText = 'Étape 3/3 Prête';
        } else if (percent === 100) {
            badge.className = 'badge bg-success text-white rounded-pill font-google extra-small px-2.5 py-1 fw-bold shadow-xs transition-all';
            badge.innerText = '🚀 Génération en cours...';
        }
    }
}

function showGoogleToast(msg, icon = 'bi-check-circle-fill') {
    const container = document.getElementById('googleToastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = 'toast align-items-center text-white bg-dark border-0 shadow-lg show rounded-4 mb-2 toast-feedback-entry';
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    toast.style.background = '#202124';
    toast.innerHTML = `
        <div class="d-flex p-3">
            <div class="toast-body font-google small d-flex align-items-center gap-2">
                <i class="bi ${icon} text-primary fs-5"></i>
                <span>${msg}</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('toast-feedback-exit');
        setTimeout(() => toast.remove(), 300);
    }, 3200);
}

function onExpressCycleChangeSequential(cycleId) {
    const levelSelect = document.getElementById('express_level_id');
    const weekSelect = document.getElementById('express_week_id');
    const badgeStep1 = document.getElementById('badgeStep1');
    const statusStep1 = document.getElementById('statusStep1');
    const badgeStep2 = document.getElementById('badgeStep2');
    const statusStep2 = document.getElementById('statusStep2');
    const labelStep2 = document.getElementById('labelStep2');
    const cycleLoader = document.getElementById('expressCycleLoader');

    if (!cycleId) {
        levelSelect.disabled = true;
        levelSelect.classList.add('opacity-50');
        levelSelect.innerHTML = '<option value="">-- 2. Choisir d\'abord un cycle --</option>';
        
        weekSelect.disabled = true;
        weekSelect.classList.add('opacity-50');

        statusStep1.className = 'badge bg-primary text-white rounded-pill extra-small font-google fw-bold shadow-xs';
        statusStep1.innerText = 'Étape 1';
        badgeStep1.className = 'google-step-badge bg-google-blue shadow-xs';
        badgeStep1.innerHTML = '1';

        statusStep2.className = 'badge bg-light text-muted rounded-pill extra-small font-google border';
        statusStep2.innerText = '🔒 En attente du cycle';
        badgeStep2.className = 'google-step-badge bg-secondary opacity-50';

        updateExpressProgress(0);
        return;
    }

    badgeStep1.innerHTML = '<i class="bi bi-check-lg"></i>';
    badgeStep1.className = 'google-step-badge bg-success text-white shadow-xs icon-pop';
    statusStep1.className = 'badge bg-success text-white px-2.5 py-1 rounded-pill extra-small font-google fw-bold shadow-xs';
    statusStep1.innerText = '✓ Validé';

    if (cycleLoader) cycleLoader.classList.remove('d-none');

    levelSelect.disabled = false;
    levelSelect.classList.remove('opacity-50');
    levelSelect.classList.add('step-highlight-active');
    labelStep2.className = 'form-label font-google fw-bold text-google-dark small mb-0';
    badgeStep2.className = 'google-step-badge bg-google-green shadow-xs';
    statusStep2.className = 'badge bg-primary text-white px-2.5 py-1 rounded-pill extra-small font-google fw-bold shadow-xs';
    statusStep2.innerText = 'Étape 2 Active';
    updateExpressProgress(33);

    fetch(`/timetables/api/wizard/levels?cycle_id=${cycleId}`)
        .then(r => r.json())
        .then(data => {
            if (cycleLoader) cycleLoader.classList.add('d-none');
            const levelsList = data.levels ? data.levels : (Array.isArray(data) ? data : []);
            let options = '<option value="">-- 2. Sélectionner le niveau --</option>';
            options += '<option value="0">Toutes les classes du cycle (Vue globale)</option>';
            if (levelsList && levelsList.length > 0) {
                levelsList.forEach(l => {
                    const label = l.nom || (l.libelle_fr ? l.libelle_fr : (l.libelle_en ? l.libelle_en : `Niveau ${l.code || l.id}`));
                    options += `<option value="${l.id}">${escapeHtml(label)}</option>`;
                });
            } else {
                showGoogleToast('Aucun niveau spécifique associé à ce cycle. Option "Toutes les classes" disponible.', 'bi-info-circle');
            }
            levelSelect.innerHTML = options;
        })
        .catch(err => {
            if (cycleLoader) cycleLoader.classList.add('d-none');
            console.error('Erreur chargement niveaux express:', err);
            levelSelect.innerHTML = '<option value="">-- 2. Sélectionner le niveau --</option><option value="0">Toutes les classes du cycle (Vue globale)</option>';
            showGoogleToast('Impossible de charger les niveaux du cycle.', 'bi-exclamation-triangle-fill');
        });
}

function onLevelChangeSequential(levelId) {
    const weekSelect = document.getElementById('express_week_id');
    const badgeStep2 = document.getElementById('badgeStep2');
    const statusStep2 = document.getElementById('statusStep2');
    const badgeStep3 = document.getElementById('badgeStep3');
    const statusStep3 = document.getElementById('statusStep3');
    const labelStep3 = document.getElementById('labelStep3');

    if (levelId === '') {
        weekSelect.disabled = true;
        weekSelect.classList.add('opacity-50');
        statusStep2.innerText = 'Étape 2 Active';
        updateExpressProgress(33);
        return;
    }

    badgeStep2.innerHTML = '<i class="bi bi-check-lg"></i>';
    badgeStep2.className = 'google-step-badge bg-success text-white shadow-xs icon-pop';
    statusStep2.className = 'badge bg-success text-white px-2.5 py-1 rounded-pill extra-small font-google fw-bold shadow-xs';
    statusStep2.innerText = '✓ Validé';

    weekSelect.disabled = false;
    weekSelect.classList.remove('opacity-50');
    weekSelect.classList.add('step-highlight-active');
    labelStep3.className = 'form-label font-google fw-bold text-google-dark small mb-0';
    badgeStep3.className = 'google-step-badge bg-google-yellow text-dark shadow-xs';
    statusStep3.className = 'badge bg-primary text-white px-2.5 py-1 rounded-pill extra-small font-google fw-bold shadow-xs';
    statusStep3.innerText = 'Étape 3 Finale';

    showGoogleToast('Étape 2/3 Validée ! Choisissez enfin la semaine pour générer.', 'bi-layers-fill');
    updateExpressProgress(66);
}

function onWeekChangeSequential(weekId) {
    if (weekId === '') return;

    const badgeStep3 = document.getElementById('badgeStep3');
    const statusStep3 = document.getElementById('statusStep3');

    badgeStep3.innerHTML = '<i class="bi bi-check-lg"></i>';
    badgeStep3.className = 'google-step-badge bg-success text-white shadow-xs icon-pop';
    statusStep3.className = 'badge bg-success text-white px-2.5 py-1 rounded-pill extra-small font-google fw-bold shadow-xs';
    statusStep3.innerText = '✓ Complet';

    showGoogleToast('⚡ 3/3 Informations validées ! Lancement automatique de la grille...', 'bi-rocket-takeoff-fill');
    updateExpressProgress(100);

    triggerGoogleInstantLaunch();
}

document.addEventListener('DOMContentLoaded', function() {
    resetWizard();

    const expressForm = document.getElementById('expressForm');
    if (expressForm) {
        expressForm.addEventListener('submit', function() {
            triggerGoogleInstantLaunch();
        });
    }

    const wizardForm = document.getElementById('wizardForm');
    if (wizardForm) {
        wizardForm.addEventListener('submit', function() {
            const btn = document.getElementById('btnFinalGenerate');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Génération en cours...';
            }
        });
    }
});
</script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

    .transition-all {
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .hover-lift {
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .hover-lift:hover {
        transform: translateY(-4px) scale(1.01);
        box-shadow: 0 10px 25px -5px rgba(26, 115, 232, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
    }

    .hover-scale:hover {
        transform: scale(1.03);
    }
    .hover-scale:active {
        transform: scale(0.98);
    }

    .preset-interactive-card {
        background: var(--bg-card-secondary, #ffffff);
        border: 1px solid rgba(0, 0, 0, 0.08);
    }
    .preset-interactive-card:hover {
        background: #ffffff;
        border-color: #1a73e8 !important;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(26, 115, 232, 0.12) !important;
    }
    .preset-interactive-card:hover .card-arrow-icon {
        transform: translateX(4px) scale(1.1);
    }
    .preset-card-active {
        border-color: #34a853 !important;
        box-shadow: 0 0 0 4px rgba(52, 168, 83, 0.2) !important;
    }

    .header-hover-card:hover {
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06) !important;
    }

    .icon-pulse-subtle {
        animation: pulseSubtle 2.5s infinite ease-in-out;
    }
    @keyframes pulseSubtle {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.06); }
    }

    .icon-pop {
        animation: popScale 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    @keyframes popScale {
        0% { transform: scale(0.5); }
        100% { transform: scale(1); }
    }

    .animate-bounce-short {
        animation: bounceShort 1.5s infinite;
    }
    @keyframes bounceShort {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(-4px); }
        60% { transform: translateY(-2px); }
    }

    .icon-star-rotate:hover {
        transform: rotate(20deg) scale(1.2);
        transition: transform 0.2s ease;
    }

    .skeleton-card {
        background: linear-gradient(90deg, rgba(220, 225, 235, 0.4) 25%, rgba(245, 247, 250, 0.8) 50%, rgba(220, 225, 235, 0.4) 75%);
        background-size: 200% 100%;
        animation: shimmerSkeleton 1.5s infinite;
    }
    @keyframes shimmerSkeleton {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    .skeleton-avatar {
        width: 48px;
        height: 48px;
        background: rgba(200, 205, 215, 0.5);
    }
    .skeleton-line {
        height: 12px;
        background: rgba(200, 205, 215, 0.5);
        border-radius: 6px;
    }
    .skeleton-title {
        width: 60%;
    }
    .skeleton-subtitle {
        width: 35%;
    }

    .toast-feedback-entry {
        animation: slideInRight 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .toast-feedback-exit {
        animation: slideOutRight 0.3s ease forward;
    }
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }

    .step-highlight-active {
        border-color: #1a73e8 !important;
        box-shadow: 0 0 0 4px rgba(26, 115, 232, 0.25) !important;
        animation: pulseStep 1.8s infinite alternate;
    }
    @keyframes pulseStep {
        0% { box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.2); }
        100% { box-shadow: 0 0 0 6px rgba(26, 115, 232, 0.35); }
    }

    .bg-gradient-google-animated {
        background: linear-gradient(90deg, #4285f4, #ea4335, #fbbc05, #34a853, #4285f4);
        background-size: 300% 100%;
        animation: googleGradientMove 3s linear infinite;
    }
    @keyframes googleGradientMove {
        0% { background-position: 0% 0%; }
        100% { background-position: 100% 0%; }
    }

    .google-input-glow {
        transition: all 0.25s ease;
    }
    .google-input-glow:focus {
        border-color: #1a73e8 !important;
        box-shadow: 0 0 0 4px rgba(26, 115, 232, 0.18) !important;
    }

    .google-btn-outlined {
        background: transparent;
        border: 1.5px solid rgba(0, 0, 0, 0.12);
        color: var(--text-color, #202124);
    }
    .google-btn-outlined:hover {
        background: rgba(26, 115, 232, 0.06);
        border-color: #1a73e8;
        color: #1a73e8;
    }

    .google-btn-ghost {
        background: transparent;
        border: none;
        color: #5f6368;
    }
    .google-btn-ghost:hover {
        background: rgba(0, 0, 0, 0.05);
        color: #202124;
    }

    [data-theme="dark"] .preset-interactive-card {
        background: #1e293b;
        border-color: rgba(255, 255, 255, 0.1);
    }
    [data-theme="dark"] .preset-interactive-card:hover {
        background: #0f172a;
        border-color: #60a5fa !important;
    }
    [data-theme="dark"] .google-btn-outlined {
        border-color: rgba(255, 255, 255, 0.15);
        color: #f8fafc;
    }
    [data-theme="dark"] .google-btn-outlined:hover {
        background: rgba(96, 165, 250, 0.15);
        border-color: #60a5fa;
        color: #60a5fa;
    }
    [data-theme="dark"] .skeleton-card {
        background: linear-gradient(90deg, #1e293b 25%, #334155 50%, #1e293b 75%);
    }
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../templates/layout.php';


