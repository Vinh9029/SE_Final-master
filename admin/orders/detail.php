<?php
session_start();
include_once __DIR__ . '/../../database/db_connection.php';
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
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = $_POST['status'] ?? '';
    if (in_array($new_status, ['pending', 'processing', 'completed', 'cancelled'])) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        if ($stmt->execute()) {
            $message = 'Cập nhật trạng thái thành công!';
        } else {
            $message = 'Có lỗi xảy ra.';
        }
        $stmt->close();
    }
}

// Fetch order details
$stmt = $conn->prepare("SELECT o.order_id, o.order_date, o.status, o.total, u.full_name, u.email, u.phone FROM orders o JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: list.php');
    exit;
}

// Fetch order items
$stmt = $conn->prepare("SELECT oi.quantity, oi.price, p.name FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
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
  <div class="bg-white rounded-2xl shadow-xl p-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2"><i class="fa fa-eye text-yellow-500"></i> Chi tiết đơn hàng #<?php echo htmlspecialchars($order['order_id']); ?></h1>
    <?php if ($message): ?>
      <div class="mb-4 p-4 rounded-xl bg-green-100 text-green-800">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>
    <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-2">
      <div class="font-semibold text-gray-700">Khách hàng: <span class="text-pink-600"><?php echo htmlspecialchars($order['full_name']); ?></span></div>
      <div class="font-semibold text-gray-700">Email: <span class="text-pink-600"><?php echo htmlspecialchars($order['email']); ?></span></div>
      <div class="font-semibold text-gray-700">Số điện thoại: <span class="text-pink-600"><?php echo htmlspecialchars($order['phone']); ?></span></div>
      <div class="font-semibold text-gray-700">Ngày đặt: <span class="text-orange-600"><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></span></div>
      <div class="font-semibold text-gray-700">Tổng tiền: <span class="text-yellow-600"><?php echo number_format($order['total'], 0, ',', '.'); ?>đ</span></div>
      <div class="font-semibold text-gray-700">Trạng thái: 
        <?php list($status_text, $status_class) = get_status_label($order['status']); ?>
        <span class="<?php echo $status_class; ?> px-2 py-1 rounded-full font-bold"><?php echo $status_text; ?></span>
      </div>
    </div>
    <form method="post" class="mb-6">
      <label class="block text-sm font-semibold text-gray-700 mb-1">Cập nhật trạng thái</label>
      <select name="status" class="border rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-yellow-200">
        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
        <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Đã giao</option>
        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
      </select>
      <button type="submit" class="mt-2 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded font-bold shadow transition">Cập nhật</button>
    </form>
    <table class="w-full text-left border-collapse mb-6">
      <thead>
        <tr class="bg-yellow-100 text-yellow-700">
          <th class="px-4 py-2 rounded-tl-xl">Sản phẩm</th>
          <th class="px-4 py-2">Số lượng</th>
          <th class="px-4 py-2">Đơn giá</th>
          <th class="px-4 py-2 rounded-tr-xl">Thành tiền</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
          <tr class="hover:bg-yellow-50 transition">
            <td class="px-4 py-2"><?php echo htmlspecialchars($item['name']); ?></td>
            <td class="px-4 py-2"><?php echo htmlspecialchars($item['quantity']); ?></td>
            <td class="px-4 py-2"><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</td>
            <td class="px-4 py-2"><?php echo number_format($item['quantity'] * $item['price'], 0, ',', '.'); ?>đ</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <a href="#" data-page="orders/list.php" class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 px-4 py-2 rounded font-bold shadow transition flex items-center gap-1"><i class="fa fa-arrow-left"></i> Quay lại danh sách đơn hàng</a>
  </div>
</div>
