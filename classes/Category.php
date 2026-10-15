<?php
require_once __DIR__ . '/Database.php';

/**
 * Category
 * Maps to the Categories table (e.g. Mains, Desserts, Holiday Specials).
 */
class Category
{
    public ?int $id;
    public string $name;
    public string $description;

    public function __construct(array $row = [])
    {
        $this->id          = isset($row['Category_ID']) ? (int) $row['Category_ID'] : null;
        $this->name        = $row['Category_Name'] ?? '';
        $this->description = $row['Description'] ?? '';
    }

    public function save(): int
    {
        $db = Database::getConnection();
        if ($this->id) {
            $stmt = $db->prepare('UPDATE Categories SET Category_Name = ?, Description = ? WHERE Category_ID = ?');
            $stmt->execute([$this->name, $this->description, $this->id]);
            return $this->id;
        }
        $stmt = $db->prepare('INSERT INTO Categories (Category_Name, Description) VALUES (?, ?)');
        $stmt->execute([$this->name, $this->description]);
        $this->id = (int) $db->lastInsertId();
        return $this->id;
    }

    public static function findById(int $id): ?self
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM Categories WHERE Category_ID = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? new self($row) : null;
    }

    /** @return self[] */
    public static function all(): array
    {
        $rows = Database::getConnection()->query('SELECT * FROM Categories ORDER BY Category_Name')->fetchAll();
        return array_map(fn($row) => new self($row), $rows);
    }

    public static function count(): int
    {
        return (int) Database::getConnection()->query('SELECT COUNT(*) FROM Categories')->fetchColumn();
    }
}
