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
$stateRepublic = $isEnglish ? ($i['school_republic_en'] ?? __('republic_of_cameroon')) : ($i['school_republic'] ?? __('republic_of_cameroon'));
$stateMinistry = $isEnglish ? ($i['school_ministry_en'] ?? __('ministry_secondary_education')) : ($i['school_ministry'] ?? __('ministry_secondary_education'));
$stateMotto = $isEnglish ? ($i['school_motto_en'] ?? __('motto')) : ($i['school_motto'] ?? __('motto'));
$stateSlogan = $isEnglish ? ($i['school_slogan_en'] ?? __('slogan')) : ($i['school_slogan'] ?? __('slogan'));
$stateDelegation = $isEnglish ? ($i['school_delegation_en'] ?? '') : ($i['school_delegation'] ?? '');
$stateDelegationHtml = nl2br(htmlspecialchars(str_replace(';', "\n", trim((string) $stateDelegation)), ENT_QUOTES, 'UTF-8'));
$schoolPhone = trim((string) ($i['school_phone'] ?? ''));
$schoolAddress = trim((string) ($i['school_address'] ?? $i['school_city'] ?? ''));
?>

<style>
    .header-wrapper {
        width: 100%;
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: start;
        column-gap: 8px;
        margin-bottom: 5px;
    }
    .header-left,
    .header-center,
    .header-right { min-width: 0; }
    .header-left { text-align: left; }
    .header-center { display: flex; flex-direction: column; align-items: center; text-align: center; }
    .header-right { text-align: right; }
    .header-side-content { width: 100%; padding: 0 2px; }
    .header-line, .header-contact, .school-name-display, .academic-year-display { margin: 0; line-height: 1.15; }
    .header-line { font-size: 16px; font-weight: bold; text-transform: uppercase; }
    .header-contact { font-size: 15px; }
    .header-left .header-line { font-size: 20px; color: #0057b8; }
    .header-left .ministry-line { font-size: 17px; color: #000; }
    .header-left .header-contact { font-size: 17px; }
    .header-contact-label { color: #0057b8; }
    .header-contact-value { color: #000; font-weight: 700; }
    .header-right .header-line { font-size: 15px; }
    .header-separator { margin: 0; font-size: 13px; line-height: 1; color: #000; }
    .school-name-display { font-family: 'Arial Black', Arial, sans-serif; font-weight: 900; font-size: 19px; color: #0057b8; text-transform: uppercase; }
    .academic-year-display { margin-top: 1px; font-weight: 700; font-size: 17px; text-transform: uppercase; }

    .student-photo-cell {
        width: 86px;
        height: 100%;
        vertical-align: middle;
        padding: 0 12px 0 0;
        border-right: 1px solid #14347a !important;
    }
    .student-photo-container {
        width: 79px;
        height: 100%;
        min-height: 84px;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        max-width: 100%;
        max-height: 100%;
    }
    .student-photo-container img {
        width: 100%;
        height: 100%;
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        object-position: center;
        display: block;
        overflow: hidden;
    }
    .student-photo-placeholder {
        width: 79px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-size: 40px;
        color: #000;
        line-height: 1.2;
    }
    .student-identity-row {
        padding: 4px 6px;
        border: 1px solid #14347a !important;
        line-height: 1.2;
    }
    .student-identity-label {
        font-weight: 900;
        margin-right: 3px;
        font-size: 14px;
        color: #000;
    }
    .student-identity-value {
        font-weight: 900;
        color: #0057b8;
        font-size: 12px;
    }
    .student-identity-item {
        display: inline-block;
        margin-right: 15px;
    }
    .student-identity-item:last-child {
        margin-right: 0;
    }
    .student-name-value {
        font-weight: 900;
        font-size: 14px;
        text-transform: uppercase;
        color: #0057b8;
    }
</style>

        <!-- A. EN-TÊTE EN TROIS COLONNES -->
        <div class="header-wrapper">
            <div class="header-left">
                <div class="header-side-content">
                    <p class="header-line ministry-line"><?= htmlspecialchars((string) $stateMinistry) ?></p>
                    <p class="header-line school-name-display"><?= htmlspecialchars($schoolDisplayName) ?></p>
                    <p class="header-contact"><span class="header-contact-label"><?= htmlspecialchars(__('tel')) ?>:</span> <span class="header-contact-value"><?= htmlspecialchars($schoolPhone) ?></span></p>
                    <p class="header-contact"><span class="header-contact-label"><?= htmlspecialchars(__('address')) ?>:</span> <span class="header-contact-value"><?= htmlspecialchars($schoolAddress) ?></span></p>
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
                <div class="academic-year-display"><?= __('academic_years') ?> : <?= htmlspecialchars((string) ($activeYear['nom'] ?? '')) ?></div>
            </div>

            <div class="header-right">
                <div class="header-side-content">
                    <p class="header-line"><?= htmlspecialchars((string) $stateRepublic) ?></p>
                    <p class="header-separator">*************</p>
                    <p class="header-line"><?= htmlspecialchars((string) $stateMotto) ?></p>
                    <p class="header-separator">*************</p>
                    <?php if ($stateDelegationHtml !== ''): ?>
                        <p class="header-line"><?= $stateDelegationHtml ?></p>
                        <p class="header-separator">*************</p>
                    <?php endif; ?>
                    <p class="header-line"><?= htmlspecialchars((string) $stateSlogan) ?></p>
                </div>
            </div>

        </div>

        <div class="title-box" style="font-weight: bold;"><?= __('report_card') ?> <?= strtoupper($bulletinType) ?></div>

        <!-- B. TITRE ET CARTE D'IDENTITÉ -->
        <div class="department-banner">
            <span class="department-label"><?= htmlspecialchars(__('department')) ?> :</span>
            <span class="department-name"><?= htmlspecialchars((string) ($student['department_nom'] ?? '-')) ?></span>
        </div>

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
                <td colspan="5" class="student-identity-row">
                    <span class="student-identity-label"><?= __('name_and_surname') ?> :</span>
                    <span class="student-name-value"><?= htmlspecialchars($studentLastName . ' ' . ($student['prenom'] ?? '')) ?></span>
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

