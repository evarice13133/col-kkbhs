<?php
// Script pour supprimer le diagnostic après utilisation
$file = __DIR__ . '/diagnose_progress.php';
if (file_exists($file)) {
    unlink($file);
    echo "✅ Script diagnose_progress.php supprimé avec succès.";
    echo "<br><a href='/dashboard'>Retour au tableau de bord</a>";
} else {
    echo "❌ Le fichier n'existe pas déjà.";
}
