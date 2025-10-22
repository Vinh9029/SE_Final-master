<!-- <?php
session_start();
include_once __DIR__ . '/database/db_connection.php';
?> -->
<style>
    footer {
        font-family: 'Merriweather', serif;
    }
    footer h1, footer h2, footer h3, footer h4, footer h5, footer h6 {
        font-family: 'Playfair Display', serif;
    }
</style>
<footer class="bg-gray-900 text-gray-200 pt-10 pb-4 mt-10">
    <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        <!-- Cột 1: Logo + slogan + liên hệ -->
        <div>
            <div class="flex items-center gap-2 mb-3">
                <img src="<?php echo $base_url; ?>/Photos/banner.jpg" alt="Logo" class="h-12 w-10 object-cover rounded-full shadow" />
                <span class="text-xl font-bold text-pink-400">Old Favour</span>
            </div>
            <div class="mb-2 text-pink-200 italic">Hạnh phúc trong từng tách cà phê!</div>
            <div class="text-sm flex flex-col gap-1">
                <span><i class="fa fa-map-marker-alt text-pink-400 mr-2"></i>123 Main St, Ho Chi Minh City</span>
                <span><i class="fa fa-phone-alt text-pink-400 mr-2"></i>(123) 456-7890</span>
                <span><i class="fa fa-envelope text-pink-400 mr-2"></i>info@oldfavourcoffee.com</span>
            </div>
        </div>
        <!-- Cột 2: Liên kết nhanh -->
        <div>
            <div class="font-semibold text-lg mb-2 text-pink-300">Liên kết nhanh</div>
            <ul class="space-y-1 text-sm">
                <li><a href="<?php echo $base_url; ?>/index.php" class="hover:text-pink-400 transition">Trang chủ</a></li>
                <li><a href="<?php echo $base_url; ?>/menus/menus.php" class="hover:text-pink-400 transition">Thực đơn</a></li>
                <li><a href="<?php echo $base_url; ?>/index.php" class="hover:text-pink-400 transition">Khuyến mãi</a></li>
                <li><a href="<?php echo $base_url; ?>/pages/contactUS.php" class="hover:text-pink-400 transition">Liên hệ</a></li>
                <li><a href="<?php echo $base_url; ?>/login/registerAccount.php" class="hover:text-pink-400 transition">Đăng ký</a></li>
            </ul>
        </div>
        <!-- Cột 3: Mạng xã hội + đối tác giao hàng -->
        <div>
            <div class="font-semibold text-lg mb-2 text-pink-300">Kết nối với chúng tôi</div>
            <div class="flex gap-3 mb-3">
                <a href="#" class="hover:text-pink-400 text-2xl"><i class="fab fa-facebook"></i></a>
                <a href="#" class="hover:text-pink-400 text-2xl"><i class="fab fa-instagram"></i></a>
                <a href="#" class="hover:text-pink-400 text-2xl"><i class="fab fa-twitter"></i></a>
            </div>
            <div class="font-semibold text-sm mb-1 text-pink-200">Đối tác giao hàng</div>
            <div class="flex gap-3 items-center">
                <img src="<?php echo $base_url; ?>/Photos/grab.jpg" alt="Grab" class="h-8 w-auto max-w-[60px] bg-white rounded p-1 object-contain shadow" />
                <img src="<?php echo $base_url; ?>/Photos/shopee_food.png" alt="ShopeeFood" class="h-8 w-auto max-w-[60px] bg-white rounded p-1 object-contain shadow" />
                <img src="<?php echo $base_url; ?>/Photos/baemin.png" alt="Baemin" class="h-8 w-auto max-w-[60px] bg-white rounded p-1 object-contain shadow" />
            </div>
        </div>
        <!-- Cột 4: Newsletter + bản đồ nhỏ -->
        <div>
            <div class="font-semibold text-lg mb-2 text-pink-300">Nhận ưu đãi & tin mới</div>
            <form class="flex mb-3">
                <input type="email" placeholder="Nhập email của bạn" class="rounded-l px-3 py-2 w-full text-gray-800 focus:outline-none" required />
                <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white px-4 rounded-r">Gửi</button>
            </form>
            <div class="font-semibold text-sm mb-1 text-pink-200">Địa chỉ quán</div>
            <iframe src="https://www.google.com/maps?q=10.762622,106.660172&z=15&output=embed" width="120%" height="200" style="border:0; border-radius:8px;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>
    <div class="text-center text-xs text-gray-400 mt-8">
        &copy; 2025 Old Favour Coffee. All rights reserved.
    </div>
</footer>
