<?php
require_once __DIR__.'/../../lib/auth.php';
require_once __DIR__.'/../../lib/helpers.php';
$u = current_user();
if ($u) {
  header('Location: /wastech/public/index.php'); exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Wastech — Login</title>
  <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
  <div class="container">
    <h1>Wastech</h1>
    <p class="muted">E-waste management (no external APIs)</p>

    <div class="card two-col">
      <form method="post" action="../controllers/auth-login.php">
        <h2>Login</h2>
        <?php if (isset($_GET['err'])): ?>
          <div class="alert error"><?= h($_GET['err']) ?></div>
        <?php endif; ?>
        <label>Email</label>
        <input name="email" type="email" required>
        <label>Password</label>
        <input name="password" type="password" required>
        <?php csrf_input(); ?>
        <button type="submit">Sign In</button>
      </form>

      <form method="post" action="../controllers/auth-register.php">
        <h2>Register (Recycler)</h2>
        <label>Name</label>
        <input name="name" required>
        <label>Email</label>
        <input name="email" type="email" required>
        <label>Password</label>
        <input name="password" type="password" required>
        <?php csrf_input(); ?>
        <button type="submit">Create Account</button>
        <p class="muted small">Collector accounts are created by admin manually.</p>
      </form>
    </div>
  </div>
</body>
</html>
