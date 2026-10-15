<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Recipe.php';

/**
 * Bookmark
 * Maps to the Bookmarks table — a join between a User and a Recipe
 * representing a saved favorite.
 */
class Bookmark
{
    public ?int $id;
    public int $userId;
    public int $recipeId;
    public ?string $dateBookmarked;

    public function __construct(array $row = [])
    {
        $this->id             = isset($row['Bookmark_ID']) ? (int) $row['Bookmark_ID'] : null;
        $this->userId         = (int) ($row['User_ID'] ?? 0);
        $this->recipeId       = (int) ($row['Recipe_ID'] ?? 0);
        $this->dateBookmarked = $row['Date_Bookmarked'] ?? null;
    }

    public static function isBookmarked(int $userId, int $recipeId): bool
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT COUNT(*) FROM Bookmarks WHERE User_ID = ? AND Recipe_ID = ?'
        );
        $stmt->execute([$userId, $recipeId]);
        return ((int) $stmt->fetchColumn()) > 0;
    }

    /** Adds the bookmark if missing, removes it if present. Returns the new state. */
    public static function toggle(int $userId, int $recipeId): bool
    {
        $db = Database::getConnection();
        if (self::isBookmarked($userId, $recipeId)) {
            $stmt = $db->prepare('DELETE FROM Bookmarks WHERE User_ID = ? AND Recipe_ID = ?');
            $stmt->execute([$userId, $recipeId]);
            return false;
        }
        $stmt = $db->prepare(
            'INSERT INTO Bookmarks (User_ID, Recipe_ID, Date_Bookmarked) VALUES (?, ?, NOW())'
        );
        $stmt->execute([$userId, $recipeId]);
        return true;
    }

    /** @return Recipe[] every recipe this user has bookmarked, newest first */
    public static function recipesForUser(int $userId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT r.* FROM Bookmarks b
             JOIN Recipes r ON r.Recipe_ID = b.Recipe_ID
             WHERE b.User_ID = ?
             ORDER BY b.Date_Bookmarked DESC'
        );
        $stmt->execute([$userId]);
        return array_map(fn($row) => new Recipe($row), $stmt->fetchAll());
    }

    public static function countForUser(int $userId): int
    {
        $stmt = Database::getConnection()->prepare('SELECT COUNT(*) FROM Bookmarks WHERE User_ID = ?');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }
}
