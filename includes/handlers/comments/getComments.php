<?php
include_once __DIR__ . '/../../../database/db_connection.php';
include_once __DIR__ . '/../../../config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$target_type = $_GET['target_type'] ?? null;
$target_id = isset($_GET['target_id']) ? (int)$_GET['target_id'] : 0;
$current_user_id = $_SESSION['user_id'] ?? null;

if (!$target_type || !$target_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid target.']);
    exit;
}

// This query fetches:
// 1. All 'approved' comments for the target.
// 2. Any 'pending' comments for the target that belong to the currently logged-in user.
$sql = "
    SELECT 
        c.id, c.content, c.created_at, c.user_id,
        u.full_name AS username,
        u.avatar_image AS user_avatar,
        (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id) AS likes,
        (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id AND user_id = ?) AS user_has_liked
    FROM comments c
    JOIN users u ON c.user_id = u.user_id
    WHERE 
        c.target_type = ? AND c.target_id = ?
        AND (c.status = 'approved' OR (c.status = 'pending' AND c.user_id = ?))
    ORDER BY c.created_at ASC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database query preparation failed.']);
    exit;
}

$stmt->bind_param('isis', $current_user_id, $target_type, $target_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();
$comments = $result->fetch_all(MYSQLI_ASSOC);

// Process avatar paths
foreach ($comments as &$comment) {
    if ($comment['user_avatar'] && file_exists(__DIR__ . '/../../../' . $comment['user_avatar'])) {
        $comment['user_avatar'] = $base_url . '/' . $comment['user_avatar'];
    } else {
        // Fallback to a default avatar if the user's avatar is not set or not found
        $comment['user_avatar'] = $base_url . '/customer/Photos/avatar/avatar1.jpg';
    }
}

echo json_encode(['success' => true, 'comments' => $comments]);

$stmt->close();
$conn->close();
?>