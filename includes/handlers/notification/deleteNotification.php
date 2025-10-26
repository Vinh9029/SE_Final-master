<?php
// Handler for deleting notifications
include_once __DIR__ . '/../../../config.php';
include_once __DIR__ . '/../../../database/db_connection.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Notification ID is missing.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$notification_id = (int)$_POST['id'];

$sql = "DELETE FROM notifications WHERE id = ? AND customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $notification_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Notification deleted.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not delete notification.']);
}

$stmt->close();
$conn->close();
?>