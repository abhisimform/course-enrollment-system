<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>View Student</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 40px;
      background: #f5f5f5;
    }

    .card {
      background: #fff;
      padding: 20px;
      max-width: 500px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    h2 {
      margin-top: 0;
    }

    .row {
      margin-bottom: 10px;
    }

    .label {
      font-weight: bold;
    }

    .btn-group {
      margin-top: 20px;
    }

    .btn {
      display: inline-block;
      padding: 8px 12px;
      text-decoration: none;
      border-radius: 5px;
      margin-right: 10px;
      color: #fff;
      font-size: 14px;
    }

    .btn-back {
      background: #6c757d;
    }

    .btn-edit {
      background: #007bff;
    }

    .btn:hover {
      opacity: 0.9;
    }
  </style>
</head>

<body>

  <div class="card">
    <h2>Student Details</h2>

    <div class="row">
      <span class="label">ID:</span>
      <span><?= htmlspecialchars($student['id'] ?? '') ?></span>
    </div>

    <div class="row">
      <span class="label">Name:</span>
      <span><?= htmlspecialchars($student['name'] ?? '') ?></span>
    </div>

    <div class="row">
      <span class="label">Email:</span>
      <span><?= htmlspecialchars($student['email'] ?? '') ?></span>
    </div>

    <div class="row">
      <span class="label">Phone:</span>
      <span><?= htmlspecialchars($student['phone'] ?? '') ?></span>
    </div>

    <div class="row">
      <span class="label">Created At:</span>
      <span><?= htmlspecialchars($student['created_at'] ?? '') ?></span>
    </div>

    <div class="btn-group">
      <a href="/students" class="btn btn-back">← Back</a>
      <a href="/students/edit/<?= $student['id'] ?>" class="btn btn-edit">Edit</a>
    </div>

  </div>

</body>

</html>