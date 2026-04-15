<?php

require_once BASE_PATH . "/app/models/Student.php";

class Students
{
  private $studentModel;

  public function __construct()
  {
    requireLogin();

    $this->studentModel = new StudentModel();
  }

  public function index()
  {
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = 10;

    $search = $_GET['search'] ?? '';
    $showDeleted = isset($_GET['deleted']) ? true : false;

    $students = $this->studentModel->getStudents($search, $currentPage, $perPage, $showDeleted);
    $totalStudents = $this->studentModel->countStudents($search, $showDeleted);
    $totalPages = ceil($totalStudents / $perPage);

    $view = BASE_PATH . "/views/students/index.php";
    require BASE_PATH . "/views/layouts/main.php";
  }

  public function restore($id)
  {
    if (Rbac::has('restore_student')) die("Access denied");
    $this->studentModel->restore($id);
    setFlash('success', 'Student restored');
    header("Location: /students?deleted=1");
    exit;
  }

  public function create()
  {
    if (Rbac::has('create_student')) {
      die("Access denied");
    }

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $name = trim($_POST['name'] ?? '');
      $email = trim($_POST['email'] ?? '');
      $password = $_POST['password'] ?? '';

      if ($name === '')
        $errors['name'] = 'Name is required';

      if ($email === '')
        $errors['email'] = 'Email is required';
      elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors['email'] = 'Invalid email';
      elseif ($this->studentModel->emailExists($email))
        $errors['email'] = 'Email already in use';

      if ($password === '')
        $errors['password'] = 'Password is required';
      elseif (strlen($password) < 6)
        $errors['password'] = 'Password must be at least 6 characters';

      if (empty($errors)) {
        $this->studentModel->create([
          'name' => $name,
          'email' => $email,
          'password' => $password
        ]);

        setFlash('success', 'Student created');
        header("Location: /students");
        exit;
      }
    }

    $view = BASE_PATH . "/views/students/create.php";
    require BASE_PATH . "/views/layouts/main.php";
  }

  public function view($id)
  {
    $student = $this->studentModel->find($id);

    if (!$student) {
      die("Student not found");
    }

    require BASE_PATH . "/views/students/view.php";
  }

  public function edit($id)
  {
    if (Rbac::has('edit_student')) {
      die("Access denied");
    }

    $student = $this->studentModel->find($id);
    if (!$student) die("Student not found");

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $name = trim($_POST['name'] ?? '');
      $email = trim($_POST['email'] ?? '');
      $password = $_POST['password'] ?? '';

      if ($name === '')
        $errors['name'] = 'Name is required';

      if ($email === '')
        $errors['email'] = 'Email is required';
      elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors['email'] = 'Invalid email';
      elseif ($email !== $student['email'] && $this->studentModel->emailExists($email)) {
        $errors['email'] = 'Email already in use';
      }

      if ($password !== '' && strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
      }

      if (empty($errors)) {
        $this->studentModel->update($id, [
          'name' => $name,
          'email' => $email,
          'password' => $password
        ]);

        setFlash('success', 'Student updated');
        header("Location: /students/view/$id");
        exit;
      }
    }

    $view = BASE_PATH . "/views/students/edit.php";
    require BASE_PATH . "/views/layouts/main.php";
  }

  public function delete($id)
  {
    if (Rbac::has('delete_student')) {
      die("Access denied");
    }

    $this->studentModel->softDelete($id);

    setFlash('success', 'Student deleted');
    header("Location: /students");
    exit;
  }
}
