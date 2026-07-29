<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

echo "=== VERIFICATION RBAC POUR TOUS LES RÔLES ===\n\n";

$roles = $db->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

foreach ($roles as $role) {
    echo "Rôle: {$role['role_code']} ({$role['role_name']})\n";
    $stmt = $db->prepare("
        SELECT p.perm_code 
        FROM role_permissions rp 
        JOIN permissions p ON rp.permission_id = p.id 
        WHERE rp.role_id = ? 
        ORDER BY p.perm_code ASC
    ");
    $stmt->execute([$role['id']]);
    $perms = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "  Permissions (" . count($perms) . "): " . implode(', ', $perms) . "\n\n";
}

echo "Vérification des exceptions pour l'Admin :\n";
$stmtAdmin = $db->prepare("
    SELECT p.perm_code 
    FROM role_permissions rp 
    JOIN permissions p ON rp.permission_id = p.id 
    JOIN roles r ON rp.role_id = r.id 
    WHERE r.role_code = 'admin' AND p.perm_code = 'manage_academic_years'
");
$stmtAdmin->execute();
$hasAcademicYear = $stmtAdmin->fetchColumn();

if ($hasAcademicYear) {
    echo "  [ÉCHEC] L'Admin possède toujours la permission 'manage_academic_years'.\n";
} else {
    echo "  [SUCCÈS] L'Admin n'a PAS la permission 'manage_academic_years'.\n";
}
