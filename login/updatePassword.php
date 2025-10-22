<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include_once __DIR__ . '/../database/db_connection.php';
include_once __DIR__ . '/../config.php';

$update_error = '';
$update_success = '';
if (!isset($_SESSION['otp_verified']) || !isset($_SESSION['reset_email'])) {
    header('Location: resetPassword.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    if ($new_password !== $confirm_password) {
        $update_error = "Mật khẩu xác nhận không khớp.";
    } elseif (strlen($new_password) < 8) {
        $update_error = "Mật khẩu phải từ 8 ký tự trở lên.";
    } elseif (!preg_match('/[A-Z]/', $new_password)) {
        $update_error = "Mật khẩu phải có ít nhất 1 chữ hoa.";
    } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $new_password)) {
        $update_error = "Mật khẩu phải có ít nhất 1 ký tự đặc biệt.";
    } else {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $email = $_SESSION['reset_email'];
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
        $stmt->bind_param("ss", $hash, $email);
        if ($stmt->execute()) {
            unset($_SESSION['otp_verified']);
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_otp']);
            echo '<div id="successModal" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;z-index:9999;">
                    <div style="background:#fff;border-radius:16px;padding:32px 24px;box-shadow:0 8px 32px 0 rgba(31,38,135,0.18);display:flex;flex-direction:column;align-items:center;">
                        <i class="fa-solid fa-circle-check" style="font-size:3rem;color:#4ade80;margin-bottom:12px;"></i>
                        <div style="font-size:1.2rem;font-weight:600;color:#16a34a;margin-bottom:8px;">Đổi mật khẩu thành công!</div>
                        <div style="color:#555;margin-bottom:18px;">Đang chuyển hướng về trang đăng nhập...</div>
                        <div class="loader" style="width:40px;height:40px;border:4px solid #f3f3f3;border-top:4px solid #fc466b;border-radius:50%;animation:spin 1s linear infinite;"></div>
                    </div>
                </div>
                <style>@keyframes spin{0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}</style>';
            echo '<script>setTimeout(function(){window.location.href="index.php";}, 1800);</script>';
            exit;
        } else {
            $update_error = "Đổi mật khẩu thất bại. Vui lòng thử lại.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Password - Coffee Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: url('../Photos/login_background.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
        }

        .update-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.32);
            padding: 40px 32px 32px 32px;
            width: 350px;
            max-width: 95vw;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .update-header {
            color: #fc466b;
            margin-bottom: 18px;
            font-size: 2rem;
            font-weight: 600;
        }

        .profile-icon {
            font-size: 3rem;
            color: #fc466b;
            margin-bottom: 16px;
        }

        .input-group {
            width: 100%;
            margin-bottom: 18px;
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-group i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #fc466b;
            font-size: 1.2rem;
            z-index: 2;
        }

        .input-group input {
            width: 100%;
            padding: 12px 12px 12px 44px;
            border-radius: 10px;
            border: none;
            background: rgba(255, 255, 255, 0.25);
            color: #222;
            font-size: 1rem;
            outline: none;
            box-sizing: border-box;
        }

        .input-group input::placeholder {
            color: #888;
        }

        .update-btn {
            width: 100%;
            background: #fc466b;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.2s;
        }

        .update-btn:hover {
            background: #3f5efb;
        }

        .back-link {
            margin-top: 16px;
            text-align: center;
            color: #fff;
            font-size: 1rem;
        }

        .back-link a {
            color: #fc466b;
            text-decoration: underline;
            margin-left: 6px;
            cursor: pointer;
            transition: color 0.2s;
        }

        .back-link a:hover {
            color: #3f5efb;
        }

        .error-message {
            color: #fff;
            background: linear-gradient(90deg, rgb(220, 65, 96) 0%, rgb(151, 102, 28) 100%);
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 1rem;
            text-align: center;
            opacity: 0;
            transition: opacity 0.4s, transform 0.4s;
            padding: 12px 18px;
            box-shadow: 0 2px 12px rgba(127, 97, 47, 0.18);
            position: relative;
            transform: translateY(-10px);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .error-message.show {
            opacity: 1;
            transform: translateY(0);
        }

        .error-message i {
            font-size: 1.3rem;
            color: #fff;
            margin-right: 8px;
            animation: shake 0.5s;
        }

        @keyframes shake {
            0% {
                transform: translateX(0);
            }

            20% {
                transform: translateX(-4px);
            }

            40% {
                transform: translateX(4px);
            }

            60% {
                transform: translateX(-4px);
            }

            80% {
                transform: translateX(4px);
            }

            100% {
                transform: translateX(0);
            }
        }
    </style>
</head>

<body>
    <div style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
        <div class="update-container">
            <div class="profile-icon" style="cursor:pointer;" onclick="window.location.href='user.php'">
                <img src="../Photos/logo.png" alt="Logo" style="width:220px; height:100px; object-fit:cover;" />
            </div>
            <div class="update-header">Update Password</div>
            <form method="post" autocomplete="off">
                <div class="input-group">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="new_password" placeholder="Mật khẩu mới" required id="new_password">
                </div>
                <div class="input-group">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu mới" required id="confirm_password">
                </div>
                <?php if ($update_error): ?>
                    <div class="error-message show"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($update_error); ?></div>
                <?php endif; ?>
                <button type="submit" class="reset-btn">Đổi mật khẩu</button>
            </form>
            <div class="back-link">
                <span>Back to</span>
                <a href="user.php">Login</a>
            </div>
        </div>
    </div>
    <script>
        function checkStrength() {
            var pwd = document.getElementById('newPassword').value;
            var bar = document.getElementById('progressBar');
            var label = document.getElementById('progressLabel');
            var text = document.getElementById('strengthText');
            // Criteria
            var hasLength = pwd.length >= 8;
            var hasNumber = /[0-9]/.test(pwd);
            var hasLower = /[a-z]/.test(pwd);
            var hasUpper = /[A-Z]/.test(pwd);
            var hasSpecial = /[^A-Za-z0-9]/.test(pwd);
            var met = [hasLength, hasNumber, hasLower, hasUpper, hasSpecial].filter(Boolean).length;
            // Progress bar
            var percent = met * 20;
            var colors = [
                "bg-red-500",
                "bg-orange-400",
                "bg-yellow-400",
                "bg-blue-400",
                "bg-green-500"
            ];
            var labels = [
                "Very Weak",
                "Weak",
                "Fair",
                "Strong",
                "Very Strong"
            ];
            bar.style.width = percent + "%";
            bar.className = "h-2 rounded-full transition-all duration-300 " + colors[met === 0 ? 0 : met - 1];
            label.innerText = labels[met === 0 ? 0 : met - 1];
            if (!pwd) {
                label.innerText = '';
                bar.style.width = '0%';
                text.innerHTML = '<span class="text-gray-400">Start typing to check password strength...</span>';
            } else {
                text.innerText = '';
            }
        }
    </script>
</body>
</html>