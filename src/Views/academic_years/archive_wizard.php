<?php $title = "Archivage de l'Année";
ob_start(); ?>

<div class="row min-vh-100 justify-content-center align-items-center mt-n5">
    <div class="col-lg-6">
        <div class="card shadow-lg border-0 border-top border-danger border-4">
            <div class="card-body p-5">
                <h2 class="text-danger fw-bold text-center mb-4">Archiver l'année
                    "<?= htmlspecialchars((string) $year['nom']) ?>"</h2>

                <div class="alert alert-warning shadow-sm">
                    <strong>⚠️ Processus système lourd :</strong> L'archivage clôturera définitivement cette année
                    académique.
                    <br><br>
                    <strong>1.</strong> Une sauvegarde complète (Fichier SQL compressé dans un ZIP) sera générée.<br>
                    <strong>2.</strong> Elle sera synchronisée vers GitHub par processus background.<br>
                    <strong>3.</strong> Les tables de votre base de données seront remises à zéro selon vos filtres afin
                    de commencer une année à neuf.
                </div>

                <form action="/academic_years/do_archive" method="POST">
                    <input type="hidden" name="year_id" value="<?= $year['id'] ?>">

                    <h5 class="fw-bold mt-4 mb-3 border-bottom pb-2">Que souhaitez-vous effacer de l'espace de travail ?
                    </h5>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="truncate_grades" id="grades" checked>
                        <label class="form-check-label fw-bold" for="grades">Vider le carnet de notes
                            (Recommandé)</label>
                        <div class="text-muted small">Les notes enregistrées de l'année précédente disparaîtront de
                            l'interface active.</div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="truncate_students" id="students" checked>
                        <label class="form-check-label fw-bold" for="students">Vider le registre des Étudiants
                            (Recommandé)</label>
                        <div class="text-muted small">Purgera tous les élèves. Recommandé si vous avez un recrutement
                            annuel d'une nouvelle promotion distincte.</div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="truncate_subjects" id="subjects">
                        <label class="form-check-label fw-bold" for="subjects">Vider les Matières Scolaires
                            (Optionnel)</label>
                        <div class="text-muted small">Généralement, on laisse cette case DÉCOCHÉE pour conserver le
                            programme existant l'année suivante.</div>
                    </div>

                    <div class="form-check form-switch mb-4 mt-4 bg-light p-3 border rounded">
                        <input class="form-check-input text-danger bg-danger border-danger ms-0 me-2" type="checkbox"
                            name="truncate_users" id="users">
                        <label class="form-check-label fw-bold text-danger pt-1" for="users"><i class="fw-bold">Purge du
                                staff :</i> Supprimer les Utilisateurs</label>
                        <div class="text-muted small ms-4">Supprimera tous les comptes Enseignants et Administrateurs.
                            Seul votre compte actuel restera debout. Utile en cas de changement complet d'équipe à 100%.
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-danger btn-lg shadow-sm"
                            onclick="return confirm('Attention: Il n\'y aura pas de retour à l\'écran précédent. La purge base de données va démarrer. Confirmer définitivement ?');">Lancer
                            l'Archivage et clôturer</button>
                    </div>
                    <div class="text-center mt-3">
                        <a href="/academic_years" class="text-decoration-none text-muted">Annuler et revenir à la
                            liste</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>