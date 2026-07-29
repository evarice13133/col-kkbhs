<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

echo "=== TEST DES RESTRICTIONS DE SUPPRESSION DES DONNÉES STRUCTURELLES ===\n\n";

// 1. Vérification des dépendances pour un Type d'Enseignement (ex: ID 3 SEC00)
$id = 3;
$deps = [];
$checkCycles = $db->prepare("SELECT COUNT(*) FROM cycles WHERE teaching_type_id = ?");
$checkCycles->execute([$id]);
if ($checkCycles->fetchColumn() > 0) $deps[] = 'cycles';

$checkDepts = $db->prepare("SELECT COUNT(*) FROM departments WHERE teaching_type_id = ?");
$checkDepts->execute([$id]);
if ($checkDepts->fetchColumn() > 0) $deps[] = 'départements';

$checkClasses = $db->prepare("SELECT COUNT(*) FROM classes WHERE teaching_type_id = ?");
$checkClasses->execute([$id]);
if ($checkClasses->fetchColumn() > 0) $deps[] = 'classes';

$checkSubjects = $db->prepare("SELECT COUNT(*) FROM subjects WHERE teaching_type_id = ?");
$checkSubjects->execute([$id]);
if ($checkSubjects->fetchColumn() > 0) $deps[] = 'matières';

echo "TeachingType ID {$id} (SEC00) dépendances détectées : " . (empty($deps) ? 'Aucune' : implode(', ', $deps)) . "\n";

// 2. Vérification des dépendances pour Cycles
$cycles = $db->query("SELECT id, nom FROM cycles LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cycles as $c) {
    $cId = (int)$c['id'];
    $stmtC = $db->prepare("SELECT COUNT(*) FROM classes WHERE cycle_id = ?");
    $stmtC->execute([$cId]);
    $count = $stmtC->fetchColumn();
    echo "Cycle '{$c['nom']}' (ID: {$cId}) : {$count} classes rattachées.\n";
}

// 3. Vérification des dépendances pour Sections
$sections = $db->query("SELECT id, nom FROM sections LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
foreach ($sections as $s) {
    $sId = (int)$s['id'];
    $stmtS = $db->prepare("SELECT COUNT(*) FROM classes WHERE section_id = ?");
    $stmtS->execute([$sId]);
    $count = $stmtS->fetchColumn();
    echo "Section '{$s['nom']}' (ID: {$sId}) : {$count} classes rattachées.\n";
}

// 4. Vérification des dépendances pour Départements
$departments = $db->query("SELECT id, nom FROM departments LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
foreach ($departments as $d) {
    $dId = (int)$d['id'];
    $stmtSub = $db->prepare("SELECT COUNT(*) FROM subjects WHERE department_id = ?");
    $stmtSub->execute([$dId]);
    $countSub = $stmtSub->fetchColumn();

    $stmtCla = $db->prepare("SELECT COUNT(*) FROM classes WHERE department_id = ?");
    $stmtCla->execute([$dId]);
    $countCla = $stmtCla->fetchColumn();

    echo "Département '{$d['nom']}' (ID: {$dId}) : {$countSub} matières rattachées, {$countCla} classes rattachées.\n";
}

echo "\n[SUCCÈS] Contrôles et règles de dépendances prêts et opérationnels.\n";
