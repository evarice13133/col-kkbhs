<?php
/**
 * Nettoyage des références invalides dans la table grades
 */
$db = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4', 'root', '', 
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "Nettoyage des références invalides...\n";

// Identifier les teacher_id invalides
$invalidTeachers = $db->query(
    'SELECT DISTINCT teacher_id FROM grades WHERE teacher_id NOT IN (SELECT id FROM users)'
)->fetchAll(PDO::FETCH_COLUMN);

if (!empty($invalidTeachers)) {
    echo "  Teacher IDs invalides trouvés : " . implode(', ', $invalidTeachers) . "\n";
    
    // Mettre à jour ces notes avec teacher_id = NULL
    $db->exec('UPDATE grades SET teacher_id = NULL WHERE teacher_id NOT IN (SELECT id FROM users)');
    
    $affectedCount = count($invalidTeachers);
    $orphanedCount = (int) $db->query('SELECT COUNT(*) FROM grades WHERE teacher_id IS NULL')->fetchColumn();
    
    echo "  ✅ $affectedCount teacher IDs mis à NULL\n";
    echo "  Total des notes orphelines maintenant : $orphanedCount\n";
} else {
    echo "  ✅ Aucune référence invalide trouvée.\n";
}

// Vérifier que tout est valide maintenant
$invalidCount = (int) $db->query(
    'SELECT COUNT(*) FROM grades WHERE teacher_id NOT IN (SELECT id FROM users WHERE id IS NOT NULL)'
)->fetchColumn();

echo "\nVérification finale : $invalidCount références invalides\n";

if ($invalidCount === 0) {
    echo "✅ Intégrité complète!\n";
}
?>
