<?php
/**
 * Database
 * Thin PDO wrapper implemented as a singleton so every class shares one
 * open connection for the lifetime of a request.
 */
class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../config/database.php';
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['dbname'],
                $config['charset'] ?? 'utf8mb4'
            );

            try {
                self::$instance = new PDO($dsn, $config['user'], $config['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                die(
                    'Database connection failed: ' . $e->getMessage() .
                    '. Check config/database.php and make sure the "folkskitchen" ' .
                    'database has been created and imported (see database/ folder).'
                );
            }
        }

        return self::$instance;
    }
}
