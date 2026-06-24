<?php
$title = __('insolvent_title');
ob_start();
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-black text-main-theme mb-0 fs-4"><?= __('insolvent_header') ?></h2>
            <p class="text-muted-theme small mb-0"><?= __('insolvent_subtitle') ?></p>
        </div>
        <div class="d-flex gap-2">
            <a id="btn-print-pdf" href="#" target="_blank" class="btn btn-theme-soft rounded-pill px-3 fw-bold disabled" style="pointer-events: none; opacity: 0.6;">
                <i class="bi bi-printer me-1"></i> <?= __('print_pdf') ?>
            </a>
            <button id="btn-export-excel" class="btn btn-outline-success rounded-pill px-3 fw-bold">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> <?= __('btn_excel') ?>
            </button>
        </div>
    </div>

    <!-- Stats Island -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="modern-card border-0 shadow-sm p-4 d-flex align-items-center gap-3 position-relative overflow-hidden" style="background: rgba(220, 53, 69, 0.05); border: 1px solid rgba(220, 53, 69, 0.1) !important;">
                <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center shadow-sm" style="width: 54px; height: 54px; border: 1px solid rgba(220, 53, 69, 0.2);">
                    <i class="bi bi-people-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted-theme small text-uppercase fw-bold letter-spacing-1"><?= __('insolvent_students') ?></div>
                    <div class="fs-3 fw-black text-danger mt-1" id="stats-count"><?= count($insolventStudents) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="modern-card border-0 shadow-sm p-4 d-flex align-items-center gap-3 position-relative overflow-hidden" style="background: rgba(255, 193, 7, 0.05); border: 1px solid rgba(255, 193, 7, 0.1) !important;">
                <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center shadow-sm" style="width: 54px; height: 54px; border: 1px solid rgba(255, 193, 7, 0.2);">
                    <i class="bi bi-currency-exchange fs-3"></i>
                </div>
                <div>
                    <div class="text-muted-theme small text-uppercase fw-bold letter-spacing-1"><?= __('global_amount_due') ?></div>
                    <div class="fs-3 fw-black text-warning mt-1" id="stats-total-due">
                        <?php
                        $total = 0.0;
                        foreach ($insolventStudents as $row) {
                            $total += (float)($row['amount_due'] ?? $row['reste_a_payer'] ?? 0);
                        }
                        echo number_format($total, 0, '.', ' ') . ' FCFA';
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Island -->
    <div class="modern-card border-0 shadow-sm p-4 mb-4">
        <form id="filters-form" method="GET" class="row g-3 align-items-end" onsubmit="event.preventDefault();">
            <div class="col-md-2">
                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('teaching_type') ?></label>
                <select name="teaching_type_id" id="teaching-type-select" class="form-select premium-select">
                    <option value=""><?= __('all_m') ?></option>
                    <?php foreach ($teachingTypes as $tt): ?>
                        <option value="<?= $tt['id'] ?>" <?= $filters['teaching_type_id'] === (int)$tt['id'] ? 'selected' : '' ?>><?= h($tt['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('cycle') ?></label>
                <select name="cycle_id" id="cycle-select" class="form-select premium-select">
                    <option value=""><?= __('all_cycles') ?></option>
                    <?php foreach ($cycles as $cy): ?>
                        <option value="<?= $cy['id'] ?>" <?= $filters['cycle_id'] === (int)$cy['id'] ? 'selected' : '' ?>><?= h($cy['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('class_section') ?></label>
                <select name="section_id" id="section-select" class="form-select premium-select">
                    <option value=""><?= __('all_sections') ?></option>
                    <?php foreach ($sections as $sec): ?>
                        <option value="<?= $sec['id'] ?>" <?= $filters['section_id'] === (int)$sec['id'] ? 'selected' : '' ?>><?= h($sec['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('class') ?></label>
                <select name="class_id" id="class-select" class="form-select premium-select">
                    <option value=""><?= __('all_classes') ?></option>
                    <?php foreach ($classes as $cla): ?>
                        <option value="<?= $cla['id'] ?>" <?= $filters['class_id'] === (int)$cla['id'] ? 'selected' : '' ?>><?= h($cla['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('col_installment') ?></label>
                <select name="installment_number" id="tranche-select" class="form-select premium-select">
                    <option value=""><?= __('all_installments') ?></option>
                    <?php foreach ($tranches as $tr): ?>
                        <option value="<?= $tr['installment_order'] ?>" <?= $filters['installment_number'] === (int)$tr['installment_order'] ? 'selected' : '' ?>>
                            <?= h($tr['name']) ?> (<?= number_format($tr['amount'], 0, '.', ' ') ?> F)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <div class="w-100">
                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('recherche') ?></label>
                    <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2 border-theme-light" style="border: 1px solid var(--border-color); height: 38px;">
                        <span class="input-group-text border-0 bg-transparent text-primary py-1">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="search-input" name="q" class="form-control border-0 bg-transparent shadow-none py-1 small text-main" value="<?= h($filters['q']) ?>" placeholder="<?= __('search_placeholder_student') ?>" style="font-size: 0.85rem;">
                    </div>
                </div>
                <a href="/school_fees/insolvables" class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center reset-btn" style="width: 40px; height: 40px; margin-bottom: 2px;" title="<?= __('reset') ?>">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Insolvables Table Card -->
    <div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in">
        <div class="table-responsive">
            <table class="table-modern" id="insolvables-table">
                <thead>
                    <tr>
                        <th class="ps-4"><?= __('student') ?></th>
                        <th><?= __('class') ?></th>
                        <th><?= __('class_section') ?></th>
                        <th><?= __('teaching_type') ?></th>
                        <th class="text-end text-danger"><?= __('col_amount_due') ?></th>
                        <th class="text-center"><?= __('col_unpaid_tranches') ?></th>
                        <th><?= __('col_last_overdue') ?></th>
                        <th class="text-end pe-4"><?= __('col_remaining_total') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($insolventStudents)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-success">
                                <i class="bi bi-check-circle-fill fs-3 d-block mb-2 text-success"></i>
                                <?= __('congrats_no_insolvent') ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($insolventStudents as $row): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-init bg-danger bg-opacity-10 text-danger fw-bold rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                             style="width: 32px; height: 32px; font-size: 0.85rem; border: 1px solid rgba(220, 53, 69, 0.2);">
                                            <?= strtoupper(substr((string) $row['student_nom'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-main-theme" style="font-size: 0.85rem;">
                                                <?= h($row['student_nom']) ?>
                                            </div>
                                            <div class="text-muted opacity-75" style="font-size: 0.72rem;">
                                                <?= h($row['student_prenom']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-15 px-2 py-0.5 rounded-pill small">
                                        <?= h($row['class_name'] ?: '-') ?>
                                    </span>
                                </td>
                                <td><?= h($row['section_name'] ?: '-') ?></td>
                                <td><?= h($row['teaching_type_name'] ?: '-') ?></td>
                                <td class="text-end fw-black text-danger">
                                    <?= number_format($row['amount_due'], 0, '.', ' ') ?> <span class="extra-small">FCFA</span>
                                </td>
                                <td class="text-center fw-bold">
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-15 px-2.5 py-0.5 rounded-pill">
                                        <?= $row['unpaid_installments_count'] ?> <?= __('unpaid_tranches_suffix') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-premium badge-premium-danger">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i><?= $row['last_overdue_deadline'] ? date('d/m/Y', strtotime($row['last_overdue_deadline'])) : '-' ?>
                                    </span>
                                </td>
                                <td class="text-end fw-bold pe-4 text-muted">
                                    <?= number_format($row['total_reste_a_payer'], 0, '.', ' ') ?> FCFA
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const teachingTypeSelect = document.getElementById('teaching-type-select');
    const cycleSelect = document.getElementById('cycle-select');
    const sectionSelect = document.getElementById('section-select');
    const classSelect = document.getElementById('class-select');
    const trancheSelect = document.getElementById('tranche-select');
    const searchInput = document.getElementById('search-input');
    const btnPrintPdf = document.getElementById('btn-print-pdf');
    const statsCount = document.getElementById('stats-count');
    const statsTotalDue = document.getElementById('stats-total-due');
    const tableHeader = document.querySelector('#insolvables-table thead');
    const tableBody = document.querySelector('#insolvables-table tbody');

    // Helper dynamically toggle print button state
    function updatePrintButton() {
        const classId = classSelect.value;
        const trancheId = trancheSelect.value;
        if (classId && trancheId) {
            btnPrintPdf.classList.remove('disabled');
            btnPrintPdf.style.pointerEvents = 'auto';
            btnPrintPdf.style.opacity = '1';
            btnPrintPdf.setAttribute('href', `/school_fees/insolvables/print?class_id=${classId}&installment_number=${trancheId}`);
        } else {
            btnPrintPdf.classList.add('disabled');
            btnPrintPdf.style.pointerEvents = 'none';
            btnPrintPdf.style.opacity = '0.6';
            btnPrintPdf.setAttribute('href', '#');
        }
    }

    // Main fetch reload function
    function reloadTable() {
        const params = new URLSearchParams({
            ajax: 1,
            action: 'get_insolvables',
            teaching_type_id: teachingTypeSelect.value,
            cycle_id: cycleSelect.value,
            section_id: sectionSelect.value,
            class_id: classSelect.value,
            installment_number: trancheSelect.value,
            q: searchInput.value
        });

        tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-5 text-muted">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    <?= __('loading_data') ?>
                </td>
            </tr>`;

        fetch(`/school_fees/insolvables?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    tableHeader.innerHTML = data.thead;
                    tableBody.innerHTML = data.tbody;
                    statsCount.textContent = data.count;
                    statsTotalDue.textContent = data.total_remaining;
                    updatePrintButton();
                }
            })
            .catch(err => {
                console.error("Erreur de rechargement AJAX:", err);
            });
    }

    // Cascading listeners
    teachingTypeSelect.addEventListener('change', function() {
        const typeId = this.value;
        fetch(`/school_fees/insolvables?ajax=1&action=get_cycles&teaching_type_id=${typeId}`)
            .then(res => res.json())
            .then(cycles => {
                cycleSelect.innerHTML = `<option value=""><?= __('all_cycles') ?></option>`;
                cycles.forEach(cy => {
                    cycleSelect.innerHTML += `<option value="${cy.id}">${cy.nom}</option>`;
                });
                sectionSelect.innerHTML = `<option value=""><?= __('all_sections') ?></option>`;
                classSelect.innerHTML = `<option value=""><?= __('all_classes') ?></option>`;
                trancheSelect.innerHTML = `<option value=""><?= __('all_installments') ?></option>`;
                reloadTable();
            });
    });

    cycleSelect.addEventListener('change', function() {
        const typeId = teachingTypeSelect.value;
        const cycleId = this.value;
        fetch(`/school_fees/insolvables?ajax=1&action=get_sections&teaching_type_id=${typeId}&cycle_id=${cycleId}`)
            .then(res => res.json())
            .then(sections => {
                sectionSelect.innerHTML = `<option value=""><?= __('all_sections') ?></option>`;
                sections.forEach(sec => {
                    sectionSelect.innerHTML += `<option value="${sec.id}">${sec.nom}</option>`;
                });
                classSelect.innerHTML = `<option value=""><?= __('all_classes') ?></option>`;
                trancheSelect.innerHTML = `<option value=""><?= __('all_installments') ?></option>`;
                reloadTable();
            });
    });

    sectionSelect.addEventListener('change', function() {
        const typeId = teachingTypeSelect.value;
        const cycleId = cycleSelect.value;
        const sectionId = this.value;
        fetch(`/school_fees/insolvables?ajax=1&action=get_classes&teaching_type_id=${typeId}&cycle_id=${cycleId}&section_id=${sectionId}`)
            .then(res => res.json())
            .then(classes => {
                classSelect.innerHTML = `<option value=""><?= __('all_classes') ?></option>`;
                classes.forEach(cla => {
                    classSelect.innerHTML += `<option value="${cla.id}">${cla.nom}</option>`;
                });
                trancheSelect.innerHTML = `<option value=""><?= __('all_installments') ?></option>`;
                reloadTable();
            });
    });

    classSelect.addEventListener('change', function() {
        const classId = this.value;
        fetch(`/school_fees/insolvables?ajax=1&action=get_tranches&class_id=${classId}`)
            .then(res => res.json())
            .then(tranches => {
                trancheSelect.innerHTML = `<option value=""><?= __('all_installments') ?></option>`;
                tranches.forEach(tr => {
                    trancheSelect.innerHTML += `<option value="${tr.installment_order}">${tr.name} (${new Intl.NumberFormat().format(tr.amount)} F)</option>`;
                });
                reloadTable();
            });
    });

    trancheSelect.addEventListener('change', function() {
        reloadTable();
    });

    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(reloadTable, 300);
    });

    updatePrintButton();

    // Export Excel Client-side
    document.getElementById('btn-export-excel')?.addEventListener('click', function() {
        let csv = [];
        let rows = document.querySelectorAll("#insolvables-table tr");
        
        for (let i = 0; i < rows.length; i++) {
            let row = [], cols = rows[i].querySelectorAll("td, th");
            
            for (let j = 0; j < cols.length; j++) {
                let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, " ").replace(/(\s\s+)/gm, ' ').trim();
                data = data.replace(/"/g, '""');
                row.push('"' + data + '"');
            }
            csv.push(row.join(";"));
        }
        
        let csvContent = "data:text/csv;charset=utf-8,\uFEFF" + csv.join("\n");
        let encodedUri = encodeURI(csvContent);
        let link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "eleves_insolvables_" + new Date().toISOString().slice(0,10) + ".csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
});
</script>

<style>
@media print {
    .topbar, .sidebar, .topbar-glass, main.main-area header, .btn, form {
        display: none !important;
    }
    main.main-area {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    body {
        background: #ffffff !important;
        color: #000000 !important;
    }
    .modern-card {
        box-shadow: none !important;
        border: none !important;
        background: transparent !important;
    }
    .table-modern {
        border: 1px solid #dee2e6 !important;
    }
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>

