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

    <?php
    $groupedPermissions = [];
    foreach ($allPermissions as $perm) {
      $parts = explode('.', $perm['name']);
      $group = $parts[0];
      $groupedPermissions[$group][] = $perm;
    }
    ?>

    <div style="max-height:500px; overflow-y:auto; border:1px solid #ccc; border-radius:8px; padding:15px;">

      <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap:15px;">

        <?php foreach ($groupedPermissions as $group => $permissions): ?>

          <details open style="border:1px solid #ddd; border-radius:8px; padding:10px; background:#fafafa;">
            
            <summary style="cursor:pointer; font-weight:bold; font-size:14px;">
              <?= ucfirst($group) ?>
            </summary>

            <div style="margin-top:10px; display:grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap:6px;">

              <?php foreach ($permissions as $perm): ?>

                <?php
                  $parts = explode('.', $perm['name']);
                  $action = ucfirst($parts[1] ?? $perm['name']);
                ?>

                <label style="border:1px solid #eee; padding:5px; border-radius:5px; font-size:12px; background:white;">
                  
                  <input type="checkbox"
                    name="permissions[]"
                    value="<?= $perm['id'] ?>"
                    <?= in_array($perm['id'], $currentPermissions) ? 'checked' : '' ?>>

                  <?= htmlspecialchars($action) ?>

                </label>

              <?php endforeach; ?>

            </div>

          </details>

        <?php endforeach; ?>

      </div>

    </div>

    <br>
    <button type="submit" style="padding:8px 16px; cursor:pointer;">
      💾 Save Role Permissions
    </button>

  </form>

<?php endif; ?>