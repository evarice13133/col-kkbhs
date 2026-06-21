<?php
$title = __('tranches_title');
ob_start();
?>

<!-- Load Google Font for premium feel -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    .premium-container {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    
    .glass-card {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(14px) saturate(190%);
        -webkit-backdrop-filter: blur(14px) saturate(190%);
        border: 1px solid rgba(255, 255, 255, 0.45);
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        transition: all 0.3s ease;
    }
    
    .glass-card:hover {
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
    }
    
    .tranche-row {
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(0, 0, 0, 0.06);
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.9);
        margin-bottom: 15px;
    }
    
    .tranche-row:hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05) !important;
        border-color: var(--bs-primary);
        transform: translateY(-2px);
    }
    
    .premium-input {
        border-radius: 10px;
        border: 1px solid rgba(0, 0, 0, 0.12);
        padding: 0.6rem 0.9rem;
        transition: all 0.2s ease-in-out;
        background-color: #ffffff;
    }
    
    .premium-input:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
        background-color: #ffffff;
    }
    
    /* Allocation alert styles */
    .status-alert-success {
        background: rgba(25, 135, 84, 0.07);
        color: #198754;
        border: 1px solid rgba(25, 135, 84, 0.15);
    }
    .status-alert-warning {
        background: rgba(255, 193, 7, 0.07);
        color: #664d03;
        border: 1px solid rgba(255, 193, 7, 0.15);
    }
    .status-alert-danger {
        background: rgba(220, 53, 69, 0.07);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.15);
    }
    
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(12px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    
    .animate-slide-in {
        animation: slideIn 0.35s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }
    
    .target-status-box {
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.02) 0%, rgba(79, 70, 229, 0.05) 100%);
        border-radius: 16px;
        border: 1px solid rgba(79, 70, 229, 0.12);
    }
    
    .active-config-card {
        border-radius: 16px;
        transition: all 0.3s ease;
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .active-config-card:hover {
        border-color: rgba(79, 70, 229, 0.25);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
        transform: translateY(-2px);
    }
    
    .timeline-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: var(--bs-primary);
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
    }
    
    .configs-scroll-container {
        max-height: 680px;
        overflow-y: auto;
        padding-right: 8px;
    }
    
    .configs-scroll-container::-webkit-scrollbar {
        width: 6px;
    }
    
    .configs-scroll-container::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .configs-scroll-container::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.08);
        border-radius: 10px;
    }
    
    .configs-scroll-container::-webkit-scrollbar-thumb:hover {
        background: rgba(0, 0, 0, 0.18);
    }

    .btn-xs {
        padding: 0.25rem 0.6rem;
        font-size: 0.7rem;
        font-weight: 700;
        border-radius: 50px;
    }
    
    .badge-target {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.3px;
    }
    
    /* Dark Theme Support */
    [data-theme="dark"] .glass-card {
        background: rgba(30, 41, 59, 0.45);
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
    }
    
    [data-theme="dark"] .tranche-row {
        background: rgba(30, 41, 59, 0.25);
        border-color: rgba(255, 255, 255, 0.08);
    }
    
    [data-theme="dark"] .premium-input {
        background-color: rgba(15, 23, 42, 0.6) !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
        color: #f8fafc !important;
    }
    
    [data-theme="dark"] .premium-input:focus {
        background-color: rgba(15, 23, 42, 0.8) !important;
        border-color: var(--bs-primary) !important;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.25) !important;
    }
    
    [data-theme="dark"] .active-config-card {
        background: rgba(30, 41, 59, 0.2) !important;
        border-color: rgba(255, 255, 255, 0.05) !important;
    }

    [data-theme="dark"] .active-config-card:hover {
        border-color: rgba(79, 70, 229, 0.4) !important;
    }
    
    [data-theme="dark"] .timeline-dot {
        border-color: #020617; /* Matches --bg-body dark background */
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.4);
    }
    
    [data-theme="dark"] .configs-scroll-container::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.12);
    }
    
    [data-theme="dark"] .configs-scroll-container::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.22);
    }

    [data-theme="dark"] .configs-scroll-container::-webkit-scrollbar-track {
        background: transparent;
    }
</style>

<div class="premium-container animate-fade-in container-fluid py-3 px-md-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-black text-main-theme mb-0 fs-4" style="letter-spacing: -0.5px;"><?= __('tranches_title') ?></h2>
            <p class="text-muted-theme small mb-0"><?= __('tranches_subtitle') ?></p>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row g-4">
        <!-- Configuration Form Column (Left) -->
        <div class="col-lg-6">
            <div class="glass-card p-4">
                <h5 class="fw-bold text-main-theme mb-4">
                    <i class="bi bi-sliders text-primary me-2"></i><?= __('config_editor') ?>
                </h5>
                <form action="/school_fees/tranches" method="POST" id="tranche-config-form">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">

                    <!-- Cible Type -->
                    <div class="mb-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2" style="letter-spacing: 0.5px;"><?= __('application_level') ?></label>
                        <select name="target_type" id="target_type" class="form-select premium-input" required>
                            <option value="" disabled selected><?= __('choose_level') ?></option>
                            <option value="class"><?= __('by_class') ?></option>
                            <option value="cycle"><?= __('by_cycle') ?></option>
                            <option value="teaching_type"><?= __('by_teaching_type') ?></option>
                        </select>
                    </div>

                    <!-- Target Element Selects -->
                    <div class="mb-4 d-none" id="div-class-select">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2" style="letter-spacing: 0.5px;"><?= __('select_class_editor') ?></label>
                        <select name="target_id" id="select-class" class="form-select premium-input">
                            <option value="" disabled selected><?= __('choose_class_placeholder') ?></option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= h($c['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4 d-none" id="div-cycle-select">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2" style="letter-spacing: 0.5px;"><?= __('select_cycle_editor') ?></label>
                        <select name="target_id" id="select-cycle" class="form-select premium-input">
                            <option value="" disabled selected><?= __('choose_cycle_placeholder') ?></option>
                            <?php foreach ($cycles as $cy): ?>
                                <option value="<?= $cy['id'] ?>"><?= h($cy['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4 d-none" id="div-teaching-type-select">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2" style="letter-spacing: 0.5px;"><?= __('select_teaching_type_editor') ?></label>
                        <select name="target_id" id="select-teaching-type" class="form-select premium-input">
                            <option value="" disabled selected><?= __('choose_type_placeholder') ?></option>
                            <?php foreach ($teachingTypes as $tt): ?>
                                <option value="<?= $tt['id'] ?>"><?= h($tt['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Target Info & Balance Status Box -->
                    <div id="target-status-banner" class="d-none target-status-box p-3 mb-4 animate-slide-in">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2 border-primary border-opacity-10">
                            <span class="small fw-bold text-uppercase text-muted-theme" style="letter-spacing: 0.5px;"><?= __('distribution_state') ?></span>
                            <span id="inheritance-badge" class="badge">Configuration</span>
                        </div>
                        <div class="row g-2 text-center mb-3">
                            <div class="col-6 border-end border-primary border-opacity-10">
                                <div class="extra-small text-muted text-uppercase mb-1" style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.3px;"><?= __('expected_tuition') ?></div>
                                <div class="fs-5 fw-bold text-primary" id="tuition-amount-display">0 FCFA</div>
                            </div>
                            <div class="col-6">
                                <div class="extra-small text-muted text-uppercase mb-1" style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.3px;"><?= __('total_allocated') ?></div>
                                <div class="fs-5 fw-bold text-dark" id="total-tranches-display">0 FCFA</div>
                            </div>
                        </div>

                        <!-- Sleek progress bar -->
                        <div class="px-1 mb-2">
                            <div class="progress" style="height: 8px; border-radius: 50px; background-color: rgba(0,0,0,0.06);">
                                <div id="allocation-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%; border-radius: 50px;"></div>
                            </div>
                        </div>

                        <div id="balance-status-alert" class="small p-2.5 rounded-3 text-center fw-semibold mt-3">
                            <!-- Injected Message -->
                        </div>
                    </div>

                    <!-- Tranche Definitions Container -->
                    <div class="border-top pt-4 mt-3">
                        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-0" style="letter-spacing: 0.5px;"><?= __('definition_tranches') ?></label>
                            
                            <!-- Quick tools bar -->
                            <div class="d-flex gap-1.5" id="quick-distribution-toolbar" style="display: none !important;">
                                <button type="button" class="btn btn-xs btn-outline-primary" id="btn-quick-split" title="<?= __('equidistribute_tooltip') ?>">
                                    <i class="bi bi-distribute-horizontal me-1"></i><?= __('equidistribute') ?>
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="btn-quick-mono" title="<?= __('monotranche_tooltip') ?>">
                                    <i class="bi bi-file-earmark-fill me-1"></i><?= __('monotranche') ?>
                                </button>
                            </div>
                        </div>
                        
                        <div id="tranches-rows-container" class="d-flex flex-column gap-2 mb-3">
                            <div class="text-center py-5 text-muted small bg-light bg-opacity-40 rounded-4 border border-dashed">
                                <i class="bi bi-shield-lock fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                <?= __('editor_activation_help') ?>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3 border-top pt-3">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-bold" id="btn-add-row" style="font-size: 0.75rem; display: none;">
                                <i class="bi bi-plus-circle-fill me-1"></i> <?= __('add_tranche') ?>
                            </button>
                            <div class="text-end text-muted extra-small" style="font-weight: 700; font-size: 0.8rem;">
                                <?= __('total_tuition_label') ?> <span id="total-scolarite-sum" class="text-primary fw-extrabold">0</span> FCFA
                            </div>
                        </div>
                    </div>

                    <!-- Validation button -->
                    <div class="mt-4 pt-3 border-top text-end">
                        <button type="submit" class="btn btn-primary rounded-pill px-5 py-2.5 fw-bold shadow-sm" id="btn-submit-form" disabled>
                            <i class="bi bi-check-circle-fill me-2"></i><?= __('save_config') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Configurations List Column (Right) -->
        <div class="col-lg-6">
            <div class="glass-card p-4 h-100 d-flex flex-column">
                <!-- Card Header with Search -->
                <div class="d-flex align-items-center justify-content-between mb-4 gap-3 flex-wrap">
                    <h5 class="fw-bold text-main-theme mb-0">
                        <i class="bi bi-list-task text-primary me-2"></i><?= __('active_configs') ?>
                    </h5>
                    <div class="search-box position-relative" style="min-width: 250px;">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="search-config" class="form-control form-control-sm ps-5 rounded-pill premium-input" placeholder="<?= __('search_class_cycle') ?>">
                    </div>
                </div>

                <!-- Accordion Timeline list -->
                <div class="configs-scroll-container flex-grow-1" id="active-configs-list">
                    <?php
                    // Group installments
                    $groupedInstallments = [];
                    foreach ($installments as $inst) {
                        $key = '';
                        $label = '';
                        $type = '';
                        if ($inst['class_id']) {
                            $key = 'class_' . $inst['class_id'];
                            $label = __('class') . ' : ' . $inst['class_name'];
                            $type = 'primary';
                        } elseif ($inst['cycle_id']) {
                            $key = 'cycle_' . $inst['cycle_id'];
                            $label = __('cycle') . ' : ' . $inst['cycle_name'];
                            $type = 'info';
                        } elseif ($inst['teaching_type_id']) {
                            $key = 'teaching_type_' . $inst['teaching_type_id'];
                            $label = __('teaching_type') . ' : ' . $inst['teaching_type_name'];
                            $type = 'warning';
                        }
                        
                        if (!isset($groupedInstallments[$key])) {
                            $groupedInstallments[$key] = [
                                'label' => $label,
                                'type' => $type,
                                'target_type' => $inst['class_id'] ? 'class' : ($inst['cycle_id'] ? 'cycle' : 'teaching_type'),
                                'target_id' => $inst['class_id'] ?: ($inst['cycle_id'] ?: $inst['teaching_type_id']),
                                'total_amount' => 0.0,
                                'items' => []
                            ];
                        }
                        
                        $groupedInstallments[$key]['total_amount'] += (float)$inst['amount'];
                        $groupedInstallments[$key]['items'][] = $inst;
                    }
                    ?>

                    <?php if (empty($groupedInstallments)): ?>
                        <div class="text-center py-5 text-muted glass-card border border-dashed rounded-4 bg-light bg-opacity-25">
                            <i class="bi bi-calendar-x fs-1 text-secondary opacity-50 mb-3 d-block"></i>
                            <p class="mb-0 fw-medium"><?= __('no_config_yet') ?></p>
                            <small class="text-muted text-uppercase extra-small font-weight-bold"><?= __('use_editor_to_start') ?></small>
                        </div>
                    <?php else: ?>
                        <?php foreach ($groupedInstallments as $key => $group): ?>
                            <div class="active-config-card mb-3 p-3 animate-slide-in border border-opacity-50">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="badge badge-target bg-<?= $group['type'] ?> bg-opacity-10 text-<?= $group['type'] ?> border border-<?= $group['type'] ?> border-opacity-20 px-3 py-1.5 rounded-pill">
                                        <?= h($group['label']) ?>
                                    </span>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold text-dark fs-6"><?= number_format($group['total_amount'], 0, '.', ' ') ?> <span class="small text-muted" style="font-size:0.7rem;">FCFA</span></span>
                                        <button type="button" class="btn btn-sm btn-outline-primary border-0 rounded-circle btn-load-target" data-type="<?= $group['target_type'] ?>" data-id="<?= $group['target_id'] ?>" title="Charger dans l'éditeur pour modifier">
                                            <i class="bi bi-pencil-square fs-5"></i>
                                        </button>
                                    </div>
                                </div>
                                <!-- Timeline Detail -->
                                <div class="timeline-container ps-3 ms-2 position-relative" style="border-left: 2px solid rgba(79, 70, 229, 0.08);">
                                    <?php foreach ($group['items'] as $item): ?>
                                        <div class="timeline-item mb-2 position-relative">
                                            <div class="timeline-dot position-absolute" style="left: -21px; top: 6px;"></div>
                                            <div class="d-flex justify-content-between align-items-center small">
                                                <span class="fw-semibold text-dark"><?= h($item['name']) ?></span>
                                                <div class="text-end">
                                                    <span class="fw-bold text-primary me-2"><?= number_format($item['amount'], 0, '.', ' ') ?> FCFA</span>
                                                    <span class="text-muted" style="font-size: 0.72rem;"><i class="bi bi-calendar-event me-1"></i><?= date('d/m/Y', strtotime($item['deadline_date'])) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const targetTypeSelect = document.getElementById('target_type');
    const divClass = document.getElementById('div-class-select');
    const divCycle = document.getElementById('div-cycle-select');
    const divTeachingType = document.getElementById('div-teaching-type-select');

    const selectClass = document.getElementById('select-class');
    const selectCycle = document.getElementById('select-cycle');
    const selectTeachingType = document.getElementById('select-teaching-type');
    
    const statusBanner = document.getElementById('target-status-banner');
    const tuitionAmountDisplay = document.getElementById('tuition-amount-display');
    const totalTranchesDisplay = document.getElementById('total-tranches-display');
    const progressBar = document.getElementById('allocation-progress-bar');
    const balanceStatusAlert = document.getElementById('balance-status-alert');
    const inheritanceBadge = document.getElementById('inheritance-badge');
    const tranchesRowsContainer = document.getElementById('tranches-rows-container');
    const btnAdd = document.getElementById('btn-add-row');
    const btnSubmit = document.getElementById('btn-submit-form');
    const form = document.getElementById('tranche-config-form');
    const quickToolbar = document.getElementById('quick-distribution-toolbar');
    
    const btnQuickSplit = document.getElementById('btn-quick-split');
    const btnQuickMono = document.getElementById('btn-quick-mono');

    let currentTargetTuition = 0;

    // Toggle target inputs
    targetTypeSelect.addEventListener('change', function() {
        divClass.classList.add('d-none');
        divCycle.classList.add('d-none');
        divTeachingType.classList.add('d-none');
        selectClass.required = false;
        selectCycle.required = false;
        selectTeachingType.required = false;
        
        selectClass.value = '';
        selectCycle.value = '';
        selectTeachingType.value = '';

        resetFormState();

        if (this.value === 'class') {
            divClass.classList.remove('d-none');
            selectClass.required = true;
            selectClass.name = 'target_id';
            selectCycle.name = '';
            selectTeachingType.name = '';
        } else if (this.value === 'cycle') {
            divCycle.classList.remove('d-none');
            selectCycle.required = true;
            selectCycle.name = 'target_id';
            selectClass.name = '';
            selectTeachingType.name = '';
        } else if (this.value === 'teaching_type') {
            divTeachingType.classList.remove('d-none');
            selectTeachingType.required = true;
            selectTeachingType.name = 'target_id';
            selectClass.name = '';
            selectCycle.name = '';
        }
    });

    // Handle change of specific targets to load existing tranches
    [selectClass, selectCycle, selectTeachingType].forEach(select => {
        select.addEventListener('change', function() {
            const targetType = targetTypeSelect.value;
            const targetId = this.value;
            if (targetType && targetId) {
                loadTargetConfig(targetType, targetId);
            } else {
                resetFormState();
            }
        });
    });

    function resetFormState() {
        statusBanner.classList.add('d-none');
        tranchesRowsContainer.innerHTML = `
            <div class="text-center py-5 text-muted small bg-light bg-opacity-40 rounded-4 border border-dashed">
                <i class="bi bi-shield-lock fs-2 d-block mb-2 text-secondary opacity-50"></i>
                Veuillez sélectionner un niveau d'application et une cible ci-dessus pour activer l'éditeur.
            </div>
        `;
        currentTargetTuition = 0;
        btnSubmit.disabled = true;
        btnAdd.style.display = 'none';
        quickToolbar.setAttribute('style', 'display: none !important;');
        calculateSum();
    }

    function loadTargetConfig(targetType, targetId) {
        tranchesRowsContainer.innerHTML = `
            <div class="text-center py-5 text-muted w-100 animate-slide-in" id="loading-spinner">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                <?= __('loading_config') ?>
            </div>
        `;
        statusBanner.classList.add('d-none');
        btnSubmit.disabled = true;
        btnAdd.style.display = 'none';
        quickToolbar.setAttribute('style', 'display: none !important;');

        fetch(`/school_fees/tranches?ajax=1&target_type=${targetType}&target_id=${targetId}`)
            .then(res => res.json())
            .then(data => {
                tranchesRowsContainer.innerHTML = '';
                
                currentTargetTuition = parseFloat(data.tuition_amount) || 0;
                btnSubmit.disabled = false;
                btnAdd.style.display = 'inline-block';
                quickToolbar.setAttribute('style', 'display: flex !important;');
                
                inheritanceBadge.textContent = data.inherited_from;
                if (data.inherited) {
                    inheritanceBadge.className = 'badge bg-warning text-dark px-2.5 py-1.5 rounded-pill fw-bold';
                } else {
                    inheritanceBadge.className = 'badge bg-success px-2.5 py-1.5 rounded-pill fw-bold';
                }

                statusBanner.classList.remove('d-none');
                tuitionAmountDisplay.textContent = currentTargetTuition.toLocaleString('fr-FR') + ' FCFA';

                if (data.installments && data.installments.length > 0) {
                    data.installments.forEach((inst, index) => {
                        createTrancheRow(inst.name, parseFloat(inst.amount), inst.deadline_date, index + 1);
                    });
                } else {
                    const part = currentTargetTuition > 0 ? Math.round(currentTargetTuition / 3) : 0;
                    createTrancheRow('Tranche 1', part, '', 1);
                    createTrancheRow('Tranche 2', part, '', 2);
                    createTrancheRow('Tranche 3', currentTargetTuition - (part * 2) > 0 ? currentTargetTuition - (part * 2) : 0, '', 3);
                }
                
                calculateSum();
            })
            .catch(err => {
                console.error(err);
                tranchesRowsContainer.innerHTML = `
                    <div class="alert alert-danger small m-2">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= __('error_loading_data') ?>
                    </div>
                `;
            });
    }

    function createTrancheRow(name = '', amount = 0, deadline = '', orderNum = null) {
        const num = orderNum || (tranchesRowsContainer.querySelectorAll('.tranche-row').length + 1);
        const row = document.createElement('div');
        row.className = 'tranche-row p-3 shadow-sm bg-light bg-opacity-50 animate-slide-in';
        row.style.borderLeft = '4px solid var(--bs-primary)';
        
        if (!deadline) {
            deadline = new Date().toISOString().slice(0, 10);
        }

        row.innerHTML = `
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label text-muted-theme fw-bold extra-small mb-1 text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.3px;">
                        <i class="bi bi-tag-fill me-1 text-primary"></i><?= __('tranche_name') ?>
                    </label>
                    <input type="text" name="tranche_name[]" class="form-control form-control-sm premium-input" value="${name || 'Tranche ' + num}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted-theme fw-bold extra-small mb-1 text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.3px;">
                        <i class="bi bi-cash-stack me-1 text-success"></i><?= __('tranche_amount') ?>
                    </label>
                    <input type="number" name="tranche_amount[]" min="0" class="form-control form-control-sm premium-input text-end fw-bold input-amount" value="${amount}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted-theme fw-bold extra-small mb-1 text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.3px;">
                        <i class="bi bi-calendar-check-fill me-1 text-info"></i><?= __('tranche_deadline') ?>
                    </label>
                    <input type="date" name="tranche_deadline[]" class="form-control form-control-sm premium-input input-deadline" value="${deadline}" required>
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 p-2 btn-remove-row rounded-circle" title="<?= __('delete_tranche_tooltip') ?>">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
            </div>
        `;

        row.querySelector('.btn-remove-row').addEventListener('click', function() {
            row.style.transform = 'scale(0.95)';
            row.style.opacity = '0';
            setTimeout(() => {
                row.remove();
                reindexRows();
                calculateSum();
            }, 200);
        });

        row.querySelector('.input-amount').addEventListener('input', calculateSum);
        row.querySelector('.input-deadline').addEventListener('change', validateDates);

        tranchesRowsContainer.appendChild(row);
        calculateSum();
    }

    btnAdd.addEventListener('click', function() {
        createTrancheRow('', 0, '', null);
    });

    function reindexRows() {
        tranchesRowsContainer.querySelectorAll('.tranche-row').forEach((row, idx) => {
            const num = idx + 1;
            const nameInput = row.querySelector('input[name="tranche_name[]"]');
            if (nameInput && nameInput.value.startsWith('Tranche ')) {
                nameInput.value = 'Tranche ' + num;
            }
        });
    }

    function calculateSum() {
        let sum = 0;
        tranchesRowsContainer.querySelectorAll('.input-amount').forEach(inp => {
            sum += parseFloat(inp.value) || 0;
        });
        
        totalTranchesDisplay.textContent = sum.toLocaleString('fr-FR') + ' FCFA';
        document.getElementById('total-scolarite-sum').textContent = sum.toLocaleString('fr-FR');

        // Progress bar logic
        if (currentTargetTuition > 0) {
            const pct = Math.min((sum / currentTargetTuition) * 100, 100);
            progressBar.style.width = pct + '%';
            if (sum === currentTargetTuition) {
                progressBar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-success';
            } else if (sum > currentTargetTuition) {
                progressBar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-danger';
            } else {
                progressBar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-primary';
            }
        } else {
            progressBar.style.width = '0%';
        }

        // Check balance
        if (targetTypeSelect.value && !statusBanner.classList.contains('d-none')) {
            balanceStatusAlert.className = 'small p-2.5 rounded-3 text-center fw-semibold';
            if (currentTargetTuition === 0) {
                balanceStatusAlert.classList.add('status-alert-warning');
                balanceStatusAlert.innerHTML = '<i class="bi bi-info-circle-fill me-1"></i> <?= __('no_tuition_configured_target') ?>';
            } else if (sum === currentTargetTuition) {
                balanceStatusAlert.classList.add('status-alert-success');
                balanceStatusAlert.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> <?= __('balanced_distribution_100') ?>';
            } else {
                const diff = currentTargetTuition - sum;
                balanceStatusAlert.classList.add('status-alert-danger');
                if (diff > 0) {
                    balanceStatusAlert.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> <?= __('remaining_to_distribute') ?> <strong>+${diff.toLocaleString('fr-FR')} FCFA</strong>`;
                } else {
                    balanceStatusAlert.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> <?= __('total_exceeds_tuition') ?> <strong>${Math.abs(diff).toLocaleString('fr-FR')} FCFA</strong>`;
                }
            }
        }
        
        validateDates();
    }

    function validateDates() {
        let hasError = false;
        let lastDateVal = null;
        
        tranchesRowsContainer.querySelectorAll('.tranche-row').forEach((row, idx) => {
            const dateInput = row.querySelector('.input-deadline');
            const errorDivId = 'date-error-' + idx;
            let errorDiv = row.querySelector('#' + errorDivId);
            
            if (errorDiv) errorDiv.remove();
            
            if (!dateInput || !dateInput.value) return;
            
            const currentDateVal = new Date(dateInput.value);
            
            if (lastDateVal && currentDateVal < lastDateVal) {
                hasError = true;
                const err = document.createElement('div');
                err.id = errorDivId;
                err.className = 'text-danger extra-small mt-1 fw-bold';
                err.style.fontSize = '0.7rem';
                err.innerHTML = '<i class="bi bi-exclamation-circle"></i> <?= __('deadline_chronology_error') ?>';
                dateInput.parentNode.appendChild(err);
            }
            
            lastDateVal = currentDateVal;
        });
        
        return !hasError;
    }

    // Quick Split & Mono Actions
    btnQuickSplit.addEventListener('click', function() {
        const rows = tranchesRowsContainer.querySelectorAll('.tranche-row');
        if (rows.length === 0 || currentTargetTuition <= 0) return;
        
        const count = rows.length;
        const part = Math.floor(currentTargetTuition / count);
        const remainder = currentTargetTuition - (part * count);
        
        rows.forEach((row, idx) => {
            const amtInput = row.querySelector('.input-amount');
            if (amtInput) {
                amtInput.value = idx === count - 1 ? (part + remainder) : part;
            }
        });
        calculateSum();
    });

    btnQuickMono.addEventListener('click', function() {
        tranchesRowsContainer.innerHTML = '';
        createTrancheRow('Tranche Unique', currentTargetTuition, '', 1);
        calculateSum();
    });

    // Form submission validation
    form.addEventListener('submit', function(e) {
        let sum = 0;
        tranchesRowsContainer.querySelectorAll('.input-amount').forEach(inp => {
            sum += parseFloat(inp.value) || 0;
        });

        if (!validateDates()) {
            e.preventDefault();
            alert(<?= json_encode(__('error_chronology_alert')) ?>);
            return;
        }

        if (currentTargetTuition > 0 && sum !== currentTargetTuition) {
            const diff = currentTargetTuition - sum;
            const msg = <?= json_encode(__('warning_mismatch_alert')) ?>;
            if (!confirm(msg)) {
                e.preventDefault();
            }
        }
    });

    // Click on load target button in active list (Right side)
    document.addEventListener('click', function(e) {
        const loadBtn = e.target.closest('.btn-load-target');
        if (loadBtn) {
            const targetType = loadBtn.dataset.type;
            const targetId = loadBtn.dataset.id;
            
            // Set values and trigger events
            targetTypeSelect.value = targetType;
            targetTypeSelect.dispatchEvent(new Event('change'));
            
            setTimeout(() => {
                let activeSelect = null;
                if (targetType === 'class') activeSelect = selectClass;
                else if (targetType === 'cycle') activeSelect = selectCycle;
                else if (targetType === 'teaching_type') activeSelect = selectTeachingType;
                
                if (activeSelect) {
                    activeSelect.value = targetId;
                    activeSelect.dispatchEvent(new Event('change'));
                }
            }, 100);
            
            // Smooth scroll to form editor
            form.scrollIntoView({ behavior: 'smooth' });
        }
    });

    // Client-side table filter
    const searchInput = document.getElementById('search-config');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const cards = document.querySelectorAll('#active-configs-list .active-config-card');
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                if (text.includes(query)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
