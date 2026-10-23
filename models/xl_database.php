<?php
require_once __DIR__ . "/../models/database.php";
class xl_data extends database{
    public function readitem($sql) {
        $conn = $this->connection_database();
        $result = $conn->query($sql);
        
        // Cải tiến: Kiểm tra xem truy vấn có thành công không trước khi fetchAll
        if ($result) {
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }
        return []; // Trả về mảng rỗng nếu lỗi
    }
    function execute_item($sql){
        $this->connection_database()->query($sql);
    }

    public function executePrepared(string $sql, array $params = []): bool {
        $statement = $this->connection_database()->prepare($sql);
        return $statement->execute($params);
    }

    public function transaction(callable $callback): void {
        $connection = $this->connection_database();
        $connection->beginTransaction();
        try {
            $callback($connection);
            $connection->commit();
        } catch (Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            throw $exception;
        }
    }
}
?>