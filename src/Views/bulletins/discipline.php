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

    <!-- Flash Messages gérés par JS/SweetAlert -->
    <?php if ($classId <= 0): ?>
        <div class="mb-empty-state text-center p-5 text-muted-theme mt-4">
            <div class="mb-empty-icon p-4 rounded-circle d-inline-flex mb-3 shadow-sm">
                <i class="bi bi-door-open fs-1 text-primary opacity-75"></i>
            </div>
            <h4 class="fw-bold text-main-theme"><?= __('no_class_selected') ?></h4>
            <p class="mb-0 fs-5"><?= __('select_class_to_manage_discipline') ?></p>
        </div>
    <?php else: ?>
        <div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in">
            <div class="table-responsive">
                <form method="POST" action="/bulletins/discipline/save" id="disciplineForm" class="no-loader">
                    <input type="hidden" name="class_id" value="<?= (int) $classId ?>">
                    <input type="hidden" name="academic_year_id" value="<?= (int) $academicYearId ?>">
                    <input type="hidden" name="term" value="<?= (int) $term ?>">

                    <table class="table-modern align-middle">
                        <thead>
                            <tr>
                                <th class="ps-4" style="width: 50px;">#</th>
                                <th><?= __('student') ?></th>
                                <th class="text-center" style="width: 85px;"><?= __('total_absences') ?></th>
                                <th class="text-center" style="width: 85px;"><?= __('justified') ?></th>
                                <th class="text-center" style="width: 85px;"><?= __('unjustified') ?></th>
                                <th class="text-center" style="width: 85px;"><?= __('suspended') ?></th>
                                <th class="text-center" style="width: 85px;"><?= __('consignes') ?></th>
                                <th class="text-center" style="width: 140px;"><?= __('warn_conduct') ?></th>
                                <th class="text-center pe-4" style="width: 140px;"><?= __('blame_conduct') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $index => $student): ?>
                                <?php $studentId = (int) $student['id']; ?>
                                <tr class="student-row transition-base" data-student-id="<?= $studentId ?>">
                                    <td class="ps-4 text-muted-theme small opacity-50"><?= $index + 1 ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                                style="width: 38px; height: 38px; font-size: 1rem; border: 1px solid rgba(var(--primary-rgb), 0.2);">
                                                <?= strtoupper(substr((string) ($student['nom'] ?? 'S'), 0, 1)) ?>
                                            </div>
                                            <div class="text-nowrap overflow-hidden" style="max-width: 200px;">
                                                <div class="fw-bold text-main-theme name-gradient text-truncate" style="font-size: 0.9rem;">
                                                    <?= htmlspecialchars((string) ($student['nom'] ?? '')) ?>
                                                </div>
                                                <div class="text-muted-theme opacity-75 text-truncate" style="font-size: 0.75rem;">
                                                    <?= htmlspecialchars((string) ($student['prenom'] ?? '')) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" min="0" class="form-control form-control-sm js-discipline-input js-abs-total text-center fw-bold bg-transparent text-main-theme border-theme-dynamic rounded-pill"
                                            style="height: 36px;"
                                            name="absences_total[<?= $studentId ?>]"
                                            value="<?= sprintf('%02d', (int) ($disciplineMap[$studentId]['absences_total'] ?? 0)) ?>">
                                    </td>
                                    <td>
                                        <input type="number" min="0" class="form-control form-control-sm js-discipline-input js-abs-justified text-center fw-bold bg-transparent text-main-theme border-theme-dynamic rounded-pill"
                                            style="height: 36px;"
                                            name="absences_justified[<?= $studentId ?>]"
                                            value="<?= sprintf('%02d', (int) ($disciplineMap[$studentId]['absences_justified'] ?? 0)) ?>">
                                    </td>
                                    <td>
                                        <input type="number" min="0" class="form-control form-control-sm js-discipline-input js-abs-unjustified text-center fw-bold bg-secondary bg-opacity-10 text-main-theme border-theme-dynamic rounded-pill"
                                            style="height: 36px;"
                                            name="absences_unjustified[<?= $studentId ?>]" readonly
                                            value="<?= sprintf('%02d', (int) (max(0, ($disciplineMap[$studentId]['absences_total'] ?? 0) - ($disciplineMap[$studentId]['absences_justified'] ?? 0)))) ?>">
                                    </td>
                                    <td>
                                        <input type="number" min="0" class="form-control form-control-sm js-discipline-input text-center fw-bold bg-transparent text-main-theme border-theme-dynamic rounded-pill"
                                            style="height: 36px;"
                                            name="exclusion_days[<?= $studentId ?>]"
                                            value="<?= sprintf('%02d', (int) ($disciplineMap[$studentId]['exclusion_days'] ?? 0)) ?>">
                                    </td>
                                    <td>
                                        <input type="number" min="0" class="form-control form-control-sm js-discipline-input text-center fw-bold bg-transparent text-main-theme border-theme-dynamic rounded-pill"
                                            style="height: 36px;"
                                            name="consignes[<?= $studentId ?>]"
                                            value="<?= sprintf('%02d', (int) ($disciplineMap[$studentId]['consignes'] ?? 0)) ?>">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm js-discipline-input text-center bg-transparent text-main-theme border-theme-dynamic rounded-pill px-2" 
                                            style="height: 36px;"
                                            name="warning_conduct[<?= $studentId ?>]"
                                            <?php $wc = trim((string) ($disciplineMap[$studentId]['warning_conduct'] ?? '')); ?>
                                            value="<?= htmlspecialchars($wc !== '' ? $wc : '00') ?>"
                                            placeholder="00" maxlength="20">
                                    </td>
                                    <td class="pe-4">
                                        <input type="text" class="form-control form-control-sm js-discipline-input text-center bg-transparent text-main-theme border-theme-dynamic rounded-pill px-2" 
                                            style="height: 36px;"
                                            name="blame_conduct[<?= $studentId ?>]"
                                            <?php $bc = trim((string) ($disciplineMap[$studentId]['blame_conduct'] ?? '')); ?>
                                            value="<?= htmlspecialchars($bc !== '' ? $bc : '00') ?>"
                                            placeholder="00" maxlength="20">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="p-4 border-top border-theme-dynamic bg-light bg-opacity-10 d-flex justify-content-end align-items-center">
                        <div class="me-3 small text-muted-theme opacity-75">
                            <i class="bi bi-info-circle me-1"></i> <?= __('save_discipline_hint') ?>
                        </div>
                        <button type="submit"
                            class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-lg d-inline-flex align-items-center gap-2">
                            <i class="bi bi-check2-circle fs-5"></i>
                            <?= __('save') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    .name-gradient {
        background: linear-gradient(135deg, var(--text-main), var(--primary-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    [data-theme="dark"] .name-gradient {
        background: linear-gradient(135deg, #ffffff, var(--primary-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .table-modern tbody tr:hover {
        background: rgba(var(--primary-rgb), 0.03);
    }

    .form-control-sm.js-discipline-input {
        border-width: 1.5px;
        transition: all 0.2s;
        font-size: 0.8rem;
        height: 32px;
    }

    .form-control-sm.js-discipline-input:focus {
        border-color: var(--primary-color);
        background: rgba(var(--primary-rgb), 0.05) !important;
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.15);
    }

    /* Adaptabilité Thème Sombre */
    [data-theme="dark"] .modern-card {
        background: rgba(30, 30, 45, 0.6);
        border-color: rgba(255, 255, 255, 0.08) !important;
    }

    [data-theme="dark"] .table-modern thead th {
        background: rgba(255, 255, 255, 0.05);
        color: #ffffff;
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }

    [data-theme="dark"] .table-modern tbody tr {
        border-bottom-color: rgba(255, 255, 255, 0.05);
    }

    [data-theme="dark"] .table-modern tbody tr:hover {
        background: rgba(255, 255, 255, 0.05);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Affichage des messages flash avec SweetAlert
    <?php if ($flashSuccess): ?>
        AlertService.success(<?= json_encode((string)$flashSuccess, JSON_UNESCAPED_UNICODE) ?>);
    <?php endif; ?>
    <?php if ($flashError): ?>
        AlertService.error(<?= json_encode((string)$flashError, JSON_UNESCAPED_UNICODE) ?>);
    <?php endif; ?>

    const form = document.getElementById('disciplineForm');
    if (!form) return;

    // Gestion intelligente des valeurs par défaut (00) et calculs
    form.addEventListener('focusout', function(e) {
        if (e.target.classList.contains('js-discipline-input')) {
            let val = e.target.value.trim();
            
            // Si le champ est vide, remettre "00"
            if (val === '') {
                e.target.value = '00';
            } 
            // Si c'est un nombre, s'assurer qu'il a 2 chiffres (ex: 5 -> 05)
            else if (e.target.type === 'number') {
                let n = parseInt(val, 10);
                if (!isNaN(n)) {
                    e.target.value = n.toString().padStart(2, '0');
                }
            }

            // Recalcul des absences non justifiées si nécessaire
            if (e.target.classList.contains('js-abs-total') || e.target.classList.contains('js-abs-justified')) {
                const row = e.target.closest('tr');
                const totalInput = row.querySelector('.js-abs-total');
                const justifiedInput = row.querySelector('.js-abs-justified');
                const unjustifiedInput = row.querySelector('.js-abs-unjustified');
                
                if (totalInput && justifiedInput && unjustifiedInput) {
                    const total = parseInt(totalInput.value, 10) || 0;
                    const justified = parseInt(justifiedInput.value, 10) || 0;
                    unjustifiedInput.value = Math.max(0, total - justified).toString().padStart(2, '0');
                }
            }
        }
    });

    // Mise à jour en temps réel simple
    form.addEventListener('input', function(e) {
        if (e.target.classList.contains('js-abs-total') || e.target.classList.contains('js-abs-justified')) {
            const row = e.target.closest('tr');
            const totalInput = row.querySelector('.js-abs-total');
            const justifiedInput = row.querySelector('.js-abs-justified');
            const unjustifiedInput = row.querySelector('.js-abs-unjustified');
            
            if (totalInput && justifiedInput && unjustifiedInput) {
                const total = parseInt(totalInput.value, 10) || 0;
                const justified = parseInt(justifiedInput.value, 10) || 0;
                unjustifiedInput.value = Math.max(0, total - justified).toString().padStart(2, '0');
            }
        }
    });

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

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../templates/layout.php'; ?>
