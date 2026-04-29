<?php

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
