<?php
$title = __('timetables_weeks_title') . " - " . __('app_name');
ob_start();
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h2 class="fw-black text-main-theme mb-0 fs-4">
                <i class="bi bi-calendar-range text-primary me-2"></i><?= __('timetables_weeks_title') ?>
            </h2>
            <p class="text-muted-theme small mb-0"><?= __('timetables_weeks_subtitle') ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="/timetables" class="btn btn-sm btn-light-theme rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-1 border-theme-light shadow-sm">
                <i class="bi bi-arrow-left"></i> <?= __('cancel') ?? 'Retour' ?>
            </a>
            <button class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-bold shadow-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addWeekModal">
                <i class="bi bi-plus-circle-fill"></i> <?= __('timetables_new_week') ?>
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

    <?php if ($suggestion): ?>
        <div class="modern-card border-primary p-3 mb-4 shadow-sm">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 rounded-pill fw-bold mb-1"><i class="bi bi-magic me-1"></i><?= __('timetables_smart_suggestion') ?></span>
                    <div class="fw-bold text-main-theme"><?= __('timetables_suggested_next') ?></div>
                    <div class="text-muted small">
                        <strong><?= h($suggestion['suggested_libelle']) ?></strong> du 
                        <code><?= date('d/m/Y', strtotime($suggestion['suggested_start'])) ?></code> au 
                        <code><?= date('d/m/Y', strtotime($suggestion['suggested_end'])) ?></code>
                    </div>
                </div>
                <button class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-bold shadow-sm" onclick="applySuggestion('<?= h($suggestion['suggested_libelle']) ?>', '<?= $suggestion['suggested_start'] ?>', '<?= $suggestion['suggested_end'] ?>')">
                    <i class="bi bi-arrow-right-circle me-1"></i><?= __('timetables_use_suggestion') ?>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Table Card -->
    <div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in">
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th class="ps-4"><?= __('timetables_col_week_label') ?></th>
                        <th><?= __('timetables_col_academic_year') ?></th>
                        <th><?= __('timetables_col_start_date') ?></th>
                        <th><?= __('timetables_col_end_date') ?></th>
                        <th class="text-center"><?= __('timetables_col_days_count') ?></th>
                        <th class="pe-4 text-end"><?= __('timetables_col_actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($weeks as $week): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-main-theme fs-6">
                                <?= h($week['libelle']) ?>
                            </td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2.5 py-1 rounded-pill fw-medium" style="font-size: 0.75rem;">
                                    <?= h($week['academic_year_libelle'] ?? 'N/A') ?>
                                </span>
                            </td>
                            <td class="fw-bold text-muted" style="font-size: 0.88rem;">
                                <i class="bi bi-calendar-event me-1 text-primary"></i><?= date('d/m/Y', strtotime($week['date_debut'])) ?>
                            </td>
                            <td class="fw-bold text-muted" style="font-size: 0.88rem;">
                                <i class="bi bi-calendar-check me-1 text-success"></i><?= date('d/m/Y', strtotime($week['date_fin'])) ?>
                            </td>
                            <td class="text-center text-muted fw-semibold">
                                <?php 
                                $d1 = new DateTime($week['date_debut']);
                                $d2 = new DateTime($week['date_fin']);
                                $diff = $d1->diff($d2)->days + 1;
                                echo $diff . " " . (__('days') ?? 'jours');
                                ?>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <button class="btn btn-sm btn-action-modern text-primary" onclick='editWeek(<?= json_encode($week) ?>)' title="<?= __('edit') ?>">
                                        <i class="bi bi-pencil-fill fs-5"></i>
                                    </button>
                                    <form method="POST" action="/timetables/weeks/delete" class="d-inline" onsubmit="return confirm('<?= __('confirm') ?> ?');">
                                        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                                        <input type="hidden" name="id" value="<?= $week['id'] ?>">
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

<!-- Modal Ajout Semaine -->
<div class="modal fade" id="addWeekModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="/timetables/weeks/store" class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: var(--bg-card);">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
            <div class="modal-header bg-primary text-white p-4">
                <h5 class="modal-title fw-black"><i class="bi bi-calendar-plus me-2"></i><?= __('timetables_modal_add_week') ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_col_academic_year') ?> *</label>
                        <select name="academic_year_id" id="add_academic_year_id" class="form-select rounded-3" required>
                            <?php foreach ($years as $y): ?>
                                <option value="<?= $y['id'] ?>"><?= h($y['libelle']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_col_week_label') ?> *</label>
                        <input type="text" name="libelle" id="add_libelle" class="form-control rounded-3" placeholder="EX: Semaine 12" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_col_start_date') ?> *</label>
                        <input type="date" name="date_debut" id="add_date_debut" class="form-control rounded-3" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_col_end_date') ?> *</label>
                        <input type="date" name="date_fin" id="add_date_fin" class="form-control rounded-3" required>
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

<!-- Modal Édition Semaine -->
<div class="modal fade" id="editWeekModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="/timetables/weeks/update" class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: var(--bg-card);">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
            <input type="hidden" name="id" id="edit_week_id">
            <div class="modal-header bg-dark text-white p-4">
                <h5 class="modal-title fw-black"><i class="bi bi-pencil me-2"></i><?= __('timetables_modal_edit_week') ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_col_academic_year') ?> *</label>
                        <select name="academic_year_id" id="edit_academic_year_id" class="form-select rounded-3" required>
                            <?php foreach ($years as $y): ?>
                                <option value="<?= $y['id'] ?>"><?= h($y['libelle']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_col_week_label') ?> *</label>
                        <input type="text" name="libelle" id="edit_libelle" class="form-control rounded-3" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_col_start_date') ?> *</label>
                        <input type="date" name="date_debut" id="edit_date_debut" class="form-control rounded-3" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_col_end_date') ?> *</label>
                        <input type="date" name="date_fin" id="edit_date_fin" class="form-control rounded-3" required>
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
function applySuggestion(libelle, start, end) {
    document.getElementById('add_libelle').value = libelle;
    document.getElementById('add_date_debut').value = start;
    document.getElementById('add_date_fin').value = end;

    var addModal = new bootstrap.Modal(document.getElementById('addWeekModal'));
    addModal.show();
}

function editWeek(week) {
    document.getElementById('edit_week_id').value = week.id;
    document.getElementById('edit_academic_year_id').value = week.academic_year_id;
    document.getElementById('edit_libelle').value = week.libelle;
    document.getElementById('edit_date_debut').value = week.date_debut;
    document.getElementById('edit_date_fin').value = week.date_fin;

    var editModal = new bootstrap.Modal(document.getElementById('editWeekModal'));
    editModal.show();
}
</script>

<style>
    /* Canva & MS 365 Inspired Weeks Table & Dark Mode */
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
