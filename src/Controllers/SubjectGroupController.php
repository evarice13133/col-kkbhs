<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Core\PermissionManager;
use PDO;

/**
 * SubjectGroupController
 * 
 * Gestion des groupes de modules / matières.
 * Accès réservé aux administrateurs (manage_subjects ou manage_cycles).
 */
class SubjectGroupController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        if (!Session::isLogged()) {
            header("Location: /login");
            exit;
        }
        PermissionManager::requirePermission('manage_subjects');
    }

    /**
     * Liste tous les groupes de modules
     */
    public function index()
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        $teaching_type_id = !empty($_GET['teaching_type_id']) ? (int) $_GET['teaching_type_id'] : null;

        // Détecter si la colonne teaching_form_id existe (migration peut ne pas avoir encore été exécutée)
        $colCheck = $this->db->query("SHOW COLUMNS FROM subject_groups LIKE 'teaching_form_id'")->fetchColumn();
        $hasTeachingFormCol = !empty($colCheck);

        $params = [];
        if ($hasTeachingFormCol) {
                 // Sélectionne la forme associée (si existante) et le type via la colonne dédiée pour affichage
                 // Affiche uniquement les groupes dont le Type d'enseignement est actif. Si une forme est rattachée elle doit être active.
                 $sql = "SELECT sg.*, tf.nom as teaching_form_nom, tf.id as teaching_form_id, tt.nom as teaching_type_nom, tt.code as teaching_type_code,
                          (SELECT COUNT(*) FROM subjects s WHERE s.subject_group_id = sg.id) as subjects_count
                      FROM subject_groups sg
                      LEFT JOIN teaching_forms tf ON sg.teaching_form_id = tf.id
                      LEFT JOIN teaching_types tt ON sg.teaching_type_id = tt.id
                      WHERE tt.actif = 1 AND (tf.status = 1 OR sg.teaching_form_id IS NULL)";

            if ($q !== '') {
                $sql .= " AND LOWER(sg.libelle) LIKE ?";
                $params[] = '%' . strtolower($q) . '%';
            }
            if ($teaching_type_id) {
                // Filtre par type d'enseignement via la colonne teaching_type_id
                $sql .= " AND sg.teaching_type_id = ?";
                $params[] = $teaching_type_id;
            }

            $sql .= " ORDER BY sg.libelle ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
                 // Mode compatibilité si la migration n'a pas encore été appliquée
                 // Affiche uniquement les groupes dont le Type d'enseignement est actif (compatibilité legacy)
                 $sql = "SELECT sg.*, tt.nom as teaching_type_nom, tt.code as teaching_type_code,
                          (SELECT COUNT(*) FROM subjects s WHERE s.subject_group_id = sg.id) as subjects_count
                      FROM subject_groups sg
                      LEFT JOIN teaching_types tt ON sg.teaching_type_id = tt.id
                      WHERE tt.actif = 1";

            if ($q !== '') {
                $sql .= " AND LOWER(sg.libelle) LIKE ?";
                $params[] = '%' . strtolower($q) . '%';
            }
            if ($teaching_type_id) {
                $sql .= " AND sg.teaching_type_id = ?";
                $params[] = $teaching_type_id;
            }

            $sql .= " ORDER BY sg.libelle ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $teachingTypes = $this->db->query("SELECT id, nom, code FROM teaching_types WHERE actif = 1 ORDER BY position ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        // Formes d'enseignement actives pour la modale (si la table existe)
        $tfTableCheck = $this->db->query("SHOW TABLES LIKE 'teaching_forms'")->fetchColumn();
        if ($tfTableCheck) {
            $teachingForms = $this->db->query("SELECT id, nom, teaching_type_id FROM teaching_forms WHERE status = 1 ORDER BY teaching_type_id ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $teachingForms = [];
        }

        $filters = ['q' => $q, 'teaching_type_id' => $teaching_type_id];

        include __DIR__ . '/../Views/subject_groups/index.php';
    }

    /**
     * Crée un groupe de modules
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $libelle = trim($_POST['libelle'] ?? '');
            $teaching_form_id = !empty($_POST['teaching_form_id']) ? (int) $_POST['teaching_form_id'] : null;

            // Vérifier si la colonne teaching_form_id est présente
            $colCheck = $this->db->query("SHOW COLUMNS FROM subject_groups LIKE 'teaching_form_id'")->fetchColumn();
            $hasTeachingFormCol = !empty($colCheck);

            // Valider les champs: la forme vient en addition du type
            $teaching_type_id = !empty($_POST['teaching_type_id']) ? (int) $_POST['teaching_type_id'] : null;

            if ($hasTeachingFormCol) {
                // Exiger à la fois le type et la forme (forme obligatoire selon règle métier)
                if (empty($libelle) || empty($teaching_type_id) || empty($teaching_form_id)) {
                    Session::setFlash('error', __('fill_required_fields') ?? 'Veuillez remplir les champs obligatoires.');
                    header("Location: /subject-groups");
                    exit;
                }

                // Vérifier que le teaching_type est actif
                $ttStmt = $this->db->prepare("SELECT id FROM teaching_types WHERE id = ? AND actif = 1 LIMIT 1");
                $ttStmt->execute([$teaching_type_id]);
                if (!$ttStmt->fetchColumn()) {
                    Session::setFlash('error', __('invalid_teaching_type') ?? 'Type d\'enseignement invalide.');
                    header("Location: /subject-groups");
                    exit;
                }

                // Vérifier que la forme existe et est active
                $tfStmt = $this->db->prepare("SELECT id FROM teaching_forms WHERE id = ? AND status = 1 LIMIT 1");
                $tfStmt->execute([$teaching_form_id]);
                if (!$tfStmt->fetchColumn()) {
                    Session::setFlash('error', __('invalid_teaching_form') ?? 'Forme d\'enseignement invalide.');
                    header("Location: /subject-groups");
                    exit;
                }
            } else {
                // Avant migration : exiger au minimum le libellé et le type si fourni
                if (empty($libelle)) {
                    Session::setFlash('error', __('fill_required_fields') ?? 'Veuillez remplir les champs obligatoires.');
                    header("Location: /subject-groups");
                    exit;
                }
            }

            // Insérer : si la colonne teaching_form_id existe, l'inclure, sinon garder l'insertion legacy
            if ($hasTeachingFormCol) {
                $stmt = $this->db->prepare("INSERT INTO subject_groups (libelle, teaching_type_id, teaching_form_id, status) VALUES (?, ?, ?, 1)");
                $stmt->execute([$libelle, $teaching_type_id, $teaching_form_id]);
            } else {
                $stmt = $this->db->prepare("INSERT INTO subject_groups (libelle, teaching_type_id, status) VALUES (?, ?, 1)");
                $stmt->execute([$libelle, $teaching_type_id]);
            }

            Session::setFlash('success', __('created_success'));
            header("Location: /subject-groups");
            exit;
        }
    }

    /**
     * Met à jour un groupe de modules
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) $id;
            $libelle = trim($_POST['libelle'] ?? '');
            $teaching_form_id = !empty($_POST['teaching_form_id']) ? (int) $_POST['teaching_form_id'] : null;
            $status = isset($_POST['status']) ? 1 : 0;

            // Vérifier si la colonne teaching_form_id est présente
            $colCheck = $this->db->query("SHOW COLUMNS FROM subject_groups LIKE 'teaching_form_id'")->fetchColumn();
            $hasTeachingFormCol = !empty($colCheck);

            // Valider les champs: la forme vient en addition du type
            $teaching_type_id = !empty($_POST['teaching_type_id']) ? (int) $_POST['teaching_type_id'] : null;

            if ($hasTeachingFormCol) {
                // Exiger à la fois le type et la forme
                if ($id <= 0 || empty($libelle) || empty($teaching_type_id) || empty($teaching_form_id)) {
                    Session::setFlash('error', __('fill_required_fields') ?? 'Veuillez remplir les champs obligatoires.');
                    header("Location: /subject-groups");
                    exit;
                }

                // Vérifier que le teaching_type est actif
                $ttStmt = $this->db->prepare("SELECT id FROM teaching_types WHERE id = ? AND actif = 1 LIMIT 1");
                $ttStmt->execute([$teaching_type_id]);
                if (!$ttStmt->fetchColumn()) {
                    Session::setFlash('error', __('invalid_teaching_type') ?? 'Type d\'enseignement invalide.');
                    header("Location: /subject-groups");
                    exit;
                }

                // Vérifier que la forme existe et est active
                $tfStmt = $this->db->prepare("SELECT id FROM teaching_forms WHERE id = ? AND status = 1 LIMIT 1");
                $tfStmt->execute([$teaching_form_id]);
                if (!$tfStmt->fetchColumn()) {
                    Session::setFlash('error', __('invalid_teaching_form') ?? 'Forme d\'enseignement invalide.');
                    header("Location: /subject-groups");
                    exit;
                }
            } else {
                if ($id <= 0 || empty($libelle)) {
                    Session::setFlash('error', __('fill_required_fields') ?? 'Veuillez remplir les champs obligatoires.');
                    header("Location: /subject-groups");
                    exit;
                }
            }

            // Mettre à jour selon la présence de la colonne teaching_form_id
            if ($hasTeachingFormCol) {
                $stmt = $this->db->prepare("UPDATE subject_groups SET libelle = ?, teaching_type_id = ?, teaching_form_id = ?, status = ? WHERE id = ?");
                $stmt->execute([$libelle, $teaching_type_id, $teaching_form_id, $status, $id]);
            } else {
                $stmt = $this->db->prepare("UPDATE subject_groups SET libelle = ?, teaching_type_id = ?, status = ? WHERE id = ?");
                $stmt->execute([$libelle, $teaching_type_id, $status, $id]);
            }

            Session::setFlash('success', __('updated_success'));
            header("Location: /subject-groups");
            exit;
        }
    }

    /**
     * Active / Désactive un groupe de modules
     */
    public function toggle($id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            Session::setFlash('error', __('invalid_id') ?? 'Identifiant invalide.');
            header("Location: /subject-groups");
            exit;
        }

        $stmt = $this->db->prepare("UPDATE subject_groups SET status = NOT status WHERE id = ?");
        $stmt->execute([$id]);

        Session::setFlash('success', __('status_updated_success'));
        header("Location: /subject-groups");
        exit;
    }

    /**
     * Supprime un groupe de modules
     */
    public function delete($id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            Session::setFlash('error', __('invalid_id') ?? 'Identifiant invalide.');
            header("Location: /subject-groups");
            exit;
        }

        // Vérifier s'il y a des matières rattachées
        $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM subjects WHERE subject_group_id = ?");
        $stmtCount->execute([$id]);
        if ((int) $stmtCount->fetchColumn() > 0) {
            Session::setFlash('error', __('cannot_delete_group_has_subjects') ?? 'Impossible de supprimer ce groupe car des matières y sont rattachées.');
            header("Location: /subject-groups");
            exit;
        }

        $stmt = $this->db->prepare("DELETE FROM subject_groups WHERE id = ?");
        $stmt->execute([$id]);

        Session::setFlash('success', __('deleted_success'));
        header("Location: /subject-groups");
        exit;
    }
}
