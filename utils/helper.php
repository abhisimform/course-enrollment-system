<?php

function dd(...$vars)
{
  $isAjax = (
    !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
  ) || (
    isset($_SERVER['HTTP_ACCEPT']) &&
    strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
  );

  $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
  $line = file($backtrace[0]['file'])[$backtrace[0]['line'] - 1] ?? '';
  preg_match('/dd\((.+)\)/', $line, $matches);
  $names = isset($matches[1]) ? explode(',', $matches[1]) : [];

  if ($isAjax) {
    header('Content-Type: application/json');

    $output = [];

    foreach ($vars as $index => $var) {
      $name = isset($names[$index]) ? trim($names[$index]) : "var_" . ($index + 1);
      $output[$name] = $var;
    }

    echo json_encode([
      'debug' => $output
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    die();
  }

  echo '
    <style>
      .dd-container {
        font-family: monospace;
      }
      .dd-accordion {
        background: #f4f4f4;
        border: 1px solid #ddd;
        margin-bottom: 5px;
        border-radius: 4px;
      }
      .dd-header {
        padding: 10px;
        cursor: pointer;
        background: #e2e2e2;
        font-weight: bold;
      }
      .dd-content {
        display: none;
        padding: 10px;
        border-top: 1px solid #ddd;
        background: #fff;
      }
    </style>

    <div class="dd-container">
    ';

  foreach ($vars as $index => $var) {
    $name = isset($names[$index]) ? trim($names[$index]) : "Variable " . ($index + 1);

    echo '
        <div class="dd-accordion">
            <div class="dd-header"
              onclick="this.nextElementSibling.style.display =
              this.nextElementSibling.style.display === \'block\' ? \'none\' : \'block\'">
              ' . e($name) . '
            </div>
            <div class="dd-content">
              <pre>';
    print_r($var);
    echo '</pre>
            </div>
        </div>';
  }

  echo '</div>';

  die();
}

function e($data)
{
  return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
}

function isLoggedIn()
{
  return !empty($_SESSION['user']);
}

function requireLogin()
{
  if (!isLoggedIn()) {
    header("Location: /auth/login");
    exit;
  }
}

function redirectIfLoggedIn()
{
  if (isLoggedIn()) {
    header("Location: /dashboard");
    exit;
  }
}

function requireAdmin()
{
  if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Access denied");
  }
}

function setFlash($type, $message)
{
  $_SESSION['flash'] = [
    'type' => $type,
    'message' => $message
  ];
}

function getFlash()
{
  if (!isset($_SESSION['flash'])) return null;

  $flash = $_SESSION['flash'];
  unset($_SESSION['flash']);

  return $flash;
}

function ensureCsrfToken()
{
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }

  return $_SESSION['csrf_token'];
}

function newCSRFToken()
{
  return ensureCsrfToken();
}

function csrfInput()
{
  return '<input type="hidden" name="csrf_token" value="' . e(newCSRFToken()) . '">';
}

function isValidCsrfToken($token)
{
  $sessionToken = $_SESSION['csrf_token'] ?? '';
  $submittedToken = is_string($token) ? $token : '';

  return $sessionToken !== '' && $submittedToken !== '' && hash_equals($sessionToken, $submittedToken);
}

function redirectBack($fallback = '/dashboard')
{
  $target = $_SERVER['HTTP_REFERER'] ?? $fallback;
  header("Location: $target");
  exit;
}

function postActionLink($text, $url, $confirm = '')
{
  $id = 'f_' . uniqid();

  $html = '<form id="' . $id . '" method="POST" action="' . $url . '" style="display:inline;">';
  $html .= csrfInput();
  $html .= '</form>';

  $onclick = '';

  if ($confirm) {
    $onclick = "if(!confirm('$confirm')) return false;";
  }

  $onclick .= "document.getElementById('$id').submit(); return false;";

  $html .= '<a href="#" onclick="' . $onclick . '">' . $text . '</a>';

  return $html;
}

function isPOSTRequest()
{
  return $_SERVER['REQUEST_METHOD'] === 'POST';
}
