<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

$pdo = App\Core\Database::getInstance()->getConnection();

$tablesToTruncate = [
    'discipline',
    'enrollments',
    'grades',
    'insolvent_students',
    'payment_receipts',
    'student_payment_allocations',
    'payments',
    'student_payments',
    'student_installments',
    'student_discounts',
    'student_scholarships',
    'students',
    'expenses',
    'expense_logs',
    'receipt_verifications_log',
    'conseils_classe',
    'decisions_fin_annee',
    'historique_modifications_conseil',
    'historique_passages'
];

echo "=== TRUNCATE / DELETE CHECK ===\n";
foreach ($tablesToTruncate as $tbl) {
    $cnt = $pdo->query("SELECT COUNT(*) FROM `$tbl`")->fetchColumn();
    echo "$tbl: $cnt rows\n";
}

echo "\n=== FINANCIAL HISTORY FILTER CHECK ===\n";
$fh_total = $pdo->query("SELECT COUNT(*) FROM financial_history")->fetchColumn();
$fh_to_delete = $pdo->query("SELECT COUNT(*) FROM financial_history WHERE entity_type IN ('payment', 'student_payment')")->fetchColumn();
$fh_to_keep = $pdo->query("SELECT COUNT(*) FROM financial_history WHERE entity_type NOT IN ('payment', 'student_payment')")->fetchColumn();
echo "Financial history - Total: $fh_total, Payments to delete: $fh_to_delete, Config to keep: $fh_to_keep\n";

echo "\n=== ACTIVITY LOGS FILTER CHECK ===\n";
$al_total = $pdo->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();
$al_entity_types = $pdo->query("SELECT DISTINCT entity_type FROM activity_logs")->fetchAll(PDO::FETCH_COLUMN);
echo "Activity logs - Total: $al_total, Entity types present: " . implode(', ', array_map(fn($v)=>$v?:'NULL', $al_entity_types)) . "\n";
