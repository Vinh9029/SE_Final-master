<?php
// Handler for removing a favourite item
include_once __DIR__ . '/../../../config.php';
include_once __DIR__ . '/../../../database/db_connection.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Please log in to manage your wishlist.']);
    exit;
}

if (!isset($_POST['product_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Product ID is missing.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = (int)$_POST['product_id'];

$sql = "DELETE FROM favourites WHERE customer_id = ? AND product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $user_id, $product_id);

if ($stmt->execute()) {
    // Get new count
    $count_sql = "SELECT COUNT(*) AS total FROM favourites WHERE customer_id = ?";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param('i', $user_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result()->fetch_assoc();
    $new_count = $count_result['total'] ?? 0;

    echo json_encode(['success' => true, 'message' => 'Removed from wishlist.', 'favourite_count' => $new_count]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not remove from wishlist.']);
}

$stmt->close();
$conn->close();
?>