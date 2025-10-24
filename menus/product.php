<?php
include_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database/db_connection.php';
require_once __DIR__ . '/helper.php';

$slug = isset($_GET['slug']) ? $_GET['slug'] : null;
if (!$slug) {
    die('Không tìm thấy sản phẩm!');
}
// Lấy sản phẩm theo slug
$stmt = $conn->query("SELECT p.*, c.name as category_name, c.description as category_desc FROM products p JOIN categories c ON p.category_id = c.category_id");
$products = $stmt->fetch_all(MYSQLI_ASSOC);
$product = null;
foreach ($products as $p) {
    if (generateSlug($p['name']) === $slug) {
        $product = $p;
        break;
    }
}
if (!$product) {
    die('Sản phẩm không tồn tại!');
}

// Lấy danh sách size từ DB
$sizeQuery = $conn->prepare("SELECT * FROM product_sizes WHERE product_id = ?");
$sizeQuery->bind_param("i", $product['product_id']);
$sizeQuery->execute();
$sizeResult = $sizeQuery->get_result();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - Chi tiết sản phẩm | Old Favour Coffee</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .btn-orange {
            background: #FF7A00;
        }

        .btn-orange:hover {
            background: #ff9800;
        }

        .footer-bg {
            background: #3d2c1a;
        }
    </style>
</head>

<body class="bg-pink-50 font-sans">
    <?php include_once __DIR__ . '/../includes/header.php'; ?>
    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Breadcrumb -->
        <nav class="text-lg font-extrabold flex items-center mb-6" aria-label="Breadcrumb">
            <a href="../index.php" class="text-pink-500 hover:text-pink-600">Trang chủ</a>
            <span class="mx-2 text-pink-300 font-bold">/</span>
            <a href="menus.php?cat=<?php echo generateSlug($product['category_name']); ?>" class="text-pink-500 hover:text-pink-600"><?php echo $product['category_name']; ?></a>
            <span class="mx-2 text-pink-300 font-bold">/</span>
            <span class="text-pink-600"><?php echo $product['name']; ?></span>
        </nav>
        <div class="bg-white rounded-2xl shadow-xl p-8 flex flex-col md:flex-row gap-8 items-center">
            <div class="flex-shrink-0">
                <img src="<?php echo $base_url . '/' . ($product['image'] ?: 'Photos/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-64 h-64 object-cover rounded-xl shadow bg-gray-100 border-2 border-pink-100" />
            </div>
            <div class="flex-1 flex flex-col justify-center">
                <h1 class="font-extrabold text-pink-600 text-3xl mb-2"><?php echo $product['name']; ?></h1>
                <div class="mb-6">
                    <h4 class="font-bold text-lg mb-2 text-pink-600">Chọn Size</h4>
                    <div class="flex flex-wrap gap-4">
                        <?php if ($sizeResult->num_rows > 0): ?>
                            <?php while ($size = $sizeResult->fetch_assoc()): ?>
                                <label class="size-option flex items-center gap-2 px-4 py-2 rounded-xl border border-pink-200 bg-pink-50 cursor-pointer hover:bg-pink-100 transition">
                                    <input type="radio" name="product_size" value="<?php echo $size['size_id']; ?>" data-extra="<?php echo $size['extra_price']; ?>" class="accent-pink-500" required>
                                    <span class="font-semibold text-pink-700"><?php echo htmlspecialchars($size['size_name']); ?></span>
                                    <span class="text-gray-500 text-sm"><?php echo htmlspecialchars($size['volume']); ?></span>
                                    <?php if ($size['extra_price'] > 0): ?>
                                        <span class="text-orange-600 font-bold text-sm">(+<?php echo number_format($size['extra_price'], 0, ',', '.'); ?> đ)</span>
                                    <?php endif; ?>
                                </label>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="italic text-gray-400">Hiện chưa có size cho sản phẩm này.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="text-orange-600 font-bold text-2xl mb-4" id="product-price"><?php echo number_format($product['price'], 0, ',', '.'); ?> đ</div>
                <div class="mb-4 text-gray-700 text-base leading-relaxed"><?php echo $product['description'] ?: '<span class="italic text-gray-400">Chưa có mô tả cho sản phẩm này.</span>'; ?></div>
                <div class="flex gap-4 mt-6">
                    <button id="add-to-cart-btn" class="btn-orange hover:bg-orange-600 text-white px-8 py-3 rounded-xl font-bold text-lg shadow transition duration-200"><i class="fa fa-shopping-cart mr-2"></i>Thêm món ngay</button>
                    <a href="menus.php?cat=<?php echo generateSlug($product['category_name']); ?>" class="bg-gray-100 hover:bg-pink-100 text-pink-600 px-6 py-3 rounded-xl font-bold text-lg shadow transition duration-200"><i class="fa fa-arrow-left mr-2"></i>Quay lại menu</a>
                </div>
            </div>
        </div>
    </div>
    <?php include_once __DIR__ . '/../includes/footer.php'; ?>

    <!-- Toast Notification -->
    <div id="toast" class="fixed top-20 right-5 bg-green-500 text-white py-3 px-6 rounded-xl shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out z-50">
        <i class="fa fa-check-circle mr-2"></i>
        <span id="toast-message"></span>
    </div>
    <style>
        #toast.show {
            transform: translateX(0);
        }
        #toast.error {
            background-color: #ef4444; /* bg-red-500 */
        }
    </style>

    <script>
        // Cập nhật giá khi chọn size
        setTimeout(function() {
            document.querySelectorAll('input[name="product_size"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const basePrice = <?php echo $product['price']; ?>;
                    const extra = parseFloat(this.dataset.extra) || 0;
                    const finalPrice = basePrice + extra;
                    document.getElementById("product-price").innerText = finalPrice.toLocaleString('vi-VN') + " đ";
                });
            });
        }, 100);

        // Toast function
        function showToast(message, isError = false) {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');
            toastMessage.textContent = message;
            if (isError) {
                toast.classList.add('error');
            } else {
                toast.classList.remove('error');
            }
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 2500);
        }
        // Xử lý thêm vào giỏ hàng
        document.getElementById('add-to-cart-btn').onclick = function() {
            const productId = <?php echo $product['product_id']; ?>;
            const sizeRadio = document.querySelector('input[name="product_size"]:checked');
            const sizeId = sizeRadio ? sizeRadio.value : null;
            const quantity = 1;
            fetch('<?php echo $base_url; ?>/customer/cart/add.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `product_id=${productId}&size_id=${sizeId}&quantity=${quantity}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Đã thêm vào giỏ hàng!');
                    setTimeout(() => {
                        window.location.reload(); // Tự động tải lại trang sau khi toast biến mất
                    }, 800);
                } else {
                    showToast(data.message, true);
                }
            });
        };
    </script>
</body>

</html>