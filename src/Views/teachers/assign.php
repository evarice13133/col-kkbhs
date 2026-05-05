<?php $title = __('assignments') . ": " . htmlspecialchars($teacher['nom']); ob_start(); ?>

<div class="animate-fade-in container-fluid py-4">
    
    <!-- HEADER : Titre & Actions -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-0 text-body text-main-theme"><?= __('pedagogical_assignments') ?></h2>
            <p class="text-secondary small mb-0">
                <i class="bi bi-person-circle me-1"></i> 
                <strong><?= htmlspecialchars($teacher['nom'] . ' ' . $teacher['prenom']) ?></strong> | <?= count($assignedSubjectsMap) ?> <?= __('subjects') ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" form="mainAssignmentForm" class="btn btn-primary px-4 fw-bold shadow-sm rounded-3">
                <i class="bi bi-check2-circle me-1"></i> <?= __('save') ?>
            </button>
            <a href="/teachers" class="btn btn-outline-secondary border-0 shadow-none px-3">
                <i class="bi bi-arrow-left me-1"></i> <span class="small"><?= __('back') ?></span>
            </a>
        </div>
    </div>

    <!-- RECHERCHE GLOBALE -->
    <div class="alert border-0 shadow-sm mb-4 d-flex align-items-center gap-3 py-2 px-3" style="background: var(--bs-tertiary-bg);">
        <i class="bi bi-search text-primary fs-5"></i>
        <input type="text" id="assignment-search" class="form-control border-0 shadow-none bg-transparent text-body" placeholder="<?= __('search_placeholder_global') ?>...">
    </div>

    <?php if ($err = App\Core\Session::get('error_msg')): ?>
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars((string) $err) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php App\Core\Session::remove('error_msg'); ?>
    <?php endif; ?>

    <!-- NAVIGATION PAR ONGLETS (Structure Paramètres) -->
    <div class="mb-5">
        <ul class="nav nav-pills custom-settings-tabs" id="assignmentTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active text-main-theme" id="current-tab" data-bs-toggle="pill" data-bs-target="#tab-current" type="button" role="tab">
                    <i class="bi bi-journal-check me-2"></i> <?= __('current_load') ?>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link text-main-theme" id="catalog-tab" data-bs-toggle="pill" data-bs-target="#tab-catalog" type="button" role="tab">
                    <i class="bi bi-plus-circle me-2"></i> <?= __('available_catalog') ?>
                </button>
            </li>
        </ul>
    </div>

    <form action="/teachers/store_assignment?id=<?= $teacher['id'] ?>" method="POST" id="mainAssignmentForm">
        <input type="hidden" name="csrf_token" value="<?= App\Core\Session::generateCsrfToken() ?>">

        <div class="tab-content" id="assignmentTabsContent">
            
            <!-- TABS 1 : CHARGE ACTUELLE -->
            <div class="tab-pane fade show active" id="tab-current" role="tabpanel">
                <?php if (empty($assignedSubjectsMap)): ?>
                    <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                        <i class="bi bi-journal-x fs-1 text-secondary opacity-25 mb-3"></i>
                        <h5 class="text-secondary"><?= __('no_current_assignments') ?></h5>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($assignedSubjectsMap as $sub_id => $data): ?>
                            <div class="col-md-6 col-xl-4 searchable" data-search="<?= strtolower(($data['nom'] ?? '') . ' ' . implode(' ', array_column($data['classes'] ?? [], 'nom'))) ?>">
                                <div class="card border-0 shadow-sm rounded-4 h-100 subject-card">
                                    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0 text-primary text-uppercase small"><?= htmlspecialchars($data['nom']) ?></h6>
                                        <div class="form-check form-switch p-0">
                                            <input class="form-check-input subject-toggle ms-0" type="checkbox" checked data-target=".group-current-<?= $sub_id ?>">
                                        </div>
                                    </div>
                                    <div class="card-body p-4 pt-2">
                                        <div class="d-grid gap-2">
                                            <?php foreach ($data['classes'] as $cls):
                                                $pair_key = $sub_id . '_' . $cls['id'];
                                            ?>
                                                <label class="class-item selected" for="asg_current_<?= $pair_key ?>">
                                                    <input class="form-check-input group-current-<?= $sub_id ?> d-none" type="checkbox" name="assignments[]" value="<?= $pair_key ?>" id="asg_current_<?= $pair_key ?>" checked>
                                                    <div class="checkbox-custom"></div>
                                                    <span class="small fw-bold"><?= htmlspecialchars($cls['nom']) ?></span>
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
                    <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                        <i class="bi bi-check-all fs-1 text-success mb-3"></i>
                        <h5 class="text-secondary"><?= __('all_subjects_assigned') ?></h5>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($availableSubjectsMap as $sub_id => $data): ?>
                            <?php $searchIndex = strtolower(($data['nom'] ?? '') . ' ' . implode(' ', array_column($data['classes'] ?? [], 'nom'))); ?>
                            <div class="col-md-6 col-xl-4 searchable" data-search="<?= htmlspecialchars($searchIndex) ?>">
                                <div class="card border-0 shadow-sm rounded-4 h-100 subject-card">
                                    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0 text-body text-uppercase small"><?= htmlspecialchars($data['nom'] ?? 'Inconnu') ?></h6>
                                        <div class="form-check form-switch p-0">
                                            <input class="form-check-input subject-toggle ms-0" type="checkbox" data-target=".group-avail-<?= $sub_id ?>">
                                        </div>
                                    </div>
                                    <div class="card-body p-4 pt-2">
                                        <div class="d-grid gap-2">
                                            <?php foreach ($data['classes'] as $cls):
                                                $pair_key = $sub_id . '_' . $cls['id'];
                                                $is_taken = ($cls['other_teacher'] !== null);
                                            ?>
                                                <label class="class-item <?= $is_taken ? 'is-taken' : '' ?>" <?= !$is_taken ? 'for="asg_'.$pair_key.'"' : '' ?>>
                                                    <?php if(!$is_taken): ?>
                                                        <input class="form-check-input group-avail-<?= $sub_id ?> d-none" type="checkbox" name="assignments[]" value="<?= $pair_key ?>" id="asg_<?= $pair_key ?>">
                                                        <div class="checkbox-custom"></div>
                                                    <?php else: ?>
                                                        <i class="bi bi-lock-fill text-danger me-2"></i>
                                                    <?php endif; ?>
                                                    <span class="small fw-bold"><?= htmlspecialchars($cls['nom']) ?></span>
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
.custom-settings-tabs {
    gap: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--bs-border-color);
}

.custom-settings-tabs .nav-link {
    background: transparent !important;
    color: var(--bs-body-color) !important;
    font-weight: 600;
    font-size: 0.95rem;
    padding: 0.75rem 1.25rem;
    border-radius: 0.75rem;
    transition: all 0.2s ease;
    border: 1px solid var(--bs-border-color);
}

.custom-settings-tabs .nav-link:hover {
    background: transparent !important;
    color: var(--bs-body-color) !important;
    border-color: var(--bs-secondary-color);
}

.custom-settings-tabs .nav-link.active {
    background: transparent !important;
    color: var(--bs-primary) !important;
    border: 1px solid var(--bs-primary);
}

.text-main-theme {
    color: inherit !important;
}

#assignment-search::placeholder {
    color: var(--bs-secondary-color);
    opacity: 1;
}

.subject-card {
    transition: all 0.3s ease;
}

.subject-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.08) !important;
}

.class-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    background: var(--bs-tertiary-bg);
    border-radius: 12px;
    border: 2px solid transparent;
    transition: all 0.2s;
    cursor: pointer;
}

.class-item:hover:not(.is-taken) {
    background: rgba(var(--bs-primary-rgb), 0.08);
    border-color: rgba(67, 97, 238, 0.1);
}

.class-item.selected {
    background: rgba(var(--bs-primary-rgb), 0.12);
    border-color: var(--bs-primary);
}

.class-item.is-taken {
    background: rgba(var(--bs-danger-rgb), 0.1);
    border-color: rgba(var(--bs-danger-rgb), 0.25);
    cursor: not-allowed;
    opacity: 0.7;
}

.checkbox-custom {
    width: 1.1rem;
    height: 1.1rem;
    border: 2px solid var(--bs-border-color);
    border-radius: 5px;
    margin-right: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

input:checked + .checkbox-custom {
    background: var(--bs-primary);
    border-color: var(--bs-primary);
}

input:checked + .checkbox-custom::after {
    content: '\F26E';
    font-family: 'bootstrap-icons';
    color: white;
    font-size: 0.7rem;
}

.avatar-large {
    width: 100px;
    height: 100px;
    background: rgba(255,255,255,0.2);
    border: 3px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    font-weight: 800;
    font-family: 'Outfit', sans-serif;
}

.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('assignment-search');
    const panes = document.querySelectorAll('.tab-pane');
    
    // Switch to tab if results found
    function filterAssignments() {
        const query = searchInput.value.trim().toLowerCase();
        let totalVisible = 0;

        panes.forEach(pane => {
            const cards = pane.querySelectorAll('.searchable');
            let paneVisibleCount = 0;

            cards.forEach(card => {
                const haystack = card.getAttribute('data-search') || '';
                const isVisible = query === '' || haystack.includes(query);
                card.classList.toggle('d-none', !isVisible);
                if (isVisible) paneVisibleCount++;
            });
            totalVisible += paneVisibleCount;
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

    document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
        cb.addEventListener('change', () => updateVisuals(cb));
    });

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

