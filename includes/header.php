
<?php
include_once __DIR__ . "/../config.php";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Lấy số lượng sản phẩm trong giỏ hàng
$cart_count = 0;
$favourite_count = 0;
$notification_count = 0;

if (isset($_SESSION['user_id'])) {
    include_once __DIR__ . '/../database/db_connection.php';
    $user_id = $_SESSION['user_id'];

    // Cart count
    $sql = "SELECT SUM(quantity) AS total FROM cart_items WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $cart_count = $result['total'] ?? 0;

    // Favourite count
    $fav_sql = "SELECT COUNT(*) AS total FROM favourites WHERE customer_id = ?";
    $fav_stmt = $conn->prepare($fav_sql);
    $fav_stmt->bind_param('i', $user_id);
    $fav_stmt->execute();
    $fav_result = $fav_stmt->get_result()->fetch_assoc();
    $favourite_count = $fav_result['total'] ?? 0;

    // Unread notification count
    $noti_sql = "SELECT COUNT(*) AS total FROM notifications WHERE customer_id = ? AND is_read = 0";
    $noti_stmt = $conn->prepare($noti_sql);
    $noti_stmt->bind_param('i', $user_id);
    $noti_stmt->execute();
    $noti_result = $noti_stmt->get_result()->fetch_assoc();
    $notification_count = $noti_result['total'] ?? 0;

    // Kiểm tra tài khoản có bị vô hiệu hóa không
    include_once __DIR__ . '/deactivatedUser.php';
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
            
            <!-- Cart icon -->
            <a href="<?php echo $base_url; ?>/customer/cart/index.php" class="relative text-gray-700 hover:text-pink-600 text-xl transition">
                <i class="fa fa-shopping-cart"></i>
                <?php if ($cart_count > 0): ?>
                    <span class="absolute -top-2 -right-2 bg-pink-500 text-white text-xs rounded-full px-1"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Favourite icon -->
                <div class="relative group" id="fav-group">
                    <a href="<?php echo $base_url; ?>/customer/favourites.php" class="relative text-gray-700 hover:text-pink-600 text-xl transition">
                        <i class="fa fa-heart"></i>
                        <?php if ($favourite_count > 0): ?>
                            <span id="favourite-badge" class="absolute -top-2 -right-2 bg-pink-500 text-white text-xs rounded-full px-1"><?php echo $favourite_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <div id="mini-favourites-dropdown" class="absolute right-0 mt-2 w-80 bg-white border rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <div class="p-4 text-center text-gray-500">Loading...</div>
                    </div>
                </div>

                <!-- Notification icon -->
                <div class="relative group" id="notif-group">
                    <a href="<?php echo $base_url; ?>/customer/notifications.php" class="relative text-gray-700 hover:text-pink-600 text-xl transition">
                        <i class="fa fa-bell"></i>
                        <?php if ($notification_count > 0): ?>
                            <span id="notification-badge" class="absolute -top-2 -right-2 bg-pink-500 text-white text-xs rounded-full px-1"><?php echo $notification_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <div id="notifications-dropdown" class="absolute right-0 mt-2 w-80 bg-white border rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <div class="p-4 text-center text-gray-500">Loading...</div>
                    </div>
                </div>

                <!-- User account dropdown -->
                <div class="relative group">
                    <a href="<?php echo $base_url; ?>/customer/account.php" class="text-gray-700 hover:text-pink-600 font-medium transition flex items-center gap-1">
                        <i class="fa fa-user-circle text-lg"></i>
                        <span>Tài khoản</span>
                    </a>
                    <div class="absolute right-0 mt-2 w-48 bg-white border rounded shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <a href="<?php echo $base_url; ?>/customer/account.php?page=profile" class="block px-4 py-2 text-gray-700 hover:bg-pink-50">Thông tin tài khoản</a>
                        <a href="<?php echo $base_url; ?>/customer/account.php?page=orders" class="block px-4 py-2 text-gray-700 hover:bg-pink-50">Đơn hàng</a>
                        <a href="<?php echo $base_url; ?>/customer/account.php?page=vouchers" class="block px-4 py-2 text-gray-700 hover:bg-pink-50">Voucher của tôi</a>
                        <a href="<?php echo $base_url; ?>/customer/account.php?page=comments" class="block px-4 py-2 text-gray-700 hover:bg-pink-50">Bình luận của tôi</a>
                        <a href="<?php echo $base_url; ?>/customer/account.php?page=settings" class="block px-4 py-2 text-gray-700 hover:bg-pink-50 border-t">Cài đặt</a>
                        <a href="<?php echo $base_url; ?>/customer/logout.php" class="block px-4 py-2 text-gray-700 hover:bg-pink-50 border-t">Đăng xuất</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Login link -->
                <a href="<?php echo $base_url; ?>/login/index.php" class="text-gray-700 hover:text-pink-600 font-medium transition flex items-center gap-1">
                    <i class="fa fa-user-circle text-lg"></i>
                    <span>Đăng nhập</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Search script
        const searchInput = document.getElementById('searchInput');
        const searchDropdown = document.getElementById('searchDropdown');
        let searchTimeout = null;
        if(searchInput) {
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
                    fetch(`<?php echo $base_url; ?>/menus/searchProduct.php?q=${encodeURIComponent(val)}`)
                        .then(res => res.json())
                        .then(data => {
                            if (!Array.isArray(data) || data.length === 0) {
                                searchDropdown.innerHTML = '<div class="py-3 px-4 text-center text-gray-400">Không tìm thấy sản phẩm phù hợp.</div>';
                                return;
                            }
                            let html = '<div class="py-2 px-4 font-bold text-pink-600 border-b">Sản phẩm</div>';
                            data.forEach(item => {
                                html += `<a href="<?php echo $base_url; ?>/menus/product.php?slug=${item.slug}" class="flex items-center gap-3 px-4 py-2 hover:bg-pink-50 transition">
                                    <img src="<?php echo $base_url; ?>/${item.image}" alt="${item.name}" class="w-12 h-12 object-cover rounded shadow border border-pink-100" />
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
            window.addEventListener('click', function(e) {
                if (searchDropdown && !searchDropdown.contains(e.target) && e.target !== searchInput) {
                    searchDropdown.style.display = 'none';
                }
            });
        }

        <?php if (isset($_SESSION['user_id'])): ?>
        // Favourites and Notifications script
        const miniFavouritesDropdown = document.getElementById('mini-favourites-dropdown');
        const notificationsDropdown = document.getElementById('notifications-dropdown');
        const favGroup = document.getElementById('fav-group');
        const notifGroup = document.getElementById('notif-group');

        function loadMiniFavourites() {
            fetch(`<?php echo $base_url; ?>/includes/handlers/favourite/miniFavourites.php`)
                .then(res => res.text())
                .then(data => {
                    if(miniFavouritesDropdown) miniFavouritesDropdown.innerHTML = data;
                }).catch(err => {
                    if(miniFavouritesDropdown) miniFavouritesDropdown.innerHTML = '<div class="p-4 text-center text-red-500">Could not load items.</div>';
                });
        }

        function loadNotifications() {
            fetch(`<?php echo $base_url; ?>/includes/handlers/notification/getNotifications.php?mini=true`)
                .then(res => res.text())
                .then(data => {
                    if(notificationsDropdown) notificationsDropdown.innerHTML = data;
                }).catch(err => {
                    if(notificationsDropdown) notificationsDropdown.innerHTML = '<div class="p-4 text-center text-red-500">Could not load notifications.</div>';
                });
        }

        let favLoaded = false;
        if(favGroup) {
            favGroup.addEventListener('mouseenter', () => {
                if (!favLoaded) {
                    loadMiniFavourites();
                    favLoaded = true;
                }
            });
        }

        let notifLoaded = false;
        if(notifGroup) {
            notifGroup.addEventListener('mouseenter', () => {
                if (!notifLoaded) {
                    loadNotifications();
                    notifLoaded = true;
                }
            });
        }
        <?php endif; ?>
    });
    </script>
</header>