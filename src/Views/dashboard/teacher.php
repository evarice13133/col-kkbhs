<?php
$title = __('dashboard');
// 
if (!function_exists('nm_level_class')) {
    function nm_level_class($label)
    {
        $map = [
            'Excellent' => 'level-excellent',
            'Bon' => 'level-bon',
            'Moyen' => 'level-moyen',
            'Faible' => 'level-faible',
            'A demarrer' => 'level-ademarrer',
        ];

        $lang = \App\Core\Session::get('app_lang', 'fr');
        if ($lang === 'en') {
            $transMap = [
                'Excellent' => 'Excellent',
                'Bon' => 'Good',
                'Moyen' => 'Average',
                'Faible' => 'Weak',
                'A demarrer' => 'To start',
            ];
            $label = $transMap[$label] ?? $label;
        }

        return $map[$label] ?? 'level-ademarrer';
    }
}

if (!function_exists('nm_evaluation_short_label')) {
    function nm_evaluation_short_label($label)
    {
        if (preg_match('/Trimestre\s+(\d+)\s*-\s*Sequence\s+(\d+)/i', (string) $label, $matches)) {
            return 'T' . $matches[1] . ' S' . $matches[2];
        }
        return (string) $label;
    }
}

ob_start();
?>

<div class="animate-fade-in teacher-analytics container-fluid py-4">

    <!-- Header & Quick Actions Banner -->
    <div class="modern-card border-0 shadow-sm p-4 mb-4" style="background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.06) 0%, rgba(var(--primary-rgb), 0.02) 100%); border-radius: 16px;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle shadow-sm">
                    <i class="bi bi-person-workspace fs-3"></i>
                </div>
                <div>
                    <h4 class="fw-black text-main-theme m-0" style="font-family: 'Outfit', sans-serif; letter-spacing: -0.02em;"><?= __('dashboard_teacher_title') ?></h4>
                    <p class="text-muted-theme small mb-0"><?= __('dashboard_teacher_subtitle') ?></p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <?php if (!empty($has_lmd_classes)): ?>
                    <a href="/timetables"
                        class="btn btn-outline-info rounded-pill px-3 py-2 fw-bold shadow-sm scale-on-hover d-flex align-items-center gap-2"
                        title="<?= __('dashboard_teacher_my_schedule') ?>">
                        <i class="bi bi-calendar3-week"></i> <?= __('dashboard_teacher_my_schedule') ?>
                    </a>
                <?php endif; ?>
                <a href="/students"
                    class="btn btn-outline-primary rounded-pill px-3 py-2 fw-bold shadow-sm scale-on-hover d-flex align-items-center gap-2">
                    <i class="bi bi-people"></i> <?= __('my_students') ?? 'Mes Élèves' ?>
                </a>
                <a href="/notes"
                    class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm scale-on-hover d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square"></i> <?= __('enter_marks') ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Section KPI Enseignant : SaaS/ERP Modern style -->
    <div class="row g-3 g-md-4 mb-4">
        <!-- Classes Affectées -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-primary shadow-sm rounded-4 p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-value fs-2 fw-black" data-count-up="<?= (int) $stats_classes ?>"><?= $stats_classes ?></div>
                        <div class="kpi-label text-muted extra-small text-uppercase fw-bold mt-1"><?= __('assigned_classes_count') ?></div>
                    </div>
                    <div class="erp-icon-box p-3 rounded-3 bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-door-open-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Disciplines Enseignées -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-info shadow-sm rounded-4 p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-value fs-2 fw-black text-info" data-count-up="<?= (int) $stats_subjects ?>"><?= $stats_subjects ?></div>
                        <div class="kpi-label text-muted extra-small text-uppercase fw-bold mt-1"><?= __('disciplines_taught') ?></div>
                    </div>
                    <div class="erp-icon-box p-3 rounded-3 bg-info bg-opacity-10 text-info">
                        <i class="bi bi-journal-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Saisies Confirmées -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-success shadow-sm rounded-4 p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-value fs-2 fw-black text-success" data-count-up="<?= (int) $stats_filled ?>"><?= $stats_filled ?></div>
                        <div class="kpi-label text-muted extra-small text-uppercase fw-bold mt-1"><?= __('confirmed_entries') ?></div>
                    </div>
                    <div class="erp-icon-box p-3 rounded-3 bg-success bg-opacity-10 text-success">
                        <i class="bi bi-check-all fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Progression Globale -->
        <div class="col-6 col-md-3">
            <div class="erp-stat-card card-warning shadow-sm rounded-4 p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-value fs-2 fw-black text-warning" data-count-up="<?= (int) $stats_progress ?>" data-suffix="%"><?= $stats_progress ?>%</div>
                        <div class="kpi-label text-muted extra-small text-uppercase fw-bold mt-1"><?= __('global_progress') ?></div>
                    </div>
                    <div class="erp-icon-box p-3 rounded-3 bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-lightning-charge-fill fs-4"></i>
                    </div>
                </div>
                <div class="progress mt-2" style="height: 5px; border-radius: 10px; background: rgba(var(--primary-rgb), 0.08);">
                    <div class="progress-bar bg-warning shadow-sm" style="width: <?= $stats_progress ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Principale : Mes Affectations et Accès Rapides -->
    <?php if (!empty($teacherAssignments)): ?>
        <div class="modern-card border-0 shadow-sm rounded-4 mb-4 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0 text-main-theme d-flex align-items-center gap-2 fs-6">
                    <i class="bi bi-briefcase text-primary"></i> <?= __('dashboard_teacher_my_assignments') ?>
                </h5>
                <a href="/notes" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                    <?= __('dashboard_teacher_all_my_entries') ?>
                </a>
            </div>
            <div class="row g-3">
                <?php foreach ($teacherAssignments as $assign): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="p-3 border rounded-3 bg-white bg-opacity-5 d-flex align-items-center justify-content-between gap-2 shadow-xs hover-shadow transition-all">
                            <div>
                                <div class="fw-bold text-main-theme" style="font-size: 0.9rem;"><?= htmlspecialchars($assign['subject_nom'] ?? 'Matière') ?></div>
                                <div class="extra-small text-muted d-flex align-items-center gap-1 mt-1">
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-0-5 extra-small">
                                        <i class="bi bi-door-open me-1"></i><?= htmlspecialchars($assign['class_nom']) ?>
                                    </span>
                                </div>
                            </div>
                            <a href="/notes/saisie?class_id=<?= $assign['class_id'] ?>&subject_id=<?= $assign['subject_id'] ?>" 
                               class="btn btn-sm btn-primary rounded-pill px-3 shadow-xs scale-on-hover flex-shrink-0 d-flex align-items-center gap-1">
                                <i class="bi bi-pencil-square"></i> <?= __('dashboard_teacher_enter') ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Compétences Management Section -->
    <div class="modern-card border-0 shadow-sm rounded-4 mb-4 p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold m-0 text-main-theme d-flex align-items-center gap-2 fs-6">
                <i class="bi bi-list-check text-info"></i> <?= __('competencies_management') ?? 'Gestion des Compétences' ?>
            </h5>
        </div>
        
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">
                    <?= __('select_class') ?? 'Sélectionner une classe' ?>
                </label>
                <select id="competencyClassSelect" class="form-select premium-input">
                    <option value=""><?= __('select_class_placeholder') ?? 'Choisir une classe...' ?></option>
                    <?php 
                    $uniqueClasses = [];
                    foreach ($teacherAssignments as $assign) {
                        $key = $assign['class_id'];
                        if (!isset($uniqueClasses[$key])) {
                            $uniqueClasses[$key] = $assign['class_nom'];
                        }
                    }
                    foreach ($uniqueClasses as $classId => $className): ?>
                        <option value="<?= $classId ?>"><?= h($className) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1">
                    <?= __('select_subject') ?? 'Sélectionner une matière' ?>
                </label>
                <select id="competencySubjectSelect" class="form-select premium-input" disabled>
                    <option value=""><?= __('select_subject_placeholder') ?? 'Choisir une matière...' ?></option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button id="loadCompetenciesBtn" class="btn btn-info rounded-pill px-4 py-2 fw-bold shadow-sm w-100" disabled>
                    <i class="bi bi-arrow-clockwise me-2"></i><?= __('load_competencies') ?? 'Charger les compétences' ?>
                </button>
            </div>
        </div>

        <div id="competenciesManagementArea" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold m-0 text-info extra-small text-uppercase">
                    <?= __('subject_competencies') ?? 'Compétences de la matière' ?>
                </h6>
                <button id="addCompetencyBtn" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold">
                    <i class="bi bi-plus-circle me-1"></i><?= __('add_competency') ?? 'Ajouter' ?>
                </button>
            </div>
            
            <div id="competenciesList" class="row g-2 mb-3">
                <!-- Competencies will be loaded here -->
            </div>

            <div id="noCompetenciesMessage" class="text-center py-4 text-muted border border-dashed rounded-4" style="display: none;">
                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                <?= __('no_competencies_defined') ?? 'Aucune compétence définie pour cette matière' ?>
            </div>
        </div>
    </div>

    <!-- Pédagogie : Séquences (7/12) + Progression par Classe (5/12) -->
    <div class="row g-4 mb-5">
        <div class="col-xl-7">
            <div class="registry-card h-100 border-0 shadow-sm rounded-4">
                <div class="p-4 border-bottom border-light border-opacity-10">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold m-0 text-main fs-6"><?= __('active_sequences_state') ?></h5>
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1-5 fw-bold extra-small">
                            <?= count($evaluationStats) ?> <?= __('sequences') ?>
                        </span>
                    </div>
                </div>
                <div class="p-4">
                    <div class="row g-4">
                        <?php if (empty($evaluationStats)): ?>
                            <div class="col-12 text-center py-5 text-muted border border-dashed rounded-4 opacity-50">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-25"></i>
                                <?= __('no_active_sequence') ?>
                            </div>
                        <?php else: ?>
                            <?php foreach ($evaluationStats as $ev): ?>
                                <div class="col-12 col-sm-6">
                                    <div class="p-3 border border-light border-opacity-10 rounded-4 bg-transparent transition-base h-100 scale-on-hover shadow-xs">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-main small text-truncate"><?= h($ev['label']) ?></span>
                                            <span class="level-badge <?= nm_level_class($ev['level_label'] ?? '') ?> d-none d-md-inline-block"><?= __($ev['level_label'] ?? 'A demarrer') ?></span>
                                        </div>
                                        <div class="d-flex align-items-end justify-content-between mb-2">
                                            <div class="fs-3 fw-black text-primary lh-1" data-count-up="<?= (int) $ev['progress_percent'] ?>" data-suffix="%">
                                                <?= $ev['progress_percent'] ?>%</div>
                                            <div class="extra-small registry-text-muted fw-semibold text-muted d-none d-md-block">
                                                <?= $ev['filled_count'] ?> / <?= $ev['expected_count'] ?></div>
                                        </div>
                                        <div class="progress" style="height: 6px; border-radius: 10px; background: var(--border-color);">
                                            <div class="progress-bar bg-primary shadow-sm" style="width: <?= $ev['progress_percent'] ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progression par Classe : Utilisation de registry-table -->
        <div class="col-xl-5">
            <div class="registry-card h-100 border-0 shadow-sm overflow-hidden rounded-4">
                <div class="p-4 border-bottom border-light border-opacity-10">
                    <h5 class="fw-bold m-0 text-main fs-6"><?= __('progress_by_class') ?></h5>
                </div>
                <div class="table-responsive">
                    <table class="registry-table mb-0">
                        <thead>
                            <tr class="bg-transparent">
                                <th class="ps-4 py-3 text-muted extra-small text-uppercase"><?= __('class') ?></th>
                                <th class="text-end pe-4 py-3 text-muted extra-small text-uppercase"><?= __('progress') ?></th>
                            </tr>
                        </thead>
                        <tbody class="bg-transparent">
                            <?php foreach ($classProgress as $cp): ?>
                                <tr class="bg-transparent border-bottom border-light border-opacity-10">
                                    <td class="ps-4 py-3 bg-transparent">
                                        <div class="fw-bold text-main-theme" style="font-size: 0.88rem;"><?= h($cp['class_nom']) ?></div>
                                        <div class="extra-small text-muted"><?= $cp['student_count'] ?> <?= __('students_short') ?></div>
                                    </td>
                                    <td class="text-end pe-4 bg-transparent">
                                        <div class="d-flex align-items-center justify-content-end gap-3">
                                            <div class="text-end" style="min-width: 90px;">
                                                <div class="fw-bold text-primary mb-1 extra-small"><?= $cp['progress_percent'] ?>%</div>
                                                <div class="progress ms-auto" style="height: 4px; width: 70px; border-radius: 10px; background: var(--border-color);">
                                                    <div class="progress-bar bg-primary shadow-sm" style="width: <?= $cp['progress_percent'] ?>%"></div>
                                                </div>
                                            </div>
                                            <span class="level-badge <?= nm_level_class($cp['level_label']) ?>"><?= __($cp['level_label']) ?></span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
        </div>
    </div>
</div>

<!-- Support WhatsApp Floating Widget -->

<div id="whatsapp-support" class="wa-float-container">
    <button class="wa-close-toggle" onclick="dismissSupport()" title="<?= __('dashboard_teacher_hide_support') ?>">
        <i class="bi bi-x"></i>
    </button>
    <a href="https://wa.me/237679164801?text=<?= rawurlencode(__('dashboard_teacher_whatsapp_message')) ?>"
        target="_blank" class="wa-fab shadow-lg">
        <div class="wa-icon-box">
            <i class="bi bi-whatsapp"></i>
        </div>
        <span class="wa-label-text"><?= __('technical_support') ?></span>
    </a>
</div>

<script>
    function dismissSupport() {
        const support = document.getElementById('whatsapp-support');
        support.style.opacity = '0';
        support.style.transform = 'translateY(20px) scale(0.9)';
        setTimeout(() => support.remove(), 300);
        // Optionnel: Persister avec localStorage
        localStorage.setItem('nm_wa_support_dismissed', 'true');
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Restaurer l'état
        if (localStorage.getItem('nm_wa_support_dismissed') === 'true') {
            document.getElementById('whatsapp-support')?.remove();
        }

        // Competency Management JavaScript
        const competencyClassSelect = document.getElementById('competencyClassSelect');
        const competencySubjectSelect = document.getElementById('competencySubjectSelect');
        const loadCompetenciesBtn = document.getElementById('loadCompetenciesBtn');
        const competenciesManagementArea = document.getElementById('competenciesManagementArea');
        const competenciesList = document.getElementById('competenciesList');
        const noCompetenciesMessage = document.getElementById('noCompetenciesMessage');
        const addCompetencyBtn = document.getElementById('addCompetencyBtn');

        // Teacher assignments data from PHP
        const teacherAssignments = <?= json_encode($teacherAssignments ?? []) ?>;

        // Load subjects when class is selected
        if (competencyClassSelect) {
            competencyClassSelect.addEventListener('change', function() {
                const classId = this.value;
                
                // Reset subject select
                competencySubjectSelect.innerHTML = '<option value=""><?= __('select_subject_placeholder') ?? 'Choisir une matière...' ?></option>';
                competencySubjectSelect.disabled = true;
                loadCompetenciesBtn.disabled = true;
                competenciesManagementArea.style.display = 'none';

                if (classId) {
                    // Filter subjects for this class
                    const subjects = teacherAssignments.filter(a => a.class_id == classId);
                    const uniqueSubjects = {};
                    subjects.forEach(s => {
                        if (!uniqueSubjects[s.subject_id]) {
                            uniqueSubjects[s.subject_id] = s.subject_nom;
                        }
                    });

                    Object.entries(uniqueSubjects).forEach(([id, name]) => {
                        const option = document.createElement('option');
                        option.value = id;
                        option.textContent = name;
                        competencySubjectSelect.appendChild(option);
                    });

                    competencySubjectSelect.disabled = false;
                }
            });
        }

        // Load competencies when button is clicked
        if (loadCompetenciesBtn) {
            loadCompetenciesBtn.addEventListener('click', function() {
                const classId = competencyClassSelect.value;
                const subjectId = competencySubjectSelect.value;

                if (!classId || !subjectId) return;

                this.disabled = true;
                this.innerHTML = '<i class="bi bi-arrow-clockwise me-2 spin"></i><?= __('loading') ?? 'Chargement...' ?>';

                fetch(`/competencies/api/by-subject?subject_id=${subjectId}&class_id=${classId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            renderCompetencies(data.competencies);
                            competenciesManagementArea.style.display = 'block';
                        } else {
                            alert(data.error || '<?= __('error_loading_competencies') ?? 'Erreur lors du chargement des compétences' ?>');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('<?= __('error_loading_competencies') ?? 'Erreur lors du chargement des compétences' ?>');
                    })
                    .finally(() => {
                        this.disabled = false;
                        this.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i><?= __('load_competencies') ?? 'Charger les compétences' ?>';
                    });
            });
        }

        // Render competencies list
        function renderCompetencies(competencies) {
            competenciesList.innerHTML = '';
            
            if (competencies.length === 0) {
                noCompetenciesMessage.style.display = 'block';
                return;
            }

            noCompetenciesMessage.style.display = 'none';

            competencies.forEach((comp, index) => {
                const col = document.createElement('div');
                col.className = 'col-md-6 col-lg-4';
                col.innerHTML = `
                    <div class="p-2 border border-info border-opacity-25 rounded-3 bg-info bg-opacity-5 d-flex align-items-center justify-content-between gap-2">
                        <div class="flex-grow-1">
                            <div class="fw-bold text-info small text-truncate">${comp.libelle}</div>
                            ${comp.description ? `<div class="extra-small text-muted text-truncate">${comp.description}</div>` : ''}
                        </div>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-info rounded-circle p-1" onclick="editCompetency(${comp.id}, '${comp.libelle.replace(/'/g, "\\'")}', '${(comp.description || '').replace(/'/g, "\\'")}')" title="<?= __('edit') ?? 'Modifier' ?>">
                                <i class="bi bi-pencil fs-6"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger rounded-circle p-1" onclick="deleteCompetency(${comp.id})" title="<?= __('delete') ?? 'Supprimer' ?>">
                                <i class="bi bi-trash fs-6"></i>
                            </button>
                        </div>
                    </div>
                `;
                competenciesList.appendChild(col);
            });
        }

        // Add new competency
        if (addCompetencyBtn) {
            addCompetencyBtn.addEventListener('click', function() {
                const libelle = prompt('<?= __('enter_competency_name') ?? 'Entrez le nom de la compétence' ?>:');
                if (!libelle) return;

                const description = prompt('<?= __('enter_competency_description_optional') ?? 'Description (optionnel)' ?? 'Description (optionnel):' ?>:') || '';

                const classId = competencyClassSelect.value;
                const subjectId = competencySubjectSelect.value;

                const formData = new FormData();
                formData.append('subject_id', subjectId);
                formData.append('class_id', classId);
                formData.append('libelle', libelle);
                formData.append('description', description);

                fetch('/competencies/api/create', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadCompetenciesBtn.click();
                    } else {
                        alert(data.error || '<?= __('error_creating_competency') ?? 'Erreur lors de la création' ?>');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('<?= __('error_creating_competency') ?? 'Erreur lors de la création' ?>');
                });
            });
        }

        // Edit competency (global function)
        window.editCompetency = function(id, currentLibelle, currentDescription) {
            const libelle = prompt('<?= __('enter_competency_name') ?? 'Entrez le nom de la compétence' ?>:', currentLibelle);
            if (libelle === null) return;

            const description = prompt('<?= __('enter_competency_description_optional') ?? 'Description (optionnel):' ?>', currentDescription) || '';

            const formData = new FormData();
            formData.append('competency_id', id);
            formData.append('libelle', libelle);
            formData.append('description', description);

            fetch('/competencies/api/update', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCompetenciesBtn.click();
                } else {
                    alert(data.error || '<?= __('error_updating_competency') ?? 'Erreur lors de la mise à jour' ?>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?= __('error_updating_competency') ?? 'Erreur lors de la mise à jour' ?>');
            });
        };

        // Delete competency (global function)
        window.deleteCompetency = function(id) {
            if (!confirm('<?= __('confirm_delete_competency') ?? 'Êtes-vous sûr de vouloir supprimer cette compétence ?' ?>')) return;

            const formData = new FormData();
            formData.append('competency_id', id);

            fetch('/competencies/api/delete', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCompetenciesBtn.click();
                } else {
                    alert(data.error || '<?= __('error_deleting_competency') ?? 'Erreur lors de la suppression' ?>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?= __('error_deleting_competency') ?? 'Erreur lors de la suppression' ?>');
            });
        };

        document.querySelectorAll('[data-count-up]').forEach((element, index) => {
            const target = Number(element.dataset.countUp || '0');
            const suffix = element.dataset.suffix || '';
            if (!Number.isFinite(target)) return;

            const duration = 800;
            const start = performance.now();

            const tick = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                const easeProgress = 1 - Math.pow(1 - progress, 5);
                const value = Math.round(target * easeProgress);
                element.childNodes[0].nodeValue = `${value}${suffix}`;
                if (progress < 1) requestAnimationFrame(tick);
            };

            setTimeout(() => requestAnimationFrame(tick), 200 + (index * 50));
        });
    });
</script>

<style>


    .wa-float-container {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 2000;
        display: flex;
        align-items: center;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .wa-close-toggle {
        position: absolute;
        top: -8px;
        left: -8px;
        width: 24px;
        height: 24px;
        background: #fff;
        border: 1px solid #eee;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        z-index: 2001;
        opacity: 0;
        transform: scale(0.5);
        transition: all 0.3s ease;
    }

    .wa-float-container:hover .wa-close-toggle {
        opacity: 1;
        transform: scale(1);
    }

    .wa-fab {
        background: #25D366;
        color: white;
        text-decoration: none;
        display: flex;
        align-items: center;
        height: 56px;
        border-radius: 28px;
        width: 56px;
        /* Circle by default */
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 10px 30px rgba(37, 211, 102, 0.3);
    }

    .wa-fab:hover {
        width: 220px;
        /* Expand on hover */
        background: #20BA5A;
        color: white;
    }

    .wa-icon-box {
        min-width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }

    .wa-label-text {
        white-space: nowrap;
        font-weight: 700;
        padding-right: 25px;
        opacity: 0;
        transform: translateX(-10px);
        transition: all 0.3s ease;
    }

    .wa-fab:hover .wa-label-text {
        opacity: 1;
        transform: translateX(0);
    }

    .fw-black {
        font-weight: 800;
    }

    .scale-on-hover {
        transition: transform 0.2s ease;
    }

    .scale-on-hover:hover {
        transform: scale(1.05);
    }

    /* Animations */
    .animate-slide-down {
        animation: slideDown 0.6s cubic-bezier(0.23, 1, 0.32, 1);
    }

    @keyframes slideDown {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes fade-in-up {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fade-in-up 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>