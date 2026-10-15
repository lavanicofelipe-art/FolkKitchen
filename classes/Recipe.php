<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Category.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Ingredient.php';
require_once __DIR__ . '/RecipeStep.php';

/**
 * Recipe
 * Maps to the Recipes table. The heart of the archive — every recipe
 * belongs to a Category and was archived by a User (Created_By).
 */
class Recipe
{
    public ?int $id;
    public string $title;
    public string $description;
    public int $categoryId;
    public int $createdBy;
    public int $prepTime;
    public int $cookTime;
    public int $servings;
    public ?string $createdAt;

    public function __construct(array $row = [])
    {
        $this->id          = isset($row['Recipe_ID']) ? (int) $row['Recipe_ID'] : null;
        $this->title       = $row['Title'] ?? '';
        $this->description = $row['Description'] ?? '';
        $this->categoryId  = (int) ($row['Category_ID'] ?? 0);
        $this->createdBy   = (int) ($row['Created_By'] ?? 0);
        $this->prepTime    = (int) ($row['Prep_Time'] ?? 0);
        $this->cookTime    = (int) ($row['Cook_Time'] ?? 0);
        $this->servings    = (int) ($row['Servings'] ?? 1);
        $this->createdAt   = $row['Created_At'] ?? null;
    }

    public function totalTime(): int
    {
        return $this->prepTime + $this->cookTime;
    }

    public function category(): ?Category
    {
        return Category::findById($this->categoryId);
    }

    public function creator(): ?User
    {
        return User::findById($this->createdBy);
    }

    /** @return Ingredient[] */
    public function ingredients(): array
    {
        return $this->id ? Ingredient::forRecipe($this->id) : [];
    }

    /** @return RecipeStep[] */
    public function steps(): array
    {
        return $this->id ? RecipeStep::forRecipe($this->id) : [];
    }

    /** Insert (new recipe) or update (existing). Returns the Recipe_ID. */
    public function save(): int
    {
        $db = Database::getConnection();

        if ($this->id) {
            $stmt = $db->prepare(
                'UPDATE Recipes SET Title = ?, Description = ?, Category_ID = ?, Prep_Time = ?, Cook_Time = ?, Servings = ?
                 WHERE Recipe_ID = ?'
            );
            $stmt->execute([
                $this->title, $this->description, $this->categoryId,
                $this->prepTime, $this->cookTime, $this->servings, $this->id,
            ]);
            return $this->id;
        }

        $stmt = $db->prepare(
            'INSERT INTO Recipes (Title, Description, Category_ID, Created_By, Prep_Time, Cook_Time, Servings, Created_At)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([
            $this->title, $this->description, $this->categoryId, $this->createdBy,
            $this->prepTime, $this->cookTime, $this->servings,
        ]);
        $this->id = (int) $db->lastInsertId();
        return $this->id;
    }

    /**
     * Deletes the recipe along with its ingredients, steps, and bookmarks.
     * Activity_Logs.Recipe_ID is a NOT NULL foreign key with no cascade
     * defined in the schema, so any log rows pointing at this recipe are
     * removed too (their text summary already captured what happened).
     */
    public function delete(): void
    {
        if (!$this->id) {
            return;
        }
        $db = Database::getConnection();
        Ingredient::deleteForRecipe($this->id);
        RecipeStep::deleteForRecipe($this->id);
        $db->prepare('DELETE FROM Bookmarks WHERE Recipe_ID = ?')->execute([$this->id]);
        $db->prepare('DELETE FROM Activity_Logs WHERE Recipe_ID = ?')->execute([$this->id]);
        $db->prepare('DELETE FROM Recipes WHERE Recipe_ID = ?')->execute([$this->id]);
    }

    public static function findById(int $id): ?self
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM Recipes WHERE Recipe_ID = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? new self($row) : null;
    }

    /** @return self[] most recently added first */
    public static function all(): array
    {
        $rows = Database::getConnection()
            ->query('SELECT * FROM Recipes ORDER BY Created_At DESC')
            ->fetchAll();
        return array_map(fn($row) => new self($row), $rows);
    }

    /** @return self[] newest first, limited */
    public static function recent(int $limit = 3): array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM Recipes ORDER BY Created_At DESC LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return array_map(fn($row) => new self($row), $stmt->fetchAll());
    }

    /**
     * Searches recipes by title/description text and/or category.
     * @return self[]
     */
    public static function search(string $query = '', ?int $categoryId = null): array
    {
        $sql = 'SELECT * FROM Recipes WHERE 1 = 1';
        $params = [];

        if (trim($query) !== '') {
            $sql .= ' AND (Title LIKE ? OR Description LIKE ?)';
            $like = '%' . $query . '%';
            $params[] = $like;
            $params[] = $like;
        }
        if ($categoryId) {
            $sql .= ' AND Category_ID = ?';
            $params[] = $categoryId;
        }
        $sql .= ' ORDER BY Created_At DESC';

        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute($params);
        return array_map(fn($row) => new self($row), $stmt->fetchAll());
    }

    public static function count(): int
    {
        return (int) Database::getConnection()->query('SELECT COUNT(*) FROM Recipes')->fetchColumn();
    }
}
