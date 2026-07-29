<?php $title = __('add_level') ?? 'Ajouter un Niveau'; ob_start(); ?>

<div class="animate-fade-in container-fluid py-4 max-w-700">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="fw-black text-main-theme m-0 fs-4">
            <i class="bi bi-bar-chart-steps me-2 text-primary"></i><?= __('add_level') ?? 'Ajouter un Niveau' ?>
        </h2>
        <a href="/levels" class="btn btn-sm btn-light-theme rounded-pill px-3 border-theme-light">
            <i class="bi bi-arrow-left me-1"></i> <?= __('back_to_list') ?? 'Retour' ?>
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars((string)$error) ?>
        </div>
    <?php endif; ?>

    <div class="modern-card border-0 shadow-sm overflow-hidden">
        <div class="card-body p-4">
            <form action="/levels/store" method="POST">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">

                <div class="mb-3">
                    <label class="form-label fw-bold extra-small text-uppercase text-muted-theme"><?= __('teaching_type') ?? 'Type d\'enseignement' ?> *</label>
                    <select name="teaching_type_id" class="form-select premium-input border-primary border-opacity-25" required>
                        <option value=""><?= __('select_teaching_type') ?? '-- Sélectionner un type --' ?></option>
                        <?php foreach ($teachingTypes as $tt): ?>
                            <option value="<?= $tt['id'] ?>" <?= (int)($_POST['teaching_type_id'] ?? 0) === (int)$tt['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $tt['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold extra-small text-uppercase text-muted-theme"><?= __('level_code') ?? 'Code du Niveau' ?> *</label>
                    <input type="text" name="code" class="form-control premium-input text-uppercase" placeholder="Ex: SIL, 6EME, L1" value="<?= htmlspecialchars((string)($_POST['code'] ?? '')) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold extra-small text-uppercase text-muted-theme"><?= __('label_fr') ?? 'Libellé (Français)' ?> *</label>
                    <input type="text" name="libelle_fr" class="form-control premium-input" placeholder="Ex: Section d'Initiation à la Lecture" value="<?= htmlspecialchars((string)($_POST['libelle_fr'] ?? '')) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold extra-small text-uppercase text-muted-theme"><?= __('label_en') ?? 'Libellé (Anglais)' ?> *</label>
                    <input type="text" name="libelle_en" class="form-control premium-input" placeholder="Ex: Class 1 / Level 1" value="<?= htmlspecialchars((string)($_POST['libelle_en'] ?? '')) ?>" required>
                </div>

                <div class="form-check form-switch mt-3 mb-4">
                    <input class="form-check-input" type="checkbox" name="status" id="create_status" value="1" <?= isset($_POST['status']) || $_SERVER['REQUEST_METHOD'] === 'GET' ? 'checked' : '' ?>>
                    <label class="form-check-label fw-bold text-main-theme" for="create_status"><?= __('active') ?? 'Actif' ?></label>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="/levels" class="btn btn-light rounded-pill px-4"><?= __('cancel') ?? 'Annuler' ?></a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"><?= __('save') ?? 'Enregistrer' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../templates/layout.php'; ?>
