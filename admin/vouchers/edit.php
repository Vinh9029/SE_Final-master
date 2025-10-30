<?php
include_once __DIR__ . '/../../database/db_connection.php';
global $conn;

$voucher = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM vouchers WHERE voucher_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $voucher = $result->fetch_assoc();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $voucher) {
    $id = (int)$_POST['voucher_id'];
    $code = trim($_POST['code']);
    $title = trim($_POST['title']);
    $discount_value = (float)$_POST['discount_value'];
    $discount_type = $_POST['discount_type'];
    $min_order_value = (float)$_POST['min_order_value'];
    $user_id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
    $status = $_POST['status'];
    $expires_at = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;

    $message = '';
    if ($discount_type === 'percent' && ($discount_value < 0 || $discount_value > 100)) {
        $message = "Giá trị giảm giá theo % phải từ 0 đến 100.";
    }

    if (empty($message)) {
        $stmt = $conn->prepare("UPDATE vouchers SET user_id=?, code=?, title=?, discount_type=?, discount_value=?, min_order_value=?, status=?, expires_at=? WHERE voucher_id=?");
        $stmt->bind_param("isssddssi", $user_id, $code, $title, $discount_type, $discount_value, $min_order_value, $status, $expires_at, $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật voucher thành công!', 'redirect' => 'vouchers/list.php']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại. Mã voucher có thể đã tồn tại.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => $message]);
    }
    exit;
}

if (!$voucher) {
    echo "<div class='p-6 text-red-500'>Không tìm thấy voucher.</div>";
    exit;
}
?>

<div class="p-6 bg-gray-50 min-h-screen">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Chỉnh sửa Voucher #<?= $voucher['voucher_id'] ?></h2>

    <div class="bg-white p-8 rounded-lg shadow-md">
        <form method="post" action="vouchers/edit.php?id=<?= $voucher['voucher_id'] ?>" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <input type="hidden" name="voucher_id" value="<?= $voucher['voucher_id'] ?>">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tên chương trình</label>
                <input name="title" value="<?= htmlspecialchars($voucher['title']) ?>" required class="w-full border px-3 py-2 rounded-md focus:ring-purple-500 focus:border-purple-500" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Mã voucher</label>
                <input name="code" value="<?= htmlspecialchars($voucher['code']) ?>" required class="w-full border px-3 py-2 rounded-md focus:ring-purple-500 focus:border-purple-500" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Giảm giá</label>
                <div class="flex gap-2">
                    <select name="discount_type" class="border px-3 py-2 rounded-md focus:ring-purple-500 focus:border-purple-500">
                        <option value="percent" <?= $voucher['discount_type'] == 'percent' ? 'selected' : '' ?>>%</option>
                        <option value="fixed" <?= $voucher['discount_type'] == 'fixed' ? 'selected' : '' ?>>VND</option>
                    </select>
                    <input name="discount_value" type="number" value="<?= $voucher['discount_value'] ?>" step="any" min="0" required class="w-full border px-3 py-2 rounded-md focus:ring-purple-500 focus:border-purple-500" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Đơn hàng tối thiểu</label>
                <input name="min_order_value" type="number" value="<?= $voucher['min_order_value'] ?>" min="0" class="w-full border px-3 py-2 rounded-md focus:ring-purple-500 focus:border-purple-500" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">ID khách hàng (để trống nếu cho mọi người)</label>
                <input name="user_id" type="number" value="<?= $voucher['user_id'] ?>" min="1" class="w-full border px-3 py-2 rounded-md focus:ring-purple-500 focus:border-purple-500" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Trạng thái</label>
                <select name="status" class="w-full border px-3 py-2 rounded-md focus:ring-purple-500 focus:border-purple-500">
                    <option value="active" <?= $voucher['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="used" <?= $voucher['status'] == 'used' ? 'selected' : '' ?>>Used</option>
                    <option value="expired" <?= $voucher['status'] == 'expired' ? 'selected' : '' ?>>Expired</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Ngày hết hạn</label>
                <input name="expires_at" type="datetime-local" value="<?= $voucher['expires_at'] ? date('Y-m-d\TH:i', strtotime($voucher['expires_at'])) : '' ?>" class="w-full border px-3 py-2 rounded-md focus:ring-purple-500 focus:border-purple-500" />
            </div>
            <div class="md:col-span-2 flex justify-center gap-4 mt-6">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold px-6 py-2 rounded-md transition-colors"><i class="fas fa-save"></i> Lưu thay đổi</button>
                <a href="#" data-page="vouchers/list.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold px-6 py-2 rounded-md transition-colors">Hủy</a>
            </div>
        </form>
    </div>
</div>