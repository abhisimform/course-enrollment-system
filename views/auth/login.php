<div class="login-container">
  <h2>Login</h2>

  <?php if (!empty($errors ?? [])): ?>
    <div class="error-summary">
      <ul>
        <?php foreach ($errors as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="POST" action="/auth/login" id="loginForm">
    <?= csrfInput() ?>

    <div class="form-group">
      <label for="email">Email</label>
      <input type="text" name="email" id="email" placeholder="Email" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
      <span class="error-message"></span>
    </div>

    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" name="password" id="password" placeholder="Password">
      <span class="error-message"></span>
    </div>

    <div class="form-group">
      <label for="captcha">Captcha</label>
      <div class="captcha-wrapper">
        <img id="captcha-image" src="/auth/captcha" alt="captcha">
        <button type="button" class="refresh-btn" onclick="document.getElementById('captcha-image').src='/auth/captcha?t=' + Date.now();">⟲</button>
      </div>
      <input type="text" name="captcha" id="captcha" placeholder="Enter captcha" maxlength="6" autocomplete="off">
      <span class="error-message"></span>
    </div>

    <button type="submit" class="btn">Login</button>
  </form>
</div>

<script>
  $(document).ready(function() {

    $('#captcha').on('input', function() {
      let cursor = this.selectionStart;
      this.value = this.value.toUpperCase();
      this.setSelectionRange(cursor, cursor);
    });

    $('#loginForm').validate({
      errorElement: 'span',
      errorClass: 'error-message',
      errorPlacement: function(error, element) {
        error.appendTo(element.parent());
      },
      rules: {
        email: {
          required: true,
          email: true
        },
        password: {
          required: true
        },
        captcha: {
          required: true,
          minlength: 6,
          maxlength: 6
        }
      },
      messages: {
        email: {
          required: 'Please enter your email',
          email: 'Please enter a valid email'
        },
        password: {
          required: 'Please enter your password'
        },
        captcha: {
          required: 'Please enter the captcha',
          minlength: 'Captcha must be 6 characters',
          maxlength: 'Captcha must be 6 characters'
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