<?php
include_once __DIR__ . '/../../database/db_connection.php';
include_once __DIR__ . '/../../includes/handlers/notification/createNotification.php'; // Thêm file xử lý thông báo
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
    // Nếu cập nhật thành công, gửi thông báo cho người dùng
    if ($stmt->affected_rows > 0) {
        // Lấy user_id của người bình luận
        $user_stmt = $conn->prepare("SELECT user_id, parent_id, target_type, target_id, LEFT(content, 50) as content_preview FROM comments WHERE id = ?");
        $user_stmt->bind_param('i', $comment_id);
        $user_stmt->execute();
        $comment_data = $user_stmt->get_result()->fetch_assoc(); // Dữ liệu của bình luận vừa được duyệt
        $user_stmt->close();

        if ($comment_data) {
            $customer_id = $comment_data['user_id'];
            $content_preview = $comment_data['content_preview'];
            $notification_title = '';
            $notification_body = '';
            $url = '#';

            // Tạo URL dựa trên target_type
            if ($comment_data['target_type'] === 'product') {
                $product_stmt = $conn->prepare("SELECT name FROM products WHERE product_id = ?");
                $product_stmt->bind_param('i', $comment_data['target_id']);
                $product_stmt->execute();
                $product_name = $product_stmt->get_result()->fetch_assoc()['name'] ?? null;
                if ($product_name) {
                    include_once __DIR__ . '/../../menus/helper.php';
                    $url = '/menus/product.php?slug=' . generateSlug($product_name) . '#comment-wrapper-' . $comment_data['parent_id'];
                }
            } elseif ($comment_data['target_type'] === 'blog') {
                $blog_stmt = $conn->prepare("SELECT slug FROM blogs WHERE blog_id = ?");
                $blog_stmt->bind_param('i', $comment_data['target_id']);
                $blog_stmt->execute();
                $blog_slug = $blog_stmt->get_result()->fetch_assoc()['slug'] ?? null;
                if ($blog_slug) {
                    $url = '/pages/blogs/detail.php?slug=' . $blog_slug . '#comment-wrapper-' . $comment_data['parent_id'];
                }
            }

            if ($new_status === 'approved') {
                $notification_title = 'Bình luận của bạn đã được duyệt';
                $notification_body = "Bình luận \"{$content_preview}...\" của bạn đã được hiển thị công khai.";
                create_notification($conn, $customer_id, 'comment_approved', $notification_title, $notification_body, $comment_id, $url);

                // **LOGIC MỚI: Gửi thông báo cho người được trả lời**
                if (!empty($comment_data['parent_id'])) {
                    $parent_stmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
                    $parent_stmt->bind_param('i', $comment_data['parent_id']);
                    $parent_stmt->execute();
                    $parent_author_id = $parent_stmt->get_result()->fetch_assoc()['user_id'] ?? null;
                    $parent_stmt->close();

                    // Chỉ gửi thông báo nếu người trả lời không phải là tác giả bình luận gốc
                    if ($parent_author_id && $parent_author_id != $customer_id) {
                        create_notification($conn, $parent_author_id, 'comment_reply', 'Có người đã trả lời bình luận của bạn', "Một người dùng đã trả lời bình luận của bạn.", $comment_data['parent_id'], $url);
                    }
                }
            } elseif ($new_status === 'rejected') {
                $notification_title = 'Bình luận của bạn đã bị từ chối';
                $notification_body = "Bình luận \"{$content_preview}...\" của bạn không phù hợp với quy định của chúng tôi.";
                create_notification($conn, $customer_id, 'comment_rejected', $notification_title, $notification_body, $comment_id, '/customer/account.php?page=comments'); // Link về trang quản lý bình luận
            }
        }

        echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái và gửi thông báo thành công.']);
    } else {
        // Trạng thái không thay đổi
        echo json_encode(['success' => true, 'message' => 'Trạng thái bình luận không thay đổi.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update comment status.']);
}

$stmt->close();
$conn->close();