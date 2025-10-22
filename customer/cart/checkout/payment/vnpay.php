<?php
session_start();
require_once '../../../includes/config/database.php';
require_once '../../../includes/config/vnpay_config.php';

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

// Tạo URL thanh toán VNPay
$vnpay_url = createVNPayPaymentURL($order_id, $order_info['total_amount']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán VNPay - Đơn hàng #<?php echo $order_id; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .payment-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .btn-primary {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #ee5a24 0%, #ff6b6b 100%);
        }
        .qr-container {
            transition: all 0.3s ease;
        }
        .qr-container:hover {
            transform: scale(1.05);
        }
        .pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
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
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-white mb-4">Thanh toán qua VNPay QR</h1>
                    <p class="text-white text-lg">Quét mã QR để thanh toán đơn hàng #<?php echo $order_id; ?></p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- QR Code Section -->
                    <div class="bg-white rounded-3xl shadow-2xl p-8">
                        <div class="text-center">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6">Mã QR Thanh toán</h2>

                            <!-- QR Code Container -->
                            <div class="qr-container mx-auto mb-6">
                                <div class="w-64 h-64 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto pulse">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=256x256&data=<?php echo urlencode($vnpay_url); ?>"
                                         alt="VNPay QR Code"
                                         class="w-full h-full rounded-2xl">
                                </div>
                            </div>

                            <!-- Amount -->
                            <div class="bg-orange-50 rounded-xl p-4 mb-6">
                                <div class="text-center">
                                    <p class="text-sm text-gray-600 mb-1">Số tiền thanh toán</p>
                                    <p class="text-3xl font-bold text-orange-600"><?php echo number_format($order_info['total_amount']); ?>đ</p>
                                </div>
                            </div>

                            <!-- Payment Instructions -->
                            <div class="bg-blue-50 rounded-xl p-4 mb-6">
                                <h3 class="font-bold text-blue-800 mb-3">Hướng dẫn thanh toán:</h3>
                                <ul class="text-blue-700 space-y-2 text-sm">
                                    <li>1. Mở ứng dụng ngân hàng hoặc ví điện tử</li>
                                    <li>2. Chọn chức năng quét mã QR</li>
                                    <li>3. Quét mã QR ở trên</li>
                                    <li>4. Xác nhận thông tin và thanh toán</li>
                                    <li>5. Chờ xác nhận thanh toán thành công</li>
                                </ul>
                            </div>

                            <!-- Alternative Payment -->
                            <div class="flex flex-col sm:flex-row gap-3">
                                <a href="<?php echo $vnpay_url; ?>" class="btn-primary text-white px-6 py-3 rounded-xl font-bold text-center">
                                    <i class="fas fa-external-link-alt mr-2"></i>
                                    Thanh toán trực tiếp
                                </a>

                                <button onclick="window.print()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-xl font-bold">
                                    <i class="fas fa-print mr-2"></i>
                                    In mã QR
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Order Details -->
                    <div class="bg-white rounded-3xl shadow-2xl p-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                            <i class="fas fa-receipt text-orange-500"></i>
                            Thông tin đơn hàng
                        </h2>

                        <!-- Customer Info -->
                        <div class="mb-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-3">Khách hàng</h3>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <p class="font-medium"><?php echo $customer_info['name']; ?></p>
                                <p class="text-gray-600"><?php echo $customer_info['phone']; ?></p>
                                <p class="text-gray-600"><?php echo $customer_info['email']; ?></p>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="mb-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-3">Sản phẩm</h3>
                            <div class="space-y-3">
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
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-3">Phương thức</h3>
                            <div class="bg-blue-50 rounded-xl p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-qrcode text-blue-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium">VNPay QR</p>
                                        <p class="text-sm text-gray-600">Thanh toán qua mã QR</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="border-t pt-4">
                            <div class="flex justify-between items-center text-xl font-bold">
                                <span>Tổng cộng:</span>
                                <span class="text-orange-600"><?php echo number_format($order_info['total_amount']); ?>đ</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-6 space-y-3">
                            <button onclick="checkPaymentStatus()" class="w-full bg-green-500 hover:bg-green-600 text-white py-3 rounded-xl font-bold">
                                <i class="fas fa-check mr-2"></i>
                                Kiểm tra trạng thái
                            </button>

                            <a href="../orders/" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 rounded-xl font-bold text-center block">
                                <i class="fas fa-list mr-2"></i>
                                Xem đơn hàng
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Payment Status -->
                <div id="payment-status" class="mt-8 hidden">
                    <div class="bg-white rounded-3xl shadow-2xl p-8 text-center">
                        <div id="status-icon" class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center">
                            <i class="fas fa-spinner fa-spin text-2xl text-blue-500"></i>
                        </div>
                        <h3 id="status-title" class="text-xl font-bold mb-2">Đang kiểm tra...</h3>
                        <p id="status-message" class="text-gray-600">Vui lòng đợi trong khi chúng tôi kiểm tra trạng thái thanh toán.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../../includes/footer.php'; ?>

    <script>
        // Check payment status
        function checkPaymentStatus() {
            const statusDiv = document.getElementById('payment-status');
            const statusIcon = document.getElementById('status-icon');
            const statusTitle = document.getElementById('status-title');
            const statusMessage = document.getElementById('status-message');

            statusDiv.classList.remove('hidden');
            statusIcon.innerHTML = '<i class="fas fa-spinner fa-spin text-2xl text-blue-500"></i>';
            statusTitle.textContent = 'Đang kiểm tra...';
            statusMessage.textContent = 'Vui lòng đợi trong khi chúng tôi kiểm tra trạng thái thanh toán.';

            fetch(`check-payment.php?order_id=<?php echo $order_id; ?>`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        statusIcon.innerHTML = '<i class="fas fa-check-circle text-4xl text-green-500"></i>';
                        statusTitle.textContent = 'Thanh toán thành công!';
                        statusMessage.textContent = 'Đơn hàng của bạn đã được thanh toán thành công. Chúng tôi sẽ xử lý đơn hàng ngay lập tức.';

                        // Redirect after 3 seconds
                        setTimeout(() => {
                            window.location.href = '../orders/?success=1';
                        }, 3000);
                    } else {
                        statusIcon.innerHTML = '<i class="fas fa-times-circle text-4xl text-red-500"></i>';
                        statusTitle.textContent = 'Thanh toán thất bại';
                        statusMessage.textContent = data.message || 'Có lỗi xảy ra khi kiểm tra thanh toán. Vui lòng thử lại.';

                        // Hide status after 5 seconds
                        setTimeout(() => {
                            statusDiv.classList.add('hidden');
                        }, 5000);
                    }
                })
                .catch(error => {
                    statusIcon.innerHTML = '<i class="fas fa-exclamation-triangle text-4xl text-yellow-500"></i>';
                    statusTitle.textContent = 'Lỗi kết nối';
                    statusMessage.textContent = 'Không thể kiểm tra trạng thái thanh toán. Vui lòng thử lại sau.';

                    setTimeout(() => {
                        statusDiv.classList.add('hidden');
                    }, 5000);
                });
        }

        // Auto check payment status every 30 seconds
        let checkCount = 0;
        const maxChecks = 20; // Stop after 10 minutes

        function autoCheckPayment() {
            if (checkCount >= maxChecks) return;

            fetch(`check-payment.php?order_id=<?php echo $order_id; ?>`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Payment successful, redirect
                        window.location.href = '../orders/?success=1';
                    }
                })
                .catch(error => {
                    // Silent fail for auto-check
                });

            checkCount++;
        }

        // Start auto-check after 30 seconds
        setTimeout(() => {
            setInterval(autoCheckPayment, 30000);
        }, 30000);

        // Print QR code
        function printQR() {
            const printContent = document.querySelector('.qr-container').innerHTML;
            const originalContent = document.body.innerHTML;

            document.body.innerHTML = `
                <div style="text-align: center; padding: 50px;">
                    <h2>Mã QR Thanh toán</h2>
                    <p>Đơn hàng: #<?php echo $order_id; ?></p>
                    <p>Số tiền: <?php echo number_format($order_info['total_amount']); ?>đ</p>
                    <div style="margin: 50px auto; width: 300px; height: 300px;">
                        ${printContent}
                    </div>
                </div>
            `;

            window.print();
            document.body.innerHTML = originalContent;
            location.reload();
        }
    </script>
</body>
</html>

<?php
// VNPay payment URL generation function
function createVNPayPaymentURL($order_id, $amount) {
    $vnp_TmnCode = VNP_TMN_CODE;
    $vnp_HashSecret = VNP_HASH_SECRET;
    $vnp_Url = VNP_URL;
    $vnp_Returnurl = VNP_RETURN_URL;

    $vnp_TxnRef = $order_id;
    $vnp_OrderInfo = "Thanh toan don hang #" . $order_id;
    $vnp_OrderType = "billpayment";
    $vnp_Amount = $amount * 100; // VNPay uses smallest currency unit
    $vnp_Locale = "vn";
    $vnp_BankCode = "";
    $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

    $inputData = array(
        "vnp_Version" => "2.1.0",
        "vnp_TmnCode" => $vnp_TmnCode,
        "vnp_Amount" => $vnp_Amount,
        "vnp_Command" => "pay",
        "vnp_CreateDate" => date('YmdHis'),
        "vnp_CurrCode" => "VND",
        "vnp_IpAddr" => $vnp_IpAddr,
        "vnp_Locale" => $vnp_Locale,
        "vnp_OrderInfo" => $vnp_OrderInfo,
        "vnp_OrderType" => $vnp_OrderType,
        "vnp_ReturnUrl" => $vnp_Returnurl,
        "vnp_TxnRef" => $vnp_TxnRef,
    );

    if (isset($vnp_BankCode) && $vnp_BankCode != "") {
        $inputData['vnp_BankCode'] = $vnp_BankCode;
    }

    ksort($inputData);
    $query = "";
    $i = 0;
    $hashdata = "";
    foreach ($inputData as $key => $value) {
        if ($i == 1) {
            $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashdata .= urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
        $query .= urlencode($key) . "=" . urlencode($value) . '&';
    }

    $vnp_Url = $vnp_Url . "?" . $query;
    if (isset($vnp_HashSecret)) {
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
    }

    return $vnp_Url;
}

include_once __DIR__ . '/../../../../database/db_connection.php';
$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    echo 'Thiếu mã đơn hàng.';
    exit;
}
// Giả lập tạo mã QR và xác nhận thanh toán
$sql = 'UPDATE orders SET status = "completed" WHERE order_id = ?';
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $order_id);
$stmt->execute();
echo 'Thanh toán VNPay thành công! Đơn hàng đã hoàn tất.';
?>
