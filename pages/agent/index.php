<?php
include_once __DIR__ . '/../../config.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TheOldFlavour Agent | Old Flavour Coffee</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #E6D3B1; }
        h1, h2 { font-family: 'Playfair Display', serif; }
        .agent-shell { min-height: calc(100vh - 220px); }
        .msg-bot { background: #fff; border: 1px solid #ead9c0; color: #4B2E05; }
        .msg-user { background: #fce7f3; color: #9d174d; }
        .cta-agent { background: linear-gradient(135deg, #4B2E05 0%, #C4A35A 100%); }
        .cta-agent:hover { background: linear-gradient(135deg, #C4A35A 0%, #4B2E05 100%); }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <?php include_once __DIR__ . '/../../includes/header.php'; ?>
    <main class="flex-1 max-w-5xl mx-auto w-full px-4 py-8 agent-shell">
        <div class="text-center mb-6">
            <p class="text-sm uppercase tracking-wide text-pink-600 font-semibold">Trợ lý quán</p>
            <h1 class="text-3xl md:text-4xl font-bold text-pink-600 mb-2">TheOldFlavour Agent</h1>
            <p class="text-gray-700">Hỏi về thực đơn, giờ mở cửa, giao hàng và khuyến mãi — phong cách Old Flavour.</p>
        </div>
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col" style="min-height: 560px;">
            <div class="cta-agent text-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="<?php echo $base_url; ?>/Photos/banner.jpg" alt="Logo" class="h-12 w-12 object-cover rounded-full shadow border-2 border-white/40" />
                    <div>
                        <div class="font-bold">TheOldFlavour Agent</div>
                        <div class="text-sm text-white/90">Đang trực tuyến</div>
                    </div>
                </div>
                <a href="<?php echo $base_url; ?>/pages/contactUS.php" class="text-sm bg-white/20 hover:bg-white/30 px-3 py-1 rounded-full">Liên hệ quán</a>
            </div>
            <div id="agent-page-messages" class="flex-1 overflow-y-auto p-5 space-y-3" style="background:#fdfaf6;"></div>
            <form id="agent-page-form" class="p-4 border-t flex gap-3 bg-white">
                <input id="agent-page-input" type="text" maxlength="500" placeholder="Ví dụ: Quán mở cửa lúc mấy giờ?" class="flex-1 border rounded-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-200" required />
                <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white px-6 py-3 rounded-full font-semibold">Gửi</button>
            </form>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <button type="button" class="agent-hint bg-white rounded-xl shadow p-4 text-left hover:shadow-pink-200 transition" data-q="Thực đơn hôm nay có gì?">
                <div class="font-semibold text-pink-600 mb-1"><i class="fa fa-mug-hot mr-1"></i> Thực đơn</div>
                <div class="text-sm text-gray-600">Gợi ý cà phê, trà sữa và món kèm.</div>
            </button>
            <button type="button" class="agent-hint bg-white rounded-xl shadow p-4 text-left hover:shadow-pink-200 transition" data-q="Phí giao hàng như thế nào?">
                <div class="font-semibold text-pink-600 mb-1"><i class="fa fa-motorcycle mr-1"></i> Giao hàng</div>
                <div class="text-sm text-gray-600">Nhận tại quầy hoặc ship tận nơi.</div>
            </button>
            <button type="button" class="agent-hint bg-white rounded-xl shadow p-4 text-left hover:shadow-pink-200 transition" data-q="Có khuyến mãi hay voucher không?">
                <div class="font-semibold text-pink-600 mb-1"><i class="fa fa-gift mr-1"></i> Ưu đãi</div>
                <div class="text-sm text-gray-600">Combo, giờ vàng và mã giảm giá.</div>
            </button>
        </div>
    </main>
    <?php include_once __DIR__ . '/../../includes/footer.php'; ?>
    <script>
    (function() {
        const box = document.getElementById('agent-page-messages');
        const form = document.getElementById('agent-page-form');
        const input = document.getElementById('agent-page-input');
        const endpoint = <?php echo json_encode($base_url . '/includes/handlers/agent/reply.php'); ?>;

        function addMsg(text, who) {
            const wrap = document.createElement('div');
            wrap.className = 'flex ' + (who === 'user' ? 'justify-end' : 'justify-start');
            const bubble = document.createElement('div');
            bubble.className = 'max-w-[80%] px-4 py-3 rounded-2xl text-sm leading-relaxed ' + (who === 'user' ? 'msg-user' : 'msg-bot');
            bubble.textContent = text;
            wrap.appendChild(bubble);
            box.appendChild(wrap);
            box.scrollTop = box.scrollHeight;
        }

        async function sendMessage(text) {
            addMsg(text, 'user');
            try {
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: text })
                });
                const data = await res.json();
                addMsg(data.reply || 'Xin lỗi, mình chưa trả lời được.', 'bot');
            } catch (e) {
                addMsg('Không gửi được tin. Hãy chắc Apache đang chạy trên XAMPP.', 'bot');
            }
        }

        addMsg('Chào bạn, mình là TheOldFlavour Agent. Hỏi mình về menu, địa chỉ, giờ mở cửa hoặc cách đặt món nhé.', 'bot');

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const text = input.value.trim();
            if (!text) return;
            input.value = '';
            sendMessage(text);
        });
        document.querySelectorAll('.agent-hint').forEach(function(btn) {
            btn.addEventListener('click', function() {
                sendMessage(btn.getAttribute('data-q'));
            });
        });
    })();
    </script>
</body>
</html>
