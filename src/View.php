<?php
declare(strict_types=1);

namespace CampusBoard;

final class View
{
    private static string $templateDir = __DIR__ . '/../templates';

    public static function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function money(int $cents): string
    {
        return $cents === 0 ? 'Free' : '$' . number_format($cents / 100, 2);
    }

    public static function render(string $template, array $vars = []): string
    {
        extract($vars, EXTR_SKIP);
        ob_start();
        require self::$templateDir . "/$template.php";
        $content = ob_get_clean();
        ob_start();
        require self::$templateDir . '/layout.php';
        return (string)ob_get_clean();
    }

    public static function json(mixed $data, int $status = 200): string
    {
        http_response_code($status);
        header('Content-Type: application/json');
        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
