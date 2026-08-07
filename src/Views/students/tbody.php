<?php if (empty($students)): ?>
    <tr>
        <td colspan="<?= \App\Core\PermissionManager::hasPermission('manage_students') ? 6 : 5 ?>"
            class="text-center py-5 text-muted">
            <i class="bi bi-info-circle fs-4 d-block mb-2 text-secondary"></i>
            <?= __('no_students_found') ?: 'Aucun élève trouvé' ?>
        </td>
    </tr>
<?php else: ?>
    <?php foreach ($students as $s): ?>
        <tr class="student-row">
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                        style="width: 36px; height: 36px; font-size: 1rem; border: 1px solid rgba(var(--primary-rgb), 0.2);">
                        <?= strtoupper(substr((string) $s['nom'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="fw-bold text-main-theme name-gradient" style="font-size: 0.9rem;">
                            <?= htmlspecialchars((string) $s['nom']) ?>
                        </div>
                        <div class="text-muted-theme opacity-75" style="font-size: 0.75rem;">
                            <?= htmlspecialchars((string) $s['prenom']) ?>
                        </div>
                    </div>
                </div>
            </td>
            <td>
                <span class="badge bg-primary text-white px-2 py-1 rounded-pill fw-bold shadow-sm" style="font-size: 0.7rem;">
                    <i
                        class="bi bi-door-open-fill me-1"></i><?= htmlspecialchars((string) ($s['classe_nom'] ?: __('no_class'))) ?>
                </span>
            </td>
            <td>
                <span
                    class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1 rounded-pill fw-medium"
                    style="font-size: 0.7rem;">
                    <i class="bi bi-layers-half me-1"></i><?= htmlspecialchars((string) ($s['section_nom'] ?: '-')) ?>
                </span>
            </td>
            <td>
                <span
                    class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill fw-medium"
                    style="font-size: 0.7rem;">
                    <i class="bi bi-diagram-3 me-1"></i><?= htmlspecialchars((string) ($s['teaching_type_nom'] ?: '-')) ?>
                </span>
            </td>
            <td>
                <span
                    class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1 rounded-pill fw-medium"
                    style="font-size: 0.7rem;">
                    <i class="bi bi-building me-1"></i><?= htmlspecialchars((string) ($s['department_nom'] ?: '-')) ?>
                </span>
            </td>
            <?php if (\App\Core\PermissionManager::hasPermission('manage_students')): ?>
                <td class="text-end pe-4">
                    <div class="d-flex justify-content-end gap-1 align-items-center table-row-actions">
                        <a href="/students/edit?id=<?= $s['id'] ?>" class="btn btn-sm btn-action-modern text-primary"
                            title="<?= __('edit') ?>">
                            <i class="bi bi-pencil-square fs-5"></i>
                        </a>
                        <?php if ($filters['withdrawn'] ?? 0): ?>
                            <a href="/students/restore?id=<?= $s['id'] ?>&csrf_token=<?= \App\Core\Session::generateCsrfToken() ?>"
                                class="btn btn-sm btn-action-modern text-success btn-confirm-restore"
                                data-confirm="<?= __('restore_student_confirm') ?>" title="<?= __('restore') ?>">
                                <i class="bi bi-arrow-counterclockwise fs-5"></i>
                            </a>
                        <?php else: ?>
                            <a href="/students/withdraw?id=<?= $s['id'] ?>&csrf_token=<?= \App\Core\Session::generateCsrfToken() ?>"
                                class="btn btn-sm btn-action-modern text-warning btn-confirm-withdraw"
                                data-confirm="<?= __('withdraw_student_confirm') ?>" title="<?= __('withdraw') ?>">
                                <i class="bi bi-person-x fs-5"></i>
                            </a>
                        <?php endif; ?>
                        <button type="button"
                            class="btn btn-sm btn-action-modern text-danger"
                            data-impact-delete="student"
                            data-id="<?= $s['id'] ?>"
                            title="<?= __('delete') ?>">
                            <i class="bi bi-trash fs-5"></i>
                        </button>
                    </div>
                </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>