<?php

namespace App\Services\Import;

use App\Core\Database;
use Exception;
use PDO;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Import Excel des comptes enseignants (users.role = enseignant).
 */
class TeacherImportProcessor
{
    private PDO $db;

    /** @var list<string> */
    private array $errors = [];

    private int $successCount = 0;

    private const DEFAULT_PASSWORD = '0000';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * @return array{success: bool, count: int, errors: list<string>}
     */
    public function process(string $filePath, string $lang = 'fr'): array
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, true, true, true);
            if (count($data) < 2) {
                throw new Exception('Document vide ou sans données.');
            }

            $headers = array_shift($data);
            $this->validateHeaders($headers, $lang);

            $this->db->beginTransaction();

            foreach ($data as $rowIndex => $row) {
                $line = $rowIndex + 2;
                if (!$this->rowHasData($row)) {
                    continue;
                }
                $this->processRow($row, $line);
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
        foreach (['A', 'B', 'C'] as $c) {
            if (!empty(trim((string) ($row[$c] ?? '')))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int|string, mixed> $headers
     */
    private function validateHeaders(array $headers, string $lang): void
    {
        $expected = $lang === 'en'
            ? ['Last name', 'First name', 'Username']
            : ['Nom', 'Prenom', 'Username'];

        $i = 0;
        foreach (range('A', 'C') as $col) {
            $h = trim((string) ($headers[$col] ?? ''));
            if (strcasecmp($h, $expected[$i]) !== 0) {
                throw new Exception(
                    "En-tête colonne {$col} invalide : attendu « {$expected[$i]} », reçu « {$h} »."
                );
            }
            $i++;
        }
    }

    /**
     * @param array<string, mixed> $row
     */
    private function processRow(array $row, int $line): void
    {
        $nom = trim((string) ($row['A'] ?? ''));
        $prenom = trim((string) ($row['B'] ?? ''));
        $username = trim((string) ($row['C'] ?? ''));

        if ($nom === '' || $prenom === '' || $username === '') {
            $this->logError($line, 'Nom, prénom et username sont obligatoires.');

            return;
        }

        $stmt = $this->db->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $this->logError($line, "Username déjà utilisé : {$username}");

            return;
        }

        $email = $username . '+import' . bin2hex(random_bytes(3)) . '@notesmaster.local';

        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $this->logError($line, "E-mail déjà utilisé : {$email}");

            return;
        }

        $hash = password_hash(self::DEFAULT_PASSWORD, PASSWORD_BCRYPT);

        try {
            $this->db->prepare(
                "INSERT INTO users (nom, prenom, username, email, password, role) VALUES (?, ?, ?, ?, ?, 'enseignant')"
            )->execute([$nom, $prenom, $username, $email, $hash]);

            $this->successCount++;
        } catch (\Throwable $e) {
            $this->logError($line, 'Base : ' . $e->getMessage());
        }
    }

    private function logError(int $line, string $message): void
    {
        $this->errors[] = "Ligne {$line} : {$message}";
    }
}
