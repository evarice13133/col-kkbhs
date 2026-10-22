<?php 
$title = __('classes') ?? 'Salles de classe';
ob_start(); 

$canManage = \App\Core\PermissionManager::hasPermission('manage_classes_structure');
?>

<div class="classes-page animate-fade-in container-fluid py-3 px-md-4">

    <div class="classes-page-heading">
        <div class="classes-heading-copy">
            <div class="classes-heading-icon"><i class="bi bi-door-open-fill" aria-hidden="true"></i></div>
            <div>
                <h1><?= __('lang') === 'en' ? 'Class management' : 'Gestion des classes' ?></h1>
                <p><?= __('lang') === 'en' ? 'Organize classrooms, academic levels and teaching teams.' : 'Organisez les salles de classe, les niveaux et les équipes pédagogiques.' ?></p>
            </div>
        </div>
        <div class="classes-heading-actions">
            <?php if (!empty($academicYear['nom'])): ?>
                <span class="classes-year-badge"><i class="bi bi-calendar3" aria-hidden="true"></i><?= htmlspecialchars((string) $academicYear['nom']) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <?php
    $classStatCards = [
        ['icon' => 'bi-door-open-fill', 'label' => __('lang') === 'en' ? 'Total classes' : 'Total des classes', 'value' => $classStats['total_classes'] ?? 0, 'detail' => __('lang') === 'en' ? 'In the school' : 'Dans l’établissement', 'tone' => 'blue'],
        ['icon' => 'bi-check-circle-fill', 'label' => __('lang') === 'en' ? 'Active classes' : 'Classes actives', 'value' => $classStats['active_classes'] ?? 0, 'detail' => __('lang') === 'en' ? 'Available for the year' : 'Disponibles cette année', 'tone' => 'green'],
        ['icon' => 'bi-layers-fill', 'label' => __('lang') === 'en' ? 'Cycles' : 'Cycles', 'value' => $classStats['cycles'] ?? 0, 'detail' => __('lang') === 'en' ? 'Active cycles' : 'Cycles actifs', 'tone' => 'purple'],
        ['icon' => 'bi-people-fill', 'label' => __('lang') === 'en' ? 'Active students' : 'Élèves actifs', 'value' => $classStats['students'] ?? 0, 'detail' => __('lang') === 'en' ? 'For the active year' : 'Pour l’année en cours', 'tone' => 'cyan'],
    ];
    ?>
    <section class="classes-stats-grid" aria-label="<?= __('lang') === 'en' ? 'Class summary' : 'Résumé des classes' ?>">
        <?php foreach ($classStatCards as $stat): ?>
            <article class="classes-stat-card classes-stat-<?= $stat['tone'] ?>">
                <span class="classes-stat-icon"><i class="bi <?= $stat['icon'] ?>" aria-hidden="true"></i></span>
                <div class="classes-stat-content">
                    <span class="classes-stat-label"><?= $stat['label'] ?></span>
                    <strong><?= number_format((int) $stat['value'], 0, ',', ' ') ?></strong>
                    <span class="classes-stat-detail"><?= $stat['detail'] ?></span>
                </div>
            </article>
        <?php endforeach; ?>
    </section>

    <div id="classesListContainer" class="classes-list-shell">
        <div class="classes-filter-panel">
            <form method="GET" action="/classes" id="class-filter-form" class="classes-filter-form">
                <label class="classes-search-field">
                    <span class="visually-hidden"><?= htmlspecialchars((string) __('class_name'), ENT_QUOTES, 'UTF-8') ?></span>
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input type="search" name="q" id="search-input"
                        value="<?= htmlspecialchars((string) ($filters['q'] ?? '')) ?>"
                        placeholder="<?= __('search') ?? 'Rechercher' ?> (<?= __('class_name') ?? 'Nom de classe' ?>)..."
                        aria-label="<?= htmlspecialchars((string) __('class_name'), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="button" id="clear-class-search" aria-label="<?= htmlspecialchars((string) __('clear_search'), ENT_QUOTES, 'UTF-8') ?>" <?= ($filters['q'] ?? '') === '' ? 'hidden' : '' ?>>
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </button>
                </label>

                <label class="classes-filter-field">
                    <span><?= __('level') ?? 'Niveau' ?></span>
                    <select name="level_id" id="filter_level">
                        <option value=""><?= __('all_levels') ?? 'Tous les niveaux' ?></option>
                        <?php foreach ($levels as $lvl): ?>
                            <option value="<?= $lvl['id'] ?>" data-teaching-type="<?= $lvl['teaching_type_id'] ?? '' ?>" <?= (int) ($filters['level_id'] ?? 0) === (int) $lvl['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $lvl['code']) ?> - <?= htmlspecialchars((string) (\App\Core\Translator::lang() === 'en' ? $lvl['libelle_en'] : $lvl['libelle_fr'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="classes-filter-field">
                    <span><?= __('section') ?? 'Section' ?></span>
                    <select name="section_id" id="filter_section">
                        <option value=""><?= __('all_sections') ?? 'Toutes les sections' ?></option>
                        <?php foreach ($sections as $section): ?>
                            <option value="<?= $section['id'] ?>" <?= (int) ($filters['section_id'] ?? 0) === (int) $section['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $section['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="classes-filter-field">
                    <span><?= __('department') ?? 'Filière' ?></span>
                    <select name="department_id" id="filter_department">
                        <option value=""><?= __('all_departments') ?? 'Toutes les filières' ?></option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept['id'] ?>" data-teaching-type="<?= $dept['teaching_type_id'] ?? '' ?>" <?= (int) ($filters['department_id'] ?? 0) === (int) $dept['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $dept['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="classes-filter-field">
                    <span><?= __('teaching_type') ?? 'Type d’enseignement' ?></span>
                    <select name="teaching_type_id" id="filter_teaching_type">
                        <option value=""><?= __('all_teaching_types') ?? 'Tous les types' ?></option>
                        <?php foreach ($teachingTypes as $tt): ?>
                            <option value="<?= $tt['id'] ?>" <?= (int) ($filters['teaching_type_id'] ?? 0) === (int) $tt['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $tt['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="classes-filter-field">
                    <span><?= __('cycle') ?? 'Cycle' ?></span>
                    <select name="cycle_id" id="filter_cycle">
                        <option value=""><?= __('all_cycles') ?? 'Tous les cycles' ?></option>
                        <?php foreach ($cycles as $cycle): ?>
                            <option value="<?= $cycle['id'] ?>" data-teaching-type="<?= $cycle['teaching_type_id'] ?? '' ?>" <?= (int) ($filters['cycle_id'] ?? 0) === (int) $cycle['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $cycle['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="classes-filter-field">
                    <span><?= __('status') ?? 'Statut' ?></span>
                    <select name="status" id="filter_status">
                        <option value=""><?= __('lang') === 'en' ? 'All statuses' : 'Tous les statuts' ?></option>
                        <option value="1" <?= ($filters['status'] ?? '') === '1' ? 'selected' : '' ?>><?= __('active') ?? 'Actif' ?></option>
                        <option value="0" <?= ($filters['status'] ?? '') === '0' ? 'selected' : '' ?>><?= __('inactive') ?? 'Inactif' ?></option>
                    </select>
                </label>

                <button type="submit" class="classes-filter-submit">
                    <i class="bi bi-funnel" aria-hidden="true"></i><span><?= __('filter') ?? 'Filtrer' ?></span>
                </button>
                <a href="/classes" class="classes-filter-reset">
                    <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i><span><?= __('reset') ?? 'Réinitialiser' ?></span>
                </a>
            </form>
        </div>

        <div class="classes-bulk-toolbar">
            <div class="classes-bulk-selection">
                <?php if ($canManage): ?>
                    <label class="classes-select-all">
                        <input type="checkbox" id="selectAllClasses" aria-label="<?= __('lang') === 'en' ? 'Select all classes on this page' : 'Sélectionner toutes les classes de cette page' ?>">
                        <span><?= __('lang') === 'en' ? 'Select all' : 'Tout sélectionner' ?></span>
                    </label>
                    <span class="classes-selected-count" id="selectedClassesCount" aria-live="polite"></span>
                <?php endif; ?>
            </div>
            <div class="classes-toolbar-actions">
                <?php if ($canManage): ?>
                    <a href="/classes/create" class="classes-action-primary">
                        <i class="bi bi-plus-lg" aria-hidden="true"></i><span><?= __('add_class') ?? 'Ajouter une classe' ?></span>
                    </a>
                <?php endif; ?>
                <button type="button" class="classes-action-secondary" data-bs-toggle="modal" data-bs-target="#importClassesModal">
                    <i class="bi bi-upload" aria-hidden="true"></i><span><?= __('import') ?? 'Importer' ?></span>
                </button>
                <a href="/classes/export<?= !empty($filters) ? '?' . http_build_query($filters) : '' ?>" class="classes-action-secondary">
                    <i class="bi bi-file-earmark-pdf" aria-hidden="true"></i><span><?= __('lang') === 'en' ? 'Export PDF' : 'Exporter PDF' ?></span>
                </a>
                <?php if ($canManage): ?>
                    <button type="button" class="classes-action-secondary" id="openBulkStatus" data-bs-toggle="modal" data-bs-target="#bulkStatusModal">
                        <i class="bi bi-toggles" aria-hidden="true"></i><span><?= __('bulk_class_status_manage') ?></span><span class="classes-bulk-status-count" id="bulkStatusSelectedCount" hidden></span>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="classes-table-scroll">
            <table class="classes-table">
                <thead>
                    <tr>
                        <?php if ($canManage): ?><th class="classes-check-column"><span class="visually-hidden"><?= __('lang') === 'en' ? 'Select' : 'Sélection' ?></span></th><?php endif; ?>
                        <th><?= __('class') ?? 'Classe' ?></th>
                        <th><?= __('level') ?? 'Niveau' ?></th>
                        <th><?= __('cycle') ?? 'Cycle' ?></th>
                        <th><?= __('section') ?? 'Section' ?></th>
                        <th><?= __('department') ?? 'Filière' ?></th>
                        <th class="classes-number-column"><?= __('lang') === 'en' ? 'Students' : 'Effectif' ?></th>
                        <th><?= __('status') ?? 'Statut' ?></th>
                        <th class="classes-actions-column"><?= __('actions') ?? 'Actions' ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classes as $c): ?>
                        <tr class="<?= (int) $c['status'] === 0 ? 'class-row-inactive' : '' ?>">
                            <?php if ($canManage): ?>
                                <td class="classes-check-column" data-label="<?= __('lang') === 'en' ? 'Select' : 'Sélection' ?>">
                                    <input type="checkbox" class="class-row-checkbox" value="<?= (int) $c['id'] ?>" aria-label="<?= htmlspecialchars((string) $c['nom'], ENT_QUOTES, 'UTF-8') ?>">
                                </td>
                            <?php endif; ?>
                            <td class="classes-name-cell" data-label="<?= __('class') ?? 'Classe' ?>">
                                <span class="classes-class-icon"><i class="bi bi-door-open" aria-hidden="true"></i></span>
                                <span class="classes-class-name"><?= htmlspecialchars((string) $c['nom']) ?></span>
                            </td>
                            <td class="classes-level-cell" data-label="<?= __('level') ?? 'Niveau' ?>"><?= !empty($c['level_code']) ? '<span class="classes-data-chip classes-level-chip">' . htmlspecialchars((string) $c['level_code']) . '</span>' : '<span class="classes-empty-value">—</span>' ?></td>
                            <td class="classes-cycle-cell" data-label="<?= __('cycle') ?? 'Cycle' ?>"><?= !empty($c['cycle_nom']) ? '<span class="classes-data-chip classes-cycle-chip">' . htmlspecialchars((string) $c['cycle_nom']) . '</span>' : '<span class="classes-empty-value">—</span>' ?></td>
                            <td class="classes-section-cell" data-label="<?= __('section') ?? 'Section' ?>"><?= !empty($c['section_nom']) ? '<span class="classes-data-chip classes-section-chip">' . htmlspecialchars((string) $c['section_nom']) . '</span>' : '<span class="classes-empty-value">—</span>' ?></td>
                            <td class="classes-department-cell" data-label="<?= __('department') ?? 'Filière' ?>"><?= !empty($c['department_nom']) ? '<span class="classes-data-chip classes-department-chip">' . htmlspecialchars((string) $c['department_nom']) . '</span>' : '<span class="classes-empty-value">—</span>' ?></td>
                            <td class="classes-number-column" data-label="<?= __('lang') === 'en' ? 'Students' : 'Effectif' ?>"><span class="classes-student-count"><i class="bi bi-people-fill" aria-hidden="true"></i><?= (int) ($c['student_count'] ?? 0) ?></span></td>
                            <td data-label="<?= __('status') ?? 'Statut' ?>">
                                <?php if ((int) $c['status'] === 1): ?>
                                    <span class="classes-status-badge is-active"><span></span><?= __('active') ?? 'Active' ?></span>
                                <?php else: ?>
                                    <span class="classes-status-badge is-inactive"><span></span><?= __('inactive') ?? 'Inactive' ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="classes-actions-column" data-label="<?= __('actions') ?? 'Actions' ?>">
                                <div class="dropdown">
                                    <button class="classes-row-menu" type="button" data-bs-toggle="dropdown" aria-label="<?= htmlspecialchars((string) __('actions'), ENT_QUOTES, 'UTF-8') ?>" aria-expanded="false"><i class="bi bi-three-dots" aria-hidden="true"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 p-2">
                                        <?php if (in_array(App\Core\Session::get('user_role'), ['superadmin', 'admin', 'caissier', 'comptable'])): ?>
                                            <li><a class="dropdown-item" href="/classes/edit?id=<?= (int) $c['id'] ?>"><i class="bi bi-pencil me-2 text-primary"></i><?= __('edit') ?? 'Modifier' ?></a></li>
                                            <li><a class="dropdown-item" href="/classes/manage-team?id=<?= (int) $c['id'] ?>"><i class="bi bi-people me-2 text-info"></i><?= __('manage_teaching_team') ?? 'Équipe pédagogique' ?></a></li>
                                        <?php endif; ?>
                                        <?php if (in_array(App\Core\Session::get('user_role'), ['superadmin', 'admin'])): ?>
                                            <li><button type="button" class="dropdown-item toggle-status-btn" data-id="<?= (int) $c['id'] ?>"><?php if ((int) $c['status'] === 1): ?><i class="bi bi-toggle-off me-2 text-warning"></i><?= __('deactivate') ?? 'Désactiver' ?><?php else: ?><i class="bi bi-toggle-on me-2 text-success"></i><?= __('activate') ?? 'Activer' ?><?php endif; ?></button></li>
                                            <li><button type="button" class="dropdown-item text-danger" data-impact-delete="class" data-id="<?= (int) $c['id'] ?>"><i class="bi bi-trash me-2"></i><?= __('delete') ?? 'Supprimer' ?></button></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($classes)): ?>
                        <tr>
                            <td colspan="<?= $canManage ? 9 : 8 ?>" class="classes-empty-row">
                                <i class="bi bi-search" aria-hidden="true"></i>
                                <strong><?= __('no_data') ?? 'Aucune classe trouvée' ?></strong>
                                <span><?= __('lang') === 'en' ? 'Try changing or clearing your filters.' : 'Essayez de modifier ou de réinitialiser les filtres.' ?></span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="classes-table-footer">
            <span class="classes-results-count">
                <?= __('classes_showing_count', [
                    'start' => $totalCount > 0 ? $offset + 1 : 0,
                    'end' => min($offset + $limit, $totalCount),
                    'total' => $totalCount
                ]) ?>
            </span>
            <?php if ($totalPages > 1): ?>
                <nav aria-label="<?= __('lang') === 'en' ? 'Class pages' : 'Pages des classes' ?>">
                    <ul class="pagination pagination-modern mb-0">
                        <?php if ($page > 1): ?>
                            <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $page - 1])) ?>" aria-label="<?= __('lang') === 'en' ? 'Previous page' : 'Page précédente' ?>"><i class="bi bi-chevron-left"></i></a></li>
                        <?php endif; ?>
                        <?php
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        if ($start > 1): ?>
                            <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => 1])) ?>">1</a></li>
                            <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                        <?php endif; ?>
                        <?php for ($i = $start; $i <= $end; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                        <?php if ($end < $totalPages): ?>
                            <?php if ($end < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                            <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $totalPages])) ?>"><?= $totalPages ?></a></li>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $page + 1])) ?>" aria-label="<?= __('lang') === 'en' ? 'Next page' : 'Page suivante' ?>"><i class="bi bi-chevron-right"></i></a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
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

    <?php if ($canManage): ?>
    <div class="modal fade" id="bulkStatusModal" tabindex="-1" aria-labelledby="bulkStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0 shadow-lg" style="background: var(--bg-card);">
                <div class="modal-header border-bottom border-theme-light">
                    <div>
                        <h5 class="modal-title fw-bold text-main-theme" id="bulkStatusModalLabel"><?= __('bulk_class_status_title') ?></h5>
                        <p class="small text-muted mb-0"><?= __('bulk_class_status_description') ?></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= __('bulk_class_status_close') ?>"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <input type="search" class="form-control flex-grow-1" id="statusClassSearch" placeholder="<?= __('bulk_class_status_search') ?>" aria-label="<?= __('bulk_class_status_search') ?>">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="selectAllStatusClasses"><?= __('bulk_class_status_select_all') ?></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="clearStatusClasses"><?= __('bulk_class_status_deselect_all') ?></button>
                    </div>
                    <div class="small text-muted mb-2"><span id="statusClassSelectionCount">0</span> <span id="statusClassSelectionLabel"><?= __('bulk_class_status_selected_many') ?></span></div>
                    <div class="list-group" id="statusClassList" style="max-height: 50vh; overflow-y: auto;">
                        <?php foreach ($statusClasses as $statusClass): ?>
                            <label class="list-group-item d-flex align-items-center gap-3 status-class-option" data-class-name="<?= htmlspecialchars(mb_strtolower((string) $statusClass['nom']), ENT_QUOTES, 'UTF-8') ?>">
                                <input class="form-check-input m-0 status-class-checkbox" type="checkbox" value="<?= (int) $statusClass['id'] ?>">
                                <span class="flex-grow-1"><?= htmlspecialchars((string) $statusClass['nom']) ?></span>
                                <?php if ((int) $statusClass['status'] === 1): ?>
                                    <span class="badge text-bg-success"><?= __('bulk_class_status_active') ?></span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary"><?= __('bulk_class_status_inactive') ?></span>
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer border-top border-theme-light">
                    <input type="hidden" id="bulkStatusCsrfToken" value="<?= htmlspecialchars(\App\Core\Session::generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= __('bulk_class_status_cancel') ?></button>
                    <button type="button" class="btn btn-outline-danger" id="bulkDeactivateClasses"><i class="bi bi-toggle-off me-1"></i><?= __('deactivate') ?></button>
                    <button type="button" class="btn btn-success" id="bulkActivateClasses"><i class="bi bi-toggle-on me-1"></i><?= __('activate') ?></button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
    .classes-page {
        --classes-accent: #3478f6;
        --classes-accent-soft: rgba(52, 120, 246, 0.12);
        --classes-line: var(--border-theme, #dce5f2);
        --classes-surface: var(--bg-card, #fff);
        --classes-control: var(--bg-body, #f7f9fc);
        --classes-ink: var(--text-main, #17243a);
        --classes-muted: var(--text-muted, #70809a);
        color: var(--classes-ink);
    }

    [data-theme="dark"] .classes-page {
        --classes-accent: #5d9bff;
        --classes-accent-soft: rgba(52, 120, 246, 0.16);
        --classes-line: rgba(116, 151, 203, 0.17);
        --classes-surface: #0a1930;
        --classes-control: #09172b;
        --classes-ink: #e7efff;
        --classes-muted: #91a5c3;
    }

    .classes-page-heading {
        min-height: 78px;
        margin: 0 0 0.85rem;
        padding: 0.9rem 1.05rem;
        border: 1px solid var(--classes-line);
        border-radius: 12px;
        background: var(--classes-surface);
    }

    .classes-heading-icon {
        border: 1px solid rgba(52, 120, 246, 0.15);
        border-radius: 12px;
        background: var(--classes-accent-soft);
        color: var(--classes-accent);
    }

    .classes-page-heading h1 {
        color: var(--classes-ink);
        font-size: clamp(1.25rem, 2vw, 1.55rem);
        font-weight: 750;
        letter-spacing: -0.025em;
    }

    .classes-page-heading p { color: var(--classes-muted); }
    .classes-year-badge { color: var(--classes-muted); }

    .classes-stats-grid {
        gap: 0.7rem;
        margin-bottom: 0.85rem;
    }

    .classes-stat-card {
        min-height: 76px;
        gap: 0.75rem;
        padding: 0.7rem 0.85rem;
        border-color: var(--classes-line);
        border-radius: 11px;
        background: var(--classes-surface);
        box-shadow: none;
    }

    .classes-stat-icon {
        width: 40px;
        height: 40px;
        flex-basis: 40px;
    }

    .classes-stat-content strong {
        font-size: 1.2rem;
        font-weight: 750;
    }

    #classesListContainer.classes-list-shell {
        overflow: visible;
        border: 1px solid var(--classes-line);
        border-radius: 12px;
        background: var(--classes-surface);
        box-shadow: none;
    }

    .classes-filter-panel {
        padding: 0.85rem;
        border-bottom: 1px solid var(--classes-line);
    }

    .classes-filter-form {
        display: grid;
        grid-template-columns: minmax(210px, 1.65fr) repeat(6, minmax(110px, 1fr)) auto auto;
        align-items: end;
        gap: 0.65rem;
    }

    .classes-search-field,
    .classes-filter-field {
        min-width: 0;
        margin: 0;
    }

    .classes-search-field {
        position: relative;
        display: flex;
        height: 39px;
        align-items: center;
        border: 1px solid var(--classes-line);
        border-radius: 7px;
        background: var(--classes-control);
        color: var(--classes-muted);
    }

    .classes-search-field > i {
        position: absolute;
        left: 0.75rem;
        color: var(--classes-accent);
        pointer-events: none;
    }

    .classes-search-field input {
        width: 100%;
        height: 100%;
        padding: 0 2.25rem;
        border: 0;
        outline: 0;
        background: transparent;
        color: var(--classes-ink);
        font-size: 0.78rem;
    }

    .classes-search-field input::placeholder { color: var(--classes-muted); }

    .classes-search-field:focus-within,
    .classes-filter-field select:focus-visible {
        border-color: var(--classes-accent);
        box-shadow: 0 0 0 3px var(--classes-accent-soft);
        outline: 0;
    }

    .classes-search-field button {
        position: absolute;
        right: 0.35rem;
        display: grid;
        width: 27px;
        height: 27px;
        place-items: center;
        border: 0;
        border-radius: 5px;
        background: transparent;
        color: var(--classes-muted);
    }

    .classes-search-field button[hidden] { display: none; }

    .classes-filter-field {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .classes-filter-field > span {
        overflow: hidden;
        color: var(--classes-muted);
        font-size: 0.66rem;
        font-weight: 650;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .classes-filter-field select {
        width: 100%;
        height: 39px;
        padding: 0 1.9rem 0 0.65rem;
        border: 1px solid var(--classes-line);
        border-radius: 7px;
        background-color: var(--classes-control);
        color: var(--classes-ink);
        font-size: 0.74rem;
    }

    .classes-filter-submit,
    .classes-filter-reset,
    .classes-action-primary,
    .classes-action-secondary {
        display: inline-flex;
        min-height: 38px;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        padding: 0.45rem 0.75rem;
        border: 1px solid var(--classes-line);
        border-radius: 7px;
        background: transparent;
        color: var(--classes-ink);
        font-size: 0.74rem;
        font-weight: 650;
        text-decoration: none;
        white-space: nowrap;
        transition: border-color 0.15s ease, background-color 0.15s ease, color 0.15s ease;
    }

    .classes-filter-submit,
    .classes-action-primary {
        border-color: var(--classes-accent);
        background: var(--classes-accent);
        color: #fff;
    }

    .classes-filter-submit:hover,
    .classes-action-primary:hover {
        border-color: color-mix(in srgb, var(--classes-accent) 85%, #000);
        background: color-mix(in srgb, var(--classes-accent) 85%, #000);
        color: #fff;
    }

    .classes-filter-reset:hover,
    .classes-action-secondary:hover {
        border-color: var(--classes-accent);
        background: var(--classes-accent-soft);
        color: var(--classes-accent);
    }

    .classes-bulk-toolbar {
        display: flex;
        min-height: 54px;
        align-items: center;
        justify-content: space-between;
        gap: 0.85rem;
        padding: 0.55rem 0.85rem;
        border-bottom: 1px solid var(--classes-line);
    }

    .classes-bulk-selection,
    .classes-select-all,
    .classes-toolbar-actions {
        display: flex;
        align-items: center;
        gap: 0.55rem;
    }

    .classes-select-all {
        margin: 0;
        color: var(--classes-muted);
        cursor: pointer;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .classes-page input[type="checkbox"] {
        width: 15px;
        height: 15px;
        margin: 0;
        accent-color: var(--classes-accent);
        cursor: pointer;
    }

    .classes-selected-count {
        color: var(--classes-accent);
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .classes-selected-count:empty { display: none; }
    .classes-toolbar-actions { flex-wrap: wrap; justify-content: flex-end; }

    .classes-action-primary,
    .classes-action-secondary {
        min-height: 34px;
        padding: 0.35rem 0.65rem;
        font-size: 0.72rem;
    }

    .classes-table-scroll {
        width: 100%;
        overflow-x: auto;
    }

    .classes-table {
        width: 100%;
        min-width: 900px;
        border-collapse: collapse;
        color: var(--classes-ink);
        font-size: 0.76rem;
    }

    .classes-table th,
    .classes-table td {
        padding: 0.65rem 0.75rem;
        border-bottom: 1px solid var(--classes-line);
        text-align: left;
        vertical-align: middle;
    }

    .classes-table th {
        background: color-mix(in srgb, var(--classes-control) 72%, var(--classes-surface));
        color: var(--classes-muted);
        font-size: 0.68rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .classes-table tbody tr:last-child td { border-bottom: 0; }

    .classes-table tbody tr:hover td {
        background: color-mix(in srgb, var(--classes-accent) 4%, var(--classes-surface));
    }

    .classes-table .classes-check-column {
        width: 42px;
        padding-right: 0.2rem;
        text-align: center;
    }

    .classes-name-cell {
        display: flex;
        min-width: 160px;
        align-items: center;
        gap: 0.55rem;
    }

    .classes-class-icon {
        display: grid;
        width: 29px;
        height: 29px;
        flex: 0 0 29px;
        place-items: center;
        border-radius: 7px;
        background: var(--classes-accent-soft);
        color: var(--classes-accent);
    }

    .classes-class-name {
        overflow: hidden;
        font-weight: 650;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .classes-data-chip,
    .classes-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 5px;
        font-size: 0.68rem;
        font-weight: 650;
        line-height: 1.2;
        white-space: nowrap;
    }

    .classes-data-chip {
        padding: 0.28rem 0.5rem;
        background: var(--classes-accent-soft);
        color: var(--classes-accent);
    }

    .classes-status-badge {
        padding: 0.3rem 0.55rem;
    }

    .classes-status-badge > span {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .classes-status-badge.is-active {
        background: rgba(22, 163, 104, 0.12);
        color: #14845a;
    }

    .classes-status-badge.is-inactive {
        background: rgba(148, 163, 184, 0.15);
        color: var(--classes-muted);
    }

    [data-theme="dark"] .classes-status-badge.is-active { color: #57d6a0; }

    .classes-student-count {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        color: var(--classes-muted);
        font-variant-numeric: tabular-nums;
    }

    .classes-student-count i { color: var(--classes-accent); }
    .classes-empty-value { color: var(--classes-muted); }

    .classes-row-menu {
        display: grid;
        width: 30px;
        height: 28px;
        place-items: center;
        border: 1px solid transparent;
        border-radius: 6px;
        background: var(--classes-accent-soft);
        color: var(--classes-ink);
    }

    .classes-row-menu:hover,
    .classes-row-menu:focus-visible {
        border-color: var(--classes-accent);
        color: var(--classes-accent);
        outline: none;
    }

    .classes-row-inactive { opacity: 0.65; }

    .classes-empty-row {
        height: 150px;
        color: var(--classes-muted) !important;
        text-align: center !important;
    }

    .classes-empty-row > * { display: block; margin: 0.3rem auto; }
    .classes-empty-row > i { color: var(--classes-accent); font-size: 1.25rem; }
    .classes-empty-row > strong { color: var(--classes-ink); }

    .classes-table-footer {
        display: flex;
        min-height: 50px;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.45rem 0.85rem;
        border-top: 1px solid var(--classes-line);
    }

    .classes-table-footer .pagination-modern { gap: 0.3rem; }

    .classes-table-footer .pagination-modern .page-link {
        width: 31px;
        height: 31px;
        border: 1px solid var(--classes-line);
        border-radius: 6px;
        background: transparent;
        box-shadow: none;
        color: var(--classes-muted);
        font-size: 0.72rem;
    }

    .classes-table-footer .pagination-modern .active .page-link {
        border-color: var(--classes-accent);
        background: var(--classes-accent);
        color: #fff;
    }

    .classes-table-footer .pagination-modern .disabled .page-link {
        border-color: transparent;
        background: transparent;
    }

    .classes-page-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.25rem;
        margin: 0 0 1.15rem;
    }

    .classes-heading-copy,
    .classes-heading-actions {
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }

    .classes-heading-copy { min-width: 0; }

    .classes-heading-icon {
        display: grid;
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        place-items: center;
        border-radius: 12px;
        background: rgba(var(--primary-rgb, 59, 130, 246), 0.12);
        color: #315bff;
        font-size: 1.35rem;
    }

    .classes-page-heading h1 {
        margin: 0 0 0.2rem;
        color: var(--text-main, #0f172a);
        font-size: clamp(1.25rem, 2vw, 1.65rem);
        font-weight: 800;
        line-height: 1.2;
    }

    .classes-page-heading p {
        margin: 0;
        color: var(--text-muted, #64748b);
        font-size: 0.85rem;
    }

    .classes-heading-actions { flex: 0 0 auto; }

    .classes-year-badge,
    .classes-create-button {
        display: inline-flex;
        min-height: 40px;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.55rem 0.85rem;
        border: 1px solid var(--border-theme, #dbe3ef);
        border-radius: 9px;
        background: var(--bg-card, #fff);
        color: var(--text-main, #0f172a);
        font-size: 0.8rem;
        font-weight: 650;
        text-decoration: none;
        white-space: nowrap;
    }

    .classes-year-badge { color: var(--text-muted, #475569); }

    .classes-create-button {
        padding-inline: 1rem;
        border-color: #315bff;
        background: #315bff;
        color: #fff;
        box-shadow: 0 2px 6px rgba(49, 91, 255, 0.18) !important;
        transition: background-color 0.15s ease, border-color 0.15s ease;
    }

    .classes-create-button:hover,
    .classes-create-button:focus-visible {
        border-color: #2449df;
        background: #2449df;
        color: #fff;
        box-shadow: 0 3px 8px rgba(49, 91, 255, 0.22) !important;
        transform: none !important;
    }

    .classes-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.8rem;
        margin-bottom: 1rem;
    }

    .classes-stat-card {
        display: flex;
        min-width: 0;
        min-height: 88px;
        align-items: center;
        gap: 0.8rem;
        padding: 0.8rem 0.95rem;
        border: 1px solid var(--border-theme, #e4eaf3);
        border-radius: 10px;
        background: var(--bg-card, #fff);
        box-shadow: 0 3px 12px rgba(15, 23, 42, 0.035);
    }

    .classes-stat-icon {
        display: grid;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        place-items: center;
        border-radius: 50%;
        font-size: 1.05rem;
    }

    .classes-stat-content {
        display: flex;
        min-width: 0;
        flex-direction: column;
        gap: 0.06rem;
    }

    .classes-stat-label {
        overflow: hidden;
        color: var(--text-muted, #64748b);
        font-size: 0.72rem;
        font-weight: 650;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .classes-stat-content strong {
        color: var(--text-main, #0f172a);
        font-size: 1.25rem;
        line-height: 1.25;
    }

    .classes-stat-detail {
        overflow: hidden;
        color: var(--text-muted, #94a3b8);
        font-size: 0.66rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .classes-stat-blue .classes-stat-icon { background: #eaf0ff; color: #315bff; }
    .classes-stat-green .classes-stat-icon { background: #e5f8ef; color: #12a568; }
    .classes-stat-purple .classes-stat-icon { background: #f2eaff; color: #8b3dce; }
    .classes-stat-cyan .classes-stat-icon { background: #e2f8fb; color: #08a4b7; }

    [data-theme="dark"] .classes-stat-blue .classes-stat-icon { background: rgba(49, 91, 255, 0.18); }
    [data-theme="dark"] .classes-stat-green .classes-stat-icon { background: rgba(18, 165, 104, 0.18); }
    [data-theme="dark"] .classes-stat-purple .classes-stat-icon { background: rgba(139, 61, 206, 0.18); }
    [data-theme="dark"] .classes-stat-cyan .classes-stat-icon { background: rgba(8, 164, 183, 0.18); }

    [data-theme="dark"] .classes-stat-card {
        border-color: rgba(255, 255, 255, 0.1);
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.12);
    }

    #classesListContainer.classes-list-shell {
        position: relative;
        overflow: visible;
        border: 1px solid var(--border-theme, #e4eaf3);
        border-radius: 13px;
        background: var(--bg-card, #fff);
        box-shadow: 0 5px 18px rgba(15, 23, 42, 0.045);
    }

    [data-theme="dark"] #classesListContainer.classes-list-shell {
        border-color: rgba(255, 255, 255, 0.1);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
    }

    #classesListContainer .filter-island-container {
        position: relative;
        z-index: 5;
        width: 100%;
        margin: 0 !important;
        overflow: visible;
    }

    #classesListContainer .filter-island-container > .filter-island {
        width: 100% !important;
        max-width: none !important;
        box-sizing: border-box;
        border: 0;
        border-radius: 0 !important;
        background: transparent;
        box-shadow: none;
    }

    .classes-list-toolbar {
        position: relative;
        z-index: 6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-top: 1rem;
        padding-top: 0.8rem;
        border-top: 1px solid var(--border-theme, #e8edf5);
    }

    .classes-results-count {
        color: var(--text-muted, #64748b);
        font-size: 0.76rem;
        white-space: nowrap;
    }

    .classes-toolbar-actions { display: flex; align-items: center; gap: 0.55rem; }

    .classes-toolbar-actions .classes-toolbar-button {
        display: inline-flex;
        min-height: 36px;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        padding: 0.45rem 0.8rem;
        border: 1px solid var(--border-theme, #e0e6ef);
        border-radius: 7px;
        background: var(--bg-body, #f8fafc);
        color: var(--text-main, #334155);
        font-size: 0.76rem;
        font-weight: 650;
        white-space: nowrap;
        box-shadow: none !important;
        transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }

    .classes-toolbar-actions .classes-toolbar-button:hover,
    .classes-toolbar-actions .classes-toolbar-button:focus-visible {
        border-color: var(--button-accent-border);
        background: var(--button-accent-hover) !important;
        color: var(--button-accent-text) !important;
        box-shadow: none !important;
        transform: none !important;
    }

    .classes-import-button {
        --button-accent: #159669;
        --button-accent-text: #11734f;
        --button-accent-border: color-mix(in srgb, var(--button-accent) 35%, var(--border-theme, #e0e6ef));
        --button-accent-hover: color-mix(in srgb, var(--button-accent) 18%, var(--bg-card, #fff));
        border-color: color-mix(in srgb, var(--button-accent) 25%, var(--border-theme, #e0e6ef)) !important;
        background: color-mix(in srgb, var(--button-accent) 14%, var(--bg-card, #fff)) !important;
        color: var(--button-accent-text) !important;
    }

    .classes-status-button {
        --button-accent: #c28522;
        --button-accent-text: #9a6516;
        --button-accent-border: color-mix(in srgb, var(--button-accent) 35%, var(--border-theme, #e0e6ef));
        --button-accent-hover: color-mix(in srgb, var(--button-accent) 18%, var(--bg-card, #fff));
        border-color: color-mix(in srgb, var(--button-accent) 25%, var(--border-theme, #e0e6ef)) !important;
        background: color-mix(in srgb, var(--button-accent) 14%, var(--bg-card, #fff)) !important;
        color: var(--button-accent-text) !important;
    }

    .classes-import-button i { color: #159669; }
    .classes-status-button i { color: #c28522; }

    [data-theme="dark"] .classes-import-button { --button-accent-text: #63d3a2; }
    [data-theme="dark"] .classes-status-button { --button-accent-text: #e7b95e; }

    .classes-toolbar-actions .classes-toolbar-button:focus-visible,
    .classes-create-button:focus-visible,
    .classes-page .class-filter-controls > .btn-primary:focus-visible {
        outline: 3px solid rgba(49, 91, 255, 0.35);
        outline-offset: 2px;
    }

    .classes-page .class-filter-controls > .btn-primary {
        box-shadow: none !important;
        transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }

    .classes-page .class-filter-controls > .btn-primary:hover,
    .classes-page .class-filter-controls > .btn-primary:focus-visible {
        box-shadow: none !important;
        transform: none !important;
    }

    #classesListContainer > .classes-grid {
        position: relative;
        z-index: 1;
        margin: 0;
        padding: 1rem;
        border-top: 1px solid var(--border-theme, #e8edf5);
    }

    #classesListContainer .class-card-item { min-width: 0; }

    #classesListContainer .subject-card-compact {
        border-radius: 10px !important;
        box-shadow: 0 3px 12px rgba(15, 23, 42, 0.035);
    }

    #classesListContainer .subject-card-compact:hover {
        transform: translateY(-2px);
        border-style: solid !important;
        box-shadow: 0 8px 20px rgba(49, 91, 255, 0.1);
    }

    .classes-page #classesListContainer .class-card {
        --class-accent: #5268d8;
        --class-accent-soft: rgba(82, 104, 216, 0.1);
        overflow: visible !important;
        border: 1px solid var(--border-theme, #e2e8f0) !important;
        border-radius: 12px !important;
        background: var(--bg-card, #fff) !important;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.035) !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease !important;
    }

    [data-theme="dark"] .classes-page #classesListContainer .class-card {
        --class-accent: #9aa9ff;
        --class-accent-soft: rgba(118, 137, 245, 0.14);
        border-color: rgba(255, 255, 255, 0.09) !important;
        background: #0d1526 !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12) !important;
    }

    .classes-page #classesListContainer .class-card:hover {
        transform: translateY(-2px) !important;
        border: 1px solid color-mix(in srgb, var(--class-accent) 42%, var(--border-theme, #e2e8f0)) !important;
        border-style: solid !important;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08) !important;
    }

    [data-theme="dark"] .classes-page #classesListContainer .class-card:hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.22) !important;
    }

    .classes-page #classesListContainer .class-card .card-body {
        padding: 1.1rem !important;
    }

    .class-card-icon {
        width: 44px !important;
        height: 44px !important;
        border-radius: 11px !important;
        background: var(--class-accent-soft) !important;
        color: var(--class-accent) !important;
        font-size: 1.05rem !important;
    }

    .class-card-title {
        color: var(--text-main, #0f172a) !important;
        font-size: 1.03rem;
        font-weight: 700;
        letter-spacing: -0.015em;
        line-height: 1.35;
    }

    .class-card-students,
    .class-card-teacher,
    .class-card-chip {
        display: inline-flex;
        align-items: center;
        min-height: 23px;
        padding: 0.25rem 0.5rem !important;
        border: 0 !important;
        border-radius: 5px !important;
        font-size: 0.72rem !important;
        font-weight: 600 !important;
        line-height: 1.2;
    }

    .class-card-students {
        background: var(--class-accent-soft) !important;
        color: var(--class-accent) !important;
        border: 1px solid color-mix(in srgb, var(--class-accent) 18%, transparent) !important;
    }

    .class-card-teacher,
    .class-card-chip {
        background: var(--bg-body, #f3f5f9) !important;
        color: var(--text-muted, #64748b) !important;
        border: 1px solid var(--border-theme, #e2e8f0) !important;
    }

    .class-card-chip-level {
        background: var(--class-accent-soft) !important;
        color: var(--class-accent) !important;
        border-color: color-mix(in srgb, var(--class-accent) 18%, transparent) !important;
    }

    [data-theme="dark"] .class-card-teacher,
    [data-theme="dark"] .class-card-chip {
        border-color: rgba(255, 255, 255, 0.08) !important;
        background: #151f33 !important;
        color: #c5cede !important;
    }

    [data-theme="dark"] .class-card-students,
    [data-theme="dark"] .class-card-chip-level {
        border-color: rgba(154, 169, 255, 0.2) !important;
        background: rgba(118, 137, 245, 0.15) !important;
        color: #b5c0ff !important;
    }

    .class-card-metadata {
        gap: 0.4rem !important;
    }

    .class-card-metadata .class-card-chip i {
        color: var(--class-accent);
        opacity: 0.8;
    }

    .class-card-menu {
        display: grid;
        width: 32px;
        height: 32px;
        place-items: center;
        border-radius: 7px !important;
        color: var(--text-muted, #64748b) !important;
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .class-card-menu:hover,
    .class-card-menu:focus-visible {
        background: var(--class-accent-soft);
        color: var(--class-accent) !important;
        outline: none;
    }

    .class-card-footer {
        border-color: var(--border-theme, #e2e8f0) !important;
        color: var(--text-muted, #64748b);
    }

    .class-card-footer > span {
        color: var(--text-muted, #64748b) !important;
        opacity: 1 !important;
        font-weight: 500;
    }

    .class-card-footer > span i {
        color: var(--class-accent);
    }

    .classes-page #classesListContainer .class-card-footer > a.btn-light-theme {
        border: 1px solid color-mix(in srgb, var(--class-accent) 22%, transparent) !important;
        background: var(--class-accent-soft) !important;
        color: var(--class-accent) !important;
        transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease !important;
    }

    .classes-page #classesListContainer .class-card-footer > a.btn-light-theme:hover,
    .classes-page #classesListContainer .class-card-footer > a.btn-light-theme:focus-visible {
        border-color: color-mix(in srgb, var(--class-accent) 40%, transparent) !important;
        background: color-mix(in srgb, var(--class-accent) 20%, var(--bg-card, #fff)) !important;
        color: var(--class-accent) !important;
        transform: none !important;
        outline: none;
    }

    [data-theme="dark"] .class-card-footer {
        border-color: rgba(255, 255, 255, 0.09) !important;
    }

    .class-card-inactive {
        opacity: 0.62;
    }

    .classes-pagination {
        padding: 0.8rem 1rem;
        border-top: 1px solid var(--border-theme, #e8edf5);
    }

    [data-theme="dark"] #classesListContainer .filter-island {
        background: transparent;
    }

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

    .class-search-control {
        flex: 1 1 320px;
        min-width: min(100%, 280px);
    }

    .class-search-control .dept-filter-input {
        width: 100%;
        padding-right: 44px !important;
    }

    .class-search-clear {
        position: absolute;
        right: 8px;
        z-index: 6;
        display: inline-flex;
        width: 34px;
        height: 34px;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 50%;
        background: transparent;
        color: var(--text-muted, #64748b);
        cursor: pointer;
    }

    .class-search-clear[hidden] {
        display: none;
    }

    .class-search-clear:hover,
    .class-search-clear:focus-visible {
        background: rgba(var(--primary-rgb), 0.1);
        color: var(--primary-color);
        outline: none;
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

    @media (max-width: 767.98px) {
        .class-filter-controls,
        .class-search-control {
            width: 100%;
            min-width: 0;
        }

        .class-search-control {
            flex: 0 0 auto;
        }

        .class-search-control .dept-filter-input {
            min-height: 46px;
        }

        .class-filter-controls > .dept-select-wrapper {
            flex: 0 0 auto !important;
            width: 100%;
            min-width: 0 !important;
            max-width: none !important;
        }

        .class-filter-controls > .dept-select-wrapper .dept-filter-select {
            width: 100%;
            min-height: 44px;
        }

        #classesListContainer > .classes-grid {
            padding: 0.75rem;
        }
    }

    @media (max-width: 575.98px) {
        .classes-page-heading {
            align-items: flex-start;
            gap: 0.65rem;
        }

        .classes-heading-copy,
        .classes-heading-actions {
            gap: 0.55rem;
        }

        .classes-heading-icon {
            width: 38px;
            height: 38px;
            flex-basis: 38px;
            border-radius: 10px;
            font-size: 1.1rem;
        }

        .classes-page-heading p {
            max-width: 13rem;
            font-size: 0.72rem;
            line-height: 1.35;
        }

        .classes-heading-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .classes-year-badge,
        .classes-create-button {
            min-height: 34px;
            padding: 0.4rem 0.55rem;
            font-size: 0.68rem;
        }

        .classes-stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.55rem;
        }

        .classes-stat-card {
            min-height: 76px;
            gap: 0.55rem;
            padding: 0.65rem;
        }

        .classes-stat-icon {
            width: 34px;
            height: 34px;
            flex-basis: 34px;
            font-size: 0.9rem;
        }

        .classes-stat-label { font-size: 0.65rem; }
        .classes-stat-detail { font-size: 0.6rem; }

        .classes-list-toolbar {
            align-items: flex-start;
            flex-direction: column;
        }

        .classes-toolbar-actions { width: 100%; }

        .classes-pagination {
            justify-content: center !important;
        }

        .classes-pagination .pagination {
            flex-wrap: wrap;
        }
    }

    @media (max-width: 1199.98px) {
        .classes-filter-form {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .classes-search-field { grid-column: span 2; }
        .classes-filter-submit,
        .classes-filter-reset { width: 100%; }
    }

    @media (max-width: 767.98px) {
        .classes-page-heading {
            margin-bottom: 0.7rem;
            padding: 0.75rem;
        }

        .classes-filter-form {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .classes-search-field { grid-column: 1 / -1; }
        .classes-filter-submit,
        .classes-filter-reset { width: 100%; }

        .classes-bulk-toolbar {
            align-items: flex-start;
            flex-direction: column;
        }

        .classes-toolbar-actions {
            display: grid;
            width: 100%;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .classes-action-primary,
        .classes-action-secondary { width: 100%; }

        .classes-table-scroll { overflow: visible; }

        .classes-table {
            min-width: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .classes-table thead { display: none; }
        .classes-table tbody { display: grid; gap: 0.65rem; padding: 0.65rem; }

        .classes-table tbody tr {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            overflow: visible;
            border: 1px solid var(--classes-line);
            border-radius: 9px;
            background: var(--classes-surface);
        }

        .classes-table tbody tr:hover td { background: transparent; }

        .classes-table tbody td {
            display: flex;
            min-width: 0;
            min-height: 42px;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            padding: 0.5rem 0.65rem;
            border-bottom: 1px solid var(--classes-line);
            font-size: 0.74rem;
        }

        .classes-table tbody td::before {
            flex: 0 0 auto;
            color: var(--classes-muted);
            content: attr(data-label);
            font-size: 0.64rem;
            font-weight: 600;
        }

        .classes-table tbody .classes-name-cell {
            grid-column: 1 / -1;
            justify-content: flex-start;
            min-height: 49px;
        }

        .classes-table tbody .classes-name-cell::before { margin-right: 0.2rem; }
        .classes-table tbody .classes-check-column { justify-content: flex-start; }
        .classes-table tbody .classes-number-column { justify-content: space-between; }
        .classes-table tbody .classes-actions-column { grid-column: 1 / -1; }

        .classes-table tbody tr > td:last-child { border-bottom: 0; }
        .classes-empty-row { display: block !important; grid-column: 1 / -1; }
        .classes-empty-row::before { content: none !important; }

        .classes-table-footer {
            align-items: flex-start;
            flex-direction: column;
        }

        .classes-table-footer nav { align-self: flex-end; }
    }

    @media (max-width: 575.98px) {
        .classes-page-heading {
            flex-wrap: wrap;
            align-items: center;
        }

        .classes-heading-copy {
            flex: 1 1 100%;
            align-items: center;
        }

        .classes-page-heading p { max-width: none; }

        .classes-heading-actions {
            width: 100%;
            flex-direction: row;
            justify-content: flex-end;
        }

        .classes-year-badge { min-height: 34px; }
        .classes-filter-panel { padding: 0.65rem; }
        .classes-filter-form { gap: 0.5rem; }
        .classes-bulk-toolbar { padding: 0.65rem; }
        .classes-table-footer { padding: 0.65rem; }
    }

    [data-theme="dark"] .classes-page .classes-stat-card {
        border-color: rgba(84, 132, 199, 0.22);
        background: #0b1d37;
        box-shadow: none;
    }

    [data-theme="dark"] .classes-page #classesListContainer.classes-list-shell {
        border-color: rgba(84, 132, 199, 0.24);
        background: #07172d;
        box-shadow: none;
    }

    [data-theme="dark"] .classes-page .classes-filter-panel {
        background: #091a32;
    }

    [data-theme="dark"] .classes-page .classes-filter-field select {
        border-color: rgba(92, 137, 197, 0.35);
        background-color: #0b1e38 !important;
        color: #dce9ff !important;
    }

    [data-theme="dark"] .classes-page .classes-filter-field select option {
        background: #0b1e38;
        color: #dce9ff;
    }

    [data-theme="dark"] .classes-page .classes-search-field {
        border-color: rgba(92, 137, 197, 0.4);
        background: #0b1e38;
    }

    [data-theme="dark"] .classes-page .classes-search-field input {
        color: #e7efff;
    }

    [data-theme="dark"] .classes-page .classes-search-field input::placeholder {
        color: #92a8c9;
    }

    [data-theme="dark"] .classes-page .classes-bulk-toolbar {
        background: #091a32;
    }

    [data-theme="dark"] .classes-page .classes-action-secondary,
    [data-theme="dark"] .classes-page .classes-filter-reset {
        border-color: rgba(75, 126, 191, 0.38);
        background: #0b1e38;
        color: #d7e5fb;
    }

    [data-theme="dark"] .classes-page .classes-action-secondary:hover,
    [data-theme="dark"] .classes-page .classes-filter-reset:hover {
        border-color: #3987ff;
        background: rgba(52, 120, 246, 0.16);
        color: #90bdff;
    }

    [data-theme="dark"] .classes-page .classes-table {
        background: #081a32;
    }

    [data-theme="dark"] .classes-page .classes-table th {
        border-color: rgba(79, 128, 191, 0.28);
        background: #102a49;
        color: #b5c9e7;
    }

    [data-theme="dark"] .classes-page .classes-table td {
        border-color: rgba(79, 128, 191, 0.2);
        background: #091d36;
        color: #dce8f8;
    }

    [data-theme="dark"] .classes-page .classes-table tbody tr:nth-child(even) td {
        background: #0b203b;
    }

    [data-theme="dark"] .classes-page .classes-table tbody tr:hover td {
        background: #112d4f;
    }

    [data-theme="dark"] .classes-page .classes-table tbody .classes-empty-row {
        background: #091d36;
        color: #a6bad7 !important;
    }

    [data-theme="dark"] .classes-page .classes-table .classes-class-icon {
        background: rgba(47, 120, 243, 0.22);
        color: #80b3ff;
    }

    [data-theme="dark"] .classes-page .classes-level-chip {
        background: #174d9d;
        color: #dcebff;
    }

    [data-theme="dark"] .classes-page .classes-cycle-chip {
        background: rgba(111, 74, 209, 0.32);
        color: #d4c5ff;
    }

    [data-theme="dark"] .classes-page .classes-section-chip {
        background: rgba(16, 153, 119, 0.28);
        color: #8be2c4;
    }

    [data-theme="dark"] .classes-page .classes-department-chip {
        background: rgba(89, 74, 194, 0.28);
        color: #c5c2ff;
    }

    [data-theme="dark"] .classes-page .classes-status-badge.is-active {
        background: rgba(13, 145, 98, 0.35);
        color: #78e0b7;
    }

    [data-theme="dark"] .classes-page .classes-status-badge.is-inactive {
        background: rgba(116, 139, 170, 0.2);
        color: #b4c1d4;
    }

    [data-theme="dark"] .classes-page .classes-row-menu {
        border-color: rgba(70, 123, 190, 0.2);
        background: #102a49;
        color: #dce9ff;
    }

    [data-theme="dark"] .classes-page .classes-table-footer {
        border-color: rgba(79, 128, 191, 0.25);
        background: #091a32;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('class-filter-form');
    const searchInput = document.getElementById('search-input');
    const clearSearchButton = document.getElementById('clear-class-search');
    const filterTT = document.getElementById('filter_teaching_type');
    const filterCycle = document.getElementById('filter_cycle');
    const filterDept = document.getElementById('filter_department');
    const filterSection = document.getElementById('filter_section');
    const filterLevel = document.getElementById('filter_level');
    const selectedClassesCount = document.getElementById('selectedClassesCount');
    const bulkStatusSelectedCount = document.getElementById('bulkStatusSelectedCount');

    const getSelectedClassCheckboxes = () => Array.from(document.querySelectorAll('.class-row-checkbox:checked'));
    const updateClassSelectionUI = () => {
        const checkboxes = Array.from(document.querySelectorAll('.class-row-checkbox'));
        const selected = getSelectedClassCheckboxes();
        const selectAll = document.getElementById('selectAllClasses');
        if (selectAll) {
            selectAll.checked = checkboxes.length > 0 && selected.length === checkboxes.length;
            selectAll.indeterminate = selected.length > 0 && selected.length < checkboxes.length;
        }
        const count = selected.length;
        if (selectedClassesCount) {
            selectedClassesCount.textContent = count
                ? `${count} ${count === 1 ? <?= json_encode(__('lang') === 'en' ? 'selected' : 'sélectionnée') ?> : <?= json_encode(__('lang') === 'en' ? 'selected' : 'sélectionnées') ?>}`
                : '';
        }
        if (bulkStatusSelectedCount) {
            bulkStatusSelectedCount.hidden = count === 0;
            bulkStatusSelectedCount.textContent = count ? `(${count})` : '';
        }
    };

    document.addEventListener('change', function(event) {
        if (event.target.id === 'selectAllClasses') {
            document.querySelectorAll('.class-row-checkbox').forEach(checkbox => {
                checkbox.checked = event.target.checked;
            });
            updateClassSelectionUI();
            return;
        }
        if (event.target.matches('.class-row-checkbox')) updateClassSelectionUI();
    });
    updateClassSelectionUI();

    const statusSuccessMessages = {
        activate: {
            title: <?= json_encode(__('bulk_class_status_success_activate_title'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
            one: <?= json_encode(__('bulk_class_status_success_activate_one'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
            many: <?= json_encode(__('bulk_class_status_success_activate_many'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
        },
        deactivate: {
            title: <?= json_encode(__('bulk_class_status_success_deactivate_title'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
            one: <?= json_encode(__('bulk_class_status_success_deactivate_one'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
            many: <?= json_encode(__('bulk_class_status_success_deactivate_many'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
        }
    };

    const showStatusSuccess = (action, count) => {
        const messages = statusSuccessMessages[action];
        const message = count === 1 ? messages.one : messages.many.replace(':count', String(count));
        if (typeof AlertService !== 'undefined' && typeof AlertService.success === 'function') {
            AlertService.success(messages.title, message).then(() => window.location.reload());
        } else {
            alert(messages.title + '\n' + message);
            window.location.reload();
        }
    };

    const statusModal = document.getElementById('bulkStatusModal');
    if (statusModal) {
        const statusSearch = document.getElementById('statusClassSearch');
        const statusOptions = Array.from(statusModal.querySelectorAll('.status-class-option'));
        const statusCheckboxes = Array.from(statusModal.querySelectorAll('.status-class-checkbox'));
        const selectedCount = document.getElementById('statusClassSelectionCount');
        const selectedLabel = document.getElementById('statusClassSelectionLabel');
        const closeStatusModal = () => new Promise(resolve => {
            const modalInstance = bootstrap.Modal.getInstance(statusModal);
            if (!modalInstance || !statusModal.classList.contains('show')) {
                resolve();
                return;
            }
            statusModal.addEventListener('hidden.bs.modal', resolve, { once: true });
            modalInstance.hide();
        });
        const updateSelectionCount = () => {
            const count = statusCheckboxes.filter(checkbox => checkbox.checked).length;
            selectedCount.textContent = String(count);
            selectedLabel.textContent = count === 1
                ? <?= json_encode(__('bulk_class_status_selected_one'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
                : <?= json_encode(__('bulk_class_status_selected_many'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        };

        statusModal.addEventListener('show.bs.modal', function() {
            const selectedIds = new Set(getSelectedClassCheckboxes().map(checkbox => checkbox.value));
            if (selectedIds.size === 0) return;
            statusCheckboxes.forEach(checkbox => {
                checkbox.checked = selectedIds.has(checkbox.value);
            });
            updateSelectionCount();
        });

        statusCheckboxes.forEach(checkbox => checkbox.addEventListener('change', updateSelectionCount));
        statusSearch.addEventListener('input', function() {
            const query = statusSearch.value.trim().toLocaleLowerCase();
            statusOptions.forEach(option => {
                option.classList.toggle('d-none', !option.dataset.className.includes(query));
            });
        });
        document.getElementById('selectAllStatusClasses').addEventListener('click', function() {
            statusOptions.filter(option => !option.classList.contains('d-none')).forEach(option => {
                option.querySelector('.status-class-checkbox').checked = true;
            });
            updateSelectionCount();
        });
        document.getElementById('clearStatusClasses').addEventListener('click', function() {
            statusCheckboxes.forEach(checkbox => checkbox.checked = false);
            updateSelectionCount();
        });

        const submitBulkStatus = async action => {
            const ids = statusCheckboxes.filter(checkbox => checkbox.checked).map(checkbox => Number(checkbox.value));
            if (!ids.length) {
                if (typeof AlertService !== 'undefined') AlertService.toast('warning', <?= json_encode(__('bulk_class_status_select_required'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);
                return;
            }
            const buttons = [document.getElementById('bulkActivateClasses'), document.getElementById('bulkDeactivateClasses')];
            buttons.forEach(button => button.disabled = true);
            try {
                const response = await fetch('/classes/bulk-toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.getElementById('bulkStatusCsrfToken').value
                    },
                    body: JSON.stringify({ ids, action })
                });
                const result = await response.json();
                if (!response.ok || !result.success) throw new Error(result.message || <?= json_encode(__('bulk_class_status_update_failed'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);
                await closeStatusModal();
                showStatusSuccess(action, ids.length);
            } catch (error) {
                if (typeof AlertService !== 'undefined') AlertService.toast('error', error.message);
                else alert(error.message);
            } finally {
                buttons.forEach(button => button.disabled = false);
            }
        };
        document.getElementById('bulkActivateClasses').addEventListener('click', () => submitBulkStatus('activate'));
        document.getElementById('bulkDeactivateClasses').addEventListener('click', () => submitBulkStatus('deactivate'));
    }

    if (searchInput && clearSearchButton) {
        searchInput.addEventListener('input', function () {
            clearSearchButton.hidden = searchInput.value.length === 0;
        });

        clearSearchButton.addEventListener('click', function () {
            searchInput.value = '';
            clearSearchButton.hidden = true;
            searchInput.focus();
        });

        searchInput.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && searchInput.value !== '') {
                event.preventDefault();
                searchInput.value = '';
                clearSearchButton.hidden = true;
            }
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
    const importFileName = document.getElementById('class-import-file-name');
    const defaultImportFileName = importFileName ? importFileName.textContent : '';
    const importSubmitBtn = document.getElementById('class-import-submit');

    if (importFileInput && importSubmitBtn) {
        importFileInput.addEventListener('change', function() {
            importSubmitBtn.disabled = importFileInput.files.length === 0;
            if (importFileName) {
                importFileName.textContent = importFileInput.files.length > 0 ? importFileInput.files[0].name : defaultImportFileName;
            }
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

    document.addEventListener('click', async function(event) {
        const toggleButton = event.target.closest('.toggle-status-btn');
        if (!toggleButton) return;
        toggleButton.disabled = true;
        try {
            const response = await fetch('/classes/toggle?id=' + encodeURIComponent(toggleButton.dataset.id), {
                headers: { 'Accept': 'application/json' }
            });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || <?= json_encode(__('bulk_class_status_update_failed'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);
            const action = Number(result.newStatus) === 1 ? 'activate' : 'deactivate';
            showStatusSuccess(action, 1);
        } catch (error) {
            toggleButton.disabled = false;
            if (typeof AlertService !== 'undefined') AlertService.toast('error', error.message);
            else alert(error.message);
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>