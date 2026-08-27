<?php
// Migration non destructive
// Objectif: assurer que la table `subjects` a une PRIMARY KEY `id` et que la colonne est AUTO_INCREMENT.
// - Ne supprime pas de données
// - Ne réinitialise pas d'ID existants
// - Ne s'exécute pas si des doublons d'ID sont détectés (intervention manuelle requise)

// Charger l'autoload et la configuration minimale
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

// Initialiser une connexion PDO minimale en utilisant la configuration existante
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
try {
    $db = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    echo "Impossible d'établir la connexion à la base de données: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Migration: fix subjects AUTO_INCREMENT/PK\n";

try {
    // Vérifier s'il existe des doublons d'ID
    $dupStmt = $db->query("SELECT id, COUNT(*) AS c FROM subjects GROUP BY id HAVING c > 1");
    $dup = $dupStmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($dup)) {
        echo "Doublons d'ID détectés dans la table subjects. Abandon de la migration.\n";
        echo "Exemples: \n";
        foreach ($dup as $r) {
            echo "id={$r['id']} count={$r['c']}\n";
        }
        exit(1);
    }

    // Faire les modifications (sans transaction car ALTER TABLE provoque des commits implicites)
    try {
        $db->exec("ALTER TABLE subjects MODIFY id INT(11) NOT NULL AUTO_INCREMENT");
    } catch (PDOException $e) {
        echo "WARN: échec du MODIFY id AUTO_INCREMENT : " . $e->getMessage() . "\n";
    }

    try {
        $db->exec("ALTER TABLE subjects ADD PRIMARY KEY (id)");
    } catch (PDOException $e) {
        echo "WARN: échec de l'ajout de PRIMARY KEY (déjà présente ou données incohérentes) : " . $e->getMessage() . "\n";
    }

    // Synchroniser AUTO_INCREMENT
    $maxId = (int) $db->query("SELECT COALESCE(MAX(id),0) FROM subjects")->fetchColumn();
    $next = $maxId + 1;
    try {
        $db->exec("ALTER TABLE subjects AUTO_INCREMENT = " . (int)$next);
        echo "AUTO_INCREMENT synchronisé à $next\n";
    } catch (PDOException $e) {
        echo "WARN: échec de la synchronisation AUTO_INCREMENT : " . $e->getMessage() . "\n";
    }

    echo "Migration terminée avec succès.\n";
    exit(0);
} catch (Exception $e) {
    echo "Erreur lors de la migration : " . $e->getMessage() . "\n";
    exit(1);
}
