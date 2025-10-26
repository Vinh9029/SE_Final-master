<?php
// This is a logic-only file. It should not output any HTML.
// Start session and include only necessary config/db files.
include_once __DIR__ . '/../../config.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../../database/db_connection.php';

// Chỉ cho phép người dùng đã đăng nhập truy cập
if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_url . "/login/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$blog_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$confirm = filter_input(INPUT_GET, 'confirm');

// Chỉ thực hiện xóa nếu có ID hợp lệ và có xác nhận từ popup
if (!$blog_id || $confirm !== 'true') {
    $_SESSION['error_message'] = "ID bài viết không hợp lệ.";
    header("Location: myBlogs.php");
    exit();
}

// Lấy thông tin bài viết để kiểm tra quyền và xóa ảnh
$stmt = $conn->prepare("SELECT user_id, cover_image FROM blogs WHERE blog_id = ?");
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$result = $stmt->get_result();
$blog = $result->fetch_assoc();
$stmt->close();

if ($blog && $blog['user_id'] == $user_id) {
    // Xóa file ảnh bìa trên server nếu có
    if (!empty($blog['cover_image']) && file_exists(__DIR__ . '/../../' . $blog['cover_image'])) {
        unlink(__DIR__ . '/../../' . $blog['cover_image']);
    }
    // Xóa bài viết khỏi CSDL
    $delete_stmt = $conn->prepare("DELETE FROM blogs WHERE blog_id = ?");
    $delete_stmt->bind_param("i", $blog_id);
    $delete_stmt->execute();
    $delete_stmt->close();
    $_SESSION['success_message'] = "Đã xóa bài viết thành công.";
} else {
    $_SESSION['error_message'] = "Không tìm thấy bài viết hoặc bạn không có quyền xóa.";
}

header("Location: myBlogs.php");
exit();