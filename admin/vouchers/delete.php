<?php
// Bắt đầu session và kiểm tra quyền admin trước
include_once __DIR__ . '/../admin_auth.php';

include_once __DIR__ . '/../../database/db_connection.php'; // Kết nối DB sau khi xác thực
global $conn;

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM vouchers WHERE voucher_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Xóa voucher thành công!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Xóa voucher thất bại.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Thiếu ID voucher.']);
}
exit;
?>