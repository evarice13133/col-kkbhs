<?php
$dayOptions = [
    'Sunday' => __('sunday'),
    'Monday' => __('monday'),
    'Tuesday' => __('tuesday'),
    'Wednesday' => __('wednesday'),
    'Thursday' => __('thursday'),
    'Friday' => __('friday'),
    'Saturday' => __('saturday'),
];
?>

<div class="modern-card border-0 shadow-sm overflow-hidden animate-fade-in bg-glass-theme">
    <div class="card-body p-4 p-md-5">
        <div class="row g-4">
            <div class="col-xl-7 mt-0">
                <div class="p-4 border-theme-dynamic rounded-4 bg-soft-light h-100 shadow-sm">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                        <div>
                            <h6 class="fw-black text-primary m-0 text-uppercase letter-spacing-1"><?= __('backup_automation_heading') ?></h6>
                            <p class="text-muted-theme extra-small mb-0 opacity-75"><?= __('backup_automation_subtitle') ?></p>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 px-3 py-2 rounded-pill fw-black extra-small">
                            <?= __('weekly_backup_label') ?>
                        </span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('backup_enabled_label') ?></label>
                            <select name="backup_enabled" class="form-select premium-select">
                                <option value="1" <?= ($settings['backup_enabled'] ?? '1') === '1' ? 'selected' : '' ?>><?= __('enabled') ?></option>
                                <option value="0" <?= ($settings['backup_enabled'] ?? '1') === '0' ? 'selected' : '' ?>><?= __('disabled') ?></option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('backup_push_enabled_label') ?></label>
                            <select name="backup_push_enabled" class="form-select premium-select">
                                <option value="1" <?= ($settings['backup_push_enabled'] ?? '1') === '1' ? 'selected' : '' ?>><?= __('enabled') ?></option>
                                <option value="0" <?= ($settings['backup_push_enabled'] ?? '1') === '0' ? 'selected' : '' ?>><?= __('disabled') ?></option>
                            </select>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('backup_storage_path_label') ?></label>
                            <input type="text" name="backup_storage_path" class="form-control premium-input"
                                value="<?= htmlspecialchars((string) ($settings['backup_storage_path'] ?? 'storage/backups')) ?>">
                            <small class="extra-small text-muted-theme opacity-75 mt-1 d-block"><?= __('backup_storage_path_help') ?></small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('backup_retention_label') ?></label>
                            <input type="number" min="1" max="104" name="backup_retention_count"
                                class="form-control premium-input"
                                value="<?= (int) ($settings['backup_retention_count'] ?? 12) ?>">
                            <small class="extra-small text-muted-theme opacity-75 mt-1 d-block"><?= __('backup_retention_help') ?></small>
                        </div>

                        <div class="col-md-8 mt-4">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('backup_git_worktree_label') ?></label>
                            <input type="text" name="backup_git_worktree" class="form-control premium-input"
                                value="<?= htmlspecialchars((string) ($settings['backup_git_worktree'] ?? 'storage/backup-repository')) ?>">
                            <small class="extra-small text-muted-theme opacity-75 mt-1 d-block"><?= __('backup_git_worktree_help') ?></small>
                        </div>
                        <div class="col-md-4 mt-4">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('backup_github_branch_label') ?></label>
                            <input type="text" name="backup_github_branch" class="form-control premium-input"
                                value="<?= htmlspecialchars((string) ($settings['backup_github_branch'] ?? 'main')) ?>">
                        </div>

                        <div class="col-md-5 mt-4">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('backup_github_owner_label') ?></label>
                            <input type="text" name="backup_github_owner" class="form-control premium-input"
                                value="<?= htmlspecialchars((string) ($settings['backup_github_owner'] ?? 'evarice13133')) ?>">
                        </div>
                        <div class="col-md-5 mt-4">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('backup_github_repo_label') ?></label>
                            <input type="text" name="backup_github_repository" class="form-control premium-input"
                                value="<?= htmlspecialchars((string) ($settings['backup_github_repository'] ?? 'notesmaster-backups')) ?>"
                                placeholder="<?= __('backup_repository_hint') ?>">
                        </div>
                        <div class="col-md-2 mt-4">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('backup_github_auth_label') ?></label>
                            <select name="backup_github_auth" class="form-select premium-select">
                                <option value="ssh" <?= ($settings['backup_github_auth'] ?? 'ssh') === 'ssh' ? 'selected' : '' ?>><?= __('auth_mode_ssh') ?></option>
                                <option value="https" <?= ($settings['backup_github_auth'] ?? 'ssh') === 'https' ? 'selected' : '' ?>><?= __('auth_mode_https') ?></option>
                            </select>
                        </div>

                        <div class="col-md-6 mt-4">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('backup_git_user_name_label') ?></label>
                            <input type="text" name="backup_git_user_name" class="form-control premium-input"
                                value="<?= htmlspecialchars((string) ($settings['backup_git_user_name'] ?? 'NotesMaster Backup Bot')) ?>">
                        </div>
                        <div class="col-md-6 mt-4">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('backup_git_user_email_label') ?></label>
                            <input type="email" name="backup_git_user_email" class="form-control premium-input"
                                value="<?= htmlspecialchars((string) ($settings['backup_git_user_email'] ?? 'backup-bot@notesmaster.local')) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-5 mt-xl-0">
                <div class="p-4 rounded-4 bg-soft-primary border border-primary border-opacity-10 h-100 d-flex flex-column gap-4">
                    <div>
                        <h6 class="fw-black text-primary m-0 text-uppercase extra-small letter-spacing-1 mb-2"><?= __('backup_status_heading') ?></h6>
                        <p class="text-muted-theme extra-small mb-0 opacity-75"><?= __('backup_security_notice_text') ?></p>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('backup_schedule_day_label') ?></label>
                            <select name="backup_schedule_day" class="form-select premium-select">
                                <?php foreach ($dayOptions as $value => $label): ?>
                                    <option value="<?= htmlspecialchars($value) ?>" <?= ($settings['backup_schedule_day'] ?? 'Sunday') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label text-muted-theme fw-bold extra-small text-uppercase mb-1"><?= __('backup_schedule_time_label') ?></label>
                            <input type="time" name="backup_schedule_time" class="form-control premium-input"
                                value="<?= htmlspecialchars((string) ($settings['backup_schedule_time'] ?? '02:00')) ?>">
                        </div>
                    </div>

                    <div class="alert bg-white border-0 shadow-sm mb-0 rounded-4 p-4">
                        <div class="d-flex gap-3 align-items-center mb-2">
                            <div class="avatar-xs bg-soft-warning text-warning rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-shield-lock-fill"></i>
                            </div>
                            <h6 class="fw-bold mb-0 text-main-theme small"><?= __('backup_security_notice_title') ?></h6>
                        </div>
                        <p class="extra-small text-muted-theme mb-0 lh-sm"><?= __('backup_security_notice_text') ?></p>
                    </div>

                    <div class="p-4 bg-white rounded-4 shadow-sm border border-theme-light mt-auto">
                        <h6 class="fw-bold mb-2 text-main-theme small"><?= __('backup_manual_run_heading') ?></h6>
                        <p class="text-muted-theme extra-small mb-3 opacity-75"><?= __('backup_manual_run_text') ?></p>
                        <form action="/settings/run_backup" method="POST" id="runBackupForm">
                            <button type="submit" class="btn btn-outline-primary fw-bold px-4 rounded-pill btn-sm w-100 transition-base"
                                onclick="return confirmRunBackup()">
                                <i class="bi bi-cloud-arrow-up-fill me-2"></i><?= __('run_backup_now') ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>