<?php
require_once __DIR__ . "/xl_database.php";

class danhgia
{
    public function __construct()
    {
        $xl = new xl_data();
        $xl->execute_item(
            "CREATE TABLE IF NOT EXISTS product_reviews (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                customer_id INT NOT NULL,
                rating TINYINT NOT NULL,
                body TEXT NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'visible',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL,
                INDEX idx_product_reviews_product (product_id),
                INDEX idx_product_reviews_status (status),
                CONSTRAINT chk_product_review_rating CHECK (rating BETWEEN 1 AND 5)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        $xl->execute_item(
            "CREATE TABLE IF NOT EXISTS product_review_images (
                id INT AUTO_INCREMENT PRIMARY KEY,
                review_id INT NOT NULL,
                image_path VARCHAR(255) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_review_images_review (review_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        $xl->execute_item(
            "CREATE TABLE IF NOT EXISTS product_review_comments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                review_id INT NOT NULL,
                customer_id INT NULL,
                author_type VARCHAR(20) NOT NULL DEFAULT 'customer',
                author_name VARCHAR(120) NOT NULL,
                body TEXT NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_review_comments_review (review_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    public function getForProduct(int $productId, bool $includeHidden = false): array
    {
        $statusCondition = $includeHidden ? '' : " AND pr.status = 'visible'";
        $rows = (new xl_data())->readPreparedAll(
            "SELECT pr.id, pr.product_id, pr.customer_id, pr.rating, pr.body, pr.status,
                    pr.created_at, kh.ho_ten AS customer_name
             FROM product_reviews pr
             LEFT JOIN khachhang kh ON kh.id = pr.customer_id
             WHERE pr.product_id = :product_id{$statusCondition}
             ORDER BY pr.created_at DESC, pr.id DESC",
            [':product_id' => $productId]
        );
        return $this->attachDetails($rows);
    }

    public function getSummary(int $productId): array
    {
        $summary = (new xl_data())->readPrepared(
            "SELECT COUNT(*) AS review_count, COALESCE(AVG(rating), 0) AS average_rating
             FROM product_reviews
             WHERE product_id = :product_id AND status = 'visible'",
            [':product_id' => $productId]
        );
        return [
            'review_count' => (int) ($summary['review_count'] ?? 0),
            'average_rating' => (float) ($summary['average_rating'] ?? 0),
        ];
    }

    public function getAllForManagement(): array
    {
        $rows = (new xl_data())->readPreparedAll(
            "SELECT pr.id, pr.product_id, pr.customer_id, pr.rating, pr.body, pr.status,
                    pr.created_at, sp.Name AS product_name, kh.ho_ten AS customer_name
             FROM product_reviews pr
             INNER JOIN sanpham sp ON sp.id_sp = pr.product_id
             LEFT JOIN khachhang kh ON kh.id = pr.customer_id
             ORDER BY pr.created_at DESC, pr.id DESC"
        );
        return $this->attachDetails($rows);
    }

    public function create(int $productId, int $customerId, int $rating, string $body, array $imagePaths = []): int
    {
        if ($rating < 1 || $rating > 5 || $body === '' || mb_strlen($body) > 3000) {
            throw new InvalidArgumentException('Nội dung hoặc điểm đánh giá không hợp lệ.');
        }
        $xl = new xl_data();
        $reviewId = 0;
        $xl->transaction(function (PDO $connection) use (
            $productId, $customerId, $rating, $body, $imagePaths, &$reviewId
        ): void {
            $statement = $connection->prepare(
                "INSERT INTO product_reviews (product_id, customer_id, rating, body)
                 VALUES (:product_id, :customer_id, :rating, :body)"
            );
            $statement->execute([
                ':product_id' => $productId,
                ':customer_id' => $customerId,
                ':rating' => $rating,
                ':body' => $body,
            ]);
            $reviewId = (int) $connection->lastInsertId();
            if ($imagePaths) {
                $imageStatement = $connection->prepare(
                    "INSERT INTO product_review_images (review_id, image_path)
                     VALUES (:review_id, :image_path)"
                );
                foreach ($imagePaths as $imagePath) {
                    $imageStatement->execute([
                        ':review_id' => $reviewId,
                        ':image_path' => $imagePath,
                    ]);
                }
            }
        });
        return $reviewId;
    }

    public function addComment(
        int $reviewId,
        ?int $customerId,
        string $authorType,
        string $authorName,
        string $body,
        bool $allowHidden = false
    ): void
    {
        if (!in_array($authorType, ['customer', 'manager'], true) || $body === '' || mb_strlen($body) > 2000) {
            throw new InvalidArgumentException('Bình luận không hợp lệ.');
        }
        $statusCondition = $allowHidden ? '' : " AND status = 'visible'";
        (new xl_data())->executePrepared(
            "INSERT INTO product_review_comments
                (review_id, customer_id, author_type, author_name, body)
             SELECT :review_id, :customer_id, :author_type, :author_name, :body
             FROM product_reviews
             WHERE id = :review_id_check{$statusCondition}",
            [
                ':review_id' => $reviewId,
                ':customer_id' => $customerId,
                ':author_type' => $authorType,
                ':author_name' => $authorName,
                ':body' => $body,
                ':review_id_check' => $reviewId,
            ]
        );
    }

    public function updateStatus(int $reviewId, string $status): void
    {
        if (!in_array($status, ['visible', 'hidden'], true)) {
            throw new InvalidArgumentException('Trạng thái bài đánh giá không hợp lệ.');
        }
        (new xl_data())->executePrepared(
            "UPDATE product_reviews SET status = :status, updated_at = NOW() WHERE id = :id",
            [':status' => $status, ':id' => $reviewId]
        );
    }

    public function delete(int $reviewId): void
    {
        $xl = new xl_data();
        $images = $xl->readPreparedAll(
            "SELECT image_path FROM product_review_images WHERE review_id = :id",
            [':id' => $reviewId]
        );
        $xl->transaction(function (PDO $connection) use ($reviewId): void {
            foreach ([
                'DELETE FROM product_review_comments WHERE review_id = :id',
                'DELETE FROM product_review_images WHERE review_id = :id',
                'DELETE FROM product_reviews WHERE id = :id',
            ] as $sql) {
                $statement = $connection->prepare($sql);
                $statement->execute([':id' => $reviewId]);
            }
        });
        foreach ($images as $image) {
            $path = __DIR__ . '/../view/images/reviews/' . basename((string) $image['image_path']);
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    private function attachDetails(array $rows): array
    {
        if (!$rows) {
            return [];
        }
        $xl = new xl_data();
        $reviewIds = array_map(static fn (array $row): int => (int) $row['id'], $rows);
        $placeholders = implode(',', array_fill(0, count($reviewIds), '?'));
        $images = $xl->readPreparedAll(
            "SELECT review_id, image_path FROM product_review_images WHERE review_id IN ({$placeholders})",
            $reviewIds
        );
        $comments = $xl->readPreparedAll(
            "SELECT review_id, author_type, author_name, body, created_at
             FROM product_review_comments
             WHERE review_id IN ({$placeholders})
             ORDER BY created_at ASC, id ASC",
            $reviewIds
        );
        $imagesByReview = [];
        foreach ($images as $image) {
            $imagesByReview[(int) $image['review_id']][] = basename((string) $image['image_path']);
        }
        $commentsByReview = [];
        foreach ($comments as $comment) {
            $commentsByReview[(int) $comment['review_id']][] = $comment;
        }
        foreach ($rows as &$row) {
            $id = (int) $row['id'];
            $row['images'] = $imagesByReview[$id] ?? [];
            $row['comments'] = $commentsByReview[$id] ?? [];
            $row['customer_name'] = $row['customer_name'] ?: 'Khách hàng';
        }
        unset($row);
        return $rows;
    }
}
