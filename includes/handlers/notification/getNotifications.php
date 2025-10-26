<?php
// Handler for getting notifications
include_once __DIR__ . '/../../../config.php';
include_once __DIR__ . '/../../../database/db_connection.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo '<div class="p-4 text-center text-gray-500">Please log in to see your notifications.</div>';
    exit;
}

$user_id = $_SESSION['user_id'];
$is_mini = isset($_GET['mini']) && $_GET['mini'] == 'true';
$limit = $is_mini ? 5 : 20; // 5 for dropdown, 20 for full page

$sql = "SELECT id, title, body, url, is_read, created_at, type
        FROM notifications
        WHERE customer_id = ?
        ORDER BY created_at DESC
        LIMIT ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $user_id, $limit);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="p-4 text-center text-gray-500">You have no notifications.</div>';
    if ($is_mini) {
        echo '<div class="p-2 text-center border-t"><a href="' . $base_url . '/customer/notifications.php" class="text-pink-600 hover:underline">View all</a></div>';
    }
    exit;
}

if ($is_mini) {
    echo '<div class="py-2 px-4 font-bold text-pink-600 border-b flex justify-between items-center"><span>Notifications</span> <a href="#" class="text-sm font-normal text-blue-500 hover:underline" id="mark-all-read">Mark all as read</a></div>';
}

while ($row = $result->fetch_assoc()) {
    $url = $row['url'] ? ($base_url . $row['url']) : '#';
    $font_weight = $row['is_read'] ? 'font-normal' : 'font-bold';
    $bg_color = $row['is_read'] ? 'bg-white' : 'bg-pink-50';

    echo '<a href="' . $url . '" data-id="' . $row['id'] . '" class="notification-item block px-4 py-3 hover:bg-gray-100 transition ' . $bg_color . '">';
    echo '    <div class="' . $font_weight . ' text-gray-800">' . htmlspecialchars($row['title']) . '</div>';
    echo '    <div class="text-sm text-gray-600">' . htmlspecialchars($row['body']) . '</div>';
    echo '    <div class="text-xs text-gray-400 mt-1">' . date("d/m/Y H:i", strtotime($row['created_at'])) . '</div>';
    echo '</a>';
}

if ($is_mini) {
    echo '<div class="p-2 text-center border-t"><a href="' . $base_url . '/customer/notifications.php" class="text-pink-600 hover:underline font-bold">View all notifications</a></div>';
}

// Add script for marking as read
if ($is_mini) {
    echo <<<HTML
    <script>
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function(e) {
            const notifId = this.dataset.id;
            fetch(`<?php echo $base_url; ?>/includes/handlers/notification/markAsRead.php?id=${notifId}`);
            // No need to prevent default, let the user navigate
        });
    });

    const markAllReadBtn = document.getElementById('mark-all-read');
    if(markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            fetch(`<?php echo $base_url; ?>/includes/handlers/notification/markAsRead.php?all=true`)
                .then(() => {
                    // Visually mark all as read
                    document.querySelectorAll('.notification-item').forEach(item => {
                        item.style.fontWeight = 'normal';
                        item.style.backgroundColor = 'white';
                    });
                    const badge = document.getElementById('notification-badge');
                    if (badge) badge.style.display = 'none';
                });
        });
    }
    </script>
HTML;
}


$stmt->close();
$conn->close();
?>