<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Core\PermissionManager;
use PDO;

/**
 * DepartmentController
 * 
 * Gère le registre des départements pédagogiques.
 * Accès réservé aux utilisateurs ayant la permission 'manage_departments'.
 */
class DepartmentController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        
        if (!Session::isLogged()) {
            header("Location: /login");
            exit;
        }
    }

    /**
     * Liste les départements avec support des filtres et recherche instantanée.
     */
    public function index()
    {
        PermissionManager::requirePermission('manage_departments');

        $q = trim((string) ($_GET['q'] ?? ''));
        $teaching_type_id = !empty($_GET['teaching_type_id']) ? (int) $_GET['teaching_type_id'] : null;

        $conditions = [];
        $params = [];

        // Ne sélectionner que les départements rattachés à un type d'enseignement actif (ou sans type)
        $conditions[] = "(t.actif = 1 OR d.teaching_type_id IS NULL)";

        if ($q !== '') {
            $conditions[] = "(d.nom LIKE ? OR d.code LIKE ?)";
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }

        if ($teaching_type_id !== null) {
            $conditions[] = "d.teaching_type_id = ?";
            $params[] = $teaching_type_id;
        }

        $whereClause = !empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "";
        $query = "SELECT d.*, t.nom as teaching_type_nom FROM departments d LEFT JOIN teaching_types t ON d.teaching_type_id = t.id" . $whereClause . " ORDER BY d.nom ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $teachingTypes = $this->db->query("SELECT * FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        $filters = [
            'q' => $q,
            'teaching_type_id' => $teaching_type_id
        ];

        include __DIR__ . '/../Views/departments/index.php';
    }

    /**
     * Formulaire de création.
     */
    public function create()
    {
        PermissionManager::requirePermission('manage_departments');

        $teachingTypes = $this->db->query("SELECT * FROM teaching_types ORDER BY position ASC")->fetchAll(PDO::FETCH_ASSOC);
        include __DIR__ . '/../Views/departments/create.php';
    }

    /**
     * Enregistre un nouveau département.
     */
    public function store()
    {
        PermissionManager::requirePermission('manage_departments');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim((string)($_POST['nom'] ?? ''));
            $code = strtoupper(trim((string)($_POST['code'] ?? '')));
            $teaching_type_id = !empty($_POST['teaching_type_id']) ? (int) $_POST['teaching_type_id'] : null;

            if ($nom === '' || $code === '' || !$teaching_type_id) {
                $error = __('required');
                $teachingTypes = $this->db->query("SELECT * FROM teaching_types ORDER BY position ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/departments/create.php';
                return;
            }

            try {
                $stmt = $this->db->prepare("INSERT INTO departments (nom, code, status, teaching_type_id) VALUES (?, ?, 1, ?)");
                $stmt->execute([$nom, $code, $teaching_type_id]);
                
                Session::setFlash('success', __('created_success'));
                header("Location: /departments");
                exit;
            } catch (\PDOException $e) {
                $error = __('error_generic');
                $teachingTypes = $this->db->query("SELECT * FROM teaching_types ORDER BY position ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/departments/create.php';
            }
        }
    }

    /**
     * Formulaire d'édition.
     */
    public function edit($id)
    {
        PermissionManager::requirePermission('manage_departments');

        $stmt = $this->db->prepare("SELECT * FROM departments WHERE id = ?");
        $stmt->execute([(int)$id]);
        $department = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$department) {
            header("Location: /departments");
            exit;
        }

        $teachingTypes = $this->db->query("SELECT * FROM teaching_types ORDER BY position ASC")->fetchAll(PDO::FETCH_ASSOC);
        include __DIR__ . '/../Views/departments/edit.php';
    }

    /**
     * Met à jour un département.
     */
    public function update($id)
    {
        PermissionManager::requirePermission('manage_departments');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim((string)($_POST['nom'] ?? ''));
            $code = strtoupper(trim((string)($_POST['code'] ?? '')));
            $teaching_type_id = !empty($_POST['teaching_type_id']) ? (int) $_POST['teaching_type_id'] : null;

            if ($nom === '' || $code === '' || !$teaching_type_id) {
                $error = __('required');
                $department = ['id' => $id, 'nom' => $nom, 'code' => $code, 'teaching_type_id' => $teaching_type_id];
                $teachingTypes = $this->db->query("SELECT * FROM teaching_types ORDER BY position ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/departments/edit.php';
                return;
            }

            try {
                $stmt = $this->db->prepare("UPDATE departments SET nom = ?, code = ?, teaching_type_id = ? WHERE id = ?");
                $stmt->execute([$nom, $code, $teaching_type_id, (int)$id]);
                
                Session::setFlash('success', __('updated_success'));
                header("Location: /departments");
                exit;
            } catch (\PDOException $e) {
                $error = __('error_generic');
                $department = ['id' => $id, 'nom' => $nom, 'code' => $code, 'teaching_type_id' => $teaching_type_id];
                $teachingTypes = $this->db->query("SELECT * FROM teaching_types ORDER BY position ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/departments/edit.php';
            }
        }
    }

    /**
     * Active/Désactive un département.
     */
    public function toggleStatus($id)
    {
        PermissionManager::requirePermission('manage_departments');

        $stmt = $this->db->prepare("UPDATE departments SET status = NOT status WHERE id = ?");
        $stmt->execute([(int)$id]);
        
        Session::setFlash('success', __('updated_success'));
        header("Location: /departments");
        exit;
    }

    /**
     * La suppression définitive d'un département est désactivée.
     * La désactivation est l'unique moyen de retirer un département de l'utilisation active.
     */
    public function delete($id)
    {
        PermissionManager::requirePermission('manage_departments');

        Session::setFlash('error', "La suppression définitive des départements est désactivée. Veuillez utiliser la désactivation pour suspendre son utilisation.");
        header("Location: /departments");
        exit;
    }
}
