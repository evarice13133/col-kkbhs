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
                <div class="mb-3">
                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('creation_decree_label') ?></label>
                    <input type="text" name="creation_decree" class="form-control premium-input" 
                        placeholder="<?= __('creation_decree_placeholder') ?>"
                        value="<?= htmlspecialchars((string) ($settings['creation_decree'] ?? '')) ?>">
                    <div class="form-text extra-small text-muted-theme mt-1">
                        <i class="bi bi-info-circle me-1"></i><?= __('creation_decree_help_text') ?>
                    </div>
                    <?php if (!empty($settings['creation_decree'])): ?>
                        <div class="mt-2 p-2.5 rounded-3 bg-light-theme border border-theme-light extra-small">
                            <div class="fw-bold text-primary mb-1"><i class="bi bi-eye-fill me-1"></i>Aperçu de l'affichage sur les documents officiels :</div>
                            <div class="text-main-theme lh-sm ps-2 border-start border-2 border-primary">
                                <?= \App\Core\Helpers::formatCreationDecree((string) $settings['creation_decree']) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-5 mt-md-0">
                <div class="row g-3">
                    <!-- LOGO ÉTABLISSEMENT -->
                    <div class="col-12">
                        <div class="p-3 border-theme-dynamic rounded-4 bg-soft-light text-center d-flex flex-column justify-content-center align-items-center">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2 d-block"><?= __('institution_logo') ?></label>
                            
                            <?php 
                            $db = \App\Core\Database::getInstance()->getConnection();
                            $currentTtId = $currentTeachingTypeId ?? null;
                            $logoManager = \App\Core\LogoManager::getInstance($db, $currentTtId);
                            $hasSchoolLogo = $logoManager->hasLogo();
                            ?>
                            
                            <div class="position-relative mb-2 group-hover">
                                <div id="school_logo_container">
                                    <?php if ($hasSchoolLogo): ?>
                                        <img id="school_logo_img" src="<?= $logoManager->getLogoBase64() ?>" alt="Logo" class="img-fluid rounded-4 shadow-sm border border-theme-light" style="max-height: 80px; min-width: 80px; object-fit: contain;">
                                    <?php else: ?>
                                        <div id="school_logo_placeholder" class="avatar-init bg-soft-primary text-primary rounded-4 d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px; margin: 0 auto;">
                                            <i class="bi bi-image fs-1 opacity-25"></i>
                                        </div>
                                        <img id="school_logo_img" src="" alt="Logo" class="img-fluid rounded-4 shadow-sm border border-theme-light d-none" style="max-height: 80px; min-width: 80px; object-fit: contain;">
                                    <?php endif; ?>
                                </div>
                                
                                <input type="hidden" name="delete_school_logo" id="delete_school_logo" value="0">
                            </div>

                            <div class="d-flex align-items-center gap-2 w-100 mt-1">
                                <input type="file" name="school_logo" id="school_logo_input" class="form-control premium-input-sm border-theme-light shadow-none" accept="image/*" onchange="previewImage(this, 'school_logo_img', 'school_logo_placeholder', 'school_logo_del_btn')">
                                <?php if ($hasSchoolLogo): ?>
                                    <button type="button" id="school_logo_del_btn" class="btn btn-outline-danger btn-sm rounded-3 px-2 py-1" onclick="markDeleteLogo('school_logo', 'delete_school_logo', 'school_logo_img', 'school_logo_placeholder', 'school_logo_del_btn')" title="Supprimer le logo">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="button" id="school_logo_del_btn" class="btn btn-outline-danger btn-sm rounded-3 px-2 py-1 d-none" onclick="markDeleteLogo('school_logo', 'delete_school_logo', 'school_logo_img', 'school_logo_placeholder', 'school_logo_del_btn')" title="Supprimer le logo">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- LOGO TUTELLE (LMD & tous types d'enseignement) -->
                    <div class="col-12">
                        <div class="p-3 border-theme-dynamic rounded-4 bg-soft-light text-center d-flex flex-column justify-content-center align-items-center">
                            <label class="form-label text-primary fw-bold extra-small text-uppercase mb-2 d-block">
                                <i class="bi bi-patch-check me-1"></i> <?= __('tutelage_logo') ?>
                            </label>

                            <?php 
                            $hasTutelageLogo = $logoManager->hasTutelageLogo();
                            $tutelageBase64 = $logoManager->getTutelageLogoBase64();
                            ?>

                            <div class="position-relative mb-2 group-hover">
                                <div id="tutelage_logo_container">
                                    <?php if ($hasTutelageLogo): ?>
                                        <img id="tutelage_logo_img" src="<?= $tutelageBase64 ?>" alt="Logo Tutelle" class="img-fluid rounded-4 shadow-sm border border-theme-light" style="max-height: 80px; min-width: 80px; object-fit: contain;">
                                    <?php else: ?>
                                        <div id="tutelage_logo_placeholder" class="avatar-init bg-soft-info text-info rounded-4 d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px; margin: 0 auto;">
                                            <i class="bi bi-building fs-1 opacity-25"></i>
                                        </div>
                                        <img id="tutelage_logo_img" src="" alt="Logo Tutelle" class="img-fluid rounded-4 shadow-sm border border-theme-light d-none" style="max-height: 80px; min-width: 80px; object-fit: contain;">
                                    <?php endif; ?>
                                </div>

                                <input type="hidden" name="delete_tutelage_logo" id="delete_tutelage_logo" value="0">
                            </div>

                            <div class="d-flex align-items-center gap-2 w-100 mt-1">
                                <input type="file" name="tutelage_logo" id="tutelage_logo_input" class="form-control premium-input-sm border-theme-light shadow-none" accept="image/*" onchange="previewImage(this, 'tutelage_logo_img', 'tutelage_logo_placeholder', 'tutelage_logo_del_btn')">
                                <?php if ($hasTutelageLogo): ?>
                                    <button type="button" id="tutelage_logo_del_btn" class="btn btn-outline-danger btn-sm rounded-3 px-2 py-1" onclick="markDeleteLogo('tutelage_logo', 'delete_tutelage_logo', 'tutelage_logo_img', 'tutelage_logo_placeholder', 'tutelage_logo_del_btn')" title="Supprimer le logo de tutelle">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="button" id="tutelage_logo_del_btn" class="btn btn-outline-danger btn-sm rounded-3 px-2 py-1 d-none" onclick="markDeleteLogo('tutelage_logo', 'delete_tutelage_logo', 'tutelage_logo_img', 'tutelage_logo_placeholder', 'tutelage_logo_del_btn')" title="Supprimer le logo de tutelle">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            function previewImage(input, imgId, placeholderId, delBtnId) {
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.getElementById(imgId);
                        const placeholder = document.getElementById(placeholderId);
                        const delBtn = document.getElementById(delBtnId);
                        
                        img.src = e.target.result;
                        img.classList.remove('d-none');
                        if (placeholder) placeholder.classList.add('d-none');
                        if (delBtn) delBtn.classList.remove('d-none');
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }

            function markDeleteLogo(fieldPrefix, deleteInputId, imgId, placeholderId, delBtnId) {
                document.getElementById(deleteInputId).value = "1";
                const img = document.getElementById(imgId);
                const placeholder = document.getElementById(placeholderId);
                const delBtn = document.getElementById(delBtnId);
                const fileInput = document.getElementById(fieldPrefix + '_input');

                if (fileInput) fileInput.value = "";
                if (img) {
                    img.src = "";
                    img.classList.add('d-none');
                }
                if (placeholder) placeholder.classList.remove('d-none');
                if (delBtn) delBtn.classList.add('d-none');
            }
            </script>

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
                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('delegations') ?></label>
                <div class="row g-3">
                    <div class="col-md-6">
                        <textarea name="school_delegation" class="form-control premium-input" rows="2"
                            placeholder="<?= __('school_delegation_fr_placeholder') ?>"><?= htmlspecialchars((string) ($settings['school_delegation'] ?? '')) ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <textarea name="school_delegation_en" class="form-control premium-input" rows="2"
                            placeholder="<?= __('school_delegation_en_placeholder') ?>"><?= htmlspecialchars((string) ($settings['school_delegation_en'] ?? '')) ?></textarea>
                    </div>
                </div>
                <div class="form-text"><?= __('delegations_help_text') ?></div>
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