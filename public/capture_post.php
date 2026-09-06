<?php
// Capture the POST data sent by the form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = array_merge($_POST, $_GET);
  
  $output = "=== CAPTURED POST DATA ===\n\n";
  $output .= "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
  
  $output .= "Fields:\n";
  foreach ($data as $key => $value) {
    if (is_array($value)) {
      $output .= "  " . $key . " = [" . implode(", ", $value) . "]\n";
    } else {
      $output .= "  " . $key . " = " . $value . "\n";
    }
  }
  
  // Write to file for later inspection
  file_put_contents('/tmp/form_capture.txt', $output, FILE_APPEND);
  
  echo $output;
  echo "\n[Data saved to /tmp/form_capture.txt]\n";
} else {
  echo "Not a POST request\n";
}
?>
