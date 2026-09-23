<?php
/**
 * BULLETIN HEADER HTML PARTIAL
 * Contient l'entête HTML commun à tous les types de bulletins (annuel, trimestre, sequence)
 * 
 * Variables requises :
 * - $institution : Informations sur l'école
 * - $activeYear : Année académique en cours
 * - $student : Données personnelles de l'élève
 * - $contact : Informations de contact
 * - $schoolDisplayName : Nom de l'école en majuscules
 * - $birthDate : Date de naissance
 * - $birthPlace : Lieu de naissance
 * - $isRedoublant : Si l'élève redouble
 * - $effectif : Effectif de la classe
 * - $displayMatricule : Matricule à afficher
 * - $bulletinType : Type de bulletin (annual_short, trimester_short, sequence_short)
 * - $showTeacherNamesOnBulletins : Si on affiche les noms des enseignants
 */

$i = $institution;
$isEnglish = (($lang ?? \App\Core\Session::get('app_lang', 'fr')) === 'en');
$stateRepublicFr = $i['school_republic'] ?? __('republic_of_cameroon');
$stateRepublicEn = $i['school_republic_en'] ?? __('republic_of_cameroon');
$stateMinistryFr = $i['school_ministry'] ?? __('ministry_secondary_education');
$stateMinistryEn = $i['school_ministry_en'] ?? __('ministry_secondary_education');
$stateMottoFr = $i['school_motto'] ?? __('motto');
$stateMottoEn = $i['school_motto_en'] ?? __('motto');
$stateSloganFr = $i['school_slogan'] ?? __('slogan');
$stateSloganEn = $i['school_slogan_en'] ?? __('slogan');
$stateDelegationFr = $i['school_delegation'] ?? '';
$stateDelegationEn = $i['school_delegation_en'] ?? '';
$stateDelegationFrHtml = nl2br(htmlspecialchars(str_replace(';', "\n", trim((string) $stateDelegationFr)), ENT_QUOTES, 'UTF-8'));
$stateDelegationEnHtml = nl2br(htmlspecialchars(str_replace(';', "\n", trim((string) $stateDelegationEn)), ENT_QUOTES, 'UTF-8'));
$schoolPhone = trim((string) ($i['school_phone'] ?? ''));
$schoolAddress = trim((string) ($i['school_address'] ?? ''));
if ($schoolAddress === '') {
    $schoolAddress = trim((string) ($i['school_city'] ?? ''));
}
$frTranslations = require __DIR__ . '/../../../i18n/fr.php';
$enTranslations = require __DIR__ . '/../../../i18n/en.php';
$phoneLabelFr = $frTranslations['tel'] ?? 'TEL';
$addressLabelFr = $frTranslations['address'] ?? 'Adresse';
$phoneLabelEn = $enTranslations['tel'] ?? 'Tel';
$addressLabelEn = $enTranslations['address'] ?? 'Address';
?>

<style>
    .header-wrapper {
        width: 100%;
        display: grid;
        grid-template-columns: minmax(0, 1fr) 200px minmax(0, 1fr);
        align-items: start;
        column-gap: 4px;
        margin-bottom: 0;
        page-break-inside: avoid;
    }
    .header-left,
    .header-center,
    .header-right { min-width: 0; }
    .header-left { font-size: 12px; text-align: center; }
    .header-right { font-size: 12px; text-align: center; }
    .header-center { display: flex; align-items: center; justify-content: center; text-align: center; }
    .header-center .logo-box { margin: 0 auto 5px; }
    .header-branding { grid-column: 1 / -1; text-align: center; margin-top: 0; }
    .header-side-content {
        display: inline-flex;
        flex-direction: column;
        align-items: stretch;
        width: 100%;
        max-width: 100%;
        padding: 0 4px;
        margin: 0 auto;
        overflow-wrap: anywhere;
        word-break: normal;
        font-family: 'Arial Black', Arial, sans-serif;
        letter-spacing: 0;
        gap: 1px;
    }
    .header-line-group { display: block; width: 100%; max-width: 100%; margin: 0; }
    .header-line-group + .header-line-group { margin-top: 0; }
    .header-line-group .header-line { display: block; width: 100%; margin: 0; }
    .header-contact-row { display: flex; align-items: baseline; justify-content: center; gap: 4px; white-space: nowrap; flex-wrap: wrap; margin-top: 1px; }
    .header-line, .header-contact, .school-name-display, .academic-year-display { margin: 0; line-height: 1.05; }
    .header-line { font-family: 'Arial Black', Arial, sans-serif; font-size: 6.2px; font-weight: 900; text-transform: uppercase; }
    .header-contact { font-family: 'Arial Black', Arial, sans-serif; font-size: 6.2px; margin-top: 0; text-transform: uppercase; }
    .header-side-content .republic-line { font-size: 6.8px; color: #0057b8; }
    .header-side-content .motto-line { font-size: 6.4px; font-style: italic; }
    .header-side-content .ministry-line { font-size: 6.1px; color: #000; }
    .header-side-content .delegation-line { font-size: 6.1px; }
    .header-side-content .slogan-line { font-size: 6.1px; }
    .header-contact-label { color: #0057b8; font-size: 6.1px; }
    .header-contact-value { color: #000; font-weight: 700; font-size: 6.1px; }
    .school-name-display { font-family: 'Arial Black', Arial, sans-serif; font-weight: 900; font-size: 17px; color: #0057b8; text-transform: uppercase; text-align: center; overflow-wrap: anywhere; }
    .academic-year-display { margin-top: 0; margin-bottom: 2px; font-weight: 700; font-size: 12px; text-transform: uppercase; text-align: center; }

    .student-header-layout {
        width: 100%;
        display: flex;
        align-items: stretch;
        gap: 6px;
        margin: 0 0 4px;
    }
    .student-photo-block {
        flex: 0 0 72px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .student-photo-block .student-photo-container {
        width: 72px;
        height: 78px;
    }
    .student-photo-container {
        background: #fff;
        border: 1px solid #14347a;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .student-photo-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
    }
    .student-photo-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-size: 26px;
        color: #14347a;
        background: #f4f7fb;
    }
    .student-identity-grid {
        flex: 1 1 auto;
        min-width: 0;
        display: grid;
        grid-template-columns: 1.7fr 1.1fr .9fr 1.3fr;
        justify-content: stretch;
        gap: 0 2px;
        align-items: stretch;
        border: none;
        border-radius: 0;
        padding: 0;
        overflow: hidden;
        background: transparent;
        font-weight: 700;
    }
    .student-identity-grid * {
        font-weight: 700;
    }
    .student-identity-item {
        display: flex;
        align-items: baseline;
        justify-content: flex-start;
        gap: 3px;
        min-height: 19px;
        padding: 2px 3px;
        background: transparent;
        text-align: left;
        border: none;
    }
    .student-identity-item.full {
        grid-column: 1 / -1;
        align-items: center;
        min-height: 28px;
        padding: 4px 3px;
        background: transparent;
        border-bottom: none;
    }
    .student-identity-item:nth-child(2),
    .student-identity-item:nth-child(3),
    .student-identity-item:nth-child(4),
    .student-identity-item:nth-child(5) {
        background: transparent;
    }
    .student-identity-item:nth-child(6),
    .student-identity-item:nth-child(7),
    .student-identity-item:nth-child(8),
    .student-identity-item:nth-child(9) {
        background: transparent;
    }
    .student-identity-footer {
        grid-column: 1 / -1;
        display: flex;
        align-items: baseline;
        gap: 5px;
        padding: 0;
        background: transparent;
        border-top: none;
    }
    .student-identity-footer .student-identity-item {
        margin-right: 0;
    }
    .student-identity-footer .student-contact-item {
        flex: 1 1 0;
        min-width: 0;
    }
    .student-identity-footer .student-principal-item {
        flex: 1 1 0;
        min-width: 0;
    }
    .student-identity-label {
        font-weight: 800;
        font-size: 9.7px;
        line-height: 1.05;
        color: #1d3557;
        white-space: nowrap;
        letter-spacing: 0;
    }
    .student-identity-value {
        font-weight: 900;
        font-size: 10.2px;
        line-height: 1.05;
        color: #074b8a;
        white-space: nowrap;
        text-transform: uppercase;
        border-bottom: none;
        overflow: visible;
        text-overflow: clip;
        flex-shrink: 0;
        text-decoration: underline;
        text-decoration-thickness: 1px;
        text-underline-offset: 1px;
    }
    .student-name-value {
        font-weight: 900;
        font-size: 13px;
        line-height: 1.05;
        text-transform: uppercase;
        color: #0057b8;
        letter-spacing: .15px;
        border-bottom: none;
        text-decoration: underline;
        text-decoration-thickness: 1px;
        text-underline-offset: 1px;
    }
    .title-box {
        display: block;
        width: 100%;
        text-align: center;
        font-family: 'Arial Black', Arial, sans-serif;
        font-size: 15px;
        margin: 0 auto 3px;
        text-transform: uppercase;
        padding: 2px 3px;
        border: 2px solid #000;
    }
</style>

        <!-- A. EN-TÊTE EN TROIS COLONNES -->
        <div class="header-wrapper">
            <div class="header-left">
                <div class="header-side-content">
                    <div class="header-line-group">
                        <p class="header-line republic-line"><?= htmlspecialchars((string) $stateRepublicFr) ?></p>
                    </div>
                    <div class="header-line-group">
                        <p class="header-line motto-line"><?= htmlspecialchars((string) $stateMottoFr) ?></p>
                    </div>
                    <div class="header-line-group">
                        <p class="header-line ministry-line"><?= htmlspecialchars((string) $stateMinistryFr) ?></p>
                    </div>
                    <?php if ($stateDelegationFrHtml !== ''): ?>
                        <div class="header-line-group">
                            <p class="header-line delegation-line"><?= $stateDelegationFrHtml ?></p>
                        </div>
                    <?php endif; ?>
                    <p class="header-line slogan-line"><?= htmlspecialchars((string) $stateSloganFr) ?></p>
                    <div class="header-contact-row">
                        <p class="header-contact"><span class="header-contact-label"><?= htmlspecialchars($phoneLabelFr) ?>:</span> <span class="header-contact-value"><?= htmlspecialchars($schoolPhone) ?></span></p>
                        <p class="header-contact"><span class="header-contact-label"><?= htmlspecialchars($addressLabelFr) ?>:</span> <span class="header-contact-value"><?= htmlspecialchars($schoolAddress) ?></span></p>
                    </div>
                </div>
            </div>

            <div class="header-center">
                <div class="logo-box">
                    <?php if (!empty($i['school_logo_base64'])): ?>
                        <img src="<?= $i['school_logo_base64'] ?>" alt="Logo">
                    <?php elseif (!empty($i['school_logo'])):
                        $logoPath = \App\Core\Helpers::normalizeLogoPath((string) $i['school_logo']); ?>
                        <img src="<?= htmlspecialchars($logoPath) ?>" alt="Logo de l'etablissement">
                    <?php else: ?>
                        <div class="logo-placeholder">LOGO</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="header-right">
                <div class="header-side-content">
                    <div class="header-line-group">
                        <p class="header-line republic-line"><?= htmlspecialchars((string) $stateRepublicEn) ?></p>
                    </div>
                    <div class="header-line-group">
                        <p class="header-line motto-line"><?= htmlspecialchars((string) $stateMottoEn) ?></p>
                    </div>
                    <div class="header-line-group">
                        <p class="header-line ministry-line"><?= htmlspecialchars((string) $stateMinistryEn) ?></p>
                    </div>
                    <?php if ($stateDelegationEnHtml !== ''): ?>
                        <div class="header-line-group">
                            <p class="header-line delegation-line"><?= $stateDelegationEnHtml ?></p>
                        </div>
                    <?php endif; ?>
                    <p class="header-line slogan-line"><?= htmlspecialchars((string) $stateSloganEn) ?></p>
                    <div class="header-contact-row">
                        <p class="header-contact"><span class="header-contact-label"><?= htmlspecialchars($phoneLabelEn) ?>:</span> <span class="header-contact-value"><?= htmlspecialchars($schoolPhone) ?></span></p>
                        <p class="header-contact"><span class="header-contact-label"><?= htmlspecialchars($addressLabelEn) ?>:</span> <span class="header-contact-value"><?= htmlspecialchars($schoolAddress) ?></span></p>
                    </div>
                </div>
            </div>

        </div>

        <div class="header-branding">
            <div class="school-name-display"><?= htmlspecialchars($schoolDisplayName) ?></div>
            <div class="academic-year-display"><?= __('academic_years') ?> : <?= htmlspecialchars((string) ($activeYear['nom'] ?? '')) ?></div>
        </div>

        <!-- B. TITRE ET CARTE D'IDENTITÉ -->
        <div class="student-header-layout">
            <div class="student-photo-block">
                <?php if (!empty($student['photo_eleve'])): ?>
                    <?php
                    $photoPath = $student['photo_eleve'];
                    if (strpos($photoPath, '/public/uploads/') !== 0 && strpos($photoPath, '/uploads/') === 0) {
                        $photoPath = '/public' . $photoPath;
                    }
                    ?>
                    <div class="student-photo-container">
                        <img src="<?= $photoPath ?>" alt="Photo de l'élève">
                    </div>
                <?php else: ?>
                    <div class="student-photo-container student-photo-placeholder">👤</div>
                <?php endif; ?>
            </div>
            <div class="student-identity-grid">
                <div class="student-identity-item full">
                    <span class="student-identity-label"><?= __('name_and_surname') ?> :</span>
                    <span class="student-name-value"><?= htmlspecialchars($studentLastName . ' ' . ($student['prenom'] ?? '')) ?></span>
                </div>

                <div class="student-identity-item">
                    <span class="student-identity-label"><?= __('department') ?> :</span>
                    <span class="student-identity-value"><?= htmlspecialchars((string) ($student['department_nom'] ?? '-')) ?></span>
                </div>
                <div class="student-identity-item">
                    <span class="student-identity-label"><?= __('matricule') ?> :</span>
                    <span class="student-identity-value"><?= htmlspecialchars((string) ($displayMatricule ?? $student['matricule'] ?? '')) ?></span>
                </div>
                <div class="student-identity-item">
                    <span class="student-identity-label"><?= __('class') ?> :</span>
                    <span class="student-identity-value"><?= htmlspecialchars((string) ($student['class_nom'] ?? '')) ?></span>
                </div>
                <div class="student-identity-item">
                    <span class="student-identity-label"><?= __('birth_date') ?> :</span>
                    <span class="student-identity-value"><?= htmlspecialchars(formatBulletinDate($birthDate)) ?></span>
                </div>
                <div class="student-identity-item">
                    <span class="student-identity-label"><?= __('birth_place') ?> :</span>
                    <span class="student-identity-value"><?= htmlspecialchars($birthPlace) ?></span>
                </div>
                <div class="student-identity-item">
                    <span class="student-identity-label"><?= __('sex') ?> :</span>
                    <span class="student-identity-value"><?= htmlspecialchars((string) ($student['sexe'] ?? '-')) ?></span>
                </div>
                <div class="student-identity-item">
                    <span class="student-identity-label"><?= __('effectif') ?> :</span>
                    <span class="student-identity-value"><?= (int) $effectif ?></span>
                </div>
                <div class="student-identity-item">
                    <span class="student-identity-label"><?= __('repeating') ?> :</span>
                    <span class="student-identity-value"><?= $isRedoublant ? __('yes') : __('no') ?></span>
                </div>
                <div class="student-identity-footer">
                    <div class="student-identity-item student-contact-item">
                        <span class="student-identity-label"><?= __('parents_guardians_contact') ?> :</span>
                        <span class="student-identity-value"><?= htmlspecialchars(implode(' / ', array_filter([
                            $student['parent_contact'] ?? '',
                            $student['guardian_contact'] ?? '',
                        ])) ?: '-') ?></span>
                    </div>
                    <div class="student-identity-item student-principal-item">
                        <span class="student-identity-label"><?= __('main_teacher') ?> :</span>
                        <span class="student-identity-value"><?= htmlspecialchars((string) ($professor_name ?? '-')) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="title-box" style="font-weight: bold;"><?= __('report_card') ?> <?= strtoupper($bulletinType) ?></div>

