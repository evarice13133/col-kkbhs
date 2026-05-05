<?php $title = __('manage_teaching_team') . ($class ? ": " . htmlspecialchars($class['nom']) : ""); ob_start(); ?>

<div class="animate-fade-in container-fluid py-3 px-md-4">
    
    <!-- HEADER & FILTRE STYLE ISLAND -->
    <div class="d-flex justify-content-center mb-5">
        <div class="filter-island px-3 py-2 shadow-lg animate-slide-down" style="min-width: 90%;">
            <div class="d-flex align-items-center gap-3 flex-wrap flex-md-nowrap w-100">
                
                <!-- Titre / Icône -->
                <div class="d-flex align-items-center gap-2 pe-3 border-end border-opacity-10 border-secondary me-2">
                    <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                        style="width: 40px; height: 40px;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="d-none d-md-block">
                        <h6 class="fw-bold m-0 text-main-theme small"><?= __('manage_teaching_team') ?></h6>
                    </div>
                </div>

                <!-- Sélecteur de Classe & Recherche -->
                <div class="flex-grow-1 d-flex gap-2 align-items-center">
                    <select id="classSelector" class="form-select border-0 bg-white bg-opacity-10 shadow-none py-2 text-main rounded-pill px-3" style="max-width: 250px;">
                        <option value=""><?= __('select_class') ?>...</option>
                        <?php foreach ($allClasses as $cl): ?>
                            <option value="<?= $cl['id'] ?>" <?= (int)$id === (int)$cl['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cl['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2 flex-grow-1 d-none d-sm-flex">
                        <span class="input-group-text border-0 bg-transparent text-primary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="teacherSearch" class="form-control border-0 bg-transparent shadow-none py-2 text-main"
                            placeholder="<?= __('search_placeholder_global') ?>...">
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex gap-2 align-items-center ps-2">
                    <?php if ($class): ?>
                        <button type="submit" form="mainTeamForm" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> <?= __('save') ?>
                        </button>
                    <?php endif; ?>
                    <a href="/classes" class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center shadow-sm" 
                       style="width: 40px; height: 40px;" title="<?= __('back') ?>">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if ($msg = App\Core\Session::get('success')): ?>
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars((string) $msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php App\Core\Session::remove('success'); ?>
    <?php endif; ?>

    <?php if ($err = App\Core\Session::get('error')): ?>
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars((string) $err) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php App\Core\Session::remove('error'); ?>
    <?php endif; ?>

    <?php if ($class): ?>
        <form action="/classes/set-main-teacher?id=<?= $class['id'] ?>" method="POST" id="mainTeamForm">
            <input type="hidden" name="csrf_token" value="<?= App\Core\Session::generateCsrfToken() ?>">
            
            <div class="row g-2 g-md-4">
                <!-- Option "Aucun Titulaire" -->
                <div class="col-6 col-sm-6 col-xl-3 searchable-card" data-name="aucun none reset">
                    <div class="subject-card-compact border-theme-dynamic h-100 position-relative">
                        <label class="teacher-selection-card <?= empty($class['main_teacher_id']) ? 'active' : '' ?> h-100 w-100" for="teacher_none">
                            <input type="radio" name="teacher_id" value="" id="teacher_none" class="d-none" <?= empty($class['main_teacher_id']) ? 'checked' : '' ?>>
                            <div class="card-body p-3 d-flex flex-column align-items-center text-center justify-content-center h-100">
                                <div class="avatar-init bg-light text-secondary fw-bold rounded-circle d-flex align-items-center justify-content-center mb-2" style="width: 45px; height: 45px;">
                                    <i class="bi bi-x-lg fs-4"></i>
                                </div>
                                <h6 class="fw-bold mb-0 text-main-theme small"><?= __('no_teacher') ?></h6>
                                <span class="extra-small text-muted"><?= __('reset') ?></span>
                                
                                <div class="selection-indicator mt-2">
                                    <i class="bi bi-check-circle-fill fs-5 text-primary"></i>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <?php if (empty($teachers)): ?>
                    <div class="col-6 col-sm-6 col-xl-9">
                        <div class="subject-card-compact p-4 text-center border-dashed h-100 d-flex flex-column justify-content-center">
                            <i class="bi bi-person-x fs-2 opacity-25 mb-2 d-block"></i>
                            <h6 class="text-muted small fw-bold"><?= __('no_teacher_assigned_to_class') ?></h6>
                            <p class="extra-small text-muted mb-3">Veuillez d'abord affecter des matières à des enseignants pour cette classe.</p>
                            <div>
                                <a href="/teachers" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">
                                     <?= __('assign_teacher') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($teachers as $teacher): 
                        $isMain = (int)$class['main_teacher_id'] === (int)$teacher['id'];
                        $alreadyMain = !empty($teacher['other_classes']);
                    ?>
                        <div class="col-6 col-sm-6 col-xl-3 searchable-card" data-name="<?= strtolower($teacher['nom'] . ' ' . $teacher['prenom']) ?>">
                            <div class="subject-card-compact border-theme-dynamic h-100 position-relative <?= $alreadyMain ? 'already-main' : '' ?>">
                                <label class="teacher-selection-card <?= $isMain ? 'active' : '' ?> h-100 w-100" for="teacher_<?= $teacher['id'] ?>">
                                    <input type="radio" name="teacher_id" value="<?= $teacher['id'] ?>" id="teacher_<?= $teacher['id'] ?>" class="d-none" <?= $isMain ? 'checked' : '' ?>>
                                    
                                    <div class="card-body p-3 h-100 d-flex flex-column position-relative" style="z-index: 1;">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm"
                                                style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                <?= strtoupper(substr($teacher['nom'], 0, 1) . substr($teacher['prenom'], 0, 1)) ?>
                                            </div>
                                            <div class="overflow-hidden">
                                                <h6 class="fw-bold lh-1 text-main-theme m-0 text-truncate name-gradient" style="font-size: 0.8rem;">
                                                    <?= htmlspecialchars($teacher['nom'] . ' ' . $teacher['prenom']) ?>
                                                </h6>
                                            </div>
                                        </div>

                                        <?php if ($alreadyMain): ?>
                                            <div class="mt-auto pt-2 border-top border-opacity-10 border-secondary">
                                                <div class="extra-small text-danger fw-bold d-flex align-items-center gap-1">
                                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                                    <span class="text-truncate" title="<?= htmlspecialchars($teacher['other_classes']) ?>">
                                                        Titulaire : <?= htmlspecialchars($teacher['other_classes']) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="selection-indicator position-absolute top-0 end-0 m-2">
                                            <i class="bi bi-check-circle-fill fs-5 text-primary"></i>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </form>
    <?php else: ?>
        <!-- État Vide : Aucune classe sélectionnée -->
        <div class="d-flex flex-column align-items-center justify-content-center py-5 animate-fade-in">
            <div class="avatar-init bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mb-4"
                style="width: 80px; height: 80px; font-size: 2rem;">
                <i class="bi bi-door-open"></i>
            </div>
            <h4 class="fw-bold text-main-theme"><?= __('select_class_to_start') ?></h4>
            <p class="text-muted small"><?= __('choose_class_hint') ?></p>
        </div>
    <?php endif; ?>
</div>

<style>
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

.search-pill {
    background: rgba(var(--primary-rgb), 0.05);
}

.teacher-selection-card {
    cursor: pointer;
    transition: all 0.2s ease;
    border-radius: 12px;
}

.teacher-selection-card.active {
    background: rgba(var(--bs-primary-rgb), 0.05);
}

.teacher-selection-card.active::before {
    content: '';
    position: absolute;
    inset: 0;
    border: 2px solid var(--bs-primary);
    border-radius: inherit;
    z-index: 2;
    pointer-events: none;
}

.selection-indicator {
    opacity: 0;
    transform: scale(0.5);
    transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.teacher-selection-card.active .selection-indicator {
    opacity: 1;
    transform: scale(1);
}

.already-main {
    border-color: rgba(var(--bs-danger-rgb), 0.2) !important;
}

.extra-small { font-size: 0.65rem; }
.letter-spacing-1 { letter-spacing: 0.5px; }

.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

#classSelector option {
    background: var(--bs-body-bg);
    color: var(--bs-body-color);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const cards = document.querySelectorAll('.teacher-selection-card');
    const searchInput = document.getElementById('teacherSearch');
    const searchableCards = document.querySelectorAll('.searchable-card');
    const classSelector = document.getElementById('classSelector');

    // Gestion du sélecteur de classe
    if (classSelector) {
        classSelector.addEventListener('change', function() {
            const val = this.value;
            if (val) {
                window.location.href = '/classes/manage-team?id=' + val;
            } else {
                window.location.href = '/classes/manage-team';
            }
        });
    }

    cards.forEach(card => {
        card.addEventListener('click', function() {
            cards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            searchableCards.forEach(card => {
                const name = card.getAttribute('data-name');
                if (name.includes(query)) {
                    card.classList.remove('d-none');
                } else {
                    card.classList.add('d-none');
                }
            });
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
