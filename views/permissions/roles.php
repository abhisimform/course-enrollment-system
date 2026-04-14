<h2>⚙️ Manage Role Permissions</h2>

<form method="GET">
  <label>Select Role:</label>
  <select name="role" onchange="this.form.submit()">
    <option value="">-- Select Role --</option>

    <?php foreach ($roles as $role): ?>
      <option value="<?= $role ?>" <?= ($selectedRole == $role) ? 'selected' : '' ?>>
        <?= ucfirst($role) ?>
      </option>
    <?php endforeach; ?>

  </select>
</form>

<hr>

<?php if (!empty($selectedRole)): ?>

  <?php $currentPermissions = $rolePermissions[$selectedRole] ?? []; ?>

  <form method="POST" action="/permissions/roles">

    <input type="hidden" name="role" value="<?= $selectedRole ?>">

    <h3>Permissions for <?= ucfirst($selectedRole) ?></h3>

    <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:10px;">

      <?php foreach ($allPermissions as $perm): ?>

        <label style="border:1px solid #ccc; padding:8px; border-radius:6px;">
          <input type="checkbox"
            name="permissions[]"
            value="<?= $perm['id'] ?>"
            <?= in_array($perm['id'], $currentPermissions) ? 'checked' : '' ?>>

          <?= htmlspecialchars($perm['name']) ?>
        </label>

      <?php endforeach; ?>

    </div>

    <br>
    <button type="submit">💾 Save Role Permissions</button>

  </form>

<?php endif; ?>