<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    $column = $db->query("SHOW COLUMNS FROM timetable_weeks LIKE 'id'")->fetch(PDO::FETCH_ASSOC);

    if (!$column) {
        throw new RuntimeException("Column timetable_weeks.id was not found.");
    }

    $zeroWeekExists = (bool)$db->query("SELECT 1 FROM timetable_weeks WHERE id = 0 LIMIT 1")->fetchColumn();
    if ($zeroWeekExists) {
        $nextId = (int)$db->query("SELECT COALESCE(MAX(id), 0) + 1 FROM timetable_weeks")->fetchColumn();
        $hasTimetables = (bool)$db->query("SHOW TABLES LIKE 'timetables'")->fetchColumn();

        $db->exec("SET FOREIGN_KEY_CHECKS = 0");
        try {
            $db->beginTransaction();
            $stmt = $db->prepare("UPDATE timetable_weeks SET id = ? WHERE id = 0");
            $stmt->execute([$nextId]);
            if ($hasTimetables) {
                $stmt = $db->prepare("UPDATE timetables SET week_id = ? WHERE week_id = 0");
                $stmt->execute([$nextId]);
            }
            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        } finally {
            $db->exec("SET FOREIGN_KEY_CHECKS = 1");
        }
    }

    if (stripos((string)$column['Extra'], 'auto_increment') !== false) {
        echo "timetable_weeks.id already has AUTO_INCREMENT.\n";
        exit(0);
    }

    $db->exec("ALTER TABLE timetable_weeks MODIFY COLUMN id INT(11) NOT NULL AUTO_INCREMENT");
    echo "Added AUTO_INCREMENT to timetable_weeks.id.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Migration failed: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
