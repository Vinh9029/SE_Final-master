<?php
include_once __DIR__ . '/../../database/db_connection.php';
include_once __DIR__ . '/../../config.php';
include_once __DIR__ . '/../../menus/helper.php';

// --- Filtering & Pagination Logic ---
$limit = 15; // Số bình luận mỗi trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$where_clauses = [];
$params = [];
$types = '';

// Filter by status
$status_filter = $_GET['status'] ?? '';
if ($status_filter && in_array($status_filter, ['pending', 'approved', 'rejected'])) {
    $where_clauses[] = "c.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

// Filter by type
$type_filter = $_GET['type'] ?? '';
if ($type_filter && in_array($type_filter, ['product', 'blog'])) {
    $where_clauses[] = "c.target_type = ?";
    $params[] = $type_filter;
    $types .= 's';
}

// Search keyword
$search_keyword = $_GET['search'] ?? '';
if ($search_keyword) {
    $where_clauses[] = "(c.content LIKE ? OR u.full_name LIKE ?)";
    $search_param = "%" . $search_keyword . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(' AND ', $where_clauses) : '';

// --- Fetch Comments ---
$sql = "
    SELECT 
        c.id, c.content, c.created_at, c.status, c.target_type, c.target_id,
        u.full_name AS user_name, u.user_id,
        p.name AS product_name,
        b.title AS blog_title, b.slug AS blog_slug
    FROM comments c
    JOIN users u ON c.user_id = u.user_id
    LEFT JOIN products p ON c.target_type = 'product' AND c.target_id = p.product_id
    LEFT JOIN blogs b ON c.target_type = 'blog' AND c.target_id = b.blog_id
    $where_sql
    ORDER BY c.created_at DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);

$query_params = $params;
$query_types = $types . 'ii';
$query_params[] = $limit;
$query_params[] = $offset;

if (!empty($query_types)) {
    $stmt->bind_param($query_types, ...$query_params);
}

$stmt->execute();
$result = $stmt->get_result();
$comments = $result->fetch_all(MYSQLI_ASSOC);

// --- Count Total for Pagination ---
$count_sql = "SELECT COUNT(c.id) FROM comments c JOIN users u ON c.user_id = u.user_id $where_sql";
$count_stmt = $conn->prepare($count_sql);
if (!empty($types)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_comments = $count_stmt->get_result()->fetch_row()[0];
$total_pages = ceil($total_comments / $limit);

// --- Helper Functions ---
function get_status_badge($status)
{
    switch ($status) {
        case 'approved':
            return '<span class="bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full">Đã duyệt</span>';
        case 'pending':
            return '<span class="bg-yellow-100 text-yellow-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full">Chờ duyệt</span>';
        case 'rejected':
            return '<span class="bg-red-100 text-red-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full">Bị từ chối</span>';
        default:
            return '<span class="bg-gray-100 text-gray-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full">Không rõ</span>';
    }
}

function get_target_link($comment, $base_url)
{
    if ($comment['target_type'] === 'product' && $comment['product_name']) {
        $slug = generateSlug($comment['product_name']);
        return "<a href='{$base_url}/menus/product.php?slug={$slug}' target='_blank' class='text-blue-600 hover:underline'>{$comment['product_name']}</a>";
    } elseif ($comment['target_type'] === 'blog' && $comment['blog_title']) {
        return "<a href='{$base_url}/pages/blogs/detail.php?slug={$comment['blog_slug']}' target='_blank' class='text-purple-600 hover:underline'>{$comment['blog_title']}</a>";
    }
    return "<span>Không rõ</span>";
}
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Quản lý Bình luận</h1>

    <!-- Filter Form -->
    <form method="GET" class="bg-white p-4 rounded-lg shadow-md mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Tìm kiếm</label>
                <input type="text" name="search" id="search" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Nội dung, người dùng..." value="<?php echo htmlspecialchars($search_keyword); ?>">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Trạng thái</label>
                <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Tất cả</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Chờ duyệt</option>
                    <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Đã duyệt</option>
                    <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Bị từ chối</option>
                </select>
            </div>
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Loại</label>
                <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Tất cả</option>
                    <option value="product" <?php echo $type_filter == 'product' ? 'selected' : ''; ?>>Sản phẩm</option>
                    <option value="blog" <?php echo $type_filter == 'blog' ? 'selected' : ''; ?>>Blog</option>
                </select>
            </div>
            <div class="self-end">
                <button type="submit" class="w-full bg-pink-600 text-white py-2 px-4 rounded-md hover:bg-pink-700 font-semibold">Lọc</button>
            </div>
        </div>
    </form>

    <!-- Comments Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Người dùng</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nội dung</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Đối tượng</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày gửi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($comments)) : ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Không tìm thấy bình luận nào.</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($comments as $comment) : ?>
                        <tr id="comment-row-<?php echo $comment['id']; ?>">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($comment['user_name']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate" title="<?php echo htmlspecialchars($comment['content']); ?>"><?php echo htmlspecialchars($comment['content']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo get_target_link($comment, $base_url); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" id="status-cell-<?php echo $comment['id']; ?>"><?php echo get_status_badge($comment['status']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php if ($comment['status'] === 'pending') : ?>
                                    <button onclick="updateCommentStatus(<?php echo $comment['id']; ?>, 'approved')" class="text-green-600 hover:text-green-900">Duyệt</button>
                                    <span class="mx-1 text-gray-300">|</span>
                                    <button onclick="updateCommentStatus(<?php echo $comment['id']; ?>, 'rejected')" class="text-yellow-600 hover:text-yellow-900">Từ chối</button>
                                <?php elseif ($comment['status'] === 'approved') : ?>
                                    <button onclick="updateCommentStatus(<?php echo $comment['id']; ?>, 'rejected')" class="text-yellow-600 hover:text-yellow-900">Ẩn</button>
                                <?php endif; ?>
                                <span class="mx-1 text-gray-300">|</span>
                                <button onclick="deleteComment(<?php echo $comment['id']; ?>)" class="text-red-600 hover:text-red-900">Xóa</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex justify-between items-center">
        <span class="text-sm text-gray-700">
            Hiển thị từ <span class="font-medium"><?php echo min($offset + 1, $total_comments); ?></span> đến <span class="font-medium"><?php echo min($offset + $limit, $total_comments); ?></span> trong tổng số <span class="font-medium"><?php echo $total_comments; ?></span> bình luận
        </span>
        <div class="flex">
            <?php if ($total_pages > 1) : ?>
                <?php
                // Build query string for pagination links
                $query_params = $_GET;
                ?>
                <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => $page - 1])); ?>" class="px-3 py-1 border rounded-l-md <?php echo $page <= 1 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-gray-50'; ?>">
                    Trước
                </a>
                <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => $page + 1])); ?>" class="px-3 py-1 border-t border-b border-r rounded-r-md <?php echo $page >= $total_pages ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-gray-50'; ?>">
                    Sau
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function updateCommentStatus(commentId, newStatus) {
        if (!confirm(`Bạn có chắc muốn ${newStatus === 'approved' ? 'duyệt' : (newStatus === 'rejected' ? 'từ chối/ẩn' : 'cập nhật')} bình luận này?`)) {
            return;
        }

        const formData = new FormData();
        formData.append('comment_id', commentId);
        formData.append('status', newStatus);

        fetch('admin/comments/update_status.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Cập nhật trạng thái thành công!', 'success');
                    // Cập nhật giao diện trực tiếp thay vì tải lại
                    const statusCell = document.getElementById(`status-cell-${commentId}`);
                    const actionCell = statusCell.nextElementSibling;

                    // Cập nhật badge trạng thái
                    if (newStatus === 'approved') {
                        statusCell.innerHTML = '<span class="bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full">Đã duyệt</span>';
                        actionCell.innerHTML = `<button onclick="updateCommentStatus(${commentId}, 'rejected')" class="text-yellow-600 hover:text-yellow-900">Ẩn</button>
                                                <span class="mx-1 text-gray-300">|</span>
                                                <button onclick="deleteComment(${commentId})" class="text-red-600 hover:text-red-900">Xóa</button>`;
                    } else if (newStatus === 'rejected') {
                        statusCell.innerHTML = '<span class="bg-red-100 text-red-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full">Bị từ chối</span>';
                        actionCell.innerHTML = `<button onclick="updateCommentStatus(${commentId}, 'approved')" class="text-green-600 hover:text-green-900">Duyệt</button>
                                                <span class="mx-1 text-gray-300">|</span>
                                                <button onclick="deleteComment(${commentId})" class="text-red-600 hover:text-red-900">Xóa</button>`;
                    } else { // pending
                        statusCell.innerHTML = '<span class="bg-yellow-100 text-yellow-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full">Chờ duyệt</span>';
                        actionCell.innerHTML = `<button onclick="updateCommentStatus(${commentId}, 'approved')" class="text-green-600 hover:text-green-900">Duyệt</button>
                                                <span class="mx-1 text-gray-300">|</span>
                                                <button onclick="updateCommentStatus(${commentId}, 'rejected')" class="text-yellow-600 hover:text-yellow-900">Từ chối</button>`;
                    }
                } else {
                    showToast(data.message || 'Có lỗi xảy ra.', 'error');
                }
            })
            .catch(() => showToast('Lỗi kết nối.', 'error'));
    }

    function deleteComment(commentId) {
        if (!confirm('Bạn có chắc chắn muốn XÓA vĩnh viễn bình luận này? Hành động này không thể hoàn tác.')) {
            return;
        }

        const formData = new FormData();
        formData.append('comment_id', commentId);

        fetch('includes/handlers/comments/deleteComment.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Đã xóa bình luận thành công!', 'success');
                    const row = document.getElementById(`comment-row-${commentId}`);
                    if (row) {
                        row.style.opacity = 0;
                        setTimeout(() => row.remove(), 500);
                    }
                } else {
                    showToast(data.message || 'Có lỗi xảy ra.', 'error');
                }
            })
            .catch(() => showToast('Lỗi kết nối.', 'error'));
    }
</script>