<?php
$title = __('payments_ledger');

// Calcul des statistiques cumulées
$totalExpected = 0.0;
$totalCollected = 0.0;
$totalDebt = 0.0;

foreach ($students as $s) {
    $totalExpected += (float)$s['scolarite_nette'];
    $totalCollected += (float)$s['total_paye'];
    $totalDebt += (float)$s['reste_a_payer'];
}
$collectionRate = $totalExpected > 0 ? ($totalCollected / $totalExpected) * 100 : 0;

ob_start();
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-black text-main-theme mb-0 fs-4"><?= __('payments_ledger_students') ?></h2>
            <p class="text-muted-theme small mb-0"><?= __('payments_ledger_subtitle') ?></p>
        </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4 animate-fade-in">
        <div class="col-6 col-md-3">
            <div class="modern-card kpi-card border-0 shadow-sm">
                <div class="kpi-icon-wrapper bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="kpi-value text-primary"><?= number_format($totalExpected, 0, '.', ' ') ?> <span class="fs-7 text-muted fw-normal">FCFA</span></div>
                <div class="kpi-label"><?= __('net_tuition_expected') ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="modern-card kpi-card shadow-sm border-success">
                <div class="kpi-icon-wrapper bg-success bg-opacity-10 text-success">
                    <i class="bi bi-cash-coin"></i>
                </div>
                <div class="kpi-value text-success"><?= number_format($totalCollected, 0, '.', ' ') ?> <span class="fs-7 text-muted fw-normal">FCFA</span></div>
                <div class="kpi-label"><?= __('total_already_collected') ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="modern-card kpi-card shadow-sm border-danger">
                <div class="kpi-icon-wrapper bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-exclamation-octagon"></i>
                </div>
                <div class="kpi-value text-danger"><?= number_format($totalDebt, 0, '.', ' ') ?> <span class="fs-7 text-muted fw-normal">FCFA</span></div>
                <div class="kpi-label"><?= __('remaining_global_to_recover') ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="modern-card kpi-card border-0 shadow-sm">
                <div class="kpi-icon-wrapper bg-info bg-opacity-10 text-info">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div class="kpi-value text-info"><?= number_format($collectionRate, 1) ?> %</div>
                <div class="kpi-label"><?= __('recovery_rate') ?></div>
            </div>
        </div>
    </div>

    <!-- BARRE D'ACTIONS COMPLÈTE : Style Floating Island -->
    <div class="d-flex justify-content-center mb-5">
        <div class="filter-island px-3 py-2 shadow-lg animate-slide-down" style="min-width: 95%;">
            <form method="GET" class="d-flex align-items-center gap-2 flex-wrap flex-md-nowrap filter-form w-100">
                <!-- Recherche -->
                <div class="flex-grow-1">
                    <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2" style="border: 1px solid var(--border-color);">
                        <span class="input-group-text border-0 bg-transparent text-primary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-0 bg-transparent shadow-none py-2 text-main"
                               value="<?= h($search) ?>" placeholder="<?= __('search_student_dots') ?>" style="min-width: 150px;">
                    </div>
                </div>

                <!-- Filtre Classe -->
                <div class="ms-md-2">
                    <select name="class_id" class="form-select border-0 bg-white bg-opacity-10 text-main shadow-none py-2 rounded-pill" onchange="this.form.submit()" style="min-width: 160px; font-size: 0.85rem; border: 1px solid var(--border-color) !important;">
                        <option value=""><?= __('all_classes') ?></option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $classId === (int)$c['id'] ? 'selected' : '' ?>><?= h($c['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtre Statut -->
                <div class="ms-md-2">
                    <select name="status" class="form-select border-0 bg-white bg-opacity-10 text-main shadow-none py-2 rounded-pill" onchange="this.form.submit()" style="min-width: 160px; font-size: 0.85rem; border: 1px solid var(--border-color) !important;">
                        <option value=""><?= __('all_status') ?></option>
                        <option value="paid" <?= $status === 'paid' ? 'selected' : '' ?>><?= __('fully_paid') ?></option>
                        <option value="debt" <?= $status === 'debt' ? 'selected' : '' ?>><?= __('tuition_due') ?></option>
                        <option value="unpaid" <?= $status === 'unpaid' ? 'selected' : '' ?>><?= __('no_payment') ?></option>
                    </select>
                </div>

                <!-- Action bouton de soumission et réinitialisation -->
                <div class="d-flex gap-2 align-items-center ps-md-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"><?= __('filter') ?></button>
                    <a href="/payments" class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center border-theme-light" style="width: 40px; height: 40px; border: 1px solid var(--border-color);" title="<?= __('reset') ?>">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in">
        <div class="table-responsive payments-table-wrapper">
            <table class="table-modern payments-list-table">
                <thead>
                    <tr>
                        <th class="ps-4"><?= __('grade_export_student') ?></th>
                        <th><?= __('matricule') ?></th>
                        <th><?= __('class') ?></th>
                        <th class="text-end"><?= __('col_tuition_gross') ?></th>
                        <th class="text-end"><?= __('discounts') ?></th>
                        <th class="text-end"><?= __('scholarships') ?></th>
                        <th class="text-end text-primary"><?= __('col_net_to_pay') ?></th>
                        <th class="text-end text-success"><?= __('col_total_paid') ?></th>
                        <th class="text-end"><?= __('col_remaining_to_pay') ?></th>
                        <th class="pe-4 text-center"><?= __('action_label') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr class="payment-empty-row">
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-info-circle fs-3 d-block mb-2 text-secondary"></i>
                                <?= __('no_student_found') ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $s): ?>
                            <?php 
                            $reste = (float)$s['reste_a_payer'];
                            $net = (float)$s['scolarite_nette'];
                            $paye = (float)$s['total_paye'];
                            ?>
                            <tr class="student-row payment-row">
                                <td class="ps-4 payment-identity-cell" data-label="<?= htmlspecialchars((string) __('grade_export_student'), ENT_QUOTES, 'UTF-8') ?>">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                             style="width: 36px; height: 36px; font-size: 1rem; border: 1px solid rgba(var(--primary-rgb), 0.2);">
                                            <?= strtoupper(substr((string) $s['nom'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-main-theme" style="font-size: 0.9rem;">
                                                <?= h($s['nom']) ?>
                                            </div>
                                            <div class="text-muted opacity-75" style="font-size: 0.75rem;">
                                                <?= h($s['prenom']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="payment-matricule-cell" data-label="<?= htmlspecialchars((string) __('matricule'), ENT_QUOTES, 'UTF-8') ?>"><code class="small text-secondary"><?= h($s['matricule']) ?></code></td>
                                <td data-label="<?= htmlspecialchars((string) __('class'), ENT_QUOTES, 'UTF-8') ?>">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 rounded-pill fw-medium" style="font-size: 0.7rem;">
                                        <i class="bi bi-door-open-fill me-1"></i><?= h($s['classe_nom'] ?: __('no_class')) ?>
                                    </span>
                                </td>
                                <td class="text-end text-muted payment-amount-cell" data-label="<?= htmlspecialchars((string) __('col_tuition_gross'), ENT_QUOTES, 'UTF-8') ?>"><?= number_format($s['frais_scolarite_brut'], 0, '.', ' ') ?> <span style="font-size: 0.7rem;">FCFA</span></td>
                                <td class="text-end payment-amount-cell" data-label="<?= htmlspecialchars((string) __('discounts'), ENT_QUOTES, 'UTF-8') ?>">
                                    <?php if ($s['total_reductions'] > 0): ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10 px-2 py-0.5 rounded small fw-bold" style="font-size: 0.7rem;">
                                            -<?= number_format($s['total_reductions'], 0, '.', ' ') ?> <span style="font-size: 0.6rem; font-weight: normal;">FCFA</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted opacity-50">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end payment-amount-cell" data-label="<?= htmlspecialchars((string) __('scholarships'), ENT_QUOTES, 'UTF-8') ?>">
                                    <?php if ($s['total_bourses'] > 0): ?>
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-10 px-2 py-0.5 rounded small fw-bold" style="font-size: 0.7rem;">
                                            -<?= number_format($s['total_bourses'], 0, '.', ' ') ?> <span style="font-size: 0.6rem; font-weight: normal;">FCFA</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted opacity-50">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-primary fw-black payment-net-cell" data-label="<?= htmlspecialchars((string) __('col_net_to_pay'), ENT_QUOTES, 'UTF-8') ?>"><?= number_format($net, 0, '.', ' ') ?> <span style="font-size: 0.75rem; font-weight: normal;">FCFA</span></td>
                                <td class="text-end text-success fw-bold payment-amount-cell" data-label="<?= htmlspecialchars((string) __('col_total_paid'), ENT_QUOTES, 'UTF-8') ?>"><?= number_format($paye, 0, '.', ' ') ?> <span style="font-size: 0.75rem; font-weight: normal;">FCFA</span></td>
                                <td class="text-end payment-amount-cell" data-label="<?= htmlspecialchars((string) __('col_remaining_to_pay'), ENT_QUOTES, 'UTF-8') ?>">
                                    <?php if ($reste > 0): ?>
                                        <span class="badge-premium badge-premium-danger" style="font-size: 0.72rem;">
                                            <i class="bi bi-hourglass-split"></i> <?= number_format($reste, 0, '.', ' ') ?> <span class="fw-normal" style="font-size: 0.6rem;">FCFA</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-premium badge-premium-success">
                                            <i class="bi bi-check-all"></i> <?= __('settled_badge') ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-center payment-action-cell" data-label="<?= htmlspecialchars((string) __('action_label'), ENT_QUOTES, 'UTF-8') ?>">
                                    <a href="/payments/student?id=<?= $s['id'] ?>" class="btn btn-sm btn-action-modern text-primary" title="<?= __('financial_sheet') ?>">
                                        <i class="bi bi-credit-card-2-back-fill fs-5"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<style>
    /* Floating Island Filters */
    .filter-island {
        background: rgba(var(--bg-card-rgb), 0.7);
        backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(var(--primary-rgb), 0.15);
        border-radius: 100px;
        min-width: 60%;
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .filter-island {
        background: rgba(30, 30, 45, 0.6);
        border-color: rgba(255, 255, 255, 0.08);
    }

    .filter-island:focus-within {
        border-color: var(--primary-color);
        box-shadow: 0 15px 35px -10px rgba(var(--primary-rgb), 0.25);
        transform: translateY(-2px);
    }

    /* Animations */
    .animate-slide-down {
        animation: slideDown 0.6s cubic-bezier(0.23, 1, 0.32, 1);
    }

    @keyframes slideDown {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @media (max-width: 767.98px) {
        .filter-island {
            border-radius: 24px;
            min-width: 100%;
            padding: 1rem !important;
        }
    }

    @media (min-width: 992px) {
        .payments-list-table thead th:first-child,
        .payments-list-table tbody td:first-child {
            width: 20%;
        }
    }

    @media (max-width: 991.98px) {
        .payments-table-wrapper {
            overflow: visible !important;
        }

        .payments-list-table,
        .payments-list-table tbody {
            display: block;
            width: 100%;
        }

        .payments-list-table {
            min-width: 0 !important;
            table-layout: auto;
        }

        .payments-list-table thead {
            display: none;
        }

        .payments-list-table tbody {
            display: grid;
            gap: 0.75rem;
            padding: 0.75rem;
        }

        .payments-list-table tbody tr.payment-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0 0.9rem;
            min-width: 0;
            padding: 0.5rem 0.85rem;
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 10px;
            background: var(--bg-card, #fff);
        }

        .payments-list-table tbody tr.payment-row td {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
            width: 100%;
            min-width: 0;
            padding: 0.65rem 0.1rem !important;
            border-radius: 0;
            text-align: left !important;
            font-size: 0.9rem;
        }

        .payments-list-table tbody tr.payment-row td::before {
            content: attr(data-label);
            margin-bottom: 0.2rem;
            color: var(--text-muted, #64748b);
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1.25;
            text-transform: uppercase;
        }

        .payments-list-table tbody tr.payment-row td.payment-identity-cell {
            grid-column: 1 / -1;
            padding: 0.6rem 0 0.75rem !important;
            border-bottom: 1px solid var(--border-color, #e2e8f0);
        }

        .payments-list-table tbody tr.payment-row td.payment-identity-cell::before {
            content: none;
        }

        .payments-list-table .payment-identity-cell .fw-bold {
            font-size: 1rem !important;
            overflow-wrap: anywhere;
        }

        .payments-list-table .payment-matricule-cell code {
            white-space: normal;
            overflow-wrap: anywhere;
            font-size: 0.78rem;
        }

        .payments-list-table .payment-amount-cell {
            font-size: 0.9rem;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .payments-list-table tbody tr.payment-row td.payment-net-cell {
            padding: 0.6rem 0.5rem !important;
            border-radius: 8px;
            background: rgba(var(--primary-rgb, 59, 130, 246), 0.08);
            font-size: 1rem;
        }

        .payments-list-table tbody tr.payment-row td.payment-action-cell {
            grid-column: 1 / -1;
            flex-direction: row;
            justify-content: flex-end;
            padding-top: 0.5rem !important;
            border-top: 1px solid var(--border-color, #e2e8f0);
        }

        .payments-list-table tbody tr.payment-row td.payment-action-cell::before {
            margin: 0 auto 0 0;
        }

        .payments-list-table .payment-action-cell .btn {
            height: 48px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .payments-list-table tbody tr.payment-empty-row {
            display: block;
            padding: 0;
            border: 0;
            background: transparent;
        }

        .payments-list-table tbody tr.payment-empty-row td {
            display: block;
            text-align: center !important;
        }
    }

    @media (max-width: 359.98px) {
        .payments-list-table tbody tr.payment-row {
            grid-template-columns: minmax(0, 1fr);
        }

        .payments-list-table tbody tr.payment-row td.payment-identity-cell,
        .payments-list-table tbody tr.payment-row td.payment-action-cell {
            grid-column: 1;
        }
    }

    .payments-list-table .payment-action-cell .btn i {
        display: flex;
        width: 100%;
        height: 100%;
        align-items: center;
        justify-content: center;
        font-size: 2.4rem !important;
        line-height: 1;
    }

    .payments-list-table .payment-action-cell .btn {
        width: 56px;
        min-width: 56px;
        height: 42px;
        padding: 0;
    }

    @media (max-width: 991.98px) {
        .payments-list-table .payment-action-cell .btn {
            height: 48px;
        }

        .payments-list-table .payment-action-cell .btn i {
            font-size: 2.7rem !important;
        }
    }

    /* Thème sombre pour le tableau */
    [data-theme="dark"] .modern-card {
        background: rgba(30, 30, 45, 0.6);
        border-color: rgba(255, 255, 255, 0.08);
    }

    [data-theme="dark"] .table-modern thead th {
        background: rgba(255, 255, 255, 0.05);
        color: #ffffff;
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }

    [data-theme="dark"] .table-modern tbody tr {
        border-bottom-color: rgba(255, 255, 255, 0.05);
    }

    [data-theme="dark"] .table-modern tbody td {
        color: #e0e0e0;
    }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
