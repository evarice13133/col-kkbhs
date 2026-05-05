<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Classe BaseModel
 * 
 * Fournit une connexion partagée à la base de données et des méthodes utilitaires pour tous les modèles.
 * Garantit que tous les modèles dérivés utilisent la même connexion Singleton.
 */
abstract class BaseModel {
    /** @var PDO La connexion active à la base de données */
    protected $db;

    /**
     * Constructeur du BaseModel.
     * Initialise la connexion à la base de données.
     */
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
}
