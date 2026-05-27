<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/styles.css">
    <title>ITISnews</title>
</head>

<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>
    <aside class="sidebar">
        <h1>Категории</h1>
        <br>
        <ul>
            <?php foreach ($categories as $cat): ?>
                <li>
                    <label class="checkbox-container">
                        <span class="label-text"><?= htmlspecialchars($cat['name']) ?></span>
                        <input type="checkbox" value="<?= htmlspecialchars($cat['slug']) ?>">
                        <svg viewBox="0 0 64 64">
                            <path
                                d="M 0 16 V 56 A 8 8 90 0 0 8 64 H 56 A 8 8 90 0 0 64 56 V 8 A 8 8 90 0 0 56 0 H 8 A 8 8 90 0 0 0 8 V 16 L 32 48 L 64 16 V 8 A 8 8 90 0 0 56 0 H 8 A 8 8 90 0 0 0 8 V 56 A 8 8 90 0 0 8 64 H 56 A 8 8 90 0 0 64 56 V 16"
                                pathLength="575.0541381835938" class="path"></path>
                        </svg>
                    </label>
                </li>
            <?php endforeach; ?>
        </ul>
    </aside>
    <main class="content">
        <?php foreach ($news as $item): ?>
            <div class="news-box" id="<?= $item['id'] ?>" style="white-space: pre-line;">
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
                                <input type="checkbox" name="alt-reaction" value="👍" class="alt-reaction-checkbox" hidden>
                                <span>👍 42</span>
                            </label>
                            <label class="alt-reaction-checkbox">
                                <input type="checkbox" name="alt-reaction" value="❤️" class="alt-reaction-checkbox" hidden>
                                <span>❤️ 42</span>
                            </label>
                            <label class="alt-reaction-checkbox">
                                <input type="checkbox" name="alt-reaction" value="🔥" class="alt-reaction-checkbox" hidden>
                                <span>🔥 42</span>
                            </label>
                            <label class="alt-reaction-checkbox">
                                <input type="checkbox" name="alt-reaction" value="😊" class="alt-reaction-checkbox" hidden>
                                <span>😊 42</span>
                            </label>
                            <label class="alt-reaction-checkbox">
                                <input type="checkbox" name="alt-reaction" value="😢" class="alt-reaction-checkbox" hidden>
                                <span>😢 42</span>
                            </label>
                        </div>
                    </div>
                    <div class="comment-stats">
                        <a href="/news/<?= $item['id'] ?>#comments" class="comment-button">
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
                            <span><?= $item['comments_count'] ?></span>
                        </a>
                    </div>
                    <div class="views-count">
                        <svg viewBox="0 0 24 24" width="25" height="25" stroke="currentColor" stroke-width="2" fill="none"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <span><?= $item['views'] ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </main>
    <?php include __DIR__ . '/../partials/footer.php'; ?>