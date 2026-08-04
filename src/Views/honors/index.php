<?php
$title = __('honor_roll_generation');
ob_start();
?>

<div class="animate-fade-in admin-analytics module-bureau-flow">

    <!-- BARRE D'ACTIONS COMPLÈTE : Style Floating Island Responsive -->
    <div class="d-flex justify-content-center mb-4 mb-md-5">
        <div class="filter-island px-3 py-2 shadow-lg animate-slide-down">
            <form method="GET" action="/honors" class="d-flex align-items-center gap-2 gap-md-3 flex-wrap flex-md-nowrap filter-form w-100">
                
                <div class="row g-2 flex-grow-1 w-100 m-0">
                    <div class="col-12 col-sm-6 col-lg flex-grow-1 p-0 px-sm-1">
                        <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-3 py-1 w-100">
                            <span class="input-group-text border-0 bg-transparent text-primary small fw-bold text-uppercase me-1">
                                <?= __('year') ?>
                            </span>
                            <select name="academic_year_id" class="form-select border-0 bg-transparent shadow-none fw-bold text-main"
                                onchange="this.form.submit()">
                                <?php foreach ($academicYears as $year): ?>
                                    <option value="<?= $year['id'] ?>" <?= $academicYearId === (int) $year['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $year['nom']) ?>
                                        <?= (int) $year['is_active'] === 1 ? '(' . __('active') . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-lg flex-grow-1 p-0 px-sm-1">
                        <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-3 py-1 w-100">
                            <span class="input-group-text border-0 bg-transparent text-primary small fw-bold text-uppercase me-1">
                                <?= __('class') ?>
                            </span>
                            <select name="class_id" class="form-select border-0 bg-transparent shadow-none fw-bold text-main"
                                onchange="this.form.submit()">
                                <option value=""><?= __('choose_class') ?></option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= $class['id'] ?>" <?= $classId === (int) $class['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $class['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 align-items-center justify-content-center ps-md-2 pt-2 pt-md-0 border-top border-top-md-0 border-opacity-10 border-secondary flex-shrink-0">
                     <a href="/honors" class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center reset-btn shadow-sm" style="width: 44px; height: 44px;" title="<?= __('reset') ?>">
                        <i class="bi bi-arrow-counterclockwise fs-5 text-primary"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php if ($classId > 0): ?>
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="modern-card border-0 shadow-sm rounded-5">
                    <div class="modern-card-body p-4 p-lg-5">
                        <form id="unifiedForm" target="_blank" action="#" method="GET">
                            <input type="hidden" name="academic_year_id" value="<?= (int) $academicYearId ?>">
                            <input type="hidden" name="class_id" value="<?= $classId ?>">

                            <!-- Step 1: Type Selection -->
                            <div class="flow-step mb-5 animate-fade-in">
                                <div class="flow-step-number">1</div>
                                <h5 class="fw-bold mb-3"><?= __('select_honor_roll_type') ?></h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="w-100 option-card-wrap m-0" data-type="trimestre">
                                            <input type="radio" name="type" value="trimestre" class="d-none" required>
                                            <div class="card card-body text-center shadow-sm hover-elevate border-light transition-all rounded-4 p-4">
                                                <i class="bi bi-calendar3 fs-1 text-success mb-2"></i>
                                                <h6 class="fw-bold m-0"><?= __('term_honor_roll') ?></h6>
                                                <p class="small text-muted mb-0">Par trimestre spécifique</p>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="w-100 option-card-wrap m-0" data-type="annuel">
                                            <input type="radio" name="type" value="annuel" class="d-none">
                                            <div class="card card-body text-center shadow-sm hover-elevate border-light transition-all rounded-4 p-4">
                                                <i class="bi bi-award fs-1 text-warning mb-2"></i>
                                                <h6 class="fw-bold m-0"><?= __('annual_honor_roll') ?></h6>
                                                <p class="small text-muted mb-0">Synthèse de l'année</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Dynamic Detail (Trimestre) -->
                            <div id="dynamic-detail-section" class="flow-step mb-5 d-none">
                                <div class="flow-step-number">2</div>
                                <div id="term-select" class="animate-fade-in">
                                    <h5 class="fw-bold mb-3 text-main-theme"><?= __('trimestre') ?></h5>
                                    <select name="term" id="term_input" class="form-select form-select-lg rounded-4 shadow-sm border-0 bg-light">
                                        <option value=""><?= __('choose_term') ?></option>
                                        <?php foreach ($terms as $term): ?>
                                            <option value="<?= $term ?>"><?= __('trimesters') ?> <?= $term ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Step 3: Format Selection -->
                            <div id="format-section" class="flow-step mb-5 d-none">
                                <div class="flow-step-number" id="format-step-number">3</div>
                                <h5 class="fw-bold mb-3 text-main-theme"><?= __('output_format') ?></h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="w-100 option-card-wrap m-0">
                                            <input type="radio" name="mode" value="list" class="d-none" checked>
                                            <div class="card card-body text-center shadow-sm hover-elevate border-light transition-all rounded-4 p-4">
                                                <i class="bi bi-list-check fs-1 text-primary mb-2"></i>
                                                <h6 class="fw-bold m-0"><?= __('honor_roll_list') ?></h6>
                                                <p class="small text-muted mb-0">Format Portrait - Récapitulatif</p>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="w-100 option-card-wrap m-0">
                                            <input type="radio" name="mode" value="bulk" class="d-none">
                                            <div class="card card-body text-center shadow-sm hover-elevate border-light transition-all rounded-4 p-4">
                                                <i class="bi bi-file-earmark-medical fs-1 text-danger mb-2"></i>
                                                <h6 class="fw-bold m-0"><?= __('honor_roll_certificates') ?></h6>
                                                <p class="small text-muted mb-0">Format Paysage - Elite Edition</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end border-top pt-4">
                                <button type="submit" id="generate-btn"
                                    class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg d-inline-flex align-items-center gap-2"
                                    disabled style="transition: all 0.3s;">
                                    <span class="spinner-border spinner-border-sm d-none" id="generate-loader"></span>
                                    <i class="bi bi-stars fs-5" id="generate-icon"></i>
                                    <span id="generate-text"><?= __('complete_selections') ?></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="mb-empty-state text-center p-5 text-muted-theme mt-4">
            <div class="mb-empty-icon p-4 rounded-circle d-inline-flex mb-3 shadow-sm bg-white">
                <i class="bi bi-door-open fs-1 text-primary opacity-75"></i>
            </div>
            <h4 class="fw-bold text-main-theme"><?= __('no_class_selected') ?></h4>
            <p class="mb-0 fs-5"><?= __('select_class_to_start') ?></p>
        </div>
    <?php endif; ?>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('unifiedForm');
        if (!form) return;

        const typeRadios = document.querySelectorAll('input[name="type"]');
        const termInput = document.getElementById('term_input');
        const dynamicDetailSection = document.getElementById('dynamic-detail-section');
        const formatSection = document.getElementById('format-section');
        const formatStepNumber = document.getElementById('format-step-number');
        const generateBtn = document.getElementById('generate-btn');
        const generateText = document.getElementById('generate-text');

        function updateState() {
            let selectedType = document.querySelector('input[name="type"]:checked')?.value;
            let selectedTerm = termInput.value;

            // Step logic
            if (selectedType) {
                if (selectedType === 'annuel') {
                    dynamicDetailSection.classList.add('d-none');
                    formatSection.classList.remove('d-none');
                    formatStepNumber.textContent = '2';
                } else {
                    dynamicDetailSection.classList.remove('d-none');
                    if (selectedTerm) {
                        formatSection.classList.remove('d-none');
                        formatStepNumber.textContent = '3';
                    } else {
                        formatSection.classList.add('d-none');
                    }
                }
            }

            // Validation
            let isValid = false;
            if (selectedType) {
                if (selectedType === 'annuel') isValid = true;
                else if (selectedType === 'trimestre' && selectedTerm) isValid = true;
            }

            generateBtn.disabled = !isValid;
            if (isValid) {
                generateText.textContent = "<?= __('generate') ?>";
                
                // Update form action
                let typePath = selectedType === 'trimestre' ? 'trimestre' : 'annuel';
                let mode = document.querySelector('input[name="mode"]:checked')?.value || 'list';
                let baseUrl = '/honors/' + typePath;
                if (mode === 'bulk') baseUrl += '/bulk';
                form.action = baseUrl;
            } else {
                generateText.textContent = "<?= __('complete_selections') ?>";
            }
        }

        typeRadios.forEach(r => r.addEventListener('change', updateState));
        document.querySelectorAll('input[name="mode"]').forEach(r => r.addEventListener('change', updateState));
        termInput.addEventListener('change', updateState);

        form.addEventListener('submit', function (e) {
            const loader = document.getElementById('generate-loader');
            const icon = document.getElementById('generate-icon');
            loader.classList.remove('d-none');
            icon.classList.add('d-none');
            generateText.textContent = "<?= __('generating') ?>";
            generateBtn.classList.add('disabled', 'opacity-75');

            setTimeout(() => {
                loader.classList.add('d-none');
                icon.classList.remove('d-none');
                generateBtn.classList.remove('disabled', 'opacity-75');
                updateState();
            }, 1000);
        });
    });
</script>

<style>
    /* Global Styles matched with Bulletins */
    .filter-island {
        background: rgba(var(--bg-card-rgb), 0.7);
        backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(var(--primary-rgb), 0.15);
        border-radius: 100px;
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .filter-island {
        background: rgba(30, 30, 45, 0.6);
        border-color: rgba(255, 255, 255, 0.08);
    }

    .search-pill {
        border: 1px solid rgba(var(--primary-rgb), 0.1);
        transition: all 0.3s ease;
    }

    .search-pill:focus-within {
        background: rgba(var(--primary-rgb), 0.05) !important;
        border-color: var(--primary-color);
        box-shadow: 0 5px 15px rgba(var(--primary-rgb), 0.1);
    }

    /* Flow Steps Styling */
    .flow-step {
        position: relative;
        padding-left: 60px;
    }
    .flow-step-number {
        position: absolute;
        left: 0;
        top: 0;
        width: 40px;
        height: 40px;
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 1.1rem;
        box-shadow: 0 4px 10px rgba(var(--primary-rgb), 0.3);
    }

    .option-card-wrap input:checked + .card {
        border-color: var(--primary-color) !important;
        background: rgba(var(--primary-rgb), 0.05) !important;
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(var(--primary-rgb), 0.1) !important;
    }

    .hover-elevate {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .hover-elevate:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
    }

    .modern-card {
        background: var(--bg-card);
        border-radius: 2rem;
    }

    .animate-slide-down {
        animation: slideDown 0.6s cubic-bezier(0.23, 1, 0.32, 1);
    }

    @keyframes slideDown {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @media (max-width: 767.98px) {
        .filter-island {
            border-radius: 24px;
            padding: 1rem !important;
        }
    }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
