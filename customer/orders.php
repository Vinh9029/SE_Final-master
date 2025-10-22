<?php
session_start();
include_once __DIR__ . '/../database/db_connection.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch orders for the user
$stmt = $conn->prepare("SELECT order_id, order_date, status, total FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->bind_param("i", $user_id);
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
<div class="bg-orange-50 rounded-3xl shadow-xl p-8">
  <div class="font-bold text-2xl text-orange-600 mb-4 flex items-center gap-2"><i class="fa fa-box text-orange-500"></i> Lịch sử đơn hàng</div>
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
      <thead>
        <tr class="bg-orange-100 text-orange-700">
          <th class="px-4 py-2 rounded-tl-xl">Mã đơn</th>
          <th class="px-4 py-2">Ngày đặt</th>
          <th class="px-4 py-2">Trạng thái</th>
          <th class="px-4 py-2">Tổng tiền</th>
          <th class="px-4 py-2 rounded-tr-xl">Chi tiết</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($orders)): ?>
          <tr>
            <td colspan="5" class="px-4 py-8 text-center text-gray-500">Bạn chưa có đơn hàng nào.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($orders as $order): ?>
            <tr class="hover:bg-orange-50">
              <td class="px-4 py-2 font-bold">#<?php echo htmlspecialchars($order['order_id']); ?></td>
              <td class="px-4 py-2"><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></td>
              <td class="px-4 py-2">
                <?php list($status_text, $status_class) = get_status_label($order['status']); ?>
                <span class="<?php echo $status_class; ?> px-2 py-1 rounded-full font-bold"><?php echo $status_text; ?></span>
              </td>
              <td class="px-4 py-2 text-orange-600 font-bold"><?php echo number_format($order['total'], 0, ',', '.'); ?>đ</td>
              <td class="px-4 py-2"><button class="btn-orange hover:bg-orange-600 text-white px-4 py-1 rounded-xl font-bold shadow transition" onclick="viewOrderDetail(<?php echo $order['order_id']; ?>)">Xem</button></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
// Function to view order detail (placeholder, can be expanded to modal or AJAX)
function viewOrderDetail(orderId) {
    alert('Chi tiết đơn hàng #' + orderId + ' (chức năng xem chi tiết sẽ được triển khai)');
}
</script>
