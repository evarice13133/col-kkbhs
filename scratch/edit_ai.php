<?php
$file = 'src/Services/AIAssistantService.php';
// Restore from backup
$content = file_get_contents('scratch/AIAssistantService_backup.php');

// Replace calculateSimilarity
$newCalculateSimilarity = <<<'EOD'
    private function calculateSimilarity($str1, $str2)
    {
        $str1 = $this->normalizeString($str1);
        $str2 = $this->normalizeString($str2);

        // Extraire les mots avec expansion de synonymes
        $words1 = $this->extractWords($str1);
        $words2 = $this->extractWords($str2);

        // Calculer l'intersection
        $intersection = array_intersect($words1, $words2);

        if (count($words1) === 0) return 0;

        // Utiliser l'Overlap coefficient au lieu de Jaccard pour ne pas pénaliser
        // les sujets qui ont beaucoup de mots-clés
        $overlap = count($intersection) / count($words1);

        // Bonus pour les mots consécutifs
        $consecutiveBonus = $this->calculateConsecutiveBonus($str1, $str2);

        // Bonus pour la présence de mots-clés importants
        $keywordBonus = $this->calculateKeywordBonus($str1, $str2);

        // Score final avec pondération
        return ($overlap * 0.6) + ($consecutiveBonus * 0.2) + ($keywordBonus * 0.2);
    }
EOD;

$content = preg_replace('~private function calculateSimilarity\(\$str1, \$str2\).*?\}\s+private function calculateKeywordBonus~s', $newCalculateSimilarity . "\n\n    private function calculateKeywordBonus", $content);

// Replace calculateKeywordBonus
$newCalculateKeywordBonus = <<<'EOD'
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
            'profil', 'compte',
            'register', 'create', 'add', 'new', 'enter',
            'payment', 'money', 'fee', 'amount',
            'student', 'pupil',
            'mark', 'score',
            'report', 'transcript',
            'room', 'teacher', 'professor',
            'edit', 'update', 'delete', 'remove',
            'setting', 'account'
        ];

        $str1Lower = strtolower($str1);
        $str2Lower = strtolower($str2);

        $keywordMatches = 0;
        $totalKeywordsInStr1 = 0;

        foreach ($importantKeywords as $keyword) {
            if (strpos($str1Lower, $keyword) !== false) {
                $totalKeywordsInStr1++;
                if (strpos($str2Lower, $keyword) !== false) {
                    $keywordMatches++;
                }
            }
        }

        if ($totalKeywordsInStr1 === 0) return 0;

        return $keywordMatches / $totalKeywordsInStr1;
    }
EOD;

$content = preg_replace('~private function calculateKeywordBonus\(\$str1, \$str2\).*?\}\s+private function normalizeString~s', $newCalculateKeywordBonus . "\n\n    private function normalizeString", $content);

// Replace findBestMatch to include keywords
$newFindBestMatch = <<<'EOD'
    private function findBestMatch($question)
    {
        $bestMatch = null;
        $bestScore = 0;

        // Chercher dans les sujets d'aide
        foreach ($this->indexData['help_topics'] as $topic) {
            $keywordsStr = isset($topic['keywords']) ? implode(' ', $topic['keywords']) : '';
            $score = $this->calculateSimilarity($question, $topic['question'] . ' ' . $keywordsStr);
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $actionLabel = $this->appLang === 'en' ? 'Go to ' : 'Accéder à ';
                $bestMatch = [
                    'answer' => $topic['answer'],
                    'actions' => [
                        [
                            'label' => $actionLabel . $topic['question'],
                            'url' => $topic['url'],
                            'icon' => 'bi-arrow-right-circle'
                        ]
                    ]
                ];
            }
        }

        // Chercher dans la navigation
        foreach ($this->indexData['navigation'] as $navItem) {
            $keywordsStr = isset($navItem['keywords']) ? implode(' ', $navItem['keywords']) : '';
            $score = $this->calculateSimilarity($question, $navItem['label'] . ' ' . $navItem['description'] . ' ' . $keywordsStr);
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $navAnswer = $this->appLang === 'en' 
                    ? $navItem['description'] . '. You can access it via the navigation menu.'
                    : $navItem['description'] . '. Vous pouvez y accéder via le menu de navigation.';
                $bestMatch = [
                    'answer' => $navAnswer,
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
EOD;

$content = preg_replace('~private function findBestMatch\(\$question\).*?\}\s+private function searchInTranslations~s', $newFindBestMatch . "\n\n    private function searchInTranslations", $content);

file_put_contents($file, $content);
echo "Modifications made successfully.\n";
