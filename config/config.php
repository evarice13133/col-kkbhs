<?php

/**
 * Configuration Globale de NotesMaster
 * Ce fichier contient les identifiants de base de données et les constantes d'environnement.
 * Il est recommandé de garder ce fichier hors de la vue du public.
 */
/* paramettres de configuration du serveur de données en ligne

  define('DB_HOST', 'localhost');
   define('DB_NAME', 'u290233073_col_kkbhs_db');
   define('DB_USER', 'u290233073_root_kkbhs');
   define('DB_PASS', 'Tezempa13133!!');
   define('DB_CHARSET', 'utf8mb4');
   define('APP_URL', 'https://futura.camertech.com');

   define('APP_ENV', 'production');
   define('DEBUG_MODE', false); */   //evite l'affichage des erreures PHP au utilisateurs en production


/* en developpement pour la configuration locale  */   
   
   define('DB_HOST', 'localhost');
    define('DB_NAME', 'kemuel_prod1');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4'); 
    define('APP_URL', 'http://localhost:8000');

    define('APP_ENV', 'development');
    define('DEBUG_MODE', true);  //affichage des erreures PHP au developpeur

/* Force error display for debugging    */
  
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);
 
//   git@github.com:evarice13133/futura.camertech.git    -     https://webhooks.hostinger.com/deploy/5c268448f78945b471bbef333ea10955
//   git@github.com:evarice13133/col-kkbhs.git           -     https://webhooks.hostinger.com/deploy/cd6d548d7e495bd4ec18fa3c3f7b2e44

// WhatsApp credentials and account details are supplied per installation.
if (!defined('WHATSAPP_TEST_MODE')) {
    define('WHATSAPP_TEST_MODE', getenv('WHATSAPP_TEST_MODE') !== false ? (bool) filter_var(getenv('WHATSAPP_TEST_MODE'), FILTER_VALIDATE_BOOLEAN) : true);
}
if (!defined('WHATSAPP_BUSINESS_PHONE_NUMBER')) {
    define('WHATSAPP_BUSINESS_PHONE_NUMBER', getenv('WHATSAPP_BUSINESS_PHONE_NUMBER') ?: '');
}
if (!defined('WHATSAPP_PHONE_NUMBER_ID')) {
    define('WHATSAPP_PHONE_NUMBER_ID', getenv('WHATSAPP_PHONE_NUMBER_ID') ?: (getenv('WHATSAPP_PHONE_ID') ?: ''));
}
if (!defined('WHATSAPP_ACCESS_TOKEN')) {
    define('WHATSAPP_ACCESS_TOKEN', getenv('WHATSAPP_ACCESS_TOKEN') ?: (getenv('WHATSAPP_TOKEN') ?: ''));
}
if (!defined('WHATSAPP_WEBHOOK_VERIFY_TOKEN')) {
    define('WHATSAPP_WEBHOOK_VERIFY_TOKEN', getenv('WHATSAPP_WEBHOOK_VERIFY_TOKEN') ?: '');
}
if (!defined('WHATSAPP_API_BASE_URL')) {
    $whatsappApiVersion = getenv('WHATSAPP_API_VERSION') ?: 'v20.0';
    define('WHATSAPP_API_BASE_URL', getenv('WHATSAPP_API_BASE_URL') ?: 'https://graph.facebook.com/' . trim($whatsappApiVersion, '/'));
}
if (!defined('WHATSAPP_TEMPLATE_NAME')) {
    define('WHATSAPP_TEMPLATE_NAME', getenv('WHATSAPP_TEMPLATE_NAME') ?: 'notemaster_notification');
}
if (!defined('WHATSAPP_APP_ID')) {
    define('WHATSAPP_APP_ID', getenv('WHATSAPP_APP_ID') ?: '');
}
if (!defined('WHATSAPP_APP_SECRET')) {
    define('WHATSAPP_APP_SECRET', getenv('WHATSAPP_APP_SECRET') ?: '');
}
