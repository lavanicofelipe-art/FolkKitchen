<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipeId = (int) ($_POST['recipe_id'] ?? 0);
    if ($recipeId && Recipe::findById($recipeId)) {
        $nowBookmarked = Bookmark::toggle($user->id, $recipeId);
        ActivityLog::record($user->id, $recipeId, $nowBookmarked ? 'Bookmarked recipe' : 'Removed bookmark');
        $_SESSION['flash'] = $nowBookmarked ? 'Added to your bookmarks.' : 'Removed from your bookmarks.';
    }
}

$returnTo = $_POST['return_to'] ?? 'recipes.php';
// Only allow relative redirects within this app, never an external URL.
if (!preg_match('#^[a-zA-Z0-9_./?=&-]+$#', $returnTo) || str_starts_with($returnTo, '//')) {
    $returnTo = 'recipes.php';
}
header('Location: ' . $returnTo);
exit;
