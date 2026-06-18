<?php
require_once 'c:/laragon/www/Nouveau dossier/copobimat.camertech/config/config.php';
require_once 'c:/laragon/www/Nouveau dossier/copobimat.camertech/vendor/autoload.php';

use App\Core\Database;
use App\Services\AcademicYearService;

$db = Database::getInstance()->getConnection();
$academicYearService = new AcademicYearService($db);

try {
    $db->beginTransaction();

    $nom = "Classe Test Verification " . time();
    $cycle_id = null;
    $section_id = null;
    $department_id = null;
    $teaching_type_id = null;
    $frais_inscription = 15000.0;
    $frais_inscription_reinscription = 10000.0;
    $frais_scolarite_brut = 100000.0;
    $nbr_tranches = 3;
    $tranches = [
        1 => ['amount' => 50000.0, 'deadline' => '2026-10-31'],
        2 => ['amount' => 30000.0, 'deadline' => '2026-12-31'],
        3 => ['amount' => 20000.0, 'deadline' => '2027-02-28']
    ];

    // Insert class
    $stmt = $db->prepare("INSERT INTO classes (nom, cycle_id, section_id, department_id, teaching_type_id, frais_inscription, frais_inscription_reinscription, frais_scolarite_brut, nbr_tranches) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nom, $cycle_id, $section_id, $department_id, $teaching_type_id, $frais_inscription, $frais_inscription_reinscription, $frais_scolarite_brut, $nbr_tranches]);
    $newClassId = (int) $db->lastInsertId();
    echo "Class inserted with ID: $newClassId\n";

    $activeYearId = $academicYearService->getActiveYearId();
    echo "Active Academic Year ID: $activeYearId\n";

    $ins = $db->prepare("INSERT INTO class_installments (class_id, installment_number, amount) VALUES (?, ?, ?)");
    $insFeeInst = $db->prepare("INSERT INTO fee_installments (academic_year_id, name, installment_order, amount, deadline_date, class_id) VALUES (?, ?, ?, ?, ?, ?)");
    $insDeadlines = $db->prepare("INSERT INTO installment_deadlines (academic_year_id, class_id, installment_number, deadline_date) VALUES (?, ?, ?, ?)");

    for ($i = 1; $i <= $nbr_tranches; $i++) {
        $amt = $tranches[$i]['amount'];
        $deadline = $tranches[$i]['deadline'];

        $ins->execute([$newClassId, $i, $amt]);
        $insFeeInst->execute([$activeYearId, "Tranche " . $i, $i, $amt, $deadline, $newClassId]);
        if ($deadline) {
            $insDeadlines->execute([$activeYearId, $newClassId, $i, $deadline]);
        }
    }
    echo "Installments inserted in all tables.\n";

    // Verify class_installments
    $stmtVerifClass = $db->prepare("SELECT * FROM class_installments WHERE class_id = ? ORDER BY installment_number ASC");
    $stmtVerifClass->execute([$newClassId]);
    $resClass = $stmtVerifClass->fetchAll(PDO::FETCH_ASSOC);
    echo "Verification class_installments: " . count($resClass) . " rows found.\n";
    foreach ($resClass as $row) {
        echo "  - Installment #{$row['installment_number']}: amount={$row['amount']}\n";
    }

    // Verify fee_installments
    $stmtVerifFee = $db->prepare("SELECT * FROM fee_installments WHERE class_id = ? AND academic_year_id = ? ORDER BY installment_order ASC");
    $stmtVerifFee->execute([$newClassId, $activeYearId]);
    $resFee = $stmtVerifFee->fetchAll(PDO::FETCH_ASSOC);
    echo "Verification fee_installments: " . count($resFee) . " rows found.\n";
    foreach ($resFee as $row) {
        echo "  - Installment #{$row['installment_order']}: name='{$row['name']}' amount={$row['amount']} deadline={$row['deadline_date']}\n";
    }

    // Verify installment_deadlines
    $stmtVerifDeadlines = $db->prepare("SELECT * FROM installment_deadlines WHERE class_id = ? AND academic_year_id = ? ORDER BY installment_number ASC");
    $stmtVerifDeadlines->execute([$newClassId, $activeYearId]);
    $resDeadlines = $stmtVerifDeadlines->fetchAll(PDO::FETCH_ASSOC);
    echo "Verification installment_deadlines: " . count($resDeadlines) . " rows found.\n";
    foreach ($resDeadlines as $row) {
        echo "  - Installment #{$row['installment_number']}: deadline={$row['deadline_date']}\n";
    }

    // Success check
    if (count($resClass) === 3 && count($resFee) === 3 && count($resDeadlines) === 3) {
        echo "SUCCESS: All tables have 3 installments with the correct deadlines!\n";
    } else {
        echo "FAILURE: Row counts do not match!\n";
    }

    $db->rollBack();
    echo "Transaction rolled back successfully.\n";
} catch (\Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
}
