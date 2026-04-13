<h3>Teachers</h3>

<form method="GET" style="margin-bottom:10px;">
  <input type="text" name="search" placeholder="Search by name or email" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
  <label>
    <input type="checkbox" name="deleted" value="1" <?= isset($_GET['deleted']) ? 'checked' : '' ?>>
    Show Deleted
  </label>
  <button type="submit">Filter</button>
</form>

<?php if (hasPermission('create_teacher') && !isset($_GET['deleted'])): ?>
  <a href="/teachers/create">Add Teacher</a>
<?php endif; ?>

<table border="1" cellpadding="10">
  <tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Actions</th>
  </tr>

  <?php foreach ($teachers as $t): ?>
    <tr>
      <td><?= $t['id'] ?></td>
      <td><?= htmlspecialchars($t['name']) ?></td>
      <td><?= htmlspecialchars($t['email']) ?></td>
      <td>
        <?php if (!isset($_GET['deleted'])): ?>
          <?php if (hasPermission('edit_teacher')): ?>
            <a href="/teachers/edit/<?= $t['id'] ?>">Edit</a>
          <?php endif; ?>
          <?php if (hasPermission('delete_teacher')): ?>
            <a href="/teachers/delete/<?= $t['id'] ?>" onclick="return confirm('Delete teacher?')">Delete</a>
          <?php endif; ?>
        <?php else: ?>
          <?php if (hasPermission('restore_teacher')): ?>
            <a href="/teachers/restore/<?= $t['id'] ?>">Restore</a>
          <?php endif; ?>
        <?php endif; ?>
        <a href="/teachers/view/<?= $t['id'] ?>">View</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<?php if ($totalPages > 1): ?>
  <div style="margin-top:10px;">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
      <a href="?page=<?= $p ?><?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?><?= isset($_GET['deleted']) ? '&deleted=1' : '' ?>">
        <?= $p ?>
      </a>
    <?php endfor; ?>
  </div>
<?php endif; ?>