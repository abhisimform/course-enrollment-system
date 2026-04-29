<h2>Edit Teacher</h2>

<a href="/teachers">&larr; Back to All Teachers</a>
<br><br>

<?php if (!empty($errors)): ?>
  <div style="color:red;">
    <ul>
      <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST">
  <?= csrfInput() ?>
  <label>Name:</label>
  <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? $teacher['name']) ?>" required>
  <br><br>

  <label>Email:</label>
  <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? $teacher['email']) ?>" required>
  <br><br>

  <button type="submit">Update Teacher</button>
</form>
