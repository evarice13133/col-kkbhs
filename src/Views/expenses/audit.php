<?php
$title = __('audit_logs');
ob_start();
?>

<style>
.log-card {
    transition: box-shadow 0.2s ease;
}
.log-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;
}
.value-box {
    background-color: var(--bg-body);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
    font-family: monospace;
    font-size: 0.8rem;
    white-space: pre-wrap;
    max-height: 150px;
    overflow-y: auto;
}
.badge-action-create { background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); }
.badge-action-update { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2); }
.badge-action-deactivate { background: rgba(100, 116, 139, 0.1); color: #64748b; border: 1px solid rgba(100, 116, 139, 0.2); }
.badge-action-reactivate { background: rgba(6, 182, 212, 0.1); color: #06b6d4; border: 1px solid rgba(6, 182, 212, 0.2); }
.badge-action-cancel { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
</style>

<div class="animate-fade-in container-fluid py-3 px-md-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-black text-main-theme mb-1 fs-4 d-flex align-items-center gap-2">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-warning bg-opacity-10 text-warning p-2" style="width:40px;height:40px;">
                    <i class="bi bi-shield-check fs-5"></i>
                </span>
                <?= __('expense_audit_title') ?>
            </h2>
            <p class="text-muted-theme small mb-0"><?= __('expense_audit_desc') ?></p>
        </div>
        <div>
            <a href="/expenses" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
                <i class="bi bi-arrow-left me-2"></i> <?= __('expense_cat_back') ?>
            </a>
        </div>
    </div>

    <!-- Audit Logs List -->
    <div class="modern-card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 15%;"><?= __('expense_audit_th_datetime') ?></th>
                        <th style="width: 15%;"><?= __('expense_audit_th_user') ?></th>
                        <th style="width: 10%;" class="text-center"><?= __('expense_audit_th_action') ?></th>
                        <th style="width: 15%;"><?= __('expense_audit_th_target') ?></th>
                        <th style="width: 20%;"><?= __('expense_audit_th_old_values') ?></th>
                        <th style="width: 25%;" class="pe-4"><?= __('expense_audit_th_new_values') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-info-circle fs-3 d-block mb-2 text-secondary"></i>
                                <?= __('expense_audit_no_data') ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): 
                            $badgeClass = 'badge-action-' . $log['action'];
                            
                            $targetName = '-';
                            if ($log['expense_ref']) {
                                $targetName = __('expenses') . " : <strong>" . h($log['expense_ref']) . "</strong>";
                            } elseif ($log['category_name']) {
                                $targetName = __('category') . " : <strong>" . h($log['category_name']) . "</strong>";
                            } elseif ($log['category_id']) {
                                $targetName = __('category') . " #" . $log['category_id'];
                            } elseif ($log['expense_id']) {
                                $targetName = __('expenses') . " #" . $log['expense_id'];
                            }

                            // Format old/new values
                            $oldValFmt = '';
                            if ($log['old_values']) {
                                $arr = json_decode($log['old_values'], true);
                                if ($arr) {
                                    foreach ($arr as $k => $v) {
                                        $oldValFmt .= h($k) . " : " . h(is_array($v) ? json_encode($v) : $v) . "\n";
                                    }
                                } else {
                                    $oldValFmt = h($log['old_values']);
                                }
                            }

                            $newValFmt = '';
                            if ($log['new_values']) {
                                $arr = json_decode($log['new_values'], true);
                                if ($arr) {
                                    foreach ($arr as $k => $v) {
                                        $newValFmt .= h($k) . " : " . h(is_array($v) ? json_encode($v) : $v) . "\n";
                                    }
                                } else {
                                    $newValFmt = h($log['new_values']);
                                }
                            }
                        ?>
                            <tr>
                                <td class="ps-4 text-muted small"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                                <td>
                                    <div class="fw-bold text-main-theme small"><?= h($log['user_name']) ?></div>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary extra-small px-2 py-0.5 rounded-pill text-capitalize"><?= h($log['user_role']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge-premium <?= $badgeClass ?> text-uppercase fw-bold text-nowrap" style="font-size: 0.65rem;">
                                        <?= h($log['action']) ?>
                                    </span>
                                </td>
                                <td><span class="small text-main-theme"><?= $targetName ?></span></td>
                                <td>
                                    <?php if ($oldValFmt): ?>
                                        <div class="value-box"><?= trim($oldValFmt) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4">
                                    <?php if ($newValFmt): ?>
                                        <div class="value-box"><?= trim($newValFmt) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                    <?php if ($log['reason']): ?>
                                        <div class="small text-danger mt-1"><strong><?= __('expense_cancel_reason_title') ?> :</strong> <?= h($log['reason']) ?></div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
