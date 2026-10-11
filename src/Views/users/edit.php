<?php $title = __('user_editing'); ob_start(); ?>

<div class="animate-fade-in container-fluid py-3 py-md-4 px-3 px-md-4 user-edit-page">
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="fw-black text-main-theme mb-0 fs-4"><?= __('edit_user') ?></h2>
            <p class="text-muted-theme small mb-0"><?= __('user_editing_subtitle') ?></p>
        </div>
        <a href="/users" class="btn btn-sm btn-light-theme rounded-pill px-3 border-theme-light shadow-sm user-edit-back-link">
            <i class="bi bi-arrow-left me-1"></i> <?= __('back_to_list') ?>
        </a>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="modern-card border-0 shadow-sm overflow-hidden mb-4 bg-glass-theme">
                <div class="card-body p-3 p-sm-4 p-md-5 position-relative">
                    
                    <?php if ($error = App\Core\Session::getFlash('error')): ?>
                        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 py-3 d-flex align-items-center animate-fade-in">
                            <i class="bi bi-exclamation-octagon-fill me-3 fs-4"></i>
                            <div class="fw-bold"><?= htmlspecialchars((string) $error) ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="/users/update?id=<?= $user['id'] ?>" method="POST" id="user-edit-form">
                        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                        
                        <!-- Identification -->
                        <div class="row g-3 mb-4">
                            <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                                <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1"><?= __('user_identity') ?></h6>
                            </div>
                            
                            <div class="col-md-6 mt-0">
                                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('name') ?></label>
                                <input type="text" name="nom" class="form-control premium-input" value="<?= htmlspecialchars((string) $user['nom']) ?>" required autofocus>
                            </div>
                            <div class="col-md-6 mt-0">
                                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('first_name') ?></label>
                                <input type="text" name="prenom" class="form-control premium-input" value="<?= htmlspecialchars((string) $user['prenom']) ?>" required>
                            </div>
                        </div>
                        
                        <!-- Account Details -->
                        <div class="row g-3 mb-4">
                            <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                                <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1"><?= __('account_credentials') ?></h6>
                            </div>
                            
                            <div class="col-md-6 mt-0">
                                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('username_login') ?></label>
                                <div class="input-group">
                                    <span class="input-group-text border-theme-light bg-soft-primary text-primary"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" name="username" class="form-control premium-input" value="<?= htmlspecialchars((string) $user['username']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6 mt-0">
                                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('email_address_optional') ?></label>
                                <div class="input-group">
                                    <span class="input-group-text border-theme-light bg-soft-info text-info"><i class="bi bi-envelope-at-fill"></i></span>
                                    <input type="email" name="email" class="form-control premium-input" value="<?= htmlspecialchars((string) $user['email']) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6 mt-0">
                                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('password_edit_label') ?></label>
                                <div class="input-group shadow-sm-hover rounded-3 user-edit-password-group">
                                    <span class="input-group-text border-theme-light bg-soft-danger text-danger"><i class="bi bi-key-fill"></i></span>
                                    <input type="password" name="password" id="user-edit-password" class="form-control premium-input" autocomplete="new-password" aria-describedby="user-edit-password-help">
                                    <button type="button" class="btn btn-outline-secondary user-edit-password-toggle" id="user-edit-password-toggle" aria-label="<?= htmlspecialchars((string) __('show_password'), ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars((string) __('show_password'), ENT_QUOTES, 'UTF-8') ?>" aria-controls="user-edit-password" data-show-label="<?= htmlspecialchars((string) __('show_password'), ENT_QUOTES, 'UTF-8') ?>" data-hide-label="<?= htmlspecialchars((string) __('hide_password'), ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <div class="form-text" id="user-edit-password-help"><?= __('leave_blank_to_keep_current') ?></div>
                            </div>
                            <div class="col-md-6 mt-0">
                                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('access_profile') ?></label>
                                <select name="role" id="user-edit-role" class="form-select premium-select">
                                    <option value="enseignant" <?= $user['role'] === 'enseignant' ? 'selected' : '' ?>><?= __('teacher_classic') ?></option>
                                    <option value="personnel" <?= $user['role'] === 'personnel' ? 'selected' : '' ?>><?= __('role_personnel_access') ?></option>
                                    <option value="direction_academique" <?= $user['role'] === 'direction_academique' ? 'selected' : '' ?>><?= __('direction_academique_role_label') ?></option>
                                    <option value="caissier" <?= $user['role'] === 'caissier' ? 'selected' : '' ?>><?= __('role_caissier_option') ?></option>
                                    <option value="comptable" <?= $user['role'] === 'comptable' ? 'selected' : '' ?>><?= __('role_comptable_option') ?></option>
                                    <?php if (\App\Core\Session::get('user_role') !== 'admin'): ?>
                                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>><?= __('admin_restricted') ?></option>
                                    <?php endif; ?>
                                    <option value="it_manager" <?= $user['role'] === 'it_manager' ? 'selected' : '' ?>><?= __('role_it_manager_option') ?></option>
                                    <?php if (\App\Core\Session::get('user_role') !== 'admin' && \App\Core\PermissionManager::hasPermission('manage_rbac')): ?>
                                        <option value="superadmin" <?= $user['role'] === 'superadmin' ? 'selected' : '' ?>><?= __('superadmin_max') ?></option>
                                    <?php endif; ?>
                                </select>
                                <div class="form-text"><?= __('user_role_access_help') ?></div>
                            </div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_staff" id="user-edit-is-staff" value="1" <?= !empty($user['is_staff']) || in_array($user['role'], ['enseignant', 'personnel'], true) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold" for="user-edit-is-staff"><?= __('user_is_staff') ?></label>
                        </div>
                        <fieldset id="user-edit-personnel-fields" class="row g-3 mb-4" <?= !empty($user['is_staff']) || in_array($user['role'], ['enseignant', 'personnel'], true) ? '' : 'hidden disabled' ?>>
                            <legend class="col-12 border-bottom border-theme-light pb-2 mb-2">
                                <span class="fw-black text-primary text-uppercase letter-spacing-1"><?= __('staff_information') ?></span>
                            </legend>
                            <div class="col-md-6 mt-0">
                                <label for="user-edit-cni" class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('staff_cni_number') ?></label>
                                <input id="user-edit-cni" type="text" name="cni_number" class="form-control premium-input <?= ($errorField ?? '') === 'cni_number' ? 'is-invalid' : '' ?>" maxlength="64" value="<?= htmlspecialchars((string) ($user['cni_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-6 mt-0">
                                <label for="user-edit-nui" class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('staff_nui_number') ?></label>
                                <input id="user-edit-nui" type="text" name="nui_number" class="form-control premium-input <?= ($errorField ?? '') === 'nui_number' ? 'is-invalid' : '' ?>" maxlength="64" value="<?= htmlspecialchars((string) ($user['nui_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                                <div class="form-text"><?= __('staff_nui_help') ?></div>
                            </div>
                            <div class="col-md-6 mt-0">
                                <label for="user-edit-job-title" class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('staff_job_title') ?></label>
                                <input id="user-edit-job-title" type="text" name="job_title" class="form-control premium-input <?= ($errorField ?? '') === 'job_title' ? 'is-invalid' : '' ?>" maxlength="150" value="<?= htmlspecialchars((string) ($user['job_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-6 mt-0">
                                <label for="user-edit-salary" class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('staff_base_salary') ?></label>
                                <input id="user-edit-salary" type="number" name="base_salary" class="form-control premium-input <?= ($errorField ?? '') === 'base_salary' ? 'is-invalid' : '' ?>" min="0" step="0.01" value="<?= htmlspecialchars((string) ($user['base_salary'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                        </fieldset>

                        <div class="alert bg-soft-warning border border-warning border-opacity-10 rounded-3 p-3 mt-2 mb-3 user-edit-warning" role="note">
                            <div class="d-flex align-items-start gap-3">
                                <div class="avatar-sm bg-warning bg-opacity-20 text-warning rounded-2 d-flex align-items-center justify-content-center flex-shrink-0">
                                    <i class="bi bi-shield-exclamation fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-warning-emphasis mb-1"><?= __('modification_warning') ?></h6>
                                    <p class="text-muted-theme small mb-0"><?= __('role_access_warning') ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end border-top border-theme-light pt-3 pt-sm-4 mt-3 mt-sm-4">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 px-sm-5 py-2 fw-bold shadow-sm transition-base scale-on-hover user-edit-submit">
                                <i class="bi bi-check-circle-fill me-2"></i> <?= __('update_account') ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="modern-card border-0 shadow-sm p-4 h-100 bg-primary bg-opacity-5 border border-primary border-opacity-10">
                <h5 class="fw-black text-main-theme mb-4"><?= __('user_management_tips') ?></h5>
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex align-items-start gap-3 bg-white bg-opacity-50 p-3 rounded-4 shadow-sm">
                        <div class="avatar-xs bg-white text-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center flex-shrink-0">
                            <i class="bi bi-shield-fill-check"></i>
                        </div>
                        <div>
                            <p class="small text-muted-theme mb-0 lh-sm"><?= __('user_edit_tip_1') ?></p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3 bg-white bg-opacity-50 p-3 rounded-4 shadow-sm">
                        <div class="avatar-xs bg-white text-info rounded-circle shadow-sm d-flex align-items-center justify-content-center flex-shrink-0">
                            <i class="bi bi-key-fill"></i>
                        </div>
                        <div>
                            <p class="small text-muted-theme mb-0 lh-sm"><?= __('user_edit_tip_2') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-xs { width: 32px; height: 32px; font-size: 0.8rem; }
    .avatar-sm { width: 40px; height: 40px; }
    .scale-on-hover { transition: transform 0.2s ease; }
    .scale-on-hover:hover { transform: scale(1.02); }
    .user-edit-page .letter-spacing-1 { letter-spacing: 0; font-size: 0.82rem; }
    .user-edit-password-toggle { min-width: 44px; }
    .user-edit-password-group .form-control { min-width: 0; }
    #user-edit-personnel-fields[hidden] { display: none !important; }

    @media (max-width: 575.98px) {
        .user-edit-back-link,
        .user-edit-submit { width: 100%; }

        .user-edit-warning { padding: 0.9rem !important; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const passwordInput = document.getElementById('user-edit-password');
    const passwordToggle = document.getElementById('user-edit-password-toggle');
    const roleSelect = document.getElementById('user-edit-role');
    const staffToggle = document.getElementById('user-edit-is-staff');
    const personnelFields = document.getElementById('user-edit-personnel-fields');

    if (passwordInput && passwordToggle) {
        passwordToggle.addEventListener('click', function () {
            const shouldReveal = passwordInput.type === 'password';
            const label = shouldReveal ? passwordToggle.dataset.hideLabel : passwordToggle.dataset.showLabel;
            passwordInput.type = shouldReveal ? 'text' : 'password';
            passwordToggle.setAttribute('aria-label', label);
            passwordToggle.title = label;
            passwordToggle.querySelector('i').className = shouldReveal ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    }

    if (roleSelect && staffToggle && personnelFields) {
        const updatePersonnelFields = function () {
            const isStaff = staffToggle.checked;
            personnelFields.hidden = !isStaff;
            personnelFields.disabled = !isStaff;
        };
        staffToggle.addEventListener('change', updatePersonnelFields);
        roleSelect.addEventListener('change', function () {
            if (roleSelect.value === 'enseignant' || roleSelect.value === 'personnel') {
                staffToggle.checked = true;
                updatePersonnelFields();
            }
        });
        updatePersonnelFields();
    }
});
</script>

<?php 
$content = ob_get_clean(); 
include __DIR__ . '/../templates/layout.php'; 
?>
