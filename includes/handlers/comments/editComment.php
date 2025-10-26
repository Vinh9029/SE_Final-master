<?php
include_once __DIR__ . '/../../../database/db_connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để chỉnh sửa.']);
    exit;
}

// 2. Get and validate input
$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
$new_content = isset($_POST['content']) ? trim($_POST['content']) : '';

if (empty($new_content)) {
    echo json_encode(['success' => false, 'message' => 'Nội dung bình luận không được để trống.']);
    exit;
}
if (!$comment_id) {
    echo json_encode(['success' => false, 'message' => 'ID bình luận không hợp lệ.']);
    exit;
}

// 3. Build the query based on user role
// Admin can edit any comment. A regular user can only edit their own. After editing, the status is set back to 'pending' for review.
$sql = "UPDATE comments SET content = ?, status = 'pending' WHERE id = ?";
$params = [$new_content, $comment_id];
$types = 'si';

if (!$is_admin) {
    $sql .= " AND user_id = ?";
    $params[] = $user_id;
    $types .= 'i';
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Đã cập nhật bình luận. Bình luận của bạn sẽ được duyệt lại.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể cập nhật hoặc bạn không có quyền chỉnh sửa bình luận này.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi khi cập nhật.']);
}

$stmt->close();
$conn->close();
?>