<?php

class Students
{

  private $studentModel;

  public function __construct()
  {
    require_once BASE_PATH . "/app/models/Student.php";
    require_once BASE_PATH . "/utils/helper.php";

    // dd(1);
    requireLogin();

    $this->studentModel = new StudentModel();
  }

  public function index()
  {
    $students = $this->studentModel->getAll();
    // dd($_SESSION);
    $view = BASE_PATH . "/views/students/index.php";
    require BASE_PATH . "/views/layouts/main.php";
  }

  public function create()
  {
    if (!hasPermission('create_student')) {
      die("Access denied");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $this->studentModel->create($_POST);

      setFlash('success', 'Student created');
      header("Location: /students");
      exit;
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
    if (!hasPermission('edit_student')) {
      die("Access denied");
    }

    $student = $this->studentModel->find($id);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $this->studentModel->update($id, $_POST);

      setFlash('success', 'Student updated');
      header("Location: /students/view/$id");
      exit;
    }

    $view = BASE_PATH . "/views/students/edit.php";
    require BASE_PATH . "/views/layouts/main.php";
  }

  public function delete($id)
  {
    if (!hasPermission('delete_student')) {
      die("Access denied");
    }

    $this->studentModel->softDelete($id);

    setFlash('success', 'Student deleted');
    header("Location: /students");
    exit;
  }
}
