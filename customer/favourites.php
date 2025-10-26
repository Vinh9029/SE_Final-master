<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<?php
include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../includes/header.php';
include_once __DIR__ . '/../database/db_connection.php';
include_once __DIR__ . '/../menus/helper.php'; // Include slug generator

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: " . $base_url . "/login/index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all favourite items
$sql = "SELECT p.product_id, p.name, p.price, p.image, p.description
        FROM favourites f
        JOIN products p ON f.product_id = p.product_id
        WHERE f.customer_id = ?
        ORDER BY f.added_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$favourites = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="container mx-auto my-10 p-6 bg-white rounded-lg shadow-md">
    <h1 class="text-3xl font-bold text-pink-600 mb-6">My Wishlist</h1>

    <?php if (count($favourites) === 0): ?>
        <div class="text-center text-gray-500">
            <p>You haven't added any items to your wishlist yet.</p>
            <a href="<?php echo $base_url; ?>/menus/menus.php" class="mt-4 inline-block bg-pink-600 text-white px-6 py-2 rounded-full hover:bg-pink-700 transition">Explore Menu</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($favourites as $item): ?>
                <div class="relative bg-white border rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 group favourite-item" data-product-id="<?php echo $item['product_id']; ?>">
                    <a href="<?php echo $base_url . '/menus/product.php?slug=' . generateSlug($item['name']); ?>">
                        <img src="<?php echo $base_url . '/' . $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full h-48 object-cover rounded-t-lg">
                    </a>
                    <div class="p-4">
                        <h3 class="text-lg font-bold text-pink-600 truncate">
                            <a href="<?php echo $base_url . '/menus/product.php?slug=' . generateSlug($item['name']); ?>"><?php echo htmlspecialchars($item['name']); ?></a>
                        </h3>
                        <p class="text-gray-600 text-sm mt-1 h-10 overflow-hidden"><?php echo htmlspecialchars($item['description']); ?></p>
                        <div class="flex justify-between items-center mt-4">
                            <span class="text-xl font-bold text-orange-600"><?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ</span>
                        </div>
                    </div>
                    <!-- Action Buttons -->
                    <div class="p-4 border-t flex gap-2">
                        <button class="btn-add-to-cart flex-1 bg-pink-600 text-white px-4 py-2 rounded-full hover:bg-pink-700 transition text-sm">Add to Cart</button>
                        <button class="btn-remove-favourite bg-gray-200 text-gray-700 px-3 py-2 rounded-full hover:bg-red-500 hover:text-white transition" title="Remove from Wishlist">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const favouriteItems = document.querySelectorAll('.favourite-item');

    favouriteItems.forEach(item => {
        const productId = item.dataset.productId;
        const addToCartBtn = item.querySelector('.btn-add-to-cart');
        const removeBtn = item.querySelector('.btn-remove-favourite');

        // Add to cart functionality (basic)
        if(addToCartBtn) {
            addToCartBtn.addEventListener('click', function() {
                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('quantity', 1);

                fetch('<?php echo $base_url; ?>/customer/cart/add.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Added to cart!');
                        // Optionally, update cart count in header
                        location.reload(); // Simple way to update header count
                    } else {
                        alert(data.message || 'Could not add to cart.');
                    }
                });
            });
        }

        // Remove from favourite functionality
        if(removeBtn) {
            removeBtn.addEventListener('click', function() {
                if (!confirm('Are you sure you want to remove this item from your wishlist?')) {
                    return;
                }

                const formData = new FormData();
                formData.append('product_id', productId);

                fetch('<?php echo $base_url; ?>/includes/handlers/favourite/removeFavourite.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the item from the view
                        item.style.transition = 'opacity 0.5s';
                        item.style.opacity = '0';
                        setTimeout(() => {
                            item.remove();
                            if (document.querySelectorAll('.favourite-item').length === 0) {
                                location.reload(); // Reload to show empty message
                            }
                        }, 500);
                    } else {
                        alert(data.message || 'Could not remove item.');
                    }
                });
            });
        }
    });
});
</script>

<?php
include_once __DIR__ . '/../includes/footer.php';
?>