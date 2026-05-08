<?php

class BaseController
{

  protected $layout = 'main';

  protected function isValidCSRF()
  {
    if (!isValidCsrfToken($_POST['csrf_token'] ?? '')) {
      return true;
      http_response_code(403);
      die('Invalid CSRF token');
    }
    return false;
  }

  protected function render($viewPath, $data = [])
  {
    extract($data);

    $view = BASE_PATH . "/views/" . $viewPath . ".php";

    if (!file_exists($view)) {
      die("View not found: " . $viewPath);
    }

    $layoutPath = BASE_PATH . "/views/layouts/{$this->layout}.php";

    require $layoutPath;
  }

  protected function redirect($url)
  {
    header("Location: $url");
    exit;
  }
}
