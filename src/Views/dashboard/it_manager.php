<?php
$title = __('it_dashboard_title');
ob_start();
?>

<div class="animate-fade-in container-fluid py-4">

    <!-- Page Header -->
    <div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h1 class="fw-black text-main-theme mb-1 fs-4 d-flex align-items-center gap-2">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-info bg-opacity-10 text-info p-2" style="width:40px;height:40px;">
                    <i class="bi bi-pc-display fs-5"></i>
                </span>
                <?= __('it_dashboard_title') ?>
            </h1>
            <p class="text-muted-theme mb-0"><?= __('it_dashboard_subtitle') ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="/users" class="btn btn-info text-white rounded-pill px-4 fw-semibold shadow-sm">
                <i class="bi bi-people-fill me-2"></i><?= __('users') ?>
            </a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="modern-card border-0 shadow-sm p-4 h-100 position-relative overflow-hidden">
                <div class="kpi-icon bg-info bg-opacity-10 text-info rounded-3 d-flex align-items-center justify-content-center mb-3" style="width:48px;height:48px;">
                    <i class="bi bi-person-circle fs-4"></i>
                </div>
                <div class="fw-black text-main-theme fs-4 mb-1"><?= number_format($totalUsers) ?></div>
                <div class="text-muted-theme small fw-semibold"><?= __('total_users') ?></div>
                <div class="position-absolute bottom-0 start-0 w-100" style="height:4px; background: linear-gradient(90deg,#06b6d4,#0ea5e9); border-radius:0 0 12px 12px;"></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="modern-card border-0 shadow-sm p-4 h-100 position-relative overflow-hidden">
                <div class="kpi-icon bg-warning bg-opacity-10 text-warning rounded-3 d-flex align-items-center justify-content-center mb-3" style="width:48px;height:48px;">
                    <i class="bi bi-person-badge fs-4"></i>
                </div>
                <div class="fw-black text-main-theme fs-4 mb-1"><?= number_format($totalTeachers) ?></div>
                <div class="text-muted-theme small fw-semibold"><?= __('total_teachers') ?></div>
                <div class="position-absolute bottom-0 start-0 w-100" style="height:4px; background: linear-gradient(90deg,#f59e0b,#d97706); border-radius:0 0 12px 12px;"></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="modern-card border-0 shadow-sm p-4 h-100 position-relative overflow-hidden">
                <div class="kpi-icon bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center mb-3" style="width:48px;height:48px;">
                    <i class="bi bi-door-open fs-4"></i>
                </div>
                <div class="fw-black text-main-theme fs-4 mb-1"><?= number_format($totalClasses) ?></div>
                <div class="text-muted-theme small fw-semibold"><?= __('classes') ?></div>
                <div class="position-absolute bottom-0 start-0 w-100" style="height:4px; background: linear-gradient(90deg,#3b82f6,#6366f1); border-radius:0 0 12px 12px;"></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="modern-card border-0 shadow-sm p-4 h-100 position-relative overflow-hidden">
                <div class="kpi-icon bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center mb-3" style="width:48px;height:48px;">
                    <i class="bi bi-people fs-4"></i>
                </div>
                <div class="fw-black text-main-theme fs-4 mb-1"><?= number_format($totalStudents) ?></div>
                <div class="text-muted-theme small fw-semibold"><?= __('students') ?></div>
                <div class="position-absolute bottom-0 start-0 w-100" style="height:4px; background: linear-gradient(90deg,#22c55e,#16a34a); border-radius:0 0 12px 12px;"></div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Role Distribution -->
        <div class="col-lg-5">
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

        <!-- Recent Activity -->
        <div class="col-lg-7">
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

    <!-- Quick Actions -->
    <div class="row g-4 mt-2">
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

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
