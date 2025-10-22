<?php
require_once __DIR__ . '/../database/db_connection.php';
require_once __DIR__ . '/helper.php';
header('Content-Type: application/json; charset=utf-8');
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($q === '') {
    echo json_encode([]); exit;
}
$q_noaccent = mb_strtolower(removeVietnameseAccents($q));
$q_lower = mb_strtolower($q);
$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.category_id WHERE p.name LIKE CONCAT('%', ?, '%') OR p.description LIKE CONCAT('%', ?, '%') LIMIT 30");
$stmt->bind_param('ss', $q, $q);
$stmt->execute();
$res = $stmt->get_result();
$results = [];
while ($row = $res->fetch_assoc()) {
    $name_noaccent = mb_strtolower(removeVietnameseAccents($row['name']));
    $desc_noaccent = mb_strtolower(removeVietnameseAccents($row['description']));
    $name_lower = mb_strtolower($row['name']);
    $desc_lower = mb_strtolower($row['description']);
    if (
        mb_stripos($name_lower, $q_lower) !== false ||
        mb_stripos($desc_lower, $q_lower) !== false ||
        mb_stripos($name_noaccent, $q_noaccent) !== false ||
        mb_stripos($desc_noaccent, $q_noaccent) !== false
    ) {
        $results[] = [
            'name' => $row['name'],
            'image' => $row['image'] ?: '../Photos/placeholder.png',
            'price' => number_format($row['price'], 0, ',', '.') . ' đ',
            'slug' => generateSlug($row['name']),
            'category_slug' => generateSlug($row['category_name'])
        ];
    }
}
echo json_encode(array_slice($results, 0, 10));
