<?php
$title = __('expenses_list');
ob_start();
?>

<style>
.hover-card {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.hover-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px -6px rgba(0, 0, 0, 0.08) !important;
}
.table-modern td {
    vertical-align: middle;
}
.badge-premium-cancelled {
    background-color: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.2);
    border-radius: 99px;
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.cancelled-row {
    opacity: 0.65;
    background-color: rgba(0,0,0,0.01);
}
.cancelled-text {
    text-decoration: line-through;
}
</style>

<div class="animate-fade-in container-fluid py-3 px-md-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h2 class="fw-black text-main-theme mb-1 fs-4 d-flex align-items-center gap-2">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary p-2" style="width:40px;height:40px;">
                    <i class="bi bi-wallet2 fs-5"></i>
                </span>
                <?= __('expenses_list') ?>
            </h2>
            <p class="text-muted-theme small mb-0"><?= __('expenses_desc') ?></p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <i class="bi bi-plus-circle-fill me-2"></i> <?= __('expense_register') ?>
            </button>
            <a href="/expenses/categories" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
                <i class="bi bi-tags-fill me-2"></i> <?= __('expense_categories') ?>
            </a>
            <a href="/expenses/print?<?= http_build_query($_GET) ?>" target="_blank" class="btn btn-outline-danger rounded-pill px-4 fw-bold">
                <i class="bi bi-file-earmark-pdf-fill me-2"></i> <?= __('print') ?> PDF
            </a>
        </div>
    </div>

    <!-- KPIs Row -->
    <div class="row g-3 mb-4 animate-fade-in">
        <div class="col-6 col-md-3">
            <div class="modern-card kpi-card border-0 shadow-sm">
                <div class="kpi-icon-wrapper bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-cash-coin"></i>
                </div>
                <div class="kpi-value text-primary"><?= number_format($totalAmountFiltered, 0, ',', ' ') ?> <span class="fs-7 text-muted fw-normal">FCFA</span></div>
                <div class="kpi-label"><?= __('expense_kpi_filtered_active') ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="modern-card kpi-card border-0 shadow-sm">
                <div class="kpi-icon-wrapper bg-success bg-opacity-10 text-success">
                    <i class="bi bi-file-earmark-check"></i>
                </div>
                <div class="kpi-value text-success"><?= $totalItems ?></div>
                <div class="kpi-label"><?= __('expense_kpi_operations_count') ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="modern-card kpi-card border-0 shadow-sm">
                <div class="kpi-icon-wrapper bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-person-workspace"></i>
                </div>
                <div class="kpi-value text-warning"><?= count($users) ?></div>
                <div class="kpi-label"><?= __('expense_kpi_active_operators') ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="modern-card kpi-card border-0 shadow-sm">
                <div class="kpi-icon-wrapper bg-info bg-opacity-10 text-info">
                    <i class="bi bi-tags"></i>
                </div>
                <div class="kpi-value text-info"><?= count($categories) ?></div>
                <div class="kpi-label"><?= __('expense_kpi_registered_categories') ?></div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="modern-card border-0 shadow-sm p-4 mb-4">
        <h5 class="fw-bold text-main-theme mb-3 small text-uppercase"><i class="bi bi-funnel-fill me-2"></i><?= __('expense_filter_advanced') ?></h5>
        <form method="GET" action="/expenses" class="row g-3">
            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold"><?= __('expense_filter_search') ?></label>
                <input type="text" name="q" class="form-control rounded-pill px-3" placeholder="<?= __('expense_filter_search_placeholder') ?>" value="<?= h($search) ?>">
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small fw-bold"><?= __('expense_filter_start_date') ?></label>
                <input type="date" name="start_date" class="form-control rounded-pill px-3" value="<?= h($filters['start_date'] ?? '') ?>">
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small fw-bold"><?= __('expense_filter_end_date') ?></label>
                <input type="date" name="end_date" class="form-control rounded-pill px-3" value="<?= h($filters['end_date'] ?? '') ?>">
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small fw-bold"><?= __('expense_filter_category') ?></label>
                <select name="category" class="form-select rounded-pill px-3">
                    <option value=""><?= __('expense_filter_category_all') ?></option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (int)($filters['category'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>>
                            <?= h($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small fw-bold"><?= __('expense_filter_status') ?></label>
                <select name="status" class="form-select rounded-pill px-3">
                    <option value=""><?= __('expense_filter_status_all') ?></option>
                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>><?= __('expense_status_active') ?></option>
                    <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>><?= __('expense_status_cancelled') ?></option>
                </select>
            </div>
            <div class="col-12 col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary rounded-pill w-100 fw-bold shadow-sm">
                    <i class="bi bi-filter"></i>
                </button>
            </div>
            <div class="col-12 text-end mt-2">
                <a href="/expenses" class="text-decoration-none small text-muted hover-primary me-3">
                    <i class="bi bi-x-circle me-1"></i><?= __('expense_filter_reset') ?>
                </a>
            </div>
        </form>
    </div>

    <!-- Expenses Table -->
    <div class="modern-card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th class="ps-4"><?= __('expense_th_reference') ?></th>
                        <th><?= __('expense_th_date') ?></th>
                        <th><?= __('expense_th_category') ?></th>
                        <th><?= __('expense_th_motive') ?></th>
                        <th class="text-end"><?= __('expense_th_amount') ?></th>
                        <th><?= __('expense_th_created_by') ?></th>
                        <th><?= __('expense_th_status') ?></th>
                        <th class="pe-4 text-center"><?= __('expense_th_actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expenses)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-info-circle fs-3 d-block mb-2 text-secondary"></i>
                                <?= __('expense_no_data') ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $e): 
                            $isCancelled = $e['status'] === 'cancelled';
                            $rowClass = $isCancelled ? 'cancelled-row' : '';
                            $textClass = $isCancelled ? 'cancelled-text' : '';
                        ?>
                            <tr class="<?= $rowClass ?>">
                                <td class="ps-4 fw-bold"><code class="small text-primary"><?= h($e['reference']) ?></code></td>
                                <td><?= date('d/m/Y', strtotime($e['expense_date'])) ?></td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2.5 py-1 rounded-pill">
                                        <?= h($e['category_name']) ?>
                                    </span>
                                </td>
                                <td class="<?= $textClass ?>"><?= h($e['motive']) ?></td>
                                <td class="text-end fw-black <?= $textClass ?>">
                                    <?= number_format($e['amount'], 0, ',', ' ') ?> <span class="small fw-normal text-muted" style="font-size: 11px;">FCFA</span>
                                </td>
                                <td><span class="text-main-theme fw-medium"><?= h($e['user_name']) ?></span></td>
                                <td>
                                    <?php if ($e['status'] === 'active'): ?>
                                        <span class="badge-premium badge-premium-success"><i class="bi bi-check-circle-fill"></i> <?= __('expense_status_active') ?></span>
                                    <?php else: ?>
                                        <span class="badge-premium-cancelled" data-bs-toggle="tooltip" title="<?= h($e['cancel_reason']) ?>"><i class="bi bi-x-circle-fill"></i> <?= __('expense_status_cancelled') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <?php if (!$isCancelled): ?>
                                            <button type="button" class="btn btn-sm btn-action-modern text-primary edit-expense-btn" 
                                                data-id="<?= $e['id'] ?>"
                                                data-category="<?= $e['category_id'] ?>"
                                                data-date="<?= $e['expense_date'] ?>"
                                                data-amount="<?= $e['amount'] ?>"
                                                data-motive="<?= h($e['motive']) ?>"
                                                data-desc="<?= h($e['description'] ?? '') ?>"
                                                data-bs-toggle="modal" data-bs-target="#editExpenseModal"
                                                title="<?= __('edit') ?>">
                                                <i class="bi bi-pencil fs-5"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-action-modern text-danger cancel-expense-btn"
                                                data-id="<?= $e['id'] ?>"
                                                data-bs-toggle="modal" data-bs-target="#cancelExpenseModal"
                                                title="<?= __('expense_cancel_title') ?>">
                                                <i class="bi bi-trash fs-5"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-action-modern text-muted" 
                                                onclick="Swal.fire({title: '<?= addslashes((string) __('expense_cancel_reason_title')) ?>', text: '<?= addslashes(h($e['cancel_reason'])) ?>', icon: 'info'})"
                                                title="<?= __('expense_view_cancel_reason') ?>">
                                                <i class="bi bi-info-circle fs-5"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="d-flex justify-content-between align-items-center p-4 border-top">
                <span class="small text-muted-theme"><?= __('expense_pagination_info', ['count' => count($expenses), 'total' => $totalItems]) ?></span>
                <nav>
                    <ul class="pagination pagination-rounded mb-0">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"><i class="bi bi-chevron-left"></i></a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $page === $i ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>"><i class="bi bi-chevron-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Ajouter une Dépense -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="/expenses/store" method="POST" class="modal-content border-0 rounded-4 shadow-lg">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-main-theme"><i class="bi bi-plus-circle me-2 text-primary"></i><?= __('expense_register') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold"><?= __('expense_field_date') ?></label>
                    <input type="date" name="expense_date" class="form-control rounded-3" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold"><?= __('expense_field_category') ?></label>
                    <select name="category_id" class="form-select rounded-3" required>
                        <option value=""><?= __('expense_field_category_placeholder') ?></option>
                        <?php foreach ($categories as $cat): ?>
                            <?php if ($cat['active'] == 1): ?>
                                <option value="<?= $cat['id'] ?>"><?= h($cat['name']) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold"><?= __('expense_field_amount') ?></label>
                    <input type="number" name="amount" class="form-control rounded-3" placeholder="Ex: 25000" min="1" step="any" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold"><?= __('expense_field_motive') ?></label>
                    <input type="text" name="motive" class="form-control rounded-3" placeholder="<?= __('expense_field_motive_placeholder') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold"><?= __('expense_field_description') ?></label>
                    <textarea name="description" class="form-control rounded-3" rows="3" placeholder="<?= __('expense_field_description_placeholder') ?>"></textarea>
                </div>
            </div>
            <div class="modal-footer border-top p-3 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"><?= __('save') ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Modifier une Dépense -->
<div class="modal fade" id="editExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="/expenses/update" method="POST" class="modal-content border-0 rounded-4 shadow-lg">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
            <input type="hidden" name="id" id="edit-id">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-main-theme"><i class="bi bi-pencil me-2 text-primary"></i><?= __('expense_edit') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold"><?= __('expense_field_date') ?></label>
                    <input type="date" name="expense_date" id="edit-date" class="form-control rounded-3" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold"><?= __('expense_field_category') ?></label>
                    <select name="category_id" id="edit-category" class="form-select rounded-3" required>
                        <option value=""><?= __('expense_field_category_placeholder') ?></option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= h($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold"><?= __('expense_field_amount') ?></label>
                    <input type="number" name="amount" id="edit-amount" class="form-control rounded-3" min="1" step="any" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold"><?= __('expense_field_motive') ?></label>
                    <input type="text" name="motive" id="edit-motive" class="form-control rounded-3" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold"><?= __('expense_field_description') ?></label>
                    <textarea name="description" id="edit-desc" class="form-control rounded-3" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer border-top p-3 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"><?= __('save') ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Annuler une Dépense (Suppression logique) -->
<div class="modal fade" id="cancelExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="/expenses/cancel" method="POST" class="modal-content border-0 rounded-4 shadow-lg">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
            <input type="hidden" name="id" id="cancel-id">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= __('expense_cancel_title') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted-theme small"><?= __('expense_cancel_warning') ?></p>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-danger"><?= __('expense_cancel_reason_label') ?></label>
                    <input type="text" name="cancel_reason" class="form-control rounded-3 border-danger" placeholder="<?= __('expense_cancel_reason_placeholder') ?>" required>
                </div>
            </div>
            <div class="modal-footer border-top p-3 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal"><?= __('back') ?></button>
                <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm"><?= __('expense_cancel_confirm') ?></button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Remplissage modal modification
    const editBtns = document.querySelectorAll('.edit-expense-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit-id').value = this.dataset.id;
            document.getElementById('edit-date').value = this.dataset.date;
            document.getElementById('edit-category').value = this.dataset.category;
            document.getElementById('edit-amount').value = this.dataset.amount;
            document.getElementById('edit-motive').value = this.dataset.motive;
            document.getElementById('edit-desc').value = this.dataset.desc;
        });
    });

    // Remplissage modal annulation
    const cancelBtns = document.querySelectorAll('.cancel-expense-btn');
    cancelBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('cancel-id').value = this.dataset.id;
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
