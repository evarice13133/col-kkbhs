<?php
/** @var array $student */
/** @var array $institution */
/** @var array $classInfo */
/** @var string $periodLabel */
/** @var array $activeYear */

$i = $institution;
$schoolDisplayName = function_exists('mb_strtoupper') ? mb_strtoupper((string) ($i['school_name'] ?? $i['school_code'] ?? ''), 'UTF-8') : strtoupper((string) ($i['school_name'] ?? $i['school_code'] ?? ''));
?>
<div class="ultra-premium-cert">
    <!-- Intricate SVG Corner Ornaments -->
    <div class="svg-ornament top-left-svg">
        <svg viewBox="0 0 100 100"><path d="M0 0 L100 0 L100 10 L10 10 L10 100 L0 100 Z" fill="url(#gold-grad)"/><circle cx="15" cy="15" r="5" fill="url(#gold-grad)"/></svg>
    </div>
    <div class="svg-ornament top-right-svg">
        <svg viewBox="0 0 100 100" style="transform: rotate(90deg)"><path d="M0 0 L100 0 L100 10 L10 10 L10 100 L0 100 Z" fill="url(#gold-grad)"/><circle cx="15" cy="15" r="5" fill="url(#gold-grad)"/></svg>
    </div>
    <div class="svg-ornament bottom-left-svg">
        <svg viewBox="0 0 100 100" style="transform: rotate(270deg)"><path d="M0 0 L100 0 L100 10 L10 10 L10 100 L0 100 Z" fill="url(#gold-grad)"/><circle cx="15" cy="15" r="5" fill="url(#gold-grad)"/></svg>
    </div>
    <div class="svg-ornament bottom-right-svg">
        <svg viewBox="0 0 100 100" style="transform: rotate(180deg)"><path d="M0 0 L100 0 L100 10 L10 10 L10 100 L0 100 Z" fill="url(#gold-grad)"/><circle cx="15" cy="15" r="5" fill="url(#gold-grad)"/></svg>
    </div>

    <svg style="width:0;height:0;position:absolute;" aria-hidden="true" focusable="false">
        <linearGradient id="gold-grad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#bf953f;stop-opacity:1" />
            <stop offset="25%" style="stop-color:#fcf6ba;stop-opacity:1" />
            <stop offset="50%" style="stop-color:#b38728;stop-opacity:1" />
            <stop offset="75%" style="stop-color:#fcf6ba;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#aa771c;stop-opacity:1" />
        </linearGradient>
    </svg>

    <div class="outer-frame">
        <div class="inner-frame">
            <div class="paper-texture"></div>
            
            <div class="cert-content-wrapper">
                
                <!-- Watermark -->
                <?php if (!empty($i['school_logo_base64'])): ?>
                    <div class="cert-watermark">
                        <img src="<?= $i['school_logo_base64'] ?>" alt="Watermark">
                        <div class="watermark-code"><?= htmlspecialchars($i['school_code'] ?? '') ?></div>
                    </div>
                <?php endif; ?>

                <!-- Institutional Header (UNTOUCHED as requested) -->
                <header class="cert-header-elite">
                    <div class="header-side-box">
                        <div class="meta-item"><?= htmlspecialchars((string) ($i['school_republic'] ?? 'REPUBLIQUE DU CAMEROUN')) ?></div>
                        <div class="meta-motto"><?= htmlspecialchars((string) ($i['school_motto'] ?? 'PAIX - TRAVAIL - PATRIE')) ?></div>
                        <div class="meta-line"></div>
                        <div class="meta-dept"><?= htmlspecialchars((string) ($i['school_ministry'] ?? 'MINESEC')) ?></div>
                        <div class="meta-school-header-wrap">
                            <?php 
                                $headerNameLen = mb_strlen($schoolDisplayName);
                                $headerFontSize = 11;
                                if ($headerNameLen > 25) $headerFontSize = 9;
                                if ($headerNameLen > 35) $headerFontSize = 7.5;
                            ?>
                            <div class="meta-school-header" style="font-size: <?= $headerFontSize ?>px;"><?= htmlspecialchars($schoolDisplayName) ?></div>
                            <div class="meta-school-motto">« <?= htmlspecialchars((string) ($i['school_slogan'] ?? 'Vers l\'Excellence')) ?> »</div>
                        </div>
                    </div>
                    <div class="header-logo-box">
                        <?php if (!empty($i['school_logo_base64'])): ?>
                            <img src="<?= $i['school_logo_base64'] ?>" class="elite-logo" alt="Logo">
                        <?php endif; ?>
                    </div>
                    <div class="header-side-box align-right">
                        <div class="meta-item">REPUBLIC OF CAMEROON</div>
                        <div class="meta-motto">PEACE - WORK - FATHERLAND</div>
                        <div class="meta-line"></div>
                        <div class="meta-dept">MINISTRY OF SECONDARY EDUCATION</div>
                        <div class="meta-school-header-wrap">
                            <?php 
                                $schoolNameEn = !empty($i['school_name_en']) ? $i['school_name_en'] : $i['school_name'];
                                $schoolNameEn = function_exists('mb_strtoupper') ? mb_strtoupper((string) $schoolNameEn, 'UTF-8') : strtoupper((string) $schoolNameEn);
                                $schoolSloganEn = !empty($i['school_slogan_en']) ? $i['school_slogan_en'] : ($i['school_slogan'] ?? 'Towards Excellence');
                            ?>
                            <div class="meta-school-header" style="font-size: <?= $headerFontSize ?>px;"><?= htmlspecialchars($schoolNameEn) ?></div>
                            <div class="meta-school-motto">« <?= htmlspecialchars($schoolSloganEn) ?> »</div>
                        </div>
                    </div>
                </header>

                <!-- NEW LAYOUT BELOW HEADER -->
                
                <!-- 1. The Award Title -->
                <section class="award-title-elite">
                    <div class="title-decoration left"></div>
                    <div class="title-main-box">
                        <h1 class="main-honor-title"><?= __('honor_roll_title') ?></h1>
                    </div>
                    <div class="title-decoration right"></div>
                </section>

                <div class="award-period-box">
                    <span class="period-text"><?= $periodLabel ?> — <?= htmlspecialchars($activeYear['nom']) ?></span>
                </div>

                <!-- 2. The Recipient (Focal Point) -->
                <div class="recipient-flow">
                    <?php
                        $fullName = trim((string) (($student['nom'] ?? '') . ' ' . ($student['prenom'] ?? '')));
                        $safeSchool = htmlspecialchars($schoolDisplayName);
                        $safeStudent = htmlspecialchars($fullName);
                        $safePeriod = htmlspecialchars($periodLabel ?? '');
                    ?>
                    <p class="award-intro"><?= __('award_intro', ['school' => $safeSchool, 'student' => $safeStudent, 'period' => $safePeriod]) ?></p>

                    <div class="student-name-premium">
                        <?php
                            $nameLength = mb_strlen($fullName);
                            // Dynamic font size calculation (Base 48px, scales down for long names)
                            $baseSize = 48;
                            if ($nameLength > 25) $baseSize = 40;
                            if ($nameLength > 35) $baseSize = 32;
                            if ($nameLength > 45) $baseSize = 24;
                            if ($nameLength > 60) $baseSize = 18;
                        ?>
                        <span class="name-inner" style="font-size: <?= $baseSize ?>px;"><?= htmlspecialchars($fullName) ?></span>
                    </div>

                    <?php 
                        $avg = $student['average'];

                        if ($avg >= 16) {
                            $rawPhrases = __('award_phrases_high');
                        } elseif ($avg >= 14) {
                            $rawPhrases = __('award_phrases_mid');
                        } else {
                            $rawPhrases = __('award_phrases_low');
                        }

                        $phrases = array_map('trim', explode('||', (string) $rawPhrases));
                        if (empty($phrases)) {
                            $phrases = [''];
                        }

                        $phrase = $phrases[$student['id'] % count($phrases)];
                    ?>
                    <div class="motivation-wrapper-elite">
                        <p class="motivational-phrase">« <?= htmlspecialchars($phrase) ?> »</p>
                        <div class="motivation-underline"></div>
                    </div>

                    <p class="student-class"><?= __('student_in_class') ?> <span class="class-highlight"><?= htmlspecialchars($classInfo['nom']) ?></span></p>
                </div>

                <!-- 3. Performance Metrics (Simplified) -->
                <div class="metrics-elite">
                    <div class="metric-card-elite">
                        <span class="m-label"><?= __('rank_label') ?></span>
                        <span class="m-value"><?= $student['rank'] ?><small><?= $student['rank'] == 1 ? __('rank_suffix_er') : __('rank_suffix_eme') ?></small></span>
                    </div>

                    <div class="metric-card-elite">
                        <span class="m-label"><?= __('average_label') ?></span>
                        <span class="m-value"><?= number_format($student['average'], 2, ',', ' ') ?> / 20</span>
                    </div>
                    
                    <div class="metric-card-elite">
                        <span class="m-label"><?= __('mention_label') ?></span>
                        <span class="m-value-mention"><?= htmlspecialchars($this->getMention($student['average'])) ?></span>
                    </div>
                </div>

                <!-- 4. Footer: Location, Date & Signatures -->
                <div class="cert-metadata">
                    <?= __('cert_made_at', ['city' => htmlspecialchars($i['school_city'] ?? ''), 'date' => date('d/m/Y')]) ?>
                </div>

                <footer class="signatures-elite">
                    <div class="sig-wrapper">
                        <div class="sig-field"></div>
                        <span class="sig-role"><?= __('sig_class_holder') ?></span>
                        <div class="sig-teacher-name"><?= htmlspecialchars(trim(($classInfo['main_teacher_nom'] ?? '') . ' ' . ($classInfo['main_teacher_prenom'] ?? ''))) ?></div>
                    </div>
                    <div class="sig-wrapper">
                        <div class="sig-field"></div>
                        <span class="sig-role"><?= __('sig_principal') ?></span>
                        <div class="sig-teacher-name"><?= htmlspecialchars($institution['school_principal'] ?? '') ?></div>
                    </div>

                    <!-- Prestige Seal (Repositioned) -->
                    <div class="prestige-seal-absolute">
                        <div class="seal-outer">
                            <div class="seal-inner">
                                <div class="seal-content">
                                    <span class="seal-year"><?= date('Y') ?></span>
                                    <span class="seal-label"><?= __('seal_label') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>

                <div class="security-footer">
                    <span><?= __('cert_footer', ['id' => strtoupper(substr(md5($student['id'] . time()), 0, 8))]) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700;900&family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Montserrat:wght@400;700;900&display=swap');

    @page { 
        size: landscape; 
        margin: 0; 
    }
    body { 
        margin: 0; 
        padding: 0; 
        background: #f4f7f6; 
        display: flex; 
        flex-direction: column; 
        align-items: center; 
    }
    .ultra-premium-cert {
        width: 297mm;
        height: 210mm;
        padding: 10mm;
        background: #fff;
        page-break-after: always;
        position: relative;
        font-family: 'Playfair Display', serif;
        overflow: hidden;
        box-sizing: border-box;
    }

    @media print {
        body { background: none; }
        .ultra-premium-cert { margin: 0; border: none; }
    }

    /* Ornaments (Absolute to cert) */
    .svg-ornament { position: absolute; width: 60px; height: 60px; z-index: 50; opacity: 0.9; }
    .top-left-svg { top: 10mm; left: 10mm; }
    .top-right-svg { top: 10mm; right: 10mm; }
    .bottom-left-svg { bottom: 10mm; left: 10mm; }
    .bottom-right-svg { bottom: 10mm; right: 10mm; }

    .outer-frame {
        border: 1px solid #aa771c;
        height: 100%;
        width: 100%;
        padding: 3mm;
        position: relative;
        box-sizing: border-box;
    }
    .outer-frame::after {
        content: "";
        position: absolute;
        top: 1.5mm; left: 1.5mm; right: 1.5mm; bottom: 1.5mm;
        border: 8px double #1a3a5a;
        pointer-events: none;
    }

    .inner-frame {
        height: 100%;
        width: 100%;
        border: 1px solid #bf953f;
        padding: 4mm 10mm;
        background: radial-gradient(circle at center, #fff 0%, #fff9e6 100%);
        position: relative;
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
    }

    .paper-texture {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        opacity: 0.03;
        background-image: url('https://www.transparenttextures.com/patterns/natural-paper.png');
        pointer-events: none;
    }

    .cert-content-wrapper { 
        height: 100%; 
        width: 100%;
        display: flex; 
        flex-direction: column; 
        align-items: center; 
        justify-content: space-between; /* Spread content to occupy space */
        position: relative; 
        z-index: 2; 
    }

    .cert-watermark {
        position: absolute;
        top: 50%; left: 50%; transform: translate(-50%, -50%);
        width: 380px; opacity: 0.09; filter: sepia(100%) saturate(200%) brightness(80%);
        z-index: -1;
        text-align: center;
    }
    .cert-watermark img { width: 100%; }
    .watermark-code { font-family: 'Montserrat', sans-serif; font-size: 28px; font-weight: 900; color: #1a3a5a; margin-top: 10px; letter-spacing: 10px; text-transform: uppercase; }

    /* Header */
    .cert-header-elite { width: 100%; display: flex; justify-content: space-between; margin-bottom: 3mm; align-items: center; }
    .header-side-box { width: 33%; font-family: 'Montserrat', sans-serif; font-size: 10.5px; font-weight: 800; color: #1a3a5a; text-transform: uppercase; line-height: 1.2; }
    .header-side-box.align-right { text-align: right; }
    .meta-motto { font-size: 9px; font-weight: 600; font-style: italic; color: #aa771c; }
    .meta-line { width: 40px; height: 1px; background: #aa771c; margin: 4px 0; }
    .align-right .meta-line { margin-left: auto; }
    .meta-dept { margin-bottom: 2px; }
    .meta-school-header { font-weight: 900; color: #1a3a5a; letter-spacing: 0.5px; margin-top: 2px; border-top: 1px solid rgba(26, 58, 90, 0.1); padding-top: 2px; white-space: nowrap; overflow: hidden; }
    .meta-school-motto { font-size: 7.5px; font-weight: 600; font-style: italic; color: #aa771c; margin-top: 1px; }
    .header-logo-box { width: 85px; }
    .elite-logo { width: 100%; height: 85px; object-fit: contain; }

    .school-identity-elite { margin-bottom: 2mm; }
    .school-name-display {
        font-family: 'Cinzel Decorative', serif;
        font-size: 22px;
        font-weight: 900;
        color: #1a3a5a;
        margin: 0;
        letter-spacing: 2px;
        text-shadow: 1px 1px 0px #fff, 2px 2px 0px rgba(0,0,0,0.05);
    }

    /* Title */
    .award-title-elite { display: flex; align-items: center; gap: 15px; width: 100%; justify-content: center; margin: 2mm 0 1mm 0; }
    .title-decoration { width: 80px; height: 1px; background: linear-gradient(to right, transparent, #aa771c, transparent); }
    .main-honor-title {
        font-family: 'Cinzel Decorative', serif;
        font-size: 44px; /* Slightly reduced to avoid overflow */
        font-weight: 900;
        color: #1a3a5a; /* Deep Royal Blue for maximum visibility */
        margin: 0;
        line-height: 1;
        letter-spacing: 4px;
        text-transform: uppercase;
    }
    .award-period-box { margin-bottom: 2mm; text-align: center; width: 100%; }
    .period-text {
        font-family: 'Montserrat', sans-serif;
        font-size: 16px; /* Increased size */
        font-weight: 900;
        color: #aa771c; /* Solid Gold color */
        padding: 0; /* No background padding */
        background: none; /* Removed background */
        display: inline-block;
        letter-spacing: 3px;
        text-transform: uppercase;
        border-top: 1px solid #aa771c;
        border-bottom: 1px solid #aa771c;
        padding: 2px 20px;
    }

    /* Recipient */
    .recipient-flow { 
        flex-grow: 1; 
        display: flex; 
        flex-direction: column; 
        justify-content: center; 
        align-items: center; 
        width: 100%; 
        margin-bottom: 3mm; 
        text-align: center;
    }
    .award-intro { font-size: 16px; font-style: italic; color: #555; margin-bottom: 5px; width: 80%; }
    .student-name-premium { margin: 5px 0; position: relative; width: 100%; text-align: center; }
    .name-inner {
        font-weight: 900;
        color: #1a3a5a;
        border-bottom: 2px solid #bf953f;
        padding: 0 40px;
        display: inline-block;
        font-style: italic;
        white-space: nowrap; /* Force single line */
    }
    .motivational-phrase {
        font-family: 'Cormorant Garamond', serif;
        font-size: 14px;
        font-style: italic;
        color: #64748b;
        margin: 0;
        max-width: 80%;
        line-height: 1.2;
    }
    .motivation-wrapper-elite {
        margin: 1mm 0 3mm 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
    }
    .motivation-underline {
        width: 50px;
        height: 1px;
        background: linear-gradient(to right, transparent, #bf953f, transparent);
        margin-top: 1mm;
    }
    .student-class { font-family: 'Montserrat', sans-serif; font-size: 16px; font-weight: 600; margin-top: 5px; }
    .class-highlight { color: #3b82f6; font-weight: 900; text-decoration: underline; text-transform: uppercase; }

    /* Metrics */
    .metrics-elite { display: flex; justify-content: center; align-items: center; gap: 20px; margin: 3mm 0; width: 100%; }
    .metric-card-elite {
        background: rgba(255,255,255,0.8);
        border: 1px solid #bf953f;
        padding: 8px 15px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        min-width: 140px;
    }
    .m-label { display: block; font-family: 'Montserrat', sans-serif; font-size: 9px; font-weight: 900; color: #aa771c; text-transform: uppercase; margin-bottom: 2px; letter-spacing: 1px; }
    .m-value { font-size: 22px; font-weight: 800; color: #1a3a5a; }
    .m-value-mention { font-size: 18px; font-weight: 800; color: #1a3a5a; text-transform: uppercase; letter-spacing: 1px; }

    .cert-metadata { width: 100%; text-align: right; font-size: 13px; font-style: italic; color: #555; margin-top: 3mm; padding-right: 15mm; }

    /* Prestige Seal (Absolute Positioned between signatures) */
    .prestige-seal-absolute { 
        width: 80px; height: 80px; 
        position: absolute;
        left: 50%;
        top: -10px;
        transform: translateX(-50%) rotate(-15deg);
        z-index: 5;
    }
    .seal-outer {
        width: 100%; height: 100%;
        background: #bf953f;
        border-radius: 50%;
        padding: 4px;
        box-shadow: 0 4px 10px rgba(184, 134, 11, 0.3);
    }
    .seal-inner {
        width: 100%; height: 100%;
        background: linear-gradient(135deg, #bf953f, #aa771c);
        border: 2px dashed rgba(255,255,255,0.6);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
    }
    .seal-content { color: #fff; font-family: 'Cinzel Decorative', serif; display: flex; flex-direction: column; line-height: 1; text-align: center; }
    .seal-year { font-size: 14px; font-weight: 900; }
    .seal-label { font-size: 7px; font-weight: 700; letter-spacing: 1px; }

    /* Signatures */
    .signatures-elite { 
        width: 100%; 
        display: flex; 
        justify-content: space-between; 
        margin-top: 3mm; 
        position: relative; /* Context for absolute seal */
    }
    .sig-wrapper { width: 35%; text-align: center; }
    .sig-field { height: 45px; border-bottom: 1.5px solid #1a3a5a; margin-bottom: 8px; }
    .sig-role { font-family: 'Montserrat', sans-serif; font-size: 11px; font-weight: 800; color: #1a3a5a; text-transform: uppercase; display: block; margin-bottom: 2px; }
    .sig-teacher-name { font-family: 'Playfair Display', serif; font-size: 12px; font-weight: 700; color: #1a3a5a; text-transform: uppercase; }

    .security-footer { margin-top: 5mm; font-family: 'Montserrat', sans-serif; font-size: 7.5px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 3px; }

</style>
