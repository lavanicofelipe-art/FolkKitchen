<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = requireLogin();

$errors = [];
$saved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['first_name'] ?? '');
    $last = trim($_POST['last_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $birthdate = trim($_POST['birthdate'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $city = trim($_POST['city'] ?? '');

    if ($first === '' || $last === '' || $email === '' || $username === '') {
        $errors[] = 'First name, last name, username, and email are all required.';
    }
    if (!preg_match('/^[A-Za-z0-9_.]{3,50}$/', $username)) {
        $errors[] = 'Usernames can only contain letters, numbers, dots, and underscores (3-50 characters).';
    } else {
        $existingUsername = User::findByUsername($username);
        if ($existingUsername && $existingUsername->id !== $user->id) {
            $errors[] = 'Another family member already has that username.';
        }
    }
    $existingEmail = User::findByEmail($email);
    if ($existingEmail && $existingEmail->id !== $user->id) {
        $errors[] = 'Another family member is already using that email.';
    }
    if ($birthdate === '' || !DateTime::createFromFormat('Y-m-d', $birthdate)) {
        $errors[] = 'Please enter a valid birthdate.';
    }
    if (!in_array($gender, GENDER_OPTIONS, true)) {
        $errors[] = 'Please select a gender.';
    }
    if ($city === '') {
        $errors[] = 'Please enter a city.';
    }

    if (!$errors) {
        $user->firstName = $first;
        $user->lastName = $last;
        $user->username = $username;
        $user->email = $email;
        $user->birthdate = $birthdate;
        $user->gender = $gender;
        $user->city = $city;
        $user->save();
        $saved = true;
    }
}

$bookmarks = Bookmark::recipesForUser($user->id);

$activeNav = 'profile';
require __DIR__ . '/includes/header.php';
?>
<div class="topbar"><h2 class="page-title">My Profile</h2></div>

<?php if ($saved): ?><div class="flash">Profile updated.</div><?php endif; ?>
<?php if ($errors): ?><div class="login-error"><?= implode('<br>', array_map('e', $errors)) ?></div><?php endif; ?>

<div class="two-col">
  <div class="card">
    <h3 style="margin-bottom:14px;">Account details</h3>
    <form method="post" action="profile.php">
      <div class="form-grid2">
        <div class="form-row"><label>First name</label><input type="text" name="first_name" value="<?= e($user->firstName) ?>" required></div>
        <div class="form-row"><label>Last name</label><input type="text" name="last_name" value="<?= e($user->lastName) ?>" required></div>
      </div>
      <div class="form-grid2">
        <div class="form-row"><label>Username</label><input type="text" name="username" value="<?= e($user->username) ?>" required></div>
        <div class="form-row"><label>Email</label><input type="email" name="email" value="<?= e($user->email) ?>" required></div>
      </div>
      <div class="form-grid3">
        <div class="form-row"><label>Birthdate</label><input type="date" name="birthdate" value="<?= e($user->birthdate ?? '') ?>" required></div>
        <div class="form-row">
          <label>Gender</label>
          <select name="gender" required>
            <?php foreach (GENDER_OPTIONS as $g): ?>
              <option value="<?= e($g) ?>" <?= $user->gender === $g ? 'selected' : '' ?>><?= e($g) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row"><label>City</label><input type="text" name="city" value="<?= e($user->city) ?>" required></div>
      </div>
      <div class="form-row"><label>Role</label><input type="text" value="<?= e($user->role) ?>" disabled style="opacity:.65;"></div>
      <button type="submit" class="btn btn-primary">Save profile</button>
    </form>
  </div>
  <div class="card">
    <h3 style="margin-bottom:14px;">Favorite recipe shortcuts</h3>
    <?php if ($bookmarks): ?>
      <ul class="ingredient-list">
        <?php foreach ($bookmarks as $r): ?>
          <li><span><?= e($r->title) ?></span><a class="link-btn" href="recipe_detail.php?id=<?= $r->id ?>">Open →</a></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <div class="empty-state">No favorites bookmarked yet.</div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
