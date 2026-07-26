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
     * Liste toutes les séquences (évaluations) avec filtre par type d'enseignement actif
     */
    public function index()
    {
        $teaching_type_id = !empty($_GET['teaching_type_id']) ? (int)$_GET['teaching_type_id'] : null;

        $sql = "SELECT s.*, tt.nom as teaching_type_nom, tt.code as teaching_type_code 
                FROM sequences s 
                LEFT JOIN teaching_types tt ON s.teaching_type_id = tt.id
                WHERE (tt.actif = 1 OR s.teaching_type_id IS NULL)";
        
        $params = [];
        if ($teaching_type_id) {
            $sql .= " AND s.teaching_type_id = ?";
            $params[] = $teaching_type_id;
        }
        $sql .= " ORDER BY s.position ASC, s.code ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $sequences = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $teachingTypes = $this->db->query("SELECT id, nom, code FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $filters = ['teaching_type_id' => $teaching_type_id];

        include __DIR__ . '/../Views/sequences/index.php';
    }

    /**
     * Affiche le formulaire de création
     */
    public function create()
    {
        $teachingTypes = $this->db->query("SELECT id, nom, code FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        include __DIR__ . '/../Views/sequences/create.php';
    }

    /**
     * Enregistre une nouvelle séquence / évaluation
     */
    public function store()
    {
        $teaching_type_id = !empty($_POST['teaching_type_id']) ? (int)$_POST['teaching_type_id'] : null;
        $code = trim((string)($_POST['code'] ?? ''));
        $label = trim((string)($_POST['label'] ?? ''));
        $short_label = trim((string)($_POST['short_label'] ?? ''));
        $trimestre = (int) ($_POST['trimestre'] ?? 1);
        $position = (int) ($_POST['position'] ?? 1);
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

        $stmt = $this->db->prepare("INSERT INTO sequences (teaching_type_id, code, label, short_label, trimestre, position, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$teaching_type_id, $code, $label, $short_label, $trimestre, $position, $start_date, $end_date]);

        Session::setFlash('success', __('created_success'));
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

        $teachingTypes = $this->db->query("SELECT id, nom, code FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        include __DIR__ . '/../Views/sequences/edit.php';
    }

    /**
     * Met à jour une séquence
     */
    public function update($id)
    {
        $teaching_type_id = !empty($_POST['teaching_type_id']) ? (int)$_POST['teaching_type_id'] : null;
        $code = trim((string)($_POST['code'] ?? ''));
        $label = trim((string)($_POST['label'] ?? ''));
        $short_label = trim((string)($_POST['short_label'] ?? ''));
        $trimestre = (int) ($_POST['trimestre'] ?? 1);
        $position = (int) ($_POST['position'] ?? 1);
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        $stmt = $this->db->prepare("UPDATE sequences SET teaching_type_id = ?, code = ?, label = ?, short_label = ?, trimestre = ?, position = ?, start_date = ?, end_date = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$teaching_type_id, $code, $label, $short_label, $trimestre, $position, $start_date, $end_date, $is_active, (int) $id]);

        Session::setFlash('success', __('updated_success'));
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
        Session::setFlash('success', __('deleted_success'));
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
        Session::setFlash('success', __('updated_success'));
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

            teaching_type_id INT NULL,

            code VARCHAR(20) NOT NULL,

            label VARCHAR(100) NOT NULL,

            short_label VARCHAR(20) NULL,

            start_date DATE NULL,

            end_date DATE NULL,

            trimestre TINYINT NOT NULL DEFAULT 1,

            position TINYINT NOT NULL DEFAULT 1,

            is_active TINYINT(1) NOT NULL DEFAULT 1

        )");



        $count = $this->db->query("SELECT COUNT(*) FROM sequences")->fetchColumn();

        if ($count == 0) {

            $stmtTT = $this->db->query("SELECT id FROM teaching_types WHERE code = 'ESG' OR LOWER(nom) LIKE '%secondaire%' LIMIT 1");

            $defaultTT = $stmtTT ? $stmtTT->fetchColumn() : null;

            if (!$defaultTT) {
                $defaultTT = $this->db->query("SELECT id FROM teaching_types ORDER BY id ASC LIMIT 1")->fetchColumn();
            }

            if (!$defaultTT) {
                return; // Ne pas insérer si aucun type d'enseignement n'existe
            }



            $stmt = $this->db->prepare("INSERT INTO sequences (teaching_type_id, code, label, short_label, trimestre, position, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");

            foreach (self::DEFAULT_SEQUENCES as $sequence) {

                $stmt->execute([$defaultTT, $sequence['code'], $sequence['label'], $sequence['short_label'], $sequence['trimestre'], $sequence['position']]);

            }

        }

    }
}
