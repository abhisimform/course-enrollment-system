<?php

require_once BASE_PATH . "/app/models/Teacher.php";

class Teachers extends BaseController
{
  private $teacherModel;

  public function __construct()
  {
    requireLogin();
    $this->teacherModel = new TeacherModel();
  }

  public function index()
  {
    $currentPage = (int)($_GET['page'] ?? 1);
    $perPage = 10;

    $search = $_GET['search'] ?? '';
    $showDeleted = isset($_GET['deleted']);

    $teachers = $this->teacherModel->getTeachers($search, $currentPage, $perPage, $showDeleted);
    $totalTeachers = $this->teacherModel->countTeachers($search, $showDeleted);
    $totalPages = ceil($totalTeachers / $perPage);

    return $this->render('teachers/index', compact(
      'teachers',
      'currentPage',
      'perPage',
      'search',
      'showDeleted',
      'totalTeachers',
      'totalPages'
    ));
  }

  public function restore($id)
  {
    if (!Rbac::has('restore_teacher')) {
      die("Access denied");
    }

    $this->teacherModel->restore($id);
    setFlash('success', 'Teacher restored');

    return $this->redirect("/teachers?deleted=1");
  }

  public function create()
  {
    if (!Rbac::has('create_teacher')) {
      die("Access denied");
    }

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $name = trim($_POST['name'] ?? '');
      $email = trim($_POST['email'] ?? '');
      $password = $_POST['password'] ?? '';

      if ($name === '') {
        $errors['name'] = 'Name is required';
      }

      if ($email === '') {
        $errors['email'] = 'Email is required';
      } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
      } elseif ($this->teacherModel->emailExists($email)) {
        $errors['email'] = 'Email already in use';
      }

      if ($password === '') {
        $errors['password'] = 'Password is required';
      } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
      }

      if (empty($errors)) {

        $this->teacherModel->create([
          'name' => $name,
          'email' => $email,
          'password' => $password
        ]);

        setFlash('success', 'Teacher created');

        return $this->redirect("/teachers");
      }
    }

    return $this->render('teachers/create', compact('errors'));
  }

  public function view($id)
  {
    $teacher = $this->teacherModel->find($id);

    if (!$teacher) {
      die("Teacher not found");
    }

    return $this->render('teachers/view', compact('teacher'));
  }

  public function edit($id)
  {
    if (!Rbac::has('edit_teacher')) {
      die("Access denied");
    }

    $teacher = $this->teacherModel->find($id);

    if (!$teacher) {
      die("Teacher not found");
    }

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $name = trim($_POST['name'] ?? '');
      $email = trim($_POST['email'] ?? '');
      $password = $_POST['password'] ?? '';

      if ($name === '') {
        $errors['name'] = 'Name is required';
      }

      if ($email === '') {
        $errors['email'] = 'Email is required';
      } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
      } elseif (
        $email !== $teacher['email'] &&
        $this->teacherModel->emailExists($email)
      ) {
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

        return $this->redirect("/teachers/view/$id");
      }
    }

    return $this->render('teachers/edit', compact('teacher', 'errors'));
  }

  public function delete($id)
  {
    if (!Rbac::has('delete_teacher')) {
      die("Access denied");
    }

    $this->teacherModel->softDelete($id);

    setFlash('success', 'Teacher deleted');

    return $this->redirect("/teachers");
  }
}
