<?php
$title = __('financial_dashboard_title');
ob_start();
?>
<style>
.hover-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.hover-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 18px rgba(0,0,0,0.08) !important;
}
.kpi-section-title {
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    font-weight: 800;
}
</style>

<div class="animate-fade-in container-fluid py-4">

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h1 class="fw-black text-main-theme mb-1 fs-4 d-flex align-items-center gap-2">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-success bg-opacity-10 text-success p-2" style="width:40px;height:40px;">
                    <i class="bi bi-wallet2 fs-5"></i>
                </span>
                <?= __('financial_dashboard_title') ?>
            </h1>
            <p class="text-muted-theme mb-0"><?= __('financial_dashboard_subtitle') ?></p>
        </div>
        <div class="d-flex flex-column flex-md-row gap-2 ms-md-auto mt-3 mt-md-0 align-items-stretch align-items-md-center justify-content-md-end w-100 w-md-auto">
            <!-- <a href="/school_fees/versements" class="btn btn-primary rounded-pill px-3 px-md-4 fw-semibold shadow-sm text-center text-nowrap">
                <i class="bi bi-cash-coin me-2"></i><?= __('versements_menu') ?>
            </a> -->
            <a href="/payments" class="btn btn-success rounded-pill px-3 px-md-4 fw-semibold shadow-sm text-center text-nowrap">
                <i class="bi bi-plus-circle me-2"></i><?= __('payments_menu') ?>
            </a>
            <!-- Nouveau raccourci -->
            <button type="button" class="btn btn-info text-white rounded-pill px-3 px-md-4 fw-semibold shadow-sm text-center text-nowrap" data-bs-toggle="modal" data-bs-target="#newVersementModal">
                <i class="bi bi-wallet-fill me-2"></i><?= __('new_versement') ?>
            </button>
            <a href="/financial-history" class="btn btn-outline-secondary rounded-pill px-3 px-md-4 fw-semibold text-center text-nowrap">
                <i class="bi bi-journal-text me-2"></i><?= __('financial_history') ?>
            </a>
        </div>
    </div>
    
    <!-- Modal Nouveau Versement Raccourci (Redirect to page with hash or implement directly, but since we are in dashboard, let's just make it a link to the page that opens the modal) -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const newVersementBtn = document.querySelector('[data-bs-target="#newVersementModal"]');
        if (newVersementBtn) {
            newVersementBtn.addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = '/school_fees/versements#new';
            });
        }
    });
    </script>
    <!-- Section: Situation de la Caisse (Recettes) -->
    <div class="mb-4">
        <div class="kpi-section-title text-primary mb-3 d-flex align-items-center gap-2">
            <span class="d-inline-block rounded-circle bg-primary bg-opacity-10 p-1"></span>
            <?= __('total_general_collected') ?> & Recettes
        </div>
        <div class="row g-4">
            <!-- Recettes Totales de la Caisse -->
            <div class="col-sm-6 col-xl-3">
                <div class="modern-card hover-card border-0 shadow-sm p-4 h-100 position-relative overflow-hidden">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="kpi-icon bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <i class="bi bi-piggy-bank fs-4"></i>
                        </div>
                    </div>
                    <div class="fw-black text-main-theme fs-4 mb-1"><?= number_format($totalGeneralCollected, 0, ',', ' ') ?> <small class="fs-6 fw-normal text-muted">FCFA</small></div>
                    <div class="text-muted-theme small fw-semibold"><?= __('total_general_collected') ?></div>
                    <div class="position-absolute bottom-0 start-0 w-100" style="height:4px; background: linear-gradient(90deg,#8b5cf6,#6366f1); border-radius:0 0 12px 12px;"></div>
                </div>
            </div>
            <!-- Frais de Scolarité Encaissés -->
            <div class="col-sm-6 col-xl-3">
                <div class="modern-card hover-card border-0 shadow-sm p-4 h-100 position-relative overflow-hidden">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="kpi-icon bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <i class="bi bi-cash-stack fs-4"></i>
                        </div>
                        <span class="badge bg-success-subtle text-success rounded-pill fw-bold px-3 py-2"><?= number_format($collectionRate, 1) ?>%</span>
                    </div>
                    <div class="fw-black text-main-theme fs-4 mb-1"><?= number_format($totalTuitionCollected, 0, ',', ' ') ?> <small class="fs-6 fw-normal text-muted">FCFA</small></div>
                    <div class="text-muted-theme small fw-semibold"><?= __('total_tuition_collected') ?></div>
                    <div class="position-absolute bottom-0 start-0 w-100" style="height:4px; background: linear-gradient(90deg,#22c55e,#10b981); border-radius:0 0 12px 12px;"></div>
                </div>
            </div>
            <!-- Frais d'Inscription Encaissés -->
            <div class="col-sm-6 col-xl-3">
                <div class="modern-card hover-card border-0 shadow-sm p-4 h-100 position-relative overflow-hidden">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="kpi-icon bg-info bg-opacity-10 text-info rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <i class="bi bi-journal-check fs-4"></i>
                        </div>
                    </div>
                    <div class="fw-black text-main-theme fs-4 mb-1"><?= number_format($totalRegistrationCollected, 0, ',', ' ') ?> <small class="fs-6 fw-normal text-muted">FCFA</small></div>
                    <div class="text-muted-theme small fw-semibold"><?= __('total_registration_collected') ?></div>
                    <div class="position-absolute bottom-0 start-0 w-100" style="height:4px; background: linear-gradient(90deg,#06b6d4,#0ea5e9); border-radius:0 0 12px 12px;"></div>
                </div>
            </div>
            <!-- Scolarité Attendue -->
            <div class="col-sm-6 col-xl-3">
                <div class="modern-card hover-card border-0 shadow-sm p-4 h-100 position-relative overflow-hidden">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="kpi-icon bg-secondary bg-opacity-10 text-secondary rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <i class="bi bi-graph-up-arrow fs-4"></i>
                        </div>
                    </div>
                    <div class="fw-black text-main-theme fs-4 mb-1"><?= number_format($totalExpected, 0, ',', ' ') ?> <small class="fs-6 fw-normal text-muted">FCFA</small></div>
                    <div class="text-muted-theme small fw-semibold"><?= __('total_expected') ?></div>
                    <div class="position-absolute bottom-0 start-0 w-100" style="height:4px; background: linear-gradient(90deg,#64748b,#475569); border-radius:0 0 12px 12px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Situation des Inscriptions -->
    <div class="mb-4">
        <div class="kpi-section-title text-success mb-3 d-flex align-items-center gap-2">
            <span class="d-inline-block rounded-circle bg-success bg-opacity-10 p-1"></span>
            Situation des Inscriptions & Rentrée Scolaire
        </div>
        <div class="row g-4">
            <!-- Élèves Déjà Inscrits -->
            <div class="col-sm-6 col-xl-3">
                <div class="modern-card hover-card border-0 shadow-sm p-4 h-100 position-relative overflow-hidden">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="kpi-icon bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <i class="bi bi-person-check fs-4"></i>
                        </div>
                    </div>
                    <div class="fw-black text-main-theme fs-4 mb-1"><?= number_format($totalEnrolled) ?></div>
                    <div class="text-muted-theme small fw-semibold"><?= __('enrolled_students') ?></div>
                    <div class="position-absolute bottom-0 start-0 w-100" style="height:4px; background: linear-gradient(90deg,#10b981,#059669); border-radius:0 0 12px 12px;"></div>
                </div>
            </div>
            <!-- Élèves Non Inscrits -->
            <div class="col-sm-6 col-xl-3">
                <div class="modern-card hover-card border-0 shadow-sm p-4 h-100 position-relative overflow-hidden">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="kpi-icon bg-danger bg-opacity-10 text-danger rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <i class="bi bi-person-x fs-4"></i>
                        </div>
                    </div>
                    <div class="fw-black text-main-theme fs-4 mb-1"><?= number_format($totalNonEnrolled) ?></div>
                    <div class="text-muted-theme small fw-semibold"><?= __('non_enrolled_students') ?></div>
                    <div class="position-absolute bottom-0 start-0 w-100" style="height:4px; background: linear-gradient(90deg,#ef4444,#dc2626); border-radius:0 0 12px 12px;"></div>
                </div>
            </div>
            <!-- Taux d'inscription global -->
            <div class="col-sm-6 col-xl-3">
                <div class="modern-card hover-card border-0 shadow-sm p-4 h-100 position-relative overflow-hidden">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="kpi-icon bg-warning bg-opacity-10 text-warning rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <i class="bi bi-percent fs-4"></i>
                        </div>
                    </div>
                    <?php 
                    $registrationRate = $totalStudents > 0 ? round(($totalEnrolled / $totalStudents) * 100, 1) : 0;
                    ?>
                    <div class="fw-black text-main-theme fs-4 mb-1"><?= $registrationRate ?>%</div>
                    <div class="text-muted-theme small fw-semibold"><?= __('registration_rate') ?></div>
                    <div class="position-absolute bottom-0 start-0 w-100" style="height:4px; background: linear-gradient(90deg,#f59e0b,#d97706); border-radius:0 0 12px 12px;"></div>
                </div>
            </div>
            <!-- Effectif Total Actif -->
            <div class="col-sm-6 col-xl-3">
                <div class="modern-card hover-card border-0 shadow-sm p-4 h-100 position-relative overflow-hidden">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="kpi-icon bg-info bg-opacity-10 text-info rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                    </div>
                    <div class="fw-black text-main-theme fs-4 mb-1"><?= number_format($totalStudents) ?></div>
                    <div class="text-muted-theme small fw-semibold"><?= __('active_students') ?></div>
                    <div class="position-absolute bottom-0 start-0 w-100" style="height:4px; background: linear-gradient(90deg,#0ea5e9,#0284c7); border-radius:0 0 12px 12px;"></div>
                </div>
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

    <!-- Class enrollment stats breakdown -->
    <div class="row g-4 mb-4 animate-fade-in">
        <div class="col-12">
            <div class="modern-card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 px-4 pt-4 pb-0 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h6 class="fw-black text-main-theme mb-1"><?= __('class_registration_stats') ?></h6>
                        <p class="text-muted-theme small mb-0">Statut des inscriptions par classe (Politique : <?= htmlspecialchars(ucfirst($policy)) ?>)</p>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="border-bottom border-theme-light">
                                    <th class="ps-4 py-3 fw-semibold text-muted-theme small text-uppercase"><?= __('class_name_header') ?? 'Classe' ?></th>
                                    <th class="py-3 fw-semibold text-muted-theme text-center small text-uppercase"><?= __('total_students_header') ?? 'Total Élèves' ?></th>
                                    <th class="py-3 fw-semibold text-muted-theme text-center small text-uppercase"><?= __('enrolled_count_header') ?? 'Élèves Inscrits' ?></th>
                                    <th class="py-3 fw-semibold text-muted-theme text-center small text-uppercase"><?= __('non_enrolled_count_header') ?? 'Élèves Non Inscrits' ?></th>
                                    <th class="py-3 fw-semibold text-muted-theme small text-uppercase"><?= __('registration_rate') ?? 'Taux d\'Inscription' ?></th>
                                    <th class="pe-4 py-3 fw-semibold text-muted-theme text-end small text-uppercase"><?= __('registration_revenue_header') ?? 'Frais Inscription Encaissés' ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($classRegistrationStats as $classStat): 
                                    $classRate = $classStat['total_students'] > 0 ? round(($classStat['enrolled_count'] / $classStat['total_students']) * 100) : 0;
                                    $progressColor = 'bg-danger';
                                    if ($classRate >= 80) {
                                        $progressColor = 'bg-success';
                                    } elseif ($classRate >= 50) {
                                        $progressColor = 'bg-warning';
                                    }
                                ?>
                                    <tr class="border-bottom border-theme-light">
                                        <td class="ps-4 py-3 fw-bold text-main-theme">
                                            <?= htmlspecialchars($classStat['class_name']) ?>
                                        </td>
                                        <td class="py-3 text-center text-main-theme fw-semibold">
                                            <?= number_format($classStat['total_students']) ?>
                                        </td>
                                        <td class="py-3 text-center">
                                            <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1.5 fw-semibold">
                                                <?= number_format($classStat['enrolled_count']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3 text-center">
                                            <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1.5 fw-semibold">
                                                <?= number_format($classStat['non_enrolled_count']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3" style="min-width: 150px;">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 6px; background-color: var(--border-color);">
                                                    <div class="progress-bar <?= $progressColor ?>" role="progressbar" style="width: <?= $classRate ?>%" aria-valuenow="<?= $classRate ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <span class="small fw-bold text-main-theme"><?= $classRate ?>%</span>
                                            </div>
                                        </td>
                                        <td class="pe-4 py-3 text-end fw-bold text-success">
                                            <?= number_format((float)$classStat['total_registration_collected'], 0, ',', ' ') ?> <small class="fw-normal text-muted">FCFA</small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
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
                                            <td class="fw-bold text-success">
                                                <div><?= number_format((float)$payment['amount'], 0, ',', ' ') ?> <small class="fw-normal text-muted">FCFA</small></div>
                                                <div class="mt-1">
                                                    <?php if (($payment['type'] ?? '') === 'inscription'): ?>
                                                        <span class="badge rounded-pill text-uppercase px-2 py-1" style="font-size: 0.65rem; background-color: rgba(59, 130, 246, 0.1); color: #2563eb;">
                                                            <i class="bi bi-journal-check me-1"></i>Inscription
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge rounded-pill text-uppercase px-2 py-1" style="font-size: 0.65rem; background-color: rgba(16, 185, 129, 0.1); color: #059669;">
                                                            <i class="bi bi-cash-coin me-1"></i>Scolarité
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
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
