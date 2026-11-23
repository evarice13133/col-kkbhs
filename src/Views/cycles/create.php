<?php
$title = __('add_cycle_title');
ob_start();
?>

<div class="animate-fade-in container-fluid py-2">
    <!-- Compact Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="fw-black text-main-theme mb-0 fs-4"><?= __('add_new_cycle') ?></h2>
        <a href="/cycles" class="btn btn-sm btn-light-theme rounded-pill px-3 border-theme-light">
            <i class="bi bi-arrow-left me-1"></i> <?= __('back_to_list') ?>
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger rounded-4" role="alert">
            <?= htmlspecialchars((string) $error) ?>
        </div>
    <?php endif; ?>

    <form action="/cycles/store" method="POST" id="cycleCreateForm">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">

        <div class="subject-card-compact border-0 shadow-sm overflow-hidden mb-4">
            <div class="card-body p-4">
                
                <!-- Identification Section -->
                <div class="row g-4 mb-4">
                    <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                        <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1"><?= __('cycle_identification') ?></h6>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('cycle_name_label') ?></label>
                        <input type="text" name="nom" class="form-control premium-input" 
                            placeholder="<?= __('cycle_name_placeholder') ?>" value="<?= htmlspecialchars((string) ($cycle['nom'] ?? '')) ?>" required autofocus>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">Type Enseignement *</label>
                        <select name="teaching_type_id" id="cycleCreateTeachingType" class="form-select premium-input border-primary border-opacity-25" required>
                            <option value="" disabled <?= empty($cycle['teaching_type_id']) ? 'selected' : '' ?>>Sélectionner un type d'enseignement</option>
                            <?php foreach ($teachingTypes as $tt): ?>
                                <option value="<?= $tt['id'] ?>" <?= (int) ($cycle['teaching_type_id'] ?? 0) === (int) $tt['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $tt['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('honor_roll_threshold_label') ?></label>
                        <input type="number" name="honor_roll_threshold" step="0.01" min="0" max="20" class="form-control premium-input">
                        <div class="form-text extra-small mt-1 opacity-75"><?= __('honor_roll_threshold_help') ?></div>
                    </div>
                    <?php if (!empty($allLevels)): ?>
                        <div class="col-12">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2">
                                Niveaux d'études associés
                            </label>
                            <div class="row g-2 p-3 rounded-4 bg-light bg-opacity-50 border">
                                <?php $selectedLevelIds = array_map('strval', $cycle['level_ids'] ?? []); ?>
                                <?php foreach ($allLevels as $lvl): ?>
                                    <div class="col-6 cycle-create-level-option">
                                        <div class="form-check">
                                            <input class="form-check-input cycle-create-level-checkbox" type="checkbox" name="level_ids[]" value="<?= (int) $lvl['id'] ?>" data-teaching-type-id="<?= (int) $lvl['teaching_type_id'] ?>" id="cycle_create_level_<?= (int) $lvl['id'] ?>" <?= in_array((string) $lvl['id'], $selectedLevelIds, true) ? 'checked' : '' ?>>
                                            <label class="form-check-label small fw-semibold text-main-theme cursor-pointer" for="cycle_create_level_<?= (int) $lvl['id'] ?>">
                                                <?= htmlspecialchars((string) $lvl['nom']) ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Action Footer -->
                <div class="d-flex justify-content-end border-top border-theme-light pt-4 mt-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm transition-base scale-on-hover">
                        <i class="bi bi-check-circle-fill me-2"></i> Valider
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
(function () {
    const teachingTypeSelect = document.getElementById('cycleCreateTeachingType');
    if (!teachingTypeSelect) return;

    function updateLevelVisibility() {
        document.querySelectorAll('.cycle-create-level-checkbox').forEach(function (checkbox) {
            const levelOption = checkbox.closest('.cycle-create-level-option');
            const matchesType = !teachingTypeSelect.value || checkbox.dataset.teachingTypeId === teachingTypeSelect.value;
            if (levelOption) levelOption.hidden = !matchesType;
            if (!matchesType) checkbox.checked = false;
        });
    }

    teachingTypeSelect.addEventListener('change', updateLevelVisibility);
    updateLevelVisibility();
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
