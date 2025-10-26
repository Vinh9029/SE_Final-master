<?php
include_once __DIR__ . '/../../database/db_connection.php';
session_start();

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$comment_id = $_POST['comment_id'] ?? null;
$new_status = $_POST['status'] ?? null;

if (!$comment_id || !in_array($new_status, ['approved', 'rejected', 'pending'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

$stmt = $conn->prepare("UPDATE comments SET status = ? WHERE id = ?");
$stmt->bind_param('si', $new_status, $comment_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Comment status updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update comment status.']);
}

$stmt->close();
$conn->close();