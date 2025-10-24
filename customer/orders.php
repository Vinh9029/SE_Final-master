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
              <td class="px-4 py-2"><button class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-1 rounded-xl font-bold shadow transition" onclick="viewOrderDetail(<?php echo $order['order_id']; ?>)">Xem</button></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Order Detail Modal -->
<div id="orderDetailModal" onclick="closeModal()" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4 transition-opacity duration-300">
    <div id="orderDetailContent" onclick="event.stopPropagation()" class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col transform transition-transform duration-300 scale-95">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-4 border-b bg-gray-50 rounded-t-2xl">
            <h2 class="text-xl font-bold text-gray-800">Chi tiết đơn hàng</h2>
            <button onclick="closeModal()" class="text-gray-500 hover:text-red-600 transition text-2xl">
                <i class="fas fa-times-circle"></i>
            </button>
        </div>
        <!-- Modal Body -->
        <div id="modalBody" class="p-6 overflow-y-auto">
            <!-- Content will be loaded here by AJAX -->
            <div class="text-center py-10">
                <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-orange-500 mx-auto"></div>
                <p class="mt-4 text-gray-600">Đang tải dữ liệu...</p>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript equivalent of the PHP get_status_label function
function get_status_label(status) {
    switch (status) {
        case 'pending': return ['Chờ xử lý', 'bg-gray-100 text-gray-700'];
        case 'processing': return ['Đang xử lý', 'bg-yellow-100 text-yellow-700'];
        case 'completed': return ['Đã giao', 'bg-green-100 text-green-700'];
        case 'cancelled': return ['Đã hủy', 'bg-red-100 text-red-700'];
        default: return ['Không xác định', 'bg-gray-100 text-gray-700'];
    }
}

function closeModal() {
    const modal = document.getElementById('orderDetailModal');
    const content = document.getElementById('orderDetailContent');
    modal.classList.add('opacity-0');
    content.classList.add('scale-95');
    setTimeout(() => modal.classList.add('hidden'), 300); // Wait for animation to finish
}

function viewOrderDetail(orderId) {
    const modal = document.getElementById('orderDetailModal');
    modal.classList.remove('hidden');
    // Trigger fade-in and scale-up animation
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        document.getElementById('orderDetailContent').classList.remove('scale-95');
    }, 10);

    const modalBody = document.getElementById('modalBody');
    modalBody.innerHTML = `<div class="text-center py-10">
                                <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-orange-500 mx-auto"></div>
                                <p class="mt-4 text-gray-600">Đang tải dữ liệu...</p>
                           </div>`;

    fetch(`get_order_detail.php?id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const order = data.order;
                const items = data.items;
                
                let itemsHtml = items.map(item => `
                    <div class="flex items-center gap-4 py-2 border-b last:border-b-0">
                        <img src="${data.base_url}/${item.image || 'Photos/placeholder.png'}" class="w-12 h-12 object-cover rounded-lg">
                        <div class="flex-grow">
                            <p class="font-semibold">${item.name} ${item.size_name ? `<span class='text-xs text-gray-500'>(${item.size_name})</span>` : ''}</p>
                            <p class="text-sm text-gray-500">Số lượng: 
                                <span class="font-bold">${item.quantity}</span>
                            </p>
                            ${item.take_note ? `<p class="text-xs text-gray-500 italic">Ghi chú: ${item.take_note}</p>` : ''}
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-800">${(item.price * item.quantity).toLocaleString('vi-VN')}đ</p>
                            <p class="text-xs text-gray-500">${item.price.toLocaleString('vi-VN')}đ</p>
                        </div>
                    </div>
                `).join('');

                let discountHtml = '';
                if (order.voucher_code) {
                    discountHtml = `
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-gray-600">Giảm giá (${order.voucher_code}):</span>
                            <span class="font-bold text-green-600">-${order.discount_amount.toLocaleString('vi-VN')}đ</span>
                        </div>`;
                }

                modalBody.innerHTML = `
                    <div class="mb-4">
                        <p><strong>Mã đơn hàng:</strong> #${order.order_id}</p>
                        <p><strong>Ngày đặt:</strong> ${new Date(order.order_date).toLocaleDateString('vi-VN')}</p>
                        <p><strong>Trạng thái:</strong> <span class="${get_status_label(order.status)[1]} px-2 py-1 rounded-full text-xs font-bold">${get_status_label(order.status)[0]}</span></p>
                    </div>
                    <h3 class="font-bold text-lg mb-2">Các sản phẩm</h3>
                    <div class="space-y-2 mb-4">${itemsHtml}</div>
                    <div class="border-t-2 border-dashed pt-4 space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Tạm tính:</span>
                            <span class="font-semibold">${(Number(order.total) + (Number(order.discount_amount) || 0)).toLocaleString('vi-VN')}đ</span>
                        </div>
                        ${discountHtml}
                        <div class="flex justify-between items-center text-xl font-bold pt-2 border-t">
                            <span>Thành tiền:</span>
                            <span class="text-orange-600">${order.total.toLocaleString('vi-VN')}đ</span>
                        </div>
                    </div>`;
            } else {
                modalBody.innerHTML = `<p class="text-red-500 text-center">${data.message}</p>`;
            }
        })
        .catch(() => {
            modalBody.innerHTML = '<p class="text-red-500 text-center">Có lỗi xảy ra khi tải chi tiết đơn hàng.</p>';
        });
}
</script>
