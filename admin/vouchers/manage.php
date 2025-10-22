<?php
// admin/vouchers/manage.php
include_once __DIR__ . '/../database/db_connection.php';
session_start();
// Kiểm tra quyền admin
if (!isset($_SESSION['admin'])) {
    header('Location: ../login/index.php');
    exit;
}
// Xử lý CRUD voucher
if (isset($_POST['add'])) {
    $code = $_POST['code'];
    $desc = $_POST['desc'];
    $discount = $_POST['discount'];
    $user_id = $_POST['user_id'] ?? null;
    if ($user_id === '' || $user_id === null) {
        $user_id = null;
    }
    $stmt = $conn->prepare("INSERT INTO vouchers (user_id, code, description, discount_percent) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issd", $user_id, $code, $desc, $discount);
    $stmt->execute();
    $stmt->close();
}
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM vouchers WHERE voucher_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}
// Lấy danh sách voucher
$vouchers = [];
$result = $conn->query("SELECT v.*, u.username FROM vouchers v LEFT JOIN users u ON v.user_id = u.user_id ORDER BY v.voucher_id DESC");
while ($row = $result->fetch_assoc()) {
    $vouchers[] = $row;
}
?>
<h2 class="text-2xl font-bold mb-4">Quản lý Voucher</h2>
<form method="post" class="mb-6 flex gap-2 flex-wrap items-center">
    <input name="code" placeholder="Mã voucher" required class="border px-2 py-1 rounded" />
    <input name="desc" placeholder="Mô tả" required class="border px-2 py-1 rounded" />
    <input name="discount" type="number" step="0.01" min="0" max="100" placeholder="Giảm (%)" required class="border px-2 py-1 rounded w-24" />
    <input name="user_id" type="number" min="1" placeholder="ID khách hàng (bỏ trống cho toàn bộ)" class="border px-2 py-1 rounded w-40" />
    <button name="add" class="bg-purple-500 text-white px-4 py-1 rounded">Thêm</button>
</form>
<table class="w-full border rounded">
    <thead><tr class="bg-gray-100"><th>ID</th><th>Mã</th><th>Mô tả</th><th>Giảm (%)</th><th>Khách hàng</th><th>Đã dùng</th><th>Xóa</th></tr></thead>
    <tbody>
    <?php foreach ($vouchers as $v): ?>
        <tr>
            <td><?php echo $v['voucher_id']; ?></td>
            <td><?php echo htmlspecialchars($v['code']); ?></td>
            <td><?php echo htmlspecialchars($v['description']); ?></td>
            <td><?php echo $v['discount_percent']; ?></td>
            <td><?php echo $v['user_id'] ? htmlspecialchars($v['username']) : '<span class="text-gray-400">Toàn bộ</span>'; ?></td>
            <td><?php echo $v['is_used'] ? 'Đã dùng' : 'Chưa dùng'; ?></td>
            <td>
                <form method="post" style="display:inline">
                    <input type="hidden" name="id" value="<?php echo $v['voucher_id']; ?>" />
                    <button name="delete" class="text-red-500">Xóa</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
