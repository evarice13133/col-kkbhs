<?php
$title = __('import_grades');
ob_start();
?>

<div class="animate-fade-in container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="/notes"
                class="btn btn-outline-secondary rounded-circle shadow-sm p-2 d-flex align-items-center justify-content-center"
                style="width: 40px; height: 40px;">
                <i class="bi bi-arrow-left fs-5"></i>
            </a>
            <div>
                <h2 class="fw-bold mb-0 text-body text-main-theme"><?= __('import_grades') ?></h2>
                <p class="text-secondary text-main-theme small mb-0">
                    <?= htmlspecialchars($classInfo['nom']) ?>
                </p>
            </div>
        </div>
        <div></div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 p-4">
            <h5 class="fw-bold text-danger mb-3"><i class="bi bi-exclamation-octagon-fill me-2"></i><?= __('error_title') ?>
            </h5>
            <ul class="list-unstyled mb-0 small">
                <?php foreach ($errors as $error): ?>
                    <li class="mb-2"><?= htmlspecialchars((string) $error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="modern-card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <span
                            class="badge bg-primary text-white rounded-circle p-0 d-flex align-items-center justify-content-center text-main-theme"
                            style="width: 24px; height: 24px;">1</span>
                        <h5 class="fw-bold mb-0 text-body text-main-theme"><?= __('download_template') ?></h5>
                    </div>
                </div>
                <div class="card-body p-4 pt-2">
                    <p class="small text-secondary text-main-theme mb-3"><?= __('import_grades_multi_sheet_desc') ?></p>
                    <a href="/notes/downloadTemplate?class_id=<?= $class_id ?>&subject_id=0"
                        class="btn btn-outline-primary rounded-3 px-4 fw-bold">
                        <i class="bi bi-download me-2"></i><?= __('download_template') ?>
                    </a>
                </div>
            </div>

            <div class="modern-card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <span
                            class="badge bg-primary text-white rounded-circle p-0 d-flex align-items-center justify-content-center text-main-theme"
                            style="width: 24px; height: 24px;">2</span>
                        <h5 class="fw-bold mb-0 text-body text-main-theme"><?= __('validate_import_final') ?></h5>
                    </div>
                </div>
                <div class="card-body p-4 pt-2">
                    <form action="/notes/upload" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                        <input type="hidden" name="class_id" value="<?= $class_id ?>">
                        <input type="hidden" name="subject_id" value="0">
                        <input type="file" id="grade-import-file" name="import_file" class="form-control mb-3"
                            accept=".xlsx" required>
                        <button type="submit" id="grade-import-submit"
                            class="btn btn-outline-success w-100 fw-bold rounded-3 py-3" disabled>
                            <i class="bi bi-cloud-upload me-2"></i> <?= __('validate_import_final') ?>
                        </button>
                        <p class="small text-secondary text-main-theme mt-2 mb-0">
                            <?= __('select_xlsx_to_enable_import') ?>
                        </p>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="modern-card border-0 shadow-sm p-4 rounded-4">
                <h6 class="fw-bold text-body text-main-theme mb-2"><?= __('data_integrity') ?></h6>
                <p class="small text-secondary text-main-theme mb-0"><?= __('import_grades_integrity_desc') ?></p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const importFile = document.getElementById('grade-import-file');
        const importSubmit = document.getElementById('grade-import-submit');
        if (!importFile || !importSubmit) return;

        importFile.addEventListener('change', function () {
            importSubmit.disabled = importFile.files.length === 0;
        });
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>