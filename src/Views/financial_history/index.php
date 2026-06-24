<?php
$title = "Historique Financier";

// Helpers pour formater l'historique d'audit
if (!function_exists('getFriendlyFieldName')) {
    function getFriendlyFieldName($key) {
        $map = [
            'amount' => 'Montant',
            'amount_type' => 'Type',
            'motive' => 'Motif',
            'status' => 'Statut',
            'date_effet' => 'Date effet',
            'commentaire' => 'Note',
            'reference' => 'Référence',
            'payment_method' => 'Méthode',
            'payment_date' => 'Date paiement',
            'student_id' => 'ID Élève',
            'class_id' => 'ID Classe',
            'type' => 'Type de frais'
        ];
        return $map[$key] ?? ucfirst($key);
    }
}

if (!function_exists('formatHistoryValue')) {
    function formatHistoryValue($key, $val) {
        if ($key === 'amount_type') {
            return $val === 'percentage' ? 'Pourcentage (%)' : 'Montant fixe';
        }
        if ($key === 'amount' && is_numeric($val)) {
            return number_format((float)$val, 0, '.', ' ') . ' FCFA';
        }
        if ($key === 'status') {
            return $val === 'active' ? 'Actif' : 'Inactif';
        }
        if (is_array($val)) {
            return json_encode($val, JSON_UNESCAPED_UNICODE);
        }
        return h($val);
    }
}

ob_start();
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-black text-main-theme mb-0 fs-4">Journal d'Audit Financier</h2>
            <p class="text-muted-theme small mb-0">Historique complet des créations, modifications et suppressions d'opérations financières</p>
        </div>
    </div>

    <!-- History Table Card -->
    <div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in">
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 170px;">Date & Heure</th>
                        <th>Opérateur</th>
                        <th>Entité</th>
                        <th>ID Entité</th>
                        <th>Action</th>
                        <th>Anciennes Valeurs</th>
                        <th class="pe-4">Nouvelles Valeurs</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-info-circle fs-4 d-block mb-2 text-secondary"></i>
                                Aucune action enregistrée dans le journal d'audit.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($history as $h): ?>
                            <?php 
                            // Formatage de l'action pour les badges
                            $action = strtolower($h['action']);
                            if ($action === 'create') {
                                $badgeClass = 'bg-success bg-opacity-10 text-success border border-success border-opacity-25';
                                $actionTxt = 'Création';
                            } elseif ($action === 'delete') {
                                $badgeClass = 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25';
                                $actionTxt = 'Suppression';
                            } elseif ($action === 'update_status') {
                                $badgeClass = 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25';
                                $actionTxt = 'Statut';
                            } else {
                                $badgeClass = 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25';
                                $actionTxt = 'Mise à jour';
                            }

                            // Formatage du type d'entité
                            $entityType = strtolower($h['entity_type']);
                            if ($entityType === 'payment') {
                                $entityTxt = 'Paiement';
                            } elseif ($entityType === 'student_discount') {
                                $entityTxt = 'Réduction Élève';
                            } elseif ($entityType === 'class_discount') {
                                $entityTxt = 'Réduction Classe';
                            } elseif ($entityType === 'student_scholarship') {
                                $entityTxt = 'Bourse Élève';
                            } elseif ($entityType === 'class_scholarship') {
                                $entityTxt = 'Bourse Classe';
                            } elseif ($entityType === 'class_finance') {
                                $entityTxt = 'Frais Classe';
                            } else {
                                $entityTxt = h($h['entity_type']);
                            }

                            // Décoder les valeurs JSON
                            $oldValDecoded = json_decode($h['old_value'] ?? '', true);
                            $newValDecoded = json_decode($h['new_value'] ?? '', true);
                            ?>
                            <tr class="student-row">
                                <td class="ps-4">
                                    <div class="fw-bold text-main-theme"><?= date('d/m/Y H:i:s', strtotime($h['event_date'])) ?></div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-init bg-secondary bg-opacity-10 text-secondary fw-bold rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                            style="width: 32px; height: 32px; font-size: 0.85rem; border: 1px solid rgba(100, 116, 139, 0.2);">
                                            <?= strtoupper(substr((string) ($h['user_nom'] ?: 'S'), 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-main-theme" style="font-size: 0.85rem;"><?= h($h['user_nom'] ?: 'Système') ?> <?= h($h['user_prenom'] ?? '') ?></div>
                                            <div class="extra-small text-muted" style="text-transform: uppercase;"><?= h($h['user_role'] ? __($h['user_role']) : 'automatique') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1 rounded-pill"><?= $entityTxt ?></span>
                                </td>
                                <td><code class="small text-secondary">#<?= $h['entity_id'] ?></code></td>
                                <td>
                                    <span class="badge rounded-pill px-2.5 py-1 fw-bold fs-8 <?= $badgeClass ?>"><?= $actionTxt ?></span>
                                </td>
                                <td style="font-size: 0.8rem; vertical-align: top;">
                                    <?php if ($oldValDecoded && is_array($oldValDecoded)): ?>
                                        <div class="d-flex flex-column gap-1">
                                            <?php foreach ($oldValDecoded as $k => $v): ?>
                                                <?php if ($k !== 'tranches'): ?>
                                                    <div>
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-0.5 rounded" style="font-size: 0.7rem; font-weight: 600;">
                                                            <?= getFriendlyFieldName($k) ?>
                                                        </span>
                                                        <span class="text-main-theme ms-1 font-monospace" style="font-size: 0.75rem;">
                                                            <?= formatHistoryValue($k, $v) ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php elseif ($h['old_value']): ?>
                                        <div class="text-muted text-truncate" style="max-width: 200px;" title="<?= h($h['old_value']) ?>"><?= h($h['old_value']) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted opacity-50">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4" style="font-size: 0.8rem; vertical-align: top;">
                                    <?php if ($newValDecoded && is_array($newValDecoded)): ?>
                                        <div class="d-flex flex-column gap-1">
                                            <?php foreach ($newValDecoded as $k => $v): ?>
                                                <?php if ($k !== 'tranches'): ?>
                                                    <div>
                                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-0.5 rounded" style="font-size: 0.7rem; font-weight: 600;">
                                                            <?= getFriendlyFieldName($k) ?>
                                                        </span>
                                                        <?php 
                                                        // Highlight if changed
                                                        $changed = isset($oldValDecoded[$k]) && $oldValDecoded[$k] !== $v;
                                                        $style = $changed ? 'color: var(--warning-color); font-weight: bold;' : '';
                                                        ?>
                                                        <span class="text-main-theme ms-1 font-monospace" style="font-size: 0.75rem; <?= $style ?>">
                                                            <?= formatHistoryValue($k, $v) ?>
                                                            <?php if ($changed): ?>
                                                                <i class="bi bi-arrow-left-short text-warning" title="Modifié"></i>
                                                            <?php endif; ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php elseif ($h['new_value']): ?>
                                        <div class="text-muted text-truncate" style="max-width: 200px;" title="<?= h($h['new_value']) ?>"><?= h($h['new_value']) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted opacity-50">-</span>
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
