<?php
include_once '../../database/db_connection.php';

// Lấy danh mục cho filter
$catResult = $conn->query("SELECT name FROM categories");
$catList = [];
while ($row = $catResult->fetch_assoc()) {
    $catList[] = $row['name'];
}
$cat = isset($_GET['cat']) ? $_GET['cat'] : '';
$catFilter = $cat ? " AND c.name = '" . $conn->real_escape_string($cat) . "'" : '';

$search = isset($_GET['search']) ? $conn->real_escape_string(trim($_GET['search'])) : '';
$searchFilter = $search ? " AND (p.name LIKE '%$search%' OR CAST(p.price AS CHAR) LIKE '%$search%')" : '';

// PHÂN TRANG
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$sqlCount = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE 1=1 $catFilter $searchFilter";
$total = $conn->query($sqlCount)->fetch_row()[0];
$totalPages = ceil($total / $limit);

$sql = "SELECT p.product_id, p.name, c.name AS category, p.price, p.is_signature, p.created_at FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE 1=1 $catFilter $searchFilter ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>
<style>
  th.sortable { cursor: pointer; user-select: none; }
  th.sortable:hover { background: #ffe4b5; }
  th.sorted-asc:after { content: ' \25B2'; color: #fc466b; }
  th.sorted-desc:after { content: ' \25BC'; color: #fc466b; }
  .fade-in { animation: fadeIn 0.5s ease-in; }
  @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>
<div class="p-6">
  <div class="flex justify-between items-center mb-8">
    <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2"><i class="fa fa-coffee text-pink-500"></i> Danh sách sản phẩm</h1>
    <a href="#" data-page="products/add.php" class="bg-pink-600 hover:bg-pink-700 text-white px-6 py-2 rounded-xl font-bold shadow transition flex items-center gap-2"><i class="fa fa-plus"></i> Thêm sản phẩm</a>
  </div>
    <div class="bg-white rounded-xl shadow p-8">
      <h2 class="text-2xl font-bold mb-6 text-orange-600 flex items-center gap-2"><i class="fa fa-coffee"></i> Danh sách sản phẩm</h2>
      <?php
      $message = '';
      $msgType = '';
      if (isset($_GET['success'])) {
          switch ($_GET['success']) {
              case 'added': $message = 'Thêm sản phẩm thành công!'; $msgType = 'success'; break;
              case 'updated': $message = 'Cập nhật sản phẩm thành công!'; $msgType = 'success'; break;
              case 'deleted': $message = 'Xóa sản phẩm thành công!'; $msgType = 'success'; break;
          }
      } elseif (isset($_GET['error'])) {
          switch ($_GET['error']) {
              case 'invalid': $message = 'Sản phẩm không tồn tại.'; $msgType = 'error'; break;
          }
      }
      if ($message): ?>
        <div class="bg-<?= $msgType == 'success' ? 'green' : 'red' ?>-100 border border-<?= $msgType == 'success' ? 'green' : 'red' ?>-400 text-<?= $msgType == 'success' ? 'green' : 'red' ?>-700 px-4 py-3 rounded mb-4">
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>
      <div class="mb-4 flex items-center gap-2">
        <select name="cat" class="border rounded px-2 py-1">
          <option value="">Tất cả danh mục</option>
          <?php foreach($catList as $catName): ?>
            <option value="<?= htmlspecialchars($catName) ?>" <?= $cat == $catName ? 'selected' : '' ?>><?= htmlspecialchars($catName) ?></option>
          <?php endforeach; ?>
        </select>
        <input type="text" id="searchInput" placeholder="Tìm kiếm sản phẩm..." value="<?= htmlspecialchars($search) ?>" class="border rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-orange-200">
        <button id="clearSearch" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold"><i class="fa fa-times"></i></button>
      </div>
    <table id="productTable" class="min-w-full table-auto border-collapse">
      <thead>
        <tr class="bg-orange-100 text-orange-700">
          <th class="px-4 py-2 sortable" data-sort="product_id">ID</th>
          <th class="px-4 py-2 sortable" data-sort="name">Tên sản phẩm</th>
          <th class="px-4 py-2 sortable" data-sort="category">Danh mục</th>
          <th class="px-4 py-2 sortable" data-sort="price">Giá</th>
          <th class="px-4 py-2 sortable" data-sort="is_signature">Signature</th>
          <th class="px-4 py-2 sortable" data-sort="created_at">Ngày tạo</th>
          <th class="px-4 py-2">Sửa</th>
          <th class="px-4 py-2">Xóa</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr class="border-b fade-in">
          <td class="px-4 py-2" data-value="<?= $row['product_id'] ?>"><?= $row['product_id'] ?></td>
          <td class="px-4 py-2" data-value="<?= htmlspecialchars($row['name']) ?>"><?= htmlspecialchars($row['name']) ?></td>
          <td class="px-4 py-2" data-value="<?= htmlspecialchars($row['category']) ?>"><?= htmlspecialchars($row['category']) ?></td>
          <td class="px-4 py-2" data-value="<?= $row['price'] ?>"><?= number_format($row['price'], 0, ',', '.') ?>đ</td>
          <td class="px-4 py-2" data-value="<?= $row['is_signature'] ?>"><?= $row['is_signature'] ? '<span class="text-orange-600 font-bold">★</span>' : '' ?></td>
          <td class="px-4 py-2" data-value="<?= $row['created_at'] ?>"><?= $row['created_at'] ?></td>
          <td class="px-4 py-2"><a href="#" data-page="products/edit.php?id=<?= $row['product_id'] ?>" class="text-orange-500 hover:underline">Sửa</a></td>
          <td class="px-4 py-2"><a href="#" data-page="products/delete.php?id=<?= $row['product_id'] ?>" class="text-red-500 hover:underline">Xóa</a></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
      <!-- Pagination of slicing products  -->
    </table>
    <div class="mt-4 flex gap-2">
      <?php for($i=1;$i<=$totalPages;$i++): ?>
        <a href="#" data-page="products/list.php?page=<?= $i ?>&cat=<?= urlencode($cat) ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 rounded <?= $i==$page ? 'bg-pink-500 text-white' : 'bg-gray-100 text-gray-700' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
  </div>
</div>
<script>
  // --- Refactored and Unified AJAX/Search/Filter Logic ---

  // Check if the loadPage function exists in the parent window
  const canAjax = window.parent && typeof window.parent.loadPage === 'function';

  // Function to build URL with current filters
  function buildUrl(page, baseFile = 'products/list.php') {
    const term = document.getElementById('searchInput').value.trim();
    const category = document.querySelector('select[name="cat"]').value;
    const params = new URLSearchParams({
      page: page,
      search: term,
      cat: category
    });
    return `${baseFile}?${params.toString()}`;
  }

  // Attach event listeners for search and filter
  let searchTimeout;
  document.getElementById('searchInput').addEventListener('input', () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => canAjax && window.parent.loadPage(buildUrl(1)), 500);
  });

  document.querySelector('select[name="cat"]').addEventListener('change', () => {
    canAjax && window.parent.loadPage(buildUrl(1));
  });

  document.getElementById('clearSearch').addEventListener('click', () => {
    document.getElementById('searchInput').value = '';
    document.querySelector('select[name="cat"]').value = '';
    canAjax && window.parent.loadPage('products/list.php?page=1');
  });

  // Bind all data-page links (pagination, edit, delete, add)
  document.querySelectorAll('a[data-page]').forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      const pageUrl = this.getAttribute('data-page');
      if (canAjax) {
        window.parent.loadPage(pageUrl);
      } else {
        window.location.href = pageUrl; // Fallback for direct access
      }
    });
  });
</script>
