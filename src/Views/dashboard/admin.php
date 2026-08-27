<?php
$title = __("command_center");

$db = \App\Core\Database::getInstance()->getConnection();
$activeYearName = $db->query("SELECT nom FROM academic_years WHERE is_active = 1 LIMIT 1")->fetchColumn();

if (!function_exists('nm_level_class')) {
    function nm_level_class($label)
    {
        $map = [
            'Excellent' => 'level-excellent',
            'Bon' => 'level-bon',
            'Moyen' => 'level-moyen',
            'Faible' => 'level-faible',
            'A demarrer' => 'level-ademarrer',
        ];

        return $map[$label] ?? 'level-ademarrer';
    }
}

if (!function_exists('nm_backup_state_class')) {
    function nm_backup_state_class(string $state): string
    {
        return match ($state) {
            'success' => 'bg-success bg-opacity-10 text-success',
            'warning' => 'bg-warning bg-opacity-10 text-warning',
            'stale', 'failed' => 'bg-danger bg-opacity-10 text-danger',
            default => 'bg-secondary bg-opacity-10 text-secondary',
        };
    }
}

$teachersUnder50 = count(array_filter($teacherMetrics, fn($m) => $m['progress_percent'] < 50));
$topTeacher = $teacherMetrics[0] ?? null;
$worstTeacher = !empty($teacherMetrics) ? $teacherMetrics[count($teacherMetrics) - 1] : null;
$recentTeacherActivity = array_slice($teacherActivitySummary ?? [], 0, 7);
$backupRecentArchives = $backupOverview['recent_archives'] ?? [];
$formatDateTime = static function (?string $value): string {
    if ($value === null || trim($value) === '') {
        return __('never');
    }

    $timestamp = strtotime($value);
    return $timestamp ? date('Y-m-d H:i', $timestamp) : __('not_available');
};

$usageCards = [
    ['value' => (int) ($usageMetrics['weekly_active_users'] ?? 0), 'label' => __('weekly_active_users'), 'hint' => __('active_users_label'), 'icon' => 'bi-person-check-fill', 'accent' => 'primary'],
    ['value' => (int) ($usageMetrics['monthly_active_users'] ?? 0), 'label' => __('monthly_active_users'), 'hint' => __('active_users_label'), 'icon' => 'bi-people-fill', 'accent' => 'info'],
    ['value' => (int) ($usageMetrics['weekly_visits'] ?? 0), 'label' => __('weekly_visits'), 'hint' => __('visits_label'), 'icon' => 'bi-activity', 'accent' => 'success'],
    ['value' => (int) ($usageMetrics['monthly_visits'] ?? 0), 'label' => __('monthly_visits'), 'hint' => __('visits_label'), 'icon' => 'bi-bar-chart-line-fill', 'accent' => 'info'],
    ['value' => (int) ($usageMetrics['weekly_activity'] ?? 0), 'label' => __('weekly_activity'), 'hint' => __('activity_events_label'), 'icon' => 'bi-lightning-charge-fill', 'accent' => 'warning'],
    ['value' => (int) ($usageMetrics['monthly_activity'] ?? 0), 'label' => __('monthly_activity'), 'hint' => __('activity_events_label'), 'icon' => 'bi-graph-up-arrow', 'accent' => 'danger'],
];

$quickAccessLinks = [
    // ── Ressources humaines ──────────────────────────────────
    ['url' => '/students', 'icon' => 'bi-people-fill',           'label' => __('students'),            'meta' => (string) ((int) $stats_students)],
    ['url' => '/teachers', 'icon' => 'bi-person-badge-fill',     'label' => __('teachers'),            'meta' => (string) ((int) $stats_teachers)],
    // ── Structure ────────────────────────────────────────────
    ['url' => '/classes',  'icon' => 'bi-door-open-fill',        'label' => __('classes'),             'meta' => (string) ((int) $stats_classes)],
    ['url' => '/subjects', 'icon' => 'bi-book-fill',             'label' => __('subjects'),            'meta' => (string) ((int) $stats_subjects)],
// ── Gestion des notes ───────────────────────────────────────
    ['url' => '/notes',    'icon' => 'bi-pencil-square',         'label' => __('enter_marks'),         'meta' => (string) ((int) $globalPending)],
    ['url' => '/bulletins/discipline', 'icon' => 'bi-shield-check', 'label' => __('discipline_management'), 'meta' => null],
    ['url' => '/honors',   'icon' => 'bi-award-fill',            'label' => __('honor_roll_title'),    'meta' => null],
    ['url' => '/proces-verbal', 'icon' => 'bi-file-earmark-text','label' => __('proces_verbaux'),      'meta' => null],
    // ── Pilotage ─────────────────────────────────────────────
    ['url' => '/sequences',       'icon' => 'bi-check2-square',  'label' => __('evaluations'),         'meta' => null],
    // ── Affectations ─────────────────────────────────────────
    ['url' => '/teachers', 'icon' => 'bi-diagram-3-fill',        'label' => __('academic_assignment'), 'meta' => (string) ((int) $teachers_without_assignment)],
    // ── Aide ─────────────────────────────────────────────────
    ['url' => '/documentation', 'icon' => 'bi-question-circle-fill', 'label' => __('help'),            'meta' => null],
];

// Ajouter conditionnellement le lien Bulletin si l'impression est activée
if ($bulletin_printing_enabled ?? true) {
    array_splice($quickAccessLinks, 6, 0, [['url' => '/bulletins','icon' => 'bi-file-earmark-pdf', 'label' => __('bulletins'), 'meta' => null]]);
}

if (in_array(\App\Core\Session::get('user_role'), ['superadmin', 'it_manager'])) {
    $quickAccessLinks[] = ['url' => '/academic_years',  'icon' => 'bi-calendar-event', 'label' => __('academic_years'),      'meta' => null];
}

$quickAccessLinks[] = ['url' => '/departments', 'icon' => 'bi-building',       'label' => __('departments'),       'meta' => null];
$quickAccessLinks[] = ['url' => '/sections',    'icon' => 'bi-grid-3x3-gap',   'label' => __('academic_sections'), 'meta' => null];
$quickAccessLinks[] = ['url' => '/cycles',      'icon' => 'bi-layers',          'label' => __('academic_cycles'),   'meta' => null];
$quickAccessLinks[] = ['url' => '/settings',    'icon' => 'bi-gear-fill',       'label' => __('settings'),          'meta' => null];

if (\App\Core\Session::get('user_role') === 'superadmin') {
    $quickAccessLinks[] = ['url' => '/users',       'icon' => 'bi-people-fill',     'label' => __('users'),             'meta' => (string) ((int) $stats_users)];
}

ob_start();
?>

<div class="animate-fade-in admin-analytics">

    <!-- Tabs header for modern SaaS/ERP -->
    <!-- Tabs header for modern SaaS/ERP -->
    <div class="dashboard-tabs-container mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div>
                <h5 class="fw-black text-main-theme m-0" style="font-family: 'Outfit', sans-serif; letter-spacing: -0.02em; font-size: 1.4rem;"><?= __('dashboard_admin_title') ?></h5>
                <p class="text-muted-theme small mb-0"><?= __('dashboard_admin_subtitle') ?></p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <?php if (!empty($no_active_teaching_types)): ?>
                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2 fw-bold small">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= __('dashboard_admin_no_teaching_types') ?>
                    </span>
                <?php elseif (!empty($activeTeachingTypes) && count($activeTeachingTypes) === 1): ?>
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 fw-bold small">
                        <i class="bi bi-building me-1"></i> <?= __('dashboard_admin_teaching_type_colon') ?> <?= htmlspecialchars($activeTeachingTypes[0]['nom']) ?>
                    </span>
                <?php elseif (!empty($activeTeachingTypes) && count($activeTeachingTypes) > 1): ?>
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 fw-bold small">
                        <i class="bi bi-diagram-3-fill me-1"></i> <?= count($activeTeachingTypes) ?> <?= __('dashboard_admin_active_teaching_types') ?>
                    </span>
                <?php endif; ?>
                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 fw-bold small">
                    <i class="bi bi-calendar-event me-1"></i> <?= __('dashboard_admin_academic_year_colon') ?> <?= htmlspecialchars($activeYearName ?: '') ?>
                </span>
            </div>
        </div>
        <ul class="nav nav-pills dashboard-nav-pills gap-2 flex-nowrap overflow-auto pb-2" id="dashboard-view-selector" role="tablist">
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link active" data-view="general" role="tab">
                    <i class="bi bi-grid-fill"></i> <?= __('dashboard_admin_tab_general') ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link" data-view="finances" role="tab">
                    <i class="bi bi-wallet2"></i> <?= __('dashboard_admin_tab_finances') ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link" data-view="inscriptions" role="tab">
                    <i class="bi bi-person-check-fill"></i> <?= __('dashboard_admin_tab_registrations') ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link" data-view="scolarite" role="tab">
                    <i class="bi bi-cash-coin"></i> <?= __('dashboard_admin_tab_fees') ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link" data-view="rh" role="tab">
                    <i class="bi bi-people-fill"></i> <?= __('dashboard_admin_tab_rh') ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link" data-view="pedagogie" role="tab">
                    <i class="bi bi-mortarboard-fill"></i> <?= __('dashboard_admin_tab_pedagogy') ?>
                </button>
            </li>
        </ul>
    </div>

    <?php if (!empty($no_active_teaching_types)): ?>
        <div class="alert alert-warning border-0 shadow-sm rounded-4 p-4 mb-4">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-exclamation-triangle-fill fs-2 text-warning"></i>
                <div>
                    <h6 class="fw-bold m-0 text-main-theme"><?= __('dashboard_admin_no_teaching_types') ?></h6>
                    <p class="mb-0 text-muted small"><?= __('dashboard_admin_no_teaching_types_desc') ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($activeTeachingTypes) && count($activeTeachingTypes) > 1 && !empty($statsByTeachingType)): ?>
        <!-- Section Multi-Synthèses par Type d'Enseignement Actif (CAS 2) -->
        <div class="mb-5 animate-fade-in" data-views="general,pedagogie">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-diagram-3-fill text-primary fs-5"></i>
                    <h6 class="fw-bold m-0 text-uppercase small letter-spacing-1 text-main-theme"><?= __('dashboard_admin_teaching_type_summaries') ?></h6>
                </div>
                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-bold extra-small"><?= count($activeTeachingTypes) ?> <?= __('dashboard_admin_distinct_summaries') ?></span>
            </div>
            
            <div class="row g-4">
                <?php foreach ($statsByTeachingType as $ttId => $stt): 
                    $ttInfo = $stt['teaching_type'];
                    $finData = $stt['financial_data'] ?? [];
                ?>
                    <div class="col-12 col-xl-6">
                        <div class="modern-card p-4 border-0 shadow-sm border-top border-primary border-4 rounded-4 h-100" style="background: rgba(var(--primary-rgb), 0.02);">
                            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                                <div>
                                    <h5 class="fw-black text-main-theme m-0" style="font-family: 'Outfit', sans-serif;">
                                        <i class="bi bi-building text-primary me-2"></i><?= __('dashboard_admin_summary_dash') ?> <?= htmlspecialchars(strtoupper($ttInfo['nom'])) ?>
                                    </h5>
                                    <small class="text-muted-theme"><?= __('dashboard_admin_attached_teaching_type') ?></small>
                                </div>
                                <span class="badge bg-primary text-white rounded-pill px-3 py-1 fw-bold small">Code: <?= htmlspecialchars($ttInfo['code']) ?></span>
                            </div>
                            
                            <div class="row g-3 mb-3">
                                <div class="col-6 col-sm-3">
                                    <div class="p-3 bg-body rounded-3 border shadow-2xs h-100">
                                        <div class="text-muted small fw-bold text-truncate mb-1"><?= __('dashboard_admin_students_students') ?></div>
                                        <div class="h4 fw-black m-0 text-primary"><?= (int)$stt['stats_students'] ?></div>
                                        <div class="extra-small text-success fw-bold mt-1"><i class="bi bi-check-circle me-1"></i><?= (int)$stt['stats_students_inscrits'] ?> <?= __('dashboard_admin_enrolled') ?></div>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <div class="p-3 bg-body rounded-3 border shadow-2xs h-100">
                                        <div class="text-muted small fw-bold text-truncate mb-1">Classes</div>
                                        <div class="h4 fw-black m-0 text-success"><?= (int)$stt['stats_classes'] ?></div>
                                        <div class="extra-small text-muted mt-1"><?= (int)$stt['stats_subjects'] ?> <?= __('dashboard_admin_subjects_count') ?></div>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <div class="p-3 bg-body rounded-3 border shadow-2xs h-100">
                                        <div class="text-muted small fw-bold text-truncate mb-1"><?= __('dashboard_admin_marks_entries') ?></div>
                                        <div class="h4 fw-black m-0 text-info"><?= (int)$stt['globalProgress'] ?>%</div>
                                        <div class="extra-small text-muted mt-1"><?= (int)$stt['globalFilled'] ?>/<?= (int)$stt['globalExpected'] ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center bg-body p-3 rounded-3 border extra-small">
                                <div>
                                    <span class="fw-bold text-main-theme me-2"><?= __('dashboard_admin_fees_recovery_colon') ?></span>
                                    <span class="fw-black text-success me-3"><?= number_format($finData['totalTuitionCollected'] ?? 0, 0, ',', ' ') ?> FCFA</span>
                                    <span class="text-muted">(Taux : <?= number_format($finData['collectionRate'] ?? 0, 1) ?>%)</span>
                                </div>
                                <div>
                                    <span class="fw-bold text-danger"><i class="bi bi-person-exclamation me-1"></i><?= (int)($finData['totalInsolvent'] ?? 0) ?> <?= __('dashboard_admin_insolvent') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>


    <?php if (\App\Core\Session::get('user_role') === 'superadmin'): ?>
        <!-- Notifications Vitrine -->
        <?php if (!empty($landing_notifications)): ?>
            <div class="row g-3 mb-5 animate-fade-in" data-views="general">
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-bell-fill text-accent fs-5"></i>
                            <h6 class="fw-bold m-0 text-uppercase small letter-spacing-1"><?= __('dashboard_admin_showcase_notifications') ?></h6>
                        </div>
                        <span class="badge bg-accent bg-opacity-10 text-accent rounded-pill"><?= count($landing_notifications) ?> <?= __('dashboard_admin_new_notifications') ?></span>
                    </div>
                    <div class="table-responsive">
                        <div class="d-flex gap-3 pb-3">
                            <?php foreach ($landing_notifications as $notif): 
                                $isArchived = $notif['archived'] ?? false;
                            ?>
                                <div class="modern-card p-3 shadow-sm <?= $isArchived ? 'border-secondary opacity-75' : 'border-accent' ?> flex-shrink-0" id="notif-<?= h($notif['id']) ?>" style="min-width: 300px; max-width: 350px; background: <?= $isArchived ? 'rgba(0,0,0,0.02)' : 'rgba(var(--primary-rgb), 0.02)' ?>;">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="fw-bold text-main-theme small d-flex align-items-center gap-2">
                                            <?= h($notif['name']) ?>
                                            <?php if ($isArchived): ?>
                                                <span class="badge bg-secondary extra-small"><?= __('dashboard_admin_archived') ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="small text-muted" style="font-size: 0.7rem;"><?= h($formatDateTime($notif['created_at'])) ?></div>
                                    </div>
                                    <div class="small text-primary mb-2"><?= h($notif['email']) ?></div>
                                    <div class="text-main-theme small text-truncate-2 mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        <?= h($notif['message']) ?>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-0 extra-small" onclick="manageNotif('<?= h($notif['id']) ?>', 'toggle-archive', '<?= $isArchived ? addslashes((string) __('dashboard_admin_restore_notif_confirm')) : addslashes((string) __('dashboard_admin_archive_notif_confirm')) ?>')" title="<?= $isArchived ? addslashes((string) __('restore')) : addslashes((string) __('archive')) ?>">
                                                <i class="bi <?= $isArchived ? 'bi-arrow-counterclockwise' : 'bi-archive' ?>"></i>
                                            </button>
                                            <button class="btn btn-xs btn-outline-danger rounded-pill px-2 py-0 extra-small" onclick="manageNotif('<?= h($notif['id']) ?>', 'delete', '<?= addslashes((string) __('dashboard_admin_delete_notif_confirm')) ?>')" title="<?= addslashes((string) __('delete')) ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        <button class="btn btn-sm btn-link p-0 text-accent fw-bold text-decoration-none small" onclick="Swal.fire({title: '<?= addslashes((string) __('message_from')) ?> ' + '<?= h(addslashes($notif['name'])) ?>', text: '<?= h(addslashes($notif['message'])) ?>', footer: 'Contact: <?= h(addslashes($notif['email'])) ?>'})"><?= __('read') ?></button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            function manageNotif(id, action, confirmMsg) {
                const url = action === 'delete' ? '/notifications/delete' : '/notifications/toggle-archive';

                Swal.fire({
                    title: '<?= addslashes((string) __('confirmation')) ?>',
                    text: confirmMsg,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<?= addslashes((string) __('yes')) ?>',
                    cancelButtonText: '<?= addslashes((string) __('cancel')) ?>'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`${url}?id=${id}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                Swal.fire('<?= addslashes((string) __('error_title')) ?>', data.error, 'error');
                            }
                        });
                    }
                });
            }
            </script>
        <?php endif; ?>

        <div class="row g-3 mb-5" data-views="global,rh">
            <div class="col-12">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-graph-up-arrow text-primary"></i>
                    <h6 class="fw-bold m-0 text-uppercase small letter-spacing-1">
                        <?= __('usage_statistics') ?>
                    </h6>
                </div>
            </div>
            <?php foreach ($usageCards as $index => $card): ?>
                <div class="col-6 col-md-4 col-xl-2 stats-col">
                    <div class="modern-card p-3 border-0 shadow-sm h-100 transition-base scale-on-hover stats-card usage-stat-card"
                        style="--stats-index: <?= (int) $index ?>;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="avatar-xs bg-<?= h($card['accent']) ?> bg-opacity-10 text-<?= h($card['accent']) ?> rounded-3 d-flex align-items-center justify-content-center"
                                 style="width: 32px; height: 32px;">
                                <i class="bi <?= h($card['icon']) ?>"></i>
                            </div>
                        </div>
                        <div class="h3 fw-black m-0" data-count-up="<?= (int) $card['value'] ?>"><?= (int) $card['value'] ?>
                        </div>
                        <div class="text-muted small fw-bold text-truncate" title="<?= h($card['label']) ?>">
                            <?= h($card['label']) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>


    <!-- Vue Générale : KPI Cards (SaaS/ERP Modern style) -->
    <div class="row g-3 g-md-4 mb-4" data-views="general">
        <!-- Effectif Total -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-primary">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="kpi-value" data-count-up="<?= (int) $stats_students ?>"><?= $stats_students ?></div>
                    <div class="kpi-label"><?= __('total_effectif') ?></div>
                </div>
                <div class="kpi-trend text-primary">
                    <i class="bi bi-arrow-up-right"></i> <?= number_format($conversion_rate, 1) ?>% <?= __('dashboard_admin_enrollment_rate') ?>
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
                    <div class="kpi-label"><?= __('dashboard_financial_global_revenue') ?></div>
                </div>
                <div class="kpi-trend text-success">
                    <i class="bi bi-percent"></i> <?= number_format($collectionRate, 1) ?>% <?= __('dashboard_admin_recovery_rate') ?>
                </div>
            </div>
        </div>
        <!-- Progression Notes -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-warning">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <div class="kpi-value" data-count-up="<?= (int) $globalProgress ?>" data-suffix="%"><?= $globalProgress ?>%</div>
                    <div class="kpi-label"><?= __('dashboard_admin_marks_progress_title') ?></div>
                </div>
                <div class="kpi-trend text-warning">
                    <i class="bi bi-hourglass-split"></i> <?= number_format($globalPending) ?> <?= __('pending') ?>
                </div>
            </div>
        </div>
        <!-- Utilisateurs Actifs -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-info">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                    <div class="kpi-value" data-count-up="<?= (int) ($usageMetrics['weekly_active_users'] ?? 0) ?>"><?= (int) ($usageMetrics['weekly_active_users'] ?? 0) ?></div>
                    <div class="kpi-label"><?= __('weekly_active_users') ?></div>
                </div>
                <div class="kpi-trend text-info">
                    <i class="bi bi-activity"></i> <?= (int) ($usageMetrics['weekly_visits'] ?? 0) ?> <?= __('visits_label') ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (\App\Core\Session::get('user_role') === 'superadmin'): ?>
        <div class="row g-3 mb-5" data-views="general">
            <div class="col-12">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-graph-up-arrow text-primary"></i>
                    <h6 class="fw-bold m-0 text-uppercase small letter-spacing-1">
                        <?= __('usage_statistics') ?>
                    </h6>
                </div>
            </div>
            <?php foreach ($usageCards as $index => $card): ?>
                <div class="col-6 col-md-4 col-xl-2 stats-col">
                    <div class="modern-card p-3 border-0 shadow-sm h-100 transition-base scale-on-hover stats-card usage-stat-card"
                        style="--stats-index: <?= (int) $index ?>;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="avatar-xs bg-<?= h($card['accent']) ?> bg-opacity-10 text-<?= h($card['accent']) ?> rounded-3 d-flex align-items-center justify-content-center"
                                 style="width: 32px; height: 32px;">
                                <i class="bi <?= h($card['icon']) ?>"></i>
                            </div>
                        </div>
                        <div class="h3 fw-black m-0" data-count-up="<?= (int) $card['value'] ?>"><?= (int) $card['value'] ?>
                        </div>
                        <div class="text-muted small fw-bold text-truncate" title="<?= h($card['label']) ?>">
                            <?= h($card['label']) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Pédagogie : KPI Cards (SaaS/ERP Modern style) -->
    <div class="row g-3 g-md-4 mb-4" data-views="pedagogie">
        <!-- Classes -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="erp-stat-card card-success">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-door-open-fill"></i>
                    </div>
                    <div class="kpi-value" data-count-up="<?= (int) $stats_classes ?>"><?= $stats_classes ?></div>
                    <div class="kpi-label"><?= __('active_rooms') ?></div>
                </div>
            </div>
        </div>
        <!-- Enseignants -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="erp-stat-card card-warning">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-person-video3"></i>
                    </div>
                    <div class="kpi-value" data-count-up="<?= (int) $stats_teachers ?>"><?= $stats_teachers ?></div>
                    <div class="kpi-label"><?= __('teachers') ?></div>
                </div>
            </div>
        </div>
        <!-- Matières -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="erp-stat-card card-secondary">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-book-half"></i>
                    </div>
                    <div class="kpi-value" data-count-up="<?= (int) $stats_subjects ?>"><?= (int) $stats_subjects ?></div>
                    <div class="kpi-label"><?= __('subjects') ?></div>
                </div>
                <?php if (\App\Core\Session::get('user_role') === 'superadmin' && $stats_subjects_inactive > 0): ?>
                    <div class="kpi-trend text-danger">
                        <i class="bi bi-exclamation-circle"></i> <?= $stats_subjects_inactive ?> inactives
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- Progression Globale -->
        <div class="col-6 col-md-4 col-xl-3">
            <div class="erp-stat-card card-info">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <div class="kpi-value" data-count-up="<?= (int) $globalProgress ?>" data-suffix="%"><?= $globalProgress ?>%</div>
                    <div class="kpi-label"><?= __('global_progress') ?></div>
                </div>
                <div class="progress mt-2" style="height: 4px; border-radius: 10px; background: rgba(var(--primary-rgb), 0.08);">
                    <div class="progress-bar bg-info" style="width: <?= $globalProgress ?>%"></div>
                </div>
            </div>
        </div>
        <!-- Alertes Enseignants -->
        <div class="col-6 col-md-4 col-xl-3">
            <div class="erp-stat-card card-danger">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div class="kpi-value text-danger" data-count-up="<?= (int) $teachersUnder50 ?>"><?= $teachersUnder50 ?></div>
                    <div class="kpi-label"><?= __('critical_delays') ?></div>
                </div>
                <div class="kpi-trend text-danger">
                    Progression &lt; 50%
                </div>
            </div>
        </div>
    </div>

    <!-- Répartitions Démographiques (Dashboard Exécutif) -->
    <div class="row g-4 mb-4" data-views="pedagogie">
        <!-- Taux de Réussite Global Card -->
        <div class="col-lg-4">
            <div class="erp-stat-card card-primary h-100 justify-content-center">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-award-fill"></i>
                    </div>
                    <div class="kpi-value" data-count-up="<?= (int)$successRate ?>" data-suffix="%"><?= $successRate ?>%</div>
                    <div class="kpi-label"><?= __('dashboard_admin_global_success_rate') ?></div>
                </div>
                <div class="kpi-trend text-primary">
                    <?= __('dashboard_admin_success_rate_calc') ?>
                </div>
            </div>
        </div>

        <!-- Répartition par Sexe -->
        <div class="col-lg-4">
            <div class="modern-card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-bold text-main-theme mb-3"><i class="bi bi-gender-ambiguous text-primary me-2"></i><?= __('dashboard_admin_gender_distribution') ?></h6>
                <?php 
                $malePercent = $stats_students > 0 ? round(($maleCount / $stats_students) * 100) : 0;
                $femalePercent = $stats_students > 0 ? round(($femaleCount / $stats_students) * 100) : 0;
                ?>
                <div class="progress rounded-pill mb-3" style="height: 20px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $malePercent ?>%" aria-valuenow="<?= $malePercent ?>" aria-valuemin="0" aria-valuemax="100" title="Garçons: <?= $maleCount ?>"><?= $malePercent ?>%</div>
                    <div class="progress-bar bg-danger bg-opacity-75" role="progressbar" style="width: <?= $femalePercent ?>%" aria-valuenow="<?= $femalePercent ?>" aria-valuemin="0" aria-valuemax="100" title="Filles: <?= $femaleCount ?>"><?= $femalePercent ?>%</div>
                </div>
                <div class="d-flex justify-content-between small text-muted-theme">
                    <span><i class="bi bi-gender-male text-primary me-1"></i><?= __('dashboard_admin_boys') ?> (<?= $maleCount ?>)</span>
                    <span><?= __('dashboard_admin_girls') ?> (<?= $femaleCount ?>)<i class="bi bi-gender-female text-danger ms-1"></i></span>
                </div>
            </div>
        </div>

        <!-- Répartition par Cycle -->
        <div class="col-lg-4">
            <div class="modern-card border-0 shadow-sm p-4 h-100">
                <h6 class="fw-bold text-main-theme mb-3"><i class="bi bi-diagram-3 text-success me-2"></i><?= __('dashboard_admin_cycle_distribution') ?></h6>
                <div class="d-flex flex-column gap-2" style="max-height: 150px; overflow-y: auto;">
                    <?php foreach ($cycleRepartition as $cycle): 
                        $cycleP = $stats_students > 0 ? round(($cycle['count'] / $stats_students) * 100) : 0;
                    ?>
                        <div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="fw-bold text-main-theme"><?= htmlspecialchars($cycle['cycle_nom']) ?></span>
                                <span class="text-muted-theme"><?= $cycle['count'] ?> élèves (<?= $cycleP ?>%)</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $cycleP ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($cycleRepartition)): ?>
                        <p class="text-muted small text-center my-2"><?= __('dashboard_admin_no_cycles_registered') ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Vue Pédagogie : Supervision Enseignants -->
    <div class="row g-4 mb-4" data-views="pedagogie">
        <div class="col-xl-8">
            <!-- Suivi Progression Enseignants -->
            <div class="modern-card border-0 shadow-lg border-top border-success border-4 h-100">
                <div class="modern-card-header bg-transparent p-4 border-bottom">
                    <h5 class="fw-bold m-0 text-main-theme"><i class="bi bi-clock-history text-success me-2"></i>Suivi de la saisie des notes par enseignant</h5>
                </div>
                <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th class="ps-4 border-0 py-3">Enseignant</th>
                                <th class="border-0 py-3 text-center">Saisies</th>
                                <th class="border-0 py-3">Progression</th>
                                <th class="border-0 py-3 pe-4">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teacherMetrics as $m): ?>
                                <tr>
                                    <td class="ps-4 border-0 py-3">
                                        <div class="fw-bold text-main-theme"><?= h($m['teacher_name']) ?></div>
                                        <div class="small text-muted-theme"><?= $m['classes_count'] ?> classe(s)</div>
                                    </td>
                                    <td class="text-center border-0 fw-bold text-main-theme">
                                        <?= $m['filled_count'] ?>
                                        <small class="text-muted-theme">/<?= $m['expected_count'] ?></small>
                                    </td>
                                    <td class="border-0" style="min-width: 150px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px; border-radius: 10px; background: var(--border-color);">
                                                <div class="progress-bar bg-primary" style="width: <?= $m['progress_percent'] ?>%"></div>
                                            </div>
                                            <span class="small fw-bold text-main-theme"><?= $m['progress_percent'] ?>%</span>
                                        </div>
                                    </td>
                                    <td class="pe-4 border-0"><span class="level-badge <?= nm_level_class($m['level_label']) ?>"><?= __($m['level_label']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-4 d-flex flex-column gap-3">
            <!-- Enseignant le plus actif -->
            <?php if ($topTeacher): ?>
                <div class="modern-card border-0 shadow-sm overflow-hidden text-white flex-grow-1" style="background: linear-gradient(135deg, #10b981, #059669) !important; border-radius: 20px !important;">
                    <div class="modern-card-body p-4 position-relative d-flex flex-column justify-content-between h-100">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2 opacity-75">
                                <i class="bi bi-star-fill text-warning"></i>
                                <span class="fw-bold small text-uppercase letter-spacing-1 text-white" style="color: #ffffff !important;"><?= __('dashboard_admin_most_active_teacher_card') ?></span>
                            </div>
                            <h4 class="fw-black mb-1 text-white" style="color: #ffffff !important;"><?= h($topTeacher['teacher_name']) ?></h4>
                            <p class="mb-4 small text-white" style="color: #ffffff !important; opacity: 0.9 !important;"><?= $topTeacher['classes_count'] ?> classe(s) • <?= $topTeacher['filled_count'] ?>/<?= $topTeacher['expected_count'] ?> notes</p>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <span class="badge rounded-pill px-3 py-1.5 fw-bold" style="background-color: rgba(255, 255, 255, 0.25) !important; color: #ffffff !important;"><?= $topTeacher['progress_percent'] ?>% Rempli</span>
                            <i class="bi bi-patch-check-fill text-white fs-1 position-absolute bottom-0 end-0 m-3 opacity-25" style="font-size: 5rem !important;"></i>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Enseignant le moins actif -->
            <?php if ($worstTeacher && $worstTeacher['teacher_name'] !== ($topTeacher['teacher_name'] ?? '')): ?>
                <div class="modern-card border-0 shadow-sm overflow-hidden text-white flex-grow-1" style="background: linear-gradient(135deg, #f43f5e, #e11d48) !important; border-radius: 20px !important;">
                    <div class="modern-card-body p-4 position-relative d-flex flex-column justify-content-between h-100">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2 opacity-75">
                                <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                                <span class="fw-bold small text-uppercase letter-spacing-1 text-white" style="color: #ffffff !important;"><?= __('dashboard_admin_least_active_teacher_card') ?></span>
                            </div>
                            <h4 class="fw-black mb-1 text-white" style="color: #ffffff !important;"><?= h($worstTeacher['teacher_name']) ?></h4>
                            <p class="mb-4 small text-white" style="color: #ffffff !important; opacity: 0.9 !important;"><?= $worstTeacher['classes_count'] ?> class(s) • <?= $worstTeacher['filled_count'] ?>/<?= $worstTeacher['expected_count'] ?> notes</p>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <span class="badge rounded-pill px-3 py-1.5 fw-bold" style="background-color: rgba(255, 255, 255, 0.25) !important; color: #ffffff !important;"><?= $worstTeacher['progress_percent'] ?>% Rempli</span>
                            <i class="bi bi-x-circle-fill text-white fs-1 position-absolute bottom-0 end-0 m-3 opacity-25" style="font-size: 5rem !important;"></i>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Section: Workflow & Statut des Inscriptions -->
    <?php $registrationRate = $totalStudents > 0 ? round(($totalEnrolled / $totalStudents) * 100, 1) : 0; ?>
    <div class="row g-3 g-md-4 mb-4" data-views="inscriptions">
        <div class="col-12">
            <div class="d-flex align-items-center gap-2 mt-2 mb-1">
                <i class="bi bi-person-check text-primary fs-5"></i>
                <h6 class="fw-bold m-0 text-uppercase small letter-spacing-1 text-main-theme"><?= __('dashboard_admin_admin_status_title') ?></h6>
            </div>
        </div>
        <!-- 1. Administratif: Inscrits -->
        <div class="col-6 col-md-4 col-xl">
            <div class="erp-stat-card card-success h-100">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                    <div class="kpi-value text-success" data-count-up="<?= (int) $stats_students_inscrits ?>"><?= $stats_students_inscrits ?></div>
                    <div class="kpi-label"><?= __('dashboard_admin_enrolled_status_label') ?></div>
                </div>
                <div class="kpi-trend text-success">
                    <?= __('dashboard_admin_conv_rate_colon') ?> <?= number_format($conversion_rate, 1) ?>%
                </div>
            </div>
        </div>
        <!-- 2. Administratif: Non Inscrits -->
        <div class="col-6 col-md-4 col-xl">
            <div class="erp-stat-card card-warning h-100">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-person-dash-fill"></i>
                    </div>
                    <div class="kpi-value text-warning" data-count-up="<?= (int) $stats_students_non_inscrits ?>"><?= $stats_students_non_inscrits ?></div>
                    <div class="kpi-label"><?= __('pending') ?> (<?= __('status') ?>)</div>
                </div>
            </div>
        </div>
        <!-- 3. Administratif: Démissionnaires -->
        <div class="col-6 col-md-4 col-xl">
            <div class="erp-stat-card card-danger h-100">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-person-x-fill"></i>
                    </div>
                    <div class="kpi-value text-danger" data-count-up="<?= (int) $stats_students_demissionnaires ?>"><?= $stats_students_demissionnaires ?></div>
                    <div class="kpi-label"><?= __('withdrawn') ?></div>
                </div>
            </div>
        </div>
        <!-- 4. Financier: Payés -->
        <div class="col-6 col-md-4 col-xl">
            <div class="erp-stat-card card-primary h-100">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <div class="kpi-value text-primary" data-count-up="<?= (int) $totalEnrolled ?>"><?= $totalEnrolled ?></div>
                    <div class="kpi-label"><?= __('dashboard_admin_school_fees') ?></div>
                </div>
                <div class="kpi-trend text-primary">
                    <?= __('dashboard_financial_payment_rate') ?> : <?= number_format($registrationRate, 1) ?>%
                </div>
            </div>
        </div>
        <!-- 5. Financier: Reste à payer -->
        <div class="col-6 col-md-4 col-xl">
            <div class="erp-stat-card card-secondary h-100">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="kpi-value text-secondary" data-count-up="<?= (int) $totalNonEnrolled ?>"><?= $totalNonEnrolled ?></div>
                    <div class="kpi-label"><?= __('dashboard_financial_unpaid_registrations') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Accès rapide - Scroll horizontal -->
    <div class="modern-card mb-4 border-0 shadow-sm border-top border-primary border-4 animate-fade-in" style="border-radius: 24px !important;" data-views="general">
        <div class="modern-card-header border-bottom bg-transparent py-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-lightning-fill text-primary fs-5"></i>
                <h5 class="modern-card-title m-0 text-main-theme"><?= __('quick_actions') ?></h5>
            </div>
            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold"><?= count($quickAccessLinks) ?> <?= __('links') ?></span>
        </div>
        <div class="modern-card-body p-3">
            <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                <div class="d-grid gap-2" style="grid-template-columns: repeat(3, minmax(220px, 1fr)); min-width: 700px;">
                    <?php foreach ($quickAccessLinks as $link): ?>
                        <a href="<?= h($link['url']) ?>" class="quick-access-link text-decoration-none">
                            <span class="quick-access-icon">
                                <i class="bi <?= h($link['icon']) ?>"></i>
                            </span>
                            <span class="quick-access-text"><?= h($link['label']) ?></span>
                            <?php if (!empty($link['meta'])): ?>
                                <span class="quick-access-meta"><?= h((string) $link['meta']) ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- SECTION : TABLEAU DE BORD INTELLIGENT AVANCÉ -->
    <!-- ========================================== -->
    <div class="row g-4 mb-5" data-views="pedagogie">
        <!-- 1. Classement des Élèves & Répartition des Niveaux -->
        <div class="col-xl-6">
            <div class="modern-card border-0 shadow-lg border-top border-accent border-4 h-100 animate-fade-in" style="border-radius: 24px !important;">
                <div class="modern-card-header bg-transparent p-4 border-bottom d-flex justify-content-between align-items-center" style="border-radius: 24px 24px 0 0 !important;">
                    <div>
                        <h5 class="fw-bold m-0 text-main-theme"><i class="bi bi-cpu-fill text-accent me-2"></i><?= __('db_predictive_dashboard') ?></h5>
                        <p class="text-muted small m-0" style="font-size: 11px;"><?= __('db_predictive_subtitle') ?></p>
                    </div>
                </div>
                
                <div class="p-4">
                    <!-- Nav tabs style ultra-premium -->
                    <ul class="nav nav-pills mb-4 gap-2" id="studentTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded-pill fw-bold text-uppercase px-3 py-2 btn-sm" id="top-students-tab" data-bs-toggle="tab" data-bs-target="#top-students" type="button" role="tab" aria-controls="top-students" aria-selected="true" style="font-size: 11px; transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);">
                                <?= __('db_tab_excellences') ?>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill fw-bold text-uppercase px-3 py-2 btn-sm btn-outline-danger" id="strug-students-tab" data-bs-toggle="tab" data-bs-target="#strug-students" type="button" role="tab" aria-controls="strug-students" aria-selected="false" style="font-size: 11px; transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);">
                                <?= __('db_tab_alerts') ?>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill fw-bold text-uppercase px-3 py-2 btn-sm btn-outline-info" id="distrib-tab" data-bs-toggle="tab" data-bs-target="#distrib-pane" type="button" role="tab" aria-controls="distrib-pane" aria-selected="false" style="font-size: 11px; transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);">
                                <?= __('db_tab_distribution') ?>
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="studentTabContent">
                        <!-- A. Top Students -->
                        <div class="tab-pane fade show active" id="top-students" role="tabpanel" aria-labelledby="top-students-tab">
                            <?php if (empty($topStudents)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-journal-x fs-2 d-block mb-2"></i> <?= __('db_no_notes') ?>
                                </div>
                            <?php else: ?>
                                <div class="d-flex flex-column gap-3">
                                    <?php foreach ($topStudents as $index => $std): ?>
                                        <div class="d-flex align-items-center justify-content-between p-3 rounded-4 transition-base scale-on-hover" style="border-radius: 16px !important; background: var(--bg-body); border: 1px solid var(--border-color) !important; box-shadow: 0 4px 12px rgba(0,0,0,0.01);">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center fw-bold text-white shadow-sm" 
                                                     style="width: 42px; height: 42px; background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                                                    #<?= $index + 1 ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-main-theme" style="font-size: 13.5px;"><?= htmlspecialchars(strtoupper($std['nom']) . ' ' . ucwords(strtolower($std['prenom']))) ?></div>
                                                    <span class="badge bg-soft-primary extra-small rounded-pill px-2.5"><?= htmlspecialchars($std['classe_nom']) ?></span>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-black text-success" style="font-size: 17px;"><?= number_format((float)$std['moyenne'], 2, ',', ' ') ?><span class="small text-muted" style="font-size: 10px;">/20</span></div>
                                                <span class="extra-small text-muted-theme" style="font-size: 10px; font-weight: 600;"><?= __('db_general_average') ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- B. Struggling Students -->
                        <div class="tab-pane fade" id="strug-students" role="tabpanel" aria-labelledby="strug-students-tab">
                            <?php if (empty($strugglingStudents)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-emoji-smile-fill text-success fs-1 d-block mb-2"></i>
                                    <span class="fw-bold d-block text-success"><?= __('db_all_excellence') ?></span>
                                </div>
                            <?php else: ?>
                                <div class="d-flex flex-column gap-3">
                                    <?php foreach ($strugglingStudents as $index => $std): ?>
                                        <div class="d-flex align-items-center justify-content-between p-3 rounded-4 transition-base scale-on-hover" style="border-radius: 16px !important; background: var(--bg-body); border: 1px solid var(--border-color) !important;">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center fw-bold bg-soft-danger shadow-sm" 
                                                     style="width: 42px; height: 42px;">
                                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-main-theme" style="font-size: 13.5px;"><?= htmlspecialchars(strtoupper($std['nom']) . ' ' . ucwords(strtolower($std['prenom']))) ?></div>
                                                    <span class="badge bg-soft-danger extra-small rounded-pill px-2.5"><?= htmlspecialchars($std['classe_nom']) ?></span>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-black text-danger" style="font-size: 17px;"><?= number_format((float)$std['moyenne'], 2, ',', ' ') ?><span class="small text-muted" style="font-size: 10px;">/20</span></div>
                                                <span class="extra-small text-danger" style="font-size: 10px; font-weight: 600;"><?= __('db_academic_support') ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
 
                        <!-- C. Distribution Chart Grid -->
                        <div class="tab-pane fade" id="distrib-pane" role="tabpanel" aria-labelledby="distrib-tab">
                            <div class="p-2">
                                <h6 class="fw-bold mb-3 text-main-theme small text-uppercase letter-spacing-1"><?= __('db_general_dist_curve') ?></h6>
                                <div class="d-flex flex-column gap-3">
                                    <?php 
                                    $totalDist = array_sum($distribution ?? []);
                                    $getPercent = fn($val) => $totalDist > 0 ? round(($val / $totalDist) * 100) : 0;
                                    
                                    $bands = [
                                        ['label' => __('db_elite'), 'val' => $distribution['elite'] ?? 0, 'color' => 'bg-warning', 'badge' => 'Gold'],
                                        ['label' => __('db_satisfactory'), 'val' => $distribution['satisfait'] ?? 0, 'color' => 'bg-success', 'badge' => 'Emerald'],
                                        ['label' => __('db_passable'), 'val' => $distribution['passable'] ?? 0, 'color' => 'bg-primary', 'badge' => 'Indigo'],
                                        ['label' => __('db_support_required'), 'val' => $distribution['soutien'] ?? 0, 'color' => 'bg-danger', 'badge' => 'Sunset'],
                                    ];
                                    
                                    foreach ($bands as $b):
                                        $p = $getPercent($b['val']);
                                    ?>
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="small fw-bold text-main-theme" style="font-size: 12px;"><?= $b['label'] ?></span>
                                                <span class="small fw-black text-muted-theme" style="font-size: 11px;"><?= __('db_student_count', ['count' => $b['val'], 'percent' => $p]) ?></span>
                                            </div>
                                            <div class="progress" style="height: 10px; border-radius: 99px; background: rgba(0,0,0,0.04);">
                                                <div class="progress-bar <?= $b['color'] ?> progress-bar-striped progress-bar-animated" style="width: <?= $p ?>%; border-radius: 99px;"></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
 
        <!-- 2. Diagnostic des Disciplines / Points Forts & Faibles -->
        <div class="col-xl-6">
            <div class="modern-card border-0 shadow-lg border-top border-warning border-4 h-100 animate-fade-in" style="border-radius: 24px !important;">
                <div class="modern-card-header bg-transparent p-4 border-bottom d-flex justify-content-between align-items-center" style="border-radius: 24px 24px 0 0 !important;">
                    <div>
                        <h5 class="fw-bold m-0 text-main-theme"><i class="bi bi-compass-fill text-warning me-2"></i><?= __('db_diagnostic_disciplines') ?></h5>
                        <p class="text-muted small m-0" style="font-size: 11px;"><?= __('db_diagnostic_subtitle') ?></p>
                    </div>
                </div>

                <!-- Onglets de navigation style ultra-premium -->
                <ul class="nav nav-pills mb-4 gap-2 px-4 pt-3" id="disciplineTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill fw-bold text-uppercase px-3 py-2 btn-sm" id="best-tab" data-bs-toggle="tab" data-bs-target="#best-content" type="button" role="tab" aria-controls="best-content" aria-selected="true" style="font-size: 11px; transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);">
                            <i class="bi bi-trophy-fill me-1"></i><?= __('db_best_disciplines') ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-bold text-uppercase px-3 py-2 btn-sm btn-outline-danger" id="critical-tab" data-bs-toggle="tab" data-bs-target="#critical-content" type="button" role="tab" aria-controls="critical-content" aria-selected="false" style="font-size: 11px; transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i><?= __('db_critical_disciplines') ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-bold text-uppercase px-3 py-2 btn-sm btn-outline-info" id="average-tab" data-bs-toggle="tab" data-bs-target="#average-content" type="button" role="tab" aria-controls="average-content" aria-selected="false" style="font-size: 11px; transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);">
                            <i class="bi bi-bar-chart-fill me-1"></i><?= __('db_average_disciplines') ?>
                        </button>
                    </li>
                </ul>

                <div class="tab-content px-4 pb-4" id="disciplineTabsContent">
                    <!-- Contenu: 5 Meilleures Disciplines -->
                    <div class="tab-pane fade show active" id="best-content" role="tabpanel">
                        <div class="d-flex flex-column gap-3">
                            <?php if (empty($top5Subjects)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    <?= __('db_no_subject_stats') ?>
                                </div>
                            <?php else: ?>
                                <?php foreach ($top5Subjects as $index => $subject): ?>
                                    <div class="d-flex align-items-center justify-content-between p-3 rounded-4 transition-base scale-on-hover" style="border-radius: 16px !important; background: var(--bg-body); border: 1px solid var(--border-color) !important; box-shadow: 0 4px 12px rgba(0,0,0,0.01);">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center fw-bold text-white shadow-sm" 
                                                 style="width: 42px; height: 42px; background: linear-gradient(135deg, #10b981, #059669);">
                                                #<?= $index + 1 ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-main-theme" style="font-size: 13.5px;"><?= htmlspecialchars($subject['nom']) ?></div>
                                                <div class="small text-muted-theme" style="font-size: 11px;">
                                                    <i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($subject['teachers'] ?? '-') ?>
                                                </div>
                                                <div class="small text-muted-theme" style="font-size: 11px;">
                                                    <i class="bi bi-door-open-fill me-1"></i><?= htmlspecialchars($subject['classes'] ?? '-') ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-black text-success" style="font-size: 17px;"><?= number_format((float)$subject['moyenne'], 2, ',', ' ') ?><span class="small text-muted" style="font-size: 10px;">/20</span></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Contenu: 5 Pires Disciplines -->
                    <div class="tab-pane fade" id="critical-content" role="tabpanel">
                        <div class="d-flex flex-column gap-3">
                            <?php if (empty($bottom5Subjects)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    <?= __('db_no_subject_stats') ?>
                                </div>
                            <?php else: ?>
                                <?php foreach ($bottom5Subjects as $index => $subject): ?>
                                    <div class="d-flex align-items-center justify-content-between p-3 rounded-4 transition-base scale-on-hover" style="border-radius: 16px !important; background: var(--bg-body); border: 1px solid var(--border-color) !important; box-shadow: 0 4px 12px rgba(0,0,0,0.01);">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center fw-bold text-white shadow-sm" 
                                                 style="width: 42px; height: 42px; background: linear-gradient(135deg, #ef4444, #dc2626);">
                                                #<?= $index + 1 ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-main-theme" style="font-size: 13.5px;"><?= htmlspecialchars($subject['nom']) ?></div>
                                                <div class="small text-muted-theme" style="font-size: 11px;">
                                                    <i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($subject['teachers'] ?? '-') ?>
                                                </div>
                                                <div class="small text-muted-theme" style="font-size: 11px;">
                                                    <i class="bi bi-door-open-fill me-1"></i><?= htmlspecialchars($subject['classes'] ?? '-') ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-black text-danger" style="font-size: 17px;"><?= number_format((float)$subject['moyenne'], 2, ',', ' ') ?><span class="small text-muted" style="font-size: 10px;">/20</span></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Contenu: Disciplines Moyennes par Évaluation -->
                    <div class="tab-pane fade" id="average-content" role="tabpanel">
                        <?php if (empty($activeEvaluations)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                <?= __('db_no_evaluations') ?>
                            </div>
                        <?php else: ?>
                            <?php foreach ($activeEvaluations as $eval): ?>
                                <div class="mb-4">
                                    <h6 class="fw-bold text-main-theme mb-3" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                                        <i class="bi bi-calendar-check me-1"></i><?= htmlspecialchars($eval) ?>
                                    </h6>
                                    <div class="d-flex flex-column gap-2">
                                        <?php if (empty($subjectByEval[$eval])): ?>
                                            <div class="text-center text-muted small py-3">
                                                <?= __('db_no_subject_stats') ?>
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($subjectByEval[$eval] as $index => $subject): ?>
                                                <div class="d-flex align-items-center justify-content-between p-2 rounded-3 transition-base scale-on-hover" style="border-radius: 12px !important; background: var(--bg-body); border: 1px solid var(--border-color) !important; box-shadow: 0 2px 8px rgba(0,0,0,0.01);">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="avatar-xs rounded-circle d-flex align-items-center justify-content-center fw-bold text-white shadow-sm" 
                                                             style="width: 32px; height: 32px; background: linear-gradient(135deg, #3b82f6, #2563eb); font-size: 11px;">
                                                            #<?= $index + 1 ?>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-main-theme" style="font-size: 12px;"><?= htmlspecialchars($subject['nom']) ?></div>
                                                            <div class="small text-muted-theme" style="font-size: 10px;">
                                                                <i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($subject['teachers'] ?? '-') ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="fw-black text-info" style="font-size: 14px;"><?= number_format((float)$subject['moyenne'], 2, ',', ' ') ?><span class="small text-muted" style="font-size: 9px;">/20</span></div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
 
    <!-- ========================================== -->
    <!-- SECTION : DYNAMIQUE DE PERFORMANCE & ÉVOLUTION -->
    <!-- ========================================== -->
    <div class="row g-4 mb-5" data-views="pedagogie">
        <!-- 1. Performances Générales des Classes -->
        <div class="col-xl-6">
            <div class="modern-card border-0 shadow-lg border-top border-success border-4 h-100 animate-fade-in" style="border-radius: 24px !important;">
                <div class="modern-card-header bg-transparent p-4 border-bottom d-flex justify-content-between align-items-center" style="border-radius: 24px 24px 0 0 !important;">
                    <div>
                        <h5 class="fw-bold m-0 text-main-theme"><i class="bi bi-building-fill text-success me-2"></i><?= __('db_class_performances') ?></h5>
                        <p class="text-muted small m-0" style="font-size: 11px;"><?= __('db_class_subtitle') ?></p>
                    </div>
                </div>
                
                <div class="p-4" style="max-height: 400px; overflow-y: auto;">
                    <?php if (empty($classStats)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-building-fill-slash fs-1 d-block mb-3 text-muted"></i>
                            <?= __('db_no_class_stats') ?>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-4">
                            <?php foreach ($classStats as $cs): 
                                $successRate = (int)($cs['success_rate'] ?? 0);
                                $barColor = $successRate >= 75 ? 'bg-success' : ($successRate >= 50 ? 'bg-primary' : 'bg-warning');
                                $softClass = $successRate >= 75 ? 'bg-soft-success' : ($successRate >= 50 ? 'bg-soft-primary' : 'bg-soft-warning');
                            ?>
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div>
                                            <span class="fw-black text-main-theme" style="font-size: 14px;"><?= htmlspecialchars($cs['class_name']) ?></span>
                                            <small class="text-muted ms-2">(<?= $cs['total_students'] ?>)</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="fw-bold text-main-theme" style="font-size: 13.5px;"><?= number_format((float)$cs['class_avg'], 2, ',', ' ') ?> / 20</span>
                                            <span class="badge rounded-pill px-2.5 py-1 ms-2 <?= $softClass ?> font-weight-700" style="font-size: 10.5px;"><?= __('db_success_rate', ['percent' => $successRate]) ?></span>
                                        </div>
                                    </div>
                                    <div class="progress" style="height: 10px; border-radius: 99px; background: rgba(var(--primary-rgb), 0.05); box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);">
                                        <div class="progress-bar <?= $barColor ?>" style="width: <?= $successRate ?>%; border-radius: 99px; background-image: linear-gradient(45deg,rgba(255,255,255,.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.15) 50%,rgba(255,255,255,.15) 75%,transparent 75%,transparent);"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 2. Évolution Académique Globale -->
        <div class="col-xl-6">
            <div class="modern-card border-0 shadow-lg border-top border-info border-4 h-100 animate-fade-in" style="border-radius: 24px !important;">
                <div class="modern-card-header bg-transparent p-4 border-bottom d-flex justify-content-between align-items-center" style="border-radius: 24px 24px 0 0 !important;">
                    <div>
                        <h5 class="fw-bold m-0 text-main-theme"><i class="bi bi-graph-up-arrow text-info me-2"></i><?= __('db_overall_evolution') ?></h5>
                        <p class="text-muted small m-0" style="font-size: 11px;"><?= __('db_evolution_subtitle') ?></p>
                    </div>
                </div>
                
                <div class="p-4">
                    <?php if (empty($seqAverages)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-calendar2-x fs-2 d-block mb-2 text-muted"></i>
                            <?= __('db_insufficient_eval') ?>
                        </div>
                    <?php else: ?>
                        <div class="row g-4 align-items-center">
                            <!-- Visual dynamic SVG Curve chart -->
                            <div class="col-lg-8">
                                <div class="p-3 bg-light bg-opacity-10" style="border-radius: 20px !important; border: 1px solid var(--border-color) !important;">
                                    <?php
                                        // Calculate points for the dynamic SVG Curve!
                                        $svgWidth = 500;
                                        $svgHeight = 130;
                                        $padTop = 20;
                                        $padBot = 20;
                                        $countSeq = count($seqAverages);
                                        $points = [];
                                        
                                        if ($countSeq > 1) {
                                            $vals = array_column($seqAverages, 'moyenne');
                                            $minVal = max(0, min($vals) - 1.5);
                                            $maxVal = min(20, max($vals) + 1.5);
                                            $range = $maxVal - $minVal;
                                            if ($range <= 0) $range = 1;
                                            
                                            foreach ($seqAverages as $idx => $sa) {
                                                $x = ($idx / ($countSeq - 1)) * $svgWidth;
                                                $norm = ($sa['moyenne'] - $minVal) / $range;
                                                $y = $svgHeight - ($norm * ($svgHeight - $padTop - $padBot)) - $padBot;
                                                $points[] = "$x,$y";
                                            }
                                            $path = "M " . implode(" L ", $points);
                                            $areaPath = $path . " L {$svgWidth},{$svgHeight} L 0,{$svgHeight} Z";
                                        } else {
                                            $path = "";
                                            $areaPath = "";
                                        }
                                    ?>
                                    
                                    <?php if ($countSeq > 1): ?>
                                        <div class="position-relative">
                                            <svg viewBox="0 0 500 130" class="w-100 h-auto" style="overflow: visible;">
                                                <defs>
                                                    <linearGradient id="curve-grad" x1="0" y1="0" x2="0" y2="1">
                                                        <stop offset="0%" stop-color="#0dcaf0" stop-opacity="0.35"/>
                                                        <stop offset="100%" stop-color="#0dcaf0" stop-opacity="0.01"/>
                                                    </linearGradient>
                                                    <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
                                                        <feGaussianBlur stdDeviation="3" result="blur" />
                                                        <feComposite in="SourceGraphic" in2="blur" operator="over"/>
                                                    </filter>
                                                </defs>
                                                <!-- Grid horizontal lines -->
                                                <line x1="0" y1="20" x2="500" y2="20" stroke="var(--border-color)" stroke-opacity="0.5" stroke-dasharray="4"/>
                                                <line x1="0" y1="65" x2="500" y2="65" stroke="var(--border-color)" stroke-opacity="0.5" stroke-dasharray="4"/>
                                                <line x1="0" y1="110" x2="500" y2="110" stroke="var(--border-color)" stroke-opacity="0.5" stroke-dasharray="4"/>
                                                
                                                <!-- Gradient area -->
                                                <path d="<?= $areaPath ?>" fill="url(#curve-grad)" />
                                                <!-- Main stroke line -->
                                                <path d="<?= $path ?>" fill="none" stroke="#0dcaf0" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round" filter="url(#glow)"/>
                                                
                                                <!-- Interactive circle markers -->
                                                <?php foreach ($points as $idx => $pt): 
                                                    list($cx, $cy) = explode(',', $pt);
                                                ?>
                                                    <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="6.5" fill="var(--bg-card)" stroke="#0dcaf0" stroke-width="3.5" class="transition-base cursor-pointer" style="transition: transform 0.18s; transform-origin: <?= $cx ?>px <?= $cy ?>px;" onmouseover="this.setAttribute('r', '8.5');" onmouseout="this.setAttribute('r', '6.5');"/>
                                                <?php endforeach; ?>
                                            </svg>
                                            
                                            <!-- Labels below chart -->
                                            <div class="d-flex justify-content-between align-items-center mt-3 px-1">
                                                <?php foreach ($seqAverages as $sa): ?>
                                                    <div class="text-center">
                                                        <span class="fw-black text-main-theme d-block" style="font-size: 11.5px;"><?= htmlspecialchars($sa['periode']) ?></span>
                                                        <span class="badge bg-soft-primary extra-small fw-bold"><?= number_format((float)$sa['moyenne'], 2, ',', ' ') ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="d-flex flex-wrap justify-content-between align-items-center py-2">
                                            <?php foreach ($seqAverages as $sa): ?>
                                                <div class="text-center p-3 rounded-4 bg-light flex-grow-1 mx-2">
                                                    <span class="text-muted d-block small mb-1"><?= htmlspecialchars($sa['periode']) ?></span>
                                                    <span class="h4 fw-black text-info m-0"><?= number_format((float)$sa['moyenne'], 2, ',', ' ') ?> / 20</span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Detailed Trend Report -->
                            <div class="col-lg-4 border-start border-light ps-lg-4">
                                <div class="p-4 rounded-4 bg-info bg-opacity-10 border border-info border-opacity-15 h-100" style="border-radius: 20px !important;">
                                    <h6 class="fw-bold text-info mb-2"><i class="bi bi-lightning-charge-fill me-1"></i><?= __('db_predictive_report') ?></h6>
                                    <p class="small text-main-theme mb-0 lh-base" style="font-size: 12px; font-weight: 500;">
                                        <?php 
                                            if ($countSeq >= 2) {
                                                $first = $seqAverages[0]['moyenne'];
                                                $last = $seqAverages[$countSeq - 1]['moyenne'];
                                                $diff = $last - $first;
                                                if ($diff > 0) {
                                                    echo __('db_trend_up', ['diff' => number_format($diff, 2)]);
                                                } elseif ($diff < 0) {
                                                    echo __('db_trend_down', ['diff' => number_format(abs($diff), 2)]);
                                                } else {
                                                    echo __('db_trend_stable', ['last' => number_format($last, 2)]);
                                                }
                                            } else {
                                                echo __('db_trend_insufficient');
                                            }
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>


    <!-- Finances : KPI Cards (SaaS/ERP Modern style) -->
    <div class="row g-3 g-md-4 mb-4" data-views="finances">
        <!-- Recettes Globales -->
        <div class="col-12 col-md-4">
            <div class="erp-stat-card card-success">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div class="kpi-value" style="font-size: 1.6rem;"><?= number_format($totalGeneralCollected, 0, ',', ' ') ?> <span class="small font-normal text-muted" style="font-size: 10px;">FCFA</span></div>
                    <div class="kpi-label"><?= __('dashboard_financial_global_revenue') ?></div>
                </div>
                <div class="kpi-trend text-success">
                    <?= __('dashboard_financial_tuition_fees') ?> (<?= number_format($totalTuitionCollected, 0, ',', ' ') ?>) + <?= __('dashboard_financial_registration_fees') ?> (<?= number_format($totalRegistrationCollected, 0, ',', ' ') ?>)
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
                    <div class="kpi-label"><?= __('dashboard_financial_yearly_expenses') ?></div>
                </div>
                <div class="kpi-trend text-danger">
                    <?= __('dashboard_financial_this_month_colon') ?> <?= number_format($monthlyExpenses, 0, ',', ' ') ?> FCFA
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
                    <div class="kpi-label"><?= __('dashboard_financial_real_balance') ?></div>
                </div>
                <div class="kpi-trend <?= $netBalance >= 0 ? 'text-info' : 'text-danger' ?>">
                    <i class="bi <?= $netBalance >= 0 ? 'bi-plus-circle' : 'bi-dash-circle' ?>"></i> <?= __('dashboard_financial_net_situation') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Scolarité : KPI Cards (SaaS/ERP Modern style) -->
    <div class="row g-3 g-md-4 mb-4" data-views="scolarite">
        <!-- Scolarité Attendue -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-primary">
                <div>
                    <div class="erp-icon-box">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div class="kpi-value" style="font-size: 1.45rem;"><?= number_format($totalExpected, 0, ',', ' ') ?> <span class="small font-normal text-muted" style="font-size: 10px;">FCFA</span></div>
                    <div class="kpi-label"><?= __('dashboard_financial_expected_tuition') ?></div>
                </div>
                <?php if (!empty($totalReductions) && $totalReductions > 0): ?>
                    <div class="kpi-trend text-muted">
                        <?= __('dashboard_financial_gross_colon') ?> <?= number_format($totalExpectedGross, 0, ',', ' ') ?> (-<?= number_format($totalReductions, 0, ',', ' ') ?>)
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
                    <div class="kpi-label"><?= __('dashboard_financial_collected_tuition') ?></div>
                </div>
                <div class="kpi-trend text-success">
                    <?= __('dashboard_financial_recovery_rate_colon') ?> <?= number_format($collectionRate, 1) ?>%
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
                    <div class="kpi-label"><?= __('dashboard_financial_remaining_to_recover') ?></div>
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
                    <div class="kpi-value" style="font-size: 1.45rem;"><?= number_format($totalReductions + $totalScholarships, 0, ',', ' ') ?> <span class="small font-normal text-muted" style="font-size: 10px;">FCFA</span></div>
                    <div class="kpi-label"><?= __('dashboard_financial_discounts_scholarships') ?></div>
                </div>
                <div class="kpi-trend text-warning">
                    Réd. : <?= number_format($totalReductions, 0, ',', ' ') ?> | Bourses : <?= number_format($totalScholarships, 0, ',', ' ') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Scolarité: Tables & Analyse -->
    <div class="row g-4 mb-4" data-views="scolarite">
        <!-- Situation des Tranches -->
        <div class="col-12">
            <div class="modern-card border-0 shadow-sm p-4">
                <h6 class="fw-bold text-main-theme mb-3"><i class="bi bi-bar-chart-steps text-primary me-2"></i><?= __('dashboard_financial_installments_situation') ?></h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th><?= __('installment') ?></th>
                                <th class="text-end"><?= __('expected_amount') ?></th>
                                <th class="text-end"><?= __('paid_amount') ?></th>
                                <th class="text-end"><?= __('remaining_amount') ?></th>
                                <th style="width: 250px;"><?= __('progress') ?></th>
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
                                    <td class="fw-bold text-main-theme"><?= __('installment') ?> #<?= htmlspecialchars($ts['installment_number']) ?></td>
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
                                    <td colspan="5" class="text-center text-muted py-3"><?= __('dashboard_financial_no_installments') ?></td>
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
                    <h6 class="fw-bold text-main-theme mb-0"><i class="bi bi-door-open text-danger me-2"></i><?= __('dashboard_financial_insolvency_by_class') ?></h6>
                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill"><?= number_format($totalInsolventAmount, 0, ',', ' ') ?> FCFA</span>
                </div>
                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th><?= __('class') ?></th>
                                <th class="text-center"><?= __('students') ?></th>
                                <th class="text-end"><?= __('amount_due') ?></th>
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
                                    <td colspan="3" class="text-center text-muted py-3"><?= __('dashboard_financial_no_insolvent_class') ?></td>
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
                <h6 class="fw-bold text-main-theme mb-3"><i class="bi bi-people-fill text-warning me-2"></i><?= __('dashboard_financial_top_insolvents') ?></h6>
                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th><?= __('student') ?></th>
                                <th><?= __('class') ?></th>
                                <th class="text-center"><?= __('installments') ?></th>
                                <th class="text-end"><?= __('delay_due') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topInsolvents as $ti): ?>
                                <tr class="border-bottom border-theme-light">
                                    <td class="fw-bold text-main-theme"><?= htmlspecialchars(strtoupper($ti['student_nom']) . ' ' . ucwords(strtolower($ti['student_prenom']))) ?></td>
                                    <td><span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill"><?= htmlspecialchars($ti['class_name']) ?></span></td>
                                    <td class="text-center text-muted-theme fw-semibold"><?= $ti['unpaid_installments_count'] ?> <?= __('installments') ?></td>
                                    <td class="text-end text-danger fw-bold"><?= number_format($ti['amount_due'], 0, ',', ' ') ?> FCFA</td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($topInsolvents)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3"><?= __('dashboard_financial_no_delays') ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Recettes & Dépenses par Période -->
    <div class="row g-3 mb-4" data-views="finances">
        <div class="col-lg-6">
            <div class="modern-card border-0 shadow-sm p-4 h-100" style="border-radius: 20px !important;">
                <h6 class="fw-bold text-main-theme mb-3"><i class="bi bi-calendar-range text-primary me-2"></i><?= __('dashboard_financial_revenue_detail') ?></h6>
                <div class="row g-2">
                    <div class="col-4">
                        <div class="p-3 bg-light bg-opacity-25 rounded-4 border text-center h-100">
                            <span class="text-muted-theme small fw-bold d-block mb-1"><?= __('today') ?></span>
                            <span class="fw-extrabold text-main-theme small d-block"><?= number_format($dailyCollections, 0, ',', ' ') ?></span>
                            <small class="text-muted" style="font-size: 8px;">FCFA</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-light bg-opacity-25 rounded-4 border text-center h-100">
                            <span class="text-muted-theme small fw-bold d-block mb-1"><?= __('this_week') ?></span>
                            <span class="fw-extrabold text-main-theme small d-block"><?= number_format($weeklyCollections, 0, ',', ' ') ?></span>
                            <small class="text-muted" style="font-size: 8px;">FCFA</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-light bg-opacity-25 rounded-4 border text-center h-100">
                            <span class="text-muted-theme small fw-bold d-block mb-1"><?= __('this_month') ?></span>
                            <span class="fw-extrabold text-main-theme small d-block"><?= number_format($monthlyCollections, 0, ',', ' ') ?></span>
                            <small class="text-muted" style="font-size: 8px;">FCFA</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="modern-card border-0 shadow-sm p-4 h-100" style="border-radius: 20px !important;">
                <h6 class="fw-bold text-main-theme mb-3"><i class="bi bi-wallet2 text-danger me-2"></i><?= __('dashboard_financial_expenses_detail') ?></h6>
                <div class="row g-2">
                    <div class="col-4">
                        <div class="p-3 bg-light bg-opacity-25 rounded-4 border text-center h-100">
                            <span class="text-muted-theme small fw-bold d-block mb-1"><?= __('today') ?></span>
                            <span class="fw-extrabold text-main-theme small d-block"><?= number_format($dailyExpenses, 0, ',', ' ') ?></span>
                            <small class="text-muted" style="font-size: 8px;">FCFA</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-light bg-opacity-25 rounded-4 border text-center h-100">
                            <span class="text-muted-theme small fw-bold d-block mb-1"><?= __('this_week') ?></span>
                            <span class="fw-extrabold text-main-theme small d-block"><?= number_format($weeklyExpenses, 0, ',', ' ') ?></span>
                            <small class="text-muted" style="font-size: 8px;">FCFA</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-light bg-opacity-25 rounded-4 border text-center h-100">
                            <span class="text-muted-theme small fw-bold d-block mb-1"><?= __('this_month') ?></span>
                            <span class="fw-extrabold text-main-theme small d-block"><?= number_format($monthlyExpenses, 0, ',', ' ') ?></span>
                            <small class="text-muted" style="font-size: 8px;">FCFA</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row for Financial View -->
    <div class="row g-4 mb-4" data-views="finances">
        <!-- Collection Rate Donut -->
        <div class="col-lg-3">
            <div class="modern-card border-0 shadow-sm p-4 h-100" style="border-radius: 20px !important;">
                <h6 class="fw-black text-main-theme mb-4"><?= __('collection_rate') ?></h6>
                <div class="d-flex align-items-center justify-content-center" style="height:220px; position:relative;">
                    <canvas id="adminCollectionRateChart"></canvas>
                    <div class="position-absolute text-center">
                        <div class="fw-black fs-3 text-success"><?= number_format($collectionRate, 1) ?>%</div>
                        <div class="small text-muted-theme"><?= __('collection_rate') ?></div>
                    </div>
                </div>
                <div class="d-flex justify-content-center gap-4 mt-3">
                    <div class="text-center">
                        <div class="small text-muted-theme"><?= __('total_collected') ?></div>
                        <div class="fw-bold text-success small"><?= number_format($totalTuitionCollected, 0, ',', ' ') ?></div>
                    </div>
                    <div class="text-center">
                        <div class="small text-muted-theme"><?= __('total_expected') ?></div>
                        <div class="fw-bold text-primary small"><?= number_format($totalExpected, 0, ',', ' ') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expenses by Category Doughnut -->
        <div class="col-lg-3">
            <div class="modern-card border-0 shadow-sm p-4 h-100" style="border-radius: 20px !important;">
                <h6 class="fw-black text-main-theme mb-4"><?= __('dashboard_financial_expenses_distribution') ?></h6>
                <div class="d-flex align-items-center justify-content-center" style="height:220px; position:relative;">
                    <canvas id="adminExpensesCategoryChart"></canvas>
                    <?php if (empty($expensesByCategory)): ?>
                        <div class="position-absolute text-center text-muted small"><?= __('dashboard_financial_no_active_expenses') ?></div>
                    <?php endif; ?>
                </div>
                <div class="text-center mt-3">
                    <span class="small text-muted-theme"><?= __('dashboard_financial_total_expenses_colon') ?></span>
                    <span class="fw-bold text-danger small"><?= number_format($totalExpenses, 0, ',', ' ') ?> FCFA</span>
                </div>
            </div>
        </div>

        <!-- Monthly Evolution Chart -->
        <div class="col-lg-6">
            <div class="modern-card border-0 shadow-sm p-4 h-100" style="border-radius: 20px !important;">
                <h6 class="fw-black text-main-theme mb-4"><?= __('dashboard_financial_monthly_comparison') ?></h6>
                <div style="height:220px;">
                    <canvas id="adminMonthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Modes de règlement, Bourses & Réductions -->
    <div class="row g-4 mb-4" data-views="finances">
        <!-- Modes de Règlement -->
        <div class="col-lg-4">
            <div class="modern-card border-0 shadow-sm p-4 h-100" style="border-radius: 20px !important;">
                <h6 class="fw-bold text-main-theme mb-3"><i class="bi bi-credit-card-2-back text-primary me-2"></i><?= __('dashboard_financial_payment_modes') ?></h6>
                <div style="height: 180px; position: relative;" class="d-flex align-items-center justify-content-center">
                    <canvas id="adminPaymentMethodChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Motifs des Réductions -->
        <div class="col-lg-4">
            <div class="modern-card border-0 shadow-sm p-4 h-100" style="border-radius: 20px !important;">
                <h6 class="fw-bold text-main-theme mb-3"><i class="bi bi-percent text-warning me-2"></i><?= __('dashboard_financial_discounts_reasons') ?></h6>
                <div style="height: 180px; position: relative;" class="d-flex align-items-center justify-content-center">
                    <?php if (empty($reductionsRepartition)): ?>
                        <div class="text-center text-muted small py-5"><?= __('dashboard_financial_no_active_discounts') ?></div>
                    <?php else: ?>
                        <canvas id="adminReductionsChart"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Motifs des Bourses -->
        <div class="col-lg-4">
            <div class="modern-card border-0 shadow-sm p-4 h-100" style="border-radius: 20px !important;">
                <h6 class="fw-bold text-main-theme mb-3"><i class="bi bi-award text-success me-2"></i><?= __('dashboard_financial_scholarships_reasons') ?></h6>
                <div style="height: 180px; position: relative;" class="d-flex align-items-center justify-content-center">
                    <?php if (empty($scholarshipsRepartition)): ?>
                        <div class="text-center text-muted small py-5"><?= __('dashboard_financial_no_active_scholarships') ?></div>
                    <?php else: ?>
                        <canvas id="adminScholarshipsChart"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Class enrollment stats breakdown -->
    <div class="row g-4 mb-4" data-views="inscriptions">
        <div class="col-12">
            <div class="modern-card border-0 shadow-sm" style="border-radius: 20px !important;">
                <div class="card-header bg-transparent border-0 px-4 pt-4 pb-0 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h6 class="fw-black text-main-theme mb-1"><?= __('class_registration_stats') ?></h6>
                        <p class="text-muted-theme small mb-0"><?= __('dashboard_admin_class_registration_status') ?> (<?= __('policy') ?> : <?= htmlspecialchars(ucfirst($policy)) ?>)</p>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="border-bottom border-theme-light">
                                    <th class="ps-4 py-3 fw-semibold text-muted-theme small text-uppercase"><?= __('class') ?></th>
                                    <th class="py-3 fw-semibold text-muted-theme text-center small text-uppercase"><?= __('total_students') ?></th>
                                    <th class="py-3 fw-semibold text-muted-theme text-center small text-uppercase"><?= __('paid_registrations') ?></th>
                                    <th class="py-3 fw-semibold text-muted-theme text-center small text-uppercase"><?= __('unpaid_registrations') ?></th>
                                    <th class="py-3 fw-semibold text-muted-theme small text-uppercase"><?= __('dashboard_financial_payment_rate') ?></th>
                                    <th class="pe-4 py-3 fw-semibold text-muted-theme text-end small text-uppercase"><?= __('collected_amount') ?></th>
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
            <div class="modern-card border-0 shadow-sm" style="border-radius: 20px !important;">
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
                                                            <i class="bi bi-journal-check me-1"></i><?= __('registration') ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge rounded-pill text-uppercase px-2 py-1" style="font-size: 0.65rem; background-color: rgba(16, 185, 129, 0.1); color: #059669;">
                                                            <i class="bi bi-cash-coin me-1"></i><?= __('tuition') ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $methodIcons = ['especes' => 'bi-cash', 'mobile_money' => 'bi-phone', 'virement' => 'bi-bank', 'cheque' => 'bi-credit-card-2-front'];
                                                $methodLabels = ['especes' => __('cash'), 'mobile_money' => __('mobile_money'), 'virement' => __('bank_transfer'), 'cheque' => __('cheque')];
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

    <!-- ========================================== -->
    <!-- SECTION : RESSOURCES HUMAINES (VUE PILOTAGE)-->
    <!-- ========================================== -->
    <!-- KPI Row for HR View -->
    <div class="row g-3 mb-4 kpi-row" data-views="rh">
        <!-- Total Users -->
        <div class="col-sm-6 col-xl-3 stats-col">
            <div class="modern-card kpi-card shadow-sm stats-card kpi-stat-card border-primary" style="min-height: 140px;">
                <div class="kpi-icon-wrapper bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="kpi-value" data-count-up="<?= (int) $stats_users ?>"><?= $stats_users ?></div>
                <div class="kpi-label"><?= __('total_users') ?></div>
            </div>
        </div>
        <!-- Active Teachers -->
        <div class="col-sm-6 col-xl-3 stats-col">
            <div class="modern-card kpi-card shadow-sm stats-card kpi-stat-card border-success" style="min-height: 140px;">
                <div class="kpi-icon-wrapper bg-success bg-opacity-10 text-success">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
                <div class="kpi-value" data-count-up="<?= (int) $stats_teachers ?>"><?= $stats_teachers ?></div>
                <div class="kpi-label"><?= __('active_teachers') ?></div>
            </div>
        </div>
        <!-- Administrative Personnel -->
        <div class="col-sm-6 col-xl-3 stats-col">
            <div class="modern-card kpi-card shadow-sm stats-card kpi-stat-card border-warning" style="min-height: 140px;">
                <div class="kpi-icon-wrapper bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <div class="kpi-value" data-count-up="<?= (int) $adminsCount ?>"><?= $adminsCount ?></div>
                <div class="kpi-label"><?= __('dashboard_admin_staff_label') ?></div>
            </div>
        </div>
        <!-- Teachers without assignments -->
        <div class="col-sm-6 col-xl-3 stats-col">
            <div class="modern-card kpi-card shadow-sm stats-card kpi-stat-card border-danger" style="min-height: 140px;">
                <div class="kpi-icon-wrapper bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-person-x-fill"></i>
                </div>
                <div class="kpi-value text-danger" data-count-up="<?= (int) $teachers_without_assignment ?>"><?= $teachers_without_assignment ?></div>
                <div class="kpi-label"><?= __('dashboard_admin_unassigned_teachers_label') ?></div>
            </div>
        </div>
    </div>

    <!-- Charts & Tables for HR View -->
    <div class="row g-4 mb-5" data-views="rh">
        <div class="col-xl-6">
            <div class="modern-card border-0 shadow-lg border-top border-warning border-4 h-100">
                <div class="modern-card-header bg-transparent p-4 border-bottom">
                    <h5 class="fw-bold m-0 text-main-theme"><i class="bi bi-pie-chart-fill text-warning me-2"></i><?= __('dashboard_admin_role_distribution') ?></h5>
                </div>
                <div class="p-4" style="height: 320px; position: relative;">
                    <canvas id="adminRoleDistributionChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="modern-card border-0 shadow-lg border-top border-info border-4 h-100">
                <div class="modern-card-header bg-transparent p-4 border-bottom">
                    <h5 class="fw-bold m-0 text-main-theme"><i class="bi bi-list-stars text-info me-2"></i><?= __('dashboard_admin_role_staff_details') ?></h5>
                </div>
                <div class="p-4" style="max-height: 320px; overflow-y: auto;">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3 text-main-theme"><?= __('role') ?></th>
                                <th class="text-end pe-4 py-3 text-main-theme"><?= __('users_count') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($roleDistribution as $roleRow): ?>
                                <tr>
                                    <td class="ps-4 py-3 fw-bold text-capitalize text-main-theme"><?= __(strtolower($roleRow['role'])) ?></td>
                                    <td class="text-end pe-4 py-3 fw-black text-primary"><?= (int)$roleRow['count'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php if (\App\Core\Session::get('user_role') === 'superadmin'): ?>
        <div class="row g-4 mb-5" data-views="global,rh">
            <div class="col-xl-12">
                <div class="modern-card border-0 shadow-sm">
                    <div class="modern-card-header bg-white p-4 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold m-0"><?= __('teacher_activity_analytics') ?></h5>
                                <p class="text-muted small m-0"><?= __('teacher_activity_subtitle') ?></p>
                            </div>
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 fw-bold">
                                <i class="bi bi-clock-history me-1"></i> <?= __('live_update') ?>
                            </span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-muted small text-uppercase">
                                    <th class="ps-4 border-0 py-3"><?= __('teacher') ?></th>
                                    <th class="border-0 py-3 text-center"><?= __('weekly_actions_short') ?></th>
                                    <th class="border-0 py-3 text-center"><?= __('monthly_actions_short') ?></th>
                                    <th class="border-0 py-3"><?= __('usage_frequency') ?></th>
                                    <th class="border-0 py-3 pe-4"><?= __('last_activity') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentTeacherActivity as $row): ?>
                                    <tr>
                                        <td class="ps-4 border-bottom-0 py-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold"
                                                    style="width: 38px; height: 38px;">
                                                    <?= strtoupper(substr($row['teacher_name'], 0, 1)) ?>
                                                </div>
                                                <div class="fw-bold"><?= h((string) $row['teacher_name']) ?></div>
                                            </div>
                                        </td>
                                        <td class="text-center border-bottom-0"><?= (int) $row['weekly_actions'] ?></td>
                                        <td class="text-center border-bottom-0"><span
                                                class="fw-bold text-primary"><?= (int) $row['monthly_actions'] ?></span></td>
                                        <td class="border-bottom-0">
                                            <span
                                                class="badge rounded-pill px-3 py-2 <?= (int) $row['active_days'] >= 20 ? 'bg-success bg-opacity-10 text-success' : ((int) $row['active_days'] >= 10 ? 'bg-primary bg-opacity-10 text-primary' : 'bg-warning bg-opacity-10 text-warning') ?>">
                                                <?= h((string) $row['frequency_label']) ?>
                                            </span>
                                        </td>
                                        <td class="pe-4 border-bottom-0 small text-muted">
                                            <?= h($formatDateTime($row['last_activity_at'] ?? null)) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Vue Pédagogie : Anomalies (Matières sans prof & Inactives) -->
    <div class="row g-4 mb-5" data-views="pedagogie">
        <div class="col-12">
            <!-- Matières sans prof (Priorité Anomalies) -->
            <div class="modern-card mb-4 border-0 shadow-sm border-top border-warning border-4">
                <div class="modern-card-header border-bottom bg-transparent py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-patch-exclamation-fill text-warning fs-5"></i>
                        <h5 class="modern-card-title m-0 text-main-theme"><?= __('subjects_without_teachers') ?></h5>
                    </div>
                    <span class="badge bg-warning bg-opacity-10 text-warning fw-bold"><?= count($unassignedSubjects) ?> <?= __('anomalies') ?></span>
                </div>
                <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th class="ps-4 py-3 text-main-theme"><?= __('affected_class') ?></th>
                                <th class="py-3 text-main-theme"><?= __('subject_to_provide') ?></th>
                                <th class="text-end pe-4 py-3 text-main-theme"><?= __('action_label') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($unassignedSubjects)): ?>
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-main-theme"><i class="bi bi-check-circle-fill text-success fs-2 d-block mb-2 text-main-theme"></i><?= __('all_subjects_covered') ?></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($unassignedSubjects as $us): ?>
                                    <tr>
                                        <td class="ps-4 py-3"><span class="badge bg-soft-primary px-3 rounded-pill text-main-theme"><?= h($us['class_name']) ?></span></td>
                                        <td class="py-3">
                                            <div class="fw-bold text-main-theme"><?= h($us['subject_name']) ?></div>
                                        </td>
                                        <td class="text-end pe-4 py-3">
                                            <a href="/teachers/select_teacher?subject_id=<?= $us['subject_id'] ?>&class_id=<?= $us['class_id'] ?>" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold">
                                                <i class="bi bi-person-plus me-1"></i> <?= __('assign') ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Matières Inactives (Superadmin Only) -->
            <?php if (\App\Core\Session::get('user_role') === 'superadmin' && !empty($inactive_subjects_list)): ?>
            <div class="modern-card mb-4 border-0 shadow-sm border-top border-danger border-4">
                <div class="modern-card-header border-bottom bg-transparent py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-pause-circle-fill text-danger fs-5"></i>
                        <h5 class="modern-card-title m-0 text-main-theme"><?= __('inactive_subjects') ?></h5>
                    </div>
                    <span class="badge bg-danger bg-opacity-10 text-danger fw-bold"><?= count($inactive_subjects_list) ?></span>
                </div>
                <div class="p-3">
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($inactive_subjects_list as $is): ?>
                            <div class="d-flex align-items-center gap-2 bg-light bg-opacity-50 border rounded-pill px-3 py-2 animate-fade-in">
                                <span class="fw-bold text-muted small"><?= h($is['nom']) ?></span>
                                <a href="/subjects/toggleStatus?id=<?= $is['id'] ?>" 
                                   class="btn btn-sm btn-success rounded-circle p-0 d-flex align-items-center justify-content-center btn-confirm-toggle" 
                                   style="width: 24px; height: 24px;"
                                   data-confirm="<?= __('activate_subject_confirm', ['name' => $is['nom']]) ?>"
                                   title="<?= __('activate') ?>">
                                    <i class="bi bi-play-fill"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Vue RH : Supervision Enseignants & Backup Center -->
    <div class="row g-4 mb-5" data-views="rh">
        <div class="col-xl-8">
            <!-- Suivi Progression Enseignants -->
            <div class="modern-card mb-4 border-0 shadow-lg border-top border-success border-4">
                <div class="modern-card-header bg-transparent p-4 border-bottom">
                    <h5 class="fw-bold m-0 text-main-theme"><?= __('teacher_progress_tracking') ?></h5>
                </div>
                <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th class="ps-4 border-0 py-3"><?= __('teacher') ?></th>
                                <th class="border-0 py-3 text-center"><?= __('entries') ?></th>
                                <th class="border-0 py-3"><?= __('progress') ?></th>
                                <th class="border-0 py-3 pe-4"><?= __('status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teacherMetrics as $m): ?>
                                <tr>
                                    <td class="ps-4 border-0 py-3">
                                        <div class="fw-bold text-main-theme"><?= h($m['teacher_name']) ?></div>
                                        <div class="small text-muted-theme"><?= $m['classes_count'] ?> <?= __('classes') ?></div>
                                    </td>
                                    <td class="text-center border-0 fw-bold text-main-theme">
                                        <?= $m['filled_count'] ?>
                                        <small class="text-muted-theme">/<?= $m['expected_count'] ?></small>
                                    </td>
                                    <td class="border-0" style="min-width: 150px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px; border-radius: 10px; background: var(--border-color);">
                                                <div class="progress-bar bg-primary" style="width: <?= $m['progress_percent'] ?>%"></div>
                                            </div>
                                            <span class="small fw-bold text-main-theme"><?= $m['progress_percent'] ?>%</span>
                                        </div>
                                    </td>
                                    <td class="pe-4 border-0"><span class="level-badge <?= nm_level_class($m['level_label']) ?>"><?= __($m['level_label']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <!-- Enseignant à l'honneur -->
            <?php if ($topTeacher): ?>
                <div class="modern-card mb-4 border-0 shadow-sm overflow-hidden bg-primary text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;">
                    <div class="modern-card-body p-4 position-relative">
                        <div class="d-flex align-items-center gap-2 mb-3 opacity-75">
                            <i class="bi bi-star-fill text-warning"></i>
                            <span class="fw-bold small text-uppercase letter-spacing-1"><?= __('top_performing_teacher') ?></span>
                        </div>
                        <h3 class="fw-bold mb-1 lh-sm"><?= h($topTeacher['teacher_name']) ?></h3>
                        <div class="d-flex justify-content-between align-items-end mt-4">
                            <div>
                                <div class="fs-1 fw-black lh-1" data-count-up="<?= (int) $topTeacher['progress_percent'] ?>" data-suffix="%"><?= $topTeacher['progress_percent'] ?>%</div>
                                <div class="small opacity-75 mt-1"><?= $topTeacher['filled_count'] ?> <?= __('entries') ?></div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold"><?= $topTeacher['classes_count'] ?></div>
                                <div class="small opacity-75"><?= __('classes') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Supervision rapide - RESPONSIVE -->
            <div class="modern-card border-0 shadow-sm mb-3 mb-md-4">
                <div class="modern-card-header bg-transparent p-3 p-md-4 border-bottom">
                    <h5 class="fw-bold m-0 text-main-theme fs-6 fs-md-5"><?= __('supervision_actions') ?></h5>
                </div>
                <div class="modern-card-body p-2 p-md-4">
                    <div class="d-grid gap-2 gap-md-3">
                        <a href="/teachers" class="admin-pilot-link">
                            <div>
                                <div class="fw-bold text-main-theme"><?= __('teachers') ?></div>
                                <div class="small text-muted-theme d-none d-sm-block"><?= __('teachers_to_contact', ['count' => $teachersUnder50]) ?></div>
                            </div>
                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill"><?= (int) $teachersUnder50 ?></span>
                        </a>
                        <a href="/notes" class="admin-pilot-link">
                            <div>
                                <div class="fw-bold text-main-theme"><?= __('notes_management') ?></div>
                                <div class="small text-muted-theme d-none d-sm-block"><?= __('processing_in_progress') ?></div>
                            </div>
                            <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill"><?= (int) $globalPending ?></span>
                        </a>
                        <a href="/bulletins" class="admin-pilot-link">
                            <div>
                                <div class="fw-bold text-main-theme"><?= __('reports_ready') ?></div>
                                <div class="small text-muted-theme d-none d-sm-block"><?= __('final_verification_recommended') ?></div>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Backup Status (Superadmin only) -->
            <?php if (\App\Core\Session::get('user_role') === 'superadmin'): ?>
                <div class="modern-card border-0 shadow-sm">
                    <div class="modern-card-header bg-white p-4 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold m-0"><?= __('backup_center') ?></h5>
                            <?php $stateKey = match ($backupOverview['freshness_state'] ?? 'unknown') { 'success' => 'backup_fresh', 'warning' => 'backup_warning', 'stale' => 'backup_stale', 'failed' => 'status_failed', default => 'status_unknown'}; ?>
                            <span class="badge rounded-pill px-3 py-2 <?= nm_backup_state_class((string) ($backupOverview['freshness_state'] ?? 'unknown')) ?>"><?= __($stateKey) ?></span>
                        </div>
                    </div>
                    <div class="modern-card-body p-4">
                        <div class="d-flex flex-column gap-3 mb-4">
                            <div class="p-3 rounded-4 bg-light bg-opacity-10 border border-white border-opacity-10">
                                <div class="small text-muted mb-1"><?= __('last_backup_run') ?></div>
                                <div class="fw-bold d-flex align-items-center gap-2">
                                    <i class="bi bi-calendar3 text-primary"></i>
                                    <?= h($formatDateTime($backupOverview['last_run_at'] ?? null)) ?>
                                </div>
                            </div>
                            <div class="p-3 rounded-4 bg-light bg-opacity-10 border border-white border-opacity-10">
                                <div class="small text-muted mb-1 text-truncate"><?= __('configured_repository') ?></div>
                                <div class="fw-bold text-truncate d-flex align-items-center gap-2">
                                    <i class="bi bi-github text-dark"></i>
                                    <?= h((string) ($backupOverview['repository_label'] ?? __('not_available'))) ?>
                                </div>
                            </div>
                        </div>
                        <a href="/settings#tab-automation" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-gear-fill"></i> <?= __('backup_open_settings') ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-count-up]').forEach((element, index) => {
            const target = Number(element.dataset.countUp || '0');
            const suffix = element.dataset.suffix || '';
            if (!Number.isFinite(target)) {
                return;
            }

            const duration = 800;
            const start = performance.now();

            const tick = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                const easeProgress = 1 - Math.pow(1 - progress, 5); // easeOutQuint
                const value = Math.round(target * easeProgress);
                element.childNodes[0].nodeValue = `${value}${suffix}`;
                if (progress < 1) {
                    requestAnimationFrame(tick);
                }
            };

            setTimeout(() => requestAnimationFrame(tick), 200 + (index * 50));
        });

        // View Selector Logic
        const viewSelector = document.getElementById('dashboard-view-selector');
        if (viewSelector) {
            const buttons = viewSelector.querySelectorAll('[data-view]');
            const viewableElements = document.querySelectorAll('[data-views]');

            const applyView = (selectedView) => {
                // Update button active states
                buttons.forEach(btn => {
                    if (btn.dataset.view === selectedView) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });

                // Show/hide sections based on data-views attribute
                viewableElements.forEach(el => {
                    const views = el.dataset.views.split(',');
                    if (views.includes(selectedView)) {
                        el.style.display = '';
                    } else {
                        el.style.display = 'none';
                    }
                });

                // Initialize charts dynamically when the view is changed (to avoid animation issues with display:none)
                if (selectedView === 'finances') {
                    initFinancialCharts();
                } else if (selectedView === 'rh') {
                    initHRCharts();
                }
            };

            buttons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const view = btn.dataset.view;
                    applyView(view);
                    // Save to local storage for persistence across reloads
                    localStorage.setItem('admin_dashboard_active_view', view);
                });
            });

            // Restore last active view or default to general
            let activeView = localStorage.getItem('admin_dashboard_active_view') || 'general';
            if (activeView === 'academic' || activeView === 'global' || activeView === 'financial') {
                activeView = 'general';
            }
            applyView(activeView);
        }
    });

    let financialChartsInitialized = false;
    let hrChartsInitialized = false;

    const initFinancialCharts = () => {
        if (financialChartsInitialized) return;
        financialChartsInitialized = true;

        // Load Chart.js dynamically if not already loaded
        if (typeof Chart === 'undefined') {
            const script = document.createElement('script');
            script.src = "https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js";
            script.onload = () => buildFinancialCharts();
            document.head.appendChild(script);
        } else {
            buildFinancialCharts();
        }
    };

    const buildFinancialCharts = () => {
        const pmCtx = document.getElementById('adminPaymentMethodChart');
        if (pmCtx) {
            const dataPay = <?= json_encode($paymentMethodRepartition) ?>;
            const methodLabels = {
                especes: '<?= addslashes((string) __('cash')) ?>',
                mobile_money: '<?= addslashes((string) __('mobile_money')) ?>',
                virement: '<?= addslashes((string) __('bank_transfer')) ?>',
                cheque: '<?= addslashes((string) __('cheque')) ?>'
            };
            new Chart(pmCtx, {
                type: 'doughnut',
                data: {
                    labels: dataPay.map(x => methodLabels[x.payment_method] || x.payment_method || '<?= addslashes((string) __('other')) ?>'),
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

        const collectionCtx = document.getElementById('adminCollectionRateChart');
        if (collectionCtx) {
            const collected = <?= json_encode($totalTuitionCollected) ?>;
            const expected = <?= json_encode($totalExpected) ?>;
            const remaining = Math.max(0, expected - collected);

            new Chart(collectionCtx, {
                type: 'doughnut',
                data: {
                    labels: ['<?= addslashes((string) __('paid_amount')) ?>', '<?= addslashes((string) __('remaining_amount')) ?>'],
                    datasets: [{
                        data: [collected, remaining],
                        backgroundColor: ['#22c55e', 'rgba(15, 23, 42, 0.08)'],
                        borderColor: ['#ffffff', '#ffffff'],
                        borderWidth: 2,
                        cutout: '78%',
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => {
                                    const value = ctx.parsed;
                                    return `${ctx.label}: ${new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', maximumFractionDigits: 0 }).format(value)}`;
                                }
                            }
                        }
                    }
                }
            });
        }

        const monthlyCtx = document.getElementById('adminMonthlyChart');
        if (monthlyCtx) {
            const monthlyData = <?= json_encode($monthlyPayments) ?>;
            const monthlyExpData = <?= json_encode($monthlyExpensesHist) ?>;
            
            // Union des mois
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

            new Chart(monthlyCtx, {
                type: 'bar',
                data: {
                    labels: monthLabels,
                    datasets: [
                        {
                            label: '<?= addslashes((string) __('dashboard_financial_global_revenue')) ?>',
                            data: monthTotals,
                            backgroundColor: 'rgba(16, 185, 129, 0.85)',
                            borderRadius: 6
                        },
                        {
                            label: '<?= addslashes((string) __('dashboard_financial_yearly_expenses')) ?>',
                            data: monthExpTotals,
                            backgroundColor: 'rgba(239, 68, 68, 0.85)',
                            borderRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // --- Doughnut: Expenses by Category ---
        const expCatCtx = document.getElementById('adminExpensesCategoryChart');
        const expCatData = <?= json_encode($expensesByCategory) ?>;
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
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: true }
                    }
                }
            });
        }

        const redCtx = document.getElementById('adminReductionsChart');
        if (redCtx) {
            const rawReductions = <?= json_encode($reductionsRepartition) ?>;
            new Chart(redCtx, {
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

        const schCtx = document.getElementById('adminScholarshipsChart');
        if (schCtx) {
            const rawScholarships = <?= json_encode($scholarshipsRepartition) ?>;
            new Chart(schCtx, {
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
    };

    const initHRCharts = () => {
        if (hrChartsInitialized) return;
        hrChartsInitialized = true;

        if (typeof Chart === 'undefined') {
            const script = document.createElement('script');
            script.src = "https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js";
            script.onload = () => buildHRCharts();
            document.head.appendChild(script);
        } else {
            buildHRCharts();
        }
    };

    const buildHRCharts = () => {
        const roleCtx = document.getElementById('adminRoleDistributionChart');
        if (roleCtx) {
            const roleData = <?= json_encode($roleDistribution) ?>;
            const roleLabels = {
                superadmin: '<?= addslashes((string) __('superadmin')) ?>',
                teacher: '<?= addslashes((string) __('teacher')) ?>',
                financial: '<?= addslashes((string) __('financial')) ?>',
                it_manager: '<?= addslashes((string) __('it_manager')) ?>'
            };
            new Chart(roleCtx, {
                type: 'doughnut',
                data: {
                    labels: roleData.map(x => roleLabels[x.role.toLowerCase()] || x.role),
                    datasets: [{
                        data: roleData.map(x => parseInt(x.count)),
                        backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444', '#8b5cf6', '#64748b'],
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
    };
</script>

<style>
    /* Animations CSS ultra-fluides */
    @keyframes fade-in-up {
        from {
            opacity: 0;
            transform: translateY(40px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fade-in-up 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    .admin-analytics .stats-col {
        perspective: 900px;
    }

    .admin-analytics .kpi-row {
        --kpi-gap: 0.75rem;
        --kpi-per-row: 6;
    }

    .admin-analytics .kpi-row>.stats-col {
        width: calc((100% / var(--kpi-per-row)) - ((var(--kpi-gap) * (var(--kpi-per-row) - 1)) / var(--kpi-per-row)));
        max-width: calc((100% / var(--kpi-per-row)) - ((var(--kpi-gap) * (var(--kpi-per-row) - 1)) / var(--kpi-per-row)));
        flex: 0 0 calc((100% / var(--kpi-per-row)) - ((var(--kpi-gap) * (var(--kpi-per-row) - 1)) / var(--kpi-per-row)));
    }

    .admin-analytics .kpi-row .kpi-card {
        padding: 1rem 0.95rem !important;
        min-height: 170px;
    }

    .admin-analytics .kpi-row .kpi-value {
        font-size: 1.55rem;
    }

    .admin-analytics .kpi-row .kpi-label {
        font-size: 0.78rem;
    }

    @media (max-width: 1199.98px) {
        .admin-analytics .kpi-row>.stats-col {
            width: auto;
            max-width: none;
            flex: 0 0 auto;
        }
    }

    @media (max-width: 767.98px) {
        .admin-analytics .kpi-row {
            --kpi-per-row: 3;
            --kpi-gap: 0.3rem;
            justify-content: center;
            margin-left: calc(var(--kpi-gap) * -0.5);
            margin-right: calc(var(--kpi-gap) * -0.5);
        }

        .admin-analytics .kpi-row>.stats-col {
            padding-left: calc(var(--kpi-gap) * 0.5);
            padding-right: calc(var(--kpi-gap) * 0.5);
            width: calc(100% / 3);
            max-width: calc(100% / 3);
            flex: 0 0 calc(100% / 3);
            margin-bottom: 0.6rem;
        }

        .admin-analytics .kpi-row .kpi-card {
            min-height: 110px;
            padding: 0.7rem 0.35rem !important;
            border-radius: 20px !important;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: color-mix(in srgb, var(--bg-card) 92%, white);
            border: 1px solid rgba(var(--primary-rgb), 0.15) !important;
            box-shadow: 0 8px 20px -10px rgba(0, 0, 0, 0.08) !important;
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .admin-analytics .kpi-row .kpi-card:active {
            transform: scale(0.94);
        }

        .admin-analytics .kpi-row .kpi-value {
            font-size: 1.2rem;
            margin-top: 0.3rem;
            margin-bottom: 0.1rem;
            letter-spacing: -0.8px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color), #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 10px 20px rgba(var(--primary-rgb), 0.1);
        }

        .admin-analytics .kpi-row .kpi-label {
            font-size: 0.58rem;
            line-height: 1.1;
            min-height: auto;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            opacity: 0.85;
        }

        .admin-analytics .kpi-stat-card .kpi-icon-wrapper {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            font-size: 0.95rem;
            margin-bottom: 0.1rem;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.2), 0 4px 8px -2px rgba(0, 0, 0, 0.05);
        }

        .admin-analytics .kpi-row .progress {
            width: 70%;
            margin: 0.4rem auto 0 !important;
            height: 4px !important;
            background: rgba(var(--primary-rgb), 0.08);
            overflow: visible;
        }

        .admin-analytics .kpi-row .progress-bar {
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(var(--primary-rgb), 0.4);
        }
    }

    .admin-analytics .stats-card {
        border-radius: 26px !important;
        border: 1px solid rgba(var(--primary-rgb), 0.12) !important;
        overflow: hidden;
        position: relative;
        height: 100%;
        min-height: 170px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transform-origin: center;
        animation: stats-burst-in 0.42s cubic-bezier(0.2, 0.95, 0.26, 1.25) both;
        animation-delay: calc(var(--stats-index, 0) * 70ms);
        transition: transform 0.18s cubic-bezier(0.2, 0.95, 0.26, 1.25),
            box-shadow 0.18s ease,
            border-color 0.18s ease;
    }

    .admin-analytics .kpi-stat-card .kpi-icon-wrapper {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .admin-analytics .kpi-stat-card .kpi-value {
        line-height: 1.1;
        margin-top: 0.55rem;
        margin-bottom: 0.45rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .admin-analytics .kpi-stat-card .kpi-label {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.3em;
        line-height: 1.15;
    }

    .admin-analytics .usage-stat-card .h3 {
        line-height: 1.1;
        margin-bottom: 0.35rem !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .admin-analytics .stats-card::after {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 100% 0, rgba(124, 58, 237, 0.16), transparent 58%);
        pointer-events: none;
    }

    .admin-analytics .stats-card:hover {
        transform: translateY(-10px) scale(1.04) rotateX(2deg);
        box-shadow: 0 24px 40px -18px rgba(109, 40, 217, 0.55);
        border-color: rgba(124, 58, 237, 0.35) !important;
    }

    .admin-analytics .stats-card:hover .kpi-icon-wrapper,
    .admin-analytics .stats-card:hover .avatar-xs {
        transform: scale(1.14);
        transition: transform 0.18s cubic-bezier(0.2, 0.95, 0.26, 1.25);
    }

    @keyframes stats-burst-in {
        0% {
            opacity: 0;
            transform: translateY(30px) scale(0.82) rotate(-2deg);
            filter: blur(3px);
        }

        65% {
            opacity: 1;
            transform: translateY(-6px) scale(1.06) rotate(0.4deg);
            filter: blur(0);
        }

        100% {
            opacity: 1;
            transform: translateY(0) scale(1) rotate(0);
            filter: blur(0);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .admin-analytics .stats-card {
            animation: none;
            transition: none;
        }
    }

    .quick-access-link {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        padding: 0.8rem 0.95rem;
        border: 1px solid var(--border-color);
        border-radius: 14px;
        text-decoration: none;
        color: var(--text-main);
        background: color-mix(in srgb, var(--bg-card) 88%, transparent);
        transition: all .2s ease;
    }

    .quick-access-link:hover {
        border-color: color-mix(in srgb, var(--primary-color) 45%, transparent);
        background: var(--primary-soft);
        transform: translateY(-1px);
        color: var(--text-main);
    }

    .quick-access-icon {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        background: color-mix(in srgb, var(--primary-color) 20%, transparent);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-color);
        flex-shrink: 0;
    }

    .quick-access-text {
        font-weight: 700;
        line-height: 1.2;
        flex: 1;
    }

    .quick-access-meta {
        font-size: .75rem;
        font-weight: 700;
        color: var(--text-muted);
        background: color-mix(in srgb, var(--border-color) 70%, transparent);
        border-radius: 999px;
        padding: .15rem .5rem;
    }

    .admin-pilot-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .8rem;
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: .8rem .95rem;
        text-decoration: none;
        background: color-mix(in srgb, var(--bg-card) 90%, transparent);
        color: inherit;
        transition: all .2s ease;
    }

    .admin-pilot-link:hover {
        background: var(--primary-soft);
        border-color: color-mix(in srgb, var(--primary-color) 45%, transparent);
        transform: translateY(-1px);
    }

    /* Financial Dashboard Card Styles */
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
        transform: scale(1.08);
    }
    .hover-card .card-indicator {
        transition: height 0.3s ease;
    }
    .hover-card:hover .card-indicator {
        height: 5px !important;
    }
    .hover-card .text-muted-theme.small {
        opacity: 0.85;
    }
    .hover-card .fw-black {
        line-height: 1.1;
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
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>