<?php

namespace App\Services;

use App\Core\Database;
use App\Core\PermissionManager;
use PDO;

/**
 * Class PermissionAutoDetectorService
 * 
 * Service d'analyse automatique du code source et du routage pour la détection
 * des modules, sous-modules, actions CRUD et création automatique des permissions manquantes.
 */
class PermissionAutoDetectorService
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance()->getConnection();
    }

    /**
     * Analyse l'application et enregistre automatiquement toute nouvelle permission détectée.
     * 
     * @return array Rapport récapitulatif des permissions scannées, créées et existantes
     */
    public function scanAndSync(): array
    {
        $detected = $this->detectFromCodebase();
        
        $createdCount = 0;
        $existingCount = 0;
        $newPermissions = [];

        $stmtCheck = $this->db->prepare("SELECT id FROM permissions WHERE perm_code = ?");
        $stmtInsert = $this->db->prepare("
            INSERT INTO permissions (perm_code, perm_name, module, submodule, action, description, criticality, status, is_system)
            VALUES (:perm_code, :perm_name, :module, :submodule, :action, :description, :criticality, 'active', 0)
        ");

        foreach ($detected as $item) {
            $stmtCheck->execute([$item['perm_code']]);
            $exists = $stmtCheck->fetchColumn();

            if (!$exists) {
                $stmtInsert->execute([
                    'perm_code' => $item['perm_code'],
                    'perm_name' => $item['perm_name'],
                    'module' => $item['module'],
                    'submodule' => $item['submodule'],
                    'action' => $item['action'],
                    'description' => $item['description'],
                    'criticality' => $item['criticality']
                ]);
                $createdCount++;
                $newPermissions[] = $item;
            } else {
                $existingCount++;
            }
        }

        // Vider le cache de permissions
        PermissionManager::clearCache();

        // Logger l'opération d'analyse
        PermissionManager::logAudit(
            'scan_executed',
            'system',
            'auto_detector',
            "Scan automatique exécuté : {$createdCount} nouvelle(s) permission(s) détectée(s).",
            null,
            ['scanned_total' => count($detected), 'created' => $createdCount, 'existing' => $existingCount]
        );

        return [
            'total_scanned' => count($detected),
            'created_count' => $createdCount,
            'existing_count' => $existingCount,
            'new_permissions' => $newPermissions
        ];
    }

    /**
     * Détecte les composants à partir des contrôleurs, vues et fichiers de routing.
     * 
     * @return array Liste des permissions potentielles
     */
    public function detectFromCodebase(): array
    {
        $permissions = [];
        $srcDir = __DIR__ . '/..';

        // 1. Scan des contrôleurs dans src/Controllers
        $controllersDir = $srcDir . '/Controllers';
        if (is_dir($controllersDir)) {
            foreach (scandir($controllersDir) as $file) {
                if (str_ends_with($file, 'Controller.php')) {
                    $controllerName = str_replace('Controller.php', '', $file);
                    $moduleCode = strtolower($controllerName);

                    $content = file_get_contents($controllersDir . '/' . $file);
                    
                    // Détection des méthodes publiques
                    preg_match_all('/public\s+function\s+([a-zA-Z0-9_]+)\s*\(/', $content, $matches);
                    if (!empty($matches[1])) {
                        foreach ($matches[1] as $method) {
                            if (in_array($method, ['__construct', 'render', 'jsonResponse', 'redirect'])) {
                                continue;
                            }

                            $actionType = $this->inferActionType($method);
                            $permCode = "{$moduleCode}_{$method}";
                            $permName = ucfirst($actionType) . " " . ucfirst($moduleCode) . " (" . $method . ")";

                            $permissions[$permCode] = [
                                'perm_code' => $permCode,
                                'perm_name' => $permName,
                                'module' => $this->categorizeModule($moduleCode),
                                'submodule' => $moduleCode,
                                'action' => $actionType,
                                'description' => "Permission auto-détectée pour la méthode {$controllerName}::{$method}()",
                                'criticality' => $actionType === 'delete' ? 'high' : ($actionType === 'create' || $actionType === 'edit' ? 'medium' : 'low')
                            ];
                        }
                    }
                }
            }
        }

        // 2. Scan des vérifications de permissions existantes (requirePermission / hasPermission / hasRole)
        $filesToScan = [
            $srcDir . '/../public/index.php',
            $srcDir . '/Views/templates/layout.php'
        ];

        foreach ($filesToScan as $filePath) {
            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                
                // Detection codes dans hasPermission('code') ou requirePermission('code')
                preg_match_all("/(?:hasPermission|requirePermission)\s*\(\s*['\"]([^'\"]+)['\"]/", $content, $permMatches);
                if (!empty($permMatches[1])) {
                    foreach ($permMatches[1] as $code) {
                        if (!isset($permissions[$code])) {
                            $parts = explode('_', $code, 2);
                            $action = $parts[0] ?? 'view';
                            $submodule = $parts[1] ?? 'general';

                            $permissions[$code] = [
                                'perm_code' => $code,
                                'perm_name' => ucfirst(str_replace('_', ' ', $code)),
                                'module' => $this->categorizeModule($submodule),
                                'submodule' => $submodule,
                                'action' => $this->inferActionType($action),
                                'description' => "Permission détectée dans le code source ({$code})",
                                'criticality' => 'medium'
                            ];
                        }
                    }
                }
            }
        }

        return array_values($permissions);
    }

    /**
     * Déduit le type d'action à partir d'un nom de méthode ou préfixe.
     */
    private function inferActionType(string $name): string
    {
        $name = strtolower($name);
        if (str_contains($name, 'index') || str_contains($name, 'view') || str_contains($name, 'get') || str_contains($name, 'show') || str_contains($name, 'list')) {
            return 'view';
        }
        if (str_contains($name, 'create') || str_contains($name, 'add') || str_contains($name, 'store') || str_contains($name, 'save') || str_contains($name, 'wizard')) {
            return 'create';
        }
        if (str_contains($name, 'edit') || str_contains($name, 'update') || str_contains($name, 'modify') || str_contains($name, 'toggle')) {
            return 'edit';
        }
        if (str_contains($name, 'delete') || str_contains($name, 'destroy') || str_contains($name, 'remove') || str_contains($name, 'purge')) {
            return 'delete';
        }
        if (str_contains($name, 'export') || str_contains($name, 'download') || str_contains($name, 'print') || str_contains($name, 'pdf')) {
            return 'export';
        }
        if (str_contains($name, 'import') || str_contains($name, 'upload')) {
            return 'import';
        }
        return 'manage';
    }

    /**
     * Catégorise un module en grand sous-ensemble.
     */
    private function categorizeModule(string $code): string
    {
        $code = strtolower($code);
        if (in_array($code, ['user', 'setting', 'rbac', 'dashboard', 'documentation', 'impactanalysis', 'system', 'audit'])) {
            return 'system';
        }
        if (in_array($code, ['class', 'cycle', 'level', 'section', 'department', 'subject', 'subjectgroup', 'teacher', 'timetable', 'academicyear', 'sequence', 'teachingtype', 'pedagogy'])) {
            return 'pedagogy';
        }
        if (in_array($code, ['student', 'grade', 'bulletin', 'transcript', 'procesverbal', 'honorroll'])) {
            return 'students';
        }
        if (in_array($code, ['payment', 'discount', 'discounttype', 'scholarship', 'schoolfee', 'financialhistory', 'expense', 'finance'])) {
            return 'finance';
        }
        return 'general';
    }
}
