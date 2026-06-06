<div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in bg-glass-theme">
    <div class="card-body p-4 p-md-5">
        <div class="row g-4">
            <div class="col-md-12 border-bottom border-theme-light pb-2 mb-2">
                <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1">
                    <?= __('academic_rules_validation') ?>
                </h6>
            </div>

            <div class="col-md-7 mt-0">
                <div class="p-4 border-theme-dynamic rounded-4 bg-soft-light h-100 shadow-sm">
                    <label class="form-label text-primary fw-black extra-small text-uppercase mb-3"><?= __('validation_signatures') ?></label>
                    <div class="mb-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('principal_name_label') ?></label>
                        <input type="text" name="principal_name" class="form-control premium-input" 
                            value="<?= htmlspecialchars((string) ($settings['principal_name'] ?? '')) ?>" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('principal_title_label') ?></label>
                        <input type="text" name="principal_title" class="form-control premium-input" 
                            value="<?= htmlspecialchars((string) ($settings['principal_title'] ?? '')) ?>"
                            placeholder="<?= __('principal_title_placeholder') ?>" required>
                    </div>
                </div>
            </div>

            <div class="col-md-5 mt-md-0">
                <div class="p-4 border-theme-dynamic rounded-4 bg-soft-primary h-100 shadow-sm text-center d-flex flex-column justify-content-center">
                    <label class="form-label text-primary fw-black extra-small text-uppercase mb-3"><?= __('digital_stamp_signature') ?></label>
                    <div class="row g-3 align-items-center">
                        <div class="col-6 text-start">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('signature_label') ?></label>
                            <input type="file" name="principal_signature" class="form-control premium-input-sm shadow-none">
                        </div>
                        <div class="col-6 text-start">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('school_stamp_label') ?></label>
                            <input type="file" name="school_stamp" class="form-control premium-input-sm shadow-none">
                        </div>
                    </div>
                    <div class="mt-3 py-2 px-3 bg-white bg-opacity-50 rounded-pill border border-primary border-opacity-10">
                        <small class="extra-small text-primary fw-bold"><i class="bi bi-info-circle me-1"></i> <?= __('recommended_format_png') ?></small>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mt-5">
                <div class="mb-4">
                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('school_year_pdf_format') ?></label>
                    <div class="input-group">
                        <span class="input-group-text border-theme-light bg-soft-primary text-primary"><i class="bi bi-calendar-range-fill"></i></span>
                        <input type="text" name="display_school_year" class="form-control premium-input font-monospace fw-bold"
                            value="<?= htmlspecialchars((string) ($settings['display_school_year'] ?? '')) ?>" placeholder="2025/2026">
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('signature_city_label') ?></label>
                    <div class="input-group">
                        <span class="input-group-text border-theme-light bg-soft-info text-info"><i class="bi bi-geo-alt-fill"></i></span>
                        <input type="text" name="school_city" class="form-control premium-input" 
                            value="<?= htmlspecialchars((string) ($settings['school_city'] ?? '')) ?>" placeholder="<?= __('signature_city_label') ?>">
                    </div>
                </div>
            </div>
                <div class="col-md-6 mt-5">
                    <div class="mb-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('honor_roll_default_threshold_label') ?></label>
                        <div class="input-group">
                            <span class="input-group-text border-theme-light bg-soft-info text-info"><i class="bi bi-award-fill"></i></span>
                            <input type="number" name="honor_roll_default_threshold" class="form-control premium-input" step="0.01" min="0" max="20"
                                value="<?= htmlspecialchars((string) ($settings['honor_roll_default_threshold'] ?? '12')) ?>" placeholder="12">
                        </div>
                        <small class="extra-small text-muted-theme opacity-75 mt-1 d-block"><?= __('honor_roll_default_threshold_help') ?></small>
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">Impression des bulletins</label>
                        <div class="d-flex align-items-center gap-3 p-3 border-theme-dynamic rounded-4 bg-soft-primary">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="bulletin_printing_enabled" id="bulletin_printing_enabled"
                                    value="1" <?= ($settings['bulletin_printing_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold text-primary small" for="bulletin_printing_enabled">
                                    <?= ($settings['bulletin_printing_enabled'] ?? '1') === '1' ? 'Activé' : 'Désactivé' ?>
                                </label>
                            </div>
                            <small class="extra-small text-muted-theme opacity-75">Active ou désactive l'accès à l'impression des bulletins pour les administrateurs.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>