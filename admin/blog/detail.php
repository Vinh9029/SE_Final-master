<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../../database/db_connection.php';
include_once __DIR__ . '/../../config.php';

// Bảo mật: Chỉ admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit('Bạn không có quyền truy cập.');
}

$blog_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$blog_id) {
    echo "<div class='p-8'><p class='text-red-500'>ID bài viết không hợp lệ.</p></div>";
    exit();
}

// Lấy chi tiết bài viết và tên tác giả
$stmt = $conn->prepare(
    "SELECT b.*, u.full_name 
     FROM blogs b 
     JOIN users u ON b.user_id = u.user_id 
     WHERE b.blog_id = ?"
);
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$result = $stmt->get_result();
$blog = $result->fetch_assoc();
$stmt->close();

if (!$blog) {
    echo "<div class='p-8'><p class='text-red-500'>Không tìm thấy bài viết.</p></div>";
    exit();
}

// Hàm lấy nhãn trạng thái (tương tự list.php)
function get_blog_status_label($status) {
    switch ($status) {
        case 'pending': return ['Chờ duyệt', 'bg-yellow-100 text-yellow-700'];
        case 'approved': return ['Đã duyệt', 'bg-green-100 text-green-700'];
        case 'rejected': return ['Đã từ chối', 'bg-red-100 text-red-700'];
        default: return ['Không xác định', 'bg-gray-100 text-gray-700'];
    }
}
?>

<div class="bg-white rounded-3xl shadow-xl p-8">
    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($blog['title']); ?></h1>
            <div class="text-sm text-gray-500">
                <span>Tác giả: <strong><?php echo htmlspecialchars($blog['full_name']); ?></strong></span>
                <span class="mx-2">•</span>
                <span>Ngày tạo: <?php echo date('d/m/Y', strtotime($blog['created_at'])); ?></span>
            </div>
        </div>
        <div class="flex-shrink-0">
            <?php list($status_text, $status_class) = get_blog_status_label($blog['status']); ?>
            <span class="<?php echo $status_class; ?> px-3 py-1.5 rounded-full text-sm font-bold"><?php echo $status_text; ?></span>
        </div>
    </div>

    <?php if ($blog['cover_image']) : ?>
        <img src="<?php echo $base_url . '/' . htmlspecialchars($blog['cover_image']); ?>" alt="Ảnh bìa" class="w-full h-auto max-h-96 object-cover rounded-lg mb-8 shadow-md">
    <?php endif; ?>

    <div class="prose max-w-none">
        <?php echo $blog['content']; // Nội dung HTML được render trực tiếp ?>
    </div>

    <div class="mt-12 border-t pt-6 flex justify-between items-center">
        <a href="#" data-page="blog/list.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg transition">
            <i class="fas fa-arrow-left mr-2"></i> Quay lại danh sách
        </a>
        <div>
            <?php if ($blog['status'] === 'pending') : ?>
                <a href="blog/approve.php?id=<?php echo $blog['blog_id']; ?>" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg transition">Duyệt bài</a>
                <a href="blog/reject.php?id=<?php echo $blog['blog_id']; ?>" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition ml-2">Từ chối</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Thêm CSS cho nội dung bài viết để hiển thị đẹp hơn -->
<style>
    .prose {
        line-height: 1.7;
    }
    .prose h1, .prose h2, .prose h3 {
        font-weight: 700;
        margin-top: 1.5em;
        margin-bottom: 0.5em;
    }
    .prose img {
        border-radius: 0.5rem;
        margin: 1.5em auto;
    }
    .prose blockquote {
        border-left: 4px solid #e5e7eb;
        padding-left: 1em;
        font-style: italic;
    }
</style>