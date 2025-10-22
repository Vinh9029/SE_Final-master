<?php
session_start();
include_once __DIR__ . '/../../database/db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$voucher_code = $data['voucher_code'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$voucher_code) {
    // Nếu mã voucher là null, người dùng muốn xóa voucher đã áp dụng
    unset($_SESSION['voucher_code']);
    unset($_SESSION['voucher_discount']);
    unset($_SESSION['voucher_type']);
    unset($_SESSION['voucher_min_order']);
    
    echo json_encode(['success' => true, 'message' => 'Đã bỏ chọn voucher.']); // Trả về thành công
    exit;
}

// Check if voucher is valid for the user
$stmt = $conn->prepare("SELECT * FROM vouchers WHERE user_id = ? AND code = ? AND status = 'active' AND (expires_at IS NULL OR expires_at >= NOW())");
$stmt->bind_param('is', $user_id, $voucher_code);
$stmt->execute();
$result = $stmt->get_result();

if ($voucher = $result->fetch_assoc()) {
    // Store voucher details in session
    $_SESSION['voucher_code'] = $voucher['code'];
    $_SESSION['voucher_discount'] = $voucher['discount_percent']; // Assuming percent for now
    $_SESSION['voucher_type'] = $voucher['discount_percent'] > 0 ? 'percent' : 'cash';
    $_SESSION['voucher_min_order'] = $voucher['min_order_value'];

    echo json_encode(['success' => true, 'voucher' => $voucher]);
} else {
    echo json_encode(['success' => false, 'message' => 'Mã voucher không hợp lệ hoặc đã hết hạn.']);
}
$stmt->close();