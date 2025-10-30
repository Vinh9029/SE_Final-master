<?php
include_once __DIR__ . '/../admin_auth.php';
include_once __DIR__ . '/../../database/db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
    exit;
}

$order_id = $_POST['order_id'] ?? null;
$new_status = $_POST['status'] ?? null;

if (!$order_id || !$new_status) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin đơn hàng hoặc trạng thái.']);
    exit;
}

$conn->begin_transaction();

try {
    // Cập nhật trạng thái đơn hàng
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    if (!$stmt) throw new Exception("Lỗi chuẩn bị câu lệnh cập nhật đơn hàng: " . $conn->error);
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();

    // Nếu đơn hàng bị hủy, hoàn lại voucher (nếu có)
    if ($new_status === 'cancelled') {
        // Lấy mã voucher từ đơn hàng
        $stmt_get_voucher = $conn->prepare("SELECT voucher_code FROM orders WHERE order_id = ? AND voucher_code IS NOT NULL AND voucher_code != ''");
        if (!$stmt_get_voucher) throw new Exception("Lỗi chuẩn bị câu lệnh lấy voucher: " . $conn->error);
        $stmt_get_voucher->bind_param("i", $order_id);
        $stmt_get_voucher->execute();
        $result = $stmt_get_voucher->get_result();
        
        if ($voucher_row = $result->fetch_assoc()) {
            $voucher_code = $voucher_row['voucher_code'];

            // Cập nhật lại trạng thái voucher thành 'active'
            $stmt_refund_voucher = $conn->prepare("UPDATE vouchers SET status = 'active' WHERE code = ?");
            if (!$stmt_refund_voucher) throw new Exception("Lỗi chuẩn bị câu lệnh hoàn voucher: " . $conn->error);
            $stmt_refund_voucher->bind_param("s", $voucher_code);
            $stmt_refund_voucher->execute();
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái đơn hàng thành công.']);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Order Status Update Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật trạng thái.']);
}

exit;