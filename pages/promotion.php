<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include_once __DIR__ . '/../database/db_connection.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Old Flavour - Thời Gian Hòa Vị</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #E6D3B1;
            color: #222222;
            overflow-x: hidden;
        }
        h1, h2, h3 {
            font-family: 'Playfair Display', serif;
        }
        .hero-bg {
            background: linear-gradient(rgba(75, 46, 5, 0.7), rgba(75, 46, 5, 0.7)), url('../Photos/banner.jpg') center/cover no-repeat;
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-animation {
            animation: fadeInUp 2s ease-out;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .parallax {
            background-attachment: fixed;
            background-size: cover;
            background-position: center;
        }
        .fade-in {
            animation: fadeIn 1s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .section-bg {
            background-color: #E6D3B1;
        }
        .card-hover {
            transition: transform 0.3s ease, filter 0.3s ease;
        }
        .card-hover:hover {
            transform: scale(1.05);
            filter: sepia(20%);
        }
        .cta-button {
            background: linear-gradient(135deg, #4B2E05 0%, #C4A35A 100%);
            transition: all 0.3s ease;
        }
        .cta-button:hover {
            background: linear-gradient(135deg, #C4A35A 0%, #4B2E05 100%);
            transform: scale(1.05);
        }
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #4B2E05;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeOut 3s ease-out forwards;
        }
        @keyframes fadeOut {
            to { opacity: 0; visibility: hidden; }
        }
        .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }
        .popup.show {
            display: flex;
        }
        .slider {
            overflow: hidden;
            position: relative;
        }
        .slider-images {
            display: flex;
            transition: transform 0.5s ease;
        }
        .slider-images img {
            width: 100%;
            flex-shrink: 0;
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-screen" id="loading">
        <div class="text-center text-white">
            <h1 class="text-4xl font-bold mb-4">The Old Flavour</h1>
            <div class="animate-pulse">☕</div>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero-bg" id="hero">
        <div class="text-center text-white logo-animation">
            <h1 class="text-6xl md:text-8xl font-bold mb-4 drop-shadow-lg">The Old Flavour</h1>
            <p class="text-xl md:text-2xl mb-8 drop-shadow">Thời Gian Hòa Vị</p>
            <a href="#about" class="cta-button text-white px-8 py-4 rounded-full font-semibold inline-block">Khám Phá Hương Vị Cổ Điển</a>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 section-bg fade-in">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-4B2E05 mb-4">Câu Chuyện Của The Old Flavour</h2>
                <p class="text-lg text-222222">Nơi hơi thở cổ điển gặp công nghệ hiện đại. Cà phê là hành trình cảm xúc giữa xưa và nay.</p>
            </div>
            <div class="flex flex-col lg:flex-row items-center gap-8">
                <div class="lg:w-1/2">
                    <img src="../Photos/artisan.jpg" alt="Góc quán cổ điển" class="w-full h-80 object-cover rounded-xl shadow-lg">
                </div>
                <div class="lg:w-1/2">
                    <p class="text-222222 leading-relaxed mb-4">Từ những hạt cà phê rang thủ công đến tách espresso pha máy hiện đại, The Old Flavour mang đến trải nghiệm hòa quyện giữa truyền thống và đương đại.</p>
                    <blockquote class="text-C4A35A italic border-l-4 border-4B2E05 pl-4">“Một tách cà phê – một mảnh ký ức.”</blockquote>
                </div>
            </div>
        </div>
    </section>

    <!-- Menu Nổi Bật Section - Redesigned -->
    <section id="menu" class="py-20 section-bg">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-4B2E05 mb-4">Hương Vị Signature</h2>
                <p class="text-lg text-gray-700">Những tuyệt tác được yêu thích nhất tại The Old Flavour.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover">
                    <img src="../Photos/menus/caphe/ca-phe-muoi.jpg" alt="Cà Phê Muối" class="w-full h-56 object-cover">
                    <div class="p-6">
                        <h3 class="text-2xl font-bold text-4B2E05 mb-2">Cà Phê Muối</h3>
                        <p class="text-gray-600">Sự hòa quyện độc đáo giữa vị đắng cà phê và lớp kem muối béo ngậy, tạo nên một trải nghiệm khó quên.</p>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover">
                    <img src="../Photos/menus/trasua/tra-dao-cam-sa.jpg" alt="Trà Đào Cam Sả" class="w-full h-56 object-cover">
                    <div class="p-6">
                        <h3 class="text-2xl font-bold text-4B2E05 mb-2">Trà Đào Cam Sả</h3>
                        <p class="text-gray-600">Thức uống giải nhiệt hoàn hảo với vị ngọt của đào, chua nhẹ của cam và hương thơm thư giãn từ sả.</p>
                    </div>
                </div>
                <!-- Card 3 -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover">
                    <img src="../Photos/menus/ankem/tiramisu.jpg" alt="Bánh Tiramisu" class="w-full h-56 object-cover">
                    <div class="p-6">
                        <h3 class="text-2xl font-bold text-4B2E05 mb-2">Bánh Tiramisu</h3>
                        <p class="text-gray-600">Chiếc bánh kinh điển từ Ý với lớp kem mascarpone mềm mịn, xen kẽ vị cà phê và bột cacao đắng nhẹ.</p>
                    </div>
                </div>
            </div>
            <div class="text-center mt-8">
                <a href="../menus/menus.php" class="cta-button text-white px-8 py-4 rounded-full font-semibold inline-block">Khám Phá Toàn Bộ Menu</a>
            </div>
        </div>
    </section>

    <!-- Experience Section -->
    <section id="experience" class="py-20 section-bg fade-in">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-4B2E05 mb-4">Trải Nghiệm Không Gian</h2>
                <p class="text-lg text-222222">Bước vào thế giới của chúng tôi</p>
            </div>
            <div class="slider relative rounded-xl overflow-hidden shadow-lg" id="experience-slider">
                <div class="slider-images flex transition-transform duration-400 ease-in-out">
                    <img src="../Photos/interior.jpg" alt="Không gian quán" class="w-full flex-shrink-0">
                    <img src="../Photos/interior1.jpg" alt="Góc ngồi" class="w-full flex-shrink-0">
                    <img src="../Photos/stories.jpg" alt="Khách hàng" class="w-full flex-shrink-0">
                </div>
                <!-- Navigation Buttons -->
                <button id="prevBtn" class="absolute top-1/2 left-4 transform -translate-y-1/2 bg-white/50 hover:bg-white/80 text-4B2E05 w-10 h-10 rounded-full shadow-md transition flex items-center justify-center">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button id="nextBtn" class="absolute top-1/2 right-4 transform -translate-y-1/2 bg-white/50 hover:bg-white/80 text-4B2E05 w-10 h-10 rounded-full shadow-md transition flex items-center justify-center">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <!-- Dots -->
                <div id="slider-dots" class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2">
                </div>
            </div>
        </div>
    </section>

    <!-- Promotion Section - Redesigned -->
    <section id="promotion" class="py-20 section-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-4B2E05 mb-4">Ưu Đãi Tháng Này</h2>
                <p class="text-lg text-222222 max-w-3xl mx-auto">Những ưu đãi độc quyền chỉ có tại The Old Flavour, đừng bỏ lỡ cơ hội thưởng thức hương vị tuyệt hảo với giá tốt nhất.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                
                <!-- Promotion Card 1 -->
                <div class="bg-white rounded-lg shadow-xl overflow-hidden transform hover:-translate-y-2 transition-transform duration-300 ease-in-out group">
                    <div class="relative">
                        <img class="w-full h-56 object-cover" src="../Photos/menus/ankem/cheesecake-viet-quat.jpg" alt="Combo Cà phê và Bánh">
                        <div class="absolute top-0 right-0 bg-red-600 text-white text-sm font-bold px-3 py-1 m-4 rounded-md">-25%</div>
                    </div>
                    <div class="p-6 flex flex-col">
                        <h3 class="text-2xl font-bold text-4B2E05 mb-2">Combo Chiều Thu</h3>
                        <p class="text-gray-600 mb-4 flex-grow">Thưởng thức một ly Cà Phê Muối đậm đà cùng một miếng Bánh Cheesecake Việt Quất mềm mịn.</p>
                        <div class="border-t border-gray-200 pt-4 mt-auto">
                            <p class="text-sm text-gray-500 mb-3">Áp dụng cho đến hết tháng 10.</p>
                            <a href="../index.php#menu" class="cta-button text-white font-semibold px-6 py-3 rounded-full inline-block w-full text-center group-hover:shadow-lg">Khám Phá Ngay</a>
                        </div>
                    </div>
                </div>

                <!-- Promotion Card 2 -->
                <div class="bg-white rounded-lg shadow-xl overflow-hidden transform hover:-translate-y-2 transition-transform duration-300 ease-in-out group">
                    <div class="relative">
                        <img class="w-full h-56 object-cover" src="../Photos/menus/nuocdacbiet/soda-viet-quat-bac-ha.jpg" alt="Giờ Vàng">
                        <div class="absolute top-0 right-0 bg-yellow-500 text-gray-800 text-sm font-bold px-3 py-1 m-4 rounded-md">GIỜ VÀNG</div>
                    </div>
                    <div class="p-6 flex flex-col">
                        <h3 class="text-2xl font-bold text-4B2E05 mb-2">Mua 1 Tặng 1</h3>
                        <p class="text-gray-600 mb-4 flex-grow">Áp dụng cho các dòng Trà và Soda đặc biệt trong khung giờ vàng từ 14:00 đến 16:00 mỗi ngày.</p>
                        <div class="border-t border-gray-200 pt-4 mt-auto">
                            <p class="text-sm text-gray-500 mb-3">Áp dụng mỗi ngày trong tuần.</p>
                            <a href="../index.php#menu" class="cta-button text-white font-semibold px-6 py-3 rounded-full inline-block w-full text-center group-hover:shadow-lg">Xem Menu Nước</a>
                        </div>
                    </div>
                </div>

                <!-- Promotion Card 3 -->
                <div class="bg-white rounded-lg shadow-xl overflow-hidden transform hover:-translate-y-2 transition-transform duration-300 ease-in-out group">
                    <div class="relative">
                        <img class="w-full h-56 object-cover" src="../Photos/baemin.png" alt="Miễn Phí Vận Chuyển">
                        <div class="absolute top-0 right-0 bg-green-500 text-white text-sm font-bold px-3 py-1 m-4 rounded-md">FREESHIP</div>
                    </div>
                    <div class="p-6 flex flex-col">
                        <h3 class="text-2xl font-bold text-4B2E05 mb-2">Miễn Phí Giao Hàng</h3>
                        <p class="text-gray-600 mb-4 flex-grow">Miễn phí vận chuyển cho mọi đơn hàng từ 100.000đ trong bán kính 3km khi đặt qua website.</p>
                        <div class="border-t border-gray-200 pt-4 mt-auto">
                            <p class="text-sm text-gray-500 mb-3">Nhập mã: <span class="font-bold text-gray-700">OLDFLAVOUR</span></p>
                            <a href="../index.php" class="cta-button text-white font-semibold px-6 py-3 rounded-full inline-block w-full text-center group-hover:shadow-lg">Đặt Hàng Ngay</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Lucky Spinner Section -->
    <section id="lucky-spinner" class="py-20 bg-white parallax" style="background-image: url('../Photos/background.jpg');">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <h2 class="text-4xl md:text-5xl font-bold text-white drop-shadow-lg mb-4">Vòng Quay May Mắn</h2>
            <p class="text-lg md:text-xl text-white drop-shadow mb-8 max-w-2xl mx-auto">Thử vận may của bạn và nhận những phần quà hấp dẫn chỉ có tại The Old Flavour!</p>
            <a href="spinner/spinner.php" class="cta-button text-white text-xl px-10 py-4 rounded-full font-semibold inline-block transform hover:scale-110 transition-transform duration-300">
                <i class="fas fa-sync-alt fa-spin mr-2"></i>
                Tham gia ngay
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-4B2E05 text-white py-12">
        <div class="max-w-6xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">The Old Flavour</h3>
                    <p>Đánh thức ký ức, chạm đến hiện đại.</p>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Giờ Mở Cửa</h3>
                    <p>7:00 - 22:00 hàng ngày</p>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Theo Dõi Chúng Tôi</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-C4A35A hover:text-white"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-C4A35A hover:text-white"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-C4A35A hover:text-white"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
            </div>
            <div class="mt-8">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3724.0969!2d105.8342!3d21.0278!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjHCsDAxJzQwLjAiTiAxMDXCsDUwJzAzLjEiRQ!5e0!3m2!1sen!2s!4v1234567890" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </footer>

    <?php include '../includes/popup_signup.php'; ?>
    <?php include '../includes/scrollButton.php'; ?>
    <script>
        // Loading screen
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('loading').style.display = 'none';
            }, 3000);
        });

        // Slider
        const sliderContainer = document.getElementById('experience-slider');
        if (sliderContainer) {
            let currentSlide = 0;
            const slides = sliderContainer.querySelector('.slider-images');
            const totalSlides = slides.children.length;
            const dotsContainer = document.getElementById('slider-dots');
            let autoSlideInterval;

            // Create dots
            for (let i = 0; i < totalSlides; i++) {
                const dot = document.createElement('button');
                dot.classList.add('w-3', 'h-3', 'rounded-full', 'transition-colors');
                dot.addEventListener('click', () => {
                    currentSlide = i;
                    updateSlide();
                    resetAutoSlide();
                });
                dotsContainer.appendChild(dot);
            }
            const dots = dotsContainer.querySelectorAll('button');

            function updateSlide() {
                slides.style.transform = `translateX(-${currentSlide * 100}%)`;
                dots.forEach((dot, index) => {
                    dot.classList.toggle('bg-white', index === currentSlide);
                    dot.classList.toggle('bg-white/50', index !== currentSlide);
                });
            }

            function resetAutoSlide() {
                clearInterval(autoSlideInterval);
                autoSlideInterval = setInterval(() => {
                    currentSlide = (currentSlide + 1) % totalSlides;
                    updateSlide();
                }, 5000);
            }

            sliderContainer.querySelector('#nextBtn').addEventListener('click', () => { currentSlide = (currentSlide + 1) % totalSlides; updateSlide(); resetAutoSlide(); });
            sliderContainer.querySelector('#prevBtn').addEventListener('click', () => { currentSlide = (currentSlide - 1 + totalSlides) % totalSlides; updateSlide(); resetAutoSlide(); });
            
            updateSlide();
            resetAutoSlide();
        }

        // Auto open popup after 5 seconds
        setTimeout(() => {
            document.getElementById('signupPopup').classList.add('show');
        }, 5000);

        // Lắng nghe thông báo từ iframe của vòng quay
        window.addEventListener('message', function(event) {
            // Không cần kiểm tra origin nếu iframe cùng nguồn, nhưng đây là cách làm tốt
            // if (event.origin !== 'http://your-domain.com') return;

            const messageContainer = document.getElementById('spinner-message');
            if (event.data && (event.data.type === 'SPIN_SUCCESS' || event.data.type === 'SPIN_ERROR')) {
                messageContainer.textContent = event.data.message;
                messageContainer.style.display = 'block';

                if(event.data.type === 'SPIN_ERROR') {
                    messageContainer.classList.remove('bg-green-600/80');
                    messageContainer.classList.add('bg-red-600/80');
                }
            }
        });

    </script>
</body>
</html>
