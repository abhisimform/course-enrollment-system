<style>
  .profile-hero {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 16px;
    align-items: center;
    background: #fff;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 18px;
    box-shadow: var(--shadow);
    margin-bottom: 18px;
  }

  .profile-avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: #dbeafe;
    color: #1d4ed8;
    font-size: 24px;
    font-weight: 700;
  }

  .profile-name {
    margin: 0;
    font-size: 24px;
  }

  .profile-meta {
    margin: 6px 0 0;
    color: var(--text-muted);
  }

  .profile-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(320px, 0.65fr);
    gap: 16px;
    align-items: start;
  }

  .profile-card {
    padding: 18px;
  }

  .profile-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px 14px;
    margin-top: 14px;
  }

  .profile-form-grid .field-full {
    grid-column: 1 / -1;
  }

  .readonly-field {
    min-height: 38px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 9px 11px;
    background: var(--surface-muted);
    color: var(--text-muted);
  }

  .profile-hint {
    margin-top: 14px;
    padding: 12px;
    border-radius: var(--radius);
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 14px;
    line-height: 1.5;
  }

  .simple-hint {
    margin-top: 14px;
    color: var(--text-muted);
    font-size: 14px;
    line-height: 1.5;
  }

  .error-panel {
    padding: 14px;
    margin-bottom: 16px;
    border-left: 4px solid var(--danger);
    color: var(--danger);
  }

  @media (max-width: 980px) {

    .profile-grid,
    .profile-hero {
      grid-template-columns: 1fr;
    }

    .profile-form-grid {
      grid-template-columns: 1fr;
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