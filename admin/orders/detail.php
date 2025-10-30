<?php
session_start();
include_once __DIR__ . '/../../database/db_connection.php';
include_once __DIR__ . '/../../includes/handlers/notification/createNotification.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login/index.php');
    exit;
}

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: list.php');
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status']) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    $new_status = $_POST['status'] ?? '';
    $response = ['success' => false, 'message' => 'Trạng thái không hợp lệ.'];

    if (in_array($new_status, ['pending', 'processing', 'completed', 'cancelled'])) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        if ($stmt->execute()) {
            // Fetch order details for notification and points
            $order_info_stmt = $conn->prepare("SELECT user_id, total FROM orders WHERE order_id = ?");
            $order_info_stmt->bind_param("i", $order_id);
            $order_info_stmt->execute();
            $order_info = $order_info_stmt->get_result()->fetch_assoc();
            $customer_id = $order_info['user_id'];

            // Status translation for notification
            $status_map = [
                'pending' => 'đang chờ xử lý',
                'processing' => 'đang được xử lý',
                'completed' => 'đã được giao thành công',
                'cancelled' => 'đã bị hủy'
            ];
            $status_text = $status_map[$new_status] ?? 'đã được cập nhật';

            // Create Order Status Notification
            create_notification(
                $conn,
                $customer_id,
                'order_status',
                "Cập nhật đơn hàng #{$order_id}",
                "Đơn hàng của bạn {$status_text}.",
                $order_id,
                '/customer/orders.php'
            );

            // If order is completed, calculate and add loyalty points
            if ($new_status === 'completed') {
                $points_to_add = floor($order_info['total'] / 1000);
                if ($points_to_add > 0) {
                    // Check if user exists in loyalty_points table
                    $check_points_stmt = $conn->prepare("SELECT point_id FROM loyalty_points WHERE user_id = ?");
                    $check_points_stmt->bind_param("i", $customer_id);
                    $check_points_stmt->execute();
                    $has_points_record = $check_points_stmt->get_result()->num_rows > 0;
                    $check_points_stmt->close();

                    if ($has_points_record) {
                        $update_points_stmt = $conn->prepare("UPDATE loyalty_points SET points = points + ? WHERE user_id = ?");
                        $update_points_stmt->bind_param("ii", $points_to_add, $customer_id);
                        $update_points_stmt->execute();
                        $update_points_stmt->close();
                    } else {
                        $insert_points_stmt = $conn->prepare("INSERT INTO loyalty_points (user_id, points) VALUES (?, ?)");
                        $insert_points_stmt->bind_param("ii", $customer_id, $points_to_add);
                        $insert_points_stmt->execute();
                        $insert_points_stmt->close();
                    }

                    // Create Loyalty Points Notification
                    create_notification(
                        $conn,
                        $customer_id,
                        'loyalty_point',
                        "Bạn đã nhận được điểm thưởng!",
                        "Bạn đã được cộng {$points_to_add} điểm từ đơn hàng #{$order_id}.",
                        $order_id,
                        '/customer/account.php?page=points' // Assuming a points page exists
                    );
                }
            }

            $response = ['success' => true, 'message' => 'Cập nhật trạng thái thành công!', 'redirect' => 'orders/detail.php?id=' . $order_id];
        } else {
            $response['message'] = 'Có lỗi xảy ra khi cập nhật.';
        }
        $stmt->close();
    }
    echo json_encode($response);
    exit;
} else {
    $message = ''; // Để xử lý cho trường hợp không phải AJAX (nếu cần)
}

// Fetch order details
$stmt = $conn->prepare("SELECT o.order_id, o.order_date, o.status, o.total, o.voucher_code, o.discount_amount, u.full_name, u.email, u.phone FROM orders o JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: list.php');
    exit;
}

// Fetch order items
$stmt = $conn->prepare("SELECT oi.quantity, oi.price, p.name, ps.size_name FROM order_items oi JOIN products p ON oi.product_id = p.product_id LEFT JOIN product_sizes ps ON oi.size_id = ps.size_id WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Function to get status label
function get_status_label($status) {
    switch ($status) {
        case 'pending': return ['Chờ xử lý', 'bg-gray-100 text-gray-700'];
        case 'processing': return ['Đang xử lý', 'bg-yellow-100 text-yellow-700'];
        case 'completed': return ['Đã giao', 'bg-green-100 text-green-700'];
        case 'cancelled': return ['Đã hủy', 'bg-red-100 text-red-700'];
        default: return ['Không xác định', 'bg-gray-100 text-gray-700'];
    }
}
?>
<div class="max-w-2xl mx-auto py-8">
  <div class="bg-white rounded-2xl shadow-xl p-8 space-y-6">
    <div class="flex justify-between items-center border-b pb-4">
        <h1 class="text-2xl font-bold text-brown flex items-center gap-2"><i class="fa fa-receipt text-yellow-600"></i> Chi tiết đơn hàng #<?php echo htmlspecialchars($order['order_id']); ?></h1>
        <?php list($status_text, $status_class) = get_status_label($order['status']); ?>
        <span class="<?php echo $status_class; ?> px-3 py-1 rounded-full font-bold text-sm"><?php echo $status_text; ?></span>
    </div>

    <!-- Customer Info -->
    <div class="bg-yellow-50 p-4 rounded-lg">
        <h2 class="font-bold text-brown mb-2">Thông tin khách hàng</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-1 text-sm">
            <p><strong class="text-gray-600">Khách hàng:</strong> <span class="font-semibold text-brown"><?php echo htmlspecialchars($order['full_name']); ?></span></p>
            <p><strong class="text-gray-600">Email:</strong> <span class="font-semibold text-brown"><?php echo htmlspecialchars($order['email']); ?></span></p>
            <p><strong class="text-gray-600">Số điện thoại:</strong> <span class="font-semibold text-brown"><?php echo htmlspecialchars($order['phone']); ?></span></p>
            <p><strong class="text-gray-600">Ngày đặt:</strong> <span class="font-semibold"><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></span></p>
        </div>
    </div>

    <!-- Update Status Form -->
    <form method="post" action="orders/detail.php?id=<?php echo $order_id; ?>" class="bg-gray-50 p-4 rounded-lg">
      <label class="block text-sm font-semibold text-gray-700 mb-2">Cập nhật trạng thái đơn hàng</p>
      <div class="flex items-center gap-4">
        <select name="status" class="border rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-yellow-400">
          <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
          <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
          <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Đã giao</option>
          <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
        </select>
        <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-5 py-2 rounded-lg font-bold shadow transition whitespace-nowrap">Cập nhật</button>
      </div>
    </form>

    <!-- Order Items -->
    <div>
        <h2 class="font-bold text-brown mb-2">Các sản phẩm trong đơn</h2>
        <div class="overflow-x-auto rounded-lg border">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-yellow-100 text-yellow-800 text-sm">
                    <th class="px-4 py-2">Sản phẩm</th>
                    <th class="px-4 py-2 text-center">Số lượng</th>
                    <th class="px-4 py-2 text-right">Đơn giá</th>
                    <th class="px-4 py-2 text-right">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr class="hover:bg-yellow-50 transition border-b last:border-b-0">
                        <td class="px-4 py-3">
                        <?php echo htmlspecialchars($item['name']); ?>
                        <?php if (!empty($item['size_name'])): ?><span class="text-xs text-gray-500 ml-1">(<?php echo htmlspecialchars($item['size_name']); ?>)</span><?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center"><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td class="px-4 py-3 text-right"><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</td>
                        <td class="px-4 py-3 text-right font-semibold"><?php echo number_format($item['quantity'] * $item['price'], 0, ',', '.'); ?>đ</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Order Total -->
    <div class="border-t-2 border-dashed border-yellow-200 pt-4 space-y-2">
        <div class="flex justify-between items-center text-gray-600">
            <span>Tạm tính:</span>
            <span class="font-semibold"><?php echo number_format($order['total'] + ($order['discount_amount'] ?? 0), 0, ',', '.'); ?>đ</span>
        </div>
        <?php if ($order['voucher_code']): ?>
            <div class="flex justify-between items-center text-green-600">
                <span>Giảm giá (<?php echo htmlspecialchars($order['voucher_code']); ?>):</span>
                <span class="font-bold">-<?php echo number_format($order['discount_amount'], 0, ',', '.'); ?>đ</span>
            </div>
        <?php endif; ?>
        <div class="flex justify-between items-center text-xl font-bold pt-2 border-t mt-2">
            <span class="text-brown">Tổng thanh toán:</span>
            <span class="text-yellow-700"><?php echo number_format($order['total'], 0, ',', '.'); ?>đ</span>
        </div>
    </div>

    <a href="#" data-page="orders/list.php" class="inline-flex items-center gap-2 bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg font-bold shadow-sm transition"><i class="fa fa-arrow-left"></i> Quay lại danh sách</a>
  </div>
</div>
