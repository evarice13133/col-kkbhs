<?php
// Supprime un groupe de matières de façon sûre :
// - vérifie l'existence du groupe par libellé (argument 1)
// - compte les matières liées
// - si une contrainte FK existe avec ON DELETE SET NULL, supprime directement
// - sinon, met à NULL les subject_group_id dans subjects avant suppression

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$name = $argv[1] ?? null;
if (!$name) {
    echo "Usage: php scripts/delete_subject_group_safe.php \"libelle du groupe\"\n";
    exit(1);
}

$db = Database::getInstance()->getConnection();

// Cherche le groupe (match exact ou similaire)
$stmt = $db->prepare("SELECT * FROM subject_groups WHERE libelle = :name LIMIT 1");
$stmt->execute([':name' => $name]);
$grp = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$grp) {
    // essai LIKE
    $stmt = $db->prepare("SELECT * FROM subject_groups WHERE libelle LIKE :like LIMIT 1");
    $stmt->execute([':like' => "%$name%"]);
    $grp = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$grp) {
    echo "Groupe introuvable pour '$name'. Vérifiez le libellé.\n";
    exit(1);
}

$groupId = (int)$grp['id'];
echo "Trouvé groupe id={$groupId} libelle='{$grp['libelle']}' status={$grp['status']}\n";

// Compte les matières liées
$stmt = $db->prepare("SELECT COUNT(*) AS c FROM subjects WHERE subject_group_id = :gid");
$stmt->execute([':gid' => $groupId]);
$count = (int)$stmt->fetchColumn();
echo "Matières liées: {$count}\n";

// Vérifie existence FK et action ON DELETE
$fkStmt = $db->prepare(
    "SELECT rc.DELETE_RULE
     FROM information_schema.REFERENTIAL_CONSTRAINTS rc
     JOIN information_schema.KEY_COLUMN_USAGE kcu ON rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME AND rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
     WHERE rc.CONSTRAINT_SCHEMA = DATABASE()
       AND rc.REFERENCED_TABLE_NAME = 'subject_groups'
       AND kcu.TABLE_NAME = 'subjects'
     LIMIT 1"
);
$fkStmt->execute();
$fk = $fkStmt->fetch(PDO::FETCH_ASSOC);

$canDeleteDirect = false;
if ($fk && isset($fk['DELETE_RULE'])) {
    $rule = strtoupper($fk['DELETE_RULE']);
    echo "Constraint FK found, ON DELETE rule: {$rule}\n";
    if ($rule === 'SET NULL' || $rule === 'SET_NULL') {
        $canDeleteDirect = true;
    }
} else {
    echo "Aucune contrainte FK explicite trouvée entre subjects.subject_group_id et subject_groups.id\n";
}

if ($count > 0 && !$canDeleteDirect) {
    echo "Mise à NULL des subject_group_id pour les {$count} matières liées...\n";
    $u = $db->prepare("UPDATE subjects SET subject_group_id = NULL WHERE subject_group_id = :gid");
    $u->execute([':gid' => $groupId]);
}

// Maintenant suppression du groupe
$del = $db->prepare("DELETE FROM subject_groups WHERE id = :gid");
$del->execute([':gid' => $groupId]);
$rows = $del->rowCount();
if ($rows > 0) {
    echo "Groupe id={$groupId} supprimé avec succès.\n";
} else {
    echo "Aucune ligne supprimée (peut-être déjà supprimée ou erreur).\n";
}

// Info : nombre restantes
$stmt = $db->prepare("SELECT COUNT(*) FROM subject_groups WHERE id = :gid");
$stmt->execute([':gid'=>$groupId]);
$still = (int)$stmt->fetchColumn();
echo "Présence résiduelle du groupe en base: {$still}\n";

exit(0);
