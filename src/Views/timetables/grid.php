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
                    <span
                        class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 rounded-pill fw-medium"
                        style="font-size: 0.75rem;">
                        <i class="bi bi-mortarboard-fill me-1"></i><?= __('timetables_type') ?> Supérieur LMD
                    </span>
                    <span
                        class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2.5 py-1 rounded-pill fw-medium"
                        style="font-size: 0.75rem;">
                        <i class="bi bi-diagram-3-fill me-1"></i><?= __('timetables_cycle') ?> <?= $cycleName ?>
                    </span>
                    <span
                        class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill fw-medium"
                        style="font-size: 0.75rem;">
                        <i class="bi bi-layers-fill me-1"></i><?= __('level') ?? 'Niveau' ?>: <?= $levelName ?>
                    </span>
                    <span
                        class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2.5 py-1 rounded-pill fw-medium"
                        style="font-size: 0.75rem;">
                        <i class="bi bi-calendar-event me-1"></i><?= __('timetables_week') ?> <?= $weekLibelle ?>
                    </span>
                </div>
                <h2 class="fw-black text-main-theme mb-0 fs-3"><?= __('timetables_grid_header_title') ?> -
                    <?= $levelName ?></h2>
                <p class="text-muted small mb-0">
                    <?= __('timetables_period_from') ?> <?= date('d/m/Y', strtotime($weekRow['date_debut'])) ?>
                    <?= __('timetables_to') ?> <?= date('d/m/Y', strtotime($weekRow['date_fin'])) ?>
                    | <?= count($classes) ?> <?= __('timetables_all_classes') ?>
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2 align-items-center">
                <?php if ($canEdit): ?>
                    <button type="button" class="btn btn-sm btn-gradient-primary rounded-pill px-3 py-2 fw-bold shadow-sm"
                        onclick="openBulkScheduleModal()" style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); color: white; border: none;">
                        <i class="bi bi-layers-half me-1"></i>Planification en Masse
                    </button>
                    <!-- <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-2 fw-bold shadow-sm"
                        data-bs-toggle="offcanvas" data-bs-target="#quickAssignPaletteOffcanvas"
                        onclick="toggleQuickAssignPalette()">
                        <i class="bi bi-layout-sidebar-reverse me-1"></i>Palette d'affectation (Glisser-Déposer)
                    </button> -->
                <?php endif; ?>
                <a href="/timetables/wizard" class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-bold shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i><?= __('timetables_new_wizard') ?>
                </a>
                <a href="/timetables"
                    class="btn btn-sm btn-light-theme rounded-pill px-3 py-2 fw-semibold border-theme-light shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i><?= __('back') ?? 'Retour' ?>
                </a>

                <button type="button" onclick="triggerPrintModel(<?= (int)$cycleId ?>, <?= (int)$levelId ?>, <?= (int)$weekId ?>)"
                    class="btn btn-sm btn-action-modern text-primary border px-3 rounded-pill fw-semibold"
                    title="<?= __('print') ?? 'Imprimer' ?>">
                    <i class="bi bi-printer-fill me-1"></i><?= __('print') ?? 'Imprimer' ?>
                </button>
                <a href="/timetables/pdf?cycle_id=<?= $cycleId ?>&level_id=<?= $levelId ?>&week_id=<?= $weekId ?>&mode=download"
                    class="btn btn-sm btn-danger rounded-pill px-3 py-2 fw-bold shadow-sm">
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
                    Des enseignants ou des salles sont affectés simultanément à des matières différentes au même créneau.
                    (Remarque : Un cours identique au même créneau pour plusieurs classes est autorisé en cours
                    mutualisé/tronc commun).
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Volet d'Affectation Rapide (Palette Glisser-Déposer Offcanvas Lateral + Floating) -->
    <?php if ($canEdit): ?>
        <div class="offcanvas offcanvas-end rounded-start-4 shadow-lg border-0" data-bs-scroll="true" data-bs-backdrop="false" tabindex="-1" id="quickAssignPaletteOffcanvas" aria-labelledby="quickAssignPaletteLabel" style="width: 380px; background: var(--bg-card, #ffffff);">
            <div class="offcanvas-header border-bottom py-3 px-4" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(79, 70, 229, 0.08) 100%);">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="avatar-init bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-xs" style="width: 38px; height: 38px;">
                        <i class="bi bi-hand-index-thumb-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="offcanvas-title fw-black text-main-theme mb-0" id="quickAssignPaletteLabel">Palette d'Affectation Rapide</h6>
                        <span class="text-muted extra-small">Glissez une matière dans la grille</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
            </div>
            <div class="offcanvas-body p-4">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="searchPaletteSubject" class="form-control border-start-0 ps-0 rounded-end-3"
                            placeholder="Rechercher une matière..." onkeyup="filterPaletteSubjects(this.value)">
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted extra-small fw-bold text-uppercase">Matières disponibles</span>
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2.5 py-1 extra-small fw-bold" id="paletteSubjectCount">
                        <?= count($gridData['subjects']) ?> matière(s)
                    </span>
                </div>

                <div id="paletteNoMatchAlert" class="alert alert-warning rounded-3 small p-3 text-center mb-3 d-none">
                    <i class="bi bi-search me-1"></i> Aucune matière ne correspond à votre recherche.
                </div>

                <?php if (empty($gridData['subjects'])): ?>
                    <div class="alert alert-info rounded-3 small p-3 text-center mb-0">
                        <i class="bi bi-info-circle fs-4 text-info d-block mb-2"></i>
                        <strong>Aucune matière disponible.</strong><br>
                        Toutes les matières sont déjà planifiées ou aucune n'a été rattachée à ce niveau.
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-2.5 overflow-auto pe-1" id="paletteSubjectsContainer" style="max-height: calc(100vh - 210px);">
                        <?php foreach ($gridData['subjects'] as $sub): ?>
                            <?php $sColor = !empty($sub['couleur_hex']) ? $sub['couleur_hex'] : '#3b82f6'; ?>
                            <div class="palette-subject-chip card border rounded-3 p-3 d-flex flex-row align-items-center justify-content-between cursor-grab shadow-xs transition-all hover-lift"
                                style="background-color: <?= $sColor ?>0d; border-color: <?= $sColor ?>40 !important;"
                                draggable="true"
                                onclick="selectPaletteSubject(<?= json_encode(['id' => $sub['id'], 'nom' => $sub['nom'], 'code' => $sub['code'] ?? '', 'color' => $sColor]) ?>, this)"
                                ondragstart="handlePaletteDragStart(event, <?= json_encode(['id' => $sub['id'], 'nom' => $sub['nom'], 'code' => $sub['code'] ?? '', 'color' => $sColor]) ?>)"
                                ondragend="handleDragEnd(event)">
                                <div class="d-flex align-items-center gap-3 overflow-hidden me-2">
                                    <i class="bi bi-grip-vertical fs-5 text-muted opacity-50 flex-shrink-0"></i>
                                    <div class="badge rounded-circle p-2 flex-shrink-0" style="background-color: <?= $sColor ?>;"></div>
                                    <div class="text-truncate">
                                        <h6 class="fw-bold text-main-theme mb-0 text-truncate" style="font-size: 0.92rem;"><?= h($sub['nom']) ?></h6>
                                        <span class="extra-small text-muted font-monospace"><?= h($sub['code'] ?? 'UV') ?></span>
                                    </div>
                                </div>
                                <span class="badge rounded-pill bg-light text-dark border extra-small flex-shrink-0"><i class="bi bi-arrows-move me-1"></i>Glisser</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Palette Inline Escamotable pour Fallback -->
        <div id="quickAssignPalette" class="modern-card border-0 shadow-sm p-3 mb-4 d-none">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar-init bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 32px; height: 32px;">
                        <i class="bi bi-hand-index-thumb-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-main-theme mb-0">Palette d'Affectation Rapide</h6>
                        <span class="text-muted extra-small">Glissez une matière dans la grille</span>
                    </div>
                </div>
                <button type="button" class="btn-close" onclick="toggleQuickAssignPalette()"></button>
            </div>
            <div class="d-flex flex-wrap gap-2 overflow-auto py-1" style="max-height: 120px;">
                <?php foreach ($gridData['subjects'] as $sub): ?>
                    <?php $sColor = !empty($sub['couleur_hex']) ? $sub['couleur_hex'] : '#3b82f6'; ?>
                    <div class="palette-subject-chip badge px-3 py-2 rounded-pill d-flex align-items-center gap-2 cursor-grab shadow-xs text-start transition-all"
                        style="background-color: <?= $sColor ?>1a; color: <?= $sColor ?>; border: 1.5px solid <?= $sColor ?>50;"
                        draggable="true"
                        ondragstart="handlePaletteDragStart(event, <?= json_encode(['id' => $sub['id'], 'nom' => $sub['nom'], 'code' => $sub['code'] ?? '', 'color' => $sColor]) ?>)"
                        ondragend="handleDragEnd(event)">
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
            <table class="table-modern align-middle mb-0 grid-table text-center"
                style="min-width: 950px; border-collapse: separate; border-spacing: 0;">
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
                            <th class="py-3 px-3 text-center text-uppercase fw-black text-primary sticky-header-col"
                                style="min-width: 200px;">
                                <div class="d-flex align-items-center justify-content-center gap-1.5 mb-1">
                                    <span class="fs-6 text-truncate"
                                        title="<?= h($cls['nom']) ?>"><?= h($cls['nom']) ?></span>
                                    <?php if ($isSuperAdmin && $ttObj): ?>
                                        <button type="button"
                                            class="btn btn-sm btn-link text-danger p-0 ms-1 text-decoration-none"
                                            data-bs-toggle="modal" data-bs-target="#deleteTimetableModal"
                                            data-timetable-id="<?= $ttObj['id'] ?>"
                                            data-timetable-title="<?= h($ttObj['titre'] ?? ('Emploi du temps - ' . $cls['nom'])) ?>"
                                            title="Supprimer cet emploi du temps">
                                            <i class="bi bi-trash3-fill fs-6"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <span
                                    class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill extra-small font-normal fw-medium px-2 py-0.5">
                                    <i class="bi bi-people-fill me-1"></i><?= (int) $cls['effectif'] ?> élèves
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
                                    <td rowspan="<?= count($slots) ?>"
                                        class="fw-black text-uppercase align-middle day-cell sticky-col-1">
                                        <div class="fs-6 text-primary tracking-wider text-rotate-270-md py-2"><?= $day ?></div>
                                    </td>
                                <?php endif; ?>

                                <!-- Colonne Horaires (Sticky Left 2) -->
                                <td class="fw-bold py-2.5 slot-time-cell sticky-col-2">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="small fw-black text-main-theme font-monospace"
                                            style="letter-spacing: -0.2px;">
                                            <?= substr($slot['heure_debut'], 0, 5) ?> <span
                                                class="text-muted fw-normal">à</span> <?= substr($slot['heure_fin'], 0, 5) ?>
                                        </div>
                                        <?php if ($isPause): ?>
                                            <span
                                                class="badge pause-badge rounded-pill extra-small fw-bold mt-1 px-2 py-0.5">
                                                <i class="bi bi-cup-hot-fill me-1"></i>PAUSE
                                            </span>
                                        <?php else: ?>
                                            <span
                                                class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-20 rounded-pill extra-small fw-medium mt-1">
                                                <i class="bi bi-hourglass-split me-1"></i><?= (int) $slot['duree_minutes'] ?>m
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Ligne de Pause (Bandeau Sérénité sur toutes les colonnes) -->
                                <?php if ($isPause): ?>
                                    <td colspan="<?= count($classes) ?>" class="p-3 cell-pause-full align-middle">
                                        <div class="d-flex align-items-center justify-content-center gap-3 py-1">
                                            <div class="pause-icon-circle rounded-circle bg-success text-white d-flex align-items-center justify-content-center shadow-xs"
                                                style="width: 32px; height: 32px;">
                                                <i class="bi bi-cup-hot-fill fs-6"></i>
                                            </div>
                                            <div class="text-center">
                                                <span class="fw-black text-uppercase tracking-wider fs-6 pause-text-emerald me-2">PAUSE &
                                                    INTERVALLE</span>
                                                <span
                                                    class="badge pause-badge rounded-pill font-monospace fw-bold px-2.5 py-1">
                                                    <?= substr($slot['heure_debut'], 0, 5) ?> -
                                                    <?= substr($slot['heure_fin'], 0, 5) ?> (<?= (int) $slot['duree_minutes'] ?> min)
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                <?php else: ?>
                                    <!-- Colonnes par Classe -->
                                    <?php foreach ($classes as $cls): ?>
                                        <?php
                                        $classId = (int) $cls['id'];
                                        $entry = $matrix[$day][$slot['id']][$classId] ?? null;
                                        $cellKey = $day . '_' . $slot['id'] . '_' . $classId;
                                        $hasConflict = isset($gridConflicts[$cellKey]);
                                        $conflictMsgs = $gridConflicts[$cellKey] ?? [];
                                        $timetableObj = $timetablesByClass[$classId] ?? null;
                                        $timetableId = $timetableObj ? (int) $timetableObj['id'] : 0;
                                        $subjectColor = !empty($entry['couleur_hex']) ? $entry['couleur_hex'] : '#3b82f6';
                                        ?>

                                        <td class="p-2 grid-cell <?= $hasConflict ? 'conflict-cell' : '' ?>"
                                            data-day="<?= h($day) ?>"
                                            data-slot-id="<?= (int)$slot['id'] ?>"
                                            data-class-id="<?= (int)$classId ?>"
                                            data-class-name="<?= h($cls['nom']) ?>"
                                            data-timetable-id="<?= (int)$timetableId ?>"
                                            tabindex="0" role="button"
                                            aria-label="<?= $entry ? h($entry['subject_name']) . ' avec ' . h($entry['teacher_name']) . ' en ' . h($entry['room_name']) : 'Créneau libre pour ' . h($cls['nom']) ?>"
                                            <?php if ($canEdit): ?>
                                                onclick="handleCellClick(this)"
                                                ondragover="handleCellDragOver(event, '<?= h($day) ?>', <?= (int)$slot['id'] ?>, <?= (int)$classId ?>)"
                                                ondragenter="handleCellDragEnter(event, '<?= h($day) ?>', <?= (int)$slot['id'] ?>, <?= (int)$classId ?>)"
                                                ondragleave="handleCellDragLeave(event)"
                                                ondrop="handleCellDrop(event, '<?= h($day) ?>', <?= (int)$slot['id'] ?>, <?= (int)$classId ?>, '<?= h($cls['nom']) ?>', <?= (int)$timetableId ?>)"
                                            <?php endif; ?>>

                                            <?php if ($entry): ?>
                                                <!-- Carte de cours Modern Canva/M365 SaaS Component -->
                                                <div class="course-card p-2.5 rounded-3 text-start transition-all border h-100 w-100 d-flex flex-column justify-content-between position-relative"
                                                    style="--subject-color: <?= $subjectColor ?>;" <?php if ($canEdit): ?> draggable="true"
                                                        ondragstart="handleCardDragStart(event, <?= json_encode(['timetable_id' => $timetableId, 'slot_id' => $slot['id'], 'day' => $day, 'class_id' => $classId, 'entry' => $entry]) ?>)"
                                                        ondragend="handleDragEnd(event)"
                                                    <?php endif; ?>>

                                                    <!-- Badge Type / Accent Bar + Actions -->
                                                    <div class="d-flex align-items-center justify-content-between gap-1 mb-1.5">
                                                        <span
                                                            class="course-type-badge badge rounded-pill fw-bold text-truncate extra-small px-2 py-0.5"
                                                            style="background-color: <?= $subjectColor ?>1f; color: <?= $subjectColor ?>; border: 1px solid <?= $subjectColor ?>40;">
                                                            <i class="bi bi-journal-bookmark-fill me-1"
                                                                style="font-size: 0.6rem; vertical-align: middle; color: <?= $subjectColor ?>;"></i><?= h($entry['subject_code'] ?? 'COURS') ?>
                                                        </span>

                                                        <div class="d-flex align-items-center gap-1">
                                                            <?php if ($hasConflict): ?>
                                                                <span class="badge bg-danger text-white rounded-circle p-1 shadow-sm"
                                                                    title="<?= h(implode(' | ', $conflictMsgs)) ?>" data-bs-toggle="tooltip">
                                                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                                                </span>
                                                            <?php endif; ?>

                                                            <?php if ($canEdit): ?>
                                                                <button type="button"
                                                                    class="btn-delete-course btn btn-link p-0 flex-shrink-0 border-0 bg-transparent"
                                                                    onclick="event.stopPropagation(); deleteEntry(<?= $timetableId ?>, <?= $slot['id'] ?>, '<?= $day ?>')"
                                                                    title="Libérer ce créneau">
                                                                    <i class="bi bi-x-circle-fill"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <!-- Matière (Titre principal) -->
                                                    <div class="course-title fw-extrabold lh-sm mb-2"
                                                        title="<?= h($entry['subject_name']) ?>">
                                                        <?= h($entry['subject_name']) ?>
                                                    </div>

                                                    <!-- Métadonnées (Enseignant & Salle) -->
                                                    <div class="course-meta d-flex flex-column gap-1 pt-1.5 border-top">
                                                        <div class="d-flex align-items-center gap-1.5 teacher-info"
                                                            title="Enseignant: <?= h($entry['teacher_name']) ?>">
                                                            <i class="bi bi-person-circle text-primary flex-shrink-0"></i>
                                                            <span class="teacher-name text-truncate"><?= h($entry['teacher_name']) ?></span>
                                                        </div>
                                                        <div class="d-flex align-items-center gap-1.5 room-info"
                                                            title="Salle: <?= h($entry['room_name']) ?>">
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
                                                    <div
                                                        class="empty-slot-placeholder p-2 text-center rounded-3 transition-all border border-dashed h-100 w-100 d-flex flex-column align-items-center justify-content-center">
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
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="closeAssignModal()"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="assignForm">
                        <input type="hidden" id="assign_slot_id">
                        <input type="hidden" id="assign_day">
                        <input type="hidden" id="assign_class_id">
                        <input type="hidden" id="assign_timetable_id">
                        <input type="hidden" id="assign_week_id" value="<?= $weekId ?>">

                        <!-- Section Classes Sélectionnées pour ce cours -->
                        <div class="mb-3 p-3 rounded-3 border"
                            style="background: var(--bg-card-secondary, rgba(0,0,0,0.02));">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold text-main-theme small mb-0">
                                    <i
                                        class="bi bi-people-fill me-1 text-primary"></i><?= __('timetables_selected_classes') ?>
                                </label>
                                <span class="badge bg-secondary opacity-75 extra-small" id="selectedClassesCount">1
                                    classe</span>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mb-2" id="selectedClassesContainer">
                                <!-- Populated dynamically by JS -->
                            </div>
                            <div id="noClassesAlert" class="alert alert-danger rounded-3 small p-2 mb-2 d-none">
                                <i class="bi bi-exclamation-octagon-fill me-1"></i>
                                <strong>Attention :</strong> Aucune classe sélectionnée. Veuillez rajouter au moins une
                                classe pour pouvoir enregistrer ce cours.
                            </div>
                            <div id="addClassDropdownContainer" class="mt-2">
                                <select id="add_mutual_class_select" class="form-select form-select-sm rounded-3"
                                    onchange="onAddMutualClass(this)">
                                    <option value=""><?= __('timetables_add_mutual_class') ?></option>
                                </select>
                            </div>
                        </div>

                        <!-- 1. Matière -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-main-theme small">
                                <?= __('timetables_subject_label') ?>
                            </label>
                            <select id="assign_subject_id" class="form-select rounded-3" required
                                onchange="onSubjectChange()">
                                <option value="">-- Chargement des matières... --</option>
                            </select>
                            <div class="form-text extra-small text-muted">Sélectionnez la matière à planifier.</div>
                        </div>

                        <!-- Banner d'avertissement pour matière non rattachée -->
                        <div id="subjectUnattachedNotice"
                            class="alert alert-info rounded-3 small p-2.5 mb-3 d-none border-info">
                            <i class="bi bi-info-circle-fill text-info me-1 fs-6"></i>
                            <span id="subjectUnattachedNoticeText"><strong>Matière non rattachée à cette classe :</strong>
                                Une confirmation vous sera demandée lors de l'enregistrement pour associer automatiquement
                                cette matière à la classe.</span>
                        </div>

                        <!-- Banner d'avertissement pour affectation automatique d'enseignant -->
                        <div id="teacherAutoAssignNotice"
                            class="alert alert-warning rounded-3 small p-2.5 mb-3 d-none border-warning">
                            <i class="bi bi-info-circle-fill text-warning me-1 fs-6"></i>
                            <span id="teacherAutoAssignText"><strong>Avertissement d'affectation (en coulisses) :</strong>
                                Cet enseignant n'est pas encore officiellement affecté à cette matière. Après validation,
                                cette matière sera automatiquement ajoutée à la liste de ses affectations.</span>
                        </div>

                        <!-- 2. Enseignant (Liste de tous les enseignants) -->
                        <div class="mb-3">
                            <label
                                class="form-label fw-bold text-main-theme small d-flex justify-content-between align-items-center">
                                <span><?= __('timetables_teacher_label') ?></span>
                                <button type="button"
                                    class="btn btn-link text-primary p-0 extra-small fw-bold text-decoration-none"
                                    onclick="toggleQuickTeacherInput()">
                                    <i class="bi bi-person-plus-fill me-1"></i>+ Nouvel enseignant
                                </button>
                            </label>
                            <select id="assign_teacher_id" class="form-select rounded-3" required
                                onchange="onTeacherChange()">
                                <option value="">-- Sélectionnez d'abord une matière --</option>
                            </select>
                        </div>


                        <!-- Champ Ajout Rapide d'Enseignant (Masqué par défaut) -->
                        <div id="quickTeacherContainer"
                            class="card card-theme p-3 mb-3 border-primary border-opacity-30 rounded-3 bg-primary bg-opacity-10 d-none">
                            <label class="form-label fw-bold text-main-theme extra-small mb-1">Nom et Prénom du nouvel
                                enseignant *</label>
                            <div class="input-group">
                                <input type="text" id="quick_teacher_name"
                                    class="form-control form-control-sm rounded-start-3" placeholder="Ex: Dr. Martin KAMGA">
                                <button type="button" class="btn btn-sm btn-primary fw-bold rounded-end-3"
                                    onclick="triggerQuickTeacherConfirmation()">
                                    <i class="bi bi-plus-circle me-1"></i>Créer
                                </button>
                            </div>
                        </div>

                        <!-- 3. Salle de classe -->
                        <div class="mb-3">
                            <label
                                class="form-label fw-bold text-main-theme small"><?= __('timetables_room_label') ?></label>
                            <select id="assign_room_id" class="form-select rounded-3" required
                                onchange="checkRealtimeConflict()">
                                <option value="">-- Choisir une salle --</option>
                                <?php foreach ($gridData['rooms'] as $r): ?>
                                    <option value="<?= $r['id'] ?>"><?= h($r['nom']) ?> (<?= h($r['code']) ?> -
                                        <?= $r['capacite'] ?> places)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label
                                class="form-label fw-bold text-main-theme small"><?= __('timetables_card_color') ?></label>
                            <input type="color" id="assign_color" class="form-control form-control-color w-100 rounded-3"
                                value="#3b82f6">
                        </div>

                        <div id="modalConflictFeedback" class="alert alert-danger d-none rounded-3 small mb-0"></div>
                    </form>
                </div>
                <div class="modal-footer border-top p-3" style="background: var(--bg-card);">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal" onclick="closeAssignModal()">Annuler</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"
                        onclick="saveAssignment()">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pop-Up de Confirmation Ajout Rapide d'Enseignant -->
    <div class="modal fade" id="confirmNewTeacherModal" tabindex="-1" style="z-index: 1085;">
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
                        Vous allez créer un nouvel enseignant : <strong id="confirmTeacherNameText"
                            class="text-primary fs-6"></strong>.
                    </p>
                    <div class="alert alert-warning border-0 rounded-3 small p-3 mb-3">
                        <div class="fw-bold mb-2"><i class="bi bi-info-circle-fill me-1"></i> Actions automatiques qui vont
                            être exécutées :</div>
                        <ul class="mb-0 ps-3">
                            <li class="mb-1">Création officielle du compte enseignant dans l'établissement ;</li>
                            <li class="mb-1">Ajout à la liste officielle des enseignants ;</li>
                            <li class="mb-1">Affectation immédiate de la matière <strong
                                    id="confirmSubjectNameText"></strong> à cet enseignant ;</li>
                            <li>Disponibilité immédiate pour la programmation de ce cours.</li>
                        </ul>
                    </div>
                    <p class="small text-muted mb-0">Voulez-vous vraiment poursuivre cette opération ?</p>
                </div>
                <div class="modal-footer border-top p-3" style="background: var(--bg-card);">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm text-dark"
                        onclick="executeQuickTeacherCreation()">
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
                        La matière <strong id="attachModalSubjectName" class="text-primary"></strong> n'est pas encore
                        officiellement rattachée à cette classe.
                    </p>
                    <div class="alert alert-info border-0 rounded-3 small mb-3">
                        <div class="fw-bold mb-2"><i class="bi bi-info-circle-fill me-1"></i> Opérations automatiques lors
                            de la confirmation :</div>
                        <ul class="mb-0 ps-3">
                            <li class="mb-1">Rattachement de la <strong>matière existante</strong> à cette classe (aucune
                                création de doublon) ;</li>
                            <li class="mb-1">Conservation du <strong>coefficient d'origine (Coef: <span
                                        id="attachModalSubjectCoef">1</span>)</strong> ;</li>
                            <li class="mb-1">Enregistrement de l'association <strong>Classe ↔ Matière</strong> pour l'année
                                académique active ;</li>
                            <li>Programmation immédiate du cours sur la grille d'emploi du temps.</li>
                        </ul>
                    </div>
                    <p class="small text-muted mb-0">Souhaitez-vous confirmer le rattachement et poursuivre la programmation
                        du cours ?</p>
                </div>
                <div class="modal-footer border-top p-3 gap-2" style="background: var(--bg-card);">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"
                        onclick="executeConfirmedSubjectAttachment()">
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
                        Êtes-vous sûr de vouloir retirer la classe <strong id="removeClassModalName"
                            class="text-primary"></strong> de cette programmation de cours ?
                    </p>
                    <div class="alert alert-warning border-0 rounded-3 extra-small mb-0 p-2 text-dark">
                        <i class="bi bi-info-circle-fill me-1"></i> <strong>Information :</strong> Cette action retire
                        uniquement la classe de cette séance. La classe et ses données ne seront <strong>pas
                            supprimées</strong> de l'établissement.
                    </div>
                </div>
                <div class="modal-footer border-top p-2 gap-2" style="background: var(--bg-card);">
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3"
                        data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold shadow-sm"
                        onclick="executeRemoveClassFromAssignment()">
                        <i class="bi bi-trash-fill me-1"></i> Retirer
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    function triggerPrintModel(cycleId, levelId, weekId) {
        const cId = cycleId || 0;
        const lId = levelId || 0;
        const wId = weekId || 0;
        const url = `/timetables/pdf?cycle_id=${cId}&level_id=${lId}&week_id=${wId}&mode=print`;
        const newWin = window.open(url, '_blank');
        if (!newWin || newWin.closed || typeof newWin.closed === 'undefined') {
            window.location.href = url;
        }
    }

    function handleCellClick(tdEl) {
        if (!tdEl) return;
        const slotId = parseInt(tdEl.dataset.slotId) || 0;
        const day = tdEl.dataset.day || '';
        const classId = parseInt(tdEl.dataset.classId) || 0;
        const className = tdEl.dataset.className || '';
        const timetableId = parseInt(tdEl.dataset.timetableId) || 0;

        let entry = null;
        if (window.TIMETABLE_DATA && window.TIMETABLE_DATA.matrix && window.TIMETABLE_DATA.matrix[day] && window.TIMETABLE_DATA.matrix[day][slotId]) {
            entry = window.TIMETABLE_DATA.matrix[day][slotId][classId] || null;
        }

        openAssignModal(slotId, day, classId, className, entry, timetableId);
    }

    // Hydratation de l'état local du Frontend pour zéro latence
    window.TIMETABLE_DATA = {
        classes: <?= json_encode($classes) ?>,
        days: <?= json_encode($days) ?>,
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

    // Palette & Drag-and-Drop Functions (Offcanvas Drawer Lateral + Universal Fallback Sans Backdrop Bloquant)
    function toggleQuickAssignPalette() {
        const offcanvasEl = document.getElementById('quickAssignPaletteOffcanvas');
        if (!offcanvasEl) {
            const inlinePalette = document.getElementById('quickAssignPalette');
            if (inlinePalette) {
                inlinePalette.classList.toggle('d-none');
                if (!inlinePalette.classList.contains('d-none')) {
                    inlinePalette.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
            return;
        }

        // 1. Tenter l'ouverture via l'API officielle Bootstrap Offcanvas (sans backdrop, défilement activé)
        if (typeof bootstrap !== 'undefined' && bootstrap.Offcanvas) {
            try {
                const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl, {
                    backdrop: false,
                    scroll: true
                });
                bsOffcanvas.toggle();
                setTimeout(() => {
                    document.querySelectorAll('.offcanvas-backdrop').forEach(b => b.remove());
                    document.body.style.overflow = '';
                }, 50);
                return;
            } catch (e) {
                console.warn("Bootstrap Offcanvas fallback:", e);
            }
        }

        // 2. Fallback JS Pur garanti
        const isShowing = offcanvasEl.classList.contains('show');
        if (isShowing) {
            closeQuickAssignPalette();
        } else {
            openQuickAssignPalette();
        }
    }

    function openQuickAssignPalette() {
        const offcanvasEl = document.getElementById('quickAssignPaletteOffcanvas');
        if (!offcanvasEl) return;

        offcanvasEl.classList.add('show');
        offcanvasEl.style.visibility = 'visible';
        offcanvasEl.style.transform = 'none';

        // Supprimer tout backdrop pour débloquer l'écran
        document.querySelectorAll('.offcanvas-backdrop, #quickAssignBackdrop').forEach(b => b.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
    }

    function closeQuickAssignPalette() {
        const offcanvasEl = document.getElementById('quickAssignPaletteOffcanvas');
        if (offcanvasEl) {
            offcanvasEl.classList.remove('show');
            offcanvasEl.style.transform = '';
            offcanvasEl.style.visibility = '';
        }

        document.querySelectorAll('.offcanvas-backdrop, #quickAssignBackdrop').forEach(b => b.remove());
    }

    function filterPaletteSubjects(query) {
        const q = query.toLowerCase().trim();
        let visibleCount = 0;
        document.querySelectorAll('.palette-subject-chip').forEach(chip => {
            const text = chip.textContent.toLowerCase();
            if (text.includes(q)) {
                chip.classList.remove('d-none');
                chip.classList.add('d-flex');
                visibleCount++;
            } else {
                chip.classList.add('d-none');
                chip.classList.remove('d-flex');
            }
        });

        const countBadge = document.getElementById('paletteSubjectCount');
        if (countBadge) {
            countBadge.textContent = `${visibleCount} matière(s)`;
        }

        const noMatchAlert = document.getElementById('paletteNoMatchAlert');
        if (noMatchAlert) {
            if (visibleCount === 0) {
                noMatchAlert.classList.remove('d-none');
            } else {
                noMatchAlert.classList.add('d-none');
            }
        }
    }

    // Toast Notification System pour les Opérations de la Grille
    function showGridToast(type, title, message) {
        let container = document.getElementById('gridToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'gridToastContainer';
            container.className = 'grid-toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        const bgClass = type === 'success' ? 'bg-success text-white' : (type === 'danger' ? 'bg-danger text-white' : 'bg-primary text-white');
        const iconClass = type === 'success' ? 'bi-check-circle-fill' : (type === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill');

        toast.className = `grid-toast-item toast show align-items-center ${bgClass} border-0 shadow-lg rounded-3 p-1 mb-2`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        toast.innerHTML = `
            <div class="d-flex p-2 align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi ${iconClass} fs-5"></i>
                    <div>
                        <strong class="d-block small lh-1 fw-bold">${title}</strong>
                        <span class="extra-small opacity-90">${message}</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-1" onclick="this.closest('.grid-toast-item').remove()"></button>
            </div>
        `;

        container.appendChild(toast);
        setTimeout(() => {
            if (toast && toast.parentNode) {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }
        }, 4000);
    }

    function handlePaletteDragStart(e, subjectObj) {
        draggedData = { type: 'PALETTE_SUBJECT', subject: subjectObj };
        window.draggedData = draggedData;
        window.currentDraggedSubject = subjectObj;
        window.selectedPaletteSubject = subjectObj;

        try {
            e.dataTransfer.setData('text/plain', JSON.stringify(draggedData));
            e.dataTransfer.setData('application/json', JSON.stringify(draggedData));
        } catch (err) {}

        e.dataTransfer.effectAllowed = 'copy';
        document.body.classList.add('is-dragging-active');

        const chip = e.target.closest('.palette-subject-chip') || e.target;
        if (chip && chip.classList) {
            chip.classList.add('dragging-chip');
        }
    }

    function handleCardDragStart(e, cardObj) {
        e.stopPropagation();
        draggedData = { type: 'EXISTING_CARD', card: cardObj };
        try {
            e.dataTransfer.setData('text/plain', JSON.stringify(draggedData));
        } catch (err) {}
        e.dataTransfer.effectAllowed = 'move';
        document.body.classList.add('is-dragging-active');
        if (e.target && e.target.classList) {
            e.target.classList.add('dragging-card');
        }
    }

    function handleDragEnd(e) {
        document.body.classList.remove('is-dragging-active');
        document.querySelectorAll('.dragging-chip, .dragging-card').forEach(el => el.classList.remove('dragging-chip', 'dragging-card'));
        document.querySelectorAll('.grid-cell').forEach(cell => {
            cell.classList.remove('drop-target-valid', 'drop-target-invalid', 'bg-primary', 'bg-opacity-10', 'border-primary');
        });
    }

    function handleCellDragEnter(e, day, slotId, classId) {
        e.preventDefault();
        const isPause = e.currentTarget.parentElement && e.currentTarget.parentElement.classList.contains('bg-pause-row');
        if (isPause) {
            e.currentTarget.classList.add('drop-target-invalid');
            e.currentTarget.classList.remove('drop-target-valid');
        } else {
            e.currentTarget.classList.add('drop-target-valid');
            e.currentTarget.classList.remove('drop-target-invalid');
        }
    }

    function handleCellDragOver(e, day, slotId, classId) {
        e.preventDefault();
        const isPause = e.currentTarget.parentElement && e.currentTarget.parentElement.classList.contains('bg-pause-row');
        if (isPause) {
            e.dataTransfer.dropEffect = 'none';
            e.currentTarget.classList.add('drop-target-invalid');
            e.currentTarget.classList.remove('drop-target-valid');
        } else {
            e.dataTransfer.dropEffect = (draggedData && draggedData.type === 'EXISTING_CARD') ? 'move' : 'copy';
            e.currentTarget.classList.add('drop-target-valid');
            e.currentTarget.classList.remove('drop-target-invalid');
        }
    }

    function handleCellDragLeave(e) {
        if (!e.currentTarget.contains(e.relatedTarget)) {
            e.currentTarget.classList.remove('drop-target-valid', 'drop-target-invalid', 'bg-primary', 'bg-opacity-10', 'border-primary');
        }
    }

    function handleCellDrop(e, targetDay, targetSlotId, targetClassId, targetClassName, targetTimetableId) {
        e.preventDefault();
        handleDragEnd(e);

        const isPause = e.currentTarget.parentElement && e.currentTarget.parentElement.classList.contains('bg-pause-row');
        if (isPause) {
            showGridToast('danger', 'Déplacement Bloqué', 'Ce créneau horaire est une PAUSE. Aucun cours ne peut y être planifié.');
            return;
        }

        if (!draggedData && e.dataTransfer) {
            try {
                const raw = e.dataTransfer.getData('text/plain') || e.dataTransfer.getData('application/json');
                if (raw) draggedData = JSON.parse(raw);
            } catch (err) {}
        }

        if (!draggedData) {
            draggedData = window.draggedData;
        }

        if (!draggedData && window.currentDraggedSubject) {
            draggedData = { type: 'PALETTE_SUBJECT', subject: window.currentDraggedSubject };
        }

        if (!draggedData) return;

        if (draggedData.type === 'PALETTE_SUBJECT') {
            const sub = draggedData.subject;
            openAssignModal(targetSlotId, targetDay, targetClassId, targetClassName, null, targetTimetableId);

            setTimeout(() => {
                const subSelect = document.getElementById('assign_subject_id');
                if (subSelect) {
                    subSelect.value = sub.id;
                    onSubjectChange();

                    // Auto-sélection intelligente de l'enseignant habilité
                    const teacherSelect = document.getElementById('assign_teacher_id');
                    if (teacherSelect && (!teacherSelect.value || teacherSelect.value === '')) {
                        const autoTeacherId = getHabilitatedTeacherId(sub.id);
                        if (autoTeacherId) {
                            teacherSelect.value = autoTeacherId;
                            onTeacherChange();
                        }
                    }

                    // Auto-sélection d'une salle libre
                    const roomSelect = document.getElementById('assign_room_id');
                    if (roomSelect && (!roomSelect.value || roomSelect.value === '')) {
                        const freeRoomId = getFreeRoomId(targetDay, targetSlotId);
                        if (freeRoomId) {
                            roomSelect.value = freeRoomId;
                        }
                    }

                    checkRealtimeConflict();
                }
                showGridToast('info', 'Préparation du Cours', `Affectation de ${sub.nom} pour ${targetClassName} (${targetDay}). Enseignant & salle pré-remplis !`);
            }, 100);

        } else if (draggedData.type === 'EXISTING_CARD') {
            const source = draggedData.card;
            if (source.day === targetDay && source.slot_id === targetSlotId && source.class_id === targetClassId) return;

            const entry = source.entry;
            const payload = {
                timetable_id: targetTimetableId || source.timetable_id,
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
                        }).then(() => {
                            showGridToast('success', 'Cours Déplacé', `Le cours de ${entry.subject_name} a été déplacé vers ${targetClassName} (${targetDay}).`);
                            setTimeout(() => location.reload(), 300);
                        });
                    } else {
                        showGridToast('danger', 'Conflit de Déplacement', res.message || 'Impossible de déplacer ce cours.');
                    }
                })
                .catch(err => {
                    showGridToast('danger', 'Erreur Réseau', 'Erreur lors de la communication avec le serveur.');
                });
        }

        draggedData = null;
    }

    // Support des Raccourcis Clavier
    document.addEventListener('keydown', function (e) {
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
                removeBtn.onclick = function () {
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

    function selectPaletteSubject(subjectObj, chipEl) {
        window.selectedPaletteSubject = subjectObj;
        window.draggedData = { type: 'PALETTE_SUBJECT', subject: subjectObj };
        document.querySelectorAll('.palette-subject-chip').forEach(c => {
            c.classList.remove('border-primary', 'shadow-md', 'border-2');
        });
        if (chipEl) {
            chipEl.classList.add('border-primary', 'shadow-md', 'border-2');
        }
        showGridToast('info', 'Matière Sélectionnée', `Matière "${subjectObj.nom}" sélectionnée. Cliquez sur n'importe quel créneau libre de la grille pour planifier !`);
    }

    function getHabilitatedTeacherId(subjectId) {
        if (!window.TIMETABLE_DATA || !window.TIMETABLE_DATA.teachers) return null;
        const teachers = window.TIMETABLE_DATA.teachers;
        const assigned = teachers.find(t => t.is_assigned == 1);
        if (assigned) return assigned.id;
        return teachers.length > 0 ? teachers[0].id : null;
    }

    function getFreeRoomId(day, slotId) {
        if (!window.TIMETABLE_DATA || !window.TIMETABLE_DATA.rooms) return null;
        const rooms = window.TIMETABLE_DATA.rooms;
        if (!rooms || rooms.length === 0) return null;
        const matrix = (window.TIMETABLE_DATA && window.TIMETABLE_DATA.matrix) ? window.TIMETABLE_DATA.matrix : {};
        
        for (let r of rooms) {
            let isOccupied = false;
            if (matrix[day] && matrix[day][slotId]) {
                for (let cId in matrix[day][slotId]) {
                    if (matrix[day][slotId][cId] && matrix[day][slotId][cId].room_id == r.id) {
                        isOccupied = true;
                        break;
                    }
                }
            }
            if (!isOccupied) return r.id;
        }
        return rooms[0].id;
    }

    function closeAssignModal() {
        const modalEl = document.getElementById('assignModal');
        if (!modalEl) return;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalObj = bootstrap.Modal.getInstance(modalEl);
            if (modalObj) modalObj.hide();
        }
        modalEl.classList.remove('show');
        modalEl.style.display = 'none';
        modalEl.setAttribute('aria-hidden', 'true');
        modalEl.removeAttribute('aria-modal');
        document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
    }

    function openAssignModal(slotId, day, classId, className, entry, timetableId) {
        try {
            let modalEl = document.getElementById('assignModal');
            if (!modalEl) {
                console.error("Élément #assignModal introuvable dans le DOM.");
                return;
            }

            // Déplacer la modale à la racine de document.body pour éviter l'occlusion par stacking context (.animate-fade-in)
            if (modalEl.parentNode !== document.body) {
                document.body.appendChild(modalEl);
            }
            modalEl.style.zIndex = '1065';

            const slotIn = document.getElementById('assign_slot_id');
            const dayIn = document.getElementById('assign_day');
            const classIn = document.getElementById('assign_class_id');
            const ttIn = document.getElementById('assign_timetable_id');

            if (slotIn) slotIn.value = slotId;
            if (dayIn) dayIn.value = day;
            if (classIn) classIn.value = classId;
            if (ttIn) ttIn.value = timetableId || 0;
            currentEntryData = entry;

            selectedClasses = [{ id: classId, nom: className }];

            if (entry && typeof window.TIMETABLE_DATA !== 'undefined' && window.TIMETABLE_DATA.matrix) {
                const matrix = window.TIMETABLE_DATA.matrix;
                if (typeof allGridClasses !== 'undefined' && Array.isArray(allGridClasses)) {
                    allGridClasses.forEach(cls => {
                        if (cls.id !== classId) {
                            const cellEntry = matrix && matrix[day] && matrix[day][slotId] ? matrix[day][slotId][cls.id] : null;
                            if (cellEntry && cellEntry.subject_id == entry.subject_id && cellEntry.teacher_id == entry.teacher_id && cellEntry.room_id == entry.room_id) {
                                selectedClasses.push({ id: cls.id, nom: cls.nom });
                            }
                        }
                    });
                }
            }

            initialClassIds = selectedClasses.map(c => c.id);
            renderSelectedClassesUI();

            const headerEl = document.getElementById('modalTargetClassHeader');
            if (headerEl) headerEl.innerText = `Jour : ${day}`;

            const feedbackEl = document.getElementById('modalConflictFeedback');
            if (feedbackEl) {
                feedbackEl.classList.add('d-none');
                feedbackEl.innerHTML = '';
            }

            const quickTeacherEl = document.getElementById('quickTeacherContainer');
            if (quickTeacherEl) quickTeacherEl.classList.add('d-none');

            const autoNoticeEl = document.getElementById('teacherAutoAssignNotice');
            if (autoNoticeEl) autoNoticeEl.classList.add('d-none');

            const subSelect = document.getElementById('assign_subject_id');
            if (subSelect) {
                subSelect.innerHTML = '<option value="">-- Choisir une matière --</option>';

                if (window.TIMETABLE_DATA && window.TIMETABLE_DATA.subjects) {
                    window.TIMETABLE_DATA.subjects.forEach(s => {
                        const sel = (entry && entry.subject_id == s.id) ? 'selected' : '';
                        const isAttached = (s.is_attached == 1 || typeof s.is_attached === 'undefined');
                        const badge = isAttached ? '' : ' [Non rattachée]';
                        subSelect.innerHTML += `<option value="${s.id}" data-is-attached="${isAttached ? 1 : 0}" data-color="${s.couleur_hex || '#3b82f6'}" data-coef="${s.coefficient || 1}" ${sel}>${escapeHtml(s.nom)} (${escapeHtml(s.code || 'UV')})${badge}</option>`;
                    });
                }

                if (entry && entry.subject_id) {
                    subSelect.value = entry.subject_id;
                    onSubjectChange(entry.teacher_id);
                } else if (window.selectedPaletteSubject) {
                    const activeSub = window.selectedPaletteSubject;
                    subSelect.value = activeSub.id;
                    onSubjectChange();

                    const autoTeacherId = getHabilitatedTeacherId(activeSub.id);
                    if (autoTeacherId) {
                        const tSel = document.getElementById('assign_teacher_id');
                        if (tSel) tSel.value = autoTeacherId;
                        onTeacherChange();
                    }

                    const freeRoomId = getFreeRoomId(day, slotId);
                    if (freeRoomId) {
                        const rSel = document.getElementById('assign_room_id');
                        if (rSel) rSel.value = freeRoomId;
                    }

                    checkRealtimeConflict();
                    showGridToast('success', 'Matière Positionnée', `Matière ${activeSub.nom} pré-sélectionnée pour ${className}.`);
                    window.selectedPaletteSubject = null;
                    document.querySelectorAll('.palette-subject-chip').forEach(c => c.classList.remove('border-primary', 'shadow-md', 'border-2'));
                } else {
                    const tSel = document.getElementById('assign_teacher_id');
                    if (tSel) tSel.innerHTML = '<option value="">-- Sélectionnez d\'abord une matière --</option>';
                }
            }

            const roomSel = document.getElementById('assign_room_id');
            const colorSel = document.getElementById('assign_color');

            if (entry) {
                if (roomSel) roomSel.value = entry.room_id || '';
                if (colorSel) colorSel.value = entry.couleur_hex || '#3b82f6';
            } else if (!window.selectedPaletteSubject) {
                if (roomSel) roomSel.value = '';
                if (colorSel) colorSel.value = '#3b82f6';
            }

            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';

            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modalObj = bootstrap.Modal.getOrCreateInstance(modalEl);
                modalObj.show();
            }
            
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
            modalEl.removeAttribute('aria-hidden');
            modalEl.setAttribute('aria-modal', 'true');
        } catch (err) {
            console.error("Erreur lors de l'ouverture de la modale d'affectation:", err);
            const modalEl = document.getElementById('assignModal');
            if (modalEl) {
                if (modalEl.parentNode !== document.body) document.body.appendChild(modalEl);
                modalEl.classList.add('show');
                modalEl.style.display = 'block';
                modalEl.style.zIndex = '1065';
            }
        }
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

        const confirmModalEl = document.getElementById('confirmNewTeacherModal');
        if (confirmModalEl.parentNode !== document.body) {
            document.body.appendChild(confirmModalEl);
        }
        confirmModalEl.style.zIndex = '1085';

        const confirmModal = new bootstrap.Modal(confirmModalEl);
        confirmModalEl.addEventListener('shown.bs.modal', function () {
            const backdrops = document.querySelectorAll('.modal-backdrop');
            if (backdrops.length > 1) {
                backdrops[backdrops.length - 1].style.zIndex = '1080';
            }
        }, { once: true });
        confirmModal.show();
    }

    function executeQuickTeacherCreation() {
        const nameInput = document.getElementById('quick_teacher_name');
        const name = nameInput ? nameInput.value.trim() : '';
        const subjectSelect = document.getElementById('assign_subject_id');
        const subjectId = subjectSelect ? subjectSelect.value : 0;
        
        let classId = 0;
        if (typeof selectedClasses !== 'undefined' && selectedClasses.length > 0) {
            classId = selectedClasses[0].id;
        } else if (document.getElementById('assign_class_id')) {
            classId = document.getElementById('assign_class_id').value;
        } else if (window.TIMETABLE_DATA && window.TIMETABLE_DATA.class_id) {
            classId = window.TIMETABLE_DATA.class_id;
        }

        fetch('/timetables/api/quick-create-teacher', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nom_complet: name, subject_id: subjectId, class_id: classId })
        })
            .then(r => r.json())
            .then(res => {
                if (res.success && res.teacher) {
                    const t = res.teacher;
                    if (window.TIMETABLE_DATA && window.TIMETABLE_DATA.teachers) {
                        const existsInArray = window.TIMETABLE_DATA.teachers.some(existing => existing.id == t.id);
                        if (!existsInArray) {
                            window.TIMETABLE_DATA.teachers.push({ id: t.id, nom_complet: t.nom_complet, role: t.role, is_assigned: 1 });
                        }
                    }

                    const teacherSelect = document.getElementById('assign_teacher_id');
                    if (teacherSelect) {
                        let existingOpt = Array.from(teacherSelect.options).find(opt => opt.value == t.id);
                        if (!existingOpt) {
                            const label = t.nom_complet + (res.already_existed ? '' : ' (Nouvellement créé)');
                            existingOpt = new Option(label, t.id, true, true);
                            teacherSelect.add(existingOpt, teacherSelect.options[1] || null);
                        }
                        teacherSelect.value = t.id;
                    }

                    const quickContainer = document.getElementById('quickTeacherContainer');
                    if (quickContainer) quickContainer.classList.add('d-none');
                    if (nameInput) nameInput.value = '';

                    const confirmModalEl = document.getElementById('confirmNewTeacherModal');
                    if (confirmModalEl) {
                        const modalObj = bootstrap.Modal.getInstance(confirmModalEl);
                        if (modalObj) modalObj.hide();
                    }

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
    /* Drag & Drop Visual Feedback Styles & User Select Fix */
    .palette-subject-chip {
        user-select: none !important;
        -webkit-user-select: none !important;
        -moz-user-select: none !important;
        -ms-user-select: none !important;
        -webkit-user-drag: element !important;
        cursor: grab !important;
        touch-action: none;
    }

    .palette-subject-chip:active {
        cursor: grabbing !important;
    }

    .palette-subject-chip * {
        user-select: none !important;
        -webkit-user-select: none !important;
        pointer-events: none !important;
    }

    body.is-dragging-active .grid-cell * {
        pointer-events: none !important;
    }

    .grid-cell {
        cursor: pointer !important;
    }

    .empty-slot-placeholder {
        cursor: pointer !important;
    }

    .empty-slot-placeholder * {
        pointer-events: none !important;
    }

    .grid-cell.drop-target-valid {
        background-color: rgba(16, 185, 129, 0.15) !important;
        outline: 2.5px dashed #10b981 !important;
        outline-offset: -3px;
        box-shadow: inset 0 0 15px rgba(16, 185, 129, 0.25) !important;
        transition: all 0.15s ease-in-out;
    }

    .grid-cell.drop-target-invalid {
        background-color: rgba(239, 68, 68, 0.15) !important;
        outline: 2.5px dashed #ef4444 !important;
        outline-offset: -3px;
        box-shadow: inset 0 0 15px rgba(239, 68, 68, 0.25) !important;
        cursor: not-allowed !important;
        transition: all 0.15s ease-in-out;
    }

    .palette-subject-chip.dragging-chip,
    .course-card.dragging-card {
        opacity: 0.5 !important;
        transform: scale(0.96);
        box-shadow: 0 8px 20px rgba(0,0,0,0.18) !important;
    }

    /* Toast Float Container */
    .grid-toast-container {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 999999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        pointer-events: none;
    }

    .grid-toast-item {
        pointer-events: auto;
        min-width: 280px;
        max-width: 400px;
        animation: slideInRight 0.3s ease-out forwards;
    }

    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

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

    .grid-table td,
    .grid-table th {
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

    .grid-cell:hover,
    .grid-cell:focus {
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

    .pause-text-emerald {
        color: #047857 !important;
    }

    .pause-badge {
        background-color: rgba(16, 185, 129, 0.18) !important;
        color: #047857 !important;
        border: 1px solid rgba(16, 185, 129, 0.35) !important;
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
        0% {
            box-shadow: inset 0 0 0 2px #ef4444;
        }

        50% {
            box-shadow: inset 0 0 0 4px rgba(239, 68, 68, 0.4);
        }

        100% {
            box-shadow: inset 0 0 0 2px #ef4444;
        }
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

    [data-theme="dark"] .grid-table td,
    [data-theme="dark"] .grid-table th {
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

    [data-theme="dark"] .grid-cell:hover,
    [data-theme="dark"] .grid-cell:focus {
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

    [data-theme="dark"] .pause-text-emerald {
        color: #34d399 !important;
    }

    [data-theme="dark"] .pause-badge {
        background-color: rgba(16, 185, 129, 0.3) !important;
        color: #34d399 !important;
        border: 1px solid rgba(52, 211, 153, 0.4) !important;
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

        .btn,
        button,
        nav,
        header,
        sidebar,
        footer,
        .modal,
        #quickAssignPalette,
        .btn-delete-course {
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

        .grid-table th,
        .grid-table td {
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
                        <p class="mb-2 text-main-theme fw-semibold" style="font-size: 1rem;">Êtes-vous sûr de vouloir
                            supprimer définitivement cet emploi du temps ?</p>
                        <div class="alert alert-danger border-0 rounded-3 small mb-0 d-flex gap-2 align-items-start">
                            <i class="bi bi-shield-alert fs-5 flex-shrink-0 mt-0.5"></i>
                            <div>
                                <strong>Attention :</strong> Cette action est irréversible. Tous les créneaux positionnés et
                                le journal d'audit associés à cet emploi du temps (<strong
                                    id="delete_timetable_title"></strong>) seront supprimés.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 pb-4 px-4 gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">
                            <i class="bi bi-trash3-fill me-1"></i> Supprimer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteModal = document.getElementById('deleteTimetableModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function (event) {
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

<!-- Modal Assistant Planification en Masse -->
<?php if ($canEdit): ?>
<div class="modal fade" id="bulkScheduleModal" tabindex="-1" aria-labelledby="bulkScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" style="background: var(--bg-card, #ffffff);">
            
            <!-- Modal Header -->
            <div class="modal-header border-bottom px-4 py-3" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.08) 0%, rgba(59, 130, 246, 0.08) 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-icon bg-primary text-white rounded-3 p-2.5 shadow-sm d-flex align-items-center justify-content-center">
                        <i class="bi bi-layers-half fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-black text-main-theme mb-0" id="bulkScheduleModalLabel">Assistant de Planification en Masse</h5>
                        <p class="text-muted small mb-0">Programmez plusieurs créneaux, classes et jours en une seule opération intelligente.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Stepper Indicator Header -->
            <div class="bg-body-tertiary px-4 py-2 border-bottom d-flex align-items-center justify-content-center gap-4 text-center">
                <div class="step-indicator active d-flex align-items-center gap-2" id="stepIndicator1">
                    <span class="badge bg-primary rounded-circle px-2 py-1">1</span>
                    <span class="fw-bold text-primary small">Configuration & Sélections Multiples</span>
                </div>
                <i class="bi bi-chevron-right text-muted"></i>
                <div class="step-indicator d-flex align-items-center gap-2 opacity-50" id="stepIndicator2">
                    <span class="badge bg-secondary rounded-circle px-2 py-1">2</span>
                    <span class="fw-bold text-muted small">Prévisualisation & Validation des Conflits</span>
                </div>
            </div>

            <div class="modal-body p-4">
                
                <!-- ÉTAPE 1 : Configuration -->
                <div id="bulkStep1">
                    <form id="bulkConfigForm" onsubmit="event.preventDefault(); onBulkAnalyzeSubmit();">
                        
                        <!-- Ligne 1 : Matière & Enseignant -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-main-theme small">
                                    <i class="bi bi-journal-bookmark-fill me-1 text-primary"></i>Matière <span class="text-danger">*</span>
                                </label>
                                <select id="bulk_subject_id" class="form-select rounded-3 shadow-xs" required onchange="onBulkSubjectChange(this.value)">
                                    <option value="">-- Sélectionner une matière --</option>
                                    <?php foreach ($gridData['subjects'] as $sub): ?>
                                        <option value="<?= $sub['id'] ?>" data-color="<?= htmlspecialchars($sub['couleur_hex']) ?>">
                                            <?= htmlspecialchars($sub['nom']) ?> (<?= htmlspecialchars($sub['code'] ?? 'UE') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <label class="form-label fw-bold text-main-theme small mb-0">
                                        <i class="bi bi-person-badge-fill me-1 text-primary"></i>Enseignant <span class="text-danger">*</span>
                                    </label>
                                </div>
                                <select id="bulk_teacher_id" class="form-select rounded-3 shadow-xs" required>
                                    <option value="">-- Sélectionner un enseignant --</option>
                                    <?php foreach ($gridData['teachers'] as $t): ?>
                                        <option value="<?= $t['id'] ?>">
                                            <?= htmlspecialchars($t['nom_complet']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Section 2 : Jours (Multi-sélection Badge Chips) -->
                        <div class="mb-4 p-3 rounded-4 border bg-body-tertiary">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label fw-bold text-main-theme small mb-0">
                                    <i class="bi bi-calendar-week-fill me-1 text-primary"></i>Jours de la semaine (Multi-sélection) <span class="text-danger">*</span>
                                </label>
                                <button type="button" class="btn btn-xs btn-link text-primary text-decoration-none fw-bold p-0" onclick="toggleAllChips('days')">
                                    <i class="bi bi-check-all me-1"></i>Tout Sélectionner / Désélectionner
                                </button>
                            </div>
                            <div class="d-flex flex-wrap gap-2" id="bulkDaysContainer">
                                <?php foreach ($days as $day): ?>
                                    <div class="chip-badge chip-toggle px-3 py-2 rounded-pill border fw-bold cursor-pointer transition-all d-flex align-items-center gap-1.5"
                                         data-group="days" data-value="<?= $day ?>" onclick="toggleChip(this)">
                                        <i class="bi bi-calendar2-day text-primary"></i>
                                        <span><?= $day ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Section 3 : Créneaux Horaires (Multi-sélection Badge Chips) -->
                        <div class="mb-4 p-3 rounded-4 border bg-body-tertiary">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label fw-bold text-main-theme small mb-0">
                                    <i class="bi bi-clock-history me-1 text-primary"></i>Créneaux Horaires (Multi-sélection) <span class="text-danger">*</span>
                                </label>
                                <button type="button" class="btn btn-xs btn-link text-primary text-decoration-none fw-bold p-0" onclick="toggleAllChips('slots')">
                                    <i class="bi bi-check-all me-1"></i>Tout Sélectionner / Désélectionner
                                </button>
                            </div>
                            <div class="d-flex flex-wrap gap-2" id="bulkSlotsContainer">
                                <?php foreach ($gridData['slots'] as $slot): ?>
                                    <?php if ($slot['type_creneau'] === 'pause') continue; ?>
                                    <div class="chip-badge chip-toggle px-3 py-2 rounded-pill border fw-bold cursor-pointer transition-all d-flex align-items-center gap-1.5"
                                         data-group="slots" data-value="<?= $slot['id'] ?>" onclick="toggleChip(this)">
                                        <i class="bi bi-clock text-info"></i>
                                        <span><?= substr($slot['heure_debut'], 0, 5) ?> - <?= substr($slot['heure_fin'], 0, 5) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Section 4 : Classes (Multi-sélection Badge Chips) -->
                        <div class="mb-4 p-3 rounded-4 border bg-body-tertiary">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label fw-bold text-main-theme small mb-0">
                                    <i class="bi bi-people-fill me-1 text-primary"></i>Classes Concernées (Multi-sélection) <span class="text-danger">*</span>
                                </label>
                                <button type="button" class="btn btn-xs btn-link text-primary text-decoration-none fw-bold p-0" onclick="toggleAllChips('classes')">
                                    <i class="bi bi-check-all me-1"></i>Toutes les Classes
                                </button>
                            </div>
                            <div class="d-flex flex-wrap gap-2" id="bulkClassesContainer">
                                <?php foreach ($classes as $cls): ?>
                                    <div class="chip-badge chip-toggle px-3 py-2 rounded-pill border fw-bold cursor-pointer transition-all d-flex align-items-center gap-1.5 active"
                                         data-group="classes" data-value="<?= $cls['id'] ?>" onclick="toggleChip(this)">
                                        <i class="bi bi-mortarboard-fill text-success"></i>
                                        <span><?= htmlspecialchars($cls['nom']) ?></span>
                                        <span class="badge bg-secondary rounded-pill extra-small"><?= (int)$cls['effectif'] ?> élèves</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Section 5 : Affectation des Salles -->
                        <div class="mb-4 p-3 rounded-4 border bg-body-tertiary">
                            <label class="form-label fw-bold text-main-theme small mb-3">
                                <i class="bi bi-geo-alt-fill me-1 text-primary"></i>Mode d'Affectation des Salles <span class="text-danger">*</span>
                            </label>
                            
                            <div class="row g-3">
                                <!-- Mode 1 : Auto -->
                                <div class="col-md-4">
                                    <div class="card h-100 border p-3 rounded-3 cursor-pointer room-mode-card active" id="cardRoomModeAuto" onclick="setRoomMode('auto')">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="bulk_room_mode" id="modeAuto" value="auto" checked>
                                            <label class="form-check-label fw-bold text-main-theme cursor-pointer" for="modeAuto">
                                                <i class="bi bi-magic text-primary me-1"></i>Affectation Automatique
                                            </label>
                                        </div>
                                        <p class="extra-small text-muted mb-0 mt-2">Le moteur recherche et attribue automatiquement des salles libres sans conflit pour chaque classe.</p>
                                    </div>
                                </div>

                                <!-- Mode 2 : Mutualisé (TC) -->
                                <div class="col-md-4">
                                    <div class="card h-100 border p-3 rounded-3 cursor-pointer room-mode-card" id="cardRoomModeMutualized" onclick="setRoomMode('mutualized')">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="bulk_room_mode" id="modeMutualized" value="mutualized">
                                            <label class="form-check-label fw-bold text-main-theme cursor-pointer" for="modeMutualized">
                                                <i class="bi bi-building-fill text-warning me-1"></i>Cours Mutualisé / TC
                                            </label>
                                        </div>
                                        <p class="extra-small text-muted mb-0 mt-2">Une salle unique est partagée simultanément par toutes les classes sélectionnées.</p>
                                    </div>
                                </div>

                                <!-- Mode 3 : Custom Pool -->
                                <div class="col-md-4">
                                    <div class="card h-100 border p-3 rounded-3 cursor-pointer room-mode-card" id="cardRoomModePool" onclick="setRoomMode('custom_pool')">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="bulk_room_mode" id="modeCustomPool" value="custom_pool">
                                            <label class="form-check-label fw-bold text-main-theme cursor-pointer" for="modeCustomPool">
                                                <i class="bi bi-door-open-fill text-info me-1"></i>Pool de Salles Spécifiques
                                            </label>
                                        </div>
                                        <p class="extra-small text-muted mb-0 mt-2">Sélectionnez une liste restreinte de salles parmi lesquelles distribuer les cours.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Options dynamiques de Salle selon le mode -->
                            <div id="roomModeMutualizedOptions" class="mt-3 p-3 rounded-3 border bg-white d-none">
                                <label class="form-label fw-bold small text-main-theme">Choisir la salle unique pour le cours mutualisé :</label>
                                <select id="bulk_single_room_id" class="form-select form-select-sm rounded-3">
                                    <?php foreach ($gridData['rooms'] as $rm): ?>
                                        <option value="<?= $rm['id'] ?>"><?= htmlspecialchars($rm['nom']) ?> (Capacité: <?= (int)$rm['capacite'] ?> places)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div id="roomModePoolOptions" class="mt-3 p-3 rounded-3 border bg-white d-none">
                                <label class="form-label fw-bold small text-main-theme mb-2">Sélectionner les salles autorisées (Multi-sélection) :</label>
                                <div class="d-flex flex-wrap gap-2" id="bulkPoolRoomsContainer">
                                    <?php foreach ($gridData['rooms'] as $rm): ?>
                                        <div class="chip-badge chip-toggle px-3 py-1.5 rounded-pill border extra-small fw-bold cursor-pointer active"
                                             data-group="pool_rooms" data-value="<?= $rm['id'] ?>" onclick="toggleChip(this)">
                                            <i class="bi bi-door-open text-primary me-1"></i><?= htmlspecialchars($rm['nom']) ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                        </div>

                        <!-- Section 6 : Couleur de carte & Résumé en Temps Réel -->
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 p-3 rounded-4 border bg-primary bg-opacity-10 mb-4">
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label fw-bold text-main-theme small mb-0">Couleur d'affichage :</label>
                                <input type="color" id="bulk_color_hex" class="form-control form-control-color rounded-circle border-0 p-0 cursor-pointer" value="#3b82f6" style="width: 32px; height: 32px;">
                            </div>

                            <!-- Calculateur Temps Réel -->
                            <div id="bulkRealtimeCounter" class="badge bg-primary text-white px-3 py-2 rounded-pill fs-6 fw-bold shadow-xs">
                                <i class="bi bi-lightning-charge-fill me-1"></i>0 cours à générer
                            </div>
                        </div>

                        <!-- Actions Étape 1 -->
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="btnBulkAnalyze">
                                <i class="bi bi-magic me-1"></i>Analyser & Prévisualiser (Étape 2)
                            </button>
                        </div>

                    </form>
                </div>

                <!-- ÉTAPE 2 : Prévisualisation & Résolution Interactive -->
                <div id="bulkStep2" class="d-none">
                    
                    <!-- Top Summary Stats Bar -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="modern-card p-3 rounded-4 border text-center shadow-xs">
                                <span class="text-muted extra-small fw-bold text-uppercase d-block">Total Cours Générés</span>
                                <span class="fs-3 fw-black text-main-theme" id="statTotalGenerated">0</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="modern-card p-3 rounded-4 border border-success border-opacity-30 bg-success bg-opacity-10 text-center shadow-xs">
                                <span class="text-success extra-small fw-bold text-uppercase d-block">Programmations Valides</span>
                                <span class="fs-3 fw-black text-success" id="statValidCount">0</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="modern-card p-3 rounded-4 border border-danger border-opacity-30 bg-danger bg-opacity-10 text-center shadow-xs">
                                <span class="text-danger extra-small fw-bold text-uppercase d-block">Conflits Détectés</span>
                                <span class="fs-3 fw-black text-danger" id="statConflictCount">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Interactive Preview Table -->
                    <div class="table-responsive rounded-4 border mb-4 overflow-auto" style="max-height: 420px;">
                        <table class="table table-hover align-middle mb-0 text-center extra-small" id="bulkPreviewTable">
                            <thead class="bg-body-tertiary sticky-top">
                                <tr>
                                    <th class="py-2.5">Classe</th>
                                    <th class="py-2.5">Jour</th>
                                    <th class="py-2.5">Créneau</th>
                                    <th class="py-2.5">Matière</th>
                                    <th class="py-2.5">Enseignant</th>
                                    <th class="py-2.5" style="min-width: 160px;">Salle Affectée</th>
                                    <th class="py-2.5">Statut / Diagnostic</th>
                                    <th class="py-2.5" style="width: 50px;">Inclure</th>
                                </tr>
                            </thead>
                            <tbody id="bulkPreviewTbody">
                                <!-- Populated dynamically by JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Actions Étape 2 -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 border-top pt-3">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-3" onclick="showBulkStep(1)">
                            <i class="bi bi-arrow-left me-1"></i>Modifier les filtres (Étape 1)
                        </button>

                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <button type="button" class="btn btn-warning rounded-pill px-3 fw-bold text-dark shadow-sm d-none" id="btnAutoFixRooms" onclick="autoFixAllRooms()">
                                <i class="bi bi-wrench-adjustable me-1"></i>Tout corriger automatiquement
                            </button>
                            <button type="button" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" id="btnSaveValidOnly" onclick="executeBulkSave(true)">
                                <i class="bi bi-check-circle-fill me-1"></i>Enregistrer les programmations valides
                            </button>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>
<?php endif; ?>

<style>
.chip-toggle {
    user-select: none;
    transition: all 0.2s ease-in-out;
    background-color: var(--bg-card, #ffffff);
    color: var(--text-main, #334155);
}
.chip-toggle:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.08);
}
.chip-toggle.active {
    background-color: rgba(59, 130, 246, 0.15) !important;
    border-color: #3b82f6 !important;
    color: #2563eb !important;
}
.room-mode-card.active {
    border-color: #3b82f6 !important;
    background-color: rgba(59, 130, 246, 0.05) !important;
}
</style>

<script>
let currentBulkSchedules = [];

function openBulkScheduleModal() {
    const modalEl = document.getElementById('bulkScheduleModal');
    if (!modalEl) return;
    showBulkStep(1);
    updateRealtimeCounter();
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function showBulkStep(stepNum) {
    const step1 = document.getElementById('bulkStep1');
    const step2 = document.getElementById('bulkStep2');
    const ind1 = document.getElementById('stepIndicator1');
    const ind2 = document.getElementById('stepIndicator2');

    if (stepNum === 1) {
        step1.classList.remove('d-none');
        step2.classList.add('d-none');
        ind1.classList.add('active');
        ind1.classList.remove('opacity-50');
        ind2.classList.remove('active');
        ind2.classList.add('opacity-50');
    } else {
        step1.classList.add('d-none');
        step2.classList.remove('d-none');
        ind2.classList.add('active');
        ind2.classList.remove('opacity-50');
        ind1.classList.remove('active');
        ind1.classList.add('opacity-50');
    }
}

function toggleChip(el) {
    el.classList.toggle('active');
    updateRealtimeCounter();
}

function toggleAllChips(group) {
    const chips = document.querySelectorAll(`.chip-toggle[data-group="${group}"]`);
    const allActive = Array.from(chips).every(c => c.classList.contains('active'));
    chips.forEach(c => {
        if (allActive) {
            c.classList.remove('active');
        } else {
            c.classList.add('active');
        }
    });
    updateRealtimeCounter();
}

function setRoomMode(mode) {
    document.querySelectorAll('.room-mode-card').forEach(c => c.classList.remove('active'));
    const radio = document.querySelector(`input[name="bulk_room_mode"][value="${mode}"]`);
    if (radio) radio.checked = true;

    const mutualizedOptions = document.getElementById('roomModeMutualizedOptions');
    const poolOptions = document.getElementById('roomModePoolOptions');

    if (mode === 'auto') {
        document.getElementById('cardRoomModeAuto').classList.add('active');
        mutualizedOptions.classList.add('d-none');
        poolOptions.classList.add('d-none');
    } else if (mode === 'mutualized') {
        document.getElementById('cardRoomModeMutualized').classList.add('active');
        mutualizedOptions.classList.remove('d-none');
        poolOptions.classList.add('d-none');
    } else if (mode === 'custom_pool') {
        document.getElementById('cardRoomModePool').classList.add('active');
        mutualizedOptions.classList.add('d-none');
        poolOptions.classList.remove('d-none');
    }
}

function updateRealtimeCounter() {
    const activeDays = document.querySelectorAll('.chip-toggle[data-group="days"].active').length;
    const activeSlots = document.querySelectorAll('.chip-toggle[data-group="slots"].active').length;
    const activeClasses = document.querySelectorAll('.chip-toggle[data-group="classes"].active').length;

    const total = activeDays * activeSlots * activeClasses;
    const counterEl = document.getElementById('bulkRealtimeCounter');
    if (counterEl) {
        counterEl.innerHTML = `<i class="bi bi-lightning-charge-fill me-1"></i>${total} cours à générer (${activeClasses} cl. × ${activeDays} j. × ${activeSlots} cr.)`;
    }
}

function onBulkSubjectChange(subjectId) {
    const selectSub = document.getElementById('bulk_subject_id');
    const opt = selectSub.options[selectSub.selectedIndex];
    if (opt && opt.dataset.color) {
        document.getElementById('bulk_color_hex').value = opt.dataset.color;
    }
}

function onBulkAnalyzeSubmit() {
    const subjectId = parseInt(document.getElementById('bulk_subject_id').value || 0);
    const teacherId = parseInt(document.getElementById('bulk_teacher_id').value || 0);
    
    const days = Array.from(document.querySelectorAll('.chip-toggle[data-group="days"].active')).map(c => c.dataset.value);
    const slotIds = Array.from(document.querySelectorAll('.chip-toggle[data-group="slots"].active')).map(c => parseInt(c.dataset.value));
    const classIds = Array.from(document.querySelectorAll('.chip-toggle[data-group="classes"].active')).map(c => parseInt(c.dataset.value));
    
    const roomMode = document.querySelector('input[name="bulk_room_mode"]:checked').value;
    const singleRoomId = parseInt(document.getElementById('bulk_single_room_id').value || 0);
    const poolRoomIds = Array.from(document.querySelectorAll('.chip-toggle[data-group="pool_rooms"].active')).map(c => parseInt(c.dataset.value));
    const colorHex = document.getElementById('bulk_color_hex').value;

    if (!subjectId || !teacherId || days.length === 0 || slotIds.length === 0 || classIds.length === 0) {
        alert('Veuillez renseigner tous les champs obligatoires (Matière, Enseignant, au moins 1 Jour, 1 Créneau et 1 Classe).');
        return;
    }

    const payload = {
        week_id: <?= (int)$weekId ?>,
        subject_id: subjectId,
        teacher_id: teacherId,
        days: days,
        slot_ids: slotIds,
        class_ids: classIds,
        room_mode: roomMode,
        room_id: singleRoomId,
        room_ids: poolRoomIds,
        couleur_hex: colorHex
    };

    const btn = document.getElementById('btnBulkAnalyze');
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Analyse des conflits en cours...`;

    fetch('/timetables/api/bulk-validate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = `<i class="bi bi-magic me-1"></i>Analyser & Prévisualiser (Étape 2)`;

        if (!data.success) {
            alert(data.message || 'Erreur lors de l\'analyse des cours.');
            return;
        }

        currentBulkSchedules = data.schedules || [];
        document.getElementById('statTotalGenerated').textContent = data.total_generated || 0;
        document.getElementById('statValidCount').textContent = data.valid_count || 0;
        document.getElementById('statConflictCount').textContent = data.conflict_count || 0;

        const btnFix = document.getElementById('btnAutoFixRooms');
        if (data.conflict_count > 0 && currentBulkSchedules.some(s => s.has_conflict && s.suggested_room_id)) {
            btnFix.classList.remove('d-none');
        } else {
            btnFix.classList.add('d-none');
        }

        renderBulkPreviewTable(currentBulkSchedules);
        showBulkStep(2);
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = `<i class="bi bi-magic me-1"></i>Analyser & Prévisualiser (Étape 2)`;
        alert('Erreur réseau lors de la communication avec le serveur.');
    });
}

function renderBulkPreviewTable(schedules) {
    const tbody = document.getElementById('bulkPreviewTbody');
    tbody.innerHTML = '';

    if (schedules.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-muted py-4">Aucune programmation n'a pu être générée.</td></tr>`;
        return;
    }

    schedules.forEach((item, index) => {
        const tr = document.createElement('tr');
        if (item.has_conflict) {
            tr.className = 'table-danger border-danger border-opacity-25';
        }

        let roomOptionsHtml = '';
        if (item.all_rooms && item.all_rooms.length > 0) {
            roomOptionsHtml = `<select class="form-select form-select-sm extra-small py-0.5 rounded-2" onchange="onBulkItemRoomChange(${index}, this.value)">`;
            item.all_rooms.forEach(r => {
                const selected = (parseInt(r.id) === parseInt(item.room_id)) ? 'selected' : '';
                roomOptionsHtml += `<option value="${r.id}" ${selected}>${r.nom}</option>`;
            });
            roomOptionsHtml += `</select>`;
        } else {
            roomOptionsHtml = item.room_name;
        }

        let statusHtml = '';
        if (!item.has_conflict) {
            statusHtml = `<span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30 rounded-pill px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Valide</span>`;
        } else {
            const msgs = (item.conflict_messages || []).join(' | ');
            statusHtml = `<span class="badge bg-danger text-white rounded-pill px-2 py-1 cursor-pointer" title="${msgs}" data-bs-toggle="tooltip"><i class="bi bi-exclamation-triangle-fill me-1"></i>Conflit</span>`;
            if (item.suggested_room_id) {
                statusHtml += `<div class="extra-small text-primary mt-1 fw-bold">Suggestion: ${item.suggested_room_name}</div>`;
            }
        }

        tr.innerHTML = `
            <td class="fw-bold text-primary">${item.class_name}</td>
            <td class="fw-bold">${item.day_of_week}</td>
            <td class="font-monospace">${item.slot_label}</td>
            <td class="fw-bold">${item.subject_name}</td>
            <td>${item.teacher_name}</td>
            <td>${roomOptionsHtml}</td>
            <td>${statusHtml}</td>
            <td>
                <input class="form-check-input bulk-include-check" type="checkbox" data-index="${index}" ${!item.has_conflict ? 'checked' : ''}>
            </td>
        `;

        tbody.appendChild(tr);
    });

    if (typeof bootstrap !== 'undefined') {
        const tooltips = [].slice.call(document.querySelectorAll('#bulkPreviewTable [data-bs-toggle="tooltip"]'));
        tooltips.map(el => new bootstrap.Tooltip(el));
    }
}

function onBulkItemRoomChange(index, newRoomId) {
    if (!currentBulkSchedules[index]) return;
    currentBulkSchedules[index].room_id = parseInt(newRoomId);
    const rObj = (currentBulkSchedules[index].all_rooms || []).find(r => parseInt(r.id) === parseInt(newRoomId));
    if (rObj) currentBulkSchedules[index].room_name = rObj.nom;
}

function autoFixAllRooms() {
    let fixedCount = 0;
    currentBulkSchedules.forEach((item) => {
        if (item.has_conflict && item.suggested_room_id) {
            item.room_id = item.suggested_room_id;
            item.room_name = item.suggested_room_name;
            item.has_conflict = false;
            item.conflict_messages = [];
            fixedCount++;
        }
    });

    if (fixedCount > 0) {
        let validCount = currentBulkSchedules.filter(s => !s.has_conflict).length;
        let conflictCount = currentBulkSchedules.filter(s => s.has_conflict).length;

        document.getElementById('statValidCount').textContent = validCount;
        document.getElementById('statConflictCount').textContent = conflictCount;

        renderBulkPreviewTable(currentBulkSchedules);
    }
}

function executeBulkSave(validOnly = true) {
    let schedulesToSave = [];
    const checks = document.querySelectorAll('.bulk-include-check');

    checks.forEach((chk) => {
        const idx = parseInt(chk.dataset.index);
        if (chk.checked && currentBulkSchedules[idx]) {
            const item = currentBulkSchedules[idx];
            if (!validOnly || !item.has_conflict) {
                schedulesToSave.push(item);
            }
        }
    });

    if (schedulesToSave.length === 0) {
        alert('Aucune programmation valide sélectionnée pour l\'enregistrement.');
        return;
    }

    const btnSave = document.getElementById('btnSaveValidOnly');
    btnSave.disabled = true;
    btnSave.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Enregistrement de ${schedulesToSave.length} cours...`;

    fetch('/timetables/api/bulk-save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ schedules: schedulesToSave })
    })
    .then(res => res.json())
    .then(data => {
        btnSave.disabled = false;
        btnSave.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i>Enregistrer les programmations valides`;

        if (data.success) {
            const modalEl = document.getElementById('bulkScheduleModal');
            const bsModal = bootstrap.Modal.getInstance(modalEl);
            if (bsModal) bsModal.hide();

            window.location.reload();
        } else {
            alert(data.message || 'Erreur lors de l\'enregistrement des cours.');
        }
    })
    .catch(err => {
        btnSave.disabled = false;
        btnSave.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i>Enregistrer les programmations valides`;
        alert('Erreur de connexion lors de l\'enregistrement.');
    });
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../templates/layout.php';
?>