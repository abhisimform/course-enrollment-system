<?php require_once BASE_PATH . '/utils/helper.php'; ?>

<!DOCTYPE html>
<html>

<head>
  <title>Admin Panel</title>
  <style>
    :root {
      --page-bg: #f5f7fb;
      --surface: #ffffff;
      --surface-muted: #f8fafc;
      --border: #d9e2ec;
      --border-strong: #bcccdc;
      --text: #102a43;
      --text-muted: #52606d;
      --primary: #1d4ed8;
      --primary-soft: #e0ecff;
      --success: #137333;
      --danger: #c62828;
      --shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
      --radius: 16px;
    }

    body {
      margin: 0;
      padding: 24px;
      background: linear-gradient(180deg, #f8fbff 0%, #eef3f8 100%);
      color: var(--text);
      font-family: Arial, sans-serif;
      line-height: 1.5;
    }

    a {
      margin-right: 10px;
      text-decoration: none;
      color: var(--primary);
    }

    .nav {
      padding: 14px 16px;
      background: rgba(255, 255, 255, 0.9);
      border: 1px solid rgba(188, 204, 220, 0.8);
      border-radius: 14px;
      margin-bottom: 18px;
      box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
    }

    .flash {
      padding: 12px 14px;
      color: #fff;
      margin-bottom: 16px;
      border-radius: 12px;
      box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
    }
  </style>

  <!-- jQuery library -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <!-- jQuery Validation -->
  <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.22.0/dist/jquery.validate.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.22.0/dist/additional-methods.min.js"></script>

  <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.css" />

  <script src="https://cdn.datatables.net/2.3.8/js/dataTables.js"></script>
</head>

<body>

  <h2><?= ucfirst(e($_SESSION['user']['role'])) ?> Panel</h2>

  <p>
    Welcome, <?= e($_SESSION['user']['name']) ?> (<?= e($_SESSION['user']['role']) ?>)
  </p>

  <hr>

  <div class="nav">

    <?php if (Rbac::has('dashboard.view')): ?>
      <a href="/dashboard">Dashboard</a>
    <?php endif; ?>

    <?php if (Rbac::has('student.view_all')): ?>
      <a href="/students">Students</a>
    <?php endif; ?>

    <?php if (Rbac::has('course.view_all')): ?>
      <a href="/courses">Courses</a>
    <?php endif; ?>

    <?php if (Rbac::has('enrollment.view')): ?>
      <a href="/enrollments">Enrollments</a>
    <?php endif; ?>

    <?php if (Rbac::has('teacher.view_all')): ?>
      <a href="/teachers">Teachers</a>
    <?php endif; ?>

    <?php if (Rbac::has('permission.manage')): ?>
      <a href="/permissions">Permissions</a>
      <a href="/permissions/roles">Role Permissions</a>
      <a href="/permissions/users">User Permissions</a>
    <?php endif; ?>

    <?php if (Rbac::has('audit.view_all')): ?>
      <a href="/audit">Audit Logs</a>
    <?php endif; ?>

    <?php if (Rbac::isAdmin()): ?>
      <a href="/notifications">Email Queue</a>
    <?php endif; ?>

    <?php if (Rbac::has('audit.auth_log')): ?>
      <a href="/authlogs">Auth Logs</a>
    <?php endif; ?>

    <?php if (Rbac::has('profile.view')): ?>
      <a href="/auth/profile">My Profile</a>
    <?php endif; ?>

    <a href="/auth/logout">Logout</a>
  </div>

  <hr>

  <?php if ($flash = getFlash()): ?>
    <div class="flash" style="background: <?= $flash['type'] == 'success' ? 'green' : 'red' ?>;">
      <?= e($flash['message']) ?>
    </div>
  <?php endif; ?>

  <?php require $view; ?>

  <script>
    <?php readfile(BASE_PATH . '/public/assets/js/app.js'); ?>
  </script>
</body>

</html>
