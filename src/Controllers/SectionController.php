<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Core\PermissionManager;
use App\Services\SettingsStore;
use PDO;

/**
 * SectionController
 * 
 * Ce contrôleur gère la structuration académique par sections (ex: Scientifique, Littéraire).
 * L'accès est strictement réservé au rôle SUPERADMIN conformément aux exigences de sécurité.
 */
class SectionController
{
    /** @var PDO Instance de connexion à la base de données */
    private $db;
    private SettingsStore $settingsStore;

    /**
     * Constructeur de SectionController.
     * Initialise la connexion et vérifie les privilèges d'accès.
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->settingsStore = new SettingsStore($this->db);
        
        // Sécurité RBAC : Accès réservé aux administrateurs
        PermissionManager::requirePermission('manage_sections');
    }

    /**
     * Liste toutes les sections enregistrées.
     */
    public function index()
    {
        // Récupération par ordre alphabétique pour une meilleure ergonomie
        $stmt = $this->db->query("SELECT * FROM sections ORDER BY nom ASC");
        $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
        include __DIR__ . '/../Views/sections/index.php';
    }

    /**
     * Affiche le formulaire de création d'une section.
     */
    public function create()
    {
        include __DIR__ . '/../Views/sections/create.php';
    }

    /**
     * Enregistre une nouvelle section dans la base de données.
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim((string) ($_POST['nom'] ?? ''));
            
            // Validation de base : le nom ne peut pas être vide
            if ($nom === '') {
                $error = __('required');
                include __DIR__ . '/../Views/sections/create.php';
                return;
            }

            try {
                $stmt = $this->db->prepare("INSERT INTO sections (nom) VALUES (?)");
                $stmt->execute([$nom]);
                $newSectionId = (int)$this->db->lastInsertId();
                $threshold = trim((string) ($_POST['honor_roll_threshold'] ?? ''));
                if ($threshold !== '') {
                    $this->settingsStore->set('honor_roll_threshold_section_' . $newSectionId, $threshold);
                }
                
                Session::setFlash('success', __('created_success'));
                header("Location: /sections");
                exit;
            } catch (\PDOException $e) {
                // Gestion des doublons via contrainte UNIQUE SQL
                $error = __('error_generic'); 
                include __DIR__ . '/../Views/sections/create.php';
            }
        }
    }

    /**
     * Affiche le formulaire de modification d'une section existante.
     */
    public function edit($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM sections WHERE id = ?");
        $stmt->execute([(int)$id]);
        $section = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$section) {
            header("Location: /sections");
            exit;
        }

        $section['honor_roll_threshold'] = $this->settingsStore->get('honor_roll_threshold_section_' . $section['id'], '');

        include __DIR__ . '/../Views/sections/edit.php';
    }

    /**
     * Met à jour les informations d'une section.
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim((string) ($_POST['nom'] ?? ''));

            if ($nom === '') {
                $error = __('required');
                $section = ['id' => $id, 'nom' => $nom];
                include __DIR__ . '/../Views/sections/edit.php';
                return;
            }

            try {
                $stmt = $this->db->prepare("UPDATE sections SET nom = ? WHERE id = ?");
                $stmt->execute([$nom, (int)$id]);
                $threshold = trim((string) ($_POST['honor_roll_threshold'] ?? ''));
                $this->settingsStore->set('honor_roll_threshold_section_' . $id, $threshold);
                
                Session::setFlash('success', __('updated_success'));
                header("Location: /sections");
                exit;
            } catch (\PDOException $e) {
                $error = __('error_generic');
                $section = ['id' => $id, 'nom' => $nom];
                include __DIR__ . '/../Views/sections/edit.php';
            }
        }
    }

    /**
     * Supprime une section de la base de données.
     */
    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM sections WHERE id = ?");
            $stmt->execute([(int)$id]);
            Session::setFlash('success', __('deleted_success'));
        } catch (\PDOException $e) {
            Session::setFlash('error', __('error_generic'));
        }

        header("Location: /sections");
        exit;
    }
}
