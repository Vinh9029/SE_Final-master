<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include_once __DIR__ . '/../database/db_connection.php';
include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/sendGiftVoucher.php';

$register_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    if ($password !== $confirm_password) {
        $register_error = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $register_error = "Password must be at least 8 characters.";
    } elseif (!preg_match('/[a-z]/', $password)) {
        $register_error = "Password must contain at least one lowercase letter.";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $register_error = "Password must contain at least one uppercase letter.";
    } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $register_error = "Password must contain at least one special symbol.";
    } else {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username=? OR email=?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $register_error = "Username or email already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'customer')");
            $stmt->bind_param("sss", $username, $hash, $email);
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['username'] = $username;

                // Insert default 0 points into loyalty_points for new user
                $user_id = $stmt->insert_id;
                $points_stmt = $conn->prepare("INSERT INTO loyalty_points (user_id, points) VALUES (?, 0)");
                $points_stmt->bind_param("i", $user_id);
                $points_stmt->execute();
                $points_stmt->close();

                // Tạo mã voucher ngẫu nhiên
                $voucher_code = strtoupper(substr(md5(uniqid($username, true)), 0, 10));
                $program_name = 'Chào mừng thành viên mới';
                $min_order_value = 0;
                $status = 'active';
                $expires_at = date('Y-m-d', strtotime('+30 days'));
                $voucher_stmt = $conn->prepare("INSERT INTO vouchers (user_id, code, discount_percent, program_name, min_order_value, status, expires_at) VALUES (?, ?, 10, ?, ?, ?, ?)");
                $voucher_stmt->bind_param("issdss", $user_id, $voucher_code, $program_name, $min_order_value, $status, $expires_at);
                $voucher_stmt->execute();
                $voucher_stmt->close();
                echo '<div id="successModal" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;z-index:9999;">
                        <div style="background:#fff;border-radius:16px;padding:32px 24px;box-shadow:0 8px 32px 0 rgba(31,38,135,0.18);display:flex;flex-direction:column;align-items:center;">
                            <i class="fa-solid fa-circle-check" style="font-size:3rem;color:#4ade80;margin-bottom:12px;"></i>
                            <div style="font-size:1.2rem;font-weight:600;color:#16a34a;margin-bottom:8px;">Đăng ký thành công!</div>
                            <div style="color:#555;margin-bottom:18px;">Đang chuyển hướng về trang chủ...</div>
                            <div class="loader" style="width:40px;height:40px;border:4px solid #f3f3f3;border-top:4px solid #fc466b;border-radius:50%;animation:spin 1s linear infinite;"></div>
                        </div>
                    </div>
                    <style>@keyframes spin{0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}</style>';
                echo '<script>setTimeout(function(){window.location.href="' . $base_url . '/index.php";}, 1800);</script>';
                sendGiftVoucher($email, $username, $voucher_code);
                exit;
            } else {
                $register_error = "Registration failed. Please try again.";
            }
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
    <title>Register Account - Coffee Shop</title>
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

        .register-container {
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

        .register-header {
            color: #fc466b;
            margin-bottom: 18px;
            font-size: 2rem;
            font-weight: 600;
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

        .register-btn {
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

        .register-btn:hover {
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

    <div class="register-container">
        <div class="profile-icon" style="cursor:pointer;" onclick="window.location.href='<?php echo $base_url; ?>/login/index.php'">
            <img src="../Photos/logo.png" alt="Logo" style="width:210px; height:100px; object-fit:cover;" />
        </div>
        <div class="register-header">Register Account</div>
        <form method="post" autocomplete="off">
            <div class="input-group">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="input-group">
                <i class="fa-solid fa-envelope"></i>
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="input-group">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" placeholder="New Password" required id="password" oninput="checkStrength()">
                
            </div>
            <div class="w-full flex items-center gap-2 mb-2">
                <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div id="progressBar" class="h-2 rounded-full transition-all duration-300 bg-red-500" style="width:0%"></div>
                </div>
                <span id="progressLabel" class="text-xs font-semibold text-gray-700 min-w-[60px] text-right"></span>
            </div>
            <div id="strengthText" class="text-xs mb-1 font-semibold"></div>
            <div class="input-group">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="confirm_password" placeholder="Confirm New Password" required id="confirm_password">
            </div>
            <div id="errorMessage" class="error-message <?php if ($register_error) echo 'show'; ?>">
                <?php if ($register_error) echo '<i class="fa-solid fa-triangle-exclamation"></i> ' . htmlspecialchars($register_error); ?>
            </div>
            <button type="submit" class="register-btn">Create Account</button>
        </form>
        <div class="back-link">
            <span>Already have an account?</span>
            <a href="<?php echo $base_url; ?>/login/index.php">Login</a>
        </div>
    </div>
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            var password = document.getElementById('password').value;
            var confirmPassword = document.getElementById('confirm_password').value;
            var errorMessage = document.getElementById('errorMessage');
            var hasSymbol = /[!@#$%^&*(),.?":{}|<>]/.test(password);
            var hasUppercase = /[A-Z]/.test(password);
            let msg = '';
            if (password !== confirmPassword) {
                msg = '<i class="fa-solid fa-triangle-exclamation"></i> Passwords do not match.';
            } else if (password.length < 8) {
                msg = '<i class="fa-solid fa-triangle-exclamation"></i> Password must be at least 8 characters.';
            } else if (!hasSymbol) {
                msg = '<i class="fa-solid fa-triangle-exclamation"></i> Password must contain at least one special symbol.';
            } else if (!hasUppercase) {
                msg = '<i class="fa-solid fa-triangle-exclamation"></i> Password must contain at least one uppercase letter.';
            }
            if (msg) {
                errorMessage.innerHTML = msg;
                errorMessage.classList.add('show');
                e.preventDefault();
                setTimeout(function() {
                    errorMessage.classList.remove('show');
                    errorMessage.innerText = '';
                }, 2500);
                return;
            }
            errorMessage.innerText = "";
            errorMessage.classList.remove('show');
        });

        function checkStrength() {
            var pwd = document.getElementById('password').value;
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
            // Optionally, show a checklist or other feedback below
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