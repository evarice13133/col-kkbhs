<?php
$title = __('define_academic_year');
ob_start();
?>

<div class="animate-fade-in container-fluid py-2">
    <!-- Compact Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="fw-black text-main-theme mb-0 fs-4"><?= __('create_new_academic_year') ?></h2>
        <a href="/academic_years" class="btn btn-sm btn-light-theme rounded-pill px-3 border-theme-light">
            <i class="bi bi-arrow-left me-1"></i> <?= __('back_to_list') ?>
        </a>
    </div>

    <form action="/academic_years/store" method="POST" id="academicYearCreateForm">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">

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
