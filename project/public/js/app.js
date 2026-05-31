// ========== 1. Расширение textarea ==========
function initAutoResize() {
    const textarea = document.querySelector('.reply-form textarea');
    if (!textarea) return;

    function autoResize() {
        this.style.height = 'auto';
        const newHeight = Math.min(this.scrollHeight, 300);
        this.style.height = newHeight + 'px';

        if (this.scrollHeight > 300) {
            this.style.overflowY = 'auto';
        } else {
            this.style.overflowY = 'hidden';
        }
    }

    textarea.addEventListener('input', autoResize);
    autoResize.call(textarea);
}

// ========== 2. Анимация отправки комментария ==========
function initReplySubmit() {
    const submitBtn = document.querySelector('.reply-submit');
    if (!submitBtn) return;

    submitBtn.addEventListener('click', function (e) {
        const svg = this.querySelector('svg');
        if (!svg) return;

        svg.style.animation = 'fly 0.5s ease-out forwards';
        setTimeout(() => {
            svg.style.animation = '';
        }, 500);
    });
}

// ========== 3. Анимация лайка ==========
function initLikes() {
    const likeInputs = document.querySelectorAll('.like-input');
    if (!likeInputs.length) return;

    likeInputs.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const span = this.nextElementSibling;
            if (this.checked) {
                span.style.transition = 'all 0.2s ease-in';
            } else {
                span.style.transition = 'all 0.3s ease-out';
            }
        });
    });
}

// ========== 4. Фильтрация по категориям (AJAX) ==========
let loadFilteredNewsGlobal = null;

function initCategoryFilter() {
    const checkboxes = document.querySelectorAll('.category-checkbox');
    const newsContainer = document.querySelector('.news-feed');

    if (!newsContainer || !checkboxes.length) return;

    function loadFilteredNews(page = 1) {
        const selectedCategories = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        const body = new URLSearchParams();
        selectedCategories.forEach(id => body.append('categories[]', id));
        body.append('page', page);

        fetch('/api/news/filter', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: body.toString()
        })
            .then(response => response.json())
            .then(data => {
                if (data.html) {
                    newsContainer.innerHTML = data.html;
                    attachPaginationHandlers();
                    initReactionsOnPage();
                }
            })
            .catch(error => console.error('Filter error:', error));
    }

    loadFilteredNewsGlobal = loadFilteredNews;

    // При загрузке применяем фильтр, если чекбоксы отмечены
    if (Array.from(checkboxes).some(cb => cb.checked)) {
        loadFilteredNews(1);
    }

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => loadFilteredNews(1));
    });
}

// ========== 5. Пагинация ==========
function attachPaginationHandlers() {
    document.querySelectorAll('.ajax-page').forEach(link => {
        const oldHandler = link._paginationHandler;
        if (oldHandler) {
            link.removeEventListener('click', oldHandler);
        }
        const handler = function (e) {
            e.preventDefault();
            const page = this.dataset.page;
            if (page && loadFilteredNewsGlobal) {
                loadFilteredNewsGlobal(parseInt(page));
            }
        };
        link._paginationHandler = handler;
        link.addEventListener('click', handler);
    });
}

// ========== 6. Отправка комментария через AJAX ==========
function initComments() {
    const submitBtn = document.getElementById('submit-comment');
    const textarea = document.getElementById('comment-text');
    const commentsList = document.getElementById('comments-list');
    const commentCounter = document.querySelector('.comment-counter');

    if (!submitBtn || !textarea || !commentsList) return;

    const match = window.location.pathname.match(/\d+/);
    if (!match) return;
    const newsId = match[0];

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    submitBtn.addEventListener('click', function () {
        const text = textarea.value.trim();

        if (text === '') {
            alert('Введите комментарий');
            return;
        }

        if (text.length > 250) {
            alert('Комментарий не должен превышать 250 символов');
            return;
        }

        fetch(`/api/news/${newsId}/comment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'text=' + encodeURIComponent(text)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const newComment = `
                    <div class="comment-item" data-comment-id="${data.comment.id}">
                        <div class="comment-header">
                            <span class="comment-author">${escapeHtml(data.comment.author)}</span>
                            <span class="comment-date">${data.comment.date}</span>
                        </div>
                        <div class="comment-text">${data.comment.text}</div>
                    </div>
                `;
                    commentsList.insertAdjacentHTML('afterbegin', newComment);
                    textarea.value = '';

                    if (commentCounter) {
                        const currentCount = parseInt(commentCounter.textContent) || 0;
                        commentCounter.textContent = currentCount + 1;
                    }
                } else {
                    alert(data.error || 'Ошибка отправки');
                }
            })
            .catch(error => console.error('Error:', error));
    });
}

// ========== 7. Реакции (лайки) через AJAX ==========
function initReactionsOnPage() {
    const reactionBlocks = document.querySelectorAll('.reaction');
    if (!reactionBlocks.length) return;

    reactionBlocks.forEach(block => {
        if (block.dataset.reactionsInitialized === 'true') return;
        block.dataset.reactionsInitialized = 'true';

        const newsBox = block.closest('.news-box');
        if (!newsBox) return;
        const newsId = newsBox.id;

        function updateReactionDisplay(type, count) {
            const panelSpan = block.querySelector(`.alt-reaction-checkbox input[value="${type}"] + span`);
            if (panelSpan) {
                panelSpan.textContent = `${type} ${count}`;
            }
            if (type === '👍') {
                const likeSpan = block.querySelector('.like-checkbox span');
                if (likeSpan) {
                    likeSpan.textContent = `👍 ${count}`;
                }
            }
        }

        function sendReaction(type) {
            fetch(`/api/news/${newsId}/react`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'type=' + encodeURIComponent(type)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateReactionDisplay(data.type, data.count);
                    const checkbox = block.querySelector(`.alt-reaction-checkbox input[value="${data.type}"]`);
                    if (checkbox) {
                        checkbox.checked = data.action === 'added';
                    }
                    if (data.type === '👍') {
                        const likeCheckbox = block.querySelector('.like-checkbox .like-input');
                        if (likeCheckbox) {
                            likeCheckbox.checked = data.action === 'added';
                        }
                    }
                } else if (data.error === 'Необходимо авторизоваться') {
                    alert('Пожалуйста, войдите в систему');
                }
            })
            .catch(error => console.error('Error:', error));
        }

        fetch(`/api/news/${newsId}/user-reactions`)
            .then(response => response.json())
            .then(data => {
                block.querySelectorAll('.alt-reaction-checkbox input').forEach(checkbox => {
                    checkbox.checked = data.reactions.includes(checkbox.value);
                });
                const likeCheckbox = block.querySelector('.like-checkbox .like-input');
                if (likeCheckbox) {
                    likeCheckbox.checked = data.reactions.includes('👍');
                }
            });

        block.querySelectorAll('.alt-reaction-checkbox input').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                sendReaction(this.value);
            });
        });

        const likeCheckbox = block.querySelector('.like-checkbox .like-input');
        if (likeCheckbox) {
            likeCheckbox.addEventListener('change', function() {
                const panelLike = block.querySelector('.alt-reaction-checkbox input[value="👍"]');
                if (panelLike && panelLike.checked !== this.checked) {
                    panelLike.checked = this.checked;
                    panelLike.dispatchEvent(new Event('change'));
                }
            });
        }
    });
}

// ========== 8. Синхронизация фильтра при возврате назад ==========
window.addEventListener('pageshow', function() {
    const checkboxes = document.querySelectorAll('.category-checkbox');
    if (checkboxes.length && Array.from(checkboxes).some(cb => cb.checked) && typeof loadFilteredNewsGlobal === 'function') {
        loadFilteredNewsGlobal(1);
    }
});

// ========== 9. Запуск функций ==========
document.addEventListener('DOMContentLoaded', function () {
    initCategoryFilter();
    initAutoResize();
    initReplySubmit();
    initLikes();
    initComments();
    initReactionsOnPage();
    attachPaginationHandlers();
});
