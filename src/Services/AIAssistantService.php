<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Session;

/**
 * Service d'Assistant IA pour NotesMaster
 * Analyse les questions des utilisateurs et fournit des réponses contextuelles
 */
class AIAssistantService
{
    private $db;
    private $userRole;
    private $indexData;
    private $synonyms;
    private $stopWords;
    private $appLang;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->userRole = Session::get('user_role', 'guest');
        $this->appLang = Session::get('app_lang', 'fr');
        $this->indexData = $this->buildSystemIndex();
        $this->synonyms = $this->buildSynonymsDictionary();
        $this->stopWords = $this->buildStopWords();
    }

    /**
     * Construit l'index des données système
     */
    private function buildSystemIndex()
    {
        $index = [
            'translations' => $this->indexTranslations(),
            'navigation' => $this->indexNavigation(),
            'routes' => $this->indexRoutes(),
            'features' => $this->indexFeatures(),
            'help_topics' => $this->indexHelpTopics()
        ];

        return $index;
    }

    /**
     * Indexe les traductions i18n
     */
    private function indexTranslations()
    {
        $translations = [];
        $lang = Session::get('app_lang', 'fr');
        $i18nFile = __DIR__ . '/../../i18n/' . $lang . '.php';

        if (file_exists($i18nFile)) {
            $translations = include $i18nFile;
        }

        return $translations;
    }

    /**
     * Indexe la structure de navigation
     */
    private function indexNavigation()
    {
        return [
            'dashboard' => [
                'label' => 'Tableau de bord',
                'url' => '/',
                'description' => 'Vue d\'ensemble de l\'application',
                'keywords' => ['accueil', 'principal', 'vue', 'overview', 'home']
            ],
            'students' => [
                'label' => 'Élèves',
                'url' => '/students',
                'description' => 'Gestion des élèves',
                'keywords' => ['étudiant', 'inscription', 'élève', 'student', 'registration']
            ],
            'teachers' => [
                'label' => 'Enseignants',
                'url' => '/teachers',
                'description' => 'Gestion des enseignants',
                'keywords' => ['professeur', 'enseignant', 'teacher', 'prof']
            ],
            'classes' => [
                'label' => 'Classes',
                'url' => '/classes',
                'description' => 'Gestion des classes',
                'keywords' => ['salle', 'classe', 'room', 'classroom']
            ],
            'subjects' => [
                'label' => 'Matières',
                'url' => '/subjects',
                'description' => 'Gestion des matières',
                'keywords' => ['discipline', 'matière', 'subject', 'course']
            ],
            'grades' => [
                'label' => 'Notes',
                'url' => '/notes',
                'description' => 'Saisie et gestion des notes',
                'keywords' => ['note', 'évaluation', 'grade', 'mark', 'score']
            ],
            'bulletins' => [
                'label' => 'Bulletins',
                'url' => '/bulletins',
                'description' => 'Génération des bulletins',
                'keywords' => ['bulletin', 'relevé', 'report', 'transcript']
            ],
            'payments' => [
                'label' => 'Paiements',
                'url' => '/payments',
                'description' => 'Gestion des paiements',
                'keywords' => ['paiement', 'frais', 'payment', 'fee', 'money']
            ],
            'settings' => [
                'label' => 'Paramètres',
                'url' => '/settings',
                'description' => 'Configuration de l\'application',
                'keywords' => ['configuration', 'paramètre', 'setting', 'config']
            ],
            'users' => [
                'label' => 'Utilisateurs',
                'url' => '/users',
                'description' => 'Gestion des utilisateurs',
                'keywords' => ['utilisateur', 'compte', 'user', 'account']
            ],
            'academic_years' => [
                'label' => 'Années Académiques',
                'url' => '/academic_years',
                'description' => 'Gestion des années scolaires',
                'keywords' => ['année', 'scolaire', 'year', 'academic']
            ],
            'sequences' => [
                'label' => 'Évaluations',
                'url' => '/sequences',
                'description' => 'Gestion des séquences d\'évaluation',
                'keywords' => ['séquence', 'évaluation', 'trimestre', 'sequence', 'term']
            ],
            'cycles' => [
                'label' => 'Cycles Académiques',
                'url' => '/cycles',
                'description' => 'Gestion des cycles (primaire, secondaire, etc.)',
                'keywords' => ['cycle', 'niveau', 'level', 'primary', 'secondary']
            ],
            'sections' => [
                'label' => 'Sections',
                'url' => '/sections',
                'description' => 'Gestion des sections académiques',
                'keywords' => ['section', 'filière', 'stream']
            ],
            'departments' => [
                'label' => 'Départements',
                'url' => '/departments',
                'description' => 'Gestion des départements',
                'keywords' => ['département', 'department']
            ],
            'documentation' => [
                'label' => 'Aide',
                'url' => '/documentation',
                'description' => 'Documentation et aide',
                'keywords' => ['aide', 'help', 'documentation', 'support', 'guide']
            ],
            'profile' => [
                'label' => 'Mon Profil',
                'url' => '/profile',
                'description' => 'Gestion du profil utilisateur',
                'keywords' => ['profil', 'mon compte', 'profile', 'my account']
            ],
            'financial_history' => [
                'label' => 'Historique Financier',
                'url' => '/financial-history',
                'description' => 'Historique des transactions financières',
                'keywords' => ['historique', 'financier', 'transaction', 'history', 'financial']
            ],
            'discounts' => [
                'label' => 'Réductions',
                'url' => '/discounts',
                'description' => 'Gestion des réductions et bourses',
                'keywords' => ['réduction', 'bourse', 'discount', 'scholarship']
            ],
            'school_fees' => [
                'label' => 'Frais Scolaires',
                'url' => '/school_fees/grille',
                'description' => 'Grille des frais scolaires',
                'keywords' => ['frais', 'scolarité', 'school fees', 'tuition']
            ],
            'honors' => [
                'label' => 'Tableau d\'Honneur',
                'url' => '/honors',
                'description' => 'Liste des élèves excellents',
                'keywords' => ['honneur', 'excellence', 'honor', 'excellence']
            ],
            'proces_verbal' => [
                'label' => 'Procès-Verbaux',
                'url' => '/proces-verbal',
                'description' => 'Documents procès-verbaux',
                'keywords' => ['procès-verbal', 'pv', 'report', 'minutes']
            ]
        ];
    }

    /**
     * Indexe les routes disponibles
     */
    private function indexRoutes()
    {
        return [
            '/' => 'Page d\'accueil',
            '/login' => 'Connexion',
            '/logout' => 'Déconnexion',
            '/students' => 'Liste des élèves',
            '/students/create' => 'Créer un élève',
            '/teachers' => 'Liste des enseignants',
            '/classes' => 'Liste des classes',
            '/classes/create' => 'Créer une classe',
            '/subjects' => 'Liste des matières',
            '/subjects/create' => 'Créer une matière',
            '/notes' => 'Saisie des notes',
            '/bulletins' => 'Bulletins',
            '/payments' => 'Paiements',
            '/settings' => 'Paramètres',
            '/users' => 'Utilisateurs',
            '/profile' => 'Profil'
        ];
    }

    /**
     * Indexe les fonctionnalités par rôle
     */
    private function indexFeatures()
    {
        $features = [
            'superadmin' => [
                'Gestion complète des utilisateurs',
                'Configuration système',
                'Gestion des cycles et sections',
                'Gestion financière complète',
                'Accès à tous les rapports'
            ],
            'admin' => [
                'Gestion des élèves et enseignants',
                'Saisie des notes',
                'Génération des bulletins',
                'Gestion des paiements',
                'Rapports académiques'
            ],
            'enseignant' => [
                'Saisie des notes',
                'Consultation des classes',
                'Génération des bulletins',
                'Consultation de la documentation'
            ],
            'caissier' => [
                'Gestion des paiements',
                'Consultation des élèves',
                'Historique financier',
                'Gestion des réductions'
            ],
            'comptable' => [
                'Gestion financière',
                'Rapports financiers',
                'Consultation des historiques'
            ],
            'it_manager' => [
                'Gestion des utilisateurs',
                'Support technique',
                'Maintenance système'
            ]
        ];

        return $features[$this->userRole] ?? [];
    }

    /**
     * Indexe les sujets d'aide avec variations de formulation
     */
    private function indexHelpTopics()
    {
        $lang = $this->appLang;
        
        if ($lang === 'en') {
            return [
                'how_create_student' => [
                    'question' => 'How to create a student?',
                    'answer' => 'To create a new student, go to the "Students" section and click on "New Student". Fill in the form with the required information.',
                    'url' => '/students/create',
                    'keywords' => ['create', 'student', 'new', 'add', 'register', 'enroll']
                ],
                'how_enter_grades' => [
                    'question' => 'How to enter grades?',
                    'answer' => 'To enter grades, select a class, subject and sequence in the "Grades" section. You can then enter grades for each student.',
                    'url' => '/notes',
                    'keywords' => ['enter', 'grade', 'mark', 'score', 'input', 'record']
                ],
                'how_generate_report' => [
                    'question' => 'How to generate a report card?',
                    'answer' => 'Go to the "Reports" section, select the report type (sequence or trimester), choose the class and student, then click "Generate".',
                    'url' => '/bulletins',
                    'keywords' => ['generate', 'report', 'transcript', 'bulletin', 'create']
                ],
                'how_record_payment' => [
                    'question' => 'How to record a payment?',
                    'answer' => 'In the "Payments" section, click on "New Payment", select the student, payment type and amount, then validate.',
                    'url' => '/payments',
                    'keywords' => ['record', 'payment', 'fee', 'money', 'register', 'add', 'save']
                ],
                'how_create_class' => [
                    'question' => 'How to create a class?',
                    'answer' => 'Go to "Classes", click on "New Class", define the name, cycle, section and capacity of the class.',
                    'url' => '/classes/create',
                    'keywords' => ['create', 'class', 'room', 'add', 'new']
                ],
                'how_edit_profile' => [
                    'question' => 'How to edit my profile?',
                    'answer' => 'Click on your avatar in the top right, then select "My Profile". You can modify your personal information.',
                    'url' => '/profile',
                    'keywords' => ['edit', 'profile', 'account', 'modify', 'change']
                ],
                'where_find_help' => [
                    'question' => 'Where to find help?',
                    'answer' => 'You can access the complete documentation via the "Help" menu in the "Control Center" section.',
                    'url' => '/documentation',
                    'keywords' => ['help', 'support', 'assistance', 'documentation']
                ],
                'login_problem' => [
                    'question' => 'I cannot login',
                    'answer' => 'Check your username and password. If the problem persists, contact the system administrator or technical support.',
                    'url' => '/documentation',
                    'keywords' => ['login', 'password', 'connect', 'sign in', 'authentication']
                ],
                'how_create_teacher' => [
                    'question' => 'How to create a teacher?',
                    'answer' => 'Go to "Teachers", click on "New Teacher" and fill in the required information (name, subjects, contact).',
                    'url' => '/teachers',
                    'keywords' => ['create', 'teacher', 'professor', 'instructor', 'add', 'new']
                ],
                'how_view_history' => [
                    'question' => 'How to view financial history?',
                    'answer' => 'The "Financial History" section allows you to view all transactions, filter by period and operation type.',
                    'url' => '/financial-history',
                    'keywords' => ['history', 'financial', 'transaction', 'view', 'check']
                ]
            ];
        }
        
        // Français par défaut
        return [
            'comment_creer_eleve' => [
                'question' => 'Comment créer un élève ?',
                'answer' => 'Pour créer un nouvel élève, allez dans la section "Élèves" puis cliquez sur le bouton "Nouvel Élève". Remplissez le formulaire avec les informations requises.',
                'url' => '/students/create',
                'keywords' => ['créer', 'élève', 'nouveau', 'ajouter', 'enregistrer', 'inscrire', 'étudiant', 'enfant']
            ],
            'jaimerai_creer_eleve' => [
                'question' => 'J\'aimerais créer un élève',
                'answer' => 'Pour créer un nouvel élève, allez dans la section "Élèves" puis cliquez sur le bouton "Nouvel Élève". Remplissez le formulaire avec les informations requises.',
                'url' => '/students/create',
                'keywords' => ['créer', 'élève', 'nouveau', 'ajouter', 'enregistrer', 'inscrire', 'étudiant', 'enfant', 'jaimerais', 'aimerai']
            ],
            'nouvel_eleve' => [
                'question' => 'Ajouter un nouvel élève',
                'answer' => 'Pour créer un nouvel élève, allez dans la section "Élèves" puis cliquez sur le bouton "Nouvel Élève". Remplissez le formulaire avec les informations requises.',
                'url' => '/students/create',
                'keywords' => ['créer', 'élève', 'nouveau', 'ajouter', 'enregistrer', 'inscrire', 'étudiant', 'enfant']
            ],
            'comment_saisir_notes' => [
                'question' => 'Comment saisir des notes ?',
                'answer' => 'Pour saisir des notes, sélectionnez une classe, une matière et une séquence dans la section "Notes". Vous pourrez ensuite entrer les notes pour chaque élève.',
                'url' => '/notes',
                'keywords' => ['saisir', 'note', 'évaluation', 'grade', 'entrer', 'enregistrer', 'noter']
            ],
            'jaimerai_saisir_notes' => [
                'question' => 'J\'aimerais saisir des notes',
                'answer' => 'Pour saisir des notes, sélectionnez une classe, une matière et une séquence dans la section "Notes". Vous pourrez ensuite entrer les notes pour chaque élève.',
                'url' => '/notes',
                'keywords' => ['saisir', 'note', 'évaluation', 'grade', 'entrer', 'enregistrer', 'noter', 'jaimerais', 'aimerai']
            ],
            'comment_generer_bulletin' => [
                'question' => 'Comment générer un bulletin ?',
                'answer' => 'Allez dans la section "Bulletins", sélectionnez le type de bulletin (séquence ou trimestre), choisissez la classe et l\'élève, puis cliquez sur "Générer".',
                'url' => '/bulletins',
                'keywords' => ['générer', 'bulletin', 'relevé', 'report', 'créer', 'imprimer']
            ],
            'comment_enregistrer_paiement' => [
                'question' => 'Comment enregistrer un paiement ?',
                'answer' => 'Dans la section "Paiements", cliquez sur "Nouveau Paiement", sélectionnez l\'élève, le type de paiement et le montant, puis validez.',
                'url' => '/payments',
                'keywords' => ['enregistrer', 'paiement', 'frais', 'argent', 'versement', 'créer', 'ajouter', 'nouveau']
            ],
            'jaimerai_enregistrer_versement' => [
                'question' => 'J\'aimerais enregistrer un nouveau versement',
                'answer' => 'Dans la section "Paiements", cliquez sur "Nouveau Paiement", sélectionnez l\'élève, le type de paiement et le montant, puis validez.',
                'url' => '/payments',
                'keywords' => ['enregistrer', 'paiement', 'frais', 'argent', 'versement', 'créer', 'ajouter', 'nouveau', 'jaimerais', 'aimerai']
            ],
            'nouveau_versement' => [
                'question' => 'Enregistrer un nouveau versement',
                'answer' => 'Dans la section "Paiements", cliquez sur "Nouveau Paiement", sélectionnez l\'élève, le type de paiement et le montant, puis validez.',
                'url' => '/payments',
                'keywords' => ['enregistrer', 'paiement', 'frais', 'argent', 'versement', 'créer', 'ajouter', 'nouveau']
            ],
            'faire_paiement' => [
                'question' => 'Faire un paiement',
                'answer' => 'Dans la section "Paiements", cliquez sur "Nouveau Paiement", sélectionnez l\'élève, le type de paiement et le montant, puis validez.',
                'url' => '/payments',
                'keywords' => ['enregistrer', 'paiement', 'frais', 'argent', 'versement', 'créer', 'ajouter', 'nouveau', 'faire']
            ],
            'comment_creer_classe' => [
                'question' => 'Comment créer une classe ?',
                'answer' => 'Allez dans "Classes", cliquez sur "Nouvelle Classe", définissez le nom, le cycle, la section et la capacité de la classe.',
                'url' => '/classes/create',
                'keywords' => ['créer', 'classe', 'salle', 'groupe', 'ajouter', 'nouveau']
            ],
            'comment_modifier_profil' => [
                'question' => 'Comment modifier mon profil ?',
                'answer' => 'Cliquez sur votre avatar en haut à droite, puis sélectionnez "Mon Profil". Vous pourrez modifier vos informations personnelles.',
                'url' => '/profile',
                'keywords' => ['modifier', 'profil', 'compte', 'changer', 'éditer', 'mettre à jour']
            ],
            'ou_trouver_aide' => [
                'question' => 'Où trouver de l\'aide ?',
                'answer' => 'Vous pouvez accéder à la documentation complète via le menu "Aide" dans la section "Centre de Pilotage".',
                'url' => '/documentation',
                'keywords' => ['aide', 'help', 'support', 'assistance', 'documentation']
            ],
            'probleme_connexion' => [
                'question' => 'Je ne peux pas me connecter',
                'answer' => 'Vérifiez votre identifiant et mot de passe. Si le problème persiste, contactez l\'administrateur système ou le support technique.',
                'url' => '/documentation',
                'keywords' => ['connexion', 'login', 'mot de passe', 'password', 'connect', 'authentifier']
            ],
            'comment_creer_enseignant' => [
                'question' => 'Comment créer un enseignant ?',
                'answer' => 'Allez dans "Enseignants", cliquez sur "Nouvel Enseignant" et remplissez les informations requises (nom, matières, contact).',
                'url' => '/teachers',
                'keywords' => ['créer', 'enseignant', 'professeur', 'prof', 'ajouter', 'nouveau']
            ],
            'comment_consulter_historique' => [
                'question' => 'Comment consulter l\'historique financier ?',
                'answer' => 'La section "Historique Financier" vous permet de consulter toutes les transactions, filtrer par période et par type d\'opération.',
                'url' => '/financial-history',
                'keywords' => ['historique', 'financier', 'transaction', 'voir', 'consulter', 'afficher']
            ]
        ];
    }

    /**
     * Traite une question de l'utilisateur
     */
    public function processQuestion($question)
    {
        $question = strtolower(trim($question));
        
        // Vérifier si la question est hors contexte
        if ($this->isOutOfContext($question)) {
            return [
                'success' => true,
                'response' => 'Cette question sort du cadre de l\'application NotesMaster. Pour toute demande hors contexte, veuillez contacter le support technique via la section "Aide" du menu.',
                'actions' => [
                    [
                        'label' => 'Contacter le support',
                        'url' => '/documentation',
                        'icon' => 'bi-question-circle'
                    ]
                ]
            ];
        }

        // Analyser la question et trouver la meilleure réponse
        $result = $this->findBestMatch($question);

        if ($result) {
            return [
                'success' => true,
                'response' => $result['answer'],
                'actions' => $result['actions'] ?? []
            ];
        }

        // Si pas de correspondance, chercher dans les traductions
        $translationResult = $this->searchInTranslations($question);
        if ($translationResult) {
            return [
                'success' => true,
                'response' => $translationResult['answer'],
                'actions' => $translationResult['actions'] ?? []
            ];
        }

        // Réponse par défaut
        return [
            'success' => true,
            'response' => 'Je n\'ai pas trouvé d\'information spécifique pour votre demande. Voici quelques sections qui pourraient vous aider :',
            'actions' => [
                [
                    'label' => 'Documentation',
                    'url' => '/documentation',
                    'icon' => 'bi-book'
                ],
                [
                    'label' => 'Tableau de bord',
                    'url' => '/',
                    'icon' => 'bi-speedometer2'
                ]
            ]
        ];
    }

    /**
     * Traite une question avec étapes de recherche détaillées (streaming)
     */
    public function processQuestionWithSteps($question, $stepCallback)
    {
        $question = strtolower(trim($question));
        
        // Étape 1: Analyse mot à mot
        $stepCallback('🔍 Analyse mot à mot', 'Décomposition et compréhension de votre question');
        usleep(800000); // 0.8 seconde
        
        $words = $this->extractWords($question);
        $stepCallback('📝 Extraction des mots-clés', 'Identification des concepts: ' . implode(', ', array_slice($words, 0, 5)));
        usleep(600000); // 0.6 seconde

        // Étape 2: Vérification du contexte
        $stepCallback('🎯 Vérification du contexte', 'Analyse de la pertinence dans NotesMaster');
        usleep(700000); // 0.7 seconde
        
        if ($this->isOutOfContext($question)) {
            $stepCallback('⚠️ Hors contexte détecté', 'Redirection vers le support technique');
            usleep(500000); // 0.5 seconde
            
            return [
                'response' => 'Cette question sort du cadre de l\'application NotesMaster. Pour toute demande hors contexte, veuillez contacter le support technique via la section "Aide" du menu.',
                'actions' => [
                    [
                        'label' => 'Contacter le support',
                        'url' => '/documentation',
                        'icon' => 'bi-question-circle'
                    ]
                ]
            ];
        }

        // Étape 3: Recherche dans la base de connaissances
        $stepCallback('📚 Recherche dans la base de connaissances', 'Analyse des sujets d\'aide disponibles');
        usleep(900000); // 0.9 seconde
        
        $helpResult = $this->searchInHelpTopicsWithDetails($question, $stepCallback);
        
        // Étape 4: Recherche dans la navigation
        $stepCallback('🧭 Analyse de la navigation', 'Exploration des sections de l\'application');
        usleep(800000); // 0.8 seconde
        
        $navResult = $this->searchInNavigationWithDetails($question, $stepCallback);

        // Étape 5: Recherche dans les traductions
        $stepCallback('🌍 Recherche multilingue', 'Analyse des traductions disponibles');
        usleep(700000); // 0.7 seconde
        
        $translationResult = $this->searchInTranslationsWithDetails($question, $stepCallback);

        // Étape 6: Comparaison et sélection
        $stepCallback('⚖️ Comparaison des résultats', 'Évaluation des meilleures correspondances');
        usleep(600000); // 0.6 seconde
        
        $bestResult = $this->selectBestResult($helpResult, $navResult, $translationResult);

        // Étape 7: Finalisation
        $stepCallback('✨ Finalisation de la réponse', 'Préparation de la réponse personnalisée');
        usleep(500000); // 0.5 seconde

        if ($bestResult) {
            return [
                'response' => $bestResult['answer'],
                'actions' => $bestResult['actions'] ?? []
            ];
        }

        // Réponse par défaut
        return [
            'response' => 'Après une analyse approfondie, je n\'ai pas trouvé d\'information spécifique pour votre demande. Voici quelques sections qui pourraient vous aider :',
            'actions' => [
                [
                    'label' => 'Documentation',
                    'url' => '/documentation',
                    'icon' => 'bi-book'
                ],
                [
                    'label' => 'Tableau de bord',
                    'url' => '/',
                    'icon' => 'bi-speedometer2'
                ]
            ]
        ];
    }

    /**
     * Construit le dictionnaire de synonymes
     */
    private function buildSynonymsDictionary()
    {
        return [
            'fr' => [
                'enregistrer' => ['créer', 'ajouter', 'nouveau', 'saisir', 'inscrire', 'faire', 'effectuer'],
                'versement' => ['paiement', 'payement', 'argent', 'frais', 'somme', 'transaction'],
                'élève' => ['étudiant', 'enfant', 'inscrit', 'personne'],
                'créer' => ['enregistrer', 'ajouter', 'nouveau', 'faire', 'inscrire'],
                'comment' => ['comment faire', 'quelle méthode', 'de quelle manière', 'procédure'],
                'aimerai' => ['veux', 'souhaite', 'désire', 'voudrais'],
                'nouveau' => ['créer', 'ajouter', 'fresh', 'nouvelle'],
                'note' => ['évaluation', 'grade', 'score', 'résultat', 'mark'],
                'bulletin' => ['relevé', 'report', 'transcript', 'document'],
                'classe' => ['salle', 'groupe', 'section', 'room'],
                'enseignant' => ['professeur', 'prof', 'teacher', 'maître'],
                'saisir' => ['entrer', 'enregistrer', 'noter', 'insérer'],
                'modifier' => ['changer', 'éditer', 'mettre à jour', 'update'],
                'supprimer' => ['effacer', 'retirer', 'enlever', 'delete'],
                'chercher' => ['rechercher', 'trouver', 'look for', 'find'],
                'voir' => ['afficher', 'consulter', 'visualiser', 'show'],
                'aller' => ['accéder', 'naviguer', 'go to', 'access'],
                'aide' => ['support', 'assistance', 'help'],
                'problème' => ['difficulté', 'erreur', 'issue', 'problem'],
                'connexion' => ['login', 'se connecter', 'authentifier'],
                'déconnexion' => ['logout', 'se déconnecter', 'quitter'],
                'paramètre' => ['configuration', 'setting', 'option'],
                'profil' => ['mon compte', 'mes infos', 'account'],
                'facture' => ['invoice', 'reçu', 'receipt'],
                'solde' => ['balance', 'reste', 'amount'],
                'bourse' => ['réduction', 'discount', 'aide'],
                'inscription' => ['registration', 'enrollment', 'admission']
            ],
            'en' => [
                'register' => ['create', 'add', 'new', 'save', 'record', 'make', 'do'],
                'payment' => ['pay', 'money', 'fee', 'amount', 'transaction'],
                'student' => ['pupil', 'child', 'enrolled', 'person'],
                'create' => ['register', 'add', 'new', 'make', 'save'],
                'how' => ['how to', 'what method', 'which way', 'procedure'],
                'would like' => ['want', 'wish', 'desire'],
                'new' => ['create', 'add', 'fresh'],
                'grade' => ['mark', 'score', 'result', 'evaluation'],
                'report' => ['transcript', 'bulletin', 'document'],
                'class' => ['room', 'group', 'section'],
                'teacher' => ['professor', 'instructor', 'master'],
                'enter' => ['input', 'record', 'note', 'insert'],
                'edit' => ['change', 'modify', 'update'],
                'delete' => ['remove', 'erase', 'take away'],
                'search' => ['look for', 'find'],
                'view' => ['display', 'show', 'see'],
                'go' => ['access', 'navigate'],
                'help' => ['support', 'assistance'],
                'problem' => ['difficulty', 'error', 'issue'],
                'login' => ['connect', 'authenticate', 'sign in'],
                'logout' => ['disconnect', 'sign out', 'quit'],
                'setting' => ['configuration', 'option', 'parameter'],
                'profile' => ['my account', 'my info'],
                'invoice' => ['receipt', 'bill'],
                'balance' => ['remaining', 'amount'],
                'scholarship' => ['discount', 'aid', 'help'],
                'registration' => ['enrollment', 'admission']
            ]
        ];
    }

    /**
     * Construit la liste des mots de liaison à ignorer
     */
    private function buildStopWords()
    {
        return [
            'fr' => ['le', 'la', 'les', 'un', 'une', 'des', 'du', 'de', 'à', 'au', 'aux', 'pour', 'par', 'avec', 'sur', 'dans', 'en', 'et', 'ou', 'mais', 'donc', 'or', 'ni', 'car', 'que', 'qui', 'quoi', 'où', 'quand', 'comment', 'pourquoi', 'combien', 'ce', 'cet', 'cette', 'ces', 'mon', 'ma', 'mes', 'ton', 'ta', 'tes', 'son', 'sa', 'ses', 'notre', 'nos', 'votre', 'vos', 'leur', 'leurs', 'je', 'tu', 'il', 'elle', 'nous', 'vous', 'ils', 'elles', 'avoir', 'être', 'faire', 'aller', 'vouloir', 'pouvoir', 'devoir', 'savoir', 'connaître', 'voir', 'dire', 'prendre', 'mettre', 'donner', 'venir', 'passer', 'tenir', 'trouver', 'porter', 'regarder', 'sembler', 'parler', 'aimer', 'falloir', 'être', 'avoir', 'jaimerai', 'jaimerais'],
            'en' => ['the', 'a', 'an', 'and', 'or', 'but', 'for', 'nor', 'so', 'yet', 'at', 'by', 'for', 'from', 'in', 'into', 'of', 'on', 'to', 'with', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'me', 'him', 'her', 'us', 'them', 'my', 'your', 'his', 'her', 'its', 'our', 'their', 'mine', 'yours', 'hers', 'ours', 'theirs', 'this', 'that', 'these', 'those', 'who', 'whom', 'whose', 'which', 'what', 'where', 'when', 'why', 'how', 'be', 'am', 'is', 'are', 'was', 'were', 'being', 'been', 'have', 'has', 'had', 'having', 'do', 'does', 'did', 'doing', 'will', 'would', 'shall', 'should', 'can', 'could', 'may', 'might', 'must', 'ought', 'need', 'dare', 'used', 'to', 'want', 'like', 'would', 'like']
        ];
    }

    /**
     * Extrait les mots d'une question avec expansion de synonymes
     */
    private function extractWords($question)
    {
        $question = $this->normalizeString($question);
        $words = explode(' ', $question);
        
        // Filtrer les mots de liaison
        $stopWords = $this->stopWords[$this->appLang] ?? [];
        $words = array_filter($words, function($word) use ($stopWords) {
            return strlen($word) > 2 && !in_array($word, $stopWords);
        });
        
        // Expansion avec synonymes
        $expandedWords = [];
        $synonyms = $this->synonyms[$this->appLang] ?? [];
        
        foreach ($words as $word) {
            $expandedWords[] = $word;
            
            // Ajouter les synonymes si disponibles
            foreach ($synonyms as $key => $synList) {
                if ($word === $key || in_array($word, $synList)) {
                    $expandedWords = array_merge($expandedWords, $synList);
                }
            }
        }
        
        return array_values(array_unique($expandedWords));
    }

    /**
     * Recherche dans les sujets d'aide avec détails
     */
    private function searchInHelpTopicsWithDetails($question, $stepCallback)
    {
        $bestMatch = null;
        $bestScore = 0;
        $analyzedCount = 0;

        foreach ($this->indexData['help_topics'] as $topic) {
            $score = $this->calculateSimilarity($question, $topic['question'] . ' ' . implode(' ', $topic['keywords']));
            $analyzedCount++;
            
            if ($analyzedCount % 3 === 0) {
                $stepCallback('📚 Analyse des sujets', $analyzedCount . ' sujets analysés sur ' . count($this->indexData['help_topics']));
                usleep(200000); // 0.2 seconde
            }
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = [
                    'answer' => $topic['answer'],
                    'actions' => [
                        [
                            'label' => 'Accéder à ' . $topic['question'],
                            'url' => $topic['url'],
                            'icon' => 'bi-arrow-right-circle'
                        ]
                    ],
                    'score' => $score
                ];
            }
        }

        return $bestMatch;
    }

    /**
     * Recherche dans la navigation avec détails
     */
    private function searchInNavigationWithDetails($question, $stepCallback)
    {
        $bestMatch = null;
        $bestScore = 0;
        $analyzedCount = 0;

        foreach ($this->indexData['navigation'] as $navItem) {
            $score = $this->calculateSimilarity($question, $navItem['label'] . ' ' . $navItem['description'] . ' ' . implode(' ', $navItem['keywords']));
            $analyzedCount++;
            
            if ($analyzedCount % 4 === 0) {
                $stepCallback('🧭 Analyse de la navigation', $analyzedCount . ' sections analysées sur ' . count($this->indexData['navigation']));
                usleep(150000); // 0.15 seconde
            }
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = [
                    'answer' => $navItem['description'] . '. Vous pouvez y accéder via le menu de navigation.',
                    'actions' => [
                        [
                            'label' => $navItem['label'],
                            'url' => $navItem['url'],
                            'icon' => 'bi-arrow-right-circle'
                        ]
                    ],
                    'score' => $score
                ];
            }
        }

        return $bestMatch;
    }

    /**
     * Recherche dans les traductions avec détails
     */
    private function searchInTranslationsWithDetails($question, $stepCallback)
    {
        $bestMatch = null;
        $bestScore = 0;
        $analyzedCount = 0;
        $totalTranslations = count($this->indexData['translations']);

        foreach ($this->indexData['translations'] as $key => $value) {
            if (is_string($value) && !$this->isSensitiveData($key)) {
                $score = $this->calculateSimilarity($question, $value);
                $analyzedCount++;
                
                if ($analyzedCount % 50 === 0) {
                    $stepCallback('🌍 Analyse des traductions', $analyzedCount . ' entrées analysées sur ' . $totalTranslations);
                    usleep(100000); // 0.1 seconde
                }
                
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = [
                        'answer' => 'Cette information est disponible dans l\'application : ' . $this->sanitizeResponse($value),
                        'score' => $score
                    ];
                }
            }
        }

        return $bestMatch;
    }

    /**
     * Sélectionne le meilleur résultat
     */
    private function selectBestResult($helpResult, $navResult, $translationResult)
    {
        $results = [];
        
        if ($helpResult && $helpResult['score'] > 0.3) {
            $results[] = $helpResult;
        }
        if ($navResult && $navResult['score'] > 0.3) {
            $results[] = $navResult;
        }
        if ($translationResult && $translationResult['score'] > 0.4) {
            $results[] = $translationResult;
        }

        if (empty($results)) {
            return null;
        }

        // Trier par score
        usort($results, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $results[0];
    }

    /**
     * Vérifie si une clé contient des données sensibles
     */
    private function isSensitiveData($key)
    {
        $sensitivePatterns = [
            'password', 'secret', 'token', 'api_key', 'private',
            'credit_card', 'ssn', 'social_security', 'bank_account',
            'personal', 'contact', 'address', 'phone', 'email'
        ];

        foreach ($sensitivePatterns as $pattern) {
            if (strpos(strtolower($key), $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Nettoie la réponse pour éviter d'exposer des données sensibles
     */
    private function sanitizeResponse($text)
    {
        // Masquer les adresses email
        $text = preg_replace('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/', '[email protégé]', $text);
        
        // Masquer les numéros de téléphone
        $text = preg_replace('/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/', '[téléphone protégé]', $text);
        
        // Masquer les numéros de carte de crédit potentiels
        $text = preg_replace('/\b\d{4}[-\s]?\d{4}[-\s]?\d{4}[-\s]?\d{4}\b/', '[carte protégée]', $text);
        
        return $text;
    }

    /**
     * Vérifie si la question est hors contexte
     */
    private function isOutOfContext($question)
    {
        $outOfContextKeywords = [
            'météo', 'weather', 'politique', 'politics', 'sport', 'sports',
            'actualité', 'news', 'cuisine', 'recipe', 'voyage', 'travel',
            'film', 'movie', 'musique', 'music', 'jeu', 'game', 'blague', 'joke'
        ];

        foreach ($outOfContextKeywords as $keyword) {
            if (strpos($question, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Trouve la meilleure correspondance pour la question
     */
    private function findBestMatch($question)
    {
        $bestMatch = null;
        $bestScore = 0;

        // Chercher dans les sujets d'aide
        foreach ($this->indexData['help_topics'] as $topic) {
            $score = $this->calculateSimilarity($question, $topic['question']);
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = [
                    'answer' => $topic['answer'],
                    'actions' => [
                        [
                            'label' => 'Accéder à ' . $topic['question'],
                            'url' => $topic['url'],
                            'icon' => 'bi-arrow-right-circle'
                        ]
                    ]
                ];
            }
        }

        // Chercher dans la navigation
        foreach ($this->indexData['navigation'] as $navItem) {
            $score = $this->calculateSimilarity($question, $navItem['label'] . ' ' . $navItem['description'] . ' ' . implode(' ', $navItem['keywords']));
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = [
                    'answer' => $navItem['description'] . '. Vous pouvez y accéder via le menu de navigation.',
                    'actions' => [
                        [
                            'label' => $navItem['label'],
                            'url' => $navItem['url'],
                            'icon' => 'bi-arrow-right-circle'
                        ]
                    ]
                ];
            }
        }

        // Seuil de similarité minimum
        if ($bestScore > 0.3) {
            return $bestMatch;
        }

        return null;
    }

    /**
     * Cherche dans les traductions
     */
    private function searchInTranslations($question)
    {
        $bestMatch = null;
        $bestScore = 0;

        foreach ($this->indexData['translations'] as $key => $value) {
            if (is_string($value)) {
                $score = $this->calculateSimilarity($question, $value);
                
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = [
                        'answer' => 'Cette information est disponible dans l\'application : ' . $value
                    ];
                }
            }
        }

        if ($bestScore > 0.4) {
            return $bestMatch;
        }

        return null;
    }

    /**
     * Calcule la similarité entre deux chaînes (algorithme amélioré avec synonymes)
     */
    private function calculateSimilarity($str1, $str2)
    {
        $str1 = $this->normalizeString($str1);
        $str2 = $this->normalizeString($str2);

        // Extraire les mots avec expansion de synonymes
        $words1 = $this->extractWords($str1);
        $words2 = $this->extractWords($str2);

        // Calculer l'intersection et l'union
        $intersection = array_intersect($words1, $words2);
        $union = array_unique(array_merge($words1, $words2));

        if (count($union) === 0) return 0;

        // Similarité Jaccard
        $jaccard = count($intersection) / count($union);

        // Bonus pour les mots consécutifs
        $consecutiveBonus = $this->calculateConsecutiveBonus($str1, $str2);

        // Bonus pour la présence de mots-clés importants
        $keywordBonus = $this->calculateKeywordBonus($str1, $str2);

        // Score final avec pondération
        return ($jaccard * 0.5) + ($consecutiveBonus * 0.25) + ($keywordBonus * 0.25);
    }

    /**
     * Calcule un bonus pour les mots-clés importants
     */
    private function calculateKeywordBonus($str1, $str2)
    {
        $importantKeywords = [
            'enregistrer', 'créer', 'ajouter', 'nouveau', 'saisir',
            'versement', 'paiement', 'argent', 'frais',
            'élève', 'étudiant', 'inscrit',
            'note', 'évaluation', 'grade',
            'bulletin', 'relevé',
            'classe', 'salle',
            'enseignant', 'professeur',
            'modifier', 'changer',
            'supprimer', 'effacer',
            'connexion', 'login',
            'déconnexion', 'logout',
            'paramètre', 'configuration',
            'profil', 'compte'
        ];

        $str1Lower = strtolower($str1);
        $str2Lower = strtolower($str2);

        $keywordMatches = 0;
        $totalKeywordsInStr2 = 0;

        foreach ($importantKeywords as $keyword) {
            if (strpos($str2Lower, $keyword) !== false) {
                $totalKeywordsInStr2++;
                if (strpos($str1Lower, $keyword) !== false) {
                    $keywordMatches++;
                }
            }
        }

        if ($totalKeywordsInStr2 === 0) return 0;

        return $keywordMatches / $totalKeywordsInStr2;
    }

    /**
     * Normalise une chaîne pour la comparaison
     */
    private function normalizeString($str)
    {
        $str = strtolower($str);
        $str = preg_replace('/[àáâãäå]/', 'a', $str);
        $str = preg_replace('/[èéêë]/', 'e', $str);
        $str = preg_replace('/[ìíîï]/', 'i', $str);
        $str = preg_replace('/[òóôõö]/', 'o', $str);
        $str = preg_replace('/[ùúûü]/', 'u', $str);
        $str = preg_replace('/[ýÿ]/', 'y', $str);
        $str = preg_replace('/[ç]/', 'c', $str);
        $str = preg_replace('/[^a-z0-9\s]/', '', $str);
        
        return $str;
    }

    /**
     * Calcule un bonus pour les mots consécutifs
     */
    private function calculateConsecutiveBonus($str1, $str2)
    {
        $words1 = explode(' ', $str1);
        $words2 = explode(' ', $str2);
        
        $maxConsecutive = 0;
        $currentConsecutive = 0;

        foreach ($words1 as $word) {
            if (in_array($word, $words2)) {
                $currentConsecutive++;
            } else {
                $maxConsecutive = max($maxConsecutive, $currentConsecutive);
                $currentConsecutive = 0;
            }
        }

        $maxConsecutive = max($maxConsecutive, $currentConsecutive);
        
        // Normaliser le bonus
        return min($maxConsecutive / max(count($words1), count($words2)), 1);
    }
}
