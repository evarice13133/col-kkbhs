<?php
$title = __('financial_dashboard_title');
ob_start();
?>
<style>
.hover-card {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.3s ease, background-color 0.3s ease;
    border: 1px solid rgba(148, 163, 184, 0.18) !important;
    background: var(--bg-card);
    box-shadow: 0 20px 45px -28px rgba(15, 23, 42, 0.12);
    border-radius: 20px;
}
.hover-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 24px 52px -30px rgba(15, 23, 42, 0.18);
}
.hover-card .kpi-icon {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.3s ease;
    width: 52px;
    height: 52px;
    border-radius: 14px;
}
.hover-card:hover .kpi-icon {
    transform: scale(1.1);
}
.hover-card .card-indicator {
    transition: height 0.3s ease;
}
.hover-card:hover .card-indicator {
    height: 5px !important;
}

/* Card Primary (Purple/Indigo) */
.kpi-card-primary:hover {
    border-color: rgba(139, 92, 246, 0.4) !important;
    box-shadow: 0 12px 20px -5px rgba(139, 92, 246, 0.3) !important;
    background-color: rgba(139, 92, 246, 0.02) !important;
}
.kpi-card-primary:hover .kpi-icon {
    background-color: rgba(139, 92, 246, 0.2) !important;
}

/* Card Success (Green) */
.kpi-card-success:hover {
    border-color: rgba(34, 197, 94, 0.4) !important;
    box-shadow: 0 12px 20px -5px rgba(34, 197, 94, 0.3) !important;
    background-color: rgba(34, 197, 94, 0.02) !important;
}
.kpi-card-success:hover .kpi-icon {
    background-color: rgba(34, 197, 94, 0.2) !important;
}

/* Card Info (Cyan/Blue) */
.kpi-card-info:hover {
    border-color: rgba(6, 182, 212, 0.4) !important;
    box-shadow: 0 12px 20px -5px rgba(6, 182, 212, 0.3) !important;
    background-color: rgba(6, 182, 212, 0.02) !important;
}
.kpi-card-info:hover .kpi-icon {
    background-color: rgba(6, 182, 212, 0.2) !important;
}

/* Card Warning (Amber/Orange) */
.kpi-card-warning:hover {
    border-color: rgba(245, 158, 11, 0.4) !important;
    box-shadow: 0 12px 20px -5px rgba(245, 158, 11, 0.3) !important;
    background-color: rgba(245, 158, 11, 0.02) !important;
}
.kpi-card-warning:hover .kpi-icon {
    background-color: rgba(245, 158, 11, 0.2) !important;
}

/* Card Danger (Red) */
.kpi-card-danger:hover {
    border-color: rgba(239, 68, 68, 0.4) !important;
    box-shadow: 0 12px 20px -5px rgba(239, 68, 68, 0.3) !important;
    background-color: rgba(239, 68, 68, 0.02) !important;
}
.kpi-card-danger:hover .kpi-icon {
    background-color: rgba(239, 68, 68, 0.2) !important;
}

/* Card Secondary (Slate) */
.kpi-card-secondary:hover {
    border-color: rgba(100, 116, 139, 0.4) !important;
    box-shadow: 0 12px 20px -5px rgba(100, 116, 139, 0.3) !important;
    background-color: rgba(100, 116, 139, 0.02) !important;
}
.kpi-card-secondary:hover .kpi-icon {
    background-color: rgba(100, 116, 139, 0.2) !important;
}

.kpi-section-title {
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    font-weight: 800;
}

@media (max-width: 576px) {
    .hover-card {
        padding: 0.5rem 0.65rem !important;
    }
    .hover-card .kpi-icon {
        width: 38px !important;
        height: 38px !important;
    }
    .hover-card .kpi-icon i {
        font-size: 1.1rem !important;
    }
    .hover-card .d-flex {
        gap: 0.5rem !important;
    }
    .hover-card .fs-5 {
        font-size: 0.85rem !important;
    }
    .hover-card .fs-5 span {
        font-size: 0.6rem !important;
    }
    .hover-card .text-muted-theme.small {
        font-size: 0.62rem !important;
        line-height: 1.1;
    }
    .hover-card .badge {
        font-size: 0.58rem !important;
        padding: 0.1rem 0.25rem !important;
    }
}
</style>

<div class="animate-fade-in container-fluid py-4">

    <!-- Tabs header for modern SaaS/ERP -->
    <div class="dashboard-tabs-container mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div>
                <h5 class="fw-black text-main-theme m-0" style="font-family: 'Outfit', sans-serif; letter-spacing: -0.02em; font-size: 1.4rem;">Tableau de bord financier</h5>
                <p class="text-muted-theme small mb-0">Gestion de la caisse, des scolarités et des inscriptions</p>
            </div>
            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 fw-bold small">
                <i class="bi bi-wallet2 me-1"></i> Caisse Active
            </span>
        </div>
        <ul class="nav nav-pills dashboard-nav-pills gap-2 flex-nowrap overflow-auto pb-2" id="dashboard-view-selector" role="tablist">
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link active" data-view="general" role="tab">
                    <i class="bi bi-grid-fill"></i> Vue Générale
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link" data-view="finances" role="tab">
                    <i class="bi bi-wallet2"></i> Finances
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link" data-view="inscriptions" role="tab">
                    <i class="bi bi-person-check-fill"></i> Inscriptions
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link" data-view="scolarite" role="tab">
                    <i class="bi bi-cash-coin"></i> Scolarité
                </button>
            </li>
        </ul>
    </div>

    <!-- Vue Générale : KPI Cards & Shortcuts -->
    <div class="row g-3 g-md-4 mb-4" data-views="general">
        <!-- Effectif Total -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-primary">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="kpi-value" data-count-up="<?= (int) $totalStudents ?>"><?= $totalStudents ?></div>
                    <div class="kpi-label">Effectif Total Actif</div>
                </div>
            </div>
        </div>
        <!-- Recettes Globales -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-success">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div class="kpi-value" style="font-size: 1.45rem; font-weight: 800;"><?= number_format($totalGeneralCollected, 0, ',', ' ') ?> <span class="small font-normal text-muted" style="font-size: 10px;">FCFA</span></div>
                    <div class="kpi-label">Recettes Globales</div>
                </div>
                <div class="kpi-trend text-success">
                    <i class="bi bi-percent"></i> <?= number_format($collectionRate, 1) ?>% Recouvrement
                </div>
            </div>
        </div>
        <!-- Dépenses de l'Année -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-danger">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="kpi-value" style="font-size: 1.45rem; font-weight: 800;"><?= number_format($totalExpenses, 0, ',', ' ') ?> <span class="small font-normal text-muted" style="font-size: 10px;">FCFA</span></div>
                    <div class="kpi-label">Dépenses de l'Année</div>
                </div>
            </div>
        </div>
        <!-- Taux de Recouvrement -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-info">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-percent"></i>
                    </div>
                    <div class="kpi-value"><?= number_format($collectionRate, 1) ?>%</div>
                    <div class="kpi-label">Taux de Recouvrement</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick actions inside Vue Générale -->
    <div class="modern-card mb-4 border-0 shadow-sm border-top border-primary border-4 animate-fade-in" style="border-radius: 24px !important;" data-views="general">
        <div class="modern-card-header border-bottom bg-transparent py-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-lightning-fill text-primary fs-5"></i>
                <h5 class="modern-card-title m-0 text-main-theme"><?= __('quick_actions') ?></h5>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="d-flex gap-3 flex-wrap">
                <button type="button" class="btn btn-info text-white rounded-pill px-4 py-2 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#newVersementModal">
                    <i class="bi bi-wallet-fill me-2"></i><?= __('new_versement') ?>
                </button>
                <a href="/payments" class="btn btn-success rounded-pill px-4 py-2 fw-semibold shadow-sm">
                    <i class="bi bi-plus-circle me-2"></i><?= __('payments_menu') ?>
                </a>
                <a href="/financial-history" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
                    <i class="bi bi-journal-text me-2"></i><?= __('financial_history') ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Finances : KPI Cards -->
    <div class="row g-3 g-md-4 mb-4" data-views="finances">
        <!-- Recettes Globales -->
        <div class="col-12 col-md-4">
            <div class="erp-stat-card card-success">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div class="kpi-value" style="font-size: 1.6rem;"><?= number_format($totalGeneralCollected, 0, ',', ' ') ?> <span class="small font-normal text-muted" style="font-size: 10px;">FCFA</span></div>
                    <div class="kpi-label">Recettes Globales</div>
                </div>
                <div class="kpi-trend text-success">
                    Scolarité (<?= number_format($totalTuitionCollected, 0, ',', ' ') ?>) + Inscription (<?= number_format($totalRegistrationCollected, 0, ',', ' ') ?>)
                </div>
            </div>
        </div>
        <!-- Dépenses Totales -->
        <div class="col-12 col-md-4">
            <div class="erp-stat-card card-danger">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="kpi-value" style="font-size: 1.6rem;"><?= number_format($totalExpenses, 0, ',', ' ') ?> <span class="small font-normal text-muted" style="font-size: 10px;">FCFA</span></div>
                    <div class="kpi-label">Dépenses de l'Année</div>
                </div>
                <div class="kpi-trend text-danger">
                    Ce mois : <?= number_format($monthlyExpenses, 0, ',', ' ') ?> FCFA
                </div>
            </div>
        </div>
        <!-- Solde Net -->
        <div class="col-12 col-md-4">
            <div class="erp-stat-card <?= $netBalance >= 0 ? 'card-info' : 'card-danger' ?>">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-wallet-fill"></i>
                    </div>
                    <div class="kpi-value" style="font-size: 1.6rem;"><?= number_format($netBalance, 0, ',', ' ') ?> <span class="small font-normal text-muted" style="font-size: 10px;">FCFA</span></div>
                    <div class="kpi-label">Solde Réel (Recettes - Dépenses)</div>
                </div>
                <div class="kpi-trend <?= $netBalance >= 0 ? 'text-info' : 'text-danger' ?>">
                    <i class="bi <?= $netBalance >= 0 ? 'bi-plus-circle' : 'bi-dash-circle' ?>"></i> Situation Net
                </div>
            </div>
        </div>
    </div>

    <!-- Scolarité : KPI Cards -->
    <div class="row g-3 g-md-4 mb-4" data-views="scolarite">
        <!-- Scolarité Attendue -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-primary">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div class="kpi-value" style="font-size: 1.45rem;"><?= number_format($totalExpected, 0, ',', ' ') ?> <span class="small font-normal text-muted" style="font-size: 10px;">FCFA</span></div>
                    <div class="kpi-label">Scolarité Attendue</div>
                </div>
                <?php if (!empty($totalReductions) && $totalReductions > 0): ?>
                    <div class="kpi-trend text-muted">
                        Brut : <?= number_format($totalExpectedGross, 0, ',', ' ') ?> (-<?= number_format($totalReductions, 0, ',', ' ') ?>)
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- Scolarité Encaissée -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-success">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="kpi-value" style="font-size: 1.45rem;"><?= number_format($totalTuitionCollected, 0, ',', ' ') ?> <span class="small font-normal text-muted" style="font-size: 10px;">FCFA</span></div>
                    <div class="kpi-label">Scolarité Encaissée</div>
                </div>
                <div class="kpi-trend text-success">
                    Taux de Recouvrement : <?= number_format($collectionRate, 1) ?>%
                </div>
            </div>
        </div>
        <!-- Reste à Recouvrer -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-danger">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-exclamation-circle-fill"></i>
                    </div>
                    <?php $remainingTuition = max(0.0, $totalExpected - $totalTuitionCollected); ?>
                    <div class="kpi-value" style="font-size: 1.45rem;"><?= number_format($remainingTuition, 0, ',', ' ') ?> <span class="small font-normal text-muted" style="font-size: 10px;">FCFA</span></div>
                    <div class="kpi-label">Reste à Recouvrer</div>
                </div>
            </div>
        </div>
        <!-- Réductions et Bourses -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-warning">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-gift-fill"></i>
                    </div>
                    <div class="kpi-value" style="font-size: 1.45rem;"><?= number_format($totalReductions, 0, ',', ' ') ?> <span class="small font-normal text-muted" style="font-size: 10px;">FCFA</span></div>
                    <div class="kpi-label">Réductions appliquées</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scolarité: Tables & Analyse -->
    <div class="row g-4 mb-4" data-views="scolarite">
        <!-- Situation des Tranches -->
        <div class="col-12">
            <div class="modern-card border-0 shadow-sm p-4">
                <h6 class="fw-bold text-main-theme mb-3"><i class="bi bi-bar-chart-steps text-primary me-2"></i>Situation des tranches configurées</h6>
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
        </div>

        <!-- Insolvabilité par Classe -->
        <div class="col-12 col-lg-6">
            <div class="modern-card border-0 shadow-sm p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-main-theme mb-0"><i class="bi bi-door-open text-danger me-2"></i>Insolvabilité par Classe</h6>
                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill"><?= number_format($totalInsolventAmount, 0, ',', ' ') ?> FCFA</span>
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
                <h6 class="fw-bold text-main-theme mb-3"><i class="bi bi-people-fill text-warning me-2"></i>Top 10 des retards les plus importants</h6>
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

    <!-- Inscriptions : KPI Cards -->
    <div class="row g-3 g-md-4 mb-4" data-views="inscriptions">
        <div class="col-12">
            <div class="kpi-section-title text-success mb-2 d-flex align-items-center gap-2">
                <i class="bi bi-person-check fs-5"></i> Validation Financière des Inscriptions & Rentrée
            </div>
        </div>
        <!-- Élèves Déjà Inscrits -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-success">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div class="kpi-value" data-count-up="<?= (int)$totalEnrolled ?>"><?= number_format($totalEnrolled) ?></div>
                    <div class="kpi-label">Inscriptions Payées (Caisse)</div>
                </div>
            </div>
        </div>
        <!-- Élèves Non Inscrits -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-danger">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-person-x"></i>
                    </div>
                    <div class="kpi-value" data-count-up="<?= (int)$totalNonEnrolled ?>"><?= number_format($totalNonEnrolled) ?></div>
                    <div class="kpi-label">Inscriptions Non Payées</div>
                </div>
            </div>
        </div>
        <!-- Taux d'inscription global -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-warning">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-percent"></i>
                    </div>
                    <?php $registrationRate = $totalStudents > 0 ? round(($totalEnrolled / $totalStudents) * 100, 1) : 0; ?>
                    <div class="kpi-value" data-count-up="<?= (int)$registrationRate ?>" data-suffix="%"><?= $registrationRate ?>%</div>
                    <div class="kpi-label">Taux de Paiement</div>
                </div>
            </div>
        </div>
        <!-- Effectif Total Actif -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-info">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="kpi-value" data-count-up="<?= (int)$totalStudents ?>"><?= number_format($totalStudents) ?></div>
                    <div class="kpi-label">Total Élèves Attendus</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recettes & Dépenses par Période -->
    <div class="row g-3 mb-4" data-views="finances">
        <div class="col-lg-6">
            <div class="modern-card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-bold text-main-theme mb-3"><i class="bi bi-calendar-range text-primary me-2"></i>Détail des Recettes</h6>
                <div class="row g-2">
                    <div class="col-4">
                        <div class="p-3 bg-light bg-opacity-25 rounded-4 border text-center h-100">
                            <span class="text-muted-theme small fw-bold d-block mb-1">Aujourd'hui</span>
                            <span class="fw-extrabold text-main-theme small d-block"><?= number_format($dailyCollections, 0, ',', ' ') ?></span>
                            <small class="text-muted" style="font-size: 8px;">FCFA</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-light bg-opacity-25 rounded-4 border text-center h-100">
                            <span class="text-muted-theme small fw-bold d-block mb-1">Cette semaine</span>
                            <span class="fw-extrabold text-main-theme small d-block"><?= number_format($weeklyCollections, 0, ',', ' ') ?></span>
                            <small class="text-muted" style="font-size: 8px;">FCFA</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-light bg-opacity-25 rounded-4 border text-center h-100">
                            <span class="text-muted-theme small fw-bold d-block mb-1">Ce mois</span>
                            <span class="fw-extrabold text-main-theme small d-block"><?= number_format($monthlyCollections, 0, ',', ' ') ?></span>
                            <small class="text-muted" style="font-size: 8px;">FCFA</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="modern-card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-bold text-main-theme mb-3"><i class="bi bi-wallet2 text-danger me-2"></i>Détail des Dépenses</h6>
                <div class="row g-2">
                    <div class="col-4">
                        <div class="p-3 bg-light bg-opacity-25 rounded-4 border text-center h-100">
                            <span class="text-muted-theme small fw-bold d-block mb-1">Aujourd'hui</span>
                            <span class="fw-extrabold text-main-theme small d-block"><?= number_format($dailyExpenses, 0, ',', ' ') ?></span>
                            <small class="text-muted" style="font-size: 8px;">FCFA</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-light bg-opacity-25 rounded-4 border text-center h-100">
                            <span class="text-muted-theme small fw-bold d-block mb-1">Cette semaine</span>
                            <span class="fw-extrabold text-main-theme small d-block"><?= number_format($weeklyExpenses, 0, ',', ' ') ?></span>
                            <small class="text-muted" style="font-size: 8px;">FCFA</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-light bg-opacity-25 rounded-4 border text-center h-100">
                            <span class="text-muted-theme small fw-bold d-block mb-1">Ce mois</span>
                            <span class="fw-extrabold text-main-theme small d-block"><?= number_format($monthlyExpenses, 0, ',', ' ') ?></span>
                            <small class="text-muted" style="font-size: 8px;">FCFA</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4" data-views="finances">
        <!-- Collection Rate Donut -->
        <div class="col-lg-4 col-xl-3">
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

        <!-- Expenses by Category Doughnut -->
        <div class="col-lg-4 col-xl-3">
            <div class="modern-card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-black text-main-theme mb-4">Répartition des Dépenses</h6>
                <div class="d-flex align-items-center justify-content-center" style="height:220px; position:relative;">
                    <canvas id="expensesCategoryChart"></canvas>
                    <?php if (empty($expensesByCategory)): ?>
                        <div class="position-absolute text-center text-muted small">Aucune dépense active</div>
                    <?php endif; ?>
                </div>
                <div class="text-center mt-3">
                    <span class="small text-muted-theme">Total des dépenses :</span>
                    <span class="fw-bold text-danger small"><?= number_format($totalExpenses, 0, ',', ' ') ?> FCFA</span>
                </div>
            </div>
        </div>

        <!-- Monthly Evolution (Revenue vs Expenses) -->
        <div class="col-lg-4 col-xl-6">
            <div class="modern-card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-black text-main-theme mb-4">Comparatif Mensuel (Recettes vs Dépenses)</h6>
                <div style="height:220px;">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Modes de règlement, Bourses & Réductions -->
    <div class="row g-4 mb-4" data-views="finances">
        <!-- Modes de Règlement -->
        <div class="col-lg-4">
            <div class="modern-card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-bold text-main-theme mb-3"><i class="bi bi-credit-card-2-back text-primary me-2"></i>Modes de Règlement</h6>
                <div style="height: 180px; position: relative;" class="d-flex align-items-center justify-content-center">
                    <canvas id="paymentMethodChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Motifs des Réductions -->
        <div class="col-lg-4">
            <div class="modern-card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-bold text-main-theme mb-3"><i class="bi bi-percent text-warning me-2"></i>Motifs des Réductions</h6>
                <div style="height: 180px; position: relative;" class="d-flex align-items-center justify-content-center">
                    <?php if (empty($reductionsRepartition)): ?>
                        <div class="text-center text-muted small py-5">Aucune réduction active</div>
                    <?php else: ?>
                        <canvas id="reductionsChart"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Motifs des Bourses -->
        <div class="col-lg-4">
            <div class="modern-card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-bold text-main-theme mb-3"><i class="bi bi-award text-success me-2"></i>Motifs des Bourses</h6>
                <div style="height: 180px; position: relative;" class="d-flex align-items-center justify-content-center">
                    <?php if (empty($scholarshipsRepartition)): ?>
                        <div class="text-center text-muted small py-5">Aucune bourse active</div>
                    <?php else: ?>
                        <canvas id="scholarshipsChart"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Class enrollment stats breakdown -->
    <div class="row g-4 mb-4 animate-fade-in" data-views="inscriptions">
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
                                    <th class="ps-4 py-3 fw-semibold text-muted-theme small text-uppercase">Classe</th>
                                    <th class="py-3 fw-semibold text-muted-theme text-center small text-uppercase">Total Élèves</th>
                                    <th class="py-3 fw-semibold text-muted-theme text-center small text-uppercase">Inscriptions Payées</th>
                                    <th class="py-3 fw-semibold text-muted-theme text-center small text-uppercase">Inscriptions Non Payées</th>
                                    <th class="py-3 fw-semibold text-muted-theme small text-uppercase">Taux de Paiement</th>
                                    <th class="pe-4 py-3 fw-semibold text-muted-theme text-end small text-uppercase">Montant Encaissé</th>
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
    <div class="row g-4 mb-4" data-views="finances">
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

    let financialChartsInitialized = false;
    window.initFinancialCharts = function() {
        if (financialChartsInitialized) return;

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

        // --- Bar: Monthly Evolution (Revenue vs Expenses) ---
        const monthlyData = <?= json_encode($monthlyPayments) ?>;
        const monthlyExpData = <?= json_encode($monthlyExpensesHist) ?>;
        
        const allMonths = Array.from(new Set([
            ...monthlyData.map(r => r.month),
            ...monthlyExpData.map(e => e.month)
        ])).sort();
        
        const monthLabels = allMonths.map(m => {
            const [y, mm] = m.split('-');
            return new Date(y, mm - 1).toLocaleDateString('fr-FR', { month: 'short', year: '2-digit' });
        });
        
        const monthTotals = allMonths.map(m => {
            const found = monthlyData.find(r => r.month === m);
            return found ? parseFloat(found.total) : 0;
        });
        
        const monthExpTotals = allMonths.map(m => {
            const found = monthlyExpData.find(r => r.month === m);
            return found ? parseFloat(found.total) : 0;
        });

        const barCtx = document.getElementById('monthlyChart');
        if (barCtx) {
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: monthLabels,
                    datasets: [
                        {
                            label: 'Recettes',
                            data: monthTotals,
                            backgroundColor: 'rgba(59,130,246,0.75)',
                            borderRadius: 6,
                            borderSkipped: false
                        },
                        {
                            label: 'Dépenses',
                            data: monthExpTotals,
                            backgroundColor: 'rgba(239,68,68,0.75)',
                            borderRadius: 6,
                            borderSkipped: false
                        }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: true, labels: { color: textColor } } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: textColor } },
                        y: { grid: { color: gridColor }, ticks: { color: textColor, callback: v => (v/1000).toFixed(0) + 'k' } }
                    }
                }
            });
        }

        // --- Doughnut: Expenses by Category ---
        const expCatData = <?= json_encode($expensesByCategory) ?>;
        const expCatCtx = document.getElementById('expensesCategoryChart');
        if (expCatCtx && expCatData.length > 0) {
            new Chart(expCatCtx, {
                type: 'doughnut',
                data: {
                    labels: expCatData.map(c => c.category_name),
                    datasets: [{
                        data: expCatData.map(c => parseFloat(c.total)),
                        backgroundColor: [
                            '#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', 
                            '#ec4899', '#6366f1', '#14b8a6', '#f43f5e', '#06b6d4'
                        ],
                        borderWidth: 0,
                        cutout: '70%'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: true }
                    }
                }
            });
        }

        // --- Doughnut: Payment Method ---
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
                            labels: { boxWidth: 12, font: { size: 10 } }
                        }
                    }
                }
            });
        }

        // --- Doughnut: Discounts ---
        const reductionsCtx = document.getElementById('reductionsChart');
        if (reductionsCtx) {
            const rawReductions = <?= json_encode($reductionsRepartition) ?>;
            new Chart(reductionsCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(rawReductions),
                    datasets: [{
                        data: Object.values(rawReductions),
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
                            labels: { boxWidth: 12, font: { size: 10 } }
                        }
                    }
                }
            });
        }

        // --- Doughnut: Scholarships ---
        const scholarshipsCtx = document.getElementById('scholarshipsChart');
        if (scholarshipsCtx) {
            const rawScholarships = <?= json_encode($scholarshipsRepartition) ?>;
            new Chart(scholarshipsCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(rawScholarships),
                    datasets: [{
                        data: Object.values(rawScholarships),
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
                            labels: { boxWidth: 12, font: { size: 10 } }
                        }
                    }
                }
            });
        }

        financialChartsInitialized = true;
    };

    document.addEventListener('DOMContentLoaded', function() {
        const viewSelector = document.getElementById('dashboard-view-selector');
        if (viewSelector) {
            const buttons = viewSelector.querySelectorAll('[data-view]');
            const viewableElements = document.querySelectorAll('[data-views]');

            const applyView = (selectedView) => {
                buttons.forEach(btn => {
                    if (btn.dataset.view === selectedView) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });

                viewableElements.forEach(el => {
                    const views = el.dataset.views.split(',');
                    if (views.includes(selectedView)) {
                        el.style.display = '';
                    } else {
                        el.style.display = 'none';
                    }
                });

                if (selectedView === 'finances') {
                    window.initFinancialCharts();
                }
            };

            buttons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const view = btn.dataset.view;
                    applyView(view);
                    localStorage.setItem('financial_dashboard_active_view', view);
                });
            });

            // Restore active view or default to general
            let activeView = localStorage.getItem('financial_dashboard_active_view') || 'general';
            if (activeView === 'financial' || activeView === 'academic' || activeView === 'global') {
                activeView = 'general';
            }
            applyView(activeView);
        }
    });
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
