<?php
session_start();
include_once __DIR__ . '/../database/db_connection.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/index.php');
    exit;
}
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
<div class="flex flex-col items-center justify-center h-full py-20">
  <div class="bg-white rounded-3xl shadow-xl p-8 flex flex-col items-center">
    <i class="fa fa-sign-out-alt text-red-500 text-5xl mb-4 animate-bounce"></i>
    <div class="font-bold text-xl text-gray-800 mb-2">Bạn có chắc muốn đăng xuất?</div>
    <div class="text-gray-500 mb-6">Sau khi đăng xuất, bạn sẽ cần đăng nhập lại để sử dụng các dịch vụ.</div>
    <a href="../user.php" class="btn-orange hover:bg-orange-600 text-white px-8 py-3 rounded-xl font-bold shadow transition mb-2">Đăng xuất</a>
    <a href="account.php" class="text-pink-600 hover:underline font-semibold">Quay lại tài khoản</a>
  </div>
</div>
<style>
@keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
.animate-bounce { animation: bounce 0.7s infinite; }
</style>
