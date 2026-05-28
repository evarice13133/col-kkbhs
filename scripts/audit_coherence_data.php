<?php
/**
 * AUDIT DE COHÉRENCE DES DONNÉES PAR ANNÉE ACADÉMIQUE
 * 
 * Ce script vérifie que :
 * 1. Les données ne sont pas mélangées entre années
 * 2. Aucune année académique n'a été écrasée
 * 3. Les relations entre tables sont cohérentes
 * 4. Les données orphelines sont détectées
 * 
 * À exécuter régulièrement pour valider l'intégrité de la base.
 */

$pdo = new PDO('mysql:host=localhost;dbname=notemaster_imt;charset=utf8mb4','root','');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$timestamp = date('Y-m-d H:i:s');
$report = "[\n$timestamp] AUDIT DE COHÉRENCE DES DONNÉES PAR ANNÉE\n";
$report .= "=".str_repeat("=", 100)."\n\n";

// 1. Vérifier la distribution par année dans les tables critiques
$report .= "1. DISTRIBUTION DES DONNÉES PAR ANNÉE ACADÉMIQUE\n";
$report .= "-".str_repeat("-", 100)."\n";

$tables = ['grades', 'students', 'classes', 'subjects'];
foreach ($tables as $table) {
    $report .= "\nTable $table :\n";
    $q = $pdo->query("SELECT academic_year_id, COUNT(*) as n FROM $table GROUP BY academic_year_id ORDER BY academic_year_id");
    if ($q->rowCount() === 0) {
        $report .= "  ⚠️  VIDE - Aucune donnée\n";
    } else {
        foreach($q as $row) {
            $yearId = $row['academic_year_id'] ?? 'NULL';
            $report .= "  academic_year_id=$yearId : ".$row['n']." lignes\n";
        }
    }
}

// 2. Vérifier les données sans academic_year_id (corromption potentielle)
$report .= "\n\n2. DÉTECTION DE DONNÉES SANS ANNÉE ACADÉMIQUE (CORROMPTION ?)\n";
$report .= "-".str_repeat("-", 100)."\n";

foreach ($tables as $table) {
    $q = $pdo->query("SELECT COUNT(*) as n FROM $table WHERE academic_year_id IS NULL OR academic_year_id = ''");
    $n = $q->fetch()['n'];
    if ($n > 0) {
        $report .= "🚨 $table : $n lignes SANS academic_year_id (CORROMPTION DÉTECTÉE)\n";
    } else {
        $report .= "✅ $table : 0 lignes sans academic_year_id (OK)\n";
    }
}

// 3. Vérifier les doublons suspects dans grades
$report .= "\n\n3. DÉTECTION DE DOUBLONS DANS GRADES\n";
$report .= "-".str_repeat("-", 100)."\n";

$q = $pdo->query("
    SELECT student_id, subject_id, academic_year_id, COUNT(*) as n 
    FROM grades 
    GROUP BY student_id, subject_id, academic_year_id 
    HAVING COUNT(*) > 1
");

if ($q->rowCount() === 0) {
    $report .= "✅ Aucun doublon détecté (OK)\n";
} else {
    $report .= "🚨 Doublons détectés :\n";
    foreach($q as $row) {
        $report .= "  student_id=".$row['student_id'].", subject_id=".$row['subject_id'].", academic_year_id=".$row['academic_year_id'].", n=".$row['n']."\n";
    }
}

// 4. Vérifier la cohérence des années (studentsaffectés à des années qui existent)
$report .= "\n\n4. VÉRIFICATION DE LA COHÉRENCE DES RÉFÉRENCES AUX ANNÉES\n";
$report .= "-".str_repeat("-", 100)."\n";

$q = $pdo->query("
    SELECT DISTINCT academic_year_id FROM (
        SELECT academic_year_id FROM grades
        UNION
        SELECT academic_year_id FROM students
        UNION
        SELECT academic_year_id FROM classes
        UNION
        SELECT academic_year_id FROM subjects
    ) as all_years
");

$referencedYears = $q->fetchAll(PDO::FETCH_COLUMN);
$q = $pdo->query("SELECT id FROM academic_years");
$existingYears = $q->fetchAll(PDO::FETCH_COLUMN);

$orphanYears = array_diff($referencedYears, $existingYears);
if (!empty($orphanYears)) {
    $report .= "🚨 Données orphelines pour années inexistantes : " . implode(', ', array_filter($orphanYears, fn($y) => $y !== null)) . "\n";
} else {
    $report .= "✅ Toutes les références d'années existent en base (OK)\n";
}

// 5. Résumé des années archivées vs actives
$report .= "\n\n5. STATUS DES ANNÉES ACADÉMIQUES\n";
$report .= "-".str_repeat("-", 100)."\n";

$q = $pdo->query("SELECT id, nom, status, is_active FROM academic_years ORDER BY id");
foreach($q as $row) {
    $status = $row['status'] === 'archived' ? '🔒 ARCHIVÉE' : '🟢 ACTIVE';
    $active = $row['is_active'] ? '(Année courante)' : '';
    $report .= "  ID=".$row['id']." : ".$row['nom']." - $status $active\n";
}

// 6. Vérifier les anomalies de distribution (ex: année active vide)
$report .= "\n\n6. ANOMALIES POTENTIELLES\n";
$report .= "-".str_repeat("-", 100)."\n";

$q = $pdo->query("
    SELECT id, nom FROM academic_years WHERE is_active = TRUE
");
$activeYear = $q->fetch();

if ($activeYear) {
    $activeId = $activeYear['id'];
    $activeName = $activeYear['nom'];
    
    $q = $pdo->query("SELECT COUNT(*) as n FROM grades WHERE academic_year_id = ?", [$activeId]);
    $gradeCount = $q->fetch()['n'];
    
    if ($gradeCount === 0) {
        $report .= "⚠️  L'année active '$activeName' n'a aucune note. C'est peut-être normal en début d'année.\n";
    } else {
        $report .= "✅ L'année active '$activeName' contient $gradeCount notes (OK)\n";
    }
}

// 7. Finale
$report .= "\n\n" . "=".str_repeat("=", 100)."\n";
$report .= "FIN AUDIT\n";
$report .= date('Y-m-d H:i:s') . "\n";

echo $report;

// Sauvegarder le rapport
$logsDir = __DIR__ . '/../logs';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0755, true);
}
file_put_contents($logsDir . '/audit_cohesion_' . date('Y-m-d_His') . '.log', $report);
echo "\n✅ Rapport sauvegardé dans logs/\n";
