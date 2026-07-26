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
        $teaching_type_id = !empty($_GET['teaching_type_id']) ? (int) $_GET['teaching_type_id'] : null;

        $sql = "SELECT sg.*, tt.nom as teaching_type_nom, tt.code as teaching_type_code,
                       (SELECT COUNT(*) FROM subjects s WHERE s.subject_group_id = sg.id) as subjects_count
                FROM subject_groups sg
                LEFT JOIN teaching_types tt ON sg.teaching_type_id = tt.id
                WHERE (tt.actif = 1 OR sg.teaching_type_id IS NULL)";

        $params = [];
        if ($q !== '') {
            $sql .= " AND LOWER(sg.libelle) LIKE ?";
            $params[] = '%' . strtolower($q) . '%';
        }
        if ($teaching_type_id) {
            $sql .= " AND sg.teaching_type_id = ?";
            $params[] = $teaching_type_id;
        }

        $sql .= " ORDER BY sg.libelle ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $teachingTypes = $this->db->query("SELECT id, nom, code FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $filters = ['q' => $q, 'teaching_type_id' => $teaching_type_id];

        include __DIR__ . '/../Views/subject_groups/index.php';
    }

    /**
     * Crée un groupe de modules
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $libelle = trim($_POST['libelle'] ?? '');
            $teaching_type_id = !empty($_POST['teaching_type_id']) ? (int) $_POST['teaching_type_id'] : null;

            if (empty($libelle) || empty($teaching_type_id)) {
                Session::setFlash('error', __('fill_required_fields') ?? 'Veuillez remplir les champs obligatoires.');
                header("Location: /subject-groups");
                exit;
            }

            $stmt = $this->db->prepare("INSERT INTO subject_groups (libelle, teaching_type_id, status) VALUES (?, ?, 1)");
            $stmt->execute([$libelle, $teaching_type_id]);

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
            $id = (int) $id;
            $libelle = trim($_POST['libelle'] ?? '');
            $teaching_type_id = !empty($_POST['teaching_type_id']) ? (int) $_POST['teaching_type_id'] : null;
            $status = isset($_POST['status']) ? 1 : 0;

            if ($id <= 0 || empty($libelle) || empty($teaching_type_id)) {
                Session::setFlash('error', __('fill_required_fields') ?? 'Veuillez remplir les champs obligatoires.');
                header("Location: /subject-groups");
                exit;
            }

            $stmt = $this->db->prepare("UPDATE subject_groups SET libelle = ?, teaching_type_id = ?, status = ? WHERE id = ?");
            $stmt->execute([$libelle, $teaching_type_id, $status, $id]);

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
        $id = (int) $id;
        $stmt = $this->db->prepare("UPDATE subject_groups SET status = NOT status WHERE id = ?");
        $stmt->execute([$id]);

        Session::setFlash('success', __('status_updated_success'));
        header("Location: /subject-groups");
        exit;
    }

    /**
     * Supprime un groupe de modules
     */
    public function delete($id)
    {
        $id = (int) $id;

        // Vérifier s'il y a des matières rattachées
        $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM subjects WHERE subject_group_id = ?");
        $stmtCount->execute([$id]);
        if ((int) $stmtCount->fetchColumn() > 0) {
            Session::setFlash('error', __('cannot_delete_group_has_subjects') ?? 'Impossible de supprimer ce groupe car des matières y sont rattachées.');
            header("Location: /subject-groups");
            exit;
        }

        $stmt = $this->db->prepare("DELETE FROM subject_groups WHERE id = ?");
        $stmt->execute([$id]);

        Session::setFlash('success', __('deleted_success'));
        header("Location: /subject-groups");
        exit;
    }
}
