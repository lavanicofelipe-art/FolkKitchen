<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = requireLogin();

$activeNav = 'dashboard';
require __DIR__ . '/includes/header.php';

$recentRecipes = Recipe::recent(3);
$myBookmarkCount = Bookmark::countForUser($user->id);
$days = daysUntilChristmas();
?>
<div class="topbar">
  <div>
    <h2 class="page-title">Welcome back, <?= e($user->firstName) ?> 👋</h2>
    <div class="page-sub">Here's what's cooking in the family archive.</div>
  </div>
</div>

<div class="holiday-banner">
  <div class="ic">🎄</div>
  <div class="txt"><strong><?= $days ?></strong> day<?= $days === 1 ? '' : 's' ?> until Christmas — time to line up the Holiday Specials recipes.</div>
</div>

<div class="stat-grid">
  <div class="stat-card"><div class="stat-num"><?= Recipe::count() ?></div><div class="stat-label">Family Recipes</div></div>
  <div class="stat-card"><div class="stat-num"><?= User::count() ?></div><div class="stat-label">Family Members</div></div>
  <div class="stat-card"><div class="stat-num"><?= $myBookmarkCount ?></div><div class="stat-label">Your Bookmarks</div></div>
  <div class="stat-card"><div class="stat-num"><?= Category::count() ?></div><div class="stat-label">Categories</div></div>
</div>

<div class="section-head">
  <h3>Recently added</h3>
  <a class="link-btn" href="recipes.php">View full recipe box →</a>
</div>
<div class="recipe-grid">
  <?php if ($recentRecipes): ?>
    <?php foreach ($recentRecipes as $recipe): ?>
      <?php require __DIR__ . '/includes/recipe_card.php'; ?>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="empty-state">No recipes yet.</div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
