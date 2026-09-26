<?php $title = __('unregistered_students');
ob_start(); ?>

<div class="animate-fade-in container-fluid py-3 px-md-4">

    <!-- Filter Island -->
    <div class="modern-card border-0 shadow-sm p-4 mb-4">
        <form id="filters-form" method="GET" action="/students/non-inscrits">
            <!-- Main Filter Controls Row -->
            <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center justify-content-between gap-3">
                
                <!-- Search Box -->
                <div class="flex-grow-1">
                    <div class="search-box-container position-relative">
                        <span class="position-absolute start-0 top-50 translate-middle-y ps-3 text-muted">
                            <i class="bi bi-search text-primary"></i>
                        </span>
                        <input type="text" name="q" id="search-input" class="form-control premium-input ps-5" value="<?= htmlspecialchars((string) ($filters['q'] ?? '')) ?>" placeholder="<?= __('search_student_placeholder') ?>..." style="height: 42px;">
                        <span id="search-clear" class="position-absolute end-0 top-50 translate-middle-y pe-3 text-muted cursor-pointer" style="display: <?= !empty($filters['q']) ? 'block' : 'none' ?>;">
                            <i class="bi bi-x-circle-fill"></i>
                        </span>
                    </div>
                </div>

                <!-- Advanced & Action Buttons -->
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <?php
                    $advActiveCount = 0;
                    if ((int)($filters['teaching_type_id'] ?? 0) > 0) $advActiveCount++;
                    if ((int)($filters['class_id'] ?? 0) > 0) $advActiveCount++;
                    if (!empty($filters['status']) && $filters['status'] !== 'Non inscrit') $advActiveCount++;
                    ?>
                    
                    <!-- Advanced Filters Toggle Button -->
                    <button type="button" class="btn btn-theme-soft rounded-pill px-3 fw-bold d-flex align-items-center gap-2" 
                            style="height: 42px;"
                            data-bs-toggle="collapse" data-bs-target="#advanced-filters-collapse" aria-expanded="<?= $advActiveCount > 0 ? 'true' : 'false' ?>">
                        <i class="bi bi-sliders"></i>
                        <span><?= __('advanced_filters') ?? 'Filtres Avancés' ?></span>
                        <span class="badge bg-primary text-white rounded-pill px-2" id="adv-filter-count" style="font-size: 0.75rem; <?= $advActiveCount === 0 ? 'display: none;' : '' ?>"><?= $advActiveCount ?></span>
                    </button>

                    <!-- Reset Form Button -->
                    <a href="/students/non-inscrits" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center reset-btn" style="width: 42px; height: 42px; min-width: 42px;" title="<?= __('clear_filters') ?? 'Réinitialiser' ?>">
                        <i class="bi bi-arrow-counterclockwise fs-5"></i>
                    </a>
                </div>
            </div>

            <!-- Collapsible Advanced Filters Section -->
            <div class="collapse <?= $advActiveCount > 0 ? 'show' : '' ?>" id="advanced-filters-collapse">
                <div class="advanced-filters-panel p-4 mt-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('class') ?? 'Classe' ?></label>
                            <select name="class_id" id="class-select" class="form-select premium-select">
                                <option value=""><?= __('all_classes') ?? 'Toutes les Classes' ?></option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= ((int)($filters['class_id'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('teaching_type') ?? 'Type d\'Enseignement' ?></label>
                            <select name="teaching_type_id" id="teaching-type-select" class="form-select premium-select">
                                <option value=""><?= 'Tous les Types' ?></option>
                                <?php foreach ($teachingTypes as $tt): ?>
                                    <option value="<?= $tt['id'] ?>" <?= ((int)($filters['teaching_type_id'] ?? 0) === $tt['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tt['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('status') ?? 'Statut' ?></label>
                            <select name="status" id="status-select" class="form-select premium-select">
                                <option value="Non inscrit" <?= (($filters['status'] ?? 'Non inscrit') === 'Non inscrit') ? 'selected' : '' ?>><?= __('status_non_inscrit') ?? 'Non inscrit' ?></option>
                                <option value="Démissionnaire" <?= (($filters['status'] ?? '') === 'Démissionnaire') ? 'selected' : '' ?>><?= __('status_demissionnaire') ?? 'Démissionnaire' ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Active Filters Badges Container -->
    <div id="active-filters-container">
        <?php include __DIR__ . '/badges_non_inscrits.php'; ?>
    </div>

    <!-- Table Card -->
    <div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in">
        
        <!-- Bulk Actions Bar (Hidden by default) -->
        <div id="bulk-actions-bar" class="bg-light p-3 border-bottom d-none align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill px-3 py-2" id="selected-count">0 sélectionné(s)</span>
            </div>
            <div>
                <button type="button" class="btn btn-success btn-sm fw-bold px-3 d-flex align-items-center gap-2" id="btn-bulk-validate">
                    <i class="bi bi-check-circle"></i>
                    Valider l'inscription
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>Matricule</th>
                        <th><?= __('student') ?></th>
                        <th>Sexe</th>
                        <th><?= __('class') ?></th>
                        <th><?= __('section') ?></th>
                        <th>Cycle</th>
                        <th>Type Ensg.</th>
                        <th>Importé le</th>
                        <th>Statut</th>
                        <?php if (\App\Core\PermissionManager::hasPermission('manage_students')): ?>
                        <th class="text-end"><?= __('actions') ?></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php include __DIR__ . '/tbody_non_inscrits.php'; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Container -->
    <div id="pagination-container">
        <?php include __DIR__ . '/pagination.php'; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filtersForm = document.getElementById('filters-form');
    const searchInput = document.getElementById('search-input');
    const searchClear = document.getElementById('search-clear');
    const classSelect = document.getElementById('class-select');
    const teachingTypeSelect = document.getElementById('teaching-type-select');
    const statusSelect = document.getElementById('status-select');
    
    const tableContainer = document.querySelector('.modern-card.overflow-hidden');
    
    let currentPage = <?= $page ?>;

    function updateAdvancedBadge() {
        let count = 0;
        if (classSelect && classSelect.value !== '') count++;
        if (teachingTypeSelect && teachingTypeSelect.value !== '') count++;
        if (statusSelect && statusSelect.value !== 'Non inscrit') count++;
        
        const badge = document.getElementById('adv-filter-count');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline-block' : 'none';
        }
    }

    function handleFilterChange(resetPage = true) {
        if (resetPage) {
            currentPage = 1;
        }

        updateAdvancedBadge();
        
        if (tableContainer) {
            tableContainer.classList.add('table-loading-active');
        }

        const params = new URLSearchParams({
            q: searchInput.value,
            class_id: classSelect ? classSelect.value : '',
            teaching_type_id: teachingTypeSelect ? teachingTypeSelect.value : '',
            status: statusSelect ? statusSelect.value : 'Non inscrit',
            page: currentPage,
            ajax: 1
        });

        const newUrl = `${window.location.pathname}?${params.toString().replace('&ajax=1', '')}`;
        window.history.pushState({ path: newUrl }, '', newUrl);

        fetch(`/students/non-inscrits?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const tbody = document.querySelector('.table-modern tbody');
                    if (tbody) {
                        tbody.innerHTML = data.tbody;
                    }

                    const badgesContainer = document.getElementById('active-filters-container');
                    if (badgesContainer) {
                        badgesContainer.innerHTML = data.badges;
                    }

                    const paginationContainer = document.getElementById('pagination-container');
                    if (paginationContainer) {
                        paginationContainer.innerHTML = data.pagination;
                    }
                }
                
                if (tableContainer) {
                    tableContainer.classList.remove('table-loading-active');
                }
            })
            .catch(err => {
                console.error("Erreur de filtrage AJAX :", err);
                if (tableContainer) {
                    tableContainer.classList.remove('table-loading-active');
                }
            });
    }

    // Input listeners
    let searchDebounceTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            if (this.value) {
                searchClear.style.display = 'block';
            } else {
                searchClear.style.display = 'none';
            }
            
            clearTimeout(searchDebounceTimeout);
            searchDebounceTimeout = setTimeout(() => {
                handleFilterChange(true);
            }, 300);
        });

        searchClear.addEventListener('click', function() {
            searchInput.value = '';
            searchClear.style.display = 'none';
            handleFilterChange(true);
            searchInput.focus();
        });
    }

    if (classSelect) classSelect.addEventListener('change', () => handleFilterChange(true));
    if (teachingTypeSelect) teachingTypeSelect.addEventListener('change', () => handleFilterChange(true));
    if (statusSelect) statusSelect.addEventListener('change', () => handleFilterChange(true));

    filtersForm.addEventListener('submit', function(e) {
        e.preventDefault();
        handleFilterChange(true);
    });

    // Reset individual filters from badges
    window.resetFilter = function(type) {
        if (type === 'q') {
            if (searchInput) {
                searchInput.value = '';
                searchClear.style.display = 'none';
            }
        } else if (type === 'class') {
            if (classSelect) classSelect.value = '';
        } else if (type === 'teaching_type') {
            if (teachingTypeSelect) teachingTypeSelect.value = '';
        } else if (type === 'status') {
            if (statusSelect) statusSelect.value = 'Non inscrit';
        }
        handleFilterChange(true);
    };

    // Intercept pagination clicks
    document.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination-modern .page-link');
        if (link) {
            e.preventDefault();
            const url = new URL(link.href, window.location.origin);
            const page = url.searchParams.get('page') || 1;
            currentPage = parseInt(page);
            handleFilterChange(false);
            
            if (tableContainer) {
                tableContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    });

    // Bulk validation logic
    const selectAllCheckbox = document.getElementById('selectAll');
    const bulkActionsBar = document.getElementById('bulk-actions-bar');
    const selectedCountSpan = document.getElementById('selected-count');
    const btnBulkValidate = document.getElementById('btn-bulk-validate');

    function updateBulkActionsVisibility() {
        const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
        if (checkedBoxes.length > 0) {
            bulkActionsBar.classList.remove('d-none');
            bulkActionsBar.classList.add('d-flex');
            selectedCountSpan.textContent = `${checkedBoxes.length} sélectionné(s)`;
        } else {
            bulkActionsBar.classList.add('d-none');
            bulkActionsBar.classList.remove('d-flex');
        }

        const allBoxes = document.querySelectorAll('.student-checkbox');
        if (allBoxes.length > 0 && checkedBoxes.length === allBoxes.length) {
            if(selectAllCheckbox) selectAllCheckbox.checked = true;
        } else {
            if(selectAllCheckbox) selectAllCheckbox.checked = false;
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = selectAllCheckbox.checked;
            });
            updateBulkActionsVisibility();
        });
    }

    // Event delegation for dynamically loaded checkboxes
    document.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('student-checkbox')) {
            updateBulkActionsVisibility();
        }
    });

    if (btnBulkValidate) {
        btnBulkValidate.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
            const studentIds = Array.from(checkedBoxes).map(cb => cb.value);

            if (studentIds.length === 0) return;

            Swal.fire({
                title: <?= json_encode(__('bulk_validate_enrollment_title'), JSON_UNESCAPED_UNICODE) ?>,
                text: <?= json_encode(__('bulk_validate_enrollment_text'), JSON_UNESCAPED_UNICODE) ?>.replace(':count', studentIds.length),
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#bdc3c7',
                confirmButtonText: <?= json_encode(__('bulk_validate_enrollment_action'), JSON_UNESCAPED_UNICODE) ?>,
                cancelButtonText: <?= json_encode(__('cancel'), JSON_UNESCAPED_UNICODE) ?>
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('csrf_token', '<?= \App\Core\Session::generateCsrfToken() ?>');
                    studentIds.forEach(id => formData.append('student_ids[]', id));

                    fetch('/students/bulk-validate', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire(<?= json_encode(__('success_title'), JSON_UNESCAPED_UNICODE) ?>, data.message, 'success').then(() => {
                                handleFilterChange(false);
                                if(selectAllCheckbox) selectAllCheckbox.checked = false;
                                updateBulkActionsVisibility();
                            });
                        } else {
                            Swal.fire(<?= json_encode(__('error_title'), JSON_UNESCAPED_UNICODE) ?>, data.message || <?= json_encode(__('error_generic'), JSON_UNESCAPED_UNICODE) ?>, 'error');
                        }
                    })
                    .catch(err => {
                        console.error("Erreur de validation en masse :", err);
                        Swal.fire(<?= json_encode(__('error_title'), JSON_UNESCAPED_UNICODE) ?>, <?= json_encode(__('communication_error'), JSON_UNESCAPED_UNICODE) ?>, 'error');
                    });
                }
            });
        });
    }

    // Initial setup
    updateAdvancedBadge();
});

// Confirmation dialog for withdraw
function confirmWithdraw(url) {
    Swal.fire({
        title: <?= json_encode(__('are_you_sure'), JSON_UNESCAPED_UNICODE) ?>,
        text: <?= json_encode(__('withdraw_student_confirm'), JSON_UNESCAPED_UNICODE) ?>,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#bdc3c7',
        confirmButtonText: <?= json_encode(__('confirm_withdraw_action'), JSON_UNESCAPED_UNICODE) ?>,
        cancelButtonText: <?= json_encode(__('cancel'), JSON_UNESCAPED_UNICODE) ?>
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}

// Confirmation dialog for restore
function confirmRestore(url) {
    Swal.fire({
        title: <?= json_encode(__('confirm_restore_title'), JSON_UNESCAPED_UNICODE) ?>,
        text: <?= json_encode(__('confirm_restore_text'), JSON_UNESCAPED_UNICODE) ?>,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2ecc71',
        cancelButtonColor: '#bdc3c7',
        confirmButtonText: <?= json_encode(__('confirm_restore_action'), JSON_UNESCAPED_UNICODE) ?>,
        cancelButtonText: <?= json_encode(__('cancel'), JSON_UNESCAPED_UNICODE) ?>
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}
</script>

<style>
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

    .name-gradient {
        background: linear-gradient(135deg, var(--text-main), var(--primary-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

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

    [data-theme="dark"] .table-modern tbody td .text-muted-theme {
        color: #a0a0a0;
    }
</style>

<?php $content = ob_get_clean(); ?>

<?php include __DIR__ . '/../templates/layout.php'; ?>
