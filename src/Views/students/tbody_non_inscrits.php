<?php if (empty($students)): ?>
    <tr>
        <td colspan="10" class="text-center py-5 text-muted">
            <i class="bi bi-info-circle fs-4 d-block mb-2 text-secondary"></i>
            <?= __('no_students_found') ?: 'Aucun élève trouvé' ?>
        </td>
    </tr>
<?php else: ?>
    <?php foreach ($students as $s): ?>
        <tr class="student-row">
            <!-- Matricule -->
            <td>
                <span class="fw-bold text-main-theme small"><?= htmlspecialchars((string) ($s['email'] ?: '-')) ?></span>
            </td>
            <!-- Nom et prénom -->
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
            <!-- Sexe -->
            <td>
                <span class="small fw-semibold"><?= htmlspecialchars((string) $s['sexe']) ?></span>
            </td>
            <!-- Classe -->
            <td>
                <span class="badge bg-primary text-white px-2 py-1 rounded-pill fw-bold shadow-sm" style="font-size: 0.7rem;">
                    <i class="bi bi-door-open-fill me-1"></i><?= htmlspecialchars((string) ($s['classe_nom'] ?: __('no_class'))) ?>
                </span>
            </td>
            <!-- Section -->
            <td>
                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1 rounded-pill fw-medium" style="font-size: 0.7rem;">
                    <i class="bi bi-layers-half me-1"></i><?= htmlspecialchars((string) ($s['section_nom'] ?: '-')) ?>
                </span>
            </td>
            <!-- Cycle -->
            <td>
                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1 rounded-pill fw-medium" style="font-size: 0.7rem;">
                    <i class="bi bi-layers me-1"></i><?= htmlspecialchars((string) ($s['cycle_nom'] ?: '-')) ?>
                </span>
            </td>
            <!-- Type d'enseignement -->
            <td>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill fw-medium" style="font-size: 0.7rem;">
                    <i class="bi bi-diagram-3 me-1"></i><?= htmlspecialchars((string) ($s['teaching_type_nom'] ?: '-')) ?>
                </span>
            </td>
            <!-- Date d'importation -->
            <td>
                <span class="small text-muted-theme"><?= $s['created_at'] ? date('d/m/Y H:i', strtotime($s['created_at'])) : '-' ?></span>
            </td>
            <!-- Statut -->
            <td>
                <?php if ($s['status'] === 'Non inscrit'): ?>
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2.5 py-1 small fw-bold">
                        Non inscrit
                    </span>
                <?php elseif ($s['status'] === 'Démissionnaire'): ?>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1 small fw-bold">
                        Démissionnaire
                    </span>
                <?php else: ?>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 small fw-bold">
                        <?= htmlspecialchars($s['status']) ?>
                    </span>
                <?php endif; ?>
            </td>
            <!-- Actions -->
            <?php if (\App\Core\PermissionManager::hasPermission('manage_students')): ?>
            <td class="text-end pe-4">
                <div class="d-flex justify-content-end align-items-center gap-2">
                    <?php if ($s['status'] === 'Non inscrit'): ?>
                        <!-- Inscrire -->
                        <a href="/students/create?student_id=<?= $s['id'] ?>" class="btn btn-xs btn-outline-success rounded-pill px-3 py-1 fw-bold d-inline-flex align-items-center gap-1 shadow-sm transition-base hover-lift" style="font-size: 0.7rem;">
                            <i class="bi bi-person-plus-fill fs-6"></i>
                            <span>Inscrire</span>
                        </a>
                        <!-- Démissionner -->
                        <button onclick="confirmWithdraw('/students/withdraw?id=<?= $s['id'] ?>&csrf_token=<?= \App\Core\Session::generateCsrfToken() ?>')" class="btn btn-sm btn-action-modern text-danger" title="Marquer démissionnaire">
                            <i class="bi bi-person-x fs-5"></i>
                        </button>
                    <?php elseif ($s['status'] === 'Démissionnaire'): ?>
                        <!-- Restaurer -->
                        <button onclick="confirmRestore('/students/restore?id=<?= $s['id'] ?>&csrf_token=<?= \App\Core\Session::generateCsrfToken() ?>')" class="btn btn-sm btn-action-modern text-success" title="Restaurer l'élève">
                            <i class="bi bi-arrow-counterclockwise fs-5"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
