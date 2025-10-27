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
                if (data.success && data.comments.length > 0) {
                    commentsContainer.innerHTML = ''; // Xóa nội dung cũ
                    data.comments.forEach(comment => {
                        const commentElement = createCommentElement(comment);
                        commentsContainer.appendChild(commentElement);
                    });
                    addCommentActionListeners();
                } else {
                    commentsContainer.innerHTML = '<p class="text-center text-gray-500">Chưa có bình luận nào. Hãy là người đầu tiên!</p>';
                }
            });
    }

    /**
     * Tạo một phần tử HTML cho một bình luận (và các trả lời của nó).
     * @param {object} comment - Đối tượng bình luận.
     * @param {boolean} isReply - Cờ xác định đây có phải là một trả lời không.
     * @returns {HTMLElement} - Phần tử div chứa bình luận.
     */
    function createCommentElement(comment, isReply = false) {
        const commentWrapper = document.createElement('div');
        commentWrapper.id = `comment-wrapper-${comment.id}`;
        if (isReply) {
            commentWrapper.className = 'ml-10 mt-4'; // Thụt vào cho các trả lời
        }

        const commentElement = document.createElement('div');
        commentElement.id = `comment-${comment.id}`;
        commentElement.className = 'flex items-start space-x-3';

        const avatarSize = isReply ? 'w-10 h-10' : 'w-12 h-12';

        let ownerActions = '';
        if (loggedInUserId && comment.user_id == loggedInUserId) {
            ownerActions = `
                <button class="font-semibold text-blue-600 hover:underline edit-comment-btn" data-comment-id="${comment.id}">Sửa</button>
                <span class="mx-1">·</span>
                <button class="font-semibold text-red-600 hover:underline delete-comment-btn" data-comment-id="${comment.id}">Xóa</button>
            `;
        }

        const likeBtnClass = comment.user_has_liked == 1 ? themeColorClass : 'text-gray-600';

        commentElement.innerHTML = `
            <img src="${comment.user_avatar || defaultAvatar}" alt="${comment.username}" class="${avatarSize} rounded-full object-cover">
            <div class="flex-1">
                <div class="bg-gray-100 rounded-xl p-3">
                    <div class="flex justify-between items-center">
                        <p class="font-bold text-gray-800 text-sm">${comment.username}</p>
                        <span class="text-xs text-gray-500">${new Date(comment.created_at).toLocaleString('vi-VN')}</span>
                    </div>
                    <div class="comment-content text-gray-700 mt-1 text-sm">${comment.content}</div>
                </div>
                <div class="comment-actions mt-1 flex items-center space-x-3 text-xs px-2">
                    <button class="font-semibold hover:${themeColorClass} like-comment-btn ${likeBtnClass}" data-comment-id="${comment.id}">
                        Thích (<span class="like-count">${comment.likes}</span>)
                    </button>
                    <span class="text-gray-400">·</span>
                    <button class="font-semibold text-gray-600 hover:${themeColorClass} reply-comment-btn" data-comment-id="${comment.id}">Trả lời</button>
                    <span class="text-gray-400">·</span>
                    ${ownerActions}
                </div>
            </div>
        `;

        commentWrapper.appendChild(commentElement);

        // Vùng chứa các trả lời và form trả lời
        const repliesContainer = document.createElement('div');
        repliesContainer.id = `replies-container-${comment.id}`;
        commentWrapper.appendChild(repliesContainer);

        // Render các trả lời nếu có
        if (comment.replies && comment.replies.length > 0) {
            comment.replies.forEach(reply => {
                const replyElement = createCommentElement(reply, true);
                repliesContainer.appendChild(replyElement);
            });
        }

        return commentWrapper;
    }

    function createReplyForm(parentId) {
        const form = document.createElement('form');
        form.className = 'reply-form flex items-start space-x-3 ml-10 mt-4';
        form.innerHTML = `
            <img src="${document.getElementById('user-avatar-header')?.src || defaultAvatar}" alt="Your avatar" class="w-10 h-10 rounded-full object-cover">
            <div class="flex-1">
                <textarea name="content" class="w-full p-2 border rounded-lg text-sm" placeholder="Viết câu trả lời..." rows="2" required></textarea>
                <input type="hidden" name="parent_id" value="${parentId}">
                <div class="text-right mt-2">
                    <button type="button" class="text-xs font-bold text-gray-500 hover:underline cancel-reply-btn">Hủy</button>
                    <button type="submit" class="text-xs font-bold text-green-600 hover:underline ml-4">Gửi</button>
                </div>
            </div>
        `;
        return form;
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
                            this.innerHTML = `Thích (<span class="like-count">${data.new_like_count}</span>)`;                            this.classList.toggle(themeColorClass, data.action === 'liked');
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
                            document.getElementById(`comment-wrapper-${commentId}`).remove();
                        } else {
                            showToast(data.message, true);
                        }
                    });
            };
        });

        document.querySelectorAll('.edit-comment-btn').forEach(btn => {
            btn.onclick = function() {
                const commentId = this.dataset.commentId;
                const commentWrapper = document.getElementById(`comment-wrapper-${commentId}`);
                const contentDiv = commentWrapper.querySelector('.comment-content');
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

                contentDiv.querySelector('.cancel-edit-btn').onclick = () => {
                    contentDiv.innerHTML = originalContent;
                    actionsDiv.style.display = 'flex';
                };
                contentDiv.querySelector('.save-edit-btn').onclick = () => {
                    const newContent = contentDiv.querySelector('textarea').value.trim();
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

        document.querySelectorAll('.reply-comment-btn').forEach(btn => {
            btn.onclick = function() {
                if (!loggedInUserId) { showToast('Vui lòng đăng nhập để trả lời.', true); return; }
                const commentId = this.dataset.commentId;
                const repliesContainer = document.getElementById(`replies-container-${commentId}`);

                // Xóa form trả lời cũ nếu có
                const existingForm = repliesContainer.querySelector('.reply-form');
                if (existingForm) {
                    existingForm.remove();
                    return; // Đóng form nếu nhấn lần nữa
                }

                const replyForm = createReplyForm(commentId);
                repliesContainer.appendChild(replyForm);
                replyForm.querySelector('textarea').focus();

                replyForm.querySelector('.cancel-reply-btn').onclick = () => replyForm.remove();

                replyForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    formData.append('target_id', targetId);
                    formData.append('target_type', targetType);

                    fetch(`${baseUrl}/includes/handlers/comments/addComment.php`, { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                showToast('Trả lời của bạn đã được gửi và đang chờ duyệt.');
                                replyForm.remove();
                            } else {
                                showToast(data.message || 'Không thể gửi trả lời.', true);
                            }
                        });
                });
            };
        });
    }

    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const content = formData.get('content').trim();
            if (!content) { showToast('Vui lòng nhập nội dung bình luận.', true); return; }
            formData.append('target_id', targetId);
            formData.append('target_type', targetType);

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