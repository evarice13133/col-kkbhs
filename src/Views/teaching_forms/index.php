<?php
$title = __('teaching_forms') ?? 'Formes d\'enseignement';
ob_start();

$canManage = \App\Core\PermissionManager::hasPermission('manage_teaching_forms');
$filters = [
    'q' => $q ?? '',
    'teaching_type_id' => $teaching_type_id ?? null,
];
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">
    <div class="dept-header-card mb-4 p-3 p-md-4 rounded-4 shadow-sm position-relative overflow-hidden">
        <div class="dept-header-bg"></div>
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between w-100 gap-3 position-relative" style="z-index: 2;">
            <div class="d-flex align-items-center gap-3">
                <div class="dept-icon-wrapper rounded-4 d-flex align-items-center justify-content-center flex-shrink-0">
                    <i class="bi bi-bookmarks fs-4 text-primary"></i>
                </div>
                <div>
                    <h1 class="fw-black fs-4 text-main-theme mb-1 lh-1">
                        <?= __('teaching_forms') ?? 'Formes d\'enseignement' ?>
                    </h1>
                    <p class="text-muted-theme mb-0 fw-medium opacity-75" style="font-size: 0.88rem;">
                        <?= __('lang') === 'en' ? 'Manage teaching forms and academic organization' : 'Gérez les formes d\'enseignement et l\'organisation académique' ?>
                    </p>
                </div>
            </div>

            <?php if ($canManage): ?>
                <div class="d-flex flex-row w-100 w-md-auto justify-content-end ms-md-auto gap-2 mt-2 mt-md-0">
                    <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm flex-grow-1 flex-md-grow-0 d-flex justify-content-center align-items-center gap-2 text-nowrap scale-on-hover" onclick="openCreateTeachingFormModal()">
                        <i class="bi bi-plus-lg"></i>
                        <span><?= __('add_teaching_form') ?? 'Ajouter une forme' ?></span>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="filter-island-container mb-4">
        <div class="filter-island p-3 rounded-4 shadow-sm">
            <form method="GET" action="/teaching_forms" id="tf-filter-form" class="filter-form w-100 m-0">
                <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between">
                    <div class="d-flex flex-column flex-sm-row gap-2 flex-grow-1">
                        <div class="dept-search-pill flex-grow-1 position-relative">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" name="q" id="tf-search-input" class="form-control dept-filter-input ps-5"
                                value="<?= htmlspecialchars((string) ($filters['q'] ?? '')) ?>"
                                placeholder="<?= __('search') ?? 'Rechercher' ?> (Nom, Code)...">
                        </div>

                        <div class="dept-select-wrapper" style="min-width: 200px;">
                            <select name="teaching_type_id" id="tf-teaching-type-select" class="form-select dept-filter-select">
                                <option value=""><?= __('all_teaching_types') ?? 'Tous les types d\'enseignement' ?></option>
                                <?php foreach ($teachingTypes as $tt): ?>
                                    <option value="<?= (int) $tt['id'] ?>" <?= (int) ($filters['teaching_type_id'] ?? 0) === (int) $tt['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $tt['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex gap-2 align-items-center justify-content-end">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm text-nowrap scale-on-hover">
                            <i class="bi bi-funnel-fill me-1"></i> <?= __('filter') ?? 'Filtrer' ?>
                        </button>
                        <a href="/teaching_forms" class="btn btn-light-theme rounded-circle p-2 d-flex align-items-center justify-content-center reset-btn scale-on-hover" style="width: 42px; height: 42px;" title="<?= __('reset') ?? 'Réinitialiser' ?>">
                            <i class="bi bi-arrow-counterclockwise fs-5"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4" id="teaching-forms-grid">
        <?php foreach ($teachingForms as $form): ?>
            <div class="col-12 col-md-6 col-xl-4 teaching-form-card-item">
                <div class="subject-card-compact border-theme-dynamic h-100 position-relative <?= ($canManage && !$form['status']) ? 'opacity-75' : '' ?>">
                    <div class="subject-card-glow"></div>
                    <div class="card-body p-4 position-relative d-flex flex-column justify-content-between h-100" style="z-index: 1;">
                        <div>
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-3 overflow-hidden">
                                    <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-black rounded-4 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 54px; height: 54px; font-size: 1.2rem;">
                                        <i class="bi bi-bookmarks"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <h5 class="fw-black m-0 text-main-theme text-truncate" title="<?= htmlspecialchars((string) $form['nom']) ?>">
                                            <?= htmlspecialchars((string) $form['nom']) ?>
                                        </h5>
                                        <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                            <span class="badge bg-soft-primary text-primary extra-small fw-bold px-2 py-1 rounded-pill">
                                                <?= htmlspecialchars((string) $form['code']) ?>
                                            </span>
                                            <?php if (!empty($form['teaching_type_nom'])): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success extra-small fw-bold px-2 py-1 rounded-pill border border-success border-opacity-10">
                                                    <i class="bi bi-diagram-3 me-1"></i> <?= htmlspecialchars((string) $form['teaching_type_nom']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($canManage): ?>
                                    <div class="dropdown flex-shrink-0">
                                        <button class="btn btn-link text-muted p-0 shadow-none border-0" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical fs-5"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2">
                                            <li>
                                                <button type="button" class="dropdown-item dropdown-item-modern border-0 bg-transparent text-start w-100" onclick="openEditTeachingFormModal(<?= htmlspecialchars(json_encode([
                                                    'id' => (int) $form['id'],
                                                    'nom' => $form['nom'],
                                                    'code' => $form['code'],
                                                    'teaching_type_id' => (int) ($form['teaching_type_id'] ?? 0),
                                                    'status' => (int) ($form['status'] ?? 1)
                                                ]), ENT_QUOTES, 'UTF-8') ?>)">
                                                    <i class="bi bi-pencil text-primary"></i> <?= __('edit') ?? 'Modifier' ?>
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item dropdown-item-modern border-0 bg-transparent text-start w-100" onclick="confirmToggleTeachingForm(<?= (int) $form['id'] ?>, <?= htmlspecialchars(json_encode($form['nom']), ENT_QUOTES, 'UTF-8') ?>, <?= $form['status'] ? 'true' : 'false' ?>)">
                                                    <i class="bi <?= $form['status'] ? 'bi-eye-slash text-warning' : 'bi-eye text-success' ?>"></i>
                                                    <?= $form['status'] ? (__('deactivate') ?? 'Désactiver') : (__('activate') ?? 'Activer') ?>
                                                </button>
                                            </li>
                                            <?php if (\App\Core\Session::get('user_role') === 'superadmin'): ?>
                                                <li>
                                                    <a class="dropdown-item dropdown-item-modern text-danger border-0 bg-transparent text-start w-100 btn-confirm-delete"
                                                       href="/teaching_forms/delete?id=<?= $form['id'] ?>&csrf_token=<?= \App\Core\Session::generateCsrfToken() ?>"
                                                       data-confirm="<?= __('confirm_delete_text') ?? 'Voulez-vous supprimer cette forme d\'enseignement ?' ?>">
                                                        <i class="bi bi-trash text-danger"></i> <?= __('delete') ?? 'Supprimer' ?>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top border-theme-light d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($form['status']): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 rounded-pill extra-small px-3">
                                        <i class="bi bi-check-circle-fill me-1"></i> <?= __('active') ?? 'Actif' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10 rounded-pill extra-small px-3">
                                        <i class="bi bi-x-circle-fill me-1"></i> <?= __('inactive') ?? 'Inactif' ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="text-muted-theme extra-small opacity-75 font-monospace fw-bold">
                                CODE: <?= htmlspecialchars((string) $form['code']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($teachingForms)): ?>
            <div class="col-12">
                <div class="subject-card-compact p-5 text-center border-dashed">
                    <i class="bi bi-bookmarks fs-1 opacity-25 mb-3 d-block"></i>
                    <h5 class="text-muted"><?= __('no_data') ?? 'Aucune donnée disponible' ?></h5>
                    <?php if ($canManage): ?>
                        <p class="small text-muted mb-4">Commencez par créer la première forme d'enseignement de votre établissement.</p>
                        <button type="button" class="btn btn-primary rounded-pill px-4" onclick="openCreateTeachingFormModal()"><?= __('add_teaching_form') ?? 'Ajouter une forme' ?></button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($canManage): ?>
<div class="modal fade" id="teachingFormModal" tabindex="-1" aria-labelledby="teachingFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: var(--bg-card);">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-init bg-primary text-white rounded-4 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px;" id="tfModalIcon">
                        <i class="bi bi-bookmarks fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-black text-main-theme" id="teachingFormModalLabel"><?= __('add_teaching_form') ?? 'Ajouter une forme' ?></h5>
                        <p class="text-muted-theme small mb-0 opacity-75" id="tfModalSubtext">Formulaire de configuration de la forme d'enseignement</p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="teachingFormForm" action="/teaching_forms/store" method="POST">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">
                            Intitulé <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="bi bi-fonts"></i></span>
                            <input type="text" name="nom" id="tf_nom" class="form-control premium-input" placeholder="ex: Formation Professionnelle" required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">
                            Code <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="bi bi-hash"></i></span>
                            <input type="text" name="code" id="tf_code" class="form-control premium-input" placeholder="ex: FP" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">
                            <?= __('teaching_type') ?? 'Type d\'enseignement' ?> <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="bi bi-diagram-3"></i></span>
                            <select name="teaching_type_id" id="tf_teaching_type_id" class="form-select premium-input" required>
                                <option value="" disabled selected><?= __('select_teaching_type') ?? 'Sélectionner le type' ?></option>
                                <?php foreach ($teachingTypes as $tt): ?>
                                    <option value="<?= (int) $tt['id'] ?>"><?= htmlspecialchars((string) $tt['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top-0 pt-0 px-4 pb-4 gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold scale-on-hover" data-bs-dismiss="modal">
                        <?= __('cancel') ?>
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm scale-on-hover">
                        <i class="bi bi-check-circle-fill me-2"></i> <span id="tfSubmitBtnText"><?= __('save') ?></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tfSearchInput = document.getElementById('tf-search-input');
    const tfTeachingTypeSelect = document.getElementById('tf-teaching-type-select');
    const tfFilterForm = document.getElementById('tf-filter-form');
    let debounceTimer;

    if (tfSearchInput && tfFilterForm) {
        tfSearchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => tfFilterForm.submit(), 400);
        });
    }

    if (tfTeachingTypeSelect && tfFilterForm) {
        tfTeachingTypeSelect.addEventListener('change', function () {
            tfFilterForm.submit();
        });
    }
});

function openCreateTeachingFormModal() {
    const form = document.getElementById('teachingFormForm');
    if (!form) return;
    form.action = '/teaching_forms/store';
    document.getElementById('teachingFormModalLabel').textContent = "<?= addslashes(__('add_teaching_form')) ?>";
    document.getElementById('tfModalSubtext').textContent = "Formulaire de configuration de la forme d'enseignement";
    document.getElementById('tfSubmitBtnText').textContent = "<?= addslashes(__('save')) ?>";
    document.getElementById('tfModalIcon').innerHTML = '<i class="bi bi-bookmarks fs-4"></i>';

    document.getElementById('tf_nom').value = '';
    document.getElementById('tf_code').value = '';
    document.getElementById('tf_teaching_type_id').value = '';

    const modal = new bootstrap.Modal(document.getElementById('teachingFormModal'));
    modal.show();
}

function openEditTeachingFormModal(form) {
    const formEl = document.getElementById('teachingFormForm');
    if (!formEl || !form) return;
    formEl.action = '/teaching_forms/update?id=' + form.id;
    document.getElementById('teachingFormModalLabel').textContent = "<?= addslashes(__('edit_teaching_form')) ?>";
    document.getElementById('tfModalSubtext').textContent = form.nom || '';
    document.getElementById('tfSubmitBtnText').textContent = "<?= addslashes(__('save')) ?>";
    document.getElementById('tfModalIcon').innerHTML = '<i class="bi bi-pencil-square fs-4"></i>';

    document.getElementById('tf_nom').value = form.nom || '';
    document.getElementById('tf_code').value = form.code || '';
    document.getElementById('tf_teaching_type_id').value = form.teaching_type_id || '';

    const modal = new bootstrap.Modal(document.getElementById('teachingFormModal'));
    modal.show();
}

function confirmToggleTeachingForm(formId, formName, isCurrentlyActive) {
    const toggleUrl = '/teaching_forms/toggle?id=' + formId;
    const msg = isCurrentlyActive ? 'Désactiver cette forme d\'enseignement ?' : 'Activer cette forme d\'enseignement ?';
    if (confirm(msg)) {
        window.location.href = toggleUrl;
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
