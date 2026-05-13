<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Services\SettingsStore;

class LandingController
{
    private $db;
    private $settingsStore;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->settingsStore = new SettingsStore($this->db);
    }

    /**
     * Affiche la page d'accueil publique optimisée pour le SEO
     */
    public function index()
    {
        $school_name = $this->settingsStore->get('school_name', 'NoteMaster');
        
        // Variables SEO optimisées pour Camertech & NoteMaster
        $title = "Camertech - NoteMaster : Logiciel de Gestion Scolaire de Référence au Cameroun";
        $meta_description = "Découvrez NoteMaster par Camertech : la solution pour la gestion de votre établissement scolaire au Cameroun. Saisie des notes, bulletins automatiques et suivi complet.";
        
        include __DIR__ . '/../Views/landing/index.php';
    }

    /**
     * Gère l'envoi du formulaire de contact (Sans DB, via JSON Log)
     */
    public function sendContact()
    {
        header('Content-Type: application/json');
        
        try {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $message = trim($_POST['message'] ?? '');

            if (empty($name) || empty($email) || empty($message)) {
                throw new \Exception("Tous les champs obligatoires doivent être remplis.");
            }

            $notification = [
                'id' => uniqid(),
                'type' => 'contact',
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'city' => $city,
                'message' => $message,
                'created_at' => date('Y-m-d H:i:s'),
                'read' => false
            ];

            // Stockage ultra-rapide en fichier JSON
            $logPath = __DIR__ . '/../../logs/notifications.json';
            $notifications = [];
            
            if (file_exists($logPath)) {
                $content = file_get_contents($logPath);
                $notifications = json_decode($content, true) ?: [];
            }

            // Ajouter au début (plus récent d'abord)
            array_unshift($notifications, $notification);
            
            // Garder seulement les 50 derniers messages pour la performance
            $notifications = array_slice($notifications, 0, 50);

            file_put_contents($logPath, json_encode($notifications, JSON_PRETTY_PRINT));

            // ENVOI D'EMAIL (Notification SuperAdmin)
            $to = "evaricekuete2@gmail.com";
            $subject = "Nouvelle demande de démo : " . $name;
            $body = "Vous avez reçu une nouvelle demande de démo via la vitrine NoteMaster.\n\n" .
                    "Établissement: " . $name . "\n" .
                    "Email: " . $email . "\n" .
                    "Téléphone: " . ($phone ?: 'Non précisé') . "\n" .
                    "Ville: " . ($city ?: 'Non précisé') . "\n" .
                    "Message:\n" . $message . "\n\n" .
                    "Gérez vos notifications ici : https://notemaster.camertech.com/dashboard";
            $headers = "From: no-reply@camertech.com\r\n" .
                       "Reply-To: " . $email . "\r\n" .
                       "X-Mailer: PHP/" . phpversion();

            @mail($to, $subject, $body, $headers);

            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Archive ou restaure une notification
     */
    public function toggleArchiveNotification()
    {
        header('Content-Type: application/json');
        $role = Session::get('user_role');
        if ($role !== 'superadmin' && $role !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Accès refusé']);
            exit;
        }

        $id = $_GET['id'] ?? '';
        $logPath = __DIR__ . '/../../logs/notifications.json';
        
        if (file_exists($logPath)) {
            $notifications = json_decode(file_get_contents($logPath), true) ?: [];
            foreach ($notifications as &$notif) {
                if ($notif['id'] === $id) {
                    $notif['archived'] = !($notif['archived'] ?? false);
                    break;
                }
            }
            file_put_contents($logPath, json_encode($notifications, JSON_PRETTY_PRINT));
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Fichier introuvable']);
        }
    }

    /**
     * Supprime définitivement une notification
     */
    public function deleteNotification()
    {
        header('Content-Type: application/json');
        $role = Session::get('user_role');
        if ($role !== 'superadmin' && $role !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Accès refusé']);
            exit;
        }

        $id = $_GET['id'] ?? '';
        $logPath = __DIR__ . '/../../logs/notifications.json';
        
        if (file_exists($logPath)) {
            $notifications = json_decode(file_get_contents($logPath), true) ?: [];
            $notifications = array_filter($notifications, fn($n) => $n['id'] !== $id);
            file_put_contents($logPath, json_encode(array_values($notifications), JSON_PRETTY_PRINT));
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Fichier introuvable']);
        }
    }

    /**
     * Page spécifique : Gestion des notes
     */
    public function marks()
    {
        $title = "Logiciel de gestion des notes et bulletins au Cameroun";
        include __DIR__ . '/../Views/landing/marks.php';
    }
}
