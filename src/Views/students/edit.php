<?php
$title = __('modify_student_profile');
$selectedSexe = (string) ($student['sexe'] ?? '');
$selectedDepartment = (string) ($student['department_id'] ?? '');
$selectedTeachingType = (string) ($student['teaching_type_id'] ?? '');
$isRedoublant = (string) ((int) ($student['is_redoublant'] ?? 0));
ob_start();
?>

<div class="animate-fade-in container-fluid py-2">
    <!-- Compact Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h2 class="fw-black text-main-theme mb-0 fs-4"><?= __('modify_learner') ?></h2>
            <p class="text-muted-theme small mb-0"><?= h($student['nom'] . ' ' . $student['prenom']) ?> •
                <?= h($student['email']) ?></p>
        </div>
        <a href="/students" class="btn btn-sm btn-light-theme rounded-pill px-3 border-theme-light">
            <i class="bi bi-arrow-left me-1"></i> <?= __('back_to_list') ?>
        </a>
    </div>

    <form action="/students/update?id=<?= $student['id'] ?>" method="POST" id="studentEditForm" enctype="multipart/form-data" class="no-loader">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::generateCsrfToken() ?>">

        <div class="subject-card-compact border-0 shadow-sm overflow-hidden mb-4">
            <div class="card-body p-4">

                <!-- Identity Section -->
                <div class="row g-4 mb-5">
                    <div
                        class="col-12 border-bottom border-theme-light pb-2 mb-2 d-flex justify-content-between align-items-center">
                        <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1">
                            <?= __('learner_identity') ?>
                        </h6>
                    </div>

                    <div class="col-md-3">
                        <label
                            class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('family_name') ?></label>
                        <input type="text" name="nom" class="form-control premium-input"
                            placeholder="<?= __('name_placeholder') ?>" value="<?= h($student['nom'] ?? '') ?>"
                            required>
                    </div>
                    <div class="col-md-3">
                        <label
                            class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('first_names') ?></label>
                        <input type="text" name="prenom" class="form-control premium-input"
                            placeholder="<?= __('first_name_placeholder') ?>" value="<?= h($student['prenom'] ?? '') ?>"
                            required>
                    </div>
                    <div class="col-md-3">
                        <label
                            class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('matricule') ?></label>
                        <input type="text" name="email" class="form-control premium-input border-primary border-opacity-25 fw-black"
                            value="<?= h($student['email'] ?? '') ?>"
                            placeholder="Laisser vide pour générer automatiquement">
                    </div>
                    <div class="col-md-3">
                        <label
                            class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('sex') ?></label>
                        <select name="sexe" class="form-select premium-input" required>
                            <option value="M" <?= $selectedSexe === 'M' ? 'selected' : '' ?>><?= __('male') ?></option>
                            <option value="F" <?= $selectedSexe === 'F' ? 'selected' : '' ?>><?= __('female') ?></option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label
                            class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('birth_date_full') ?></label>
                        <input type="date" name="date_naissance" class="form-control premium-input"
                            value="<?= h($student['date_naissance'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label
                            class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('birth_place_full') ?></label>
                        <input type="text" name="lieu_naissance" class="form-control premium-input"
                            placeholder="Lieu de naissance" value="<?= h($student['lieu_naissance'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('parent_contact') ?? 'Contact Père/Mère' ?></label>
                        <input type="tel" name="parent_contact" class="form-control premium-input" 
                            placeholder="+237 600000000" value="<?= h($student['parent_contact'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('guardian_contact') ?? 'Contact Tuteur' ?></label>
                        <input type="tel" name="guardian_contact" class="form-control premium-input" 
                            placeholder="+237 600000000" value="<?= h($student['guardian_contact'] ?? '') ?>">
                    </div>
                </div>

                <!-- Photo Section -->
                <div class="row g-4 mb-4">
                    <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                        <h6 class="fw-black text-info m-0 text-uppercase letter-spacing-1"><?= __('student_photo') ?></h6>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('current_photo') ?></label>
                        <div class="d-flex align-items-center gap-3">
                            <?php if (!empty($student['photo_eleve'])): ?>
                                <div class="border rounded overflow-hidden" style="width: 100px; height: 100px;">
                                    <?php 
                                    $photoPath = $student['photo_eleve'];
                                    // S'assurer que le chemin commence par /
                                    if (strpos($photoPath, '/') !== 0) {
                                        $photoPath = '/' . $photoPath;
                                    }
                                    // Gérer les deux formats de chemin: /uploads/ et /public/uploads/
                                    if (strpos($photoPath, '/public/uploads/') === 0) {
                                        // Le chemin est déjà au bon format
                                    } elseif (strpos($photoPath, '/uploads/') === 0) {
                                        // Ancien format, ajouter /public/
                                        $photoPath = '/public' . $photoPath;
                                    }
                                    ?>
                                    <img src="<?= $photoPath ?>" alt="Photo actuelle" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'; this.parentElement.innerHTML='<span class=\\'text-muted small text-center px-2\\'>Erreur chargement</span>';">
                                </div>
                                <div>
                                    <button type="submit" name="delete_photo" value="1" class="btn btn-sm btn-outline-danger rounded-pill">
                                        <i class="bi bi-trash me-1"></i> <?= __('delete_photo') ?>
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="border rounded d-flex align-items-center justify-content-center bg-light" style="width: 100px; height: 100px;">
                                    <span class="text-muted small text-center px-2"><?= __('no_photo') ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('replace_photo') ?></label>
                        <input type="file" name="photo_eleve" class="form-control premium-input" accept="image/jpeg,image/jpg,image/png,image/webp" id="photoInputEdit">
                        <div class="form-text small text-muted">
                            <?= __('photo_formats') ?>: JPG, JPEG, PNG, WEBP<br>
                            <?= __('photo_max_size') ?>: 5MB
                        </div>
                    </div>
                </div>

                <!-- Academic Section -->
                <div class="row g-4 mb-4">
                    <div class="col-12 border-bottom border-theme-light pb-2 mb-2">
                        <h6 class="fw-black text-success m-0 text-uppercase letter-spacing-1">
                            <?= __('academic_assignment') ?></h6>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1 opacity-50">Type Enseignement</label>
                        <select id="teaching_type_select" name="teaching_type_id" class="form-select premium-input">
                            <option value="">Tous les types</option>
                            <?php foreach ($teachingTypes as $tt): ?>
                                <option value="<?= $tt['id'] ?>" <?= $selectedTeachingType === (string) $tt['id'] ? 'selected' : '' ?>><?= h($tt['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label
                            class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1 opacity-50"><?= __('cycle_membership_label') ?></label>
                        <select id="cycle_select" name="cycle_id" class="form-select premium-input">
                            <option value=""><?= __('all_cycles') ?></option>
                            <?php foreach ($cycles as $cy): ?>
                                <option value="<?= $cy['id'] ?>" <?= (string) ($student['cycle_id'] ?? '') === (string) $cy['id'] ? 'selected' : '' ?>><?= h($cy['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label
                            class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1 opacity-50"><?= __('section_stream') ?></label>
                        <select id="section_select" name="section_id" class="form-select premium-input">
                            <option value=""><?= __('all_sections') ?></option>
                            <?php foreach ($sections as $sec): ?>
                                <option value="<?= $sec['id'] ?>" <?= (string) ($student['section_id'] ?? '') === (string) $sec['id'] ? 'selected' : '' ?>><?= h($sec['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label
                            class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1 opacity-50"><?= __('department') ?></label>
                        <select id="department_select" name="department_id" class="form-select premium-input">
                            <option value=""><?= __('all_departments') ?? 'Tous les départements' ?></option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>" <?= $selectedDepartment === (string) $dept['id'] ? 'selected' : '' ?>><?= h($dept['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label
                            class="form-label text-primary fw-black extra-small text-uppercase mb-1"><?= __('student_class_label') ?>
                            *</label>
                        <select name="class_id" id="class_select"
                            class="form-select premium-input border-primary border-opacity-25" required
                            data-current="<?= h($student['class_id'] ?? '') ?>">
                            <option value=""><?= __('select_class') ?></option>
                            <?php foreach ($classes as $cla): ?>
                                <option value="<?= $cla['id'] ?>" data-teaching-type="<?= $cla['teaching_type_id'] ?>" data-cycle="<?= $cla['cycle_id'] ?>"
                                    data-section="<?= $cla['section_id'] ?>" data-department="<?= $cla['department_id'] ?>"><?= h($cla['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label
                            class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('repeat_status') ?></label>
                        <div class="d-flex gap-2">
                            <div class="flex-grow-1">
                                <input type="radio" class="btn-check" name="is_redoublant" id="red_no" value="0"
                                    <?= $isRedoublant !== '1' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-secondary btn-sm w-100 rounded-pill"
                                    for="red_no"><?= __('no') ?></label>
                            </div>
                            <div class="flex-grow-1">
                                <input type="radio" class="btn-check" name="is_redoublant" id="red_yes" value="1"
                                    <?= $isRedoublant === '1' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-warning btn-sm w-100 rounded-pill"
                                    for="red_yes"><?= __('yes') ?></label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="d-flex justify-content-end border-top border-theme-light pt-4 mt-2">
                    <button type="submit"
                        class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm transition-base scale-on-hover">
                        <i class="bi bi-save2-fill me-2"></i> Valider
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const teachingTypeSelect = document.getElementById('teaching_type_select');
        const cycleSelect = document.getElementById('cycle_select');
        const sectionSelect = document.getElementById('section_select');
        const departmentSelect = document.getElementById('department_select');
        const classSelect = document.getElementById('class_select');
        const currentClassId = classSelect.getAttribute('data-current') || '';

        const labels = <?= json_encode([
            'selectClass' => __('select_class'),
            'noClassForCriteria' => __('no_class_for_criteria'),
        ], JSON_UNESCAPED_UNICODE) ?>;

        const originalOptions = Array.from(classSelect.options).filter(opt => opt.value !== '');

        function filterClasses() {
            const selectedTeachingType = teachingTypeSelect.value;
            const selectedCycle = cycleSelect.value;
            const selectedSection = sectionSelect.value;
            const selectedDept = departmentSelect.value;
            classSelect.innerHTML = '<option value="">' + labels.selectClass + '</option>';

            let addedCount = 0;
            originalOptions.forEach(opt => {
                const matchTeachingType = !selectedTeachingType || opt.getAttribute('data-teaching-type') === selectedTeachingType;
                const matchCycle = !selectedCycle || opt.getAttribute('data-cycle') === selectedCycle;
                const matchSection = !selectedSection || opt.getAttribute('data-section') === selectedSection;
                const matchDept = !selectedDept || opt.getAttribute('data-department') === selectedDept;

                if (matchTeachingType && matchCycle && matchSection && matchDept) {
                    const clonedOption = opt.cloneNode(true);
                    if (clonedOption.value === currentClassId) clonedOption.selected = true;
                    classSelect.appendChild(clonedOption);
                    addedCount++;
                }
            });

            if (addedCount === 0 && (selectedTeachingType || selectedCycle || selectedSection || selectedDept)) {
                classSelect.innerHTML = '<option value="">' + labels.noClassForCriteria + '</option>';
            }
        }

        if (teachingTypeSelect) teachingTypeSelect.addEventListener('change', filterClasses);
        if (cycleSelect) cycleSelect.addEventListener('change', filterClasses);
        sectionSelect.addEventListener('change', filterClasses);
        departmentSelect.addEventListener('change', filterClasses);
        filterClasses();

        // Photo preview for edit form
        const photoInputEdit = document.getElementById('photoInputEdit');
        if (photoInputEdit) {
            photoInputEdit.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Show preview in a modal or alert
                        alert('Nouvelle photo sélectionnée: ' + file.name);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>