<?php
$levelName = $levelRow ? h($levelRow['libelle_fr'] ?? $levelRow['code']) : 'Toutes les classes';
$cycleName = $cycleRow ? h($cycleRow['nom']) : 'Cycle';
$weekLibelle = $weekRow ? h($weekRow['libelle']) : 'Semaine';
$title = "Emploi du temps - Niveau " . $levelName . " (" . $weekLibelle . ")";
ob_start();
$isSuperAdmin = \App\Core\Session::get('user_role') === 'superadmin';
$classes = $gridData['classes'];
$days = $gridData['days'];
$slots = $gridData['slots'];
$matrix = $gridData['matrix'];
$timetablesByClass = $gridData['timetablesByClass'];
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">
    <!-- Header -->
    <div class="modern-card border-0 shadow-sm p-4 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 rounded-pill fw-medium" style="font-size: 0.75rem;">
                        <i class="bi bi-mortarboard-fill me-1"></i><?= __('timetables_type') ?> Supérieur LMD
                    </span>
                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2.5 py-1 rounded-pill fw-medium" style="font-size: 0.75rem;">
                        <i class="bi bi-diagram-3-fill me-1"></i><?= __('timetables_cycle') ?> <?= $cycleName ?>
                    </span>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill fw-medium" style="font-size: 0.75rem;">
                        <i class="bi bi-layers-fill me-1"></i><?= __('level') ?? 'Niveau' ?>: <?= $levelName ?>
                    </span>
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2.5 py-1 rounded-pill fw-medium" style="font-size: 0.75rem;">
                        <i class="bi bi-calendar-event me-1"></i><?= __('timetables_week') ?> <?= $weekLibelle ?>
                    </span>
                </div>
                <h2 class="fw-black text-main-theme mb-0 fs-3"><?= __('timetables_grid_header_title') ?> - <?= $levelName ?></h2>
                <p class="text-muted small mb-0">
                    <?= __('timetables_period_from') ?> <?= date('d/m/Y', strtotime($weekRow['date_debut'])) ?> <?= __('timetables_to') ?> <?= date('d/m/Y', strtotime($weekRow['date_fin'])) ?> 
                    | <?= count($classes) ?> <?= __('timetables_all_classes') ?>
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2 align-items-center">
                <?php if ($canEdit): ?>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-2 fw-bold shadow-sm" onclick="toggleQuickAssignPalette()">
                        <i class="bi bi-layout-sidebar-reverse me-1"></i>Palette d'affectation (Glisser-Déposer)
                    </button>
                <?php endif; ?>
                <a href="/timetables/wizard" class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-bold shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i><?= __('timetables_new_wizard') ?>
                </a>
                <a href="/timetables" class="btn btn-sm btn-light-theme rounded-pill px-3 py-2 fw-semibold border-theme-light shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i><?= __('back') ?? 'Retour' ?>
                </a>

                <button type="button" onclick="window.print()" class="btn btn-sm btn-action-modern text-primary border px-3 rounded-pill fw-semibold" title="<?= __('print') ?? 'Imprimer' ?>">
                    <i class="bi bi-printer-fill me-1"></i><?= __('print') ?? 'Imprimer' ?>
                </button>
                <a href="/timetables/pdf?cycle_id=<?= $cycleId ?>&level_id=<?= $levelId ?>&week_id=<?= $weekId ?>&mode=download" class="btn btn-sm btn-danger rounded-pill px-3 py-2 fw-bold shadow-sm">
                    <i class="bi bi-file-earmark-pdf-fill me-1"></i><?= __('timetables_pdf_export') ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Notification Conflits Globales -->
    <?php if (!empty($gridConflicts)): ?>
        <div class="alert alert-danger rounded-4 shadow-sm border-2 border-danger d-flex align-items-center mb-4">
            <i class="bi bi-exclamation-octagon-fill fs-2 me-3 text-danger flex-shrink-0"></i>
            <div>
                <h6 class="fw-bold mb-1">Conflits détectés dans l'emploi du temps (<?= count($gridConflicts) ?>)</h6>
                <div class="small">
                    Des enseignants ou des salles sont affectés simultanément à des matières différentes au même créneau. (Remarque : Un cours identique au même créneau pour plusieurs classes est autorisé en cours mutualisé/tronc commun).
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Volet d'Affectation Rapide (Palette Glisser-Déposer Escamotable) -->
    <?php if ($canEdit): ?>
        <div id="quickAssignPalette" class="modern-card border-0 shadow-sm p-3 mb-4 d-none">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar-init bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-hand-index-thumb-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-main-theme mb-0">Palette d'Affectation Rapide (Glisser-Déposer)</h6>
                        <span class="text-muted extra-small">Glissez une matière directement dans la cellule souhaitée pour programmer le cours</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <input type="text" id="searchPaletteSubject" class="form-control form-control-sm rounded-pill px-3" placeholder="Rechercher une matière..." onkeyup="filterPaletteSubjects(this.value)">
                    <button type="button" class="btn-close" onclick="toggleQuickAssignPalette()"></button>
                </div>
            </div>
            
            <div class="d-flex flex-wrap gap-2 overflow-auto py-1" id="paletteSubjectsContainer" style="max-height: 120px;">
                <?php foreach ($gridData['subjects'] as $sub): ?>
                    <?php $sColor = !empty($sub['couleur_hex']) ? $sub['couleur_hex'] : '#3b82f6'; ?>
                    <div class="palette-subject-chip badge px-3 py-2 rounded-pill d-flex align-items-center gap-2 cursor-grab shadow-xs text-start transition-all"
                         style="background-color: <?= $sColor ?>1a; color: <?= $sColor ?>; border: 1.5px solid <?= $sColor ?>50;"
                         draggable="true"
                         ondragstart="handlePaletteDragStart(event, <?= json_encode(['id' => $sub['id'], 'nom' => $sub['nom'], 'code' => $sub['code'] ?? '', 'color' => $sColor]) ?>)">
                        <i class="bi bi-grip-vertical opacity-50"></i>
                        <div>
                            <span class="fw-bold d-block text-truncate" style="max-width: 160px;"><?= h($sub['nom']) ?></span>
                            <span class="extra-small font-normal opacity-75"><?= h($sub['code'] ?? 'UV') ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Grille Multi-Classes par Niveau -->
    <div class="modern-card border-0 shadow-sm overflow-hidden mb-4">
        <div class="timetable-grid-wrapper overflow-auto" style="max-height: calc(100vh - 220px); min-height: 500px;">
            <table class="table-modern align-middle mb-0 grid-table text-center" style="min-width: 950px; border-collapse: separate; border-spacing: 0;">
                <thead>
                    <tr>
                        <!-- Coin Haut-Gauche Fixe 1: Jours -->
                        <th style="width: 100px; min-width: 100px;" class="py-3 text-center fw-bold sticky-top-left-1">
                            <i class="bi bi-calendar3 me-1"></i>Jours
                        </th>
                        <!-- Coin Haut-Gauche Fixe 2: Horaires -->
                        <th style="width: 130px; min-width: 130px;" class="py-3 text-center fw-bold sticky-top-left-2">
                            <i class="bi bi-clock-history me-1"></i>Horaires
                        </th>
                        <!-- Colonnes Fixes en Haut: Classes -->
                        <?php foreach ($classes as $cls): ?>
                            <?php $ttObj = $timetablesByClass[$cls['id']] ?? null; ?>
                            <th class="py-3 px-3 text-center text-uppercase fw-black text-primary sticky-header-col" style="min-width: 200px;">
                                <div class="d-flex align-items-center justify-content-center gap-1.5 mb-1">
                                    <span class="fs-6 text-truncate" title="<?= h($cls['nom']) ?>"><?= h($cls['nom']) ?></span>
                                    <?php if ($isSuperAdmin && $ttObj): ?>
                                        <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-1 text-decoration-none" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteTimetableModal" 
                                                data-timetable-id="<?= $ttObj['id'] ?>" 
                                                data-timetable-title="<?= h($ttObj['titre'] ?? ('Emploi du temps - ' . $cls['nom'])) ?>" 
                                                title="Supprimer cet emploi du temps">
                                            <i class="bi bi-trash3-fill fs-6"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill extra-small font-normal fw-medium px-2 py-0.5">
                                    <i class="bi bi-people-fill me-1"></i><?= (int)$cls['effectif'] ?> élèves
                                </span>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($days as $dayIndex => $day): ?>
                        <?php foreach ($slots as $slotIndex => $slot): ?>
                            <?php $isPause = ($slot['type_creneau'] === 'pause'); ?>
                            
                            <tr class="<?= $isPause ? 'bg-pause-row' : '' ?>">
                                <!-- Colonne Jour (Sticky Left 1) -->
                                <?php if ($slotIndex === 0): ?>
                                    <td rowspan="<?= count($slots) ?>" class="fw-black text-uppercase align-middle day-cell sticky-col-1">
                                        <div class="fs-6 text-primary tracking-wider text-rotate-270-md py-2"><?= $day ?></div>
                                    </td>
                                <?php endif; ?>

                                <!-- Colonne Horaires (Sticky Left 2) -->
                                <td class="fw-bold py-2.5 slot-time-cell sticky-col-2">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="small fw-black text-main-theme font-monospace" style="letter-spacing: -0.2px;">
                                            <?= substr($slot['heure_debut'], 0, 5) ?> <span class="text-muted fw-normal">à</span> <?= substr($slot['heure_fin'], 0, 5) ?>
                                        </div>
                                        <?php if ($isPause): ?>
                                            <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 rounded-pill extra-small fw-bold mt-1 px-2 py-0.5">
                                                <i class="bi bi-cup-hot-fill me-1"></i>PAUSE
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-20 rounded-pill extra-small fw-medium mt-1">
                                                <i class="bi bi-hourglass-split me-1"></i><?= (int)$slot['duree_minutes'] ?>m
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Ligne de Pause (Bandeau Sérénité sur toutes les colonnes) -->
                                <?php if ($isPause): ?>
                                    <td colspan="<?= count($classes) ?>" class="p-3 cell-pause-full align-middle">
                                        <div class="d-flex align-items-center justify-content-center gap-3 py-1">
                                            <div class="pause-icon-circle rounded-circle bg-success text-white d-flex align-items-center justify-content-center shadow-xs" style="width: 32px; height: 32px;">
                                                <i class="bi bi-cup-hot-fill fs-6"></i>
                                            </div>
                                            <div class="text-center">
                                                <span class="fw-black text-uppercase tracking-wider fs-6 text-success me-2">PAUSE & INTERVALLE</span>
                                                <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30 rounded-pill font-monospace fw-bold">
                                                    <?= substr($slot['heure_debut'], 0, 5) ?> - <?= substr($slot['heure_fin'], 0, 5) ?> (<?= (int)$slot['duree_minutes'] ?> min)
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                <?php else: ?>
                                    <!-- Colonnes par Classe -->
                                    <?php foreach ($classes as $cls): ?>
                                        <?php 
                                        $classId = (int)$cls['id'];
                                        $entry = $matrix[$day][$slot['id']][$classId] ?? null;
                                        $cellKey = $day . '_' . $slot['id'] . '_' . $classId;
                                        $hasConflict = isset($gridConflicts[$cellKey]);
                                        $conflictMsgs = $gridConflicts[$cellKey] ?? [];
                                        $timetableObj = $timetablesByClass[$classId] ?? null;
                                        $timetableId = $timetableObj ? (int)$timetableObj['id'] : 0;
                                        $subjectColor = !empty($entry['couleur_hex']) ? $entry['couleur_hex'] : '#3b82f6';
                                        ?>
                                        
                                         <td class="p-2 grid-cell <?= $hasConflict ? 'conflict-cell' : '' ?>"
                                            data-day="<?= $day ?>"
                                            data-slot-id="<?= $slot['id'] ?>"
                                            data-class-id="<?= $classId ?>"
                                            data-timetable-id="<?= $timetableId ?>"
                                            tabindex="0"
                                            role="button"
                                            aria-label="<?= $entry ? h($entry['subject_name']) . ' avec ' . h($entry['teacher_name']) . ' en ' . h($entry['room_name']) : 'Créneau libre pour ' . h($cls['nom']) ?>"
                                            <?php if ($canEdit): ?>
                                                onclick="openAssignModal(<?= $slot['id'] ?>, '<?= $day ?>', <?= $classId ?>, '<?= h($cls['nom']) ?>', <?= json_encode($entry) ?>, <?= $timetableId ?>)"
                                                ondragover="handleCellDragOver(event)"
                                                ondragleave="handleCellDragLeave(event)"
                                                ondrop="handleCellDrop(event, '<?= $day ?>', <?= $slot['id'] ?>, <?= $classId ?>, '<?= h($cls['nom']) ?>', <?= $timetableId ?>)"
                                            <?php endif; ?>>

                                            <?php if ($entry): ?>
                                                <!-- Carte de cours Modern Canva/M365 SaaS Component -->
                                                <div class="course-card p-2.5 rounded-3 text-start transition-all border h-100 w-100 d-flex flex-column justify-content-between position-relative"
                                                     style="--subject-color: <?= $subjectColor ?>;"
                                                     <?php if ($canEdit): ?>
                                                         draggable="true"
                                                         ondragstart="handleCardDragStart(event, <?= json_encode(['timetable_id' => $timetableId, 'slot_id' => $slot['id'], 'day' => $day, 'class_id' => $classId, 'entry' => $entry]) ?>)"
                                                     <?php endif; ?>>
                                                    
                                                    <!-- Badge Type / Accent Bar + Actions -->
                                                    <div class="d-flex align-items-center justify-content-between gap-1 mb-1.5">
                                                        <span class="course-type-badge badge rounded-pill fw-bold text-truncate extra-small px-2 py-0.5" 
                                                              style="background-color: <?= $subjectColor ?>1f; color: <?= $subjectColor ?>; border: 1px solid <?= $subjectColor ?>40;">
                                                            <i class="bi bi-journal-bookmark-fill me-1" style="font-size: 0.6rem; vertical-align: middle; color: <?= $subjectColor ?>;"></i><?= h($entry['subject_code'] ?? 'COURS') ?>
                                                        </span>
                                                        
                                                        <div class="d-flex align-items-center gap-1">
                                                            <?php if ($hasConflict): ?>
                                                                <span class="badge bg-danger text-white rounded-circle p-1 shadow-sm" title="<?= h(implode(' | ', $conflictMsgs)) ?>" data-bs-toggle="tooltip">
                                                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                                                </span>
                                                            <?php endif; ?>

                                                            <?php if ($canEdit): ?>
                                                                <button type="button" class="btn-delete-course btn btn-link p-0 flex-shrink-0 border-0 bg-transparent" onclick="event.stopPropagation(); deleteEntry(<?= $timetableId ?>, <?= $slot['id'] ?>, '<?= $day ?>')" title="Libérer ce créneau">
                                                                    <i class="bi bi-x-circle-fill"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <!-- Matière (Titre principal) -->
                                                    <div class="course-title fw-extrabold lh-sm mb-2" title="<?= h($entry['subject_name']) ?>">
                                                        <?= h($entry['subject_name']) ?>
                                                    </div>

                                                    <!-- Métadonnées (Enseignant & Salle) -->
                                                    <div class="course-meta d-flex flex-column gap-1 pt-1.5 border-top">
                                                        <div class="d-flex align-items-center gap-1.5 teacher-info" title="Enseignant: <?= h($entry['teacher_name']) ?>">
                                                            <i class="bi bi-person-circle text-primary flex-shrink-0"></i>
                                                            <span class="teacher-name text-truncate"><?= h($entry['teacher_name']) ?></span>
                                                        </div>
                                                        <div class="d-flex align-items-center gap-1.5 room-info" title="Salle: <?= h($entry['room_name']) ?>">
                                                            <i class="bi bi-geo-alt-fill text-danger flex-shrink-0"></i>
                                                            <span class="room-badge badge rounded-2 text-truncate">
                                                                <?= h($entry['room_name']) ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <!-- Slot Libre -->
                                                <?php if ($canEdit): ?>
                                                    <div class="empty-slot-placeholder p-2 text-center rounded-3 transition-all border border-dashed h-100 w-100 d-flex flex-column align-items-center justify-content-center">
                                                        <i class="bi bi-plus-circle-dotted text-primary fs-5 mb-1 opacity-75"></i>
                                                        <span class="extra-small fw-bold text-muted-theme">Affecter</span>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-muted extra-small py-2 opacity-50 fw-semibold">- Libre -</div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Affectation Cours -->
<?php if ($canEdit): ?>
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: var(--bg-card);">
            <div class="modal-header bg-primary text-white p-4">
                <div>
                    <h5 class="modal-title fw-black" id="assignModalTitle">
                        <i class="bi bi-calendar-plus me-2"></i><?= __('timetables_assign_course') ?>
                    </h5>
                    <div id="modalTargetClassHeader" class="small opacity-90 fw-semibold mt-1"></div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="assignForm">
                    <input type="hidden" id="assign_slot_id">
                    <input type="hidden" id="assign_day">
                    <input type="hidden" id="assign_class_id">
                    <input type="hidden" id="assign_timetable_id">
                    <input type="hidden" id="assign_week_id" value="<?= $weekId ?>">

                    <!-- Section Classes Sélectionnées pour ce cours -->
                    <div class="mb-3 p-3 rounded-3 border" style="background: var(--bg-card-secondary, rgba(0,0,0,0.02));">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold text-main-theme small mb-0">
                                <i class="bi bi-people-fill me-1 text-primary"></i><?= __('timetables_selected_classes') ?>
                            </label>
                            <span class="badge bg-secondary opacity-75 extra-small" id="selectedClassesCount">1 classe</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-2" id="selectedClassesContainer">
                            <!-- Populated dynamically by JS -->
                        </div>
                        <div id="noClassesAlert" class="alert alert-danger rounded-3 small p-2 mb-2 d-none">
                            <i class="bi bi-exclamation-octagon-fill me-1"></i>
                            <strong>Attention :</strong> Aucune classe sélectionnée. Veuillez rajouter au moins une classe pour pouvoir enregistrer ce cours.
                        </div>
                        <div id="addClassDropdownContainer" class="mt-2">
                            <select id="add_mutual_class_select" class="form-select form-select-sm rounded-3" onchange="onAddMutualClass(this)">
                                <option value=""><?= __('timetables_add_mutual_class') ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- 1. Matière -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-main-theme small">
                            <?= __('timetables_subject_label') ?>
                        </label>
                        <select id="assign_subject_id" class="form-select rounded-3" required onchange="onSubjectChange()">
                            <option value="">-- Chargement des matières... --</option>
                        </select>
                        <div class="form-text extra-small text-muted">Sélectionnez la matière à planifier.</div>
                    </div>

                    <!-- Banner d'avertissement pour matière non rattachée -->
                    <div id="subjectUnattachedNotice" class="alert alert-info rounded-3 small p-2.5 mb-3 d-none border-info">
                        <i class="bi bi-info-circle-fill text-info me-1 fs-6"></i>
                        <span id="subjectUnattachedNoticeText"><strong>Matière non rattachée à cette classe :</strong> Une confirmation vous sera demandée lors de l'enregistrement pour associer automatiquement cette matière à la classe.</span>
                    </div>

                    <!-- Banner d'avertissement pour affectation automatique d'enseignant -->
                    <div id="teacherAutoAssignNotice" class="alert alert-warning rounded-3 small p-2.5 mb-3 d-none border-warning">
                        <i class="bi bi-info-circle-fill text-warning me-1 fs-6"></i>
                        <span id="teacherAutoAssignText"><strong>Avertissement d'affectation (en coulisses) :</strong> Cet enseignant n'est pas encore officiellement affecté à cette matière. Après validation, cette matière sera automatiquement ajoutée à la liste de ses affectations.</span>
                    </div>

                    <!-- 2. Enseignant (Liste de tous les enseignants) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-main-theme small d-flex justify-content-between align-items-center">
                            <span><?= __('timetables_teacher_label') ?></span>
                            <button type="button" class="btn btn-link text-primary p-0 extra-small fw-bold text-decoration-none" onclick="toggleQuickTeacherInput()">
                                <i class="bi bi-person-plus-fill me-1"></i>+ Nouvel enseignant
                            </button>
                        </label>
                        <select id="assign_teacher_id" class="form-select rounded-3" required onchange="onTeacherChange()">
                            <option value="">-- Sélectionnez d'abord une matière --</option>
                        </select>
                    </div>


                    <!-- Champ Ajout Rapide d'Enseignant (Masqué par défaut) -->
                    <div id="quickTeacherContainer" class="card card-theme p-3 mb-3 border-primary border-opacity-30 rounded-3 bg-primary bg-opacity-10 d-none">
                        <label class="form-label fw-bold text-main-theme extra-small mb-1">Nom et Prénom du nouvel enseignant *</label>
                        <div class="input-group">
                            <input type="text" id="quick_teacher_name" class="form-control form-control-sm rounded-start-3" placeholder="Ex: Dr. Martin KAMGA">
                            <button type="button" class="btn btn-sm btn-primary fw-bold rounded-end-3" onclick="triggerQuickTeacherConfirmation()">
                                <i class="bi bi-plus-circle me-1"></i>Créer
                            </button>
                        </div>
                    </div>

                    <!-- 3. Salle de classe -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_room_label') ?></label>
                        <select id="assign_room_id" class="form-select rounded-3" required onchange="checkRealtimeConflict()">
                            <option value="">-- Choisir une salle --</option>
                            <?php foreach ($gridData['rooms'] as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= h($r['nom']) ?> (<?= h($r['code']) ?> - <?= $r['capacite'] ?> places)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-main-theme small"><?= __('timetables_card_color') ?></label>
                        <input type="color" id="assign_color" class="form-control form-control-color w-100 rounded-3" value="#3b82f6">
                    </div>

                    <div id="modalConflictFeedback" class="alert alert-danger d-none rounded-3 small mb-0"></div>
                </form>
            </div>
            <div class="modal-footer border-top p-3" style="background: var(--bg-card);">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="saveAssignment()">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pop-Up de Confirmation Ajout Rapide d'Enseignant -->
<div class="modal fade" id="confirmNewTeacherModal" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: var(--bg-card);">
            <div class="modal-header bg-warning text-dark p-4">
                <h5 class="modal-title fw-black">
                    <i class="bi bi-shield-exclamation me-2"></i>Confirmation : Création de l'enseignant
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-main-theme mb-3">
                    Vous allez créer un nouvel enseignant : <strong id="confirmTeacherNameText" class="text-primary fs-6"></strong>.
                </p>
                <div class="alert alert-warning border-0 rounded-3 small p-3 mb-3">
                    <div class="fw-bold mb-2"><i class="bi bi-info-circle-fill me-1"></i> Actions automatiques qui vont être exécutées :</div>
                    <ul class="mb-0 ps-3">
                        <li class="mb-1">Création officielle du compte enseignant dans l'établissement ;</li>
                        <li class="mb-1">Ajout à la liste officielle des enseignants ;</li>
                        <li class="mb-1">Affectation immédiate de la matière <strong id="confirmSubjectNameText"></strong> à cet enseignant ;</li>
                        <li>Disponibilité immédiate pour la programmation de ce cours.</li>
                    </ul>
                </div>
                <p class="small text-muted mb-0">Voulez-vous vraiment poursuivre cette opération ?</p>
            </div>
            <div class="modal-footer border-top p-3" style="background: var(--bg-card);">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm text-dark" onclick="executeQuickTeacherCreation()">
                    <i class="bi bi-check-circle-fill me-1"></i>Confirmer la création
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmation Rattachement Matière à la Classe -->
<div class="modal fade" id="confirmAttachSubjectModal" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: var(--bg-card);">
            <div class="modal-header bg-primary text-white p-4">
                <div>
                    <h5 class="modal-title fw-black">
                        <i class="bi bi-link-45deg me-2"></i>Rattachement automatique de la matière
                    </h5>
                    <div id="attachModalClassName" class="small opacity-90 fw-semibold mt-1"></div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-3 text-main-theme">
                    La matière <strong id="attachModalSubjectName" class="text-primary"></strong> n'est pas encore officiellement rattachée à cette classe.
                </p>
                <div class="alert alert-info border-0 rounded-3 small mb-3">
                    <div class="fw-bold mb-2"><i class="bi bi-info-circle-fill me-1"></i> Opérations automatiques lors de la confirmation :</div>
                    <ul class="mb-0 ps-3">
                        <li class="mb-1">Rattachement de la <strong>matière existante</strong> à cette classe (aucune création de doublon) ;</li>
                        <li class="mb-1">Conservation du <strong>coefficient d'origine (Coef: <span id="attachModalSubjectCoef">1</span>)</strong> ;</li>
                        <li class="mb-1">Enregistrement de l'association <strong>Classe ↔ Matière</strong> pour l'année académique active ;</li>
                        <li>Programmation immédiate du cours sur la grille d'emploi du temps.</li>
                    </ul>
                </div>
                <p class="small text-muted mb-0">Souhaitez-vous confirmer le rattachement et poursuivre la programmation du cours ?</p>
            </div>
            <div class="modal-footer border-top p-3 gap-2" style="background: var(--bg-card);">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="executeConfirmedSubjectAttachment()">
                    <i class="bi bi-check-circle-fill me-1"></i>Confirmer & Enregistrer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmation Retrait d'une classe de la programmation -->
<div class="modal fade" id="confirmRemoveClassModal" tabindex="-1" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: var(--bg-card);">
            <div class="modal-header bg-warning bg-opacity-10 text-warning p-3">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2 mb-0">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i> Retirer la classe
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <p class="mb-2 text-main-theme small fw-semibold">
                    Êtes-vous sûr de vouloir retirer la classe <strong id="removeClassModalName" class="text-primary"></strong> de cette programmation de cours ?
                </p>
                <div class="alert alert-warning border-0 rounded-3 extra-small mb-0 p-2 text-dark">
                    <i class="bi bi-info-circle-fill me-1"></i> <strong>Information :</strong> Cette action retire uniquement la classe de cette séance. La classe et ses données ne seront <strong>pas supprimées</strong> de l'établissement.
                </div>
            </div>
            <div class="modal-footer border-top p-2 gap-2" style="background: var(--bg-card);">
                <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold shadow-sm" onclick="executeRemoveClassFromAssignment()">
                    <i class="bi bi-trash-fill me-1"></i> Retirer
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Hydratation de l'état local du Frontend pour zéro latence
window.TIMETABLE_DATA = {
    classes: <?= json_encode($classes) ?>,
    subjects: <?= json_encode($gridData['subjects']) ?>,
    teachers: <?= json_encode($gridData['teachers']) ?>,
    rooms: <?= json_encode($gridData['rooms']) ?>,
    slots: <?= json_encode($slots) ?>,
    matrix: <?= json_encode($matrix) ?>,
    timetablesByClass: <?= json_encode($timetablesByClass) ?>,
    canEdit: <?= $canEdit ? 'true' : 'false' ?>,
    weekId: <?= $weekId ?>,
    cycleId: <?= $cycleId ?>,
    levelId: <?= $levelId ?>
};

const allGridClasses = window.TIMETABLE_DATA.classes.map(c => ({ id: parseInt(c.id), nom: c.nom }));
let currentAssignTarget = null;
let currentEntryData = null;
let selectedClasses = [];
let initialClassIds = [];
let classToRemoveTemp = null;
let draggedData = null;
let clipboardEntry = null;

// Palette & Drag-and-Drop Functions
function toggleQuickAssignPalette() {
    const palette = document.getElementById('quickAssignPalette');
    if (palette) {
        palette.classList.toggle('d-none');
    }
}

function filterPaletteSubjects(query) {
    const q = query.toLowerCase().trim();
    document.querySelectorAll('.palette-subject-chip').forEach(chip => {
        const text = chip.textContent.toLowerCase();
        if (text.includes(q)) {
            chip.classList.remove('d-none');
        } else {
            chip.classList.add('d-none');
        }
    });
}

function handlePaletteDragStart(e, subjectObj) {
    draggedData = { type: 'PALETTE_SUBJECT', subject: subjectObj };
    e.dataTransfer.setData('text/plain', JSON.stringify(draggedData));
    e.dataTransfer.effectAllowed = 'copy';
}

function handleCardDragStart(e, cardObj) {
    e.stopPropagation();
    draggedData = { type: 'EXISTING_CARD', card: cardObj };
    e.dataTransfer.setData('text/plain', JSON.stringify(draggedData));
    e.dataTransfer.effectAllowed = 'move';
}

function handleCellDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
    e.currentTarget.classList.add('bg-primary', 'bg-opacity-10', 'border-primary');
}

function handleCellDragLeave(e) {
    e.currentTarget.classList.remove('bg-primary', 'bg-opacity-10', 'border-primary');
}

function handleCellDrop(e, targetDay, targetSlotId, targetClassId, targetClassName, targetTimetableId) {
    e.preventDefault();
    e.currentTarget.classList.remove('bg-primary', 'bg-opacity-10', 'border-primary');

    if (!draggedData) return;

    if (draggedData.type === 'PALETTE_SUBJECT') {
        const sub = draggedData.subject;
        openAssignModal(targetSlotId, targetDay, targetClassId, targetClassName, null, targetTimetableId);
        
        setTimeout(() => {
            const subSelect = document.getElementById('assign_subject_id');
            if (subSelect) {
                subSelect.value = sub.id;
                onSubjectChange();
            }
        }, 50);

    } else if (draggedData.type === 'EXISTING_CARD') {
        const source = draggedData.card;
        if (source.day === targetDay && source.slot_id === targetSlotId && source.class_id === targetClassId) return;

        // Déplacer l'entrée existante vers le nouveau créneau
        const entry = source.entry;
        const payload = {
            timetable_id: targetTimetableId,
            week_id: window.TIMETABLE_DATA.weekId,
            class_id: targetClassId,
            class_ids: [targetClassId],
            slot_id: targetSlotId,
            day_of_week: targetDay,
            subject_id: entry.subject_id,
            teacher_id: entry.teacher_id,
            room_id: entry.room_id,
            couleur_hex: entry.couleur_hex || '#3b82f6'
        };

        // Sauvegarder sur la destination puis libérer la source
        fetch('/timetables/api/grid/save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                // Supprimer l'ancienne position
                fetch('/timetables/api/grid/delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        timetable_id: source.timetable_id,
                        slot_id: source.slot_id,
                        day_of_week: source.day
                    })
                }).then(() => location.reload());
            } else {
                alert(res.message || 'Erreur lors du déplacement.');
            }
        });
    }

    draggedData = null;
}

// Support des Raccourcis Clavier
document.addEventListener('keydown', function(e) {
    // Si un input ou modal est ouvert, ne pas exécuter les raccourcis globaux
    if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) return;

    if (e.key === 'Escape') {
        const openModal = document.querySelector('.modal.show');
        if (openModal) {
            const bModal = bootstrap.Modal.getInstance(openModal);
            if (bModal) bModal.hide();
        }
    }
});

function renderSelectedClassesUI() {
    const container = document.getElementById('selectedClassesContainer');
    const countBadge = document.getElementById('selectedClassesCount');
    const noClassesAlert = document.getElementById('noClassesAlert');
    const addSelect = document.getElementById('add_mutual_class_select');
    const addContainer = document.getElementById('addClassDropdownContainer');

    if (!container) return;
    container.innerHTML = '';

    if (countBadge) {
        countBadge.textContent = selectedClasses.length + (selectedClasses.length > 1 ? ' classes' : ' classe');
    }

    if (selectedClasses.length === 0) {
        if (noClassesAlert) noClassesAlert.classList.remove('d-none');
    } else {
        if (noClassesAlert) noClassesAlert.classList.add('d-none');
        selectedClasses.forEach(cls => {
            const badge = document.createElement('div');
            badge.className = 'badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill d-flex align-items-center gap-2 shadow-sm';
            badge.style.fontSize = '0.85rem';
            
            const nameSpan = document.createElement('span');
            nameSpan.className = 'fw-bold';
            nameSpan.innerHTML = `<i class="bi bi-door-open-fill me-1"></i>${escapeHtml(cls.nom)}`;
            badge.appendChild(nameSpan);

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn-close btn-close-primary ms-1';
            removeBtn.style.fontSize = '0.65rem';
            removeBtn.title = 'Retirer cette classe de la programmation';
            removeBtn.onclick = function() {
                confirmRemoveClassFromAssignment(cls.id, cls.nom);
            };
            badge.appendChild(removeBtn);

            container.appendChild(badge);
        });
    }

    if (addSelect) {
        let addOpts = '<option value="">+ Ajouter une classe (Cours mutualisé)...</option>';
        const selectedIds = selectedClasses.map(c => c.id);
        const available = allGridClasses.filter(c => !selectedIds.includes(c.id));

        if (available.length > 0) {
            available.forEach(c => {
                addOpts += `<option value="${c.id}">${escapeHtml(c.nom)}</option>`;
            });
            addSelect.innerHTML = addOpts;
            if (addContainer) addContainer.classList.remove('d-none');
        } else {
            if (addContainer) addContainer.classList.add('d-none');
        }
    }
}

function escapeHtml(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function onAddMutualClass(selectEl) {
    const classId = parseInt(selectEl.value);
    if (!classId) return;

    const found = allGridClasses.find(c => c.id === classId);
    if (found && !selectedClasses.some(c => c.id === classId)) {
        selectedClasses.push({ id: found.id, nom: found.nom });
        renderSelectedClassesUI();
        checkRealtimeConflict();
    }
    selectEl.value = '';
}

function confirmRemoveClassFromAssignment(classId, className) {
    classToRemoveTemp = { id: classId, nom: className };
    document.getElementById('removeClassModalName').textContent = className;

    const modalEl = document.getElementById('confirmRemoveClassModal');
    const modalObj = new bootstrap.Modal(modalEl);
    modalObj.show();
}

function executeRemoveClassFromAssignment() {
    if (!classToRemoveTemp) return;

    const classId = classToRemoveTemp.id;
    selectedClasses = selectedClasses.filter(c => c.id !== classId);

    const modalEl = document.getElementById('confirmRemoveClassModal');
    const modalObj = bootstrap.Modal.getInstance(modalEl);
    if (modalObj) modalObj.hide();

    renderSelectedClassesUI();
    checkRealtimeConflict();
}

function openAssignModal(slotId, day, classId, className, entry, timetableId) {
    document.getElementById('assign_slot_id').value = slotId;
    document.getElementById('assign_day').value = day;
    document.getElementById('assign_class_id').value = classId;
    document.getElementById('assign_timetable_id').value = timetableId || 0;
    currentEntryData = entry;

    selectedClasses = [{ id: classId, nom: className }];

    if (entry && typeof window.TIMETABLE_DATA !== 'undefined' && window.TIMETABLE_DATA.matrix) {
        const matrix = window.TIMETABLE_DATA.matrix;
        allGridClasses.forEach(cls => {
            if (cls.id !== classId) {
                const cellEntry = matrix && matrix[day] && matrix[day][slotId] ? matrix[day][slotId][cls.id] : null;
                if (cellEntry && cellEntry.subject_id == entry.subject_id && cellEntry.teacher_id == entry.teacher_id && cellEntry.room_id == entry.room_id) {
                    selectedClasses.push({ id: cls.id, nom: cls.nom });
                }
            }
        });
    }

    initialClassIds = selectedClasses.map(c => c.id);
    renderSelectedClassesUI();

    document.getElementById('modalTargetClassHeader').innerText = `Jour : ${day}`;
    document.getElementById('modalConflictFeedback').classList.add('d-none');

    document.getElementById('modalConflictFeedback').innerHTML = '';
    document.getElementById('quickTeacherContainer').classList.add('d-none');
    const autoNotice = document.getElementById('teacherAutoAssignNotice');
    if (autoNotice) autoNotice.classList.add('d-none');

    // Remplissage ultra-rapide depuis TIMETABLE_DATA en mémoire local
    const subSelect = document.getElementById('assign_subject_id');
    subSelect.innerHTML = '<option value="">-- Choisir une matière --</option>';

    if (window.TIMETABLE_DATA && window.TIMETABLE_DATA.subjects) {
        window.TIMETABLE_DATA.subjects.forEach(s => {
            const sel = (entry && entry.subject_id == s.id) ? 'selected' : '';
            const isAttached = (s.is_attached == 1 || typeof s.is_attached === 'undefined');
            const badge = isAttached ? '' : ' [Non rattachée]';
            subSelect.innerHTML += `<option value="${s.id}" data-is-attached="${isAttached ? 1 : 0}" data-color="${s.couleur_hex || '#3b82f6'}" data-coef="${s.coefficient || 1}" ${sel}>${s.nom} (${s.code || 'UV'})${badge}</option>`;
        });
    }

    if (entry && entry.subject_id) {
        subSelect.value = entry.subject_id;
        onSubjectChange(entry.teacher_id);
    } else {
        document.getElementById('assign_teacher_id').innerHTML = '<option value="">-- Sélectionnez d\'abord une matière --</option>';
    }

    if (entry) {
        document.getElementById('assign_room_id').value = entry.room_id || '';
        document.getElementById('assign_color').value = entry.couleur_hex || '#3b82f6';
    } else {
        document.getElementById('assign_room_id').value = '';
        document.getElementById('assign_color').value = '#3b82f6';
    }

    const modal = new bootstrap.Modal(document.getElementById('assignModal'));
    modal.show();
}

function onSubjectChange(targetTeacherId = null) {
    const subjectId = document.getElementById('assign_subject_id').value;
    const classId = document.getElementById('assign_class_id').value;
    const teacherSelect = document.getElementById('assign_teacher_id');
    const autoNotice = document.getElementById('teacherAutoAssignNotice');
    const subNotice = document.getElementById('subjectUnattachedNotice');
    const subSelect = document.getElementById('assign_subject_id');
    const selectedSubOpt = subSelect.options[subSelect.selectedIndex];

    if (selectedSubOpt && selectedSubOpt.getAttribute('data-is-attached') === '0') {
        if (subNotice) subNotice.classList.remove('d-none');
    } else {
        if (subNotice) subNotice.classList.add('d-none');
    }

    if (!subjectId) {
        teacherSelect.innerHTML = '<option value="">-- Sélectionnez d\'abord une matière --</option>';
        if (autoNotice) autoNotice.classList.add('d-none');
        return;
    }

    // Chargement instantané des enseignants depuis la mémoire local TIMETABLE_DATA
    let optionsHtml = '<option value="">-- Choisir un enseignant --</option>';

    if (window.TIMETABLE_DATA && window.TIMETABLE_DATA.teachers) {
        window.TIMETABLE_DATA.teachers.forEach(t => {
            const isAssigned = (t.is_assigned == 1 || typeof t.is_assigned === 'undefined');
            const badge = isAssigned ? ' (Habilité)' : ' (Non affecté - Affectation auto)';
            const sel = (targetTeacherId && targetTeacherId == t.id) ? 'selected' : '';
            optionsHtml += `<option value="${t.id}" data-is-assigned="${isAssigned ? 1 : 0}" ${sel}>${t.nom_complet}${badge}</option>`;
        });
    }

    optionsHtml += '<option value="NEW_TEACHER" class="fw-bold text-primary">+ Nouvel enseignant...</option>';
    teacherSelect.innerHTML = optionsHtml;

    if (targetTeacherId) {
        teacherSelect.value = targetTeacherId;
    }

    onTeacherChange();
}

function onTeacherChange() {
    const teacherSelect = document.getElementById('assign_teacher_id');
    const val = teacherSelect.value;
    const autoNotice = document.getElementById('teacherAutoAssignNotice');
    const selectedOpt = teacherSelect.options[teacherSelect.selectedIndex];

    if (val === 'NEW_TEACHER') {
        document.getElementById('quickTeacherContainer').classList.remove('d-none');
        if (autoNotice) autoNotice.classList.add('d-none');
        document.getElementById('quick_teacher_name').focus();
    } else {
        document.getElementById('quickTeacherContainer').classList.add('d-none');

        if (selectedOpt && selectedOpt.getAttribute('data-is-assigned') === '0') {
            if (autoNotice) autoNotice.classList.remove('d-none');
        } else {
            if (autoNotice) autoNotice.classList.add('d-none');
        }

        checkRealtimeConflict();
    }
}

function toggleQuickTeacherInput() {
    const container = document.getElementById('quickTeacherContainer');
    container.classList.toggle('d-none');
    if (!container.classList.contains('d-none')) {
        document.getElementById('quick_teacher_name').focus();
    }
}

function triggerQuickTeacherConfirmation() {
    const name = document.getElementById('quick_teacher_name').value.trim();
    const subjectSelect = document.getElementById('assign_subject_id');
    const subjectName = subjectSelect.options[subjectSelect.selectedIndex]?.text || 'Matière sélectionnée';

    if (!name) {
        alert('Veuillez saisir le nom du nouvel enseignant.');
        return;
    }
    if (!subjectSelect.value) {
        alert('Veuillez d\'abord sélectionner la matière.');
        return;
    }

    document.getElementById('confirmTeacherNameText').innerText = name;
    document.getElementById('confirmSubjectNameText').innerText = subjectName;

    const confirmModal = new bootstrap.Modal(document.getElementById('confirmNewTeacherModal'));
    confirmModal.show();
}

function executeQuickTeacherCreation() {
    const name = document.getElementById('quick_teacher_name').value.trim();
    const subjectId = document.getElementById('assign_subject_id').value;
    const classId = document.getElementById('assign_class_id').value;

    fetch('/timetables/api/quick-create-teacher', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nom_complet: name, subject_id: subjectId, class_id: classId })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success && res.teacher) {
            const t = res.teacher;
            window.TIMETABLE_DATA.teachers.push({ id: t.id, nom_complet: t.nom_complet, role: t.role, is_assigned: 1 });
            
            const teacherSelect = document.getElementById('assign_teacher_id');
            const newOpt = new Option(t.nom_complet + ' (Nouvellement créé)', t.id, true, true);
            teacherSelect.add(newOpt, teacherSelect.options[1]);
            teacherSelect.value = t.id;

            document.getElementById('quickTeacherContainer').classList.add('d-none');
            document.getElementById('quick_teacher_name').value = '';

            const confirmModalEl = document.getElementById('confirmNewTeacherModal');
            const modalObj = bootstrap.Modal.getInstance(confirmModalEl);
            if (modalObj) modalObj.hide();

            checkRealtimeConflict();
        } else {
            alert(res.message || 'Erreur lors de la création de l\'enseignant.');
        }
    });
}

function checkRealtimeConflict() {
    const timetableId = document.getElementById('assign_timetable_id').value;
    const weekId = document.getElementById('assign_week_id').value;
    const classId = selectedClasses.length > 0 ? selectedClasses[0].id : document.getElementById('assign_class_id').value;
    const slotId = document.getElementById('assign_slot_id').value;
    const day = document.getElementById('assign_day').value;
    const subjectId = document.getElementById('assign_subject_id').value;
    const teacherId = document.getElementById('assign_teacher_id').value;
    const roomId = document.getElementById('assign_room_id').value;
    const feedback = document.getElementById('modalConflictFeedback');

    if (!teacherId || !roomId || teacherId === 'NEW_TEACHER' || selectedClasses.length === 0) {
        feedback.classList.add('d-none');
        return;
    }

    fetch(`/timetables/api/validate-conflict?timetable_id=${timetableId}&week_id=${weekId}&class_id=${classId}&slot_id=${slotId}&day_of_week=${encodeURIComponent(day)}&subject_id=${subjectId}&teacher_id=${teacherId}&room_id=${roomId}`)
        .then(r => r.json())
        .then(res => {
            if (res.has_conflict) {
                feedback.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> ' + res.messages.join('<br>');
                feedback.classList.remove('d-none');
            } else {
                feedback.classList.add('d-none');
                feedback.innerHTML = '';
            }
        });
}

function saveAssignment(skipAttachmentCheck = false) {
    if (selectedClasses.length === 0) {
        alert('Veuillez sélectionner au moins une classe pour pouvoir programmer ce cours.');
        return;
    }

    const classIds = selectedClasses.map(c => c.id);
    const removedClassIds = initialClassIds.filter(id => !classIds.includes(id));

    const payload = {
        timetable_id: document.getElementById('assign_timetable_id').value,
        week_id: document.getElementById('assign_week_id').value,
        class_id: classIds[0],
        class_ids: classIds,
        removed_class_ids: removedClassIds,
        slot_id: document.getElementById('assign_slot_id').value,
        day_of_week: document.getElementById('assign_day').value,
        subject_id: document.getElementById('assign_subject_id').value,
        teacher_id: document.getElementById('assign_teacher_id').value,
        room_id: document.getElementById('assign_room_id').value,
        couleur_hex: document.getElementById('assign_color').value
    };

    if (payload.teacher_id === 'NEW_TEACHER') {
        alert('Veuillez finaliser la création du nouvel enseignant.');
        return;
    }

    if (!payload.subject_id || !payload.teacher_id || !payload.room_id) {
        alert('Veuillez remplir tous les champs obligatoires (matière, enseignant, salle).');
        return;
    }

    const subSelect = document.getElementById('assign_subject_id');
    const selectedSubOpt = subSelect.options[subSelect.selectedIndex];
    const isAttached = selectedSubOpt ? selectedSubOpt.getAttribute('data-is-attached') : '1';

    if (isAttached === '0' && !skipAttachmentCheck) {
        const rawText = selectedSubOpt.text || '';
        const subName = rawText.replace(' [Non rattachée]', '');
        const subCoef = selectedSubOpt.getAttribute('data-coef') || '1';
        const classNameHeader = selectedClasses.map(c => c.nom).join(', ');

        document.getElementById('attachModalSubjectName').textContent = subName;
        document.getElementById('attachModalClassName').textContent = `Classes : ${classNameHeader}`;
        document.getElementById('attachModalSubjectCoef').textContent = subCoef;

        const attachModal = new bootstrap.Modal(document.getElementById('confirmAttachSubjectModal'));
        attachModal.show();
        return;
    }

    fetch('/timetables/api/grid/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            location.reload();
        } else {
            const feedback = document.getElementById('modalConflictFeedback');
            feedback.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> ' + (res.message || 'Erreur d\'enregistrement');
            feedback.classList.remove('d-none');
        }
    })
    .catch(err => {
        alert('Erreur réseau lors de l\'enregistrement.');
    });
}

function executeConfirmedSubjectAttachment() {
    const confirmModalEl = document.getElementById('confirmAttachSubjectModal');
    const modalObj = bootstrap.Modal.getInstance(confirmModalEl);
    if (modalObj) modalObj.hide();

    saveAssignment(true);
}

function deleteEntry(timetableId, slotId, day) {
    if (!confirm('Voulez-vous vraiment libérer ce créneau ?')) return;

    fetch('/timetables/api/grid/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            timetable_id: timetableId,
            slot_id: slotId,
            day_of_week: day
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            location.reload();
        } else {
            alert(res.message || 'Erreur lors de la suppression');
        }
    });
}
</script>

<style>
    /* Premium SaaS Timetable Grid Styling (Canva & Notion inspired) */
    .timetable-grid-wrapper {
        position: relative;
        scrollbar-width: thin;
        scrollbar-color: rgba(37, 99, 235, 0.3) rgba(0, 0, 0, 0.05);
    }
    .timetable-grid-wrapper::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .timetable-grid-wrapper::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.03);
        border-radius: 4px;
    }
    .timetable-grid-wrapper::-webkit-scrollbar-thumb {
        background: rgba(37, 99, 235, 0.25);
        border-radius: 4px;
    }
    .timetable-grid-wrapper::-webkit-scrollbar-thumb:hover {
        background: rgba(37, 99, 235, 0.5);
    }

    .grid-table {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        border-radius: 12px;
        background: var(--bg-card, #ffffff);
    }
    .grid-table td, .grid-table th {
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        border-right: 1px solid rgba(0, 0, 0, 0.06);
    }

    /* Sticky Headers (Top) */
    .sticky-header-col {
        position: sticky;
        top: 0;
        z-index: 10;
        background: var(--bg-card-secondary, #f8fafc);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        backdrop-filter: blur(8px);
    }

    /* Sticky Left 1 (Jours) */
    .sticky-col-1 {
        position: sticky;
        left: 0;
        z-index: 9;
        background: var(--bg-card-secondary, #f8fafc);
        box-shadow: 2px 0 4px rgba(0, 0, 0, 0.03);
        border-right: 2px solid rgba(37, 99, 235, 0.15) !important;
    }

    /* Sticky Left 2 (Horaires) */
    .sticky-col-2 {
        position: sticky;
        left: 100px;
        z-index: 9;
        background: var(--bg-card-secondary, #f8fafc);
        box-shadow: 2px 0 4px rgba(0, 0, 0, 0.03);
        border-right: 2px solid rgba(0, 0, 0, 0.08) !important;
    }

    /* Top Left Corners (Jours & Horaires Headers) */
    .sticky-top-left-1 {
        position: sticky;
        top: 0;
        left: 0;
        z-index: 20;
        background: var(--bg-card-secondary, #f1f5f9) !important;
        box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.06);
        border-right: 2px solid rgba(37, 99, 235, 0.15) !important;
    }
    .sticky-top-left-2 {
        position: sticky;
        top: 0;
        left: 100px;
        z-index: 20;
        background: var(--bg-card-secondary, #f1f5f9) !important;
        box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.06);
        border-right: 2px solid rgba(0, 0, 0, 0.08) !important;
    }

    .grid-cell {
        min-height: 120px;
        height: 125px;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        background-color: var(--bg-card, #ffffff);
        vertical-align: top;
    }
    .grid-cell:hover, .grid-cell:focus {
        background-color: rgba(37, 99, 235, 0.04) !important;
        box-shadow: inset 0 0 0 2px rgba(37, 99, 235, 0.4);
        outline: none;
    }

    /* Modern Canva & Microsoft 365 Course Card Design System */
    .course-card {
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        border-radius: 10px;
        border: 1px solid rgba(0, 0, 0, 0.08) !important;
        border-left: 4px solid var(--subject-color, #3b82f6) !important;
        background: var(--bg-card, #ffffff);
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
    }
    .course-card:hover {
        transform: translateY(-2px);
        border-color: rgba(37, 99, 235, 0.3) !important;
        border-left-color: var(--subject-color, #3b82f6) !important;
        box-shadow: 0 8px 20px -4px rgba(15, 23, 42, 0.12) !important;
    }

    .course-title {
        font-size: 0.88rem;
        font-weight: 700;
        color: #0f172a;
        word-break: break-word;
        letter-spacing: -0.01em;
        line-height: 1.25;
    }

    .course-meta {
        border-top-color: rgba(0, 0, 0, 0.07) !important;
    }

    .teacher-info {
        font-size: 0.77rem;
    }
    .teacher-name {
        font-weight: 600;
        color: #475569;
    }

    .room-info {
        font-size: 0.75rem;
    }
    .room-badge {
        background-color: #f1f5f9;
        color: #1e293b;
        border: 1px solid #cbd5e1;
        font-weight: 700;
        padding: 0.2em 0.5em;
    }

    .btn-delete-course {
        color: #94a3b8;
        font-size: 0.95rem;
        transition: color 0.15s ease, transform 0.15s ease;
    }
    .btn-delete-course:hover {
        color: #ef4444 !important;
        transform: scale(1.15);
    }

    /* Pause Row Styling */
    .cell-pause-full {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(16, 185, 129, 0.16) 100%) !important;
        border-top: 1px dashed rgba(16, 185, 129, 0.3) !important;
        border-bottom: 1px dashed rgba(16, 185, 129, 0.3) !important;
    }

    .empty-slot-placeholder {
        transition: all 0.2s ease;
        background-color: rgba(248, 250, 252, 0.6);
        border-color: rgba(203, 213, 225, 0.7) !important;
    }
    .empty-slot-placeholder:hover {
        border-color: var(--primary-color, #2563eb) !important;
        background-color: rgba(37, 99, 235, 0.06);
        color: var(--primary-color, #2563eb) !important;
    }

    .conflict-cell {
        animation: pulse-conflict 2s infinite ease-in-out;
    }
    @keyframes pulse-conflict {
        0% { box-shadow: inset 0 0 0 2px #ef4444; }
        50% { box-shadow: inset 0 0 0 4px rgba(239, 68, 68, 0.4); }
        100% { box-shadow: inset 0 0 0 2px #ef4444; }
    }

    .text-rotate-270-md {
        writing-mode: vertical-lr;
        transform: rotate(180deg);
        text-align: center;
        margin: 0 auto;
        font-weight: 800;
        letter-spacing: 2px;
    }

    /* Dark Mode Specific Overrides for Grid */
    [data-theme="dark"] .grid-table {
        background-color: #0f172a !important;
    }
    [data-theme="dark"] .grid-table td, [data-theme="dark"] .grid-table th {
        border-color: rgba(255, 255, 255, 0.08) !important;
    }
    [data-theme="dark"] .sticky-header-col, 
    [data-theme="dark"] .sticky-col-1, 
    [data-theme="dark"] .sticky-col-2,
    [data-theme="dark"] .sticky-top-left-1,
    [data-theme="dark"] .sticky-top-left-2 {
        background-color: #1e293b !important;
        color: #f8fafc !important;
    }
    [data-theme="dark"] .grid-cell {
        background-color: #0f172a !important;
    }
    [data-theme="dark"] .grid-cell:hover, [data-theme="dark"] .grid-cell:focus {
        background-color: rgba(59, 130, 246, 0.15) !important;
        box-shadow: inset 0 0 0 2px rgba(59, 130, 246, 0.5);
    }
    [data-theme="dark"] .course-card {
        background: #1e293b !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
        border-left-color: var(--subject-color, #3b82f6) !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.4) !important;
    }
    [data-theme="dark"] .course-card:hover {
        border-color: rgba(96, 165, 250, 0.45) !important;
        border-left-color: var(--subject-color, #3b82f6) !important;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6) !important;
    }
    [data-theme="dark"] .course-title {
        color: #f8fafc !important;
    }
    [data-theme="dark"] .teacher-name {
        color: #cbd5e1 !important;
    }
    [data-theme="dark"] .course-meta {
        border-top-color: rgba(255, 255, 255, 0.1) !important;
    }
    [data-theme="dark"] .room-badge {
        background-color: #334155 !important;
        color: #f1f5f9 !important;
        border-color: #475569 !important;
    }
    [data-theme="dark"] .empty-slot-placeholder {
        background-color: rgba(30, 41, 59, 0.5);
        border-color: rgba(255, 255, 255, 0.12) !important;
    }
    [data-theme="dark"] .cell-pause-full {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(16, 185, 129, 0.28) 100%) !important;
        border-color: rgba(52, 211, 153, 0.3) !important;
    }

    /* Print Stylesheet for Instant Browser Printing */
    @media print {
        @page {
            size: A4 landscape;
            margin: 5mm;
        }
        body {
            background: #ffffff !important;
            color: #000000 !important;
        }
        .btn, button, nav, header, sidebar, footer, .modal, #quickAssignPalette, .btn-delete-course {
            display: none !important;
        }
        .timetable-grid-wrapper {
            max-height: none !important;
            overflow: visible !important;
        }
        .grid-table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        .grid-table th, .grid-table td {
            border: 1px solid #64748b !important;
            box-shadow: none !important;
        }
        .course-card {
            border: 1px solid #475569 !important;
            box-shadow: none !important;
            background: #f8fafc !important;
        }
    }
</style>

<?php if ($isSuperAdmin): ?>
<!-- Modal de Confirmation Suppression Emploi du Temps -->
<div class="modal fade" id="deleteTimetableModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-danger bg-opacity-10 py-3">
                <h5 class="modal-title fw-bold text-danger d-flex align-items-center gap-2 mb-0">
                    <i class="bi bi-exclamation-triangle-fill"></i> Supprimer l'emploi du temps
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <form method="POST" action="/timetables/delete">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">
                <input type="hidden" name="timetable_id" id="delete_timetable_id" value="">
                <div class="modal-body py-4 px-4">
                    <p class="mb-2 text-main-theme fw-semibold" style="font-size: 1rem;">Êtes-vous sûr de vouloir supprimer définitivement cet emploi du temps ?</p>
                    <div class="alert alert-danger border-0 rounded-3 small mb-0 d-flex gap-2 align-items-start">
                        <i class="bi bi-shield-alert fs-5 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <strong>Attention :</strong> Cette action est irréversible. Tous les créneaux positionnés et le journal d'audit associés à cet emploi du temps (<strong id="delete_timetable_title"></strong>) seront supprimés.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4 gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-trash3-fill me-1"></i> Supprimer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = document.getElementById('deleteTimetableModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const timetableId = button.getAttribute('data-timetable-id');
            const timetableTitle = button.getAttribute('data-timetable-title');
            
            document.getElementById('delete_timetable_id').value = timetableId;
            document.getElementById('delete_timetable_title').textContent = timetableTitle || '';
        });
    }
});
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../templates/layout.php';
?>
