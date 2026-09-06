<?php
$base = 'http://127.0.0.1:8000';
$jar = __DIR__ . '/subject_test_cookies.txt';
@unlink($jar);

function http($url, $method = 'GET', $headers = [], $body = null, $cookieFile = null) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_NOBODY => false,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CUSTOMREQUEST => $method,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    $raw = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    if ($errno) {
        throw new RuntimeException("cURL error for $url: $errno - $error");
    }

    $headerSize = $info['header_size'];
    $headersStr = substr($raw, 0, $headerSize);
    $bodyStr = substr($raw, $headerSize);
    return ['status' => $info['http_code'], 'headers' => $headersStr, 'body' => $bodyStr, 'url' => $info['url']];
}

// 1) Login page
$loginPage = http($base . '/login', 'GET', [], null, $jar);
if ($loginPage['status'] !== 200) {
    throw new RuntimeException('Login page not reachable: ' . $loginPage['status']);
}
if (!preg_match('/name="csrf_token"[^>]*value="([^"]+)"/i', $loginPage['body'], $m)) {
    throw new RuntimeException('CSRF token not found on login page');
}
$loginCsrf = $m[1];

// 2) Login POST
$loginBody = http_build_query([
    'username' => 'admin',
    'password' => '0000',
    'csrf_token' => $loginCsrf,
]);
$loginResult = http($base . '/login', 'POST', ['Content-Type: application/x-www-form-urlencoded'], $loginBody, $jar);
if ($loginResult['status'] !== 302 && !str_contains($loginResult['headers'], 'Location: /')) {
    echo "LOGIN_RESULT_STATUS={$loginResult['status']}\n";
    echo $loginResult['headers'] . "\n";
    echo substr($loginResult['body'], 0, 1200) . "\n";
    exit(1);
}

// 3) Create page
$createPage = http($base . '/subjects/create', 'GET', [], null, $jar);
if ($createPage['status'] !== 200) {
    throw new RuntimeException('Create page not reachable: ' . $createPage['status']);
}
if (!preg_match('/name="csrf_token"[^>]*value="([^"]+)"/i', $createPage['body'], $m2)) {
    throw new RuntimeException('CSRF token not found on create page');
}
$createCsrf = $m2[1];

$subjectName = 'REAL_HTTP_TEST_' . time();
$postFields = [
    'csrf_token' => $createCsrf,
    'nom' => $subjectName,
    'coefficient' => '2',
    'teaching_type_id' => '3',
    'teaching_form_id' => '2',
    'subject_group_id' => '1',
    'vhm' => '20',
    'vhp' => '18',
    'th_max' => '25',
    'observations' => 'validation from real HTTP test',
    'classes[]' => ['16', '15'],
    'competencies[]' => ['Compétence A', 'Compétence B'],
];

$storeResult = http($base . '/subjects/store', 'POST', ['Content-Type: application/x-www-form-urlencoded'], http_build_query($postFields), $jar);

echo "STORE_STATUS={$storeResult['status']}\n";
echo "STORE_HEADERS:\n" . $storeResult['headers'] . "\n";
echo "STORE_BODY_SNIPPET:\n" . substr($storeResult['body'], 0, 1500) . "\n";

// 4) DB check
$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$rows = $pdo->prepare('SELECT id, nom, subject_group_id, teaching_type_id, teaching_form_id FROM subjects WHERE nom = :name ORDER BY id DESC LIMIT 10');
$rows->execute([':name' => $subjectName]);
$matches = $rows->fetchAll();

echo "DB_MATCHES=" . json_encode($matches, JSON_UNESCAPED_UNICODE) . "\n";
