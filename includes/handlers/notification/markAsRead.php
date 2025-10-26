<?php
// Handler for marking notifications as read
include_once __DIR__ . '/../../../config.php';
include_once __DIR__ . '/../../../database/db_connection.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    // Mark a single notification as read
    $notif_id = (int)$_GET['id'];
    $sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND customer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $notif_id, $user_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    $stmt->close();
} elseif (isset($_GET['all']) && $_GET['all'] == 'true') {
    // Mark all notifications as read for the user
    $sql = "UPDATE notifications SET is_read = 1 WHERE customer_id = ? AND is_read = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}

$conn->close();
?>