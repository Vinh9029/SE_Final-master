<?php
session_start();
include_once __DIR__ . '/../../../database/db_connection.php';
include_once __DIR__ . '/../../../includes/handlers/notification/createNotification.php';
header('Content-Type: application/json');
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit;
}
$full_name = $_POST['full_name'] ?? '';
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
$delivery_method = $_POST['delivery_method'] ?? 'pickup';
$address = $_POST['address'] ?? '';
$city = $_POST['city'] ?? '';
$district = $_POST['district'] ?? '';
$notes = $_POST['notes'] ?? '';
$payment_method = $_POST['payment_method'] ?? 'cash';
// Lấy giỏ hàng
$sql = 'SELECT ci.*, p.price, ps.extra_price FROM cart_items ci JOIN products p ON ci.product_id = p.product_id LEFT JOIN product_sizes ps ON ci.size_id = ps.size_id WHERE ci.user_id = ?';
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$cart_items = [];
$total = 0;
while ($item = $res->fetch_assoc()) {
    $item_price = $item['price'] + ($item['extra_price'] ?? 0);
    $cart_items[] = $item;
    $total += $item_price * $item['quantity'];
}
if (empty($cart_items)) {
    echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
    exit;
}

// Lấy voucher từ session và tính toán giảm giá
$voucher_code = $_SESSION['voucher_code'] ?? null;
$voucher_discount_value = $_SESSION['voucher_discount_value'] ?? 0;
$voucher_type = $_SESSION['voucher_type'] ?? 'cash';
$voucher_min_order = (float) ($_SESSION['voucher_min_order'] ?? 0);
$discount_amount = 0;

if ($voucher_code && $total >= $voucher_min_order) {
    if ($voucher_type === 'percent') {
        $discount_amount = round($total * $voucher_discount_value / 100);
    } else { // 'fixed'
        $discount_amount = $voucher_discount_value;
    }
}

$final_total = $total - $discount_amount;

// Cập nhật thông tin cá nhân nếu có thay đổi
$stmt = $conn->prepare('SELECT full_name, phone, address FROM users WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
if ($user) {
    if ($user['full_name'] !== $full_name || $user['phone'] !== $phone || $user['address'] !== $address) {
        $stmt2 = $conn->prepare('UPDATE users SET full_name = ?, phone = ?, address = ? WHERE user_id = ?');
        $stmt2->bind_param('sssi', $full_name, $phone, $address, $user_id);
        $stmt2->execute();
        $stmt2->close();
    }
}

// Tạo đơn hàng
$sql = 'INSERT INTO orders (user_id, order_date, status, total, voucher_code, discount_amount) VALUES (?, NOW(), "pending", ?, ?, ?)';
$stmt = $conn->prepare($sql);
$stmt->bind_param('idssd', $user_id, $final_total, $voucher_code, $discount_amount);
$stmt->execute();
$order_id = $conn->insert_id;

// Create notification for new order
if ($order_id) {
    create_notification(
        $conn,
        $user_id,
        'order_status',
        "Đơn hàng mới #{$order_id}",
        'Đơn hàng của bạn đã được tạo thành công và đang chờ xử lý.',
        $order_id,
        '/customer/orders.php'
    );
}

// Lưu chi tiết đơn hàng
foreach ($cart_items as $item) {
    $sql = 'INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiid', $order_id, $item['product_id'], $item['quantity'], $item['price']);
    $stmt->execute();
}
// Xóa giỏ hàng
$sql = 'DELETE FROM cart_items WHERE user_id = ?';
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
// Trả về kết quả
if ($payment_method === 'vnpay') {
    // Chuyển hướng sang VNPay
    $redirect_url = 'payment/vnpay.php?order_id=' . $order_id;
    echo json_encode(['success' => true, 'redirect_url' => $redirect_url]);
} else {
    // Chuyển hướng sang trang xác nhận thanh toán tiền mặt
    $redirect_url = 'payment/cash.php?order_id=' . $order_id;
    echo json_encode(['success' => true, 'redirect_url' => $redirect_url]);
}
exit;
?>
