<?php

class BaseController
{

  protected $layout = 'main';

  protected function ensureCsrf()
  {
    ensureCsrfToken();
  }

  protected function validateCsrfOrFail()
  {
    if (!isValidCsrfToken($_POST['csrf_token'] ?? '')) {
      http_response_code(403);
      die('Invalid CSRF token');
    }
  }

  protected function render($viewPath, $data = [])
  {
    $this->ensureCsrf();

    extract($data);
    // dd($data);

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
