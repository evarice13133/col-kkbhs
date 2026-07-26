<?php
$title = __('edit_teacher');
ob_start();
?>

<div class="animate-fade-in container-fluid py-2">
    <!-- Compact Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h2 class="fw-black text-main-theme mb-0 fs-4"><?= __('edit_teacher') ?></h2>
            <p class="text-muted-theme small mb-0"><?= h($teacher['nom'] . ' ' . $teacher['prenom']) ?> • <?= h($teacher['username']) ?></p>
        </div>
        <a href="/teachers" class="btn btn-sm btn-light-theme rounded-pill px-3 border-theme-light">
            <i class="bi bi-arrow-left me-1"></i> <?= __('back_to_list') ?>
        </a>
    </div>

    <form action="/teachers/update?id=<?= (int) $teacher['id'] ?>" method="POST" id="teacherEditForm">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show rounded-4 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars((string) $error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

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
                            placeholder="<?= __('name_placeholder') ?>" value="<?= h($teacher['nom'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('first_names') ?></label>
                        <input type="text" name="prenom" class="form-control premium-input" 
                            placeholder="<?= __('first_name_placeholder') ?>" value="<?= h($teacher['prenom'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('contact_email_optional') ?></label>
                        <input type="email" name="email" class="form-control premium-input" 
                            placeholder="<?= __('contact_email_placeholder') ?>" value="<?= h($teacher['email'] ?? '') ?>">
                    </div>
                </div>


                <!-- Teaching Type Section -->
                <div class="row g-4 mb-4">
                    <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                        <h6 class="fw-black text-warning m-0 text-uppercase letter-spacing-1">Type d'Enseignement</h6>
                    </div>
                    
                    <div class="col-12">
                        <div class="d-flex flex-wrap gap-3">
                            <?php foreach ($teachingTypes as $tt): ?>
                                <?php $isChecked = in_array((string)$tt['id'], array_map('strval', $teacher['teaching_type_ids'] ?? []), true); ?>
                                <div class="form-check">
                                    <input class="form-check-input border-primary" type="checkbox" name="teaching_type_ids[]" value="<?= $tt['id'] ?>" id="tt_<?= $tt['id'] ?>" <?= $isChecked ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-bold" for="tt_<?= $tt['id'] ?>">
                                        <?= h($tt['nom']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
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
                            placeholder="<?= __('username_placeholder') ?>" value="<?= h($teacher['username'] ?? '') ?>" required>
                        <div class="form-text extra-small mt-1 opacity-75"><?= __('username_help') ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('change_password') ?></label>
                        <div class="input-group shadow-none">
                            <span class="input-group-text bg-light border-theme-light text-primary"><i class="bi bi-shield-lock"></i></span>
                            <input type="password" name="password" class="form-control premium-input border-start-0" 
                                placeholder="<?= __('password_leave_blank') ?>" autocomplete="new-password">
                        </div>
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
