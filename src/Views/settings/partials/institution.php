<div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in bg-glass-theme">
    <div class="card-body p-4 p-md-5">
        <div class="row g-4">
            <div class="col-md-12 border-bottom border-theme-light pb-2 mb-2">
                <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1">
                    <?= __('institution_identity') ?>
                </h6>
            </div>
            
            <div class="col-md-7 mt-0">
                <div class="mb-4">
                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('institution_name_label') ?></label>
                    <input type="text" name="school_name" class="form-control premium-input" 
                        placeholder="<?= __('institution_name_label') ?>"
                        value="<?= htmlspecialchars((string) ($settings['school_name'] ?? '')) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('school_code_abbreviation') ?></label>
                    <div class="input-group">
                        <span class="input-group-text border-theme-light bg-soft-primary text-primary font-monospace fw-bold">ID</span>
                        <input type="text" name="school_code" class="form-control premium-input font-monospace fw-bold text-primary" 
                            placeholder="Code" value="<?= htmlspecialchars((string) ($settings['school_code'] ?? '')) ?>" required>
                    </div>
                </div>
            </div>

            <div class="col-md-5 mt-md-0">
                <div class="p-4 border-theme-dynamic rounded-4 bg-soft-light text-center h-100 d-flex flex-column justify-content-center align-items-center">
                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2 d-block"><?= __('institution_logo') ?></label>
                    <div class="position-relative mb-3 group-hover">
                        <?php 
                        $db = \App\Core\Database::getInstance()->getConnection();
                        $logoManager = \App\Core\LogoManager::getInstance($db);
                        ?>
                        <?php if ($logoManager->hasLogo()): ?>
                            <?= $logoManager->getLogoHtml('Logo', 'img-fluid rounded-4 shadow-sm border border-theme-light', ['style' => 'max-height: 100px; min-width: 100px; object-fit: contain;']) ?>
                        <?php else: ?>
                            <div class="avatar-init bg-soft-primary text-primary rounded-4 d-flex align-items-center justify-content-center shadow-sm" style="width: 100px; height: 100px;">
                                <i class="bi bi-image fs-1 opacity-25"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="school_logo" class="form-control premium-input-sm border-theme-light shadow-none" accept="image/*">
                </div>
            </div>

            <!-- Administration & Localization -->
            <div class="col-md-12 border-bottom border-theme-light pb-2 mb-2 mt-5">
                <h6 class="fw-black text-success m-0 text-uppercase letter-spacing-1">
                    <?= __('republic_ministries') ?>
                </h6>
            </div>

            <div class="col-md-6 mt-0">
                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('republic_ministries') ?> (FR)</label>
                <div class="input-group mb-3">
                    <span class="input-group-text border-theme-light bg-soft-light text-muted fw-bold extra-small">FR</span>
                    <input type="text" name="school_republic" class="form-control premium-input"
                        value="<?= htmlspecialchars((string) ($settings['school_republic'] ?? '')) ?>"
                        placeholder="<?= __('school_republic_fr_placeholder') ?>">
                </div>
                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('republic_ministries') ?> (EN)</label>
                <div class="input-group">
                    <span class="input-group-text border-theme-light bg-soft-light text-muted fw-bold extra-small">EN</span>
                    <input type="text" name="school_republic_en" class="form-control premium-input"
                        value="<?= htmlspecialchars((string) ($settings['school_republic_en'] ?? '')) ?>"
                        placeholder="<?= __('school_republic_en_placeholder') ?>">
                </div>
            </div>

            <div class="col-md-6 mt-md-0 mt-4">
                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('supervising_ministry') ?> (FR)</label>
                <div class="input-group mb-3">
                    <span class="input-group-text border-theme-light bg-soft-light text-muted fw-bold extra-small">FR</span>
                    <input type="text" name="school_ministry" class="form-control premium-input"
                        value="<?= htmlspecialchars((string) ($settings['school_ministry'] ?? '')) ?>"
                        placeholder="<?= __('school_ministry_fr_placeholder') ?>">
                </div>
                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('supervising_ministry') ?> (EN)</label>
                <div class="input-group">
                    <span class="input-group-text border-theme-light bg-soft-light text-muted fw-bold extra-small">EN</span>
                    <input type="text" name="school_ministry_en" class="form-control premium-input"
                        value="<?= htmlspecialchars((string) ($settings['school_ministry_en'] ?? '')) ?>"
                        placeholder="<?= __('school_ministry_en_placeholder') ?>">
                </div>
            </div>

            <div class="col-12 mt-5">
                <div class="p-4 rounded-4 bg-soft-primary border border-primary border-opacity-10 position-relative overflow-hidden">
                    <div class="position-absolute top-0 end-0 p-3 opacity-10"><i class="bi bi-quote fs-1"></i></div>
                    <label class="form-label text-primary fw-black extra-small text-uppercase mb-3"><?= __('slogan_and_motto') ?></label>
                    <div class="row g-3 position-relative" style="z-index: 1;">
                        <div class="col-md-6">
                            <input type="text" name="school_slogan" class="form-control premium-input border-0 shadow-sm"
                                value="<?= htmlspecialchars((string) ($settings['school_slogan'] ?? '')) ?>"
                                placeholder="<?= __('school_slogan_fr_placeholder') ?>">
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="school_slogan_en" class="form-control premium-input border-0 shadow-sm"
                                value="<?= htmlspecialchars((string) ($settings['school_slogan_en'] ?? '')) ?>"
                                placeholder="<?= __('school_slogan_en_placeholder') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact & Localization -->
            <div class="col-md-12 border-bottom border-theme-light pb-2 mb-2 mt-5">
                <h6 class="fw-black text-info m-0 text-uppercase letter-spacing-1">
                    <?= __('contact_and_localization') ?>
                </h6>
            </div>

            <div class="col-md-4">
                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('city_location') ?></label>
                <input type="text" name="school_city" class="form-control premium-input"
                    value="<?= htmlspecialchars((string) ($settings['school_city'] ?? '')) ?>"
                    placeholder="<?= __('city_location_placeholder') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('phone') ?></label>
                <input type="text" name="school_phone" class="form-control premium-input"
                    value="<?= htmlspecialchars((string) ($settings['school_phone'] ?? '')) ?>"
                    placeholder="<?= __('phone_placeholder') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('email') ?></label>
                <input type="email" name="school_email" class="form-control premium-input"
                    value="<?= htmlspecialchars((string) ($settings['school_email'] ?? '')) ?>"
                    placeholder="<?= __('email_placeholder') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('po_box') ?></label>
                <input type="text" name="school_po_box" class="form-control premium-input"
                    value="<?= htmlspecialchars((string) ($settings['school_po_box'] ?? '')) ?>"
                    placeholder="<?= __('po_box_placeholder') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('website') ?></label>
                <input type="text" name="school_website" class="form-control premium-input"
                    value="<?= htmlspecialchars((string) ($settings['school_website'] ?? '')) ?>"
                    placeholder="<?= __('website_placeholder') ?>">
            </div>
        </div>
    </div>
</div>