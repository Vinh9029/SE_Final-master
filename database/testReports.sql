USE theoldfavour;

-- Users (customers)
INSERT INTO users (username, password, email, full_name, phone, address, role) VALUES
('user1', '123456', 'user1@example.com', 'Nguyen Van A', '0901111111', 'Hà Nội', 'customer'),
('user2', '123456', 'user2@example.com', 'Tran Thi B', '0902222222', 'HCM', 'customer'),
('user3', '123456', 'user3@example.com', 'Le Van C', '0903333333', 'Đà Nẵng', 'customer');

-- Categories
INSERT INTO categories (name, description) VALUES
('Coffee', 'Các loại cà phê'),
('Tea', 'Các loại trà'),
('Pastry', 'Bánh ngọt');

-- Products
INSERT INTO products (category_id, name, description, price, is_signature) VALUES
(1, 'Cà phê sữa đá', 'Cà phê truyền thống', 30000, 1),
(1, 'Espresso', 'Cà phê Ý', 35000, 0),
(2, 'Trà đào cam sả', 'Trà trái cây', 40000, 1),
(2, 'Trà xanh', 'Trà xanh nguyên chất', 25000, 0),
(3, 'Croissant', 'Bánh sừng bò', 20000, 0);

-- Orders
INSERT INTO orders (user_id, order_date, status, total) VALUES
(1, '2025-08-01 10:30:00', 'completed', 95000),
(2, '2025-08-02 15:00:00', 'completed', 55000),
(3, '2025-08-05 09:00:00', 'completed', 60000),
(1, '2025-09-01 08:45:00', 'completed', 75000),
(2, '2025-09-10 19:30:00', 'completed', 95000),
(3, '2025-09-15 14:20:00', 'completed', 55000),
(1, '2025-09-20 11:00:00', 'completed', 105000),
(2, '2025-09-25 17:10:00', 'completed', 60000),
(3, '2025-09-27 09:40:00', 'completed', 85000);

-- Order Items
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(1, 1, 2, 60000),  -- 2x Cà phê sữa đá
(1, 5, 1, 20000),  -- 1x Croissant
(1, 2, 1, 15000),  -- Espresso (giá giảm thử nghiệm)

(2, 4, 2, 50000),  -- 2x Trà xanh
(2, 5, 1, 5000),   -- Croissant (giá khuyến mãi)

(3, 3, 1, 40000),  -- Trà đào cam sả
(3, 5, 1, 20000),  -- Croissant

(4, 2, 1, 35000),  -- Espresso
(4, 1, 1, 30000),  -- Cà phê sữa đá
(4, 5, 1, 10000),  -- Croissant (discount)

(5, 3, 2, 80000),  -- 2x Trà đào cam sả
(5, 5, 1, 15000),  -- Croissant

(6, 4, 2, 50000),
(6, 5, 1, 5000),

(7, 1, 2, 60000),
(7, 2, 1, 35000),
(7, 5, 1, 10000),

(8, 4, 2, 50000),
(8, 5, 1, 10000),

(9, 3, 2, 80000),
(9, 5, 1, 5000);