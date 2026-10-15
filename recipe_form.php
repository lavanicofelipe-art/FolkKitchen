<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = requireAdmin();

$editId = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : null);
$editing = $editId ? Recipe::findById($editId) : null;
$categories = Category::all();

$errors = [];
$values = [
    'title' => $editing->title ?? '',
    'description' => $editing->description ?? '',
    'category_id' => $editing->categoryId ?? ($categories[0]->id ?? 0),
    'servings' => $editing->servings ?? 4,
    'prep_time' => $editing->prepTime ?? 15,
    'cook_time' => $editing->cookTime ?? 20,
];
$ingredientRows = $editing
    ? array_map(fn($i) => ['name' => $i->name, 'quantity' => $i->quantity, 'unit' => $i->unit], $editing->ingredients())
    : [['name' => '', 'quantity' => '', 'unit' => '']];
if (!$ingredientRows) $ingredientRows = [['name' => '', 'quantity' => '', 'unit' => '']];

$stepRows = $editing ? array_map(fn($s) => $s->instruction, $editing->steps()) : [''];
if (!$stepRows) $stepRows = [''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['title'] = trim($_POST['title'] ?? '');
    $values['description'] = trim($_POST['description'] ?? '');
    $values['category_id'] = (int) ($_POST['category_id'] ?? 0);
    $values['servings'] = max(1, (int) ($_POST['servings'] ?? 1));
    $values['prep_time'] = max(0, (int) ($_POST['prep_time'] ?? 0));
    $values['cook_time'] = max(0, (int) ($_POST['cook_time'] ?? 0));

    $ingNames = $_POST['ing_name'] ?? [];
    $ingQtys = $_POST['ing_qty'] ?? [];
    $ingUnits = $_POST['ing_unit'] ?? [];
    $ingredientRows = [];
    $ingredientsForSave = [];
    foreach ($ingNames as $idx => $name) {
        $name = trim($name);
        $ingredientRows[] = ['name' => $name, 'quantity' => $ingQtys[$idx] ?? '', 'unit' => trim($ingUnits[$idx] ?? '')];
        if ($name !== '') {
            $ingredientsForSave[] = ['name' => $name, 'quantity' => (float) ($ingQtys[$idx] ?? 0), 'unit' => trim($ingUnits[$idx] ?? '')];
        }
    }
    if (!$ingredientRows) $ingredientRows = [['name' => '', 'quantity' => '', 'unit' => '']];

    $stepTexts = $_POST['step_text'] ?? [];
    $stepRows = array_map('trim', $stepTexts);
    if (!$stepRows) $stepRows = [''];
    $stepsForSave = array_values(array_filter(array_map('trim', $stepTexts), fn($s) => $s !== ''));

    if ($values['title'] === '') $errors[] = 'Title is required.';
    if ($values['description'] === '') $errors[] = 'Description is required.';
    if (!$values['category_id']) $errors[] = 'Please choose a category.';
    if (!$ingredientsForSave) $errors[] = 'Add at least one ingredient.';
    if (!$stepsForSave) $errors[] = 'Add at least one step.';

    if (!$errors) {
        $recipe = $editing ?: new Recipe();
        $recipe->title = $values['title'];
        $recipe->description = $values['description'];
        $recipe->categoryId = $values['category_id'];
        $recipe->servings = $values['servings'];
        $recipe->prepTime = $values['prep_time'];
        $recipe->cookTime = $values['cook_time'];
        if (!$editing) {
            $recipe->createdBy = $user->id;
        }
        $recipeId = $recipe->save();

        Ingredient::replaceForRecipe($recipeId, $ingredientsForSave);
        RecipeStep::replaceForRecipe($recipeId, $stepsForSave);

        ActivityLog::record($user->id, $recipeId, $editing ? 'Updated recipe' : 'Created recipe');
        $_SESSION['flash'] = $editing ? 'Recipe updated.' : 'Recipe added to the archive.';
        header('Location: recipe_detail.php?id=' . $recipeId);
        exit;
    }
}

$activeNav = 'add-recipe';
require __DIR__ . '/includes/header.php';
?>
<div class="topbar"><h2 class="page-title"><?= $editing ? 'Edit Recipe' : 'Add a New Recipe' ?></h2></div>

<?php if ($errors): ?>
  <div class="login-error"><?= implode('<br>', array_map('e', $errors)) ?></div>
<?php endif; ?>

<div class="card">
  <form method="post" action="recipe_form.php<?= $editing ? '?id=' . $editing->id : '' ?>">
    <?php if ($editing): ?><input type="hidden" name="id" value="<?= $editing->id ?>"><?php endif; ?>

    <div class="form-grid2">
      <div class="form-row">
        <label>Title</label>
        <input type="text" name="title" required value="<?= e($values['title']) ?>">
      </div>
      <div class="form-row">
        <label>Category</label>
        <select name="category_id" required>
          <?php foreach ($categories as $c): ?>
            <option value="<?= $c->id ?>" <?= $values['category_id'] === $c->id ? 'selected' : '' ?>><?= e($c->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-row">
      <label>Description</label>
      <textarea name="description" required><?= e($values['description']) ?></textarea>
    </div>

    <div class="form-grid3">
      <div class="form-row"><label>Servings</label><input type="number" min="1" name="servings" required value="<?= (int) $values['servings'] ?>"></div>
      <div class="form-row"><label>Prep time (min)</label><input type="number" min="0" name="prep_time" required value="<?= (int) $values['prep_time'] ?>"></div>
      <div class="form-row"><label>Cook time (min)</label><input type="number" min="0" name="cook_time" required value="<?= (int) $values['cook_time'] ?>"></div>
    </div>

    <div class="form-row">
      <label>Ingredients</label>
      <div id="ingredients-container">
        <?php foreach ($ingredientRows as $row): ?>
          <div class="dyn-row ingredient-row">
            <input type="text" name="ing_name[]" placeholder="Ingredient name" value="<?= e($row['name']) ?>">
            <input type="number" step="any" name="ing_qty[]" placeholder="Qty" value="<?= e((string) $row['quantity']) ?>">
            <input type="text" name="ing_unit[]" placeholder="Unit (cup, g…)" value="<?= e($row['unit']) ?>">
            <button type="button" class="row-remove" onclick="this.closest('.dyn-row').remove()" title="Remove">✕</button>
          </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="btn btn-ghost btn-sm add-row-btn" onclick="addRow('ingredients-container', ingredientRowHTML())">+ Add ingredient</button>
    </div>

    <div class="form-row">
      <label>Steps</label>
      <div id="steps-container">
        <?php foreach ($stepRows as $instruction): ?>
          <div class="dyn-row step-row">
            <textarea name="step_text[]" placeholder="Describe this step…" rows="2"><?= e($instruction) ?></textarea>
            <button type="button" class="row-remove" onclick="this.closest('.dyn-row').remove()" title="Remove">✕</button>
          </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="btn btn-ghost btn-sm add-row-btn" onclick="addRow('steps-container', stepRowHTML())">+ Add step</button>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><?= $editing ? 'Save Changes' : 'Save Recipe' ?></button>
      <a class="btn btn-ghost" href="<?= $editing ? 'recipe_detail.php?id=' . $editing->id : 'recipes.php' ?>">Cancel</a>
    </div>
  </form>
</div>

<script>
  function ingredientRowHTML() {
    return '<div class="dyn-row ingredient-row">' +
      '<input type="text" name="ing_name[]" placeholder="Ingredient name">' +
      '<input type="number" step="any" name="ing_qty[]" placeholder="Qty">' +
      '<input type="text" name="ing_unit[]" placeholder="Unit (cup, g…)">' +
      '<button type="button" class="row-remove" onclick="this.closest(\'.dyn-row\').remove()" title="Remove">✕</button>' +
      '</div>';
  }
  function stepRowHTML() {
    return '<div class="dyn-row step-row">' +
      '<textarea name="step_text[]" placeholder="Describe this step…" rows="2"></textarea>' +
      '<button type="button" class="row-remove" onclick="this.closest(\'.dyn-row\').remove()" title="Remove">✕</button>' +
      '</div>';
  }
  function addRow(containerId, html) {
    document.getElementById(containerId).insertAdjacentHTML('beforeend', html);
  }
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
