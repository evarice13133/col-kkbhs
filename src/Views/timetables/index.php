<?php
$title = __('timetables_menu') . " - " . __('app_name');
$userRole = \App\Core\Session::get('user_role');
$canManage = in_array($userRole, ['admin', 'superadmin'], true) || \App\Core\PermissionManager::hasPermission('manage_timetables');
$csrfToken = \App\Core\Session::generateCsrfToken();
ob_start();
?>


<div class="animate-fade-in container-fluid py-3 px-md-4 google-material-scope">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h2 class="fw-black text-main-theme mb-0 fs-4 font-google">
                    <i class="bi bi-calendar3-week text-primary me-2 icon-pulse-subtle"></i><?= __('timetables_menu') ?>
                </h2>
                <span
                    class="badge bg-primary bg-opacity-10 text-primary rounded-pill font-monospace extra-small px-2.5 py-1 fw-bold border border-primary border-opacity-25">
                    <?= count($timetables) ?> Grille(s)
                </span>
            </div>
            <p class="text-muted-theme small mb-0 font-google"><?= __('timetables_subtitle') ?></p>
        </div>
        <?php if ($canManage ?? true): ?>
            <div class="d-flex flex-wrap gap-2">
                <a href="/timetables/slots"
                    class="btn btn-sm btn-light-theme rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-1.5 border-theme-light shadow-xs hover-lift transition-all">
                    <i class="bi bi-clock text-primary"></i> <?= __('timetables_slots_menu') ?>
                </a>
                <a href="/timetables/rooms"
                    class="btn btn-sm btn-light-theme rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-1.5 border-theme-light shadow-xs hover-lift transition-all">
                    <i class="bi bi-building text-info"></i> <?= __('timetables_rooms_menu') ?>
                </a>
                <a href="/timetables/weeks"
                    class="btn btn-sm btn-light-theme rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-1.5 border-theme-light shadow-xs hover-lift transition-all">
                    <i class="bi bi-calendar-range text-warning"></i> <?= __('timetables_weeks_menu') ?>
                </a>
                <a href="/timetables/wizard"
                    class="btn btn-sm btn-primary rounded-pill px-3.5 py-2 fw-bold shadow-sm d-flex align-items-center gap-1.5 hover-scale transition-all">
                    <i class="bi bi-plus-circle-fill"></i> <?= __('timetables_new_wizard') ?>
                </a>
            </div>
        <?php endif; ?>
    </div>


    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4 animate-fade-in">
        <div class="col-6 col-md-4">
            <div class="modern-card kpi-card border-0 shadow-sm rounded-4 p-3 hover-lift transition-all">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-value text-primary fw-black fs-3 mb-0"><?= count($timetables) ?></div>
                        <div class="kpi-label text-muted-theme small fw-semibold"><?= __('timetables_total_kpi') ?>
                        </div>
                    </div>
                    <div class="kpi-icon-wrapper bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 46px; height: 46px;">
                        <i class="bi bi-grid-3x3-gap fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div
                class="modern-card kpi-card shadow-sm border-0 border-start border-4 border-success rounded-4 p-3 hover-lift transition-all">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-value text-success fw-black fs-3 mb-0">
                            <?= count(array_filter($timetables, fn($t) => $t['statut'] === 'publie')) ?>
                        </div>
                        <div class="kpi-label text-muted-theme small fw-semibold"><?= __('timetables_published_kpi') ?>
                        </div>
                    </div>
                    <div class="kpi-icon-wrapper bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 46px; height: 46px;">
                        <i class="bi bi-check-circle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div
                class="modern-card kpi-card shadow-sm border-0 border-start border-4 border-warning rounded-4 p-3 hover-lift transition-all">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-value text-warning fw-black fs-3 mb-0">
                            <?= count(array_filter($timetables, fn($t) => $t['is_locked_calc'])) ?>
                        </div>
                        <div class="kpi-label text-muted-theme small fw-semibold"><?= __('timetables_locked_kpi') ?>
                        </div>
                    </div>
                    <div class="kpi-icon-wrapper bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 46px; height: 46px;">
                        <i class="bi bi-lock-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Island Filters -->
    <div class="d-flex justify-content-center mb-4">
        <div class="filter-island p-3 shadow-sm rounded-pill w-100 transition-all">
            <form id="filterForm" method="GET" action="/timetables" class="w-100 m-0">
                <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between">
                    <div class="d-flex flex-column flex-sm-row gap-2 flex-grow-1">
                        <!-- Filtre Année -->
                        <div class="flex-grow-1 position-relative">
                            <select name="year_id"
                                class="form-select border-0 bg-transparent text-main-theme fw-semibold custom-select-glow"
                                style="font-size: 0.88rem;" onchange="submitFilterWithFeedback()">
                                <option value=""><?= __('timetables_all_years') ?></option>
                                <?php foreach ($years as $y): ?>
                                    <option value="<?= $y['id'] ?>" <?= $selectedYear == $y['id'] ? 'selected' : '' ?>>
                                        <?= h($y['libelle']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Filtre Classe -->
                        <div class="flex-grow-1 position-relative">
                            <select name="class_id"
                                class="form-select border-0 bg-transparent text-main-theme fw-semibold custom-select-glow"
                                style="font-size: 0.88rem;" onchange="submitFilterWithFeedback()">
                                <option value=""><?= __('timetables_all_classes') ?></option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= $selectedClass == $c['id'] ? 'selected' : '' ?>>
                                        <?= h($c['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Filtre Semaine -->
                        <div class="flex-grow-1 position-relative">
                            <select name="week_id"
                                class="form-select border-0 bg-transparent text-main-theme fw-semibold custom-select-glow"
                                style="font-size: 0.88rem;" onchange="submitFilterWithFeedback()">
                                <option value=""><?= __('timetables_all_weeks') ?></option>
                                <?php foreach ($weeks as $w): ?>
                                    <option value="<?= $w['id'] ?>" <?= $selectedWeek == $w['id'] ? 'selected' : '' ?>>
                                        <?= h($w['libelle']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex gap-2 align-items-center justify-content-end">
                        <button type="submit" id="btnFilterSubmit"
                            class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm text-nowrap hover-scale transition-all">
                            <i class="bi bi-funnel-fill me-1"></i> <?= __('timetables_filter_btn') ?>
                        </button>
                        <a href="/timetables"
                            class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center border-theme-light hover-rotate transition-all filter-reset-btn"
                            style="width: 40px; height: 40px;" title="<?= __('timetables_reset_btn') ?>">
                            <i class="bi bi-arrow-counterclockwise fs-5"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="modern-card border-0 shadow-sm rounded-4 overflow-hidden animate-fade-in timetable-list-card">
        <div class="timetable-list-heading">
            <div class="d-flex align-items-center gap-3">
                <span class="timetable-list-heading-icon" aria-hidden="true">
                    <i class="bi bi-calendar2-week"></i>
                </span>
                <div>
                    <h3 class="timetable-list-title">Grilles planifiées</h3>
                    <p class="timetable-list-description">Retrouvez les périodes, classes et actions de chaque grille.</p>
                </div>
            </div>
            <span class="timetable-list-count">
                <i class="bi bi-grid-3x3-gap me-1" aria-hidden="true"></i><?= count($timetables) ?>
                <?= count($timetables) > 1 ? 'grilles' : 'grille' ?>
            </span>
        </div>
        <div class="table-responsive timetable-table-scroll">
            <table class="table table-modern align-middle mb-0" id="timetablesTable">
                <caption class="visually-hidden">Emplois du temps planifiés et actions disponibles</caption>
                <thead>
                    <tr>
                        <th scope="col" class="ps-4 py-3"><?= __('timetables_col_status') ?></th>
                        <th scope="col" class="py-3"><?= __('cycle') ?? 'Cycle' ?></th>
                        <th scope="col" class="py-3"><?= __('level') ?? 'Niveau' ?></th>
                        <th scope="col" class="py-3"><?= __('timetables_col_week') ?></th>
                        <th scope="col" class="py-3">Classes concernées</th>
                        <th scope="col" class="pe-4 text-end py-3"><?= __('timetables_col_actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($timetables)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted-theme">
                                <div class="py-4">
                                    <i class="bi bi-calendar-x fs-1 d-block mb-3 text-secondary opacity-50"></i>
                                    <h6 class="fw-bold text-main-theme mb-1"><?= __('timetables_no_found') ?></h6>
                                    <p class="small text-muted-theme mb-3">Aucun emploi du temps ne correspond à vos
                                        filtres.</p>
                                    <?php if ($canManage): ?>
                                        <a href="/timetables/wizard"
                                            class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-bold shadow-sm">
                                            <i class="bi bi-magic me-1"></i>Créer une grille
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>

                    <?php else: ?>
                        <?php
                        $userRole = \App\Core\Session::get('user_role');
                        $canManage = in_array($userRole, ['admin', 'superadmin'], true) || \App\Core\PermissionManager::hasPermission('manage_timetables');
                        $csrfToken = \App\Core\Session::generateCsrfToken();
                        ?>
                        <?php foreach ($timetables as $t): ?>
                            <tr class="table-row-hover transition-all">
                                <!-- Statut -->
                                <td class="ps-4 py-3">
                                    <?php if ($t['statut'] === 'publie'): ?>
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill fw-bold shadow-xs">
                                            <i class="bi bi-check-circle-fill me-1"></i><?= __('timetables_status_published') ?>
                                        </span>
                                    <?php elseif ($t['statut'] === 'verrouille' || $t['is_locked_calc']): ?>
                                        <span
                                            class="badge bg-secondary bg-opacity-15 text-main-theme border border-secondary border-opacity-25 px-2.5 py-1 rounded-pill fw-bold shadow-xs">
                                            <i class="bi bi-lock-fill me-1"></i><?= __('timetables_status_locked') ?>
                                        </span>
                                    <?php else: ?>
                                        <span
                                            class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2.5 py-1 rounded-pill fw-bold shadow-xs">
                                            <i class="bi bi-pencil-fill me-1"></i><?= __('timetables_status_draft') ?>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Cycle -->
                                <td>
                                    <span
                                        class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2.5 py-1 rounded-pill fw-bold"
                                        style="font-size: 0.78rem;">
                                        <i class="bi bi-diagram-3-fill me-1 opacity-75"></i><?= h($t['cycle_name'] ?? 'LMD') ?>
                                    </span>
                                </td>

                                <!-- Niveau -->
                                <td>
                                    <span class="fw-bold text-main-theme" style="font-size: 0.88rem;">
                                        <?= h($t['level_name'] ?? 'Général') ?>
                                    </span>
                                    <?php if (!empty($t['teaching_type_name'])): ?>
                                        <div class="extra-small text-muted-theme opacity-75"><?= h($t['teaching_type_name']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Semaine -->
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center shadow-xs"
                                            style="width: 32px; height: 32px; font-size: 0.85rem;">
                                            <i class="bi bi-calendar3-range"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-main-theme" style="font-size: 0.88rem;">
                                                <?= h($t['week_libelle'] ?? 'Semaine sans nom') ?>
                                            </div>
                                            <div class="text-muted-theme opacity-75 extra-small">
                                                <?= !empty($t['week_start']) ? date('d/m/Y', strtotime($t['week_start'])) : '-' ?>
                                                au
                                                <?= !empty($t['week_end']) ? date('d/m/Y', strtotime($t['week_end'])) : '-' ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Classes concernées -->
                                <td>
                                    <?php if (!empty($t['classes'])): ?>
                                        <?php $classCount = count($t['classes']); ?>
                                        <?php if ($classCount > 5): ?>
                                            <details class="timetable-class-details">
                                                <summary>
                                                    <span class="timetable-class-count">
                                                        <i class="bi bi-mortarboard-fill" aria-hidden="true"></i>
                                                        <?= $classCount ?> classes
                                                    </span>
                                                    <span class="timetable-class-toggle">Liste complète</span>
                                                    <i class="bi bi-chevron-down timetable-class-chevron" aria-hidden="true"></i>
                                                </summary>
                                                <div class="timetable-class-list">
                                                    <?php foreach ($t['classes'] as $cls): ?>
                                                        <span class="badge class-badge rounded-pill px-2.5 py-1 fw-bold">
                                                            <i class="bi bi-mortarboard-fill me-1 opacity-75" aria-hidden="true"></i><?= h($cls['nom']) ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </details>
                                        <?php else: ?>
                                            <div class="timetable-class-list">
                                                <?php foreach ($t['classes'] as $cls): ?>
                                                    <span class="badge class-badge rounded-pill px-2.5 py-1 fw-bold">
                                                        <i class="bi bi-mortarboard-fill me-1 opacity-75" aria-hidden="true"></i><?= h($cls['nom']) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif (!empty($t['classes_list'])): ?>
                                        <div class="timetable-class-list">
                                            <span class="badge class-badge rounded-pill px-2.5 py-1 fw-bold">
                                                <?= h($t['classes_list']) ?>
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <div class="timetable-class-list">
                                            <span
                                                class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-0.5 extra-small">
                                                Toutes les classes
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Actions -->
                                <td class="pe-4 text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1 table-row-actions"
                                        role="group" aria-label="Actions de l'emploi du temps">
                                        <a href="/timetables/grid?cycle_id=<?= $t['cycle_id'] ?>&level_id=<?= $t['level_id'] ?>&week_id=<?= $t['week_id'] ?>"
                                            class="btn btn-sm btn-action-modern text-primary hover-scale transition-all"
                                            title="Consulter et planifier la grille" aria-label="Consulter et planifier la grille">
                                            <i class="bi bi-grid-3x3-gap-fill fs-5" aria-hidden="true"></i>
                                        </a>

                                        <?php if ($canManage): ?>
                                            <?php if ($t['statut'] === 'publie'): ?>
                                                <!-- Bouton Dépublier -->
                                                <form method="POST" action="/timetables/unpublish" class="d-inline m-0">
                                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                    <input type="hidden" name="timetable_ids" value="<?= $t['timetable_ids'] ?>">
                                                    <button type="submit"
                                                        class="btn btn-sm btn-action-modern text-warning hover-scale transition-all"
                                                        title="Dépublier (remettre en brouillon)" aria-label="Dépublier et remettre en brouillon">
                                                        <i class="bi bi-arrow-counterclockwise fs-5" aria-hidden="true"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <!-- Bouton Publier -->
                                                <form method="POST" action="/timetables/publish" class="d-inline m-0">
                                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                    <input type="hidden" name="timetable_ids" value="<?= $t['timetable_ids'] ?>">
                                                    <button type="submit"
                                                        class="btn btn-sm btn-action-modern text-success hover-scale transition-all"
                                                        title="Publier officiellement l'emploi du temps" aria-label="Publier officiellement l'emploi du temps">
                                                        <i class="bi bi-check-circle-fill fs-5" aria-hidden="true"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <a href="/timetables/pdf?cycle_id=<?= $t['cycle_id'] ?>&level_id=<?= $t['level_id'] ?>&week_id=<?= $t['week_id'] ?>&mode=print"
                                            target="_blank"
                                            class="btn btn-sm btn-action-modern text-secondary hover-scale transition-all"
                                            rel="noopener"
                                            title="<?= __('print') ?? 'Imprimer' ?>" aria-label="<?= __('print') ?? 'Imprimer' ?>">
                                            <i class="bi bi-printer fs-5" aria-hidden="true"></i>
                                        </a>
                                        <a href="/timetables/pdf?cycle_id=<?= $t['cycle_id'] ?>&level_id=<?= $t['level_id'] ?>&week_id=<?= $t['week_id'] ?>&mode=download"
                                            class="btn btn-sm btn-action-modern text-danger hover-scale transition-all"
                                            title="Télécharger l'emploi du temps en PDF" aria-label="Télécharger l'emploi du temps en PDF">
                                            <i class="bi bi-file-earmark-pdf-fill fs-5" aria-hidden="true"></i>
                                        </a>

                                        <?php if ($canManage): ?>
                                            <button type="button"
                                                class="btn btn-sm btn-action-modern text-danger hover-scale transition-all"
                                                data-impact-delete="timetable" data-id="<?= explode(',', $t['timetable_ids'])[0] ?>"
                                                title="Radiographie d'impact & Suppression" aria-label="Analyser l'impact et supprimer l'emploi du temps">
                                                <i class="bi bi-trash3-fill fs-5" aria-hidden="true"></i>
                                            </button>
                                        <?php endif; ?>
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

<!-- Toast Container pour Feedback Instantané -->
<div id="indexToastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;"></div>

<style>
    /* Design Tokens & Universal Theme Compatibility */
    .btn-light-theme {
        background-color: var(--card-bg, #ffffff);
        color: var(--text-color, #1e293b);
        border: 1px solid var(--border-color, rgba(0, 0, 0, 0.1));
    }

    .btn-light-theme:hover {
        background-color: rgba(26, 115, 232, 0.08);
        color: #1a73e8;
        border-color: rgba(26, 115, 232, 0.3);
    }

    .border-theme-light {
        border-color: var(--border-color, rgba(0, 0, 0, 0.08)) !important;
    }

    .text-main-theme {
        color: var(--text-main, #1e293b) !important;
    }

    .text-muted-theme {
        color: var(--text-muted, #64748b) !important;
    }

    /* Class Badge Design */
    .class-badge {
        background-color: rgba(26, 115, 232, 0.08);
        color: #1a73e8;
        border: 1px solid rgba(26, 115, 232, 0.2);
        font-size: 0.75rem;
        transition: all 0.2s ease;
    }

    .class-badge:hover {
        background-color: #1a73e8;
        color: #ffffff;
        transform: translateY(-1px);
    }

    /* Action Modern Buttons */
    .btn-action-modern {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(0, 0, 0, 0.06);
        transition: all 0.2s ease;
    }

    .btn-action-modern:hover {
        background-color: rgba(26, 115, 232, 0.1);
        border-color: rgba(26, 115, 232, 0.3);
        transform: scale(1.08);
    }

    /* Micro-Animations & Dynamic States */
    .transition-all {
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .hover-lift:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
    }

    .hover-scale:hover {
        transform: scale(1.05);
    }

    .hover-scale:active {
        transform: scale(0.96);
    }

    .hover-rotate:hover {
        transform: rotate(-45deg);
    }

    .table-modern {
        color: var(--text-color, #1e293b);
    }

    .table-modern thead th {
        background-color: rgba(0, 0, 0, 0.02);
        color: var(--text-muted, #64748b);
        font-weight: 700;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid rgba(0, 0, 0, 0.06);
    }

    .table-row-hover:hover {
        background-color: rgba(26, 115, 232, 0.04) !important;
    }

    .filter-island {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(0, 0, 0, 0.08);
        min-width: 65%;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .filter-island:focus-within {
        border-color: #1a73e8 !important;
        box-shadow: 0 12px 30px -8px rgba(26, 115, 232, 0.2) !important;
        transform: translateY(-2px);
    }

    .custom-select-glow:focus {
        box-shadow: none !important;
        color: #1a73e8 !important;
    }

    /* Skeleton Animation for Table Refresh */
    .skeleton-tr {
        animation: pulseTableSkeleton 1.4s infinite;
    }

    @keyframes pulseTableSkeleton {

        0%,
        100% {
            opacity: 0.6;
        }

        50% {
            opacity: 1;
        }
    }

    /* Toast Animations */
    .toast-feedback-entry {
        animation: slideInRight 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    /* ==========================================================================
       Dark Mode Overrides
       ========================================================================== */
    [data-theme="dark"] .filter-island {
        background: #1e293b !important;
        border-color: rgba(255, 255, 255, 0.15) !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4) !important;
    }

    [data-theme="dark"] .filter-island select {
        color: #f8fafc !important;
    }

    [data-theme="dark"] .filter-island select option {
        background-color: #1e293b !important;
        color: #f8fafc !important;
    }

    [data-theme="dark"] .modern-card {
        background: #1e293b !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
        color: #f8fafc !important;
    }

    [data-theme="dark"] .text-main-theme {
        color: #f8fafc !important;
    }

    [data-theme="dark"] .text-muted-theme {
        color: #94a3b8 !important;
    }

    [data-theme="dark"] .table-modern {
        color: #f8fafc !important;
    }

    [data-theme="dark"] .table-modern thead th {
        background-color: rgba(255, 255, 255, 0.04) !important;
        color: #cbd5e1 !important;
        border-bottom-color: rgba(255, 255, 255, 0.1) !important;
    }

    [data-theme="dark"] .table-modern td {
        border-color: rgba(255, 255, 255, 0.06) !important;
    }

    [data-theme="dark"] .table-row-hover:hover {
        background-color: rgba(255, 255, 255, 0.05) !important;
    }

    [data-theme="dark"] .class-badge {
        background-color: rgba(96, 165, 250, 0.15);
        color: #60a5fa;
        border-color: rgba(96, 165, 250, 0.3);
    }

    [data-theme="dark"] .class-badge:hover {
        background-color: #3b82f6;
        color: #ffffff;
    }

    [data-theme="dark"] .btn-action-modern {
        background-color: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.12);
        color: #f8fafc;
    }

    [data-theme="dark"] .btn-action-modern:hover {
        background-color: rgba(96, 165, 250, 0.2);
        border-color: #60a5fa;
        color: #60a5fa;
    }

    [data-theme="dark"] .btn-light-theme {
        background-color: #1e293b;
        color: #f8fafc;
        border-color: rgba(255, 255, 255, 0.15);
    }

    [data-theme="dark"] .btn-light-theme:hover {
        background-color: rgba(96, 165, 250, 0.15);
        color: #60a5fa;
        border-color: #60a5fa;
    }

    [data-theme="dark"] .filter-reset-btn {
        background-color: #1e293b !important;
        color: #f8fafc !important;
        border-color: rgba(255, 255, 255, 0.15) !important;
    }

    [data-theme="dark"] .modal-content {
        background-color: #1e293b !important;
        color: #f8fafc !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }

    /* Timetable list */
    .timetable-list-card,
    .timetable-list-card:hover {
        border-style: solid !important;
        border-color: var(--border-color, rgba(148, 163, 184, 0.18)) !important;
        transform: none !important;
    }

    .timetable-list-card {
        overflow: hidden !important;
    }

    .timetable-list-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.15rem 1.35rem;
        border-bottom: 1px solid var(--border-color, rgba(148, 163, 184, 0.18));
        background: linear-gradient(110deg, rgba(26, 115, 232, 0.055), transparent 70%);
    }

    .timetable-list-heading-icon {
        display: inline-flex;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        align-items: center;
        justify-content: center;
        border-radius: 13px;
        background: rgba(26, 115, 232, 0.1);
        color: #1a73e8;
        font-size: 1.15rem;
    }

    .timetable-list-title {
        margin: 0;
        color: var(--text-main, #1e293b);
        font-size: 1rem;
        font-weight: 750;
        letter-spacing: -0.02em;
    }

    .timetable-list-description {
        margin: 0.2rem 0 0;
        color: var(--text-muted, #64748b);
        font-size: 0.78rem;
    }

    .timetable-list-count {
        display: inline-flex;
        align-items: center;
        padding: 0.42rem 0.72rem;
        border: 1px solid rgba(26, 115, 232, 0.16);
        border-radius: 999px;
        background: rgba(26, 115, 232, 0.07);
        color: #1a73e8;
        font-size: 0.75rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .timetable-table-scroll {
        overscroll-behavior-x: contain;
        scrollbar-color: rgba(100, 116, 139, 0.45) transparent;
        scrollbar-width: thin;
    }

    #timetablesTable.table-modern {
        min-width: 1040px;
        table-layout: auto;
    }

    #timetablesTable.table-modern thead th {
        padding-top: 0.9rem;
        padding-bottom: 0.9rem;
        border-bottom-width: 1px;
        font-size: 0.69rem;
        letter-spacing: 0.075em;
        white-space: nowrap;
    }

    #timetablesTable.table-modern tbody td {
        padding-top: 1.05rem;
        padding-bottom: 1.05rem;
        border-bottom: 1px solid var(--border-color, rgba(148, 163, 184, 0.14));
        vertical-align: middle;
    }

    #timetablesTable.table-modern tbody tr:last-child td {
        border-bottom: 0;
    }

    #timetablesTable .table-row-actions {
        flex-wrap: nowrap;
        white-space: nowrap;
    }

    #timetablesTable .btn-action-modern {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        border-radius: 11px;
    }

    #timetablesTable .btn-action-modern:focus-visible,
    .timetable-class-details summary:focus-visible {
        outline: 3px solid rgba(26, 115, 232, 0.4);
        outline-offset: 2px;
    }

    .timetable-class-list {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.38rem;
    }

    #timetablesTable .class-badge {
        padding: 0.36rem 0.62rem !important;
        border-radius: 999px !important;
        font-size: 0.7rem;
        letter-spacing: 0.01em;
        white-space: nowrap;
    }

    .timetable-class-details {
        min-width: 145px;
    }

    .timetable-class-details summary {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.42rem 0.62rem;
        border: 1px solid rgba(26, 115, 232, 0.16);
        border-radius: 10px;
        background: rgba(26, 115, 232, 0.055);
        cursor: pointer;
        list-style: none;
        user-select: none;
    }

    .timetable-class-details summary::-webkit-details-marker {
        display: none;
    }

    .timetable-class-count {
        display: inline-flex;
        align-items: center;
        gap: 0.32rem;
        color: #1a73e8;
        font-size: 0.76rem;
        font-weight: 750;
        white-space: nowrap;
    }

    .timetable-class-toggle {
        color: var(--text-muted, #64748b);
        font-size: 0.69rem;
        white-space: nowrap;
    }

    .timetable-class-chevron {
        color: var(--text-muted, #64748b);
        font-size: 0.68rem;
        transition: transform 0.2s ease;
    }

    .timetable-class-details[open] summary {
        margin-bottom: 0.55rem;
    }

    .timetable-class-details[open] .timetable-class-chevron {
        transform: rotate(180deg);
    }

    .timetable-class-details .timetable-class-list {
        max-width: 340px;
    }

    [data-theme="dark"] .timetable-list-heading {
        background: linear-gradient(110deg, rgba(96, 165, 250, 0.1), transparent 70%);
    }

    [data-theme="dark"] .timetable-list-heading-icon {
        background: rgba(96, 165, 250, 0.15);
        color: #93c5fd;
    }

    [data-theme="dark"] .timetable-list-count,
    [data-theme="dark"] .timetable-class-details summary {
        border-color: rgba(96, 165, 250, 0.2);
        background: rgba(96, 165, 250, 0.1);
    }

    [data-theme="dark"] .timetable-list-count,
    [data-theme="dark"] .timetable-class-count {
        color: #93c5fd;
    }

    @media (max-width: 767.98px) {
        .timetable-list-heading {
            align-items: flex-start;
            padding: 1rem;
        }

        .timetable-list-heading-icon {
            width: 38px;
            height: 38px;
            flex-basis: 38px;
        }

        .timetable-list-description {
            max-width: 230px;
            line-height: 1.45;
        }

        .timetable-list-count {
            padding: 0.35rem 0.55rem;
            font-size: 0.7rem;
        }

        #timetablesTable.table-modern {
            min-width: 980px;
        }

        #timetablesTable.table-modern thead th {
            padding-right: 0.7rem;
            padding-left: 0.7rem;
        }

        #timetablesTable.table-modern tbody td {
            padding-right: 0.7rem;
            padding-left: 0.7rem;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .timetable-class-chevron {
            transition: none;
        }
    }
</style>

<script>
    function submitFilterWithFeedback() {
        const btn = document.getElementById('btnFilterSubmit');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Filtrage...';
        }
        document.getElementById('filterForm').submit();
    }

    function showIndexToast(msg, icon = 'bi-check-circle-fill') {
        const container = document.getElementById('indexToastContainer');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = 'toast align-items-center text-white bg-dark border-0 shadow-lg show rounded-4 mb-2 toast-feedback-entry';
        toast.setAttribute('role', 'alert');
        toast.style.background = '#202124';
        toast.innerHTML = `
        <div class="d-flex p-3">
            <div class="toast-body font-google small d-flex align-items-center gap-2">
                <i class="bi ${icon} text-primary fs-5"></i>
                <span>${msg}</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
        container.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
</script>

<?php if (\App\Core\Session::get('user_role') === 'superadmin'): ?>
    <!-- Modal de Confirmation Suppression Emploi du Temps -->
    <div class="modal fade" id="deleteTimetableModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden animate-fade-in">
                <div class="modal-header border-0 bg-danger bg-opacity-10 py-3">
                    <h5 class="modal-title fw-bold text-danger d-flex align-items-center gap-2 mb-0">
                        <i class="bi bi-exclamation-triangle-fill"></i> Supprimer l'emploi du temps
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <form method="POST" action="/timetables/delete" id="deleteForm">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                    <input type="hidden" name="timetable_ids" id="delete_timetable_ids" value="">
                    <div class="modal-body py-4 px-4">
                        <p class="mb-2 text-main-theme fw-semibold" style="font-size: 1rem;">Êtes-vous sûr de vouloir
                            supprimer définitivement cet emploi du temps regroupé ?</p>
                        <div
                            class="alert alert-danger border-0 rounded-3 small mb-0 d-flex gap-2 align-items-start shadow-xs">
                            <i class="bi bi-shield-alert fs-5 flex-shrink-0 mt-0.5"></i>
                            <div>
                                <strong>Attention :</strong> Cette action est irréversible. Tous les créneaux positionnés et
                                le journal d'audit associés à cet emploi du temps (<strong
                                    id="delete_timetable_title"></strong>) seront supprimés pour l'ensemble des classes
                                concernées.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 pb-4 px-4 gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold"
                            data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" id="btnConfirmDelete"
                            class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm hover-scale transition-all">
                            <i class="bi bi-trash3-fill me-1"></i> Supprimer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteModal = document.getElementById('deleteTimetableModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const timetableIds = button.getAttribute('data-timetable-ids');
                    const timetableTitle = button.getAttribute('data-timetable-title');

                    document.getElementById('delete_timetable_ids').value = timetableIds || '';
                    document.getElementById('delete_timetable_title').textContent = timetableTitle || '';
                });
            }

            const deleteForm = document.getElementById('deleteForm');
            if (deleteForm) {
                deleteForm.addEventListener('submit', function () {
                    const btn = document.getElementById('btnConfirmDelete');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Suppression...';
                    }
                });
            }
        });
    </script>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../templates/layout.php';
?>