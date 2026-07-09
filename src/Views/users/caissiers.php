<?php $title = __('manage_cashiers_menu'); ob_start(); ?>

<div class="animate-fade-in container-fluid py-4">

    <!-- FLOATING ACTIONS BAR (Style Floating Island) -->
    <div class="d-flex justify-content-center mb-5">
        <div class="filter-island px-4 py-3 shadow-lg animate-slide-down" style="min-width: 80%;">
            <form method="GET" class="d-flex align-items-center gap-3 flex-wrap flex-md-nowrap filter-form w-100">
                
                <!-- Action Button -->
                <div class="d-flex gap-2 pe-3 border-end border-theme-light align-items-center me-2">
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm text-nowrap d-flex align-items-center gap-2 transition-base scale-on-hover" data-bs-toggle="modal" data-bs-target="#createCashierModal">
                        <i class="bi bi-person-plus-fill fs-5"></i> <?= __('new_cashier_btn') ?>
                    </button>
                </div>

                <!-- Search and Filter Fields -->
                <div class="flex-grow-1 d-flex gap-3 flex-wrap flex-sm-nowrap">
                    <!-- Search Input -->
                    <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2 flex-grow-1">
                        <span class="input-group-text border-0 bg-transparent text-primary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-0 bg-transparent shadow-none py-2 text-main"
                            value="<?= htmlspecialchars((string) ($filters['q'] ?? '')) ?>"
                            placeholder="<?= __('search_placeholder') ?>..." style="min-width: 150px;">
                    </div>
                    
                    <!-- Status Dropdown -->
                    <div class="input-group status-pill bg-white bg-opacity-10 rounded-pill px-2 flex-grow-1" style="max-width: 240px;">
                        <span class="input-group-text border-0 bg-transparent text-primary">
                            <i class="bi bi-funnel-fill"></i>
                        </span>
                        <select name="status" class="form-select border-0 bg-transparent shadow-none py-2 text-main rounded-pill pe-5">
                            <option value=""><?= __('all_status') ?></option>
                            <option value="1" <?= (isset($filters['status']) && $filters['status'] === '1') ? 'selected' : '' ?>><?= __('status_actives') ?></option>
                            <option value="0" <?= (isset($filters['status']) && $filters['status'] === '0') ? 'selected' : '' ?>><?= __('status_deactivateds') ?></option>
                        </select>
                    </div>
                </div>

                <!-- Form Buttons -->
                <div class="d-flex gap-2 align-items-center ps-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-1 transition-base">
                        <i class="bi bi-filter"></i> <?= __('filter') ?>
                    </button>
                    <a href="/users/caissiers" class="btn btn-light-theme rounded-circle p-2 d-flex align-items-center justify-content-center reset-btn border border-theme-light shadow-sm" style="width: 40px; height: 40px;" title="<?= __('reset') ?>">
                        <i class="bi bi-arrow-counterclockwise fs-5"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <?php if ($successFlash = \App\Core\Session::getFlash('success')): ?>
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 py-3 d-flex align-items-center animate-fade-in">
            <i class="bi bi-check-circle-fill me-3 fs-4"></i>
            <div class="fw-bold"><?= htmlspecialchars((string) $successFlash) ?></div>
        </div>
    <?php endif; ?>
    <?php if ($errorFlash = \App\Core\Session::getFlash('error')): ?>
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 py-3 d-flex align-items-center animate-fade-in">
            <i class="bi bi-exclamation-octagon-fill me-3 fs-4"></i>
            <div class="fw-bold"><?= htmlspecialchars((string) $errorFlash) ?></div>
        </div>
    <?php endif; ?>

    <!-- LIST OF CASHIERS -->
    <div class="modern-card border-0 shadow-sm overflow-hidden mb-4 bg-glass-theme">
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 25%;"><?= __('name') ?></th>
                        <th style="width: 20%;"><?= __('username') ?></th>
                        <th style="width: 25%;"><?= __('email') ?></th>
                        <th style="width: 15%;" class="text-center"><?= __('cashier_status_label') ?></th>
                        <th style="width: 15%;" class="pe-4 text-center"><?= __('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cashiers)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted-theme opacity-50">
                                <i class="bi bi-people fs-1 d-block mb-3"></i>
                                <?= __('cashier_list_empty') ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cashiers as $c): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-black rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                            style="width: 38px; height: 38px; font-size: 1rem;">
                                            <?= strtoupper(substr((string) ($c['nom'] ?: $c['username']), 0, 1)) ?>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-main-theme m-0">
                                                <?= htmlspecialchars($c['nom'] . ' ' . $c['prenom']) ?>
                                            </h6>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code class="text-primary fw-semibold"><?= htmlspecialchars($c['username']) ?></code>
                                </td>
                                <td>
                                    <span class="text-muted-theme"><?= htmlspecialchars($c['email'] ?: __('not_provided')) ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($c['status']): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 px-3 py-2 rounded-pill fw-bold">
                                            <i class="bi bi-check-circle-fill me-1"></i> <?= __('status_active') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10 px-3 py-2 rounded-pill fw-bold">
                                            <i class="bi bi-dash-circle-fill me-1"></i> <?= __('status_deactivated') ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="/users/edit?id=<?= $c['id'] ?>" class="btn-icon-modern text-primary" title="<?= __('edit') ?>">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <button onclick="confirmToggleStatus('/users/toggle-status?id=<?= $c['id'] ?>', <?= $c['status'] ? 1 : 0 ?>)" 
                                            class="btn-icon-modern <?= $c['status'] ? 'text-warning' : 'text-success' ?>" 
                                            title="<?= $c['status'] ? __('deactivate_account_title') : __('activate_account_title') ?>">
                                            <i class="bi <?= $c['status'] ? 'bi-power' : 'bi-shield-check' ?>"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Nouveau Caissier -->
<div class="modal fade" id="createCashierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg bg-glass-theme">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-black text-primary"><?= __('create_cashier_title') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="/users/store-caissier" method="POST" id="createCashierForm">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                    <input type="hidden" name="role" value="caissier">
                    
                    <!-- Identity Section -->
                    <div class="mb-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('name') ?> *</label>
                        <input type="text" name="nom" class="form-control premium-input" placeholder="Doe" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('first_name') ?> *</label>
                        <input type="text" name="prenom" class="form-control premium-input" placeholder="John" required>
                    </div>
                    
                    <!-- Credentials Section -->
                    <div class="mb-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('username_login') ?> *</label>
                        <div class="input-group">
                            <span class="input-group-text border-theme-light bg-soft-primary text-primary"><i class="bi bi-person-fill"></i></span>
                            <input type="text" name="username" class="form-control premium-input" placeholder="john.doe" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('email_address_optional') ?></label>
                        <div class="input-group">
                            <span class="input-group-text border-theme-light bg-soft-info text-info"><i class="bi bi-envelope-at-fill"></i></span>
                            <input type="email" name="email" class="form-control premium-input" placeholder="john@example.com">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('password_required_label') ?> *</label>
                        <div class="input-group">
                            <span class="input-group-text border-theme-light bg-soft-danger text-danger"><i class="bi bi-key-fill"></i></span>
                            <input type="password" name="password" class="form-control premium-input" placeholder="********" required>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 mt-4 pt-2 border-top border-theme-light">
                        <button type="button" class="btn btn-light-theme rounded-pill px-4" data-bs-dismiss="modal"><?= __('confirm_cancel') ?></button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"><?= __('save_account') ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .filter-island {
        background: rgba(255, 255, 255, 0.65);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-radius: 30px;
        border: 1px solid rgba(var(--primary-rgb), 0.08);
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    }
    [data-theme="dark"] .filter-island {
        background: rgba(26, 26, 39, 0.6);
        border-color: rgba(255, 255, 255, 0.05);
    }
    .filter-island:hover {
        border-color: rgba(var(--primary-rgb), 0.15);
        box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.05);
    }
    .filter-island:focus-within {
        border-color: var(--primary-color);
        box-shadow: 0 20px 40px -10px rgba(var(--primary-rgb), 0.15);
        transform: translateY(-2px);
    }
    
    .search-pill, .status-pill {
        border: 1px solid rgba(var(--primary-rgb), 0.08) !important;
        background: rgba(var(--primary-rgb), 0.02) !important;
        transition: all 0.3s ease;
    }
    .search-pill:focus-within, .status-pill:focus-within {
        border-color: var(--primary-color) !important;
        background: rgba(var(--primary-rgb), 0.05) !important;
        box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.12);
    }
    
    .reset-btn {
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .reset-btn:hover {
        transform: rotate(-180deg);
        background: var(--primary-color) !important;
        color: white !important;
        border-color: var(--primary-color) !important;
        box-shadow: 0 5px 15px rgba(var(--primary-rgb), 0.25);
    }

    .btn-icon-modern {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: rgba(var(--primary-rgb), 0.05);
        border: none;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        text-decoration: none !important;
    }
    .btn-icon-modern:hover {
        transform: translateY(-3px) rotate(8deg);
        background: var(--primary-color);
        color: white !important;
        box-shadow: 0 5px 15px rgba(var(--primary-rgb), 0.3);
    }
    .btn-icon-modern.text-warning:hover {
        background: #f1c40f;
        color: white !important;
        box-shadow: 0 5px 15px rgba(241, 196, 15, 0.3);
    }
    .btn-icon-modern.text-success:hover {
        background: #2ecc71;
        color: white !important;
        box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
    }
</style>

<script>
function confirmToggleStatus(url, status) {
    const actionText = status ? <?= json_encode(__('action_deactivate')) ?> : <?= json_encode(__('action_activate')) ?>;
    const confirmText = status ? <?= json_encode(__('confirm_toggle_deactivate')) ?> : <?= json_encode(__('confirm_toggle_activate')) ?>;
    const confirmButtonColor = status ? '#d33' : '#2ecc71';
    
    Swal.fire({
        title: <?= json_encode(__('confirm_title')) ?>,
        text: <?= json_encode(__('confirm_toggle_cashier_text')) ?>.replace(':action', actionText),
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: confirmButtonColor,
        cancelButtonColor: '#95a5a6',
        confirmButtonText: confirmText,
        cancelButtonText: <?= json_encode(__('confirm_cancel')) ?>
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../templates/layout.php'; ?>
