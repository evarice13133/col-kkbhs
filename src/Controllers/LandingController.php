<?php

namespace App\Controllers;

use App\Core\Database;
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
        $school_name = $this->settingsStore->get('school_name', 'Copobimat');
        
        // Variables SEO
        $title = "Logiciel de Gestion Scolaire au Cameroun";
        $meta_description = "Copobimat : Solution complète de gestion scolaire au Cameroun. Gestion des notes, bulletins automatiques, suivi des élèves et des enseignants. Simplifiez votre administration dès aujourd'hui.";
        
        include __DIR__ . '/../Views/landing/index.php';
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
