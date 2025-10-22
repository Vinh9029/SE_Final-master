<?php
include_once __DIR__ . "/../config.php";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Lấy số lượng sản phẩm trong giỏ hàng
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    include_once __DIR__ . '/../database/db_connection.php';
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT SUM(quantity) AS total FROM cart_items WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $cart_count = $result['total'] ?? 0;
}
?>

<header class="bg-white shadow-md sticky top-0 z-50">
    <div class="container mx-auto flex items-center justify-between py-3 px-6">
        <!-- Logo + Tên quán -->
        <div class="flex items-center gap-2">
            <img src="<?php echo $base_url; ?>/Photos/banner.jpg" alt="Logo" class="h-12 w-12 object-cover rounded-full shadow" />
            <span class="text-2xl font-bold text-pink-600 tracking-wide select-none"><a href="<?php echo $base_url; ?>/index.php">Old Flavour</a></span>
        </div>

        <!-- Menu điều hướng -->
        <nav class="flex-1 flex justify-center">
            <ul class="flex items-center gap-6">
                <li><a href="<?php echo $base_url; ?>/index.php" class="text-gray-700 hover:text-pink-600 font-medium transition">Trang chủ</a></li>
                <li><a href="<?php echo $base_url; ?>/menus/menus.php" class="text-gray-700 hover:text-pink-600 font-bold transition flex items-center gap-2">Thực đơn</a></li>
                <li><a href="<?php echo $base_url; ?>/pages/promotion.php" class="relative text-gray-700 hover:text-pink-600 font-bold transition flex items-center gap-2"><i class="fa fa-gift text-pink-500"></i> Khuyến mãi</a></li>
                <li><a href="<?php echo $base_url; ?>/pages/aboutUs.php" class="text-gray-700 hover:text-pink-600 font-medium transition">Về chúng tôi</a></li>
                <li><a href="<?php echo $base_url; ?>/pages/contactUS.php" class="text-gray-700 hover:text-pink-600 font-medium transition">Liên hệ</a></li>
                <li><a href="<?php echo $base_url; ?>/pages/blogs/index.php" class="text-gray-700 hover:text-pink-600 font-medium transition">Blogs</a></li>
            </ul>
        </nav>

        <!-- Search | Cart | Login/Account -->
        <div class="flex items-center gap-4">
            <!-- Search bar -->
            <form action="#" method="get" class="relative hidden md:block" autocomplete="off" id="searchForm">
                <input type="text" name="search" id="searchInput" placeholder="Tìm kiếm..." class="border rounded-full px-3 py-1 pl-8 focus:outline-none focus:ring-2 focus:ring-pink-200 text-sm bg-gray-50" />
                <span class="absolute left-2 top-1.5 text-gray-400"><i class="fa fa-search"></i></span>
                <div id="searchDropdown" class="absolute left-0 top-10 w-full bg-white rounded-xl shadow-lg z-50" style="display:none;"></div>
            </form>
            <script>
            const searchInput = document.getElementById('searchInput');
            const searchDropdown = document.getElementById('searchDropdown');
            let searchTimeout = null;
            searchInput.addEventListener('input', function() {
                const val = this.value.trim();
                if (val.length === 0) {
                    searchDropdown.style.display = 'none';
                    searchDropdown.innerHTML = '';
                    return;
                }
                searchDropdown.style.display = 'block';
                searchDropdown.innerHTML = '<div class="py-3 px-4 text-center"><i class="fa fa-spinner fa-spin text-pink-500"></i> Đang tìm kiếm...</div>';
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    fetch(`/menus/searchProduct.php?q=" + encodeURIComponent(val) + "`)
                        .then(res => res.json())
                        .then(data => {
                            if (!Array.isArray(data) || data.length === 0) {
                                searchDropdown.innerHTML = '<div class="py-3 px-4 text-center text-gray-400">Không tìm thấy sản phẩm phù hợp.</div>';
                                return;
                            }
                            let html = '<div class="py-2 px-4 font-bold text-pink-600 border-b">Sản phẩm</div>';
                            data.forEach(item => {
                                html += `<a href="/menus/product.php?slug=${item.slug}" class="flex items-center gap-3 px-4 py-2 hover:bg-pink-50 transition">
                                    <img src="${item.image}" alt="${item.name}" class="w-12 h-12 object-cover rounded shadow border border-pink-100" />
                                    <div class="flex-1">
                                        <div class="font-bold text-pink-600">${item.name}</div>
                                        <div class="text-orange-600 font-semibold text-sm">${item.price}</div>
                                    </div>
                                </a>`;
                            });
                            searchDropdown.innerHTML = html;
                        });
                }, 350);
            });
            // Ẩn dropdown khi click ra ngoài
            window.addEventListener('click', function(e) {
                if (!searchDropdown.contains(e.target) && e.target !== searchInput) {
                    searchDropdown.style.display = 'none';
                }
            });
            </script>
            <!-- Cart icon -->
            <a href="<?php echo $base_url; ?>/customer/cart/index.php" class="relative text-gray-700 hover:text-pink-600 text-xl transition">
                <i class="fa fa-shopping-cart"></i>
                <!-- Badge số lượng (nếu có) -->
                <?php if ($cart_count > 0): ?>
                    <span class="absolute -top-2 -right-2 bg-pink-500 text-white text-xs rounded-full px-1"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="relative group">
                    <a href="<?php echo $base_url; ?>/customer/account.php" class="text-gray-700 hover:text-pink-600 font-medium transition flex items-center gap-1">
                        <i class="fa fa-user-circle text-lg"></i>
                        <span>Tài khoản</span>
                    </a>
                    <div class="absolute right-0 mt-2 w-48 bg-white border rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-50">
                        <a href="<?php echo $base_url; ?>/customer/account.php?page=profile" class="block px-4 py-2 text-gray-700 hover:bg-pink-50">Thông tin tài khoản</a>
                        <a href="<?php echo $base_url; ?>/customer/account.php?page=orders" class="block px-4 py-2 text-gray-700 hover:bg-pink-50">Đơn hàng</a>
                        <a href="<?php echo $base_url; ?>/customer/account.php?page=settings" class="block px-4 py-2 text-gray-700 hover:bg-pink-50">Cài đặt tài khoản</a>
                        <a href="<?php echo $base_url; ?>/customer/logout.php" class="block px-4 py-2 text-gray-700 hover:bg-pink-50">Đăng xuất</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo $base_url; ?>/login/index.php" class="text-gray-700 hover:text-pink-600 font-medium transition flex items-center gap-1">
                    <i class="fa fa-user-circle text-lg"></i>
                    <span>Đăng nhập</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>
