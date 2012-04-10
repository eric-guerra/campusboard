<?php
declare(strict_types=1);

namespace CampusBoard;

use PDO;

final class Auth
{
    public function __construct(private PDO $db)
    {
    }

    /** @return int|string new user id, or an error message */
    public function register(string $email, string $displayName, string $password): int|string
    {
        $exists = $this->db->prepare('SELECT 1 FROM users WHERE email = :email');
        $exists->execute([':email' => $email]);
        if ($exists->fetchColumn()) {
            return 'An account with that email already exists.';
        }
        $stmt = $this->db->prepare(
            'INSERT INTO users (email, display_name, password_hash) VALUES (:email, :name, :hash)'
        );
        $stmt->execute([
            ':email' => $email,
            ':name' => $displayName,
            ':hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function attempt(string $email, string $password): ?array
    {
        $stmt = $this->db->prepare('SELECT id, email, display_name, password_hash FROM users WHERE email = :email');
        $stmt->execute([':email' => strtolower(trim($email))]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $this->db->prepare('UPDATE users SET password_hash = :h WHERE id = :id')
                ->execute([':h' => password_hash($password, PASSWORD_DEFAULT), ':id' => $user['id']]);
        }
        unset($user['password_hash']);
        return $user;
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int)$user['id'], 'display_name' => $user['display_name']];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }
}
