<?php

class Teachers
{

  private $teacherModel;

  public function __construct()
  {
    require_once BASE_PATH . "/app/models/Teacher.php";
    require_once BASE_PATH . "/utils/helper.php";

    // dd(1);
    requireLogin();

    $this->teacherModel = new TeacherModel();
  }

  public function index()
  {

    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = 10;

    $search = $_GET['search'] ?? '';
    
    $showDeleted = isset($_GET['deleted']) ? true : false;

    $teachers = $this->teacherModel->getTeachers($search, $currentPage, $perPage, $showDeleted);
    $totalTeachers = $this->teacherModel->countTeachers($search, $showDeleted);
    $totalPages = ceil($totalTeachers / $perPage);

    $view = BASE_PATH . "/views/teachers/index.php";
    require BASE_PATH . "/views/layouts/main.php";
  }

  public function restore($id)
  {
    if (!hasPermission('restore_teacher')) die("Access denied");
    $this->teacherModel->restore($id);
    setFlash('success', 'Teacher restored');
    header("Location: /teachers?deleted=1");
    exit;
  }

  public function create()
  {
    if (!hasPermission('create_teacher')) {
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
        $errors['email'] = 'Invalid email formate';
      elseif ($this->teacherModel->emailExists($email))
        $errors['email'] = 'Email already in use';

      if ($password === '')
        $errors['password'] = 'Password is required';
      elseif (strlen($password) < 6)
        $errors['password'] = 'Password must be at least 6 characters';

      if (empty($errors)) {
        $this->teacherModel->create([
          'name' => $name,
          'email' => $email,
          'password' => $password
        ]);

        setFlash('success', 'Teacher created');
        header("Location: /teachers");
        exit;
      }
    }

    $view = BASE_PATH . "/views/teachers/create.php";
    require BASE_PATH . "/views/layouts/main.php";
  }

  public function view($id)
  {
    $teacher = $this->teacherModel->find($id);

    if (!$teacher) {
      die("Teacher not found");
    }

    require BASE_PATH . "/views/teachers/view.php";
  }

  public function edit($id)
  {
    if (!hasPermission('edit_teacher')) {
      die("Access denied");
    }

    $teacher = $this->teacherModel->find($id);
    if (!$teacher) die("Teacher not found");

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
        $errors['email'] = 'Invalid email formate';
      elseif ($email !== $teacher['email'] && $this->teacherModel->emailExists($email)) {
        $errors['email'] = 'Email already in use';
      }

      if ($password !== '' && strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
      }

      if (empty($errors)) {
        $this->teacherModel->update($id, [
          'name' => $name,
          'email' => $email,
          'password' => $password
        ]);

        setFlash('success', 'Teacher updated');
        header("Location: /teachers/view/$id");
        exit;
      }
    }

    $view = BASE_PATH . "/views/teachers/edit.php";
    require BASE_PATH . "/views/layouts/main.php";
  }

  public function delete($id)
  {
    if (!hasPermission('delete_teacher')) {
      die("Access denied");
    }

    $this->teacherModel->softDelete($id);

    setFlash('success', 'Teacher deleted');
    header("Location: /teachers");
    exit;
  }
}
