<?php

require_once BASE_PATH . '/app/models/Auth.php';
require_once BASE_PATH . '/app/models/Permission.php';

class Auth extends BaseController
{
  private $authModel;
  private $permissionModel;

  protected $layout = 'public';

  public function __construct()
  {
    $this->permissionModel = new PermissionModel();
    $this->authModel = new AuthModel();
  }

  private function ensureCsrf()
  {
    if (empty($_SESSION['csrf_token'])) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
  }

  private function validateCsrf()
  {
    if (
      !isset($_POST['csrf_token']) ||
      !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
      die("Invalid CSRF token");
    }
  }

  public function login()
  {
    redirectIfLoggedIn();

    $this->ensureCsrf();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $this->validateCsrf();

      $email = trim($_POST['email'] ?? '');
      $password = $_POST['password'] ?? '';

      $user = $this->authModel->login($email, $password);

      if (!$user) {
        setFlash('error', 'Invalid email or password');
        return $this->render('auth/login', [], 'public');
      }

      session_regenerate_id(true);

      $_SESSION['user'] = [
        'id'   => $user['id'],
        'name' => $user['name'],
        'role' => $user['role']
      ];

      $_SESSION['permissions'] =
        $this->permissionModel->getUserPermissions($user['id']);

      setFlash('success', 'Login successful!');

      return $this->redirect(
        $user['role'] === 'admin' ? '/dashboard' : '/students'
      );
    }

    return $this->render('auth/login', [], 'public');
  }

  public function register()
  {
    redirectIfLoggedIn();

    $this->ensureCsrf();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $this->validateCsrf();

      $this->authModel->register($_POST);

      setFlash('success', 'Registered successfully! Please login.');

      return $this->redirect("/auth/login");
    }

    return $this->render('auth/register', [], 'public');
  }

  public function logout()
  {
    session_start();

    $_SESSION = [];
    session_destroy();

    return $this->redirect("/auth/login");
  }
}
