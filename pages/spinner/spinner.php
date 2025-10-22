<?php
// filepath: c:/xampp/htdocs/SE_Final-Cart-Checkout/pages/spinner/spinner.php
// Vòng quay may mắn - front-end + back-end xử lý
// session_start();
include_once __DIR__ . '/../../config.php';
include_once __DIR__ . '/../../database/db_connection.php';

// Xử lý quay vòng quay
$prizes = [
    ["label" => "🎟️ Giảm giá 20%", "desc" => "Giảm 20% cho đơn hàng từ 100k", "type" => "discount", "rate" => 40],
    ["label" => "🚚 Miễn phí vận chuyển", "desc" => "Miễn phí vận chuyển cho đơn > 150k", "type" => "shipping", "rate" => 10],
    ["label" => "🎉 Voucher 50k", "desc" => "Giảm trực tiếp 50k vào thanh toán", "type" => "voucher", "value" => 50000, "rate" => 7],
    ["label" => "🎉 Voucher 100k", "desc" => "Giảm trực tiếp 100k vào thanh toán", "type" => "voucher", "value" => 100000, "rate" => 5],
    ["label" => "🎉 Voucher 200k", "desc" => "Giảm trực tiếp 200k vào thanh toán", "type" => "voucher", "value" => 200000, "rate" => 3],
    ["label" => "🔁 Lượt quay thêm 1 lần", "desc" => "Bạn được quay thêm 1 lần miễn phí!", "type" => "extra_spin", "rate" => 10],
    ["label" => "🎁 Thưởng điểm tích luỹ", "desc" => "Tặng 100 điểm tích luỹ", "type" => "loyalty", "value" => 100, "rate" => 5],
    ["label" => "😅 Chúc bạn may mắn lần sau", "desc" => "Không trúng gì, thử lại lần sau!", "type" => "none", "rate" => 15]
];

// Hàm chọn phần thưởng ngẫu nhiên theo tỉ lệ
function getRandomPrize($prizes) {
    $total = array_sum(array_column($prizes, 'rate'));
    $rand = mt_rand(1, $total);
    $acc = 0;
    foreach ($prizes as $prize) {
        $acc += $prize['rate'];
        if ($rand <= $acc) return $prize;
    }
    return $prizes[0];
}

// Xử lý khi người dùng quay
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_SESSION['customer_id'] ?? null;
    $prize = getRandomPrize($prizes);
    $can_spin = true;
    $extra_spin = false;
    if ($customer_id) {
        // Kiểm tra số lượt quay đã dùng
        $sql_check = "SELECT COUNT(*) as cnt FROM spin_history WHERE customer_id = ? AND extra_spin_used = 0";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute([$customer_id]);
        $row = $stmt_check->fetch();
        $spins = $row['cnt'] ?? 0;
        // Nếu đã quay rồi và không phải lượt quay thêm thì không cho quay nữa
        if ($spins >= 1) {
            // Kiểm tra nếu có lượt quay thêm chưa dùng
            $sql_extra = "SELECT COUNT(*) as cnt FROM spin_history WHERE customer_id = ? AND prize = '🔁 Lượt quay thêm 1 lần' AND extra_spin_used = 0";
            $stmt_extra = $conn->prepare($sql_extra);
            $stmt_extra->execute([$customer_id]);
            $row_extra = $stmt_extra->fetch();
            if (($row_extra['cnt'] ?? 0) > 0) {
                $extra_spin = true;
            } else {
                $can_spin = false;
            }
        }
    }
    if (!$customer_id) {
        // Demo: không giới hạn lượt quay, không ghi nhận phần thưởng
        header('Content-Type: application/json');
        echo json_encode($prize);
        exit;
    }
    if ($can_spin || $extra_spin) {
        // Lưu lịch sử quay
        $sql = "INSERT INTO spin_history (customer_id, prize, created_at, extra_spin_used) VALUES (?, ?, NOW(), ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$customer_id, $prize['label'], $extra_spin ? 1 : 0]);
        // Nếu trúng voucher hoặc giảm giá, tạo voucher
        if ($prize['type'] === 'voucher' || $prize['type'] === 'discount' || $prize['type'] === 'shipping') {
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
            $value = $prize['value'] ?? null;
            $desc = $prize['desc'];
            $sql2 = "INSERT INTO vouchers (customer_id, code, type, value, description, created_at, expired_at, spin_id) VALUES (?, ?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), LAST_INSERT_ID())";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->execute([$customer_id, $code, $prize['type'], $value, $desc]);
            $prize['voucher_code'] = $code;
        }
        // Nếu trúng điểm tích luỹ
        if ($prize['type'] === 'loyalty') {
            $sql3 = "UPDATE customers SET loyalty_points = loyalty_points + ? WHERE id = ?";
            $stmt3 = $conn->prepare($sql3);
            $stmt3->execute([$prize['value'], $customer_id]);
        }
        // Nếu quay xong lượt quay thêm thì đánh dấu lượt quay thêm đã dùng
        if ($extra_spin) {
            $sql4 = "UPDATE spin_history SET extra_spin_used = 1 WHERE customer_id = ? AND prize = '🔁 Lượt quay thêm 1 lần' AND extra_spin_used = 0 LIMIT 1";
            $stmt4 = $conn->prepare($sql4);
            $stmt4->execute([$customer_id]);
        }
        header('Content-Type: application/json');
        echo json_encode($prize);
        exit;
    } else {
        header('Content-Type: application/json');
        echo json_encode(["label" => "", "desc" => "Bạn đã hết lượt quay!", "type" => "none"]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Vòng quay may mắn</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #fff5f8; font-family: 'Montserrat', sans-serif; }
        .wheel-container { position: relative; width: 450px; height: 450px; margin: 0 auto; }
        .wheel-canvas { width: 100%; height: 100%; border-radius: 50%; box-shadow: 0 8px 32px #f9a8d4; }
        .spin-btn { background: linear-gradient(90deg, #f472b6, #fbbf24); color: #fff; font-size: 1.5rem; font-weight: bold; border: none; border-radius: 50px; padding: 18px 48px; box-shadow: 0 4px 16px #f472b6; cursor: pointer; transition: 0.2s; margin-top: 32px; }
        .spin-btn:hover { background: linear-gradient(90deg, #fbbf24, #f472b6); box-shadow: 0 8px 32px #f472b6; }
        .arrow { position: absolute; top: -32px; left: 50%; transform: translateX(-50%); font-size: 3rem; color: #f472b6; text-shadow: 0 2px 8px #fff; }
        .result-message { margin-top: 32px; font-size: 1.3rem; font-weight: bold; color: #f472b6; text-align: center; }
    </style>
</head>
<body>
    <div class="wheel-container" style="margin-top:60px; margin-bottom:60px;">
        <div class="arrow"><i class="fa fa-location-arrow"></i></div>
        <canvas id="wheel" class="wheel-canvas" width="400" height="400"></canvas>
        <div style="width:100%;display:flex;justify-content:center;align-items:center;">
            <button id="spinBtn" class="spin-btn">Quay ngay</button>
        </div>
        <div id="result" class="result-message"></div>
    </div>
    <script>
        // Vẽ vòng quay
        const prizes = <?php echo json_encode($prizes); ?>;
        const colors = ["#fbbf24", "#34d399", "#f472b6", "#60a5fa", "#f87171", "#a78bfa", "#f59e42", "#d1d5db"];
        const wheel = document.getElementById('wheel');
        const ctx = wheel.getContext('2d');
        const num = prizes.length;
        const radius = 200;
        function drawWheel(angle = 0) {
            ctx.clearRect(0, 0, 400, 400);
            for (let i = 0; i < num; i++) {
                ctx.save();
                ctx.beginPath();
                ctx.moveTo(200, 200);
                ctx.arc(200, 200, radius, (i * 2 * Math.PI / num) + angle, ((i + 1) * 2 * Math.PI / num) + angle);
                ctx.closePath();
                ctx.fillStyle = colors[i % colors.length];
                ctx.fill();
                ctx.restore();
                // Vẽ text
                ctx.save();
                ctx.translate(200, 200);
                ctx.rotate((i + 0.5) * 2 * Math.PI / num + angle);
                ctx.textAlign = "center";
                ctx.font = "bold 20px Montserrat";
                ctx.fillStyle = "#fff";
                ctx.shadowColor = "#000";
                ctx.shadowBlur = 4;
                ctx.fillText(prizes[i].label, 120, 10);
                ctx.restore();
            }
        }
        drawWheel();
        // Xử lý quay
        let spinning = false;
        document.getElementById('spinBtn').onclick = function() {
            if (spinning) return;
            spinning = true;
            let spinAngle = Math.random() * 2 * Math.PI + 6 * 2 * Math.PI; // Quay nhiều vòng
            let current = 0;
            let steps = 120;
            let prizeIndex = null;
            function animate() {
                current += (spinAngle - current) / 12;
                drawWheel(current);
                if (Math.abs(current - spinAngle) > 0.01) {
                    requestAnimationFrame(animate);
                } else {
                    // Xác định phần thưởng
                    let finalAngle = (spinAngle % (2 * Math.PI));
                    prizeIndex = num - Math.floor(finalAngle / (2 * Math.PI / num)) - 1;
                    if (prizeIndex < 0) prizeIndex += num;
                    // Gửi request lấy phần thưởng
                    fetch('spinner.php', { method: 'POST' })
                        .then(res => res.json())
                        .then(data => {
                            let msg = `<span>${data.label}</span><br><span style='font-size:1rem;color:#333;'>${data.desc}</span>`;
                            if (data.voucher_code) msg += `<br><span style='color:#34d399'>Mã voucher: <b>${data.voucher_code}</b></span>`;
                            document.getElementById('result').innerHTML = msg;
                            // Gửi message ra ngoài cho parent
                            window.parent.postMessage({ type: 'spinner-result', prize: data.label }, '*');
                            spinning = false;
                        });
                }
            }
            animate();
        };
    </script>
</body>
</html>
