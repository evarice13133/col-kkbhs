<?php
$title = __('transcripts') ?? 'Relevés de Notes';
ob_start();
?>

<div class="animate-fade-in admin-analytics module-bureau-flow">

    <!-- BARRE D'ACTIONS FLOATING ISLAND RESPONSIVE -->
    <div class="d-flex justify-content-center mb-4 mb-md-5">
        <div class="filter-island px-3 py-2 shadow-lg animate-slide-down">
            <form method="GET" action="/transcripts" id="transcriptFilterForm"
                class="d-flex align-items-center gap-2 gap-md-3 flex-wrap flex-md-nowrap filter-form w-100">

                <div class="row g-2 flex-grow-1 w-100 m-0">
                    <!-- 1. ANNÉE ACADÉMIQUE -->
                    <div class="col-12 col-sm-4 flex-grow-1 p-0 px-sm-1">
                        <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-3 py-1 w-100">
                            <span class="input-group-text border-0 bg-transparent text-primary small fw-bold text-uppercase me-1">
                                <i class="bi bi-calendar-event me-1"></i><?= __('academic_years') ?>
                            </span>
                            <select name="academic_year_id" id="academic_year_id_select"
                                class="form-select border-0 bg-transparent shadow-none fw-bold text-main">
                                <?php foreach ($academicYears as $year): ?>
                                    <option value="<?= $year['id'] ?>" <?= $academicYearId === (int) $year['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $year['nom']) ?>
                                        <?= (int) $year['is_active'] === 1 ? '(' . __('active') . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- 2. TYPE D'ENSEIGNEMENT -->
                    <div class="col-12 col-sm-4 flex-grow-1 p-0 px-sm-1">
                        <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-3 py-1 w-100">
                            <span class="input-group-text border-0 bg-transparent text-primary small fw-bold text-uppercase me-1">
                                <i class="bi bi-diagram-3 me-1"></i><?= __('teaching_type') ?? 'Type' ?>
                            </span>
                            <select name="teaching_type_id" id="teaching_type_id_select"
                                class="form-select border-0 bg-transparent shadow-none fw-bold text-main">
                                <?php foreach ($teachingTypes as $type): ?>
                                    <option value="<?= $type['id'] ?>" <?= $teachingTypeId === (int) $type['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $type['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- 3. CLASSE -->
                    <div class="col-12 col-sm-4 flex-grow-1 p-0 px-sm-1">
                        <div class="input-group search-pill bg-white bg-opacity-10 rounded-pill px-3 py-1 w-100 position-relative">
                            <span class="input-group-text border-0 bg-transparent text-primary small fw-bold text-uppercase me-1">
                                <i class="bi bi-mortarboard me-1"></i><?= __('classes') ?>
                            </span>
                            <select name="class_id" id="class_id_select"
                                class="form-select border-0 bg-transparent shadow-none fw-bold text-main pe-4"
                                onchange="document.getElementById('transcriptFilterForm').submit()">
                                <option value=""><?= __('select_class') ?? 'Sélectionner une classe' ?></option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= $class['id'] ?>" <?= $classId === (int) $class['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars((string) $class['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div id="class_loader_spinner" class="spinner-border spinner-border-sm text-primary position-absolute end-0 top-50 translate-middle-y me-3 d-none" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 align-items-center justify-content-center ps-md-2 pt-2 pt-md-0 border-top border-top-md-0 border-opacity-10 border-secondary flex-shrink-0">
                    <a href="/transcripts"
                        class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center reset-btn shadow-sm"
                        style="width: 44px; height: 44px;" title="<?= __('reset') ?>">
                        <i class="bi bi-arrow-counterclockwise fs-5 text-primary"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php if ($classId > 0): ?>
        <div class="modern-card border-0 shadow-sm">
            <div class="modern-card-body p-4 p-lg-5">
                <form id="transcriptForm" target="_blank" action="/transcripts/generate" method="GET">
                    <input type="hidden" name="academic_year_id" value="<?= (int) $academicYearId ?>">
                    <input type="hidden" name="teaching_type_id" value="<?= (int) $teachingTypeId ?>">
                    <input type="hidden" name="class_id" value="<?= $classId ?>">

                    <!-- ÉTAPE : SÉLECTION DE L'ÉLÈVE -->
                    <div class="flow-step mb-4">
                        <div class="flow-step-number">1</div>
                        <h5 class="fw-bold mb-3"><?= __('select_student') ?></h5>
                        <div class="row g-3 align-items-center">
                            <div class="col-md-8">
                                <select name="student_id" class="form-select form-select-lg rounded-3 shadow-none fw-semibold">
                                    <option value="0">-- <?= __('all_students') ?> --</option>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?= $student['id'] ?>">
                                            <?= htmlspecialchars((string) $student['nom'] . ' ' . $student['prenom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-file-earmark-spreadsheet fs-5"></i>
                                    <span><?= __('generate_transcript') ?></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- INFORMATION COMPLÉMENTAIRE -->
                    <div class="alert alert-info border-0 rounded-4 shadow-sm p-4 d-flex align-items-center gap-3">
                        <i class="bi bi-info-circle-fill fs-2 text-info"></i>
                        <div>
                            <h6 class="fw-bold mb-1"><?= __('transcript_of_records') ?></h6>
                            <p class="mb-0 text-muted small">
                                Ce module génère un Relevé de Notes officiel structuré par semestre et par groupe de modules (UE), intégrant l'en-tête officiel de l'établissement, le détail des crédits et la décision de la commission du jury.
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <div class="empty-state-icon bg-light text-muted rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                <i class="bi bi-file-earmark-spreadsheet fs-1 text-primary opacity-75"></i>
            </div>
            <h4 class="fw-bold text-main-theme mb-2"><?= __('select_class_to_continue') ?></h4>
            <p class="text-muted-theme small mb-0" style="max-width: 520px; margin: 0 auto;"><?= __('select_class_desc') ?></p>
        </div>
    <?php endif; ?>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const yearSelect = document.getElementById('academic_year_id_select');
        const teachingTypeSelect = document.getElementById('teaching_type_id_select');
        const classSelect = document.getElementById('class_id_select');
        const classLoader = document.getElementById('class_loader_spinner');
        const filterForm = document.getElementById('transcriptFilterForm');

        function fetchClassesForFilter(resetAndSubmit = true) {
            const yearId = yearSelect ? yearSelect.value : '';
            const typeId = teachingTypeSelect ? teachingTypeSelect.value : '';

            if (!typeId) return;

            if (classLoader) classLoader.classList.remove('d-none');
            if (classSelect) classSelect.disabled = true;

            fetch(`/bulletins/classes-json?teaching_type_id=${typeId}&academic_year_id=${yearId}`)
                .then(res => res.json())
                .then(classes => {
                    if (classSelect) {
                        classSelect.innerHTML = `<option value=""><?= __('select_class') ?? 'Sélectionner une classe' ?></option>`;
                        classes.forEach(c => {
                            const opt = document.createElement('option');
                            opt.value = c.id;
                            opt.textContent = c.nom;
                            classSelect.appendChild(opt);
                        });
                        classSelect.disabled = false;
                        if (resetAndSubmit) {
                            classSelect.value = '';
                        }
                    }
                })
                .catch(err => console.error('Error fetching classes:', err))
                .finally(() => {
                    if (classLoader) classLoader.classList.add('d-none');
                    if (resetAndSubmit && filterForm) {
                        filterForm.submit();
                    }
                });
        }

        if (teachingTypeSelect) {
            teachingTypeSelect.addEventListener('change', function () {
                fetchClassesForFilter(true);
            });
        }

        if (yearSelect) {
            yearSelect.addEventListener('change', function () {
                fetchClassesForFilter(true);
            });
        }
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
