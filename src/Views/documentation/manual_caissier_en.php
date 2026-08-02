<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Manual - Cashier - NoteMaster</title>
    <style>
        :root {
            --m-primary: #059669;
            --m-primary-dark: #047857;
            --m-bg: #ffffff;
            --m-text: #0f172a;
            --m-text-light: #64748b;
            --m-border: #e2e8f0;
            --m-section-bg: #f0fdf4;
            --m-tip-bg: #ecfdf5;
            --m-tip-border: #a7f3d0;
            --m-tip-text: #065f46;
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
            <h1 class="title">User Guide — Cashier</h1>
            <p class="subtitle">Student Registrations, Fee Payments Recording & Secure Cash Receipts</p>
        </div>

        <div class="section">
            <div class="section-title">1. Workspace & Navigation Ribbon</div>
            <p>As a <strong>Cashier</strong>, your workspace is streamlined for fast cashier operations and student registrations.</p>
            <div class="feature-box">
                <strong>Accessible Menus & Tabs:</strong>
                <ul>
                    <li><strong>Home:</strong> Overview dashboard and quick cashier shortcuts.</li>
                    <li><strong>Human Resources / Students:</strong> Student registration (`/students/create`), registered student directory, and my registration history.</li>
                    <li><strong>Financial Management (School Fees & Exemptions):</strong>
                        <ul>
                            <li>Payments & Receipts (`/payments`) — Fee collection and receipt printing.</li>
                            <li>Fee Schedule (`/school_fees/grille`) — View class fee breakdown.</li>
                            <li>Payment Journal (`/school_fees/versements`) — Cash collection transaction history.</li>
                            <li>Insolvent Report (`/school_fees/insolvables`) — Overdue student fee tracking.</li>
                            <li>Discounts & Scholarships (`/discounts`, `/scholarships`) — View exemptions.</li>
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="tip">
                💡 <strong>Quick Shortcut:</strong> Press <kbd>Ctrl+K</kbd> or <kbd>Cmd+K</kbd> anywhere in the application to open the Command Palette and search for a student or action instantly.
            </div>
        </div>

        <div class="section">
            <div class="section-title">2. Procedure: Collecting a Payment & Issuing a Receipt</div>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">
                    <span class="step-title">Access Payments Module</span>
                    Click on <strong>Financial Management → Payments & Receipts</strong> in the ribbon or click the credit card icon in the Quick Access Toolbar.
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">
                    <span class="step-title">Select Student</span>
                    Use the search field to find the student by name, surname, or unique ID number.
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">
                    <span class="step-title">Enter Payment Details</span>
                    Input the amount paid, payment method (Cash, Mobile Money, Bank Transfer), and installment target.
                </div>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-text">
                    <span class="step-title">Generate Secure Receipt</span>
                    Submit the transaction. The system automatically generates a PDF receipt with a <strong>public QR code verification badge</strong>. Print the receipt for the parent.
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">3. Procedure: Registering a New Student</div>
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-text">
                    <span class="step-title">Open Registration Form</span>
                    Go to <strong>Human Resources → Register Student</strong> (`/students/create`).
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">
                    <span class="step-title">Fill Student Details</span>
                    Enter first name, last name, date of birth, gender, target class, and parent/guardian contact details.
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">
                    <span class="step-title">Validation & ID Generation</span>
                    Submit the form. The system assigns a unique matricule ID to the student and registers them immediately.
                </div>
            </div>
        </div>

        <div class="footer">
            NoteMaster © <?= date('Y') ?> Futura CamerTech — Cashier User Manual v2.5
        </div>
    </div>
</body>
</html>
