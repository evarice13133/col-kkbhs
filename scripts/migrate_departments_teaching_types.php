<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    echo "Starting Migration: Integrating teaching_type_id to departments and department_id to subjects...\n";

    // 1. Ensure teaching_type_id exists in departments
    $stmt = $db->query("DESCRIBE departments");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('teaching_type_id', $columns)) {
        echo "Adding teaching_type_id to departments...\n";
        $db->exec("ALTER TABLE departments ADD COLUMN teaching_type_id INT NULL");
        $db->exec("ALTER TABLE departments ADD CONSTRAINT fk_dept_teaching_type FOREIGN KEY (teaching_type_id) REFERENCES teaching_types(id) ON DELETE SET NULL");
    } else {
        echo "teaching_type_id already exists in departments.\n";
    }

    // 2. Ensure department_id exists in subjects
    $stmt = $db->query("DESCRIBE subjects");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('department_id', $columns)) {
        echo "Adding department_id to subjects...\n";
        $db->exec("ALTER TABLE subjects ADD COLUMN department_id INT NULL");
        $db->exec("ALTER TABLE subjects ADD CONSTRAINT fk_subject_dept FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL");
    } else {
        echo "department_id already exists in subjects.\n";
    }

    // 3. Find the ID for 'Secondaire' teaching type
    $stmt = $db->prepare("SELECT id FROM teaching_types WHERE nom LIKE '%Secondaire%' LIMIT 1");
    $stmt->execute();
    $secondaireId = $stmt->fetchColumn();

    if ($secondaireId) {
        echo "Found 'Secondaire' teaching_type_id: $secondaireId\n";
        
        // 4. Update existing departments to 'Secondaire'
        $db->exec("UPDATE departments SET teaching_type_id = $secondaireId WHERE teaching_type_id IS NULL");
        echo "Existing departments updated to Secondaire.\n";
    } else {
        echo "Warning: 'Secondaire' teaching type not found. Could not set default teaching_type_id for departments.\n";
    }

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
