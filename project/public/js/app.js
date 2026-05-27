// ========== 1. Расширени textarea (для комментариев) ==========
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

// ========== 2. Анимацаия отправки комментария ==========
function initReplySubmit() {
    const submitBtn = document.querySelector('.reply-submit');
    if (!submitBtn) return;

    submitBtn.addEventListener('click', function(e) {
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
    document.querySelectorAll('.like-input').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const span = this.nextElementSibling;
            if (this.checked) {
                span.style.transition = 'all 0.2s ease-in';
            } else {
                span.style.transition = 'all 0.3s ease-out';
            }
        });
    });
}

// ========== 4. Запуск функций ==========
document.addEventListener('DOMContentLoaded', function() {
    initAutoResize();
    initReplySubmit();
    initLikes();
});
