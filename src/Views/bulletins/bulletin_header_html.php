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
?>

<style>
    .student-photo-cell {
        width: 115px;
        vertical-align: top;
        padding: 8px 12px 8px 0;
        border-right: 1px solid #000 !important;
    }
    .student-photo-container {
        width: 105px;
        height: 120px;
        border: 1px solid #000;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .student-photo-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .student-photo-placeholder {
        width: 105px;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-size: 40px;
        color: #000;
        line-height: 1.2;
        border: 1px solid #000;
    }
    .student-identity-row {
        padding: 4px 6px;
        border-bottom: 1px solid #000;
        line-height: 1.2;
    }
    .student-identity-label {
        font-weight: 900;
        margin-right: 3px;
        font-size: 14px;
        color: #1b6e26;
    }
    .student-identity-value {
        font-weight: 900;
        color: #000;
        font-size: 12px;
        border-bottom: 1px solid #000;
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
        border-bottom: 1px solid #000;
    }
</style>

        <!-- A. EN-TÊTE MINISTÉRIEL ET LOGO -->
        <div class="header-wrapper">
            <div class="header-left">
                <div class="header-side-content">
                    <p class="header-line">
                        <?= htmlspecialchars((string) ($i['school_republic'] ?? __('republic_of_cameroon'))) ?>
                    </p>
                    <p class="header-line"><?= htmlspecialchars((string) ($i['school_motto'] ?? __('motto'))) ?></p>
                    <p class="header-line">
                        <?= htmlspecialchars((string) ($i['school_ministry'] ?? __('ministry_secondary_education'))) ?>
                    </p>
                    <p class="header-line"><?= htmlspecialchars((string) ($i['school_slogan'] ?? __('slogan'))) ?></p>
                    <p class="header-contact"><?= htmlspecialchars(strtoupper($contact)) ?></p>
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
                    <p class="header-line">
                        <?= htmlspecialchars((string) ($i['school_republic_en'] ?? 'REPUBLIC OF CAMEROON')) ?>
                    </p>
                    <p class="header-line">
                        <?= htmlspecialchars((string) ($i['school_motto_en'] ?? 'PEACE - WORK - FATHERLAND')) ?>
                    </p>
                    <p class="header-line">
                        <?= htmlspecialchars((string) ($i['school_ministry_en'] ?? 'MINISTRY OF SECONDARY EDUCATION')) ?>
                    </p>
                    <p class="header-line">
                        <?= htmlspecialchars((string) ($i['school_slogan_en'] ?? 'DISCIPLINE - WORK - SUCCESS')) ?>
                    </p>
                    <p class="header-contact"><?= htmlspecialchars(strtoupper($contact)) ?></p>
                </div>
            </div>

            <div class="school-name-row">
                <div class="school-name-display"><?= htmlspecialchars($schoolDisplayName) ?></div>
                <div class="academic-year-display"><?= __('academic_years') ?> : <?= htmlspecialchars((string) ($activeYear['nom'] ?? '')) ?></div>
            </div>
        </div>

        <!-- B. TITRE ET CARTE D'IDENTITÉ -->
       

        <table class="student-info-table">
            <tr>
                <td class="student-photo-cell" rowspan="3">
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
                    <span class="student-identity-label"><?= __('birth_place') ?> :</span>
                    <span class="student-identity-value"><?= htmlspecialchars($birthPlace) ?></span>
                </td>
                <td class="student-identity-row">
                    <span class="student-identity-label"><?= __('matricule') ?> :</span>
                    <span class="student-identity-value"><?= htmlspecialchars((string) ($displayMatricule ?? $student['matricule'] ?? '')) ?></span>
                </td>
            </tr>
            <tr>
                <td class="student-identity-row">
                    <span class="student-identity-label"><?= __('class') ?> :</span>
                    <span class="student-identity-value"><?= htmlspecialchars((string) ($student['class_nom'] ?? '')) ?></span>
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
        </table>
        <!-- titre du bulletin -->
         <div class="title-box" style="font-weight: bold; margin-bottom:5px;"><?= __('report_card') ?> <?= strtoupper($bulletinType) ?>
        </div>
