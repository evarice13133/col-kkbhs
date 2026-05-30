<?php
$db = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "Vérification après la suppression de l'enseignant...\n\n";

// Vérifier si les notes existent toujours avec teacher_id = NULL
$orphaned = (int) $db->query("
    SELECT COUNT(*) FROM grades 
    WHERE teacher_id IS NULL AND teacher_nom_snapshot LIKE '%Lonfo%'
")->fetchColumn();

echo "Notes orphelines (teacher_id = NULL) avec snapshot 'Lonfo': $orphaned\n";

// Vérifier toutes les notes avec teacher_id = NULL
$allOrphaned = (int) $db->query("SELECT COUNT(*) FROM grades WHERE teacher_id IS NULL")->fetchColumn();
echo "Toutes les notes orphelines (teacher_id = NULL): $allOrphaned\n";

// Vérifier si les notes existent avec un teacher_id invalide
$invalid = (int) $db->query("
    SELECT COUNT(*) FROM grades 
    WHERE teacher_id NOT IN (SELECT id FROM users WHERE id IS NOT NULL)
")->fetchColumn();
echo "Notes avec teacher_id invalide: $invalid\n";

// Vérifier les snapshots remplis
$withSnapshot = (int) $db->query("
    SELECT COUNT(*) FROM grades 
    WHERE teacher_nom_snapshot IS NOT NULL
")->fetchColumn();
echo "Notes avec snapshot rempli: $withSnapshot\n";

// Vérifier si 'Lonfo Derick' a des notes
$lonfoNotes = (int) $db->query("
    SELECT COUNT(*) FROM grades 
    WHERE teacher_nom_snapshot LIKE '%Lonfo%'
")->fetchColumn();
echo "Notes avec snapshot 'Lonfo': $lonfoNotes\n";

// Montrer quelques exemples
echo "\nExemples de notes orphelines:\n";
$examples = $db->query("
    SELECT g.id, g.teacher_id, g.teacher_nom_snapshot, g.valeur
    FROM grades 
    WHERE teacher_id IS NULL 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($examples as $ex) {
    echo "  Note #{$ex['id']}: teacher_id={$ex['teacher_id']}, snapshot='{$ex['teacher_nom_snapshot']}', valeur={$ex['valeur']}\n";
}
?>
