<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include_once __DIR__ . '/../database/db_connection.php';
include_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php'; 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendOtpMail($to, $otp) {
    $mail = new PHPMailer(true);
    try {
        // $mail->SMTPDebug = 2; // Bật để gỡ lỗi nếu cần, sau đó hãy tắt đi
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'bearastrikingresemblance@gmail.com'; // Thay bằng Gmail của bạn
        $mail->Password   = 'dozf xgai wpkk hnil';    // Đảm bảo App Password này đúng và nhất quán
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->setFrom('bearastrikingresemblance@gmail.com', 'The Old Favour ');
        $mail->addAddress($to);
        #Fix Vietnamese characters issue
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        #content 
        $mail->isHTML(true);
        $mail->Subject = "🔐 MÃ OTP XÁC THỰC - Old Favour Coffee";
        $mail->Body = "
<div style='font-family:Segoe UI,Arial,sans-serif;padding:24px;background:#f9fafb;border-radius:12px;max-width:600px;margin:auto;border:1px solid #eee;'>
  <div style='text-align:center;margin-bottom:20px;'>    
    <img src='" . $base_url . "/Photos/banner.jpg' alt='The Old Favour Coffee' style='width:80px;margin-bottom:10px;'>
    <p style='color:#555;margin:6px 0;'>Vui lòng sử dụng mã OTP bên dưới để tiếp tục</p>
  </div>
  <div style='margin:20px auto;padding:20px;background:#fff0f5;border:2px dashed #fc466b;border-radius:10px;text-align:center;max-width:300px;'>
    <span style='font-size:1.5rem;color:#fc466b;font-weight:bold;letter-spacing:3px;'>$otp</span>
  </div>
  <p style='color:#333;text-align:center;margin-top:20px;font-size:0.95rem;'>
    Mã OTP chỉ có hiệu lực trong <b>5 phút</b>.<br>
    Tuyệt đối không chia sẻ mã này cho bất kỳ ai.
  </p>
  <hr style='margin:24px 0;border:none;border-top:1px solid #eee;'>
  <small style='color:#888;display:block;text-align:center;line-height:1.6;'>
    Đây là email tự động từ hệ thống <b>The Old Favour Coffee</b>. <br>
    Nếu bạn không yêu cầu OTP, vui lòng bỏ qua email này.
  </small>
</div>
";
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer Error (sendOtpMail): " . $e->getMessage()); // Log the actual error
        return false;
    }
}

$reset_error = '';
$reset_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $otp = rand(100000, 999999);
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_otp'] = $otp;
        if (sendOtpMail($email, $otp)) {
            $reset_success = "<div style='display:flex;flex-direction:column;align-items:center;gap:10px;'>"
                . "<i class='fa-solid fa-circle-check' style='font-size:2.2rem;color:#4ade80;'></i>"
                . "<span style='font-weight:600;color:#16a34a;'>Mã OTP đã được gửi đến email của bạn!</span>"
                . "<span style='color:#555;'>Vui lòng kiểm tra hộp thư và nhập mã OTP tại <a href='verifyOtp.php' style='color:#fc466b;text-decoration:underline;'>trang xác thực OTP</a>.</span>"
                . "</div>";
        } else {
            $reset_error = "<div style='display:flex;flex-direction:column;align-items:center;gap:10px;'>"
                . "<i class='fa-solid fa-triangle-exclamation' style='font-size:2.2rem;color:#fc466b;'></i>"
                . "<span style='font-weight:600;color:#fc466b;'>Gửi email thất bại!</span>"
                . "<span style='color:#555;'>Vui lòng thử lại hoặc kiểm tra kết nối.</span>"
                . "</div>";
        }
    } else {
        $reset_error = "Email không tồn tại trong hệ thống.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Coffee Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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

        .reset-container {
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

        .reset-header {
            color: #fc466b;
            margin-bottom: 18px;
            font-size: 2rem;
            font-weight: 600;
        }

        .profile-icon {
            font-size: 3rem;
            color: #fc466b;
            margin-bottom: 2px;
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

        .reset-btn {
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

        .reset-btn:hover {
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
    </style>
</head>

<body>
    <div style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
        <div class="reset-container">
            <div class="profile-icon" style="cursor:pointer;" onclick="window.location.href='<?php echo $base_url; ?>/login/index.php'">
                <img src="../Photos/logo.png" alt="Logo" style="width:210px; height:100px; object-fit:cover;" />
            </div>
            <div class="reset-header">Reset Password</div>
            <form method="post" autocomplete="off">
                <div class="input-group">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <button type="submit" class="reset-btn">Gửi mã OTP</button>
                <?php if ($reset_error): ?>
                    <div class="error-message show"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($reset_error); ?></div>
                <?php endif; ?>
                <?php if ($reset_success): ?>
                    <div class="success-message show"><i class="fa-solid fa-circle-check"></i> <?php echo $reset_success; ?></div>
                    <div style="margin-top:12px;text-align:center;color:#555;font-size:0.98rem;">Đã gửi mã OTP, vui lòng kiểm tra email và nhập mã tại <a href='verifyOtp.php' style='color:#fc466b;text-decoration:underline;'>trang xác thực OTP</a>.</div>
                <?php endif; ?>
            </form>
            <div class="back-link">
                <span>Remembered your password?</span>
                <a href="<?php echo $base_url; ?>/login/index.php">Login</a>
            </div>
        </div>
    </div>
</body>

</html>