<?php
session_start();
include_once __DIR__ . '/../database/db_connection.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_email = trim($_POST['new_email'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Fetch current hash
    $stmt = $conn->prepare("SELECT password, email FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($hash, $current_email);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($current_password, $hash)) {
        $message = 'Mật khẩu hiện tại không đúng.';
        $message_type = 'error';
    } else {
        $updated = false;
        if (!empty($new_email)) {
            // Change email
            if ($new_email === $current_email) {
                $message = 'Email mới giống email hiện tại.';
                $message_type = 'error';
            } else {
                // Check uniqueness
                $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
                $stmt->bind_param("s", $new_email);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $message = 'Email đã được sử dụng.';
                    $message_type = 'error';
                } else {
                    $stmt = $conn->prepare("UPDATE users SET email = ? WHERE user_id = ?");
                    $stmt->bind_param("si", $new_email, $user_id);
                    if ($stmt->execute()) {
                        $message = 'Cập nhật email thành công!';
                        $message_type = 'success';
                        $updated = true;
                    } else {
                        $message = 'Có lỗi khi cập nhật email.';
                        $message_type = 'error';
                    }
                }
                $stmt->close();
            }
        }
        if (!empty($new_password)) {
            // Change password
            if ($new_password !== $confirm_password) {
                $message = 'Mật khẩu mới và xác nhận không khớp.';
                $message_type = 'error';
            } elseif (strlen($new_password) < 8) {
                $message = 'Mật khẩu mới phải ít nhất 8 ký tự.';
                $message_type = 'error';
            } elseif (!preg_match('/[a-z]/', $new_password)) {
                $message = 'Mật khẩu mới phải chứa ít nhất một chữ thường.';
                $message_type = 'error';
            } elseif (!preg_match('/[A-Z]/', $new_password)) {
                $message = 'Mật khẩu mới phải chứa ít nhất một chữ hoa.';
                $message_type = 'error';
            } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $new_password)) {
                $message = 'Mật khẩu mới phải chứa ít nhất một ký tự đặc biệt.';
                $message_type = 'error';
            } else {
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $stmt->bind_param("si", $new_hash, $user_id);
                if ($stmt->execute()) {
                    if ($updated) {
                        $message .= ' Và cập nhật mật khẩu thành công!';
                        $message_type = 'success';
                    } else {
                        echo '<style>@keyframes spin{0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}</style>';
                        echo '<div id="successModal" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;z-index:9999;">
                                <div style="background:#fff;border-radius:16px;padding:32px 24px;box-shadow:0 8px 32px 0 rgba(31,38,135,0.18);display:flex;flex-direction:column;align-items:center;">
                                    <i class="fa-solid fa-circle-check" style="font-size:3rem;color:#4ade80;margin-bottom:12px;"></i>
                                    <div style="font-size:1.2rem;font-weight:600;color:#16a34a;margin-bottom:8px;">Đổi mật khẩu thành công!</div>
                                    <div style="color:#555;margin-bottom:18px;">Đang chuyển hướng về trang tài khoản...</div>
                                    <div class="loader" style="width:40px;height:40px;border:4px solid #f3f3f3;border-top:4px solid #fc466b;border-radius:50%;animation:spin 1s linear infinite;"></div>
                                </div>
                            </div>
                            <script>setTimeout(function(){window.location.href="account.php";}, 1800);</script>';
                        exit;
                    }
                } else {
                    $message = 'Có lỗi khi cập nhật mật khẩu.';
                    $message_type = 'error';
                }
                $stmt->close();
            }
        }
        if (empty($new_email) && empty($new_password)) {
            $message = 'Vui lòng nhập thông tin cần thay đổi.';
            $message_type = 'error';
        }
    }
}
?>
<div class="bg-yellow-50 rounded-3xl shadow-xl p-8 max-w-2xl mx-auto">
  <div class="font-bold text-2xl text-yellow-600 mb-4 flex items-center gap-2"><i class="fa fa-cog text-yellow-500"></i> Cài đặt tài khoản</div>
  <?php if ($message): ?>
    <div class="mb-4 p-4 rounded-xl <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
      <?php echo htmlspecialchars($message); ?>
    </div>
  <?php endif; ?>
  <form method="post" class="flex flex-col gap-6">
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Email mới (để trống nếu không đổi)</label>
      <input type="email" name="new_email" class="border rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-yellow-200" placeholder="Nhập email mới" />
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Mật khẩu hiện tại</label>
      <input type="password" name="current_password" class="border rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-yellow-200" placeholder="Nhập mật khẩu hiện tại" required />
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Mật khẩu mới (để trống nếu không đổi)</label>
      <input type="password" name="new_password" id="new_password" oninput="checkStrength()" class="border rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-yellow-200" placeholder="Nhập mật khẩu mới" />
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Xác nhận mật khẩu mới</label>
      <input type="password" name="confirm_password" class="border rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-yellow-200" placeholder="Xác nhận mật khẩu mới" />
    </div>
    <div class="w-full flex items-center gap-2 mb-2">
      <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
        <div id="progressBar" class="h-2 rounded-full transition-all duration-300 bg-red-500" style="width:0%"></div>
      </div>
      <span id="progressLabel" class="text-xs font-semibold text-gray-700 min-w-[60px] text-right"></span>
    </div>
    <div id="strengthText" class="text-xs mb-1 font-semibold"></div>
    <button type="submit" class="btn-orange hover:bg-orange-600 text-white px-8 py-3 rounded-xl font-bold shadow transition w-fit">Lưu thay đổi</button>
  </form>
</div>
<script>
  function checkStrength() {
    var pwd = document.getElementById('new_password').value;
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
