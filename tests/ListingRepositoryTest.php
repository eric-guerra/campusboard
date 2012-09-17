<?php
declare(strict_types=1);

namespace CampusBoard\Tests;

use CampusBoard\Auth;
use CampusBoard\Db;
use CampusBoard\ListingRepository;
use PDO;
use PHPUnit\Framework\TestCase;

/** Runs against in-memory SQLite with an equivalent schema. */
final class ListingRepositoryTest extends TestCase
{
    private PDO $db;
    private ListingRepository $repo;
    private int $alice;
    private int $bob;

    protected function setUp(): void
    {
        $this->db = Db::connect('sqlite::memory:');
        $this->db->exec(<<<SQL
            CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT UNIQUE, display_name TEXT,
                password_hash TEXT, created_at TEXT DEFAULT CURRENT_TIMESTAMP);
            CREATE TABLE categories (id INTEGER PRIMARY KEY AUTOINCREMENT, slug TEXT UNIQUE, name TEXT);
            CREATE TABLE listings (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, category_id INTEGER,
                title TEXT, body TEXT, price_cents INTEGER DEFAULT 0, status TEXT DEFAULT 'active',
                created_at TEXT DEFAULT CURRENT_TIMESTAMP, updated_at TEXT DEFAULT CURRENT_TIMESTAMP);
            INSERT INTO categories (slug, name) VALUES ('textbooks', 'Textbooks'), ('furniture', 'Furniture');
        SQL);
        $auth = new Auth($this->db);
        $this->alice = $auth->register('alice@uh.edu', 'Alice', 'password1');
        $this->bob = $auth->register('bob@uh.edu', 'Bob', 'password2');
        $this->repo = new ListingRepository($this->db);
    }

    private function make(int $user, int $cat, string $title): int
    {
        return $this->repo->create($user, ['category_id' => $cat, 'title' => $title, 'body' => "$title description", 'price_cents' => 1000]);
    }

    public function testSearchByKeywordAndCategory(): void
    {
        $this->make($this->alice, 1, 'Organic Chemistry textbook');
        $this->make($this->alice, 2, 'IKEA desk');
        $this->make($this->bob, 1, 'Physics 101 textbook');

        self::assertSame(2, $this->repo->search('textbook', null)['total']);
        self::assertSame(1, $this->repo->search(null, 'furniture')['total']);
        self::assertSame(1, $this->repo->search('physics', 'textbooks')['total']);
        self::assertSame(0, $this->repo->search('desk', 'textbooks')['total']);
    }

    public function testLikeWildcardsInQueryAreEscaped(): void
    {
        $this->make($this->alice, 1, 'Plain book');
        self::assertSame(0, $this->repo->search('%', null)['total']);
    }

    public function testOnlyOwnerCanChangeStatusAndSoldItemsLeaveSearch(): void
    {
        $id = $this->make($this->alice, 2, 'Futon');
        self::assertFalse($this->repo->setStatus($id, $this->bob, 'sold'));
        self::assertTrue($this->repo->setStatus($id, $this->alice, 'sold'));
        self::assertSame(0, $this->repo->search('futon', null)['total']);
        self::assertSame('sold', $this->repo->find($id)['status']);
    }

    public function testPaginationClampsPageNumber(): void
    {
        for ($i = 0; $i < 25; $i++) {
            $this->make($this->alice, 1, "Book $i");
        }
        $page2 = $this->repo->search(null, null, 2);
        self::assertSame(2, $page2['pages']);
        self::assertCount(5, $page2['items']);
        self::assertSame(2, $this->repo->search(null, null, 99)['page']);
    }

    public function testAuthRejectsDuplicatesAndBadPasswords(): void
    {
        $auth = new Auth($this->db);
        self::assertIsString($auth->register('alice@uh.edu', 'Again', 'whatever1'));
        self::assertNull($auth->attempt('alice@uh.edu', 'wrong'));
        self::assertSame('Alice', $auth->attempt('ALICE@uh.edu', 'password1')['display_name']);
    }
}
