<?php

namespace App\Controllers;

use App\Models\DiscountType;
use App\Core\Session;

/**
 * Class DiscountTypeController
 * 
 * Manages CRUD operations for discount types.
 */
class DiscountTypeController {
    private DiscountType $model;

    public function __construct() {
        \App\Core\PermissionManager::requirePermission('manage_discounts');
        $this->model = new DiscountType();
    }

    /**
     * Lists all discount types.
     */
    public function index() {
        $discountTypes = $this->model->getAll();
        include __DIR__ . '/../Views/discount_types/index.php';
    }

    /**
     * Save (create or update) a discount type.
     */
    public function store() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
            exit;
        }

        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Session expirée ou jeton CSRF invalide.']);
            exit;
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = isset($_POST['name']) ? trim((string)$_POST['name']) : '';
        $description = isset($_POST['description']) ? trim((string)$_POST['description']) : '';
        $comment = isset($_POST['comment']) ? trim((string)$_POST['comment']) : '';
        $status = isset($_POST['status']) && $_POST['status'] === 'inactive' ? 'inactive' : 'active';

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Le nom du type est obligatoire.']);
            exit;
        }

        // Check for duplicates
        $existing = $this->model->findByName($name);
        if ($existing && ($id === 0 || (int)$existing['id'] !== $id)) {
            echo json_encode(['success' => false, 'message' => 'Un type de réduction avec ce nom existe déjà.']);
            exit;
        }

        $data = [
            'name' => $name,
            'description' => $description,
            'comment' => $comment,
            'status' => $status,
            'created_by' => Session::get('user_id')
        ];

        if ($id > 0) {
            $success = $this->model->update($id, $data);
            $message = $success ? 'Type de réduction mis à jour avec succès.' : 'Erreur lors de la mise à jour.';
        } else {
            $success = $this->model->create($data);
            $message = $success ? 'Type de réduction créé avec succès.' : 'Erreur lors de la création.';
        }

        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }

    /**
     * Toggle the status (active/inactive) of a discount type.
     */
    public function toggleStatus($id) {
        header('Content-Type: application/json');
        $id = (int)$id;

        $type = $this->model->find($id);
        if (!$type) {
            echo json_encode(['success' => false, 'message' => 'Type de réduction introuvable.']);
            exit;
        }

        $newStatus = $type['status'] === 'active' ? 'inactive' : 'active';
        $data = [
            'name' => $type['name'],
            'description' => $type['description'],
            'comment' => $type['comment'],
            'status' => $newStatus
        ];

        $success = $this->model->update($id, $data);
        $message = $success ? 'Statut mis à jour.' : 'Erreur lors de la mise à jour.';
        echo json_encode(['success' => $success, 'message' => $message, 'status' => $newStatus]);
        exit;
    }

    /**
     * Delete a discount type by ID.
     */
    public function delete($id) {
        header('Content-Type: application/json');
        $id = (int)$id;

        $type = $this->model->find($id);
        if (!$type) {
            echo json_encode(['success' => false, 'message' => 'Type de réduction introuvable.']);
            exit;
        }

        if ($this->model->isUsed($id)) {
            echo json_encode(['success' => false, 'message' => 'Impossible de supprimer ce type car il est déjà utilisé.']);
            exit;
        }

        $success = $this->model->delete($id);
        $message = $success ? 'Type de réduction supprimé avec succès.' : 'Erreur lors de la suppression.';
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }
}
