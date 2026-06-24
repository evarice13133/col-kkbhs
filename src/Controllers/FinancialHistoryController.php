<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use PDO;

/**
 * FinancialHistoryController
 * 
 * Permet de consulter l'historique complet des transactions et des modifications financières.
 */
class FinancialHistoryController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();

        // Sécurité : Accès restreint aux administrateurs
        if (!in_array(Session::get('user_role'), ['superadmin', 'admin'])) {
            header("Location: /");
            exit;
        }
    }

    /**
     * Affiche l'historique financier.
     */
    public function index()
    {
        $stmt = $this->db->query("
            SELECT fh.*, u.nom as user_nom, u.prenom as user_prenom, u.role as user_role
            FROM financial_history fh
            LEFT JOIN users u ON fh.user_id = u.id
            ORDER BY fh.event_date DESC, fh.id DESC
        ");
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/financial_history/index.php';
    }
}
