<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Core\LogoManager;
use App\Models\User;
use App\Services\ActivityTracker;
use App\Services\SettingsStore;
use PDO;

/**
 * Classe AuthController
 * 
 * Gère l'authentification des utilisateurs, incluant la connexion, la déconnexion et l'initialisation de la session.
 * 
 * @package App\Controllers
 */
class AuthController
{
    /**
     * Prépare les données et affiche la vue de connexion.
     * Centralisation (Refactoring Senior) pour éviter la duplication de code et respecter le pattern MVC.
     * 
     * @param string|null $error Message d'erreur éventuel à afficher
     */
    private function renderLoginView(?string $error = null)
    {
        // Génère un jeton CSRF pour sécuriser le formulaire
        $csrfToken = Session::generateCsrfToken();
        
        // Connexion à la base et récupération des configurations de l'école (Design System, Nom, etc.)
        $db = Database::getInstance()->getConnection();
        $settingsStore = new SettingsStore($db);
        $brandSettings = $settingsStore->all(); // On passe toutes les configs à la vue pour le CSS dynamique
        
        // Préparation des données du Logo
        $logoManager = LogoManager::getInstance($db);
        $logoData = [
            'has_logo' => $logoManager->hasLogo(),
            'url' => $logoManager->getLogoUrl(),
            'base64' => $logoManager->hasLogo() ? $logoManager->getLogoBase64() : '',
            'fallback_letter' => $logoManager->getFallbackLetter()
        ];
        
        include __DIR__ . '/../Views/auth/login.php';
    }

    /**
     * Affiche la page de connexion.
     * Redirige vers le tableau de bord si l'utilisateur est déjà authentifié.
     */
    public function loginView()
    {
        if (Session::isLogged()) {
            header("Location: /");
            exit;
        }

        $this->renderLoginView();
    }

    /**
     * Traite la soumission du formulaire de connexion.
     * Valide les identifiants et initialise la session.
     */
    public function loginPost()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!\App\Core\Security::checkRateLimit('login', 5, 300)) {
                $error = "Trop de tentatives de connexion. Veuillez patienter 5 minutes.";
                $this->renderLoginView($error);
                return;
            }

            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $submittedToken = $_POST['csrf_token'] ?? '';

            // 1. Vérification de sécurité CSRF
            if (!Session::verifyCsrfToken($submittedToken)) {
                \App\Core\Security::log("Échec de vérification CSRF lors de la connexion. IP suspendue ?");
                $error = "Session expirée ou requête invalide. Veuillez réessayer.";
                $this->renderLoginView($error);
                return;
            }

            // 2. Récupération des données utilisateur via le modèle
            $userModel = new User();
            $userData = $userModel->findByUsername($username);

            if ($userData) {
                // 2.1. Vérification du statut actif du compte
                if (isset($userData['status']) && (int)$userData['status'] === 0) {
                    \App\Core\Security::log("Tentative de connexion à un compte désactivé : $username");
                    $error = __('deactivated_login_error');
                    $this->renderLoginView($error);
                    return;
                }

                // Remplit le modèle avec les données de la BD
                $user = new User(
                    $userData['nom'],
                    $userData['prenom'],
                    $userData['username'],
                    $userData['email'],
                    $userData['password'],
                    $userData['role']
                );
                $user->setId($userData['id']);

                // 3. Vérification du mot de passe
                if ($user->verifyPassword($password)) {
                    // 3.1 Régénération de l'ID de session pour prévenir la fixation de session
                    Session::regenerate();

                    // Initialise les détails de la session
                    Session::set('user_id', $user->getId());
                    Session::set('user_role', $user->getRole());
                    Session::set('user_nom', $user->getNom());
                    Session::set('user_prenom', $user->getPrenom());

                    // Message de bienvenue flash
                    Session::setFlash('welcome_user', __('welcome_back', ['name' => $user->getPrenom()]));

                    // Configure le contexte de l'année académique active globale
                    $db = Database::getInstance()->getConnection();
                    $activeYearQuery = $db->query("SELECT nom FROM academic_years WHERE is_active = 1 LIMIT 1");
                    $activeYearName = $activeYearQuery->fetchColumn();
                    Session::set('active_year_name', $activeYearName ?: 'Non définie');
                    
                    \App\Core\Security::log("Connexion réussie pour l'utilisateur : $username");
                    (new ActivityTracker($db))->recordLogin((int) $user->getId(), $user->getRole());

                    // Redirection après connexion réussie
                    header("Location: /");
                    exit;
                }
            }

            // Échec de l'authentification
            \App\Core\Security::log("Échec de connexion pour l'utilisateur : $username");
            $error = "Identifiant ou mot de passe incorrect.";
            $this->renderLoginView($error);
        }
    }

    /**
     * Termine la session de l'utilisateur et redirige vers la page de connexion.
     */
    public function logout()
    {
        Session::destroy();
        header("Location: /login");
        exit;
    }

    /**
     * Affiche le formulaire d'inscription pour les enseignants.
     */
    public function registerTeacherView(?string $error = null)
    {
        $db = Database::getInstance()->getConnection();
        $settingsStore = new SettingsStore($db);
        $brandSettings = $settingsStore->all();
        
        // Sécurité : Vérifie si la fonctionnalité est activée
        if (!isset($brandSettings['allow_teacher_registration']) || $brandSettings['allow_teacher_registration'] != '1') {
            header("Location: /login");
            exit;
        }

        $csrfToken = Session::generateCsrfToken();
        
        $logoManager = LogoManager::getInstance($db);
        $logoData = [
            'has_logo' => $logoManager->hasLogo(),
            'url' => $logoManager->getLogoUrl(),
            'base64' => $logoManager->hasLogo() ? $logoManager->getLogoBase64() : '',
            'fallback_letter' => $logoManager->getFallbackLetter()
        ];
        
        // Mode pour différencier l'inscription du login dans la vue
        $isRegister = true;
        
        include __DIR__ . '/../Views/auth/register_teacher.php';
    }

    /**
     * Traite la création d'un compte enseignant.
     */
    public function registerTeacherPost()
    {
        $db = Database::getInstance()->getConnection();
        $settingsStore = new SettingsStore($db);
        $brandSettings = $settingsStore->all();
        
        // Sécurité : Vérifie si la fonctionnalité est activée
        if (!isset($brandSettings['allow_teacher_registration']) || $brandSettings['allow_teacher_registration'] != '1') {
            header("Location: /login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!\App\Core\Security::checkRateLimit('register', 5, 300)) {
                $error = "Trop de tentatives. Veuillez patienter 5 minutes.";
                $this->registerTeacherView($error);
                return;
            }

            $submittedToken = $_POST['csrf_token'] ?? '';
            if (!Session::verifyCsrfToken($submittedToken)) {
                $error = "Session expirée ou requête invalide. Veuillez réessayer.";
                $this->registerTeacherView($error);
                return;
            }

            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';

            if (empty($username) || empty($password)) {
                $error = "Le nom d'utilisateur et le mot de passe sont obligatoires.";
                $this->registerTeacherView($error);
                return;
            }

            // Simplification : Seuls username et password sont collectés.
            // Le 'nom' est souvent requis (NOT NULL) en base de données, donc on utilise le 'username' par défaut.
            $nom = $username;
            $prenom = '';
            $email = null;

            if ($password !== $password_confirm) {
                $error = "Les mots de passe ne correspondent pas.";
                $this->registerTeacherView($error);
                return;
            }

            try {
                $pwdHash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $db->prepare("INSERT INTO users (nom, prenom, username, email, password, role) VALUES (?, ?, ?, ?, ?, 'enseignant')");
                $stmt->execute([$nom, $prenom, $username, $email ?: null, $pwdHash]);
                
                $userId = $db->lastInsertId();
                
                // Connexion automatique après inscription réussie
                Session::regenerate();
                Session::set('user_id', $userId);
                Session::set('user_role', 'enseignant');
                Session::set('user_nom', $nom);
                Session::set('user_prenom', $prenom);
                
                // Message de bienvenue flash
                Session::setFlash('welcome_user', __('welcome_back', ['name' => $username]));

                // Configurer l'année active
                $activeYearQuery = $db->query("SELECT nom FROM academic_years WHERE is_active = 1 LIMIT 1");
                $activeYearName = $activeYearQuery->fetchColumn();
                Session::set('active_year_name', $activeYearName ?: 'Non définie');
                
                \App\Core\Security::log("Nouvelle inscription enseignant réussie : $username");
                (new ActivityTracker($db))->recordLogin((int) $userId, 'enseignant');
                
                header("Location: /");
                exit;
            } catch (\PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate') !== false) {
                    $error = "Ce nom d'utilisateur ou cet email est déjà utilisé.";
                } else {
                    $error = "Une erreur technique est survenue lors de l'inscription.";
                }
                $this->registerTeacherView($error);
            }
        }
    }
}
