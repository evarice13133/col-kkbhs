<?php
$title = __('import_students');
ob_start();
?>

<div class="animate-fade-in container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="/students" class="btn btn-outline-secondary rounded-circle shadow-sm p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-arrow-left fs-5"></i>
            </a>
            <div>
                <h2 class="fw-bold mb-0 text-body text-main-theme"><?= __('import_students') ?></h2>
                <p class="text-secondary text-main-theme small mb-0"><?= __('import_students_desc') ?></p>
            </div>
        </div>
        <div></div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="mb-4">
            <div class="alert alert-warning border-0 shadow-sm rounded-4 p-3 d-flex align-items-center justify-content-between">
                <div class="small text-secondary"><?= __('import_errors_summary') ?? 'Des erreurs ont été détectées lors de l\'import.' ?></div>
                <div>
                    <button id="show-import-errors" type="button" class="btn btn-sm btn-outline-danger"><?= __('view_import_errors') ?? 'Voir les erreurs' ?></button>
                </div>
            </div>
        </div>

        <div id="import-errors-data" style="display:none;" data-errors='<?= json_encode($errors, JSON_UNESCAPED_UNICODE) ?>'></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Etape 1: Choix du type d'enseignement -->
            <div class="modern-card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary text-white rounded-circle p-0 d-flex align-items-center justify-content-center text-main-theme" style="width: 24px; height: 24px;">1</span>
                        <h5 class="fw-bold mb-0 text-body text-main-theme"><?= __('teaching_type') ?? 'Type d\'enseignement' ?></h5>
                    </div>
                </div>
                <div class="card-body p-4 pt-2">
                    <p class="small text-secondary text-main-theme mb-3">Sélectionnez le type d'enseignement pour filtrer les classes lors de l'import.</p>
                    <select id="teaching-type-select" class="form-select form-select-lg mb-3">
                        <option value="" selected disabled><?= __('select_teaching_type') ?? 'Sélectionner le type' ?></option>
                        <?php foreach ($teachingTypes as $tt): ?>
                            <option value="<?= $tt['id'] ?>"><?= htmlspecialchars((string) $tt['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Etape 2: Téléchargement du template -->
            <div class="modern-card border-0 shadow-sm rounded-4 overflow-hidden mb-4 opacity-50" id="step2-container">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary text-white rounded-circle p-0 d-flex align-items-center justify-content-center text-main-theme" style="width: 24px; height: 24px;">2</span>
                        <h5 class="fw-bold mb-0 text-body text-main-theme"><?= __('download_template') ?></h5>
                    </div>
                </div>
                <div class="card-body p-4 pt-2">
                    <p class="small text-secondary text-main-theme mb-3"><?= __('import_students_step1_desc') ?></p>
                    <a href="#" id="download-template-btn" class="btn btn-outline-primary rounded-3 px-4 fw-bold disabled">
                        <i class="bi bi-download me-2"></i><?= __('download_template') ?>
                    </a>
                </div>
            </div>

            <!-- Etape 3: Importation du fichier -->
            <div class="modern-card border-0 shadow-sm rounded-4 overflow-hidden opacity-50" id="step3-container">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary text-white rounded-circle p-0 d-flex align-items-center justify-content-center text-main-theme" style="width: 24px; height: 24px;">3</span>
                        <h5 class="fw-bold mb-0 text-body text-main-theme"><?= __('validate_import_final') ?></h5>
                    </div>
                </div>
                <div class="card-body p-4 pt-2">
                    <form action="/students/upload" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                        <input type="hidden" name="teaching_type_id" id="hidden-teaching-type" value="">
                        <input type="file" id="student-import-file" name="import_file" class="form-control mb-3" accept=".xlsx" required disabled>
                        <button type="submit" id="student-import-submit" class="btn btn-outline-success w-100 fw-bold rounded-3 py-3" disabled>
                            <i class="bi bi-cloud-upload me-2"></i> <?= __('validate_import_final') ?>
                        </button>
                        <p class="small text-secondary text-main-theme mt-2 mb-0"><?= __('select_xlsx_to_enable_import') ?></p>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="modern-card border-0 shadow-sm p-4 rounded-4">
                <h6 class="fw-bold text-body text-main-theme mb-2"><?= __('data_integrity') ?></h6>
                <p class="small text-secondary text-main-theme mb-0"><?= __('import_integrity_desc') ?></p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const importFile = document.getElementById('student-import-file');
    const importSubmit = document.getElementById('student-import-submit');
    const teachingTypeSelect = document.getElementById('teaching-type-select');
    const downloadTemplateBtn = document.getElementById('download-template-btn');
    const hiddenTeachingType = document.getElementById('hidden-teaching-type');
    const step2Container = document.getElementById('step2-container');
    const step3Container = document.getElementById('step3-container');

    if (teachingTypeSelect) {
        teachingTypeSelect.addEventListener('change', function() {
            const val = this.value;
            if (val) {
                // Enable Step 2 & 3
                step2Container.classList.remove('opacity-50');
                step3Container.classList.remove('opacity-50');
                
                downloadTemplateBtn.classList.remove('disabled');
                downloadTemplateBtn.href = '/students/download_template?teaching_type_id=' + val;
                
                hiddenTeachingType.value = val;
                if(importFile) importFile.disabled = false;
            } else {
                step2Container.classList.add('opacity-50');
                step3Container.classList.add('opacity-50');
                downloadTemplateBtn.classList.add('disabled');
                downloadTemplateBtn.href = '#';
                hiddenTeachingType.value = '';
                if(importFile) {
                    importFile.disabled = true;
                    importFile.value = '';
                }
                if(importSubmit) importSubmit.disabled = true;
            }
        });
    }

    if (importFile && importSubmit) {
        importFile.addEventListener('change', function () {
            importSubmit.disabled = importFile.files.length === 0;
        });
    }

    // Gestion du modal d'erreurs d'import (auto-open et bouton de réouverture)
    const errorsDataEl = document.getElementById('import-errors-data');
    if (errorsDataEl) {
        let errors = [];
        try {
            errors = JSON.parse(errorsDataEl.getAttribute('data-errors') || '[]');
        } catch (e) {
            errors = [];
        }
        const escaped = errors.map(e => String(e).replace(/</g, '&lt;').replace(/>/g, '&gt;'));
        const html = '<ul class="text-start small" style="margin:0;padding-left:1.2rem;">' + escaped.map(e => '<li>' + e + '</li>').join('') + '</ul>';

        // Ouvrir automatiquement le modal listant les erreurs
        AlertService.error("<?= addslashes((string) __('import_errors_title') ?? 'Erreurs d\'import') ?>", html);

        // Bouton pour ré-afficher
        const btn = document.getElementById('show-import-errors');
        if (btn) btn.addEventListener('click', () => {
            AlertService.error("<?= addslashes((string) __('import_errors_title') ?? 'Erreurs d\'import') ?>", html);
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
