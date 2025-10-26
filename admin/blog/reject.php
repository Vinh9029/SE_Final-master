<?php
session_start();
include_once __DIR__ . '/../../database/db_connection.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];

// Bảo mật: Chỉ admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $response['message'] = 'Bạn không có quyền truy cập.';
    echo json_encode($response);
    exit();
}

// Đảm bảo đây là yêu cầu AJAX
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
if (!$is_ajax) {
    $response['message'] = 'Truy cập bị từ chối.';
    echo json_encode($response);
    exit();
}

$blog_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$blog_id) {
    $response['message'] = 'ID bài viết không hợp lệ.';
    echo json_encode($response);
    exit();
}

$stmt = $conn->prepare("UPDATE blogs SET status = 'rejected' WHERE blog_id = ?");
$stmt->bind_param("i", $blog_id);
if ($stmt->execute()) {
    $response = ['success' => true, 'message' => 'Đã từ chối bài viết.'];
} else {
    $response['message'] = 'Lỗi khi từ chối bài viết: ' . $conn->error;
}
$stmt->close();

echo json_encode($response);