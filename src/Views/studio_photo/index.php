<?php
$title = __('studio_photo_title');
ob_start();
?>
<style>
    .studio-photo-page {
        --bs-body-color: var(--text-main, #0f172a);
        color: var(--text-main, #0f172a);
    }

    .studio-photo-page .card {
        background-color: var(--bg-card, #fff);
        border-color: var(--border-color, #e2e8f0) !important;
        color: var(--text-main, #0f172a);
    }

    .studio-photo-page .text-muted {
        color: var(--text-muted, #64748b) !important;
    }

    .studio-photo-page .photo-stage {
        min-height: 380px;
        background: #0f172a;
    }

    .studio-photo-page .photo-stage video,
    .studio-photo-page .photo-stage img {
        display: block;
        width: 100%;
        max-height: 65vh;
        object-fit: contain;
    }

    .studio-photo-page .photo-stage img {
        background: #e2e8f0;
    }

    .studio-photo-guide {
        background: #fff;
        border: 1px solid var(--border-color, #e2e8f0);
        border-radius: 1rem;
        box-shadow: 0 .125rem .5rem rgba(15, 23, 42, .06);
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
    }

    .studio-photo-guide-heading {
        display: flex;
        align-items: center;
        gap: .65rem;
        margin-bottom: 1rem;
    }

    .studio-photo-guide-icon {
        display: inline-grid;
        flex: 0 0 2.25rem;
        width: 2.25rem;
        height: 2.25rem;
        place-items: center;
        border-radius: .7rem;
        background: rgba(13, 110, 253, .1);
        color: var(--bs-primary);
        font-size: 1.05rem;
    }

    .studio-photo-guide-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-main, #0f172a);
    }

    .studio-photo-steps {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .75rem;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .studio-photo-step {
        display: flex;
        align-items: center;
        gap: .6rem;
        min-width: 0;
        color: var(--text-main, #0f172a);
        font-size: .84rem;
        font-weight: 600;
        line-height: 1.25;
    }

    .studio-photo-step-number {
        display: inline-grid;
        flex: 0 0 1.85rem;
        width: 1.85rem;
        height: 1.85rem;
        place-items: center;
        border: 1px solid rgba(13, 110, 253, .22);
        border-radius: 50%;
        background: rgba(13, 110, 253, .08);
        color: var(--bs-primary);
        font-size: .8rem;
        font-weight: 700;
    }

    .studio-photo-step.is-current {
        color: var(--bs-primary);
    }

    .studio-photo-step.is-current .studio-photo-step-number {
        background: var(--bs-primary);
        border-color: var(--bs-primary);
        color: #fff;
        box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .14);
    }

    .studio-photo-step.is-complete .studio-photo-step-number {
        background: #198754;
        border-color: #198754;
        color: #fff;
    }

    [data-theme="dark"] .studio-photo-guide {
        background: #1e293b;
        border-color: rgba(255, 255, 255, .12);
        box-shadow: 0 .125rem .5rem rgba(0, 0, 0, .16);
    }

    [data-theme="dark"] .studio-photo-guide-icon,
    [data-theme="dark"] .studio-photo-step-number {
        background: rgba(96, 165, 250, .14);
        border-color: rgba(96, 165, 250, .28);
        color: #93c5fd;
    }

    [data-theme="dark"] .studio-photo-step.is-current .studio-photo-step-number {
        background: #3b82f6;
        border-color: #3b82f6;
        color: #fff;
    }

    .studio-photo-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
    }

    .studio-photo-save-bar {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: .75rem;
        margin-top: 1.5rem;
    }

    [data-theme="dark"] .studio-photo-page .card {
        background-color: #1e293b;
        border-color: rgba(255, 255, 255, .12) !important;
        color: #f8fafc;
    }

    [data-theme="dark"] .studio-photo-page .text-muted {
        color: #94a3b8 !important;
    }

    @media (max-width: 575.98px) {
        .studio-photo-page {
            padding-top: .75rem !important;
            padding-bottom: 1rem !important;
            padding-right: .25rem !important;
            padding-left: .25rem !important;
            margin-right: -.5rem;
            margin-left: -.5rem;
            width: calc(100% + 1rem);
        }

        .studio-photo-page > .mb-4 {
            margin-bottom: 1rem !important;
        }

        .studio-photo-page .card-body {
            padding: .5rem !important;
        }

        .studio-photo-page .studio-photo-row {
            --bs-gutter-x: .5rem;
            --bs-gutter-y: .5rem;
        }

        .studio-photo-page .studio-photo-guide {
            padding: .85rem;
            margin-bottom: .75rem;
        }

        .studio-photo-page .studio-photo-steps {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .7rem .5rem;
        }

        .studio-photo-page .studio-photo-step {
            gap: .45rem;
            font-size: .78rem;
        }

        .studio-photo-page .studio-photo-step-number {
            flex-basis: 1.7rem;
            width: 1.7rem;
            height: 1.7rem;
        }

        .studio-photo-page .studio-photo-actions {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .5rem;
        }

        .studio-photo-page .studio-photo-actions > * {
            width: 100%;
            min-width: 0;
            min-height: 48px !important;
            margin: 0 !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            white-space: normal;
        }

        .studio-photo-page #startCameraBtn,
        .studio-photo-page #captureBtn,
        .studio-photo-page .studio-photo-actions label {
            min-height: 48px !important;
        }

        .studio-photo-page .photo-stage {
            min-height: 0;
            height: clamp(280px, 52svh, 380px);
        }

        .studio-photo-page .photo-stage video,
        .studio-photo-page .photo-stage img {
            width: 100%;
            height: 100%;
            max-height: none;
            object-fit: contain;
        }

        .studio-photo-page .studio-photo-save-bar {
            position: sticky;
            bottom: 0;
            z-index: 2;
            align-items: stretch;
            margin-right: -1rem;
            margin-bottom: -1rem;
            margin-left: -1rem;
            padding: .75rem 1rem calc(.75rem + env(safe-area-inset-bottom));
            border-top: 1px solid var(--border-color, #e2e8f0);
            background: var(--bg-card, #fff);
        }

        [data-theme="dark"] .studio-photo-page .studio-photo-save-bar {
            background: #1e293b;
            border-color: rgba(255, 255, 255, .12);
        }

        .studio-photo-page #savePhotoBtn {
            width: 100%;
            min-height: 50px !important;
        }

        .studio-photo-page > .row > .col-xl-4 {
            display: none;
        }
    }
</style>

<div class="container-fluid py-4 studio-photo-page">
    <div class="mb-4">
        <h1 class="fw-bold mb-1 text-main-theme"><?= h(__('studio_photo_title')) ?></h1>
        <p class="text-muted mb-0"><?= h(__('studio_photo_description')) ?></p>
    </div>

    <section class="studio-photo-guide" aria-labelledby="studioPhotoGuideTitle">
        <div class="studio-photo-guide-heading">
            <span class="studio-photo-guide-icon" aria-hidden="true"><i class="bi bi-list-check"></i></span>
            <h2 id="studioPhotoGuideTitle" class="studio-photo-guide-title"><?= h(__('studio_photo_guide_title')) ?></h2>
        </div>
        <ol class="studio-photo-steps" aria-label="<?= h(__('studio_photo_steps_aria')) ?>">
            <li class="studio-photo-step"><span class="studio-photo-step-number">1</span><span><?= h(__('studio_photo_step_class')) ?></span></li>
            <li class="studio-photo-step"><span class="studio-photo-step-number">2</span><span><?= h(__('studio_photo_step_student')) ?></span></li>
            <li class="studio-photo-step"><span class="studio-photo-step-number">3</span><span><?= h(__('studio_photo_step_photo')) ?></span></li>
            <li class="studio-photo-step"><span class="studio-photo-step-number">4</span><span><?= h(__('studio_photo_step_save')) ?></span></li>
        </ol>
    </section>

    <div class="row g-4 studio-photo-row">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label for="classSelect" class="form-label fw-semibold"><?= h(__('studio_photo_class')) ?></label>
                        <select id="classSelect" class="form-select" <?= empty($classes) ? 'disabled' : '' ?>>
                            <option value=""><?= h(__('studio_photo_select_class')) ?></option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= (int) $class['id'] ?>">
                                    <?= h($class['teaching_type_name'] . ' — ' . $class['nom'] . ' (' . (int) $class['student_count'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="studentSelect" class="form-label fw-semibold"><?= h(__('studio_photo_student')) ?></label>
                        <select id="studentSelect" class="form-select" disabled>
                            <option value=""><?= h(__('studio_photo_select_class_first')) ?></option>
                        </select>
                        <div id="classProgressGroup" class="mt-2" hidden>
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                <span id="classProgress" class="small text-muted" aria-live="polite"></span>
                                <span id="classProgressPercent" class="small fw-semibold text-muted"></span>
                            </div>
                            <div class="progress" style="height:6px" role="progressbar" aria-label="<?= h(__('studio_photo_class_progress_aria')) ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                <div id="classProgressBar" class="progress-bar bg-success" style="width:0%"></div>
                            </div>
                        </div>
                        <div id="classHelp" class="form-text" aria-live="polite">
                            <?= h(empty($classes) ? __('studio_photo_no_eligible_classes') : __('studio_photo_choose_class_first')) ?>
                        </div>
                    </div>

                    <div class="studio-photo-actions mb-3">
                        <button type="button" id="startCameraBtn" class="btn btn-primary" disabled>
                            <i class="bi bi-camera-video me-1"></i> <?= h(__('studio_photo_open_camera')) ?>
                        </button>
                        <button type="button" id="captureBtn" class="btn btn-outline-primary" disabled>
                            <i class="bi bi-camera me-1"></i> <?= h(__('studio_photo_take_photo')) ?>
                        </button>
                        <label id="importPhotoBtn" for="imageFileInput" class="btn btn-outline-secondary mb-0 disabled" aria-disabled="true">
                            <i class="bi bi-upload me-1"></i> <?= h(__('studio_photo_import_photo')) ?>
                        </label>
                        <input type="file" id="imageFileInput" accept="image/jpeg,image/png,image/webp" hidden disabled>
                        <button type="button" id="resetBtn" class="btn btn-outline-danger">
                            <?= h(__('studio_photo_reset')) ?>
                        </button>
                    </div>

                    <div class="photo-stage border rounded-4 overflow-hidden d-flex align-items-center justify-content-center">
                        <video id="cameraPreview" autoplay playsinline muted style="display:none"></video>
                        <img id="photoPreview" alt="<?= h(__('studio_photo_preview_alt')) ?>" style="display:none">
                        <div id="photoPlaceholder" class="text-center text-white-50 px-3">
                            <i class="bi bi-person-bounding-box d-block mb-2" style="font-size:2.5rem"></i>
                            <span><?= h(__('studio_photo_placeholder')) ?><br><?= h(__('studio_photo_placeholder_help')) ?></span>
                        </div>
                    </div>
                    <p class="small text-muted mt-2 mb-0">
                        <?= h(__('studio_photo_quality_note')) ?>
                    </p>

                    <div class="studio-photo-save-bar">
                        <div id="statusText" class="small text-muted" role="status" aria-live="polite">
                            <?= h(__('studio_photo_initial_status')) ?>
                        </div>
                        <button type="button" id="savePhotoBtn" class="btn btn-success" disabled>
                            <i class="bi bi-person-check me-1"></i> <?= h(__('studio_photo_save')) ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold"><?= h(__('studio_photo_info_title')) ?></h2>
                    <p class="text-muted small mb-0">
                        <?= h(__('studio_photo_info')) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const csrfToken = <?= json_encode(\App\Core\Session::generateCsrfToken(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const messages = <?= json_encode([
        'checkPreview' => __('studio_photo_check_preview'),
        'cameraUnavailable' => __('studio_photo_camera_unavailable'),
        'connectingCamera' => __('studio_photo_connecting_camera'),
        'cameraReady' => __('studio_photo_camera_ready'),
        'cameraDenied' => __('studio_photo_camera_denied'),
        'cameraWait' => __('studio_photo_camera_wait'),
        'preparingPhoto' => __('studio_photo_preparing'),
        'captureFailed' => __('studio_photo_capture_failed'),
        'captureTooLarge' => __('studio_photo_capture_too_large'),
        'readFailed' => __('studio_photo_read_failed'),
        'chooseFormat' => __('studio_photo_choose_format'),
        'fileTooLarge' => __('studio_photo_file_too_large'),
        'studentChanged' => __('studio_photo_student_changed'),
        'selectBeforeSave' => __('studio_photo_select_before_save'),
        'saving' => __('studio_photo_saving'),
        'saved' => __('studio_photo_saved'),
        'saveFailed' => __('studio_photo_save_failed'),
        'selectClassFirst' => __('studio_photo_select_class_first'),
        'selectStudent' => __('studio_photo_select_student'),
        'chooseClassFirst' => __('studio_photo_choose_class_first'),
        'classLoadFailed' => __('studio_photo_class_load_failed'),
        'studentHasPhoto' => __('studio_photo_student_has_photo'),
        'classProgress' => __('studio_photo_class_progress'),
        'classProgressAria' => __('studio_photo_class_progress_aria'),
        'classComplete' => __('studio_photo_class_complete'),
        'noEligibleClasses' => __('studio_photo_no_eligible_classes'),
        'allStudentsHavePhotos' => __('studio_photo_all_students_have_photos'),
        'initialStatus' => __('studio_photo_choose_class_first'),
        'choosePhoto' => __('studio_photo_choose_student_photo'),
        'classLoading' => __('studio_photo_loading_students'),
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const studentSelect = document.getElementById('studentSelect');
    const classSelect = document.getElementById('classSelect');
    const classProgress = document.getElementById('classProgress');
    const classProgressGroup = document.getElementById('classProgressGroup');
    const classProgressBar = document.getElementById('classProgressBar');
    const classProgressPercent = document.getElementById('classProgressPercent');
    const classHelp = document.getElementById('classHelp');
    const imageFileInput = document.getElementById('imageFileInput');
    const cameraPreview = document.getElementById('cameraPreview');
    const photoPreview = document.getElementById('photoPreview');
    const photoPlaceholder = document.getElementById('photoPlaceholder');
    const startCameraBtn = document.getElementById('startCameraBtn');
    const importPhotoBtn = document.getElementById('importPhotoBtn');
    const captureBtn = document.getElementById('captureBtn');
    const savePhotoBtn = document.getElementById('savePhotoBtn');
    const statusText = document.getElementById('statusText');
    let stream = null;
    let photoData = '';
    let classStudents = [];
    let isSaving = false;
    let isLoadingClass = false;

    function updateGuideSteps() {
        const classComplete = classStudents.length > 0 && classStudents.every((student) => student.has_photo);
        const currentStep = !classSelect.value || classComplete ? 0 : !studentSelect.value ? 1 : !photoData ? 2 : 3;
        document.querySelectorAll('.studio-photo-step').forEach((step, index) => {
            const isCurrent = index === currentStep;
            step.classList.toggle('is-current', isCurrent);
            step.classList.toggle('is-complete', index < currentStep);
            if (isCurrent) {
                step.setAttribute('aria-current', 'step');
            } else {
                step.removeAttribute('aria-current');
            }
        });
    }

    function updatePhotoControls() {
        const canWorkWithStudent = Boolean(classSelect.value && studentSelect.value && classStudents.length && !isLoadingClass && !isSaving);
        const classComplete = classStudents.length > 0 && classStudents.every((student) => student.has_photo);
        classSelect.disabled = isLoadingClass || isSaving || (classStudents.length > 0 && !classComplete);
        studentSelect.disabled = isLoadingClass || isSaving || classStudents.length === 0;
        startCameraBtn.disabled = !canWorkWithStudent;
        imageFileInput.disabled = !canWorkWithStudent;
        importPhotoBtn.classList.toggle('disabled', !canWorkWithStudent);
        importPhotoBtn.setAttribute('aria-disabled', String(!canWorkWithStudent));
        savePhotoBtn.disabled = !photoData || !canWorkWithStudent;
        updateGuideSteps();
    }

    function updateClassProgress() {
        const completeCount = classStudents.filter((student) => student.has_photo).length;
        const total = classStudents.length;
        const complete = total > 0 && completeCount === total;
        const percent = total > 0 ? Math.round((completeCount / total) * 100) : 0;
        classProgress.textContent = complete
            ? messages.classComplete
            : messages.classProgress.replace(':done', completeCount).replace(':total', total);
        classProgressGroup.hidden = total === 0;
        classHelp.hidden = total > 0;
        classProgressBar.style.width = `${percent}%`;
        classProgressBar.parentElement.setAttribute('aria-valuenow', String(percent));
        classProgressPercent.textContent = `${percent}%`;
        if (complete) {
            classProgress.textContent = messages.classComplete + ' ' + messages.allStudentsHavePhotos;
        }
        updatePhotoControls();
    }

    function clearPhoto() {
        photoData = '';
        photoPreview.removeAttribute('src');
        photoPreview.style.display = 'none';
        photoPlaceholder.style.display = 'block';
        imageFileInput.value = '';
        stopCamera();
        updatePhotoControls();
    }

    async function loadClassStudents(classId) {
        clearPhoto();
        classStudents = [];
        isLoadingClass = Boolean(classId);
        studentSelect.disabled = true;
        updatePhotoControls();
        studentSelect.innerHTML = `<option value="">${messages.selectClassFirst}</option>`;
        if (!classId) {
            classProgressGroup.hidden = true;
            classHelp.hidden = false;
            classHelp.textContent = messages.chooseClassFirst;
            isLoadingClass = false;
            updatePhotoControls();
            setStatus(messages.initialStatus);
            return;
        }
        classProgressGroup.hidden = true;
        classHelp.hidden = false;
        classHelp.textContent = messages.classLoading;
        try {
            const response = await fetch(`/studio-photo/students?class_id=${encodeURIComponent(classId)}`);
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                throw new Error(payload.error || messages.classLoadFailed);
            }
            classStudents = payload.students;
            isLoadingClass = false;
            studentSelect.innerHTML = `<option value="">${messages.selectStudent}</option>`;
            classStudents.forEach((student) => {
                const option = document.createElement('option');
                option.value = String(student.id);
                option.textContent = student.name + (student.has_photo ? ` — ${messages.studentHasPhoto}` : '');
                studentSelect.appendChild(option);
            });
            studentSelect.disabled = classStudents.length === 0;
            updateClassProgress();
            const firstPending = classStudents.find((student) => !student.has_photo);
            if (firstPending) {
                studentSelect.value = String(firstPending.id);
            } else if (classStudents.length > 0) {
                studentSelect.value = String(classStudents[0].id);
            } else {
                classHelp.textContent = messages.noEligibleClasses;
            }
            updatePhotoControls();
            updateClassProgress();
            setStatus(classStudents.length ? messages.choosePhoto : messages.noEligibleClasses);
        } catch (error) {
            classSelect.value = '';
            isLoadingClass = false;
            classStudents = [];
            studentSelect.innerHTML = `<option value="">${messages.selectClassFirst}</option>`;
            classProgressGroup.hidden = true;
            classHelp.hidden = false;
            classHelp.textContent = messages.classLoadFailed;
            updatePhotoControls();
            setStatus(error.message || messages.classLoadFailed, 'danger');
        }
    }

    function setStatus(message, type = 'muted') {
        statusText.textContent = message;
        statusText.className = `small text-${type}`;
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach((track) => track.stop());
            stream = null;
        }
        cameraPreview.pause();
        cameraPreview.srcObject = null;
        cameraPreview.style.display = 'none';
        captureBtn.disabled = true;
    }

    function setPhoto(dataUrl) {
        photoData = dataUrl;
        photoPreview.src = dataUrl;
        photoPreview.style.display = 'block';
        cameraPreview.style.display = 'none';
        photoPlaceholder.style.display = 'none';
        stopCamera();
        updatePhotoControls();
        setStatus(messages.checkPreview, 'primary');
    }

    startCameraBtn.addEventListener('click', async function () {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            setStatus(messages.cameraUnavailable, 'danger');
            return;
        }

        stopCamera();
        photoData = '';
        photoPreview.removeAttribute('src');
        photoPreview.style.display = 'none';
        photoPlaceholder.style.display = 'none';
        updatePhotoControls();
        setStatus(messages.connectingCamera, 'primary');
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: { ideal: 'user' },
                    width: { ideal: 1920 },
                    height: { ideal: 1080 }
                }
            });
            cameraPreview.srcObject = stream;
            cameraPreview.style.display = 'block';
            photoPreview.style.display = 'none';
            photoPlaceholder.style.display = 'none';
            captureBtn.disabled = false;
            await cameraPreview.play();
            setStatus(messages.cameraReady, 'success');
        } catch (error) {
            stopCamera();
            photoPlaceholder.style.display = 'block';
            setStatus(messages.cameraDenied, 'danger');
        }
    });

    captureBtn.addEventListener('click', function () {
        if (!stream || !cameraPreview.videoWidth || !cameraPreview.videoHeight) {
            setStatus(messages.cameraWait, 'warning');
            return;
        }

        const canvas = document.createElement('canvas');
        canvas.width = cameraPreview.videoWidth;
        canvas.height = cameraPreview.videoHeight;
        canvas.getContext('2d').drawImage(cameraPreview, 0, 0, canvas.width, canvas.height);
        captureBtn.disabled = true;
        setStatus(messages.preparingPhoto, 'primary');
        canvas.toBlob(function (blob) {
            if (!blob) {
                captureBtn.disabled = false;
                setStatus(messages.captureFailed, 'danger');
                return;
            }
            if (blob.size > 20 * 1024 * 1024) {
                captureBtn.disabled = false;
                setStatus(messages.captureTooLarge, 'danger');
                return;
            }
            const reader = new FileReader();
            reader.onload = function () {
                if (typeof reader.result !== 'string') {
                    captureBtn.disabled = false;
                    setStatus(messages.readFailed, 'danger');
                    return;
                }
                setPhoto(reader.result);
            };
            reader.onerror = function () {
                captureBtn.disabled = false;
                setStatus(messages.readFailed, 'danger');
            };
            reader.readAsDataURL(blob);
        }, 'image/png');
    });

    imageFileInput.addEventListener('change', function () {
        const file = imageFileInput.files[0];
        if (!file) {
            return;
        }
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            imageFileInput.value = '';
            setStatus(messages.chooseFormat, 'danger');
            return;
        }
        if (file.size > 20 * 1024 * 1024) {
            imageFileInput.value = '';
            setStatus(messages.fileTooLarge, 'danger');
            return;
        }

        const reader = new FileReader();
        reader.onload = function () {
            if (typeof reader.result !== 'string') {
                setStatus(messages.readFailed, 'danger');
                return;
            }
            setPhoto(reader.result);
        };
        reader.onerror = function () {
            setStatus(messages.readFailed, 'danger');
        };
        reader.readAsDataURL(file);
    });

    studentSelect.addEventListener('change', function () {
        if (photoData || stream) {
            clearPhoto();
            setStatus(messages.studentChanged, 'warning');
            return;
        }
        updatePhotoControls();
    });

    classSelect.addEventListener('change', function () {
        loadClassStudents(classSelect.value);
    });

    document.getElementById('resetBtn').addEventListener('click', function () {
        stopCamera();
        photoData = '';
        photoPreview.removeAttribute('src');
        photoPreview.style.display = 'none';
        photoPlaceholder.style.display = 'block';
        imageFileInput.value = '';
        updatePhotoControls();
        setStatus(<?= json_encode(__('studio_photo_initial_status'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
    });

    savePhotoBtn.addEventListener('click', async function () {
        if (!classSelect.value || !studentSelect.value || !photoData || isSaving) {
            setStatus(messages.selectBeforeSave, 'warning');
            return;
        }

        isSaving = true;
        updatePhotoControls();
        setStatus(messages.saving, 'primary');
        try {
            const response = await fetch('/studio-photo/save', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: new URLSearchParams({
                    class_id: classSelect.value,
                    student_id: studentSelect.value,
                    image_data: photoData,
                    csrf_token: csrfToken
                })
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                throw new Error(payload.error || messages.saveFailed);
            }
            setStatus(payload.message || messages.saved, 'success');
            const savedStudent = classStudents.find((student) => student.id === Number(studentSelect.value));
            if (savedStudent) {
                savedStudent.has_photo = true;
                const currentStudentId = studentSelect.value;
                const currentOption = studentSelect.options[studentSelect.selectedIndex];
                if (currentOption && !currentOption.textContent.includes(messages.studentHasPhoto)) {
                    currentOption.textContent += ` — ${messages.studentHasPhoto}`;
                }
                updateClassProgress();
                const nextPending = classStudents.find((student) => !student.has_photo);
                if (nextPending) {
                    studentSelect.value = String(nextPending.id);
                } else {
                    studentSelect.value = currentStudentId;
                }
            }
            clearPhoto();
        } catch (error) {
            setStatus(error.message || messages.saveFailed, 'danger');
        } finally {
            isSaving = false;
            updatePhotoControls();
        }
    });

    window.addEventListener('beforeunload', stopCamera);
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
