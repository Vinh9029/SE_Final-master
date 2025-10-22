<?php
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../database/db_connection.php';

// Kiểm tra xem có slug được truyền qua URL không
if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    // Nếu không có, chuyển hướng về trang danh sách blog
    header("Location: " . $base_url . "/pages/blogs/index.php");
    exit();
}

$slug = $_GET['slug'];

// Lấy thông tin chi tiết bài blog từ CSDL
$blog = null;
$sql = "SELECT b.*, u.full_name 
        FROM blogs b 
        JOIN users u ON b.user_id = u.user_id 
        WHERE b.slug = ? AND b.status = 'approved' 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $blog = $result->fetch_assoc();
} else {
    // Nếu không tìm thấy bài viết, hiển thị trang 404 đơn giản
    http_response_code(404);
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $blog ? htmlspecialchars($blog['title']) : 'Không tìm thấy bài viết'; ?> - Old Flavour Blog</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Merriweather', serif;
            background-color: #fdfaf6;
            color: #3a3a3a;
        }

        .blog-title {
            font-family: 'Playfair Display', serif;
            color: #C28B58;
        }

        /* Styling cho nội dung được render từ CSDL */
        .blog-content {
            line-height: 1.8;
            font-size: 1.1rem;
        }

        .blog-content h1,
        .blog-content h2,
        .blog-content h3 {
            font-family: 'Playfair Display', serif;
            color: #C28B58;
            margin-top: 2rem;
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .blog-content p {
            margin-bottom: 1.25rem;
        }

        .blog-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 2rem auto;
            display: block;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .blog-content blockquote {
            border-left: 4px solid #d4a574;
            padding-left: 1.5rem;
            margin: 2rem 0;
            font-style: italic;
            color: #6b4a28;
            font-size: 1.2rem;
        }
    </style>
</head>

<body>
    <main class="container mx-auto px-4 py-12">
        <?php if ($blog) : ?>
            <article class="max-w-4xl mx-auto bg-white p-6 sm:p-10 rounded-lg shadow-lg">
                <h1 class="blog-title text-4xl md:text-5xl font-bold text-center mb-4"><?php echo htmlspecialchars($blog['title']); ?></h1>
                <div class="text-center text-gray-500 mb-8">
                    <span>Viết bởi <strong><?php echo htmlspecialchars($blog['full_name']); ?></strong></span>
                    <span class="mx-2">•</span>
                    <span>Ngày đăng: <?php echo date('d/m/Y', strtotime($blog['created_at'])); ?></span>
                </div>

                <?php if ($blog['cover_image']) : ?>
                    <img src="<?php echo $base_url . '/' . htmlspecialchars($blog['cover_image']); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>" class="w-full h-auto object-cover rounded-lg mb-8 shadow-md">
                <?php endif; ?>

                <div class="blog-content">
                    <?php echo $blog['content']; // Nội dung HTML được render trực tiếp ?>
                </div>

                <div class="text-center mt-12">
                    <a href="<?php echo $base_url; ?>/pages/blogs/index.php" class="inline-block bg-yellow-800 text-white font-bold py-3 px-6 rounded-lg shadow-lg hover:bg-yellow-900 transition-colors duration-300">
                        <i class="fas fa-arrow-left mr-2"></i> Quay lại danh sách Blog
                    </a>
                </div>
            </article>
        <?php else : ?>
            <div class="text-center py-20">
                <h1 class="text-6xl font-bold text-gray-300">404</h1>
                <h2 class="text-2xl font-semibold text-yellow-800 mt-4">Bài viết không tồn tại</h2>
                <p class="text-gray-600 mt-2">Rất tiếc, chúng tôi không thể tìm thấy bài viết bạn yêu cầu. Có thể nó đã bị xóa hoặc đường dẫn không còn tồn tại.</p>
                <a href="<?php echo $base_url; ?>/pages/blogs/index.php" class="mt-8 inline-block bg-yellow-800 text-white font-bold py-3 px-6 rounded-lg shadow-lg hover:bg-yellow-900 transition-colors">
                    Về trang Blog
                </a>
            </div>
        <?php endif; ?>
    </main>

    <?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>

</html>