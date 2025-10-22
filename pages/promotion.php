<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// session_start();
// include_once __DIR__ . '/../database/db_connection.php';
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

    <!-- Menu Section -->
    <section id="menu" class="py-20 bg-4B2E05 text-white parallax" style="background-image: url('../Photos/background.jpg'); background-attachment: fixed;">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold mb-4">Menu Nổi Bật</h2>
                <p class="text-lg">Khám phá những hương vị signature của chúng tôi</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white text-222222 rounded-xl shadow-lg p-6 card-hover">
                    <img src="../Photos/test1.jpg" alt="Espresso" class="w-full h-48 object-cover rounded-lg mb-4">
                    <h3 class="text-xl font-bold mb-2">Espresso Cổ Điển</h3>
                    <p class="text-sm">Vị đắng tinh tế, hương thơm nồng nàn.</p>
                </div>
                <div class="bg-white text-222222 rounded-xl shadow-lg p-6 card-hover">
                    <img src="../Photos/test2.jpg" alt="Cold Brew" class="w-full h-48 object-cover rounded-lg mb-4">
                    <h3 class="text-xl font-bold mb-2">Cold Brew</h3>
                    <p class="text-sm">Ngọt dịu, mát lạnh, pha chậm 24h.</p>
                </div>
                <div class="bg-white text-222222 rounded-xl shadow-lg p-6 card-hover">
                    <img src="../Photos/banner.jpg" alt="Cappuccino" class="w-full h-48 object-cover rounded-lg mb-4">
                    <h3 class="text-xl font-bold mb-2">Cappuccino Old Style</h3>
                    <p class="text-sm">Bọt sữa dày, vị cân bằng hoàn hảo.</p>
                </div>
            </div>
            <div class="text-center mt-8">
                <a href="../index.php#menu" class="cta-button text-white px-6 py-3 rounded-full font-semibold inline-block">Xem Toàn Bộ Menu</a>
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
            <div class="slider">
                <div class="slider-images">
                    <img src="../Photos/interior.jpg" alt="Không gian quán">
                    <img src="../Photos/login_background.jpg" alt="Góc ngồi">
                    <img src="../Photos/stories.jpg" alt="Khách hàng">
                </div>
            </div>
            <div class="flex justify-center mt-4">
                <button id="prevBtn" class="mx-2 px-4 py-2 bg-4B2E05 text-white rounded">Trước</button>
                <button id="nextBtn" class="mx-2 px-4 py-2 bg-4B2E05 text-white rounded">Sau</button>
            </div>
        </div>
    </section>

    <!-- Promotion Section -->
    <section id="promotion" class="py-20 bg-C4A35A text-222222 parallax" style="background-image: url('../Photos/test1.jpg'); background-attachment: fixed;">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold mb-4">Ưu Đãi Đặc Biệt</h2>
                <p class="text-lg">Đừng bỏ lỡ những combo hấp dẫn</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-xl font-bold mb-4">Combo Sáng Năng Lượng</h3>
                    <p class="mb-4">Cà phê + Bánh mặn - Giảm 20%</p>
                    <a href="#" class="cta-button text-white px-6 py-3 rounded-full font-semibold inline-block">Đặt Ngay</a>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-xl font-bold mb-4">Combo Trà & Bánh</h3>
                    <p class="mb-4">Trà đào + Bánh ngọt - Miễn phí giao hàng</p>
                    <a href="#" class="cta-button text-white px-6 py-3 rounded-full font-semibold inline-block">Đặt Ngay</a>
                </div>
            </div>
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
    <script>
        // Loading screen
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('loading').style.display = 'none';
            }, 3000);
        });

        // Slider
        let currentSlide = 0;
        const slides = document.querySelector('.slider-images');
        const totalSlides = slides.children.length;

        document.getElementById('nextBtn').addEventListener('click', () => {
            currentSlide = (currentSlide + 1) % totalSlides;
            updateSlide();
        });

        document.getElementById('prevBtn').addEventListener('click', () => {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            updateSlide();
        });

        function updateSlide() {
            slides.style.transform = `translateX(-${currentSlide * 100}%)`;
        }

        // Auto open popup after 5 seconds
        setTimeout(() => {
            document.getElementById('signupPopup').classList.add('show');
        }, 5000);

        // Parallax effect
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const parallaxElements = document.querySelectorAll('.parallax');
            parallaxElements.forEach(el => {
                const rate = scrolled * -0.5;
                el.style.transform = `translateY(${rate}px)`;
            });
        });
    </script>
</body>
</html>
