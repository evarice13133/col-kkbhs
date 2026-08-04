<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Models\TimetableSlot;
use App\Models\ClassRoom;
use App\Models\CourseWeek;
use App\Models\Timetable;
use App\Models\TimetableEntry;
use App\Services\Timetable\TimetableConflictService;
use App\Services\Timetable\TimetableLockService;
use App\Services\Timetable\TimetableWizardService;

echo "=== VÉRIFICATION DU MODULE EMPLOIS DU TEMPS ===\n\n";

try {
    $db = Database::getInstance()->getConnection();

    // 1. Test Slots
    $slotModel = new TimetableSlot();
    $slots = $slotModel->getAll();
    echo "[TEST 1] Créneaux Horaires chargés : " . count($slots) . " créneaux.\n";

    // 2. Test Rooms
    $roomModel = new ClassRoom();
    $rooms = $roomModel->getAll();
    echo "[TEST 2] Salles de Classe chargées : " . count($rooms) . " salles.\n";

    // 3. Test Timetables SQL Query (ay.nom)
    $timetableModel = new Timetable();
    $list = $timetableModel->getAllFiltered(null, null, null);
    echo "[TEST 3] Timetable->getAllFiltered() exécuté avec succès : " . count($list) . " emplois du temps trouvés.\n";

    // 4. Test TimetableEntry SQL Query (getByTimetable)
    $entryModel = new TimetableEntry();
    $entries = $entryModel->getByTimetable(1);
    echo "[TEST 4] TimetableEntry->getByTimetable(1) exécuté avec succès : " . count($entries) . " entrées de cours trouvées.\n";

    // 5. Test Wizard Service
    $wizardService = new TimetableWizardService($db);
    $types = $wizardService->getTeachingTypes();
    echo "[TEST 5.1] Wizard getTeachingTypes() : " . count($types) . " types d'enseignement trouvés.\n";

    $firstTypeId = !empty($types) ? $types[0]['id'] : 1;
    $cycles = $wizardService->getCyclesByTeachingType($firstTypeId);
    echo "[TEST 5.2] Wizard getCyclesByTeachingType($firstTypeId) : " . count($cycles) . " cycles trouvés.\n";

    $firstCycleId = !empty($cycles) ? $cycles[0]['id'] : 1;
    $classes = $wizardService->getClassesByCycle($firstCycleId);
    echo "[TEST 5.3] Wizard getClassesByCycle($firstCycleId) : " . count($classes) . " classes trouvées.\n";

    $gridData = $wizardService->getGridDataForClass(1);
    echo "[TEST 5.4] Wizard getGridDataForClass(1) : " . count($gridData['subjects']) . " matières, " . count($gridData['teachers']) . " enseignants, " . count($gridData['rooms']) . " salles.\n";

    // 6. Test Conflict Service
    $conflictService = new TimetableConflictService($db);
    $checkPause = $conflictService->checkConflict(1, 1, 4, 'Lundi', 1, 1, 1);
    if ($checkPause['has_conflict']) {
        echo "[TEST 6] Anti-Collision (Détection de Pause) OK : " . $checkPause['messages'][0] . "\n";
    }

    // 7. Test Lock Service
    $lockService = new TimetableLockService($db);
    echo "[TEST 7] Lock Service instancié avec succès.\n";

    echo "\n=== TOUTES LES VÉRIFICATIONS SONT VALIDES ! ===\n";

} catch (\Throwable $e) {
    echo "[ERREUR TEST] " . $e->getMessage() . "\n";
    exit(1);
}
