<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/styles.css">
    <title>Поиск - IT'sNews</title>
</head>

<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>
    <div class="main-background">
        <main class="content">
            <div style="max-width: 923px; margin: 0 auto; padding: 20px;">
                <h1 style="color: white; margin-bottom: 20px;">Результаты поиска: "<?= htmlspecialchars($query) ?>"</h1>
                <p style="color: #c1c0c0; margin-bottom: 30px;">Найдено новостей: <?= $total ?></p>

                <?php if (isset($error)): ?>
                    <div style="color: #efb2b2; padding: 20px; background: rgba(0,0,0,0.2); border-radius: 20px;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php elseif (empty($news)): ?>
                    <div style="color: #c1c0c0; padding: 20px; background: rgba(0,0,0,0.2); border-radius: 20px;">
                        Ничего не найдено. Попробуйте изменить поисковый запрос.
                    </div>
                <?php else: ?>
                    <?php foreach ($news as $item): ?>
                        <div class="news-box" style="margin: 0 0 30px 0;">
                            <a href="/news/<?= $item['id'] ?>" class="news-link">
                                <h1><?= htmlspecialchars($item['title']) ?></h1>
                                <h3><?= htmlspecialchars($item['excerpt']) ?></h3>
                            </a>
                            <div class="news-bottom">
                                <div class="reaction">
                                    <label class="like-checkbox">
                                        <input type="checkbox" name="like" class="like-input" hidden>
                                        <span>👍 <?= $item['likes'] ?></span>
                                    </label>
                                    <div class="reaction-panel">
                                        <label class="alt-reaction-checkbox">
                                            <input type="checkbox" name="alt-reaction" value="👍" class="alt-reaction-checkbox"
                                                hidden>
                                            <span>👍 <?= $item['likes'] ?></span>
                                        </label>
                                        <label class="alt-reaction-checkbox">
                                            <input type="checkbox" name="alt-reaction" value="❤️" class="alt-reaction-checkbox"
                                                hidden>
                                            <span>❤️ <?= $item['hearts'] ?? 0 ?></span>
                                        </label>
                                        <label class="alt-reaction-checkbox">
                                            <input type="checkbox" name="alt-reaction" value="🔥" class="alt-reaction-checkbox"
                                                hidden>
                                            <span>🔥 <?= $item['fire'] ?? 0 ?></span>
                                        </label>
                                        <label class="alt-reaction-checkbox">
                                            <input type="checkbox" name="alt-reaction" value="😊" class="alt-reaction-checkbox"
                                                hidden>
                                            <span>😊 <?= $item['smile'] ?? 0 ?></span>
                                        </label>
                                        <label class="alt-reaction-checkbox">
                                            <input type="checkbox" name="alt-reaction" value="😢" class="alt-reaction-checkbox"
                                                hidden>
                                            <span>😢 <?= $item['sad'] ?? 0 ?></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="comment-stats">
                                    <a href="/news/<?= $item['id'] ?>#comments" class="comment-button">
                                        <svg fill="currentColor" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"
                                            width="25" height="25">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                            <g id="SVGRepo_iconCarrier">
                                                <path
                                                    d="M 3 5 L 3 23 L 8 23 L 8 28.078125 L 14.351563 23 L 29 23 L 29 5 Z M 5 7 L 27 7 L 27 21 L 13.648438 21 L 10 23.917969 L 10 21 L 5 21 Z M 10 12 C 8.894531 12 8 12.894531 8 14 C 8 15.105469 8.894531 16 10 16 C 11.105469 16 12 15.105469 12 14 C 12 12.894531 11.105469 12 10 12 Z M 16 12 C 14.894531 12 14 12.894531 14 14 C 14 15.105469 14.894531 16 16 16 C 17.105469 16 18 15.105469 18 14 C 18 12.894531 17.105469 12 16 12 Z M 22 12 C 20.894531 12 20 12.894531 20 14 C 20 15.105469 20.894531 16 22 16 C 23.105469 16 24 15.105469 24 14 C 24 12.894531 23.105469 12 22 12 Z">
                                                </path>
                                            </g>
                                        </svg>
                                        <span><?= $item['comments_count'] ?></span>
                                    </a>
                                </div>
                                <div class="views-count">
                                    <svg viewBox="0 0 24 24" width="25" height="25" stroke="currentColor" stroke-width="2"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <span><?= $item['views'] ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($currentPage > 1): ?>
                                <a href="/search?q=<?= urlencode($query) ?>&page=<?= $currentPage - 1 ?>" class="pagination-link">←
                                    Назад</a>
                            <?php endif; ?>

                            <span class="pagination-current">Страница
                                <?= $currentPage ?> из
                                <?= $totalPages ?>
                            </span>

                            <?php if ($currentPage < $totalPages): ?>
                                <a href="/search?q=<?= urlencode($query) ?>&page=<?= $currentPage + 1 ?>"
                                    class="pagination-link">Вперёд →</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <div style="margin-top: 30px;">
                    <a href="/" class="home-button">← На главную</a>
                </div>
            </div>
        </main>
    </div>
    <?php include __DIR__ . '/../partials/footer.php'; ?>