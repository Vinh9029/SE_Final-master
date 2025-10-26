<?php
// Handler for getting mini-favourites for header dropdown
include_once __DIR__ . '/../../../config.php';
include_once __DIR__ . '/../../../database/db_connection.php';
include_once __DIR__ . '/../../../menus/helper.php'; // Include slug generator
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo '<div class="p-4 text-center text-gray-500">Please log in to see your favourites.</div>';
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT p.product_id, p.name, p.price, p.image
        FROM favourites f
        JOIN products p ON f.product_id = p.product_id
        WHERE f.customer_id = ?
        ORDER BY f.added_at DESC
        LIMIT 5";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="p-4 text-center text-gray-500">Your wishlist is empty.</div>';
    echo '<div class="p-2 text-center border-t"><a href="' . $base_url . '/customer/favourites.php" class="text-pink-600 hover:underline">View all</a></div>';
    exit;
}

echo '<div class="py-2 px-4 font-bold text-pink-600 border-b">My Wishlist</div>';
while ($row = $result->fetch_assoc()) {
    echo '<a href="' . $base_url . '/menus/product.php?slug=' . generateSlug($row['name']) . '" class="flex items-center gap-3 px-4 py-2 hover:bg-pink-50 transition">';
    echo '    <img src="' . $base_url . '/' . $row['image'] . '" alt="' . htmlspecialchars($row['name']) . '" class="w-12 h-12 object-cover rounded shadow border border-pink-100" />';
    echo '    <div class="flex-1">';
    echo '        <div class="font-bold text-pink-600">' . htmlspecialchars($row['name']) . '</div>';
    echo '        <div class="text-orange-600 font-semibold text-sm">' . number_format($row['price'], 0, ',', '.') . ' VNĐ</div>';
    echo '    </div>';
    // Maybe add a remove button here later
    echo '</a>';
}
echo '<div class="p-2 text-center border-t"><a href="' . $base_url . '/customer/favourites.php" class="text-pink-600 hover:underline font-bold">View all favourites</a></div>';

$stmt->close();
$conn->close();
?>