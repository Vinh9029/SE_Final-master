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

// Nhận dữ liệu từ cả JSON và POST
if ($_SERVER['CONTENT_TYPE'] === 'application/json' || strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $data = json_decode(file_get_contents('php://input'), true);
    $product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
    $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;
    $size_id = isset($data['size_id']) ? (int)$data['size_id'] : null;
} else {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $size_id = isset($_POST['size_id']) ? (int)$_POST['size_id'] : null;
}

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit();
}
// Kiểm tra sản phẩm
$sql = "SELECT product_id, name, price FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
    exit();
}
// Kiểm tra size nếu có
$extra_price = 0;
if ($size_id) {
    $sql = "SELECT size_name, extra_price FROM product_sizes WHERE size_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $size_id, $product_id);
    $stmt->execute();
    $size = $stmt->get_result()->fetch_assoc();
    if (!$size) {
        echo json_encode(['success' => false, 'message' => 'Size không hợp lệ']);
        exit();
    }
    $extra_price = (float)$size['extra_price'];
}
// Kiểm tra sản phẩm đã có trong giỏ hàng chưa
$sql = "SELECT cart_item_id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?" . ($size_id ? " AND size_id = ?" : "");
$stmt = $conn->prepare($sql);
if ($size_id) {
    $stmt->bind_param('iii', $user_id, $product_id, $size_id);
} else {
    $stmt->bind_param('ii', $user_id, $product_id);
}
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();
if ($existing) {
    // Nếu đã có thì cập nhật số lượng
    $new_qty = $existing['quantity'] + $quantity;
    $sql = "UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $new_qty, $existing['cart_item_id']);
    $stmt->execute();
} else {
    // Nếu chưa có thì thêm mới
    $sql = "INSERT INTO cart_items (user_id, product_id, quantity, size_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiii', $user_id, $product_id, $quantity, $size_id);
    $stmt->execute();
}
// Sau khi thêm/cập nhật sản phẩm, lấy lại tổng số lượng sản phẩm trong giỏ hàng
$sql = "SELECT SUM(quantity) AS total FROM cart_items WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$count = $result['total'] ?? 0;

// Sau khi xử lý xong, reload lại trang để cập nhật giao diện
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Đã thêm vào giỏ hàng', 'count' => (int)$count]);
?>
