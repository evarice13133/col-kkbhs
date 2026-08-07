<?php
$title = __('centre_financier');
ob_start();
?>
<style>
.hover-card {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.3s ease;
    border: 1px solid var(--border-color) !important;
}
.hover-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px -6px rgba(0, 0, 0, 0.08) !important;
}
.kpi-section-title {
    font-size: 0.78rem;
    letter-spacing: 0.06em;
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
                    <i class="bi bi-cash-coin fs-5"></i>
                </span>
                <?= __('centre_financier') ?>
            </h1>
            <p class="text-muted-theme mb-0"><?= __('financial_dashboard_subtitle') ?></p>
        </div>
    </div>

    <!-- Section 1: Encaissements par période -->
    <div class="mb-4">
        <div class="kpi-section-title text-primary mb-3">
            <i class="bi bi-calendar-range me-2"></i><?= __('collections_by_period') ?>
        </div>
        <div class="row g-4">
            <!-- Jour -->
            <div class="col-6 col-md-3">
                <div class="modern-card hover-card p-3 shadow-sm h-100 border-info">
                    <span class="text-muted-theme small fw-bold d-block mb-1"><?= __('today_label') ?></span>
                    <span class="h4 fw-black text-main-theme"><?= number_format($dailyCollections, 0, ',', ' ') ?> <span class="small font-normal text-muted" style="font-size: 11px;">FCFA</span></span>
                </div>
            </div>
            <!-- Semaine -->
            <div class="col-6 col-md-3">
                <div class="modern-card hover-card p-3 shadow-sm h-100 border-primary">
                    <span class="text-muted-theme small fw-bold d-block mb-1"><?= __('this_week_label') ?></span>
                    <span class="h4 fw-black text-main-theme"><?= number_format($weeklyCollections, 0, ',', ' ') ?> <span class="small font-normal text-muted" style="font-size: 11px;">FCFA</span></span>
                </div>
            </div>
            <!-- Mois -->
            <div class="col-6 col-md-3">
                <div class="modern-card hover-card p-3 shadow-sm h-100 border-success">
                    <span class="text-muted-theme small fw-bold d-block mb-1"><?= __('this_month_label') ?></span>
                    <span class="h4 fw-black text-main-theme"><?= number_format($monthlyCollections, 0, ',', ' ') ?> <span class="small font-normal text-muted" style="font-size: 11px;">FCFA</span></span>
                </div>
            </div>
            <!-- Année -->
            <div class="col-6 col-md-3">
                <div class="modern-card hover-card p-3 shadow-sm h-100 border-warning">
                    <span class="text-muted-theme small fw-bold d-block mb-1"><?= __('school_year_label') ?></span>
                    <span class="h4 fw-black text-main-theme"><?= number_format($annualCollections, 0, ',', ' ') ?> <span class="small font-normal text-muted" style="font-size: 11px;">FCFA</span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 1b: Dépenses par période & Solde Réel -->
    <div class="mb-4 animate-fade-in">
        <div class="kpi-section-title text-danger mb-3">
            <i class="bi bi-wallet2 me-2"></i><?= __('expenses_and_balance') ?>
        </div>
        <div class="row g-4">
            <!-- Jour -->
            <div class="col-6 col-md-3">
                <div class="modern-card hover-card p-3 shadow-sm h-100 border-warning">
                    <span class="text-muted-theme small fw-bold d-block mb-1"><?= __('expenses_today') ?></span>
                    <span class="h4 fw-black text-main-theme"><?= number_format($dailyExpenses, 0, ',', ' ') ?> <span class="small font-normal text-muted" style="font-size: 11px;">FCFA</span></span>
                </div>
            </div>
            <!-- Semaine -->
            <div class="col-6 col-md-3">
                <div class="modern-card hover-card p-3 shadow-sm h-100 border-primary">
                    <span class="text-muted-theme small fw-bold d-block mb-1"><?= __('expenses_this_week') ?></span>
                    <span class="h4 fw-black text-main-theme"><?= number_format($weeklyExpenses, 0, ',', ' ') ?> <span class="small font-normal text-muted" style="font-size: 11px;">FCFA</span></span>
                </div>
            </div>
            <!-- Mois -->
            <div class="col-6 col-md-3">
                <div class="modern-card hover-card p-3 shadow-sm h-100 border-danger">
                    <span class="text-muted-theme small fw-bold d-block mb-1"><?= __('expenses_this_month') ?></span>
                    <span class="h4 fw-black text-main-theme"><?= number_format($monthlyExpenses, 0, ',', ' ') ?> <span class="small font-normal text-muted" style="font-size: 11px;">FCFA</span></span>
                </div>
            </div>
            <!-- Solde Réel -->
            <div class="col-6 col-md-3">
                <div class="modern-card hover-card p-3 shadow-sm h-100 <?= $netBalance >= 0 ? 'border-success' : 'border-danger' ?>">
                    <span class="text-muted-theme small fw-bold d-block mb-1"><?= __('net_cash_balance') ?></span>
                    <span class="h4 fw-black text-main-theme"><?= number_format($netBalance, 0, ',', ' ') ?> <span class="small font-normal text-muted" style="font-size: 11px;">FCFA</span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Répartitions & Graphiques -->
    <div class="row g-4 mb-4">
        <!-- Répartition des paiements -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="modern-card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-bold text-main-theme mb-3"><i class="bi bi-pie-chart me-2"></i>Modes de Règlement</h6>
                <div style="height: 200px; position: relative;" class="d-flex align-items-center justify-content-center">
                    <canvas id="paymentMethodChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Répartition des réductions -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="modern-card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-bold text-main-theme mb-3"><i class="bi bi-percent me-2"></i>Motifs des Réductions</h6>
                <div style="height: 200px; position: relative;" class="d-flex align-items-center justify-content-center">
                    <?php if (empty($reductionsRepartition)): ?>
                        <div class="text-center text-muted small py-5">Aucune réduction active</div>
                    <?php else: ?>
                        <canvas id="reductionsChart"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Répartition des bourses -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="modern-card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-bold text-main-theme mb-3"><i class="bi bi-award-fill me-2"></i>Motifs des Bourses</h6>
                <div style="height: 200px; position: relative;" class="d-flex align-items-center justify-content-center">
                    <?php if (empty($scholarshipsRepartition)): ?>
                        <div class="text-center text-muted small py-5">Aucune bourse active</div>
                    <?php else: ?>
                        <canvas id="scholarshipsChart"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Répartition des dépenses -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="modern-card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-bold text-main-theme mb-3"><i class="bi bi-wallet2 me-2"></i>Répartition des Dépenses</h6>
                <div style="height: 200px; position: relative;" class="d-flex align-items-center justify-content-center">
                    <?php if (empty($expensesByCategory)): ?>
                        <div class="text-center text-muted small py-5">Aucune dépense active</div>
                    <?php else: ?>
                        <canvas id="expensesPilotageChart"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Situation des Tranches -->
    <div class="modern-card border-0 shadow-sm p-4 mb-4">
        <h6 class="fw-bold text-main-theme mb-3"><i class="bi bi-bar-chart-steps me-2"></i>Situation des tranches configurées</h6>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th>Tranche</th>
                        <th class="text-end">Montant Attendu</th>
                        <th class="text-end">Montant Payé</th>
                        <th class="text-end">Montant Restant</th>
                        <th style="width: 250px;">Progression</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tranchesSituation as $ts): 
                        $planned = (float)$ts['total_planned'];
                        $paid = (float)$ts['total_paid'];
                        $remaining = max(0.0, $planned - $paid);
                        $percent = $planned > 0 ? round(($paid / $planned) * 100, 1) : 0;
                    ?>
                        <tr class="border-bottom border-theme-light">
                            <td class="fw-bold text-main-theme">Tranche #<?= htmlspecialchars($ts['installment_number']) ?></td>
                            <td class="text-end text-main-theme fw-semibold"><?= number_format($planned, 0, ',', ' ') ?> FCFA</td>
                            <td class="text-end text-success fw-semibold"><?= number_format($paid, 0, ',', ' ') ?> FCFA</td>
                            <td class="text-end text-danger fw-semibold"><?= number_format($remaining, 0, ',', ' ') ?> FCFA</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar bg-success" style="width: <?= $percent ?>%"></div>
                                    </div>
                                    <span class="small fw-bold text-muted-theme"><?= $percent ?>%</span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($tranchesSituation)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">Aucune tranche configurée pour cette année scolaire</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 4: Analyse des Insolvables -->
    <div class="row g-4">
        <!-- Classement des classes les plus insolvables -->
        <div class="col-12 col-lg-6">
            <div class="modern-card border-0 shadow-sm p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-main-theme mb-0"><i class="bi bi-door-open me-2"></i>Insolvabilité par Classe</h6>
                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill"><?= number_format($totalInsolventAmount, 0, ',', ' ') ?> FCFA en retard</span>
                </div>
                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th>Classe</th>
                                <th class="text-center">Élèves</th>
                                <th class="text-end">Montant Dû</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($insolventsByClass as $ibc): ?>
                                <tr class="border-bottom border-theme-light">
                                    <td class="fw-bold text-main-theme"><?= htmlspecialchars($ibc['class_name']) ?></td>
                                    <td class="text-center text-muted-theme"><?= $ibc['count'] ?></td>
                                    <td class="text-end text-danger fw-bold"><?= number_format($ibc['total_due'], 0, ',', ' ') ?> FCFA</td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($insolventsByClass)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">Aucune classe insolvable</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top 10 des élèves les plus insolvables -->
        <div class="col-12 col-lg-6">
            <div class="modern-card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-bold text-main-theme mb-3"><i class="bi bi-people-fill me-2"></i>Top 10 des retards les plus importants</h6>
                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th>Élève</th>
                                <th>Classe</th>
                                <th class="text-center">Échéances</th>
                                <th class="text-end">Retard Dû</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topInsolvents as $ti): ?>
                                <tr class="border-bottom border-theme-light">
                                    <td class="fw-bold text-main-theme"><?= htmlspecialchars(strtoupper($ti['student_nom']) . ' ' . ucwords(strtolower($ti['student_prenom']))) ?></td>
                                    <td><span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill"><?= htmlspecialchars($ti['class_name']) ?></span></td>
                                    <td class="text-center text-muted-theme fw-semibold"><?= $ti['unpaid_installments_count'] ?> tranches</td>
                                    <td class="text-end text-danger fw-bold"><?= number_format($ti['amount_due'], 0, ',', ' ') ?> FCFA</td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($topInsolvents)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Aucun retard de paiement détecté</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Load Chart.js and build charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Chart: Payment Methods
    const payMethodCtx = document.getElementById('paymentMethodChart');
    if (payMethodCtx) {
        const dataPay = <?= json_encode($paymentMethodRepartition) ?>;
        new Chart(payMethodCtx, {
            type: 'doughnut',
            data: {
                labels: dataPay.map(x => x.payment_method || 'Autre'),
                datasets: [{
                    data: dataPay.map(x => parseFloat(x.total)),
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#64748b', '#06b6d4'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: { size: 10 }
                        }
                    }
                }
            }
        });
    }

    // 2. Chart: Discounts
    const reductionsCtx = document.getElementById('reductionsChart');
    if (reductionsCtx) {
        const rawReductions = <?= json_encode($reductionsRepartition) ?>;
        const labelsRed = Object.keys(rawReductions);
        const dataRed = Object.values(rawReductions);

        new Chart(reductionsCtx, {
            type: 'doughnut',
            data: {
                labels: labelsRed,
                datasets: [{
                    data: dataRed,
                    backgroundColor: ['#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#64748b'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: { size: 10 }
                        }
                    }
                }
            }
        });
    }

    // 3. Chart: Scholarships
    const scholarshipsCtx = document.getElementById('scholarshipsChart');
    if (scholarshipsCtx) {
        const rawScholarships = <?= json_encode($scholarshipsRepartition) ?>;
        const labelsSch = Object.keys(rawScholarships);
        const dataSch = Object.values(rawScholarships);

        new Chart(scholarshipsCtx, {
            type: 'doughnut',
            data: {
                labels: labelsSch,
                datasets: [{
                    data: dataSch,
                    backgroundColor: ['#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: { size: 10 }
                        }
                    }
                }
            }
        });
    }

    // 4. Chart: Expenses by Category
    const expCtx = document.getElementById('expensesPilotageChart');
    if (expCtx) {
        const dataExp = <?= json_encode($expensesByCategory) ?>;
        new Chart(expCtx, {
            type: 'doughnut',
            data: {
                labels: dataExp.map(x => x.category_name),
                datasets: [{
                    data: dataExp.map(x => parseFloat(x.total)),
                    backgroundColor: ['#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#64748b', '#06b6d4'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: { size: 10 }
                        }
                    }
                }
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
