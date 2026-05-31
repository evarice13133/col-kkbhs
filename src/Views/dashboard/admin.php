<?php
$title = __("command_center");

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
    // ── Pédagogie ────────────────────────────────────────────
    ['url' => '/notes',    'icon' => 'bi-pencil-square',         'label' => __('enter_marks'),         'meta' => (string) ((int) $globalPending)],
    ['url' => '/bulletins','icon' => 'bi-file-earmark-pdf',      'label' => __('bulletins'),           'meta' => null],
    ['url' => '/honors',   'icon' => 'bi-award-fill',            'label' => __('honor_roll_title'),    'meta' => null],
    ['url' => '/bulletins/discipline', 'icon' => 'bi-shield-check', 'label' => __('discipline_management'), 'meta' => null],
    ['url' => '/proces-verbal', 'icon' => 'bi-file-earmark-text','label' => __('proces_verbaux'),      'meta' => null],
    // ── Pilotage ─────────────────────────────────────────────
    ['url' => '/sequences',       'icon' => 'bi-check2-square',  'label' => __('evaluations'),         'meta' => null],
    ['url' => '/academic_years',  'icon' => 'bi-calendar-event', 'label' => __('academic_years'),      'meta' => null],
    // ── Affectations ─────────────────────────────────────────
    ['url' => '/teachers', 'icon' => 'bi-diagram-3-fill',        'label' => __('academic_assignment'), 'meta' => (string) ((int) $teachers_without_assignment)],
    // ── Aide ─────────────────────────────────────────────────
    ['url' => '/documentation', 'icon' => 'bi-question-circle-fill', 'label' => __('help'),            'meta' => null],
];

if (\App\Core\Session::get('user_role') === 'superadmin') {
    $quickAccessLinks[] = ['url' => '/departments', 'icon' => 'bi-building',       'label' => __('departments'),       'meta' => null];
    $quickAccessLinks[] = ['url' => '/sections',    'icon' => 'bi-grid-3x3-gap',   'label' => __('academic_sections'), 'meta' => null];
    $quickAccessLinks[] = ['url' => '/cycles',      'icon' => 'bi-layers',          'label' => __('academic_cycles'),   'meta' => null];
    $quickAccessLinks[] = ['url' => '/users',       'icon' => 'bi-people-fill',     'label' => __('users'),             'meta' => (string) ((int) $stats_users)];
    $quickAccessLinks[] = ['url' => '/settings',    'icon' => 'bi-gear-fill',       'label' => __('settings'),          'meta' => null];
}

ob_start();
?>

<div class="animate-fade-in admin-analytics">


    <?php if (\App\Core\Session::get('user_role') === 'superadmin'): ?>
        <!-- Notifications Vitrine -->
        <?php if (!empty($landing_notifications)): ?>
            <div class="row g-3 mb-5 animate-fade-in">
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-bell-fill text-accent fs-5"></i>
                            <h6 class="fw-bold m-0 text-uppercase small letter-spacing-1">Notifications Vitrine (Demandes)</h6>
                        </div>
                        <span class="badge bg-accent bg-opacity-10 text-accent rounded-pill"><?= count($landing_notifications) ?> nouveaux</span>
                    </div>
                    <div class="table-responsive">
                        <div class="d-flex gap-3 pb-3">
                            <?php foreach ($landing_notifications as $notif): 
                                $isArchived = $notif['archived'] ?? false;
                            ?>
                                <div class="modern-card p-3 shadow-sm border-start border-4 <?= $isArchived ? 'border-secondary opacity-75' : 'border-accent' ?> flex-shrink-0" id="notif-<?= h($notif['id']) ?>" style="min-width: 300px; max-width: 350px; background: <?= $isArchived ? 'rgba(0,0,0,0.02)' : 'rgba(var(--primary-rgb), 0.02)' ?>;">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="fw-bold text-main-theme small d-flex align-items-center gap-2">
                                            <?= h($notif['name']) ?>
                                            <?php if ($isArchived): ?>
                                                <span class="badge bg-secondary extra-small">Archivé</span>
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
                                            <button class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-0 extra-small" onclick="manageNotif('<?= h($notif['id']) ?>', 'toggle-archive')" title="<?= $isArchived ? 'Restaurer' : 'Archiver' ?>">
                                                <i class="bi <?= $isArchived ? 'bi-arrow-counterclockwise' : 'bi-archive' ?>"></i>
                                            </button>
                                            <button class="btn btn-xs btn-outline-danger rounded-pill px-2 py-0 extra-small" onclick="manageNotif('<?= h($notif['id']) ?>', 'delete')" title="Supprimer">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        <button class="btn btn-sm btn-link p-0 text-accent fw-bold text-decoration-none small" onclick="Swal.fire({title: 'Message de <?= h($notif['name']) ?>', text: '<?= h($notif['message']) ?>', footer: 'Contact: <?= h($notif['email']) ?>'})">Lire</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            function manageNotif(id, action) {
                const url = action === 'delete' ? '/notifications/delete' : '/notifications/toggle-archive';
                const confirmMsg = action === 'delete' ? 'Supprimer définitivement ce message ?' : (document.querySelector(`#notif-${id}`).classList.contains('opacity-75') ? 'Restaurer ce message ?' : 'Archiver ce message ?');

                Swal.fire({
                    title: 'Confirmation',
                    text: confirmMsg,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Oui',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`${url}?id=${id}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                Swal.fire('Erreur', data.error, 'error');
                            }
                        });
                    }
                });
            }
            </script>
        <?php endif; ?>

        <div class="row g-3 mb-5">
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

    <div class="row g-3 mb-4 kpi-row">
        <!-- Étudiants -->
        <div class="col-sm-6 col-xl-3 stats-col">
            <div class="modern-card kpi-card border-0 shadow-sm stats-card kpi-stat-card" style="--stats-index: 0;">
                <div class="kpi-icon-wrapper bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="kpi-value" data-count-up="<?= (int) $stats_students ?>"><?= $stats_students ?></div>
                <div class="kpi-label"><?= __('total_effectif') ?></div>
            </div>
        </div>
        <!-- Classes -->
        <div class="col-sm-6 col-xl-3 stats-col">
            <div class="modern-card kpi-card border-0 shadow-sm stats-card kpi-stat-card" style="--stats-index: 1;">
                <div class="kpi-icon-wrapper bg-success bg-opacity-10 text-success">
                    <i class="bi bi-door-open-fill"></i>
                </div>
                <div class="kpi-value" data-count-up="<?= (int) $stats_classes ?>"><?= $stats_classes ?></div>
                <div class="kpi-label"><?= __('active_rooms') ?></div>
            </div>
        </div>
        <!-- Enseignants -->
        <div class="col-sm-6 col-xl-3 stats-col">
            <div class="modern-card kpi-card border-0 shadow-sm stats-card kpi-stat-card" style="--stats-index: 2;">
                <div class="kpi-icon-wrapper bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-person-video3"></i>
                </div>
                <div class="kpi-value" data-count-up="<?= (int) $stats_teachers ?>"><?= (int) $stats_teachers ?></div>
                <div class="kpi-label"><?= __('teachers') ?></div>
            </div>
        </div>
        <!-- Matières -->
        <div class="col-sm-6 col-xl-3 stats-col">
            <div class="modern-card kpi-card border-0 shadow-sm stats-card kpi-stat-card" style="--stats-index: 3;">
                <div class="kpi-icon-wrapper bg-secondary bg-opacity-10 text-secondary">
                    <i class="bi bi-book-half"></i>
                </div>
                <div class="kpi-value" data-count-up="<?= (int) $stats_subjects ?>"><?= (int) $stats_subjects ?></div>
                <div class="kpi-label"><?= __('subjects') ?></div>
                <?php if (\App\Core\Session::get('user_role') === 'superadmin' && $stats_subjects_inactive > 0): ?>
                    <div class="mt-2">
                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill small">
                            <?= $stats_subjects_inactive ?> <?= __('inactive_short') ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- Progression Globale -->
        <div class="col-sm-6 col-xl-3 stats-col">
            <div class="modern-card kpi-card border-0 shadow-sm stats-card kpi-stat-card" style="--stats-index: 4;">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="kpi-icon-wrapper bg-info bg-opacity-10 text-info">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                </div>
                <div class="kpi-value" data-count-up="<?= (int) $globalProgress ?>" data-suffix="%">
                    <?= $globalProgress ?>%
                </div>
                <div class="kpi-label"><?= __('global_progress') ?></div>
                <div class="progress mt-3" style="height: 6px; border-radius: 10px; background: var(--bg-body);">
                    <div class="progress-bar bg-info" style="width: <?= $globalProgress ?>%"></div>
                </div>
            </div>
        </div>
        <!-- Alertes Enseignants -->
        <div class="col-sm-6 col-xl-3 stats-col">
            <div class="modern-card kpi-card border-0 shadow-sm stats-card kpi-stat-card" style="--stats-index: 5;">
                <div class="kpi-icon-wrapper bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div class="kpi-value text-danger" data-count-up="<?= (int) $teachersUnder50 ?>"><?= $teachersUnder50 ?>
                </div>
                <div class="kpi-label"><?= __('critical_delays') ?></div>
            </div>
        </div>
    </div>

    <!-- Accès rapide - Scroll horizontal -->
    <div class="modern-card mb-4 border-0 shadow-sm border-top border-primary border-4 animate-fade-in" style="border-radius: 24px !important;">
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
    <div class="row g-4 mb-5">
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
    <div class="row g-4 mb-5">
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



    <?php if (\App\Core\Session::get('user_role') === 'superadmin'): ?>
        <div class="row g-4 mb-5">
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

    <div class="row g-4">
        <div class="col-xl-8">
            <!-- Matières sans prof (Priorité Anomalies) -->
            <div class="modern-card mb-4 border-0 shadow-sm border-top border-warning border-4">
                <div class="modern-card-header border-bottom bg-transparent py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-patch-exclamation-fill text-warning fs-5"></i>
                        <h5 class="modern-card-title m-0 text-main-theme"><?= __('subjects_without_teachers') ?></h5>
                    </div>
                    <span class="badge bg-warning bg-opacity-10 text-warning fw-bold"><?= count($unassignedSubjects) ?>
                        <?= __('anomalies') ?></span>
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
                                    <td colspan="3" class="text-center py-5 text-main-theme"><i
                                            class="bi bi-check-circle-fill text-success fs-2 d-block mb-2 text-main-theme"></i><?= __('all_subjects_covered') ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($unassignedSubjects as $us): ?>
                                    <tr>
                                        <td class="ps-4 py-3"><span
                                                class="badge bg-soft-primary px-3 rounded-pill text-main-theme"><?= h($us['class_name']) ?></span>
                                        </td>
                                        <td class="py-3">
                                            <div class="fw-bold text-main-theme"><?= h($us['subject_name']) ?></div>
                                        </td>
                                        <td class="text-end pe-4 py-3">
                                            <a href="/teachers?assign_subject=<?= $us['subject_id'] ?>&assign_class=<?= $us['class_id'] ?>"
                                                class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold">
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
                                        <div class="small text-muted-theme"><?= $m['classes_count'] ?>     <?= __('classes') ?>
                                        </div>
                                    </td>
                                    <td class="text-center border-0 fw-bold text-main-theme">
                                        <?= $m['filled_count'] ?>
                                        <small class="text-muted-theme">/<?= $m['expected_count'] ?></small>
                                    </td>
                                    <td class="border-0" style="min-width: 150px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1"
                                                style="height: 6px; border-radius: 10px; background: var(--border-color);">
                                                <div class="progress-bar bg-primary"
                                                    style="width: <?= $m['progress_percent'] ?>%"></div>
                                            </div>
                                            <span
                                                class="small fw-bold text-main-theme"><?= $m['progress_percent'] ?>%</span>
                                        </div>
                                    </td>
                                    <td class="pe-4 border-0"><span
                                            class="level-badge <?= nm_level_class($m['level_label']) ?>"><?= __($m['level_label']) ?></span>
                                    </td>
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
                <div class="modern-card mb-4 border-0 shadow-sm overflow-hidden bg-primary text-white"
                    style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;">
                    <div class="modern-card-body p-4 position-relative">
                        <div class="d-flex align-items-center gap-2 mb-3 opacity-75">
                            <i class="bi bi-star-fill text-warning"></i>
                            <span
                                class="fw-bold small text-uppercase letter-spacing-1"><?= __('top_performing_teacher') ?></span>
                        </div>
                        <h3 class="fw-bold mb-1 lh-sm"><?= h($topTeacher['teacher_name']) ?></h3>
                        <div class="d-flex justify-content-between align-items-end mt-4">
                            <div>
                                <div class="fs-1 fw-black lh-1" data-count-up="<?= (int) $topTeacher['progress_percent'] ?>"
                                    data-suffix="%"><?= $topTeacher['progress_percent'] ?>%</div>
                                <div class="small opacity-75 mt-1"><?= $topTeacher['filled_count'] ?>     <?= __('entries') ?>
                                </div>
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
                                <div class="small text-muted-theme d-none d-sm-block">
                                    <?= __('teachers_to_contact', ['count' => $teachersUnder50]) ?>
                                </div>
                            </div>
                            <span
                                class="badge bg-danger bg-opacity-10 text-danger rounded-pill"><?= (int) $teachersUnder50 ?></span>
                        </a>
                        <a href="/notes" class="admin-pilot-link">
                            <div>
                                <div class="fw-bold text-main-theme"><?= __('notes_management') ?></div>
                                <div class="small text-muted-theme d-none d-sm-block"><?= __('processing_in_progress') ?></div>
                            </div>
                            <span
                                class="badge bg-warning bg-opacity-10 text-warning rounded-pill"><?= (int) $globalPending ?></span>
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
                            <span
                                class="badge rounded-pill px-3 py-2 <?= nm_backup_state_class((string) ($backupOverview['freshness_state'] ?? 'unknown')) ?>"><?= __($stateKey) ?></span>
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
                        <a href="/settings#tab-automation"
                            class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm d-flex align-items-center justify-content-center gap-2">
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
    });
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
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>