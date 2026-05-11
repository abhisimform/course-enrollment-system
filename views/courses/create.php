<h2>Create Course</h2>

<?php if (!empty($errors)): ?>
  <div class="error-summary">
    <ul>
      <?php foreach ($errors as $error): ?>
        <li><?= e($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="form-container">
  <form method="POST" action="/courses/create" id="courseCreateForm">
    <?= csrfInput(); ?>

    <div class="form-group">
      <label for="course_name">Course Name:</label>
      <input
        type="text"
        name="course_name"
        id="course_name"
        placeholder="Course Name"
        value="<?= e($_POST['course_name'] ?? '') ?>"
        required>
      <span class="error-message"></span>
    </div>

    <div class="form-group">
      <label for="instructor_id">Instructor:</label>
      <select name="instructor_id" id="instructor_id" required>
        <option value="">Select Instructor</option>
        <?php foreach ($instructors as $instructor): ?>
          <option value="<?= $instructor['id'] ?>" <?= isset($_POST['instructor_id']) && $_POST['instructor_id'] == $instructor['id'] ? 'selected' : '' ?>>
            <?= e($instructor['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <span class="error-message"></span>
    </div>

    <div class="form-group">
      <label for="duration_weeks">Duration (Weeks):</label>
      <input
        type="number"
        name="duration_weeks"
        id="duration_weeks"
        placeholder="Duration in Weeks"
        value="<?= e($_POST['duration_weeks'] ?? '') ?>"
        required>
      <span class="error-message"></span>
    </div>

    <div class="form-group">
      <label for="max_seats">Max Seats:</label>
      <input
        type="number"
        name="max_seats"
        id="max_seats"
        placeholder="Max Seats"
        value="<?= e($_POST['max_seats'] ?? '') ?>"
        required>
      <span class="error-message"></span>
    </div>

    <div class="form-group">
      <label for="status">Status:</label>
      <select name="status" id="status" required>
        <option value="1" <?= isset($_POST['status']) && $_POST['status'] == '1' ? 'selected' : '' ?>>Active</option>
        <option value="0" <?= isset($_POST['status']) && $_POST['status'] == '0' ? 'selected' : '' ?>>Inactive</option>
      </select>
      <span class="error-message"></span>
    </div>

    <div class="form-actions">
      <a href="/courses" class="btn btn-secondary">Back</a>
      <button type="submit" class="btn btn-primary">Create Course</button>
    </div>
  </form>
</div>

<script>
$(document).ready(function() {
  $('#courseCreateForm').validate({
    errorElement: 'span',
    errorClass: 'error-message',
    errorPlacement: function(error, element) {
      error.appendTo(element.parent()); // display error under each input/select
    },
    rules: {
      course_name: {
        required: true,
        minlength: 3
      },
      instructor_id: {
        required: true
      },
      duration_weeks: {
        required: true,
        number: true,
        min: 1
      },
      max_seats: {
        required: true,
        number: true,
        min: 1
      },
      status: {
        required: true
      }
    },
    messages: {
      course_name: {
        required: 'Please enter a course name',
        minlength: 'Course name must be at least 3 characters'
      },
      instructor_id: {
        required: 'Please select an instructor'
      },
      duration_weeks: {
        required: 'Please enter duration in weeks',
        number: 'Duration must be a number',
        min: 'Duration must be at least 1 week'
      },
      max_seats: {
        required: 'Please enter maximum seats',
        number: 'Max seats must be a number',
        min: 'Max seats must be at least 1'
      },
      status: {
        required: 'Please select status'
      }
    },
    highlight: function(element) {
      $(element).addClass('is-invalid').removeClass('is-valid');
    },
    unhighlight: function(element) {
      $(element).removeClass('is-invalid').addClass('is-valid');
    },
    submitHandler: function(form) {
      form.submit();
    }
  });
});
</script>