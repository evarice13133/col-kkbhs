<?php $title = __('academic_cycles'); ob_start(); ?>

<div class="animate-fade-in container-fluid py-4">
    
    <!-- BARRE D'ACTIONS ET FILTRES : Style Floating Island -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-column flex-md-row gap-3">
        <form method="GET" action="/cycles" class="d-flex gap-2 align-items-center w-100 w-md-auto">
            <select name="teaching_type_id" class="form-select rounded-pill px-3 py-2 border-theme-light" onchange="this.form.submit()">
                <option value=""><?= __('all_teaching_types') ?? 'Tous les types d\'enseignement' ?></option>
                <?php foreach ($teachingTypes as $tt): ?>
                    <option value="<?= $tt['id'] ?>" <?= (int) ($filters['teaching_type_id'] ?? 0) === (int) $tt['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) $tt['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <?php if (in_array(App\Core\Session::get('user_role'), ['superadmin', 'admin'])): ?>
            <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm scale-on-hover text-nowrap" onclick="openCreateCycleModal()">
                <i class="bi bi-plus-lg me-2"></i> <?= __('add_cycle') ?>
            </button>
        <?php endif; ?>
    </div>

    <!-- LISTE DES CYCLES (Grille harmonisée) -->
    <div class="row g-2 g-md-4">
        <?php foreach ($cycles as $num => $cycle): ?>
            <div class="col-6 col-sm-6 col-xl-3">
                <div class="subject-card-compact border-theme-dynamic h-100 position-relative <?= ($cycle['status'] ?? 1) ? '' : 'opacity-75' ?>">
                    <div class="subject-card-glow"></div>
                    <div class="card-body p-3 h-100 position-relative" style="z-index: 1;">
                        <div class="d-flex flex-column h-100 justify-content-between gap-2">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-black rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                                style="width: 34px; height: 34px; font-size: 0.9rem;">
                                                <i class="bi bi-stack"></i>
                                            </div>
                                        </div>
                                        <div class="overflow-hidden">
                                            <h6 class="fw-bold lh-1 text-main-theme m-0 text-truncate"
                                                style="font-size: 0.9rem;"
                                                title="<?= htmlspecialchars((string) $cycle['nom']) ?>">
                                                <?= htmlspecialchars((string) $cycle['nom']) ?>
                                            </h6>
                                            <div class="extra-small text-muted-theme opacity-75 text-truncate"
                                                style="font-size: 0.72rem;">
                                                #<?= str_pad($num + 1, 2, '0', STR_PAD_LEFT) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-1 align-items-center" style="z-index: 10;">
                                        <?php if (in_array(App\Core\Session::get('user_role'), ['superadmin', 'admin'])): ?>
                                            <a href="/cycles/toggle?id=<?= $cycle['id'] ?>"
                                                class="btn-icon-action <?= ($cycle['status'] ?? 1) ? 'text-warning' : 'text-success' ?> position-relative"
                                                style="z-index: 10; width: 30px; height: 30px; font-size: 0.85rem;"
                                                title="<?= ($cycle['status'] ?? 1) ? 'Désactiver' : 'Activer' ?>">
                                                <i class="bi <?= ($cycle['status'] ?? 1) ? 'bi-eye-slash-fill' : 'bi-eye-fill' ?>"></i>
                                            </a>
                                            <button type="button" class="btn-icon-action text-primary position-relative border-0 bg-transparent"
                                                style="z-index: 10; width: 30px; height: 30px; font-size: 0.85rem;"
                                                onclick="openEditCycleModal(<?= htmlspecialchars(json_encode([
                                                    'id' => (int)$cycle['id'],
                                                    'nom' => $cycle['nom'],
                                                    'teaching_type_id' => (int)($cycle['teaching_type_id'] ?? 0)
                                                ]), ENT_QUOTES, 'UTF-8') ?>)" title="<?= __('edit') ?>">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <a href="/cycles/delete?id=<?= $cycle['id'] ?>&csrf_token=<?= \App\Core\Session::generateCsrfToken() ?>"
                                                class="btn-icon-action text-danger position-relative btn-confirm-delete"
                                                style="z-index: 10; width: 30px; height: 30px; font-size: 0.85rem;"
                                                data-confirm="<?= __('confirm_delete_text') ?>" title="<?= __('delete') ?>">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Info Badge Row -->
                                <div class="mt-2 d-flex flex-wrap gap-1 align-items-center">
                                    <?php if ($cycle['status'] ?? 1): ?>
                                        <div class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 extra-small px-2 py-1 rounded-pill fw-bold">
                                            <i class="bi bi-check-circle-fill me-1"></i><?= __('active') ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10 extra-small px-2 py-1 rounded-pill fw-bold">
                                            <i class="bi bi-x-circle-fill me-1"></i><?= __('inactive') ?? 'Inactif' ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($cycle['teaching_type_nom'])): ?>
                                        <div class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 extra-small px-2 py-1 rounded-pill fw-bold">
                                            <i class="bi bi-diagram-3 me-1"></i><?= htmlspecialchars((string) $cycle['teaching_type_nom']) ?>
                                        </div>
                                    <?php endif; ?>
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

        <?php if (empty($cycles)): ?>
            <div class="col-12">
                <div class="subject-card-compact p-5 text-center border-dashed">
                    <i class="bi bi-stack fs-1 opacity-25 mb-3 d-block"></i>
                    <h5 class="text-muted"><?= __('no_data') ?></h5>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL: Cycle (Création / Modification) -->
<?php if (in_array(App\Core\Session::get('user_role'), ['superadmin', 'admin'])): ?>
<div class="modal fade" id="cycleModal" tabindex="-1" aria-labelledby="cycleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: var(--bg-card);">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-init bg-primary text-white rounded-4 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px;" id="cycleModalIcon">
                        <i class="bi bi-stack fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-black text-main-theme" id="cycleModalLabel"><?= __('add_cycle') ?></h5>
                        <p class="text-muted-theme small mb-0 opacity-75" id="cycleModalSubtext">Détails du cycle académique</p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="cycleForm" action="/cycles/store" method="POST">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">
                            <?= __('cycle_name_label') ?> <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="bi bi-fonts"></i></span>
                            <input type="text" name="nom" id="cycle_nom" class="form-control premium-input" 
                                   placeholder="ex: 1er Cycle, 2nd Cycle" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">
                            Type Enseignement <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <span class="input-group-text-modern"><i class="bi bi-diagram-3"></i></span>
                            <select name="teaching_type_id" id="cycle_teaching_type_id" class="form-select premium-input" required>
                                <option value="" disabled selected>Sélectionner le type</option>
                                <?php foreach ($teachingTypes as $tt): ?>
                                    <option value="<?= $tt['id'] ?>">
                                        <?= htmlspecialchars((string) $tt['nom']) ?>
                                    </option>
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
                        <i class="bi bi-check-circle-fill me-2"></i> <span id="cycleSubmitBtnText"><?= __('save') ?></span>
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
function openCreateCycleModal() {
    const form = document.getElementById('cycleForm');
    if (!form) return;
    form.action = '/cycles/store';
    document.getElementById('cycleModalLabel').textContent = "<?= addslashes(__('add_cycle')) ?>";
    document.getElementById('cycleModalSubtext').textContent = "Détails du cycle académique";
    document.getElementById('cycleSubmitBtnText').textContent = "<?= addslashes(__('save')) ?>";
    document.getElementById('cycleModalIcon').innerHTML = '<i class="bi bi-stack fs-4"></i>';
    document.getElementById('cycle_nom').value = '';
    document.getElementById('cycle_teaching_type_id').value = '';

    const modal = new bootstrap.Modal(document.getElementById('cycleModal'));
    modal.show();
}

function openEditCycleModal(cy) {
    const form = document.getElementById('cycleForm');
    if (!form || !cy) return;
    form.action = '/cycles/update?id=' + cy.id;
    document.getElementById('cycleModalLabel').textContent = "<?= addslashes(__('edit_cycle_title')) ?>";
    document.getElementById('cycleModalSubtext').textContent = cy.nom || '';
    document.getElementById('cycleSubmitBtnText').textContent = "<?= addslashes(__('save')) ?>";
    document.getElementById('cycleModalIcon').innerHTML = '<i class="bi bi-pencil-square fs-4"></i>';
    document.getElementById('cycle_nom').value = cy.nom || '';
    document.getElementById('cycle_teaching_type_id').value = cy.teaching_type_id || '';

    const modal = new bootstrap.Modal(document.getElementById('cycleModal'));
    modal.show();
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>

