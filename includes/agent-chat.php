<?php
if (!isset($base_url)) {
    include_once __DIR__ . '/../config.php';
}
$isAgentPage = isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/pages/agent/') !== false;
if ($isAgentPage) {
    return;
}
?>
<div id="tof-agent-root">
    <button type="button" id="tof-agent-toggle" class="tof-agent-toggle" aria-label="Mở TheOldFlavour Agent">
        <i class="fa fa-mug-hot"></i>
        <span class="tof-agent-toggle-label">Agent</span>
    </button>
    <div id="tof-agent-panel" class="tof-agent-panel" hidden>
        <div class="tof-agent-head">
            <div>
                <div class="tof-agent-title">TheOldFlavour Agent</div>
                <div class="tof-agent-sub">Tư vấn menu &amp; đặt món</div>
            </div>
            <div class="tof-agent-head-actions">
                <a href="<?php echo htmlspecialchars($base_url); ?>/pages/agent/index.php" class="tof-agent-full" title="Mở trang hội thoại">
                    <i class="fa fa-expand"></i>
                </a>
                <button type="button" id="tof-agent-close" class="tof-agent-close" aria-label="Đóng chat">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        </div>
        <div id="tof-agent-messages" class="tof-agent-messages"></div>
        <form id="tof-agent-form" class="tof-agent-form" autocomplete="off">
            <input type="text" id="tof-agent-input" maxlength="500" placeholder="Nhập câu hỏi..." required />
            <button type="submit" aria-label="Gửi"><i class="fa fa-paper-plane"></i></button>
        </form>
    </div>
</div>
<style>
.tof-agent-toggle {
    position: fixed;
    right: 20px;
    bottom: 24px;
    z-index: 1100;
    width: 58px;
    height: 58px;
    border: none;
    border-radius: 50%;
    background: linear-gradient(135deg, #4B2E05 0%, #C4A35A 100%);
    color: #fff;
    box-shadow: 0 8px 20px rgba(75, 46, 5, 0.35);
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0;
    font-size: 18px;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.tof-agent-toggle:hover { transform: translateY(-3px); }
.tof-agent-toggle-label {
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.02em;
    line-height: 1;
    margin-top: 2px;
}
.tof-agent-panel {
    position: fixed;
    right: 20px;
    bottom: 96px;
    z-index: 1101;
    width: min(360px, calc(100vw - 24px));
    height: 460px;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 18px 40px rgba(75, 46, 5, 0.25);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid #ead9c0;
    font-family: 'Poppins', sans-serif;
}
.tof-agent-panel[hidden] { display: none !important; }
.tof-agent-head {
    background: linear-gradient(135deg, #4B2E05 0%, #C4A35A 100%);
    color: #fff;
    padding: 12px 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.tof-agent-title { font-weight: 700; font-size: 15px; }
.tof-agent-sub { font-size: 11px; opacity: 0.9; }
.tof-agent-head-actions { display: flex; gap: 8px; }
.tof-agent-full, .tof-agent-close {
    color: #fff;
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    background: rgba(255,255,255,0.18);
}
.tof-agent-close { border: none; cursor: pointer; }
.tof-agent-messages {
    flex: 1;
    overflow-y: auto;
    padding: 12px;
    background: #fdfaf6;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.tof-msg { max-width: 85%; padding: 8px 12px; border-radius: 14px; font-size: 13px; line-height: 1.45; }
.tof-msg.bot { align-self: flex-start; background: #fff; border: 1px solid #ead9c0; color: #4B2E05; }
.tof-msg.user { align-self: flex-end; background: #fce7f3; color: #9d174d; }
.tof-agent-form {
    display: flex;
    gap: 8px;
    padding: 10px;
    border-top: 1px solid #ead9c0;
    background: #fff;
}
.tof-agent-form input {
    flex: 1;
    border: 1px solid #e5d5c0;
    border-radius: 999px;
    padding: 8px 12px;
    font-size: 13px;
    outline: none;
}
.tof-agent-form input:focus { border-color: #C4A35A; }
.tof-agent-form button {
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 999px;
    background: #db2777;
    color: #fff;
    cursor: pointer;
}
@media (max-width: 640px) {
    .tof-agent-toggle { right: 12px; bottom: 18px; }
    .tof-agent-panel { right: 8px; left: 8px; width: auto; bottom: 88px; height: min(70vh, 480px); }
}
</style>
<script>
(function() {
    const toggle = document.getElementById('tof-agent-toggle');
    const panel = document.getElementById('tof-agent-panel');
    const closeBtn = document.getElementById('tof-agent-close');
    const form = document.getElementById('tof-agent-form');
    const input = document.getElementById('tof-agent-input');
    const box = document.getElementById('tof-agent-messages');
    const endpoint = <?php echo json_encode($base_url . '/includes/handlers/agent/reply.php'); ?>;

    function addMsg(text, who) {
        const el = document.createElement('div');
        el.className = 'tof-msg ' + who;
        el.textContent = text;
        box.appendChild(el);
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
            addMsg('Không gửi được tin. Kiểm tra kết nối XAMPP rồi thử lại.', 'bot');
        }
    }

    if (box && !box.dataset.ready) {
        addMsg('Xin chào, mình là TheOldFlavour Agent. Bạn cần gợi ý món, giờ mở cửa hay đặt hàng không?', 'bot');
        box.dataset.ready = '1';
    }

    toggle.addEventListener('click', function() {
        panel.hidden = !panel.hidden;
        if (!panel.hidden) input.focus();
    });
    closeBtn.addEventListener('click', function() { panel.hidden = true; });
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;
        input.value = '';
        sendMessage(text);
    });
})();
</script>
