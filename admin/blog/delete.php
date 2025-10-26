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

try {
    // Lấy đường dẫn ảnh để xóa file
    $stmt = $conn->prepare("SELECT cover_image FROM blogs WHERE blog_id = ?");
    $stmt->bind_param("i", $blog_id);
    $stmt->execute();
    $blog = $stmt->get_result()->fetch_assoc();

    // Xóa bài viết khỏi CSDL
    $delete_stmt = $conn->prepare("DELETE FROM blogs WHERE blog_id = ?");
    $delete_stmt->bind_param("i", $blog_id);
    if ($delete_stmt->execute()) {
        // Xóa file ảnh bìa trên server nếu có
        if ($blog && !empty($blog['cover_image']) && file_exists(__DIR__ . '/../../' . $blog['cover_image'])) {
            @unlink(__DIR__ . '/../../' . $blog['cover_image']);
        }
        $response = ['success' => true, 'message' => 'Đã xóa vĩnh viễn bài viết.'];
    } else {
        $response['message'] = 'Lỗi khi xóa bài viết khỏi cơ sở dữ liệu.';
    }
} catch (Exception $e) {
    $response['message'] = 'Đã xảy ra lỗi hệ thống: ' . $e->getMessage();
}

echo json_encode($response);
exit();