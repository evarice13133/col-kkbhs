<?php $title = __('academic_sections'); ob_start(); ?>

    <!-- BARRE D'ACTIONS COMPLÈTE : Style Floating Island -->
    <div class="d-flex justify-content-center mb-5">
        <div class="filter-island px-3 py-2 shadow-lg animate-slide-down" style="min-width: 40%;">
            <div class="d-flex align-items-center justify-content-center gap-2 w-100">
                <a href="/sections/create" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm scale-on-hover">
                    <i class="bi bi-plus-lg me-2"></i><?= __('add_section') ?>
                </a>
            </div>
        </div>
    </div>

<div class="row">
    <div class="col-12">
        <div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in">
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 80px;">N°</th>
                            <th><?= __('section_name') ?></th>
                            <th><?= __('status') ?></th>
                            <th class="text-end pe-4"><?= __('action') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sections)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="p-4 rounded-circle bg-light d-inline-block mb-3">
                                        <i class="bi bi-grid-3x3-gap fs-1 opacity-25"></i>
                                    </div>
                                    <h6 class="text-muted-theme"><?= __('no_data') ?></h6>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $num = 0; foreach ($sections as $section): $num++; ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1 rounded-3">
                                            #<?= str_pad($num, 2, '0', STR_PAD_LEFT) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                                <i class="bi bi-grid-3x3-gap"></i>
                                            </div>
                                            <span class="fw-bold text-main-theme fs-6"><?= htmlspecialchars((string) $section['nom']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill small fw-bold">
                                            <i class="bi bi-check-circle me-1"></i><?= __('active') ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="/sections/edit?id=<?= $section['id'] ?>" class="btn btn-sm btn-light rounded-pill px-3 fw-bold transition-base border" title="<?= __('edit') ?>">
                                                <i class="bi bi-pencil me-1"></i> <?= __('edit') ?>
                                            </a>
                                            <a href="/sections/delete?id=<?= $section['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold transition-base" 
                                               onclick="return confirm('<?= __('confirm_delete_text') ?>')" title="<?= __('delete') ?>">
                                                <i class="bi bi-trash me-1"></i> <?= __('delete') ?>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
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
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
