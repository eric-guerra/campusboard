<?php
declare(strict_types=1);

namespace CampusBoard;

/**
 * Validates and normalizes user input. Returns [cleanData, errors].
 */
final class Validator
{
    public const MAX_PRICE_CENTS = 10_000_00;

    /** @return array{0: array<string, mixed>, 1: array<string, string>} */
    public static function listing(array $input, array $validCategoryIds): array
    {
        $errors = [];
        $title = trim((string)($input['title'] ?? ''));
        $body = trim((string)($input['body'] ?? ''));
        $category = (int)($input['category_id'] ?? 0);
        $priceRaw = trim((string)($input['price'] ?? '0'));

        if ($title === '' || mb_strlen($title) > 120) {
            $errors['title'] = 'Title is required (max 120 characters).';
        }
        if (mb_strlen($body) < 10) {
            $errors['body'] = 'Description must be at least 10 characters.';
        }
        if (!in_array($category, $validCategoryIds, true)) {
            $errors['category_id'] = 'Choose a category.';
        }

        $priceCents = self::parsePrice($priceRaw);
        if ($priceCents === null) {
            $errors['price'] = 'Price must be a number like 25 or 25.50.';
        } elseif ($priceCents > self::MAX_PRICE_CENTS) {
            $errors['price'] = 'Price must be under $10,000.';
        }

        return [[
            'title' => $title,
            'body' => $body,
            'category_id' => $category,
            'price_cents' => $priceCents ?? 0,
        ], $errors];
    }

    /** @return array{0: array<string, string>, 1: array<string, string>} */
    public static function registration(array $input): array
    {
        $errors = [];
        $email = strtolower(trim((string)($input['email'] ?? '')));
        $name = trim((string)($input['display_name'] ?? ''));
        $password = (string)($input['password'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        } elseif (!str_ends_with($email, '.edu')) {
            $errors['email'] = 'Use your university (.edu) email.';
        }
        if ($name === '' || mb_strlen($name) > 80) {
            $errors['display_name'] = 'Display name is required.';
        }
        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        return [['email' => $email, 'display_name' => $name, 'password' => $password], $errors];
    }

    /** Parses "12", "12.5", "$12.50" into cents; null when invalid. */
    public static function parsePrice(string $raw): ?int
    {
        $raw = ltrim($raw, '$');
        if ($raw === '') {
            return 0;
        }
        if (!preg_match('/^\d{1,7}(\.\d{1,2})?$/', $raw)) {
            return null;
        }
        [$whole, $frac] = array_pad(explode('.', $raw, 2), 2, '0');
        return (int)$whole * 100 + (int)str_pad($frac, 2, '0');
    }
}
