<h3>Edit Student</h3>

<form method="POST">
  <input name="name" value="<?= $student['name'] ?>"><br><br>
  <input name="email" value="<?= $student['email'] ?>"><br><br>
  <input name="phone" value="<?= $student['phone'] ?>"><br><br>

  <button type="submit">Update</button>
</form>