<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'enrollment_db');
define('DB_USER', 'root');
define('DB_PASS', 'Root@123');

function getPDO()
{
  static $pdo = null;

  if ($pdo !== null) {
    return $pdo;
  }

  try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

    $pdo = new PDO(
      $dsn,
      DB_USER,
      DB_PASS,
      [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
      ]
    );

    return $pdo;
  } catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
  }
}
