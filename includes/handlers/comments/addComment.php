<?php
include_once __DIR__ . '/../../../database/db_connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// 0. Check HTTP Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Phương thức không được phép.']);
    exit;
}

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để bình luận.']);
    exit;
}

// 2. Get and validate input
$user_id = $_SESSION['user_id'];
$target_id = filter_input(INPUT_POST, 'target_id', FILTER_VALIDATE_INT);
$target_type = $_POST['target_type'] ?? '';
$content = trim($_POST['content'] ?? '');
$parent_id = filter_input(INPUT_POST, 'parent_id', FILTER_VALIDATE_INT);
if ($parent_id === false || $parent_id <= 0) {
    $parent_id = null; // Set to NULL if not provided or invalid
}

if (empty($content)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Nội dung bình luận không được để trống.']);
    exit;
}
if (!$target_id || !in_array($target_type, ['product', 'blog'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Đối tượng bình luận không hợp lệ.']);
    exit;
}

// 3. Insert into database with 'pending' status
$sql = "INSERT INTO comments (user_id, target_id, target_type, content, parent_id, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
$stmt = $conn->prepare($sql);
// The type for parent_id is 'i'. bind_param handles NULL correctly for integer types.
$stmt->bind_param('iissi', $user_id, $target_id, $target_type, $content, $parent_id);

if ($stmt->execute()) {
    http_response_code(201); // Created
    echo json_encode(['success' => true, 'message' => 'Bình luận của bạn đã được gửi và đang chờ duyệt.']);
} else {
    http_response_code(500); // Internal Server Error
    error_log("Comment Insert Failed: " . $stmt->error); // Log the actual error
    echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi. Vui lòng thử lại.']);
}

$stmt->close();
$conn->close();
?>