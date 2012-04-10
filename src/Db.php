<?php
declare(strict_types=1);

namespace CampusBoard;

use PDO;

final class Db
{
    public static function fromEnv(): PDO
    {
        $dsn = getenv('DB_DSN') ?: 'mysql:host=127.0.0.1;dbname=campusboard;charset=utf8mb4';
        $user = getenv('DB_USER') ?: 'campusboard';
        $pass = getenv('DB_PASS') ?: 'campusboard';
        return self::connect($dsn, $user, $pass);
    }

    public static function connect(string $dsn, ?string $user = null, ?string $pass = null): PDO
    {
        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}
