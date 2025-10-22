<?php
session_start();
include_once __DIR__ . '/../database/db_connection.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ./login/index.php');
    exit;
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT full_name, email FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($full_name, $email);
$stmt->fetch();
$stmt->close();
?>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tài khoản khách hàng | Old Favour Coffee</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<?php include '../includes/header.php'; ?>

<main class="bg-gradient-to-br from-pink-50 via-yellow-50 to-white min-h-screen py-10">
  <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-12 gap-8">
    <!-- Sidebar -->
    <aside class="md:col-span-3 col-span-12 bg-white rounded-3xl shadow-2xl p-6 flex flex-col items-center sticky top-24 h-fit">
      <?php include 'avatar.php'; ?>
      <div class="font-extrabold text-xl text-gray-800 mb-1"><?php echo htmlspecialchars($full_name); ?></div>
      <div class="text-gray-500 text-sm mb-2"><?php echo htmlspecialchars($email); ?></div>

      <!-- <?php include 'loyalty-point.php'; ?> -->

      <nav class="w-full mt-4">
        <ul class="flex flex-col gap-2">
          <li><a href="#" data-page="profile.php" class="block px-4 py-2 rounded-xl font-semibold text-gray-700 hover:bg-pink-100 transition flex items-center group"><i class="fa fa-user mr-2 text-pink-500"></i> Thông tin cá nhân</a></li>
          <li><a href="#" data-page="orders.php" class="block px-4 py-2 rounded-xl font-semibold text-gray-700 hover:bg-orange-100 transition flex items-center group"><i class="fa fa-box mr-2 text-orange-500"></i> Đơn hàng</a></li>
          <li><a href="#" data-page="vouchers.php" class="block px-4 py-2 rounded-xl font-semibold text-gray-700 hover:bg-green-100 transition flex items-center group"><i class="fa fa-ticket-alt mr-2 text-green-500"></i> Voucher của tôi</a></li>
          <li><a href="#" data-page="settings.php" class="block px-4 py-2 rounded-xl font-semibold text-gray-700 hover:bg-yellow-100 transition flex items-center group"><i class="fa fa-cog mr-2 text-yellow-500"></i> Cài đặt tài khoản</a></li>
          <li><a href="logout.php" class="block px-4 py-2 rounded-xl font-semibold text-gray-700 hover:bg-red-100 transition flex items-center group"><i class="fa fa-sign-out-alt mr-2 text-red-500"></i> Đăng xuất</a></li>
        </ul>
      </nav>
    </aside>

    <!-- Nội dung chính -->
    <section class="md:col-span-9 col-span-12 bg-white rounded-3xl shadow-2xl p-8 min-h-[500px]" id="account-content">
      <div class="flex flex-col items-center justify-center h-full"> 
        <div class="animate-pulse w-24 h-24 bg-pink-100 rounded-full mb-6"></div>
        <div class="text-center text-gray-400 mt-10">
          <i class="fa fa-info-circle text-4xl mb-4"></i>
          <div class="font-bold text-lg">Chọn mục bên trái để xem chi tiết tài khoản</div>
        </div>
      </div>
    </section>
  </div>
</main>

<?php include '../includes/footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Khi load account.php có tham số ?page=...
$(document).ready(function() {
  var page = "<?php echo isset($_GET['page']) ? $_GET['page'] : ''; ?>";
  if (page) {
    $("a[data-page='" + page + ".php']").trigger("click");
  }
});

// AJAX load nội dung khi click
$(document).on("click", "a[data-page]", function(e) {
    e.preventDefault();
    let page = $(this).data("page");
    let content = $("#account-content");

    // Loading effect
    content.html('<div class="flex flex-col items-center justify-center h-full"><div class="animate-pulse w-24 h-24 bg-pink-100 rounded-full mb-6"></div><div class="text-center text-gray-400 mt-10"><i class="fa fa-spinner fa-spin text-4xl mb-4"></i><div class="font-bold text-lg">Đang tải...</div></div></div>');

    // Fetch content
    fetch(page)
      .then(res => res.text())
      .then(html => {
        setTimeout(() => { content.html(html); }, 400);
        window.scrollTo({ top: content.offset().top - 80, behavior: 'smooth' });
      });
});
</script>
