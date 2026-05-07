<?php

function loadEnvFile($path)
{
  static $loaded = false;

  if ($loaded || !is_file($path) || !is_readable($path)) {
    return;
  }

  $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

  foreach ($lines as $line) {
    $line = trim($line);

    if ($line === '' || str_starts_with($line, '#') || strpos($line, '=') === false) {
      continue;
    }

    [$key, $value] = explode('=', $line, 2);

    $key = trim($key);
    $value = trim($value);

    if ($key === '' || getenv($key) !== false) {
      continue;
    }

    if (
      (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
      (str_starts_with($value, "'") && str_ends_with($value, "'"))
    ) {
      $value = substr($value, 1, -1);
    }

    putenv($key . '=' . $value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
  }

  $loaded = true;
}

loadEnvFile(BASE_PATH . '/.env');

function envOrDefault($key, $default)
{
  $value = getenv($key);
  if ($value === false || $value === '') {
    return $default;
  }

  return $value;
}

define('DB_HOST', envOrDefault('DB_HOST', '127.0.0.1'));
define('DB_PORT', envOrDefault('DB_PORT', '3306'));
define('DB_NAME', envOrDefault('DB_NAME', 'enrollment_db'));
define('DB_USER', envOrDefault('DB_USER', 'root'));
define('DB_PASS', envOrDefault('DB_PASS', 'Root@123'));
define('DB_SOCKET', envOrDefault('DB_SOCKET', ''));
define('MAIL_HOST', envOrDefault('MAIL_HOST', ''));
define('MAIL_PORT', envOrDefault('MAIL_PORT', '587'));
define('MAIL_USERNAME', envOrDefault('MAIL_USERNAME', ''));
define('MAIL_PASSWORD', envOrDefault('MAIL_PASSWORD', ''));
define('MAIL_ENCRYPTION', envOrDefault('MAIL_ENCRYPTION', 'tls'));
define('MAIL_FROM_ADDRESS', envOrDefault('MAIL_FROM_ADDRESS', ''));
define('MAIL_FROM_NAME', envOrDefault('MAIL_FROM_NAME', 'Course Enrollment System'));

function getPDO()
{
  static $pdo = null;

  if ($pdo !== null) {
    return $pdo;
  }

  try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";

    if (DB_SOCKET !== '') {
      $dsn = "mysql:unix_socket=" . DB_SOCKET . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    }

    $pdo = new PDO(
      $dsn,
      DB_USER,
      DB_PASS,
      [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => true
      ]
    );

    return $pdo;
  } catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
  }
}
