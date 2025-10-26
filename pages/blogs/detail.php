<?php
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../database/db_connection.php';

// Kiểm tra xem có slug được truyền qua URL không
if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    // Nếu không có, chuyển hướng về trang danh sách blog
    header("Location: " . $base_url . "/pages/blogs/index.php");
    exit();
}

$slug = $_GET['slug'];

// Lấy thông tin chi tiết bài blog từ CSDL
$blog = null;
$sql = "SELECT b.*, u.full_name 
        FROM blogs b 
        JOIN users u ON b.user_id = u.user_id 
        WHERE b.slug = ? AND b.status = 'approved' 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $blog = $result->fetch_assoc();
} else {
    // Nếu không tìm thấy bài viết, hiển thị trang 404 đơn giản
    http_response_code(404);
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $blog ? htmlspecialchars($blog['title']) : 'Không tìm thấy bài viết'; ?> - Old Flavour Blog</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Merriweather', serif;
            background-color: #fdfaf6;
            color: #3a3a3a;
        }

        .blog-title {
            font-family: 'Playfair Display', serif;
            color: #C28B58;
        }

        /* Styling cho nội dung được render từ CSDL */
        .blog-content {
            line-height: 1.8;
            font-size: 1.1rem;
        }

        .blog-content h1,
        .blog-content h2,
        .blog-content h3 {
            font-family: 'Playfair Display', serif;
            color: #C28B58;
            margin-top: 2rem;
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .blog-content p {
            margin-bottom: 1.25rem;
        }

        .blog-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 2rem auto;
            display: block;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .blog-content blockquote {
            border-left: 4px solid #d4a574;
            padding-left: 1.5rem;
            margin: 2rem 0;
            font-style: italic;
            color: #6b4a28;
            font-size: 1.2rem;
        }
    </style>
</head>

<body>
    <main class="container mx-auto px-4 py-12">
        <?php if ($blog) : ?>
            <article class="max-w-4xl mx-auto bg-white p-6 sm:p-10 rounded-lg shadow-lg">
                <h1 class="blog-title text-4xl md:text-5xl font-bold text-center mb-4"><?php echo htmlspecialchars($blog['title']); ?></h1>
                <div class="text-center text-gray-500 mb-8">
                    <span>Viết bởi <strong><?php echo htmlspecialchars($blog['full_name']); ?></strong></span>
                    <span class="mx-2">•</span>
                    <span>Ngày đăng: <?php echo date('d/m/Y', strtotime($blog['created_at'])); ?></span>
                </div>

                <?php if ($blog['cover_image']) : ?>
                    <img src="<?php echo $base_url . '/' . htmlspecialchars($blog['cover_image']); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>" class="w-full h-auto object-cover rounded-lg mb-8 shadow-md">
                <?php endif; ?>

                <div class="blog-content">
                    <?php echo $blog['content']; // Nội dung HTML được render trực tiếp ?>
                </div>

                <div class="text-center mt-12">
                    <a href="<?php echo $base_url; ?>/pages/blogs/index.php" class="inline-block bg-yellow-800 text-white font-bold py-3 px-6 rounded-lg shadow-lg hover:bg-yellow-900 transition-colors duration-300">
                        <i class="fas fa-arrow-left mr-2"></i> Quay lại danh sách Blog
                    </a>
                </div>
            </article>

            <!-- Comments Section -->
            <div class="max-w-4xl mx-auto mt-12 bg-white p-6 sm:p-10 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Bình luận</h2>

                <!-- Comment Form -->
                <?php if (isset($_SESSION['user_id'])) : ?>
                    <form id="comment-form" class="mb-8">
                        <input type="hidden" name="target_id" value="<?php echo $blog['blog_id']; ?>">
                        <input type="hidden" name="target_type" value="blog">
                        <div>
                            <textarea name="content" id="comment-content" rows="4" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-800" placeholder="Chia sẻ suy nghĩ của bạn..."></textarea>
                        </div>
                        <div class="mt-4 text-right">
                            <button type="submit" class="bg-yellow-800 text-white font-bold py-2 px-6 rounded-lg hover:bg-yellow-900 transition">Gửi bình luận</button>
                        </div>
                    </form>
                <?php else : ?>
                    <div class="text-center p-4 border-2 border-dashed rounded-lg bg-gray-50">
                        <p class="text-gray-600">Vui lòng <a href="<?php echo $base_url; ?>/login" class="font-bold text-yellow-800 hover:underline">đăng nhập</a> để để lại bình luận của bạn.</p>
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

        <?php else : ?>
            <div class="text-center py-20">
                <h1 class="text-6xl font-bold text-gray-300">404</h1>
                <h2 class="text-2xl font-semibold text-yellow-800 mt-4">Bài viết không tồn tại</h2>
                <p class="text-gray-600 mt-2">Rất tiếc, chúng tôi không thể tìm thấy bài viết bạn yêu cầu. Có thể nó đã bị xóa hoặc đường dẫn không còn tồn tại.</p>
                <a href="<?php echo $base_url; ?>/pages/blogs/index.php" class="mt-8 inline-block bg-yellow-800 text-white font-bold py-3 px-6 rounded-lg shadow-lg hover:bg-yellow-900 transition-colors">
                    Về trang Blog
                </a>
            </div>
        <?php endif; ?>
    </main>

    <?php include_once __DIR__ . '/../../includes/footer.php'; ?>

<script>
const loggedInUserId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;

document.addEventListener('DOMContentLoaded', function() {
    // A self-contained toast function for this page
    function showToast(message, isError = false) {
        const toastId = 'toast-notification';
        let toast = document.getElementById(toastId);
        if (!toast) {
            toast = document.createElement('div');
            toast.id = toastId;
            document.body.appendChild(toast);
            const style = document.createElement('style');
            style.innerHTML = `
                #${toastId} { position: fixed; top: 5rem; right: 1.25rem; padding: 1rem 1.5rem; border-radius: 0.5rem; color: white; z-index: 50; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transform: translateX(150%); transition: transform 0.3s ease-in-out; }
                #${toastId}.show { transform: translateX(0); }
                #${toastId}.error { background-color: #ef4444; } /* red-500 */
                #${toastId}:not(.error) { background-color: #22c55e; } /* green-500 */
            `;
            document.head.appendChild(style);
        }
        toast.textContent = message;
        toast.classList.remove('error');
        if(isError) toast.classList.add('error');
        toast.classList.add('show');
        setTimeout(() => { toast.classList.remove('show'); }, 3000);
    }

    <?php if ($blog) : ?>
        const commentsContainer = document.getElementById('comments-container');
        const blogId = <?php echo $blog['blog_id']; ?>;

        function loadComments() {
            fetch(`<?php echo $base_url; ?>/includes/handlers/comments/getComments.php?target_type=blog&target_id=${blogId}`)
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

                            const likeBtnClass = comment.user_has_liked == 1 ? 'text-yellow-700' : 'text-gray-600';

                            commentElement.innerHTML = `
                                <img src="${comment.user_avatar || '<?php echo $base_url; ?>/customer/Photos/avatar/avatar1.jpg'}" alt="${comment.username}" class="w-12 h-12 rounded-full object-cover">
                                <div class="flex-1">
                                    <div class="flex justify-between items-center">
                                        <p class="font-bold text-gray-800">${comment.username}</p>
                                        <span class="text-xs text-gray-500">${new Date(comment.created_at).toLocaleString('vi-VN')}</span>
                                    </div>
                                    <div class="comment-content text-gray-700 mt-1">${comment.content}</div>
                                    <div class="comment-actions mt-2 flex items-center space-x-4">
                                        <button class="font-semibold hover:text-yellow-700 like-comment-btn ${likeBtnClass}" data-comment-id="${comment.id}">
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
                                this.classList.add('text-yellow-700');
                                this.classList.remove('text-gray-600');
                            } else {
                                this.classList.remove('text-yellow-700');
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
                    actionsDiv.style.display = 'none';

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
    <?php endif; ?>
});
</script>

</body>

</html>