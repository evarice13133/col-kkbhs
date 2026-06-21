<?php
$title = __('financial_dashboard_title');
ob_start();
?>

<div class="animate-fade-in container-fluid py-4">

    <!-- Page Header -->
    <div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h1 class="fw-black text-main-theme mb-1 fs-4 d-flex align-items-center gap-2">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-success bg-opacity-10 text-success p-2" style="width:40px;height:40px;">
                    <i class="bi bi-wallet2 fs-5"></i>
                </span>
                <?= __('financial_dashboard_title') ?>
            </h1>
            <p class="text-muted-theme mb-0"><?= __('financial_dashboard_subtitle') ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="/payments" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm">
                <i class="bi bi-plus-circle me-2"></i><?= __('payments_menu') ?>
            </a>
            <a href="/financial-history" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">
                <i class="bi bi-journal-text me-2"></i><?= __('financial_history') ?>
            </a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Encaissé -->
        <div class="col-sm-6 col-xl-3">
            <div class="modern-card border-0 shadow-sm p-4 h-100 position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="kpi-icon bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="bi bi-cash-stack fs-4"></i>
                    </div>
                    <span class="badge bg-success-subtle text-success rounded-pill fw-bold px-3 py-2"><?= number_format($collectionRate, 1) ?>%</span>
                </div>
                <div class="fw-black text-main-theme fs-4 mb-1"><?= number_format($totalCollected, 0, ',', ' ') ?> <small class="fs-6 fw-normal text-muted">FCFA</small></div>
                <div class="text-muted-theme small fw-semibold"><?= __('total_collected') ?></div>
                <div class="position-absolute bottom-0 start-0 w-100" style="height:4px; background: linear-gradient(90deg,#22c55e,#16a34a); border-radius:0 0 12px 12px;"></div>
            </div>
        </div>
        <!-- Total Attendu -->
        <div class="col-sm-6 col-xl-3">
            <div class="modern-card border-0 shadow-sm p-4 h-100 position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="kpi-icon bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="bi bi-graph-up-arrow fs-4"></i>
                    </div>
                </div>
                <div class="fw-black text-main-theme fs-4 mb-1"><?= number_format($totalExpected, 0, ',', ' ') ?> <small class="fs-6 fw-normal text-muted">FCFA</small></div>
                <div class="text-muted-theme small fw-semibold"><?= __('total_expected') ?></div>
                <div class="position-absolute bottom-0 start-0 w-100" style="height:4px; background: linear-gradient(90deg,#3b82f6,#6366f1); border-radius:0 0 12px 12px;"></div>
            </div>
        </div>
        <!-- Élèves insolvables -->
        <div class="col-sm-6 col-xl-3">
            <div class="modern-card border-0 shadow-sm p-4 h-100 position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="kpi-icon bg-danger bg-opacity-10 text-danger rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="bi bi-exclamation-triangle fs-4"></i>
                    </div>
                    <a href="/school_fees/insolvables" class="btn btn-sm btn-danger-subtle text-danger fw-bold rounded-pill px-3">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="fw-black text-main-theme fs-4 mb-1"><?= number_format($totalInsolvent) ?></div>
                <div class="text-muted-theme small fw-semibold"><?= __('insolvent_students') ?></div>
                <div class="position-absolute bottom-0 start-0 w-100" style="height:4px; background: linear-gradient(90deg,#ef4444,#dc2626); border-radius:0 0 12px 12px;"></div>
            </div>
        </div>
        <!-- Élèves inscrits -->
        <div class="col-sm-6 col-xl-3">
            <div class="modern-card border-0 shadow-sm p-4 h-100 position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="kpi-icon bg-info bg-opacity-10 text-info rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="bi bi-people fs-4"></i>
                    </div>
                </div>
                <div class="fw-black text-main-theme fs-4 mb-1"><?= number_format($totalStudents) ?></div>
                <div class="text-muted-theme small fw-semibold"><?= __('students') ?></div>
                <div class="position-absolute bottom-0 start-0 w-100" style="height:4px; background: linear-gradient(90deg,#06b6d4,#0284c7); border-radius:0 0 12px 12px;"></div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Collection Rate Donut -->
        <div class="col-lg-4">
            <div class="modern-card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-black text-main-theme mb-4"><?= __('collection_rate') ?></h6>
                <div class="d-flex align-items-center justify-content-center" style="height:220px; position:relative;">
                    <canvas id="collectionRateChart"></canvas>
                    <div class="position-absolute text-center">
                        <div class="fw-black fs-3 text-success"><?= number_format($collectionRate, 1) ?>%</div>
                        <div class="small text-muted-theme"><?= __('collection_rate') ?></div>
                    </div>
                </div>
                <div class="d-flex justify-content-center gap-4 mt-3">
                    <div class="text-center">
                        <div class="small text-muted-theme"><?= __('total_collected') ?></div>
                        <div class="fw-bold text-success small"><?= number_format($totalCollected, 0, ',', ' ') ?></div>
                    </div>
                    <div class="text-center">
                        <div class="small text-muted-theme"><?= __('total_expected') ?></div>
                        <div class="fw-bold text-primary small"><?= number_format($totalExpected, 0, ',', ' ') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Evolution Chart -->
        <div class="col-lg-8">
            <div class="modern-card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-black text-main-theme mb-4"><?= __('monthly_evolution') ?></h6>
                <div style="height:220px;">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="row g-4">
        <div class="col-12">
            <div class="modern-card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 px-4 pt-4 pb-0 d-flex align-items-center justify-content-between">
                    <h6 class="fw-black text-main-theme mb-0"><?= __('recent_payments_title') ?></h6>
                    <a href="/payments" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold"><?= __('view_all') ?></a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentPayments)): ?>
                        <div class="text-center py-5 text-muted-theme">
                            <i class="bi bi-inbox fs-1 opacity-25 d-block mb-2"></i>
                            <p class="mb-0"><?= __('no_recent_payments') ?></p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="border-bottom border-theme-light">
                                        <th class="ps-4 py-3 fw-semibold text-muted-theme small text-uppercase"><?= __('student') ?></th>
                                        <th class="py-3 fw-semibold text-muted-theme small text-uppercase"><?= __('class') ?></th>
                                        <th class="py-3 fw-semibold text-muted-theme small text-uppercase"><?= __('amount') ?></th>
                                        <th class="py-3 fw-semibold text-muted-theme small text-uppercase"><?= __('payment_mode') ?></th>
                                        <th class="pe-4 py-3 fw-semibold text-muted-theme small text-uppercase"><?= __('date') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentPayments as $payment): ?>
                                        <tr class="border-bottom border-theme-light">
                                            <td class="ps-4 py-3">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar-circle-sm bg-success bg-opacity-10 text-success fw-bold rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px;height:36px;">
                                                        <?= strtoupper(substr($payment['student_name'], 0, 1)) ?>
                                                    </div>
                                                    <span class="fw-semibold text-main-theme small"><?= htmlspecialchars($payment['student_name']) ?></span>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-primary-subtle text-primary rounded-pill px-3"><?= htmlspecialchars($payment['class_nom']) ?></span></td>
                                            <td class="fw-bold text-success"><?= number_format((float)$payment['amount'], 0, ',', ' ') ?> <small class="fw-normal text-muted">FCFA</small></td>
                                            <td>
                                                <?php
                                                $methodIcons = ['especes' => 'bi-cash', 'mobile_money' => 'bi-phone', 'virement' => 'bi-bank', 'cheque' => 'bi-credit-card-2-front'];
                                                $methodLabels = ['especes' => 'Espèces', 'mobile_money' => 'Mobile Money', 'virement' => 'Virement', 'cheque' => 'Chèque'];
                                                $m = $payment['payment_method'] ?? '';
                                                ?>
                                                <span class="d-flex align-items-center gap-2 text-muted-theme small">
                                                    <i class="bi <?= $methodIcons[$m] ?? 'bi-question-circle' ?>"></i>
                                                    <?= $methodLabels[$m] ?? ucfirst($m) ?>
                                                </span>
                                            </td>
                                            <td class="pe-4 text-muted-theme small"><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    'use strict';
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';

    // --- Donut: Collection Rate ---
    const collected = <?= json_encode($totalCollected) ?>;
    const expected = <?= json_encode($totalExpected) ?>;
    const remaining = Math.max(0, expected - collected);
    const donutCtx = document.getElementById('collectionRateChart');
    if (donutCtx) {
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [collected, remaining],
                    backgroundColor: ['#22c55e', isDark ? '#1e293b' : '#e2e8f0'],
                    borderWidth: 0,
                    cutout: '78%'
                }]
            },
            options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false }, tooltip: { enabled: false } } }
        });
    }

    // --- Bar: Monthly Evolution ---
    const monthlyData = <?= json_encode($monthlyPayments) ?>;
    const monthLabels = monthlyData.map(r => {
        const [y, m] = r.month.split('-');
        return new Date(y, m - 1).toLocaleDateString('fr-FR', { month: 'short', year: '2-digit' });
    });
    const monthTotals = monthlyData.map(r => parseFloat(r.total));
    const barCtx = document.getElementById('monthlyChart');
    if (barCtx) {
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: '<?= __('total_collected') ?>',
                    data: monthTotals,
                    backgroundColor: 'rgba(59,130,246,0.75)',
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: textColor } },
                    y: { grid: { color: gridColor }, ticks: { color: textColor, callback: v => (v/1000).toFixed(0) + 'k' } }
                }
            }
        });
    }
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
