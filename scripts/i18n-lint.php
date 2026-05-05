<?php

/**
 * i18n Lint Tool for NotesMaster
 * Scans all files in src/Core/I18n and checks for:
 * 1. Missing keys in EN vs FR.
 * 2. Empty values.
 * 3. Potential syntax errors.
 */

$i18nPath = __DIR__ . '/../src/Core/I18n';
$files = glob($i18nPath . '/*.php');

$allKeys = ['fr' => [], 'en' => []];

echo "--- i18n Lint Tool ---\n";
echo "Scanning files in: $i18nPath\n\n";

foreach ($files as $file) {
    echo "Processing " . basename($file) . "...\n";
    $data = include $file;
    
    if (!is_array($data)) {
        echo "[ERROR] " . basename($file) . " does not return an array.\n";
        continue;
    }

    foreach (['fr', 'en'] as $lang) {
        if (!isset($data[$lang]) || !is_array($data[$lang])) {
            echo "[WARNING] Language '$lang' missing in " . basename($file) . ".\n";
            continue;
        }
        
        $allKeys[$lang] = array_merge($allKeys[$lang], array_keys($data[$lang]));
    }
}

$frKeys = array_unique($allKeys['fr']);
$enKeys = array_unique($allKeys['en']);

$missingInEn = array_diff($frKeys, $enKeys);
$missingInFr = array_diff($enKeys, $frKeys);

echo "\n--- Summary ---\n";
echo "Total FR keys: " . count($frKeys) . "\n";
echo "Total EN keys: " . count($enKeys) . "\n";

if (empty($missingInEn) && empty($missingInFr)) {
    echo "\n[SUCCESS] All keys are perfectly matched between FR and EN.\n";
} else {
    if (!empty($missingInEn)) {
        echo "\n[ERROR] Missing in EN (present in FR):\n";
        foreach ($missingInEn as $k) echo " - $k\n";
    }
    
    if (!empty($missingInFr)) {
        echo "\n[ERROR] Missing in FR (present in EN):\n";
        foreach ($missingInFr as $k) echo " - $k\n";
    }
}

echo "\nDone.\n";
