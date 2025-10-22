<?php
session_start();
include_once __DIR__ . '/../../../database/db_connection.php';
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
$sql = 'INSERT INTO orders (user_id, order_date, status, total) VALUES (?, NOW(), "pending", ?)';
$stmt = $conn->prepare($sql);
$stmt->bind_param('id', $user_id, $total);
$stmt->execute();
$order_id = $conn->insert_id;
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
    $payment_url = 'payment/vnpay.php?order_id=' . $order_id;
    echo json_encode(['success' => true, 'payment_url' => $payment_url]);
} else {
    echo json_encode(['success' => true]);
}
exit;
?>
