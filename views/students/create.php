<h3>Add Student</h3>

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
  <?= csrfInput() ?>
  <input name="name" placeholder="Name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required><br><br>

  <input name="email" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required><br><br>

  <input type="password" name="password" placeholder="Password" required><br><br>

  <button type="submit">Save</button>
</form>
