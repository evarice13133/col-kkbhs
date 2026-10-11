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
#expensesListContainer .expenses-list-table td {
    vertical-align: middle;
}
#expensesListContainer .expenses-list-table .table-row-actions .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 42px;
}
#expensesListContainer .expenses-list-table .table-row-actions .btn i {
    font-size: 1.5rem !important;
    line-height: 1;
}
@media (max-width: 575.98px) {
    .expense-page-actions {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        width: 100%;
    }
    #expensePageActions .btn.expense-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 0;
        min-height: 48px !important;
        padding: 0.65rem 0.75rem;
        font-size: 0.82rem;
        line-height: 1.2;
        white-space: normal;
    }
    .expense-page-actions .expense-action i {
        flex: 0 0 auto;
        margin-right: 0.4rem !important;
    }
    .expense-page-actions .expense-action-primary {
        grid-column: 1 / -1;
    }
}
@media (max-width: 991.98px) {
    #expensesListContainer .expenses-table-wrapper {
        overflow: visible !important;
    }
    #expensesListContainer .expenses-list-table,
    #expensesListContainer .expenses-list-table tbody {
        display: block;
        width: 100%;
    }
    #expensesListContainer .expenses-list-table {
        min-width: 0 !important;
        table-layout: auto;
    }
    #expensesListContainer .expenses-list-table thead {
        display: none;
    }
    #expensesListContainer .expenses-list-table tbody {
        display: grid;
        gap: 0.75rem;
        padding: 0.75rem;
    }
    #expensesListContainer .expenses-list-table tbody tr.expense-row {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0 0.9rem;
        min-width: 0;
        padding: 0.5rem 0.85rem;
        border: 1px solid var(--border-color, #e2e8f0);
        border-radius: 12px;
        background: var(--bg-card, #fff);
    }
    #expensesListContainer .expenses-list-table tbody tr.expense-row td {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: center;
        min-width: 0;
        width: 100%;
        gap: 0.25rem;
        padding: 0.7rem 0.15rem !important;
        border: 0;
        text-align: left !important;
        font-size: 0.9rem;
    }
    #expensesListContainer .expenses-list-table tbody tr.expense-row td::before {
        content: attr(data-label);
        margin-bottom: 0.1rem;
        color: var(--text-muted, #64748b);
        font-size: 0.76rem;
        font-weight: 700;
        line-height: 1.25;
        text-transform: uppercase;
    }
    #expensesListContainer .expenses-list-table tbody tr.expense-row td.col-reference {
        grid-column: 1 / -1;
        padding: 0.6rem 0 0.75rem !important;
        border-bottom: 1px solid var(--border-color, #e2e8f0);
    }
    #expensesListContainer .expenses-list-table tbody tr.expense-row td.col-reference::before {
        content: none;
    }
    #expensesListContainer .expenses-list-table tbody tr.expense-row td.col-motive,
    #expensesListContainer .expenses-list-table tbody tr.expense-row td.col-actions {
        grid-column: 1 / -1;
    }
    #expensesListContainer .expenses-list-table tbody tr.expense-row td.col-motive {
        border-top: 1px solid var(--border-color, #e2e8f0);
    }
    #expensesListContainer .expenses-list-table tbody tr.expense-row td.col-amount {
        color: var(--primary-color, #7c3aed);
        font-size: 1rem;
        font-weight: 800;
    }
    #expensesListContainer .expenses-list-table tbody tr.expense-row td.col-amount .small {
        font-size: 0.75rem !important;
    }
    #expensesListContainer .expenses-list-table tbody tr.expense-row td.col-category .badge,
    #expensesListContainer .expenses-list-table tbody tr.expense-row td.col-status .badge {
        max-width: 100%;
        white-space: normal;
        overflow-wrap: anywhere;
        text-align: left;
    }
    #expensesListContainer .expenses-list-table tbody tr.expense-row td.col-actions {
        flex-direction: row;
        justify-content: flex-end;
        gap: 0.5rem;
        padding-top: 0.5rem !important;
        border-top: 1px solid var(--border-color, #e2e8f0);
    }
    #expensesListContainer .expenses-list-table tbody tr.expense-row td.col-actions::before {
        margin: 0 auto 0 0;
    }
    #expensesListContainer .expenses-list-table .table-row-actions,
    #expensesListContainer .expenses-list-table .table-row-actions .btn {
        visibility: visible !important;
        opacity: 1 !important;
        transform: none;
    }
    #expensesListContainer .expenses-list-table .table-row-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
    }
    #expensesListContainer .expenses-list-table tbody tr.expense-empty-row {
        display: grid !important;
        grid-template-columns: minmax(0, 1fr);
        padding: 0;
        border: 0;
        background: transparent;
    }
    #expensesListContainer .expenses-list-table tbody tr.expense-empty-row td {
        display: block !important;
        text-align: center !important;
    }
}
@media (max-width: 359.98px) {
    #expensesListContainer .expenses-list-table tbody tr.expense-row {
        grid-template-columns: minmax(0, 1fr);
    }
    #expensesListContainer .expenses-list-table tbody tr.expense-row td.col-reference,
    #expensesListContainer .expenses-list-table tbody tr.expense-row td.col-motive,
    #expensesListContainer .expenses-list-table tbody tr.expense-row td.col-actions {
        grid-column: 1;
    }
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
        <div class="d-flex gap-2 expense-page-actions" id="expensePageActions">
            <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm expense-action expense-action-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <i class="bi bi-plus-circle-fill me-2"></i> <?= __('expense_register') ?>
            </button>
            <a href="/expenses/categories" class="btn btn-outline-secondary rounded-pill px-4 fw-bold expense-action">
                <i class="bi bi-tags-fill me-2"></i> <?= __('expense_categories') ?>
            </a>
            <a href="/expenses/print?<?= http_build_query($_GET) ?>" target="_blank" class="btn btn-outline-danger rounded-pill px-4 fw-bold expense-action">
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
    <div class="modern-card border-0 shadow-sm overflow-hidden" id="expensesListContainer">
        <div class="table-responsive expenses-table-wrapper">
            <table class="table-modern expenses-list-table">
                <thead>
                    <tr>
                        <th class="ps-4"><?= __('expense_th_reference') ?></th>
                        <th><?= __('expense_th_date') ?></th>
                        <th><?= __('expense_th_category') ?></th>
                        <th><?= __('expense_th_motive') ?></th>
                        <th class="text-end"><?= __('expense_th_amount') ?></th>
                        <th><?= __('expense_th_disbursed_by') ?></th>
                        <th><?= __('expense_th_created_by') ?></th>
                        <th><?= __('expense_th_status') ?></th>
                        <th class="pe-4 text-center"><?= __('expense_th_actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expenses)): ?>
                        <tr class="expense-empty-row">
                            <td colspan="9" class="text-center py-5 text-muted">
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
                            <tr class="expense-row <?= $rowClass ?>">
                                <td class="ps-4 fw-bold col-reference" data-label="<?= h(__('expense_th_reference')) ?>"><code class="small text-primary"><?= h($e['reference']) ?></code></td>
                                <td class="col-date" data-label="<?= h(__('expense_th_date')) ?>"><?= date('d/m/Y', strtotime($e['expense_date'])) ?></td>
                                <td class="col-category" data-label="<?= h(__('expense_th_category')) ?>">
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2.5 py-1 rounded-pill">
                                        <?= h($e['category_name']) ?>
                                    </span>
                                </td>
                                <td class="col-motive <?= $textClass ?>" data-label="<?= h(__('expense_th_motive')) ?>"><?= h($e['motive']) ?></td>
                                <td class="text-end fw-black col-amount <?= $textClass ?>" data-label="<?= h(__('expense_th_amount')) ?>">
                                    <?= number_format($e['amount'], 0, ',', ' ') ?> <span class="small fw-normal text-muted" style="font-size: 11px;">FCFA</span>
                                </td>
                                <td class="col-disbursed-by" data-label="<?= h(__('expense_th_disbursed_by')) ?>">
                                    <?php if (!empty($e['disbursements'])): ?>
                                        <?php foreach ($e['disbursements'] as $disbursement): ?>
                                            <div class="small">
                                                <span class="fw-semibold"><?= h($disbursement['user_name']) ?></span>
                                                <span class="text-muted"><?= number_format((float)$disbursement['amount'], 0, ',', ' ') ?> FCFA</span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted small"><?= __('expense_disbursement_not_recorded') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="col-created-by" data-label="<?= h(__('expense_th_created_by')) ?>"><span class="text-main-theme fw-medium"><?= h($e['user_name']) ?></span></td>
                                <td class="col-status" data-label="<?= h(__('expense_th_status')) ?>">
                                    <?php if ($e['status'] === 'active'): ?>
                                        <span class="badge-premium badge-premium-success"><i class="bi bi-check-circle-fill"></i> <?= __('expense_status_active') ?></span>
                                    <?php else: ?>
                                        <span class="badge-premium-cancelled" data-bs-toggle="tooltip" title="<?= h($e['cancel_reason']) ?>"><i class="bi bi-x-circle-fill"></i> <?= __('expense_status_cancelled') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-center col-actions" data-label="<?= h(__('expense_th_actions')) ?>">
                                    <div class="d-flex gap-1 justify-content-center table-row-actions">
                                        <?php if (!$isCancelled): ?>
                                            <button type="button" class="btn btn-sm btn-action-modern text-primary edit-expense-btn" 
                                                data-id="<?= $e['id'] ?>"
                                                data-category="<?= $e['category_id'] ?>"
                                                data-date="<?= $e['expense_date'] ?>"
                                                data-amount="<?= $e['amount'] ?>"
                                                data-motive="<?= h($e['motive']) ?>"
                                                data-desc="<?= h($e['description'] ?? '') ?>"
                                                data-disbursements="<?= h(json_encode($e['disbursements'], JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>"
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
                    <input type="number" name="amount" class="form-control rounded-3" id="add-amount" placeholder="Ex: 25000" min="0.01" step="0.01" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold"><?= __('expense_field_disbursed_by') ?></label>
                    <p class="text-muted small mb-2"><?= __('expense_disbursement_split_help') ?></p>
                    <div class="vstack gap-2" data-disbursement-rows></div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-add-disbursement>
                        <i class="bi bi-person-plus me-1"></i><?= __('expense_disbursement_add_person') ?>
                    </button>
                    <div class="small mt-2" data-disbursement-total aria-live="polite"></div>
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
                    <input type="number" name="amount" id="edit-amount" class="form-control rounded-3" min="0.01" step="0.01" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold"><?= __('expense_field_disbursed_by') ?></label>
                    <p class="text-muted small mb-2"><?= __('expense_disbursement_split_help') ?></p>
                    <div class="vstack gap-2" data-disbursement-rows></div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-add-disbursement>
                        <i class="bi bi-person-plus me-1"></i><?= __('expense_disbursement_add_person') ?>
                    </button>
                    <div class="small mt-2" data-disbursement-total aria-live="polite"></div>
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

<template id="expenseDisbursementOptions">
    <option value=""><?= h(__('expense_disbursement_select_person')) ?></option>
    <?php foreach ($disbursingUsers as $disbursingUser): ?>
        <option value="<?= (int)$disbursingUser['id'] ?>"><?= h($disbursingUser['prenom'] . ' ' . $disbursingUser['nom']) ?></option>
    <?php endforeach; ?>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const disbursementOptions = document.getElementById('expenseDisbursementOptions').innerHTML;
    const disbursementLabels = {
        person: <?= json_encode((string)__('expense_disbursement_person'), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
        amount: <?= json_encode((string)__('expense_disbursement_amount'), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
        remove: <?= json_encode((string)__('expense_disbursement_remove_person'), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
        total: <?= json_encode((string)__('expense_disbursement_total_label'), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
        totalMismatch: <?= json_encode((string)__('expense_disbursement_total_mismatch'), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
        duplicate: <?= json_encode((string)__('expense_disbursement_duplicate'), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) ?>
    };

    function formatExpenseAmount(cents) {
        return new Intl.NumberFormat(document.documentElement.lang || 'fr-FR', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        }).format(cents / 100);
    }

    function refreshDisbursementTotal(form) {
        const totalNode = form.querySelector('[data-disbursement-total]');
        const expenseAmount = Math.round((parseFloat(form.querySelector('[name="amount"]').value) || 0) * 100);
        const allocated = Array.from(form.querySelectorAll('[data-disbursement-rows] input[type="number"]'))
            .reduce((sum, input) => sum + Math.round((parseFloat(input.value) || 0) * 100), 0);
        const matches = expenseAmount > 0 && allocated === expenseAmount;
        const untouched = expenseAmount === 0 && allocated === 0;
        totalNode.className = `small mt-2 ${untouched ? 'text-muted' : matches ? 'text-success' : 'text-danger'}`;
        totalNode.textContent = untouched
            ? disbursementLabels.total
            : matches
                ? `${disbursementLabels.total} : ${formatExpenseAmount(allocated)} / ${formatExpenseAmount(expenseAmount)} FCFA`
                : disbursementLabels.totalMismatch
                    .replace(':amount', formatExpenseAmount(expenseAmount))
                    .replace(':allocated', formatExpenseAmount(allocated));
        return { expenseAmount, allocated, matches };
    }

    function addDisbursementRow(form, initial = {}) {
        const container = form.querySelector('[data-disbursement-rows]');
        const rowIndex = Number(form.dataset.nextDisbursementRow || 0);
        form.dataset.nextDisbursementRow = String(rowIndex + 1);
        const row = document.createElement('div');
        row.className = 'row g-2 align-items-end expense-disbursement-row';

        const personColumn = document.createElement('div');
        personColumn.className = 'col-12 col-sm-6';
        const personLabel = document.createElement('label');
        personLabel.className = 'form-label small mb-1';
        personLabel.textContent = disbursementLabels.person;
        personLabel.htmlFor = `${form.closest('.modal').id}-disbursement-person-${rowIndex}`;
        const personSelect = document.createElement('select');
        personSelect.className = 'form-select form-select-sm';
        personSelect.id = personLabel.htmlFor;
        personSelect.name = `disbursements[${rowIndex}][user_id]`;
        personSelect.required = true;
        personSelect.innerHTML = disbursementOptions;
        personSelect.value = initial.user_id || '';
        personColumn.append(personLabel, personSelect);

        const amountColumn = document.createElement('div');
        amountColumn.className = 'col-8 col-sm-4';
        const amountLabel = document.createElement('label');
        amountLabel.className = 'form-label small mb-1';
        amountLabel.textContent = disbursementLabels.amount;
        amountLabel.htmlFor = `${form.closest('.modal').id}-disbursement-amount-${rowIndex}`;
        const amountInput = document.createElement('input');
        amountInput.className = 'form-control form-control-sm';
        amountInput.id = amountLabel.htmlFor;
        amountInput.type = 'number';
        amountInput.name = `disbursements[${rowIndex}][amount]`;
        amountInput.min = '0.01';
        amountInput.step = '0.01';
        amountInput.required = true;
        amountInput.value = initial.amount || '';
        amountColumn.append(amountLabel, amountInput);

        const removeColumn = document.createElement('div');
        removeColumn.className = 'col-4 col-sm-2';
        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'btn btn-sm btn-outline-danger w-100';
        removeButton.setAttribute('aria-label', disbursementLabels.remove);
        removeButton.innerHTML = '<i class="bi bi-trash"></i>';
        removeButton.addEventListener('click', function () {
            row.remove();
            updateDisbursementRemoveButtons(form);
            refreshDisbursementTotal(form);
        });
        removeColumn.appendChild(removeButton);
        row.append(personColumn, amountColumn, removeColumn);
        container.appendChild(row);
        personSelect.addEventListener('change', () => refreshDisbursementTotal(form));
        amountInput.addEventListener('input', () => refreshDisbursementTotal(form));
        updateDisbursementRemoveButtons(form);
        refreshDisbursementTotal(form);
    }

    function updateDisbursementRemoveButtons(form) {
        const rows = form.querySelectorAll('.expense-disbursement-row');
        rows.forEach(row => {
            row.querySelector('button').disabled = rows.length <= 1;
        });
    }

    function setDisbursementRows(form, disbursements = []) {
        const container = form.querySelector('[data-disbursement-rows]');
        container.replaceChildren();
        form.dataset.nextDisbursementRow = '0';
        if (disbursements.length === 0) {
            addDisbursementRow(form);
            return;
        }
        disbursements.forEach(disbursement => addDisbursementRow(form, disbursement));
    }

    document.querySelectorAll('#addExpenseModal form, #editExpenseModal form').forEach(form => {
        form.querySelector('[name="amount"]').addEventListener('input', () => refreshDisbursementTotal(form));
        form.querySelector('[data-add-disbursement]').addEventListener('click', () => addDisbursementRow(form));
        form.addEventListener('submit', function (event) {
            const selectedUsers = Array.from(form.querySelectorAll('[data-disbursement-rows] select'))
                .map(select => select.value)
                .filter(Boolean);
            const hasDuplicateUsers = new Set(selectedUsers).size !== selectedUsers.length;
            const { matches } = refreshDisbursementTotal(form);
            if (hasDuplicateUsers || !matches) {
                event.preventDefault();
                if (hasDuplicateUsers) {
                    form.querySelector('[data-disbursement-total]').textContent = disbursementLabels.duplicate;
                }
            }
        });
        setDisbursementRows(form);
    });

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
            const editForm = document.querySelector('#editExpenseModal form');
            setDisbursementRows(editForm, JSON.parse(this.dataset.disbursements || '[]'));
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
