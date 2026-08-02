<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Manual - IT Manager - NoteMaster</title>
    <style>
        :root {
            --m-primary: #6366f1;
            --m-primary-dark: #4338ca;
            --m-bg: #ffffff;
            --m-text: #0f172a;
            --m-text-light: #64748b;
            --m-border: #e2e8f0;
            --m-section-bg: #eef2ff;
            --m-tip-bg: #e0e7ff;
            --m-tip-border: #c7d2fe;
            --m-tip-text: #3730a3;
        }
        body { font-family: 'Inter', -apple-system, sans-serif; color: var(--m-text); line-height: 1.6; background: var(--m-bg); margin: 0; padding: 0; }
        .container { max-width: 850px; margin: 0 auto; background: var(--m-bg); padding: 40px; }
        .header { text-align: center; margin-bottom: 40px; padding-bottom: 30px; border-bottom: 2px solid var(--m-border); }
        .title { font-family: 'Outfit', sans-serif; color: var(--m-primary); font-size: 30px; font-weight: 800; margin: 0 0 8px 0; }
        .subtitle { color: var(--m-text-light); font-size: 16px; margin: 0; }
        .section { margin-bottom: 40px; }
        .section-title { font-family: 'Outfit', sans-serif; display: flex; align-items: center; gap: 12px; background: var(--m-section-bg); color: var(--m-primary-dark); padding: 12px 20px; border-radius: 12px; font-size: 19px; font-weight: 700; margin-bottom: 20px; border-left: 5px solid var(--m-primary); }
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
            <h1 class="title">User Guide — IT Manager</h1>
            <p class="subtitle">Technical Management, Academic Years Setup, User Accounts & Teacher Assignments</p>
        </div>

        <div class="section">
            <div class="section-title">1. Workspace & Technical Role Overview</div>
            <p>The <strong>IT Manager</strong> manages academic infrastructure, user accounts, system configuration, and teacher-class assignments.</p>
            <div class="feature-box">
                <strong>Accessible Menus & Rights:</strong>
                <ul>
                    <li><strong>Control Center / Academic Structure:</strong>
                        <ul>
                            <li>Academic Years (`/academic_years`) — Create, activate, close, and archive school campaigns.</li>
                            <li>Departments (`/departments`) — Subject department organization.</li>
                        </ul>
                    </li>
                    <li><strong>Accounts / Staff:</strong>
                        <ul>
                            <li>Users (`/users`) — User account management (Teachers, Cashiers, Clerks).</li>
                            <li>Teaching Staff (`/teachers`) — Subject assignment and class teacher allocation.</li>
                        </ul>
                    </li>
                    <li><strong>Pedagogy & Students:</strong> Class viewing, student records, mark supervision, and control reports.</li>
                </ul>
            </div>
        </div>

        <div class="section">
            <div class="section-title">2. Procedure: Creating & Activating an Academic Year</div>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">
                    <span class="step-title">Access Academic Years</span>
                    Go to <strong>Control Center → Academic Years</strong> (`/academic_years`).
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">
                    <span class="step-title">Create New Year Entry</span>
                    Input label (e.g. `2026-2027`), start date, and end date.
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">
                    <span class="step-title">Set Active Campaign</span>
                    Click "Activate". The application sets this year as the active working period school-wide.
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">3. Procedure: Assigning Teachers to Subjects & Classes</div>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">
                    <span class="step-title">Open Teacher Register</span>
                    Go to <strong>Account Management → Teachers</strong> (`/teachers`).
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">
                    <span class="step-title">Select Teacher</span>
                    Click the <strong>Assign</strong> button next to the teacher's profile.
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">
                    <span class="step-title">Bind Subjects & Classes</span>
                    Check taught classes and subjects for this instructor and save.
                </div>
            </div>
        </div>

        <div class="footer">
            NoteMaster © <?= date('Y') ?> Futura CamerTech — IT Manager User Manual v2.5
        </div>
    </div>
</body>
</html>
