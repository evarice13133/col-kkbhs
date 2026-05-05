<?php $title = __('sequences');
ob_start(); ?>

<div class="animate-fade-in container-fluid py-4">

    <!-- BARRE D'ACTIONS COMPLÈTE : Style Floating Island -->
    <div class="d-flex justify-content-center mb-5">
        <div class="filter-island px-3 py-2 shadow-lg animate-slide-down" style="min-width: 40%;">
            <div class="d-flex align-items-center justify-content-center gap-2 w-100">
                <a href="/sequences/create" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm scale-on-hover">
                    <i class="bi bi-plus-circle me-2"></i> <?= __('add_sequence') ?>
                </a>
            </div>
        </div>
    </div>

    <!-- LISTE DES SEQUENCES -->
    <div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in">
        <div class="table-responsive">
            <table class="table-modern"> 
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 100px;"><?= __('code') ?></th>
                        <th><?= __('label') ?></th>
                        <th><?= __('Short Label') ?></th>
                        <th><?= __('trimester') ?></th>
                        <th><?= __('position') ?></th>
                        <th><?= __('status') ?></th>
                        <th class="text-end pe-4"><?= __('action') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sequences)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-calendar-x fs-1 opacity-25 mb-3 d-block"></i>
                                <span class="opacity-50"><?= __('no_data') ?></span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sequences as $s): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-1 rounded-3">
                                        <?= htmlspecialchars((string) $s['code']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold text-main-theme"><?= htmlspecialchars((string) $s['label']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary fw-bold px-3 py-1 rounded-3">
                                        <?= htmlspecialchars((string) ($s['short_label'] ?? '')) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="p-1 px-2 rounded-2 bg-info bg-opacity-10 text-info small fw-bold">
                                            T<?= (int) $s['trimestre'] ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted small">Pos. <?= (int) $s['position'] ?></span>
                                </td>
                                <td>
                                    <?php if ((int) $s['is_active'] === 1): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-bold">
                                            <i class="bi bi-check-circle-fill me-1"></i><?= __('active') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1 fw-bold">
                                            <i class="bi bi-dash-circle-fill me-1"></i><?= __('inactive') ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="/sequences/toggle?id=<?= $s['id'] ?>"
                                            class="btn btn-sm btn-action-modern <?= (int) $s['is_active'] === 1 ? 'text-warning' : 'text-success' ?>"
                                            title="<?= (int) $s['is_active'] === 1 ? __('deactivate') : __('activate') ?>">
                                            <i
                                                class="bi <?= (int) $s['is_active'] === 1 ? 'bi-pause-circle' : 'bi-play-circle' ?> fs-5"></i>
                                        </a>
                                        <a href="/sequences/edit?id=<?= $s['id'] ?>"
                                            class="btn btn-sm btn-action-modern text-primary" title="<?= __('edit') ?>">
                                            <i class="bi bi-pencil-square fs-5"></i>
                                        </a>
                                        <a href="/sequences/delete?id=<?= $s['id'] ?>"
                                            class="btn btn-sm btn-action-modern text-danger"
                                            onclick="return confirm('<?= __('confirm_delete_sequence') ?>')"
                                            title="<?= __('delete') ?>">
                                            <i class="bi bi-trash fs-5"></i>
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