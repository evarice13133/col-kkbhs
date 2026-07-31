<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use PDO;

/**
 * Contrôleur du module Procès-Verbal d'évaluation.
 *
 * Génère des documents administratifs agrégés par classe, incluant :
 * - Une matrice complète élèves × notes par matière
 * - La moyenne individuelle de chaque élève
 * - Les statistiques globales de la classe
 * - La répartition des mentions
 */
class ProcesVerbalController extends BulletinController
{
    /**
     * Interface de sélection du procès-verbal.
     */
    public function index(): void
    {
        $anneesScolaires = $this->db->query(
            "SELECT id, nom, is_active FROM academic_years ORDER BY id DESC"
        )->fetchAll(PDO::FETCH_ASSOC);

        $anneeId = (int) ($_GET['academic_year_id'] ?? 0);
        if ($anneeId <= 0) {
            $anneeActive = $this->getActiveAcademicYear();
            $anneeId = (int) $anneeActive['id'];
        }

        // 1. Récupération des types d'enseignement actifs
        $teachingTypes = $this->db->query(
            "SELECT id, code, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC"
        )->fetchAll(PDO::FETCH_ASSOC);

        $teachingTypeId = isset($_GET['teaching_type_id']) ? (int)$_GET['teaching_type_id'] : 0;
        $classeId = (int) ($_GET['class_id'] ?? 0);

        // Si une classe était présélectionnée, vérifier son type d'enseignement
        $selectedClassInfo = $classeId > 0 ? $this->getClassInfo($classeId) : null;

        // Si le filtre type d'enseignement a été choisi explicitement (ex: teaching_type_id > 0)
        // et qu'une classe était sélectionnée mais n'appartient pas à ce type, on réinitialise la classe
        if ($teachingTypeId > 0 && $selectedClassInfo) {
            if ((int)($selectedClassInfo['teaching_type_id'] ?? 0) !== $teachingTypeId) {
                $classeId = 0;
                $selectedClassInfo = null;
            }
        } elseif ($teachingTypeId <= 0 && $selectedClassInfo) {
            // Si pas de filtre explicite mais classe sélectionnée, déduire le type
            $teachingTypeId = (int) ($selectedClassInfo['teaching_type_id'] ?? 0);
        }

        // Récupération des classes filtrées par le type d'enseignement (si spécifié > 0)
        $classes = $this->getAccessibleClassesWithTeachingType($teachingTypeId > 0 ? $teachingTypeId : null);

        $isLmdClass = ($selectedClassInfo['teaching_type_code'] ?? '') === 'LMD';
        $evalTtId = $teachingTypeId > 0 ? $teachingTypeId : (int)($selectedClassInfo['teaching_type_id'] ?? 0);

        $sequences = $this->getEvaluationsByTeachingType($evalTtId > 0 ? $evalTtId : null);
        $trimestres = [1, 2, 3];

        $titrePage = __('pv_title');
        include __DIR__ . '/../Views/proces_verbal/index.php';
    }

    /**
     * Génère le procès-verbal d'Évaluation pour le Supérieur LMD.
     * Route : GET /proces-verbal/evaluation
     */
    public function evaluation(): void
    {
        $classeId       = (int) ($_GET['class_id'] ?? 0);
        $sequenceId     = (int) ($_GET['sequence_id'] ?? $_GET['evaluation_id'] ?? 0);
        $anneeId        = (int) ($_GET['academic_year_id'] ?? 0);
        $reqTtId        = (int) ($_GET['teaching_type_id'] ?? 0);

        $sequence = $this->getSequenceById($sequenceId);
        if (!$this->canAccessClass($classeId) || !$sequence || !(int) $sequence['is_active']) {
            header("Location: /proces-verbal");
            exit;
        }

        $annee      = $this->resolveAcademicYear($anneeId);
        $eleves     = $this->getStudentsByClass($classeId);
        $classeInfo = $this->getClassInfo($classeId);
        $classTtId  = (int) ($classeInfo['teaching_type_id'] ?? 0);

        // Vérification que la classe appartient au Type d'enseignement sélectionné si spécifié
        if ($reqTtId > 0 && $classTtId > 0 && $reqTtId !== $classTtId) {
            header("Location: /proces-verbal");
            exit;
        }

        $teachingTypeId = $reqTtId > 0 ? $reqTtId : $classTtId;

        if (empty($eleves)) {
            header("Location: /proces-verbal");
            exit;
        }

        $donneesPV = $this->construireDataSequence($classeId, $sequence, $annee, $eleves);

        $isLmd = ($classeInfo['teaching_type_code'] ?? '') === 'LMD';
        if ($isLmd) {
            $donneesPV['matriceEleves'] = $this->enrichirMatriceLmd($donneesPV['matriceEleves'], $donneesPV['matieres']);
            $donneesPV['statsClasseLmd'] = $this->calculerStatsLmd($donneesPV['matriceEleves'], $donneesPV['matieres'], count($eleves));
        }

        // Formater la période d'évaluation (dates de début et fin avec i18n)
        $periodeLabel = $this->formatPeriodeEvaluation($sequence);

        // Nom de la session
        $sessionNom = !empty($sequence['short_label']) ? $sequence['short_label'] : ('Session ' . ($sequence['position'] ?? 1));

        $contexte = [
            'typeEvaluation'    => htmlspecialchars($sequence['label']),
            'libelleEvaluation' => htmlspecialchars($sequence['label']),
            'codeEvaluation'    => htmlspecialchars(!empty($sequence['code']) ? $sequence['code'] : ('EVAL-' . $sequence['id'])),
            'periodeLabel'      => $periodeLabel,
            'sessionNom'        => htmlspecialchars($sessionNom),
            'classeNom'         => htmlspecialchars($classeInfo['nom'] ?? '-'),
            'specialiteNom'     => htmlspecialchars($classeInfo['nom'] ?? '-'),
            'niveauNom'         => htmlspecialchars(!empty($classeInfo['niveau_nom']) ? $classeInfo['niveau_nom'] : '-'),
            'cycleNom'          => htmlspecialchars(!empty($classeInfo['cycle_nom']) ? $classeInfo['cycle_nom'] : '-'),
            'departementNom'    => htmlspecialchars(!empty($classeInfo['departement_nom']) ? $classeInfo['departement_nom'] : '-'),
            'filiereNom'        => htmlspecialchars(!empty($classeInfo['filiere_nom']) ? $classeInfo['filiere_nom'] : ($classeInfo['departement_nom'] ?? '-')),
            'anneeNom'          => htmlspecialchars($annee['nom'] ?? '-'),
            'dateGeneration'    => date('d/m/Y'),
            'effectif'          => count($eleves),
            'teaching_type_id'  => $teachingTypeId,
            'institution'       => $this->getInstitutionSettings($teachingTypeId),
        ];

        $nomFichier = $this->genererNomFichierPV($classeInfo['nom'] ?? 'classe', $sequence['code'] ?? $sequence['label']);
        $template = $isLmd ? __DIR__ . '/../Views/proces_verbal/document_lmd.php' : __DIR__ . '/../Views/proces_verbal/document.php';
        include $template;
    }

    /**
     * Formate la période réelle d'une évaluation pour l'affichage dans le PV.
     * Respecte le système i18n (FR/EN) et les règles métier spécifiées.
     */
    protected function formatPeriodeEvaluation(?array $sequence = null): string
    {
        $hasStart = !empty($sequence['start_date']);
        $hasEnd   = !empty($sequence['end_date']);

        if ($hasStart && $hasEnd) {
            $tsStart = strtotime($sequence['start_date']);
            $tsEnd   = strtotime($sequence['end_date']);

            if ($tsStart && $tsEnd) {
                $monthStartNum = (int) date('n', $tsStart);
                $monthEndNum   = (int) date('n', $tsEnd);
                $yearStart     = date('Y', $tsStart);
                $yearEnd       = date('Y', $tsEnd);

                $lang = \App\Core\Locale::get();

                $monthsFr = [
                    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
                ];

                $monthsEn = [
                    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                ];

                $months = ($lang === 'en') ? $monthsEn : $monthsFr;

                // Même mois et même année (ex: Janvier 2026 / January 2026)
                if ($monthStartNum === $monthEndNum && $yearStart === $yearEnd) {
                    return $months[$monthStartNum] . ' ' . $yearStart;
                }

                // Mois différents mais même année (ex: Janvier - Février 2026 / January - February 2026)
                if ($yearStart === $yearEnd) {
                    return $months[$monthStartNum] . ' - ' . $months[$monthEndNum] . ' ' . $yearStart;
                }

                // Années différentes (ex: Décembre 2025 - Janvier 2026)
                return $months[$monthStartNum] . ' ' . $yearStart . ' - ' . $months[$monthEndNum] . ' ' . $yearEnd;
            }
        }

        // Période par défaut si aucune date de début/fin n'est définie : Mois et Année de la date courante (date serveur)
        $lang = \App\Core\Locale::get();
        $currentMonthNum = (int) date('n');
        $currentYear     = date('Y');

        $monthsFr = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];

        $monthsEn = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        $months = ($lang === 'en') ? $monthsEn : $monthsFr;

        return $months[$currentMonthNum] . ' ' . $currentYear;
    }

    /**
     * Récupère les classes accessibles avec leur code de type d'enseignement, optionnellement filtrées par type.
     */
    protected function getAccessibleClassesWithTeachingType(?int $teachingTypeId = null): array
    {
        $params = [];
        $whereTt = "";
        if ($teachingTypeId && $teachingTypeId > 0) {
            $whereTt = " WHERE c.teaching_type_id = ? ";
            $params[] = $teachingTypeId;
        }

        if (in_array(Session::get('user_role'), ['superadmin', 'admin'], true)) {
            $sql = "SELECT c.id, c.nom, c.teaching_type_id, tt.code as teaching_type_code 
                    FROM classes c 
                    LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id 
                    {$whereTt}
                    ORDER BY c.nom ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $academicYearId = $this->getActiveAcademicYear()['id'] ?? 0;
        $sql = "SELECT DISTINCT c.id, c.nom, c.teaching_type_id, tt.code as teaching_type_code
            FROM teacher_assignments ta
            JOIN classes c ON c.id = ta.class_id
            LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id
            WHERE ta.user_id = ? AND ta.academic_year_id = ?" . ($teachingTypeId > 0 ? " AND c.teaching_type_id = ?" : "") . "
            ORDER BY c.nom ASC";
        
        $paramsUser = [(int) Session::get('user_id'), $academicYearId];
        if ($teachingTypeId > 0) {
            $paramsUser[] = $teachingTypeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($paramsUser);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les évaluations filtrées par type d'enseignement si spécifié.
     */
    protected function getEvaluationsByTeachingType(?int $teachingTypeId = null): array
    {
        $sql = "SELECT s.* 
                FROM sequences s 
                LEFT JOIN teaching_types tt ON s.teaching_type_id = tt.id 
                WHERE s.is_active = 1 AND (tt.actif = 1 OR s.teaching_type_id IS NULL)";
        $params = [];
        if ($teachingTypeId && $teachingTypeId > 0) {
            $sql .= " AND (s.teaching_type_id = ? OR s.teaching_type_id IS NULL)";
            $params[] = $teachingTypeId;
        }
        $sql .= " ORDER BY s.position ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Génère le procès-verbal de séquence.
     * Route : GET /proces-verbal/sequence
     */
    public function sequence(): void
    {
        $classeId   = (int) ($_GET['class_id'] ?? 0);
        $sequenceId = (int) ($_GET['sequence_id'] ?? 0);
        $anneeId    = (int) ($_GET['academic_year_id'] ?? 0);
        $reqTtId    = (int) ($_GET['teaching_type_id'] ?? 0);

        $sequence = $this->getSequenceById($sequenceId);
        if (!$this->canAccessClass($classeId) || !$sequence || !(int) $sequence['is_active']) {
            header("Location: /proces-verbal");
            exit;
        }

        $annee      = $this->resolveAcademicYear($anneeId);
        $eleves     = $this->getStudentsByClass($classeId);
        $classeInfo = $this->getClassInfo($classeId);
        $classTtId  = (int) ($classeInfo['teaching_type_id'] ?? 0);

        if ($reqTtId > 0 && $classTtId > 0 && $reqTtId !== $classTtId) {
            header("Location: /proces-verbal");
            exit;
        }

        $teachingTypeId = $reqTtId > 0 ? $reqTtId : $classTtId;

        if (empty($eleves)) {
            header("Location: /proces-verbal");
            exit;
        }

        $donneesPV = $this->construireDataSequence($classeId, $sequence, $annee, $eleves);

        $isLmd = ($classeInfo['teaching_type_code'] ?? '') === 'LMD';
        if ($isLmd) {
            $donneesPV['matriceEleves'] = $this->enrichirMatriceLmd($donneesPV['matriceEleves'], $donneesPV['matieres']);
            $donneesPV['statsClasseLmd'] = $this->calculerStatsLmd($donneesPV['matriceEleves'], $donneesPV['matieres'], count($eleves));
        }

        $contexte = [
            'typeEvaluation' => __('pv_sequence'),
            'periodeLabel'   => $this->formatPeriodeEvaluation($sequence),
            'classeNom'      => htmlspecialchars($classeInfo['nom'] ?? '-'),
            'specialiteNom'  => htmlspecialchars($classeInfo['nom'] ?? '-'),
            'niveauNom'      => htmlspecialchars(!empty($classeInfo['niveau_nom']) ? $classeInfo['niveau_nom'] : '-'),
            'cycleNom'       => htmlspecialchars(!empty($classeInfo['cycle_nom']) ? $classeInfo['cycle_nom'] : '-'),
            'departementNom' => htmlspecialchars(!empty($classeInfo['departement_nom']) ? $classeInfo['departement_nom'] : '-'),
            'filiereNom'     => htmlspecialchars(!empty($classeInfo['filiere_nom']) ? $classeInfo['filiere_nom'] : ($classeInfo['departement_nom'] ?? '-')),
            'codeEvaluation' => 'SEQ-' . $sequence['id'],
            'anneeNom'       => htmlspecialchars($annee['nom'] ?? '-'),
            'dateGeneration' => date('d/m/Y'),
            'effectif'       => count($eleves),
            'teaching_type_id' => $teachingTypeId,
            'institution'    => $this->getInstitutionSettings($teachingTypeId),
        ];

        $nomFichier = $this->genererNomFichierPV($classeInfo['nom'] ?? 'classe', $sequence['label']);
        $template = $isLmd ? __DIR__ . '/../Views/proces_verbal/document_lmd.php' : __DIR__ . '/../Views/proces_verbal/document.php';
        include $template;
    }

    /**
     * Génère le procès-verbal de trimestre.
     * Route : GET /proces-verbal/trimestre
     */
    public function trimestre(): void
    {
        $classeId  = (int) ($_GET['class_id'] ?? 0);
        $trimestre = (int) ($_GET['term'] ?? 0);
        $anneeId   = (int) ($_GET['academic_year_id'] ?? 0);
        $reqTtId   = (int) ($_GET['teaching_type_id'] ?? 0);

        if (!$this->canAccessClass($classeId) || !in_array($trimestre, [1, 2, 3], true)) {
            header("Location: /proces-verbal");
            exit;
        }

        $annee              = $this->resolveAcademicYear($anneeId);
        $eleves             = $this->getStudentsByClass($classeId);
        $classeInfo         = $this->getClassInfo($classeId);
        $classTtId          = (int) ($classeInfo['teaching_type_id'] ?? 0);

        if ($reqTtId > 0 && $classTtId > 0 && $reqTtId !== $classTtId) {
            header("Location: /proces-verbal");
            exit;
        }

        $teachingTypeId = $reqTtId > 0 ? $reqTtId : $classTtId;
        $sequencesTrimestre = $this->getActiveSequencesByTerm($trimestre);

        if (empty($eleves)) {
            header("Location: /proces-verbal");
            exit;
        }

        $donneesPV = $this->construireDataTrimestre($classeId, $trimestre, $sequencesTrimestre, $annee, $eleves);

        $isLmd = ($classeInfo['teaching_type_code'] ?? '') === 'LMD';
        if ($isLmd) {
            $donneesPV['matriceEleves'] = $this->enrichirMatriceLmd($donneesPV['matriceEleves'], $donneesPV['matieres']);
            $donneesPV['statsClasseLmd'] = $this->calculerStatsLmd($donneesPV['matriceEleves'], $donneesPV['matieres'], count($eleves));
        }

        // Si des séquences existent pour le trimestre, déduire la plage globale
        $seqMin = null;
        $seqMax = null;
        if (!empty($sequencesTrimestre)) {
            $starts = array_filter(array_column($sequencesTrimestre, 'start_date'));
            $ends   = array_filter(array_column($sequencesTrimestre, 'end_date'));
            $seqMin = !empty($starts) ? min($starts) : null;
            $seqMax = !empty($ends) ? max($ends) : null;
        }

        $dummySequence = ($seqMin || $seqMax) ? ['start_date' => $seqMin, 'end_date' => $seqMax] : null;

        $contexte = [
            'typeEvaluation' => __('pv_trimestre') . ' ' . $trimestre,
            'periodeLabel'   => $this->formatPeriodeEvaluation($dummySequence),
            'classeNom'      => htmlspecialchars($classeInfo['nom'] ?? '-'),
            'specialiteNom'  => htmlspecialchars($classeInfo['nom'] ?? '-'),
            'niveauNom'      => htmlspecialchars(!empty($classeInfo['niveau_nom']) ? $classeInfo['niveau_nom'] : '-'),
            'cycleNom'       => htmlspecialchars(!empty($classeInfo['cycle_nom']) ? $classeInfo['cycle_nom'] : '-'),
            'departementNom' => htmlspecialchars(!empty($classeInfo['departement_nom']) ? $classeInfo['departement_nom'] : '-'),
            'filiereNom'     => htmlspecialchars(!empty($classeInfo['filiere_nom']) ? $classeInfo['filiere_nom'] : ($classeInfo['departement_nom'] ?? '-')),
            'codeEvaluation' => 'TRIM-' . $trimestre,
            'anneeNom'       => htmlspecialchars($annee['nom'] ?? '-'),
            'dateGeneration' => date('d/m/Y'),
            'effectif'       => count($eleves),
            'teaching_type_id' => $teachingTypeId,
            'institution'    => $this->getInstitutionSettings($teachingTypeId),
        ];

        $nomFichier = $this->genererNomFichierPV($classeInfo['nom'] ?? 'classe', 'trimestre-' . $trimestre);
        $template = $isLmd ? __DIR__ . '/../Views/proces_verbal/document_lmd.php' : __DIR__ . '/../Views/proces_verbal/document.php';
        include $template;
    }

    /**
     * Génère le procès-verbal annuel.
     * Route : GET /proces-verbal/annuel
     */
    public function annuel(): void
    {
        $classeId = (int) ($_GET['class_id'] ?? 0);
        $anneeId  = (int) ($_GET['academic_year_id'] ?? 0);
        $reqTtId  = (int) ($_GET['teaching_type_id'] ?? 0);

        if (!$this->canAccessClass($classeId)) {
            header("Location: /proces-verbal");
            exit;
        }

        $annee      = $this->resolveAcademicYear($anneeId);
        $eleves     = $this->getStudentsByClass($classeId);
        $classeInfo = $this->getClassInfo($classeId);
        $classTtId  = (int) ($classeInfo['teaching_type_id'] ?? 0);

        if ($reqTtId > 0 && $classTtId > 0 && $reqTtId !== $classTtId) {
            header("Location: /proces-verbal");
            exit;
        }

        $teachingTypeId = $reqTtId > 0 ? $reqTtId : $classTtId;

        if (empty($eleves)) {
            header("Location: /proces-verbal");
            exit;
        }

        $donneesPV = $this->construireDataAnnuel($classeId, $annee, $eleves);

        $isLmd = ($classeInfo['teaching_type_code'] ?? '') === 'LMD';
        if ($isLmd) {
            $donneesPV['matriceEleves'] = $this->enrichirMatriceLmd($donneesPV['matriceEleves'], $donneesPV['matieres']);
            $donneesPV['statsClasseLmd'] = $this->calculerStatsLmd($donneesPV['matriceEleves'], $donneesPV['matieres'], count($eleves));
        }

        $contexte = [
            'typeEvaluation' => __('pv_annuel'),
            'periodeLabel'   => $this->formatPeriodeEvaluation(null),
            'classeNom'      => htmlspecialchars($classeInfo['nom'] ?? '-'),
            'specialiteNom'  => htmlspecialchars($classeInfo['nom'] ?? '-'),
            'niveauNom'      => htmlspecialchars(!empty($classeInfo['niveau_nom']) ? $classeInfo['niveau_nom'] : '-'),
            'cycleNom'       => htmlspecialchars(!empty($classeInfo['cycle_nom']) ? $classeInfo['cycle_nom'] : '-'),
            'departementNom' => htmlspecialchars(!empty($classeInfo['departement_nom']) ? $classeInfo['departement_nom'] : '-'),
            'filiereNom'     => htmlspecialchars(!empty($classeInfo['filiere_nom']) ? $classeInfo['filiere_nom'] : ($classeInfo['departement_nom'] ?? '-')),
            'codeEvaluation' => 'ANNUAL',
            'anneeNom'       => htmlspecialchars($annee['nom'] ?? '-'),
            'dateGeneration' => date('d/m/Y'),
            'effectif'       => count($eleves),
            'teaching_type_id' => $teachingTypeId,
            'institution'    => $this->getInstitutionSettings($teachingTypeId),
        ];

        $nomFichier = $this->genererNomFichierPV($classeInfo['nom'] ?? 'classe', 'annuel');
        $template = $isLmd ? __DIR__ . '/../Views/proces_verbal/document_lmd.php' : __DIR__ . '/../Views/proces_verbal/document.php';
        include $template;
    }

    // =========================================================================
    // CONSTRUCTION DES DONNÉES : SÉQUENCE
    // =========================================================================

    /**
     * Construit toutes les données pour un PV de séquence :
     * matrice élèves × notes, statistiques agrégées, mentions.
     */
    private function construireDataSequence(int $classeId, array $sequence, array $annee, array $eleves): array
    {
        $matieres      = $this->getClassSubjects($classeId);
        $statsMatieres = $this->getSubjectStatsForSequence($classeId, $sequence['label'], (int) $annee['id']);
        $classement    = $this->computeSequenceRanking($classeId, $sequence['label'], (int) $annee['id'], true);
        $statsClasse   = $this->buildClassStats($classement);

        // Récupération de toutes les notes brutes de la classe pour cette séquence
        $notesMatrice = $this->fetchNotesMatriceSequence($classeId, $sequence['label'], (int) $annee['id']);

        // Construction de la matrice élèves × matières avec moyennes individuelles
        $matriceEleves = $this->buildMatriceEleves($matieres, $eleves, $classement, $notesMatrice);

        // Ligne de moyennes de classe par matière (pied de tableau)
        $moyennesClasse = $this->buildMoyennesClasseParMatiere($matieres, $statsMatieres);

        return [
            'matieres'             => $matieres,
            'matriceEleves'        => $matriceEleves,
            'moyennesClasse'       => $moyennesClasse,
            'statsClasse'          => $statsClasse,
            'repartitionMentions'  => $this->calculerRepartitionMentions($classement),
            'tauxReussiteGlobal'   => $this->calculerTauxReussiteGlobal($classement, count($eleves)),
            'nombreMatieres'       => count($matieres),
            'moyenneGenerale'      => $statsClasse['average'] ?? null,
        ];
    }

    // =========================================================================
    // CONSTRUCTION DES DONNÉES : TRIMESTRE
    // =========================================================================

    /**
     * Construit toutes les données pour un PV de trimestre.
     * Les notes par matière sont des moyennes des 2 séquences du trimestre.
     */
    private function construireDataTrimestre(int $classeId, int $trimestre, array $sequencesTrimestre, array $annee, array $eleves): array
    {
        $matieres      = $this->getClassSubjects($classeId);
        $statsMatieres = $this->getSubjectStatsForTrimester($classeId, $sequencesTrimestre, (int) $annee['id']);
        $classement    = $this->computeTrimesterRanking($classeId, $sequencesTrimestre, (int) $annee['id'], true);
        $statsClasse   = $this->buildClassStats($classement);

        // Notes moyennées sur les 2 séquences du trimestre
        $notesMatrice = $this->fetchNotesMatriceTrimestre($classeId, $sequencesTrimestre, (int) $annee['id']);

        $matriceEleves  = $this->buildMatriceEleves($matieres, $eleves, $classement, $notesMatrice);
        $moyennesClasse = $this->buildMoyennesClasseParMatiere($matieres, $statsMatieres);

        return [
            'matieres'             => $matieres,
            'matriceEleves'        => $matriceEleves,
            'moyennesClasse'       => $moyennesClasse,
            'statsClasse'          => $statsClasse,
            'repartitionMentions'  => $this->calculerRepartitionMentions($classement),
            'tauxReussiteGlobal'   => $this->calculerTauxReussiteGlobal($classement, count($eleves)),
            'nombreMatieres'       => count($matieres),
            'moyenneGenerale'      => $statsClasse['average'] ?? null,
        ];
    }

    // =========================================================================
    // CONSTRUCTION DES DONNÉES : ANNUEL
    // =========================================================================

    /**
     * Construit toutes les données pour un PV annuel.
     * Les notes par matière sont des moyennes des 3 trimestres.
     */
    private function construireDataAnnuel(int $classeId, array $annee, array $eleves): array
    {
        $matieres = $this->getClassSubjects($classeId);

        $sequencesParTrimestre = [
            1 => $this->getActiveSequencesByTerm(1),
            2 => $this->getActiveSequencesByTerm(2),
            3 => $this->getActiveSequencesByTerm(3),
        ];

        $statsMatieres = $this->getSubjectStatsForAnnual($classeId, $sequencesParTrimestre, (int) $annee['id']);
        $classement    = $this->computeAnnualRanking($classeId, $sequencesParTrimestre, (int) $annee['id'], true);
        $statsClasse   = $this->buildClassStats($classement);

        // Notes annuelles (moyenne des 3 trimestres) par élève et par matière
        $notesMatrice = $this->fetchNotesMatriceAnnuel($classeId, $sequencesParTrimestre, (int) $annee['id']);

        $matriceEleves  = $this->buildMatriceEleves($matieres, $eleves, $classement, $notesMatrice);
        $moyennesClasse = $this->buildMoyennesClasseParMatiere($matieres, $statsMatieres);

        return [
            'matieres'             => $matieres,
            'matriceEleves'        => $matriceEleves,
            'moyennesClasse'       => $moyennesClasse,
            'statsClasse'          => $statsClasse,
            'repartitionMentions'  => $this->calculerRepartitionMentions($classement),
            'tauxReussiteGlobal'   => $this->calculerTauxReussiteGlobal($classement, count($eleves)),
            'nombreMatieres'       => count($matieres),
            'moyenneGenerale'      => $statsClasse['average'] ?? null,
        ];
    }

    // =========================================================================
    // REQUÊTES SQL : RÉCUPÉRATION DES NOTES PAR ÉLÈVE ET PAR MATIÈRE
    // =========================================================================

    /**
     * Récupère les notes de tous les élèves d'une classe pour une séquence.
     * Retourne : [student_id => [subject_id => valeur_note]]
     *
     * @param int    $classeId      Identifiant de la classe
     * @param string $sequenceLabel Label de la séquence (ex: "Trimestre 1 - Sequence 1")
     * @param int    $anneeId       Identifiant de l'année académique
     * @return array                Matrice notes indexée par élève → matière
     */
    private function fetchNotesMatriceSequence(int $classeId, string $sequenceLabel, int $anneeId): array
    {
        $sql = "SELECT g.student_id, g.subject_id, g.valeur
                FROM grades g
                JOIN students st ON st.id = g.student_id
                JOIN subjects s ON s.id = g.subject_id
                WHERE st.class_id = ? AND g.periode = ? AND g.academic_year_id = ? AND st.is_withdrawn = 0 AND s.status = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$classeId, $sequenceLabel, $anneeId]);

        $matrice = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $matrice[(int) $row['student_id']][(int) $row['subject_id']] = (float) $row['valeur'];
        }
        return $matrice;
    }

    /**
     * Récupère les notes moyennées sur les séquences d'un trimestre.
     * Pour chaque élève et chaque matière, calcule la moyenne des séquences disponibles.
     * Retourne : [student_id => [subject_id => moyenne_trimestre]]
     *
     * @param int   $classeId           Identifiant de la classe
     * @param array $sequencesTrimestre Séquences actives du trimestre
     * @param int   $anneeId            Identifiant de l'année académique
     * @return array                    Matrice notes moyennées
     */
    private function fetchNotesMatriceTrimestre(int $classeId, array $sequencesTrimestre, int $anneeId): array
    {
        if (empty($sequencesTrimestre)) {
            return [];
        }

        $labels       = array_column($sequencesTrimestre, 'label');
        $placeholders = implode(',', array_fill(0, count($labels), '?'));
        $params       = array_merge([$classeId], $labels, [$anneeId]);

        $sql = "SELECT g.student_id, g.subject_id, g.valeur
                FROM grades g
                JOIN students st ON st.id = g.student_id
                JOIN subjects s ON s.id = g.subject_id
                WHERE st.class_id = ? AND g.periode IN ($placeholders) AND g.academic_year_id = ? AND st.is_withdrawn = 0 AND s.status = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        // Accumulation des notes par (élève, matière)
        $accumulation = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $accumulation[(int) $row['student_id']][(int) $row['subject_id']][] = (float) $row['valeur'];
        }

        // Calcul de la moyenne par (élève, matière)
        $matrice = [];
        foreach ($accumulation as $studentId => $matieres) {
            foreach ($matieres as $subjectId => $valeurs) {
                $matrice[$studentId][$subjectId] = round(array_sum($valeurs) / count($valeurs), 2);
            }
        }
        return $matrice;
    }

    /**
     * Récupère les notes annuelles (moyenne des 3 trimestres) par élève et matière.
     * La moyenne annuelle de chaque matière = moyenne des moyennes trimestrielles.
     * Retourne : [student_id => [subject_id => moyenne_annuelle]]
     *
     * @param int   $classeId              Identifiant de la classe
     * @param array $sequencesParTrimestre [1 => [...], 2 => [...], 3 => [...]]
     * @param int   $anneeId               Identifiant de l'année académique
     * @return array                        Matrice notes annuelles
     */
    private function fetchNotesMatriceAnnuel(int $classeId, array $sequencesParTrimestre, int $anneeId): array
    {
        // Aplatissement de toutes les séquences et mappage label → trimestre
        $allLabels  = [];
        $labelToTrimestre = [];
        foreach ([1, 2, 3] as $t) {
            foreach ($sequencesParTrimestre[$t] ?? [] as $seq) {
                $allLabels[]                       = $seq['label'];
                $labelToTrimestre[$seq['label']]   = $t;
            }
        }

        if (empty($allLabels)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($allLabels), '?'));
        $params       = array_merge([$classeId], $allLabels, [$anneeId]);

        $sql = "SELECT g.student_id, g.subject_id, g.valeur, g.periode
                FROM grades g
                JOIN students st ON st.id = g.student_id
                JOIN subjects s ON s.id = g.subject_id
                WHERE st.class_id = ? AND g.periode IN ($placeholders) AND g.academic_year_id = ? AND st.is_withdrawn = 0 AND s.status = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        // Accumulation par (élève, matière, trimestre)
        $parTrimestre = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $t = $labelToTrimestre[$row['periode']] ?? null;
            if ($t === null) continue;
            $parTrimestre[(int) $row['student_id']][(int) $row['subject_id']][$t][] = (float) $row['valeur'];
        }

        // Calcul : moyenne par séquence → moyenne par trimestre → moyenne annuelle
        $matrice = [];
        foreach ($parTrimestre as $studentId => $matieres) {
            foreach ($matieres as $subjectId => $trimestres) {
                $moyennesTrimestre = [];
                foreach ([1, 2, 3] as $t) {
                    if (!empty($trimestres[$t])) {
                        $moyennesTrimestre[] = array_sum($trimestres[$t]) / count($trimestres[$t]);
                    }
                }
                if (!empty($moyennesTrimestre)) {
                    $matrice[$studentId][$subjectId] = round(array_sum($moyennesTrimestre) / count($moyennesTrimestre), 2);
                }
            }
        }
        return $matrice;
    }

    // =========================================================================
    // CONSTRUCTION DE LA MATRICE ÉLÈVES ET DES STATISTIQUES
    // =========================================================================

    /**
     * Construit la matrice de lignes élèves pour la vue du tableau principal.
     * Chaque ligne contient : nom, prénom, une note par matière, moyenne individuelle,
     * et un flag indiquant si l'élève est en situation d'échec (moyenne < 10).
     *
     * @param array $matieres      Liste des matières (colonnes du tableau)
     * @param array $eleves        Liste des élèves de la classe
     * @param array $classement    Classement général avec moyennes et rangs
     * @param array $notesMatrice  [student_id => [subject_id => note]]
     * @return array               Lignes formatées pour la vue
     */
    private function buildMatriceEleves(array $matieres, array $eleves, array $classement, array $notesMatrice): array
    {
        $lignes = [];

        foreach ($eleves as $eleve) {
            $eleveId    = (int) $eleve['id'];
            $notesEleve = $notesMatrice[$eleveId] ?? [];

            // Récupération de la moyenne et du rang depuis le classement pré-calculé
            $moyenne = isset($classement[$eleveId]) ? (float) $classement[$eleveId]['average'] : null;
            $rang    = $classement[$eleveId]['rank'] ?? '-';

            // Construction du tableau de notes par matière (dans l'ordre des matières)
            $notesParMatiere = [];
            foreach ($matieres as $matiere) {
                $subjectId = (int) $matiere['id'];
                $note      = $notesEleve[$subjectId] ?? null;
                $notesParMatiere[$subjectId] = $note !== null ? round($note, 2) : null;
            }

            $lignes[] = [
                'id'             => $eleveId,
                'nom'            => htmlspecialchars(strtoupper(trim($eleve['nom']))),
                'prenom'         => htmlspecialchars(ucfirst(strtolower(trim($eleve['prenom'])))),
                'rang'           => $rang,
                'notesParMatiere'=> $notesParMatiere,
                'moyenne'        => $moyenne,
                // Flag rouge : en échec si la moyenne est inférieure à 10
                'enEchec'        => $moyenne !== null && $moyenne < 10.0,
                // Flag orange : entre 10 et 11.99 (borderline)
                'borderline'     => $moyenne !== null && $moyenne >= 10.0 && $moyenne < 12.0,
            ];
        }

        // Tri par ordre de mérite (moyenne décroissante)
        usort($lignes, function ($a, $b) {
            $moyA = $a['moyenne'] ?? -1;
            $moyB = $b['moyenne'] ?? -1;
            if ($moyA === $moyB) {
                return strcmp($a['nom'] . $a['prenom'], $b['nom'] . $b['prenom']);
            }
            return $moyB <=> $moyA;
        });

        return $lignes;
    }

    /**
     * Construit les moyennes de classe par matière pour le pied de tableau.
     *
     * @param array $matieres      Liste des matières
     * @param array $statsMatieres Statistiques pré-calculées (depuis getSubjectStatsFor*)
     * @return array               [subject_id => moyenne_formatee_ou_tiret]
     */
    private function buildMoyennesClasseParMatiere(array $matieres, array $statsMatieres): array
    {
        $moyennes = [];
        foreach ($matieres as $matiere) {
            $matiereId = (int) $matiere['id'];
            $moyenne   = $statsMatieres[$matiereId]['average'] ?? null;
            $moyennes[$matiereId] = $moyenne !== null ? number_format((float) $moyenne, 2) : '-';
        }
        return $moyennes;
    }

    // =========================================================================
    // CALCULS STATISTIQUES GLOBAUX
    // =========================================================================

    /**
     * Répartit le nombre d'élèves par mention selon leur moyenne générale.
     */
    private function calculerRepartitionMentions(array $classement): array
    {
        $repartition = [
            'Excellent'           => 0,
            'Tres Bien'           => 0,
            'Bien'                => 0,
            'Assez Bien'          => 0,
            'Passable'            => 0,
            'Admis au Rattrapage' => 0,
            'Insuffisant'         => 0,
        ];

        foreach ($classement as $entree) {
            $mention = $this->getMention($entree['average']);
            if (isset($repartition[$mention])) {
                $repartition[$mention]++;
            }
        }
        return $repartition;
    }

    /**
     * Calcule le taux de réussite global (% d'élèves avec moyenne >= 10).
     */
    private function calculerTauxReussiteGlobal(array $classement, int $effectifTotal): ?float
    {
        if ($effectifTotal === 0 || empty($classement)) {
            return null;
        }
        $nbReussis = 0;
        foreach ($classement as $entree) {
            if ((float) $entree['average'] >= 10) {
                $nbReussis++;
            }
        }
        return round(($nbReussis / $effectifTotal) * 100, 1);
    }

    /**
     * Génère un nom de fichier propre pour le procès-verbal à télécharger.
     */
    private function genererNomFichierPV(string $nomClasse, string $periode): string
    {
        $classeSlug  = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $nomClasse), '-'));
        $periodeSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $periode), '-'));
        return 'pv-' . ($classeSlug ?: 'classe') . '-' . ($periodeSlug ?: 'periode') . '.pdf';
    }

    /**
     * Surcharge de getClassInfo pour extraire les métadonnées spécifiques au LMD (teaching_type, department, cycle, etc.)
     */
    protected function getClassInfo(int $classId)
    {
        $sql = "SELECT c.id, c.nom, c.section_id, c.cycle_id, c.department_id, c.level_id, c.teaching_type_id,
                       u.nom as main_teacher_nom, u.prenom as main_teacher_prenom,
                       tt.code as teaching_type_code, tt.nom as teaching_type_nom,
                       d.nom as departement_nom,
                       cy.nom as cycle_nom,
                       COALESCE(lvl.libelle_fr, lvl.code, '') as niveau_nom
                FROM classes c 
                LEFT JOIN users u ON c.main_teacher_id = u.id 
                LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id
                LEFT JOIN departments d ON c.department_id = d.id
                LEFT JOIN cycles cy ON c.cycle_id = cy.id
                LEFT JOIN levels lvl ON c.level_id = lvl.id
                WHERE c.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$classId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Enrichit la matrice des élèves avec les crédits LMD, le sexe, le matricule et la décision.
     */
    private function enrichirMatriceLmd(array $matriceEleves, array $matieres): array
    {
        if (empty($matriceEleves)) return [];

        // Récupérer sexe et matricule depuis la BDD pour chaque élève
        $eleveIds = [];
        foreach ($matriceEleves as $el) {
            if (isset($el['id'])) $eleveIds[] = (int)$el['id'];
        }

        $detailsEleves = [];
        if (!empty($eleveIds)) {
            $in = implode(',', array_fill(0, count($eleveIds), '?'));
            // Dans ce schema, le matricule de l'élève est stocké dans le champ 'email' de la table students
            $stmt = $this->db->prepare("SELECT id, email as matricule FROM students WHERE id IN ($in)");
            $stmt->execute($eleveIds);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $detailsEleves[(int)$row['id']] = $row;
            }

            // Si la colonne 'sexe' existe dans la table 'students'
            try {
                $stmtSexe = $this->db->prepare("SELECT id, sexe FROM students WHERE id IN ($in)");
                $stmtSexe->execute($eleveIds);
                foreach ($stmtSexe->fetchAll(PDO::FETCH_ASSOC) as $rowSexe) {
                    if (isset($detailsEleves[(int)$rowSexe['id']])) {
                        $detailsEleves[(int)$rowSexe['id']]['sexe'] = $rowSexe['sexe'];
                    }
                }
            } catch (\Exception $e) {
                // Colonne sexe optionnelle si absente
            }
        }

        foreach ($matriceEleves as &$el) {
            $eId = (int)($el['id'] ?? 0);
            $el['matricule'] = $detailsEleves[$eId]['matricule'] ?? '-';
            $el['sexe'] = !empty($detailsEleves[$eId]['sexe']) ? strtoupper(substr($detailsEleves[$eId]['sexe'], 0, 1)) : 'M';

            // Groupement des matières par Groupe de Modules (UE)
            $groupedMatieres = [];
            foreach ($matieres as $m) {
                $grp = $m['groupe_nom'] ?? $m['groupe'] ?? $m['group_name'] ?? 'UE FONDAMENTALES';
                $groupedMatieres[$grp][] = $m;
            }

            $moyennesUE = [];
            $creditsAcquis = 0;
            $allUEValidated = true;
            $hasNotes = false;

            foreach ($groupedMatieres as $grpName => $subs) {
                $sumWeighted = 0;
                $sumCoef = 0;
                $ueCreditsAcquis = 0;
                $ueTotalCredits = 0;
                $ueEliminated = false; // Règle d'élimination UE

                foreach ($subs as $m) {
                    $mid = (int)$m['id'];
                    $coef = (float)($m['coefficient'] ?? 1);
                    $ueTotalCredits += $coef;
                    $note = $el['notesParMatiere'][$mid] ?? null;

                    // Si la note est absente (NULL, vide ou non saisie) ou < 10 => ÉLIMINÉ DANS CETTE UE
                    if ($note === null || $note === '' || (float)$note < 10.0) {
                        $ueEliminated = true;
                    }

                    if ($note !== null && $note !== '') {
                        $hasNotes = true;
                        $nVal = (float)$note;
                        $sumWeighted += ($nVal * $coef);
                        $sumCoef += $coef;
                        if ($nVal >= 10.0) {
                            $creditsAcquis += $coef;
                            $ueCreditsAcquis += $coef;
                        }
                    }
                }

                // La Moy. UE ne doit être calculée que si toutes les notes du groupe sont renseignées et >= 10/20
                if (!$ueEliminated && $sumCoef > 0) {
                    $moyUE = $sumWeighted / $sumCoef;
                    $isUEValidated = true;
                } else {
                    $moyUE = null; // Remplacée par "EL"
                    $isUEValidated = false;
                    $allUEValidated = false;
                }

                $moyennesUE[$grpName] = [
                    'moyenne' => $moyUE,
                    'is_valid' => $isUEValidated,
                    'is_eliminated' => $ueEliminated,
                    'credits_acquis' => $ueCreditsAcquis,
                    'credits_total' => $ueTotalCredits
                ];
            }

            $el['moyennesUE'] = $moyennesUE;
            $el['creditsAcquis'] = $creditsAcquis;

            // Calcul de la Moy. Gén. : moyenne des Moy. UE si toutes validées (aucun EL)
            $sumMoyUE = 0;
            $countMoyUE = 0;
            foreach ($moyennesUE as $ueRes) {
                if ($ueRes['moyenne'] !== null) {
                    $sumMoyUE += $ueRes['moyenne'];
                    $countMoyUE++;
                }
            }

            if ($allUEValidated && $countMoyUE > 0 && $hasNotes) {
                $moyenneGeneraleLmd = $sumMoyUE / $countMoyUE;
                $el['moyenneLmdRaw'] = $moyenneGeneraleLmd;
                $el['moyenneLmdDisplay'] = number_format($moyenneGeneraleLmd, 2, ',', ' ');
                $el['isAdmisLmd'] = true;
            } else {
                $el['moyenneLmdRaw'] = null;
                $el['moyenneLmdDisplay'] = 'EL';
                $el['isAdmisLmd'] = false;
            }

            $el['mention'] = ($el['moyenneLmdRaw'] !== null) ? $this->getMention($el['moyenneLmdRaw']) : '-';
        }
        unset($el);

        return $matriceEleves;
    }

    /**
     * Calcule le tableau des 15 indicateurs statistiques du Supérieur LMD.
     */
    private function calculerStatsLmd(array $matriceEleves, array $matieres, int $effectifTotal): array
    {
        $presents = 0;
        $absents = 0;
        $admis = 0;
        $rattrapages = 0;
        $totalCreditsValides = 0;
        $totalCreditsAttendusIndiv = 0;
        foreach ($matieres as $m) {
            $totalCreditsAttendusIndiv += (float)($m['coefficient'] ?? 1);
        }
        $totalCreditsAttendusAll = $totalCreditsAttendusIndiv * $effectifTotal;

        $toutesMoyennes = [];
        $notesParSubject = [];

        foreach ($matriceEleves as $el) {
            $moy = $el['moyenneLmdRaw'] ?? $el['moyenne'] ?? null;
            if ($moy !== null) {
                $presents++;
                $valMoy = (float)$moy;
                $toutesMoyennes[] = $valMoy;
                if (!empty($el['isAdmisLmd'])) {
                    $admis++;
                } else {
                    $rattrapages++;
                }
            } else {
                $absents++;
            }

            $totalCreditsValides += ($el['creditsAcquis'] ?? 0);

            foreach ($matieres as $m) {
                $mid = (int)$m['id'];
                $n = $el['notesParMatiere'][$mid] ?? null;
                if ($n !== null) {
                    $notesParSubject[$mid][] = (float)$n;
                }
            }
        }

        // Taux de réussite
        $tauxReussite = $effectifTotal > 0 ? round(($admis / $effectifTotal) * 100, 1) : 0;

        // Analyse matières : plus de notes < 10 et plus de notes >= 10
        $matiereInf10Count = [];
        $matiereSup10Count = [];
        foreach ($matieres as $m) {
            $mid = (int)$m['id'];
            $notes = $notesParSubject[$mid] ?? [];
            $inf = 0; $sup = 0;
            foreach ($notes as $nVal) {
                if ($nVal >= 10.0) $sup++; else $inf++;
            }
            $matiereInf10Count[$mid] = $inf;
            $matiereSup10Count[$mid] = $sup;
        }

        $midInfMax = !empty($matiereInf10Count) ? array_search(max($matiereInf10Count), $matiereInf10Count) : null;
        $midSupMax = !empty($matiereSup10Count) ? array_search(max($matiereSup10Count), $matiereSup10Count) : null;

        $nomMatiereInf10 = '-';
        $nomMatiereSup10 = '-';
        foreach ($matieres as $m) {
            if ((int)$m['id'] === $midInfMax) $nomMatiereInf10 = $m['nom'];
            if ((int)$m['id'] === $midSupMax) $nomMatiereSup10 = $m['nom'];
        }

        return [
            'effectifTotal' => $effectifTotal,
            'presents' => $presents,
            'absents' => $absents,
            'admis' => $admis,
            'rattrapages' => $rattrapages,
            'tauxReussite' => $tauxReussite,
            'totalCreditsValides' => $totalCreditsValides,
            'totalCreditsAttendus' => $totalCreditsAttendusAll,
            'totalCreditsObtenus' => $totalCreditsValides,
            'totalCreditsNonValides' => max(0, $totalCreditsAttendusAll - $totalCreditsValides),
            'matierePlusNotesInf10' => $nomMatiereInf10,
            'matierePlusNotesSup10' => $nomMatiereSup10,
            'meilleureMoyenne' => !empty($toutesMoyennes) ? max($toutesMoyennes) : 0,
            'plusFaibleMoyenne' => !empty($toutesMoyennes) ? min($toutesMoyennes) : 0,
        ];
    }
}

