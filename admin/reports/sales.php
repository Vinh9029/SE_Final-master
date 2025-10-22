<?php
session_start();
include_once __DIR__ . '/../../database/db_connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login/index.php');
    exit;
}

// Default date range: last 7 days
$start_date = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d', strtotime('-7 days'));
$end_date = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');

// Fetch daily sales data
$stmt = $conn->prepare("SELECT DATE(order_date) as date, SUM(total) as sales, COUNT(*) as orders FROM orders WHERE status = 'completed' AND DATE(order_date) BETWEEN ? AND ? GROUP BY DATE(order_date) ORDER BY DATE(order_date)");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$sales_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Prepare data for chart and table
$dates = [];
$sales = [];
$orders = [];
$growths = [];

foreach ($sales_data as $data) {
    $dates[] = date('d/m/Y', strtotime($data['date']));
    $sales[] = $data['sales'];
    $orders[] = $data['orders'];
}

// Calculate growth
for ($i = 0; $i < count($sales); $i++) {
    if ($i > 0 && $sales[$i-1] > 0) {
        $growth = (($sales[$i] - $sales[$i-1]) / $sales[$i-1]) * 100;
        $growths[] = round($growth, 1);
    } else {
        $growths[] = 0;
    }
}
?>
<div class="max-w-5xl mx-auto py-8">
  <div class="flex justify-between items-center mb-8">
    <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2"><i class="fa fa-chart-line text-green-500"></i> Báo cáo thống kê doanh số</h1>
  </div>
  <div class="bg-white rounded-2xl shadow-xl p-8">
    <form method="get" class="mb-6">
      <label class="block text-sm font-semibold text-gray-700 mb-1">Chọn khoảng thời gian</label>
      <input type="date" name="start" value="<?php echo htmlspecialchars($start_date); ?>" class="border rounded px-4 py-2 w-40 focus:outline-none focus:ring-2 focus:ring-green-200 mr-2" />
      <span class="mx-2">-</span>
      <input type="date" name="end" value="<?php echo htmlspecialchars($end_date); ?>" class="border rounded px-4 py-2 w-40 focus:outline-none focus:ring-2 focus:ring-green-200" />
      <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-xl font-bold shadow transition ml-4">Lọc</button>
    </form>
    <div class="mb-8">
      <h2 class="text-lg font-bold text-gray-800 mb-2 flex items-center gap-2"><i class="fa fa-chart-bar text-green-500"></i> Biểu đồ doanh số</h2>
      <canvas id="salesChart" width="400" height="200"></canvas>
    </div>
    <table class="w-full text-left border-collapse">
      <thead>
        <tr class="bg-green-100 text-green-700">
          <th class="px-4 py-2 rounded-tl-xl">Ngày</th>
          <th class="px-4 py-2">Số đơn hàng</th>
          <th class="px-4 py-2">Tổng doanh số</th>
          <th class="px-4 py-2 rounded-tr-xl">Tăng trưởng</th>
        </tr>
      </thead>
      <tbody>
        <?php for ($i = 0; $i < count($dates); $i++): ?>
          <tr class="hover:bg-green-50 transition">
            <td class="px-4 py-2 font-bold"><?php echo htmlspecialchars($dates[$i]); ?></td>
            <td class="px-4 py-2"><?php echo htmlspecialchars($orders[$i]); ?></td>
            <td class="px-4 py-2 text-green-600 font-bold"><?php echo number_format($sales[$i], 0, ',', '.'); ?>đ</td>
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
      renderLineChart(
        'salesChart',
        <?php echo json_encode($dates); ?>,
        <?php echo json_encode($sales); ?>,
        'Tổng doanh số (VNĐ)',
        {bg: 'rgba(54, 162, 235, 0.2)', border: 'rgba(54, 162, 235, 1)'}
      );
    </script>
