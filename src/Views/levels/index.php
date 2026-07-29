<?php $title = __('academic_levels') ?? 'Niveaux d\'Enseignement'; ob_start(); ?>

<div class="animate-fade-in container-fluid py-4">

    <!-- BARRE D'ACTIONS ET FILTRES : Style Floating Island -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-column flex-md-row gap-3">
        <form method="GET" action="/levels" class="d-flex gap-2 align-items-center w-100 w-md-auto flex-wrap">
            <div class="input-group input-group-sm style-search-group" style="max-width: 280px;">
                <span class="input-group-text bg-transparent border-end-0 rounded-start-pill border-theme-light ps-3">
                    <i class="bi bi-search text-muted-theme"></i>
                </span>
                <input type="text" name="q" class="form-control border-start-0 rounded-end-pill border-theme-light bg-transparent extra-small fw-bold"
                       placeholder="<?= __('search_placeholder') ?? 'Rechercher un niveau...' ?>" value="<?= htmlspecialchars((string) ($q ?? '')) ?>"
                       oninput="debouncedSubmit(this.form)">
            </div>

            <select name="teaching_type_id" class="form-select form-select-sm rounded-pill px-3 border-theme-light style-select" onchange="this.form.submit()">
                <option value=""><?= __('all_teaching_types') ?? 'Tous les types d\'enseignement' ?></option>
                <?php foreach ($teachingTypes as $tt): ?>
                    <option value="<?= $tt['id'] ?>" <?= (int) ($teaching_type_id ?? 0) === (int) $tt['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) $tt['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="status" class="form-select form-select-sm rounded-pill px-3 border-theme-light style-select" onchange="this.form.submit()">
                <option value=""><?= __('all_status') ?? 'Tous les statuts' ?></option>
                <option value="1" <?= isset($status) && $status === 1 ? 'selected' : '' ?>><?= __('active') ?? 'Actif' ?></option>
                <option value="0" <?= isset($status) && $status === 0 ? 'selected' : '' ?>><?= __('inactive') ?? 'Inactif' ?></option>
            </select>
        </form>

        <?php if (in_array(App\Core\Session::get('user_role'), ['superadmin', 'admin'])): ?>
            <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm scale-on-hover text-nowrap" onclick="openCreateLevelModal()">
                <i class="bi bi-plus-lg me-2"></i> <?= __('add_level') ?? 'Ajouter un niveau' ?>
            </button>
        <?php endif; ?>
    </div>

    <!-- LISTE DES NIVEAUX (Grille harmonisée) -->
    <?php if (empty($levels)): ?>
        <div class="text-center py-5">
            <div class="mb-3 text-muted-theme opacity-50">
                <i class="bi bi-bar-chart-steps display-4"></i>
            </div>
            <h5 class="fw-bold text-main-theme"><?= __('no_results_found') ?? 'Aucun niveau trouvé.' ?></h5>
            <p class="text-muted-theme small"><?= __('try_adjusting_search') ?? 'Essayez de modifier vos critères de recherche.' ?></p>
        </div>
    <?php else: ?>
        <div class="row g-2 g-md-4">
            <?php foreach ($levels as $num => $lvl): ?>
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                    <div class="subject-card-compact border-theme-dynamic h-100 position-relative <?= ($lvl['status'] ?? 1) ? '' : 'opacity-75' ?>">
                        <div class="subject-card-glow"></div>
                        <div class="card-body p-3 h-100 position-relative d-flex flex-column justify-content-between gap-2" style="z-index: 1;">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-black rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                                style="width: 36px; height: 36px; font-size: 0.85rem;">
                                                <?= htmlspecialchars((string) $lvl['code']) ?>
                                            </div>
                                        </div>
                                        <div class="overflow-hidden">
                                            <h6 class="fw-bold lh-1 text-main-theme m-0 text-truncate"
                                                style="font-size: 0.92rem;"
                                                title="<?= htmlspecialchars((string) $lvl['libelle_fr']) ?> / <?= htmlspecialchars((string) $lvl['libelle_en']) ?>">
                                                <?= App\Core\Translator::lang() === 'en' ? htmlspecialchars((string) $lvl['libelle_en']) : htmlspecialchars((string) $lvl['libelle_fr']) ?>
                                            </h6>
                                            <div class="extra-small text-muted-theme opacity-75 text-truncate mt-1" style="font-size: 0.75rem;">
                                                Code: <strong><?= htmlspecialchars((string) $lvl['code']) ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-1 align-items-center" style="z-index: 10;">
                                        <?php if (in_array(App\Core\Session::get('user_role'), ['superadmin', 'admin'])): ?>
                                            <a href="/levels/toggle?id=<?= $lvl['id'] ?>"
                                                class="btn-icon-action <?= ($lvl['status'] ?? 1) ? 'text-warning' : 'text-success' ?> position-relative"
                                                style="z-index: 10; width: 30px; height: 30px; font-size: 0.85rem;"
                                                title="<?= ($lvl['status'] ?? 1) ? 'Désactiver' : 'Activer' ?>">
                                                <i class="bi <?= ($lvl['status'] ?? 1) ? 'bi-eye-slash-fill' : 'bi-eye-fill' ?>"></i>
                                            </a>
                                            <button type="button" class="btn-icon-action text-primary position-relative border-0 bg-transparent"
                                                style="z-index: 10; width: 30px; height: 30px; font-size: 0.85rem;"
                                                onclick="openEditLevelModal(<?= htmlspecialchars(json_encode([
                                                    'id' => (int)$lvl['id'],
                                                    'code' => $lvl['code'],
                                                    'libelle_fr' => $lvl['libelle_fr'],
                                                    'libelle_en' => $lvl['libelle_en'],
                                                    'teaching_type_id' => (int)($lvl['teaching_type_id'] ?? 0),
                                                    'status' => (int)($lvl['status'] ?? 1)
                                                ]), ENT_QUOTES, 'UTF-8') ?>)" title="<?= __('edit') ?>">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <a href="/levels/delete?id=<?= $lvl['id'] ?>&csrf_token=<?= \App\Core\Session::generateCsrfToken() ?>"
                                                class="btn-icon-action text-danger position-relative btn-confirm-delete"
                                                style="z-index: 10; width: 30px; height: 30px; font-size: 0.85rem;"
                                                data-confirm="<?= __('confirm_delete_text') ?? 'Êtes-vous sûr de vouloir supprimer ce niveau ?' ?>" title="<?= __('delete') ?>">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Dual language display -->
                                <div class="small text-muted-theme mt-2 border-top border-theme-light pt-2" style="font-size: 0.78rem;">
                                    <div><i class="bi bi-translate text-primary me-1"></i> <strong>FR :</strong> <?= htmlspecialchars((string)$lvl['libelle_fr']) ?></div>
                                    <div><i class="bi bi-translate text-info me-1"></i> <strong>EN :</strong> <?= htmlspecialchars((string)$lvl['libelle_en']) ?></div>
                                </div>
                            </div>

                            <!-- Info Badge Row -->
                            <div class="mt-2 d-flex flex-wrap gap-1 align-items-center justify-content-between">
                                <?php if ($lvl['status'] ?? 1): ?>
                                    <div class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 extra-small px-2 py-1 rounded-pill fw-bold">
                                        <i class="bi bi-check-circle-fill me-1"></i><?= __('active') ?? 'Actif' ?>
                                    </div>
                                <?php else: ?>
                                    <div class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10 extra-small px-2 py-1 rounded-pill fw-bold">
                                        <i class="bi bi-x-circle-fill me-1"></i><?= __('inactive') ?? 'Inactif' ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($lvl['teaching_type_nom'])): ?>
                                    <div class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 extra-small px-2 py-1 rounded-pill fw-bold">
                                        <i class="bi bi-diagram-3 me-1"></i><?= htmlspecialchars((string) $lvl['teaching_type_nom']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL CRÉATION / ÉDITION NIVEAU -->
<div class="modal fade" id="levelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-primary text-white p-4">
                <h5 class="modal-title fw-black" id="levelModalTitle">
                    <i class="bi bi-bar-chart-steps me-2"></i><?= __('add_level') ?? 'Ajouter un niveau' ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="levelForm" action="/levels/store" method="POST">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold extra-small text-uppercase text-muted-theme"><?= __('teaching_type') ?? 'Type d\'enseignement' ?> *</label>
                        <select name="teaching_type_id" id="modal_teaching_type_id" class="form-select premium-input border-primary border-opacity-25" required>
                            <option value=""><?= __('select_teaching_type') ?? '-- Sélectionner un type --' ?></option>
                            <?php foreach ($teachingTypes as $tt): ?>
                                <option value="<?= $tt['id'] ?>"><?= htmlspecialchars((string) $tt['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold extra-small text-uppercase text-muted-theme"><?= __('level_code') ?? 'Code du Niveau' ?> *</label>
                        <input type="text" name="code" id="modal_code" class="form-control premium-input text-uppercase" placeholder="Ex: SIL, 6EME, L1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold extra-small text-uppercase text-muted-theme"><?= __('label_fr') ?? 'Libellé (Français)' ?> *</label>
                        <input type="text" name="libelle_fr" id="modal_libelle_fr" class="form-control premium-input" placeholder="Ex: Section d'Initiation à la Lecture" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold extra-small text-uppercase text-muted-theme"><?= __('label_en') ?? 'Libellé (Anglais)' ?> *</label>
                        <input type="text" name="libelle_en" id="modal_libelle_en" class="form-control premium-input" placeholder="Ex: Class 1 / Level 1" required>
                    </div>

                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="status" id="modal_status" value="1" checked>
                        <label class="form-check-label fw-bold text-main-theme" for="modal_status"><?= __('active') ?? 'Actif' ?></label>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal"><?= __('cancel') ?? 'Annuler' ?></button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"><?= __('save') ?? 'Enregistrer' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let searchTimeout;
function debouncedSubmit(form) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        form.submit();
    }, 400);
}

function openCreateLevelModal() {
    document.getElementById('levelModalTitle').innerHTML = '<i class="bi bi-bar-chart-steps me-2"></i><?= __('add_level') ?? 'Ajouter un niveau' ?>';
    const form = document.getElementById('levelForm');
    form.action = '/levels/store';
    document.getElementById('modal_teaching_type_id').value = '';
    document.getElementById('modal_code').value = '';
    document.getElementById('modal_libelle_fr').value = '';
    document.getElementById('modal_libelle_en').value = '';
    document.getElementById('modal_status').checked = true;
    
    const modal = new bootstrap.Modal(document.getElementById('levelModal'));
    modal.show();
}

function openEditLevelModal(level) {
    document.getElementById('levelModalTitle').innerHTML = '<i class="bi bi-pencil-fill me-2"></i><?= __('edit_level') ?? 'Modifier le niveau' ?>';
    const form = document.getElementById('levelForm');
    form.action = '/levels/update?id=' + level.id;
    document.getElementById('modal_teaching_type_id').value = level.teaching_type_id || '';
    document.getElementById('modal_code').value = level.code || '';
    document.getElementById('modal_libelle_fr').value = level.libelle_fr || '';
    document.getElementById('modal_libelle_en').value = level.libelle_en || '';
    document.getElementById('modal_status').checked = (parseInt(level.status) === 1);
    
    const modal = new bootstrap.Modal(document.getElementById('levelModal'));
    modal.show();
}
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../templates/layout.php'; ?>
