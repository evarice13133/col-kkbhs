<form action="/academic_years/store" method="POST" id="academicYearCreateForm">
    <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">

    <?php if (isset($error)): ?>
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars((string) $error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="subject-card-compact border-0 shadow-sm overflow-hidden mb-4">
        <div class="card-body p-4">
            
            <!-- Identification Section -->
            <div class="row g-4 mb-4">
                <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                    <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1"><?= __('academic_year_config') ?></h6>
                </div>
                
                <div class="col-md-12">
                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('academic_year_name_label') ?></label>
                    <input type="text" name="nom" class="form-control premium-input" 
                        placeholder="<?= __('academic_year_name_placeholder') ?>" required autofocus>
                    <div class="form-text extra-small mt-1 opacity-75"><?= __('academic_year_public_label_help') ?></div>
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
