<?php  $title = __('academic_years'); ob_start(); ?>

    <!-- BARRE D'ACTIONS COMPLÈTE : Style Floating Island -->
    <div class="d-flex justify-content-center mb-5">
        <div class="filter-island px-3 py-2 shadow-lg animate-slide-down" style="min-width: 40%;">
            <div class="d-flex align-items-center justify-content-center gap-2 w-100">
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm scale-on-hover" data-bs-toggle="modal" data-bs-target="#createAcademicYearModal">
                    <i class="bi bi-plus-lg me-2"></i><?= __('add_year') ?>
                </button>
            </div>
        </div>
    </div>

<div class="row g-4">
    <!-- Main column: year list -->
    <div class="col-lg-8" id="academicYearsListContainer">
        <div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in">
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th class="ps-4"><?= __('name') ?></th>
                            <th><?= __('status') ?> & <?= __('access') ?></th>
                            <th><?= __('created_at') ?></th>
                            <th class="text-center pe-4"><?= __('controls') ?></th>
                        </tr>
                    </thead>
                    <tbody class="bg-transparent">
                        <?php foreach($years as $year): ?>
                        <tr class="bg-transparent border-bottom border-light border-opacity-10">
                            <td class="ps-4 bg-transparent">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="registry-icon">
                                        <i class="bi bi-calendar-check"></i>
                                    </div>
                                    <span class="registry-text-main text-muted"><?= htmlspecialchars($year['nom']) ?></span>
                                </div>
                            </td>
                            <td class="bg-transparent">
                                <?php if($year['is_active']): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill small fw-bold">
                                        <i class="bi bi-check-circle me-1"></i><?= __('status_current_active') ?>
                                    </span>
                                <?php elseif($year['status'] === 'archived'): ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill small fw-bold">
                                        <i class="bi bi-archive me-1"></i><?= __('status_archived_closed') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill small fw-bold">
                                        <i class="bi bi-clock me-1"></i><?= __('status_pending_inactive') ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="bg-transparent"><small class="registry-text-muted text-muted"><?= date('d/m/Y', strtotime($year['created_at'])) ?></small></td>
                            <td class="text-center pe-4 bg-transparent">
                                <div class="d-flex justify-content-center gap-1">
                                    <?php if($year['status'] !== 'archived'): ?>
                                        <?php if(!$year['is_active']): ?>
                                            <a href="/academic_years/activate?id=<?= $year['id'] ?>" class="btn btn-sm btn-action-modern text-success" title="<?= __('set_active') ?>">
                                                <i class="bi bi-play-circle fs-5"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (\App\Core\PermissionManager::hasPermission('manage_academic_years')): ?>
                                            <button type="button" class="btn btn-sm btn-action-modern text-primary edit-academic-year-btn" 
                                                    data-id="<?= $year['id'] ?>" 
                                                    data-nom="<?= htmlspecialchars((string) $year['nom'], ENT_QUOTES) ?>" 
                                                    title="<?= __('edit') ?>">
                                                <i class="bi bi-pencil-square fs-5"></i>
                                            </button>
                                        <?php endif; ?>
                                        <a href="/academic_years/archive_wizard?id=<?= $year['id'] ?>" class="btn btn-sm btn-action-modern text-danger" title="<?= __('archive_close') ?>">
                                            <i class="bi bi-archive fs-5"></i>
                                        </a>
                                    <?php else: ?>
                                        <?php if (App\Core\Session::get('user_role') === 'superadmin'): ?>
                                            <a href="/academic_years/unarchive?id=<?= $year['id'] ?>" 
                                               class="btn btn-sm btn-action-modern text-primary" 
                                               title="<?= __('restore') ?>"
                                               onclick="return confirm('<?= addslashes((string) __('confirm_unarchive_year')) ?>')">
                                                <i class="bi bi-arrow-counterclockwise fs-5"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted fw-normal px-2 py-1"><i class="bi bi-lock me-1"></i><?= __('locked') ?></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if (App\Core\Session::get('user_role') === 'superadmin' && !$year['is_active']): ?>
                                        <a href="/academic_years/delete?id=<?= $year['id'] ?>" 
                                           class="btn btn-sm btn-action-modern text-danger" 
                                           title="<?= __('delete') ?>"
                                           onclick="return confirm('<?= addslashes((string) __('confirm_delete_year')) ?>')">
                                            <i class="bi bi-trash fs-5"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <?php if(empty($years)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="bi bi-calendar-x fs-1 opacity-25 mb-3 d-block"></i>
                                <span class="opacity-50"><?= __('no_data') ?></span>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sidebar column: backups -->
    <div class="col-lg-4">
        <div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in" style="animation-delay: 0.1s;">
            <div class="modern-card-header bg-transparent p-4 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-primary bg-opacity-10 rounded-3 text-primary">
                        <i class="bi bi-cloud-download-fill"></i>
                    </div>
                    <h5 class="fw-bold m-0 text-main-theme"><?= __('local_backups') ?></h5>
                </div>
            </div>
            <div class="modern-card-body p-0">
                <div class="list-group list-group-flush border-0">
                    <?php foreach($backups as $backup): ?>
                        <div class="list-group-item bg-transparent border-0 border-bottom border-light border-opacity-10 p-4 d-flex align-items-center justify-content-between gap-3 transition-base hover-bg-light">
                            <div class="d-flex align-items-center gap-3 overflow-hidden">
                                <div class="avatar-sm bg-warning bg-opacity-10 text-warning rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                    <i class="bi bi-file-earmark-zip-fill fs-5"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <div class="fw-bold text-main-theme text-truncate" title="<?= htmlspecialchars($backup['filename']) ?>">
                                        <?= htmlspecialchars($backup['filename']) ?>
                                    </div>
                                    <div class="extra-small text-muted-theme mt-1">
                                        <i class="bi bi-calendar3 me-1"></i><?= $backup['date'] ?> 
                                        <span class="mx-1">•</span> 
                                        <i class="bi bi-hdd-fill me-1"></i><?= round($backup['size'] / 1024, 2) ?> KB
                                    </div>
                                </div>
                            </div>
                            <?php if (App\Core\Session::get('user_role') === 'superadmin'): ?>
                                <a href="/academic_years/restore?file=<?= urlencode($backup['filename']) ?>"
                                   class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold shadow-none"
                                   onclick="return confirm('<?= addslashes((string) __('restore_danger_confirm')) ?>')">
                                    <i class="bi bi-arrow-repeat me-1"></i><?= __('restore') ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if(empty($backups)): ?>
                        <div class="p-5 text-center text-muted-theme">
                            <i class="bi bi-cloud-slash fs-1 opacity-25 d-block mb-2"></i>
                            <p class="small m-0"><?= __('no_data') ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="modern-card mt-4 border-0 shadow-sm border-start border-info border-4 p-4 animate-fade-in" style="animation-delay: 0.2s; background: rgba(var(--info-rgb, 13, 202, 240), 0.05);">
            <div class="d-flex align-items-center gap-2 mb-2 text-info">
                <i class="bi bi-info-circle-fill"></i>
                <h6 class="fw-bold m-0"><?= __('info_label') ?></h6>
            </div>
            <p class="text-muted-theme small m-0 lh-base">
                <?= __('archiving_system_info') ?>
            </p>
        </div>
    </div>
</div>

<!-- MODALE CRÉATION ANNÉE SCOLAIRE -->
<div class="modal fade" id="createAcademicYearModal" tabindex="-1" aria-labelledby="createAcademicYearModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-bottom border-theme-light p-4 bg-primary bg-opacity-10">
                <h5 class="modal-title fw-black text-main-theme" id="createAcademicYearModalLabel">
                    <i class="bi bi-plus-circle-fill me-2 text-primary"></i><?= __('create_new_academic_year') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <?php 
                $isModal = true;
                include __DIR__ . '/_create_form.php'; 
                ?>
            </div>
        </div>
    </div>
</div>

<!-- MODALE MODIFICATION ANNÉE SCOLAIRE -->
<div class="modal fade" id="editAcademicYearModal" tabindex="-1" aria-labelledby="editAcademicYearModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-bottom border-theme-light p-4 bg-warning bg-opacity-10">
                <h5 class="modal-title fw-black text-main-theme" id="editAcademicYearModalLabel">
                    <i class="bi bi-pencil-square me-2 text-warning"></i><?= __('edit_academic_year') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <?php 
                $isModal = true;
                $year = ['id' => '', 'nom' => ''];
                include __DIR__ . '/_edit_form.php'; 
                ?>
            </div>
        </div>
    </div>
</div>

<style>
    /* Floating Island Filters */
    .filter-island {
        background: rgba(var(--bg-card-rgb), 0.7);
        backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(var(--primary-rgb), 0.15);
        border-radius: 100px;
        transition: all 0.3s ease;
    }

    [data-theme="dark"] .filter-island {
        background: rgba(30, 30, 45, 0.6);
        border-color: rgba(255, 255, 255, 0.08);
    }

    .scale-on-hover:hover { transform: scale(1.05); }

    /* Animations */
    .animate-slide-down {
        animation: slideDown 0.6s cubic-bezier(0.23, 1, 0.32, 1);
    }

    @keyframes slideDown {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const createModalEl = document.getElementById('createAcademicYearModal');
    const editModalEl = document.getElementById('editAcademicYearModal');
    const createForm = document.getElementById('academicYearCreateForm');
    const editForm = document.getElementById('academicYearEditForm');

    // Ouverture et préremplissage de la modale d'édition via délégation d'événement
    document.addEventListener('click', function(e) {
        const editBtn = e.target.closest('.edit-academic-year-btn');
        if (editBtn) {
            const yearId = editBtn.getAttribute('data-id');
            const yearNom = editBtn.getAttribute('data-nom');

            const idInput = document.getElementById('edit_academic_year_id');
            const nomInput = document.getElementById('edit_academic_year_nom');

            if (idInput) idInput.value = yearId;
            if (nomInput) nomInput.value = yearNom;

            if (editForm) {
                editForm.action = '/academic_years/update?id=' + yearId;
            }

            if (editModalEl) {
                const bsModal = bootstrap.Modal.getInstance(editModalEl) || new bootstrap.Modal(editModalEl);
                bsModal.show();
            }
        }
    });

    // Soumission AJAX - Création
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(createForm);
            const submitBtn = createForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            fetch(createForm.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async response => {
                const data = await response.json();
                return { ok: response.ok, data: data };
            })
            .then(({ ok, data }) => {
                if (submitBtn) submitBtn.disabled = false;

                if (ok && data.success) {
                    if (createModalEl) {
                        const bsModal = bootstrap.Modal.getInstance(createModalEl) || new bootstrap.Modal(createModalEl);
                        bsModal.hide();
                    }
                    createForm.reset();

                    if (typeof AlertService !== 'undefined') {
                        AlertService.toast('success', data.message);
                    }

                    refreshAcademicYearsList();
                } else {
                    const errorMsg = data.message || "<?= addslashes((string) __('error_occurred')) ?>";
                    if (typeof AlertService !== 'undefined') {
                        AlertService.error("<?= addslashes((string) __('error_title')) ?>", errorMsg);
                    } else {
                        alert(errorMsg);
                    }
                }
            })
            .catch(err => {
                if (submitBtn) submitBtn.disabled = false;
                console.error('Create submit error:', err);
                if (typeof AlertService !== 'undefined') {
                    AlertService.toast('error', "<?= addslashes((string) __('communication_error')) ?>");
                }
            });
        });
    }

    // Soumission AJAX - Édition
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(editForm);
            const submitBtn = editForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            fetch(editForm.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async response => {
                const data = await response.json();
                return { ok: response.ok, data: data };
            })
            .then(({ ok, data }) => {
                if (submitBtn) submitBtn.disabled = false;

                if (ok && data.success) {
                    if (editModalEl) {
                        const bsModal = bootstrap.Modal.getInstance(editModalEl) || new bootstrap.Modal(editModalEl);
                        bsModal.hide();
                    }
                    editForm.reset();

                    if (typeof AlertService !== 'undefined') {
                        AlertService.toast('success', data.message);
                    }

                    refreshAcademicYearsList();
                } else {
                    const errorMsg = data.message || "<?= addslashes((string) __('error_occurred')) ?>";
                    if (typeof AlertService !== 'undefined') {
                        AlertService.error("<?= addslashes((string) __('error_title')) ?>", errorMsg);
                    } else {
                        alert(errorMsg);
                    }
                }
            })
            .catch(err => {
                if (submitBtn) submitBtn.disabled = false;
                console.error('Edit submit error:', err);
                if (typeof AlertService !== 'undefined') {
                    AlertService.toast('error', "<?= addslashes((string) __('communication_error')) ?>");
                }
            });
        });
    }

    function refreshAcademicYearsList() {
        fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.getElementById('academicYearsListContainer');
            const currentContent = document.getElementById('academicYearsListContainer');
            if (newContent && currentContent) {
                currentContent.innerHTML = newContent.innerHTML;
            }
        })
        .catch(err => console.error('Error refreshing academic years list:', err));
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
