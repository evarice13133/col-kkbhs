<?php
$title = __('sequences') ?? 'Séquences & Évaluations';
ob_start();

$canManage = \App\Core\PermissionManager::hasPermission('manage_sequences');
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">

    <!-- EN-TÊTE DE PAGE : Style Glassmorphism Premium avec support Mode Sombre -->
    <div class="dept-header-card mb-4 p-3 p-md-4 rounded-4 shadow-sm position-relative overflow-hidden">
        <div class="dept-header-bg"></div>
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between w-100 gap-3 position-relative"
            style="z-index: 2;">
            <div class="d-flex align-items-center gap-3">
                <div class="dept-icon-wrapper rounded-4 d-flex align-items-center justify-content-center flex-shrink-0">
                    <i class="bi bi-calendar-event-fill fs-4 text-primary"></i>
                </div>
                <div>
                    <h1 class="fw-black fs-4 text-main-theme mb-1 lh-1">
                        <?= __('sequences') ?? 'Séquences & Évaluations' ?>
                    </h1>
                    <p class="text-muted-theme mb-0 fw-medium opacity-75" style="font-size: 0.88rem;">
                        <?= __('lang') === 'en' ? 'Manage evaluation sequences, semesters and academic trimesters' : 'Configurez les séquences d\'évaluation, semestres et trimestres académiques' ?>
                    </p>
                </div>
            </div>

            <?php if ($canManage): ?>
                <div class="d-flex flex-row w-100 w-md-auto justify-content-end ms-md-auto gap-2 mt-2 mt-md-0">
                    <button type="button"
                        class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm flex-grow-1 flex-md-grow-0 d-flex justify-content-center align-items-center gap-2 text-nowrap scale-on-hover"
                        onclick="openCreateEvaluationModal()">
                        <i class="bi bi-plus-lg"></i>
                        <span><?= __('add_sequence') ?? 'Ajouter une séquence' ?></span>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- BARRE DE FILTRES ET RECHERCHE INSTANTANÉE -->
    <div class="filter-island-container mb-4">
        <div class="filter-island p-3 rounded-4 shadow-sm">
            <form method="GET" action="/sequences" id="sequence-filter-form" class="filter-form w-100 m-0">
                <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between">

                    <div class="d-flex flex-column flex-sm-row gap-2 flex-grow-1">
                        <!-- Recherche instantanée -->
                        <div class="dept-search-pill flex-grow-1 position-relative">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" id="sequence-search-input" class="form-control dept-filter-input ps-5"
                                placeholder="<?= __('search') ?? 'Rechercher' ?> (Intitulé, Code)...">
                        </div>

                        <!-- Type Enseignement -->
                        <div class="dept-select-wrapper" style="min-width: 220px;">
                            <select name="teaching_type_id" id="index_teaching_type_filter"
                                class="form-select dept-filter-select" onchange="this.form.submit()">
                                <option value=""><?= __('all_teaching_types') ?? 'Tous les types d\'enseignement' ?>
                                </option>
                                <?php foreach ($teachingTypes as $tt): ?>
                                    <option value="<?= $tt['id'] ?>" data-code="<?= htmlspecialchars($tt['code']) ?>"
                                        <?= (int) ($filters['teaching_type_id'] ?? 0) === (int) $tt['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $tt['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Actions Filtre -->
                    <div class="d-flex gap-2 align-items-center justify-content-end">
                        <a href="/sequences"
                            class="btn btn-light-theme rounded-circle p-2 d-flex align-items-center justify-content-center reset-btn scale-on-hover"
                            style="width: 42px; height: 42px;" title="<?= __('reset') ?? 'Réinitialiser' ?>">
                            <i class="bi bi-arrow-counterclockwise fs-5"></i>
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- LISTE DES ÉVALUATIONS / SÉQUENCES -->
    <div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in">
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 110px;"><?= __('code') ?></th>
                        <th>Type d'Enseignement</th>
                        <th><?= __('label') ?></th>
                        <th><?= __('Short_Label') ?></th>
                        <th>Période / Trimestre</th>
                        <th>Ordre</th>
                        <th><?= __('status') ?></th>
                        <th class="text-end pe-4"><?= __('action') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sequences)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-calendar-x fs-1 opacity-25 mb-3 d-block"></i>
                                <span class="text-muted-theme"><?= __('no_data') ?></span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sequences as $s): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-1 rounded-3">
                                        <?= htmlspecialchars((string) $s['code']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-secondary bg-opacity-10 text-secondary fw-bold px-3 py-1 rounded-pill small">
                                        <i
                                            class="bi bi-diagram-3 me-1"></i><?= htmlspecialchars((string) ($s['teaching_type_nom'] ?? 'N/A')) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold text-main-theme"><?= htmlspecialchars((string) $s['label']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-main-theme border fw-bold px-3 py-1 rounded-3">
                                        <?= htmlspecialchars((string) ($s['short_label'] ?? '')) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $isSecondary = empty($s['teaching_type_code']) || in_array(strtoupper((string) $s['teaching_type_code']), ['ESG', 'EST', 'SEC'], true) || stripos((string) ($s['teaching_type_nom'] ?? ''), 'secondaire') !== false;
                                    ?>
                                    <?php if (!empty($s['start_date']) || !empty($s['end_date'])): ?>
                                        <div class="small fw-semibold text-main-theme">
                                            <i class="bi bi-calendar3 me-1 text-primary"></i>
                                            <?= $s['start_date'] ? date('d/m/Y', strtotime($s['start_date'])) : '...' ?> -
                                            <?= $s['end_date'] ? date('d/m/Y', strtotime($s['end_date'])) : '...' ?>
                                        </div>
                                    <?php elseif ($isSecondary && !empty($s['trimestre'])): ?>
                                        <div
                                            class="p-1 px-2 rounded-2 bg-info bg-opacity-10 text-info small fw-bold d-inline-block">
                                            Trimestre <?= (int) $s['trimestre'] ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-muted small">Pos. <?= (int) $s['position'] ?></span>
                                </td>
                                <td>
                                    <?php if ((int) $s['is_active'] === 1): ?>
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
                                        <a href="/sequences/toggle?id=<?= $s['id'] ?>"
                                            class="btn btn-sm btn-action-modern <?= (int) $s['is_active'] === 1 ? 'text-warning' : 'text-success' ?>"
                                            title="<?= (int) $s['is_active'] === 1 ? __('deactivate') : __('activate') ?>">
                                            <i
                                                class="bi <?= (int) $s['is_active'] === 1 ? 'bi-pause-circle' : 'bi-play-circle' ?> fs-5"></i>
                                        </a>
                                        <button type="button"
                                            class="btn btn-sm btn-action-modern text-primary border-0 bg-transparent" onclick="openEditEvaluationModal(<?= htmlspecialchars(json_encode([
                                                'id' => (int) $s['id'],
                                                'teaching_type_id' => (int) ($s['teaching_type_id'] ?? 0),
                                                'code' => $s['code'],
                                                'label' => $s['label'],
                                                'short_label' => $s['short_label'],
                                                'trimestre' => (int) $s['trimestre'],
                                                'position' => (int) $s['position'],
                                                'start_date' => $s['start_date'] ?? '',
                                                'end_date' => $s['end_date'] ?? '',
                                                'is_active' => (int) $s['is_active']
                                            ]), ENT_QUOTES, 'UTF-8') ?>)" title="<?= __('edit') ?>">
                                            <i class="bi bi-pencil-square fs-5"></i>
                                        </button>
                                        <a href="/sequences/delete?id=<?= $s['id'] ?>"
                                            class="btn btn-sm btn-action-modern text-danger btn-confirm-delete"
                                            data-confirm="<?= __('confirm_delete_sequence') ?>" title="<?= __('delete') ?>">
                                            <i class="bi bi-trash fs-5"></i>
                                        </a>
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

<!-- MODAL BOOTSTRAP 5 : ÉVALUATION (Choix dynamique selon le type d'enseignement) -->
<div class="modal fade" id="evaluationModal" tabindex="-1" aria-labelledby="evaluationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: var(--bg-card);">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-init bg-primary text-white rounded-4 d-flex align-items-center justify-content-center shadow-sm"
                        style="width: 44px; height: 44px;" id="evalModalIcon">
                        <i class="bi bi-journal-plus fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-black text-main-theme" id="evaluationModalLabel">
                            <?= __('add_new_evaluation') ?></h5>
                        <p class="text-muted-theme small mb-0 opacity-75" id="evalModalSubtext">Définissez les
                            paramètres de l'évaluation</p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="evaluationForm" action="/sequences/store" method="POST">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                <div class="modal-body p-4">

                    <!-- Choix du Type d'enseignement -->
                    <div class="mb-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">
                            Type d'Enseignement <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="bi bi-diagram-3"></i></span>
                            <select name="teaching_type_id" id="eval_teaching_type_id" class="form-select premium-input"
                                required onchange="handleTeachingTypeChange()">
                                <option value="" disabled selected>Sélectionner un type...</option>
                                <?php foreach ($teachingTypes as $tt): ?>
                                    <option value="<?= $tt['id'] ?>" data-code="<?= htmlspecialchars($tt['code']) ?>"
                                        data-nom="<?= htmlspecialchars($tt['nom']) ?>">
                                        <?= htmlspecialchars((string) $tt['nom']) ?>
                                        (<?= htmlspecialchars((string) $tt['code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- BLOC SECONDAIRE (Formulaire Actuel) -->
                    <div id="bloc_secondaire" class="row g-3">
                        <div class="col-md-6">
                            <label
                                class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('sequence_code') ?>
                                *</label>
                            <div class="input-group-modern">
                                <span class="input-group-text-modern"><i class="bi bi-hash"></i></span>
                                <input type="text" name="code" id="eval_code_sec" class="form-control premium-input"
                                    placeholder="Ex: SEQ1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label
                                class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('position_order') ?>
                                *</label>
                            <div class="input-group-modern">
                                <span class="input-group-text-modern"><i class="bi bi-sort-numeric-down"></i></span>
                                <input type="number" name="position" id="eval_position_sec"
                                    class="form-control premium-input" placeholder="Ex: 1">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label
                                class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('sequence_label') ?>
                                *</label>
                            <div class="input-group-modern">
                                <span class="input-group-text-modern"><i class="bi bi-fonts"></i></span>
                                <input type="text" name="label" id="eval_label_sec" class="form-control premium-input"
                                    placeholder="Ex: Trimestre 1 - Séquence 1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label
                                class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('Short_Label') ?>
                                *</label>
                            <div class="input-group-modern">
                                <span class="input-group-text-modern"><i class="bi bi-tag"></i></span>
                                <input type="text" name="short_label" id="eval_short_label_sec"
                                    class="form-control premium-input" placeholder="Ex: SEQ 1" maxlength="20">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label
                                class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('trimester') ?>
                                *</label>
                            <div class="input-group-modern">
                                <span class="input-group-text-modern"><i class="bi bi-calendar-range"></i></span>
                                <select name="trimestre" id="eval_trimestre_sec" class="form-select premium-input">
                                    <option value="1">Trimestre 1</option>
                                    <option value="2">Trimestre 2</option>
                                    <option value="3">Trimestre 3</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- BLOC SUPÉRIEUR (Champs Spécifiques) -->
                    <div id="bloc_superieur" class="row g-3 d-none">
                        <div class="col-md-4">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Code de
                                l'évaluation *</label>
                            <div class="input-group-modern">
                                <span class="input-group-text-modern"><i class="bi bi-hash"></i></span>
                                <input type="text" name="code" id="eval_code_sup" class="form-control premium-input"
                                    placeholder="Ex: CC1, EXAMEN_S1">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Libellé
                                de l'évaluation *</label>
                            <div class="input-group-modern">
                                <span class="input-group-text-modern"><i class="bi bi-fonts"></i></span>
                                <input type="text" name="label" id="eval_label_sup" class="form-control premium-input"
                                    placeholder="Ex: Contrôle Continu Semestre 1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Libellé
                                court *</label>
                            <div class="input-group-modern">
                                <span class="input-group-text-modern"><i class="bi bi-tag"></i></span>
                                <input type="text" name="short_label" id="eval_short_label_sup"
                                    class="form-control premium-input" placeholder="Ex: CC S1" maxlength="20">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Ordre de
                                position *</label>
                            <div class="input-group-modern">
                                <span class="input-group-text-modern"><i class="bi bi-sort-numeric-down"></i></span>
                                <input type="number" name="position" id="eval_position_sup"
                                    class="form-control premium-input" placeholder="Ex: 1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Période :
                                Date de début</label>
                            <div class="input-group-modern">
                                <span class="input-group-text-modern"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" name="start_date" id="eval_start_date_sup"
                                    class="form-control premium-input">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Période :
                                Date de fin</label>
                            <div class="input-group-modern">
                                <span class="input-group-text-modern"><i class="bi bi-calendar-check"></i></span>
                                <input type="date" name="end_date" id="eval_end_date_sup"
                                    class="form-control premium-input">
                            </div>
                        </div>
                    </div>

                    <div class="form-check form-switch mt-4" id="eval_active_wrapper">
                        <input class="form-check-input" type="checkbox" name="is_active" id="eval_is_active" value="1"
                            checked>
                        <label class="form-check-label fw-bold text-main-theme small" for="eval_is_active">
                            Évaluation active
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
                            id="evalSubmitBtnText"><?= __('save') ?></span>
                    </button>
                </div>
            </form>
        </div>
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

    .scale-on-hover:hover {
        transform: scale(1.05);
    }

    /* Modal Inputs & Contrast */
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
</style>

<script>
    function handleTeachingTypeChange() {
        const ttSelect = document.getElementById('eval_teaching_type_id');
        const selectedOption = ttSelect.options[ttSelect.selectedIndex];
        const code = selectedOption ? selectedOption.getAttribute('data-code') : '';
        const nom = selectedOption ? selectedOption.getAttribute('data-nom') : '';

        const blocSec = document.getElementById('bloc_secondaire');
        const blocSup = document.getElementById('bloc_superieur');

        const isSuperior = code === 'EST' || code === 'SUP' || (nom && nom.toLowerCase().includes('supérieur'));

        if (isSuperior) {
            blocSec.classList.add('d-none');
            blocSup.classList.remove('d-none');

            // Input requirements
            document.getElementById('eval_code_sup').required = true;
            document.getElementById('eval_label_sup').required = true;
            document.getElementById('eval_short_label_sup').required = true;
            document.getElementById('eval_position_sup').required = true;

            document.getElementById('eval_code_sec').required = false;
            document.getElementById('eval_label_sec').required = false;
            document.getElementById('eval_short_label_sec').required = false;
            document.getElementById('eval_position_sec').required = false;
        } else {
            blocSup.classList.add('d-none');
            blocSec.classList.remove('d-none');

            // Input requirements
            document.getElementById('eval_code_sec').required = true;
            document.getElementById('eval_label_sec').required = true;
            document.getElementById('eval_short_label_sec').required = true;
            document.getElementById('eval_position_sec').required = true;

            document.getElementById('eval_code_sup').required = false;
            document.getElementById('eval_label_sup').required = false;
            document.getElementById('eval_short_label_sup').required = false;
            document.getElementById('eval_position_sup').required = false;
        }
    }

    function openCreateEvaluationModal() {
        const form = document.getElementById('evaluationForm');
        if (!form) return;
        form.action = '/sequences/store';

        document.getElementById('evaluationModalLabel').textContent = "<?= addslashes(__('add_new_evaluation')) ?>";
        document.getElementById('evalModalSubtext').textContent = "Définissez les paramètres de l'évaluation";
        document.getElementById('evalSubmitBtnText').textContent = "<?= addslashes(__('create_evaluation')) ?>";

        // Set default teaching type if filtered on index page
        const filterSelect = document.getElementById('index_teaching_type_filter');
        const filterVal = filterSelect ? filterSelect.value : '';
        const ttSelect = document.getElementById('eval_teaching_type_id');

        if (filterVal && Array.from(ttSelect.options).some(o => o.value === filterVal)) {
            ttSelect.value = filterVal;
        } else if (ttSelect.options.length > 1) {
            ttSelect.selectedIndex = 1;
        }

        document.getElementById('eval_code_sec').value = '';
        document.getElementById('eval_label_sec').value = '';
        document.getElementById('eval_short_label_sec').value = '';
        document.getElementById('eval_position_sec').value = '1';
        document.getElementById('eval_trimestre_sec').value = '1';

        document.getElementById('eval_code_sup').value = '';
        document.getElementById('eval_label_sup').value = '';
        document.getElementById('eval_short_label_sup').value = '';
        document.getElementById('eval_position_sup').value = '1';
        document.getElementById('eval_start_date_sup').value = '';
        document.getElementById('eval_end_date_sup').value = '';

        document.getElementById('eval_is_active').checked = true;

        handleTeachingTypeChange();

        const modal = new bootstrap.Modal(document.getElementById('evaluationModal'));
        modal.show();
    }

    function openEditEvaluationModal(evalData) {
        const form = document.getElementById('evaluationForm');
        if (!form || !evalData) return;
        form.action = '/sequences/update?id=' + evalData.id;

        document.getElementById('evaluationModalLabel').textContent = "<?= addslashes(__('edit_evaluation')) ?>";
        document.getElementById('evalModalSubtext').textContent = evalData.label || '';
        document.getElementById('evalSubmitBtnText').textContent = "<?= addslashes(__('save')) ?>";

        const ttSelect = document.getElementById('eval_teaching_type_id');
        if (evalData.teaching_type_id) {
            ttSelect.value = evalData.teaching_type_id;
        } else if (ttSelect.options.length > 1) {
            ttSelect.selectedIndex = 1;
        }

        handleTeachingTypeChange();

        const selectedOption = ttSelect.options[ttSelect.selectedIndex];
        const code = selectedOption ? selectedOption.getAttribute('data-code') : '';
        const nom = selectedOption ? selectedOption.getAttribute('data-nom') : '';
        const isSuperior = code === 'EST' || code === 'SUP' || (nom && nom.toLowerCase().includes('supérieur'));

        if (isSuperior) {
            document.getElementById('eval_code_sup').value = evalData.code || '';
            document.getElementById('eval_label_sup').value = evalData.label || '';
            document.getElementById('eval_short_label_sup').value = evalData.short_label || '';
            document.getElementById('eval_position_sup').value = evalData.position || 1;
            document.getElementById('eval_start_date_sup').value = evalData.start_date || '';
            document.getElementById('eval_end_date_sup').value = evalData.end_date || '';
        } else {
            document.getElementById('eval_code_sec').value = evalData.code || '';
            document.getElementById('eval_label_sec').value = evalData.label || '';
            document.getElementById('eval_short_label_sec').value = evalData.short_label || '';
            document.getElementById('eval_position_sec').value = evalData.position || 1;
            document.getElementById('eval_trimestre_sec').value = evalData.trimestre || 1;
        }

        document.getElementById('eval_is_active').checked = evalData.is_active == 1;

        const modal = new bootstrap.Modal(document.getElementById('evaluationModal'));
        modal.show();
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>