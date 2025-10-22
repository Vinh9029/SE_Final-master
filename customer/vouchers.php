<?php
// vouchers.php - Hiển thị danh sách voucher của user và voucher toàn bộ
session_start();
include_once __DIR__ . '/../database/db_connection.php';
$user_id = $_SESSION['user_id'] ?? null;
$vouchers = [];
if ($user_id) {
    // Lấy voucher của user và voucher toàn bộ (user_id IS NULL)
    $stmt = $conn->prepare("SELECT * FROM vouchers WHERE user_id = ? OR user_id IS NULL ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $vouchers[] = $row;
    }
    $stmt->close();
}
?>
<div class="max-w-2xl mx-auto mt-8">
  <h2 class="text-2xl font-bold mb-4 text-green-600 flex items-center gap-2"><i class="fa fa-ticket-alt"></i> Voucher của tôi</h2>
  <?php if (empty($vouchers)): ?>
    <div class="text-gray-500 text-center py-8">Bạn chưa có voucher nào.</div>
  <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <?php foreach ($vouchers as $v): ?>
        <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 <?php echo $v['status'] === 'used' ? 'border-gray-400 opacity-60' : 'border-green-500'; ?> flex flex-col gap-2">
          <div class="flex items-center gap-2 mb-2">
            <span class="font-bold text-lg text-green-600"><?php echo htmlspecialchars($v['code']); ?></span>
            <?php if ($v['status'] === 'used'): ?><span class="text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded">Đã dùng</span><?php endif; ?>
            <?php if ($v['status'] === 'expired'): ?><span class="text-xs bg-red-200 text-red-600 px-2 py-1 rounded">Hết hạn</span><?php endif; ?>
          </div>
          <div class="text-sm text-gray-700 mb-1"><?php echo isset($v['program_name']) ? htmlspecialchars($v['program_name']) : ''; ?></div>
          <div class="font-semibold text-yellow-600">Giảm <?php echo $v['discount_percent']; ?>%</div>
          <?php if ($v['min_order_value'] > 0): ?>
            <div class="text-xs text-gray-500">Áp dụng cho đơn từ <?php echo number_format($v['min_order_value']); ?>đ</div>
          <?php endif; ?>
          <?php if ($v['expires_at']): ?>
            <div class="text-xs text-gray-400">HSD: <?php echo date('d/m/Y', strtotime($v['expires_at'])); ?></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>