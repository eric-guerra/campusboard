<?php
declare(strict_types=1);

namespace CampusBoard\Tests;

use CampusBoard\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public static function prices(): array
    {
        return [
            ['', 0], ['0', 0], ['25', 2500], ['25.5', 2550], ['25.05', 2505], ['$12.99', 1299],
            ['abc', null], ['1.234', null], ['-5', null], ['12,00', null],
        ];
    }

    #[DataProvider('prices')]
    public function testParsePrice(string $raw, ?int $expected): void
    {
        self::assertSame($expected, Validator::parsePrice($raw));
    }

    public function testValidListing(): void
    {
        [$data, $errors] = Validator::listing(
            ['title' => '  Calculus 8th ed. ', 'body' => 'Barely used, no highlighting.', 'category_id' => '1', 'price' => '40'],
            [1, 2]
        );
        self::assertSame([], $errors);
        self::assertSame('Calculus 8th ed.', $data['title']);
        self::assertSame(4000, $data['price_cents']);
        self::assertSame(1, $data['category_id']);
    }

    public function testInvalidListingReportsEveryField(): void
    {
        [, $errors] = Validator::listing(['title' => '', 'body' => 'short', 'category_id' => '9', 'price' => 'free'], [1]);
        self::assertSame(['title', 'body', 'category_id', 'price'], array_keys($errors));
    }

    public function testRegistrationRequiresEduEmail(): void
    {
        [, $errors] = Validator::registration(['email' => 'a@gmail.com', 'display_name' => 'A', 'password' => 'longenough']);
        self::assertArrayHasKey('email', $errors);

        [$data, $errors] = Validator::registration(['email' => ' A@UH.EDU ', 'display_name' => 'A', 'password' => 'longenough']);
        self::assertSame([], $errors);
        self::assertSame('a@uh.edu', $data['email']);
    }
}
