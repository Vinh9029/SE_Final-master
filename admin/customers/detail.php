<?php
session_start();
include_once '../../config.php';
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
$stmt = $conn->prepare("
    SELECT 
        u.user_id, 
        u.username, 
        u.email, 
        u.full_name, 
        u.phone,
        u.created_at,
        u.avatar_image,
        u.deactivated_account
    FROM users u
    WHERE u.user_id = ? AND u.role = 'customer'
");
$stmt->bind_param("s", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();


// Fetch loyalty points and determine rank
$points_stmt = $conn->prepare("SELECT points FROM loyalty_points WHERE user_id = ?");
$points_stmt->bind_param("i", $customer_id);
$points_stmt->execute();
$points_result = $points_stmt->get_result()->fetch_assoc();
$customer['points'] = $points_result['points'] ?? 0;

// Determine rank based on points (example logic)
$rank_name = 'Đồng';
$theme_class = 'bg-yellow-200 text-yellow-800';
if ($customer['points'] >= 1000) {
    $rank_name = 'Vàng';
    $theme_class = 'bg-yellow-400 text-yellow-900';
} elseif ($customer['points'] >= 500) {
    $rank_name = 'Bạc';
    $theme_class = 'bg-gray-300 text-gray-800';
}
if (!$customer) {
    echo "<div class='p-4 bg-red-100 border border-red-500 text-red-700'>Không tìm thấy khách hàng.</div>";
    exit;
}

// Fetch customer order history
$order_stmt = $conn->prepare("SELECT order_id, order_date, total, status FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT 5");
$order_stmt->bind_param("s", $customer_id);
$order_stmt->execute();
$orders = $order_stmt->get_result();

// Fetch order stats
$stats_stmt = $conn->prepare("SELECT COUNT(order_id) as total_orders, SUM(total) as total_spent FROM orders WHERE user_id = ?");
$stats_stmt->bind_param("i", $customer_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

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

<div class="max-w-4xl mx-auto py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Chi Tiết Khách Hàng</h1>
        <a href="#" data-page="customers/list.php" class="inline-flex items-center gap-2 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-bold shadow-sm transition"><i class="fa fa-arrow-left"></i> Quay lại</a>
    </div>

    <!-- Profile Header -->
    <div class="bg-white rounded-2xl shadow-xl p-6 mb-6 flex items-center gap-6">
        <?php
        $avatar_url = $base_url . '/' . ($customer['avatar_image'] ?? 'Photos/default_avatar.png');
        $customer_name = htmlspecialchars($customer['full_name'] ?? $customer['username']);
        if (!($customer['avatar_image'] && file_exists(__DIR__ . '/../../' . $customer['avatar_image']))) {
            $initial = mb_substr($customer_name, 0, 1);
            $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($initial) . "&background=fbcfe8&color=db2777&size=128";
        }
        ?>
        <img src="<?php echo $avatar_url; ?>" alt="Avatar" class="w-24 h-24 rounded-full object-cover border-4 border-pink-200">
        <div>
            <h2 class="text-2xl font-bold text-pink-600"><?php echo $customer_name; ?></h2>
            <p class="text-gray-500">@<?php echo htmlspecialchars($customer['username']); ?></p>
            <div class="mt-2">
                <span class="<?php echo htmlspecialchars($theme_class); ?> px-3 py-1 text-sm rounded-full font-bold"><?php echo htmlspecialchars($rank_name); ?></span>
            </div>
            <div id="deactivated-status-badge" class="mt-2 <?php echo ($customer['deactivated_account'] == 0) ? 'hidden' : ''; ?>">
                <span class="bg-red-100 text-red-800 px-3 py-1 text-sm rounded-full font-bold flex items-center gap-2">
                    <i class="fa fa-exclamation-triangle"></i> Tài khoản đã bị vô hiệu hóa
                </span>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-2xl shadow-lg p-5 flex items-center gap-4">
            <i class="fa fa-star text-3xl text-yellow-400"></i>
            <div>
                <div class="text-gray-500 text-sm">Điểm tích lũy</div>
                <div class="text-2xl font-bold text-gray-800"><?php echo number_format($customer['points'] ?? 0); ?></div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg p-5 flex items-center gap-4">
            <i class="fa fa-receipt text-3xl text-orange-500"></i>
            <div>
                <div class="text-gray-500 text-sm">Tổng đơn hàng</div>
                <div class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_orders'] ?? 0); ?></div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg p-5 flex items-center gap-4">
            <i class="fa fa-coins text-3xl text-green-500"></i>
            <div>
                <div class="text-gray-500 text-sm">Tổng chi tiêu</div>
                <div class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_spent'] ?? 0, 0, ',', '.'); ?>đ</div>
            </div>
        </div>
    </div>

    <!-- Details & Order History -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <!-- Left: Details -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-xl p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Thông tin liên hệ</h3>
            <div class="space-y-3 text-sm">
                <p><i class="fa fa-envelope text-blue-500 w-5"></i> <strong class="text-gray-600">Email:</strong> <?php echo htmlspecialchars($customer['email'] ?? 'N/A'); ?></p>
                <p><i class="fa fa-phone text-orange-500 w-5"></i> <strong class="text-gray-600">SĐT:</strong> <?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></p>
                <p><i class="fa fa-calendar-alt text-green-500 w-5"></i> <strong class="text-gray-600">Ngày tham gia:</strong> <?php echo date("d/m/Y", strtotime($customer['created_at'])); ?></p>
            </div>

            <h3 class="text-xl font-bold text-gray-800 mt-6 mb-4 border-b pb-2">Hành động</h3>
            <div class="space-y-3">
                <button id="deactivate-btn" class="w-full inline-flex items-center justify-center gap-2 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-bold shadow-sm transition <?php echo ($customer['deactivated_account'] == 1) ? 'hidden' : ''; ?>">
                    <i class="fa fa-user-slash"></i> Vô hiệu hóa tài khoản
                </button>
                <button id="reactivate-btn" class="w-full inline-flex items-center justify-center gap-2 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-bold shadow-sm transition <?php echo ($customer['deactivated_account'] == 0) ? 'hidden' : ''; ?>">
                    <i class="fa fa-user-check"></i> Kích hoạt lại tài khoản
                </button>
            </div>
        </div>
        <!-- Right: Order History -->
        <div class="lg:col-span-3 bg-white rounded-2xl shadow-xl p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Đơn hàng gần đây</h3>
            <?php if ($orders->num_rows > 0): ?>
                <div class="space-y-3">
                    <?php while ($order = $orders->fetch_assoc()): ?>
                        <div class="flex justify-between items-center p-3 rounded-lg hover:bg-gray-50 transition">
                            <div>
                                <div class="font-bold text-gray-700">Đơn #<?php echo htmlspecialchars($order['order_id']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo date("d/m/Y H:i", strtotime($order['order_date'])); ?></div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-orange-600"><?php echo number_format($order['total'], 0, ',', '.'); ?>đ</div>
                                <span class="text-xs <?php echo getStatusClass($order['status']); ?> px-2 py-0.5 rounded-full font-semibold"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 mt-4 text-center">Chưa có đơn hàng nào.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const customerId = <?php echo json_encode($customer_id); ?>;
    const deactivateBtn = document.getElementById('deactivate-btn');
    const reactivateBtn = document.getElementById('reactivate-btn');
    const statusBadgeContainer = document.getElementById('deactivated-status-badge');

    function handleAccountStatusChange(action) {
        const isDeactivating = action === 'deactivate';
        const title = isDeactivating ? 'Vô hiệu hóa tài khoản?' : 'Kích hoạt lại tài khoản?';
        const text = isDeactivating 
            ? 'Người dùng sẽ không thể đăng nhập sau khi bị vô hiệu hóa. Bạn có chắc chắn?'
            : 'Người dùng sẽ có thể đăng nhập lại. Bạn có chắc chắn?';
        const confirmButtonText = isDeactivating ? 'Vâng, vô hiệu hóa!' : 'Vâng, kích hoạt lại!';

        // Sử dụng showConfirmationModal từ dashboard.php
        showConfirmationModal(title, text, confirmButtonText, () => {
            const formData = new FormData();
            formData.append('user_id', customerId);
            formData.append('action', action);

            fetch('customers/update_status.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    // Cập nhật UI trực tiếp thay vì tải lại trang
                    if (isDeactivating) {
                        deactivateBtn.classList.add('hidden');
                        reactivateBtn.classList.remove('hidden');
                        statusBadgeContainer.classList.remove('hidden');
                    } else {
                        deactivateBtn.classList.remove('hidden');
                        reactivateBtn.classList.add('hidden');
                        statusBadgeContainer.classList.add('hidden');
                    }
                } else {
                    showToast('Lỗi: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Đã xảy ra lỗi khi thực hiện hành động.', 'error');
            });
        });
    }

    if (deactivateBtn) {
        deactivateBtn.addEventListener('click', function() {
            handleAccountStatusChange('deactivate');
        });
    }

    if (reactivateBtn) {
        reactivateBtn.addEventListener('click', function() {
            handleAccountStatusChange('reactivate');
        });
    }
</script>