<?php
/**
 * Migration: Ajout des permissions RBAC pour le module Relevé de Notes (Transcripts)
 * et vérification des colonnes code_uv / code_ue dans la table subjects.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    echo "=== MIGRATION RBAC : MODULE RELEVÉ DE NOTES ===\n\n";

    // 1. Insertion des permissions
    $permissions = [
        [
            'code' => 'view_transcripts',
            'name' => 'Consulter les relevés de notes',
            'desc' => 'Visualiser et prévisualiser les relevés de notes des élèves.'
        ],
        [
            'code' => 'manage_transcripts',
            'name' => 'Gérer et imprimer les relevés de notes',
            'desc' => 'Générer, exporter en PDF et imprimer les relevés de notes.'
        ]
    ];

    foreach ($permissions as $perm) {
        $stmt = $db->prepare("SELECT id FROM permissions WHERE perm_code = ?");
        $stmt->execute([$perm['code']]);
        $existingId = $stmt->fetchColumn();

        if (!$existingId) {
            $stmtInsert = $db->prepare("INSERT INTO permissions (perm_code, perm_name, description) VALUES (?, ?, ?)");
            $stmtInsert->execute([$perm['code'], $perm['name'], $perm['desc']]);
            $permId = $db->lastInsertId();
            echo "   -> Permission '{$perm['code']}' créée (ID: {$permId}).\n";
        } else {
            $permId = $existingId;
            echo "   -> Permission '{$perm['code']}' existe déjà (ID: {$permId}).\n";
        }

        // Attribution aux rôles superadmin et admin
        $roles = $db->query("SELECT id, role_code FROM roles WHERE role_code IN ('superadmin', 'admin')")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($roles as $role) {
            $stmtCheck = $db->prepare("SELECT 1 FROM role_permissions WHERE role_id = ? AND permission_id = ?");
            $stmtCheck->execute([$role['id'], $permId]);
            if (!$stmtCheck->fetch()) {
                $stmtAssign = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                $stmtAssign->execute([$role['id'], $permId]);
                echo "      -> Attribuée au rôle '{$role['role_code']}'.\n";
            }
        }
    }

    // 2. Vérification des colonnes code_uv et code_ue dans la table subjects
    echo "\n2. Vérification de la structure de la table 'subjects'...\n";
    $checkUv = $db->query("SHOW COLUMNS FROM subjects LIKE 'code_uv'")->fetch();
    if (!$checkUv) {
        $db->exec("ALTER TABLE subjects ADD COLUMN code_uv VARCHAR(50) NULL DEFAULT NULL AFTER groupe");
        echo "   -> Colonne 'code_uv' ajoutée à 'subjects'.\n";
    }

    $checkUe = $db->query("SHOW COLUMNS FROM subjects LIKE 'code_ue'")->fetch();
    if (!$checkUe) {
        $db->exec("ALTER TABLE subjects ADD COLUMN code_ue VARCHAR(50) NULL DEFAULT NULL AFTER code_uv");
        echo "   -> Colonne 'code_ue' ajoutée à 'subjects'.\n";
    }

    echo "\n=== MIGRATION RELEVÉ DE NOTES RÉUSSIE ===\n";

} catch (\Throwable $e) {
    echo "ERREUR : " . $e->getMessage() . "\n";
    exit(1);
}
