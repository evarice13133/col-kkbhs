<?php



namespace App\Controllers;



use App\Core\Database;

use App\Core\Session;

use App\Services\Import\ExcelTemplateService;

use App\Services\Import\StudentImportProcessor;

use App\Services\AcademicYearService;

use PDO;



class StudentController

{

    private $db;

    private \App\Services\MatriculeService $matriculeService;

    private AcademicYearService $academicYearService;

    private const PER_PAGE = 16;



    public function __construct()

    {

        $this->db = Database::getInstance()->getConnection();

        $this->matriculeService = new \App\Services\MatriculeService($this->db);

        $this->academicYearService = new AcademicYearService($this->db);

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



        // Si requête AJAX, renvoyer la réponse en JSON pour fluidité UX
        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            header('Content-Type: application/json');

            $academicYearId = $this->academicYearService->getActiveYearId();
            if (Session::get('user_role') === 'enseignant') {
                $stmt = $this->db->prepare("SELECT id, nom FROM classes WHERE id IN (SELECT DISTINCT class_id FROM teacher_assignments WHERE user_id = ? AND academic_year_id = ?) ORDER BY nom ASC");
                $stmt->execute([Session::get('user_id'), $academicYearId]);
                $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $classes = $this->db->query("SELECT id, nom FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
            }
            $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);

            ob_start();
            include __DIR__ . '/../Views/students/tbody.php';
            $tbodyHtml = ob_get_clean();

            ob_start();
            include __DIR__ . '/../Views/students/badges.php';
            $badgesHtml = ob_get_clean();

            ob_start();
            include __DIR__ . '/../Views/students/pagination.php';
            $paginationHtml = ob_get_clean();

            echo json_encode([
                'success' => true,
                'tbody' => $tbodyHtml,
                'badges' => $badgesHtml,
                'pagination' => $paginationHtml,
                'count' => $totalCount,
                'totalPages' => $totalPages
            ]);
            exit;
        }

        // Progression Sécurité : Les enseignants ne voient que les classes où ils interviennent
        $academicYearId = $this->academicYearService->getActiveYearId();

        if (Session::get('user_role') === 'enseignant') {

            // Classes are now shared across years, no year filtering on classes
            $stmt = $this->db->prepare("SELECT id, nom FROM classes WHERE id IN (SELECT DISTINCT class_id FROM teacher_assignments WHERE user_id = ? AND academic_year_id = ?) ORDER BY nom ASC");

            $stmt->execute([Session::get('user_id'), $academicYearId]);

            $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } else {

            // Classes are now shared across years, no year filtering
            $stmt = $this->db->query("SELECT id, nom FROM classes ORDER BY nom ASC");
            $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        }



        $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);



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



    public function exportExcel()

    {

        // Pas de pagination pour l'export

        [$students, $filters] = $this->fetchStudentsFromFilters();



        // Nettoyer les buffers

        while (ob_get_level()) {

            ob_end_clean();

        }



        ini_set('memory_limit', '512M');



        try {

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setTitle('Export Eleves');



            // En-têtes correspondant au modèle d'import

            $headers = [

                'A1' => 'Nom',

                'B1' => 'Prénom',

                'C1' => 'Matricule',

                'D1' => 'Sexe (M/F)',

                'E1' => 'Date de Naissance',

                'F1' => 'Lieu de Naissance',

                'G1' => 'Classe',

                'H1' => 'Redoublant (OUI/NON)',

                'I1' => 'Contact Père/Mère',

                'J1' => 'Contact Tuteur'

            ];



            foreach ($headers as $cell => $value) {

                $sheet->setCellValue($cell, $value);

            }



            // Style des en-têtes

            $styleArray = [

                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],

                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],

                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],

                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']]

            ];

            $sheet->getStyle('A1:J1')->applyFromArray($styleArray);



            // Remplir les données

            $row = 2;

            foreach ($students as $student) {

                $sheet->setCellValue('A' . $row, $student['nom'] ?? '');

                $sheet->setCellValue('B' . $row, $student['prenom'] ?? '');

                $sheet->setCellValue('C' . $row, $student['email'] ?? ''); // email sert de matricule

                $sheet->setCellValue('D' . $row, $student['sexe'] ?? '');

                $sheet->setCellValue('E' . $row, $student['date_naissance'] ?? '');

                $sheet->setCellValue('F' . $row, $student['lieu_naissance'] ?? '');

                $sheet->setCellValue('G' . $row, $student['classe_nom'] ?? '');

                $sheet->setCellValue('H' . $row, ($student['is_redoublant'] ?? 0) ? 'OUI' : 'NON');

                $sheet->setCellValue('I' . $row, $student['parent_contact'] ?? '');

                $sheet->setCellValue('J' . $row, $student['guardian_contact'] ?? '');



                // Forcer le format texte sur les colonnes sensibles

                $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

                $sheet->getStyle('E' . $row . ':F' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);



                $row++;

            }



            // Ajuster la largeur des colonnes

            foreach(range('A','J') as $col) {

                $sheet->getColumnDimension($col)->setAutoSize(true);

            }



            // Générer le fichier

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

            $isWithdrawn = (int) ($filters['withdrawn'] ?? 0);

            $filename = ($isWithdrawn ? 'Export_Eleves_Retires_' : 'Export_Eleves_') . date('Y-m-d') . '.xlsx';



            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

            header('Content-Disposition: attachment;filename="' . $filename . '"');

            header('Cache-Control: max-age=0');

            header('Pragma: public');



            $writer->save('php://output');



            $spreadsheet->disconnectWorksheets();

            unset($spreadsheet);

            exit;



        } catch (\Throwable $e) {

            Session::setFlash('error', __('error_generation') . " : " . $e->getMessage());

            header("Location: /students");

            exit;

        }

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

        if (!in_array(Session::get('user_role'), ['superadmin', 'admin', 'caissier', 'comptable'])) {

            header("Location: /students");

            exit;

        }

        // Classes are now shared across years, no year filtering
        $classes = $this->db->query("SELECT id, nom, cycle_id, section_id, department_id, teaching_type_id, frais_inscription, frais_inscription_reinscription, frais_scolarite_brut FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        $departments = $this->db->query("SELECT id, nom, teaching_type_id FROM departments WHERE status = 1 ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

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
        if (!in_array(\App\Core\Session::get('user_role'), ['superadmin', 'admin', 'caissier', 'comptable'])) {
            header("Location: /students");
            exit;
        }

        // Classes are now shared across years, no year filtering
        $classes = $this->db->query("SELECT id, nom FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);

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

        $lang = \App\Core\Session::get('lang', 'fr');
        $teachingTypeId = isset($_GET['teaching_type_id']) ? (int)$_GET['teaching_type_id'] : null;

        try {
            // Utilisation du nouveau générateur
            $templateService = new \App\Services\TemplateGenerator($this->db);

            // Génération du flux binaire
            $content = $templateService->generateStudentTemplate($lang, $teachingTypeId);

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
            \App\Core\Session::setFlash('error', __('error_generation') . " : " . $e->getMessage());
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
        if (!in_array(\App\Core\Session::get('user_role'), ['superadmin', 'admin', 'caissier', 'comptable'])) {
            header("Location: /students");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file'])) {
            if (!\App\Core\Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                \App\Core\Security::log("Tentative de CSRF détectée sur l'action Student::upload");
                \App\Core\Session::setFlash('error', __('session_expired_error'));
                header("Location: /students/import");
                exit;
            }

            $file = $_FILES['import_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if ($ext !== 'xlsx') {
                \App\Core\Session::setFlash('error', __('invalid_file_format_excel'));
                header("Location: /students/import");
                exit;
            }

            $lang = \App\Core\Session::get('lang', 'fr');
            $teachingTypeId = isset($_POST['teaching_type_id']) ? (int)$_POST['teaching_type_id'] : 0;

            if ($teachingTypeId <= 0) {
                \App\Core\Session::setFlash('error', 'Le type d\'enseignement est obligatoire pour l\'importation.');
                header("Location: /students/import");
                exit;
            }

            $validator = new \App\Services\StudentImportValidator($this->db);
            $validationResult = $validator->validate($file['tmp_name'], $lang, $teachingTypeId);

            if ($validationResult['isValid']) {
                $processor = new \App\Services\StudentImportProcessor($this->db);
                $result = $processor->processValidRows($validationResult['validRows']);

                if ($result['success']) {
                    \App\Core\Session::setFlash('success', __('import_success_count', ['count' => $result['count']]));
                    header("Location: /students");
                    exit;
                } else {
                    \App\Core\Session::setFlash('popup_errors', json_encode($result['errors'], JSON_UNESCAPED_UNICODE));
                }
            } else {
                \App\Core\Session::setFlash('popup_errors', json_encode($validationResult['errors'], JSON_UNESCAPED_UNICODE));
            }

            // On reload with errors
            $classes = $this->db->query("SELECT id, nom FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
            $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
            include __DIR__ . '/../Views/students/import.php';
        }
    }



    public function store()

    {

        if (!in_array(Session::get('user_role'), ['superadmin', 'admin', 'caissier', 'comptable'])) {

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
            
            $teaching_type_id = !empty($_POST['teaching_type_id']) ? (int) $_POST['teaching_type_id'] : null;

            $section_id = !empty($_POST['section_id']) ? (int) $_POST['section_id'] : null;

            $department_id = !empty($_POST['department_id']) ? (int) $_POST['department_id'] : null;

            $sexe = $this->normalizeSexe($_POST['sexe'] ?? '');

            $date_naissance = $this->normalizeOptionalDate($_POST['date_naissance'] ?? null);

            $lieu_naissance = $this->normalizeOptionalText($_POST['lieu_naissance'] ?? '');

            $is_redoublant = $this->normalizeRedoublantFlag($_POST['is_redoublant'] ?? 0);

            $parent_contact = $this->normalizeOptionalText($_POST['parent_contact'] ?? '');

            $guardian_contact = $this->normalizeOptionalText($_POST['guardian_contact'] ?? '');

            $adresse = $this->normalizeOptionalText($_POST['adresse'] ?? '');

            // Validations strictes d'inscription et de frais d'inscription
            $hasError = false;
            $error = '';

            if (empty($nom) || empty($prenom)) {
                $error = \__('student_name_required');
                $hasError = true;
            } elseif (!$class_id) {
                $error = "La classe d'inscription est obligatoire.";
                $hasError = true;
            } else {
                // Charger la classe
                $classStmt = $this->db->prepare("SELECT nom, frais_inscription, frais_inscription_reinscription, frais_scolarite_brut FROM classes WHERE id = ?");
                $classStmt->execute([$class_id]);
                $classInfo = $classStmt->fetch(PDO::FETCH_ASSOC);

                if (!$classInfo) {
                    $error = "La classe sélectionnée est invalide.";
                    $hasError = true;
                } else {
                    // Charger la politique et calculer le montant requis
                    $settingsStore = new \App\Services\SettingsStore($this->db);
                    $policy = $settingsStore->get('registration_fee_policy', 'all');
                    $student_status = $_POST['student_status'] ?? 'nouveau';

                    $expectedFee = 0.00;
                    if ($policy === 'new_only') {
                        $expectedFee = ($student_status === 'nouveau') ? (float)$classInfo['frais_inscription'] : 0.00;
                    } elseif ($policy === 'by_status') {
                        $expectedFee = ($student_status === 'nouveau') ? (float)$classInfo['frais_inscription'] : (float)$classInfo['frais_inscription_reinscription'];
                    } else { // all
                        $expectedFee = (float)$classInfo['frais_inscription'];
                    }

                    $frais_inscription_paid = isset($_POST['frais_inscription_paid']) ? (float)$_POST['frais_inscription_paid'] : 0.00;
                    $payment_method = $_POST['payment_method'] ?? '';

                    if ($frais_inscription_paid !== $expectedFee) {
                        $error = "Le montant des frais d'inscription versé (" . number_format($frais_inscription_paid, 0, '.', ' ') . " FCFA) doit être exactement égal au montant attendu (" . number_format($expectedFee, 0, '.', ' ') . " FCFA).";
                        $hasError = true;
                    } elseif ($expectedFee > 0 && empty($payment_method)) {
                        $error = "Le mode de paiement est obligatoire pour régler les frais d'inscription.";
                        $hasError = true;
                    }
                }
            }

            if ($hasError) {
                // Rendre les variables nécessaires pour ré-afficher le formulaire create.php
                $classes = $this->db->query("SELECT id, nom, cycle_id, section_id, department_id, teaching_type_id, frais_inscription, frais_inscription_reinscription, frais_scolarite_brut FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $departments = $this->db->query("SELECT id, nom, teaching_type_id FROM departments WHERE status = 1 ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

                $formData = [
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'class_id' => $class_id,
                    'cycle_id' => $cycle_id,
                    'teaching_type_id' => $teaching_type_id,
                    'section_id' => $section_id,
                    'department_id' => $department_id,
                    'sexe' => $sexe,
                    'date_naissance' => $date_naissance,
                    'lieu_naissance' => $lieu_naissance,
                    'is_redoublant' => (string) $is_redoublant,
                    'parent_contact' => $parent_contact,
                    'guardian_contact' => $guardian_contact,
                    'adresse' => $adresse,
                    'student_status' => $_POST['student_status'] ?? 'nouveau',
                    'frais_inscription_paid' => $_POST['frais_inscription_paid'] ?? '0',
                    'payment_method' => $_POST['payment_method'] ?? '',
                    'reference' => $_POST['reference'] ?? '',
                    'reduction_amount' => $_POST['reduction_amount'] ?? '0',
                    'reduction_amount_type' => $_POST['reduction_amount_type'] ?? 'fixed',
                    'reduction_motive' => $_POST['reduction_motive'] ?? '',
                    'scholarship_amount' => $_POST['scholarship_amount'] ?? '0',
                    'scholarship_amount_type' => $_POST['scholarship_amount_type'] ?? 'fixed',
                    'scholarship_motive' => $_POST['scholarship_motive'] ?? '',
                    'adresse' => $adresse,
                ];

                include __DIR__ . '/../Views/students/create.php';
                return;
            }

            // Génération de matricule automatique si vide
            if ($email === '') {
                $email = $this->matriculeService->generate($class_id);
            }

            // Vérifier l'unicité du matricule
            $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM students WHERE email = ?");
            $checkStmt->execute([$email]);
            if ((int) $checkStmt->fetchColumn() > 0) {
                $error = __('matricule_already_exists') ?? 'Matricule déjà utilisé.';
                Session::setFlash('popup_error', $error);
                header("Location: /students/create");
                exit;
            }

            $academicYearId = $this->academicYearService->getActiveYearId();

            try {
                $this->db->beginTransaction();

                // 1. Insérer l'étudiant
                $stmt = $this->db->prepare("INSERT INTO students (nom, prenom, email, class_id, sexe, date_naissance, lieu_naissance, is_redoublant, academic_year_id, photo_eleve, parent_contact, guardian_contact, teaching_type_id, adresse, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nom, $prenom, $email, $class_id, $sexe, $date_naissance, $lieu_naissance, $is_redoublant, $academicYearId, null, $parent_contact, $guardian_contact, $teaching_type_id, $adresse, Session::get('user_id')]);
                $studentId = (int) $this->db->lastInsertId();

                // Gestion de la photo
                $photoPath = null;
                if (isset($_FILES['photo_eleve']) && $_FILES['photo_eleve']['error'] === UPLOAD_ERR_OK) {
                    $photoService = new \App\Services\PhotoUploadService();
                    $uploadResult = $photoService->uploadPhoto($_FILES['photo_eleve'], $studentId);

                    if (!$uploadResult['success']) {
                        throw new \Exception($uploadResult['error']);
                    }

                    $photoPath = $uploadResult['path'];
                    $updateStmt = $this->db->prepare("UPDATE students SET photo_eleve = ? WHERE id = ?");
                    $updateStmt->execute([$photoPath, $studentId]);
                }

                // 2. Créer l'inscription (enrollments) avec son statut
                $enrollmentStmt = $this->db->prepare("INSERT INTO enrollments (student_id, class_id, academic_year_id, student_status, frais_scolarite_brut, total_reductions, total_bourses, total_paye, reste_a_payer) VALUES (?, ?, ?, ?, 0.00, 0.00, 0.00, 0.00, 0.00)");
                $enrollmentStmt->execute([$studentId, $class_id, $academicYearId, $student_status]);

                // 3. Enregistrer l'opération financière et générer le reçu
                $paymentId = null;
                if ($frais_inscription_paid > 0.0) {
                    if (\App\Services\PaymentReferenceGenerator::isCashMethod($payment_method)) {
                        $refGen = new \App\Services\PaymentReferenceGenerator($this->db);
                        $ref = $refGen->generateUniqueReference();
                    } else {
                        $ref = trim($_POST['reference'] ?? '');
                        if (empty($ref)) {
                            $ref = 'Frais d\'inscription payés à l\'inscription';
                        }
                    }

                    // Calcul du frais attendu
                    $classStmt = $this->db->prepare("SELECT frais_inscription, frais_inscription_reinscription FROM classes WHERE id = ?");
                    $classStmt->execute([$class_id]);
                    $classData = $classStmt->fetch(PDO::FETCH_ASSOC);
                    
                    $expectedFee = ($student_status === 'nouveau') ? (float)$classData['frais_inscription'] : (float)$classData['frais_inscription_reinscription'];
                    $amountInscription = min($frais_inscription_paid, $expectedFee);
                    $surplus = max(0.0, $frais_inscription_paid - $expectedFee);
                    
                    // Si le frais attendu est 0 (gratuit) ou si la saisie est inférieure au tarif
                    if ($expectedFee <= 0) {
                        $amountInscription = 0;
                        $surplus = $frais_inscription_paid;
                    }

                    // On n'enregistre le paiement d'inscription que s'il y a un montant affecté à l'inscription ou si tout est 0
                    if ($amountInscription > 0 || $frais_inscription_paid == 0) {
                        $payStmt = $this->db->prepare("INSERT INTO payments (student_id, academic_year_id, amount, type, payment_date, payment_method, reference, created_by) VALUES (?, ?, ?, 'inscription', CURDATE(), ?, ?, ?)");
                        $payStmt->execute([$studentId, $academicYearId, $amountInscription, $payment_method, $ref, Session::get('user_id')]);
                        $paymentId = (int) $this->db->lastInsertId();

                        // Historisation financière
                        $fs = new \App\Services\FinancialService($this->db);
                        $fs->logHistory(Session::get('user_id'), 'payment', $paymentId, 'create', null, [
                            'student_id' => $studentId,
                            'amount' => $amountInscription,
                            'type' => 'inscription',
                            'payment_method' => $payment_method,
                            'reference' => $ref,
                            'commentaire' => 'Frais d\'inscription réglés lors de la création de l\'élève'
                        ]);
                    }

                    // Traitement du surplus (affectation à la scolarité)
                    if ($surplus > 0) {
                        $surplusRef = $ref . ' (Surplus Inscription)';
                        $surplusStmt = $this->db->prepare("INSERT INTO payments (student_id, academic_year_id, amount, type, payment_date, payment_method, reference, created_by, parent_payment_id, commentaire) VALUES (?, ?, ?, 'scolarite', CURDATE(), ?, ?, ?, ?, ?)");
                        $surplusStmt->execute([$studentId, $academicYearId, $surplus, $payment_method, $surplusRef, Session::get('user_id'), $paymentId, 'Versement automatique sur la scolarité (Trop-perçu inscription)']);
                        $surplusId = (int) $this->db->lastInsertId();

                        // Historisation
                        $fs = new \App\Services\FinancialService($this->db);
                        $fs->logHistory(Session::get('user_id'), 'payment', $surplusId, 'create', null, [
                            'student_id' => $studentId,
                            'amount' => $surplus,
                            'type' => 'scolarite',
                            'payment_method' => $payment_method,
                            'reference' => $surplusRef,
                            'parent_payment_id' => $paymentId,
                            'commentaire' => 'Transfert automatique du surplus d\'inscription'
                        ]);
                    }
                }

                // 4. Réduction éventuelle
                $reduction_amount = !empty($_POST['reduction_amount']) ? (float)$_POST['reduction_amount'] : 0.0;
                $reduction_amount_type = $_POST['reduction_amount_type'] ?? 'fixed';
                $reduction_motive = trim($_POST['reduction_motive'] ?? '');
                if ($reduction_amount > 0.0) {
                    $discStmt = $this->db->prepare("INSERT INTO student_discounts (student_id, amount, amount_type, motive, date_effet, status, commentaire) VALUES (?, ?, ?, ?, CURDATE(), 'active', 'Réduction initiale saisie à l\'inscription')");
                    $discStmt->execute([$studentId, $reduction_amount, $reduction_amount_type, $reduction_motive ?: 'Réduction à l\'inscription']);
                }

                // 5. Bourse éventuelle
                $scholarship_amount = !empty($_POST['scholarship_amount']) ? (float)$_POST['scholarship_amount'] : 0.0;
                $scholarship_amount_type = $_POST['scholarship_amount_type'] ?? 'fixed';
                $scholarship_motive = trim($_POST['scholarship_motive'] ?? '');
                if ($scholarship_amount > 0.0) {
                    $scholStmt = $this->db->prepare("INSERT INTO student_scholarships (student_id, amount, amount_type, motive, date_effet, status, commentaire) VALUES (?, ?, ?, ?, CURDATE(), 'active', 'Bourse initiale saisie à l\'inscription')");
                    $scholStmt->execute([$studentId, $scholarship_amount, $scholarship_amount_type, $scholarship_motive ?: 'Bourse à l\'inscription']);
                }

                // 6. Synchronisation financière globale
                $financialService = new \App\Services\FinancialService($this->db);
                $financialService->syncStudentFinancials($studentId, $academicYearId);

                $this->db->commit();

                Session::setFlash('success', __('student_created_success'));

                // Rediriger vers le reçu si généré, sinon retour à la liste des élèves
                if ($paymentId) {
                    header("Location: /payments/receipt?id=" . $paymentId);
                } else {
                    header("Location: /students");
                }
                exit;

            } catch (\Throwable $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }

                \App\Core\Security::log("Erreur lors de l'enregistrement de l'inscription : " . $e->getMessage());
                Session::setFlash('error', "Une erreur est survenue lors de l'enregistrement de l'inscription : " . $e->getMessage());
                header("Location: /students/create");
                exit;
            }

        }

    }



    public function edit($id)

    {

        if (!in_array(Session::get('user_role'), ['superadmin', 'admin', 'caissier', 'comptable'])) {

            header("Location: /students");

            exit;

        }

        $academicYearId = $this->academicYearService->getActiveYearId();

        $stmt = $this->db->prepare("SELECT s.*, c.cycle_id, c.section_id, c.department_id, e.student_status FROM students s LEFT JOIN classes c ON s.class_id = c.id LEFT JOIN enrollments e ON e.student_id = s.id AND e.academic_year_id = ? WHERE s.id = ?");

        $stmt->execute([$academicYearId, $id]);

        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {

            header("Location: /students");

            exit;

        }

        // Classes are now shared across years, no year filtering
        $classes = $this->db->query("SELECT id, nom, cycle_id, section_id, department_id, teaching_type_id, frais_inscription, frais_inscription_reinscription, frais_scolarite_brut FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        $departments = $this->db->query("SELECT id, nom FROM departments WHERE status = 1 ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        $discountStmt = $this->db->prepare("SELECT amount, amount_type, motive FROM student_discounts WHERE student_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1");
        $discountStmt->execute([$id]);
        $discount = $discountStmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $scholarshipStmt = $this->db->prepare("SELECT amount, amount_type, motive FROM student_scholarships WHERE student_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1");
        $scholarshipStmt->execute([$id]);
        $scholarship = $scholarshipStmt->fetch(PDO::FETCH_ASSOC) ?: [];

        include __DIR__ . '/../Views/students/edit.php';

    }



    public function update($id)

    {

        if (!in_array(Session::get('user_role'), ['superadmin', 'admin', 'caissier', 'comptable'])) {
            header("Location: /students");
            exit;
        }

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
            
            $teaching_type_id = !empty($_POST['teaching_type_id']) ? (int) $_POST['teaching_type_id'] : null;

            $section_id = !empty($_POST['section_id']) ? (int) $_POST['section_id'] : null;

            $department_id = !empty($_POST['department_id']) ? (int) $_POST['department_id'] : null;

            $sexe = $this->normalizeSexe($_POST['sexe'] ?? '');

            $date_naissance = $this->normalizeOptionalDate($_POST['date_naissance'] ?? null);

            $lieu_naissance = $this->normalizeOptionalText($_POST['lieu_naissance'] ?? '');

            $is_redoublant = $this->normalizeRedoublantFlag($_POST['is_redoublant'] ?? 0);

            $parent_contact = $this->normalizeOptionalText($_POST['parent_contact'] ?? '');

            $guardian_contact = $this->normalizeOptionalText($_POST['guardian_contact'] ?? '');

            $adresse = $this->normalizeOptionalText($_POST['adresse'] ?? '');



            // Récupérer l'email actuel et la photo actuelle pour déterminer si le matricule change
            $stmt = $this->db->prepare("SELECT email, photo_eleve FROM students WHERE id = ?");

            $stmt->execute([$id]);

            $currentRow = $stmt->fetch(PDO::FETCH_ASSOC);

            $currentEmail = $currentRow['email'] ?? null;
            $currentPhoto = $currentRow['photo_eleve'] ?? null;



            $newEmail = trim($_POST['email'] ?? '');

            $allowEmailChange = in_array(Session::get('user_role'), ['superadmin', 'admin', 'caissier', 'comptable']);



            if ($allowEmailChange && $newEmail !== '' && $newEmail !== $currentEmail) {

                $check = $this->db->prepare("SELECT COUNT(*) FROM students WHERE email = ? AND id != ? AND academic_year_id = ?");
                $academicYearId = $this->academicYearService->getActiveYearId();
                $check->execute([$newEmail, $id, $academicYearId]);

                if ((int) $check->fetchColumn() > 0) {

                    $error = __('matricule_already_exists') ?? "Matricule déjà utilisé.";

                    \App\Core\Session::setFlash('popup_error', $error);

                    header("Location: /students/edit?id=" . $id);

                    exit;

                }

            }



            // Gestion de la photo
            $photoService = new \App\Services\PhotoUploadService();
            $newPhotoPath = $currentPhoto;

            // Suppression de la photo si demandé
            if (isset($_POST['delete_photo']) && $_POST['delete_photo'] === '1') {
                if ($currentPhoto) {
                    $photoService->deletePhoto($currentPhoto);
                    $newPhotoPath = null;
                }
            }
            // Upload d'une nouvelle photo
            elseif (isset($_FILES['photo_eleve']) && $_FILES['photo_eleve']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $photoService->uploadPhoto($_FILES['photo_eleve'], $id);

                if (!$uploadResult['success']) {
                    $error = $uploadResult['error'];

                    \App\Core\Security::log(
                        "Student::update photo upload failed (studentId={$id}, error={$error}, fileName=" .
                        ($_FILES['photo_eleve']['name'] ?? 'NA') . ", size=" .
                        ($_FILES['photo_eleve']['size'] ?? 'NA') . ", phpUploadError=" .
                        ($_FILES['photo_eleve']['error'] ?? 'NA') . ")"
                    );

                    Session::setFlash('error', $error);

                    header("Location: /students/edit?id=" . $id);
                    exit;
                }

                // Supprimer l'ancienne photo si elle existe
                if ($currentPhoto) {
                    $photoService->deletePhoto($currentPhoto);
                }

                $newPhotoPath = $uploadResult['path'];
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
                    
                    'teaching_type_id' => $teaching_type_id,

                    'section_id' => $section_id,

                    'department_id' => $department_id,

                    'sexe' => $sexe,

                    'date_naissance' => $date_naissance,

                    'lieu_naissance' => $lieu_naissance,

                    'is_redoublant' => $is_redoublant,

                    'parent_contact' => $parent_contact,

                    'guardian_contact' => $guardian_contact,

                    'adresse' => $adresse,

                ];

                // Classes are now shared across years, no year filtering
                $classes = $this->db->query("SELECT id, nom, cycle_id, section_id, department_id, teaching_type_id FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

                $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                
                $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);

                $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

                $departments = $this->db->query("SELECT id, nom FROM departments WHERE status = 1 ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

                include __DIR__ . '/../Views/students/edit.php';

                return;

            }



            // Préparer la mise à jour. Autoriser la modification du matricule pour admin/superadmin

            $updateParts = ['nom = ?', 'prenom = ?', 'class_id = ?', 'sexe = ?', 'date_naissance = ?', 'lieu_naissance = ?', 'is_redoublant = ?', 'photo_eleve = ?', 'parent_contact = ?', 'guardian_contact = ?', 'teaching_type_id = ?', 'adresse = ?'];

            $params = [$nom, $prenom, $class_id, $sexe, $date_naissance, $lieu_naissance, $is_redoublant, $newPhotoPath, $parent_contact, $guardian_contact, $teaching_type_id, $adresse];



            if ($allowEmailChange && $newEmail !== '' && $newEmail !== $currentEmail) {

                array_unshift($params, $newEmail);

                array_unshift($updateParts, 'email = ?');

            }



            $sql = "UPDATE students SET " . implode(', ', $updateParts) . " WHERE id = ?";

            $params[] = $id;

            $stmt = $this->db->prepare($sql);

            $stmt->execute($params);

            $student_status = $_POST['student_status'] ?? 'nouveau';

            // S'assurer de l'existence de l'inscription pour l'année active
            $academicYearId = $this->academicYearService->getActiveYearId();
            $enrollCheck = $this->db->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id = ? AND academic_year_id = ?");
            $enrollCheck->execute([$id, $academicYearId]);
            if ((int)$enrollCheck->fetchColumn() === 0) {
                $enrollIns = $this->db->prepare("INSERT INTO enrollments (student_id, class_id, academic_year_id, student_status, frais_scolarite_brut, total_reductions, total_bourses, total_paye, reste_a_payer) VALUES (?, ?, ?, ?, 0.00, 0.00, 0.00, 0.00, 0.00)");
                $enrollIns->execute([$id, $class_id, $academicYearId, $student_status]);
            } else {
                // Mettre à jour la classe et le statut dans enrollments
                $enrollUpd = $this->db->prepare("UPDATE enrollments SET class_id = ?, student_status = ? WHERE student_id = ? AND academic_year_id = ?");
                $enrollUpd->execute([$class_id, $student_status, $id, $academicYearId]);
            }

            // Mise à jour de la réduction
            if (isset($_POST['reduction_amount'])) {
                $reduction_amount = (float)$_POST['reduction_amount'];
                $reduction_amount_type = $_POST['reduction_amount_type'] ?? 'fixed';
                $reduction_motive = trim($_POST['reduction_motive'] ?? '');
                
                // Désactiver l'ancienne
                $this->db->prepare("UPDATE student_discounts SET status = 'inactive' WHERE student_id = ?")->execute([$id]);
                
                if ($reduction_amount > 0) {
                    $discStmt = $this->db->prepare("INSERT INTO student_discounts (student_id, amount, amount_type, motive, date_effet, status, commentaire) VALUES (?, ?, ?, ?, CURDATE(), 'active', 'Réduction mise à jour depuis l\'édition')");
                    $discStmt->execute([$id, $reduction_amount, $reduction_amount_type, $reduction_motive]);
                }
            }

            // Mise à jour de la bourse
            if (isset($_POST['scholarship_amount'])) {
                $scholarship_amount = (float)$_POST['scholarship_amount'];
                $scholarship_amount_type = $_POST['scholarship_amount_type'] ?? 'fixed';
                $scholarship_motive = trim($_POST['scholarship_motive'] ?? '');
                
                // Désactiver l'ancienne
                $this->db->prepare("UPDATE student_scholarships SET status = 'inactive' WHERE student_id = ?")->execute([$id]);
                
                if ($scholarship_amount > 0) {
                    $scholStmt = $this->db->prepare("INSERT INTO student_scholarships (student_id, amount, amount_type, motive, date_effet, status, commentaire) VALUES (?, ?, ?, ?, CURDATE(), 'active', 'Bourse mise à jour depuis l\'édition')");
                    $scholStmt->execute([$id, $scholarship_amount, $scholarship_amount_type, $scholarship_motive]);
                }
            }

            // Lancer la synchronisation financière de l'élève
            $financialService = new \App\Services\FinancialService($this->db);
            $financialService->syncStudentFinancials((int)$id, $academicYearId);

            Session::setFlash('success', __('student_updated_success'));

            header("Location: /students");

            exit;

        }

    }



    public function withdraw($id)

    {

        if (!in_array(Session::get('user_role'), ['superadmin', 'admin', 'caissier', 'comptable'])) {

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

        if (!in_array(Session::get('user_role'), ['superadmin', 'admin', 'caissier', 'comptable'])) {

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

        if (!in_array(Session::get('user_role'), ['superadmin', 'admin', 'caissier', 'comptable'])) {

            header("Location: /students");

            exit;

        }

        if (!Session::verifyCsrfToken($_GET['csrf_token'] ?? '')) {

            \App\Core\Security::log("Tentative de CSRF détectée sur l'action Student::delete (ID: $id)");

            Session::setFlash('error', __('unauthorized_action'));

            header("Location: /students");

            exit;

        }

        $stmt = $this->db->prepare("UPDATE students SET actif = 0 WHERE id = ?");

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
        
        $teachingTypeId = (int) ($_GET['teaching_type_id'] ?? 0);

        $showWithdrawn = (int) ($_GET['withdrawn'] ?? 0);

        $onlyMine = (int) ($_GET['only_mine'] ?? 0);

        $academicYearId = $this->academicYearService->getActiveYearId();



        // --- 1. Construction des conditions ---

        $where = " WHERE s.is_withdrawn = ? AND s.actif = 1";

        $params = [$showWithdrawn];

        if ($onlyMine > 0) {
            $where .= " AND s.created_by = ?";
            $params[] = Session::get('user_id');
        }



        // Filter by academic year
        if ($academicYearId > 0) {
            $where .= " AND s.academic_year_id = ?";
            $params[] = $academicYearId;
        }



        if (Session::get('user_role') === 'enseignant') {

            $where .= " AND s.class_id IN (SELECT DISTINCT class_id FROM teacher_assignments WHERE user_id = ? AND academic_year_id = ?)";

            $params[] = Session::get('user_id');
            $params[] = $academicYearId;

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

        if ($teachingTypeId > 0) {

            $where .= " AND s.teaching_type_id = ?";

            $params[] = $teachingTypeId;

        }



        // --- 2. Calcul du total (sans pagination) ---

        $countSql = "SELECT COUNT(*) FROM students s 

                     LEFT JOIN classes c ON s.class_id = c.id 

                     LEFT JOIN departments d ON c.department_id = d.id" . $where;

        $countStmt = $this->db->prepare($countSql);

        $countStmt->execute($params);

        $totalCount = (int) $countStmt->fetchColumn();



        // --- 3. Récupération des données avec pagination si demandée ---

        $sql = "SELECT s.*, c.nom as classe_nom, cy.nom as cycle_nom, sec.nom as section_nom, d.nom as department_nom, tt.nom as teaching_type_nom

                FROM students s

                LEFT JOIN classes c ON s.class_id = c.id

                LEFT JOIN cycles cy ON c.cycle_id = cy.id

                LEFT JOIN sections sec ON c.section_id = sec.id

                LEFT JOIN departments d ON c.department_id = d.id
                
                LEFT JOIN teaching_types tt ON s.teaching_type_id = tt.id" . $where;



        $sql .= " ORDER BY s.nom ASC, s.prenom ASC";



        if ($limit !== null) {

            $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;

        }



        $stmt = $this->db->prepare($sql);

        $stmt->execute($params);



        return [

            $stmt->fetchAll(PDO::FETCH_ASSOC), 

            ['q' => $search, 'class_id' => $classId, 'section_id' => $sectionId, 'teaching_type_id' => $teachingTypeId, 'withdrawn' => $showWithdrawn, 'only_mine' => $onlyMine],

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



            if (!$this->studentColumnExists('actif')) {

                $this->db->exec("ALTER TABLE students ADD COLUMN actif TINYINT(1) NOT NULL DEFAULT 1 AFTER is_withdrawn");

            }



            if (!$this->studentColumnExists('photo_eleve')) {

                $this->db->exec("ALTER TABLE students ADD COLUMN photo_eleve VARCHAR(255) NULL AFTER is_withdrawn");

            }

            if (!$this->studentColumnExists('parent_contact')) {

                $this->db->exec("ALTER TABLE students ADD COLUMN parent_contact VARCHAR(50) NULL AFTER photo_eleve");

            }

            if (!$this->studentColumnExists('guardian_contact')) {

                $this->db->exec("ALTER TABLE students ADD COLUMN guardian_contact VARCHAR(50) NULL AFTER parent_contact");

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

