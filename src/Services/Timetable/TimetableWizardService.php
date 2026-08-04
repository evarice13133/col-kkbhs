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
     * Étape 1 : Récupère les types d'enseignement actifs avec indication du type LMD par défaut.
     */
    public function getTeachingTypes(): array
    {
        $stmt = $this->db->query("SELECT id, nom, code FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC");
        $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Si aucun type trouvé, ou pour identifier le supérieur LMD
        foreach ($types as &$t) {
            $t['is_default'] = (stripos($t['nom'], 'LMD') !== false || stripos($t['nom'], 'Supérieur') !== false);
        }
        return $types;
    }

    /**
     * Étape 2 : Récupère les cycles académiques actifs rattachés au type d'enseignement choisi.
     */
    public function getCyclesByTeachingType(int $teachingTypeId): array
    {
        $stmt = $this->db->prepare("
            SELECT id, nom 
            FROM cycles 
            WHERE status = 1 
              AND (teaching_type_id = ? OR teaching_type_id IS NULL)
            ORDER BY nom ASC
        ");
        $stmt->execute([$teachingTypeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Étape 3 : Récupère les classes appartenant au cycle choisi.
     */
    public function getClassesByCycle(int $cycleId): array
    {
        $stmt = $this->db->prepare("
            SELECT c.id, c.nom,
                   (SELECT COUNT(*) FROM students s WHERE s.class_id = c.id AND s.is_withdrawn = 0 AND s.actif = 1) as effectif
            FROM classes c
            WHERE c.cycle_id = ?
            ORDER BY c.nom ASC
        ");
        $stmt->execute([$cycleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Étape 5 : Récupère toutes les données nécessaires à la grille d'emploi du temps pour la classe.
     */
    public function getGridDataForClass(int $classId): array
    {
        // 1. Matières de la classe
        $stmtSub = $this->db->prepare("
            SELECT DISTINCT s.id, s.nom, COALESCE(s.code_uv, s.code_ue, '') as code, '#3b82f6' as couleur_hex
            FROM subject_classes sc
            JOIN subjects s ON sc.subject_id = s.id
            WHERE sc.class_id = ?
            ORDER BY s.nom ASC
        ");
        $stmtSub->execute([$classId]);
        $subjects = $stmtSub->fetchAll(PDO::FETCH_ASSOC);

        // Fallback si pas de subject_classes explicite
        if (empty($subjects)) {
            $subjects = $this->db->query("SELECT id, nom, COALESCE(code_uv, code_ue, '') as code, '#3b82f6' as couleur_hex FROM subjects ORDER BY nom ASC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
        }

        // 2. Enseignants disponibles
        $stmtTeachers = $this->db->query("
            SELECT id, TRIM(CONCAT(COALESCE(nom, ''), ' ', COALESCE(prenom, ''))) as nom_complet, role 
            FROM users 
            WHERE role IN ('enseignant', 'admin', 'it_manager', 'superadmin')
            ORDER BY nom ASC, prenom ASC
        ");
        $teachers = $stmtTeachers->fetchAll(PDO::FETCH_ASSOC);

        // 3. Salles de classe
        $stmtRooms = $this->db->query("SELECT id, nom, code, capacite FROM class_rooms WHERE status = 1 ORDER BY nom ASC");
        $rooms = $stmtRooms->fetchAll(PDO::FETCH_ASSOC);

        // 4. Créneaux horaires
        $stmtSlots = $this->db->query("SELECT * FROM timetable_time_slots ORDER BY ordre_affichage ASC, heure_debut ASC");
        $slots = $stmtSlots->fetchAll(PDO::FETCH_ASSOC);

        return [
            'subjects' => $subjects,
            'teachers' => $teachers,
            'rooms' => $rooms,
            'slots' => $slots,
            'days' => ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi']
        ];
    }
}
