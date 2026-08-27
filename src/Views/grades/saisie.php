<?php
$title = __('grade_entry_title');
ob_start();

$entrySubtitle = __('grade_entry_subtitle', [
    'class' => (string) $classInfo['nom'],
    'subject' => (string) $subjectInfo['nom'],
]);
$appreciationLabels = [
    'excellent' => __('grade_appreciation_excellent'),
    'veryGood' => __('grade_appreciation_very_good'),
    'good' => __('grade_appreciation_good'),
    'fairlyGood' => __('grade_appreciation_fairly_good'),
    'passable' => __('grade_appreciation_passable'),
    'insufficient' => __('grade_appreciation_insufficient'),
    'veryInsufficient' => __('grade_appreciation_very_insufficient'),
];
?>

<style>
    :root {
        --grade-primary: #4361ee;
        --grade-success: #4cc9f0;
        --grade-warning: #f72585;
        --grade-bg: #f8f9fa;
        --glass-bg: rgba(255, 255, 255, 0.9);
        --transition-fast: all 0.2s ease;
    }

    .grade-saisie-page {
        padding-bottom: 100px;
    }

    /* Info Bar (Canva Workspace Style) */
    .info-header-card {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #9333ea 100%);
        color: white;
        border-radius: 24px;
        padding: 28px 32px;
        margin-bottom: 24px;
        box-shadow: 0 16px 40px rgba(124, 58, 237, 0.25);
        position: relative;
        overflow: hidden;
    }

    .info-header-card::after {
        content: "";
        position: absolute;
        top: -40%;
        right: -10%;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.12);
        filter: blur(40px);
        pointer-events: none;
    }

    /* Sticky Toolbar (MS 365 Command Bar) */
    .sticky-grade-toolbar {
        position: sticky;
        top: 15px;
        z-index: 100;
        background: color-mix(in srgb, var(--bg-card) 92%, var(--primary-color));
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border-radius: 18px;
        padding: 12px 20px;
        margin-bottom: 24px;
        border: 1px solid rgba(124, 58, 237, 0.18);
        box-shadow: 0 12px 35px rgba(124, 58, 237, 0.12);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    [data-theme="dark"] .sticky-grade-toolbar {
        background: rgba(15, 23, 42, 0.88);
        border-color: rgba(255, 255, 255, 0.14);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.4);
    }

    .eval-select-pill {
        border-radius: 12px !important;
        border: 1px solid rgba(124, 58, 237, 0.25) !important;
        background: var(--bg-card) !important;
        color: var(--text-main) !important;
        font-weight: 700 !important;
        padding: 0.5rem 1rem !important;
        transition: all 0.2s ease !important;
    }

    .eval-select-pill:focus {
        border-color: #7c3aed !important;
        box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.2) !important;
    }

    /* Grade & Appreciation Input Styling (Canva & MS 365 Premium Style) */
    .input-grade-modern {
        width: 105px !important;
        height: 46px !important;
        border-radius: 14px !important;
        border: 2px solid var(--border-color) !important;
        background: color-mix(in srgb, var(--bg-card) 95%, var(--primary-color)) !important;
        color: var(--text-main) !important;
        font-weight: 800 !important;
        font-size: 1.15rem !important;
        text-align: center !important;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.03) !important;
        letter-spacing: 0.02em;
    }

    .input-grade-modern::placeholder {
        color: var(--text-muted) !important;
        opacity: 0.45 !important;
        font-weight: 600 !important;
        font-size: 1.05rem !important;
    }

    [data-theme="dark"] .input-grade-modern {
        background: rgba(15, 23, 42, 0.7) !important;
        border-color: rgba(255, 255, 255, 0.15) !important;
    }

    .input-grade-modern:hover {
        border-color: rgba(124, 58, 237, 0.4) !important;
        background: var(--bg-card) !important;
        transform: translateY(-1px);
    }

    .input-grade-modern:focus {
        border-color: #7c3aed !important;
        background: var(--bg-card) !important;
        box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.22), 0 4px 12px rgba(124, 58, 237, 0.15) !important;
        transform: scale(1.06);
        outline: none !important;
    }

    /* Live Feedback Colors for High / Low Grades */
    .input-grade-modern.note-high {
        border-color: #10b981 !important;
        background: rgba(16, 185, 129, 0.08) !important;
        color: #059669 !important;
    }

    [data-theme="dark"] .input-grade-modern.note-high {
        background: rgba(16, 185, 129, 0.18) !important;
        color: #34d399 !important;
        border-color: rgba(52, 211, 153, 0.4) !important;
    }

    .input-grade-modern.note-low {
        border-color: #f43f5e !important;
        background: rgba(244, 63, 94, 0.08) !important;
        color: #e11d48 !important;
    }

    [data-theme="dark"] .input-grade-modern.note-low {
        background: rgba(244, 63, 94, 0.18) !important;
        color: #fb7185 !important;
        border-color: rgba(251, 113, 133, 0.4) !important;
    }

    /* Appreciation Input Field */
    .input-appr-modern {
        height: 46px !important;
        border-radius: 14px !important;
        border: 1.5px solid var(--border-color) !important;
        background: color-mix(in srgb, var(--bg-card) 98%, var(--primary-color)) !important;
        color: var(--text-main) !important;
        font-weight: 600 !important;
        font-size: 0.95rem !important;
        padding: 0 1rem !important;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }

    .input-appr-modern:focus {
        border-color: #7c3aed !important;
        background: var(--bg-card) !important;
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15) !important;
        outline: none !important;
    }

    /* Floating Save Button (Canva CTA) */
    .floating-save {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 1000;
        background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);
        border: none;
        color: #ffffff;
        box-shadow: 0 14px 35px rgba(124, 58, 237, 0.4);
        padding: 14px 28px;
        border-radius: 50px;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .floating-save:hover {
        transform: translateY(-3px) scale(1.03);
        box-shadow: 0 18px 40px rgba(124, 58, 237, 0.5);
    }

    .workflow-step {
        opacity: 0.7;
    }

    .workflow-step.active {
        color: #1d4ed8;
        opacity: 1;
        font-weight: 600;
    }

    .workflow-step.done {
        color: #047857;
        opacity: 1;
        font-weight: 600;
    }

    .workflow-separator {
        opacity: 0.5;
    }

    @media (max-width: 767.98px) {
        .info-header-card {
            padding: 1.25rem 1rem !important;
            border-radius: 18px !important;
            margin-bottom: 1rem !important;
        }

        .info-header-card .d-flex {
            flex-direction: column !important;
            gap: 0.75rem !important;
        }

        .sticky-grade-toolbar {
            padding: 0.75rem 0.85rem !important;
            border-radius: 14px !important;
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 0.75rem !important;
            top: 65px !important;
        }

        .sticky-grade-toolbar .eval-select-group {
            width: 100% !important;
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 0.35rem !important;
        }

        .sticky-grade-toolbar select {
            width: 100% !important;
            min-width: 100% !important;
            min-height: 44px !important;
        }

        .floating-save {
            bottom: 15px !important;
            right: 15px !important;
            left: 15px !important;
            width: calc(100% - 30px) !important;
            border-radius: 16px !important;
            text-align: center !important;
            justify-content: center !important;
            min-height: 48px !important;
            display: flex !important;
            align-items: center !important;
        }
    }
</style>

<div class="grade-saisie-page fade-in">
    
    <!-- Header Info (Canva Workspace Banner) -->
    <div class="info-header-card">
        <div class="d-flex justify-content-between align-items-start gap-3 position-relative" style="z-index: 2;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge rounded-pill px-3 py-1.5 extra-small fw-extrabold text-uppercase tracking-wider shadow-sm" style="background: rgba(255, 255, 255, 0.25) !important; color: #ffffff !important; backdrop-filter: blur(8px);">
                        <i class="bi bi-mortarboard-fill me-1"></i><?= __('class') ?>
                    </span>
                </div>
                <h2 class="fw-black mb-2 fs-3" style="color: #ffffff !important;"><?= htmlspecialchars((string) $classInfo['nom']) ?></h2>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="badge rounded-pill px-3 py-2 fw-bold shadow-sm" style="background: #ffffff !important; color: #4f46e5 !important;">
                        <i class="bi bi-book-half me-1"></i><?= htmlspecialchars((string) $subjectInfo['nom']) ?>
                    </span>
                    <span class="badge rounded-pill px-3 py-2 fw-bold shadow-sm" style="background: rgba(255, 255, 255, 0.25) !important; color: #ffffff !important; backdrop-filter: blur(8px);">
                        <i class="bi bi-star-fill text-warning me-1"></i><?= __('coef') ?> <?= (int) $subjectInfo['coefficient'] ?>
                    </span>
                </div>
            </div>
            <a href="/notes" class="btn rounded-pill px-3 py-2 fw-bold align-self-end align-self-md-start scale-on-hover shadow-sm" style="background: rgba(255, 255, 255, 0.2) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.4) !important;">
                <i class="bi bi-x-lg me-1"></i><?= __('cancel') ?>
            </a>
        </div>
    </div>

    <!-- Alert Messages (Globally handled by AlertService in layout) -->

    <form action="/notes/store" method="POST" id="gradeEntryForm" class="no-loader">
        <input type="hidden" name="class_id" value="<?= $class_id ?>">
        <input type="hidden" name="subject_id" value="<?= $subject_id ?>">
        <input type="hidden" name="periode" value="<?= htmlspecialchars((string) $periode) ?>">

        <!-- Sticky Command Bar (MS 365 Style) -->
        <div class="sticky-grade-toolbar">
            <div class="d-flex align-items-center gap-2 gap-md-3 eval-select-group flex-grow-1">
                <span class="badge bg-primary-subtle text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-calendar-event fs-6"></i>
                </span>
                <label class="fw-bold text-muted-theme small text-uppercase flex-shrink-0"><?= __('evaluation') ?></label>
                <select class="form-select eval-select-pill shadow-none flex-grow-1" style="min-height: 44px;" 
                        onchange="window.location.href='/notes/saisie?class_id=<?= $class_id ?>&subject_id=<?= $subject_id ?>&periode=' + encodeURIComponent(this.value)">
                    <?php foreach ($periodes as $p): ?>
                        <option value="<?= htmlspecialchars((string) $p) ?>" <?= $periode === $p ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) $p) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-2">
                <a href="/notes/export?mode=report&format=pdf&class_id=<?= $class_id ?>&subject_id=<?= $subject_id ?>" target="_blank"
                   class="btn btn-light-theme text-danger rounded-pill px-3 py-2 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm border border-danger-subtle w-100 w-md-auto scale-on-hover" style="min-height: 44px;">
                    <i class="bi bi-file-earmark-pdf-fill fs-6 text-danger"></i>
                    <span class="d-inline"><?= __('grade_report_pdf') ?></span>
                </a>
            </div>
        </div>

        <div class="small text-muted-theme mb-3">
            <span id="gradeWorkflowSummary" class="fw-bold text-primary">1/4</span>
            <span class="ms-2">
                <span class="workflow-step step-evaluation" data-step="evaluation">Évaluation</span>
                <span class="workflow-separator mx-1">→</span>
                <span class="workflow-step step-competency" data-step="competency">Compétence</span>
                <span class="workflow-separator mx-1">→</span>
                <span class="workflow-step step-notes" data-step="notes">Notes</span>
                <span class="workflow-separator mx-1">→</span>
                <span class="workflow-step step-save" data-step="save">Enregistrement</span>
            </span>
        </div>

        <!-- Competency Selection Section -->
        <div class="modern-card border-0 shadow-sm rounded-4 mb-4 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold m-0 text-info d-flex align-items-center gap-2 fs-6">
                    <i class="bi bi-list-check"></i> <?= __('evaluation_competencies') ?? 'Compétences de l\'évaluation' ?>
                </h6>
                <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3 py-1 extra-small fw-bold">
                    <?= __('max_2_competencies') ?? 'Max 2 compétences' ?>
                </span>
            </div>
            
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">
                        <?= __('competency_1') ?? 'Compétence 1' ?>
                    </label>
                    <div class="d-flex gap-2">
                        <select id="competency1Select" name="competency_ids[]" class="form-select premium-input flex-grow-1">
                            <option value=""><?= __('select_competency') ?? 'Sélectionner une compétence' ?></option>
                        </select>
                        <button type="button" class="btn btn-outline-info rounded-circle p-2" onclick="openCompetencyModal(1)" title="<?= __('create_new_competency') ?? 'Créer une nouvelle compétence' ?>">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-5">
                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">
                        <?= __('competency_2') ?? 'Compétence 2 (optionnelle)' ?>
                    </label>
                    <div class="d-flex gap-2">
                        <select id="competency2Select" name="competency_ids[]" class="form-select premium-input flex-grow-1">
                            <option value=""><?= __('select_competency_optional') ?? 'Aucune (optionnel)' ?></option>
                        </select>
                        <button type="button" class="btn btn-outline-info rounded-circle p-2" onclick="openCompetencyModal(2)" title="<?= __('create_new_competency') ?? 'Créer une nouvelle compétence' ?>">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" id="saveCompetenciesBtn" class="btn btn-info rounded-pill px-4 py-2 fw-bold shadow-sm w-100">
                        <i class="bi bi-check-lg me-2"></i><?= __('save_competencies') ?? 'Enregistrer' ?>
                    </button>
                </div>
            </div>
            
            <div id="competencyStatus" class="form-text extra-small text-muted mt-2">
                <i class="bi bi-info-circle me-1"></i>
                <?= __('competency_selection_info') ?? 'Sélectionnez les compétences évaluées pour cette évaluation. Vous pouvez en créer de nouvelles si nécessaire.' ?>
            </div>
        </div>

        <div class="registry-card shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table align-middle mb-0 registry-table">
                    <thead>
                        <tr>
                            <th class="ps-4 py-3" style="width: 40%;"><?= __('student') ?></th>
                            <th class="text-center py-3" style="width: 15%;"><?= __('grade_out_of_20') ?></th>
                            <th class="text-start py-3"><?= __('observation') ?> / <?= __('appreciation') ?></th>
                        </tr>
                    </thead>
                    <tbody class="bg-transparent">
                        <?php foreach ($students as $st): ?>
                            <tr class="student-row transition-base bg-transparent border-bottom border-light border-opacity-10" id="row-<?= $st['student_id'] ?>">
                                <td class="ps-4 py-3 bg-transparent">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="registry-icon">
                                            <?= strtoupper(substr($st['nom'], 0, 1) . substr($st['prenom'], 0, 1)) ?>
                                        </div>
                                        <div class="registry-text-main text-uppercase text-muted"><?= htmlspecialchars((string) $st['nom']) ?> <?= htmlspecialchars((string) $st['prenom']) ?></div>
                                    </div>
                                </td>
                                <td class="text-center bg-transparent">
                                    <input type="number" step="0.25" min="0" max="20" name="notes[<?= $st['student_id'] ?>]"
                                        class="input-grade-modern js-note-input" 
                                        data-student-id="<?= $st['student_id'] ?>"
                                        value="<?= isset($st['valeur']) ? htmlspecialchars((string) $st['valeur']) : '' ?>"
                                        placeholder="12.5">
                                </td>
                                <td class="pe-4 bg-transparent">
                                    <input type="text" name="appreciations[<?= $st['student_id'] ?>]"
                                        class="form-control border-0 input-appr-modern js-appreciation-input w-100"
                                        data-student-id="<?= $st['student_id'] ?>"
                                        value="<?= htmlspecialchars((string) ($st['appreciation'] ?? '')) ?>"
                                        placeholder="<?= __('grade_appreciation_placeholder') ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-5 bg-transparent">
                                    <i class="bi bi-inbox fs-1 opacity-25 d-block mb-2"></i>
                                    <span class="opacity-50 fw-bold"><?= __('student_none_in_class') ?></span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (!empty($students)): ?>
            <button type="button" class="btn btn-primary floating-save" onclick="confirmGradeSubmission()">
                <i class="bi bi-check2-circle me-2"></i><?= __('save_grades') ?>
            </button>
        <?php endif; ?>
    </form>
</div>

<div class="modal fade" id="newCompetencyModal" tabindex="-1" aria-labelledby="newCompetencyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form id="newCompetencyForm" class="no-loader" data-manual-submit="true">
                <input type="hidden" id="competencySlotInput" value="1">
                <div class="modal-header border-0 pb-2" style="background: linear-gradient(135deg, rgba(67,97,238,0.08), rgba(124,58,237,0.08));">
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="newCompetencyModalLabel"><?= __('create_new_competency') ?? 'Créer une nouvelle compétence' ?></h5>
                        <small class="text-muted">
                            <?= __('subject') ?? 'Matière' ?>: <?= htmlspecialchars((string) $subjectInfo['nom']) ?>
                        </small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label for="newCompetencyName" class="form-label fw-bold small text-uppercase text-muted-theme">
                            <?= __('competency_name') ?? 'Nom de la compétence' ?>
                        </label>
                        <input type="text" id="newCompetencyName" class="form-control premium-input" required maxlength="255" placeholder="Ex. : Comprendre les fractions">
                    </div>
                    <div class="mb-0">
                        <label for="newCompetencyDescription" class="form-label fw-bold small text-uppercase text-muted-theme">
                            <?= __('description') ?? 'Description' ?>
                        </label>
                        <textarea id="newCompetencyDescription" class="form-control premium-input" rows="3" placeholder="Description optionnelle..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-2">
                    <button type="button" class="btn btn-light-theme rounded-pill px-3" data-bs-dismiss="modal"><?= __('cancel') ?? 'Annuler' ?></button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="bi bi-plus-lg me-2"></i><?= __('create') ?? 'Créer' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const appreciationLabels = <?= json_encode($appreciationLabels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

        // Competency Management for Grade Entry
        const classId = <?= $class_id ?>;
        const subjectId = <?= $subject_id ?>;
        const periode = '<?= htmlspecialchars((string) $periode) ?>';
        
        const competency1Select = document.getElementById('competency1Select');
        const competency2Select = document.getElementById('competency2Select');
        const saveCompetenciesBtn = document.getElementById('saveCompetenciesBtn');
        const competencyStatus = document.getElementById('competencyStatus');

        // Load competencies for the subject
        function loadSubjectCompetencies() {
            const selectedCompetency1 = competency1Select ? competency1Select.value : '';
            const selectedCompetency2 = competency2Select ? competency2Select.value : '';

            return fetch(`/competencies/api/by-subject?subject_id=${subjectId}&class_id=${classId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && Array.isArray(data.competencies)) {
                        [competency1Select, competency2Select].forEach(select => {
                            if (!select) return;

                            const placeholder = select.id === 'competency2Select'
                                ? <?= json_encode(__('select_competency_optional') ?? 'Aucune (optionnel)') ?>
                                : <?= json_encode(__('select_competency') ?? 'Sélectionner une compétence') ?>;

                            const currentValue = select.value;
                            const nextOptions = ['<option value="">' + placeholder + '</option>'];

                            data.competencies.forEach(comp => {
                                const option = document.createElement('option');
                                option.value = String(comp.id);
                                option.textContent = comp.libelle;
                                nextOptions.push('<option value="' + String(comp.id) + '">' + (comp.libelle || '') + '</option>');
                            });

                            select.innerHTML = nextOptions.join('');

                            const preferredValue = currentValue || (select.id === 'competency1Select' ? selectedCompetency1 : selectedCompetency2);
                            if (preferredValue && Array.from(select.options).some(option => option.value === preferredValue)) {
                                select.value = preferredValue;
                            }
                        });

                        return loadEvaluationCompetencies();
                    }

                    throw new Error(data.error || <?= json_encode(__('error_loading_competencies') ?? 'Erreur lors du chargement des compétences') ?>);
                })
                .catch(error => {
                    console.error('Error loading competencies:', error);
                    return null;
                });
        }

        // Load competencies already associated with this evaluation
        function loadEvaluationCompetencies() {
            return fetch(`/competencies/api/get-evaluation-competencies?class_id=${classId}&subject_id=${subjectId}&periode=${encodeURIComponent(periode)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.competencies && data.competencies.length > 0) {
                        data.competencies.forEach((comp, index) => {
                            if (index === 0 && competency1Select) {
                                competency1Select.value = String(comp.competency_id);
                            } else if (index === 1 && competency2Select) {
                                competency2Select.value = String(comp.competency_id);
                            }
                        });
                        updateCompetencyStatus('loaded');
                        return true;
                    }

                    if (competency1Select) competency1Select.value = '';
                    if (competency2Select) competency2Select.value = '';
                    updateCompetencyStatus('loaded');
                    return false;
                })
                .catch(error => {
                    console.error('Error loading evaluation competencies:', error);
                    return null;
                });
        }

        // Save competency associations
        if (saveCompetenciesBtn) {
            saveCompetenciesBtn.addEventListener('click', function() {
                const competencyIds = [];
                
                if (competency1Select && competency1Select.value) {
                    competencyIds.push(competency1Select.value);
                }
                
                if (competency2Select && competency2Select.value) {
                    competencyIds.push(competency2Select.value);
                }

                if (competencyIds.length === 0) {
                    alert(<?= json_encode(__('select_at_least_one_competency') ?? 'Veuillez sélectionner au moins une compétence pour cette évaluation') ?>);
                    return;
                }

                const uniqueCompetencyIds = [...new Set(competencyIds)];

                if (competencyIds.length !== uniqueCompetencyIds.length) {
                    alert(<?= json_encode(__('max_2_competencies_error') ?? 'Une compétence ne peut pas être sélectionnée deux fois') ?>);
                    return;
                }

                if (uniqueCompetencyIds.length > 2) {
                    alert(<?= json_encode(__('max_2_competencies_error') ?? 'Maximum 2 compétences autorisées') ?>);
                    return;
                }

                const formData = new FormData();
                formData.append('class_id', classId);
                formData.append('subject_id', subjectId);
                formData.append('periode', periode);
                uniqueCompetencyIds.forEach(id => formData.append('competency_ids[]', id));

                this.disabled = true;
                this.innerHTML = '<i class="bi bi-arrow-clockwise me-2 spin"></i><?= __('saving') ?? 'Enregistrement...' ?>';

                fetch('/competencies/api/link-to-evaluation', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCompetencyStatus('saved');
                    } else {
                        alert(data.error || <?= json_encode(__('error_saving_competencies') ?? 'Erreur lors de l\'enregistrement') ?>);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert(<?= json_encode(__('error_saving_competencies') ?? 'Erreur lors de l\'enregistrement') ?>);
                })
                .finally(() => {
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-check-lg me-2"></i><?= __('save_competencies') ?? 'Enregistrer' ?>';
                });
            });
        }

        // Create new competency via modal
        const competencyModal = document.getElementById('newCompetencyModal');
        const competencySlotInput = document.getElementById('competencySlotInput');
        const competencyNameInput = document.getElementById('newCompetencyName');
        const competencyDescriptionInput = document.getElementById('newCompetencyDescription');
        const competencyForm = document.getElementById('newCompetencyForm');

        window.openCompetencyModal = function(slot) {
            if (!competencyModal || !competencySlotInput) return;
            competencySlotInput.value = String(slot || 1);
            if (competencyNameInput) {
                competencyNameInput.value = '';
                competencyNameInput.focus();
            }
            if (competencyDescriptionInput) competencyDescriptionInput.value = '';

            const bootstrapModal = typeof bootstrap !== 'undefined' ? bootstrap.Modal.getOrCreateInstance(competencyModal) : null;
            if (bootstrapModal) {
                bootstrapModal.show();
            }
        };

        if (competencyForm) {
            competencyForm.addEventListener('submit', function(event) {
                event.preventDefault();

                const libelle = (competencyNameInput ? competencyNameInput.value.trim() : '').trim();
                const description = competencyDescriptionInput ? competencyDescriptionInput.value.trim() : '';
                const slot = Number(competencySlotInput ? competencySlotInput.value : 1);

                if (!libelle) {
                    if (competencyNameInput) competencyNameInput.focus();
                    alert(<?= json_encode(__('enter_competency_name') ?? 'Entrez le nom de la compétence') ?>);
                    return;
                }

                const formData = new FormData();
                formData.append('subject_id', subjectId);
                formData.append('class_id', classId);
                formData.append('libelle', libelle);
                formData.append('description', description);

                fetch('/competencies/api/create', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (competencyModal && typeof bootstrap !== 'undefined') {
                            const modalInstance = bootstrap.Modal.getInstance(competencyModal);
                            if (modalInstance) modalInstance.hide();
                        }

                        return loadSubjectCompetencies().then(() => {
                            const select = slot === 1 ? competency1Select : competency2Select;
                            if (select) {
                                select.value = String(data.competency.id);
                                updateCompetencyStatus('changed');
                            }
                        });
                    }

                    throw new Error(data.error || <?= json_encode(__('error_creating_competency') ?? 'Erreur lors de la création') ?>);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert(error.message || <?= json_encode(__('error_creating_competency') ?? 'Erreur lors de la création') ?>);
                });
            });
        }

        function updateGradeWorkflowState() {
            const stepItems = document.querySelectorAll('.workflow-step');
            const workflowSummary = document.getElementById('gradeWorkflowSummary');
            if (!stepItems.length) return;

            const evaluationDone = !!(periode && periode.trim() !== '');
            const competencyDone = !!Array.from(document.querySelectorAll('#competency1Select, #competency2Select'))
                .map(select => String(select.value || '').trim())
                .filter(Boolean).length;
            const notesDone = !!Array.from(document.querySelectorAll('.js-note-input')).some(input => String(input.value || '').trim() !== '');

            const states = {
                evaluation: evaluationDone,
                competency: competencyDone,
                notes: notesDone,
                save: competencyDone && notesDone
            };

            stepItems.forEach(step => {
                const stepName = step.dataset.step;
                const isDone = !!states[stepName];
                const isActive = !isDone && (
                    (stepName === 'evaluation' && !evaluationDone) ||
                    (stepName === 'competency' && evaluationDone && !competencyDone) ||
                    (stepName === 'notes' && evaluationDone && competencyDone && !notesDone) ||
                    (stepName === 'save' && evaluationDone && competencyDone && notesDone && !states.save)
                );

                step.classList.toggle('done', isDone);
                step.classList.toggle('active', isActive);
            });

            if (workflowSummary) {
                const completed = Object.values(states).filter(Boolean).length;
                workflowSummary.textContent = `${completed}/4`;
            }
        }

        // Update competency status message
        function updateCompetencyStatus(status) {
            if (!competencyStatus) return;
            
            const messages = {
                loaded: '<i class="bi bi-check-circle text-success me-1"></i><?= __('competencies_loaded') ?? 'Compétences chargées' ?>',
                saved: '<i class="bi bi-check-circle-fill text-success me-1"></i><?= __('competencies_saved') ?? 'Compétences enregistrées avec succès' ?>',
                changed: '<i class="bi bi-exclamation-circle text-warning me-1"></i><?= __('competencies_changed_not_saved') ?? 'Modifications non enregistrées' ?>'
            };
            
            competencyStatus.innerHTML = messages[status] || competencyStatus.innerHTML;
        }

        document.querySelectorAll('#competency1Select, #competency2Select, .js-note-input').forEach(input => {
            input.addEventListener('input', updateGradeWorkflowState);
            input.addEventListener('change', updateGradeWorkflowState);
        });

        updateGradeWorkflowState();

        // Track changes to show unsaved status
        [competency1Select, competency2Select].forEach(select => {
            if (select) {
                select.addEventListener('change', function() {
                    if (competency1Select && competency2Select && competency1Select.value && competency2Select.value && competency1Select.value === competency2Select.value) {
                        alert(<?= json_encode(__('max_2_competencies_error') ?? 'Une compétence ne peut pas être sélectionnée deux fois') ?>);
                        this.value = '';
                    }
                    updateCompetencyStatus('changed');
                });
            }
        });

        // Initial load
        loadSubjectCompetencies();

        function getAppreciation(note) {
            if (note >= 18) return appreciationLabels.excellent;
            if (note >= 16) return appreciationLabels.veryGood;
            if (note >= 14) return appreciationLabels.good;
            if (note >= 12) return appreciationLabels.fairlyGood;
            if (note >= 10) return appreciationLabels.passable;
            if (note >= 8) return appreciationLabels.insufficient;
            return appreciationLabels.veryInsufficient;
        }

        document.querySelectorAll('.js-note-input').forEach(function (noteInput) {
            const studentId = noteInput.dataset.studentId;
            const appreciationInput = document.querySelector('.js-appreciation-input[data-student-id="' + studentId + '"]');
            const row = document.getElementById('row-' + studentId);

            if (!appreciationInput || !row) return;

            // Initial styling based on value
            function styleNote(val) {
                if (val === '') {
                    noteInput.classList.remove('note-high', 'note-low');
                    row.classList.remove('row-success', 'row-danger');
                    return;
                }
                const fVal = parseFloat(val);
                noteInput.classList.toggle('note-high', fVal >= 10);
                noteInput.classList.toggle('note-low', fVal < 10);
                row.classList.toggle('row-success', fVal >= 10);
                row.classList.toggle('row-danger', fVal < 10);
            }
            
            styleNote(noteInput.value);

            appreciationInput.dataset.autoFilled = appreciationInput.value.trim() === '' ? 'true' : 'false';
            
            appreciationInput.addEventListener('input', function () {
                appreciationInput.dataset.autoFilled = 'false';
            });

            noteInput.addEventListener('input', function () {
                const rawValue = noteInput.value.trim();
                styleNote(rawValue);
                
                if (rawValue === '') {
                    if (appreciationInput.dataset.autoFilled === 'true') appreciationInput.value = '';
                    return;
                }
                if (appreciationInput.dataset.autoFilled === 'true') {
                    appreciationInput.value = getAppreciation(parseFloat(rawValue));
                }
            });
            
            // Support navigation clavier fleches haut/bas
            noteInput.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                    e.preventDefault();
                    const allInputs = Array.from(document.querySelectorAll('.js-note-input'));
                    const currentIndex = allInputs.indexOf(noteInput);
                    const nextIndex = e.key === 'ArrowDown' ? currentIndex + 1 : currentIndex - 1;
                    if (allInputs[nextIndex]) allInputs[nextIndex].focus();
                }
            });
        });

        // Fonction de confirmation (Noir sur Blanc / Mobile Compact)
        window.confirmGradeSubmission = function() {
            const form = document.getElementById('gradeEntryForm');
            if (!form) return;

            const selectedCompetencyIds = Array.from(document.querySelectorAll('#competency1Select, #competency2Select'))
                .map(select => String(select.value || '').trim())
                .filter(Boolean);

            if (selectedCompetencyIds.length === 0) {
                if (typeof AlertService !== 'undefined') {
                    AlertService.toast('error', 'Veuillez sélectionner au moins une compétence pour cette évaluation.');
                } else {
                    alert('Veuillez sélectionner au moins une compétence pour cette évaluation.');
                }
                return;
            }

            const noteInputs = form.querySelectorAll('.js-note-input');
            let hasInvalidGrade = false;

            Array.from(noteInputs).forEach(input => {
                const val = input.value.trim();
                if (val !== '') {
                    const fVal = parseFloat(val);
                    if (fVal > 20) {
                        hasInvalidGrade = true;
                        input.classList.add('is-invalid', 'border-danger');
                    } else {
                        input.classList.remove('is-invalid', 'border-danger');
                    }
                }
            });

            if (hasInvalidGrade) {
                if (typeof AlertService !== 'undefined') {
                    AlertService.toast('error', 'La note ne doit pas être supérieure à 20');
                } else {
                    alert('La note ne doit pas être supérieure à 20');
                }
                return;
            }

            const filledCount = Array.from(noteInputs).filter(input => input.value.trim() !== '').length;

            if (typeof AlertService === 'undefined') {
                if (confirm("Enregistrer les notes ?")) form.submit();
                return;
            }

            const htmlContent = `
                <div style="font-size: 0.85rem; color: #000000;">
                    <p class="mb-2 fw-medium"><?= json_encode(__('grade_save_confirm_text'), JSON_UNESCAPED_UNICODE) ?></p>
                    <div class="d-inline-block px-3 py-1 rounded-pill bg-warning-subtle text-warning-emphasis fw-bold small">
                        ${filledCount} notes
                    </div>
                </div>
            `;

            AlertService.confirm({
                title: <?= json_encode(__('grade_save_confirm_title'), JSON_UNESCAPED_UNICODE) ?>,
                html: htmlContent,
                icon: 'question',
                confirmText: <?= json_encode(__('confirm'), JSON_UNESCAPED_UNICODE) ?>,
                cancelText: <?= json_encode(__('cancel'), JSON_UNESCAPED_UNICODE) ?>,
                width: '320px',
                background: '#ffffff', // Fond blanc solide
                customClass: {
                    popup: 'rounded-4 shadow-sm p-3 border border-light',
                    title: 'text-black fw-bolder fs-5', // Titre noir
                    confirmButton: 'btn btn-primary btn-sm w-100 mb-2 rounded-pill',
                    cancelButton: 'btn btn-light btn-sm w-100 rounded-pill',
                    actions: 'd-flex flex-column w-100 gap-1'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.dataset.confirmed = 'true';
                    AlertService.loading(<?= json_encode(__('saving'), JSON_UNESCAPED_UNICODE) ?>);
                    setTimeout(() => form.submit(), 50);
                }
            });
        };

        // Empêcher aussi la soumission par "Entrée" sans confirmation
        const form = document.getElementById('gradeEntryForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (form.dataset.confirmed !== 'true') {
                    e.preventDefault();
                    window.confirmGradeSubmission();
                }
            });
        }
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
