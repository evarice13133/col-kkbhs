<?php
$hasActiveFilters = ($filters['period'] !== 'all') 
    || ($filters['teaching_type_id'] > 0) 
    || (($filters['class_id'] ?? 0) > 0) 
    || ($filters['entity_type'] !== 'all') 
    || ($filters['action'] !== 'all');
?>
<?php if ($hasActiveFilters): ?>
    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
        <span class="text-muted-theme small fw-bold me-1"><?= __('active_filters') ?> :</span>
        
        <?php if ($filters['period'] !== 'all'): ?>
            <?php
            $periodLabel = __('all_m');
            if ($filters['period'] === 'today') $periodLabel = __('today');
            elseif ($filters['period'] === 'week') $periodLabel = __('this_week');
            elseif ($filters['period'] === 'month') $periodLabel = __('this_month');
            elseif ($filters['period'] === 'custom') $periodLabel = __('custom_range') . ' (' . h($filters['start_date']) . ' - ' . h($filters['end_date']) . ')';
            ?>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 rounded-pill px-3 py-1.5 d-flex align-items-center gap-2 small">
                <span><?= __('period') ?> : <strong><?= $periodLabel ?></strong></span>
                <a href="javascript:void(0)" onclick="resetFilter('period')" class="text-primary hover-danger text-decoration-none fw-bold" style="font-size: 1.1em; line-height: 1;">&times;</a>
            </span>
        <?php endif; ?>

        <?php if ($filters['teaching_type_id'] > 0): ?>
            <?php
            $ttName = '';
            foreach ($teachingTypes as $tt) {
                if ((int)$tt['id'] === $filters['teaching_type_id']) {
                    $ttName = $tt['nom'];
                    break;
                }
            }
            ?>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 rounded-pill px-3 py-1.5 d-flex align-items-center gap-2 small">
                <span><?= __('teaching_type') ?> : <strong><?= h($ttName) ?></strong></span>
                <a href="javascript:void(0)" onclick="resetFilter('teaching_type')" class="text-primary hover-danger text-decoration-none fw-bold" style="font-size: 1.1em; line-height: 1;">&times;</a>
            </span>
        <?php endif; ?>

        <?php if (($filters['class_id'] ?? 0) > 0): ?>
            <?php
            $className = '';
            foreach ($classes as $c) {
                if ((int)$c['id'] === (int)$filters['class_id']) {
                    $className = $c['nom'];
                    break;
                }
            }
            ?>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 rounded-pill px-3 py-1.5 d-flex align-items-center gap-2 small">
                <span><?= __('class') ?> : <strong><?= h($className) ?></strong></span>
                <a href="javascript:void(0)" onclick="resetFilter('class')" class="text-primary hover-danger text-decoration-none fw-bold" style="font-size: 1.1em; line-height: 1;">&times;</a>
            </span>
        <?php endif; ?>

        <?php if ($filters['entity_type'] !== 'all'): ?>
            <?php
            $entityLabel = $filters['entity_type'];
            if ($filters['entity_type'] === 'payment') $entityLabel = __('entity_payment');
            elseif ($filters['entity_type'] === 'student_payment') $entityLabel = __('entity_student_payment');
            elseif ($filters['entity_type'] === 'student_discount') $entityLabel = __('entity_student_discount');
            elseif ($filters['entity_type'] === 'class_discount') $entityLabel = __('entity_class_discount');
            elseif ($filters['entity_type'] === 'student_scholarship') $entityLabel = __('entity_student_scholarship');
            elseif ($filters['entity_type'] === 'class_scholarship') $entityLabel = __('entity_class_scholarship');
            elseif ($filters['entity_type'] === 'class_finance') $entityLabel = __('entity_class_finance');
            ?>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 rounded-pill px-3 py-1.5 d-flex align-items-center gap-2 small">
                <span><?= __('entity') ?> : <strong><?= h($entityLabel) ?></strong></span>
                <a href="javascript:void(0)" onclick="resetFilter('entity_type')" class="text-primary hover-danger text-decoration-none fw-bold" style="font-size: 1.1em; line-height: 1;">&times;</a>
            </span>
        <?php endif; ?>

        <?php if ($filters['action'] !== 'all'): ?>
            <?php
            $actionLabel = $filters['action'];
            if ($filters['action'] === 'create') $actionLabel = __('action_create');
            elseif ($filters['action'] === 'update') $actionLabel = __('action_update');
            elseif ($filters['action'] === 'delete') $actionLabel = __('action_delete');
            elseif ($filters['action'] === 'status') $actionLabel = __('action_status');
            ?>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 rounded-pill px-3 py-1.5 d-flex align-items-center gap-2 small">
                <span><?= __('action') ?> : <strong><?= h($actionLabel) ?></strong></span>
                <a href="javascript:void(0)" onclick="resetFilter('action')" class="text-primary hover-danger text-decoration-none fw-bold" style="font-size: 1.1em; line-height: 1;">&times;</a>
            </span>
        <?php endif; ?>

        <a href="/financial-history" class="btn btn-link btn-sm text-decoration-none text-muted-theme hover-primary fw-bold p-0 ms-2 small"><?= __('clear_filters') ?></a>
    </div>
<?php endif; ?>
