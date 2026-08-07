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
            <p class="text-muted-theme mb-0">Centre de Pilotage • Pilotage global de l'établissement</p>
        </div>
    </div>

    <!-- MAIN GRID -->
    <div class="row g-4">
        
        <!-- SECTION 1: ACADÉMIQUE -->
        <div class="col-12 col-lg-4">
            <div class="modern-card shadow-sm p-4 h-100 border-primary">
                <div class="kpi-section-title text-primary mb-4 d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-mortarboard-fill me-2"></i>Académique</span>
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill"><?= $stats_students ?> Élèves</span>
                </div>

                <!-- KPI: Total Students & Success Rate -->
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="p-3 bg-light bg-opacity-25 rounded-4 border h-100">
                            <span class="text-muted-theme small fw-bold d-block mb-1">Effectif Total</span>
                            <span class="h3 fw-black text-main-theme"><?= number_format($stats_students) ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light bg-opacity-25 rounded-4 border h-100">
                            <span class="text-muted-theme small fw-bold d-block mb-1">Taux Réussite</span>
                            <span class="h3 fw-black text-success"><?= $successRate ?>%</span>
                        </div>
                    </div>
                </div>

                <!-- Répartition par Sexe -->
                <div class="mb-4">
                    <span class="text-muted-theme small fw-bold d-block mb-2">Répartition par sexe</span>
                    <?php 
                    $malePercent = $stats_students > 0 ? round(($maleCount / $stats_students) * 100) : 0;
                    $femalePercent = $stats_students > 0 ? round(($femaleCount / $stats_students) * 100) : 0;
                    ?>
                    <div class="progress rounded-pill mb-2" style="height: 16px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $malePercent ?>%" aria-valuenow="<?= $malePercent ?>" aria-valuemin="0" aria-valuemax="100" title="Garçons: <?= $maleCount ?>"><?= $malePercent ?>%</div>
                        <div class="progress-bar bg-danger bg-opacity-75" role="progressbar" style="width: <?= $femalePercent ?>%" aria-valuenow="<?= $femalePercent ?>" aria-valuemin="0" aria-valuemax="100" title="Filles: <?= $femaleCount ?>"><?= $femalePercent ?>%</div>
                    </div>
                    <div class="d-flex justify-content-between small text-muted-theme">
                        <span><i class="bi bi-gender-male text-primary me-1"></i>Garçons (<?= $maleCount ?>)</span>
                        <span>Filles (<?= $femaleCount ?>)<i class="bi bi-gender-female text-danger ms-1"></i></span>
                    </div>
                </div>

                <!-- Répartition par Cycle -->
                <div class="mb-4">
                    <span class="text-muted-theme small fw-bold d-block mb-2">Répartition par cycle</span>
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
                            <p class="text-muted small text-center my-2">Aucun cycle enregistré</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Répartition par Classe -->
                <div>
                    <span class="text-muted-theme small fw-bold d-block mb-2">Répartition par classe</span>
                    <div style="max-height: 180px; overflow-y: auto;" class="pe-2">
                        <div class="list-group list-group-flush">
                            <?php foreach ($classRepartition as $class): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-theme-light">
                                    <span class="fw-semibold text-main-theme small"><?= htmlspecialchars($class['class_nom']) ?></span>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill small"><?= $class['count'] ?> élèves</span>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($classRepartition)): ?>
                                <p class="text-muted small text-center my-2">Aucune classe enregistrée</p>
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
                    <span><i class="bi bi-wallet2 me-2"></i>Financier</span>
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Taux Recouvrement: <?= $collectionRate ?>%</span>
                </div>

                <!-- High Level KPIs Grid -->
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="p-3 bg-light bg-opacity-25 rounded-4 border h-100">
                            <span class="text-muted-theme small fw-bold d-block mb-1">Total Attendu</span>
                            <span class="h5 fw-black text-main-theme d-block mb-0"><?= number_format($totalExpected, 0, ',', ' ') ?></span>
                            <span class="extra-small text-muted">Scolarité brut</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light bg-opacity-25 rounded-4 border h-100">
                            <span class="text-muted-theme small fw-bold d-block mb-1">Total Encaissé</span>
                            <span class="h5 fw-black text-success d-block mb-0"><?= number_format($totalGeneralCollected, 0, ',', ' ') ?></span>
                            <span class="extra-small text-muted">Scolarité + Inscription</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light bg-opacity-25 rounded-4 border h-100">
                            <span class="text-muted-theme small fw-bold d-block mb-1">Total Restant</span>
                            <span class="h5 fw-black text-danger d-block mb-0"><?= number_format($totalRemaining, 0, ',', ' ') ?></span>
                            <span class="extra-small text-muted">Scolarité nette restante</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light bg-opacity-25 rounded-4 border h-100">
                            <span class="text-muted-theme small fw-bold d-block mb-1">Insolvables</span>
                            <span class="h5 fw-black text-danger d-block mb-0"><?= number_format($totalInsolvent) ?></span>
                            <span class="extra-small text-muted">Élèves en retard de paiement</span>
                        </div>
                    </div>
                </div>

                <!-- Breakdown of collections -->
                <div class="mb-4">
                    <span class="text-muted-theme small fw-bold d-block mb-2">Détail des Encaissements</span>
                    <div class="d-flex flex-column gap-2 p-3 bg-light bg-opacity-10 rounded-4 border">
                        <div class="d-flex justify-content-between align-items-center small">
                            <span class="text-muted-theme"><i class="bi bi-journal-check text-info me-1"></i> Frais d'inscription :</span>
                            <span class="fw-bold text-main-theme"><?= number_format($totalRegistrationCollected, 0, ',', ' ') ?> FCFA</span>
                        </div>
                        <hr class="my-1 opacity-10">
                        <div class="d-flex justify-content-between align-items-center small">
                            <span class="text-muted-theme"><i class="bi bi-cash-stack text-success me-1"></i> Frais de scolarité :</span>
                            <span class="fw-bold text-main-theme"><?= number_format($totalTuitionCollected, 0, ',', ' ') ?> FCFA</span>
                        </div>
                    </div>
                </div>

                <!-- Deductions and scholarships applied -->
                <div>
                    <span class="text-muted-theme small fw-bold d-block mb-2">Aides & Abattements Accordés</span>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 bg-light bg-opacity-25 rounded-4 border">
                                <span class="text-muted-theme small fw-bold d-block mb-1"><i class="bi bi-percent text-warning me-1"></i>Réductions</span>
                                <span class="fw-black text-main-theme"><?= number_format($totalReductions, 0, ',', ' ') ?> <span class="small font-normal text-muted" style="font-size:10px;">FCFA</span></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light bg-opacity-25 rounded-4 border">
                                <span class="text-muted-theme small fw-bold d-block mb-1"><i class="bi bi-award-fill text-info me-1"></i>Bourses</span>
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
                    <span><i class="bi bi-people-fill me-2"></i>Ressources Humaines</span>
                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill"><?= $personnelTotal ?> Effectifs</span>
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
                            <span class="text-muted-theme small fw-bold">Personnel Total</span>
                        </div>
                    </div>

                    <!-- Enseignants -->
                    <div class="d-flex align-items-center gap-3 p-3 bg-light bg-opacity-25 rounded-4 border">
                        <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width:46px;height:46px;">
                            <i class="bi bi-person-badge fs-4"></i>
                        </div>
                        <div>
                            <span class="h4 fw-black text-main-theme d-block mb-0"><?= number_format($teachersCount) ?></span>
                            <span class="text-muted-theme small fw-bold">Enseignants</span>
                        </div>
                    </div>

                    <!-- Administratifs -->
                    <div class="d-flex align-items-center gap-3 p-3 bg-light bg-opacity-25 rounded-4 border">
                        <div class="rounded-3 bg-success bg-opacity-10 text-success p-2 d-flex align-items-center justify-content-center" style="width:46px;height:46px;">
                            <i class="bi bi-shield-check fs-4"></i>
                        </div>
                        <div>
                            <span class="h4 fw-black text-main-theme d-block mb-0"><?= number_format($adminsCount) ?></span>
                            <span class="text-muted-theme small fw-bold">Administratifs</span>
                        </div>
                    </div>
                </div>

                <div class="mt-5 p-3 rounded-4 bg-warning bg-opacity-10 border border-warning border-opacity-20">
                    <span class="text-warning small fw-bold d-block mb-1"><i class="bi bi-info-circle-fill me-1"></i>Note RH</span>
                    <p class="small text-muted-theme mb-0" style="font-size: 11px;">Ce résumé liste les comptes utilisateurs actifs. Les administratifs incluent les rôles de direction, caisse, comptabilité et informatique.</p>
                </div>

            </div>
        </div>

    </div>

</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
