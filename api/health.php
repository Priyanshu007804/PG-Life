<?php
// Temporary health check — remove after debugging
header('Content-Type: application/json');

$checks = array(
    "DB_HOST_set" => getenv('DB_HOST') !== false && getenv('DB_HOST') !== '',
    "DB_USER_set" => getenv('DB_USER') !== false && getenv('DB_USER') !== '',
    "DB_PASSWORD_set" => getenv('DB_PASSWORD') !== false && getenv('DB_PASSWORD') !== '',
    "DB_NAME_set" => getenv('DB_NAME') !== false && getenv('DB_NAME') !== '',
    "DB_PORT_set" => getenv('DB_PORT') !== false && getenv('DB_PORT') !== '',
    "DB_SSL_set" => getenv('DB_SSL') !== false && getenv('DB_SSL') !== '',
    "DATABASE_URL_set" => getenv('DATABASE_URL') !== false && getenv('DATABASE_URL') !== '',
    "php_version" => phpversion(),
    "mysqli_available" => extension_loaded('mysqli'),
);

// Try connecting
require("../includes/database_connect.php");
$checks["db_connected"] = true;

// Check if users table exists
$table_check = $conn->query("SHOW TABLES LIKE 'users'");
$checks["users_table_exists"] = $table_check && $table_check->num_rows > 0;

echo json_encode($checks, JSON_PRETTY_PRINT);

