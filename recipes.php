<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = requireLogin();

$query = trim($_GET['q'] ?? '');
$categoryId = isset($_GET['category']) && $_GET['category'] !== '' ? (int) $_GET['category'] : null;
$results = Recipe::search($query, $categoryId);
$categories = Category::all();

$activeNav = 'recipes';
require __DIR__ . '/includes/header.php';
?>
<div class="topbar">
  <div>
    <h2 class="page-title">The Recipe Box</h2>
    <div class="page-sub"><?= count($results) ?> recipe<?= count($results) === 1 ? '' : 's' ?> in the archive</div>
  </div>
</div>

<form class="toolbar" method="get" action="recipes.php">
  <input class="input search-input" type="text" name="q" placeholder="Search by title or description…" value="<?= e($query) ?>">
  <select class="select" name="category" onchange="this.form.submit()">
    <option value="">All categories</option>
    <?php foreach ($categories as $c): ?>
      <option value="<?= $c->id ?>" <?= $categoryId === $c->id ? 'selected' : '' ?>><?= e($c->name) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn btn-ghost">Search</button>
</form>

<div class="recipe-grid">
  <?php if ($results): ?>
    <?php foreach ($results as $recipe): ?>
      <?php require __DIR__ . '/includes/recipe_card.php'; ?>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="empty-state"><div class="ic">🔍</div>No recipes match your search.</div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
