<?php
session_start();
include_once __DIR__ . '/../../database/db_connection.php';

// Bảo mật: Chỉ admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit('Bạn không có quyền truy cập.');
}

$blog_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($blog_id) {
    // Lấy đường dẫn ảnh để xóa file
    $stmt = $conn->prepare("SELECT cover_image FROM blogs WHERE blog_id = ?");
    $stmt->bind_param("i", $blog_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $blog = $result->fetch_assoc();

    // Xóa bài viết khỏi CSDL
    $delete_stmt = $conn->prepare("DELETE FROM blogs WHERE blog_id = ?");
    $delete_stmt->bind_param("i", $blog_id);
    $delete_stmt->execute();

    // Xóa file ảnh bìa trên server nếu có
    if ($blog && !empty($blog['cover_image']) && file_exists(__DIR__ . '/../../' . $blog['cover_image'])) {
        unlink(__DIR__ . '/../../' . $blog['cover_image']);
    }
    $_SESSION['admin_blog_message'] = "Đã xóa vĩnh viễn bài viết.";
}

header('Location: ../dashboard.php?page=blog/list.php');
exit();