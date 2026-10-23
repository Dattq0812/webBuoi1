<?php
require_once __DIR__ . "/xl_database.php";

class khachhang
{
    private const LOYALTY_TIERS = [
        ['code' => 'diamond', 'label' => 'Diamond', 'threshold' => 1000000000, 'discount' => 15],
        ['code' => 'platinum', 'label' => 'Platinum', 'threshold' => 500000000, 'discount' => 10],
        ['code' => 'golden', 'label' => 'Golden', 'threshold' => 150000000, 'discount' => 5],
        ['code' => 'silver', 'label' => 'Silver', 'threshold' => 50000000, 'discount' => 3],
    ];

    public function findByIdentifier(string $identifier): array
    {
        $xl = new xl_data();
        return $xl->readPrepared(
            "SELECT * FROM khachhang WHERE email = :identifier OR sdt = :identifier LIMIT 1",
            [':identifier' => $identifier]
        );
    }

    public function findByEmail(string $email): array
    {
        $xl = new xl_data();
        return $xl->readPrepared(
            "SELECT * FROM khachhang WHERE email = :email LIMIT 1",
            [':email' => $email]
        );
    }

    public function findById(int $id): array
    {
        return (new xl_data())->readPrepared(
            "SELECT * FROM khachhang WHERE id = :id LIMIT 1",
            [':id' => $id]
        );
    }

    public function getLoyaltyInfo(int $customerId): array
    {
        $customer = $this->findById($customerId);
        if (!$customer) {
            return [];
        }

        $summary = (new xl_data())->readPrepared(
            "SELECT
                COALESCE(SUM(CASE WHEN trang_thai NOT IN ('da_huy', 'hoan_tra')
                    THEN tong_tien ELSE 0 END), 0) AS total_spending,
                COALESCE(SUM(CASE WHEN trang_thai NOT IN ('da_huy', 'hoan_tra')
                    THEN 1 ELSE 0 END), 0) AS completed_orders
             FROM donhang
             WHERE customer_id = :customer_id",
            [':customer_id' => $customerId]
        );
        $totalSpending = (float) ($summary['total_spending'] ?? 0);
        $tier = self::getTierForSpending($totalSpending);

        return [
            'customer' => $customer,
            'total_spending' => $totalSpending,
            'completed_orders' => (int) ($summary['completed_orders'] ?? 0),
            'tier' => $tier,
        ];
    }

    public function getAllWithLoyalty(): array
    {
        $rows = (new xl_data())->readPreparedAll(
            "SELECT kh.*,
                COALESCE((
                    SELECT SUM(CASE WHEN d.trang_thai NOT IN ('da_huy', 'hoan_tra')
                        THEN d.tong_tien ELSE 0 END)
                    FROM donhang d
                    WHERE d.customer_id = kh.id
                ), 0) AS total_spending,
                COALESCE((
                    SELECT SUM(CASE WHEN d.trang_thai NOT IN ('da_huy', 'hoan_tra')
                        THEN 1 ELSE 0 END)
                    FROM donhang d
                    WHERE d.customer_id = kh.id
                ), 0) AS completed_orders
             FROM khachhang kh
             ORDER BY total_spending DESC, kh.id DESC"
        );
        foreach ($rows as &$row) {
            $row['total_spending'] = (float) $row['total_spending'];
            $row['completed_orders'] = (int) $row['completed_orders'];
            $row['tier'] = self::getTierForSpending($row['total_spending']);
        }
        unset($row);
        return $rows;
    }

    public static function getTierForSpending(float $totalSpending): array
    {
        foreach (self::LOYALTY_TIERS as $tier) {
            if ($totalSpending > $tier['threshold']) {
                return $tier;
            }
        }
        return [
            'code' => 'standard',
            'label' => 'Standard',
            'threshold' => 0,
            'discount' => 0,
        ];
    }

    public function create(string $name, ?string $phone, ?string $email, string $passwordHash): int
    {
        $xl = new xl_data();
        $xl->executePrepared(
            "INSERT INTO khachhang (ho_ten, sdt, email, mat_khau)
             VALUES (:ho_ten, :sdt, :email, :mat_khau)",
            [
                ':ho_ten' => $name,
                ':sdt' => $phone !== '' ? $phone : null,
                ':email' => $email !== '' ? $email : null,
                ':mat_khau' => $passwordHash,
            ]
        );
        return (int) $xl->lastInsertId();
    }

    public function saveResetToken(int $id, string $tokenHash, string $expiresAt): void
    {
        (new xl_data())->executePrepared(
            "UPDATE khachhang
             SET reset_token = :token, reset_expires_at = :expires
             WHERE id = :id",
            [':token' => $tokenHash, ':expires' => $expiresAt, ':id' => $id]
        );
    }

    public function findByResetToken(string $tokenHash): array
    {
        return (new xl_data())->readPrepared(
            "SELECT * FROM khachhang
             WHERE reset_token = :token AND reset_expires_at > NOW()
             LIMIT 1",
            [':token' => $tokenHash]
        );
    }

    public function updatePassword(int $id, string $passwordHash): void
    {
        (new xl_data())->executePrepared(
            "UPDATE khachhang
             SET mat_khau = :mat_khau, reset_token = NULL, reset_expires_at = NULL
             WHERE id = :id",
            [':mat_khau' => $passwordHash, ':id' => $id]
        );
    }
}
