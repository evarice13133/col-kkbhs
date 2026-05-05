<?php $title = __('add_section'); ob_start(); ?>

<div class="animate-fade-in container-fluid py-2">
    <!-- Compact Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="fw-black text-main-theme mb-0 fs-4"><?= __('add_section') ?></h2>
        <a href="/sections" class="btn btn-sm btn-light-theme rounded-pill px-3 border-theme-light">
            <i class="bi bi-arrow-left me-1"></i> <?= __('back_to_list') ?>
        </a>
    </div>

    <form action="/sections/store" method="POST" id="sectionCreateForm">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="subject-card-compact border-0 shadow-sm overflow-hidden mb-4">
                    <div class="card-body p-4">
                        
                        <!-- Identification Section -->
                        <div class="row g-4 mb-4">
                            <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                                <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1"><?= __('section_details') ?></h6>
                            </div>
                            
                            <div class="col-md-12">
                                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('section_name_label') ?></label>
                                <input type="text" name="nom" class="form-control premium-input" 
                                    placeholder="Ex: Anglophone, Francophone, etc." required autofocus>
                                <div class="form-text extra-small mt-1 opacity-75"><?= __('new_section_subtitle') ?></div>
                            </div>
                        </div>

                        <!-- Action Footer -->
                        <div class="d-flex justify-content-end border-top border-theme-light pt-4 mt-2">
                            <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm transition-base scale-on-hover">
                                <i class="bi bi-check-circle-fill me-2"></i> <?= __('save_section') ?>
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="p-4 rounded-4 bg-soft-primary border border-primary border-opacity-10 h-100 shadow-sm">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="avatar-xs bg-white text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm">
                            <i class="bi bi-info-circle-fill"></i>
                        </div>
                        <h6 class="fw-bold m-0 text-main-theme small text-uppercase letter-spacing-1"><?= __('why_sections_title') ?></h6>
                    </div>
                    <p class="extra-small text-muted-theme mb-4 opacity-75 lh-sm">
                        <?= __('sections_explanation_text') ?>
                    </p>
                    <ul class="list-unstyled d-flex flex-column gap-3 m-0">
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check2-circle text-success fs-5"></i>
                            <span class="extra-small text-muted-theme fw-bold"><?= __('sections_benefit_1') ?></span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check2-circle text-success fs-5"></i>
                            <span class="extra-small text-muted-theme fw-bold"><?= __('sections_benefit_2') ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>

<?php 
$content = ob_get_clean(); 
include __DIR__ . '/../templates/layout.php'; 
?>
