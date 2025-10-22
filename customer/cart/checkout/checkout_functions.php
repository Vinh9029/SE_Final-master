<?php
/**
 * Checkout Functions
 * Các hàm xử lý thanh toán dùng chung
 */

// Tính tổng tiền đơn hàng
function calculateOrderTotal($user_id, $conn = null) {
    $total = 0;

    if ($conn) {
        try {
            $stmt = $conn->prepare("SELECT SUM(c.quantity * p.price) as total FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $total = $result['total'] ?? 0;
        } catch (Exception $e) {
            $total = calculateOrderTotalFromSession();
        }
    } else {
        $total = calculateOrderTotalFromSession();
    }

    return $total;
}

// Tính tổng từ session
function calculateOrderTotalFromSession() {
    $total = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            // You would need to get product price from database or session
            // This is a simplified version
            $total += $quantity * 100000; // Assuming default price
        }
    }
    return $total;
}

// Lấy thông tin giỏ hàng để hiển thị
function getCheckoutCartItems($user_id, $conn = null) {
    $cart_items = [];

    if ($conn) {
        try {
            $stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.image, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $cart_items[] = $row;
            }
        } catch (Exception $e) {
            $cart_items = getCheckoutCartFromSession();
        }
    } else {
        $cart_items = getCheckoutCartFromSession();
    }

    return $cart_items;
}

// Lấy giỏ hàng từ session
function getCheckoutCartFromSession() {
    $cart_items = [];
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            // You would need to get product details from database
            // This is a simplified version
            $cart_items[] = [
                'product_id' => $product_id,
                'name' => 'Product ' . $product_id,
                'price' => 100000,
                'quantity' => $quantity,
                'image' => 'uploads/products/default.jpg'
            ];
        }
    }
    return $cart_items;
}

// Validate thông tin thanh toán
function validateCheckoutData($data) {
    $errors = [];

    // Required fields
    $required_fields = ['full_name', 'phone', 'email', 'address', 'city', 'district', 'payment_method'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $errors[] = "Trường " . getFieldName($field) . " là bắt buộc";
        }
    }

    // Validate email
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    }

    // Validate phone
    if (!empty($data['phone']) && !preg_match('/^[0-9]{10,11}$/', $data['phone'])) {
        $errors[] = "Số điện thoại không hợp lệ";
    }

    return $errors;
}

// Lấy tên trường tiếng Việt
function getFieldName($field) {
    $names = [
        'full_name' => 'Họ và tên',
        'phone' => 'Số điện thoại',
        'email' => 'Email',
        'address' => 'Địa chỉ',
        'city' => 'Tỉnh/Thành phố',
        'district' => 'Quận/Huyện',
        'payment_method' => 'Phương thức thanh toán'
    ];

    return $names[$field] ?? $field;
}

// Tạo mã đơn hàng
function generateOrderCode() {
    return 'ORD' . date('Ymd') . rand(1000, 9999);
}

// Gửi email xác nhận đơn hàng
function sendOrderConfirmationEmail($order_id, $customer_info, $order_total) {
    $subject = "Xác nhận đơn hàng #" . $order_id;

    $message = "
    <html>
    <head>
        <title>Xác nhận đơn hàng</title>
    </head>
    <body>
        <h2>Cảm ơn bạn đã đặt hàng!</h2>
        <p>Đơn hàng của bạn đã được tiếp nhận và đang được xử lý.</p>

        <div style='background-color: #f5f5f5; padding: 15px; margin: 20px 0;'>
            <h3>Thông tin đơn hàng:</h3>
            <p><strong>Mã đơn hàng:</strong> {$order_id}</p>
            <p><strong>Khách hàng:</strong> {$customer_info['name']}</p>
            <p><strong>Email:</strong> {$customer_info['email']}</p>
            <p><strong>Điện thoại:</strong> {$customer_info['phone']}</p>
            <p><strong>Tổng tiền:</strong> " . number_format($order_total) . "đ</p>
        </div>

        <p>Chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất để xác nhận đơn hàng.</p>

        <p>Trân trọng,<br>Đội ngũ cửa hàng</p>
    </body>
    </html>
    ";

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: no-reply@cuahang.com" . "\r\n";

    // mail($customer_info['email'], $subject, $message, $headers);
}

// Tạo mã QR cho VNPay
function generateVNPayQR($amount, $order_id) {
    // This would integrate with VNPay QR generation
    // For now, return a placeholder
    return [
        'qr_code' => 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=vnpay_payment_' . $order_id,
        'payment_url' => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'
    ];
}

// Kiểm tra trạng thái thanh toán VNPay
function checkVNPayPaymentStatus($order_id) {
    // This would check payment status from VNPay
    // For now, return a mock response
    return [
        'status' => 'success',
        'transaction_id' => 'VNP' . $order_id . rand(1000, 9999),
        'amount' => 0, // Would get from VNPay response
        'message' => 'Thanh toán thành công'
    ];
}

// Cập nhật trạng thái đơn hàng
function updateOrderStatus($order_id, $status, $payment_status = null, $conn = null) {
    if (!$conn) {
        return false;
    }

    try {
        $sql = "UPDATE orders SET order_status = ?";
        $params = [$status];
        $types = "s";

        if ($payment_status) {
            $sql .= ", payment_status = ?";
            $params[] = $payment_status;
            $types .= "s";
        }

        $sql .= " WHERE id = ?";
        $params[] = $order_id;
        $types .= "i";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

// Lấy danh sách quận/huyện theo tỉnh thành
function getDistrictsByCity($city) {
    $districts = [
        'Hà Nội' => ['Ba Đình', 'Hoàn Kiếm', 'Tây Hồ', 'Cầu Giấy', 'Đống Đa', 'Hai Bà Trưng', 'Hoàng Mai', 'Thanh Xuân', 'Nam Từ Liêm', 'Bắc Từ Liêm'],
        'TP.HCM' => ['Quận 1', 'Quận 3', 'Quận 4', 'Quận 5', 'Quận 6', 'Quận 7', 'Quận 8', 'Quận 10', 'Quận 11', 'Quận 12', 'Thủ Đức', 'Bình Thạnh', 'Gò Vấp', 'Phú Nhuận', 'Tân Bình', 'Tân Phú'],
        'Đà Nẵng' => ['Hải Châu', 'Thanh Khê', 'Sơn Trà', 'Ngũ Hành Sơn', 'Liên Chiểu', 'Cẩm Lệ', 'Hòa Vang'],
        'Cần Thơ' => ['Ninh Kiều', 'Bình Thủy', 'Cái Răng', 'Ô Môn', 'Thốt Nốt'],
        'Hải Phòng' => ['Hồng Bàng', 'Ngô Quyền', 'Lê Chân', 'Hải An', 'Kiến An', 'Đồ Sơn', 'Dương Kinh', 'Thuỷ Nguyên']
    ];

    return $districts[$city] ?? [];
}

// Format địa chỉ giao hàng
function formatShippingAddress($address, $district, $city) {
    return trim($address . ', ' . $district . ', ' . $city);
}

// Tính phí vận chuyển
function calculateShippingFee($address, $total) {
    // Simple shipping calculation
    $base_fee = 30000; // 30,000 VND
    $free_shipping_threshold = 500000; // 500,000 VND

    if ($total >= $free_shipping_threshold) {
        return 0;
    }

    // Add extra fee for distant areas
    $distant_cities = ['Hà Nội', 'TP.HCM', 'Đà Nẵng'];
    if (in_array($address['city'], $distant_cities)) {
        $base_fee += 10000;
    }

    return $base_fee;
}

// Áp dụng mã giảm giá
function applyDiscountCode($code, $total) {
    $discounts = [
        'WELCOME10' => 0.1, // 10% discount
        'SAVE20' => 0.2,    // 20% discount
        'NEWUSER' => 50000  // 50,000 VND discount
    ];

    if (isset($discounts[$code])) {
        $discount = $discounts[$code];
        if ($discount < 1) {
            // Percentage discount
            return $total * (1 - $discount);
        } else {
            // Fixed amount discount
            return max(0, $total - $discount);
        }
    }

    return $total;
}
?>
