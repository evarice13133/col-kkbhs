<?php
$title = __('timetables_slots_title') . " - " . __('app_name');
ob_start();
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h2 class="fw-black text-main-theme mb-0 fs-4">
                <i class="bi bi-clock-history text-primary me-2"></i><?= __('timetables_slots_title') ?>
            </h2>
            <p class="text-muted-theme small mb-0"><?= __('timetables_slots_subtitle') ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="/timetables" class="btn btn-sm btn-light-theme rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-1 border-theme-light shadow-sm">
                <i class="bi bi-arrow-left"></i> <?= __('cancel') ?? 'Retour' ?>
            </a>
            <button class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-bold shadow-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addSlotModal">
                <i class="bi bi-plus-circle-fill"></i> <?= __('timetables_new_slot') ?>
            </button>
        </div>
    </div>

    <?php if ($flashError = \App\Core\Session::getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= h($flashError) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($flashSuccess = \App\Core\Session::getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= h($flashSuccess) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Table Card -->
    <div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in">
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th class="ps-4"><?= __('timetables_col_order') ?></th>
                        <th><?= __('timetables_col_start_time') ?></th>
                        <th><?= __('timetables_col_end_time') ?></th>
                        <th class="text-center"><?= __('timetables_col_type') ?></th>
                        <th class="text-center"><?= __('timetables_col_duration') ?></th>
                        <th class="pe-4 text-end"><?= __('timetables_col_actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($slots as $slot): ?>
                        <tr class="slot-row">
                            <td class="ps-4">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2.5 py-1 rounded-pill fw-bold" style="font-size: 0.75rem;">
                                    #<?= (int)$slot['ordre_affichage'] ?>
                                </span>
                            </td>
                            <td class="fw-bold text-main-theme fs-6">
                                <?= substr($slot['heure_debut'], 0, 5) ?>
                            </td>
                            <td class="fw-bold text-main-theme fs-6">
                                <?= substr($slot['heure_fin'], 0, 5) ?>
                            </td>
                            <td class="text-center">
                                <?php if ($slot['type_creneau'] === 'pause'): ?>
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-1 rounded-pill fw-bold" style="font-size: 0.75rem;">
                                        <i class="bi bi-cup-hot-fill me-1"></i><?= __('timetables_type_pause') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1 rounded-pill fw-bold" style="font-size: 0.75rem;">
                                        <i class="bi bi-book-fill me-1"></i><?= __('timetables_type_course') ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center text-muted fw-semibold">
                                <i class="bi bi-stopwatch me-1 text-primary"></i><?= (int)$slot['duree_minutes'] ?> min
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <button class="btn btn-sm btn-action-modern text-primary" onclick='editSlot(<?= json_encode($slot) ?>)' title="<?= __('edit') ?>">
                                        <i class="bi bi-pencil-fill fs-5"></i>
                                    </button>
                                    <form method="POST" action="/timetables/slots/delete" class="d-inline" onsubmit="return confirm('<?= __('confirm') ?> ?');">
                                        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                                        <input type="hidden" name="id" value="<?= $slot['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-action-modern text-danger" title="<?= __('delete') ?>">
                                            <i class="bi bi-trash-fill fs-5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ajout Créneau -->
<div class="modal fade" id="addSlotModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="/timetables/slots/store" class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: var(--bg-card);">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
            <div class="modal-header bg-primary text-white p-4">
                <h5 class="modal-title fw-black"><i class="bi bi-clock me-2"></i><?= __('timetables_modal_add_slot') ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_col_start_time') ?> *</label>
                        <input type="time" name="heure_debut" class="form-control rounded-3" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_col_end_time') ?> *</label>
                        <input type="time" name="heure_fin" class="form-control rounded-3" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_col_type') ?> *</label>
                        <select name="type_creneau" class="form-select rounded-3">
                            <option value="cours"><?= __('timetables_type_course') ?></option>
                            <option value="pause"><?= __('timetables_type_pause') ?></option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_display_order') ?></label>
                        <input type="number" name="ordre_affichage" class="form-control rounded-3" value="<?= count($slots) + 1 ?>">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top p-3" style="background: var(--bg-card);">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"><?= __('save') ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Édition Créneau -->
<div class="modal fade" id="editSlotModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="/timetables/slots/update" class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: var(--bg-card);">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
            <input type="hidden" name="id" id="edit_slot_id">
            <div class="modal-header bg-dark text-white p-4">
                <h5 class="modal-title fw-black"><i class="bi bi-pencil me-2"></i><?= __('timetables_modal_edit_slot') ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_col_start_time') ?> *</label>
                        <input type="time" name="heure_debut" id="edit_heure_debut" class="form-control rounded-3" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_col_end_time') ?> *</label>
                        <input type="time" name="heure_fin" id="edit_heure_fin" class="form-control rounded-3" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_col_type') ?> *</label>
                        <select name="type_creneau" id="edit_type_creneau" class="form-select rounded-3">
                            <option value="cours"><?= __('timetables_type_course') ?></option>
                            <option value="pause"><?= __('timetables_type_pause') ?></option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_display_order') ?></label>
                        <input type="number" name="ordre_affichage" id="edit_ordre_affichage" class="form-control rounded-3">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top p-3" style="background: var(--bg-card);">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold shadow-sm"><?= __('save') ?></button>
            </div>
        </form>
    </div>
</div>

<script>
function editSlot(slot) {
    document.getElementById('edit_slot_id').value = slot.id;
    document.getElementById('edit_heure_debut').value = slot.heure_debut;
    document.getElementById('edit_heure_fin').value = slot.heure_fin;
    document.getElementById('edit_type_creneau').value = slot.type_creneau;
    document.getElementById('edit_ordre_affichage').value = slot.ordre_affichage;

    var editModal = new bootstrap.Modal(document.getElementById('editSlotModal'));
    editModal.show();
}
</script>

<style>
    /* Thème sombre pour le tableau */
    [data-theme="dark"] .modern-card {
        background: rgba(30, 30, 45, 0.6);
        border-color: rgba(255, 255, 255, 0.08);
    }

    [data-theme="dark"] .table-modern thead th {
        background: rgba(255, 255, 255, 0.05);
        color: #ffffff;
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }

    [data-theme="dark"] .table-modern tbody tr {
        border-bottom-color: rgba(255, 255, 255, 0.05);
    }

    [data-theme="dark"] .table-modern tbody td {
        color: #e0e0e0;
    }
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../templates/layout.php';
?>
