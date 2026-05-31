<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/styles.css">
    <title>IT'sNews</title>
</head>

<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>
    <main class="content news-page">
        <aside class="back-aside">
            <a href="javascript:history.back()" class="back-button">
                ← Назад
            </a>
        </aside>
        <div class="news-wrapper">
            <div class="news-box" id="<?= $news['id'] ?>" style="white-space: pre-line;">
                <h1><?= htmlspecialchars($news['title']) ?></h1>
                <h3><?= nl2br(htmlspecialchars($news['content'])) ?></h3>

                <?php if (!empty($news['source_url'])): ?>
                    <p style="margin-top: 20px;">
                        <h3>Источник:</h3>
                        <a href="<?= htmlspecialchars($news['source_url']) ?>" target="_blank" style="color: #efb2b2; padding-left: 20px;">
                            <?= htmlspecialchars($news['source_url']) ?>
                        </a>
                    </p>
                <?php endif; ?>
                <div class="news-bottom">
                    <div class="reaction">
                        <label class="like-checkbox">
                            <input type="checkbox" name="like" class="like-input" hidden>
                            <span>👍 <?= $news['likes'] ?></span>
                        </label>
                        <div class="reaction-panel">
                            <label class="alt-reaction-checkbox">
                                <input type="checkbox" name="alt-reaction" value="👍" class="alt-reaction-checkbox"
                                    hidden>
                                <span>👍 <?= $news['likes'] ?></span>
                            </label>
                            <label class="alt-reaction-checkbox">
                                <input type="checkbox" name="alt-reaction" value="❤️" class="alt-reaction-checkbox"
                                    hidden>
                                <span>❤️ <?= $news['hearts'] ?? 0 ?></span>
                            </label>
                            <label class="alt-reaction-checkbox">
                                <input type="checkbox" name="alt-reaction" value="🔥" class="alt-reaction-checkbox"
                                    hidden>
                                <span>🔥 <?= $news['fire'] ?? 0 ?></span>
                            </label>
                            <label class="alt-reaction-checkbox">
                                <input type="checkbox" name="alt-reaction" value="😊" class="alt-reaction-checkbox"
                                    hidden>
                                <span>😊 <?= $news['smile'] ?? 0 ?></span>
                            </label>
                            <label class="alt-reaction-checkbox">
                                <input type="checkbox" name="alt-reaction" value="😢" class="alt-reaction-checkbox"
                                    hidden>
                                <span>😢 <?= $news['sad'] ?? 0 ?></span>
                            </label>
                        </div>
                    </div>
                    <div class="comment-stats">
                        <button class="comment-button">
                            <svg fill="currentColor" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" width="25"
                                height="25">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <path
                                        d="M 3 5 L 3 23 L 8 23 L 8 28.078125 L 14.351563 23 L 29 23 L 29 5 Z M 5 7 L 27 7 L 27 21 L 13.648438 21 L 10 23.917969 L 10 21 L 5 21 Z M 10 12 C 8.894531 12 8 12.894531 8 14 C 8 15.105469 8.894531 16 10 16 C 11.105469 16 12 15.105469 12 14 C 12 12.894531 11.105469 12 10 12 Z M 16 12 C 14.894531 12 14 12.894531 14 14 C 14 15.105469 14.894531 16 16 16 C 17.105469 16 18 15.105469 18 14 C 18 12.894531 17.105469 12 16 12 Z M 22 12 C 20.894531 12 20 12.894531 20 14 C 20 15.105469 20.894531 16 22 16 C 23.105469 16 24 15.105469 24 14 C 24 12.894531 23.105469 12 22 12 Z">
                                    </path>
                                </g>
                            </svg>
                            <span><?= $news['comments_count'] ?></span>
                        </button>
                    </div>
                    <div class="views-count">
                        <svg viewBox="0 0 24 24" width="25" height="25" stroke="currentColor" stroke-width="2"
                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <span><?= $news['views'] ?></span>
                    </div>
                </div>
            </div>

            <div class="comment-section" id="comments">
                <div class="comment-section-header">
                    <span class="comment-title">Комментарии</span>
                    <span class="comment-counter"><?= $news['comments_count'] ?></span>
                </div>
                <div class="reply-form">
                    <div class="reply-form-container">
                        <textarea id="comment-text" placeholder="Прокомментировать..." rows="5"></textarea>
                        <div class="reply-actions">
                            <button type="button" id="submit-comment" class="reply-submit" title="Отправить">
                                <svg fill="none" viewBox="0 0 24 24" height="18" width="18"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linejoin="round" stroke-linecap="round" stroke-width="2.5"
                                        stroke="#ffffff" d="M12 5L12 20"></path>
                                    <path stroke-linejoin="round" stroke-linecap="round" stroke-width="2.5"
                                        stroke="#ffffff"
                                        d="M7 9L11.2929 4.70711C11.6262 4.37377 11.7929 4.20711 12 4.20711C12.2071 4.20711 12.3738 4.37377 12.7071 4.70711L17 9">
                                    </path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="comments-list" id="comments-list">
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-item" data-comment-id="<?= $comment['id'] ?>">
                            <div class="comment-header">
                                <span class="comment-author"><?= htmlspecialchars($comment['author']) ?></span>
                                <span class="comment-date"><?= htmlspecialchars($comment['date']) ?></span>
                            </div>
                            <div class="comment-text"><?= nl2br(htmlspecialchars($comment['text'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/../partials/footer.php'; ?>