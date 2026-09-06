<?php
/**
 * Diagnostic en lecture seule du flux de creation d'une matiere.
 * Usage CLI: php debug_subject_creation.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Ce diagnostic doit etre execute en ligne de commande.\n");
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';

$pdo = new PDO(
    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
    DB_USER,
    DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

function line(string $label, string $value): void
{
    printf("%-32s %s\n", $label, $value);
}

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?'
    );
    $stmt->execute([$table]);
    return (bool) $stmt->fetchColumn();
}

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?'
    );
    $stmt->execute([$table, $column]);
    return (bool) $stmt->fetchColumn();
}

try {
    echo "=== DIAGNOSTIC CREATION MATIERE (LECTURE SEULE) ===\n\n";
    line('Base de donnees', DB_NAME);
    line('Mode PHP', PHP_SAPI);
    echo "\nTables requises:\n";
    foreach (['subjects', 'subject_classes', 'subject_group_assignments', 'competencies', 'academic_years', 'users'] as $table) {
        line("  $table", tableExists($pdo, $table) ? 'OK' : 'MANQUANTE');
    }

    echo "\nColonnes lues/ecrites dans subjects:\n";
    foreach (['id', 'nom', 'coefficient', 'groupe', 'subject_group_id', 'teaching_type_id', 'teaching_form_id', 'code_uv', 'code_ue', 'vhm', 'vhp', 'th_max', 'observations'] as $column) {
        line("  subjects.$column", columnExists($pdo, 'subjects', $column) ? 'OK' : 'MANQUANTE');
    }

    $year = $pdo->query('SELECT id, nom FROM academic_years WHERE is_active = 1 LIMIT 1')->fetch();
    line('Annee academique active', $year ? $year['id'] . ' - ' . $year['nom'] : 'MANQUANTE');
    $user = $pdo->query('SELECT id FROM users ORDER BY id ASC LIMIT 1')->fetchColumn();
    line('Utilisateur de secours', $user !== false ? (string) $user : 'MANQUANT');

    echo "\nContraintes subjects:\n";
    if (tableExists($pdo, 'subjects')) {
        foreach ($pdo->query('SHOW CREATE TABLE subjects')->fetch(PDO::FETCH_NUM) as $index => $value) {
            if ($index === 1) {
                echo $value . "\n";
                break;
            }
        }
    }

    echo "\nConclusion: toute ligne MANQUANTE ou l'absence d'annee active explique un echec avant commit.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "ERREUR DIAGNOSTIC: " . $e->getMessage() . "\n");
    exit(1);
}
