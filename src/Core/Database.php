<?php

namespace App\Core;

// Classes standard de PHP
use PDO;
use PDOException;

/**
 * Classe Database
 * 
 * Implémente le pattern Singleton pour gérer une instance unique de connexion PDO.
 * Cela garantit une réutilisation efficace de la connexion pendant tout le cycle de vie de la requête.
 *
 * @package App\Core
 */
class Database {
    /** @var Database|null Instance Singleton de cette classe */
    private static $instance = null;

    /** @var PDO La connexion PDO active */
    private $pdo;

    /**
     * Constructeur de Database.
     * Établit une connexion PDO sécurisée à l'aide des identifiants dans config/config.php.
     * Constructeur privé pour empêcher l'instanciation manuelle.
     */
    private function __construct() {
        // Chargement des constantes globales
        $host = DB_HOST;
        $db   = DB_NAME;
        $user = DB_USER;
        $pass = DB_PASS;
        $charset = DB_CHARSET;

        // Configuration de la chaîne de connexion (DSN)
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        
        /** @var array $options Paramètres de comportement de PDO */
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lance des exceptions en cas d'erreurs SQL
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Récupère des tableaux associatifs par défaut
            PDO::ATTR_EMULATE_PREPARES   => false,                 // Utilise les instructions préparées natives
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // L'erreur fatale en développement doit être claire, mais masquée en production.
            if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
                http_response_code(500);
                die("Échec de la connexion critique : " . $e->getMessage() . " (Vérifiez les identifiants de config.php)");
            } else {
                // Log l'erreur réelle (idéalement vers un fichier)
                error_log("DATABASE ERROR: " . $e->getMessage());
                http_response_code(500);
                die("Une erreur de base de données critique est survenue. Veuillez contacter l'administrateur système.");
            }
        }
    }

    /**
     * Retourne l'instance Singleton unique.
     * 
     * @return Database L'instance unique de Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Fournit l'objet de connexion PDO brut.
     * 
     * @return PDO L'instance de connexion active
     */
    public function getConnection() {
        return $this->pdo;
    }
}
