<?php $title = __('subject_groups') ?? 'Groupes de Modules';
ob_start(); ?>

<div class="animate-fade-in container-fluid py-4">

    <!-- BARRE D'ACTIONS ET FILTRE : Style Floating Island -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-column flex-md-row gap-3">
        <form method="GET" action="/subject-groups" class="d-flex gap-2 align-items-center w-100 w-md-auto flex-wrap">
            <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2" style="max-width: 250px;">
                <span class="input-group-text border-0 bg-transparent text-primary">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="q" id="instantSearchInput"
                    class="form-control border-0 bg-transparent shadow-none py-2 text-main"
                    value="<?= htmlspecialchars((string) $filters['q']) ?>" placeholder="<?= __('search') ?>...">
            </div>

            <select name="teaching_type_id" class="form-select rounded-pill px-3 py-2 border-theme-light"
                style="max-width: 220px;" onchange="this.form.submit()">
                <option value=""><?= __('all_teaching_types') ?? 'Tous les types d\'enseignement' ?></option>
                <?php foreach ($teachingTypes as $tt): ?>
                    <option value="<?= $tt['id'] ?>" <?= (int) ($filters['teaching_type_id'] ?? 0) === (int) $tt['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) $tt['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm scale-on-hover text-nowrap"
            onclick="openCreateGroupModal()">
            <i class="bi bi-plus-lg me-2"></i> <?= __('add_subject_group') ?? 'Ajouter un Groupe' ?>
        </button>
    </div>

    <!-- LISTE DES GROUPES DE MODULES -->
    <div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in">
        <div class="table-responsive">
            <table class="table-modern" id="groupsTable">
                <thead>
                    <tr>
                        <th class="ps-4">Libellé du Groupe</th>
                        <th><?= __('subject_group_position') ?? 'Position' ?></th>
                        <th><?= __('teaching_form') ?? 'Forme d\'enseignement' ?> <span class="text-muted small">/ <?= __('teaching_type') ?? 'Type' ?></span></th>
                        <th>Nombre de Matières</th>
                        <th>Statut</th>
                        <th class="text-end pe-4"><?= __('action') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($groups)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-collection fs-1 opacity-25 mb-3 d-block"></i>
                                <span class="text-muted-theme"><?= __('no_data') ?></span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($groups as $g): ?>
                            <tr class="group-row">
                                <td class="ps-4">
                                    <span
                                        class="fw-bold text-main-theme group-libelle"><?= htmlspecialchars((string) $g['libelle']) ?></span>
                                </td>
                                <td><span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-1 rounded-pill"><?= (int) ($g['position'] ?? 0) ?></span></td>
                                <td>
                                    <div>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary fw-bold px-3 py-1 rounded-pill small me-1">
                                            <i class="bi bi-diagram-3 me-1"></i><?= htmlspecialchars((string) ($g['teaching_form_nom'] ?? '')) ?>
                                        </span>
                                        <?php if (!empty($g['teaching_type_nom'])): ?>
                                            <small class="text-muted d-block mt-1"><?= htmlspecialchars((string) $g['teaching_type_nom']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info fw-bold px-3 py-1 rounded-3">
                                        <i class="bi bi-journal-bookmark me-1"></i><?= (int) $g['subjects_count'] ?> matière(s)
                                    </span>
                                </td>
                                <td>
                                    <?php if ((int) $g['status'] === 1): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-bold">
                                            <i class="bi bi-check-circle-fill me-1"></i><?= __('active') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1 fw-bold">
                                            <i class="bi bi-dash-circle-fill me-1"></i><?= __('inactive') ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-1 align-items-center table-row-actions">
                                        <a href="/subject-groups/toggle?id=<?= $g['id'] ?>"
                                            class="btn btn-sm btn-action-modern <?= (int) $g['status'] === 1 ? 'text-warning' : 'text-success' ?>"
                                            title="<?= (int) $g['status'] === 1 ? __('deactivate') : __('activate') ?>">
                                            <i
                                                class="bi <?= (int) $g['status'] === 1 ? 'bi-pause-circle' : 'bi-play-circle' ?> fs-5"></i>
                                        </a>
                                        <button type="button"
                                            class="btn btn-sm btn-action-modern text-primary border-0 bg-transparent" onclick="openEditGroupModal(<?= htmlspecialchars(json_encode([
                                                'id' => (int) $g['id'],
                                                'libelle' => $g['libelle'],
                                                'position' => (int) ($g['position'] ?? 0),
                                                'teaching_form_id' => (int) ($g['teaching_form_id'] ?? 0),
                                                'teaching_type_id' => (int) ($g['teaching_type_id'] ?? 0),
                                                'status' => (int) $g['status']
                                            ]), ENT_QUOTES, 'UTF-8') ?>)" title="<?= __('edit') ?>">
                                            <i class="bi bi-pencil-square fs-5"></i>
                                        </button>
                                        <?php if ((int) $g['subjects_count'] == 0 && (int)$g['id'] > 0): ?>
                                            <a href="/subject-groups/delete?id=<?= $g['id'] ?>"
                                                class="btn btn-sm btn-action-modern text-danger btn-confirm-delete"
                                                data-confirm="Voulez-vous vraiment supprimer ce groupe de modules ?"
                                                title="<?= __('delete') ?>">
                                                <i class="bi bi-trash fs-5"></i>
                                            </a>
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

<!-- MODAL BOOTSTRAP 5 : CRÉATION / ÉDITION DE GROUPE -->
<div class="modal fade" id="subjectGroupModal" tabindex="-1" aria-labelledby="subjectGroupModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: var(--bg-card);">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-init bg-primary text-white rounded-4 d-flex align-items-center justify-content-center shadow-sm"
                        style="width: 44px; height: 44px;">
                        <i class="bi bi-collection fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-black text-main-theme" id="subjectGroupModalLabel">Nouveau Groupe de
                            Modules</h5>
                        <p class="text-muted-theme small mb-0 opacity-75">Paramétrez le nom et l'affiliation</p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="subjectGroupForm" action="/subject-groups/store" method="POST">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                <div class="modal-body p-4">

                    <div class="mb-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">
                            <?= __('subject_group_position') ?? 'Position' ?> <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="bi bi-sort-numeric-down"></i></span>
                            <input type="number" name="position" id="group_position" class="form-control premium-input" min="1" step="1" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">
                            Libellé du Groupe <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="bi bi-fonts"></i></span>
                            <input type="text" name="libelle" id="group_libelle" class="form-control premium-input"
                                placeholder="Ex: Groupe 1 - Matières Littéraires" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">
                            <?= __('teaching_form') ?? 'Forme d\'enseignement' ?> <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="bi bi-diagram-3"></i></span>
                            <select name="teaching_form_id" id="group_teaching_form_id"
                                class="form-select premium-input" <?= !empty($teachingForms) ? 'required' : '' ?>>
                                <option value="" disabled selected><?= __('select_teaching_form') ?? 'Sélectionner la forme' ?>...</option>
                                <?php foreach ($teachingForms as $tf): ?>
                                    <option value="<?= $tf['id'] ?>"><?= htmlspecialchars((string) $tf['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">
                            <?= __('teaching_type') ?? 'Type Enseignement' ?> <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="bi bi-tag"></i></span>
                            <select name="teaching_type_id" id="group_teaching_type_id" class="form-select premium-input" required>
                                <option value="" disabled selected><?= __('select_teaching_type') ?? 'Sélectionner le type' ?>...</option>
                                <?php foreach ($teachingTypes as $tt): ?>
                                    <option value="<?= $tt['id'] ?>"><?= htmlspecialchars((string) $tt['nom']) ?> (<?= htmlspecialchars((string) $tt['code']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-check form-switch mt-3" id="group_status_wrapper">
                        <input class="form-check-input" type="checkbox" name="status" id="group_status" value="1"
                            checked>
                        <label class="form-check-label fw-bold text-main-theme small" for="group_status">
                            Groupe actif
                        </label>
                    </div>

                </div>

                <div class="modal-footer border-top-0 pt-0 px-4 pb-4 gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold scale-on-hover"
                        data-bs-dismiss="modal">
                        <?= __('cancel') ?>
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm scale-on-hover">
                        <i class="bi bi-check-circle-fill me-2"></i> <span
                            id="groupSubmitBtnText"><?= __('save') ?></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .scale-on-hover:hover {
        transform: scale(1.05);
    }

    .input-group-modern {
        display: flex;
        align-items: center;
        min-height: 50px;
        background: var(--bg-body, #f8fafc);
        border: 1px solid var(--border-theme, #cbd5e1);
        border-radius: 16px;
        transition: all 0.3s ease;
        padding: 0 15px;
    }

    [data-theme="dark"] .input-group-modern {
        background: rgba(15, 23, 42, 0.6);
        border-color: rgba(255, 255, 255, 0.12);
    }

    .input-group-modern:focus-within {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(var(--primary-rgb, 59, 130, 246), 0.15);
    }

    .input-group-text-modern {
        color: var(--primary-color);
        opacity: 0.8;
        margin-right: 10px;
        font-size: 1.1rem;
    }

    .premium-input {
        background: transparent !important;
        border: none !important;
        height: 48px !important;
        min-height: 48px !important;
        padding: 8px 0 !important;
        box-shadow: none !important;
        color: var(--text-main, #0f172a) !important;
        font-weight: 600;
        font-size: 0.95rem;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Instant Search Logic
        const searchInput = document.getElementById('instantSearchInput');
        const rows = document.querySelectorAll('.group-row');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const query = this.value.toLowerCase().trim();
                rows.forEach(row => {
                    const text = row.querySelector('.group-libelle').textContent.toLowerCase();
                    if (text.includes(query)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
    });

    function openCreateGroupModal() {
        const form = document.getElementById('subjectGroupForm');
        if (!form) return;
        form.action = '/subject-groups/store';

        document.getElementById('subjectGroupModalLabel').textContent = "Nouveau Groupe de Modules";
        document.getElementById('groupSubmitBtnText').textContent = "<?= addslashes(__('create') ?? 'Créer') ?>";

        document.getElementById('group_libelle').value = '';
        document.getElementById('group_position').value = '';
        const tfSelect = document.getElementById('group_teaching_form_id');
        if (tfSelect && tfSelect.options.length > 1) tfSelect.selectedIndex = 1;
        const ttSelect = document.getElementById('group_teaching_type_id');
        if (ttSelect && ttSelect.options.length > 1) ttSelect.selectedIndex = 1;
        document.getElementById('group_status').checked = true;

        const modal = new bootstrap.Modal(document.getElementById('subjectGroupModal'));
        modal.show();
    }

    function openEditGroupModal(data) {
        const form = document.getElementById('subjectGroupForm');
        if (!form || !data) return;
        form.action = '/subject-groups/update?id=' + data.id;

        document.getElementById('subjectGroupModalLabel').textContent = "Modifier le Groupe de Modules";
        document.getElementById('groupSubmitBtnText').textContent = "<?= addslashes(__('save') ?? 'Enregistrer') ?>";

        document.getElementById('group_libelle').value = data.libelle || '';
        document.getElementById('group_position').value = data.position || '';
        const tfSelect = document.getElementById('group_teaching_form_id');
        if (data.teaching_form_id && tfSelect) {
            tfSelect.value = data.teaching_form_id;
        }
        const ttSelect = document.getElementById('group_teaching_type_id');
        if (data.teaching_type_id && ttSelect) {
            ttSelect.value = data.teaching_type_id;
        }
        document.getElementById('group_status').checked = data.status == 1;

        const modal = new bootstrap.Modal(document.getElementById('subjectGroupModal'));
        modal.show();
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>