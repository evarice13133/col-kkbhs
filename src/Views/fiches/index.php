<?php
$title = __('fiche_title');
ob_start();
?>

<div class="animate-fade-in admin-analytics module-bureau-flow">


    <!-- SÉLECTION DU CONTEXTE GLOBAL (FILTRES) -->
    <!-- BARRE D'ACTIONS COMPLÈTE : Style Floating Island -->
    <div class="d-flex justify-content-center mb-5">
        <div class="filter-island px-3 py-2 shadow-lg animate-slide-down" style="min-width: 70%;">
            <form method="GET" action="/fiches" class="d-flex align-items-center gap-3 flex-wrap flex-md-nowrap filter-form w-100">
                
                <div class="d-flex align-items-center gap-2 flex-grow-1">
                    <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-3 py-1 flex-grow-1">
                        <span class="input-group-text border-0 bg-transparent text-primary small fw-bold text-uppercase me-2">
                            <?= __('year') ?>
                        </span>
                        <select name="academic_year_id" class="form-select border-0 bg-transparent shadow-none fw-bold text-main"
                            onchange="this.form.submit()">
                            <?php foreach ($academicYears as $year): ?>
                                <option value="<?= $year['id'] ?>" <?= $academicYearId === (int) $year['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $year['nom']) ?> <?= (int) $year['is_active'] === 1 ? '('.__('active').')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-3 py-1 flex-grow-1">
                        <span class="input-group-text border-0 bg-transparent text-primary small fw-bold text-uppercase me-2">
                            <?= __('class') ?>
                        </span>
                        <select name="class_id" class="form-select border-0 bg-transparent shadow-none fw-bold text-main"
                            onchange="this.form.submit()">
                            <option value=""><?= __('all_classes') ?></option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= $class['id'] ?>" <?= $classId === (int) $class['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $class['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2 align-items-center border-start border-opacity-10 border-secondary ps-3">
                     <a href="/fiches" class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center reset-btn" style="width: 40px; height: 40px;" title="<?= __('reset') ?>">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php if ($classId > 0): ?>
        <div class="modern-card border-0 shadow-sm">
            <div class="modern-card-body p-4 p-lg-5">
                <form id="unifiedForm" target="_blank" action="#" method="GET">
                    <input type="hidden" name="academic_year_id" value="<?= (int) $academicYearId ?>">
                    <input type="hidden" name="class_id" value="<?= $classId ?>">

                    <div class="flow-step mb-5">
                        <div class="flow-step-number">1</div>
                        <h5 class="fw-bold mb-3 text-main-theme"><?= __('fiche_type_selection') ?></h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="w-100 bulletin-type-card m-0" data-type="sequence">
                                    <input type="radio" name="bulletin_type" value="sequence" class="d-none" required>
                                    <div class="card card-body text-center shadow-sm hover-elevate border-light transition-all rounded-4">
                                        <i class="bi bi-layout-text-sidebar-reverse fs-1 text-info mb-2"></i>
                                        <h6 class="fw-bold m-0"><?= __('sequence') ?></h6>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label class="w-100 bulletin-type-card m-0" data-type="trimestre">
                                    <input type="radio" name="bulletin_type" value="trimestre" class="d-none">
                                    <div class="card card-body text-center shadow-sm hover-elevate border-light transition-all rounded-4">
                                        <i class="bi bi-calendar3 fs-1 text-success mb-2"></i>
                                        <h6 class="fw-bold m-0"><?= __('trimestre') ?></h6>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label class="w-100 bulletin-type-card m-0" data-type="annuel">
                                    <input type="radio" name="bulletin_type" value="annuel" class="d-none">
                                    <div class="card card-body text-center shadow-sm hover-elevate border-light transition-all rounded-4">
                                        <i class="bi bi-award fs-1 text-warning mb-2"></i>
                                        <h6 class="fw-bold m-0"><?= __('annual') ?></h6>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="dynamic-detail-section" class="flow-step mb-5 d-none">
                        <div class="flow-step-number">2</div>
                        <div id="sequence-select" class="d-none animate-fade-in">
                            <h5 class="fw-bold mb-3 text-main-theme"><?= __('sequence') ?></h5>
                            <select name="sequence_id" id="sequence_id_input" class="form-select form-select-lg rounded-4 shadow-sm">
                                <option value=""><?= __('choose_sequence') ?></option>
                                <?php foreach ($sequences as $sequence): ?>
                                    <option value="<?= $sequence['id'] ?>"><?= htmlspecialchars((string) $sequence['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="term-select" class="d-none animate-fade-in">
                            <h5 class="fw-bold mb-3 text-main-theme"><?= __('trimestre') ?></h5>
                            <select name="term" id="term_input" class="form-select form-select-lg rounded-4 shadow-sm">
                                <option value=""><?= __('choose_term') ?></option>
                                <?php foreach ($terms as $term): ?>
                                    <option value="<?= $term ?>"><?= __('trimesters') ?> <?= $term ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div id="target-section" class="flow-step mb-5 d-none">
                        <div class="flow-step-number" id="target-step-number">3</div>
                        <h5 class="fw-bold mb-3 text-main-theme"><?= __('audience') ?></h5>
                        <div class="d-flex gap-2 flex-wrap mb-4">
                            <div class="form-check p-0 m-0 flex-grow-1" style="min-width: 200px;">
                                <input type="radio" class="btn-check" name="target_type" id="target_student" value="student" required>
                                <label class="btn btn-outline-primary w-100 p-3 rounded-4 fw-bold shadow-sm text-center transition-all" for="target_student">
                                    <i class="bi bi-person me-2 fs-5"></i><?= __('one_student') ?>
                                </label>
                            </div>
                            <div class="form-check p-0 m-0 flex-grow-1" style="min-width: 200px;">
                                <input type="radio" class="btn-check" name="target_type" id="target_class" value="class">
                                <label class="btn btn-outline-primary w-100 p-3 rounded-4 fw-bold shadow-sm text-center transition-all" for="target_class">
                                    <i class="bi bi-people me-2 fs-5"></i><?= __('entire_class') ?>
                                    <span class="badge bg-primary ms-2 rounded-pill"><?= count($students) ?></span>
                                </label>
                            </div>
                        </div>

                        <div id="student-select" class="d-none animate-fade-in shadow-sm p-3 border rounded-4 bg-light">
                            <select name="student_id" id="student_id_input" class="form-select form-select-lg rounded-4 shadow-sm">
                                <option value=""><?= __('choose_student') ?></option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= $student['id'] ?>"><?= htmlspecialchars((string) $student['nom']) ?> <?= htmlspecialchars((string) $student['prenom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="text-end border-top pt-4">
                        <button type="submit" id="generate-btn" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg d-inline-flex align-items-center gap-2" disabled style="transition: all 0.3s;">
                            <span class="spinner-border spinner-border-sm d-none" id="generate-loader"></span>
                            <i class="bi bi-magic fs-5" id="generate-icon"></i>
                            <span id="generate-text"><?= __('fiche_generate_btn') ?></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="mb-empty-state text-center p-5 text-muted-theme mt-4">
            <div class="mb-empty-icon p-4 rounded-circle d-inline-flex mb-3 shadow-sm">
                <i class="bi bi-door-open fs-1 text-primary opacity-75"></i>
            </div>
            <h4 class="fw-bold text-main-theme"><?= __('no_class_selected') ?></h4>
            <p class="mb-0 fs-5"><?= __('select_class_to_start') ?></p>
        </div>
    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('unifiedForm');
    if (!form) return;

    const typeRadios = document.querySelectorAll('input[name="bulletin_type"]');
    const dynamicDetailSection = document.getElementById('dynamic-detail-section');
    const sequenceSelectDiv = document.getElementById('sequence-select');
    const termSelectDiv = document.getElementById('term-select');
    const seqInput = document.getElementById('sequence_id_input');
    const termInput = document.getElementById('term_input');
    
    const targetSection = document.getElementById('target-section');
    const targetRadios = document.querySelectorAll('input[name="target_type"]');
    const studentSelectDiv = document.getElementById('student-select');
    const studentInput = document.getElementById('student_id_input');
    
    const targetStepNumber = document.getElementById('target-step-number');
    const generateBtn = document.getElementById('generate-btn');
    const generateText = document.getElementById('generate-text');

    function updateState() {
        let selectedType = document.querySelector('input[name="bulletin_type"]:checked')?.value;
        let selectedTarget = document.querySelector('input[name="target_type"]:checked')?.value;

        if (selectedType) {
            targetSection.classList.remove('d-none');
            
            if (selectedType === 'annuel') {
                dynamicDetailSection.classList.add('d-none');
                targetStepNumber.textContent = '2';
            } else {
                dynamicDetailSection.classList.remove('d-none');
                targetStepNumber.textContent = '3';
                sequenceSelectDiv.classList.add('d-none');
                termSelectDiv.classList.add('d-none');
                
                if (selectedType === 'sequence') {
                    sequenceSelectDiv.classList.remove('d-none');
                } else if (selectedType === 'trimestre') {
                    termSelectDiv.classList.remove('d-none');
                }
            }
        }

        if (selectedTarget === 'student') {
            studentSelectDiv.classList.remove('d-none');
            studentInput.required = true;
        } else {
            studentSelectDiv.classList.add('d-none');
            studentInput.required = false;
        }

        let isValid = false;
        if (selectedType && selectedTarget) {
            isValid = true;
            if (selectedType === 'sequence' && !seqInput.value) isValid = false;
            if (selectedType === 'trimestre' && !termInput.value) isValid = false;
            if (selectedTarget === 'student' && !studentInput.value) isValid = false;
        }
        
        generateBtn.disabled = !isValid;
        
        if (isValid) {
            generateBtn.classList.replace('btn-secondary', 'btn-primary');
            generateText.textContent = "<?= __('fiche_generate_btn') ?>";
        } else {
            generateText.textContent = "<?= __('complete_selections') ?>";
        }
        
        if (isValid) {
            let baseUrl = '/fiches/';
            let typePath = selectedType === 'trimestre' ? 'trimestre' : selectedType;
            let targetPath = selectedTarget === 'class' ? '/class' : '';
            form.action = baseUrl + typePath + targetPath;
            
            seqInput.disabled = selectedType !== 'sequence';
            termInput.disabled = selectedType !== 'trimestre';
            studentInput.disabled = selectedTarget !== 'student';
        }
    }

    typeRadios.forEach(r => r.addEventListener('change', updateState));
    targetRadios.forEach(r => r.addEventListener('change', updateState));
    seqInput.addEventListener('change', updateState);
    termInput.addEventListener('change', updateState);
    studentInput.addEventListener('change', updateState);

    form.addEventListener('submit', function(e) {
        const loader = document.getElementById('generate-loader');
        const icon = document.getElementById('generate-icon');
        loader.classList.remove('d-none');
        icon.classList.add('d-none');
        generateBtn.classList.add('disabled', 'opacity-75');

        setTimeout(() => {
            loader.classList.add('d-none');
            icon.classList.remove('d-none');
            generateBtn.classList.remove('disabled', 'opacity-75');
            updateState();
        }, 1500);
    });
});
</script>

<style>
    /* Floating Island Filters */
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

    /* Animations */
    .animate-slide-down {
        animation: slideDown 0.6s cubic-bezier(0.23, 1, 0.32, 1);
    }

    @keyframes slideDown {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .bulletin-type-card input:checked + .card {
        border-color: var(--primary-color) !important;
        background: rgba(var(--primary-rgb), 0.05) !important;
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(var(--primary-rgb), 0.1) !important;
    }

    .hover-elevate:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
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
