<?php
$title = __('discount_mgmt');

// Calcul des statistiques pour les KPIs
$activeStudentDiscountsCount = 0;
$activeClassDiscountsCount = 0;
$totalStudentDiscountVal = 0;
$totalClassDiscountVal = 0;

foreach ($studentDiscounts as $sd) {
    if ($sd['status'] === 'active') {
        $activeStudentDiscountsCount++;
        if ($sd['amount_type'] === 'fixed') {
            $totalStudentDiscountVal += (float)$sd['amount'];
        }
    }
}

foreach ($classDiscounts as $cd) {
    if ($cd['status'] === 'active') {
        $activeClassDiscountsCount++;
        if ($cd['amount_type'] === 'fixed') {
            $totalClassDiscountVal += (float)$cd['amount'];
        }
    }
}

// Groupement des élèves par classe pour le formulaire
$groupedStudents = [];
foreach ($students as $s) {
    $cName = $s['classe_nom'] ?: 'Sans classe';
    $groupedStudents[$cName][] = $s;
}
ksort($groupedStudents);

ob_start();
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-black text-main-theme mb-0 fs-4"><?= __('discount_mgmt') ?></h2>
            <p class="text-muted-theme small mb-0"><?= __('discount_mgmt_subtitle') ?></p>
        </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4 animate-fade-in">
        <div class="col-6 col-md-3">
            <div class="modern-card kpi-card border-0 shadow-sm">
                <div class="kpi-icon-wrapper bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-person-fill-check"></i>
                </div>
                <div class="kpi-value text-primary"><?= $activeStudentDiscountsCount ?></div>
                <div class="kpi-label"><?= __('active_indiv_discounts') ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="modern-card kpi-card border-0 shadow-sm">
                <div class="kpi-icon-wrapper bg-info bg-opacity-10 text-info">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="kpi-value text-info"><?= $activeClassDiscountsCount ?></div>
                <div class="kpi-label"><?= __('active_coll_discounts') ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="modern-card kpi-card border-0 shadow-sm">
                <div class="kpi-icon-wrapper bg-success bg-opacity-10 text-success">
                    <i class="bi bi-currency-exchange"></i>
                </div>
                <div class="kpi-value text-success"><?= number_format($totalStudentDiscountVal, 0, '.', ' ') ?> <span class="fs-7 text-muted fw-normal">FCFA</span></div>
                <div class="kpi-label"><?= __('val_indiv_discounts') ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="modern-card kpi-card border-0 shadow-sm">
                <div class="kpi-icon-wrapper bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-calculator"></i>
                </div>
                <div class="kpi-value text-warning"><?= number_format($totalClassDiscountVal, 0, '.', ' ') ?> <span class="fs-7 text-muted fw-normal">FCFA</span></div>
                <div class="kpi-label"><?= __('val_coll_discounts') ?></div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs (SaaS Style) -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center flex-wrap gap-3">
            <ul class="nav nav-pills nav-pills-custom" id="discountTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-4 py-2 fw-bold" id="individual-tab" data-bs-toggle="tab" data-bs-target="#individual" type="button" role="tab" aria-controls="individual" aria-selected="true">
                        <i class="bi bi-person me-1"></i> <?= __('discounts_indiv') ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 py-2 fw-bold ms-md-2" id="collective-tab" data-bs-toggle="tab" data-bs-target="#collective" type="button" role="tab" aria-controls="collective" aria-selected="false">
                        <i class="bi bi-people me-1"></i> <?= __('discounts_coll') ?>
                    </button>
                </li>
            </ul>

            <!-- Search input integrated next to tabs -->
            <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2 border-theme-light" style="max-width: 250px; border: 1px solid var(--border-color);">
                <span class="input-group-text border-0 bg-transparent text-primary py-1.5">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="discount-search-input" class="form-control border-0 bg-transparent shadow-none py-1.5 small text-main" placeholder="<?= __('quick_search_placeholder') ?>" style="font-size: 0.85rem;">
            </div>
        </div>

        <div class="tab-actions">
            <!-- Individual trigger -->
            <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="btn-add-indiv" data-bs-toggle="modal" data-bs-target="#addIndividualDiscountModal">
                <i class="bi bi-plus-circle-fill me-2"></i> <?= __('grant_discount') ?>
            </button>
            <!-- Collective trigger (hidden by default, managed by JS or shown together) -->
            <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm d-none" id="btn-add-coll" data-bs-toggle="modal" data-bs-target="#addCollectiveDiscountModal">
                <i class="bi bi-plus-circle-fill me-2"></i> <?= __('new_coll_discount') ?>
            </button>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="tab-content" id="discountTabsContent">
        <!-- 1. INDIVIDUAL DISCOUNTS -->
        <div class="tab-pane fade show active" id="individual" role="tabpanel" aria-labelledby="individual-tab">
            <div class="modern-card border-0 shadow-sm overflow-hidden">
                <div class="table-responsive">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th class="ps-4"><?= __('grade_export_student') ?></th>
                                <th><?= __('matricule') ?></th>
                                <th><?= __('class') ?></th>
                                <th class="text-end"><?= __('col_value') ?></th>
                                <th><?= __('motive') ?></th>
                                <th><?= __('date_effet') ?></th>
                                <th class="text-center"><?= __('status') ?></th>
                                <th class="pe-4 text-center"><?= __('actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($studentDiscounts)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-info-circle fs-4 d-block mb-2 text-secondary"></i>
                                        <?= __('no_indiv_discount') ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($studentDiscounts as $sd): ?>
                                    <tr class="student-row">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                                     style="width: 36px; height: 36px; font-size: 1rem; border: 1px solid rgba(var(--primary-rgb), 0.2);">
                                                    <?= strtoupper(substr((string) $sd['student_nom'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-main-theme" style="font-size: 0.9rem;">
                                                        <?= h($sd['student_nom']) ?>
                                                    </div>
                                                    <div class="text-muted opacity-75" style="font-size: 0.75rem;">
                                                        <?= h($sd['student_prenom']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><code class="small text-secondary"><?= h($sd['matricule']) ?></code></td>
                                        <td>
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 rounded-pill">
                                                <?= h($sd['classe_nom'] ?: '-') ?>
                                            </span>
                                        </td>
                                        <td class="text-end fw-black">
                                            <?php if ($sd['amount_type'] === 'percentage'): ?>
                                                <span class="badge-premium badge-premium-info">
                                                    <i class="bi bi-percent"></i> <?= number_format($sd['amount'], 1) ?> %
                                                </span>
                                            <?php else: ?>
                                                <span class="badge-premium badge-premium-success">
                                                    <?= number_format($sd['amount'], 0, '.', ' ') ?> <span class="fw-normal" style="font-size: 0.65rem;">FCFA</span>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="text-main-theme fw-medium"><?= h($sd['motive']) ?></span></td>
                                        <td><?= date('d/m/Y', strtotime($sd['date_effet'])) ?></td>
                                        <td class="text-center">
                                            <?php if ($sd['status'] === 'active'): ?>
                                                <span class="badge-premium badge-premium-success"><i class="bi bi-check-circle-fill"></i> <?= __('active') ?></span>
                                            <?php else: ?>
                                                <span class="badge-premium badge-premium-secondary"><i class="bi bi-x-circle-fill"></i> <?= __('inactive') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <a href="/discounts/toggle?id=<?= $sd['id'] ?>&scope=student" class="btn btn-sm btn-action-modern btn-confirm-toggle <?= $sd['status'] === 'active' ? 'text-warning' : 'text-success' ?>" data-confirm="<?= __('confirm_toggle_discount') ?>" title="<?= $sd['status'] === 'active' ? __('deactivate') : __('activate') ?>">
                                                    <i class="bi bi-power fs-5"></i>
                                                </a>
                                                <a href="/discounts/delete?id=<?= $sd['id'] ?>&scope=student" class="btn btn-sm btn-action-modern btn-confirm-delete text-danger" data-confirm="<?= __('confirm_delete_discount') ?>" title="<?= __('delete') ?>">
                                                    <i class="bi bi-trash-fill fs-5"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 2. COLLECTIVE DISCOUNTS -->
        <div class="tab-pane fade" id="collective" role="tabpanel" aria-labelledby="collective-tab">
            <div class="modern-card border-0 shadow-sm overflow-hidden">
                <div class="table-responsive">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th class="ps-4"><?= __('target_class') ?></th>
                                <th class="text-end"><?= __('col_value') ?></th>
                                <th><?= __('motive') ?></th>
                                <th><?= __('date_effet') ?></th>
                                <th class="text-center"><?= __('status') ?></th>
                                <th class="pe-4 text-center"><?= __('actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($classDiscounts)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-info-circle fs-4 d-block mb-2 text-secondary"></i>
                                        <?= __('no_coll_discount') ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($classDiscounts as $cd): ?>
                                    <tr class="student-row">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                                     style="width: 36px; height: 36px; font-size: 1rem; border: 1px solid rgba(var(--primary-rgb), 0.2);">
                                                    <i class="bi bi-door-open"></i>
                                                </div>
                                                <div class="fw-bold text-main-theme"><?= h($cd['classe_nom']) ?></div>
                                            </div>
                                        </td>
                                        <td class="text-end fw-black">
                                            <?php if ($cd['amount_type'] === 'percentage'): ?>
                                                <span class="badge-premium badge-premium-info">
                                                    <i class="bi bi-percent"></i> <?= number_format($cd['amount'], 1) ?> %
                                                </span>
                                            <?php else: ?>
                                                <span class="badge-premium badge-premium-success">
                                                    <?= number_format($cd['amount'], 0, '.', ' ') ?> <span class="fw-normal" style="font-size: 0.65rem;">FCFA</span>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="text-main-theme fw-medium"><?= h($cd['motive']) ?></span></td>
                                        <td><?= date('d/m/Y', strtotime($cd['date_effet'])) ?></td>
                                        <td class="text-center">
                                            <?php if ($cd['status'] === 'active'): ?>
                                                <span class="badge-premium badge-premium-success"><i class="bi bi-check-circle-fill"></i> <?= __('active') ?></span>
                                            <?php else: ?>
                                                <span class="badge-premium badge-premium-secondary"><i class="bi bi-x-circle-fill"></i> <?= __('inactive') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <a href="/discounts/toggle?id=<?= $cd['id'] ?>&scope=class" class="btn btn-sm btn-action-modern btn-confirm-toggle <?= $cd['status'] === 'active' ? 'text-warning' : 'text-success' ?>" data-confirm="<?= __('confirm_toggle_coll_discount') ?>" title="<?= $cd['status'] === 'active' ? __('deactivate') : __('activate') ?>">
                                                    <i class="bi bi-power fs-5"></i>
                                                </a>
                                                <a href="/discounts/delete?id=<?= $cd['id'] ?>&scope=class" class="btn btn-sm btn-action-modern btn-confirm-delete text-danger" data-confirm="<?= __('confirm_delete_coll_discount') ?>" title="<?= __('delete') ?>">
                                                    <i class="bi bi-trash-fill fs-5"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Individual Discount -->
<div class="modal fade" id="addIndividualDiscountModal" tabindex="-1" aria-labelledby="addIndivModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-black text-main-theme" id="addIndivModalLabel"><?= __('new_indiv_discount') ?></h5>
                <button type="button" class="btn-close" data-bs-shadow="none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/discounts/store" method="POST">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                <input type="hidden" name="scope" value="student">

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('select_student_label') ?></label>
                            <div class="mb-2">
                                <input type="text" class="form-control premium-input modal-student-filter" placeholder="<?= __('search_filter_placeholder') ?>">
                            </div>
                            <select name="student_id" class="form-select premium-input" required>
                                <option value="" disabled selected><?= __('search_student_dots') ?></option>
                                <?php foreach ($groupedStudents as $class => $classStudents): ?>
                                    <optgroup label="<?= h($class) ?>">
                                        <?php foreach ($classStudents as $s): ?>
                                            <option value="<?= $s['id'] ?>"><?= h($s['nom']) ?> <?= h($s['prenom']) ?> (<?= h($s['matricule']) ?>)</option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('montant_valeur') ?></label>
                            <input type="number" name="amount" min="1" class="form-control premium-input fw-bold" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('discount_type_label') ?></label>
                            <select name="amount_type" class="form-select premium-input" required>
                                <option value="fixed"><?= __('amount_fixed_fcfa') ?></option>
                                <option value="percentage"><?= __('percentage_val') ?></option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('motive_discount_type') ?></label>
                            <select name="discount_type_id" class="form-select premium-input" required>
                                <option value="" disabled selected><?= __('choose_type_placeholder') ?></option>
                                <?php foreach ($discountTypes as $dt): ?>
                                    <option value="<?= $dt['id'] ?>"><?= h($dt['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('date_effet_required') ?></label>
                            <input type="date" name="date_effet" class="form-control premium-input" required value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('initial_status') ?></label>
                            <select name="status" class="form-select premium-input" required>
                                <option value="active"><?= __('active_immediately') ?></option>
                                <option value="inactive"><?= __('inactive') ?></option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('comment') ?></label>
                            <textarea name="commentaire" class="form-control premium-input" rows="2" placeholder="<?= __('observation_placeholder') ?>"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light-theme rounded-pill px-4" data-bs-dismiss="modal"><?= __('cancel_btn') ?></button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="bi bi-check-circle-fill me-2"></i><?= __('validate_btn') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add Collective Discount -->
<div class="modal fade" id="addCollectiveDiscountModal" tabindex="-1" aria-labelledby="addCollModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-black text-main-theme" id="addCollModalLabel"><?= __('new_coll_discount') ?></h5>
                <button type="button" class="btn-close" data-bs-shadow="none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/discounts/store" method="POST">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                <input type="hidden" name="scope" value="class">

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Filtres de structure -->
                        <div class="col-12 bg-light bg-opacity-50 p-3 rounded-4 border-theme-light" style="background-color: var(--bg-body); border: 1px solid var(--border-color);">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1" style="font-size: 0.68rem; color: var(--text-muted); opacity: 0.85;"><?= __('teaching') ?></label>
                                    <select class="form-select filter-teaching-type premium-select" style="font-size: 0.8rem; height: 38px !important; padding: 4px 12px; border-radius: 8px !important;">
                                        <option value=""><?= __('all_m') ?></option>
                                        <?php foreach ($teachingTypes as $tt): ?>
                                            <option value="<?= $tt['id'] ?>"><?= h($tt['nom']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1" style="font-size: 0.68rem; color: var(--text-muted); opacity: 0.85;"><?= __('class_section') ?></label>
                                    <select class="form-select filter-section premium-select" style="font-size: 0.8rem; height: 38px !important; padding: 4px 12px; border-radius: 8px !important;">
                                        <option value=""><?= __('all_f') ?></option>
                                        <?php foreach ($sections as $sec): ?>
                                            <option value="<?= $sec['id'] ?>"><?= h($sec['nom']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1" style="font-size: 0.68rem; color: var(--text-muted); opacity: 0.85;"><?= __('cycle') ?></label>
                                    <select class="form-select filter-cycle premium-select" style="font-size: 0.8rem; height: 38px !important; padding: 4px 12px; border-radius: 8px !important;">
                                        <option value=""><?= __('all_m') ?></option>
                                        <?php foreach ($cycles as $cyc): ?>
                                            <option value="<?= $cyc['id'] ?>"><?= h($cyc['nom']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>                        <div class="col-12">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('select_class_editor') ?></label>
                            <select name="class_id" class="form-select premium-input class-select-element" required>
                                <option value="" disabled selected><?= __('select_dots') ?></option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?= $c['id'] ?>" 
                                             data-teaching-type="<?= htmlspecialchars($c['teaching_type_id'] ?? '') ?>"
                                             data-section="<?= htmlspecialchars($c['section_id'] ?? '') ?>"
                                             data-cycle="<?= htmlspecialchars($c['cycle_id'] ?? '') ?>"><?= h($c['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('montant_valeur') ?></label>
                            <input type="number" name="amount" min="1" class="form-control premium-input fw-bold" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('discount_type_label') ?></label>
                            <select name="amount_type" class="form-select premium-input" required>
                                <option value="fixed"><?= __('amount_fixed_fcfa') ?></option>
                                <option value="percentage"><?= __('percentage_val') ?></option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('motive_discount_type') ?></label>
                            <select name="discount_type_id" class="form-select premium-input" required>
                                <option value="" disabled selected><?= __('choose_type_placeholder') ?></option>
                                <?php foreach ($discountTypes as $dt): ?>
                                    <option value="<?= $dt['id'] ?>"><?= h($dt['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('date_effet_required') ?></label>
                            <input type="date" name="date_effet" class="form-control premium-input" required value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('initial_status') ?></label>
                            <select name="status" class="form-select premium-input" required>
                                <option value="active"><?= __('active_immediately') ?></option>
                                <option value="inactive"><?= __('inactive') ?></option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('comment') ?></label>
                            <textarea name="commentaire" class="form-control premium-input" rows="2" placeholder="<?= __('observation_placeholder') ?>"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light-theme rounded-pill px-4" data-bs-dismiss="modal"><?= __('cancel_btn') ?></button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="bi bi-check-circle-fill me-2"></i><?= __('validate_btn') ?>
                    </button>
                </div> </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // JavaScript helper to toggle the active trigger button depending on selected tab
    const individualTab = document.getElementById('individual-tab');
    const collectiveTab = document.getElementById('collective-tab');
    const btnAddIndiv = document.getElementById('btn-add-indiv');
    const btnAddColl = document.getElementById('btn-add-coll');

    individualTab.addEventListener('shown.bs.tab', function () {
        btnAddIndiv.classList.remove('d-none');
        btnAddColl.classList.add('d-none');
    });

    collectiveTab.addEventListener('shown.bs.tab', function () {
        btnAddIndiv.classList.add('d-none');
        btnAddColl.classList.remove('d-none');
    });

    // Instant client-side search filter
    const searchInput = document.getElementById('discount-search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
            const activePane = document.querySelector('.tab-pane.active');
            if (!activePane) return;
            const rows = activePane.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                if (row.querySelector('td[colspan]')) return; // ignore empty state row
                const text = row.textContent.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                if (text.includes(query)) {
                    row.style.setProperty('display', '', 'important');
                } else {
                    row.style.setProperty('display', 'none', 'important');
                }
            });
        });
    }

    // Reset search when tab changes
    document.querySelectorAll('#discountTabs button').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function() {
            if (searchInput) {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
            }
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
