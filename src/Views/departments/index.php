<?php $title = __('departments'); ob_start(); ?>

<div class="animate-fade-in container-fluid py-3 px-md-4">

    <!-- EN-TÊTE DE PAGE : Style Glassmorphism Premium avec support Mode Sombre -->
    <div class="dept-header-card mb-4 p-3 p-md-4 rounded-4 shadow-sm position-relative overflow-hidden">
        <div class="dept-header-bg"></div>
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between w-100 gap-3 position-relative" style="z-index: 2;">
            <div class="d-flex align-items-center gap-3">
                <div class="dept-icon-wrapper rounded-4 d-flex align-items-center justify-content-center flex-shrink-0">
                    <i class="bi bi-building-fill fs-4 text-primary"></i>
                </div>
                <div>
                    <h1 class="fw-black fs-4 text-main-theme mb-1 lh-1">
                        <?= __('departments') ?>
                    </h1>
                    <p class="text-muted-theme mb-0 fw-medium opacity-75" style="font-size: 0.88rem;">
                        <?= __('lang') === 'en' ? 'Manage pedagogical departments and academic units' : 'Gérez les départements pédagogiques et unités d\'enseignement' ?>
                    </p>
                </div>
            </div>
            
            <?php if (\App\Core\PermissionManager::hasPermission('manage_departments')): ?>
            <div class="d-flex flex-row w-100 w-md-auto justify-content-end ms-md-auto gap-2 mt-2 mt-md-0">
                <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm flex-grow-1 flex-md-grow-0 d-flex justify-content-center align-items-center gap-2 text-nowrap scale-on-hover" onclick="openCreateDepartmentModal()">
                    <i class="bi bi-plus-lg"></i> 
                    <span><?= __('add_department') ?></span>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- BARRE DE FILTRES ET RECHERCHE INSTANTANÉE -->
    <div class="filter-island-container mb-4">
        <div class="filter-island p-3 rounded-4 shadow-sm">
            <form method="GET" action="/departments" id="dept-filter-form" class="filter-form w-100 m-0">
                <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between">

                    <div class="d-flex flex-column flex-sm-row gap-2 flex-grow-1">
                        <!-- Recherche instantanée -->
                        <div class="dept-search-pill flex-grow-1 position-relative">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" name="q" id="search-input" class="form-control dept-filter-input ps-5"
                                value="<?= htmlspecialchars((string) ($filters['q'] ?? '')) ?>"
                                placeholder="<?= __('search') ?> (<?= __('department_name') ?>, <?= __('code') ?>)...">
                        </div>

                        <!-- Type Enseignement -->
                        <div class="dept-select-wrapper" style="min-width: 200px;">
                            <select name="teaching_type_id" id="teaching-type-select" class="form-select dept-filter-select">
                                <option value=""><?= __('all_teaching_types') ?></option>
                                <?php foreach ($teachingTypes as $tt): ?>
                                    <option value="<?= $tt['id'] ?>" <?= (int) ($filters['teaching_type_id'] ?? 0) === (int) $tt['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $tt['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Actions Filtre -->
                    <div class="d-flex gap-2 align-items-center justify-content-end">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm text-nowrap scale-on-hover">
                            <i class="bi bi-funnel-fill me-1"></i> <?= __('filter') ?>
                        </button>
                        <a href="/departments" class="btn btn-light-theme rounded-circle p-2 d-flex align-items-center justify-content-center reset-btn scale-on-hover" style="width: 42px; height: 42px;" title="<?= __('reset') ?>">
                            <i class="bi bi-arrow-counterclockwise fs-5"></i>
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- GRILLE DES DÉPARTEMENTS -->
    <div class="row g-4" id="departments-grid">
        <?php 
        $canManage = \App\Core\PermissionManager::hasPermission('manage_departments');
        foreach ($departments as $dept): 
        ?>
            <div class="col-12 col-md-6 col-xl-4 department-card-item">
                <div class="subject-card-compact border-theme-dynamic h-100 position-relative <?= ($canManage && !$dept['status']) ? 'opacity-75' : '' ?>">
                    <div class="subject-card-glow"></div>
                    <div class="card-body p-4 position-relative" style="z-index: 1;">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-black rounded-4 d-flex align-items-center justify-content-center shadow-sm"
                                    style="width: 54px; height: 54px; font-size: 1.2rem;">
                                    <?= mb_substr($dept['nom'], 0, 1) ?>
                                </div>
                                <div>
                                    <h5 class="fw-black m-0 text-main-theme"><?= h($dept['nom']) ?></h5>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <span class="badge bg-soft-primary text-primary extra-small fw-bold px-2 py-1 rounded-pill">
                                            <?= h($dept['code']) ?>
                                        </span>
                                        <?php if (!empty($dept['teaching_type_nom'])): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success extra-small fw-bold px-2 py-1 rounded-pill border border-success border-opacity-10">
                                                <i class="bi bi-diagram-3 me-1"></i> <?= h($dept['teaching_type_nom']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($canManage): ?>
                            <div class="dropdown">
                                <button class="btn btn-link text-muted p-0 shadow-none border-0" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical fs-5"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2">
                                    <li>
                                        <button type="button" class="dropdown-item dropdown-item-modern border-0 bg-transparent text-start w-100" onclick="openEditDepartmentModal(<?= htmlspecialchars(json_encode([
                                            'id' => (int)$dept['id'],
                                            'nom' => $dept['nom'],
                                            'code' => $dept['code'],
                                            'teaching_type_id' => (int)$dept['teaching_type_id'],
                                            'teaching_form_id' => (int)($dept['teaching_form_id'] ?? 0)
                                        ]), ENT_QUOTES, 'UTF-8') ?>)">
                                            <i class="bi bi-pencil text-primary"></i> <?= __('edit') ?>
                                        </button>
                                    </li>
                                    <li>
                                        <button type="button" class="dropdown-item dropdown-item-modern border-0 bg-transparent text-start w-100" onclick="confirmToggleDepartment(<?= (int)$dept['id'] ?>, <?= htmlspecialchars(json_encode($dept['nom']), ENT_QUOTES, 'UTF-8') ?>, <?= $dept['status'] ? 'true' : 'false' ?>)">
                                            <i class="bi <?= $dept['status'] ? 'bi-eye-slash text-warning' : 'bi-eye text-success' ?>"></i> 
                                            <?= $dept['status'] ? __('deactivate_department') : __('activate_department') ?>
                                        </button>
                                    </li>
                                    <?php if (\App\Core\Session::get('user_role') === 'superadmin'): ?>
                                    <li>
                                        <a class="dropdown-item dropdown-item-modern text-danger border-0 bg-transparent text-start w-100 btn-confirm-delete"
                                           href="/departments/delete?id=<?= $dept['id'] ?>&csrf_token=<?= \App\Core\Session::generateCsrfToken() ?>"
                                           data-confirm="<?= __('confirm_delete_text') ?? 'Voulez-vous supprimer ce département ?' ?>">
                                            <i class="bi bi-trash text-danger"></i> <?= __('delete') ?>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="mt-4 pt-3 border-top border-theme-light d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($canManage): ?>
                                    <?php if ($dept['status']): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 rounded-pill extra-small px-3">
                                            <i class="bi bi-check-circle-fill me-1"></i> <?= __('active') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10 rounded-pill extra-small px-3">
                                            <i class="bi bi-x-circle-fill me-1"></i> <?= __('inactive') ?>
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="text-muted-theme extra-small opacity-75">
                                <i class="bi bi-calendar3 me-1"></i> <?= date('d/m/Y', strtotime($dept['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($departments)): ?>
            <div class="col-12">
                <div class="subject-card-compact p-5 text-center border-dashed">
                    <i class="bi bi-building fs-1 opacity-25 mb-3 d-block"></i>
                    <h5 class="text-muted"><?= __('no_data') ?></h5>
                    <?php if ($canManage): ?>
                        <p class="small text-muted mb-4"><?= __('no_department_help') ?? 'Commencez par créer le premier département de votre établissement.' ?></p>
                        <button type="button" class="btn btn-primary rounded-pill px-4" onclick="openCreateDepartmentModal()"><?= __('add_department') ?></button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL: Département (Création / Modification) -->
<?php if ($canManage): ?>
<div class="modal fade" id="departmentModal" tabindex="-1" aria-labelledby="departmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: var(--bg-card);">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-init bg-primary text-white rounded-4 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px;" id="deptModalIcon">
                        <i class="bi bi-building-add fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-black text-main-theme" id="departmentModalLabel"><?= __('add_department') ?></h5>
                        <p class="text-muted-theme small mb-0 opacity-75" id="deptModalSubtext"><?= __('department_details') ?></p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="departmentForm" action="/departments/store" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">
                            <?= __('department_name') ?> <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="bi bi-fonts"></i></span>
                            <input type="text" name="nom" id="dept_nom" class="form-control premium-input" 
                                   placeholder="ex: Département de Mathématiques" required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">
                            <?= __('department_code') ?> <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="bi bi-hash"></i></span>
                            <input type="text" name="code" id="dept_code" class="form-control premium-input" 
                                   placeholder="ex: MATHS" required>
                        </div>
                        <small class="text-muted extra-small mt-1 d-block opacity-75">
                            <i class="bi bi-info-circle me-1"></i> Identifiant court utilisé pour les filtres et les enregistrements.
                        </small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">
                            <?= __('teaching_type') ?? 'Type Enseignement' ?> <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="bi bi-diagram-3"></i></span>
                            <select name="teaching_type_id" id="dept_teaching_type_id" class="form-select premium-input" required>
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
                            <?= __('teaching_form') ?? 'Forme d\'enseignement' ?> <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="bi bi-bookmarks"></i></span>
                            <select name="teaching_form_id" id="dept_teaching_form_id" class="form-select premium-input" required disabled>
                                <option value="" disabled selected><?= __('select_teaching_form') ?? 'Sélectionner la forme' ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top-0 pt-0 px-4 pb-4 gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold scale-on-hover" data-bs-dismiss="modal">
                        <?= __('cancel') ?>
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm scale-on-hover">
                        <i class="bi bi-check-circle-fill me-2"></i> <span id="deptSubmitBtnText"><?= __('save') ?></span>
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
                        <h5 class="modal-title fw-black text-main-theme" id="confirmToggleModalLabel"><?= __('deactivate_department') ?></h5>
                        <p class="text-muted-theme small mb-0 opacity-75" id="toggleModalDeptName"></p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="alert alert-warning border-0 rounded-3 mb-0" id="toggleModalAlert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <span id="toggleModalMessage">
                        Attention : La désactivation de ce département masquera immédiatement ses classes et ses élèves de l'ensemble des registres, formulaires et filtres actifs de l'établissement. Les données historiques restent conservées.
                    </span>
                </div>
            </div>

            <div class="modal-footer border-top-0 pt-0 px-4 pb-4 gap-2">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold scale-on-hover" data-bs-dismiss="modal">
                    <?= __('cancel') ?>
                </button>
                <a href="#" id="toggleConfirmBtn" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm scale-on-hover text-dark">
                    <i class="bi bi-check-circle-fill me-1"></i> <span id="toggleConfirmBtnText"><?= __('deactivate_department') ?></span>
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
const teachingFormsByType = <?= json_encode($teachingFormsByType ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>;
const teachingFormPlaceholder = <?= json_encode(__('select_teaching_form') ?? 'Sélectionner la forme') ?>;
const noTeachingFormForTypeText = <?= json_encode(__('no_teaching_form_for_type') ?? 'Aucune forme d’enseignement disponible pour ce type') ?>;

function populateTeachingFormSelect(typeId, selectedFormId = null) {
    const select = document.getElementById('dept_teaching_form_id');
    if (!select) return;

    const forms = typeId && teachingFormsByType[typeId] ? teachingFormsByType[typeId] : [];
    select.innerHTML = '<option value="" disabled selected>' + teachingFormPlaceholder + '</option>';

    if (!forms.length) {
        select.disabled = true;
        select.innerHTML = '<option value="" disabled selected>' + noTeachingFormForTypeText + '</option>';
        return;
    }

    select.disabled = false;
    forms.forEach(function (form) {
        const option = document.createElement('option');
        option.value = String(form.id);
        option.textContent = form.nom + (form.code ? ' (' + form.code + ')' : '');
        if (String(selectedFormId) === String(form.id)) {
            option.selected = true;
        }
        select.appendChild(option);
    });

    if (selectedFormId !== null && selectedFormId !== '' && String(selectedFormId) !== '0') {
        select.value = String(selectedFormId);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input');
    const teachingTypeSelect = document.getElementById('teaching-type-select');
    const filterForm = document.getElementById('dept-filter-form');
    const deptTeachingTypeSelect = document.getElementById('dept_teaching_type_id');
    let debounceTimer;

    if (deptTeachingTypeSelect) {
        deptTeachingTypeSelect.addEventListener('change', function () {
            populateTeachingFormSelect(this.value, null);
        });
    }

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
});

function openCreateDepartmentModal() {
    const form = document.getElementById('departmentForm');
    if (!form) return;
    form.action = '/departments/store';
    document.getElementById('departmentModalLabel').textContent = "<?= addslashes(__('add_department')) ?>";
    document.getElementById('deptModalSubtext').textContent = "<?= addslashes(__('department_details')) ?>";
    document.getElementById('deptSubmitBtnText').textContent = "<?= addslashes(__('save')) ?>";
    document.getElementById('deptModalIcon').innerHTML = '<i class="bi bi-building-add fs-4"></i>';
    
    document.getElementById('dept_nom').value = '';
    document.getElementById('dept_code').value = '';
    const teachingTypeInput = document.getElementById('dept_teaching_type_id');
    teachingTypeInput.value = '';
    populateTeachingFormSelect('', null);

    const modal = new bootstrap.Modal(document.getElementById('departmentModal'));
    modal.show();
}

function openEditDepartmentModal(dept) {
    const form = document.getElementById('departmentForm');
    if (!form || !dept) return;
    form.action = '/departments/update?id=' + dept.id;
    document.getElementById('departmentModalLabel').textContent = "<?= addslashes(__('edit_department')) ?>";
    document.getElementById('deptModalSubtext').textContent = dept.nom || '';
    document.getElementById('deptSubmitBtnText').textContent = "<?= addslashes(__('save')) ?>";
    document.getElementById('deptModalIcon').innerHTML = '<i class="bi bi-pencil-square fs-4"></i>';

    document.getElementById('dept_nom').value = dept.nom || '';
    document.getElementById('dept_code').value = dept.code || '';
    const teachingTypeInput = document.getElementById('dept_teaching_type_id');
    teachingTypeInput.value = dept.teaching_type_id || '';
    populateTeachingFormSelect(dept.teaching_type_id || '', dept.teaching_form_id || null);

    const modal = new bootstrap.Modal(document.getElementById('departmentModal'));
    modal.show();
}

function confirmToggleDepartment(deptId, deptName, isCurrentlyActive) {
    const confirmBtn = document.getElementById('toggleConfirmBtn');
    if (!confirmBtn) return;
    
    confirmBtn.href = '/departments/toggle?id=' + deptId;
    document.getElementById('toggleModalDeptName').textContent = deptName;

    if (isCurrentlyActive) {
        document.getElementById('confirmToggleModalLabel').textContent = "<?= addslashes(__('deactivate_department')) ?>";
        document.getElementById('toggleModalMessage').textContent = "Attention : La désactivation de ce département masquera immédiatement ses classes et ses élèves rattachés dans l'ensemble des formulaires et listes actives de l'application sans supprimer les données historiques. Voulez-vous vraiment désactiver ce département ?";
        document.getElementById('toggleConfirmBtnText').textContent = "<?= addslashes(__('deactivate_department')) ?>";
        confirmBtn.className = "btn btn-warning rounded-pill px-4 fw-bold shadow-sm scale-on-hover text-dark";
    } else {
        document.getElementById('confirmToggleModalLabel').textContent = "<?= addslashes(__('activate_department')) ?>";
        document.getElementById('toggleModalMessage').textContent = "Réactiver ce département le rendra de nouveau disponible ainsi que ses classes et élèves dans les formulaires et listes actives.";
        document.getElementById('toggleConfirmBtnText').textContent = "<?= addslashes(__('activate_department')) ?>";
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
