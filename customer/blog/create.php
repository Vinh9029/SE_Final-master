<?php
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../database/db_connection.php';

// Chỉ cho phép người dùng đã đăng nhập truy cập
if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_url . "/login/index.php");
    exit();
}

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
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $slug = create_slug($title) . '-' . time(); // Thêm timestamp để slug luôn unique

    if (empty($title)) $errors[] = "Tiêu đề không được để trống.";
    if (empty($content)) $errors[] = "Nội dung không được để trống.";

    // Xử lý upload ảnh
    $cover_image = null;
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        $target_dir = "uploads/blogs/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["cover_image"]["name"]);
        if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], __DIR__ . '/../../' . $target_file)) {
            $cover_image = $target_file;
        } else {
            $errors[] = "Có lỗi xảy ra khi tải ảnh lên.";
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO blogs (user_id, title, content, cover_image, slug, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("issss", $user_id, $title, $content, $cover_image, $slug);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Bài viết của bạn đã được gửi và đang chờ duyệt!";
            header("Location: myBlogs.php");
            exit();
        } else {
            $errors[] = "Lỗi khi lưu bài viết: " . $stmt->error;
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

<main class="container mx-auto px-4 py-12">
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold text-yellow-800 mb-6">Viết câu chuyện của bạn</h1>

        <?php if (!empty($errors)) : ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <?php foreach ($errors as $error) : ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="create.php" method="POST" enctype="multipart/form-data">
            <div class="mb-6">
                <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Tiêu đề bài viết:</label>
                <input type="text" id="title" name="title" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>

            <div class="mb-6">
                <label for="cover_image" class="block text-gray-700 text-sm font-bold mb-2">Ảnh bìa:</label>
                <input type="file" id="cover_image" name="cover_image" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" accept="image/*">
            </div>

            <div class="mb-6">
                <label for="content" class="block text-gray-700 text-sm font-bold mb-2">Nội dung:</label>
                <textarea id="content" name="content" rows="15"></textarea>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-yellow-800 hover:bg-yellow-900 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition-colors">
                    Gửi bài viết
                </button>
                <a href="myBlogs.php" class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800">
                    Hủy
                </a>
            </div>
        </form>
    </div>
</main>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>