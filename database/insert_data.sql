-- Active: 1761633694540@@127.0.0.1@3306@theoldflavour
USE theoldflavour;
-- =========================
-- 1. Insert CATEGORIES
-- =========================
INSERT INTO categories (name, description) VALUES
('Cà phê', 'Các loại cà phê đặc trưng'),
('Trà & sữa', 'Các loại trà và trà sữa'),
('Nước đặc biệt', 'Thức uống đặc biệt và signature'),
('Đồ ăn kèm', 'Bánh ngọt và đồ ăn kèm');

-- =========================
-- 2. Insert PRODUCTS
-- =========================

-- Cà phê (category_id = 1)
INSERT INTO products (category_id, name, description, price, image, is_signature) VALUES
(1, 'Đen đá/nóng', 'Cà phê đen truyền thống, dùng nóng hoặc đá.', 20000, 'Photos/menus/caphe/den-da-nong.jpg', FALSE),
(1, 'Sữa đá/nóng', 'Cà phê sữa đậm đà, có thể uống nóng hoặc lạnh.', 25000, 'Photos/menus/caphe/sua-da-nong.jpg', FALSE),
(1, 'Bạc xỉu', 'Cà phê sữa đá với vị ngọt béo nhẹ.', 25000, 'Photos/menus/caphe/bac-xiu.jpg', FALSE),
(1, 'Cold Brew (ủ lạnh)', 'Cà phê ủ lạnh trong nhiều giờ, hương vị thanh mát.', 40000, 'Photos/menus/caphe/cold-brew-u-lanh.jpg', FALSE),
(1, 'Cà phê muối', 'Cà phê kết hợp với lớp kem muối béo mặn.', 35000, 'Photos/menus/caphe/ca-phe-muoi.jpg', FALSE),
(1, 'Cappuccino', 'Cà phê Ý nổi tiếng với lớp bọt sữa dày.', 45000, 'Photos/menus/caphe/cappuccino.jpg', FALSE),
(1, 'Latte', 'Cà phê Ý với nhiều sữa tươi, vị nhẹ nhàng.', 45000, 'Photos/menus/caphe/latte.jpg', FALSE),
(1, 'Espresso', 'Cà phê Ý pha máy, đậm đặc.', 30000, 'Photos/menus/caphe/espresso.jpg', FALSE);

-- Trà & sữa (category_id = 2)
INSERT INTO products (category_id, name, description, price, image, is_signature) VALUES
(2, 'Trà đào cam sả', 'Trà đào kết hợp cam và sả, hương vị mát lạnh.', 35000, 'Photos/menus/trasua/tra-dao-cam-sa.jpg', FALSE),
(2, 'Trà hoa cúc mật ong', 'Trà hoa cúc thanh mát cùng vị ngọt mật ong.', 30000, 'Photos/menus/trasua/tra-hoa-cuc-mat-ong.jpg', FALSE),
(2, 'Trà sen nhãn', 'Trà thanh mát, kết hợp hương sen và nhãn tươi.', 35000, 'Photos/menus/trasua/tra-sen-nhan.jpg', FALSE),
(2, 'Trà sữa Oolong', 'Trà Oolong đậm đà pha cùng sữa béo.', 40000, 'Photos/menus/trasua/tra-sua-oolong.jpg', FALSE),
(2, 'Trà sữa Matcha', 'Trà xanh Matcha Nhật Bản hòa quyện sữa.', 40000, 'Photos/menus/trasua/tra-sua-matcha.jpg', FALSE),
(2, 'Trà sữa Hồng Trà', 'Hồng trà truyền thống kết hợp sữa tươi.', 35000, 'Photos/menus/trasua/tra-sua-hong-tra.jpg', FALSE),
(2, 'Hồng trà sữa trân châu đường nâu', 'Hồng trà sữa thêm trân châu nấu đường nâu.', 45000, 'Photos/menus/trasua/hong-tra-sua-tran-chau-duong-nau.jpg', FALSE),
(2, 'Trà dâu tằm thanh long', 'Trà trái cây kết hợp dâu tằm và thanh long.', 45000, 'Photos/menus/trasua/tra-dau-tam-thanh-long.jpg', FALSE);

-- Nước đặc biệt (category_id = 3)
INSERT INTO products (category_id, name, description, price, image, is_signature) VALUES
(3, 'The Old Flavour Coffee', 'Thức uống đặc trưng của quán, hương vị độc đáo.', 45000, 'Photos/menus/nuocdacbiet/the-old-flavour-coffee.jpg', FALSE),
(3, 'Soda chanh dây mật ong', 'Soda kết hợp chanh dây và mật ong thanh mát.', 35000, 'Photos/menus/nuocdacbiet/soda-chanh-day-mat-ong.jpg', FALSE),
(3, 'Sinh tố xoài', 'Sinh tố trái xoài tươi mát, bổ dưỡng.', 40000, 'Photos/menus/nuocdacbiet/sinh-to-xoai.jpg', FALSE),
(3, 'Sinh tố chuối cacao', 'Sinh tố chuối pha cùng cacao béo ngậy.', 40000, 'Photos/menus/nuocdacbiet/sinh-to-chuoi-cacao.jpg', FALSE),
(3, 'Sinh tố dâu', 'Sinh tố dâu tây tươi, vị chua ngọt dễ uống.', 40000, 'Photos/menus/nuocdacbiet/sinh-to-dau.jpg', FALSE),
(3, 'Chocolate đá xay', 'Thức uống chocolate đá xay mát lạnh.', 50000, 'Photos/menus/nuocdacbiet/chocolate-da-xay.jpg', FALSE),
(3, 'Soda việt quất bạc hà', 'Soda việt quất kết hợp lá bạc hà tươi.', 45000, 'Photos/menus/nuocdacbiet/soda-viet-quat-bac-ha.jpg', FALSE),
(3, 'Nước ép cam tươi', 'Cam tươi ép nguyên chất 100%.', 35000, 'Photos/menus/nuocdacbiet/nuoc-ep-cam-tuoi.jpg', FALSE);

-- Đồ ăn kèm (category_id = 4)
INSERT INTO products (category_id, name, description, price, image, is_signature) VALUES
(4, 'Croissant bơ', 'Bánh sừng bò thơm bơ, vỏ giòn ruộm.', 30000, 'Photos/menus/ankem/croissant-bo.jpg', FALSE),
(4, 'Croissant phô mai jambon', 'Bánh croissant nhân jambon và phô mai.', 40000, 'Photos/menus/ankem/croissant-pho-mai-jambon.jpg', FALSE),
(4, 'Tiramisu', 'Bánh ngọt Ý với lớp kem mascarpone mềm mịn.', 45000, 'Photos/menus/ankem/tiramisu.jpg', FALSE),
(4, 'Mousse chanh dây', 'Bánh mousse chanh dây chua ngọt thanh mát.', 45000, 'Photos/menus/ankem/mousse-chanh-day.jpg', FALSE),
(4, 'Cheesecake việt quất', 'Bánh cheesecake béo ngậy phủ sốt việt quất.', 50000, 'Photos/menus/ankem/cheesecake-viet-quat.jpg', FALSE),
(4, 'Bánh su kem (3 cái)', 'Nhân kem sữa tươi mềm mịn, 3 cái/ phần.', 30000, 'Photos/menus/ankem/banh-su-kem-3-cai.jpg', FALSE),
(4, 'Set bánh ngọt mini (mix 3 loại)', 'Set bánh mini mix ngẫu nhiên 3 loại.', 60000, 'Photos/menus/ankem/set-banh-ngot-mini-mix-3-loai.jpg', FALSE),
(4, 'Red Velvet Cake', 'Bánh Red Velvet đỏ đặc trưng, lớp kem phô mai.', 55000, 'Photos/menus/ankem/red-velvet-cake.jpg', FALSE),
(4, 'Brownie Socola', 'Bánh brownie đậm vị socola, mềm ẩm.', 50000, 'Photos/menus/ankem/brownie-socola.jpg', FALSE),
(4, 'Bánh flan caramel', 'Bánh flan mềm mịn phủ caramel.', 25000, 'Photos/menus/ankem/banh-flan-caramel.jpg', FALSE),
(4, 'Bánh apple pie mini', 'Bánh pie nhân táo nướng ngọt dịu.', 45000, 'Photos/menus/ankem/banh-apple-pie-mini.jpg', FALSE);

-- =========================
-- 3. Insert PRODUCT_SIZES
-- =========================

-- 📌 Đồ uống (id 1 → 24): size theo ml
-- Quy ước: S=350ml, M=500ml (+5k), L=750ml (+10k), XL=1000ml (+15k)

-- Cà phê (id 1 → 8)
INSERT INTO product_sizes (product_id, size_name, volume, extra_price) VALUES
((SELECT product_id FROM products WHERE name = 'Đen đá/nóng' LIMIT 1),'S','350ml',0),
((SELECT product_id FROM products WHERE name = 'Đen đá/nóng' LIMIT 1),'M','500ml',5000),
((SELECT product_id FROM products WHERE name = 'Đen đá/nóng' LIMIT 1),'L','750ml',10000),
((SELECT product_id FROM products WHERE name = 'Đen đá/nóng' LIMIT 1),'XL','1000ml',15000),
((SELECT product_id FROM products WHERE name = 'Sữa đá/nóng' LIMIT 1),'S','350ml',0),
((SELECT product_id FROM products WHERE name = 'Sữa đá/nóng' LIMIT 1),'M','500ml',5000),
((SELECT product_id FROM products WHERE name = 'Sữa đá/nóng' LIMIT 1),'L','750ml',10000),
((SELECT product_id FROM products WHERE name = 'Sữa đá/nóng' LIMIT 1),'XL','1000ml',15000),
((SELECT product_id FROM products WHERE name = 'Bạc xỉu' LIMIT 1),'S','350ml',0),
((SELECT product_id FROM products WHERE name = 'Bạc xỉu' LIMIT 1),'M','500ml',5000),
((SELECT product_id FROM products WHERE name = 'Bạc xỉu' LIMIT 1),'L','750ml',10000),
((SELECT product_id FROM products WHERE name = 'Bạc xỉu' LIMIT 1),'XL','1000ml',15000),
((SELECT product_id FROM products WHERE name = 'Cold Brew (ủ lạnh)' LIMIT 1),'S','350ml',0),
((SELECT product_id FROM products WHERE name = 'Cold Brew (ủ lạnh)' LIMIT 1),'M','500ml',5000),
((SELECT product_id FROM products WHERE name = 'Cold Brew (ủ lạnh)' LIMIT 1),'L','750ml',10000),
((SELECT product_id FROM products WHERE name = 'Cold Brew (ủ lạnh)' LIMIT 1),'XL','1000ml',15000),
((SELECT product_id FROM products WHERE name = 'Cà phê muối' LIMIT 1),'S','350ml',0),
((SELECT product_id FROM products WHERE name = 'Cà phê muối' LIMIT 1),'M','500ml',5000),
((SELECT product_id FROM products WHERE name = 'Cà phê muối' LIMIT 1),'L','750ml',10000),
((SELECT product_id FROM products WHERE name = 'Cà phê muối' LIMIT 1),'XL','1000ml',15000),
((SELECT product_id FROM products WHERE name = 'Cappuccino' LIMIT 1),'S','350ml',0),
((SELECT product_id FROM products WHERE name = 'Cappuccino' LIMIT 1),'M','500ml',5000),
((SELECT product_id FROM products WHERE name = 'Cappuccino' LIMIT 1),'L','750ml',10000),
((SELECT product_id FROM products WHERE name = 'Cappuccino' LIMIT 1),'XL','1000ml',15000),
((SELECT product_id FROM products WHERE name = 'Latte' LIMIT 1),'S','350ml',0),
((SELECT product_id FROM products WHERE name = 'Latte' LIMIT 1),'M','500ml',5000),
((SELECT product_id FROM products WHERE name = 'Latte' LIMIT 1),'L','750ml',10000),
((SELECT product_id FROM products WHERE name = 'Latte' LIMIT 1),'XL','1000ml',15000),
((SELECT product_id FROM products WHERE name = 'Espresso' LIMIT 1),'S','350ml',0),
((SELECT product_id FROM products WHERE name = 'Espresso' LIMIT 1),'M','500ml',5000),
((SELECT product_id FROM products WHERE name = 'Espresso' LIMIT 1),'L','750ml',10000),
((SELECT product_id FROM products WHERE name = 'Espresso' LIMIT 1),'XL','1000ml',15000);

-- Trà & sữa (id 9 → 16)
INSERT INTO product_sizes (product_id, size_name, volume, extra_price) VALUES
((SELECT product_id FROM products WHERE name = 'Trà đào cam sả' LIMIT 1),'S','350ml',0),
((SELECT product_id FROM products WHERE name = 'Trà đào cam sả' LIMIT 1),'M','500ml',5000),
((SELECT product_id FROM products WHERE name = 'Trà đào cam sả' LIMIT 1),'L','750ml',10000),
((SELECT product_id FROM products WHERE name = 'Trà đào cam sả' LIMIT 1),'XL','1000ml',15000),
((SELECT product_id FROM products WHERE name = 'Trà hoa cúc mật ong' LIMIT 1),'S','350ml',0),
((SELECT product_id FROM products WHERE name = 'Trà hoa cúc mật ong' LIMIT 1),'M','500ml',5000),
((SELECT product_id FROM products WHERE name = 'Trà hoa cúc mật ong' LIMIT 1),'L','750ml',10000),
((SELECT product_id FROM products WHERE name = 'Trà hoa cúc mật ong' LIMIT 1),'XL','1000ml',15000),
((SELECT product_id FROM products WHERE name = 'Trà sen nhãn' LIMIT 1),'S','350ml',0),
((SELECT product_id FROM products WHERE name = 'Trà sen nhãn' LIMIT 1),'M','500ml',5000),
((SELECT product_id FROM products WHERE name = 'Trà sen nhãn' LIMIT 1),'L','750ml',10000),
((SELECT product_id FROM products WHERE name = 'Trà sen nhãn' LIMIT 1),'XL','1000ml',15000),
((SELECT product_id FROM products WHERE name = 'Trà sữa Oolong' LIMIT 1),'S','350ml',0),
((SELECT product_id FROM products WHERE name = 'Trà sữa Oolong' LIMIT 1),'M','500ml',5000),
((SELECT product_id FROM products WHERE name = 'Trà sữa Oolong' LIMIT 1),'L','750ml',10000),
((SELECT product_id FROM products WHERE name = 'Trà sữa Oolong' LIMIT 1),'XL','1000ml',15000),
((SELECT product_id FROM products WHERE name = 'Trà sữa Matcha' LIMIT 1),'S','350ml',0),
((SELECT product_id FROM products WHERE name = 'Trà sữa Matcha' LIMIT 1),'M','500ml',5000),
((SELECT product_id FROM products WHERE name = 'Trà sữa Matcha' LIMIT 1),'L','750ml',10000),
((SELECT product_id FROM products WHERE name = 'Trà sữa Matcha' LIMIT 1),'XL','1000ml',15000),
((SELECT product_id FROM products WHERE name = 'Trà sữa Hồng Trà' LIMIT 1),'S','350ml',0),
((SELECT product_id FROM products WHERE name = 'Trà sữa Hồng Trà' LIMIT 1),'M','500ml',5000),
((SELECT product_id FROM products WHERE name = 'Trà sữa Hồng Trà' LIMIT 1),'L','750ml',10000),
((SELECT product_id FROM products WHERE name = 'Trà sữa Hồng Trà' LIMIT 1),'XL','1000ml',15000),
((SELECT product_id FROM products WHERE name = 'Hồng trà sữa trân châu đường nâu' LIMIT 1),'S','350ml',0),
((SELECT product_id FROM products WHERE name = 'Hồng trà sữa trân châu đường nâu' LIMIT 1),'M','500ml',5000),
((SELECT product_id FROM products WHERE name = 'Hồng trà sữa trân châu đường nâu' LIMIT 1),'L','750ml',10000),
((SELECT product_id FROM products WHERE name = 'Hồng trà sữa trân châu đường nâu' LIMIT 1),'XL','1000ml',15000),
((SELECT product_id FROM products WHERE name = 'Trà dâu tằm thanh long' LIMIT 1),'S','350ml',0),
((SELECT product_id FROM products WHERE name = 'Trà dâu tằm thanh long' LIMIT 1),'M','500ml',5000),
((SELECT product_id FROM products WHERE name = 'Trà dâu tằm thanh long' LIMIT 1),'L','750ml',10000),
((SELECT product_id FROM products WHERE name = 'Trà dâu tằm thanh long' LIMIT 1),'XL','1000ml',15000);

-- Nước đặc biệt (id 17 → 24)
INSERT INTO product_sizes (product_id, size_name, volume, extra_price) VALUES
((SELECT product_id FROM products WHERE name = 'The Old Flavour Coffee' LIMIT 1),'S','350ml',0),
((SELECT product_id FROM products WHERE name = 'The Old Flavour Coffee' LIMIT 1),'M','500ml',5000),
((SELECT product_id FROM products WHERE name = 'The Old Flavour Coffee' LIMIT 1),'L','750ml',10000),
((SELECT product_id FROM products WHERE name = 'The Old Flavour Coffee' LIMIT 1),'XL','1000ml',15000),
((SELECT product_id FROM products WHERE name = 'Soda chanh dây mật ong' LIMIT 1),'S','350ml',0),
((SELECT product_id FROM products WHERE name = 'Soda chanh dây mật ong' LIMIT 1),'M','500ml',5000),
((SELECT product_id FROM products WHERE name = 'Soda chanh dây mật ong' LIMIT 1),'L','750ml',10000),
((SELECT product_id FROM products WHERE name = 'Soda chanh dây mật ong' LIMIT 1),'XL','1000ml',15000),
((SELECT product_id FROM products WHERE name = 'Sinh tố xoài' LIMIT 1),'S','350ml',0),
((SELECT product_id FROM products WHERE name = 'Sinh tố xoài' LIMIT 1),'M','500ml',5000),
((SELECT product_id FROM products WHERE name = 'Sinh tố xoài' LIMIT 1),'L','750ml',10000),
((SELECT product_id FROM products WHERE name = 'Sinh tố xoài' LIMIT 1),'XL','1000ml',15000),
((SELECT product_id FROM products WHERE name = 'Sinh tố chuối cacao' LIMIT 1),'S','350ml',0),
((SELECT product_id FROM products WHERE name = 'Sinh tố chuối cacao' LIMIT 1),'M','500ml',5000),
((SELECT product_id FROM products WHERE name = 'Sinh tố chuối cacao' LIMIT 1),'L','750ml',10000),
((SELECT product_id FROM products WHERE name = 'Sinh tố chuối cacao' LIMIT 1),'XL','1000ml',15000),
((SELECT product_id FROM products WHERE name = 'Sinh tố dâu' LIMIT 1),'S','350ml',0),
((SELECT product_id FROM products WHERE name = 'Sinh tố dâu' LIMIT 1),'M','500ml',5000),
((SELECT product_id FROM products WHERE name = 'Sinh tố dâu' LIMIT 1),'L','750ml',10000),
((SELECT product_id FROM products WHERE name = 'Sinh tố dâu' LIMIT 1),'XL','1000ml',15000),
((SELECT product_id FROM products WHERE name = 'Chocolate đá xay' LIMIT 1),'S','350ml',0),
((SELECT product_id FROM products WHERE name = 'Chocolate đá xay' LIMIT 1),'M','500ml',5000),
((SELECT product_id FROM products WHERE name = 'Chocolate đá xay' LIMIT 1),'L','750ml',10000),
((SELECT product_id FROM products WHERE name = 'Chocolate đá xay' LIMIT 1),'XL','1000ml',15000),
((SELECT product_id FROM products WHERE name = 'Soda việt quất bạc hà' LIMIT 1),'S','350ml',0),
((SELECT product_id FROM products WHERE name = 'Soda việt quất bạc hà' LIMIT 1),'M','500ml',5000),
((SELECT product_id FROM products WHERE name = 'Soda việt quất bạc hà' LIMIT 1),'L','750ml',10000),
((SELECT product_id FROM products WHERE name = 'Soda việt quất bạc hà' LIMIT 1),'XL','1000ml',15000),
((SELECT product_id FROM products WHERE name = 'Nước ép cam tươi' LIMIT 1),'S','350ml',0),
((SELECT product_id FROM products WHERE name = 'Nước ép cam tươi' LIMIT 1),'M','500ml',5000),
((SELECT product_id FROM products WHERE name = 'Nước ép cam tươi' LIMIT 1),'L','750ml',10000),
((SELECT product_id FROM products WHERE name = 'Nước ép cam tươi' LIMIT 1),'XL','1000ml',15000);

-- 📌 Đồ ăn (id 25 → 35): size theo khẩu phần
-- Quy ước: Nhỏ=1 phần (0đ), Vừa=2 phần (+10k), Lớn=4 phần (+20k)

INSERT INTO product_sizes (product_id, size_name, volume, extra_price) VALUES
((SELECT product_id FROM products WHERE name = 'Croissant bơ' LIMIT 1),'Nhỏ','1 phần',0),
((SELECT product_id FROM products WHERE name = 'Croissant bơ' LIMIT 1),'Vừa','2 phần',10000),
((SELECT product_id FROM products WHERE name = 'Croissant bơ' LIMIT 1),'Lớn','4 phần',20000),
((SELECT product_id FROM products WHERE name = 'Croissant phô mai jambon' LIMIT 1),'Nhỏ','1 phần',0),
((SELECT product_id FROM products WHERE name = 'Croissant phô mai jambon' LIMIT 1),'Vừa','2 phần',10000),
((SELECT product_id FROM products WHERE name = 'Croissant phô mai jambon' LIMIT 1),'Lớn','4 phần',20000),
((SELECT product_id FROM products WHERE name = 'Tiramisu' LIMIT 1),'Nhỏ','1 phần',0),
((SELECT product_id FROM products WHERE name = 'Tiramisu' LIMIT 1),'Vừa','2 phần',10000),
((SELECT product_id FROM products WHERE name = 'Tiramisu' LIMIT 1),'Lớn','4 phần',20000),
((SELECT product_id FROM products WHERE name = 'Mousse chanh dây' LIMIT 1),'Nhỏ','1 phần',0),
((SELECT product_id FROM products WHERE name = 'Mousse chanh dây' LIMIT 1),'Vừa','2 phần',10000),
((SELECT product_id FROM products WHERE name = 'Mousse chanh dây' LIMIT 1),'Lớn','4 phần',20000),
((SELECT product_id FROM products WHERE name = 'Cheesecake việt quất' LIMIT 1),'Nhỏ','1 phần',0),
((SELECT product_id FROM products WHERE name = 'Cheesecake việt quất' LIMIT 1),'Vừa','2 phần',10000),
((SELECT product_id FROM products WHERE name = 'Cheesecake việt quất' LIMIT 1),'Lớn','4 phần',20000),
((SELECT product_id FROM products WHERE name = 'Bánh su kem (3 cái)' LIMIT 1),'Nhỏ','1 phần',0),
((SELECT product_id FROM products WHERE name = 'Bánh su kem (3 cái)' LIMIT 1),'Vừa','2 phần',10000),
((SELECT product_id FROM products WHERE name = 'Bánh su kem (3 cái)' LIMIT 1),'Lớn','4 phần',20000),
((SELECT product_id FROM products WHERE name = 'Set bánh ngọt mini (mix 3 loại)' LIMIT 1),'Nhỏ','1 phần',0),
((SELECT product_id FROM products WHERE name = 'Set bánh ngọt mini (mix 3 loại)' LIMIT 1),'Vừa','2 phần',10000),
((SELECT product_id FROM products WHERE name = 'Set bánh ngọt mini (mix 3 loại)' LIMIT 1),'Lớn','4 phần',20000),
((SELECT product_id FROM products WHERE name = 'Red Velvet Cake' LIMIT 1),'Nhỏ','1 phần',0),
((SELECT product_id FROM products WHERE name = 'Red Velvet Cake' LIMIT 1),'Vừa','2 phần',10000),
((SELECT product_id FROM products WHERE name = 'Red Velvet Cake' LIMIT 1),'Lớn','4 phần',20000),
((SELECT product_id FROM products WHERE name = 'Brownie Socola' LIMIT 1),'Nhỏ','1 phần',0),
((SELECT product_id FROM products WHERE name = 'Brownie Socola' LIMIT 1),'Vừa','2 phần',10000),
((SELECT product_id FROM products WHERE name = 'Brownie Socola' LIMIT 1),'Lớn','4 phần',20000),
((SELECT product_id FROM products WHERE name = 'Bánh flan caramel' LIMIT 1),'Nhỏ','1 phần',0),
((SELECT product_id FROM products WHERE name = 'Bánh flan caramel' LIMIT 1),'Vừa','2 phần',10000),
((SELECT product_id FROM products WHERE name = 'Bánh flan caramel' LIMIT 1),'Lớn','4 phần',20000),
((SELECT product_id FROM products WHERE name = 'Bánh apple pie mini' LIMIT 1),'Nhỏ','1 phần',0),
((SELECT product_id FROM products WHERE name = 'Bánh apple pie mini' LIMIT 1),'Vừa','2 phần',10000),
((SELECT product_id FROM products WHERE name = 'Bánh apple pie mini' LIMIT 1),'Lớn','4 phần',20000);

-- Thêm các voucher mẫu cho các phần thưởng vòng quay
-- INSERT INTO vouchers (user_id, code, discount_percent, program_name, min_order_value, status, created_at, expires_at)
-- VALUES
-- (1001, 'DISCOUNT20', 20, 'Vòng quay may mắn', 100000, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 90 DAY)),
-- (1002, 'FREESHIP', 0, 'Vòng quay may mắn', 150000, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 90 DAY)),
-- (1003, 'VOUCHER50K', 0, 'Vòng quay may mắn', 0, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 90 DAY)),
-- (1004, 'VOUCHER100K', 0, 'Vòng quay may mắn', 0, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 90 DAY)),
-- (1005, 'VOUCHER200K', 0, 'Vòng quay may mắn', 0, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 90 DAY));

-- Thêm dữ liệu blog mẫu từ trang blog.php hiện tại
-- Xóa dữ liệu cũ nếu có để tránh trùng lặp slug
DELETE FROM `blogs`;

-- Bài 1: Lời Tựa
INSERT INTO `blogs` (`user_id`, `title`, `content`, `slug`, `status`, `cover_image`) VALUES
(1, 'Lời Tựa: Khi Cuộc Đời Nhỏ Giọt Qua Tách Cà Phê', '
<p class="mb-4">Có những buổi sáng, tôi ngồi nơi góc nhỏ của quán quen, nghe tiếng thìa chạm vào tách sứ, và nhận ra – hình như cuộc đời cũng giống như một ly cà phê: phải chậm lại mới thấy được vị thật của nó.</p>
<p class="mb-4">Cà phê không chỉ là thức uống. Nó là một nghi thức của tâm hồn, là cách con người ngồi lại với chính mình, lắng nghe thời gian nhỏ giọt như hương thơm tan trong không khí.</p>
<p class="mb-4">Và giữa bao nhịp sống vội vàng, vẫn còn đó những người gìn giữ hương vị nguyên bản, những nghệ nhân không chỉ rang cà phê, mà còn rang lên những ký ức.</p>
<p class="mb-4">Tôi bắt đầu viết chuỗi “Những câu chuyện quanh tách cà phê” từ một buổi chiều mưa, khi mùi cà phê quyện với hơi đất ẩm khiến lòng người trở nên thật mềm. Trong từng bài viết, có những câu chuyện nhỏ nhí nhảnh — đôi khi là mẩu đối thoại dễ thương giữa khách và người pha, đôi khi chỉ là một ý nghĩ thoáng qua khi nhâm nhi tách trà.</p>
<p>Nếu bạn đang đọc những dòng này, có lẽ bạn cũng như tôi — một kẻ tin rằng, đôi khi chỉ cần một tách cà phê nóng, một góc nhỏ yên tĩnh và một câu chuyện kể nhẹ như khói bay... là đủ để thấy lòng mình ấm lại.</p>
', 'loi-tua-khi-cuoc-doi-nho-giot-qua-tach-ca-phe', 'approved', 'Photos/blog_image/paragraph1.jpg');

-- Bài 2: Hành Trình Của Hạt Cà Phê
INSERT INTO `blogs` (`user_id`, `title`, `content`, `slug`, `status`, `cover_image`) VALUES
(1, 'Hành Trình Của Hạt Cà Phê Nguyên Chất', '
<p class="mb-4">Người ta nói, mỗi hạt cà phê đều có linh hồn của riêng mình. Tôi tin điều đó — nhất là khi đứng giữa không gian ngập tràn mùi rang thơm nức, nghe tiếng lách tách của hạt nở bung trong lửa, và thấy bàn tay nghệ nhân lướt nhẹ trên từng mẻ cà phê như đang nâng niu kỷ niệm.</p>
<p>Hành trình của cà phê nguyên chất không bắt đầu ở quán, mà từ nơi cao nguyên gió hát. Những giọt sương sớm đọng trên lá, những người nông dân tỉ mẩn chọn từng quả đỏ mọng, rồi hong nắng trên sân phơi. Có lẽ, chính lúc ấy, hương vị chân thật nhất của cà phê đã được ươm mầm — giản dị, mộc mạc, nhưng sâu sắc như lòng người.</p>
<p class="mt-6 mb-4">Đến tay nghệ nhân rang xay, hạt cà phê lại bước vào một cuộc “hóa thân” mới. Mỗi người thợ là một người kể chuyện. Họ không chỉ rang cà phê, mà rang cả tâm hồn mình trong từng độ lửa, từng phút canh. Hơi khói quyện vào gió, thơm lừng một thứ mùi không thể nào quên — vừa mạnh mẽ, vừa dịu dàng, như bản tình ca giữa con người và thiên nhiên.</p>
<blockquote class="border-l-4 border-yellow-700 pl-4 italic text-gray-600 my-6">“Cà phê nguyên chất giống như người thật lòng – không cần thêm đường, thêm sữa vẫn đủ khiến ta say.”</blockquote>
<p>Một người bạn của tôi từng bảo: “Cà phê nguyên chất giống như người thật lòng – không cần thêm đường, thêm sữa vẫn đủ khiến ta say.” Tôi mỉm cười, nâng tách cà phê lên, thấy trong vị đắng ấy là bao nồng nàn của đất, bao chân thành của người. Và thế là, giữa nhịp sống hối hả, một tách cà phê nguyên chất lại hóa thành nơi để ta tìm thấy chính mình — nguyên sơ, tinh khôi, và thật.</p>
', 'hanh-trinh-cua-hat-ca-phe-nguyen-chat', 'approved', 'Photos/blog_image/paragraph2.jpg');

-- Bài 3: The Old Flavour
INSERT INTO `blogs` (`user_id`, `title`, `content`, `slug`, `status`, `cover_image`) VALUES
(1, 'The Old Flavour – Khi Hương Xưa Ngồi Uống Cà Phê Cùng Hiện Đại', '
<p class="mb-4">Có những buổi chiều tôi ghé lại “The Old Flavour”, quán cà phê nằm nép mình trong con phố nhỏ rêu phong. Cánh cửa gỗ cũ kỹ, bảng hiệu bạc màu thời gian, nhưng khi bước vào, ánh đèn vàng cùng tiếng nhạc jazz lại khiến lòng tôi như được đưa đi du hành qua hai thế giới.</p>
<blockquote class="text-center border-l-4 border-yellow-700 pl-4 italic text-gray-600 my-6 max-w-2xl mx-auto">“Làm sống lại hương vị xưa giữa lòng hiện đại.”</blockquote>
<p class="mb-4">Chủ quán kể rằng, “The Old Flavour” ra đời từ một ý niệm giản dị: “Làm sống lại hương vị xưa giữa lòng hiện đại.” Ngày ấy, anh từng là một kiến trúc sư trẻ, thích lang thang quán cũ, mê những chiếc tách men sứ và cái cách người ta chậm rãi pha cà phê phin. Một ngày, anh nghĩ: Tại sao không tạo nên một nơi để người ta ngồi giữa hôm nay mà vẫn cảm được hơi thở hôm qua? Vậy là “The Old Flavour” ra đời — nơi cổ kính gặp gỡ hiện đại, nơi tường vôi xưa ôm trọn bàn gỗ mới, và nơi mỗi ly cà phê được pha như một nghi lễ của ký ức.</p>
<p>Ngồi ở đây, bạn sẽ thấy người ta không chỉ đến để uống. Họ đến để nhớ. Để nói chuyện cùng quá khứ qua làn khói bay, để kể nhau nghe chuyện đời bằng giọng nhỏ nhẹ, như thể sợ làm vỡ mất không gian.</p>
', 'the-old-flavour-khi-huong-xua-gap-hien-dai', 'approved', 'Photos/blog_image/paragraph3.jpg');

-- Bài 4: Những Mẩu Chuyện Nhỏ
INSERT INTO `blogs` (`user_id`, `title`, `content`, `slug`, `status`, `cover_image`) VALUES
(1, 'Những Mẩu Chuyện Nhỏ Quanh Bàn Cà Phê', '
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
', 'nhung-mau-chuyen-nho-quanh-ban-ca-phe', 'approved', 'Photos/blog_image/paragraph4.jpg');

-- Bài 5: Lời Kết
INSERT INTO `blogs` (`user_id`, `title`, `content`, `slug`, `status`, `cover_image`) VALUES
(1, 'Lời Kết: Khi Tách Cà Phê Cũng Biết Mỉm Cười', '
<p class="mb-4 max-w-3xl mx-auto">Và thế là, qua từng câu chuyện, từng hương vị, ta nhận ra — cà phê không chỉ là thức uống, mà là một người bạn đồng hành của thời gian.</p>
<p class="mb-4 max-w-3xl mx-auto">Nó lặng lẽ chứng kiến những buổi sáng ta bận rộn, những chiều ta thả hồn vào nỗi nhớ, và cả những đêm ta ngồi một mình, viết ra những điều chưa từng nói.</p>
<p class="mb-4 max-w-3xl mx-auto">Người nghệ nhân rang cà phê đã gửi gắm cả trái tim vào từng hạt nhỏ. Người chủ quán “The Old Flavour” gom góp ký ức để tạo nên không gian vừa xưa vừa mới. Còn tôi – chỉ là kẻ ngồi giữa hai làn hương ấy, lắng nghe thế giới thở chậm lại trong từng giọt đắng ngọt ngào.</p>
<p class="mb-4 max-w-3xl mx-auto">Đôi khi, cuộc sống chẳng cần quá nhiều điều lớn lao. Chỉ cần một tách cà phê nóng, một khung cửa sổ nhỏ nhìn ra phố, và một người để ta kể vài câu chuyện nhí nhỏm – như việc con mèo nhà hàng xóm thích nằm trên máy pha cà phê, hay ông cụ góc quán vẫn đợi ai đó mà chẳng hề vội.</p>
<p class="mb-4 max-w-3xl mx-auto">Rồi ta sẽ mỉm cười, vì hiểu rằng: “Cà phê ngon nhất không nằm ở hạt, ở phin, hay ở quán — mà ở khoảnh khắc ta thật lòng thưởng thức nó.”</p>
<p class="mb-4 max-w-3xl mx-auto">Nếu một ngày nào đó bạn thấy mùi cà phê len nhẹ vào buổi sớm, hãy nhớ rằng, đâu đó giữa dòng đời, vẫn có một người đang viết tiếp những câu chuyện quanh tách cà phê – và biết đâu, trong tách cà phê của bạn, câu chuyện kế tiếp lại đang bắt đầu…</p>
<div class="signature text-center mt-8 italic text-gray-700">
    <p>☕ Ký tên: Minh – người kể chuyện giữa hương cà phê và những điều bình dị.</p>
</div>
', 'loi-ket-khi-tach-ca-phe-cung-biet-mim-cuoi', 'approved', 'Photos/blog_image/ketbai.jpg');