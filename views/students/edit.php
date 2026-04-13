<h3>Edit Student</h3>

<a href="/students" style="margin-bottom: 15px; display: inline-block;">&larr; Back to Students</a>

<?php if (!empty($errors)): ?>
  <div style="color: red;">
    <ul>
      <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST">
  <input name="name" value="<?= htmlspecialchars($_POST['name'] ?? $student['name']) ?>" placeholder="Name" required><br><br>

  <input name="email" value="<?= htmlspecialchars($_POST['email'] ?? $student['email']) ?>" placeholder="Email" required><br><br>

  <input type="password" name="password" placeholder="New Password (leave blank to keep current)"><br><br>

  <button type="submit">Update</button>
</form>