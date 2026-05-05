<?php

declare(strict_types=1);

// Remplacement des noms artificiels par de vrais noms d'apprenants.
// Le champ nom est force en majuscules pour harmoniser l'affichage
// sur les listes, exports et bulletins.

$pdo = new PDO(
    'mysql:host=localhost;dbname=notesmasterdb;charset=utf8mb4',
    'root',
    '',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);

$lastNames = [
    'TAMAFO', 'KAMENI', 'FOFACK', 'TEZEMPA', 'BONO', 'NJOYA', 'FOTSO', 'TCHUENTE',
    'NGAHA', 'SIMO', 'KOUAM', 'NKOULOU', 'MBOCK', 'MBIDA', 'NDJANA', 'MEFIRE',
    'YEMELE', 'NDEDJI', 'TSAGUE', 'TCHANA', 'MOUAFO', 'ESSOMBA', 'ABENA', 'MBOGNING',
    'DJOMO', 'KOUEMOU', 'MBANG', 'MENGUE', 'TSAFACK', 'NGASSA', 'KEMAYOU', 'MOUELLE',
    'ATCHA', 'TCHUENKAM', 'NLEPO', 'TCHAKOUNTE', 'NANA', 'MBOUA', 'WAMBA', 'ELLA',
];

$firstNames = [
    'Jordan', 'Jean Jonas', 'Loris Elessa', 'Maeva', 'Nathan', 'Diane', 'Raissa',
    'Cedric', 'Boris', 'Noeline', 'Estelle', 'Gael', 'Mireille', 'Fabrice',
    'Christelle', 'Ulrich', 'Yvan', 'Prisca', 'Brice', 'Lydie', 'Arielle',
    'Blaise', 'Ruth', 'Junior', 'Giselle', 'Steve', 'Josiane', 'Boris Landry',
    'Jean Claude', 'Merveille', 'Eloise', 'Kevin', 'Sonia', 'Naomi', 'Loic',
    'Patrick', 'Aicha', 'Florian', 'Vanessa', 'Rachelle', 'Didier', 'Carine',
    'Lionel', 'Murielle', 'Samuel', 'Kelly', 'Jessica', 'Willy', 'Michaelle',
];

$students = $pdo->query("SELECT id FROM students ORDER BY id ASC")->fetchAll();
$update = $pdo->prepare("UPDATE students SET nom = ?, prenom = ? WHERE id = ?");

$pdo->beginTransaction();

try {
    foreach ($students as $index => $student) {
        // Attribution deterministe d'un vrai nom afin d'eviter des doublons grossiers.
        $lastName = $lastNames[$index % count($lastNames)];
        $firstName = $firstNames[$index % count($firstNames)];

        // Si l'on depasse la taille des banques, on combine deux prenoms pour diversifier.
        if ($index >= count($firstNames)) {
            $secondName = $firstNames[($index + 7) % count($firstNames)];
            $parts = explode(' ', $secondName);
            $firstName .= ' ' . $parts[0];
        }

        $update->execute([$lastName, $firstName, $student['id']]);
    }

    $pdo->commit();
    echo "Noms reels appliques avec succes.\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
