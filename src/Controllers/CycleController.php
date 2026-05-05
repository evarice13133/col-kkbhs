<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
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

    /**
     * Initialisation et contrôle d'accès strict.
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        
        // SÉCURITÉ : Vérification du rôle SUPERADMIN
        if (Session::get('user_role') !== 'superadmin') {
            header("Location: /");
            exit;
        }
    }

    /**
     * Liste l'ensemble des cycles paramétrés dans le système.
     */
    public function index()
    {
        // Tri alphabétique pour la cohérence visuelle
        $stmt = $this->db->query("SELECT * FROM cycles ORDER BY nom ASC");
        $cycles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        include __DIR__ . '/../Views/cycles/index.php';
    }

    /**
     * Formulaire pour ajouter un nouveau cycle.
     */
    public function create()
    {
        include __DIR__ . '/../Views/cycles/create.php';
    }

    /**
     * Enregistre un cycle et valide les duplicatas.
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim((string) ($_POST['nom'] ?? ''));

            if ($nom === '') {
                $error = __('required');
                include __DIR__ . '/../Views/cycles/create.php';
                return;
            }

            try {
                $stmt = $this->db->prepare("INSERT INTO cycles (nom) VALUES (?)");
                $stmt->execute([$nom]);
                
                Session::setFlash('success', __('created_success'));
                header("Location: /cycles");
                exit;
            } catch (\PDOException $e) {
                // Erreur SQL PDO lancée si le cycle existe déjà (contrainte UNIQUE)
                $error = __('error_generic');
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

        include __DIR__ . '/../Views/cycles/edit.php';
    }

    /**
     * Applique les modifications d'un cycle.
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim((string) ($_POST['nom'] ?? ''));

            if ($nom === '') {
                $error = __('required');
                $cycle = ['id' => $id, 'nom' => $nom];
                include __DIR__ . '/../Views/cycles/edit.php';
                return;
            }

            try {
                $stmt = $this->db->prepare("UPDATE cycles SET nom = ? WHERE id = ?");
                $stmt->execute([$nom, (int)$id]);
                
                Session::setFlash('success', __('updated_success'));
                header("Location: /cycles");
                exit;
            } catch (\PDOException $e) {
                $error = __('error_generic');
                $cycle = ['id' => $id, 'nom' => $nom];
                include __DIR__ . '/../Views/cycles/edit.php';
            }
        }
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
