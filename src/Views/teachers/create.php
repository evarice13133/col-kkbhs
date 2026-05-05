<?php
$title = __('add_teacher');
ob_start();
?>

<div class="animate-fade-in container-fluid py-2">
    <!-- Compact Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="fw-black text-main-theme mb-0 fs-4"><?= __('teacher_profile_opening') ?></h2>
        <a href="/teachers" class="btn btn-sm btn-light-theme rounded-pill px-3 border-theme-light">
            <i class="bi bi-arrow-left me-1"></i> <?= __('back_to_list') ?>
        </a>
    </div>

    <form action="/teachers/store" method="POST" id="teacherCreateForm">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">

        <div class="subject-card-compact border-0 shadow-sm overflow-hidden mb-4">
            <div class="card-body p-4">
                
                <!-- Identity Section -->
                <div class="row g-4 mb-5">
                    <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                        <h6 class="fw-black text-success m-0 text-uppercase letter-spacing-1"><?= __('employee_identity') ?></h6>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('name') ?></label>
                        <input type="text" name="nom" class="form-control premium-input" 
                            placeholder="<?= __('name_placeholder') ?>" required autofocus>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('first_names') ?></label>
                        <input type="text" name="prenom" class="form-control premium-input" 
                            placeholder="<?= __('first_name_placeholder') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('contact_email_optional') ?></label>
                        <input type="email" name="email" class="form-control premium-input" 
                            placeholder="<?= __('contact_email_placeholder') ?>">
                    </div>
                </div>

                <!-- Account Section -->
                <div class="row g-4 mb-4">
                    <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                        <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1"><?= __('login_information') ?></h6>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('username_login') ?></label>
                        <input type="text" name="username" class="form-control premium-input" 
                            placeholder="<?= __('username_placeholder') ?>" required>
                        <div class="form-text extra-small mt-1 opacity-75"><?= __('username_help') ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('temporary_password') ?></label>
                        <div class="input-group shadow-none">
                            <span class="input-group-text bg-light border-theme-light text-primary"><i class="bi bi-key-fill"></i></span>
                            <input type="text" name="password" class="form-control premium-input border-start-0" value="0000" required>
                        </div>
                        <div class="form-text extra-small mt-1 text-danger opacity-75"><i class="bi bi-exclamation-triangle-fill me-1"></i><?= __('temporary_password_help') ?></div>
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="d-flex justify-content-end border-top border-theme-light pt-4 mt-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm transition-base scale-on-hover">
                        <i class="bi bi-check-circle-fill me-2"></i> Valider
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>