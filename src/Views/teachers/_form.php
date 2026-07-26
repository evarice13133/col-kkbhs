<form action="/teachers/store" method="POST" id="teacherCreateForm">
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
            <div class="row g-4 mb-4">
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

            <!-- Teaching Type Section -->
            <div class="row g-4 mb-4">
                <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                    <h6 class="fw-black text-warning m-0 text-uppercase letter-spacing-1"><?= __('teaching_type') ?> <span class="text-danger">*</span></h6>
                </div>
                
                <div class="col-12">
                    <div class="d-flex flex-wrap gap-3">
                        <?php if (!empty($teachingTypes)): ?>
                            <?php foreach ($teachingTypes as $tt): ?>
                                <div class="form-check">
                                    <input class="form-check-input border-primary" type="checkbox" name="teaching_type_ids[]" value="<?= $tt['id'] ?>" id="tt_<?= $tt['id'] ?>">
                                    <label class="form-check-label fw-bold" for="tt_<?= $tt['id'] ?>">
                                        <?= h($tt['nom']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="text-muted small"><?= __('no_data') ?></span>
                        <?php endif; ?>
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
            <div class="d-flex justify-content-end border-top border-theme-light pt-4 mt-2 gap-2">
                <?php if (isset($isModal) && $isModal): ?>
                    <button type="button" class="btn btn-light-theme rounded-pill px-4" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm transition-base scale-on-hover">
                    <i class="bi bi-check-circle-fill me-2"></i> <?= __('validate') ?? 'Valider' ?>
                </button>
            </div>

        </div>
    </div>
</form>
