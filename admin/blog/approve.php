<?php
session_start();
include_once __DIR__ . '/../../../database/db_connection.php';

// Bảo mật: Chỉ admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit('Bạn không có quyền truy cập.');
}

$blog_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($blog_id) {
    $stmt = $conn->prepare("UPDATE blogs SET status = 'approved' WHERE blog_id = ?");
    $stmt->bind_param("i", $blog_id);
    $stmt->execute();
    $_SESSION['admin_blog_message'] = "Đã duyệt bài viết thành công!";
}
header('Location: ../dashboard.php?page=blog/list.php');
exit();