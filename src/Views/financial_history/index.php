<?php
$title = __('financial_history');

// Helpers pour formater l'historique d'audit
if (!function_exists('getFriendlyFieldName')) {
    function getFriendlyFieldName($key) {
        $map = [
            'amount' => __('col_amount'),
            'amount_type' => __('type_field'),
            'motive' => __('motive'),
            'status' => __('status'),
            'date_effet' => __('date_effet'),
            'commentaire' => __('comment'),
            'reference' => __('col_reference'),
            'payment_method' => __('col_method'),
            'payment_date' => __('col_date'),
            'student_id' => __('student_id'),
            'class_id' => __('class_id'),
            'type' => __('fee_type')
        ];
        return $map[$key] ?? ucfirst($key);
    }
}

if (!function_exists('formatHistoryValue')) {
    function formatHistoryValue($key, $val) {
        if ($key === 'amount_type') {
            return $val === 'percentage' ? __('percentage_label') : __('amount_fixed_label');
        }
        if ($key === 'amount' && is_numeric($val)) {
            return number_format((float)$val, 0, '.', ' ') . ' FCFA';
        }
        if ($key === 'status') {
            return $val === 'active' ? __('active') : __('inactive');
        }
        if (is_array($val)) {
            return json_encode($val, JSON_UNESCAPED_UNICODE);
        }
        return h($val);
    }
}

ob_start();
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-black text-main-theme mb-0 fs-4"><?= __('financial_audit_log') ?></h2>
            <p class="text-muted-theme small mb-0"><?= __('financial_audit_log_subtitle') ?></p>
        </div>
        <div class="d-flex gap-2">
            <a id="btn-print-pdf" href="#" target="_blank" class="btn btn-theme-soft rounded-pill px-3 fw-bold">
                <i class="bi bi-printer me-1"></i> <?= __('print_pdf') ?>
            </a>
        </div>
    </div>

    <!-- Filter Island -->
    <div class="modern-card border-0 shadow-sm p-4 mb-4">
        <form id="filters-form" method="GET" action="/financial-history">
            <!-- Hidden inputs to track period and avoid native select -->
            <input type="hidden" name="period" id="period-hidden" value="<?= h($filters['period']) ?>">

            <!-- Main Filter Controls Row -->
            <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center justify-content-between gap-3 mb-3">
                
                <!-- Period Segmented Control (Pill buttons) -->
                <div>
                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-2"><?= __('period') ?></label>
                    <div class="period-segmented-control">
                        <button type="button" class="btn-segmented <?= $filters['period'] === 'all' ? 'active' : '' ?>" data-value="all">
                            <i class="bi bi-globe me-1"></i> <?= __('all_m') ?>
                        </button>
                        <button type="button" class="btn-segmented <?= $filters['period'] === 'today' ? 'active' : '' ?>" data-value="today">
                            <i class="bi bi-calendar-event me-1"></i> <?= __('today') ?>
                        </button>
                        <button type="button" class="btn-segmented <?= $filters['period'] === 'week' ? 'active' : '' ?>" data-value="week">
                            <i class="bi bi-calendar-range me-1"></i> <?= __('this_week') ?>
                        </button>
                        <button type="button" class="btn-segmented <?= $filters['period'] === 'month' ? 'active' : '' ?>" data-value="month">
                            <i class="bi bi-calendar-month me-1"></i> <?= __('this_month') ?>
                        </button>
                        <button type="button" class="btn-segmented <?= $filters['period'] === 'custom' ? 'active' : '' ?>" data-value="custom">
                            <i class="bi bi-calendar3 me-1"></i> <?= __('custom_range') ?>
                        </button>
                    </div>
                </div>

                <!-- Search & Advanced Toggle Controls -->
                <div class="d-flex flex-wrap align-items-end gap-2 mt-auto">
                    <!-- Dynamic Search Box -->
                    <div class="search-box-container position-relative" style="min-width: 250px;">
                        <span class="position-absolute start-0 top-50 translate-middle-y ps-3 text-muted">
                            <i class="bi bi-search text-primary"></i>
                        </span>
                        <input type="text" id="client-search" class="form-control premium-input ps-5" placeholder="<?= __('search_placeholder_history') ?>" style="height: 42px;">
                        <span id="search-clear" class="position-absolute end-0 top-50 translate-middle-y pe-3 text-muted cursor-pointer" style="display: none;">
                            <i class="bi bi-x-circle-fill"></i>
                        </span>
                    </div>

                    <!-- Advanced Filters Trigger Button -->
                    <?php
                    $advActiveCount = 0;
                    if ($filters['teaching_type_id'] > 0) $advActiveCount++;
                    if (($filters['class_id'] ?? 0) > 0) $advActiveCount++;
                    if ($filters['entity_type'] !== 'all') $advActiveCount++;
                    if ($filters['action'] !== 'all') $advActiveCount++;
                    ?>
                    <button type="button" class="btn btn-theme-soft rounded-pill px-3 fw-bold d-flex align-items-center gap-2" 
                            style="height: 42px;"
                            data-bs-toggle="collapse" data-bs-target="#advanced-filters-collapse" aria-expanded="<?= $advActiveCount > 0 ? 'true' : 'false' ?>">
                        <i class="bi bi-sliders"></i>
                        <span><?= __('advanced_filters') ?></span>
                        <span class="badge bg-primary text-white rounded-pill px-2" id="adv-filter-count" style="font-size: 0.75rem; <?= $advActiveCount === 0 ? 'display: none;' : '' ?>"><?= $advActiveCount ?></span>
                    </button>

                    <!-- Reset Form Button -->
                    <a href="/financial-history" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center reset-btn" style="width: 42px; height: 42px; min-width: 42px;" title="<?= __('clear_filters') ?>">
                        <i class="bi bi-arrow-counterclockwise fs-5"></i>
                    </a>
                </div>
            </div>

            <!-- Custom Date Range Sub-row (Revealed only when period === 'custom') -->
            <div id="custom-dates-row" class="row g-3 mb-3 border-top pt-3 align-items-end" style="display: <?= $filters['period'] === 'custom' ? 'flex' : 'none' ?>;">
                <div class="col-md-4 col-lg-3">
                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('start_date') ?></label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 border-2" style="border-radius: 10px 0 0 10px; border-color: var(--border-theme); height: 48px; color: var(--primary-color);">
                            <i class="bi bi-calendar-plus"></i>
                        </span>
                        <input type="date" name="start_date" id="start-date-input" class="form-control premium-input border-start-0 ps-0" value="<?= h($filters['start_date']) ?>">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('end_date') ?></label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 border-2" style="border-radius: 10px 0 0 10px; border-color: var(--border-theme); height: 48px; color: var(--primary-color);">
                            <i class="bi bi-calendar-minus"></i>
                        </span>
                        <input type="date" name="end_date" id="end-date-input" class="form-control premium-input border-start-0 ps-0" value="<?= h($filters['end_date']) ?>">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold w-100" style="height: 48px;">
                        <i class="bi bi-check-circle me-1"></i> <?= __('validate_btn') ?>
                    </button>
                </div>
            </div>

            <!-- Collapsible Advanced Filters Section -->
            <div class="collapse <?= $advActiveCount > 0 ? 'show' : '' ?>" id="advanced-filters-collapse">
                <div class="advanced-filters-panel p-4 mt-2">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('teaching_type') ?></label>
                            <select name="teaching_type_id" id="teaching-type-select" class="form-select premium-select">
                                <option value="0" <?= $filters['teaching_type_id'] === 0 ? 'selected' : '' ?>><?= __('all_m') ?></option>
                                <?php foreach ($teachingTypes as $tt): ?>
                                    <option value="<?= $tt['id'] ?>" <?= $filters['teaching_type_id'] === (int)$tt['id'] ? 'selected' : '' ?>><?= h($tt['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('class') ?></label>
                            <select name="class_id" id="class-select" class="form-select premium-select">
                                <option value="0" <?= ($filters['class_id'] ?? 0) === 0 ? 'selected' : '' ?>><?= __('all_classes') ?? 'Toutes les classes' ?></option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= ($filters['class_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>><?= h($c['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('entity') ?></label>
                            <select name="entity_type" id="entity-type-select" class="form-select premium-select">
                                <option value="all" <?= $filters['entity_type'] === 'all' ? 'selected' : '' ?>><?= __('all_entities') ?></option>
                                <option value="payment" <?= $filters['entity_type'] === 'payment' ? 'selected' : '' ?>><?= __('entity_payment') ?></option>
                                <option value="student_payment" <?= $filters['entity_type'] === 'student_payment' ? 'selected' : '' ?>><?= __('entity_student_payment') ?></option>
                                <option value="student_discount" <?= $filters['entity_type'] === 'student_discount' ? 'selected' : '' ?>><?= __('entity_student_discount') ?></option>
                                <option value="class_discount" <?= $filters['entity_type'] === 'class_discount' ? 'selected' : '' ?>><?= __('entity_class_discount') ?></option>
                                <option value="student_scholarship" <?= $filters['entity_type'] === 'student_scholarship' ? 'selected' : '' ?>><?= __('entity_student_scholarship') ?></option>
                                <option value="class_scholarship" <?= $filters['entity_type'] === 'class_scholarship' ? 'selected' : '' ?>><?= __('entity_class_scholarship') ?></option>
                                <option value="class_finance" <?= $filters['entity_type'] === 'class_finance' ? 'selected' : '' ?>><?= __('entity_class_finance') ?></option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('action') ?></label>
                            <select name="action" id="action-select" class="form-select premium-select">
                                <option value="all" <?= $filters['action'] === 'all' ? 'selected' : '' ?>><?= __('all_actions') ?></option>
                                <option value="create" <?= $filters['action'] === 'create' ? 'selected' : '' ?>><?= __('action_create') ?></option>
                                <option value="update" <?= $filters['action'] === 'update' ? 'selected' : '' ?>><?= __('action_update') ?></option>
                                <option value="delete" <?= $filters['action'] === 'delete' ? 'selected' : '' ?>><?= __('action_delete') ?></option>
                                <option value="status" <?= $filters['action'] === 'status' ? 'selected' : '' ?>><?= __('action_status') ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Active Filters Badges Container -->
    <div id="active-filters-container">
        <?php include __DIR__ . '/badges.php'; ?>
    </div>

    <!-- History Table Card -->
    <div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in">
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 170px;"><?= __('col_datetime') ?></th>
                        <th><?= __('col_operator') ?></th>
                        <th><?= __('entity') ?></th>
                        <th><?= __('entity_id') ?></th>
                        <th><?= __('col_action') ?></th>
                        <th><?= __('old_values') ?></th>
                        <th class="pe-4"><?= __('new_values') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php include __DIR__ . '/tbody.php'; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filtersForm = document.getElementById('filters-form');
    const periodHidden = document.getElementById('period-hidden');
    const customDatesRow = document.getElementById('custom-dates-row');
    const startDateInput = document.getElementById('start-date-input');
    const endDateInput = document.getElementById('end-date-input');
    const teachingTypeSelect = document.getElementById('teaching-type-select');
    const classSelect = document.getElementById('class-select');
    const entitySelect = document.getElementById('entity-type-select');
    const actionSelect = document.getElementById('action-select');
    const btnPrintPdf = document.getElementById('btn-print-pdf');
    
    // Client-side search elements
    const clientSearch = document.getElementById('client-search');
    const searchClear = document.getElementById('search-clear');
    
    const tableContainer = document.querySelector('.modern-card.overflow-hidden');

    function updateAdvancedBadge() {
        let count = 0;
        if (teachingTypeSelect && teachingTypeSelect.value !== '0') count++;
        if (classSelect && classSelect.value !== '0') count++;
        if (entitySelect && entitySelect.value !== 'all') count++;
        if (actionSelect && actionSelect.value !== 'all') count++;
        
        const badge = document.getElementById('adv-filter-count');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline-block' : 'none';
        }
    }

    function toggleCustomDates() {
        if (periodHidden.value === 'custom') {
            customDatesRow.style.display = 'flex';
        } else {
            customDatesRow.style.display = 'none';
            startDateInput.value = '';
            endDateInput.value = '';
        }
    }

    function updatePrintUrl() {
        const params = new URLSearchParams({
            period: periodHidden.value,
            start_date: startDateInput.value,
            end_date: endDateInput.value,
            teaching_type_id: teachingTypeSelect ? teachingTypeSelect.value : '0',
            class_id: classSelect ? classSelect.value : '0',
            entity_type: entitySelect ? entitySelect.value : 'all',
            action: actionSelect ? actionSelect.value : 'all'
        });
        btnPrintPdf.setAttribute('href', `/financial-history/print?${params.toString()}`);
    }

    function handleFilterChange(forceSubmit = false) {
        updatePrintUrl();
        updateAdvancedBadge();
        
        // Show loading state
        if (tableContainer) {
            tableContainer.classList.add('table-loading-active');
        }

        if (periodHidden.value === 'custom' && !forceSubmit && (!startDateInput.value || !endDateInput.value)) {
            if (tableContainer) {
                tableContainer.classList.remove('table-loading-active');
            }
            return;
        }

        const params = new URLSearchParams({
            period: periodHidden.value,
            start_date: startDateInput.value,
            end_date: endDateInput.value,
            teaching_type_id: teachingTypeSelect ? teachingTypeSelect.value : '0',
            class_id: classSelect ? classSelect.value : '0',
            entity_type: entitySelect ? entitySelect.value : 'all',
            action: actionSelect ? actionSelect.value : 'all',
            ajax: 1
        });

        fetch(`/financial-history?${params.toString()}`)
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

                    // Re-apply client search if any
                    if (clientSearch && clientSearch.value) {
                        performSearch(clientSearch.value);
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

    // Segmented Period buttons click
    document.querySelectorAll('.btn-segmented').forEach(btn => {
        btn.addEventListener('click', function() {
            const val = this.getAttribute('data-value');
            
            // Deactivate other buttons
            document.querySelectorAll('.btn-segmented').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            periodHidden.value = val;
            toggleCustomDates();
            
            if (val !== 'custom') {
                handleFilterChange();
            } else {
                updatePrintUrl();
            }
        });
    });

    // Inputs change listeners
    startDateInput.addEventListener('change', () => handleFilterChange());
    endDateInput.addEventListener('change', () => handleFilterChange());
    
    if (teachingTypeSelect) teachingTypeSelect.addEventListener('change', () => handleFilterChange(true));
    if (classSelect) classSelect.addEventListener('change', () => handleFilterChange(true));
    if (entitySelect) entitySelect.addEventListener('change', () => handleFilterChange(true));
    if (actionSelect) actionSelect.addEventListener('change', () => handleFilterChange(true));

    filtersForm.addEventListener('submit', function(e) {
        e.preventDefault();
        handleFilterChange(true);
    });

    // Helper pour réinitialiser individuellement les filtres via badges
    window.resetFilter = function(type) {
        if (type === 'period') {
            periodHidden.value = 'all';
            startDateInput.value = '';
            endDateInput.value = '';
        } else if (type === 'teaching_type') {
            if (teachingTypeSelect) teachingTypeSelect.value = '0';
        } else if (type === 'class') {
            if (classSelect) classSelect.value = '0';
        } else if (type === 'entity_type') {
            if (entitySelect) entitySelect.value = 'all';
        } else if (type === 'action') {
            if (actionSelect) actionSelect.value = 'all';
        }
        handleFilterChange(true);
    };

    // Client-side quick search implementation
    if (clientSearch) {
        clientSearch.addEventListener('input', function() {
            performSearch(this.value);
        });

        searchClear.addEventListener('click', function() {
            clientSearch.value = '';
            performSearch('');
            clientSearch.focus();
        });
    }

    function performSearch(query) {
        query = query.toLowerCase().trim();
        const rows = document.querySelectorAll('.student-row');
        
        if (query.length > 0) {
            searchClear.style.display = 'block';
        } else {
            searchClear.style.display = 'none';
        }

        let visibleCount = 0;

        rows.forEach(row => {
            // Remove existing highlight spans to reset row HTML
            removeHighlights(row);

            const text = row.textContent.toLowerCase();
            if (text.includes(query)) {
                row.style.display = '';
                visibleCount++;
                if (query.length >= 2) {
                    highlightTextInRow(row, query);
                }
            } else {
                row.style.display = 'none';
            }
        });

        // Handle empty state if all rows are hidden
        const existingNoData = document.getElementById('client-no-data-row');
        if (visibleCount === 0 && rows.length > 0) {
            if (!existingNoData) {
                const tbody = document.querySelector('.table-modern tbody');
                const tr = document.createElement('tr');
                tr.id = 'client-no-data-row';
                tr.innerHTML = `<td colspan="7" class="text-center py-5 text-muted">
                    <i class="bi bi-search fs-4 d-block mb-2 text-secondary"></i>
                    Aucune correspondance pour "${escapeHTML(query)}"
                </td>`;
                tbody.appendChild(tr);
            }
        } else if (existingNoData) {
            existingNoData.remove();
        }
    }

    function removeHighlights(element) {
        const highlights = element.querySelectorAll('.highlight-match');
        highlights.forEach(span => {
            const parent = span.parentNode;
            if (parent) {
                parent.replaceChild(document.createTextNode(span.textContent), span);
                parent.normalize();
            }
        });
    }

    function highlightTextInRow(row, query) {
        const cells = row.querySelectorAll('td');
        cells.forEach(cell => {
            highlightTextNode(cell, query);
        });
    }

    function highlightTextNode(node, query) {
        if (node.nodeType === Node.TEXT_NODE) {
            const text = node.nodeValue;
            const index = text.toLowerCase().indexOf(query);
            if (index >= 0) {
                const span = document.createElement('span');
                span.className = 'highlight-match';
                
                const matchText = text.substring(index, index + query.length);
                const beforeText = text.substring(0, index);
                const afterText = text.substring(index + query.length);
                
                span.textContent = matchText;
                
                const parent = node.parentNode;
                if (parent) {
                    if (beforeText) {
                        parent.insertBefore(document.createTextNode(beforeText), node);
                    }
                    parent.insertBefore(span, node);
                    node.nodeValue = afterText;
                    highlightTextNode(node, query);
                }
            }
        } else if (node.nodeType === Node.ELEMENT_NODE && node.childNodes && !node.classList.contains('highlight-match') && node.tagName !== 'SCRIPT' && node.tagName !== 'STYLE') {
            const children = Array.from(node.childNodes);
            children.forEach(child => highlightTextNode(child, query));
        }
    }

    function escapeHTML(str) {
        return str.replace(/[&<>'"]/g, 
            tag => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#39;',
                '"': '&quot;'
             }[tag] || tag)
        );
    }

    // Initial load configurations
    toggleCustomDates();
    updatePrintUrl();
    updateAdvancedBadge();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
