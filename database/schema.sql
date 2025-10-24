-- Active: 1758856835071@@localhost@3306@theoldflavour
-- Database schema for SE_Final-Cart-Checkout

DROP DATABASE IF EXISTS theoldflavour;
CREATE DATABASE theoldflavour;   
USE theoldflavour;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    avatar_image VARCHAR(255), 
    address VARCHAR(255),
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    is_signature BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

CREATE TABLE promotions (
    promotion_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    discount_percent DECIMAL(5,2),
    start_date DATE,
    end_date DATE
);

CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    size_id INT NULL,
    take_note VARCHAR(255) NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE cart_items (
    cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    quantity INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE loyalty_points (
    point_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    points INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Thêm kích thước cho các sản phẩm đồ uống
CREATE TABLE product_sizes (
    size_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    size_name VARCHAR(50),   -- S, M, L, XL, hoặc Small/Medium/Large/Family...
    volume VARCHAR(50),      -- ví dụ: '350ml', '500ml', '700ml', '1L', hoặc '200g'
    extra_price DECIMAL(10,2) DEFAULT 0,  -- giá cộng thêm so với base price
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

--admin account (username: admin, password: 123)

CREATE TABLE vouchers (
    voucher_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_percent INT DEFAULT 10,
    program_name VARCHAR(100),
    min_order_value DECIMAL(10,2) DEFAULT 0,
    status ENUM('active', 'used', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);


ALTER TABLE users ADD COLUMN date_of_birth DATE NULL;
ALTER TABLE users ADD COLUMN gender VARCHAR(10) DEFAULT NULL;

CREATE TABLE spin_history (
    spin_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    prize VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

ALTER TABLE vouchers
ADD COLUMN spin_id INT NULL,
ADD CONSTRAINT fk_voucher_spin
    FOREIGN KEY (spin_id) REFERENCES spin_history(spin_id)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

-- Sửa lại bảng vouchers cho phép user_id NULL
ALTER TABLE vouchers MODIFY COLUMN user_id INT NULL;
-- Thêm cột size_id vào bảng cart_items
ALTER TABLE cart_items ADD COLUMN size_id INT NULL AFTER product_id;

ALTER TABLE cart_items ADD COLUMN take_note VARCHAR(255)  AFTER size_id;

ALTER TABLE orders ADD COLUMN voucher_code VARCHAR(50) DEFAULT NULL AFTER total;

ALTER TABLE orders ADD COLUMN discount_amount DECIMAL(10, 2) DEFAULT 0 AFTER voucher_code;

-- Bảng cho chức năng Blog
CREATE TABLE blogs (
    blog_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    cover_image VARCHAR(255) NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Thêm dữ liệu blog mẫu từ trang blog.php hiện tại
-- Xóa dữ liệu cũ nếu có để tránh trùng lặp slug
DELETE FROM `blogs` WHERE `slug` = 'nhung-cau-chuyen-quanh-tach-ca-phe';

INSERT INTO `blogs` (`user_id`, `title`, `content`, `slug`, `status`, `cover_image`) VALUES
(1, 'Những Câu Chuyện Quanh Tách Cà Phê', '<section class="hero">
            <div style="position: relative; text-align: center; color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
                <h1 class="steam text-5xl font-bold mb-4" style="color: white;">☕ Những Câu Chuyện Quanh Tách Cà Phê</h1>
                <p>Viết bởi: Minh — người kể chuyện bên ly cà phê nồng</p>
            </div>
        </section>

        <section class="section" id="intro">
            <div class="content-flex flex-col md:flex-row items-center gap-8">
                <div class="image-left md:w-1/2">
                    <img src="../Photos/blog_image/paragraph1.jpg" alt="Bên trái - Cảnh cà phê" class="w-full h-auto rounded-lg shadow-md">
                </div>
                <div class="text-right md:w-1/2">
                    <h2 class="text-3xl font-bold mb-4">🌿 Lời Tựa: Khi Cuộc Đời Nhỏ Giọt Qua Tách Cà Phê</h2>
                    <p class="mb-4">Có những buổi sáng, tôi ngồi nơi góc nhỏ của quán quen, nghe tiếng thìa chạm vào tách sứ, và nhận ra – hình như cuộc đời cũng giống như một ly cà phê: phải chậm lại mới thấy được vị thật của nó.</p>
                    <p class="mb-4">Cà phê không chỉ là thức uống. Nó là một nghi thức của tâm hồn, là cách con người ngồi lại với chính mình, lắng nghe thời gian nhỏ giọt như hương thơm tan trong không khí.</p>
                    <p class="mb-4">Và giữa bao nhịp sống vội vàng, vẫn còn đó những người gìn giữ hương vị nguyên bản, những nghệ nhân không chỉ rang cà phê, mà còn rang lên những ký ức.</p>
                    <p class="mb-4">Tôi bắt đầu viết chuỗi “Những câu chuyện quanh tách cà phê” từ một buổi chiều mưa, khi mùi cà phê quyện với hơi đất ẩm khiến lòng người trở nên thật mềm. Trong từng bài viết, có những câu chuyện nhỏ nhí nhảnh — đôi khi là mẩu đối thoại dễ thương giữa khách và người pha, đôi khi chỉ là một ý nghĩ thoáng qua khi nhâm nhi tách trà.</p>
                    <p>Nếu bạn đang đọc những dòng này, có lẽ bạn cũng như tôi — một kẻ tin rằng, đôi khi chỉ cần một tách cà phê nóng, một góc nhỏ yên tĩnh và một câu chuyện kể nhẹ như khói bay... là đủ để thấy lòng mình ấm lại.</p>
                </div>
            </div>
        </section>

        <section class="section" id="article1">
            <div class="article bg-white p-8 rounded-lg shadow-lg border-l-4 border-yellow-800">
                <div class="content-flex flex-col md:flex-row-reverse items-center gap-8">
                    <div class="image-right md:w-1/2">
                        <img src="../Photos/blog_image/paragraph2.jpg" alt="Hạt cà phê nguyên chất và nghệ nhân" class="w-full h-auto rounded-lg shadow-md">
                    </div>
                    <div class="text-left md:w-1/2">
                        <h2 class="text-3xl font-bold mb-4">☕ Bài 1: Hành Trình Của Hạt Cà Phê Nguyên Chất – Khi Nghệ Thuật Gặp Gỡ Tâm Hồn</h2>
                        <p class="mb-4">Người ta nói, mỗi hạt cà phê đều có linh hồn của riêng mình. Tôi tin điều đó — nhất là khi đứng giữa không gian ngập tràn mùi rang thơm nức, nghe tiếng lách tách của hạt nở bung trong lửa, và thấy bàn tay nghệ nhân lướt nhẹ trên từng mẻ cà phê như đang nâng niu kỷ niệm.</p>
                        <p>Hành trình của cà phê nguyên chất không bắt đầu ở quán, mà từ nơi cao nguyên gió hát. Những giọt sương sớm đọng trên lá, những người nông dân tỉ mẩn chọn từng quả đỏ mọng, rồi hong nắng trên sân phơi. Có lẽ, chính lúc ấy, hương vị chân thật nhất của cà phê đã được ươm mầm — giản dị, mộc mạc, nhưng sâu sắc như lòng người.</p>
                    </div>
                </div>
                <p class="mt-6 mb-4">Đến tay nghệ nhân rang xay, hạt cà phê lại bước vào một cuộc “hóa thân” mới. Mỗi người thợ là một người kể chuyện. Họ không chỉ rang cà phê, mà rang cả tâm hồn mình trong từng độ lửa, từng phút canh. Hơi khói quyện vào gió, thơm lừng một thứ mùi không thể nào quên — vừa mạnh mẽ, vừa dịu dàng, như bản tình ca giữa con người và thiên nhiên.</p>
                <blockquote class="border-l-4 border-yellow-700 pl-4 italic text-gray-600 my-6">“Cà phê nguyên chất giống như người thật lòng – không cần thêm đường, thêm sữa vẫn đủ khiến ta say.”</blockquote>
                <p>Một người bạn của tôi từng bảo: “Cà phê nguyên chất giống như người thật lòng – không cần thêm đường, thêm sữa vẫn đủ khiến ta say.” Tôi mỉm cười, nâng tách cà phê lên, thấy trong vị đắng ấy là bao nồng nàn của đất, bao chân thành của người. Và thế là, giữa nhịp sống hối hả, một tách cà phê nguyên chất lại hóa thành nơi để ta tìm thấy chính mình — nguyên sơ, tinh khôi, và thật.</p>
            </div>
        </section>

        <section class="section" id="article2">
            <div class="article bg-white p-8 rounded-lg shadow-lg border-l-4 border-yellow-800">
                <h2 class="text-3xl font-bold mb-4 text-center">🏛️ Bài 2: “The Old Flavour” – Khi Hương Xưa Ngồi Uống Cà Phê Cùng Hiện Đại</h2>
                <p class="mb-4">Có những buổi chiều tôi ghé lại “The Old Flavour”, quán cà phê nằm nép mình trong con phố nhỏ rêu phong. Cánh cửa gỗ cũ kỹ, bảng hiệu bạc màu thời gian, nhưng khi bước vào, ánh đèn vàng cùng tiếng nhạc jazz lại khiến lòng tôi như được đưa đi du hành qua hai thế giới.</p>
                <div class="image-center my-6">
                    <img src="../Photos/blog_image/paragraph3.jpg" alt="The Old Flavour quán cà phê" class="w-full max-w-3xl mx-auto h-auto rounded-lg shadow-md">
                </div>
                <blockquote class="text-center border-l-4 border-yellow-700 pl-4 italic text-gray-600 my-6 max-w-2xl mx-auto">“Làm sống lại hương vị xưa giữa lòng hiện đại.”</blockquote>
                <p class="mb-4">Chủ quán kể rằng, “The Old Flavour” ra đời từ một ý niệm giản dị: “Làm sống lại hương vị xưa giữa lòng hiện đại.” Ngày ấy, anh từng là một kiến trúc sư trẻ, thích lang thang quán cũ, mê những chiếc tách men sứ và cái cách người ta chậm rãi pha cà phê phin. Một ngày, anh nghĩ: Tại sao không tạo nên một nơi để người ta ngồi giữa hôm nay mà vẫn cảm được hơi thở hôm qua? Vậy là “The Old Flavour” ra đời — nơi cổ kính gặp gỡ hiện đại, nơi tường vôi xưa ôm trọn bàn gỗ mới, và nơi mỗi ly cà phê được pha như một nghi lễ của ký ức.</p>
                <p>Ngồi ở đây, bạn sẽ thấy người ta không chỉ đến để uống. Họ đến để nhớ. Để nói chuyện cùng quá khứ qua làn khói bay, để kể nhau nghe chuyện đời bằng giọng nhỏ nhẹ, như thể sợ làm vỡ mất không gian.</p>
            </div>
        </section>

        <section class="section" id="stories">
            <h2 class="text-3xl font-bold mb-4 text-center">☕ Những Mẩu Chuyện Nhỏ Quanh Bàn Cà Phê</h2>
            <div class="image-center my-6">
                <img src="../Photos/blog_image/paragraph4.jpg" alt="Những mẩu chuyện nhỏ quanh bàn cà phê" class="w-full max-w-3xl mx-auto h-auto rounded-lg shadow-md">
            </div>
            <div class="space-y-6 max-w-2xl mx-auto">
                <div class="story bg-gray-50 p-6 rounded-lg border-l-4 border-green-700">
                    <h3 class="font-bold text-xl mb-2">Câu chuyện thứ nhất</h3>
                    <p>Tôi hỏi cô phục vụ nhỏ nhắn rằng: – Em nghĩ cà phê ngon nhất khi nào? Cô cười, đáp: – Khi người uống nó chưa kịp than “đắng quá” mà vẫn cười với người đối diện ạ. Tôi suýt sặc cà phê vì câu trả lời dễ thương quá mức cho phép.</p>
                </div>
                <div class="story bg-gray-50 p-6 rounded-lg border-l-4 border-blue-700">
                    <h3 class="font-bold text-xl mb-2">Câu chuyện thứ hai</h3>
                    <p>Một ông cụ tóc bạc ngồi góc quán ngày nào cũng gọi đúng một món: “Cà phê phin, nhưng đừng vội.” Hỏi ra mới biết, ông thích nhìn giọt cà phê rơi chậm – “Vì đời mà nhanh thì chẳng còn ai nhớ hương vị xưa.”</p>
                </div>
                <div class="story bg-gray-50 p-6 rounded-lg border-l-4 border-purple-700">
                    <h3 class="font-bold text-xl mb-2">Câu chuyện thứ ba</h3>
                    <p>Còn tôi, mỗi khi trời đổ mưa, lại lôi laptop ra “giả vờ làm việc” ở đây. Nhưng thật ra là đang ngắm mưa rơi, đếm từng hơi cà phê bốc khói, và nghĩ xem có nên rủ ai đó cùng nhâm nhi tách trà, nói chuyện linh tinh về giấc mơ và cuộc sống hay không…</p>
                </div>
            </div>
            <p class="text-center mt-6 max-w-3xl mx-auto">“The Old Flavour” vẫn giữ được mình giữa bao quán cà phê mọc lên như nấm — như một bản nhạc cũ không lỗi thời. Chỉ cần một người nghe, một người hiểu, là đã đủ trọn vẹn.</p>
        </section>

        <section class="section" id="conclusion">
            <h2 class="text-3xl font-bold mb-4 text-center">🌙 Lời Kết: Khi Tách Cà Phê Cũng Biết Mỉm Cười</h2>
            <p class="mb-4 max-w-3xl mx-auto">Và thế là, qua từng câu chuyện, từng hương vị, ta nhận ra — cà phê không chỉ là thức uống, mà là một người bạn đồng hành của thời gian.</p>
            <p class="mb-4 max-w-3xl mx-auto">Nó lặng lẽ chứng kiến những buổi sáng ta bận rộn, những chiều ta thả hồn vào nỗi nhớ, và cả những đêm ta ngồi một mình, viết ra những điều chưa từng nói.</p>
            <p class="mb-4 max-w-3xl mx-auto">Người nghệ nhân rang cà phê đã gửi gắm cả trái tim vào từng hạt nhỏ. Người chủ quán “The Old Flavour” gom góp ký ức để tạo nên không gian vừa xưa vừa mới. Còn tôi – chỉ là kẻ ngồi giữa hai làn hương ấy, lắng nghe thế giới thở chậm lại trong từng giọt đắng ngọt ngào.</p>
            <div class="image-center my-6">
                <img src="../Photos/blog_image/ketbai.jpg" alt="Kết bài - Tách cà phê mỉm cười" class="w-full max-w-3xl mx-auto h-auto rounded-lg shadow-md">
            </div>
            <p class="mb-4 max-w-3xl mx-auto">Đôi khi, cuộc sống chẳng cần quá nhiều điều lớn lao. Chỉ cần một tách cà phê nóng, một khung cửa sổ nhỏ nhìn ra phố, và một người để ta kể vài câu chuyện nhí nhỏm – như việc con mèo nhà hàng xóm thích nằm trên máy pha cà phê, hay ông cụ góc quán vẫn đợi ai đó mà chẳng hề vội.</p>
            <p class="mb-4 max-w-3xl mx-auto">Rồi ta sẽ mỉm cười, vì hiểu rằng: “Cà phê ngon nhất không nằm ở hạt, ở phin, hay ở quán — mà ở khoảnh khắc ta thật lòng thưởng thức nó.”</p>
            <p class="mb-4 max-w-3xl mx-auto">Nếu một ngày nào đó bạn thấy mùi cà phê len nhẹ vào buổi sớm, hãy nhớ rằng, đâu đó giữa dòng đời, vẫn có một người đang viết tiếp những câu chuyện quanh tách cà phê – và biết đâu, trong tách cà phê của bạn, câu chuyện kế tiếp lại đang bắt đầu…</p>
            <div class="signature text-center mt-8 italic text-gray-700">
                <p>☕ Ký tên: Minh – người kể chuyện giữa hương cà phê và những điều bình dị. <a href="https://instagram.com/oldfavour" target="_blank" class="text-yellow-800 hover:underline">📸 Instagram</a></p>
            </div>
        </section>', 'nhung-cau-chuyen-quanh-tach-ca-phe', 'approved', 'Photos/blog_image/hero_image.jpg');