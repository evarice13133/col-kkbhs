<?php
$file = 'src/Services/AIAssistantService.php';
$content = file_get_contents($file);

$oldSynonyms = <<<'EOD'
            'fr' => [
                'enregistrer' => ['créer', 'ajouter', 'nouveau', 'saisir', 'inscrire', 'faire', 'effectuer'],
                'versement' => ['paiement', 'payement', 'argent', 'frais', 'somme', 'transaction'],
EOD;
$newSynonyms = <<<'EOD'
            'fr' => [
                'enregistrer' => ['créer', 'ajouter', 'nouveau', 'saisir', 'inscrire', 'faire', 'effectuer', 'sauvegarder', 'garder', 'annulation', 'supprimer', 'effacer'],
                'versement' => ['paiement', 'payement', 'argent', 'frais', 'somme', 'transaction', 'payer', 'régler', 'retrait', 'remboursement'],
EOD;
$content = str_replace($oldSynonyms, $newSynonyms, $content);

file_put_contents($file, $content);
echo "Modifications 3 made successfully.\n";
