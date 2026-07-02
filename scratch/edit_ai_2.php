<?php
$file = 'src/Services/AIAssistantService.php';
$content = file_get_contents($file);

// Replace processQuestion
$newProcessQuestion = <<<'EOD'
    public function processQuestion($question)
    {
        $question = strtolower(trim($question));
        
        // Vérifier si la question est hors contexte
        if ($this->isOutOfContext($question)) {
            $msg = $this->appLang === 'en' 
                ? 'This question is outside the scope of the NotesMaster application. For any out-of-context requests, please contact technical support via the "Help" section.'
                : 'Cette question sort du cadre de l\'application NotesMaster. Pour toute demande hors contexte, veuillez contacter le support technique via la section "Aide" du menu.';
            $btn = $this->appLang === 'en' ? 'Contact support' : 'Contacter le support';
            
            return [
                'success' => true,
                'response' => $msg,
                'actions' => [
                    [
                        'label' => $btn,
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
        $msg = $this->appLang === 'en'
            ? 'I couldn\'t find specific information for your request. Here are some sections that might help you:'
            : 'Je n\'ai pas trouvé d\'information spécifique pour votre demande. Voici quelques sections qui pourraient vous aider :';
        $docBtn = $this->appLang === 'en' ? 'Documentation' : 'Documentation';
        $dashBtn = $this->appLang === 'en' ? 'Dashboard' : 'Tableau de bord';
        
        return [
            'success' => true,
            'response' => $msg,
            'actions' => [
                [
                    'label' => $docBtn,
                    'url' => '/documentation',
                    'icon' => 'bi-book'
                ],
                [
                    'label' => $dashBtn,
                    'url' => '/',
                    'icon' => 'bi-speedometer2'
                ]
            ]
        ];
    }
EOD;

$content = preg_replace('~public function processQuestion\(\$question\).*?\}\s+/\*\*\s+\*\s+Traite une question avec étapes~s', $newProcessQuestion . "\n\n    /**\n     * Traite une question avec étapes", $content);

// Replace default fallback inside processQuestionWithSteps
$oldFallbackOutContext = <<<'EOD'
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
EOD;
$newFallbackOutContext = <<<'EOD'
        if ($this->isOutOfContext($question)) {
            $stepCallback($this->appLang === 'en' ? '⚠️ Out of context detected' : '⚠️ Hors contexte détecté', 
                          $this->appLang === 'en' ? 'Redirection to technical support' : 'Redirection vers le support technique');
            usleep(500000); // 0.5 seconde
            
            $msg = $this->appLang === 'en' 
                ? 'This question is outside the scope of the NotesMaster application. For any out-of-context requests, please contact technical support via the "Help" section.'
                : 'Cette question sort du cadre de l\'application NotesMaster. Pour toute demande hors contexte, veuillez contacter le support technique via la section "Aide" du menu.';
            $btn = $this->appLang === 'en' ? 'Contact support' : 'Contacter le support';

            return [
                'response' => $msg,
                'actions' => [
                    [
                        'label' => $btn,
                        'url' => '/documentation',
                        'icon' => 'bi-question-circle'
                    ]
                ]
            ];
        }
EOD;
$content = str_replace($oldFallbackOutContext, $newFallbackOutContext, $content);

$oldFallbackDefault = <<<'EOD'
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
EOD;
$newFallbackDefault = <<<'EOD'
        // Réponse par défaut
        $msg = $this->appLang === 'en'
            ? 'After a thorough analysis, I couldn\'t find specific information for your request. Here are some sections that might help you:'
            : 'Après une analyse approfondie, je n\'ai pas trouvé d\'information spécifique pour votre demande. Voici quelques sections qui pourraient vous aider :';
        $docBtn = $this->appLang === 'en' ? 'Documentation' : 'Documentation';
        $dashBtn = $this->appLang === 'en' ? 'Dashboard' : 'Tableau de bord';

        return [
            'response' => $msg,
            'actions' => [
                [
                    'label' => $docBtn,
                    'url' => '/documentation',
                    'icon' => 'bi-book'
                ],
                [
                    'label' => $dashBtn,
                    'url' => '/',
                    'icon' => 'bi-speedometer2'
                ]
            ]
        ];
EOD;
$content = str_replace($oldFallbackDefault, $newFallbackDefault, $content);

file_put_contents($file, $content);
echo "Modifications 2 made successfully.\n";
