<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        :root {
            --m-primary: #4f46e5;
            --m-primary-dark: #312e81;
            --m-bg: #ffffff;
            --m-text: #1e293b;
            --m-text-light: #64748b;
            --m-border: #e2e8f0;
            --m-section-bg: #f8fafc;
            --m-tip-bg: #f0fdf4;
            --m-tip-border: #dcfce7;
            --m-tip-text: #166534;
            --m-warn-bg: #fffbeb;
            --m-warn-border: #fef3c7;
            --m-warn-text: #92400e;
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
        .warning-box {
            background: var(--m-warn-bg);
            border: 1px solid var(--m-warn-border);
            padding: 15px;
            border-radius: 12px;
            margin-top: 15px;
            font-size: 15px;
            color: var(--m-warn-text);
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
            <h1 class="title">Super Administrator Guide</h1>
            <p class="subtitle">Strategic control of NotesMaster v2.0</p>
        </div>

        <div class="section">
            <div class="section-title">1. Structure & Internationalization</div>
            <div class="step">
                <span class="step-number">1</span>
                <div class="step-text">
                    <span class="step-title">Bilingual Configuration</span>
                    NotesMaster v2.0 is now 100% bilingual (FR/EN). You can set the school's default language in the global settings.
                </div>
            </div>
            <div class="step">
                <span class="step-number">2</span>
                <div class="step-text">
                    <span class="step-title">Section Management</span>
                    Organize your school by sections (Francophone/Anglophone). Report templates (PV, Report Cards) automatically adapt to the chosen section.
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">2. "Gold Standard" Results Steering</div>
            <div class="step">
                <span class="step-number">1</span>
                <div class="step-text">
                    <span class="step-title">Institutional Reporting</span>
                    Supervise the generation of modernized <strong>Procès-Verbaux</strong>. These documents offer an A4 landscape view with rigorous pagination and advanced analytics (success rates, top/flop subjects).
                </div>
            </div>
            <div class="step">
                <span class="step-number">2</span>
                <div class="step-text">
                    <span class="step-title">Assignment Optimization</span>
                    Thanks to the new <strong>instant search</strong> engine, assigning subjects to classes is now 5 times faster, facilitating start-of-year configuration.
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">3. Oversight & Security</div>
            <div class="step">
                <span class="step-number">1</span>
                <div class="step-text">
                    <span class="step-title">Data Integrity</span>
                    The discipline module is now centralized. As a Superadmin, you can audit all sanctions and absences that impact final report cards.
                </div>
            </div>
            <div class="step">
                <span class="step-number">2</span>
                <div class="step-text">
                    <span class="step-title">Backup & Maintenance</span>
                    The backup system has been strengthened. Be sure to export the database regularly to ensure service continuity.
                </div>
            </div>
            <div class="warning-box">
                <strong>⚠️ Warning:</strong> Your privileges allow you to modify coefficients and subject groups at any time. Any changes will retroactively impact average calculations.
            </div>
        </div>

        <div class="footer">
            NotesMaster v2.0 - &copy; <?= date('Y') ?> - Documentation updated on <?= date('d/m/Y') ?>
        </div>
    </div>
</body>
</html>
