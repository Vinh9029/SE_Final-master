<?php
/**
 * QR Code Generator for VNPay Payments
 * Tạo mã QR cho thanh toán VNPay
 */

class QRGenerator {
    private $size;
    private $data;
    private $error_correction;
    private $margin;

    public function __construct($data, $size = 200, $error_correction = 'M', $margin = 2) {
        $this->data = $data;
        $this->size = $size;
        $this->error_correction = $error_correction;
        $this->margin = $margin;
    }

    /**
     * Generate QR code using Google Charts API
     */
    public function generateGoogleAPI() {
        $url = 'https://chart.googleapis.com/chart?';
        $params = [
            'chs' => $this->size . 'x' . $this->size,
            'cht' => 'qr',
            'chl' => urlencode($this->data),
            'choe' => 'UTF-8',
            'chld' => $this->error_correction . '|' . $this->margin
        ];

        $query = http_build_query($params);
        return $url . $query;
    }

    /**
     * Generate QR code using QRServer API
     */
    public function generateQRServer() {
        $url = 'https://api.qrserver.com/v1/create-qr-code/?';
        $params = [
            'size' => $this->size . 'x' . $this->size,
            'data' => $this->data,
            'ecc' => $this->error_correction,
            'margin' => $this->margin
        ];

        $query = http_build_query($params);
        return $url . $query;
    }

    /**
     * Generate QR code using QRCode Monkey API
     */
    public function generateQRCodeMonkey() {
        $url = 'https://api.qrcode-monkey.com/qr/custom?';
        $params = [
            'size' => $this->size,
            'data' => $this->data,
            'config' => json_encode([
                'body' => 'square',
                'eye' => 'frame3',
                'eyeBall' => 'ball3',
                'erf1' => ['color' => '#000000'],
                'erf2' => ['color' => '#000000'],
                'erf3' => ['color' => '#000000'],
                'brf1' => ['color' => '#000000'],
                'brf2' => ['color' => '#000000'],
                'brf3' => ['color' => '#000000'],
                'bodyColor' => '#000000',
                'bgColor' => '#FFFFFF'
            ])
        ];

        $query = http_build_query($params);
        return $url . $query;
    }

    /**
     * Generate QR code as base64 data URL
     */
    public function generateBase64() {
        $qr_url = $this->generateQRServer();
        $image_data = file_get_contents($qr_url);

        if ($image_data !== false) {
            $base64 = base64_encode($image_data);
            return 'data:image/png;base64,' . $base64;
        }

        return false;
    }

    /**
     * Save QR code to file
     */
    public function saveToFile($filename) {
        $qr_url = $this->generateQRServer();
        $image_data = file_get_contents($qr_url);

        if ($image_data !== false) {
            return file_put_contents($filename, $image_data);
        }

        return false;
    }
}

/**
 * VNPay QR Payment Generator
 */
class VNPayQRGenerator {
    private $order_id;
    private $amount;
    private $description;

    public function __construct($order_id, $amount, $description = '') {
        $this->order_id = $order_id;
        $this->amount = $amount;
        $this->description = $description ?: "Thanh toan don hang #{$order_id}";
    }

    /**
     * Generate VNPay payment URL for QR code
     */
    public function generatePaymentURL() {
        require_once '../../../includes/config/vnpay_config.php';

        $vnp_TmnCode = VNP_TMN_CODE;
        $vnp_HashSecret = VNP_HASH_SECRET;
        $vnp_Url = VNP_URL;
        $vnp_Returnurl = VNP_RETURN_URL;

        $vnp_TxnRef = $this->order_id;
        $vnp_OrderInfo = $this->description;
        $vnp_OrderType = "billpayment";
        $vnp_Amount = $this->amount * 100; // VNPay uses smallest currency unit
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

    /**
     * Generate QR code for VNPay payment
     */
    public function generateQR($size = 256, $format = 'url') {
        $payment_url = $this->generatePaymentURL();

        $qr = new QRGenerator($payment_url, $size);

        switch ($format) {
            case 'base64':
                return $qr->generateBase64();
            case 'file':
                $filename = "qr_vnpay_order_{$this->order_id}.png";
                return $qr->saveToFile($filename) ? $filename : false;
            case 'url':
            default:
                return $qr->generateQRServer();
        }
    }

    /**
     * Generate QR code with custom styling
     */
    public function generateStyledQR($size = 256) {
        $payment_url = $this->generatePaymentURL();

        // Create styled QR using QRCode Monkey
        $qr_url = 'https://api.qrcode-monkey.com/qr/custom?';
        $params = [
            'size' => $size,
            'data' => $payment_url,
            'config' => json_encode([
                'body' => 'square',
                'eye' => 'frame3',
                'eyeBall' => 'ball3',
                'erf1' => ['color' => '#FF6B6B'],
                'erf2' => ['color' => '#FF6B6B'],
                'erf3' => ['color' => '#FF6B6B'],
                'brf1' => ['color' => '#FF6B6B'],
                'brf2' => ['color' => '#FF6B6B'],
                'brf3' => ['color' => '#FF6B6B'],
                'bodyColor' => '#000000',
                'bgColor' => '#FFFFFF'
            ])
        ];

        $query = http_build_query($params);
        return $qr_url . $query;
    }
}

/**
 * Utility functions for QR generation
 */

// Generate QR for specific order
function generateOrderQR($order_id, $amount, $description = '', $size = 256) {
    $qr_generator = new VNPayQRGenerator($order_id, $amount, $description);
    return $qr_generator->generateQR($size);
}

// Generate styled QR for order
function generateStyledOrderQR($order_id, $amount, $description = '', $size = 256) {
    $qr_generator = new VNPayQRGenerator($order_id, $amount, $description);
    return $qr_generator->generateStyledQR($size);
}

// Generate and save QR to file
function saveOrderQR($order_id, $amount, $description = '', $filename = null) {
    if (!$filename) {
        $filename = "qr_order_{$order_id}_" . time() . ".png";
    }

    $qr_generator = new VNPayQRGenerator($order_id, $amount, $description);
    return $qr_generator->generateQR(256, 'file') ? $filename : false;
}

// Get QR as base64 for embedding in HTML
function getOrderQRBase64($order_id, $amount, $description = '') {
    $qr_generator = new VNPayQRGenerator($order_id, $amount, $description);
    return $qr_generator->generateQR(256, 'base64');
}

$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    echo 'Thiếu mã đơn hàng.';
    exit;
}
// Giả lập trả về mã QR
header('Content-Type: image/svg+xml');
echo '<svg width="200" height="200"><rect width="200" height="200" fill="#fbbf24"/><text x="50" y="100" font-size="20">QR-' . htmlspecialchars($order_id) . '</text></svg>';
?>
