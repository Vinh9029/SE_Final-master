<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập']);
    exit();
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Mã đơn hàng không hợp lệ']);
    exit();
}

try {
    include_once __DIR__ . '/../../../../database/db_connection.php';

    // Kiểm tra quyền truy cập đơn hàng
    $stmt = $conn->prepare("SELECT status FROM orders WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if (!$order) {
        echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng']);
        exit();
    }

    echo json_encode([
        'status' => $order['status']
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
}
?>
