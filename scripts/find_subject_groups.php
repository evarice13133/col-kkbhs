<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Database;

$pattern = $argv[1] ?? '%';
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT id, libelle, status, (SELECT COUNT(*) FROM subjects s WHERE s.subject_group_id = subject_groups.id) AS subjects_count FROM subject_groups WHERE libelle LIKE :p ORDER BY id DESC LIMIT 500");
$stmt->execute([':p' => $pattern]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$rows) {
    echo "Aucun groupe trouvé pour le motif: $pattern\n";
    exit(0);
}
foreach ($rows as $r) {
    echo sprintf("%d | %s | status=%s | subjects=%d\n", $r['id'], $r['libelle'], $r['status'], $r['subjects_count']);
}

exit(0);
