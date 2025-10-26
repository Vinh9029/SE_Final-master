<?php
include_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database/db_connection.php';
require_once __DIR__ . '/helper.php';

$slug = isset($_GET['slug']) ? $_GET['slug'] : null;
if (!$slug) {
    die('Không tìm thấy sản phẩm!');
}
// Lấy sản phẩm theo slug
$stmt = $conn->query("SELECT p.*, c.name as category_name, c.description as category_desc FROM products p JOIN categories c ON p.category_id = c.category_id");
$products = $stmt->fetch_all(MYSQLI_ASSOC);
$product = null;
foreach ($products as $p) {
    if (generateSlug($p['name']) === $slug) {
        $product = $p;
        break;
    }
}
if (!$product) {
    die('Sản phẩm không tồn tại!');
}

// Lấy danh sách size từ DB
$sizeQuery = $conn->prepare("SELECT * FROM product_sizes WHERE product_id = ?");
$sizeQuery->bind_param("i", $product['product_id']);
$sizeQuery->execute();
$sizeResult = $sizeQuery->get_result();

// Check if product is in favourites
$is_favourited = false;
if (isset($_SESSION['user_id'])) {
    $fav_check_sql = "SELECT id FROM favourites WHERE customer_id = ? AND product_id = ?";
    $fav_check_stmt = $conn->prepare($fav_check_sql);
    $fav_check_stmt->bind_param("ii", $_SESSION['user_id'], $product['product_id']);
    $fav_check_stmt->execute();
    if ($fav_check_stmt->get_result()->num_rows > 0) {
        $is_favourited = true;
    }
    $fav_check_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - Chi tiết sản phẩm | Old Favour Coffee</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .btn-orange {
            background: #FF7A00;
        }

        .btn-orange:hover {
            background: #ff9800;
        }

        .footer-bg {
            background: #3d2c1a;
        }
    </style>
</head>

<body class="bg-pink-50 font-sans">
    <?php include_once __DIR__ . '/../includes/header.php'; ?>
    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Breadcrumb -->
        <nav class="text-lg font-extrabold flex items-center mb-6" aria-label="Breadcrumb">
            <a href="../index.php" class="text-pink-500 hover:text-pink-600">Trang chủ</a>
            <span class="mx-2 text-pink-300 font-bold">/</span>
            <a href="menus.php?cat=<?php echo generateSlug($product['category_name']); ?>" class="text-pink-500 hover:text-pink-600"><?php echo $product['category_name']; ?></a>
            <span class="mx-2 text-pink-300 font-bold">/</span>
            <span class="text-pink-600"><?php echo $product['name']; ?></span>
        </nav>
        <div class="relative bg-white rounded-2xl shadow-xl p-8 flex flex-col md:flex-row gap-8 items-center">
            <?php if (isset($_SESSION['user_id'])): ?>
                <button id="favourite-btn" class="absolute top-4 right-4 <?php echo $is_favourited ? 'bg-pink-600 text-white' : 'bg-gray-200 text-gray-700'; ?> px-4 py-2 rounded-full font-bold text-lg shadow transition duration-200" data-product-id="<?php echo $product['product_id']; ?>">
                    <i class="fa <?php echo $is_favourited ? 'fa-heart-circle-check' : 'fa-heart'; ?>"></i>
                </button>
            <?php endif; ?>
            <div class="flex-shrink-0">
                <img src="<?php echo $base_url . '/' . ($product['image'] ?: 'Photos/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-64 h-64 object-cover rounded-xl shadow bg-gray-100 border-2 border-pink-100" />
            </div>
            <div class="flex-1 flex flex-col justify-center">
                <h1 class="font-extrabold text-pink-600 text-3xl mb-2"><?php echo $product['name']; ?></h1>
                <div class="mb-6">
                    <h4 class="font-bold text-lg mb-2 text-pink-600">Chọn Size</h4>
                    <div class="flex flex-wrap gap-4">
                        <?php if ($sizeResult->num_rows > 0): ?>
                            <?php while ($size = $sizeResult->fetch_assoc()): ?>
                                <label class="size-option flex items-center gap-2 px-4 py-2 rounded-xl border border-pink-200 bg-pink-50 cursor-pointer hover:bg-pink-100 transition">
                                    <input type="radio" name="product_size" value="<?php echo $size['size_id']; ?>" data-extra="<?php echo $size['extra_price']; ?>" class="accent-pink-500" required>
                                    <span class="font-semibold text-pink-700"><?php echo htmlspecialchars($size['size_name']); ?></span>
                                    <span class="text-gray-500 text-sm"><?php echo htmlspecialchars($size['volume']); ?></span>
                                    <?php if ($size['extra_price'] > 0): ?>
                                        <span class="text-orange-600 font-bold text-sm">(+<?php echo number_format($size['extra_price'], 0, ',', '.'); ?> đ)</span>
                                    <?php endif; ?>
                                </label>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="italic text-gray-400">Hiện chưa có size cho sản phẩm này.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="text-orange-600 font-bold text-2xl mb-4" id="product-price"><?php echo number_format($product['price'], 0, ',', '.'); ?> đ</div>
                <div class="mb-4 text-gray-700 text-base leading-relaxed"><?php echo $product['description'] ?: '<span class="italic text-gray-400">Chưa có mô tả cho sản phẩm này.</span>'; ?></div>
                <div class="flex gap-4 mt-6">
                    <button id="add-to-cart-btn" class="btn-orange hover:bg-orange-600 text-white px-8 py-3 rounded-xl font-bold text-lg shadow transition duration-200"><i class="fa fa-shopping-cart mr-2"></i>Thêm món ngay</button>
                    
                    <a href="menus.php?cat=<?php echo generateSlug($product['category_name']); ?>" class="bg-gray-100 hover:bg-pink-100 text-pink-600 px-6 py-3 rounded-xl font-bold text-lg shadow transition duration-200"><i class="fa fa-arrow-left mr-2"></i>Quay lại menu</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Comments Section -->
    <div class="mt-12 bg-white rounded-2xl shadow-xl p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Bình luận về sản phẩm</h2>

        <!-- Comment Form -->
        <?php if (isset($_SESSION['user_id'])) : ?>
            <form id="comment-form" class="mb-8">
                <input type="hidden" name="target_id" value="<?php echo $product['product_id']; ?>">
                <input type="hidden" name="target_type" value="product">
                <div>
                    <textarea name="content" id="comment-content" rows="4" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500" placeholder="Chia sẻ cảm nhận của bạn về sản phẩm này..."></textarea>
                </div>
                <div class="mt-4 text-right">
                    <button type="submit" class="btn-orange text-white font-bold py-2 px-6 rounded-lg hover:bg-orange-600 transition">Gửi bình luận</button>
                </div>
            </form>
        <?php else : ?>
            <div class="text-center p-4 border-2 border-dashed rounded-lg bg-gray-50">
                <p class="text-gray-600">Vui lòng <a href="<?php echo $base_url; ?>/login" class="font-bold text-pink-500 hover:underline">đăng nhập</a> để để lại bình luận của bạn.</p>
            </div>
        <?php endif; ?>

        <!-- Comments List -->
        <div id="comments-container" class="space-y-6">
            <!-- Comments will be loaded here by AJAX -->
            <div class="text-center text-gray-500">
                <i class="fa fa-spinner fa-spin"></i> Đang tải bình luận...
            </div>
        </div>
    </div>

    <?php include_once __DIR__ . '/../includes/footer.php'; ?>

    <!-- Toast Notification -->
    <div id="toast" class="fixed top-20 right-5 bg-green-500 text-white py-3 px-6 rounded-xl shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out z-50">
        <i class="fa fa-check-circle mr-2"></i>
        <span id="toast-message"></span>
    </div>
    <style>
        #toast.show {
            transform: translateX(0);
        }
        #toast.error {
            background-color: #ef4444; /* bg-red-500 */
        }
    </style>

    <script>
    const loggedInUserId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;

    // Cập nhật giá khi chọn size
    setTimeout(function() {
        document.querySelectorAll('input[name="product_size"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const basePrice = <?php echo $product['price']; ?>;
                const extra = parseFloat(this.dataset.extra) || 0;
                const finalPrice = basePrice + extra;
                document.getElementById("product-price").innerText = finalPrice.toLocaleString('vi-VN') + " đ";
            });
        });
    }, 100);

    // Toast function
    function showToast(message, isError = false) {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toast-message');
        toastMessage.textContent = message;
        if (isError) {
            toast.classList.add('error');
        } else {
            toast.classList.remove('error');
        }
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 2500);
    }

    // Xử lý thêm vào giỏ hàng
    document.getElementById('add-to-cart-btn').onclick = function() {
        const productId = <?php echo $product['product_id']; ?>;
        const sizeRadio = document.querySelector('input[name="product_size"]:checked');
        const sizeId = sizeRadio ? sizeRadio.value : null;
        const quantity = 1;
        fetch('<?php echo $base_url; ?>/customer/cart/add.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `product_id=${productId}&size_id=${sizeId}&quantity=${quantity}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Đã thêm vào giỏ hàng!');
                setTimeout(() => { window.location.reload(); }, 800);
            } else {
                showToast(data.message, true);
            }
        });
    };

    // Handle favourite button click
    const favBtn = document.getElementById('favourite-btn');
    if (favBtn) {
        favBtn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const isFavourited = this.classList.contains('bg-pink-600');
            const url = isFavourited 
                ? '<?php echo $base_url; ?>/includes/handlers/favourite/removeFavourite.php'
                : '<?php echo $base_url; ?>/includes/handlers/favourite/addFavourite.php';
            const formData = new FormData();
            formData.append('product_id', productId);
            fetch(url, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (isFavourited) {
                        this.classList.remove('bg-pink-600', 'text-white');
                        this.classList.add('bg-gray-200', 'text-gray-700');
                        this.querySelector('i').classList.replace('fa-heart-circle-check', 'fa-heart');
                        showToast('Removed from wishlist!');
                    } else {
                        this.classList.add('bg-pink-600', 'text-white');
                        this.classList.remove('bg-gray-200', 'text-gray-700');
                        this.querySelector('i').classList.replace('fa-heart', 'fa-heart-circle-check');
                        showToast('Added to wishlist!');
                    }
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(data.message || 'An error occurred.', true);
                }
            });
        });
    }

    // --- Comments Section Logic ---
    const commentsContainer = document.getElementById('comments-container');
    const productId = <?php echo $product['product_id']; ?>;

    function loadComments() {
        fetch(`<?php echo $base_url; ?>/includes/handlers/comments/getComments.php?target_type=product&target_id=${productId}`)
            .then(res => res.json())
            .then(data => {
                commentsContainer.innerHTML = '';
                if (data.success && data.comments.length > 0) {
                    data.comments.forEach(comment => {
                        const commentElement = document.createElement('div');
                        commentElement.id = `comment-${comment.id}`;
                        commentElement.className = 'flex items-start space-x-4 p-4 bg-gray-50 rounded-lg';
                        
                        let ownerActions = '';
                        if (loggedInUserId && comment.user_id == loggedInUserId) {
                            ownerActions = `
                                <div class="text-xs">
                                    <button class="font-semibold text-blue-600 hover:underline edit-comment-btn" data-comment-id="${comment.id}">Edit</button>
                                    <span class="mx-1">·</span>
                                    <button class="font-semibold text-red-600 hover:underline delete-comment-btn" data-comment-id="${comment.id}">Delete</button>
                                </div>
                            `;
                        }

                        const likeBtnClass = comment.user_has_liked == 1 ? 'text-pink-500' : 'text-gray-600';

                        commentElement.innerHTML = `
                            <img src="${comment.user_avatar || '<?php echo $base_url; ?>/customer/Photos/avatar/avatar1.jpg'}" alt="${comment.username}" class="w-12 h-12 rounded-full object-cover">
                            <div class="flex-1">
                                <div class="flex justify-between items-center">
                                    <p class="font-bold text-gray-800">${comment.username}</p>
                                    <span class="text-xs text-gray-500">${new Date(comment.created_at).toLocaleString('vi-VN')}</span>
                                </div>
                                <div class="comment-content text-gray-700 mt-1">${comment.content}</div>
                                <div class="comment-actions mt-2 flex items-center space-x-4">
                                    <button class="font-semibold hover:text-pink-500 like-comment-btn ${likeBtnClass}" data-comment-id="${comment.id}">
                                        <i class="fas fa-thumbs-up"></i>
                                        <span class="like-count ml-1">${comment.likes}</span>
                                    </button>
                                    ${ownerActions}
                                </div>
                            </div>
                        `;
                        commentsContainer.appendChild(commentElement);
                    });
                    addCommentActionListeners();
                } else {
                    commentsContainer.innerHTML = '<p class="text-center text-gray-500">Chưa có bình luận nào. Hãy là người đầu tiên!</p>';
                }
            });
    }

    function addCommentActionListeners() {
        // Like buttons
        document.querySelectorAll('.like-comment-btn').forEach(btn => {
            btn.onclick = function() {
                if (!loggedInUserId) { showToast('Please log in to like comments.', true); return; }
                const commentId = this.dataset.commentId;
                const formData = new FormData();
                formData.append('comment_id', commentId);

                fetch('<?php echo $base_url; ?>/includes/handlers/comments/likeComment.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.querySelector('.like-count').innerText = data.new_like_count;
                        if (data.action === 'liked') {
                            this.classList.add('text-pink-500');
                            this.classList.remove('text-gray-600');
                        } else {
                            this.classList.remove('text-pink-500');
                            this.classList.add('text-gray-600');
                        }
                    } else {
                        showToast(data.message, true);
                    }
                });
            };
        });

        // Delete buttons
        document.querySelectorAll('.delete-comment-btn').forEach(btn => {
            btn.onclick = function() {
                if (!confirm('Are you sure you want to delete this comment?')) return;
                const commentId = this.dataset.commentId;
                const formData = new FormData();
                formData.append('comment_id', commentId);
                fetch('<?php echo $base_url; ?>/includes/handlers/comments/deleteComment.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message);
                        document.getElementById(`comment-${commentId}`).remove();
                    } else {
                        showToast(data.message, true);
                    }
                });
            };
        });

        // Edit buttons
        document.querySelectorAll('.edit-comment-btn').forEach(btn => {
            btn.onclick = function() {
                const commentId = this.dataset.commentId;
                const commentElement = document.getElementById(`comment-${commentId}`);
                const contentDiv = commentElement.querySelector('.comment-content');
                const actionsDiv = commentElement.querySelector('.comment-actions');
                const originalContent = contentDiv.innerText;
                actionsDiv.style.display = 'none'; // Hide actions while editing

                contentDiv.innerHTML = `
                    <textarea class="w-full p-2 border rounded-lg text-sm">${originalContent}</textarea>
                    <div class="text-right mt-2">
                        <button class="text-xs font-bold text-gray-500 hover:underline cancel-edit-btn">Cancel</button>
                        <button class="text-xs font-bold text-green-600 hover:underline ml-2 save-edit-btn">Save</button>
                    </div>
                `;

                commentElement.querySelector('.cancel-edit-btn').onclick = () => { 
                    contentDiv.innerHTML = originalContent;
                    actionsDiv.style.display = 'flex';
                };
                commentElement.querySelector('.save-edit-btn').onclick = () => {
                    const newContent = commentElement.querySelector('textarea').value.trim();
                    if (!newContent) { showToast('Comment cannot be empty.', true); return; }

                    const formData = new FormData();
                    formData.append('comment_id', commentId);
                    formData.append('content', newContent);

                    fetch('<?php echo $base_url; ?>/includes/handlers/comments/editComment.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message);
                            contentDiv.innerHTML = newContent;
                        } else {
                            showToast(data.message, true);
                            contentDiv.innerHTML = originalContent;
                        }
                        actionsDiv.style.display = 'flex';
                    });
                };
            };
        });
    }

    loadComments();

    const commentForm = document.getElementById('comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const content = formData.get('content').trim();
            if (!content) { showToast('Vui lòng nhập nội dung bình luận.', true); return; }

            fetch('<?php echo $base_url; ?>/includes/handlers/comments/addComment.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Bình luận của bạn đã được gửi và đang chờ duyệt.');
                    document.getElementById('comment-content').value = '';
                } else {
                    showToast(data.message || 'Không thể gửi bình luận.', true);
                }
            });
        });
    }
</script>
</body>

</html>