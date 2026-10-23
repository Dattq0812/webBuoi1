<?php
require_once __DIR__ . "/xl_database.php";

class duplicate_checker
{
    private const FIELDS = [
        'category_name' => ['table' => 'danhmuc', 'column' => 'name', 'id_column' => 'id'],
        'product_name' => ['table' => 'sanpham', 'column' => 'Name', 'id_column' => 'id_sp'],
        'product_id' => ['table' => 'sanpham', 'column' => 'id_sp', 'id_column' => 'id_sp'],
    ];

    public function exists(string $field, string $value, ?int $excludeId = null): bool
    {
        if (!isset(self::FIELDS[$field])) {
            throw new InvalidArgumentException('Trường kiểm tra trùng không được hỗ trợ.');
        }

        $definition = self::FIELDS[$field];
        $sql = "SELECT COUNT(*) AS total FROM `{$definition['table']}`
                WHERE LOWER(TRIM(`{$definition['column']}`)) = LOWER(TRIM(:value))";
        $params = [':value' => $value];
        if ($excludeId !== null && $excludeId > 0) {
            $sql .= " AND `{$definition['id_column']}` <> :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }

        $result = (new xl_data())->readPrepared($sql, $params);
        return (int) ($result['total'] ?? 0) > 0;
    }
}
