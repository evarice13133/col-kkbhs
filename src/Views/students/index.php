<?php $title = __('student_list');
ob_start(); ?>

<div class="animate-fade-in container-fluid py-3 px-md-4">



    <!-- Boutons d'Action Principaux (Au-dessus du filtre) -->
    <?php if (in_array(App\Core\Session::get('user_role'), ['superadmin', 'admin'])): ?>
    <div class="d-flex justify-content-center mb-3">
        <div class="d-flex gap-2">
            <a href="/students/create" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm text-nowrap">
                <i class="bi bi-person-plus me-1"></i> <?= __('add_student') ?>
            </a>
            <a href="/students/import" class="btn btn-outline-success rounded-pill px-4 fw-bold shadow-sm text-nowrap">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> <?= __('import_excel') ?>
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- BARRE D'ACTIONS COMPLÈTE : Style Floating Island -->
    <div class="d-flex justify-content-center mb-5">
        <div class="filter-island px-3 py-2 shadow-lg animate-slide-down" style="min-width: 95%;">
            <form method="GET" class="d-flex align-items-center gap-2 flex-wrap flex-md-nowrap filter-form w-100">

                <!-- Barre de Recherche (Extensible) -->
                <div class="flex-grow-1">
                    <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2">
                        <span class="input-group-text border-0 bg-transparent text-primary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-0 bg-transparent shadow-none py-2 text-main"
                            value="<?= htmlspecialchars((string) $filters['q']) ?>"
                            placeholder="<?= __('search_student_placeholder') ?>..." style="min-width: 150px;">
                    </div>
                </div>

                <!-- Filtre Démissionnaires : Style Badge Interactif -->
                <div class="ms-2">
                    <input type="checkbox" name="withdrawn" value="1" id="filterWithdrawn" class="btn-check" 
                           <?= ($filters['withdrawn'] ?? 0) ? 'checked' : '' ?> onchange="this.form.submit()">
                    <label class="btn <?= ($filters['withdrawn'] ?? 0) ? 'btn-danger shadow-sm active-status-pulse' : 'btn-outline-theme border-opacity-25' ?> rounded-pill px-3 py-2 fw-bold d-flex align-items-center gap-2" 
                           for="filterWithdrawn" style="font-size: 0.7rem; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border-width: 1.5px;">
                        <i class="bi bi-person-x<?= ($filters['withdrawn'] ?? 0) ? '-fill' : '' ?> shadow-icon"></i>
                        <span class="text-nowrap"><?= __('show_withdrawn') ?></span>
                    </label>
                </div>

                <!-- Filtres et Utilitaires -->
                <div class="d-flex gap-2 align-items-center ps-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"><?= __('filter') ?></button>
                    <a href="/students" class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" title="<?= __('reset') ?>">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                    <div class="ms-2">
                        <a href="/students/export?<?= http_build_query($filters) ?>"
                            class="btn-export-minimal shadow-sm" title="<?= __('export_list') ?>">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- LISTE DES ÉLÈVES (Tableau structuré multi-colonnes) -->
 <div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in">
    <div class="table-responsive">

        <table class="table-modern">
            <thead>
                <tr>
                    <th><?= __('student') ?></th>
                    <th><?= __('class') ?></th>
                    <th><?= __('section') ?></th>
                    <th><?= __('department') ?></th>
                    <?php if (in_array(App\Core\Session::get('user_role'), ['superadmin', 'admin'])): ?>
                    <th class="text-end"><?= __('actions') ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $s): ?>
                <tr class="student-row">
                    <td
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                style="width: 36px; height: 36px; font-size: 1rem; border: 1px solid rgba(var(--primary-rgb), 0.2);">
                                <?= strtoupper(substr((string) $s['nom'], 0, 1)) ?>
                            </div>
                            <div>
                                <div class="fw-bold text-main-theme name-gradient"
                                    style="font-size: 0.9rem; name-gradient"
                                    style="font-size: 0.9rem;">
                                    <?= htmlspecialchars((string) $s['nom']) ?>
                                </div>
                                <div class="text-muted-t7eme opacity-75"
                                    style="font-size: 0.75rem;"><?= htmlspecialchars((string) $s['prenom']) ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-primary text-white px-2 py-1 rounded-pill fw-bold shadow-sm"
                            style="font-size: 0.7rem;">
                            <i class="bi bi-door-open-fill me-1"></i><?= htmlspecialchars((string) ($s['classe_nom'] ?: __('no_class'))) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1 rounded-pill fw-medium"
                            style="font-size: 0.7rem;">
                            <i class="bi bi-layers-half me-1"></i><?= htmlspecialchars((string) ($s['section_nom'] ?: '-')) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1 rounded-pill fw-medium"
                            style="font-size: 0.7rem;">
                            <i class="bi bi-building me-1"></i><?= htmlspecialchars((string) ($s['department_nom'] ?: '-')) ?>
                        </span>
                    </td>
                    <?php if (in_array(App\Core\Session::get('user_role'), ['superadmin', 'admin'])): ?>
                    <td class="text-end pe-4">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="/students/edit?id=<?= $s['id'] ?>"
                                class="btn btn-sm btn-action-modern text-primary" title="<?= __('edit') ?>">
                                <i class="bi bi-pencil-square fs-5"></i>
                            </a>
                            <?php if ($filters['withdrawn'] ?? 0): ?>
                                <a href="/students/restore?id=<?= $s['id'] ?>&csrf_token=<?= \App\Core\Session::generateCsrfToken() ?>"
                                    class="btn btn-sm btn-action-modern text-success btn-confirm-restore"
                                    data-confirm="<?= __('restore_student_confirm') ?>" title="<?= __('restore') ?>">
                                    <i class="bi bi-arrow-counterclockwise fs-5"></i>
                                </a>
                            <?php else: ?>
                                <a href="/students/withdraw?id=<?= $s['id'] ?>&csrf_token=<?= \App\Core\Session::generateCsrfToken() ?>"
                                    class="btn btn-sm btn-action-modern text-warning btn-confirm-withdraw"
                                    data-confirm="<?= __('withdraw_student_confirm') ?>" title="<?= __('withdraw') ?>">
                                    <i class="bi bi-person-x fs-5"></i>
                                </a>
                            <?php endif; ?>
                            <a href="/students/delete?id=<?= $s['id'] ?>&csrf_token=<?= \App\Core\Session::generateCsrfToken() ?>"
                                class="btn btn-sm btn-action-modern text-danger btn-confirm-delete"
                                data-confirm="<?= __('delete_student_confirm') ?>" title="<?= __('delete') ?>">
                                <i class="bi bi-trash fs-5"></i>
                            </a>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
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

    /* Thème sombre pour le tableau des élèves */
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
</style>

<?php $content = ob_get_clean(); ?>

<?php include __DIR__ . '/../templates/layout.php'; ?>