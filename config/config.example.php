<?php

/**
 * Configuration Globale de NotesMaster (Modèle de configuration)
 * Dupliquez ce fichier vers config/config.php et adaptez les paramètres pour votre environnement.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'notesmaster_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('APP_URL', 'http://localhost:8000');

define('APP_ENV', 'development'); // 'development' ou 'production'
define('DEBUG_MODE', true); // Passera à false en production
