<div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in bg-glass-theme">
    <div class="card-body p-4 p-md-5">  
        <div class="row g-4">
            <div class="col-md-12 border-bottom border-theme-light pb-2 mb-2">
                <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1"><?= __('login_page_theme') ?></h6>
            </div>

            <div class="col-md-6 mt-0">
                <div class="p-4 border-theme-dynamic rounded-4 bg-soft-light shadow-sm h-100">
                    <label class="form-label text-primary fw-black extra-small text-uppercase mb-4">
                        <i class="bi bi-circle-square me-2"></i><?= __('background_and_elements') ?>
                    </label>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2"><?= __('background_start') ?></label>
                            <input type="color" name="theme_login_bg_start"
                                class="form-control form-control-color w-100 premium-input border-0 p-1" style="height: 44px;"
                                value="<?= htmlspecialchars((string) ($settings['theme_login_bg_start'] ?? '#0a1726')) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2"><?= __('background_end') ?></label>
                            <input type="color" name="theme_login_bg_end"
                                class="form-control form-control-color w-100 premium-input border-0 p-1" style="height: 44px;"
                                value="<?= htmlspecialchars((string) ($settings['theme_login_bg_end'] ?? '#2f6fed')) ?>">
                        </div>
                    </div>
                    <div class="mt-5 pt-3 border-top border-theme-light">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2"><?= __('decorative_glow') ?></label>
                        <input type="color" name="theme_login_bubble" class="form-control form-control-color w-100 premium-input border-0 p-1" style="height: 44px;"
                            value="<?= htmlspecialchars((string) ($settings['theme_login_bubble'] ?? '#f4b942')) ?>">
                    </div>
                </div>
            </div>

            <div class="col-md-6 mt-md-0">
                <div class="p-4 border-theme-dynamic rounded-4 bg-soft-primary shadow-sm h-100">
                    <label class="form-label text-primary fw-black extra-small text-uppercase mb-4">
                        <i class="bi bi-window-split me-2"></i><?= __('login_panel_title') ?>
                    </label>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2"><?= __('button_color') ?></label>
                            <input type="color" name="theme_login_button"
                                class="form-control form-control-color w-100 premium-input border-0 p-1" style="height: 44px;"
                                value="<?= htmlspecialchars((string) ($settings['theme_login_button'] ?? '#1f5fbf')) ?>">
                        </div>
                        <div class="col-md-6 pt-3">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2"><?= __('panel_background') ?></label>
                            <input type="color" name="theme_login_panel_bg"
                                class="form-control form-control-color w-100 premium-input border-0 p-1" style="height: 44px;"
                                value="<?= htmlspecialchars((string) ($settings['theme_login_panel_bg'] ?? '#ffffff')) ?>">
                        </div>
                        <div class="col-md-6 pt-3">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2"><?= __('accent_badge') ?></label>
                            <input type="color" name="theme_login_panel_badge_bg"
                                class="form-control form-control-color w-100 premium-input border-0 p-1" style="height: 44px;"
                                value="<?= htmlspecialchars((string) ($settings['theme_login_panel_badge_bg'] ?? '#e8f0ff')) ?>">
                        </div>
                    </div>
                    <div class="mt-4 pt-2">
                        <small class="extra-small text-muted-theme opacity-75"><i class="bi bi-info-circle me-1"></i> <?= __('contrast_help') ?></small>
                    </div>
                </div>
            </div>

            <!-- Options supplémentaires du Login -->
            <div class="col-md-12 border-bottom border-theme-light pb-2 mb-2 mt-4">
                <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1"><?= __('login_features') ?? 'Fonctionnalités' ?></h6>
            </div>

            <div class="col-md-12 mt-0">
                <div class="p-4 border-theme-dynamic rounded-4 bg-soft-light shadow-sm">
                    <div class="form-check form-switch d-flex align-items-start gap-3 m-0">
                        <input type="hidden" name="allow_teacher_registration" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="allow_teacher_registration" name="allow_teacher_registration" value="1" <?= (isset($settings['allow_teacher_registration']) && $settings['allow_teacher_registration'] == '1') ? 'checked' : '' ?> style="transform: scale(1.3); cursor: pointer; margin-top: 0.25rem;">
                        <label class="form-check-label fw-bold" for="allow_teacher_registration" style="cursor: pointer; color: var(--text-main);">
                            <?= __('allow_teacher_registration') ?? 'Autoriser l\'inscription des enseignants' ?>
                            <small class="d-block text-muted-theme fw-normal mt-1" style="font-size: 0.85rem;">Affiche un raccourci sur la page de connexion permettant aux enseignants de créer leur propre compte.</small>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>