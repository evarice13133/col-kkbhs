<?php

namespace App\Services\Timetable;

use App\Core\Database;
use PDO;

class TimetableWizardService
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance()->getConnection();
    }

    /**
     * Étape 1 : Récupère uniquement le type d'enseignement « Supérieur LMD ».
     */
    public function getTeachingTypes(): array
    {
        $stmt = $this->db->query("
            SELECT id, nom, code 
            FROM teaching_types 
            WHERE actif = 1 AND (code = 'LMD' OR nom LIKE '%Supérieur%' OR nom LIKE '%LMD%')
            ORDER BY id DESC
            LIMIT 1
        ");
        $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($types)) {
            // Fallback si pas encore configuré
            $types = [[
                'id' => 9,
                'nom' => 'Supérieur LMD',
                'code' => 'LMD'
            ]];
        }

        foreach ($types as &$t) {
            $t['is_default'] = true;
            $t['nom'] = 'Supérieur LMD';
        }
        return $types;
    }

    /**
     * Étape 2 : Récupère les cycles académiques actifs du type Supérieur LMD.
     */
    public function getCyclesByTeachingType(int $teachingTypeId): array
    {
        $stmt = $this->db->prepare("
            SELECT c.id, c.nom 
            FROM cycles c
            LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id
            WHERE c.status = 1 
              AND (
                  c.teaching_type_id = :type_id 
                  OR c.teaching_type_id = 9 
                  OR tt.code = 'LMD' 
                  OR LOWER(tt.nom) LIKE '%lmd%' 
                  OR LOWER(tt.nom) LIKE '%supérieur%'
              )
              AND (tt.code IS NULL OR (tt.code != 'SEC00' AND LOWER(tt.nom) NOT LIKE '%secondaire%'))
              AND LOWER(c.nom) NOT LIKE '%premier cycle%' 
              AND LOWER(c.nom) NOT LIKE '%second cycle%' 
              AND LOWER(c.nom) NOT LIKE '%1ere cycle%' 
              AND LOWER(c.nom) NOT LIKE '%2nd cycle%' 
              AND LOWER(c.nom) NOT LIKE '%secondaire%'
            ORDER BY c.nom ASC
        ");
        $stmt->execute(['type_id' => $teachingTypeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Étape 3a : Récupère les niveaux (Level 1, Level 2, L1, L2, L3...) rattachés au cycle ou généraux.
     */
    public function getLevelsByCycle(int $cycleId): array
    {
        // 1. Récupération prioritaire via la table pivot cycle_levels si un cycleId est fourni
        if ($cycleId > 0) {
            $stmt = $this->db->prepare("
                SELECT l.id, 
                       COALESCE(NULLIF(l.libelle_fr, ''), NULLIF(l.libelle_en, ''), CONCAT('Niveau ', l.code)) as nom, 
                       l.code, l.libelle_fr, l.libelle_en
                FROM levels l
                JOIN cycle_levels cl ON cl.level_id = l.id
                WHERE cl.cycle_id = ? AND l.status = 1
                ORDER BY l.id ASC
            ");
            $stmt->execute([$cycleId]);
            $levels = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($levels)) {
                foreach ($levels as &$l) {
                    $displayName = !empty($l['libelle_fr']) ? $l['libelle_fr'] : (!empty($l['libelle_en']) ? $l['libelle_en'] : $l['nom']);
                    if (is_numeric($displayName)) {
                        $displayName = "Niveau " . $displayName;
                    }
                    $l['nom'] = $displayName;
                }
                return $levels;
            }
        }

        // 2. Fallback via les classes rattachées au cycle
        $stmt = $this->db->prepare("
            SELECT DISTINCT l.id, 
                   COALESCE(NULLIF(l.libelle_fr, ''), NULLIF(l.libelle_en, ''), CONCAT('Niveau ', l.code)) as nom, 
                   l.code, l.libelle_fr, l.libelle_en
            FROM levels l
            JOIN classes c ON c.level_id = l.id
            WHERE (c.cycle_id = ? OR ? = 0) AND l.status = 1
            ORDER BY l.id ASC
        ");
        $stmt->execute([$cycleId, $cycleId]);
        $levels = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Fallback général : tous les niveaux actifs
        if (empty($levels)) {
            $stmt = $this->db->query("
                SELECT id, 
                       COALESCE(NULLIF(libelle_fr, ''), NULLIF(libelle_en, ''), CONCAT('Niveau ', code)) as nom, 
                       code, libelle_fr, libelle_en
                FROM levels 
                WHERE status = 1
                ORDER BY id ASC
            ");
            $levels = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Formater proprement le champ nom pour la lisibilité
        foreach ($levels as &$l) {
            $displayName = !empty($l['libelle_fr']) ? $l['libelle_fr'] : (!empty($l['libelle_en']) ? $l['libelle_en'] : $l['nom']);
            if (is_numeric($displayName)) {
                $displayName = "Niveau " . $displayName;
            }
            $l['nom'] = $displayName;
        }

        return $levels;
    }

    /**
     * Étape 3b : Récupère les classes appartenant au cycle et au niveau.
     */
    public function getClassesByLevel(int $cycleId, int $levelId): array
    {
        $stmt = $this->db->prepare("
            SELECT c.id, c.nom, c.level_id, c.cycle_id,
                   (SELECT COUNT(*) FROM students s WHERE s.class_id = c.id AND s.is_withdrawn = 0 AND s.actif = 1) as effectif
            FROM classes c
            WHERE (c.cycle_id = ? OR ? = 0)
              AND (c.level_id = ? OR ? = 0)
            ORDER BY c.nom ASC
        ");
        $stmt->execute([$cycleId, $cycleId, $levelId, $levelId]);
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($classes) && $cycleId > 0) {
            // Si pas de niveau restreint
            $stmt = $this->db->prepare("
                SELECT c.id, c.nom, c.level_id, c.cycle_id,
                       (SELECT COUNT(*) FROM students s WHERE s.class_id = c.id AND s.is_withdrawn = 0 AND s.actif = 1) as effectif
                FROM classes c
                WHERE c.cycle_id = ?
                ORDER BY c.nom ASC
            ");
            $stmt->execute([$cycleId]);
            $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $classes;
    }

    /**
     * Étape 4 : Récupère les semaines de cours de l'année académique active.
     */
    public function getWeeksByAcademicYear(int $academicYearId): array
    {
        $stmt = $this->db->prepare("
            SELECT id, libelle, date_debut, date_fin 
            FROM timetable_weeks 
            WHERE academic_year_id = ? 
            ORDER BY date_debut ASC
        ");
        $stmt->execute([$academicYearId]);
        $weeks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($weeks)) {
            $weeks = $this->db->query("SELECT id, libelle, date_debut, date_fin FROM timetable_weeks ORDER BY date_debut ASC")->fetchAll(PDO::FETCH_ASSOC);
        }

        return $weeks;
    }

    /**
     * Récupère la matrice complète pour une grille multi-classes par niveau / cycle / semaine.
     */
    public function getMultiClassGridData(int $cycleId, int $levelId, int $weekId): array
    {
        // 1. Classes du niveau
        $classes = $this->getClassesByLevel($cycleId, $levelId);

        if (empty($classes)) {
            // S'il n'y a aucune classe rattachée au niveau, récupérer toutes les classes du cycle
            $stmt = $this->db->prepare("SELECT id, nom FROM classes WHERE cycle_id = ? ORDER BY nom ASC");
            $stmt->execute([$cycleId]);
            $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $classIds = array_column($classes, 'id');

        // 2. Matières (de l'ensemble des classes du niveau - Filtre Supérieur LMD)
        if (!empty($classIds)) {
            $inClause = implode(',', array_map('intval', $classIds));
            $stmtSub = $this->db->query("
                SELECT DISTINCT s.id, s.nom, COALESCE(s.code_uv, s.code_ue, '') as code, '#3b82f6' as couleur_hex
                FROM subject_classes sc
                JOIN subjects s ON sc.subject_id = s.id
                LEFT JOIN classes c ON sc.class_id = c.id
                WHERE sc.class_id IN ($inClause)
                  AND (s.teaching_type_id = 9 OR s.teaching_type_id IS NULL OR c.teaching_type_id = 9 OR c.teaching_type_id IS NULL)
                ORDER BY s.nom ASC
            ");
            $subjects = $stmtSub->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $subjects = [];
        }

        // Générer des couleurs pastels distinctes et harmonieuses pour chaque matière
        $pastelColors = [
            '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', 
            '#06b6d4', '#84cc16', '#6366f1', '#d97706', '#0284c7', 
            '#059669', '#7c3aed', '#db2777', '#0891b2', '#65a30d'
        ];
        foreach ($subjects as $idx => &$sub) {
            if (empty($sub['couleur_hex']) || $sub['couleur_hex'] === '#3b82f6') {
                $sub['couleur_hex'] = $pastelColors[$idx % count($pastelColors)];
            }
        }
        unset($sub);

        // 3. Enseignants disponibles (Supérieur LMD via user_teaching_types)
        $stmtTeachers = $this->db->query("
            SELECT DISTINCT u.id, TRIM(CONCAT(COALESCE(u.nom, ''), ' ', COALESCE(u.prenom, ''))) as nom_complet, u.role 
            FROM users u
            LEFT JOIN user_teaching_types utt ON u.id = utt.user_id
            WHERE u.role IN ('enseignant', 'admin', 'it_manager', 'superadmin')
              AND (utt.teaching_type_id = 9 OR utt.teaching_type_id IS NULL)
            ORDER BY u.nom ASC, u.prenom ASC
        ");
        $teachers = $stmtTeachers->fetchAll(PDO::FETCH_ASSOC);

        // 4. Salles de classe
        $stmtRooms = $this->db->query("SELECT id, nom, code, capacite FROM class_rooms WHERE status = 1 ORDER BY nom ASC");
        $rooms = $stmtRooms->fetchAll(PDO::FETCH_ASSOC);

        // 5. Créneaux horaires
        $stmtSlots = $this->db->query("SELECT * FROM timetable_time_slots ORDER BY ordre_affichage ASC, heure_debut ASC");
        $slots = $stmtSlots->fetchAll(PDO::FETCH_ASSOC);

        // 6. Indexer toutes les entrées existantes d'emploi du temps pour ces classes à cette semaine
        $matrix = []; // [day_of_week][slot_id][class_id] => entry array
        $timetablesByClass = []; // class_id => timetable record

        if (!empty($classIds) && $weekId > 0) {
            $inClause = implode(',', array_map('intval', $classIds));
            $stmtTt = $this->db->query("
                SELECT id, class_id, titre, is_locked, statut
                FROM timetables 
                WHERE class_id IN ($inClause) AND week_id = $weekId
            ");
            $timetablesList = $stmtTt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($timetablesList as $tt) {
                $timetablesByClass[$tt['class_id']] = $tt;
            }

            $ttIds = array_column($timetablesList, 'id');
            if (!empty($ttIds)) {
                $inTtClause = implode(',', array_map('intval', $ttIds));
                $stmtEntries = $this->db->query("
                    SELECT te.*, s.nom as subject_name, s.code_uv as subject_code,
                           TRIM(CONCAT(COALESCE(u.nom, ''), ' ', COALESCE(u.prenom, ''))) as teacher_name,
                           r.nom as room_name, r.code as room_code,
                           t.class_id
                    FROM timetable_entries te
                    JOIN timetables t ON te.timetable_id = t.id
                    JOIN subjects s ON te.subject_id = s.id
                    JOIN users u ON te.teacher_id = u.id
                    JOIN class_rooms r ON te.room_id = r.id
                    WHERE te.timetable_id IN ($inTtClause)
                ");
                $entries = $stmtEntries->fetchAll(PDO::FETCH_ASSOC);

                foreach ($entries as $e) {
                    $matrix[$e['day_of_week']][$e['slot_id']][$e['class_id']] = $e;
                }
            }
        }

        $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        if ($weekId > 0) {
            $stmtW = $this->db->prepare("SELECT date_debut, date_fin FROM timetable_weeks WHERE id = ?");
            $stmtW->execute([$weekId]);
            $wRow = $stmtW->fetch(PDO::FETCH_ASSOC);
            if ($wRow && !empty($wRow['date_debut']) && !empty($wRow['date_fin'])) {
                $days = $this->generateDaysFromDates($wRow['date_debut'], $wRow['date_fin']);
            }
        }

        return [
            'classes' => $classes,
            'subjects' => $subjects,
            'teachers' => $teachers,
            'rooms' => $rooms,
            'slots' => $slots,
            'days' => $days,
            'matrix' => $matrix,
            'timetablesByClass' => $timetablesByClass
        ];
    }

    /**
     * Génère la liste dynamique des noms de jours en français entre deux dates (max 7 jours).
     */
    public function generateDaysFromDates(string $startDateStr, string $endDateStr): array
    {
        if (empty($startDateStr) || empty($endDateStr)) {
            return ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        }

        try {
            $start = new \DateTime($startDateStr);
            $end = new \DateTime($endDateStr);
        } catch (\Throwable $e) {
            return ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        }

        if ($end < $start) {
            return [];
        }

        $frenchDays = [
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
            7 => 'Dimanche'
        ];

        $days = [];
        $curr = clone $start;
        $count = 0;
        while ($curr <= $end && $count < 7) {
            $dayNum = (int)$curr->format('N');
            $days[] = $frenchDays[$dayNum];
            $curr->modify('+1 day');
            $count++;
        }

        return !empty($days) ? $days : ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    }

    /**
     * Récupère uniquement les matières associées officiellement à une classe du Supérieur LMD.
     */
    public function getSubjectsByClass(int $classId): array
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT s.id, s.nom, COALESCE(s.code_uv, s.code_ue, '') as code, '#3b82f6' as couleur_hex,
                   COALESCE(s.coefficient, 1) as coefficient,
                   IF(sc.subject_id IS NOT NULL, 1, 0) as is_attached
            FROM subjects s
            LEFT JOIN subject_classes sc ON sc.subject_id = s.id AND sc.class_id = ?
            WHERE (s.teaching_type_id = 9 OR s.teaching_type_id IS NULL)
            ORDER BY is_attached DESC, s.nom ASC
        ");
        $stmt->execute([$classId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère TOUS les enseignants du Supérieur LMD.
     * Indique pour chaque enseignant s'il est déjà officiellement affecté à cette matière (is_assigned = 1 ou 0).
     */
    public function getTeachersBySubject(int $subjectId, int $classId = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT u.id, TRIM(CONCAT(COALESCE(u.nom, ''), ' ', COALESCE(u.prenom, ''))) as nom_complet, u.role,
                   IF(ta.user_id IS NOT NULL, 1, 0) as is_assigned
            FROM users u
            LEFT JOIN user_teaching_types utt ON u.id = utt.user_id
            LEFT JOIN teacher_assignments ta ON ta.user_id = u.id AND ta.subject_id = ?
            WHERE u.role IN ('enseignant', 'admin', 'it_manager', 'superadmin')
              AND u.status = 1
              AND (utt.teaching_type_id = 9 OR utt.teaching_type_id IS NULL)
            ORDER BY is_assigned DESC, u.nom ASC, u.prenom ASC
        ");
        $stmt->execute([$subjectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
