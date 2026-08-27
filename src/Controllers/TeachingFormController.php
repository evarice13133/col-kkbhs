<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\PermissionManager;
use App\Core\Session;
use PDO;

class TeachingFormController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        if (!Session::isLogged()) {
            header('Location: /login');
            exit;
        }
        PermissionManager::requirePermission('manage_teaching_forms');
    }

    public function index()
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        $teaching_type_id = !empty($_GET['teaching_type_id']) ? (int) $_GET['teaching_type_id'] : null;

        $conditions = [];
        $params = [];

        $conditions[] = '(tt.actif = 1 OR tf.teaching_type_id IS NULL)';

        if ($q !== '') {
            $conditions[] = '(tf.nom LIKE ? OR tf.code LIKE ?)';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }

        if ($teaching_type_id !== null) {
            $conditions[] = 'tf.teaching_type_id = ?';
            $params[] = $teaching_type_id;
        }

        $where = !empty($conditions) ? ' WHERE ' . implode(' AND ', $conditions) : '';
        $query = "SELECT tf.*, tt.nom AS teaching_type_nom FROM teaching_forms tf LEFT JOIN teaching_types tt ON tf.teaching_type_id = tt.id" . $where . ' ORDER BY tf.nom ASC';
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $teachingForms = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $teachingTypes = $this->db->query('SELECT * FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC')->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/teaching_forms/index.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $nom = trim((string) ($_POST['nom'] ?? ''));
        $code = strtoupper(trim((string) ($_POST['code'] ?? '')));
        $teaching_type_id = !empty($_POST['teaching_type_id']) ? (int) $_POST['teaching_type_id'] : null;
        $actif = isset($_POST['actif']) ? 1 : 1;

        if ($nom === '' || $code === '' || !$teaching_type_id) {
            Session::setFlash('error', __('required') ?? 'Veuillez remplir tous les champs obligatoires.');
            header('Location: /teaching_forms');
            exit;
        }

        try {
            $stmt = $this->db->prepare('INSERT INTO teaching_forms (nom, code, teaching_type_id, status) VALUES (?, ?, ?, ?)');
            $stmt->execute([$nom, $code, $teaching_type_id, $actif]);
            Session::setFlash('success', __('created_success') ?? 'Créé avec succès.');
            header('Location: /teaching_forms');
            exit;
        } catch (\PDOException $e) {
            Session::setFlash('error', __('error_generic') ?? 'Erreur lors de la création.');
            header('Location: /teaching_forms');
            exit;
        }
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $nom = trim((string) ($_POST['nom'] ?? ''));
        $code = strtoupper(trim((string) ($_POST['code'] ?? '')));
        $teaching_type_id = !empty($_POST['teaching_type_id']) ? (int) $_POST['teaching_type_id'] : null;
        $actif = isset($_POST['actif']) ? 1 : 1;

        if ($nom === '' || $code === '' || !$teaching_type_id) {
            Session::setFlash('error', __('required') ?? 'Veuillez remplir tous les champs obligatoires.');
            header('Location: /teaching_forms');
            exit;
        }

        try {
            $stmt = $this->db->prepare('UPDATE teaching_forms SET nom = ?, code = ?, teaching_type_id = ?, status = ? WHERE id = ?');
            $stmt->execute([$nom, $code, $teaching_type_id, $actif, (int) $id]);
            Session::setFlash('success', __('updated_success') ?? 'Mis à jour avec succès.');
            header('Location: /teaching_forms');
            exit;
        } catch (\PDOException $e) {
            Session::setFlash('error', __('error_generic') ?? 'Erreur lors de la mise à jour.');
            header('Location: /teaching_forms');
            exit;
        }
    }

    public function toggleStatus($id)
    {
        $stmt = $this->db->prepare('UPDATE teaching_forms SET status = NOT status WHERE id = ?');
        $stmt->execute([(int) $id]);
        Session::setFlash('success', __('updated_success') ?? 'Statut mis à jour.');
        header('Location: /teaching_forms');
        exit;
    }

    public function delete($id)
    {
        if (Session::get('user_role') !== 'superadmin') {
            Session::setFlash('error', 'Seul le Super Administrateur est autorisé à supprimer une forme d\'enseignement.');
            header('Location: /teaching_forms');
            exit;
        }

        try {
            $check = $this->db->prepare('SELECT COUNT(*) FROM departments WHERE teaching_form_id = ?');
            $check->execute([(int) $id]);
            if ((int) $check->fetchColumn() > 0) {
                Session::setFlash('error', 'Impossible de supprimer cette forme d\'enseignement car des départements y sont rattachés.');
                header('Location: /teaching_forms');
                exit;
            }

            $stmt = $this->db->prepare('DELETE FROM teaching_forms WHERE id = ?');
            $stmt->execute([(int) $id]);
            Session::setFlash('success', __('deleted_success') ?? 'Supprimé avec succès.');
        } catch (\PDOException $e) {
            Session::setFlash('error', __('error_generic') ?? 'Erreur lors de la suppression.');
        }

        header('Location: /teaching_forms');
        exit;
    }
}
