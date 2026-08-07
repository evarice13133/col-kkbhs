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
        <div class="table-responsive">
            <table class="table-modern">
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
                        <tr>
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
                            <tr class="student-row">
                                <td class="ps-4">
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
                                <td><code class="small text-secondary"><?= h($s['matricule']) ?></code></td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 rounded-pill fw-medium" style="font-size: 0.7rem;">
                                        <i class="bi bi-door-open-fill me-1"></i><?= h($s['classe_nom'] ?: __('no_class')) ?>
                                    </span>
                                </td>
                                <td class="text-end text-muted"><?= number_format($s['frais_scolarite_brut'], 0, '.', ' ') ?> <span style="font-size: 0.7rem;">FCFA</span></td>
                                <td class="text-end">
                                    <?php if ($s['total_reductions'] > 0): ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10 px-2 py-0.5 rounded small fw-bold" style="font-size: 0.7rem;">
                                            -<?= number_format($s['total_reductions'], 0, '.', ' ') ?> <span style="font-size: 0.6rem; font-weight: normal;">FCFA</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted opacity-50">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($s['total_bourses'] > 0): ?>
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-10 px-2 py-0.5 rounded small fw-bold" style="font-size: 0.7rem;">
                                            -<?= number_format($s['total_bourses'], 0, '.', ' ') ?> <span style="font-size: 0.6rem; font-weight: normal;">FCFA</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted opacity-50">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-primary fw-black"><?= number_format($net, 0, '.', ' ') ?> <span style="font-size: 0.75rem; font-weight: normal;">FCFA</span></td>
                                <td class="text-end text-success fw-bold"><?= number_format($paye, 0, '.', ' ') ?> <span style="font-size: 0.75rem; font-weight: normal;">FCFA</span></td>
                                <td class="text-end">
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
                                <td class="pe-4 text-center">
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
