<?php
$title = __('full_history');
ob_start();
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="/notes"
                class="btn btn-outline-secondary rounded-circle shadow-sm p-2 d-flex align-items-center justify-content-center"
                style="width: 40px; height: 40px;">
                <i class="bi bi-arrow-left fs-5"></i>
            </a>
            <div>
                <h2 class="fw-bold mb-0 text-body text-main-theme"><?= __('full_history') ?></h2>
                <p class="text-secondary text-main-theme small mb-0">
                    <?= __('total_records', ['count' => $total]) ?>
                </p>
            </div>
        </div>
    </div>

    <!-- BARRE DE FILTRES -->
    <div class="d-flex justify-content-center mb-5">
        <div class="filter-island px-3 py-2 shadow-lg animate-slide-down" style="min-width: 95%;">
            <form method="GET" class="d-flex align-items-center gap-2 flex-wrap flex-md-nowrap filter-form w-100">
                <!-- Barre de Recherche -->
                <div class="flex-grow-1">
                    <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-2">
                        <span class="input-group-text border-0 bg-transparent text-primary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="q"
                            class="form-control border-0 bg-transparent shadow-none py-2 text-main"
                            value="<?= htmlspecialchars((string) $filters['q']) ?>" placeholder="<?= __('search') ?>..."
                            style="min-width: 150px;">
                    </div>
                </div>

                <!-- Filtre Type Enseignement -->
                <div>
                    <select name="teaching_type_id" id="filter_teaching_type"
                        class="form-select border-0 bg-white bg-opacity-10 shadow-none py-2 text-main rounded-pill px-3"
                        style="min-width: 150px;" onchange="this.form.submit()">
                        <option value=""><?= __('all_types') ?></option>
                        <?php foreach ($teachingTypes as $tt): ?>
                            <option value="<?= $tt['id'] ?>" <?= (int) ($filters['teaching_type_id'] ?? 0) === (int) $tt['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $tt['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtre Classe -->
                <div>
                    <select name="class_id" id="filter_class"
                        class="form-select border-0 bg-white bg-opacity-10 shadow-none py-2 text-main rounded-pill px-3"
                        style="min-width: 150px;"
                        onchange="document.getElementById('filter_subject').value = ''; this.form.submit()">
                        <option value=""><?= __('all_classes') ?></option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= $class['id'] ?>" data-teaching-type="<?= $class['teaching_type_id'] ?? '' ?>"
                                <?= (int) $filters['class_id'] === (int) $class['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($class['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtre Matière -->
                <div>
                    <select name="subject_id" id="filter_subject"
                        class="form-select border-0 bg-white bg-opacity-10 shadow-none py-2 text-main rounded-pill px-3"
                        style="min-width: 150px;">
                        <option value=""><?= __('all_subjects') ?></option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?= $subject['id'] ?>"
                                data-teaching-type="<?= $subject['teaching_type_id'] ?? '' ?>" <?= (int) $filters['subject_id'] === (int) $subject['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($subject['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtre Période -->
                <div>
                    <select name="periode"
                        class="form-select border-0 bg-white bg-opacity-10 shadow-none py-2 text-main rounded-pill px-3"
                        style="min-width: 150px;">
                        <option value=""><?= __('all_periods') ?></option>
                        <?php foreach ($periods as $period): ?>
                            <option value="<?= htmlspecialchars($period) ?>" <?= $filters['periode'] === $period ? 'selected' : '' ?>>
                                <?= htmlspecialchars($period) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Boutons -->
                <div class="d-flex gap-2 align-items-center ps-2">
                    <button type="submit"
                        class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"><?= __('filter') ?></button>
                    <a href="/notes/history"
                        class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center"
                        style="width: 40px; height: 40px;" title="<?= __('reset') ?>">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in grades-history-card">
        <div class="table-responsive">
            <table class="table-modern grades-history-table">
                <colgroup>
                    <col class="grades-history-col-date">
                    <col class="grades-history-col-student">
                    <col class="grades-history-col-class">
                    <col class="grades-history-col-subject">
                    <col class="grades-history-col-period">
                    <col class="grades-history-col-grade">
                    <col class="grades-history-col-appreciation">
                    <col class="grades-history-col-teacher">
                    <col class="grades-history-col-actions">
                </colgroup>
                <thead>
                    <tr>
                        <th class="grades-history-date"><?= __('date') ?></th>
                        <th class="grades-history-student"><?= __('student') ?></th>
                        <th class="grades-history-class"><?= __('class') ?></th>
                        <th class="grades-history-subject"><?= __('subject') ?></th>
                        <th class="grades-history-period"><?= __('period') ?></th>
                        <th class="grades-history-grade"><?= __('grade') ?></th>
                        <th class="grades-history-appreciation"><?= __('appreciation') ?></th>
                        <th class="grades-history-teacher"><?= __('teacher') ?></th>
                        <th class="grades-history-actions text-end"><?= __('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($grades)): ?>
                        <tr>
                            <td colspan="9" class="grades-history-empty text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                <?= __('no_activity_yet') ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($grades as $grade): ?>
                            <tr class="grades-history-row">
                                <td class="grades-history-date">
                                    <span class="grades-history-date-day"><?= date('d/m/Y', strtotime($grade['created_at'])) ?></span>
                                    <span class="grades-history-date-time"><?= date('H:i', strtotime($grade['created_at'])) ?></span>
                                </td>
                                <td class="grades-history-student">
                                    <div class="grades-history-person">
                                        <div class="grades-history-avatar" aria-hidden="true">
                                            <?= htmlspecialchars(strtoupper(substr((string) $grade['student_nom'], 0, 1))) ?>
                                        </div>
                                        <div class="grades-history-person-copy">
                                            <div class="grades-history-person-name" title="<?= htmlspecialchars($grade['student_nom'], ENT_QUOTES) ?>">
                                                <?= htmlspecialchars($grade['student_nom']) ?>
                                            </div>
                                            <div class="grades-history-person-detail" title="<?= htmlspecialchars($grade['student_prenom'], ENT_QUOTES) ?>">
                                                <?= htmlspecialchars($grade['student_prenom']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="grades-history-class">
                                    <span class="grades-history-badge grades-history-badge--class" title="<?= htmlspecialchars($grade['class_nom'], ENT_QUOTES) ?>">
                                        <?= htmlspecialchars($grade['class_nom']) ?>
                                    </span>
                                </td>
                                <td class="grades-history-subject" title="<?= htmlspecialchars($grade['subject_nom'], ENT_QUOTES) ?>">
                                    <?= htmlspecialchars($grade['subject_nom']) ?>
                                </td>
                                <td class="grades-history-period">
                                    <span class="grades-history-badge grades-history-badge--period">
                                        <?= htmlspecialchars($grade['periode']) ?>
                                    </span>
                                </td>
                                <td class="grades-history-grade">
                                    <span class="grades-history-badge <?= $grade['valeur'] >= 10 ? 'grades-history-badge--success' : 'grades-history-badge--danger' ?>">
                                        <?= number_format((float) $grade['valeur'], 2, ',', ' ') ?>
                                    </span>
                                </td>
                                <td class="grades-history-appreciation">
                                    <span class="grades-history-badge <?= $grade['valeur'] >= 10 ? 'grades-history-badge--success' : 'grades-history-badge--danger' ?>"
                                        title="<?= htmlspecialchars($grade['appreciation'], ENT_QUOTES) ?>">
                                        <?= htmlspecialchars($grade['appreciation']) ?>
                                    </span>
                                </td>
                                <td class="grades-history-teacher">
                                    <div class="grades-history-teacher-info">
                                        <i class="bi bi-person" aria-hidden="true"></i>
                                        <span title="<?= htmlspecialchars(trim(($grade['teacher_nom'] ?? '') . ' ' . ($grade['teacher_prenom'] ?? '')), ENT_QUOTES) ?>">
                                            <?= htmlspecialchars(trim(($grade['teacher_nom'] ?? '') . ' ' . ($grade['teacher_prenom'] ?? ''))) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="grades-history-actions text-end">
                                    <a href="/notes/saisie?class_id=<?= $grade['class_id'] ?>&subject_id=<?= $grade['subject_id'] ?>&periode=<?= urlencode((string) $grade['periode']) ?>"
                                        class="grades-history-action"
                                        title="<?= __('edit_grade') ?>">
                                        <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                        <span class="visually-hidden"><?= __('edit_grade') ?></span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($total > 0): ?>
            <?php
            $firstRecord = (($page - 1) * $perPage) + 1;
            $lastRecord = (($page - 1) * $perPage) + count($grades);
            ?>
            <div class="grades-history-footer">
                <span class="grades-history-range">
                    <?= __('showing_records', [
                        'start' => $firstRecord,
                        'end' => $lastRecord,
                        'total' => $total,
                    ]) ?>
                </span>
                <?php if ($totalPages > 1): ?>
                <nav aria-label="<?= __('pagination') ?>">
                    <ul class="pagination justify-content-center mb-0">
                        <?php
                        $queryParams = http_build_query(array_filter($filters, function ($v) {
                            return $v !== '' && $v !== 0;
                        }));
                        $baseUrl = '/notes/history?' . $queryParams;
                        ?>
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $baseUrl ?>&page=<?= $page - 1 ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <li class="page-item active">
                                    <span class="page-link"><?= $i ?></span>
                                </li>
                            <?php elseif ($i == 1 || $i == $totalPages || abs($i - $page) <= 2): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $baseUrl ?>&page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php elseif (abs($i - $page) == 3): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $baseUrl ?>&page=<?= $page + 1 ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>