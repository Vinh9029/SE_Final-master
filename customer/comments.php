<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../database/db_connection.php';
include_once __DIR__ . '/../menus/helper.php'; // For generateSlug

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_url . "/login/index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Function to get status label
function get_comment_status_label($status)
{
    switch ($status) {
        case 'approved':
            return ['Đã duyệt', 'bg-green-100 text-green-700'];
        case 'pending':
            return ['Chờ duyệt', 'bg-yellow-100 text-yellow-700'];
        case 'rejected':
            return ['Bị từ chối', 'bg-red-100 text-red-700'];
        default:
            return ['Không xác định', 'bg-gray-100 text-gray-700'];
    }
}

// Fetch all comments from the user with context (product or blog)
$sql = "
    SELECT 
        c.id, c.content, c.created_at, c.status, c.target_type, c.likes,
        p.name AS product_name, p.image AS product_image,
        b.title AS blog_title, b.cover_image AS blog_image, b.slug AS blog_slug
    FROM comments c
    LEFT JOIN products p ON c.target_type = 'product' AND c.target_id = p.product_id
    LEFT JOIN blogs b ON c.target_type = 'blog' AND c.target_id = b.blog_id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$comments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<div class="bg-purple-50 rounded-3xl shadow-xl p-8 h-full flex flex-col">
    <div class="font-bold text-2xl text-purple-600 mb-6 flex items-center gap-3">
        <i class="fa fa-comments text-purple-500"></i> Bình luận của tôi
    </div>

    <div class="flex-grow overflow-x-auto">
        <?php if (empty($comments)) : ?>
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-comment-slash fa-3x text-gray-300 mb-4"></i>
                <p>Bạn chưa có bình luận nào.</p>
                <a href="<?php echo $base_url; ?>/menus/menus.php" class="mt-4 inline-block text-purple-600 font-semibold hover:underline">Khám phá sản phẩm và để lại cảm nhận nhé!</a>
            </div>
        <?php else : ?>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-purple-100 text-purple-800">
                        <th class="px-4 py-3 rounded-tl-xl">Nội dung</th>
                        <th class="px-4 py-3">Đối tượng</th>
                        <th class="px-4 py-3">Ngày gửi</th>
                        <th class="px-4 py-3">Trạng thái</th>
                        <th class="px-4 py-3 rounded-tr-xl">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <?php foreach ($comments as $comment) : ?>
                        <?php
                        $target_name = '';
                        $target_url = '#';

                        if ($comment['target_type'] === 'product' && $comment['product_name']) {
                            $target_name = $comment['product_name'];
                            $target_url = $base_url . '/menus/product.php?slug=' . generateSlug($target_name);
                        } elseif ($comment['target_type'] === 'blog' && $comment['blog_title']) {
                            $target_name = $comment['blog_title'];
                            $target_url = $base_url . '/pages/blogs/detail.php?slug=' . $comment['blog_slug'];
                        }
                        list($status_text, $status_class) = get_comment_status_label($comment['status']);
                        ?>
                        <tr id="my-comment-<?php echo $comment['id']; ?>" class="hover:bg-purple-50 border-b border-purple-100 last:border-b-0">
                            <td class="px-4 py-3 text-gray-700 max-w-sm">
                                <p class="truncate" title="<?php echo htmlspecialchars($comment['content']); ?>"><?php echo htmlspecialchars($comment['content']); ?></p>
                                <div class="text-xs text-gray-400 mt-1">
                                    <i class="fas fa-thumbs-up text-purple-400"></i> <?php echo $comment['likes']; ?>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <a href="<?php echo $target_url; ?>" target="_blank" class="text-purple-700 hover:underline font-semibold text-sm truncate block" title="<?php echo htmlspecialchars($target_name); ?>">
                                    <?php echo htmlspecialchars($target_name); ?>
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500"><?php echo date('d/m/Y', strtotime($comment['created_at'])); ?></td>
                            <td class="px-4 py-3">
                                <span class="<?php echo $status_class; ?> px-2 py-1 rounded-full font-bold text-xs"><?php echo $status_text; ?></span>
                            </td>
                            <td class="px-4 py-3">
                                <button onclick="deleteMyComment(<?php echo $comment['id']; ?>)" class="text-red-500 hover:text-red-700 font-semibold flex items-center gap-1 text-sm" title="Xóa bình luận">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
    // A self-contained toast function
    function showAccountToast(message, isError = false) {
        let toast = document.getElementById('account-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'account-toast';
            toast.className = 'fixed top-24 right-5 text-white py-3 px-6 rounded-xl shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out z-50';
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<i class="fa ${isError ? 'fa-times-circle' : 'fa-check-circle'} mr-2"></i> ${message}`;
        toast.style.backgroundColor = isError ? '#ef4444' : '#22c55e'; // red-500 or green-500
        
        toast.classList.remove('translate-x-full');
        setTimeout(() => toast.classList.add('translate-x-full'), 3000);
    }

    function deleteMyComment(commentId) {
        if (!confirm('Bạn có chắc chắn muốn xóa bình luận này?')) {
            return;
        }

        const formData = new FormData();
        formData.append('comment_id', commentId);

        fetch('includes/handlers/comments/deleteComment.php', { // Path relative to project root
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showAccountToast('Đã xóa bình luận thành công!', false);
                    const row = document.getElementById(`my-comment-${commentId}`);
                    if (row) {
                        row.style.transition = 'opacity 0.5s, transform 0.5s';
                        row.style.opacity = '0';
                        row.style.transform = 'scale(0.95)';
                        setTimeout(() => row.remove(), 500);
                    }
                } else {
                    showAccountToast(data.message || 'Có lỗi xảy ra.', true);
                }
            })
            .catch(() => showAccountToast('Lỗi kết nối.', true));
    }
</script>