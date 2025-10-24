<?php
session_start();
include_once __DIR__ . '/../database/db_connection.php';
include_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['id'] ?? 0;

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Mã đơn hàng không hợp lệ.']);
    exit;
}

// Fetch order details
// Assuming `orders` table has `voucher_code` and `discount_amount` columns
$stmt = $conn->prepare("SELECT order_id, order_date, status, total, voucher_code, discount_amount FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng.']);
    exit;
}

// Fetch order items
$stmt = $conn->prepare(
    "SELECT oi.quantity, oi.price, oi.take_note, p.name, p.image, ps.size_name
     FROM order_items oi 
     JOIN products p ON oi.product_id = p.product_id 
     LEFT JOIN product_sizes ps ON oi.size_id = ps.size_id
     WHERE oi.order_id = ?"
);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
$items = $items_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode([
    'success' => true, 
    'order' => $order, 
    'items' => $items,
    'base_url' => $base_url // Pass base_url for image paths
]);
?>