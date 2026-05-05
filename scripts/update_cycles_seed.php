<?php

declare(strict_types=1);

// Mise a jour directe de la base locale pour conserver uniquement les cycles
// "1ere Cycle" et "2nd Cycle", puis reaffecter les classes existantes.

$host = 'localhost';
$db = 'notesmasterdb';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

$pdo = new PDO($dsn, $user, $pass, $options);

// Creation des deux cycles attendus s'ils n'existent pas encore.
$pdo->exec("
    INSERT IGNORE INTO cycles (nom) VALUES
    ('1ere Cycle'),
    ('2nd Cycle')
");

// Recuperation des identifiants utiles pour les reaffectations.
$cycleStmt = $pdo->query("SELECT id, nom FROM cycles WHERE nom IN ('1ere Cycle', '2nd Cycle')");
$cycleMap = [];
foreach ($cycleStmt->fetchAll() as $row) {
    $cycleMap[$row['nom']] = (int) $row['id'];
}

if (!isset($cycleMap['1ere Cycle'], $cycleMap['2nd Cycle'])) {
    throw new RuntimeException('Impossible de recuperer les cycles cibles.');
}

// Affectation du premier cycle aux classes du premier cycle.
$pdo->prepare("
    UPDATE classes
    SET cycle_id = ?
    WHERE nom REGEXP '^(6eme|5eme|4eme|3eme|Form 1|Form 2|Form 3)'
")->execute([$cycleMap['1ere Cycle']]);

// Affectation du second cycle aux classes du second cycle general et technique.
$pdo->prepare("
    UPDATE classes
    SET cycle_id = ?
    WHERE nom REGEXP '^(2nde|1ere|Tle|Form 4|Form 5|Lower Sixth|Upper Sixth|2eme |3eme )'
       OR nom LIKE '% BAT %'
       OR nom LIKE '% ELE %'
       OR nom LIKE '% MECA %'
       OR nom LIKE '% INFO %'
       OR nom LIKE '% GESTI %'
       OR nom LIKE '% SOU %'
       OR nom LIKE '% COME %'
       OR nom LIKE '% SEME %'
       OR nom LIKE '% ESCOM %'
")->execute([$cycleMap['2nd Cycle']]);

// Synchronisation des eleves pour qu'ils portent le meme cycle que leur classe.
$pdo->exec("
    UPDATE students st
    JOIN classes c ON c.id = st.class_id
    SET st.cycle_id = c.cycle_id
");

// Suppression des anciens cycles qui ne doivent plus exister.
$pdo->exec("
    DELETE FROM cycles
    WHERE nom NOT IN ('1ere Cycle', '2nd Cycle')
");

echo "Cycles mis a jour avec succes.\n";
