<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Manual - Administrator - NoteMaster</title>
    <style>
        :root {
            --m-primary: #10b981;
            --m-primary-dark: #065f46;
            --m-bg: #ffffff;
            --m-text: #0f172a;
            --m-text-light: #64748b;
            --m-border: #e2e8f0;
            --m-section-bg: #f8fafc;
            --m-tip-bg: #f0fdf4;
            --m-tip-border: #dcfce7;
            --m-tip-text: #166534;
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
            <h1 class="title">User Guide — Administrator</h1>
            <p class="subtitle">Academic Management, Financial Supervision, Report Cards, PV & Transcripts</p>
        </div>

        <div class="section">
            <div class="section-title">1. Administrator Workspace & Navigation</div>
            <p>The <strong>Administrator</strong> role has full access to manage school operations, academic structures, financial oversight, and official reports.</p>
            <div class="feature-box">
                <strong>Navigation Ribbon Tabs:</strong>
                <ul>
                    <li><strong>Control Center:</strong> Teaching Types, Levels, Cycles, Sections, Departments & School Settings.</li>
                    <li><strong>Human Resources:</strong> Student Registration & Directory, Cashier & Teacher Management.</li>
                    <li><strong>Financial Management:</strong> Payments, Fee Schedules, Installments, Discounts, Scholarships, Expenses & Audit.</li>
                    <li><strong>Grade Management:</strong> Sequences, Centralized Grade Entry, Subjects, Subject Groups (UE), and Discipline.</li>
                    <li><strong>Print & Reports:</strong> PDF Report Cards, Honor Rolls, Minutes (PV), and Transcripts.</li>
                </ul>
            </div>
            <div class="tip">
                💡 <strong>Command Palette:</strong> Press <kbd>Cmd+K</kbd> or <kbd>Ctrl+K</kbd> for quick searching across all system modules.
            </div>
        </div>

        <div class="section">
            <div class="section-title">2. Procedure: Computing & Printing Report Cards</div>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">
                    <span class="step-title">Access Report Cards</span>
                    Go to <strong>Print → Report Cards</strong> (`/bulletins`).
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">
                    <span class="step-title">Filter Class & Term</span>
                    Select section, target class, and sequence/trimester.
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">
                    <span class="step-title">Calculation & PDF Batch Download</span>
                    The system automatically computes weighted averages, ranks, and remarks. Download the complete PDF batch ready for printing.
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">3. Procedure: Generating Consolidated Transcripts</div>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">
                    <span class="step-title">Open Transcripts Module</span>
                    Navigate to <strong>Print → Transcripts</strong> (`/transcripts`).
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">
                    <span class="step-title">Select Class & Student</span>
                    Choose class and student. Associated Course Units (UE/UV) load automatically.
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">
                    <span class="step-title">Export PDF Transcript</span>
                    Generate the official consolidated transcript with honor decision.
                </div>
            </div>
        </div>

        <div class="footer">
            NoteMaster © <?= date('Y') ?> Futura CamerTech — Administrator User Manual v2.5
        </div>
    </div>
</body>
</html>
