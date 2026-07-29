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
        if (Session::get('user_role') !== 'superadmin') {
            Session::setFlash('error', "Seul le Super Administrateur est autorisé à supprimer un type d'enseignement.");
            header("Location: /teaching_types");
            exit;
        }

        try {
            $stmtCode = $this->db->prepare("SELECT code FROM teaching_types WHERE id = ?");
            $stmtCode->execute([(int)$id]);
            $code = $stmtCode->fetchColumn();

            if ($code === 'SEC00') {
                Session::setFlash('error', "Le type d'enseignement système SEC00 est protégé et ne peut pas être supprimé.");
                header("Location: /teaching_types");
                exit;
            }

            $deps = [];
            $checkCycles = $this->db->prepare("SELECT COUNT(*) FROM cycles WHERE teaching_type_id = ?");
            $checkCycles->execute([(int)$id]);
            if ($checkCycles->fetchColumn() > 0) $deps[] = 'cycles';

            $checkDepts = $this->db->prepare("SELECT COUNT(*) FROM departments WHERE teaching_type_id = ?");
            $checkDepts->execute([(int)$id]);
            if ($checkDepts->fetchColumn() > 0) $deps[] = 'départements';

            $checkClasses = $this->db->prepare("SELECT COUNT(*) FROM classes WHERE teaching_type_id = ?");
            $checkClasses->execute([(int)$id]);
            if ($checkClasses->fetchColumn() > 0) $deps[] = 'classes';

            $checkSubjects = $this->db->prepare("SELECT COUNT(*) FROM subjects WHERE teaching_type_id = ?");
            $checkSubjects->execute([(int)$id]);
            if ($checkSubjects->fetchColumn() > 0) $deps[] = 'matières';

            if (!empty($deps)) {
                Session::setFlash('error', "Impossible de supprimer ce type d'enseignement car des éléments (" . implode(', ', $deps) . ") y sont rattachés.");
                header("Location: /teaching_types");
                exit;
            }

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
