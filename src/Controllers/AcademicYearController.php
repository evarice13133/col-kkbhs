<?php



namespace App\Controllers;



use App\Core\Database;

use App\Core\Session;

use App\Core\PermissionManager;

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

        // Sécurité RBAC : Accès réservé aux administrateurs
        PermissionManager::requirePermission('manage_academic_years');

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
            $start_date = trim($_POST['start_date'] ?? '');
            $end_date = trim($_POST['end_date'] ?? '');



            if (empty($nom)) {

                $error = \__('academic_year_name_required');

                include __DIR__ . '/../Views/academic_years/create.php';

                return;

            }



            try {

                $stmt = $this->db->prepare("INSERT INTO academic_years (nom, start_date, end_date, status) VALUES (?, ?, ?, 'active')");

                $stmt->execute([$nom, $start_date ?: null, $end_date ?: null]);

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



    public function rolloverWizard()

    {

        // Get current active year

        $stmt = $this->db->query("SELECT * FROM academic_years WHERE is_active = 1 LIMIT 1");

        $currentYear = $stmt->fetch(PDO::FETCH_ASSOC);



        if (!$currentYear) {

            Session::setFlash('error', __('no_active_year_for_rollover'));

            header("Location: /academic_years");

            exit;

        }



        include __DIR__ . '/../Views/academic_years/rollover_wizard.php';

    }



    public function doRollover()

    {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            exit;

        }



        if (!in_array(Session::get('user_role'), ['superadmin', 'admin'])) {

            header("Location: /academic_years");

            exit;

        }



        $currentYearId = (int) $_POST['current_year_id'];

        $newYearNom = trim($_POST['new_year_nom'] ?? '');

        $cloneClasses = isset($_POST['clone_classes']);

        $cloneSubjects = isset($_POST['clone_subjects']);

        $cloneAssignments = isset($_POST['clone_assignments']);

        $archiveCurrent = isset($_POST['archive_current']);



        if (empty($newYearNom)) {

            Session::setFlash('error', __('new_year_name_required'));

            header("Location: /academic_years/rollover");

            exit;

        }



        try {

            // Augmenter le temps d'exécution pour les opérations de clonage de données
            set_time_limit(300); // 5 minutes

            $this->db->beginTransaction();



            // 1. Create new academic year

            $stmt = $this->db->prepare("INSERT INTO academic_years (nom, start_date, end_date, status) VALUES (?, ?, ?, 'active')");

            $stmt->execute([$newYearNom, null, null]);

            $newYearId = (int) $this->db->lastInsertId();



            // 2. Clone structural data if requested
            // Classes are now shared across years, no need to clone them
            // if ($cloneClasses) {
            //     $stmt = $this->db->prepare("
            //         INSERT INTO classes (nom, cycle_id, section_id, department_id, main_teacher_id, academic_year_id)
            //         SELECT nom, cycle_id, section_id, department_id, main_teacher_id, ?
            //         FROM classes WHERE academic_year_id = ?
            //     ");
            //     $stmt->execute([$newYearId, $currentYearId]);
            // }



            if ($cloneSubjects) {

                // Classes are now shared across years, so we can directly use the class_id
                $stmt = $this->db->prepare("

                    INSERT INTO subject_classes (subject_id, class_id, academic_year_id)

                    SELECT subject_id, class_id, ?

                    FROM subject_classes

                    WHERE academic_year_id = ?

                ");

                $stmt->execute([$newYearId, $currentYearId]);

            }



            if ($cloneAssignments) {

                // Classes are now shared across years, so we can directly use the class_id
                $stmt = $this->db->prepare("

                    INSERT INTO teacher_assignments (user_id, subject_id, class_id, academic_year_id)

                    SELECT user_id, subject_id, class_id, ?

                    FROM teacher_assignments

                    WHERE academic_year_id = ?

                ");

                $stmt->execute([$newYearId, $currentYearId]);

            }



            // 3. Activate new year

            $this->db->query("UPDATE academic_years SET is_active = FALSE");

            $stmt = $this->db->prepare("UPDATE academic_years SET is_active = TRUE WHERE id = ?");

            $stmt->execute([$newYearId]);



            // 4. Archive current year if requested

            if ($archiveCurrent) {

                $stmt = $this->db->prepare("UPDATE academic_years SET status = 'archived' WHERE id = ?");

                $stmt->execute([$currentYearId]);

            }



            $this->db->commit();



            Session::setFlash('success', __('rollover_success', ['new_year' => $newYearNom]));

            header("Location: /academic_years");

            exit;



        } catch (\Exception $e) {

            if ($this->db->inTransaction()) {

                $this->db->rollBack();

            }



            Session::setFlash('error', __('rollover_error') . ': ' . $e->getMessage());

            header("Location: /academic_years/rollover");

            exit;

        }

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

            // Augmenter le temps d'exécution pour les opérations de sauvegarde
            set_time_limit(300); // 5 minutes

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



            // 4. NETTOYAGE SÉCURISÉ : DELETE WHERE academic_year_id = ? (JAMAIS TRUNCATE)
            // ⚠️ CRITIQUE : Utiliser DELETE avec filtre, pas TRUNCATE qui efface tout

            $this->db->query("SET FOREIGN_KEY_CHECKS = 0");

            try {
                if ($truncate_grades) {
                    $stmt = $this->db->prepare("DELETE FROM grades WHERE academic_year_id = ?");
                    $stmt->execute([$id]);
                    $this->logAudit('ARCHIVE_CLEANUP', "Grades deleted for year $id");
                }

                if ($truncate_students) {
                    $stmt = $this->db->prepare("DELETE FROM students WHERE academic_year_id = ?");
                    $stmt->execute([$id]);
                    $this->logAudit('ARCHIVE_CLEANUP', "Students deleted for year $id");
                }

                if ($truncate_subjects) {
                    $stmt = $this->db->prepare("DELETE FROM subjects WHERE academic_year_id = ?");
                    $stmt->execute([$id]);
                    $this->logAudit('ARCHIVE_CLEANUP', "Subjects deleted for year $id");
                }

                if ($truncate_users) {
                    // NE DELETE QUE LES ENSEIGNANTS (pas les admins actuels)
                    $c_id = (int) Session::get('user_id');
                    $stmt = $this->db->prepare(
                        "DELETE FROM users WHERE role = 'enseignant' AND id != ? AND id NOT IN (SELECT id FROM users WHERE role IN ('admin','superadmin'))"
                    );
                    $stmt->execute([$c_id]);
                    $this->logAudit('ARCHIVE_CLEANUP', "Teachers deleted, admin kept");
                }

            } catch (\Exception $e) {
                $this->db->query("SET FOREIGN_KEY_CHECKS = 1");
                $this->logAudit('ARCHIVE_ERROR', 'Cleanup failed: ' . $e->getMessage());
                header("Location: /academic_years?error=cleanup_failed");
                exit;
            }

            $this->db->query("SET FOREIGN_KEY_CHECKS = 1");



            // 5. CLÔTURE DE L'ANNÉE

            $up = $this->db->prepare("UPDATE academic_years SET status = 'archived', is_active = FALSE WHERE id = ?");

            $up->execute([$id]);
            $this->logAudit('ARCHIVE_SUCCESS', "Year $id archived");



            if (Session::get('active_year_name') === $year['nom']) {

                Session::remove('active_year_name');

            }

        }



        header("Location: /academic_years?success=archived");

        exit;

    }



    /**
     * Restaurer une année archivée (SÉCURISÉ pour ne pas écraser les données actuelles).
     * ⚠️ CRITIQUE : Vérifier que l'année restaurée n'existe pas avant de restaurer.
     */
    public function restore($file = '')

    {

        // Strictement limité au SuperAdmin

        if (Session::get('user_role') !== 'superadmin') {
            $this->logAudit('RESTORE_FAILED', 'Unauthorized role');

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



                // ⚠️ CRITIQUE : Vérifier AVANT restauration que l'année n'existe pas
                // Extraire les années du dump SQL (simple parsing)
                $existingYears = $this->db->query("SELECT id FROM academic_years")->fetchAll(PDO::FETCH_COLUMN);

                $hasConflict = false;



                // Simple check : si le dump contient des INSERT INTO academic_years avec des id existants

                foreach ($existingYears as $yearId) {

                    if (strpos($sqlContent, "INSERT INTO `academic_years` VALUES($yearId") !== false) {

                        $hasConflict = true;

                        break;

                    }

                }



                if ($hasConflict) {

                    unlink($sqlPath);
                    $this->logAudit('RESTORE_FAILED', 'Year already exists - data protection');

                    header("Location: /academic_years?error=year_conflict");

                    exit;

                }



                // ✅ SÉCURISÉ : Exécution du SQL après validation

                $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

                // Augmenter le temps d'exécution pour les grandes bases de données
                set_time_limit(300); // 5 minutes

                try {

                    $this->db->exec($sqlContent);
                    $this->logAudit('RESTORE_SUCCESS', 'Database restored from backup');

                } catch (\Exception $e) {
                    $this->logAudit('RESTORE_ERROR', 'SQL execution failed: ' . $e->getMessage());

                    header("Location: /academic_years?error=restore_failed");

                    exit;

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



    public function edit($id)

    {

        // Autorisé pour admin et superadmin

        if (!in_array(Session::get('user_role'), ['superadmin', 'admin'])) {

            header("Location: /academic_years");

            exit;

        }



        $id = (int) $id;

        $stmt = $this->db->prepare("SELECT * FROM academic_years WHERE id = ?");

        $stmt->execute([$id]);

        $year = $stmt->fetch(PDO::FETCH_ASSOC);



        if (!$year) {

            header("Location: /academic_years");

            exit;

        }



        include __DIR__ . '/../Views/academic_years/edit.php';

    }



    public function update($id)

    {

        // Autorisé pour admin et superadmin

        if (!in_array(Session::get('user_role'), ['superadmin', 'admin'])) {

            header("Location: /academic_years");

            exit;

        }



        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $id = (int) $id;

            $nom = trim($_POST['nom'] ?? '');



            if (empty($nom)) {

                $error = \__('academic_year_name_required');

                $stmt = $this->db->prepare("SELECT * FROM academic_years WHERE id = ?");

                $stmt->execute([$id]);

                $year = $stmt->fetch(PDO::FETCH_ASSOC);

                include __DIR__ . '/../Views/academic_years/edit.php';

                return;

            }



            try {

                $stmt = $this->db->prepare("UPDATE academic_years SET nom = ? WHERE id = ?");

                $stmt->execute([$nom, $id]);

                Session::setFlash('success', __('academic_year_updated_success'));

                header("Location: /academic_years");

                exit;

            } catch (\PDOException $e) {

                // Erreur de duplicata SQL STATE 23000

                $error = \__('academic_year_exists');

                $stmt = $this->db->prepare("SELECT * FROM academic_years WHERE id = ?");

                $stmt->execute([$id]);

                $year = $stmt->fetch(PDO::FETCH_ASSOC);

                include __DIR__ . '/../Views/academic_years/edit.php';

            }

        }

    }



    public function delete($id)

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



        if (!$year) {

            header("Location: /academic_years");

            exit;

        }



        // Vérifier si l'année est active

        if ($year['is_active']) {

            Session::setFlash('error', __('cannot_delete_active_year'));

            header("Location: /academic_years");

            exit;

        }



        // Vérifier si l'année a des données associées
        // Certaines installations n'ont pas la colonne `academic_year_id` (ex: renommée en `year_id`).
        // On détecte donc dynamiquement la colonne existante pour éviter le crash.

        $studentCount = 0;
        $gradeCount = 0;

        $colStudents = $this->resolveAcademicYearForeignKeyColumn('students');
        if ($colStudents !== null) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM students WHERE {$colStudents} = ?");
            $stmt->execute([$id]);
            $studentCount = (int)$stmt->fetchColumn();
        }

        $colGrades = $this->resolveAcademicYearForeignKeyColumn('grades');
        if ($colGrades !== null) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM grades WHERE {$colGrades} = ?");
            $stmt->execute([$id]);
            $gradeCount = (int)$stmt->fetchColumn();
        }




        if ($studentCount > 0 || $gradeCount > 0) {

            Session::setFlash('error', __('cannot_delete_year_with_data'));

            header("Location: /academic_years");

            exit;

        }



        // Supprimer l'année

        $stmt = $this->db->prepare("DELETE FROM academic_years WHERE id = ?");

        $stmt->execute([$id]);



        Session::setFlash('success', __('academic_year_deleted_success'));

        header("Location: /academic_years");

        exit;

    }

    private function resolveAcademicYearForeignKeyColumn(string $table): ?string
    {
        $candidates = ['academic_year_id', 'year_id'];

        try {
            $cols = $this->db->query("SHOW COLUMNS FROM {$table}")->fetchAll(PDO::FETCH_COLUMN);
            $set = array_flip($cols);

            foreach ($candidates as $c) {
                if (isset($set[$c])) {
                    return $c;
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return in_array('academic_year_id', $candidates, true) ? 'academic_year_id' : null;
    }

    /**
     * Enregistrer une opération d'audit critique dans les logs.
     * Utile pour tracer les opérations sur les années académiques.
     * 
     * @param string $action Type d'action (ARCHIVE_SUCCESS, ARCHIVE_ERROR, RESTORE_SUCCESS, etc.)
     * @param string $detail Détails de l'opération
     */
    private function logAudit($action, $detail)

    {
        try {
            $userId = Session::get('user_id') ?? 'unknown';
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $timestamp = date('Y-m-d H:i:s');
            $msg = "[$timestamp] [$action] User#$userId from $ip - $detail";
            
            // Log dans un fichier d'audit
            $logsDir = __DIR__ . '/../../logs';
            if (!is_dir($logsDir)) {
                mkdir($logsDir, 0755, true);
            }
            
            error_log($msg . "\n", 3, $logsDir . '/academic_years_audit.log');
        } catch (\Exception $e) {
            // Ne pas bloquer si le log échoue
            error_log("Audit logging failed: " . $e->getMessage());
        }
    }

}

