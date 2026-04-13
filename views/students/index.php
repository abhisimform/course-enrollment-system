<h3>Students</h3>

<form method="GET" style="margin-bottom:10px;">
  <input type="text" name="search" placeholder="Search by name or email" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
  <label>
    <input type="checkbox" name="deleted" value="1" <?= isset($_GET['deleted']) ? 'checked' : '' ?>>
    Show Deleted
  </label>
  <button type="submit">Filter</button>
</form>

<?php if (hasPermission('create_student')): ?>
  <a href="/students/create">Add Student</a>
<?php endif; ?>

<table border="1" cellpadding="10">
  <tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Actions</th>
  </tr>

  <?php foreach ($students as $s): ?>
    <tr>
      <td><?= $s['id'] ?></td>
      <td><?= htmlspecialchars($s['name']) ?></td>
      <td><?= htmlspecialchars($s['email']) ?></td>
      <td>
        <?php if (!isset($_GET['deleted'])): ?>
          <?php if (hasPermission('edit_student')): ?>
            <a href="/students/edit/<?= $s['id'] ?>">Edit</a>
          <?php endif; ?>
          <?php if (hasPermission('delete_student')): ?>
            <a href="/students/delete/<?= $s['id'] ?>"
              onclick="return confirm('Delete student?')">Delete</a>
          <?php endif; ?>
        <?php else: ?>
          <?php if (hasPermission('restore_student')): ?>
            <a href="/students/restore/<?= $s['id'] ?>">Restore</a>
          <?php endif; ?>
        <?php endif; ?>
        <a href="/students/view/<?= $s['id'] ?>">View</a>
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