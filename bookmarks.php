<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = requireLogin();

$bookmarked = Bookmark::recipesForUser($user->id);

$activeNav = 'bookmarks';
require __DIR__ . '/includes/header.php';
?>
<div class="topbar">
  <h2 class="page-title">My Bookmarks</h2>
  <div class="page-sub">Your favorite family recipes, one tap away.</div>
</div>

<div class="recipe-grid">
  <?php if ($bookmarked): ?>
    <?php foreach ($bookmarked as $recipe): ?>
      <?php require __DIR__ . '/includes/recipe_card.php'; ?>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="empty-state"><div class="ic">⭐</div>You haven't bookmarked any recipes yet.</div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
