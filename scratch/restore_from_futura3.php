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

    echo "=== IMPORTATION DE LA BASE DE DONNÉES u290233073_col_futura_db3.sql ===\n\n";

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("SET NAMES utf8mb4");

    $sql = file_get_contents($sqlFile);
    
    // Séparer les requêtes SQL
    $statements = explode(";\n", $sql);
    $executed = 0;
    $errors = 0;

    foreach ($statements as $stmtStr) {
        $query = trim($stmtStr);
        if ($query === '' || strpos($query, '--') === 0 || strpos($query, '/*') === 0) {
            continue;
        }

        try {
            $pdo->exec($query);
            $executed++;
        } catch (Exception $e) {
            $errors++;
            // ignorer les erreurs secondaires sur les index déjà existants
        }
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "-> Impotations SQL exécutées : $executed requêtes ($errors avertissements ignorés).\n";

    // Re-vérifier et appliquer les contraintes FK
    echo "\n=== APPLICATION DE LA MIGRATION FIX FK & ORPHELINS ===\n";
    require_once __DIR__ . '/../scripts/migration_fix_class_installments_fk.php';

    // Compter les statistiques
    $classesCount = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();
    $subjectsCount = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
    $subjectClassesCount = $pdo->query("SELECT COUNT(*) FROM subject_classes")->fetchColumn();
    $studentsCount = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $usersCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    echo "\n=== BILAN APRES RESTAURATION DE LA BASE ===\n";
    echo "  - Classes : $classesCount\n";
    echo "  - Matières : $subjectsCount\n";
    echo "  - Liaisons Matières-Classes : $subjectClassesCount\n";
    echo "  - Élèves : $studentsCount\n";
    echo "  - Utilisateurs : $usersCount\n";

    echo "\n=== OPÉRATION TERMINÉE AVEC SUCCÈS ===\n";

} catch (Exception $e) {
    echo "ERREUR RESTAURATION : " . $e->getMessage() . "\n";
    exit(1);
}
