<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Core\PermissionManager;
use App\Services\SettingsStore;
use PDO;

/**
 * LevelController
 * 
 * Ce contrôleur assure la gestion du référentiel des Niveaux académiques (ex: SIL, CP, CE1, 6ème, L1, L2...).
 * Accessible exclusivement aux Super Administrateurs et Administrateurs dans le Centre de Pilotage.
 */
class LevelController
{
    private $db;
    private SettingsStore $settingsStore;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->settingsStore = new SettingsStore($this->db);

        // Sécurité RBAC : Accès réservé aux administrateurs / superadmins
        PermissionManager::requirePermission('manage_levels');
    }

    /**
     * Affiche le registre filtré des niveaux.
     */
    public function index()
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        $teaching_type_id = !empty($_GET['teaching_type_id']) ? (int) $_GET['teaching_type_id'] : null;
        $status = isset($_GET['status']) && $_GET['status'] !== '' ? (int) $_GET['status'] : null;

        // Recherche du type d'enseignement Supérieur LMD
        $lmdStmt = $this->db->query("SELECT id FROM teaching_types WHERE code = 'LMD' OR LOWER(nom) LIKE '%lmd%' OR LOWER(nom) LIKE '%supérieur%' ORDER BY id ASC LIMIT 1");
        $lmdId = $lmdStmt ? (int) $lmdStmt->fetchColumn() : 0;

        if ($teaching_type_id === null && $lmdId > 0) {
            $teaching_type_id = $lmdId;
        }

        $conditions = [];
        $params = [];

        // Filtre par type d'enseignement actif (ou sans type)
        $conditions[] = "(tt.actif = 1 OR l.teaching_type_id IS NULL)";

        if ($q !== '') {
            $conditions[] = "(l.code LIKE ? OR l.libelle_fr LIKE ? OR l.libelle_en LIKE ?)";
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }

        if ($teaching_type_id !== null) {
            $conditions[] = "l.teaching_type_id = ?";
            $params[] = $teaching_type_id;
        }

        if ($status !== null) {
            $conditions[] = "l.status = ?";
            $params[] = $status;
        }

        $whereClause = !empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "";
        $query = "SELECT l.*, tt.nom as teaching_type_nom 
                  FROM levels l 
                  LEFT JOIN teaching_types tt ON l.teaching_type_id = tt.id" 
                  . $whereClause . 
                  " ORDER BY tt.position ASC, l.code ASC, l.libelle_fr ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $levels = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/levels/index.php';
    }

    /**
     * Formulaire de création d'un niveau.
     */
    public function create()
    {
        $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        include __DIR__ . '/../Views/levels/create.php';
    }

    /**
     * Enregistre un nouveau niveau.
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = strtoupper(trim((string) ($_POST['code'] ?? '')));
            $libelle_fr = trim((string) ($_POST['libelle_fr'] ?? ''));
            $libelle_en = trim((string) ($_POST['libelle_en'] ?? ''));
            $teaching_type_id = !empty($_POST['teaching_type_id']) ? (int) $_POST['teaching_type_id'] : null;
            $status = isset($_POST['status']) ? 1 : 0;

            if ($code === '' || $libelle_fr === '' || $libelle_en === '' || !$teaching_type_id) {
                $error = __('required') ?? 'Tous les champs obligatoires doivent être renseignés.';
                $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/levels/create.php';
                return;
            }

            // Vérification de l'unicité du code dans le même type d'enseignement
            $chk = $this->db->prepare("SELECT COUNT(*) FROM levels WHERE code = ? AND teaching_type_id = ?");
            $chk->execute([$code, $teaching_type_id]);
            if ($chk->fetchColumn() > 0) {
                $error = "Un niveau avec le code '$code' existe déjà pour ce type d'enseignement.";
                $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/levels/create.php';
                return;
            }

            try {
                $stmt = $this->db->prepare("INSERT INTO levels (code, libelle_fr, libelle_en, teaching_type_id, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$code, $libelle_fr, $libelle_en, $teaching_type_id, $status]);

                Session::setFlash('success', __('level_created_success') ?? 'Niveau créé avec succès.');
                header("Location: /levels");
                exit;
            } catch (\PDOException $e) {
                $error = __('error_generic') ?? 'Erreur lors de la création.';
                $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/levels/create.php';
            }
        }
    }

    /**
     * Formulaire de modification d'un niveau.
     */
    public function edit($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM levels WHERE id = ?");
        $stmt->execute([(int) $id]);
        $level = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$level) {
            header("Location: /levels");
            exit;
        }

        $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        include __DIR__ . '/../Views/levels/edit.php';
    }

    /**
     * Mettre à jour un niveau existant.
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = strtoupper(trim((string) ($_POST['code'] ?? '')));
            $libelle_fr = trim((string) ($_POST['libelle_fr'] ?? ''));
            $libelle_en = trim((string) ($_POST['libelle_en'] ?? ''));
            $teaching_type_id = !empty($_POST['teaching_type_id']) ? (int) $_POST['teaching_type_id'] : null;
            $status = isset($_POST['status']) ? 1 : 0;

            if ($code === '' || $libelle_fr === '' || $libelle_en === '' || !$teaching_type_id) {
                $error = __('required') ?? 'Tous les champs obligatoires doivent être renseignés.';
                $level = ['id' => $id, 'code' => $code, 'libelle_fr' => $libelle_fr, 'libelle_en' => $libelle_en, 'teaching_type_id' => $teaching_type_id, 'status' => $status];
                $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/levels/edit.php';
                return;
            }

            // Vérification de l'unicité du code dans le même type d'enseignement (exclusion de soi-même)
            $chk = $this->db->prepare("SELECT COUNT(*) FROM levels WHERE code = ? AND teaching_type_id = ? AND id != ?");
            $chk->execute([$code, $teaching_type_id, (int)$id]);
            if ($chk->fetchColumn() > 0) {
                $error = "Un niveau avec le code '$code' existe déjà pour ce type d'enseignement.";
                $level = ['id' => $id, 'code' => $code, 'libelle_fr' => $libelle_fr, 'libelle_en' => $libelle_en, 'teaching_type_id' => $teaching_type_id, 'status' => $status];
                $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/levels/edit.php';
                return;
            }

            try {
                $stmt = $this->db->prepare("UPDATE levels SET code = ?, libelle_fr = ?, libelle_en = ?, teaching_type_id = ?, status = ? WHERE id = ?");
                $stmt->execute([$code, $libelle_fr, $libelle_en, $teaching_type_id, $status, (int) $id]);

                Session::setFlash('success', __('level_updated_success') ?? 'Niveau mis à jour avec succès.');
                header("Location: /levels");
                exit;
            } catch (\PDOException $e) {
                $error = __('error_generic') ?? 'Erreur lors de la mise à jour.';
                $level = ['id' => $id, 'code' => $code, 'libelle_fr' => $libelle_fr, 'libelle_en' => $libelle_en, 'teaching_type_id' => $teaching_type_id, 'status' => $status];
                $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/levels/edit.php';
            }
        }
    }

    /**
     * Active ou désactive un niveau.
     */
    public function toggleStatus($id)
    {
        $stmt = $this->db->prepare("SELECT status FROM levels WHERE id = ?");
        $stmt->execute([(int) $id]);
        $currStatus = $stmt->fetchColumn();

        if ($currStatus !== false) {
            $newStatus = $currStatus == 1 ? 0 : 1;
            $upd = $this->db->prepare("UPDATE levels SET status = ? WHERE id = ?");
            $upd->execute([$newStatus, (int) $id]);
            Session::setFlash('success', __('level_toggle_success') ?? 'Statut du niveau mis à jour.');
        }

        header("Location: /levels");
        exit;
    }

    /**
     * Supprime un niveau si aucune classe n'y est rattachée.
     */
    public function delete($id)
    {
        try {
            $checkClasses = $this->db->prepare("SELECT COUNT(*) FROM classes WHERE level_id = ?");
            $checkClasses->execute([(int) $id]);
            if ($checkClasses->fetchColumn() > 0) {
                Session::setFlash('error', __('level_has_classes_error') ?? 'Impossible de supprimer ce niveau car des classes y sont rattachées.');
                header("Location: /levels");
                exit;
            }

            $stmt = $this->db->prepare("DELETE FROM levels WHERE id = ?");
            $stmt->execute([(int) $id]);
            Session::setFlash('success', __('level_deleted_success') ?? 'Niveau supprimé avec succès.');
        } catch (\PDOException $e) {
            Session::setFlash('error', __('error_generic') ?? 'Erreur lors de la suppression.');
        }

        header("Location: /levels");
        exit;
    }
}
