<?php

namespace App\Services\Import;

use Exception;
use PDO;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\SchoolFee;
use App\Models\FeeInstallment;

class GrilleImportProcessor
{
    private PDO $db;
    private array $errors = [];
    private int $successCount = 0;
    private array $classesByName = [];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->warmupLookups();
    }

    /**
     * @return array{success: bool, count: int, errors: list<string>}
     */
    public function process(
        string $filePath, 
        int $academicYearId,
        int $teachingTypeId = 0,
        int $cycleId = 0,
        int $sectionId = 0,
        int $classId = 0
    ): array {
        $this->errors = [];
        $this->successCount = 0;
        try {
            $sheet = IOFactory::load($filePath)->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
            if (count($rows) < 2) {
                throw new Exception('Le document est vide ou ne contient aucune donnée.');
            }

            $headers = array_shift($rows);
            $this->validateHeaders($headers);

            // Fetch allowed class IDs if filters are set
            $allowedClassIds = [];
            $hasFilter = ($teachingTypeId || $cycleId || $sectionId || $classId);
            if ($hasFilter) {
                $q = "SELECT id FROM classes WHERE 1=1";
                $p = [];
                if ($teachingTypeId) {
                    $q .= " AND teaching_type_id = ?";
                    $p[] = $teachingTypeId;
                }
                if ($cycleId) {
                    $q .= " AND cycle_id = ?";
                    $p[] = $cycleId;
                }
                if ($sectionId) {
                    $q .= " AND section_id = ?";
                    $p[] = $sectionId;
                }
                if ($classId) {
                    $q .= " AND id = ?";
                    $p[] = $classId;
                }
                $stmt = $this->db->prepare($q);
                $stmt->execute($p);
                $allowedClassIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            }

            // Fetch active academic year dates to validate boundaries
            $stmtYear = $this->db->prepare("SELECT nom, start_date, end_date FROM academic_years WHERE id = ? LIMIT 1");
            $stmtYear->execute([$academicYearId]);
            $activeYear = $stmtYear->fetch(PDO::FETCH_ASSOC) ?: null;

            $this->db->beginTransaction();

            foreach ($rows as $idx => $row) {
                $line = $idx + 2;
                if (!$this->rowHasData($row)) {
                    continue;
                }
                $this->processRow($row, $line, $academicYearId, $hasFilter, $allowedClassIds, $activeYear);
            }

            if (empty($this->errors)) {
                $this->db->commit();
            } else {
                $this->db->rollBack();
            }

            return [
                'success' => count($this->errors) === 0,
                'count' => $this->successCount,
                'errors' => $this->errors,
            ];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return [
                'success' => false,
                'count' => 0,
                'errors' => ['Erreur fatale : ' . $e->getMessage()],
            ];
        }
    }

    private function rowHasData(array $row): bool
    {
        // Check if class column (A) is filled
        return trim((string) ($row['A'] ?? '')) !== '';
    }

    private function validateHeaders(array $headers): void
    {
        $first = strtolower(trim((string) ($headers['A'] ?? '')));
        if ($first === '' || (!str_contains($first, 'classe') && !str_contains($first, 'class'))) {
            throw new Exception('Format d\'en-tête invalide. La première colonne doit être "Classe".');
        }
    }

    private function processRow(
        array $row, 
        int $line, 
        int $academicYearId,
        bool $hasFilter,
        array $allowedClassIds,
        ?array $activeYear
    ): void {
        $className = trim((string) ($row['A'] ?? ''));
        $fraisInscriptionNouveau = !empty($row['B']) ? (float)str_replace([' ', ','], ['', '.'], $row['B']) : 0.0;
        $fraisInscriptionAncien = !empty($row['C']) ? (float)str_replace([' ', ','], ['', '.'], $row['C']) : 0.0;
        $fraisScolariteBrut = !empty($row['D']) ? (float)str_replace([' ', ','], ['', '.'], $row['D']) : 0.0;
        $nbrTranches = !empty($row['E']) ? (int)$row['E'] : 0;

        $key = mb_strtolower($className);
        if (!isset($this->classesByName[$key])) {
            $this->logError($line, "Classe introuvable dans le système : \"{$className}\"");
            return;
        }
        $classId = (int)$this->classesByName[$key];

        // Skip silently if the class is not in the allowed list of the active filters
        if ($hasFilter && !in_array($classId, $allowedClassIds)) {
            return;
        }

        // Validation des tranches
        $tranchesList = [];
        $sumTranches = 0.0;

        if ($fraisScolariteBrut > 0.01) {
            if ($nbrTranches <= 0) {
                $this->logError($line, "Le nombre de tranches doit être supérieur à 0 car des frais de scolarité sont configurés.");
                return;
            }
            if ($nbrTranches > 6) {
                $this->logError($line, "Le système ne supporte pas plus de 6 tranches (nombre de tranches configuré : {$nbrTranches}).");
                return;
            }

            // Lire et valider chaque tranche
            $trancheColumns = [
                1 => ['name' => 'F', 'amount' => 'G', 'deadline' => 'H'],
                2 => ['name' => 'I', 'amount' => 'J', 'deadline' => 'K'],
                3 => ['name' => 'L', 'amount' => 'M', 'deadline' => 'N'],
                4 => ['name' => 'O', 'amount' => 'P', 'deadline' => 'Q'],
                5 => ['name' => 'R', 'amount' => 'S', 'deadline' => 'T'],
                6 => ['name' => 'U', 'amount' => 'V', 'deadline' => 'W']
            ];

            for ($i = 1; $i <= $nbrTranches; $i++) {
                $cols = $trancheColumns[$i];
                $tName = trim((string)($row[$cols['name']] ?? "Tranche {$i}"));
                $tAmt = !empty($row[$cols['amount']]) ? (float)str_replace([' ', ','], ['', '.'], $row[$cols['amount']]) : 0.0;
                $tDeadlineRaw = $row[$cols['deadline']] ?? '';
                $tDeadline = $this->parseDate($tDeadlineRaw);

                if ($tName === '') {
                    $tName = "Tranche {$i}";
                }

                if ($tAmt <= 0) {
                    $this->logError($line, "Le montant de la Tranche {$i} est requis et doit être positif.");
                    return;
                }

                if (!$tDeadline) {
                    $this->logError($line, "La date d'échéance de la Tranche {$i} est requise et doit être valide (Format: JJ/MM/AAAA ou AAAA-MM-JJ). Saisie: \"{$tDeadlineRaw}\"");
                    return;
                }

                $sumTranches += $tAmt;
                $tranchesList[] = [
                    'order' => $i,
                    'name' => $tName,
                    'amount' => $tAmt,
                    'deadline' => $tDeadline
                ];
            }

            // Vérifier que la somme des tranches est égale au montant brut de la scolarité
            if (abs($sumTranches - $fraisScolariteBrut) > 0.01) {
                $this->logError($line, "Incohérence financière : La somme des tranches (" . number_format($sumTranches, 0, '.', ' ') . " FCFA) est différente des frais de scolarité brut (" . number_format($fraisScolariteBrut, 0, '.', ' ') . " FCFA).");
                return;
            }
        }

        try {
            // 1. Mettre à jour la classe
            $stmtClass = $this->db->prepare("
                UPDATE classes 
                SET frais_inscription = ?, 
                    frais_inscription_reinscription = ?, 
                    frais_scolarite_brut = ?, 
                    nbr_tranches = ? 
                WHERE id = ?
            ");
            $stmtClass->execute([
                $fraisInscriptionNouveau,
                $fraisInscriptionAncien,
                $fraisScolariteBrut,
                $nbrTranches,
                $classId
            ]);

            // 2. Mettre à jour school_fees pour l'année scolaire active (résolution dynamique)
            $stmtCheckFee = $this->db->prepare("SELECT id FROM school_fees WHERE class_id = ? AND academic_year_id = ? LIMIT 1");
            $stmtCheckFee->execute([$classId, $academicYearId]);
            $feeId = $stmtCheckFee->fetchColumn();

            if ($feeId) {
                $stmtUpdateFee = $this->db->prepare("UPDATE school_fees SET amount = ? WHERE id = ?");
                $stmtUpdateFee->execute([$fraisScolariteBrut, $feeId]);
            } else {
                $stmtInsertFee = $this->db->prepare("INSERT INTO school_fees (academic_year_id, class_id, amount) VALUES (?, ?, ?)");
                $stmtInsertFee->execute([$academicYearId, $classId, $fraisScolariteBrut]);
            }

            // 3. Supprimer et insérer les échéances / tranches configurées
            // Suppression des tranches héritées (legacy static installments)
            $del1 = $this->db->prepare("DELETE FROM class_installments WHERE class_id = ?");
            $del1->execute([$classId]);

            // Suppression des tranches spécifiques de l'année scolaire active
            $del2 = $this->db->prepare("DELETE FROM fee_installments WHERE class_id = ? AND academic_year_id = ?");
            $del2->execute([$classId, $academicYearId]);

            // Suppression des dates d'échéance (legacy academic installment deadlines)
            $del3 = $this->db->prepare("DELETE FROM installment_deadlines WHERE class_id = ? AND academic_year_id = ?");
            $del3->execute([$classId, $academicYearId]);

            if ($nbrTranches > 0) {
                $insClassInst = $this->db->prepare("INSERT INTO class_installments (class_id, installment_number, amount) VALUES (?, ?, ?)");
                $insFeeInst = $this->db->prepare("INSERT INTO fee_installments (academic_year_id, name, installment_order, amount, deadline_date, class_id) VALUES (?, ?, ?, ?, ?, ?)");
                $insDeadlines = $this->db->prepare("INSERT INTO installment_deadlines (academic_year_id, class_id, installment_number, deadline_date) VALUES (?, ?, ?, ?)");

                foreach ($tranchesList as $tr) {
                    $insClassInst->execute([$classId, $tr['order'], $tr['amount']]);
                    $insFeeInst->execute([$academicYearId, $tr['name'], $tr['order'], $tr['amount'], $tr['deadline'], $classId]);
                    $insDeadlines->execute([$academicYearId, $classId, $tr['order'], $tr['deadline']]);
                }
            }

            $this->successCount++;
        } catch (\Throwable $e) {
            $this->logError($line, 'Erreur de base de données lors de la mise à jour : ' . $e->getMessage());
        }
    }

    private function parseDate($val): ?string
    {
        if (empty($val)) {
            return null;
        }
        $val = trim((string)$val);
        if ($val === '') {
            return null;
        }

        // Standard format YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
            return $val;
        }

        // French format DD/MM/YYYY or DD-MM-YYYY
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $val, $matches)) {
            return sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
        }

        // Excel numeric serial date format (serial float number)
        if (is_numeric($val) && (float)$val > 1000) {
            try {
                $unixTimestamp = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp((float)$val);
                return date('Y-m-d', $unixTimestamp);
            } catch (\Throwable $e) {
                // fall back
            }
        }

        return null;
    }

    private function warmupLookups(): void
    {
        $classes = $this->db->query("SELECT id, nom FROM classes")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($classes as $row) {
            $this->classesByName[mb_strtolower((string) $row['nom'])] = (int) $row['id'];
        }
    }

    private function logError(int $line, string $message): void
    {
        $this->errors[] = "Ligne {$line} : {$message}";
    }
}
