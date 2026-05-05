<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        :root {
            --m-primary: #8b5cf6;
            --m-primary-dark: #4c1d95;
            --m-bg: #ffffff;
            --m-text: #1e293b;
            --m-text-light: #64748b;
            --m-border: #e2e8f0;
            --m-section-bg: #f8fafc;
            --m-tip-bg: #f5f3ff;
            --m-tip-border: #ddd6fe;
            --m-tip-text: #5b21b6;
        }
        body { 
            font-family: 'Inter', -apple-system, sans-serif; 
            color: var(--m-text); 
            line-height: 1.6; 
            background: var(--m-bg);
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 850px;
            margin: 0 auto;
            background: var(--m-bg);
            padding: 40px;
        }
        .header { 
            text-align: center; 
            margin-bottom: 40px; 
            padding-bottom: 30px;
            border-bottom: 2px solid var(--m-border);
        }
        .title { 
            font-family: 'Outfit', sans-serif;
            color: var(--m-primary); 
            font-size: 32px; 
            font-weight: 800;
            margin: 0 0 8px 0;
        }
        .subtitle { 
            color: var(--m-text-light); 
            font-size: 17px; 
            margin: 0;
        }
        .section { 
            margin-bottom: 45px; 
        }
        .section-title { 
            font-family: 'Outfit', sans-serif;
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--m-section-bg); 
            color: var(--m-primary-dark); 
            padding: 12px 20px; 
            border-radius: 12px; 
            font-size: 20px; 
            font-weight: 700; 
            margin-bottom: 20px; 
            border-left: 5px solid var(--m-primary);
        }
        .step { 
            margin-bottom: 20px; 
            display: flex;
            gap: 15px;
        }
        .step-number { 
            background: var(--m-primary); 
            color: #ffffff; 
            min-width: 28px; 
            height: 28px; 
            border-radius: 8px; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            font-weight: 800; 
            font-size: 14px;
            margin-top: 2px;
        }
        .step-text { flex: 1; }
        .step-title { font-weight: 700; color: var(--m-text); display: block; margin-bottom: 4px; font-size: 16px; }
        .tip {
            background: var(--m-tip-bg);
            border: 1px solid var(--m-tip-border);
            padding: 15px;
            border-radius: 12px;
            margin-top: 15px;
            font-size: 15px;
            color: var(--m-tip-text);
            display: flex;
            gap: 10px;
        }
        .footer { 
            text-align: center; 
            font-size: 12px; 
            color: var(--m-text-light); 
            border-top: 1px solid var(--m-border); 
            padding-top: 25px;
            margin-top: 50px;
        }
        strong { color: var(--m-primary-dark); font-weight: 700; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="title">Teacher Guide</h1>
            <p class="subtitle">Daily management of your classes and grades</p>
        </div>

        <div class="section">
            <div class="section-title">1. Grade Entry & Discipline</div>
            <div class="step">
                <span class="step-number">1</span>
                <div class="step-text">
                    <span class="step-title">Access your classes</span>
                    Go to <strong>"Grade Entry"</strong> to see your assigned courses. The system now supports rapid class filtering for easier navigation.
                </div>
            </div>
            <div class="step">
                <span class="step-number">2</span>
                <div class="step-text">
                    <span class="step-title">Real-time Discipline</span>
                    You can now report absences and disciplinary sanctions directly from your interface. This information will be reflected on official report cards.
                </div>
            </div>
            <div class="tip">
                💡 <strong>Tip:</strong> Averages and ranks are calculated automatically as soon as you save grades for an evaluation.
            </div>
        </div>

        <div class="section">
            <div class="section-title">2. Reports & Consultations</div>
            <div class="step">
                <span class="step-number">1</span>
                <div class="step-text">
                    <span class="step-title">Class Procès-Verbaux (PV)</span>
                    As a teacher in charge, you can generate modernized <strong>Procès-Verbaux (PV)</strong> in A4 Landscape format, featuring detailed statistical analysis by subject group.
                </div>
            </div>
            <div class="step">
                <span class="step-number">2</span>
                <div class="step-text">
                    <span class="step-title">Multilingual Visualization</span>
                    Administrative documents automatically adapt to the language of your class's section (French or English).
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">3. Your Profile & Security</div>
            <div class="step">
                <span class="step-number">1</span>
                <div class="step-text">
                    <span class="step-title">Account Management</span>
                    Update your personal information, profile picture, and password in the <strong>"My Profile"</strong> tab.
                </div>
            </div>
        </div>

        <div class="footer">
            NotesMaster v2.0 - &copy; <?= date('Y') ?> - Documentation updated on <?= date('d/m/Y') ?>
        </div>
    </div>
</body>
</html>
