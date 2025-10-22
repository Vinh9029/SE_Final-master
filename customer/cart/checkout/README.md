# Hệ thống Thanh toán - Checkout System

## 📋 Mô tả
Hệ thống thanh toán hoàn chỉnh với 2 phương thức:
- **Tiền mặt** (Cash on Delivery)
- **VNPay QR** (Thanh toán qua mã QR)

## 📁 Cấu trúc thư mục

```
checkout/
├── index.php                    # Trang thanh toán chính
├── process.php                  # Xử lý đơn hàng
├── checkout_functions.php       # Các hàm dùng chung
├── README.md                    # Tài liệu hướng dẫn
└── payment/                     # Thư mục thanh toán
    ├── cash.php                 # Thanh toán tiền mặt
    ├── vnpay.php                # Thanh toán VNPay QR
    ├── check-payment.php        # Kiểm tra trạng thái thanh toán
    ├── check-status.php         # Kiểm tra trạng thái đơn hàng
    └── qr_generator.php         # Tạo mã QR
```

## 🚀 Cách sử dụng

### 1. Truy cập trang thanh toán
```
http://localhost/SE_Final/customer/checkout/
```

### 2. Điền thông tin
- Thông tin khách hàng (tự động điền nếu đã đăng nhập)
- Địa chỉ giao hàng
- Chọn phương thức thanh toán

### 3. Xử lý đơn hàng
- Hệ thống tự động tạo đơn hàng
- Chuyển hướng đến trang thanh toán tương ứng

## ⚙️ Cấu hình VNPay

### 1. Tạo file config
Tạo file `includes/config/vnpay_config.php`:

```php
<?php
// VNPay Configuration
define('VNP_TMN_CODE', 'YOUR_TMN_CODE');           // Mã website
define('VNP_HASH_SECRET', 'YOUR_HASH_SECRET');     // Secret key
define('VNP_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'); // URL thanh toán
define('VNP_RETURN_URL', 'http://localhost/SE_Final/customer/checkout/payment/vnpay_return.php'); // Return URL
define('VNP_API_URL', 'https://sandbox.vnpayment.vn/merchant_webapi/api/transaction'); // API URL
?>
```

### 2. Lấy thông tin từ VNPay
1. Đăng ký tài khoản tại [VNPay Merchant](https://sandbox.vnpayment.vn/)
2. Lấy TMN_CODE và HASH_SECRET từ dashboard
3. Cập nhật vào file config

## 🗄️ Database Schema

### Bảng orders
```sql
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_code VARCHAR(20) UNIQUE,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'vnpay') NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed', 'cancelled') DEFAULT 'pending',
    order_status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    customer_info JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Bảng order_items
```sql
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);
```

## 🔧 Tính năng

### ✅ Đã hoàn thành
- [x] Giao diện thanh toán responsive
- [x] Form validation đầy đủ
- [x] Tích hợp VNPay QR
- [x] Thanh toán tiền mặt
- [x] Tạo mã QR tự động
- [x] Kiểm tra trạng thái thanh toán
- [x] Xử lý đơn hàng tự động
- [x] Cập nhật tồn kho

### 🚧 Cần hoàn thiện
- [ ] Gửi email xác nhận
- [ ] Tích hợp SMS notification
- [ ] Webhook VNPay
- [ ] Báo cáo doanh thu
- [ ] Quản lý đơn hàng admin

## 📱 Responsive Design

- **Desktop**: Giao diện đầy đủ 2 cột
- **Tablet**: Giao diện 1 cột, form trên, summary dưới
- **Mobile**: Tối ưu cho màn hình nhỏ

## 🔒 Bảo mật

- CSRF protection
- Input validation
- SQL injection prevention
- Session management
- Payment verification

## 🐛 Troubleshooting

### Lỗi thường gặp:

1. **Không tạo được đơn hàng**
   - Kiểm tra kết nối database
   - Đảm bảo session hoạt động
   - Kiểm tra quyền ghi database

2. **VNPay không hoạt động**
   - Kiểm tra cấu hình VNPay
   - Đảm bảo URL callback chính xác
   - Kiểm tra TMN_CODE và HASH_SECRET

3. **QR Code không hiển thị**
   - Kiểm tra kết nối internet
   - Thử API khác trong qr_generator.php
   - Kiểm tra URL thanh toán

## 📞 Hỗ trợ

Nếu gặp vấn đề, vui lòng kiểm tra:
1. File cấu hình VNPay
2. Kết nối database
3. Session và authentication
4. Console browser để xem lỗi JavaScript

## 🔄 Flow hoạt động

1. **User** → Điền thông tin → Chọn phương thức thanh toán
2. **System** → Validate → Tạo đơn hàng → Lưu database
3. **VNPay** → Tạo URL thanh toán → Hiển thị QR code
4. **User** → Quét QR → Thanh toán
5. **VNPay** → Callback → Cập nhật trạng thái
6. **System** → Xác nhận → Gửi email

## 📝 Changelog

### Version 1.0
- Hoàn thành giao diện checkout
- Tích hợp VNPay QR
- Thanh toán tiền mặt
- Tạo mã QR tự động
- Validation đầy đủ

---

**Liên hệ**: Nếu cần hỗ trợ thêm, vui lòng tạo issue hoặc liên hệ developer.
