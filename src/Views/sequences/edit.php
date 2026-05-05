<?php $title = __('edit_sequence');
ob_start(); ?>

<div class="animate-fade-in container-fluid py-4">
    <div class="mb-4">
        <a href="/sequences"
            class="btn btn-link text-decoration-none p-0 text-muted mb-2 d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> <?= __('back_to_list') ?>
        </a>
        <h2 class="fw-bold text-main"><?= __('edit_evaluation') ?></h2>
        <p class="text-muted small"><?= __('update_sequence_details') ?> : <strong><?= h($sequence['label']) ?></strong>
        </p>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="modern-card border-0 shadow-sm p-4">
                <form action="/sequences/update?id=<?= $sequence['id'] ?>" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-main-theme small"><?= __('sequence_code') ?></label>
                            <input type="text" name="code" class="form-control" value="<?= h($sequence['code']) ?>"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-main-theme small"><?= __('position_order') ?></label>
                            <input type="number" name="position" class="form-control"
                                value="<?= (int) $sequence['position'] ?>" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold text-main-theme small"><?= __('sequence_label') ?></label>
                            <input type="text" name="label" class="form-control" value="<?= h($sequence['label']) ?>"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-main-theme small"><?= __('Short Label') ?> (CC 1, SEQ 1...)</label>
                            <input type="text" name="short_label" class="form-control" value="<?= h($sequence['short_label'] ?? '') ?>"
                                maxlength="20" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-main-theme small"><?= __('trimester') ?></label>
                            <select name="trimestre" class="form-select" required>
                                <option value="1" <?= (int) $sequence['trimestre'] === 1 ? 'selected' : '' ?>>Trimestre 1
                                </option>
                                <option value="2" <?= (int) $sequence['trimestre'] === 2 ? 'selected' : '' ?>>Trimestre 2
                                </option>
                                <option value="3" <?= (int) $sequence['trimestre'] === 3 ? 'selected' : '' ?>>Trimestre 3
                                </option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActive"
                                    <?= (int) $sequence['is_active'] === 1 ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold text-main-theme small"
                                    for="isActive"><?= __('is_active_label') ?></label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 opacity-50">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/sequences" class="btn btn-light px-4 fw-bold rounded-3"><?= __('cancel') ?></a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm rounded-3">
                            <i class="bi bi-save me-1"></i><?= __('save_modifications') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>