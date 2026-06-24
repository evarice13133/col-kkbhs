<?php

namespace App\Models;

use PDO;

/**
 * Class DiscountType
 * 
 * Represents a type of discount or scholarship.
 */
class DiscountType extends BaseModel {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Fetch all discount types.
     */
    public function getAll() {
        return $this->db->query("
            SELECT dt.*, u.nom as creator_nom, u.prenom as creator_prenom
            FROM discount_types dt
            LEFT JOIN users u ON dt.created_by = u.id
            ORDER BY dt.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch all active discount types.
     */
    public function getAllActive() {
        return $this->db->query("
            SELECT * FROM discount_types 
            WHERE status = 'active' 
            ORDER BY name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find a type by ID.
     */
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM discount_types WHERE id = ? LIMIT 1");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find a type by name (case-insensitive).
     */
    public function findByName($name) {
        $stmt = $this->db->prepare("SELECT * FROM discount_types WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) LIMIT 1");
        $stmt->execute([$name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new discount type.
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO discount_types (name, description, comment, status, created_by)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            trim((string)$data['name']),
            !empty($data['description']) ? trim((string)$data['description']) : null,
            !empty($data['comment']) ? trim((string)$data['comment']) : null,
            trim((string)($data['status'] ?? 'active')),
            !empty($data['created_by']) ? (int)$data['created_by'] : null
        ]);
    }

    /**
     * Update an existing discount type.
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE discount_types 
            SET name = ?, description = ?, comment = ?, status = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            trim((string)$data['name']),
            !empty($data['description']) ? trim((string)$data['description']) : null,
            !empty($data['comment']) ? trim((string)$data['comment']) : null,
            trim((string)($data['status'] ?? 'active')),
            (int)$id
        ]);
    }

    /**
     * Delete a discount type by ID.
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM discount_types WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    /**
     * Check if a discount type is used in any discounts or scholarships.
     */
    public function isUsed($id) {
        $id = (int)$id;
        
        // Check student_discounts
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM student_discounts WHERE discount_type_id = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) return true;

        // Check class_discounts
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM class_discounts WHERE discount_type_id = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) return true;

        // Check student_scholarships
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM student_scholarships WHERE discount_type_id = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) return true;

        // Check class_scholarships
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM class_scholarships WHERE discount_type_id = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) return true;

        return false;
    }
}
