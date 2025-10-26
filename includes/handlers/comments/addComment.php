<?php
include_once __DIR__ . '/../../../database/db_connection.php';
include_once __DIR__ . '/../../../config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để bình luận.']);
    exit;
}

// 2. Get and validate input
$user_id = $_SESSION['user_id'];
$target_id = isset($_POST['target_id']) ? (int)$_POST['target_id'] : 0;
$target_type = $_POST['target_type'] ?? '';
$parent_id = isset($_POST['parent_id']) && !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
$content = isset($_POST['content']) ? trim($_POST['content']) : '';

if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Nội dung bình luận không được để trống.']);
    exit;
}
if (!$target_id || !in_array($target_type, ['product', 'blog'])) {
    echo json_encode(['success' => false, 'message' => 'Đối tượng bình luận không hợp lệ.']);
    exit;
}

// 3. Insert into database with 'pending' status
$sql = "INSERT INTO comments (user_id, parent_id, target_id, target_type, content, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iiisss', $user_id, $parent_id, $target_id, $target_type, $content);

if ($stmt->execute()) {
    $new_comment_id = $stmt->insert_id;

    // 4. Create notification if it's a reply
    if ($parent_id) {
        // Find the owner of the parent comment
        $parent_stmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
        $parent_stmt->bind_param('i', $parent_id);
        $parent_stmt->execute();
        $parent_result = $parent_stmt->get_result();
        if ($parent_owner = $parent_result->fetch_assoc()) {
            $parent_owner_id = $parent_owner['user_id'];

            // Don't notify if users reply to their own comment
            if ($parent_owner_id != $user_id) {
                // Construct the URL to the comment
                // This is a simplified URL, you might want to make it more specific to jump to the comment
                $slug_query = $conn->prepare("SELECT p.name as product_name, b.slug as blog_slug FROM comments c LEFT JOIN products p ON c.target_id = p.product_id AND c.target_type = 'product' LEFT JOIN blogs b ON c.target_id = b.blog_id AND c.target_type = 'blog' WHERE c.id = ?");
                $slug_query->bind_param('i', $parent_id);
                $slug_query->execute();
                $slug_result = $slug_query->get_result()->fetch_assoc();
                $target_url = $target_type === 'product' ? ($base_url . '/menus/product.php?slug=' . generateSlug($slug_result['product_name'])) : ($base_url . '/pages/blogs/detail.php?slug=' . $slug_result['blog_slug']);
                $target_url .= '#comment-' . $new_comment_id;

                $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, actor_id, type, target_id, target_url) VALUES (?, ?, 'comment_reply', ?, ?)");
                $notif_stmt->bind_param('iiis', $parent_owner_id, $user_id, $new_comment_id, $target_url);
                $notif_stmt->execute();
            }
        }
    }

    echo json_encode(['success' => true, 'message' => 'Bình luận của bạn đã được gửi và đang chờ duyệt.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi. Vui lòng thử lại.']);
}

$stmt->close();
$conn->close();
?>