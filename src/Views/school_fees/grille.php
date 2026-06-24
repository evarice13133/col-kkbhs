<?php
$title = __('grille_title');
ob_start();
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h2 class="fw-black text-main-theme mb-0 fs-4"><?= __('grille_header') ?></h2>
            <p class="text-muted-theme small mb-0"><?= __('grille_subtitle') ?></p>
        </div>
        <div class="d-flex flex-column flex-md-row gap-2 ms-md-auto mt-3 mt-md-0 align-items-stretch align-items-md-center justify-content-md-end w-100 w-md-auto">
            <button type="button" class="btn btn-outline-primary rounded-pill px-3 fw-bold text-center text-nowrap" data-bs-toggle="modal" data-bs-target="#importGrilleModal">
                <i class="bi bi-file-earmark-arrow-up me-1"></i> <?= __('btn_import') ?>
            </button>
            <a href="/school_fees/grille/print<?= !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '' ?>" target="_blank" class="btn btn-theme-soft rounded-pill px-3 fw-bold text-center text-nowrap">
                <i class="bi bi-printer me-1"></i> <?= __('print_pdf') ?>
            </a>
            <button id="btn-export-excel" class="btn btn-outline-success rounded-pill px-3 fw-bold text-center text-nowrap">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> <?= __('btn_excel') ?>
            </button>
        </div>
    </div>

    <!-- Filter Island -->
    <div class="modern-card border-0 shadow-sm p-4 mb-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('teaching_type') ?></label>
                <select name="teaching_type_id" class="form-select premium-select" onchange="this.form.submit()">
                    <option value=""><?= __('all_m') ?></option>
                    <?php foreach ($teachingTypes as $tt): ?>
                        <option value="<?= $tt['id'] ?>" <?= $teachingTypeId === (int)$tt['id'] ? 'selected' : '' ?>><?= h($tt['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('cycle') ?></label>
                <select name="cycle_id" class="form-select premium-select" onchange="this.form.submit()">
                    <option value=""><?= __('all_m') ?></option>
                    <?php foreach ($cycles as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $cycleId === (int)$c['id'] ? 'selected' : '' ?>><?= h($c['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('class_section') ?></label>
                <select name="section_id" class="form-select premium-select" onchange="this.form.submit()">
                    <option value=""><?= __('all_f') ?></option>
                    <?php foreach ($sections as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= $sectionId === (int)$s['id'] ? 'selected' : '' ?>><?= h($s['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <div class="w-100">
                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('class') ?></label>
                    <select name="class_id" class="form-select premium-select" onchange="this.form.submit()">
                        <option value=""><?= __('all_f') ?></option>
                        <?php foreach ($allClasses as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $classId === (int)$c['id'] ? 'selected' : '' ?>><?= h($c['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($teachingTypeId || $cycleId || $sectionId || $classId): ?>
                    <a href="/school_fees/grille" class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center reset-btn" style="width: 40px; height: 40px; margin-bottom: 2px;" title="<?= __('reset') ?>">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="modern-card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table-modern" id="grille-table">
                <thead>
                    <tr>
                        <th class="ps-4"><?= __('col_class') ?></th>
                        <th class="text-end"><?= __('col_reg_new') ?></th>
                        <th class="text-end"><?= __('col_reg_old') ?></th>
                        <th class="text-end"><?= __('col_tuition_gross') ?></th>
                        <th class="text-center"><?= __('col_nbr_tranches') ?></th>
                        <th><?= __('col_deadlines_detail') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($grilleData)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-info-circle fs-4 d-block mb-2 text-secondary"></i>
                                <?= __('no_class_match_criteria') ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($grilleData as $row): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-main-theme">
                                    <i class="bi bi-door-open me-2 text-primary"></i><?= h($row['class_name']) ?>
                                </td>
                                <td class="text-end fw-bold">
                                    <?= number_format($row['frais_inscription_nouveau'], 0, '.', ' ') ?> <span class="extra-small text-muted">FCFA</span>
                                </td>
                                <td class="text-end fw-bold">
                                    <?= number_format($row['frais_inscription_ancien'], 0, '.', ' ') ?> <span class="extra-small text-muted">FCFA</span>
                                </td>
                                <td class="text-end fw-black text-primary">
                                    <?= number_format($row['frais_scolarite_brut'], 0, '.', ' ') ?> <span class="extra-small text-muted">FCFA</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-10 px-2 py-1 rounded-pill fw-bold">
                                        <?= $row['nbr_tranches'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (empty($row['tranches'])): ?>
                                        <span class="text-muted-theme opacity-50 small"><?= __('no_tranche_defined') ?></span>
                                    <?php else: ?>
                                        <div class="d-flex flex-wrap gap-2 py-1">
                                            <?php foreach ($row['tranches'] as $tr): ?>
                                                <div class="badge-premium badge-premium-info" style="font-size: 0.72rem; padding: 0.35rem 0.6rem;">
                                                    <span class="fw-bold me-1 text-dark"><?= h($tr['name']) ?>:</span>
                                                    <span class="fw-black text-primary"><?= number_format($tr['amount'], 0, '.', ' ') ?> FCFA</span>
                                                    <span class="ms-1 border-start border-secondary border-opacity-25 ps-1 text-muted" style="font-size: 0.65rem;">
                                                        <i class="bi bi-calendar-event me-1"></i><?= date('d/m/Y', strtotime($tr['deadline_date'])) ?>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
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
    // Export Excel Client-side
    document.getElementById('btn-export-excel')?.addEventListener('click', function() {
        let csv = [];
        let rows = document.querySelectorAll("#grille-table tr");
        
        for (let i = 0; i < rows.length; i++) {
            let row = [], cols = rows[i].querySelectorAll("td, th");
            
            for (let j = 0; j < cols.length; j++) {
                // Clean content
                let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, " ").replace(/(\s\s+)/gm, ' ').trim();
                // Escape double quotes
                data = data.replace(/"/g, '""');
                row.push('"' + data + '"');
            }
            csv.push(row.join(";"));
        }
        
        let csvContent = "data:text/csv;charset=utf-8,\uFEFF" + csv.join("\n");
        let encodedUri = encodeURI(csvContent);
        let link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "grille_de_scolarite_" + new Date().toISOString().slice(0,10) + ".csv");
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

<!-- Modal d'Importation -->
<div class="modal fade" id="importGrilleModal" tabindex="-1" aria-labelledby="importGrilleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="importGrilleModalLabel">
                    <i class="bi bi-file-earmark-arrow-up me-2"></i> <?= __('import_grille_title') ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/school_fees/grille/import<?= !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES, 'UTF-8') : '' ?>" method="POST" enctype="multipart/form-data" class="no-loader" id="importGrilleForm">
                <div class="modal-body p-4">
                    <div class="alert alert-warning border-0 small mb-3 text-start">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-2 fs-6"></i>
                        <?= __('import_warning_text') ?>
                    </div>
                    
                    <?php if ($teachingTypeId || $cycleId || $sectionId || $classId): ?>
                        <div class="alert alert-info border-0 small mb-3 text-start">
                            <i class="bi bi-funnel-fill text-info me-2 fs-6"></i>
                            <?= __('active_filter_text') ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-4 text-center">
                        <p class="text-muted small mb-2"><?= __('import_instructions_template') ?></p>
                        <a href="/school_fees/grille/template<?= !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES, 'UTF-8') : '' ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="bi bi-download me-1"></i> <?= __('download_template_btn') ?>
                        </a>
                    </div>
                    
                    <div class="mb-3 text-start">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2"><?= __('select_excel_csv_file') ?></label>
                        <!-- Stylized Drag & Drop Area -->
                        <div class="drag-drop-zone border-2 border-dashed border-primary border-opacity-25 rounded-4 p-4 text-center cursor-pointer position-relative bg-light bg-opacity-50 hover-bg-opacity-100" id="dropzone" style="border-style: dashed !important; border-width: 2px !important;">
                            <input type="file" name="import_file" id="import_file_input" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" accept=".xlsx, .xls, .csv" required style="z-index: 10;">
                            <div class="py-2" style="pointer-events: none;">
                                <i class="bi bi-cloud-arrow-up text-primary fs-2 mb-2 d-block"></i>
                                <span class="fw-bold d-block text-main-theme small" id="filename-preview"><?= __('drag_drop_preview') ?></span>
                                <span class="text-muted extra-small d-block mt-1"><?= __('accepted_files_hint') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light py-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal"><?= __('btn_cancel') ?></button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" id="submitImportBtn">
                        <i class="bi bi-check-circle-fill me-1"></i> <?= __('validate_import_btn') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('import_file_input');
    const dropzone = document.getElementById('dropzone');
    const filenamePreview = document.getElementById('filename-preview');

    if (fileInput && dropzone && filenamePreview) {
        fileInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                filenamePreview.textContent = file.name;
                filenamePreview.classList.add('text-success', 'fw-black');
                dropzone.classList.add('border-success', 'bg-success', 'bg-opacity-5');
            } else {
                filenamePreview.textContent = 'Glissez-déposez le fichier ici ou cliquez pour parcourir';
                filenamePreview.classList.remove('text-success', 'fw-black');
                dropzone.classList.remove('border-success', 'bg-success', 'bg-opacity-5');
            }
        });

        // drag & drop styling behavior
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => {
                dropzone.classList.add('border-primary', 'bg-primary', 'bg-opacity-5');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => {
                dropzone.classList.remove('border-primary', 'bg-primary', 'bg-opacity-5');
            }, false);
        });
    }

    // Modal submit spinner
    const form = document.getElementById('importGrilleForm');
    const submitBtn = document.getElementById('submitImportBtn');
    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span><?= __('import_in_progress') ?>';
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
