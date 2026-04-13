<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>

<h2>Login</h2>

<?php if ($msg = getFlash('error')): ?>
  <p style="color:red;"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>

<?php if ($msg = getFlash('success')): ?>
  <p style="color:green;"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>

<form method="POST" action="/auth/login">

  <input 
    type="hidden" 
    name="csrf_token" 
    value="<?= $_SESSION['csrf_token'] ?>"
  >

  <input 
    type="email" 
    name="email" 
    placeholder="Email"
    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
    required
  >
  <br><br>

  <input 
    type="password" 
    name="password" 
    placeholder="Password" 
    required
  >
  <br><br>

  <button type="submit">Login</button>

</form>