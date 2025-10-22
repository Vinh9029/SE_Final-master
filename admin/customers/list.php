<?php
include_once '../../database/db_connection.php';
// Lấy danh sách khách hàng
$sql = "SELECT user_id, username, email, full_name, phone, address, created_at FROM users WHERE role = 'customer' ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<div class="max-w-7xl mx-auto py-8">
  <div class="flex justify-between items-center mb-8">
    <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2"><i class="fa fa-users text-blue-500"></i> Danh sách khách hàng</h1>
  </div>
  <div class="bg-white rounded-2xl shadow-xl p-6">
    <table class="w-full text-left border-collapse">
      <thead>
        <tr class="bg-blue-100 text-blue-700">
          <th class="px-4 py-2 rounded-tl-xl">ID</th>
          <th class="px-4 py-2">Username</th>
          <th class="px-4 py-2">Email</th>
          <th class="px-4 py-2">Họ tên</th>
          <th class="px-4 py-2">SĐT</th>
          <th class="px-4 py-2">Địa chỉ</th>
          <th class="px-4 py-2">Ngày tạo</th>
          <th class="px-4 py-2 rounded-tr-xl">Chi tiết</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr class="hover:bg-blue-50 transition">
          <td class="px-4 py-2 font-bold"><?= $row['user_id'] ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($row['username']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($row['email']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($row['full_name']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($row['phone']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($row['address']) ?></td>
          <td class="px-4 py-2"><?= $row['created_at'] ?></td>
          <td class="px-4 py-2"><a href="detail.php?id=<?= $row['user_id'] ?>" class="text-blue-500 hover:underline">Xem</a></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
