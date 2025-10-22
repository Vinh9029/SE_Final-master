<?php
session_start();
require_once '../../../includes/config/database.php';

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
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
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

// Lấy thông tin khách hàng
$customer_info = json_decode($order_info['customer_info'], true);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán tiền mặt - Đơn hàng #<?php echo $order_id; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .payment-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .btn-success {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
        }
        .btn-success:hover {
            background: linear-gradient(135deg, #45a049 0%, #4CAF50 100%);
        }
        .order-card {
            transition: all 0.3s ease;
        }
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <?php include '../../includes/header.php'; ?>

    <!-- Payment Section -->
    <div class="payment-container min-h-screen py-12">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <!-- Success Message -->
                <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-2xl mb-8">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-check-circle text-2xl"></i>
                        <div>
                            <h2 class="font-bold text-lg">Đặt hàng thành công!</h2>
                            <p>Đơn hàng của bạn đã được tiếp nhận và đang chờ xác nhận.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Order Details -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
                            <!-- Header -->
                            <div class="bg-gradient-to-r from-green-500 to-blue-500 p-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <i class="fas fa-receipt text-white text-2xl"></i>
                                        <h1 class="text-2xl font-bold text-white">Đơn hàng #<?php echo $order_id; ?></h1>
                                    </div>
                                    <div class="text-white">
                                        <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm">
                                            <i class="fas fa-clock mr-1"></i>Chờ xác nhận
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6">
                                <!-- Customer Info -->
                                <div class="mb-6">
                                    <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center gap-2">
                                        <i class="fas fa-user text-green-500"></i>
                                        Thông tin khách hàng
                                    </h3>
                                    <div class="bg-gray-50 rounded-xl p-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-sm text-gray-600">Họ và tên</p>
                                                <p class="font-medium"><?php echo $customer_info['name']; ?></p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Số điện thoại</p>
                                                <p class="font-medium"><?php echo $customer_info['phone']; ?></p>
                                            </div>
                                            <div class="md:col-span-2">
                                                <p class="text-sm text-gray-600">Email</p>
                                                <p class="font-medium"><?php echo $customer_info['email']; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Shipping Info -->
                                <div class="mb-6">
                                    <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center gap-2">
                                        <i class="fas fa-truck text-blue-500"></i>
                                        Địa chỉ giao hàng
                                    </h3>
                                    <div class="bg-blue-50 rounded-xl p-4">
                                        <p class="font-medium"><?php echo $order_info['shipping_address']; ?></p>
                                        <?php if (!empty($customer_info['notes'])): ?>
                                            <div class="mt-3 pt-3 border-t border-blue-200">
                                                <p class="text-sm text-gray-600">Ghi chú:</p>
                                                <p class="text-sm"><?php echo $customer_info['notes']; ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Payment Info -->
                                <div class="mb-6">
                                    <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center gap-2">
                                        <i class="fas fa-money-bill-wave text-orange-500"></i>
                                        Phương thức thanh toán
                                    </h3>
                                    <div class="bg-orange-50 rounded-xl p-4">
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
                                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                                    <h4 class="font-bold text-yellow-800 mb-2">Các bước tiếp theo:</h4>
                                    <ul class="text-yellow-700 space-y-1 text-sm">
                                        <li>• Chúng tôi sẽ gọi điện xác nhận đơn hàng trong 30 phút</li>
                                        <li>• Thời gian giao hàng: 1-3 ngày làm việc</li>
                                        <li>• Vui lòng chuẩn bị tiền mặt khi nhận hàng</li>
                                        <li>• Kiểm tra kỹ sản phẩm trước khi thanh toán</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-3xl shadow-2xl p-6 sticky top-6">
                            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                                <i class="fas fa-shopping-bag text-orange-500"></i>
                                Tóm tắt đơn hàng
                            </h2>

                            <!-- Order Items -->
                            <div class="space-y-3 mb-6">
                                <?php
                                try {
                                    $stmt = $conn->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                                    $stmt->bind_param("i", $order_id);
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    while ($item = $result->fetch_assoc()):
                                ?>
                                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                                        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                                            <i class="fas fa-box text-gray-400"></i>
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-medium text-sm"><?php echo $item['name']; ?></h4>
                                            <p class="text-xs text-gray-600">SL: <?php echo $item['quantity']; ?></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold text-sm"><?php echo number_format($item['price'] * $item['quantity']); ?>đ</p>
                                        </div>
                                    </div>
                                <?php endwhile; } catch (Exception $e) { ?>
                                    <p class="text-gray-500 text-center py-4">Không thể tải thông tin sản phẩm</p>
                                <?php } ?>
                            </div>

                            <!-- Order Total -->
                            <div class="border-t pt-4">
                                <div class="flex justify-between items-center text-lg font-bold">
                                    <span>Tổng cộng:</span>
                                    <span class="text-orange-600"><?php echo number_format($order_info['total_amount']); ?>đ</span>
                                </div>
                                <div class="flex justify-between items-center text-sm text-gray-600 mt-1">
                                    <span>Phí vận chuyển:</span>
                                    <span>Miễn phí</span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="mt-6 space-y-3">
                                <a href="../orders/" class="w-full btn-success text-white py-3 rounded-xl font-bold text-center block">
                                    <i class="fas fa-list mr-2"></i>
                                    Xem đơn hàng
                                </a>

                                <a href="../../products.php" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 rounded-xl font-bold text-center block transition-colors">
                                    <i class="fas fa-store mr-2"></i>
                                    Tiếp tục mua sắm
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../../includes/footer.php'; ?>

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
                        // Update status display
                        const statusElement = document.querySelector('.fa-clock').parentElement;
                        if (data.status === 'confirmed') {
                            statusElement.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Đã xác nhận';
                            statusElement.className = 'bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm';
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

<?php
session_start();
include_once __DIR__ . '/../../../../database/db_connection.php';
$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    echo 'Thiếu mã đơn hàng.';
    exit;
}
$sql = 'UPDATE orders SET status = "processing" WHERE order_id = ?';
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $order_id);
$stmt->execute();
echo 'Đơn hàng đã được xác nhận. Cảm ơn bạn!';
