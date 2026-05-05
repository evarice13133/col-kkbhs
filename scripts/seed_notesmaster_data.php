<?php

declare(strict_types=1);

// Script de pre-remplissage direct de la base notesmasterdb.
// Il insere les cycles, sections, departements, classes, matieres,
// liaisons matieres/classes et 5 eleves par classe en evitant les doublons.

$pdo = new PDO(
    'mysql:host=localhost;dbname=notesmasterdb;charset=utf8mb4',
    'root',
    '',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);

// Banque de noms reels pour generer des apprenants plus credibles.
$lastNames = [
    'TAMAFO', 'KAMENI', 'FOFACK', 'TEZEMPA', 'BONO', 'NJOYA', 'FOTSO', 'TCHUENTE',
    'NGAHA', 'SIMO', 'KOUAM', 'NKOULOU', 'MBOCK', 'MBIDA', 'NDJANA', 'MEFIRE',
    'YEMELE', 'NDEDJI', 'TSAGUE', 'TCHANA', 'MOUAFO', 'ESSOMBA', 'ABENA', 'MBOGNING',
    'DJOMO', 'KOUEMOU', 'MBANG', 'MENGUE', 'TSAFACK', 'NGASSA', 'KEMAYOU', 'MOUELLE',
    'ATCHA', 'TCHUENKAM', 'NLEPO', 'TCHAKOUNTE', 'NANA', 'MBOUA', 'WAMBA', 'ELLA',
];

$firstNames = [
    'Jordan', 'Jean Jonas', 'Loris Elessa', 'Maeva', 'Nathan', 'Diane', 'Raissa',
    'Cedric', 'Boris', 'Noeline', 'Estelle', 'Gael', 'Mireille', 'Fabrice',
    'Christelle', 'Ulrich', 'Yvan', 'Prisca', 'Brice', 'Lydie', 'Arielle',
    'Blaise', 'Ruth', 'Junior', 'Giselle', 'Steve', 'Josiane', 'Boris Landry',
    'Jean Claude', 'Merveille', 'Eloise', 'Kevin', 'Sonia', 'Naomi', 'Loic',
    'Patrick', 'Aicha', 'Florian', 'Vanessa', 'Rachelle', 'Didier', 'Carine',
    'Lionel', 'Murielle', 'Samuel', 'Kelly', 'Jessica', 'Willy', 'Michaelle',
];

$pdo->beginTransaction();

try {
    // Mise en place des parametres minimum pour la generation des matricules.
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (setting_key VARCHAR(50) PRIMARY KEY, setting_value TEXT)");
    $pdo->exec("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
        ('school_code', 'CMR-COL'),
        ('matricule_format', '{SCHOOL_CODE}-{CLASS}-MT{COUNTER}'),
        ('matricule_counter', '1')");

    // Insertion des cycles reels de l'etablissement.
    $cycles = ['1ere Cycle', '2nd Cycle'];
    $stmt = $pdo->prepare('INSERT IGNORE INTO cycles (nom) VALUES (?)');
    foreach ($cycles as $cycle) {
        $stmt->execute([$cycle]);
    }

    // Insertion des sections utiles a l'etablissement.
    $sections = ['Francophone', 'Anglophone', 'Technique'];
    $stmt = $pdo->prepare('INSERT IGNORE INTO sections (nom) VALUES (?)');
    foreach ($sections as $section) {
        $stmt->execute([$section]);
    }

    // Insertion des departements pedagogiques et techniques.
    $departments = [
        'Employes et Services Comptable(ESCOM)',
        'ESG (Enseignement General)',
        'Batiment (BATI)',
        'Electricite (ELE)',
        'Mecanique (MECA)',
        'Informatique et Reseaux (INFO)',
        'Gestion/Comptabilite (GESTI)',
        'Soudure (SOU)',
        'Couture (COME)',
        'Secretariat Medical (SEME)',
    ];
    $stmt = $pdo->prepare('INSERT IGNORE INTO departments (nom) VALUES (?)');
    foreach ($departments as $department) {
        $stmt->execute([$department]);
    }

    // Recuperation des identifiants de cycles et sections pour les classes.
    $cycleMap = [];
    foreach ($pdo->query('SELECT id, nom FROM cycles') as $row) {
        $cycleMap[$row['nom']] = (int) $row['id'];
    }

    $sectionMap = [];
    foreach ($pdo->query('SELECT id, nom FROM sections') as $row) {
        $sectionMap[$row['nom']] = (int) $row['id'];
    }

    // Chargement des regles de matricule issues des parametres generaux.
    $settings = [];
    foreach ($pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('school_code', 'matricule_format', 'matricule_counter')") as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    $schoolCode = trim((string) ($settings['school_code'] ?? 'CMR-COL'));
    $matriculeFormat = trim((string) ($settings['matricule_format'] ?? '{SCHOOL_CODE}-{CLASS}-MT{COUNTER}'));
    $matriculeCounter = max(1, (int) ($settings['matricule_counter'] ?? 1));

    // Definition centralisee des classes a creer.
    $classes = [
        ['6eme A ESG', '1ere Cycle', 'Francophone', 'ESG'],
        ['6eme B ESG', '1ere Cycle', 'Francophone', 'ESG'],
        ['5eme A ESG', '1ere Cycle', 'Francophone', 'ESG'],
        ['5eme B ESG', '1ere Cycle', 'Francophone', 'ESG'],
        ['4eme A ESG', '1ere Cycle', 'Francophone', 'ESG'],
        ['4eme B ESG', '1ere Cycle', 'Francophone', 'ESG'],
        ['3eme A ESG', '1ere Cycle', 'Francophone', 'ESG'],
        ['3eme B ESG', '1ere Cycle', 'Francophone', 'ESG'],
        ['2nde A ESG', '2nd Cycle', 'Francophone', 'ESG'],
        ['2nde C ESG', '2nd Cycle', 'Francophone', 'ESG'],
        ['1ere A ESG', '2nd Cycle', 'Francophone', 'ESG'],
        ['1ere C ESG', '2nd Cycle', 'Francophone', 'ESG'],
        ['Tle A ESG', '2nd Cycle', 'Francophone', 'ESG'],
        ['Tle C ESG', '2nd Cycle', 'Francophone', 'ESG'],
        ['Form 1 A ESG', '1ere Cycle', 'Anglophone', 'ESG'],
        ['Form 1 B ESG', '1ere Cycle', 'Anglophone', 'ESG'],
        ['Form 2 A ESG', '1ere Cycle', 'Anglophone', 'ESG'],
        ['Form 2 B ESG', '1ere Cycle', 'Anglophone', 'ESG'],
        ['Form 3 A ESG', '1ere Cycle', 'Anglophone', 'ESG'],
        ['Form 3 B ESG', '1ere Cycle', 'Anglophone', 'ESG'],
        ['Form 4 A ESG', '2nd Cycle', 'Anglophone', 'ESG'],
        ['Form 4 B ESG', '2nd Cycle', 'Anglophone', 'ESG'],
        ['Form 5 A ESG', '2nd Cycle', 'Anglophone', 'ESG'],
        ['Form 5 B ESG', '2nd Cycle', 'Anglophone', 'ESG'],
        ['Lower Sixth ESG', '2nd Cycle', 'Anglophone', 'ESG'],
        ['Upper Sixth ESG', '2nd Cycle', 'Anglophone', 'ESG'],
        ['1ere BAT A', '2nd Cycle', 'Technique', 'BATI'],
        ['2eme BAT A', '2nd Cycle', 'Technique', 'BATI'],
        ['3eme BAT A', '2nd Cycle', 'Technique', 'BATI'],
        ['1ere ELE A', '2nd Cycle', 'Technique', 'ELE'],
        ['2eme ELE A', '2nd Cycle', 'Technique', 'ELE'],
        ['3eme ELE A', '2nd Cycle', 'Technique', 'ELE'],
        ['1ere MECA A', '2nd Cycle', 'Technique', 'MECA'],
        ['2eme MECA A', '2nd Cycle', 'Technique', 'MECA'],
        ['3eme MECA A', '2nd Cycle', 'Technique', 'MECA'],
        ['1ere INFO A', '2nd Cycle', 'Technique', 'INFO'],
        ['1ere INFO B', '2nd Cycle', 'Technique', 'INFO'],
        ['2eme INFO A', '2nd Cycle', 'Technique', 'INFO'],
        ['2eme INFO B', '2nd Cycle', 'Technique', 'INFO'],
        ['3eme INFO A', '2nd Cycle', 'Technique', 'INFO'],
        ['3eme INFO B', '2nd Cycle', 'Technique', 'INFO'],
        ['1ere GESTI A', '2nd Cycle', 'Technique', 'GESTI'],
        ['2eme GESTI A', '2nd Cycle', 'Technique', 'GESTI'],
        ['3eme GESTI A', '2nd Cycle', 'Technique', 'GESTI'],
        ['1ere SOU A', '2nd Cycle', 'Technique', 'SOU'],
        ['2eme SOU A', '2nd Cycle', 'Technique', 'SOU'],
        ['3eme SOU A', '2nd Cycle', 'Technique', 'SOU'],
        ['1ere COME A', '2nd Cycle', 'Technique', 'COME'],
        ['2eme COME A', '2nd Cycle', 'Technique', 'COME'],
        ['3eme COME A', '2nd Cycle', 'Technique', 'COME'],
        ['1ere SEME A', '2nd Cycle', 'Technique', 'SEME'],
        ['2eme SEME A', '2nd Cycle', 'Technique', 'SEME'],
        ['3eme SEME A', '2nd Cycle', 'Technique', 'SEME'],
        ['1ere ESCOM A', '2nd Cycle', 'Technique', 'ESCOM'],
        ['2eme ESCOM A', '2nd Cycle', 'Technique', 'ESCOM'],
        ['3eme ESCOM A', '2nd Cycle', 'Technique', 'ESCOM'],
    ];

    // Insertion des classes en respectant cycle_id et section_id.
    $stmt = $pdo->prepare('INSERT IGNORE INTO classes (nom, cycle_id, section_id) VALUES (?, ?, ?)');
    foreach ($classes as [$name, $cycleName, $sectionName]) {
        $stmt->execute([$name, $cycleMap[$cycleName], $sectionMap[$sectionName]]);
    }

    // Definition des matieres ESG et techniques avec coefficients.
    $subjectsByDepartment = [
        'ESG' => [
            ['Francais', 4], ['Anglais', 3], ['Mathematiques', 5], ['Physique', 4],
            ['Chimie', 3], ['Histoire', 2], ['Geographie', 2], ['SVT', 3],
            ['EPS', 1], ['Technologie', 2], ['Informatique', 2],
        ],
        'BATI' => [['Dessin Technique', 4], ['Topographie', 3], ['Construction', 5]],
        'ELE' => [['Installations Electriques', 5], ['Electrotechnique', 4], ['Mesures Electriques', 3]],
        'MECA' => [['Mecanique Generale', 5], ['Maintenance Industrielle', 4], ['Fabrication Mecanique', 4]],
        'INFO' => [['Algorithmique', 4], ['Reseaux Informatiques', 5], ['Maintenance Informatique', 4]],
        'GESTI' => [['Comptabilite', 5], ['Gestion Commerciale', 4], ["Economie d'Entreprise", 3]],
        'SOU' => [['Soudure MIG', 5], ['Soudure TIG', 4], ['Securite en Atelier', 2]],
        'COME' => [['Coupe', 4], ['Couture', 5], ['Couture Industrielle', 4]],
        'SEME' => [['Bureautique', 4], ['Terminologie Medicale', 5], ['Gestion des Dossiers', 3]],
        'ESCOM' => [['Comptabilite Generale', 5], ['Secretariat Administratif', 4], ['Techniques Bancaires', 3]],
    ];

    // Insertion des matieres sans doublon.
    $stmt = $pdo->prepare('INSERT INTO subjects (nom, coefficient) VALUES (?, ?) ON DUPLICATE KEY UPDATE coefficient = VALUES(coefficient)');
    foreach ($subjectsByDepartment as $items) {
        foreach ($items as [$subjectName, $coefficient]) {
            $stmt->execute([$subjectName, $coefficient]);
        }
    }

    // Reconstitution de la map des classes et des matieres pour les liaisons.
    $classMap = [];
    foreach ($pdo->query('SELECT id, nom, cycle_id, section_id FROM classes') as $row) {
        $classMap[$row['nom']] = $row;
    }

    $subjectMap = [];
    foreach ($pdo->query('SELECT id, nom FROM subjects') as $row) {
        $subjectMap[$row['nom']] = (int) $row['id'];
    }

    // Liaison de chaque matiere aux classes de son departement logique.
    $stmt = $pdo->prepare('INSERT IGNORE INTO subject_classes (subject_id, class_id) VALUES (?, ?)');
    foreach ($classes as [$className, , , $departmentCode]) {
        foreach ($subjectsByDepartment[$departmentCode] as [$subjectName]) {
            $stmt->execute([$subjectMap[$subjectName], (int) $classMap[$className]['id']]);
        }
    }

    // Generation de 5 eleves par classe predefinie avec un matricule conforme aux parametres generaux.
    $stmt = $pdo->prepare(
        'INSERT INTO students (nom, prenom, email, cycle_id, section_id, class_id)
         SELECT ?, ?, ?, ?, ?, ?
         FROM DUAL
         WHERE NOT EXISTS (SELECT 1 FROM students WHERE email = ?)'
    );

    foreach ($classes as [$className, , , $departmentCode]) {
        $classId = (int) $classMap[$className]['id'];
        $cycleId = (int) $classMap[$className]['cycle_id'];
        $sectionId = (int) $classMap[$className]['section_id'];
        $classToken = strtoupper((string) preg_replace('/[^A-Z0-9]+/i', '', (string) @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $className)));
        if ($classToken === '') {
            $classToken = 'IND';
        }

        for ($i = 1; $i <= 5; $i++) {
            $seedIndex = $matriculeCounter - 1;
            $nom = $lastNames[$seedIndex % count($lastNames)];
            $prenom = $firstNames[$seedIndex % count($firstNames)];
            $counterPad = str_pad((string) $matriculeCounter, 6, '0', STR_PAD_LEFT);
            $email = str_replace(
                ['{SCHOOL_CODE}', '{CLASS}', '{COUNTER}'],
                [$schoolCode !== '' ? $schoolCode : 'CMR-COL', $classToken, $counterPad],
                $matriculeFormat
            );

            $stmt->execute([$nom, $prenom, $email, $cycleId, $sectionId, $classId, $email]);
            $matriculeCounter++;
        }
    }

    // Complement des anciennes classes deja presentes pour garantir au moins 5 eleves par classe.
    $existingClasses = $pdo->query('SELECT id, nom, cycle_id, section_id FROM classes')->fetchAll();
    foreach ($existingClasses as $classRow) {
        $classId = (int) $classRow['id'];
        $className = (string) $classRow['nom'];
        $cycleId = $classRow['cycle_id'] !== null ? (int) $classRow['cycle_id'] : null;
        $sectionId = $classRow['section_id'] !== null ? (int) $classRow['section_id'] : null;
        $currentCount = (int) $pdo->query('SELECT COUNT(*) FROM students WHERE class_id = ' . $classId)->fetchColumn();

        if ($currentCount >= 5) {
            continue;
        }

        $classToken = strtoupper((string) preg_replace('/[^A-Z0-9]+/i', '', (string) @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $className)));
        if ($classToken === '') {
            $classToken = 'IND';
        }
        for ($i = $currentCount + 1; $i <= 5; $i++) {
            $seedIndex = $matriculeCounter - 1;
            $nom = $lastNames[$seedIndex % count($lastNames)];
            $prenom = $firstNames[$seedIndex % count($firstNames)];
            $counterPad = str_pad((string) $matriculeCounter, 6, '0', STR_PAD_LEFT);
            $email = str_replace(
                ['{SCHOOL_CODE}', '{CLASS}', '{COUNTER}'],
                [$schoolCode !== '' ? $schoolCode : 'CMR-COL', $classToken, $counterPad],
                $matriculeFormat
            );

            $stmt->execute([$nom, $prenom, $email, $cycleId, $sectionId, $classId, $email]);
            $matriculeCounter++;
        }
    }

    // Sauvegarde du prochain compteur afin que les prochains ajouts restent coherents.
    $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'matricule_counter'")
        ->execute([$matriculeCounter]);

    $pdo->commit();

    echo "Pre-remplissage termine avec succes.\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
