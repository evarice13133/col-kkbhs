<?php

namespace App\Services;

use App\Core\Database;
use App\Core\PermissionManager;
use App\Services\SettingsStore;
use PDO;

class OnboardingService
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance()->getConnection();
    }

    /**
     * Get real-time onboarding evaluation for current user
     */
    public function getOnboardingState(int $userId, string $userRole): array
    {
        $settingsStore = new SettingsStore($this->db);
        
        // Evaluate DB status indicators
        $hasCustomIdentity = $this->evalCustomIdentity($settingsStore);
        $hasAcademicYears = $this->evalTableCount('academic_years') > 0;
        $hasClasses = $this->evalTableCount('classes') > 0;
        $hasTeachers = $this->evalTeachersCount() > 0;
        $hasStudents = $this->evalTableCount('students') > 0;
        $hasNotes = $this->evalTableCount('notes') > 0;
        $hasPayments = $this->evalPaymentsCount() > 0;

        // Define potential candidate steps
        $allSteps = [
            // Admin & Manager steps
            [
                'id' => 'setup_identity',
                'title' => 'Identité de l\'établissement & Logo',
                'desc' => 'Renseignez le nom, l\'adresse et le logo officiel de votre école.',
                'url' => '/settings',
                'permission' => 'manage_settings',
                'roles' => ['superadmin', 'admin'],
                'target' => '#tourBrandLogo',
                'completed' => $hasCustomIdentity
            ],
            [
                'id' => 'academic_years',
                'title' => 'Année scolaire & Périodes',
                'desc' => 'Configurez l\'année académique en cours et ses trimestres/séquences.',
                'url' => '/academic_years',
                'permission' => 'manage_academic_years',
                'roles' => ['superadmin', 'admin', 'it_manager'],
                'target' => '#desktopNavItems',
                'completed' => $hasAcademicYears
            ],
            [
                'id' => 'setup_classes',
                'title' => 'Structure des classes & Niveaux',
                'desc' => 'Définissez la liste des classes et leurs niveaux scolaires.',
                'url' => '/classes',
                'permission' => 'manage_classes_structure',
                'roles' => ['superadmin', 'admin'],
                'target' => '#desktopNavItems',
                'completed' => $hasClasses
            ],
            [
                'id' => 'setup_teachers',
                'title' => 'Corps enseignant & Matières',
                'desc' => 'Enregistrez les professeurs et attribuez leurs cours.',
                'url' => '/teachers',
                'permission' => 'manage_teachers',
                'roles' => ['superadmin', 'admin', 'it_manager'],
                'target' => '#desktopNavItems',
                'completed' => $hasTeachers
            ],
            [
                'id' => 'register_students',
                'title' => 'Inscrire les premiers élèves',
                'desc' => 'Inscrivez des élèves et affectez-les dans leurs classes respectives.',
                'url' => '/students/create',
                'permission' => 'manage_students',
                'roles' => ['superadmin', 'admin', 'caissier', 'comptable'],
                'target' => '#tourQAT',
                'completed' => $hasStudents
            ],

            // Teacher steps
            [
                'id' => 'teacher_students',
                'title' => 'Annuaire & Suivi de vos élèves',
                'desc' => 'Accédez à la liste et aux fiches individuelles de vos élèves.',
                'url' => '/students',
                'permission' => 'view_students',
                'roles' => ['enseignant', 'teacher'],
                'target' => '#tourQAT',
                'completed' => $hasStudents
            ],
            [
                'id' => 'teacher_marks',
                'title' => 'Saisie & Évaluation des notes',
                'desc' => 'Saisissez et validez les notes d\'évaluation par classe et matière.',
                'url' => '/notes',
                'permission' => 'manage_marks',
                'roles' => ['enseignant', 'teacher'],
                'target' => '#tourQAT',
                'completed' => $hasNotes
            ],
            [
                'id' => 'teacher_timetables',
                'title' => 'Consultation des emplois du temps',
                'desc' => 'Consultez la planification hebdomadaire des cours et salles.',
                'url' => '/timetables',
                'permission' => 'view_timetables',
                'roles' => ['enseignant', 'teacher'],
                'target' => '#tourQAT',
                'completed' => $hasAcademicYears
            ],

            // Cashier / Financial steps
            [
                'id' => 'cashier_fees',
                'title' => 'Grille tarifaire des scolarités',
                'desc' => 'Consultez le barème des frais par classe et tranche.',
                'url' => '/school_fees/grille',
                'permission' => 'view_class_finances',
                'roles' => ['caissier', 'comptable'],
                'target' => '#desktopNavItems',
                'completed' => $hasClasses
            ],
            [
                'id' => 'cashier_payments',
                'title' => 'Enregistrement des versements',
                'desc' => 'Encassez les frais scolaires et délivrez les recibos imprimables.',
                'url' => '/payments',
                'permission' => 'manage_payments',
                'roles' => ['caissier', 'comptable'],
                'target' => '#tourQAT',
                'completed' => $hasPayments
            ],
            [
                'id' => 'cashier_insolvent',
                'title' => 'Suivi des élèves en retard de paiement',
                'desc' => 'Consultez le rapport d\'insolvabilité et relances.',
                'url' => '/school_fees/insolvables',
                'permission' => 'view_financial_reports',
                'roles' => ['caissier', 'comptable'],
                'target' => '#desktopNavItems',
                'completed' => $hasPayments
            ]
        ];

        // Filter steps relevant to the user's role & permissions
        $applicableSteps = array_values(array_filter($allSteps, function ($step) use ($userRole) {
            // Check permission
            if (!empty($step['permission']) && !PermissionManager::hasPermission($step['permission'])) {
                return false;
            }
            // Check role compatibility
            if (!empty($step['roles']) && !in_array($userRole, $step['roles'])) {
                return false;
            }
            return true;
        }));

        // If no specific role steps matched, fallback to default allowed steps
        if (empty($applicableSteps)) {
            $applicableSteps = array_values(array_filter($allSteps, function ($step) {
                return empty($step['permission']) || PermissionManager::hasPermission($step['permission']);
            }));
        }

        $completedCount = count(array_filter($applicableSteps, fn($s) => $s['completed']));
        $totalCount = count($applicableSteps);
        $percentage = $totalCount > 0 ? (int) round(($completedCount / $totalCount) * 100) : 100;

        return [
            'userId' => $userId,
            'userRole' => $userRole,
            'steps' => $applicableSteps,
            'completedCount' => $completedCount,
            'totalCount' => $totalCount,
            'percentage' => $percentage,
            'isComplete' => $percentage >= 100
        ];
    }

    private function evalCustomIdentity(SettingsStore $settingsStore): bool
    {
        $schoolName = trim((string) $settingsStore->get('school_name', ''));
        if ($schoolName !== '' && strtolower($schoolName) !== 'notesmaster' && strtolower($schoolName) !== 'notemaster') {
            return true;
        }

        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM logos");
            if ($stmt && (int)$stmt->fetchColumn() > 0) {
                return true;
            }
        } catch (\Throwable $e) {
        }

        return false;
    }

    private function evalTableCount(string $table): int
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM {$table}");
            return $stmt ? (int)$stmt->fetchColumn() : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function evalTeachersCount(): int
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM teachers");
            $count = $stmt ? (int)$stmt->fetchColumn() : 0;
            if ($count > 0) return $count;

            $stmt2 = $this->db->query("SELECT COUNT(*) FROM users WHERE role IN ('enseignant', 'teacher')");
            return $stmt2 ? (int)$stmt2->fetchColumn() : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function evalPaymentsCount(): int
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM versements");
            $count = $stmt ? (int)$stmt->fetchColumn() : 0;
            if ($count > 0) return $count;

            $stmt2 = $this->db->query("SELECT COUNT(*) FROM payments");
            return $stmt2 ? (int)$stmt2->fetchColumn() : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
