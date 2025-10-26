<?php
session_start();
include_once '../../../database/db_connection.php';
include_once '../../../includes/handlers/notification/createNotification.php';

// Check if the user is logged in and is an admin
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

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

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $comment_id = (int)$_GET['id'];

    $stmt = $db_connection->prepare("SELECT user_id FROM comments WHERE id = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $comment = $result->fetch_assoc();
        $author_id = $comment['user_id'];

        $update_stmt = $db_connection->prepare("UPDATE comments SET status = 'rejected' WHERE id = ?");
        $update_stmt->bind_param("i", $comment_id);

        if ($update_stmt->execute()) {
            $notification_message = "Your recent comment was rejected by an administrator.";
            createNotification($author_id, $notification_message, 'comment_rejected', $comment_id, $db_connection);
            $response = ['success' => true, 'message' => 'Comment rejected successfully.'];
        } else {
            $response['message'] = 'Error: Could not reject the comment.';
        }
        $update_stmt->close();
    } else {
        $response['message'] = 'Error: Comment not found.';
    }
    $stmt->close();
}

$db_connection->close();

if ($is_ajax) {
    header('Content-Type: application/json');
    if (!$response['success']) {
        http_response_code(400); // Bad Request or appropriate error code
    }
    echo json_encode($response);
} else {
    if ($response['success']) {
        $_SESSION['message'] = $response['message'];
    } else {
        $_SESSION['error'] = $response['message'];
    }
    header("Location: list.php");
}
exit();
?>