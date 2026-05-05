<?php

namespace App\Controllers;

use App\Models\User;
use App\Core\Database;
use App\Core\Session;
use PDO;

class UserController
{
    //declaration de la variable db
    private $db;
    //preparation du constructeur
    public function __construct()
    {
        //connexion a la base de donnee
        $this->db = Database::getInstance()->getConnection();
        //verification de la session
        if (!Session::isLogged() || !in_array(Session::get('user_role'), ['superadmin', 'admin'])) {
            header("Location: /");
            exit;
        }
    }

    public function index()
    {
        //recuperation des utilisateurs
        [$users, $filters] = $this->fetchUsersFromFilters();
        include __DIR__ . '/../Views/users/index.php';
    }
    //export des utilisateurs
    public function export()
    {
        [$users] = $this->fetchUsersFromFilters();

        $exportTitle = "Registre des utilisateurs";
        $exportSubtitle = "Liste filtrée des comptes";
        $exportColumns = ['ID', 'Nom', 'Prenom', 'Login', 'Email', 'Role'];
        $exportRows = array_map(function ($user) {
            //mapping des utilisateurs
            return [$user['id'], $user['nom'], $user['prenom'], $user['username'], $user['email'] ?: 'N/A', ucfirst($user['role'])];
        }, $users);

        include __DIR__ . '/../Views/templates/export.php';
    }

    public function create()
    {
        include __DIR__ . '/../Views/users/create.php';
    }

    public function store()
    {
        //verification de la methode de la requete
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                \App\Core\Security::log("Tentative de CSRF détectée sur l'action User::store");
                Session::setFlash('error', "Session expirée ou requête invalide.");
                header("Location: /users/create");
                exit;
            }
            //recuperation des donnees
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'enseignant';
            //verification de la permission
            if (Session::get('user_role') === 'admin' && $role === 'superadmin') {
                $error = \__('admin_cannot_create_superadmin');
                include __DIR__ . '/../Views/users/create.php';
                return;
            }
            // verification des champs
            if (empty($nom) || empty($prenom) || empty($username) || empty($password)) {
                $error = \__('user_required_fields');
                include __DIR__ . '/../Views/users/create.php';
                return;
            }

            $user = new User($nom, $prenom, $username, $email ?: null, $password, $role);
            $user->setPassword($password);

            try {
                $stmt = $this->db->prepare("INSERT INTO users (nom, prenom, username, email, password, role) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $user->getNom(),
                    $user->getPrenom(),
                    $user->getUsername(),
                    $user->getEmail(),
                    $user->getPassword(),
                    $user->getRole(),
                ]);
                header("Location: /users");
                exit;
            } catch (\PDOException $e) {
                $error = strpos($e->getMessage(), 'Duplicate') !== false ? \__('username_taken') : \__('internal_db_error');
                include __DIR__ . '/../Views/users/create.php';
                return;
            }
        }
    }

    public function edit($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            header("Location: /users");
            exit;
        }

        // Sécurité : Un admin ne peut pas éditer un superadmin
        if (Session::get('user_role') === 'admin' && $user['role'] === 'superadmin') {
            header("Location: /users");
            exit;
        }

        include __DIR__ . '/../Views/users/edit.php';
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', "Session expirée ou requête invalide.");
                header("Location: /users/edit?id=" . $id);
                exit;
            }

            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'enseignant';

            // Sécurité : Un admin ne peut pas s'attribuer le rôle superadmin ou modifier un superadmin
            $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if (Session::get('user_role') === 'admin') {
                if ($role === 'superadmin' || ($currentUser && $currentUser['role'] === 'superadmin')) {
                    Session::setFlash('error', \__('admin_cannot_assign_superadmin'));
                    header("Location: /users/edit?id=" . $id);
                    exit;
                }
            }

            try {
                if (!empty($password)) {
                    // Utilisation de l'algorithme définit dans le modèle
                    $dummyUser = new User();
                    $dummyUser->setPassword($password);
                    $hashedPassword = $dummyUser->getPassword();

                    $stmt = $this->db->prepare("UPDATE users SET nom = ?, prenom = ?, username = ?, email = ?, password = ?, role = ? WHERE id = ?");
                    $stmt->execute([$nom, $prenom, $username, $email ?: null, $hashedPassword, $role, $id]);
                } else {
                    $stmt = $this->db->prepare("UPDATE users SET nom = ?, prenom = ?, username = ?, email = ?, role = ? WHERE id = ?");
                    $stmt->execute([$nom, $prenom, $username, $email ?: null, $role, $id]);
                }

                Session::setFlash('success', \__('user_updated_success'));
                header("Location: /users");
                exit;
            } catch (\PDOException $e) {
                Session::setFlash('error', strpos($e->getMessage(), 'Duplicate') !== false ? \__('username_taken') : \__('internal_db_error'));
                header("Location: /users/edit?id=" . $id);
                exit;
            }
        }
    }

    public function delete($id)
    {
        if (!Session::verifyCsrfToken($_GET['csrf_token'] ?? '')) {
            \App\Core\Security::log("Tentative de CSRF détectée sur l'action User::delete (ID: $id)");
            header("Location: /users");
            exit;
        }
        if (Session::get('user_id') == $id) {
            header("Location: /users");
            exit;
        }

        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($targetUser) {
            if (Session::get('user_role') === 'admin' && $targetUser['role'] === 'superadmin') {
                header("Location: /users");
                exit;
            }

            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
        }

        header("Location: /users");
        exit;
    }

    private function fetchUsersFromFilters()
    {
        $search = trim($_GET['q'] ?? '');
        $roleFilter = trim($_GET['role'] ?? '');

        if (Session::get('user_role') === 'admin') {
            $sql = "SELECT id, nom, prenom, username, email, role FROM users WHERE role != 'superadmin'";
        } else {
            $sql = "SELECT id, nom, prenom, username, email, role FROM users WHERE 1=1";
        }

        $params = [];

        if ($search !== '') {
            $like = '%' . $search . '%';
            $sql .= " AND (nom LIKE ? OR prenom LIKE ? OR username LIKE ? OR email LIKE ?)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if ($roleFilter !== '') {
            $sql .= " AND role = ?";
            $params[] = $roleFilter;
        }

        $sql .= " ORDER BY nom ASC, prenom ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return [$stmt->fetchAll(PDO::FETCH_ASSOC), ['q' => $search, 'role' => $roleFilter]];
    }
}
