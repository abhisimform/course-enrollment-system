<h2>Create Course</h2>

<?php if (!empty($errors)): ?>
  <div style="color: red;">
    <ul>
      <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="POST" action="/courses/create">
  <?= csrfInput(); ?>

  <label for="course_name">Course Name:</label>
  <input
    type="text"
    name="course_name"
    id="course_name"
    placeholder="Course Name"
    value="<?= htmlspecialchars($_POST['course_name'] ?? '') ?>"
    required>
  <br><br>

  <label for="instructor_id">Instructor:</label>
  <select name="instructor_id" id="instructor_id" required>
    <option value="">Select Instructor</option>
    <?php foreach ($instructors as $instructor): ?>
      <option value="<?= $instructor['id'] ?>" <?= isset($_POST['instructor_id']) && $_POST['instructor_id'] == $instructor['id'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($instructor['name']) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <br><br>

  <label for="duration_weeks">Duration (Weeks):</label>
  <input
    type="number"
    name="duration_weeks"
    id="duration_weeks"
    placeholder="Duration in Weeks"
    value="<?= htmlspecialchars($_POST['duration_weeks'] ?? '') ?>"
    required>
  <br><br>

  <label for="max_seats">Max Seats:</label>
  <input
    type="number"
    name="max_seats"
    id="max_seats"
    placeholder="Max Seats"
    value="<?= htmlspecialchars($_POST['max_seats'] ?? '') ?>"
    required>
  <br><br>

  <label for="status">Status:</label>
  <select name="status" id="status">
    <option value="1" <?= isset($_POST['status']) && $_POST['status'] == '1' ? 'selected' : '' ?>>Active</option>
    <option value="0" <?= isset($_POST['status']) && $_POST['status'] == '0' ? 'selected' : '' ?>>Inactive</option>
  </select>
  <br><br>

  <a href="/courses">Back</a>
  <button type="submit">Create Course</button>

</form>
