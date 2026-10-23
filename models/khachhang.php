<?php
require_once __DIR__ . "/xl_database.php";

class khachhang
{
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
