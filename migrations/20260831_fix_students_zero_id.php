<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

if (!$db->query("SHOW TABLES LIKE 'students'")->fetchColumn()) {
    echo "Table students absente, migration ignoree.\n";
    exit(0);
}

$zeroIds = $db->query("SELECT id FROM students WHERE id = 0")->fetchAll(PDO::FETCH_COLUMN);
if ($zeroIds) {
    $nextId = (int) $db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM students")->fetchColumn();
    $update = $db->prepare("UPDATE students SET id = ? WHERE id = 0");
    foreach ($zeroIds as $_) {
        $update->execute([$nextId++]);
    }
}

$db->exec("ALTER TABLE students MODIFY id INT(11) NOT NULL AUTO_INCREMENT");
$nextId = (int) $db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM students")->fetchColumn();
$db->exec("ALTER TABLE students AUTO_INCREMENT = " . max(1, $nextId));

try {
    $db->exec("DROP INDEX uniq_students_email ON students");
} catch (Throwable $e) {
    // L'index legacy n'existe pas ou a déjà été remplacé.
}

try {
    $db->exec("CREATE UNIQUE INDEX uniq_students_email_year ON students(email, academic_year_id)");
} catch (Throwable $e) {
    // L'index peut déjà exister ; on ne bloque pas la migration.
}

echo "Correction de la table students appliquée.\n";
exit(0);
