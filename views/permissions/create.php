<h2>Create Permission</h2>

<?php if (!empty($errors)): ?>
  <div style="color:red;">
    <ul>
      <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST" action="/permissions/create">
  <?= csrfInput() ?>

  <label>Permission Name:</label>
  <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
  <br><br>

  <button type="submit">Create</button>
</form>

<br>
<a href="/permissions">&larr; Back to Permissions</a>
