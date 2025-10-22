<?php
include_once '../../database/db_connection.php';

// Create logs directory if not exists
$logDir = '../../logs/';
if (!file_exists($logDir)) {
    mkdir($logDir, 0777, true);
}

$product = null;
$categories = $conn->query("SELECT * FROM categories");

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.product_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    if (!$product) {
        header('Location: list.php?error=invalid');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $product) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $is_signature = isset($_POST['is_signature']) ? 1 : 0;
    $image_path = $product['image'];

  if (empty($name) || $price <= 0 || $category_id <= 0) {
    $error = "Vui lòng điền đầy đủ thông tin hợp lệ.";
  } else {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($file_extension, $allowed_types)) {
                $error = "Chỉ chấp nhận file JPG, JPEG, PNG, GIF.";
            } elseif ($_FILES['image']['size'] > $max_size) {
                $error = "File quá lớn. Tối đa 5MB.";
            } else {
                // Delete old image if exists
                if ($product['image'] && file_exists('../../' . $product['image'])) {
                    if (!unlink('../../' . $product['image'])) {
                        $logMessage = date('Y-m-d H:i:s') . ' Old image unlink error for product_id: ' . $product['product_id'] . ' | Image: ' . $product['image'] . PHP_EOL;
                        file_put_contents('../../logs/crud_errors.log', $logMessage, FILE_APPEND | LOCK_EX);
                    }
                }
                $upload_dir = '../../Photos/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $file_name = 'product_' . time() . '.' . $file_extension;
                $target_file = $upload_dir . $file_name;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_path = 'Photos/' . $file_name;
                } else {
                    // Log upload error
                    $logMessage = date('Y-m-d H:i:s') . ' Image upload error: ' . $_FILES['image']['error'] . ' for product_id: ' . $product['product_id'] . PHP_EOL;
                    file_put_contents('../../logs/crud_errors.log', $logMessage, FILE_APPEND | LOCK_EX);
                    $error = "Lỗi tải lên hình ảnh.";
                }
            }
        }

        if (!isset($error)) {
      $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, category_id = ?, price = ?, is_signature = ?, image = ? WHERE product_id = ?");
      $stmt->bind_param("ssidisi", $name, $description, $category_id, $price, $is_signature, $image_path, $product['product_id']);
      if ($stmt->execute()) {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        if ($isAjax) {
          header('Content-Type: application/json');
          echo json_encode(['success' => true, 'message' => 'Cập nhật sản phẩm thành công!', 'redirect' => 'products/list.php']);
          exit;
        } else {
          header('Location: list.php?success=updated');
          exit;
        }
      } else {
        // Log error
        $logMessage = date('Y-m-d H:i:s') . ' Update product error: ' . $conn->error . ' | product_id: ' . $product['product_id'] . ' | Name: ' . $name . PHP_EOL;
        file_put_contents('../../logs/crud_errors.log', $logMessage, FILE_APPEND | LOCK_EX);
        $error = "Lỗi cập nhật sản phẩm: " . $conn->error;
      }
      $stmt->close();
        }
    }

    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    if ($isAjax && isset($error)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $error]);
    exit;
  } elseif ($isAjax) {
    // Nếu không có lỗi nhưng không phải submit (ví dụ: load lại form), trả về JSON rỗng
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ hoặc thiếu thông tin.']);
    exit;
    }
}
?>
<div class="max-w-xl mx-auto py-10">
  <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2"><i class="fa fa-edit text-orange-500"></i> Sửa sản phẩm</h1>
  <?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>
  <?php if ($product): ?>
  <form method="post" action="products/edit.php?id=<?= $product['product_id'] ?>" enctype="multipart/form-data" class="bg-white rounded-2xl shadow p-8 flex flex-col gap-6">
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Tên sản phẩm</label>
      <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" class="border rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-orange-200" required />
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Mô tả</label>
      <textarea name="description" class="border rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-orange-200" rows="3"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Giá</label>
      <input type="number" name="price" step="0.01" value="<?= $product['price'] ?>" class="border rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-orange-200" required />
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Loại sản phẩm</label>
      <select name="category_id" class="border rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-orange-200" required>
        <option value="">Chọn danh mục</option>
        <?php $categories->data_seek(0); while ($cat = $categories->fetch_assoc()): ?>
          <option value="<?= $cat['category_id'] ?>" <?= $cat['category_id'] == $product['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Hình ảnh sản phẩm</label>
      <input type="file" name="image" accept="image/*" class="border rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-orange-200" />
      <?php if ($product['image']): ?>
        <img src="../../<?= htmlspecialchars($product['image']) ?>" class="h-16 w-16 object-cover rounded shadow mt-2" alt="Current image" />
        <p class="text-sm text-gray-500 mt-1">Hình ảnh hiện tại. Tải lên hình mới để thay thế.</p>
      <?php endif; ?>
    </div>
    <div>
      <label class="flex items-center">
        <input type="checkbox" name="is_signature" value="1" <?= $product['is_signature'] ? 'checked' : '' ?> class="mr-2">
        <span class="text-sm font-semibold text-gray-700">Sản phẩm signature</span>
      </label>
    </div>
    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-8 py-3 rounded-xl font-bold shadow transition w-fit flex items-center gap-2"><i class="fa fa-save"></i> Lưu thay đổi</button>
    <a href="#" data-page="products/list.php" class="text-pink-600 hover:underline font-semibold">Quay lại danh sách</a>
  </form>
  <?php else: ?>
    <p class="text-red-500">Sản phẩm không tồn tại.</p>
    <a href="#" data-page="products/list.php" class="text-pink-600 hover:underline font-semibold">Quay lại danh sách</a>
  <?php endif; ?>
</div>
