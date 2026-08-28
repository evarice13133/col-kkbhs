<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Core\PermissionManager;
use App\Services\Import\ExcelTemplateService;
use App\Services\Import\SubjectImportProcessor;
use App\Services\AcademicYearService;
use PDO;

class SubjectController
{
    private $db;
    private AcademicYearService $academicYearService;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->academicYearService = new AcademicYearService($this->db);
        if (!Session::isLogged()) {
            header("Location: /login");
            exit;
        }

        $this->db->exec("CREATE TABLE IF NOT EXISTS subject_classes (
            subject_id INT NOT NULL, class_id INT NOT NULL, academic_year_id INT NOT NULL,
            PRIMARY KEY (subject_id, class_id, academic_year_id),
            FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
            FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
            FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON UPDATE CASCADE
        )");

        // Ensure optional columns exist in subjects table
        try {
            $this->db->exec("ALTER TABLE subjects ADD COLUMN groupe VARCHAR(50) DEFAULT 'Groupe 1'");
        } catch (\PDOException $e) {}
        try {
            $this->db->exec("ALTER TABLE subjects ADD COLUMN vhm DECIMAL(8,2) DEFAULT NULL");
        } catch (\PDOException $e) {}
        try {
            $this->db->exec("ALTER TABLE subjects ADD COLUMN vhp DECIMAL(8,2) DEFAULT NULL");
        } catch (\PDOException $e) {}
        try {
            $this->db->exec("ALTER TABLE subjects ADD COLUMN th_max DECIMAL(8,2) DEFAULT NULL");
        } catch (\PDOException $e) {}
        try {
            $this->db->exec("ALTER TABLE subjects ADD COLUMN observations TEXT DEFAULT NULL");
        } catch (\PDOException $e) {}
        // Garantir que la colonne `id` est PRIMARY KEY et AUTO_INCREMENT de façon non destructive.
        try {
            // Vérifier s'il existe des doublons d'ID -> ne pas appliquer la modification automatiquement
            $dupStmt = $this->db->query("SELECT id, COUNT(*) AS c FROM subjects GROUP BY id HAVING c > 1");
            $hasDup = ($dupStmt && $dupStmt->fetchColumn() !== false);
            if (!$hasDup) {
                try {
                    $this->db->exec("ALTER TABLE subjects MODIFY id INT(11) NOT NULL AUTO_INCREMENT");
                } catch (\PDOException $e) {
                    // Si MODIFY échoue, on continue et on tentera d'ajouter la PK si possible
                }

                try {
                    $this->db->exec("ALTER TABLE subjects ADD PRIMARY KEY (id)");
                } catch (\PDOException $e) {
                    // PK peut déjà exister ou échouer si données incohérentes
                }

                // Synchroniser la valeur AUTO_INCREMENT avec le max(id)+1
                try {
                    $maxId = (int) $this->db->query("SELECT COALESCE(MAX(id),0) FROM subjects")->fetchColumn();
                    $next = $maxId + 1;
                    $this->db->exec("ALTER TABLE subjects AUTO_INCREMENT = " . (int)$next);
                } catch (\PDOException $e) {
                    // Ne pas bloquer l'exécution sur l'échec de la synchronisation
                }
            } else {
                // Si doublons détectés, on ne change rien automatiquement — opération manuelle requise
                // Éventuelle journalisation à ajouter ici si nécessaire
            }
        } catch (\PDOException $e) {
            // Ignorer les erreurs d'audit ici pour ne pas empêcher l'initialisation de la page
        }

    }

    public function index()
    {
        $page = (int) ($_GET['page'] ?? 1);
        $limit = 16;
        $offset = ($page - 1) * $limit;

        [$subjects, $filters, $totalCount] = $this->fetchSubjectsFromFilters($limit, $offset);
        $totalPages = (int) ceil($totalCount / $limit);

        if ($page > $totalPages && $totalCount > 0) {
            header("Location: /subjects?page=1");
            exit;
        }

        $classes = $this->db->query("SELECT c.id, c.nom, c.teaching_type_id FROM classes c LEFT JOIN departments d ON c.department_id = d.id LEFT JOIN cycles cy ON c.cycle_id = cy.id LEFT JOIN sections sec ON c.section_id = sec.id LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id WHERE (c.department_id IS NULL OR d.status = 1) AND (c.cycle_id IS NULL OR cy.status = 1) AND (c.section_id IS NULL OR sec.status = 1) AND (c.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY c.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $departments = $this->db->query("SELECT d.id, d.nom, d.teaching_type_id FROM departments d LEFT JOIN teaching_types tt ON d.teaching_type_id = tt.id WHERE d.status = 1 AND (d.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY d.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        include __DIR__ . '/../Views/subjects/index.php';
    }

    public function export()
    {
        [$subjects] = $this->fetchSubjectsFromFilters();

        $settingsStore = new \App\Services\SettingsStore($this->db);
        $logoManager = \App\Core\LogoManager::getInstance($this->db);
        
        $school_name = $settingsStore->get('school_name', 'NotesMaster');
        $logo_base64 = $logoManager->hasLogo() ? $logoManager->getLogoBase64() : '';
        $title = "Registre des matières";

        ob_start();
        include __DIR__ . '/../Views/subjects/templates/export_pdf_subject.php';
        $html = ob_get_clean();

        $this->streamPdf($html, "Registre_Matieres_" . date('Y-m-d') . ".pdf");
    }

    public function exportExcel()
    {
        PermissionManager::requirePermission('manage_subjects');

        [$subjects] = $this->fetchSubjectsFromFilters();

        while (ob_get_level()) {
            ob_end_clean();
        }
        ini_set('memory_limit', '512M');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Matières');

        $headers = [
            'A1' => 'Matière',
            'B1' => 'Coef',
            'C1' => 'Groupe',
            'D1' => 'Classes concernées',
            'E1' => 'VHm',
            'F1' => 'VHp',
            'G1' => 'TH(Max)',
            'H1' => 'Observations'
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E78']
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT
            ]
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
        $sheet->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('E1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('F1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('G1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getRowDimension(1)->setRowHeight(26);

        $row = 2;
        foreach ($subjects as $s) {
            $nom = $s['nom'] ?? '';
            $coef = is_numeric($s['coefficient']) ? (float)$s['coefficient'] : 1;
            
            $groupe = !empty($s['subject_group_libelle']) 
                ? $s['subject_group_libelle'] 
                : (!empty($s['groupe']) ? $s['groupe'] : '-');
                
            $classes = !empty($s['classes_list']) ? $s['classes_list'] : '-';
            $vhm = (!empty($s['vhm']) || (isset($s['vhm']) && is_numeric($s['vhm']))) ? (float)$s['vhm'] : null;
            $vhp = (!empty($s['vhp']) || (isset($s['vhp']) && is_numeric($s['vhp']))) ? (float)$s['vhp'] : null;
            $thMax = (!empty($s['th_max']) || (isset($s['th_max']) && is_numeric($s['th_max']))) ? (float)$s['th_max'] : null;
            $observations = !empty($s['observations']) ? $s['observations'] : '';

            $sheet->setCellValue("A{$row}", $nom);
            $sheet->setCellValue("B{$row}", $coef);
            $sheet->setCellValue("C{$row}", $groupe);
            $sheet->setCellValue("D{$row}", $classes);

            if ($vhm !== null) {
                $sheet->setCellValue("E{$row}", $vhm);
                $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0');
            } else {
                $sheet->setCellValue("E{$row}", '');
            }

            if ($vhp !== null) {
                $sheet->setCellValue("F{$row}", $vhp);
                $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('#,##0');
            } else {
                $sheet->setCellValue("F{$row}", '');
            }

            if ($thMax !== null) {
                $sheet->setCellValue("G{$row}", $thMax);
                $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0');
            } else {
                $sheet->setCellValue("G{$row}", '');
            }

            $sheet->setCellValue("H{$row}", $observations);

            $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

            $sheet->getStyle("D{$row}")->getAlignment()->setWrapText(true);
            $sheet->getStyle("H{$row}")->getAlignment()->setWrapText(true);

            $row++;
        }

        $lastDataRow = $row - 1;

        // Ligne du Total VHm
        $sheet->setCellValue("A{$row}", 'TOTAL VHm');
        if ($lastDataRow >= 2) {
            $sheet->setCellValue("E{$row}", "=SUM(E2:E{$lastDataRow})");
        } else {
            $sheet->setCellValue("E{$row}", 0);
        }

        $totalStyle = [
            'font' => [
                'bold' => true,
                'size' => 11
            ],
            'borders' => [
                'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F2F4F7']
            ]
        ];
        $sheet->getStyle("A{$row}:H{$row}")->applyFromArray($totalStyle);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0');

        if ($lastDataRow >= 2) {
            $sheet->setAutoFilter("A1:H{$lastDataRow}");
        }
        $sheet->freezePane('A2');

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = "matieres_etablissement_" . date('Y-m-d') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    protected function streamPdf(string $html, string $filename)
    {
        // Nettoyage complet des tampons de sortie
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
            $dompdf->stream($filename, ["Attachment" => true]);
        } catch (\Throwable $e) {
            echo "Erreur lors de la génération du PDF : " . $e->getMessage();
        }
        exit;
    }

    public function create()
    {
        // Sécurité RBAC : Accès réservé aux administrateurs
        PermissionManager::requirePermission('manage_subjects');
        
        $classes = $this->db->query("SELECT c.id, c.nom, c.teaching_type_id FROM classes c LEFT JOIN departments d ON c.department_id = d.id LEFT JOIN cycles cy ON c.cycle_id = cy.id LEFT JOIN sections sec ON c.section_id = sec.id LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id WHERE (c.department_id IS NULL OR d.status = 1) AND (c.cycle_id IS NULL OR cy.status = 1) AND (c.section_id IS NULL OR sec.status = 1) AND (c.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY c.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $teachingTypes = $this->db->query("SELECT id, nom, code FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $deptQuery = "SELECT d.id, d.nom, d.teaching_type_id FROM departments d LEFT JOIN teaching_types tt ON d.teaching_type_id = tt.id WHERE d.status = 1 AND (d.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY d.nom ASC";
        $departments = $this->db->query($deptQuery)->fetchAll(PDO::FETCH_ASSOC);
        $subjectGroups = $this->db->query("SELECT id, libelle, teaching_type_id FROM subject_groups WHERE status = 1 ORDER BY libelle ASC")->fetchAll(PDO::FETCH_ASSOC);
        include __DIR__ . '/../Views/subjects/create.php';
    }

    public function store()
    {
        // Sécurité RBAC : Accès réservé aux administrateurs
        PermissionManager::requirePermission('manage_subjects');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $coeff = (int) ($_POST['coefficient'] ?? 1);
            $vhm = (isset($_POST['vhm']) && $_POST['vhm'] !== '' && is_numeric($_POST['vhm'])) ? (float) $_POST['vhm'] : null;
            $vhp = (isset($_POST['vhp']) && $_POST['vhp'] !== '' && is_numeric($_POST['vhp'])) ? (float) $_POST['vhp'] : null;
            $th_max = (isset($_POST['th_max']) && $_POST['th_max'] !== '' && is_numeric($_POST['th_max'])) ? (float) $_POST['th_max'] : null;
            $observations = (isset($_POST['observations']) && trim($_POST['observations']) !== '') ? trim($_POST['observations']) : null;
            $subject_group_id = !empty($_POST['subject_group_id']) ? (int) $_POST['subject_group_id'] : null;
            $groupe = trim($_POST['groupe'] ?? 'Groupe 1');
            $teaching_type_id = !empty($_POST['teaching_type_id']) ? (int) $_POST['teaching_type_id'] : null;
            $classes_ids = array_values(array_unique(array_map('intval', $_POST['classes'] ?? [])));

            $code_uv = !empty($_POST['code_uv']) ? trim($_POST['code_uv']) : null;
            $code_ue = !empty($_POST['code_ue']) ? trim($_POST['code_ue']) : null;
            if ($teaching_type_id) {
                $stmtTt = $this->db->prepare("SELECT code FROM teaching_types WHERE id = ?");
                $stmtTt->execute([$teaching_type_id]);
                $ttCode = $stmtTt->fetchColumn();
                if ($ttCode !== 'LMD') {
                    $code_uv = null;
                    $code_ue = null;
                }
            } else {
                $code_uv = null;
                $code_ue = null;
            }

            // Si un groupe de modules est sélectionné, récupérer son libellé pour assurer la rétrocompatibilité du champ 'groupe'
            if ($subject_group_id) {
                $grpStmt = $this->db->prepare("SELECT libelle FROM subject_groups WHERE id = ?");
                $grpStmt->execute([$subject_group_id]);
                $grpLib = $grpStmt->fetchColumn();
                if ($grpLib) $groupe = $grpLib;
            }

            if (empty($nom) || empty($classes_ids)) {
                $error = \__('subject_name_and_class_required');
                $classes = $this->db->query("SELECT c.id, c.nom, c.teaching_type_id FROM classes c LEFT JOIN departments d ON c.department_id = d.id LEFT JOIN cycles cy ON c.cycle_id = cy.id LEFT JOIN sections sec ON c.section_id = sec.id LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id WHERE (c.department_id IS NULL OR d.status = 1) AND (c.cycle_id IS NULL OR cy.status = 1) AND (c.section_id IS NULL OR sec.status = 1) AND (c.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY c.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $teachingTypes = $this->db->query("SELECT id, nom, code FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $deptQuery = "SELECT d.id, d.nom, d.teaching_type_id FROM departments d LEFT JOIN teaching_types tt ON d.teaching_type_id = tt.id WHERE d.status = 1 AND (d.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY d.nom ASC";
                $departments = $this->db->query($deptQuery)->fetchAll(PDO::FETCH_ASSOC);
                $subjectGroups = $this->db->query("SELECT id, libelle, teaching_type_id FROM subject_groups WHERE status = 1 ORDER BY libelle ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/subjects/create.php';
                return;
            }

            $duplicateClasses = $this->findDuplicateClassesForSubjectName($nom, $classes_ids);
            if (!empty($duplicateClasses)) {
                $error = \__('subject_already_exists_in_classes', ['classes' => implode(', ', $duplicateClasses)]);
                $classes = $this->db->query("SELECT c.id, c.nom, c.teaching_type_id FROM classes c LEFT JOIN departments d ON c.department_id = d.id LEFT JOIN cycles cy ON c.cycle_id = cy.id LEFT JOIN sections sec ON c.section_id = sec.id LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id WHERE (c.department_id IS NULL OR d.status = 1) AND (c.cycle_id IS NULL OR cy.status = 1) AND (c.section_id IS NULL OR sec.status = 1) AND (c.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY c.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $teachingTypes = $this->db->query("SELECT id, nom, code FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $deptQuery = "SELECT d.id, d.nom, d.teaching_type_id FROM departments d LEFT JOIN teaching_types tt ON d.teaching_type_id = tt.id WHERE d.status = 1 AND (d.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY d.nom ASC";
                $departments = $this->db->query($deptQuery)->fetchAll(PDO::FETCH_ASSOC);
                $subjectGroups = $this->db->query("SELECT id, libelle, teaching_type_id FROM subject_groups WHERE status = 1 ORDER BY libelle ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/subjects/create.php';
                return;
            }

            try {
                $this->db->beginTransaction();

                $stmt = $this->db->prepare("INSERT INTO subjects (nom, coefficient, groupe, subject_group_id, teaching_type_id, code_uv, code_ue, vhm, vhp, th_max, observations) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nom, $coeff, $groupe, $subject_group_id, $teaching_type_id, $code_uv, $code_ue, $vhm, $vhp, $th_max, $observations]);
                $subject_id = (int) $this->db->lastInsertId();

                // Fallback defensif si lastInsertId() est invalide (ex: retourne 0 ou 1 de manière inattendue)
                if ($subject_id <= 1) {
                    // Tenter une recherche sûre basée sur le nom et la date de création la plus récente
                    try {
                        $fallbackStmt = $this->db->prepare("SELECT id FROM subjects WHERE nom = ? ORDER BY id DESC LIMIT 1");
                        $fallbackStmt->execute([$nom]);
                        $found = (int) $fallbackStmt->fetchColumn();
                        if ($found > 0) {
                            $subject_id = $found;
                        } else {
                            // journaliser pour investigation
                            error_log("[SubjectController] lastInsertId invalid and fallback failed for subject '{$nom}'");
                        }
                    } catch (\Throwable $e) {
                        error_log("[SubjectController] fallback select id failed: " . $e->getMessage());
                    }
                }

                $academicYearId = $this->academicYearService->getActiveYearId();
                $stmt = $this->db->prepare("INSERT INTO subject_classes (subject_id, class_id, academic_year_id) VALUES (?, ?, ?)");
                foreach ($classes_ids as $cid) {
                    $stmt->execute([$subject_id, (int) $cid, $academicYearId]);
                }

                // Sauvegarder les compétences si fournies
                $competencies = $_POST['competencies'] ?? [];
                if (!empty($competencies) && is_array($competencies)) {
                    $compStmt = $this->db->prepare("INSERT INTO competencies (subject_id, libelle, position, created_by) VALUES (?, ?, ?, ?)");
                    $userId = (int) Session::get('user_id');
                    foreach ($competencies as $index => $libelle) {
                        $libelle = trim($libelle);
                        if (!empty($libelle)) {
                            $compStmt->execute([$subject_id, $libelle, $index + 1, $userId]);
                        }
                    }
                }

                $this->db->commit();
                Session::setFlash('success', __('subject_created_success'));
                header("Location: /subjects");
                exit;
            } catch (\PDOException $e) {
                $this->db->rollBack();
                $error = \__('server_error_subject_creation');
                $classes = $this->db->query("SELECT id, nom FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $teachingTypes = $this->db->query("SELECT id, nom, code FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $deptQuery = Session::get('user_role') === 'superadmin' ? "SELECT id, nom, teaching_type_id FROM departments ORDER BY nom ASC" : "SELECT id, nom, teaching_type_id FROM departments WHERE status = 1 ORDER BY nom ASC";
                $departments = $this->db->query($deptQuery)->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/subjects/create.php';
            }
        }
    }

    public function edit($id)
    {
        \App\Core\PermissionManager::requirePermission('manage_subjects');

        $stmt = $this->db->prepare("SELECT * FROM subjects WHERE id = ?");
        $stmt->execute([$id]);
        $subject = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$subject) {
            header("Location: /subjects");
            exit;
        }

        // Récupérer l'année académique sélectionnée ou utiliser l'année active par défaut
        $selectedYearId = (int) ($_GET['academic_year_id'] ?? 0);
        if ($selectedYearId <= 0) {
            $selectedYearId = $this->academicYearService->getActiveYearId();
        }

        // Récupérer toutes les années académiques pour le sélecteur
        $academicYears = $this->db->query("SELECT id, nom, is_active FROM academic_years ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer les classes assignées pour l'année sélectionnée
        $stmt_assoc = $this->db->prepare("SELECT class_id FROM subject_classes WHERE subject_id = ? AND academic_year_id = ?");
        $stmt_assoc->execute([$id, $selectedYearId]);
        $assigned_classes = $stmt_assoc->fetchAll(PDO::FETCH_COLUMN);

        $classes = $this->db->query("SELECT c.id, c.nom, c.teaching_type_id FROM classes c LEFT JOIN departments d ON c.department_id = d.id LEFT JOIN cycles cy ON c.cycle_id = cy.id LEFT JOIN sections sec ON c.section_id = sec.id LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id WHERE (c.department_id IS NULL OR d.status = 1) AND (c.cycle_id IS NULL OR cy.status = 1) AND (c.section_id IS NULL OR sec.status = 1) AND (c.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY c.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $teachingTypes = $this->db->query("SELECT id, nom, code FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $deptQuery = "SELECT d.id, d.nom, d.teaching_type_id FROM departments d LEFT JOIN teaching_types tt ON d.teaching_type_id = tt.id WHERE d.status = 1 AND (d.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY d.nom ASC";
        $departments = $this->db->query($deptQuery)->fetchAll(PDO::FETCH_ASSOC);
        $subjectGroups = $this->db->query("SELECT id, libelle, teaching_type_id FROM subject_groups WHERE status = 1 ORDER BY libelle ASC")->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer les compétences existantes de la matière
        $stmtComp = $this->db->prepare("SELECT id, libelle, description, position FROM competencies WHERE subject_id = ? ORDER BY position, libelle");
        $stmtComp->execute([$id]);
        $existingCompetencies = $stmtComp->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/subjects/edit.php';
    }

    public function update($id)
    {
        \App\Core\PermissionManager::requirePermission('manage_subjects');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $coeff = (int) ($_POST['coefficient'] ?? 1);
            $vhm = (isset($_POST['vhm']) && $_POST['vhm'] !== '' && is_numeric($_POST['vhm'])) ? (float) $_POST['vhm'] : null;
            $vhp = (isset($_POST['vhp']) && $_POST['vhp'] !== '' && is_numeric($_POST['vhp'])) ? (float) $_POST['vhp'] : null;
            $th_max = (isset($_POST['th_max']) && $_POST['th_max'] !== '' && is_numeric($_POST['th_max'])) ? (float) $_POST['th_max'] : null;
            $observations = (isset($_POST['observations']) && trim($_POST['observations']) !== '') ? trim($_POST['observations']) : null;
            $subject_group_id = !empty($_POST['subject_group_id']) ? (int) $_POST['subject_group_id'] : null;
            $groupe = trim($_POST['groupe'] ?? 'Groupe 1');
            $teaching_type_id = !empty($_POST['teaching_type_id']) ? (int) $_POST['teaching_type_id'] : null;
            $classes_ids = array_values(array_unique(array_map('intval', $_POST['classes'] ?? [])));
            $academicYearId = (int) ($_POST['academic_year_id'] ?? $this->academicYearService->getActiveYearId());

            $code_uv = !empty($_POST['code_uv']) ? trim($_POST['code_uv']) : null;
            $code_ue = !empty($_POST['code_ue']) ? trim($_POST['code_ue']) : null;
            if ($teaching_type_id) {
                $stmtTt = $this->db->prepare("SELECT code FROM teaching_types WHERE id = ?");
                $stmtTt->execute([$teaching_type_id]);
                $ttCode = $stmtTt->fetchColumn();
                if ($ttCode !== 'LMD') {
                    $code_uv = null;
                    $code_ue = null;
                }
            } else {
                $code_uv = null;
                $code_ue = null;
            }

            if ($subject_group_id) {
                $grpStmt = $this->db->prepare("SELECT libelle FROM subject_groups WHERE id = ?");
                $grpStmt->execute([$subject_group_id]);
                $grpLib = $grpStmt->fetchColumn();
                if ($grpLib) $groupe = $grpLib;
            }

            if (empty($nom) || empty($classes_ids)) {
                $error = \__('subject_name_and_one_class_required');
                $subject = ['id' => $id, 'nom' => $nom, 'coefficient' => $coeff, 'groupe' => $groupe, 'subject_group_id' => $subject_group_id, 'teaching_type_id' => $teaching_type_id, 'code_uv' => $code_uv, 'code_ue' => $code_ue, 'vhm' => $vhm, 'vhp' => $vhp, 'th_max' => $th_max, 'observations' => $observations];
                $assigned_classes = $classes_ids;
                $classes = $this->db->query("SELECT c.id, c.nom, c.teaching_type_id FROM classes c LEFT JOIN departments d ON c.department_id = d.id LEFT JOIN cycles cy ON c.cycle_id = cy.id LEFT JOIN sections sec ON c.section_id = sec.id LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id WHERE (c.department_id IS NULL OR d.status = 1) AND (c.cycle_id IS NULL OR cy.status = 1) AND (c.section_id IS NULL OR sec.status = 1) AND (c.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY c.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $teachingTypes = $this->db->query("SELECT id, nom, code FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $academicYears = $this->db->query("SELECT id, nom, is_active FROM academic_years ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
                $deptQuery = "SELECT d.id, d.nom, d.teaching_type_id FROM departments d LEFT JOIN teaching_types tt ON d.teaching_type_id = tt.id WHERE d.status = 1 AND (d.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY d.nom ASC";
                $departments = $this->db->query($deptQuery)->fetchAll(PDO::FETCH_ASSOC);
                $subjectGroups = $this->db->query("SELECT id, libelle, teaching_type_id FROM subject_groups WHERE status = 1 ORDER BY libelle ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/subjects/edit.php';
                return;
            }

            $duplicateClasses = $this->findDuplicateClassesForSubjectName($nom, $classes_ids, (int) $id);
            if (!empty($duplicateClasses)) {
                $error = \__('subject_already_exists_in_classes', ['classes' => implode(', ', $duplicateClasses)]);
                $subject = ['id' => $id, 'nom' => $nom, 'coefficient' => $coeff, 'groupe' => $groupe, 'subject_group_id' => $subject_group_id, 'teaching_type_id' => $teaching_type_id, 'code_uv' => $code_uv, 'code_ue' => $code_ue, 'vhm' => $vhm, 'vhp' => $vhp, 'th_max' => $th_max, 'observations' => $observations];
                $assigned_classes = $classes_ids;
                $classes = $this->db->query("SELECT c.id, c.nom, c.teaching_type_id FROM classes c LEFT JOIN departments d ON c.department_id = d.id LEFT JOIN cycles cy ON c.cycle_id = cy.id LEFT JOIN sections sec ON c.section_id = sec.id LEFT JOIN teaching_types tt ON c.teaching_type_id = tt.id WHERE (c.department_id IS NULL OR d.status = 1) AND (c.cycle_id IS NULL OR cy.status = 1) AND (c.section_id IS NULL OR sec.status = 1) AND (c.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY c.nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $teachingTypes = $this->db->query("SELECT id, nom, code FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $academicYears = $this->db->query("SELECT id, nom, is_active FROM academic_years ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
                $deptQuery = "SELECT d.id, d.nom, d.teaching_type_id FROM departments d LEFT JOIN teaching_types tt ON d.teaching_type_id = tt.id WHERE d.status = 1 AND (d.teaching_type_id IS NULL OR tt.actif = 1) ORDER BY d.nom ASC";
                $departments = $this->db->query($deptQuery)->fetchAll(PDO::FETCH_ASSOC);
                $subjectGroups = $this->db->query("SELECT id, libelle, teaching_type_id FROM subject_groups WHERE status = 1 ORDER BY libelle ASC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/subjects/edit.php';
                return;
            }

            try {
                $this->db->beginTransaction();

                $stmt = $this->db->prepare("UPDATE subjects SET nom = ?, coefficient = ?, groupe = ?, subject_group_id = ?, teaching_type_id = ?, code_uv = ?, code_ue = ?, vhm = ?, vhp = ?, th_max = ?, observations = ? WHERE id = ?");
                $stmt->execute([$nom, $coeff, $groupe, $subject_group_id, $teaching_type_id, $code_uv, $code_ue, $vhm, $vhp, $th_max, $observations, $id]);

                $stmt_del = $this->db->prepare("DELETE FROM subject_classes WHERE subject_id = ? AND academic_year_id = ?");
                $stmt_del->execute([$id, $academicYearId]);

                $stmt_ins = $this->db->prepare("INSERT INTO subject_classes (subject_id, class_id, academic_year_id) VALUES (?, ?, ?)");
                foreach ($classes_ids as $cid) {
                    $stmt_ins->execute([$id, (int) $cid, $academicYearId]);
                }

                // Mise à jour des compétences
                $competencies = $_POST['competencies'] ?? [];
                
                // Supprimer les compétences existantes de cette matière
                $delCompStmt = $this->db->prepare("DELETE FROM competencies WHERE subject_id = ?");
                $delCompStmt->execute([$id]);
                
                // Réinsérer les nouvelles compétences
                if (!empty($competencies) && is_array($competencies)) {
                    $compStmt = $this->db->prepare("INSERT INTO competencies (subject_id, libelle, position, created_by) VALUES (?, ?, ?, ?)");
                    $userId = (int) Session::get('user_id');
                    foreach ($competencies as $index => $libelle) {
                        $libelle = trim($libelle);
                        if (!empty($libelle)) {
                            $compStmt->execute([$id, $libelle, $index + 1, $userId]);
                        }
                    }
                }

                $this->db->commit();
                Session::setFlash('success', __('subject_updated_success'));
                header("Location: /subjects");
                exit;
            } catch (\PDOException $e) {
                $this->db->rollBack();
                $error = \__('server_error_subject_update');
                $subject = ['id' => $id, 'nom' => $nom, 'coefficient' => $coeff, 'groupe' => $groupe, 'teaching_type_id' => $teaching_type_id, 'vhm' => $vhm, 'vhp' => $vhp, 'th_max' => $th_max, 'observations' => $observations];
                $assigned_classes = $classes_ids;
                $classes = $this->db->query("SELECT id, nom FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                $academicYears = $this->db->query("SELECT id, nom, is_active FROM academic_years ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
                include __DIR__ . '/../Views/subjects/edit.php';
            }
        }
    }

    public function toggleStatus($id)
    {
        if (Session::get('user_role') !== 'superadmin') {
            Session::setFlash('error', __('access_denied_superadmin_only'));
            header("Location: /subjects");
            exit;
        }

        $stmt = $this->db->prepare("UPDATE subjects SET status = 1 - status WHERE id = ?");
        if ($stmt->execute([$id])) {
            Session::setFlash('success', __('status_updated_success'));
        } else {
            Session::setFlash('error', __('status_update_failed'));
        }
        header("Location: /subjects");
        exit;
    }

    public function delete($id)
    {
        \App\Core\PermissionManager::requirePermission('manage_subjects');
        $stmt = $this->db->prepare("DELETE FROM subjects WHERE id = ?");
        if ($stmt->execute([$id])) {
            Session::setFlash('success', __('subject_deleted_success'));
        } else {
            Session::setFlash('error', __('subject_delete_failed'));
        }
        header("Location: /subjects");
        exit;
    }

    public function import(): void
    {
        include __DIR__ . '/../Views/subjects/import.php';
    }

    public function downloadTemplate(): void
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        ini_set('memory_limit', '512M');
        $lang = Session::get('app_lang', 'fr') === 'en' ? 'en' : 'fr';

        try {
            $svc = new ExcelTemplateService($this->db);
            $content = $svc->generateSubjectTemplate($lang);
            $filename = $lang === 'fr' ? 'Modele_Import_Matieres_FR.xlsx' : 'Subject_Import_Template_EN.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            echo $content;
            exit;
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
            header('Location: /subjects/import');
            exit;
        }
    }

    public function upload(): void
    {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['import_file'])) {
            if ($isAjax) {
                header('Content-Type: application/json', true, 400);
                echo json_encode(['success' => false, 'message' => __('invalid_request')]);
                exit;
            }
            header('Location: /subjects/import');
            exit;
        }

        if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $errMsg = __('session_expired_retry') ?? 'Session expirée ou requête invalide.';
            if ($isAjax) {
                header('Content-Type: application/json', true, 400);
                echo json_encode(['success' => false, 'message' => $errMsg]);
                exit;
            }
            Session::setFlash('error', $errMsg);
            header('Location: /subjects/import');
            exit;
        }

        $file = $_FILES['import_file'];
        $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'xlsx') {
            $errMsg = __('invalid_file_format_excel');
            if ($isAjax) {
                header('Content-Type: application/json', true, 400);
                echo json_encode(['success' => false, 'message' => $errMsg]);
                exit;
            }
            Session::setFlash('error', $errMsg);
            header('Location: /subjects/import');
            exit;
        }

        $processor = new SubjectImportProcessor($this->db);
        $result = $processor->process((string) $file['tmp_name']);

        if ($result['success']) {
            $successMsg = __('subjects_imported_success', ['count' => $result['count']]);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $successMsg, 'count' => $result['count']]);
                exit;
            }
            Session::setFlash('success', $successMsg);
            header('Location: /subjects');
            exit;
        }

        $errors = $result['errors'];
        if ($isAjax) {
            header('Content-Type: application/json', true, 400);
            echo json_encode([
                'success' => false,
                'message' => implode("<br>", array_map('htmlspecialchars', $errors)),
                'errors' => $errors
            ]);
            exit;
        }

        include __DIR__ . '/../Views/subjects/import.php';
    }

    private function fetchSubjectsFromFilters($limit = null, $offset = null)
    {
        $search = trim($_GET['q'] ?? '');
        $classId = (int) ($_GET['class_id'] ?? 0);
        $teachingTypeId = (int) ($_GET['teaching_type_id'] ?? 0);
        $departmentId = (int) ($_GET['department_id'] ?? 0);

        // 1. Count total
        $countSql = "SELECT COUNT(*) FROM subjects s LEFT JOIN teaching_types tt ON s.teaching_type_id = tt.id WHERE (tt.actif = 1 OR s.teaching_type_id IS NULL)";
        $countParams = [];
        if ($search !== '') {
            $countSql .= " AND s.nom LIKE ?";
            $countParams[] = '%' . $search . '%';
        }
        if ($classId > 0) {
            $countSql .= " AND EXISTS (SELECT 1 FROM subject_classes sc2 WHERE sc2.subject_id = s.id AND sc2.class_id = ?)";
            $countParams[] = $classId;
        }
        if ($teachingTypeId > 0) {
            $countSql .= " AND s.teaching_type_id = ?";
            $countParams[] = $teachingTypeId;
        }
        if ($departmentId > 0) {
            $countSql .= " AND EXISTS (SELECT 1 FROM subject_classes sc3 JOIN classes c3 ON c3.id = sc3.class_id WHERE sc3.subject_id = s.id AND c3.department_id = ?)";
            $countParams[] = $departmentId;
        }

        if (Session::get('user_role') !== 'superadmin') {
            $countSql .= " AND s.status = 1";
        }
        $stmtCount = $this->db->prepare($countSql);
        $stmtCount->execute($countParams);
        $totalCount = (int) $stmtCount->fetchColumn();

        // 2. Fetch data
        $sql = "SELECT s.*, tt.nom as teaching_type_nom, sg.libelle as subject_group_libelle, GROUP_CONCAT(c.nom SEPARATOR ', ') as classes_list
                FROM subjects s
                LEFT JOIN subject_classes sc ON s.id = sc.subject_id
                LEFT JOIN classes c ON sc.class_id = c.id
                LEFT JOIN teaching_types tt ON s.teaching_type_id = tt.id
                LEFT JOIN subject_groups sg ON s.subject_group_id = sg.id
                WHERE (tt.actif = 1 OR s.teaching_type_id IS NULL)";
        $params = [];

        if ($search !== '') {
            $sql .= " AND s.nom LIKE ?";
            $params[] = '%' . $search . '%';
        }

        if ($classId > 0) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM subject_classes sc2
                WHERE sc2.subject_id = s.id AND sc2.class_id = ?
            )";
            $params[] = $classId;
        }

        if ($teachingTypeId > 0) {
            $sql .= " AND s.teaching_type_id = ?";
            $params[] = $teachingTypeId;
        }

        if ($departmentId > 0) {
            $sql .= " AND EXISTS (SELECT 1 FROM subject_classes sc3 JOIN classes c3 ON c3.id = sc3.class_id WHERE sc3.subject_id = s.id AND c3.department_id = ?)";
            $params[] = $departmentId;
        }

        if (Session::get('user_role') !== 'superadmin') {
            $sql .= " AND s.status = 1";
        }

        $sql .= " GROUP BY s.id ORDER BY s.nom ASC";

        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [$data, ['q' => $search, 'class_id' => $classId, 'teaching_type_id' => $teachingTypeId, 'department_id' => $departmentId], $totalCount];
    }

    private function findDuplicateClassesForSubjectName(string $nom, array $classIds, ?int $excludeSubjectId = null): array
    {
        $classIds = array_values(array_unique(array_filter(array_map('intval', $classIds))));
        if ($nom === '' || empty($classIds)) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($classIds), '?'));
        $sql = "SELECT DISTINCT c.nom
                FROM subject_classes sc
                JOIN subjects s ON s.id = sc.subject_id
                JOIN classes c ON c.id = sc.class_id
                WHERE sc.class_id IN ($placeholders)
                  AND LOWER(TRIM(s.nom)) = LOWER(TRIM(?))";
        $params = array_merge($classIds, [$nom]);

        if ($excludeSubjectId !== null) {
            $sql .= " AND s.id <> ?";
            $params[] = $excludeSubjectId;
        }

        $sql .= " ORDER BY c.nom ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }
}
