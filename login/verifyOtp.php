<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include_once __DIR__ . '/../database/db_connection.php';
include_once __DIR__ . '/../config.php';

$verify_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    $otp = trim($_POST['otp']);
    if (isset($_SESSION['reset_otp']) && $otp == $_SESSION['reset_otp']) {
        $_SESSION['otp_verified'] = true;
        echo '<div id="successModal" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;z-index:9999;">
                <div style="background:#fff;border-radius:16px;padding:32px 24px;box-shadow:0 8px 32px 0 rgba(31,38,135,0.18);display:flex;flex-direction:column;align-items:center;">
                    <i class="fa-solid fa-circle-check" style="font-size:3rem;color:#4ade80;margin-bottom:12px;"></i>
                    <div style="font-size:1.2rem;font-weight:600;color:#16a34a;margin-bottom:8px;">Xác thực OTP thành công!</div>
                    <div style="color:#555;margin-bottom:18px;">Đang chuyển hướng đến đổi mật khẩu...</div>
                    <div class="loader" style="width:40px;height:40px;border:4px solid #f3f3f3;border-top:4px solid #fc466b;border-radius:50%;animation:spin 1s linear infinite;"></div>
                </div>
            </div>
            <style>@keyframes spin{0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}</style>';
        echo '<script>setTimeout(function(){window.location.href="updatePassword.php";}, 1800);</script>';
        exit;
    } else {
        $verify_error = "Mã OTP không đúng hoặc đã hết hạn.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực OTP - Coffee Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: url('../Photos/login_background.jpg') no-repeat center center fixed; background-size: cover; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', Arial, sans-serif; min-height: 100vh; }
        .verify-container { background: rgba(255,255,255,0.1); border-radius: 20px; box-shadow: 0 8px 32px 0 rgba(31,38,135,0.32); padding: 40px 32px 32px 32px; width: 350px; max-width: 95vw; display: flex; flex-direction: column; align-items: center; }
        .verify-header { color: #fc466b; margin-bottom: 18px; font-size: 2rem; font-weight: 600; }
        .input-group { width: 100%; margin-bottom: 18px; position: relative; display: flex; align-items: center; }
        .input-group i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #fc466b; font-size: 1.2rem; z-index: 2; }
        .input-group input { width: 100%; padding: 12px 12px 12px 44px; border-radius: 10px; border: none; background: rgba(255,255,255,0.25); color: #222; font-size: 1rem; outline: none; box-sizing: border-box; }
        .input-group input::placeholder { color: #888; }
        .verify-btn { width: 100%; background: #fc466b; color: #fff; border: none; border-radius: 8px; padding: 12px; font-size: 1.1rem; font-weight: 600; cursor: pointer; margin-top: 8px; transition: background 0.2s; }
        .verify-btn:hover { background: #3f5efb; }
        .error-message { color: #fff; background: linear-gradient(90deg, rgb(220,65,96) 0%, rgb(151,102,28) 100%); border-radius: 8px; margin-bottom: 10px; font-size: 1rem; text-align: center; opacity: 1; transition: opacity 0.4s, transform 0.4s; padding: 12px 18px; box-shadow: 0 2px 12px rgba(127,97,47,0.18); position: relative; transform: translateY(0); display: flex; align-items: center; justify-content: center; gap: 10px; }
    </style>
</head>
<body>
    <div style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
        <div class="verify-container">
            <div class="verify-header">Xác thực OTP</div>
            <form method="post" autocomplete="off">
                <div class="input-group">
                    <i class="fa-solid fa-key"></i>
                    <input type="text" name="otp" placeholder="Nhập mã OTP" maxlength="6" required>
                </div>
                <?php if ($verify_error): ?>
                    <div class="error-message show"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($verify_error); ?></div>
                <?php endif; ?>
                <button type="submit" class="verify-btn">Xác thực OTP</button>
            </form>
        </div>
    </div>
</body>
</html>
