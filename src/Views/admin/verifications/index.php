<?php
// Vue d'administration des vérifications publiques
$title = "Historique des Vérifications de Reçus";
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 text-gray-800"><i class="bi bi-shield-check me-2"></i><?= $title ?></h2>
</div>

<!-- Statistiques Rapides -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Scans</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['total']) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-qr-code-scan fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Authentiques</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['successful']) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-patch-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Échecs/Invalides</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['failed']) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-shield-x fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Taux de Succès</div>
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                    <?= $stats['total'] > 0 ? round(($stats['successful'] / $stats['total']) * 100, 1) : 0 ?>%
                                </div>
                            </div>
                            <div class="col">
                                <div class="progress progress-sm mr-2">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: <?= $stats['total'] > 0 ? ($stats['successful'] / $stats['total']) * 100 : 0 ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-percent fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form method="GET" action="/admin/verifications" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Année Scolaire</label>
                <select name="academic_year_id" class="form-select">
                    <option value="">Toutes les années</option>
                    <?php foreach ($academicYears as $year): ?>
                        <option value="<?= $year['id'] ?>" <?= ($filters['academic_year_id'] ?? '') == $year['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($year['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Statut</label>
                <select name="status" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="valid" <?= ($filters['status'] ?? '') === 'valid' ? 'selected' : '' ?>>Authentique</option>
                    <option value="invalid" <?= ($filters['status'] ?? '') === 'invalid' ? 'selected' : '' ?>>Invalide</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Recherche (Jeton, Nom Élève)</label>
                <input type="text" name="q" class="form-control" value="<?= htmlspecialchars($filters['q'] ?? '') ?>" placeholder="Rechercher...">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Filtrer</button>
            </div>
        </form>
    </div>
</div>

<!-- Historique Complet -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Historique des 500 dernières vérifications</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Date & Heure</th>
                        <th>Jeton (Code)</th>
                        <th>Statut</th>
                        <th>Erreur</th>
                        <th>Élève Concerné</th>
                        <th>Année Scolaire</th>
                        <th>Adresse IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Aucune vérification enregistrée.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($history as $log): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i:s', strtotime($log['verified_at'])) ?></td>
                                <td class="font-monospace text-primary" style="font-size: 0.85rem;"><?= htmlspecialchars($log['verification_code']) ?></td>
                                <td>
                                    <?php if ($log['is_valid']): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Valide</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Invalide</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $log['error_case'] ? htmlspecialchars($log['error_case']) : '-' ?></td>
                                <td>
                                    <?php if ($log['student_nom']): ?>
                                        <?= htmlspecialchars($log['student_nom']) ?> <?= htmlspecialchars($log['student_prenom']) ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($log['matricule']) ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($log['annee_scolaire'] ?? '-') ?></td>
                                <td style="font-size: 0.85rem;"><?= htmlspecialchars($log['ip_address']) ?></td>
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
require __DIR__ . '/../../layouts/admin.php';
?>
