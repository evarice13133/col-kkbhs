<?php
$title = __('access_cards_title');
ob_start();
?>

<div class="animate-fade-in admin-analytics module-bureau-flow">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h4 fw-bold text-main-theme mb-1"><?= __('access_cards_title') ?></h1>
        </div>
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 px-3 py-2">
            <i class="bi bi-grid-3x2 me-1"></i><?= __('access_cards_per_sheet') ?>
        </span>
    </div>

    <div class="modern-card border-0 shadow-sm mb-4">
        <div class="modern-card-body p-3 p-lg-4">
            <form method="GET" action="/access-cards" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="academic_year_id" class="form-label fw-semibold"><?= __('year') ?></label>
                    <select id="academic_year_id" name="academic_year_id" class="form-select" onchange="this.form.submit()">
                        <?php foreach ($academicYears as $year): ?>
                            <option value="<?= (int) $year['id'] ?>" <?= $academicYearId === (int) $year['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $year['nom'], ENT_QUOTES, 'UTF-8') ?><?= (int) $year['is_active'] === 1 ? ' (' . __('active') . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label for="teaching_type_id" class="form-label fw-semibold"><?= __('teaching_type') ?></label>
                    <select id="teaching_type_id" name="teaching_type_id" class="form-select" onchange="this.form.submit()">
                        <?php foreach ($teachingTypes as $type): ?>
                            <option value="<?= (int) $type['id'] ?>" <?= $teachingTypeId === (int) $type['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $type['nom'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label for="class_id" class="form-label fw-semibold"><?= __('class') ?></label>
                    <select id="class_id" name="class_id" class="form-select" onchange="this.form.submit()">
                        <option value="0"><?= __('choose_class') ?></option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= (int) $class['id'] ?>" <?= $classId === (int) $class['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $class['nom'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <?php if ($classId > 0): ?>
        <?php if (empty($students)): ?>
            <div class="alert alert-light border d-flex align-items-center gap-3 mb-0" role="status">
                <i class="bi bi-person-x fs-4 text-muted"></i>
                <span><?= __('access_cards_empty') ?></span>
            </div>
        <?php else: ?>
            <div class="modern-card border-0 shadow-sm">
                <div class="modern-card-body p-3 p-lg-4">
                    <form method="GET" action="/access-cards/print" target="_blank" class="row g-3 align-items-end">
                        <input type="hidden" name="academic_year_id" value="<?= (int) $academicYearId ?>">
                        <input type="hidden" name="teaching_type_id" value="<?= (int) $teachingTypeId ?>">
                        <input type="hidden" name="class_id" value="<?= (int) $classId ?>">
                        <div class="col-12 col-md-8">
                            <label for="student_id" class="form-label fw-semibold"><?= __('access_card_student') ?></label>
                            <select id="student_id" name="student_id" class="form-select">
                                <option value="0"><?= __('access_cards_print_all') ?> (<?= count($students) ?>)</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= (int) $student['id'] ?>">
                                        <?= htmlspecialchars(trim((string) $student['nom'] . ' ' . (string) $student['prenom']), ENT_QUOTES, 'UTF-8') ?>
                                        · <?= htmlspecialchars((string) ($student['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-printer-fill"></i><?= __('print') ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../templates/layout.php';