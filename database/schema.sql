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

-- favourites
CREATE TABLE favourites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  product_id INT NOT NULL,
  added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY (customer_id, product_id)
);

-- notifications
CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,         -- NULL nếu là global
  type VARCHAR(50) NOT NULL,        -- 'order_status','welcome','loyalty_point',...
  title VARCHAR(255),
  body TEXT,
  related_id INT DEFAULT NULL,      -- e.g. order_id
  url VARCHAR(255) DEFAULT NULL,    -- link to open
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =================================================================
-- Table for Comments functionality
-- =================================================================
CREATE TABLE comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  target_type ENUM('product', 'blog') NOT NULL,
  target_id INT NOT NULL,
  content TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
  status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  parent_id INT DEFAULT NULL, -- Allows for nested replies
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
);

-- Add indexes for better performance on lookups
CREATE INDEX idx_comments_target ON comments (target_type, target_id);

-- Drop the 'likes' column from the 'comments' table as reactions will be stored in 'comment_reactions'
ALTER TABLE comments DROP COLUMN likes;

-- =================================================================
-- Table for Comment Likes
-- =================================================================
CREATE TABLE comment_reactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  comment_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
  UNIQUE KEY (user_id, comment_id) -- Ensures a user can only like a comment once
);


-- Add a column to store the type of reaction
ALTER TABLE comment_reactions
ADD COLUMN reaction_type ENUM('like', 'love', 'haha', 'wow', 'sad', 'angry') NOT NULL DEFAULT 'like' AFTER comment_id;

-- Update existing records to 'like' as default reaction type
UPDATE comment_reactions SET reaction_type = 'like';