<?php $title = __('user_documentation');
ob_start(); ?>

<div class="animate-fade-in container-fluid py-4 documentation-page modern-card">

    <!-- HEADER : Titre & Actions (Comme classes/index.php) -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-0 text-main"><?= __('user_documentation') ?></h2>
            <p class="text-muted small mb-0"><?= __('documentation_intro_text') ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="/documentation/download" class="btn btn-outline-primary px-4 fw-bold shadow-sm rounded-3">
                <i class="bi bi-file-earmark-pdf me-1"></i> <?= __('download_pdf') ?>
            </a>
            <button class="btn btn-primary px-4 fw-bold shadow-sm rounded-3"
                onclick="document.getElementById('manual-viewer').scrollIntoView({behavior: 'smooth'})">
                <i class="bi bi-eye me-1"></i> <?= __('read_online') ?>
            </button>
        </div>
    </div>

    <div class="row modern-card g-4 mb-5">
        <!-- Manual Preview Section -->
        <div class="col-xl-8">
            <div id="manual-viewer" class="modern-card border-0 shadow-sm overflow-hidden h-100">
                <div
                    class="modern-card-header bg-transparent p-4 border-bottom d-flex align-items-center justify-content-between bg-main-theme bg-opacity-5">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-2 bg-primary bg-opacity-10 rounded-3 text-primary">
                            <i class="bi bi-book-half"></i>
                        </div>
                        <h5 class="fw-bold m-0 text-main-theme"><?= __('your_manual_title') ?></h5>
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 small fw-bold">
                        <?= $roleLabel ?>
                    </span>
                </div>
                <div class="modern-card-body p-0">
                    <div class="manual-online-preview p-4 p-lg-5" style="max-height: 800px; overflow-y: auto;">
                        <div class="manual-embed-content">
                            <?= $manual_body ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Info & Stats -->
        <div class="col-xl-4">
            <div class="row g-4 h-100 flex-column">
                <div class="col-12">
                    <div class="modern-card border-0 shadow-sm p-4 mb-4 h-auto">
                        <h5 class="fw-bold text-main-theme mb-4 d-flex align-items-center gap-2">
                            <i class="bi bi-stars text-warning"></i> <?= __('whats_inside_title') ?>
                        </h5>
                        <div class="d-flex flex-column gap-3">
                            <?php if ($role === 'enseignant'): ?>
                                <div class="guide-feature-item">
                                    <div class="feature-icon bg-primary bg-opacity-10 text-primary rounded-3"><i
                                            class="bi bi-vector-pen"></i></div>
                                    <div>
                                        <h6 class="fw-bold m-0"><?= __('guide_marks_entry') ?></h6>
                                        <p class="small text-muted-theme m-0"><?= __('guide_marks_entry_desc') ?></p>
                                    </div>
                                </div>
                                <div class="guide-feature-item">
                                    <div class="feature-icon bg-info bg-opacity-10 text-info rounded-3"><i
                                            class="bi bi-person-badge"></i></div>
                                    <div>
                                        <h6 class="fw-bold m-0"><?= __('guide_profile_mgmt') ?></h6>
                                        <p class="small text-muted-theme m-0"><?= __('guide_profile_mgmt_desc') ?></p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="guide-feature-item">
                                    <div class="feature-icon bg-success bg-opacity-10 text-success rounded-3"><i
                                            class="bi bi-people"></i></div>
                                    <div>
                                        <h6 class="fw-bold m-0"><?= __('guide_student_mgmt') ?></h6>
                                        <p class="small text-muted-theme m-0"><?= __('guide_student_mgmt_desc') ?></p>
                                    </div>
                                </div>
                                <div class="guide-feature-item">
                                    <div class="feature-icon bg-info bg-opacity-10 text-info rounded-3"><i
                                            class="bi bi-file-bar-graph"></i></div>
                                    <div>
                                        <h6 class="fw-bold m-0"><?= __('guide_bulletin_gen') ?></h6>
                                        <p class="small text-muted-theme m-0"><?= __('guide_bulletin_gen_desc') ?></p>
                                    </div>
                                </div>
                                <div class="guide-feature-item">
                                    <div class="feature-icon bg-warning bg-opacity-10 text-warning rounded-3"><i
                                            class="bi bi-diagram-3"></i></div>
                                    <div>
                                        <h6 class="fw-bold m-0"><?= __('guide_structure_mgmt') ?></h6>
                                        <p class="small text-muted-theme m-0"><?= __('guide_structure_mgmt_desc') ?></p>
                                    </div>
                                </div>
                                <div class="guide-feature-item">
                                    <div class="feature-icon bg-danger bg-opacity-10 text-danger rounded-3"><i
                                            class="bi bi-shield-check"></i></div>
                                    <div>
                                        <h6 class="fw-bold m-0"><?= __('guide_security_mgmt') ?></h6>
                                        <p class="small text-muted-theme m-0"><?= __('guide_security_mgmt_desc') ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-auto">
                    <div
                        class="modern-card border-0 shadow-sm border-start border-primary border-4 p-4 mb-0 theme-support-card">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="p-3 theme-icon-bg rounded-4 shadow-sm text-primary">
                                <i class="bi bi-chat-dots-fill fs-3"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold m-0 text-main-theme"><?= __('need_more_help') ?></h6>
                                <p class="small text-muted-theme m-0"><?= __('contact_it_support') ?></p>
                            </div>
                        </div>
                        <a href="mailto:evaricekuete2@gmail.com" class="btn btn-primary w-100 rounded-pill fw-bold">
                            <i class="bi bi-envelope-fill me-2"></i><?= __('contact_support_action') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    <?= $manual_css ?>

    /* Theme Harmonization (Classes style) */
    .manual-online-preview {
        background: transparent !important;
        --m-bg: transparent !important;
        --m-section-bg: rgba(var(--primary-rgb), 0.05) !important;
        color: var(--text-main);
    }

    .manual-online-preview::-webkit-scrollbar {
        width: 8px;
    }

    .manual-online-preview::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.1);
        border-radius: 10px;
    }

    [data-theme="dark"] .manual-online-preview::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.1);
    }

    .manual-online-preview .container {
        padding: 0;
        box-shadow: none;
        width: 100%;
        max-width: 100%;
        background: transparent !important;
    }

    /* Support Card Adaptation */
    .theme-support-card {
        background: rgba(var(--primary-rgb), 0.05);
    }

    [data-theme="dark"] .theme-support-card {
        background: rgba(var(--primary-rgb), 0.1);
    }

    .theme-icon-bg {
        background: white;
    }

    [data-theme="dark"] .theme-icon-bg {
        background: rgba(255, 255, 255, 0.05);
    }

    /* Feature Icons style refinement */
    .feature-icon {
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .guide-feature-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px;
        border-radius: 16px;
        transition: all 0.2s ease;
    }

    .guide-feature-item:hover {
        background: rgba(var(--primary-rgb), 0.05);
    }

    /* Dark mode overrides for manual content variables */
    [data-theme="dark"] .manual-online-preview {
        --m-text: #f8fafc;
        --m-text-light: #94a3b8;
        --m-border: #1e293b;
        --m-section-bg: rgba(var(--primary-rgb), 0.15) !important;
        --m-tip-bg: rgba(16, 185, 129, 0.1);
        --m-tip-border: rgba(16, 185, 129, 0.2);
        --m-tip-text: #6ee7b7;
        --m-warn-bg: rgba(245, 158, 11, 0.1);
        --m-warn-border: rgba(245, 158, 11, 0.2);
        --m-warn-text: #fbbf24;
    }

    [data-theme="dark"] .manual-online-preview strong {
        color: var(--primary-color);
    }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>