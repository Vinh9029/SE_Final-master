<?php
session_start();
include_once __DIR__ . '/../database/db_connection.php';
include_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php'; 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Helper function to send JSON response and exit
function send_json_response($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function sendOtpMail($to, $otp, $base_url) {
    // Dependencies are moved inside to prevent premature output
    require_once __DIR__ . '/../vendor/autoload.php';
    
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
    $email = trim($_POST['email']);
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $otp = rand(100000, 999999);
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_otp'] = $otp;
        $_SESSION['otp_timestamp'] = time(); // Set timestamp for expiration

        if (sendOtpMail($email, $otp, $base_url)) {
            $message = "Mã OTP đã được gửi đến email của bạn! Vui lòng kiểm tra hộp thư và nhập mã tại <a href='verifyOtp.php' style='color:#fc466b;text-decoration:underline;'>trang xác thực</a>.";
            send_json_response(['status' => 'success', 'message' => $message]);
        } else {
            $error_message = "Gửi email thất bại! Vui lòng thử lại hoặc kiểm tra lại địa chỉ email.";
            send_json_response(['status' => 'error', 'message' => $error_message]);
        }
    } else {
        send_json_response(['status' => 'error', 'message' => 'Email không tồn tại trong hệ thống.']);
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

        .message-box {
            width: 100%;
            padding: 15px;
            margin-top: 15px;
            border-radius: 8px;
            font-size: 0.95rem;
            text-align: center;
            display: none; /* Hidden by default */
            animation: fadeIn 0.5s;
        }
        .message-box.success {
            background-color: #e6f9f0;
            color: #16a34a;
            border: 1px solid #a1e9c5;
        }
        .message-box.error {
            background-color: #fde8e8;
            color: #e53e3e;
            border: 1px solid #f9b3b3;
        }
        .message-box i {
            margin-right: 8px;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            display: none; /* Hidden by default */
            align-items: center;
            justify-content: center;
            z-index: 10000;
            backdrop-filter: blur(4px);
        }
        .loader {
            width: 60px;
            height: 60px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #fc466b;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>

<body>
    <div id="loadingOverlay">
        <div class="loader"></div>
    </div>

    <div class="reset-container">
        <div class="profile-icon" style="cursor:pointer;" onclick="window.location.href='<?php echo $base_url; ?>/login/index.php'">
            <img src="../Photos/logo.png" alt="Logo" style="width:210px; height:100px; object-fit:cover;" />
        </div>
        <div class="reset-header">Reset Password</div>
        <form id="resetForm" method="post" autocomplete="off">
            <div class="input-group">
                <i class="fa-solid fa-envelope"></i>
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <button type="submit" class="reset-btn">Gửi mã OTP</button>
            <div id="messageBox" class="message-box"></div>
        </form>
        <div class="back-link">
            <span>Remembered your password?</span>
            <a href="<?php echo $base_url; ?>/login/index.php">Login</a>
        </div>
    </div>

    <script>
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const messageBox = document.getElementById('messageBox');
            const loadingOverlay = document.getElementById('loadingOverlay');
            const formData = new FormData(form);

            // Hide previous messages and show loader
            messageBox.style.display = 'none';
            loadingOverlay.style.display = 'flex';

            fetch('', { // Post to the same page
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                loadingOverlay.style.display = 'none';
                messageBox.className = `message-box ${data.status}`;
                messageBox.innerHTML = `<i class="fa-solid ${data.status === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'}"></i> ${data.message}`;
                messageBox.style.display = 'block';
            })
            .catch(error => {
                loadingOverlay.style.display = 'none';
                messageBox.className = 'message-box error';
                messageBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> An unexpected error occurred. Please try again.';
                messageBox.style.display = 'block';
                console.error('Error:', error);
            });
        });
    </script>
</body>

</html>