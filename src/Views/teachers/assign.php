<?php $title = __('assignments') . ": " . htmlspecialchars($teacher['nom']); ob_start(); ?>

<div class="animate-fade-in container-fluid py-4">
    
    <!-- HEADER : Titre & Actions -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3 px-2">
        <div>
            <h2 class="fw-bold mb-0 text-main-theme"><?= __('pedagogical_assignments') ?></h2>
            <div class="d-flex align-items-center gap-2 mt-1">
                <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="bi bi-person-fill"></i>
                </div>
                <span class="text-secondary small fw-bold">
                    <?= htmlspecialchars($teacher['nom'] . ' ' . $teacher['prenom']) ?> 
                    <span class="mx-2 opacity-25">|</span>
                    <span class="text-primary"><?= count($assignedSubjectsMap) ?></span> <?= __('subjects') ?>
                </span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" form="mainAssignmentForm" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm scale-on-hover">
                <i class="bi bi-check2-circle me-1"></i> <?= __('save') ?>
            </button>
            <a href="/teachers" class="btn btn-light rounded-pill px-3 border shadow-none small fw-bold text-main-theme">
                <i class="bi bi-arrow-left me-1"></i> <?= __('back') ?>
            </a>
        </div>
    </div>

    <!-- RECHERCHE GLOBALE : Style Floating Island -->
    <div class="d-flex justify-content-center mb-5 px-2">
        <div class="filter-island px-3 py-2 shadow-lg animate-slide-down w-100" style="max-width: 600px;">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-search text-primary fs-5"></i>
                <input type="text" id="assignment-search" class="form-control border-0 shadow-none bg-transparent text-main-theme" 
                       placeholder="<?= __('search_placeholder_global') ?>..." style="font-weight: 500;">
            </div>
        </div>
    </div>

    <?php if ($err = App\Core\Session::get('error_msg')): ?>
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show mb-4 mx-2 rounded-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars((string) $err) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php App\Core\Session::remove('error_msg'); ?>
    <?php endif; ?>

    <!-- NAVIGATION PAR ONGLETS -->
    <div class="mb-4 px-2">
        <ul class="nav nav-pills custom-pills-modern" id="assignmentTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="current-tab" data-bs-toggle="pill" data-bs-target="#tab-current" type="button" role="tab">
                    <i class="bi bi-journal-check me-2"></i> <?= __('current_load') ?>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="catalog-tab" data-bs-toggle="pill" data-bs-target="#tab-catalog" type="button" role="tab">
                    <i class="bi bi-plus-circle me-2"></i> <?= __('available_catalog') ?>
                </button>
            </li>
        </ul>
    </div>

    <form action="/teachers/store_assignment?id=<?= $teacher['id'] ?>" method="POST" id="mainAssignmentForm" class="no-loader">
        <input type="hidden" name="csrf_token" value="<?= App\Core\Session::generateCsrfToken() ?>">

        <div class="tab-content" id="assignmentTabsContent">
            
            <!-- TABS 1 : CHARGE ACTUELLE -->
            <div class="tab-pane fade show active" id="tab-current" role="tabpanel">
                <?php if (empty($assignedSubjectsMap)): ?>
                    <div class="modern-card border-0 shadow-sm rounded-4 text-center py-5 mx-2">
                        <div class="p-4 rounded-circle bg-light d-inline-block mb-3">
                            <i class="bi bi-journal-x fs-1 opacity-25"></i>
                        </div>
                        <h5 class="text-muted-theme"><?= __('no_current_assignments') ?></h5>
                    </div>
                <?php else: ?>
                    <div class="row g-4 px-2">
                        <?php foreach ($assignedSubjectsMap as $sub_id => $data): ?>
                            <div class="col-md-6 col-xl-4 searchable" data-search="<?= strtolower(($data['nom'] ?? '') . ' ' . implode(' ', array_column($data['classes'] ?? [], 'nom'))) ?>">
                                <div class="modern-card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                                    <div class="card-header bg-primary bg-opacity-10 border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0 text-primary text-uppercase small" style="letter-spacing: 0.5px;"><?= htmlspecialchars($data['nom']) ?></h6>
                                        <div class="form-check form-switch p-0">
                                            <input class="form-check-input subject-toggle ms-0 cursor-pointer" type="checkbox" checked data-target=".group-current-<?= $sub_id ?>">
                                        </div>
                                    </div>
                                    <div class="p-4 pt-3">
                                        <div class="d-grid gap-2">
                                            <?php foreach ($data['classes'] as $cls):
                                                $pair_key = $sub_id . '_' . $cls['id'];
                                            ?>
                                                <label class="class-item selected" for="asg_current_<?= $pair_key ?>">
                                                    <input class="form-check-input group-current-<?= $sub_id ?> d-none js-asg-checkbox" type="checkbox" name="assignments[]" value="<?= $pair_key ?>" id="asg_current_<?= $pair_key ?>" checked>
                                                    <div class="checkbox-custom"></div>
                                                    <span class="small fw-bold text-main-theme"><?= htmlspecialchars($cls['nom']) ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- TABS 2 : CATALOGUE -->
            <div class="tab-pane fade" id="tab-catalog" role="tabpanel">
                <?php if (empty($availableSubjectsMap)): ?>
                    <div class="modern-card border-0 shadow-sm rounded-4 text-center py-5 mx-2">
                        <div class="p-4 rounded-circle bg-success bg-opacity-10 d-inline-block mb-3">
                            <i class="bi bi-check-all fs-1 text-success"></i>
                        </div>
                        <h5 class="text-muted-theme"><?= __('all_subjects_assigned') ?></h5>
                    </div>
                <?php else: ?>
                    <div class="row g-4 px-2">
                        <?php foreach ($availableSubjectsMap as $sub_id => $data): ?>
                            <?php $searchIndex = strtolower(($data['nom'] ?? '') . ' ' . implode(' ', array_column($data['classes'] ?? [], 'nom'))); ?>
                            <div class="col-md-6 col-xl-4 searchable" data-search="<?= htmlspecialchars($searchIndex) ?>">
                                <div class="modern-card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                                    <div class="card-header bg-light border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0 text-main-theme text-uppercase small" style="letter-spacing: 0.5px;"><?= htmlspecialchars($data['nom'] ?? 'Inconnu') ?></h6>
                                        <div class="form-check form-switch p-0">
                                            <input class="form-check-input subject-toggle ms-0 cursor-pointer" type="checkbox" data-target=".group-avail-<?= $sub_id ?>">
                                        </div>
                                    </div>
                                    <div class="p-4 pt-3">
                                        <div class="d-grid gap-2">
                                            <?php foreach ($data['classes'] as $cls):
                                                $pair_key = $sub_id . '_' . $cls['id'];
                                                $is_taken = ($cls['other_teacher'] !== null);
                                            ?>
                                                <label class="class-item <?= $is_taken ? 'is-taken' : '' ?>" <?= !$is_taken ? 'for="asg_'.$pair_key.'"' : '' ?>>
                                                    <?php if(!$is_taken): ?>
                                                        <input class="form-check-input group-avail-<?= $sub_id ?> d-none js-asg-checkbox" type="checkbox" name="assignments[]" value="<?= $pair_key ?>" id="asg_<?= $pair_key ?>">
                                                        <div class="checkbox-custom"></div>
                                                    <?php else: ?>
                                                        <i class="bi bi-lock-fill text-danger me-2"></i>
                                                    <?php endif; ?>
                                                    <span class="small fw-bold text-main-theme"><?= htmlspecialchars($cls['nom']) ?></span>
                                                    <?php if($is_taken): ?>
                                                        <span class="ms-auto" data-bs-toggle="tooltip" title="<?= htmlspecialchars($cls['other_teacher']) ?>">
                                                            <i class="bi bi-person-x-fill text-danger"></i>
                                                        </span>
                                                    <?php endif; ?>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </form>
</div>

<style>
    /* Floating Island Filters */
    .filter-island {
        background: rgba(var(--bg-card-rgb), 0.7);
        backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(var(--primary-rgb), 0.15);
        border-radius: 100px;
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .filter-island {
        background: rgba(30, 30, 45, 0.6);
        border-color: rgba(255, 255, 255, 0.08);
    }

    .scale-on-hover { transition: transform 0.2s ease; }
    .scale-on-hover:hover { transform: scale(1.05); }

    .custom-pills-modern {
        gap: 0.75rem;
    }

    .custom-pills-modern .nav-link {
        background: var(--bg-card);
        color: var(--text-main);
        border-radius: 100px;
        font-weight: 600;
        padding: 0.6rem 1.5rem;
        border: 1px solid rgba(var(--primary-rgb), 0.1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .custom-pills-modern .nav-link.active {
        background: var(--primary-color) !important;
        color: white !important;
        box-shadow: 0 10px 20px -5px rgba(var(--primary-rgb), 0.4);
        border-color: transparent;
    }

    .class-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        background: rgba(var(--primary-rgb), 0.03);
        border-radius: 12px;
        border: 2px solid transparent;
        transition: all 0.2s;
        cursor: pointer;
    }

    [data-theme="dark"] .class-item { background: rgba(255,255,255, 0.03); }

    .class-item:hover:not(.is-taken) {
        background: rgba(var(--primary-rgb), 0.08);
        border-color: rgba(var(--primary-rgb), 0.1);
    }

    .class-item.selected {
        background: rgba(var(--primary-rgb), 0.1) !important;
        border-color: var(--primary-color) !important;
    }

    .class-item.is-taken {
        background: rgba(var(--bs-danger-rgb), 0.05);
        border-color: rgba(var(--bs-danger-rgb), 0.1);
        cursor: not-allowed;
        opacity: 0.7;
    }

    .checkbox-custom {
        width: 1.1rem;
        height: 1.1rem;
        border: 2px solid rgba(var(--primary-rgb), 0.2);
        border-radius: 6px;
        margin-right: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    input:checked + .checkbox-custom {
        background: var(--primary-color);
        border-color: var(--primary-color);
    }

    input:checked + .checkbox-custom::after {
        content: '\F26E';
        font-family: 'bootstrap-icons';
        color: white;
        font-size: 0.7rem;
    }

    .cursor-pointer { cursor: pointer; }

    /* Dark mode overrides for cards */
    [data-theme="dark"] .modern-card .card-header.bg-light {
        background: rgba(255, 255, 255, 0.05) !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('mainAssignmentForm');
    const searchInput = document.getElementById('assignment-search');
    const panes = document.querySelectorAll('.tab-pane');
    
    // Search logic
    function filterAssignments() {
        const query = searchInput.value.trim().toLowerCase();
        panes.forEach(pane => {
            const cards = pane.querySelectorAll('.searchable');
            cards.forEach(card => {
                const haystack = card.getAttribute('data-search') || '';
                const isVisible = query === '' || haystack.includes(query);
                card.classList.toggle('d-none', !isVisible);
            });
        });
    }

    if(searchInput) searchInput.addEventListener('input', filterAssignments);

    // Group Selection Logic
    document.querySelectorAll('.subject-toggle').forEach((toggle) => {
        toggle.addEventListener('change', function () {
            const selector = this.getAttribute('data-target');
            document.querySelectorAll(selector).forEach((checkbox) => {
                if (!checkbox.disabled) {
                    checkbox.checked = this.checked;
                    updateVisuals(checkbox);
                }
            });
        });
    });

    function updateVisuals(checkbox) {
        const item = checkbox.closest('.class-item');
        if (item && !item.classList.contains('is-taken')) {
            item.classList.toggle('selected', checkbox.checked);
        }
    }

    document.querySelectorAll('.js-asg-checkbox').forEach(cb => {
        cb.addEventListener('change', () => updateVisuals(cb));
    });

    // --- PREMIUM CONFIRMATION POPUP ---
    if (form) {
        form.addEventListener('submit', function(e) {
            if (form.dataset.confirmed === 'true') return;
            e.preventDefault();
            
            const checkedBoxes = form.querySelectorAll('.js-asg-checkbox:checked');
            const count = checkedBoxes.length;

            const htmlContent = `
                <div style="font-size: 0.85rem; color: #000000;">
                    <p class="mb-2 fw-medium"><?= json_encode(__('confirm_save_assignments'), JSON_UNESCAPED_UNICODE) ?></p>
                    <div class="d-inline-block px-3 py-1 rounded-pill bg-primary-subtle text-primary fw-bold small">
                        ${count} <?= json_encode(__('entries_detected'), JSON_UNESCAPED_UNICODE) ?>
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
    }

    // Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>

