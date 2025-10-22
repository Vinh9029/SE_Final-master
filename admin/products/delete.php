<?php
include_once '../../database/db_connection.php';

// Create logs directory if not exists
$logDir = '../../logs/';
if (!file_exists($logDir)) {
    mkdir($logDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
    
    if ($product_id > 0) {
        // Get product info to delete image
        $stmt = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
        
        if ($product) {
            // Delete image if exists
            if ($product['image'] && file_exists('../../' . $product['image'])) {
                if (!unlink('../../' . $product['image'])) {
                    $logMessage = date('Y-m-d H:i:s') . ' Old image unlink error for product_id: ' . $product_id . ' | Image: ' . $product['image'] . PHP_EOL;
                    file_put_contents('../../logs/crud_errors.log', $logMessage, FILE_APPEND | LOCK_EX);
                }
            }
            
            // Delete product
            $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
            $stmt->bind_param("i", $product_id);
            if ($stmt->execute()) {
                $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Xóa sản phẩm thành công!', 'redirect' => 'products/list.php']);
                    exit;
                } else {
                    header('Location: list.php?success=deleted');
                    exit;
                }
            } else {
                // Log error
                $logMessage = date('Y-m-d H:i:s') . ' Delete product error: ' . $conn->error . ' | product_id: ' . $product_id . PHP_EOL;
                file_put_contents('../../logs/crud_errors.log', $logMessage, FILE_APPEND | LOCK_EX);
                $error = "Lỗi xóa sản phẩm: " . $conn->error;
            }
            $stmt->close();
        } else {
            $error = "Sản phẩm không tồn tại.";
        }
        
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        if ($isAjax && isset($error)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $error]);
            exit;
        }
    } else {
        $error = "ID sản phẩm không hợp lệ.";
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $error]);
            exit;
        }
    }
} else {
    // For GET, show confirmation (but since AJAX loads, perhaps redirect or error)
    if (isset($_GET['id'])) {
        $product_id = (int)$_GET['id'];
        // Fetch product name for confirmation
        $stmt = $conn->prepare("SELECT name FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
        
        if ($product) {
            ?>
            <div class="max-w-md mx-auto py-10">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Xác nhận xóa</h2>
                <p class="text-gray-600 mb-6">Bạn có chắc chắn muốn xóa sản phẩm "<strong><?= htmlspecialchars($product['name']) ?></strong>"?</p>
                <form method="post" class="space-y-4">
                    <input type="hidden" name="product_id" value="<?= $product_id ?>">
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg font-semibold">Xóa</button>
                    <a href="#" data-page="products/list.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-semibold">Hủy</a>
                </form>
            </div>
            <?php
        } else {
            header('Location: list.php?error=invalid');
            exit;
        }
    } else {
        header('Location: list.php?error=invalid');
        exit;
    }
}
?>
