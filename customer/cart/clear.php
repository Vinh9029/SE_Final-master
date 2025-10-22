<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit();
}

include_once '../../database/db_connection.php';

$user_id = $_SESSION['user_id'];

$sql = "DELETE FROM cart_items WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();

// Sau khi xóa, tổng số lượng là 0
echo json_encode(['success' => true, 'message' => 'Đã xóa tất cả sản phẩm khỏi giỏ hàng']);
?>
