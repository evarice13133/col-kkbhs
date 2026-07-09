<?php
$title = __('it_dashboard_title');

$totalUsers = $totalUsers ?? 0;
$totalTeachers = $totalTeachers ?? 0;
$totalClasses = $totalClasses ?? 0;
$totalStudents = $totalStudents ?? 0;
$recentActivity = $recentActivity ?? [];
$roleDistribution = $roleDistribution ?? [];

ob_start();
?>

<div class="animate-fade-in container-fluid py-4">

    <!-- Tabs header for modern SaaS/ERP -->
    <div class="dashboard-tabs-container mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div>
                <h5 class="fw-black text-main-theme m-0" style="font-family: 'Outfit', sans-serif; letter-spacing: -0.02em; font-size: 1.4rem;">Portail d'Administration IT</h5>
                <p class="text-muted-theme small mb-0">Supervision système, rôles et sécurité</p>
            </div>
            <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3 py-2 fw-bold small">
                <i class="bi bi-shield-check me-1"></i> Admin IT
            </span>
        </div>
        <ul class="nav nav-pills dashboard-nav-pills gap-2 flex-nowrap overflow-auto pb-2" id="dashboard-view-selector" role="tablist">
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link active" data-view="general" role="tab">
                    <i class="bi bi-grid-fill"></i> Vue Générale
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link" data-view="rh" role="tab">
                    <i class="bi bi-people-fill"></i> Ressources Humaines
                </button>
            </li>
        </ul>
    </div>

    <!-- KPI Cards in Vue Générale -->
    <div class="row g-3 g-md-4 mb-4" data-views="general">
        <!-- Utilisateurs -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-info">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div class="kpi-value" data-count-up="<?= (int) $totalUsers ?>"><?= $totalUsers ?></div>
                    <div class="kpi-label"><?= __('total_users') ?></div>
                </div>
            </div>
        </div>
        <!-- Enseignants -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-warning">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div class="kpi-value" data-count-up="<?= (int) $totalTeachers ?>"><?= $totalTeachers ?></div>
                    <div class="kpi-label"><?= __('total_teachers') ?></div>
                </div>
            </div>
        </div>
        <!-- Classes -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-primary">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-door-open"></i>
                    </div>
                    <div class="kpi-value" data-count-up="<?= (int) $totalClasses ?>"><?= $totalClasses ?></div>
                    <div class="kpi-label"><?= __('classes') ?></div>
                </div>
            </div>
        </div>
        <!-- Elèves -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-success">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="kpi-value" data-count-up="<?= (int) $totalStudents ?>"><?= $totalStudents ?></div>
                    <div class="kpi-label"><?= __('students') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vue Générale : Activité Récente (Pleine largeur) -->
    <div class="row g-4 mb-4" data-views="general">
        <div class="col-12">
            <div class="modern-card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 px-4 pt-4 pb-0">
                    <h6 class="fw-black text-main-theme mb-0"><?= __('recent_activity') ?></h6>
                </div>
                <div class="card-body px-4 py-3">
                    <?php if (empty($recentActivity)): ?>
                        <div class="text-center py-5 text-muted-theme">
                            <i class="bi bi-activity fs-1 opacity-25 d-block mb-2"></i>
                            <p class="mb-0 small"><?= __('no_activity_log') ?></p>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-2" style="max-height:400px; overflow-y:auto;">
                            <?php foreach ($recentActivity as $act): ?>
                                <div class="d-flex align-items-start gap-3 py-2 border-bottom border-theme-light">
                                    <div class="mt-1 flex-shrink-0">
                                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-info bg-opacity-10 text-info" style="width:30px;height:30px;">
                                            <i class="bi bi-activity small"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="small fw-semibold text-main-theme"><?= htmlspecialchars((string)($act['action'] ?? '')) ?></div>
                                        <?php if (!empty($act['details'])): ?>
                                            <div class="extra-small text-muted-theme text-truncate"><?= htmlspecialchars((string)$act['details']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted-theme extra-small flex-shrink-0">
                                        <?= isset($act['created_at']) ? date('d/m H:i', strtotime($act['created_at'])) : '' ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Ressources Humaines : Répartition des Rôles (Centré) -->
    <div class="row g-4 mb-4 justify-content-center" data-views="rh">
        <div class="col-lg-6">
            <div class="modern-card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-black text-main-theme mb-4"><?= __('role_distribution') ?></h6>
                <?php
                $roleColors = [
                    'superadmin'  => ['bg' => 'bg-danger-subtle',  'text' => 'text-danger',  'icon' => 'bi-shield-fill'],
                    'admin'       => ['bg' => 'bg-primary-subtle', 'text' => 'text-primary', 'icon' => 'bi-person-fill-gear'],
                    'enseignant'  => ['bg' => 'bg-warning-subtle', 'text' => 'text-warning', 'icon' => 'bi-person-badge'],
                    'caissier'    => ['bg' => 'bg-success-subtle', 'text' => 'text-success', 'icon' => 'bi-cash-stack'],
                    'comptable'   => ['bg' => 'bg-teal-subtle',    'text' => 'text-success',  'icon' => 'bi-calculator'],
                    'it_manager'  => ['bg' => 'bg-info-subtle',   'text' => 'text-info',    'icon' => 'bi-pc-display'],
                ];
                $roleLabels = [
                    'superadmin' => __('role_superadmin'),
                    'admin'      => __('role_admin'),
                    'enseignant' => __('role_enseignant'),
                    'caissier'   => __('role_caissier'),
                    'comptable'  => __('role_comptable'),
                    'it_manager' => __('role_it_manager'),
                ];
                $maxCount = max(array_column($roleDistribution, 'count') ?: [1]);
                ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($roleDistribution as $rd): ?>
                        <?php
                        $rCode = $rd['role'];
                        $rColors = $roleColors[$rCode] ?? ['bg' => 'bg-secondary-subtle', 'text' => 'text-secondary', 'icon' => 'bi-person'];
                        $rLabel = $roleLabels[$rCode] ?? ucfirst($rCode);
                        $pct = round(($rd['count'] / max(1, $maxCount)) * 100);
                        ?>
                        <div class="d-flex align-items-center gap-3">
                            <div class="<?= $rColors['bg'] ?> <?= $rColors['text'] ?> rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:38px;height:38px;">
                                <i class="bi <?= $rColors['icon'] ?>"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small fw-semibold text-main-theme"><?= htmlspecialchars($rLabel) ?></span>
                                    <span class="badge <?= $rColors['bg'] ?> <?= $rColors['text'] ?> rounded-pill px-2 fw-bold"><?= (int) $rd['count'] ?></span>
                                </div>
                                <div class="progress" style="height:6px; border-radius:6px;">
                                    <div class="progress-bar <?= str_replace('text-', 'bg-', $rColors['text']) ?>" style="width:<?= $pct ?>%; border-radius:6px;"></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4 pt-3 border-top border-theme-light">
                    <a href="/users" class="btn btn-sm btn-outline-info rounded-pill w-100 fw-semibold">
                        <i class="bi bi-people-fill me-2"></i><?= __('manage_users') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions (Both views) -->
    <div class="row g-4 mt-2" data-views="general,rh">
        <div class="col-12">
            <div class="modern-card border-0 shadow-sm p-4">
                <h6 class="fw-black text-main-theme mb-3"><?= __('quick_actions') ?></h6>
                <div class="d-flex flex-wrap gap-3">
                    <a href="/users" class="btn btn-outline-info rounded-pill px-4 fw-semibold">
                        <i class="bi bi-person-plus-fill me-2"></i><?= __('users') ?>
                    </a>
                    <a href="/teachers" class="btn btn-outline-warning rounded-pill px-4 fw-semibold">
                        <i class="bi bi-person-badge me-2"></i><?= __('teachers') ?>
                    </a>
                    <a href="/classes" class="btn btn-outline-primary rounded-pill px-4 fw-semibold">
                        <i class="bi bi-door-open me-2"></i><?= __('classes') ?>
                    </a>
                    <a href="/academic_years" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">
                        <i class="bi bi-calendar-event me-2"></i><?= __('academic_years') ?>
                    </a>
                    <a href="/documentation" class="btn btn-outline-success rounded-pill px-4 fw-semibold">
                        <i class="bi bi-question-circle me-2"></i><?= __('help') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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
        };

        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                const view = btn.dataset.view;
                applyView(view);
                localStorage.setItem('it_dashboard_active_view', view);
            });
        });

        let activeView = localStorage.getItem('it_dashboard_active_view') || 'general';
        if (activeView === 'academic' || activeView === 'global') {
            activeView = 'general';
        }
        applyView(activeView);
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
