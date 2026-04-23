<?php require_once BASE_PATH . '/utils/helper.php'; ?>

<!DOCTYPE html>
<html>

<head>
  <title>Admin Panel</title>
  <style>
    body {
      font-family: Arial;
    }

    a {
      margin-right: 10px;
      text-decoration: none;
    }

    .nav {
      padding: 10px;
      background: #f4f4f4;
      margin-bottom: 10px;
    }

    .flash {
      padding: 10px;
      color: #fff;
      margin-bottom: 10px;
    }
  </style>
</head>

<body>

  <h2><?= ucfirst($_SESSION['user']['role']) ?> Panel</h2>

  <p>
    Welcome, <?= $_SESSION['user']['name'] ?> (<?= $_SESSION['user']['role'] ?>)
  </p>

  <hr>

  <div class="nav">

    <?php if (Rbac::has('dashboard.view')): ?>
      <a href="/dashboard">Dashboard</a>
    <?php endif; ?>

    <?php if (Rbac::has('student.view_all')): ?>
      <a href="/students">Students</a>
    <?php endif; ?>

    <?php if (Rbac::has('course.view')): ?>
      <a href="/courses">Courses</a>
    <?php endif; ?>

    <?php if (Rbac::has('enrollment.view')): ?>
      <a href="/enrollments">Enrollments</a>
    <?php endif; ?>

    <?php if (Rbac::has('teacher.view')): ?>
      <a href="/teachers">Teachers</a>
    <?php endif; ?>

    <?php if (Rbac::has('permission.assign')): ?>
      <a href="/permissions">Permissions</a>
      <a href="/permissions/roles">Role Permissions</a>
      <a href="/permissions/users">User Permissions</a>
    <?php endif; ?>

    <?php if (Rbac::has('audit.view')): ?>
      <a href="/audit">Audit Logs</a>
    <?php endif; ?>

    <a href="/auth/logout">Logout</a>

  </div>

  <hr>

  <?php if ($flash = getFlash()): ?>
    <div class="flash" style="background: <?= $flash['type'] == 'success' ? 'green' : 'red' ?>;">
      <?= $flash['message'] ?>
    </div>
  <?php endif; ?>

  <?php require $view; ?>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>
  <script src="../assets/js/app.js"></script>
</body>

</html>