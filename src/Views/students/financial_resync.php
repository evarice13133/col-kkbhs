<?php
$title = 'Resynchronisation financière';
ob_start();
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-black text-main-theme mb-1">Resynchronisation des frais</h2>
            <p class="text-muted mb-0">
                <?= $requestedStudentId > 0
                    ? 'Vérifier et compléter la synchronisation financière de l’élève sélectionné.'
                    : 'Corriger les inscriptions importées dont les frais n’ont pas été synchronisés.' ?>
            </p>
        </div>
        <a href="/students" class="btn btn-light-theme rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Retour aux élèves
        </a>
    </div>

    <div class="alert alert-warning border-0 shadow-sm">
        <div class="fw-bold mb-1"><i class="bi bi-shield-check me-1"></i> Opération contrôlée</div>
        <div>
            Seuls les élèves inscrits de l'année active, avec une classe et un frais brut enregistré à zéro
            alors que le tarif applicable est positif, seront concernés. Une sauvegarde locale vérifiée
            sera créée avant le recalcul. Les paiements déjà enregistrés seront pris en compte.
        </div>
    </div>

    <?php if ($error !== null): ?>
        <div class="alert alert-danger shadow-sm" role="alert">
            <div class="fw-bold"><?= h($error) ?></div>
            <?php if ($backupFilename !== null && $backupFilename !== ''): ?>
                <div class="small mt-2">Archive créée avant l'opération : <code><?= h($backupFilename) ?></code></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($execution !== null): ?>
        <div class="alert alert-success shadow-sm" role="status">
            <div class="fw-bold">
                <?= $execution['count'] > 0
                    ? h((string) $execution['count']) . ' élève(s) resynchronisé(s) avec succès.'
                    : 'Aucun élève n’avait besoin de resynchronisation.' ?>
            </div>
            <?php if (!empty($execution['backup_filename'])): ?>
                <div class="small mt-1">Sauvegarde locale vérifiée : <code><?= h($execution['backup_filename']) ?></code></div>
            <?php endif; ?>
        </div>

        <?php if (!empty($execution['students'])): ?>
            <div class="modern-card border-0 shadow-sm overflow-hidden mb-4">
                <div class="p-3 border-bottom border-theme-light">
                    <h5 class="fw-bold mb-0">Vérification avant / après</h5>
                </div>
                <div class="table-responsive">
                    <table class="table-modern mb-0">
                        <thead>
                            <tr>
                                <th>Élève</th>
                                <th>Classe</th>
                                <th class="text-end">Frais brut<br>avant → après</th>
                                <th class="text-end">Réductions<br>avant → après</th>
                                <th class="text-end">Bourses<br>avant → après</th>
                                <th class="text-end">Payé<br>avant → après</th>
                                <th class="text-end">Reste à payer<br>avant → après</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($execution['students'] as $student): ?>
                                <tr>
                                    <td><?= h($student['nom'] . ' ' . $student['prenom']) ?></td>
                                    <td><?= h($student['classe_nom']) ?></td>
                                    <td class="text-end">
                                        <?= number_format((float) $student['before']['frais_scolarite_brut'], 0, '.', ' ') ?>
                                        <span class="text-muted">→</span>
                                        <strong class="text-success"><?= number_format((float) $student['after']['frais_scolarite_brut'], 0, '.', ' ') ?></strong> FCFA
                                    </td>
                                    <td class="text-end">
                                        <?= number_format((float) $student['before']['total_reductions'], 0, '.', ' ') ?>
                                        <span class="text-muted">→</span>
                                        <?= number_format((float) $student['after']['total_reductions'], 0, '.', ' ') ?> FCFA
                                    </td>
                                    <td class="text-end">
                                        <?= number_format((float) $student['before']['total_bourses'], 0, '.', ' ') ?>
                                        <span class="text-muted">→</span>
                                        <?= number_format((float) $student['after']['total_bourses'], 0, '.', ' ') ?> FCFA
                                    </td>
                                    <td class="text-end">
                                        <?= number_format((float) $student['before']['total_paye'], 0, '.', ' ') ?>
                                        <span class="text-muted">→</span>
                                        <?= number_format((float) $student['after']['total_paye'], 0, '.', ' ') ?> FCFA
                                    </td>
                                    <td class="text-end">
                                        <?= number_format((float) $student['before']['reste_a_payer'], 0, '.', ' ') ?>
                                        <span class="text-muted">→</span>
                                        <?= number_format((float) $student['after']['reste_a_payer'], 0, '.', ' ') ?> FCFA
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="modern-card border-0 shadow-sm overflow-hidden">
        <div class="p-3 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom border-theme-light">
            <div>
                <h5 class="fw-bold mb-1">Élèves détectés</h5>
                <div class="small text-muted">Année scolaire active : #<?= (int) $academicYearId ?></div>
            </div>
            <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-2">
                <?= count($candidates) ?> à vérifier
            </span>
        </div>

        <?php if (empty($candidates)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-check-circle fs-3 d-block mb-2 text-success"></i>
                Aucun élève ne correspond aux critères de réparation.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table-modern mb-0">
                    <thead>
                        <tr>
                            <th>Élève</th>
                            <th>Classe</th>
                            <th class="text-end">Frais brut actuel</th>
                            <th class="text-end">Tarif applicable</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($candidates as $student): ?>
                            <tr>
                                <td><?= h($student['nom'] . ' ' . $student['prenom']) ?></td>
                                <td><?= h($student['classe_nom']) ?></td>
                                <td class="text-end"><?= number_format((float) $student['frais_scolarite_brut'], 0, '.', ' ') ?> FCFA</td>
                                <td class="text-end fw-bold"><?= number_format((float) $student['expected_amount'], 0, '.', ' ') ?> FCFA</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top border-theme-light">
                <form method="POST" action="/students/financial-resync<?= $requestedStudentId > 0 ? '?student_id=' . $requestedStudentId : '' ?>" onsubmit="return confirm('Une sauvegarde locale sera créée, puis les montants financiers de <?= count($candidates) ?> élève(s) seront recalculés. Continuer ?');">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold">
                        <i class="bi bi-arrow-repeat me-1"></i> Sauvegarder et resynchroniser
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($execution !== null && $requestedStudentId > 0): ?>
        <div class="mt-3">
            <a href="/students" class="btn btn-primary rounded-pill px-4 fw-bold">
                Retour à la liste des élèves
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
