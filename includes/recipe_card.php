<?php
/**
 * recipe_card.php
 * Renders one recipe card. Expects $recipe (Recipe) and $user (User) to
 * already be set by the including page.
 */
/** @var Recipe $recipe */
/** @var User $user */
$cat = $recipe->category();
$bookmarked = Bookmark::isBookmarked($user->id, $recipe->id);
?>
<div class="recipe-card-wrap" style="position:relative;">
  <form method="post" action="bookmark_toggle.php" style="display:inline;">
    <input type="hidden" name="recipe_id" value="<?= $recipe->id ?>">
    <input type="hidden" name="return_to" value="<?= e($_SERVER['REQUEST_URI']) ?>">
    <button type="submit" class="bookmark-star <?= $bookmarked ? 'active' : '' ?>" title="Bookmark"><?= $bookmarked ? '★' : '☆' ?></button>
  </form>
  <a class="recipe-card" href="recipe_detail.php?id=<?= $recipe->id ?>">
    <h4><?= e($recipe->title) ?></h4>
    <div class="desc"><?= e($recipe->description) ?></div>
    <div class="recipe-meta">
      <span class="tag"><?= categoryIcon($cat?->name) ?> <?= e($cat ? $cat->name : 'Uncategorized') ?></span>
      <span>⏱ <?= $recipe->totalTime() ?> min</span>
      <span>🍽 Serves <?= $recipe->servings ?></span>
    </div>
  </a>
</div>
