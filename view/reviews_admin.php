<?php
$reviews = $reviews ?? [];
$statusLabels = ['visible' => 'Đang hiển thị', 'hidden' => 'Đã ẩn'];
?>
<div class="container">
    <div class="page-heading">
        <div>
            <h1>Quản lý đánh giá sản phẩm</h1>
            <p class="subtitle">Xem, bình luận, ẩn hoặc xoá nội dung đánh giá.</p>
        </div>
    </div>
    <?php if (!$reviews): ?>
        <div class="card empty">Chưa có bài đánh giá nào.</div>
    <?php else: ?>
        <div style="display:grid; gap:16px;">
            <?php foreach ($reviews as $review): ?>
                <article class="card">
                    <div class="page-heading" style="margin-bottom:10px;">
                        <div>
                            <h3 style="margin-bottom:4px;"><?php echo htmlspecialchars($review['product_name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <strong><?php echo htmlspecialchars($review['customer_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <span class="stars" style="margin-left:10px;"><?php echo str_repeat('★', (int) $review['rating']); ?></span>
                            <small class="subtitle" style="margin-left:10px;"><?php echo htmlspecialchars($review['created_at'], ENT_QUOTES, 'UTF-8'); ?></small>
                        </div>
                        <strong><?php echo htmlspecialchars($statusLabels[$review['status']] ?? $review['status'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    </div>
                    <p><?php echo nl2br(htmlspecialchars($review['body'], ENT_QUOTES, 'UTF-8')); ?></p>
                    <?php if (!empty($review['images'])): ?>
                        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:12px;">
                            <?php foreach ($review['images'] as $image): ?>
                                <img src="view/images/reviews/<?php echo rawurlencode(basename($image)); ?>" alt="Ảnh đánh giá" width="92" height="92" style="object-fit:cover; border-radius:8px;">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php foreach ($review['comments'] as $comment): ?>
                        <div style="padding:10px 12px; margin-top:8px; background:#f8fafc; border-left:3px solid <?php echo $comment['author_type'] === 'manager' ? 'var(--primary)' : '#cbd5e1'; ?>;">
                            <strong><?php echo htmlspecialchars($comment['author_name'], ENT_QUOTES, 'UTF-8'); ?></strong>:
                            <?php echo nl2br(htmlspecialchars($comment['body'], ENT_QUOTES, 'UTF-8')); ?>
                        </div>
                    <?php endforeach; ?>
                    <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:14px;">
                        <form method="post" action="index.php?act=comment_product_review" style="display:flex; flex:1 1 360px; gap:8px;">
                            <input type="hidden" name="review_id" value="<?php echo (int) $review['id']; ?>">
                            <input type="hidden" name="product_id" value="<?php echo (int) $review['product_id']; ?>">
                            <input type="text" name="body" maxlength="2000" required placeholder="Trả lời đánh giá...">
                            <button type="submit">Bình luận</button>
                        </form>
                        <form method="post" action="index.php?act=update_review_status">
                            <input type="hidden" name="review_id" value="<?php echo (int) $review['id']; ?>">
                            <input type="hidden" name="status" value="<?php echo $review['status'] === 'visible' ? 'hidden' : 'visible'; ?>">
                            <button type="submit" class="btn-save"><?php echo $review['status'] === 'visible' ? 'Ẩn bài' : 'Hiện bài'; ?></button>
                        </form>
                        <form method="post" action="index.php?act=delete_product_review" onsubmit="return confirm('Xoá vĩnh viễn bài đánh giá này?');">
                            <input type="hidden" name="review_id" value="<?php echo (int) $review['id']; ?>">
                            <button type="submit" class="btn-delete">Xoá</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
