<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/Core/Session.php';
require_once __DIR__ . '/../src/Core/Security.php';
require_once __DIR__ . '/../src/Models/BaseModel.php';
require_once __DIR__ . '/../src/Models/Timetable.php';
require_once __DIR__ . '/../src/Models/TimetableEntry.php';
require_once __DIR__ . '/../src/Models/TimetableSlot.php';
require_once __DIR__ . '/../src/Models/TimetableAuditLog.php';
require_once __DIR__ . '/../src/Services/Timetable/TimetableLockService.php';
require_once __DIR__ . '/../src/Services/Timetable/TimetableConflictService.php';
require_once __DIR__ . '/../src/Services/Timetable/BulkSchedulingService.php';

use App\Core\Database;
use App\Services\Timetable\BulkSchedulingService;

try {
    $db = Database::getInstance()->getConnection();
    echo "=== TEST DU SERVICE DE PLANIFICATION EN MASSE (BULK SCHEDULING) ===\n\n";

    $service = new BulkSchedulingService($db);

    // 1. Récupérer des IDs réels en BDD
    $weekId = (int)$db->query("SELECT id FROM timetable_weeks ORDER BY id DESC LIMIT 1")->fetchColumn();
    $subjectId = (int)$db->query("SELECT id FROM subjects LIMIT 1")->fetchColumn();
    $teacherId = (int)$db->query("SELECT id FROM users WHERE role IN ('enseignant', 'admin', 'superadmin') LIMIT 1")->fetchColumn();
    $classes = $db->query("SELECT id FROM classes LIMIT 3")->fetchAll(PDO::FETCH_COLUMN);
    $slots = $db->query("SELECT id FROM timetable_time_slots WHERE type_creneau != 'pause' LIMIT 2")->fetchAll(PDO::FETCH_COLUMN);
    $roomId = (int)$db->query("SELECT id FROM class_rooms WHERE status = 1 LIMIT 1")->fetchColumn();

    if (!$weekId || !$subjectId || !$teacherId || empty($classes) || empty($slots)) {
        echo "Données insuffisantes dans la base pour exécuter le test complet.\n";
        exit(0);
    }

    echo "Paramètres de Test :\n";
    echo "- Semaine ID: $weekId\n";
    echo "- Matière ID: $subjectId\n";
    echo "- Enseignant ID: $teacherId\n";
    echo "- Classes IDs: " . implode(', ', $classes) . "\n";
    echo "- Créneaux IDs: " . implode(', ', $slots) . "\n";
    echo "- Salle ID: $roomId\n\n";

    // 2. Test validateBulkSchedule
    $params = [
        'week_id' => $weekId,
        'subject_id' => $subjectId,
        'teacher_id' => $teacherId,
        'class_ids' => $classes,
        'days' => ['Lundi', 'Mercredi'],
        'slot_ids' => $slots,
        'room_mode' => 'auto',
        'couleur_hex' => '#3b82f6'
    ];

    echo "1. Exécution de validateBulkSchedule (Mode Auto)...\n";
    $result = $service->validateBulkSchedule($params);

    echo "Résultat de la pré-validation :\n";
    echo "- Succès : " . ($result['success'] ? 'OUI' : 'NON') . "\n";
    echo "- Total généré : " . $result['total_generated'] . "\n";
    echo "- Valides : " . $result['valid_count'] . "\n";
    echo "- Conflits : " . $result['conflict_count'] . "\n";

    if (!empty($result['schedules'])) {
        echo "\nExemple de programmation générée :\n";
        $first = $result['schedules'][0];
        echo "  - Classe: {$first['class_name']}\n";
        echo "  - Jour: {$first['day_of_week']}\n";
        echo "  - Créneau: {$first['slot_label']}\n";
        echo "  - Salle attribuée: {$first['room_name']}\n";
        echo "  - Conflit : " . ($first['has_conflict'] ? 'OUI (' . implode(' | ', $first['conflict_messages']) . ')' : 'NON') . "\n";
    }

    echo "\n2. Exécution de saveBulkSchedule pour 2 entrées de test...\n";
    $testSchedules = array_slice($result['schedules'], 0, 2);
    $saveResult = $service->saveBulkSchedule($testSchedules, 1);
    echo "Résultat de la sauvegarde :\n";
    echo "- Succès : " . ($saveResult['success'] ? 'OUI' : 'NON') . "\n";
    echo "- Nombre enregistré : " . $saveResult['saved_count'] . "\n";
    echo "- Message : " . $saveResult['message'] . "\n";

    echo "\n=== TEST COMPLET REUSSI AVEC SUCCES ===\n";

} catch (\Throwable $e) {
    echo "ERREUR DURANT LE TEST : " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
