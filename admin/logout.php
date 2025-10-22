<?php
session_start();
include_once __DIR__ . '/../database/db_connection.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/index.php');
    exit;
}
// Hủy session và đăng xuất
session_unset();
session_destroy();
echo '<div id="successModal" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(255,255,255,0.45);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;z-index:9999;">
        <div style="background:#fff;border-radius:16px;padding:32px 24px;box-shadow:0 8px 32px 0 rgba(31,38,135,0.18);display:flex;flex-direction:column;align-items:center;">
            <i class="fa fa-sign-out-alt text-red-500 text-5xl mb-4 animate-bounce"></i>
            <div style="font-size:1.2rem;font-weight:600;color:#fc466b;margin-bottom:8px;">Đăng xuất thành công!</div>
            <div style="color:#555;margin-bottom:18px;">Đang chuyển hướng về trang chủ...</div>
            <div class="loader" style="width:40px;height:40px;border:4px solid #f3f3f3;border-top:4px solid #fc466b;border-radius:50%;animation:spin 1s linear infinite;"></div>
        </div>
    </div>
    <style>@keyframes spin{0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}</style>';
echo '<script>setTimeout(function(){window.location.href="../index.php";}, 1800);</script>';
exit;
?>
