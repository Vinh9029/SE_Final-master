<?php
session_start();
include_once __DIR__ . '/../../database/db_connection.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login/index.php');
    exit;
}
// cart1.php - Giao diện giỏ hàng mới đồng bộ màu sắc toàn site
// Chỉ xử lý front-end, chưa kết nối backend
// Copy logic lấy dữ liệu từ cart.php nếu cần tích hợp backend

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - Old Favour Coffee</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .cart-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #ee5a24 0%, #ff6b6b 100%);
        }

        .cart-item-anim {
            transition: box-shadow 0.2s, background 0.2s;
        }

        .cart-item-anim:hover {
            box-shadow: 0 8px 32px 0 rgba(255, 107, 107, 0.15);
            background: #fdf6f6;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col">
    <?php include '../../includes/header.php'; ?>
    <main class="flex-1 cart-gradient py-12">
        <div class="container mx-auto px-4">
            <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-orange-500 to-red-500 p-6">
                    <div class="flex items-center justify-between text-pink-600">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-shopping-cart text-pink text-2xl"></i>
                            <h1 class="text-2xl font-bold text-pink">Giỏ hàng của bạn</h1>
                        </div>
                        <div class="text-white">
                            <span class="text-lg">Tổng cộng: </span>
                            <span class="text-2xl font-bold" id="cart-total">0đ</span>
                        </div>
                    </div>
                </div>
                <!-- Cart Items (Demo dữ liệu tĩnh, thay bằng PHP khi tích hợp backend) -->
                <div class="p-6">
                    <!-- Cart Table Header -->
                    <div class="mb-2 px-2">
                        <div class="grid grid-cols-12 items-center text-gray-500 font-semibold text-sm py-2 border-b border-gray-200">
                            <div class="col-span-5">Sản phẩm</div>
                            <div class="col-span-2 text-center">Đơn giá</div>
                            <div class="col-span-2 text-center">Số lượng</div>
                            <div class="col-span-2 text-center">Thành tiền</div>
                            <div class="col-span-1 text-center"></div>
                        </div>
                    </div>
                    <!-- Cart Items -->
                    <div class="space-y-4 mb-8" id="cart-items">
                        <?php include 'cart_item.php'; ?>
                    </div>
                    <!-- Voucher Section -->
                    <div class="mb-6 flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
                        <div class="flex flex-col gap-2 w-full md:w-1/2">
                            <label for="voucher-input" class="font-semibold text-gray-700">Mã giảm giá của bạn</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 mt-2">
                                <?php
                                $user_id = $_SESSION['user_id'];
                                $voucher_query = "SELECT voucher_id, code, discount_percent, program_name, min_order_value, expires_at, status FROM vouchers WHERE user_id = ? AND status = 'active' AND (expires_at IS NULL OR expires_at >= NOW()) ORDER BY expires_at ASC;";
                                $voucher_stmt = $conn->prepare($voucher_query);
                                $voucher_stmt->bind_param('i', $user_id);
                                $voucher_stmt->execute();
                                $voucher_result = $voucher_stmt->get_result();
                                while ($voucher = $voucher_result->fetch_assoc()):
                                ?>
                                <button class="voucher-btn bg-gradient-to-r from-pink-100 to-pink-200 hover:from-pink-200 hover:to-pink-300 text-pink-700 px-4 py-2 rounded-xl font-semibold shadow transition flex flex-col items-start border border-pink-200" data-voucher="<?= htmlspecialchars($voucher['code']) ?>">
                                    <span class="text-base font-bold">Mã: <?= htmlspecialchars($voucher['code']) ?></span>
                                    <span class="text-xs text-gray-600">Chương trình: <?= htmlspecialchars($voucher['program_name']) ?></span>
                                    <span class="text-xs text-gray-600">Giảm: <?= $voucher['discount_percent'] > 0 ? $voucher['discount_percent'].'%' : 'Voucher tiền mặt' ?></span>
                                    <span class="text-xs text-gray-600">Đơn tối thiểu: <?= number_format($voucher['min_order_value'], 0, ',', '.') ?>đ</span>
                                    <span class="text-xs text-gray-600">HSD: <?= $voucher['expires_at'] ? date('d/m/Y', strtotime($voucher['expires_at'])) : 'Không giới hạn' ?></span>
                                </button>
                                <?php endwhile; ?>
                            </div>
                            <div id="voucher-success" class="hidden text-green-600 font-semibold mt-2 animate-bounce">🎉 Mã giảm giá đã được áp dụng!</div>
                        </div>
                        <div class="flex flex-col gap-2 w-full md:w-1/2">
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-700">Tổng số lượng món:</span>
                                <span id="order-total-qty" class="font-bold">0</span>
                            </div>
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-700">Tổng tiền:</span>
                                <span id="order-total-before" class="font-bold">0đ</span>
                            </div>
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-700">Giảm giá:</span>
                                <span id="order-discount" class="font-bold text-green-600">0đ</span>
                            </div>
                            <div class="flex justify-between mb-2" id="shipping-row" style="display:none;">
                                <span class="text-gray-700">Phí giao hàng:</span>
                                <span id="order-shipping" class="font-bold">15,000đ</span>
                            </div>
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-700">Tổng thanh toán:</span>
                                <span id="order-total-after" class="font-bold text-pink-600">0đ</span>
                            </div>
                        </div>
                    </div>
                    <!-- Cart Actions -->
                    <div class="mt-8 flex flex-col lg:flex-row gap-4 justify-between items-center">
                        <a href="../../menus/menus.php"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-full font-bold transition-colors"><i
                                class="fas fa-arrow-left mr-2"></i>Tiếp tục mua sắm</a>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button
                                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-full font-bold transition-colors"><i
                                    class="fas fa-trash mr-2"></i>Xóa tất cả</button>
                            <a href="checkout/index.php"
                                class="btn-primary text-white px-8 py-3 rounded-full font-bold shadow-lg hover:shadow-xl transition-all duration-300"><i
                                    class="fas fa-credit-card mr-2"></i>Thanh toán</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <br>
        <?php include_once __DIR__ . '/../../includes/footer.php'; ?>
    </main>
    <script>
        // --- VOUCHER & ĐƠN HÀNG ---
        const VOUCHERS = {
            'GIAM10': {
                type: 'percent',
                value: 10,
                label: 'Giảm 10%'
            },
            'FREESHIP': {
                type: 'shipping',
                value: 15000,
                label: 'Miễn phí giao hàng'
            }
        };
        let appliedVoucher = null;
        const SHIPPING_FEE = 15000;

        function updateCartTotal() {
            const subtotals = document.querySelectorAll('.subtotal');
            let total = 0;
            let totalQty = 0;
            subtotals.forEach(subtotal => {
                const price = parseFloat(subtotal.dataset.price);
                const quantity = parseInt(subtotal.closest('.cart-item').querySelector('.quantity-input')
                    .value);
                total += price * quantity;
                totalQty += quantity;
            });
            // Tính giảm giá
            let discount = 0;
            let shipping = 0; // Không hiển thị phí giao hàng ở giỏ hàng
            if (appliedVoucher) {
                if (appliedVoucher.type === 'percent') {
                    discount = Math.round(total * appliedVoucher.value / 100);
                } else if (appliedVoucher.type === 'shipping') {
                    shipping = 0;
                }
            }
            const totalAfter = total - discount + shipping;
            // Cập nhật UI
            document.getElementById('cart-total').textContent = new Intl.NumberFormat('vi-VN').format(totalAfter) + 'đ';
            document.getElementById('order-total-qty').textContent = totalQty;
            document.getElementById('order-total-before').textContent = new Intl.NumberFormat('vi-VN').format(total) +
                'đ';
            document.getElementById('order-discount').textContent = '-' + new Intl.NumberFormat('vi-VN').format(
                discount) + 'đ';
            document.getElementById('order-shipping').textContent = shipping === 0 ? '' : new Intl.NumberFormat(
                'vi-VN').format(shipping) + 'đ';
            document.getElementById('shipping-row').style.display = 'none';
            document.getElementById('order-total-after').textContent = new Intl.NumberFormat('vi-VN').format(
                totalAfter) + 'đ';
        }
        updateCartTotal();

        // Áp dụng voucher
        document.getElementById('apply-voucher-btn').onclick = function () {
            const code = document.getElementById('voucher-input').value.trim().toUpperCase();
            if (VOUCHERS[code]) {
                appliedVoucher = VOUCHERS[code];
                document.getElementById('voucher-success').classList.remove('hidden');
                setTimeout(() => document.getElementById('voucher-success').classList.add('hidden'), 2000);
            } else {
                appliedVoucher = null;
                alert('Mã không hợp lệ hoặc đã hết hạn!');
            }
            updateCartTotal();
        };
        // Chọn voucher có sẵn
        document.querySelectorAll('.voucher-btn').forEach(btn => {
            btn.onclick = function () {
                document.getElementById('voucher-input').value = btn.dataset.voucher;
                document.getElementById('apply-voucher-btn').click();
            };
        });
        
    </script>

    <script>
    // --- VOUCHER UI/UX & AJAX LOADING ---
    let appliedVoucherCode = null;
    let appliedVoucherDiscount = 0;
    let appliedVoucherMinOrder = 0;
    let appliedVoucherType = 'percent';

    function showMessage(msg, type = 'success') {
        let msgBox = document.getElementById('cart-message');
        if (!msgBox) {
            msgBox = document.createElement('div');
            msgBox.id = 'cart-message';
            msgBox.className = 'fixed top-6 left-1/2 transform -translate-x-1/2 z-50 px-6 py-3 rounded-xl shadow-lg text-lg font-bold transition-all duration-300';
            document.body.appendChild(msgBox);
        }
        msgBox.textContent = msg;
        msgBox.style.background = type === 'success' ? 'linear-gradient(90deg,#43e97b 0%,#38f9d7 100%)' : 'linear-gradient(90deg,#ff6a6a 0%,#ee5a24 100%)';
        msgBox.style.color = '#fff';
        msgBox.style.opacity = '1';
        setTimeout(() => { msgBox.style.opacity = '0'; }, 1800);
    }

    function showLoading(show = true) {
        let loader = document.getElementById('cart-loader');
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'cart-loader';
            loader.innerHTML = '<div class="flex items-center justify-center fixed inset-0 bg-black bg-opacity-20 z-50"><div class="animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-pink-500"></div></div>';
            document.body.appendChild(loader);
        }
        loader.style.display = show ? 'block' : 'none';
    }

    // Voucher click
    document.querySelectorAll('.voucher-btn').forEach(btn => {
        btn.onclick = function () {
            document.querySelectorAll('.voucher-btn').forEach(b => b.classList.remove('ring-2', 'ring-pink-400'));
            btn.classList.add('ring-2', 'ring-pink-400');
            appliedVoucherCode = btn.dataset.voucher;
            appliedVoucherDiscount = parseInt(btn.querySelector('span:nth-child(3)').textContent.replace(/\D/g, '')) || 0;
            appliedVoucherMinOrder = parseInt(btn.querySelector('span:nth-child(4)').textContent.replace(/\D/g, '')) || 0;
            appliedVoucherType = btn.querySelector('span:nth-child(3)').textContent.includes('%') ? 'percent' : 'cash';
            showMessage('Đã áp dụng mã giảm giá!', 'success');
            updateCartTotal();
        };
    });

    // --- AJAX LOADING & MESSAGE FOR CART ACTIONS ---
    function refreshCartUI() {
        fetch('get_cart_items.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('cart-items').innerHTML = data.html;
                updateCartTotal();
            }
        });
    }

    function updateCartBadge(count) {
        // Giả sử có element với id 'cart-badge' trong header
        const badge = document.getElementById('cart-badge');
        if (badge) {
            badge.textContent = count;
        }
    }

    function ajaxCartAction(url, data, onSuccess) {
        showLoading(true);
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(result => {
            showLoading(false);
            if (result.success) {
                showMessage(result.message, 'success');
                if (onSuccess) onSuccess();
                setTimeout(() => { window.location.reload(); }, 700); // Tự động reload trang sau khi thao tác
            } else {
                showMessage(result.message, 'error');
            }
        })
        .catch(() => {
            showLoading(false);
            showMessage('Có lỗi xảy ra, vui lòng thử lại!', 'error');
        });
    }

    // Sửa lại các hàm gọi ajaxCartAction
    document.querySelectorAll('.remove-btn').forEach(btn => {
        btn.onclick = function () {
            const cartItem = btn.closest('.cart-item');
            const productId = cartItem.dataset.productId;
            const sizeId = cartItem.dataset.sizeId || null;
            if (confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                ajaxCartAction('remove.php', { product_id: productId, size_id: sizeId }, () => {
                    cartItem.remove();
                    refreshCartUI();
                });
            }
        };
    });
    document.querySelectorAll('.quantity-btn').forEach(btn => {
        btn.onclick = function () {
            const cartItem = btn.closest('.cart-item');
            const productId = cartItem.dataset.productId;
            const sizeId = cartItem.dataset.sizeId || null;
            const input = btn.parentElement.querySelector('.quantity-input');
            let val = parseInt(input.value);
            if (btn.innerHTML.includes('minus')) val = Math.max(1, val - 1);
            else val = val + 1;
            ajaxCartAction('update_ItemCart.php', { product_id: productId, size_id: sizeId, quantity: val }, () => {
                input.value = val;
                refreshCartUI();
            });
        };
    });
    document.querySelector('.bg-gray-200 .fa-trash').parentElement.onclick = function () {
        if (confirm('Bạn có chắc muốn xóa tất cả sản phẩm khỏi giỏ hàng?')) {
            ajaxCartAction('clear.php', {}, () => {
                document.getElementById('cart-items').innerHTML = '';
                refreshCartUI();
            });
        }
    };

    // Khi thêm sản phẩm ở trang khác, sau khi thêm xong cũng gọi updateCartBadge()
    document.addEventListener('DOMContentLoaded', updateCartBadge);

    // --- CẬP NHẬT GIẢM GIÁ THEO VOUCHER ---
    function updateCartTotal() {
        const subtotals = document.querySelectorAll('.subtotal');
        let total = 0;
        let totalQty = 0;
        subtotals.forEach(subtotal => {
            const price = parseFloat(subtotal.dataset.price);
            const quantity = parseInt(subtotal.closest('.cart-item').querySelector('.quantity-input').value);
            total += price * quantity;
            totalQty += quantity;
        });
        // Tính giảm giá
        let discount = 0;
        let shipping = 0; // Không hiển thị phí giao hàng ở giỏ hàng
        if (appliedVoucherCode && total >= appliedVoucherMinOrder) {
            if (appliedVoucherType === 'percent') {
                discount = Math.round(total * appliedVoucherDiscount / 100);
            } else if (appliedVoucherType === 'cash') {
                discount = appliedVoucherDiscount;
            }
        }
        const totalAfter = total - discount + shipping;
        document.getElementById('cart-total').textContent = new Intl.NumberFormat('vi-VN').format(totalAfter) + 'đ';
        document.getElementById('order-total-qty').textContent = totalQty;
        document.getElementById('order-total-before').textContent = new Intl.NumberFormat('vi-VN').format(total) + 'đ';
        document.getElementById('order-discount').textContent = '-' + new Intl.NumberFormat('vi-VN').format(discount) + 'đ';
        document.getElementById('order-shipping').textContent = shipping === 0 ? '' : new Intl.NumberFormat('vi-VN').format(shipping) + 'đ';
        document.getElementById('shipping-row').style.display = 'none';
        document.getElementById('order-total-after').textContent = new Intl.NumberFormat('vi-VN').format(totalAfter) + 'đ';
    }
    </script>
</body>

</html>