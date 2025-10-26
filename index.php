<?php
include_once __DIR__ . "/config.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);
// session_start();
// include_once __DIR__ . '/database/db_connection.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Old Flavour Coffee</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            scroll-behavior: smooth;
            background-color: #E6D3B1;
        }

        h1,
        h2,
        h3 {
            font-family: 'Playfair Display', serif;
        }

        .hero-overlay {
            background: linear-gradient(135deg, rgba(75, 46, 5, 0.6) 0%, rgba(196, 163, 90, 0.4) 100%);
        }

        .section-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .section-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
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
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .cta-button {
            background: linear-gradient(135deg, #4B2E05 0%, #C4A35A 100%);
            transition: all 0.3s ease;
        }

        .cta-button:hover {
            background: linear-gradient(135deg, #C4A35A 0%, #4B2E05 100%);
            transform: scale(1.05);
        }

        .text-brown {
            color: #4B2E05;
        }

        .text-gold {
            color: #C4A35A;
        }

        .bg-beige {
            background-color: #E6D3B1;
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
            to {
                opacity: 0;
                visibility: hidden;
            }
        }

        .logo-animation {
            animation: fadeInUp 2s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }

        .popup.show {
            display: flex;
        }
    </style>

</head>

<body style="background-color: #E6D3B1;">
    <!-- Loading Screen -->
    <div class="loading-screen" id="loading">
        <div class="text-center text-white">
            <h1 class="text-4xl font-bold mb-4">Old Flavour Coffee</h1>
            <div class="animate-pulse">☕</div>
        </div>
    </div>
    <?php include 'includes/header.php'; ?>
    <!-- Body Content -->
    <main style="background-color: #E6D3B1;">
        <!-- Slider quảng cáo -->
        <div class="w-full max-w-5xl mx-auto mt-6 rounded-xl overflow-hidden shadow-lg relative">
            <div id="slider" class="relative h-64 md:h-80">
                <img src="<?php echo $base_url; ?>/Photos/banner.jpg" class="slider-img absolute inset-0 w-full h-full object-cover transition-opacity duration-700 opacity-100" style="z-index:2;" />
                <img src="<?php echo $base_url; ?>/Photos/test1.jpg" class="slider-img absolute inset-0 w-full h-full object-cover transition-opacity duration-700 opacity-0" style="z-index:1;" />
                <img src="<?php echo $base_url; ?>/Photos/test2.jpg" class="slider-img absolute inset-0 w-full h-full object-cover transition-opacity duration-700 opacity-0" style="z-index:0;" />
                <div class="hero-overlay absolute inset-0 z-5"></div>
                <div class="absolute inset-0 flex items-center justify-center z-10 text-center text-white logo-animation">
                    <div>
                        <h1 class="text-4xl md:text-6xl font-bold mb-4 drop-shadow-lg">Chào mừng đến Old Flavour</h1>
                        <p class="text-lg md:text-xl mb-6 drop-shadow">Hương vị cà phê nguyên bản, không gian ấm cúng</p>
                        <a href="#menu" class="cta-button text-white px-8 py-3 rounded-full font-semibold inline-block">Khám phá Menu</a>
                    </div>
                </div>
                <button onclick="prevSlide()" class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/70 hover:bg-brown-500 text-brown-600 hover:text-white rounded-full p-2 shadow z-20"><i class="fa fa-chevron-left"></i></button>
                <button onclick="nextSlide()" class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/70 hover:bg-brown-500 text-brown-600 hover:text-white rounded-full p-2 shadow z-20"><i class="fa fa-chevron-right"></i></button>
                <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-2 z-20">
                    <span class="slider-dot w-3 h-3 rounded-full bg-brown-500"></span>
                    <span class="slider-dot w-3 h-3 rounded-full bg-gray-300"></span>
                    <span class="slider-dot w-3 h-3 rounded-full bg-gray-300"></span>
                </div>
            </div>
        </div>
        <!-- Giới thiệu ngắn -->
        <section class="max-w-4xl mx-auto mt-10 text-center fade-in">
            <h2 class="text-2xl font-bold text-brown mb-2">Chào mừng đến với Old Flavour Coffee</h2>
            <p class="text-gray-700 text-lg">Nơi bạn tận hưởng hương vị cà phê nguyên bản, không gian ấm cúng và dịch vụ tận tâm.</p>
        </section>

        <!-- ☕ Câu chuyện thương hiệu -->
        <section class="max-w-6xl mx-auto mt-20 bg-white rounded-2xl p-8 shadow-lg section-card fade-in">
            <div class="flex flex-col lg:flex-row items-center gap-8">
                <div class="lg:w-1/2">
                    <img src="Photos/artisan.jpg" alt="Nghệ nhân rang xay" class="w-full h-80 object-cover rounded-xl shadow-md transition-transform duration-300 hover:scale-105">
                </div>
                <div class="lg:w-1/2 text-center lg:text-left">
                    <h2 class="text-4xl font-bold text-brown-800 mb-4">☕ Câu chuyện thương hiệu</h2>
                    <h3 class="text-xl font-semibold text-gold mb-4">Hành trình từ hạt cà phê đến tách hương thuần khiết</h3>
                    <p class="text-gray-700 leading-relaxed mb-4">Ở Old Flavour, mỗi hạt cà phê không chỉ là nông sản — mà là một hành trình. Hành trình bắt đầu từ sườn đồi ngập nắng, nơi người nông dân nâng niu từng chùm quả chín đỏ, rồi qua bàn tay người nghệ nhân – những người không chỉ rang cà phê, mà rang cả tâm hồn mình trong từng mẻ.</p>
                    <p class="text-gray-700 leading-relaxed mb-4">Hương thơm lan tỏa, âm vang tiếng hạt vỡ giòn tan, như nhịp thở của thời gian. Và rồi, khi tách cà phê đầu tiên chạm môi, ta thấy vị đắng dịu hòa cùng ngọt ngào – giản đơn, chân thật, và nguyên sơ như chính cái tên Old Flavour.</p>
                    <blockquote class="text-brown-600 italic border-l-4 border-brown-400 pl-4 mb-6">Từng hạt cà phê được lựa chọn tỉ mỉ, qua bàn tay nghệ nhân để tạo nên hương vị nguyên bản của Old Flavour.</blockquote>
                    <a href="pages/aboutUs.php" class="cta-button text-white px-6 py-3 rounded-full font-semibold inline-block">Tìm hiểu thêm</a>
                </div>
            </div>
        </section>

        <!-- 🪑 Không gian quán -->
        <section class="max-w-6xl mx-auto mt-16 bg-white rounded-2xl p-8 shadow-lg section-card fade-in">
            <div class="flex flex-col lg:flex-row-reverse items-center gap-8">
                <div class="lg:w-1/2">
                    <img src="<?php echo $base_url; ?>/Photos/interior.jpg" alt="Nội thất quán" class="w-full h-80 object-cover rounded-xl shadow-md transition-transform duration-300 hover:scale-105">
                </div>
                <div class="lg:w-1/2 text-center lg:text-left">
                    <h2 class="text-4xl font-bold text-brown-800 mb-4">🪑 Không gian quán</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">Bước chân vào Old Flavour, ta như lạc vào một góc nhỏ giữa hai thế giới – nơi cổ điển và hiện đại nắm tay nhau, cùng kể chuyện. Ánh đèn vàng rót xuống mặt bàn gỗ, tiếng nhạc jazz khe khẽ tan trong hương cà phê mới pha.</p>
                    <p class="text-gray-700 leading-relaxed mb-4">Mỗi chiếc ghế, mỗi khung cửa, mỗi vệt sáng đều có câu chuyện riêng — về những buổi hẹn hò đầu, những trang sách dở, hay chỉ là khoảnh khắc ai đó ngồi lặng nhìn mưa qua ô cửa kính. Quán không quá lớn, nhưng đủ rộng cho tâm hồn ta trú ngụ.</p>
                    <blockquote class="text-brown-600 italic border-l-4 border-brown-400 pl-4 mb-6">Một góc nhỏ cổ kính, nơi mỗi chiếc ghế, mỗi tách trà đều có câu chuyện riêng.</blockquote>
                    <a href="pages/contact.php" class="cta-button text-white px-6 py-3 rounded-full font-semibold inline-block">Ghé thăm quán</a>
                </div>
            </div>
        </section>

        <!-- 🕯️ Lời chào từ người sáng lập -->
        <section class="max-w-4xl mx-auto mt-16 bg-white rounded-2xl p-8 shadow-lg text-center section-card fade-in">
            <h2 class="text-4xl font-bold text-brown-800 mb-6">🕯️ Lời chào từ người sáng lập</h2>
            <div class="text-gray-700 leading-relaxed mb-6">
                <p class="mb-4">Chào bạn,</p>
                <p class="mb-4">Tôi là người đã tạo nên Old Flavour – không phải để mở một quán cà phê, mà để giữ lại một hương vị tưởng chừng đã trôi xa.</p>
                <p class="mb-4">Tôi vẫn nhớ buổi sáng đầu tiên rang thử mẻ cà phê trên chiếc chảo gang cũ của ông ngoại. Căn bếp ngập khói, mùi cà phê cháy nhẹ quyện trong tiếng chim ngoài hiên. Khi ấy, tôi nhận ra: cà phê không chỉ là thức uống – nó là ký ức, là câu chuyện, là nhịp thở chậm rãi giữa cuộc sống quá nhanh.</p>
                <p class="mb-4">Và Old Flavour ra đời từ khoảnh khắc ấy — để mỗi người khi ghé lại đều có thể tìm thấy một phần ký ức của mình trong hương vị xưa.</p>
                <p class="text-brown-600 font-semibold mb-6">— Người kể chuyện qua những giọt cà phê.</p>
                <a href="pages/aboutUs.php" class="cta-button text-white px-6 py-3 rounded-full font-semibold inline-block">Gặp gỡ người sáng lập</a>
            </div>
        </section>

        <!-- 💬 Câu chuyện nhỏ -->
        <section class="max-w-6xl mx-auto mt-16 bg-white rounded-2xl p-8 shadow-lg section-card fade-in">
            <div class="text-center mb-8">
                <h2 class="text-4xl font-bold text-cyan-800 mb-4">💬 Câu chuyện nhỏ</h2>
                <img src="<?php echo $base_url; ?>/Photos/stories.jpg" alt="Khách hàng vui vẻ" class="w-full max-w-md h-64 object-cover rounded-xl shadow-md mx-auto mb-6 transition-transform duration-300 hover:scale-105">
            </div>
            <div class="space-y-8">
                <div class="bg-white rounded-xl p-6 shadow-md">
                    <p class="text-gray-700 leading-relaxed mb-4">“Có những buổi chiều, tôi ngồi một mình, tách trà bốc khói trước mặt. Mưa gõ nhịp đều trên mái tôn cũ, và tôi nghe trong gió là mùi cà phê quen thuộc – nồng nàn, ấm áp.</p>
                    <p class="text-gray-700 leading-relaxed mb-4">Ở Old Flavour, dường như thời gian chẳng còn vội nữa. Người ta đến, nói đôi ba câu, rồi lặng im mà vẫn thấy hiểu nhau.</p>
                    <p class="text-gray-700 leading-relaxed">Thế là đủ — để một ngày trở nên dịu dàng hơn một chút.” ☂️</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-md">
                    <h3 class="text-xl font-semibold text-cyan-700 mb-4">🌧️ Tách cà phê và buổi sáng lười biếng</h3>
                    <p class="text-gray-700 leading-relaxed mb-4">Có những sáng, tôi chẳng muốn làm gì cả. Chỉ ngồi yên ở góc quen của quán, nhìn nắng rơi lấp lánh trên mặt bàn gỗ.</p>
                    <p class="text-gray-700 leading-relaxed mb-4">Anh pha chế mỉm cười, đặt trước mặt tôi một tách cà phê đen sóng sánh. “Hôm nay cô không vội, đúng không?” – anh hỏi.</p>
                    <p class="text-gray-700 leading-relaxed">Tôi chỉ khẽ gật đầu, hít một hơi dài. Hương cà phê len qua làn tóc, chạm nhẹ vào tâm trí – như thể có ai đó vừa kéo thời gian chậm lại một nhịp.</p>
                    <p class="text-gray-700 leading-relaxed">Ở Old Flavour, đôi khi “không làm gì cả” cũng là một cách để yêu cuộc sống hơn một chút. ☀️</p>
                </div>
            </div>
            <div class="text-center mt-8">
                <a href="pages/blogs/index.php" class="cta-button text-white px-6 py-3 rounded-full font-semibold inline-block">Đọc thêm câu chuyện</a>
            </div>
        </section>

        <!-- Đánh giá khách hàng -->
        <section class="max-w-5xl mx-auto mt-12 bg-white rounded-2xl p-8 shadow-lg section-card fade-in">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Khách hàng nói gì về chúng tôi?</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6" id="reviewContainer">
                <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center transition-transform duration-300 hover:scale-105 hover:bg-gray-50 review-card" data-index="0">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" class="w-16 h-16 object-cover rounded-full mb-2">
                    <div class="font-semibold">Nguyễn Văn A</div>
                    <div class="text-gray-500 text-sm text-center">“Không gian rất chill, cà phê ngon, nhân viên thân thiện!”</div>
                </div>
                <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center transition-transform duration-300 hover:scale-105 hover:bg-gray-50 review-card" data-index="1">
                    <img src="https://randomuser.me/api/portraits/women/44.jpg" class="w-16 h-16 object-cover rounded-full mb-2">
                    <div class="font-semibold">Trần Thị B</div>
                    <div class="text-gray-500 text-sm text-center">“Bánh ngọt mềm, trà đào thơm, giá hợp lý!”</div>
                </div>
                <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center transition-transform duration-300 hover:scale-105 hover:bg-gray-50 review-card" data-index="2">
                    <img src="https://randomuser.me/api/portraits/men/65.jpg" class="w-16 h-16 object-cover rounded-full mb-2">
                    <div class="font-semibold">Lê Minh C</div>
                    <div class="text-gray-500 text-sm text-center">“Combo bữa sáng tiện lợi, rất thích!”</div>
                </div>
                <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center transition-transform duration-300 hover:scale-105 hover:bg-gray-50 review-card hidden" data-index="3">
                    <img src="https://randomuser.me/api/portraits/women/18.jpg" class="w-16 h-16 object-cover rounded-full mb-2">
                    <div class="font-semibold">Phạm Thị D</div>
                    <div class="text-gray-500 text-sm text-center">“Cà phê đậm đà, không gian yên tĩnh lý tưởng để làm việc.”</div>
                </div>
                <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center transition-transform duration-300 hover:scale-105 hover:bg-gray-50 review-card hidden" data-index="4">
                    <img src="https://randomuser.me/api/portraits/men/21.jpg" class="w-16 h-16 object-cover rounded-full mb-2">
                    <div class="font-semibold">Hoàng Văn E</div>
                    <div class="text-gray-500 text-sm text-center">“Dịch vụ nhanh chóng, giá cả phải chăng, sẽ quay lại!”</div>
                </div>
                <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center transition-transform duration-300 hover:scale-105 hover:bg-gray-50 review-card hidden" data-index="5">
                    <img src="https://randomuser.me/api/portraits/women/67.jpg" class="w-16 h-16 object-cover rounded-full mb-2">
                    <div class="font-semibold">Vũ Thị F</div>
                    <div class="text-gray-500 text-sm text-center">“Trà sữa ngon, topping tươi, nhân viên nhiệt tình.”</div>
                </div>
                <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center transition-transform duration-300 hover:scale-105 hover:bg-gray-50 review-card hidden" data-index="6">
                    <img src="https://randomuser.me/api/portraits/men/88.jpg" class="w-16 h-16 object-cover rounded-full mb-2">
                    <div class="font-semibold">Đặng Minh G</div>
                    <div class="text-gray-500 text-sm text-center">“Món latte art đẹp mắt, hương vị tuyệt vời.”</div>
                </div>
            </div>
            <!-- Điều hướng -->
            <div class="flex justify-between items-center mt-6">
                <button id="prevReview" class="bg-white hover:bg-gray-100 text-brown rounded-full p-2 shadow"><i class="fa fa-chevron-left"></i></button>
                <div class="flex gap-2">
                    <span class="review-dot w-3 h-3 rounded-full bg-gold cursor-pointer"></span>
                    <span class="review-dot w-3 h-3 rounded-full bg-gray-300 cursor-pointer"></span>
                    <span class="review-dot w-3 h-3 rounded-full bg-gray-300 cursor-pointer"></span>
                </div>
                <button id="nextReview" class="bg-white hover:bg-gray-100 text-brown rounded-full p-2 shadow"><i class="fa fa-chevron-right"></i></button>
            </div>
            <style>
                .fade-up {
                    animation: fadeUp 1s cubic-bezier(.4, 0, .2, 1);
                }

                @keyframes fadeUp {
                    from {
                        opacity: 0;
                        transform: translateY(30px);
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
            </style>
        </section>
        <script>
            // Review carousel logic
            const reviewCards = document.querySelectorAll('.review-card');
            const dots = document.querySelectorAll('.review-dot');
            let current = 0;
            let timer;
            const sets = [
                [0, 1, 2],
                [3, 4, 5],
                [4, 5, 6]
            ];

            function showReviews(idx) {
                current = idx;
                reviewCards.forEach((card, i) => {
                    if (sets[idx].includes(i)) {
                        card.classList.remove('hidden');
                    } else {
                        card.classList.add('hidden');
                    }
                });
                dots.forEach((dot, i) => {
                    dot.className = 'review-dot w-3 h-3 rounded-full cursor-pointer ' + (i === idx ? 'bg-gold' : 'bg-gray-300');
                });
            }

            function nextReview() {
                current = (current + 1) % sets.length;
                showReviews(current);
            }

            function prevReview() {
                current = (current - 1 + sets.length) % sets.length;
                showReviews(current);
            }
            dots.forEach((dot, i) => {
                dot.onclick = () => showReviews(i);
            });
            document.getElementById('nextReview').onclick = nextReview;
            document.getElementById('prevReview').onclick = prevReview;

            function startAutoPlay() {
                timer = setInterval(nextReview, 5000);
            }

            function stopAutoPlay() {
                clearInterval(timer);
            }
            document.getElementById('reviewContainer').addEventListener('mouseenter', stopAutoPlay);
            document.getElementById('reviewContainer').addEventListener('mouseleave', startAutoPlay);
            showReviews(0);
            startAutoPlay();
        </script>
        <?php include 'includes/chat-zalo.php'; ?>
    </main>
    <?php include 'includes/popup_signup.php'; ?>
    <?php include 'includes/scrollButton.php'; ?>
    <!-- Scroll to Top Button -->
    <button id="scrollTopBtn" class="fixed bottom-5 right-5 bg-white text-brown-500 border border-brown-500 p-4 rounded-full shadow-lg opacity-0 transition-opacity duration-300 hover:bg-brown-500 hover:text-white">
        <i class="fas fa-arrow-up"></i>
    </button>
    <?php include 'includes/footer.php'; ?>
    <script>
        // Dropdown for menu
        function toggleDropdown(id) {
            var el = document.getElementById(id);
            if (el.classList.contains('opacity-0')) {
                el.classList.remove('opacity-0', 'invisible');
                el.classList.add('opacity-100', 'visible');
            } else {
                el.classList.remove('opacity-100', 'visible');
                el.classList.add('opacity-0', 'invisible');
            }
        }
        // Close dropdown when clicking outside
        window.addEventListener('click', function(e) {
            var menu = document.getElementById('menuDropdown');
            if (menu && !menu.contains(e.target) && !e.target.closest('[onclick*="toggleDropdown"]')) {
                menu.classList.remove('opacity-100', 'visible');
                menu.classList.add('opacity-0', 'invisible');
            }
        });
        // Slider JS
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slider-img');
        const dots = document.querySelectorAll('.slider-dot');

        function showSlide(idx) {
            slides.forEach((img, i) => {
                img.style.opacity = i === idx ? '1' : '0';
            });
            dots.forEach((dot, i) => {
                dot.className = 'slider-dot w-3 h-3 rounded-full ' + (i === idx ? 'bg-pink-500' : 'bg-gray-300');
            });
            currentSlide = idx;
        }

        function nextSlide() {
            let idx = (currentSlide + 1) % slides.length;
            showSlide(idx);
        }

        function prevSlide() {
            let idx = (currentSlide - 1 + slides.length) % slides.length;
            showSlide(idx);
        }
        document.querySelectorAll('.slider-dot').forEach((dot, i) => {
            dot.onclick = () => showSlide(i);
        });
        setInterval(nextSlide, 5000);
        showSlide(0);
        window.addEventListener('message', function(event) {
            // Chỉ nhận message từ spinner.php
            if (event.data && event.data.type === 'spinner-result') {
                var msgBox = document.getElementById('spinner-message');
                msgBox.textContent = 'Chúc mừng! Bạn đã nhận được: ' + event.data.prize;
                msgBox.style.display = 'block';
                setTimeout(function() {
                    msgBox.style.display = 'none';
                }, 4000);
            }
        });
        // Loading screen
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('loading').style.display = 'none';
            }, 3000);
        });


        // Scroll to top button
        const scrollTopBtn = document.getElementById('scrollTopBtn');
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 100) {
                scrollTopBtn.classList.remove('opacity-0');
                scrollTopBtn.classList.add('opacity-100');
            } else {
                scrollTopBtn.classList.remove('opacity-100');
                scrollTopBtn.classList.add('opacity-0');
            }
        });
        scrollTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Auto open popup after 5 seconds
        setTimeout(() => {
            document.getElementById('signupPopup').classList.add('show');
        }, 5000);
    </script>
</body>

</html>