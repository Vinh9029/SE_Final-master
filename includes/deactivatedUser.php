<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$show_deactivated_popup = false;
if (isset($_SESSION['user_id'])) {
    // Giả sử biến $conn đã được khởi tạo từ file gọi include
    if (isset($conn)) {
        $check_stmt = $conn->prepare("SELECT deactivated_account FROM users WHERE user_id = ?");
        $check_stmt->bind_param('i', $_SESSION['user_id']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result()->fetch_assoc();
        if ($check_result && $check_result['deactivated_account'] == 1) {
            $show_deactivated_popup = true;
        }
    }
}

if ($show_deactivated_popup) :
?>
    <div id="deactivated-popup" style="position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.6); display:flex; align-items:center; justify-content:center; z-index:9999;">
        <div style="background:#fff; border-radius:16px; padding:32px 24px; box-shadow:0 8px 32px 0 rgba(31,38,135,0.18); display:flex; flex-direction:column; align-items:center; max-width: 400px; text-align: center;">
            <i class="fa-solid fa-triangle-exclamation" style="font-size:3rem; color:#ef4444; margin-bottom:12px;"></i>
            <div style="font-size:1.2rem; font-weight:600; color:#dc2626; margin-bottom:8px;">Tài khoản của bạn đã bị khóa</div>
            <div style="color:#555; margin-bottom:18px;">Vui lòng liên hệ với quản trị viên để được hỗ trợ.</div>
            <a href="<?php echo $base_url; ?>/customer/logout.php" style="background-color: #dc2626; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold;">Đăng xuất</a>
        </div>
    </div>
<?php
endif;
?>