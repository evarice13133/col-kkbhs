<?php

/**
 * Configuration Globale de NotesMaster
 * Ce fichier contient les identifiants de base de données et les constantes d'environnement.
 * Il est recommandé de garder ce fichier hors de la vue du public.
 */
/* paramettres de configuration du serveur de données en ligne */

  define('DB_HOST', 'localhost');
   define('DB_NAME', 'u290233073_col_kkbhs_db');
   define('DB_USER', 'u290233073_root_kkbhs');
   define('DB_PASS', 'Tezempa13133!!');
   define('DB_CHARSET', 'utf8mb4');
   define('APP_URL', 'https://futura.camertech.com');

   define('APP_ENV', 'production');
   define('DEBUG_MODE', false);   //evite l'affichage des erreures PHP au utilisateurs en production


/* en developpement pour la configuration locale 
   
   define('DB_HOST', 'localhost');
    define('DB_NAME', 'u290233073_col_col_kkbhs_db1');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4'); 
    define('APP_URL', 'http://localhost:8000');

    define('APP_ENV', 'development');
    define('DEBUG_MODE', true);  */   //affichage des erreures PHP au developpeur

/* Force error display for debugging    
  
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);
*/ 
//   git@github.com:evarice13133/futura.camertech.git    -     https://webhooks.hostinger.com/deploy/5c268448f78945b471bbef333ea10955
//   git@github.com:evarice13133/col-kkbhs.git           -     https://webhooks.hostinger.com/deploy/cd6d548d7e495bd4ec18fa3c3f7b2e44
