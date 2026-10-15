<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $recipe = Recipe::findById($id);
    if ($recipe) {
        // Activity_Logs.Recipe_ID is a NOT NULL foreign key with no cascade
        // rule, so a log entry can never outlive the recipe it points to.
        // delete() already clears this recipe's own log rows; we simply
        // don't write a new one referencing a recipe that's about to be gone.
        $recipe->delete();
        $_SESSION['flash'] = 'Recipe "' . $recipe->title . '" was removed from the archive.';
    }
}
header('Location: recipes.php');
exit;
