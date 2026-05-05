<?php $title = __('my_profile'); ob_start(); ?>
<?php $isTeacher = strtolower((string) ($user['role'] ?? '')) === 'enseignant'; ?>

<div class="page-shell">
    <div class="page-header">
        <div>
            <h1 class="page-title"><?= __('my_profile') ?></h1>
            <?php if (!$isTeacher): ?>
                <p class="page-subtitle"><?= __('my_profile_desc') ?></p>
            <?php endif; ?>
        </div>
        <?php if (!$isTeacher): ?>
            <div class="page-actions">
                <span class="quick-chip"><i class="bi bi-shield-lock-fill"></i><?= htmlspecialchars((string) __($user['role'] ?? '')) ?></span>
            </div>
        <?php endif; ?>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger border-0 shadow-sm"><?= htmlspecialchars((string) $error) ?></div>
    <?php endif; ?>

    <?php if ($msg = App\Core\Session::get('success_msg')): ?>
        <div class="alert alert-success border-0 shadow-sm"><?= htmlspecialchars((string) $msg) ?></div>
        <?php App\Core\Session::remove('success_msg'); ?>
    <?php endif; ?>

    <div class="row g-4">
        <?php if (!$isTeacher): ?>
            <div class="col-lg-4">
                <div class="section-card h-100">
                    <div class="section-card-body text-center">
                        <div class="nm-user-avatar mx-auto mb-3" style="width:72px;height:72px;font-size:1.6rem;"><?= strtoupper(substr(trim($user['prenom'] . ' ' . $user['nom']), 0, 1)) ?></div>
                        <h3 class="h4 mb-1"><?= htmlspecialchars((string) $user['nom']) ?> <?= htmlspecialchars((string) $user['prenom']) ?></h3>
                        <p class="text-muted mb-3"><?= htmlspecialchars((string) $user['email']) ?></p>
                        <span class="quick-chip"><?= htmlspecialchars((string) __($user['role'] ?? '')) ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="<?= $isTeacher ? 'col-12' : 'col-lg-8' ?>">
            <div class="form-shell">
                <form action="/profile/update" method="POST" id="profileForm" class="no-loader">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><?= __('name') ?></label>
                            <input type="text" name="nom" class="form-control form-control-lg" value="<?= htmlspecialchars((string) $user['nom']) ?>" <?= $isTeacher ? '' : 'required' ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?= __('first_name') ?></label>
                            <input type="text" name="prenom" class="form-control form-control-lg" value="<?= htmlspecialchars((string) $user['prenom']) ?>" <?= $isTeacher ? '' : 'required' ?>>
                        </div>
                        <div class="col-12">
                            <label class="form-label"><?= __('login_email') ?></label>
                            <input type="email" name="email" class="form-control form-control-lg" value="<?= htmlspecialchars((string) $user['email']) ?>" <?= $isTeacher ? '' : 'required' ?>>
                        </div>
                        <div class="col-12">
                            <label class="form-label"><?= __('new_password') ?></label>
                            <input type="password" name="password" class="form-control form-control-lg" placeholder="<?= __('new_password_placeholder') ?>">
                            <div class="form-text"><?= __('new_password_help') ?></div>
                        </div>
                    </div>
                    <div class="filter-actions mt-4">
                        <button type="submit" class="btn btn-primary btn-lg"><?= __('save_modifications') ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('profileForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        if (form.dataset.confirmed === 'true') return;
        
        e.preventDefault();
        
        const passwordInput = form.querySelector('input[name="password"]');
        const isChangingPassword = passwordInput && passwordInput.value.trim() !== '';
        
        const htmlContent = `
            <div style="font-size: 0.85rem; color: #000000;">
                <p class="mb-2 fw-medium"><?= json_encode(__('confirm_profile_update'), JSON_UNESCAPED_UNICODE) ?></p>
                ${isChangingPassword ? `
                    <div class="d-inline-block px-3 py-1 rounded-pill bg-danger-subtle text-danger fw-bold small">
                        <i class="bi bi-shield-lock-fill me-1"></i> <?= json_encode(__('password_change_detected'), JSON_UNESCAPED_UNICODE) ?>
                    </div>
                ` : ''}
            </div>
        `;

        AlertService.confirm({
            title: <?= json_encode(__('confirmation'), JSON_UNESCAPED_UNICODE) ?>,
            html: htmlContent,
            icon: isChangingPassword ? 'warning' : 'question',
            confirmText: <?= json_encode(__('confirm'), JSON_UNESCAPED_UNICODE) ?>,
            cancelText: <?= json_encode(__('cancel'), JSON_UNESCAPED_UNICODE) ?>,
            width: '320px',
            background: '#ffffff',
            customClass: {
                popup: 'rounded-4 shadow-sm p-3 border border-light',
                title: 'text-black fw-bolder fs-5',
                confirmButton: 'btn btn-primary btn-sm w-100 mb-2 rounded-pill',
                cancelButton: 'btn btn-light btn-sm w-100 rounded-pill',
                actions: 'd-flex flex-column w-100 gap-1'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                form.dataset.confirmed = 'true';
                AlertService.loading(<?= json_encode(__('saving'), JSON_UNESCAPED_UNICODE) ?>);
                setTimeout(() => form.submit(), 50);
            }
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../templates/layout.php';
?>
