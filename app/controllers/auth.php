<?php

class Auth
{
  private $authModel;

  public function __construct()
  {
    require_once BASE_PATH . '/app/models/Auth.php';
    require_once BASE_PATH . '/utils/helper.php';

    session_start();

    $this->authModel = new AuthModel();
  }

  public function login()
  {
    if (!empty($_SESSION['user'])) {
      header("Location: /dashboard");
      exit;
    }

    if (empty($_SESSION['csrf_token'])) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
      }

      $email = trim($_POST['email']);
      $password = $_POST['password'];

      $user = $this->authModel->login($email, $password);

      if (!$user) {
        setFlash('error', 'Invalid email or password');
        require BASE_PATH . '/views/auth/login.php';
        return;
      }

      session_regenerate_id(true);

      $_SESSION['user'] = [
        'id'    => $user['id'],
        'name'  => $user['name'],
        'role'  => $user['role']
      ];

      $_SESSION['permissions'] = $this->authModel->getPermissions($user['id']);

      setFlash('success', 'Login successful!');

      if ($user['role'] === 'admin') {
        header("Location: /dashboard");
      } else {
        header("Location: /students");
      }

      exit;
    }

    require BASE_PATH . '/views/auth/login.php';
  }

  public function logout()
  {
    session_start();

    $_SESSION = [];
    session_destroy();

    header("Location: /auth/login");
    exit;
  }

  public function register()
  {
    if (!empty($_SESSION['user'])) {
      header("Location: /dashboard");
      exit;
    }

    if (empty($_SESSION['csrf_token'])) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
      }

      $this->authModel->register($_POST);

      setFlash('success', 'Registered successfully! Please login.');
      header("Location: /auth/login");
      exit;
    }

    require BASE_PATH . '/views/auth/register.php';
  }
}
