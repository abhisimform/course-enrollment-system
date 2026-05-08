<style>
  .profile-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
  }

  .profile-hero-left {
    display: flex;
    align-items: center;
    gap: 18px;
  }

  .profile-avatar {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #dbeafe;
    color: #2563eb;
    font-size: 28px;
    font-weight: 700;
    flex-shrink: 0;
  }

  .profile-name {
    margin: 0;
    font-size: 30px;
    font-weight: 700;
    color: #111827;
  }

  .profile-meta {
    margin-top: 6px;
    color: #6b7280;
    font-size: 15px;
  }

  .status-pill {
    padding: 8px 14px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 600;
  }

  .status-pill.success {
    background: #dcfce7;
    color: #166534;
  }

  .status-pill.warning {
    background: #fef3c7;
    color: #92400e;
  }

  .profile-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 22px;
    align-items: start;
  }

  .profile-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 24px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
  }

  .profile-card h2 {
    margin: 0;
    font-size: 24px;
    color: #111827;
  }

  .profile-card .page-subtitle {
    margin-top: 8px;
    margin-bottom: 24px;
    color: #6b7280;
    line-height: 1.5;
  }

  .profile-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
  }

  .profile-form-grid .field-full {
    grid-column: 1 / -1;
  }

  .profile-card label {
    display: block;
    margin-bottom: 7px;
    font-weight: 600;
    color: #374151;
    font-size: 14px;
  }

  .profile-card input {
    width: 100%;
    height: 44px;
    padding: 0 14px;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    font-size: 15px;
    transition: all 0.2s ease;
    background: #fff;
  }

  .profile-card input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    outline: none;
  }

  .readonly-field {
    height: 44px;
    display: flex;
    align-items: center;
    padding: 0 14px;
    border-radius: 10px;
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    color: #6b7280;
    font-size: 15px;
  }

  .actions {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 24px;
  }

  .actions button,
  .actions .button {
    height: 42px;
    padding: 0 18px;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: 0.2s ease;
  }

  .actions button {
    background: #2563eb;
    color: #fff;
  }

  .actions button:hover {
    background: #1d4ed8;
  }

  .actions .button.secondary {
    background: #f3f4f6;
    color: #374151;
  }

  .actions .button.secondary:hover {
    background: #e5e7eb;
  }

  .simple-hint {
    margin-top: 14px;
    padding: 12px 14px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    color: #6b7280;
    font-size: 14px;
    line-height: 1.6;
  }

  .error-panel {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-left: 4px solid #dc2626;
    color: #b91c1c;
    padding: 16px;
    border-radius: 10px;
    margin-bottom: 20px;
  }

  .error-panel ul {
    margin-top: 10px;
    padding-left: 18px;
  }

  @media (max-width: 1024px) {
    .profile-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 768px) {
    .profile-hero {
      flex-direction: column;
      align-items: flex-start;
    }

    .profile-form-grid {
      grid-template-columns: 1fr;
    }

    .profile-name {
      font-size: 26px;
    }
  }
</style>

<?php
$initials = strtoupper(substr($profile['name'] ?? 'U', 0, 1));
$roleLabel = ucfirst($profile['role'] ?? 'user');
?>

<div class="page-header">
  <div>
    <h1 class="page-title">My Profile</h1>
    <p class="page-subtitle">Manage account details, contact information, and password.</p>
  </div>
</div>

<section class="profile-hero">
  <div class="profile-avatar"><?= e($initials) ?></div>
  <div>
    <h2 class="profile-name"><?= e($profile['name']) ?></h2>
    <p class="profile-meta"><?= e($profile['email']) ?> · <?= e($roleLabel) ?></p>
  </div>
  <span class="status-pill <?= (int)($profile['status'] ?? 1) === 1 ? 'success' : 'warning' ?>">
    <?= (int)($profile['status'] ?? 1) === 1 ? 'Active' : 'Inactive' ?>
  </span>
</section>

<?php if (!empty($errors)): ?>
  <div class="content-card error-panel">
    <strong>Please fix the following:</strong>
    <ul>
      <?php foreach ($errors as $error): ?>
        <li><?= e($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="profile-grid">
  <form class="content-card profile-card" method="POST" action="/auth/profile">
    <?= csrfInput() ?>
    <input type="hidden" name="form_type" value="profile">

    <h2 class="page-title" style="font-size:20px;">Account Details</h2>
    <p class="page-subtitle">These details appear across dashboard and module views.</p>

    <div class="profile-form-grid">
      <div>
        <label for="name">Name <span style="color:#b91c1c;">*</span></label>
        <input id="name" type="text" name="name" maxlength="100" value="<?= e($_POST['name'] ?? $profile['name']) ?>" required>
      </div>

      <div>
        <label for="email">Email <span style="color:#b91c1c;">*</span></label>
        <input id="email" type="email" name="email" maxlength="100" value="<?= e($_POST['email'] ?? $profile['email']) ?>" required>
      </div>

      <div>
        <label for="role">Role</label>
        <div class="readonly-field"><?= e($roleLabel) ?></div>
      </div>

      <div>
        <label>Status</label>
        <div class="readonly-field"><?= (int)($profile['status'] ?? 1) === 1 ? 'Active' : 'Inactive' ?></div>
      </div>

      <?php if ($profile['role'] === 'student'): ?>
        <div>
          <label for="phone">Phone</label>
          <input id="phone" type="text" name="phone" maxlength="20" value="<?= e($_POST['phone'] ?? ($profile['phone'] ?? '')) ?>">
        </div>

        <div>
          <label for="enrolled_on">Enrolled On</label>
          <input id="enrolled_on" type="date" name="enrolled_on" value="<?= e($_POST['enrolled_on'] ?? ($profile['enrolled_on'] ?? '')) ?>">
        </div>
      <?php endif; ?>
    </div>

    <div class="actions" style="margin-top:16px;">
      <button type="submit">Save Profile</button>
      <a class="button secondary" href="/dashboard">Cancel</a>
    </div>
  </form>

  <form class="content-card profile-card" method="POST" action="/auth/profile" data-password-validation>
    <?= csrfInput() ?>
    <input type="hidden" name="form_type" value="password">

    <h2 class="page-title" style="font-size:20px;">Change Password</h2>
    <p class="page-subtitle">Update your password securely for this account.</p>

    <label for="current_password">Current Password <span style="color:#b91c1c;">*</span></label>
    <input id="current_password" type="password" name="current_password" autocomplete="current-password">

    <label for="new_password">New Password <span style="color:#b91c1c;">*</span></label>
    <input id="new_password" type="password" name="new_password" autocomplete="new-password">

    <label for="confirm_password">Confirm Password <span style="color:#b91c1c;">*</span></label>
    <input id="confirm_password" type="password" name="confirm_password" autocomplete="new-password">

    <div class="simple-hint">
      Password should include uppercase, lowercase, number, and special character.
    </div>

    <div class="actions" style="margin-top:16px;">
      <button type="submit">Update Password</button>
    </div>
  </form>
</div>

<script>
  $(document).ready(function() {

    $('form[action="/auth/profile"]:not([data-password-validation])').validate({
      errorElement: 'span',
      errorClass: 'error-message',

      errorPlacement: function(error, element) {
        error.insertAfter(element);
      },

      rules: {
        name: {
          required: true,
          minlength: 3,
          maxlength: 100
        },

        email: {
          required: true,
          email: true,
          maxlength: 100
        },

        phone: {
          digits: true,
          minlength: 10,
          maxlength: 20
        },

        enrolled_on: {
          date: true
        }
      },

      messages: {
        name: {
          required: 'Please enter your name',
          minlength: 'Name must be at least 3 characters',
          maxlength: 'Name cannot exceed 100 characters'
        },

        email: {
          required: 'Please enter your email',
          email: 'Please enter a valid email address',
          maxlength: 'Email cannot exceed 100 characters'
        },

        phone: {
          digits: 'Phone number should contain digits only',
          minlength: 'Phone number must be at least 10 digits',
          maxlength: 'Phone number cannot exceed 20 digits'
        },

        enrolled_on: {
          date: 'Please enter a valid date'
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

    $('form[data-password-validation]').validate({
      errorElement: 'span',
      errorClass: 'error-message',

      errorPlacement: function(error, element) {
        error.insertAfter(element);
      },

      rules: {
        current_password: {
          required: true
        },

        new_password: {
          required: true,
          minlength: 8,
          pwcheck: true
        },

        confirm_password: {
          required: true,
          equalTo: '#new_password'
        }
      },

      messages: {
        current_password: {
          required: 'Please enter current password'
        },

        new_password: {
          required: 'Please enter new password',
          minlength: 'Password must be at least 8 characters',
          pwcheck: 'Password must contain uppercase, lowercase, number, and special character'
        },

        confirm_password: {
          required: 'Please confirm your password',
          equalTo: 'Passwords do not match'
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

    $.validator.addMethod(
      'pwcheck',
      function(value) {
        return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/.test(value);
      }
    );

  });
</script>