<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>

<h2>Edit Course</h2>

<?php if ($msg = getFlash('error')): ?>
  <p style="color:red;"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>

<?php if ($msg = getFlash('success')): ?>
  <p style="color:green;"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>

<form method="POST" action="/courses/edit/<?= $course['id'] ?>">

  <input 
    type="hidden" 
    name="csrf_token" 
    value="<?= $_SESSION['csrf_token'] ?>"
  >

  <input type="hidden" name="id" value="<?= $course['id'] ?>">

  <label for="course_name">Course Name:</label>
  <input 
    type="text" 
    name="course_name" 
    id="course_name" 
    placeholder="Course Name" 
    value="<?= htmlspecialchars($course['course_name']) ?>"
    required
  >
  <br><br>

  <label for="instructor_id">Instructor:</label>
  <select name="instructor_id" id="instructor_id" required>
    <option value="">Select Instructor</option>
    <?php foreach ($instructors as $instructor): ?>
      <option value="<?= $instructor['id'] ?>" <?= $course['instructor_id'] == $instructor['id'] ? 'selected' : '' ?>>
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
    value="<?= htmlspecialchars($course['duration_weeks']) ?>"
    required
  >
  <br><br>

  <label for="max_seats">Max Seats:</label>
  <input 
    type="number" 
    name="max_seats" 
    id="max_seats" 
    placeholder="Max Seats" 
    value="<?= htmlspecialchars($course['max_seats']) ?>"
    required
  >
  <br><br>

  <label for="status">Status:</label>
  <select name="status" id="status">
    <option value="1" <?= $course['status'] == '1' ? 'selected' : '' ?>>Active</option>
    <option value="0" <?= $course['status'] == '0' ? 'selected' : '' ?>>Inactive</option>
  </select>
  <br><br>

  <a href="/courses">Back</a>
  <button type="submit">Update Course</button>

</form>