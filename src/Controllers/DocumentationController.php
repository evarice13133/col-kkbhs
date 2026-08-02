<?php

namespace App\Controllers;

use App\Core\Session;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * DocumentationController
 * 
 * Ce contrôleur gère la génération des manuels utilisateurs en PDF.
 * Les guides sont segmentés par rôle (Enseignant, Admin, Superadmin)
 * pour offrir une expérience simplifiée et pédagogique.
 */
class DocumentationController
{
    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }

    /**
     * Résout le chemin du fichier de manuel avec fallback sécurisé.
     */
    private function getManualPath(string $role, string $lang): string
    {
        $baseDir = __DIR__ . '/../Views/documentation/';
        $file = $baseDir . "manual_{$role}_{$lang}.php";
        if (file_exists($file)) {
            return $file;
        }
        $fallbackLang = $baseDir . "manual_{$role}_fr.php";
        if (file_exists($fallbackLang)) {
            return $fallbackLang;
        }
        return $baseDir . "manual_admin_fr.php";
    }

    /**
     * Affiche l'interface de consultation de la documentation.
     */
    public function index()
    {
        $role = Session::get('user_role');
        $lang = \App\Core\Locale::get();
        
        // Préparation du libellé du rôle pour la vue
        $roleLabels = [
            'superadmin' => __('superadmin'),
            'admin' => __('admin'),
            'it_manager' => 'IT Manager',
            'comptable' => 'Comptable',
            'caissier' => 'Caissier',
            'enseignant' => __('teacher_role'),
        ];
        $roleLabel = $roleLabels[$role] ?? ucfirst((string) $role);
        
        // Charger le contenu pour l'affichage direct avec fallback
        $manualFile = $this->getManualPath((string) $role, (string) $lang);
        ob_start();
        include $manualFile;
        $manual_content = ob_get_clean();

        // Extraire le CSS
        $manual_css = '';
        if (preg_match('/<style[^>]*>(.*?)<\/style>/is', $manual_content, $matches)) {
            $manual_css = $matches[1];
        }

        // Extraire seulement le contenu du body pour l'injecter proprement
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $manual_content, $matches)) {
            $manual_body = $matches[1];
        } else {
            $manual_body = $manual_content;
        }
        
        include __DIR__ . '/../Views/documentation/index.php';
    }

    /**
     * Génère et télécharge le PDF du manuel correspondant au rôle de l'utilisateur.
     */
    public function download()
    {
        $role = Session::get('user_role');
        $lang = \App\Core\Locale::get();

        // Préparation du contenu HTML basé sur le rôle et la langue
        $manualFile = $this->getManualPath((string) $role, (string) $lang);
        ob_start();
        include $manualFile;
        $html = ob_get_clean();

        // Configuration de Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // Pour le logo et les styles
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);

        // Format A4, Portrait
        $dompdf->setPaper('A4', 'portrait');

        // Rendu du PDF
        $dompdf->render();

        // Envoi au navigateur
        $filename = "Guide_NotesMaster_" . ucfirst($role) . "_" . strtoupper($lang) . ".pdf";
        $dompdf->stream($filename, ["Attachment" => true]);
        exit;
    }
}
