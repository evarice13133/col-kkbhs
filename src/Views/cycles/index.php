<?php $title = __('academic_cycles'); ob_start(); ?>

<div class="animate-fade-in container-fluid py-4">
    
    <!-- BARRE D'ACTIONS COMPLÈTE : Style Floating Island -->
    <div class="d-flex justify-content-center mb-5">
        <div class="filter-island px-3 py-2 shadow-lg animate-slide-down" style="min-width: 40%;">
            <div class="d-flex align-items-center justify-content-center gap-2 w-100">
                <a href="/cycles/create" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm scale-on-hover">
                    <i class="bi bi-plus-circle me-2"></i> <?= __('add_cycle') ?>
                </a>
            </div>
        </div>
    </div>

    <!-- LISTE DES CYCLES (Grille harmonisée) -->
    <div class="row g-2 g-md-4">
        <?php foreach ($cycles as $num => $cycle): ?>
            <div class="col-6 col-sm-6 col-xl-3">
                <div class="subject-card-compact border-theme-dynamic h-100 position-relative">
                    <div class="subject-card-glow"></div>
                    <div class="card-body p-2 h-100 position-relative" style="z-index: 1;">
                        <div class="d-flex flex-column h-100 justify-content-between gap-1">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-init bg-success bg-opacity-10 text-success fw-black rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                                style="width: 30px; height: 30px; font-size: 0.9rem;">
                                                <i class="bi bi-stack"></i>
                                            </div>
                                        </div>
                                        <div class="overflow-hidden">
                                            <h6 class="fw-bold lh-1 text-main-theme m-0 text-truncate"
                                                style="font-size: 0.85rem;"
                                                title="<?= htmlspecialchars((string) $cycle['nom']) ?>">
                                                <?= htmlspecialchars((string) $cycle['nom']) ?>
                                            </h6>
                                            <div class="extra-small text-muted-theme opacity-75 text-truncate"
                                                style="font-size: 0.7rem;">
                                                #<?= str_pad($num + 1, 2, '0', STR_PAD_LEFT) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-1 align-items-center">
                                        <?php if (in_array(App\Core\Session::get('user_role'), ['superadmin', 'admin'])): ?>
                                            <a href="/cycles/delete?id=<?= $cycle['id'] ?>&csrf_token=<?= \App\Core\Session::generateCsrfToken() ?>"
                                                class="btn-icon-action text-danger position-relative btn-confirm-delete"
                                                style="z-index: 10; width: 28px; height: 28px; font-size: 0.8rem;"
                                                data-confirm="<?= __('confirm_delete_text') ?>" title="<?= __('delete') ?>">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Stretched Link for Edit -->
                                <?php if (in_array(App\Core\Session::get('user_role'), ['superadmin', 'admin'])): ?>
                                    <a href="/cycles/edit?id=<?= $cycle['id'] ?>" class="stretched-link"></a>
                                <?php endif; ?>

                                <!-- Info Badge Row -->
                                <div class="mt-1 d-flex flex-wrap gap-1 align-items-center">
                                    <div class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 extra-small px-2 py-1 rounded-pill fw-bold">
                                        <i class="bi bi-check-circle-fill me-1"></i><?= __('active') ?>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto d-flex justify-content-end align-items-center position-relative"
                                style="z-index: 1;">
                                <div class="card-arrow-container">
                                    <i class="bi bi-arrow-right-short text-success opacity-50 fs-5"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($cycles)): ?>
            <div class="col-12">
                <div class="subject-card-compact p-5 text-center border-dashed">
                    <i class="bi bi-stack fs-1 opacity-25 mb-3 d-block"></i>
                    <h5 class="text-muted"><?= __('no_data') ?></h5>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Floating Island Filters */
    .filter-island {
        background: rgba(var(--bg-card-rgb), 0.7);
        backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(var(--primary-rgb), 0.15);
        border-radius: 100px;
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .filter-island {
        background: rgba(30, 30, 45, 0.6);
        border-color: rgba(255, 255, 255, 0.08);
    }

    .scale-on-hover:hover { transform: scale(1.05); }

    /* Animations */
    .animate-slide-down {
        animation: slideDown 0.6s cubic-bezier(0.23, 1, 0.32, 1);
    }

    @keyframes slideDown {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .subject-card-compact {
        background: var(--bg-card);
        border-radius: 24px;
        border: 1px solid var(--border-theme) !important;
        display: block;
        text-decoration: none !important;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        position: relative;
        overflow: hidden;
    }

    [data-theme="dark"] .subject-card-compact {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(10px);
        border-color: rgba(255, 255, 255, 0.08) !important;
    }

    .subject-card-glow {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at top right, rgba(var(--primary-rgb), 0.15), transparent 70%);
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    .subject-card-compact:hover {
        transform: translateY(-8px) scale(1.02);
        border-color: var(--primary-color) !important;
        box-shadow: 0 20px 40px rgba(var(--primary-rgb), 0.12);
    }

    .subject-card-compact:hover .subject-card-glow {
        opacity: 1;
    }

    .btn-icon-action {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: rgba(var(--primary-rgb), 0.05);
        transition: all 0.2s ease;
        text-decoration: none !important;
        font-size: 0.9rem;
    }

    .btn-icon-action.text-danger {
        background: rgba(220, 53, 69, 0.05);
    }

    .btn-icon-action:hover {
        transform: scale(1.1);
        background: var(--primary-color);
        color: white !important;
    }

    .btn-icon-action.text-danger:hover {
        background: #dc3545;
        color: white !important;
    }

    .avatar-init {
        font-family: 'Inter', sans-serif;
        letter-spacing: -1px;
    }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>

