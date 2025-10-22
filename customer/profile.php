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
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';

    // Basic validation
    if (empty($full_name)) {
        $message = 'Họ tên không được để trống.';
        $message_type = 'error';
    } elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
        $message = 'Số điện thoại không hợp lệ.';
        $message_type = 'error';
    } else {
        // Update user data
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ?, date_of_birth = ?, gender = ? WHERE user_id = ?");
        $stmt->bind_param("sssssi", $full_name, $phone, $address, $date_of_birth, $gender, $user_id);
        if ($stmt->execute()) {
            $message = 'Cập nhật thông tin thành công!';
            $message_type = 'success';
        } else {
            $message = 'Có lỗi xảy ra. Vui lòng thử lại. Lỗi: ' . $stmt->error;
            $message_type = 'error';
        }
        $stmt->close();
    }
}

// Fetch current user data
$stmt = $conn->prepare("SELECT full_name, email, phone, address, date_of_birth, gender FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($full_name, $email, $phone, $address, $date_of_birth, $gender);
$stmt->fetch();
$stmt->close();
?>
<div class="flex flex-col md:flex-row gap-8 items-center md:items-start">
  <!-- Info & Edit Form -->
  <div class="bg-pink-50 rounded-3xl shadow-xl p-8 flex-1 w-full">
    <div class="font-bold text-2xl text-pink-600 mb-4 flex items-center gap-2"><i class="fa fa-user text-pink-500"></i> Thông tin cá nhân</div>
    <?php if ($message): ?>
      <div class="mb-4 p-4 rounded-xl <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>
    <form method="post" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Họ tên</label>
        <input type="text" name="full_name" class="border rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-200" value="<?php echo htmlspecialchars($full_name); ?>" required />
      </div>
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
        <input type="email" class="border rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-200" value="<?php echo htmlspecialchars($email); ?>" readonly />
      </div>
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Số điện thoại</label>
        <input type="text" name="phone" class="border rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-200" value="<?php echo htmlspecialchars($phone); ?>" required />
      </div>
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Ngày sinh</label>
        <input type="date" name="date_of_birth" class="border rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-200" value="<?php echo htmlspecialchars($date_of_birth); ?>" />
      </div>
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Giới tính</label>
        <select name="gender" class="border rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-200">
          <option value="Nam" <?php echo $gender === 'Nam' ? 'selected' : ''; ?>>Nam</option>
          <option value="Nữ" <?php echo $gender === 'Nữ' ? 'selected' : ''; ?>>Nữ</option>
          <option value="Khác" <?php echo $gender === 'Khác' ? 'selected' : ''; ?>>Khác</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Địa chỉ</label>
        <input type="text" name="address" class="border rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-200" value="<?php echo htmlspecialchars($address); ?>" />
      </div>
      <div class="md:col-span-2">
        <button type="submit" class="btn-orange hover:bg-orange-600 text-white px-8 py-3 rounded-xl font-bold shadow transition w-fit">Lưu thay đổi</button>
      </div>
    </form>
  </div>
</div>
