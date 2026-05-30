<?php

/**
 * Configuration Globale de NotesMaster
 * Ce fichier contient les identifiants de base de données et les constantes d'environnement.
 * Il est recommandé de garder ce fichier hors de la vue du public.
 */

         define('DB_HOST', 'localhost');
         define('DB_NAME', 'u290233073_copobimat_db_2');
         define('DB_USER', 'u290233073_copobimat_db_2');
         define('DB_PASS', 'Tezempa12133!!');
         define('DB_CHARSET', 'utf8mb4');
         define('APP_URL', 'https://copobimat.camertech.com');

         define('APP_ENV', 'production');
         define('DEBUG_MODE', false);

// en developpement pour la configuration locale
        // define('DB_HOST', 'localhost');
        // define('DB_NAME', 'notemaster_imt');
        //  define('DB_USER', 'root');
        //  define('DB_PASS', '');
        //  define('DB_CHARSET', 'utf8mb4'); 
        //  define('APP_URL', 'http://localhost:8000');

        //  define('APP_ENV', 'development');
        //  define('DEBUG_MODE', true);

// Force error display for debugging
   // ini_set('display_errors', 1);
   // ini_set('display_startup_errors', 1);
   // error_reporting(E_ALL);