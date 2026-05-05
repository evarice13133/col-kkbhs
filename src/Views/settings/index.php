<?php $title = __('system_configuration'); ob_start(); ?>

<div class="animate-fade-in container-fluid py-4">
    <!-- BARRE D'ACTIONS COMPLÈTE : Style Floating Island -->
    <div class="d-flex justify-content-center mb-5">
        <div class="filter-island px-3 py-2 shadow-lg animate-slide-down" style="min-width: 40%;">
            <div class="d-flex align-items-center justify-content-center gap-2 w-100">
                <button type="submit" form="settingsForm" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm scale-on-hover">
                    <i class="bi bi-check-circle-fill me-2"></i> <?= __('save') ?>
                </button>
                <button type="button" class="btn btn-light-theme rounded-pill border-theme-light shadow-none px-3" onclick="confirmReset()">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> <span class="small fw-bold"><?= __('default_theme') ?></span>
                </button>
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

    <div class="mb-5 overflow-auto">
        <ul class="nav nav-pills custom-settings-tabs flex-nowrap" id="settingsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="general-tab" data-bs-toggle="pill" data-bs-target="#tab-general"
                    type="button" role="tab">
                    <i class="bi bi-buildings me-2"></i> <?= __('institution_tab') ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="academic-tab" data-bs-toggle="pill" data-bs-target="#tab-academic"
                    type="button" role="tab">
                    <i class="bi bi-mortarboard me-2"></i> <?= __('academic_tab') ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="appearance-tab" data-bs-toggle="pill" data-bs-target="#tab-appearance"
                    type="button" role="tab">
                    <i class="bi bi-palette2 me-2"></i> <?= __('appearance_tab') ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="login-tab" data-bs-toggle="pill" data-bs-target="#tab-login" type="button"
                    role="tab">
                    <i class="bi bi-lock me-2"></i> <?= __('login_tab') ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="automation-tab" data-bs-toggle="pill" data-bs-target="#tab-automation"
                    type="button" role="tab">
                    <i class="bi bi-cloud-check me-2"></i> <?= __('automation_tab') ?>
                </button>
            </li>
        </ul>
    </div>

    <form action="/settings/store" method="POST" enctype="multipart/form-data" id="settingsForm">
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

            <div class="tab-pane fade" id="tab-automation" role="tabpanel">
                <?php include __DIR__ . '/partials/automation.php'; ?>
            </div>
        </div>
    </form>
</div>

<style>
    /* Floating Island Filters */
    .filter-island {
        background: rgba(var(--bg-card-rgb), 0.7);
        backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(var(--primary-rgb), 0.15);
        border-radius: 100px;
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .filter-island {
        background: rgba(30, 30, 45, 0.6);
        border-color: rgba(255, 255, 255, 0.08);
    }

    .scale-on-hover:hover { transform: scale(1.05); }

    /* Animations */
    .animate-slide-down {
        animation: slideDown 0.6s cubic-bezier(0.23, 1, 0.32, 1);
    }

    @keyframes slideDown {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .custom-settings-tabs {
        gap: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--border-color);
    }

    .custom-settings-tabs .nav-link {
        background: transparent !important;
        color: var(--text-muted) !important;
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 0.75rem 1.5rem;
        border-radius: 100px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .custom-settings-tabs .nav-link:hover {
        background: var(--primary-soft) !important;
        color: var(--primary-color) !important;
    }

    .custom-settings-tabs .nav-link.active {
        background: var(--primary-color) !important;
        color: white !important;
        box-shadow: 0 10px 20px -5px rgba(124, 58, 237, 0.4);
        border-color: var(--primary-color);
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