<?php
// session_start();
// include_once __DIR__ . '/database/db_connection.php';
include_once __DIR__ . "/../config.php"; 
?>
<section id="promotion" class="max-w-6xl mx-auto mt-12 px-2 py-12 rounded-3xl" style="background: linear-gradient(135deg, #fff5f8 0%, #f9f9f9 100%);">
    <!-- Banner lớn đầu trang -->
    <section class="w-full bg-gradient-to-r from-pink-500 to-yellow-300 py-10 mb-8 rounded-b-3xl shadow-xl">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4 flex items-center justify-center gap-3 drop-shadow-lg">
                <span>Tháng 9 – Ưu đãi cho học sinh, sinh viên</span>
                <span class="bg-white text-pink-500 px-6 py-2 rounded-full font-bold text-2xl shadow-lg border-2 border-pink-300">-15%</span>
            </h1>
            <div class="text-white text-lg font-semibold drop-shadow">Áp dụng từ 20/9 – 30/9</div>
        </div>
    </section>
    <h3 class="text-2xl font-extrabold text-pink-600 mb-8 text-center">Chương trình khuyến mãi nổi bật</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-10 items-end">
        <!-- Card 1 -->
        <div class="bg-white rounded-3xl shadow-2xl p-8 flex flex-col items-center relative hover:scale-105 hover:shadow-yellow-300 transition duration-200 border-2 border-yellow-300" style="z-index:1;">
            <span class="absolute top-6 left-6 bg-gradient-to-r from-yellow-400 to-yellow-200 text-yellow-900 text-xs font-extrabold px-4 py-1 rounded-full shadow border border-yellow-400">🎁 Combo</span>
            <img src="Photos/test1.jpg" alt="Combo" class="w-28 h-28 object-cover rounded-2xl mb-4 shadow border-2 border-yellow-300" />
            <div class="font-extrabold text-xl text-yellow-700 mb-2 text-center">Cà phê + bánh ngọt</div>
            <div class="text-gray-800 font-semibold mb-2 text-center">Tiết kiệm hơn khi mua combo!</div>
            <div class="text-sm text-orange-700 font-bold mb-3">Áp dụng từ 20/9 – 30/9</div>
            <a href="#" class="btn-orange hover:bg-orange-600 text-white px-6 py-2 rounded-xl font-extrabold text-base shadow transition mb-2">Đặt món</a>
            <a href="promotion.php" class="bg-yellow-400 hover:bg-yellow-500 text-yellow-900 font-extrabold px-5 py-2 rounded-xl shadow border border-yellow-500 transition">Xem chi tiết</a>
        </div>
        <!-- Card 2 (nổi bật) -->
        <div class="bg-white rounded-3xl shadow-2xl p-10 flex flex-col items-center relative hover:scale-105 hover:shadow-green-300 transition duration-200 border-4 border-green-400 animate-pulse-slow" style="transform: translateY(-15px); z-index:2;">
            <span class="absolute top-6 left-6 bg-gradient-to-r from-green-400 to-green-200 text-white text-xs font-bold px-4 py-1 rounded-full shadow">🎉 Birthday</span>
            <span class="absolute top-6 right-6 bg-pink-500 text-white text-xs font-bold px-4 py-1 rounded-full shadow">🔥 Hot Deal</span>
            <img src="<?php echo $base_url; ?>/Photos/banner.jpg" alt="Birthday" class="w-32 h-32 object-cover rounded-2xl mb-4 shadow border-2 border-green-200 animate-bounce-slow" />
            <div class="font-extrabold text-2xl text-green-700 mb-2 text-center">Giảm giá sinh nhật khách hàng</div>
            <div class="text-gray-700 mb-2 text-center">Ưu đãi đặc biệt trong tháng sinh nhật!</div>
            <div class="text-sm text-orange-600 font-bold mb-3">Áp dụng toàn năm</div>
            <a href="#" class="btn-orange hover:bg-orange-600 text-white px-7 py-2 rounded-xl font-extrabold text-base shadow transition mb-2">Đặt món</a>
            <a href="promotion.php" class="bg-green-400 hover:bg-green-500 text-white font-extrabold px-5 py-2 rounded-xl shadow border border-green-500 transition">Xem chi tiết</a>
        </div>
        <!-- Card 3 -->
        <div class="bg-white rounded-3xl shadow-2xl p-8 flex flex-col items-center relative hover:scale-105 hover:shadow-gray-500 transition duration-200 border-2 border-green-700" style="z-index:1;">
            <span class="absolute top-6 left-6 bg-gradient-to-r from-orange-500 to-red-400 text-orange-900 text-xs font-extrabold px-4 py-1 rounded-full shadow border border-orange-500">🔥 Flash Sale</span>
            <img src="Photos/banner.jpg" alt="Flash Sale" class="w-28 h-28 object-cover rounded-2xl mb-4 shadow border-2 border-gray-700" />
            <div class="font-extrabold text-xl text-gray-800 mb-2 text-center">Giảm giá theo khung giờ</div>
            <div class="text-gray-800 font-semibold mb-2 text-center">8h–11h sáng, giảm đến 30%</div>
            <div class="text-sm text-orange-700 font-bold mb-3">Chỉ áp dụng hôm nay</div>
            <a href="#" class="btn-orange hover:bg-orange-600 text-white px-6 py-2 rounded-xl font-extrabold text-base shadow transition mb-2">Đặt món</a>
            <a href="promotion.php" class="bg-gray-700 hover:bg-gray-900 text-white font-extrabold px-5 py-2 rounded-xl shadow border border-gray-700 transition">Xem chi tiết</a>
        </div>
    </div>
</section>
<style>
@keyframes pulse-slow {
  0%, 100% { box-shadow: 0 0 0 0 #34d39944; }
  50% { box-shadow: 0 0 24px 4px #34d39988; }
}
.animate-pulse-slow { animation: pulse-slow 2.5s infinite; }
@keyframes bounce-slow {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}
.animate-bounce-slow { animation: bounce-slow 2.5s infinite; }
</style>
