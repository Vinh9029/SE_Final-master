<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once __DIR__ . '/../config.php';
// header.php is already included in account.php which loads this file.
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

<div class="bg-purple-50 rounded-3xl shadow-xl p-8 max-w-4xl mx-auto my-8">
    <div class="font-bold text-2xl text-purple-600 mb-6 flex items-center gap-3">
        <i class="fa fa-comments text-purple-500"></i> Bình luận của tôi
    </div>

    <div class="space-y-6">
        <?php if (empty($comments)) : ?>
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-comment-slash fa-3x text-gray-300 mb-4"></i>
                <p>Bạn chưa có bình luận nào.</p>
                <a href="<?php echo $base_url; ?>/menus/menus.php" class="mt-4 inline-block text-purple-600 font-semibold hover:underline">Khám phá sản phẩm và để lại cảm nhận nhé!</a>
            </div>
        <?php else : ?>
            <?php foreach ($comments as $comment) : ?>
                <?php
                $target_name = '';
                $target_url = '#';
                $target_image = $base_url . '/Photos/placeholder.png';

                if ($comment['target_type'] === 'product' && $comment['product_name']) {
                    $target_name = $comment['product_name'];
                    $target_url = $base_url . '/menus/product.php?slug=' . generateSlug($target_name);
                    if ($comment['product_image']) $target_image = $base_url . '/' . $comment['product_image'];
                } elseif ($comment['target_type'] === 'blog' && $comment['blog_title']) {
                    $target_name = $comment['blog_title'];
                    $target_url = $base_url . '/pages/blogs/detail.php?slug=' . $comment['blog_slug'];
                    if ($comment['blog_image']) $target_image = $base_url . '/' . $comment['blog_image'];
                }
                list($status_text, $status_class) = get_comment_status_label($comment['status']);
                ?>
                <div id="my-comment-<?php echo $comment['id']; ?>" class="bg-white p-5 rounded-xl shadow-md border-l-4 border-purple-300 hover:shadow-lg transition-shadow duration-300">
                    <div class="flex gap-4">
                        <img src="<?php echo htmlspecialchars($target_image); ?>" alt="<?php echo htmlspecialchars($target_name); ?>" class="w-20 h-20 object-cover rounded-lg flex-shrink-0">
                        <div class="flex-1">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="text-xs text-gray-500">Bạn đã bình luận về:</p>
                                    <a href="<?php echo $target_url; ?>" class="font-bold text-purple-700 hover:underline"><?php echo htmlspecialchars($target_name); ?></a>
                                </div>
                                <span class="text-xs text-gray-400"><?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></span>
                            </div>
                            <p class="text-gray-800 bg-gray-50 p-3 rounded-md">"<?php echo nl2br(htmlspecialchars($comment['content'])); ?>"</p>
                            <div class="mt-3 flex items-center justify-between text-sm">
                                <div class="flex items-center gap-4 text-gray-500">
                                    <span><i class="fas fa-thumbs-up text-purple-400"></i> <?php echo $comment['likes']; ?> Lượt thích</span>
                                    <button onclick="deleteMyComment(<?php echo $comment['id']; ?>)" class="text-red-500 hover:text-red-700 hover:underline font-semibold flex items-center gap-1">
                                        <i class="fas fa-trash-alt"></i> Xóa
                                    </button>
                                </div>
                                <span class="<?php echo $status_class; ?> px-2 py-1 rounded-full font-bold text-xs"><?php echo $status_text; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
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
                    document.getElementById(`my-comment-${commentId}`).remove();
                } else {
                    showAccountToast(data.message || 'Có lỗi xảy ra.', true);
                }
            })
            .catch(() => showAccountToast('Lỗi kết nối.', true));
    }
</script>