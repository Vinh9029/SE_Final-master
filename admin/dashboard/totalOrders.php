<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../../database/db_connection.php';

// Lấy tổng số đơn hàng trong ngày hôm nay
$stmt = $conn->prepare("SELECT COUNT(*) as daily_orders FROM orders WHERE DATE(order_date) = CURDATE()");
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

$daily_orders = $result['daily_orders'] ?? 0;

$response = [
    'labels' => ['Đơn hàng hôm nay'],
    'data' => [$daily_orders]
];

echo json_encode($response);
?>