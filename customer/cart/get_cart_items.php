qod<?php
session_start();
include_once __DIR__ . '/../../database/db_connection.php';
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}
$user_id = $_SESSION['user_id'];
// Lấy sản phẩm trong giỏ hàng
$sql = "SELECT ci.cart_item_id, ci.product_id, ci.quantity, p.name, p.price, p.image, ps.size_name, ps.extra_price
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        LEFT JOIN product_sizes ps ON ci.product_id = ps.product_id AND ci.size_id = ps.size_id
        WHERE ci.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
ob_start();
while ($row = $result->fetch_assoc()):
    $img = $row['image'] ? '../../Photos/' . $row['image'] : '../../Photos/default.jpg';
    $size = $row['size_name'] ? $row['size_name'] : '';
    $base_price = (float)$row['price'];
    $extra_price = (float)$row['extra_price'];
    $price = $base_price + $extra_price;
    $subtotal = $price * $row['quantity'];
?>
<div class="cart-item cart-item-anim grid grid-cols-12 items-center gap-2 p-4 bg-gray-50 rounded-2xl" data-product-id="<?= $row['product_id'] ?>" data-cart-item-id="<?= $row['cart_item_id'] ?>" data-size-id="<?= $row['size_id'] ?? '' ?>">
    <div class="col-span-5 flex gap-3 items-center">
        <img src="<?= $img ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="w-16 h-16 object-cover rounded-xl shadow-md">
        <div>
            <h3 class="font-bold text-base text-gray-800"><?= htmlspecialchars($row['name']) ?><?= $size ? ' - ' . htmlspecialchars($size) : '' ?></h3>
            <input type="text" class="note-input mt-1 w-full border border-gray-300 rounded px-2 py-1 text-xs" placeholder="Ghi chú cho món này (ví dụ: Ít đá, Không đường, Thêm sữa...)">
        </div>
    </div>
    <div class="col-span-2 text-center">
        <span class="text-orange-600 font-bold"><?= number_format($price, 0, ',', '.') ?>đ</span>
    </div>
    <div class="col-span-2 flex justify-center items-center gap-2">
        <button class="quantity-btn bg-gray-200 hover:bg-gray-300 w-7 h-7 rounded-full flex items-center justify-center transition-colors"><i class="fas fa-minus text-xs"></i></button>
        <input type="number" value="<?= $row['quantity'] ?>" class="quantity-input w-12 text-center border-2 border-gray-300 rounded-lg py-1 text-sm" readonly>
        <button class="quantity-btn bg-gray-200 hover:bg-gray-300 w-7 h-7 rounded-full flex items-center justify-center transition-colors"><i class="fas fa-plus text-xs"></i></button>
    </div>
    <div class="col-span-2 text-center">
        <span class="text-orange-600 font-bold subtotal" data-price="<?= $price ?>"><?= number_format($subtotal, 0, ',', '.') ?>đ</span>
    </div>
    <div class="col-span-1 text-center">
        <button class="remove-btn text-red-500 hover:text-red-700" title="Xóa"><i class="fas fa-trash"></i></button>
    </div>
</div>
<?php endwhile;
$html = ob_get_clean();
echo json_encode(['success' => true, 'html' => $html]);
?>
