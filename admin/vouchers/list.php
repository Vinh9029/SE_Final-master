<?php
include_once __DIR__ . '/../../database/db_connection.php';
global $conn;

// Lấy danh sách voucher
$search = isset($_GET['search']) ? $conn->real_escape_string(trim($_GET['search'])) : '';
$status = isset($_GET['status']) ? $conn->real_escape_string(trim($_GET['status'])) : '';

$whereClauses = [];
if ($search) {
    $whereClauses[] = "(v.code LIKE '%$search%' OR v.title LIKE '%$search%' OR u.username LIKE '%$search%')";
}
if ($status) {
    $whereClauses[] = "v.status = '$status'";
}

$whereSql = count($whereClauses) > 0 ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

// Phân trang
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$totalResult = $conn->query("SELECT COUNT(*) FROM vouchers v LEFT JOIN users u ON v.user_id = u.user_id $whereSql");
$total = $totalResult->fetch_row()[0];
$totalPages = ceil($total / $limit);

$vouchers = [];
$result = $conn->query("SELECT v.*, u.username FROM vouchers v LEFT JOIN users u ON v.user_id = u.user_id $whereSql ORDER BY v.created_at DESC LIMIT $limit OFFSET $offset");
while ($row = $result->fetch_assoc()) {
    $vouchers[] = $row;
}
?>

<div class="p-6 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Quản lý Voucher</h2>
        <a href="#" data-page="vouchers/add.php" class="bg-purple-600 hover:bg-purple-700 text-white font-bold px-4 py-2 rounded-md transition-colors duration-300 flex items-center justify-center gap-2">
            <i class="fas fa-plus-circle"></i> Thêm Voucher
        </a>
    </div>

    <!-- Filter Form -->
    <form method="get" action="vouchers/list.php" class="bg-white p-4 rounded-lg shadow-md mb-6 flex gap-4 items-center">
        <input type="text" name="search" placeholder="Tìm mã, tên chương trình, username..." value="<?= htmlspecialchars($search) ?>" class="w-full border px-3 py-2 rounded-md focus:ring-purple-500 focus:border-purple-500">
        <select name="status" class="border px-3 py-2 rounded-md focus:ring-purple-500 focus:border-purple-500">
            <option value="">Tất cả trạng thái</option>
            <option value="active" <?= $status == 'active' ? 'selected' : '' ?>>Active</option>
            <option value="used" <?= $status == 'used' ? 'selected' : '' ?>>Used</option>
            <option value="expired" <?= $status == 'expired' ? 'selected' : '' ?>>Expired</option>
        </select>
        <button type="submit" class="bg-purple-500 text-white px-4 py-2 rounded-md hover:bg-purple-600">Lọc</button>
    </form>

    <!-- Bảng danh sách voucher -->
    <div class="bg-white rounded-lg shadow-md overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr class="text-left text-gray-600">
                    <th class="p-3">ID</th>
                    <th class="p-3">Mã Voucher</th>
                    <th class="p-3">Chương trình</th>
                    <th class="p-3">Giảm giá</th>
                    <th class="p-3">Khách hàng</th>
                    <th class="p-3">Trạng thái</th>
                    <th class="p-3">Hết hạn</th>
                    <th class="p-3 text-center">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($vouchers)) : ?>
                    <tr><td colspan="8" class="text-center p-6 text-gray-500">Không tìm thấy voucher nào.</td></tr>
                <?php else : ?>
                    <?php foreach ($vouchers as $v) : ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3"><?= $v['voucher_id']; ?></td>
                            <td class="p-3 font-mono font-bold text-purple-700"><?= htmlspecialchars($v['code']); ?></td>
                            <td class="p-3"><?= htmlspecialchars($v['title']); ?></td>
                            <td class="p-3 font-semibold text-green-600">
                                <?= number_format($v['discount_value']); ?><?= $v['discount_type'] === 'percent' ? '%' : 'đ'; ?>
                            </td>
                            <td class="p-3"><?= $v['user_id'] ? htmlspecialchars($v['username']) . ' (ID: ' . $v['user_id'] . ')' : '<span class="text-gray-500 italic">Mọi người</span>'; ?></td>
                            <td class="p-3">
                                <?php
                                $status_class = 'bg-gray-200 text-gray-800';
                                if ($v['status'] === 'active') $status_class = 'bg-green-100 text-green-800';
                                if ($v['status'] === 'used') $status_class = 'bg-yellow-100 text-yellow-800';
                                if ($v['status'] === 'expired') $status_class = 'bg-red-100 text-red-800';
                                echo '<span class="px-2 py-1 text-xs font-semibold rounded-full ' . $status_class . '">' . ucfirst($v['status']) . '</span>';
                                ?>
                            </td>
                            <td class="p-3 text-sm text-gray-600"><?= $v['expires_at'] ? date('d/m/Y H:i', strtotime($v['expires_at'])) : 'Không có'; ?></td>
                            <td class="p-3 text-center space-x-2">
                                <a href="#" data-page="vouchers/edit.php?id=<?= $v['voucher_id'] ?>" class="text-blue-500 hover:text-blue-700 transition-colors" title="Sửa"><i class="fas fa-edit"></i></a>
                                <a href="vouchers/delete.php?id=<?= $v['voucher_id'] ?>" class="text-red-500 hover:text-red-700 transition-colors delete-link" title="Xóa"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- Pagination -->
    <div class="mt-6 flex justify-center gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
            <a href="#" data-page="vouchers/list.php?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>" class="px-4 py-2 rounded-md <?= $i == $page ? 'bg-purple-600 text-white' : 'bg-white text-gray-700 hover:bg-purple-100' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
</div>