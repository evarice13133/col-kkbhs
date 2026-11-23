<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Core\PermissionManager;
use App\Services\AcademicYearService;
use App\Services\FinancialService;
use PDO;

/**
 * DiscountController
 * 
 * Gère la création, modification et suppression des réductions (individuelles et collectives).
 */
class DiscountController
{
    private PDO $db;
    private AcademicYearService $academicYearService;
    private FinancialService $financialService;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->academicYearService = new AcademicYearService($this->db);
        $this->financialService = new FinancialService($this->db);

        // Sécurité RBAC : Accès réservé aux rôles financiers
        PermissionManager::requirePermission('manage_discounts');
    }

    /**
     * Affiche la liste des réductions.
     */
    public function index()
    {
        $activeYearId = $this->academicYearService->getActiveYearId();
        $activeTeachingTypeId = (new \App\Services\SettingsStore($this->db))->getDefaultTeachingTypeId();

        // 1. Liste des réductions individuelles actives/inactives
        $studentDiscounts = $this->db->query("
            SELECT sd.*, s.nom as student_nom, s.prenom as student_prenom, s.email as matricule, c.nom as classe_nom, COALESCE(dt.name, sd.motive) as motive
            FROM student_discounts sd
            JOIN students s ON sd.student_id = s.id
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN discount_types dt ON sd.discount_type_id = dt.id
            WHERE s.is_withdrawn = 0
            ORDER BY sd.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // 2. Liste des réductions collectives (classes)
        $classDiscounts = $this->db->query("
            SELECT cd.*, c.nom as classe_nom, COALESCE(dt.name, cd.motive) as motive
            FROM class_discounts cd
            JOIN classes c ON cd.class_id = c.id
            LEFT JOIN discount_types dt ON cd.discount_type_id = dt.id
            ORDER BY cd.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // 3. Charger les élèves et les classes pour alimenter le formulaire de création
        $studentsStmt = $this->db->prepare("
            SELECT s.id, s.nom, s.prenom, s.email as matricule, c.nom as classe_nom 
            FROM students s
            JOIN classes c ON s.class_id = c.id
            JOIN teaching_types tt ON tt.id = c.teaching_type_id AND tt.actif = 1
            WHERE s.is_withdrawn = 0
              AND s.actif = 1
              AND s.status = 'Inscrit'
              AND s.academic_year_id = ?
              AND c.teaching_type_id = ?
            ORDER BY s.nom ASC, s.prenom ASC
        ");
        $studentsStmt->execute([$activeYearId, $activeTeachingTypeId]);
        $students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);

        $eligibleClassCriteria = "
            c.status = 1
            AND c.teaching_type_id = ?
            AND EXISTS (
                SELECT 1 FROM students s
                WHERE s.class_id = c.id
                  AND s.academic_year_id = ?
                  AND s.status = 'Inscrit'
                  AND s.actif = 1
                  AND s.is_withdrawn = 0
            )
        ";
        $classesStmt = $this->db->prepare("
            SELECT c.id, c.nom, c.teaching_type_id, c.section_id, c.cycle_id
            FROM classes c
            WHERE $eligibleClassCriteria
            ORDER BY c.nom ASC
        ");
        $classesStmt->execute([$activeTeachingTypeId, $activeYearId]);
        $classes = $classesStmt->fetchAll(PDO::FETCH_ASSOC);

        $teachingTypesStmt = $this->db->prepare("
            SELECT id, nom FROM teaching_types
            WHERE actif = 1 AND id = ?
            ORDER BY position ASC, nom ASC
        ");
        $teachingTypesStmt->execute([$activeTeachingTypeId]);
        $teachingTypes = $teachingTypesStmt->fetchAll(PDO::FETCH_ASSOC);

        $sectionsStmt = $this->db->prepare("
            SELECT DISTINCT sec.id, sec.nom
            FROM sections sec
            JOIN classes c ON c.section_id = sec.id
            WHERE $eligibleClassCriteria
            ORDER BY sec.nom ASC
        ");
        $sectionsStmt->execute([$activeTeachingTypeId, $activeYearId]);
        $sections = $sectionsStmt->fetchAll(PDO::FETCH_ASSOC);

        $cyclesStmt = $this->db->prepare("
            SELECT DISTINCT cy.id, cy.nom
            FROM cycles cy
            JOIN classes c ON c.cycle_id = cy.id
            WHERE $eligibleClassCriteria
            ORDER BY cy.nom ASC
        ");
        $cyclesStmt->execute([$activeTeachingTypeId, $activeYearId]);
        $cycles = $cyclesStmt->fetchAll(PDO::FETCH_ASSOC);

        $discountTypes = (new \App\Models\DiscountType())->getAllActive();

        include __DIR__ . '/../Views/discounts/index.php';
    }

    /**
     * Enregistre une réduction.
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('error', "Session expirée ou requête invalide.");
                header("Location: /discounts");
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
            $activeTeachingTypeId = (new \App\Services\SettingsStore($this->db))->getDefaultTeachingTypeId();

            if ($amount <= 0.0) {
                Session::setFlash('error', "Le montant/pourcentage de la réduction doit être supérieur à 0.");
                header("Location: /discounts");
                exit;
            }

            if ($discount_type_id <= 0) {
                Session::setFlash('error', "Le type de réduction est obligatoire.");
                header("Location: /discounts");
                exit;
            }

            // Fetch name of the discount type to preserve motive compatibility
            $stmtType = $this->db->prepare("SELECT name FROM discount_types WHERE id = ?");
            $stmtType->execute([$discount_type_id]);
            $motive = $stmtType->fetchColumn();

            if (!$motive) {
                Session::setFlash('error', "Type de réduction invalide.");
                header("Location: /discounts");
                exit;
            }

            try {
                $this->db->beginTransaction();

                if ($scope === 'student') {
                    $studentId = (int)$_POST['student_id'];
                    if ($studentId <= 0) {
                        throw new \Exception("Élève invalide.");
                    }

                    $eligibleStudent = $this->db->prepare("
                        SELECT s.id
                        FROM students s
                        JOIN classes c ON c.id = s.class_id
                        JOIN teaching_types tt ON tt.id = c.teaching_type_id AND tt.actif = 1
                        WHERE s.id = ?
                          AND s.academic_year_id = ?
                          AND c.teaching_type_id = ?
                          AND s.status = 'Inscrit'
                          AND s.actif = 1
                          AND s.is_withdrawn = 0
                        LIMIT 1
                    ");
                    $eligibleStudent->execute([$studentId, $activeYearId, $activeTeachingTypeId]);
                    if (!$eligibleStudent->fetchColumn()) {
                        throw new \Exception("L'élève sélectionné n'est pas inscrit dans le type d'enseignement actif pour l'année en cours.");
                    }

                    $stmt = $this->db->prepare("INSERT INTO student_discounts (student_id, discount_type_id, amount, amount_type, motive, date_effet, status, commentaire) 
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
                    $discountId = (int)$this->db->lastInsertId();

                    // Recalculer le solde de cet élève
                    $this->financialService->syncStudentFinancials($studentId, $activeYearId);

                    // Audit
                    $this->financialService->logHistory(
                        Session::get('user_id'),
                        'student_discount',
                        $discountId,
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

                    $eligibleClass = $this->db->prepare("
                        SELECT c.id
                        FROM classes c
                        JOIN teaching_types tt ON tt.id = c.teaching_type_id AND tt.actif = 1
                        WHERE c.id = ?
                          AND c.status = 1
                          AND c.teaching_type_id = ?
                          AND EXISTS (
                              SELECT 1 FROM students s
                              WHERE s.class_id = c.id
                                AND s.academic_year_id = ?
                                AND s.status = 'Inscrit'
                                AND s.actif = 1
                                AND s.is_withdrawn = 0
                          )
                        LIMIT 1
                    ");
                    $eligibleClass->execute([$classId, $activeTeachingTypeId, $activeYearId]);
                    if (!$eligibleClass->fetchColumn()) {
                        throw new \Exception("La classe sélectionnée ne comporte aucun élève inscrit dans le type d'enseignement actif pour l'année en cours.");
                    }

                    $stmt = $this->db->prepare("INSERT INTO class_discounts (class_id, discount_type_id, amount, amount_type, motive, date_effet, status, commentaire) 
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
                    $discountId = (int)$this->db->lastInsertId();

                    // Recalculer le solde de tous les élèves de cette classe
                    $this->financialService->syncClassFinancials($classId, $activeYearId);

                    // Audit
                    $this->financialService->logHistory(
                        Session::get('user_id'),
                        'class_discount',
                        $discountId,
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
                Session::setFlash('success', "Réduction enregistrée et soldes mis à jour.");
            } catch (\Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                Session::setFlash('error', "Erreur lors de la création de la réduction : " . $e->getMessage());
            }

            header("Location: /discounts");
            exit;
        }
    }

    /**
     * Active/désactive une réduction.
     */
    public function toggleStatus($id)
    {
        $id = (int)$id;
        $scope = trim((string)($_GET['scope'] ?? 'student')); // 'student' ou 'class'
        $activeYearId = $this->academicYearService->getActiveYearId();

        try {
            $this->db->beginTransaction();

            if ($scope === 'student') {
                $stmt = $this->db->prepare("SELECT * FROM student_discounts WHERE id = ?");
                $stmt->execute([$id]);
                $discount = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$discount) {
                    throw new \Exception("Réduction introuvable.");
                }

                $newStatus = $discount['status'] === 'active' ? 'inactive' : 'active';
                
                $upd = $this->db->prepare("UPDATE student_discounts SET status = ? WHERE id = ?");
                $upd->execute([$newStatus, $id]);

                // Recalculer le solde de cet élève
                $this->financialService->syncStudentFinancials((int)$discount['student_id'], $activeYearId);

                // Audit
                $this->financialService->logHistory(
                    Session::get('user_id'),
                    'student_discount',
                    $id,
                    'update_status',
                    ['status' => $discount['status']],
                    ['status' => $newStatus]
                );

            } else { // class
                $stmt = $this->db->prepare("SELECT * FROM class_discounts WHERE id = ?");
                $stmt->execute([$id]);
                $discount = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$discount) {
                    throw new \Exception("Réduction introuvable.");
                }

                $newStatus = $discount['status'] === 'active' ? 'inactive' : 'active';

                $upd = $this->db->prepare("UPDATE class_discounts SET status = ? WHERE id = ?");
                $upd->execute([$newStatus, $id]);

                // Recalculer le solde de tous les élèves de cette classe
                $this->financialService->syncClassFinancials((int)$discount['class_id'], $activeYearId);

                // Audit
                $this->financialService->logHistory(
                    Session::get('user_id'),
                    'class_discount',
                    $id,
                    'update_status',
                    ['status' => $discount['status']],
                    ['status' => $newStatus]
                );
            }

            $this->db->commit();
            Session::setFlash('success', "Le statut de la réduction a été modifié.");
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            Session::setFlash('error', "Erreur : " . $e->getMessage());
        }

        header("Location: /discounts");
        exit;
    }

    /**
     * Supprime une réduction.
     */
    public function delete($id)
    {
        $id = (int)$id;
        $scope = trim((string)($_GET['scope'] ?? 'student')); // 'student' ou 'class'
        $activeYearId = $this->academicYearService->getActiveYearId();

        try {
            $this->db->beginTransaction();

            if ($scope === 'student') {
                $stmt = $this->db->prepare("SELECT * FROM student_discounts WHERE id = ?");
                $stmt->execute([$id]);
                $discount = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$discount) {
                    throw new \Exception("Réduction introuvable.");
                }

                $del = $this->db->prepare("DELETE FROM student_discounts WHERE id = ?");
                $del->execute([$id]);

                // Recalculer le solde de cet élève
                $this->financialService->syncStudentFinancials((int)$discount['student_id'], $activeYearId);

                // Audit
                $this->financialService->logHistory(
                    Session::get('user_id'),
                    'student_discount',
                    $id,
                    'delete',
                    $discount,
                    null
                );

            } else { // class
                $stmt = $this->db->prepare("SELECT * FROM class_discounts WHERE id = ?");
                $stmt->execute([$id]);
                $discount = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$discount) {
                    throw new \Exception("Réduction introuvable.");
                }

                $del = $this->db->prepare("DELETE FROM class_discounts WHERE id = ?");
                $del->execute([$id]);

                // Recalculer le solde de tous les élèves de cette classe
                $this->financialService->syncClassFinancials((int)$discount['class_id'], $activeYearId);

                // Audit
                $this->financialService->logHistory(
                    Session::get('user_id'),
                    'class_discount',
                    $id,
                    'delete',
                    $discount,
                    null
                );
            }

            $this->db->commit();
            Session::setFlash('success', "Réduction supprimée et soldes mis à jour.");
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            Session::setFlash('error', "Erreur lors de la suppression : " . $e->getMessage());
        }

        header("Location: /discounts");
        exit;
    }
}
