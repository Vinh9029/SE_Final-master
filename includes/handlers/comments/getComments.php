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

// Câu SQL để lấy tất cả bình luận, thông tin người dùng, và cảm xúc của người dùng hiện tại
$sql = "
    SELECT 
        c.id, c.user_id, c.content, c.created_at, c.parent_id,
        u.full_name AS username,
        u.avatar_image AS user_avatar,
        lp.points AS user_points,
        cr.reaction_type AS user_reaction
    FROM comments c
    JOIN users u ON c.user_id = u.user_id
    LEFT JOIN loyalty_points lp ON u.user_id = lp.user_id
    LEFT JOIN comment_reactions cr ON c.id = cr.comment_id AND cr.user_id = ?
    WHERE c.target_type = ? AND c.target_id = ? AND c.status = 'approved'
    ORDER BY c.created_at ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $user_id, $target_type, $target_id);
$stmt->execute();
$result = $stmt->get_result();
$all_comments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Lấy số lượng của từng loại cảm xúc cho tất cả các bình luận được hiển thị
$comment_ids = array_column($all_comments, 'id');
$reactions = [];
if (!empty($comment_ids)) {
    $ids_placeholder = implode(',', array_fill(0, count($comment_ids), '?'));
    $types = str_repeat('i', count($comment_ids));
    $reaction_sql = "SELECT comment_id, reaction_type, COUNT(*) as count FROM comment_reactions WHERE comment_id IN ($ids_placeholder) GROUP BY comment_id, reaction_type";
    $reaction_stmt = $conn->prepare($reaction_sql);
    $reaction_stmt->bind_param($types, ...$comment_ids);
    $reaction_stmt->execute();
    $reaction_result = $reaction_stmt->get_result();
    while ($row = $reaction_result->fetch_assoc()) {
        $reactions[$row['comment_id']][$row['reaction_type']] = $row['count'];
    }
    $reaction_stmt->close();
}

// Sắp xếp bình luận thành cây phân cấp (cha-con)
$comments_by_id = [];
foreach ($all_comments as &$comment) { // Dùng tham chiếu để cập nhật avatar
    if ($comment['user_avatar'] && file_exists(__DIR__ . '/../../../' . $comment['user_avatar'])) {
        $comment['user_avatar'] = $base_url . '/' . $comment['user_avatar'];
    } else {
        $comment['user_avatar'] = $base_url . '/customer/Photos/avatar/avatar1.jpg';
    }
    $comment['reactions'] = $reactions[$comment['id']] ?? []; // Gán dữ liệu reactions
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