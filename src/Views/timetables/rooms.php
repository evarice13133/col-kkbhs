<?php
$title = __('timetables_rooms_title') . " - " . __('app_name');
ob_start();
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h2 class="fw-black text-main-theme mb-0 fs-4">
                <i class="bi bi-building text-primary me-2"></i><?= __('timetables_rooms_title') ?>
            </h2>
            <p class="text-muted-theme small mb-0"><?= __('timetables_rooms_subtitle') ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="/timetables" class="btn btn-sm btn-light-theme rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-1 border-theme-light shadow-sm">
                <i class="bi bi-arrow-left"></i> <?= __('cancel') ?? 'Retour' ?>
            </a>
            <button class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-bold shadow-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                <i class="bi bi-plus-circle-fill"></i> <?= __('timetables_new_room') ?>
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
                        <th class="ps-4"><?= __('timetables_col_room_code') ?></th>
                        <th><?= __('timetables_col_room_name') ?></th>
                        <th class="text-center"><?= __('timetables_col_capacity') ?></th>
                        <th><?= __('timetables_col_description') ?></th>
                        <th class="text-center"><?= __('timetables_col_dynamic_status') ?></th>
                        <th class="pe-4 text-end"><?= __('timetables_col_actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2.5 py-1 rounded-pill fw-bold" style="font-size: 0.75rem;">
                                    <?= h($room['code']) ?>
                                </span>
                            </td>
                            <td class="fw-bold text-main-theme fs-6">
                                <?= h($room['nom']) ?>
                            </td>
                            <td class="text-center fw-bold text-primary fs-6">
                                <i class="bi bi-people-fill me-1"></i><?= (int)$room['capacite'] ?>
                            </td>
                            <td class="text-muted small opacity-75">
                                <?= h($room['description'] ?? '-') ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-1 rounded-pill fw-bold" style="font-size: 0.75rem;">
                                    <i class="bi bi-check-circle-fill me-1"></i><?= __('timetables_status_available') ?>
                                </span>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex align-items-center justify-content-end gap-1 table-row-actions">
                                    <button class="btn btn-sm btn-action-modern text-primary" onclick='editRoom(<?= json_encode($room) ?>)' title="<?= __('edit') ?>">
                                        <i class="bi bi-pencil-fill fs-5"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-action-modern text-danger" data-impact-delete="room" data-id="<?= $room['id'] ?>" title="<?= __('delete') ?>">
                                        <i class="bi bi-trash-fill fs-5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ajout Salle -->
<div class="modal fade" id="addRoomModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="/timetables/rooms/store" class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: var(--bg-card);">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
            <div class="modal-header bg-primary text-white p-4">
                <h5 class="modal-title fw-black"><i class="bi bi-building me-2"></i><?= __('timetables_modal_add_room') ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_col_room_code') ?> *</label>
                        <input type="text" name="code" class="form-control rounded-3 text-uppercase" placeholder="EX: S-101" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_col_room_name') ?> *</label>
                        <input type="text" name="nom" class="form-control rounded-3" placeholder="EX: Amphi A" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_col_capacity') ?> *</label>
                        <input type="number" name="capacite" class="form-control rounded-3" value="40" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_col_description') ?></label>
                        <textarea name="description" class="form-control rounded-3" rows="3"></textarea>
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

<!-- Modal Édition Salle -->
<div class="modal fade" id="editRoomModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="/timetables/rooms/update" class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: var(--bg-card);">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
            <input type="hidden" name="id" id="edit_room_id">
            <div class="modal-header bg-dark text-white p-4">
                <h5 class="modal-title fw-black"><i class="bi bi-pencil me-2"></i><?= __('timetables_modal_edit_room') ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_col_room_code') ?> *</label>
                        <input type="text" name="code" id="edit_room_code" class="form-control rounded-3 text-uppercase" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_col_room_name') ?> *</label>
                        <input type="text" name="nom" id="edit_room_nom" class="form-control rounded-3" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_col_capacity') ?> *</label>
                        <input type="number" name="capacite" id="edit_room_capacite" class="form-control rounded-3" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_col_description') ?></label>
                        <textarea name="description" id="edit_room_desc" class="form-control rounded-3" rows="3"></textarea>
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
function editRoom(room) {
    document.getElementById('edit_room_id').value = room.id;
    document.getElementById('edit_room_code').value = room.code;
    document.getElementById('edit_room_nom').value = room.nom;
    document.getElementById('edit_room_capacite').value = room.capacite;
    document.getElementById('edit_room_desc').value = room.description || '';

    var editModal = new bootstrap.Modal(document.getElementById('editRoomModal'));
    editModal.show();
}
</script>

<style>
    /* Canva & MS 365 Inspired Rooms Table & Dark Mode */
    [data-theme="dark"] .modern-card {
        background: #1e293b !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
        color: #f8fafc !important;
    }

    [data-theme="dark"] .table-modern thead th {
        background: #0f172a !important;
        color: #f8fafc !important;
        border-bottom-color: rgba(255, 255, 255, 0.12) !important;
    }

    [data-theme="dark"] .table-modern tbody tr {
        border-bottom-color: rgba(255, 255, 255, 0.06) !important;
    }

    [data-theme="dark"] .table-modern tbody tr:hover {
        background: rgba(255, 255, 255, 0.04) !important;
    }

    [data-theme="dark"] .table-modern tbody td {
        color: #cbd5e1 !important;
    }
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../templates/layout.php';
?>
