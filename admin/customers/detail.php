<?php
session_start();
include_once '../../database/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login/index.php');
    exit;
}

// Get customer_id from query string
$customer_id = $_GET['id'] ?? null;
if (!$customer_id) {
    echo "<div class='p-4 bg-red-100 border border-red-500 text-red-700'>Không có ID khách hàng.</div>";
    exit;
}

// Fetch customer information
// Use a JOIN to get the rank name directly from the database
$stmt = $conn->prepare("
    SELECT 
        u.user_id, 
        u.username, 
        u.email, 
        u.full_name, 
        u.phone, 
        u.created_at, 
        u.points,
        mr.rank_name,
        mr.theme_class
    FROM users u
    LEFT JOIN membership_ranks mr ON u.rank_id = mr.rank_id
    WHERE u.user_id = ? AND u.role = 'customer'
");
$stmt->bind_param("s", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();


if (!$customer) {
    echo "<div class='p-4 bg-red-100 border border-red-500 text-red-700'>Không tìm thấy khách hàng.</div>";
    exit;
}

// Fetch customer order history
$order_stmt = $conn->prepare("SELECT order_id, order_date, total, status FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$order_stmt->bind_param("s", $customer_id);
$order_stmt->execute();
$orders = $order_stmt->get_result();

// Function to get status style
function getStatusClass($status) {
    switch (strtolower($status)) {
        case 'completed':
            return 'bg-green-100 text-green-700';
        case 'processing':
            return 'bg-yellow-100 text-yellow-700';
        case 'cancelled':
            return 'bg-red-100 text-red-700';
        default:
            return 'bg-gray-100 text-gray-700';
    }
}

?>

<div class="max-w-2xl mx-auto py-8">
  <div class="bg-white rounded-2xl shadow-xl p-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2"><i class="fa fa-user text-blue-500"></i> Chi tiết khách hàng KH<?php echo htmlspecialchars($customer['user_id']); ?></h1>
    <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="font-semibold text-gray-700">Tên khách hàng: <span class="text-pink-600 font-normal"><?php echo htmlspecialchars($customer['full_name'] ?? 'N/A'); ?></span></div>
      <div class="font-semibold text-gray-700">Email: <span class="text-blue-600 font-normal"><?php echo htmlspecialchars($customer['email'] ?? 'N/A'); ?></span></div>
      <div class="font-semibold text-gray-700">Số điện thoại: <span class="text-orange-600 font-normal"><?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></span></div>
      <div class="font-semibold text-gray-700">Ngày đăng ký: <span class="text-yellow-600 font-normal"><?php echo date("d/m/Y", strtotime($customer['created_at'])); ?></span></div>
      <div class="font-semibold text-gray-700">Điểm tích lũy: <span class="text-yellow-500 font-normal"><?php echo number_format($customer['points'] ?? 0); ?> ⭐</span></div>
      <div class="font-semibold text-gray-700">Hạng thành viên: <span class="<?php echo htmlspecialchars($customer['theme_class'] ?? 'bg-gray-100 text-gray-700'); ?> px-2 py-1 rounded-full font-bold"><?php echo htmlspecialchars($customer['rank_name'] ?? 'Chưa có hạng'); ?></span></div>
    </div>
    <div class="mt-6">
      <h2 class="text-lg font-bold text-gray-800 mb-2 flex items-center gap-2"><i class="fa fa-history text-pink-500"></i> Lịch sử đơn hàng</h2>
      <?php if ($orders->num_rows > 0): ?>
      <table class="w-full text-left border-collapse mb-2">
        <thead>
          <tr class="bg-blue-100 text-blue-700">
            <th class="px-4 py-2 rounded-tl-xl">Mã đơn</th>
            <th class="px-4 py-2">Ngày đặt</th>
            <th class="px-4 py-2">Tổng tiền</th>
            <th class="px-4 py-2 rounded-tr-xl">Trạng thái</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($order = $orders->fetch_assoc()): ?>
          <tr class="hover:bg-blue-50 transition">
            <td class="px-4 py-2 font-bold border-b border-gray-200">#<?php echo htmlspecialchars($order['order_id']); ?></td>
            <td class="px-4 py-2 border-b border-gray-200"><?php echo date("d/m/Y", strtotime($order['order_date'])); ?></td>
            <td class="px-4 py-2 text-orange-600 font-bold border-b border-gray-200"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</td>
            <td class="px-4 py-2 border-b border-gray-200"><span class="<?php echo getStatusClass($order['status']); ?> px-2 py-1 rounded-full font-bold"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></span></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p class="text-gray-500 mt-4">Chưa có đơn hàng nào.</p>
      <?php endif; ?>
    </div>
    <a href="#" data-page="customers/list.php" class="mt-6 inline-flex items-center gap-2 bg-blue-100 hover:bg-blue-200 text-blue-700 px-4 py-2 rounded-lg font-bold shadow transition"><i class="fa fa-arrow-left"></i> Quay lại danh sách khách hàng</a>
  </div>
</div>