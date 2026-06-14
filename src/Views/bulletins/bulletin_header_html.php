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
                <td></td>
                <td colspan="4" class="nowrap" style="width: auto;"><span
                        class="student-info-label"><?= __('name_and_surname') ?>
                        :</span><span
                        class="student-info-value uppercase"><?= htmlspecialchars($studentLastName . ' ' . ($student['prenom'] ?? '')) ?></span>
                </td>
                <td class="nowrap" style="width: 1%;"><span class="student-info-label"><?= __('matricule') ?>
                        :</span><span
                        class="student-info-value"><?= htmlspecialchars((string) ($displayMatricule ?? $student['matricule'] ?? '')) ?></span>
                </td>
                <td class="nowrap" style="width: 1%;"><span class="student-info-label"><?= __('class') ?> :</span><span
                        class="student-info-value"><?= htmlspecialchars((string) ($student['class_nom'] ?? '')) ?></span>
                </td>
            </tr>
            <tr>
                <td class="nowrap"><span class="student-info-label"><?= __('birth_date') ?> :</span><span
                        class="student-info-value"><?= htmlspecialchars(formatBulletinDate($birthDate)) ?></span></td>
                <td colspan="2" class="nowrap"><span class="student-info-label"><?= __('birth_place') ?> :</span><span
                        class="student-info-value"><?= htmlspecialchars($birthPlace) ?></span></td>
                <td class="nowrap"><span class="student-info-label"><?= __('sex') ?> :</span><span
                        class="student-info-value"><?= htmlspecialchars((string) ($student['sexe'] ?? '-')) ?></span>
                </td>
                <td colspan="2" class="nowrap"><span class="student-info-label"><?= __('repeating') ?> :</span><span
                        class="check-group student-info-value"><?= __('yes') ?><?= $isRedoublant ? '[X]' : '[ ]' ?>
                        <?= __('no') ?><?= !$isRedoublant ? '[X]' : '[ ]' ?></span></td>
                <td class="nowrap"><span class="student-info-label"><?= __('effectif') ?> :</span><span
                        class="student-info-value"><?= (int) $effectif ?></span>
                </td>
            </tr>
        </table>
        <!-- titre du bulletin -->
         <div class="title-box" style="font-weight: bold; margin-bottom:5px;"><?= __('report_card') ?> <?= strtoupper($bulletinType) ?>
        </div>
