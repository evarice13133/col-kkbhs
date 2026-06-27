<?php $title = __('user_editing'); ob_start(); ?>

<div class="animate-fade-in container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-black text-main-theme mb-0 fs-4"><?= __('edit_user') ?></h2>
            <p class="text-muted-theme small mb-0"><?= __('user_editing_subtitle') ?></p>
        </div>
        <a href="/users" class="btn btn-sm btn-light-theme rounded-pill px-3 border-theme-light shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> <?= __('back_to_list') ?>
        </a>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="modern-card border-0 shadow-sm overflow-hidden mb-4 bg-glass-theme">
                <div class="card-body p-4 p-md-5 position-relative">
                    
                    <?php if ($error = App\Core\Session::getFlash('error')): ?>
                        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 py-3 d-flex align-items-center animate-fade-in">
                            <i class="bi bi-exclamation-octagon-fill me-3 fs-4"></i>
                            <div class="fw-bold"><?= htmlspecialchars((string) $error) ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="/users/update?id=<?= $user['id'] ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                        
                        <!-- Identification -->
                        <div class="row g-4 mb-4">
                            <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                                <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1" style="font-size: 0.75rem;"><?= __('user_identity') ?></h6>
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
                        <div class="row g-4 mb-4">
                            <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                                <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1" style="font-size: 0.75rem;"><?= __('account_credentials') ?></h6>
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

                        <div class="row g-4 mb-4">
                            <div class="col-md-6 mt-0">
                                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('password_edit_label') ?></label>
                                <div class="input-group shadow-sm-hover rounded-3">
                                    <span class="input-group-text border-theme-light bg-soft-danger text-danger"><i class="bi bi-key-fill"></i></span>
                                    <input type="password" name="password" class="form-control premium-input" placeholder="<?= __('leave_blank_to_keep_current') ?>">
                                </div>
                            </div>
                            <div class="col-md-6 mt-0">
                                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('user_role_label') ?></label>
                                <select name="role" class="form-select premium-select">
                                    <option value="enseignant" <?= $user['role'] === 'enseignant' ? 'selected' : '' ?>><?= __('teacher_classic') ?></option>
                                    <?php if (\App\Core\PermissionManager::hasRole('superadmin')): ?>
                                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>><?= __('admin_restricted') ?></option>
                                        <option value="superadmin" <?= $user['role'] === 'superadmin' ? 'selected' : '' ?>><?= __('superadmin_max') ?></option>
                                        <option value="caissier" <?= $user['role'] === 'caissier' ? 'selected' : '' ?>><?= __('role_caissier_option') ?></option>
                                        <option value="comptable" <?= $user['role'] === 'comptable' ? 'selected' : '' ?>><?= __('role_comptable_option') ?></option>
                                        <option value="it_manager" <?= $user['role'] === 'it_manager' ? 'selected' : '' ?>><?= __('role_it_manager_option') ?></option>
                                    <?php elseif (\App\Core\PermissionManager::hasRole('admin')): ?>
                                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>><?= __('admin_restricted') ?></option>
                                        <option value="caissier" <?= $user['role'] === 'caissier' ? 'selected' : '' ?>><?= __('role_caissier_option') ?></option>
                                        <option value="comptable" <?= $user['role'] === 'comptable' ? 'selected' : '' ?>><?= __('role_comptable_option') ?></option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="alert bg-soft-warning border-0 rounded-4 p-4 mt-2 shadow-none border border-warning border-opacity-10">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-warning bg-opacity-20 text-warning rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0">
                                    <i class="bi bi-shield-lock-fill fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-warning-emphasis mb-1"><?= __('modification_warning') ?></h6>
                                    <p class="text-muted-theme extra-small mb-0 opacity-75"><?= __('role_access_warning') ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end border-top border-theme-light pt-4 mt-4">
                            <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm transition-base scale-on-hover">
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
    .letter-spacing-1 { letter-spacing: 1px; }
</style>

<?php 
$content = ob_get_clean(); 
include __DIR__ . '/../templates/layout.php'; 
?>
