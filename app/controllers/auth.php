<?php

session_start();

class Auth
{
  private $authModel;

  public function __construct()
  {
    require_once BASE_PATH . '/app/models/Auth.php';
    $this->authModel = new AuthModel();
  }

  public function login()
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $email = $_POST['email'];
      $password = $_POST['password'];

      $user = $this->authModel->login($email, $password);

      if (!$user) {
        setFlash('error', 'Invalid email or password');
        require BASE_PATH . '/views/auth/login.php';
        return;
      }

      $_SESSION['user'] = [
        'id'    => $user['id'],
        'name'  => $user['name'],
        'role'  => $user['role']
      ];

      $_SESSION['permissions'] = $this->authModel->getPermissions($user['id']);

      if ($user['role'] === 'admin') {
        setFlash('success', 'Login successful!');
        header("Location: /dashboard");
      } else {
        header("Location: /students/list");
      }

      exit;
    }

    require BASE_PATH . '/views/auth/login.php';
  }

  public function logout()
  {
    session_destroy();
    header("Location: /auth/login");
    exit;
  }

  public function register()
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $this->authModel->register($_POST);

      header("Location: /auth/login");
      exit;
    }

    require BASE_PATH . '/views/auth/register.php';
  }
}
