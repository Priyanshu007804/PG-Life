<?php
// Production & Development Database Connection
// Supports:
// 1. Single DATABASE_URL / MYSQL_URL (common in Render, Railway, Aiven)
// 2. Individual environment variables (DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT)
// 3. Fallback to local Docker / XAMPP defaults
// 4. SSL connection support (DB_SSL=true) for managed cloud databases (Aiven, TiDB, Clever Cloud)

// Auto-load .env file if present
$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $key = trim($key);
            $val = trim(trim($val), "\"'");
            if (getenv($key) === false) {
                putenv("{$key}={$val}");
                $_ENV[$key] = $val;
                $_SERVER[$key] = $val;
            }
        }
    }
}

$db_url = getenv('DATABASE_URL') ?: getenv('MYSQL_URL');

if ($db_url) {
    $parsed_url = parse_url($db_url);
    $db_host = $parsed_url['host'] ?? '127.0.0.1';
    $db_user = $parsed_url['user'] ?? 'root';
    $db_pass = $parsed_url['pass'] ?? '';
    $db_name = isset($parsed_url['path']) ? ltrim($parsed_url['path'], '/') : 'pglife';
    $db_port = (int)($parsed_url['port'] ?? 3306);
} else {
    $db_host = getenv('DB_HOST') ?: '127.0.0.1';
    $db_user = getenv('DB_USER') ?: 'root';
    $db_pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';
    $db_name = getenv('DB_NAME') ?: 'pglife';
    $db_port = (int)(getenv('DB_PORT') ?: 3306);
}

$db_ssl = getenv('DB_SSL') === 'true' || getenv('DB_SSL') === '1';

$conn = mysqli_init();
if (!$conn) {
    die("mysqli_init failed");
}

if ($db_ssl) {
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
    mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}

// 5-second connection timeout for cloud networks
mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 5);

$connected = @mysqli_real_connect(
    $conn,
    $db_host,
    $db_user,
    $db_pass,
    $db_name,
    $db_port,
    NULL,
    $db_ssl ? MYSQLI_CLIENT_SSL : 0
);

if (!$connected) {
    echo "Database connection error: " . mysqli_connect_error();
    exit;
}
