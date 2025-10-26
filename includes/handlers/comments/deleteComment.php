<?php
include_once __DIR__ . '/../../../database/db_connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để thực hiện hành động này.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;

if (!$comment_id) {
    echo json_encode(['success' => false, 'message' => 'ID bình luận không hợp lệ.']);
    exit;
}

// 2. Build the query based on user role
// Admin can delete any comment. A regular user can only delete their own.
$sql = "DELETE FROM comments WHERE id = ?";
$params = [$comment_id];
$types = 'i';

if (!$is_admin) {
    $sql .= " AND user_id = ?";
    $params[] = $user_id;
    $types .= 'i';
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Đã xóa bình luận thành công.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể xóa bình luận hoặc bạn không có quyền.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa bình luận.']);
}

$stmt->close();
$conn->close();
?>