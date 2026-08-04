<?php
$title = __('timetables_menu') . " - " . __('app_name');
ob_start();
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h2 class="fw-black text-main-theme mb-0 fs-4">
                <i class="bi bi-calendar3-week text-primary me-2"></i><?= __('timetables_menu') ?>
            </h2>
            <p class="text-muted-theme small mb-0"><?= __('timetables_subtitle') ?></p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="/timetables/slots" class="btn btn-sm btn-light-theme rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-1 border-theme-light shadow-sm">
                <i class="bi bi-clock text-primary"></i> <?= __('timetables_slots_menu') ?>
            </a>
            <a href="/timetables/rooms" class="btn btn-sm btn-light-theme rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-1 border-theme-light shadow-sm">
                <i class="bi bi-building text-info"></i> <?= __('timetables_rooms_menu') ?>
            </a>
            <a href="/timetables/weeks" class="btn btn-sm btn-light-theme rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-1 border-theme-light shadow-sm">
                <i class="bi bi-calendar-range text-warning"></i> <?= __('timetables_weeks_menu') ?>
            </a>
            <a href="/timetables/wizard" class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-bold shadow-sm d-flex align-items-center gap-1">
                <i class="bi bi-plus-circle-fill"></i> <?= __('timetables_new_wizard') ?>
            </a>
        </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4 animate-fade-in">
        <div class="col-6 col-md-4">
            <div class="modern-card kpi-card border-0 shadow-sm">
                <div class="kpi-icon-wrapper bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-grid-3x3-gap"></i>
                </div>
                <div class="kpi-value text-primary"><?= count($timetables) ?></div>
                <div class="kpi-label"><?= __('timetables_total_kpi') ?></div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="modern-card kpi-card border-0 shadow-sm border-start border-4 border-success">
                <div class="kpi-icon-wrapper bg-success bg-opacity-10 text-success">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="kpi-value text-success">
                    <?= count(array_filter($timetables, fn($t) => $t['statut'] === 'publie')) ?>
                </div>
                <div class="kpi-label"><?= __('timetables_published_kpi') ?></div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="modern-card kpi-card border-0 shadow-sm border-start border-4 border-warning">
                <div class="kpi-icon-wrapper bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-lock-fill"></i>
                </div>
                <div class="kpi-value text-warning">
                    <?= count(array_filter($timetables, fn($t) => $t['is_locked_calc'])) ?>
                </div>
                <div class="kpi-label"><?= __('timetables_locked_kpi') ?></div>
            </div>
        </div>
    </div>

    <!-- Floating Island Filters -->
    <div class="d-flex justify-content-center mb-4">
        <div class="filter-island p-3 shadow-sm">
            <form method="GET" action="/timetables" class="w-100 m-0">
                <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between">
                    <div class="d-flex flex-column flex-sm-row gap-2 flex-grow-1">
                        <!-- Filtre Année -->
                        <div class="flex-grow-1">
                            <select name="year_id" class="form-select border-0 bg-transparent text-main-theme fw-medium" style="font-size: 0.88rem;" onchange="this.form.submit()">
                                <option value=""><?= __('timetables_all_years') ?></option>
                                <?php foreach ($years as $y): ?>
                                    <option value="<?= $y['id'] ?>" <?= $selectedYear == $y['id'] ? 'selected' : '' ?>>
                                        <?= h($y['libelle']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Filtre Classe -->
                        <div class="flex-grow-1">
                            <select name="class_id" class="form-select border-0 bg-transparent text-main-theme fw-medium" style="font-size: 0.88rem;" onchange="this.form.submit()">
                                <option value=""><?= __('timetables_all_classes') ?></option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= $selectedClass == $c['id'] ? 'selected' : '' ?>>
                                        <?= h($c['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Filtre Semaine -->
                        <div class="flex-grow-1">
                            <select name="week_id" class="form-select border-0 bg-transparent text-main-theme fw-medium" style="font-size: 0.88rem;" onchange="this.form.submit()">
                                <option value=""><?= __('timetables_all_weeks') ?></option>
                                <?php foreach ($weeks as $w): ?>
                                    <option value="<?= $w['id'] ?>" <?= $selectedWeek == $w['id'] ? 'selected' : '' ?>>
                                        <?= h($w['libelle']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex gap-2 align-items-center justify-content-end">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm text-nowrap">
                            <i class="bi bi-funnel-fill me-1"></i> <?= __('timetables_filter_btn') ?>
                        </button>
                        <a href="/timetables" class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center border-theme-light" style="width: 40px; height: 40px;" title="<?= __('timetables_reset_btn') ?>">
                            <i class="bi bi-arrow-counterclockwise fs-5"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in">
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th class="ps-4"><?= __('timetables_col_title_class') ?></th>
                        <th><?= __('timetables_col_type_cycle') ?></th>
                        <th><?= __('timetables_col_week') ?></th>
                        <th class="text-center"><?= __('timetables_col_status') ?></th>
                        <th class="text-center"><?= __('timetables_col_lock') ?></th>
                        <th class="pe-4 text-end"><?= __('timetables_col_actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($timetables)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-3 d-block mb-2 text-secondary"></i>
                                <?= __('timetables_no_found') ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($timetables as $t): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                             style="width: 36px; height: 36px; font-size: 1rem; border: 1px solid rgba(var(--primary-rgb), 0.2);">
                                            <i class="bi bi-calendar3"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-main-theme" style="font-size: 0.9rem;">
                                                <?= h($t['titre']) ?>
                                            </div>
                                            <div class="text-muted opacity-75" style="font-size: 0.75rem;">
                                                <?= __('class') ?> : <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-0.5 rounded-pill"><?= h($t['class_name']) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2.5 py-1 rounded-pill fw-medium" style="font-size: 0.75rem;">
                                        <?= h($t['teaching_type_name'] ?? 'LMD') ?>
                                    </span>
                                    <div class="small text-muted opacity-75 mt-1" style="font-size: 0.75rem;"><?= h($t['cycle_name']) ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold text-main-theme" style="font-size: 0.88rem;"><?= h($t['week_libelle']) ?></div>
                                    <div class="text-muted opacity-75 extra-small">
                                        <?= date('d/m/Y', strtotime($t['week_start'])) ?> au <?= date('d/m/Y', strtotime($t['week_end'])) ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if ($t['statut'] === 'publie'): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill fw-medium" style="font-size: 0.75rem;">
                                            <i class="bi bi-check-circle-fill me-1"></i><?= __('timetables_status_published') ?>
                                        </span>
                                    <?php elseif ($t['statut'] === 'verrouille' || $t['is_locked_calc']): ?>
                                        <span class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25 px-2.5 py-1 rounded-pill fw-medium" style="font-size: 0.75rem;">
                                            <i class="bi bi-lock-fill me-1"></i><?= __('timetables_status_locked') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2.5 py-1 rounded-pill fw-medium" style="font-size: 0.75rem;">
                                            <i class="bi bi-pencil-fill me-1"></i><?= __('timetables_status_draft') ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($t['is_locked_calc']): ?>
                                        <span class="badge-premium badge-premium-danger" style="font-size: 0.72rem;">
                                            <i class="bi bi-shield-lock-fill me-1"></i> <?= __('timetables_lock_closed') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-premium badge-premium-success" style="font-size: 0.72rem;">
                                            <i class="bi bi-unlock-fill me-1"></i> <?= __('timetables_lock_editable') ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <a href="/timetables/grid?id=<?= $t['id'] ?>" class="btn btn-sm btn-action-modern text-primary" title="<?= __('timetables_grid_btn') ?>">
                                            <i class="bi bi-grid-3x3-gap-fill fs-5"></i>
                                        </a>
                                        <a href="/timetables/pdf?id=<?= $t['id'] ?>&mode=print" target="_blank" class="btn btn-sm btn-action-modern text-secondary" title="<?= __('print') ?? 'Imprimer' ?>">
                                            <i class="bi bi-printer fs-5"></i>
                                        </a>
                                        <a href="/timetables/pdf?id=<?= $t['id'] ?>&mode=download" class="btn btn-sm btn-action-modern text-danger" title="PDF">
                                            <i class="bi bi-file-earmark-pdf-fill fs-5"></i>
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

<style>
    /* Floating Island Filters */
    .filter-island {
        background: rgba(var(--bg-card-rgb), 0.7);
        backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(var(--primary-rgb), 0.15);
        border-radius: 100px;
        min-width: 65%;
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .filter-island {
        background: rgba(30, 30, 45, 0.6);
        border-color: rgba(255, 255, 255, 0.08);
    }

    .filter-island:focus-within {
        border-color: var(--primary-color);
        box-shadow: 0 15px 35px -10px rgba(var(--primary-rgb), 0.25);
        transform: translateY(-2px);
    }

    /* Thème sombre pour le tableau */
    [data-theme="dark"] .modern-card {
        background: rgba(30, 30, 45, 0.6);
        border-color: rgba(255, 255, 255, 0.08);
    }

    [data-theme="dark"] .table-modern thead th {
        background: rgba(255, 255, 255, 0.05);
        color: #ffffff;
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }

    [data-theme="dark"] .table-modern tbody tr {
        border-bottom-color: rgba(255, 255, 255, 0.05);
    }

    [data-theme="dark"] .table-modern tbody td {
        color: #e0e0e0;
    }
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../templates/layout.php';
?>
