<div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in bg-glass-theme">
    <div class="card-body p-4 p-md-5">
        <div class="row g-4">
            <div class="col-md-12 border-bottom border-theme-light pb-2 mb-2">
                <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1"><?= __('dashboard_theme') ?></h6>
            </div>

            <div class="col-md-6 mt-0">
                <div class="p-4 border-theme-dynamic rounded-4 bg-soft-light shadow-sm h-100">
                    <label class="form-label text-primary fw-black extra-small text-uppercase mb-4">
                        <i class="bi bi-layout-sidebar-inset-reverse me-2"></i><?= __('sidebar_navigation') ?>
                    </label>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2"><?= __('background_label') ?></label>
                            <div class="color-picker-wrapper p-1 bg-white rounded-circle shadow-sm d-inline-block">
                                <input type="color" name="theme_navbar_bg"
                                    class="form-control form-control-color border-0 rounded-circle shadow-none" style="width: 44px; height: 44px;"
                                    value="<?= htmlspecialchars((string) ($settings['theme_navbar_bg'] ?? '#0f172a')) ?>">
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2"><?= __('hover_label') ?></label>
                            <div class="color-picker-wrapper p-1 bg-white rounded-circle shadow-sm d-inline-block">
                                <input type="color" name="theme_navbar_hover"
                                    class="form-control form-control-color border-0 rounded-circle shadow-none" style="width: 44px; height: 44px;"
                                    value="<?= htmlspecialchars((string) ($settings['theme_navbar_hover'] ?? '#1e293b')) ?>">
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 pt-3 border-top border-theme-light">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-4"><?= __('action_button_colors') ?></label>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2"><?= __('button_background') ?></label>
                                <input type="color" name="theme_button_bg"
                                    class="form-control form-control-color w-100 premium-input border-0 p-1" style="height: 44px;"
                                    value="<?= htmlspecialchars((string) ($settings['theme_button_bg'] ?? '#3b82f6')) ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2"><?= __('button_text') ?></label>
                                <input type="color" name="theme_button_text"
                                    class="form-control form-control-color w-100 premium-input border-0 p-1" style="height: 44px;"
                                    value="<?= htmlspecialchars((string) ($settings['theme_button_text'] ?? '#ffffff')) ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mt-md-0">
                <div class="p-4 border-theme-dynamic rounded-4 bg-soft-primary shadow-sm h-100">
                    <label class="form-label text-primary fw-black extra-small text-uppercase mb-4">
                        <i class="bi bi-display me-2"></i><?= __('hero_banners') ?>
                    </label>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2"><?= __('gradient_start') ?></label>
                            <input type="color" name="theme_admin_hero_start"
                                class="form-control form-control-color w-100 premium-input border-0 p-1" style="height: 44px;"
                                value="<?= htmlspecialchars((string) ($settings['theme_admin_hero_start'] ?? '#16324f')) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2"><?= __('gradient_end') ?></label>
                            <input type="color" name="theme_admin_hero_end"
                                class="form-control form-control-color w-100 premium-input border-0 p-1" style="height: 44px;"
                                value="<?= htmlspecialchars((string) ($settings['theme_admin_hero_end'] ?? '#2f6fed')) ?>">
                        </div>
                        <div class="col-12 pt-3">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2"><?= __('glow_effect') ?></label>
                            <input type="color" name="theme_admin_hero_glow"
                                class="form-control form-control-color w-100 premium-input border-0 p-1" style="height: 44px;"
                                value="<?= htmlspecialchars((string) ($settings['theme_admin_hero_glow'] ?? '#f4b942')) ?>">
                            <small class="extra-small text-muted-theme opacity-75 mt-2 d-block"><?= __('hero_glow_help') ?></small>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top border-theme-light">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2"><?= __('kpi_card_color') ?></label>
                        <input type="color" name="theme_admin_hero_card"
                            class="form-control form-control-color w-100 premium-input border-0 p-1" style="height: 44px;"
                            value="<?= htmlspecialchars((string) ($settings['theme_admin_hero_card'] ?? '#5d7894')) ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>