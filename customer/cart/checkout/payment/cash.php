<?php
session_start();
include_once __DIR__ . '/../../../../database/db_connection.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../account.php');
    exit();
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    header('Location: ../');
    exit();
}

// Lấy thông tin đơn hàng
$order_info = [];
try {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
    $stmt->execute();
    $order_info = $stmt->get_result()->fetch_assoc();

    if (!$order_info) {
        header('Location: ../');
        exit();
    }
} catch (Exception $e) {
    header('Location: ../');
    exit();
}

// Lấy thông tin khách hàng từ bảng users
$customer_stmt = $conn->prepare("SELECT full_name, email, phone FROM users WHERE user_id = ?");
$customer_stmt->bind_param("i", $_SESSION['user_id']);
$customer_stmt->execute();
$customer_info = $customer_stmt->get_result()->fetch_assoc();

// Lấy ghi chú từ đơn hàng (nếu có)
// Giả sử bạn có một cột `notes` trong bảng `orders`
$notes = $order_info['notes'] ?? '';

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán tiền mặt - Đơn hàng #<?php echo $order_id; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #E6D3B1;
        }
        h1, h2, h3 {
            font-family: 'Playfair Display', serif;
        }
        .cta-button {
            background: linear-gradient(135deg, #4B2E05 0%, #C4A35A 100%);
        }
        .cta-button:hover {
            background: linear-gradient(135deg, #C4A35A 0%, #4B2E05 100%);
        }
        .text-brown {
            color: #4B2E05;
        }
    </style>
</head>
<body class="bg-beige">
    <!-- Header -->
    <?php include_once __DIR__ . '/../../../../includes/header.php'; ?>

    <!-- Payment Section -->
    <main class="min-h-screen py-12">
        <div class="max-w-5xl mx-auto px-4">
                <!-- Success Message -->
                <div class="bg-yellow-50 border-l-4 border-yellow-500 text-yellow-800 px-6 py-4 rounded-r-lg mb-8 shadow-lg">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-check-circle text-2xl text-yellow-600"></i>
                        <div>
                            <h2 class="font-bold text-lg text-brown">Đặt hàng thành công!</h2>
                            <p>Cảm ơn bạn đã tin tưởng Old Flavour. Đơn hàng của bạn đã được tiếp nhận và đang chờ xử lý.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Order Details -->
                    <div class="lg:col-span-2 bg-white rounded-2xl shadow-xl p-8">
                            <!-- Header -->
                            <div class="flex items-center justify-between pb-4 border-b mb-6">
                                <h1 class="text-3xl font-bold text-brown flex items-center gap-3">
                                    <i class="fas fa-receipt text-yellow-600"></i>
                                    Chi tiết Đơn hàng #<?php echo $order_id; ?>
                                </h1>
                                <div id="order-status-badge" class="text-yellow-700 bg-yellow-100 px-3 py-1 rounded-full text-sm font-semibold">
                                    <i class="fas fa-clock mr-1"></i>
                                    Chờ xử lý
                                </div>
                            </div>

                                <!-- Customer Info -->
                                <div class="mb-6">
                                    <h3 class="text-xl font-bold text-brown mb-3 flex items-center gap-2">
                                        <i class="fas fa-user text-yellow-700"></i>
                                        Thông tin khách hàng
                                    </h3>
                                    <div class="bg-yellow-50 rounded-xl p-4 text-gray-700">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-sm text-gray-600">Họ và tên</p>
                                                <p class="font-semibold text-brown"><?php echo htmlspecialchars($customer_info['full_name']); ?></p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Số điện thoại</p>
                                                <p class="font-semibold text-brown"><?php echo htmlspecialchars($customer_info['phone']); ?></p>
                                            </div>
                                            <div class="md:col-span-2">
                                                <p class="text-sm text-gray-600">Email</p>
                                                <p class="font-semibold text-brown"><?php echo htmlspecialchars($customer_info['email']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Shipping Info -->
                                <div class="mb-8">
                                    <h3 class="text-xl font-bold text-brown mb-3 flex items-center gap-2">
                                        <i class="fas fa-truck text-yellow-700"></i>
                                        Địa chỉ giao hàng
                                    </h3>
                                    <div class="bg-yellow-50 rounded-xl p-4 text-gray-700">
                                        <p class="font-semibold text-brown"><?php echo htmlspecialchars($order_info['address'] ?? 'Nhận tại quầy'); ?></p>
                                        <?php if (!empty($notes)): ?>
                                            <div class="mt-3 pt-3 border-t border-yellow-200">
                                                <p class="text-sm text-gray-600">Ghi chú:</p>
                                                <p class="text-sm italic">"<?php echo htmlspecialchars($notes); ?>"</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Payment Info -->
                                <div class="mb-8">
                                    <h3 class="text-xl font-bold text-brown mb-3 flex items-center gap-2">
                                        <i class="fas fa-money-bill-wave text-yellow-700"></i>
                                        Phương thức thanh toán
                                    </h3>
                                    <div class="bg-yellow-50 rounded-xl p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-money-bill-alt text-green-600"></i>
                                            </div>
                                            <div>
                                                <p class="font-medium">Tiền mặt</p>
                                                <p class="text-sm text-gray-600">Thanh toán khi nhận hàng</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Next Steps -->
                                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                                    <h4 class="font-bold text-amber-800 mb-2">Các bước tiếp theo:</h4>
                                    <ul class="text-amber-700 space-y-1 text-sm list-disc list-inside">
                                        <li>• Chúng tôi sẽ gọi điện xác nhận đơn hàng trong 30 phút</li>
                                        <li>• Thời gian giao hàng: 1-3 ngày làm việc</li>
                                        <li>• Vui lòng chuẩn bị tiền mặt khi nhận hàng</li>
                                        <li>• Kiểm tra kỹ sản phẩm trước khi thanh toán</li>
                                    </ul>
                                </div>
                        </div>

                    <!-- Order Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-2xl shadow-xl p-6 sticky top-24">
                            <h2 class="text-2xl font-bold text-brown mb-6 flex items-center gap-2">
                                <i class="fas fa-shopping-bag text-yellow-600"></i>
                                Tóm tắt đơn hàng
                            </h2>

                            <!-- Order Items -->
                            <div class="space-y-4 mb-6 max-h-80 overflow-y-auto pr-2">
                                <?php
                                try {
                                    $stmt = $conn->prepare(
                                        "SELECT oi.*, p.name, p.image, ps.size_name 
                                         FROM order_items oi 
                                         JOIN products p ON oi.product_id = p.product_id 
                                         LEFT JOIN product_sizes ps ON oi.size_id = ps.size_id
                                         WHERE oi.order_id = ?"
                                    );
                                    $stmt->bind_param("i", $order_id);
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    while ($item = $result->fetch_assoc()):
                                ?>
                                    <div class="flex items-center gap-3 p-3 bg-yellow-50 rounded-xl">
                                        <img src="<?php echo $base_url . '/' . ($item['image'] ?: 'Photos/placeholder.png'); ?>" class="w-12 h-12 object-cover rounded-lg" alt="<?php echo htmlspecialchars($item['name']); ?>" />
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-sm text-brown leading-tight"><?php echo htmlspecialchars($item['name']); ?></h4>
                                            <?php if (!empty($item['size_name'])): ?><p class="text-xs text-gray-500 font-semibold">Size: <?php echo htmlspecialchars($item['size_name']); ?></p><?php endif; ?>
                                            <p class="text-xs text-gray-600">SL: <?php echo htmlspecialchars($item['quantity']); ?></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold text-sm text-brown"><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>đ</p>
                                        </div>
                                    </div>
                                <?php endwhile; } catch (Exception $e) { ?>
                                    <p class="text-gray-500 text-center py-4">Không thể tải thông tin sản phẩm</p>
                                <?php } ?>
                            </div>

                            <!-- Order Total -->
                            <div class="border-t-2 border-dashed border-yellow-200 pt-4 space-y-2">
                                <div class="flex justify-between items-center text-gray-600">
                                    <span>Tạm tính:</span>
                                    <span class="font-semibold"><?php echo number_format($order_info['total'] + ($order_info['discount_amount'] ?? 0), 0, ',', '.'); ?>đ</span>
                                </div>
                                <?php if ($order_info['voucher_code']): ?>
                                    <div class="flex justify-between items-center text-green-600 font-semibold">
                                        <span>Giảm giá (<?php echo htmlspecialchars($order_info['voucher_code']); ?>):</span>
                                        <span class="font-bold">-<?php echo number_format($order_info['discount_amount'], 0, ',', '.'); ?>đ</span>
                                    </div>
                                <?php endif; ?>
                                <div class="flex justify-between items-center text-sm text-gray-600 mt-1">
                                    <span>Phí vận chuyển:</span>
                                    <span class="font-semibold">Miễn phí</span>
                                </div>
                                <div class="border-t pt-3 mt-3 flex justify-between items-center text-xl font-bold">
                                    <span class="text-brown">Thành tiền:</span>
                                    <span class="text-yellow-700"><?php echo number_format($order_info['total'], 0, ',', '.'); ?>đ</span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="mt-6 space-y-3">
                                <a href="../../orders.php" class="w-full cta-button text-white py-3 rounded-full font-bold text-center block shadow-lg hover:shadow-xl transition-all">
                                    <i class="fas fa-list mr-2"></i>
                                    Xem đơn hàng
                                </a>

                                <a href="/SE_Final-master/index.php" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 rounded-full font-bold text-center block transition-colors">
                                    <i class="fas fa-store mr-2"></i>
                                    Tiếp tục mua sắm
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include_once __DIR__ . '/../../../../includes/footer.php'; ?>

    <script>
        // Auto refresh order status every 30 seconds
        let checkCount = 0;
        const maxChecks = 10; // Stop after 5 minutes

        function checkOrderStatus() {
            if (checkCount >= maxChecks) return;

            fetch(`check-status.php?order_id=<?php echo $order_id; ?>`)
                .then(response => response.json())
                .then(data => {
                    if (data.status !== 'pending') {
                        const statusBadge = document.getElementById('order-status-badge');
                        if (data.status === 'processing') {
                            statusBadge.innerHTML = '<i class="fas fa-sync-alt fa-spin mr-1"></i>Đang xử lý';
                            statusBadge.className = 'text-blue-700 bg-blue-100 px-3 py-1 rounded-full text-sm font-semibold';
                        } else if (data.status === 'completed') {
                            statusBadge.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Đã giao';
                            statusBadge.className = 'text-green-700 bg-green-100 px-3 py-1 rounded-full text-sm font-semibold';
                        } else if (data.status === 'cancelled') {
                            statusBadge.innerHTML = '<i class="fas fa-times-circle mr-1"></i>Đã hủy';
                            statusBadge.className = 'text-red-700 bg-red-100 px-3 py-1 rounded-full text-sm font-semibold';
                        }
                    }
                })
                .catch(error => {
                    console.log('Status check failed:', error);
                });

            checkCount++;
        }

        // Check status every 30 seconds
        setInterval(checkOrderStatus, 30000);

        // Initial check after 10 seconds
        setTimeout(checkOrderStatus, 10000);
    </script>
</body>
</html>
