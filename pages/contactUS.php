<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên hệ - Old Favour Coffee</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <?php include '../includes/header.php'; ?>
    <main class="flex-1">
        <section class="max-w-4xl mx-auto mt-12 mb-8 text-center">
            <h1 class="text-3xl md:text-4xl font-bold text-pink-600 mb-2">Liên hệ với Old Favour Coffee</h1>
            <p class="text-lg text-gray-700 mb-4">Dù bạn là người yêu cà phê, kẻ mộng mơ hay chỉ đang tìm một góc yên tĩnh – chúng tôi luôn sẵn sàng lắng nghe bạn.</p>
        </section>
        <section class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-10 mb-16">
            <!-- Left: Info & Map -->
            <div class="flex flex-col gap-6 justify-between">
                <div class="bg-white rounded-2xl shadow p-6 mb-2">
                    <div class="mb-3">
                        <div class="font-bold text-lg text-pink-600 mb-1 flex items-center gap-2"><i class="fa fa-map-marker-alt"></i> Địa chỉ</div>
                        <div class="text-gray-700">Bạn tìm thấy chúng tôi ở góc phố thơm mùi cà phê – <span class="font-semibold">123 Đường Trà Sữa, Tây Ninh</span></div>
                    </div>
                    <div class="mb-3">
                        <div class="font-bold text-lg text-pink-600 mb-1 flex items-center gap-2"><i class="fa fa-phone-alt"></i> Số điện thoại</div>
                        <div class="text-gray-700">Gọi ngay để đặt bàn hoặc trò chuyện về cà phê – <span class="font-semibold">0909.xxx.xxx</span></div>
                    </div>
                    <div class="mb-3">
                        <div class="font-bold text-lg text-pink-600 mb-1 flex items-center gap-2"><i class="fa fa-envelope"></i> Email</div>
                        <div class="text-gray-700">Gửi lời nhắn yêu thương đến hòm thư của chúng tôi – <span class="font-semibold">info@oldfavourcoffee.com</span></div>
                    </div>
                    <div class="mb-3">
                        <div class="font-bold text-lg text-pink-600 mb-1 flex items-center gap-2"><i class="fa fa-clock"></i> Giờ hoạt động</div>
                        <div class="text-gray-700">Chúng tôi thức dậy cùng mặt trời và ngủ khi thành phố lên đèn – <span class="font-semibold">từ 7:00 đến 22:00 mỗi ngày</span></div>
                    </div>
                </div>
                <div class="rounded-2xl overflow-hidden shadow">
                    <img src="<?php echo $base_url; ?>/Photos/theoldfavour.jpg" alt="Không gian quán" class="w-full h-56 object-cover" />
                </div>
                <div class="bg-white rounded-2xl shadow p-4 flex flex-col gap-2 items-center">
                    <div class="font-semibold text-pink-600 mb-1">Kết nối mạng xã hội</div>
                    <div class="flex gap-4 text-2xl">
                        <a href="#" class="hover:text-pink-500" title="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="hover:text-pink-500" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="hover:text-pink-500" title="TikTok"><i class="fab fa-tiktok"></i></a>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Theo dõi hành trình cà phê của chúng tôi tại các mạng xã hội!</div>
                </div>
            </div>
            <!-- Right: Map & Contact Form -->
            <div class="flex flex-col gap-6">
                <div class="rounded-2xl overflow-hidden shadow mb-2">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d733.7707072456304!2d106.10837308802235!3d11.313987943066103!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x310b6b007c84119d%3A0x55dd0ed7ccb8a79!2sG%C3%B3c%20coffee!5e1!3m2!1svi!2s!4v1757658742472!5m2!1svi!2s" width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    <div class="flex justify-end mt-2">
                        <a href="https://goo.gl/maps/2Qw2Qw2Qw2Qw2Qw2Q" target="_blank" class="text-pink-600 hover:underline text-sm flex items-center gap-1"><i class="fa fa-location-arrow"></i> Tìm chúng tôi trên Google Maps</a>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow p-6">
                    <div class="font-bold text-lg text-pink-600 mb-2 flex items-center gap-2"><i class="fa fa-paper-plane"></i> Gửi liên hệ nhanh</div>
                    <form class="flex flex-col gap-4">
                        <input type="text" placeholder="Tên của bạn" class="rounded px-3 py-2 border border-gray-300 focus:border-pink-500 focus:ring-2 focus:ring-pink-100 outline-none text-sm" required />
                        <input type="email" placeholder="Email của bạn" class="rounded px-3 py-2 border border-gray-300 focus:border-pink-500 focus:ring-2 focus:ring-pink-100 outline-none text-sm" required />
                        <textarea placeholder="Bạn có điều gì muốn nói với chúng tôi? Đừng ngại ngần, hãy gửi vài dòng nhé!" class="rounded px-3 py-2 border border-gray-300 focus:border-pink-500 focus:ring-2 focus:ring-pink-100 outline-none text-sm resize-none" rows="4" required></textarea>
                        <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white font-semibold rounded px-4 py-2 transition">Gửi liên hệ</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
