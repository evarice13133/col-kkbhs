<?php
$title = __('import_teachers_title');
ob_start();
?>

<div class="animate-fade-in container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="/teachers" class="btn btn-outline-secondary rounded-circle shadow-sm p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-arrow-left fs-5"></i>
            </a>
            <div>
                <h2 class="fw-bold mb-0 text-body text-main-theme"><?= __('import_teachers_title') ?></h2>
                <p class="text-secondary text-main-theme small mb-0"><?= __('import_teachers_desc') ?></p>
            </div>
        </div>
        <div></div>
    </div>

    <?php include __DIR__ . '/_import_form.php'; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const importFile = document.getElementById('teacher-import-file');
    const importSubmit = document.getElementById('teacher-import-submit');
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
