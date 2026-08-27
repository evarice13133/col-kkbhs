<?php $title = __('assign') . ' - ' . __('teachers_list');
ob_start(); ?>

<div class="animate-fade-in container-fluid py-4">

    <?php if ($success = App\Core\Session::getFlash('success') ?: App\Core\Session::getFlash('success_msg')): ?>
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4 mx-2 rounded-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars((string) $success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($err = App\Core\Session::get('error_msg')): ?>
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show mb-4 mx-2 rounded-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars((string) $err) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php App\Core\Session::remove('error_msg'); ?>
    <?php endif; ?>

    <!-- Header avec informations sur l'affectation -->
    <div class="alert alert-primary border-0 shadow-lg rounded-4 p-4 mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-circle bg-white text-primary rounded-circle d-flex align-items-center justify-content-center"
                    style="width: 50px; height: 50px;">
                    <i class="bi bi-person-plus-fill fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1 text-white"><?= __('choosing_teacher_for') ?> : <span
                            class="text-warning"><?= h($subjectData['nom'] ?? 'Matière inconnue') ?></span></h5>
                    <p class="mb-0 text-white opacity-75 small">
                        <i class="bi bi-door-open me-1"></i> <?= __('class') ?> :
                        <strong><?= h($classData['nom'] ?? 'Classe inconnue') ?></strong>
                    </p>
                </div>
            </div>
            <a href="/" class="btn btn-light rounded-pill px-4 fw-bold">
                <i class="bi bi-x-circle me-1"></i> <?= __('cancel') ?>
            </a>
        </div>
    </div>

    <!-- Liste des enseignants -->
    <div class="modern-card border-0 shadow-sm rounded-4 p-4">
        <h4 class="fw-bold mb-4 text-main-theme"><?= __('teachers_list') ?></h4>
        
        <?php if (empty($teachers)): ?>
            <div class="text-center py-5">
                <i class="bi bi-person-x fs-1 text-muted mb-3 d-block"></i>
                <p class="text-muted"><?= __('no_teachers_available') ?? 'Aucun enseignant disponible' ?></p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><?= __('name') ?></th>
                            <th><?= __('username') ?></th>
                            <th class="text-end"><?= __('action') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teachers as $t): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= h($t['nom'] . ' ' . $t['prenom']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-muted"><?= h($t['username']) ?></span>
                                </td>
                                <td class="text-end">
                                    <a href="/teachers/direct_assign?teacher_id=<?= $t['id'] ?>&subject_id=<?= $subjectId ?>&class_id=<?= $classId ?>"
                                        class="btn btn-sm btn-success rounded-pill px-3 fw-bold">
                                        <i class="bi bi-check2-circle me-1"></i> <?= __('choose') ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<style>
    .modern-card {
        background: var(--bg-card);
        border: 1px solid rgba(var(--primary-rgb), 0.08) !important;
        border-radius: 20px;
    }
    
    .icon-circle {
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../templates/layout.php';
