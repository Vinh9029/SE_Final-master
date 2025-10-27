<?php
include_once __DIR__ . '/../../../database/db_connection.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để tương tác.']);
    exit;
}

// 2. Get and validate input
$user_id = $_SESSION['user_id'];
$comment_id = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);
$reaction_type = $_POST['reaction_type'] ?? '';
$allowed_reactions = ['like', 'love', 'haha', 'wow', 'sad', 'angry'];

if (!$comment_id || !in_array($reaction_type, $allowed_reactions)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
    exit;
}

$conn->begin_transaction();

try {
    // Check for existing reaction from this user on this comment
    $stmt = $conn->prepare("SELECT id, reaction_type FROM comment_reactions WHERE user_id = ? AND comment_id = ?");
    $stmt->bind_param("ii", $user_id, $comment_id);
    $stmt->execute();
    $existing_reaction = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $action = '';

    if ($existing_reaction) {
        // User has already reacted
        if ($existing_reaction['reaction_type'] === $reaction_type) {
            // User clicked the same reaction again, so we remove it (un-react)
            $stmt = $conn->prepare("DELETE FROM comment_reactions WHERE id = ?");
            $stmt->bind_param("i", $existing_reaction['id']);
            $stmt->execute();
            $action = 'unreacted';
        } else {
            // User changed their reaction
            $stmt = $conn->prepare("UPDATE comment_reactions SET reaction_type = ? WHERE id = ?");
            $stmt->bind_param("si", $reaction_type, $existing_reaction['id']);
            $stmt->execute();
            $action = 'changed';
        }
    } else {
        // New reaction
        $stmt = $conn->prepare("INSERT INTO comment_reactions (user_id, comment_id, reaction_type) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $comment_id, $reaction_type);
        $stmt->execute();
        $action = 'reacted';
    }
    $stmt->close();

    // Get updated reaction counts for the comment
    $reaction_counts = [];
    $stmt = $conn->prepare("SELECT reaction_type, COUNT(*) as count FROM comment_reactions WHERE comment_id = ? GROUP BY reaction_type");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $reaction_counts[$row['reaction_type']] = $row['count'];
    }
    $stmt->close();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'action' => $action,
        'new_reactions' => $reaction_counts
    ]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi. Vui lòng thử lại.']);
}

$conn->close();
?>

