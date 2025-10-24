<?php
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../database/db_connection.php';

// Lấy các bài blog đã được duyệt
$blogs = [];
$sql = "SELECT b.*, u.full_name FROM blogs b JOIN users u ON b.user_id = u.user_id WHERE b.status = 'approved' ORDER BY b.created_at DESC";
$result = $conn->query($sql);
if ($result) {
    $blogs = $result->fetch_all(MYSQLI_ASSOC);
}

// Tách bài viết đầu tiên làm bài nổi bật
$featured_blog = array_shift($blogs);

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Những Câu Chuyện Cà Phê</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Merriweather', serif;
            background-color: #fdfaf6;
        }

        h1,
        h2,
        h3 {
            font-family: 'Playfair Display', serif;
            color: #C28B58;
        }

        .blog-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .blog-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="text-gray-800">

    <main class="container mx-auto px-4 py-12">
        <div class="text-center mb-12">
            <h1 class="text-5xl font-bold mb-2">Những Câu Chuyện Quanh Tách Cà Phê</h1>
            <p class="text-lg text-gray-600">Nơi hương vị và ký ức hòa quyện.</p>
        </div>

        <!-- Nút viết bài mới cho người dùng đã đăng nhập -->
        <div class="text-center mb-12">
            <?php if (isset($_SESSION['user_id'])) : ?>
                <a href="<?php echo $base_url; ?>/customer/blog/create.php" class="inline-block bg-yellow-800 text-white font-bold py-3 px-6 rounded-lg shadow-lg hover:bg-yellow-900 transition-colors duration-300">
                    <i class="fas fa-pencil-alt mr-2"></i> Viết bài mới
                </a>
                <a href="<?php echo $base_url; ?>/customer/blog/myBlogs.php" class="inline-block bg-gray-600 text-white font-bold py-3 px-6 rounded-lg shadow-lg hover:bg-gray-700 transition-colors duration-300 ml-4">
                    <i class="fas fa-book-reader mr-2"></i> Bài viết của tôi
                </a>
            <?php else : ?>
                <p class="text-gray-600">
                    <a href="<?php echo $base_url; ?>/login/index.php" class="text-yellow-800 hover:underline font-semibold">Đăng nhập</a> để chia sẻ câu chuyện của bạn!
                </p>
            <?php endif; ?>
        </div>


        <!-- Danh sách bài blog -->
        <div>
            <?php if (!$featured_blog) : ?>
                <p class="text-center text-gray-500 text-xl">Chưa có bài viết nào được đăng.</p>
            <?php else : ?>
                <!-- Featured Blog Post -->
                <div class="mb-16 blog-card bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2">
                        <div class="p-8 flex flex-col justify-center">
                            <span class="text-sm font-semibold text-yellow-800 mb-2">BÀI VIẾT MỚI NHẤT</span>
                            <h2 class="text-4xl font-bold mb-4">
                                <a href="detail.php?slug=<?php echo htmlspecialchars($featured_blog['slug']); ?>" class="hover:text-yellow-900 transition-colors">
                                    <?php echo htmlspecialchars($featured_blog['title']); ?>
                                </a>
                            </h2>
                            <div class="text-sm text-gray-500 mb-4">
                                <span>Viết bởi <strong><?php echo htmlspecialchars($featured_blog['full_name']); ?></strong></span>
                                <span class="mx-2">•</span>
                                <span><?php echo date('d/m/Y', strtotime($featured_blog['created_at'])); ?></span>
                            </div>
                            <p class="text-gray-700 mb-6 flex-grow">
                                <?php
                                $stripped_content = strip_tags($featured_blog['content']);
                                echo mb_substr($stripped_content, 0, 200) . (mb_strlen($stripped_content) > 200 ? '...' : '');
                                ?>
                            </p>
                            <div>
                                <a href="detail.php?slug=<?php echo htmlspecialchars($featured_blog['slug']); ?>" class="font-bold text-yellow-800 hover:text-yellow-900 transition-colors group">
                                    Đọc tiếp <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                        <a href="detail.php?slug=<?php echo htmlspecialchars($featured_blog['slug']); ?>" class="block">
                            <img src="<?php echo $base_url . '/' . htmlspecialchars($featured_blog['cover_image'] ?: 'Photos/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($featured_blog['title']); ?>" class="w-full h-full object-cover">
                        </a>
                    </div>
                </div>

                <!-- Other Blog Posts -->
                <?php if (!empty($blogs)) : ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <?php foreach ($blogs as $blog) : ?>
                            <div class="blog-card bg-white rounded-lg shadow-md overflow-hidden flex flex-col">
                                <a href="detail.php?slug=<?php echo htmlspecialchars($blog['slug']); ?>">
                                    <img src="<?php echo $base_url . '/' . htmlspecialchars($blog['cover_image'] ?: 'Photos/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>" class="w-full h-56 object-cover">
                                </a>
                                <div class="p-6 flex-grow flex flex-col">
                                    <h2 class="text-2xl font-bold mb-2">
                                        <a href="detail.php?slug=<?php echo htmlspecialchars($blog['slug']); ?>" class="hover:text-yellow-900 transition-colors">
                                            <?php echo htmlspecialchars($blog['title']); ?>
                                        </a>
                                    </h2>
                                    <div class="text-sm text-gray-500 mb-4">
                                        <span>Viết bởi <strong><?php echo htmlspecialchars($blog['full_name']); ?></strong></span>
                                        <span class="mx-2">•</span>
                                        <span><?php echo date('d/m/Y', strtotime($blog['created_at'])); ?></span>
                                    </div>
                                    <div class="text-gray-700 mb-4 flex-grow">
                                        <?php
                                        $stripped_content = strip_tags($blog['content']);
                                        echo mb_substr($stripped_content, 0, 120) . (mb_strlen($stripped_content) > 120 ? '...' : '');
                                        ?>
                                    </div>
                                    <div class="mt-auto">
                                        <a href="detail.php?slug=<?php echo htmlspecialchars($blog['slug']); ?>" class="font-semibold text-yellow-800 hover:text-yellow-900 transition-colors group">
                                            Đọc tiếp <i class="fas fa-arrow-right ml-1 group-hover:translate-x-1 transition-transform"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

    </main>

    <button class="back-to-top fixed bottom-8 right-8 bg-yellow-800 text-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center text-xl hover:bg-yellow-900 transition-all duration-300 opacity-0" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        window.addEventListener('scroll', function() {
            const backToTop = document.querySelector('.back-to-top');
            if (window.pageYOffset > 300) {
                backToTop.classList.remove('opacity-0');
            } else {
                backToTop.classList.add('opacity-0');
            }
        });
    </script>

    <?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>

</html>