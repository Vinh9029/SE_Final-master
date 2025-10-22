<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include_once __DIR__ . '/../database/db_connection.php';
include_once __DIR__ . '/../config.php';

$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $stmt = $conn->prepare("SELECT user_id, password, role FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $hash, $db_role);
        $stmt->fetch();
        if (password_verify($password, $hash) && $db_role === $role) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $db_role;
            if ($db_role === 'admin') {
                echo '<div id="successModal" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;z-index:9999;">
                        <div style="background:#fff;border-radius:16px;padding:32px 24px;box-shadow:0 8px 32px 0 rgba(31,38,135,0.18);display:flex;flex-direction:column;align-items:center;">
                            <i class="fa-solid fa-circle-check" style="font-size:3rem;color:#4ade80;margin-bottom:12px;"></i>
                            <div style="font-size:1.2rem;font-weight:600;color:#16a34a;margin-bottom:8px;">Đăng nhập quản trị viên thành công!</div>
                            <div style="color:#555;margin-bottom:18px;">Đang chuyển hướng về Dashboard...</div>
                            <div class="loader" style="width:40px;height:40px;border:4px solid #f3f3f3;border-top:4px solid #fc466b;border-radius:50%;animation:spin 1s linear infinite;"></div>
                        </div>
                    </div>
                    <style>@keyframes spin{0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}</style>';
                echo '<script>setTimeout(function(){window.location.href="' . $base_url . '/admin/dashboard.php";}, 1800);</script>';
                exit;
            } else {
                echo '<div id="successModal" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;z-index:9999;">
                        <div style="background:#fff;border-radius:16px;padding:32px 24px;box-shadow:0 8px 32px 0 rgba(31,38,135,0.18);display:flex;flex-direction:column;align-items:center;">
                            <i class="fa-solid fa-circle-check" style="font-size:3rem;color:#4ade80;margin-bottom:12px;"></i>
                            <div style="font-size:1.2rem;font-weight:600;color:#16a34a;margin-bottom:8px;">Đăng nhập thành công!</div>
                            <div style="color:#555;margin-bottom:18px;">Đang chuyển hướng về trang chủ...</div>
                            <div class="loader" style="width:40px;height:40px;border:4px solid #f3f3f3;border-top:4px solid #fc466b;border-radius:50%;animation:spin 1s linear infinite;"></div>
                        </div>
                    </div>
                    <style>@keyframes spin{0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}</style>';
                echo '<script>setTimeout(function(){window.location.href="' . $base_url . '/index.php";}, 1800);</script>';
                exit;
            }
        } else {
            $login_error = "Sai thông tin đăng nhập hoặc vai trò.";
        }
    } else {
        $login_error = "Sai thông tin đăng nhập hoặc vai trò.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coffee Shop Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: url('../Photos/login_background.jpg') no-repeat center center fixed;
            background-size: cover;
            /* min-height: 100vh; */
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .side-image-box {
            width: 390px;
            height: 484px;
            /* padding-right: 120px; */
            background: rgba(255,255,255,0.10);
            border-radius: 60px 10 0 20px;
            margin-right: 10px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
            border-style: solid 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        @media (max-width: 900px) {
            .side-image-box {
                display: none;
            }
        }
        .login-container {
            background: rgba(255,255,255,0.1);
            border-radius: 0 20px 20px 0;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.32);
            padding: 40px 32px 32px 32px;
            width: 330px;
            max-width: 95vw;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .profile-icon {
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            padding: 18px;
            margin-bottom: 18px;
            font-size: 2.5rem;
            color: #fff;
        }
        .switch-btn {
            background: #fff;
            color: #3f5efb;
            border: none;
            border-radius: 20px;
            padding: 6px 18px;
            margin-bottom: 18px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.2s;
        }
        .switch-btn.active {
            background: #fc466b;
            color: #fff;
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
            color: #3f5efb;
            font-size: 1.2rem;
            z-index: 2;
        }
        .input-group input {
            width: 100%;
            padding: 12px 60px 12px 44px;
            border-radius: 10px;
            border: none;
            background: rgba(255,255,255,0.25);
            color: #222;
            font-size: 1rem;
            outline: none;
            box-sizing: border-box;
        }
        .input-group input::placeholder {
            color: #888;
        }
        .toggle-password {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            z-index: 2;
            color: #3f5efb;
            font-size: 1.3rem;
            background: transparent;
            border: none;
            padding: 0;
            display: flex;
            align-items: center;
            padding-left: 30px;
        }
        .toggle-password:hover {
            color: #fc466b;
        }
        .hint {
            font-size: 0.85rem;
            color: #fc466b;
            margin-top: 2px;
            margin-left: 4px;
            margin-bottom: -10px;
        }
        .options {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }
        .remember {
            display: flex;
            align-items: center;
            font-size: 0.95rem;
            color: #fff;
        }
        .remember input {
            margin-right: 6px;
        }
        .forgot {
            color: #fff;
            text-decoration: underline;
            font-size: 0.95rem;
            cursor: pointer;
            transition: color 0.2s;
        }
        .forgot:hover {
            color: #fc466b;
        }
        .forgot a,
        .register-link a {
            color: #fc466b;
            text-decoration: underline;
            margin-left: 6px;
            cursor: pointer;
            transition: color 0.2s;
        }
        .forgot a:hover,
        .register-link a:hover {
            color: #3f5efb;
        }
        .login-btn {
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
        .login-btn:hover {
            background: #3f5efb;
        }
        .register-link {
            margin-top: 16px;
            text-align: center;
            color: #fff;
            font-size: 1rem;
        }
        .register-link a {
            color: #fc466b;
            text-decoration: underline;
            margin-left: 6px;
            cursor: pointer;
        }
        .register-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .register-content {
            background: #fff;
            border-radius: 16px;
            padding: 32px 24px;
            min-width: 320px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .register-content h2 {
            color: #fc466b;
            margin-bottom: 18px;
        }
        .register-content .input-group i {
            color: #fc466b;
        }
        .register-content .login-btn {
            width: 100%;
            margin-top: 12px;
        }
        .error-message {
            color: #fc466b;
            font-size: 0.9rem;
            margin-bottom: 10px;
            display: none;
        }
        .error-message.show {
            display: block;
        }
    </style>
</head>
<body>
    <div style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
        <div class="side-image-box">
            <img id="login_banner" src="../Photos/login_banner.jpg" alt="Login Illustration" style="width:100%; height:100%; object-fit:cover; border-radius:20px 0 0 20px;" />
        </div>
        <div class="login-container">
            <div class="profile-icon">
                <i class="fa-solid fa-user"></i>
            </div>
            <div style="display: flex; gap: 8px; margin-bottom: 10px;">
                <button class="switch-btn active" id="userBtn" onclick="switchRole('user')">User</button>
                <button class="switch-btn" id="adminBtn" onclick="switchRole('admin')">Admin</button>
            </div>
            <form method="post" autocomplete="off">
                <input type="hidden" name="role" id="roleInput" value="customer">
                <div class="input-group">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="input-group">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required id="password">
                    <span class="toggle-password" onclick="togglePassword()">
                        <i class="fa-regular fa-eye" id="eyeIcon"></i>
                    </span>
                </div>
                <div class="options">
                    <label class="remember"><input type="checkbox"><strong>Remember me</strong></label>
                    <span class="forgot"><a href="resetPassword.php"><strong>Forgot your password?</strong></a></span>
                </div>
                <div id="errorMessage" class="error-message <?php if ($login_error) echo 'show'; ?>">
                    <?php if ($login_error) echo '<i class="fa-solid fa-triangle-exclamation"></i> ' . htmlspecialchars($login_error); ?>
                </div>
                <button type="submit" class="login-btn">LOGIN</button>
                <div class="register-link">
                    <span><strong>Don't have an account?</strong></span>
                    <a href="registerAccount.php"><strong>Register</strong></a>
                </div>
            </form>
        </div>
    </div>
    <div id="register-modal" class="register-modal" style="display:none;">
        <div class="register-content">
            <h2>Register Account</h2>
            <form>
                <div class="input-group">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" placeholder="Username" required>
                </div>
                <div class="input-group">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" placeholder="Email" required>
                </div>
                <div class="input-group">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" placeholder="Password" required>
                </div>
                <button type="submit" class="login-btn">Create Account</button>
                <button type="button" class="login-btn" style="background:#888; margin-top:8px;" onclick="hideRegister()">Cancel</button>
            </form>
        </div>
    </div>
    <script>
        function switchRole(role) {
            const userBtn = document.getElementById('userBtn');
            const adminBtn = document.getElementById('adminBtn');
            document.getElementById('roleInput').value = (role === 'user') ? 'customer' : 'admin';
            if (role === 'user') {
                userBtn.classList.add('active');
                adminBtn.classList.remove('active');
                document.querySelector('.profile-icon i').className = 'fa-solid fa-user';
            } else {
                adminBtn.classList.add('active');
                userBtn.classList.remove('active');
                document.querySelector('.profile-icon i').className = 'fa-solid fa-user-tie';
                showAdminToast();
            }
        }

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.className = 'fa-regular fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                eyeIcon.className = 'fa-regular fa-eye';
            }
        }

        function showRegister() {
            document.getElementById('register-modal').style.display = 'flex';
        }

        function hideRegister() {
            document.getElementById('register-modal').style.display = 'none';
        }

        function showAdminToast() {
            let toast = document.createElement('div');
            toast.innerText = 'Bạn đang đăng nhập với vai trò Quản trị viên!';
            toast.style.position = 'fixed';
            toast.style.top = '24px';
            toast.style.right = '24px';
            toast.style.background = '#fc466b';
            toast.style.color = '#fff';
            toast.style.padding = '12px 24px';
            toast.style.borderRadius = '8px';
            toast.style.boxShadow = '0 2px 8px rgba(0,0,0,0.15)';
            toast.style.zIndex = '9999';
            document.body.appendChild(toast);
            setTimeout(() => { toast.remove(); }, 2200);
        }
    </script>
</body>
</html>
