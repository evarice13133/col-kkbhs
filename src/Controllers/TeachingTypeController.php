<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use PDO;

class TeachingTypeController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        
        if (!in_array(Session::get('user_role'), ['superadmin', 'admin'])) {
            header("Location: /");
            exit;
        }
    }

    public function index()
    {
        $stmt = $this->db->query("SELECT * FROM teaching_types ORDER BY position ASC, nom ASC");
        $teachingTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        include __DIR__ . '/../Views/teaching_types/index.php';
    }

    public function create()
    {
        include __DIR__ . '/../Views/teaching_types/create.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim((string) ($_POST['nom'] ?? ''));
            $code = trim((string) ($_POST['code'] ?? ''));
            $position = (int) ($_POST['position'] ?? 0);
            $actif = isset($_POST['actif']) ? 1 : 0;

            if ($nom === '' || $code === '') {
                $error = __('required');
                include __DIR__ . '/../Views/teaching_types/create.php';
                return;
            }

            try {
                $stmt = $this->db->prepare("INSERT INTO teaching_types (nom, code, position, actif) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nom, $code, $position, $actif]);
                
                Session::setFlash('success', __('created_success') ?? 'Créé avec succès.');
                header("Location: /teaching_types");
                exit;
            } catch (\PDOException $e) {
                $error = __('error_generic') ?? 'Erreur lors de la création.';
                include __DIR__ . '/../Views/teaching_types/create.php';
            }
        }
    }

    public function edit($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM teaching_types WHERE id = ?");
        $stmt->execute([(int)$id]);
        $teachingType = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$teachingType) {
            header("Location: /teaching_types");
            exit;
        }

        include __DIR__ . '/../Views/teaching_types/edit.php';
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim((string) ($_POST['nom'] ?? ''));
            $code = trim((string) ($_POST['code'] ?? ''));
            $position = (int) ($_POST['position'] ?? 0);
            $actif = isset($_POST['actif']) ? 1 : 0;

            if ($nom === '' || $code === '') {
                $error = __('required');
                $teachingType = ['id' => $id, 'nom' => $nom, 'code' => $code, 'position' => $position, 'actif' => $actif];
                include __DIR__ . '/../Views/teaching_types/edit.php';
                return;
            }

            try {
                $stmt = $this->db->prepare("UPDATE teaching_types SET nom = ?, code = ?, position = ?, actif = ? WHERE id = ?");
                $stmt->execute([$nom, $code, $position, $actif, (int)$id]);
                
                Session::setFlash('success', __('updated_success') ?? 'Mis à jour avec succès.');
                header("Location: /teaching_types");
                exit;
            } catch (\PDOException $e) {
                $error = __('error_generic') ?? 'Erreur lors de la mise à jour.';
                $teachingType = ['id' => $id, 'nom' => $nom, 'code' => $code, 'position' => $position, 'actif' => $actif];
                include __DIR__ . '/../Views/teaching_types/edit.php';
            }
        }
    }

    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM teaching_types WHERE id = ?");
            $stmt->execute([(int)$id]);
            Session::setFlash('success', __('deleted_success') ?? 'Supprimé avec succès.');
        } catch (\PDOException $e) {
            Session::setFlash('error', __('error_generic') ?? 'Erreur lors de la suppression.');
        }

        header("Location: /teaching_types");
        exit;
    }
}
