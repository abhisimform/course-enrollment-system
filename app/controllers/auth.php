<?php

require_once BASE_PATH . '/app/models/Auth.php';
require_once BASE_PATH . '/app/models/Permission.php';

class Auth extends BaseController
{
  private $authModel;
  private $permissionModel;
  private $validator;

  protected $layout = 'public';

  public function __construct()
  {
    $this->permissionModel = new PermissionModel();
    $this->authModel = new AuthModel();
    $this->validator = new Validator();;
  }

  public function index()
  {
    return $this->redirect("/auth/login");
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

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $this->validateCsrf();

      $email = trim($_POST['email'] ?? '');
      $password = $_POST['password'] ?? '';
      $captcha = trim($_POST['captcha'] ?? '');

      if (empty($email)) {
        $errors[] = 'Email is required';
      }

      if (empty($password)) {
        $errors[] = 'Password is required';
      }

      if (empty($captcha)) {
        $errors[] = 'Captcha is required';
      } elseif (
        empty($_SESSION['captcha_code']) ||
        strcasecmp($_SESSION['captcha_code'], $captcha) !== 0
      ) {
        $errors[] = 'Invalid captcha';
      }

      if (empty($errors)) {
        $user = $this->authModel->login($email, $password);

        if (!$user) {
          $errors[] = 'Invalid email or password';
        }
      }

      if (!empty($errors)) {
        return $this->render('auth/login', [
          'errors' => $errors,
          'old' => ['email' => $email]
        ], 'public');
      }

      session_regenerate_id(true);
      unset($_SESSION['captcha_code']);

      $_SESSION['user'] = [
        'id'   => $user['id'],
        'name' => $user['name'],
        'role' => $user['role']
      ];

      $_SESSION['permissions'] =
        $this->permissionModel->getUserPermissions($user['id']);

      setFlash('success', 'Login successful!');

      return $this->redirect('/dashboard');
    }

    return $this->render('auth/login', [], 'public');
  }

  public function captcha()
  {
    $code = substr(str_shuffle('123'), 0, 5);
    $code = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 5);
    $_SESSION['captcha_code'] = $code;

    header('Content-type: image/png');

    $image = imagecreate(120, 40);
    $bgColor = imagecolorallocate($image, 255, 255, 255);
    $text = imagecolorallocate($image, 0, 0, 0);
    $line = imagecolorallocate($image, 120, 120, 120);

    imagefilledrectangle($image, 0, 0, 130, 45, $bgColor);

    for ($i = 0; $i < 5; $i++) {
      imageline($image, rand(0, 120), rand(0, 40), rand(0, 120), rand(0, 40), $line);
    }

    for ($i = 0; $i < 300; $i++) {
      imagesetpixel($image, rand(0, 130), rand(0, 45), $line);
    }

    $x = 5;

    for ($i = 0; $i < strlen($code); $i++) {
      $y = rand(10, 25);
      imagestring($image, 2, $x, $y, $code[$i], $text);
      $x += 22;
    }

    imagepng($image);
    imagedestroy($image);
    exit;
  }

  public function register()
  {
    redirectIfLoggedIn();

    $this->ensureCsrf();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $this->validateCsrf();

      $rules = AuthValidator::register();

      if (!$this->validator->validate($_POST, $rules)) {
        $errors = $this->validator->errors();

        return $this->redirect("/auth/register");
      }

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
