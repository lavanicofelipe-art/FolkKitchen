<?php
/**
 * header.php
 * Shared page shell: <head>, sidebar navigation, and the opening <main> tag.
 * Every protected page sets $activeNav before including this file so the
 * matching sidebar item gets highlighted.
 */
$user = currentUser();
$activeNav = $activeNav ?? '';

$navItems = [
    ['id' => 'dashboard',  'href' => 'index.php',       'icon' => '🏠', 'label' => 'Dashboard'],
    ['id' => 'recipes',    'href' => 'recipes.php',     'icon' => '📖', 'label' => 'Recipe Box'],
    ['id' => 'add-recipe', 'href' => 'recipe_form.php', 'icon' => '➕', 'label' => 'Add Recipe', 'adminOnly' => true],
    ['id' => 'bookmarks',  'href' => 'bookmarks.php',   'icon' => '⭐', 'label' => 'My Bookmarks'],
    ['id' => 'members',    'href' => 'members.php',     'icon' => '👪', 'label' => 'Family Members', 'adminOnly' => true],
    ['id' => 'activity',   'href' => 'activity.php',    'icon' => '🕘', 'label' => 'Activity Log'],
    ['id' => 'profile',    'href' => 'profile.php',     'icon' => '🙂', 'label' => 'My Profile'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FolksKitchen — Family Recipe Archive</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500;1,9..144,600&family=Karla:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php if ($user): ?>
<div id="app-shell" class="active">
  <aside class="sidebar">
    <div class="brand">
      <div class="brand-mark">🍲</div>
      <div>
        <div class="brand-name">FolksKitchen</div>
        <div class="brand-tag">the family recipe box</div>
      </div>
    </div>
    <nav class="nav">
      <?php foreach ($navItems as $item): ?>
        <?php if (!empty($item['adminOnly']) && !$user->isAdmin()) continue; ?>
        <a class="nav-item <?= $activeNav === $item['id'] ? 'active' : '' ?>" href="<?= e($item['href']) ?>">
          <span class="ic"><?= $item['icon'] ?></span> <?= e($item['label']) ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="avatar" style="background:<?= avatarColor($user->id) ?>"><?= e($user->initials()) ?></div>
        <div>
          <div class="name"><?= e($user->fullName()) ?></div>
          <div class="role"><?= e($user->role) ?></div>
        </div>
      </div>
      <a class="logout-btn" href="logout.php" style="display:block; text-align:center; text-decoration:none;">Log out</a>
    </div>
  </aside>
  <main class="main">
    <?php if (!empty($_SESSION['flash'])): ?>
      <div class="flash"><?= e($_SESSION['flash']) ?></div>
      <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>
<?php else: ?>
  <main class="main" style="max-width:none;">
<?php endif; ?>
