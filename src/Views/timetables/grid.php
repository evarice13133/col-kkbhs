<?php
$title = h($timetable['titre']) . " - " . __('timetables_grid_header_title');
ob_start();
$isSuperAdmin = \App\Core\Session::get('user_role') === 'superadmin';
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">
    <!-- Header Canva-Style (Style payments/index.php) -->
    <div class="modern-card border-0 shadow-sm p-4 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 rounded-pill fw-medium" style="font-size: 0.75rem;">
                        <i class="bi bi-door-open-fill me-1"></i><?= h($timetable['class_name']) ?>
                    </span>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2.5 py-1 rounded-pill fw-medium" style="font-size: 0.75rem;">
                        <i class="bi bi-calendar-event me-1"></i><?= h($timetable['week_libelle']) ?>
                    </span>
                    <?php if ($isLocked): ?>
                        <span class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25 px-2.5 py-1 rounded-pill fw-medium" style="font-size: 0.75rem;">
                            <i class="bi bi-lock-fill me-1"></i><?= __('timetables_status_locked') ?> (168h)
                        </span>
                    <?php else: ?>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill fw-medium" style="font-size: 0.75rem;">
                            <i class="bi bi-unlock-fill me-1"></i><?= __('timetables_lock_editable') ?>
                        </span>
                    <?php endif; ?>
                </div>
                <h2 class="fw-black text-main-theme mb-0 fs-3"><?= h($timetable['titre']) ?></h2>
                <p class="text-muted small mb-0">
                    <?= __('timetables_period_from') ?> <?= date('d/m/Y', strtotime($timetable['week_start'])) ?> au <?= date('d/m/Y', strtotime($timetable['week_end'])) ?> 
                    | <?= __('timetables_author') ?> <?= h($timetable['author_name'] ?? 'Admin') ?>
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="/timetables" class="btn btn-sm btn-light-theme rounded-pill px-3 py-2 fw-semibold border-theme-light shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i><?= __('cancel') ?? 'Retour' ?>
                </a>
                
                <?php if ($isLocked && $isSuperAdmin): ?>
                    <button type="button" class="btn btn-sm btn-warning rounded-pill px-3 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#unlockModal">
                        <i class="bi bi-key-fill me-1"></i><?= __('timetables_unlock_superadmin') ?>
                    </button>
                <?php endif; ?>

                <a href="/timetables/pdf?id=<?= $timetable['id'] ?>&mode=print" target="_blank" class="btn btn-sm btn-action-modern text-primary border px-3" title="<?= __('print') ?? 'Imprimer' ?>">
                    <i class="bi bi-printer-fill me-1"></i><?= __('print') ?? 'Imprimer' ?>
                </a>
                <a href="/timetables/pdf?id=<?= $timetable['id'] ?>&mode=download" class="btn btn-sm btn-danger rounded-pill px-3 py-2 fw-bold shadow-sm">
                    <i class="bi bi-file-earmark-pdf-fill me-1"></i>PDF
                </a>
                <button class="btn btn-sm btn-light-theme border rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#auditModal">
                    <i class="bi bi-shield-check me-1"></i><?= __('timetables_audit_log') ?>
                </button>
            </div>
        </div>
    </div>

    <?php if ($isLocked): ?>
        <div class="alert alert-dark border-0 rounded-4 shadow-sm p-3 mb-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="bi bi-lock-fill fs-3 text-warning me-3"></i>
                <div>
                    <h6 class="fw-bold mb-0"><?= __('timetables_locked_notice_title') ?></h6>
                    <p class="small mb-0 opacity-75"><?= __('timetables_locked_notice_desc') ?></p>
                </div>
            </div>
            <?php if ($isSuperAdmin): ?>
                <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill">Superadmin</span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Notification Conflit -->
    <div id="conflictAlertContainer" class="d-none mb-4">
        <div class="alert alert-danger rounded-4 shadow-sm border-2 border-danger d-flex align-items-center">
            <i class="bi bi-exclamation-octagon-fill fs-2 me-3 text-danger"></i>
            <div>
                <h6 class="fw-bold mb-1"><?= __('timetables_conflict_detected') ?></h6>
                <div id="conflictAlertMessage" class="small"></div>
            </div>
        </div>
    </div>

    <!-- Grille Emploi du Temps Style Moderne (Plein espace & Design optimisé) -->
    <div class="modern-card border-0 shadow-sm overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table-modern table-bordered align-middle mb-0 grid-table">
                <thead>
                    <tr>
                        <th style="width: 140px;" class="ps-4 text-center py-3"><?= __('timetables_time_day') ?></th>
                        <?php foreach ($gridData['days'] as $day): ?>
                            <th class="py-3 px-2 text-center text-uppercase" style="min-width: 170px;"><?= $day ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gridData['slots'] as $slot): ?>
                        <?php $isPause = ($slot['type_creneau'] === 'pause'); ?>
                        <tr class="<?= $isPause ? 'bg-pause-row' : '' ?>">
                            <!-- Colonne Heure -->
                            <td class="text-center fw-bold bg-light-theme py-3 slot-time-cell">
                                <div class="fs-6 text-main-theme"><?= substr($slot['heure_debut'], 0, 5) ?> - <?= substr($slot['heure_fin'], 0, 5) ?></div>
                                <?php if ($isPause): ?>
                                    <span class="badge bg-warning text-dark rounded-pill extra-small fw-bold mt-1">
                                        <i class="bi bi-cup-hot-fill me-1"></i><?= __('timetables_type_pause') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted extra-small d-block mt-1"><i class="bi bi-clock me-1 opacity-50"></i><?= (int)$slot['duree_minutes'] ?> min</span>
                                <?php endif; ?>
                            </td>

                            <!-- Colonnes par Jour -->
                            <?php foreach ($gridData['days'] as $day): ?>
                                <?php 
                                $entry = $matrix[$slot['id']][$day] ?? null;
                                ?>
                                <td class="text-center p-1.5 grid-cell <?= $isPause ? 'cell-disabled' : '' ?>" 
                                    data-slot-id="<?= $slot['id'] ?>" 
                                    data-day="<?= $day ?>"
                                    <?php if (!$isPause && $canEdit): ?>
                                        onclick="openAssignModal(<?= $slot['id'] ?>, '<?= $day ?>', <?= json_encode($entry) ?>)"
                                    <?php endif; ?>>
                                    
                                    <?php if ($isPause): ?>
                                        <!-- Pause Cell (Plein Espace) -->
                                        <div class="pause-box rounded-3 p-3 text-center bg-warning bg-opacity-10 border border-warning border-opacity-20 text-warning shadow-xs h-100 w-100 d-flex flex-column align-items-center justify-content-center">
                                            <i class="bi bi-cup-hot-fill fs-4 mb-1"></i>
                                            <span class="fw-bold extra-small text-uppercase tracking-wider"><?= __('timetables_type_pause') ?></span>
                                        </div>
                                    <?php elseif ($entry): ?>
                                        <!-- Carte de Cours (Occupation Optimale de l'EspaceDisponible) -->
                                        <div class="course-card p-3 rounded-3 text-start transition-all shadow-sm border border-primary border-opacity-15 h-100 w-100 d-flex flex-column justify-content-between" 
                                             style="background: rgba(var(--primary-rgb), 0.04);">
                                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                                <div class="d-flex align-items-center gap-2 overflow-hidden flex-grow-1">
                                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" 
                                                         style="width: 32px; height: 32px; font-size: 0.9rem;">
                                                        <i class="bi bi-book-half"></i>
                                                    </div>
                                                    <div class="fw-bold text-main-theme lh-sm" style="font-size: 0.88rem; word-break: break-word;">
                                                        <?= h($entry['subject_name']) ?>
                                                    </div>
                                                </div>
                                                <?php if ($canEdit): ?>
                                                    <button class="btn btn-link text-danger p-0 ms-1 opacity-75 opacity-100-hover flex-shrink-0" onclick="event.stopPropagation(); deleteEntry(<?= $slot['id'] ?>, '<?= $day ?>')" title="<?= __('delete') ?>">
                                                        <i class="bi bi-x-circle-fill fs-6"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>

                                            <div class="d-flex flex-column gap-1.5 pt-2 border-top border-primary border-opacity-10">
                                                <div class="d-flex align-items-center gap-1.5 text-muted opacity-90" style="font-size: 0.78rem;">
                                                    <i class="bi bi-person-badge text-primary fs-6"></i>
                                                    <span class="fw-medium text-truncate"><?= h($entry['teacher_name']) ?></span>
                                                </div>
                                                <div class="d-flex align-items-center gap-1.5 text-muted opacity-90" style="font-size: 0.76rem;">
                                                    <i class="bi bi-geo-alt-fill text-danger fs-6"></i>
                                                    <span class="fw-semibold text-truncate"><?= h($entry['room_name']) ?></span>
                                                </div>
                                            </div>
                                        </div>

                                    <?php else: ?>
                                        <!-- Slot Vide (Plein Espace avec Icône Catalyseur) -->
                                        <?php if ($canEdit): ?>
                                            <div class="empty-slot-placeholder p-3 text-center rounded-3 transition-all border border-dashed border-2 border-secondary border-opacity-20 h-100 w-100 d-flex flex-column align-items-center justify-content-center">
                                                <i class="bi bi-plus-circle text-primary opacity-50 fs-4 mb-1"></i>
                                                <span class="extra-small text-muted fw-semibold"><?= __('add') ?? 'Affecter' ?></span>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-muted extra-small py-3 h-100 d-flex align-items-center justify-content-center">- Libre -</div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Affectation Cours -->
<?php if ($canEdit): ?>
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: var(--bg-card);">
            <div class="modal-header bg-primary text-white p-4">
                <h5 class="modal-title fw-black" id="assignModalTitle">
                    <i class="bi bi-calendar-plus me-2"></i><?= __('timetables_assign_course') ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="assignForm">
                    <input type="hidden" id="assign_slot_id">
                    <input type="hidden" id="assign_day">

                    <div class="mb-3">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_subject_label') ?></label>
                        <select id="assign_subject_id" class="form-select rounded-3" required onchange="checkRealtimeConflict()">
                            <option value="">-- Choisir une matière --</option>
                            <?php foreach ($gridData['subjects'] as $s): ?>
                                <option value="<?= $s['id'] ?>" data-color="<?= $s['couleur_hex'] ?? '#3b82f6' ?>">
                                    <?= h($s['nom']) ?> (<?= h($s['code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_teacher_label') ?></label>
                        <select id="assign_teacher_id" class="form-select rounded-3" required onchange="checkRealtimeConflict()">
                            <option value="">-- Choisir un enseignant --</option>
                            <?php foreach ($gridData['teachers'] as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= h($t['nom_complet']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_room_label') ?></label>
                        <select id="assign_room_id" class="form-select rounded-3" required onchange="checkRealtimeConflict()">
                            <option value="">-- Choisir une salle --</option>
                            <?php foreach ($gridData['rooms'] as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= h($r['nom']) ?> (<?= h($r['code']) ?> - <?= $r['capacite'] ?> places)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3 d-none">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_card_color') ?></label>
                        <input type="color" id="assign_color" class="form-control form-control-color w-100 rounded-3" value="#3b82f6">
                    </div>

                    <div id="modalConflictFeedback" class="alert alert-danger d-none rounded-3 small mb-0"></div>
                </form>
            </div>
            <div class="modal-footer border-top p-3" style="background: var(--bg-card);">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="saveAssignment()"><?= __('save') ?></button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Audit Log -->
<div class="modal fade" id="auditModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: var(--bg-card);">
            <div class="modal-header bg-dark text-white p-4">
                <h5 class="modal-title fw-black"><i class="bi bi-shield-check me-2"></i><?= __('timetables_audit_title') ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th class="ps-4"><?= __('timetables_timestamp') ?></th>
                                <th><?= __('timetables_user') ?></th>
                                <th><?= __('action') ?? 'Action' ?></th>
                                <th><?= __('timetables_details') ?></th>
                                <th class="pe-4">IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($auditLogs)): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">Aucun événement enregistré.</td></tr>
                            <?php else: ?>
                                <?php foreach ($auditLogs as $log): ?>
                                    <tr>
                                        <td class="ps-4 text-muted small"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                                        <td class="fw-bold text-main-theme"><?= h($log['user_name']) ?> <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-0.5 rounded-pill"><?= h($log['user_role']) ?></span></td>
                                        <td>
                                            <span class="badge bg-primary text-white fw-bold"><?= h($log['action_type']) ?></span>
                                        </td>
                                        <td class="small text-muted"><?= h($log['details']) ?></td>
                                        <td class="pe-4 text-muted extra-small"><code><?= h($log['ip_address']) ?></code></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top p-3" style="background: var(--bg-card);">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
const TIMETABLE_ID = <?= (int)$timetable['id'] ?>;

function openAssignModal(slotId, day, entry) {
    document.getElementById('assign_slot_id').value = slotId;
    document.getElementById('assign_day').value = day;
    document.getElementById('modalConflictFeedback').classList.add('d-none');

    if (entry) {
        document.getElementById('assignModalTitle').innerHTML = '<i class="bi bi-pencil-fill me-2"></i>Modifier l\'affectation (' + day + ')';
        document.getElementById('assign_subject_id').value = entry.subject_id;
        document.getElementById('assign_teacher_id').value = entry.teacher_id;
        document.getElementById('assign_room_id').value = entry.room_id;
        document.getElementById('assign_color').value = entry.couleur_hex || '#3b82f6';
    } else {
        document.getElementById('assignModalTitle').innerHTML = '<i class="bi bi-calendar-plus me-2"></i>Affecter un cours (' + day + ')';
        document.getElementById('assign_subject_id').value = '';
        document.getElementById('assign_teacher_id').value = '';
        document.getElementById('assign_room_id').value = '';
        document.getElementById('assign_color').value = '#3b82f6';
    }

    var modal = new bootstrap.Modal(document.getElementById('assignModal'));
    modal.show();
}

function checkRealtimeConflict() {
    const slotId = document.getElementById('assign_slot_id').value;
    const day = document.getElementById('assign_day').value;
    const teacherId = document.getElementById('assign_teacher_id').value;
    const roomId = document.getElementById('assign_room_id').value;

    if (!teacherId || !roomId) return;

    fetch(`/timetables/api/validate-conflict?timetable_id=${TIMETABLE_ID}&slot_id=${slotId}&day_of_week=${day}&teacher_id=${teacherId}&room_id=${roomId}`)
        .then(r => r.json())
        .then(res => {
            const feedback = document.getElementById('modalConflictFeedback');
            if (res.has_conflict) {
                feedback.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i>' + res.messages.join('<br>');
                feedback.classList.remove('d-none');
            } else {
                feedback.classList.add('d-none');
            }
        });
}

function saveAssignment() {
    const slotId = document.getElementById('assign_slot_id').value;
    const day = document.getElementById('assign_day').value;
    const subjectId = document.getElementById('assign_subject_id').value;
    const teacherId = document.getElementById('assign_teacher_id').value;
    const roomId = document.getElementById('assign_room_id').value;
    const color = document.getElementById('assign_color').value;

    if (!subjectId || !teacherId || !roomId) {
        alert('Veuillez sélectionner la matière, l\'enseignant et la salle.');
        return;
    }

    fetch('/timetables/api/save-entry', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            timetable_id: TIMETABLE_ID,
            slot_id: slotId,
            day_of_week: day,
            subject_id: subjectId,
            teacher_id: teacherId,
            room_id: roomId,
            couleur_hex: color
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            window.location.reload();
        } else {
            const feedback = document.getElementById('modalConflictFeedback');
            feedback.innerHTML = '<i class="bi bi-exclamation-octagon-fill me-1"></i>' + res.message;
            feedback.classList.remove('d-none');

            document.getElementById('conflictAlertMessage').innerText = res.message;
            document.getElementById('conflictAlertContainer').classList.remove('d-none');
        }
    });
}

function deleteEntry(slotId, day) {
    if (!confirm('Voulez-vous libérer ce créneau ?')) return;

    fetch('/timetables/api/delete-entry', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            timetable_id: TIMETABLE_ID,
            slot_id: slotId,
            day_of_week: day
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            window.location.reload();
        } else {
            alert(res.message);
        }
    });
}
</script>

<style>
    .grid-table {
        border-collapse: collapse;
        width: 100%;
    }
    .grid-cell {
        min-width: 170px;
        height: 110px;
        vertical-align: stretch;
        transition: background 0.2s;
    }
    .grid-cell:not(.cell-disabled):hover {
        background-color: rgba(var(--primary-rgb), 0.05);
        cursor: pointer;
    }
    .cell-disabled {
        background-color: rgba(248, 250, 252, 0.5) !important;
    }
    .bg-pause-row {
        background-color: rgba(254, 240, 138, 0.12) !important;
    }
    .course-card {
        cursor: pointer;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .course-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(var(--primary-rgb), 0.15) !important;
    }
    .empty-slot-placeholder {
        background-color: rgba(var(--primary-rgb), 0.02);
        transition: all 0.2s ease;
    }
    .grid-cell:hover .empty-slot-placeholder {
        border-color: var(--primary-color) !important;
        background-color: rgba(var(--primary-rgb), 0.08);
    }
    .grid-cell:hover .empty-slot-placeholder i {
        opacity: 1 !important;
        transform: scale(1.1);
    }
    .extra-small { font-size: 0.75rem; }

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

    [data-theme="dark"] .course-card {
        background: rgba(30, 30, 45, 0.9) !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
    }
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../templates/layout.php';
?>
