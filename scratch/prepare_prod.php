<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Démarrage du nettoyage de la base de données pour mise en production...\n";

    // Désactivation temporaire des contraintes de clé étrangère pour permettre le TRUNCATE
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Liste des tables à vider (ordre logique pour la prod)
    $tables = [
        'grades',               // Notes (dépend de élèves et matières)
        'discipline',           // Discipline (dépend de élèves)
        'teacher_assignments',  // Affectations (dépend de matières, classes, profs)
        'subject_classes',      // Association Matières/Classes
        'students',             // Élèves
        'subjects',             // Matières
        'classes',              // Classes
        'activity_logs'         // Logs d'activité (Dashboard stats)
    ];

    foreach ($tables as $table) {
        echo "Vidage de la table : $table... ";
        $db->exec("TRUNCATE TABLE `$table` ");
        echo "OK\n";
    }

    echo "Suppression des comptes enseignants... ";
    $db->exec("DELETE FROM users WHERE role = 'enseignant'");
    echo "OK\n";

    // Réactivation des contraintes
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "\nBase de données nettoyée avec succès ! Les structures sont conservées, mais les données académiques sont supprimées.\n";

} catch (\Exception $e) {
    echo "ERREUR : " . $e->getMessage() . "\n";
}
