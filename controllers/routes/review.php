<?php

require_once __DIR__ . "/../controller.php";
require_once __DIR__ . "/../../services/auth.php";
require_once __DIR__ . "/../../services/customer_auth.php";

function reviewRedirect(int $productId, string $message): void
{
    echo '<script>alert(' . json_encode($message, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP)
        . '); window.location.href="index.php?act=chitietsp&idsp=' . $productId . '";</script>';
    exit;
}

function storeReviewImages(): array
{
    $files = $_FILES['images'] ?? null;
    if (!$files || !is_array($files['name'] ?? null)) {
        return [];
    }
    $directory = __DIR__ . '/../../view/images/reviews';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Không thể tạo thư mục lưu ảnh đánh giá.');
    }
    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $stored = [];
    $fileCount = min(count($files['name']), 5);
    for ($index = 0; $index < $fileCount; $index++) {
        if (($files['error'][$index] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if (($files['error'][$index] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK
            || (int) ($files['size'][$index] ?? 0) > 5 * 1024 * 1024) {
            throw new InvalidArgumentException('Mỗi ảnh đánh giá phải nhỏ hơn 5MB.');
        }
        $tmpName = (string) ($files['tmp_name'][$index] ?? '');
        $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($tmpName);
        if (!isset($allowedMimeTypes[$mimeType])) {
            throw new InvalidArgumentException('Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.');
        }
        $fileName = bin2hex(random_bytes(16)) . '.' . $allowedMimeTypes[$mimeType];
        if (!move_uploaded_file($tmpName, $directory . '/' . $fileName)) {
            throw new RuntimeException('Không thể lưu ảnh đánh giá.');
        }
        $stored[] = $fileName;
    }
    return $stored;
}

switch ($act) {
    case 'submit_product_review':
        requireCustomer();
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
        $body = trim((string) ($_POST['body'] ?? ''));
        if (!$productId || !$rating || $rating < 1 || $rating > 5 || $body === '' || mb_strlen($body) > 3000) {
            reviewRedirect((int) $productId, 'Vui lòng chọn điểm và nhập nội dung đánh giá tối đa 3000 ký tự.');
        }
        try {
            $controller = new controller();
            $reviewProduct = new sanpham();
            $reviewProduct->setId($productId);
            if (!$controller->getOneProduct($reviewProduct)) {
                reviewRedirect($productId, 'Không tìm thấy sản phẩm.');
            }
            $imagePaths = storeReviewImages();
            $controller->createProductReview($productId, customerId(), $rating, $body, $imagePaths);
            header('Location: index.php?act=chitietsp&idsp=' . $productId);
            exit;
        } catch (Throwable $exception) {
            reviewRedirect($productId, $exception->getMessage());
        }

    case 'comment_product_review':
        if (!isCustomer() && !isManager()) {
            header('Location: index.php?act=login');
            exit;
        }
        $reviewId = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $body = trim((string) ($_POST['body'] ?? ''));
        $isManagerAccount = isManager();
        if (!$reviewId || !$productId || $body === '' || mb_strlen($body) > 2000) {
            if ($isManagerAccount) {
                header('Location: index.php?act=review_management');
                exit;
            }
            reviewRedirect((int) $productId, 'Vui lòng nhập bình luận hợp lệ, tối đa 2000 ký tự.');
        }
        $authorName = $isManagerAccount
            ? 'Quản lý TechZone'
            : (string) ($_SESSION['customer_name'] ?? 'Khách hàng');
        try {
            (new controller())->commentProductReview(
                $reviewId,
                $isManagerAccount ? null : customerId(),
                $isManagerAccount ? 'manager' : 'customer',
                $authorName,
                $body,
                $isManagerAccount
            );
            header('Location: ' . ($isManagerAccount
                ? 'index.php?act=review_management'
                : 'index.php?act=chitietsp&idsp=' . $productId));
            exit;
        } catch (Throwable $exception) {
            if ($isManagerAccount) {
                echo '<script>alert(' . json_encode($exception->getMessage(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP)
                    . '); window.location.href="index.php?act=review_management";</script>';
                exit;
            }
            reviewRedirect($productId, $exception->getMessage());
        }

    case 'review_management':
        requireManager();
        $reviews = (new controller())->getReviewsForManagement();
        include __DIR__ . "/../../view/reviews_admin.php";
        break;

    case 'update_review_status':
        requireManager();
        $reviewId = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
        $status = (string) ($_POST['status'] ?? '');
        if (!$reviewId) {
            header('Location: index.php?act=review_management');
            exit;
        }
        try {
            (new controller())->updateProductReviewStatus($reviewId, $status);
        } catch (Throwable $exception) {
            echo '<script>alert(' . json_encode($exception->getMessage(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP)
                . '); window.location.href="index.php?act=review_management";</script>';
            exit;
        }
        header('Location: index.php?act=review_management');
        exit;

    case 'delete_product_review':
        requireManager();
        $reviewId = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
        if ($reviewId) {
            (new controller())->deleteProductReview($reviewId);
        }
        header('Location: index.php?act=review_management');
        exit;
}
