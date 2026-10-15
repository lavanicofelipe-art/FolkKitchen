<?php
require_once __DIR__ . '/Database.php';

/**
 * RecipeStep
 * Maps to the Recipe_Steps table. Always belongs to one Recipe and is
 * ordered by Step_Number.
 */
class RecipeStep
{
    public ?int $id;
    public int $recipeId;
    public int $stepNumber;
    public string $instruction;

    public function __construct(array $row = [])
    {
        $this->id          = isset($row['Step_ID']) ? (int) $row['Step_ID'] : null;
        $this->recipeId    = (int) ($row['Recipe_ID'] ?? 0);
        $this->stepNumber  = (int) ($row['Step_Number'] ?? 0);
        $this->instruction = $row['Instruction'] ?? '';
    }

    /** @return self[] ordered by Step_Number */
    public static function forRecipe(int $recipeId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT * FROM Recipe_Steps WHERE Recipe_ID = ? ORDER BY Step_Number'
        );
        $stmt->execute([$recipeId]);
        return array_map(fn($row) => new self($row), $stmt->fetchAll());
    }

    /**
     * Replaces all steps for a recipe with a fresh, re-numbered list.
     * $instructions is a plain array of instruction strings, in order.
     */
    public static function replaceForRecipe(int $recipeId, array $instructions): void
    {
        $db = Database::getConnection();
        $db->prepare('DELETE FROM Recipe_Steps WHERE Recipe_ID = ?')->execute([$recipeId]);

        $stmt = $db->prepare(
            'INSERT INTO Recipe_Steps (Recipe_ID, Step_Number, Instruction) VALUES (?, ?, ?)'
        );
        $stepNumber = 1;
        foreach ($instructions as $instruction) {
            $instruction = trim($instruction);
            if ($instruction === '') {
                continue;
            }
            $stmt->execute([$recipeId, $stepNumber, $instruction]);
            $stepNumber++;
        }
    }

    public static function deleteForRecipe(int $recipeId): void
    {
        Database::getConnection()->prepare('DELETE FROM Recipe_Steps WHERE Recipe_ID = ?')->execute([$recipeId]);
    }
}
