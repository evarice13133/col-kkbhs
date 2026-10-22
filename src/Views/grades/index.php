<?php

/**

 * Vue Index des Notes

 * Refonte Premium avec Glassmorphism et Analyse en temps réel

 */

$title = __('notes_management');

ob_start();



$listExportUrl = '/notes/export?' . http_build_query(array_merge($filters, ['mode' => 'list']));

$reportExportUrl = '/notes/export?' . http_build_query(array_merge($filters, ['mode' => 'report']));

$reportPdfUrl = '/notes/export?' . http_build_query(array_merge($filters, ['mode' => 'report', 'format' => 'pdf']));

$canExportList = (int) $filters['class_id'] > 0 && $classHasFilledGrades;

$canExportReport = (int) $filters['class_id'] > 0 && (int) $filters['subject_id'] > 0;

?>



<div class="animate-fade-in grades-workspace py-3 px-md-4" data-grade-entry-complete-message="<?= htmlspecialchars(__('grade_entry_complete') ?? 'Toutes les évaluations actives ont été saisies.', ENT_QUOTES, 'UTF-8') ?>">

    <!-- EN-TÊTE DE PAGE : Style Glassmorphism Premium avec support Mode Sombre -->
    <div class="dept-header-card mb-4 p-3 p-md-4 rounded-4 shadow-sm position-relative">
        <div class="dept-header-bg"></div>
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between w-100 gap-3 position-relative" style="z-index: 2;">
            <div class="d-flex align-items-center gap-3">
                <div class="dept-icon-wrapper rounded-4 d-flex align-items-center justify-content-center flex-shrink-0">
                    <i class="bi bi-journal-check fs-4 text-primary"></i>
                </div>
                <div>
                    <h1 class="fw-black fs-4 text-main-theme mb-1 lh-1">
                        <?= __('notes_management') ?? 'Gestion des Notes' ?>
                    </h1>
                    <p class="text-muted-theme mb-0 fw-medium opacity-75" style="font-size: 0.88rem;">
                        <?= __('lang') === 'en' ? 'Manage grades, entry sheets and class evaluation reports' : 'Consultez, saisissez et exportez les notes et procès-verbaux d\'évaluation' ?>
                    </p>
                </div>
            </div>
            
            <div class="d-flex flex-row w-100 w-md-auto justify-content-end ms-md-auto gap-2 mt-2 mt-md-0">
                <!-- Export Panel (Premium Dropdown) -->
                <div class="dropdown">
                    <button class="btn btn-light-theme rounded-pill px-3 py-2 fw-semibold d-flex justify-content-center align-items-center gap-2 scale-on-hover" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-printer-fill fs-6"></i>
                        <span><?= __('exports') ?? 'Impression & Exports' ?></span>
                        <i class="bi bi-chevron-down small opacity-75 ms-1"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end export-dropdown-menu animate-slide-down">
                        <div class="export-dropdown-header d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-printer me-1 text-primary"></i> <?= __('exports') ?? 'Options d\'impression' ?></span>
                            <span class="badge bg-primary-subtle text-primary rounded-pill extra-small px-2">PDF & Sheets</span>
                        </div>
                        <?php if ($canExportList): ?>
                            <a class="export-dropdown-item" href="<?= htmlspecialchars($listExportUrl) ?>" target="_blank">
                                <div class="export-icon-box bg-danger-subtle text-danger">
                                    <i class="bi bi-file-earmark-pdf-fill"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold small lh-sm"><?= __('grade_list_export') ?? 'Liste globale des notes' ?></span>
                                    <span class="extra-small text-muted"><?= __('export_list_desc') ?? 'Export PDF complet de la classe' ?></span>
                                </div>
                            </a>
                        <?php endif; ?>
                        <?php if ($canExportReport): ?>
                            <a class="export-dropdown-item" href="<?= htmlspecialchars($reportExportUrl) ?>" target="_blank">
                                <div class="export-icon-box bg-primary-subtle text-primary">
                                    <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold small lh-sm"><?= __('grade_report_sheet') ?? 'Fiche de saisie' ?></span>
                                    <span class="extra-small text-muted"><?= __('export_sheet_desc') ?? 'Grille papier pour l\'enseignant' ?></span>
                                </div>
                            </a>
                            <a class="export-dropdown-item" href="<?= htmlspecialchars($reportPdfUrl) ?>" target="_blank">
                                <div class="export-icon-box" style="background-color: rgba(124, 58, 237, 0.12); color: #7c3aed;">
                                    <i class="bi bi-award-fill"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold small lh-sm"><?= __('grade_report_pdf') ?? 'PV d\'évaluation (PDF)' ?></span>
                                    <span class="extra-small text-muted"><?= __('export_pv_desc') ?? 'Procès-verbal officiel par matière' ?></span>
                                </div>
                            </a>
                        <?php endif; ?>
                        <?php if (!$canExportList && !$canExportReport): ?>
                            <div class="p-3 text-center rounded-3 bg-light bg-opacity-50 m-1 border border-dashed">
                                <i class="bi bi-info-circle text-primary fs-5 d-block mb-1"></i>
                                <span class="extra-small text-muted fw-medium d-block">
                                    <?= __('grade_export_hint') ?? 'Sélectionnez une classe et une matière pour débloquer les options d\'impression.' ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (in_array(App\Core\Session::get('user_role'), ['admin', 'superadmin'])): ?>
    <!-- BARRE DE FILTRES ET RECHERCHE INSTANTANÉE RESPONSIVE -->
    <div class="filter-island-container mb-4">
        <div class="filter-island p-3 rounded-4 shadow-sm">
            <form method="GET" action="/notes" class="filter-form w-100 m-0" id="filterForm">
                <div class="d-flex flex-column flex-md-row gap-2 gap-md-3 align-items-center justify-content-between">

                    <div class="row g-2 flex-grow-1 w-100 m-0">
                        <!-- Type Enseignement -->
                        <div class="col-12 col-sm-6 col-lg p-0 px-sm-1">
                            <select name="teaching_type_id" id="filter_teaching_type" class="form-select search-pill rounded-pill bg-white bg-opacity-10 fw-bold text-main py-2 px-3 w-100" onchange="this.form.submit()">
                                <option value=""><?= __('all_teaching_types') ?? 'Tous les types' ?></option>
                                <?php foreach ($teachingTypes as $tt): ?>
                                    <option value="<?= $tt['id'] ?>" <?= (int) $filters['teaching_type_id'] === (int) $tt['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $tt['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Classe -->
                        <div class="col-12 col-sm-6 col-lg p-0 px-sm-1">
                            <select name="class_id" id="filter_class" class="form-select search-pill rounded-pill bg-white bg-opacity-10 fw-bold text-main py-2 px-3 w-100" onchange="this.form.submit()">
                                <option value=""><?= __('all_classes') ?? 'Toutes les classes' ?></option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= $class['id'] ?>" data-teaching-type="<?= $class['teaching_type_id'] ?? '' ?>" <?= (int) $filters['class_id'] === (int) $class['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $class['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Matière -->
                        <div class="col-12 col-sm-6 col-lg p-0 px-sm-1">
                            <select name="subject_id" id="filter_subject" class="form-select search-pill rounded-pill bg-white bg-opacity-10 fw-bold text-main py-2 px-3 w-100" onchange="this.form.submit()">
                                <option value=""><?= __('all_subjects') ?? 'Toutes les matières' ?></option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?= $subject['id'] ?>" data-teaching-type="<?= $subject['teaching_type_id'] ?? '' ?>" <?= (int) $filters['subject_id'] === (int) $subject['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $subject['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Recherche Locale -->
                        <div class="col-12 col-sm-6 col-lg p-0 px-sm-1">
                            <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-3 py-1 w-100">
                                <span class="input-group-text border-0 bg-transparent text-primary p-0 me-2">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" id="subject-search" class="form-control border-0 bg-transparent shadow-none fw-bold text-main"
                                    placeholder="<?= __('search_placeholder_global') ?? 'Rechercher' ?>...">
                            </div>
                        </div>
                    </div>

                    <!-- Actions Filtre -->
                    <div class="d-flex gap-2 align-items-center justify-content-center justify-content-md-end ps-md-2 pt-2 pt-md-0 border-top border-top-md-0 border-opacity-10 border-secondary flex-shrink-0">
                        <a href="/notes" class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center reset-btn scale-on-hover shadow-sm" style="width: 44px; height: 44px;" title="<?= __('reset') ?? 'Réinitialiser' ?>">
                            <i class="bi bi-arrow-counterclockwise fs-5 text-primary"></i>
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>
    <?php else: ?>
    <!-- BARRE DE RECHERCHE ENSEIGNANT RESPONSIVE -->
    <div class="filter-island-container mb-4">
        <div class="filter-island p-3 rounded-4 shadow-sm w-100">
            <form class="filter-form w-100 m-0">
                <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-3 py-1 w-100">
                    <span class="input-group-text border-0 bg-transparent text-primary p-0 me-2">
                        <i class="bi bi-search fs-5"></i>
                    </span>
                    <input type="text" id="subject-search" class="form-control border-0 bg-transparent shadow-none fw-bold text-main"
                        placeholder="<?= __('search') ?? 'Rechercher' ?>..." style="min-height: 38px;">
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content Workspace -->

    <div class="row g-4">

        <!-- Main List Area -->

        <div class="col-lg-9 grades-main-column">

            <div id="grid-container">

                <?php
                    $userRole = \App\Core\Session::get('user_role');
                    $isAdmin = in_array($userRole, ['admin', 'superadmin'], true);
                ?>

                <?php if (empty($dashboard)): ?>

                    <div class="modern-card p-5 text-center border-dashed border-2 rounded-5 glass-card animate-fade-in">

                        <i class="bi bi-stars fs-1 text-warning mb-3 d-block"></i>

                        <h4 class="fw-bold text-main-theme"><?= __('all_grades_filled') ?></h4>

                        <p class="text-muted-theme small mb-0"><?= __('no_pending_assignments') ?></p>

                    </div>

                <?php endif; ?>



                <?php foreach ($dashboard as $class_name => $subjectsRaw): ?>

                    <?php

                        // Récupérer le class_id depuis le premier sujet

                        $class_id = !empty($subjectsRaw) ? $subjectsRaw[0]['class_id'] : 0;

                    ?>

                    <div class="class-section animate-slide-up mb-4" data-class="<?= strtolower($class_name) ?>">

                        <div class="class-header-premium mb-4">

                            <div class="d-flex align-items-center gap-3">

                                <div class="class-header-icon">

                                    <i class="bi bi-door-open-fill"></i>

                                </div>

                                <div>

                                    <div class="d-flex align-items-center gap-2">

                                        <h5 class="m-0 fw-black text-main-theme text-uppercase letter-spacing-2"><?= htmlspecialchars((string) $class_name) ?></h5>

                                    </div>

                                    <div class="small text-muted-theme opacity-50 d-flex align-items-center gap-2 mt-1">

                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-0 extra-small border border-primary border-opacity-10">

                                            <?= count($subjectsRaw) ?> <?= __('subjects') ?>

                                        </span>

                                        <div class="header-line-accent"></div>

                                    </div>

                                </div>

                            </div>

                            <?php if ($isAdmin && $class_id > 0): ?>

                            <button type="button" class="btn btn-outline-success rounded-pill fw-bold shadow-sm text-nowrap flex-shrink-0 grades-import-button" data-bs-toggle="modal" data-bs-target="#gradeImportModal<?= $class_id ?>">

                                <i class="bi bi-upload me-1"></i> <?= __('import_grades') ?>

                            </button>

                            <?php endif; ?>

                        </div>

                        <div class="row g-2 g-md-3">

                            <?php foreach ($subjectsRaw as $sub): ?>

                                <div class="col-12 col-md-4 subject-grid-item"

                                    data-subject-name="<?= strtolower($sub['subject_nom']) ?>">

                                    <?php $gradeEntryModalId = 'gradeEntryModal' . (int) $sub['class_id'] . '_' . (int) $sub['subject_id']; ?>

                                    <div class="subject-card-compact border-theme-dynamic h-100 <?= empty($sub['teacher_nom']) ? 'is-unassigned' : '' ?>">

                                        <div class="subject-card-glow"></div>

                                        <div class="card-body p-2 p-md-3 h-100 position-relative" style="z-index: 1;">

                                            <div class="d-flex flex-column h-100 justify-content-between gap-1">

                                                <div>

                                                    <div class="d-flex align-items-start gap-2 mb-1 text-main-theme">

                                                        <div class="flex-shrink-0 mt-1">

                                                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">

                                                                <i class="bi bi-journal-text text-primary small"></i>

                                                            </div>

                                                        </div>

                                                        <span class="fw-bold lh-sm flex-grow-1" style="font-size: 0.8rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">

                                                            <?= htmlspecialchars((string) $sub['subject_nom']) ?>

                                                        </span>

                                                    </div>

                                                    

                                                    <?php if ($isAdmin && $sub['teacher_nom']): ?>

                                                        <div class="extra-small text-muted-theme fw-normal opacity-75 d-flex align-items-center gap-1 ps-4 ms-1">

                                                            <i class="bi bi-person text-primary opacity-50"></i>

                                                            <span class="text-truncate"><?= htmlspecialchars($sub['teacher_prenom'] . ' ' . $sub['teacher_nom']) ?></span>

                                                        </div>

                                                    <?php elseif (!$sub['teacher_nom']): ?>

                                                        <?php

                                                            $filled = (int) ($sub['filled_count'] ?? 0);

                                                            $total = (int) ($sub['total_count'] ?? 0);

                                                            $isComplete = (bool) ($sub['is_complete'] ?? ($total > 0 && $filled >= $total));

                                                        ?>

                                                        <div class="extra-small text-warning fw-bold d-flex align-items-center gap-1 ps-4 ms-1">

                                                            <i class="bi bi-exclamation-triangle-fill"></i>

                                                            <span><?= __('not_assigned') ?></span>

                                                            <span class="text-muted-theme fw-semibold opacity-75">

                                                                <?= $filled ?>/<?= $total ?>

                                                            </span>

                                                        </div>

                                                    <?php endif; ?>

                                                </div>



                                                <div class="mt-auto pt-2 d-flex justify-content-between align-items-center">

                                                    <?php if (!$sub['teacher_nom']): ?>

                                                        <div class="d-flex gap-2 w-100">

                                                            <?php if ($isAdmin): ?>
                                                                <?php $assignmentModalId = 'assignTeacherModal' . (int) $sub['class_id'] . '_' . (int) $sub['subject_id']; ?>
                                                                <button type="button" class="btn btn-xs btn-outline-warning rounded-pill py-0 px-2 extra-small fw-bold shadow-sm flex-grow-1" data-bs-toggle="modal" data-bs-target="#<?= $assignmentModalId ?>">
                                                                    <i class="bi bi-person-plus-fill me-1"></i><?= __('assign') ?>
                                                                </button>
                                                            <?php else: ?>
                                                                <a href="/teachers?assign_subject=<?= $sub['subject_id'] ?>&assign_class=<?= $sub['class_id'] ?>" class="btn btn-xs btn-outline-warning rounded-pill py-0 px-2 extra-small fw-bold shadow-sm flex-grow-1 text-decoration-none text-center">
                                                                    <i class="bi bi-person-plus-fill me-1"></i><?= __('assign') ?>
                                                                </a>
                                                            <?php endif; ?>

                                                            <?php if ($isAdmin): ?>

                                                            <button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-0 px-2 extra-small fw-bold shadow-sm flex-grow-1" data-bs-toggle="modal" data-bs-target="#<?= $gradeEntryModalId ?>">
                                                                <i class="bi bi-pencil-fill me-1"></i><?= __('enter_grades') ?>
                                                            </button>

                                                            <?php endif; ?>

                                                        </div>

                                                    <?php else: ?>

                                                        <?php

                                                            $filled = (int) ($sub['filled_count'] ?? 0);

                                                            $total = (int) ($sub['total_count'] ?? 0);

                                                            $isComplete = (bool) ($sub['is_complete'] ?? ($total > 0 && $filled >= $total));

                                                        ?>

                                                        <div class="d-flex align-items-center gap-1">

                                                            <?php if ($total <= 0 || $filled <= 0): ?>

                                                                <span class="d-inline-flex align-items-center gap-2">

                                                                    <!-- <span class="btn btn-xs btn-outline-primary rounded-pill py-0 px-2 extra-small fw-bold shadow-sm text-decoration-none text-center" style="pointer-events:none;">

                                                                        <i class="bi bi-plus-circle-fill me-1"></i> Saisir les notes

                                                                    </span> -->

                                                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill extra-small px-1 px-md-2 py-0.5 border border-primary border-opacity-10" style="pointer-events:none;" title="À saisir">

                                                                        <i class="bi bi-award-fill me-1"></i><?=__('to_enter')?>

                                                                    </span>

                                                                </span>

                                                            <?php else: ?>

                                                                <?php

                                                                    $success = $isComplete && $filled >= $total;

                                                                    // Partiel : affichage d'avancement (orange)

                                                                    $badgeClass = $success

                                                                        ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-10'

                                                                        : 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-10';

                                                                    $icon = $success ? 'bi-check-circle-fill' : 'bi-pencil-fill';

                                                                ?>

                                                                <span class="badge <?= $badgeClass ?> rounded-pill extra-small px-1 px-md-2 py-0.5 d-inline-flex align-items-center gap-1" style="pointer-events:none;">

                                                                    <i class="bi <?= $icon ?>"></i>

                                                                    <?= $filled ?>/<?= $total ?><?= $success ? ' ✓' : '' ?>

                                                                </span>

                                                            <?php endif; ?>

                                                            <button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-0 px-2 extra-small fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#<?= $gradeEntryModalId ?>" title="<?= __('enter_grades') ?>">
                                                                <i class="bi bi-pencil-fill"></i>
                                                            </button>

                                                        </div>

                                                    <?php endif; ?>

                                                    <div class="card-arrow-container">

                                                        <i class="bi bi-arrow-right-short text-primary opacity-50"></i>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                    <?php if ($isAdmin || !empty($sub['teacher_nom'])): ?>
                                        <div class="modal fade grade-entry-modal" id="<?= $gradeEntryModalId ?>" tabindex="-1" aria-labelledby="<?= $gradeEntryModalId ?>Label" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-xl">
                                                <div class="modal-content border-0 rounded-4 overflow-hidden">
                                                    <div class="modal-header">
                                                        <div>
                                                            <h5 class="modal-title fw-bold" id="<?= $gradeEntryModalId ?>Label">
                                                                <i class="bi bi-pencil-square text-primary me-2"></i><?= __('enter_grades') ?>
                                                            </h5>
                                                            <p class="small text-muted-theme mb-0"><?= htmlspecialchars((string) $sub['subject_nom']) ?> · <?= htmlspecialchars((string) $class_name) ?></p>
                                                        </div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= __('close') ?>"></button>
                                                    </div>
                                                    <div class="modal-body p-0">
                                                        <div class="grade-entry-loading d-flex flex-column align-items-center justify-content-center gap-3" role="status">
                                                            <div class="spinner-border text-primary" aria-hidden="true"></div>
                                                            <span><?= __('grade_entry_loading') ?></span>
                                                        </div>
                                                        <div class="grade-entry-load-error alert alert-danger m-3 d-none" role="alert">
                                                            <?= __('grade_entry_load_error') ?>
                                                        </div>
                                                        <iframe class="grade-entry-frame w-100 border-0" title="<?= htmlspecialchars(__('enter_grades') . ' - ' . $sub['subject_nom'], ENT_QUOTES) ?>" src="about:blank" data-src="/notes/saisie?class_id=<?= (int) $sub['class_id'] ?>&subject_id=<?= (int) $sub['subject_id'] ?>&entry_modal=1" loading="eager"></iframe>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($isAdmin && !$sub['teacher_nom']): ?>
                                        <?php $assignmentModalId = 'assignTeacherModal' . (int) $sub['class_id'] . '_' . (int) $sub['subject_id']; ?>
                                        <div class="modal fade" id="<?= $assignmentModalId ?>" tabindex="-1" aria-labelledby="<?= $assignmentModalId ?>Label" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content modern-card border-0 rounded-4 overflow-hidden">
                                                    <div class="modal-header border-bottom">
                                                        <div>
                                                            <h5 class="modal-title fw-bold" id="<?= $assignmentModalId ?>Label">
                                                                <i class="bi bi-person-plus-fill text-warning me-2"></i><?= __('assign') ?>
                                                            </h5>
                                                            <p class="small text-muted-theme mb-0">
                                                                <?= htmlspecialchars((string) $sub['subject_nom']) ?> · <?= htmlspecialchars((string) $class_name) ?>
                                                            </p>
                                                        </div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= __('close') ?? 'Fermer' ?>"></button>
                                                    </div>
                                                    <form action="/teachers/direct_assign" method="GET">
                                                        <input type="hidden" name="subject_id" value="<?= (int) $sub['subject_id'] ?>">
                                                        <input type="hidden" name="class_id" value="<?= (int) $sub['class_id'] ?>">
                                                        <input type="hidden" name="return_to" value="notes">
                                                        <div class="modal-body">
                                                            <label for="<?= $assignmentModalId ?>Teacher" class="form-label fw-semibold"><?= __('select_teacher') ?></label>
                                                            <select class="form-select" id="<?= $assignmentModalId ?>Teacher" name="teacher_id" required>
                                                                <option value=""><?= __('select_teacher') ?></option>
                                                                <?php foreach ($assignmentTeachers as $assignmentTeacher): ?>
                                                                    <option value="<?= (int) $assignmentTeacher['id'] ?>">
                                                                        <?= htmlspecialchars(trim($assignmentTeacher['nom'] . ' ' . $assignmentTeacher['prenom'])) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="modal-footer border-top">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                                                            <button type="submit" class="btn btn-warning fw-bold" <?= empty($assignmentTeachers) ? 'disabled' : '' ?>>
                                                                <i class="bi bi-check2-circle me-1"></i><?= __('assign') ?>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                </div>

                            <?php endforeach; ?>

                        </div>

                        <?php if ($isAdmin && $class_id > 0): ?>
                            <?php $importModalId = 'gradeImportModal' . $class_id; ?>
                            <div class="modal fade" id="<?= $importModalId ?>" tabindex="-1" aria-labelledby="<?= $importModalId ?>Label" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content modern-card border-0 rounded-4 overflow-hidden">
                                        <div class="modal-header border-bottom">
                                            <div>
                                                <h5 class="modal-title fw-bold" id="<?= $importModalId ?>Label">
                                                    <i class="bi bi-file-earmark-spreadsheet text-success me-2"></i><?= __('import_grades') ?>
                                                </h5>
                                                <p class="small text-muted-theme mb-0"><?= htmlspecialchars((string) $class_name) ?></p>
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= __('close') ?? 'Fermer' ?>"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <p class="text-muted-theme mb-3"><?= __('import_grades_multi_sheet_desc') ?></p>
                                            <div class="d-flex flex-wrap align-items-end gap-3 mb-4">
                                                <div class="flex-grow-1">
                                                    <label class="form-label fw-semibold" for="gradeImportSubject<?= $class_id ?>"><?= __('subject') ?></label>
                                                    <select id="gradeImportSubject<?= $class_id ?>" class="form-select grade-import-subject">
                                                        <option value="0"><?= __('all_subjects') ?></option>
                                                        <?php foreach ($subjectsRaw as $importSubject): ?>
                                                            <option value="<?= (int) $importSubject['subject_id'] ?>"><?= htmlspecialchars((string) $importSubject['subject_nom']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <a href="/notes/downloadTemplate?class_id=<?= $class_id ?>&subject_id=0" class="btn btn-outline-primary fw-semibold grade-import-template" data-class-id="<?= $class_id ?>">
                                                    <i class="bi bi-download me-2"></i><?= __('download_template') ?>
                                                </a>
                                            </div>
                                            <form action="/notes/upload" method="POST" enctype="multipart/form-data" class="grade-import-form">
                                                <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                                                <input type="hidden" name="class_id" value="<?= $class_id ?>">
                                                <input type="hidden" name="subject_id" value="0" class="grade-import-subject-id">
                                                <label class="form-label fw-semibold" for="gradeImportFile<?= $class_id ?>"><?= __('choose_file') ?></label>
                                                <input type="file" id="gradeImportFile<?= $class_id ?>" name="import_file" class="form-control grade-import-file mb-3" accept=".xlsx" required>
                                                <p class="small text-muted-theme mb-0"><?= __('import_grades_integrity_desc') ?></p>
                                                <div class="modal-footer border-0 px-0 pb-0 mt-3">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                                                    <button type="submit" class="btn btn-success fw-bold grade-import-submit" disabled>
                                                        <i class="bi bi-cloud-upload me-2"></i><?= __('validate_import_final') ?>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            </div>

        </div>



        <!-- Latest Activities Side Column (Admin uniquement) -->

        <?php if (in_array(\App\Core\Session::get('user_role'), ['superadmin', 'admin'])): ?>

        <div class="col-lg-3 grades-activity-column">

            <h5 class="fw-bold text-main-theme mb-3 px-1 d-flex align-items-center gap-2">

                <i class="bi bi-lightning-charge text-warning"></i>

                <?= __('recent_activity') ?>

            </h5>

            <div class="modern-card border-0 shadow-sm glass-card p-0 overflow-hidden grades-activity-card">
                <div class="list-group list-group-flush list-group-theme grades-activity-list">

                    <?php foreach (array_slice($recentGrades, 0, 10) as $grade): ?>

                        <div class="list-group-item bg-transparent p-3 border-0 border-bottom border-theme grades-activity-item">

                            <div class="d-flex justify-content-between align-items-start mb-2">

                                <div class="fw-bold text-main-theme small d-flex align-items-center gap-2">

                                    <i class="bi bi-person-circle text-muted opacity-50"></i>

                                    <?= htmlspecialchars($grade['student_nom'] . ' ' . $grade['student_prenom']) ?>

                                </div>

                                <div class="text-primary fw-black d-flex align-items-center gap-1">

                                    <i class="bi bi-award-fill small"></i>

                                    <?= number_format((float) $grade['valeur'], 1) ?>

                                </div>

                            </div>

                            <div class="d-flex justify-content-between align-items-center small">

                                <span class="text-muted-theme opacity-75 d-flex align-items-center gap-1">

                                    <i class="bi bi-mortarboard small"></i>

                                    <?= htmlspecialchars($grade['class_nom']) ?>

                                </span>

                                <a href="/notes/saisie?class_id=<?= $grade['class_id'] ?>&subject_id=<?= $grade['subject_id'] ?>&periode=<?= urlencode((string) $grade['periode']) ?>"

                                    class="btn-icon-pill p-1 px-2 text-primary bg-primary bg-opacity-10 rounded-pill transition-base">

                                    <i class="bi bi-pencil-square"></i>

                                </a>

                            </div>

                        </div>

                    <?php endforeach; ?>

                    <?php if (empty($recentGrades)): ?>

                        <div class="p-4 text-center text-muted small">

                            <i class="bi bi-inbox fs-4 d-block mb-2 opacity-25"></i>

                            <?= __('no_activity_yet') ?>

                        </div>

                    <?php endif; ?>

                </div>

                <div class="p-3 bg-theme-input text-center grades-activity-footer">

                    <a href="/notes/history" class="small fw-bold text-primary text-decoration-none">

                        <i class="bi bi-arrow-right-circle me-1"></i><?= __('full_history') ?>

                    </a>

                </div>

            </div>

        </div>

        <?php endif; ?>

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



    .filter-island:focus-within {

        border-color: var(--primary-color);

        box-shadow: 0 15px 35px -10px rgba(var(--primary-rgb), 0.25);

        transform: translateY(-2px);

    }



    .search-pill {

        border: 1px solid rgba(var(--primary-rgb), 0.1);

        transition: all 0.3s ease;

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



    .dept-header-card {
        background: var(--bg-card);
        border: 1px solid var(--border-theme);
        overflow: visible !important;
        backdrop-filter: blur(16px);
        transition: all 0.3s ease;
    }

    .dept-header-card.has-open-export-dropdown {
        z-index: 1061;
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

    .grades-workspace {
        --border-theme: rgba(226, 232, 240, 0.5);
    }

    [data-theme="dark"] .grades-workspace {
        --border-theme: rgba(255, 255, 255, 0.05);
    }

    .fw-black { font-weight: 800; }

    .class-header-icon {
        width: 42px;
        height: 42px;
        background: linear-gradient(135deg, var(--primary-color) 0%, color-mix(in srgb, var(--primary-color) 70%, black) 100%);
        color: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        box-shadow: 0 8px 16px rgba(var(--primary-rgb), 0.2);
    }

    .subject-card-compact {
        background: var(--bg-card);
        border-radius: 24px;
        border: 1px solid var(--border-theme) !important;
        display: block;
        text-decoration: none !important;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        position: relative;
        overflow: hidden;
    }

    [data-theme="dark"] .subject-card-compact {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(10px);
        border-color: rgba(255, 255, 255, 0.08) !important;
    }

    .subject-card-glow {
        position: absolute;
        bottom: 0;
        left: 0;
        z-index: 0;
        width: 85%;
        height: 80%;
        background: radial-gradient(ellipse at bottom left, rgba(124, 58, 237, 0.3), transparent 70%);
        opacity: 1;
        pointer-events: none;
        transition: opacity 0.4s ease;
    }

    .subject-card-compact:hover {
        transform: translateY(-8px) scale(1.02);
        border-color: var(--primary-color) !important;
        box-shadow: 0 20px 40px rgba(var(--primary-rgb), 0.12);
    }

    .subject-card-compact:hover .subject-card-glow {
        opacity: 1;
    }

    .grades-workspace {
        --grades-accent: #7c3aed;
        --grades-border: rgba(124, 58, 237, 0.2);
        --grades-surface-muted: rgba(124, 58, 237, 0.035);
    }

    .grades-workspace .filter-island {
        width: 100%;
        max-width: none !important;
        border-radius: 14px;
        border-color: var(--border-color, #e2e8f0);
        box-shadow: 0 3px 12px rgba(15, 23, 42, 0.035);
    }

    .grades-workspace .filter-island-container,
    .grades-workspace .filter-form {
        width: 100%;
        max-width: none !important;
    }

    .grades-workspace .search-pill,
    .grades-workspace .filter-form select {
        min-height: 42px;
        border-radius: 8px !important;
    }

    .grades-workspace .class-section {
        padding: 1rem;
        border: 1px solid var(--border-color, #e2e8f0);
        border-radius: 14px;
        background: var(--bg-card, #fff);
    }

    .grades-workspace .class-header-premium {
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 54px;
        padding: 0 0 0.85rem;
        border-bottom: 1px solid var(--grades-border);
    }

    .grades-workspace .class-header-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        box-shadow: none;
        font-size: 1rem;
    }

    .grades-workspace .class-header-premium h5 {
        font-size: 0.98rem;
        letter-spacing: 0.025em;
    }

    .grades-workspace .class-header-premium .grades-import-button {
        min-height: 44px;
        padding: 0.55rem 1rem !important;
        border-style: solid !important;
        font-size: 1.05rem !important;
        line-height: 1.2;
    }

    .grades-workspace .class-header-premium .grades-import-button:hover,
    .grades-workspace .class-header-premium .grades-import-button:focus-visible {
        border-style: solid !important;
    }

    .grades-workspace .class-header-premium .header-line-accent {
        display: none;
    }

    .grade-entry-modal .modal-dialog {
        max-width: min(1200px, 96vw);
    }

    .grade-entry-frame {
        display: none;
        height: min(78vh, 900px);
        min-height: 520px;
        background: var(--bg-body, #f8fafc);
    }

    .grade-entry-frame.is-ready {
        display: block;
    }

    .grade-entry-loading {
        min-height: min(78vh, 900px);
        color: var(--text-main);
        background: var(--bg-card);
    }

    .grade-entry-load-error {
        min-height: 120px;
        align-content: center;
    }

    .grades-workspace .class-section > .row {
        --bs-gutter-y: 0.8rem;
        --bs-gutter-x: 0.8rem;
    }

    .grades-workspace .subject-grid-item {
        display: flex;
    }

    .grades-workspace .subject-card-compact {
        display: flex;
        width: 100%;
        min-height: 150px;
        flex-direction: column;
        border: 1px solid var(--grades-border) !important;
        border-radius: 11px !important;
        background: var(--bg-card, #fff) !important;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.035) !important;
        color: var(--text-main);
        transition: border-color 0.16s ease, background-color 0.16s ease, box-shadow 0.16s ease !important;
    }

    .grades-workspace .subject-card-compact:hover {
        transform: translateY(-2px) !important;
        border: 1px solid var(--grades-accent) !important;
        border-style: solid !important;
        box-shadow: 0 8px 18px rgba(124, 58, 237, 0.09) !important;
    }

    .grades-workspace .subject-card-compact.is-unassigned {
        background: linear-gradient(135deg, var(--bg-card, #fff), var(--grades-surface-muted)) !important;
    }

    .grades-workspace .subject-card-compact .card-body {
        flex: 1;
        padding: 0.9rem !important;
    }

    .grades-workspace .subject-card-compact .card-body > .d-flex {
        gap: 0.6rem !important;
    }

    .grades-workspace .subject-card-compact .rounded-circle {
        width: 30px !important;
        height: 30px !important;
        flex: 0 0 30px;
        margin-top: 0 !important;
        border-radius: 8px !important;
    }

    .grades-workspace .subject-card-compact .rounded-circle i {
        font-size: 0.85rem !important;
    }

    .grades-workspace .subject-card-compact .fw-bold.lh-sm {
        min-height: 2.3em;
        font-size: 0.86rem !important;
        line-height: 1.35 !important;
    }

    .grades-workspace .subject-card-compact .extra-small {
        font-size: 0.75rem;
        line-height: 1.35;
    }

    .grades-workspace .subject-card-compact .mt-auto {
        padding-top: 0.75rem !important;
        border-top: 1px solid var(--grades-border);
    }

    .grades-workspace .subject-card-compact .btn-xs {
        min-height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.3rem 0.55rem !important;
        border-radius: 7px !important;
        font-size: 0.78rem !important;
        line-height: 1.2;
        box-shadow: none !important;
    }

    .grades-workspace .subject-card-compact .btn-outline-primary {
        border-color: #7c3aed !important;
        background-color: #7c3aed !important;
        color: #fff !important;
    }

    .grades-workspace .subject-card-compact .btn-outline-primary:hover,
    .grades-workspace .subject-card-compact .btn-outline-primary:focus-visible {
        border-color: #6d28d9 !important;
        background-color: #6d28d9 !important;
        color: #fff !important;
    }

    .grades-workspace .subject-card-compact .card-arrow-container {
        display: none;
    }

    .grades-workspace .grades-activity-column {
        position: sticky;
        top: 1rem;
    }

    .grades-workspace .grades-activity-column > h5 {
        min-height: 38px;
        margin-bottom: 0.6rem !important;
        font-size: 0.9rem;
    }

    .grades-workspace .grades-activity-card {
        border: 1px solid var(--border-color, #e2e8f0) !important;
        border-radius: 14px !important;
        background: var(--bg-card, #fff) !important;
        box-shadow: 0 3px 12px rgba(15, 23, 42, 0.035) !important;
    }

    .grades-workspace .grades-activity-list {
        max-height: min(66vh, 620px);
        overflow-y: auto;
    }

    .grades-workspace .grades-activity-item {
        padding: 0.8rem !important;
        border-bottom: 1px solid var(--border-color, #e2e8f0) !important;
    }

    .grades-workspace .grades-activity-item:last-child {
        border-bottom: 0 !important;
    }

    .grades-workspace .grades-activity-item .fw-bold {
        min-width: 0;
        line-height: 1.35;
    }

    .grades-workspace .grades-activity-footer {
        border-top: 1px solid var(--border-color, #e2e8f0);
    }

    [data-theme="dark"] .grades-workspace .class-section,
    [data-theme="dark"] .grades-workspace .subject-card-compact,
    [data-theme="dark"] .grades-workspace .grades-activity-card {
        border-color: rgba(139, 92, 246, 0.28) !important;
        background-color: #0b1a30 !important;
    }

    [data-theme="dark"] .grades-workspace .subject-card-compact.is-unassigned {
        background: linear-gradient(135deg, #0b1a30, rgba(124, 58, 237, 0.1)) !important;
    }

    [data-theme="dark"] .grades-workspace .class-header-premium {
        border-bottom-color: rgba(139, 92, 246, 0.25);
    }

    [data-theme="dark"] .grades-workspace .subject-card-compact .mt-auto,
    [data-theme="dark"] .grades-workspace .grades-activity-item,
    [data-theme="dark"] .grades-workspace .grades-activity-footer {
        border-color: rgba(139, 92, 246, 0.2) !important;
    }

    [data-theme="dark"] .grades-workspace .subject-card-compact:hover {
        border-color: #a78bfa !important;
        background-color: #102342 !important;
    }

    @media (max-width: 991.98px) {
        .grades-workspace .grades-activity-column {
            position: static;
        }

        .grades-workspace .grades-activity-list {
            max-height: 420px;
        }
    }

    @media (max-width: 575.98px) {
        .grades-workspace {
            padding-right: 0.65rem !important;
            padding-left: 0.65rem !important;
        }

        .grades-workspace .class-section {
            padding: 0.75rem;
            border-radius: 12px;
        }

        .grades-workspace .class-header-premium {
            margin-bottom: 0.75rem !important;
        }

        .grades-workspace .class-header-icon {
            width: 34px;
            height: 34px;
        }

        .grades-workspace .subject-card-compact {
            min-height: 138px;
        }

        .grades-workspace .subject-card-compact .card-body {
            padding: 0.75rem !important;
        }

        .grades-workspace .grades-activity-column {
            margin-top: 0.25rem;
        }

        .grade-entry-frame {
            height: 76vh;
            min-height: 420px;
        }

        .grade-entry-loading {
            min-height: 76vh;
        }
    }

    @media (max-width: 991.98px) {
        .filter-island {
            border-radius: 24px;
            padding: 1rem !important;
        }
    }
</style>



<script>

    document.addEventListener('DOMContentLoaded', function () {

        const exportDropdown = document.querySelector('.dept-header-card .dropdown');
        const pageHeader = document.querySelector('.dept-header-card');
        if (exportDropdown && pageHeader) {
            exportDropdown.addEventListener('show.bs.dropdown', function () {
                pageHeader.classList.add('has-open-export-dropdown');
            });
            exportDropdown.addEventListener('hidden.bs.dropdown', function () {
                pageHeader.classList.remove('has-open-export-dropdown');
            });
        }

        const searchInput = document.getElementById('subject-search');

        const sections = document.querySelectorAll('.class-section');
        const workspace = document.querySelector('.grades-workspace');
        const currentUrl = new URL(window.location.href);
        if (currentUrl.searchParams.has('grade_entry_complete')) {
            if (typeof AlertService !== 'undefined' && workspace) {
                AlertService.toast('success', workspace.dataset.gradeEntryCompleteMessage);
            }
            currentUrl.searchParams.delete('grade_entry_complete');
            window.history.replaceState({}, '', currentUrl);
        }



        if (searchInput) {

            searchInput.addEventListener('input', function () {

                const query = this.value.toLowerCase().trim();

                sections.forEach(section => {

                    const className = section.getAttribute('data-class');

                    const items = section.querySelectorAll('.subject-grid-item');

                    let visibleCount = 0;



                    items.forEach(item => {

                        const subjectName = item.getAttribute('data-subject-name');

                        const match = subjectName.includes(query) || className.includes(query);

                        item.style.display = match ? 'block' : 'none';

                        if (match) visibleCount++;

                    });



                    section.style.display = visibleCount > 0 ? 'block' : 'none';

                });

            });

        }

        function syncGradeEntryFrameTheme(frameDocument) {
            if (!frameDocument) return;
            const theme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            frameDocument.documentElement.setAttribute('data-theme', theme);
            try {
                frameDocument.defaultView.localStorage.setItem('theme', theme);
            } catch (error) {
                console.error('Unable to synchronize grade-entry theme:', error);
            }
        }

        const gradeEntryThemeObserver = new MutationObserver(function () {
            document.querySelectorAll('.grade-entry-frame').forEach(function (frame) {
                try {
                    if (frame.contentDocument && frame.contentWindow.location.pathname !== 'about:blank') {
                        syncGradeEntryFrameTheme(frame.contentDocument);
                    }
                } catch (error) {
                    console.error('Unable to update grade-entry theme:', error);
                }
            });
        });
        gradeEntryThemeObserver.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-theme']
        });

        document.querySelectorAll('.grade-entry-modal').forEach(function (modalElement) {
            const iframe = modalElement.querySelector('.grade-entry-frame');
            const loadingIndicator = modalElement.querySelector('.grade-entry-loading');
            const loadError = modalElement.querySelector('.grade-entry-load-error');
            if (!iframe) return;

            iframe.addEventListener('load', function () {
                try {
                    const framePath = iframe.contentWindow.location.pathname;
                    if (framePath === 'about:blank') return;

                    if (framePath === '/notes') {
                        bootstrap.Modal.getOrCreateInstance(modalElement).hide();
                        const frameUrl = new URL(iframe.contentWindow.location.href);
                        if (frameUrl.searchParams.get('grade_entry_complete') === '1') {
                            const returnUrl = new URL(window.location.href);
                            returnUrl.searchParams.set('grade_entry_complete', '1');
                            window.location.href = returnUrl.href;
                        }
                        return;
                    }

                    const frameDocument = iframe.contentDocument;
                    if (!frameDocument) {
                        throw new Error('The grade-entry page returned an empty document.');
                    }

                    syncGradeEntryFrameTheme(frameDocument);

                    if (!frameDocument.getElementById('grade-entry-embed-styles')) {
                        const style = frameDocument.createElement('style');
                        style.id = 'grade-entry-embed-styles';
                        style.textContent = `
                            .desktop-navbar-container, .mobile-ribbon-drawer, .page-context-bar,
                            .footer, #onboardingCard, .command-palette-overlay { display: none !important; }
                            .dashboard-wrapper { display: block !important; height: 100vh !important; min-height: 0 !important; overflow: hidden !important; }
                            .main-area { width: 100% !important; height: 100vh !important; max-height: 100vh !important; margin: 0 !important; overflow-y: auto !important; }
                            .content-inner { width: 100% !important; max-width: none !important; min-height: 0 !important; margin: 0 !important; padding: 0 !important; }
                            .grade-saisie-page { padding: 1rem 1rem 5rem !important; }
                            .floating-save { position: sticky !important; inset: auto !important; display: block !important; width: fit-content !important; margin: 1rem auto !important; }
                            body { min-width: 0 !important; overflow: auto !important; }
                        `;
                        frameDocument.head.appendChild(style);
                    }

                    if (!frameDocument.getElementById('gradeEntryForm')) {
                        throw new Error('The grade-entry form is missing from the loaded page.');
                    }

                    iframe.classList.add('is-ready');
                    if (loadingIndicator) loadingIndicator.classList.add('d-none');
                    if (loadError) loadError.classList.add('d-none');
                } catch (error) {
                    console.error('Unable to prepare grade-entry modal content:', error);
                    iframe.classList.remove('is-ready');
                    if (loadingIndicator) loadingIndicator.classList.add('d-none');
                    if (loadError) loadError.classList.remove('d-none');
                }
            });

            modalElement.addEventListener('shown.bs.modal', function () {
                if (loadError) loadError.classList.add('d-none');
                let framePath = 'about:blank';
                try {
                    framePath = iframe.contentWindow.location.pathname;
                } catch (error) {
                    console.error('Unable to inspect grade-entry modal:', error);
                }

                if (framePath !== '/notes/saisie') {
                    iframe.classList.remove('is-ready');
                    if (loadingIndicator) loadingIndicator.classList.remove('d-none');
                    iframe.src = iframe.dataset.src;
                    return;
                }

                try {
                    if (iframe.contentDocument && iframe.contentWindow.location.pathname !== 'about:blank') {
                        syncGradeEntryFrameTheme(iframe.contentDocument);
                    }
                } catch (error) {
                    console.error('Unable to restore grade-entry modal:', error);
                }
            });
        });

        document.querySelectorAll('.grade-import-subject').forEach(function (subjectSelect) {
            const modal = subjectSelect.closest('.modal');
            const subjectIdInput = modal ? modal.querySelector('.grade-import-subject-id') : null;
            const templateLink = modal ? modal.querySelector('.grade-import-template') : null;
            const fileInput = modal ? modal.querySelector('.grade-import-file') : null;
            const submitButton = modal ? modal.querySelector('.grade-import-submit') : null;

            subjectSelect.addEventListener('change', function () {
                if (subjectIdInput) subjectIdInput.value = subjectSelect.value;
                if (templateLink) {
                    templateLink.href = '/notes/downloadTemplate?class_id='
                        + encodeURIComponent(templateLink.dataset.classId)
                        + '&subject_id=' + encodeURIComponent(subjectSelect.value);
                }
            });

            if (fileInput && submitButton) {
                fileInput.addEventListener('change', function () {
                    submitButton.disabled = fileInput.files.length === 0;
                });
            }
        });

    });

</script>





<?php

$content = ob_get_clean();

include __DIR__ . '/../templates/layout.php';

?>
