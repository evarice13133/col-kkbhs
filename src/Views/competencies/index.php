<?php
$title = __('competencies_and_assignments');
ob_start();
?>
<div class="container-fluid py-4" id="competency-management">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h1 class="page-title mb-1"><?= __('competencies_and_assignments') ?></h1>
            <p class="text-muted mb-0"><?= __('competencies_and_assignments_intro') ?></p>
        </div>
        <a href="/notes" class="btn btn-outline-primary"><i class="bi bi-pencil-square me-2"></i> Saisie des notes</a>
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
                <div class="col-lg-4"><div class="border rounded-3 p-3 h-100"><h2 class="h5">1. Choisir le groupe</h2><label class="form-label" for="assignment-class-filter">Classe concernée</label><select id="assignment-class-filter" class="form-select mb-3"><option value="">Toutes les classes</option><?php foreach ($classes as $class): ?><option value="<?= (int) $class['id'] ?>"><?= h($class['nom']) ?></option><?php endforeach; ?></select><label class="form-label" for="teaching-type-filter">Type d'enseignement actif</label><select id="teaching-type-filter" class="form-select mb-3"><option value="">Tous les types actifs</option><?php foreach ($teachingTypes as $type): ?><option value="<?= (int) $type['id'] ?>"><?= h($type['nom']) ?><?= !empty($type['code']) ? ' (' . h($type['code']) . ')' : '' ?></option><?php endforeach; ?></select><label class="form-label" for="teaching-form-filter">Forme d'enseignement active</label><select id="teaching-form-filter" class="form-select mb-3" disabled><option value="">Toutes les formes actives</option><?php foreach ($teachingForms as $form): ?><option value="<?= (int) $form['id'] ?>" data-type="<?= (int) $form['teaching_type_id'] ?>"><?= h($form['nom']) ?></option><?php endforeach; ?></select><label class="form-label" for="group-select">Groupe de matières</label><select id="group-select" class="form-select"><option value="">Sélectionner...</option><?php foreach ($groups as $group): $groupFormLabel = ''; if (!empty($group['teaching_form_id'])) { foreach ($teachingForms as $form) { if ((int)$form['id'] === (int)$group['teaching_form_id']) { $groupFormLabel = ' [' . h($form['nom']) . ']'; break; } } } ?><option value="<?= (int) $group['id'] ?>" data-type="<?= (int) ($group['teaching_type_id'] ?? 0) ?>" data-form="<?= (int) ($group['teaching_form_id'] ?? 0) ?>"><?= h($group['libelle']) ?><?= $groupFormLabel ?></option><?php endforeach; ?></select><p class="small text-muted mt-3 mb-0">Les matières affichées sont enregistrées dans la classe choisie et correspondent au type d’enseignement actif. Les formes peuvent différer sans empêcher l’affectation indépendante.</p></div></div>
                <div class="col-lg-8"><div class="border rounded-3 p-3"><div class="d-flex justify-content-between align-items-center gap-2 mb-3"><h2 class="h5 mb-0">2. Sélectionner les matières</h2><input id="subject-search" class="form-control form-control-sm" style="max-width:240px" placeholder="Rechercher..."></div><div id="assignment-list" class="row g-2"></div><div class="d-flex justify-content-between align-items-center mt-3"><span id="assignment-count" class="small text-muted">Sélectionnez un groupe et au moins une matière</span><button id="preview-assignment" class="btn btn-primary" disabled title="Sélectionnez un groupe et au moins une matière"><i class="bi bi-check2-circle me-1"></i>Valider l'affectation</button></div></div></div>
            </div>
        </section>

        <section class="tab-pane fade" id="teacher-assignments-pane">
            <div class="row g-3 align-items-end mb-4">
                <div class="col-md-3"><label class="form-label fw-semibold" for="teacher-assignment-class">Classe concernée</label><select id="teacher-assignment-class" class="form-select"><option value="">Sélectionner...</option><?php foreach ($classes as $class): ?><option value="<?= (int) $class['id'] ?>"><?= h($class['nom']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label fw-semibold" for="teacher-assignment-type">Type d'enseignement actif</label><select id="teacher-assignment-type" class="form-select"><option value="">Tous les types actifs</option><?php foreach ($teachingTypes as $type): ?><option value="<?= (int) $type['id'] ?>"><?= h($type['nom']) ?><?= !empty($type['code']) ? ' (' . h($type['code']) . ')' : '' ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label fw-semibold" for="teacher-assignment-teacher">Enseignant</label><select id="teacher-assignment-teacher" class="form-select" disabled><option value="">Choisir d'abord la classe et le type...</option></select></div>
                <div class="col-md-2"><button id="load-teacher-subjects" class="btn btn-primary w-100" disabled><i class="bi bi-arrow-clockwise me-1"></i>Charger</button></div>
            </div>
            <div id="teacher-assignment-notice" class="alert alert-info d-none"></div>
            <div id="teacher-subject-list" class="row g-2"><div class="col-12 text-muted">Choisissez une classe, un type et un enseignant.</div></div>
            <div class="d-flex justify-content-between align-items-center mt-3"><span id="teacher-assignment-count" class="small text-muted">0 matière sélectionnée</span><button id="submit-teacher-assignment" class="btn btn-primary" disabled><i class="bi bi-person-check me-1"></i>Valider l'affectation</button></div>
        </section>
    </div>
</div>

<div class="modal fade" id="competency-modal" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><form class="modal-content no-loader" id="competency-form" data-ajax="true"><div class="modal-header"><h2 class="modal-title h5" id="competency-modal-title">Ajouter une compétence</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button></div><div class="modal-body"><input type="hidden" id="competency-id"><input type="hidden" id="competency-subject-id"><label class="form-label" for="competency-label">Libellé</label><input class="form-control mb-3" id="competency-label" required maxlength="255"><label class="form-label" for="competency-description">Description</label><textarea class="form-control" id="competency-description" rows="3"></textarea><div id="competency-form-error" class="alert alert-danger d-none mt-3 mb-0"></div></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary" type="submit">Enregistrer</button></div></form></div></div>
<div class="modal fade" id="impact-modal" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h2 class="modal-title h5" id="impact-title">Analyse d'impact</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button></div><div class="modal-body" id="impact-body"></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button><button type="button" class="btn btn-danger d-none" id="confirm-delete">Supprimer</button><button type="button" class="btn btn-primary d-none" id="confirm-assignment"><i class="bi bi-check2-circle me-1"></i>Valider définitivement</button><button type="button" class="btn btn-primary d-none" id="confirm-teacher-assignment"><i class="bi bi-person-check me-1"></i>Confirmer le remplacement</button><button type="button" class="btn btn-primary d-none" id="confirm-create">Confirmer la création</button></div></div></div></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
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

    const teacherClass = document.getElementById('teacher-assignment-class'); const teacherType = document.getElementById('teacher-assignment-type'); const teacherSelect = document.getElementById('teacher-assignment-teacher'); const teacherList = document.getElementById('teacher-subject-list'); const submitTeacherAssignment = document.getElementById('submit-teacher-assignment');
    let teacherSubjects = []; let pendingTeacherAssignment = null;
    async function loadTeacherData() { teacherSelect.disabled = true; teacherSelect.innerHTML = '<option value="">Chargement...</option>'; if (!teacherClass.value || !teacherType.value) { teacherSelect.innerHTML = '<option value="">Choisir d’abord la classe et le type...</option>'; teacherList.innerHTML = '<div class="col-12 text-muted">Choisissez une classe et un type d’enseignement.</div>'; return; } const response = await fetch(`/competencies/api/teacher-assignment-data?class_id=${teacherClass.value}&teaching_type_id=${teacherType.value}`); const data = await response.json(); if (!data.success) { teacherSelect.innerHTML = `<option value="">${esc(data.error)}</option>`; return; } teacherSelect.innerHTML = '<option value="">Sélectionner un enseignant...</option>' + data.teachers.map(item => `<option value="${item.id}">${esc(`${item.prenom || ''} ${item.nom || ''}`.trim())}</option>`).join(''); teacherSelect.disabled = !data.teachers.length; teacherSubjects = data.subjects || []; renderTeacherSubjects(); }
    function renderTeacherSubjects() { const selectedTeacher = Number(teacherSelect.value || 0); teacherList.innerHTML = teacherSubjects.map(item => { const hasAssignedTeacher = Boolean(item.assigned_teacher_id); const conflict = hasAssignedTeacher && selectedTeacher > 0 && Number(item.assigned_teacher_id) !== selectedTeacher; const assignedToSelected = hasAssignedTeacher && selectedTeacher > 0 && Number(item.assigned_teacher_id) === selectedTeacher; return `<div class="col-md-6"><label class="border rounded-2 p-2 d-flex gap-2 align-items-center ${conflict ? 'border-warning bg-warning bg-opacity-10' : ''}"><input type="checkbox" class="teacher-subject" value="${item.id}" ${assignedToSelected ? 'checked' : ''}><span>${esc(item.nom)}</span>${conflict ? `<small class="ms-auto text-warning-emphasis" title="Déjà affectée à cet enseignant">Déjà affectée : ${esc((item.assigned_teacher_name || '').trim())}</small>` : ''}</label></div>`; }).join('') || '<div class="col-12 text-muted">Aucune matière active affectée à cette classe pour ce type.</div>'; refreshTeacherState(); }
    function refreshTeacherState() { const count = document.querySelectorAll('.teacher-subject:checked').length; document.getElementById('teacher-assignment-count').textContent = `${count} matière${count > 1 ? 's' : ''} sélectionnée${count > 1 ? 's' : ''}`; submitTeacherAssignment.disabled = !teacherSelect.value || !count; }
    teacherClass.addEventListener('change', loadTeacherData); teacherType.addEventListener('change', loadTeacherData); teacherSelect.addEventListener('change', renderTeacherSubjects); teacherList.addEventListener('change', refreshTeacherState);
    submitTeacherAssignment.addEventListener('click', async () => { const subjectIds = [...document.querySelectorAll('.teacher-subject:checked')].map(input => Number(input.value)); const data = new FormData(); subjectIds.forEach(id => data.append('subject_ids[]', id)); data.append('teacher_id', teacherSelect.value); data.append('class_id', teacherClass.value); const result = await post('/competencies/api/teacher-assignment-impact', data); pendingTeacherAssignment = {teacherId: teacherSelect.value, classId: teacherClass.value, subjectIds}; document.getElementById('impact-title').textContent = 'Radiographie d’impact : affectation enseignant'; document.getElementById('impact-body').innerHTML = result.has_conflicts ? `<div class="alert alert-danger"><strong>Remplacement d’affectation</strong><br>${esc(result.message)}</div>${result.conflicts.map(item => `<div class="border-top py-2"><strong>${esc(item.nom)}</strong><br><span class="text-danger">Ancien enseignant : ${esc((item.teacher_name || '').trim())}</span></div>`).join('')}` : `<div class="alert alert-success">${esc(result.message)}</div><p>Les matières seront affectées à l’enseignant sélectionné pour la classe choisie.</p>`; document.getElementById('confirm-delete').classList.add('d-none'); document.getElementById('confirm-create').classList.add('d-none'); document.getElementById('confirm-assignment').classList.add('d-none'); document.getElementById('confirm-teacher-assignment').classList.remove('d-none'); impactModal.show(); });
    document.getElementById('confirm-teacher-assignment').addEventListener('click', async () => { const data = new FormData(); pendingTeacherAssignment.subjectIds.forEach(id => data.append('subject_ids[]', id)); data.append('teacher_id', pendingTeacherAssignment.teacherId); data.append('class_id', pendingTeacherAssignment.classId); const result = await post('/competencies/api/assign-teacher-subjects', data); if (!result.success) return alert(result.error); impactModal.hide(); const notice = document.getElementById('teacher-assignment-notice'); notice.className = 'alert alert-success'; notice.textContent = `${result.assigned} matière(s) affectée(s) définitivement à l’enseignant sélectionné.`; notice.classList.remove('d-none'); loadTeacherData(); });
    if (window.location.hash === '#teacher-assignments') { const teacherTab = document.querySelector('[data-bs-target="#teacher-assignments-pane"]'); if (teacherTab) new bootstrap.Tab(teacherTab).show(); }
});
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
