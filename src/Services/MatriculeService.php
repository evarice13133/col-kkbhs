<?php

namespace App\Services;

use PDO;

/**
 * MatriculeService
 * 
 * Centralise la logique de génération des matricules pour garantir l'unicité
 * et la cohérence du format à travers tout le système (Création manuelle, Import, etc).
 */
class MatriculeService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->ensureSettings();
    }

    /**
     * Génère un matricule unique basé sur la configuration globale.
     * 
     * @param int|null $classId ID de la classe pour le token {CLASS}
     * @return string Le matricule généré
     */
    public function generate(?int $classId): string
    {
        $config = $this->getSettings();

        $schoolCode = trim((string) ($config['school_code'] ?? 'CMR-COL'));
        $format = trim((string) ($config['matricule_format'] ?? '{SCHOOL_CODE}-{CLASS}-MT{COUNTER}'));
        $classToken = $this->buildClassToken($classId);
        $counter = max(1, (int) ($config['matricule_counter'] ?? 1));

        // On boucle jusqu'à trouver un matricule encore libre dans la base.
        while (true) {
            $counterPad = str_pad((string) $counter, 6, '0', STR_PAD_LEFT);
            $matricule = str_replace(
                ['{SCHOOL_CODE}', '{CLASS}', '{COUNTER}'],
                [$schoolCode !== '' ? $schoolCode : 'CMR-COL', $classToken, $counterPad],
                $format
            );

            if (!$this->exists($matricule)) {
                // On met à jour le compteur global
                $this->db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'matricule_counter'")
                    ->execute([$counter + 1]);
                return $matricule;
            }

            $counter++;
        }
    }

    private function exists(string $matricule): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM students WHERE email = ? LIMIT 1");
        $stmt->execute([$matricule]);
        return (bool) $stmt->fetchColumn();
    }

    private function ensureSettings(): void
    {
        try {
            $this->db->exec("CREATE TABLE IF NOT EXISTS settings (setting_key VARCHAR(50) PRIMARY KEY, setting_value TEXT)");
            $this->db->exec("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
                ('school_code', 'CMR-COL'),
                ('matricule_format', '{SCHOOL_CODE}-{CLASS}-MT{COUNTER}'),
                ('matricule_counter', '1')");
        } catch (\Exception $e) {
        }
    }

    private function getSettings(): array
    {
        $stmt = $this->db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('school_code', 'matricule_format', 'matricule_counter')");
        $config = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $config[$row['setting_key']] = $row['setting_value'];
        }
        return $config;
    }

    private function buildClassToken(?int $classId): string
    {
        if (!$classId) {
            return 'IND';
        }

        $stmt = $this->db->prepare("SELECT nom FROM classes WHERE id = ?");
        $stmt->execute([$classId]);
        $class = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$class || empty($class['nom'])) {
            return 'IND';
        }

        $value = (string) $class['nom'];
        
        // Nettoyage pour le matricule (pas d'espaces, pas d'accents)
        $normalized = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($normalized !== false && $normalized !== '') {
            $value = $normalized;
        }

        $value = strtoupper((string) preg_replace('/[^A-Z0-9]+/i', '', $value));
        return $value !== '' ? $value : 'IND';
    }
}
