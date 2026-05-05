<?php $title = __('teachers_list');
ob_start(); ?>

<div class="animate-fade-in container-fluid py-3 px-md-4">



    <?php if ($msg = App\Core\Session::get('success_msg')): ?>
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars((string) $msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php App\Core\Session::remove('success_msg'); ?>
    <?php endif; ?>
    <?php if ($err = App\Core\Session::get('error_msg')): ?>
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars((string) $err) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php App\Core\Session::remove('error_msg'); ?>
    <?php endif; ?>

    <!-- BANNIÈRE MODE AFFECTATION -->
    <?php if ($assignContext): ?>
        <div class="alert alert-primary border-0 shadow-lg rounded-4 p-4 mb-4 animate-pulse">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-circle bg-white text-primary rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 50px; height: 50px;">
                        <i class="bi bi-person-plus-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1 text-white"><?= __('choosing_teacher_for') ?> : <span
                                class="text-warning"><?= h($assignContext['subject_name']) ?></span></h5>
                        <p class="mb-0 text-white opacity-75 small">
                            <i class="bi bi-door-open me-1"></i> <?= __('class') ?> :
                            <strong><?= h($assignContext['class_name']) ?></strong>
                        </p>
                    </div>
                </div>
                <a href="/teachers" class="btn btn-light rounded-pill px-4 fw-bold">
                    <i class="bi bi-x-circle me-1"></i> <?= __('cancel') ?>
                </a>
            </div>
        </div>

        <style>
            .alert-primary {
                background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            }

            .animate-pulse {
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0% {
                    transform: scale(1);
                }

                50% {
                    transform: scale(1.01);
                }

                100% {
                    transform: scale(1);
                }
            }
        </style>
    <?php endif; ?>

    <!-- BARRE D'ACTIONS COMPLÈTE : Style Floating Island -->
    <div class="d-flex justify-content-center mb-5">
        <div class="filter-island px-3 py-2 shadow-lg animate-slide-down" style="min-width: 85%;">
            <form method="GET" class="d-flex align-items-center gap-2 flex-wrap flex-md-nowrap filter-form w-100">
                
                <!-- Boutons d'Action Principaux -->
                <div class="d-flex gap-2 pe-3 border-end border-opacity-10 border-secondary me-2">
                    <a href="/teachers/create" class="btn btn-primary rounded-pill px-3 fw-bold shadow-sm text-nowrap">
                        <i class="bi bi-person-plus me-1"></i> <?= __('add_teacher') ?>
                    </a>
                    <a href="/teachers/import" class="btn btn-outline-success rounded-pill px-3 fw-bold text-nowrap d-none d-xl-inline-block">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> <?= __('import_excel') ?>
                    </a>
                </div>

                <!-- Barre de Recherche (Extensible) -->
                <div class="flex-grow-1">
                    <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2">
                        <span class="input-group-text border-0 bg-transparent text-primary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-0 bg-transparent shadow-none py-2 text-main"
                            value="<?= htmlspecialchars((string) $filters['q']) ?>"
                            placeholder="<?= __('search_placeholder') ?>..." style="min-width: 200px;">
                    </div>
                </div>

                <!-- Filtres et Utilitaires -->
                <div class="d-flex gap-2 align-items-center ps-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"><?= __('filter') ?></button>
                    <a href="/teachers" class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center reset-btn" style="width: 40px; height: 40px;" title="<?= __('reset') ?>">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                    <div class="ms-2">
                        <a href="/teachers/export?<?= http_build_query($filters) ?>"
                            class="btn-export-minimal shadow-sm" title="<?= __('export_list') ?>">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- LISTE DES ENSEIGNANTS -->
    <div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in">
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th class="ps-4"><?= __('teacher') ?></th>
                        <th><?= __('username') ?></th>
                        <th><?= __('subjects') ?></th>
                        <th><?= __('classes') ?></th>
                        <th class="text-end pe-4"><?= __('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($teachers)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-person-workspace fs-1 opacity-25 mb-3 d-block"></i>
                                <span class="opacity-50"><?= __('no_data') ?></span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($teachers as $t): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                            style="width: 36px; height: 36px; font-size: 1rem; border: 1px solid rgba(var(--primary-rgb), 0.2);">
                                            <?= strtoupper(substr((string) $t['nom'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-main-theme">
                                                <?= htmlspecialchars((string) $t['nom']) ?>
                                            </div>
                                            <div class="text-muted-theme opacity-75"
                                                style="font-size: 0.85rem;"><?= htmlspecialchars((string) $t['prenom']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-1 rounded-3">
                                        <?= htmlspecialchars((string) $t['username']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info fw-bold px-3 py-1 rounded-3">
                                        <?= (int) $t['subjects_count'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted small"><?= htmlspecialchars((string) ($t['classes_list'] ?: '-')) ?></span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-1">
                                        <?php if (!$assignContext): ?>
                                            <a href="/teachers/edit?id=<?= $t['id'] ?>"
                                                class="btn btn-sm btn-action-modern text-primary" title="<?= __('edit') ?>">
                                                <i class="bi bi-pencil-square fs-5"></i>
                                            </a>
                                            <a href="/teachers/assign?id=<?= $t['id'] ?>"
                                                class="btn btn-sm btn-action-modern text-info" title="<?= __('assignments') ?>">
                                                <i class="bi bi-journal-plus fs-5"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="/teachers/direct_assign?teacher_id=<?= $t['id'] ?>&subject_id=<?= $assignContext['subject_id'] ?>&class_id=<?= $assignContext['class_id'] ?>"
                                                class="btn btn-sm btn-action-modern text-success" title="<?= __('choose') ?>">
                                                <i class="bi bi-check2-circle fs-5"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="/teachers/delete?id=<?= $t['id'] ?>"
                                            class="btn btn-sm btn-action-modern text-danger btn-confirm-delete"
                                            data-confirm="<?= __('delete_teacher_confirm') ?>"
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

    <!-- PAGINATION -->
    <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-between align-items-center mt-5 mb-4 flex-wrap gap-3">
            <div class="text-muted small">
                <?= __('showing_count', [
                    'start' => $offset + 1,
                    'end' => min($offset + $limit, $totalCount),
                    'total' => $totalCount
                ]) ?>
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-modern mb-0">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $page - 1])) ?>" aria-label="Previous">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    if ($start > 1): ?>
                        <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => 1])) ?>">1</a></li>
                        <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($end < $totalPages): ?>
                        <?php if ($end < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                        <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $totalPages])) ?>"><?= $totalPages ?></a></li>
                    <?php endif; ?>

                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $page + 1])) ?>" aria-label="Next">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Floating Island Filters */
    .filter-island {
        background: rgba(var(--bg-card-rgb), 0.7);
        backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(var(--primary-rgb), 0.15);
        border-radius: 100px;
        min-width: 60%;
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .filter-island {
        background: rgba(30, 30, 45, 0.6);
        border-color: rgba(255, 255, 255, 0.08);
    }

    .filter-island:focus-within {
        border-color: var(--primary-color);
        box-shadow: 0 15px 35px -10px rgba(var(--primary-rgb), 0.25);
        transform: translateY(-2px);
    }

    .btn-export-minimal {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: var(--bg-card);
        color: #f1c40f;
        border: 1px solid rgba(241, 196, 15, 0.2);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        text-decoration: none !important;
    }

    .btn-export-minimal:hover {
        background: #f1c40f;
        color: white !important;
        transform: scale(1.1) rotate(8deg);
        box-shadow: 0 8px 20px rgba(241, 196, 15, 0.3);
    }

    /* Animations */
    .animate-slide-down {
        animation: slideDown 0.6s cubic-bezier(0.23, 1, 0.32, 1);
    }

    @keyframes slideDown {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    /* Pagination Modern Style */
    .pagination-modern {
        gap: 8px;
    }

    .pagination-modern .page-item .page-link {
        border: none;
        border-radius: 12px;
        color: var(--text-main);
        background: var(--bg-card);
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        transition: all 0.2s;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
    }

    .pagination-modern .page-item.active .page-link {
        background: var(--primary-color);
        color: white;
        box-shadow: 0 10px 15px -3px rgba(var(--primary-rgb), 0.3);
    }

    .pagination-modern .page-item .page-link:hover:not(.active) {
        background: color-mix(in srgb, var(--primary-color) 10%, transparent);
        color: var(--primary-color);
        transform: translateY(-2px);
    }

    .subject-card-compact {
        background: var(--bg-card);
        border-radius: 28px;
        border: 1px solid rgba(var(--primary-rgb), 0.08) !important;
        display: block;
        text-decoration: none !important;
        transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        position: relative;
        overflow: hidden;
    }

    [data-theme="dark"] .subject-card-compact {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(15px);
        border-color: rgba(255, 255, 255, 0.06) !important;
    }

    .name-gradient {
        background: linear-gradient(135deg, var(--text-main), var(--primary-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .subject-card-glow {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at top right, rgba(var(--primary-rgb), 0.2), transparent 70%);
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    .subject-card-compact:hover {
        transform: translateY(-12px) scale(1.03);
        border-color: var(--primary-color) !important;
        box-shadow: 0 30px 60px -12px rgba(var(--primary-rgb), 0.25);
    }

    .subject-card-compact:active {
        transform: scale(0.96);
    }

    .subject-card-compact:hover .subject-card-glow {
        opacity: 1;
    }

    @media (max-width: 767.98px) {
        .filter-island {
            border-radius: 24px;
            min-width: 100%;
            padding: 1rem !important;
        }
    }

    /* Thème sombre pour le tableau des enseignants */
    [data-theme="dark"] .modern-card {
        background: rgba(30, 30, 45, 0.6);
        border-color: rgba(255, 255, 255, 0.08);
    }

    [data-theme="dark"] .table-modern thead th {
        background: rgba(255, 255, 255, 0.05);
        color: #ffffff;
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }

    [data-theme="dark"] .table-modern tbody tr {
        border-bottom-color: rgba(255, 255, 255, 0.05);
    }

    [data-theme="dark"] .table-modern tbody tr:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    [data-theme="dark"] .table-modern tbody td {
        color: #e0e0e0;
    }

    [data-theme="dark"] .table-modern tbody td .fw-bold {
        color: #ffffff;
    }

    [data-theme="dark"] .table-modern tbody td .text-muted-theme {
        color: #a0a0a0;
    }

    [data-theme="dark"] .table-modern tbody td .text-muted {
        color: #a0a0a0;
    }
</style>

<?php $content = ob_get_clean(); ?>

<?php include __DIR__ . '/../templates/layout.php'; ?>