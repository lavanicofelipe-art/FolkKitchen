<?php
require_once __DIR__ . '/Database.php';

/**
 * User
 * Maps to the Users table. Handles authentication, registration, and
 * profile data for both Admins (full access) and Participants (view +
 * bookmark only).
 */
class User
{
    public const ROLE_ADMIN       = 'Admin';
    public const ROLE_PARTICIPANT = 'Participant';

    public ?int $id;
    public string $firstName;
    public string $lastName;
    public string $username;
    public string $email;
    public ?string $passwordHash;
    public ?string $birthdate;   // 'YYYY-MM-DD'
    public string $gender;
    public string $city;
    public string $role;
    public ?string $createdAt;

    public function __construct(array $row = [])
    {
        $this->id           = isset($row['User_ID']) ? (int) $row['User_ID'] : null;
        $this->firstName    = $row['First_Name'] ?? '';
        $this->lastName     = $row['Last_Name'] ?? '';
        $this->username     = $row['Username'] ?? '';
        $this->email        = $row['Email'] ?? '';
        $this->passwordHash = $row['Password_Hash'] ?? null;
        $this->birthdate    = $row['Birthdate'] ?? null;
        $this->gender       = $row['Gender'] ?? '';
        $this->city         = $row['City'] ?? '';
        $this->role         = $row['Role'] ?? self::ROLE_PARTICIPANT;
        $this->createdAt    = $row['Created_At'] ?? null;
    }

    public function fullName(): string
    {
        return trim("{$this->firstName} {$this->lastName}");
    }

    public function initials(): string
    {
        $a = $this->firstName !== '' ? substr($this->firstName, 0, 1) : '';
        $b = $this->lastName !== '' ? substr($this->lastName, 0, 1) : '';
        return strtoupper($a . $b);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function age(): ?int
    {
        if (!$this->birthdate) {
            return null;
        }
        return (new DateTime($this->birthdate))->diff(new DateTime('today'))->y;
    }

    public function verifyPassword(string $plainPassword): bool
    {
        return $this->passwordHash !== null && password_verify($plainPassword, $this->passwordHash);
    }

    /** Insert (new user) or update (existing user). Returns the User_ID. */
    public function save(): int
    {
        $db = Database::getConnection();

        if ($this->id) {
            $stmt = $db->prepare(
                'UPDATE Users SET First_Name = ?, Last_Name = ?, Username = ?, Email = ?,
                 Birthdate = ?, Gender = ?, City = ?, Role = ? WHERE User_ID = ?'
            );
            $stmt->execute([
                $this->firstName, $this->lastName, $this->username, $this->email,
                $this->birthdate, $this->gender, $this->city, $this->role, $this->id,
            ]);
            return $this->id;
        }

        $stmt = $db->prepare(
            'INSERT INTO Users (First_Name, Last_Name, Username, Email, Password_Hash, Birthdate, Gender, City, Role, Created_At)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([
            $this->firstName,
            $this->lastName,
            $this->username,
            $this->email,
            $this->passwordHash ?? password_hash(bin2hex(random_bytes(4)), PASSWORD_DEFAULT),
            $this->birthdate,
            $this->gender,
            $this->city,
            $this->role,
        ]);
        $this->id = (int) $db->lastInsertId();
        return $this->id;
    }

    public static function findById(int $id): ?self
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM Users WHERE User_ID = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? new self($row) : null;
    }

    public static function findByEmail(string $email): ?self
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM Users WHERE Email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ? new self($row) : null;
    }

    public static function findByUsername(string $username): ?self
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM Users WHERE Username = ?');
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row ? new self($row) : null;
    }

    /** @return self[] */
    public static function all(): array
    {
        $rows = Database::getConnection()
            ->query('SELECT * FROM Users ORDER BY First_Name, Last_Name')
            ->fetchAll();
        return array_map(fn($row) => new self($row), $rows);
    }

    public static function count(): int
    {
        return (int) Database::getConnection()->query('SELECT COUNT(*) FROM Users')->fetchColumn();
    }
}
