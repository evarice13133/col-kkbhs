<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use PDO;

/**
 * DepartmentController
 * 
 * Gère le registre des départements pédagogiques.
 * Accès gestion (Create/Update/Status) réservé au SUPERADMIN.
 */
class DepartmentController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        
        // Sécurité de base : Authentification requise
        if (!Session::isLogged()) {
            header("Location: /login");
            exit;
        }
    }

    /**
     * Liste les départements.
     */
    public function index()
    {
        // Seul le superadmin peut voir la liste
        if (Session::get('user_role') !== 'superadmin') {
            header("Location: /");
            exit;
        }

        $query = "SELECT d.*, t.nom as teaching_type_nom FROM departments d LEFT JOIN teaching_types t ON d.teaching_type_id = t.id";
        if (Session::get('user_role') !== 'superadmin') {
            $query .= " WHERE d.status = 1";
        }
        $query .= " ORDER BY d.nom ASC";
        
        $departments = $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
        
        include __DIR__ . '/../Views/departments/index.php';
    }

    /**
     * Formulaire de création (SuperAdmin uniquement).
     */
    public function create()
    {
        if (Session::get('user_role') !== 'superadmin') {
            header("Location: /departments");
            exit;
        }
        $teachingTypes = $this->db->query("SELECT * FROM teaching_types ORDER BY position ASC")->fetchAll(PDO::FETCH_ASSOC);
        include __DIR__ . '/../Views/departments/create.php';
    }

    /**
     * Enregistre un nouveau département.
     */
    public function store()
    {
        if (Session::get('user_role') !== 'superadmin') {
            header("Location: /departments");
            exit;
        }

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
                include __DIR__ . '/../Views/departments/create.php';
            }
        }
    }

    /**
     * Formulaire d'édition (SuperAdmin uniquement).
     */
    public function edit($id)
    {
        if (Session::get('user_role') !== 'superadmin') {
            header("Location: /departments");
            exit;
        }

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
        if (Session::get('user_role') !== 'superadmin') {
            header("Location: /departments");
            exit;
        }

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
     * Active/Désactive un département (SuperAdmin uniquement).
     */
    public function toggleStatus($id)
    {
        if (Session::get('user_role') !== 'superadmin') {
            header("Location: /departments");
            exit;
        }

        $stmt = $this->db->prepare("UPDATE departments SET status = NOT status WHERE id = ?");
        $stmt->execute([(int)$id]);
        
        Session::setFlash('success', __('updated_success'));
        header("Location: /departments");
        exit;
    }

    /**
     * Supprime un département si inutilisé.
     */
    public function delete($id)
    {
        if (Session::get('user_role') !== 'superadmin') {
            header("Location: /departments");
            exit;
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM departments WHERE id = ?");
            $stmt->execute([(int)$id]);
            Session::setFlash('success', __('deleted_success'));
        } catch (\PDOException $e) {
            Session::setFlash('error', __('error_generic'));
        }

        header("Location: /departments");
        exit;
    }
}
