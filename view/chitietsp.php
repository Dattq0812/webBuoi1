<?php
$product = $product ?? [];
$imageName = basename((string) ($product['image'] ?? ''));
$imageFile = __DIR__ . '/images/' . $imageName;
$originalPrice = (float) ($product['Price'] ?? 0);
$salePercent = max(0, min(100, (float) ($product['Sale'] ?? 0)));
$salePrice = $originalPrice * (1 - $salePercent / 100);
$productReviewSummary = $productReviewSummary ?? ['review_count' => 0, 'average_rating' => 0];
$productReviews = $productReviews ?? [];
?>

<div class="container">
    <div class="page-heading">
        <div>
            <h1>Chi tiết sản phẩm</h1>
            <p class="subtitle">Thông tin sản phẩm tại TechZone.</p>
        </div>
        <a class="btn" href="index.php?act=home">Quay lại trang chủ</a>
    </div>

    <div class="card" style="display:grid; grid-template-columns:minmax(250px,380px) 1fr; gap:34px; align-items:start;">
        <div class="product-image" style="border-radius:12px;">
            <?php if ($imageName !== '' && is_file($imageFile)): ?>
                <img src="view/images/<?php echo rawurlencode($imageName); ?>"
                     alt="<?php echo htmlspecialchars($product['Name'], ENT_QUOTES, 'UTF-8'); ?>">
            <?php else: ?>
                <span>Chưa có ảnh</span>
            <?php endif; ?>
        </div>
        <div>
            <p class="subtitle">Mã sản phẩm: #<?php echo (int) $product['id_sp']; ?></p>
            <h2><?php echo htmlspecialchars($product['Name'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <div class="detail-price">
                <?php if ($salePercent > 0): ?>
                    <span class="detail-price-old"><?php echo number_format($originalPrice, 0, ',', '.'); ?>đ</span>
                    <strong class="detail-price-new"><?php echo number_format($salePrice, 0, ',', '.'); ?>đ</strong>
                <?php else: ?>
                    <strong class="detail-price-new"><?php echo number_format($originalPrice, 0, ',', '.'); ?>đ</strong>
                <?php endif; ?>
            </div>
            <p><strong>Số lượng:</strong> <?php echo htmlspecialchars($product['Mount'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>Giảm giá:</strong> <?php echo number_format($salePercent, 0); ?>%</p>
            <h3>Mô tả sản phẩm</h3>
            <p class="subtitle"><?php echo nl2br(htmlspecialchars($product['Decribe'], ENT_QUOTES, 'UTF-8')); ?></p>
            <form method="post" action="index.php?act=addcart" style="display:flex; gap:10px; align-items:center; margin-top:22px;">
                <input type="hidden" name="id_sp" value="<?php echo (int) $product['id_sp']; ?>">
                <input type="number" name="quantity" min="1" max="<?php echo (int) $product['Mount']; ?>" value="1"
                       style="width:90px;" <?php echo (int) $product['Mount'] < 1 ? 'disabled' : ''; ?>>
                <button type="submit" <?php echo (int) $product['Mount'] < 1 ? 'disabled' : ''; ?>>
                    <?php echo (int) $product['Mount'] < 1 ? 'Hết hàng' : 'Thêm vào giỏ hàng'; ?>
                </button>
            </form>
        </div>
    </div>

    <section class="card product-reviews-section">
            <div class="page-heading" style="margin-bottom:18px;">
                <div>
                    <h2>Đánh giá sản phẩm</h2>
                    <p class="subtitle">Chia sẻ trải nghiệm và tham khảo ý kiến từ khách hàng khác.</p>
                </div>
                <div class="review-summary">
                    <strong><?php echo number_format((float) $productReviewSummary['average_rating'], 1, ',', '.'); ?>/5</strong>
                    <span class="stars" aria-label="Điểm trung bình">
                        <?php echo str_repeat('★', (int) round((float) $productReviewSummary['average_rating'])); ?>
                    </span>
                    <small><?php echo (int) $productReviewSummary['review_count']; ?> bài đánh giá</small>
                </div>
            </div>

            <?php if (isCustomer()): ?>
                <form method="post" action="index.php?act=submit_product_review" enctype="multipart/form-data" class="review-form">
                    <input type="hidden" name="product_id" value="<?php echo (int) $product['id_sp']; ?>">
                    <label for="review-rating">Điểm đánh giá</label>
                    <select id="review-rating" name="rating" required>
                        <option value="">Chọn số sao</option>
                        <?php for ($star = 5; $star >= 1; $star--): ?>
                            <option value="<?php echo $star; ?>"><?php echo $star; ?> sao</option>
                        <?php endfor; ?>
                    </select>
                    <label for="review-body">Nội dung đánh giá</label>
                    <textarea id="review-body" name="body" rows="4" maxlength="3000" required placeholder="Chia sẻ chất lượng sản phẩm và trải nghiệm của bạn"></textarea>
                    <label for="review-images">Ảnh sản phẩm (tối đa 5 ảnh, mỗi ảnh 5MB)</label>
                    <input id="review-images" type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple>
                    <button type="submit">Đăng đánh giá</button>
                </form>
            <?php elseif (!isManager()): ?>
                <p class="empty">Vui lòng <a href="index.php?act=login">đăng nhập</a> để đăng đánh giá.</p>
            <?php endif; ?>

            <?php if (!$productReviews): ?>
                <div class="empty review-empty">Sản phẩm chưa có bài đánh giá nào.</div>
            <?php else: ?>
                <div class="review-list">
                    <?php foreach ($productReviews as $review): ?>
                        <article class="review-item">
                            <div class="review-item-heading">
                                <strong><?php echo htmlspecialchars($review['customer_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                <span class="stars"><?php echo str_repeat('★', (int) $review['rating']); ?></span>
                                <small class="subtitle"><?php echo htmlspecialchars($review['created_at'], ENT_QUOTES, 'UTF-8'); ?></small>
                            </div>
                            <p><?php echo nl2br(htmlspecialchars($review['body'], ENT_QUOTES, 'UTF-8')); ?></p>
                            <?php if (!empty($review['images'])): ?>
                                <div class="review-images">
                                    <?php foreach ($review['images'] as $reviewImage): ?>
                                        <img src="view/images/reviews/<?php echo rawurlencode(basename($reviewImage)); ?>" alt="Ảnh do khách hàng đăng">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php foreach ($review['comments'] as $comment): ?>
                                <div class="review-comment <?php echo $comment['author_type'] === 'manager' ? 'review-comment-manager' : ''; ?>">
                                    <strong><?php echo htmlspecialchars($comment['author_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <small><?php echo htmlspecialchars($comment['created_at'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <div><?php echo nl2br(htmlspecialchars($comment['body'], ENT_QUOTES, 'UTF-8')); ?></div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (isCustomer() || isManager()): ?>
                                <form method="post" action="index.php?act=comment_product_review" class="review-comment-form">
                                    <input type="hidden" name="review_id" value="<?php echo (int) $review['id']; ?>">
                                    <input type="hidden" name="product_id" value="<?php echo (int) $product['id_sp']; ?>">
                                    <input type="text" name="body" maxlength="2000" required placeholder="Viết bình luận...">
                                    <button type="submit">Bình luận</button>
                                </form>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
    </section>
</div>

<style>
.detail-price { display:flex; flex-direction:column; gap:2px; margin:10px 0 16px; }
.detail-price-old { color:#94a3b8; font-size:17px; text-decoration:line-through; }
.detail-price-new { color:var(--primary); font-size:27px; font-weight:800; }
.product-reviews-section { margin-top:20px; }
.review-summary { display:flex; align-items:center; gap:9px; color:var(--muted); }
.review-summary strong { color:var(--primary); font-size:24px; }
.stars { color:#f59e0b; letter-spacing:1px; }
.review-form { display:grid; gap:8px; max-width:760px; padding:16px; margin-bottom:22px; background:#f8fafc; border:1px solid var(--border); border-radius:10px; }
.review-list { display:grid; gap:14px; }
.review-item { padding:18px; border:1px solid var(--border); border-radius:12px; }
.review-item-heading { display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:8px; }
.review-item-heading small { margin-left:auto; }
.review-images { display:flex; gap:10px; flex-wrap:wrap; margin:12px 0; }
.review-images img { width:92px; height:92px; object-fit:cover; border-radius:8px; border:1px solid var(--border); }
.review-comment { padding:10px 12px; margin-top:12px; background:#f8fafc; border-left:3px solid #cbd5e1; border-radius:6px; }
.review-comment-manager { background:#eff6ff; border-left-color:var(--primary); }
.review-comment small { margin-left:8px; color:var(--muted); }
.review-comment-form { display:flex; gap:8px; margin-top:12px; }
.review-comment-form input { flex:1; }
.review-empty { padding:20px; margin-top:12px; }
@media (max-width: 700px) {
    .card[style*="grid-template-columns"] { grid-template-columns: 1fr !important; }
    .review-summary { align-items:flex-start; flex-direction:column; }
    .review-item-heading small { margin-left:0; }
    .review-comment-form { align-items:stretch; flex-direction:column; }
}
</style>
