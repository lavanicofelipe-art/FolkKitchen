<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = requireLogin();

$id = (int) ($_GET['id'] ?? 0);
$recipe = Recipe::findById($id);

$activeNav = 'recipes';
require __DIR__ . '/includes/header.php';

if (!$recipe) {
    echo '<div class="empty-state">This recipe was removed from the archive.</div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$cat = $recipe->category();
$creator = $recipe->creator();
$ingredients = $recipe->ingredients();
$steps = $recipe->steps();
$bookmarked = Bookmark::isBookmarked($user->id, $recipe->id);
?>
<a class="btn btn-ghost btn-sm" href="recipes.php">← Back to recipe box</a>

<div class="detail-head" style="margin-top:16px;">
  <div>
    <span class="tag"><?= categoryIcon($cat?->name) ?> <?= e($cat ? $cat->name : 'Uncategorized') ?></span>
    <h2 class="page-title" style="margin-top:10px;"><?= e($recipe->title) ?></h2>
    <div class="page-sub"><?= e($recipe->description) ?></div>
  </div>
  <div class="detail-actions">
    <form method="post" action="bookmark_toggle.php" style="display:inline;">
      <input type="hidden" name="recipe_id" value="<?= $recipe->id ?>">
      <input type="hidden" name="return_to" value="recipe_detail.php?id=<?= $recipe->id ?>">
      <button type="submit" class="btn <?= $bookmarked ? 'btn-primary' : 'btn-ghost' ?> btn-sm"><?= $bookmarked ? '★ Bookmarked' : '☆ Bookmark' ?></button>
    </form>
    <button type="button" class="btn btn-ghost btn-sm" onclick="window.print()">🖨 Print</button>
    <?php if ($user->isAdmin()): ?>
      <a class="btn btn-sage btn-sm" href="recipe_form.php?id=<?= $recipe->id ?>">✎ Edit</a>
      <form method="post" action="recipe_delete.php" style="display:inline;" onsubmit="return confirm('Remove &quot;<?= e($recipe->title) ?>&quot; from the family archive? This can\'t be undone.');">
        <input type="hidden" name="id" value="<?= $recipe->id ?>">
        <button type="submit" class="btn btn-danger btn-sm">🗑 Delete</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<div class="detail-meta-row">
  <div class="meta-item"><b><?= $recipe->prepTime ?> min</b>Prep time</div>
  <div class="meta-item"><b><?= $recipe->cookTime ?> min</b>Cook time</div>
  <div class="meta-item"><b><?= $recipe->servings ?></b>Servings</div>
  <div class="meta-item"><b><?= e($creator ? $creator->fullName() : 'Unknown') ?></b>Recipe keeper</div>
  <div class="meta-item"><b><?= formatDate($recipe->createdAt) ?></b>Archived on</div>
</div>

<div class="two-col">
  <div class="card">
    <h3 style="margin-bottom:12px;">Ingredients</h3>
    <ul class="ingredient-list">
      <?php if ($ingredients): foreach ($ingredients as $i): ?>
        <li><span><?= e($i->name) ?></span><span><?= rtrim(rtrim(number_format($i->quantity, 2), '0'), '.') ?><?= $i->unit ? ' ' . e($i->unit) : '' ?></span></li>
      <?php endforeach; else: ?>
        <li>No ingredients listed.</li>
      <?php endif; ?>
    </ul>
  </div>
  <div class="card">
    <h3 style="margin-bottom:4px;">Steps</h3>
    <ol class="step-list">
      <?php if ($steps): foreach ($steps as $s): ?>
        <li><?= e($s->instruction) ?></li>
      <?php endforeach; else: ?>
        <li>No steps listed.</li>
      <?php endif; ?>
    </ol>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
