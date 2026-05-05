<?php $title = __('unarchive_year_title');
ob_start(); ?>

<div class="row min-vh-100 justify-content-center align-items-center mt-n5">
    <div class="col-lg-6">
        <div class="card shadow-lg border-0 border-top border-primary border-4 animate-fade-in">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <div class="p-3 rounded-circle bg-primary bg-opacity-10 text-primary d-inline-block mb-3">
                        <i class="bi bi-arrow-counterclockwise fs-1"></i>
                    </div>
                    <h2 class="fw-bold text-main"><?= __('unarchive_year_title') ?> "<?= htmlspecialchars((string) $year['nom']) ?>"</h2>
                    <p class="text-muted"><?= __('unarchive_year_desc') ?></p>
                </div>

                <div class="alert alert-info shadow-sm border-0 rounded-4 p-3 mb-4">
                    <div class="d-flex gap-3">
                        <i class="bi bi-info-circle-fill fs-4"></i>
                        <div>
                            <strong>Note :</strong> <?= __('unarchive_info_note') ?>
                        </div>
                    </div>
                </div>

                <form action="/academic_years/do_unarchive" method="POST">
                    <input type="hidden" name="year_id" value="<?= $year['id'] ?>">

                    <div class="mb-4">
                        <h5 class="fw-bold mb-3 border-bottom pb-2"><?= __('access_options') ?></h5>
                        
                        <div class="form-check form-switch mb-3 p-3 border rounded-3 transition-base hover-shadow-sm">
                            <input class="form-check-input ms-0 me-3" type="checkbox" name="set_active" id="set_active" checked>
                            <label class="form-check-label fw-bold cursor-pointer" for="set_active">
                                <?= __('set_as_current_year') ?>
                            </label>
                            <div class="text-muted small ms-5">
                                <?= __('set_as_current_year_desc') ?>
                                <span class="text-danger fw-bold"><?= __('current_year_deactivation_warning') ?></span>
                            </div>
                        </div>

                        <div class="form-check form-switch mb-3 p-3 border rounded-3 opacity-75">
                            <input class="form-check-input ms-0 me-3" type="checkbox" id="admin_access" checked disabled>
                            <label class="form-check-label fw-bold" for="admin_access">
                                <?= __('admin_access') ?>
                            </label>
                            <div class="text-muted small ms-5"><?= __('default_included') ?></div>
                        </div>

                        <div class="form-check form-switch mb-3 p-3 border rounded-3 opacity-75">
                            <input class="form-check-input ms-0 me-3" type="checkbox" id="teacher_access" checked disabled>
                            <label class="form-check-label fw-bold" for="teacher_access">
                                <?= __('teacher_access') ?>
                            </label>
                            <div class="text-muted small ms-5"><?= __('included_if_current') ?></div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm fw-bold">
                            <i class="bi bi-check-circle me-2"></i> <?= __('confirm_restoration') ?>
                        </button>
                        <a href="/academic_years" class="btn btn-link text-muted text-decoration-none"><?= __('cancel_and_back') ?></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.hover-shadow-sm:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    border-color: var(--primary-color) !important;
}
.cursor-pointer { cursor: pointer; }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
