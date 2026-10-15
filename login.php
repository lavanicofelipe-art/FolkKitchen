<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (currentUser()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = User::findByEmail($email);
    if ($user && $user->verifyPassword($password)) {
        $_SESSION['user_id'] = $user->id;
        header('Location: index.php');
        exit;
    }
    $error = 'That email and password don\'t match a family account.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Log in — FolksKitchen</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500;1,9..144,600&family=Karla:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div id="login-screen">
  <div class="login-card">
    <div class="login-mark">🍲</div>
    <h1>FolksKitchen</h1>
    <p class="login-sub">A shared kitchen table for the whole family. Log in to open the recipe box — Admins can add and edit dishes and manage members, Participants can browse, search, and bookmark family favorites.</p>

    <?php if ($error): ?><div class="login-error"><?= e($error) ?></div><?php endif; ?>

    <form class="login-form" method="post" action="login.php">
      <div class="form-row">
        <label>Email</label>
        <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-row">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Log in</button>
    </form>

    <p style="margin-top:16px; font-size:13.5px; color:var(--ink-soft);">
      New to the family? <a href="register.php" style="color:var(--terracotta); font-weight:700; text-decoration:none;">Create an account →</a>
    </p>

    <div class="demo-hint">
      <b>First time running this project?</b> Import <code>database/folkskitchen_schema.sql</code> and
      <code>database/seed.sql</code>, then log in as any seeded family member with password
      <b>family123</b> — for example <b>odysseus@folkskitchen.family</b> (Admin) or
      <b>elena@folkskitchen.family</b> (Participant). Or just register a brand new account below.
    </div>
  </div>
</div>
</body>
</html>
