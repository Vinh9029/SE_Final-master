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
                // Respond with JSON for AJAX request
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Xóa sản phẩm thành công!', 'redirect' => 'products/list.php']);
                exit;
            } else {
                // Log error
                $logMessage = date('Y-m-d H:i:s') . ' Delete product error: ' . $conn->error . ' | product_id: ' . $product_id . PHP_EOL;
                file_put_contents('../../logs/crud_errors.log', $logMessage, FILE_APPEND | LOCK_EX);
                $error = "Lỗi xóa sản phẩm: " . $conn->error;
            }
            $stmt->close();
        } else {
            $error = "Sản phẩm không tồn tại hoặc đã bị xóa.";
        }
    } else {
        $error = "ID sản phẩm không hợp lệ.";
    }
    // If we reach here, there was an error. Send JSON error response.
    header('Content-Type: application/json');
    // Set a 400 Bad Request status code for errors
    http_response_code(400); 
    echo json_encode(['success' => false, 'message' => $error]);
    exit;
} else {
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
            <div class="max-w-lg mx-auto py-10 flex items-center justify-center h-full">
                <div class="bg-white rounded-2xl shadow-2xl p-8 text-center">
                    <div class="text-red-500 mb-4"><i class="fas fa-exclamation-triangle fa-3x"></i></div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Xác nhận xóa</h2>
                    <p class="text-gray-600 mb-6">Bạn có chắc chắn muốn xóa vĩnh viễn sản phẩm <br>"<strong class="text-red-600"><?= htmlspecialchars($product['name']) ?></strong>"?<br>Hành động này không thể hoàn tác.</p>
                    <form method="post" action="products/delete.php?id=<?= $product_id ?>" class="flex justify-center gap-4">
                    <input type="hidden" name="product_id" value="<?= $product_id ?>">
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-8 py-2 rounded-lg font-semibold shadow-md transition">Xác nhận Xóa</button>
                        <a href="#" data-page="products/list.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-8 py-2 rounded-lg font-semibold shadow-md transition">Hủy</a>
                    </form>
                </div>
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
