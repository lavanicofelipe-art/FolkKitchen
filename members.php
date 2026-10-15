<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = requireAdmin();

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

    if ($values['first_name'] === '' || $values['last_name'] === '') $errors[] = 'First name and last name are required.';
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
        $errors[] = 'That email is already registered.';
    }
    if ($values['birthdate'] === '' || !DateTime::createFromFormat('Y-m-d', $values['birthdate'])) {
        $errors[] = 'Please enter a valid birthdate.';
    }
    if ($values['gender'] === '' || !in_array($values['gender'], GENDER_OPTIONS, true)) {
        $errors[] = 'Please select a gender.';
    }
    if ($values['city'] === '') $errors[] = 'Please enter a city.';
    if (!in_array($values['role'], [User::ROLE_ADMIN, User::ROLE_PARTICIPANT], true)) $errors[] = 'Please select a valid role.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

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
        $newUser->save();

        // Note: Activity_Logs.Recipe_ID is a required foreign key in the
        // schema, so member-management events (which have no recipe) can't
        // be written to that table — we just confirm it with a flash message.
        $_SESSION['flash'] = $newUser->fullName() . ' was added to FolksKitchen.';
        header('Location: members.php');
        exit;
    }
}

$members = User::all();

$activeNav = 'members';
require __DIR__ . '/includes/header.php';
?>
<div class="topbar">
  <h2 class="page-title">Family Members</h2>
  <div class="page-sub">Manage who can cook (Admin) and who can browse (Participant).</div>
</div>

<?php if ($errors): ?>
  <div class="login-error"><?= implode('<br>', array_map('e', $errors)) ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom:22px;">
  <h3 style="margin-bottom:14px;">Invite a family member</h3>
  <form method="post" action="members.php">
    <div class="form-grid2">
      <div class="form-row"><label>First name</label><input type="text" name="first_name" required value="<?= e($values['first_name']) ?>"></div>
      <div class="form-row"><label>Last name</label><input type="text" name="last_name" required value="<?= e($values['last_name']) ?>"></div>
    </div>
    <div class="form-grid2">
      <div class="form-row"><label>Username</label><input type="text" name="username" required value="<?= e($values['username']) ?>"></div>
      <div class="form-row"><label>Email</label><input type="email" name="email" required value="<?= e($values['email']) ?>"></div>
    </div>
    <div class="form-grid3">
      <div class="form-row"><label>Birthdate</label><input type="date" name="birthdate" required value="<?= e($values['birthdate']) ?>"></div>
      <div class="form-row">
        <label>Gender</label>
        <select name="gender" required>
          <option value="">Select…</option>
          <?php foreach (GENDER_OPTIONS as $g): ?>
            <option value="<?= e($g) ?>" <?= $values['gender'] === $g ? 'selected' : '' ?>><?= e($g) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-row"><label>City</label><input type="text" name="city" required value="<?= e($values['city']) ?>"></div>
    </div>
    <div class="form-grid2">
      <div class="form-row">
        <label>Role</label>
        <select name="role">
          <option value="<?= User::ROLE_PARTICIPANT ?>" <?= $values['role'] === User::ROLE_PARTICIPANT ? 'selected' : '' ?>>Participant (view + bookmark)</option>
          <option value="<?= User::ROLE_ADMIN ?>" <?= $values['role'] === User::ROLE_ADMIN ? 'selected' : '' ?>>Admin (full access)</option>
        </select>
      </div>
      <div class="form-row"><label>Temporary password</label><input type="text" name="password" required placeholder="At least 6 characters"></div>
    </div>
    <button type="submit" class="btn btn-primary">Add family member</button>
  </form>
</div>

<table class="data-table">
  <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>City</th><th>Role</th><th>Joined</th></tr></thead>
  <tbody>
    <?php foreach ($members as $m): ?>
      <tr>
        <td>
          <span class="avatar" style="width:26px;height:26px;font-size:11px;display:inline-flex;align-items:center;justify-content:center;border-radius:50%;color:#fff;background:<?= avatarColor($m->id) ?>;vertical-align:middle;margin-right:8px;"><?= e($m->initials()) ?></span>
          <?= e($m->fullName()) ?>
        </td>
        <td>@<?= e($m->username) ?></td>
        <td><?= e($m->email) ?></td>
        <td><?= e($m->city) ?></td>
        <td><span class="badge <?= $m->isAdmin() ? 'badge-admin' : 'badge-participant' ?>"><?= e($m->role) ?></span></td>
        <td><?= formatDate($m->createdAt) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php require __DIR__ . '/includes/footer.php'; ?>
