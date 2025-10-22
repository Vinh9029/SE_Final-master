    <?php
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../database/db_connection.php';

// Chỉ cho phép người dùng đã đăng nhập truy cập
if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_url . "/login/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy các bài blog của người dùng
$blogs = [];
$sql = "SELECT * FROM blogs WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $blogs = $result->fetch_all(MYSQLI_ASSOC);
}

$status_map = [
    'pending' => ['text' => 'Chờ duyệt', 'color' => 'yellow'],
    'approved' => ['text' => 'Đã duyệt', 'color' => 'green'],
    'rejected' => ['text' => 'Bị từ chối', 'color' => 'red'],
];
?>

<main class="container mx-auto px-4 py-12">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-bold text-yellow-800">Bài viết của tôi</h1>
        <a href="create.php" class="bg-yellow-800 text-white font-bold py-2 px-4 rounded-lg shadow-lg hover:bg-yellow-900 transition-colors">
            <i class="fas fa-plus mr-2"></i>Viết bài mới
        </a>
    </div>

    <?php if (isset($_SESSION['success_message'])) : ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline"><?php echo $_SESSION['success_message']; ?></span>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tiêu đề</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Ngày tạo</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Trạng thái</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($blogs)) : ?>
                    <tr>
                        <td colspan="4" class="text-center py-10 text-gray-500">Bạn chưa có bài viết nào.</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($blogs as $blog) :
                        $status_info = $status_map[$blog['status']];
                    ?>
                        <tr>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap"><?php echo htmlspecialchars($blog['title']); ?></p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap"><?php echo date('d/m/Y', strtotime($blog['created_at'])); ?></p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <span class="relative inline-block px-3 py-1 font-semibold text-<?php echo $status_info['color']; ?>-900 leading-tight">
                                    <span aria-hidden class="absolute inset-0 bg-<?php echo $status_info['color']; ?>-200 opacity-50 rounded-full"></span>
                                    <span class="relative"><?php echo $status_info['text']; ?></span>
                                </span>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                <?php if ($blog['status'] == 'approved') : ?>
                                    <a href="<?php echo $base_url; ?>/pages/blogs/detail.php?slug=<?php echo $blog['slug']; ?>" class="text-blue-600 hover:text-blue-900 mr-3" title="Xem"><i class="fas fa-eye"></i></a>
                                <?php endif; ?>
                                <a href="edit.php?id=<?php echo $blog['blog_id']; ?>" class="text-yellow-600 hover:text-yellow-900 mr-3" title="Sửa"><i class="fas fa-edit"></i></a>
                                <a href="delete.php?id=<?php echo $blog['blog_id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này?');" title="Xóa"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>