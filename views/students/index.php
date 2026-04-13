<h3>Students</h3>

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
      <td><?= $s['name'] ?></td>
      <td><?= $s['email'] ?></td>
      <td>
        <?php if (hasPermission('edit_student')): ?>
          <a href="/students/edit/<?= $s['id'] ?>">Edit</a>
        <?php endif; ?>

        <?php if (hasPermission('delete_student')): ?>
          <a href="/students/delete/<?= $s['id'] ?>"
            onclick="return confirm('Delete student?')">Delete</a>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>

</table>