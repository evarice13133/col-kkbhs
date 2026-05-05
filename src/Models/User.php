<?php

namespace App\Models;

use PDO;

/**
 * Classe User
 * 
 * Représente un utilisateur du système (Admin ou Enseignant).
 * Gère les propriétés de l'utilisateur et la logique d'accès aux données.
 */
class User extends BaseModel {
    /** @var int|null L'ID de l'utilisateur */
    private $id;
    /** @var string|null Le nom de famille */
    private $nom;
    /** @var string|null Le prénom */
    private $prenom;
    /** @var string|null Le nom d'utilisateur unique (login) */
    private $username;
    /** @var string|null L'adresse e-mail (optionnelle) */
    private $email;
    /** @var string|null Le mot de passe haché */
    private $password;
    /** @var string Le rôle de l'utilisateur (superadmin, admin, enseignant) */
    private $role;

    /**
     * Constructeur de User.
     */
    public function __construct($nom = null, $prenom = null, $username = null, $email = null, $password = null, $role = 'enseignant') {
        parent::__construct();
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }

    // --- Méthodes d'accès aux données ---

    /**
     * Recherche un utilisateur par son adresse e-mail.
     * 
     * @param string $email
     * @return array|false Les données de l'utilisateur ou false si non trouvé
     */
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Recherche un utilisateur par son nom d'utilisateur (login).
     * 
     * @param string $username
     * @return array|false
     */
    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Recherche un utilisateur par son ID.
     * 
     * @param int $id
     * @return array|false
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les utilisateurs de la base de données.
     * 
     * @return array
     */
    public function getAll() {
        return $this->db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- Getters & Setters ---

    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getPrenom() { return $this->prenom; }
    public function getUsername() { return $this->username; }
    public function getEmail() { return $this->email; }
    public function getPassword() { return $this->password; }
    public function getRole() { return $this->role; }

    public function setId($id) { $this->id = $id; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setPrenom($prenom) { $this->prenom = $prenom; }
    public function setUsername($username) { $this->username = $username; }
    public function setEmail($email) { $this->email = $email; }
    
    /**
     * Hache le mot de passe de manière sécurisée et l'assigne.
     */
    public function setPassword($password) { 
        $this->password = password_hash($password, PASSWORD_ARGON2ID); 
    }
    
    public function setRole($role) { $this->role = $role; }

    /**
     * Vérifie si le mot de passe fourni correspond au hachage en base de données.
     */
    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }
    
    // --- Utilitaires ---
    public function isSuperAdmin() { return $this->role === 'superadmin'; }
    public function isAdmin() { return $this->role === 'admin' || $this->role === 'superadmin'; }
    public function isEnseignant() { return $this->role === 'enseignant'; }
}
