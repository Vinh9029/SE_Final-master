<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../../database/db_connection.php';

// Lấy tổng doanh thu trong ngày hôm nay
$stmt = $conn->prepare("SELECT SUM(total) as daily_revenue FROM orders WHERE status = 'completed' AND DATE(order_date) = CURDATE()");
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

$daily_revenue = $result['daily_revenue'] ?? 0;

$response = [
    'labels' => ['Doanh thu hôm nay'],
    'data' => [$daily_revenue]
];

echo json_encode($response);
?>