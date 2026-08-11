<?php $title = __('assignments') . ": " . htmlspecialchars($teacher['nom']); ob_start(); ?>

<div class="animate-fade-in container-fluid py-4">
    
    <!-- HEADER : Titre & Actions -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3 px-2 animate-fade-in">
        <div>
            <h2 class="fw-black text-main-theme mb-0 fs-4"><?= __('pedagogical_assignments') ?></h2>
            <div class="d-flex align-items-center gap-2 mt-1">
                <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.85rem;">
                    <?= strtoupper(substr((string) ($teacher['nom']), 0, 1)) ?>
                </div>
                <span class="text-secondary small fw-bold text-main-theme">
                    <?= htmlspecialchars($teacher['nom'] . ' ' . $teacher['prenom']) ?> 
                    <span class="mx-2 opacity-25">|</span>
                    <span class="text-primary fw-extrabold"><?= count($assignedSubjectsMap) ?></span> <?= __('subjects') ?>
                </span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" form="mainAssignmentForm" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm transition-base scale-on-hover" 
                    <?= $isHistoricalView ? 'disabled' : '' ?>>
                <i class="bi bi-check2-circle me-1 fs-6"></i> <?= __('save') ?>
            </button>
            <a href="/teachers" class="btn btn-light-theme rounded-pill px-4 border border-theme-light shadow-sm transition-base text-main-theme fw-bold">
                <i class="bi bi-arrow-left me-1"></i> <?= __('back') ?>
            </a>
        </div>
    </div>

    <!-- ACTIONS & FILTERS PANEL (Unified Horizontal Action Bar) -->
    <div class="d-flex justify-content-center mb-5 px-2">
        <div class="filter-island px-4 py-3 shadow-lg animate-slide-down w-100" style="max-width: 800px;">
            <div class="d-flex align-items-center gap-3 flex-wrap flex-md-nowrap w-100">
                
                <!-- Academic Year Selector -->
                <div class="input-group year-pill bg-white bg-opacity-10 rounded-pill px-2 flex-grow-1" style="max-width: 320px;">
                    <span class="input-group-text border-0 bg-transparent text-primary">
                        <i class="bi bi-calendar3"></i>
                    </span>
                    <select id="academic_year_selector" class="form-select border-0 bg-transparent shadow-none py-2 text-main-theme fw-bold" 
                            onchange="changeAcademicYear(this.value)">
                        <?php foreach ($academicYears as $year): ?>
                            <option value="<?= $year['id'] ?>" <?= $selectedYearId == $year['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($year['nom']) ?>
                                <?= $year['is_active'] ? ' (' . __('active') . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Search Box -->
                <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2 flex-grow-1">
                    <span class="input-group-text border-0 bg-transparent text-primary">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="assignment-search" class="form-control border-0 bg-transparent shadow-none py-2 text-main-theme" 
                           placeholder="<?= __('search_placeholder_global') ?>..." style="font-weight: 500;">
                </div>
                
            </div>
        </div>
    </div>

    <?php if ($isHistoricalView): ?>
    <div class="alert alert-warning border-0 shadow-sm mb-4 mx-2 rounded-4" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>
        <?= __('viewing_historical_assignments_read_only') ?? 'Consultation des affectations historiques (lecture seule)' ?>
    </div>
    <?php endif; ?>

    <?php if ($success = App\Core\Session::getFlash('success') ?: App\Core\Session::getFlash('success_msg')): ?>
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4 mx-2 rounded-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars((string) $success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

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
            <?php if (!$isHistoricalView): ?>
            <li class="nav-item">
                <button class="nav-link" id="catalog-tab" data-bs-toggle="pill" data-bs-target="#tab-catalog" type="button" role="tab">
                    <i class="bi bi-plus-circle me-2"></i> <?= __('available_catalog') ?>
                </button>
            </li>
            <?php endif; ?>
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
                                                <div class="d-flex align-items-center justify-content-between gap-2 p-1">
                                                    <label class="class-item selected flex-grow-1 mb-0" for="asg_current_<?= $pair_key ?>">
                                                        <input class="form-check-input group-current-<?= $sub_id ?> d-none js-asg-checkbox" type="checkbox" name="assignments[]" value="<?= $pair_key ?>" id="asg_current_<?= $pair_key ?>" checked>
                                                        <div class="checkbox-custom"></div>
                                                        <span class="small fw-bold text-main-theme"><?= htmlspecialchars($cls['nom']) ?></span>
                                                    </label>
                                                    <?php if (!$isHistoricalView): ?>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-danger border-0 rounded-circle p-1 d-flex align-items-center justify-content-center" 
                                                                style="width: 28px; height: 28px;"
                                                                title="<?= __('cancel_assignment') ?? 'Annuler cette affectation' ?>" 
                                                                onclick="confirmCancelAssignment(event, '<?= (int)$teacher['id'] ?>', '<?= (int)$sub_id ?>', '<?= (int)$cls['id'] ?>', '<?= htmlspecialchars($data['nom'], ENT_QUOTES) ?>', '<?= htmlspecialchars($cls['nom'], ENT_QUOTES) ?>')">
                                                            <i class="bi bi-trash fs-6"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
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
                                                        <button type="button" 
                                                                class="btn btn-xs btn-outline-warning rounded-pill ms-auto px-2 py-1 extra-small fw-bold border-1 shadow-sm" 
                                                                title="<?= htmlspecialchars($cls['other_teacher']) ?>"
                                                                onclick="openCourseTransferModal(<?= (int)$sub_id ?>, <?= (int)$cls['id'] ?>, '<?= htmlspecialchars($data['nom'], ENT_QUOTES) ?>', '<?= htmlspecialchars($cls['nom'], ENT_QUOTES) ?>', '<?= htmlspecialchars($cls['other_teacher'], ENT_QUOTES) ?>', <?= (int)($cls['other_teacher_id'] ?? 0) ?>, <?= (int)$teacher['id'] ?>)">
                                                            <i class="bi bi-arrow-left-right me-1"></i> Transférer / Réaffecter
                                                        </button>
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
    /* Unified Action Bar Glassmorphism */
    .filter-island {
        background: rgba(255, 255, 255, 0.65);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-radius: 30px;
        border: 1px solid rgba(var(--primary-rgb), 0.08);
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    }
    [data-theme="dark"] .filter-island {
        background: rgba(26, 26, 39, 0.6);
        border-color: rgba(255, 255, 255, 0.05);
    }
    .filter-island:hover {
        border-color: rgba(var(--primary-rgb), 0.15);
        box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.05);
    }
    .filter-island:focus-within {
        border-color: var(--primary-color);
        box-shadow: 0 20px 40px -10px rgba(var(--primary-rgb), 0.15);
        transform: translateY(-2px);
    }
    
    .search-pill, .year-pill {
        border: 1px solid rgba(var(--primary-rgb), 0.08) !important;
        background: rgba(var(--primary-rgb), 0.02) !important;
        transition: all 0.3s ease;
    }
    .search-pill:focus-within, .year-pill:focus-within {
        border-color: var(--primary-color) !important;
        background: rgba(var(--primary-rgb), 0.05) !important;
        box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.12);
    }

    .scale-on-hover { transition: transform 0.2s ease; }
    .scale-on-hover:hover { transform: scale(1.03); }

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

    .modern-card {
        background: var(--bg-card);
        border: 1px solid rgba(var(--primary-rgb), 0.08) !important;
        border-radius: 20px;
        transition: all 0.3s ease;
    }
    .modern-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.05);
        border-color: rgba(var(--primary-rgb), 0.15) !important;
    }

    .class-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        background: rgba(var(--primary-rgb), 0.02);
        border-radius: 12px;
        border: 1.5px solid rgba(var(--primary-rgb), 0.05);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }

    [data-theme="dark"] .class-item { background: rgba(255,255,255, 0.03); }

    .class-item:hover:not(.is-taken) {
        background: rgba(var(--primary-rgb), 0.05);
        border-color: rgba(var(--primary-rgb), 0.15);
    }

    .class-item.selected {
        background: rgba(var(--primary-rgb), 0.08) !important;
        border-color: var(--primary-color) !important;
        box-shadow: 0 4px 12px -3px rgba(var(--primary-rgb), 0.15);
    }

    .class-item.is-taken {
        background: rgba(var(--bs-danger-rgb), 0.05);
        border-color: rgba(var(--bs-danger-rgb), 0.1);
        cursor: not-allowed;
        opacity: 0.7;
    }

    .checkbox-custom {
        width: 1.2rem;
        height: 1.2rem;
        border: 2px solid rgba(var(--primary-rgb), 0.25);
        border-radius: 6px;
        margin-right: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    input:checked + .checkbox-custom {
        background: var(--primary-color);
        border-color: var(--primary-color);
        box-shadow: 0 2px 6px rgba(var(--primary-rgb), 0.3);
    }

    input:checked + .checkbox-custom::after {
        content: '\F26E';
        font-family: 'bootstrap-icons';
        color: white;
        font-size: 0.75rem;
        font-weight: 900;
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

    // --- CHANGEMENT D'ANNÉE SCOLAIRE ---
    function changeAcademicYear(yearId) {
        const url = new URL(window.location.href);
        url.searchParams.set('academic_year_id', yearId);
        window.location.href = url.toString();
    }

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

window.confirmCancelAssignment = function(e, teacherId, subjectId, classId, subjectName, className) {
    if (e) {
        e.stopPropagation();
        e.preventDefault();
    }

    AlertService.confirm({
        title: <?= json_encode(__('cancel_assignment') ?? 'Annuler l\'affectation', JSON_UNESCAPED_UNICODE) ?>,
        html: `Voulez-vous vraiment annuler l'affectation de la matière <strong>${subjectName}</strong> pour la classe <strong>${className}</strong> ?`,
        icon: 'warning',
        confirmText: <?= json_encode(__('confirm') ?? 'Oui, annuler', JSON_UNESCAPED_UNICODE) ?>,
        cancelText: <?= json_encode(__('cancel') ?? 'Annuler', JSON_UNESCAPED_UNICODE) ?>,
        customClass: {
            confirmButton: 'btn btn-danger btn-sm rounded-pill px-4 me-2',
            cancelButton: 'btn btn-light btn-sm rounded-pill px-4'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('cancel_teacher_id').value = teacherId;
            document.getElementById('cancel_subject_id').value = subjectId;
            document.getElementById('cancel_class_id').value = classId;
            AlertService.loading(<?= json_encode(__('processing') ?? 'Traitement...', JSON_UNESCAPED_UNICODE) ?>);
            document.getElementById('cancelAssignmentForm').submit();
        }
    });
};

window.openCourseTransferModal = function(subjectId, classId, subjectName, className, sourceTeacherName, sourceTeacherId, targetTeacherId) {
    document.getElementById('tr_subject_id').value = subjectId;
    document.getElementById('tr_class_id').value = classId;
    document.getElementById('tr_source_teacher_id').value = sourceTeacherId || 0;

    document.getElementById('tr_subject_name_text').innerText = subjectName;
    document.getElementById('tr_class_name_text').innerText = className;
    document.getElementById('tr_source_teacher_text').innerText = sourceTeacherName || 'Non spécifié';

    const targetSelect = document.getElementById('tr_target_teacher_id');
    if (targetSelect) {
        if (targetTeacherId) targetSelect.value = targetTeacherId;
        targetSelect.disabled = false;
    }

    const checkNew = document.getElementById('create_new_teacher');
    if (checkNew) {
        checkNew.checked = false;
        toggleNewTeacherFields(checkNew);
    }

    const modalEl = document.getElementById('transferCourseModal');
    if (modalEl.parentNode !== document.body) {
        document.body.appendChild(modalEl);
    }
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
};

window.toggleNewTeacherFields = function(checkbox) {
    const container = document.getElementById('newTeacherInputContainer');
    const targetSelect = document.getElementById('tr_target_teacher_id');
    if (container) {
        container.classList.toggle('d-none', !checkbox.checked);
    }
    if (targetSelect) {
        targetSelect.disabled = checkbox.checked;
    }
};
</script>

<!-- Modal Pop-Up de Conflit & Transfert de Cours -->
<div class="modal fade" id="transferCourseModal" tabindex="-1" aria-labelledby="transferCourseModalLabel" aria-hidden="true" style="z-index: 1085;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: var(--bg-card);">
            <div class="modal-header bg-warning text-dark p-4">
                <h5 class="modal-title fw-black" id="transferCourseModalLabel">
                    <i class="bi bi-arrow-left-right me-2"></i>Conflit d'affectation : Réaffectation & Transfert
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="transferCourseForm" action="/teachers/transfer_course" method="POST">
                <input type="hidden" name="csrf_token" value="<?= App\Core\Session::generateCsrfToken() ?>">
                <input type="hidden" name="redirect_teacher_id" value="<?= (int)$teacher['id'] ?>">
                <input type="hidden" name="subject_id" id="tr_subject_id">
                <input type="hidden" name="class_id" id="tr_class_id">
                <input type="hidden" name="source_teacher_id" id="tr_source_teacher_id">

                <div class="modal-body p-4">
                    <div class="alert alert-warning border-0 rounded-3 p-3 mb-4">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-exclamation-triangle-fill fs-5 text-warning flex-shrink-0"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Un même cours ne peut pas être affecté à deux enseignants simultanément.</h6>
                                <p class="small mb-0 opacity-85">
                                    Ce cours est déjà attribué à un autre enseignant. Pour lui attribuer un nouveau titulaire, vous devez effectuer le transfert des données et de l'affectation.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Récapitulatif du cours en conflit -->
                    <div class="card border border-warning border-opacity-30 rounded-3 p-3 mb-4 bg-warning bg-opacity-10">
                        <div class="row g-3 text-main-theme">
                            <div class="col-md-4">
                                <span class="text-muted extra-small d-block text-uppercase fw-bold">Matière concernée</span>
                                <strong id="tr_subject_name_text" class="text-primary fs-6"></strong>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted extra-small d-block text-uppercase fw-bold">Classe concernée</span>
                                <strong id="tr_class_name_text" class="text-main-theme fs-6"></strong>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted extra-small d-block text-uppercase fw-bold">Enseignant Actuel</span>
                                <strong id="tr_source_teacher_text" class="text-danger fs-6"></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Sélection du nouveau titulaire -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-main-theme small">
                            <i class="bi bi-person-check-fill me-1 text-primary"></i>Choisir le nouveau titulaire du cours :
                        </label>
                        <select id="tr_target_teacher_id" name="target_teacher_id" class="form-select rounded-3">
                            <option value="">-- Sélectionner un enseignant destinataire --</option>
                            <?php foreach ($allTeachers as $t_opt): ?>
                                <option value="<?= $t_opt['id'] ?>" <?= $t_opt['id'] == $teacher['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t_opt['nom'] . ' ' . $t_opt['prenom']) ?> (<?= htmlspecialchars($t_opt['username']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Création rapide d'un nouvel enseignant -->
                    <div class="p-3 rounded-3 border bg-light-theme mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input cursor-pointer" type="checkbox" id="create_new_teacher" name="create_new_teacher" value="1" onchange="toggleNewTeacherFields(this)">
                            <label class="form-check-label fw-bold text-main-theme small cursor-pointer" for="create_new_teacher">
                                <i class="bi bi-person-plus-fill me-1 text-success"></i>Créer directement un nouvel enseignant et lui transférer ce cours
                            </label>
                        </div>
                        <div id="newTeacherInputContainer" class="mt-3 d-none">
                            <label class="form-label fw-bold extra-small text-main-theme mb-1">Nom et Prénom du nouvel enseignant *</label>
                            <input type="text" name="new_teacher_name" id="tr_new_teacher_name" class="form-control rounded-3" placeholder="Ex: Dr. Martin KAMGA">
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-top p-3" style="background: var(--bg-card);">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm text-dark">
                        <i class="bi bi-arrow-left-right me-1"></i>Transférer les données & Affecter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($conflictData = App\Core\Session::getFlash('assignment_conflict')): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    openCourseTransferModal(
        <?= (int)$conflictData['subject_id'] ?>,
        <?= (int)$conflictData['class_id'] ?>,
        <?= json_encode($conflictData['subject_name']) ?>,
        <?= json_encode($conflictData['class_name']) ?>,
        <?= json_encode($conflictData['source_teacher_name']) ?>,
        <?= (int)$conflictData['source_teacher_id'] ?>,
        <?= (int)$conflictData['target_teacher_id'] ?>
    );
});
</script>
<?php endif; ?>

<form id="cancelAssignmentForm" action="/teachers/remove_assignment" method="POST" class="d-none">
    <input type="hidden" name="csrf_token" value="<?= App\Core\Session::generateCsrfToken() ?>">
    <input type="hidden" name="teacher_id" id="cancel_teacher_id">
    <input type="hidden" name="subject_id" id="cancel_subject_id">
    <input type="hidden" name="class_id" id="cancel_class_id">
</form>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>

