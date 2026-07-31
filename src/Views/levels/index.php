<?php 
$title = __('academic_levels') ?? 'Niveaux d\'Enseignement'; 
ob_start(); 

$canManage = \App\Core\PermissionManager::hasRole(['superadmin', 'admin']);
$filters = [
    'q' => $q ?? '',
    'teaching_type_id' => $teaching_type_id ?? '',
    'status' => $status ?? ''
];
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">

    <!-- EN-TÊTE DE PAGE : Style Glassmorphism Premium avec support Mode Sombre -->
    <div class="dept-header-card mb-4 p-3 p-md-4 rounded-4 shadow-sm position-relative overflow-hidden">
        <div class="dept-header-bg"></div>
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between w-100 gap-3 position-relative" style="z-index: 2;">
            <div class="d-flex align-items-center gap-3">
                <div class="dept-icon-wrapper rounded-4 d-flex align-items-center justify-content-center flex-shrink-0">
                    <i class="bi bi-bar-chart-steps fs-4 text-primary"></i>
                </div>
                <div>
                    <h1 class="fw-black fs-4 text-main-theme mb-1 lh-1">
                        <?= __('academic_levels') ?? 'Niveaux d\'Enseignement' ?>
                    </h1>
                    <p class="text-muted-theme mb-0 fw-medium opacity-75" style="font-size: 0.88rem;">
                        <?= __('lang') === 'en' ? 'Manage academic levels and educational pathways' : 'Gérez les niveaux scolaires, académiques et parcours d\'enseignement' ?>
                    </p>
                </div>
            </div>
            
            <?php if ($canManage): ?>
            <div class="d-flex flex-row w-100 w-md-auto justify-content-end ms-md-auto gap-2 mt-2 mt-md-0">
                <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm flex-grow-1 flex-md-grow-0 d-flex justify-content-center align-items-center gap-2 text-nowrap scale-on-hover" onclick="openCreateLevelModal()">
                    <i class="bi bi-plus-lg"></i> 
                    <span><?= __('add_level') ?? 'Ajouter un niveau' ?></span>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- BARRE DE FILTRES ET RECHERCHE INSTANTANÉE -->
    <div class="filter-island-container mb-4">
        <div class="filter-island p-3 rounded-4 shadow-sm">
            <form method="GET" action="/levels" id="level-filter-form" class="filter-form w-100 m-0">
                <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between">

                    <div class="d-flex flex-column flex-sm-row gap-2 flex-grow-1">
                        <!-- Recherche instantanée -->
                        <div class="dept-search-pill flex-grow-1 position-relative">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" name="q" id="search-input" class="form-control dept-filter-input ps-5"
                                value="<?= htmlspecialchars((string) ($filters['q'] ?? '')) ?>"
                                placeholder="<?= __('search') ?? 'Rechercher' ?> (<?= __('level_code') ?? 'Code' ?>, <?= __('label_fr') ?? 'Libellé' ?>)...">
                        </div>

                        <!-- Type Enseignement -->
                        <div class="dept-select-wrapper" style="min-width: 200px;">
                            <select name="teaching_type_id" id="teaching-type-select" class="form-select dept-filter-select">
                                <option value=""><?= __('all_teaching_types') ?? 'Tous les types d\'enseignement' ?></option>
                                <?php foreach ($teachingTypes as $tt): ?>
                                    <option value="<?= $tt['id'] ?>" <?= (int) ($filters['teaching_type_id'] ?? 0) === (int) $tt['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $tt['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Statut -->
                        <div class="dept-select-wrapper" style="min-width: 140px;">
                            <select name="status" id="status-select" class="form-select dept-filter-select">
                                <option value=""><?= __('all_status') ?? 'Tous les statuts' ?></option>
                                <option value="1" <?= isset($filters['status']) && $filters['status'] === '1' ? 'selected' : '' ?>><?= __('active') ?? 'Actif' ?></option>
                                <option value="0" <?= isset($filters['status']) && $filters['status'] === '0' ? 'selected' : '' ?>><?= __('inactive') ?? 'Inactif' ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Actions Filtre -->
                    <div class="d-flex gap-2 align-items-center justify-content-end">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm text-nowrap scale-on-hover">
                            <i class="bi bi-funnel-fill me-1"></i> <?= __('filter') ?? 'Filtrer' ?>
                        </button>
                        <a href="/levels" class="btn btn-light-theme rounded-circle p-2 d-flex align-items-center justify-content-center reset-btn scale-on-hover" style="width: 42px; height: 42px;" title="<?= __('reset') ?? 'Réinitialiser' ?>">
                            <i class="bi bi-arrow-counterclockwise fs-5"></i>
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- GRILLE DES NIVEAUX -->
    <div class="row g-4" id="levels-grid">
        <?php foreach ($levels as $lvl): ?>
            <div class="col-12 col-md-6 col-xl-4 level-card-item">
                <div class="subject-card-compact border-theme-dynamic h-100 position-relative <?= ($canManage && !($lvl['status'] ?? 1)) ? 'opacity-75' : '' ?>">
                    <div class="subject-card-glow"></div>
                    <div class="card-body p-4 position-relative d-flex flex-column justify-content-between h-100" style="z-index: 1;">
                        <div>
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-3 overflow-hidden">
                                    <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-black rounded-4 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0"
                                        style="width: 54px; height: 54px; font-size: 1.1rem;">
                                        <?= htmlspecialchars((string) mb_substr($lvl['code'], 0, 4)) ?>
                                    </div>
                                    <div class="overflow-hidden">
                                        <h5 class="fw-black m-0 text-main-theme text-truncate" title="<?= htmlspecialchars((string) $lvl['libelle_fr']) ?>">
                                            <?= App\Core\Translator::lang() === 'en' ? htmlspecialchars((string) $lvl['libelle_en']) : htmlspecialchars((string) $lvl['libelle_fr']) ?>
                                        </h5>
                                        <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                            <span class="badge bg-soft-primary text-primary extra-small fw-bold px-2 py-1 rounded-pill">
                                                <?= htmlspecialchars((string) $lvl['code']) ?>
                                            </span>
                                            <?php if (!empty($lvl['teaching_type_nom'])): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success extra-small fw-bold px-2 py-1 rounded-pill border border-success border-opacity-10 text-truncate" style="max-width: 150px;">
                                                    <i class="bi bi-diagram-3 me-1"></i> <?= htmlspecialchars((string) $lvl['teaching_type_nom']) ?>
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
                                            <button type="button" class="dropdown-item dropdown-item-modern border-0 bg-transparent text-start w-100" onclick="openEditLevelModal(<?= htmlspecialchars(json_encode([
                                                'id' => (int)$lvl['id'],
                                                'code' => $lvl['code'],
                                                'libelle_fr' => $lvl['libelle_fr'],
                                                'libelle_en' => $lvl['libelle_en'],
                                                'teaching_type_id' => (int)($lvl['teaching_type_id'] ?? 0),
                                                'status' => (int)($lvl['status'] ?? 1)
                                            ]), ENT_QUOTES, 'UTF-8') ?>)">
                                                <i class="bi bi-pencil text-primary"></i> <?= __('edit') ?? 'Modifier' ?>
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item dropdown-item-modern border-0 bg-transparent text-start w-100" onclick="confirmToggleLevel(<?= (int)$lvl['id'] ?>, <?= htmlspecialchars(json_encode($lvl['libelle_fr']), ENT_QUOTES, 'UTF-8') ?>, <?= ($lvl['status'] ?? 1) ? 'true' : 'false' ?>)">
                                                <i class="bi <?= ($lvl['status'] ?? 1) ? 'bi-eye-slash text-warning' : 'bi-eye text-success' ?>"></i> 
                                                <?= ($lvl['status'] ?? 1) ? 'Désactiver le niveau' : 'Activer le niveau' ?>
                                            </button>
                                        </li>
                                        <?php if (\App\Core\Session::get('user_role') === 'superadmin'): ?>
                                        <li>
                                            <a class="dropdown-item dropdown-item-modern text-danger border-0 bg-transparent text-start w-100 btn-confirm-delete"
                                               href="/levels/delete?id=<?= $lvl['id'] ?>&csrf_token=<?= \App\Core\Session::generateCsrfToken() ?>"
                                               data-confirm="<?= __('confirm_delete_text') ?? 'Voulez-vous supprimer ce niveau ?' ?>">
                                                <i class="bi bi-trash text-danger"></i> <?= __('delete') ?? 'Supprimer' ?>
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Bloc Libellés FR & EN -->
                            <div class="mt-3 p-3 rounded-3 bg-body-tertiary border border-theme-light extra-small">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="text-muted-theme opacity-75 fw-bold"><i class="bi bi-translate text-primary me-1.5"></i> FR</span>
                                    <span class="fw-semibold text-main-theme text-truncate ms-2" title="<?= htmlspecialchars((string)$lvl['libelle_fr']) ?>"><?= htmlspecialchars((string)$lvl['libelle_fr']) ?></span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="text-muted-theme opacity-75 fw-bold"><i class="bi bi-translate text-info me-1.5"></i> EN</span>
                                    <span class="fw-semibold text-main-theme text-truncate ms-2" title="<?= htmlspecialchars((string)$lvl['libelle_en']) ?>"><?= htmlspecialchars((string)$lvl['libelle_en']) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top border-theme-light d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($lvl['status'] ?? 1): ?>
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
                                CODE: <?= htmlspecialchars((string)$lvl['code']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($levels)): ?>
            <div class="col-12">
                <div class="subject-card-compact p-5 text-center border-dashed">
                    <i class="bi bi-bar-chart-steps fs-1 opacity-25 mb-3 d-block"></i>
                    <h5 class="text-muted"><?= __('no_data') ?? 'Aucune donnée disponible' ?></h5>
                    <?php if ($canManage): ?>
                        <p class="small text-muted mb-4">Commencez par créer le premier niveau d'enseignement de votre établissement.</p>
                        <button type="button" class="btn btn-primary rounded-pill px-4" onclick="openCreateLevelModal()"><?= __('add_level') ?? 'Ajouter un niveau' ?></button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL: Niveau (Création / Modification) -->
<?php if ($canManage): ?>
<div class="modal fade" id="levelModal" tabindex="-1" aria-labelledby="levelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: var(--bg-card);">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-init bg-primary text-white rounded-4 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px;" id="levelModalIcon">
                        <i class="bi bi-bar-chart-steps fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-black text-main-theme" id="levelModalLabel"><?= __('add_level') ?? 'Ajouter un niveau' ?></h5>
                        <p class="text-muted-theme small mb-0 opacity-75" id="levelModalSubtext">Formulaire de configuration de niveau académique</p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="levelForm" action="/levels/store" method="POST">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">
                            <?= __('teaching_type') ?? 'Type Enseignement' ?> <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="bi bi-diagram-3"></i></span>
                            <select name="teaching_type_id" id="modal_teaching_type_id" class="form-select premium-input" required>
                                <option value="" disabled selected><?= __('select_teaching_type') ?? 'Sélectionner le type' ?></option>
                                <?php foreach ($teachingTypes as $tt): ?>
                                    <option value="<?= $tt['id'] ?>">
                                        <?= htmlspecialchars((string) $tt['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">
                            <?= __('level_code') ?? 'Code du Niveau' ?> <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="bi bi-qr-code"></i></span>
                            <input type="text" name="code" id="modal_code" class="form-control premium-input text-uppercase font-monospace" 
                                   placeholder="ex: 6EME, SIL, L1" required autofocus>
                        </div>
                        <small class="text-muted extra-small mt-1 d-block opacity-75">
                            <i class="bi bi-info-circle me-1"></i> Identifiant unique court pour ce niveau d'enseignement.
                        </small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">
                            <?= __('label_fr') ?? 'Libellé (Français)' ?> <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="bi bi-fonts"></i></span>
                            <input type="text" name="libelle_fr" id="modal_libelle_fr" class="form-control premium-input" 
                                   placeholder="ex: Sixième, SIL" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">
                            <?= __('label_en') ?? 'Libellé (Anglais)' ?> <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="bi bi-translate"></i></span>
                            <input type="text" name="libelle_en" id="modal_libelle_en" class="form-control premium-input" 
                                   placeholder="ex: Form 1, Class 1" required>
                        </div>
                    </div>

                    <div class="p-3 rounded-3 bg-body-tertiary border border-theme-light">
                        <div class="form-check form-switch m-0 d-flex align-items-center justify-content-between ps-0">
                            <label class="form-check-label fw-bold text-main-theme cursor-pointer" for="modal_status">
                                <i class="bi bi-power me-1.5 text-primary"></i> <?= __('active') ?? 'Actif' ?>
                            </label>
                            <input class="form-check-input ms-0 cursor-pointer" type="checkbox" name="status" id="modal_status" value="1" checked style="width: 2.5em; height: 1.25em;">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top-0 pt-0 px-4 pb-4 gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold scale-on-hover" data-bs-dismiss="modal">
                        <?= __('cancel') ?? 'Annuler' ?>
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm scale-on-hover">
                        <i class="bi bi-check-circle-fill me-2"></i> <span id="levelSubmitBtnText"><?= __('save') ?? 'Enregistrer' ?></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- MODAL: Confirmation de Désactivation / Activation -->
<?php if ($canManage): ?>
<div class="modal fade" id="confirmToggleModal" tabindex="-1" aria-labelledby="confirmToggleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: var(--bg-card);">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-init bg-warning bg-opacity-10 text-warning rounded-4 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px;" id="toggleModalIcon">
                        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-black text-main-theme" id="confirmToggleModalLabel">Désactiver le niveau</h5>
                        <p class="text-muted-theme small mb-0 opacity-75" id="toggleModalLevelName"></p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="alert alert-warning border-0 rounded-3 mb-0" id="toggleModalAlert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <span id="toggleModalMessage">
                        Attention : La désactivation de ce niveau le masquera des sélecteurs de création de classe et formulaires actifs.
                    </span>
                </div>
            </div>

            <div class="modal-footer border-top-0 pt-0 px-4 pb-4 gap-2">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold scale-on-hover" data-bs-dismiss="modal">
                    <?= __('cancel') ?? 'Annuler' ?>
                </button>
                <a href="#" id="toggleConfirmBtn" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm scale-on-hover text-dark">
                    <i class="bi bi-check-circle-fill me-1"></i> <span id="toggleConfirmBtnText">Confirmer</span>
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
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

    .animate-slide-down {
        animation: slideDown 0.5s cubic-bezier(0.23, 1, 0.32, 1);
    }

    @keyframes slideDown {
        from { transform: translateY(-15px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .border-dashed { border-style: dashed !important; border-width: 2px !important; }

    /* Modal Inputs & Contrast */
    .input-group-modern {
        display: flex;
        align-items: center;
        min-height: 52px;
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
        height: 50px !important;
        min-height: 50px !important;
        padding: 10px 0 !important;
        box-shadow: none !important;
        color: var(--text-main, #0f172a) !important;
        font-weight: 600;
        font-size: 0.95rem;
    }

    select.premium-input {
        height: 50px !important;
        min-height: 50px !important;
        padding-right: 25px !important;
        cursor: pointer;
    }

    [data-theme="dark"] .premium-input {
        color: #f8fafc !important;
    }

    [data-theme="dark"] .modal-content {
        background: #1e293b !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: #f8fafc;
    }

    [data-theme="dark"] .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input');
    const teachingTypeSelect = document.getElementById('teaching-type-select');
    const statusSelect = document.getElementById('status-select');
    const filterForm = document.getElementById('level-filter-form');
    let debounceTimer;

    if (searchInput && filterForm) {
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                filterForm.submit();
            }, 400);
        });
    }

    if (teachingTypeSelect && filterForm) {
        teachingTypeSelect.addEventListener('change', function () {
            filterForm.submit();
        });
    }

    if (statusSelect && filterForm) {
        statusSelect.addEventListener('change', function () {
            filterForm.submit();
        });
    }
});

function openCreateLevelModal() {
    const form = document.getElementById('levelForm');
    if (!form) return;
    form.action = '/levels/store';
    document.getElementById('levelModalLabel').textContent = "<?= addslashes(__('add_level') ?? 'Ajouter un niveau') ?>";
    document.getElementById('levelModalSubtext').textContent = "Formulaire de configuration de niveau académique";
    document.getElementById('levelSubmitBtnText').textContent = "<?= addslashes(__('save') ?? 'Enregistrer') ?>";
    document.getElementById('levelModalIcon').innerHTML = '<i class="bi bi-bar-chart-steps fs-4"></i>';
    
    document.getElementById('modal_teaching_type_id').value = '';
    document.getElementById('modal_code').value = '';
    document.getElementById('modal_libelle_fr').value = '';
    document.getElementById('modal_libelle_en').value = '';
    document.getElementById('modal_status').checked = true;

    const modal = new bootstrap.Modal(document.getElementById('levelModal'));
    modal.show();
}

function openEditLevelModal(level) {
    const form = document.getElementById('levelForm');
    if (!form || !level) return;
    form.action = '/levels/update?id=' + level.id;
    document.getElementById('levelModalLabel').textContent = "<?= addslashes(__('edit_level') ?? 'Modifier le niveau') ?>";
    document.getElementById('levelModalSubtext').textContent = (level.libelle_fr || '') + " (" + (level.code || '') + ")";
    document.getElementById('levelSubmitBtnText').textContent = "<?= addslashes(__('save') ?? 'Enregistrer') ?>";
    document.getElementById('levelModalIcon').innerHTML = '<i class="bi bi-pencil-square fs-4"></i>';

    document.getElementById('modal_teaching_type_id').value = level.teaching_type_id || '';
    document.getElementById('modal_code').value = level.code || '';
    document.getElementById('modal_libelle_fr').value = level.libelle_fr || '';
    document.getElementById('modal_libelle_en').value = level.libelle_en || '';
    document.getElementById('modal_status').checked = (parseInt(level.status) === 1);

    const modal = new bootstrap.Modal(document.getElementById('levelModal'));
    modal.show();
}

function confirmToggleLevel(levelId, levelName, isCurrentlyActive) {
    const confirmBtn = document.getElementById('toggleConfirmBtn');
    if (!confirmBtn) return;
    
    confirmBtn.href = '/levels/toggle?id=' + levelId;
    document.getElementById('toggleModalLevelName').textContent = levelName;

    if (isCurrentlyActive) {
        document.getElementById('confirmToggleModalLabel').textContent = "Désactiver le niveau";
        document.getElementById('toggleModalMessage').textContent = "Attention : La désactivation de ce niveau le masquera immédiatement des sélecteurs de création de classe et formulaires actifs sans supprimer l'historique. Voulez-vous vraiment désactiver ce niveau ?";
        document.getElementById('toggleConfirmBtnText').textContent = "Désactiver le niveau";
        confirmBtn.className = "btn btn-warning rounded-pill px-4 fw-bold shadow-sm scale-on-hover text-dark";
    } else {
        document.getElementById('confirmToggleModalLabel').textContent = "Activer le niveau";
        document.getElementById('toggleModalMessage').textContent = "Réactiver ce niveau le rendra de nouveau disponible dans l'ensemble des formulaires et sélecteurs de l'application.";
        document.getElementById('toggleConfirmBtnText').textContent = "Activer le niveau";
        confirmBtn.className = "btn btn-success rounded-pill px-4 fw-bold shadow-sm scale-on-hover text-white";
    }

    const modal = new bootstrap.Modal(document.getElementById('confirmToggleModal'));
    modal.show();
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>


