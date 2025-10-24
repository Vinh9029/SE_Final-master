<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../../database/db_connection.php';

// Fetch all blog posts with user's full name
$stmt = $conn->prepare(
    "SELECT b.blog_id, b.title, b.status, b.created_at, u.full_name 
     FROM blogs b 
     JOIN users u ON b.user_id = u.user_id 
     ORDER BY b.created_at DESC"
);
$stmt->execute();
$blogs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Function to get status label
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
    <div class="font-bold text-2xl text-gray-800 mb-6 flex items-center gap-2">
        <i class="fa fa-newspaper text-blue-500"></i> Quản lý bài viết Blog
    </div>

    <?php if (isset($_SESSION['admin_blog_message'])): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p><?php echo $_SESSION['admin_blog_message']; ?></p>
        </div>
        <?php unset($_SESSION['admin_blog_message']); ?>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-blue-50 text-blue-700">
                    <th class="px-4 py-2 rounded-tl-xl">Tiêu đề</th>
                    <th class="px-4 py-2">Tác giả</th>
                    <th class="px-4 py-2">Ngày tạo</th>
                    <th class="px-4 py-2">Trạng thái</th>
                    <th class="px-4 py-2 rounded-tr-xl">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($blogs)): ?>
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Chưa có bài viết nào.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($blogs as $blog): ?>
                        <tr class="hover:bg-blue-50">
                            <td class="px-4 py-2 font-semibold text-gray-800"><?php echo htmlspecialchars($blog['title']); ?></td>
                            <td class="px-4 py-2 text-gray-600"><?php echo htmlspecialchars($blog['full_name']); ?></td>
                            <td class="px-4 py-2 text-gray-500"><?php echo date('d/m/Y', strtotime($blog['created_at'])); ?></td>
                            <td class="px-4 py-2">
                                <?php list($status_text, $status_class) = get_blog_status_label($blog['status']); ?>
                                <span class="<?php echo $status_class; ?> px-2 py-1 rounded-full text-xs font-bold"><?php echo $status_text; ?></span>
                            </td>
                            <td class="px-4 py-2 flex gap-2">
                                <?php if ($blog['status'] === 'pending'): ?>
                                    <a href="blog/approve.php?id=<?php echo $blog['blog_id']; ?>" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-lg text-xs font-bold shadow transition">Duyệt</a>
                                    <a href="blog/reject.php?id=<?php echo $blog['blog_id']; ?>" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg text-xs font-bold shadow transition">Từ chối</a>
                                <?php endif; ?>
                                <a href="#" data-page="blog/detail.php?id=<?php echo $blog['blog_id']; ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-lg text-xs font-bold shadow transition">Xem</a>
                                <a href="#" data-page="blog/edit.php?id=<?php echo $blog['blog_id']; ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-lg text-xs font-bold shadow transition">Sửa</a>
                                <a href="blog/delete.php?id=<?php echo $blog['blog_id']; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn bài viết này?');" class="bg-gray-700 hover:bg-gray-800 text-white px-3 py-1 rounded-lg text-xs font-bold shadow transition">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>