<?php
$title = __('edit_academic_year');
ob_start();
?>

<div class="animate-fade-in container-fluid py-2">
    <!-- Compact Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="fw-black text-main-theme mb-0 fs-4"><?= __('edit_academic_year') ?></h2>
        <a href="/academic_years" class="btn btn-sm btn-light-theme rounded-pill px-3 border-theme-light">
            <i class="bi bi-arrow-left me-1"></i> <?= __('back_to_list') ?>
        </a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="/academic_years/update?id=<?= $year['id'] ?>" method="POST" id="academicYearEditForm">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">

        <div class="subject-card-compact border-0 shadow-sm overflow-hidden mb-4">
            <div class="card-body p-4">
                
                <!-- Identification Section -->
                <div class="row g-4 mb-4">
                    <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                        <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1"><?= __('academic_year_config') ?></h6>
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('academic_year_name_label') ?></label>
                        <input type="text" name="nom" class="form-control premium-input" 
                            value="<?= htmlspecialchars($year['nom']) ?>"
                            placeholder="<?= __('academic_year_name_placeholder') ?>" required autofocus>
                        <div class="form-text extra-small mt-1 opacity-75"><?= __('academic_year_public_label_help') ?></div>
                    </div>
                </div>

                <!-- Info Section -->
                <div class="row g-4 mb-4">
                    <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                        <h6 class="fw-black text-info m-0 text-uppercase letter-spacing-1"><?= __('year_info') ?></h6>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('status') ?></label>
                        <div class="p-2 bg-light rounded">
                            <?php if($year['is_active']): ?>
                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill small fw-bold">
                                    <i class="bi bi-check-circle me-1"></i><?= __('status_current_active') ?>
                                </span>
                            <?php elseif($year['status'] === 'archived'): ?>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill small fw-bold">
                                    <i class="bi bi-archive me-1"></i><?= __('status_archived_closed') ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill small fw-bold">
                                    <i class="bi bi-clock me-1"></i><?= __('status_pending_inactive') ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('created_at') ?></label>
                        <div class="p-2 bg-light rounded">
                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($year['created_at'])) ?></small>
                        </div>
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="d-flex justify-content-end border-top border-theme-light pt-4 mt-2 gap-2">
                    <a href="/academic_years" class="btn btn-light-theme rounded-pill px-4 py-2 fw-bold border-theme-light">
                        <i class="bi bi-x-circle me-2"></i> <?= __('cancel') ?>
                    </a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm transition-base scale-on-hover">
                        <i class="bi bi-check-circle-fill me-2"></i> <?= __('save') ?>
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
