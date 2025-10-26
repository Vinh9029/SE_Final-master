<?php
include_once __DIR__ . '/../../../database/db_connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để bình luận.']);
    exit;
}

// 2. Get and validate input
$user_id = $_SESSION['user_id'];
$target_id = isset($_POST['target_id']) ? (int)$_POST['target_id'] : 0;
$target_type = $_POST['target_type'] ?? '';
$content = isset($_POST['content']) ? trim($_POST['content']) : '';

if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Nội dung bình luận không được để trống.']);
    exit;
}
if (!$target_id || !in_array($target_type, ['product', 'blog'])) {
    echo json_encode(['success' => false, 'message' => 'Đối tượng bình luận không hợp lệ.']);
    exit;
}

// 3. Insert into database with 'pending' status
$sql = "INSERT INTO comments (user_id, target_id, target_type, content, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iiss', $user_id, $target_id, $target_type, $content);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Bình luận của bạn đã được gửi và đang chờ duyệt.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi. Vui lòng thử lại.']);
}

$stmt->close();
$conn->close();
?>