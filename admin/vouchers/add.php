<?php
include_once __DIR__ . '/../../database/db_connection.php';
global $conn;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code']);
    $title = trim($_POST['title']);
    $discount_value = (float)$_POST['discount_value'];
    $discount_type = $_POST['discount_type'];
    $min_order_value = (float)$_POST['min_order_value'];
    $user_id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
    $expires_at = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;

    $message = '';
    if ($discount_type === 'percent' && ($discount_value < 0 || $discount_value > 100)) {
        $message = "Giá trị giảm giá theo % phải từ 0 đến 100.";
    }

    if (empty($message)) {
        $stmt = $conn->prepare("INSERT INTO vouchers (user_id, code, title, discount_type, discount_value, min_order_value, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssdds", $user_id, $code, $title, $discount_type, $discount_value, $min_order_value, $expires_at);

        if ($stmt->execute()) {
            // Nếu là voucher cho toàn bộ người dùng, gửi thông báo
            if ($user_id === null) {
                $new_voucher_id = $stmt->insert_id;

                // Lấy ID của tất cả khách hàng
                $customers_result = $conn->query("SELECT user_id FROM users WHERE role = 'customer'");
                $customer_ids = [];
                while ($row = $customers_result->fetch_assoc()) {
                    $customer_ids[] = $row['user_id'];
                }

                if (!empty($customer_ids)) {
                    $notif_stmt = $conn->prepare("INSERT INTO notifications (customer_id, type, title, body, related_id, url) VALUES (?, 'new_voucher', ?, ?, ?, ?)");
                    $notif_title = "Bạn có voucher mới!";
                    $notif_body = "Bạn vừa nhận được voucher '{$code}' - {$title}. Dùng ngay!";
                    $notif_url = "customer/account.php?tab=vouchers";
                    
                    foreach ($customer_ids as $customer_id) {
                        $notif_stmt->bind_param("isiss", $customer_id, $notif_title, $notif_body, $new_voucher_id, $notif_url);
                        $notif_stmt->execute();
                    }
                    $notif_stmt->close();
                }
            }
            echo json_encode(['success' => true, 'message' => 'Thêm voucher thành công!', 'redirect' => 'vouchers/list.php']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Thêm voucher thất bại. Mã voucher có thể đã tồn tại.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => $message]);
    }
    exit;
}
?>

<div class="p-6 bg-gray-50 min-h-screen">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Thêm Voucher Mới</h2>

    <div class="bg-white p-8 rounded-lg shadow-md">
        <form method="post" action="vouchers/add.php" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tên chương trình</label>
                <input name="title" placeholder="Ví dụ: Khuyến mãi cuối năm" required class="w-full border px-3 py-2 rounded-md focus:ring-purple-500 focus:border-purple-500" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Mã voucher</label>
                <input name="code" placeholder="Ví dụ: SALE50" required class="w-full border px-3 py-2 rounded-md focus:ring-purple-500 focus:border-purple-500" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Giảm giá</label>
                <div class="flex gap-2">
                    <select name="discount_type" class="border px-3 py-2 rounded-md focus:ring-purple-500 focus:border-purple-500">
                        <option value="percent">%</option>
                        <option value="fixed">VND</option>
                    </select>
                    <input name="discount_value" type="number" step="any" min="0" placeholder="Giá trị giảm" required class="w-full border px-3 py-2 rounded-md focus:ring-purple-500 focus:border-purple-500" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Đơn hàng tối thiểu</label>
                <input name="min_order_value" type="number" min="0" value="0" placeholder="Đơn hàng tối thiểu" class="w-full border px-3 py-2 rounded-md focus:ring-purple-500 focus:border-purple-500" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">ID khách hàng (để trống nếu cho mọi người)</label>
                <input name="user_id" type="number" min="1" placeholder="ID khách hàng" class="w-full border px-3 py-2 rounded-md focus:ring-purple-500 focus:border-purple-500" />
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Ngày hết hạn</label>
                <input name="expires_at" type="datetime-local" title="Ngày hết hạn" class="w-full border px-3 py-2 rounded-md focus:ring-purple-500 focus:border-purple-500" />
            </div>
            <div class="md:col-span-2 flex justify-center gap-4 mt-4">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold px-6 py-2 rounded-md transition-colors"><i class="fas fa-save"></i> Lưu Voucher</button>
                <a href="#" data-page="vouchers/list.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold px-6 py-2 rounded-md transition-colors">Hủy</a>
            </div>
        </form>
    </div>
</div>