<?php 
$title = __('teaching_types') ?? 'Types d\'Enseignement'; 
ob_start(); 

$canManage = \App\Core\PermissionManager::hasPermission('manage_teaching_types');
$filters = [
    'q' => $q ?? '',
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
                    <i class="bi bi-diagram-3 fs-4 text-primary"></i>
                </div>
                <div>
                    <h1 class="fw-black fs-4 text-main-theme mb-1 lh-1">
                        <?= __('teaching_types') ?? 'Types d\'Enseignement' ?>
                    </h1>
                    <p class="text-muted-theme mb-0 fw-medium opacity-75" style="font-size: 0.88rem;">
                        <?= __('lang') === 'en' ? 'Configure and organize major educational subsystems of your institution' : 'Configurez et organisez les grands types d\'enseignement de votre établissement' ?>
                    </p>
                </div>
            </div>
            
            <?php if ($canManage): ?>
            <div class="d-flex flex-row w-100 w-md-auto justify-content-end ms-md-auto gap-2 mt-2 mt-md-0">
                <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm flex-grow-1 flex-md-grow-0 d-flex justify-content-center align-items-center gap-2 text-nowrap scale-on-hover" onclick="openCreateTeachingTypeModal()">
                    <i class="bi bi-plus-lg"></i> 
                    <span><?= __('add_teaching_type') ?? 'Ajouter un type' ?></span>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- BARRE DE FILTRES ET RECHERCHE INSTANTANÉE -->
    <div class="filter-island-container mb-4">
        <div class="filter-island p-3 rounded-4 shadow-sm">
            <form method="GET" action="/teaching_types" id="tt-filter-form" class="filter-form w-100 m-0">
                <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between">

                    <div class="d-flex flex-column flex-sm-row gap-2 flex-grow-1">
                        <!-- Recherche instantanée -->
                        <div class="dept-search-pill flex-grow-1 position-relative">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" name="q" id="search-input" class="form-control dept-filter-input ps-5"
                                value="<?= htmlspecialchars((string) ($filters['q'] ?? '')) ?>"
                                placeholder="<?= __('search') ?? 'Rechercher' ?> (Intitulé, Code)...">
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
                        <a href="/teaching_types" class="btn btn-light-theme rounded-circle p-2 d-flex align-items-center justify-content-center reset-btn scale-on-hover" style="width: 42px; height: 42px;" title="<?= __('reset') ?? 'Réinitialiser' ?>">
                            <i class="bi bi-arrow-counterclockwise fs-5"></i>
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- GRILLE DES TYPES D'ENSEIGNEMENT -->
    <div class="row g-4" id="teaching-types-grid">
        <?php foreach ($teachingTypes as $num => $type): ?>
            <div class="col-12 col-md-6 col-xl-4 tt-card-item">
                <div class="subject-card-compact border-theme-dynamic h-100 position-relative <?= ($canManage && !$type['actif']) ? 'opacity-75' : '' ?>">
                    <div class="subject-card-glow"></div>
                    <div class="card-body p-4 position-relative d-flex flex-column justify-content-between h-100" style="z-index: 1;">
                        <div>
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-3 overflow-hidden">
                                    <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-black rounded-4 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0"
                                        style="width: 54px; height: 54px; font-size: 1.2rem;">
                                        <i class="bi bi-diagram-3"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <h5 class="fw-black m-0 text-main-theme text-truncate" title="<?= htmlspecialchars((string) $type['nom']) ?>">
                                            <?= htmlspecialchars((string) $type['nom']) ?>
                                        </h5>
                                        <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                            <span class="badge bg-soft-primary text-primary extra-small fw-bold px-2 py-1 rounded-pill">
                                                <?= htmlspecialchars((string) $type['code']) ?>
                                            </span>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary extra-small fw-bold px-2 py-1 rounded-pill border border-secondary border-opacity-10">
                                                Position: <?= (int)($type['position'] ?? 0) ?>
                                            </span>
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
                                            <button type="button" class="dropdown-item dropdown-item-modern border-0 bg-transparent text-start w-100" onclick="openEditTeachingTypeModal(<?= htmlspecialchars(json_encode([
                                                'id' => (int)$type['id'],
                                                'nom' => $type['nom'],
                                                'code' => $type['code'],
                                                'position' => (int)($type['position'] ?? 0),
                                                'actif' => (int)($type['actif'] ?? 1)
                                            ]), ENT_QUOTES, 'UTF-8') ?>)">
                                                <i class="bi bi-pencil text-primary"></i> <?= __('edit') ?? 'Modifier' ?>
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item dropdown-item-modern border-0 bg-transparent text-start w-100" onclick="confirmToggleTeachingType(<?= (int)$type['id'] ?>, <?= htmlspecialchars(json_encode($type['nom']), ENT_QUOTES, 'UTF-8') ?>, <?= $type['actif'] ? 'true' : 'false' ?>)">
                                                <i class="bi <?= $type['actif'] ? 'bi-eye-slash text-warning' : 'bi-eye text-success' ?>"></i> 
                                                <?= $type['actif'] ? 'Désactiver le type' : 'Activer le type' ?>
                                            </button>
                                        </li>
                                        <?php if (App\Core\Session::get('user_role') === 'superadmin'): ?>
                                            <?php if ($type['code'] !== 'SEC00'): ?>
                                            <li>
                                                <a class="dropdown-item dropdown-item-modern text-danger border-0 bg-transparent text-start w-100 btn-confirm-delete"
                                                   href="/teaching_types/delete?id=<?= $type['id'] ?>&csrf_token=<?= \App\Core\Session::generateCsrfToken() ?>"
                                                   data-confirm="<?= __('confirm_delete_text') ?? 'Voulez-vous supprimer ce type d\'enseignement ?' ?>">
                                                    <i class="bi bi-trash text-danger"></i> <?= __('delete') ?? 'Supprimer' ?>
                                                </a>
                                            </li>
                                            <?php else: ?>
                                            <li>
                                                <span class="dropdown-item dropdown-item-modern text-secondary opacity-50 cursor-not-allowed">
                                                    <i class="bi bi-shield-lock-fill me-1"></i> Système protégé
                                                </span>
                                            </li>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top border-theme-light d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($type['actif']): ?>
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
                                CODE: <?= htmlspecialchars((string)$type['code']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($teachingTypes)): ?>
            <div class="col-12">
                <div class="subject-card-compact p-5 text-center border-dashed">
                    <i class="bi bi-diagram-3 fs-1 opacity-25 mb-3 d-block"></i>
                    <h5 class="text-muted"><?= __('no_data') ?? 'Aucune donnée disponible' ?></h5>
                    <?php if ($canManage): ?>
                        <p class="small text-muted mb-4">Commencez par créer le premier type d'enseignement de votre établissement.</p>
                        <button type="button" class="btn btn-primary rounded-pill px-4" onclick="openCreateTeachingTypeModal()"><?= __('add_teaching_type') ?? 'Ajouter un type' ?></button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL: Type d'enseignement (Création / Modification) -->
<?php if ($canManage): ?>
<div class="modal fade" id="teachingTypeModal" tabindex="-1" aria-labelledby="teachingTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: var(--bg-card);">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-init bg-primary text-white rounded-4 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px;" id="ttModalIcon">
                        <i class="bi bi-diagram-3 fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-black text-main-theme" id="teachingTypeModalLabel"><?= __('add_teaching_type') ?? 'Ajouter un type' ?></h5>
                        <p class="text-muted-theme small mb-0 opacity-75" id="ttModalSubtext">Formulaire de configuration du type d'enseignement</p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="teachingTypeForm" action="/teaching_types/store" method="POST">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">
                            Intitulé <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="bi bi-fonts"></i></span>
                            <input type="text" name="nom" id="tt_nom" class="form-control premium-input" 
                                   placeholder="ex: Enseignement Secondaire Général" required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">
                            Code <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="bi bi-hash"></i></span>
                            <input type="text" name="code" id="tt_code" class="form-control premium-input text-uppercase font-monospace" 
                                   placeholder="ex: ESG, ESTP" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">
                            Position d'affichage
                        </label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="bi bi-sort-numeric-down"></i></span>
                            <input type="number" name="position" id="tt_position" class="form-control premium-input" 
                                   placeholder="0" value="0">
                        </div>
                    </div>

                    <div class="p-3 rounded-3 bg-body-tertiary border border-theme-light mt-3">
                        <div class="form-check form-switch m-0 d-flex align-items-center justify-content-between ps-0">
                            <label class="form-check-label fw-bold text-main-theme cursor-pointer" for="tt_actif">
                                <i class="bi bi-power me-1.5 text-primary"></i> <?= __('active') ?? 'Actif' ?>
                            </label>
                            <input class="form-check-input ms-0 cursor-pointer" type="checkbox" name="actif" id="tt_actif" value="1" checked style="width: 2.5em; height: 1.25em;">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top-0 pt-0 px-4 pb-4 gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold scale-on-hover" data-bs-dismiss="modal">
                        <?= __('cancel') ?? 'Annuler' ?>
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm scale-on-hover">
                        <i class="bi bi-check-circle-fill me-2"></i> <span id="ttSubmitBtnText"><?= __('save') ?? 'Enregistrer' ?></span>
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
                        <h5 class="modal-title fw-black text-main-theme" id="confirmToggleModalLabel">Désactiver le type</h5>
                        <p class="text-muted-theme small mb-0 opacity-75" id="toggleModalTTName"></p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="alert alert-warning border-0 rounded-3 mb-0" id="toggleModalAlert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <span id="toggleModalMessage">
                        Attention : La désactivation de ce type d'enseignement impactera l'ensemble des modules associés.
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

    .cursor-not-allowed {
        cursor: not-allowed !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input');
    const statusSelect = document.getElementById('status-select');
    const filterForm = document.getElementById('tt-filter-form');
    let debounceTimer;

    if (searchInput && filterForm) {
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                filterForm.submit();
            }, 400);
        });
    }

    if (statusSelect && filterForm) {
        statusSelect.addEventListener('change', function () {
            filterForm.submit();
        });
    }
});

function openCreateTeachingTypeModal() {
    const form = document.getElementById('teachingTypeForm');
    if (!form) return;
    form.action = '/teaching_types/store';
    document.getElementById('teachingTypeModalLabel').textContent = "<?= addslashes(__('add_teaching_type') ?? 'Ajouter un type') ?>";
    document.getElementById('ttModalSubtext').textContent = "Formulaire de configuration du type d'enseignement";
    document.getElementById('ttSubmitBtnText').textContent = "<?= addslashes(__('save') ?? 'Enregistrer') ?>";
    document.getElementById('ttModalIcon').innerHTML = '<i class="bi bi-diagram-3 fs-4"></i>';
    document.getElementById('tt_nom').value = '';
    document.getElementById('tt_code').value = '';
    document.getElementById('tt_position').value = '0';
    document.getElementById('tt_actif').checked = true;

    const modal = new bootstrap.Modal(document.getElementById('teachingTypeModal'));
    modal.show();
}

function openEditTeachingTypeModal(tt) {
    const form = document.getElementById('teachingTypeForm');
    if (!form || !tt) return;
    form.action = '/teaching_types/update?id=' + tt.id;
    document.getElementById('teachingTypeModalLabel').textContent = "<?= addslashes(__('edit_teaching_type') ?? 'Modifier le type') ?>";
    document.getElementById('ttModalSubtext').textContent = (tt.nom || '') + " (" + (tt.code || '') + ")";
    document.getElementById('ttSubmitBtnText').textContent = "<?= addslashes(__('save') ?? 'Enregistrer') ?>";
    document.getElementById('ttModalIcon').innerHTML = '<i class="bi bi-pencil-square fs-4"></i>';
    document.getElementById('tt_nom').value = tt.nom || '';
    document.getElementById('tt_code').value = tt.code || '';
    document.getElementById('tt_position').value = tt.position || 0;
    document.getElementById('tt_actif').checked = (parseInt(tt.actif) === 1);

    const modal = new bootstrap.Modal(document.getElementById('teachingTypeModal'));
    modal.show();
}

function confirmToggleTeachingType(ttId, ttName, isCurrentlyActive) {
    const confirmBtn = document.getElementById('toggleConfirmBtn');
    if (!confirmBtn) return;
    
    confirmBtn.href = '/teaching_types/toggle?id=' + ttId;
    document.getElementById('toggleModalTTName').textContent = ttName;

    if (isCurrentlyActive) {
        document.getElementById('confirmToggleModalLabel').textContent = "Désactiver le type d'enseignement";
        document.getElementById('toggleModalMessage').textContent = "Attention : La désactivation de ce type d'enseignement impactera l'ensemble des modules associés.";
        document.getElementById('toggleConfirmBtnText').textContent = "Désactiver le type";
        confirmBtn.className = "btn btn-warning rounded-pill px-4 fw-bold shadow-sm scale-on-hover text-dark";
    } else {
        document.getElementById('confirmToggleModalLabel').textContent = "Activer le type d'enseignement";
        document.getElementById('toggleModalMessage').textContent = "Réactiver ce type d'enseignement le rendra de nouveau disponible dans l'ensemble de l'application.";
        document.getElementById('toggleConfirmBtnText').textContent = "Activer le type";
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
