<?php
$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== DEBUG: subject_classes ===\n\n";

// Check if subject_classes table exists
$tableExists = $pdo->query("SHOW TABLES LIKE 'subject_classes'")->fetch();
if (!$tableExists) {
    echo "❌ La table subject_classes n'existe pas!\n";
    exit(1);
}

// Count subject_classes by academic year
echo "Subject_classes par année scolaire:\n";
$result = $pdo->query("SELECT academic_year_id, COUNT(*) as count FROM subject_classes GROUP BY academic_year_id ORDER BY academic_year_id DESC")->fetchAll(PDO::FETCH_ASSOC);
if (empty($result)) {
    echo "⚠ Aucune subject_classes trouvée dans la base de données\n";
} else {
    foreach ($result as $row) {
        echo "  Année {$row['academic_year_id']}: {$row['count']} entrées\n";
    }
}

echo "\n";

// Get active academic year
echo "Année scolaire active:\n";
$activeYear = $pdo->query("SELECT id, nom, is_active FROM academic_years WHERE is_active = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if ($activeYear) {
    echo "  ID: {$activeYear['id']}, Nom: {$activeYear['nom']}\n";
} else {
    echo "  ⚠ Aucune année active trouvée\n";
}

echo "\n";

// Sample subject_classes data
echo "Exemple de données subject_classes:\n";
$sample = $pdo->query("SELECT * FROM subject_classes LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
if (empty($sample)) {
    echo "  ⚠ Aucune donnée à afficher\n";
} else {
    foreach ($sample as $row) {
        echo "  subject_id: {$row['subject_id']}, class_id: {$row['class_id']}, academic_year_id: {$row['academic_year_id']}\n";
    }
}

echo "\n=== FIN DEBUG ===\n";
