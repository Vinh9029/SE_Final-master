<?php
include_once __DIR__ . '/../../database/db_connection.php';
session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thực hiện hành động này.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$comment_id = $_POST['comment_id'] ?? null;

if (!$comment_id || !filter_var($comment_id, FILTER_VALIDATE_INT)) {
    echo json_encode(['success' => false, 'message' => 'ID bình luận không hợp lệ.']);
    exit;
}

// Admin can delete any comment. Users can only delete their own.
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
    // Check if any row was actually deleted
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Đã xóa bình luận thành công.']);
    } else {
        // This happens if the comment doesn't exist or the user doesn't have permission
        echo json_encode(['success' => false, 'message' => 'Không thể xóa bình luận hoặc bạn không có quyền.']);
    }
} else {
    // Database execution error
    error_log("Failed to delete comment: " . $stmt->error); // Ghi log lỗi
    echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa bình luận.']);
}

$stmt->close();
$conn->close();