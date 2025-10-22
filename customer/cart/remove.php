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

$data = json_decode(file_get_contents('php://input'), true);
$product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
$size_id = isset($data['size_id']) ? (int)$data['size_id'] : null;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "DELETE FROM cart_items WHERE user_id = ? AND product_id = ?" . ($size_id ? " AND size_id = ?" : "");
$stmt = $conn->prepare($sql);
if ($size_id) {
    $stmt->bind_param('iii', $user_id, $product_id, $size_id);
} else {
    $stmt->bind_param('ii', $user_id, $product_id);
}
$stmt->execute();

// Sau khi xóa, lấy lại tổng số lượng sản phẩm tr giỏ hàng
$sql = "SELECT SUM(quantity) AS total FROM cart_items WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$count = $result['total'] ?? 0;

echo json_encode(['success' => true, 'message' => 'Đã xóa sản phẩm khỏi giỏ hàng']);
?>
