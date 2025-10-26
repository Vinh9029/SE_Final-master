<?php
session_start();
include_once __DIR__ . '/../../database/db_connection.php';

// Check if the user is logged in and is an admin
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    if ($is_ajax) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    } else {
        header("Location: ../../../login/index.php");
    }
    exit();
}

$response = ['success' => false, 'message' => 'Invalid request.'];

$comment_id = $_POST['comment_id'] ?? null;

if (!$comment_id) {
    $response['message'] = 'Invalid comment ID.';
} else {
    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->bind_param('i', $comment_id);

    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Comment deleted successfully.'];
    } else {
        $response['message'] = 'Failed to delete comment.';
    }
    $stmt->close();
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($response);
?>