<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (currentUser()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$values = [
    'first_name' => '', 'last_name' => '', 'username' => '', 'email' => '',
    'birthdate' => '', 'gender' => '', 'city' => '', 'role' => User::ROLE_PARTICIPANT,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $key => $default) {
        $values[$key] = trim($_POST[$key] ?? $default);
    }
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // --- Validation ---
    if ($values['first_name'] === '' || $values['last_name'] === '') {
        $errors[] = 'First name and last name are required.';
    }
    if ($values['username'] === '') {
        $errors[] = 'Please choose a username.';
    } elseif (!preg_match('/^[A-Za-z0-9_.]{3,50}$/', $values['username'])) {
        $errors[] = 'Usernames can only contain letters, numbers, dots, and underscores (3-50 characters).';
    } elseif (User::findByUsername($values['username'])) {
        $errors[] = 'That username is already taken.';
    }
    if ($values['email'] === '' || !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (User::findByEmail($values['email'])) {
        $errors[] = 'An account with that email already exists.';
    }
    if ($values['birthdate'] === '') {
        $errors[] = 'Please enter your birthdate.';
    } else {
        $bd = DateTime::createFromFormat('Y-m-d', $values['birthdate']);
        $today = new DateTime('today');
        if (!$bd || $bd > $today) {
            $errors[] = 'Please enter a valid birthdate.';
        }
    }
    if ($values['gender'] === '' || !in_array($values['gender'], GENDER_OPTIONS, true)) {
        $errors[] = 'Please select a gender.';
    }
    if ($values['city'] === '') {
        $errors[] = 'Please enter your city.';
    }
    if (!in_array($values['role'], [User::ROLE_ADMIN, User::ROLE_PARTICIPANT], true)) {
        $errors[] = 'Please select a valid role.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Password and re-entered password do not match.';
    }

    if (!$errors) {
        $newUser = new User();
        $newUser->firstName = $values['first_name'];
        $newUser->lastName = $values['last_name'];
        $newUser->username = $values['username'];
        $newUser->email = $values['email'];
        $newUser->passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $newUser->birthdate = $values['birthdate'];
        $newUser->gender = $values['gender'];
        $newUser->city = $values['city'];
        $newUser->role = $values['role'];
        $newUserId = $newUser->save();

        $_SESSION['user_id'] = $newUserId;
        $_SESSION['flash'] = 'Welcome to FolksKitchen, ' . $newUser->firstName . '! Your account has been created.';
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create an account — FolksKitchen</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500;1,9..144,600&family=Karla:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div id="login-screen">
  <div class="login-card" style="max-width:600px;">
    <div class="login-mark">🍲</div>
    <h1>Join the family</h1>
    <p class="login-sub">Create an account to start archiving and browsing FolksKitchen recipes.</p>

    <?php if ($errors): ?>
      <div class="login-error"><?= implode('<br>', array_map('e', $errors)) ?></div>
    <?php endif; ?>

    <form class="login-form" method="post" action="register.php">
      <div class="form-grid2">
        <div class="form-row">
          <label>First name</label>
          <input type="text" name="first_name" required value="<?= e($values['first_name']) ?>">
        </div>
        <div class="form-row">
          <label>Last name</label>
          <input type="text" name="last_name" required value="<?= e($values['last_name']) ?>">
        </div>
      </div>

      <div class="form-grid2">
        <div class="form-row">
          <label>Username</label>
          <input type="text" name="username" required value="<?= e($values['username']) ?>" placeholder="letters, numbers, _ and .">
        </div>
        <div class="form-row">
          <label>Email</label>
          <input type="email" name="email" required value="<?= e($values['email']) ?>">
        </div>
      </div>

      <div class="form-grid2">
        <div class="form-row">
          <label>Password</label>
          <input type="password" name="password" required minlength="6">
        </div>
        <div class="form-row">
          <label>Re-enter password</label>
          <input type="password" name="confirm_password" required minlength="6">
        </div>
      </div>

      <div class="form-grid3">
        <div class="form-row">
          <label>Birthdate</label>
          <input type="date" name="birthdate" required value="<?= e($values['birthdate']) ?>">
        </div>
        <div class="form-row">
          <label>Gender</label>
          <select name="gender" required>
            <option value="">Select…</option>
            <?php foreach (GENDER_OPTIONS as $g): ?>
              <option value="<?= e($g) ?>" <?= $values['gender'] === $g ? 'selected' : '' ?>><?= e($g) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row">
          <label>City</label>
          <input type="text" name="city" required value="<?= e($values['city']) ?>">
        </div>
      </div>

      <div class="form-row">
        <label>Account role</label>
        <select name="role">
          <option value="<?= User::ROLE_PARTICIPANT ?>" <?= $values['role'] === User::ROLE_PARTICIPANT ? 'selected' : '' ?>>Participant — browse, search, and bookmark recipes</option>
          <option value="<?= User::ROLE_ADMIN ?>" <?= $values['role'] === User::ROLE_ADMIN ? 'selected' : '' ?>>Admin — full access, can add/edit/delete recipes and manage members</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:6px;">Create account</button>
    </form>

    <p style="margin-top:16px; font-size:13.5px; color:var(--ink-soft);">
      Already have an account? <a href="login.php" style="color:var(--terracotta); font-weight:700; text-decoration:none;">Log in →</a>
    </p>
  </div>
</div>
</body>
</html>
