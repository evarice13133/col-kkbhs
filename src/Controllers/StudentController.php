<?php



namespace App\Controllers;



use App\Core\Database;

use App\Core\Session;

use App\Services\Import\ExcelTemplateService;

use App\Services\Import\StudentImportProcessor;

use PDO;



class StudentController

{

    private $db;

    private \App\Services\MatriculeService $matriculeService;

    private const PER_PAGE = 16;



    public function __construct()

    {

        $this->db = Database::getInstance()->getConnection();

        $this->matriculeService = new \App\Services\MatriculeService($this->db);

        if (!Session::isLogged()) {

            header("Location: /login");

            exit;

        }

        $this->ensureStudentProfileSchema();

    }



    public function index()

    {

        $page = max(1, (int) ($_GET['page'] ?? 1));

        $limit = self::PER_PAGE;

        $offset = ($page - 1) * $limit;



        [$students, $filters, $totalCount] = $this->fetchStudentsFromFilters($limit, $offset);

        

        $totalPages = (int) ceil($totalCount / $limit);



        if ($page > $totalPages && $totalCount > 0) {

            header("Location: /students?page=1");

            exit;

        }



        // Progression Sécurité : Les enseignants ne voient que les classes où ils interviennent

        if (Session::get('user_role') === 'enseignant') {

            $stmt = $this->db->prepare("SELECT id, nom FROM classes WHERE id IN (SELECT DISTINCT class_id FROM teacher_assignments WHERE user_id = ?) ORDER BY nom ASC");

            $stmt->execute([Session::get('user_id')]);

            $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } else {

            $classes = $this->db->query("SELECT id, nom FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        }



        $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);



        include __DIR__ . '/../Views/students/index.php';

    }



    public function export()

    {

        // Pas de pagination pour l'export

        [$students, $filters] = $this->fetchStudentsFromFilters();



        $settingsStore = new \App\Services\SettingsStore($this->db);

        $logoManager   = \App\Core\LogoManager::getInstance($this->db);



        $school_name = $settingsStore->get('school_name', 'NotesMaster');

        $logo_base64 = $logoManager->hasLogo() ? $logoManager->getLogoBase64() : '';



        // Année académique active

        $ayRow = $this->db->query("SELECT nom FROM academic_years WHERE is_active = 1 LIMIT 1")->fetch(\PDO::FETCH_ASSOC);

        $academic_year_nom = $ayRow['nom'] ?? date('Y');



        // Contexte des filtres actifs pour le sous-titre

        $filter_class   = '';

        $filter_section = '';

        if (!empty($filters['class_id'])) {

            $stmt = $this->db->prepare("SELECT nom FROM classes WHERE id = ?");

            $stmt->execute([$filters['class_id']]);

            $filter_class = (string) ($stmt->fetchColumn() ?: '');

        }

        if (!empty($filters['section_id'])) {

            $stmt = $this->db->prepare("SELECT nom FROM sections WHERE id = ?");

            $stmt->execute([$filters['section_id']]);

            $filter_section = (string) ($stmt->fetchColumn() ?: '');

        }



        $isWithdrawn = (int) ($filters['withdrawn'] ?? 0);

        $title = $isWithdrawn ? __('withdrawn_students_register') : __('student_register');



        ob_start();

        include __DIR__ . '/../Views/students/templates/export_pdf_students.php';

        $html = ob_get_clean();



        $filename = ($isWithdrawn ? 'Liste_Retires_' : 'Registre_Eleves_') . date('Y-m-d') . '.pdf';

        $this->streamPdf($html, $filename);

    }



    protected function streamPdf(string $html, string $filename): void

    {

        while (ob_get_level()) {

            ob_end_clean();

        }



        $options = new \Dompdf\Options();

        $options->set('isHtml5ParserEnabled', true);

        $options->set('isRemoteEnabled', true);

        $options->set('defaultFont', 'Helvetica');



        $dompdf = new \Dompdf\Dompdf($options);

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'portrait');



        try {

            $dompdf->render();

            $dompdf->stream($filename, ['Attachment' => true]);

        } catch (\Throwable $e) {

            echo 'Erreur lors de la génération du PDF : ' . $e->getMessage();

        }

        exit;

    }



    public function create()

    {

        if (!in_array(Session::get('user_role'), ['superadmin', 'admin'])) {

            header("Location: /students");

            exit;

        }

        $classes = $this->db->query("SELECT id, nom, cycle_id, section_id, department_id FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        $departments = $this->db->query("SELECT id, nom FROM departments WHERE status = 1 ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        $formData = ['is_redoublant' => '0', 'sexe' => ''];

        include __DIR__ . '/../Views/students/create.php';

    }



    /**

     * Affiche l'interface du module d'importation (Étape 1).

     * 

     * Cette interface guide l'utilisateur à travers les 3 étapes clés :

     * 1. Téléchargement du modèle structuré

     * 2. Remplissage des données avec assistance Excel

     * 3. Chargement et validation finale

     * 

     * @return void

     */

    public function import()

    {

        if (!in_array(Session::get('user_role'), ['superadmin', 'admin'])) {

            header("Location: /students");

            exit;

        }

        // On récupère les classes pour l'affichage éventuel, bien que le template 

        // Excel contienne déjà ses propres menus déroulants dynamiques.

        $classes = $this->db->query("SELECT id, nom FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/students/import.php';

    }



    /**

     * Génère et télécharge le modèle Excel (.xlsx) intelligent.

     * 

     * Cette version est sécurisée contre les interférences de tampons (buffers) 

     * et gère les erreurs potentielles de la bibliothèque PHPSpreadsheet.

     */

    public function downloadTemplate()

    {

        // On s'assure d'avoir un environnement propre pour le binaire

        // (Vider tout tampon de sortie pré-existant)

        while (ob_get_level())

            ob_end_clean();



        // On augmente temporairement la limite mémoire car PHPSpreadsheet est gourmand

        ini_set('memory_limit', '512M');



        $lang = Session::get('lang', 'fr');



        try {

            // Initialisation du service indépendant

            $templateService = new ExcelTemplateService($this->db);



            // Génération du flux binaire

            $content = $templateService->generateStudentTemplate($lang);



            if (empty($content)) {

                throw new \Exception("Le flux généré est vide.");

            }



            $filename = "Modele_Import_Eleves_" . strtoupper($lang) . ".xlsx";



            // Envoi des en-têtes officiels Microsoft Excel

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

            header('Content-Disposition: attachment;filename="' . $filename . '"');

            header('Cache-Control: max-age=0');

            header('Pragma: public');

            header('Content-Length: ' . strlen($content));



            echo $content;

            exit;

        } catch (\Throwable $e) {

            // En cas d'erreur fatale (ex: extension PHP manquante), on informe l'utilisateur

            Session::setFlash('error', __('error_generation') . " : " . $e->getMessage());

            header("Location: /students/import");

            exit;

        }

    }



    /**

     * Traite le chargement du fichier Excel (.xlsx) rempli par l'utilisateur.

     * 

     * Cette méthode orchestre la validation stricte via le StudentImportProcessor.

     * Si des erreurs sont trouvées, un rapport détaillé est affiché.

     * En cas de succès, les élèves sont insérés de manière atomique (transaction).

     * 

     * @return void

     */

    public function upload()

    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file'])) {

            if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {

                \App\Core\Security::log("Tentative de CSRF détectée sur l'action Student::upload");

                Session::setFlash('error', __('session_expired_error'));

                header("Location: /students/import");

                exit;

            }

            $file = $_FILES['import_file'];

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));



            // Format exclusif .xlsx requis pour bénéficier des validations Excel

            if ($ext !== 'xlsx') {

                Session::setFlash('error', __('invalid_file_format_excel'));

                header("Location: /students/import");

                exit;

            }



            // Utilisation du service de traitement indépendant

            $processor = new StudentImportProcessor($this->db);

            $lang = Session::get('lang', 'fr');



            $result = $processor->process($file['tmp_name'], $lang);



            if ($result['success']) {

                Session::setFlash('success', __('import_success_count', ['count' => $result['count']]));

                header("Location: /students");

                exit;

            } else {

                // En cas d'erreurs (validation de données ou relations), on affiche le rapport

                $errors = $result['errors'];

                // Définit une flash contenant la liste JSON des erreurs pour affichage en modal

                \App\Core\Session::setFlash('popup_errors', json_encode($errors, JSON_UNESCAPED_UNICODE));

                $classes = $this->db->query("SELECT id, nom FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

                include __DIR__ . '/../Views/students/import.php';

            }

        }

    }



    public function store()

    {

        if (!in_array(Session::get('user_role'), ['superadmin', 'admin'])) {

            header("Location: /students");

            exit;

        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {

                \App\Core\Security::log("Tentative de CSRF détectée sur l'action Student::store");

                Session::setFlash('error', __('session_expired_error'));

                header("Location: /students/create");

                exit;

            }

            $this->ensureStudentProfileSchema();

            $nom = $this->normalizeStudentLastName($_POST['nom'] ?? '');

            $prenom = trim($_POST['prenom'] ?? '');

            $email = trim($_POST['email'] ?? '');

            $class_id = !empty($_POST['class_id']) ? (int) $_POST['class_id'] : null;

            $cycle_id = !empty($_POST['cycle_id']) ? (int) $_POST['cycle_id'] : null;

            $section_id = !empty($_POST['section_id']) ? (int) $_POST['section_id'] : null;

            $department_id = !empty($_POST['department_id']) ? (int) $_POST['department_id'] : null;

            $sexe = $this->normalizeSexe($_POST['sexe'] ?? '');

            $date_naissance = $this->normalizeOptionalDate($_POST['date_naissance'] ?? null);

            $lieu_naissance = $this->normalizeOptionalText($_POST['lieu_naissance'] ?? '');

            $is_redoublant = $this->normalizeRedoublantFlag($_POST['is_redoublant'] ?? 0);



            if (empty($nom) || empty($prenom)) {

                $error = \__('student_name_required');

                $classes = $this->db->query("SELECT id, nom, cycle_id, section_id, department_id FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

                $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

                $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

                $departments = $this->db->query("SELECT id, nom FROM departments WHERE status = 1 ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

                $formData = [

                    'nom' => $nom,

                    'prenom' => $prenom,

                    'email' => $email,

                    'class_id' => $class_id,

                    'cycle_id' => $cycle_id,

                    'section_id' => $section_id,

                    'department_id' => $department_id,

                    'sexe' => $sexe,

                    'date_naissance' => $date_naissance,

                    'lieu_naissance' => $lieu_naissance,

                    'is_redoublant' => (string) $is_redoublant,

                ];

                include __DIR__ . '/../Views/students/create.php';

                return;

            }



            // Si aucun matricule n'est fourni, on le genere via le service centralise.

            if ($email === '') {

                $email = $this->matriculeService->generate($class_id);

            }



            // Vérifier l'unicité du matricule fourni ou généré

            $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM students WHERE email = ?");

            $checkStmt->execute([$email]);

            if ((int) $checkStmt->fetchColumn() > 0) {

                $error = __('matricule_already_exists') ?? 'Matricule déjà utilisé.';

                \App\Core\Session::setFlash('popup_error', $error);

                header("Location: /students/create");

                exit;

            }



            // Enregistrement via le modèle normalisé (seul class_id est requis pour le lien)

            $stmt = $this->db->prepare("INSERT INTO students (nom, prenom, email, class_id, sexe, date_naissance, lieu_naissance, is_redoublant) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([$nom, $prenom, $email, $class_id, $sexe, $date_naissance, $lieu_naissance, $is_redoublant]);



            Session::setFlash('success', __('student_created_success'));

            header("Location: /students");

            exit;

        }

    }



    public function edit($id)

    {

        if (!in_array(Session::get('user_role'), ['superadmin', 'admin'])) {

            header("Location: /students");

            exit;

        }

        $stmt = $this->db->prepare("SELECT s.*, c.cycle_id, c.section_id, c.department_id FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE s.id = ?");

        $stmt->execute([$id]);

        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {

            header("Location: /students");

            exit;

        }



        $classes = $this->db->query("SELECT id, nom, cycle_id, section_id, department_id FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        $departments = $this->db->query("SELECT id, nom FROM departments WHERE status = 1 ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/students/edit.php';

    }



    public function update($id)

    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {

                \App\Core\Security::log("Tentative de CSRF détectée sur l'action Student::update (ID: $id)");

                Session::setFlash('error', __('session_expired_error'));

                header("Location: /students/edit?id=" . $id);

                exit;

            }

            $this->ensureStudentProfileSchema();

            $nom = $this->normalizeStudentLastName($_POST['nom'] ?? '');

            $prenom = trim($_POST['prenom'] ?? '');

            $email = trim($_POST['email'] ?? '');

            $class_id = !empty($_POST['class_id']) ? (int) $_POST['class_id'] : null;

            $cycle_id = !empty($_POST['cycle_id']) ? (int) $_POST['cycle_id'] : null;

            $section_id = !empty($_POST['section_id']) ? (int) $_POST['section_id'] : null;

            $department_id = !empty($_POST['department_id']) ? (int) $_POST['department_id'] : null;

            $sexe = $this->normalizeSexe($_POST['sexe'] ?? '');

            $date_naissance = $this->normalizeOptionalDate($_POST['date_naissance'] ?? null);

            $lieu_naissance = $this->normalizeOptionalText($_POST['lieu_naissance'] ?? '');

            $is_redoublant = $this->normalizeRedoublantFlag($_POST['is_redoublant'] ?? 0);



            // Récupérer l'email actuel pour déterminer si le matricule change

            $stmt = $this->db->prepare("SELECT email FROM students WHERE id = ?");

            $stmt->execute([$id]);

            $currentRow = $stmt->fetch(PDO::FETCH_ASSOC);

            $currentEmail = $currentRow['email'] ?? null;



            $newEmail = trim($_POST['email'] ?? '');

            $allowEmailChange = in_array(Session::get('user_role'), ['superadmin', 'admin']);



            if ($allowEmailChange && $newEmail !== '' && $newEmail !== $currentEmail) {

                $check = $this->db->prepare("SELECT COUNT(*) FROM students WHERE email = ? AND id != ?");

                $check->execute([$newEmail, $id]);

                if ((int) $check->fetchColumn() > 0) {

                    $error = __('matricule_already_exists') ?? "Matricule déjà utilisé.";

                    \App\Core\Session::setFlash('popup_error', $error);

                    header("Location: /students/edit?id=" . $id);

                    exit;

                }

            }



            if (empty($nom) || empty($prenom)) {

                $error = \__('student_name_required');

                $student = [

                    'id' => $id,

                    'nom' => $nom,

                    'prenom' => $prenom,

                    'email' => $email,

                    'class_id' => $class_id,

                    'cycle_id' => $cycle_id,

                    'section_id' => $section_id,

                    'department_id' => $department_id,

                    'sexe' => $sexe,

                    'date_naissance' => $date_naissance,

                    'lieu_naissance' => $lieu_naissance,

                    'is_redoublant' => $is_redoublant,

                ];

                $classes = $this->db->query("SELECT id, nom, cycle_id, section_id, department_id FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

                $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

                $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

                $departments = $this->db->query("SELECT id, nom FROM departments WHERE status = 1 ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

                include __DIR__ . '/../Views/students/edit.php';

                return;

            }



            // Préparer la mise à jour. Autoriser la modification du matricule pour admin/superadmin

            $updateParts = ['nom = ?', 'prenom = ?', 'class_id = ?', 'sexe = ?', 'date_naissance = ?', 'lieu_naissance = ?', 'is_redoublant = ?'];

            $params = [$nom, $prenom, $class_id, $sexe, $date_naissance, $lieu_naissance, $is_redoublant];



            if ($allowEmailChange && $newEmail !== '' && $newEmail !== $currentEmail) {

                array_unshift($params, $newEmail);

                array_unshift($updateParts, 'email = ?');

            }



            $sql = "UPDATE students SET " . implode(', ', $updateParts) . " WHERE id = ?";

            $params[] = $id;

            $stmt = $this->db->prepare($sql);

            $stmt->execute($params);



            Session::setFlash('success', __('student_updated_success'));

            header("Location: /students");

            exit;

        }

    }



    public function withdraw($id)

    {

        if (!in_array(Session::get('user_role'), ['superadmin', 'admin'])) {

            header("Location: /students");

            exit;

        }

        if (!Session::verifyCsrfToken($_GET['csrf_token'] ?? '')) {

            Session::setFlash('error', __('unauthorized_action'));

            header("Location: /students");

            exit;

        }

        $stmt = $this->db->prepare("UPDATE students SET is_withdrawn = 1 WHERE id = ?");

        $stmt->execute([$id]);

        Session::setFlash('success', __('student_withdrawn_success'));

        header("Location: /students");

        exit;

    }



    public function restore($id)

    {

        if (!in_array(Session::get('user_role'), ['superadmin', 'admin'])) {

            header("Location: /students");

            exit;

        }

        if (!Session::verifyCsrfToken($_GET['csrf_token'] ?? '')) {

            Session::setFlash('error', __('unauthorized_action'));

            header("Location: /students");

            exit;

        }

        $stmt = $this->db->prepare("UPDATE students SET is_withdrawn = 0 WHERE id = ?");

        $stmt->execute([$id]);

        Session::setFlash('success', __('student_restored_success'));

        header("Location: /students?withdrawn=1");

        exit;

    }



    public function delete($id)

    {

        if (!in_array(Session::get('user_role'), ['superadmin', 'admin'])) {

            header("Location: /students");

            exit;

        }

        if (!Session::verifyCsrfToken($_GET['csrf_token'] ?? '')) {

            \App\Core\Security::log("Tentative de CSRF détectée sur l'action Student::delete (ID: $id)");

            Session::setFlash('error', __('unauthorized_action'));

            header("Location: /students");

            exit;

        }

        $stmt = $this->db->prepare("DELETE FROM students WHERE id = ?");

        $stmt->execute([$id]);

        Session::setFlash('success', __('student_deleted_success'));

        header("Location: /students");

        exit;

    }



    private function fetchStudentsFromFilters(?int $limit = null, ?int $offset = null)

    {

        $search = trim($_GET['q'] ?? '');

        $classId = (int) ($_GET['class_id'] ?? 0);

        $sectionId = (int) ($_GET['section_id'] ?? 0);

        $showWithdrawn = (int) ($_GET['withdrawn'] ?? 0);



        // --- 1. Construction des conditions ---

        $where = " WHERE s.is_withdrawn = ?";

        $params = [$showWithdrawn];



        if (Session::get('user_role') === 'enseignant') {

            $where .= " AND s.class_id IN (SELECT DISTINCT class_id FROM teacher_assignments WHERE user_id = ?)";

            $params[] = Session::get('user_id');

        }



        if ($search !== '') {

            $like = '%' . $search . '%';

            $where .= " AND (s.nom LIKE ? OR s.prenom LIKE ? OR s.email LIKE ? OR d.nom LIKE ? OR d.code LIKE ?)";

            $params[] = $like;

            $params[] = $like;

            $params[] = $like;

            $params[] = $like;

            $params[] = $like;

        }



        if ($classId > 0) {

            $where .= " AND s.class_id = ?";

            $params[] = $classId;

        }



        if ($sectionId > 0) {

            $where .= " AND c.section_id = ?";

            $params[] = $sectionId;

        }



        // --- 2. Calcul du total (sans pagination) ---

        $countSql = "SELECT COUNT(*) FROM students s 

                     LEFT JOIN classes c ON s.class_id = c.id 

                     LEFT JOIN departments d ON c.department_id = d.id" . $where;

        $countStmt = $this->db->prepare($countSql);

        $countStmt->execute($params);

        $totalCount = (int) $countStmt->fetchColumn();



        // --- 3. Récupération des données avec pagination si demandée ---

        $sql = "SELECT s.*, c.nom as classe_nom, cy.nom as cycle_nom, sec.nom as section_nom, d.nom as department_nom

                FROM students s

                LEFT JOIN classes c ON s.class_id = c.id

                LEFT JOIN cycles cy ON c.cycle_id = cy.id

                LEFT JOIN sections sec ON c.section_id = sec.id

                LEFT JOIN departments d ON c.department_id = d.id" . $where;



        $sql .= " ORDER BY s.nom ASC, s.prenom ASC";



        if ($limit !== null) {

            $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;

        }



        $stmt = $this->db->prepare($sql);

        $stmt->execute($params);



        return [

            $stmt->fetchAll(PDO::FETCH_ASSOC), 

            ['q' => $search, 'class_id' => $classId, 'section_id' => $sectionId, 'withdrawn' => $showWithdrawn],

            $totalCount

        ];

    }





    private function normalizeStudentLastName(string $value): string

    {

        // Le nom de famille est toujours stocke en majuscules pour homogeniser l'affichage.

        $value = trim($value);

        return function_exists('mb_strtoupper') ? mb_strtoupper($value, 'UTF-8') : strtoupper($value);

    }



    private function ensureStudentProfileSchema(): void

    {

        try {

            if (!$this->tableExists('students')) {

                return;

            }



            if (!$this->studentColumnExists('date_naissance')) {

                $this->db->exec("ALTER TABLE students ADD COLUMN date_naissance DATE NULL AFTER prenom");

            }



            if (!$this->studentColumnExists('sexe')) {

                $this->db->exec("ALTER TABLE students ADD COLUMN sexe VARCHAR(20) NULL AFTER class_id");

            }



            if (!$this->studentColumnExists('lieu_naissance')) {

                $this->db->exec("ALTER TABLE students ADD COLUMN lieu_naissance VARCHAR(150) NULL AFTER date_naissance");

            }



            if (!$this->studentColumnExists('is_redoublant')) {

                $this->db->exec("ALTER TABLE students ADD COLUMN is_redoublant TINYINT(1) NOT NULL DEFAULT 0 AFTER lieu_naissance");

            }



            if (!$this->studentColumnExists('is_withdrawn')) {

                $this->db->exec("ALTER TABLE students ADD COLUMN is_withdrawn TINYINT(1) NOT NULL DEFAULT 0 AFTER is_redoublant");

            }



            // Tenter de créer un index unique sur email si la colonne existe et qu'il n'y a pas de doublons

            if ($this->studentColumnExists('email')) {

                try {

                    $dupStmt = $this->db->query("SELECT email, COUNT(*) c FROM students GROUP BY email HAVING c > 1 LIMIT 1");

                    $dup = $dupStmt->fetch(\PDO::FETCH_ASSOC);

                    if (!$dup) {

                        // Vérifier si l'index existe

                        $idxCheck = $this->db->prepare("SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'students' AND INDEX_NAME = 'uniq_students_email'");

                        $idxCheck->execute();

                        if ((int) $idxCheck->fetchColumn() === 0) {

                            $this->db->exec("CREATE UNIQUE INDEX uniq_students_email ON students(email)");

                        }

                    }

                } catch (\Throwable $e) {

                    // Ne pas empêcher l'application de démarrer si l'index ne peut pas être créé

                }

            }

        } catch (\Throwable $e) {

        }

    }



    private function tableExists(string $tableName): bool

    {

        $stmt = $this->db->prepare("SELECT COUNT(*)

            FROM information_schema.TABLES

            WHERE TABLE_SCHEMA = DATABASE()

              AND TABLE_NAME = ?");

        $stmt->execute([$tableName]);

        return (int) $stmt->fetchColumn() > 0;

    }



    private function studentColumnExists(string $columnName): bool

    {

        $stmt = $this->db->prepare("SELECT COUNT(*)

            FROM information_schema.COLUMNS

            WHERE TABLE_SCHEMA = DATABASE()

              AND TABLE_NAME = 'students'

              AND COLUMN_NAME = ?");

        $stmt->execute([$columnName]);

        return (int) $stmt->fetchColumn() > 0;

    }



    private function normalizeOptionalDate(?string $value): ?string

    {

        $value = trim((string) $value);

        if ($value === '') {

            return null;

        }



        $date = \DateTime::createFromFormat('Y-m-d', $value);

        if (!$date || $date->format('Y-m-d') !== $value) {

            return null;

        }



        return $date->format('Y-m-d');

    }



    private function normalizeOptionalText(string $value): ?string

    {

        $value = trim($value);

        return $value !== '' ? $value : null;

    }



    private function normalizeRedoublantFlag($value): int

    {

        return (int) ((string) $value === '1');

    }



    private function normalizeSexe(string $value): ?string

    {

        $value = strtoupper(trim($value));

        if (in_array($value, ['M', 'F'], true)) {

            return $value;

        }

        return null;

    }

}

