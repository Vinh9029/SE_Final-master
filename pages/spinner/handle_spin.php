<?php
session_start();
include_once __DIR__ . '/../../database/db_connection.php';
include_once __DIR__ . '/../../config.php'; // Cần base_url cho đường dẫn thông báo
include_once __DIR__ . '/../../includes/handlers/notification/createNotification.php'; // Hàm tạo thông báo

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để tham gia vòng quay!']);
    exit;
}

$user_id = $_SESSION['user_id'];

// --- Kiểm tra lượt quay trong ngày ---
$today_start = date('Y-m-d 00:00:00');
$today_end = date('Y-m-d 23:59:59');

$stmt = $conn->prepare("SELECT COUNT(*) as spin_count FROM spin_history WHERE user_id = ? AND created_at BETWEEN ? AND ?");
$stmt->bind_param('iss', $user_id, $today_start, $today_end);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($result['spin_count'] > 0) {
    echo json_encode(['success' => false, 'message' => 'Bạn đã quay rồi, hãy quay lại vào ngày mai nhé!']);
    exit;
}

// --- Lấy thông tin giải thưởng từ request ---
$data = json_decode(file_get_contents('php://input'), true);
$prize_index = $data['prize_index'] ?? -1;

// --- Định nghĩa các giải thưởng ---
// Index phải khớp với index trên frontend
$prizes = [
    0 => ['name' => 'Giảm 10%', 'type' => 'discount', 'value' => 10, 'min_order' => 50000],
    1 => ['name' => 'Chúc bạn\nmay mắn', 'type' => 'no_prize', 'value' => 0, 'min_order' => 0],
    2 => ['name' => 'Giảm 20%', 'type' => 'discount', 'value' => 20, 'min_order' => 150000],
    3 => ['name' => 'Freeship', 'type' => 'freeship', 'value' => 0, 'min_order' => 100000],
    4 => ['name' => 'Giảm 15%', 'type' => 'discount', 'value' => 15, 'min_order' => 100000],
    5 => ['name' => 'Thêm lượt', 'type' => 'extra_spin', 'value' => -1, 'min_order' => 0],
    6 => ['name' => 'Giảm 30%', 'type' => 'discount', 'value' => 30, 'min_order' => 200000],
    7 => ['name' => 'Mua 1 Tặng 1', 'type' => 'buy_one_get_one', 'value' => 0, 'min_order' => 0],
];

if (!isset($prizes[$prize_index])) {
    echo json_encode(['success' => false, 'message' => 'Giải thưởng không hợp lệ.']);
    exit;
}

$won_prize = $prizes[$prize_index];
$prize_name = $won_prize['name']; // Tên giải thưởng hiển thị
$prize_type = $won_prize['type']; // Loại giải thưởng (discount, freeship, extra_spin, no_prize, buy_one_get_one)
$prize_value = $won_prize['value']; // Giá trị giảm giá hoặc mã đặc biệt
$prize_min_order = $won_prize['min_order']; // Giá trị đơn hàng tối thiểu

$conn->begin_transaction();

try {
    // 1. Lưu lịch sử quay
    $stmt = $conn->prepare("INSERT INTO spin_history (user_id, prize) VALUES (?, ?)");
    $stmt->bind_param('is', $user_id, $prize_name);
    $stmt->execute();
    $spin_id = $conn->insert_id;

    $notification_url = $base_url . '/customer/account.php?page=vouchers'; // URL mặc định cho voucher
    $notification_related_id = $spin_id; // ID liên quan mặc định là spin_id
    $message_to_user = '';
    $notification_title = '';
    $notification_body = '';
    $retry_spin = false;

    switch ($prize_type) {
        case 'discount':
        case 'freeship':
        case 'buy_one_get_one':
            $voucher_code = strtoupper(substr($prize_type, 0, 3)) . strtoupper(substr(uniqid(), 7, 6)); // Ví dụ: DIS..., FRE..., BUY...
            $title = 'Vòng Quay May Mắn - ' . $prize_name;
            $expires_at = date('Y-m-d H:i:s', strtotime('+7 days')); // Hạn 7 ngày

            $stmt = $conn->prepare(
                "INSERT INTO vouchers (user_id, code, discount_value, discount_type, title, min_order_value, status, expires_at, spin_id)
                 VALUES (?, ?, ?, 'percent', ?, ?, 'active', ?, ?)"
            );
            $stmt->bind_param('isdsisi', $user_id, $voucher_code, $prize_value, $title, $prize_min_order, $expires_at, $spin_id);
            $stmt->execute();
            $voucher_id = $conn->insert_id;
            $notification_related_id = $voucher_id; // Sử dụng voucher_id nếu voucher được tạo

            $notification_title = 'Bạn đã trúng voucher!';
            $notification_body = "Chúc mừng bạn đã trúng voucher {$prize_name}! Mã voucher của bạn là: {$voucher_code}. Voucher có hạn trong 7 ngày. Hãy kiểm tra mục 'Voucher của tôi' trong tài khoản.";
            $message_to_user = $notification_body;

            create_notification(
                $conn,
                $user_id,
                'spin_voucher_won',
                $notification_title,
                $notification_body,
                $notification_related_id,
                $notification_url
            );
            break;

        case 'extra_spin':
            // Xóa lịch sử quay của lượt này để cho phép quay lại
            $stmt = $conn->prepare("DELETE FROM spin_history WHERE spin_id = ?");
            $stmt->bind_param('i', $spin_id);
            $stmt->execute();
            $retry_spin = true;

            $notification_title = 'Bạn đã trúng thêm lượt quay!';
            $notification_body = 'Bạn đã trúng "Thêm lượt quay"! Hãy thử vận may lần nữa nào!';
            $message_to_user = $notification_body;
            $notification_url = $base_url . '/pages/spinner/spinner.php'; // Link về trang vòng quay

            create_notification(
                $conn,
                $user_id,
                'spin_extra_chance',
                $notification_title,
                $notification_body,
                $notification_related_id,
                $notification_url
            );
            break;

        case 'no_prize':
        default:
            $notification_title = 'Kết quả Vòng Quay May Mắn';
            $notification_body = "Kết quả: {$prize_name}. Cảm ơn bạn đã tham gia!";
            $message_to_user = $notification_body;
            $notification_url = $base_url . '/pages/spinner/spinner.php'; // Link về trang vòng quay

            create_notification(
                $conn,
                $user_id,
                'spin_no_prize',
                $notification_title,
                $notification_body,
                $notification_related_id,
                $notification_url
            );
            break;
    }

    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => $message_to_user,
        'retry' => $retry_spin
    ]);
} catch (Exception $e) {
    $conn->rollback();
    error_log("Spin Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Đã có lỗi xảy ra, vui lòng thử lại sau.']);
}

$conn->close();