<?php
declare(strict_types=1);

namespace CampusBoard;

final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . View::e(self::token()) . '">';
    }

    public static function check(?string $submitted): bool
    {
        return is_string($submitted) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $submitted);
    }
}
