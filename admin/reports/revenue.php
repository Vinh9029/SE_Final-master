<?php
session_start();
include_once __DIR__ . '/../../database/db_connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login/index.php');
    exit;
}

// Thiết lập khoảng thời gian mặc định: 6 tháng gần nhất
$default_end_date = date('Y-m');
$default_start_date = date('Y-m', strtotime('-5 months'));

$start_month = isset($_GET['start']) ? $_GET['start'] : $default_start_date;
$end_month = isset($_GET['end']) ? $_GET['end'] : $default_end_date;

// Chuyển đổi Y-m sang ngày đầu tháng và cuối tháng để truy vấn
$start_date_sql = date('Y-m-01', strtotime($start_month));
$end_date_sql = date('Y-m-t', strtotime($end_month));

// Lấy dữ liệu doanh thu theo tháng trong khoảng thời gian đã chọn
$stmt = $conn->prepare("SELECT YEAR(order_date) as year, MONTH(order_date) as month, SUM(total) as revenue, COUNT(*) as orders FROM orders WHERE status = 'completed' AND order_date BETWEEN ? AND ? GROUP BY year, month ORDER BY year DESC, month DESC");
$stmt->bind_param("ss", $start_date_sql, $end_date_sql);
$stmt->execute();
$revenue_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Lấy dữ liệu khách hàng mới theo tháng trong khoảng thời gian đã chọn
$stmt = $conn->prepare("SELECT YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as new_customers FROM users WHERE created_at BETWEEN ? AND ? GROUP BY year, month ORDER BY year DESC, month DESC");
$stmt->bind_param("ss", $start_date_sql, $end_date_sql);
$stmt->execute();
$customer_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Combine data
$months = [];
$revenues = [];
$orders = [];
$new_customers = [];
$growths = [];

foreach ($revenue_data as $rev) {
    $key = $rev['month'] . '/' . $rev['year'];
    $months[] = $key;
    $revenues[] = $rev['revenue'];
    $orders[] = $rev['orders'];
    $new_customers[$key] = 0; // default
}

foreach ($customer_data as $cust) {
    $key = $cust['month'] . '/' . $cust['year'];
    if (isset($new_customers[$key])) {
        $new_customers[$key] = $cust['new_customers'];
    }
}

// Calculate growth
$prev_revenue = null;
for ($i = count($revenues) - 1; $i >= 0; $i--) {
    if ($prev_revenue !== null && $prev_revenue > 0) {
        $growth = (($revenues[$i] - $prev_revenue) / $prev_revenue) * 100;
        $growths[$i] = round($growth, 1);
    } else {
        $growths[$i] = 0;
    }
    $prev_revenue = $revenues[$i];
}
$growths = array_reverse($growths);
$months = array_reverse($months);
$revenues = array_reverse($revenues);
$orders = array_reverse($orders);
$new_customers = array_reverse(array_values($new_customers));
?>
<div class="max-w-5xl mx-auto py-8">
  <div class="flex justify-between items-center mb-8">
    <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2"><i class="fa fa-coins text-purple-500"></i> Báo cáo doanh thu</h1>
  </div>
  <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
    <form method="get" class="mb-2">
      <label class="block text-sm font-semibold text-gray-700 mb-1">Chọn khoảng thời gian (Tháng/Năm)</label>
      <input type="month" name="start" value="<?php echo htmlspecialchars($start_month); ?>" class="border rounded px-4 py-2 w-48 focus:outline-none focus:ring-2 focus:ring-purple-200 mr-2" />
      <span class="mx-2">-</span>
      <input type="month" name="end" value="<?php echo htmlspecialchars($end_month); ?>" class="border rounded px-4 py-2 w-48 focus:outline-none focus:ring-2 focus:ring-purple-200" />
      <button type="submit" class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-2 rounded-xl font-bold shadow transition ml-4">Lọc</button>
    </form>
  </div>
  <div class="bg-white rounded-2xl shadow-xl p-8">
    <div class="mb-8">
      <h2 class="text-lg font-bold text-gray-800 mb-2 flex items-center gap-2"><i class="fa fa-chart-line text-purple-500"></i> Biểu đồ doanh thu</h2>
      <canvas id="revenueChart" width="400" height="200"></canvas>
    </div>
    <table class="w-full text-left border-collapse">
      <thead>
        <tr class="bg-purple-100 text-purple-700">
          <th class="px-4 py-2 rounded-tl-xl">Tháng</th>
          <th class="px-4 py-2">Tổng doanh thu</th>
          <th class="px-4 py-2">Số đơn hàng</th>
          <th class="px-4 py-2">Khách hàng mới</th>
          <th class="px-4 py-2 rounded-tr-xl">Tăng trưởng</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($months)): for ($i = 0; $i < count($months); $i++): ?>
          <tr class="hover:bg-purple-50 transition">
            <td class="px-4 py-2 font-bold"><?php echo htmlspecialchars($months[$i]); ?></td>
            <td class="px-4 py-2 text-purple-600 font-bold"><?php echo number_format($revenues[$i], 0, ',', '.'); ?>đ</td>
            <td class="px-4 py-2"><?php echo htmlspecialchars($orders[$i]); ?></td>
            <td class="px-4 py-2"><?php echo htmlspecialchars($new_customers[$i]); ?></td>
            <td class="px-4 py-2">
              <?php if ($growths[$i] > 0): ?>
                <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full font-bold">+<?php echo $growths[$i]; ?>%</span>
              <?php elseif ($growths[$i] < 0): ?>
                <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full font-bold"><?php echo $growths[$i]; ?>%</span>
              <?php else: ?>
                <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded-full font-bold">0%</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endfor; else: ?>
            <tr>
                <td colspan="5" class="text-center py-8 text-gray-500">Không có dữ liệu cho khoảng thời gian này.</td>
            </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<script>
      renderBarChart(
        'revenueChart',
        <?php echo json_encode($months); ?>,
        <?php echo json_encode($revenues); ?>,
        'Tổng doanh thu (VNĐ)',
        {bg: 'rgba(168, 85, 247, 0.6)', border: 'rgba(147, 51, 234, 1)'}
      );
    </script>
