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

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài viết của tôi - Old Favour Coffee</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50">

<main class="container mx-auto px-4 py-12">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-bold text-yellow-800">Bài viết của tôi</h1>
        <br>
        <br>
        <br>
        <br>
        <br>
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
                                <a href="edit.php?id=<?php echo $blog['blog_id']; ?>" class="text-yellow-600 hover:text-yellow-900 mr-3" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button data-blog-id="<?php echo $blog['blog_id']; ?>" data-blog-title="<?php echo htmlspecialchars($blog['title']); ?>" class="delete-btn text-red-600 hover:text-red-900" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-2xl shadow-2xl p-8 text-center max-w-md w-full transform transition-all">
        <div class="text-red-500 mb-4">
            <i class="fas fa-exclamation-triangle fa-4x"></i>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Xác nhận xóa bài viết</h1>
        <p class="text-gray-600 mb-6">
            Bạn có chắc chắn muốn xóa vĩnh viễn bài viết:
            <br>
            <strong id="blogTitleToDelete" class="text-red-600"></strong>?
            <br>
            Hành động này không thể hoàn tác.
        </p>
        <form id="deleteForm" class="flex justify-center gap-4">
            <input type="hidden" name="blog_id" id="blogIdToDelete">
            <button type="button" id="cancelDelete" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-8 py-2 rounded-lg font-semibold shadow-md transition">
                Hủy
            </button>
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-8 py-2 rounded-lg font-semibold shadow-md transition">
                Xác nhận Xóa
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteModal = document.getElementById('deleteModal');
    const cancelDeleteBtn = document.getElementById('cancelDelete');
    const deleteForm = document.getElementById('deleteForm');
    const blogTitleEl = document.getElementById('blogTitleToDelete');
    const blogIdInput = document.getElementById('blogIdToDelete');

    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function () {
            const blogId = this.dataset.blogId;
            const blogTitle = this.dataset.blogTitle;

            blogTitleEl.textContent = `"${blogTitle}"`;
            blogIdInput.value = blogId;
            deleteModal.classList.remove('hidden');
        });
    });

    cancelDeleteBtn.addEventListener('click', () => {
        deleteModal.classList.add('hidden');
    });

    deleteForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const blogId = blogIdInput.value;
        // Thay vì submit form, chúng ta sẽ dùng fetch để gửi yêu cầu
        // và xử lý kết quả mà không cần tải lại trang.
        // Tuy nhiên, để đơn giản và giữ logic hiện tại, ta sẽ submit form đến delete.php
        window.location.href = `delete.php?id=${blogId}&confirm=true`;
    });
});
</script>

</body>
</html>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>