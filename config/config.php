<?php

/**
 * Configuration Globale de NotesMaster
 * Ce fichier contient les identifiants de base de données et les constantes d'environnement.
 * Il est recommandé de garder ce fichier hors de la vue du public.
 */
$appEnv = strtolower(getenv('APP_ENV') ?: 'production');

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'u290233073_col_col_kkbhs_db1');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost:8000');
define('APP_ENV', $appEnv);
define('DEBUG_MODE', $appEnv === 'development' && filter_var(getenv('DEBUG_MODE') ?: '1', FILTER_VALIDATE_BOOLEAN));

ini_set('display_errors', DEBUG_MODE ? '1' : '0');
ini_set('display_startup_errors', DEBUG_MODE ? '1' : '0');
error_reporting(E_ALL);

//   git@github.com:evarice13133/futura.camertech.git    -     https://webhooks.hostinger.com/deploy/5c268448f78945b471bbef333ea10955
//   git@github.com:evarice13133/col-kkbhs.git           -     https://webhooks.hostinger.com/deploy/cd6d548d7e495bd4ec18fa3c3f7b2e44
