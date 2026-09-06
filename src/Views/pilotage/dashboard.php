<?php
$title = __('dashboard_executif');
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
.circle-progress-wrapper {
    position: relative;
    width: 90px;
    height: 90px;
}
.circle-progress-wrapper svg {
    transform: rotate(-90deg);
}
</style>

<div class="animate-fade-in container-fluid py-4">

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h1 class="fw-black text-main-theme mb-1 fs-4 d-flex align-items-center gap-2">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary p-2" style="width:40px;height:40px;">
                    <i class="bi bi-pie-chart-fill fs-5"></i>
                </span>
                <?= __('dashboard_executif') ?>
            </h1>
            <p class="text-muted-theme mb-0"><?= __('command_center_home') ?> • <?= __('pilotage_global_subtitle') ?></p>
        </div>
        <?php if (\App\Core\PermissionManager::hasPermission('manage_rbac')): ?>
            <a href="/pilotage/rbac" class="btn btn-primary rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-shield-lock-fill"></i>
                <span><?= __('manage_permissions') ?></span>
            </a>
        <?php endif; ?>
    </div>

    <!-- MAIN GRID -->
    <div class="row g-4">
        
        <!-- SECTION 1: ACADÉMIQUE -->
        <div class="col-12 col-lg-4">
            <div class="modern-card shadow-sm p-4 h-100 border-primary">
                <div class="kpi-section-title text-primary mb-4 d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-mortarboard-fill me-2"></i><?= __('academic') ?></span>
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill"><?= __('students_count', ['count' => $stats_students]) ?></span>
                </div>

                <!-- KPI: Total Students & Success Rate -->
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="p-3 bg-light bg-opacity-25 rounded-4 border h-100">
                            <span class="text-muted-theme small fw-bold d-block mb-1"><?= __('total_students') ?></span>
                            <span class="h3 fw-black text-main-theme"><?= number_format($stats_students) ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light bg-opacity-25 rounded-4 border h-100">
                            <span class="text-muted-theme small fw-bold d-block mb-1"><?= __('success_rate') ?></span>
                            <span class="h3 fw-black text-success"><?= $successRate ?>%</span>
                        </div>
                    </div>
                </div>

                <!-- Répartition par Sexe -->
                <div class="mb-4">
                    <span class="text-muted-theme small fw-bold d-block mb-2"><?= __('gender_distribution') ?></span>
                    <?php 
                    $malePercent = $stats_students > 0 ? round(($maleCount / $stats_students) * 100) : 0;
                    $femalePercent = $stats_students > 0 ? round(($femaleCount / $stats_students) * 100) : 0;
                    ?>
                    <div class="progress rounded-pill mb-2" style="height: 16px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $malePercent ?>%" aria-valuenow="<?= $malePercent ?>" aria-valuemin="0" aria-valuemax="100" title="Garçons: <?= $maleCount ?>"><?= $malePercent ?>%</div>
                        <div class="progress-bar bg-danger bg-opacity-75" role="progressbar" style="width: <?= $femalePercent ?>%" aria-valuenow="<?= $femalePercent ?>" aria-valuemin="0" aria-valuemax="100" title="Filles: <?= $femaleCount ?>"><?= $femalePercent ?>%</div>
                    </div>
                    <div class="d-flex justify-content-between small text-muted-theme">
                        <span><i class="bi bi-gender-male text-primary me-1"></i><?= __('dashboard_admin_boys') ?> (<?= $maleCount ?>)</span>
                        <span><?= __('dashboard_admin_girls') ?> (<?= $femaleCount ?>)<i class="bi bi-gender-female text-danger ms-1"></i></span>
                    </div>
                </div>

                <!-- Répartition par Cycle -->
                <div class="mb-4">
                    <span class="text-muted-theme small fw-bold d-block mb-2"><?= __('cycle_distribution') ?></span>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($cycleRepartition as $cycle): 
                            $cycleP = $stats_students > 0 ? round(($cycle['count'] / $stats_students) * 100) : 0;
                        ?>
                            <div>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fw-bold text-main-theme"><?= htmlspecialchars($cycle['cycle_nom']) ?></span>
                                    <span class="text-muted-theme"><?= $cycle['count'] ?> élèves (<?= $cycleP ?>%)</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $cycleP ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($cycleRepartition)): ?>
                            <p class="text-muted small text-center my-2"><?= __('no_cycle_registered') ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Répartition par Classe -->
                <div>
                    <span class="text-muted-theme small fw-bold d-block mb-2"><?= __('class_distribution') ?></span>
                    <div style="max-height: 180px; overflow-y: auto;" class="pe-2">
                        <div class="list-group list-group-flush">
                            <?php foreach ($classRepartition as $class): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-theme-light">
                                    <span class="fw-semibold text-main-theme small"><?= htmlspecialchars($class['class_nom']) ?></span>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill small"><?= $class['count'] ?> élèves</span>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($classRepartition)): ?>
                                <p class="text-muted small text-center my-2"><?= __('no_class_registered') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- SECTION 2: FINANCIER -->
        <div class="col-12 col-lg-5">
            <div class="modern-card shadow-sm p-4 h-100 border-success">
                <div class="kpi-section-title text-success mb-4 d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-wallet2 me-2"></i><?= __('financial') ?></span>
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill"><?= __('dashboard_financial_recovery_rate_label') ?> <?= $collectionRate ?>%</span>
                </div>

                <!-- High Level KPIs Grid -->
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="p-3 bg-light bg-opacity-25 rounded-4 border h-100">
                            <span class="text-muted-theme small fw-bold d-block mb-1"><?= __('total_expected') ?></span>
                            <span class="h5 fw-black text-main-theme d-block mb-0"><?= number_format($totalExpected, 0, ',', ' ') ?></span>
                            <span class="extra-small text-muted"><?= __('gross_tuition') ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light bg-opacity-25 rounded-4 border h-100">
                            <span class="text-muted-theme small fw-bold d-block mb-1"><?= __('total_collected') ?></span>
                            <span class="h5 fw-black text-success d-block mb-0"><?= number_format($totalGeneralCollected, 0, ',', ' ') ?></span>
                            <span class="extra-small text-muted"><?= __('tuition_registration') ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light bg-opacity-25 rounded-4 border h-100">
                            <span class="text-muted-theme small fw-bold d-block mb-1"><?= __('total_remaining') ?></span>
                            <span class="h5 fw-black text-danger d-block mb-0"><?= number_format($totalRemaining, 0, ',', ' ') ?></span>
                            <span class="extra-small text-muted"><?= __('net_tuition_remaining') ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light bg-opacity-25 rounded-4 border h-100">
                            <span class="text-muted-theme small fw-bold d-block mb-1"><?= __('insolvent_students') ?></span>
                            <span class="h5 fw-black text-danger d-block mb-0"><?= number_format($totalInsolvent) ?></span>
                            <span class="extra-small text-muted"><?= __('late_payment_students') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Breakdown of collections -->
                <div class="mb-4">
                    <span class="text-muted-theme small fw-bold d-block mb-2"><?= __('collection_details') ?></span>
                    <div class="d-flex flex-column gap-2 p-3 bg-light bg-opacity-10 rounded-4 border">
                        <div class="d-flex justify-content-between align-items-center small">
                            <span class="text-muted-theme"><i class="bi bi-journal-check text-info me-1"></i> <?= __('registration_fees') ?> :</span>
                            <span class="fw-bold text-main-theme"><?= number_format($totalRegistrationCollected, 0, ',', ' ') ?> FCFA</span>
                        </div>
                        <hr class="my-1 opacity-10">
                        <div class="d-flex justify-content-between align-items-center small">
                            <span class="text-muted-theme"><i class="bi bi-cash-stack text-success me-1"></i> <?= __('tuition_fees') ?> :</span>
                            <span class="fw-bold text-main-theme"><?= number_format($totalTuitionCollected, 0, ',', ' ') ?> FCFA</span>
                        </div>
                    </div>
                </div>

                <!-- Deductions and scholarships applied -->
                <div>
                    <span class="text-muted-theme small fw-bold d-block mb-2"><?= __('aid_discounts') ?></span>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 bg-light bg-opacity-25 rounded-4 border">
                                <span class="text-muted-theme small fw-bold d-block mb-1"><i class="bi bi-percent text-warning me-1"></i><?= __('reductions') ?></span>
                                <span class="fw-black text-main-theme"><?= number_format($totalReductions, 0, ',', ' ') ?> <span class="small font-normal text-muted" style="font-size:10px;">FCFA</span></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light bg-opacity-25 rounded-4 border">
                                <span class="text-muted-theme small fw-bold d-block mb-1"><i class="bi bi-award-fill text-info me-1"></i><?= __('scholarships') ?></span>
                                <span class="fw-black text-main-theme"><?= number_format($totalScholarships, 0, ',', ' ') ?> <span class="small font-normal text-muted" style="font-size:10px;">FCFA</span></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- SECTION 3: RESSOURCES HUMAINES -->
        <div class="col-12 col-lg-3">
            <div class="modern-card shadow-sm p-4 h-100 border-warning">
                <div class="kpi-section-title text-warning mb-4 d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-people-fill me-2"></i><?= __('ressources_humaines') ?></span>
                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill"><?= $personnelTotal ?> <?= __('staff') ?></span>
                </div>

                <!-- RH KPI list -->
                <div class="d-flex flex-column gap-4">
                    <!-- Personnel Total -->
                    <div class="d-flex align-items-center gap-3 p-3 bg-light bg-opacity-25 rounded-4 border">
                        <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-2 d-flex align-items-center justify-content-center" style="width:46px;height:46px;">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                        <div>
                            <span class="h4 fw-black text-main-theme d-block mb-0"><?= number_format($personnelTotal) ?></span>
                            <span class="text-muted-theme small fw-bold"><?= __('total_staff') ?></span>
                        </div>
                    </div>

                    <!-- Enseignants -->
                    <div class="d-flex align-items-center gap-3 p-3 bg-light bg-opacity-25 rounded-4 border">
                        <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width:46px;height:46px;">
                            <i class="bi bi-person-badge fs-4"></i>
                        </div>
                        <div>
                            <span class="h4 fw-black text-main-theme d-block mb-0"><?= number_format($teachersCount) ?></span>
                            <span class="text-muted-theme small fw-bold"><?= __('teachers') ?></span>
                        </div>
                    </div>

                    <!-- Administratifs -->
                    <div class="d-flex align-items-center gap-3 p-3 bg-light bg-opacity-25 rounded-4 border">
                        <div class="rounded-3 bg-success bg-opacity-10 text-success p-2 d-flex align-items-center justify-content-center" style="width:46px;height:46px;">
                            <i class="bi bi-shield-check fs-4"></i>
                        </div>
                        <div>
                            <span class="h4 fw-black text-main-theme d-block mb-0"><?= number_format($adminsCount) ?></span>
                            <span class="text-muted-theme small fw-bold"><?= __('administrative_staff') ?></span>
                        </div>
                    </div>
                </div>

                <div class="mt-5 p-3 rounded-4 bg-warning bg-opacity-10 border border-warning border-opacity-20">
                    <span class="text-warning small fw-bold d-block mb-1"><i class="bi bi-info-circle-fill me-1"></i><?= __('hr_note') ?></span>
                    <p class="small text-muted-theme mb-0" style="font-size: 11px;"><?= __('hr_note_description') ?></p>
                </div>

            </div>
        </div>

    </div>

</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
