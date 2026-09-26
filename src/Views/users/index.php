<?php $title = __('user_management'); ob_start(); ?>

<?php
$userRoleOptions = [
    'superadmin' => __('role_superadmin'),
    'admin' => __('role_admin'),
    'direction_academique' => __('role_direction_academique'),
    'enseignant' => __('role_enseignant'),
    'caissier' => __('role_caissier'),
    'comptable' => __('role_comptable'),
    'it_manager' => __('role_it_manager'),
];
if (\App\Core\Session::get('user_role') === 'admin') {
    unset($userRoleOptions['superadmin'], $userRoleOptions['admin']);
}
?>

<div class="animate-fade-in container-fluid py-4">


    <!-- BARRE D'ACTIONS COMPLÈTE : Style Floating Island -->
    <div class="d-flex justify-content-center mb-5">
        <div class="filter-island px-3 py-2 shadow-lg animate-slide-down" style="min-width: 80%;">
            <form method="GET" class="d-flex align-items-center gap-2 flex-wrap flex-md-nowrap filter-form users-filter-form w-100">
                
                <!-- Boutons d'Action Principaux -->
                <div class="d-flex gap-2 pe-3 border-end border-opacity-10 border-secondary me-2 user-create-action">
                    <a href="/users/create" class="btn btn-primary rounded-pill px-3 fw-bold shadow-sm text-nowrap user-create-button">
                        <i class="bi bi-person-plus-fill me-1"></i> <?= __('add_user') ?>
                    </a>
                </div>

                <!-- Barre de Recherche et Filtre Rôle -->
                <div class="flex-grow-1 d-flex gap-2 user-search-role-group">
                    <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2 user-search-pill">
                        <span class="input-group-text border-0 bg-transparent text-primary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-0 bg-transparent shadow-none py-2 text-main user-search-input"
                            value="<?= htmlspecialchars((string) $filters['q']) ?>"
                            placeholder="<?= __('search_placeholder') ?>..." aria-label="<?= htmlspecialchars((string) __('search_placeholder'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    
                    <select name="role" class="form-select border-0 bg-white bg-opacity-10 shadow-none py-2 text-main rounded-pill px-3 user-role-select" aria-label="<?= htmlspecialchars((string) __('all_roles'), ENT_QUOTES, 'UTF-8') ?>">
                        <option value=""><?= __('all_roles') ?></option>
                        <?php foreach ($userRoleOptions as $roleValue => $roleLabel): ?>
                            <option value="<?= $roleValue ?>" <?= $filters['role'] === $roleValue ? 'selected' : '' ?>><?= $roleLabel ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtres et Utilitaires -->
                <div class="d-flex gap-2 align-items-center ps-2 user-filter-actions">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"><?= __('filter') ?></button>
                    <a href="/users" class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center reset-btn" style="width: 40px; height: 40px;" title="<?= __('reset') ?>">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                    <div class="ms-2">
                        <a href="/users/export?<?= http_build_query($filters) ?>"
                            class="btn-export-minimal shadow-sm" title="<?= __('export_list') ?>">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Grille des utilisateurs -->
    <div class="row g-3 g-md-4">
        <?php foreach ($users as $user): ?>
            <div class="col-6 col-sm-6 col-xl-3 animate-fade-up">
                <div class="subject-card-compact border-theme-dynamic h-100 position-relative">
                    <div class="subject-card-glow"></div>
                    <div class="card-body p-2 h-100 position-relative" style="z-index: 1;">
                        <div class="d-flex flex-column h-100 justify-content-between gap-1">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-black rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                                style="width: 30px; height: 30px; font-size: 0.9rem;">
                                                <?= strtoupper(substr((string) ($user['nom'] ?: $user['username']), 0, 1)) ?>
                                            </div>
                                        </div>
                                        <div class="overflow-hidden">
                                            <h6 class="fw-bold lh-1 text-main-theme m-0 text-truncate"
                                                style="font-size: 0.85rem;"
                                                title="<?= htmlspecialchars((string) $user['nom'] . ' ' . $user['prenom']) ?>">
                                                <?= htmlspecialchars((string) $user['nom'] ?: $user['username']) ?>
                                            </h6>
                                            <div class="extra-small text-muted-theme opacity-75 text-truncate"
                                                style="font-size: 0.7rem;"><?= htmlspecialchars((string) $user['username']) ?></div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-1 align-items-center">
                                        <a href="/users/edit?id=<?= $user['id'] ?>"
                                            class="btn-icon-action text-primary position-relative stretched-link"
                                            style="z-index: 10; width: 28px; height: 28px; font-size: 0.8rem;"
                                            title="<?= __('edit') ?>">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <?php if (App\Core\Session::get('user_id') != $user['id']): ?>
                                            <button type="button"
                                                class="btn-icon-action text-danger position-relative"
                                                style="z-index: 10; width: 28px; height: 28px; font-size: 0.8rem;"
                                                data-impact-delete="user"
                                                data-id="<?= $user['id'] ?>"
                                                title="<?= __('delete') ?>">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Info Badge Row -->
                                <div class="mt-1 d-flex flex-wrap gap-1 align-items-center">
                                    <div class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 extra-small px-2 py-1 rounded-pill fw-bold">
                                        <i class="bi bi-shield-lock-fill me-1"></i><?= htmlspecialchars(ucfirst((string) $user['role'])) ?>
                                    </div>
                                    <div class="extra-small text-muted-theme opacity-50 d-none d-md-block text-truncate" style="font-size: 0.6rem; max-width: 100px;">
                                        <?= htmlspecialchars((string) ($user['email'] ?: '')) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto d-flex justify-content-end align-items-center position-relative" style="z-index: 1;">
                                <div class="card-arrow-container">
                                    <i class="bi bi-arrow-right-short text-primary opacity-50 fs-5"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($users)): ?>
            <div class="col-12">
                <div class="subject-card-compact p-5 text-center border-dashed">
                    <i class="bi bi-people fs-1 opacity-25 mb-3 d-block"></i>
                    <h5 class="text-muted-theme"><?= __('no_data') ?></h5>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Floating Island Filters */
    .filter-island {
        background: rgba(var(--bg-card-rgb), 0.7);
        backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(var(--primary-rgb), 0.15);
        border-radius: 100px;
        min-width: 80%;
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .filter-island {
        background: rgba(30, 30, 45, 0.6);
        border-color: rgba(255, 255, 255, 0.08);
    }

    .filter-island:focus-within {
        border-color: var(--primary-color);
        box-shadow: 0 15px 35px -10px rgba(var(--primary-rgb), 0.25);
        transform: translateY(-2px);
    }

    .user-search-role-group,
    .user-search-pill,
    .user-search-input {
        min-width: 0;
    }

    .user-search-pill {
        flex: 1 1 auto;
    }

    .user-role-select {
        flex: 0 0 180px;
        width: 180px;
        max-width: 180px;
    }

    .btn-export-minimal {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: var(--bg-card);
        color: #f1c40f;
        border: 1px solid rgba(241, 196, 15, 0.2);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        text-decoration: none !important;
    }

    .btn-export-minimal:hover {
        background: #f1c40f;
        color: white !important;
        transform: scale(1.1) rotate(8deg);
        box-shadow: 0 8px 20px rgba(241, 196, 15, 0.3);
    }

    /* Animations */
    .animate-slide-down {
        animation: slideDown 0.6s cubic-bezier(0.23, 1, 0.32, 1);
    }

    @keyframes slideDown {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .subject-card-compact {
        background: var(--bg-card);
        border-radius: 14px !important;
        border: 1px solid var(--border-color, rgba(226, 232, 240, 0.8)) !important;
        display: block;
        text-decoration: none !important;
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1),
                    box-shadow 0.3s cubic-bezier(0.16, 1, 0.3, 1),
                    border-color 0.3s ease,
                    border-style 0.3s ease;
        box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.04);
        position: relative;
        overflow: hidden;
    }

    [data-theme="dark"] .subject-card-compact {
        background: var(--bg-card, #0f172a);
        backdrop-filter: blur(15px);
        border-color: rgba(255, 255, 255, 0.08) !important;
    }

    .subject-card-glow {
        display: none !important;
    }

    .subject-card-compact:hover {
        transform: translateY(-3px) !important;
        border-style: dashed !important;
        border-width: 1.5px !important;
        border-color: var(--primary-color, #7c3aed) !important;
        box-shadow: 0 14px 28px -6px rgba(124, 58, 237, 0.15) !important;
    }

    .btn-icon-action {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: rgba(var(--primary-rgb), 0.05);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        text-decoration: none !important;
    }

    .btn-icon-action:hover {
        transform: translateY(-3px) rotate(8deg);
        background: var(--primary-color);
        color: white !important;
        box-shadow: 0 5px 15px rgba(var(--primary-rgb), 0.3);
    }

    .btn-icon-action.text-danger:hover {
        background: #dc3545;
        color: white !important;
        box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
    }

    @media (max-width: 767.98px) {
        .filter-island {
            border-radius: 24px;
            min-width: 0 !important;
            padding: 1rem !important;
            width: 100%;
        }

        .users-filter-form {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr);
            gap: 0.65rem !important;
            width: 100%;
        }

        .users-filter-form > * {
            min-width: 0;
            width: 100%;
        }

        .user-create-action {
            padding: 0 !important;
            margin: 0 !important;
            border: 0 !important;
        }

        .user-create-button {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            width: 100%;
        }

        .user-search-role-group {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr);
            gap: 0.6rem !important;
            width: 100%;
        }

        .user-search-pill,
        .user-role-select {
            width: 100% !important;
            max-width: none !important;
            min-height: 44px;
        }

        .user-search-input {
            width: 100%;
        }

        .user-filter-actions {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) 44px 44px;
            gap: 0.5rem !important;
            padding: 0 !important;
            width: 100%;
        }

        .user-filter-actions > button {
            min-height: 44px;
        }

        .user-filter-actions .reset-btn,
        .user-filter-actions .btn-export-minimal {
            width: 44px !important;
            height: 44px !important;
            padding: 0 !important;
            border-radius: 12px !important;
        }

        .user-filter-actions > div {
            margin: 0 !important;
        }
    }
</style>

<?php $content = ob_get_clean(); include __DIR__ . '/../templates/layout.php'; ?>