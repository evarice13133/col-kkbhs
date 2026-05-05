<?php $title = __('classes');
ob_start(); ?>

<div class="animate-fade-in container-fluid py-3 px-md-4">

    <!-- EN-TÊTE DE PAGE : Titre + Boutons d'Action (Premium Mobile-First) -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3 bg-white p-3 p-md-4 rounded-4 shadow-sm border-0" style="border-left: 4px solid var(--bs-primary) !important;">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px;">
                <i class="bi bi-door-open-fill fs-5"></i>
            </div>
            <div>
                <h1 class="fw-bold fs-5 text-main-theme mb-1 lh-1">
                    <?= __('classes') ?>
                </h1>
                <p class="text-muted mb-0 fw-medium" style="font-size: 0.85rem;">
                    <?= __('lang') === 'en' ? 'Manage your classrooms' : 'Gérez vos salles et filières' ?>
                </p>
            </div>
        </div>
        
        <div class="d-flex flex-row w-100 w-md-auto gap-2 mt-1 mt-md-0">
            <a href="/classes/import" class="btn btn-light rounded-pill px-3 fw-semibold flex-grow-1 flex-md-grow-0 d-flex justify-content-center align-items-center gap-2 border" style="font-size: 0.9rem; transition: all 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                <i class="bi bi-file-earmark-spreadsheet text-success fs-6"></i> 
                <span><?= __('import') ?></span>
            </a>
            <a href="/classes/create" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm flex-grow-1 flex-md-grow-0 d-flex justify-content-center align-items-center gap-2" style="font-size: 0.9rem; transition: all 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                <i class="bi bi-plus-lg"></i> 
                <span><?= __('add_class') ?></span>
            </a>
        </div>
    </div>

    <!-- BARRE DE FILTRES : Style Floating Island -->
    <div class="d-flex justify-content-center mb-5">
        <div class="filter-island px-3 py-3 shadow-lg animate-slide-down" style="min-width: 95%;">
            <form method="GET" class="filter-form w-100">

                <!-- Rangée unique : tous les champs s'adaptent en colonne sur mobile -->
                <div class="d-flex flex-wrap gap-2 align-items-center">

                    <!-- Recherche -->
                    <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2 flex-grow-1" style="min-width: 150px;">
                        <span class="input-group-text border-0 bg-transparent text-primary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-0 bg-transparent shadow-none py-2 text-main"
                            value="<?= htmlspecialchars((string) $filters['q']) ?>"
                            placeholder="<?= __('class_name') ?>...">
                    </div>

                    <!-- Cycle -->
                    <select name="cycle_id" class="form-select border-0 bg-white bg-opacity-10 shadow-none py-2 text-main rounded-pill px-3 flex-grow-1" style="min-width: 130px; max-width: 180px;">
                        <option value=""><?= __('all_cycles') ?></option>
                        <?php foreach ($cycles as $cycle): ?>
                            <option value="<?= $cycle['id'] ?>" <?= (int) $filters['cycle_id'] === (int) $cycle['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $cycle['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Section -->
                    <select name="section_id" class="form-select border-0 bg-white bg-opacity-10 shadow-none py-2 text-main rounded-pill px-3 flex-grow-1" style="min-width: 130px; max-width: 180px;">
                        <option value=""><?= __('all_sections') ?></option>
                        <?php foreach ($sections as $section): ?>
                            <option value="<?= $section['id'] ?>" <?= (int) $filters['section_id'] === (int) $section['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $section['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Actions Filtre -->
                    <div class="d-flex gap-2 align-items-center ms-auto">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm text-nowrap"><?= __('filter') ?></button>
                        <a href="/classes" class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center reset-btn" style="width: 40px; height: 40px;" title="<?= __('reset') ?>">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- LISTE DES CLASSES (Grille compacte harmonisée avec le module élèves) -->
    <div class="row g-2 g-md-4">
        <?php foreach ($classes as $c): ?>
            <div class="col-6 col-sm-6 col-xl-3">
                <div class="subject-card-compact border-theme-dynamic h-100 position-relative">
                    <div class="subject-card-glow"></div>
                    <div class="card-body p-2 h-100 position-relative" style="z-index: 1;">
                        <div class="d-flex flex-column h-100 justify-content-between gap-1">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                                style="width: 32px; height: 32px; font-size: 0.9rem; border: 1px solid rgba(var(--primary-rgb), 0.2);">
                                                <i class="bi bi-door-open-fill"></i>
                                            </div>
                                        </div>
                                        <div class="overflow-hidden">
                                            <h6 class="fw-bold lh-1 text-main-theme m-0 text-truncate name-gradient"
                                                style="font-size: 0.85rem;"
                                                title="<?= htmlspecialchars((string) $c['nom']) ?>">
                                                <?= htmlspecialchars((string) $c['nom']) ?>
                                            </h6>
                                            <div class="extra-small text-muted-theme opacity-75 text-truncate"
                                                style="font-size: 0.65rem;">
                                                <?= $c['student_count'] ?? 0 ?> <?= __('students_short') ?>
                                            </div>
                                            <?php if (!empty($c['main_teacher_nom'])): ?>
                                                <div class="extra-small text-primary fw-bold text-truncate"
                                                    style="font-size: 0.65rem;" title="<?= __('main_teacher') ?>: <?= htmlspecialchars($c['main_teacher_nom'] . ' ' . $c['main_teacher_prenom']) ?>">
                                                    <i class="bi bi-star-fill me-1"></i><?= htmlspecialchars($c['main_teacher_nom']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-1 align-items-center">
                                        <?php if (in_array(App\Core\Session::get('user_role'), ['superadmin', 'admin'])): ?>
                                            <a href="/classes/manage-team?id=<?= $c['id'] ?>"
                                                class="btn-icon-action text-primary position-relative"
                                                style="z-index: 10; width: 28px; height: 28px; font-size: 0.8rem;"
                                                title="<?= __('manage_teaching_team') ?>">
                                                <i class="bi bi-people-fill"></i>
                                            </a>
                                            <a href="/classes/delete?id=<?= $c['id'] ?>&csrf_token=<?= \App\Core\Session::generateCsrfToken() ?>"
                                                class="btn-icon-action text-danger position-relative btn-confirm-delete"
                                                style="z-index: 10; width: 28px; height: 28px; font-size: 0.8rem;"
                                                title="<?= __('delete') ?>">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Stretched Link for Edit -->
                                <?php if (in_array(App\Core\Session::get('user_role'), ['superadmin', 'admin'])): ?>
                                    <a href="/classes/edit?id=<?= $c['id'] ?>" class="stretched-link"></a>
                                <?php endif; ?>

                                <!-- Info Badge Row -->
                                <div class="mt-1 d-flex flex-wrap gap-1 align-items-center">
                                    <?php if ($c['cycle_nom']): ?>
                                        <div class="badge bg-primary text-white px-2 py-1 rounded-pill fw-bold shadow-sm"
                                            style="font-size: 0.65rem;">
                                            <i class="bi bi-layers-fill me-1"></i><?= htmlspecialchars((string) $c['cycle_nom']) ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($c['section_nom']): ?>
                                        <div class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-10 extra-small px-2 py-1 rounded-pill fw-medium">
                                            <i class="bi bi-diagram-3-fill me-1"></i><?= htmlspecialchars((string) $c['section_nom']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mt-auto d-flex justify-content-end align-items-center position-relative"
                                style="z-index: 1;">
                                <div class="card-arrow-container">
                                    <i class="bi bi-arrow-right-short text-primary opacity-50 fs-5"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($classes)): ?>
            <div class="col-12">
                <div class="subject-card-compact p-5 text-center border-dashed">
                    <i class="bi bi-door-closed fs-1 opacity-25 mb-3 d-block"></i>
                    <h5 class="text-muted"><?= __('no_data') ?></h5>
                </div>
            </div>
        <?php endif; ?>
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
        min-width: 80%;
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
            border-radius: 20px;
            min-width: 100%;
            padding: 1rem !important;
        }
        .filter-form .d-flex.flex-wrap {
            flex-direction: column;
        }
        .filter-form .input-group,
        .filter-form .form-select {
            max-width: 100% !important;
            width: 100% !important;
        }
        .filter-form .ms-auto {
            margin-left: 0 !important;
            justify-content: stretch;
            width: 100%;
        }
        .filter-form .ms-auto .btn {
            flex: 1;
        }
        .subject-card-compact {
            border-radius: 22px;
            padding: 0.15rem !important;
            min-height: 130px;
        }
        .subject-card-compact .avatar-init {
            width: 32px !important;
            height: 32px !important;
            font-size: 0.9rem !important;
        }
    }

    .btn-icon-action {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: rgba(var(--primary-rgb), 0.05);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        text-decoration: none !important;
    }

    .btn-icon-action:hover {
        transform: translateY(-3px) rotate(8deg);
        background: var(--primary-color);
        color: white !important;
        box-shadow: 0 5px 15px rgba(var(--primary-rgb), 0.3);
    }

    .btn-icon-action.text-danger:hover {
        background: #dc3545;
        color: white !important;
        box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
    }

    .avatar-init {
        transition: all 0.4s ease;
    }
    
    .subject-card-compact:hover .avatar-init {
        transform: scale(1.1) rotate(-5deg);
        filter: drop-shadow(0 5px 10px rgba(var(--primary-rgb), 0.3));
    }
    .border-dashed { border-style: dashed !important; border-width: 2px !important; }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>