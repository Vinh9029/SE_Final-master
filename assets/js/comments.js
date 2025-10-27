/**
 * Khởi tạo khu vực bình luận trên một trang.
 * @param {object} config - Đối tượng cấu hình.
 * @param {string} config.targetType - Loại đối tượng ('product' hoặc 'blog').
 * @param {number} config.targetId - ID của sản phẩm hoặc blog.
 * @param {number|null} config.loggedInUserId - ID của người dùng đã đăng nhập, hoặc null.
 * @param {string} config.baseUrl - URL gốc của trang web.
 * @param {string} config.themeColorClass - Lớp màu chủ đạo cho các nút (ví dụ: 'text-yellow-700' hoặc 'text-pink-500').
 * @param {string} config.defaultAvatar - URL ảnh đại diện mặc định.
 */
function initializeCommentsSection(config) {
    const { targetType, targetId, loggedInUserId, baseUrl, themeColorClass, defaultAvatar } = config;

    const commentsContainer = document.getElementById('comments-container');
    const commentForm = document.getElementById('comment-form');

    // Một hàm toast nội bộ, có thể được thay thế bằng một hệ thống toast toàn cục sau này.
    function showToast(message, isError = false) {
        const toastId = 'toast-notification';
        let toast = document.getElementById(toastId);
        if (!toast) {
            toast = document.createElement('div');
            toast.id = toastId;
            document.body.appendChild(toast);
            const style = document.createElement('style');
            style.innerHTML = `
                #${toastId} { position: fixed; top: 5rem; right: 1.25rem; padding: 1rem 1.5rem; border-radius: 0.5rem; color: white; z-index: 10000; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transform: translateX(150%); transition: transform 0.3s ease-in-out; }
                #${toastId}.show { transform: translateX(0); }
                #${toastId}.error { background-color: #ef4444; } /* red-500 */
                #${toastId}:not(.error) { background-color: #22c55e; } /* green-500 */
            `;
            document.head.appendChild(style);
        }
        toast.textContent = message;
        toast.classList.remove('error');
        if (isError) toast.classList.add('error');
        toast.classList.add('show');
        setTimeout(() => { toast.classList.remove('show'); }, 3000);
    }

    function loadComments() {
        fetch(`${baseUrl}/includes/handlers/comments/getComments.php?target_type=${targetType}&target_id=${targetId}`)
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

                        const likeBtnClass = comment.user_has_liked == 1 ? themeColorClass : 'text-gray-600';

                        commentElement.innerHTML = `
                            <img src="${comment.user_avatar || defaultAvatar}" alt="${comment.username}" class="w-12 h-12 rounded-full object-cover">
                            <div class="flex-1">
                                <div class="flex justify-between items-center">
                                    <p class="font-bold text-gray-800">${comment.username}</p>
                                    <span class="text-xs text-gray-500">${new Date(comment.created_at).toLocaleString('vi-VN')}</span>
                                </div>
                                <div class="comment-content text-gray-700 mt-1">${comment.content}</div>
                                <div class="comment-actions mt-2 flex items-center space-x-4">
                                    <button class="font-semibold hover:${themeColorClass} like-comment-btn ${likeBtnClass}" data-comment-id="${comment.id}">
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
        document.querySelectorAll('.like-comment-btn').forEach(btn => {
            btn.onclick = function() {
                if (!loggedInUserId) { showToast('Vui lòng đăng nhập để thích bình luận.', true); return; }
                const commentId = this.dataset.commentId;
                const formData = new FormData();
                formData.append('comment_id', commentId);

                fetch(`${baseUrl}/includes/handlers/comments/likeComment.php`, { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.querySelector('.like-count').innerText = data.new_like_count;
                            this.classList.toggle(themeColorClass, data.action === 'liked');
                            this.classList.toggle('text-gray-600', data.action !== 'liked');
                        } else {
                            showToast(data.message, true);
                        }
                    });
            };
        });

        document.querySelectorAll('.delete-comment-btn').forEach(btn => {
            btn.onclick = function() {
                if (!confirm('Bạn có chắc muốn xóa bình luận này không?')) return;
                const commentId = this.dataset.commentId;
                const formData = new FormData();
                formData.append('comment_id', commentId);
                fetch(`${baseUrl}/includes/handlers/comments/deleteComment.php`, { method: 'POST', body: formData })
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
                        <button class="text-xs font-bold text-gray-500 hover:underline cancel-edit-btn">Hủy</button>
                        <button class="text-xs font-bold text-green-600 hover:underline ml-2 save-edit-btn">Lưu</button>
                    </div>
                `;

                commentElement.querySelector('.cancel-edit-btn').onclick = () => {
                    contentDiv.innerHTML = originalContent;
                    actionsDiv.style.display = 'flex';
                };
                commentElement.querySelector('.save-edit-btn').onclick = () => {
                    const newContent = commentElement.querySelector('textarea').value.trim();
                    if (!newContent) { showToast('Bình luận không được để trống.', true); return; }

                    const formData = new FormData();
                    formData.append('comment_id', commentId);
                    formData.append('content', newContent);

                    fetch(`${baseUrl}/includes/handlers/comments/editComment.php`, { method: 'POST', body: formData })
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

    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const content = formData.get('content').trim();
            if (!content) { showToast('Vui lòng nhập nội dung bình luận.', true); return; }

            fetch(`${baseUrl}/includes/handlers/comments/addComment.php`, { method: 'POST', body: formData })
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

    // Initial load
    if (commentsContainer) {
        loadComments();
    }
}