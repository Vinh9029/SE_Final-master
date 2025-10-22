<?php
include_once __DIR__ . '/../includes/header.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Những Câu Chuyện Quanh Tách Cà Phê - Blog</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Merriweather:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Merriweather', serif;
            background: linear-gradient(180deg, #f7f0e8 0%, #fffaf7 100%);
            color: #333;
            line-height: 1.8;
            background-image: radial-gradient(circle at 20% 50%, rgba(194, 139, 88, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(139, 69, 19, 0.05) 0%, transparent 50%);
            background-attachment: fixed;
        }

        h1,
        h2,
        h3 {
            font-family: 'Playfair Display', serif;
            color: #C28B58;
        }

        h1:hover {
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .hero {
            background: linear-gradient(135deg, #d4a574 0%, #f4e1d2 100%);
            background-attachment: fixed;
            background-size: cover;
            color: #5d4037;
            padding: 5rem 2rem;
            text-align: center;
            border-radius: 0 0 3rem 3rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .section {
            max-width: 900px;
            margin: 4rem auto;
            padding: 2.5rem;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            opacity: 0;
            transform: translateY(50px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .section.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .article {
            margin-bottom: 3rem;
            padding: 2.5rem;
            background: linear-gradient(135deg, #fef7f0 0%, #fff 100%);
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            border-left: 5px solid #C28B58;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .article:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            background-color: #fff9f5;
        }

        .story {
            margin-bottom: 2rem;
            padding: 2rem;
            background: #f9f9f9;
            border-radius: 12px;
            border-left: 4px solid #8d6e63;
            opacity: 0;
            transform: translateX(-50px);
            transition: opacity 0.6s ease, transform 0.6s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .story.visible {
            opacity: 1;
            transform: translateX(0);
        }

        blockquote {
            border-left: 3px solid #C28B58;
            padding-left: 1rem;
            font-style: italic;
            color: #6b4a28;
            margin: 1rem 0;
        }

        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #C28B58, transparent);
            margin: 2rem 0;
        }

        .image-placeholder {
            width: 100%;
            height: 200px;
            background: #e8d5b7;
            border-radius: 8px;
            margin: 1rem 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8B4513;
            font-style: italic;
        }

        .signature {
            text-align: center;
            font-style: italic;
            color: #5d4037;
            margin-top: 3rem;
            padding: 2rem;
            background: rgba(255, 250, 247, 0.8);
            border-radius: 12px;
        }

        .related-posts {
            max-width: 900px;
            margin: 4rem auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .back-to-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: #C28B58;
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: background 0.3s ease;
        }

        .back-to-top:hover {
            background: #8B4513;
        }

        @keyframes steam {
            0% {
                opacity: 0;
                transform: translateY(0);
            }

            50% {
                opacity: 1;
                transform: translateY(-10px);
            }

            100% {
                opacity: 0;
                transform: translateY(-20px);
            }
        }

        .steam {
            position: relative;
        }

        .steam::after {
            content: '☕';
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            animation: steam 2s infinite;
            font-size: 1.5rem;
        }

        .content-flex {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .image-left {
            flex: 0 0 500px;
        }

        .text-right {
            flex: 1;
        }

        .image-right {
            flex: 0 0 500px;
        }

        .text-left {
            flex: 1;
        }

        .image-center {
            text-align: center;
            margin: 2rem 0;
        }

        .image-center img {
            max-width: 800px;
            height: auto;
            border-radius: 8px;
        }

        .story:nth-child(1) h3::before {
            content: '☕';
            margin-right: 0.5rem;
        }

        .story:nth-child(2) h3::before {
            content: '🌿';
            margin-right: 0.5rem;
        }

        .story:nth-child(3) h3::before {
            content: '💬';
            margin-right: 0.5rem;
        }
    </style>
</head>

<body>
    <main>
        <section class="hero">
            <img src="../Photos/blog_image/hero_image.jpg" alt="Tách cà phê buổi sớm" style="width: 100%; height: 500px; object-fit: cover; border-radius: 0 0 3rem 3rem;">
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
                <h1 class="steam">☕ Những Câu Chuyện Quanh Tách Cà Phê</h1>
                <p>Viết bởi: Minh — người kể chuyện bên ly cà phê nồng</p>
            </div>
        </section>

        <section class="section" id="intro">
            <div class="content-flex">
                <div class="image-left">
                    <img src="../Photos/blog_image/paragraph1.jpg" alt="Bên trái - Cảnh cà phê" width="500" height="400" style="width: 100%; height: auto; border-radius: 8px;">
                </div>
                <div class="text-right">
                    <h2>🌿 Lời Tựa: Khi Cuộc Đời Nhỏ Giọt Qua Tách Cà Phê</h2>
                    <p>Có những buổi sáng, tôi ngồi nơi góc nhỏ của quán quen, nghe tiếng thìa chạm vào tách sứ, và nhận ra – hình như cuộc đời cũng giống như một ly cà phê: phải chậm lại mới thấy được vị thật của nó.</p>
                    <p>Cà phê không chỉ là thức uống. Nó là một nghi thức của tâm hồn, là cách con người ngồi lại với chính mình, lắng nghe thời gian nhỏ giọt như hương thơm tan trong không khí.</p>
                    <p>Và giữa bao nhịp sống vội vàng, vẫn còn đó những người gìn giữ hương vị nguyên bản, những nghệ nhân không chỉ rang cà phê, mà còn rang lên những ký ức.</p>
                    <p>Tôi bắt đầu viết chuỗi “Những câu chuyện quanh tách cà phê” từ một buổi chiều mưa, khi mùi cà phê quyện với hơi đất ẩm khiến lòng người trở nên thật mềm. Trong từng bài viết, có những câu chuyện nhỏ nhí nhảnh — đôi khi là mẩu đối thoại dễ thương giữa khách và người pha, đôi khi chỉ là một ý nghĩ thoáng qua khi nhâm nhi tách trà.</p>
                    <p>Nếu bạn đang đọc những dòng này, có lẽ bạn cũng như tôi — một kẻ tin rằng, đôi khi chỉ cần một tách cà phê nóng, một góc nhỏ yên tĩnh và một câu chuyện kể nhẹ như khói bay... là đủ để thấy lòng mình ấm lại.</p>
                </div>
            </div>
        </section>

        <section class="section" id="article1">
            <div class="article">
                <div class="content-flex">
                    <div class="text-left">
                        <h2>☕ Bài 1: Hành Trình Của Hạt Cà Phê Nguyên Chất – Khi Nghệ Thuật Gặp Gỡ Tâm Hồn</h2>
                        <p>Người ta nói, mỗi hạt cà phê đều có linh hồn của riêng mình. Tôi tin điều đó — nhất là khi đứng giữa không gian ngập tràn mùi rang thơm nức, nghe tiếng lách tách của hạt nở bung trong lửa, và thấy bàn tay nghệ nhân lướt nhẹ trên từng mẻ cà phê như đang nâng niu kỷ niệm.</p>
                        <p>Hành trình của cà phê nguyên chất không bắt đầu ở quán, mà từ nơi cao nguyên gió hát. Những giọt sương sớm đọng trên lá, những người nông dân tỉ mẩn chọn từng quả đỏ mọng, rồi hong nắng trên sân phơi. Có lẽ, chính lúc ấy, hương vị chân thật nhất của cà phê đã được ươm mầm — giản dị, mộc mạc, nhưng sâu sắc như lòng người.</p>
                    </div>
                    <div class="image-right">
                        <img src="../Photos/blog_image/paragraph2.jpg" alt="Hạt cà phê nguyên chất và nghệ nhân" width="500" height="400" style="width: 100%; height: auto; border-radius: 8px;">
                    </div>
                </div>
                <p>Đến tay nghệ nhân rang xay, hạt cà phê lại bước vào một cuộc “hóa thân” mới. Mỗi người thợ là một người kể chuyện. Họ không chỉ rang cà phê, mà rang cả tâm hồn mình trong từng độ lửa, từng phút canh. Hơi khói quyện vào gió, thơm lừng một thứ mùi không thể nào quên — vừa mạnh mẽ, vừa dịu dàng, như bản tình ca giữa con người và thiên nhiên.</p>
                <blockquote>“Cà phê nguyên chất giống như người thật lòng – không cần thêm đường, thêm sữa vẫn đủ khiến ta say.”</blockquote>
                <p>Một người bạn của tôi từng bảo: “Cà phê nguyên chất giống như người thật lòng – không cần thêm đường, thêm sữa vẫn đủ khiến ta say.” Tôi mỉm cười, nâng tách cà phê lên, thấy trong vị đắng ấy là bao nồng nàn của đất, bao chân thành của người. Và thế là, giữa nhịp sống hối hả, một tách cà phê nguyên chất lại hóa thành nơi để ta tìm thấy chính mình — nguyên sơ, tinh khôi, và thật.</p>
            </div>
        </section>

        <section class="section" id="article2">
            <div class="article">
                <h2>🏛️ Bài 2: “The Old Flavour” – Khi Hương Xưa Ngồi Uống Cà Phê Cùng Hiện Đại</h2>
                <p>Có những buổi chiều tôi ghé lại “The Old Flavour”, quán cà phê nằm nép mình trong con phố nhỏ rêu phong. Cánh cửa gỗ cũ kỹ, bảng hiệu bạc màu thời gian, nhưng khi bước vào, ánh đèn vàng cùng tiếng nhạc jazz lại khiến lòng tôi như được đưa đi du hành qua hai thế giới.</p>
                <div class="image-center">
                    <img src="../Photos/blog_image/paragraph3.jpg" alt="The Old Flavour quán cà phê" width="800" height="400" style="width: 100%; height: auto; border-radius: 8px;">
                </div>
                <blockquote>“Làm sống lại hương vị xưa giữa lòng hiện đại.”</blockquote>
                <p>Chủ quán kể rằng, “The Old Flavour” ra đời từ một ý niệm giản dị: “Làm sống lại hương vị xưa giữa lòng hiện đại.” Ngày ấy, anh từng là một kiến trúc sư trẻ, thích lang thang quán cũ, mê những chiếc tách men sứ và cái cách người ta chậm rãi pha cà phê phin. Một ngày, anh nghĩ: Tại sao không tạo nên một nơi để người ta ngồi giữa hôm nay mà vẫn cảm được hơi thở hôm qua? Vậy là “The Old Flavour” ra đời — nơi cổ kính gặp gỡ hiện đại, nơi tường vôi xưa ôm trọn bàn gỗ mới, và nơi mỗi ly cà phê được pha như một nghi lễ của ký ức.</p>
                <p>Ngồi ở đây, bạn sẽ thấy người ta không chỉ đến để uống. Họ đến để nhớ. Để nói chuyện cùng quá khứ qua làn khói bay, để kể nhau nghe chuyện đời bằng giọng nhỏ nhẹ, như thể sợ làm vỡ mất không gian.</p>
            </div>
        </section>

        <section class="section" id="stories">
            <h2>☕ Những Mẩu Chuyện Nhỏ Quanh Bàn Cà Phê</h2>
            <div class="image-center">
                <img src="../Photos/blog_image/paragraph4.jpg" alt="Những mẩu chuyện nhỏ quanh bàn cà phê" width="800" height="400" style="width: 100%; height: auto; border-radius: 8px;">
            </div>
            <div class="story" id="story1">
                <h3>Câu chuyện thứ nhất</h3>
                <p>Tôi hỏi cô phục vụ nhỏ nhắn rằng: – Em nghĩ cà phê ngon nhất khi nào? Cô cười, đáp: – Khi người uống nó chưa kịp than “đắng quá” mà vẫn cười với người đối diện ạ. Tôi suýt sặc cà phê vì câu trả lời dễ thương quá mức cho phép.</p>
            </div>
            <div class="story" id="story2">
                <h3>Câu chuyện thứ hai</h3>
                <p>Một ông cụ tóc bạc ngồi góc quán ngày nào cũng gọi đúng một món: “Cà phê phin, nhưng đừng vội.” Hỏi ra mới biết, ông thích nhìn giọt cà phê rơi chậm – “Vì đời mà nhanh thì chẳng còn ai nhớ hương vị xưa.”</p>
            </div>
            <div class="story" id="story3">
                <h3>Câu chuyện thứ ba</h3>
                <p>Còn tôi, mỗi khi trời đổ mưa, lại lôi laptop ra “giả vờ làm việc” ở đây. Nhưng thật ra là đang ngắm mưa rơi, đếm từng hơi cà phê bốc khói, và nghĩ xem có nên rủ ai đó cùng nhâm nhi tách trà, nói chuyện linh tinh về giấc mơ và cuộc sống hay không…</p>
            </div>
            <p>“The Old Flavour” vẫn giữ được mình giữa bao quán cà phê mọc lên như nấm — như một bản nhạc cũ không lỗi thời. Chỉ cần một người nghe, một người hiểu, là đã đủ trọn vẹn.</p>
        </section>

        <section class="section" id="conclusion">
            <h2>🌙 Lời Kết: Khi Tách Cà Phê Cũng Biết Mỉm Cười</h2>
            <p>Và thế là, qua từng câu chuyện, từng hương vị, ta nhận ra — cà phê không chỉ là thức uống, mà là một người bạn đồng hành của thời gian.</p>
            <p>Nó lặng lẽ chứng kiến những buổi sáng ta bận rộn, những chiều ta thả hồn vào nỗi nhớ, và cả những đêm ta ngồi một mình, viết ra những điều chưa từng nói.</p>
            <p>Người nghệ nhân rang cà phê đã gửi gắm cả trái tim vào từng hạt nhỏ. Người chủ quán “The Old Flavour” gom góp ký ức để tạo nên không gian vừa xưa vừa mới. Còn tôi – chỉ là kẻ ngồi giữa hai làn hương ấy, lắng nghe thế giới thở chậm lại trong từng giọt đắng ngọt ngào.</p>
            <div class="image-center">
                <img src="../Photos/blog_image/ketbai.jpg" alt="Kết bài - Tách cà phê mỉm cười" width="800" height="400" style="width: 100%; height: auto; border-radius: 8px;">
            </div>
            <p>Đôi khi, cuộc sống chẳng cần quá nhiều điều lớn lao. Chỉ cần một tách cà phê nóng, một khung cửa sổ nhỏ nhìn ra phố, và một người để ta kể vài câu chuyện nhí nhỏm – như việc con mèo nhà hàng xóm thích nằm trên máy pha cà phê, hay ông cụ góc quán vẫn đợi ai đó mà chẳng hề vội.</p>
            <p>Rồi ta sẽ mỉm cười, vì hiểu rằng: “Cà phê ngon nhất không nằm ở hạt, ở phin, hay ở quán — mà ở khoảnh khắc ta thật lòng thưởng thức nó.”</p>
            <p>Nếu một ngày nào đó bạn thấy mùi cà phê len nhẹ vào buổi sớm, hãy nhớ rằng, đâu đó giữa dòng đời, vẫn có một người đang viết tiếp những câu chuyện quanh tách cà phê – và biết đâu, trong tách cà phê của bạn, câu chuyện kế tiếp lại đang bắt đầu…</p>
            <div class="signature">
                <p>☕ Ký tên: Minh – người kể chuyện giữa hương cà phê và những điều bình dị. <a href="https://instagram.com/oldfavour" target="_blank">📸 Instagram</a></p>
            </div>
        </section>

        <section class="related-posts">
            <h2>☕ Bài Viết Liên Quan</h2>
            <p>Nếu bạn thích hương cà phê nguyên chất, bạn có thể đọc tiếp:</p>
            <ul style="list-style: none; padding: 0;">
                <li><a href="<?php echo $base_url; ?>/pages/blog.php#article1" style="color: #C28B58; text-decoration: none;">• Hành Trình Của Hạt Cà Phê Nguyên Chất</a></li>
                <li><a href="<?php echo $base_url; ?>/pages/blog.php#article2" style="color: #C28B58; text-decoration: none;">• The Old Flavour – Hương Xưa & Hiện Đại</a></li>
                <li><a href="<?php echo $base_url; ?>/menus/menus.php" style="color: #C28B58; text-decoration: none;">• Khám Phá Menu Cà Phê Của Chúng Tôi</a></li>
            </ul>
        </section>
    </main>

    <button class="back-to-top" onclick="scrollToTop()">↑</button>

    <script>
        // Intersection Observer for scroll animations
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, {
            threshold: 0.1
        });

        document.querySelectorAll('.section, .story, .related-posts').forEach(el => {
            observer.observe(el);
        });

        // Smooth scrolling for navigation (if needed)
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Back to top function
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Show/hide back-to-top button
        window.addEventListener('scroll', function() {
            const backToTop = document.querySelector('.back-to-top');
            if (window.pageYOffset > 300) {
                backToTop.style.display = 'block';
            } else {
                backToTop.style.display = 'none';
            }
        });

        // Initially hide back-to-top button
        document.querySelector('.back-to-top').style.display = 'none';
    </script>

    <?php
    include_once __DIR__ . '/../includes/footer.php';
    ?>
</body>

</html>