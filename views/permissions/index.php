<h2>Permissions</h2>

<div style="margin-bottom: 15px;">
  <a href="/permissions/create">➕ Add Permission</a> |

  <a href="/permissions/roles">⚙️ Manage Role Permissions</a> |
  <a href="/permissions/users">👤 Manage User Permissions</a>
</div>

<form method="GET" action="/permissions">
  <label>Search:</label>
  <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
  <button type="submit">Search</button>
</form>

<br>

<table border="1" cellpadding="10">
  <tr>
    <th>ID</th>
    <th>Name</th>
    <th>Status</th>
    <th>Actions</th>
  </tr>

  <?php foreach ($permissions as $perm): ?>
    <tr>
      <td><?= $perm['id'] ?></td>
      <td><?= htmlspecialchars($perm['name'] ?? '') ?></td>
      <td><?= $perm['deleted_at'] ? 'Deleted' : 'Active' ?></td>
      <td>
        <a href="/permissions/view/<?= $perm['id'] ?>">View</a>

        <?php if (!$perm['deleted_at']): ?>
          | <a href="/permissions/edit/<?= $perm['id'] ?>">Edit</a>
          | <a href="/permissions/delete/<?= $perm['id'] ?>"
            onclick="return confirm('Delete this permission?')">Delete</a>
        <?php else: ?>
          | <a href="/permissions/restore/<?= $perm['id'] ?>"
            onclick="return confirm('Restore this permission?')">Restore</a>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<?php if ($totalPages > 1): ?>
  <div>
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <?php if ($i == $page): ?>
        <strong><?= $i ?></strong>
      <?php else: ?>
        <a href="/permissions?page=<?= $i ?>&q=<?= urlencode($_GET['q'] ?? '') ?>">
          <?= $i ?>
        </a>
      <?php endif; ?>
    <?php endfor; ?>
  </div>
<?php endif; ?>