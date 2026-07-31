<?php $title = __('system_configuration'); ob_start(); ?>

<div class="animate-fade-in container-fluid pt-0 pb-4 px-3 px-md-4">
    <!-- EN-TÊTE DE SECTION & BARRE D'ACTIONS COMPLÈTE -->
    <div class="settings-header-card mb-3 p-3 p-md-4 rounded-4 shadow-sm border-theme-light">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <!-- Titre & Info Contexte -->
            <div class="d-flex align-items-center gap-3">
                <div class="settings-icon-badge rounded-3 d-flex align-items-center justify-content-center">
                    <i class="bi bi-gear-wide-connected fs-3 text-primary"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
                        <?= __('system_configuration') ?>
                        <span class="badge bg-primary-subtle text-primary rounded-pill fs-7 fw-semibold px-2 py-1">v2.5</span>
                    </h4>
                    <p class="text-muted small mb-0">
                        <?= __('Gérez les paramètres globaux, la charte graphique et la configuration par type d\'enseignement.') ?>
                    </p>
                </div>
            </div>

            <!-- Floating Island Actions & Type Selector -->
            <div class="settings-actions-wrapper d-flex align-items-center flex-wrap gap-2">
                <?php if (!empty($teachingTypes)): ?>
                    <form method="GET" action="/settings" class="d-inline-block m-0" id="ttSelectForm">
                        <div class="input-group input-group-sm custom-select-group shadow-sm rounded-pill overflow-hidden border">
                            <span class="input-group-text bg-light-subtle border-0 text-primary fw-semibold px-3">
                                <i class="bi bi-diagram-3-fill me-1"></i> <span class="d-none d-sm-inline"><?= __('Type :') ?></span>
                            </span>
                            <select name="teaching_type_id" class="form-select border-0 bg-transparent fw-bold text-dark pe-4" style="cursor: pointer;" onchange="document.getElementById('ttSelectForm').submit()">
                                <?php foreach ($teachingTypes as $tt): ?>
                                    <option value="<?= $tt['id'] ?>" <?= ((int)$currentTeachingTypeId === (int)$tt['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string)$tt['nom']) ?> (<?= htmlspecialchars((string)$tt['code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                <?php endif; ?>

                <div class="d-flex align-items-center gap-2">
                    <button type="submit" form="settingsForm" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm scale-on-hover d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill fs-6"></i>
                        <span><?= __('save') ?></span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary rounded-pill shadow-none px-3 py-2 d-flex align-items-center gap-1 scale-on-hover" onclick="confirmReset()" title="<?= __('default_theme') ?>">
                        <i class="bi bi-arrow-counterclockwise fs-6"></i>
                        <span class="small fw-semibold d-none d-sm-inline"><?= __('default_theme') ?></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php if ($msg = App\Core\Session::get('success_msg')): ?>
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4 py-3 rounded-4" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i><?= htmlspecialchars((string) $msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= __('close') ?>"></button>
        </div>
        <?php App\Core\Session::remove('success_msg'); ?>
    <?php endif; ?>

    <?php if ($warning = App\Core\Session::get('warning_msg')): ?>
        <div class="alert alert-warning border-0 shadow-sm alert-dismissible fade show mb-4 py-3 rounded-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i><?= htmlspecialchars((string) $warning) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= __('close') ?>"></button>
        </div>
        <?php App\Core\Session::remove('warning_msg'); ?>
    <?php endif; ?>

    <?php if ($errorMsg = App\Core\Session::get('error_msg')): ?>
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show mb-4 py-3 rounded-4" role="alert">
            <i class="bi bi-x-circle-fill me-2 fs-5"></i><?= htmlspecialchars((string) $errorMsg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= __('close') ?>"></button>
        </div>
        <?php App\Core\Session::remove('error_msg'); ?>
    <?php endif; ?>

    <!-- ONGLETS DE NAVIGATION DESIGN PRO -->
    <div class="mb-4 settings-tabs-wrapper overflow-auto pb-1">
        <ul class="nav nav-pills custom-settings-tabs flex-nowrap" id="settingsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="general-tab" data-bs-toggle="pill" data-bs-target="#tab-general"
                    type="button" role="tab">
                    <span class="tab-icon-wrapper me-2"><i class="bi bi-buildings"></i></span>
                    <span><?= __('institution_tab') ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="academic-tab" data-bs-toggle="pill" data-bs-target="#tab-academic"
                    type="button" role="tab">
                    <span class="tab-icon-wrapper me-2"><i class="bi bi-mortarboard"></i></span>
                    <span><?= __('academic_tab') ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="appearance-tab" data-bs-toggle="pill" data-bs-target="#tab-appearance"
                    type="button" role="tab">
                    <span class="tab-icon-wrapper me-2"><i class="bi bi-palette2"></i></span>
                    <span><?= __('appearance_tab') ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="login-tab" data-bs-toggle="pill" data-bs-target="#tab-login" type="button"
                    role="tab">
                    <span class="tab-icon-wrapper me-2"><i class="bi bi-shield-lock"></i></span>
                    <span><?= __('login_tab') ?></span>
                </button>
            </li>
            <?php if (\App\Core\Session::get('user_role') === 'superadmin'): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="automation-tab" data-bs-toggle="pill" data-bs-target="#tab-automation"
                    type="button" role="tab">
                    <span class="tab-icon-wrapper me-2"><i class="bi bi-cloud-check"></i></span>
                    <span><?= __('automation_tab') ?></span>
                    <span class="badge rounded-pill bg-danger-subtle text-danger ms-2 fs-8">Admin</span>
                </button>
            </li>
            <?php endif; ?>
        </ul>
    </div>

    <form action="/settings/store" method="POST" enctype="multipart/form-data" id="settingsForm">
        <input type="hidden" name="teaching_type_id" value="<?= (int)$currentTeachingTypeId ?>">
        <div class="tab-content" id="settingsTabsContent">
            <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                <?php include __DIR__ . '/partials/institution.php'; ?>
            </div>

            <div class="tab-pane fade" id="tab-academic" role="tabpanel">
                <?php include __DIR__ . '/partials/academic.php'; ?>
            </div>

            <div class="tab-pane fade" id="tab-appearance" role="tabpanel">
                <?php include __DIR__ . '/partials/appearance.php'; ?>
            </div>

            <div class="tab-pane fade" id="tab-login" role="tabpanel">
                <?php include __DIR__ . '/partials/login.php'; ?>
            </div>

            <?php if (\App\Core\Session::get('user_role') === 'superadmin'): ?>
            <div class="tab-pane fade" id="tab-automation" role="tabpanel">
                <?php include __DIR__ . '/partials/automation.php'; ?>
            </div>
            <?php endif; ?>
        </div>
    </form>
    
    <!-- Formulaire de sauvegarde manuelle (externe pour éviter l'imbrication) -->
    <form action="/settings/run_backup" method="POST" id="runBackupForm" class="d-none"></form>
</div>

<style>
    /* Header Card & Glassmorphism */
    .settings-header-card {
        background: rgba(var(--bg-card-rgb, 255, 255, 255), 0.85);
        backdrop-filter: blur(16px) saturate(180%);
        border: 1px solid rgba(var(--primary-rgb, 124, 58, 237), 0.12);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    [data-theme="dark"] .settings-header-card {
        background: rgba(30, 30, 45, 0.75);
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.25);
    }

    .settings-icon-badge {
        width: 52px;
        height: 52px;
        background: var(--primary-soft, rgba(124, 58, 237, 0.1));
        border: 1px solid rgba(var(--primary-rgb, 124, 58, 237), 0.2);
    }

    /* Select Group Customization */
    .custom-select-group {
        background: var(--bg-card, #ffffff);
        border-color: rgba(var(--primary-rgb, 124, 58, 237), 0.2) !important;
        transition: all 0.25s ease;
    }

    .custom-select-group:focus-within,
    .custom-select-group:hover {
        border-color: var(--primary-color, #7c3aed) !important;
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15) !important;
    }

    .scale-on-hover {
        transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s ease;
    }

    .scale-on-hover:hover {
        transform: translateY(-2px) scale(1.02);
    }

    /* Tabs Styling */
    .settings-tabs-wrapper {
        border-bottom: 2px solid rgba(0, 0, 0, 0.05);
    }

    [data-theme="dark"] .settings-tabs-wrapper {
        border-bottom-color: rgba(255, 255, 255, 0.08);
    }

    .custom-settings-tabs {
        gap: 0.5rem;
        padding-bottom: 0.25rem;
    }

    .custom-settings-tabs .nav-link {
        background: transparent !important;
        color: var(--text-muted, #64748b) !important;
        font-weight: 600;
        font-size: 0.875rem;
        letter-spacing: 0.3px;
        padding: 0.65rem 1.25rem;
        border-radius: 12px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid transparent;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
    }

    .custom-settings-tabs .nav-link:hover {
        background: var(--primary-soft, rgba(124, 58, 237, 0.08)) !important;
        color: var(--primary-color, #7c3aed) !important;
        transform: translateY(-1px);
    }

    .custom-settings-tabs .nav-link.active {
        background: var(--primary-color, #7c3aed) !important;
        color: #ffffff !important;
        box-shadow: 0 8px 16px -4px rgba(124, 58, 237, 0.35);
        border-color: var(--primary-color, #7c3aed);
    }

    .custom-settings-tabs .nav-link.active .badge {
        background-color: rgba(255, 255, 255, 0.2) !important;
        color: #ffffff !important;
    }

    .tab-icon-wrapper {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
    }
</style>

<script>
    function confirmReset() {
        Swal.fire({
            title: <?= json_encode(__('reset_theme_confirm_title'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
            text: <?= json_encode(__('reset_theme_confirm_text'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#7c3aed',
            cancelButtonColor: '#6c757d',
            confirmButtonText: <?= json_encode(__('reset_theme_confirm_action'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
            cancelButtonText: <?= json_encode(__('cancel'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '/settings/reset';
            }
        })
    }

    function confirmRunBackup() {
        Swal.fire({
            title: <?= json_encode(__('run_backup_confirm_title'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
            text: <?= json_encode(__('run_backup_confirm_text'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#7c3aed',
            cancelButtonColor: '#6c757d',
            confirmButtonText: <?= json_encode(__('run_backup_confirm_action'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
            cancelButtonText: <?= json_encode(__('cancel'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('runBackupForm').submit();
            }
        });

        return false;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const hash = window.location.hash;
        if (!hash) {
            return;
        }

        const tabButton = document.querySelector(`[data-bs-target="${hash}"]`);
        if (tabButton && window.bootstrap?.Tab) {
            new bootstrap.Tab(tabButton).show();
        }
    });
</script>

<?php 
$content = ob_get_clean(); 
include __DIR__ . '/../templates/layout.php'; 
?>