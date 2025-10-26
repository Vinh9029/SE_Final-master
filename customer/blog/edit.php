<?php
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../database/db_connection.php';

// Chỉ cho phép người dùng đã đăng nhập truy cập
if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_url . "/login/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$blog_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$blog_id) {
    $_SESSION['error_message'] = "ID bài viết không hợp lệ.";
    header("Location: myBlogs.php");
    exit();
}

// Lấy thông tin bài viết để kiểm tra quyền và hiển thị
$stmt = $conn->prepare("SELECT * FROM blogs WHERE blog_id = ? AND user_id = ?");
$stmt->bind_param("ii", $blog_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$blog = $result->fetch_assoc();
$stmt->close();

if (!$blog) {
    $_SESSION['error_message'] = "Không tìm thấy bài viết hoặc bạn không có quyền chỉnh sửa.";
    header("Location: myBlogs.php");
    exit();
}

// Hàm tạo slug (giống create.php)
function create_slug($string)
{
    $search = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', '(', ')', '[', ']', '{', '}', ' ');
    $replace = array('A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', '-', '-', '-', '-', '-', '-', '-');
    $string = str_replace($search, $replace, $string);
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9-]/', '', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $slug = create_slug($title) . '-' . $blog_id; // Giữ blog_id để slug ổn định

    if (empty($title)) $errors[] = "Tiêu đề không được để trống.";
    if (empty($content)) $errors[] = "Nội dung không được để trống.";

    $cover_image = $blog['cover_image'];
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        $target_dir = "uploads/blogs/";
        if (!is_dir(__DIR__ . '/../../' . $target_dir)) {
            mkdir(__DIR__ . '/../../' . $target_dir, 0777, true);
        }
        $target_file = $target_dir . time() . '_' . basename($_FILES["cover_image"]["name"]);
        if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], __DIR__ . '/../../' . $target_file)) {
            // Xóa ảnh cũ nếu có
            if ($cover_image && file_exists(__DIR__ . '/../../' . $cover_image)) {
                unlink(__DIR__ . '/../../' . $cover_image);
            }
            $cover_image = $target_file;
        } else {
            $errors[] = "Có lỗi xảy ra khi tải ảnh mới lên.";
        }
    }

    if (empty($errors)) {
        // Khi người dùng sửa, trạng thái sẽ chuyển về 'pending' để admin duyệt lại
        $stmt = $conn->prepare("UPDATE blogs SET title = ?, content = ?, cover_image = ?, slug = ?, status = 'pending', updated_at = NOW() WHERE blog_id = ? AND user_id = ?");
        $stmt->bind_param("ssssii", $title, $content, $cover_image, $slug, $blog_id, $user_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Bài viết đã được cập nhật và đang chờ duyệt lại!";
            header("Location: myBlogs.php");
            exit();
        } else {
            $errors[] = "Lỗi khi cập nhật bài viết: " . $stmt->error;
        }
    }
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa bài viết - Old Favour Coffee</title>
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: 'textarea#content',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        });
    </script>
    <!-- <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet"> -->
    <script src="https://cdn.tailwindcss.com"></script>

</head>

<main class="container mx-auto px-4 py-12">
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold text-yellow-800 mb-6">Chỉnh sửa bài viết</h1>

        <?php if (!empty($errors)) : ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <?php foreach ($errors as $error) : ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="edit.php?id=<?php echo $blog_id; ?>" method="POST" enctype="multipart/form-data">
            <div class="mb-6">
                <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Tiêu đề:</label>
                <input type="text" id="title" name="title" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="<?php echo htmlspecialchars($blog['title']); ?>" required>
            </div>

            <div class="mb-6">
                <label for="cover_image" class="block text-gray-700 text-sm font-bold mb-2">Ảnh bìa hiện tại:</label>
                <?php if ($blog['cover_image']) : ?>
                    <img src="<?php echo $base_url . '/' . htmlspecialchars($blog['cover_image']); ?>" alt="Ảnh bìa" class="w-48 h-auto rounded mb-2">
                <?php else: ?>
                    <p class="text-gray-500 text-sm">Chưa có ảnh bìa.</p>
                <?php endif; ?>
                <label for="cover_image" class="block text-gray-700 text-sm font-bold mb-2 mt-2">Tải ảnh mới (nếu muốn thay đổi):</label>
                <input type="file" id="cover_image" name="cover_image" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" accept="image/*">
            </div>

            <div class="mb-6">
                <label for="content" class="block text-gray-700 text-sm font-bold mb-2">Nội dung:</label>
                <textarea id="content" name="content" rows="15"><?php echo htmlspecialchars($blog['content']); ?></textarea>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-yellow-800 hover:bg-yellow-900 text-white font-bold py-2 px-4 rounded transition-colors">
                    Lưu và gửi duyệt lại
                </button>
                <a href="myBlogs.php" class="font-bold text-sm text-gray-600 hover:text-gray-800">
                    Hủy
                </a>
            </div>
        </form>
    </div>
</main>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>