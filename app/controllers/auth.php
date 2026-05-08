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
    $this->authModel = new AuthModel();
    $this->permissionModel = new PermissionModel();
  }

  public function index()
  {
    return $this->redirect("/auth/login");
  }

  public function login()
  {
    redirectIfLoggedIn();

    $this->ensureCsrf();

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      if ($this->validateCsrfOrFail())
        $errors['csrf_token'] = 'Invalid CSRF token';

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
        ]);
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

    return $this->render('auth/login', []);
  }

  public function captcha()
  {
    $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';
    for ($i = 0; $i < 6; $i++) {
      $code .= $characters[random_int(0, strlen($characters) - 1)];
    }
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
      imagestring($image, 15, $x, $y, $code[$i], $text);
      $x += 18;
    }

    imagepng($image);
    imagedestroy($image);
    exit;
  }

  public function logout()
  {
    $_SESSION = [];
    session_destroy();

    return $this->redirect("/auth/login");
  }

  public function profile()
  {
    requireLogin();

    Rbac::require('profile.view');

    $this->layout = 'main';
    $this->ensureCsrf();

    $userId = $_SESSION['user']['id'];

    $profile = $this->authModel->findUser($userId);

    if (!$profile) {
      setFlash('error', 'Profile not found');
      return $this->redirect('/dashboard');
    }

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if ($this->validateCsrfOrFail())
        $errors['csrf_token'] = 'Invalid CSRF token';

      $formType = $_POST['form_type'] ?? 'profile';

      if ($formType === 'password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($currentPassword === '') {
          $errors['current_password'] = 'Current password is required';
        }

        if (strlen($newPassword) < 6) {
          $errors['new_password'] = 'New password must be at least 6 characters';
        }

        if ($newPassword !== $confirmPassword) {
          $errors['confirm_password'] = 'Password confirmation does not match';
        }

        if (empty($errors) && !$this->authModel->changePassword($userId, $currentPassword, $newPassword)) {
          $errors['current_password'] = 'Current password is incorrect';
        }

        if (empty($errors)) {
          setFlash('success', 'Password updated');
          return $this->redirect('/auth/profile');
        }
      } else {
        Rbac::require('profile.edit');

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $enrolledOn = trim($_POST['enrolled_on'] ?? '');

        if ($name === '') {
          $errors['name'] = 'Name is required';
        }

        if ($email === '') {
          $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $errors['email'] = 'Invalid email address';
        } elseif ($this->authModel->emailExistsForOtherUser($email, $userId)) {
          $errors['email'] = 'Email is already in use';
        }

        if ($phone !== '' && !preg_match('/^[0-9+\\-\\s]{7,20}$/', $phone)) {
          $errors['phone'] = 'Phone number format is invalid';
        }

        if ($enrolledOn !== '' && !strtotime($enrolledOn)) {
          $errors['enrolled_on'] = 'Enrolled date is invalid';
        }

        if (empty($errors)) {
          $beforeProfile = [
            'name' => $profile['name'],
            'email' => $profile['email'],
            'phone' => $profile['phone'] ?? null,
            'enrolled_on' => $profile['enrolled_on'] ?? null
          ];
          $afterProfile = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'enrolled_on' => $enrolledOn
          ];

          $this->authModel->updateProfile($userId, [
            'name' => $name,
            'email' => $email,
            'role' => $profile['role'],
            'phone' => $phone,
            'enrolled_on' => $enrolledOn
          ]);

          $_SESSION['user']['name'] = $name;

          setFlash('success', 'Profile updated');
          return $this->redirect('/auth/profile');
        }
      }
    }

    return $this->render('auth/profile', compact('profile', 'errors'));
  }
}
