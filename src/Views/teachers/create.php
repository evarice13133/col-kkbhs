<?php
$title = __('add_teacher');
ob_start();
?>

<div class="animate-fade-in container-fluid py-2">
    <!-- Compact Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="fw-black text-main-theme mb-0 fs-4"><?= __('teacher_profile_opening') ?></h2>
        <a href="/teachers" class="btn btn-sm btn-light-theme rounded-pill px-3 border-theme-light">
            <i class="bi bi-arrow-left me-1"></i> <?= __('back_to_list') ?>
        </a>
    </div>

    <?php include __DIR__ . '/_form.php'; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('teacherCreateForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const checked = form.querySelectorAll('input[name="teaching_type_ids[]"]:checked');
            if (checked.length === 0) {
                e.preventDefault();
                if (typeof AlertService !== 'undefined') {
                    AlertService.toast('error', "<?= htmlspecialchars(__('select_at_least_one_teaching_type'), ENT_QUOTES) ?>");
                } else {
                    alert("<?= htmlspecialchars(__('select_at_least_one_teaching_type'), ENT_QUOTES) ?>");
                }
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>