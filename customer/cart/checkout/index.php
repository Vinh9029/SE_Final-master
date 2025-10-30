<?php
session_start();
include_once __DIR__ . '/../../../database/db_connection.php';
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: ../../login/index.php');
    exit;
}
// Lấy thông tin user
$user_info = [];
$stmt = $conn->prepare('SELECT full_name, phone, email FROM users WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $user_info = $row;
}
// Lấy giỏ hàng
$cart_items = [];
$total = 0;
$sql = 'SELECT ci.*, p.name, p.price, p.image, ps.size_name, ps.extra_price FROM cart_items ci JOIN products p ON ci.product_id = p.product_id LEFT JOIN product_sizes ps ON ci.size_id = ps.size_id WHERE ci.user_id = ?';
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($item = $res->fetch_assoc()) {
    $item_price = $item['price'] + ($item['extra_price'] ?? 0);
    $item['price'] = $item_price;
    $cart_items[] = $item;
    $total += $item_price * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - Cửa hàng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #E6D3B1;
        }
        .cta-button {
            background: linear-gradient(135deg, #4B2E05 0%, #C4A35A 100%);
        }

        .cta-button:hover {
            background: linear-gradient(135deg, #C4A35A 0%, #4B2E05 100%);
        }
        .payment-method {
            transition: all 0.3s ease;
        }

        .payment-method:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .payment-method.active {
            border-color: #C4A35A !important;
            background-color: #fffbeb !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body>
    <!-- Header -->
    <?php include '../../../includes/header.php'; ?>

    <!-- Checkout Section -->
    <div class="checkout-container min-h-screen py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Checkout Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-yellow-800 to-yellow-900 p-6">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-credit-card text-white text-2xl"></i>
                                <h1 class="text-2xl font-bold text-white">Thông tin thanh toán</h1>
                            </div>
                        </div>

                        <form id="checkout-form" class="p-6">
                            <!-- Customer Information -->
                            <div class="mb-6">
                                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                                    <i class="fas fa-user text-yellow-800"></i>
                                    Thông tin khách hàng
                                </h2>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Họ và tên *</label>
                                        <input type="text" name="full_name" value="<?php echo $user_info['full_name'] ?? ''; ?>" required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Số điện thoại *</label>
                                        <input type="tel" name="phone" value="<?php echo $user_info['phone'] ?? ''; ?>" required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                                        <input type="email" name="email" value="<?php echo $user_info['email'] ?? ''; ?>" required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                    </div>
                                </div>
                            </div>

                            <!-- Shipping Information -->
                            <div class="mb-6">
                                <h2 class="text-xl font-bold text-yellow-900 mb-4 flex items-center gap-2">
                                    <i class="fas fa-shipping-fast text-yellow-800"></i>
                                    Phương thức nhận hàng
                                </h2>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Nhận tại quầy -->
                                    <div class="delivery-method payment-method border-2 border-gray-200 rounded-2xl p-4 cursor-pointer"
                                        onclick="selectDelivery('pickup', this)">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" name="delivery_method" value="pickup" class="accent-yellow-600"
                                                checked onclick="showAddress(false)">
                                            <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-store text-gray-600 text-xl"></i>
                                            </div>
                                            <div>
                                                <h3 class="font-bold text-gray-800">Nhận tại quầy</h3>
                                                <p class="text-sm text-gray-600">Đến quán nhận trực tiếp</p>
                                            </div>
                                        </label>
                                    </div>
                                    <!-- Giao tận nơi -->
                                    <div class="delivery-method payment-method border-2 border-gray-200 rounded-2xl p-4 cursor-pointer"
                                        onclick="selectDelivery('delivery', this)">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="radio" name="delivery_method" value="delivery" class="accent-yellow-600"
                                                onclick="showAddress(true)">
                                            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-truck text-orange-500 text-xl"></i>
                                            </div>
                                            <div>
                                                <h3 class="font-bold text-gray-800">Giao tận nơi</h3>
                                                <p class="text-sm text-gray-600">Giao hàng đến địa chỉ của bạn</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <!-- Địa chỉ giao hàng -->
                            <div id="address-card" class="mb-6 bg-yellow-50 p-6 rounded-2xl border border-yellow-200" style="display: none;">
                                <!-- Shipping Information -->
                                <div>
                                    <h2 class="text-xl font-bold text-yellow-900 mb-4 flex items-center gap-2">
                                        <i class="fas fa-truck text-yellow-800"></i>
                                        Địa chỉ giao hàng
                                    </h2>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Địa chỉ *</label>
                                            <input type="text" name="address"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                                placeholder="123 Đường ABC, Phường XYZ">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Tỉnh/Thành phố *</label>
                                            <select name="city" required
                                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                                <option value="">Chọn tỉnh/thành phố</option>
                                                <option value="Hà Nội">Hà Nội</option>
                                                <option value="TP.HCM" selected>TP.HCM</option>
                                                <option value="Đà Nẵng">Đà Nẵng</option>
                                                <option value="Cần Thơ">Cần Thơ</option>
                                                <option value="Hải Phòng">Hải Phòng</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Quận/Huyện *</label>
                                            <select name="district" required
                                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                                <option value="">Chọn quận/huyện</option>
                                                <option value="Quận 1" selected>Quận 1</option>
                                                <option value="Quận 3">Quận 3</option>
                                                <option value="Quận 7">Quận 7</option>
                                                <option value="Thủ Đức">Thủ Đức</option>
                                                <option value="Bình Thạnh">Bình Thạnh</option>
                                            </select>
                                        </div>

                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Ghi chú</label>
                                            <textarea name="notes" rows="3"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                                placeholder="Ghi chú về đơn hàng..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Payment Methods -->
                            <div class="mb-6">
                                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                                    <i class="fas fa-money-bill-wave text-yellow-800"></i>
                                    Phương thức thanh toán
                                </h2>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Cash Payment -->
                                    <div class="payment-method border-2 border-gray-200 rounded-2xl p-4 cursor-pointer" onclick="selectPayment('cash', this)">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-money-bill-alt text-green-600 text-xl"></i>
                                            </div>
                                            <div>
                                                <h3 class="font-bold text-gray-800">Tiền mặt</h3>
                                                <p class="text-sm text-gray-600">Thanh toán khi nhận hàng</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- VNPay Payment -->
                                    <div class="payment-method border-2 border-gray-200 rounded-2xl p-4 cursor-pointer" onclick="selectPayment('vnpay', this)">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-qrcode text-blue-600 text-xl"></i>
                                            </div>
                                            <div>
                                                <h3 class="font-bold text-gray-800">VNPay QR</h3>
                                                <p class="text-sm text-gray-600">Quét mã QR để thanh toán</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="payment_method" id="payment_method" required>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                                <a href="../index.php" class="text-gray-600 hover:text-yellow-800 font-semibold transition-colors flex items-center gap-2"><i class="fas fa-arrow-left"></i> Quay lại giỏ hàng</a>
                                <button type="submit" class="cta-button text-white px-8 py-4 rounded-full font-bold text-lg shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2">
                                    <i class="fas fa-shopping-cart mr-2"></i>
                                    Đặt hàng ngay
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-xl p-6 sticky top-24">
                        <h2 class="text-2xl font-bold text-brown mb-6 flex items-center gap-2">
                            <i class="fas fa-receipt text-yellow-800"></i>
                            Tóm tắt đơn hàng
                        </h2>

                        <!-- Order Items -->
                        <div class="space-y-4 mb-6 max-h-80 overflow-y-auto pr-2">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="flex items-center gap-3 p-3 bg-yellow-50 rounded-xl">
                                    <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center">
                                        <img src="<?php echo $base_url . '/' . ($item['image'] ?: 'Photos/placeholder.png'); ?>" class="w-12 h-12 object-cover rounded-lg" alt="<?php echo htmlspecialchars($item['name']); ?>" />
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-medium text-sm leading-tight"><?php echo htmlspecialchars($item['name']); ?></h4>
                                        <?php if (!empty($item['size_name'])) : ?>
                                            <p class="text-xs text-gray-500 font-semibold">Size: <?php echo htmlspecialchars($item['size_name']); ?></p>
                                        <?php endif; ?>
                                        <p class="text-xs text-gray-600 mt-1">
                                            SL: <span class="font-bold"><?php echo $item['quantity']; ?></span>
                                        </p>
                                    </div>
                                    <div class="text-right font-semibold">
                                        <p class="font-bold text-brown"><?php echo number_format($item['price'] * $item['quantity']); ?>đ</p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <!-- Voucher & Order Total -->
                        <?php 
                        // Giả sử voucher đã áp dụng lưu trong session
                        $voucher_code = $_SESSION['voucher_code'] ?? null;
                        $voucher_discount_value = $_SESSION['voucher_discount_value'] ?? 0;
                        $voucher_type = $_SESSION['voucher_type'] ?? 'cash';
                        $voucher_min_order = (float) ($_SESSION['voucher_min_order'] ?? 0);
                        $discount = 0;
                        if ($voucher_code && $total >= $voucher_min_order) {
                            if ($voucher_type === 'percent') {
                                $discount = round($total * $voucher_discount_value / 100);
                            } else {
                                $discount = $voucher_discount_value;
                            }
                        }
                        $shipping_fee = 0;
                        if (isset($_POST['delivery_method']) && $_POST['delivery_method'] === 'delivery') {
                            $shipping_fee = 15000;
                        }
                        $total_after = $total - $discount + $shipping_fee;
                        ?>
                        <div class="border-t-2 border-dashed border-yellow-200 pt-4 space-y-2">
                            <div class="flex justify-between items-center text-gray-600">
                                <span>Tạm tính:</span>
                                <span class="font-semibold" id="order-total"><?php echo number_format($total); ?>đ</span>
                            </div>
                            <?php if ($voucher_code && $total >= $voucher_min_order) : ?>
                                <div class="flex justify-between items-center text-green-600 font-semibold">
                                    <span>Giảm giá (<?php echo htmlspecialchars($voucher_code); ?>):</span>
                                    <span class="font-bold" id="order-discount">- <?php echo number_format($discount); ?>đ</span>
                                </div>
                            <?php elseif ($voucher_code) : ?>
                                <div class="flex justify-between items-center text-red-600">
                                    <span>Voucher không hợp lệ:</span>
                                    <span class="font-bold"><?php echo htmlspecialchars($voucher_code); ?></span>
                                </div>
                                <div class="text-xs text-red-500 text-right">Đơn hàng chưa đạt giá trị tối thiểu <?php echo number_format($voucher_min_order); ?>đ</div>
                            <?php endif; ?>
                            <div class="flex justify-between items-center text-gray-600" id="shipping-row" style="display: none;">
                                <span>Phí giao hàng:</span>
                                <span class="font-semibold" id="order-shipping"></span>
                            </div>
                            <div class="border-t pt-3 mt-3 flex justify-between items-center text-xl font-bold">
                                <span class="text-brown">Thành tiền:</span>
                                <span class="text-yellow-800" id="order-total-after"><?php echo number_format($total_after); ?>đ</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../../../includes/footer.php'; ?>

    <script>
        // --- UI/UX Functions ---
        function showMessage(msg, type = 'success') {
            let msgBox = document.getElementById('checkout-message');
            if (!msgBox) {
                msgBox = document.createElement('div');
                msgBox.id = 'checkout-message';
                msgBox.className = 'fixed top-6 left-1/2 transform -translate-x-1/2 z-[9999] px-6 py-3 rounded-xl shadow-lg text-lg font-bold text-white transition-all duration-300';
                document.body.appendChild(msgBox);
            }
            msgBox.textContent = msg;
            // Sử dụng màu sắc phù hợp với thương hiệu
            msgBox.style.background = type === 'success' ? 'linear-gradient(90deg, #43e97b 0%, #38f9d7 100%)' : 'linear-gradient(90deg, #ff6a6a 0%, #ee5a24 100%)';
            msgBox.style.opacity = '1';
            msgBox.style.transform = 'translate(-50%, 0)';
            setTimeout(() => {
                msgBox.style.opacity = '0';
                msgBox.style.transform = 'translate(-50%, -20px)';
            }, 2500);
        }

        // Ẩn/hiện địa chỉ giao hàng
        function showAddress(show) {
            const addressCard = document.getElementById('address-card');
            const addressInput = addressCard.querySelector('input[name="address"]');
            const cityInput = addressCard.querySelector('select[name="city"]');
            const districtInput = addressCard.querySelector('select[name="district"]');

            addressCard.style.display = show ? 'block' : 'none';
            if (show) {
                addressInput.setAttribute('required', 'required');
                cityInput.setAttribute('required', 'required');
                districtInput.setAttribute('required', 'required');
            } else {
                addressInput.removeAttribute('required');
                cityInput.removeAttribute('required');
                districtInput.removeAttribute('required');
            }
        }
        // Mặc định ẩn địa chỉ giao hàng
        showAddress(false);

        function selectDelivery(method, element) {
            document.querySelectorAll('.delivery-method').forEach(el => el.classList.remove('active'));
            element.classList.add('active');
        }

        // Select payment method
        function selectPayment(method, element) {
            // Remove active class from all payment methods
            document.querySelectorAll('.payment-method').forEach(pm => {
                pm.classList.remove('active');
            });

            // Add active class to selected method
            element.classList.add('active');

            // Set payment method value
            document.getElementById('payment_method').value = method;
        }

        // Form submission
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const paymentMethod = document.getElementById('payment_method').value;
            console.log('Submit checkout, paymentMethod:', paymentMethod);
            if (!paymentMethod) {
                showMessage('Vui lòng chọn phương thức thanh toán', 'error');
                return;
            }

            // Submit form data
            const formData = new FormData(this);

            fetch('process.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Response:', data);
                    if (data.success && data.redirect_url) { 
                        const modalHtml = `
                        <div id="successModal" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;z-index:9999;">
                            <div style="background:#fff;border-radius:16px;padding:32px 24px;box-shadow:0 8px 32px 0 rgba(31,38,135,0.18);display:flex;flex-direction:column;align-items:center;">
                                <i class="fa-solid fa-circle-check" style="font-size:3rem;color:#4ade80;margin-bottom:12px;"></i>
                                <div style="font-size:1.2rem;font-weight:600;color:#16a34a;margin-bottom:8px;">Đặt hàng thành công!</div>
                                <div style="color:#555;margin-bottom:18px;">Đang chuyển hướng đến trang xác nhận...</div>
                                <div class="loader" style="width:40px;height:40px;border:4px solid #f3f3f3;border-top:4px solid #fc466b;border-radius:50%;animation:spin 1s linear infinite;"></div>
                            </div>
                        </div>
                        <style>@keyframes spin{0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}</style>`;
                        document.body.insertAdjacentHTML('beforeend', modalHtml);
                        setTimeout(function(){window.location.href = data.redirect_url;}, 1800);
                    } else {
                        showMessage(data.message || 'Có lỗi xảy ra khi xử lý đơn hàng.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('Có lỗi xảy ra khi xử lý đơn hàng.', 'error');
                });
        });

        // Auto-fill district based on city
        document.querySelector('[name="city"]').addEventListener('change', function() {
            const districtSelect = document.querySelector('[name="district"]');
            const districts = {
                'Hà Nội': ['Ba Đình', 'Hoàn Kiếm', 'Tây Hồ', 'Cầu Giấy', 'Đống Đa'],
                'TP.HCM': ['Quận 1', 'Quận 3', 'Quận 7', 'Thủ Đức', 'Bình Thạnh'],
                'Đà Nẵng': ['Hải Châu', 'Thanh Khê', 'Sơn Trà', 'Ngũ Hành Sơn'],
                'Cần Thơ': ['Ninh Kiều', 'Bình Thủy', 'Cái Răng', 'Ô Môn'],
                'Hải Phòng': ['Hồng Bàng', 'Ngô Quyền', 'Lê Chân', 'Hải An']
            };

            districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
            if (districts[this.value]) {
                districts[this.value].forEach(district => {
                    districtSelect.innerHTML += `<option value="${district}">${district}</option>`;
                });
            }
        });

        // --- Order Summary Calculation ---
        const totalFromServer = <?php echo json_encode($total); ?>;
        const discountFromServer = <?php echo json_encode($discount); ?>;
        let currentShippingFee = 0;

        function updateOrderSummary() {
            const total = totalFromServer;
            const discount = discountFromServer;
            const shipping = currentShippingFee;
            const finalTotal = total - discount + shipping;

            const shippingRow = document.getElementById('shipping-row');
            const shippingValueEl = document.getElementById('order-shipping');
            const totalAfterEl = document.getElementById('order-total-after');
            const totalEl = document.getElementById('order-total');

            if (shipping > 0) {
                shippingValueEl.textContent = new Intl.NumberFormat('vi-VN').format(shipping) + 'đ';
                shippingRow.style.display = 'flex';
            } else {
            }

            totalAfterEl.textContent = new Intl.NumberFormat('vi-VN').format(finalTotal) + 'đ';
        }

        // Sự kiện chọn phương thức giao hàng
        const deliveryRadios = document.querySelectorAll('input[name="delivery_method"]');
        deliveryRadios.forEach(radio => {
            radio.addEventListener('change', function() {

                // Calculation
                if (this.value === 'delivery') {
                    currentShippingFee = 15000;
                } else {
                    currentShippingFee = 0;
                }
                updateOrderSummary();
            });
        });
        // Khởi tạo lại khi load và chọn mặc định
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('input[name="delivery_method"][value="pickup"]').closest('.delivery-method').classList.add('active');
            document.querySelector('input[name="payment_method"][value="cash"]').closest('.payment-method').classList.add('active');
        });
        updateOrderSummary();
    </script>
</body>

</html>