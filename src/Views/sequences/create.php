<?php $title = __('add_sequence');
ob_start(); ?>

<div class="animate-fade-in container-fluid py-4">
    <div class="mb-4">
        <a href="/sequences"
            class="btn btn-link text-decoration-none p-0 text-muted mb-2 d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> <?= __('back_to_list') ?>
        </a>
        <h2 class="fw-bold text-main"><?= __('add_new_evaluation') ?></h2>
        <p class="text-muted small"><?= __('add_sequence_subtitle') ?></p>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="modern-card border-0 shadow-sm p-4">
                <form action="/sequences/store" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-main-theme small"><?= __('sequence_code') ?></label>
                            <input type="text" name="code" class="form-control" placeholder="Ex: SEQ1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-main-theme small"><?= __('position_order') ?></label>
                            <input type="number" name="position" class="form-control" placeholder="Ex: 1" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold text-main-theme small"><?= __('sequence_label') ?></label>
                            <input type="text" name="label" class="form-control"
                                placeholder="Ex: Trimestre 1 - Séquence 1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-main-theme small"><?= __('Short Label') ?></label>
                            <input type="text" name="short_label" class="form-control"
                                placeholder="Ex: SEQ 1 or CC 1" maxlength="20" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-main-theme small"><?= __('trimester') ?></label>
                            <select name="trimestre" class="form-select" required>
                                <option value="1">Trimestre 1</option>
                                <option value="2">Trimestre 2</option>
                                <option value="3">Trimestre 3</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4 opacity-50">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/sequences" class="btn btn-light px-4 fw-bold rounded-3"><?= __('cancel') ?></a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm rounded-3">
                            <i class="bi bi-check-lg me-1"></i><?= __('create_evaluation') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="modern-card border-0 shadow-sm p-4 bg-primary bg-opacity-5">
                <h5 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle me-2"></i>Conseils</h5>
                <ul class="text-muted small mb-0 d-flex flex-column gap-2">
                    <li><strong>Code :</strong> Utilisez un identifiant court unique (ex: S1, SEQ1).</li>
                    <li><strong>Position :</strong> Détermine l'ordre d'affichage dans les listes et bulletins.</li>
                    <li><strong>Trimestre :</strong> Rattache l'évaluation à une période académique globale.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>