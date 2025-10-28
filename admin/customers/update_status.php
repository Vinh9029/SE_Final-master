<?php
session_start();
include_once '../../database/db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập.']);
    exit;
}

$user_id = $_POST['user_id'] ?? null;
$action = $_POST['action'] ?? null; // 'deactivate' or 'reactivate'

if (!$user_id || !in_array($action, ['deactivate', 'reactivate'])) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
    exit;
}

$new_status = ($action === 'deactivate') ? 1 : 0;
$message = ($action === 'deactivate') ? 'Vô hiệu hóa tài khoản thành công.' : 'Kích hoạt lại tài khoản thành công.';

try {
    $stmt = $conn->prepare("UPDATE users SET deactivated_account = ? WHERE user_id = ?");
    if (!$stmt) {
        throw new Exception("Lỗi khi chuẩn bị câu lệnh: " . $conn->error);
    }
    $stmt->bind_param("ii", $new_status, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy người dùng hoặc trạng thái không thay đổi.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}