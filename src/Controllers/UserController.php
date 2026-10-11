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
        
        \App\Core\PermissionManager::requirePermission('manage_users');
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
            $roleLabel = $user['role'] === 'personnel' ? \__('role_personnel') : ucfirst($user['role']);
            return [$user['id'], $user['nom'], $user['prenom'], $user['username'], $user['email'] ?: 'N/A', $roleLabel];
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
            $role = is_string($_POST['role'] ?? null) ? $_POST['role'] : 'enseignant';
            $personnelData = $this->getPersonnelDataFromRequest($role);
            $formData = array_merge(compact('nom', 'prenom', 'username', 'email', 'role'), $personnelData);
            //verification de la permission
            if (Session::get('user_role') === 'admin' && in_array($role, ['superadmin'], true)) {
                $error = \__('admin_cannot_manage_admin_roles');
                $errorField = 'role';
                include __DIR__ . '/../Views/users/create.php';
                return;
            }
            // verification des champs
            if (empty($nom) || empty($prenom) || empty($username) || empty($password)) {
                $error = \__('user_required_fields');
                $errorField = empty($nom) ? 'nom' : (empty($prenom) ? 'prenom' : (empty($username) ? 'username' : 'password'));
                include __DIR__ . '/../Views/users/create.php';
                return;
            }

            if ($personnelData['is_staff'] && ($validationError = $this->validatePersonnelData($personnelData))) {
                $error = \__($validationError['message']);
                $errorField = $validationError['field'];
                include __DIR__ . '/../Views/users/create.php';
                return;
            }

            $user = new User($nom, $prenom, $username, $email ?: null, $password, $role);
            $user->setPassword($password);

            try {
                $stmt = $this->db->prepare("INSERT INTO users (nom, prenom, username, email, password, role, is_staff, cni_number, job_title, base_salary, nui_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $user->getNom(),
                    $user->getPrenom(),
                    $user->getUsername(),
                    $user->getEmail(),
                    $user->getPassword(),
                    $user->getRole(),
                    $personnelData['is_staff'],
                    $personnelData['cni_number'],
                    $personnelData['job_title'],
                    $personnelData['base_salary'],
                    $personnelData['nui_number'],
                ]);
                header("Location: /users");
                exit;
            } catch (\PDOException $e) {
                $duplicate = $this->getDuplicateField($e);
                $error = $duplicate === 'cni_number'
                    ? \__('staff_cni_taken')
                    : ($duplicate === 'nui_number'
                        ? \__('staff_nui_taken')
                        : (strpos($e->getMessage(), 'Duplicate') !== false ? \__('username_taken') : \__('internal_db_error')));
                $errorField = $duplicate ?? (strpos($e->getMessage(), 'Duplicate') !== false ? 'username' : '');
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

        // Un admin ne peut pas consulter ni modifier le compte Super Administrateur.
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
            $role = is_string($_POST['role'] ?? null) ? $_POST['role'] : 'enseignant';

            // Un admin ne peut pas attribuer ni modifier les rôles admin privilégiés.
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$currentUser) {
                header("Location: /users");
                exit;
            }

            if (Session::get('user_role') === 'admin' && in_array($role, ['superadmin'], true)) {
                Session::setFlash('error', \__('admin_cannot_manage_admin_roles'));
                header("Location: /users");
                exit;
            }

            if (Session::get('user_role') === 'admin' && $currentUser && $currentUser['role'] === 'superadmin') {
                Session::setFlash('error', \__('admin_cannot_manage_admin_roles'));
                header("Location: /users");
                exit;
            }

            $personnelData = $this->getPersonnelDataFromRequest($role);
            $submittedUser = array_merge($currentUser, compact('nom', 'prenom', 'username', 'email', 'role'), $personnelData);
            if ($personnelData['is_staff'] && ($validationError = $this->validatePersonnelData($personnelData, (int)$id))) {
                Session::setFlash('error', \__($validationError['message']));
                $errorField = $validationError['field'];
                $user = $submittedUser;
                include __DIR__ . '/../Views/users/edit.php';
                return;
            }

            try {
                if (!empty($password)) {
                    // Utilisation de l'algorithme définit dans le modèle
                    $dummyUser = new User();
                    $dummyUser->setPassword($password);
                    $hashedPassword = $dummyUser->getPassword();

                    $stmt = $this->db->prepare("UPDATE users SET nom = ?, prenom = ?, username = ?, email = ?, password = ?, role = ?, is_staff = ?, cni_number = ?, job_title = ?, base_salary = ?, nui_number = ? WHERE id = ?");
                    $stmt->execute([$nom, $prenom, $username, $email ?: null, $hashedPassword, $role, $personnelData['is_staff'], $personnelData['cni_number'], $personnelData['job_title'], $personnelData['base_salary'], $personnelData['nui_number'], $id]);
                } else {
                    $stmt = $this->db->prepare("UPDATE users SET nom = ?, prenom = ?, username = ?, email = ?, role = ?, is_staff = ?, cni_number = ?, job_title = ?, base_salary = ?, nui_number = ? WHERE id = ?");
                    $stmt->execute([$nom, $prenom, $username, $email ?: null, $role, $personnelData['is_staff'], $personnelData['cni_number'], $personnelData['job_title'], $personnelData['base_salary'], $personnelData['nui_number'], $id]);
                }

                $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $currentUserRole = $stmt->fetchColumn();

                Session::setFlash('success', \__('user_updated_success'));
                if ($currentUserRole === 'caissier') {
                    header("Location: /users/caissiers");
                } else {
                    header("Location: /users");
                }
                exit;
            } catch (\PDOException $e) {
                $duplicate = $this->getDuplicateField($e);
                if ($duplicate === 'cni_number' || $duplicate === 'nui_number') {
                    Session::setFlash('error', \__($duplicate === 'cni_number' ? 'staff_cni_taken' : 'staff_nui_taken'));
                    $errorField = $duplicate;
                    $user = $submittedUser;
                    include __DIR__ . '/../Views/users/edit.php';
                    return;
                }
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
            Session::setFlash('success', \__('user_deleted_success') ?: 'Compte supprimé avec succès.');
        }

        header("Location: /users");
        exit;
    }

    private function fetchUsersFromFilters()
    {
        $search = trim($_GET['q'] ?? '');
        $roleFilter = trim($_GET['role'] ?? '');
        $staffFilter = isset($_GET['staff']) && $_GET['staff'] === '1' ? 1 : null;

        if (Session::get('user_role') === 'admin' && in_array($roleFilter, ['superadmin', 'admin'], true)) {
            $roleFilter = '';
        }

        if (Session::get('user_role') === 'admin') {
            $sql = "SELECT id, nom, prenom, username, email, role, is_staff, job_title FROM users WHERE role NOT IN ('superadmin', 'admin')";
        } else {
            $sql = "SELECT id, nom, prenom, username, email, role, is_staff, job_title FROM users WHERE 1=1";
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
        if ($staffFilter !== null) {
            $sql .= " AND is_staff = ?";
            $params[] = $staffFilter;
        }

        $sql .= " ORDER BY nom ASC, prenom ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return [$stmt->fetchAll(PDO::FETCH_ASSOC), ['q' => $search, 'role' => $roleFilter, 'staff' => $staffFilter]];
    }

    private function getPersonnelDataFromRequest(string $role): array
    {
        $isStaff = isset($_POST['is_staff']) && $_POST['is_staff'] === '1';
        if ($role === 'personnel') {
            $isStaff = true;
        }
        if (!$isStaff) {
            return [
                'is_staff' => 0,
                'cni_number' => null,
                'job_title' => null,
                'base_salary' => null,
                'nui_number' => null,
            ];
        }

        return [
            'is_staff' => 1,
            'cni_number' => trim((string)($_POST['cni_number'] ?? '')),
            'job_title' => trim((string)($_POST['job_title'] ?? '')),
            'base_salary' => trim((string)($_POST['base_salary'] ?? '')),
            'nui_number' => trim((string)($_POST['nui_number'] ?? '')),
        ];
    }

    private function validatePersonnelData(array $data, ?int $userId = null): ?array
    {
        foreach (['cni_number', 'job_title', 'base_salary', 'nui_number'] as $field) {
            if ($data[$field] === '') {
                return ['message' => 'staff_required_fields', 'field' => $field];
            }
        }

        if (!is_numeric($data['base_salary']) || (float)$data['base_salary'] < 0) {
            return ['message' => 'staff_invalid_salary', 'field' => 'base_salary'];
        }

        foreach (['cni_number' => 'staff_cni_taken', 'nui_number' => 'staff_nui_taken'] as $field => $message) {
            $sql = "SELECT id FROM users WHERE {$field} = ?";
            $params = [$data[$field]];
            if ($userId !== null) {
                $sql .= " AND id <> ?";
                $params[] = $userId;
            }
            $stmt = $this->db->prepare($sql . " LIMIT 1");
            $stmt->execute($params);
            if ($stmt->fetchColumn()) {
                return ['message' => $message, 'field' => $field];
            }
        }

        return null;
    }

    private function getDuplicateField(\PDOException $exception): ?string
    {
        $message = strtolower($exception->getMessage());
        if (strpos($message, 'uq_users_cni_number') !== false) {
            return 'cni_number';
        }
        if (strpos($message, 'uq_users_nui_number') !== false) {
            return 'nui_number';
        }
        return null;
    }

    public function createCaissier()
    {
        include __DIR__ . '/../Views/users/create_caissier.php';
    }

    public function storeCaissier()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                \App\Core\Security::log("Tentative de CSRF détectée sur l'action User::storeCaissier");
                Session::setFlash('error', "Session expirée ou requête invalide.");
                header("Location: /users/caissiers");
                exit;
            }

            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = 'caissier';

            if (empty($nom) || empty($prenom) || empty($username) || empty($password)) {
                $error = \__('user_required_fields') ?: "Veuillez remplir tous les champs obligatoires.";
                Session::setFlash('error', $error);
                header("Location: /users/caissiers");
                exit;
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

                Session::setFlash('success', __('cashier_created_success'));
                
                $userRole = Session::get('user_role');
                if (in_array($userRole, ['superadmin', 'admin', 'caissier', 'comptable'])) {
                    header("Location: /users/caissiers");
                } else {
                    header("Location: /students");
                }
                exit;
            } catch (\PDOException $e) {
                $error = strpos($e->getMessage(), 'Duplicate') !== false ? \__('username_taken') : \__('internal_db_error');
                Session::setFlash('error', $error);
                header("Location: /users/caissiers");
                exit;
            }
        }
    }

    public function caissiers()
    {
        $search = trim($_GET['q'] ?? '');
        $statusFilter = $_GET['status'] ?? ''; // '1', '0' or ''

        $sql = "SELECT id, nom, prenom, username, email, status FROM users WHERE role = 'caissier'";
        $params = [];

        if ($search !== '') {
            $like = '%' . $search . '%';
            $sql .= " AND (nom LIKE ? OR prenom LIKE ? OR username LIKE ? OR email LIKE ?)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if ($statusFilter !== '') {
            $sql .= " AND status = ?";
            $params[] = (int)$statusFilter;
        }

        $sql .= " ORDER BY nom ASC, prenom ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $cashiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $filters = ['q' => $search, 'status' => $statusFilter];

        include __DIR__ . '/../Views/users/caissiers.php';
    }

    public function toggleStatus($id)
    {
        $stmt = $this->db->prepare("SELECT role, status FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            header("Location: /users/caissiers");
            exit;
        }

        // Security: admin cannot deactivate superadmin
        if (Session::get('user_role') === 'admin' && $user['role'] === 'superadmin') {
            header("Location: /users/caissiers");
            exit;
        }

        // Toggle status
        $newStatus = $user['status'] ? 0 : 1;
        $stmt = $this->db->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);

        Session::setFlash('success', __('status_updated_success'));

        if ($user['role'] === 'caissier') {
            header("Location: /users/caissiers");
        } else {
            header("Location: /users");
        }
        exit;
    }
}
