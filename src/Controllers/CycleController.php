<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Core\PermissionManager;
use App\Services\SettingsStore;
use PDO;

/**
 * CycleController
 * 
 * Ce contrôleur gère la gestion des cycles d'études (ex: Primaire, Collège, Lycée).
 * L'accès à ce pilotage structurel est réservé exclusivement au SUPERADMIN.
 */
class CycleController
{
    /** @var PDO Instance pour les opérations SQL */
    private $db;
    private SettingsStore $settingsStore;

    /**
     * Initialisation et contrôle d'accès strict.
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->settingsStore = new SettingsStore($this->db);
        
        // Sécurité RBAC : Accès réservé aux administrateurs
        PermissionManager::requirePermission('manage_cycles');
    }

    /**
     * Liste l'ensemble des cycles paramétrés dans le système.
     */
    public function index()
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        $teaching_type_id = !empty($_GET['teaching_type_id']) ? (int) $_GET['teaching_type_id'] : null;

        $conditions = [];
        $params = [];

        // Ne sélectionner que les cycles rattachés à un type d'enseignement actif (ou sans type)
        $conditions[] = "(t.actif = 1 OR c.teaching_type_id IS NULL)";

        if ($q !== '') {
            $conditions[] = "c.nom LIKE ?";
            $params[] = '%' . $q . '%';
        }

        if ($teaching_type_id !== null) {
            $conditions[] = "c.teaching_type_id = ?";
            $params[] = $teaching_type_id;
        }

        $whereClause = !empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "";
        $query = "SELECT c.*, t.nom as teaching_type_nom FROM cycles c LEFT JOIN teaching_types t ON c.teaching_type_id = t.id" . $whereClause . " ORDER BY c.nom ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $cycles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $teachingTypes = $this->db->query("SELECT * FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        $filters = [
            'q' => $q,
            'teaching_type_id' => $teaching_type_id
        ];

        include __DIR__ . '/../Views/cycles/index.php';
    }

    /**
     * Formulaire pour ajouter un nouveau cycle.
     */
    public function create()
    {
        $teachingTypes = $this->db->query("SELECT * FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        include __DIR__ . '/../Views/cycles/create.php';
    }

    /**
     * Enregistre un cycle et valide les duplicatas.
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim((string) ($_POST['nom'] ?? ''));
            $teaching_type_id = !empty($_POST['teaching_type_id']) ? (int) $_POST['teaching_type_id'] : null;

            if ($nom === '' || !$teaching_type_id) {
                $error = __('required');
                $teachingTypes = $this->db->query("SELECT * FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/cycles/create.php';
                return;
            }

            try {
                $stmt = $this->db->prepare("INSERT INTO cycles (nom, teaching_type_id) VALUES (?, ?)");
                $stmt->execute([$nom, $teaching_type_id]);
                $newCycleId = (int)$this->db->lastInsertId();
                $threshold = trim((string) ($_POST['honor_roll_threshold'] ?? ''));
                if ($threshold !== '') {
                    $this->settingsStore->set('honor_roll_threshold_cycle_' . $newCycleId, $threshold);
                }
                
                Session::setFlash('success', __('created_success'));
                header("Location: /cycles");
                exit;
            } catch (\PDOException $e) {
                $error = __('error_generic');
                $teachingTypes = $this->db->query("SELECT * FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/cycles/create.php';
            }
        }
    }

    /**
     * Affiche l'écran de modification d'un cycle précis.
     */
    public function edit($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM cycles WHERE id = ?");
        $stmt->execute([(int)$id]);
        $cycle = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cycle) {
            header("Location: /cycles");
            exit;
        }

        $cycle['honor_roll_threshold'] = $this->settingsStore->get('honor_roll_threshold_cycle_' . $cycle['id'], '');
        $teachingTypes = $this->db->query("SELECT * FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/cycles/edit.php';
    }

    /**
     * Applique les modifications d'un cycle.
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim((string) ($_POST['nom'] ?? ''));
            $teaching_type_id = !empty($_POST['teaching_type_id']) ? (int) $_POST['teaching_type_id'] : null;

            if ($nom === '' || !$teaching_type_id) {
                $error = __('required');
                $cycle = ['id' => $id, 'nom' => $nom, 'teaching_type_id' => $teaching_type_id];
                $teachingTypes = $this->db->query("SELECT * FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/cycles/edit.php';
                return;
            }

            try {
                $stmt = $this->db->prepare("UPDATE cycles SET nom = ?, teaching_type_id = ? WHERE id = ?");
                $stmt->execute([$nom, $teaching_type_id, (int)$id]);
                $threshold = trim((string) ($_POST['honor_roll_threshold'] ?? ''));
                $this->settingsStore->set('honor_roll_threshold_cycle_' . $id, $threshold);
                
                Session::setFlash('success', __('updated_success'));
                header("Location: /cycles");
                exit;
            } catch (\PDOException $e) {
                $error = __('error_generic');
                $cycle = ['id' => $id, 'nom' => $nom, 'teaching_type_id' => $teaching_type_id];
                $teachingTypes = $this->db->query("SELECT * FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/cycles/edit.php';
            }
        }
    }

    /**
     * Active ou désactive un cycle.
     */
    public function toggleStatus($id)
    {
        $stmt = $this->db->prepare("UPDATE cycles SET status = NOT status WHERE id = ?");
        $stmt->execute([(int)$id]);
        
        Session::setFlash('success', __('updated_success'));
        header("Location: /cycles");
        exit;
    }

    /**
     * Supprime un cycle s'il n'est plus utilisé.
     */
    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM cycles WHERE id = ?");
            $stmt->execute([(int)$id]);
            Session::setFlash('success', __('deleted_success'));
        } catch (\PDOException $e) {
            // Empêche la suppression si lié à des classes (clé étrangère)
            Session::setFlash('error', __('error_generic'));
        }

        header("Location: /cycles");
        exit;
    }
}
