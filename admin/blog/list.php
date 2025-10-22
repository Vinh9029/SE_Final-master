<?php
include_once __DIR__ . '/../../database/db_connection.php';
include_once __DIR__ . '/../../config.php';

// Lấy tất cả bài blog và thông tin người đăng
$sql = "SELECT b.*, u.full_name 
        FROM blogs b 
        JOIN users u ON b.user_id = u.user_id 
        ORDER BY b.created_at DESC";
$result = $conn->query($sql);
$blogs = $result->fetch_all(MYSQLI_ASSOC);

$status_map = [
    'pending' => ['text' => 'Chờ duyệt', 'color' => 'yellow'],
    'approved' => ['text' => 'Đã duyệt', 'color' => 'green'],
    'rejected' => ['text' => 'Bị từ chối', 'color' => 'red'],
];
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <i class="fa fa-blog text-indigo-500"></i> Quản lý Blog
        </h1>
    </div>

    <?php if (isset($_SESSION['admin_blog_message'])) : ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline"><?php echo $_SESSION['admin_blog_message']; ?></span>
        </div>
        <?php unset($_SESSION['admin_blog_message']); ?>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr class="bg-indigo-100 text-indigo-700">
                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Tiêu đề</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Tác giả</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">Ngày tạo</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 text-center text-xs font-semibold uppercase tracking-wider">Trạng thái</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 text-center text-xs font-semibold uppercase tracking-wider">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($blogs)) : ?>
                    <tr>
                        <td colspan="5" class="text-center py-10 text-gray-500">Chưa có bài viết nào.</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($blogs as $blog) :
                        $status_info = $status_map[$blog['status']];
                    ?>
                        <tr class="hover:bg-indigo-50 transition">
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap font-semibold"><?php echo htmlspecialchars($blog['title']); ?></p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap"><?php echo htmlspecialchars($blog['full_name']); ?></p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap"><?php echo date('d/m/Y H:i', strtotime($blog['created_at'])); ?></p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                <span class="relative inline-block px-3 py-1 font-semibold text-<?php echo $status_info['color']; ?>-900 leading-tight">
                                    <span aria-hidden class="absolute inset-0 bg-<?php echo $status_info['color']; ?>-200 opacity-50 rounded-full"></span>
                                    <span class="relative"><?php echo $status_info['text']; ?></span>
                                </span>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                <?php if ($blog['status'] == 'pending') : ?>
                                    <a href="#" data-page="blog/approve.php?id=<?php echo $blog['blog_id']; ?>" class="text-green-600 hover:text-green-900 mr-3" title="Duyệt"><i class="fas fa-check-circle fa-lg"></i></a>
                                    <a href="#" data-page="blog/reject.php?id=<?php echo $blog['blog_id']; ?>" class="text-gray-600 hover:text-gray-900 mr-3" title="Từ chối"><i class="fas fa-times-circle fa-lg"></i></a>
                                <?php endif; ?>
                                
                                <a href="<?php echo $base_url; ?>/pages/blogs/detail.php?slug=<?php echo $blog['slug']; ?>" target="_blank" class="text-blue-600 hover:text-blue-900 mr-3" title="Xem bài viết"><i class="fas fa-eye fa-lg"></i></a>
                                
                                <!-- Admin có thể sửa và xóa bất kỳ lúc nào -->
                                <a href="#" data-page="blog/edit.php?id=<?php echo $blog['blog_id']; ?>" class="text-yellow-600 hover:text-yellow-900 mr-3" title="Sửa"><i class="fas fa-edit fa-lg"></i></a>
                                <a href="#" data-page="blog/delete.php?id=<?php echo $blog['blog_id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn bài viết này?');" title="Xóa"><i class="fas fa-trash fa-lg"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Script AJAX đã có sẵn trong dashboard.php sẽ tự động xử lý các link data-page
    // Chúng ta chỉ cần đảm bảo các file approve, reject, edit, delete của admin được tạo đúng
</script>