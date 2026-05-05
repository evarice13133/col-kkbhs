<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Services\BackupService;
use PDO;

class AcademicYearController
{
    private $db;
    private BackupService $backupService;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->backupService = new BackupService($this->db);
        // Seuls superadmin et admin gèrent les années
        if (!Session::isLogged() || !in_array(Session::get('user_role'), ['superadmin', 'admin'])) {
            header("Location: /");
            exit;
        }
    }

    public function index()
    {
        // Extraction de toutes les années reconnues en DB
        $stmt = $this->db->query("SELECT * FROM academic_years ORDER BY id DESC");
        $years = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $backups = $this->backupService->listArchives();
        $backupsDir = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/../../')), '/') . '/';
        foreach (glob($backupsDir . 'backup_*.zip') ?: [] as $file) {
            $backups[] = [
                'filename' => basename($file),
                'path' => $file,
                'size' => filesize($file),
                'date' => date("Y-m-d H:i:s", filemtime($file))
            ];
        }

        $backupsByName = [];
        foreach ($backups as $backup) {
            $backupsByName[$backup['filename']] = $backup;
        }
        $backups = array_values($backupsByName);
        usort($backups, static fn(array $left, array $right) => strcmp($right['date'], $left['date']));

        include __DIR__ . '/../Views/academic_years/index.php';
    }

    public function create()
    {
        include __DIR__ . '/../Views/academic_years/create.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');

            if (empty($nom)) {
                $error = \__('academic_year_name_required');
                include __DIR__ . '/../Views/academic_years/create.php';
                return;
            }

            try {
                $stmt = $this->db->prepare("INSERT INTO academic_years (nom, status) VALUES (?, 'active')");
                $stmt->execute([$nom]);
                header("Location: /academic_years");
                exit;
            } catch (\PDOException $e) {
                // Erreur de duplicata SQL STATE 23000
                $error = \__('academic_year_exists');
                include __DIR__ . '/../Views/academic_years/create.php';
            }
        }
    }

    public function activate($id)
    {
        $id = (int) $id;

        // Étape 1 : Tout désactiver logiciellement
        $this->db->query("UPDATE academic_years SET is_active = FALSE");

        // Étape 2 : Activer l'année spécifiée (seulement si non archivée globalement)
        $stmt = $this->db->prepare("UPDATE academic_years SET is_active = TRUE WHERE id = ? AND status != 'archived'");
        $stmt->execute([$id]);

        // Étape 3 : Consigner l'action dans la session de façon volatile (pour UI)
        $stmt = $this->db->prepare("SELECT nom FROM academic_years WHERE id = ?");
        $stmt->execute([$id]);
        $annee = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($annee) {
            Session::set('active_year_name', $annee['nom']);
        }

        header("Location: /academic_years");
        exit;
    }

    public function archiveWizard($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM academic_years WHERE id = ?");
        $stmt->execute([$id]);
        $year = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$year || $year['status'] === 'archived') {
            header("Location: /academic_years");
            exit;
        }

        include __DIR__ . '/../Views/academic_years/archive_wizard.php';
    }

    public function doArchive()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            exit;

        // Limitation stricte aux rôles administratifs
        if (!in_array(Session::get('user_role'), ['superadmin', 'admin'])) {
            header("Location: /academic_years");
            exit;
        }

        $id = (int) $_POST['year_id'];
        $truncate_students = isset($_POST['truncate_students']);
        $truncate_grades = isset($_POST['truncate_grades']);
        $truncate_subjects = isset($_POST['truncate_subjects']);
        $truncate_users = isset($_POST['truncate_users']);

        $stmt = $this->db->prepare("SELECT * FROM academic_years WHERE id = ?");
        $stmt->execute([$id]);
        $year = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($year) {
            $etablissement = "NotesMaster";
            $date = date('d_M_Y_His');
            $zipName = "backup_{$etablissement}_{$date}.zip";
            $sqlName = "dump_{$date}.sql";
            $rootDir = str_replace('\\', '/', realpath(__DIR__ . '/../../')) . '/';
            $sqlPath = $rootDir . $sqlName;
            $zipPath = $rootDir . $zipName;

            // 1. DUMP SQL NATIVE EN PHP (Compatibilité Universelle Windows/Linux sans PATH mysql)
            $script = "SET FOREIGN_KEY_CHECKS = 0;\n";
            $tables = ['settings', 'academic_years', 'users', 'cycles', 'sections', 'classes', 'departments', 'user_departments', 'students', 'subjects', 'subject_classes', 'teacher_assignments', 'grades'];
            foreach ($tables as $t) {
                $script .= "DROP TABLE IF EXISTS `$t`;\n";
                $row = $this->db->query("SHOW CREATE TABLE `$t`")->fetch(PDO::FETCH_NUM);
                $script .= $row[1] . ";\n";

                $rows = $this->db->query("SELECT * FROM `$t`")->fetchAll(PDO::FETCH_NUM);
                foreach ($rows as $r) {
                    $script .= "INSERT INTO `$t` VALUES(";
                    $vals = [];
                    foreach ($r as $val) {
                        if ($val === null) {
                            $vals[] = "NULL";
                        } else {
                            $val = addslashes((string) $val);
                            $val = str_replace("\n", "\\n", $val);
                            $val = str_replace("\r", "\\r", $val);
                            $vals[] = "'" . $val . "'";
                        }
                    }
                    $script .= implode(',', $vals) . ");\n";
                }
                $script .= "\n";
            }
            $script .= "SET FOREIGN_KEY_CHECKS = 1;\n";
            file_put_contents($sqlPath, $script);

            // 2. CRÉATION DU ZIP
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
                $zip->addFile($sqlPath, $sqlName);
                $zip->close();
            }
            unlink($sqlPath); // Nettoyage du .sql

            // 3. SYNCHRONISATION GITHUB (Nécessite environnement configuré localement avec clef SSH ou PAT)
            $cmd = "cd " . escapeshellarg(rtrim($rootDir, '/')) . " && git add *.zip && git commit -m \"Auto Backup $zipName\" && git push";
            if (function_exists('shell_exec')) {
                @shell_exec($cmd);
            }

            // 4. NETTOYAGE (TRUNCATE) DES BDD SELON LE CIBLAGE DE L'ADMIN
            $this->db->query("SET FOREIGN_KEY_CHECKS = 0");
            if ($truncate_grades)
                $this->db->query("TRUNCATE TABLE grades");
            if ($truncate_students)
                $this->db->query("TRUNCATE TABLE students");
            if ($truncate_subjects)
                $this->db->query("TRUNCATE TABLE subjects");
            if ($truncate_users) {
                $c_id = (int) Session::get('user_id');
                $this->db->query("DELETE FROM users WHERE id != $c_id");
            }
            $this->db->query("SET FOREIGN_KEY_CHECKS = 1");

            // 5. CLÔTURE DE L'ANNÉE
            $up = $this->db->prepare("UPDATE academic_years SET status = 'archived', is_active = FALSE WHERE id = ?");
            $up->execute([$id]);

            if (Session::get('active_year_name') === $year['nom']) {
                Session::remove('active_year_name');
            }
        }

        header("Location: /academic_years");
        exit;
    }

    public function restore($file = '')
    {
        // Strictement limité au SuperAdmin
        if (Session::get('user_role') !== 'superadmin') {
            header("Location: /academic_years");
            exit;
        }

        $file = $_GET['file'] ?? '';
        $rootDir = str_replace('\\', '/', realpath(__DIR__ . '/../../')) . '/';
        $zipPath = $this->backupService->resolveArchivePath($file) ?? ($rootDir . basename($file));

        if (file_exists($zipPath)) {
            $zip = new \ZipArchive();
            if ($zip->open($zipPath) === TRUE) {
                $sqlName = null;
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $name = $zip->getNameIndex($i);
                    if (is_string($name) && str_ends_with($name, '.sql')) {
                        $sqlName = $name;
                        break;
                    }
                }

                if ($sqlName === null) {
                    $zip->close();
                    header("Location: /academic_years");
                    exit;
                }

                $zip->extractTo($rootDir, $sqlName);
                $zip->close();

                $sqlPath = $rootDir . $sqlName;
                $sqlContent = file_get_contents($sqlPath);

                // Exécution massive SQL
                $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
                try {
                    $this->db->exec($sqlContent);
                } catch (\Exception $e) {
                }
                $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                unlink($sqlPath);

                // Destruction cache pour reconnection sur BDD neuve
                Session::destroy();
                header("Location: /login");
                exit;
            }
        }
        header("Location: /academic_years");
        exit;
    }

    public function unarchive($id)
    {
        // Strictement limité au SuperAdmin
        if (Session::get('user_role') !== 'superadmin') {
            header("Location: /academic_years");
            exit;
        }

        $id = (int) $id;
        $stmt = $this->db->prepare("SELECT * FROM academic_years WHERE id = ?");
        $stmt->execute([$id]);
        $year = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$year || $year['status'] !== 'archived') {
            header("Location: /academic_years");
            exit;
        }

        include __DIR__ . '/../Views/academic_years/unarchive.php';
    }

    public function doUnarchive()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            exit;

        // Strictement limité au SuperAdmin
        if (Session::get('user_role') !== 'superadmin') {
            header("Location: /academic_years");
            exit;
        }

        $id = (int) $_POST['year_id'];
        $set_active = isset($_POST['set_active']);

        // 1. Restaurer le statut
        $stmt = $this->db->prepare("UPDATE academic_years SET status = 'active' WHERE id = ?");
        $stmt->execute([$id]);

        // 2. Définir comme année courante si demandé
        if ($set_active) {
            $this->activate($id);
        }

        header("Location: /academic_years");
        exit;
    }
}
