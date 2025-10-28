<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Bao gồm file config để lấy base_url
include_once __DIR__ . '/../config.php';

/**
 * Kiểm tra xem người dùng có phải là admin không.
 * Nếu không, chuyển hướng họ đến trang đăng nhập hoặc trả về lỗi 403 cho các yêu cầu AJAX.
 */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Đối với các yêu cầu AJAX, trả về lỗi JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        http_response_code(403); // Forbidden
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Truy cập bị từ chối. Bạn không có quyền thực hiện hành động này.']);
    } else {
        // Đối với truy cập trang trực tiếp, chuyển hướng đến trang đăng nhập
        header('Location: ' . $base_url . '/login/index.php?error=access_denied');
    }
    exit();
}