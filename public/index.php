<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

define('ROOT_PATH', __DIR__);
define('BASE_PATH', __DIR__ . "/..");

require BASE_PATH . "/config/database.php";
require BASE_PATH . "/utils/helper.php";
require BASE_PATH . "/app/core/BaseController.php";
require BASE_PATH . "/app/services/Rbac.php";
require BASE_PATH . "/app/services/QueryBuilder.php";
require BASE_PATH . "/app/services/Mailer.php";

session_start();

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request = trim($request, '/');
$parts = $request ? explode('/', $request) : [];

$module = $parts[0] ?? 'auth';
$action = $parts[1] ?? 'index';
$id     = $parts[2] ?? null;

$file = "../app/controllers/{$module}.php";

if (!file_exists($file)) {
  die("Module not found");
}

$publicRoutes = ['auth'];

if (!in_array($module, $publicRoutes)) {
  requireLogin();
}

require $file;

$class = ucfirst($module);

if (!class_exists($class)) {
  die("Controller class missing");
}

$controller = new $class();

if (!method_exists($controller, $action)) {
  die("Action not found");
}

if ($id !== null) {
  if (ctype_digit((string)$id) && (int)$id > 0) {
    $controller->$action((int)$id);
  } else {
    die("Invalid ID");
  }
} else {
  $controller->$action();
}
