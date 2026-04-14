<h2>Edit Permission</h2>

<?php if (!empty($errors)): ?>
  <div style="color:red;">
    <ul>
      <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST" action="/permissions/edit/<?= $permission['id'] ?>">
  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

  <label>Permission Name:</label>
  <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? $permission['name']) ?>" required>
  <br><br>

  <button type="submit">Update</button>
</form>

<br>
<a href="/permissions">&larr; Back to Permissions</a>