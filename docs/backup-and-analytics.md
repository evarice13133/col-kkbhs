# Backup And Analytics

## Weekly backup

Run the backup manually:

```powershell
php scripts/run_weekly_backup.php
```

Install the Windows scheduled task:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/install_weekly_backup_task.ps1 -PhpPath "C:\php\php.exe" -DayOfWeek Sunday -Time 02:00
```

Linux cron equivalent:

```cron
0 2 * * 0 cd /path/to/notesmaster && /usr/bin/php scripts/run_weekly_backup.php >> storage/backups/logs/cron.log 2>&1
```

## GitHub authentication

- Preferred: configure an SSH key for the machine account and keep `backup_github_auth = ssh`.
- Alternative: use HTTPS with Git Credential Manager or a PAT handled by the host OS, not inside the app database.

## What is tracked

- `auth_login` for successful sign-ins
- `page_view` for authenticated GET navigations
- `grades_created` and `grades_updated` for teacher grade activity
- `settings_updated` for admin configuration changes

These events feed the admin dashboard KPIs for weekly/monthly active users, visits, activity volume, and teacher engagement.
