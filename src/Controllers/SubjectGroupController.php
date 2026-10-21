<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Core\PermissionManager;
use PDO;

/**
 * SubjectGroupController
 * 
 * Gestion des groupes de modules / matières.
 * Accès réservé aux administrateurs (manage_subjects ou manage_cycles).
 */
class SubjectGroupController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        if (!Session::isLogged()) {
            header("Location: /login");
            exit;
        }
        PermissionManager::requirePermission('manage_subjects');
    }

    /**
     * Liste tous les groupes de modules
     */
    public function index()
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        $teachingTypes = $this->db->query("SELECT id, nom, code FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $teaching_type_id = array_key_exists('teaching_type_id', $_GET)
            ? (!empty($_GET['teaching_type_id']) ? (int) $_GET['teaching_type_id'] : null)
            : (!empty($teachingTypes) ? (int) $teachingTypes[0]['id'] : null);

        $sectionsTableExists = (bool) $this->db->query("SHOW TABLES LIKE 'sections'")->fetchColumn();
        $sections = $sectionsTableExists
            ? $this->db->query("SELECT id, nom FROM sections WHERE status = 1 ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC)
            : [];
        $sectionColumn = $this->db->query("SHOW COLUMNS FROM subject_groups LIKE 'section_id'")->fetchColumn();
        $hasSectionCol = $sectionsTableExists && !empty($sectionColumn);
        $section_id = $hasSectionCol && !empty($_GET['section_id']) ? (int) $_GET['section_id'] : null;

        $hasAssignmentTable = (bool) $this->db->query("SHOW TABLES LIKE 'subject_group_assignments'")->fetchColumn();
        $additionalAssignedSubjects = $hasAssignmentTable
            ? " + (SELECT COUNT(*) FROM subject_group_assignments sga
                   WHERE sga.subject_group_id = sg.id
                     AND NOT EXISTS (
                         SELECT 1 FROM subjects s
                         WHERE s.id = sga.subject_id AND s.subject_group_id = sg.id
                     ))"
            : '';

        $teachingFormsTableExists = (bool) $this->db->query("SHOW TABLES LIKE 'teaching_forms'")->fetchColumn();
        $teachingFormColumn = $this->db->query("SHOW COLUMNS FROM subject_groups LIKE 'teaching_form_id'")->fetchColumn();
        $hasTeachingFormCol = $teachingFormsTableExists && !empty($teachingFormColumn);
        $positionColumn = $this->db->query("SHOW COLUMNS FROM subject_groups LIKE 'position'")->fetchColumn();
        $hasPositionCol = !empty($positionColumn);

        $params = [];
        $selectTeachingForm = $hasTeachingFormCol
            ? 'tf.nom AS teaching_form_nom, tf.id AS teaching_form_id'
            : 'NULL AS teaching_form_nom, NULL AS teaching_form_id';
        $selectSection = $hasSectionCol ? 'sec.nom AS section_nom' : 'NULL AS section_nom';
        $joins = [
            'LEFT JOIN teaching_types tt ON sg.teaching_type_id = tt.id',
        ];
        $conditions = ['tt.actif = 1'];

        if ($hasTeachingFormCol) {
            $joins[] = 'LEFT JOIN teaching_forms tf ON sg.teaching_form_id = tf.id';
            $conditions[] = '(tf.status = 1 OR sg.teaching_form_id IS NULL)';
        }
        if ($hasSectionCol) {
            $joins[] = 'LEFT JOIN sections sec ON sec.id = sg.section_id';
            $conditions[] = '(sec.status = 1 OR sg.section_id IS NULL)';
        }

        $sql = "SELECT sg.*, {$selectTeachingForm}, tt.nom AS teaching_type_nom, tt.code AS teaching_type_code, {$selectSection},
                       ((SELECT COUNT(*) FROM subjects s WHERE s.subject_group_id = sg.id){$additionalAssignedSubjects}) AS subjects_count
                FROM subject_groups sg
                " . implode("\n                ", $joins) . "
                WHERE " . implode(' AND ', $conditions);

        if ($q !== '') {
            $sql .= " AND LOWER(sg.libelle) LIKE ?";
            $params[] = '%' . strtolower($q) . '%';
        }
        if ($teaching_type_id) {
            $sql .= " AND sg.teaching_type_id = ?";
            $params[] = $teaching_type_id;
        }
        if ($section_id !== null) {
            $sql .= " AND sg.section_id = ?";
            $params[] = $section_id;
        }

        $orderBy = [];
        if ($hasSectionCol) {
            $orderBy[] = 'sg.section_id ASC';
        }
        if ($hasTeachingFormCol) {
            $orderBy[] = 'sg.teaching_form_id IS NULL ASC';
            $orderBy[] = 'sg.teaching_form_id ASC';
        } else {
            $orderBy[] = 'sg.teaching_type_id ASC';
        }
        if ($hasPositionCol) {
            $orderBy[] = 'sg.position ASC';
        }
        $orderBy[] = 'sg.id ASC';
        $sql .= ' ORDER BY ' . implode(', ', $orderBy);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Formes d'enseignement actives pour la modale (si la table existe)
        if ($teachingFormsTableExists) {
            $teachingForms = $this->db->query("SELECT id, nom, teaching_type_id FROM teaching_forms WHERE status = 1 ORDER BY teaching_type_id ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $teachingForms = [];
        }

        // Récupérer les classes utilisant chaque groupe pour afficher les formes d'enseignement compatibles
        $groupClasses = [];
        $classTeachingFormColumn = $this->db->query("SHOW COLUMNS FROM classes LIKE 'teaching_form_id'")->fetchColumn();
        $hasClassTeachingFormCol = $teachingFormsTableExists && !empty($classTeachingFormColumn);
        if (!empty($groups)) {
            $groupIds = array_column($groups, 'id');
            if (!empty($groupIds)) {
                $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
                $classTeachingFormSelect = $hasClassTeachingFormCol
                    ? 'c.teaching_form_id, tf.nom AS teaching_form_nom'
                    : 'NULL AS teaching_form_id, NULL AS teaching_form_nom';
                $classTeachingFormJoin = $hasClassTeachingFormCol
                    ? 'LEFT JOIN teaching_forms tf ON tf.id = c.teaching_form_id'
                    : '';
                $classQuery = $this->db->prepare("
                    SELECT DISTINCT s.subject_group_id, c.id as class_id, c.nom as class_nom, {$classTeachingFormSelect}
                    FROM subjects s
                    JOIN subject_classes sc ON sc.subject_id = s.id
                    JOIN classes c ON c.id = sc.class_id
                    {$classTeachingFormJoin}
                    WHERE s.subject_group_id IN ({$placeholders})
                    AND c.status = 1
                    ORDER BY s.subject_group_id, c.nom
                ");
                $classQuery->execute($groupIds);
                $classResults = $classQuery->fetchAll(PDO::FETCH_ASSOC);

                foreach ($classResults as $cr) {
                    $groupId = (int) $cr['subject_group_id'];
                    if (!isset($groupClasses[$groupId])) {
                        $groupClasses[$groupId] = [];
                    }
                    $groupClasses[$groupId][] = [
                        'class_id' => (int) $cr['class_id'],
                        'class_nom' => $cr['class_nom'],
                        'teaching_form_id' => $cr['teaching_form_id'],
                        'teaching_form_nom' => $cr['teaching_form_nom']
                    ];
                }
            }
        }

        $filters = ['q' => $q, 'teaching_type_id' => $teaching_type_id, 'section_id' => $section_id];

        include __DIR__ . '/../Views/subject_groups/index.php';
    }

    /**
     * Crée un groupe de modules
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', __('session_expired_retry') ?? 'Session expirée ou requête invalide.');
                header("Location: /subject-groups");
                exit;
            }

            $libelle = trim($_POST['libelle'] ?? '');
            $teaching_form_id = !empty($_POST['teaching_form_id']) ? (int) $_POST['teaching_form_id'] : null;
            $section_id = !empty($_POST['section_id']) ? (int) $_POST['section_id'] : null;
            $position = filter_var($_POST['position'] ?? null, FILTER_VALIDATE_INT);

            // Vérifier si la colonne teaching_form_id est présente
            $colCheck = $this->db->query("SHOW COLUMNS FROM subject_groups LIKE 'teaching_form_id'")->fetchColumn();
            $hasTeachingFormCol = !empty($colCheck);

            // Valider les champs: la forme vient en addition du type
            $teaching_type_id = !empty($_POST['teaching_type_id']) ? (int) $_POST['teaching_type_id'] : null;

            if ($hasTeachingFormCol) {
                // Exiger à la fois le type et la forme (forme obligatoire selon règle métier)
                if (empty($libelle) || empty($teaching_type_id) || empty($teaching_form_id) || empty($section_id) || $position === false || $position < 1) {
                    Session::setFlash('error', __('fill_required_fields') ?? 'Veuillez remplir les champs obligatoires.');
                    header("Location: /subject-groups");
                    exit;
                }

                // Vérifier que le teaching_type est actif
                $ttStmt = $this->db->prepare("SELECT id FROM teaching_types WHERE id = ? AND actif = 1 LIMIT 1");
                $ttStmt->execute([$teaching_type_id]);
                if (!$ttStmt->fetchColumn()) {
                    Session::setFlash('error', __('invalid_teaching_type') ?? 'Type d\'enseignement invalide.');
                    header("Location: /subject-groups");
                    exit;
                }

                // Vérifier que la forme existe et est active
                $tfStmt = $this->db->prepare("SELECT id FROM teaching_forms WHERE id = ? AND teaching_type_id = ? AND status = 1 LIMIT 1");
                $tfStmt->execute([$teaching_form_id, $teaching_type_id]);
                if (!$tfStmt->fetchColumn()) {
                    Session::setFlash('error', __('invalid_teaching_form') ?? 'Forme d\'enseignement invalide.');
                    header("Location: /subject-groups");
                    exit;
                }

                $sectionStmt = $this->db->prepare("SELECT id FROM sections WHERE id = ? AND status = 1 LIMIT 1");
                $sectionStmt->execute([$section_id]);
                if (!$sectionStmt->fetchColumn()) {
                    Session::setFlash('error', __('invalid_active_section'));
                    header("Location: /subject-groups");
                    exit;
                }
            } else {
                // Avant migration : exiger au minimum le libellé et le type si fourni
                if (empty($libelle)) {
                    Session::setFlash('error', __('fill_required_fields') ?? 'Veuillez remplir les champs obligatoires.');
                    header("Location: /subject-groups");
                    exit;
                }
            }

            // Insérer : si la colonne teaching_form_id existe, l'inclure, sinon garder l'insertion legacy
            if ($hasTeachingFormCol) {
                $this->db->beginTransaction();
                $lockSection = $this->db->prepare('SELECT id FROM sections WHERE id = ? AND status = 1 FOR UPDATE');
                $lockSection->execute([$section_id]);
                if (!$lockSection->fetchColumn()) {
                    $this->db->rollBack();
                    Session::setFlash('error', __('invalid_active_section'));
                    header("Location: /subject-groups");
                    exit;
                }
                if ($this->groupNameExists($libelle, $teaching_type_id, $teaching_form_id, $section_id)) {
                    $this->db->rollBack();
                    Session::setFlash('error', 'Un groupe portant ce nom existe déjà pour cette section et cette forme d’enseignement.');
                    header("Location: /subject-groups");
                    exit;
                }
                $positionStmt = $this->db->prepare("SELECT 1 FROM subject_groups WHERE section_id = ? AND teaching_form_id = ? AND position = ? LIMIT 1");
                $positionStmt->execute([$section_id, $teaching_form_id, $position]);
                if ($positionStmt->fetchColumn()) {
                    $this->db->rollBack();
                    Session::setFlash('error', __('subject_group_position_taken') ?? 'Cette position est déjà utilisée pour cette forme d’enseignement.');
                    header("Location: /subject-groups");
                    exit;
                }
                $stmt = $this->db->prepare("INSERT INTO subject_groups (libelle, teaching_type_id, teaching_form_id, section_id, position, status) VALUES (?, ?, ?, ?, ?, 1)");
                $stmt->execute([$libelle, $teaching_type_id, $teaching_form_id, $section_id, $position]);
                $this->db->commit();
            } else {
                $stmt = $this->db->prepare("INSERT INTO subject_groups (libelle, teaching_type_id, status) VALUES (?, ?, 1)");
                $stmt->execute([$libelle, $teaching_type_id]);
            }

            Session::setFlash('success', __('created_success'));
            header("Location: /subject-groups");
            exit;
        }
    }

    /**
     * Met à jour un groupe de modules
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', __('session_expired_retry') ?? 'Session expirée ou requête invalide.');
                header("Location: /subject-groups");
                exit;
            }

            $id = (int) $id;
            $libelle = trim($_POST['libelle'] ?? '');
            $teaching_form_id = !empty($_POST['teaching_form_id']) ? (int) $_POST['teaching_form_id'] : null;
            $section_id = !empty($_POST['section_id']) ? (int) $_POST['section_id'] : null;
            $position = filter_var($_POST['position'] ?? null, FILTER_VALIDATE_INT);
            $status = isset($_POST['status']) ? 1 : 0;

            // Vérifier si la colonne teaching_form_id est présente
            $colCheck = $this->db->query("SHOW COLUMNS FROM subject_groups LIKE 'teaching_form_id'")->fetchColumn();
            $hasTeachingFormCol = !empty($colCheck);

            // Récupérer les valeurs actuelles pour permettre la modification partielle
            $currentGroupStmt = $this->db->prepare("SELECT libelle, teaching_type_id, teaching_form_id, section_id, position FROM subject_groups WHERE id = ? LIMIT 1");
            $currentGroupStmt->execute([$id]);
            $currentGroup = $currentGroupStmt->fetch(PDO::FETCH_ASSOC);

            if (!$currentGroup) {
                Session::setFlash('error', __('invalid_id') ?? 'Groupe introuvable.');
                header("Location: /subject-groups");
                exit;
            }

            // Valider les champs: la forme vient en addition du type
            $teaching_type_id = !empty($_POST['teaching_type_id']) ? (int) $_POST['teaching_type_id'] : null;

            if ($hasTeachingFormCol) {
                // Si des champs ne sont pas fournis, utiliser les valeurs existantes
                $libelleProvided = !empty($libelle);
                $teaching_type_idProvided = isset($_POST['teaching_type_id']) && $_POST['teaching_type_id'] !== '';
                $teaching_form_idProvided = isset($_POST['teaching_form_id']) && $_POST['teaching_form_id'] !== '';
                $section_idProvided = isset($_POST['section_id']) && $_POST['section_id'] !== '';
                $positionProvided = $position !== false && $position >= 1;

                if (!$libelleProvided) {
                    $libelle = $currentGroup['libelle'];
                }
                if (!$teaching_type_idProvided) {
                    $teaching_type_id = $currentGroup['teaching_type_id'] !== null ? (int) $currentGroup['teaching_type_id'] : null;
                }
                if (!$teaching_form_idProvided) {
                    $teaching_form_id = $currentGroup['teaching_form_id'] !== null ? (int) $currentGroup['teaching_form_id'] : null;
                }
                if (!$section_idProvided) {
                    $section_id = $currentGroup['section_id'] !== null ? (int) $currentGroup['section_id'] : null;
                }
                if (!$positionProvided) {
                    $position = (int) $currentGroup['position'];
                }

                // Valider les champs qui ont été modifiés
                if ($teaching_type_idProvided) {
                    $ttStmt = $this->db->prepare("SELECT id FROM teaching_types WHERE id = ? AND actif = 1 LIMIT 1");
                    $ttStmt->execute([$teaching_type_id]);
                    if (!$ttStmt->fetchColumn()) {
                        Session::setFlash('error', __('invalid_teaching_type') ?? 'Type d\'enseignement invalide.');
                        header("Location: /subject-groups");
                        exit;
                    }
                }

                if ($teaching_form_idProvided) {
                    $tfStmt = $this->db->prepare("SELECT id FROM teaching_forms WHERE id = ? AND teaching_type_id = ? AND status = 1 LIMIT 1");
                    $tfStmt->execute([$teaching_form_id, $teaching_type_id]);
                    if (!$tfStmt->fetchColumn()) {
                        Session::setFlash('error', __('invalid_teaching_form') ?? 'Forme d\'enseignement invalide.');
                        header("Location: /subject-groups");
                        exit;
                    }
                }

                if ($section_idProvided) {
                    $sectionStmt = $this->db->prepare("SELECT id FROM sections WHERE id = ? AND status = 1 LIMIT 1");
                    $sectionStmt->execute([$section_id]);
                    if (!$sectionStmt->fetchColumn()) {
                        Session::setFlash('error', __('invalid_active_section'));
                        header("Location: /subject-groups");
                        exit;
                    }
                }
            } else {
                if ($id <= 0 || empty($libelle)) {
                    Session::setFlash('error', __('fill_required_fields') ?? 'Veuillez remplir les champs obligatoires.');
                    header("Location: /subject-groups");
                    exit;
                }
            }

            // Mettre à jour selon la présence de la colonne teaching_form_id
            if ($hasTeachingFormCol) {
                $this->db->beginTransaction();
                $lockSection = $this->db->prepare('SELECT id FROM sections WHERE id = ? AND status = 1 FOR UPDATE');
                $lockSection->execute([$section_id]);
                if (!$lockSection->fetchColumn()) {
                    $this->db->rollBack();
                    Session::setFlash('error', __('invalid_active_section'));
                    header("Location: /subject-groups");
                    exit;
                }

                if ($this->groupNameExists($libelle, $teaching_type_id, $teaching_form_id, $section_id, $id)) {
                    $this->db->rollBack();
                    Session::setFlash('error', "Un groupe portant ce nom existe déjà pour cette section et cette forme d'enseignement.");
                    header("Location: /subject-groups");
                    exit;
                }

                $positionStmt = $this->db->prepare("SELECT 1 FROM subject_groups WHERE section_id = ? AND (teaching_form_id = ? OR (teaching_form_id IS NULL AND ? IS NULL)) AND position = ? AND id <> ? LIMIT 1");
                $positionStmt->execute([$section_id, $teaching_form_id, $teaching_form_id, $position, $id]);
                if ($positionStmt->fetchColumn()) {
                    $this->db->rollBack();
                    Session::setFlash('error', __('subject_group_position_taken') ?? 'Cette position est déjà utilisée pour cette forme d’enseignement.');
                    header("Location: /subject-groups");
                    exit;
                }
                $stmt = $this->db->prepare("UPDATE subject_groups SET libelle = ?, teaching_type_id = ?, teaching_form_id = ?, section_id = ?, position = ?, status = ? WHERE id = ?");
                $stmt->execute([$libelle, $teaching_type_id, $teaching_form_id, $section_id, $position, $status, $id]);
                $this->db->commit();
            } else {
                $stmt = $this->db->prepare("UPDATE subject_groups SET libelle = ?, teaching_type_id = ?, status = ? WHERE id = ?");
                $stmt->execute([$libelle, $teaching_type_id, $status, $id]);
            }

            Session::setFlash('success', __('updated_success'));
            header("Location: /subject-groups");
            exit;
        }
    }

    /**
     * Active / Désactive un groupe de modules
     */
    public function toggle($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', __('session_expired_retry') ?? 'Session expirée ou requête invalide.');
            header("Location: /subject-groups");
            exit;
        }

        $id = (int) $id;
        if ($id <= 0) {
            Session::setFlash('error', __('invalid_id') ?? 'Identifiant invalide.');
            header("Location: /subject-groups");
            exit;
        }

        $groupStmt = $this->db->prepare('SELECT libelle, teaching_type_id, teaching_form_id, section_id, status FROM subject_groups WHERE id = ? FOR UPDATE');
        $this->db->beginTransaction();
        try {
            $groupStmt->execute([$id]);
            $group = $groupStmt->fetch(PDO::FETCH_ASSOC);
            if (!$group) {
                throw new \RuntimeException('Groupe introuvable.');
            }
            if ((int) $group['status'] === 0 && !empty($group['teaching_form_id']) && $this->groupNameExists(
                (string) $group['libelle'],
                (int) $group['teaching_type_id'],
                (int) $group['teaching_form_id'],
                (int) $group['section_id'],
                $id
            )) {
                throw new \RuntimeException('Un groupe actif porte déjà ce nom pour cette section et cette forme.');
            }
            $stmt = $this->db->prepare('UPDATE subject_groups SET status = 1 - status WHERE id = ?');
            $stmt->execute([$id]);
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('[SubjectGroupController] Group status update failed: ' . $e->getMessage());
            Session::setFlash('error', $e->getMessage());
            header("Location: /subject-groups");
            exit;
        }

        Session::setFlash('success', __('status_updated_success'));
        header("Location: /subject-groups");
        exit;
    }

    /**
     * Supprime un groupe de modules
     */
    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', __('session_expired_retry') ?? 'Session expirée ou requête invalide.');
            header('Location: /subject-groups');
            exit;
        }

        $id = (int) $id;
        if ($id <= 0) {
            Session::setFlash('error', __('invalid_id') ?? 'Identifiant invalide.');
            header("Location: /subject-groups");
            exit;
        }

        // Vérifier s'il y a des matières rattachées
        $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM subjects WHERE subject_group_id = ?");
        $stmtCount->execute([$id]);
        $subjectCount = (int) $stmtCount->fetchColumn();
        $hasAssignmentTable = (bool) $this->db->query("SHOW TABLES LIKE 'subject_group_assignments'")->fetchColumn();
        if ($hasAssignmentTable) {
            $assignmentCountStmt = $this->db->prepare("SELECT COUNT(*) FROM subject_group_assignments WHERE subject_group_id = ?");
            $assignmentCountStmt->execute([$id]);
            $subjectCount = max($subjectCount, (int) $assignmentCountStmt->fetchColumn());
        }
        if ($subjectCount > 0) {
            Session::setFlash('error', __('cannot_delete_group_has_subjects') ?? 'Impossible de supprimer ce groupe car des matières y sont rattachées.');
            header("Location: /subject-groups");
            exit;
        }

        $stmt = $this->db->prepare('UPDATE subject_groups SET status = 0 WHERE id = ? AND status = 1');
        $stmt->execute([$id]);

        Session::setFlash('success', __('deleted_success'));
        header("Location: /subject-groups");
        exit;
    }

    private function groupNameExists(string $name, ?int $teachingTypeId, ?int $teachingFormId, ?int $sectionId, ?int $exceptId = null): bool
    {
        $sql = 'SELECT id FROM subject_groups
                WHERE LOWER(TRIM(libelle)) = LOWER(TRIM(?))
                  AND (teaching_type_id = ? OR (teaching_type_id IS NULL AND ? IS NULL))
                  AND (teaching_form_id = ? OR (teaching_form_id IS NULL AND ? IS NULL))
                  AND (section_id = ? OR (section_id IS NULL AND ? IS NULL))';
        $params = [$name, $teachingTypeId, $teachingTypeId, $teachingFormId, $teachingFormId, $sectionId, $sectionId];
        if ($exceptId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $exceptId;
        }
        $sql .= ' LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() !== false;
    }
}
