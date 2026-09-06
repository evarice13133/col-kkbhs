<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Core\PermissionManager;
use App\Services\AcademicYearService;
use PDO;

/**
 * CompetencyController
 * 
 * Contrôleur pour la gestion des compétences/objectifs des matières.
 * Permet aux administrateurs et enseignants de gérer les compétences
 * selon leurs permissions et affectations.
 */
class CompetencyController
{
    private $db;
    private AcademicYearService $academicYearService;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->academicYearService = new AcademicYearService($this->db);

        if (!Session::isLogged()) {
            header("Location: /login");
            exit;
        }
    }

    /**
     * Liste les compétences accessibles à l'utilisateur
     * Filtre par matière, classe et permissions
     */
    public function index()
    {
        $userRole = Session::get('user_role');
        $userId = (int) Session::get('user_id');

        if (!in_array($userRole, ['admin', 'superadmin'], true) && !PermissionManager::hasPermission('manage_subjects')) {
            header('Location: /');
            exit;
        }

        // Récupérer les filtres
        $subjectId = (int) ($_GET['subject_id'] ?? 0);
        $classId = (int) ($_GET['class_id'] ?? 0);

        // Construire la requête selon le rôle
        if (in_array($userRole, ['admin', 'superadmin'], true)) {
            // Admin voit toutes les compétences
            $sql = "SELECT c.*, s.nom as subject_nom 
                    FROM competencies c
                    LEFT JOIN subjects s ON c.subject_id = s.id
                    WHERE 1=1";
            $params = [];
            
            if ($subjectId > 0) {
                $sql .= " AND c.subject_id = ?";
                $params[] = $subjectId;
            }
            
            $sql .= " ORDER BY c.subject_id, c.position, c.libelle";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $competencies = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Récupérer toutes les matières pour le filtre
            $subjects = $this->db->query("SELECT id, nom FROM subjects WHERE status = 1 ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Enseignant ne voit que les compétences de ses matières affectées
            $sql = "SELECT DISTINCT c.*, s.nom as subject_nom
                    FROM competencies c
                    LEFT JOIN subjects s ON c.subject_id = s.id
                    INNER JOIN teacher_assignments ta ON s.id = ta.subject_id
                    WHERE ta.user_id = ?";
            $params = [$userId];
            
            if ($subjectId > 0) {
                $sql .= " AND c.subject_id = ?";
                $params[] = $subjectId;
            }
            
            $sql .= " ORDER BY c.subject_id, c.position, c.libelle";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $competencies = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Récupérer les matières de l'enseignant pour le filtre
            $subjects = $this->db->prepare("SELECT DISTINCT s.id, s.nom 
                                            FROM subjects s
                                            INNER JOIN teacher_assignments ta ON s.id = ta.subject_id
                                            WHERE ta.user_id = ? AND s.status = 1
                                            ORDER BY s.nom ASC");
            $subjects->execute([$userId]);
            $subjects = $subjects->fetchAll(PDO::FETCH_ASSOC);
        }

        $classes = $this->db->query("SELECT id, nom FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $teachingTypes = $this->db->query("SELECT id, nom, code FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $hasTeachingFormColumn = (bool) $this->db->query("SHOW COLUMNS FROM subject_groups LIKE 'teaching_form_id'")->fetchColumn();
        $groupsSql = "SELECT id, libelle, teaching_type_id" . ($hasTeachingFormColumn ? ", teaching_form_id" : ", NULL AS teaching_form_id") . " FROM subject_groups WHERE status = 1 ORDER BY libelle ASC";
        $groups = $this->db->query($groupsSql)->fetchAll(PDO::FETCH_ASSOC);
        $teachingForms = [];
        if ($this->db->query("SHOW TABLES LIKE 'teaching_forms'")->fetchColumn()) {
            $teachingForms = $this->db->query("SELECT id, nom, teaching_type_id FROM teaching_forms WHERE status = 1 ORDER BY teaching_type_id ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        }
        $allSubjects = $this->db->query("SELECT s.id, s.nom, s.subject_group_id, sg.libelle AS group_nom,
                            sg.teaching_type_id AS group_type_id,
                            " . ($hasTeachingFormColumn ? "sg.teaching_form_id" : "NULL") . " AS group_form_id
                                         FROM subjects s
                                         LEFT JOIN subject_groups sg ON sg.id = s.subject_group_id
                                         WHERE s.status = 1 ORDER BY s.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $activeYearId = $this->academicYearService->getActiveYearId();
        $subjectClassSql = "SELECT DISTINCT subject_id, class_id FROM subject_classes";
        $subjectClassParams = [];
        if ($activeYearId > 0) {
            $subjectClassSql .= " WHERE academic_year_id = ?";
            $subjectClassParams[] = $activeYearId;
        }
        $subjectClassStmt = $this->db->prepare($subjectClassSql);
        $subjectClassStmt->execute($subjectClassParams);
        $subjectClassMap = [];
        foreach ($subjectClassStmt->fetchAll(PDO::FETCH_ASSOC) as $association) {
            $subjectClassMap[(int) $association['class_id']][] = (int) $association['subject_id'];
        }

        include __DIR__ . '/../Views/competencies/index.php';
    }

    public function apiAssignmentData(): void
    {
        header('Content-Type: application/json');
        if (!$this->canManageAssignments()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Non autorisé.']);
            exit;
        }
        $subjects = $this->db->query("SELECT s.id, s.nom, s.subject_group_id, sg.libelle AS group_nom
                                      FROM subjects s LEFT JOIN subject_groups sg ON sg.id = s.subject_group_id
                                      WHERE s.status = 1 ORDER BY s.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'subjects' => $subjects]);
        exit;
    }

    public function apiAssignmentImpact(): void
    {
        header('Content-Type: application/json');
        if (!$this->canManageAssignments()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Non autorisé.']);
            exit;
        }
        $subjectIds = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['subject_ids'] ?? [])))));
        $groupId = (int) ($_POST['group_id'] ?? 0);
        if (!$subjectIds || $groupId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Sélectionnez au moins une matière et un groupe.']);
            exit;
        }

        $placeholders = implode(',', array_fill(0, count($subjectIds), '?'));
        $stmt = $this->db->prepare("SELECT s.nom, sg.libelle AS group_nom FROM subjects s
                                    LEFT JOIN subject_groups sg ON sg.id = s.subject_group_id
                                    WHERE s.id IN ($placeholders)");
        $stmt->execute($subjectIds);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'count' => count($rows), 'items' => $rows,
            'message' => count($rows) . ' matière(s) seront rattachée(s) au groupe sélectionné.']);
        exit;
    }

    public function apiAssignSubjects(): void
    {
        header('Content-Type: application/json');
        if (!$this->canManageAssignments()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Non autorisé.']);
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée.']);
            exit;
        }
        $subjectIds = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['subject_ids'] ?? [])))));
        $groupId = (int) ($_POST['group_id'] ?? 0);
        if (!$subjectIds || $groupId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Sélectionnez au moins une matière et un groupe.']);
            exit;
        }
        $groupCheck = $this->db->prepare("SELECT id FROM subject_groups WHERE id = ? AND status = 1");
        $groupCheck->execute([$groupId]);
        if (!$groupCheck->fetchColumn()) {
            echo json_encode(['success' => false, 'error' => 'Groupe invalide.']);
            exit;
        }
        try {
            $this->db->beginTransaction();
            $placeholders = implode(',', array_fill(0, count($subjectIds), '?'));
            $stmt = $this->db->prepare("UPDATE subjects SET subject_group_id = ? WHERE id IN ($placeholders) AND status = 1");
            $stmt->execute(array_merge([$groupId], $subjectIds));
            $updated = $stmt->rowCount();
            $this->db->commit();
            echo json_encode(['success' => true, 'updated' => $updated]);
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            echo json_encode(['success' => false, 'error' => 'Affectation impossible : ' . $e->getMessage()]);
        }
        exit;
    }

    private function canManageAssignments(): bool
    {
        return in_array(Session::get('user_role'), ['admin', 'superadmin'], true)
            || PermissionManager::hasPermission('manage_subjects');
    }

    public function apiTeacherAssignmentData(): void
    {
        header('Content-Type: application/json');
        if (!$this->canManageAssignments()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Non autorisé.']);
            exit;
        }
        $typeId = (int) ($_GET['teaching_type_id'] ?? 0);
        $classId = (int) ($_GET['class_id'] ?? 0);
        $activeYearId = $this->academicYearService->getActiveYearId();
        $teachersSql = "SELECT DISTINCT u.id, u.nom, u.prenom, u.username
                        FROM users u
                        INNER JOIN user_teaching_types utt ON utt.user_id = u.id
                        WHERE u.role = 'enseignant' AND u.status = 1";
        $teacherParams = [];
        if ($typeId > 0) {
            $teachersSql .= " AND utt.teaching_type_id = ?";
            $teacherParams[] = $typeId;
        }
        $teachersSql .= " ORDER BY u.nom, u.prenom";
        $teacherStmt = $this->db->prepare($teachersSql);
        $teacherStmt->execute($teacherParams);
        $teachers = $teacherStmt->fetchAll(PDO::FETCH_ASSOC);

        $subjectSql = "SELECT DISTINCT s.id, s.nom, s.teaching_type_id,
                              ta.user_id AS assigned_teacher_id,
                              CONCAT(COALESCE(u.prenom, ''), ' ', COALESCE(u.nom, '')) AS assigned_teacher_name
                       FROM subjects s
                       INNER JOIN subject_classes sc ON sc.subject_id = s.id
                       LEFT JOIN teacher_assignments ta ON ta.subject_id = s.id AND ta.class_id = sc.class_id
                           AND (ta.academic_year_id = ? OR ta.academic_year_id IS NULL)
                       LEFT JOIN users u ON u.id = ta.user_id
                       INNER JOIN teaching_types tt ON tt.id = s.teaching_type_id AND tt.actif = 1
                       WHERE s.status = 1";
        $subjectParams = [$activeYearId];
        if ($classId > 0) {
            $subjectSql .= " AND sc.class_id = ?";
            $subjectParams[] = $classId;
        }
        if ($activeYearId > 0) {
            $subjectSql .= " AND sc.academic_year_id = ?";
            $subjectParams[] = $activeYearId;
        }
        if ($typeId > 0) {
            $subjectSql .= " AND s.teaching_type_id = ?";
            $subjectParams[] = $typeId;
        }
        $subjectSql .= " ORDER BY s.nom ASC";
        $subjectStmt = $this->db->prepare($subjectSql);
        $subjectStmt->execute($subjectParams);
        echo json_encode(['success' => true, 'teachers' => $teachers, 'subjects' => $subjectStmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    public function apiTeacherAssignmentImpact(): void
    {
        header('Content-Type: application/json');
        if (!$this->canManageAssignments()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Non autorisé.']);
            exit;
        }
        $teacherId = (int) ($_POST['teacher_id'] ?? 0);
        $classId = (int) ($_POST['class_id'] ?? 0);
        $subjectIds = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['subject_ids'] ?? [])))));
        if ($teacherId <= 0 || $classId <= 0 || !$subjectIds) {
            echo json_encode(['success' => false, 'error' => 'Sélectionnez un enseignant, une classe et au moins une matière.']);
            exit;
        }
        $activeYearId = $this->academicYearService->getActiveYearId();
        $placeholders = implode(',', array_fill(0, count($subjectIds), '?'));
        $params = array_merge([$classId, $activeYearId], $subjectIds);
        $stmt = $this->db->prepare("SELECT s.id, s.nom, ta.user_id, CONCAT(COALESCE(u.prenom, ''), ' ', COALESCE(u.nom, '')) AS teacher_name
                                    FROM subjects s
                                    INNER JOIN teacher_assignments ta ON ta.subject_id = s.id AND ta.class_id = ?
                                    LEFT JOIN users u ON u.id = ta.user_id
                                    WHERE (ta.academic_year_id = ? OR ta.academic_year_id IS NULL) AND s.id IN ($placeholders) AND ta.user_id <> ?");
        $stmt->execute(array_merge($params, [$teacherId]));
        $conflicts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'conflicts' => $conflicts, 'has_conflicts' => !empty($conflicts),
            'message' => empty($conflicts) ? 'Aucune affectation existante ne sera remplacée.' : count($conflicts) . ' matière(s) sont déjà affectées et seront retirées de leur enseignant actuel.']);
        exit;
    }

    public function apiAssignTeacherSubjects(): void
    {
        header('Content-Type: application/json');
        if (!$this->canManageAssignments() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Opération non autorisée.']);
            exit;
        }
        $teacherId = (int) ($_POST['teacher_id'] ?? 0);
        $classId = (int) ($_POST['class_id'] ?? 0);
        $subjectIds = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['subject_ids'] ?? [])))));
        if ($teacherId <= 0 || $classId <= 0 || !$subjectIds) {
            echo json_encode(['success' => false, 'error' => 'Données d’affectation incomplètes.']);
            exit;
        }
        $activeYearId = $this->academicYearService->getActiveYearId();
        try {
            $this->db->beginTransaction();
            $placeholders = implode(',', array_fill(0, count($subjectIds), '?'));
            $valid = $this->db->prepare("SELECT COUNT(*) FROM subject_classes WHERE class_id = ? AND academic_year_id = ? AND subject_id IN ($placeholders)");
            $valid->execute(array_merge([$classId, $activeYearId], $subjectIds));
            if ((int) $valid->fetchColumn() !== count($subjectIds)) {
                throw new \RuntimeException('Une ou plusieurs matières ne sont pas affectées à cette classe.');
            }
            $delete = $this->db->prepare("DELETE FROM teacher_assignments WHERE class_id = ? AND academic_year_id = ? AND subject_id IN ($placeholders)");
            $delete->execute(array_merge([$classId, $activeYearId], $subjectIds));
            $insert = $this->db->prepare("INSERT INTO teacher_assignments (user_id, subject_id, class_id, academic_year_id) VALUES (?, ?, ?, ?)");
            foreach ($subjectIds as $subjectId) $insert->execute([$teacherId, $subjectId, $classId, $activeYearId]);
            $this->db->commit();
            echo json_encode(['success' => true, 'assigned' => count($subjectIds)]);
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * API: Récupère les compétences pour une matière donnée (AJAX)
     */
    public function apiBySubject()
    {
        header('Content-Type: application/json');
        
        $subjectId = (int) ($_GET['subject_id'] ?? 0);
        $classId = (int) ($_GET['class_id'] ?? 0);
        
        if ($subjectId <= 0) {
            echo json_encode(['error' => 'subject_id requis']);
            exit;
        }

        if ($classId > 0) {
            $activeYearId = $this->academicYearService->getActiveYearId();
            $sql = "SELECT COUNT(*) FROM subject_classes WHERE subject_id = ? AND class_id = ?";
            $params = [$subjectId, $classId];
            if ($activeYearId > 0) {
                $sql .= " AND academic_year_id = ?";
                $params[] = $activeYearId;
            }
            $classSubjectCheck = $this->db->prepare($sql);
            $classSubjectCheck->execute($params);
            if ((int) $classSubjectCheck->fetchColumn() === 0) {
                echo json_encode(['error' => 'Cette matière n’est pas affectée à la classe sélectionnée.']);
                exit;
            }
        }

        // Vérifier les permissions
        $userRole = Session::get('user_role');
        $userId = (int) Session::get('user_id');

        if (!in_array($userRole, ['admin', 'superadmin'], true)) {
            // Vérifier que l'enseignant est affecté à cette matière/classe
            $check = $this->db->prepare("SELECT COUNT(*) FROM teacher_assignments 
                                         WHERE user_id = ? AND subject_id = ?" . 
                                         ($classId > 0 ? " AND class_id = ?" : ""));
            $params = [$userId, $subjectId];
            if ($classId > 0) {
                $params[] = $classId;
            }
            $check->execute($params);
            
            if ($check->fetchColumn() == 0) {
                echo json_encode(['error' => 'Non autorisé']);
                exit;
            }
        }

        // Récupérer les compétences de la matière
        $stmt = $this->db->prepare("SELECT id, libelle, description 
                                     FROM competencies 
                                     WHERE subject_id = ? 
                                     ORDER BY position, libelle");
        $stmt->execute([$subjectId]);
        $competencies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'competencies' => $competencies]);
        exit;
    }

    /**
     * API: Crée une nouvelle compétence (AJAX)
     */
    public function apiCreate()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Méthode non autorisée']);
            exit;
        }

        $userRole = Session::get('user_role');
        $userId = (int) Session::get('user_id');

        // Vérifier les permissions
        if (!in_array($userRole, ['admin', 'superadmin'], true)) {
            // Les enseignants peuvent créer des compétences pour leurs matières
            $subjectId = (int) ($_POST['subject_id'] ?? 0);
            $classId = (int) ($_POST['class_id'] ?? 0);
            
            if ($subjectId <= 0) {
                echo json_encode(['error' => 'subject_id requis']);
                exit;
            }

            $check = $this->db->prepare("SELECT COUNT(*) FROM teacher_assignments 
                                         WHERE user_id = ? AND subject_id = ?" . 
                                         ($classId > 0 ? " AND class_id = ?" : ""));
            $params = [$userId, $subjectId];
            if ($classId > 0) {
                $params[] = $classId;
            }
            $check->execute($params);
            
            if ($check->fetchColumn() == 0) {
                echo json_encode(['error' => 'Non autorisé à créer des compétences pour cette matière']);
                exit;
            }
        }

        $libelle = trim($_POST['libelle'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $subjectId = (int) ($_POST['subject_id'] ?? 0);
        $position = (int) ($_POST['position'] ?? 0);

        if (empty($libelle)) {
            echo json_encode(['error' => 'Le libellé est obligatoire']);
            exit;
        }

        try {
            $this->db->beginTransaction();

            // Récupérer la position maximale si non spécifiée
            if ($position <= 0 && $subjectId > 0) {
                $maxPos = $this->db->prepare("SELECT MAX(position) FROM competencies WHERE subject_id = ?");
                $maxPos->execute([$subjectId]);
                $position = ((int) $maxPos->fetchColumn()) + 1;
            }

            $stmt = $this->db->prepare("INSERT INTO competencies (subject_id, libelle, description, position, created_by) 
                                        VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$subjectId > 0 ? $subjectId : null, $libelle, $description ?: null, $position, $userId]);

            $competencyId = $this->db->lastInsertId();
            $this->db->commit();

            echo json_encode([
                'success' => true,
                'competency' => [
                    'id' => $competencyId,
                    'libelle' => $libelle,
                    'description' => $description,
                    'position' => $position
                ]
            ]);
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            echo json_encode(['error' => 'Erreur lors de la création: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * API: Met à jour une compétence (AJAX)
     */
    public function apiUpdate()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Méthode non autorisée']);
            exit;
        }

        $userRole = Session::get('user_role');
        $userId = (int) Session::get('user_id');
        $competencyId = (int) ($_POST['competency_id'] ?? 0);

        if ($competencyId <= 0) {
            echo json_encode(['error' => 'competency_id requis']);
            exit;
        }

        // Vérifier les permissions
        if (!in_array($userRole, ['admin', 'superadmin'], true)) {
            // Vérifier que l'enseignant peut modifier cette compétence
            $check = $this->db->prepare("SELECT c.subject_id, c.created_by 
                                         FROM competencies c
                                         INNER JOIN teacher_assignments ta ON c.subject_id = ta.subject_id
                                         WHERE c.id = ? AND ta.user_id = ?");
            $check->execute([$competencyId, $userId]);
            
            if ($check->rowCount() == 0) {
                echo json_encode(['error' => 'Non autorisé à modifier cette compétence']);
                exit;
            }
        }

        $libelle = trim($_POST['libelle'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $position = (int) ($_POST['position'] ?? 0);

        if (empty($libelle)) {
            echo json_encode(['error' => 'Le libellé est obligatoire']);
            exit;
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("UPDATE competencies 
                                        SET libelle = ?, description = ?, position = ? 
                                        WHERE id = ?");
            $stmt->execute([$libelle, $description ?: null, $position, $competencyId]);

            $this->db->commit();

            echo json_encode(['success' => true]);
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            echo json_encode(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * API: Supprime une compétence (AJAX)
     */
    public function apiDelete()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Méthode non autorisée']);
            exit;
        }

        $userRole = Session::get('user_role');
        $userId = (int) Session::get('user_id');
        $competencyId = (int) ($_POST['competency_id'] ?? 0);

        if ($competencyId <= 0) {
            echo json_encode(['error' => 'competency_id requis']);
            exit;
        }

        // Vérifier si la compétence est utilisée dans des évaluations
        $checkUsage = $this->db->prepare("SELECT COUNT(*) FROM evaluation_competencies WHERE competency_id = ?");
        $checkUsage->execute([$competencyId]);
        
        if ($checkUsage->fetchColumn() > 0) {
            echo json_encode(['error' => 'Cette compétence est utilisée dans des évaluations et ne peut pas être supprimée']);
            exit;
        }

        // Vérifier les permissions
        if (!in_array($userRole, ['admin', 'superadmin'], true)) {
            // Vérifier que l'enseignant peut supprimer cette compétence
            $check = $this->db->prepare("SELECT c.subject_id 
                                         FROM competencies c
                                         INNER JOIN teacher_assignments ta ON c.subject_id = ta.subject_id
                                         WHERE c.id = ? AND ta.user_id = ?");
            $check->execute([$competencyId, $userId]);
            
            if ($check->rowCount() == 0) {
                echo json_encode(['error' => 'Non autorisé à supprimer cette compétence']);
                exit;
            }
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("DELETE FROM competencies WHERE id = ?");
            $stmt->execute([$competencyId]);

            $this->db->commit();

            echo json_encode(['success' => true]);
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            echo json_encode(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * API: Associe des compétences à une évaluation (AJAX)
     * Maximum 2 compétences par évaluation
     */
    public function apiLinkToEvaluation()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Méthode non autorisée']);
            exit;
        }

        $classId = (int) ($_POST['class_id'] ?? 0);
        $subjectId = (int) ($_POST['subject_id'] ?? 0);
        $period = trim($_POST['periode'] ?? '');
        $competencyIds = $_POST['competency_ids'] ?? [];

        if ($classId <= 0 || $subjectId <= 0 || empty($period)) {
            echo json_encode(['error' => 'Paramètres manquants']);
            exit;
        }

        if (empty($competencyIds)) {
            echo json_encode(['error' => 'Veuillez sélectionner au moins une compétence pour cette évaluation']);
            exit;
        }

        // Limiter à 2 compétences maximum
        if (count($competencyIds) > 2) {
            echo json_encode(['error' => 'Maximum 2 compétences par évaluation']);
            exit;
        }

        // Vérifier les permissions
        $userRole = Session::get('user_role');
        $userId = (int) Session::get('user_id');

        if (!in_array($userRole, ['admin', 'superadmin'], true)) {
            $check = $this->db->prepare("SELECT COUNT(*) FROM teacher_assignments 
                                         WHERE user_id = ? AND subject_id = ? AND class_id = ?");
            $check->execute([$userId, $subjectId, $classId]);
            
            if ($check->fetchColumn() == 0) {
                echo json_encode(['error' => 'Non autorisé']);
                exit;
            }
        }

        try {
            $activeYear = $this->academicYearService->getActiveYear();
            if (!$activeYear) {
                echo json_encode(['error' => 'Aucune année académique active']);
                exit;
            }

            // Récupérer l'ID de la séquence
            $seqStmt = $this->db->prepare("SELECT id FROM sequences WHERE label = ? LIMIT 1");
            $seqStmt->execute([$period]);
            $sequenceId = $seqStmt->fetchColumn();

            $this->db->beginTransaction();

            // Supprimer les associations existantes pour cette évaluation
            $deleteStmt = $this->db->prepare("DELETE FROM evaluation_competencies 
                                              WHERE class_id = ? AND subject_id = ? 
                                              AND academic_year_id = ? AND periode = ?");
            $deleteStmt->execute([$classId, $subjectId, $activeYear['id'], $period]);

            // Ajouter les nouvelles associations
            $insertStmt = $this->db->prepare("INSERT INTO evaluation_competencies 
                                                (class_id, subject_id, academic_year_id, sequence_id, periode, competency_id, position)
                                                VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($competencyIds as $index => $compId) {
                $insertStmt->execute([
                    $classId,
                    $subjectId,
                    $activeYear['id'],
                    $sequenceId,
                    $period,
                    (int) $compId,
                    $index + 1
                ]);
            }

            $this->db->commit();

            echo json_encode(['success' => true]);
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            echo json_encode(['error' => 'Erreur lors de l\'association: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * API: Récupère les compétences associées à une évaluation (AJAX)
     */
    public function apiGetEvaluationCompetencies()
    {
        header('Content-Type: application/json');
        
        $classId = (int) ($_GET['class_id'] ?? 0);
        $subjectId = (int) ($_GET['subject_id'] ?? 0);
        $period = trim($_GET['periode'] ?? '');

        if ($classId <= 0 || $subjectId <= 0 || empty($period)) {
            echo json_encode(['error' => 'Paramètres manquants']);
            exit;
        }

        try {
            $activeYear = $this->academicYearService->getActiveYear();
            if (!$activeYear) {
                echo json_encode(['error' => 'Aucune année académique active']);
                exit;
            }

            $stmt = $this->db->prepare("SELECT ec.competency_id, c.libelle, c.description, ec.position
                                         FROM evaluation_competencies ec
                                         INNER JOIN competencies c ON ec.competency_id = c.id
                                         WHERE ec.class_id = ? AND ec.subject_id = ? 
                                         AND ec.academic_year_id = ? AND ec.periode = ?
                                         ORDER BY ec.position");
            $stmt->execute([$classId, $subjectId, $activeYear['id'], $period]);
            $competencies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'competencies' => $competencies]);
        } catch (\PDOException $e) {
            echo json_encode(['error' => 'Erreur: ' . $e->getMessage()]);
        }
        exit;
    }
}
