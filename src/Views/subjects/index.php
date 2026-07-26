<?php $title = __('subjects');
ob_start(); ?>

<div class="animate-fade-in container-fluid py-3 px-2 px-md-4">

    <!-- Boutons d'Action Principaux (Au-dessus du filtre) -->
    <?php if (\App\Core\PermissionManager::hasPermission('manage_subjects')): ?>
    <div class="d-flex justify-content-center mb-3">
        <div class="d-flex gap-2">
            <a href="/subjects/create" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm text-nowrap">
                <i class="bi bi-plus-circle me-1"></i> <?= __('add_subject') ?>
            </a>
            <button type="button" class="btn btn-outline-success rounded-pill px-4 fw-bold shadow-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#importSubjectsModal">
                <i class="bi bi-upload me-1"></i> <?= __('import') ?>
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- BARRE DE FILTRES : Style Floating Island -->
    <div class="d-flex justify-content-center mb-4 mb-md-5">
        <div class="filter-island px-2 px-md-3 py-2 shadow-lg animate-slide-down w-100" style="max-width: 95%;">
            <form method="GET" class="d-flex align-items-center gap-2 flex-wrap flex-md-nowrap filter-form w-100">

                <!-- Barre de Recherche -->
                <div class="flex-grow-1 d-flex gap-2 flex-wrap flex-md-nowrap w-100">
                    <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2 flex-grow-1">
                        <span class="input-group-text border-0 bg-transparent text-primary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-0 bg-transparent shadow-none py-2 text-main"
                            value="<?= htmlspecialchars((string) $filters['q']) ?>"
                            placeholder="<?= __('subject_name') ?>...">
                    </div>
                    <!-- Type Enseignement -->
                    <select name="teaching_type_id" id="filter_teaching_type" class="form-select border-0 bg-white bg-opacity-10 shadow-none py-2 text-main rounded-pill px-3 flex-shrink-0" style="max-width: 150px; min-width: 120px;">
                        <option value="">Tous les Types</option>
                        <?php foreach ($teachingTypes as $tt): ?>
                            <option value="<?= $tt['id'] ?>" <?= (int) ($filters['teaching_type_id'] ?? 0) === (int) $tt['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $tt['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Département -->
                    <select name="department_id" id="filter_department" class="form-select border-0 bg-white bg-opacity-10 shadow-none py-2 text-main rounded-pill px-3 flex-shrink-0" style="max-width: 160px; min-width: 130px;">
                        <option value=""><?= __('all_departments') ?? 'Tous les départements' ?></option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept['id'] ?>" data-teaching-type="<?= $dept['teaching_type_id'] ?? '' ?>" <?= (int) ($filters['department_id'] ?? 0) === (int) $dept['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $dept['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Classe -->
                    <select name="class_id" id="filter_class" class="form-select border-0 bg-white bg-opacity-10 shadow-none py-2 text-main rounded-pill px-3 flex-shrink-0" style="max-width: 150px; min-width: 120px;">
                        <option value=""><?= __('all_classes') ?></option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= $class['id'] ?>" data-teaching-type="<?= $class['teaching_type_id'] ?? '' ?>" <?= (int) $filters['class_id'] === (int) $class['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $class['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtres et Utilitaires -->
                <div class="d-flex gap-2 align-items-center ps-md-2 flex-shrink-0">
                    <button type="submit" class="btn btn-primary rounded-pill px-3 px-md-4 fw-bold shadow-sm text-nowrap">
                        <i class="bi bi-funnel-fill d-inline d-md-none"></i>
                        <span class="d-none d-md-inline"><?= __('filter') ?></span>
                    </button>
                    <a href="/subjects" class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;" title="<?= __('reset') ?>">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                    <a href="/subjects/export?<?= http_build_query($filters) ?>"
                        class="btn-export-minimal shadow-sm" style="width: 38px; height: 38px;" title="<?= __('export_list') ?>">
                        <i class="bi bi-file-earmark-pdf"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- LISTE ET PAGINATION DES MATIÈRES -->
    <div id="subjectsListContainer">
        <div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in">
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th class="ps-4"><?= __('subject') ?></th>
                            <th><?= __('classes') ?></th>
                            <th><?= __('coefficient') ?></th>
                            <?php if (\App\Core\PermissionManager::hasRole('superadmin')): ?>
                            <th><?= __('status') ?></th>
                            <?php endif; ?>
                            <th><?= __('group') ?></th>
                            <?php if (\App\Core\PermissionManager::hasPermission('manage_subjects')): ?>
                            <th class="text-end pe-4"><?= __('actions') ?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($subjects)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="bi bi-book fs-1 opacity-25 mb-3 d-block"></i>
                                    <span class="opacity-50"><?= __('no_data') ?></span>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($subjects as $s): 
                                $isActive = (int)($s['status'] ?? 1) === 1;
                            ?>
                                <tr class="<?= !$isActive ? 'opacity-50 grayscale bg-light' : '' ?>">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-init bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                                style="width: 36px; height: 36px; border: 1px solid rgba(var(--primary-rgb), 0.2);">
                                                <i class="bi bi-book text-primary small"></i>
                                            </div>
                                            <div class="fw-bold text-main-theme">
                                                <?= htmlspecialchars((string) $s['nom']) ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted small"><?= htmlspecialchars((string) ($s['classes_list'] ?: __('no_class_associated'))) ?></span>
                                        <?php if (!empty($s['subject_group_libelle']) || !empty($s['groupe'])): ?>
                                            <div class="mt-1">
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary fw-bold px-2 py-1 rounded-pill" style="font-size: 0.68rem;">
                                                    <i class="bi bi-collection me-1"></i><?= htmlspecialchars((string) ($s['subject_group_libelle'] ?? $s['groupe'])) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-1 rounded-3">
                                            <?= __('coef') ?>: <?= (int) $s['coefficient'] ?>
                                        </span>
                                        <?php if (!empty($s['teaching_type_nom'])): ?>
                                            <div class="mt-1">
                                                <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1 rounded-pill" style="font-size: 0.65rem;">
                                                    <i class="bi bi-diagram-3-fill me-1"></i><?= htmlspecialchars((string) $s['teaching_type_nom']) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <?php if (\App\Core\PermissionManager::hasRole('superadmin')): ?>
                                    <td>
                                        <?php if ($isActive): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                                <i class="bi bi-check-circle-fill me-1"></i> <?= __('active') ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                                <i class="bi bi-x-circle-fill me-1"></i> <?= __('inactive') ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <?php endif; ?>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-1 rounded-3">
                                            <?= htmlspecialchars($s['groupe'] ?? 'Groupe 1') ?>
                                        </span>
                                    </td>
                                    <?php if (\App\Core\PermissionManager::hasPermission('manage_subjects')): ?>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-1">
                                            <?php if (\App\Core\PermissionManager::hasRole('superadmin')): ?>
                                            <a href="/subjects/toggleStatus?id=<?= $s['id'] ?>"
                                               class="btn btn-sm btn-action-modern btn-confirm-toggle <?= $isActive ? 'text-warning' : 'text-success' ?>"
                                               data-confirm="<?= $isActive ? __('deactivate_subject_confirm', ['name' => $s['nom']]) : __('activate_subject_confirm', ['name' => $s['nom']]) ?>"
                                               title="<?= $isActive ? __('deactivate') : __('activate') ?>">
                                                <i class="bi bi-power fs-5"></i>
                                            </a>
                                            <?php endif; ?>
                                            <a href="/subjects/edit?id=<?= $s['id'] ?>"
                                                class="btn btn-sm btn-action-modern text-primary" title="<?= __('edit') ?>">
                                                <i class="bi bi-pencil-square fs-5"></i>
                                            </a>
                                            <a href="/subjects/delete?id=<?= $s['id'] ?>"
                                                class="btn btn-sm btn-action-modern text-danger btn-confirm-delete"
                                                data-confirm="<?= __('delete_subject_confirm') ?>"
                                                title="<?= __('delete') ?>">
                                                <i class="bi bi-trash fs-5"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PAGINATION -->
        <?php if ($totalPages > 1): ?>
            <div class="d-flex justify-content-between align-items-center mt-5 mb-4 flex-wrap gap-3">
                <div class="text-muted small">
                    <?= __('showing_count', [
                        'start' => $offset + 1,
                        'end' => min($offset + $limit, $totalCount),
                        'total' => $totalCount
                    ]) ?>
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-modern mb-0">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $page - 1])) ?>" aria-label="Previous">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        if ($start > 1): ?>
                            <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => 1])) ?>">1</a></li>
                            <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $start; $i <= $end; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($end < $totalPages): ?>
                            <?php if ($end < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                            <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $totalPages])) ?>"><?= $totalPages ?></a></li>
                        <?php endif; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $page + 1])) ?>" aria-label="Next">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>

    <!-- MODALE IMPORT EXCEL MATIÈRES -->
    <div class="modal fade" id="importSubjectsModal" tabindex="-1" aria-labelledby="importSubjectsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                <div class="modal-header border-bottom border-theme-light p-4 bg-success bg-opacity-10">
                    <h5 class="modal-title fw-black text-main-theme" id="importSubjectsModalLabel">
                        <i class="bi bi-file-earmark-spreadsheet-fill me-2 text-success"></i><?= __('import_subjects') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <?php include __DIR__ . '/_import_form.php'; ?>
                </div>
            </div>
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
        min-width: 70%;
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

    .btn-export-minimal {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: var(--bg-card);
        color: #f1c40f;
        border: 1px solid rgba(241, 196, 15, 0.2);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        text-decoration: none !important;
    }

    .btn-export-minimal:hover {
        background: #f1c40f;
        color: white !important;
        transform: scale(1.1) rotate(8deg);
        box-shadow: 0 8px 20px rgba(241, 196, 15, 0.3);
    }

    /* Animations */
    .animate-slide-down {
        animation: slideDown 0.6s cubic-bezier(0.23, 1, 0.32, 1);
    }

    @keyframes slideDown {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    /* Pagination Modern Style */
    .pagination-modern {
        gap: 8px;
    }

    .pagination-modern .page-item .page-link {
        border: none;
        border-radius: 12px;
        color: var(--text-main);
        background: var(--bg-card);
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        transition: all 0.2s;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
    }

    .pagination-modern .page-item.active .page-link {
        background: var(--primary-color);
        color: white;
        box-shadow: 0 10px 15px -3px rgba(var(--primary-rgb), 0.3);
    }

    .pagination-modern .page-item .page-link:hover:not(.active) {
        background: color-mix(in srgb, var(--primary-color) 10%, transparent);
        color: var(--primary-color);
        transform: translateY(-2px);
    }

    /* Thème sombre pour le tableau des matières */
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

    [data-theme="dark"] .table-modern tbody tr:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    [data-theme="dark"] .table-modern tbody td {
        color: #e0e0e0;
    }

    [data-theme="dark"] .table-modern tbody td .fw-bold {
        color: #ffffff;
    }

    [data-theme="dark"] .table-modern tbody td .text-muted {
        color: #a0a0a0;
    }

    @media (max-width: 767.98px) {
        .filter-island {
            border-radius: 24px;
            min-width: 100%;
            padding: 1rem !important;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterTT = document.getElementById('filter_teaching_type');
    const filterDept = document.getElementById('filter_department');
    const filterClass = document.getElementById('filter_class');

    if (!filterTT || !filterClass) return;

    const originalDepts = filterDept ? Array.from(filterDept.options).filter(opt => opt.value !== '') : [];
    const originalClasses = Array.from(filterClass.options).filter(opt => opt.value !== '');

    function updateDependentFilters() {
        const selectedTT = filterTT.value;

        // 1. Filtrer les Départements
        if (filterDept) {
            const currentDeptId = filterDept.value;
            filterDept.innerHTML = '<option value=""><?= addslashes(__('all_departments') ?? 'Tous les départements') ?></option>';
            let deptValid = false;

            originalDepts.forEach(opt => {
                const optTT = opt.getAttribute('data-teaching-type');
                if (!selectedTT || !optTT || optTT === selectedTT) {
                    const cloned = opt.cloneNode(true);
                    if (cloned.value === currentDeptId) {
                        cloned.selected = true;
                        deptValid = true;
                    }
                    filterDept.appendChild(cloned);
                }
            });

            if (currentDeptId && !deptValid) {
                filterDept.value = '';
            }
        }

        // 2. Filtrer les Classes
        const currentClassId = filterClass.value;
        filterClass.innerHTML = '<option value=""><?= addslashes(__('all_classes')) ?></option>';
        let classValid = false;

        originalClasses.forEach(opt => {
            const optTT = opt.getAttribute('data-teaching-type');
            if (!selectedTT || !optTT || optTT === selectedTT) {
                const cloned = opt.cloneNode(true);
                if (cloned.value === currentClassId) {
                    cloned.selected = true;
                    classValid = true;
                }
                filterClass.appendChild(cloned);
            }
        });

        if (currentClassId && !classValid) {
            filterClass.value = '';
        }
    }

    filterTT.addEventListener('change', updateDependentFilters);
    updateDependentFilters();

    // Gestion de l'importation Excel Matières via AJAX
    const importModalEl = document.getElementById('importSubjectsModal');
    const importForm = document.getElementById('subjectImportForm');
    const importFileInput = document.getElementById('subject-import-file');
    const importSubmitBtn = document.getElementById('subject-import-submit');

    if (importFileInput && importSubmitBtn) {
        importFileInput.addEventListener('change', function() {
            importSubmitBtn.disabled = importFileInput.files.length === 0;
        });
    }

    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            e.preventDefault();

            if (!importFileInput || importFileInput.files.length === 0) {
                return;
            }

            const formData = new FormData(importForm);
            if (importSubmitBtn) importSubmitBtn.disabled = true;

            fetch('/subjects/upload', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async response => {
                const data = await response.json();
                return { ok: response.ok, data: data };
            })
            .then(({ ok, data }) => {
                if (importSubmitBtn) importSubmitBtn.disabled = false;

                if (ok && data.success) {
                    if (importModalEl) {
                        const bsModal = bootstrap.Modal.getInstance(importModalEl) || new bootstrap.Modal(importModalEl);
                        bsModal.hide();
                    }
                    importForm.reset();
                    if (importSubmitBtn) importSubmitBtn.disabled = true;

                    if (typeof AlertService !== 'undefined') {
                        AlertService.toast('success', data.message);
                    }

                    refreshSubjectsList();
                } else {
                    const errorMsg = data.message || "<?= addslashes((string) __('error_occurred')) ?>";
                    if (typeof AlertService !== 'undefined') {
                        AlertService.error("<?= addslashes((string) __('error_title')) ?>", errorMsg);
                    } else {
                        alert(errorMsg);
                    }
                }
            })
            .catch(err => {
                if (importSubmitBtn) importSubmitBtn.disabled = false;
                console.error('Import submit error:', err);
                if (typeof AlertService !== 'undefined') {
                    AlertService.toast('error', "<?= addslashes((string) __('communication_error')) ?>");
                }
            });
        });
    }

    function refreshSubjectsList() {
        fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.getElementById('subjectsListContainer');
            const currentContent = document.getElementById('subjectsListContainer');
            if (newContent && currentContent) {
                currentContent.innerHTML = newContent.innerHTML;
            }
        })
        .catch(err => console.error('Error refreshing subjects list:', err));
    }
});
</script>

<?php $content = ob_get_clean(); ?>

<?php include __DIR__ . '/../templates/layout.php'; ?>