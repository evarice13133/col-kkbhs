<?php 
$title = __('edit_subject'); 
ob_start(); 
?>

<div class="animate-fade-in container-fluid py-2 px-2 px-md-3">
    <!-- Compact Header -->
    <div class="d-flex align-items-center justify-content-between mb-2 mb-md-3 flex-wrap gap-2">
        <div>
            <h2 class="fw-black text-main-theme mb-0 fs-5 fs-md-4"><?= __('edit_subject_title') ?></h2>
            <p class="text-muted-theme small mb-0 d-none d-md-block"><?= h($subject['nom'] ?? '') ?> • Coef: <?= (int)($subject['coefficient'] ?? 1) ?></p>
        </div>
        <a href="/subjects" class="btn btn-sm btn-light-theme rounded-pill px-3 border-theme-light">
            <i class="bi bi-arrow-left me-1"></i> <span class="d-none d-sm-inline"><?= __('back_to_list') ?></span>
        </a>
    </div>

    <form action="/subjects/update?id=<?= $subject['id'] ?>" method="POST" id="editSubjectForm">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">

        <div class="modern-card border-0 shadow-sm overflow-hidden mb-3 mb-md-4">
            <div class="card-body p-3 p-md-4">
                
                <!-- Core Config -->
                <div class="row g-3 g-md-4 mb-4 mb-md-5">
                    <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                        <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1"><?= __('subject_identification') ?></h6>
                    </div>
                    
                    <div class="col-md-5">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('subject_official_name') ?></label>
                        <input type="text" name="nom" class="form-control premium-input" 
                            placeholder="<?= __('subject_name_placeholder') ?>" value="<?= h($subject['nom'] ?? '') ?>" required autofocus>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('subject_group') ?></label>
                        <select name="groupe" class="form-select premium-input">
                            <option value="Groupe 1" <?= ($subject['groupe'] ?? '') === 'Groupe 1' ? 'selected' : '' ?>>Groupe 1 - Matières Littéraires</option>
                            <option value="Groupe 2" <?= ($subject['groupe'] ?? '') === 'Groupe 2' ? 'selected' : '' ?>>Groupe 2 - Matières Scientifiques</option>
                            <option value="Groupe 3" <?= ($subject['groupe'] ?? '') === 'Groupe 3' ? 'selected' : '' ?>>Groupe 3 - Développement Personnel</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('base_coefficient') ?></label>
                        <input type="number" name="coefficient" class="form-control premium-input text-center"
                            value="<?= h($subject['coefficient'] ?? 1) ?>" min="1" required>
                    </div>
                </div>

                <!-- Class Assignment Section -->
                <div class="row g-3 g-md-4 mb-3 mb-md-4">
                    <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-3">
                            <h6 class="fw-black text-success m-0 text-uppercase letter-spacing-1"><?= __('impacted_classes') ?></h6>
                            
                            <div class="d-flex align-items-center gap-2 w-100 w-md-auto">
                                <div class="flex-grow-1" style="max-width: 250px;">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-transparent border-end-0 rounded-start-pill border-theme-light ps-3">
                                            <i class="bi bi-search text-muted-theme"></i>
                                        </span>
                                        <input type="text" id="classSearchInput" class="form-control border-start-0 rounded-end-pill border-theme-light bg-transparent extra-small fw-bold" 
                                               placeholder="<?= __('Rechercher...') ?>">
                                    </div>
                                </div>

                                <div class="form-check form-switch m-0 flex-shrink-0">
                                    <input class="form-check-input" type="checkbox" id="selectAllClasses">
                                    <label class="form-check-label extra-small fw-bold text-muted-theme ms-1 d-none d-sm-inline" for="selectAllClasses"><?= __('all') ?></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="row g-2">
                            <?php 
                            // Sort classes: assigned ones first, then alphabetical
                            usort($classes, function($a, $b) use ($assigned_classes) {
                                $aChecked = in_array($a['id'], $assigned_classes ?? []);
                                $bChecked = in_array($b['id'], $assigned_classes ?? []);
                                if ($aChecked && !$bChecked) return -1;
                                if (!$aChecked && $bChecked) return 1;
                                return strcasecmp((string)$a['nom'], (string)$b['nom']);
                            });
                            foreach($classes as $c): 
                            ?>
                                <?php $isChecked = in_array($c['id'], $assigned_classes ?? []); ?>
                                <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                                    <div class="class-selection-item d-flex align-items-center p-2 rounded-3 border border-theme-light h-100 transition-base mobile-compact">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input border-primary class-checkbox" 
                                                   type="checkbox" name="classes[]" 
                                                   value="<?= $c['id'] ?>" 
                                                   id="class_<?= $c['id'] ?>" 
                                                   <?= $isChecked ? 'checked' : '' ?>>
                                        </div>
                                        <label class="d-flex align-items-center mb-0 ms-2 w-100 py-1" for="class_<?= $c['id'] ?>" style="cursor: pointer;">
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="fw-bold text-main-theme text-truncate" style="font-size: 0.8rem;"><?= h($c['nom']) ?></div>
                                                <div class="extra-small <?= $isChecked ? 'text-success' : 'text-muted-theme opacity-50' ?>">
                                                    <i class="bi <?= $isChecked ? 'bi-check-circle-fill' : 'bi-circle' ?> me-1"></i>
                                                    <?= $isChecked ? __('assigned') : __('not_assigned') ?>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="form-text extra-small text-danger mt-3 opacity-75">
                            <i class="bi bi-info-circle me-1"></i><?= __('impacted_classes_warning') ?>
                        </div>
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="d-flex justify-content-end border-top border-theme-light pt-3 pt-md-4 mt-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 px-md-5 py-2 fw-bold shadow-sm transition-base scale-on-hover w-100 w-md-auto">
                        <i class="bi bi-check-circle-fill me-2"></i> <?= __('validate') ?>
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAllClasses');
    const checkboxes = document.querySelectorAll('.class-checkbox');

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => {
                cb.checked = selectAll.checked;
            });
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            const allChecked = Array.from(checkboxes).every(c => c.checked);
            const noneChecked = Array.from(checkboxes).every(c => !c.checked);
            selectAll.checked = allChecked;
            selectAll.indeterminate = !allChecked && !noneChecked;
        });
    });

    // Instant Search Logic
    const searchInput = document.getElementById('classSearchInput');
    const classItems = document.querySelectorAll('.class-selection-item');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            
            classItems.forEach(item => {
                const label = item.querySelector('.text-truncate').textContent.toLowerCase();
                const container = item.closest('.col-6'); // The grid column wrapper
                
                if (label.includes(query)) {
                    container.classList.remove('d-none');
                } else {
                    container.classList.add('d-none');
                }
            });
        });
    }
});
</script>

<style>
    /* Styles pour améliorer la présentation mobile */
    @media (max-width: 767.98px) {
        .mobile-compact {
            padding: 0.5rem !important;
        }
        
        .mobile-compact .form-check-input {
            width: 1.1em;
            height: 1.1em;
            margin-top: 0.1em;
        }
        
        .mobile-compact label {
            font-size: 0.75rem;
        }
        
        .mobile-compact .text-truncate {
            max-width: 80px !important;
        }
        
        .extra-small {
            font-size: 0.65rem;
        }
        
        /* Réduire les espacements entre les éléments de formulaire */
        .form-label {
            margin-bottom: 0.25rem;
            font-size: 0.7rem;
        }
        
        .premium-input {
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
        }
        
        /* Optimiser le switch sur mobile */
        .form-check-input[type="checkbox"] {
            width: 2.5em;
            height: 1.3em;
        }
    }
    
    /* Styles pour les transitions */
    .transition-base {
        transition: all 0.2s ease;
    }
    
    .scale-on-hover:hover {
        transform: scale(1.02);
    }
    
    /* Thème sombre pour le formulaire */
    [data-theme="dark"] .modern-card {
        background: rgba(30, 30, 45, 0.6);
        border-color: rgba(255, 255, 255, 0.08);
    }
    
    [data-theme="dark"] .border-theme-light {
        border-color: rgba(255, 255, 255, 0.1) !important;
    }
    
    [data-theme="dark"] .premium-input {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.1);
        color: #ffffff;
    }
    
    [data-theme="dark"] .premium-input:focus {
        background: rgba(255, 255, 255, 0.08);
        border-color: var(--primary-color);
        color: #ffffff;
    }
    
    [data-theme="dark"] .class-selection-item:hover {
        background: rgba(255, 255, 255, 0.05);
    }
</style>

<?php 
$content = ob_get_clean(); 
include __DIR__ . '/../templates/layout.php'; 
?>
