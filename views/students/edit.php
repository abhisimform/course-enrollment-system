<h3>Edit Student</h3>

<a href="/students" class="btn btn-secondary" style="margin-bottom: 15px; display: inline-block;">&larr; Back to Students</a>

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
  <form method="POST" id="editStudentForm">
    <?= csrfInput() ?>

    <div class="form-group">
      <label for="name">Name:</label>
      <input 
        type="text" 
        name="name" 
        id="name" 
        placeholder="Name" 
        value="<?= e($_POST['name'] ?? $student['name']) ?>" 
        required>
      <span class="error-message"></span>
    </div>

    <div class="form-group">
      <label for="email">Email:</label>
      <input 
        type="email" 
        name="email" 
        id="email" 
        placeholder="Email" 
        value="<?= e($_POST['email'] ?? $student['email']) ?>" 
        required>
      <span class="error-message"></span>
    </div>

    <div class="form-group">
      <label for="password">New Password (leave blank to keep current):</label>
      <input 
        type="password" 
        name="password" 
        id="password" 
        placeholder="New Password (leave blank to keep current)">
      <span class="error-message"></span>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Update</button>
    </div>
  </form>
</div>

<script>
$(document).ready(function() {
  $('#editStudentForm').validate({
    errorElement: 'span',
    errorClass: 'error-message',
    errorPlacement: function(error, element) {
      error.appendTo(element.parent());
    },
    rules: {
      name: {
        required: true,
        minlength: 3
      },
      email: {
        required: true,
        email: true
      },
      password: {
        minlength: 6
      }
    },
    messages: {
      name: {
        required: 'Please enter the student name',
        minlength: 'Name must be at least 3 characters'
      },
      email: {
        required: 'Please enter the email',
        email: 'Please enter a valid email'
      },
      password: {
        minlength: 'Password must be at least 6 characters'
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