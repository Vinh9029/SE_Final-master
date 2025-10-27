# Tổng Quan Dự Án SE_Final-master: Old Flavour Coffee

## Giới Thiệu Dự Án

**Old Flavour Coffee** (còn gọi là **The Old Flavour**) là một hệ thống website thương mại điện tử toàn diện cho quán cà phê, được phát triển bằng PHP, HTML, CSS, JavaScript và MySQL. Dự án này mô phỏng một quán cà phê cổ điển với không gian ấm cúng, kết hợp công nghệ hiện đại để cung cấp trải nghiệm mua sắm trực tuyến hoàn chỉnh.

### Mục Tiêu Dự Án
- Tạo nền tảng thương mại điện tử cho quán cà phê với giao diện thân thiện và dễ sử dụng
- Quản lý toàn diện các chức năng bán hàng, quản lý khách hàng và nội dung
- Tích hợp các tính năng tương tác như blog, bình luận và vòng quay may mắn
- Cung cấp hệ thống quản trị mạnh mẽ cho admin

## Kiến Trúc Hệ Thống

### Công Nghệ Sử Dụng
- **Backend**: PHP 7+ với MySQL
- **Frontend**: HTML5, CSS3 (Tailwind CSS), JavaScript (Vanilla JS)
- **Database**: MySQL với schema được thiết kế chi tiết
- **Thư Viện**: PHPMailer, Chart.js, SweetAlert2, Font Awesome
- **Môi Trường**: XAMPP (Apache, MySQL, PHP)

### Cấu Trúc Thư Mục
```
SE_Final-master/
├── admin/                 # Giao diện quản trị
├── assets/                # Tài nguyên tĩnh (CSS, JS, hình ảnh)
├── customer/              # Chức năng khách hàng
├── database/              # Kết nối DB và schema
├── includes/              # Các thành phần dùng chung
├── login/                 # Xác thực người dùng
├── menus/                 # Hiển thị menu sản phẩm
├── pages/                 # Các trang tĩnh
├── Photos/                # Hình ảnh sản phẩm và giao diện
├── composer.json          # Quản lý dependencies PHP
├── config.php             # Cấu hình hệ thống
└── index.php              # Trang chủ
```

## Chức Năng Chính

### 1. Giao Diện Khách Hàng (Frontend)

#### Trang Chủ (`index.php`)
- **Slider quảng cáo**: Hiển thị banner với hiệu ứng chuyển động
- **Giới thiệu quán**: Câu chuyện thương hiệu, không gian quán, lời chào từ người sáng lập
- **Câu chuyện nhỏ**: Các bài viết ngắn về trải nghiệm quán
- **Đánh giá khách hàng**: Carousel hiển thị feedback với hiệu ứng tự động
- **Loading screen**: Hiệu ứng loading khi vào trang
- **Popup đăng ký**: Tự động hiển thị sau 5 giây

#### Menu Sản Phẩm (`menus/menus.php`)
- **Danh mục sản phẩm**: Tabs phân loại (Cà phê, Ăn kem, Nước đặc biệt, Trà sữa)
- **Tìm kiếm**: Thanh search với dropdown gợi ý sản phẩm
- **Sắp xếp**: Theo tên (A-Z/Z-A), giá (thấp-cao/cao-thấp), mặc định
- **Phân trang**: Hiển thị 9 sản phẩm/trang
- **Yêu thích**: Nút heart để thêm/xóa khỏi danh sách yêu thích
- **Chi tiết sản phẩm**: Trang riêng với thông tin đầy đủ

#### Giỏ Hàng (`customer/cart/`)
- **Thêm sản phẩm**: Từ trang chi tiết hoặc menu
- **Cập nhật số lượng**: Tăng/giảm số lượng, xóa item
- **Áp dụng voucher**: Nhập mã giảm giá
- **Thanh toán**: Chuyển đến trang checkout

#### Thanh Toán (`customer/cart/checkout/index.php`)
- **Thông tin khách hàng**: Họ tên, số điện thoại, email
- **Phương thức nhận hàng**:
  - Nhận tại quầy
  - Giao tận nơi (có phí ship 15.000đ)
- **Địa chỉ giao hàng**: Tỉnh/thành phố, quận/huyện, ghi chú
- **Phương thức thanh toán**:
  - Tiền mặt (thanh toán khi nhận)
  - VNPay QR (quét mã thanh toán online)
- **Tóm tắt đơn hàng**: Hiển thị tổng tiền, giảm giá, phí ship

#### Tài Khoản Khách Hàng (`customer/account.php`)
- **Đăng ký/Đăng nhập**: Hệ thống xác thực với OTP
- **Quên mật khẩu**: Reset password qua email
- **Hồ sơ cá nhân**: Cập nhật thông tin, avatar
- **Đơn hàng**: Lịch sử và chi tiết đơn hàng
- **Voucher**: Danh sách mã giảm giá
- **Yêu thích**: Sản phẩm đã lưu
- **Thông báo**: Push notification về đơn hàng, điểm tích lũy
- **Cài đặt**: Thay đổi mật khẩu, thông tin cá nhân

#### Blog (`pages/blogs/index.php`)
- **Xem blog**: Danh sách bài viết được duyệt
- **Viết blog**: Tạo bài viết mới (cần duyệt)
- **Quản lý blog cá nhân**: Chỉnh sửa, xóa bài viết của mình
- **Chi tiết blog**: Đọc full bài viết với bình luận

#### Bình Luận & Phản Ứng (`includes/handlers/comments/`)
- **Bình luận**: Trên sản phẩm và blog
- **Phản ứng**: Like, love, haha, wow, sad, angry
- **Quản lý**: Admin duyệt/từ chối bình luận


#### Khuyến Mãi (`pages/promotion.php`)
- **Combo đặc biệt**: Giảm giá combo
- **Giờ vàng**: Mua 1 tặng 1
- **Miễn phí ship**: Cho đơn từ 100k

### 2. Hệ Thống Quản Trị (Admin Panel)

#### Dashboard (`admin/dashboard.php`)
- **Thống kê tổng quan**: Sản phẩm, đơn hàng, khách hàng, doanh thu tháng
- **Biểu đồ**: Đơn hàng và doanh thu hàng ngày
- **Sidebar điều hướng**: Menu quản lý với AJAX loading

#### Quản Lý Sản Phẩm (`admin/products/`)
- **CRUD sản phẩm**: Thêm, sửa, xóa, xem danh sách
- **Danh mục**: Quản lý categories
- **Upload hình ảnh**: Hỗ trợ nhiều định dạng

#### Quản Lý Đơn Hàng (`admin/orders/`)
- **Xem đơn hàng**: Chi tiết, trạng thái, tổng tiền
- **Cập nhật trạng thái**: Pending → Processing → Completed/Cancelled
- **Lọc và tìm kiếm**: Theo ngày, trạng thái

#### Quản Lý Khách Hàng (`admin/customers/`)
- **Xem danh sách**: Thông tin cá nhân, lịch sử mua hàng
- **Chi tiết khách hàng**: Đơn hàng, voucher, điểm tích lũy

#### Quản Lý Blog (`admin/blog/`)
- **Duyệt bài viết**: Approve/Reject bài viết pending
- **Xem chi tiết**: Nội dung đầy đủ
- **Thống kê**: Số bài viết theo trạng thái

#### Quản Lý Bình Luận (`admin/comments/`)
- **Duyệt bình luận**: Approve/Reject
- **Chỉnh sửa**: Sửa nội dung bình luận
- **Lọc**: Theo trạng thái, sản phẩm/blog

#### Quản Lý Voucher (`admin/vouchers/manage.php`)
- **Tạo voucher**: Mã giảm giá thủ công
- **Theo dõi**: Sử dụng, hết hạn
- **Thống kê**: Hiệu quả chương trình

#### Báo Cáo (`admin/reports/`)
- **Doanh số**: Biểu đồ theo thời gian
- **Doanh thu**: Chi tiết theo ngày/tháng
- **Xuất báo cáo**: CSV hoặc PDF

### 3. Hệ Thống Thông Báo

#### Loại Thông Báo
- **Đơn hàng**: Cập nhật trạng thái
- **Welcome**: Chào mừng khách hàng mới
- **Loyalty Points**: Thông báo điểm tích lũy
- **Khuyến mãi**: Voucher mới

#### Chức Năng
- **Push notification**: Hiển thị real-time
- **Đánh dấu đã đọc**: Quản lý trạng thái
- **Mini dropdown**: Xem nhanh trong header

## Cơ Sở Dữ Liệu

### Các Bảng Chính

#### Users
- Thông tin khách hàng và admin
- Xác thực đăng nhập
- Hồ sơ cá nhân (avatar, ngày sinh, giới tính)

#### Products & Categories
- Sản phẩm với danh mục
- Kích thước và giá cộng thêm (size_id, extra_price)
- Hình ảnh và mô tả

#### Orders & Order_Items
- Đơn hàng với chi tiết sản phẩm
- Voucher áp dụng và giảm giá
- Trạng thái đơn hàng

#### Cart_Items
- Giỏ hàng tạm thời
- Size và số lượng

#### Blogs & Comments
- Hệ thống blog với bình luận
- Phản ứng emoji
- Duyệt nội dung

#### Notifications
- Hệ thống push notification
- Phân loại theo loại và người nhận

### Quan Hệ Cơ Sở Dữ Liệu
- **Users** ↔ **Orders**: 1-N
- **Products** ↔ **Order_Items**: 1-N
- **Users** ↔ **Cart_Items**: 1-N
- **Users** ↔ **Blogs**: 1-N
- **Blogs/Products** ↔ **Comments**: 1-N
- **Comments** ↔ **Comment_Reactions**: 1-N
- **Users** ↔ **Notifications**: 1-N

## Tính Năng Nâng Cao

### Bảo Mật
- **Mã hóa mật khẩu**: Sử dụng hashing
- **Session management**: Bảo vệ phiên đăng nhập
- **XSS protection**: Sanitize input
- **CSRF protection**: Token validation

### Tối Ưu Hiệu Suất
- **AJAX loading**: Tải trang không đồng bộ
- **Pagination**: Phân trang dữ liệu lớn
- **Caching**: Cache query thường dùng
- **Image optimization**: Nén hình ảnh

### Trải Nghiệm Người Dùng
- **Responsive design**: Tương thích mobile/desktop
- **Loading animations**: Hiệu ứng mượt mà
- **Toast notifications**: Thông báo không gián đoạn
- **Search autocomplete**: Gợi ý tìm kiếm

## Quy Trình Phát Triển

### Setup Môi Trường
1. Cài đặt XAMPP
2. Import database từ `database/schema.sql`
3. Chạy `composer install` để cài dependencies
4. Cấu hình `config.php` với thông tin DB

### Quy Trình Deploy
1. Upload files lên server
2. Cấu hình database production
3. Setup cron jobs cho notifications
4. Cấu hình email SMTP

## Kết Luận

Dự án **SE_Final-master** là một hệ thống thương mại điện tử hoàn chỉnh cho quán cà phê, tích hợp đầy đủ các chức năng cần thiết từ bán hàng đến quản trị. Với kiến trúc rõ ràng, giao diện thân thiện và tính năng phong phú, dự án đáp ứng được nhu cầu kinh doanh thực tế của một quán cà phê hiện đại.

### Điểm Mạnh
- **Đầy đủ chức năng**: Từ frontend đến admin panel
- **UX/UI tốt**: Giao diện đẹp, trải nghiệm mượt mà
- **Tính năng tương tác**: Blog, bình luận, quay thưởng
- **Bảo mật**: Xác thực và phân quyền rõ ràng

### Hướng Phát Triển Tương Lai
- Tích hợp thanh toán online thực tế
- Mobile app companion
- AI chatbot tư vấn
- Phân tích dữ liệu nâng cao
- Tích hợp với hệ thống POS quán

---

**Tác giả**: dxanonymous_9029  
**Ngày tạo**: 2025
**Phiên bản**: 1.0
