<?php
include_once __DIR__ . '/../../database/db_connection.php';
session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để thực hiện hành động này.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$comment_id = $_POST['comment_id'] ?? null;

if (!$comment_id) {
    echo json_encode(['success' => false, 'message' => 'ID bình luận không hợp lệ.']);
    exit;
}

// A user can only delete their own comments.
$stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $comment_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Đã xóa bình luận thành công.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Không thể xóa bình luận.']);
}

$stmt->close();
$conn->close();