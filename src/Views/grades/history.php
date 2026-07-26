<?php
$title = __('full_history');
ob_start();
?>

<div class="animate-fade-in container-fluid py-3 px-md-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="/notes" class="btn btn-outline-secondary rounded-circle shadow-sm p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
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
                        <input type="text" name="q" class="form-control border-0 bg-transparent shadow-none py-2 text-main"
                            value="<?= htmlspecialchars((string) $filters['q']) ?>"
                            placeholder="<?= __('search') ?>..." style="min-width: 150px;">
                    </div>
                </div>

                <!-- Filtre Type Enseignement -->
                <div>
                    <select name="teaching_type_id" id="filter_teaching_type" class="form-select border-0 bg-white bg-opacity-10 shadow-none py-2 text-main rounded-pill px-3" style="min-width: 150px;" onchange="this.form.submit()">
                        <option value="">Tous les types</option>
                        <?php foreach ($teachingTypes as $tt): ?>
                            <option value="<?= $tt['id'] ?>" <?= (int) ($filters['teaching_type_id'] ?? 0) === (int) $tt['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $tt['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtre Classe -->
                <div>
                    <select name="class_id" id="filter_class" class="form-select border-0 bg-white bg-opacity-10 shadow-none py-2 text-main rounded-pill px-3" style="min-width: 150px;">
                        <option value=""><?= __('all_classes') ?></option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= $class['id'] ?>" data-teaching-type="<?= $class['teaching_type_id'] ?? '' ?>" <?= (int) $filters['class_id'] === (int) $class['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($class['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtre Matière -->
                <div>
                    <select name="subject_id" id="filter_subject" class="form-select border-0 bg-white bg-opacity-10 shadow-none py-2 text-main rounded-pill px-3" style="min-width: 150px;">
                        <option value=""><?= __('all_subjects') ?></option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?= $subject['id'] ?>" data-teaching-type="<?= $subject['teaching_type_id'] ?? '' ?>" <?= (int) $filters['subject_id'] === (int) $subject['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($subject['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtre Période -->
                <div>
                    <select name="periode" class="form-select border-0 bg-white bg-opacity-10 shadow-none py-2 text-main rounded-pill px-3" style="min-width: 150px;">
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
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"><?= __('filter') ?></button>
                    <a href="/notes/history" class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" title="<?= __('reset') ?>">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in">
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th><?= __('date') ?></th>
                        <th><?= __('student') ?></th>
                        <th><?= __('class') ?></th>
                        <th><?= __('subject') ?></th>
                        <th><?= __('period') ?></th>
                        <th><?= __('grade') ?></th>
                        <th><?= __('appreciation') ?></th>
                        <th><?= __('teacher') ?></th>
                        <th class="text-end"><?= __('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($grades)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                <?= __('no_activity_yet') ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($grades as $grade): ?>
                            <tr class="student-row">
                                <td class="small">
                                    <?= date('d/m/Y H:i', strtotime($grade['created_at'])) ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-init bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                            style="width: 36px; height: 36px; font-size: 1rem; border: 1px solid rgba(var(--primary-rgb), 0.2);">
                                            <?= strtoupper(substr((string) $grade['student_nom'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-main-theme" style="font-size: 0.9rem;">
                                                <?= htmlspecialchars($grade['student_nom']) ?>
                                            </div>
                                            <div class="text-muted-theme opacity-75" style="font-size: 0.75rem;">
                                                <?= htmlspecialchars($grade['student_prenom']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-1 small">
                                        <?= htmlspecialchars($grade['class_nom']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($grade['subject_nom']) ?></td>
                                <td><?= htmlspecialchars($grade['periode']) ?></td>
                                <td>
                                    <span class="fw-bold <?= $grade['valeur'] >= 10 ? 'text-success' : 'text-danger' ?>">
                                        <?= number_format((float) $grade['valeur'], 2, ',', ' ') ?>
                                    </span>
                                </td>
                                <td class="small"><?= htmlspecialchars($grade['appreciation']) ?></td>
                                <td class="small">
                                    <?= htmlspecialchars($grade['teacher_nom'] ?? '') ?> <?= htmlspecialchars($grade['teacher_prenom'] ?? '') ?>
                                </td>
                                <td class="text-end">
                                    <a href="/notes/saisie?class_id=<?= $grade['class_id'] ?>&subject_id=<?= $grade['subject_id'] ?>&periode=<?= urlencode((string) $grade['periode']) ?>"
                                       class="btn-icon-pill p-1 px-2 text-primary bg-primary bg-opacity-10 rounded-pill transition-base"
                                       title="<?= __('edit_grade') ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-transparent border-top p-3">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <?php
                    $queryParams = http_build_query(array_filter($filters, function($v) { return $v !== '' && $v !== 0; }));
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
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
