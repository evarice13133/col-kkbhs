<?php $title = __('departments'); ob_start(); ?>

<div class="animate-fade-in container-fluid py-4">
    
    <!-- BARRE D'ACTIONS : Style Floating Island -->
    <?php if (App\Core\Session::get('user_role') === 'superadmin'): ?>
    <div class="d-flex justify-content-center mb-5">
        <div class="filter-island px-3 py-2 shadow-lg animate-slide-down" style="min-width: 40%;">
            <div class="d-flex align-items-center justify-content-center gap-2 w-100">
                <a href="/departments/create" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm scale-on-hover">
                    <i class="bi bi-plus-circle me-2"></i> <?= __('add_department') ?>
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- GRILLE DES DÉPARTEMENTS -->
    <div class="row g-4">
        <?php foreach ($departments as $dept): 
            $isSuperAdmin = App\Core\Session::get('user_role') === 'superadmin';
        ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="subject-card-compact border-theme-dynamic h-100 position-relative <?= ($isSuperAdmin && !$dept['status']) ? 'opacity-75' : '' ?>">
                    <div class="subject-card-glow"></div>
                    <div class="card-body p-4 position-relative" style="z-index: 1;">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-black rounded-4 d-flex align-items-center justify-content-center shadow-sm"
                                    style="width: 54px; height: 54px; font-size: 1.2rem;">
                                    <?= substr($dept['nom'], 0, 1) ?>
                                </div>
                                <div>
                                    <h5 class="fw-black m-0 text-main-theme"><?= h($dept['nom']) ?></h5>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <span class="badge bg-soft-primary text-primary extra-small fw-bold px-2 py-1 rounded-pill">
                                            <?= h($dept['code']) ?>
                                        </span>
                                        <?php if (!empty($dept['teaching_type_nom'])): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success extra-small fw-bold px-2 py-1 rounded-pill border border-success border-opacity-10">
                                                <i class="bi bi-diagram-3 me-1"></i> <?= h($dept['teaching_type_nom']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (App\Core\Session::get('user_role') === 'superadmin'): ?>
                            <div class="dropdown">
                                <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical fs-5"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2">
                                    <li>
                                        <a class="dropdown-item dropdown-item-modern" href="/departments/edit?id=<?= $dept['id'] ?>">
                                            <i class="bi bi-pencil text-primary"></i> <?= __('edit') ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item dropdown-item-modern" href="/departments/toggle?id=<?= $dept['id'] ?>">
                                            <i class="bi <?= $dept['status'] ? 'bi-eye-slash text-warning' : 'bi-eye text-success' ?>"></i> 
                                            <?= $dept['status'] ? __('deactivate_department') : __('activate_department') ?>
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item dropdown-item-modern text-danger btn-confirm-delete" 
                                           href="/departments/delete?id=<?= $dept['id'] ?>"
                                           data-confirm="<?= __('confirm_delete_text') ?>">
                                            <i class="bi bi-trash"></i> <?= __('delete') ?>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="mt-4 pt-3 border-top border-theme-light d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($isSuperAdmin): ?>
                                    <?php if ($dept['status']): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 rounded-pill extra-small px-3">
                                            <i class="bi bi-check-circle-fill me-1"></i> <?= __('active') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10 rounded-pill extra-small px-3">
                                            <i class="bi bi-x-circle-fill me-1"></i> <?= __('inactive') ?>
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="text-muted-theme extra-small opacity-75">
                                <i class="bi bi-calendar3 me-1"></i> <?= date('d/m/Y', strtotime($dept['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($departments)): ?>
            <div class="col-12">
                <div class="subject-card-compact p-5 text-center border-dashed">
                    <i class="bi bi-building fs-1 opacity-25 mb-3 d-block"></i>
                    <h5 class="text-muted"><?= __('no_data') ?></h5>
                    <?php if (App\Core\Session::get('user_role') === 'superadmin'): ?>
                        <p class="small text-muted mb-4"><?= __('no_department_help') ?? 'Commencez par créer le premier département de votre établissement.' ?></p>
                        <a href="/departments/create" class="btn btn-primary rounded-pill px-4"><?= __('add_department') ?></a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
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

    .subject-card-compact {
        background: var(--bg-card);
        border-radius: 28px;
        border: 1px solid var(--border-theme) !important;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }

    .subject-card-compact:hover {
        transform: translateY(-8px);
        border-color: var(--primary-color) !important;
        box-shadow: 0 20px 40px rgba(var(--primary-rgb), 0.12);
    }

    .subject-card-glow {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at top right, rgba(var(--primary-rgb), 0.1), transparent 70%);
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    .subject-card-compact:hover .subject-card-glow {
        opacity: 1;
    }

    .dropdown-item-modern {
        border-radius: 10px;
        padding: 8px 12px;
        font-weight: 600;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.2s ease;
    }

    .dropdown-item-modern:hover {
        background-color: rgba(var(--primary-rgb), 0.08);
        color: var(--primary-color);
        transform: translateX(5px);
    }

    .avatar-init {
        font-family: 'Inter', sans-serif;
        letter-spacing: -1px;
    }

    .animate-slide-down {
        animation: slideDown 0.6s cubic-bezier(0.23, 1, 0.32, 1);
    }

    @keyframes slideDown {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
