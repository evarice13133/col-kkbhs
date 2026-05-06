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

        $classes   = $this->getAccessibleClasses();
        $classeId  = (int) ($_GET['class_id'] ?? 0);
        $sequences = $this->getActiveSequences();
        $trimestres = [1, 2, 3];

        $titrePage = __('pv_title');
        include __DIR__ . '/../Views/proces_verbal/index.php';
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

        $sequence = $this->getSequenceById($sequenceId);
        if (!$this->canAccessClass($classeId) || !$sequence || !(int) $sequence['is_active']) {
            header("Location: /proces-verbal");
            exit;
        }

        $annee      = $this->resolveAcademicYear($anneeId);
        $eleves     = $this->getStudentsByClass($classeId);
        $classeInfo = $this->getClassInfo($classeId);

        if (empty($eleves)) {
            header("Location: /proces-verbal");
            exit;
        }

        $donneesPV = $this->construireDataSequence($classeId, $sequence, $annee, $eleves);

        $contexte = [
            'typeEvaluation' => __('pv_sequence'),
            'periodeLabel'   => htmlspecialchars($sequence['label']),
            'classeNom'      => htmlspecialchars($classeInfo['nom'] ?? '-'),
            'anneeNom'       => htmlspecialchars($annee['nom'] ?? '-'),
            'dateGeneration' => date('d/m/Y'),
            'effectif'       => count($eleves),
            'institution'    => $this->getInstitutionSettings(),
        ];

        $nomFichier = $this->genererNomFichierPV($classeInfo['nom'] ?? 'classe', $sequence['label']);
        include __DIR__ . '/../Views/proces_verbal/document.php';
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

        if (!$this->canAccessClass($classeId) || !in_array($trimestre, [1, 2, 3], true)) {
            header("Location: /proces-verbal");
            exit;
        }

        $annee              = $this->resolveAcademicYear($anneeId);
        $eleves             = $this->getStudentsByClass($classeId);
        $classeInfo         = $this->getClassInfo($classeId);
        $sequencesTrimestre = $this->getActiveSequencesByTerm($trimestre);

        if (empty($eleves)) {
            header("Location: /proces-verbal");
            exit;
        }

        $donneesPV = $this->construireDataTrimestre($classeId, $trimestre, $sequencesTrimestre, $annee, $eleves);

        $contexte = [
            'typeEvaluation' => __('pv_trimestre') . ' ' . $trimestre,
            'periodeLabel'   => __('Trimestre') . ' ' . $trimestre,
            'classeNom'      => htmlspecialchars($classeInfo['nom'] ?? '-'),
            'anneeNom'       => htmlspecialchars($annee['nom'] ?? '-'),
            'dateGeneration' => date('d/m/Y'),
            'effectif'       => count($eleves),
            'institution'    => $this->getInstitutionSettings(),
        ];

        $nomFichier = $this->genererNomFichierPV($classeInfo['nom'] ?? 'classe', 'trimestre-' . $trimestre);
        include __DIR__ . '/../Views/proces_verbal/document.php';
    }

    /**
     * Génère le procès-verbal annuel.
     * Route : GET /proces-verbal/annuel
     */
    public function annuel(): void
    {
        $classeId = (int) ($_GET['class_id'] ?? 0);
        $anneeId  = (int) ($_GET['academic_year_id'] ?? 0);

        if (!$this->canAccessClass($classeId)) {
            header("Location: /proces-verbal");
            exit;
        }

        $annee      = $this->resolveAcademicYear($anneeId);
        $eleves     = $this->getStudentsByClass($classeId);
        $classeInfo = $this->getClassInfo($classeId);

        if (empty($eleves)) {
            header("Location: /proces-verbal");
            exit;
        }

        $donneesPV = $this->construireDataAnnuel($classeId, $annee, $eleves);

        $contexte = [
            'typeEvaluation' => __('pv_annuel'),
            'periodeLabel'   => __('pv_annuel'),
            'classeNom'      => htmlspecialchars($classeInfo['nom'] ?? '-'),
            'anneeNom'       => htmlspecialchars($annee['nom'] ?? '-'),
            'dateGeneration' => date('d/m/Y'),
            'effectif'       => count($eleves),
            'institution'    => $this->getInstitutionSettings(),
        ];

        $nomFichier = $this->genererNomFichierPV($classeInfo['nom'] ?? 'classe', 'annuel');
        include __DIR__ . '/../Views/proces_verbal/document.php';
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
}
