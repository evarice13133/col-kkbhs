<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Manual - Teacher - NoteMaster</title>
    <style>
        :root {
            --m-primary: #3b82f6;
            --m-primary-dark: #1d4ed8;
            --m-bg: #ffffff;
            --m-text: #0f172a;
            --m-text-light: #64748b;
            --m-border: #e2e8f0;
            --m-section-bg: #eff6ff;
            --m-tip-bg: #dbeafe;
            --m-tip-border: #bfdbfe;
            --m-tip-text: #1e40af;
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
            <h1 class="title">User Guide — Teacher</h1>
            <p class="subtitle">Centralized Grade Entry, Student Tracking & Excel Import/Export</p>
        </div>

        <div class="section">
            <div class="section-title">1. Teacher Workspace & Overview</div>
            <p>As a <strong>Teacher</strong>, your workspace is focused on mark entry and academic tracking for your assigned classes.</p>
            <div class="feature-box">
                <strong>Accessible Features:</strong>
                <ul>
                    <li><strong>Enter Marks (`/notes`):</strong> Direct grade entry by class, subject, and evaluation sequence.</li>
                    <li><strong>My Students (`/students`):</strong> View class rosters and individual student details for assigned classes.</li>
                    <li><strong>Help & Documentation (`/documentation`):</strong> View and download role-based user guides.</li>
                </ul>
            </div>
            <div class="tip">
                💡 <strong>Pro Tip:</strong> On the grade entry form, press <kbd>Enter</kbd> or <kbd>Tab</kbd> to automatically jump to the next student.
            </div>
        </div>

        <div class="section">
            <div class="section-title">2. Procedure: Entering Marks for a Sequence</div>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">
                    <span class="step-title">Access Grade Entry</span>
                    Click <strong>Enter Marks</strong> in the ribbon or use the pencil icon in the Quick Access Toolbar.
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">
                    <span class="step-title">Select Filters</span>
                    Choose academic year, target class, assigned subject, and evaluation sequence (e.g. Sequence 1).
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">
                    <span class="step-title">Input & Save Grades</span>
                    Enter grades (between 0 and the max score, e.g., 20). Class average calculates in real-time. Click <strong>Save Grades</strong>.
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">3. Procedure: Excel Import / Export for Mark Sheets</div>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">
                    <span class="step-title">Download Excel Template</span>
                    On the grade entry page, click <strong>Download Excel Template</strong>.
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">
                    <span class="step-title">Fill Grades Offline</span>
                    Input marks offline in Excel without altering student matricule IDs.
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">
                    <span class="step-title">Upload Completed File</span>
                    Click <strong>Upload Excel File</strong> and select your saved spreadsheet. The system validates and imports grades automatically.
                </div>
            </div>
        </div>

        <div class="footer">
            NoteMaster © <?= date('Y') ?> Futura CamerTech — Teacher User Manual v2.5
        </div>
    </div>
</body>
</html>
