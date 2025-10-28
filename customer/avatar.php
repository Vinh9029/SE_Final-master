<?php
// Sử dụng kết nối chuẩn
include_once __DIR__ . '/../database/db_connection.php';

$user_id = $_SESSION['user_id'];


// Lấy avatar từ trường avatar_image hoặc random/avatar upload
$stmt = $conn->prepare("SELECT avatar_image FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($avatar_image_db);
$stmt->fetch();
$stmt->close();

$avatar_src = '';
$frame_src = '';
// Avatar logic: ưu tiên avatar_image trong DB, sau đó là file upload theo user_id, cuối cùng là random mặc định
if ($avatar_image_db && file_exists(__DIR__ . '/../' . $avatar_image_db)) {
    $avatar_src = '../' . $avatar_image_db;
} elseif (glob(__DIR__ . '/Photos/upload_avatar/' . $user_id . '_*.*')) {
    // Tìm file upload theo user_id, có thể là png/jpg/jpeg, đặt tên theo user_id_timestamp.ext
    $files = glob(__DIR__ . '/Photos/upload_avatar/' . $user_id . '_*.*');
    $avatar_src = $files ? '../customer/Photos/upload_avatar/' . basename($files[0]) : '';
} else {
    // Nếu chưa có avatar trong DB, gán avatar mặc định random vào DB cho user
    $random = rand(1, 12);
    $default_avatar = 'customer/Photos/avatar/avatar' . $random . '.jpg';
    $avatar_src = '../' . $default_avatar;
    // Chỉ gán nếu DB chưa có avatar
    $stmt = $conn->prepare("UPDATE users SET avatar_image = ? WHERE user_id = ? AND (avatar_image IS NULL OR avatar_image = '')");
    $stmt->bind_param("si", $default_avatar, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch loyalty points
$stmt = $conn->prepare("SELECT SUM(points) AS total_points FROM loyalty_points WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($total_points);
$stmt->fetch();
$stmt->close();
$total_points = $total_points ?? 0;

// Cập nhật session với dữ liệu mới nhất
$_SESSION['avatar_image'] = $avatar_image_db;
$_SESSION['loyalty_points'] = $total_points;

// Determine user level based on points
if ($total_points >= 1000) {
    $level = 'Diamond';
    $level_icon = '💎';
    $border_color = 'border-blue-500';
} elseif ($total_points >= 600) {
    $level = 'Platinum';
    $level_icon = '✨';
    $border_color = 'border-white';
} elseif ($total_points >= 300) {
    $level = 'Gold';
    $level_icon = '🟡';
    $border_color = 'border-yellow-400';
} elseif ($total_points >= 100) {
    $level = 'Silver';
    $level_icon = '⚪';
    $border_color = 'border-gray-400';
} else {
    $level = 'Bronze';
    $level_icon = '🟤';
    $border_color = 'border-yellow-800';
}

// Chọn border style theo level
$border_style = '';
if ($level === 'Diamond') {
    $border_style = 'border-4 border-blue-400 ring-4 ring-blue-200 shadow-lg';
} elseif ($level === 'Platinum') {
    $border_style = 'border-4 border-gray-300 ring-4 ring-gray-200 shadow-lg';
} elseif ($level === 'Gold') {
    $border_style = 'border-4 border-yellow-400 ring-4 ring-yellow-200 shadow-lg';
} elseif ($level === 'Silver') {
    $border_style = 'border-4 border-gray-400 ring-2 ring-gray-200 shadow-md';
} else {
    $border_style = 'border-4 border-yellow-800 ring-2 ring-yellow-100 shadow';
}

// Kiểm tra lại frame_src
if (!file_exists($frame_src)) {
    // Nếu frame không tồn tại, dùng frame mặc định
    $frame_src = '../customer/Photos/frame/bronze.png';
}

// Calculate points needed for next level and next level name
if ($total_points < 100) {
    $next_level = 'Silver';
    $points_to_next = 100 - $total_points;
} elseif ($total_points < 300) {
    $next_level = 'Gold';
    $points_to_next = 300 - $total_points;
} elseif ($total_points < 600) {
    $next_level = 'Platinum';
    $points_to_next = 600 - $total_points;
} elseif ($total_points < 1000) {
    $next_level = 'Diamond';
    $points_to_next = 1000 - $total_points;
} else {
    $next_level = null;
    $points_to_next = 0;
}

// Progress bar tính theo từng mốc level
$level_points = [0, 100, 300, 600, 1000, 5000];
$level_index = 0;
for ($i = 0; $i < count($level_points) - 1; $i++) {
    if ($total_points >= $level_points[$i] && $total_points < $level_points[$i + 1]) {
        $level_index = $i;
        break;
    }
    if ($total_points >= 1000) $level_index = 4;
}
$min = $level_points[$level_index];
$max = $level_points[$level_index + 1];
$progress = min(100, max(0, (($total_points - $min) / ($max - $min)) * 100));
?>
<div class="mb-4 flex flex-col items-center">
    <div class="relative w-36 h-36 rounded-full overflow-hidden group cursor-pointer <?php echo $border_style; ?>">
        <form id="avatar-upload-form" enctype="multipart/form-data" method="post" class="absolute inset-0 w-full h-full flex items-center justify-center z-10" style="background:rgba(255,255,255,0.0);">
            <input type="file" name="avatar_image" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" title=" " onchange="this.form.submit()" />
            <img src="<?php echo $avatar_src; ?>" alt="Avatar" class="w-full h-full object-cover rounded-full pointer-events-none" />
            <div class="absolute top-1 right-1 text-2xl pointer-events-none"><?php echo $level_icon; ?></div>
        </form>
    </div>
    <div class="mt-2 text-center">
        <div class="font-bold text-lg"><?php echo htmlspecialchars($level); ?> Level <?php echo $level_icon; ?></div>
        <div class="text-yellow-500 font-semibold"><?php echo $total_points; ?> ⭐ Điểm tích lũy</div>
    </div>
    <?php if ($next_level): ?>
        <div class="w-full max-w-xs mt-3">
            <div class="text-sm mb-1 font-semibold text-pink-600 flex items-center gap-2">
                <i class="fa fa-star text-yellow-400"></i>
                Còn <span class="font-bold text-pink-500 mx-1"><?php echo $points_to_next; ?></span> điểm để lên <span class="font-bold text-yellow-500 mx-1"><?php echo $next_level; ?></span>!
            </div>
            <div class="w-full h-4 bg-gray-100 rounded-full overflow-hidden shadow-inner">
                <div class="h-4 bg-gradient-to-r from-pink-400 to-yellow-400 rounded-full transition-all flex items-center" style="width: <?php echo $progress; ?>%">
                    <span class="text-xs text-white ml-2 font-bold"><?php echo round($progress); ?>%</span>
                </div>
            </div>
            <div class="flex justify-between text-xs text-gray-400 mt-1">
                <span><?php echo $min; ?> ⭐</span>
                <span><?php echo $max; ?> ⭐</span>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php
// Handle avatar upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar_image'])) {
    $file = $_FILES['avatar_image'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/Photos/upload_avatar/';
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $target = $upload_dir . $user_id . '_' . time() . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            // Lưu đường dẫn vào DB
            $avatar_path = 'customer/Photos/upload_avatar/' . basename($target);
            $stmt = $conn->prepare("UPDATE users SET avatar_image = ? WHERE user_id = ?");
            $stmt->bind_param("si", $avatar_path, $user_id);
            $stmt->execute();
            $stmt->close();
            // Chỉ reload 1 lần sau upload thành công
            echo '<script>window.location.replace(window.location.href.split("?")[0]);</script>';
        } else {
            echo '<div class="text-red-500 text-sm mt-2">Lỗi upload ảnh đại diện.</div>';
        }
    } else {
        echo '<div class="text-red-500 text-sm mt-2">Lỗi upload ảnh đại diện.</div>';
    }
}
?>