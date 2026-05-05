<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        :root {
            --m-primary: #3b82f6;
            --m-primary-dark: #1e3a8a;
            --m-bg: #ffffff;
            --m-text: #1e293b;
            --m-text-light: #64748b;
            --m-border: #e2e8f0;
            --m-section-bg: #f8fafc;
            --m-tip-bg: #eff6ff;
            --m-tip-border: #dbeafe;
            --m-tip-text: #1e40af;
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
            <h1 class="title">Administrator Guide</h1>
            <p class="subtitle">Streamlined and efficient school management</p>
        </div>

        <div class="section">
            <div class="section-title">1. Students & Classes Management</div>
            <div class="step">
                <span class="step-number">1</span>
                <div class="step-text">
                    <span class="step-title">Enrollment & Summary Sheets</span>
                    Manage new students via the <strong>"Students"</strong> menu. You can generate class summary sheets with a single click.
                </div>
            </div>
            <div class="step">
                <span class="step-number">2</span>
                <div class="step-text">
                    <span class="step-title">Subject Configuration</span>
                    When creating or editing a subject, use the <strong>instant search</strong> feature to quickly assign relevant classes without scrolling through the entire list.
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">2. Results & Procès-Verbaux (PV)</div>
            <div class="step">
                <span class="step-number">1</span>
                <div class="step-text">
                    <span class="step-title">"Gold Standard" Procès-Verbaux</span>
                    PVs are now generated in <strong>A4 Landscape</strong> mode. They include automatic pagination (max 20 students per page) and wrapping subject headers for perfect readability.
                </div>
            </div>
            <div class="step">
                <span class="step-number">2</span>
                <div class="step-text">
                    <span class="step-title">Advanced Analytics</span>
                    Each PV automatically integrates a synthesis by subject group, top/flop subject analysis, and the global success rate. Signatures are fixed at the end of the document.
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">3. Discipline & Internationalization</div>
            <div class="step">
                <span class="step-number">1</span>
                <div class="step-text">
                    <span class="step-title">Disciplinary Tracking</span>
                    Enter absences and sanctions in the dedicated module. This data is automatically synced to report cards and PVs.
                </div>
            </div>
            <div class="step">
                <span class="step-number">2</span>
                <div class="step-text">
                    <span class="step-title">Bilingual System</span>
                    The application fully switches between French and English. PVs and report cards automatically adapt to the school's section (Francophone/Anglophone).
                </div>
            </div>
        </div>

        <div class="tip">
            <strong>Tip:</strong> for optimal PV printing, ensure that the "Headers and Footers" option is enabled in your browser to benefit from the integrated automatic pagination.
        </div>

        <div class="footer">
            NotesMaster v2.0 - &copy; <?= date('Y') ?> - Documentation updated on <?= date('d/m/Y') ?>
        </div>
    </div>
</body>
</html>
