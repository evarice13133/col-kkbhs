<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Manual - Accountant - NoteMaster</title>
    <style>
        :root {
            --m-primary: #0284c7;
            --m-primary-dark: #0369a1;
            --m-bg: #ffffff;
            --m-text: #0f172a;
            --m-text-light: #64748b;
            --m-border: #e2e8f0;
            --m-section-bg: #f0f9ff;
            --m-tip-bg: #e0f2fe;
            --m-tip-border: #bae6fd;
            --m-tip-text: #0369a1;
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
            <h1 class="title">User Guide — Accountant</h1>
            <p class="subtitle">Global Financial Management, Fee Structure Setup, Operational Expenses & Treasury Audit</p>
        </div>

        <div class="section">
            <div class="section-title">1. Workspace & Financial Responsibilities</div>
            <p>The <strong>Accountant</strong> manages school fee structures, operational expenses, financial audits, and cash reconciliation.</p>
            <div class="feature-box">
                <strong>Accessible Menus & Modules:</strong>
                <ul>
                    <li><strong>Home:</strong> Income/Expense overview dashboard and financial metrics.</li>
                    <li><strong>Financial Management / School Fees:</strong>
                        <ul>
                            <li>Fee Schedule (`/school_fees/grille`) — Set school fees per class.</li>
                            <li>Payment Installments (`/school_fees/tranches`) — Configure installment deadlines.</li>
                            <li>Payment Journal (`/school_fees/versements`) — Complete transaction history.</li>
                            <li>Insolvent Report (`/school_fees/insolvables`) — Track student payment defaults.</li>
                        </ul>
                    </li>
                    <li><strong>Financial Management / Exemptions:</strong>
                        <ul>
                            <li>Discounts & Types (`/discounts`, `/discount_types`) — Manage fee reduction rules.</li>
                            <li>Scholarships (`/scholarships`) — Assign student scholarships.</li>
                        </ul>
                    </li>
                    <li><strong>Financial Management / Expenses & Audit:</strong>
                        <ul>
                            <li>Financial History (`/financial-history`) — General accounting journal.</li>
                            <li>Operational Expenses (`/expenses`) — Record and approve expenses.</li>
                            <li>Expense Categories (`/expenses/categories`) — Budgetary classification.</li>
                            <li>Audit Trail (`/expenses/audit`) — Change history and compliance log.</li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>

        <div class="section">
            <div class="section-title">2. Procedure: Setting Class Fee Schedules</div>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">
                    <span class="step-title">Access Fee Schedule</span>
                    Go to <strong>Financial Management → Fee Schedule</strong> (`/school_fees/grille`).
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">
                    <span class="step-title">Select Class & Teaching Type</span>
                    Filter by teaching type (e.g. General or Technical) and select the target class.
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">
                    <span class="step-title">Configure Total Fee & Installments</span>
                    Enter registration fee, installment 1, 2, and 3 breakdown with due dates.
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">3. Procedure: Recording & Approving Expenses</div>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">
                    <span class="step-title">Open Expenses Module</span>
                    Navigate to <strong>Financial Management → Expenses List</strong> (`/expenses`).
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">
                    <span class="step-title">Create Expense Entry</span>
                    Click "New Expense". Enter motive, amount, category (e.g. Utilities, Salaries, Supplies), and receipt voucher code.
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">
                    <span class="step-title">Validation & Audit Log</span>
                    Submitting logs the expense in the general financial journal and updates the <strong>Audit Trail</strong> (`/expenses/audit`).
                </div>
            </div>
        </div>

        <div class="footer">
            NoteMaster © <?= date('Y') ?> Futura CamerTech — Accountant User Manual v2.5
        </div>
    </div>
</body>
</html>
