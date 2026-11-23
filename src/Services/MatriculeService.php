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
    }

    /**
     * Génère un matricule unique selon le type d’enseignement de la classe.
     * 
     * @param int|null $classId ID de la classe pour le token {CLASS}
     * @return string Le matricule généré
     */
    public function generate(?int $classId): string
    {
        $settingsStore = $this->getSettingsStore($classId);
        $config = $settingsStore->all();

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
                $settingsStore->set('matricule_counter', (string) ($counter + 1));
                return $matricule;
            }

            $counter++;
        }
    }

    /**
     * Génère un matricule explicitement temporaire pour les imports.
     */
    public function generateTemporary(?int $classId): string
    {
        $config = $this->getSettingsStore($classId)->all();
        $schoolCode = preg_replace('/[^A-Z0-9]+/i', '', strtoupper(trim((string) ($config['school_code'] ?? 'CMR'))));
        $schoolCode = $schoolCode !== '' ? $schoolCode : 'CMR';
        $classToken = $this->buildClassToken($classId);

        do {
            $matricule = sprintf(
                'TMP-%s-%s-%s-%s',
                $schoolCode,
                $classToken,
                date('YmdHis'),
                strtoupper(bin2hex(random_bytes(3)))
            );
        } while ($this->exists($matricule));

        return $matricule;
    }

    private function exists(string $matricule): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM students WHERE email = ? LIMIT 1");
        $stmt->execute([$matricule]);
        return (bool) $stmt->fetchColumn();
    }

    private function getSettingsStore(?int $classId): SettingsStore
    {
        $teachingTypeId = null;
        if ($classId !== null && $classId > 0) {
            $stmt = $this->db->prepare('SELECT teaching_type_id FROM classes WHERE id = ?');
            $stmt->execute([$classId]);
            $value = $stmt->fetchColumn();
            if ($value !== false && (int) $value > 0) {
                $teachingTypeId = (int) $value;
            }
        }

        return new SettingsStore($this->db, $teachingTypeId);
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
