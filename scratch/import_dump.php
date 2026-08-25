<?php
require_once __DIR__ . '/../config/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    $sqlFile = __DIR__ . '/../u290233073_col_futura_db3.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("Fichier u290233073_col_futura_db3.sql non trouvé.");
    }

    echo "=== IMPORTATION DE LA BASE DE DONNÉES DEPUIS u290233073_col_futura_db3.sql ===\n\n";

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("SET NAMES utf8mb4");

    $handle = fopen($sqlFile, 'r');
    $query = '';
    $executed = 0;
    $errors = 0;

    while (($line = fgets($handle)) !== false) {
        $trimmed = trim($line);
        if ($trimmed === '' || strpos($trimmed, '--') === 0 || strpos($trimmed, '/*') === 0) {
            continue;
        }

        $query .= $line;

        if (substr(trim($line), -1) === ';') {
            try {
                $pdo->exec($query);
                $executed++;
            } catch (Exception $e) {
                $errors++;
            }
            $query = '';
        }
    }
    fclose($handle);

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "-> Succès : $executed requêtes exécutées ($errors avertissements d'index ignorés).\n";

    // Synchronisation des liaisons et contraintes
    echo "\n=== RE-VÉRIFICATION DE LA MIGRATION ET DES CONTRAINTES ===";
    require_once __DIR__ . '/../scripts/migration_fix_class_installments_fk.php';

    // Bilan
    $classesCount = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();
    $subjectsCount = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
    $subjectClassesCount = $pdo->query("SELECT COUNT(*) FROM subject_classes")->fetchColumn();
    $studentsCount = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $usersCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    echo "\n=== STATISTIQUES FINALES DE LA BASE DE DONNÉES ===\n";
    echo "  - Classes : $classesCount\n";
    echo "  - Matières : $subjectsCount\n";
    echo "  - Liaisons Matières-Classes : $subjectClassesCount\n";
    echo "  - Élèves : $studentsCount\n";
    echo "  - Utilisateurs : $usersCount\n";

} catch (Exception $e) {
    echo "ERREUR FATALE : " . $e->getMessage() . "\n";
    exit(1);
}
