<?php
session_start();
include_once __DIR__ . '/../../database/db_connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login/index.php');
    exit;
}

// Fetch all orders with customer name
$stmt = $conn->prepare("SELECT o.order_id, o.order_date, o.status, o.total, u.full_name FROM orders o JOIN users u ON o.user_id = u.user_id ORDER BY o.order_date DESC");
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
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
<div class="max-w-7xl mx-auto py-8">
  <div class="flex justify-between items-center mb-8">
    <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2"><i class="fa fa-receipt text-yellow-500"></i> Danh sách đơn hàng</h1>
  </div>
  <div class="bg-white rounded-2xl shadow-xl p-6">
    <table class="w-full text-left border-collapse">
      <thead>
        <tr class="bg-yellow-100 text-yellow-700">
          <th class="px-4 py-2 rounded-tl-xl">Mã đơn</th>
          <th class="px-4 py-2">Khách hàng</th>
          <th class="px-4 py-2">Ngày đặt</th>
          <th class="px-4 py-2">Tổng tiền</th>
          <th class="px-4 py-2">Trạng thái</th>
          <th class="px-4 py-2 rounded-tr-xl">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($orders)): ?>
          <tr>
            <td colspan="6" class="px-4 py-8 text-center text-gray-500">Không có đơn hàng nào.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($orders as $order): ?>
            <tr class="hover:bg-yellow-50 transition">
              <td class="px-4 py-2 font-bold"><?php echo htmlspecialchars($order['order_id']); ?></td>
              <td class="px-4 py-2"><?php echo htmlspecialchars($order['full_name']); ?></td>
              <td class="px-4 py-2"><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></td>
              <td class="px-4 py-2 text-orange-600 font-bold"><?php echo number_format($order['total'], 0, ',', '.'); ?>đ</td>
              <td class="px-4 py-2">
                <?php list($status_text, $status_class) = get_status_label($order['status']); ?>
                <span class="<?php echo $status_class; ?> px-2 py-1 rounded-full font-bold"><?php echo $status_text; ?></span>
              </td>
              <td class="px-4 py-2 flex gap-2">
                <a href="#" data-page="orders/detail.php?id=<?php echo $order['order_id']; ?>" class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 px-3 py-1 rounded font-bold shadow transition flex items-center gap-1"><i class="fa fa-eye"></i> Xem</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
