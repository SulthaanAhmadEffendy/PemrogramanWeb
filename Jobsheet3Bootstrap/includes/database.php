<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$configPath = __DIR__ . '/config.local.php';
$localConfig = is_file($configPath) ? require $configPath : [];
$localConfig = is_array($localConfig) ? $localConfig : [];

$readDatabaseConfig = static function (string $key, string $environmentVariable, string $default) use ($localConfig): string {
    $environmentValue = getenv($environmentVariable);
    if ($environmentValue !== false && $environmentValue !== '') {
        return $environmentValue;
    }

    return (string) ($localConfig[$key] ?? $default);
};

$host = $readDatabaseConfig('host', 'DB_HOST', '127.0.0.1');
$port = $readDatabaseConfig('port', 'DB_PORT', '3306');
$database = $readDatabaseConfig('database', 'DB_DATABASE', 'simpus_mini');
$username = $readDatabaseConfig('username', 'DB_USERNAME', 'root');
$password = $readDatabaseConfig('password', 'DB_PASSWORD', '');

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $exception) {
    error_log('[Jobsheet3Bootstrap] MySQL connection failed: ' . $exception->getMessage());
    http_response_code(500);
    exit('Koneksi database gagal. Periksa konfigurasi MySQL.');
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(): bool
{
    $submittedToken = $_POST['csrf_token'] ?? '';
    return is_string($submittedToken)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $submittedToken);
}