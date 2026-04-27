<h2>👤 Manage User Permissions</h2>

<form method="GET" action="/permissions/users">
  <label>Select User:</label>
  <select name="user_id" onchange="this.form.submit()">
    <option value="">-- Select User --</option>
    <?php foreach ($users as $u): ?>
      <option value="<?= $u['id'] ?>" <?= ($selectedUser['id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($u['name']) ?>
      </option>
    <?php endforeach; ?>
  </select>
</form>

<hr>

<?php if (!empty($selectedUser)): ?>

  <h3>🧑 Selected User: <?= htmlspecialchars($selectedUser['name']) ?></h3>

  <form method="POST" action="/permissions/users">

    <input type="hidden" name="user_id" value="<?= $selectedUser['id'] ?>">

    <label>Assign Role (optional preset):</label>
    <select name="role" id="roleSelect">
      <option value="">-- Custom Permissions Only --</option>
      <?php

      foreach ($roles as $role): ?>
        <option value="<?= $role ?>"
          <?= ($selectedUser['role'] ?? '') == $role ? 'selected' : '' ?>>
          <?= ucfirst($role) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <br><br>

    <h3>🔐 Permissions</h3>

    <input type="text"
      id="permissionSearch"
      placeholder="Search permission..."
      style="width:100%; padding:8px; margin-bottom:10px;">

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

            <summary style="font-weight:bold; font-size:14px;">
              <?= ucfirst($group) ?>
            </summary>

            <div style="margin-top:10px; display:grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap:6px;">

              <?php foreach ($permissions as $perm): ?>

                <?php
                $parts = explode('.', $perm['name']);
                $action = ucfirst($parts[1] ?? $perm['name']);
                $action = $perm['name'];
                ?>

                <div class="perm-item">
                  <label style="border:1px solid #eee; padding:5px; border-radius:5px; font-size:12px; background:white; display:block;">

                    <input type="checkbox"
                      name="permissions[]"
                      value="<?= $perm['id'] ?>"
                      <?= in_array($perm['id'], $userPermissions ?? []) ? 'checked' : '' ?>>

                    <?= htmlspecialchars($action) ?>

                  </label>
                </div>

              <?php endforeach; ?>

            </div>

          </details>

        <?php endforeach; ?>

      </div>

    </div>

    <br><br>
    <button type="submit">💾 Save Permissions</button>

  </form>

<?php endif; ?>

<script>
  let roleBaseline = new Set();
  let isProgrammaticChange = false;

  document.getElementById('roleSelect')?.addEventListener('change', function() {
    const role = this.value;

    if (!role) return;

    fetch('/permissions/getRolePermissionsJson?role=' + role)
      .then(res => res.json())
      .then(data => {

        isProgrammaticChange = true;

        roleBaseline = new Set(data);

        document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
          const val = parseInt(cb.value);
          cb.checked = roleBaseline.has(val);
        });

        setTimeout(() => {
          isProgrammaticChange = false;
        }, 50);
      });
  });

  document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
    cb.addEventListener('change', function() {

      if (isProgrammaticChange) return;

      const currentRole = document.getElementById('roleSelect');

      if (!currentRole.value) return;

      currentRole.value = "";

    });
  });
</script>

<script>
  function debounce(fn, delay) {
    let timer;
    return function(...args) {
      clearTimeout(timer);
      timer = setTimeout(() => fn.apply(this, args), delay);
    };
  }

  const searchInput = document.getElementById('permissionSearch');

  searchInput?.addEventListener('input', debounce(function() {
    const query = this.value.toLowerCase();

    document.querySelectorAll('.perm-item').forEach(item => {
      const text = item.innerText.toLowerCase();

      if (text.includes(query)) {
        item.style.display = 'block';
      } else {
        item.style.display = 'none';
      }
    });

  }, 200));
</script>