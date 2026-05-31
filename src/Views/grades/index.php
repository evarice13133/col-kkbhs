<?php
/**
 * Vue Index des Notes
 * Refonte Premium avec Glassmorphism et Analyse en temps réel
 */
$title = __('notes_management');
ob_start();

$listExportUrl = '/notes/export?' . http_build_query(array_merge($filters, ['mode' => 'list']));
$reportExportUrl = '/notes/export?' . http_build_query(array_merge($filters, ['mode' => 'report']));
$reportPdfUrl = '/notes/export?' . http_build_query(array_merge($filters, ['mode' => 'report', 'format' => 'pdf']));
$canExportList = (int) $filters['class_id'] > 0 && $classHasFilledGrades;
$canExportReport = (int) $filters['class_id'] > 0 && (int) $filters['subject_id'] > 0;
?>

<div class="animate-fade-in grades-workspace">


    <?php if (in_array(App\Core\Session::get('user_role'), ['admin', 'superadmin'])): ?>
    <!-- BARRE D'ACTIONS COMPLÈTE : Style Floating Island -->
    <div class="d-flex justify-content-center mb-5">
        <div class="filter-island px-3 py-2 shadow-lg animate-slide-down" style="min-width: 90%;">
            <form method="GET" class="d-flex align-items-center gap-2 flex-wrap flex-md-nowrap filter-form w-100" id="filterForm">
                
                <!-- Sélecteurs de Contexte -->
                <div class="d-flex gap-2 pe-3 border-end border-opacity-10 border-secondary me-2">
                    <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2 py-1">
                        <span class="input-group-text border-0 bg-transparent text-primary small fw-bold text-uppercase me-2 d-none d-xl-inline-block">
                            <?= __('class') ?>
                        </span>
                        <select name="class_id" class="form-select border-0 bg-transparent shadow-none fw-bold text-main"
                                onchange="this.form.submit()">
                            <option value=""><?= __('all_classes') ?></option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= $class['id'] ?>" <?= (int) $filters['class_id'] === (int) $class['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $class['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2 py-1">
                        <span class="input-group-text border-0 bg-transparent text-primary small fw-bold text-uppercase me-2 d-none d-xl-inline-block">
                            <?= __('subject') ?>
                        </span>
                        <select name="subject_id" class="form-select border-0 bg-transparent shadow-none fw-bold text-main"
                                onchange="this.form.submit()">
                            <option value=""><?= __('all_subjects') ?></option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?= $subject['id'] ?>" <?= (int) $filters['subject_id'] === (int) $subject['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $subject['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Barre de Recherche Locale -->
                <div class="flex-grow-1">
                    <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2">
                        <span class="input-group-text border-0 bg-transparent text-primary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="subject-search" class="form-control border-0 bg-transparent shadow-none py-2 text-main"
                            placeholder="<?= __('search_placeholder_global') ?>...">
                    </div>
                </div>

                <!-- Utilitaires -->
                <div class="d-flex gap-2 align-items-center ps-2">
                    <a href="/notes" class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center reset-btn" style="width: 40px; height: 40px;" title="<?= __('reset') ?>">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                    
                    <!-- Export Panel (Compact Dropdown) -->
                    <div class="dropdown">
                        <button class="btn-export-minimal shadow-sm" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-modern shadow-lg">
                            <li><h6 class="dropdown-header small text-uppercase fw-bold opacity-75"><?= __('exports') ?></h6></li>
                            <?php if ($canExportList): ?>
                                <li><a class="dropdown-item dropdown-item-modern" href="<?= htmlspecialchars($listExportUrl) ?>">
                                    <i class="bi bi-file-pdf text-danger"></i> <?= __('grade_list_export') ?>
                                </a></li>
                            <?php endif; ?>
                            <?php if ($canExportReport): ?>
                                <li><a class="dropdown-item dropdown-item-modern" href="<?= htmlspecialchars($reportExportUrl) ?>">
                                    <i class="bi bi-printer-fill text-primary"></i> <?= __('grade_report_sheet') ?>
                                </a></li>
                                <li><a class="dropdown-item dropdown-item-modern" href="<?= htmlspecialchars($reportPdfUrl) ?>">
                                    <i class="bi bi-file-earmark-pdf-fill text-danger"></i> <?= __('grade_report_pdf') ?>
                                </a></li>
                            <?php endif; ?>
                            <?php if (!$canExportList && !$canExportReport): ?>
                                <li class="px-3 py-2 small text-muted"><?= __('grade_export_hint') ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php else: ?>
        <!-- TEACHER BARRE D'ACTIONS COMPLÈTE -->
        <div class="d-flex justify-content-center mb-5">
            <div class="filter-island px-3 py-2 shadow-lg animate-slide-down" style="min-width: 60%;">
                <form class="d-flex align-items-center gap-2 filter-form w-100">
                    <div class="flex-grow-1">
                        <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2">
                            <span class="input-group-text border-0 bg-transparent text-primary">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" id="subject-search" class="form-control border-0 bg-transparent shadow-none py-2 text-main"
                                placeholder="<?= __('search') ?>...">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content Workspace -->
    <div class="row g-4">
        <!-- Main List Area -->
        <div class="col-lg-8">
            <div id="grid-container">
                <?php if (empty($dashboard)): ?>
                    <div class="modern-card p-5 text-center border-dashed border-2 rounded-5 glass-card animate-fade-in">
                        <i class="bi bi-stars fs-1 text-warning mb-3 d-block"></i>
                        <h4 class="fw-bold text-main-theme"><?= __('all_grades_filled') ?></h4>
                        <p class="text-muted-theme small mb-0"><?= __('no_pending_assignments') ?></p>
                    </div>
                <?php endif; ?>

                <?php foreach ($dashboard as $class_name => $subjectsRaw): ?>
                    <?php
                        // Récupérer le class_id depuis le premier sujet
                        $class_id = !empty($subjectsRaw) ? $subjectsRaw[0]['class_id'] : 0;
                    ?>
                    <div class="class-section animate-slide-up mb-5" data-class="<?= strtolower($class_name) ?>">
                        <div class="class-header-premium mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="class-header-icon">
                                    <i class="bi bi-door-open-fill"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h5 class="m-0 fw-black text-main-theme text-uppercase letter-spacing-2"><?= htmlspecialchars((string) $class_name) ?></h5>
                                        <?php if ($isAdmin && $class_id > 0): ?>
                                        <a href="/notes/import?class_id=<?= $class_id ?>&subject_id=0" class="btn btn-outline-success rounded-pill px-2 py-1 small fw-bold shadow-sm text-nowrap" title="<?= __('import_grades') ?>">
                                            <i class="bi bi-upload me-1"></i> <?= __('import') ?>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small text-muted-theme opacity-50 d-flex align-items-center gap-2 mt-1">
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-0 extra-small border border-primary border-opacity-10">
                                            <?= count($subjectsRaw) ?> <?= __('subjects') ?>
                                        </span>
                                        <div class="header-line-accent"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        $userRole = \App\Core\Session::get('user_role');
                        $isAdmin = in_array($userRole, ['admin', 'superadmin']);
                        ?>
                        <div class="row g-2 g-md-3">
                            <?php foreach ($subjectsRaw as $sub): ?>
                                <div class="col-6 col-sm-6 col-xl-4 subject-grid-item"
                                    data-subject-name="<?= strtolower($sub['subject_nom']) ?>">
                                    <?php if ($sub['teacher_nom']): ?>
                                    <a href="/notes/saisie?class_id=<?= $sub['class_id'] ?>&subject_id=<?= $sub['subject_id'] ?>" class="subject-card-compact border-theme-dynamic h-100">
                                    <?php else: ?>
                                    <div class="subject-card-compact border-theme-dynamic h-100 is-unassigned">
                                    <?php endif; ?>
                                        <div class="subject-card-glow"></div>
                                        <div class="card-body p-2 p-md-3 h-100 position-relative" style="z-index: 1;">
                                            <div class="d-flex flex-column h-100 justify-content-between gap-1">
                                                <div>
                                                    <div class="d-flex align-items-start gap-2 mb-1 text-main-theme">
                                                        <div class="flex-shrink-0 mt-1">
                                                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                                                                <i class="bi bi-journal-text text-primary small"></i>
                                                            </div>
                                                        </div>
                                                        <span class="fw-bold lh-sm flex-grow-1" style="font-size: 0.8rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                            <?= htmlspecialchars((string) $sub['subject_nom']) ?>
                                                        </span>
                                                    </div>
                                                    
                                                    <?php if ($isAdmin && $sub['teacher_nom']): ?>
                                                        <div class="extra-small text-muted-theme fw-normal opacity-75 d-flex align-items-center gap-1 ps-4 ms-1">
                                                            <i class="bi bi-person text-primary opacity-50"></i>
                                                            <span class="text-truncate"><?= htmlspecialchars($sub['teacher_prenom'] . ' ' . $sub['teacher_nom']) ?></span>
                                                        </div>
                                                    <?php elseif (!$sub['teacher_nom']): ?>
                                                        <?php
                                                            $filled = (int) ($sub['filled_count'] ?? 0);
                                                            $total = (int) ($sub['total_count'] ?? 0);
                                                            $isComplete = (bool) ($sub['is_complete'] ?? ($total > 0 && $filled >= $total));
                                                        ?>
                                                        <div class="extra-small text-warning fw-bold d-flex align-items-center gap-1 ps-4 ms-1">
                                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                                            <span><?= __('not_assigned') ?></span>
                                                            <span class="text-muted-theme fw-semibold opacity-75">
                                                                <?= $filled ?>/<?= $total ?>
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="mt-auto pt-2 d-flex justify-content-between align-items-center">
                                                    <?php if (!$sub['teacher_nom']): ?>
                                                        <div class="d-flex gap-2 w-100">
                                                            <a href="/teachers?assign_subject=<?= $sub['subject_id'] ?>&assign_class=<?= $sub['class_id'] ?>" class="btn btn-xs btn-outline-warning rounded-pill py-0 px-2 extra-small fw-bold shadow-sm flex-grow-1 text-decoration-none text-center">
                                                                <i class="bi bi-person-plus-fill me-1"></i><?= __('assign') ?>
                                                            </a>
                                                            <?php if ($isAdmin): ?>
                                                            <a href="/notes/saisie?class_id=<?= $sub['class_id'] ?>&subject_id=<?= $sub['subject_id'] ?>" class="btn btn-xs btn-outline-primary rounded-pill py-0 px-2 extra-small fw-bold shadow-sm flex-grow-1 text-decoration-none text-center">
                                                                <i class="bi bi-pencil-fill me-1"></i><?= __('enter_grades') ?>
                                                            </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <?php
                                                            $filled = (int) ($sub['filled_count'] ?? 0);
                                                            $total = (int) ($sub['total_count'] ?? 0);
                                                            $isComplete = (bool) ($sub['is_complete'] ?? ($total > 0 && $filled >= $total));
                                                        ?>
                                                        <div class="d-flex align-items-center gap-1">
                                                            <?php if ($total <= 0 || $filled <= 0): ?>
                                                                <span class="d-inline-flex align-items-center gap-2">
                                                                    <span class="btn btn-xs btn-outline-primary rounded-pill py-0 px-2 extra-small fw-bold shadow-sm text-decoration-none text-center" style="pointer-events:none;">
                                                                        <i class="bi bi-plus-circle-fill me-1"></i> Saisir les notes
                                                                    </span>
                                                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill extra-small px-1 px-md-2 py-0.5 border border-primary border-opacity-10" style="pointer-events:none;" title="À saisir">
                                                                        <i class="bi bi-award-fill me-1"></i>À saisir
                                                                    </span>
                                                                </span>
                                                            <?php else: ?>
                                                                <?php
                                                                    $success = $isComplete && $filled >= $total;
                                                                    // Partiel : affichage d'avancement (orange)
                                                                    $badgeClass = $success
                                                                        ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-10'
                                                                        : 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-10';
                                                                    $icon = $success ? 'bi-check-circle-fill' : 'bi-pencil-fill';
                                                                ?>
                                                                <span class="badge <?= $badgeClass ?> rounded-pill extra-small px-1 px-md-2 py-0.5 d-inline-flex align-items-center gap-1" style="pointer-events:none;">
                                                                    <i class="bi <?= $icon ?>"></i>
                                                                    <?= $filled ?>/<?= $total ?><?= $success ? ' ✓' : '' ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="card-arrow-container">
                                                        <i class="bi bi-arrow-right-short text-primary opacity-50"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php if ($sub['teacher_nom']): ?>
                                    </a>
                                    <?php else: ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Latest Activities Side Column (Admin uniquement) -->
        <?php if (in_array(\App\Core\Session::get('user_role'), ['superadmin', 'admin'])): ?>
        <div class="col-lg-4">
            <h5 class="fw-bold text-main-theme mb-3 px-1 d-flex align-items-center gap-2">
                <i class="bi bi-lightning-charge text-warning"></i>
                <?= __('recent_activity') ?>
            </h5>
            <div class="modern-card border-0 shadow-sm glass-card p-0 overflow-hidden">
                <div class="list-group list-group-flush list-group-theme">
                    <?php foreach (array_slice($recentGrades, 0, 10) as $grade): ?>
                        <div class="list-group-item bg-transparent p-3 border-0 border-bottom border-theme">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="fw-bold text-main-theme small d-flex align-items-center gap-2">
                                    <i class="bi bi-person-circle text-muted opacity-50"></i>
                                    <?= htmlspecialchars($grade['student_nom'] . ' ' . $grade['student_prenom']) ?>
                                </div>
                                <div class="text-primary fw-black d-flex align-items-center gap-1">
                                    <i class="bi bi-award-fill small"></i>
                                    <?= number_format((float) $grade['valeur'], 1) ?>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center small">
                                <span class="text-muted-theme opacity-75 d-flex align-items-center gap-1">
                                    <i class="bi bi-mortarboard small"></i>
                                    <?= htmlspecialchars($grade['class_nom']) ?>
                                </span>
                                <a href="/notes/saisie?class_id=<?= $grade['class_id'] ?>&subject_id=<?= $grade['subject_id'] ?>&periode=<?= urlencode((string) $grade['periode']) ?>"
                                    class="btn-icon-pill p-1 px-2 text-primary bg-primary bg-opacity-10 rounded-pill transition-base">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($recentGrades)): ?>
                        <div class="p-4 text-center text-muted small">
                            <i class="bi bi-inbox fs-4 d-block mb-2 opacity-25"></i>
                            <?= __('no_activity_yet') ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="p-3 bg-theme-input text-center">
                    <a href="/notes/history" class="small fw-bold text-primary text-decoration-none">
                        <i class="bi bi-arrow-right-circle me-1"></i><?= __('full_history') ?>
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
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

    .filter-island:focus-within {
        border-color: var(--primary-color);
        box-shadow: 0 15px 35px -10px rgba(var(--primary-rgb), 0.25);
        transform: translateY(-2px);
    }

    .search-pill {
        border: 1px solid rgba(var(--primary-rgb), 0.1);
        transition: all 0.3s ease;
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

    .grades-workspace {
        --border-theme: rgba(226, 232, 240, 0.5);
    }

    [data-theme="dark"] .grades-workspace {
        --border-theme: rgba(255, 255, 255, 0.05);
    }

    .fw-black { font-weight: 800; }

    .class-header-icon {
        width: 42px;
        height: 42px;
        background: linear-gradient(135deg, var(--primary-color) 0%, color-mix(in srgb, var(--primary-color) 70%, black) 100%);
        color: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        box-shadow: 0 8px 16px rgba(var(--primary-rgb), 0.2);
    }

    .subject-card-compact {
        background: var(--bg-card);
        border-radius: 24px;
        border: 1px solid var(--border-theme) !important;
        display: block;
        text-decoration: none !important;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        position: relative;
        overflow: hidden;
    }

    [data-theme="dark"] .subject-card-compact {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(10px);
        border-color: rgba(255, 255, 255, 0.08) !important;
    }

    .subject-card-glow {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at top right, rgba(var(--primary-rgb), 0.15), transparent 70%);
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    .subject-card-compact:hover {
        transform: translateY(-8px) scale(1.02);
        border-color: var(--primary-color) !important;
        box-shadow: 0 20px 40px rgba(var(--primary-rgb), 0.12);
    }

    .subject-card-compact:hover .subject-card-glow {
        opacity: 1;
    }

    @media (max-width: 991.98px) {
        .filter-island {
            border-radius: 24px;
            padding: 1rem !important;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('subject-search');
        const sections = document.querySelectorAll('.class-section');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const query = this.value.toLowerCase().trim();
                sections.forEach(section => {
                    const className = section.getAttribute('data-class');
                    const items = section.querySelectorAll('.subject-grid-item');
                    let visibleCount = 0;

                    items.forEach(item => {
                        const subjectName = item.getAttribute('data-subject-name');
                        const match = subjectName.includes(query) || className.includes(query);
                        item.style.display = match ? 'block' : 'none';
                        if (match) visibleCount++;
                    });

                    section.style.display = visibleCount > 0 ? 'block' : 'none';
                });
            });
        }
    });
</script>


<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
