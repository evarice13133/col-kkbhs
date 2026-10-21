<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use App\Core\Database;

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only.\n");
}

if ($argc !== 2 || trim($argv[1]) === '') {
    exit("Usage: php scripts/reset_user_password.php <username>\n");
}

function readHiddenInput(string $prompt): string
{
    if (PHP_OS_FAMILY === 'Windows') {
        $powershell = <<<'POWERSHELL'
$secure = Read-Host -Prompt 'Saisie masquée' -AsSecureString
$bstr = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure)
try {
    [Console]::Out.Write([Runtime.InteropServices.Marshal]::PtrToStringBSTR($bstr))
} finally {
    [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($bstr)
}
POWERSHELL;
        $encodedCommand = iconv('UTF-8', 'UTF-16LE', $powershell);
        if ($encodedCommand === false) {
            throw new RuntimeException('Impossible de préparer la saisie sécurisée PowerShell.');
        }
        $command = [
            'powershell.exe',
            '-NoProfile',
            '-EncodedCommand',
            base64_encode($encodedCommand),
        ];
        $process = proc_open(
            $command,
            [
                0 => ['file', 'CONIN$', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );
        if (!is_resource($process)) {
            throw new RuntimeException('Impossible d’ouvrir la saisie sécurisée PowerShell.');
        }

        fwrite(STDOUT, $prompt . PHP_EOL);
        $value = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);
        if ($exitCode !== 0) {
            throw new RuntimeException(
                'La saisie sécurisée a échoué.' . ($error !== '' ? ' ' . trim($error) : '')
            );
        }

        return (string) $value;
    }

    fwrite(STDOUT, $prompt . ' (saisie masquée) : ');
    if (function_exists('shell_exec')) {
        shell_exec('stty -echo');
    }
    try {
        $value = fgets(STDIN);
    } finally {
        if (function_exists('shell_exec')) {
            shell_exec('stty echo');
        }
        fwrite(STDOUT, PHP_EOL);
    }

    if ($value === false) {
        throw new RuntimeException('Aucune valeur n’a été saisie.');
    }

    return rtrim($value, "\r\n");
}

$username = trim($argv[1]);
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare('SELECT id, username, status FROM users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    exit("Utilisateur introuvable. Aucun changement effectué.\n");
}
if (isset($user['status']) && (int) $user['status'] !== 1) {
    exit("Le compte est désactivé. Aucun changement effectué.\n");
}

try {
    $password = readHiddenInput('Nouveau mot de passe');
    $confirmation = readHiddenInput('Confirmer le mot de passe');

    if ($password !== $confirmation) {
        exit("Les mots de passe ne correspondent pas. Aucun changement effectué.\n");
    }
    if (strlen($password) < 12) {
        exit("Le mot de passe doit contenir au moins 12 caractères. Aucun changement effectué.\n");
    }
    if (!in_array('argon2id', password_algos(), true)) {
        throw new RuntimeException('Argon2id n’est pas disponible dans cette installation PHP.');
    }

    $hash = password_hash($password, PASSWORD_ARGON2ID);
    if ($hash === false) {
        throw new RuntimeException('Le hachage du mot de passe a échoué.');
    }

    $update = $db->prepare('UPDATE users SET password = ? WHERE id = ? AND status = 1');
    $update->execute([$hash, (int) $user['id']]);
    if ($update->rowCount() !== 1) {
        throw new RuntimeException('Le mot de passe n’a pas été modifié.');
    }

    echo "Mot de passe réinitialisé pour le compte actif « {$username} ».\n";
    echo "Connectez-vous avec le nom d’utilisateur, puis supprimez ce script après usage.\n";
} catch (Throwable $error) {
    fwrite(STDERR, 'Échec de la réinitialisation : ' . $error->getMessage() . PHP_EOL);
    exit(1);
} finally {
    if (isset($password)) {
        $password = '';
    }
    if (isset($confirmation)) {
        $confirmation = '';
    }
    if (isset($hash)) {
        $hash = '';
    }
}
