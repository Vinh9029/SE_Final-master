<?php
include_once __DIR__ . '/../../../database/db_connection.php';
include_once __DIR__ . '/../../../config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$target_id = filter_input(INPUT_GET, 'target_id', FILTER_VALIDATE_INT);
$target_type = $_GET['target_type'] ?? '';
$user_id = $_SESSION['user_id'] ?? null;

if (!$target_id || !in_array($target_type, ['product', 'blog'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid target.']);
    exit;
}

// Câu SQL để lấy tất cả bình luận và thông tin người dùng, bao gồm cả trạng thái like
$sql = "
    SELECT 
        c.id, c.user_id, c.content, c.created_at, c.likes, c.parent_id,
        u.full_name AS username,
        u.avatar_image AS user_avatar,
        (CASE WHEN cl.id IS NOT NULL THEN 1 ELSE 0 END) AS user_has_liked
    FROM comments c
    JOIN users u ON c.user_id = u.user_id
    LEFT JOIN comment_likes cl ON c.id = cl.comment_id AND cl.user_id = ?
    WHERE c.target_type = ? AND c.target_id = ? AND c.status = 'approved'
    ORDER BY c.created_at ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $user_id, $target_type, $target_id);
$stmt->execute();
$result = $stmt->get_result();
$all_comments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Sắp xếp bình luận thành cây phân cấp (cha-con)
$comments_by_id = [];
foreach ($all_comments as &$comment) { // Dùng tham chiếu để cập nhật avatar
    if ($comment['user_avatar'] && file_exists(__DIR__ . '/../../../' . $comment['user_avatar'])) {
        $comment['user_avatar'] = $base_url . '/' . $comment['user_avatar'];
    } else {
        $comment['user_avatar'] = $base_url . '/customer/Photos/avatar/avatar1.jpg';
    }
    $comments_by_id[$comment['id']] = $comment;
    $comments_by_id[$comment['id']]['replies'] = []; // Khởi tạo mảng replies
}
unset($comment); // Hủy tham chiếu

$nested_comments = [];
foreach ($comments_by_id as $id => &$comment) { // Dùng tham chiếu &
    if ($comment['parent_id'] && isset($comments_by_id[$comment['parent_id']])) {
        // Nếu là reply, thêm nó vào mảng replies của cha
        $comments_by_id[$comment['parent_id']]['replies'][] = &$comment;
    } else {
        // Nếu là bình luận gốc, thêm vào mảng kết quả
        $nested_comments[] = &$comment;
    }
}
unset($comment); // Hủy tham chiếu

// Sắp xếp lại bình luận gốc theo thứ tự mới nhất lên đầu
usort($nested_comments, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

echo json_encode(['success' => true, 'comments' => $nested_comments]);

$conn->close();
?>