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
        grid-template-columns: minmax(0, 1fr) minmax(150px, 155px) minmax(0, 1fr);
        align-items: start;
        column-gap: 10px;
        margin-bottom: 5px;
        page-break-inside: avoid;
    }
    .header-left,
    .header-center,
    .header-right { min-width: 0; }
    .header-left { text-align: center; }
    .header-center { display: flex; flex-direction: column; align-items: center; text-align: center; }
    .header-right { text-align: center; }
    .header-branding { grid-column: 1 / -1; text-align: center; margin-top: 2px; }
    .header-side-content { display: inline-flex; flex-direction: column; align-items: stretch; width: auto; max-width: 100%; padding: 0 2px; overflow-wrap: anywhere; word-break: normal; }
    .header-line-group { display: table; width: auto; max-width: 100%; margin: 0 auto; }
    .header-line-group .header-line { display: table; width: 100%; }
    .header-contact-row { display: flex; align-items: baseline; justify-content: center; gap: 10px; white-space: nowrap; }
    .header-line, .header-contact, .school-name-display, .academic-year-display { margin: 0; line-height: 1.15; }
    .header-line { font-size: 12px; font-weight: bold; text-transform: uppercase; }
    .header-contact { font-size: 11px; margin-top: 2px; text-transform: uppercase; }
    .header-side-content .republic-line { font-size: 14px; color: #0057b8; }
    .header-side-content .motto-line { font-size: 11px; font-style: italic; }
    .header-side-content .ministry-line { font-size: 12px; color: #000; }
    .header-side-content .slogan-line { font-size: 11px; }
    .header-contact-label { color: #0057b8; }
    .header-contact-value { color: #000; font-weight: 700; }
    .school-name-display { font-family: 'Arial Black', Arial, sans-serif; font-weight: 900; font-size: 23px; color: #0057b8; text-transform: uppercase; text-align: center; overflow-wrap: anywhere; }
    .academic-year-display { margin-top: 2px; margin-bottom: 10px; font-weight: 700; font-size: 16px; text-transform: uppercase; text-align: center; }

    .student-photo-cell {
        width: 101px;
        min-width: 101px;
        height: 100%;
        vertical-align: middle;
        padding: 0 12px 0 0;
        border-right: 1px solid #14347a !important;
    }
    .student-photo-container {
        width: 100%;
        height: auto;
        min-height: 98px;
        background: #fff;
        border: 2px solid #14347a;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        max-width: 100%;
        max-height: 100%;
    }
    .student-photo-container img {
        width: 100%;
        height: auto;
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        object-position: center;
        display: block;
        overflow: hidden;
    }
    .student-photo-placeholder {
        width: 100%;
        height: auto;
        min-height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-size: 40px;
        color: #000;
        line-height: 1.2;
    }
    .student-identity-row {
        padding: 0 4px;
        border: 1px solid #14347a !important;
        line-height: 1;
        margin: 0;
    }
    .student-info-table td,
    .student-info-table tr + tr td,
    .student-info-table .student-photo-cell {
        border: none !important;
    }
    .student-info-table tr {
        margin: 0;
        padding: 0;
    }
    .student-info-table tr + tr td {
        margin-top: 0;
        padding-top: 0;
    }
    .student-identity-half {
        width: 50%;
    }
    .student-identity-label {
        font-weight: 700;
        margin-right: 2px;
        margin-bottom: 0;
        font-size: 9px;
        line-height: 1;
        display: inline-block;
        vertical-align: middle;
        color: #000;
    }
    .student-identity-value {
        font-weight: 700;
        color: #0057b8;
        margin-bottom: 0;
        font-size: 9px;
        line-height: 1;
        display: inline-block;
        vertical-align: middle;
        border-bottom: 1px solid #14347a;
        padding-bottom: 0;
    }
    .student-identity-item {
        display: inline-block;
        margin-right: 10px;
    }
    .student-identity-item:last-child {
        margin-right: 0;
    }
    .student-name-value {
        font-weight: 900;
        font-size: 11px;
        line-height: 1;
        margin-bottom: 0;
        text-transform: uppercase;
        color: #0057b8;
        display: inline-block;
        vertical-align: middle;
        border-bottom: 1px solid #14347a;
        padding-bottom: 0;
    }
    .title-box {
        display: block;
        width: 100%;
        text-align: center;
        font-family: 'Arial Black', Arial, sans-serif;
        font-size: 19px;
        margin: 8px auto 5px;
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
        <table class="student-info-table">
            <tr>
                <td class="student-photo-cell" rowspan="4">
                    <?php if (!empty($student['photo_eleve'])): ?>
                        <?php
                        $photoPath = $student['photo_eleve'];
                        // Gérer les deux formats de chemin: /uploads/ et /public/uploads/
                        if (strpos($photoPath, '/public/uploads/') === 0) {
                            // Le chemin est déjà au bon format
                        } elseif (strpos($photoPath, '/uploads/') === 0) {
                            // Ancien format, ajouter /public/
                            $photoPath = '/public' . $photoPath;
                        }
                        ?>
                        <div class="student-photo-container">
                            <img src="<?= $photoPath ?>" alt="Photo de l'élève">
                        </div>
                    <?php else: ?>
                        <div class="student-photo-placeholder">
                            👤
                        </div>
                    <?php endif; ?>
                </td>
                <td colspan="2" class="student-identity-row student-identity-half">
                    <span class="student-identity-label"><?= __('name_and_surname') ?> :</span>
                    <span class="student-name-value"><?= htmlspecialchars($studentLastName . ' ' . ($student['prenom'] ?? '')) ?></span>
                </td>
                <td colspan="3" class="student-identity-row student-identity-half">
                    <span class="student-identity-label"><?= __('department') ?> :</span>
                    <span class="student-identity-value"><?= htmlspecialchars((string) ($student['department_nom'] ?? '-')) ?></span>
                </td>
            </tr>
            <tr>
                <td class="student-identity-row">
                    <span class="student-identity-label"><?= __('birth_date') ?> :</span>
                    <span class="student-identity-value"><?= htmlspecialchars(formatBulletinDate($birthDate)) ?></span>
                </td>
                <td colspan="2" class="student-identity-row">
                    <span class="student-identity-label"><?= __('matricule') ?> :</span>
                    <span class="student-identity-value"><?= htmlspecialchars((string) ($displayMatricule ?? $student['matricule'] ?? '')) ?></span>
                </td>
                <td class="student-identity-row">
                    <span class="student-identity-label"><?= __('class') ?> :</span>
                    <span class="student-identity-value"><?= htmlspecialchars((string) ($student['class_nom'] ?? '')) ?></span>
                </td>
            </tr>
            <tr>
                <td class="student-identity-row">
                    <span class="student-identity-label"><?= __('birth_place') ?> :</span>
                    <span class="student-identity-value"><?= htmlspecialchars($birthPlace) ?></span>
                </td>
                <td class="student-identity-row">
                    <span class="student-identity-label"><?= __('effectif') ?> :</span>
                    <span class="student-identity-value"><?= (int) $effectif ?></span>
                </td>
                <td class="student-identity-row">
                    <span class="student-identity-label"><?= __('sex') ?> :</span>
                    <span class="student-identity-value"><?= htmlspecialchars((string) ($student['sexe'] ?? '-')) ?></span>
                </td>
                <td colspan="2" class="student-identity-row">
                    <span class="student-identity-label"><?= __('repeating') ?> :</span>
                    <span class="student-identity-value"><?= $isRedoublant ? __('yes') : __('no') ?></span>
                </td>
            </tr>
            <tr>
                <td colspan="3" class="student-identity-row">
                    <span class="student-identity-label"><?= __('parents_guardians_contact') ?> :</span>
                    <span class="student-identity-value">
                        <?= htmlspecialchars(implode(' / ', array_filter([
                            $student['parent_contact'] ?? '',
                            $student['guardian_contact'] ?? '',
                        ])) ?: '-') ?>
                    </span>
                </td>
                <td colspan="2" class="student-identity-row">
                    <span class="student-identity-label"><?= __('main_teacher') ?> :</span>
                    <span class="student-identity-value"><?= htmlspecialchars((string) ($professor_name ?? '-')) ?></span>
                </td>
            </tr>
        </table>

        <div class="title-box" style="font-weight: bold;"><?= __('report_card') ?> <?= strtoupper($bulletinType) ?></div>

