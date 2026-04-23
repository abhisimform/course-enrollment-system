<?php

require_once BASE_PATH . "/app/models/Student.php";

class Students extends BaseController
{
  private $studentModel;

  public function __construct()
  {
    requireLogin();
    $this->studentModel = new StudentModel();
  }

  public function index()
  {
    Rbac::require('student.view_all');
    
    $currentPage = (int)($_GET['page'] ?? 1);
    $perPage = 10;

    $search = $_GET['search'] ?? '';
    $showDeleted = isset($_GET['deleted']);

    $students = $this->studentModel->getStudents($search, $currentPage, $perPage, $showDeleted);
    $totalStudents = $this->studentModel->countStudents($search, $showDeleted);
    $totalPages = ceil($totalStudents / $perPage);

    return $this->render('students/index', compact(
      'students',
      'currentPage',
      'perPage',
      'search',
      'showDeleted',
      'totalPages'
    ));
  }

  public function restore($id)
  {
    if (!Rbac::has('restore_student')) {
      die("Access denied");
    }

    $this->studentModel->restore($id);
    setFlash('success', 'Student restored');

    return $this->redirect("/students?deleted=1");
  }

  public function create()
  {
    if (!Rbac::has('create_student')) {
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
        $errors['email'] = 'Invalid email';
      } elseif ($this->studentModel->emailExists($email)) {
        $errors['email'] = 'Email already in use';
      }

      if ($password === '') {
        $errors['password'] = 'Password is required';
      } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
      }

      if (empty($errors)) {

        $this->studentModel->create([
          'name' => $name,
          'email' => $email,
          'password' => $password
        ]);

        setFlash('success', 'Student created');

        return $this->redirect("/students");
      }
    }

    return $this->render('students/create', compact('errors'));
  }

  public function self()
  {
    $id = $_SESSION['user']['id'];
    $student = $this->studentModel->find($id);

    if (!$student) {
      die("Student not found");
    }

    return $this->render('students/self', compact('student'));
  }

  public function edit($id)
  {
    if (!Rbac::has('edit_student')) {
      die("Access denied");
    }

    $student = $this->studentModel->find($id);

    if (!$student) {
      die("Student not found");
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
        $errors['email'] = 'Invalid email';
      } elseif ($email !== $student['email'] && $this->studentModel->emailExists($email)) {
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

        return $this->redirect("/students/view/$id");
      }
    }

    return $this->render('students/edit', compact('student', 'errors'));
  }

  public function delete($id)
  {
    if (!Rbac::has('delete_student')) {
      die("Access denied");
    }

    $this->studentModel->softDelete($id);

    setFlash('success', 'Student deleted');

    return $this->redirect("/students");
  }
}
