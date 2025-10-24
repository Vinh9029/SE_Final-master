<?php
session_start();
include_once __DIR__ . '/../../database/db_connection.php';

// Bảo mật: Chỉ admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // For AJAX requests, return JSON error
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Bạn không có quyền truy cập.']);
        exit();
    }
    // For direct access, redirect
    exit('Bạn không có quyền truy cập.');
}

$blog_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT); // Dùng cho trường hợp truy cập trực tiếp (non-AJAX GET)
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($is_ajax && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $blog_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT); // Lấy ID từ POST cho AJAX
    if (!$blog_id) {
        echo json_encode(['success' => false, 'message' => 'ID bài viết không hợp lệ.']);
        exit();
    }
    $stmt = $conn->prepare("UPDATE blogs SET status = 'rejected' WHERE blog_id = ?");
    $stmt->bind_param("i", $blog_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Đã từ chối bài viết.', 'redirect' => 'blog/list.php']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi từ chối bài viết: ' . $conn->error]);
    }
    $stmt->close();
    exit();
}

// Logic cho trường hợp non-AJAX GET
if ($blog_id) { // Chỉ thực hiện nếu có blog_id hợp lệ từ GET
    $stmt = $conn->prepare("UPDATE blogs SET status = 'rejected' WHERE blog_id = ?");
    $stmt->bind_param("i", $blog_id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['admin_blog_message'] = "Đã từ chối bài viết.";
}
header('Location: ../dashboard.php?page=blog/list.php'); // Chuyển hướng sau khi xử lý non-AJAX
exit();