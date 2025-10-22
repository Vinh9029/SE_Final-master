<?php
include_once '../../database/db_connection.php';

// Create logs directory if not exists
$logDir = '../../logs/';
if (!file_exists($logDir)) {
    mkdir($logDir, 0777, true);
}

$categories = $conn->query("SELECT * FROM categories");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $is_signature = isset($_POST['is_signature']) ? 1 : 0;
    $image_path = null;

  if (empty($name) || $price <= 0 || $category_id <= 0) {
    $error = "Vui lòng điền đầy đủ thông tin hợp lệ.";
  } else {
        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($file_extension, $allowed_types)) {
                $error = "Chỉ chấp nhận file JPG, JPEG, PNG, GIF.";
            } elseif ($_FILES['image']['size'] > $max_size) {
                $error = "File quá lớn. Tối đa 5MB.";
            } else {
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
                    $logMessage = date('Y-m-d H:i:s') . ' Image upload error: ' . $_FILES['image']['error'] . ' for product: ' . $name . PHP_EOL;
                    file_put_contents('../../logs/crud_errors.log', $logMessage, FILE_APPEND | LOCK_EX);
                    $error = "Lỗi tải lên hình ảnh.";
                }
            }
        }

        if (!isset($error)) {
      $stmt = $conn->prepare("INSERT INTO products (name, description, category_id, price, is_signature, image, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
      $stmt->bind_param("ssidis", $name, $description, $category_id, $price, $is_signature, $image_path);
      if ($stmt->execute()) {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        if ($isAjax) {
          header('Content-Type: application/json');
          echo json_encode(['success' => true, 'message' => 'Thêm sản phẩm thành công!', 'redirect' => 'products/list.php']);
          exit;
        } else {
          header('Location: list.php?success=added');
          exit;
        }
      } else {
        // Log error
        $logMessage = date('Y-m-d H:i:s') . ' Add product error: ' . $conn->error . ' | Name: ' . $name . PHP_EOL;
        file_put_contents('../../logs/crud_errors.log', $logMessage, FILE_APPEND | LOCK_EX);
        $error = "Lỗi thêm sản phẩm: " . $conn->error;
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
  <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2"><i class="fa fa-plus text-orange-500"></i> Thêm sản phẩm mới</h1>
  <?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>
  <form method="post" action="products/add.php" enctype="multipart/form-data" class="bg-white rounded-2xl shadow p-8 flex flex-col gap-6">
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Tên sản phẩm</label>
      <input type="text" name="name" class="border rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-orange-200" placeholder="Nhập tên sản phẩm" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required />
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Mô tả</label>
      <textarea name="description" class="border rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-orange-200" placeholder="Nhập mô tả sản phẩm" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Giá</label>
      <input type="number" name="price" step="0.01" class="border rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-orange-200" placeholder="Nhập giá" value="<?= $_POST['price'] ?? '' ?>" required />
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Loại sản phẩm</label>
      <select name="category_id" class="border rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-orange-200" required>
        <option value="">Chọn danh mục</option>
        <?php $categories->data_seek(0); while ($cat = $categories->fetch_assoc()): ?>
          <option value="<?= $cat['category_id'] ?>" <?= (isset($_POST['category_id']) && $_POST['category_id'] == $cat['category_id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Hình ảnh sản phẩm</label>
      <input type="file" name="image" accept="image/*" class="border rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-orange-200" />
    </div>
    <div>
      <label class="flex items-center">
        <input type="checkbox" name="is_signature" value="1" <?= isset($_POST['is_signature']) ? 'checked' : '' ?> class="mr-2">
        <span class="text-sm font-semibold text-gray-700">Sản phẩm signature</span>
      </label>
    </div>
    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-8 py-3 rounded-xl font-bold shadow transition w-fit flex items-center gap-2"><i class="fa fa-save"></i> Lưu sản phẩm</button>
    <a href="#" data-page="products/list.php" class="text-pink-600 hover:underline font-semibold">Quay lại danh sách</a>
  </form>
</div>
