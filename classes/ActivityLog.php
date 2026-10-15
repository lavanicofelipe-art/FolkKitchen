<?php
require_once __DIR__ . '/Database.php';

/**
 * ActivityLog
 * Maps to the Activity_Logs table. A running audit trail of who did what
 * to which recipe, and when.
 */
class ActivityLog
{
    public ?int $id;
    public int $userId;
    public ?int $recipeId;
    public string $action;
    public ?string $logDate;

    public function __construct(array $row = [])
    {
        $this->id       = isset($row['Log_ID']) ? (int) $row['Log_ID'] : null;
        $this->userId   = (int) ($row['User_ID'] ?? 0);
        $this->recipeId = isset($row['Recipe_ID']) ? (int) $row['Recipe_ID'] : null;
        $this->action   = $row['Action'] ?? '';
        $this->logDate  = $row['Log_Date'] ?? null;
    }

    /**
     * Records an action against a recipe. Recipe_ID is a required (NOT
     * NULL) foreign key in the schema, so every logged action must point
     * at a recipe that currently exists — this method is only called for
     * create/update/bookmark actions, never for events with no recipe
     * (like a deleted recipe or a new member joining).
     */
    public static function record(int $userId, int $recipeId, string $action): void
    {
        $stmt = Database::getConnection()->prepare(
            'INSERT INTO Activity_Logs (User_ID, Recipe_ID, Action, Log_Date) VALUES (?, ?, ?, NOW())'
        );
        $stmt->execute([$userId, $recipeId, $action]);
    }

    /** @return array<int, array{log: self, userName: string, recipeTitle: ?string}> */
    public static function recent(int $limit = 60): array
    {
        $sql = 'SELECT l.*, u.First_Name, u.Last_Name, r.Title AS Recipe_Title
                FROM Activity_Logs l
                LEFT JOIN Users u ON u.User_ID = l.User_ID
                LEFT JOIN Recipes r ON r.Recipe_ID = l.Recipe_ID
                ORDER BY l.Log_Date DESC
                LIMIT ?';
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();

        $results = [];
        foreach ($stmt->fetchAll() as $row) {
            $results[] = [
                'log'         => new self($row),
                'userName'    => trim(($row['First_Name'] ?? '') . ' ' . ($row['Last_Name'] ?? '')) ?: 'Unknown',
                'recipeTitle' => $row['Recipe_Title'] ?? null,
            ];
        }
        return $results;
    }
}
