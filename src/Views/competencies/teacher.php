<?php
$title = __('manage_competencies') ?? 'Gestion des compétences';
ob_start();
?>
<div class="container-fluid py-4" id="teacher-competencies">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1"><?= h($title) ?></h1>
            <p class="text-muted mb-0"><?= __('teacher_competencies_intro') ?? 'Gérez les compétences des matières qui vous sont affectées pour l’année en cours.' ?></p>
        </div>
        <a href="/" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i><?= __('back_to_dashboard') ?? 'Retour au tableau de bord' ?></a>
    </div>

    <?php if (empty($teacherAssignments)): ?>
        <div class="alert alert-info" role="status">
            <i class="bi bi-info-circle me-2"></i><?= __('no_active_subject_assignments') ?? 'Aucune matière ne vous est affectée pour l’année académique active.' ?>
        </div>
    <?php else: ?>
        <section class="modern-card border-0 shadow-sm rounded-4 p-4 mb-4" aria-labelledby="competency-selection-title">
            <h2 id="competency-selection-title" class="h6 fw-bold mb-3"><i class="bi bi-list-check text-info me-2"></i><?= __('competencies_management') ?? 'Gestion des compétences' ?></h2>
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="competency-class"><?= __('select_class') ?? 'Sélectionner une classe' ?></label>
                    <select id="competency-class" class="form-select">
                        <option value=""><?= __('select_class_placeholder') ?? 'Choisir une classe...' ?></option>
                        <?php $teacherClasses = [];
                        foreach ($teacherAssignments as $assignment) {
                            $teacherClasses[(int) $assignment['class_id']] = $assignment['class_nom'];
                        }
                        foreach ($teacherClasses as $classId => $className): ?>
                            <option value="<?= $classId ?>"><?= h($className) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="competency-subject"><?= __('select_subject') ?? 'Sélectionner une matière' ?></label>
                    <select id="competency-subject" class="form-select" disabled>
                        <option value=""><?= __('select_subject_placeholder') ?? 'Choisir une matière...' ?></option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button id="load-competencies" type="button" class="btn btn-info text-white fw-semibold w-100" disabled>
                        <i class="bi bi-arrow-clockwise me-2"></i><?= __('load_competencies') ?? 'Charger les compétences' ?>
                    </button>
                </div>
            </div>
            <div id="competency-feedback" class="alert d-none mt-3 mb-0" role="status" aria-live="polite"></div>
        </section>

        <section id="competency-results" class="d-none" aria-labelledby="competency-results-title">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h2 id="competency-results-title" class="h5 fw-bold mb-1"><?= __('subject_competencies') ?? 'Compétences de la matière' ?></h2>
                    <p id="competency-context" class="small text-muted mb-0"></p>
                </div>
                <button id="add-competency" type="button" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i><?= __('add_competency') ?? 'Ajouter une compétence' ?>
                </button>
            </div>
            <div id="competency-list" class="row g-3"></div>
            <div id="no-competencies" class="text-center border rounded-3 py-5 px-3 text-muted d-none">
                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                <?= __('no_competencies_defined') ?? 'Aucune compétence définie pour cette matière.' ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<div class="modal fade" id="competency-modal" tabindex="-1" aria-labelledby="competency-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="competency-form" class="modal-content no-loader">
            <div class="modal-header">
                <h2 class="modal-title h5" id="competency-modal-title"><?= __('add_competency') ?? 'Ajouter une compétence' ?></h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= __('close') ?? 'Fermer' ?>"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="competency-id">
                <input type="hidden" id="competency-position" value="0">
                <label class="form-label fw-semibold" for="competency-label"><?= __('competency_label') ?? 'Libellé' ?></label>
                <input id="competency-label" class="form-control mb-3" maxlength="255" required>
                <label class="form-label fw-semibold" for="competency-description"><?= __('description') ?? 'Description' ?></label>
                <textarea id="competency-description" class="form-control" rows="4"></textarea>
                <div id="competency-form-error" class="alert alert-danger d-none mt-3 mb-0" role="alert"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= __('cancel') ?? 'Annuler' ?></button>
                <button id="save-competency" type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i><?= __('save') ?? 'Enregistrer' ?></button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const assignments = <?= json_encode($teacherAssignments, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const classSelect = document.getElementById('competency-class');
    if (!classSelect) return;

    const subjectSelect = document.getElementById('competency-subject');
    const loadButton = document.getElementById('load-competencies');
    const results = document.getElementById('competency-results');
    const list = document.getElementById('competency-list');
    const emptyState = document.getElementById('no-competencies');
    const feedback = document.getElementById('competency-feedback');
    const modalElement = document.getElementById('competency-modal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const form = document.getElementById('competency-form');
    let competencies = [];

    function showFeedback(message, type = 'danger') {
        feedback.textContent = message;
        feedback.className = `alert alert-${type} mt-3 mb-0`;
    }

    function clearFeedback() {
        feedback.textContent = '';
        feedback.className = 'alert d-none mt-3 mb-0';
    }

    function selectedAssignment() {
        return assignments.find(item => Number(item.class_id) === Number(classSelect.value)
            && Number(item.subject_id) === Number(subjectSelect.value));
    }

    function renderCompetencies() {
        list.replaceChildren();
        emptyState.classList.toggle('d-none', competencies.length > 0);
        competencies.forEach(item => {
            const column = document.createElement('div');
            column.className = 'col-md-6 col-xl-4';
            column.innerHTML = `<article class="border rounded-3 p-3 h-100 d-flex flex-column"><div class="d-flex justify-content-between gap-2"><h3 class="h6 fw-bold mb-2"></h3><div class="d-flex gap-1 flex-shrink-0"><button type="button" class="btn btn-sm btn-outline-secondary edit-competency" title="<?= __('edit') ?? 'Modifier' ?>" aria-label="<?= __('edit') ?? 'Modifier' ?>"><i class="bi bi-pencil"></i></button><button type="button" class="btn btn-sm btn-outline-danger delete-competency" title="<?= __('delete') ?? 'Supprimer' ?>" aria-label="<?= __('delete') ?? 'Supprimer' ?>"><i class="bi bi-trash"></i></button></div></div><p class="small text-muted mb-0 flex-grow-1"></p></article>`;
            column.querySelector('h3').textContent = item.libelle;
            column.querySelector('p').textContent = item.description || '<?= __('no_description') ?? 'Aucune description' ?>';
            column.querySelector('.edit-competency').addEventListener('click', () => openEditor(item));
            column.querySelector('.delete-competency').addEventListener('click', () => deleteCompetency(item));
            list.appendChild(column);
        });
    }

    async function loadCompetencies() {
        const assignment = selectedAssignment();
        if (!assignment) return;
        clearFeedback();
        loadButton.disabled = true;
        loadButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span><?= __('loading') ?? 'Chargement...' ?>';
        try {
            const query = new URLSearchParams({subject_id: assignment.subject_id, class_id: assignment.class_id});
            const response = await fetch(`/competencies/api/by-subject?${query}`);
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.error || '<?= __('error_loading_competencies') ?? 'Erreur lors du chargement des compétences.' ?>');
            competencies = data.competencies || [];
            document.getElementById('competency-context').textContent = `${assignment.subject_nom} · ${assignment.class_nom}`;
            renderCompetencies();
            results.classList.remove('d-none');
        } catch (error) {
            showFeedback(error.message);
        } finally {
            loadButton.disabled = false;
            loadButton.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i><?= __('load_competencies') ?? 'Charger les compétences' ?>';
        }
    }

    function openEditor(item = null) {
        clearFeedback();
        document.getElementById('competency-form-error').classList.add('d-none');
        document.getElementById('competency-id').value = item?.id || '';
        document.getElementById('competency-position').value = item?.position || 0;
        document.getElementById('competency-label').value = item?.libelle || '';
        document.getElementById('competency-description').value = item?.description || '';
        document.getElementById('competency-modal-title').textContent = item ? '<?= __('edit_competency') ?? 'Modifier la compétence' ?>' : '<?= __('add_competency') ?? 'Ajouter une compétence' ?>';
        modal.show();
    }

    async function deleteCompetency(item) {
        if (!window.confirm(`<?= __('confirm_delete_competency') ?? 'Supprimer la compétence' ?> « ${item.libelle} » ?`)) return;
        const assignment = selectedAssignment();
        const data = new FormData();
        data.append('competency_id', item.id);
        data.append('subject_id', assignment.subject_id);
        data.append('class_id', assignment.class_id);
        try {
            const response = await fetch('/competencies/api/delete', {method: 'POST', body: data});
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.error || '<?= __('error_deleting_competency') ?? 'Erreur lors de la suppression.' ?>');
            await loadCompetencies();
            showFeedback('<?= __('competency_deleted') ?? 'Compétence supprimée.' ?>', 'success');
        } catch (error) {
            showFeedback(error.message);
        }
    }

    classSelect.addEventListener('change', () => {
        const classId = Number(classSelect.value);
        const available = assignments.filter(item => Number(item.class_id) === classId);
        subjectSelect.replaceChildren(new Option('<?= __('select_subject_placeholder') ?? 'Choisir une matière...' ?>', ''));
        available.forEach(item => subjectSelect.add(new Option(item.subject_nom, item.subject_id)));
        subjectSelect.disabled = available.length === 0;
        loadButton.disabled = true;
        results.classList.add('d-none');
        clearFeedback();
    });
    subjectSelect.addEventListener('change', () => {
        loadButton.disabled = !selectedAssignment();
        results.classList.add('d-none');
        clearFeedback();
    });
    loadButton.addEventListener('click', loadCompetencies);
    document.getElementById('add-competency').addEventListener('click', () => openEditor());

    form.addEventListener('submit', async event => {
        event.preventDefault();
        const assignment = selectedAssignment();
        const competencyId = document.getElementById('competency-id').value;
        const data = new FormData();
        data.append('subject_id', assignment.subject_id);
        data.append('class_id', assignment.class_id);
        data.append('libelle', document.getElementById('competency-label').value.trim());
        data.append('description', document.getElementById('competency-description').value.trim());
        if (competencyId) {
            data.append('competency_id', competencyId);
            data.append('position', document.getElementById('competency-position').value);
        }
        const saveButton = document.getElementById('save-competency');
        saveButton.disabled = true;
        try {
            const response = await fetch(`/competencies/api/${competencyId ? 'update' : 'create'}`, {method: 'POST', body: data});
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.error || '<?= __('error_saving_competency') ?? 'Erreur lors de l’enregistrement.' ?>');
            modal.hide();
            await loadCompetencies();
            showFeedback(competencyId ? '<?= __('competency_updated') ?? 'Compétence mise à jour.' ?>' : '<?= __('competency_created') ?? 'Compétence créée.' ?>', 'success');
        } catch (error) {
            const errorElement = document.getElementById('competency-form-error');
            errorElement.textContent = error.message;
            errorElement.classList.remove('d-none');
        } finally {
            saveButton.disabled = false;
        }
    });
});
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';