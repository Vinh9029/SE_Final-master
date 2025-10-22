<?php
session_start();
include_once __DIR__ . '/../../database/db_connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login/index.php');
    exit;
}

// Fetch monthly revenue data for last 6 months
$stmt = $conn->prepare("SELECT YEAR(order_date) as year, MONTH(order_date) as month, SUM(total) as revenue, COUNT(*) as orders FROM orders WHERE status = 'completed' GROUP BY year, month ORDER BY year DESC, month DESC LIMIT 6");
$stmt->execute();
$revenue_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch new customers per month
$stmt = $conn->prepare("SELECT YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as new_customers FROM users GROUP BY year, month ORDER BY year DESC, month DESC LIMIT 6");
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
        <?php for ($i = 0; $i < count($months); $i++): ?>
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
        <?php endfor; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?php echo $base_url; ?>/assets/js/chart.js"></script>
<script>
      renderBarChart(
        'revenueChart',
        <?php echo json_encode($months); ?>,
        <?php echo json_encode($revenues); ?>,
        'Tổng doanh thu (VNĐ)',
        {bg: 'rgba(255, 99, 132, 0.6)', border: 'rgba(255, 99, 132, 1)'}
      );
    </script>
