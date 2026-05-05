<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use PDO;

class SequenceController
{
    private $db;

    private const DEFAULT_SEQUENCES = [
        ['code' => 'SEQ1', 'label' => 'Trimestre 1 - Sequence 1', 'short_label' => 'SEQ 1', 'trimestre' => 1, 'position' => 1],
        ['code' => 'SEQ2', 'label' => 'Trimestre 1 - Sequence 2', 'short_label' => 'SEQ 2', 'trimestre' => 1, 'position' => 2],
        ['code' => 'SEQ3', 'label' => 'Trimestre 2 - Sequence 3', 'short_label' => 'SEQ 3', 'trimestre' => 2, 'position' => 3],
        ['code' => 'SEQ4', 'label' => 'Trimestre 2 - Sequence 4', 'short_label' => 'SEQ 4', 'trimestre' => 2, 'position' => 4],
        ['code' => 'SEQ5', 'label' => 'Trimestre 3 - Sequence 5', 'short_label' => 'SEQ 5', 'trimestre' => 3, 'position' => 5],
        ['code' => 'SEQ6', 'label' => 'Trimestre 3 - Sequence 6', 'short_label' => 'SEQ 6', 'trimestre' => 3, 'position' => 6],
    ];

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureSchema();
    }

    /**
     * Liste toutes les séquences (évaluations)
     */
    public function index()
    {
        $stmt = $this->db->query("SELECT * FROM sequences ORDER BY position ASC");
        $sequences = $stmt->fetchAll(PDO::FETCH_ASSOC);
        include __DIR__ . '/../Views/sequences/index.php';
    }

    /**
     * Affiche le formulaire de création
     */
    public function create()
    {
        include __DIR__ . '/../Views/sequences/create.php';
    }

    /**
     * Enregistre une nouvelle séquence
     */
    public function store()
    {
        $code = $_POST['code'] ?? '';
        $label = $_POST['label'] ?? '';
        $short_label = $_POST['short_label'] ?? '';
        $trimestre = (int) ($_POST['trimestre'] ?? 1);
        $position = (int) ($_POST['position'] ?? 1);

        $stmt = $this->db->prepare("INSERT INTO sequences (code, label, short_label, trimestre, position) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$code, $label, $short_label, $trimestre, $position]);

        header("Location: /sequences");
        exit;
    }

    /**
     * Affiche le formulaire d'édition
     */
    public function edit($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM sequences WHERE id = ?");
        $stmt->execute([(int) $id]);
        $sequence = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$sequence) {
            header("Location: /sequences");
            exit;
        }

        include __DIR__ . '/../Views/sequences/edit.php';
    }

    /**
     * Met à jour une séquence
     */
    public function update($id)
    {
        $code = $_POST['code'] ?? '';
        $label = $_POST['label'] ?? '';
        $short_label = $_POST['short_label'] ?? '';
        $trimestre = (int) ($_POST['trimestre'] ?? 1);
        $position = (int) ($_POST['position'] ?? 1);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        $stmt = $this->db->prepare("UPDATE sequences SET code = ?, label = ?, short_label = ?, trimestre = ?, position = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$code, $label, $short_label, $trimestre, $position, $is_active, (int) $id]);

        header("Location: /sequences");
        exit;
    }

    /**
     * Supprime une séquence
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM sequences WHERE id = ?");
        $stmt->execute([(int) $id]);
        header("Location: /sequences");
        exit;
    }

    /**
     * Active/Désactive une séquence
     */
    public function toggle($id)
    {
        $stmt = $this->db->prepare("UPDATE sequences SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([(int) $id]);
        header("Location: /sequences");
        exit;
    }

    /**
     * Garantit que la table existe et contient les données par défaut
     */
    private function ensureSchema()
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS sequences (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(20) NOT NULL UNIQUE,
            label VARCHAR(100) NOT NULL UNIQUE,
            short_label VARCHAR(20) NULL,
            trimestre TINYINT NOT NULL,
            position TINYINT NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1
        )");

        $count = $this->db->query("SELECT COUNT(*) FROM sequences")->fetchColumn();
        if ($count == 0) {
            $stmt = $this->db->prepare("INSERT INTO sequences (code, label, short_label, trimestre, position, is_active) VALUES (?, ?, ?, ?, ?, 1)");
            foreach (self::DEFAULT_SEQUENCES as $sequence) {
                $stmt->execute([$sequence['code'], $sequence['label'], $sequence['short_label'], $sequence['trimestre'], $sequence['position']]);
            }
        }
    }
}
