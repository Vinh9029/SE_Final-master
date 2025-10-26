<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<?php
include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../includes/header.php';
include_once __DIR__ . '/../database/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_url . "/login/index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all notifications
$sql = "SELECT id, title, body, url, is_read, created_at, type
        FROM notifications
        WHERE customer_id = ?
        ORDER BY created_at DESC
        LIMIT 50"; // Limit to last 50 notifications on the main page

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Mark all as read on page load
$update_sql = "UPDATE notifications SET is_read = 1 WHERE customer_id = ? AND is_read = 0";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param('i', $user_id);
$update_stmt->execute();
$update_stmt->close();

?>

<div class="container mx-auto my-10 p-6 bg-white rounded-lg shadow-md">
    <h1 class="text-3xl font-bold text-pink-600 mb-6">Notifications</h1>

    <div class="space-y-4">
        <?php if (count($notifications) === 0): ?>
            <div class="text-center text-gray-500 py-10">
                <i class="fa fa-bell-slash fa-3x text-gray-300"></i>
                <p class="mt-4">You have no notifications yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notif): ?>
                <?php
                    $url = $notif['url'] ? ($base_url . $notif['url']) : '#';
                    $icon = 'fa-info-circle'; // default icon
                    switch ($notif['type']) {
                        case 'order_status':
                            $icon = 'fa-box';
                            break;
                        case 'welcome':
                            $icon = 'fa-hand-sparkles';
                            break;
                        case 'loyalty_point':
                            $icon = 'fa-star';
                            break;
                    }
                ?>
                <div class="notification-item border rounded-lg p-4 flex items-start gap-4 hover:bg-gray-50 transition" data-notification-id="<?php echo $notif['id']; ?>">
                    <div class="text-pink-500 text-xl mt-1">
                        <i class="fa <?php echo $icon; ?>"></i>
                    </div>
                    <div class="flex-1">
                        <a href="<?php echo $url; ?>" class="stretched-link">
                            <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($notif['title']); ?></h3>
                        </a>
                        <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($notif['body']); ?></p>
                        <span class="text-xs text-gray-400 mt-2 block"><?php echo date("F j, Y, g:i a", strtotime($notif['created_at'])); ?></span>
                    </div>
                    <button class="btn-delete-notification text-gray-400 hover:text-red-500 transition" title="Delete Notification">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-delete-notification').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Prevent link navigation

            if (!confirm('Are you sure you want to delete this notification?')) {
                return;
            }

            const item = this.closest('.notification-item');
            const notificationId = item.dataset.notificationId;

            const formData = new FormData();
            formData.append('id', notificationId);

            fetch('<?php echo $base_url; ?>/includes/handlers/notification/deleteNotification.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    item.style.transition = 'opacity 0.5s';
                    item.style.opacity = '0';
                    setTimeout(() => item.remove(), 500);
                } else {
                    alert(data.message || 'Could not delete notification.');
                }
            });
        });
    });
});
</script>

<?php
include_once __DIR__ . '/../includes/footer.php';
$conn->close();
?>