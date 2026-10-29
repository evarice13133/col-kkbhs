<?php
$title = __('competencies_and_assignments');
ob_start();
?>
<div class="container-fluid py-4" id="competency-management">
    <style>
        #competency-management .nav-tabs { gap: .5rem; border: 0; }
        #competency-management .nav-tabs .nav-link { border: 1px solid transparent; border-radius: .75rem; color: var(--bs-secondary-color); padding: .75rem 1rem; font-weight: 600; }
        #competency-management .nav-tabs .nav-link:hover { background: var(--bs-tertiary-bg); }
        #competency-management .nav-tabs .nav-link.active { color: var(--bs-primary); background: var(--bs-primary-bg-subtle); border-color: var(--bs-primary-border-subtle); }
        #competency-management .teacher-panel { background: var(--bg-card, var(--bs-body-bg)); border: 1px solid var(--border-color, var(--bs-border-color)); border-radius: 1rem; box-shadow: 0 .25rem 1rem rgba(16, 24, 40, .08); color: var(--text-main, var(--bs-body-color)); }
        #competency-management .teacher-panel h2 { color: var(--text-main, var(--bs-heading-color)); }
        #competency-management .teacher-panel .text-muted,
        #competency-management .teacher-panel .form-text { color: var(--text-muted, var(--bs-secondary-color)) !important; }
        #competency-management .teacher-panel .form-label { color: var(--text-main, var(--bs-body-color)); }
        #teacher-assignment-import-modal .teacher-import-modal-content { background: var(--bg-card, var(--bs-body-bg)); color: var(--text-main, var(--bs-body-color)); border: 1px solid var(--border-color, var(--bs-border-color)); border-radius: 1rem; }
        #teacher-assignment-import-modal .teacher-import-modal-content .modal-header,
        #teacher-assignment-import-modal .teacher-import-modal-content .modal-footer { border-color: var(--border-color, var(--bs-border-color)); }
        #teacher-assignment-import-modal .teacher-import-status-icon { display: grid; width: 3.5rem; height: 3.5rem; margin: 0 auto 1rem; place-items: center; border-radius: 50%; background: #eff6ff; color: #2563eb; font-size: 1.5rem; }
        #teacher-assignment-import-modal .teacher-import-status-icon.is-success { background: #ecfdf5; color: #059669; }
        #teacher-assignment-import-modal .teacher-import-status-icon.is-error { background: #fef2f2; color: #dc2626; }
        #teacher-assignment-import-modal .teacher-import-summary { white-space: pre-line; }
        [data-theme="dark"] #competency-management .teacher-panel { box-shadow: 0 .25rem 1rem rgba(0, 0, 0, .35); }
        [data-theme="dark"] #teacher-assignment-import-modal .teacher-import-modal-content .modal-title { color: var(--text-main) !important; }
        [data-theme="dark"] #teacher-assignment-import-modal .teacher-import-modal-content .text-muted { color: var(--text-muted) !important; }
        [data-theme="dark"] #teacher-assignment-import-modal .teacher-import-modal-content .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
        [data-theme="dark"] #competency-management .badge.text-bg-light { background-color: var(--border-color) !important; color: var(--text-main) !important; }
        #competency-management .teacher-panel-heading { display: flex; align-items: flex-start; gap: .85rem; }
        #competency-management .teacher-step-icon { display: grid; flex: 0 0 2.5rem; width: 2.5rem; height: 2.5rem; place-items: center; border-radius: .75rem; color: var(--bs-primary); background: var(--bs-primary-bg-subtle); font-size: 1.15rem; }
        #competency-management .teacher-subject-item { height: 100%; }
        #competency-management .teacher-subject-option { min-height: 3.25rem; height: 100%; margin: 0; padding: .75rem; border: 1px solid var(--bs-border-color); border-radius: .7rem; cursor: pointer; transition: border-color .15s ease, background-color .15s ease, box-shadow .15s ease; }
        #competency-management .teacher-subject-option:hover { border-color: var(--bs-primary); background: var(--bs-primary-bg-subtle); }
        #competency-management .teacher-subject-option:has(.teacher-subject:checked) { border-color: var(--bs-primary); background: var(--bs-primary-bg-subtle); box-shadow: inset 0 0 0 1px var(--bs-primary); }
        #competency-management .teacher-subject-name { min-width: 0; overflow-wrap: anywhere; }
        #competency-management .teacher-subject-conflict { max-width: 45%; }
        #competency-management .teacher-subject-results { max-height: 34rem; overflow: auto; padding: .15rem; }
        @media (max-width: 575.98px) {
            #competency-management .nav-tabs { flex-wrap: nowrap; overflow-x: auto; }
            #competency-management .nav-tabs .nav-link { white-space: nowrap; }
            #competency-management .teacher-subject-conflict { max-width: 55%; }
        }
    </style>
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h1 class="page-title mb-1"><?= __('competencies_and_assignments') ?></h1>
            <p class="text-muted mb-0"><?= __('competencies_and_assignments_intro') ?></p>
        </div>
        <a href="/notes" class="btn btn-outline-primary"><i class="bi bi-pencil-square me-2"></i> <?= __('competencies_notes_entry') ?></a>
    </div>

    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#competencies-pane" type="button"><i class="bi bi-list-check me-2"></i><?= __('competencies_tab') ?></button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#assignments-pane" type="button"><i class="bi bi-link-45deg me-2"></i><?= __('group_subjects_assignment') ?></button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#teacher-assignments-pane" type="button"><i class="bi bi-person-workspace me-2"></i><?= __('teacher_subjects_assignment') ?></button></li>
    </ul>

    <div class="tab-content">
        <section class="tab-pane fade show active" id="competencies-pane">
            <div class="row g-3 align-items-end mb-4">
                <div class="col-md-5"><label class="form-label fw-semibold" for="class-filter"><?= __('concerned_class') ?></label><select id="class-filter" class="form-select"><option value=""><?= __('all_classes') ?></option><?php foreach ($classes as $class): ?><option value="<?= (int) $class['id'] ?>"><?= h($class['nom']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-5"><label class="form-label fw-semibold" for="subject-filter"><?= __('select_subject') ?></label><select id="subject-filter" class="form-select"><option value=""><?= __('all_subjects') ?></option><?php foreach ($allSubjects as $subject): ?><option value="<?= (int) $subject['id'] ?>" data-group="<?= (int) ($subject['subject_group_id'] ?? 0) ?>"><?= h($subject['nom']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-2"><button class="btn btn-primary w-100" id="add-competency"><i class="bi bi-plus-lg me-1"></i><?= __('add_competency') ?></button></div>
            </div>
            <div id="competency-context" class="alert alert-info d-none"></div>
            <div id="competency-list" class="row g-3"><div class="col-12 text-muted"><?= __('select_subject_to_view_competencies') ?></div></div>
        </section>

        <section class="tab-pane fade" id="assignments-pane">
            <div id="assignments" class="row g-4">
                <div class="col-lg-4"><div class="border rounded-3 p-3 h-100"><h2 class="h5"><?= __('competencies_group_step') ?></h2><label class="form-label" for="assignment-class-filter"><?= __('assignment_class_label') ?></label><select id="assignment-class-filter" class="form-select mb-3"><option value=""><?= __('competencies_all_classes') ?></option><?php foreach ($classes as $class): ?><option value="<?= (int) $class['id'] ?>"><?= h($class['nom']) ?></option><?php endforeach; ?></select><label class="form-label" for="teaching-type-filter"><?= __('active_teaching_type') ?></label><select id="teaching-type-filter" class="form-select mb-3"><option value=""><?= __('competencies_active_types') ?></option><?php foreach ($teachingTypes as $type): ?><option value="<?= (int) $type['id'] ?>"><?= h($type['nom']) ?><?= !empty($type['code']) ? ' (' . h($type['code']) . ')' : '' ?></option><?php endforeach; ?></select><label class="form-label" for="teaching-form-filter"><?= __('active_teaching_form') ?></label><select id="teaching-form-filter" class="form-select mb-3" disabled><option value=""><?= __('competencies_active_forms') ?></option><?php foreach ($teachingForms as $form): ?><option value="<?= (int) $form['id'] ?>" data-type="<?= (int) $form['teaching_type_id'] ?>"><?= h($form['nom']) ?></option><?php endforeach; ?></select><label class="form-label" for="group-select"><?= __('select_group') ?></label><select id="group-select" class="form-select"><option value=""><?= __('competencies_select_placeholder') ?></option><?php foreach ($groups as $group): $groupFormLabel = ''; if (!empty($group['teaching_form_id'])) { foreach ($teachingForms as $form) { if ((int)$form['id'] === (int)$group['teaching_form_id']) { $groupFormLabel = ' [' . h($form['nom']) . ']'; break; } } } ?><option value="<?= (int) $group['id'] ?>" data-type="<?= (int) ($group['teaching_type_id'] ?? 0) ?>" data-form="<?= (int) ($group['teaching_form_id'] ?? 0) ?>"><?= h($group['libelle']) ?><?= $groupFormLabel ?></option><?php endforeach; ?></select><p class="small text-muted mt-3 mb-0"><?= __('competencies_group_hint') ?></p></div></div>
                <div class="col-lg-8"><div class="border rounded-3 p-3"><div class="d-flex justify-content-between align-items-center gap-2 mb-3"><h2 class="h5 mb-0"><?= __('competencies_subjects_step') ?></h2><input id="subject-search" class="form-control form-control-sm" style="max-width:240px" placeholder="<?= __('competencies_search_placeholder') ?>"></div><div id="assignment-list" class="row g-2"></div><div class="d-flex justify-content-between align-items-center mt-3"><span id="assignment-count" class="small text-muted"><?= __('competencies_select_group_subjects') ?></span><button id="preview-assignment" class="btn btn-primary" disabled title="<?= __('competencies_select_group_subjects') ?>"><i class="bi bi-check2-circle me-1"></i><?= __('validate_assignment') ?></button></div></div></div>
            </div>
        </section>

        <section class="tab-pane fade" id="teacher-assignments-pane">
            <div class="d-grid gap-3">
                <section class="teacher-panel p-3 p-lg-4" aria-labelledby="teacher-step-title">
                    <div class="teacher-panel-heading mb-3">
                        <span class="teacher-step-icon" aria-hidden="true"><i class="bi bi-person-workspace"></i></span>
                        <div>
                            <h2 class="h5 mb-1" id="teacher-step-title"><?= __('competencies_teacher_context_title') ?></h2>
                            <p class="small text-muted mb-0"><?= __('competencies_teacher_context_intro') ?></p>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label fw-semibold" for="teacher-assignment-class"><?= __('assignment_class_label') ?></label><select id="teacher-assignment-class" class="form-select"><option value=""><?= __('select_class_placeholder') ?></option><?php foreach ($classes as $class): ?><option value="<?= (int) $class['id'] ?>"><?= h($class['nom']) ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-4"><label class="form-label fw-semibold" for="teacher-assignment-type"><?= __('teaching_type') ?></label><select id="teacher-assignment-type" class="form-select"><option value=""><?= __('select_teaching_type') ?></option><?php foreach ($teachingTypes as $type): ?><option value="<?= (int) $type['id'] ?>"><?= h($type['nom']) ?><?= !empty($type['code']) ? ' (' . h($type['code']) . ')' : '' ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-4"><label class="form-label fw-semibold" for="teacher-assignment-teacher"><?= __('teacher') ?></label><select id="teacher-assignment-teacher" class="form-select" disabled><option value=""><?= __('competencies_teacher_placeholder') ?></option></select></div>
                    </div>
                </section>

                <section class="teacher-panel p-3 p-lg-4" aria-labelledby="teacher-excel-title">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                        <div class="teacher-panel-heading">
                            <span class="teacher-step-icon text-success bg-success-subtle" aria-hidden="true"><i class="bi bi-file-earmark-spreadsheet"></i></span>
                            <div>
                                <h2 class="h5 mb-1" id="teacher-excel-title"><?= __('competencies_excel_title') ?></h2>
                                <p class="small text-muted mb-0"><?= __('competencies_excel_intro') ?></p>
                            </div>
                        </div>
                        <button type="button" id="export-teacher-assignments" class="btn btn-outline-success">
                            <i class="bi bi-download me-1"></i> <?= __('competencies_download_template') ?>
                        </button>
                    </div>
                    <form id="import-teacher-assignments-form" class="row g-2 align-items-end" enctype="multipart/form-data" data-ajax>
                        <input type="hidden" name="csrf_token" value="<?= h(\App\Core\Session::generateCsrfToken()) ?>">
                        <div class="col-lg-8">
                            <label class="form-label fw-semibold" for="teacher-assignment-file"><?= __('competencies_excel_file') ?></label>
                            <input type="file" id="teacher-assignment-file" name="import_file" class="form-control" accept=".xlsx" aria-label="<?= __('competencies_file_aria') ?>" required>
                            <div id="teacher-assignment-file-name" class="form-text"><?= __('competencies_no_file') ?></div>
                        </div>
                        <div class="col-lg-4 d-grid">
                            <button type="submit" id="import-teacher-assignments" class="btn btn-primary" disabled>
                                <i class="bi bi-upload me-1"></i> <?= __('competencies_import') ?>
                            </button>
                        </div>
                    </form>
                    <div class="small text-muted mt-3"><i class="bi bi-info-circle me-1"></i><?= __('competencies_excel_hint') ?></div>
                </section>

                <div id="teacher-assignment-notice" class="alert alert-info d-none mb-0" role="status" aria-live="polite"></div>

                <section class="teacher-panel p-3 p-lg-4" aria-labelledby="teacher-subjects-title">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                        <div class="teacher-panel-heading">
                            <span class="teacher-step-icon" aria-hidden="true"><i class="bi bi-journal-check"></i></span>
                            <div>
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <h2 class="h5 mb-0" id="teacher-subjects-title"><?= __('competencies_teacher_subjects') ?></h2>
                                    <span id="teacher-subject-total" class="badge text-bg-light"><?= __('competencies_pending') ?></span>
                                </div>
                                <p class="small text-muted mb-0 mt-1"><?= __('competencies_teacher_subjects_intro') ?></p>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" id="select-visible-teacher-subjects" class="btn btn-sm btn-outline-secondary" disabled><?= __('competencies_select_visible') ?></button>
                            <button type="button" id="clear-teacher-subjects" class="btn btn-sm btn-outline-secondary" disabled><?= __('competencies_clear_all') ?></button>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text" aria-hidden="true"><i class="bi bi-search"></i></span>
                        <input type="search" id="teacher-subject-search" class="form-control" placeholder="<?= __('competencies_search_subject') ?>" aria-label="<?= __('competencies_search_subject') ?>" disabled>
                    </div>
                    <div id="teacher-subject-list" class="row g-2 teacher-subject-results">
                        <div class="col-12 text-center text-muted py-4"><i class="bi bi-hand-index-thumb d-block fs-3 mb-2"></i><?= __('competencies_choose_context') ?></div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-top mt-3 pt-3">
                        <span id="teacher-assignment-count" class="small text-muted" aria-live="polite">0 matière sélectionnée</span>
                        <button id="submit-teacher-assignment" class="btn btn-primary" disabled><i class="bi bi-person-check me-1"></i> <?= __('competencies_analyze_assignment') ?></button>
                    </div>
                </section>
            </div>
        </section>
    </div>
</div>

<div class="modal fade" id="teacher-assignment-import-modal" tabindex="-1" aria-labelledby="teacher-assignment-import-title" aria-describedby="teacher-assignment-import-message" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content teacher-import-modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5 mb-0" id="teacher-assignment-import-title"></h2>
                <button type="button" class="btn-close d-none" id="teacher-assignment-import-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div class="teacher-import-status-icon" id="teacher-assignment-import-icon" aria-hidden="true">
                    <span class="spinner-border" id="teacher-assignment-import-spinner"></span>
                    <i class="bi d-none" id="teacher-assignment-import-status-symbol"></i>
                </div>
                <p class="fw-semibold mb-2" id="teacher-assignment-import-message" role="status" aria-live="polite"></p>
                <p class="small text-muted mb-0" id="teacher-assignment-import-detail"></p>
                <div class="teacher-import-summary small mt-3" id="teacher-assignment-import-summary"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary d-none" id="teacher-assignment-import-done" data-bs-dismiss="modal"></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="competency-modal" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><form class="modal-content no-loader" id="competency-form" data-ajax="true"><div class="modal-header"><h2 class="modal-title h5" id="competency-modal-title"><?= __('add_competency') ?></h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= __('close') ?>"></button></div><div class="modal-body"><input type="hidden" id="competency-id"><input type="hidden" id="competency-subject-id"><label class="form-label" for="competency-label"><?= __('competency_label') ?></label><input class="form-control mb-3" id="competency-label" required maxlength="255"><label class="form-label" for="competency-description"><?= __('description') ?></label><textarea class="form-control" id="competency-description" rows="3"></textarea><div id="competency-form-error" class="alert alert-danger d-none mt-3 mb-0"></div></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= __('cancel') ?></button><button class="btn btn-primary" type="submit"><?= __('save') ?></button></div></form></div></div>
<div class="modal fade" id="impact-modal" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h2 class="modal-title h5" id="impact-title"><?= __('impact_analysis') ?></h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= __('close') ?>"></button></div><div class="modal-body" id="impact-body"></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= __('cancel') ?></button><button type="button" class="btn btn-danger d-none" id="confirm-delete"><?= __('delete') ?></button><button type="button" class="btn btn-primary d-none" id="confirm-assignment"><i class="bi bi-check2-circle me-1"></i><?= __('confirm_permanently') ?></button><button type="button" class="btn btn-primary d-none" id="confirm-teacher-assignment"><i class="bi bi-person-check me-1"></i><?= __('confirm_replacement') ?></button><button type="button" class="btn btn-primary d-none" id="confirm-create"><?= __('confirm_creation') ?></button></div></div></div></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    ['teaching-type-filter', 'teacher-assignment-type'].forEach(id => {
        const select = document.getElementById(id);
        const firstActiveType = select?.querySelector('option:not([value=""])');
        if (firstActiveType) select.value = firstActiveType.value;
    });

    const translations = <?= json_encode([
        'Saisie des notes' => __('notes_entry'),
        'Grade entry' => __('notes_entry'),
        '1. Choisir le groupe' => __('choose_group_step'),
        '1. Choose the group' => __('choose_group_step'),
        '2. Sélectionner les matières' => __('select_subjects_step'),
        '2. Select subjects' => __('select_subjects_step'),
        'Classe concernée' => __('assignment_class_label'),
        'Class concerned' => __('assignment_class_label'),
        'Type d\'enseignement actif' => __('active_teaching_type'),
        'Active teaching type' => __('active_teaching_type'),
        'Forme d\'enseignement active' => __('active_teaching_form'),
        'Active teaching form' => __('active_teaching_form'),
        'Tous les types actifs' => __('all_active_teaching_types'),
        'All active types' => __('all_active_teaching_types'),
        'Toutes les formes actives' => __('all_active_teaching_forms'),
        'All active forms' => __('all_active_teaching_forms'),
        'Toutes les matières' => __('all_subjects'),
        'All subjects' => __('all_subjects'),
        'Toutes les classes' => __('all_classes'),
        'All classes' => __('all_classes'),
        'Sélectionner une matière affectée...' => __('assigned_subject_placeholder'),
        'Select an assigned subject...' => __('assigned_subject_placeholder'),
        'Groupe de matières' => __('select_group'),
        'Choose group' => __('select_group'),
        'Sélectionner...' => __('select_group_placeholder'),
        'Select...' => __('select_group_placeholder'),
        'Rechercher...' => __('search_subjects'),
        'Search...' => __('search_subjects'),
        'Sélectionnez un groupe et au moins une matière' => __('select_group_and_subjects'),
        'Select a group and at least one subject' => __('select_group_and_subjects'),
        'Valider l\'affectation' => __('validate_assignment'),
        'Validate assignment' => __('validate_assignment'),
        'Analyser puis valider définitivement l\'affectation' => __('assignment_analyze_validate'),
        'Analyze and permanently validate the assignment' => __('assignment_analyze_validate'),
        'Enseignant' => __('teacher'),
        'Teacher' => __('teacher'),
        'Sélectionner...' => __('select_group_placeholder'),
        'Choisir d\'abord la classe et le type...' => __('select_teacher_first'),
        'Choose the class and type first...' => __('select_teacher_first'),
        'Charger' => __('load'),
        'Load' => __('load'),
        'Choisissez une classe, un type et un enseignant.' => __('choose_class_type_teacher'),
        'Choose a class, type and teacher.' => __('choose_class_type_teacher'),
        'Ajouter une compétence' => __('add_competency'),
        'Add a competency' => __('add_competency'),
        'Libellé' => __('competency_label'),
        'Label' => __('competency_label'),
        'Description' => __('description'),
        'Annuler' => __('cancel'),
        'Cancel' => __('cancel'),
        'Enregistrer' => __('save'),
        'Save' => __('save'),
        'Analyse d\'impact' => __('impact_analysis'),
        'Impact analysis' => __('impact_analysis'),
        'Supprimer' => __('delete'),
        'Delete' => __('delete'),
        'Valider définitivement' => __('confirm_permanently'),
        'Confirm permanently' => __('confirm_permanently'),
        'Confirmer le remplacement' => __('confirm_replacement'),
        'Confirm replacement' => __('confirm_replacement'),
        'Confirmer la création' => __('confirm_creation'),
        'Confirm creation' => __('confirm_creation'),
        'Chargement...' => __('loading'),
        'Loading...' => __('loading'),
        'Sélectionner un enseignant...' => __('select_teacher'),
        'Select a teacher...' => __('select_teacher'),
        'Aucune description' => __('no_description'),
        'No description' => __('no_description'),
        'Sélectionnez une matière pour afficher ses compétences.' => __('select_subject_to_view_competencies'),
        'Select a subject to view its competencies.' => __('select_subject_to_view_competencies'),
        'Aucune matière ne correspond aux filtres actifs.' => __('no_subject_matches_filters'),
        'No subject matches the active filters.' => __('no_subject_matches_filters'),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const dynamicTranslations = <?= json_encode([
        'subjectContext' => __('subject_context'),
        'classContext' => __('class_context'),
        'impactDeletion' => __('impact_deletion'),
        'teacherCount' => __('teacher_assignment_count'),
        'teacherCountPlural' => __('teacher_assignment_count_plural'),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const translateText = value => {
        const trimmed = value.trim();
        if (translations[trimmed]) return value.replace(trimmed, translations[trimmed]);
        let match = trimmed.match(/^(?:Matière|Subject) : (.+?)(?: \| (?:Classe|Class) : (.+))?$/);
        if (match) {
            const classContext = match[2] ? dynamicTranslations.classContext.replace(':class', match[2]) : '';
            return value.replace(trimmed, dynamicTranslations.subjectContext.replace(':subject', match[1]).replace(':class_context', classContext));
        }
        match = trimmed.match(/^(?:Impact de la suppression|Deletion impact):?\s*(.+)$/);
        if (match) return value.replace(trimmed, dynamicTranslations.impactDeletion.replace(':label', match[1]));
        match = trimmed.match(/^(\d+) matière sélectionnée$/);
        if (match) return value.replace(trimmed, dynamicTranslations.teacherCount.replace(':count', match[1]));
        match = trimmed.match(/^(\d+) matières sélectionnées$/);
        if (match) return value.replace(trimmed, dynamicTranslations.teacherCountPlural.replace(':count', match[1]));
        return value;
    };
    const translatePage = root => {
        const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
        const nodes = [];
        while (walker.nextNode()) nodes.push(walker.currentNode);
        nodes.forEach(node => {
            if (!node.parentElement.closest('script, style')) node.nodeValue = translateText(node.nodeValue);
        });
        root.querySelectorAll?.('input[placeholder], [title], [aria-label]').forEach(element => {
            ['placeholder', 'title', 'aria-label'].forEach(attribute => {
                if (element.hasAttribute(attribute)) element.setAttribute(attribute, translateText(element.getAttribute(attribute)));
            });
        });
    };
    translatePage(document.getElementById('competency-management'));
    const translationObserver = new window.MutationObserver(() => translatePage(document.getElementById('competency-management')));
    translationObserver.observe(document.getElementById('competency-management'), {childList: true, subtree: true});

    const subjects = <?= json_encode($allSubjects, JSON_UNESCAPED_UNICODE) ?>;
    const subjectClassMap = <?= json_encode($subjectClassMap, JSON_UNESCAPED_UNICODE) ?>;
    const classFilter = document.getElementById('class-filter');
    const subjectFilter = document.getElementById('subject-filter');
    const list = document.getElementById('competency-list');
    const modal = new bootstrap.Modal(document.getElementById('competency-modal'));
    const impactModal = new bootstrap.Modal(document.getElementById('impact-modal'));
    let assignmentSubjects = subjects;
    let pendingDelete = 0;
    let pendingAssignment = [];
    let pendingCreate = null;

    const esc = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
    const post = (url, data) => fetch(url, {method: 'POST', body: data}).then(response => response.json());
    const selectedSubject = () => Number(subjectFilter.value || 0);
    function renderSubjectOptions() {
        const currentValue = subjectFilter.value;
        const classId = Number(classFilter.value || 0);
        const allowedIds = classId ? new Set((subjectClassMap[classId] || []).map(Number)) : null;
        subjectFilter.innerHTML = '<option value="">' + (classId ? 'Sélectionner une matière affectée...' : 'Toutes les matières') + '</option>';
        subjects.filter(item => !allowedIds || allowedIds.has(Number(item.id))).forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.nom;
            option.dataset.group = item.subject_group_id || 0;
            subjectFilter.appendChild(option);
        });
        if ([...subjectFilter.options].some(option => option.value === currentValue)) subjectFilter.value = currentValue;
        else subjectFilter.value = '';
    }

    async function loadCompetencies() {
        const subjectId = selectedSubject();
        if (!subjectId) { list.innerHTML = '<div class="col-12 text-muted">Sélectionnez une matière pour afficher ses compétences.</div>'; return; }
        const classId = Number(classFilter.value || 0);
        const response = await fetch(`/competencies/api/by-subject?subject_id=${subjectId}&class_id=${classId}`);
        const data = await response.json();
        if (!data.success) { list.innerHTML = `<div class="col-12 alert alert-danger">${esc(data.error)}</div>`; return; }
        document.getElementById('competency-context').classList.remove('d-none');
        document.getElementById('competency-context').textContent = `Matière : ${subjectFilter.selectedOptions[0].text} ${classId ? ` | Classe : ${classFilter.selectedOptions[0].text}` : ''}`;
        list.innerHTML = data.competencies.length ? data.competencies.map(item => `<div class="col-md-6 col-xl-4"><article class="border rounded-3 p-3 h-100"><div class="d-flex justify-content-between gap-2"><h2 class="h6 mb-2">${esc(item.libelle)}</h2><div class="text-nowrap"><button class="btn btn-sm btn-outline-secondary edit-competency" data-id="${item.id}" data-label="${esc(item.libelle)}" data-description="${esc(item.description)}" title="Modifier"><i class="bi bi-pencil"></i></button> <button class="btn btn-sm btn-outline-danger delete-competency" data-id="${item.id}" data-label="${esc(item.libelle)}" title="Analyser et supprimer"><i class="bi bi-trash"></i></button></div></div><p class="small text-muted mb-0">${esc(item.description || 'Aucune description')}</p></article></div>`).join('') : '<div class="col-12"><div class="border rounded-3 p-4 text-center text-muted">Aucune compétence définie pour cette matière.</div></div>';
    }

    function openCreate() { document.getElementById('competency-modal-title').textContent = 'Ajouter une compétence'; document.getElementById('competency-id').value = ''; document.getElementById('competency-subject-id').value = selectedSubject(); document.getElementById('competency-label').value = ''; document.getElementById('competency-description').value = ''; modal.show(); }
    document.getElementById('add-competency').addEventListener('click', () => selectedSubject() ? openCreate() : alert('Sélectionnez d’abord une matière.'));
    subjectFilter.addEventListener('change', loadCompetencies); classFilter.addEventListener('change', () => { renderSubjectOptions(); loadCompetencies(); });
    list.addEventListener('click', async event => {
        const edit = event.target.closest('.edit-competency'); const remove = event.target.closest('.delete-competency');
        if (edit) { document.getElementById('competency-modal-title').textContent = 'Modifier une compétence'; document.getElementById('competency-id').value = edit.dataset.id; document.getElementById('competency-subject-id').value = selectedSubject(); document.getElementById('competency-label').value = edit.dataset.label; document.getElementById('competency-description').value = edit.dataset.description; modal.show(); }
        if (remove) { pendingDelete = Number(remove.dataset.id); document.getElementById('impact-title').textContent = `Impact de la suppression : ${remove.dataset.label}`; document.getElementById('impact-body').innerHTML = '<div class="text-muted">Analyse en cours...</div>'; document.getElementById('confirm-delete').classList.add('d-none'); document.getElementById('confirm-assignment').classList.add('d-none'); document.getElementById('confirm-create').classList.add('d-none'); impactModal.show(); const response = await fetch(`/api/impact-analysis?type=competency&id=${pendingDelete}`); const data = await response.json(); document.getElementById('impact-body').innerHTML = data.error ? `<div class="alert alert-danger">${esc(data.message || data.error)}</div>` : `<p>${esc(data.impact_summary?.direct_deletion || 'Cette compétence sera supprimée.')}</p><p>${esc(data.impact_summary?.dependencies || 'Aucune dépendance détectée.')}</p>`; if (data.can_direct_delete !== false) document.getElementById('confirm-delete').classList.remove('d-none'); }
    });
    document.getElementById('competency-form').addEventListener('submit', async event => { event.preventDefault(); const data = new FormData(); data.append('subject_id', document.getElementById('competency-subject-id').value); data.append('libelle', document.getElementById('competency-label').value); data.append('description', document.getElementById('competency-description').value); const id = document.getElementById('competency-id').value; if (id) { data.append('competency_id', id); const response = await post('/competencies/api/update', data); if (!response.success) return alert(response.error); modal.hide(); loadCompetencies(); return; } pendingCreate = data; modal.hide(); document.getElementById('impact-title').textContent = 'Impact de la création'; document.getElementById('impact-body').innerHTML = `<p>La compétence <strong>${esc(data.get('libelle'))}</strong> sera ajoutée à la matière sélectionnée.</p><p class="text-muted mb-0">Elle sera disponible pour les évaluations de cette matière, sans modifier les notes existantes.</p>`; document.getElementById('confirm-delete').classList.add('d-none'); document.getElementById('confirm-assignment').classList.add('d-none'); document.getElementById('confirm-create').classList.remove('d-none'); impactModal.show(); });
    document.getElementById('confirm-delete').addEventListener('click', async () => { const data = new FormData(); data.append('competency_id', pendingDelete); const result = await post('/competencies/api/delete', data); if (!result.success) return alert(result.error); impactModal.hide(); loadCompetencies(); });
    document.getElementById('confirm-create').addEventListener('click', async () => { const result = await post('/competencies/api/create', pendingCreate); if (!result.success) return alert(result.error); pendingCreate = null; impactModal.hide(); loadCompetencies(); });

    const assignmentList = document.getElementById('assignment-list'); const search = document.getElementById('subject-search'); const group = document.getElementById('group-select'); const preview = document.getElementById('preview-assignment');
    const teachingType = document.getElementById('teaching-type-filter'); const teachingForm = document.getElementById('teaching-form-filter'); const assignmentClass = document.getElementById('assignment-class-filter');
    function filterTeachingGroups() {
        const typeId = teachingType.value;
        const formOptions = [...teachingForm.options].filter(option => option.value);
        formOptions.forEach(option => {
            const visible = !typeId || option.dataset.type === typeId;
            option.hidden = !visible;
        });

        teachingForm.disabled = !typeId || formOptions.every(option => option.hidden);
        if (teachingForm.disabled) teachingForm.value = '';

        [...group.options].forEach(option => {
            if (!option.value) return;
            const matchType = !typeId || option.dataset.type === typeId;
            option.hidden = !matchType;
        });

        if (group.selectedOptions[0]?.hidden) group.value = '';
        renderAssignments();
        refreshAssignmentState();
    }
    function renderAssignments() {
        const query = search.value.toLowerCase();
        const typeId = teachingType.value;
        const classId = assignmentClass.value;
        const classSubjects = classId ? new Set((subjectClassMap[classId] || []).map(Number)) : null;
        const filtered = assignmentSubjects.filter(item =>
            item.nom.toLowerCase().includes(query) &&
            (!classSubjects || classSubjects.has(Number(item.id))) &&
            (!typeId || String(item.group_type_id || '') === typeId)
        );
        assignmentList.innerHTML = filtered.map(item => `<div class="col-md-6"><label class="border rounded-2 p-2 d-flex gap-2 align-items-center"><input type="checkbox" class="assignment-subject" value="${item.id}"><span>${esc(item.nom)}</span><small class="ms-auto text-muted">${esc(item.group_nom || 'Sans groupe')}</small></label></div>`).join('') || '<div class="col-12 text-muted">Aucune matière ne correspond aux filtres actifs.</div>';
    }
    function refreshAssignmentState() { const count = document.querySelectorAll('.assignment-subject:checked').length; document.getElementById('assignment-count').textContent = count ? `${count} matière${count > 1 ? 's' : ''} sélectionnée${count > 1 ? 's' : ''}` : 'Sélectionnez un groupe et au moins une matière'; preview.disabled = !group.value || !count; preview.title = preview.disabled ? 'Sélectionnez un groupe et au moins une matière' : 'Analyser puis valider définitivement l’affectation'; }
    search.addEventListener('input', renderAssignments); assignmentClass.addEventListener('change', renderAssignments); teachingType.addEventListener('change', filterTeachingGroups); teachingForm.addEventListener('change', filterTeachingGroups); group.addEventListener('change', refreshAssignmentState); assignmentList.addEventListener('change', refreshAssignmentState); renderAssignments(); renderSubjectOptions(); filterTeachingGroups();
    preview.addEventListener('click', async () => { pendingAssignment = [...document.querySelectorAll('.assignment-subject:checked')].map(input => Number(input.value)); const data = new FormData(); pendingAssignment.forEach(id => data.append('subject_ids[]', id)); data.append('group_id', group.value); const result = await post('/competencies/api/assignment-impact', data); document.getElementById('impact-title').textContent = 'Impact de l’affectation'; document.getElementById('impact-body').innerHTML = `<p>${esc(result.message || result.error)}</p>${(result.items || []).map(item => `<div class="small border-top py-2"><strong>${esc(item.nom)}</strong> <span class="text-muted">(${esc(item.group_nom || 'Sans groupe')})</span></div>`).join('')}`; document.getElementById('confirm-delete').classList.add('d-none'); document.getElementById('confirm-create').classList.add('d-none'); document.getElementById('confirm-assignment').classList.toggle('d-none', !result.success); impactModal.show(); });
    document.getElementById('confirm-assignment').addEventListener('click', async () => { const data = new FormData(); pendingAssignment.forEach(id => data.append('subject_ids[]', id)); data.append('group_id', group.value); const result = await post('/competencies/api/assign-subjects', data); if (!result.success) return alert(result.error); const targetGroup = group.selectedOptions[0]; impactModal.hide(); assignmentSubjects = assignmentSubjects.map(item => pendingAssignment.includes(Number(item.id)) ? {...item, subject_group_id: Number(group.value), group_nom: targetGroup.text, group_type_id: targetGroup.dataset.type || item.group_type_id, group_form_id: targetGroup.dataset.form || item.group_form_id} : item); renderAssignments(); refreshAssignmentState(); alert(`${result.updated} matière(s) affectée(s).`); });

    const teacherClass = document.getElementById('teacher-assignment-class'); const teacherType = document.getElementById('teacher-assignment-type'); const teacherSelect = document.getElementById('teacher-assignment-teacher'); const teacherList = document.getElementById('teacher-subject-list'); const submitTeacherAssignment = document.getElementById('submit-teacher-assignment'); const exportTeacherAssignments = document.getElementById('export-teacher-assignments'); const importTeacherAssignmentsForm = document.getElementById('import-teacher-assignments-form'); const teacherAssignmentFile = document.getElementById('teacher-assignment-file'); const importTeacherAssignmentsButton = document.getElementById('import-teacher-assignments'); const teacherAssignmentFileName = document.getElementById('teacher-assignment-file-name'); const teacherSubjectSearch = document.getElementById('teacher-subject-search'); const teacherSubjectTotal = document.getElementById('teacher-subject-total'); const selectVisibleTeacherSubjects = document.getElementById('select-visible-teacher-subjects'); const clearTeacherSubjects = document.getElementById('clear-teacher-subjects');
    const teacherImportModalElement = document.getElementById('teacher-assignment-import-modal');
    const teacherImportModal = new bootstrap.Modal(teacherImportModalElement, {backdrop: 'static', keyboard: false});
    const teacherImportModalTitle = document.getElementById('teacher-assignment-import-title');
    const teacherImportModalMessage = document.getElementById('teacher-assignment-import-message');
    const teacherImportModalDetail = document.getElementById('teacher-assignment-import-detail');
    const teacherImportModalSummary = document.getElementById('teacher-assignment-import-summary');
    const teacherImportModalIcon = document.getElementById('teacher-assignment-import-icon');
    const teacherImportModalSpinner = document.getElementById('teacher-assignment-import-spinner');
    const teacherImportModalSymbol = document.getElementById('teacher-assignment-import-status-symbol');
    const teacherImportModalClose = document.getElementById('teacher-assignment-import-close');
    const teacherImportModalDone = document.getElementById('teacher-assignment-import-done');
    let teacherSubjects = []; let pendingTeacherAssignment = null;
    let teacherImportInProgress = false;
    const teacherImportMessages = {
        fr: {
            button: 'Importer les affectations',
            fileSelected: file => `${file.name} · ${(file.size / (1024 * 1024)).toFixed(2)} Mo`,
            noFile: 'Aucun fichier sélectionné · maximum 10 Mo',
            loadingTitle: 'Importation des affectations',
            loadingMessage: 'Traitement en cours…',
            loadingDetail: 'Veuillez patienter. Ne fermez pas cette fenêtre.',
            successTitle: 'Import terminé',
            successMessage: 'Les affectations ont été importées avec succès.',
            errorTitle: 'Échec de l’importation',
            errorMessage: 'Les affectations n’ont pas été importées.',
            invalidResponse: 'Le serveur a retourné une réponse invalide.',
            classes: count => `${count} classe(s) traitée(s)`,
            counts: result => `${result.created} affectation(s) créée(s) · ${result.updated} modifiée(s) · ${result.unchanged} inchangée(s) · ${result.skipped} ligne(s) vide(s) ignorée(s)`,
            done: 'Terminé',
            close: 'Fermer'
        },
        en: {
            button: 'Import assignments',
            fileSelected: file => `${file.name} · ${(file.size / (1024 * 1024)).toFixed(2)} MB`,
            noFile: 'No file selected · 10 MB maximum',
            loadingTitle: 'Importing assignments',
            loadingMessage: 'Processing…',
            loadingDetail: 'Please wait. Do not close this window.',
            successTitle: 'Import complete',
            successMessage: 'The assignments were imported successfully.',
            errorTitle: 'Import failed',
            errorMessage: 'The assignments could not be imported.',
            invalidResponse: 'The server returned an invalid response.',
            classes: count => `${count} class(es) processed`,
            counts: result => `${result.created} assignment(s) created · ${result.updated} updated · ${result.unchanged} unchanged · ${result.skipped} blank row(s) skipped`,
            done: 'Done',
            close: 'Close'
        }
    };
    const teacherImportText = () => teacherImportMessages[
        (document.documentElement.lang || window.NM_I18N?.lang || 'fr').toLowerCase().startsWith('en') ? 'en' : 'fr'
    ];
    function setTeacherImportModalState(state, result = null, error = '') {
        const messages = teacherImportText();
        const loading = state === 'loading';
        const success = state === 'success';
        teacherImportModalTitle.textContent = loading ? messages.loadingTitle : success ? messages.successTitle : messages.errorTitle;
        teacherImportModalMessage.textContent = loading ? messages.loadingMessage : success ? messages.successMessage : messages.errorMessage;
        teacherImportModalDetail.textContent = loading ? messages.loadingDetail : success ? messages.classes(result.classes) : error || messages.invalidResponse;
        teacherImportModalSummary.textContent = success ? messages.counts(result) : '';
        teacherImportModalSpinner.classList.toggle('d-none', !loading);
        teacherImportModalSymbol.classList.toggle('d-none', loading);
        teacherImportModalSymbol.className = `bi ${success ? 'bi-check-lg' : 'bi-exclamation-lg'}${loading ? ' d-none' : ''}`;
        teacherImportModalIcon.classList.toggle('is-success', success);
        teacherImportModalIcon.classList.toggle('is-error', !loading && !success);
        teacherImportModalClose.classList.toggle('d-none', loading);
        teacherImportModalClose.setAttribute('aria-label', messages.close);
        teacherImportModalDone.textContent = messages.done;
        teacherImportModalDone.classList.toggle('d-none', loading);
    }
    function refreshTeacherExcelActions() {
        const file = teacherAssignmentFile.files[0];
        importTeacherAssignmentsButton.disabled = !file || teacherImportInProgress;
        const messages = teacherImportText();
        teacherAssignmentFileName.textContent = file ? messages.fileSelected(file) : messages.noFile;
    }
    exportTeacherAssignments.addEventListener('click', () => { window.location.href = '/competencies/teacher-assignments/export'; });
    teacherAssignmentFile.addEventListener('change', refreshTeacherExcelActions);
    importTeacherAssignmentsForm.addEventListener('submit', async event => {
        event.preventDefault();
        if (importTeacherAssignmentsButton.disabled || !teacherAssignmentFile.files.length) return;
        teacherImportInProgress = true;
        refreshTeacherExcelActions();
        const messages = teacherImportText();
        importTeacherAssignmentsButton.innerHTML = `<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>${messages.loadingMessage}`;
        setTeacherImportModalState('loading');
        teacherImportModal.show();
        const formData = new FormData(importTeacherAssignmentsForm);
        try {
            const response = await fetch('/competencies/teacher-assignments/import', {method: 'POST', body: formData});
            const result = await response.json().catch(() => null);
            if (!result) throw new Error(messages.invalidResponse);
            if (!response.ok || !result.success) throw new Error(result.error || messages.errorMessage);
            setTeacherImportModalState('success', result);
            if (teacherClass.value && teacherType.value) await loadTeacherData();
        } catch (error) {
            setTeacherImportModalState('error', null, error.message || messages.errorMessage);
        } finally {
            teacherImportInProgress = false;
            importTeacherAssignmentsButton.innerHTML = `<i class="bi bi-upload me-1"></i> ${messages.button}`;
            refreshTeacherExcelActions();
        }
    });
    async function loadTeacherData() {
        teacherSelect.disabled = true;
        teacherSelect.innerHTML = '<option value="">Chargement des enseignants...</option>';
        teacherSubjects = [];
        teacherSubjectSearch.value = '';
        if (!teacherClass.value || !teacherType.value) {
            teacherSelect.innerHTML = '<option value="">Choisissez d’abord la classe et le type...</option>';
            teacherList.innerHTML = '<div class="col-12 text-center text-muted py-4"><i class="bi bi-hand-index-thumb d-block fs-3 mb-2"></i>Choisissez une classe et un type pour commencer.</div>';
            refreshTeacherState();
            return;
        }
        teacherList.innerHTML = '<div class="col-12 text-center text-muted py-4"><span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Chargement des matières...</div>';
        try {
            const response = await fetch(`/competencies/api/teacher-assignment-data?class_id=${teacherClass.value}&teaching_type_id=${teacherType.value}`);
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.error || 'Impossible de charger les affectations.');
            teacherSelect.innerHTML = '<option value="">Sélectionner un enseignant...</option>' + data.teachers.map(item => `<option value="${item.id}">${esc(`${item.prenom || ''} ${item.nom || ''}`.trim())}</option>`).join('');
            teacherSelect.disabled = !data.teachers.length;
            teacherSubjects = data.subjects || [];
            renderTeacherSubjects();
        } catch (error) {
            teacherSelect.innerHTML = `<option value="">${esc(error.message || 'Erreur de chargement')}</option>`;
            teacherList.innerHTML = `<div class="col-12"><div class="alert alert-danger mb-0" role="alert">${esc(error.message || 'Impossible de charger les affectations.')}</div></div>`;
            refreshTeacherState();
        }
    }
    function renderTeacherSubjects() {
        const selectedTeacher = Number(teacherSelect.value || 0);
        teacherList.innerHTML = teacherSubjects.map(item => {
            const hasAssignedTeacher = Boolean(item.assigned_teacher_id);
            const conflict = hasAssignedTeacher && selectedTeacher > 0 && Number(item.assigned_teacher_id) !== selectedTeacher;
            const assignedToSelected = hasAssignedTeacher && selectedTeacher > 0 && Number(item.assigned_teacher_id) === selectedTeacher;
            const searchText = `${item.nom} ${conflict ? item.assigned_teacher_name || '' : ''}`.toLocaleLowerCase();
            return `<div class="col-sm-6 col-xl-4 teacher-subject-item" data-search="${esc(searchText)}"><label class="teacher-subject-option d-flex gap-2 align-items-center ${conflict ? 'border-warning bg-warning bg-opacity-10' : ''}"><input type="checkbox" class="form-check-input teacher-subject flex-shrink-0" value="${item.id}" ${assignedToSelected ? 'checked' : ''}><span class="teacher-subject-name flex-grow-1">${esc(item.nom)}</span>${conflict ? `<small class="teacher-subject-conflict badge text-bg-warning-subtle text-warning-emphasis" title="Déjà affectée à cet enseignant">Déjà affectée : ${esc((item.assigned_teacher_name || '').trim())}</small>` : ''}</label></div>`;
        }).join('') || '<div class="col-12 text-center text-muted py-4"><i class="bi bi-journal-x d-block fs-3 mb-2"></i>Aucune matière active pour cette classe et ce type.</div>';
        teacherList.insertAdjacentHTML('beforeend', '<div id="teacher-subject-no-results" class="col-12 text-center text-muted py-4 d-none"><i class="bi bi-search d-block fs-3 mb-2"></i>Aucune matière ne correspond à cette recherche.</div>');
        teacherSubjectSearch.disabled = !teacherSubjects.length;
        applyTeacherSubjectSearch();
        refreshTeacherState();
    }
    function applyTeacherSubjectSearch() {
        const query = teacherSubjectSearch.value.trim().toLocaleLowerCase();
        const items = [...document.querySelectorAll('#teacher-subject-list .teacher-subject-item')];
        items.forEach(item => {
            item.classList.toggle('d-none', !item.dataset.search.includes(query));
        });
        const noResults = document.getElementById('teacher-subject-no-results');
        if (noResults) noResults.classList.toggle('d-none', !teacherSubjects.length || items.some(item => !item.classList.contains('d-none')));
        refreshTeacherState();
    }
    function refreshTeacherState() {
        const checkboxes = [...document.querySelectorAll('#teacher-subject-list .teacher-subject')];
        const selectedCount = checkboxes.filter(input => input.checked).length;
        const visibleCount = checkboxes.filter(input => !input.closest('.teacher-subject-item').classList.contains('d-none')).length;
        document.getElementById('teacher-assignment-count').textContent = `${selectedCount} matière${selectedCount > 1 ? 's' : ''} sélectionnée${selectedCount > 1 ? 's' : ''}`;
        teacherSubjectTotal.textContent = teacherSubjects.length ? `${teacherSubjects.length} matière${teacherSubjects.length > 1 ? 's' : ''}` : 'En attente';
        selectVisibleTeacherSubjects.disabled = visibleCount === 0;
        clearTeacherSubjects.disabled = selectedCount === 0;
        submitTeacherAssignment.disabled = !teacherSelect.value || !selectedCount;
    }
    teacherSubjectSearch.addEventListener('input', applyTeacherSubjectSearch);
    selectVisibleTeacherSubjects.addEventListener('click', () => {
        document.querySelectorAll('#teacher-subject-list .teacher-subject-item:not(.d-none) .teacher-subject').forEach(input => { input.checked = true; });
        refreshTeacherState();
    });
    clearTeacherSubjects.addEventListener('click', () => {
        document.querySelectorAll('#teacher-subject-list .teacher-subject:checked').forEach(input => { input.checked = false; });
        refreshTeacherState();
    });
    teacherClass.addEventListener('change', loadTeacherData); teacherType.addEventListener('change', loadTeacherData); teacherSelect.addEventListener('change', renderTeacherSubjects); teacherList.addEventListener('change', refreshTeacherState);
    submitTeacherAssignment.addEventListener('click', async () => { const subjectIds = [...document.querySelectorAll('.teacher-subject:checked')].map(input => Number(input.value)); const data = new FormData(); subjectIds.forEach(id => data.append('subject_ids[]', id)); data.append('teacher_id', teacherSelect.value); data.append('class_id', teacherClass.value); const result = await post('/competencies/api/teacher-assignment-impact', data); pendingTeacherAssignment = {teacherId: teacherSelect.value, classId: teacherClass.value, subjectIds}; document.getElementById('impact-title').textContent = 'Radiographie d’impact : affectation enseignant'; document.getElementById('impact-body').innerHTML = result.has_conflicts ? `<div class="alert alert-danger"><strong>Remplacement d’affectation</strong><br>${esc(result.message)}</div>${result.conflicts.map(item => `<div class="border-top py-2"><strong>${esc(item.nom)}</strong><br><span class="text-danger">Ancien enseignant : ${esc((item.teacher_name || '').trim())}</span></div>`).join('')}` : `<div class="alert alert-success">${esc(result.message)}</div><p>Les matières seront affectées à l’enseignant sélectionné pour la classe choisie.</p>`; document.getElementById('confirm-delete').classList.add('d-none'); document.getElementById('confirm-create').classList.add('d-none'); document.getElementById('confirm-assignment').classList.add('d-none'); document.getElementById('confirm-teacher-assignment').classList.remove('d-none'); impactModal.show(); });
    document.getElementById('confirm-teacher-assignment').addEventListener('click', async () => { const data = new FormData(); pendingTeacherAssignment.subjectIds.forEach(id => data.append('subject_ids[]', id)); data.append('teacher_id', pendingTeacherAssignment.teacherId); data.append('class_id', pendingTeacherAssignment.classId); const result = await post('/competencies/api/assign-teacher-subjects', data); if (!result.success) return alert(result.error); impactModal.hide(); const notice = document.getElementById('teacher-assignment-notice'); notice.className = 'alert alert-success'; notice.textContent = `${result.assigned} matière(s) affectée(s) définitivement à l’enseignant sélectionné.`; notice.classList.remove('d-none'); loadTeacherData(); });
    if (window.location.hash === '#teacher-assignments') { const teacherTab = document.querySelector('[data-bs-target="#teacher-assignments-pane"]'); if (teacherTab) new bootstrap.Tab(teacherTab).show(); }
});
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
