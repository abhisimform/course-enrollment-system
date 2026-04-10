<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

define('ROOT_PATH', __DIR__);
define('BASE_PATH', __DIR__ . "/..");

require BASE_PATH . "/config/database.php";
require BASE_PATH . "/utils/helper.php";

// dd(ROOT_PATH, BASE_PATH);

$request = trim($_SERVER['REQUEST_URI'], '/');
$parts = $request ? explode('/', $request) : [];

// dd($request, $parts);

/*
URL structure:
0 => module
1 => action
2 => id (optional)
*/

$module = $parts[0] ?? 'auth';
$action = $parts[1] ?? 'index';
$id     = $parts[2] ?? null;

$file = "../app/controllers/{$module}.php";

if (!file_exists($file)) {
  die("Module not found");
}

require $file;

$class = ucfirst($module);

if (!class_exists($class)) {
  die("Controller class missing");
}

$controller = new $class();

/*
Call method dynamically
students/list → Student::list()
students/view/1 → Student::view(1)
*/

if (!method_exists($controller, $action)) {
  die("Action not found");
}

$controller->$action($id);
