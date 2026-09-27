<?php
$title = __('subjects') ?? 'Matières';
ob_start();

$canManage = \App\Core\PermissionManager::hasPermission('manage_subjects');
$hasActiveSubjectFilters = !empty($filters['q']) || !empty($filters['teaching_type_id']) || !empty($filters['department_id']) || !empty($filters['class_id']);
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">

    <!-- EN-TÊTE DE PAGE : Style Glassmorphism Premium avec support Mode Sombre -->
    <div class="dept-header-card mb-4 p-3 p-md-4 rounded-4 shadow-sm position-relative overflow-hidden">
        <div class="dept-header-bg"></div>
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between w-100 gap-3 position-relative"
            style="z-index: 2;">
            <div class="d-flex align-items-center gap-3">
                <div class="dept-icon-wrapper rounded-4 d-flex align-items-center justify-content-center flex-shrink-0">
                    <i class="bi bi-journal-bookmark-fill fs-4 text-primary"></i>
                </div>
                <div>
                    <h1 class="fw-black fs-4 text-main-theme mb-1 lh-1">
                        <?= __('subjects') ?? 'Matières & Disciplines' ?>
                    </h1>
                    <p class="text-muted-theme mb-0 fw-medium opacity-75" style="font-size: 0.88rem;">
                        <?= __('lang') === 'en' ? 'Manage subjects, coefficients and academic groups' : 'Gérez les matières, coefficients et groupes de cours de l\'établissement' ?>
                    </p>
                </div>
            </div>

            <div class="d-flex flex-row w-100 w-md-auto justify-content-end ms-md-auto gap-2 mt-2 mt-md-0">
                <a id="btn-export-pdf" href="/subjects/export?<?= http_build_query($filters) ?>"
                    class="btn btn-light-theme rounded-pill px-3 py-2 fw-semibold d-flex justify-content-center align-items-center gap-2 scale-on-hover"
                    title="<?= __('export_list') ?? 'Exporter PDF' ?>">
                    <i class="bi bi-file-earmark-pdf text-danger fs-6"></i>
                    <span><?= __('lang') === 'en' ? 'Export PDF' : 'Exporter PDF' ?></span>
                </a>
                <a id="btn-export-excel" href="/subjects/exportExcel?<?= http_build_query($filters) ?>"
                    class="btn btn-success rounded-pill px-3 py-2 fw-semibold d-flex justify-content-center align-items-center gap-2 scale-on-hover"
                    title="Exporter au format Excel (.xlsx)">
                    <i class="bi bi-file-earmark-excel fs-6"></i>
                    <span><?= __('lang') === 'en' ? 'Export Excel' : 'Exporter Excel' ?></span>
                </a>
                <?php if ($canManage): ?>
                    <button type="button"
                        class="btn btn-light-theme rounded-pill px-3 py-2 fw-semibold d-flex justify-content-center align-items-center gap-2 scale-on-hover"
                        data-bs-toggle="modal" data-bs-target="#importSubjectsModal">
                        <i class="bi bi-file-earmark-spreadsheet text-success fs-6"></i>
                        <span><?= __('import') ?? 'Importer' ?></span>
                    </button>
                    <a href="/subjects/create"
                        class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm flex-grow-1 flex-md-grow-0 d-flex justify-content-center align-items-center gap-2 text-nowrap scale-on-hover">
                        <i class="bi bi-plus-lg"></i>
                        <span><?= __('add_subject') ?? 'Ajouter une matière' ?></span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- BARRE DE FILTRES ET RECHERCHE INSTANTANÉE -->
    <div class="filter-island-container mb-4">
        <div class="filter-island subjects-filter-panel p-3 rounded-4 shadow-sm">
            <form method="GET" action="/subjects" id="subject-filter-form" class="filter-form w-100 m-0">
                <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between subjects-filter-layout">

                    <div class="d-flex flex-column flex-sm-row gap-2 flex-grow-1 flex-wrap subjects-filter-fields">
                        <!-- Recherche instantanée -->
                        <div class="subjects-filter-search-field">
                            <label class="subjects-filter-label" for="search-input"><?= htmlspecialchars((string) __('subject'), ENT_QUOTES, 'UTF-8') ?></label>
                            <div class="dept-search-pill position-relative">
                                <i class="bi bi-search search-icon"></i>
                                <input type="text" name="q" id="search-input" class="form-control dept-filter-input ps-5"
                                    value="<?= htmlspecialchars((string) ($filters['q'] ?? '')) ?>"
                                    placeholder="<?= __('search') ?? 'Rechercher' ?> (<?= __('subject_name') ?? 'Intitulé de la matière' ?>)...">
                            </div>
                        </div>

                        <!-- Type Enseignement -->
                        <div class="dept-select-wrapper">
                            <label class="subjects-filter-label" for="filter_teaching_type"><?= htmlspecialchars((string) __('teaching_type'), ENT_QUOTES, 'UTF-8') ?></label>
                            <select name="teaching_type_id" id="filter_teaching_type"
                                class="form-select dept-filter-select">
                                <option value=""><?= __('all_teaching_types') ?? 'Tous les Types' ?></option>
                                <?php foreach ($teachingTypes as $tt): ?>
                                    <option value="<?= $tt['id'] ?>" <?= (int) ($filters['teaching_type_id'] ?? 0) === (int) $tt['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $tt['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Département -->
                        <div class="dept-select-wrapper">
                            <label class="subjects-filter-label" for="filter_department"><?= htmlspecialchars((string) __('department'), ENT_QUOTES, 'UTF-8') ?></label>
                            <select name="department_id" id="filter_department" class="form-select dept-filter-select">
                                <option value=""><?= __('all_departments') ?? 'Tous les départements' ?></option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>"
                                        data-teaching-type="<?= $dept['teaching_type_id'] ?? '' ?>" <?= (int) ($filters['department_id'] ?? 0) === (int) $dept['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $dept['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Classe -->
                        <div class="dept-select-wrapper">
                            <label class="subjects-filter-label" for="filter_class"><?= htmlspecialchars((string) __('class'), ENT_QUOTES, 'UTF-8') ?></label>
                            <select name="class_id" id="filter_class" class="form-select dept-filter-select">
                                <option value=""><?= __('all_classes') ?? 'Toutes les classes' ?></option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= $class['id'] ?>"
                                        data-teaching-type="<?= $class['teaching_type_id'] ?? '' ?>" <?= (int) ($filters['class_id'] ?? 0) === (int) $class['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $class['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Actions Filtre -->
                    <div class="d-flex gap-2 align-items-center justify-content-end subjects-filter-actions">
                        <button type="submit"
                            class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm text-nowrap scale-on-hover">
                            <i class="bi bi-funnel-fill me-1"></i> <?= __('filter') ?? 'Filtrer' ?>
                        </button>
                        <a href="/subjects"
                            class="btn btn-light-theme rounded-circle p-2 d-flex align-items-center justify-content-center reset-btn scale-on-hover"
                            style="width: 42px; height: 42px;" title="<?= __('reset') ?? 'Réinitialiser' ?>">
                            <i class="bi bi-arrow-counterclockwise fs-5"></i>
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- LISTE ET PAGINATION DES MATIÈRES -->
    <div id="subjectsListContainer">
        <div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in">
            <div class="table-responsive subjects-table-wrapper">
                <table class="table-modern subjects-list-table">
                    <thead>
                        <tr>
                            <th class="ps-4 col-subject"><?= __('subject') ?></th>
                            <th class="col-classes"><?= __('classes') ?? 'Classes concernées' ?></th>
                            <th class="col-coefficient"><?= __('coefficient') ?></th>
                            <th class="col-group"><?= __('group') ?></th>
                            <th class="col-status"><?= __('status') ?></th>
                            <?php if (\App\Core\PermissionManager::hasPermission('manage_subjects')): ?>
                                <th class="text-end pe-4 col-actions"><?= __('actions') ?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($subjects)): ?>
                            <tr class="subject-empty-row">
                                <td colspan="<?= $canManage ? 6 : 5 ?>" class="text-center py-5">
                                    <i class="bi bi-book fs-1 opacity-25 mb-3 d-block"></i>
                                    <span class="opacity-50">
                                        <?= $hasActiveSubjectFilters
                                            ? (__('lang') === 'en' ? 'No subjects match these filters.' : 'Aucune matière ne correspond aux filtres sélectionnés.')
                                            : __('no_data') ?>
                                    </span>
                                    <?php if ($hasActiveSubjectFilters): ?>
                                        <div>
                                            <a href="/subjects" class="btn btn-sm btn-light-theme rounded-pill px-3 py-2 mt-3">
                                                <i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i>
                                                <?= __('lang') === 'en' ? 'Clear filters' : 'Réinitialiser les filtres' ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($subjects as $s):
                                $isActive = (int) ($s['status'] ?? 1) === 1;
                                ?>
                                <tr class="subject-row <?= !$isActive ? 'opacity-50 grayscale bg-light' : '' ?>">
                                    <td class="ps-4 col-subject" data-label="<?= htmlspecialchars((string) __('subject'), ENT_QUOTES, 'UTF-8') ?>">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-init bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                                style="width: 36px; height: 36px; border: 1px solid rgba(var(--primary-rgb), 0.2);">
                                                <i class="bi bi-book text-primary small"></i>
                                            </div>
                                            <div class="fw-bold text-main-theme">
                                                <?= htmlspecialchars((string) $s['nom']) ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="col-classes" data-label="<?= htmlspecialchars((string) (__('classes') ?? 'Classes concernées'), ENT_QUOTES, 'UTF-8') ?>">
                                        <?php
                                        $classNames = [];
                                        if (!empty($s['classes_list'])) {
                                            $classNames = array_values(array_filter(array_map('trim', preg_split('/\s*,\s*/', (string) $s['classes_list']))));
                                            $classNames = array_values(array_unique($classNames));
                                        }
                                        ?>
                                        <?php if (empty($classNames)): ?>
                                            <span class="text-muted small"><?= htmlspecialchars((string) __('no_class_associated')) ?></span>
                                        <?php else: ?>
                                            <div class="d-flex flex-wrap gap-1 align-items-center" style="min-height: 28px;">
                                                <?php foreach ($classNames as $className): ?>
                                                    <span class="badge bg-info bg-opacity-10 text-info fw-bold px-2 py-1 rounded-pill" style="font-size: 0.68rem; white-space: nowrap;">
                                                        <i class="bi bi-people-fill me-1"></i><?= htmlspecialchars((string) $className) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="col-coefficient" data-label="<?= htmlspecialchars((string) __('coefficient'), ENT_QUOTES, 'UTF-8') ?>">
                                        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-1 rounded-3">
                                            <?= __('coef') ?>: <?= (int) $s['coefficient'] ?>
                                        </span>
                                        <?php if (isset($s['vhm']) && $s['vhm'] !== null && $s['vhm'] !== ''): ?>
                                            <span class="badge bg-info bg-opacity-10 text-info fw-bold px-2 py-1 rounded-3 ms-1" title="Volume Horaire Ministériel">
                                                VHm: <?= (float) $s['vhm'] ?>h
                                            </span>
                                        <?php endif; ?>
                                        <?php if (isset($s['vhp']) && $s['vhp'] !== null && $s['vhp'] !== ''): ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary fw-bold px-2 py-1 rounded-3 ms-1" title="Volume Horaire Proposé">
                                                VHp: <?= (float) $s['vhp'] ?>h
                                            </span>
                                        <?php endif; ?>
                                        <?php if (isset($s['th_max']) && $s['th_max'] !== null && $s['th_max'] !== ''): ?>
                                            <span class="badge bg-warning bg-opacity-10 text-dark fw-bold px-2 py-1 rounded-3 ms-1" title="Taux Horaire Maximal">
                                                TH(Max): <?= (float) $s['th_max'] ?>h
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($s['observations'])): ?>
                                            <div class="mt-1 text-muted extra-small fst-italic">
                                                <i class="bi bi-info-circle me-1"></i><?= htmlspecialchars((string)$s['observations']) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($s['teaching_type_nom'])): ?>
                                            <div class="mt-1 d-flex gap-1 flex-wrap align-items-center">
                                                <span
                                                    class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1 rounded-pill"
                                                    style="font-size: 0.65rem;">
                                                    <i
                                                        class="bi bi-diagram-3-fill me-1"></i><?= htmlspecialchars((string) $s['teaching_type_nom']) ?>
                                                </span>
                                                <?php if (!empty($s['code_uv'])): ?>
                                                    <span
                                                        class="badge bg-purple bg-opacity-10 text-purple border border-purple border-opacity-25 px-2 py-1 rounded-pill"
                                                        style="font-size: 0.65rem; color: #8b5cf6; background: rgba(139,92,246,0.1);">
                                                        UV: <?= htmlspecialchars((string) $s['code_uv']) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!empty($s['code_ue'])): ?>
                                                    <span
                                                        class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25 px-2 py-1 rounded-pill"
                                                        style="font-size: 0.65rem;">
                                                        UE: <?= htmlspecialchars((string) $s['code_ue']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="col-group" data-label="<?= htmlspecialchars((string) __('group'), ENT_QUOTES, 'UTF-8') ?>">
                                        <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-1 rounded-3">
                                            <?= htmlspecialchars((string) (($s['group_list'] ?? '') ?: ($s['subject_group_libelle'] ?? $s['groupe'] ?? 'Groupe 1'))) ?>
                                        </span>
                                    </td>
                                    <td class="col-status" data-label="<?= htmlspecialchars((string) __('status'), ENT_QUOTES, 'UTF-8') ?>">
                                        <?php if ($isActive): ?>
                                            <span
                                                class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1"
                                                style="font-size: 0.7rem;">
                                                <i class="bi bi-check-circle-fill me-1"></i> <?= __('active') ?>
                                            </span>
                                        <?php else: ?>
                                            <span
                                                class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-1"
                                                style="font-size: 0.7rem;">
                                                <i class="bi bi-x-circle-fill me-1"></i> <?= __('inactive') ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if (\App\Core\PermissionManager::hasPermission('manage_subjects')): ?>
                                        <td class="text-end pe-4 col-actions" data-label="<?= htmlspecialchars((string) __('actions'), ENT_QUOTES, 'UTF-8') ?>">
                                            <div class="d-flex justify-content-end gap-1 align-items-center table-row-actions">
                                                <?php if (\App\Core\Session::get('user_role') === 'superadmin'): ?>
                                                    <a href="/subjects/toggleStatus?id=<?= $s['id'] ?>"
                                                        class="btn btn-sm btn-action-modern btn-confirm-toggle <?= $isActive ? 'text-warning' : 'text-success' ?>"
                                                        data-confirm="<?= $isActive ? __('deactivate_subject_confirm', ['name' => $s['nom']]) : __('activate_subject_confirm', ['name' => $s['nom']]) ?>"
                                                        title="<?= $isActive ? __('deactivate') : __('activate') ?>">
                                                        <i class="bi bi-power fs-5"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="/subjects/edit?id=<?= $s['id'] ?>"
                                                    class="btn btn-sm btn-action-modern text-primary" title="<?= __('edit') ?>">
                                                    <i class="bi bi-pencil-square fs-5"></i>
                                                </a>
                                                 <button type="button"
                                                     class="btn btn-sm btn-action-modern text-danger"
                                                     data-impact-delete="subject"
                                                     data-id="<?= $s['id'] ?>"
                                                     title="<?= __('delete') ?>">
                                                     <i class="bi bi-trash fs-5"></i>
                                                 </button>
                                            </div>
                                        </td>
                                    <?php endif; ?>
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
                                <a class="page-link"
                                    href="?<?= http_build_query(array_merge($filters, ['page' => $page - 1])) ?>"
                                    aria-label="Previous">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        if ($start > 1): ?>
                            <li class="page-item"><a class="page-link"
                                    href="?<?= http_build_query(array_merge($filters, ['page' => 1])) ?>">1</a></li>
                            <?php if ($start > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $start; $i <= $end; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link"
                                    href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($end < $totalPages): ?>
                            <?php if ($end < $totalPages - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                            <li class="page-item"><a class="page-link"
                                    href="?<?= http_build_query(array_merge($filters, ['page' => $totalPages])) ?>"><?= $totalPages ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link"
                                    href="?<?= http_build_query(array_merge($filters, ['page' => $page + 1])) ?>"
                                    aria-label="Next">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
        <!-- MODALE IMPORT EXCEL MATIÈRES -->
        <div class="modal fade" id="importSubjectsModal" tabindex="-1" aria-labelledby="importSubjectsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden"
                    style="background: var(--bg-card);">
                    <div class="modal-header border-bottom border-theme-light p-4 bg-success bg-opacity-10">
                        <h5 class="modal-title fw-black text-main-theme" id="importSubjectsModalLabel">
                            <i
                                class="bi bi-file-earmark-spreadsheet-fill me-2 text-success"></i><?= __('import_subjects') ?? 'Importer des matières' ?>
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

        .dept-filter-select option,
        select.premium-input option {
            background-color: #ffffff;
            color: #0f172a;
            padding: 10px;
        }

        [data-theme="dark"] .dept-filter-select option,
        [data-theme="dark"] select.premium-input option {
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

        .filter-island.subjects-filter-panel {
            width: 100%;
            max-width: none !important;
            padding: 1rem !important;
            border-radius: 14px !important;
        }

        .subjects-filter-layout {
            gap: 1rem !important;
        }

        .subjects-filter-label {
            display: block;
            margin: 0 0 0.35rem;
            color: var(--text-muted, #64748b);
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .subjects-filter-search-field,
        .subjects-filter-fields .dept-select-wrapper {
            width: 100%;
            min-width: 0 !important;
            max-width: none !important;
        }

        .subjects-filter-search-field .dept-search-pill {
            width: 100%;
            min-width: 0;
        }

        #subject-filter-form .dept-filter-input,
        #subject-filter-form .dept-filter-select {
            min-height: 44px;
            border-radius: 10px !important;
        }

        #subject-filter-form .dept-filter-select {
            padding: 10px 14px !important;
        }

        .subjects-filter-actions {
            align-self: end;
            flex-wrap: nowrap;
        }

        .subjects-filter-actions .reset-btn {
            width: 44px !important;
            height: 44px !important;
            flex: 0 0 44px;
        }

        @media (min-width: 992px) {
            .subjects-filter-layout {
                display: grid !important;
                grid-template-columns: minmax(0, 1fr) auto;
            }

            .subjects-filter-fields {
                display: grid !important;
                grid-template-columns: minmax(240px, 1.65fr) repeat(3, minmax(145px, 1fr));
                gap: 0.75rem !important;
                min-width: 0;
            }
        }

        @media (min-width: 576px) and (max-width: 991.98px) {
            .subjects-filter-layout {
                display: grid !important;
                grid-template-columns: minmax(0, 1fr) auto;
            }

            .subjects-filter-fields {
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.75rem !important;
                min-width: 0;
            }

            .subjects-filter-search-field {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 575.98px) {
            .filter-island.subjects-filter-panel {
                padding: 0.9rem !important;
            }

            .subjects-filter-layout,
            .subjects-filter-fields {
                display: grid !important;
                grid-template-columns: minmax(0, 1fr);
                gap: 0.75rem !important;
            }

            .subjects-filter-actions {
                width: 100%;
            }

            .subjects-filter-actions .btn-primary {
                flex: 1 1 auto;
            }
        }

        /* Animations */
        .animate-slide-down {
            animation: slideDown 0.6s cubic-bezier(0.23, 1, 0.32, 1);
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
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

        /* Thème sombre pour le tableau des matières */
        [data-theme="dark"] .modern-card {
            background: rgba(30, 41, 59, 0.7);
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

        [data-theme="dark"] .table-modern tbody td .text-muted {
            color: #a0a0a0;
        }

        @media (max-width: 767.98px) {
            .filter-island {
                border-radius: 24px;
                min-width: 100%;
                padding: 1rem !important;
            }
        }

        @media (max-width: 575.98px) {
            .dept-header-card .d-flex.flex-row.w-100.w-md-auto {
                flex-wrap: wrap;
                justify-content: flex-start !important;
            }

            .dept-header-card .d-flex.flex-row.w-100.w-md-auto > a[href="/subjects/create"] {
                flex: 1 1 100%;
            }
        }

        .table-modern .col-actions .table-row-actions .btn i {
            font-size: 1.5rem !important;
            line-height: 1;
        }

        @media (max-width: 991.98px) {
            #subjectsListContainer .subjects-table-wrapper {
                overflow: visible !important;
            }

            #subjectsListContainer .subjects-list-table,
            #subjectsListContainer .subjects-list-table tbody {
                display: block;
                width: 100%;
            }

            #subjectsListContainer .subjects-list-table {
                min-width: 0 !important;
                table-layout: auto;
            }

            #subjectsListContainer .subjects-list-table thead {
                display: none;
            }

            #subjectsListContainer .subjects-list-table tbody {
                display: grid;
                gap: 0.75rem;
                padding: 0.75rem;
            }

            #subjectsListContainer .subjects-list-table tbody tr.subject-row {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0 0.9rem;
                min-width: 0;
                padding: 0.5rem 0.85rem;
                border: 1px solid var(--border-color, #e2e8f0);
                border-radius: 10px;
                background: var(--bg-card, #fff);
            }

            #subjectsListContainer .subjects-list-table tbody tr.subject-row td {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                justify-content: center;
                min-width: 0;
                width: 100%;
                gap: 0.25rem;
                padding: 0.75rem 0.15rem !important;
                border-radius: 0;
                text-align: left !important;
                font-size: 0.95rem;
            }

            #subjectsListContainer .subjects-list-table tbody tr.subject-row td::before {
                content: attr(data-label);
                margin-bottom: 0.1rem;
                color: var(--text-muted, #64748b);
                font-size: 0.8rem;
                font-weight: 700;
                line-height: 1.25;
                text-transform: uppercase;
            }

            #subjectsListContainer .subjects-list-table tbody tr.subject-row td.col-subject {
                grid-column: 1 / -1;
                padding: 0.6rem 0 0.75rem !important;
                border-bottom: 1px solid var(--border-color, #e2e8f0);
            }

            #subjectsListContainer .subjects-list-table tbody tr.subject-row td.col-subject::before {
                content: none;
            }

            #subjectsListContainer .subjects-list-table tbody tr.subject-row td.col-subject > div {
                width: 100%;
                min-width: 0;
            }

            #subjectsListContainer .subjects-list-table tbody tr.subject-row td.col-subject .fw-bold {
                font-size: 1.05rem;
                line-height: 1.3;
                overflow-wrap: anywhere;
            }

            #subjectsListContainer .subjects-list-table tbody tr.subject-row td.col-classes .badge,
            #subjectsListContainer .subjects-list-table tbody tr.subject-row td.col-coefficient .badge,
            #subjectsListContainer .subjects-list-table tbody tr.subject-row td.col-group .badge,
            #subjectsListContainer .subjects-list-table tbody tr.subject-row td.col-status .badge {
                padding: 0.4rem 0.6rem !important;
                font-size: 0.85rem !important;
                line-height: 1.35;
            }

            #subjectsListContainer .subjects-list-table tbody tr.subject-row td.col-coefficient .extra-small {
                font-size: 0.82rem !important;
            }

            #subjectsListContainer .subjects-list-table tbody tr.subject-row td .badge {
                max-width: 100%;
                white-space: normal !important;
                overflow-wrap: anywhere;
                text-align: left;
            }

            #subjectsListContainer .subjects-list-table tbody tr.subject-row td.col-actions {
                grid-column: 1 / -1;
                flex-direction: row;
                justify-content: flex-end;
                gap: 0.5rem;
                padding-top: 0.5rem !important;
                border-top: 1px solid var(--border-color, #e2e8f0);
            }

            #subjectsListContainer .subjects-list-table tbody tr.subject-row td.col-actions::before {
                margin: 0 auto 0 0;
            }

            #subjectsListContainer .subjects-list-table .table-row-actions,
            #subjectsListContainer .subjects-list-table .table-row-actions .btn {
                visibility: visible !important;
                opacity: 1 !important;
                transform: none;
            }

            #subjectsListContainer .subjects-list-table .table-row-actions .btn {
                width: 40px;
                height: 40px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            #subjectsListContainer .subjects-list-table tbody tr.subject-empty-row {
                display: block;
                padding: 0;
                border: 0;
                background: transparent;
            }

            #subjectsListContainer .subjects-list-table tbody tr.subject-empty-row td {
                display: block;
                text-align: center !important;
            }
        }

        @media (max-width: 359.98px) {
            #subjectsListContainer .subjects-list-table tbody tr.subject-row {
                grid-template-columns: minmax(0, 1fr);
            }

            #subjectsListContainer .subjects-list-table tbody tr.subject-row td.col-subject,
            #subjectsListContainer .subjects-list-table tbody tr.subject-row td.col-actions {
                grid-column: 1;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterForm = document.getElementById('subject-filter-form');
            const searchInput = document.getElementById('search-input');
            const filterTT = document.getElementById('filter_teaching_type');
            const filterDept = document.getElementById('filter_department');
            const filterClass = document.getElementById('filter_class');
            let debounceTimer;

            if (searchInput && filterForm) {
                searchInput.addEventListener('input', function () {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        filterForm.submit();
                    }, 400);
                });
            }

            [filterTT, filterDept, filterClass].forEach(selectEl => {
                if (selectEl && filterForm) {
                    selectEl.addEventListener('change', function () {
                        filterForm.submit();
                    });
                }
            });

            // L'import ne doit pas dependre de la presence des filtres de la liste.
            const earlyImportFileInput = document.getElementById('subject-import-file');
            const earlyImportFileName = document.getElementById('subject-import-file-name');
            const defaultEarlyImportFileName = earlyImportFileName ? earlyImportFileName.textContent : '';
            const earlyImportSubmitBtn = document.getElementById('subject-import-submit');
            if (earlyImportFileInput && earlyImportSubmitBtn) {
                earlyImportFileInput.addEventListener('change', function () {
                    earlyImportSubmitBtn.disabled = earlyImportFileInput.files.length === 0;
                    if (earlyImportFileName) {
                        earlyImportFileName.textContent = earlyImportFileInput.files.length > 0 ? earlyImportFileInput.files[0].name : defaultEarlyImportFileName;
                    }
                });
            }

            if (!filterTT || !filterClass) return;

            const originalDepts = filterDept ? Array.from(filterDept.options).filter(opt => opt.value !== '') : [];
            const originalClasses = Array.from(filterClass.options).filter(opt => opt.value !== '');

            function updateDependentFilters() {
                const selectedTT = filterTT.value;

                // 1. Filtrer les Départements
                if (filterDept) {
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

                    if (currentDeptId && !deptValid) {
                        filterDept.value = '';
                    }
                }

                // 2. Filtrer les Classes
                const currentClassId = filterClass.value;
                filterClass.innerHTML = '<option value=""><?= addslashes(__('all_classes') ?? 'Toutes les classes') ?></option>';
                let classValid = false;

                originalClasses.forEach(opt => {
                    const optTT = opt.getAttribute('data-teaching-type');
                    if (!selectedTT || !optTT || optTT === selectedTT) {
                        const cloned = opt.cloneNode(true);
                        if (cloned.value === currentClassId) {
                            cloned.selected = true;
                            classValid = true;
                        }
                        filterClass.appendChild(cloned);
                    }
                });

                if (currentClassId && !classValid) {
                    filterClass.value = '';
                }

                updateExportLinks();
            }

            function updateExportLinks() {
                const searchInput = document.getElementById('search-input');
                const btnPdf = document.getElementById('btn-export-pdf');
                const btnExcel = document.getElementById('btn-export-excel');
                
                const params = new URLSearchParams();
                if (searchInput && searchInput.value.trim() !== '') params.set('q', searchInput.value.trim());
                if (filterTT && filterTT.value) params.set('teaching_type_id', filterTT.value);
                if (filterDept && filterDept.value) params.set('department_id', filterDept.value);
                if (filterClass && filterClass.value) params.set('class_id', filterClass.value);

                const queryString = params.toString() ? `?${params.toString()}` : '';
                if (btnPdf) btnPdf.setAttribute('href', `/subjects/export${queryString}`);
                if (btnExcel) btnExcel.setAttribute('href', `/subjects/exportExcel${queryString}`);
            }

            filterTT.addEventListener('change', updateDependentFilters);
            if (filterDept) filterDept.addEventListener('change', updateExportLinks);
            if (filterClass) filterClass.addEventListener('change', updateExportLinks);
            if (searchInput) searchInput.addEventListener('input', updateExportLinks);

            updateDependentFilters();

            // Gestion de l'importation Excel Matières via AJAX
            const importModalEl = document.getElementById('importSubjectsModal');
            const importForm = document.getElementById('subjectImportForm');
            const importFileInput = document.getElementById('subject-import-file');
            const importSubmitBtn = document.getElementById('subject-import-submit');

            if (importFileInput && importSubmitBtn) {
                importFileInput.addEventListener('change', function () {
                    importSubmitBtn.disabled = importFileInput.files.length === 0;
                });
            }

            if (importForm) {
                importForm.addEventListener('submit', function (e) {
                    e.preventDefault();

                    if (!importFileInput || importFileInput.files.length === 0) {
                        return;
                    }

                    const formData = new FormData(importForm);
                    if (importSubmitBtn) importSubmitBtn.disabled = true;

                    fetch('/subjects/upload', {
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

                                refreshSubjectsList();
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

            function refreshSubjectsList() {
                fetch(window.location.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(res => res.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newContent = doc.getElementById('subjectsListContainer');
                        const currentContent = document.getElementById('subjectsListContainer');
                        if (newContent && currentContent) {
                            currentContent.innerHTML = newContent.innerHTML;
                        }
                    })
                    .catch(err => console.error('Error refreshing subjects list:', err));
            }
        });
    </script>

    <?php $content = ob_get_clean(); ?>

    <?php include __DIR__ . '/../templates/layout.php'; ?>