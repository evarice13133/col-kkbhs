<?php 
$title = __('classes') ?? 'Salles de classe';
ob_start(); 

$canManage = in_array(App\Core\Session::get('user_role'), ['superadmin', 'admin']);
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">

    <!-- EN-TÊTE DE PAGE : Style Glassmorphism Premium avec support Mode Sombre -->
    <div class="dept-header-card mb-4 p-3 p-md-4 rounded-4 shadow-sm position-relative overflow-hidden">
        <div class="dept-header-bg"></div>
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between w-100 gap-3 position-relative" style="z-index: 2;">
            <div class="d-flex align-items-center gap-3">
                <div class="dept-icon-wrapper rounded-4 d-flex align-items-center justify-content-center flex-shrink-0">
                    <i class="bi bi-door-open-fill fs-4 text-primary"></i>
                </div>
                <div>
                    <h1 class="fw-black fs-4 text-main-theme mb-1 lh-1">
                        <?= __('classes') ?? 'Salles de Classe' ?>
                    </h1>
                    <p class="text-muted-theme mb-0 fw-medium opacity-75" style="font-size: 0.88rem;">
                        <?= __('lang') === 'en' ? 'Manage classrooms, pedagogical teams and academic sections' : 'Gérez vos salles de classe, filières et équipes pédagogiques' ?>
                    </p>
                </div>
            </div>
            
            <div class="d-flex flex-row w-100 w-md-auto justify-content-end ms-md-auto gap-2 mt-2 mt-md-0">
                <button type="button" class="btn btn-light-theme rounded-pill px-3 py-2 fw-semibold d-flex justify-content-center align-items-center gap-2 scale-on-hover" data-bs-toggle="modal" data-bs-target="#importClassesModal">
                    <i class="bi bi-file-earmark-spreadsheet text-success fs-6"></i> 
                    <span><?= __('import') ?? 'Importer' ?></span>
                </button>
                <?php if ($canManage): ?>
                <a href="/classes/create" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm flex-grow-1 flex-md-grow-0 d-flex justify-content-center align-items-center gap-2 text-nowrap scale-on-hover">
                    <i class="bi bi-plus-lg"></i> 
                    <span><?= __('add_class') ?? 'Ajouter une classe' ?></span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- BARRE DE FILTRES ET RECHERCHE INSTANTANÉE -->
    <div class="filter-island-container mb-4">
        <div class="filter-island p-3 rounded-4 shadow-sm">
            <form method="GET" action="/classes" id="class-filter-form" class="filter-form w-100 m-0">
                <div class="d-flex flex-column gap-3">

                    <div class="d-flex flex-column flex-md-row gap-2 align-items-md-center flex-wrap">
                        <!-- Recherche instantanée -->
                        <div class="dept-search-pill flex-grow-1 position-relative" style="min-width: 220px;">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" name="q" id="search-input" class="form-control dept-filter-input ps-5"
                                value="<?= htmlspecialchars((string) ($filters['q'] ?? '')) ?>"
                                placeholder="<?= __('search') ?? 'Rechercher' ?> (<?= __('class_name') ?? 'Nom de classe' ?>)...">
                        </div>

                        <!-- Type Enseignement -->
                        <div class="dept-select-wrapper flex-grow-1" style="min-width: 160px; max-width: 200px;">
                            <select name="teaching_type_id" id="filter_teaching_type" class="form-select dept-filter-select">
                                <option value=""><?= __('all_teaching_types') ?? 'Tous les Types' ?></option>
                                <?php foreach ($teachingTypes as $tt): ?>
                                    <option value="<?= $tt['id'] ?>" <?= (int) ($filters['teaching_type_id'] ?? 0) === (int) $tt['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $tt['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Cycle -->
                        <div class="dept-select-wrapper flex-grow-1" style="min-width: 150px; max-width: 180px;">
                            <select name="cycle_id" id="filter_cycle" class="form-select dept-filter-select">
                                <option value=""><?= __('all_cycles') ?? 'Tous les cycles' ?></option>
                                <?php foreach ($cycles as $cycle): ?>
                                    <option value="<?= $cycle['id'] ?>" data-teaching-type="<?= $cycle['teaching_type_id'] ?? '' ?>" <?= (int) ($filters['cycle_id'] ?? 0) === (int) $cycle['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $cycle['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Section -->
                        <div class="dept-select-wrapper flex-grow-1" style="min-width: 150px; max-width: 180px;">
                            <select name="section_id" id="filter_section" class="form-select dept-filter-select">
                                <option value=""><?= __('all_sections') ?? 'Toutes les sections' ?></option>
                                <?php foreach ($sections as $section): ?>
                                    <option value="<?= $section['id'] ?>" <?= (int) ($filters['section_id'] ?? 0) === (int) $section['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $section['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Niveau -->
                        <div class="dept-select-wrapper flex-grow-1" style="min-width: 150px; max-width: 180px;">
                            <select name="level_id" id="filter_level" class="form-select dept-filter-select">
                                <option value=""><?= __('all_levels') ?? 'Tous les niveaux' ?></option>
                                <?php foreach ($levels as $lvl): ?>
                                    <option value="<?= $lvl['id'] ?>" data-teaching-type="<?= $lvl['teaching_type_id'] ?? '' ?>" <?= (int) ($filters['level_id'] ?? 0) === (int) $lvl['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $lvl['code']) ?> - <?= htmlspecialchars((string) (\App\Core\Translator::lang() === 'en' ? $lvl['libelle_en'] : $lvl['libelle_fr'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Département -->
                        <div class="dept-select-wrapper flex-grow-1" style="min-width: 160px; max-width: 200px;">
                            <select name="department_id" id="filter_department" class="form-select dept-filter-select">
                                <option value=""><?= __('all_departments') ?? 'Tous les départements' ?></option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>" data-teaching-type="<?= $dept['teaching_type_id'] ?? '' ?>" <?= (int) ($filters['department_id'] ?? 0) === (int) $dept['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $dept['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Actions Filtre -->
                        <div class="d-flex gap-2 align-items-center ms-auto">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm text-nowrap scale-on-hover">
                                <i class="bi bi-funnel-fill me-1"></i> <?= __('filter') ?? 'Filtrer' ?>
                            </button>
                            <a href="/classes" class="btn btn-light-theme rounded-circle p-2 d-flex align-items-center justify-content-center reset-btn scale-on-hover" style="width: 42px; height: 42px;" title="<?= __('reset') ?? 'Réinitialiser' ?>">
                                <i class="bi bi-arrow-counterclockwise fs-5"></i>
                            </a>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- LISTE ET PAGINATION DES CLASSES -->
    <div id="classesListContainer">
        <div class="row g-4">
            <?php foreach ($classes as $c): ?>
                <div class="col-12 col-md-6 col-xl-4 class-card-item">
                    <div class="subject-card-compact border-theme-dynamic h-100 position-relative">
                        <div class="subject-card-glow"></div>
                        <div class="card-body p-4 position-relative d-flex flex-column justify-content-between h-100" style="z-index: 1;">
                            <div>
                                <div class="d-flex align-items-start justify-content-between mb-3">
                                    <div class="d-flex align-items-center gap-3 overflow-hidden">
                                        <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-black rounded-4 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0"
                                            style="width: 54px; height: 54px; font-size: 1.2rem;">
                                            <i class="bi bi-door-open-fill"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <h5 class="fw-black m-0 text-main-theme text-truncate" title="<?= htmlspecialchars((string) $c['nom']) ?>">
                                                <?= htmlspecialchars((string) $c['nom']) ?>
                                            </h5>
                                            <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                                <span class="badge bg-soft-primary text-primary extra-small fw-bold px-2.5 py-1 rounded-pill">
                                                    <i class="bi bi-people-fill me-1"></i><?= $c['student_count'] ?? 0 ?> <?= __('students_short') ?? 'Élèves' ?>
                                                </span>
                                                <?php if (!empty($c['main_teacher_nom'])): ?>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary extra-small fw-bold px-2.5 py-1 rounded-pill border border-primary border-opacity-10 text-truncate" style="max-width: 160px;" title="<?= __('main_teacher') ?? 'Prof. Principal' ?>: <?= htmlspecialchars($c['main_teacher_nom'] . ' ' . $c['main_teacher_prenom']) ?>">
                                                        <i class="bi bi-star-fill me-1 text-warning"></i><?= htmlspecialchars($c['main_teacher_nom']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="dropdown flex-shrink-0">
                                        <button class="btn btn-link text-muted p-0 shadow-none border-0" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical fs-5"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2">
                                            <?php if (in_array(App\Core\Session::get('user_role'), ['superadmin', 'admin', 'caissier', 'comptable'])): ?>
                                            <li>
                                                <a class="dropdown-item dropdown-item-modern border-0 bg-transparent text-start w-100" href="/classes/edit?id=<?= $c['id'] ?>">
                                                    <i class="bi bi-pencil text-primary"></i> <?= __('edit') ?? 'Modifier' ?>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item dropdown-item-modern border-0 bg-transparent text-start w-100" href="/classes/manage-team?id=<?= $c['id'] ?>">
                                                    <i class="bi bi-people text-info"></i> <?= __('manage_teaching_team') ?? 'Équipe pédagogique' ?>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                            <?php if (in_array(App\Core\Session::get('user_role'), ['superadmin', 'admin'])): ?>
                                            <li>
                                                <a class="dropdown-item dropdown-item-modern text-danger border-0 bg-transparent text-start w-100 btn-confirm-delete"
                                                   href="/classes/delete?id=<?= $c['id'] ?>&csrf_token=<?= \App\Core\Session::generateCsrfToken() ?>"
                                                   data-student-count="<?= $c['student_count'] ?? 0 ?>"
                                                   data-confirm="<?= __('confirm_delete_text') ?? 'Voulez-vous supprimer cette classe ?' ?>">
                                                    <i class="bi bi-trash text-danger"></i> <?= __('delete') ?? 'Supprimer' ?>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Info Badges Row -->
                                <div class="mt-2 d-flex flex-wrap gap-1.5 align-items-center">
                                    <?php if (!empty($c['level_code'])): ?>
                                        <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 extra-small fw-bold px-2.5 py-1 rounded-pill"
                                            title="<?= htmlspecialchars((string)(\App\Core\Translator::lang() === 'en' ? $c['level_libelle_en'] : $c['level_libelle_fr'])) ?>">
                                            <i class="bi bi-bar-chart-steps me-1"></i><?= htmlspecialchars((string) $c['level_code']) ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if (!empty($c['cycle_nom'])): ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 extra-small fw-bold px-2.5 py-1 rounded-pill">
                                            <i class="bi bi-layers me-1"></i><?= htmlspecialchars((string) $c['cycle_nom']) ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($c['teaching_type_nom'])): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 extra-small fw-bold px-2.5 py-1 rounded-pill">
                                            <i class="bi bi-diagram-3 me-1"></i><?= htmlspecialchars((string) $c['teaching_type_nom']) ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($c['section_nom'])): ?>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-10 extra-small fw-bold px-2.5 py-1 rounded-pill">
                                            <i class="bi bi-grid-3x3-gap me-1"></i><?= htmlspecialchars((string) $c['section_nom']) ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if (!empty($c['department_nom'])): ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-10 extra-small fw-bold px-2.5 py-1 rounded-pill">
                                            <i class="bi bi-building me-1"></i><?= htmlspecialchars((string) $c['department_nom']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top border-theme-light d-flex justify-content-between align-items-center">
                                <span class="extra-small text-muted-theme opacity-75">
                                    <i class="bi bi-door-open me-1"></i> Salle d'enseignement
                                </span>
                                <a href="/classes/edit?id=<?= $c['id'] ?>" class="btn btn-sm btn-light-theme rounded-circle p-1.5 d-flex align-items-center justify-content-center scale-on-hover" style="width: 32px; height: 32px;" title="<?= __('edit') ?? 'Gérer' ?>">
                                    <i class="bi bi-arrow-right-short fs-5"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($classes)): ?>
                <div class="col-12">
                    <div class="subject-card-compact p-5 text-center border-dashed">
                        <i class="bi bi-door-closed fs-1 opacity-25 mb-3 d-block"></i>
                        <h5 class="text-muted"><?= __('no_data') ?? 'Aucune classe trouvée' ?></h5>
                        <?php if ($canManage): ?>
                            <p class="small text-muted mb-4">Commencez par créer la première classe de votre établissement.</p>
                            <a href="/classes/create" class="btn btn-primary rounded-pill px-4"><?= __('add_class') ?? 'Ajouter une classe' ?></a>
                        <?php endif; ?>
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

    <!-- MODALE IMPORT EXCEL CLASSES -->
    <div class="modal fade" id="importClassesModal" tabindex="-1" aria-labelledby="importClassesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: var(--bg-card);">
                <div class="modal-header border-bottom border-theme-light p-4 bg-success bg-opacity-10">
                    <h5 class="modal-title fw-black text-main-theme" id="importClassesModalLabel">
                        <i class="bi bi-file-earmark-spreadsheet-fill me-2 text-success"></i><?= __('import_classes') ?? 'Importer des classes' ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <?php include __DIR__ . '/_import_form.php'; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .dept-header-card {
        background: var(--bg-card);
        border: 1px solid var(--border-theme);
        backdrop-filter: blur(16px);
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .dept-header-card {
        background: rgba(30, 41, 59, 0.7);
        border-color: rgba(255, 255, 255, 0.1);
    }

    .dept-header-bg {
        position: absolute;
        top: 0;
        right: 0;
        width: 320px;
        height: 100%;
        background: radial-gradient(circle at top right, rgba(var(--primary-rgb, 59, 130, 246), 0.15), transparent 70%);
        pointer-events: none;
    }

    .dept-icon-wrapper {
        width: 52px;
        height: 52px;
        background: rgba(var(--primary-rgb, 59, 130, 246), 0.12);
        border: 1px solid rgba(var(--primary-rgb, 59, 130, 246), 0.2);
        box-shadow: inset 0 0 12px rgba(var(--primary-rgb, 59, 130, 246), 0.1);
    }

    .scale-on-hover {
        transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s ease;
    }

    .scale-on-hover:hover {
        transform: translateY(-2px) scale(1.02);
    }

    /* Filter Bar High Contrast Styles */
    .filter-island {
        background: var(--bg-card, #ffffff);
        border: 1px solid var(--border-theme, #e2e8f0);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .filter-island {
        background: rgba(30, 41, 59, 0.7);
        border-color: rgba(255, 255, 255, 0.1);
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.25);
    }

    .dept-search-pill {
        display: flex;
        align-items: center;
    }

    .search-icon {
        position: absolute;
        left: 14px;
        color: var(--primary-color, #3b82f6);
        font-size: 1rem;
        z-index: 5;
        pointer-events: none;
    }

    .dept-filter-input {
        background: var(--bg-body, #f8fafc) !important;
        border: 1px solid var(--border-theme, #cbd5e1) !important;
        color: var(--text-main, #0f172a) !important;
        border-radius: 50px !important;
        padding: 10px 16px 10px 42px !important;
        font-weight: 500;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }

    .dept-filter-input:focus {
        border-color: var(--primary-color) !important;
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb, 59, 130, 246), 0.15) !important;
    }

    [data-theme="dark"] .dept-filter-input {
        background: rgba(15, 23, 42, 0.6) !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
        color: #f8fafc !important;
    }

    .dept-filter-select {
        background-color: var(--bg-body, #f8fafc) !important;
        border: 1px solid var(--border-theme, #cbd5e1) !important;
        color: var(--text-main, #0f172a) !important;
        border-radius: 50px !important;
        padding: 10px 20px !important;
        font-weight: 500;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }

    .dept-filter-select:focus {
        border-color: var(--primary-color) !important;
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb, 59, 130, 246), 0.15) !important;
    }

    [data-theme="dark"] .dept-filter-select {
        background-color: rgba(15, 23, 42, 0.6) !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
        color: #f8fafc !important;
    }

    .dept-filter-select option, select.premium-input option {
        background-color: #ffffff;
        color: #0f172a;
        padding: 10px;
    }

    [data-theme="dark"] .dept-filter-select option, [data-theme="dark"] select.premium-input option {
        background-color: #1e293b !important;
        color: #f8fafc !important;
    }

    .btn-light-theme {
        background: var(--bg-body, #f1f5f9);
        color: var(--text-main, #334155);
        border: 1px solid var(--border-theme, #cbd5e1);
    }

    [data-theme="dark"] .btn-light-theme {
        background: rgba(255, 255, 255, 0.1);
        color: #f8fafc;
        border-color: rgba(255, 255, 255, 0.12);
    }

    .subject-card-compact {
        background: var(--bg-card);
        border-radius: 24px;
        border: 1px solid var(--border-theme) !important;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }

    .subject-card-compact:hover {
        transform: translateY(-6px);
        border-color: var(--primary-color) !important;
        box-shadow: 0 16px 32px rgba(var(--primary-rgb), 0.12);
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
        transform: translateX(4px);
    }

    .avatar-init {
        font-family: 'Inter', sans-serif;
        letter-spacing: -1px;
    }

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

    .border-dashed { border-style: dashed !important; border-width: 2px !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('class-filter-form');
    const searchInput = document.getElementById('search-input');
    const filterTT = document.getElementById('filter_teaching_type');
    const filterCycle = document.getElementById('filter_cycle');
    const filterDept = document.getElementById('filter_department');
    const filterSection = document.getElementById('filter_section');
    const filterLevel = document.getElementById('filter_level');
    let debounceTimer;

    if (searchInput && filterForm) {
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                filterForm.submit();
            }, 400);
        });
    }

    [filterTT, filterCycle, filterDept, filterSection, filterLevel].forEach(selectEl => {
        if (selectEl && filterForm) {
            selectEl.addEventListener('change', function () {
                filterForm.submit();
            });
        }
    });

    if (!filterTT || !filterCycle || !filterDept) return;

    const originalCycles = Array.from(filterCycle.options).filter(opt => opt.value !== '');
    const originalDepts = Array.from(filterDept.options).filter(opt => opt.value !== '');

    function updateDependentFilters() {
        const selectedTT = filterTT.value;

        // Filtrer les cycles
        const currentCycleId = filterCycle.value;
        filterCycle.innerHTML = '<option value=""><?= addslashes(__('all_cycles') ?? 'Tous les cycles') ?></option>';
        let cycleValid = false;
        originalCycles.forEach(opt => {
            const optTT = opt.getAttribute('data-teaching-type');
            if (!selectedTT || !optTT || optTT === selectedTT) {
                const cloned = opt.cloneNode(true);
                if (cloned.value === currentCycleId) {
                    cloned.selected = true;
                    cycleValid = true;
                }
                filterCycle.appendChild(cloned);
            }
        });
        if (currentCycleId && !cycleValid) filterCycle.value = '';

        // Filtrer les départements
        const currentDeptId = filterDept.value;
        filterDept.innerHTML = '<option value=""><?= addslashes(__('all_departments') ?? 'Tous les départements') ?></option>';
        let deptValid = false;
        originalDepts.forEach(opt => {
            const optTT = opt.getAttribute('data-teaching-type');
            if (!selectedTT || !optTT || optTT === selectedTT) {
                const cloned = opt.cloneNode(true);
                if (cloned.value === currentDeptId) {
                    cloned.selected = true;
                    deptValid = true;
                }
                filterDept.appendChild(cloned);
            }
        });
        if (currentDeptId && !deptValid) filterDept.value = '';
    }

    filterTT.addEventListener('change', updateDependentFilters);
    updateDependentFilters();

    // Gestion de l'importation Excel Classes via AJAX
    const importModalEl = document.getElementById('importClassesModal');
    const importForm = document.getElementById('classImportForm');
    const importFileInput = document.getElementById('class-import-file');
    const importSubmitBtn = document.getElementById('class-import-submit');

    if (importFileInput && importSubmitBtn) {
        importFileInput.addEventListener('change', function() {
            importSubmitBtn.disabled = importFileInput.files.length === 0;
        });
    }

    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            e.preventDefault();

            if (!importFileInput || importFileInput.files.length === 0) {
                return;
            }

            const formData = new FormData(importForm);
            if (importSubmitBtn) importSubmitBtn.disabled = true;

            fetch('/classes/upload', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async response => {
                const data = await response.json();
                return { ok: response.ok, data: data };
            })
            .then(({ ok, data }) => {
                if (importSubmitBtn) importSubmitBtn.disabled = false;

                if (ok && data.success) {
                    if (importModalEl) {
                        const bsModal = bootstrap.Modal.getInstance(importModalEl) || new bootstrap.Modal(importModalEl);
                        bsModal.hide();
                    }
                    importForm.reset();
                    if (importSubmitBtn) importSubmitBtn.disabled = true;

                    if (typeof AlertService !== 'undefined') {
                        AlertService.toast('success', data.message);
                    }

                    refreshClassesList();
                } else {
                    const errorMsg = data.message || "<?= addslashes((string) __('error_occurred')) ?>";
                    if (typeof AlertService !== 'undefined') {
                        AlertService.error("<?= addslashes((string) __('error_title')) ?>", errorMsg);
                    } else {
                        alert(errorMsg);
                    }
                }
            })
            .catch(err => {
                if (importSubmitBtn) importSubmitBtn.disabled = false;
                console.error('Import submit error:', err);
                if (typeof AlertService !== 'undefined') {
                    AlertService.toast('error', "<?= addslashes((string) __('communication_error')) ?>");
                }
            });
        });
    }

    function refreshClassesList() {
        fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.getElementById('classesListContainer');
            const currentContent = document.getElementById('classesListContainer');
            if (newContent && currentContent) {
                currentContent.innerHTML = newContent.innerHTML;
            }
        })
        .catch(err => console.error('Error refreshing classes list:', err));
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
hp';
?>