<?php
declare(strict_types=1);

namespace CampusBoard;

use PDO;

final class ListingRepository
{
    public const PAGE_SIZE = 20;

    public function __construct(private PDO $db)
    {
    }

    /** @return list<array<string, mixed>> */
    public function categories(): array
    {
        return $this->db->query('SELECT id, slug, name FROM categories ORDER BY name')->fetchAll();
    }

    /** @return list<int> */
    public function categoryIds(): array
    {
        return array_map('intval', array_column($this->categories(), 'id'));
    }

    /**
     * Search active listings. Uses LIKE so it works on both MySQL and SQLite (tests);
     * the FULLTEXT index in schema.sql is used by the MATCH variant in production.
     *
     * @return array{items: list<array<string, mixed>>, total: int, page: int, pages: int}
     */
    public function search(?string $q, ?string $categorySlug, int $page = 1): array
    {
        $where = ["l.status = 'active'"];
        $params = [];
        if ($q !== null && trim($q) !== '') {
            $where[] = '(l.title LIKE :q OR l.body LIKE :q)';
            $params[':q'] = '%' . addcslashes(trim($q), '%_') . '%';
        }
        if ($categorySlug !== null && $categorySlug !== '') {
            $where[] = 'c.slug = :slug';
            $params[':slug'] = $categorySlug;
        }
        $whereSql = implode(' AND ', $where);

        $count = $this->db->prepare("SELECT COUNT(*) FROM listings l JOIN categories c ON c.id = l.category_id WHERE $whereSql");
        $count->execute($params);
        $total = (int)$count->fetchColumn();

        $pages = max(1, (int)ceil($total / self::PAGE_SIZE));
        $page = min(max(1, $page), $pages);
        $offset = ($page - 1) * self::PAGE_SIZE;

        $stmt = $this->db->prepare(
            "SELECT l.id, l.title, l.price_cents, l.created_at, c.name AS category, c.slug AS category_slug,
                    u.display_name AS seller
             FROM listings l
             JOIN categories c ON c.id = l.category_id
             JOIN users u ON u.id = l.user_id
             WHERE $whereSql
             ORDER BY l.created_at DESC, l.id DESC
             LIMIT " . self::PAGE_SIZE . " OFFSET $offset"
        );
        $stmt->execute($params);

        return ['items' => $stmt->fetchAll(), 'total' => $total, 'page' => $page, 'pages' => $pages];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT l.*, c.name AS category, u.display_name AS seller, u.email AS seller_email
             FROM listings l
             JOIN categories c ON c.id = l.category_id
             JOIN users u ON u.id = l.user_id
             WHERE l.id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function create(int $userId, array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO listings (user_id, category_id, title, body, price_cents)
             VALUES (:user_id, :category_id, :title, :body, :price_cents)'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':category_id' => $data['category_id'],
            ':title' => $data['title'],
            ':body' => $data['body'],
            ':price_cents' => $data['price_cents'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    /** Only the owner may change a listing's status. Returns false if not permitted. */
    public function setStatus(int $id, int $userId, string $status): bool
    {
        if (!in_array($status, ['active', 'sold', 'removed'], true)) {
            throw new \InvalidArgumentException("Invalid status $status");
        }
        $stmt = $this->db->prepare('UPDATE listings SET status = :status WHERE id = :id AND user_id = :user_id');
        $stmt->execute([':status' => $status, ':id' => $id, ':user_id' => $userId]);
        return $stmt->rowCount() === 1;
    }

    /** @return list<array<string, mixed>> */
    public function byUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, title, price_cents, status, created_at FROM listings
             WHERE user_id = :user_id AND status <> 'removed' ORDER BY created_at DESC"
        );
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
