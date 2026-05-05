<?php

declare(strict_types=1);

// Resynchronisation des matricules des eleves selon les parametres generaux.
// Le champ students.email est utilise comme champ de matricule dans ce projet.

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

// Mise en place des regles minimum si elles sont absentes.
$pdo->exec("CREATE TABLE IF NOT EXISTS settings (setting_key VARCHAR(50) PRIMARY KEY, setting_value TEXT)");
$pdo->exec("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
    ('school_code', 'CMR-COL'),
    ('matricule_format', '{SCHOOL_CODE}-{CLASS}-MT{COUNTER}'),
    ('matricule_counter', '1')");

$settings = [];
foreach ($pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('school_code', 'matricule_format', 'matricule_counter')") as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$schoolCode = trim((string) ($settings['school_code'] ?? 'CMR-COL'));
$format = trim((string) ($settings['matricule_format'] ?? '{SCHOOL_CODE}-{CLASS}-MT{COUNTER}'));
$counter = max(1, (int) ($settings['matricule_counter'] ?? 1));

$students = $pdo->query("
    SELECT st.id, st.class_id, c.nom AS class_nom
    FROM students st
    LEFT JOIN classes c ON c.id = st.class_id
    ORDER BY st.id ASC
")->fetchAll();

$used = [];
$update = $pdo->prepare("UPDATE students SET email = ? WHERE id = ?");

$pdo->beginTransaction();

try {
    foreach ($students as $student) {
        // Construction du token classe a partir du nom de la classe de l'eleve.
        $className = (string) ($student['class_nom'] ?? 'IND');
        $normalized = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $className);
        if ($normalized !== false && $normalized !== '') {
            $className = $normalized;
        }
        $classToken = strtoupper((string) preg_replace('/[^A-Z0-9]+/i', '', $className));
        if ($classToken === '') {
            $classToken = 'IND';
        }

        // Recherche du prochain matricule libre.
        while (true) {
            $counterPad = str_pad((string) $counter, 6, '0', STR_PAD_LEFT);
            $matricule = str_replace(
                ['{SCHOOL_CODE}', '{CLASS}', '{COUNTER}'],
                [$schoolCode !== '' ? $schoolCode : 'CMR-COL', $classToken, $counterPad],
                $format
            );

            if (!isset($used[$matricule])) {
                $used[$matricule] = true;
                $counter++;
                break;
            }

            $counter++;
        }

        // Mise a jour du matricule de l'eleve courant.
        $update->execute([$matricule, $student['id']]);
    }

    // Sauvegarde du prochain compteur global.
    $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'matricule_counter'")
        ->execute([$counter]);

    $pdo->commit();
    echo "Matricules resynchronises avec succes.\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
