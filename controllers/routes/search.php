<?php

require_once __DIR__ . "/../controller.php";
require_once __DIR__ . "/../../services/mailer.php";
require_once __DIR__ . "/../../services/auth.php";
require_once __DIR__ . "/../../services/customer_auth.php";

switch ($act)
{
        case 'search_suggestions':
            header('Content-Type: application/json; charset=utf-8');
            $keyword = trim($_GET['q'] ?? '');
            if ($keyword === '') {
                echo json_encode(['success' => true, 'products' => []], JSON_UNESCAPED_UNICODE);
                exit;
            }
            try {
                $products = (new controller())->searchProducts($keyword, 6);
                echo json_encode([
                    'success' => true,
                    'products' => array_map(static function (array $product): array {
                        return [
                            'id' => (int) $product['id_sp'],
                            'name' => $product['Name'],
                            'price' => number_format((float) $product['Price'], 0, ',', '.') . 'Ä‘',
                            'image' => basename((string) ($product['image'] ?? '')),
                        ];
                    }, $products),
                ], JSON_UNESCAPED_UNICODE);
            } catch (Throwable $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'products' => []], JSON_UNESCAPED_UNICODE);
            }
            exit;
        case 'search':
            $keyword = trim($_GET['q'] ?? '');
            $searchResults = $keyword === '' ? [] : (new controller())->searchProducts($keyword);
            include __DIR__ . "/../../view/search.php";
            break;
        case 'check_duplicate':
            header('Content-Type: application/json; charset=utf-8');
            $field = trim($_GET['field'] ?? '');
            $value = trim($_GET['value'] ?? '');
            $excludeId = filter_input(INPUT_GET, 'exclude_id', FILTER_VALIDATE_INT);
            if ($value === '' || $field === '') {
                echo json_encode(['success' => false, 'duplicate' => false, 'message' => 'Thiáº¿u dá»¯ liá»‡u kiá»ƒm tra.']);
                exit;
            }
            try {
                $duplicate = (new controller())->isDuplicate($field, $value, $excludeId ?: null);
                echo json_encode(['success' => true, 'duplicate' => $duplicate]);
            } catch (Throwable $e) {
                http_response_code(400);
                echo json_encode(['success' => false, 'duplicate' => false, 'message' => $e->getMessage()]);
            }
            exit;
}
?>

