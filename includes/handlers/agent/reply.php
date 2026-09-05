<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'reply' => 'Phương thức không hợp lệ.']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$message = trim($data['message'] ?? $_POST['message'] ?? '');

if ($message === '') {
    echo json_encode(['success' => true, 'reply' => 'Bạn cứ hỏi mình về menu, giờ mở cửa, giao hàng hay khuyến mãi nhé.']);
    exit;
}

$q = mb_strtolower($message, 'UTF-8');

function agent_match($haystack, $keywords) {
    foreach ($keywords as $kw) {
        if (mb_stripos($haystack, $kw, 0, 'UTF-8') !== false) {
            return true;
        }
    }
    return false;
}

$reply = 'Mình là TheOldFlavour Agent. Bạn có thể hỏi về thực đơn, giờ mở cửa, địa chỉ, giao hàng, voucher hoặc cách đặt món. Muốn trò chuyện đầy đủ hơn, mở trang Chatbot trên menu nhé.';

if (agent_match($q, ['xin chào', 'hello', 'hi', 'chào'])) {
    $reply = 'Xin chào! Mình là TheOldFlavour Agent. Old Flavour Coffee sẵn sàng giúp bạn chọn món, xem giờ mở cửa hoặc hỗ trợ đặt hàng.';
} elseif (agent_match($q, ['giờ', 'mở cửa', 'đóng cửa', 'làm việc'])) {
    $reply = 'Quán mở cửa từ 7:00 đến 22:00 mỗi ngày. Ghé sớm để thưởng thức cà phê rang mới nhé.';
} elseif (agent_match($q, ['địa chỉ', 'ở đâu', 'map', 'bản đồ', 'đường'])) {
    $reply = 'Bạn tìm chúng tôi tại 123 Đường Trà Sữa, Tây Ninh. Trang Liên hệ có bản đồ Google Maps để chỉ đường.';
} elseif (agent_match($q, ['menu', 'thực đơn', 'món', 'cà phê', 'trà sữa', 'giá'])) {
    $reply = 'Thực đơn có cà phê, kem, nước đặc biệt và trà sữa. Vào mục Thực đơn trên thanh điều hướng để xem giá, size và đặt món.';
} elseif (agent_match($q, ['ship', 'giao', 'phí ship', 'giao hàng'])) {
    $reply = 'Bạn có thể nhận tại quầy hoặc giao tận nơi (phí ship 15.000đ). Đơn từ 100.000đ thường được miễn phí ship theo chương trình khuyến mãi.';
} elseif (agent_match($q, ['voucher', 'khuyến mãi', 'giảm giá', 'combo', 'mã'])) {
    $reply = 'Xem Khuyến mãi để biết combo, giờ vàng và miễn phí ship. Khi thanh toán, nhập mã voucher nếu bạn đã có trong tài khoản.';
} elseif (agent_match($q, ['đặt', 'order', 'giỏ', 'thanh toán', 'vnpay'])) {
    $reply = 'Chọn món trong Thực đơn, thêm vào giỏ, rồi thanh toán tiền mặt khi nhận hoặc VNPay QR. Cần đăng nhập để lưu đơn hàng.';
} elseif (agent_match($q, ['liên hệ', 'sđt', 'điện thoại', 'email', 'zalo'])) {
    $reply = 'Gọi 0909.xxx.xxx, email info@oldfavourcoffee.com, hoặc chat Zalo góc phải màn hình. Trang Liên hệ cũng có form gửi nhanh.';
} elseif (agent_match($q, ['blog', 'câu chuyện'])) {
    $reply = 'Mục Blogs chia sẻ câu chuyện quanh tách cà phê. Bạn có thể đọc bài đã duyệt hoặc viết bài (cần đăng nhập).';
}

echo json_encode(['success' => true, 'reply' => $reply], JSON_UNESCAPED_UNICODE);
