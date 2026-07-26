<?php $title = __('teaching_types') ?? 'Types d\'Enseignement'; ob_start(); ?>

<div class="animate-fade-in container-fluid py-4">
    
    <!-- BARRE D'ACTIONS COMPLÈTE : Style Floating Island -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-column flex-md-row gap-3">
        <div>
            <h1 class="fw-black fs-4 text-main-theme mb-1 lh-1">
                <?= __('teaching_types') ?? 'Types d\'Enseignement' ?>
            </h1>
            <p class="text-muted-theme mb-0 small opacity-75">
                Configurez et organisez les grands types d'enseignement de votre établissement
            </p>
        </div>
        
        <?php if (\App\Core\PermissionManager::hasRole(['superadmin', 'admin'])): ?>
            <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm scale-on-hover text-nowrap" onclick="openCreateTeachingTypeModal()">
                <i class="bi bi-plus-lg me-2"></i><?= __('add_teaching_type') ?? 'Ajouter un type' ?>
            </button>
        <?php endif; ?>
    </div>

    <!-- LISTE DES TYPES D'ENSEIGNEMENT (Grille harmonisée sur cycles/index.php) -->
    <div class="row g-2 g-md-4">
        <?php foreach ($teachingTypes as $num => $type): ?>
            <div class="col-6 col-sm-6 col-xl-3">
                <div class="subject-card-compact border-theme-dynamic h-100 position-relative <?= $type['actif'] ? '' : 'opacity-75' ?>">
                    <div class="subject-card-glow"></div>
                    <div class="card-body p-3 h-100 position-relative" style="z-index: 1;">
                        <div class="d-flex flex-column h-100 justify-content-between gap-2">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-black rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                                style="width: 34px; height: 34px; font-size: 0.9rem;">
                                                <i class="bi bi-diagram-3"></i>
                                            </div>
                                        </div>
                                        <div class="overflow-hidden">
                                            <h6 class="fw-bold lh-1 text-main-theme m-0 text-truncate"
                                                style="font-size: 0.9rem;"
                                                title="<?= htmlspecialchars((string) $type['nom']) ?>">
                                                <?= htmlspecialchars((string) $type['nom']) ?>
                                            </h6>
                                            <div class="extra-small text-muted-theme opacity-75 text-truncate"
                                                style="font-size: 0.72rem;">
                                                CODE : <?= htmlspecialchars((string) $type['code']) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-1 align-items-center" style="z-index: 10;">
                                        <?php if (\App\Core\PermissionManager::hasRole(['superadmin', 'admin'])): ?>
                                            <button type="button" class="btn-icon-action text-primary position-relative border-0 bg-transparent"
                                                style="z-index: 10; width: 30px; height: 30px; font-size: 0.85rem;"
                                                onclick="openEditTeachingTypeModal(<?= htmlspecialchars(json_encode([
                                                    'id' => (int)$type['id'],
                                                    'nom' => $type['nom'],
                                                    'code' => $type['code'],
                                                    'position' => (int)($type['position'] ?? 0),
                                                    'actif' => (int)($type['actif'] ?? 1)
                                                ]), ENT_QUOTES, 'UTF-8') ?>)" title="<?= __('edit') ?>">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <a href="/teaching_types/delete?id=<?= $type['id'] ?>&csrf_token=<?= \App\Core\Session::generateCsrfToken() ?>"
                                                class="btn-icon-action text-danger position-relative btn-confirm-delete"
                                                style="z-index: 10; width: 30px; height: 30px; font-size: 0.85rem;"
                                                data-confirm="<?= __('confirm_delete_text') ?? 'Voulez-vous supprimer ?' ?>" title="<?= __('delete') ?>">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Info Badge Row -->
                                <div class="mt-2 d-flex flex-wrap gap-1 align-items-center">
                                    <?php if ($type['actif']): ?>
                                        <div class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 extra-small px-2 py-1 rounded-pill fw-bold">
                                            <i class="bi bi-check-circle-fill me-1"></i><?= __('active') ?? 'Actif' ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10 extra-small px-2 py-1 rounded-pill fw-bold">
                                            <i class="bi bi-x-circle-fill me-1"></i>Inactif
                                        </div>
                                    <?php endif; ?>
                                    <div class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-10 extra-small px-2 py-1 rounded-pill fw-bold">
                                        Pos: <?= (int)($type['position'] ?? 0) ?>
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

        <?php if (empty($teachingTypes)): ?>
            <div class="col-12">
                <div class="subject-card-compact p-5 text-center border-dashed">
                    <i class="bi bi-diagram-3 fs-1 opacity-25 mb-3 d-block"></i>
                    <h5 class="text-muted"><?= __('no_data') ?></h5>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL: Type d'enseignement (Création / Modification) -->
<?php if (\App\Core\PermissionManager::hasRole(['superadmin', 'admin'])): ?>
<div class="modal fade" id="teachingTypeModal" tabindex="-1" aria-labelledby="teachingTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: var(--bg-card);">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-init bg-primary text-white rounded-4 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px;" id="ttModalIcon">
                        <i class="bi bi-diagram-3 fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-black text-main-theme" id="teachingTypeModalLabel"><?= __('add_teaching_type') ?? 'Ajouter un Type' ?></h5>
                        <p class="text-muted-theme small mb-0 opacity-75" id="ttModalSubtext">Détails du type d'enseignement</p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="teachingTypeForm" action="/teaching_types/store" method="POST">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">
                            Intitulé <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="bi bi-fonts"></i></span>
                            <input type="text" name="nom" id="tt_nom" class="form-control premium-input" 
                                   placeholder="ex: Enseignement Secondaire Général" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">
                            Code <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="bi bi-hash"></i></span>
                            <input type="text" name="code" id="tt_code" class="form-control premium-input" 
                                   placeholder="ex: ESG" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">
                            Position d'affichage
                        </label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="bi bi-sort-numeric-down"></i></span>
                            <input type="number" name="position" id="tt_position" class="form-control premium-input" 
                                   placeholder="0" value="0">
                        </div>
                    </div>

                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="actif" id="tt_actif" value="1" checked>
                        <label class="form-check-label fw-bold text-main-theme small" for="tt_actif">
                            Type d'enseignement actif
                        </label>
                    </div>
                </div>

                <div class="modal-footer border-top-0 pt-0 px-4 pb-4 gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold scale-on-hover" data-bs-dismiss="modal">
                        <?= __('cancel') ?>
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm scale-on-hover">
                        <i class="bi bi-check-circle-fill me-2"></i> <span id="ttSubmitBtnText"><?= __('save') ?></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
    .scale-on-hover:hover { transform: scale(1.05); }

    .subject-card-compact {
        background: var(--bg-card);
        border-radius: 24px;
        border: 1px solid var(--border-theme) !important;
        display: block;
        text-decoration: none !important;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        position: relative;
        overflow: hidden;
    }

    [data-theme="dark"] .subject-card-compact {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(10px);
        border-color: rgba(255, 255, 255, 0.08) !important;
    }

    .subject-card-glow {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at top right, rgba(var(--primary-rgb), 0.15), transparent 70%);
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    .subject-card-compact:hover {
        transform: translateY(-8px) scale(1.02);
        border-color: var(--primary-color) !important;
        box-shadow: 0 20px 40px rgba(var(--primary-rgb), 0.12);
    }

    .subject-card-compact:hover .subject-card-glow {
        opacity: 1;
    }

    .btn-icon-action {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: rgba(var(--primary-rgb), 0.05);
        transition: all 0.2s ease;
        text-decoration: none !important;
        font-size: 0.9rem;
    }

    .btn-icon-action.text-danger {
        background: rgba(220, 53, 69, 0.05);
    }

    .btn-icon-action:hover {
        transform: scale(1.1);
        background: var(--primary-color);
        color: white !important;
    }

    .avatar-init {
        font-family: 'Inter', sans-serif;
        letter-spacing: -1px;
    }

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

    .border-dashed { border-style: dashed !important; border-width: 2px !important; }
</style>

<script>
function openCreateTeachingTypeModal() {
    const form = document.getElementById('teachingTypeForm');
    if (!form) return;
    form.action = '/teaching_types/store';
    document.getElementById('teachingTypeModalLabel').textContent = "<?= addslashes(__('add_teaching_type') ?? 'Ajouter un Type') ?>";
    document.getElementById('ttModalSubtext').textContent = "Détails du type d'enseignement";
    document.getElementById('ttSubmitBtnText').textContent = "<?= addslashes(__('save')) ?>";
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
    document.getElementById('teachingTypeModalLabel').textContent = "Modifier le Type";
    document.getElementById('ttModalSubtext').textContent = tt.nom || '';
    document.getElementById('ttSubmitBtnText').textContent = "<?= addslashes(__('save')) ?>";
    document.getElementById('ttModalIcon').innerHTML = '<i class="bi bi-pencil-square fs-4"></i>';
    document.getElementById('tt_nom').value = tt.nom || '';
    document.getElementById('tt_code').value = tt.code || '';
    document.getElementById('tt_position').value = tt.position || 0;
    document.getElementById('tt_actif').checked = tt.actif == 1;

    const modal = new bootstrap.Modal(document.getElementById('teachingTypeModal'));
    modal.show();
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
