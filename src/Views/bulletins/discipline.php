<?php
$title = __('discipline_management');
ob_start();
?>

<div class="animate-fade-in admin-analytics module-bureau-flow">

    <!-- BARRE D'ACTIONS COMPLÈTE : Style Floating Island - RESPONSIVE -->
    <div class="d-flex justify-content-center mb-4 mb-md-5 px-2 px-md-0">
        <div class="filter-island px-2 px-md-3 py-2 shadow-lg animate-slide-down w-100" style="max-width: 900px;">
            <form method="GET" action="/bulletins/discipline"
                class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2 gap-md-3 filter-form w-100">

                <div class="d-flex flex-column flex-sm-row align-items-stretch gap-2 flex-grow-1">
                    <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2 px-md-3 py-1">
                        <span class="input-group-text border-0 bg-transparent text-primary small fw-bold text-uppercase me-1 me-md-2">
                            <?= __('year') ?>
                        </span>
                        <select name="academic_year_id" class="form-select border-0 bg-transparent shadow-none fw-bold text-main"
                            onchange="this.form.submit()">
                            <?php foreach ($academicYears as $year): ?>
                                <option value="<?= (int) $year['id'] ?>" <?= $academicYearId === (int) $year['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $year['nom']) ?>
                                    <?= (int) $year['is_active'] === 1 ? '(' . __('active') . ')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2 px-md-3 py-1">
                        <span class="input-group-text border-0 bg-transparent text-primary small fw-bold text-uppercase me-1 me-md-2">
                            <?= __('class') ?>
                        </span>
                        <select name="class_id" class="form-select border-0 bg-transparent shadow-none fw-bold text-main"
                            onchange="this.form.submit()">
                            <option value=""><?= __('choose_class') ?></option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= (int) $class['id'] ?>" <?= $classId === (int) $class['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $class['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2 px-md-3 py-1">
                        <span class="input-group-text border-0 bg-transparent text-primary small fw-bold text-uppercase me-1 me-md-2">
                            <?= __('period') ?>
                        </span>
                        <select name="term" class="form-select border-0 bg-transparent shadow-none fw-bold text-main"
                            onchange="this.form.submit()">
                            <option value="1" <?= $term === 1 ? 'selected' : '' ?>><?= __('trimesters') ?> 1</option>
                            <option value="2" <?= $term === 2 ? 'selected' : '' ?>><?= __('trimesters') ?> 2</option>
                            <option value="3" <?= $term === 3 ? 'selected' : '' ?>><?= __('trimesters') ?> 3</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2 align-items-center justify-content-center justify-content-md-start border-0 border-md-start border-opacity-10 border-secondary ps-0 ps-md-3 pt-2 pt-md-0">
                    <a href="/bulletins/discipline?academic_year_id=<?= (int) $academicYearId ?>&class_id=<?= (int) $classId ?>&term=<?= (int) $term ?>"
                        class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center reset-btn"
                        style="width: 40px; height: 40px; min-width: 40px;" title="<?= __('reset') ?>">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($flashSuccess)): ?>
        <div class="alert alert-success"><?= htmlspecialchars((string) $flashSuccess) ?></div>
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars((string) $flashError) ?></div>
    <?php endif; ?>

    <?php if ($classId <= 0): ?>
        <div class="mb-empty-state text-center p-5 text-muted-theme mt-4">
            <div class="mb-empty-icon p-4 rounded-circle d-inline-flex mb-3 shadow-sm">
                <i class="bi bi-door-open fs-1 text-primary opacity-75"></i>
            </div>
            <h4 class="fw-bold text-main-theme"><?= __('no_class_selected') ?></h4>
            <p class="mb-0 fs-5"><?= __('select_class_to_manage_discipline') ?></p>
        </div>
    <?php else: ?>
        <div class="modern-card border-0 shadow-sm">
            <div class="modern-card-body p-2 p-md-4 p-lg-5">
                <form method="POST" action="/bulletins/discipline/save" id="disciplineForm" class="no-loader">
                    <input type="hidden" name="class_id" value="<?= (int) $classId ?>">
                    <input type="hidden" name="academic_year_id" value="<?= (int) $academicYearId ?>">
                    <input type="hidden" name="term" value="<?= (int) $term ?>">

                    <div class="table-responsive">
                        <table class="table table-modern compact-table table-striped mb-0 align-middle bg-transparent">
                            <thead>
                                <tr>
                                    <th class="d-none d-sm-table-cell" style="width: 50px;">#</th>
                                    <th><?= __('name_and_surname') ?></th>
                                    <th style="min-width: 70px;"><?= __('total') ?></th>
                                    <th style="min-width: 70px;"><?= __('justified') ?></th>
                                    <th style="min-width: 80px;"><?= __('unjustified') ?></th>
                                    <th style="min-width: 90px;"><?= __('suspended') ?></th>
                                    <th style="min-width: 120px;"><?= __('warn_conduct') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $index => $student): ?>
                                    <?php $studentId = (int) $student['id']; ?>
                                    <tr class="bg-transparent">
                                        <td class="d-none d-sm-table-cell"><?= $index + 1 ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars((string) ($student['nom'] ?? '')) ?></strong>
                                            <span class="d-block d-md-inline"><?= htmlspecialchars((string) ($student['prenom'] ?? '')) ?></span>
                                        </td>
                                        <td>
                                            <input type="number" min="0" class="form-control form-control-sm js-discipline-input"
                                                name="absences_total[<?= $studentId ?>]"
                                                value="<?= (int) ($disciplineMap[$studentId]['absences_total'] ?? 0) ?>">
                                        </td>
                                        <td>
                                            <input type="number" min="0" class="form-control form-control-sm js-discipline-input"
                                                name="absences_justified[<?= $studentId ?>]"
                                                value="<?= (int) ($disciplineMap[$studentId]['absences_justified'] ?? 0) ?>">
                                        </td>
                                        <td>
                                            <input type="number" min="0" class="form-control form-control-sm js-discipline-input"
                                                name="absences_unjustified[<?= $studentId ?>]"
                                                value="<?= (int) ($disciplineMap[$studentId]['absences_unjustified'] ?? 0) ?>">
                                        </td>
                                        <td>
                                            <input type="number" min="0" class="form-control form-control-sm js-discipline-input"
                                                name="exclusion_days[<?= $studentId ?>]"
                                                value="<?= (int) ($disciplineMap[$studentId]['exclusion_days'] ?? 0) ?>">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm js-discipline-input" name="warning_conduct[<?= $studentId ?>]"
                                                value="<?= htmlspecialchars((string) ($disciplineMap[$studentId]['warning_conduct'] ?? '')) ?>"
                                                maxlength="20">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end border-top pt-3 pt-md-4 mt-3">
                        <button type="submit"
                            class="btn btn-primary rounded-pill px-4 px-md-5 py-2 py-md-3 fw-bold shadow-lg d-inline-flex align-items-center gap-2">
                            <i class="bi bi-check2-circle fs-5"></i>
                            <?= __('save') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('disciplineForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        if (form.dataset.confirmed === 'true') return;
        
        e.preventDefault();
        
        const inputs = form.querySelectorAll('.js-discipline-input');
        const filledCount = Array.from(inputs).filter(input => {
            return input.type === 'number' ? (parseInt(input.value) > 0) : (input.value.trim() !== '');
        }).length;

        const htmlContent = `
            <div style="font-size: 0.85rem; color: #000000;">
                <p class="mb-2 fw-medium"><?= json_encode(__('confirm_save_discipline'), JSON_UNESCAPED_UNICODE) ?></p>
                <div class="d-inline-block px-3 py-1 rounded-pill bg-warning-subtle text-warning-emphasis fw-bold small">
                    ${filledCount} <?= json_encode(__('entries_detected'), JSON_UNESCAPED_UNICODE) ?>
                </div>
            </div>
        `;

        AlertService.confirm({
            title: <?= json_encode(__('confirmation'), JSON_UNESCAPED_UNICODE) ?>,
            html: htmlContent,
            icon: 'question',
            confirmText: <?= json_encode(__('confirm'), JSON_UNESCAPED_UNICODE) ?>,
            cancelText: <?= json_encode(__('cancel'), JSON_UNESCAPED_UNICODE) ?>,
            width: '320px',
            background: '#ffffff',
            customClass: {
                popup: 'rounded-4 shadow-sm p-3 border border-light',
                title: 'text-black fw-bolder fs-5',
                confirmButton: 'btn btn-primary btn-sm w-100 mb-2 rounded-pill',
                cancelButton: 'btn btn-light btn-sm w-100 rounded-pill',
                actions: 'd-flex flex-column w-100 gap-1'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                form.dataset.confirmed = 'true';
                AlertService.loading(<?= json_encode(__('saving'), JSON_UNESCAPED_UNICODE) ?>);
                setTimeout(() => form.submit(), 50);
            }
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
