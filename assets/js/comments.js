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
    const { targetType, targetId, loggedInUserId, baseUrl, themeColorClass, defaultAvatar, currentUser } = config;

    const commentsContainer = document.getElementById('comments-container');
    const commentForm = document.getElementById('comment-form');

    // --- REACTION CONSTANTS (Moved to higher scope) ---
    const reactionsMap = {
        'like': '👍', 'love': '❤️', 'haha': '😂', 'wow': '😮', 'sad': '😢', 'angry': '😠'
    };
    const reactionColors = {
        'like': 'text-blue-500', 'love': 'text-red-500', 'haha': 'text-yellow-500',
        'wow': 'text-yellow-500', 'sad': 'text-yellow-500', 'angry': 'text-orange-600'
    };
    // Function to capitalize first letter
    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
    // --- END REACTION CONSTANTS ---

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

    // Helper function to get rank class based on points
    function getRankClasses(points) {
        const p = parseInt(points || 0);
        if (p >= 1000) return 'rank-diamond';
        if (p >= 600) return 'rank-platinum';
        if (p >= 300) return 'rank-gold';
        if (p >= 100) return 'rank-silver';
        return 'rank-bronze';
    }

    // Function to render the current user's avatar and rank border
    function renderUserAvatarWithRank(container, avatarUrl, points, sizeClass = 'w-12 h-12') {
        if (!container) return;
        const rankClass = getRankClasses(points);
        const rankTooltip = {
            'rank-diamond': 'Hạng Kim Cương 💎',
            'rank-platinum': 'Hạng Bạch Kim ✨',
            'rank-gold': 'Hạng Vàng 🟡',
            'rank-silver': 'Hạng Bạc ⚪',
            'rank-bronze': 'Hạng Đồng 🟤'
        }[rankClass];

        container.className = `relative ${sizeClass} flex-shrink-0`;
        container.title = rankTooltip;
        container.innerHTML = `
            <img src="${(avatarUrl && (avatarUrl.startsWith('http') || avatarUrl.startsWith('/'))) ? avatarUrl : (baseUrl + '/' + (avatarUrl || defaultAvatar.replace(baseUrl + '/', '')))}" alt="Your avatar" class="w-full h-full rounded-full object-cover">
            <div class="absolute inset-0 rounded-full ${rankClass}"></div>
        `;
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
        const rankClass = getRankClasses(comment.user_points);
        // Tooltip text for the rank
        const rankTooltip = {
            'rank-diamond': 'Hạng Kim Cương 💎',
            'rank-platinum': 'Hạng Bạch Kim ✨',
            'rank-gold': 'Hạng Vàng 🟡',
            'rank-silver': 'Hạng Bạc ⚪',
            'rank-bronze': 'Hạng Đồng 🟤'
        }[rankClass];

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

        // Create reaction summary HTML
        let reactionSummaryContent = '';
        if (Object.keys(comment.reactions).length > 0) {
            reactionSummaryContent = `<div class="reaction-summary absolute -bottom-3 right-2 bg-white rounded-full shadow-md px-2 py-0.5 flex items-center text-xs">`;
            for (const type in comment.reactions) {
                reactionSummaryContent += `<span class="mr-1">${reactionsMap[type]} ${comment.reactions[type]}</span>`;
            }
            reactionSummaryContent += `</div>`;
        }

        commentElement.innerHTML = `
            <div class="relative ${avatarSize}" title="${rankTooltip}">
                <img src="${comment.user_avatar || defaultAvatar}" alt="${comment.username}" class="w-full h-full rounded-full object-cover">
                <div class="absolute inset-0 rounded-full ${rankClass}"></div>
            </div>
            <div class="flex-1">
                <div class="bg-gray-100 rounded-xl p-3 relative">
                    <div class="flex justify-between items-center">
                        <p class="font-bold text-gray-800 text-sm">${comment.username}</p>
                        <span class="text-xs text-gray-500">${new Date(comment.created_at).toLocaleString('vi-VN')}</span>
                    </div>
                    <div class="comment-content text-gray-700 mt-1 text-sm">${comment.content}</div>
                    ${reactionSummaryContent}
                </div>
                <div class="comment-actions mt-1 flex items-center space-x-3 text-xs px-2">
                    <div class="reaction-btn-wrapper relative">
                        <button class="font-semibold react-btn ${comment.user_reaction ? reactionColors[comment.user_reaction] : 'text-gray-600'}" data-comment-id="${comment.id}" data-current-reaction="${comment.user_reaction || ''}">
                            ${comment.user_reaction ? reactionsMap[comment.user_reaction] + ' ' + capitalizeFirstLetter(comment.user_reaction) : 'Thích'}
                        </button>
                        ${createReactionPopup(comment.id)}
                    </div>
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

    function createReactionPopup(commentId) {
        const reactions = { 'like': '👍', 'love': '❤️', 'haha': '😂', 'wow': '😮', 'sad': '😢', 'angry': '😠' };
        let popupHTML = `<div class="reaction-popup absolute bottom-full left-1/2 -translate-x-1/2 bg-white rounded-full shadow-lg p-1 flex space-x-1 opacity-0 pointer-events-none transition-all duration-200 transform scale-90 z-10">`;
        for (const type in reactions) {
            popupHTML += `<button class="reaction-option text-2xl transform hover:scale-125 transition-transform" data-comment-id="${commentId}" data-reaction-type="${type}">${reactions[type]}</button>`;
        }
        popupHTML += `</div>`;
        return popupHTML;
    }

    /**
     * Cập nhật giao diện người dùng cho một bình luận cụ thể sau khi có phản ứng.
     * @param {number} commentId - ID của bình luận.
     * @param {string|null} currentUserReaction - Loại cảm xúc hiện tại của người dùng (hoặc null nếu đã hủy).
     * @param {object} allReactions - Đối tượng chứa số lượng của tất cả các loại cảm xúc cho bình luận này.
     */
    function updateReactionUI(commentId, currentUserReaction, allReactions) {
        const commentWrapper = document.getElementById(`comment-wrapper-${commentId}`);
        if (!commentWrapper) return;

        const reactBtn = commentWrapper.querySelector('.react-btn');
        const reactionSummaryDiv = commentWrapper.querySelector('.reaction-summary'); // Find the summary div

        // 1. Update the main reaction button
        // Remove all existing reaction color classes
        Object.values(reactionColors).forEach(cls => reactBtn.classList.remove(cls));

        if (currentUserReaction) {
            reactBtn.classList.add(reactionColors[currentUserReaction]);
            reactBtn.innerHTML = `${reactionsMap[currentUserReaction]} ${capitalizeFirstLetter(currentUserReaction)}`;
            reactBtn.dataset.currentReaction = currentUserReaction;
        } else {
            reactBtn.classList.add('text-gray-600');
            reactBtn.innerHTML = 'Thích';
            reactBtn.dataset.currentReaction = '';
        }

        // 2. Update the reaction summary block
        if (Object.keys(allReactions).length > 0) {
            let summaryHTML = '';
            for (const type in allReactions) {
                summaryHTML += `<span class="mr-1">${reactionsMap[type]} ${allReactions[type]}</span>`;
            }
            if (reactionSummaryDiv) {
                reactionSummaryDiv.innerHTML = summaryHTML;
            } else {
                // If summary div doesn't exist, create and append it
                const newSummaryDiv = document.createElement('div');
                newSummaryDiv.className = "reaction-summary absolute -bottom-3 right-2 bg-white rounded-full shadow-md px-2 py-0.5 flex items-center text-xs";
                newSummaryDiv.innerHTML = summaryHTML;
                commentWrapper.querySelector('.bg-gray-100').appendChild(newSummaryDiv);
            }
        } else if (reactionSummaryDiv) {
            // If no reactions, remove the summary div
            reactionSummaryDiv.remove();
        }
    }

    function createReplyForm(parentId) {
        const form = document.createElement('form');
        form.className = 'reply-form flex items-start space-x-3 ml-10 mt-4';
        
        const avatarContainer = document.createElement('div');
        renderUserAvatarWithRank(avatarContainer, currentUser.avatar, currentUser.points, 'w-10 h-10');

        form.innerHTML = `
            <div class="flex-1">
                <textarea name="content" class="w-full p-2 border rounded-lg text-sm" placeholder="Viết câu trả lời..." rows="2" required></textarea>
                <input type="hidden" name="parent_id" value="${parentId}">
                <div class="text-right mt-2">
                    <button type="button" class="text-xs font-bold text-gray-500 hover:underline cancel-reply-btn">Hủy</button>
                    <button type="submit" class="text-xs font-bold text-green-600 hover:underline ml-4">Gửi</button>
                </div>
            </div>
        `;
        form.prepend(avatarContainer);
        return form;
    }

    function addCommentActionListeners() {
        // Reaction button hover/click logic
        document.querySelectorAll('.reaction-btn-wrapper').forEach(wrapper => {
            const popup = wrapper.querySelector('.reaction-popup');
            const reactBtn = wrapper.querySelector('.react-btn');

            wrapper.addEventListener('mouseenter', () => {
                popup.classList.remove('opacity-0', 'pointer-events-none', 'scale-90');
            });
            wrapper.addEventListener('mouseleave', () => {
                popup.classList.add('opacity-0', 'pointer-events-none', 'scale-90');
            });

            // Default action for the main button is 'like'
            reactBtn.addEventListener('click', () => {
                const currentReaction = reactBtn.dataset.currentReaction;
                // If user already reacted with 'like', clicking again should un-react.
                // Otherwise, it should react with 'like'.
                handleReaction(reactBtn.dataset.commentId, currentReaction === 'like' ? currentReaction : 'like');
            });
        });


        function handleReaction(commentId, reactionType) {
            if (!loggedInUserId) { showToast('Vui lòng đăng nhập để tương tác.', true); return; }
            const formData = new FormData();
            formData.append('comment_id', commentId);
            formData.append('reaction_type', reactionType);

            fetch(`${baseUrl}/includes/handlers/comments/reactComment.php`, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        updateReactionUI(commentId, data.action === 'unreacted' ? null : reactionType, data.new_reactions); // Pass reactionType for current user
                    } else { showToast(data.message, true); }
                });
        }

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
                const actionsDiv = commentWrapper.querySelector('.comment-actions');
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

        // Xử lý khi click vào một tùy chọn cảm xúc trong popup
        document.querySelectorAll('.reaction-option').forEach(option => {
            option.onclick = () => handleReaction(option.dataset.commentId, option.dataset.reactionType);
        });

    }

    // Sử dụng Event Delegation cho các hành động trên bình luận
    if (commentsContainer) {
        commentsContainer.addEventListener('click', function(e) {
            const target = e.target;
            // Xử lý khi click vào một tùy chọn cảm xúc
            // Logic này đã được chuyển vào addCommentActionListeners để đảm bảo hoạt động với các bình luận được thêm sau.
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

    // Render avatar for the main comment form
    if (loggedInUserId && currentUser) {
        const mainAvatarContainer = document.getElementById('main-comment-avatar-rank-wrapper');
        renderUserAvatarWithRank(mainAvatarContainer, currentUser.avatar, currentUser.points);
    }

    // Initial load
    if (commentsContainer) {
        loadComments();
    }
}