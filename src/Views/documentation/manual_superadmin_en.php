<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Manual - Super Administrator - NoteMaster</title>
    <style>
        :root {
            --m-primary: #7c3aed;
            --m-primary-dark: #5b21b6;
            --m-bg: #ffffff;
            --m-text: #0f172a;
            --m-text-light: #64748b;
            --m-border: #e2e8f0;
            --m-section-bg: #f5f3ff;
            --m-tip-bg: #ede9fe;
            --m-tip-border: #ddd6fe;
            --m-tip-text: #4c1d95;
        }
        body { font-family: 'Inter', -apple-system, sans-serif; color: var(--m-text); line-height: 1.6; background: var(--m-bg); margin: 0; padding: 0; }
        .container { max-width: 850px; margin: 0 auto; background: var(--m-bg); padding: 40px; }
        .header { text-align: center; margin-bottom: 40px; padding-bottom: 30px; border-bottom: 2px solid var(--m-border); }
        .title { font-family: 'Outfit', sans-serif; color: var(--m-primary); font-size: 32px; font-weight: 800; margin: 0 0 8px 0; }
        .subtitle { color: var(--m-text-light); font-size: 17px; margin: 0; }
        .section { margin-bottom: 45px; }
        .section-title { font-family: 'Outfit', sans-serif; display: flex; align-items: center; gap: 12px; background: var(--m-section-bg); color: var(--m-primary-dark); padding: 12px 20px; border-radius: 12px; font-size: 20px; font-weight: 700; margin-bottom: 20px; border-left: 5px solid var(--m-primary); }
        .step { margin-bottom: 20px; display: flex; gap: 15px; }
        .step-number { background: var(--m-primary); color: #ffffff; min-width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; margin-top: 2px; }
        .step-text { flex: 1; }
        .step-title { font-weight: 700; color: var(--m-text); display: block; margin-bottom: 4px; font-size: 16px; }
        .tip { background: var(--m-tip-bg); border: 1px solid var(--m-tip-border); padding: 15px; border-radius: 12px; margin-top: 15px; font-size: 15px; color: var(--m-tip-text); }
        .feature-box { border: 1px solid var(--m-border); border-radius: 12px; padding: 15px; margin-bottom: 15px; background: #fafafa; }
        .footer { text-align: center; font-size: 12px; color: var(--m-text-light); margin-top: 50px; padding-top: 20px; border-top: 1px solid var(--m-border); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="title">User Guide — Super Administrator</h1>
            <p class="subtitle">Complete System Administration, Database Backups, Year-End Closing & RBAC Security</p>
        </div>

        <div class="section">
            <div class="section-title">1. Super Administrator Workspace</div>
            <p>The <strong>Super Administrator</strong> possesses full, unconstrained control over the NoteMaster SaaS platform.</p>
            <div class="feature-box">
                <strong>Exclusive Responsibilities:</strong>
                <ul>
                    <li><strong>Database Backup & Restore:</strong> One-click system backups and SQL archive restoration (`/settings/run_backup`).</li>
                    <li><strong>Year-End Archiving & Closing:</strong> Campaign closure wizard and read-only year locks (`/academic_years/archive_wizard`).</li>
                    <li><strong>RBAC Security Management:</strong> Role mapping, permission overrides, and security audit logs (`/admin/run-migrations`, `security.log`).</li>
                    <li><strong>System Branding:</strong> School identity, logo configuration by teaching type, and system parameters.</li>
                </ul>
            </div>
        </div>

        <div class="section">
            <div class="section-title">2. Procedure: Running a System Backup</div>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">
                    <span class="step-title">Access System Settings</span>
                    Go to <strong>Control Center → Settings</strong> (`/settings`).
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">
                    <span class="step-title">Trigger Database Backup</span>
                    Click "Run DB Backup". The system generates a complete SQL dump in `storage/backups/`.
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">3. Procedure: Year-End Closing & Campaign Switch</div>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">
                    <span class="step-title">Open Archiving Wizard</span>
                    In <strong>Academic Years</strong> (`/academic_years`), click "Archive" on the completed year.
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">
                    <span class="step-title">Coherence Verification</span>
                    The wizard checks that all report cards and minutes are generated and marks are permanently locked.
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">
                    <span class="step-title">Lock & Activate New Year</span>
                    The past year is locked in read-only mode and the new campaign is activated system-wide.
                </div>
            </div>
        </div>

        <div class="footer">
            NoteMaster © <?= date('Y') ?> Futura CamerTech — Super Administrator User Manual v2.5
        </div>
    </div>
</body>
</html>
