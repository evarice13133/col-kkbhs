<?php
$title = __('expense_categories');
ob_start();
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-black text-main-theme mb-1 fs-4 d-flex align-items-center gap-2">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-success bg-opacity-10 text-success p-2" style="width:40px;height:40px;">
                    <i class="bi bi-tags fs-5"></i>
                </span>
                <?= __('expense_categories') ?>
            </h2>
            <p class="text-muted-theme small mb-0"><?= __('expense_cat_desc') ?></p>
        </div>
        <div>
            <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="bi bi-plus-circle-fill me-2"></i> <?= __('expense_cat_add') ?>
            </button>
            <a href="/expenses" class="btn btn-outline-secondary rounded-pill px-4 fw-bold ms-2">
                <i class="bi bi-arrow-left me-2"></i> <?= __('expense_cat_back') ?>
            </a>
        </div>
    </div>

    <!-- Categories List Card -->
    <div class="modern-card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 10%;"><?= __('expense_cat_th_id') ?></th>
                        <th style="width: 50%;"><?= __('expense_cat_th_name') ?></th>
                        <th style="width: 20%;" class="text-center"><?= __('expense_th_status') ?></th>
                        <th style="width: 20%;" class="pe-4 text-center"><?= __('expense_th_actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-info-circle fs-3 d-block mb-2 text-secondary"></i>
                                <?= __('expense_cat_no_data') ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-muted">#<?= $cat['id'] ?></td>
                                <td class="fw-bold text-main-theme"><?= h($cat['name']) ?></td>
                                <td class="text-center">
                                    <?php if ($cat['active'] == 1): ?>
                                        <span class="badge-premium badge-premium-success"><i class="bi bi-check-circle-fill"></i> <?= __('expense_status_active') ?></span>
                                    <?php else: ?>
                                        <span class="badge-premium badge-premium-secondary"><i class="bi bi-x-circle-fill"></i> <?= __('expense_status_inactive') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button type="button" class="btn btn-sm btn-action-modern text-primary edit-cat-btn"
                                            data-id="<?= $cat['id'] ?>"
                                            data-name="<?= h($cat['name']) ?>"
                                            data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                                            title="<?= __('edit') ?>">
                                            <i class="bi bi-pencil fs-5"></i>
                                        </button>
                                        
                                        <?php if ($cat['active'] == 1): ?>
                                            <a href="/expenses/categories/toggle?id=<?= $cat['id'] ?>" class="btn btn-sm btn-action-modern text-warning" title="<?= __('expense_cat_deactivate') ?>">
                                                <i class="bi bi-shield-slash fs-5"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="/expenses/categories/toggle?id=<?= $cat['id'] ?>" class="btn btn-sm btn-action-modern text-success" title="<?= __('expense_cat_activate') ?>">
                                                <i class="bi bi-shield-check fs-5"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Ajouter Catégorie -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="/expenses/categories/store" method="POST" class="modal-content border-0 rounded-4 shadow-lg">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-main-theme"><i class="bi bi-plus-circle me-2 text-primary"></i><?= __('expense_cat_add') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold"><?= __('expense_cat_field_name') ?></label>
                    <input type="text" name="name" class="form-control rounded-3" placeholder="<?= __('expense_cat_field_name_placeholder') ?>" required>
                </div>
            </div>
            <div class="modal-footer border-top p-3 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"><?= __('save') ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Modifier Catégorie -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="/expenses/categories/update" method="POST" class="modal-content border-0 rounded-4 shadow-lg">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
            <input type="hidden" name="id" id="edit-id">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-main-theme"><i class="bi bi-pencil me-2 text-primary"></i><?= __('expense_cat_edit') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold"><?= __('expense_cat_field_name') ?></label>
                    <input type="text" name="name" id="edit-name" class="form-control rounded-3" required>
                </div>
            </div>
            <div class="modal-footer border-top p-3 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"><?= __('save') ?></button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editBtns = document.querySelectorAll('.edit-cat-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit-id').value = this.dataset.id;
            document.getElementById('edit-name').value = this.dataset.name;
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
