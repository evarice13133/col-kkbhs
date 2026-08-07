<?php
$title = __('rbac_title') . " - NoteMaster";
ob_start();
?>

<div class="rbac-container">
    <!-- Header Hero Banner M365 Style -->
    <div class="rbac-header shadow-sm border-0 rounded-4 p-4 mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-1 rounded-pill">
                        <i class="bi bi-shield-lock-fill me-1"></i> Enterprise Security Center
                    </span>
                    <span class="badge bg-success-subtle text-success fw-semibold px-3 py-1 rounded-pill">
                        <i class="bi bi-check-circle-fill me-1"></i> RBAC Engine Active
                    </span>
                </div>
                <h1 class="h3 fw-bold mb-1 tracking-tight text-body"><?= __('rbac_title') ?></h1>
                <p class="text-secondary mb-0 small">
                    <?= __('rbac_subtitle') ?>
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-primary rounded-3 px-3 py-2 btn-sm fw-medium d-inline-flex align-items-center gap-2" id="btn-scan-app">
                    <i class="bi bi-cpu"></i> <span><?= __('scan_app') ?></span>
                </button>
                <button class="btn btn-primary rounded-3 px-3 py-2 btn-sm fw-medium d-inline-flex align-items-center gap-2" id="btn-create-backup">
                    <i class="bi bi-cloud-arrow-up"></i> <span><?= __('create_backup') ?></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Navigation par Onglets M365 -->
    <ul class="nav nav-pills custom-m365-tabs mb-4 p-1 rounded-3 bg-body-tertiary border" id="rbacTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-2 fw-semibold py-2 px-3 d-flex align-items-center gap-2" id="catalog-tab" data-bs-toggle="tab" data-bs-target="#tab-catalog" type="button" role="tab">
                <i class="bi bi-journal-text"></i> <span><?= __('catalog_tab') ?></span>
                <span class="badge bg-secondary-subtle text-body rounded-pill ms-1" id="count-catalog-perms">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-2 fw-semibold py-2 px-3 d-flex align-items-center gap-2" id="roles-tab" data-bs-toggle="tab" data-bs-target="#tab-roles" type="button" role="tab">
                <i class="bi bi-people-fill"></i> <span><?= __('roles_tab') ?></span>
                <span class="badge bg-secondary-subtle text-body rounded-pill ms-1" id="count-roles">0</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-2 fw-semibold py-2 px-3 d-flex align-items-center gap-2" id="users-tab" data-bs-toggle="tab" data-bs-target="#tab-users" type="button" role="tab">
                <i class="bi bi-person-gear"></i> <span><?= __('users_tab') ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-2 fw-semibold py-2 px-3 d-flex align-items-center gap-2" id="audit-tab" data-bs-toggle="tab" data-bs-target="#tab-audit" type="button" role="tab">
                <i class="bi bi-clock-history"></i> <span><?= __('audit_tab') ?></span>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="rbacTabsContent">
        <!-- ONGLET 1: CATALOGUE DES PERMISSIONS -->
        <div class="tab-pane fade show active" id="tab-catalog" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <!-- Barres de filtres & recherche -->
                <div class="row g-3 align-items-center mb-4">
                    <div class="col-12 col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" id="search-perm" placeholder="Rechercher une permission, code ou module...">
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <select class="form-select" id="filter-module">
                            <option value="">Tous les modules</option>
                            <option value="system">Système & Administration</option>
                            <option value="pedagogy">Pédagogie & Emplois du temps</option>
                            <option value="students">Élèves & Notes</option>
                            <option value="finance">Finances & Scolarité</option>
                            <option value="hr">Ressources Humaines</option>
                            <option value="general">Général</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <select class="form-select" id="filter-criticality">
                            <option value="">Toutes criticités</option>
                            <option value="low">Faible</option>
                            <option value="medium">Moyenne</option>
                            <option value="high">Élevée</option>
                            <option value="critical">Critique</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2 text-end">
                        <button class="btn btn-outline-secondary w-100 rounded-3" id="btn-reset-filters">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Réinitialiser
                        </button>
                    </div>
                </div>

                <!-- Accordéon arborescent par module -->
                <div class="accordion accordion-flush custom-rbac-accordion" id="accordionCatalog">
                    <div class="text-center py-5" id="catalog-loading">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <p class="text-muted">Chargement du catalogue des permissions...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ONGLET 2: GESTION PAR RÔLE -->
        <div class="tab-pane fade" id="tab-roles" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <div class="row g-3 mb-4 align-items-center">
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-bold text-muted small uppercase">Sélectionner un Rôle à configurer</label>
                        <select class="form-select form-select-lg rounded-3 fw-semibold text-primary border-primary-subtle" id="select-role-manage">
                            <!-- Populated dynamically -->
                        </select>
                    </div>
                    <div class="col-12 col-md-8 text-md-end d-flex flex-wrap justify-content-md-end gap-2 align-items-end">
                        <button class="btn btn-outline-secondary rounded-3 btn-sm" id="btn-select-all-role">
                            <i class="bi bi-check-all me-1"></i> Tout sélectionner
                        </button>
                        <button class="btn btn-outline-secondary rounded-3 btn-sm" id="btn-deselect-all-role">
                            <i class="bi bi-x-circle me-1"></i> Tout désélectionner
                        </button>
                        <button class="btn btn-outline-info rounded-3 btn-sm" id="btn-copy-role-modal" data-bs-toggle="modal" data-bs-target="#modalCopyRole">
                            <i class="bi bi-copy me-1"></i> Copier d'un rôle
                        </button>
                        <button class="btn btn-outline-warning rounded-3 btn-sm" id="btn-compare-role-modal" data-bs-toggle="modal" data-bs-target="#modalCompareRole">
                            <i class="bi bi-layout-split me-1"></i> Comparer 2 rôles
                        </button>
                        <button class="btn btn-outline-danger rounded-3 btn-sm" id="btn-reset-role">
                            <i class="bi bi-arrow-repeat me-1"></i> Profil par défaut
                        </button>
                        <button class="btn btn-success rounded-3 px-4 py-2 font-weight-bold shadow-sm d-inline-flex align-items-center gap-2" id="btn-save-role-perms">
                            <i class="bi bi-floppy-fill"></i> <span>Enregistrer le Rôle</span>
                        </button>
                    </div>
                </div>

                <!-- Description & Métadonnées du rôle sélectionné -->
                <div class="alert alert-primary-subtle border-0 rounded-3 p-3 mb-4 d-flex justify-content-between align-items-center" id="role-info-banner">
                    <div>
                        <h6 class="fw-bold mb-1" id="role-info-title">Rôle Sélectionné</h6>
                        <p class="mb-0 small text-muted" id="role-info-desc">Chargement des privilèges...</p>
                    </div>
                    <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill" id="role-info-count">0/0 permissions</span>
                </div>

                <!-- Liste interactive des permissions par module avec cases à cocher -->
                <div id="role-permissions-matrix" class="row g-3">
                    <!-- Populated dynamically -->
                </div>
            </div>
        </div>

        <!-- ONGLET 3: EXCEPTIONS UTILISATEURS -->
        <div class="tab-pane fade" id="tab-users" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <div class="row g-3 align-items-center mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold text-muted small">Rechercher un Utilisateur</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-person-search"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" id="search-user-input" placeholder="Tapez un nom, identifiant ou rôle...">
                            <button class="btn btn-primary px-3" id="btn-search-user">Chercher</button>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold text-muted small">Utilisateurs trouvés</label>
                        <select class="form-select" id="select-user-result">
                            <option value="">Sélectionnez un utilisateur dans la liste...</option>
                        </select>
                    </div>
                </div>

                <div id="user-override-container" style="display:none;">
                    <div class="card bg-body-tertiary border-0 rounded-4 p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="fw-bold mb-1 text-primary" id="user-target-name">Nom Utilisateur</h5>
                                <span class="badge bg-secondary rounded-pill me-2" id="user-target-role">Rôle: Enseignant</span>
                                <span class="text-muted small" id="user-target-email">email@etablissement.com</span>
                            </div>
                            <button class="btn btn-success rounded-3 px-4 py-2 shadow-sm d-inline-flex align-items-center gap-2" id="btn-save-user-overrides">
                                <i class="bi bi-floppy-fill"></i> <span>Enregistrer les exceptions</span>
                            </button>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 rounded-3 small mb-4">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>Priorité d'évaluation :</strong> Une exception individuelle sur un utilisateur prévaut TOUJOURS sur la permission attribuée à son rôle.
                    </div>

                    <div id="user-permissions-list" class="row g-3">
                        <!-- Populated dynamically -->
                    </div>
                </div>
            </div>
        </div>

        <!-- ONGLET 4: AUDIT & SAUVEGARDES -->
        <div class="tab-pane fade" id="tab-audit" role="tabpanel">
            <div class="row g-4">
                <!-- Colonne de gauche: Sauvegardes -->
                <div class="col-12 col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0"><i class="bi bi-cloud-check text-primary me-2"></i>Sauvegardes RBAC</h5>
                            <button class="btn btn-sm btn-outline-primary rounded-3" id="btn-refresh-backups"><i class="bi bi-arrow-repeat"></i></button>
                        </div>
                        <p class="text-muted small mb-4">
                            Sauvegardez l'état complet des permissions de vos rôles et restaures-y en 1 clic en cas d'erreur.
                        </p>
                        <div class="list-group list-group-flush rounded-3 border" id="backups-list">
                            <div class="text-center py-4 text-muted small">Aucune sauvegarde enregistrée.</div>
                        </div>
                    </div>
                </div>

                <!-- Colonne de droite: Journal d'Audit -->
                <div class="col-12 col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0"><i class="bi bi-journal-check text-success me-2"></i>Journal d'Audit Sécurité</h5>
                            <button class="btn btn-sm btn-outline-secondary rounded-3" id="btn-refresh-audit"><i class="bi bi-arrow-repeat"></i></button>
                        </div>
                        <p class="text-muted small mb-4">
                            Historique en temps réel de toutes les modifications apportées aux permissions système.
                        </p>
                        <div class="table-responsive" style="max-height: 450px;">
                            <table class="table table-hover align-middle table-sm small mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Date</th>
                                        <th>Utilisateur</th>
                                        <th>Action</th>
                                        <th>Détails</th>
                                    </tr>
                                </thead>
                                <tbody id="audit-log-rows">
                                    <tr><td colspan="4" class="text-center py-4 text-muted">Chargement de l'audit...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL COPIER RÔLE -->
<div class="modal fade" id="modalCopyRole" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-copy text-info me-2"></i>Copier les permissions d'un rôle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body py-4">
                <p class="text-muted small">
                    Copier les autorisations d'un rôle existant vers le rôle actuellement sélectionné.
                </p>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Rôle Source (Modèle)</label>
                    <select class="form-select" id="select-copy-source">
                        <!-- Populated -->
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Rôle Cible (Destination)</label>
                    <input type="text" class="form-control" id="text-copy-target" readonly>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-info rounded-3 font-weight-bold text-white" id="btn-confirm-copy-role">Appliquer la copie</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL COMPARER RÔLES -->
<div class="modal fade" id="modalCompareRole" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-layout-split text-warning me-2"></i>Comparaison Côte à Côte de 2 Rôles</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body py-4">
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <label class="form-label fw-bold small">Rôle A</label>
                        <select class="form-select" id="select-compare-role-1"></select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold small">Rôle B</label>
                        <select class="form-select" id="select-compare-role-2"></select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Permission</th>
                                <th>Module</th>
                                <th class="text-center" id="th-compare-role-1">Rôle A</th>
                                <th class="text-center" id="th-compare-role-2">Rôle B</th>
                            </tr>
                        </thead>
                        <tbody id="compare-matrix-rows">
                            <tr><td colspan="4" class="text-center py-4 text-muted">Sélectionnez deux rôles à comparer.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rbac-header {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(37, 99, 235, 0.02) 100%);
    border-left: 4px solid var(--bs-primary) !important;
}
.custom-m365-tabs .nav-link {
    color: var(--bs-secondary-color);
    transition: all 0.2s ease-in-out;
}
.custom-m365-tabs .nav-link.active {
    background-color: var(--bs-body-bg) !important;
    color: var(--bs-primary) !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
}
.badge-critical-low { background-color: rgba(16, 185, 129, 0.15); color: #059669; }
.badge-critical-medium { background-color: rgba(245, 158, 11, 0.15); color: #d97706; }
.badge-critical-high { background-color: rgba(239, 68, 68, 0.15); color: #dc2626; }
.badge-critical-critical { background-color: rgba(168, 85, 247, 0.18); color: #7e22ce; font-weight: 700; }

.perm-card-checkbox {
    border: 1px solid var(--bs-border-color);
    border-radius: 0.5rem;
    padding: 0.75rem;
    transition: all 0.15s ease-in-out;
    background-color: var(--bs-body-bg);
}
.perm-card-checkbox:hover {
    border-color: var(--bs-primary-border-subtle);
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.perm-card-checkbox.active {
    background-color: rgba(59, 130, 246, 0.04);
    border-color: var(--bs-primary);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let state = {
        permissions: [],
        roles: [],
        selectedRoleId: null,
        rolePermIds: [],
        selectedUserId: null,
        userOverrides: {},
        userRolePermIds: []
    };

    // Initialisation
    loadPermissionsCatalog();
    loadRolesList();
    loadAuditLogs();
    loadBackupsList();

    // Event Listeners Filtres Catalogue
    document.getElementById('search-perm').addEventListener('input', renderCatalogTree);
    document.getElementById('filter-module').addEventListener('change', renderCatalogTree);
    document.getElementById('filter-criticality').addEventListener('change', renderCatalogTree);
    document.getElementById('btn-reset-filters').addEventListener('click', function() {
        document.getElementById('search-perm').value = '';
        document.getElementById('filter-module').value = '';
        document.getElementById('filter-criticality').value = '';
        renderCatalogTree();
    });

    // Chargement du catalogue
    function loadPermissionsCatalog() {
        fetch('/api/rbac/permissions')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    state.permissions = data.data;
                    document.getElementById('count-catalog-perms').textContent = state.permissions.length;
                    renderCatalogTree();
                }
            });
    }

    // Chargement des Rôles
    function loadRolesList() {
        fetch('/api/rbac/roles')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    state.roles = data.data;
                    document.getElementById('count-roles').textContent = state.roles.length;
                    populateRoleSelects();
                    if (state.roles.length > 0 && !state.selectedRoleId) {
                        selectRoleToManage(state.roles[0].id);
                    }
                }
            });
    }

    function populateRoleSelects() {
        const selectManage = document.getElementById('select-role-manage');
        const selectCopySource = document.getElementById('select-copy-source');
        const selectComp1 = document.getElementById('select-compare-role-1');
        const selectComp2 = document.getElementById('select-compare-role-2');

        selectManage.innerHTML = '';
        selectCopySource.innerHTML = '';
        selectComp1.innerHTML = '<option value="">Choisir Rôle A</option>';
        selectComp2.innerHTML = '<option value="">Choisir Rôle B</option>';

        state.roles.forEach(r => {
            selectManage.innerHTML += `<option value="${r.id}">${r.role_name} (${r.perm_count} perms - ${r.user_count} utilisateurs)</option>`;
            selectCopySource.innerHTML += `<option value="${r.id}">${r.role_name}</option>`;
            selectComp1.innerHTML += `<option value="${r.id}">${r.role_name}</option>`;
            selectComp2.innerHTML += `<option value="${r.id}">${r.role_name}</option>`;
        });

        selectManage.value = state.selectedRoleId || (state.roles[0] ? state.roles[0].id : '');
    }

    function getTranslatedPermName(p) {
        if (!p) return '';
        if (p.perm_code === 'view_class_finances') {
            return '<?= addslashes((string)__('view_class_finances')) ?>';
        }
        if (p.perm_code === 'edit_class_finances') {
            return '<?= addslashes((string)__('edit_class_finances')) ?>';
        }
        return p.perm_name;
    }

    // Render Arborescence Catalogue
    function renderCatalogTree() {
        const search = document.getElementById('search-perm').value.toLowerCase().trim();
        const moduleFilter = document.getElementById('filter-module').value;
        const critFilter = document.getElementById('filter-criticality').value;

        const filtered = state.permissions.filter(p => {
            const matchSearch = !search || p.perm_code.toLowerCase().includes(search) || p.perm_name.toLowerCase().includes(search) || (p.description && p.description.toLowerCase().includes(search));
            const matchMod = !moduleFilter || p.module === moduleFilter;
            const matchCrit = !critFilter || p.criticality === critFilter;
            return matchSearch && matchMod && matchCrit;
        });

        const container = document.getElementById('accordionCatalog');
        if (filtered.length === 0) {
            container.innerHTML = '<div class="text-center py-5 text-muted"><i class="bi bi-search fs-2 d-block mb-2"></i>Aucune permission ne correspond à vos critères.</div>';
            return;
        }

        // Regrouper par module -> submodule
        const grouped = {};
        filtered.forEach(p => {
            const mod = p.module || 'general';
            const sub = p.submodule || 'general';
            if (!grouped[mod]) grouped[mod] = {};
            if (!grouped[mod][sub]) grouped[mod][sub] = [];
            grouped[mod][sub].push(p);
        });

        let html = '';
        let modIndex = 0;
        for (const mod in grouped) {
            modIndex++;
            const submodules = grouped[mod];
            let modTotal = 0;
            for (const s in submodules) modTotal += submodules[s].length;

            html += `
                <div class="accordion-item border rounded-3 mb-3 overflow-hidden">
                    <h2 class="accordion-header" id="headingMod${modIndex}">
                        <button class="accordion-button ${modIndex > 1 ? 'collapsed' : ''} bg-body-tertiary font-weight-bold text-capitalize" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMod${modIndex}">
                            <i class="bi bi-folder-fill text-primary me-2"></i> Module: ${mod}
                            <span class="badge bg-primary-subtle text-primary rounded-pill ms-2 font-monospace">${modTotal} permissions</span>
                        </button>
                    </h2>
                    <div id="collapseMod${modIndex}" class="accordion-collapse collapse ${modIndex === 1 ? 'show' : ''}" data-bs-parent="#accordionCatalog">
                        <div class="accordion-body p-3">
            `;

            for (const sub in submodules) {
                const perms = submodules[sub];
                html += `
                    <div class="mb-3">
                        <h6 class="fw-bold text-muted small text-uppercase mb-2"><i class="bi bi-layers me-1"></i>Sous-module : ${sub}</h6>
                        <div class="row g-2">
                `;
                perms.forEach(p => {
                    const critBadge = getCriticalityBadge(p.criticality);
                    html += `
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card h-100 border-0 shadow-sm bg-body p-3 rounded-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="font-monospace fw-bold small text-primary">${escapeHtml(p.perm_code)}</span>
                                    ${critBadge}
                                </div>
                                <h6 class="fw-bold mb-1 small">${escapeHtml(getTranslatedPermName(p))}</h6>
                                <p class="text-muted small mb-0">${escapeHtml(p.description || 'Pas de description')}</p>
                            </div>
                        </div>
                    `;
                });
                html += `</div></div>`;
            }

            html += `</div></div></div>`;
        }
        container.innerHTML = html;
    }

    function getCriticalityBadge(crit) {
        switch(crit) {
            case 'low': return '<span class="badge badge-critical-low rounded-pill">Faible</span>';
            case 'medium': return '<span class="badge badge-critical-medium rounded-pill">Moyenne</span>';
            case 'high': return '<span class="badge badge-critical-high rounded-pill">Élevée</span>';
            case 'critical': return '<span class="badge badge-critical-critical rounded-pill"><i class="bi bi-exclamation-triangle-fill me-1"></i>Critique</span>';
            default: return '<span class="badge bg-secondary-subtle text-body rounded-pill">Standard</span>';
        }
    }

    // Gestion par Rôle
    document.getElementById('select-role-manage').addEventListener('change', function() {
        selectRoleToManage(parseInt(this.value));
    });

    function selectRoleToManage(roleId) {
        state.selectedRoleId = roleId;
        const role = state.roles.find(r => r.id === roleId);
        if (!role) return;

        document.getElementById('text-copy-target').value = role.role_name;
        document.getElementById('role-info-title').textContent = role.role_name;
        document.getElementById('role-info-desc').textContent = role.description || 'Rôle système de NoteMaster';

        fetch(`/api/rbac/roles/permissions?role_id=${roleId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    state.rolePermIds = data.permission_ids.map(id => parseInt(id));
                    document.getElementById('role-info-count').textContent = `${state.rolePermIds.length} / ${state.permissions.length} permissions`;
                    renderRolePermissionsMatrix();
                }
            });
    }

    function renderRolePermissionsMatrix() {
        const container = document.getElementById('role-permissions-matrix');
        const grouped = {};
        state.permissions.forEach(p => {
            const mod = p.module || 'general';
            if (!grouped[mod]) grouped[mod] = [];
            grouped[mod].push(p);
        });

        let html = '';
        for (const mod in grouped) {
            const perms = grouped[mod];
            const modCheckedCount = perms.filter(p => state.rolePermIds.includes(p.id)).length;
            const allChecked = modCheckedCount === perms.length;

            html += `
                <div class="col-12">
                    <div class="card border rounded-3 p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="fw-bold mb-0 text-capitalize text-primary"><i class="bi bi-folder2-open me-2"></i>Module : ${mod}</h6>
                                <span class="badge bg-secondary-subtle text-body rounded-pill">${modCheckedCount}/${perms.length} sélectionnées</span>
                            </div>
                            <div>
                                <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 me-3 btn-select-module" data-module="${mod}">Tout cocher</button>
                                <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 text-muted btn-deselect-module" data-module="${mod}">Tout décocher</button>
                            </div>
                        </div>
                        <div class="row g-2">
            `;

            perms.forEach(p => {
                const isChecked = state.rolePermIds.includes(p.id);
                html += `
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="form-check perm-card-checkbox ${isChecked ? 'active' : ''}">
                            <input class="form-check-input role-perm-checkbox" type="checkbox" data-perm-id="${p.id}" id="chk_role_p_${p.id}" ${isChecked ? 'checked' : ''}>
                            <label class="form-check-label w-100 cursor-pointer" for="chk_role_p_${p.id}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold small">${escapeHtml(getTranslatedPermName(p))}</span>
                                    ${getCriticalityBadge(p.criticality)}
                                </div>
                                <span class="font-monospace text-muted d-block" style="font-size:0.75rem;">${escapeHtml(p.perm_code)}</span>
                            </label>
                        </div>
                    </div>
                `;
            });

            html += `</div></div></div>`;
        }
        container.innerHTML = html;

        // Listeners Checkboxes Rôle
        document.querySelectorAll('.role-perm-checkbox').forEach(chk => {
            chk.addEventListener('change', function() {
                const pId = parseInt(this.getAttribute('data-perm-id'));
                if (this.checked) {
                    if (!state.rolePermIds.includes(pId)) state.rolePermIds.push(pId);
                } else {
                    state.rolePermIds = state.rolePermIds.filter(id => id !== pId);
                }
                document.getElementById('role-info-count').textContent = `${state.rolePermIds.length} / ${state.permissions.length} permissions`;
                renderRolePermissionsMatrix();
            });
        });

        // Listeners Tout Cocher / Décocher par Module
        document.querySelectorAll('.btn-select-module').forEach(btn => {
            btn.addEventListener('click', function() {
                const mod = this.getAttribute('data-module');
                const pIds = state.permissions.filter(p => p.module === mod).map(p => p.id);
                pIds.forEach(id => {
                    if (!state.rolePermIds.includes(id)) state.rolePermIds.push(id);
                });
                renderRolePermissionsMatrix();
            });
        });
        document.querySelectorAll('.btn-deselect-module').forEach(btn => {
            btn.addEventListener('click', function() {
                const mod = this.getAttribute('data-module');
                const pIds = state.permissions.filter(p => p.module === mod).map(p => p.id);
                state.rolePermIds = state.rolePermIds.filter(id => !pIds.includes(id));
                renderRolePermissionsMatrix();
            });
        });
    }

    // Action Sauvegarder le Rôle
    document.getElementById('btn-save-role-perms').addEventListener('click', function() {
        if (!state.selectedRoleId) return;
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...';

        fetch('/api/rbac/roles/permissions', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                role_id: state.selectedRoleId,
                permission_ids: state.rolePermIds
            })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-floppy-fill"></i> <span>Enregistrer le Rôle</span>';
            if (data.success) {
                alert(data.message);
                loadRolesList();
                loadAuditLogs();
            } else {
                alert('Erreur: ' + data.message);
            }
        });
    });

    document.getElementById('btn-select-all-role').addEventListener('click', function() {
        state.rolePermIds = state.permissions.map(p => p.id);
        renderRolePermissionsMatrix();
    });
    document.getElementById('btn-deselect-all-role').addEventListener('click', function() {
        state.rolePermIds = [];
        renderRolePermissionsMatrix();
    });

    // Modal Copier Rôle
    document.getElementById('btn-confirm-copy-role').addEventListener('click', function() {
        const sourceId = parseInt(document.getElementById('select-copy-source').value);
        if (!sourceId || sourceId === state.selectedRoleId) {
            alert('Veuillez choisir un rôle source différent du rôle cible.');
            return;
        }

        fetch('/api/rbac/roles/copy', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                source_role_id: sourceId,
                target_role_id: state.selectedRoleId
            })
        })
        .then(res => res.json())
        .then(data => {
            bootstrap.Modal.getInstance(document.getElementById('modalCopyRole')).hide();
            if (data.success) {
                alert(data.message);
                selectRoleToManage(state.selectedRoleId);
            } else {
                alert('Erreur: ' + data.message);
            }
        });
    });

    // Modal Comparer Rôles
    document.getElementById('select-compare-role-1').addEventListener('change', compareRolesExecute);
    document.getElementById('select-compare-role-2').addEventListener('change', compareRolesExecute);

    function compareRolesExecute() {
        const r1 = document.getElementById('select-compare-role-1').value;
        const r2 = document.getElementById('select-compare-role-2').value;
        if (!r1 || !r2) return;

        fetch(`/api/rbac/roles/compare?role_id_1=${r1}&role_id_2=${r2}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('th-compare-role-1').textContent = data.role_1.role_name;
                    document.getElementById('th-compare-role-2').textContent = data.role_2.role_name;

                    let html = '';
                    data.comparison.forEach(item => {
                        html += `
                            <tr>
                                <td>
                                    <div class="fw-semibold">${escapeHtml(item.permission.perm_name)}</div>
                                    <span class="font-monospace text-muted small">${escapeHtml(item.permission.perm_code)}</span>
                                </td>
                                <td><span class="badge bg-secondary-subtle text-body">${escapeHtml(item.permission.module)}</span></td>
                                <td class="text-center">${item.role_1_has ? '<i class="bi bi-check-circle-fill text-success fs-5"></i>' : '<i class="bi bi-x-circle text-muted fs-5"></i>'}</td>
                                <td class="text-center">${item.role_2_has ? '<i class="bi bi-check-circle-fill text-success fs-5"></i>' : '<i class="bi bi-x-circle text-muted fs-5"></i>'}</td>
                            </tr>
                        `;
                    });
                    document.getElementById('compare-matrix-rows').innerHTML = html;
                }
            });
    }

    // Réinitialiser Rôle
    document.getElementById('btn-reset-role').addEventListener('click', function() {
        if (!state.selectedRoleId) return;
        if (!confirm('Voulez-vous réinitialiser ce rôle avec son profil système par défaut ?')) return;

        fetch('/api/rbac/roles/reset', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ role_id: state.selectedRoleId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                selectRoleToManage(state.selectedRoleId);
            }
        });
    });

    // Exceptions Utilisateurs
    document.getElementById('btn-search-user').addEventListener('click', executeUserSearch);
    document.getElementById('search-user-input').addEventListener('keypress', function(e) { if(e.key==='Enter') executeUserSearch(); });

    function executeUserSearch() {
        const query = document.getElementById('search-user-input').value.trim();
        fetch(`/api/rbac/users?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('select-user-result');
                    select.innerHTML = '<option value="">Sélectionnez un utilisateur dans la liste...</option>';
                    data.data.forEach(u => {
                        select.innerHTML += `<option value="${u.id}">${escapeHtml(u.name)} (${escapeHtml(u.role_name || u.role)}) - ${escapeHtml(u.email || u.username)}</option>`;
                    });
                }
            });
    }

    document.getElementById('select-user-result').addEventListener('change', function() {
        const uId = parseInt(this.value);
        if (!uId) return;

        fetch(`/api/rbac/users/permissions?user_id=${uId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    state.selectedUserId = uId;
                    state.userOverrides = data.overrides || {};
                    state.userRolePermIds = data.role_permission_ids.map(id => parseInt(id));

                    document.getElementById('user-target-name').textContent = data.user.name;
                    document.getElementById('user-target-role').textContent = 'Rôle: ' + (data.user.role_name || data.user.role);
                    document.getElementById('user-target-email').textContent = data.user.email || data.user.username;

                    document.getElementById('user-override-container').style.display = 'block';
                    renderUserPermissionsList();
                }
            });
    });

    function renderUserPermissionsList() {
        const container = document.getElementById('user-permissions-list');
        let html = '';

        state.permissions.forEach(p => {
            const roleHas = state.userRolePermIds.includes(p.id);
            const overrideVal = state.userOverrides[p.id] !== undefined ? parseInt(state.userOverrides[p.id]) : -1;

            html += `
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card border rounded-3 p-3 h-100">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="fw-semibold small">${escapeHtml(p.perm_name)}</span>
                            ${getCriticalityBadge(p.criticality)}
                        </div>
                        <span class="font-monospace text-muted mb-3 d-block" style="font-size:0.75rem;">${escapeHtml(p.perm_code)}</span>
                        
                        <div class="mt-auto">
                            <label class="form-label text-muted small fw-bold mb-1">Règle appliquée :</label>
                            <select class="form-select form-select-sm select-user-override" data-perm-id="${p.id}">
                                <option value="-1" ${overrideVal === -1 ? 'selected' : ''}>Hérité du Rôle (${roleHas ? 'AUTORISÉ' : 'INTERDIT'})</option>
                                <option value="1" ${overrideVal === 1 ? 'selected' : ''} class="text-success fw-bold">✓ ACCORDÉ (Surcharge Explicite)</option>
                                <option value="0" ${overrideVal === 0 ? 'selected' : ''} class="text-danger fw-bold">✗ INTERDIT (Surcharge Explicite)</option>
                            </select>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;

        document.querySelectorAll('.select-user-override').forEach(sel => {
            sel.addEventListener('change', function() {
                const pId = parseInt(this.getAttribute('data-perm-id'));
                const val = parseInt(this.value);
                if (val === -1) {
                    delete state.userOverrides[pId];
                } else {
                    state.userOverrides[pId] = val;
                }
            });
        });
    }

    document.getElementById('btn-save-user-overrides').addEventListener('click', function() {
        if (!state.selectedUserId) return;
        const btn = this;
        btn.disabled = true;

        fetch('/api/rbac/users/permissions', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                user_id: state.selectedUserId,
                overrides: state.userOverrides
            })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            if (data.success) {
                alert(data.message);
                loadAuditLogs();
            }
        });
    });

    // Scan Automatique
    document.getElementById('btn-scan-app').addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Analyse en cours...';

        fetch('/api/rbac/scan', { method: 'POST' })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-cpu"></i> <span>Détecter les permissions</span>';
                if (data.success) {
                    alert(`Scan réussi ! ${data.report.created_count} nouvelle(s) permission(s) créée(s) sur ${data.report.total_scanned} scannées.`);
                    loadPermissionsCatalog();
                    loadAuditLogs();
                }
            });
    });

    // Sauvegardes & Restauration
    document.getElementById('btn-create-backup').addEventListener('click', function() {
        const name = prompt('Nom de la sauvegarde :', 'Sauvegarde_' + new Date().toISOString().slice(0,10));
        if (!name) return;

        fetch('/api/rbac/backups/create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: name, description: 'Sauvegarde manuelle RBAC' })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                loadBackupsList();
                loadAuditLogs();
            }
        });
    });

    function loadBackupsList() {
        fetch('/api/rbac/backups')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const list = document.getElementById('backups-list');
                    if (data.data.length === 0) {
                        list.innerHTML = '<div class="text-center py-4 text-muted small">Aucune sauvegarde disponible.</div>';
                        return;
                    }
                    let html = '';
                    data.data.forEach(b => {
                        html += `
                            <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                                <div>
                                    <div class="fw-bold small text-primary">${escapeHtml(b.backup_name)}</div>
                                    <div class="text-muted" style="font-size:0.75rem;">Créée par ${escapeHtml(b.created_by_name || 'Système')} le ${b.created_at}</div>
                                </div>
                                <button class="btn btn-sm btn-outline-success btn-restore-backup" data-id="${b.id}">Restaurer</button>
                            </div>
                        `;
                    });
                    list.innerHTML = html;

                    document.querySelectorAll('.btn-restore-backup').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const bId = parseInt(this.getAttribute('data-id'));
                            if (confirm('Voulez-vous restaurer le système RBAC à partir de cette sauvegarde ?')) {
                                fetch('/api/rbac/backups/restore', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ backup_id: bId })
                                })
                                .then(res => res.json())
                                .then(resData => {
                                    if (resData.success) {
                                        alert(resData.message);
                                        loadPermissionsCatalog();
                                        loadRolesList();
                                        loadAuditLogs();
                                    }
                                });
                            }
                        });
                    });
                }
            });
    }

    function loadAuditLogs() {
        fetch('/api/rbac/audit')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const tbody = document.getElementById('audit-log-rows');
                    if (data.data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Aucun événement d\'audit.</td></tr>';
                        return;
                    }
                    let html = '';
                    data.data.forEach(log => {
                        html += `
                            <tr>
                                <td class="text-nowrap">${log.created_at}</td>
                                <td><span class="fw-semibold">${escapeHtml(log.user_name || 'Système')}</span></td>
                                <td><span class="badge bg-secondary-subtle text-body">${escapeHtml(log.action_type)}</span></td>
                                <td>${escapeHtml(log.details || '')}</td>
                            </tr>
                        `;
                    });
                    tbody.innerHTML = html;
                }
            });
    }

    document.getElementById('btn-refresh-audit').addEventListener('click', loadAuditLogs);
    document.getElementById('btn-refresh-backups').addEventListener('click', loadBackupsList);

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
