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

    /* Info Bar */
    .info-header-card {
        background: linear-gradient(135deg, #4361ee, #3a0ca3);
        color: white;
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 15px 35px rgba(67, 97, 238, 0.2);
    }

    /* Sticky Toolbar */
    .sticky-grade-toolbar {
        position: sticky;
        top: 10px;
        z-index: 100;
        background: var(--bg-card);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 15px 25px;
        margin-bottom: 25px;
        border: 1px solid var(--border-color);
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    /* Saisie Table */
    .saisie-card {
        background: var(--bg-card);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        border: 1px solid var(--border-color);
    }

    .student-avatar {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(67, 97, 238, 0.1);
        color: #4361ee;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.9rem;
    }

    /* Input Styling */
    .input-grade-modern {
        border-radius: 12px;
        border: 2px solid var(--border-color);
        background: var(--bg-body);
        color: var(--text-main);
        font-weight: 800;
        text-align: center;
        font-size: 1.1rem;
        transition: var(--transition-fast);
        padding: 10px;
        width: 100px;
    }

    .input-grade-modern:focus {
        border-color: #4361ee;
        box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
        transform: scale(1.05);
        outline: none;
    }

    .input-appr-modern {
        border-radius: 12px;
        border: 2px solid var(--border-color);
        background: var(--bg-body);
        color: var(--text-main);
        font-weight: 600;
        transition: var(--transition-fast);
        padding: 10px 15px;
    }

    /* Feedback Colors */
    .row-success { background: rgba(76, 201, 240, 0.03); }
    .row-danger { background: rgba(247, 37, 133, 0.03); }

    .note-high { color: #2ecc71; }
    .note-low { color: #e74c3c; }

    /* Floating Save Button */
    .floating-save {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 1000;
        box-shadow: 0 15px 30px rgba(67, 97, 238, 0.4);
        padding: 15px 30px;
        border-radius: 50px;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
</style>

<div class="grade-saisie-page fade-in">
    
    <!-- Header Info -->
    <div class="info-header-card">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h2 class="fw-extrabold mb-1"><?= htmlspecialchars((string) $classInfo['nom']) ?></h2>
                <div class="d-flex align-items-center gap-2 opacity-90">
                    <span class="badge bg-white text-primary rounded-pill px-3 py-2 fw-bold">
                        <i class="bi bi-book me-1"></i><?= htmlspecialchars((string) $subjectInfo['nom']) ?>
                    </span>
                    <span><i class="bi bi-star-fill small ms-2 me-1"></i><?= __('coef') ?> <?= (int) $subjectInfo['coefficient'] ?></span>
                </div>
            </div>
            <a href="/notes" class="btn btn-link text-white text-decoration-none fw-bold">
                <i class="bi bi-x-lg me-1"></i><?= __('cancel') ?>
            </a>
        </div>
    </div>

    <!-- Alert Messages (Globally handled by AlertService in layout) -->

    <form action="/notes/store" method="POST" id="gradeEntryForm" class="no-loader">
        <input type="hidden" name="class_id" value="<?= $class_id ?>">
        <input type="hidden" name="subject_id" value="<?= $subject_id ?>">
        <input type="hidden" name="periode" value="<?= htmlspecialchars((string) $periode) ?>">

        <!-- Sticky Toolbar -->
        <div class="sticky-grade-toolbar">
            <div class="d-flex align-items-center gap-3">
                <label class="fw-bold text-muted-theme small text-uppercase"><?= __('evaluation') ?></label>
                <select class="form-select border-0 bg-theme-input text-main-theme fw-bold" style="border-radius: 10px; min-width: 250px;" 
                        onchange="window.location.href='/notes/saisie?class_id=<?= $class_id ?>&subject_id=<?= $subject_id ?>&periode=' + encodeURIComponent(this.value)">
                    <?php foreach ($periodes as $p): ?>
                        <option value="<?= htmlspecialchars((string) $p) ?>" <?= $periode === $p ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) $p) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="/notes/export?mode=report&format=pdf&class_id=<?= $class_id ?>&subject_id=<?= $subject_id ?>" 
                   class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold d-flex align-items-center gap-2 shadow-sm border-2">
                    <i class="bi bi-file-earmark-pdf-fill"></i>
                    <span class="d-none d-md-inline"><?= __('grade_report_pdf') ?></span>
                </a>
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
                                        placeholder="--">
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const appreciationLabels = <?= json_encode($appreciationLabels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

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
