<?php
$title = __('edit_teaching_type') ?? 'Modifier un Type Enseignement';
ob_start();
?>

<div class="animate-fade-in container-fluid py-2">
    <!-- Compact Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="fw-black text-main-theme mb-0 fs-4"><?= $title ?></h2>
        <a href="/teaching_types" class="btn btn-sm btn-light-theme rounded-pill px-3 border-theme-light">
            <i class="bi bi-arrow-left me-1"></i> <?= __('back_to_list') ?>
        </a>
    </div>

    <form action="/teaching_types/update?id=<?= $teachingType['id'] ?>" method="POST" id="teachingTypeEditForm">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">

        <div class="subject-card-compact border-0 shadow-sm overflow-hidden mb-4">
            <div class="card-body p-4">
                
                <!-- Identification Section -->
                <div class="row g-4 mb-4">
                    <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                        <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1">Paramètres du Type</h6>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Nom</label>
                        <input type="text" name="nom" class="form-control premium-input" 
                            placeholder="Nom du type" value="<?= h($teachingType['nom']) ?>" required autofocus>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Code</label>
                        <input type="text" name="code" class="form-control premium-input" 
                            placeholder="Code court" value="<?= h($teachingType['code']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Position</label>
                        <input type="number" name="position" class="form-control premium-input" value="<?= h($teachingType['position']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Actif</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="actif" id="actifSwitch" value="1" <?= $teachingType['actif'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="actifSwitch">Activer ce type</label>
                        </div>
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="d-flex justify-content-end border-top border-theme-light pt-4 mt-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm transition-base scale-on-hover">
                        <i class="bi bi-save2-fill me-2"></i> Mettre à jour
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
