<?php
$hasActiveFilters = (!empty($filters['q'])) 
    || ((int)($filters['teaching_type_id'] ?? 0) > 0) 
    || ((int)($filters['class_id'] ?? 0) > 0) 
    || (!empty($filters['withdrawn']))
    || (!empty($filters['only_mine']));
?>
<?php if ($hasActiveFilters): ?>
    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
        <span class="text-muted-theme small fw-bold me-1"><?= __('active_filters') ?> :</span>
        
        <?php if (!empty($filters['q'])): ?>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 rounded-pill px-3 py-1.5 d-flex align-items-center gap-2 small">
                <span><?= __('search') ?? 'Recherche' ?> : <strong><?= htmlspecialchars($filters['q']) ?></strong></span>
                <a href="javascript:void(0)" onclick="resetFilter('q')" class="text-primary hover-danger text-decoration-none fw-bold" style="font-size: 1.1em; line-height: 1;">&times;</a>
            </span>
        <?php endif; ?>

        <?php if (!empty($filters['only_mine'])): ?>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 rounded-pill px-3 py-1.5 d-flex align-items-center gap-2 small">
                <span><strong><?= __('my_registrations_only') ?? 'Mes inscriptions uniquement' ?></strong></span>
                <a href="javascript:void(0)" onclick="resetFilter('only_mine')" class="text-primary hover-danger text-decoration-none fw-bold" style="font-size: 1.1em; line-height: 1;">&times;</a>
            </span>
        <?php endif; ?>

        <?php if ((int)($filters['teaching_type_id'] ?? 0) > 0): ?>
            <?php
            $ttName = '';
            foreach ($teachingTypes as $tt) {
                if ((int)$tt['id'] === (int)$filters['teaching_type_id']) {
                    $ttName = $tt['nom'];
                    break;
                }
            }
            ?>
            <?php if (!empty($ttName)): ?>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 rounded-pill px-3 py-1.5 d-flex align-items-center gap-2 small">
                    <span><?= __('teaching_type') ?? 'Enseignement' ?> : <strong><?= htmlspecialchars($ttName) ?></strong></span>
                    <a href="javascript:void(0)" onclick="resetFilter('teaching_type')" class="text-primary hover-danger text-decoration-none fw-bold" style="font-size: 1.1em; line-height: 1;">&times;</a>
                </span>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ((int)($filters['class_id'] ?? 0) > 0): ?>
            <?php
            $className = '';
            foreach ($classes as $c) {
                if ((int)$c['id'] === (int)$filters['class_id']) {
                    $className = $c['nom'];
                    break;
                }
            }
            ?>
            <?php if (!empty($className)): ?>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 rounded-pill px-3 py-1.5 d-flex align-items-center gap-2 small">
                    <span><?= __('class') ?? 'Classe' ?> : <strong><?= htmlspecialchars($className) ?></strong></span>
                    <a href="javascript:void(0)" onclick="resetFilter('class')" class="text-primary hover-danger text-decoration-none fw-bold" style="font-size: 1.1em; line-height: 1;">&times;</a>
                </span>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($filters['withdrawn'])): ?>
            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 rounded-pill px-3 py-1.5 d-flex align-items-center gap-2 small">
                <span><strong><?= __('show_withdrawn') ?? 'Démissionnaires uniquement' ?></strong></span>
                <a href="javascript:void(0)" onclick="resetFilter('withdrawn')" class="text-danger hover-danger text-decoration-none fw-bold" style="font-size: 1.1em; line-height: 1;">&times;</a>
            </span>
        <?php endif; ?>

        <a href="/students" class="btn btn-link btn-sm text-decoration-none text-muted-theme hover-primary fw-bold p-0 ms-2 small"><?= __('clear_filters') ?? 'Tout effacer' ?></a>
    </div>
<?php endif; ?>
