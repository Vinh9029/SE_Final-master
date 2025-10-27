<?php
session_start();
include_once __DIR__ . '/../database/db_connection.php';
include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../menus/helper.php'; // Để sử dụng generateSlug

if (!isset($_SESSION['user_id'])) {
    // Trang này được load bằng AJAX, nên chỉ trả về lỗi thay vì redirect
    echo '<div class="text-red-500 p-8">Vui lòng đăng nhập để xem bình luận.</div>';
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch comments by the user
$sql = "
    SELECT 
        c.id, c.content, c.created_at, c.status, c.target_type, c.target_id,
        p.name AS product_name,
        b.title AS blog_title, b.slug AS blog_slug
    FROM comments c
    LEFT JOIN products p ON c.target_type = 'product' AND c.target_id = p.product_id
    LEFT JOIN blogs b ON c.target_type = 'blog' AND c.target_id = b.blog_id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$comments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

function get_status_badge($status)
{
    switch ($status) {
        case 'approved':
            return '<span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Đã duyệt</span>';
        case 'pending':
            return '<span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Chờ duyệt</span>';
        case 'rejected':
            return '<span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Bị từ chối</span>';
        default:
            return '<span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Không rõ</span>';
    }
}

function get_target_info($comment, $base_url)
{
    if ($comment['target_type'] === 'product' && $comment['product_name']) {
        $slug = generateSlug($comment['product_name']);
        $url = "{$base_url}/menus/product.php?slug={$slug}" . ($comment['parent_id'] ? '#comment-wrapper-' . $comment['parent_id'] : '');
        return [
            'url' => $url,
            'text' => "về sản phẩm: <a href='{$url}' target='_blank' class='font-bold text-pink-600 hover:underline'>" . htmlspecialchars($comment['product_name']) . "</a>",
            'icon' => 'fa-coffee text-orange-500'
        ];
    } elseif ($comment['target_type'] === 'blog' && $comment['blog_title']) {
        $url = "{$base_url}/pages/blogs/detail.php?slug={$comment['blog_slug']}" . ($comment['parent_id'] ? '#comment-wrapper-' . $comment['parent_id'] : '');
        return [
            'url' => $url,
            'text' => "về bài viết: <a href='{$url}' target='_blank' class='font-bold text-purple-600 hover:underline'>" . htmlspecialchars($comment['blog_title']) . "</a>",
            'icon' => 'fa-blog text-indigo-500'
        ];
    }
    return [
        'url' => '#',
        'text' => 'về một nội dung không xác định',
        'icon' => 'fa-question-circle text-gray-500'
    ];
}
?>

<div class="bg-purple-50 rounded-3xl shadow-xl p-8">
    <div class="font-bold text-2xl text-purple-600 mb-6 flex items-center gap-2">
        <i class="fa fa-comments text-purple-500"></i> Bình luận của tôi
    </div>

    <?php if (empty($comments)) : ?>
        <div class="text-center py-16 text-gray-500">
            <i class="fa fa-comment-slash fa-3x text-gray-300 mb-4"></i>
            <p class="font-semibold">Bạn chưa có bình luận nào.</p>
            <p class="text-sm">Hãy chia sẻ cảm nhận của bạn về sản phẩm và bài viết của chúng tôi nhé!</p>
        </div>
    <?php else : ?>
        <div class="space-y-6">
            <?php foreach ($comments as $comment) : ?>
                <?php $target_info = get_target_info($comment, $base_url); ?>
                <div id="comment-<?php echo $comment['id']; ?>" class="bg-white rounded-2xl shadow-md transition-all duration-300 hover:shadow-lg hover:ring-2 hover:ring-purple-300">
                    <div class="p-5">
                        <!-- Comment Header -->
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <i class="fas <?php echo $target_info['icon']; ?>"></i>
                                <span onclick="event.stopPropagation();">
                                    <?php echo $target_info['text']; ?>
                                </span>
                            </div>
                            <?php echo get_status_badge($comment['status']); ?>
                        </div>

                        <!-- Comment Body -->
                        <a href="<?php echo $target_info['url']; ?>" target="_blank" class="block my-2">
                            <p class="text-gray-800 leading-relaxed text-base italic">"<?php echo htmlspecialchars($comment['content']); ?>"</p>
                        </a>
                    </div>
                    <!-- Comment Footer -->
                    <div class="bg-gray-50 px-5 py-3 rounded-b-2xl flex justify-between items-center">
                        <div class="text-xs text-gray-500 flex items-center gap-1">
                            <i class="fa fa-calendar-alt"></i>
                            <span>Đã đăng vào <?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></span>
                        </div>
                        <button onclick="deleteComment(<?php echo $comment['id']; ?>)" class="text-gray-400 hover:text-red-500 transition-colors duration-200 text-sm flex items-center gap-1" title="Xóa bình luận">
                            <i class="fa fa-trash-alt"></i> <span>Xóa</span>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    function showConfirm(message, onConfirm) {
        // Using the existing confirmation modal from notifications.php
        // Ensure this function is globally available or defined in account.php
        let modal = document.getElementById('confirm-modal');
        if (!modal) {
            // Fallback if modal doesn't exist
            if (confirm(message)) {
                onConfirm();
            }
            return;
        }
        document.getElementById('confirm-modal-message').textContent = message;
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.remove('opacity-0', 'scale-95'), 10);

        // Re-bind events to avoid multiple triggers
        const confirmBtn = document.getElementById('confirm-modal-confirm');
        const cancelBtn = document.getElementById('confirm-modal-cancel');
        const closeModal = () => {
            modal.style.display = 'none';
        };

        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        newConfirmBtn.addEventListener('click', () => {
            onConfirm();
            closeModal();
        });

        const newCancelBtn = cancelBtn.cloneNode(true);
        cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
        newCancelBtn.addEventListener('click', closeModal);
    }

    function deleteComment(commentId) {
        showConfirm('Bạn có chắc chắn muốn xóa bình luận này không? Hành động này không thể hoàn tác.', () => {
            fetch('comment/delete.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `comment_id=${commentId}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const commentEl = document.getElementById(`comment-${commentId}`);
                        commentEl.style.transform = 'scale(0.95)';
                        commentEl.style.opacity = '0';
                        setTimeout(() => commentEl.remove(), 300);
                        // You can add a toast message here for better UX
                    } else {
                        alert(data.message || 'Không thể xóa bình luận.');
                    }
                })
                .catch(err => alert('Đã xảy ra lỗi. Vui lòng thử lại.'));
        });
    }
</script>