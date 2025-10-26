<?php
include_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database/db_connection.php';
require_once __DIR__ . '/helper.php';

// Lấy slug danh mục từ query string (?cat=)
$catSlug = isset($_GET['cat']) ? $_GET['cat'] : null;
$sort = $_GET['sort'] ?? 'default';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Lấy tất cả danh mục
$catStmt = $conn->query("SELECT * FROM categories");
$categories = $catStmt->fetch_all(MYSQLI_ASSOC);
$cat_names = [];
foreach ($categories as $c) {
    $cat_names[generateSlug($c['name'])] = $c['name'];
}
// Tìm danh mục theo slug
$category = null;
foreach ($categories as $c) {
    if (generateSlug($c['name']) === $catSlug) {
        $category = $c;
        break;
    }
}
if (!$category) {
    // Nếu không có slug, lấy danh mục đầu tiên
    $category = $categories[0];
    $catSlug = generateSlug($category['name']);
}
// Lấy sản phẩm thuộc danh mục
$query = "SELECT * FROM products WHERE category_id = ?";
if ($sort == 'name_asc') $query .= " ORDER BY name ASC";
elseif ($sort == 'name_desc') $query .= " ORDER BY name DESC";
elseif ($sort == 'price_asc') $query .= " ORDER BY price ASC";
elseif ($sort == 'price_desc') $query .= " ORDER BY price DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $category['category_id']);
$stmt->execute();
$allItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
// Phân trang
$itemsPerPage = 9;
$totalItems = count($allItems);
$totalPages = ceil($totalItems / $itemsPerPage);
$offset = ($page - 1) * $itemsPerPage;
$pagedItems = array_slice($allItems, $offset, $itemsPerPage);

// Get all favourite IDs for the current user
$favourite_ids = [];
if (isset($_SESSION['user_id'])) {
    $fav_sql = "SELECT product_id FROM favourites WHERE customer_id = ?";
    $fav_stmt = $conn->prepare($fav_sql);
    $fav_stmt->bind_param('i', $_SESSION['user_id']);
    $fav_stmt->execute();
    $fav_result = $fav_stmt->get_result();
    while ($row = $fav_result->fetch_assoc()) {
        $favourite_ids[] = $row['product_id'];
    }
    $fav_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thực đơn | Old Favour Coffee</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .btn-orange { background: #FF7A00; }
        .btn-orange:hover { background: #ff9800; }
        .footer-bg { background: #3d2c1a; }
    </style>
</head>
<body class="bg-pink-50 font-sans">
    <!-- Header -->
    <?php include_once __DIR__ . '/../includes/header.php'; ?>
    <!-- Hero Banner Slider -->
    <section class="relative h-64 md:h-80 w-full flex items-center justify-center mb-10">
        <div id="hero-slider" class="relative w-full h-full overflow-hidden rounded-xl">
            <img src="../Photos/banner.jpg" class="hero-slide absolute inset-0 w-full h-full object-cover brightness-75 opacity-100 transition-opacity duration-700" style="z-index:2;" />
            <img src="../Photos/test1.jpg" class="hero-slide absolute inset-0 w-full h-full object-cover brightness-75 opacity-0 transition-opacity duration-700" style="z-index:1;" />
            <img src="../Photos/test2.jpg" class="hero-slide absolute inset-0 w-full h-full object-cover brightness-75 opacity-0 transition-opacity duration-700" style="z-index:0;" />
            <button onclick="prevHeroSlide()" class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/70 hover:bg-orange-500 text-orange-600 hover:text-white rounded-full p-2 shadow z-10"><i class="fa fa-chevron-left"></i></button>
            <button onclick="nextHeroSlide()" class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/70 hover:bg-orange-500 text-orange-600 hover:text-white rounded-full p-2 shadow z-10"><i class="fa fa-chevron-right"></i></button>
            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                <span class="hero-dot w-3 h-3 rounded-full bg-orange-500"></span>
                <span class="hero-dot w-3 h-3 rounded-full bg-gray-300"></span>
                <span class="hero-dot w-3 h-3 rounded-full bg-gray-300"></span>
            </div>
            <div class="absolute inset-0 flex items-center justify-center">
                <h1 class="text-3xl md:text-4xl font-bold text-white drop-shadow-lg text-center">Hạnh phúc trong từng tách cà phê!</h1>
            </div>
        </div>
    </section>
    <script>
        let heroCurrent = 0;
        const heroSlides = document.querySelectorAll('.hero-slide');
        const heroDots = document.querySelectorAll('.hero-dot');
        function showHeroSlide(idx) {
            heroSlides.forEach((img, i) => { img.style.opacity = i === idx ? '1' : '0'; });
            heroDots.forEach((dot, i) => { dot.className = 'hero-dot w-3 h-3 rounded-full ' + (i === idx ? 'bg-orange-500' : 'bg-gray-300'); });
            heroCurrent = idx;
        }
        function nextHeroSlide() { showHeroSlide((heroCurrent + 1) % heroSlides.length); }
        function prevHeroSlide() { showHeroSlide((heroCurrent - 1 + heroSlides.length) % heroSlides.length); }
        document.querySelectorAll('.hero-dot').forEach((dot, i) => { dot.onclick = () => showHeroSlide(i); });
        setInterval(nextHeroSlide, 5000); showHeroSlide(0);
    </script>
    <!-- Breadcrumb -->
    <div class="max-w-6xl mx-auto px-2 mb-4">
        <nav class="text-lg font-extrabold flex items-center" aria-label="Breadcrumb">
            <a href="../index.php" class="text-pink-500 hover:text-pink-600">Trang chủ</a>
            <span class="mx-2 text-pink-300 font-bold">/</span>
            <span class="text-pink-600"><?php echo $category['name']; ?></span>
        </nav>
    </div>
    <!-- Menu Tabs/Filter -->
    <div class="max-w-6xl mx-auto px-2">
        <div class="flex flex-wrap justify-between items-center gap-2 mb-8">
            <div class="flex flex-wrap gap-2">
                <?php foreach ($cat_names as $key => $label): ?>
                    <a href="?cat=<?php echo htmlspecialchars($key); ?><?php echo isset($_GET['sort']) ? '&sort=' . htmlspecialchars($_GET['sort']) : ''; ?>" class="px-6 py-2 rounded-t-xl font-bold text-lg text-gray-700 <?php echo ($catSlug == $key) ? 'bg-pink-500 text-white shadow-lg' : 'bg-pink-100 hover:bg-pink-200 shadow'; ?> transition duration-200">
                        <?php echo htmlspecialchars($label); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="flex items-center gap-2">
                <label for="sort-select" class="font-bold text-lg text-gray-700 flex items-center gap-1" aria-label="Sắp xếp theo">
                    <i class="fa fa-sort text-gray-500"></i> Sắp xếp:
                </label>
                <select id="sort-select" class="border rounded px-3 py-2 text-base font-semibold focus:outline-none focus:ring-2 focus:ring-orange-200 bg-white shadow" onchange="onSortChange(this.value)">
                    <option value="default" <?php echo ($sort == 'default') ? 'selected' : ''; ?>>Mặc định</option>
                    <option value="name_asc" <?php echo ($sort == 'name_asc') ? 'selected' : ''; ?>>Tên A-Z</option>
                    <option value="name_desc" <?php echo ($sort == 'name_desc') ? 'selected' : ''; ?>>Tên Z-A</option>
                    <option value="price_asc" <?php echo ($sort == 'price_asc') ? 'selected' : ''; ?>>Giá thấp đến cao</option>
                    <option value="price_desc" <?php echo ($sort == 'price_desc') ? 'selected' : ''; ?>>Giá cao xuống thấp</option>
                </select>
            </div>
        </div>
        <script>
            function onSortChange(val) {
                const url = new URL(window.location.href);
                url.searchParams.set('sort', val);
                window.location.href = url.toString();
            }
        </script>
        <!-- Grid sản phẩm -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-8">
            <?php foreach ($pagedItems as $item): ?>
                <div class="relative bg-white rounded-2xl shadow-xl p-6 flex flex-col items-center hover:scale-105 hover:shadow-pink-300 transition duration-200 group">
                    <?php if (isset($_SESSION['user_id'])):
                        $is_favourited = in_array($item['product_id'], $favourite_ids);
                    ?>
                        <button class="favourite-btn absolute top-3 right-3 text-xl <?php echo $is_favourited ? 'text-pink-500' : 'text-gray-300'; ?> hover:text-pink-400 transition" data-product-id="<?php echo $item['product_id']; ?>">
                            <i class="fa <?php echo $is_favourited ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
                        </button>
                    <?php endif; ?>

                    <a href="product.php?slug=<?php echo generateSlug($item['name']); ?>">
                        <img src="<?php echo $base_url . '/' . ($item['image'] ?: 'Photos/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-32 h-32 object-cover rounded-xl mb-4 shadow bg-gray-100 border-2 border-pink-100" />
                    </a>
                    <div class="font-extrabold text-pink-600 text-xl text-center mb-1"><?php echo $item['name']; ?></div>
                    <div class="text-orange-600 font-bold text-lg mb-2 text-center"><?php echo number_format($item['price'], 0, ',', '.'); ?> đ</div>
                    <a href="product.php?slug=<?php echo generateSlug($item['name']); ?>" class="btn-orange hover:bg-orange-600 text-white px-6 py-2 rounded-xl font-bold text-base mt-2 shadow transition duration-200">Xem chi tiết</a>
                </div>
            <?php endforeach; ?>
        </div>
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="flex justify-center mt-6 space-x-2">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?cat=<?php echo urlencode($catSlug); ?>&sort=<?php echo urlencode($sort); ?>&page=<?php echo $i; ?>"
                        class="px-4 py-2 rounded-2xl font-extrabold text-lg transition border-2 <?php echo $i === $page ? 'bg-pink-500 text-white border-pink-500 shadow-lg' : 'bg-white text-pink-500 border-pink-200 hover:bg-pink-100 hover:text-pink-600'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
    <br><br>
    
    <?php include_once __DIR__ . '/../includes/footer.php'; ?>

    <script>
    // Favourite button handler
    document.querySelectorAll('.favourite-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent link navigation if button is inside an <a> tag
            
            const productId = this.dataset.productId;
            const icon = this.querySelector('i');
            const isFavourited = icon.classList.contains('fa-solid');

            const url = isFavourited 
                ? '<?php echo $base_url; ?>/includes/handlers/favourite/removeFavourite.php'
                : '<?php echo $base_url; ?>/includes/handlers/favourite/addFavourite.php';

            const formData = new FormData();
            formData.append('product_id', productId);

            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (isFavourited) {
                        icon.classList.remove('fa-solid', 'text-pink-500');
                        icon.classList.add('fa-regular', 'text-gray-300');
                    } else {
                        icon.classList.add('fa-solid', 'text-pink-500');
                        icon.classList.remove('fa-regular', 'text-gray-300');
                    }
                    // Reload header to update count
                    // A better implementation would be to update the count via JS
                    setTimeout(() => {
                        $("#header-container").load(location.href + " #header-container>*", "");
                    }, 200)
                } else {
                    if (data.message && data.message.includes('log in')) {
                        window.location.href = '<?php echo $base_url; ?>/login/index.php';
                    } else {
                        alert(data.message || 'An error occurred.');
                    }
                }
            });
        });
    });
    </script>
</body>
</html>