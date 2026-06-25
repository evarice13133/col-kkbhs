<?php
$filepath = 'c:/Users/ALPHA NUMERIQUE/Downloads/u290233073_futura_db_2 (2).sql';
if (!file_exists($filepath)) {
    die("File not found: $filepath\n");
}
echo "Reading file...\n";
$content = file_get_contents($filepath);
$original_size = strlen($content);

// Count occurrences
$count = 0;
$content = str_ireplace('utf8mb4_0900_ai_ci', 'utf8mb4_general_ci', $content, $count);

echo "Replaced $count occurrences of 'utf8mb4_0900_ai_ci' with 'utf8mb4_general_ci'.\n";

if ($count > 0) {
    echo "Saving file...\n";
    if (file_put_contents($filepath, $content) !== false) {
        echo "File saved successfully. New size: " . strlen($content) . " bytes.\n";
    } else {
        echo "Error saving file.\n";
    }
} else {
    echo "No occurrences found, file not modified.\n";
}
