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

  <h2>Admin Panel</h2>

  <p>
    Welcome, <?= $_SESSION['user']['name'] ?> (<?= $_SESSION['user']['role'] ?>)
  </p>

  <hr>

  <div class="nav">

    <?php if (hasPermission('dashboard.view')): ?>
      <a href="/dashboard">Dashboard</a>
    <?php endif; ?>

    <?php if (hasPermission('student.view')): ?>
      <a href="/students">Students</a>
    <?php endif; ?>

    <?php if (hasPermission('course.view')): ?>
      <a href="/courses">Courses</a>
    <?php endif; ?>

    <?php if (hasPermission('enrollment.view')): ?>
      <a href="/enrollments">Enrollments</a>
    <?php endif; ?>

    <?php if (hasPermission('teacher.view')): ?>
      <a href="/teachers">Teachers</a>
    <?php endif; ?>

    <?php if (hasPermission('permission.assign')): ?>
      <a href="/permissions">Permissions</a>
      <a href="/permissions/roles">Role Permissions</a>
      <a href="/permissions/users">User Permissions</a>
    <?php endif; ?>

    <?php if (hasPermission('audit.view')): ?>
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

</body>

</html>