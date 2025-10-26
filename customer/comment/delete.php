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

if (!$comment_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid comment ID.']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
$stmt->bind_param('i', $comment_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Comment deleted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete comment.']);
}

$stmt->close();
$conn->close();