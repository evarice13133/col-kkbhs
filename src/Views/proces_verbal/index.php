<?php
/**
 * Vue principale du module Procès-Verbal.
 * Harmonisée avec le style des Bulletins (Bureau Flow).
 */

$title = __('pv_title');
ob_start();
?>

<div class="animate-fade-in admin-analytics module-bureau-flow">


    <!-- BARRE D'ACTIONS COMPLÈTE : Style Floating Island -->
    <div class="d-flex justify-content-center mb-5">
        <div class="filter-island px-3 py-2 shadow-lg animate-slide-down" style="min-width: 70%;">
            <form method="GET" action="/proces-verbal" class="d-flex align-items-center gap-3 flex-wrap flex-md-nowrap filter-form w-100" id="contextForm">
                
                <div class="d-flex align-items-center gap-2 flex-grow-1">
                    <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-3 py-1 flex-grow-1">
                        <span class="input-group-text border-0 bg-transparent text-primary small fw-bold text-uppercase me-2">
                            <?= __('year') ?>
                        </span>
                        <select name="academic_year_id" class="form-select border-0 bg-transparent shadow-none fw-bold text-main"
                            onchange="this.form.submit()">
                            <?php foreach ($anneesScolaires as $annee): ?>
                                <option value="<?= $annee['id'] ?>" <?= $anneeId === (int) $annee['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($annee['nom']) ?>
                                    <?= (int) $annee['is_active'] === 1 ? '(' . __('active') . ')' : '' ?>
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
                            <option value=""><?= __('choose_class') ?></option>
                            <?php foreach ($classes as $classe): ?>
                                <option value="<?= $classe['id'] ?>" <?= $classeId === (int) $classe['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($classe['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2 align-items-center border-start border-opacity-10 border-secondary ps-3">
                     <a href="/proces-verbal" class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center reset-btn" style="width: 40px; height: 40px;" title="<?= __('reset') ?>">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php if ($classeId > 0): ?>
        <div class="modern-card border-0 shadow-sm">
            <div class="modern-card-body p-4 p-lg-5">
                <?php if (!empty($isLmdClass)): ?>
                    <!-- FORMULAIRE SPÉCIFIQUE : SUPÉRIEUR LMD (Génération par Évaluation uniquement) -->
                    <form id="lmdPvForm" target="_blank" action="/proces-verbal/evaluation" method="GET">
                        <input type="hidden" name="academic_year_id" value="<?= (int) $anneeId ?>">
                        <input type="hidden" name="class_id" value="<?= (int) $classeId ?>">

                        <div class="flow-step mb-5">
                            <div class="flow-step-number">1</div>
                            <h5 class="fw-bold mb-3 text-main-theme"><?= __('select_evaluation_lmd') ?></h5>
                            <select name="sequence_id" id="lmd_evaluation_id_input" class="form-select form-select-lg rounded-4 shadow-sm" required>
                                <option value=""><?= __('choose_sequence') ?></option>
                                <?php foreach ($sequences as $seq): ?>
                                    <option value="<?= $seq['id'] ?>">
                                        <?= htmlspecialchars($seq['label']) ?> <?= !empty($seq['code']) ? '(' . htmlspecialchars($seq['code']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="text-end border-top pt-4">
                            <button type="submit" id="generate-pv-lmd-btn"
                                class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg d-inline-flex align-items-center gap-2"
                                disabled style="transition: all 0.3s;">
                                <span class="spinner-border spinner-border-sm d-none" id="pv-loader-lmd"></span>
                                <i class="bi bi-file-earmark-pdf fs-5" id="pv-icon-lmd"></i>
                                <span id="pv-text-lmd"><?= __('complete_selections') ?></span>
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <!-- FORMULAIRE SEC00 / SECONDAIRE (Séquence, Trimestre, Annuel) -->
                    <form id="unifiedPvForm" target="_blank" action="#" method="GET">
                        <input type="hidden" name="academic_year_id" value="<?= (int) $anneeId ?>">
                        <input type="hidden" name="class_id" value="<?= (int) $classeId ?>">

                        <!-- ÉTAPE 1 : Type de PV -->
                        <div class="flow-step mb-5">
                            <div class="flow-step-number">1</div>
                            <h5 class="fw-bold mb-3"><?= __('select_pv_type') ?></h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="w-100 pv-type-card m-0" data-type="sequence">
                                        <input type="radio" name="pv_type" value="sequence" class="d-none" required>
                                        <div class="card card-body text-center shadow-sm hover-elevate border-light transition-all rounded-4 cursor-pointer">
                                            <i class="bi bi-layout-text-sidebar-reverse fs-1 text-info mb-2"></i>
                                            <h6 class="fw-bold m-0"><?= __('Proces verbal de sequence') ?></h6>
                                            <small class="text-muted extra-small d-block mt-1"><?= __('pv_type_seq_hint') ?></small>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <label class="w-100 pv-type-card m-0" data-type="trimestre">
                                        <input type="radio" name="pv_type" value="trimestre" class="d-none">
                                        <div class="card card-body text-center shadow-sm hover-elevate border-light transition-all rounded-4 cursor-pointer">
                                            <i class="bi bi-calendar3 fs-1 text-success mb-2"></i>
                                            <h6 class="fw-bold m-0"><?= __('Proces verbal de trimestre') ?></h6>
                                            <small class="text-muted extra-small d-block mt-1"><?= __('pv_type_trim_hint') ?></small>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <label class="w-100 pv-type-card m-0" data-type="annuel">
                                        <input type="radio" name="pv_type" value="annuel" class="d-none">
                                        <div class="card card-body text-center shadow-sm hover-elevate border-light transition-all rounded-4 cursor-pointer">
                                            <i class="bi bi-award fs-1 text-warning mb-2"></i>
                                            <h6 class="fw-bold m-0"><?= __('Proces verbal annuel') ?></h6>
                                            <small class="text-muted extra-small d-block mt-1"><?= __('pv_type_ann_hint') ?></small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- ÉTAPE 2 : Période (si nécessaire) -->
                        <div id="dynamic-period-section" class="flow-step mb-5 d-none">
                            <div class="flow-step-number">2</div>
                            
                            <div id="sequence-select" class="d-none animate-fade-in">
                                <h5 class="fw-bold mb-3 text-main-theme"><?= __('sequence') ?></h5>
                                <select name="sequence_id" id="sequence_id_input" class="form-select form-select-lg rounded-4 shadow-sm">
                                    <option value=""><?= __('choose_sequence') ?></option>
                                    <?php foreach ($sequences as $seq): ?>
                                        <option value="<?= $seq['id'] ?>"><?= htmlspecialchars($seq['label']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div id="term-select" class="d-none animate-fade-in">
                                <h5 class="fw-bold mb-3 text-main-theme"><?= __('trimestre') ?></h5>
                                <select name="term" id="term_input" class="form-select form-select-lg rounded-4 shadow-sm">
                                    <option value=""><?= __('choose_term') ?></option>
                                    <?php foreach ($trimestres as $t): ?>
                                        <option value="<?= $t ?>"><?= __('Trimesters') ?> <?= $t ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- ÉTAPE FINALE : Bouton de génération -->
                        <div class="text-end border-top pt-4">
                            <button type="submit" id="generate-pv-btn"
                                class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg d-inline-flex align-items-center gap-2"
                                disabled style="transition: all 0.3s;">
                                <span class="spinner-border spinner-border-sm d-none" id="pv-loader"></span>
                                <i class="bi bi-file-earmark-pdf fs-5" id="pv-icon"></i>
                                <span id="pv-text"><?= __('complete_selections') ?></span>
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- ÉTAT VIDE -->
        <div class="mb-empty-state text-center p-5 text-muted-theme mt-4">
            <div class="mb-empty-icon p-4 rounded-circle d-inline-flex mb-3 shadow-sm">
                <i class="bi bi-clipboard-data fs-1 text-primary opacity-75"></i>
            </div>
            <h4 class="fw-bold text-main-theme"><?= __('no_class_selected') ?></h4>
            <p class="mb-0 fs-5"><?= __('select_class_to_start') ?></p>
        </div>
    <?php endif; ?>
</div>

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

    .cursor-pointer { cursor: pointer; }
    .pv-type-card input:checked + .card {
        border-color: var(--primary-color) !important;
        background-color: rgba(var(--primary-rgb), 0.05) !important;
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Formulaire Supérieur LMD
        const lmdForm = document.getElementById('lmdPvForm');
        if (lmdForm) {
            const lmdEvalInput = document.getElementById('lmd_evaluation_id_input');
            const generateLmdBtn = document.getElementById('generate-pv-lmd-btn');
            const generateLmdText = document.getElementById('pv-text-lmd');

            lmdEvalInput.addEventListener('change', function() {
                const isValid = !!this.value;
                generateLmdBtn.disabled = !isValid;
                generateLmdText.textContent = isValid ? "<?= __('pv_generate_btn') ?>" : "<?= __('complete_selections') ?>";
            });

            lmdForm.addEventListener('submit', function (e) {
                const loader = document.getElementById('pv-loader-lmd');
                const icon = document.getElementById('pv-icon-lmd');
                loader.classList.remove('d-none');
                icon.classList.add('d-none');
                generateLmdBtn.classList.add('disabled', 'opacity-75');

                setTimeout(() => {
                    loader.classList.add('d-none');
                    icon.classList.remove('d-none');
                    generateLmdBtn.classList.remove('disabled', 'opacity-75');
                }, 2000);
            });
        }

        // Formulaire SEC00 / Secondaire
        const form = document.getElementById('unifiedPvForm');
        if (!form) return;

        const typeRadios = document.querySelectorAll('input[name="pv_type"]');
        const periodSection = document.getElementById('dynamic-period-section');
        const sequenceSelect = document.getElementById('sequence-select');
        const termSelect = document.getElementById('term-select');
        const seqInput = document.getElementById('sequence_id_input');
        const termInput = document.getElementById('term_input');
        const generateBtn = document.getElementById('generate-pv-btn');
        const generateText = document.getElementById('pv-text');

        function updatePvState() {
            let selectedType = document.querySelector('input[name="pv_type"]:checked')?.value;
            
            // Masquer tout par défaut
            periodSection.classList.add('d-none');
            sequenceSelect.classList.add('d-none');
            termSelect.classList.add('d-none');
            
            let isValid = false;

            if (selectedType) {
                if (selectedType === 'annuel') {
                    isValid = true;
                    form.action = '/proces-verbal/annuel';
                } else {
                    periodSection.classList.remove('d-none');
                    if (selectedType === 'sequence') {
                        sequenceSelect.classList.remove('d-none');
                        form.action = '/proces-verbal/sequence';
                        if (seqInput.value) isValid = true;
                    } else if (selectedType === 'trimestre') {
                        termSelect.classList.remove('d-none');
                        form.action = '/proces-verbal/trimestre';
                        if (termInput.value) isValid = true;
                    }
                }
            }

            generateBtn.disabled = !isValid;
            generateText.textContent = isValid ? "<?= __('pv_generate_btn') ?>" : "<?= __('complete_selections') ?>";
        }

        typeRadios.forEach(r => r.addEventListener('change', updatePvState));
        seqInput.addEventListener('change', updatePvState);
        termInput.addEventListener('change', updatePvState);

        form.addEventListener('submit', function (e) {
            const loader = document.getElementById('pv-loader');
            const icon = document.getElementById('pv-icon');
            loader.classList.remove('d-none');
            icon.classList.add('d-none');
            generateBtn.classList.add('disabled', 'opacity-75');

            setTimeout(() => {
                loader.classList.add('d-none');
                icon.classList.remove('d-none');
                generateBtn.classList.remove('disabled', 'opacity-75');
            }, 2000);
        });
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>