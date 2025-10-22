<?php
include_once __DIR__ . '/../../database/db_connection.php';
include_once __DIR__ . '/../../config.php';
session_start();

// Bảo mật: Chỉ admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit('Bạn không có quyền truy cập.');
}

$blog_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$blog_id) {
    header('Location: ../dashboard.php?page=blog/list.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM blogs WHERE blog_id = ?");
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$result = $stmt->get_result();
$blog = $result->fetch_assoc();

if (!$blog) {
    $_SESSION['admin_blog_message'] = "Không tìm thấy bài viết.";
    header('Location: ../dashboard.php?page=blog/list.php');
    exit();
}

function create_slug($string) {
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
    $slug = create_slug($title) . '-' . $blog_id;

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
            if ($cover_image && file_exists(__DIR__ . '/../../' . $cover_image)) {
                unlink(__DIR__ . '/../../' . $cover_image);
            }
            $cover_image = $target_file;
        } else {
            $errors[] = "Có lỗi xảy ra khi tải ảnh mới lên.";
        }
    }

    if (empty($errors)) {
        // Admin sửa không cần duyệt lại, giữ nguyên status
        $stmt = $conn->prepare("UPDATE blogs SET title = ?, content = ?, cover_image = ?, slug = ? WHERE blog_id = ?");
        $stmt->bind_param("ssssi", $title, $content, $cover_image, $slug, $blog_id);
        if ($stmt->execute()) {
            $_SESSION['admin_blog_message'] = "Admin đã cập nhật bài viết thành công!";
            header('Location: ../dashboard.php?page=blog/list.php');
            exit();
        } else {
            $errors[] = "Lỗi khi cập nhật bài viết: " . $stmt->error;
        }
    }
}
?>

<head>
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: 'textarea#content',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        });
    </script>
</head>

<div class="p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Admin Chỉnh sửa bài viết</h1>

    <?php if (!empty($errors)) : ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <?php foreach ($errors as $error) : ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form class="bg-white p-8 rounded-lg shadow-lg" action="blog/edit.php?id=<?php echo $blog_id; ?>" method="POST" enctype="multipart/form-data">
        <div class="mb-6">
            <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Tiêu đề:</label>
            <input type="text" id="title" name="title" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="<?php echo htmlspecialchars($blog['title']); ?>" required>
        </div>

        <div class="mb-6">
            <label for="cover_image" class="block text-gray-700 text-sm font-bold mb-2">Ảnh bìa:</label>
            <?php if ($blog['cover_image']) : ?>
                <img src="<?php echo $base_url . '/' . htmlspecialchars($blog['cover_image']); ?>" alt="Ảnh bìa" class="w-48 h-auto rounded mb-2">
            <?php endif; ?>
            <input type="file" id="cover_image" name="cover_image" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" accept="image/*">
        </div>

        <div class="mb-6">
            <label for="content" class="block text-gray-700 text-sm font-bold mb-2">Nội dung:</label>
            <textarea id="content" name="content" rows="15"><?php echo htmlspecialchars($blog['content']); ?></textarea>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                Lưu thay đổi
            </button>
            <a href="#" data-page="blog/list.php" class="font-bold text-sm text-gray-600 hover:text-gray-800">
                Hủy
            </a>
        </div>
    </form>
</div>