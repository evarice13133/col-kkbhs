<?php $title = __('edit_department'); ob_start(); ?>

<div class="animate-fade-in container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="modern-card border-0 shadow-lg overflow-hidden rounded-4">
                <div class="card-header bg-primary bg-opacity-10 border-0 p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-init bg-primary text-white rounded-4 d-flex align-items-center justify-content-center shadow-sm"
                            style="width: 48px; height: 48px;">
                            <i class="bi bi-pencil-square fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-black m-0 text-primary text-uppercase letter-spacing-1"><?= __('edit_department') ?></h5>
                            <p class="text-muted small mb-0"><?= h($department['nom']) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger rounded-4 border-0 shadow-sm animate-shake mb-4">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= h($error) ?>
                        </div>
                    <?php endif; ?>

                    <form action="/departments/update?id=<?= $department['id'] ?>" method="POST">
                        <div class="mb-4">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">
                                <?= __('department_name') ?> <span class="text-danger">*</span>
                            </label>
                            <div class="input-group-modern">
                                <span class="input-group-text-modern"><i class="bi bi-fonts"></i></span>
                                <input type="text" name="nom" class="form-control premium-input" 
                                       value="<?= h($department['nom']) ?>" required autofocus>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">
                                <?= __('department_code') ?> <span class="text-danger">*</span>
                            </label>
                            <div class="input-group-modern">
                                <span class="input-group-text-modern"><i class="bi bi-hash"></i></span>
                                <input type="text" name="code" class="form-control premium-input" 
                                       value="<?= h($department['code']) ?>" required>
                            </div>
                        </div>

                        <div class="d-flex gap-3 mt-5">
                            <a href="/departments" class="btn btn-light rounded-pill px-4 fw-bold flex-grow-1 scale-on-hover">
                                <?= __('cancel') ?>
                            </a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold flex-grow-1 shadow-sm scale-on-hover">
                                <i class="bi bi-save-fill me-2"></i> <?= __('save') ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .modern-card { background: var(--bg-card); }
    .input-group-modern {
        display: flex;
        align-items: center;
        background: var(--bg-body);
        border: 1px solid var(--border-theme);
        border-radius: 16px;
        transition: all 0.3s ease;
        padding: 0 15px;
    }
    .input-group-modern:focus-within {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.1);
    }
    .input-group-text-modern {
        color: var(--primary-color);
        opacity: 0.6;
        margin-right: 10px;
    }
    .premium-input {
        background: transparent !important;
        border: none !important;
        padding: 12px 0 !important;
        box-shadow: none !important;
        color: var(--text-main) !important;
        font-weight: 600;
    }
    .scale-on-hover:hover { transform: scale(1.02); }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
