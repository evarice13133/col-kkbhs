<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Services\AcademicYearService;
use App\Services\FinancialService;
use PDO;

/**
 * ScholarshipController
 * 
 * Gère la création, modification et suppression des bourses (individuelles et collectives).
 */
class ScholarshipController
{
    private PDO $db;
    private AcademicYearService $academicYearService;
    private FinancialService $financialService;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->academicYearService = new AcademicYearService($this->db);
        $this->financialService = new FinancialService($this->db);

        // Sécurité : Accès restreint aux administrateurs, caissiers et comptables
        if (!in_array(Session::get('user_role'), ['superadmin', 'admin', 'caissier', 'comptable'])) {
            header("Location: /");
            exit;
        }
    }

    /**
     * Affiche la liste des bourses.
     */
    public function index()
    {
        $activeYearId = $this->academicYearService->getActiveYearId();

        // 1. Liste des bourses individuelles
        $studentScholarships = $this->db->query("
            SELECT ss.*, s.nom as student_nom, s.prenom as student_prenom, s.email as matricule, c.nom as classe_nom, COALESCE(dt.name, ss.motive) as motive
            FROM student_scholarships ss
            JOIN students s ON ss.student_id = s.id
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN discount_types dt ON ss.discount_type_id = dt.id
            WHERE s.is_withdrawn = 0
            ORDER BY ss.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // 2. Liste des bourses collectives (classes)
        $classScholarships = $this->db->query("
            SELECT cs.*, c.nom as classe_nom, COALESCE(dt.name, cs.motive) as motive
            FROM class_scholarships cs
            JOIN classes c ON cs.class_id = c.id
            LEFT JOIN discount_types dt ON cs.discount_type_id = dt.id
            ORDER BY cs.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // 3. Charger les élèves et les classes pour le formulaire
        $students = $this->db->query("
            SELECT s.id, s.nom, s.prenom, s.email as matricule, c.nom as classe_nom 
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE s.is_withdrawn = 0
            ORDER BY s.nom ASC, s.prenom ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $classes = $this->db->query("SELECT id, nom, teaching_type_id, section_id, cycle_id FROM classes ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $teachingTypes = $this->db->query("SELECT id, nom FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $sections = $this->db->query("SELECT id, nom FROM sections ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        $cycles = $this->db->query("SELECT id, nom FROM cycles ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

        $discountTypes = (new \App\Models\DiscountType())->getAllActive();

        include __DIR__ . '/../Views/scholarships/index.php';
    }

    /**
     * Enregistre une bourse.
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', "Session expirée ou requête invalide.");
                header("Location: /scholarships");
                exit;
            }

            $scope = trim((string)($_POST['scope'] ?? 'student')); // 'student' ou 'class'
            $amount = (float)$_POST['amount'];
            $amount_type = trim((string)($_POST['amount_type'] ?? 'fixed')); // 'fixed' ou 'percentage'
            $discount_type_id = (int)($_POST['discount_type_id'] ?? 0);
            $date_effet = trim((string)($_POST['date_effet'] ?? date('Y-m-d')));
            $status = trim((string)($_POST['status'] ?? 'active'));
            $commentaire = trim((string)($_POST['commentaire'] ?? ''));

            $activeYearId = $this->academicYearService->getActiveYearId();

            if ($amount <= 0.0) {
                Session::setFlash('error', "Le montant/pourcentage de la bourse doit être supérieur à 0.");
                header("Location: /scholarships");
                exit;
            }

            if ($discount_type_id <= 0) {
                Session::setFlash('error', "Le type de bourse est obligatoire.");
                header("Location: /scholarships");
                exit;
            }

            // Fetch name of the discount type to preserve motive compatibility
            $stmtType = $this->db->prepare("SELECT name FROM discount_types WHERE id = ?");
            $stmtType->execute([$discount_type_id]);
            $motive = $stmtType->fetchColumn();

            if (!$motive) {
                Session::setFlash('error', "Type de bourse invalide.");
                header("Location: /scholarships");
                exit;
            }

            try {
                $this->db->beginTransaction();

                if ($scope === 'student') {
                    $studentId = (int)$_POST['student_id'];
                    if ($studentId <= 0) {
                        throw new \Exception("Élève invalide.");
                    }

                    $stmt = $this->db->prepare("INSERT INTO student_scholarships (student_id, discount_type_id, amount, amount_type, motive, date_effet, status, commentaire) 
                                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $studentId,
                        $discount_type_id,
                        $amount,
                        $amount_type,
                        $motive,
                        $date_effet,
                        $status,
                        $commentaire ?: null
                    ]);
                    $scholarshipId = (int)$this->db->lastInsertId();

                    // Recalculer le solde de cet élève
                    $this->financialService->syncStudentFinancials($studentId, $activeYearId);

                    // Audit
                    $this->financialService->logHistory(
                        Session::get('user_id'),
                        'student_scholarship',
                        $scholarshipId,
                        'create',
                        null,
                        [
                            'student_id' => $studentId,
                            'discount_type_id' => $discount_type_id,
                            'amount' => $amount,
                            'amount_type' => $amount_type,
                            'motive' => $motive
                        ]
                    );

                } else { // collective (class)
                    $classId = (int)$_POST['class_id'];
                    if ($classId <= 0) {
                        throw new \Exception("Classe invalide.");
                    }

                    $stmt = $this->db->prepare("INSERT INTO class_scholarships (class_id, discount_type_id, amount, amount_type, motive, date_effet, status, commentaire) 
                                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $classId,
                        $discount_type_id,
                        $amount,
                        $amount_type,
                        $motive,
                        $date_effet,
                        $status,
                        $commentaire ?: null
                    ]);
                    $scholarshipId = (int)$this->db->lastInsertId();

                    // Recalculer le solde de tous les élèves de cette classe
                    $this->financialService->syncClassFinancials($classId, $activeYearId);

                    // Audit
                    $this->financialService->logHistory(
                        Session::get('user_id'),
                        'class_scholarship',
                        $scholarshipId,
                        'create',
                        null,
                        [
                            'class_id' => $classId,
                            'discount_type_id' => $discount_type_id,
                            'amount' => $amount,
                            'amount_type' => $amount_type,
                            'motive' => $motive
                        ]
                    );
                }

                $this->db->commit();
                Session::setFlash('success', "Bourse enregistrée et soldes mis à jour.");
            } catch (\Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                Session::setFlash('error', "Erreur lors de la création de la bourse : " . $e->getMessage());
            }

            header("Location: /scholarships");
            exit;
        }
    }

    /**
     * Active/désactive une bourse.
     */
    public function toggleStatus($id)
    {
        $id = (int)$id;
        $scope = trim((string)($_GET['scope'] ?? 'student')); // 'student' ou 'class'
        $activeYearId = $this->academicYearService->getActiveYearId();

        try {
            $this->db->beginTransaction();

            if ($scope === 'student') {
                $stmt = $this->db->prepare("SELECT * FROM student_scholarships WHERE id = ?");
                $stmt->execute([$id]);
                $scholarship = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$scholarship) {
                    throw new \Exception("Bourse introuvable.");
                }

                $newStatus = $scholarship['status'] === 'active' ? 'inactive' : 'active';
                
                $upd = $this->db->prepare("UPDATE student_scholarships SET status = ? WHERE id = ?");
                $upd->execute([$newStatus, $id]);

                // Recalculer le solde de cet élève
                $this->financialService->syncStudentFinancials((int)$scholarship['student_id'], $activeYearId);

                // Audit
                $this->financialService->logHistory(
                    Session::get('user_id'),
                    'student_scholarship',
                    $id,
                    'update_status',
                    ['status' => $scholarship['status']],
                    ['status' => $newStatus]
                );

            } else { // class
                $stmt = $this->db->prepare("SELECT * FROM class_scholarships WHERE id = ?");
                $stmt->execute([$id]);
                $scholarship = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$scholarship) {
                    throw new \Exception("Bourse introuvable.");
                }

                $newStatus = $scholarship['status'] === 'active' ? 'inactive' : 'active';

                $upd = $this->db->prepare("UPDATE class_scholarships SET status = ? WHERE id = ?");
                $upd->execute([$newStatus, $id]);

                // Recalculer le solde de tous les élèves de cette classe
                $this->financialService->syncClassFinancials((int)$scholarship['class_id'], $activeYearId);

                // Audit
                $this->financialService->logHistory(
                    Session::get('user_id'),
                    'class_scholarship',
                    $id,
                    'update_status',
                    ['status' => $scholarship['status']],
                    ['status' => $newStatus]
                );
            }

            $this->db->commit();
            Session::setFlash('success', "Le statut de la bourse a été modifié.");
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            Session::setFlash('error', "Erreur : " . $e->getMessage());
        }

        header("Location: /scholarships");
        exit;
    }

    /**
     * Supprime une bourse.
     */
    public function delete($id)
    {
        $id = (int)$id;
        $scope = trim((string)($_GET['scope'] ?? 'student')); // 'student' ou 'class'
        $activeYearId = $this->academicYearService->getActiveYearId();

        try {
            $this->db->beginTransaction();

            if ($scope === 'student') {
                $stmt = $this->db->prepare("SELECT * FROM student_scholarships WHERE id = ?");
                $stmt->execute([$id]);
                $scholarship = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$scholarship) {
                    throw new \Exception("Bourse introuvable.");
                }

                $del = $this->db->prepare("DELETE FROM student_scholarships WHERE id = ?");
                $del->execute([$id]);

                // Recalculer le solde de cet élève
                $this->financialService->syncStudentFinancials((int)$scholarship['student_id'], $activeYearId);

                // Audit
                $this->financialService->logHistory(
                    Session::get('user_id'),
                    'student_scholarship',
                    $id,
                    'delete',
                    $scholarship,
                    null
                );

            } else { // class
                $stmt = $this->db->prepare("SELECT * FROM class_scholarships WHERE id = ?");
                $stmt->execute([$id]);
                $scholarship = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$scholarship) {
                    throw new \Exception("Bourse introuvable.");
                }

                $del = $this->db->prepare("DELETE FROM class_scholarships WHERE id = ?");
                $del->execute([$id]);

                // Recalculer le solde de tous les élèves de cette classe
                $this->financialService->syncClassFinancials((int)$scholarship['class_id'], $activeYearId);

                // Audit
                $this->financialService->logHistory(
                    Session::get('user_id'),
                    'class_scholarship',
                    $id,
                    'delete',
                    $scholarship,
                    null
                );
            }

            $this->db->commit();
            Session::setFlash('success', "Bourse supprimée et soldes mis à jour.");
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            Session::setFlash('error', "Erreur lors de la suppression : " . $e->getMessage());
        }

        header("Location: /scholarships");
        exit;
    }
}
