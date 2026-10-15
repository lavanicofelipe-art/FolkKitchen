<?php
require_once __DIR__ . '/Database.php';

/**
 * Ingredient
 * Maps to the Ingredients table. Always belongs to one Recipe.
 */
class Ingredient
{
    public ?int $id;
    public int $recipeId;
    public string $name;
    public float $quantity;
    public string $unit;

    public function __construct(array $row = [])
    {
        $this->id       = isset($row['Ingredient_ID']) ? (int) $row['Ingredient_ID'] : null;
        $this->recipeId = (int) ($row['Recipe_ID'] ?? 0);
        $this->name     = $row['Ingredient_Name'] ?? '';
        $this->quantity = isset($row['Quantity']) ? (float) $row['Quantity'] : 0.0;
        $this->unit     = $row['Unit'] ?? '';
    }

    /** @return self[] */
    public static function forRecipe(int $recipeId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT * FROM Ingredients WHERE Recipe_ID = ? ORDER BY Ingredient_ID'
        );
        $stmt->execute([$recipeId]);
        return array_map(fn($row) => new self($row), $stmt->fetchAll());
    }

    /**
     * Replaces all ingredients for a recipe with a fresh list.
     * $items is an array of ['name' => ..., 'quantity' => ..., 'unit' => ...]
     */
    public static function replaceForRecipe(int $recipeId, array $items): void
    {
        $db = Database::getConnection();
        $db->prepare('DELETE FROM Ingredients WHERE Recipe_ID = ?')->execute([$recipeId]);

        $stmt = $db->prepare(
            'INSERT INTO Ingredients (Recipe_ID, Ingredient_Name, Quantity, Unit) VALUES (?, ?, ?, ?)'
        );
        foreach ($items as $item) {
            if (trim($item['name'] ?? '') === '') {
                continue;
            }
            $stmt->execute([$recipeId, $item['name'], $item['quantity'] ?? 0, $item['unit'] ?? '']);
        }
    }

    public static function deleteForRecipe(int $recipeId): void
    {
        Database::getConnection()->prepare('DELETE FROM Ingredients WHERE Recipe_ID = ?')->execute([$recipeId]);
    }
}
