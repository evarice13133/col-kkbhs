<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use PDO;

class ProfileController {
    
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        // Le profil est accessible par absolument tous les utilisateurs connectés
        if (!Session::isLogged()) { header("Location: /login"); exit; }
    }

    public function index() {
        $stmt = $this->db->prepare("SELECT nom, prenom, email, role FROM users WHERE id = ?");
        $stmt->execute([Session::get('user_id')]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        include __DIR__ . '/../Views/profile/index.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            
            $id = Session::get('user_id');

            try {
                if (!empty($password)) {
                    // L'utilisateur change son mot de passe
                    $pwdHash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $this->db->prepare("UPDATE users SET nom = ?, prenom = ?, email = ?, password = ? WHERE id = ?");
                    $stmt->execute([$nom, $prenom, $email, $pwdHash, $id]);
                } else {
                    // Update simple de l'identité
                    $stmt = $this->db->prepare("UPDATE users SET nom = ?, prenom = ?, email = ? WHERE id = ?");
                    $stmt->execute([$nom, $prenom, $email, $id]);
                }
                
                // Mettre à jour la session pour refléter le changement de nom en haut à droite
                Session::set('user_name', $nom); 
                Session::set('success_msg', 'Vos paramètres personnels ont été appliqués avec succès.');
                header("Location: /profile");
                exit;
            } catch (\PDOException $e) {
                // Erreur typiquement due à un email duplicate
                $error = strpos($e->getMessage(), 'Duplicate') !== false ? "Erreur : Cette adresse email est déjà utilisée par un autre collaborateur." : "Erreur de base de données : " . $e->getMessage();
                $user = ['nom' => $nom, 'prenom' => $prenom, 'email' => $email, 'role' => Session::get('user_role')];
                include __DIR__ . '/../Views/profile/index.php';
            }
        }
    }
}
